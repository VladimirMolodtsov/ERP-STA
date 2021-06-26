<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\bootstrap\Html;
use yii\filters\VerbFilter;
use yii\base\Model;

use app\models\ColdForm;
use app\models\MarketSchetForm;
use app\models\MarketPrintForm;
use app\models\MarketZakazForm;
use app\models\MarketCalendarForm;
use app\models\MarketViewForm;
use app\models\MarketNewOrgForm;
use app\models\MarketingForm;
use app\models\OrgList;
use app\models\OrgDetail;
use app\models\MarketGoodsForm;
use app\models\MarketPriceForm;
use app\models\MarketSchetAct;
use app\models\WarehouseForm;
use app\models\DataSyncGoogle;
use app\models\HeadForm;
use app\models\MailForm;

use app\models\ExportToWord;

use app\models\ClientSchetForm;

class MarketController extends Controller
{
    /**
     * @inheritdoc
     */
    public function dropUse()
    {
        $validTimeStamp = time()-60*60*24*5;   
        $strSql="UPDATE {{%orglist}} set isInWork=0 where isInWork>0 AND startTimeInWork < ".$validTimeStamp;
        Yii::$app->db->createCommand($strSql)->execute();
    
    }
    public function setUse($id)
    {
     $this->dropUse();
     $record = OrgList::findOne($id);    
     if (empty($record)) return;
     $curUser=Yii::$app->user->identity;
/*     if ($record->isInWork == 1)         
     {
         
            if(isset($record->ref_user) && ($record->ref_user != $curUser->id)) 
            {
              $this->redirect(['site/org-inuse', 'id' => $id]);            
            }
     }
*/     
         $record->ref_user = $curUser->id;
         $record->isInWork = 1; 
         $record->startTimeInWork = time();
         $record->save();
         
     return $record;
    }   
     
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionMarketStart()
    {
        /*Сбрасываем в работе*/
        //if (Yii::$app->user->isGuest == true){return $this->goHome();}

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $curUser=Yii::$app->user->identity;
        if (!(($curUser->roleFlg & 0x0002) ||  ($curUser->roleFlg & 0x0004) || ($curUser->roleFlg & 0x0080))) $this->redirect(['site/index']);         

        $mailModel = new MailForm(); 
        
        $validTimeStamp = time()-60*30;   
        $strSql="UPDATE {{%orglist}} set isInWork=0 where isInWork>0 AND startTimeInWork < ".$validTimeStamp;
        Yii::$app->db->createCommand($strSql)->execute();
        /***/

         $model = new MarketCalendarForm();
         $request = Yii::$app->request; 
                 
         $model->mode = intval($request->get('mode', 1));
         $model->show = intval($request->get('show', 0));    
         if (empty ($model->mode)){$model->mode=1;}
         $model->tab = intval($request->get('tab'));
         $model->detail = intval($request->get('detail',7));
         $model->filtDate = ($request->get('filtDate'));
        
         $tab = intval($request->get('tab', 1));
         if (empty($tab)) {$tab = 1;}
         
         $model->type= intval($request->get('type', 1));
         

         if (!empty($model->filtDate))
         {
             $filtTime=strtotime($model->filtDate);
             
         $model->d =date("d",$filtTime);
         $model->m =date("m",$filtTime);
         $model->y =date("Y",$filtTime);
         }
         
         if(!empty($request->get('d')))$model->d =intval($request->get('d')); 
         if(!empty($request->get('m')))$model->m =intval($request->get('m'));
         if(!empty($request->get('y')))$model->y =intval($request->get('Y'));
         
         if (empty($model->d))$model->d = date("d");
         if (empty($model->m))$model->m = date("m");
         if (empty($model->y))$model->y = date("Y");
         
         $this->dropUse();
    
         if ($model->mode == 2)
         {
             $model->mode = 1;
             $model->userShow = 1;
         }
         
         switch ($model->mode)         
         {
             case 1:             
                   if ($model->type < 4) { $provider = $model->getCurrentDealProvider(Yii::$app->request->get());}                   
               elseif ($model->type < 7) { $provider=$model->getNonActiveClientListProvider(Yii::$app->request->get());}
               elseif ($model->type == 10){ $provider=$model->getClientListProvider(Yii::$app->request->get());} //
               elseif ($model->type == 11){ $provider=$model->getClientListProvider(Yii::$app->request->get());} //
                                    else { $provider = $model->getDetailProvider(Yii::$app->request->get()); }        
                                    
             return $this->render('market-start-m1', ['model' => $model,  'provider' => $provider,'tab' => $tab, 'mailModel' => $mailModel]);
             break;
             
             case 3:
             $detailProvider = $model->getDetailProvider(Yii::$app->request->get());         
             return $this->render('market-start', ['model' => $model,  'detailProvider' => $detailProvider, 'tab' => $tab]);
             break;
             
             default:
             $detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());                      
             return $this->render('market-start-m1', ['model' => $model,  'detailProvider' => $detailProvider, 'tab' => $tab]);
             break;
             
         }
        
