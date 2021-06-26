<?php

namespace app\modules\managment\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\managment\models\HeadMonitorForm;
use app\modules\managment\models\PurchClassifyForm;
use app\modules\managment\models\ShowSrcForm;
use app\modules\managment\models\PersonalControlForm;
use app\modules\managment\models\PlanProductionForm;
use app\modules\managment\models\GooglePriceForm;

use app\modules\bank\models\BuhStatistics;
use app\modules\bank\models\BankOperation;

use app\models\DataSync;

/**
 * controller for the `head` module

 */
class HeadController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
        
        $this->redirect(['/managment/head/head-monitor']);  return;         
    }
 
 
/*******************************************/
/********* Монитор собственника  ***********/
/*******************************************/
    public function actionHeadMonitor()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();

         

    //Период        
    
        $model->stDate= $request->get('stDate',date('Y-m-01', time())); //            
        $model->enDate= $request->get('enDate',date('Y-m-d')); //
                   
        //$provider    = $model->getFinControlProvider(Yii::$app->request->get());                                
        return $this->render('head-monitor', ['model' => $model, /*'provider' =>$provider*/]);
    }
    
/*******************************************/    
    public function actionMonitorRowCfg()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $model->rowType= intval($request->get('rowType',0)); //

    $model->stDate= $request->get('stDate',date('Y-m-d', time() - 7*24*3600)); //            
    $model->enDate= $request->get('enDate',date('Y-m-d')); //
    $model->stTime = strtotime($model->stDate);
    $model->enTime = strtotime($model->enDate);

         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->addRow();         
           if ($res == false)    {$this->redirect(['site/problem']);return;} // ошибка сохранения 
           $this->redirect(['monitor-row-cfg', 'rowType' => $model->rowType, 'noframe' => 1]);
           return;
         }
         
        $provider    = $model->getRowCfgProvider(Yii::$app->request->get());         
        return $this->render('monitor-row-cfg', ['model' => $model, 'provider' =>$provider]);
    }

/*******************************************/    
    public function actionRemoveRow()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $rowRef= intval($request->get('rowRef',0)); //
        $model->removeRow($rowRef);
        $this->redirect('index.php?r=/site/success');
    }

/*******************************************/    
    public function actionMonitorProfitCfg()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $model->rowRef= intval($request->get('rowRef',0)); //
        $model->ownerRowN = 5;
         
        $ownprovider = $model->getOwnerProfitProvider(Yii::$app->request->get());         
        $provider    = $model->getSrcTypeProfitProvider(Yii::$app->request->get());         
        return $this->render('monitor-profit-cfg', ['model' => $model, 'provider' =>$provider, 'ownprovider' =>$ownprovider]);
    }
/*******************************************/    
    public function actionMonitorBankOpCfg()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $model->rowRef= intval($request->get('rowRef',0)); //
        $model->ownerRowN = 5;
         
        $ownprovider = $model->getOwnerBankOpProvider(Yii::$app->request->get());          
        $provider    = $model->getRowBankOpProvider(Yii::$app->request->get());         
        return $this->render('monitor-bank-op-cfg', ['model' => $model, 'provider' =>$provider, 'ownprovider' =>$ownprovider]);
    }

/*******************************************/    
    public function actionMonitorDolgiCfg()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $model->rowRef= intval($request->get('rowRef',0)); //
        $model->ownerRowN = 5;
        
        $ownprovider    = $model->getRowOwnerProvider(Yii::$app->request->get());         
        $provider    = $model->getRowDolgiProvider(Yii::$app->request->get());         
        return $this->render('monitor-dolgi-cfg', ['model' => $model, 'provider' =>$provider, 'ownprovider' =>$ownprovider]);
    }

