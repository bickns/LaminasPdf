<?php
/**
 * Standalone demo - NOT a PHPUnit test.
 * Produces demo-shading.pdf so you can eyeball the actual gradient output
 * in a real PDF viewer, rather than just checking dictionary contents.
 *
 * Run from the repo root:
 *   php test/demo-shading.php
 */

require __DIR__ . '/../vendor/autoload.php';

use LaminasPdf as Pdf;
use LaminasPdf\Color;
use LaminasPdf\Resource\Shading;

$pdf = new Pdf\PdfDocument();
$font = Pdf\Font::fontWithName(Pdf\Font::FONT_HELVETICA);

// Reusable color stops
$darkBlue = [0, 0, 0.4];
$blue     = [0.3, 0.5, 1];
$red      = [1, 0, 0];
$orange   = [1, 0.5, 0];
$yellow   = [1, 1, 0];
$green    = [0, 0.7, 0];
$violet   = [0.5, 0, 0.8];


// ===================== PAGE 1: basic (2-color) directions =====================

$pdf->pages[] = ($page1 = $pdf->newPage('A4'));
$page1->setFont($font, 12)->setFillColor(new Color\GrayScale(0));

$page1->drawText('1. Top -> bottom (red -> blue)', 60, 770);
$page1->drawRectangleWithShading(
    Shading::topToBottom(60, 690, 535, 760, [$red, [0, 0, 1]]),
    60,
    690,
    535,
    760
);

$page1->drawText('2. Left -> right (green -> yellow)', 60, 670);
$page1->drawRectangleWithShading(
    Shading::leftToRight(60, 590, 535, 660, [$green, $yellow]),
    60,
    590,
    535,
    660
);

$page1->drawText('3. Corner -> corner: all four diagonals', 60, 570);
$page1->drawRectangleWithShading(
    Shading::cornerToCorner(60, 490, 285, 560, [$orange, $violet], 'TL->BR'),
    60,
    490,
    285,
    560
);
$page1->drawRectangleWithShading(
    Shading::cornerToCorner(310, 490, 535, 560, [$orange, $violet], 'BR->TL'),
    310,
    490,
    535,
    560
);
$page1->drawRectangleWithShading(
    Shading::cornerToCorner(60, 400, 285, 470, [$orange, $violet], 'BL->TR'),
    60,
    400,
    285,
    470
);
$page1->drawRectangleWithShading(
    Shading::cornerToCorner(310, 400, 535, 470, [$orange, $violet], 'TR->BL'),
    310,
    400,
    535,
    470
);

$page1->drawText('4. Radial (white center -> deep blue edge)', 60, 380);
$page1->drawRectangleWithShading(
    Shading::radialCentered(60, 230, 250, 350, [[1, 1, 1], [0, 0, 0.5]]),
    60,
    230,
    250,
    350
);

$page1->drawText('5. Radial on a wide, short rect', 60, 210);
$page1->drawRectangleWithShading(
    Shading::radialCentered(60, 130, 535, 190, [[1, 1, 0.6], [0.6, 0.2, 0]]),
    60,
    130,
    535,
    190
);

$page1->drawText('6. Gradient text (glyphs only, not a rect):', 60, 100);
$page1->setFont($font, 48);
$titleShading = Shading::axial(60, 20, 60, 60, [$red, $orange]); // bottom to top
$page1->drawTextWithShading($titleShading, 'GRADIENT TEXT', 60, 30);


// ===================== PAGE 2: multi-stop (3+ color) gradients =====================

$pdf->pages[] = ($page2 = $pdf->newPage('A4'));
$page2->setFont($font, 12)->setFillColor(new Color\GrayScale(0));

// --- 7. "Curved pill" look: dark -> light -> dark suggests a rounded highlight ---
$page2->drawText('7. Top -> bottom, 3 stops (dark blue -> blue -> dark blue)', 60, 770);
$page2->drawRectangleWithShading(
    Shading::topToBottom(60, 690, 535, 760, [$darkBlue, $blue, $darkBlue]),
    60,
    690,
    535,
    760
);

$page2->drawText('7b. Same idea, left -> right', 60, 670);
$page2->drawRectangleWithShading(
    Shading::leftToRight(60, 590, 535, 660, [$darkBlue, $blue, $darkBlue]),
    60,
    590,
    535,
    660
);

// --- 8. Rainbow / unicorn pattern, 5 stops, diagonal TR->BL ---
$page2->drawText('8. Rainbow, 5 stops, corner-to-corner TR->BL', 60, 570);
$page2->drawRectangleWithShading(
    Shading::cornerToCorner(
        60,
        460,
        535,
        560,
        [$red, $orange, $yellow, $green, [0, 0.3, 1]],
        'TR->BL'
    ),
    60,
    460,
    535,
    560
);

// --- 9. Multi-stop radial, for completeness ---
$page2->drawText('9. Radial, 4 stops', 60, 440);
$page2->drawRectangleWithShading(
    Shading::radialCentered(60, 300, 250, 420, [[1, 1, 1], $yellow, $orange, $red]),
    60,
    300,
    250,
    420
);

// --- 10. Multi-stop gradient text - drawTextWithShading needed NO changes for this ---
// --- 10. Multi-stop gradient text - same corner-to-corner treatment as case 8 ---
$page2->drawText('10. Multi-stop gradient text (unicorn):', 60, 270);
$page2->setFont($font, 48);
$rainbowTextShading = Shading::cornerToCorner(
    60,
    170,
    320,
    210,
    [$red, $orange, $yellow, $green, [0, 0.3, 1]],
    'TR->BL'
);
$page2->drawTextWithShading($rainbowTextShading, 'UNICORN', 60, 170);
$page2->setFont($font, 12); // reset the font size 

// --- 11. Rounded rectangle with a radial shading through the clip ---
$page2->drawText('11. Rounded rectangle, axial shading', 60, 100);
$page2->drawRoundedRectangleWithShading(
    Shading::axial(60, 20, 300, 90, [[1, 1, 1], $darkBlue]),
    60,
    20,
    300,
    90,
    20
);

$outputPath = __DIR__ . '/output/demo-shading.pdf';
$pdf->save($outputPath);

echo "Saved: $outputPath\n";