         return $this->render('market-start', ['model' => $model,  'detailProvider' => $detailProvider, 'tab' => $tab]);
    }

/********************************/
    public function actionMarketKanban()
    {                  
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request; 
         $model = new HeadForm();
         $marketModel = new MarketCalendarForm();
         
         $detail = intval($request->get('detail',1));
         $model->mode = intval($request->get('mode',2));
         $format = $request->get('format','html');
         
         $model -> detail = $detail;
        if ($format == 'csv')
        {
            return;
        /*    if      ($detail < 9)   $detailFile = $model->getCurrentDealData(Yii::$app->request->get());     
            elseif ($detail == 9)   $detailFile = $model->getContactListData(Yii::$app->request->get()); 
            
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;*/
        }    
         
        if ($detail < 9)       $detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        elseif ($detail == 9)  $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        else
        {
             $detailProvider = $model->getCurrentDealProvider(Yii::$app->request->get());         
        }    
         return $this->render('market-kanban', ['model' => $model, 'detail'=> $detail, 'marketModel' => $marketModel, 'detailProvider' => $detailProvider]); 
    }
    
/******************************************************************/
    public function actionCreateSchet()
    {   
         $request = Yii::$app->request; 
         $zakazId = intval($request->get('zakazId',0));
         $orgRef = intval($request->get('orgId',0));        
         $model = new MarketSchetForm();
         
         $sendArray = $model->createSchet($zakazId, $orgRef);
         if(Yii::$app->request->isAjax)
         {                
                echo json_encode($sendArray);
                return;
         }
         $this->redirect(['site/success']);
         return;
    }
    
    public function actionCreateLead()
    {   
         $request = Yii::$app->request; 
         $zakazId = intval($request->get('zakazId',0));         
         $model = new MarketZakazForm();         
         $sendArray = $model->createLead($zakazId);
         if(Yii::$app->request->isAjax)
         {                
                echo json_encode($sendArray);
                return;
         }
         $this->redirect(['site/success']);
         return;
    }
    
/******************************************************************/
    
    public function actionMarketRegSchet()
    {   
         $request = Yii::$app->request; 
         $orgId   = intval($request->get('orgId'));
         $zakazId = intval($request->get('zakazId'));
         $eventId = intval($request->get('eventId'));    
         $this->redirect(['data/create-sync-schet-select', 'refOrg' => $orgId, 'zakazId' => $zakazId, 'eventId' => $eventId]);         
         return;
    }
    
/******************************************************************/

    public function actionRegManualSchet()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }

         $model = new MarketSchetForm();
         $request = Yii::$app->request; 
         
         $curUser=Yii::$app->user->identity;
         $orgId   = intval($request->get('orgId'));
         $zakazId = intval($request->get('zakazId'));
         $eventId = intval($request->get('eventId'));             
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
            $id = $model->regSchet();
            //$this->redirect(['site/success']);
            $this->redirect(['market/market-schet', 'noframe' => 1, 'id' => $id]);         
            return;
         } 
         $record = $this->setUse ($orgId);       
         $model->orgId = $orgId;
         $model->zakazId = $zakazId;
         $model->eventId = $eventId;
         $model->schetDate = date("d.m.Y");
         return $this->render('market-reg-schet', ['model' => $model, 'record'=>$record ]);              
    }


/******************************************************************/
//       Операции со строками заказа
/******************************************************************/

    public function actionSyncPrice()
    {         
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }   
        $request = Yii::$app->request;  
        $mode = $request->get('mode', 0);                          

        if ($mode == 0)
        {    
            $session = Yii::$app->session;      
            $session->open();
            $session->set('actionName', 'market/sync-price');
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);
        }
        else 
        {            
            $model = new DataSyncGoogle();                
            $page = $model->syncSclad1C();
        $this->redirect(['site/success']);
        return;
        }
    }

/****************/

    public function actionMarketZakazFrameRemove()
    {   
    
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->removeZakaz ($id);      
         $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }


    public function actionMarketZakazFrameDelete()
    {   
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->delZakaz ($id);         
         $this->redirect(['site/success']);
         $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }
    
    public function actionMarketZakazFrameReverse()
    {   
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->reverseZakaz ($id);
         $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }
/******************************************************************/
    
