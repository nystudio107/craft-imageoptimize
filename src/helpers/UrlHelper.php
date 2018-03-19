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

use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper as CraftUrlHelper;

use yii\base\Exception;

/**
 * ImageOptimize Settings model
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class UrlHelper extends CraftUrlHelper
{
    // Public Static Properties
    // =========================================================================

    // Public Static Methods
    // =========================================================================

    /**
     * Return an absolute URL with protocol that curl will be happy with
     *
     * @param string $url
     *
     * @return string
     */
    public static function absoluteUrlWithProtocol($url)
    {
        // Make this a full URL
        if (!self::isAbsoluteUrl($url)) {
            $protocol = "http";
            if (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
                || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0
            ) {
                $protocol = "https";
            }
            if (self::isProtocolRelativeUrl($url)) {
                try {
                    $url = self::urlWithScheme($url, $protocol);
                } catch (SiteNotFoundException $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            } else {
                try {
                    $url = self::siteUrl($url, null, $protocol);
                    if (self::isProtocolRelativeUrl($url)) {
                        $url = self::urlWithScheme($url, $protocol);
                    }
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
        }

        return $url;
    }
}
