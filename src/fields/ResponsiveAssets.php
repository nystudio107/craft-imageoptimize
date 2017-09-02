<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\fields;

use Craft;
use craft\fields\Assets;
use craft\base\ElementInterface;
use craft\helpers\Assets as AssetsHelper;

/**
 * ImageOptimize Settings model
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class ResponsiveAssets extends Assets
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'Responsive Assets');
    }

    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->restrictFiles = true;
        $this->allowedKinds = ['image'];
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

    /**
     * @inheritdoc
     */
    public function getFileKindOptions(): array
    {
        $fileKindOptions = [];

        $fileKinds = AssetsHelper::getFileKinds();
        $imageFileKind = $fileKinds['image'];
        $fileKindOptions[] = [
            'value' => 'image',
            'label' => $imageFileKind['label']
            ];

        return $fileKindOptions;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $html = parent::getInputHtml($value, $element);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        $html .= Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/ResponsiveAssets_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return parent::normalizeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return parent::getSettingsHtml();
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        $variables = parent::inputTemplateVariables($value, $element);

        return $variables;
    }

    // Private Methods
    // =========================================================================
}