/****************/

    public function actionMarketZakazRemove()
    {   
    
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->removeZakaz ($id);      
         $this->redirect(['market/market-zakaz', 'noframe' => 1, 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }


    public function actionMarketZakazDelete()
    {   
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->delZakaz ($id);         
         $this->redirect(['site/success']);
         $this->redirect(['market/market-zakaz', 'noframe' => 1, 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }
    
    public function actionMarketZakazReverse()
    {   
         $model = new MarketZakazForm();
         $request = Yii::$app->request;     
         $id = intval($request->get('id'));      
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));
         $model->reverseZakaz ($id);
         $this->redirect(['market/market-zakaz', 'noframe' => 1, 'zakazId' => $zakazId, 'orgId' => $orgId]);                 
    }
/******************************************************************/


    public function actionMarketPriceSelect()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketPriceForm();
         $session = Yii::$app->session;     
         $session->open();

         $modelArray = [];
         $framesrc = $session->get('framesrc',0);


        if (Yii::$app->request->isPost) {
            $zakazId = $session->get('MarketZakazId');
            $orgId   = $session->get('MarketOrgId');
            if ($zakazId == 0) {$this->redirect(['site/error']);}
            $zakazModel = new MarketZakazForm();

            $priceList=$session->get('MarketPrice');
            for($i=0;$i<count($priceList);$i++ ){$modelArray[]  = new MarketPriceForm();}
            
            
        if (Model::loadMultiple($modelArray, Yii::$app->request->post()) &&  Model::validateMultiple($modelArray)) 
        {        
            for ($index=0; $index< count($modelArray); $index++)
            {   
            $model = $modelArray[$index];
             if ($model->count > 0)
             {             
               $zakazModel->addRecToZakaz($zakazId, $priceList[$index]['GoodTitle'],$model->count, $priceList[$index]['ed'], $priceList[$index]['Val']);
             }          
            }           
        }           
        if ($framesrc == 1) {$this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]); return;}
            $this->redirect(['market/market-zakaz', 'zakazId' => $zakazId, 'orgId' => $orgId]);     
        return; 
        }/** save it**/
        
         $request = Yii::$app->request;     
         $zakazId = intval($request->get('zakazId'));
         $orgId = intval($request->get('orgId'));       
         $priceList=$model->getPrice();              
         for($i=0;$i<count($priceList);$i++ )
         {
             $modelArray[]  = new MarketPriceForm();
         }

         $session->set('MarketPrice', $priceList);
         $session->set('MarketZakazId', $zakazId);
         $session->set('MarketOrgId', $orgId);
         
         return $this->render('market-price-select', ['modelArray' => $modelArray,'priceList' => $priceList]);
     }

/******************************************************************/
    public function actionMarketZakazCreate()
    {       
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         $orgId = $request->get('id');
         $zakazId = $model->getNewZakaz ($orgId);
         $this->redirect(['market/market-zakaz', 'zakazId' => $zakazId, 'noframe'=> 0, 'orgId' => $orgId]);           
    }

    public function actionMarketZakazFrameCreate()
    {       
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         $orgId = $request->get('id');
         $zakazId = $model->getNewZakaz ($orgId);
         $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);           
    }
        
/******************************************************************/
    public function actionMarketReserveZakaz()
    {
         //$this->setUse();
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveReserve();         
                $this->redirect(['site/success']);
                //$this->redirect(['market/market-zakaz-inwork']);         
                return;
            }
         }
         
         $orgId = $request->get('orgId');
         $zakazId = $request->get('zakazId');
         $model->id = intval($orgId);
         $model->zakazId = intval($zakazId);
         $record = $this->setUse ($orgId);
        
         $model->nextdate = date("d.m.Y", time()+60*60*24);
         return $this->render('market-reserve-zakaz', ['model' => $model, 'record' => $record]);

    }

    public function actionMarketViewZakaz()
    {
         //$this->setUse();
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         $zakazId = $request->get('zakazId');
         $model->zakazId = intval($zakazId);
         return $this->render('market-view-zakaz', ['model' => $model]);
    }

/****************/    
    public function actionMarketRequestList()
    {   
        $curUser=Yii::$app->user->identity; 
        $model = new WarehouseForm ();        
        $model->userRestrict = $curUser->id;
        $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
          return $this->render('market-request-list', ['model' => $model, 'provider' => $provider]);
         
    }
/****************/    
    public function actionMarketRequestSupply()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         //$this->setUse();
         $model = new MarketSchetForm();
         $orgModel = new OrgDetail();
         $request = Yii::$app->request; 
         $schetId = $request->get('schetId');
         $model->id = intval($schetId);
         $model->loadSchetData();
         $orgRecord   = $model->getOrgRecordBySchet();
         $orgModel->orgId = $orgRecord->id;
         
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->dstType != "") 
            {                           
                $page=$model->regRequestSupply();                       
                $this->redirect(['site/close']);
                //echo $page;
                return;
            }

         $model->contactPhone = $orgRecord->contactPhone;
         $model->contactFIO = $orgRecord->contactFIO;
         $model->contactEmail = $orgRecord->contactEmail;
         $model->supplyDate = date ("d.m.Y", time());
         $model->loadRequestData();
         
         return $this->render('market-request-supply', ['model' => $model, 'orgModel' => $orgModel, 'orgRecord' => $orgRecord]);
    }
    
    
/****************/
    public function actionSaveDst()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         
         $model = new MarketSchetForm();         
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {

                $sendArray = $model->saveDstData();
                echo json_encode($sendArray);
                return;
            }    
        }
    }       
    
