<?php

namespace app\modules\tasks\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\tasks\models\TasksMain;
use app\modules\tasks\models\TasksEditForm;
use app\modules\tasks\models\OrgSelect;
use app\modules\tasks\models\TasksAcceptForm;
use app\modules\tasks\models\EventRegForm;
use app\modules\tasks\models\BuhEventForm;
/**
 * Default controller for the `bank` module
 */
class BuhController extends Controller
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
/*********************************************/    

    public function actionEventExec()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));

        return $this->render('event-exec', ['model' => $model, 'date' => $date]);
    }   

    public function actionEventExecDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new BuhEventForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        $modelForm=  new TasksEditForm();
        if ($modelForm->load(Yii::$app->request->post()) && $modelForm->validate() )
        {          
         $modelForm->saveMarketTask();
         $this->redirect(['event-exec-detail', 'date' => $date, 'id' => $id]);         
         return;
        }

         $orgModel = new OrgSelect();
         $provider = $orgModel->getOrgSelectProvider(Yii::$app->request->get());             
         
         $modelForm->executorRef = $id ;
         $modelForm->loadData();


        return $this->render('event-exec-detail', ['model' => $model,'modelForm' => $modelForm,  'date' => $date, 'userId' => $id,
                                     'orgModel' => $orgModel, 'provider' => $provider,]);
    }   

    public function actionEventExecWeek()
    {    
        $request = Yii::$app->request;    
        $model=  new BuhEventForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));
        
        $modelForm=  new TasksEditForm();
        if ($modelForm->load(Yii::$app->request->post()) && $modelForm->validate() )
        {          
         $modelForm->saveMarketTask();
         $this->redirect(['event-exec-week', 'date' => $date, 'id' => $id]);         
         return;
        }

         $orgModel = new OrgSelect();
         $provider = $orgModel->getOrgSelectProvider(Yii::$app->request->get());             
         
         $modelForm->executorRef = $id ;
         $modelForm->loadData();
     
         return $this->render('event-exec-week', ['model' => $model,'modelForm' => $modelForm, 'date' => $date, 'userId' => $id, 
                             'orgModel' => $orgModel, 'provider' => $provider,]);
    }   


    public function actionEventExecShort()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        return $this->render('event-exec-short', ['model' => $model, 'date' => $date, 'userId' => $id]);
    }   

/*********************************************/    

    public function actionMarketTask()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
     $request = Yii::$app->request;
     $refManager= intval($request->get('refManager',0));         

      $model = new TasksEditForm();
      if ($model->load(Yii::$app->request->post()) )
      {
       
          
          if( $model->validate() ){
             $model->saveMarketTask();
             return;
          }
      }
      
         $model->id = intval($request->get('id',0));                  
         $action = $request->get('action','none');
         if ($model->id == 0)
         {
             $model->executorRef= $refManager;
         } 

         $orgModel = new OrgSelect();
         $provider = $orgModel->getOrgSelectProvider(Yii::$app->request->get());             
         return $this->render('market-task', ['model' => $model, 'orgModel' => $orgModel, 'provider' => $provider, 'action' => $action]);
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

/****/
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

/*********************************************/    
    public function actionRejectTask()
    {
     $request = Yii::$app->request;     
     $model = new TasksAcceptForm();
     $eventid = intval($request->get('id',0));                  
     $model->rejectEventToTask($eventid );
     $this->redirect(['/site/success']);
     
    }

/*******************************************/

/*******************************************/
/********* Service  ************************/
/*******************************************/

    
}
