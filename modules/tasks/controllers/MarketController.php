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
use app\modules\tasks\models\EventRegForm1;
use app\modules\tasks\models\TasksList;
use app\modules\tasks\models\TaskControlForm;
/**
 * Default controller for the `bank` module
 */
class MarketController extends Controller
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

    public function actionEventLog()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));

        return $this->render('event-exec', ['model' => $model, 'date' => $date]);
    }   
/*******************************************/
    public function actionEventExecDetailOld()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
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


        return $this->render('event-exec-detail-old', ['model' => $model,'modelForm' => $modelForm,  'date' => $date, 'userId' => $id,
                                     'orgModel' => $orgModel, 'provider' => $provider,]);
    }   

/*******************************************/
    public function actionEventExecDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
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
    
/*******************************************/        
    public function actionEventExecDetailPrint()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        $modelForm=  new TasksEditForm();

        return $this->render('event-exec-detail-print', ['model' => $model, 'date' => $date, 'userId' => $id,  ]);
        
        
    }   
/*******************************************/        
   public function actionSaveEventExecDetail()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new TasksEditForm();
    
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }
        
        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }

        
    }       

/***************************************************/
   public function actionSaveSetExec()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new TasksEditForm();
    
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveSetExec();
                echo json_encode($sendArray);
                return;
            }    
        }
        
        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveSetExec();
                echo json_encode($sendArray);
                return;
            }    
        }

        
    }       
/***************************************************/
    public function actionEventExecWeek()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
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


    
    
    
    public function actionEventExecWeekDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm1();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));
        
        $modelForm=  new TasksEditForm();
        if ($modelForm->load(Yii::$app->request->post()) && $modelForm->validate() )
        {          
         $modelForm->saveMarketTask();
         $this->redirect(['event-exec-week-detail', 'date' => $date, 'id' => $id]);         
         return;
        }

         $orgModel = new OrgSelect();
         $provider = $orgModel->getOrgSelectProvider(Yii::$app->request->get());             
         
         $modelForm->executorRef = $id ;
         $modelForm->loadData();
     
         return $this->render('event-exec-week-detail', ['model' => $model,'modelForm' => $modelForm, 'date' => $date, 'userId' => $id, 
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
    public function actionRejectTask()
    {
     $request = Yii::$app->request;     
     $model = new TasksAcceptForm();
     $eventid = intval($request->get('id',0));                  
     $model->rejectEventToTask($eventid );
     $this->redirect(['/site/success']);
     
    }

/*******************************************/


    public function actionTaskList()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TasksList();
      $request = Yii::$app->request;
      $provider = $model->getIssuedTasksProvider(Yii::$app->request->get());    
          return $this->render('task-list', ['model' => $model, 'provider' => $provider,]);
    }
                           
    public function actionGetSchet()
    {
      $request = Yii::$app->request;
      $id   = intval($request->get('id', 0 ));
      $model = new TasksMain();
        //if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getSchetData($id);
                echo json_encode($sendArray);
                return;
        }
    }

/*******************************************/

    public function actionTaskExecution()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TasksList();
      $request = Yii::$app->request;
      $provider = $model->getIssuedTasksProvider(Yii::$app->request->get());    
          return $this->render('task-list', ['model' => $model, 'provider' => $provider,]);
    }
                           


/*******************************************/

    public function actionTaskControl()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TaskControlForm();
      $request = Yii::$app->request;
      $strDate= $request->get('date', date('Y-m-d') );        
      $model->userRef = intval($request->get('userRef', 0 ));        
      $model->curTime=strtotime($strDate);
      $provider = $model->getTaskControlProvider(Yii::$app->request->get());    
          return $this->render('task-control', ['model' => $model, 'provider' => $provider,]);
    }
                           
/*******************************************/

    public function actionTaskGlobalControl()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }

      $model = new TaskControlForm();
      $request = Yii::$app->request;
      $strDate= $request->get('date', date('Y-m-d') );        
      $model->curTime=strtotime($strDate);
      $provider = $model->getTaskGlobalControlProvider(Yii::$app->request->get());    
          return $this->render('task-global-control', ['model' => $model, 'provider' => $provider,]);
    }
                           

/*******************************************/
/********* Service  ************************/
/*******************************************/

    
}
