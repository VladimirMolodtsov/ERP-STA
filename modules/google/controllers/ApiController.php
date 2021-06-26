<?php

namespace app\modules\google\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\google\models\DiskApi;
//use app\modules\yandex\models\ChkDisk;

/**
 * Default controller for the `yandex` module
 * Operations with storage
 */
class ApiController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */

     
    public function actionIndex()
    {

        $model= new DiskApi();
        $model ->gtest();
    }


    public function actionScanDisk()
    {

        $model= new DiskApi();
        $sendArray= $model ->scanDisk();
        if(Yii::$app->request->isAjax)
        {                
                echo json_encode($sendArray);
                return;         
        }
        print_r($sendArray);
    }


    
/*******************************************/
/********* Service  ************************/
/*******************************************/


    
}
