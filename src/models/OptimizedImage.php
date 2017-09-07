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

    /**
     * @var array
     */
    public $focalPoint;

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
            ['focalPoint', 'safe'],
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
     *  Get the file size of any remote resource (using curl),
     *  either in bytes or - default - as human-readable formatted string.
     *
     *  @author  Stephan Schmitz <eyecatchup@gmail.com>
     *  @license MIT <http://eyecatchup.mit-license.org/>
     *  @url     <https://gist.github.com/eyecatchup/f26300ffd7e50a92bc4d>
     *
     * @param   string  $url        Takes the remote object's URL.
     * @param   boolean $formatSize Whether to return size in bytes or
     *                              formatted.
     * @param   boolean $useHead    Whether to use HEAD requests. If false,
     *                              uses GET.
     *
     * @return  int|mixed|string    Returns human-readable formatted size
     *                              or size in bytes (default: formatted).
     */
    public function getRemoteFileSize($url, $formatSize = true, $useHead = true)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY         => 1,
        ]);
        if (false !== $useHead) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_exec($ch);
        // content-length of download (in bytes), read from Content-Length: field
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        // cannot retrieve file size, return "-1"
        if (!$contentLength) {
            return -1;
        }
        // return size in bytes
        if (!$formatSize) {
            return $contentLength;
        }

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
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