/*******************************************/    
    public function actionMonitorWareCfg()
    {
       
        $request = Yii::$app->request;  
        $model = new HeadMonitorForm();
        $model->rowRef= intval($request->get('rowRef',0)); //
        $model->ownerRowN = 5;
        
        $ownprovider = $model->getRowWareOwnerProvider(Yii::$app->request->get());         
        $provider    = $model->getRowWareProvider(Yii::$app->request->get());         
        return $this->render('monitor-ware-cfg', ['model' => $model, 'provider' =>$provider, 'ownprovider' =>$ownprovider]);
    }

/*******************************************/    
    public function actionSaveRowCfgData()
    {
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
        $model = new HeadMonitorForm();
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveRowCfgData();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }
    public function actionSaveRowCfg()
    {
        $model = new HeadMonitorForm();
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveRowCfg();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }
/*******************************************/
    public function actionPurchClassifyCfg()
    {        
        $request = Yii::$app->request;  
        $model = new PurchClassifyForm();
        
        $provider    = $model->getClassifyProvider(Yii::$app->request->get());         
        return $this->render('purch-classify-cfg', ['model' => $model, 'provider' =>$provider, ]);
    }

    public function actionAddPurchMask()
    {        
        $request = Yii::$app->request;  
        $model = new PurchClassifyForm();
        $model->addPurchMask();
        $this->redirect('index.php?r=/site/success');
    }
    public function actionRemovePurchMask()
    {        
        $request = Yii::$app->request;  
        $maskRef  = intval($request->get('maskRef',0)); //
      
        $model = new PurchClassifyForm();
        $model->removePurchMask($maskRef);
        $this->redirect('index.php?r=/site/success');
    }

    public function actionSavePurchClassifyData()
    {
        $model = new PurchClassifyForm();
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->savePurchClassifyData();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }

   public function actionPurchClassify()
   {
    $model = new DataSync();        
    $model->purchClassify(0); 
    $this->redirect(['show-purch', 'noframe' => 1]);   
   }

   public function actionShowPurch()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
        
    $sumprovider    = $model->getPurchSumProvider(Yii::$app->request->get());         
    $provider    = $model->getPurchProvider(Yii::$app->request->get());         
    return $this->render('show-purch', ['model' => $model, 'provider' =>$provider, 'sumprovider' => $sumprovider ]);
   }

   public function actionShowPurchByRowRef()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
    $model->rowRef  = intval($request->get('rowRef',0)); //

    $model->stDate= $request->get('stDate',date('Y-m-d', time() - 7*24*3600)); //            
    $model->enDate= $request->get('enDate',date('Y-m-d')); //
        
    $sumprovider    = $model->getPurchSumProvider(Yii::$app->request->get());         
    $provider    = $model->getPurchProvider(Yii::$app->request->get());         
    return $this->render('show-purch', ['model' => $model, 'provider' =>$provider, 'sumprovider' => $sumprovider ]);
   }
/**/
   public function actionShowProfit()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
        
    $sumprovider    = $model->getProfitSumProvider(Yii::$app->request->get());             
    $provider    = $model->getProfitProvider(Yii::$app->request->get());         
    return $this->render('show-profit', ['model' => $model, 'provider' =>$provider, 'sumprovider' =>$sumprovider ]);
    
   }



   public function actionShowProfitByRow()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
    $model->rowRef  = intval($request->get('rowRef',0)); //

    $model->stDate= $request->get('stDate',date('Y-m-d', time() - 7*24*3600)); //            
    $model->enDate= $request->get('enDate',date('Y-m-d')); //
    
    $sumprovider    = $model->getProfitSumProvider(Yii::$app->request->get());             
    $provider    = $model->getProfitProvider(Yii::$app->request->get());         
    return $this->render('show-profit', ['model' => $model, 'provider' =>$provider, 'sumprovider' =>$sumprovider ]);
   }

