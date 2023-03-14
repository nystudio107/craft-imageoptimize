<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2023 nystudio107
 */

namespace nystudio107\imageoptimize\helpers;

use Craft;
use putyourlightson\blitz\Blitz;

/**
 * ImageOptimize Settings model
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     4.0.5
 */
class Plugin
{
    // Constants
    // =========================================================================

    public const BLITZ_PLUGIN_HANDLE = 'blitz';

    // Public Static Methods
    // =========================================================================

    /**
     * Determine if the Blitz plugin is installed, and if cache warming is enabled
     *
     * @return bool
     */
    public static function blitzWarmingActive(): bool
    {
        /** @var Blitz $blitzPlugin */
        $blitzPlugin = Craft::$app->getPlugins()->getPlugin(self::BLITZ_PLUGIN_HANDLE);
        if (!$blitzPlugin) {
            return false;
        }
        $blitzSettings = $blitzPlugin::$plugin->settings;

        return $blitzSettings->cachingEnabled && $blitzSettings->warmCacheAutomatically;
    }
}
