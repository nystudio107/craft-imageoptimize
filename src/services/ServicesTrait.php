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
        // Constants aren't allowed in traits until PHP >= 8.2, and config() is called before __construct(),
        // so we can't extract it from the passed in $config
        $majorVersion = '4';
        // Dev server container name & port are based on the major version of this plugin
        $devPort = 3000 + (int)$majorVersion;
        $versionName = 'v' . $majorVersion;
        return [
            'components' => [
                'optimize' => OptimizeService::class,
                'optimizedImages' => OptimizedImagesService::class,
                'placeholder' => PlaceholderService::class,
                // Register the vite service
                'vite' => [
                    'assetClass' => ImageOptimizeAsset::class,
                    'checkDevServer' => true,
                    'class' => VitePluginService::class,
                    'devServerInternal' => 'http://craft-imageoptimize-' . $versionName . '-buildchain-dev:' . $devPort,
                    'devServerPublic' => 'http://localhost:' . $devPort,
                    'errorEntry' => 'src/js/ImageOptimize.js',
                    'useDevServer' => true,
                ],
            ],
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
