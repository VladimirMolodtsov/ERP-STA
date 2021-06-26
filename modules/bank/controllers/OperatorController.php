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
use app\modules\bank\models\DocOrgList;
use app\modules\bank\models\DocClassify;

use app\modules\bank\models\PlatDispetcher;
use app\modules\bank\models\ShipDispetcher;

use app\modules\bank\models\BuhDispetcher;

use app\modules\tasks\models\TaskTiming;

/**
 * Default controller for the `bank` module
 */
class OperatorController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
     
       $this->redirect(['extract-check']);             

/*Старый индекс*/
        $curUser=Yii::$app->user->identity; 
        $request =Yii::$app->request;      
        
        $model = new BankMain();   
        $extractModel = new BankExtract();      
        $detailModel = new BankExtractDetail();      
        $detailModel->maxRowDetail = 15;
        
        $detailModel->fromDate = $request->get('fromDate',date("Y-m-d", time()-90*24*3600));
        $detailModel->toDate = $request->get('toDate',date("Y-m-d"));
        
        $detailModel->overdueVal =intval($request->get('overdue',1));           
        $detailModel->yesterdayVal =intval($request->get('yesterday',1));           
        $detailModel->todayVal =intval($request->get('today',1));           

        $format = $request->get('format','html');
         
        if ($format == 'csv')
        {        
            $reportFile = $detailModel->getBankExtractShowData(Yii::$app->request->get());                 
            $url = Yii::$app->request->baseUrl."/../".$reportFile;
            $this->redirect(['/site/download', 'url' => $url]);             
            return;
        }    
                   
        $extractProvider = $extractModel->getBanctExtractionListProvider(Yii::$app->request->get());
        $detailProvider  = $detailModel ->getBankExtractShowProvider(Yii::$app->request->get());
        return $this->render('index', ['model' => $model, 'detailModel' => $detailModel, 'extractProvider' => $extractProvider, 'detailProvider' =>$detailProvider  ] );
    }

 /*******************************************/           
    public function actionExtractList()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
        $request =Yii::$app->request;      
        
   
        $model = new BankExtract();      
            
        $model->fromDate = $request->get('fromDate',date("Y-m-d", time()-90*24*3600));
        $model->toDate = $request->get('toDate',date("Y-m-d"));
                           
        $extractProvider = $model->getBanctExtractionListProvider(Yii::$app->request->get());
        return $this->render('extract-list', ['model' => $model,  'extractProvider' => $extractProvider,  ] );
    }
    
 /*******************************************/       
     public function actionExtractCheck()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
        $request =Yii::$app->request;      
        
        $model = new BankMain();   
   
        $detailModel = new BankExtractDetail();      
        $detailModel->maxRowDetail = 15;
        
        $detailModel->fromDate = $request->get('fromDate',date("Y-m-d", time()-90*24*3600));
        $detailModel->toDate = $request->get('toDate',date("Y-m-d"));
        
        $detailModel->overdueVal =intval($request->get('overdue',1));           
        $detailModel->yesterdayVal =intval($request->get('yesterday',1));           
        $detailModel->todayVal =intval($request->get('today',1));           
        
        $detailModel->detail==intval($request->get('detail',0));           

        $format = $request->get('format','html');
         
        if ($format == 'csv')
        {        
            $reportFile = $detailModel->getBankExtractShowData(Yii::$app->request->get());                 
            $url = Yii::$app->request->baseUrl."/../".$reportFile;
            $this->redirect(['/site/download', 'url' => $url]);             
            return;
        }    
             
        if ($format == 'print')
        {        
            echo  $detailModel->printBankExtractShowData(Yii::$app->request->get());                             
            exit(0);
            return;            
        }    

        if ($format == 'short')
        {        
            echo  $detailModel->printBankExtractShort(Yii::$app->request->get());                             
            exit(0);
            return;            
        }    
                                   
        $detailProvider  = $detailModel ->getBankExtractShowProvider(Yii::$app->request->get());
        return $this->render('extract-check', ['model' => $model, 'detailModel' => $detailModel,  'detailProvider' =>$detailProvider  ] );
    }
    
    public function actionExtractErrorCalendar()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new BankExtractDetail();     
        $request = Yii::$app->request; 
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));

        $request = Yii::$app->request; 
           
        return $this->render('extract-error-calendar', ['model' => $model, 'month' => $month, 'year' => $year  ]);        
    }
    
