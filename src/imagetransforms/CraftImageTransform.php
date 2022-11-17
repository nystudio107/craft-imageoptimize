<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use Craft;
use craft\elements\Asset;
use craft\models\ImageTransform as CraftImageTransformModel;
use nystudio107\imageoptimize\ImageOptimize;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.0
 */
class CraftImageTransform extends ImageTransform
{
    // Static Methods
    // =========================================================================

    /**
     * @var bool
     */
    public bool $generateTransformsBeforePageLoad;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'Craft');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get our $generateTransformsBeforePageLoad setting
        $this->generateTransformsBeforePageLoad = $settings->generateTransformsBeforePageLoad ?? true;
    }

    /**
     * @inheritDoc
     */
    public function getTransformUrl(Asset $asset, CraftImageTransformModel|string|array|null $transform): ?string
    {
        // Generate the URLs to the optimized images
        $oldValue = Craft::$app->getConfig()->getGeneral()->generateTransformsBeforePageLoad;

        if ($this->generateTransformsBeforePageLoad) {
            Craft::$app->getConfig()->getGeneral()->generateTransformsBeforePageLoad = true;
        }
        $url = $asset->getUrl($transform);
        Craft::$app->getConfig()->getGeneral()->generateTransformsBeforePageLoad = $oldValue;

        return $url;
    }

    /**
     * @inheritDoc
     */
    public function getWebPUrl(string $url, Asset $asset, CraftImageTransformModel|string|array|null $transform): ?string
    {
        return $this->appendExtension($url, '.webp');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
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
     * No savable fields for this component
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        return array_merge($rules, [
        ]);
    }
}
