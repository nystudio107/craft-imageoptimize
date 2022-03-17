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
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        /** @var OptimizedImage $source */
        $fieldName = $resolveInfo->fieldName;

        switch ($fieldName) {
            // Special-case the `src` field with arguments
            case 'src':
                $width = $arguments['width'] ?? 0;

                return $source->src($width);
                break;

            // Special-case the `srcWebp` field with arguments
            case 'srcWebp':
                $width = $arguments['width'] ?? 0;

                return $source->srcWebp($width);
                break;

            // Special-case the `srcset` field with arguments
            case 'srcset':
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcset($dpr);
                break;

            // Special-case the `srcsetMinWidth` field with arguments
            case 'srcsetMinWidth':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcsetMinWidth($width, $dpr);
                break;

            // Special-case the `srcsetMaxWidth` field with arguments
            case 'srcsetMaxWidth':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcsetMaxWidth($width, $dpr);
                break;

            // Special-case the `srcsetWebp` field with arguments
            case 'srcsetWebp':
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcsetWebp($dpr);
                break;

            // Special-case the `srcsetMinWidthWebp` field with arguments
            case 'srcsetMinWidthWebp':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcsetMinWidthWebp($width, $dpr);
                break;

            // Special-case the `srcsetMaxWidthWebp` field with arguments
            case 'srcsetMaxWidthWebp':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;

                return $source->srcsetMaxWidthWebp($width, $dpr);
                break;

            // Special-case the `maxSrcsetWidth` field
            case 'maxSrcsetWidth':
                return $source->maxSrcsetWidth();
                break;

            // Special-case the `placeholderImage` field
            case 'placeholderImage':
                return $source->placeholderImage();
                break;

            // Special-case the `placeholderBox` field
            case 'placeholderBox':
                $color = $arguments['color'] ?? null;

                return $source->placeholderBox($color);
                break;

            // Special-case the `placeholderSilhouette` field
            case 'placeholderSilhouette':
                return $source->placeholderSilhouette();
                break;

            // Special-case the `srcUrls` field
            case 'srcUrls':
                $result = [];
                foreach ($source->optimizedImageUrls as $width => $url) {
                    $result[] = ['width' => $width, 'url' => $url];
                }

                return $result;
                break;

            // Special-case the `colorPaletteRgb` field
            case 'colorPaletteRgb':
                return $source->colorPaletteRgb();
                break;

            // Default to just returning the field value
            default:
                return $source[$fieldName];
                break;
        }
    }
}
