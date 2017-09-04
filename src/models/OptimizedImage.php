<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\models;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImage extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $variants = [
        [
            'width' => 1170,
            'aspectRatioX' => 16.0,
            'aspectRatioY' => 9.0,
            'format' => '',
        ],
        [
            'width' => 970,
            'aspectRatioX' => 16.0,
            'aspectRatioY' => 9.0,
            'format' => '',
        ],
        [
            'width' => 750,
            'aspectRatioX' => 16.0,
            'aspectRatioY' => 9.0,
            'format' => '',
        ],
        [
            'width' => 320,
            'aspectRatioX' => 4.0,
            'aspectRatioY' => 3.0,
            'format' => '',
        ],
    ];
    /**
     * @var array
     */
    public $optimizedImageUrls = [];

    /**
     * @var array
     */
    public $optimizedWebPImageUrls = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
            ['optimizedImageUrls', ArrayValidator::class],
            ['optimizedWebPImageUrls', ArrayValidator::class],
        ];
    }

    /**
     * Return a string of image URLs and their sizes
     *
     * @return string
     */
    public function srcset(): string
    {
        Craft::dd($this);
        return $this->getSrcsetFromArray($this->optimizedImageUrls);
    }

    /**
     * @return string
     */
    public function srcsetWebp(): string
    {
        return $this->getSrcsetFromArray($this->optimizedWebPImageUrls);
    }

    /**
     * Return a base64-encoded placeholder image
     *
     * @param int    $width
     * @param int    $height
     * @param string $color
     *
     * @return string
     */
    public function placeholderImage($width = 1, $height = 1, $color = 'transparent')
    {
        $header = 'data:image/svg+xml;charset=utf-8,';
        $content = "<svg xmlns='http://www.w3.org/2000/svg' width='$width' height='$height' style='background:$color'/>";

        return $header . rawurlencode($content);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param array $array
     *
     * @return string
     */
    protected function getSrcsetFromArray(array $array): string
    {
        $srcset = '';
        foreach ($array as $key => $value) {
            $srcset .= $value . ' ' . $key . 'w, ';
        }
        $srcset = rtrim($srcset, ', ');

        return $srcset;
    }
}
