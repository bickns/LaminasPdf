<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   LaminasPdf
 */

namespace LaminasPdfTest;

use LaminasPdf as Pdf;
use LaminasPdf\Color;
use LaminasPdf\InternalType;
use LaminasPdf\Resource\Shading;

/** \LaminasPdf */

/** \LaminasPdf\Page */

/** \LaminasPdf\Resource\Shading */


/** PHPUnit Test Case */

/**
 * @category   Zend
 * @package    LaminasPdf
 * @subpackage UnitTests
 * @group      LaminasPdf
 */
class ShadingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Stores the original set timezone
     * @var string
     */
    private $_originaltimezone;

    public function setUp(): void
    {
        $this->_originaltimezone = date_default_timezone_get();
        date_default_timezone_set('GMT');
    }

    /**
     * Teardown environment
     */
    public function tearDown(): void
    {
        date_default_timezone_set($this->_originaltimezone);
    }

    public function testDrawRectangleWithShading()
    {
        $pdf = new Pdf\PdfDocument();

        $pdf->pages[] = ($page = $pdf->newPage('A4'));

        // Basic top->bottom axial shading, red at top fading to blue at bottom
        $shading1 = Shading::topToBottom(60, 400, 500, 700, [[1, 0, 0], [0, 0, 1]]);
        $page->drawRectangleWithShading($shading1, 60, 400, 500, 700);

        // A second, independent shaded rect on the same page - exercises resource
        // dedup/naming (should get its own "S2" resource, not collide with the first)
        $shading2 = Shading::topToBottom(60, 100, 500, 350, [[0, 1, 0], [1, 1, 0]]);
        $page->drawRectangleWithShading($shading2, 60, 100, 500, 350);

        $pdf->save(__DIR__ . '/_files/output.pdf');
        unset($pdf);

        $pdf1 = Pdf\PdfDocument::load(__DIR__ . '/_files/output.pdf');
        $this->assertTrue($pdf1 instanceof Pdf\PdfDocument);
        unset($pdf1);

        unlink(__DIR__ . '/_files/output.pdf');
    }

    public function testShadingResourceAttachment()
    {
        $pdf = new Pdf\PdfDocument();
        $pdf->pages[] = ($page = $pdf->newPage('A4'));

        $shading = Shading::topToBottom(60, 400, 500, 700, [[1, 0, 0], [0, 0, 1]]);
        $page->drawRectangleWithShading($shading, 60, 400, 500, 700);

        $resources = $page->extractResources();
        $this->assertNotNull($resources->Shading);

        // Exactly one Shading resource should have been registered
        $shadingKeys = $resources->Shading->getKeys();
        $this->assertCount(1, $shadingKeys);

        // ... and it should follow the same S<n> naming convention as F<n>/X<n>
        $this->assertEquals('S1', $shadingKeys[0]);
    }

    public function testShadingDictionaryContents()
    {
        $shading = Shading::axial(60, 350, 60, 700, [[1, 0, 0], [0, 0, 1]]);

        $resource = $shading->getResource();
        $this->assertTrue($resource instanceof InternalType\IndirectObject);
        $this->assertEquals(2, $resource->ShadingType->value);
        $this->assertEquals('DeviceRGB', $resource->ColorSpace->value);
        $this->assertNotNull($resource->Function);

        $coords = $resource->Coords->items;
        $this->assertEquals(60, $coords[0]->value);
        $this->assertEquals(350, $coords[1]->value);
        $this->assertEquals(60, $coords[2]->value);
        $this->assertEquals(700, $coords[3]->value);
    }

    public function testTwoColorFunctionIsType2()
    {
        // Exactly 2 stops should use a plain Type 2 function, no stitching overhead
        $shading = Shading::axial(0, 0, 0, 100, [[1, 0, 0], [0, 0, 1]]);

        $function = $shading->getResource()->Function;
        $this->assertEquals(2, $function->FunctionType->value);
    }

    public function testMultiStopUsesStitchingFunction()
    {
        // 3 stops should produce a Type 3 stitching function wrapping 2
        // Type 2 sub-functions, with 1 boundary between them
        $shading = Shading::axial(0, 0, 0, 100, [[0, 0, 0.4], [0.3, 0.5, 1], [0, 0, 0.4]]);

        $function = $shading->getResource()->Function;
        $this->assertEquals(3, $function->FunctionType->value);
        $this->assertCount(2, $function->Functions->items);
        $this->assertCount(1, $function->Bounds->items);

        foreach ($function->Functions->items as $subFunction) {
            $this->assertEquals(2, $subFunction->FunctionType->value);
        }
    }

    public function testFiveStopBoundsAreEvenlySpaced()
    {
        // 5 stops -> 4 sub-functions -> 3 bounds, at 0.25/0.5/0.75
        $shading = Shading::axial(
            0,
            0,
            0,
            100,
            [[1, 0, 0], [1, 0.5, 0], [1, 1, 0], [0, 1, 0], [0, 0, 1]]
        );

        $function = $shading->getResource()->Function;
        $this->assertCount(4, $function->Functions->items);
        $this->assertCount(3, $function->Bounds->items);

        $this->assertEqualsWithDelta(0.25, $function->Bounds->items[0]->value, 0.0001);
        $this->assertEqualsWithDelta(0.5, $function->Bounds->items[1]->value, 0.0001);
        $this->assertEqualsWithDelta(0.75, $function->Bounds->items[2]->value, 0.0001);
    }

    public function testRadialShadingType()
    {
        $shading = Shading::radialCentered(0, 0, 100, 100, [[1, 1, 1], [0, 0, 0.5]]);

        $resource = $shading->getResource();
        $this->assertEquals(3, $resource->ShadingType->value);

        // Coords: [x0, y0, r0, x1, y1, r1] - inner circle radius should be 0
        $coords = $resource->Coords->items;
        $this->assertEquals(0, $coords[2]->value);
    }

    public function testCornerToCornerProducesAxialShading()
    {
        // Full geometry correctness (the "brush angle" construction) is
        // eyeballed via demo-shading.php - this just confirms it produces a
        // valid Type 2 shading and doesn't throw across all four directions.
        foreach (['TL->BR', 'BR->TL', 'BL->TR', 'TR->BL'] as $direction) {
            $shading = Shading::cornerToCorner(0, 0, 100, 60, [[1, 0.5, 0], [0.5, 0, 0.5]], $direction);
            $this->assertEquals(2, $shading->getResource()->ShadingType->value);
        }
    }

    public function testDrawRoundedRectangleWithShading()
    {
        $pdf = new Pdf\PdfDocument();
        $pdf->pages[] = ($page = $pdf->newPage('A4'));

        $shading = Shading::radialCentered(60, 600, 300, 700, [[1, 1, 1], [0, 0, 0.5]]);
        $page->drawRoundedRectangleWithShading($shading, 60, 600, 300, 700, 20);

        $resources = $page->extractResources();
        $shadingKeys = $resources->Shading->getKeys();
        $this->assertCount(1, $shadingKeys);

        $pdf->save(__DIR__ . '/_files/output.pdf');
        unset($pdf);

        $pdf1 = Pdf\PdfDocument::load(__DIR__ . '/_files/output.pdf');
        $this->assertTrue($pdf1 instanceof Pdf\PdfDocument);
        unset($pdf1);

        unlink(__DIR__ . '/_files/output.pdf');
    }
}