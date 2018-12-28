<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\elements\Asset;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.5.0
 */
class CraftImageTransform extends ImageTransform
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'Craft');
    }

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     */
    public function getTransformUrl(Asset $asset, $transform, array $params = [])
    {
        $generateTransformsBeforePageLoad = $params['generateTransformsBeforePageLoad'] ?? true;
        // Generate the URLs to the optimized images
        $assets = Craft::$app->getAssets();
        $url = $assets->getAssetUrl($asset, $transform, $generateTransformsBeforePageLoad);

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
    public function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string
    {
        $url = $this->appendExtension($url, '.webp');

        return $url;
    }

    /**
     * @return array
     */
    public function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get our $generateTransformsBeforePageLoad setting
        $generateTransformsBeforePageLoad = $settings->generateTransformsBeforePageLoad ?? true;
        $params = [
            'generateTransformsBeforePageLoad' => $generateTransformsBeforePageLoad,
        ];

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $imageProcessors = ImageOptimize::$plugin->optimize->getActiveImageProcessors();
        $variantCreators = ImageOptimize::$plugin->optimize->getActiveVariantCreators();
        return Craft::$app->getView()->renderTemplate('craft-image-transform/settings/image-transforms/craft.twig', [
            'imageTransform' => $this,
            'imageProcessors' => $imageProcessors,
            'variantCreators' => $variantCreators,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
        ]);

        return $rules;
    }
}