/****************/

/*Работа с заказом*/    
    public function actionMarketZakazNew()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         //$this->setUse();
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         
         if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveData();        
                if ($model->status == 2) {$this->redirect(['site/success']);return;}
                if ($model->status == 1) {$this->redirect(['market/market-zakaz-inwork']);return;}                
                $this->redirect(['market/market-zakaz-new', 'noframe' => 1, 'zakazId' => $model->zakazId]);                
                //$this->redirect(['market/market-zakaz-inwork']);         
                return;
            }
         }    
         $model->zakazId = $request->get('zakazId', 0);
         return $this->render('market-zakaz-new', ['model' => $model]);
    }

    
/*Работа с заказом*/    
    public function actionMarketZakaz()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         //$this->setUse();
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 
         
         if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveData();        
                //if ($model->status == 2) {$this->redirect(['site/success']);return;}
                //if ($model->status == 1) {$this->redirect(['market/market-zakaz-inwork']);return;}                
                $this->redirect(['market/market-zakaz', 'noframe' => 1, 'zakazId' => $model->zakazId]);                
                //$this->redirect(['market/market-zakaz-inwork']);         
                return;
            }
         }    
         $model->zakazId = $request->get('zakazId', 0);
         return $this->render('market-zakaz', ['model' => $model]);
    }

    
/*Работа с заказом*/    
    public function actionMarketZakazFrame()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         //$this->setUse();
         $model = new MarketZakazForm();
         $request = Yii::$app->request; 

        if ($request->get('action') == "addZakaz" )
        {
            $initialZakaz=trim($request->get('initialZakaz'));
            $orgId   =  intval($request->get('orgId'));
            $zakazId = intval($request->get('zakazId'));                
            $model->id = intval($orgId);
            $model->zakazId = intval($zakazId);
            $model->initialZakaz = $initialZakaz;
            $model -> addToZakaz($initialZakaz);
            $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);            
        }

        if ($request->get('action') == "editZakaz" )
        {
            $orgId   =  intval($request->get('orgId'));
            $zakazId = intval($request->get('zakazId'));                
            $zakazContentId = intval($request->get('id'));
            $proposal = $request->get('proposal');
            $actionType = intval($request->get('actionType'));          
            
            $model->id = intval($orgId);
            $model->zakazId = intval($zakazId);         
            $model -> addProposal($zakazContentId, $proposal, $actionType);
            $this->redirect(['market/market-zakaz-frame', 'zakazId' => $zakazId, 'orgId' => $orgId]);            
        }
         
         if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveFrameData();        
                /*if ($model->status == 2) {$this->redirect(['site/success']);return;}
                if ($model->status == 1) {$this->redirect(['market/market-zakaz-inwork']);return;}*/                
                $this->redirect(['market/market-zakaz-frame', 'orgId' => $model->id, 'zakazId' => $model->zakazId]);                
                //$this->redirect(['market/market-zakaz-inwork']);         
                return;
            }
         }
         
         
         $orgId = $request->get('orgId');
         $zakazId = $request->get('zakazId');
         $model->id = intval($orgId);
         $model->zakazId = intval($zakazId);
         $record = $this->setUse ($orgId);
         
         $model->contactPhone = $record->contactPhone;
         $model->contactEmail = $record->contactEmail;
         $model->contactFIO = $record->contactFIO;       
         $model->status = 3;
         $model->nextdate = date("d.m.Y", time()+60*60*24);
         return $this->render('market-zakaz-frame', ['model' => $model, 'record' => $record]);
    }
    
/****************/
    public function actionSaveZakazDetail()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         
         $model = new MarketZakazForm();         
      //  if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {

                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }
    }       
/****************/


    public function actionAddWareZakaz()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $zakazId = intval($request->get('zakazId', 0));
         $wareRef = intval($request->get('wareRef', 0));
         $wareEd   = $request->get('wareEd', '');
         
         $model = new MarketZakazForm();         
        //if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->addWareZakaz($zakazId, $wareRef, $wareEd);
                echo json_encode($sendArray);
                return;
        }
         
    }   


 public function actionAddDocToZakaz()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $zakazid= intval($request->get('zakazid', 0));
         $docid = intval($request->get('docid', 0));
         $model = new MarketZakazForm();

       // if(Yii::$app->request->isAjax)    
        {            
            {
                $sendArray = $model->addDocToZakaz($zakazid, $docid);
                echo json_encode($sendArray);
                return;
            }    
        }
    }
 public function actionRmDocToZakaz()
  {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $zakazid= intval($request->get('zakazid', 0));
         $docid = intval($request->get('docid', 0));
         $model = new MarketZakazForm();

       // if(Yii::$app->request->isAjax)    
        {            
            {
                $sendArray = $model->rmDocToZakaz($zakazid, $docid);
                echo json_encode($sendArray);
                return;
            }    
        }
    }            
    
 public function actionGetOrgInfo()
  {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $orgId= intval($request->get('orgId', 0));
         $model = new MarketZakazForm();
       // if(Yii::$app->request->isAjax)    
        {            
            {
                $sendArray = $model->getOrgInfo($orgId);
                echo json_encode($sendArray);
                return;
            }    
        }
    }            
    
    
    
