<?php

namespace app\modules\tasks\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//use yii\web\UploadedFile;

use app\modules\tasks\models\TasksMain;
use app\modules\tasks\models\TasksEditForm;
use app\modules\tasks\models\OrgSelect;
use app\modules\tasks\models\TasksAcceptForm;
/**
 * Default controller for the `bank` module
 */
class MainController extends Controller
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


      $model = new TasksMain();

      $provider = $model->getTaskTemplateListProvider(Yii::$app->request->get());
      return $this->render('index', ['model' => $model, 'provider' => $provider,  ] );
    }

    public function actionTaskCreate()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TasksEditForm();
      $id =  $model->taskCreate();
      if ($id ==false)
      {
         $this->redirect(['error']);
         return;
      }
         $this->redirect(['task', 'id' => $id]);
    }


    public function actionTask()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TasksEditForm();
      if ($model->load(Yii::$app->request->post()) && $model->validate())
      {
         $model->saveData();
         return;
         $this->redirect(['task', 'id' => $id]);
         return;
      }
         $request = Yii::$app->request;
         $model->id = intval($request->get('id',0));         
         return $this->render('task', ['model' => $model]);
    }
/*********************************************/
    public function actionSetTaskOrg()
    {
     $request = Yii::$app->request;
     $orgRef= intval($request->get('orgRef',0));         
     $refManager= intval($request->get('refManager',0));         
     $model = new TasksEditForm();
     $session = Yii::$app->session;        
     $session->open();
     $session->set('tasksOrgRef', $orgRef);                
     $this->redirect(['market-task', 'noframe'=>1, 'refManager' => $refManager]);      
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
              
             /*$session = Yii::$app->session;        
             $session->open();
         
             $session->set('tasksStartDate', $model->startDate);
             $session->set('tasksPlanDate', $model->planDate);
             $session->set('tasksDeadDate', $model->deadDate);

             $session->set('tasksStartTime', $model->startTime);
             $session->set('tasksPlanTime', $model->planTime);
             $session->set('tasksDeadTime', $model->deadTime);
             
             $session->set('tasksNote', $model->note);
             $session->set('taskPriority', $model->taskPriority);         */
      
         $model->saveMarketTask();
                  
         //$this->redirect(['market-task','id' => $refManager, 'action' => 'saved']);
         $this->redirect(['reload']);
         return;
          }
      }
      
         $model->id = intval($request->get('id',0));                  
         $action = $request->get('action','none');
         if ($model->id == 0)
         {
/*             $session = Yii::$app->session;        
             $session->open();
             $model->orgRef=$session->get('tasksOrgRef', 0);
             $model->orgTitle=$model->getOrgTitle($model->orgRef);                             
             
             $model->startDate = $session->get('tasksStartDate', date('d.m.Y'));
             $model->planDate  = $session->get('tasksPlanDate', date('d.m.Y'));
             $model->deadDate  = $session->get('tasksDeadDate', date('d.m.Y'));

             $model->startTime = $session->get('tasksStartTime', date('H:i:s', time()+4*3600));
             $model->planTime  = $session->get('tasksPlanTime', date('H:i:s', time()+4*3600));
             $model->deadTime  = $session->get('tasksDeadTime', date('H:i:s', time()+4*3600));
             
             $model->note  = $session->get('tasksNote', "");
             $model->taskPriority  = $session->get('taskPriority', 0);
             */
             
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
    public function actionSetDone()
    {
     $request = Yii::$app->request;     
     $model = new TasksOperation();
     $eventid   = intval($request->get('id',0));                  
     $execstate = intval($request->get('state',100));                  
     $model->setTaskDone($eventid, $execstate );
     $this->redirect(['/site/success']);
     
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

    public function actionSuccess()
    {
        return $this->render('success');
    }

    public function actionReload()
    {
        return $this->render('reload');
    }

}
