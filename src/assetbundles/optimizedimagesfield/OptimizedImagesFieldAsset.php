<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\assetbundles\optimizedimagesfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImagesFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@nystudio107/imageoptimize/assetbundles/optimizedimagesfield/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/OptimizedImages.js',
        ];

        $this->css = [
            'css/OptimizedImages.css',
        ];

        parent::init();
    }
}
