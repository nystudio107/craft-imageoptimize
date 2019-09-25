<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace nystudio107\imageoptimize\gql\types;

use nystudio107\imageoptimize\models\OptimizedImage;

use craft\gql\base\ObjectType;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.2
 */
class OptimizedImagesType extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        /** @var OptimizedImage $source */
        $fieldName = $resolveInfo->fieldName;

        switch ($fieldName) {
            // Special-case the `src` field with arguments
            case 'src':
                $width = $arguments['width'] ?? 0;

                return $source->src($width);
                break;

            // Special-case the `srcUrls` field
            case 'srcUrls':
                $result = [];
                foreach ($source->optimizedImageUrls as $width => $url) {
                    $result[] = ['width' => $width, 'url' => $url];
                }

                return $result;
                break;

            // Default to just returning the field value
            default:
                return $source[$fieldName];
                break;
        }
    }
}
