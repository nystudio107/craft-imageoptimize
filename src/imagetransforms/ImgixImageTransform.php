<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use nystudio107\imageoptimize\ImageOptimize;

use craft\elements\Asset;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;

use Imgix\UrlBuilder;
use Psr\Http\Message\ResponseInterface;

use Craft;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ImgixImageTransform extends ImageTransform implements ImageTransformInterface
{
    // Constants
    // =========================================================================

    const TRANSFORM_ATTRIBUTES_MAP = [
        'width'   => 'w',
        'height'  => 'h',
        'quality' => 'q',
        'format'  => 'fm',
    ];

    const IMGIX_PURGE_ENDPOINT = 'https://api.imgix.com/v2/image/purger';

    // Static Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function getTransformUrl(Asset $asset, $transform, array $params = [])
    {
        $url = null;
        $settings = ImageOptimize::$plugin->getSettings();

        $domain = isset($params['domain'])
            ? $params['domain']
            : 'demos.imgix.net';
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            if ($transform) {
                // Map the transform properties
                foreach (self::TRANSFORM_ATTRIBUTES_MAP as $key => $value) {
                    if (!empty($transform[$key])) {
                        $params[$value] = $transform[$key];
                    }
                }
                // Remove any 'AUTO' settings
                ArrayHelper::removeValue($params, 'AUTO');
                // Handle the Imgix auto setting for compression/format
                $autoParams = [];
                if (empty($params['q'])) {
                    $autoParams[] = 'compress';
                }
                if (empty($params['fm'])) {
                    $autoParams[] = 'format';
                }
                if (!empty($autoParams)) {
                    $params['auto'] = implode(',', $autoParams);
                }
                // Handle interlaced images
                if (property_exists($transform, 'interlace')) {
                    if (($transform->interlace != 'none')
                        && (!empty($params['fm']))
                        && ($params['fm'] == 'jpg')
                    ) {
                        $params['fm'] = 'pjpg';
                    }
                }
                if ($settings->autoSharpenScaledImages) {
                    // See if the image has been scaled >= 50%
                    $widthScale = $asset->getWidth() / ($transform->width ?? $asset->getWidth());
                    $heightScale = $asset->getHeight() / ($transform->height ?? $asset->getHeight());
                    if (($widthScale >= 2.0) || ($heightScale >= 2.0)) {
                        $params['usm'] = 50.0;
                    }
                }
                // Handle the mode
                switch ($transform->mode) {
                    case 'fit':
                        $params['fit'] = 'clamp';
                        break;

                    case 'stretch':
                        $params['fit'] = 'scale';
                        break;

                    default:
                        // Fit mode
                        $params['fit'] = 'crop';
                        $cropParams = [];
                        // Handle the focal point
                        $focalPoint = $asset->getFocalPoint();
                        if (!empty($focalPoint)) {
                            $params['fp-x'] = $focalPoint['x'];
                            $params['fp-y'] = $focalPoint['y'];
                            $cropParams[] = 'focalpoint';
                        } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position)) {
                            // Imgix defaults to 'center' if no param is present
                            $filteredCropParams = explode('-', $transform->position);
                            $filteredCropParams = array_diff($filteredCropParams, ['center']);
                            $cropParams[] = $filteredCropParams;
                        }
                        if (!empty($cropParams)) {
                            $params['crop'] = implode(',', $cropParams);
                        }
                        break;
                }
            } else {
                // No transform was passed in; so just auto all the things
                $params['auto'] = 'format,compress';
            }
            // Remove the api-key param
            unset($params['api-key']);
            // Apply the Security Token, if set
            if (!empty($settings->imgixSecurityToken)) {
                $builder->setSignKey($settings->imgixSecurityToken);
            }
            // Finally, create the Imgix URL for this transformed image
            $assetUri = self::getAssetUri($asset);
            $url = $builder->createURL($assetUri, $params);
            Craft::debug(
                'Imgix transform created for: '.$assetUri.' - Params: '.print_r($params, true).' - URL: '.$url,
                __METHOD__
            );
        }

        return $url;
    }

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public static function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string
    {
        $url = preg_replace('/fm=[^&]*/', 'fm=webp', $url);

        return $url;
    }

    /**
     * @param Asset $asset
     * @param array $params
     *
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getPurgeUrl(Asset $asset, array $params = [])
    {
        $url = null;

        $domain = isset($params['domain'])
            ? $params['domain']
            : 'demos.imgix.net';
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            // Create the Imgix URL for purging this image
            $assetUri = self::getAssetUri($asset);
            $url = $builder->createURL($assetUri, $params);
            // Strip the query string so we just pass in the raw URL
            $url = UrlHelper::stripQueryString($url);
        }

        return $url;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public static function purgeUrl(string $url, array $params = []): bool
    {
        $result = false;
        $apiKey = isset($params['api-key'])
            ? $params['api-key']
            : '';
        // create new guzzle client
        $guzzleClient = Craft::createGuzzleClient(['timeout' => 120, 'connect_timeout' => 120]);
        // Submit the sitemap index to each search engine
        try {
            /** @var ResponseInterface $response */
            $response = $guzzleClient->post(self::IMGIX_PURGE_ENDPOINT, [
                'auth'        => [
                    $apiKey,
                    '',
                ],
                'form_params' => [
                    'url' => $url,
                ],
            ]);
            // See if it succeeded
            if (($response->getStatusCode() >= 200)
                && ($response->getStatusCode() < 400)
            ) {
                $result = true;
            }
            Craft::info(
                'URL purged: '.$url.' - Response code: '.$response->getStatusCode(),
                __METHOD__
            );
        } catch (\Exception $e) {
            Craft::error(
                'Error purging URL: '.$url.' - '.$e->getMessage(),
                __METHOD__
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $params = [
            'domain'  => $settings->imgixDomain,
            'api-key' => $settings->imgixApiKey,
        ];

        return $params;
    }
}