/****************/

    public function actionMarketZakazInwork()
    {
         $this->dropUse();
         $model = new MarketZakazForm();         
         return $this->render('market-zakaz-inwork', ['model' => $model, 'provider' => $model->getInWorkProvider()]);
    }
    
    public function actionMarketZakazSelect()
    {
         $this->dropUse();
         $model = new MarketZakazForm();         
         return $this->render('market-zakaz-select', ['model' => $model, 'provider' => $model->getAvailiableZakazListProvider()]);
    }


    public function actionSendZakaz()
    {        
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new MarketZakazForm();      
        $session = Yii::$app->session;      
        $session->open();
         
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {   
            $page=$session->get('MarketProposal');  
            $model->orgId =  $session->get('orgId');  
            $success= $model->sendProposal($page);

            $session->set('MarketProposal', "");  
            $session->set('orgId', "");             

            if ($success)    
            {
                $session->set('MarketProposal', "");    
                $this->redirect(['site/success']);         
                return;
            }
            else  
            {
                $this->redirect(['site/problem']);
                return;
            }        
            $this->redirect(['site/success']);         
            return;
            //$this->redirect(['market/market-zakaz', 'zakazd' => $goodRequestId]);            
        }        

         $request = Yii::$app->request;          
         $zakazId = $request->get('zakazId');
         $model->zakazId = intval($zakazId);         
         $page=$model->formRequestZakaz();     
   
          if ($page == false) 
          {
              $this->redirect(['site/problem']);
              return;
          }
         
         $model->email = $request->get('email');
         $session->set('MarketProposal', $page);    
         $session->set('orgId', $model->orgId);    
         return $this->render('market-mail', ['model' => $model, 'page' => $page]);
    }
    
    
/***************************************************************/
/*********** Закупка товара ************************************/
/***************************************************************/
    public function actionMarketGoodList()
    {        
         $model = new MarketGoodsForm();         
         return $this->render('market-good-list', ['model' => $model, 'provider' => $model->getRequestListProvider(Yii::$app->request->get())]);
    }


    public function actionMarketGoodRequestCreate()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketGoodsForm();
         $request = Yii::$app->request; 
         //$orgId = $request->get('orgId');
         
         $zakazId= $request->get('zakazId');
         $goodRequestId = $model->createGoodRequest($zakazId);
         if ($goodRequestId == -1){$this->redirect(['site/error']);}
         else $this->redirect(['market/market-good-request', 'id' => $goodRequestId]);         
    }


    public function actionMarketGoodRequest()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketGoodsForm();
         $request = Yii::$app->request; 
         $id= intval($request->get('id'));
         $model->requestId = $id;
         
         $action = $request->get('action');
         if ($action == "addRequest")
         {
             $good= $request->get('good');
             $count= $request->get('count');
             $marketDate= $request->get('marketDate');
             $sclad= $request->get('sklad');
             $model->addToRequest($good, $count, $marketDate, $sclad);           
             $this->redirect(['market/market-good-request', 'id' => $model->requestId]);           
         }
         if ($action == "delRequestRec")
         {
             $recId= intval($request->get('recId'));
             $model->delFromRequest($recId);             
             $this->redirect(['market/market-good-request', 'id' => $model->requestId]);           
         }
         if ($action == "editRequest")
         {
             $good= $request->get('good');
             $count= $request->get('count');
             $recId= intval($request->get('recId'));
             $marketDate= $request->get('marketDate');
             $sclad= $request->get('sklad');
             $model->editRequest($recId, $good, $count, $marketDate, $sclad);
             $this->redirect(['market/market-good-request', 'id' => $model->requestId]);           
         }
         
         if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $page = $model->saveData();        
                
                //echo $page;
                $this->redirect(['market-good-list']);         
                return;
            }
         }

         $model->isFormed =0;
         $requestRecord = $model->loadRequestData($id);
         return $this->render('market-good-request', ['model' => $model, 'provider' => $model->getRequestContentListProvider(), 'requestRecord' =>$requestRecord]);
    }

    
