<?php

namespace app\modules\bank\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\bank\models\BankMain;
use app\modules\bank\models\BankExtract;
use app\modules\bank\models\BankExtractDetail;
use app\modules\bank\models\BankExtractLog;
use app\modules\bank\models\BankOperation;
use app\modules\bank\models\BankExtractCheck;
use app\modules\bank\models\BankDispetcher;


use app\modules\bank\models\DocDispetcher;
use app\modules\bank\models\DocLoad;
use app\modules\bank\models\DocImportData;

use app\modules\bank\models\PlatDispetcher;
use app\modules\bank\models\ShipDispetcher;

use app\modules\bank\models\BuhDispetcher;
use app\modules\bank\models\BuhStatistics;

use app\modules\tasks\models\TaskTiming;

/**
 * Default controller for the `bank` module
 */
class BuhController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
        
     $this->redirect(['/bank/buh/buh-day-detail']);  return;    
     
    }
 /*******************************************/   
    public function actionBuhStatistics()
    {
        $request = Yii::$app->request;
        $model = new BuhStatistics();        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        
        $provider       =$model->getBuhStatProvider(Yii::$app->request->get());      
        $controlprovider=$model->getBuhControlProvider(Yii::$app->request->get());
        $eventprovider  =$model->getBuhEventProvider(Yii::$app->request->get());            
        
        return $this->render('buh-statistics', ['model' => $model, 'provider' => $provider, 
                            'controlprovider' => $controlprovider, 'eventprovider' => $eventprovider, ]);   
    }
    
    
    public function actionSetEventTitle()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $timestart  = $request->get('timestart', time() );
        $val        = $request->get('val',  "" );       
        $model->setEventTitle($timestart, $val);        
        $this->redirect(['/site/success']);  return;            
    }

    public function actionSetEventNote()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $timestart  = $request->get('timestart', time() );
        $val        = $request->get('val',  "" );       
        $model->setEventNote($timestart, $val);        
        $this->redirect(['/site/success']);  return;            
    }
    
        
/*******************************************/   
    public function actionSetChecked()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $model->setChecked($col);
        
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
    public function actionSetFinished()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $model->setFinished($col);
        
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
    public function actionStartCheck()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $syncTime = $model->startCheck($col);
         
        $this->redirect(['/data/sync-buh-stat', 'syncTime' =>$syncTime , 'noframe' => 1]);  return;            
    }

/*******************************************/   
    public function actionSetDataUse()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;                
        $refCheck     = intval($request->get('refCheck', 0));
        $opType       = intval($request->get('opType', 0));
        $refSrc       = intval($request->get('refSrc', 0));
        $mult         = intval($request->get('mult', 0));
       
        $model->setDataUse($refCheck, $opType, $refSrc, $mult );
      //  return;
        $this->redirect(['/site/success']);  return;            
    }
    
    
    
/*******************************************/   
    public function actionSetStatistics()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
        $order   = intval($request->get('order', 0 ));
        $val     = $request->get('val'  , 0 );
        
        $model->setStatistics($col, $order, $val);
        
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
    public function actionShowCalc()
    {
        $model = new BuhStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $model->col     = intval($request->get('col'  , 0 ));
        $model->idx     = intval($request->get('idx', 0 ));
              
        $provider       =$model->getBuhStatProvider(Yii::$app->request->get());      
        $controlprovider=$model->getBuhControlProvider(Yii::$app->request->get());
                        
        return $this->render('buh-show-calc', ['model' => $model, 'provider' => $provider, 
                            'controlprovider' => $controlprovider, ]);        

    }
    
/*******************************************/   
    public function actionBuhDayDetail()
    {    
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request;        
        $showDate = $request->get('showDate', strtotime(date("Y-m-d")) );
        
        $bankmodel = new BankDispetcher();                   
        $bankmodel->showDate = $showDate;
        $bankmodel->borderArray = TaskTiming::getTiming(2);

        $docmodel = new DocDispetcher();     
        $docmodel->showDate = $showDate;
        $docmodel->borderArray = TaskTiming::getTiming(4);

        $platmodel = new PlatDispetcher();     
        $platmodel->showDate = $showDate;
        $platmodel->borderArray = TaskTiming::getTiming(5);

        $shipmodel = new ShipDispetcher();     
        $shipmodel->showDate = $showDate;
        $shipmodel->borderArray = TaskTiming::getTiming(6);

        $buhmodel = new BuhDispetcher();     
        $buhmodel->showDate = $showDate;
        $buhmodel->borderArray = TaskTiming::getTiming(7);
        
        

        return $this->render('buh-day-detail', ['bankmodel' => $bankmodel, 'docmodel' => $docmodel, 
                             'shipmodel' => $shipmodel, 'platmodel' => $platmodel, 'buhmodel' => $buhmodel]);        
    }
    
            
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    public function actionClose()
    {
        return $this->render('close');
    }
    
}
