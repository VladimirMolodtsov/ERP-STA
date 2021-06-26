<?php

namespace app\modules\market\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//use yii\web\UploadedFile;

use app\modules\market\models\MainPlan;
use app\modules\market\models\OrgSelect;
/**
 * Default controller for the `bank` module
 */
class PlanController extends Controller
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


      $model = new MainPlan();

      $model ->prepareCurrentMonth();
      //$provider = $model->getTaskTemplateListProvider(Yii::$app->request->get());
      //return $this->render('index', ['model' => $model, 'provider' => $provider,  ] );
    }


/****/

    public function actionOrgSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new OrgSelect();
      $request = Yii::$app->request;
      $model->refManager = intval($request->get('id',0));    
      $model->userId = intval($request->get('id',0));    // а это, чтобы правильно вернуть
      $provider = $model->getOrgSelectProvider(Yii::$app->request->get());    
          return $this->render('org-select', ['model' => $model, 'provider' => $provider,]);
    }

/*********************************************/    
    public function actionRemoveTask()
    {
     $request = Yii::$app->request;     
     $model = new TasksAcceptForm();
     $model->id = intval($request->get('id',0));                  
     $model->removeCurrentTask();
     $this->redirect(['/site/success']);
     
    }

/*********************************************/    
    public function actionMarketTaskAccept()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
     $request = Yii::$app->request;     
     $model = new TasksAcceptForm();
      
      if ($model->load(Yii::$app->request->post()) && $model->validate())
      {
         $model->acceptMarketTask();
         $this->redirect(['/site/success']);
         return;
      }
         $model->id = intval($request->get('id',0));                  
         $model->dt = $request->get('dt',0);                  
         $model->tm = $request->get('tm',0);                  
         return $this->render('market-task-accept', ['model' => $model]);
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
