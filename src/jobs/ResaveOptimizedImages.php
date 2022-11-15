<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\jobs;

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\fields\OptimizedImages as OptimizedImagesField;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\console\Application as ConsoleApplication;
use craft\db\Paginator;
use craft\elements\Asset;
use craft\elements\db\ElementQuery;
use craft\helpers\App;
use craft\queue\BaseJob;

use yii\base\Exception;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.4.8
 */
class ResaveOptimizedImages extends BaseJob
{
    // Constants
    // =========================================================================

    /**
     * @const The number of assets to return in a single paginated query
     */
    const ASSET_QUERY_PAGE_SIZE = 100;

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
        if (ImageOptimize::$craft35) {
            Craft::$app->getElements()->invalidateCachesForElementType(Asset::class);
        } else {
            Craft::$app->getTemplateCaches()->deleteCachesByElementType(Asset::class);
        }

        // Now find the affected element IDs
        /** @var ElementQuery $query */
        $query = Asset::find();
        if (!empty($this->criteria)) {
            Craft::configure($query, $this->criteria);
        }
        if (Craft::$app instanceof ConsoleApplication) {
            echo $this->description . PHP_EOL;
        }
        // Use craft\db\Paginator to paginate the results so we don't exceed any memory limits
        // See batch() and each() discussion here: https://github.com/yiisoft/yii2/issues/8420
        // and here: https://github.com/craftcms/cms/issues/7338
        $paginator = new Paginator($query, [
            'pageSize' => self::ASSET_QUERY_PAGE_SIZE,
        ]);
        $currentElement = 0;
        $totalElements = $paginator->getTotalResults();
        // Iterate through the paginated results
        while ($currentElement < $totalElements) {
            $elements = $paginator->getPageResults();
            if (Craft::$app instanceof ConsoleApplication) {
                echo 'Query ' . $paginator->getCurrentPage() . '/' . $paginator->getTotalPages()
                    . ' - assets: ' . $paginator->getTotalResults()
                    . PHP_EOL;
            }
            /** @var ElementInterface $element */
            foreach ($elements as $element) {
                $currentElement++;
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
                $this->setProgress($queue, $currentElement / $totalElements);
            }
            $paginator->currentPage++;
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
