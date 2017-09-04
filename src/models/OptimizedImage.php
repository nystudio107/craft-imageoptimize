<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\models;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImage extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $optimizedImageUrls = [];

    /**
     * @var array
     */
    public $optimizedWebPImageUrls = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['optimizedImageUrls', ArrayValidator::class],
            ['optimizedWebPImageUrls', ArrayValidator::class],
        ];
    }

    /**
     * Return a string of image URLs and their sizes
     *
     * @return string
     */
    public function srcset(): string
    {
        return $this->getSrcsetFromArray($this->optimizedImageUrls);
    }

    /**
     * @return string
     */
    public function srcsetWebp(): string
    {
        return $this->getSrcsetFromArray($this->optimizedWebPImageUrls);
    }

    /**
     * Return a base64-encoded placeholder image
     *
     * @param int    $width
     * @param int    $height
     * @param string $color
     *
     * @return string
     */
    public function placeholderImage($width = 1, $height = 1, $color = '#CCC')
    {
        $header = 'data:image/svg+xml;charset=utf-8,';
        $content = "<svg xmlns='http://www.w3.org/2000/svg' width='$width' height='$height' style='background:$color'/>";

        return $header . rawurlencode($content);
    }

    /**
     * @param $url
     *
     * @return int|string
     */
    public function curlGetFileSize($url)
    {
        // Assume failure.
        $result = -1;

        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $data = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($data) {
            $content_length = "unknown";

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                $content_length = (int)$matches[1];
            }

            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if ($status == 200 || ($status > 300 && $status <= 308)) {
                if ($content_length != "unknown") {
                    $result = ImageOptimize::$plugin->optimize->humanFileSize($content_length, 1);
                }
            }
        }

        return $result;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param array $array
     *
     * @return string
     */
    protected function getSrcsetFromArray(array $array): string
    {
        $srcset = '';
        foreach ($array as $key => $value) {
            $srcset .= $value . ' ' . $key . 'w, ';
        }
        $srcset = rtrim($srcset, ', ');

        return $srcset;
    }
}