/***************************************************************/   
    public function actionMarketSchetSelect()
    {        
         $model = new MarketSchetForm();         
         $model->schetStatus = 2; 
         $provider = $model->getActiveSchetListProvider(Yii::$app->request->get());
         return $this->render('market-schet-select', ['model' => $model, 'provider' => $provider]);
    }

    public function actionMarketSchetSupplySelect()
    {        
         $model = new MarketSchetForm();         
         $model->schetStatus = 3; 
         $provider = $model->getActiveSchetListProvider(Yii::$app->request->get());
         return $this->render('market-schet-select', ['model' => $model, 'provider' => $provider]);
    }

    
     public function actionMarketNew()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketNewOrgForm();        
         $request = Yii::$app->request; 
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $id =$model->saveData();        
            
           $this->redirect(['market/market-zakaz-create', 'id' => $id]);           
           return;
         }
         else 
         {               
           return $this->render('market-new', ['model' => $model]);
         }
    }

    
    
    public function actionMarketFind()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $mode = $request->get('mode');                          
        if (empty($mode)){$mode = 1;}
        $session = Yii::$app->session;      
        $session->open();
        
        if (isset($sort))
        {
            $session->set('MarketFindSort', $sort);
        }
        else  
        {
           $sort=$session->get('MarketFindSort');
        }
        
         $model = new MarketViewForm();
         $model->setSort =$sort; 
         $dataProvider = $model->search(Yii::$app->request->get());      
         $model->mode = $mode;
         return $this->render('market-find', ['model' => $model, 'provider' =>$dataProvider, 'mode'=> $mode ]);
    }

    public function actionMarketSearch()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
     $model = new MarketViewForm();
     
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $mode = $request->get('mode');                          


        $findString = $request->get('findString');  
        if (!empty($findString)){$model->findString = $findString;}
        if (empty($mode)){$mode = 1;}
        $session = Yii::$app->session;      
        $session->open();
        
        if (isset($sort))
        {
            $session->set('MarketFindSort', $sort);
        }
        else  
        {
           $sort=$session->get('MarketFindSort');
        }       
         $model->setSort =$sort; 
         $dataProvider = $model->findByString(Yii::$app->request->get());        
         $model->mode = $mode;
         return $this->render('market-search', ['model' => $model, 'provider' =>$dataProvider, 'mode'=> $mode ]);
    }

   /*******************************************************************************/
   /**************** Счета ********************************************************/
   /*******************************************************************************/
   
    public function actionMarketAkt()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketSchetAct();
         $request = Yii::$app->request; 
         
         $id = $request->get('id', 0);
         $model->id = intval($id);                           
         return $this->render('market-akt', ['model' => $model,  ]);
         
    }

    public function actionAddActInSchet()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketSchetAct();
         $request = Yii::$app->request; 
         $schetId = $request->get('schetId', 0);         
         $sendArray  = $model->createAct($schetId);
        if(Yii::$app->request->isAjax)
        {
                echo json_encode($sendArray);
                return;        
        }

          $this->redirect(['site/success']);
          return;
    }

    
    public function actionSaveActData()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         
         $model = new MarketSchetAct();         
       // if(Yii::$app->request->isAjax)
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
    
    public function actionMarketSchet()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketSchetForm();
         $request = Yii::$app->request; 
         
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveData();         
                if ($res < 0)    {$this->redirect(['site/problem']);return;} // ошибка сохранения счета
                //if ($res == 2)    {$this->redirect(['site/success']);return;} // завершена работа со счетом                
           return $this->redirect(['market-schet','noframe' => 1, 'id' => $model->id, 'src' => $model->src, 'changed' => 1]); // другое
           //$this->redirect(['site/success']);
           //return;
         }
         else 
         {               
         $id = $request->get('id', 0);
         $model->id = intval($id);
         $model->src= $request->get('src', 'none');
         $model->changed= $request->get('changed', '0');
         return $this->render('market-schet', ['model' => $model,  ]);
         }
    }
    
    public function actionMarketSchetFrame()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MarketSchetForm();
         $request = Yii::$app->request; 
         
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveData();         
                if ($res < 0)    {$this->redirect(['site/problem']);return;} // ошибка сохранения счета
                if ($res == 2)    {$this->redirect(['site/success']);return;} // завершена работа со счетом
           return $this->redirect(['market-schet-frame','noframe' => 1, 'id' => $model->id]); // другое
           //$this->redirect(['site/success']);
           return;
         }
         else 
         {               
         $id = $request->get('id');
         $model->id = intval($id);
         return $this->render('market-schet-frame', ['model' => $model ]);
         }
    }
    
        
    public function actionMarketSchetFinish()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
       $model = new MarketSchetForm();
         $request = Yii::$app->request; 
         $id = $request->get('id',0);
         if($id == 0 ){
            $problemMessage    ="Вероятно не задан идентификатор счета";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;
         }
         $model->id = intval($id);
         if ($model->finishSchet())
         {
         $this->redirect(['site/success']);
         return;
         }
           $problemMessage    ="Вероятно не верно задан идентификатор счета";
        $session->set('problemMessage', $problemMessage);         
        $this->redirect(['site/problem']);
      
    }
    /*********************/
    
    public function actionSaveSchetDetail()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         
         $model = new MarketSchetForm();         
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {

                $sendArray = $model->saveAjaxData();
                echo json_encode($sendArray);
                return;
            }    
        }
    }       

    public function actionSaveSchetParam()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         
         $model = new MarketSchetForm();         
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {

                $sendArray = $model->saveAjaxParam();
                echo json_encode($sendArray);
                return;
            }    
        }
    }       


    public function actionRmWareSchet()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $schetId = intval($request->get('schetId', 0));
         $wareRef = intval($request->get('wareRef', 0));
         
        $model = new MarketSchetForm();         
        if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->rmWareSchet($schetId, $wareRef);
                echo json_encode($sendArray);
                return;
        }
         
    }       

    public function actionCopyWareInSchet()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $schetId = intval($request->get('schetId', 0));
         
        $model = new MarketSchetForm();         
        //if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->copyWareInSchet($schetId);
                echo json_encode($sendArray);
                return;
        }
         
    }       

    
    public function actionAddWareSchet()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $schetId = intval($request->get('schetId', 0));
         $wareRef = intval($request->get('wareRef', 0));
         $edRef   = intval($request->get('edRef', 0));
         
         $model = new MarketSchetForm();         
        if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->AddWareSchet($schetId, $wareRef, $edRef);
                echo json_encode($sendArray);
                return;
        }
         
    }       
    
    public function actionSyncSchetTransport()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;
         $schetId = intval($request->get('schetId', 0));

        $model = new MarketSchetForm();
        //if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->copyTransportFromZakaz($schetId);
                echo json_encode($sendArray);
                return;
        }

    }

    /*********************/

    public function actionMarketSchetClose()
    {
        $this->redirect(['site/success']);
    }
    /*********************/
    
    /*********************/

     public function actionClientManagment()
    {
         $this->dropUse();
         $model = new MarketZakazForm();         
        
         
         $provider=$model->getClientManagmentProvider(Yii::$app->request->get());        
         return $this->render('client-managment', ['model' => $model, 'provider' => $provider]);
    }

    /*********************/

     public function actionHelperSetEnable()
    {

        $model = new MarketZakazForm(); 
        $request = Yii::$app->request;  
        $id=intval($request->get('id'));
        $model->helperSetEnable($id);           
        $this->redirect(['site/success']);        
    }
    
     public function actionHelperSetDisable()
    {

        $model = new MarketZakazForm(); 
        $request = Yii::$app->request;  
        $id=intval($request->get('id'));
        $model->helperSetDisable($id);          
        $this->redirect(['site/success']);        
    }
    
    /*********************/
     public function actionMarketClientSelect()
    {
         $this->dropUse();
         $model = new MarketZakazForm();  
         $request = Yii::$app->request; 
         $model->showMyClient=intval($request->get('showMyClient',0));
         
         $provider=$model->getClientListProvider(Yii::$app->request->get());         
         return $this->render('market-client-select', ['model' => $model, 'provider' => $provider]);
    }


    /*********************/
     public function actionMarketNoactiveClient()
    {
         $this->dropUse();
         $model = new MarketCalendarForm();         
         
         $provider=$model->getNonActiveClientListProvider(Yii::$app->request->get());         
         return $this->render('market-noactive-client', ['model' => $model, 'provider' => $provider]);
    }

    
        /*********************
            Marketing
        ********************/

    public function actionMarketingInteresByRazdel()
    {
         $model = new MarketingForm();       
         $provider=$model->getInteresByRazdelProvider(Yii::$app->request->get());                
         return $this->render('marketing-interes-by-razdel', ['model' => $model, 'provider' => $provider]);
    }

    public function actionMarketingInteresByCity()
    {
         $model = new MarketingForm();       
         $provider=$model->getInteresByCityProvider(Yii::$app->request->get());              
         return $this->render('marketing-interes-by-city', ['model' => $model, 'provider' => $provider]);
    }
