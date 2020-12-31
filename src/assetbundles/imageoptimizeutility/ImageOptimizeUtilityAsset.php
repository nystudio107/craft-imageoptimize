<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2020 nystudio107
 */

namespace nystudio107\imageoptimize\assetbundles\imageoptimizeutility;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ImageOptimizeUtilityAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@nystudio107/imageoptimize/assetbundles/imageoptimizeutility/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ImageOptimizeUtility.js',
        ];

        $this->css = [
            'css/ImageOptimizeUtility.css',
        ];

        parent::init();
    }
}
