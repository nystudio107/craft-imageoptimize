<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\assetbundles\ImageOptimize;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class ImageOptimizeAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@nystudio107/imageoptimize/assetbundles/imageoptimize/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ImageOptimize.js',
        ];

        $this->css = [
            'css/ImageOptimize.css',
        ];

        parent::init();
    }
}
