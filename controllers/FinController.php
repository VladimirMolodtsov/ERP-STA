<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\FinForm;
use app\models\FinProfitForm;
use app\models\FinBankForm;
use app\models\FinSverkaForm;
use app\models\FinPurchForm;
use app\models\BuhSchetForm;

class FinController extends Controller
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
/************* Финансы ************************/
/*************************************************/
    
    public function actionFinStart()
    {
        
        if (Yii::$app->user->isGuest) $this->redirect(['site/index']);         
        $curUser=Yii::$app->user->identity;
        if (!($curUser->roleFlg & 0x0040)) $this->redirect(['site/index']);         
    

    
          $model = new FinForm();         
         return $this->render('fin-start', ['model' => $model]);
         
    }

      public function actionFinOplataRemove()
    {
        $request =Yii::$app->request;
        $oplataId  = intval($request->get('oplataId',0));                        
        $refSchet = intval($request->get('refSchet',0));                        
        
        if ($oplataId == 0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();                           
        $model -> removeOplata($oplataId, $refSchet);         
        $this->redirect(['site/success']);         
    }


    
    public function actionFinOplata()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    
        
        $model->setMonth = intval($request->get('setMonth',0));                        
        $model->setDate = $request->get('setDate',0);                        

        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOplataListData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getOplataListProvider(Yii::$app->request->get());
         return $this->render('fin-oplata', ['model' => $model,'provider' => $provider]);
    }
    
    
    public function actionFinRequest()
    {
        
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
          $model = new FinForm();         
         
         $provider=$model->getSchetStateProvider(Yii::$app->request->get());
         return $this->render('fin-request', ['model' => $model,'provider' => $provider]);
    }


    public function actionFinConfirm()
    {
        $request =Yii::$app->request;
        $status  = intval($request->get('status',1));                        
        $schetId = intval($request->get('schetId',0));                        
        

        
        if ($schetId == 0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();         
                  
        $model -> setConfirmStatus($schetId, $status);
         
        $this->redirect(['site/success']);         
    }

    /*********** Oplata *************/
    
    public function actionOplataAttach()
    {
        $request =Yii::$app->request;
        $oplataId  = intval($request->get('oplataId',0));                        
        $schetId = intval($request->get('schetId',0));                        
        

        
        if ($schetId == 0 || $oplataId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета или оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();                           
        $model -> oplataAttach($schetId, $oplataId);         
        $this->redirect(['site/success']);         
    }

    public function actionOplataDetach()
    {
        $request =Yii::$app->request;
        $oplataId  = intval($request->get('oplataId',0));                        
        
        if ( $oplataId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();                           
        $model -> oplataDetach($oplataId);         
        $this->redirect(['site/success']);         
    }

    public function actionOplataList()
    {
        
        $request =Yii::$app->request;
        $schetId = intval($request->get('schetId',0));                                
        if ($schetId == 0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета');        
            $this->redirect(['site/problem']);         
            return;
        }

         $model = new FinForm();         
                  
         
        $linkedListProvider=$model->getOplataReestrProvider(Yii::$app->request->get(), $schetId);
        $freeListProvider=$model->getOplataReestrProvider(Yii::$app->request->get(), 0);
        return $this->render('oplata-list', ['model' => $model,'schetId'=> $schetId, 'linkedListProvider' => $linkedListProvider,'freeListProvider' => $freeListProvider]);
    }

    /*********** Extract *************/
    
    public function actionExtractAttach()
    {
        $request =Yii::$app->request;
        $oplataId  = intval($request->get('oplataId',0));                        
        $schetId = intval($request->get('schetId',0));                        
                
                
                
        if ($schetId == 0 || $oplataId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета или оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
        $model = new FinForm();                           
        $model -> extractAttach($schetId, $oplataId);         
        $this->redirect(['site/success']);         
    }

    public function actionExtractDetach()
    {
        $request =Yii::$app->request;
        $oplataId  = intval($request->get('oplataId',0));                     
        $schetId = intval($request->get('schetId',0));                                
        
        if ( $oplataId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();                           
        $model -> extractDetach($schetId, $oplataId);        
        $this->redirect(['site/success']);         
    }

    public function actionSaveExtractLnkData()
    {
        
      if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $model = new FinForm();

        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveExtractLnkData();
                echo json_encode($sendArray);
                return;
            }    
        }
        
    }
    
    public function actionExtractList()
    {
        
        $request =Yii::$app->request;
        $schetId = intval($request->get('schetId',0));                                
        if ($schetId == 0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета');        
            $this->redirect(['site/problem']);         
            return;
        }

        $model = new FinForm();         
         
        $linkedListProvider=$model->getExtractReestrProvider(Yii::$app->request->get(), $schetId);
        $freeListProvider=$model->getExtractReestrProvider(Yii::$app->request->get(), 0);
        return $this->render('extract-list', ['model' => $model,'schetId'=> $schetId, 'linkedListProvider' => $linkedListProvider,'freeListProvider' => $freeListProvider]);
    }

    /*********** Supply *************/
    
    public function actionSupplyAttach()
    {
        $request =Yii::$app->request;
        $supplyId  = intval($request->get('supplyId',0));                        
        $schetId = intval($request->get('schetId',0));                        
        

        
        if ($schetId == 0 || $supplyId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета или поставки');        
            $this->redirect(['site/problem']);         
            return;
        }
        
         $model = new FinForm();                           
        $model -> supplyAttach($schetId, $supplyId);         
        $this->redirect(['site/success']);         
    }

    public function actionSupplyDetach()
    {
        $request =Yii::$app->request;
        $supplyId  = intval($request->get('supplyId',0));                        
        
        if ( $supplyId ==0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор оплаты');        
            $this->redirect(['site/problem']);         
            return;
        }
        
        $model = new FinForm();                           
        $model -> supplyDetach($supplyId);         
        $this->redirect(['site/success']);         
    }

    public function actionSupplyList()
    {
        
        $request =Yii::$app->request;
        $schetId = intval($request->get('schetId',0));                                
        if ($schetId == 0)
        {    
            $session = Yii::$app->session;        
            $session->open();
            $session->set('problemMessage','Не задан идентификатор счета');        
            $this->redirect(['site/problem']);         
            return;
        }

         $model = new FinForm();         
                  
         
        $linkedListProvider=$model->getSupplyReestrProvider(Yii::$app->request->get(), $schetId);
        $freeListProvider=$model->getSupplyReestrProvider(Yii::$app->request->get(), 0);
        return $this->render('supply-list', ['model' => $model,'schetId'=> $schetId, 'linkedListProvider' => $linkedListProvider,'freeListProvider' => $freeListProvider]);
    }

    /*********************************************************/

      public function actionClientSchetSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
        
        $model->ref1C=$request->get('ref1C','');        
        $model->schetDate = $request->get('schetDate',"");
        $model->refSchet=$request->get('refSchet','');        
        
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getClientSchetSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getClientSchetSrcProvider(Yii::$app->request->get());
         return $this->render('client-schet-src', ['model' => $model,'provider' => $provider]);
    }
    
    /*********************************************************/

      public function actionZakazSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getZakazSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getZakazSrcProvider(Yii::$app->request->get());
         return $this->render('zakaz-src', ['model' => $model,'provider' => $provider]);
    }

    /*********************************************************/

      public function actionOplataSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    
        
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->setDate = $request->get('setDate',0);
        $model->fixPeriod();
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOplataSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getOplataSrcProvider(Yii::$app->request->get());
         return $this->render('oplata-src', ['model' => $model,'provider' => $provider]);
    }
    /*********************************************************/

      public function actionSupplySrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->setDate = $request->get('setDate',0);
        $model->fromDate = $request->get('fromDate',0);
        $model->toDate = $request->get('toDate',0);
        $model->wareListRef = $request->get('wareRef',0);
        $model->orgRef= $request->get('orgRef',0);
             
        if (!empty($model->setDate))
        {
            if (empty($model->fromDate)) $model->fromDate = $model->setDate;
            if (empty($model->toDate))   $model->toDate   = $model->setDate;
        }
       
        $model->fixPeriod();
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getSupplySrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getSupplySrcProvider(Yii::$app->request->get());
         return $this->render('supply-src', ['model' => $model,'provider' => $provider]);
    }
    
    /*********************************************************/

      public function actionSupplierOplataSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

         
        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->setDate = $request->get('setDate',0);        
        $model->fixPeriod();
        
        $model->refSuppSchet = $request->get('refSuppSchet',0);        
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getSupplierOplataSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getSupplierOplataSrcProvider(Yii::$app->request->get());
         return $this->render('supplier-oplata-src', ['model' => $model,'provider' => $provider]);
    }
    
    /*********************************************************/

      public function actionSupplierWaresSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->fixPeriod();
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getSupplierWaresSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getSupplierWaresSrcProvider(Yii::$app->request->get());
         return $this->render('supplier-wares-src', ['model' => $model,'provider' => $provider]);
    }
    

    /*********************************************************/
    

      public function actionSupplierSchetSrc()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
         $request =Yii::$app->request;      
         $model = new FinForm();    

        $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        $model->refSuppSchet= intval($request->get('id',0));
        $model->fixPeriod();
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getSupplierSchetSrcData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
            
         $provider=$model->getSupplierSchetSrcProvider(Yii::$app->request->get());
         return $this->render('supplier-schet-src', ['model' => $model,'provider' => $provider]);
    }
    
    /*********************************************************/
    

     public function actionProfitSrc()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinProfitForm();
        $model->strDate= $request->get('strDate',date('Y-m-d')); 

        $provider = $model->getProfitSrcProvider(Yii::$app->request->get());
        return $this->render('profit-src', ['model' => $model, 'provider' => $provider, ]);
    }
    /*********************************************************/
    

     public function actionPurchSrc()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinPurchForm();
        $model->strDate= $request->get('strDate',date('Y-m-d')); 

        $provider = $model->getPurchSrcProvider(Yii::$app->request->get());
        return $this->render('purch-src', ['model' => $model, 'provider' => $provider, ]);
    }
    
    /*********************************************************/
     public function actionBankSrc()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinBankForm();
        $model->strDate= $request->get('strDate',date('Y-m-d')); 

        $provider = $model->getBankControlProvider(Yii::$app->request->get());
        return $this->render('bank-src', ['model' => $model, 'provider' => $provider, ]);
    }
    
    
   public function actionSwitchBankUse()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new FinBankForm();
     $id = intval($request->get('id',0)); 
     
      $model->switchBankUse($id);  
      
      $this->redirect(['site/success']);
      return;
   }

     /*********************************************************/
     public function actionSverkaUse()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinSverkaForm();
        $tmDate= strtotime($request->get('strDate',date('Y-m-d'))); 
        $model->strDate = date('Y-m-d', $tmDate);
        $model->orgFlt = intval($request->get('orgFlt',0)); 
        $model->useAll = intval($request->get('useAll',0)); 
        $model->ftType=$request->get('ftType','showAll');
        
        /*$model->isInUse = intval($request->get('isInUse',0));
        $model->isBlack = intval($request->get('isBlack',0));
        $model->isOther = intval($request->get('isOther',0));
        $model->isService = intval($request->get('isService',0));        
        $model->isBank = intval($request->get('isBank',0));*/    
     
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveSverkaRecord();         
                if ($res['res'] == false)    {$this->redirect(['site/problem']);return;} // ошибка сохранения 
                $this->redirect(['fin/sverka-use', 'noframe' => 1, 'strDate' => $model->strDate]);
           return;
         }

        
        
        $fltProvider = $model->getSverkaFltProvider(Yii::$app->request->get());
        $provider    = $model->getSverkaProvider(Yii::$app->request->get());
        return $this->render('sverka-use', ['model' => $model, 'provider' => $provider, 'fltProvider' =>$fltProvider ]);
    }

   public function actionSwitchSverkaFlt()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new FinSverkaForm();
     $id = intval($request->get('id',0)); 
     
      $model->switchSverkaFlt($id);  
      $this->redirect(['site/success']);
      return;
   }


    public function actionSaveSverkaRecord()
    {
         $model = new FinSverkaForm();        

       // if(Yii::$app->request->isAjax)
        {
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
             $sendArray = $model->saveSverkaRecord();                  
             echo json_encode($sendArray);
                return;            
         }           
        }
        
    }

    /*********************************************************/    
    /*********************************************************/

     public function actionBuhSchet()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new BuhSchetForm();
        $model->stDate= $request->get('stDate',0); //за месяц
        $model->enDate= $request->get('enDate',0); 

        if ($model->stDate != 0 && $model->enDate !=0)
        {
         if ($model->checkData() == 0) {$this->redirect(['data/sync-buh-schet', 'stDate' =>$model->stDate, 'enDate' =>$model->enDate ]);
         return;   }     
        }
                
        return $this->render('buh-schet', ['model' => $model, ]);
    }
    
     public function actionBuhSchetCfg()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
                
        $request    = Yii::$app->request;          
        $model      = new BuhSchetForm();
        $model->id  = intval($request->get('id',0)); 
        $model->loadData();
        
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveData();         
                if ($res == false)    {$this->redirect(['site/problem']);return;} // ошибка сохранения 
                $this->redirect(['site/success']);
                return; // успешно завершена работа 
                
           //return $this->redirect(['market-schet-frame','noframe' => 1, 'id' => $model->id]); // другое
           //$this->redirect(['site/success']);
           return;
         }
        $provider    = $model->getBuhSchetCfgProvider(Yii::$app->request->get());        
        return $this->render('buh-schet-cfg', ['model' => $model, 'provider' =>$provider]);
    }
    
     public function actionBuhSchetAdd()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new BuhSchetForm();
        $isCredit  = intval($request->get('isCredit',0)); 
        $model ->addNewRow($isCredit);        
        $this->redirect(['site/success']);
        return;
    }

    
    public function actionSwitchSchetUse()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new BuhSchetForm();
        $rowRef     = intval($request->get('rowRef',0));
        $reportRef  = intval($request->get('reportRef',0));  
        $model ->switchSchetUse($reportRef, $rowRef );        
        $this->redirect(['site/close']);
        return;
    }
        
        
       
        
    /*********************************************************/    
    /*********************************************************/
    

     public function actionOplataReestr()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }


         $curUser=Yii::$app->user->identity;
         if ($curUser->roleFlg & 0x0020) 
         {
           $this->redirect(['head/oplata-reestr']);         
         }

        
         $request =Yii::$app->request;      
         $model = new FinForm();    
        $model->id=intval($request->get('id',0));
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOplateReestrData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
   
         $provider=$model->getOplateReestrProvider(Yii::$app->request->get());
         return $this->render('fin-oplata-reestr', ['model' => $model,'provider' => $provider]);
    }
    

    /*********************************************************/    
      public function actionAddInReestr()
    {

         $request =Yii::$app->request;              
         $model = new FinForm();                           
        $id = $model -> addNewInReestr();         
        $this->redirect(['fin/oplata-reestr', 'id' => $id]);         
    }
    
    /*********************************************************/    
      public function actionRmFromReestr()
    {

         $request =Yii::$app->request;      
        
         $model = new FinForm();                      
        $model->id=intval($request->get('id',0));        
        $model -> rmFromReestr($model->id);         
        $this->redirect(['site/success']);        
    }

    /*********************************************************/
    
    public function actionSchetListReestr()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         //$model->supplierRef= intval($request->get('supplierRef',0)); 
         
         $provider = $model->getSchetListReestr(Yii::$app->request->get());
         
         
         return $this->render('schet-list-reestr', ['model' => $model, 'provider'=> $provider]);         
    }

    /*********************************************************/
    
    public function actionMultiSchetListReestr()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         //$model->supplierRef= intval($request->get('supplierRef',0)); 
         
         $provider = $model->getSchetListReestr(Yii::$app->request->get());
         
         
         return $this->render('multi-schet-list-reestr', ['model' => $model, 'provider'=> $provider]);         
    }
    
    
    /*******************************************************************************/      

    public function actionOplataListReestr()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $model->reestrId=intval($request->get('reestrId',0));   
         $provider = $model->getOplateListReestr(Yii::$app->request->get());
         $providerAttached = $model->getAttachedOplateListReestr(Yii::$app->request->get());
         return $this->render('oplata-list-reestr', ['model' => $model, 'provider'=> $provider, 'providerAttached' => $providerAttached]);         
    }

    /*******************************************************************************/      

    public function actionReestrSetFormdate()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetFormdate($id, $val);         
           $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetOplatedate()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetOplatedate($id, $val);        
           $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetNote()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetNote($id, $val);         
           $this->redirect(['site/success']);        

    }
    
    /*******************************************************************************/      

    public function actionReestrSetOplatetype()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetOplateType($id, $val);         
           $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetSummoplate()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetSummOplate($id, $val);         
           $this->redirect(['site/success']);        

    }
    /*******************************************************************************/      

    public function actionReestrSetSummrequest()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetSummRequest($id, $val);              
           $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetOrg()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= $request->get('val'); 
         
         $model -> setReestrSetOrg($id, $val);         
           $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetSchet()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $schetId= $request->get('schetId'); 
         
         $model -> setReestrSchet($id, $schetId);         
           $this->redirect(['site/success']);        

    }
    
    public function actionReestrSetMultiSchet()    
   {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $schetListId= $request->get('schetListId'); 
         
         $model -> setReestrMultiSchet($schetListId);         
           $this->redirect(['site/success']);        
    }
   
    /*******************************************************************************/      

    public function actionReestrSetOplata()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $reestrId= intval($request->get('reestrId',0)); 
         $oplateId= $request->get('oplateId'); 
         
         $model -> setReestrOplata($reestrId, $oplateId);    
        
           $this->redirect(['site/success']);        

    }
   
    public function actionDetachFromReestr()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $reestrId= intval($request->get('id',0)); 
         $oplateId= intval($request->get('linkId',0)); 
         $model->detachReestrOplata($reestrId, $oplateId);         
         
         $this->redirect(['site/success']);        

    }

    /*******************************************************************************/      

    public function actionReestrSetPlan()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= floatval( $request->get('val',0)); 
         
         $model -> setReestrPlan($id, $val);         
           $this->redirect(['site/success']);        

    }
    /*********************************************************/    
      public function actionReestrFinit()
    {

         $request =Yii::$app->request;      
        
         $model = new FinForm();                      
        $model->id=intval($request->get('id',0));        
        $model -> reestrFinit($model->id);    
        $this->redirect(['site/success']);        
    }

    /*********************************************************/
    public function actionReestrSetLnkoplate()
    {   
    
         $model = new FinForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $val= floatval( $request->get('val',0)); 
         
         $model -> setReestrLnkOplateVal($id, $val);         
           $this->redirect(['site/success']);        

    }
    /*********************************************************/    
    


    
    
}
