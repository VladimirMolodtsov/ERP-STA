<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\FinForm;
use app\models\HeadForm;
use app\models\HeadCfgControl;
use app\models\ColdForm;
use app\models\DeliversForm;
use app\models\ColdInitSelectForm;
use app\models\SupplyForm;
use app\models\HeadControl;
use app\models\HeadClientActivity;
use app\models\HeadOrgSearch;
use app\models\ContractsForm;
use app\models\ContractsEditForm;
use app\models\EventRegForm;
use app\models\MailForm;
use app\models\UserInfoForm;
use app\models\SdelkaForm;

use app\models\ExportToWord;

class HeadController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
/*************************************************/
/************* Старт ************************/
/*************************************************/
    
    public function actionHeadStart()
    {
        
        if (Yii::$app->user->isGuest )
        {
            $this->redirect(['site/index']);         
        }

         $curUser=Yii::$app->user->identity;
         if (!($curUser->roleFlg & 0x0020)) { return $this->render('index');}

        
          $model = new HeadForm();     
         return $this->render('head-start', ['model' => $model]);
         
    }
    
    
    public function actionHeadSale()
    {
       if (Yii::$app->user->isGuest )
        {
            $this->redirect(['site/index']);         
        }

         $curUser=Yii::$app->user->identity;
         if (!($curUser->roleFlg & 0x0020)) { return $this->render('index');}
         
         $request = Yii::$app->request; 
         $model = new HeadForm();
         $cold_model = new ColdForm();
         $cold_view_model = new ColdInitSelectForm();

    
         $detail = intval($request->get('detail',1));
         $format = $request->get('format','html');
         
         $model -> detail = $detail;
        if ($format == 'csv')
        {
            if      ($detail < 9)      {
                $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
                return;            
            }                    
            elseif ($detail == 9)   $detailFile = $model->getContactListData(Yii::$app->request->get()); 
            elseif ($detail == 10)  $detailFile = $model->getLostListData(Yii::$app->request->get()); 
            elseif ($detail == 11)  $detailFile = $model->getContactListData(Yii::$app->request->get());     
            elseif ($detail == 12)  $detailFile = $cold_view_model->getData(Yii::$app->request->get());
            elseif ($detail == 13)    {
                $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
                return;            
            }                    
            elseif ($detail == 15)  $detailFile = $model->getSavedClientReestrData(Yii::$app->request->get());                 

            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        if ($detail < 9)       {
        $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
        return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        }
        elseif ($detail == 9)  $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        elseif ($detail == 10) $detailProvider = $model->getLostListProvider(Yii::$app->request->get());
        elseif ($detail == 11) $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        elseif ($detail == 12) $detailProvider = $cold_view_model->search(Yii::$app->request->get());
        elseif ($detail == 13) {
        $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
        return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        }
        elseif ($detail == 15) $detailProvider = $model->getSavedClientReestrProvider(Yii::$app->request->get());        
        else
        {
            
//            $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
//            return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        
        }    
         return $this->render('head-sale', ['model' => $model, 'detail'=> $detail, 'cold_model' => $cold_model, 'cold_view_model' => $cold_view_model, 'detailProvider' => $detailProvider]); 
    }
    
    
public function actionUpdateReestrClient()    
{
  $this->redirect(['data/progress', 'nextForm' => 'head/load-reestr-client']);         
}

public function actionLoadReestrClient()    
{
  $model = new HeadForm();
  $model->fillClientReestrData();
  $this->redirect(['head/head-sale', 'detail' => 15]);         
}

    
/***********************/    
    public function actionHeadActivity()
    {
       if (Yii::$app->user->isGuest )
        {
            $this->redirect(['site/index']);         
        }

         $curUser=Yii::$app->user->identity;
         if (!($curUser->roleFlg & 0x0020)) { return $this->render('index');}
         
         $request = Yii::$app->request; 
         $model = new HeadForm();
         $cold_model = new ColdForm();
         $cold_view_model = new ColdInitSelectForm();

    
         $detail = intval($request->get('detail',1));
         $format = $request->get('format','html');
         
         $model -> detail = $detail;
        if ($format == 'csv')
        {
            if      ($detail < 9)   {
                $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
                return;            
            }                    
            elseif ($detail == 10)  $detailFile = $model->getLostListData(Yii::$app->request->get()); 
            elseif ($detail == 11)  $detailFile = $model->getContactListData(Yii::$app->request->get());     
            elseif ($detail == 12)  $detailFile = $cold_view_model->getData(Yii::$app->request->get());
            elseif ($detail == 13)    {
                $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
                return;            
            }                    
            elseif ($detail == 15)  $detailFile = $model->getClientReestrData(Yii::$app->request->get());                 

            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        if ($detail < 9)       {
        $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
        return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        }        
        elseif ($detail == 9)  $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        elseif ($detail == 10) $detailProvider = $model->getLostListProvider(Yii::$app->request->get());
        elseif ($detail == 11) $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        elseif ($detail == 12) $detailProvider = $cold_view_model->search(Yii::$app->request->get());
        elseif ($detail == 13) {
        $this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
        return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        }
        elseif ($detail == 15) 
        {
            $detailProvider = $model->getClientReestrProvider(Yii::$app->request->get());
        
        }
        else
        {
             
        //$this->redirect(['sdelka-list', 'detail' => $detail, 'format' => $format]);             
        //return;
            //$detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        
        }    
         return $this->render('head-activity', ['model' => $model, 'detail'=> $detail, 'cold_model' => $cold_model, 'cold_view_model' => $cold_view_model, 'detailProvider' => $detailProvider]); 
    }
    
    /*************/
    public function actionOplataReestr()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }


         $curUser=Yii::$app->user->identity;
         if (!($curUser->roleFlg & 0x0020)) 
         {
           $this->redirect(['fin/oplata-reestr']);         
         }

        
        $request =Yii::$app->request;      
        $model = new FinForm();    
        $model->id=intval($request->get('id',0));
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOplateReestrCSV(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    

        if ($format == 'print')
        {
             $dataList = $model->getOplateReestrPrint(Yii::$app->request->get());     
             return $this->render('oplata-reestr-print', ['model' => $model,'dataList' => $dataList]);
        }    
        
           
         $provider=$model->getOplateReestrProvider(Yii::$app->request->get());
         return $this->render('oplata-reestr', ['model' => $model,'provider' => $provider]);
    }

    /*******************************************************************************/      
    public function actionReestrDetail()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    
         $model->id=intval($request->get('id',0));
        
        
        //$schetProvider=$model->getReestrSchetDetail();
        return $this->render('reestr-detail', ['model' => $model]);
    }
    
    /*******************************************************************************/      
      public function actionDeliverWaresFinit()
    {

        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
                
         $request =Yii::$app->request;      
         $model = new DeliversForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
        
    /*    $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getFinishedDeliversListProvider(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
      */      
         $provider=$model->getFinishedDeliversListProvider(Yii::$app->request->get());
         return $this->render('deliver-wares-finit', ['model' => $model,'provider' => $provider]);
    }

      /*******************************************************************************/      
    public function actionSupplyRequestList()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $model = new HeadForm(); 
         $provider=$model->getSupplyRequestListProvider(Yii::$app->request->get());
         return $this->render('supply-request-list', ['model' => $model,'provider' => $provider]);
    }
    
    public function actionSupplyRequestAccept()
    {
        
         $model = new SupplyForm(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->acceptSupplyRequest($id);             
         $this->redirect(['site/success']);       
     }
        
    public function actionSupplyRequestUnaccept()
    {
        
         $model = new SupplyForm(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->unAcceptSupplyRequest($id);             
         $this->redirect(['site/success']);       
     }
  
    public function actionSupplyRequestReject()
    {
        
         $model = new SupplyForm(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->rejectSupplyRequest($id);             
         $this->redirect(['site/success']);       
     }

   /*******************************************************************************/      
    public function actionBuhControl()
    {

        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
                
       $request =Yii::$app->request;      
       $model = new HeadControl();    
       
       return $this->render('buh-control', ['model' => $model]);
    }
 
 
   /*******************************************************************************/      
    public function actionCfgScladControl()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadCfgControl();    
       $provider = $model->getControlRemainsCfgProvider(Yii::$app->request->get());
       return $this->render('cfg-sclad-control', ['model' => $model,'provider' => $provider]);
    }

    public function actionSwitchControlRemainReal()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlRemainReal($id);             
         $this->redirect(['site/success']);       
     }
         
    public function actionSwitchControlRemainAll()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlRemainAll($id);             
         $this->redirect(['site/success']);       
     }
   /*******************************************************************************/      
    public function actionCfgSverkaControl()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadCfgControl();    
       $providerInUse   = $model->getControlSverkaUseCfgProvider(Yii::$app->request->get());
       $providerIsBlack = $model->getControlSverkaBlackCfgProvider(Yii::$app->request->get());
       return $this->render('cfg-sverka-control', ['model' => $model,'providerInUse' => $providerInUse, 'providerIsBlack' => $providerIsBlack]);
    }

    public function actionSwitchControlSverkaUse()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlSverkaUse($id);             
         $this->redirect(['site/success']);       
     }
             
    public function actionSwitchControlSverkaBlack()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlSverkaBlack($id);             
         $this->redirect(['site/success']);       
     }
     
    /*******************************************************************************/      
    public function actionCfgBankControl()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadCfgControl();    
       $provider = $model->getControlBankCfgProvider(Yii::$app->request->get());
       return $this->render('cfg-bank-control', ['model' => $model,'provider' => $provider]);
    }

    public function actionSwitchControlBankReal()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlBankReal($id);             
         $this->redirect(['site/success']);       
     }
         
    public function actionSwitchControlBankAll()
    {        
         $model = new HeadCfgControl(); 
         $request =Yii::$app->request;     
         $id=intval($request->get('id',0));
         $model ->switchControlBankAll($id);             
         $this->redirect(['site/success']);       
     }
            
    
    
    /*******************************************************************************/      
    /****************  Коммерческий директор             ***************************/      
    /*******************************************************************************/      

    public function actionMarketHead()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
        $model = new HeadForm();            
        $mailModel = new MailForm(); 
        return $this->render('market-head', ['model' => $model,'mailModel' => $mailModel]);
    }
    /*******************************************************************************/
    public function actionClientReestr()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadForm();           
       $model->fltCategory = intval($request->get('fltCategory',0));
       
       $provider = $model->getSavedClientReestrProvider(Yii::$app->request->get());       
       return $this->render('client-reestr', ['model' => $model,'provider' => $provider]);
    }

    /*******************************************************************************/
    /*******************************************************************************/
    public function actionClientWareFlt()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadOrgSearch();
       
       $model->wareTypes=$request->get('wareTypes','');
       $model->wareGrp=$request->get('wareGrp','');
       $model->wareList=$request->get('wareList','');
       
       $provider = $model->getWareFilterProvider(Yii::$app->request->get(), 0);       
       return $this->render('client-ware-flt', ['model' => $model,'provider' => $provider]);
    }
    /*******************************************************************************/
    public function actionClientSearch()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadOrgSearch();

       $model->wareTypes=$request->get('wareTypes','');
       $model->wareGrp=$request->get('wareGrp','');
       $model->wareList=$request->get('wareList','');
       $model->orgFilter=$request->get('orgFilter','');
       $model->fltManager=$request->get('fltManager','');
       $model->fltForm=$request->get('fltForm','');
       
       $model->curOrgJobList=$request->get('curOrgJobList',0);

       $model->parseFilter();
       $setType=$request->get('setType','');
       $setId=intval($request->get('setId',0));
       $setValue=$request->get('setVal',0);

              
       if ($model->chngFilter($setType, $setId, $setValue))
       {
        $this->redirect(['client-search', 'orgFilter' => $model->orgFilter,'wareTypes' => $model->wareTypes, 
        'wareGrp' => $model->wareGrp,'curOrgJobList' => $model->curOrgJobList, 'fltManager' => $model->fltManager,
        'fltForm' => $model->fltForm
        ]);
        return;
       }
