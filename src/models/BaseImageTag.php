<?php
/**
 * Image Optimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) nystudio107
 */

namespace nystudio107\imageoptimize\models;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     5.0.0-beta.1
 */
abstract class BaseImageTag extends BaseTag
{
    /**
     * Swap the tag attributes to work with lazy loading
     * ref: https://web.dev/native-lazy-loading/#how-do-i-handle-browsers-that-don't-yet-support-native-lazy-loading
     *
     * @param string $loading 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     * @param string $placeHolder 'box', 'color', 'image', 'silhouette'
     * @param array $attrs
     *
     * @return array
     */
    protected function swapLazyLoadAttrs(string $loading, string $placeHolder, array $attrs): array
    {
        // Set the class and loading attributes
        if (isset($attrs['class'])) {
            $attrs['class'] = trim($attrs['class'] . ' lazyload');
        }
        // Set the style on this element to be the placeholder image as the background-image
        if (isset($attrs['style']) && !empty($attrs['src'])) {
            if (empty($attrs['style'])) {
                $attrs['style'] = [];
            }
            // If the original image is an SVG or gif, don't add the placeholder box CSS so that transparency works as intended
            $path = parse_url($attrs['src'], PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension !== 'svg' && $extension !== 'gif') {
                $attrs['style']['background-image'] = 'url(' . $this->getLazyLoadSrc($placeHolder) . ')';
                $attrs['style']['background-size'] = 'cover';
            }
        }
        // Handle attributes that lazy  and lazySizesFallback have in common
        switch ($loading) {
            case 'lazy':
            case 'lazySizesFallback':
                if (isset($attrs['loading'])) {
                    $attrs['loading'] = 'lazy';
                }
                break;
            default:
                break;
        }
        // Handle attributes that lazySizes and lazySizesFallback have in common
        switch ($loading) {
            case 'lazySizes':
            case 'lazySizesFallback':
                // Only swap to data- attributes if they want the LazySizes fallback
                if (!empty($attrs['sizes'])) {
                    $attrs['data-sizes'] = $attrs['sizes'];
                    $attrs['sizes'] = '';
                }
                if (!empty($attrs['srcset'])) {
                    $attrs['data-srcset'] = $attrs['srcset'];
                    $attrs['srcset'] = '';
                }
                if (!empty($attrs['src'])) {
                    $attrs['data-src'] = $attrs['src'];
                    $attrs['src'] = $this->getLazyLoadSrc($placeHolder);
                }
                break;
            default:
                break;
        }

        return $attrs;
    }

    /**
     * Return a lazy loading placeholder image based on the passed in $lazyload setting
     *
     * @param string $lazyLoad
     *
     * @return string
     */
    protected function getLazyLoadSrc(string $lazyLoad): string
    {
        $lazyLoad = strtolower($lazyLoad);
        return match ($lazyLoad) {
            'image' => $this->optimizedImage->getPlaceholderImage(),
            'silhouette' => $this->optimizedImage->getPlaceholderSilhouette(),
            'color' => $this->optimizedImage->getPlaceholderBox($this->colorPalette[0] ?? null),
            default => $this->optimizedImage->getPlaceholderBox('#CCC'),
        };
    }
}