/**/
   public function actionShowBankOp()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
        
        
    $sumprovider = $model->getBankOpSumProvider(Yii::$app->request->get());                 
    $provider    = $model->getBankOpProvider(Yii::$app->request->get());         
    return $this->render('show-bank-op', ['model' => $model, 'provider' =>$provider, 'sumprovider' => $sumprovider ]);
   }



   public function actionShowBankOpByRow()
   {        
    $request = Yii::$app->request;  
    $model = new ShowSrcForm();
    $model->rowRef  = intval($request->get('rowRef',0)); //

    $model->stDate= $request->get('stDate',date('Y-m-d', time() - 7*24*3600)); //            
    $model->enDate= $request->get('enDate',date('Y-m-d')); //
        
    $sumprovider = $model->getBankOpSumProvider(Yii::$app->request->get());                 
    $provider    = $model->getBankOpProvider(Yii::$app->request->get());         
    return $this->render('show-bank-op', ['model' => $model, 'provider' =>$provider, 'sumprovider' => $sumprovider ]);
   }


/*******************************************/
    public function actionSyncBankOperation()
    {
        $request = Yii::$app->request;          
        $st = $request->get('st', time());        
        $et = $request->get('et', $st+24*3600);        
        
        $sd = date("dmY",$st);        
        $ed = date("dmY",$et);        
        
        $model = new BankOperation();             
        $id = $model->syncOperations($sd, $ed, $st);
                
        $this->redirect('index.php?r=/site/success'); 

    }
/*******************************************/    
/**/
   public function actionBuhActivityControl()
   {        
    $request = Yii::$app->request;  
    $model = new PersonalControlForm();
    $request = Yii::$app->request;          
    
    $stDate=date('Y-m-01'); 
    $enDate=date('Y-m-t');
    
    $model->stDate = $request->get('stDate', $stDate);        
    $model->enDate = $request->get('enDate', $enDate);        
     
    $list=$model->getBuhStatAcivityProvider(Yii::$app->request->get());
      
/*     echo "<pre>";      
     print_r($list);
     echo "</pre>";*/
        
    $provider  = $model->getBuhStatAcivityProvider(Yii::$app->request->get());         
    return $this->render('buh-activity-control', ['model' => $model, 'provider' =>$provider, ]);
   }

   /*******************************************/
   
   public function actionPlanProduction()
   {        
    $request = Yii::$app->request;  
    $model = new PlanProductionForm    ();
    $request = Yii::$app->request;          
        
    $provider  = $model->getGogleDataProvider(Yii::$app->request->get());         
    return $this->render('plan-production', ['model' => $model, 'provider' =>$provider, ]);
   }

   public function actionReadyProduction()
   {        
    $request = Yii::$app->request;  
    $model = new PlanProductionForm    ();
    $request = Yii::$app->request;          
        
    $provider  = $model->getGogleReadyProvider(Yii::$app->request->get());         
    return $this->render('ready-production', ['model' => $model, 'provider' =>$provider, ]);
   }

   public function actionGooglePrice()
   {        
    $request = Yii::$app->request;  
    $model = new GooglePriceForm ();
    $request = Yii::$app->request;          
        
    $provider  = $model->getPriceGogleDataProvider(Yii::$app->request->get());
    $prodProvider  = $model->getProductGogleDataProvider(Yii::$app->request->get());                  
    return $this->render('google-price', ['model' => $model, 'provider' =>$provider, 'prodProvider' => $prodProvider]);
   }

   public function actionGooglePriceSync()
   {        
    $request = Yii::$app->request;  
    $model = new GooglePriceForm ();
    $request = Yii::$app->request;          
        
    $provider  = $model->syncPriceGogleData(Yii::$app->request->get());
    //$prodProvider  = $model->getProductGogleDataProvider(Yii::$app->request->get());                  
    return $this->render('google-price-sync', ['model' => $model, 'provider' =>$provider, /*'prodProvider' => $prodProvider*/]);
   }


/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
       $this->redirect('index.php?r=/site/success');
    }

    public function actionClose()
    {
      $this->redirect('index.php?r=/site/close');
    }
    
}
