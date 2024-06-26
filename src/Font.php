<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   LaminasPdf
 */

namespace LaminasPdf;

use LaminasPdf\Exception;

/**
 * Abstract factory class which vends {@link \LaminasPdf\Resource\Font\AbstractFont} objects.
 *
 * Font objects themselves are normally instantiated through the factory methods
 * {@link fontWithName()} or {@link fontWithPath()}.
 *
 * This class is also the home for font-related constants because the name of
 * the true base class ({@link \LaminasPdf\Resource\Font\AbstractFont}) is not intuitive
 * for the end user.
 *
 * @package    LaminasPdf
 * @subpackage LaminasPdf\Fonts
 */
abstract class Font
{
    /**** Class Constants ****/


    /* Font Types */

    /**
     * Unknown font type.
     */
    public const TYPE_UNKNOWN = 0;

    /**
     * One of the standard 14 PDF fonts.
     */
    public const TYPE_STANDARD = 1;

    /**
     * A PostScript Type 1 font.
     */
    public const TYPE_TYPE_1 = 2;

    /**
     * A TrueType font or an OpenType font containing TrueType outlines.
     */
    public const TYPE_TRUETYPE = 3;

    /**
     * Type 0 composite font.
     */
    public const TYPE_TYPE_0 = 4;

    /**
     * CID font containing a PostScript Type 1 font.
     * These fonts are used only to construct Type 0 composite fonts and can't be used directly
     */
    public const TYPE_CIDFONT_TYPE_0 = 5;

    /**
     * CID font containing a TrueType font or an OpenType font containing TrueType outlines.
     * These fonts are used only to construct Type 0 composite fonts and can't be used directly
     */
    public const TYPE_CIDFONT_TYPE_2 = 6;


    /* Names of the Standard 14 PDF Fonts */

    /**
     * Name of the standard PDF font Courier.
     */
    public const FONT_COURIER = 'Courier';

    /**
     * Name of the bold style of the standard PDF font Courier.
     */
    public const FONT_COURIER_BOLD = 'Courier-Bold';

    /**
     * Name of the italic style of the standard PDF font Courier.
     */
    public const FONT_COURIER_OBLIQUE = 'Courier-Oblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_COURIER_OBLIQUE}.
     */
    public const FONT_COURIER_ITALIC = 'Courier-Oblique';

    /**
     * Name of the bold and italic style of the standard PDF font Courier.
     */
    public const FONT_COURIER_BOLD_OBLIQUE = 'Courier-BoldOblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_COURIER_BOLD_OBLIQUE}.
     */
    public const FONT_COURIER_BOLD_ITALIC = 'Courier-BoldOblique';

    /**
     * Name of the standard PDF font Helvetica.
     */
    public const FONT_HELVETICA = 'Helvetica';

    /**
     * Name of the bold style of the standard PDF font Helvetica.
     */
    public const FONT_HELVETICA_BOLD = 'Helvetica-Bold';

    /**
     * Name of the italic style of the standard PDF font Helvetica.
     */
    public const FONT_HELVETICA_OBLIQUE = 'Helvetica-Oblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_HELVETICA_OBLIQUE}.
     */
    public const FONT_HELVETICA_ITALIC = 'Helvetica-Oblique';

    /**
     * Name of the bold and italic style of the standard PDF font Helvetica.
     */
    public const FONT_HELVETICA_BOLD_OBLIQUE = 'Helvetica-BoldOblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_HELVETICA_BOLD_OBLIQUE}.
     */
    public const FONT_HELVETICA_BOLD_ITALIC = 'Helvetica-BoldOblique';

    /**
     * Name of the standard PDF font Symbol.
     */
    public const FONT_SYMBOL = 'Symbol';

