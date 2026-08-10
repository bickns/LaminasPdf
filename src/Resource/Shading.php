<?php
namespace LaminasPdf\Resource;

use LaminasPdf\Exception;
use LaminasPdf\InternalType;
use LaminasPdf\ObjectFactory;

class Shading extends AbstractResource
{
    // Deliberately NOT calling parent::__construct() - it would allocate
    // its own factory, disconnected from the one the Function object needs to share.
    protected function __construct(InternalType\DictionaryObject $shadingDict, ObjectFactory $factory)
    {
        $this->_objectFactory = $factory;
        $this->_resource = $factory->newObject($shadingDict);
    }


    // ===================== Low-level builders =====================

    /**
     * Axial (Type 2 / "linear") shading between two points, through 2 or more colors.
     *
     * @param float $x1,$y1  Gradient axis start point
     * @param float $x2,$y2  Gradient axis end point
     * @param array $colors  List of [r, g, b] stops (min 2), evenly spaced along the axis
     * @param bool  $extend  Extend the end colors beyond the axis endpoints
     */
    public static function axial($x1, $y1, $x2, $y2, array $colors, $extend = true)
    {
        $factory = ObjectFactory::createFactory(1);
        $functionObj = self::_buildColorFunction($factory, $colors);

        $shadingDict = new InternalType\DictionaryObject();
        $shadingDict->ShadingType = new InternalType\NumericObject(2);
        $shadingDict->ColorSpace  = new InternalType\NameObject('DeviceRGB');
        $shadingDict->Coords = new InternalType\ArrayObject([
            new InternalType\NumericObject($x1),
            new InternalType\NumericObject($y1),
            new InternalType\NumericObject($x2),
            new InternalType\NumericObject($y2),
        ]);
        $shadingDict->Function = $functionObj;
        $shadingDict->Extend = new InternalType\ArrayObject([
            new InternalType\BooleanObject($extend),
            new InternalType\BooleanObject($extend),
        ]);

        return new self($shadingDict, $factory);
    }

    /**
     * Radial (Type 3) shading between two circles, through 2 or more colors.
     *
     * @param float $x0,$y0,$r0  Inner circle (center + radius)
     * @param float $x1,$y1,$r1  Outer circle (center + radius)
     * @param array $colors  List of [r, g, b] stops (min 2), evenly spaced from inner to outer
     * @param bool  $extend  Extend the outer color beyond r1
     */
    public static function radial($x0, $y0, $r0, $x1, $y1, $r1, array $colors, $extend = true)
    {
        $factory = ObjectFactory::createFactory(1);
        $functionObj = self::_buildColorFunction($factory, $colors);

        $shadingDict = new InternalType\DictionaryObject();
        $shadingDict->ShadingType = new InternalType\NumericObject(3);
        $shadingDict->ColorSpace  = new InternalType\NameObject('DeviceRGB');
        $shadingDict->Coords = new InternalType\ArrayObject([
            new InternalType\NumericObject($x0),
            new InternalType\NumericObject($y0),
            new InternalType\NumericObject($r0),
            new InternalType\NumericObject($x1),
            new InternalType\NumericObject($y1),
            new InternalType\NumericObject($r1),
        ]);
        $shadingDict->Function = $functionObj;
        $shadingDict->Extend = new InternalType\ArrayObject([
            new InternalType\BooleanObject($extend),
            new InternalType\BooleanObject($extend),
        ]);

        return new self($shadingDict, $factory);
    }


    // ===================== Convenience factories (rect-relative) =====================
    // Moved here from Page.php - Page shouldn't need to know how a shading's
    // geometry is derived, only how to paint one (see Page::shadeRect() /
    // Page::drawTextWithShading()).

    /**
     * Shading for a top-to-bottom gradient over the given rectangle.
     *
     * @param float $x1,$y1,$x2,$y2  Rectangle bounds
     * @param array $colors  List of [r, g, b] stops (min 2) -
     *                       e.g. [$darkBlue, $blue, $darkBlue] for a "pill" look
     */
    public static function topToBottom($x1, $y1, $x2, $y2, array $colors)
    {
        return self::axial($x1, $y2, $x1, $y1, $colors);
    }

    /**
     * Shading for a left-to-right gradient over the given rectangle.
     *
     * @param float $x1,$y1,$x2,$y2  Rectangle bounds
     * @param array $colors  List of [r, g, b] stops (min 2)
     */
    public static function leftToRight($x1, $y1, $x2, $y2, array $colors)
    {
        $midY = ($y1 + $y2) / 2;

        return self::axial($x1, $midY, $x2, $midY, $colors);
    }