/*******************************************/   
    public function actionGetDealsList()
    {
    $model = new BankExtractDetail();      
    $request = Yii::$app->request; 
    $typeId= intval($request->get('typeId', 0));
    $extractRef= intval($request->get('extractRef', 0));
      //  if(Yii::$app->request->isAjax)
        {        
                $sendArray = $model->getDealsListArray($typeId, $extractRef);
                echo json_encode($sendArray);
                return;                
        }
        
    }    
        
/*******************************************/   
    
    public function actionBankOperationCheck()
    {
        $request = Yii::$app->request;          
        $model = new BankOperation();     
        
        $provider=$model->getBankOpCheckProvider(Yii::$app->request->get()); 
        return $this->render('bank-operation', ['model' => $model, 'provider' => $provider]);        
      
    }
/*******************************************/       
    public function actionSchetExtract()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
        $request =Yii::$app->request;      
        $model = new BankExtractDetail();    
         
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
                        
        $model->flt = $request->get('flt','showAll');        
        $model->refExtract = $request->get('refExtract',0);        
                    
        $provider=$model->getSchetExtractionLnkProvider(Yii::$app->request->get());
        return $this->render('schet-extract', ['model' => $model,'provider' => $provider]);
    }
        
    
 /*******************************************/       
    public function actionDocExtract()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
        $request =Yii::$app->request;      
        $model = new BankExtractDetail();    
         
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
                        
        $model->flt = $request->get('flt','showAll');        
        $model->refExtract = $request->get('refExtract',0);        
                    
        $provider=$model->getDocExtractionLnkProvider(Yii::$app->request->get());
        return $this->render('doc-extract', ['model' => $model,'provider' => $provider]);
    }
        
 /*******************************************/   
    public function actionSaveExtractionLnk()
    {
    $model = new BankExtractDetail();      
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveLnk();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    }       
 /*******************************************/   
    public function actionSaveExtractionParam()
    {
    $model = new BankExtractDetail();      
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveData();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    }    
 /*******************************************/   
    
    public function actionLoadBank()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new BankExtract();     
        $model->showDate = strtotime(date("Y-m-d")); // на сегодня
        
        if (Yii::$app->request->isPost) {
            $model->xlsxFile = UploadedFile::getInstance($model, 'xlsxFile');            
            $uploadPath=__DIR__."/../uploads/";
            $model->debug.=$uploadPath;
            
            if ($model->upload()) 
            {
                
                $fname = $uploadPath.$model->xlsxFile->name;
                $model->loadBankExtract($fname);
                $this->redirect(['load-bank']); 
                return;
            }
        }
        $provider=$model->getBanctExtractionListProvider(Yii::$app->request->get());      
        return $this->render('load-bank', ['model' => $model, 'provider' => $provider]);        
    }
    
    
    
    
 /*******************************************/   
    
    public function actionLoadLog()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request;          
        $model = new BankExtract();     
       
        $provider=$model->getBanctExtractionListProvider(Yii::$app->request->get()); 
        $modelLog = new BankExtractLog ();    
       
        
        $modelLog->reportMonth = $request->get('reportMonth',0);
        $modelLog->reportYear  = $request->get('reportYear' ,0);

        return $this->render('load-log', ['model' => $model, 'modelLog' =>$modelLog, 'provider' => $provider]);        
    }
 
 /*******************************************/   
    
    public function actionShowExtract()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new BankExtractDetail();     
        
        $request = Yii::$app->request;          
        $model->extractRef = intval($request->get('id',0));
        
        $provider=$model->getBankExtractionDetailProvider(Yii::$app->request->get()); 
        
        //return;     
        return $this->render('extract-detail', ['model' => $model, 'provider' => $provider]);        
    }