    /**
     * Name of the standard PDF font Times.
     */
    public const FONT_TIMES_ROMAN = 'Times-Roman';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_TIMES_ROMAN}.
     */
    public const FONT_TIMES = 'Times-Roman';

    /**
     * Name of the bold style of the standard PDF font Times.
     */
    public const FONT_TIMES_BOLD = 'Times-Bold';

    /**
     * Name of the italic style of the standard PDF font Times.
     */
    public const FONT_TIMES_ITALIC = 'Times-Italic';

    /**
     * Name of the bold and italic style of the standard PDF font Times.
     */
    public const FONT_TIMES_BOLD_ITALIC = 'Times-BoldItalic';

    /**
     * Name of the standard PDF font Zapf Dingbats.
     */
    public const FONT_ZAPFDINGBATS = 'ZapfDingbats';


    /* Font Name String Types */

    /**
     * Full copyright notice for the font.
     */
    public const NAME_COPYRIGHT = 0;

    /**
     * Font family name. Used to group similar styles of fonts together.
     */
    public const NAME_FAMILY = 1;

    /**
     * Font style within the font family. Examples: Regular, Italic, Bold, etc.
     */
    public const NAME_STYLE = 2;

    /**
     * Unique font identifier.
     */
    public const NAME_ID = 3;

    /**
     * Full font name. Usually a combination of the {@link NAME_FAMILY} and
     * {@link NAME_STYLE} strings.
     */
    public const NAME_FULL = 4;

    /**
     * Version number of the font.
     */
    public const NAME_VERSION = 5;

    /**
     * PostScript name for the font. This is the name used to identify fonts
     * internally and within the PDF file.
     */
    public const NAME_POSTSCRIPT = 6;

    /**
     * Font trademark notice. This is distinct from the {@link NAME_COPYRIGHT}.
     */
    public const NAME_TRADEMARK = 7;

    /**
     * Name of the font manufacturer.
     */
    public const NAME_MANUFACTURER = 8;

    /**
     * Name of the designer of the font.
     */
    public const NAME_DESIGNER = 9;

    /**
     * Description of the font. May contain revision information, usage
     * recommendations, features, etc.
     */
    public const NAME_DESCRIPTION = 10;

    /**
     * URL of the font vendor. Some fonts may contain a unique serial number
     * embedded in this URL, which is used for licensing.
     */
    public const NAME_VENDOR_URL = 11;

    /**
     * URL of the font designer ({@link NAME_DESIGNER}).
     */
    public const NAME_DESIGNER_URL = 12;

    /**
     * Plain language licensing terms for the font.
     */
    public const NAME_LICENSE = 13;

    /**
     * URL of more detailed licensing information for the font.
     */
    public const NAME_LICENSE_URL = 14;

    /**
     * Preferred font family. Used by some fonts to work around a Microsoft
     * Windows limitation where only four fonts styles can share the same
     * {@link NAME_FAMILY} value.
     */
    public const NAME_PREFERRED_FAMILY = 16;

    /**
     * Preferred font style. A more descriptive string than {@link NAME_STYLE}.
     */
    public const NAME_PREFERRED_STYLE = 17;

    /**
     * Suggested text to use as a representative sample of the font.
     */
    public const NAME_SAMPLE_TEXT = 19;

    /**
     * PostScript CID findfont name.
     */
    public const NAME_CID_NAME = 20;


    /* Font Weights */

    /**
     * Thin font weight.
     */
    public const WEIGHT_THIN = 100;

    /**
     * Extra-light (Ultra-light) font weight.
     */
    public const WEIGHT_EXTRA_LIGHT = 200;

    /**
     * Light font weight.
     */
    public const WEIGHT_LIGHT = 300;

    /**
     * Normal (Regular) font weight.
     */
    public const WEIGHT_NORMAL = 400;

    /**
     * Medium font weight.
     */
    public const WEIGHT_MEDIUM = 500;

    /**
     * Semi-bold (Demi-bold) font weight.
     */
    public const WEIGHT_SEMI_BOLD = 600;

    /**
     * Bold font weight.
     */
    public const WEIGHT_BOLD = 700;

    /**
     * Extra-bold (Ultra-bold) font weight.
     */
    public const WEIGHT_EXTRA_BOLD = 800;

    /**
     * Black (Heavy) font weight.
     */
    public const WEIGHT_BLACK = 900;


    /* Font Widths */

    /**
     * Ultra-condensed font width. Typically 50% of normal.
     */
    public const WIDTH_ULTRA_CONDENSED = 1;

    /**
     * Extra-condensed font width. Typically 62.5% of normal.
     */
    public const WIDTH_EXTRA_CONDENSED = 2;

    /**
     * Condensed font width. Typically 75% of normal.
     */
    public const WIDTH_CONDENSED = 3;

    /**
     * Semi-condensed font width. Typically 87.5% of normal.
     */
    public const WIDTH_SEMI_CONDENSED = 4;

    /**
     * Normal (Medium) font width.
     */
    public const WIDTH_NORMAL = 5;

    /**
     * Semi-expanded font width. Typically 112.5% of normal.
     */
    public const WIDTH_SEMI_EXPANDED = 6;

    /**
     * Expanded font width. Typically 125% of normal.
     */
    public const WIDTH_EXPANDED = 7;

    /**
     * Extra-expanded font width. Typically 150% of normal.
     */
    public const WIDTH_EXTRA_EXPANDED = 8;

    /**
     * Ultra-expanded font width. Typically 200% of normal.
     */
    public const WIDTH_ULTRA_EXPANDED = 9;


    /* Font Embedding Options */

    /**
     * Do not embed the font in the PDF document.
     */
    public const EMBED_DONT_EMBED = 0x01;

    /**
     * Embed, but do not subset the font in the PDF document.
     */
    public const EMBED_DONT_SUBSET = 0x02;

    /**
     * Embed, but do not compress the font in the PDF document.
     */
    public const EMBED_DONT_COMPRESS = 0x04;

    /**
     * Suppress the exception normally thrown if the font cannot be embedded
     * due to its copyright bits being set.
     */
    public const EMBED_SUPPRESS_EMBED_EXCEPTION = 0x08;


    /**** Class Variables ****/


    /**
     * Array whose keys are the unique PostScript names of instantiated fonts.
     * The values are the font objects themselves.
     * @var array
     */
    private static $_fontNames = [];

    /**
     * Array whose keys are the md5 hash of the full paths on disk for parsed
     * fonts. The values are the font objects themselves.
     * @var array
     */
    private static $_fontFilePaths = [];


    /**** Public Interface ****/


    /* Factory Methods */

    /**
     * Returns a {@link \LaminasPdf\Resource\Font\AbstractFont} object by full name.
     *
     * This is the preferred method to obtain one of the standard 14 PDF fonts.
     *
     * The result of this method is cached, preventing unnecessary duplication
     * of font objects. Repetitive calls for a font with the same name will
     * return the same object.
     *
     * The $embeddingOptions parameter allows you to set certain flags related
     * to font embedding. You may combine options by OR-ing them together. See
     * the EMBED_ constants defined in {@link \LaminasPdf\Font} for the list of
     * available options and their descriptions. Note that this value is only
     * used when creating a font for the first time. If a font with the same
     * name already exists, you will get that object and the options you specify
     * here will be ignored. This is because fonts are only embedded within the
     * PDF file once.
     *
     * If the font name supplied does not match the name of a previously
     * instantiated object and it is not one of the 14 standard PDF fonts, an
     * exception will be thrown.
     *
     * @param string $name Full PostScript name of font.
     * @param integer $embeddingOptions (optional) Options for font embedding.
     * @return \LaminasPdf\Resource\Font\AbstractFont
     * @throws \LaminasPdf\Exception\ExceptionInterface
     */
    public static function fontWithName($name, $embeddingOptions = 0)
    {
        /* First check the cache. Don't duplicate font objects.
         */
        if (isset(self::$_fontNames[$name])) {
            return self::$_fontNames[$name];
        }

        /**
         * @todo It would be cool to be able to have a mapping of font names to
         *   file paths in a configuration file for frequently used custom
         *   fonts. This would allow a user to use custom fonts without having
         *   to hard-code file paths all over the place. Table this idea until
         *   {@link \Traversable} is ready.
         */

        /* Not an existing font and no mapping in the config file. Check to see
         * if this is one of the standard 14 PDF fonts.
         */
        switch ($name) {
            case self::FONT_COURIER:
                $font = new Resource\Font\Simple\Standard\Courier();
                break;

            case self::FONT_COURIER_BOLD:
                $font = new Resource\Font\Simple\Standard\CourierBold();
                break;

            case self::FONT_COURIER_OBLIQUE:
                $font = new Resource\Font\Simple\Standard\CourierOblique();
                break;

            case self::FONT_COURIER_BOLD_OBLIQUE:
                $font = new Resource\Font\Simple\Standard\CourierBoldOblique();
                break;

            case self::FONT_HELVETICA:
                $font = new Resource\Font\Simple\Standard\Helvetica();
                break;

            case self::FONT_HELVETICA_BOLD:
                $font = new Resource\Font\Simple\Standard\HelveticaBold();
                break;

            case self::FONT_HELVETICA_OBLIQUE:
                $font = new Resource\Font\Simple\Standard\HelveticaOblique();
                break;

            case self::FONT_HELVETICA_BOLD_OBLIQUE:
                $font = new Resource\Font\Simple\Standard\HelveticaBoldOblique();
                break;

            case self::FONT_SYMBOL:
                $font = new Resource\Font\Simple\Standard\Symbol();
                break;

            case self::FONT_TIMES_ROMAN:
                $font = new Resource\Font\Simple\Standard\TimesRoman();
                break;

            case self::FONT_TIMES_BOLD:
                $font = new Resource\Font\Simple\Standard\TimesBold();
                break;

            case self::FONT_TIMES_ITALIC:
                $font = new Resource\Font\Simple\Standard\TimesItalic();
                break;

            case self::FONT_TIMES_BOLD_ITALIC:
                $font = new Resource\Font\Simple\Standard\TimesBoldItalic();
                break;

            case self::FONT_ZAPFDINGBATS:
                $font = new Resource\Font\Simple\Standard\ZapfDingbats();
                break;

            default:
                throw new Exception\InvalidArgumentException("Unknown font name: $name");
        }

        /* Add this new font to the cache array and return it for use.
         */
        self::$_fontNames[$name] = $font;
        return $font;
    }

    /**
     * Returns a {@link \LaminasPdf\Resource\Font\AbstractFont} object by file path.
     *
     * The result of this method is cached, preventing unnecessary duplication
     * of font objects. Repetitive calls for the font with the same path will
     * return the same object.
     *
     * The $embeddingOptions parameter allows you to set certain flags related
     * to font embedding. You may combine options by OR-ing them together. See
     * the EMBED_ constants defined in {@link \LaminasPdf\Font} for the list of
     * available options and their descriptions. Note that this value is only
     * used when creating a font for the first time. If a font with the same
     * name already exists, you will get that object and the options you specify
     * here will be ignored. This is because fonts are only embedded within the
     * PDF file once.
     *
     * If the file path supplied does not match the path of a previously
     * instantiated object or the font type cannot be determined, an exception
     * will be thrown.
     *
     * @param string $filePath Full path to the font file.
     * @param integer $embeddingOptions (optional) Options for font embedding.
     * @return \LaminasPdf\Resource\Font\AbstractFont
     * @throws \LaminasPdf\Exception\ExceptionInterface
     */
    public static function fontWithPath($filePath, $embeddingOptions = 0)
    {
        /* First check the cache. Don't duplicate font objects.
         */
        $filePathKey = md5($filePath);
        if (isset(self::$_fontFilePaths[$filePathKey])) {
            return self::$_fontFilePaths[$filePathKey];
        }

        /* Create a file parser data source object for this file. File path and
         * access permission checks are handled here.
         */
        $dataSource = new BinaryParser\DataSource\File($filePath);

        /* Attempt to determine the type of font. We can't always trust file
         * extensions, but try that first since it's fastest.
         */
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        /* If it turns out that the file is named improperly and we guess the
         * wrong type, we'll get null instead of a font object.
         */
        switch ($fileExtension) {
            case 'ttf':
                $font = self::_extractTrueTypeFont($dataSource, $embeddingOptions);
                break;

            default:
                /* Unrecognized extension. Try to determine the type by actually
                 * parsing it below.
                 */
                $font = null;
                break;
        }


        if ($font === null) {
            /* There was no match for the file extension or the extension was
             * wrong. Attempt to detect the type of font by actually parsing it.
             * We'll do the checks in order of most likely format to try to
             * reduce the detection time.
             */

            // OpenType

            // TrueType
            if (($font === null) && ($fileExtension != 'ttf')) {
                $font = self::_extractTrueTypeFont($dataSource, $embeddingOptions);
            }

            // Type 1 PostScript

            // Mac OS X dfont

            // others?
        }


        /* Done with the data source object.
         */
        $dataSource = null;

        if ($font !== null) {
            /* Parsing was successful. Add this font instance to the cache arrays
             * and return it for use.
             */
            $fontName = $font->getFontName(self::NAME_POSTSCRIPT, '', '');
            self::$_fontNames[$fontName] = $font;
            $filePathKey = md5($filePath);
            self::$_fontFilePaths[$filePathKey] = $font;
            return $font;
        } else {
            /* The type of font could not be determined. Give up.
             */
            throw new Exception\DomainException("Cannot determine font type: $filePath");
        }
    }



    /**** Internal Methods ****/


    /* Font Extraction Methods */

    /**
     * Attempts to extract a TrueType font from the data source.
     *
     * If the font parser throws an exception that suggests the data source
     * simply doesn't contain a TrueType font, catches it and returns null. If
     * an exception is thrown that suggests the TrueType font is corrupt or
     * otherwise unusable, throws that exception. If successful, returns the
     * font object.
     *
     * @param \LaminasPdf\BinaryParser\DataSource\AbstractDataSource $dataSource
     * @param integer $embeddingOptions Options for font embedding.
     * @return \LaminasPdf\Resource\Font\OpenType\TrueType May also return null if
     *   the data source does not appear to contain a TrueType font.
     * @throws \LaminasPdf\Exception\ExceptionInterface
     */
    protected static function _extractTrueTypeFont($dataSource, $embeddingOptions)
    {
        try {
            $fontParser = new BinaryParser\Font\OpenType\TrueType($dataSource);

            $fontParser->parse();
            if ($fontParser->isAdobeLatinSubset) {
                $font = new Resource\Font\Simple\Parsed\TrueType($fontParser, $embeddingOptions);
            } else {
                /* Use Composite Type 0 font which supports Unicode character mapping */
                $cidFont = new Resource\Font\CidFont\TrueType($fontParser, $embeddingOptions);
                $font = new Resource\Font\Type0($cidFont);
            }
        } catch (Exception\UnrecognizedFontException $e) {
            /**
             * This exception suggests that this isn't really a TrueType font.
             * If we caught such an exception, simply return null.
             */
            return null;
        }

        return $font;
    }
}