    /**
     * Shading for a diagonal, corner-to-corner gradient over the given rectangle.
     *
     * The direction of travel is perpendicular to the line joining the two
     * UNUSED corners, not the line joining the two named corners themselves -
     * same construction CSS uses for linear-gradient(to bottom right, ...).
     * Using the named corners directly as the axis only looks right on a
     * square; on any other rectangle it puts the color bands at whatever odd
     * angle that diagonal happens to be, rather than running evenly across
     * the rect the way a brush stroke at that corner-to-corner angle would.
     *
     * @param float $x1,$y1,$x2,$y2  Rectangle bounds
     * @param array $colors  List of [r, g, b] stops (min 2)
     * @param string $direction  'TL->BR' (default), 'BR->TL', 'BL->TR', or 'TR->BL'
     */
    public static function cornerToCorner($x1, $y1, $x2, $y2, array $colors, $direction = 'TL->BR')
    {
        $tl = [$x1, $y2];
        $tr = [$x2, $y2];
        $br = [$x2, $y1];
        $bl = [$x1, $y1];

        switch ($direction) {
            case 'BR->TL':
                [$usedStart, $usedEnd] = [$br, $tl];
                [$unusedA, $unusedB]   = [$tr, $bl];
                break;
            case 'BL->TR':
                [$usedStart, $usedEnd] = [$bl, $tr];
                [$unusedA, $unusedB]   = [$tl, $br];
                break;
            case 'TR->BL':
                [$usedStart, $usedEnd] = [$tr, $bl];
                [$unusedA, $unusedB]   = [$tl, $br];
                break;
            case 'TL->BR':
            default:
                [$usedStart, $usedEnd] = [$tl, $br];
                [$unusedA, $unusedB]   = [$tr, $bl];
                break;
        }

        // Direction perpendicular to the unused-corners line - this is the
        // "brush angle". Color bands end up parallel to the unused corners;
        // travel runs perpendicular to them.
        $dx = $unusedB[0] - $unusedA[0];
        $dy = $unusedB[1] - $unusedA[1];
        $length = sqrt($dx ** 2 + $dy ** 2);
        $perpX = -$dy / $length;
        $perpY = $dx / $length;

        // Make sure the perpendicular points from usedStart towards usedEnd
        $towardEndX = $usedEnd[0] - $usedStart[0];
        $towardEndY = $usedEnd[1] - $usedStart[1];
        if (($perpX * $towardEndX + $perpY * $towardEndY) < 0) {
            $perpX = -$perpX;
            $perpY = -$perpY;
        }

        $centerX = ($x1 + $x2) / 2;
        $centerY = ($y1 + $y2) / 2;

        // Project the used corners onto the perpendicular axis through the
        // rect's center - this is what makes the gradient reach pure color
        // exactly at each named corner's projection, while staying centered.
        $t1 = ($usedStart[0] - $centerX) * $perpX + ($usedStart[1] - $centerY) * $perpY;
        $t2 = ($usedEnd[0] - $centerX) * $perpX + ($usedEnd[1] - $centerY) * $perpY;

        $axisX1 = $centerX + $t1 * $perpX;
        $axisY1 = $centerY + $t1 * $perpY;
        $axisX2 = $centerX + $t2 * $perpX;
        $axisY2 = $centerY + $t2 * $perpY;

        return self::axial($axisX1, $axisY1, $axisX2, $axisY2, $colors);
    }

    /**
     * Shading for a radial gradient centered on the given rectangle.
     * Outer radius reaches the corners (half the diagonal) so the whole
     * rect gets shaded.
     *
     * @param float $x1,$y1,$x2,$y2  Rectangle bounds
     * @param array $colors  List of [r, g, b] stops (min 2), center to edge
     */
    public static function radialCentered($x1, $y1, $x2, $y2, array $colors)
    {
        $centerX = ($x1 + $x2) / 2;
        $centerY = ($y1 + $y2) / 2;
        $outerRadius = sqrt((($x2 - $x1) / 2) ** 2 + (($y2 - $y1) / 2) ** 2);

        return self::radial($centerX, $centerY, 0, $centerX, $centerY, $outerRadius, $colors);
    }


    // ===================== Function construction (private) =====================

    /**
     * Build the color Function driving a shading, from 2 or more color stops.
     *
     * Exactly 2 colors: a single Type 2 (exponential interpolation) function.
     * 3+ colors: N-1 Type 2 functions chained with a Type 3 ("stitching")
     * function across evenly-spaced sub-ranges of the [0,1] domain.
     *
     * @return InternalType\IndirectObject
     */
    private static function _buildColorFunction(ObjectFactory $factory, array $colors)
    {
        $stopCount = count($colors);

        if ($stopCount < 2) {
            throw new Exception\InvalidArgumentException('Shading requires at least 2 colors.');
        }

        if ($stopCount === 2) {
            return self::_buildType2Function($factory, $colors[0], $colors[1]);
        }

        $segmentCount = $stopCount - 1;

        $subFunctions = new InternalType\ArrayObject();
        $bounds = new InternalType\ArrayObject();
        $encode = new InternalType\ArrayObject();

        for ($i = 0; $i < $segmentCount; $i++) {
            $subFunctions->items[] = self::_buildType2Function($factory, $colors[$i], $colors[$i + 1]);

            $encode->items[] = new InternalType\NumericObject(0);
            $encode->items[] = new InternalType\NumericObject(1);

            if ($i < $segmentCount - 1) {
                $bounds->items[] = new InternalType\NumericObject(($i + 1) / $segmentCount);
            }
        }

        $stitchingFunction = new InternalType\DictionaryObject();
        $stitchingFunction->FunctionType = new InternalType\NumericObject(3);
        $stitchingFunction->Domain = new InternalType\ArrayObject([
            new InternalType\NumericObject(0),
            new InternalType\NumericObject(1),
        ]);
        $stitchingFunction->Functions = $subFunctions;
        $stitchingFunction->Bounds = $bounds;
        $stitchingFunction->Encode = $encode;

        return $factory->newObject($stitchingFunction);
    }

    /**
     * Build a single Type 2 (exponential interpolation) function between two colors.
     */
    private static function _buildType2Function(ObjectFactory $factory, array $c0Rgb, array $c1Rgb)
    {
        $function = new InternalType\DictionaryObject();
        $function->FunctionType = new InternalType\NumericObject(2);
        $function->Domain = new InternalType\ArrayObject([
            new InternalType\NumericObject(0),
            new InternalType\NumericObject(1),
        ]);
        $function->C0 = new InternalType\ArrayObject(array_map(
            fn($c) => new InternalType\NumericObject($c),
            $c0Rgb
        ));
        $function->C1 = new InternalType\ArrayObject(array_map(
            fn($c) => new InternalType\NumericObject($c),
            $c1Rgb
        ));
        $function->N = new InternalType\NumericObject(1);

        return $factory->newObject($function);
    }
}