//print_r($setType);
//print_r($model->wareGrp);
//       return;

       $provider = $model->getSavedClientReestrProvider(Yii::$app->request->get());       
       return $this->render('client-search', ['model' => $model,'provider' => $provider]);
    }
    

/**********************/    
    

    public function actionSaveOrgJobData()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);

       $model = new HeadOrgSearch();
        // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveOrgJobData();
                echo json_encode($sendArray);
                return;
            }    
        }
    }


/*********************/
    public function actionOrgWareSupply  ()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);
       $request =Yii::$app->request;
       $model = new HeadOrgSearch();
       $refOrg=$request->get('refOrg',0);
       $refWare=$request->get('refWare',0);

       $provider = $model->getOrgWareSupplyProvider($refOrg, $refWare);
       return $this->render('org-ware-supply', ['model' => $model,'provider' => $provider]);
    }

/*********************/        
    public function actionOrgJobListReestr    ()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadOrgSearch();
              
       $provider = $model->getOrgJobListReestrProvider(Yii::$app->request->get());       
       return $this->render('org-job-list-reestr', ['model' => $model,'provider' => $provider]);
    }
    
    public function actionOrgJobList   ()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadOrgSearch();
       $model->curOrgJobList=$request->get('curOrgJobList',0);
              
       $provider = $model->getOrgJobListProvider(Yii::$app->request->get());       
       return $this->render('org-job-list', ['model' => $model,'provider' => $provider]);
    }

