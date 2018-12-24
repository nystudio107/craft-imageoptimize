<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\validators;

use Craft;

use yii\base\Model;
use yii\validators\Validator;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.5.0
 */
class EmbeddedModelValidator extends Validator
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /** @var Model $model */
        $value = $model->$attribute;

        if (!empty($value) && \is_object($value) && $value instanceof Model) {
            /** @var Model $value */
            if (!$value->validate()) {
                $errors = $value->getErrors();
                foreach ($errors as $attributeError => $valueErrors) {
                    /** @var array $valueErrors */
                    foreach ($valueErrors as $valueError) {
                        $model->addError(
                            $attribute,
                            Craft::t('image-optimize', 'Object failed to validate')
                            .'-'.$attributeError.' - '.$valueError
                        );
                    }
                }
            }
        } else {
            $model->addError($attribute, Craft::t('image-optimize', 'Is not a Model object.'));
        }
    }
}
