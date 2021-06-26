<?php

namespace app\modules\tasks\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//use yii\web\UploadedFile;

use app\modules\tasks\models\DispetcherForm;

/**
 * Default controller for the `bank` module
 */
class DispetcherController extends Controller
{

   /* public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => '@app/views/site/custom-error-view.php'
            ],
        ];
    }
    */
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }


      $model = new DispetcherForm();

      return $this->render('index', ['model' => $model,  ] );
    }


/*******************************************/

/*******************************************/
/********* Service  ************************/
/*******************************************/



}
