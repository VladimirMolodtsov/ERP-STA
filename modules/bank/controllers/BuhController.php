<?php

namespace app\modules\bank\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\bank\models\BankMain;
use app\modules\bank\models\BankExtract;
use app\modules\bank\models\StatBank;
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
use app\modules\bank\models\BuhMonthStatistics;

use app\modules\bank\models\StoreOplata;
use app\modules\bank\models\GetExtract;

use app\modules\tasks\models\TaskTiming;

use app\modules\bank\models\AuroraExtract;

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
/*******************************************/       
    public function actionBuhMonthStatistics()
    {
        $request = Yii::$app->request;
        $model = new BuhMonthStatistics();        
        $model->dtstart = $request->get('dtstart', (date("Y-01-01")) );
        $model->manual  = intval($request->get('manual', 0 ));
        $provider       =$model->getBuhStatProvider(Yii::$app->request->get());  
/*echo "<pre>";
print_r($model->buhStatPrepared);
print_r($model->dateList);
print_r($model->timeList);
echo "</pre>";
return;  */      
        $controlprovider=$model->getBuhControlProvider(Yii::$app->request->get());
        $eventprovider  =$model->getBuhEventProvider(Yii::$app->request->get());            
        
        return $this->render('buh-month-statistics', ['model' => $model, 'provider' => $provider, 
                            'controlprovider' => $controlprovider, 'eventprovider' => $eventprovider, ]);   
    }
    
    
/*******************************************/   
    public function actionShowMonthCalc()
    {
        $model = new BuhMonthStatistics();
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
    public function actionSetMonthChecked()
    {
        $model = new BuhMonthStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $model->setChecked($col);
        
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
    public function actionSetMonthFinished()
    {
        $model = new BuhMonthStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $model->setFinished($col);
        
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
    public function actionStartMonthSync()
    {
        $model = new BuhMonthStatistics();
        $request = Yii::$app->request;        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $col     = intval($request->get('col'  , 0 ));
       
        $syncTime = $model->startCheck($col);
         
        $this->redirect(['/site/success']);  return;            
    }
/*******************************************/   
/*******************************************/   

     
    public function actionBuhStatistics()
    {
        $request = Yii::$app->request;
        $model = new BuhStatistics();        
        $model->dtstart = $request->get('dtstart', (date("Y-m-d")) );
        $model->manual  = intval($request->get('manual', 0 ));
        $provider       =$model->getBuhStatProvider(Yii::$app->request->get());  
/*echo "<pre>";
print_r($model->buhStatPrepared);
print_r($model->checkedList);
echo "</pre>";
return;        */
        $controlprovider=$model->getBuhControlProvider(Yii::$app->request->get());
        $eventprovider  =$model->getBuhEventProvider(Yii::$app->request->get());            
        
        return $this->render('buh-statistics', ['model' => $model, 'provider' => $provider, 
                            'controlprovider' => $controlprovider, 'eventprovider' => $eventprovider, ]);   
    }
 /*******************************************/   
   
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
/********* Aurora **************************/
/*******************************************/
    public function actionAuroraLoadExtract()
    {
        $model = new AuroraExtract();     
        $model -> getExtractAttach();        
        return $this->render('success');
    }
            
    public function actionAuroraGetExtract()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        $model = new AuroraExtract();     
        
        $request = Yii::$app->request;        
        
        $sd = $request->get('sd', date("Y-m-d") );
        $ed = $request->get('ed', date("Y-m-d") );
        $out = $model -> getExtractData($sd, $ed);        
        
        \Yii::$app->response->data = $out;
        /*echo $out;*/
        Yii::$app->end();
        return;
    }
            
/*******************************************************************************/          
/*******************************************************************************/          

    public function actionStoreOplata()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new StoreOplata();     
    //    $model->showDate = strtotime(date("Y-m-d")); // на сегодня
        $request = Yii::$app->request; 
        $model->flt = $request->get('flt','all');
        $format = $request->get('format','html');
         
        if ($format == 'csv')
        {        
            $reportFile = $model->getDocLoadListData(Yii::$app->request->get());                 
            $url = Yii::$app->request->baseUrl."/../".$reportFile;
            $this->redirect(['/site/download', 'url' => $url]);             
            return;
        }    

        $provider= $model->getDocLoadListProvider(Yii::$app->request->get());         
        return $this->render('store-oplata', ['model' => $model, 'provider' => $provider]);  
    }
            
    public function actionStorePay()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new StoreOplata();     
    
        $request = Yii::$app->request; 
        $model->flt = $request->get('flt','all');
        $format = $request->get('format','html');
         
        $model->fromDate = $request->get('fromDate',date("Y-m-d", time()-90*24*3600));
        $model->toDate = $request->get('toDate',date("Y-m-d"));
        
        $model->overdueVal = $request->get('overdueVal','0');    
        $model->todayVal   = $request->get('todayVal','1');
        $model->tomorrowVal= $request->get('tomorrowVal','0');
        $model->furtherVal = $request->get('furtherVal','0');

        
        $noframe = $request->get('noframe',0);

        $provider= $model->getDocLoadListProvider(Yii::$app->request->get());         
        return $this->render('store-pay', ['model' => $model, 'provider' => $provider, 'noframe' => $noframe]);  
    }
                
