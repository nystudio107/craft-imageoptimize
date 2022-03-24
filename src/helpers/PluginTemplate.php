<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2021 nystudio107
 */

namespace nystudio107\imageoptimize\helpers;

use Craft;
use craft\helpers\Template;
use craft\web\View;
use nystudio107\minify\Minify;
use yii\base\Exception;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.7.0
 */
class PluginTemplate
{
    // Constants
    // =========================================================================

    public const MINIFY_PLUGIN_HANDLE = 'minify';

    // Static Methods
    // =========================================================================

    public static function renderStringTemplate(string $templateString, array $params = []): string
    {
        try {
            $html = Craft::$app->getView()->renderString($templateString, $params);
        } catch (\Exception $e) {
            $html = Craft::t(
                'image-optimize',
                'Error rendering template string -> {error}',
                ['error' => $e->getMessage()]
            );
            Craft::error($html, __METHOD__);
        }

        return $html;
    }

    /**
     * Render a plugin template
     *
     * @param string $templatePath
     * @param array $params
     * @param string|null $minifier
     *
     * @return string
     */
    public static function renderPluginTemplate(
        string $templatePath,
        array  $params = [],
        string $minifier = null
    ): string
    {
        $template = 'image-optimize/' . $templatePath;
        $oldMode = Craft::$app->view->getTemplateMode();
        // Look for the template on the frontend first
        try {
            $templateMode = View::TEMPLATE_MODE_CP;
            if (Craft::$app->view->doesTemplateExist($template, View::TEMPLATE_MODE_SITE)) {
                $templateMode = View::TEMPLATE_MODE_SITE;
            }
            Craft::$app->view->setTemplateMode($templateMode);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        // Render the template with our vars passed in
        try {
            $htmlText = Craft::$app->view->renderTemplate($template, $params);
            if ($minifier) {
                // If Minify is installed, use it to minify the template
                $minify = Craft::$app->getPlugins()->getPlugin(self::MINIFY_PLUGIN_HANDLE);
                if ($minify) {
                    $htmlText = Minify::$plugin->minify->$minifier($htmlText);
                }

            }
        } catch (\Exception $e) {
            $htmlText = Craft::t(
                'image-optimize',
                'Error rendering `{template}` -> {error}',
                ['template' => $templatePath, 'error' => $e->getMessage()]
            );
            Craft::error($htmlText, __METHOD__);
        }

        // Restore the old template mode
        try {
            Craft::$app->view->setTemplateMode($oldMode);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return Template::raw($htmlText);
    }
}
