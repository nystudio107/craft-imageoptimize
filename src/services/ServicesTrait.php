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

use craft\helpers\ArrayHelper;
use nystudio107\imageoptimize\assetbundles\imageoptimize\ImageOptimizeAsset;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;
use nystudio107\imageoptimize\services\OptimizedImages as OptimizedImagesService;
use nystudio107\imageoptimize\services\Placeholder as PlaceholderService;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.49
 *
 * @property OptimizeService $optimize
 * @property PlaceholderService $placeholder
 * @property OptimizedImagesService $optimizedImages
 * @property VitePluginService $vite
 */
trait ServicesTrait
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Constants aren't allowed in traits until PHP >= 8.2
        $majorVersion = '1';
        // Dev server container name & port are based on the major version of this plugin
        $devPort = 3000 + (int)$majorVersion;
        $versionName = 'v' . $majorVersion;
        // Merge in the passed config, so it our config can be overridden by Plugins::pluginConfigs['vite']
        // ref: https://github.com/craftcms/cms/issues/1989
        $config = ArrayHelper::merge([
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
        ], $config);

        parent::__construct($id, $parent, $config);
    }

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
