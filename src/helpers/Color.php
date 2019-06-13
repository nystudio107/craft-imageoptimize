<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\helpers;

/**
 * ImageOptimize Settings model
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.5.7
 */
class Color
{
    // Public Static Properties
    // =========================================================================

    // Public Static Methods
    // =========================================================================

    /**
     * Convert a HTML color (e.g. #FFFFFF or #FFF) to an array of RGB colors
     *
     * @param string $htmlCode
     *
     * @return array
     */
    public static function HTMLToRGB(string $htmlCode): array
    {
        if (strpos($htmlCode, '#') === 0) {
            $htmlCode = substr($htmlCode, 1);
        }

        if (strlen($htmlCode) === 3) {
            $htmlCode = $htmlCode[0].$htmlCode[0].$htmlCode[1].$htmlCode[1].$htmlCode[2].$htmlCode[2];
        }

        $r = hexdec($htmlCode[0].$htmlCode[1]);
        $g = hexdec($htmlCode[2].$htmlCode[3]);
        $b = hexdec($htmlCode[4].$htmlCode[5]);

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    /**
     * Convert an RGB color array to a HSL color array
     *
     * @param array $rgb
     *
     * @return array
     */
    public static function RGBToHSL(array $rgb): array
    {
        $r = ((float)$rgb['r']) / 255.0;
        $g = ((float)$rgb['g']) / 255.0;
        $b = ((float)$rgb['b']) / 255.0;

        $maxC = max($r, $g, $b);
        $minC = min($r, $g, $b);

        $l = ($maxC + $minC) / 2.0;

        $s = 0;
        $h = 0;
        if ($maxC !== $minC) {
            if ($l < .5) {
                $s = ($maxC - $minC) / ($maxC + $minC);
            } else {
                $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
            }
            if ($r === $maxC) {
                $h = ($g - $b) / ($maxC - $minC);
            }
            if ($g === $maxC) {
                $h = 2.0 + ($b - $r) / ($maxC - $minC);
            }
            if ($b === $maxC) {
                $h = 4.0 + ($r - $g) / ($maxC - $minC);
            }

            $h /= 6.0;
        }

        $h = (int)round(360.0 * $h);
        $s = (int)round(100.0 * $s);
        $l = (int)round(100.0 * $l);

        return ['h' => $h, 's' => $s, 'l' => $l];
    }
}
