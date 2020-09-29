<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\jobs;

use nystudio107\imageoptimize\fields\OptimizedImages as OptimizedImagesField;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\console\Application as ConsoleApplication;
use craft\elements\Asset;
use craft\elements\db\ElementQuery;
use craft\helpers\App;
use craft\queue\BaseJob;

use nystudio107\imageoptimize\ImageOptimize;
use yii\base\Exception;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.4.8
 */
class ResaveOptimizedImages extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var array|null The element criteria that determines which elements should be resaved
     */
    public $criteria;

    /**
     * @var int|null The id of the field to resave images for, or null for all images
     */
    public $fieldId;

    /**
     * @var bool Whether image variants should be forced to recreated, even if they already exist on disk
     * @since 1.6.18
     */
    public $force = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Let's save ourselves some trouble and just clear all the caches for this element class
        Craft::$app->getTemplateCaches()->deleteCachesByElementType(Asset::class);

        // Now find the affected element IDs
        /** @var ElementQuery $query */
        $query = Asset::find();
        if (!empty($this->criteria)) {
            Craft::configure($query, $this->criteria);
        }
        $query
            ->offset(null)
            ->limit(null)
            ->orderBy(null);

        if (Craft::$app instanceof ConsoleApplication) {
            echo $this->description.PHP_EOL;
        }

        $totalElements = $query->count();
        $currentElement = 0;

        /** @var ElementInterface $element */
        foreach ($query->each() as $element) {
            // Find each OptimizedImages field and process it
            $layout = $element->getFieldLayout();
            if ($layout !== null) {
                $fields = $layout->getFields();
                /** @var  $field Field */
                foreach ($fields as $field) {
                    if ($field instanceof OptimizedImagesField && $element instanceof Asset) {
                        if ($this->fieldId === null || $field->id == $this->fieldId) {
                            if (Craft::$app instanceof ConsoleApplication) {
                                echo $currentElement . '/' . $totalElements
                                    . ' - processing asset: ' . $element->title
                                    . ' from field: ' . $field->name . PHP_EOL;
                            }
                            try {
                                ImageOptimize::$plugin->optimizedImages->updateOptimizedImageFieldData($field, $element, $this->force);
                            } catch (Exception $e) {
                                Craft::error($e->getMessage(), __METHOD__);
                                if (Craft::$app instanceof ConsoleApplication) {
                                    echo '[error]: '
                                        . $e->getMessage()
                                        . ' while processing '
                                        . $currentElement . '/' . $totalElements
                                        . ' - processing asset: ' . $element->title
                                        . ' from field: ' . $field->name . PHP_EOL;
                                }
                            }
                        }
                    }
                }
            }
            $this->setProgress($queue, $currentElement++ / $totalElements);
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Resaving {class} elements', [
            'class' => App::humanizeClass(Asset::class),
        ]);
    }
}
