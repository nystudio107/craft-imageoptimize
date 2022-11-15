<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2022 nystudio107
 */

namespace nystudio107\imageoptimize\services;

use nystudio107\imageoptimize\assetbundles\imageoptimize\ImageOptimizeAsset;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;
use nystudio107\imageoptimize\services\OptimizedImages as OptimizedImagesService;
use nystudio107\imageoptimize\services\Placeholder as PlaceholderService;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     4.0.3
 *
 * @property OptimizeService $optimize
 * @property PlaceholderService $placeholder
 * @property OptimizedImagesService $optimizedImages
 * @property VitePluginService $vite
 */
trait ServicesTrait
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'optimize' => OptimizeService::class,
                'optimizedImages' => OptimizedImagesService::class,
                'placeholder' => PlaceholderService::class,
                // Register the vite service
                'vite' => [
                    'class' => VitePluginService::class,
                    'assetClass' => ImageOptimizeAsset::class,
                    'useDevServer' => true,
                    'devServerPublic' => 'http://localhost:3001',
                    'serverPublic' => 'http://localhost:8000',
                    'errorEntry' => 'src/js/ImageOptimize.js',
                    'devServerInternal' => 'http://craft-imageoptimize-buildchain:3001',
                    'checkDevServer' => true,
                ],
            ]
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the optimize service
     *
     * @return OptimizeService The optimize service
     * @throws InvalidConfigException
     */
    public function getOptimize(): OptimizeService
    {
        return $this->get('optimize');
    }

    /**
     * Returns the optimizedImages service
     *
     * @return OptimizedImagesService The optimizedImages service
     * @throws InvalidConfigException
     */
    public function getOptimizedImages(): OptimizedImagesService
    {
        return $this->get('optimizedImages');
    }

    /**
     * Returns the placeholder service
     *
     * @return PlaceholderService The placeholder service
     * @throws InvalidConfigException
     */
    public function getPlaceholder(): PlaceholderService
    {
        return $this->get('placeholder');
    }

    /**
     * Returns the vite service
     *
     * @return VitePluginService The vite service
     * @throws InvalidConfigException
     */
    public function getVite(): VitePluginService
    {
        return $this->get('vite');
    }
}