/*******************************************************************************/          
    public function actionSaveStoreOplata()
    {   
        $request = Yii::$app->request;    
        $model = new StoreOplata();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
/*******************************************************************************/          
    public function actionPreparePaymentOrder()
    {   
        $request = Yii::$app->request;    
        $model = new StoreOplata();    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
           $sendArray = $model->preparePaymentOrder();          
           echo json_encode($sendArray);
           return;
            }
        }
    }    
  
    public function actionDownloadPaymentOrder()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }    
       $request = Yii::$app->request; 
       $reportFile = $request->get('reportFile','');
       $url = Yii::$app->request->baseUrl."/../".$reportFile;
       $this->redirect(['/site/download', 'url' => $url, 'type' => 'txt']);             
      
       return;
    }
     

    public function actionPayOrders()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new StoreOplata();     
        $provider= $model->getPayOrdersProvider(Yii::$app->request->get());         
        return $this->render('pay-orders', ['model' => $model, 'provider' => $provider]);  
    }
    
    public function actionDownloadPayOrder()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }  
       $request = Yii::$app->request; 
       $id = $request->get('id',0);
       $model = new StoreOplata();     
       $reportFile = $model->getPayFile($id);
       $url = Yii::$app->request->baseUrl."/../".$reportFile;
       $this->redirect(['/site/download', 'url' => $url, 'type' => 'txt', 'redirect' => 0, 'noframe' => 1 ]);                     
    }
     
    public function actionShowPayOrder()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }  
       $request = Yii::$app->request; 
       $model = new StoreOplata();     
       $model->id = $request->get('id',0);
       $provider= $model->getPayOrderDetailProvider(Yii::$app->request->get());         
       return $this->render('show-pay-order', ['model' => $model, 'provider' => $provider]);          
    }

/*******************************************************************************/               
    public function actionSupplierOplata()
    {
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
        $request =Yii::$app->request;      
        $model = new StoreOplata();    

         
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
//        $model->setDate = $request->get('setDate',0);        
        $model->fixPeriod();
        
        $model->refSuppSchet      = $request->get('refSuppSchet',0);        
        $model->refSupplierOplata = $request->get('refSupplierOplata',0);        
        $model->refDocOplata      = $request->get('refDocOplata',0);        
        
        $model->flt = $request->get('flt','showAll');        
                    
        $provider=$model->getSupplierOplataProvider(Yii::$app->request->get());
        return $this->render('supplier-oplata', ['model' => $model,'provider' => $provider]);
    }
         
    public function actionSaveLnkOplata()
    {   
        $request = Yii::$app->request;    
        $model = new StoreOplata();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveLnkOplata();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
         

    public function actionExtractOplata()
    {
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
        $request =Yii::$app->request;      
        $model = new StoreOplata();    
         
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
//        $model->setDate = $request->get('setDate',0);        
        $model->fixPeriod();
                        
        $model->flt = $request->get('flt','showAll');        
        $model->refDocOplata = $request->get('refDocOplata',0);        
                    
        $provider=$model->getExtractOplataProvider(Yii::$app->request->get());
        return $this->render('extract-oplata', ['model' => $model,'provider' => $provider]);
    }
        

    public function actionSwitchPp()
    {   
        $request = Yii::$app->request;    
        $model = new StoreOplata();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->switchPP();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
/*******************************************/
/*******************************************/
/*******************************************/
    public function actionStatBank()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new StatBank();     
        $provider= $model->getStatBankProvider(Yii::$app->request->get());         
        return $this->render('stat-bank', ['model' => $model, 'provider' => $provider]);  
    }
         

public function actionDownloadExtract()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }  
       $request = Yii::$app->request; 
       $model = new StatBank();  
       $model->curMonth = $request->get('curMonth',date("n"));
       $model->curYear  = $request->get('curYear',date("Y"));
             
       
       $reportFile = $model->getBankExtract(Yii::$app->request->get());
       $url = Yii::$app->request->baseUrl."/../".$reportFile;
       $this->redirect(['/site/download', 'url' => $url, /*'type' => 'txt', 'redirect' => 0, 'noframe' => 1 */]);                     
    }
public function actionDownloadBankOp()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }  
       $request = Yii::$app->request; 
       $model = new StatBank();  
       $model->curMonth = $request->get('curMonth',date("n"));
       $model->curYear  = $request->get('curYear',date("Y"));
       
       $reportFile = $model->getBankOp(Yii::$app->request->get());
       $url = Yii::$app->request->baseUrl."/../".$reportFile;
       $this->redirect(['/site/download', 'url' => $url, /*'type' => 'txt', 'redirect' => 0, 'noframe' => 1 */]);                     
    }

         
/*******************************************/
/********* Service  ************************/
/*******************************************/

//get-extract&sd=2021-01-01&ed=2021-02-28    
    public function actionGetExtract()    
    {
      // \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
      //  Yii::$app->response->headers->add('Content-Type', 'text/csv');
        $model = new GetExtract();     
        
        $request = Yii::$app->request;        
        
        $sd = $request->get('sd', date("Y-m-d") );
        $ed = $request->get('ed', date("Y-m-d") );
        $out = $model -> getExtractData($sd, $ed);        
        
        \Yii::$app->response->data = $out;
        /*echo $out;*/
        Yii::$app->end();
        return;
    }


    public function actionSuccess()
    {
        return $this->render('success');
    }

    public function actionClose()
    {
        return $this->render('close');
    }
    
}
