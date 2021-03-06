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
        $curUser=Yii::$app->user->identity; 
     
        
        $model = new BankMain();   
        $extractModel = new BankExtract();      
        $detailModel = new BankExtractDetail();      
        $detailModel->maxRowDetail = 15;
                   
        $extractProvider = $extractModel->getBanctExtractionListProvider(Yii::$app->request->get());
        $detailProvider  = $detailModel ->getBankExtractionRecordsProvider(Yii::$app->request->get());
        return $this->render('index', ['model' => $model, 'detailModel' => $detailModel, 'extractProvider' => $extractProvider, 'detailProvider' =>$detailProvider  ] );
    }
 /*******************************************/   
    public function actionSaveExtractionParam()
    {
    $model = new BankExtractDetail();      
        if(Yii::$app->request->isAjax)
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
        $model->showDate = strtotime(date("Y-m-d")); // ???? ??????????????
        
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
        $now=date("dmY");
        $next=date("dmY", time()+24*3600);
        $sd = $request->get('sd',$now);        
        $ed = $request->get('ed',$next);      
        
        $model = new BankOperation();             
        $id = $model->syncOperations($sd, $ed);
                
        $this->redirect(['show-bank-operation', 'id' => $id ]); 
        
    }
/*******************************************/   
    public function actionShowBankOperation()
    {
        $request = Yii::$app->request;          
        $model = new BankOperation();     
        $model->id = intval($request->get('id',0));           
        
        $provider=$model->getBankOperationProvider(Yii::$app->request->get()); 
        return $this->render('show-bank-operation', ['model' => $model, 'provider' => $provider]);        
      
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
    //    $model->showDate = strtotime(date("Y-m-d")); // ???? ??????????????
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
        return $this->render('doc-list', ['model' => $model, 'provider' => $provider]);        
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
        $model->showDate = strtotime(date("Y-m-d")); // ???? ??????????????
        
        $provider= $model->getDocLoadListProvider(Yii::$app->request->get());         
 
        return $this->render('load-doc', ['model' => $model, 'provider' => $provider]);        
    }
    
    
/*******************************************/  
    public function actionRegDoc()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        $model = new DocLoad();     
        $model ->id = intval($request->get('id', 0));
        $model ->refDocHeader = intval($request->get('ref', 0));

      	if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
           $model->saveData();		  		   
		   $this->redirect(['success']);		   
		   return;
		}
        
        return $this->render('reg-doc', ['model' => $model,]);        
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