/*******************************************/   
    
    public function actionShowIncome()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new BankExtractDetail();     
        $model->extractType = 1; 
        
        $model->cutType = 1; 
        
        $request = Yii::$app->request;          
        $model->extractRef = intval($request->get('id',0));
        
        $model->from = $request->get('from');        
        $model->to   = $request->get('to');
        $model->flt  = $request->get('flt');
        
        
        
        $provider=$model->getBankExtractionRecordsProvider(Yii::$app->request->get()); 

        return $this->render('extract-records', ['model' => $model, 'provider' => $provider]);        
    }

/*******************************************/
/********* Operation ***********************/
/*******************************************/
    public function actionSyncBankOperation()
    {
        $request = Yii::$app->request;          
        $now=time();
        $next=time()+24*3600;
        $sd = $request->get('sd',$now);        
        $ed = $request->get('ed',$next);      
        
        $model = new BankOperation();             
        $id = $model->syncOperations($sd, $ed, time());
        
/*        echo "<pre>";
        echo date ("d-m-Y", $sd);
        echo date ("d-m-Y", $ed);
        
        print_r($model->debug);
        echo "</pre>";*/
        $this->redirect(['show-bank-operation', 'id' => $id ]); 
        
    }
/*******************************************/   
    public function actionShowBankOperation()
    {
        $request = Yii::$app->request;          
        $model = new BankOperation();     
        
        $provider=$model->getBankOperationProvider(Yii::$app->request->get()); 
        return $this->render('show-bank-operation', ['model' => $model, 'provider' => $provider]);        
      
    }

/*******************************************/   
    public function actionBankOperationSelect()
    {
        $request = Yii::$app->request;          
        $model = new BankOperation();     
      
        $model->refOrg = intval($request->get('refOrg',0));           
        $model->refExtract = intval($request->get('refExtract',0));           
        
        $fromDate = $request->get('fromDate','');
        $toDate   = $request->get('toDate','');
        
        $model->setPeriod($fromDate, $toDate);
        
        $provider=$model->getBankOperationSelectProvider(Yii::$app->request->get()); 
        return $this->render('bank-operation-select', ['model' => $model, 'provider' => $provider, 'refExtract' => $model->refExtract]);        
      
    }

/*******************************************/
/********* Check    ************************/
/*******************************************/
    public function actionBankCheck()
    {
        $request = Yii::$app->request;          
        $model = new BankExtractCheck();     
        $model->refBankHeader = intval($request->get('refBankHeader',0));
        $model->refOpHeader   = intval($request->get('refOpHeader',0));                      
        //$model->prepareCheckData(Yii::$app->request->get());
        
        //return $this->render('bank-check', ['model' => $model]);
        $provider=$model->getBankCheckProvider(Yii::$app->request->get()); 
        $freeOpProvider=$model->getFreeOperationProvider(Yii::$app->request->get());
        return $this->render('bank-check', ['model' => $model, 'provider' => $provider, 'freeOpProvider' => $freeOpProvider]);        
      
    }

    public function actionFinalizeCheck()
    {
        $request = Yii::$app->request;          
        $model = new BankExtractCheck();     
        $model->refBankHeader = intval($request->get('refBankHeader',0));
        $model->refOpHeader   = intval($request->get('refOpHeader',0));                      
        $model->finalizeCheck();
      
        $this->redirect(['close']); 
    }
    
    
    //extract-operation-lnk&extract='+bankExtractRef+'&operation='+opId)
    public function actionExtractOperationLnk()
    {
        $request = Yii::$app->request;          
        $model = new BankExtractCheck();     
        $extract_id = intval($request->get('extract',0));
        $operation_id = intval($request->get('operation',0));
        $model->setExtractOperationLnk($extract_id,$operation_id);
      
        $this->redirect(['success']); 
    }
    //bank/operator/set-chk-status&status=-1&extract='+bankExtractRef
    public function actionSetChkStatus()
    {
        $request = Yii::$app->request;          
        $model = new BankExtractCheck();     
        $extract_id = intval($request->get('extract',0));
        $status = intval($request->get('status',0));
        $model->setExtractChkStatus($extract_id,$status);
      
        $this->redirect(['success']); 
    }
    