/*********************/    
    public function actionOrgSetCategory()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);
       $request =Yii::$app->request;
       $orgRef=intval($request->get('orgRef',0));
       $cat=intval($request->get('cat',0));

       $model = new HeadForm();
       $sendArray =$model->orgSetCategory($orgRef, $cat);
          if(Yii::$app->request->isAjax){
             echo json_encode($sendArray);
             return;
         }
        echo "<pre>";
        print_r($sendArray);
        echo "</pre>";
    }
    /*******************************************************************************/
    /*******************************************************************************/
    public function actionOrgCfgCategory()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadForm();           
       
       $provider = $model->getOrgCategoryProvider(Yii::$app->request->get());       
       return $this->render('cat-list-cfg', ['model' => $model,'provider' => $provider]);
    }
    /*******************************************************************************/
    public function actionSaveCatCfg()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
        // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveCfgData();
                echo json_encode($sendArray);
                return;
            }    
        }
    } 
    


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    public function actionSdelkaList()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new SdelkaForm();
       
       $model->detail = intval($request->get('detail',0));
       $model->format = $request->get('format','html');
         
       $from = $request->get('from',date("Y-m-d",time()-90*24*3600));
       $to   = $request->get('to',date("Y-m-d"));
      
       $model->frm_time=strtotime($from);
       $model->to_time=strtotime($to);
       
       
       $statmodel = $model;//new HeadForm();
       $leafValue=$statmodel->getLeafValue();
       
       
       $provider = $model->getCurrentDealProvider(Yii::$app->request->get());
       
       return $this->render('sdelka-list', ['model' => $model,'provider' => $provider, 'leafValue' => $leafValue]);
    }
        
       
    
    public function actionClientActivity()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadClientActivity();    
       $model->format=$request->get('format','html');
       if ($model->format == 'csv')
       {
            $detailFile = $model->getClientActivityCSV(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;           
       }
       
       $provider = $model->getSavedClientActivityProvider(Yii::$app->request->get());       
       return $this->render('client-activity', ['model' => $model,'provider' => $provider]);
    }
        
   public function actionUpdateClientActivity()    
   {
     $this->redirect(['data/progress', 'nextForm' => 'head/load-activity-client']);         
   }

   public function actionLoadActivityClient()    
   {
     $model = new HeadForm();
     $model->fillClientReestrData();
     $this->redirect(['head/client-activity', 'detail' => 15]);         
   }
  
  
     public function actionContractsList()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new ContractsForm();           
       $provider = $model->getContractsListProvider(Yii::$app->request->get());
       
       return $this->render('contracts-list', ['model' => $model,'provider' => $provider]);
    }

    public function actionContractNew()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new ContractsEditForm();           
       $model->refOrg=intval($request->get('refOrg',0));
       if ($model->load(Yii::$app->request->post()) && $model->validate())
       {
           $model->saveDataNew();
           $this->redirect(['site/success']);            
       }

       return $this->render('contract-new', ['model' => $model]);
    }
    
    public function actionGetContractNumber()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new ContractsEditForm();           
        $sendArray =$model->getContructNumber();         
          if(Yii::$app->request->isAjax){
             echo json_encode($sendArray);
             return; 
         }
        echo "<pre>";  
        print_r($sendArray);
        echo "</pre>";
    }
    
     public function actionContractEdit()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new ContractsEditForm();           
       $model->id=intval($request->get('id',0));
       if ($model->load(Yii::$app->request->post()) && $model->validate()) 
       {
           $model->saveData();                      
           $this->redirect(['site/success']);            
       }

       return $this->render('contract-edit', ['model' => $model]);
    }

    public function actionEventLog()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));

        return $this->render('event-exec', ['model' => $model, 'date' => $date]);
    }   

    public function actionEventExecDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        return $this->render('event-exec-detail', ['model' => $model, 'date' => $date, 'userId' => $id]);
    }   

    public function actionEventExecWeek()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        return $this->render('event-exec-week', ['model' => $model, 'date' => $date, 'userId' => $id]);
    }   


    public function actionEventExecShort()
    {    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();        
        $date = $request->get('date', date('Y-m-d'));
        $id   = intval($request->get('id', 0 ));

        return $this->render('event-exec-short', ['model' => $model, 'date' => $date, 'userId' => $id]);
    }   
   /***/
    public function actionActiveSdelka()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new HeadForm();    
       
       $format = $request->get('format','html');
         
        if ($format == 'csv')
        {        
            $reportFile = $model->getActiveSdelkaData(Yii::$app->request->get());                 
            $url = Yii::$app->request->baseUrl."/../".$reportFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    

       
       $provider = $model->getActiveSdelkaProvider(Yii::$app->request->get());
       return $this->render('active-sdelka', ['model' => $model,'provider' => $provider]);
    }

    
    public function actionSwitchUserRptMail()
    {    
        $request = Yii::$app->request;    
        $model=  new UserInfoForm();                
        $id   = intval($request->get('id', 0 ));
        $sendArray = $model->switchUserRptMail($id);

        if(Yii::$app->request->isAjax)
        {
                echo json_encode($sendArray);
                return;
        }
        
       $this->redirect(['site/success']);            
    }   

    /********************************/

   public function actionPrintContract()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new ContractsEditForm();
        $request = Yii::$app->request;

        $format = $request->get('format','html');
        $id = $request->get('id', 0);

        $html = $model->prepareContractDoc($id);

        if ($format == 'doc')
        {

        $css= $model->getContractCss();
        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
        $curUser=Yii::$app->user->identity;
        $fname = 'contract_'.$curUser->id;

        $mask = realpath(dirname(__FILE__))."/../uploads/".$fname."*.doc";
        array_map("unlink", glob($mask));
        $fname = $fname."_".time().".doc";
        $filePath = $uploadPath.$fname;
         ExportToWord::htmlToDoc($html, $css, $filePath, 'UTF-8', 0);
         $url = Yii::$app->request->baseUrl."/../uploads/".$fname;

         $this->redirect(['/site/download', 'url' => $url]);
         return;
        }
       else echo $html;

       exit (0);
        return;
    }



  /***/
}