/********/
    public function actionMarketingZakazByRazdel()
    {
         $model = new MarketingForm();       
         $provider=$model->getZakazByRazdelProvider(Yii::$app->request->get());              
         return $this->render('marketing-zakaz-by-razdel', ['model' => $model, 'provider' => $provider]);
    }

    public function actionMarketingZakazByCity()
    {
         $model = new MarketingForm();       
         $provider=$model->getZakazByCityProvider(Yii::$app->request->get());                
         return $this->render('marketing-zakaz-by-city', ['model' => $model, 'provider' => $provider]);
    }

/********/
    public function actionMarketingSchetByRazdel()
    {
         $model = new MarketingForm();       
         $provider=$model->getSchetByRazdelProvider(Yii::$app->request->get());              
         return $this->render('marketing-schet-by-razdel', ['model' => $model, 'provider' => $provider]);
    }

    public function actionMarketingSchetByCity()
    {
         $model = new MarketingForm();       
         $provider=$model->getSchetByCityProvider(Yii::$app->request->get());                
         return $this->render('marketing-schet-by-city', ['model' => $model, 'provider' => $provider]);
    }

    
    /***** Event operation *********/

    public function actionMarkEventDone()
    {
         $model = new MarketCalendarForm();
         $request = Yii::$app->request; 
         $id = intval($request->get('id'));
         $model->markEvent ($id);        
         $this->redirect(['site/success']);         
    }
    /*********/


    public function actionEventMark()
    {
         $model = new MarketCalendarForm();
         $request = Yii::$app->request; 
         $id = intval($request->get('id'));
         $model->markEvent ($id);        
         $this->redirect(['market/market-start']);         
    }
    /*********/
    
     public function actionEventShift()
    {

        $model = new MarketCalendarForm(); 
        $request = Yii::$app->request;  
        $orgId=intval($request->get('id', 0));
        $shift=intval($request->get('shift', 0));
        
        $success = $model->shiftArbitraryEvent($orgId,$shift);          
        $this->redirect(['site/success']);        
    }
    /*********/
     public function actionDealEventShift()
    {

        $model = new MarketCalendarForm(); 
        $request = Yii::$app->request;  
        $eventId=intval($request->get('id', 0));
        $shift=intval($request->get('shift', 0));
        
        $success = $model->shiftDealEvent($eventId, $shift);          
        $this->redirect(['site/success']);        
    }
    /*********/
      public function actionSetRemindToUser()
    {

        $model = new MarketCalendarForm(); 
        $request = Yii::$app->request;  
        $userId=intval($request->get('userId', 0));
        $orgListId=$request->get('orgListId', 0);
        if ($userId > 0)
        {
            $success = $model->setEventsToUser($userId, $orgListId, 'Возобновить контакт');  
        }
        $this->redirect(['site/success']);        
    }
    /*********/
     public function actionPrintZakaz()      
    {
        $model = new MarketPrintForm();
        $request = Yii::$app->request;  
        $model->mode=intval($request->get('mode', 0));           
        $model->zakazId=intval($request->get('zakazId', 0));           
           echo $model->prepareZakazDetail();
        exit (0);       
        return;
    } 
    /*********/
     public function actionPrintSchet()      
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new MarketPrintForm();
        $request = Yii::$app->request;  
        $model->schetId=intval($request->get('schetId', 0));           
        $model->stamp=intval($request->get('stamp', 1));           
        $model->showTransport=intval($request->get('showTransport', 1));           

        $html = $model->prepareSchetPrint();
        if ($model->stamp == 3)
        {

        $css = '<style type = "text/css">.test {font-weight: 600;}</style>';
        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";


        $curUser=Yii::$app->user->identity;
        $fname = 'specify'.$curUser->id;

        $mask = realpath(dirname(__FILE__))."/../uploads/".$fname."*.doc";
        array_map("unlink", glob($mask));
        $fname = $fname."_".time().".doc";
        $filePath = $uploadPath.$fname;
         ExportToWord::htmlToDoc($html, $css, $filePath, 'UTF-8', 1);
         $url = Yii::$app->request->baseUrl."/../uploads/".$fname;

         $this->redirect(['/site/download', 'url' => $url]);
         return;
        }
       else echo $html;

       exit (0);    
        return;
    } 

    