/*******************************************/   
    public function actionOpDayDetail()
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

        return $this->render('op-day-detail', ['bankmodel' => $bankmodel, 'docmodel' => $docmodel, 'shipmodel' => $shipmodel, 'platmodel' => $platmodel]);        
    }
    
/*******************************************/
/********* Dispetcher  ************************/
/*******************************************/
        
/*******************************************/   
    public function actionDispLog()
    {    
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request;          
        $model = new BankExtract();     
        
        $model->showDate = $request->get('showDate', strtotime(date("Y-m-d")) );
        $borderArray = TaskTiming::getTiming(2);

        
        $provider=$model->getBanctExtractionListProvider(Yii::$app->request->get());         
        return $this->render('disp-log', ['model' => $model, 'provider' => $provider, 'borderArray' => $borderArray]);        
    }
    
/*******************************************/   
    public function actionDispDayDetail()
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
        
        

        return $this->render('disp-day-detail', ['bankmodel' => $bankmodel, 'docmodel' => $docmodel, 
                             'shipmodel' => $shipmodel, 'platmodel' => $platmodel, 'buhmodel' => $buhmodel]);        
    }

/*******************************************/   

    public function actionDocList()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new DocLoad();     
    //    $model->showDate = strtotime(date("Y-m-d")); // на сегодня
        $request = Yii::$app->request; 
        $model->flt = $request->get('flt','all');
        $model->detail = $request->get('detail','0');
        $model->orgRef= $request->get('orgRef','0');
        
        $year  = $request->get('year',date("Y"));
        $month = $request->get('month',date("m"));
        $day   = $request->get('day',date("d"));        
         $model->month = $month;
         $model->year = $year;
       $model->showDate = strtotime($year."-".$month."-".$day);
       $format = $request->get('format','html');
         
        if ($format == 'csv')
        {        
            $reportFile = $model->getDocLoadListData(Yii::$app->request->get());                 
            $url = Yii::$app->request->baseUrl."/../".$reportFile;
            $this->redirect(['/site/download', 'url' => $url]);             
            return;
        }    

        if (Yii::$app->request->isPost) {
            $model->loadFile = UploadedFile::getInstance($model, 'loadFile');            
            $uploadPath=__DIR__."/../uploads/";
            if ($model->upload()) 
            {
                $fname = $uploadPath.$model->loadFile->name;
                $model->loadFileDocument($fname);
//return;                
                $this->redirect(['doc-list']); 
                return;
            }
        }


        $provider= $model->getDocLoadListProvider(Yii::$app->request->get());         
        return $this->render('doc-list', ['model' => $model, 'provider' => $provider]);        
    }

 /*******************************************/   

    public function actionDocErrorCalendar()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new DocLoad();     
        $request = Yii::$app->request; 
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));

        $request = Yii::$app->request; 
           
        return $this->render('doc-error-calendar', ['model' => $model, 'month' => $month, 'year' => $year  ]);        
    }

    public function actionDocControlCalendar()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new DocLoad();     
        $request = Yii::$app->request; 
        
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));
/*
        $startMonth = strtotime($year."-".$month."-01");
        $em=date('t', $startMonth);
        $endMonth = strtotime($year."-".$month."-".$em);    
        $fromDate = $request->get('fromDate',date("Y-m-d", $startMonth));
        $toDate = $request->get('toDate',$endMonth);        
        $from = strtotime($fromDate);
        $to   = strtotime($toDate);        
        if ($from < $startMonth) $from = $startMonth;
        if ($to   > $endMonth)   $to   = $endMonth;        
        if ($to ==0) $to = time();
*/        
        return $this->render('doc-control-calendar', ['model' => $model,  'month' => $month, 'year' => $year ]);        
    }

 /*******************************************/   
    public function actionSaveDocParam()
    {
    $model = new DocLoad();      
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    }    
 /*******************************************/   
    public function actionSaveDocRef()
    {
    $model = new DocLoad();      
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveRefData();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    }    

