<?php
/**
 * Retour plugin for Craft CMS 3.x
 *
 * Retour allows you to intelligently redirect legacy URLs, so that you don't
 * lose SEO value when rebuilding & restructuring a website
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\controllers;

use craft\helpers\UrlHelper;
use craft\models\Site;
use nystudio107\retour\Retour;
use nystudio107\retour\assetbundles\retour\RetourAsset;
use nystudio107\retour\assetbundles\retour\RetourDashboardAsset;

use Craft;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    nystudio107
 * @package   Retour
 * @since     3.0.0
 */
class CpNavController extends Controller
{
    // Constants
    // =========================================================================

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [
        'resource'
    ];

    // Public Methods
    // =========================================================================

    /**
     * Make webpack async bundle loading work out of published AssetBundles
     *
     * @param string $resourceType
     * @param string $fileName
     *
     * @return Response
     */
    public function actionResource(string $resourceType = '', string $fileName = ''): Response
    {
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            '@nystudio107/imageoptimize/assetbundles/imageoptimize/dist',
            true
        );
        $url = "{$baseAssetsUrl}/{$resourceType}/{$fileName}";

        return $this->redirect($url);
    }
}
