<?php

namespace app\modules\cold\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\cold\models\ColdMainForm;
use app\modules\cold\models\ColdImportData;
use app\modules\cold\models\ColdNewContactForm;
use app\modules\cold\models\ColdInitForm;
use app\modules\cold\models\ColdNeedForm;


/**
 * Common controller for the `cold` module
 */
class SiteController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

		return $this->render('index');
    }
 /*******************************************/   
    
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    
}