/*********/        

     public function actionPrintAct()      
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new MarketPrintForm();
        $request = Yii::$app->request;  
        $model->actId=intval($request->get('id', 0));           
        $model->stamp=intval($request->get('stamp', 0));           

        $html = $model->prepareActPrint();
        if ($model->stamp == 3)
        {

        $css = '<style type = "text/css">.test {font-weight: 600;}</style>';
        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";


        $curUser=Yii::$app->user->identity;
        $fname = 'act'.time().$curUser->id;

        $mask = realpath(dirname(__FILE__))."/../uploads/".$fname."*.doc";
        array_map("unlink", glob($mask));
        $fname = $fname."_".time().".doc";
        $filePath = $uploadPath.$fname;
         ExportToWord::htmlToDoc($html, $css, $filePath, 'UTF-8', 1);
         $url = Yii::$app->request->baseUrl."/../uploads/".$fname;

         $this->redirect(['/site/download', 'url' => $url]);
         return;
        }
       else echo $html;

       exit (0);    
        return;
    } 

/*********/

    public function actionClientSchetSelect()
    {
         $request = Yii::$app->request;  
         $model = new ClientSchetForm();      
         $model->refOrg=intval($request->get('refOrg', 0));
         $model->refSchet=intval($request->get('refSchet', 0));                  
         $model->fltOrg=intval($request->get('fltOrg', 1));         
         $provider=$model->getClientSchetProvider(Yii::$app->request->get());                
         return $this->render('client-schet-select', ['model' => $model, 'provider' => $provider]);
    }

/*********/
   public function actionTest()
   {

    $html = '<html><body><div class = "test">Test</div></body></html>';
    $css = '<style type = "text/css">.test {font-weight: 600;}</style>';
    $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
    $fileName = $uploadPath.'test.doc';
    ExportToWord::htmlToDoc($html, $css, $fileName);
   }
}