/*******************************************/   
    public function actionGetOperation()
    {
    $model = new DocLoad();      
    $request = Yii::$app->request; 
    $id= intval($request->get('id', 0));
        if(Yii::$app->request->isAjax)
        {        
                $sendArray = $model->getOperationArray($id);
                echo json_encode($sendArray);
                return;                
        }
        
    }    
 /*******************************************/   
    
    public function actionDocOrgList()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocOrgList();     
        $model ->searchINN = intval($request->get('searchINN', ''));
        
        $provider= $model->getDocOrgListProvider(Yii::$app->request->get());         
 
        return $this->render('doc-orglist', ['model' => $model, 'orgListProvider' => $provider]);        
    }

    
 /*******************************************/   
    public function actionLoadDoc()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new DocLoad();     
        $model->showDate = strtotime(date("Y-m-d")); // на сегодня
        
        $provider= $model->getDocLoadListProvider(Yii::$app->request->get());         
 
        return $this->render('load-doc', ['model' => $model, 'provider' => $provider]);        
    }
    
    
/*******************************************/  
    public function actionDuplicateDoc()
    {
        $request = Yii::$app->request; 
        $model = new DocLoad();     
        $srcRef = intval($request->get('srcRef', 0));
        $id= $model->duplicateDoc($srcRef);
        $this->redirect(['reg-doc', 'noframe' => 1, 'id'=> $id]);		   
    }
    public function actionRegDoc()
    {
        
    // Yii::$app->response->headers->set('X-Frame-Options', 'Allow');      
    // Yii::$app->response->headers->set('Access-Control-Allow-Origin', '*');   
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocLoad();     

      	if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
           $model->saveData();		  		   
           
		   $this->redirect(['success']);		   
		   return;
		}
        
        $model ->id = intval($request->get('id', 0));
        $model ->refDocHeader = intval($request->get('ref', 0));        
        $model->loadData();        
        $accountProvider= $model->getAccountsProvider(Yii::$app->request->get());         
        return $this->render('reg-doc', ['model' => $model,'accountProvider' =>$accountProvider]);        
    }

    
 /*******************************************/   
    
    public function actionUploadYandex()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new DocLoad();             
        if (Yii::$app->request->isPost) {
            $model->loadFile = UploadedFile::getInstance($model, 'loadFile');            
            $uploadPath=__DIR__."/../uploads/";
            $model->debug[]=$uploadPath;
            
            if ( $model->upload()) 
            {
                $model->load(Yii::$app->request->post());
                $fname = $uploadPath.$model->loadFile->name;
                $model->yandexDiskUpload($fname);
                $this->redirect(['reg-doc', 'noframe'=> 1, 'id' => $model->id]); 
                return;
            }
        }
        
        $this->redirect(['reg-doc', 'noframe'=> 1, 'id' => $model->id]); 
    }
    
    
    
    
    public function actionStartSyncRegDoc()
    {
        $syncUrl = "bank/operator/sync-reg-doc";
        return $this->render('sync-progress', ['syncUrl' => $syncUrl]);        
    }
    
    public function actionSyncRegDoc()
    {
        $request = Yii::$app->request; 
        $model = new DocImportData();     
        $model -> loadDocsFromUrl();        
        $this->redirect(['success']);		   
    }
    
    public function actionRegOrgList()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocOrgList();     
        $model ->searchINN = $request->get('searchINN', '');
        $model ->searchTitle = $request->get('searchTitle', '');
        
        $provider= $model->getDocOrgListProvider(Yii::$app->request->get());         
 
        return $this->render('reg-orglist', ['model' => $model, 'orgListProvider' => $provider]);        
    }

    public function actionRegOrgAcc()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocLoad();     
        $model->refOrg= intval($request->get('refOrg', 0));
        $accountProvider= $model->getAccountsProvider(Yii::$app->request->get());         
        return $this->render('reg-orgacc', ['model' => $model, 'accountProvider' => $accountProvider]);        
    }

    
    public function actionGetSelectedOrg()
    {
        $model = new DocLoad(); 
        
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->getOrgInfo();
                echo json_encode($sendArray);
                return;
            }    
        }
    }

    public function actionGetAccountInfo()
    {
        $model = new DocLoad(); 
        
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->getAccountInfo();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    
    }

    public function actionSetDealParam()
    {
        $model = new DocLoad(); 
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {

                $sendArray = $model->actionSetDealParam();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    
    }
    
    
    public function actionGetDealParam()
    {
        $model = new DocLoad(); 
        $request = Yii::$app->request; 
        $dealRef = $request->get('id', 0);
       // if(Yii::$app->request->isAjax)
        {

                $sendArray = $model->actionGetDealParam($dealRef);
                echo json_encode($sendArray);
                return;
        }
        
    
    }
/*******************************************/

    public function actionDocClassify()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocClassify();     
       
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveEditForm();
                $this->redirect(['doc-classify']);		 
                return;
            }    

        
        $provider= $model->getDocClassifyProvider(Yii::$app->request->get());         
        return $this->render('doc-classify', ['model' => $model, 'provider' => $provider]);        
    }

    public function actionSwitchClassify()
    {
        $model = new DocClassify(); 
        $request = Yii::$app->request; 
        $classRef = $request->get('classRef', 0);
        $grpRef = $request->get('grpRef', 0);
        
       // if(Yii::$app->request->isAjax)
        {

                $sendArray = $model->switchClassGrp($classRef,$grpRef );
                echo json_encode($sendArray);
                return;
        }
        
    
    }

    public function actionRegDocClassify()
    {
        $model = new DocClassify(); 
        $request = Yii::$app->request; 
        $model->docId = $request->get('docId', 0);
        
        return $this->render('reg-doc-classify', ['model' => $model]);            
    }
    

    public function actionGetClassifyParam()
    {
        $model = new DocClassify(); 
        $request = Yii::$app->request; 
        $classRef = $request->get('classRef', 0);
        $grpRef = $request->get('grpRef', 0);
       // if(Yii::$app->request->isAjax)
        {
           $sendArray = $model->getClassifParam($classRef, $grpRef);
           echo json_encode($sendArray);
          return;
        }
    
    }
    
/*******************************************/
/* 1C счета*/
    public function actionSelectSchet()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocLoad();     
        $model ->id = $request->get('docid', '');
        $model ->fromDate = $request->get('fromDate', '');
        $model ->toDate = $request->get('toDate', '');
        
        $clientProvider= $model->getClientSchetProvider(Yii::$app->request->get());         
        $supplierProvider= $model->getSupplierSchetProvider(Yii::$app->request->get());
        $supplyProvider= $model->getSupplyProvider(Yii::$app->request->get());
        $purchProvider= $model->getPurchProvider(Yii::$app->request->get());      
                       
        return $this->render('select-schet', ['model' => $model, 'clientProvider' => $clientProvider
        , 'supplierProvider' => $supplierProvider, 'supplyProvider' => $supplyProvider, 'purchProvider' => $purchProvider]);        
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
