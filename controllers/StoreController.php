<?php

/*
Работа со складом и закупками


Закупки последовательность
заявка от покупателя -> заявка на закупку от отдела продаж ->
-> запрос цены -> закупка

*/

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\base\Model;

use app\models\WarehouseForm;
use app\models\MarketZakazForm;
use app\models\SupplyForm;
use app\models\DeliversForm;
use app\models\PurchesForm;
use app\models\OrgContactForm;
use app\models\PriceForm;
use app\models\SaleForm;
use app\models\DstNoteForm;
use app\models\SdelkaForm;


use app\models\WareForm;
use app\models\WareGoodForm;
use app\models\WareNomenklatura;
use app\models\WareNames;
use app\models\SupplyRequestReestr;

use app\models\WareOtves;
use app\models\DeliverSclad;
use app\models\TransportTarif;

class StoreController extends Controller
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
     /*if ($record->isInWork == 1)         
     {
         $curUser=Yii::$app->user->identity;
            if(isset($record->ref_user) && ($record->ref_user != $curUser->id)) 
            {
              $this->redirect(['site/org-inuse', 'id' => $id]);            
            }
     }*/
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

    /*******************************************************************************/   
    
    public function actionScladStart()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
        $this->redirect(['store/sclad-start2']);
        return;
        
        $request = Yii::$app->request;  
        $detail= intval($request->get('detail', 1));
          $model = new WarehouseForm();         
          $deliverModel = new DeliversForm();         
          
        
        $model->detail = $detail;
        $deliverModel->detail = $detail;

        switch ($detail)
        {
            case 1:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 2:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 3:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            
            case 4:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());
            break;
            case 5:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            case 6:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            
            case 7:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 8:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 9:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;

            default:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;

        }
        
         return $this->render('sclad-start', ['model' => $model, 'deliverModel'=> $deliverModel, 'provider' => $provider]);
    }


    /*******************************************************************************/   
    public function actionHeadSclad()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;  
        $detail= intval($request->get('detail', 1));
          $model = new WarehouseForm();         
          $deliverModel = new DeliversForm();         
          
        
        $model->detail = $detail;
        $deliverModel->detail = $detail;

        $format = $request->get('format','html');
         
        if ($format == 'csv' || $format == 'google')
        {
           
           if ($format == 'csv')    $codePage= "Windows-1251";
           if ($format == 'google') $codePage= "utf-8";
           
            switch ($detail)
            {
                
                case 11:
                $detailFile =$model->getGoodsInOrderData(Yii::$app->request->get());        
                break;

                case 12:
                $detailFile =$model->getGoodsInPredictData(Yii::$app->request->get(), $codePage);        
                break;
            
                case 13:
                $detailFile =$model->getGoodsInStoreData(Yii::$app->request->get());        
                break;

                case 14:
                $detailFile =$model->getGoodsInTransitData(Yii::$app->request->get());        
                break;
            
            }
            
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }

        if ($format == 'xml')
        {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'text/xml');
           switch ($detail)
            {
                
                case 12:
                    return $model->getGoodsInPredictXML();        
                    //exit(0);
                    return;
                break;                        
            }
            
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }


        
                        
        switch ($detail)
        {
            /*Отгрузка*/
            case 1:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 2:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 3:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            
            /*Доставка*/
            case 4:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());
            break;
            case 5:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            case 6:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            case 15:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;

            
            /*Поставщики*/
            case 7:
            $provider= $model->getSupplierListProvider(Yii::$app->request->get());        
            break;
            case 8:
            $provider= $model->getSupplierSchetListProvider(Yii::$app->request->get());        
            break;
            case 9:
            $provider= $model->getSupplierGoodsProvider(Yii::$app->request->get());        
            break;

            /*Склад*/
            case 11:
            $provider= $model->getGoodsInOrderProvider(Yii::$app->request->get());        
            break;
            case 12:
            $provider= $model->getGoodsInPredictProvider(Yii::$app->request->get());        
            break;
            case 13:
            $provider= $model->getGoodsInStoreProvider(Yii::$app->request->get());        
            break;
            case 14:
            $provider= $model->getGoodsInTransitProvider(Yii::$app->request->get());        
            break;
            
            
            
            default:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;

        }
        
         return $this->render('head-sclad', ['model' => $model, 'deliverModel'=> $deliverModel, 'provider' => $provider]);
    }


    /*******************************************************************************/   

    public function actionShowDeliver()
    {
        
        $request = Yii::$app->request;  
        $detail= intval($request->get('detail', 1));
        $model = new WarehouseForm();         
        $deliverModel = new DeliversForm();         
        
        $model->detail = $detail;
        $deliverModel->detail = $detail;
        $deliverModel->dFrom = $request->get('dFrom');          
        $deliverModel->dTo =   $request->get('dTo');        

        $model->dFrom = $deliverModel->dFrom;          
        $model->dTo =   $deliverModel->dTo;        

        $model->view =intval($request->get('view', 0));        

        $format = $request->get('format','html');
         
    
        if ($format == 'print')
        {
            switch ($detail)
            {
                
                case 4:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 5:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 6:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 15:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());        
                       return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;

                case 115:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;

            }


            
        }


        if ($format == 'print2')
        {
            switch ($detail)
            {

                case 4:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 5:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 6:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;


                case 15:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;

            }
        }

    }
    
    
    
    public function actionScladStart2()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $curUser=Yii::$app->user->identity;
        if (!($curUser->roleFlg & 0x0010) ){ $this->redirect(['site/index']);  return; }   
             
        
        $request = Yii::$app->request;  
        $detail= intval($request->get('detail', 1));
          $model = new WarehouseForm();         
          $deliverModel = new DeliversForm();         
//return;        
        $model->detail = $detail;
        $deliverModel->detail = $detail;
        $deliverModel->dFrom = $request->get('dFrom');          
        $deliverModel->dTo =   $request->get('dTo');        

        $model->dFrom = $deliverModel->dFrom;          
        $model->dTo =   $deliverModel->dTo;        

        
        $model->view =intval($request->get('view', 0));        

        $format = $request->get('format','html');
         
        if ($format == 'csv' || $format == 'google')
        {
           
           if ($format == 'csv')    $code= "Windows-1251";
           if ($format == 'google') $code= "utf-8";
            switch ($detail)
            {
                
                case 11:
                $detailFile =$model->getGoodsInOrderData(Yii::$app->request->get());        
                break;

                case 12:
                $detailFile =$model->getGoodsInPredictData(Yii::$app->request->get(), $code);        
                break;
            
                case 13:
                $detailFile =$model->getGoodsInStoreData(Yii::$app->request->get());        
                break;

                case 14:
                $detailFile =$model->getGoodsInTransitData(Yii::$app->request->get());        
                break;
            
               case 15:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());        
                    $detailFile =$deliverModel->getDeliverRouteFile($deliversListData);        
                break;

            
            }
            
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }

        if ($format == 'print')
        {
            switch ($detail)
            {
                
                case 4:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 5:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 6:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());
                    return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 15:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());        
                       return $this->render('print-deliver-list', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
               
                
                case 115:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;

            }            
        }

        if ($format == 'ttn')
        {
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());        
                    $deliverModel->printAllTTN($deliversListData);
                    exit(0);
        }
        
        if ($format == 'reestr')
        {
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());        
                    $deliverModel->printPageReestrTTN($deliversListData);
                    exit(0);
        }
        


        if ($format == 'print2')
        {
            switch ($detail)
            {

                case 4:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 5:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;
                case 6:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;


                case 15:
                    $deliversListData= $deliverModel->getDeliversListData(Yii::$app->request->get());

                    //return;
                    return $this->render('print-deliver-route', ['model' => $deliverModel, 'noframe' =>1, 'deliversListData' => $deliversListData]);
                break;

            }
        }


        
        switch ($detail)
        {
            /*Отгрузка*/
            case 1:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 2:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            case 3:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;
            
            /*Доставка*/
            case 4:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());
            break;
            case 5:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            case 6:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            case 15:
            $provider= $deliverModel->getDeliversListProvider(Yii::$app->request->get());        
            break;
            
            /*Поставщики*/
            case 7:
            $provider= $model->getSupplierListProvider(Yii::$app->request->get());        
            break;
            case 8:
            $provider= $model->getSupplierSchetListProvider(Yii::$app->request->get());        
            break;
            case 9:
            $provider= $model->getSupplierGoodsProvider(Yii::$app->request->get());        
            break;

            /*Склад*/
            case 11:
            $provider= $model->getGoodsInOrderProvider(Yii::$app->request->get());        
            break;
            case 12:
            $provider= $model->getGoodsInPredictProvider(Yii::$app->request->get());        
            break;
            case 13:
            $provider= $model->getGoodsInStoreProvider(Yii::$app->request->get());        
            break;
            case 14:
            $provider= $model->getGoodsInTransitProvider(Yii::$app->request->get());        
            break;
            
            
            
            default:
            $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
            break;

        }
        $otvesModel = new WareOtves();         
        $otvesInWork=$otvesModel->getOtvesInWork();
         return $this->render('sclad-start2', ['model' => $model, 'deliverModel'=> $deliverModel, 'provider' => $provider, 'otvesInWork' => $otvesInWork]);
    }
    
    /*******************************************************************************/   
    
/*******************************************************************************/   
    public function actionWareAvRashod()
    {   
        $request = Yii::$app->request;  
        $model = new WarehouseForm();
        $model->id = intval($request->get('id',0));                                  
        $warePrihodListProvider = $model->getWarePrihodListProvider(Yii::$app->request->get());
        $wareRashodListProvider = $model->getWareRashodListProvider(Yii::$app->request->get());
        return $this->render('ware-av-rashod', ['model' => $model, 'warePrihodListProvider'=>$warePrihodListProvider, 'wareRashodListProvider' => $wareRashodListProvider]);         
    }

    public function actionSetAvRashod()
    {   
        $request = Yii::$app->request;  
        $model = new WarehouseForm();
        $id = intval($request->get('id',0));                                  
        $val = floatval($request->get('val',0));                                  
        $model->setAvRashod($id, $val);        
        $this->redirect(['site/success']);
    }

    
    /*******************************************************************************/   
    
    
    public function actionSwitchAnalyze()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0));
         $model->switchGoodAnalyze($id);
         $this->redirect(['site/success']);
    }

    
    /*******************************************************************************/   

    public function actionReserved()
    {
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        if (isset($sort))
        {
            $session->set('WarehouseReservedSort', $sort);
        }
        else  
        {
           $sort=$session->get('WarehouseReservedSort');
        }
        
         $model = new WarehouseForm();
         $model->zakazId = intval($request->get('zakazId'));
         $zakazModel = new MarketZakazForm ();
         $zakazModel->zakazId = $model->zakazId;
         $zakazRecord=$zakazModel->getZakazRecord();
         
         $model->setSort =$sort; 
         $provider= $model->getReserveListProvider();
         return $this->render('reserve', ['model' => $model, 'provider' => $provider, 'zakazRecord' => $zakazRecord]);
    }

  public function actionReserveOtves()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));
         $reserved= floatval($request->get('reserved', 0));         
         $zakazId= intval($request->get('zakazId'));         
         $model->setReserveOtves($zakazId, $id, $reserved);
         $this->redirect(['site/success']);
    }
    
  public function actionUnreserveOtves()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));
         $reserved= floatval($request->get('reserved', 0));         
         $zakazId= intval($request->get('zakazId'));         
         $model->unSetReserveOtves($zakazId, $id, $reserved);
         $this->redirect(['site/success']);
    }

  public function actionSetReserve()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  


         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $model->setReserveSize();         
           $this->redirect(['site/success']);
           return;
         }

         $size= floatval($request->get('cnt'));
         $zakazId= intval($request->get('zakazId'));         
         $id= intval($request->get('id'));
        
        $model->zakazId =  $zakazId;
        $model->size = $size; 
        $model->id = $id; 
         
         return $this->render('set-reserve', ['model' => $model]);
         
    }
/*******************************************************************************/   
    public function actionFillSchetRequest()
    {
        $request = Yii::$app->request;  
        $good= ($request->get('good'));        
        $model = new WarehouseForm();
        $requestData = $model->loadRequestData($good);
        return $this->render('fill-schet-request', ['model' => $model, 'requestData' => $requestData]);
    }


    
    
/*******************************************************************************/   
    public function actionGoodRequestList()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('GoodRequestSort', $sort);
        }
        else  
        {
           $sort=$session->get('GoodRequestSort');
        }
        
         $model = new WarehouseForm();
         $model->setSort =$sort; 
         $provider= $model->getGoodRequestProvider(Yii::$app->request->get());
         return $this->render('good-request-list', ['model' => $model, 'provider' => $provider]);
    }

/******************************************************************/

    public function actionDeliverExecute()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }     
        $request = Yii::$app->request;  
        $model = new DeliversForm();

    /*    $model->m_from = intval($request->get('m_from',0));
        $model->m_to = intval($request->get('m_to',0));
        $model->y_from = intval($request->get('y_from',0));
        $model->y_to = intval($request->get('y_to',0));
        */

        $model->dFrom = $request->get('dFrom',0);          
        $model->dTo =   $request->get('dTo',0);        
        
        $model->fixPeriod();
           
        if (empty($model->dFrom))
        {
            $model->dFrom = date("Y-m-d", strtotime( $model->y_from."-".$model->m_from."-"."01"));
            //echo "here ".$model->dFrom;
        }

        if (empty($model->dTo))
        {   
            $strTo =  $model->y_to."-".$model->m_to."-".date('t', mktime(0,0,0,$model->m_to,1,$model->y_to));
            $model->dTo = date("Y-m-d", strtotime($strTo ));            
        }

        
        
        //return;
        $format = $request->get('format','html');
        $action = $request->get('action','actShow');
        $actionType = $request->get('actionType',0);

        if ($format == 'print')
        {
                    $deliversListData= $model->getDeliverExecuteData(Yii::$app->request->get());
                    return $this->render('print-deliver-execute', ['model' => $model, 'noframe' =>1, 'deliversListData' => $deliversListData]);            
        }

        
          if ($action == 'actOplate')
        {
            $sum =0;
                $valWeight = floatval($request->get('valWeight',0));                    
                $valTime = floatval($request->get('valTime',0));                                    
                $model->setDeliverValues($valTime, $valWeight);
            
            switch ($actionType)
            {
            case 1:    
                $sum = floatval($request->get('expWrkItog',0));                                    
            break;
            case 2:    
                $sum = floatval($request->get('expCostItog',0));  
                
            break;
            case 3:    
                $sum = floatval($request->get('driveItog',0));                    
            break;
       }

           $model->oplateDeliverExecute(Yii::$app->request->get(), $sum, $actionType);
   
        }

         $provider= $model->getDeliverExecuteProvider(Yii::$app->request->get());
         return $this->render('deliver-execute', ['model' => $model,  'provider' => $provider]);
    }
    


    public function actionDeliverList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('DeliverSort', $sort);
        }
        else  
        {
           $sort=$session->get('DeliverSort');
        }
        
         $model = new DeliversForm();
         $model->setSort =$sort; 
         $provider= $model->getDeliversListProvider(Yii::$app->request->get());
         return $this->render('delivers-list', ['model' => $model,  'provider' => $provider]);
    }
    
        
    public function actionDeliverStatus()
    {   
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $id = intval($request->get('id',0));                          
        $status = intval($request->get('status',0));                          
        $model = new DeliversForm();
        
           if ($id == 0) 
         {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Вероятно не задан идентификатор задания на доставку";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
         }
         
            $success=$model -> setDeliverStatus($id, $status);
            if ($success == true)     {    
                    $this->redirect(['site/success']); 
                    return;
            }
               else {

                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Ошибка при обновлении статуса";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;
         }

    }


    public function actionDeliverFinalize()
    {   
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $id = intval($request->get('id',0));                          
        $status = intval($request->get('status',0));                          
        $model = new DeliversForm();
        
           if ($id == 0) 
         {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Вероятно не задан идентификатор задания на доставку";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
         }
         $model->id = $id;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $success = $model->saveFinalizeData(); 
           if ($success == true)     {    
                    $this->redirect(['site/success']); 
                    return;
            }
               else {

                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Ошибка при обновлении статуса";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;
         }
        }

        return $this->render('deliver-finalize', ['model' => $model,]);
    }
    
    
/******************************************************************/
    public function actionDeliverZakaz()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
         $request = Yii::$app->request;     
         $model = new DeliversForm();
         $noframe = intval($request->get('noframe',0));                  

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $id = $model->saveData(); 
           
           $this->redirect(['store/deliver-zakaz', 'noframe' =>1, 'id' => $id]);
           return;
         }

         
         $id = intval($request->get('id',0));                  
         $model->id = $id;                   
         $model->schetId = intval($request->get('schetId',0));         
         $action = $request->get('action','none');

         if ($action == 'addGood')
         {
             $requestGoodTitle= $request->get('requestGoodTitle','-');
             $requestCount= $request->get('requestCount',0);
             $requestMeasure= $request->get('requestMeasure','-');
             $requestGoodRef= $request->get('requestGoodRef',0);
             $model->addGoodInRequest($id,$requestGoodTitle, $requestCount,$requestMeasure, $requestGoodRef);
             $this->redirect(['store/deliver-zakaz', 'id' => $id, 'noframe' => $noframe]);             
         }
         elseif ($action == 'delGood')
         {
             $goodId= $request->get('goodId',0);
             $model->delGoodFromRequest($goodId);             
         }     
         elseif ($action == 'editGood')
         {
             $goodId= $request->get('goodId',0);
             $proposal= $request->get('proposal',0);
             $model->setRequestGood ($goodId, $proposal);             
         }
         elseif ($action == 'editCount')
         {
             $goodId= $request->get('goodId',0);
             $proposal= floatval($request->get('proposal',0));
             $model->setRequestCount ($goodId, $proposal);             
         }
         elseif ($action == 'editMeasure')
         {
             $goodId= $request->get('goodId',0);
             $proposal= $request->get('proposal',0);
             $model->setRequestMeasure ($goodId, $proposal);             
         }
         elseif ($action == 'create')
         {            
         
            $model->type=$request->get('type',""); 
            $model->requestSupplyId=intval($request->get('requestId',0)); 
            $model->refPurchase=intval($request->get('refPurchase',0)); 
            $id = $model->createNewDeliver();
            $model->id = $id;
         }
                 
         if ($id == 0) 
         {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Вероятно не задан идентификатор задания на доставку";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
         }
         
         
         $goodListProvider = $model->getGoodListProvider(Yii::$app->request->get());
         $scladListProvider = $model->getScladListProvider(Yii::$app->request->get());
         $provider= $model->getContentDeliverProvider();
         return $this->render('deliver-zakaz', ['model' => $model, 'goodListProvider'=>$goodListProvider , 'scladListProvider' => $scladListProvider, 'provider' => $provider]);
    }

/*******************************************************************************/       
    
public function actionStoreSdelkaList()
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
       
       return $this->render('store-sdelka-list', ['model' => $model,'provider' => $provider, 'leafValue' => $leafValue]);
    }
            
/*******************************************************************************/   
    public function actionDeliverScladList()
    {
         $request = Yii::$app->request;
         $model = new DeliverSclad();

         $provider= $model->getScladListProvider(Yii::$app->request->get());
         return $this->render('deliver-sclad-list', ['model' => $model, 'provider' => $provider]);
    }


    public function actionSaveDeliverSclad()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;

         $model = new DeliverSclad();
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {

                $sendArray = $model->saveData();
                echo json_encode($sendArray);
                return;
            }
        }
    }


/******************************************************************/
    public function actionDeliverZakazPrint()
    {   
         $request = Yii::$app->request;     
         $model = new DeliversForm();
         
         $id = intval($request->get('id',0));                  
         $model->id = $id;                   
         
         $provider= $model->getContentDeliverProvider();
         return $this->render('deliver-zakaz-print', ['model' => $model, 'provider' => $provider]);
    }
/******************************************************************/
    public function actionDeliverPrintTtn()
    {   
         $request = Yii::$app->request;     
         $model = new DeliversForm();
         
         $id = intval($request->get('id',0));                  
         $model->id = $id;                   
         
         echo $model->printTTNPage ();
         
         exit(0);
    }
    
/*******************************************************************************/   


public function actionDeliverSetupd()
    {   
         $model = new DeliversForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));                          
         $upd=$request->get('upd');                          
         $model->setUPD($id, $upd);         
         $this->redirect(['site/success']);
    }
/*******************************************************************************/   
public function actionDeliverDelete()
    {   
         $model = new DeliversForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));                          
         $model->deleteDeliver($id);         
         $this->redirect(['site/success']);
    }
    
/*******************************************************************************/   
public function actionDeliverOrgList()
    {   
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }

        $request = Yii::$app->request;  
        $act = intval($request->get('act',0));                          
        $model = new DeliversForm();
        $orgListProvider = $model->getOrgListProvider(Yii::$app->request->get());
        return $this->render('deliver-orglist', ['model' => $model, 'orgListProvider'=>$orgListProvider, 'act' => $act]);         
    }
/*******************************************************************************/   
    
    public function actionWarehouse()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('WarehouseSort', $sort);
        }
        else  
        {
           $sort=$session->get('WarehouseSort');
        }
        
         $model = new WarehouseForm();
         $model->setSort =$sort; 
         $provider= $model->getWareListProvider(Yii::$app->request->get());
         return $this->render('warehouse', ['model' => $model, 'provider' => $provider]);
    }

/*******************************************************************************/   
/*******************************************************************************/   

    public function actionPrice()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('WarehouseSort', $sort);
        }
        else  
        {
           $sort=$session->get('WarehouseSort');
        }
          
         $model = new WarehouseForm();
         $model->setSort =$sort; 
         $provider= $model->getWareListProvider(Yii::$app->request->get());
         return $this->render('price', ['model' => $model, 'provider' => $provider]);
    }
/*******************************************************************************/   

    public function actionGooglePrice()
    {
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        

        $model = new PriceForm();
        $model->zakazId= intval($request->get('zakazId', 0));         
        $orgId= intval($request->get('orgId', 0));                 
                
        if($model->isNeedRefresh())
        {            
            $session->set('GooglePriceZakazId', $model->zakazId);
            $session->set('GooglePriceOrgId', $orgId);

            $this->redirect(['data/sync-google-price']);   
           return;           
        }

        if (isset($sort))
        {
            $session->set('GooglePriceSort', $sort);
        }
        else  
        {
           $sort=$session->get('GooglePriceSort');
        }
        
         
         $model->setSort =$sort; 
         $provider= $model->getPriceProvider(Yii::$app->request->get());
         $zakazProvider= $model->getZakazProvider();
         return $this->render('google-price', ['model' => $model, 'provider' => $provider, 'zakazProvider' => $zakazProvider, 'orgId' => $orgId]);
    }


    public function actionAddGpriceZakaz()
    {
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $model = new PriceForm();
        $zakazId= intval($request->get('zakazId', 0));                 
        $priceid= intval($request->get('priceid', 0));         
        $val= floatval($request->get('val', 0));         
        $ed= $request->get('ed', '-');         
      
        $model->addToZakaz ($zakazId, $priceid, $val,$ed);
  
        $this->redirect(['site/success']);   
    }
    
/******************************************************************/
    public function actionMarketPrice()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('WarehouseSort', $sort);
        }
        else  
        {
           $sort=$session->get('WarehouseSort');
        }
        
         $request = Yii::$app->request;     
         $zakazId = intval($request->get('zakazId',0));
         $orgId = intval($request->get('orgId',0));       
        
         if ($orgId ==0 || $zakazId ==0 ) 
         {     
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Не верно заданы параметры заказа";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
         }
         
         $model = new WarehouseForm();
         $model->setSort =$sort; 
         $provider= $model->getWareListProvider(Yii::$app->request->get());
         return $this->render('market-price', ['model' => $model, 'zakazId' => $zakazId, 'orgId' => $orgId, 'provider' => $provider]);
    }
/******************************************************************/
    public function actionMarketingPrice()
    {   
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;  
        $sort = $request->get('sort');                          
        $session = Yii::$app->session;      
        $session->open();
        
        
        if (isset($sort))
        {
            $session->set('WarehouseSort', $sort);
        }
        else  
        {
           $sort=$session->get('WarehouseSort');
        }
        
         $request = Yii::$app->request;     
         
         $model = new WarehouseForm();
         $model->setSort =$sort; 
         $provider= $model->getWareListProvider(Yii::$app->request->get());
         return $this->render('marketing-price', ['model' => $model, 'provider' => $provider]);
    }
    
    /*******************************************************************************/   
    
      public function actionSetMarketprice()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveMarketprice();         
           $this->redirect(['site/success']);
           return;
         }
         
         $id= intval($request->get('id',0));                          
         $model->id = $id; 
         return $this->render('set-marketprice', ['model' => $model]);
    }        

    
    /*******************************************************************************/   
/****** Obsoleted ********/    
  public function actionEnableOtves()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));                          
         $model->setEnableOtves($id);         
         $this->redirect(['site/success']);
    }

/****** Obsoleted ********/    
  public function actionDisableOtves()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id'));                          
         $model->setDisableOtves($id);         
         $this->redirect(['site/success']);
    }

/****** Obsoleted ********/            
  public function actionAddOtves()
    {   
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           if ($model->addOtves() > 0 )     $this->redirect(['site/success']);
           else {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Вероятно превышен лимит остатков на складе, добавление отвеса невозможно";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
           }           
               
           return;
         }

         $id= intval($request->get('id'));                          
         $model->id = $id; 
         return $this->render('add-otves', ['model' => $model]);
    }        

/****** Obsoleted ********/            
  public function actionEditOtves()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->editOtves();         
           $this->redirect(['site/success']);
           return;
         }
         
         $id= intval($request->get('id'));                          
         $model->otvesId = $id; 
         return $this->render('edit-otves', ['model' => $model]);
    }        
/****** Obsoleted ********/    
  public function actionShowOtves()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WarehouseForm();
         $request = Yii::$app->request;  
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->editOtves();         
           $this->redirect(['site/success']);
           return;
         }
         
         $id= intval($request->get('id'));                          
         $model->otvesId = $id; 
         return $this->render('show-otves', ['model' => $model]);
    }        
/**************/    
  public function actionOtvesSvod()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WareOtves();
         $request = Yii::$app->request;  

         $model->mode = intval($request->get('mode',0));                          
         $noframe= intval($request->get('noframe',0));                          
         $provider= $model->getWareOtvesSvodProvider(Yii::$app->request->get());        
         return $this->render('otves-svod', ['model' => $model,'provider' => $provider, 'noframe' => $noframe]);
    }        
        
  public function actionOtvesList()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WareOtves();
         $request = Yii::$app->request;  

         $model->mode = intval($request->get('mode',0));                          
         $noframe= intval($request->get('noframe',0));                          
         $provider= $model->getWareOtvesListProvider(Yii::$app->request->get());        
         return $this->render('otves-list', ['model' => $model,'provider' => $provider, 'noframe' => $noframe]);
    }        
        
  public function actionWareOtvesList()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WareOtves();
         $request = Yii::$app->request;  
        
         $model->wareRef= intval($request->get('wareRef',0));                          

         $model->refSchet = intval($request->get('refSchet',0));                          
         $model->refZakaz = intval($request->get('refZakaz',0));                          
         $model->wareNameRef= intval($request->get('wareNameRef',0));                          
         $model->onlyUsable = 1;
         $model->mode = intval($request->get('mode',5));

         $provider= $model->getWareOtvesListProvider(Yii::$app->request->get());        
         return $this->render('ware-otves-list', ['model' => $model,'provider' => $provider]);
    }        
    

  public function actionOtvesCreate()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new WareOtves    ();
         $request = Yii::$app->request;  

         $model->wareScladRef= intval($request->get('wareScladRef',0));                          
         $model->loadWareData();       
         $provider= $model->getWareOtvesEditProvider(Yii::$app->request->get());        
         return $this->render('otves-create', ['model' => $model,'provider' => $provider]);
    }      
        


  public function actionSaveOtvesData()    
  {
      
        $model = new WareOtves();
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveOtvesData();
                echo json_encode($sendArray);
                return;
            }    
        }  
  
  }

/*******************************************************************************/      
  
  public function actionTransportTarif()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
         $model = new TransportTarif();
         $request = Yii::$app->request;  
         $noframe= intval($request->get('noframe',0));                          
         $provider= $model->getTransportTarifProvider(Yii::$app->request->get());        
         return $this->render('transport-tarif', ['model' => $model,'provider' => $provider, 'noframe' => $noframe]);
    }        
      
/*******************************************************************************/      
  public function actionSupplyRequestList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $model = new WarehouseForm();        
        $provider= $model->getSupplyRequestProvider(Yii::$app->request->get());        
          return $this->render('supply-request-list', ['model' => $model, 'provider' => $provider]);
         
    }
    
    
/*******************************************************************************/      
   public function actionSaveLnkDocRequest    ()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $model = new SupplyForm();        
        $request = Yii::$app->request;  
    
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $sendArray = $model->savelnkDocRequest();            
            if(Yii::$app->request->isAjax)
            {
               echo json_encode($sendArray);
               return;            
            }
            
         }

         print_r($sendArray);
         //  $this->redirect(['site/success']);
           return;
    }


  public function actionSupplyRequest()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $model = new SupplyForm();        
        $request = Yii::$app->request;  
    
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $model->saveData(); 
            $this->redirect(['site/success']);
           return;
         }

        
        $model->id= intval($request->get('id',0));         
        $model->viewMode = $request->get('viewMode', 'simple');
          return $this->render('supply-request', ['model' => $model]);
         
    }

   public function actionSupplyRequestNew()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $model = new SupplyForm();        
        $request = Yii::$app->request;  
    
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $model->saveData(); 
            $this->redirect(['site/success']);
           return;
         }

        
        $model->id= intval($request->get('id',0));         
        $model->viewMode = $request->get('viewMode', 'simple');
          return $this->render('supply-request-new', ['model' => $model]);
         
    }
/********************************/   
   public function actionScenarioEditor()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $model = new SupplyForm();                
          return $this->render('scenario-editor', ['model' => $model]);         
    }
    
   public function actionAddScenario()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $name=$request->get('name');
        $r=$model->addScenario($name);
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
    
   public function actionEditScenario()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $name=$request->get('name');
        $id = intval($request->get('id',0));
        $r=$model->setScenarioName($id,$name);
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
 
  public function actionSetScenarioStatus()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $etap = intval($request->get('etap',0));
        $id = intval($request->get('id',0));
        $status = intval($request->get('status',0));
        $r=$model->setScenarioStatus($id,$etap,$status);
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
    
  public function actionSetScenarioTime()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $etap = intval($request->get('etap',0));
        $id = intval($request->get('id',0));
        $val = intval($request->get('val',0));
        $r=$model->setScenarioTime($id,$etap,$val);
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
/*********************************/        

  public function actionSetScenario()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $requestId = intval($request->get('requestId',0));
        $scenId = intval($request->get('scenId',0));        
        $r=$model->setRequestScenario($requestId,$scenId );
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
/*********************************/        

  public function actionSetSupplyStatus()
    {   
        $request = Yii::$app->request;               
        $model = new SupplyForm();                
        $requestId = intval($request->get('requestId',0));
        $statusId = intval($request->get('statusId',0));        
        $val = $request->get('val',0);        
        $r=$model->setRequestSupplyStatus($requestId,$statusId,$val);
        if ($r==false)
        {
            $this->redirect(['site/problem']);
            return;
        }
        
          $this->redirect(['site/success']);
    }
    

    
/*********************************/    
    public function actionPrintSupplyRequest()
    {   
        $model = new SupplyForm();        
        $request = Yii::$app->request;  
        
        $model->id= intval($request->get('id',0)); 
        $page = $model->printRequestSupply();
        echo $page;
        return;
    
    }
    
  public function actionResyncRemain()
    {   
    
        $model = new SupplyForm();        
        $request = Yii::$app->request;  
        $zakazid = intval($request->get('zakazid',0));                 
          
        $model->resyncRemain($zakazid);         
        $this->redirect(['site/success']); 
    }

/******************************************************************/      
/******************************************************************/
    public function actionPurchaseStart()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->mode= intval($request->get('mode',1)); 
        if($model->mode == 1) $provider = $model->getGoodRequestProvider(Yii::$app->request->get());        
        elseif($model->mode < 6) $provider = $model->getPurchaseZakazListProvider(Yii::$app->request->get());
        else                    $provider = $model->getPurchesListProvider(Yii::$app->request->get());
        
        return $this->render('purchase-start', ['model' => $model,  'provider' => $provider]);
    }

    public function actionHeadPurchaseStart()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        return $this->render('head-purchase-start', ['model' => $model]);
    }

    
/******************************************************************/    
    public function actionHeadPurchaseZakazList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $model = new PurchesForm();
        $provider = $model->getPurchaseZakazListProvider(Yii::$app->request->get());
        return $this->render('head-purchase-zakaz-list', ['model' => $model, 'provider' => $provider]);
    }

    public function actionHeadPurchaseZakaz()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 
        $isNew=0;
        if ($model->id == 0) $isNew=1;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveZakaz(); 
           if ($isNew==1)$this->redirect(['store/head-purchase-zakaz', 'noframe' =>1,  'id' => $model->id]);
           else $this->redirect(['site/success']);
           return;
         }        
        $variantsProvider = $model->getVariantsProvider(Yii::$app->request->get());
        $wareInZakazProvider= $model->getWareInZakazProvider(Yii::$app->request->get());
        $goodListProvider = $model->getGoodListProvider(Yii::$app->request->get());
        return $this->render('head-purchase-zakaz', ['model' => $model, 'wareInZakazProvider' => $wareInZakazProvider,'variantsProvider' => $variantsProvider, 'goodListProvider' => $goodListProvider]);
    }



    public function actionKomPurchaseZakaz()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 
        $isNew=0;
        if ($model->id == 0) $isNew=1;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveZakaz(); 
           if ($isNew==1)$this->redirect(['store/kom-purchase-zakaz', 'noframe' =>1,  'id' => $model->id]);
           else $this->redirect(['site/success']);
           return;
         }        
        $variantsProvider = $model->getVariantsProvider(Yii::$app->request->get());
        $wareInZakazProvider= $model->getWareInZakazProvider(Yii::$app->request->get());
        $goodListProvider = $model->getGoodListProvider(Yii::$app->request->get());
        return $this->render('kom-purchase-zakaz', ['model' => $model, 'wareInZakazProvider' => $wareInZakazProvider,'variantsProvider' => $variantsProvider, 'goodListProvider' => $goodListProvider]);
    }
    
/******************************************************************/    
    public function actionPurchaseZakazList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $model = new PurchesForm();
        $provider = $model->getPurchaseZakazListProvider(Yii::$app->request->get());
        return $this->render('purchase-zakaz-list', ['model' => $model, 'provider' => $provider]);
    }

    public function actionPurchaseZakaz()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 
        $model->zakazRef= intval($request->get('zakazref',0));         
        $isNew=0;
        if ($model->id == 0) $isNew=1;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveZakaz(); 
           if ($isNew==1)$this->redirect(['store/purchase-zakaz', 'noframe' =>1,  'id' => $model->id]);
           else $this->redirect(['site/success']);
           return;
         }        
        $variantsProvider = $model->getVariantsProvider(Yii::$app->request->get());
        $wareInZakazProvider= $model->getWareInZakazProvider(Yii::$app->request->get());
        $goodListProvider = $model->getGoodListProvider(Yii::$app->request->get());
        return $this->render('purchase-zakaz', ['model' => $model, 'wareInZakazProvider' => $wareInZakazProvider,'variantsProvider' => $variantsProvider, 'goodListProvider' => $goodListProvider]);
    }

    public function actionCreateGoodRequest()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $zakazref= intval($request->get('zakazref',0)); 
        $id = $model -> createZaprosFromZakazGood($zakazref); 
        $this->redirect(['store/purchase-zakaz', 'id' => $id, 'noframe' => 1 ]);                   
    }

/*    public function actionCreateJobRequest()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $zakazref= intval($request->get('zakazref',0)); 
        $id = $model -> createZaprosFromZakazJob($zakazref); 
        $this->redirect(['store/purchase-zakaz', 'id' => $id, 'noframe' => 1 ]);                   
    }
  */  
    public function actionPurchaseZakazActivate()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $variantId = intval($request->get('variantId',0)); 
        $model->setVariantActive($id,$variantId);
        $this->redirect(['site/success']);                   
    }    

    public function actionPurchaseZakazAddware()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $wareId = intval($request->get('wareId',0)); 
        $model->addWareInZakaz($id,$wareId);
        $this->redirect(['site/success']);                   
    }    

    public function actionPurchaseZakazRmware()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $wareId = intval($request->get('wareref',0)); 
        $model->rmWareFromZakaz($wareId);
 
        $this->redirect(['site/success']);                   
    }    

    public function actionPurchaseZakazSetcount()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $wareId = intval($request->get('wareref',0)); 
        $wareCount = floatval($request->get('count',0)); 
        $model->setWareZakazCount($wareId,$wareCount);
 
        $this->redirect(['site/success']);                   
    }    

    public function actionPurchaseZakazRmvariant()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $variantId = intval($request->get('variantId',0));         
        $model->purchaseZakazRmVariant($variantId); 
        $this->redirect(['site/success']);                   
    }    

    
    
    public function actionPurchaseZakazSetSchet()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $schetId = intval($request->get('schetId',0)); 
        $ret = $model->setPurchaseZakazSchet($id,$schetId); 
        if ($ret < 0) 
        {
          $session = Yii::$app->session;        
          $session->open();

          if ($ret == -1) $problemMessage    ="Не найден вариант!";
                     else $problemMessage    ="Счет не содержит необходимого товара!";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem', 'noframe' => 1]);
         }
        
//        echo $ret;
//        return;
        $this->redirect(['site/success']);                   
    }    
    
   
    public function actionPurchaseZakazPermit()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazPermit($id); 
        $this->redirect(['site/success']);                   
    }    
    
    public function actionPurchaseZakazRecall()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazRecall($id); 
        $this->redirect(['site/success']);                   
    }    

    public function actionPurchaseZakazPermited()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazPermited($id); 
        $this->redirect(['site/success']);                   
    }    
/* пустить в работу мониторинг цены */
    public function actionPurchaseZakazSetinwork()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazInWork($id); 
        $this->redirect(['site/success']);                   
    }    
    
/* Снять мониторинг цены */    
    public function actionPurchaseZakazUnsetinwork()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazNoWork($id); 
        $this->redirect(['site/success']);                   
    }    

/* выполнен мониторинг цены */
    public function actionPurchaseZakazWorkdone()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazWorkDone($id); 
        $this->redirect(['site/success']);                   
    }    
    
/* не выполнен мониторинг цены */    
    public function actionPurchaseZakazWorkundone()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazWorkUnDone($id); 
        $this->redirect(['site/success']);                   
    }    
    
/**/

    
    public function actionPurchaseZakazUnpermited()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazUnPermited($id); 
        $this->redirect(['site/success']);                   
    }    
    
   public function actionPurchaseZakazDeny()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= intval($request->get('id',0)); 
        $model->setPurchaseZakazDeny($id); 
        $this->redirect(['site/success']);                   
    }     
    
    
   public function actionPurchaseSetRequest()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id            = intval($request->get('id',0)); 
        $editVariantId  = intval($request->get('editVariantId',0)); 
        $sendArray = $model->savePurchaseRequest($id, $editVariantId);                 
        if(Yii::$app->request->isAjax){
             echo json_encode($sendArray);
             return; 
         }        
        $this->redirect(['site/success']);                           
    }     


    
   public function actionPurchaseZakazAddorg()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id            = intval($request->get('id',0)); 
        $orgRef        = intval($request->get('orgref',0)); 
        $orgTitle      = $request->get('orgtitle'); 
        
        
        $model->addOrgToZakaz($id, $orgRef, $orgTitle); 
        $this->redirect(['site/success']);                   
    }     

   public function actionPurchaseZakazDel()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id            = intval($request->get('id',0)); 
        
        $model->purchaseZakazDel($id); 
        $this->redirect(['site/close']);                   
    }     
    

   public function actionPurchaseZaprosFinit()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id            = intval($request->get('id',0)); 
        $relizeVal     = floatval($request->get('relizeVal',0)); 
        if ($relizeVal > 0 && $id > 0)
        {        
            $model->purchaseZaprosFinit($id, $relizeVal); 
            $this->redirect(['site/close']);                   
            return;
        }
        
        $this->redirect(['store/head-purchase-zakaz', 'noframe' => 1, 'id' =>$id]);    
    }     
    
    
  
  
    
   public function actionPurchaseAddFromSchet()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id             = intval($request->get('id',0)); 
        $supplierWareId = intval($request->get('supplierWareId',0)); 

        $model->addWareFromSchet($id, $supplierWareId); 
        
       //return; 
        $this->redirect(['site/success']);        
    }     
    
/******************************************************************/

    public function actionPurchaseCreateFromRequest()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $requestId= $request->get('id','0,'); 
        $id = $model->purchaseCreateFromRequest($requestId);
        if ($id == -1) {$this->redirect(['site/problem']);return;}
        $this->redirect(['purchase-zakaz', 'noframe' => 1, 'id' => $id]);                           
    }    
   
/******************************************************************/
    public function actionPurchaseCreateFromClientZakaz()
    {   
    
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $zakazContentId= $request->get('contentid','0,'); 
        $id = $model->purchaseCreateFromClientZakaz($zakazContentId);
        if ($id == -1) {$this->redirect(['site/problem']);return;}
        $this->redirect(['purchase-zakaz', 'noframe' => 1, 'id' => $id]);                           
    }    

/******************************************************************/
    public function actionRmFromRequest()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $requestId= $request->get('id','0,'); 
        $model->rmFromRequest($requestId);        
        $this->redirect(['site/success']);                               
    }    
   

/******************************************************************/

    public function actionPurchaseCreate()
    {       
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $varlist= $request->get('varlist','0,'); 
        $model->createPurches($varlist);
        
        $this->redirect(['site/success']);                   
    }    
/******************************************************************/    
   public function actionPurchaseList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $provider = $model->getPurchesListProvider(Yii::$app->request->get());
        return $this->render('purchase-list', ['model' => $model, 'provider' => $provider,]);
    }    
    

    public function actionPurchase()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
           $model->id= intval($request->get('id',0)); 

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->savePurchase(); 
           $this->redirect(['store/purchase', 'id' => $model->id]);                   
        }        

        return $this->render('purchase', ['model' => $model]);
    }
    
    public function actionSavePurchData()
    {   
        $request = Yii::$app->request;    
        $model = new PurchesForm();
    
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveLnkAjax();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    

/******************************************************************/    
   public function actionHeadPurchaseList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $provider = $model->getPurchesListProvider(Yii::$app->request->get());
        return $this->render('head-purchase-list', ['model' => $model, 'provider' => $provider,]);
    }    
    

    public function actionHeadPurchase()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 
        return $this->render('head-purchase', ['model' => $model]);
    }

/*******************************************************************************/      
 public function actionPurchaseOrgList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
         $model = new OrgContactForm();
         $orgListProvider = $model->getOrgListProvider(Yii::$app->request->get());
         return $this->render('purchase-org-list', ['model' => $model, 'orgListProvider'=>$orgListProvider]);         
    }

/*******************************************************************************/      
 public function actionPurchaseSchetList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
         $model = new PurchesForm();
         $request = Yii::$app->request;

     
         $model ->fromDate = $request->get('fromDate',  date('d.m.Y', time()-10*24*3600));
         $model ->toDate = $request->get('toDate', date('d.m.Y'));
         
         $model->supplierRef= intval($request->get('supplierRef',0)); 
         $schetProvider = $model->getPurchaseSchetList(Yii::$app->request->get());
         $docProvider   = $model->getPurchaseDocList(Yii::$app->request->get());
         return $this->render('purchase-schet-list', ['model' => $model, 'schetProvider'=> $schetProvider,
         'docProvider' => $docProvider
         ]);         
    }

 public function actionPurchaseZaprosList()
    {   
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new PurchesForm();
         $request = Yii::$app->request;  
         $model->id= intval($request->get('zaprosId',0)); 
         $providerWare = $model->getWareInPurcheProvider(Yii::$app->request->get());    
         $provider = $model->getPurchaseZaprosList(Yii::$app->request->get());
         return $this->render('purchase-zapros-list', ['model' => $model, 'provider'=> $provider, 'providerWare' => $providerWare]);         
    }
    

 public function actionPurchaseControlList()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
         $model = new PurchesForm();
         $request = Yii::$app->request;

     
         $model ->fromDate = $request->get('fromDate',  date('d.m.Y', time()-10*24*3600));
         $model ->toDate = $request->get('toDate', date('d.m.Y'));
         
         $showTab=$request->get('showTab', 0);
         $lnkId=$request->get('lnkId', 0);
         
         $model ->fltOrgTitle= $request->get('fltOrgTitle', '');
         $model->supplierRef= intval($request->get('supplierRef',0)); 
         $controlProvider = $model->getPurchaseControlProvider(Yii::$app->request->get());         
         $docProvider   = $model->getPurchaseDocList(Yii::$app->request->get());
         return $this->render('purchase-control-list', ['model' => $model, 'controlProvider'=> $controlProvider, 'docProvider' => $docProvider,        
         'showTab' => $showTab, 'lnkId' => $lnkId
         ]);         
    }
    
        
 /*public function actionRmZaprosFromPurchase()
    {       
         $model = new PurchesForm();
         $request = Yii::$app->request;  
         $id= intval($request->get('id',0)); 
         $model->removeZaprosFromPurchase($id);
    }*/

 public function actionPurchaseRequestList()
    {   
    
         $model = new PurchesForm();
         $request = Yii::$app->request;  
         $provider = $model->getGoodRequestProvider(Yii::$app->request->get());        
         return $this->render('purchase-request-list', ['model' => $model, 'provider'=> $provider]);         
    }
    
    public function actionPurchaseWareSchet()
    {   
    
         $model = new PurchesForm();
         $request = Yii::$app->request;           
         $provider = $model->getPurchaseWareSchet(Yii::$app->request->get());
         return $this->render('purchase-ware-schet', ['model' => $model, 'provider'=> $provider]);         
    }
/* Закупка */    
  public function actionPurchaseSetSchet()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= $request->get('id','0'); 
        $schetId= $request->get('schetId','0'); 
        $schetType= $request->get('schetType','0'); 
        $model->setSchet($id, $schetId, $schetType);
        $this->redirect(['site/success']);                   
    }    
  public function actionPurchaseSchetUnlink()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= $request->get('id','0'); 
        $model->unlinkSchet($id);
        
      $this->redirect(['site/success']);                   
    }    
    
/*Присоеденим запрос к закупке*/
  public function actionPurchaseZaprosLink()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $purchaseId= $request->get('id','0'); 
        $zaprosWareId= $request->get('zaprosWareId','0'); 
        $r=$model->setPurchaseZaprosLink($purchaseId, $zaprosWareId);
        if ($r)      $this->redirect(['site/success']);                   
        
        echo "Error while add in ".$purchaseId." the ".$zaprosWareId;
    }    
/*Уберем запрос из закупки*/    
  public function actionPurchaseRmware()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $purchaseId= $request->get('id','0'); 
        $zaprosWareId= $request->get('wareref','0'); 
        $r=$model->rmPurchaseZaprosLink($purchaseId, $zaprosWareId);


/*        echo $purchaseId;
        echo "<br>";
        echo $zaprosWareId;
return;        */
       if ($r)      $this->redirect(['site/success']);                   
        
        echo "Errror while remove from ".$purchaseId." the ".$zaprosWareId;
    }    

/* Запрос цены */
    
/*Присоединим заявку на закупку от продажников к запросу цены*/       
  public function actionPurchaseZakazAddLink()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $requestId= $request->get('requestId','0'); 
        $zaprosId= $request->get('zaprosId','0'); 
        $model->purchaseZakazAddLink($requestId, $zaprosId);
        
        $this->redirect(['site/success']);                   
    }    
    
  public function actionPurchaseRmLink()
    {   
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $requestId= $request->get('requestId','0'); 
        $model->purchaseZakazRmLink($requestId);
        $this->redirect(['site/success']);                   
    }    

    
   public function actionPurchaseSetVal()
    {   
        if (Yii::$app->user->isGuest)$this->redirect(['site/index']);         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= $request->get('id','0,'); 
        $boxid= $request->get('boxid',''); 
        $dateVal= $request->get('dateVal',''); 
          
        $ret = $model->setStageSatus($id, $boxid, $dateVal );
        if ($ret ==0)$this->redirect(['site/success']);                   
        switch ($ret)
        {
            case -1:
                echo "system error";
            break;

            case -2:
                echo "Record Not Found";
            break;

            case -3:
                echo "Multiply purchase for on order";
            break;
                        
            default:
            echo "Unknown error";
        }
       
    }    

   public function actionPurchaseUnreject()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $id= $request->get('id','0,'); 
          
        $ret = $model->setPurchaseUnreject($id);
        if ($ret){
            $this->redirect(['site/success']);                   
            return;
        }

        $session = Yii::$app->session;        
        $session->open();        
        $problemMessage    ="Невозможно выполнить операцию, возможно неверно задан идентификатор закупки.";
        $session->set('problemMessage', $problemMessage);         
        $this->redirect(['site/problem']);
        return;        
    }    
    

/*******************************************************************************/      
   public function actionPurchaseTable()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->mode = $request->get('mode','0'); 
        $provider = $model->getPurchTableProvider(Yii::$app->request->get());
           return $this->render('purchase-table', ['model' => $model, 'provider'=> $provider]);         

    }    

   public function actionPurchaseSelect()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->mode = $request->get('mode','0'); 
        $provider = $model->getPurchTableProvider(Yii::$app->request->get());
           return $this->render('purchase-select', ['model' => $model, 'provider'=> $provider]);         

    }    
 
    
    
   public function actionZaprosTable()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->mode = $request->get('mode','1'); 
        $provider = $model->getZaprosTableProvider(Yii::$app->request->get());
           return $this->render('zapros-table', ['model' => $model, 'provider'=> $provider]);         

    }    
      
   public function actionZapros()
    {   
    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveZakaz(); 
           $this->redirect(['site/success']);
           return;
         }        
        $variantsProvider = $model->getVariantsProvider(Yii::$app->request->get());
        $wareInZakazProvider= $model->getWareInZakazProvider(Yii::$app->request->get());
        $goodListProvider = $model->getGoodListProvider(Yii::$app->request->get());
        return $this->render('zapros', ['model' => $model, 'wareInZakazProvider' => $wareInZakazProvider,'variantsProvider' => $variantsProvider, 'goodListProvider' => $goodListProvider]);
    }    

/*******************************************************************************/      

   public function actionZaprosOdobrenie()
    {   
    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $model->id= intval($request->get('id',0)); 

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveZaprosOdobrenie(); 
           $this->redirect(['site/success']);
           return;
         }        
           
        return $this->render('zapros-odobrenie', ['model' => $model, ]);
    }    
  

     public function actionChngZaprosCategory()
    {   
    
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $zaprosId= intval($request->get('zaprosId',0)); 
        $zaprosWareType= intval($request->get('zaprosType',0));
        $zaprosCategory= intval($request->get('zaprosCategory',0)); 
        $wareTitle= $request->get('wareTitle'); 
        
           $model->chngCategoryType ($zaprosId, $zaprosCategory, $zaprosWareType, $wareTitle); 
           $this->redirect(['site/success']);
           return;
    }    
  

     public function actionZaprosSwitchInList()
    {   
    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }       
        $request = Yii::$app->request;  
        $model = new PurchesForm();
        $variantId= intval($request->get('variantId',0));         
           $model->zaprosSwitchInList ($variantId); 
           $this->redirect(['site/success']);
           return;
    }    
  
    
    
/*******************************************************************************/      

   public function actionWareContent()
    {   
    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new WareForm();
        $model->strDate= $request->get('strDate',date('Y-m-d')); 
        $model->fltScladTitle= $request->get('scladTitle','');
        $model->fltOrgTitle= $request->get('fltOrgTitle','');          
        $model->errOnly= intval($request->get('errOnly',0)); 
        $provider = $model->getWareListProvider(Yii::$app->request->get());
        
        
        return $this->render('ware-content', ['model' => $model, 'provider' => $provider, ]);
    }    

   public function actionSwitchWareActive()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new WareForm();
     $id = intval($request->get('id',0)); 
     
      $model->switchWareActive($id);  
      
      $this->redirect(['site/success']);
      return;
   }



   public function actionWareUse()
    {   
    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new WareForm();
        $model->strDate= $request->get('strDate',date('Y-m-d')); 
       
        $provider =  $model->getWareUseProvider(Yii::$app->request->get());
        $orgProvider =  $model->getWareOrgProvider(Yii::$app->request->get());
        return $this->render('ware-use', ['model' => $model, 'provider' => $provider, 'orgProvider' => $orgProvider ]);
    }    

   public function actionSwitchWareFiltered()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new WareForm();
     $org       = intval($request->get('org',0));
     $filterVal = intval($request->get('filterVal',0));  
     
      $model->switchWareWareFiltered($org,$filterVal);  
      
      $this->redirect(['site/success']);
      return;
   }

   public function actionSwitchWareInsum()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new WareForm();
     $id       = intval($request->get('id',0));
      $model->switchWareWareInSum($id);  
      $this->redirect(['site/success']);
      return;
   }
      
   public function actionSwitchWareUse()
   {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
     $request = Yii::$app->request;  
     $model = new WareForm();
     $id = intval($request->get('id',0)); 
     $strDate= $request->get('strDate',date('Y-m-d')); 
     
      $model->switchWareUse($id,$strDate);  
      
      $this->redirect(['site/success']);
      return;
   }


/*******************************************************************************/      
   public function actionProductStart()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  

        
           return $this->render('product-start');         

    }    
 
      
        
/*******************************************************************************/      
/*******************************************************************************/      
    public function actionSupplyRequestReestr()
    {

       $request = Yii::$app->request;    
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
         
         $model = new SupplyRequestReestr();          
         $model->mode = intval($request->get('mode',0));         
         $provider=$model->getSupplyRequestReestrProvider(Yii::$app->request->get());
         
         switch ($model->mode){
           case 1 :
               return $this->render('supply-market-reestr', ['model' => $model,'provider' => $provider]);
           break;    
           
           default:  return $this->render('supply-request-reestr', ['model' => $model,'provider' => $provider]);
         }
    }



/*******************************************************************************/          
//save-supply-request-note
    public function actionSaveSupplyRequestNote()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new SupplyRequestReestr();
    
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveSupplyRequestNote();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
/*******************************************************************************/          
//switch-in-supply-request
    public function actionSwitchInSupplyRequest()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new SupplyRequestReestr();
    
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->switchInRequest();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
/*******************************************************************************/          
    public function actionSaveDataSupplyRequest()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new SupplyRequestReestr();
    
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveSupplyDataVal();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
/*******************************************************************************/   
 public function actionSaleList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
    $request = Yii::$app->request;    
    
         $model = new SaleForm();
         $defOwner = 0; //$model->getDefOwner();
         $model->curOwner = intval($request->get('curOwner',$defOwner)); 
        
         $model->mode = $request->get('mode', 0);          
         $from_date = $request->get('from_date', date('d.m.Y', time() -30*24*3600));          
         $to_date =   $request->get('to_date',date('d.m.Y', time()) );

         $model->fltDate = $request->get('fltDate', 0);
        
         if(empty($model->fltDate)){  
             $model->from = strtotime($from_date);
             $model->to   = strtotime($to_date);
         }else
         {
             $model->from = strtotime($model->fltDate);
             $model->to   = $model->from+24*3600;
         }
                  
         $provider = $model->getSaleProvider(Yii::$app->request->get());
         return $this->render('sale-list', ['model' => $model, 'provider'=>$provider ]);         
    }
    
 public function actionSaleCalendar()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        
        $request = Yii::$app->request; 
        $model = new SaleForm();                    
        
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));        
           
        return $this->render('sale-calendar', ['model' => $model, 'month' => $month, 'year' => $year  ]);                       
          
    }
    
/*******************************************************************************/       
    public function actionWareShow()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();       
         $model->errOnly = $request->get('errOnly',0 );   
         $provider= $model->getWareShowProvider(Yii::$app->request->get());
         return $this->render('ware-show', ['model' => $model, 'provider' => $provider, 'errOnly' =>$model->errOnly ]);
    }

    public function actionWareSclad()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();                
         $provider= $model->getWareScladProvider(Yii::$app->request->get());
         return $this->render('ware-sclad', ['model' => $model, 'provider' => $provider, 'errOnly' =>$model->errOnly ]);
    }

    
    public function actionWareScladDetail()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();                
         $model->grpRef= $request->get('grpRef',0 );   
         $provider= $model->getWareScladProvider(Yii::$app->request->get());
         return $this->render('ware-sclad-detail', ['model' => $model, 'provider' => $provider, 'errOnly' =>$model->errOnly ]);
    }
    
    public function actionWareGrpSclad()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();                
          //$model->wareEd='кг';
         $provider= $model->getWareGrpScladProvider(Yii::$app->request->get());
         return $this->render('ware-grp-sclad', ['model' => $model, 'provider' => $provider, 'errOnly' =>$model->errOnly ]);
    }

    public function actionWareSchetDetail()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new WareForm();  
        
        $model->grpRef = $request->get('grpRef',0 );
        $model->state = $request->get('state',0 );      
        
                      
        $provider= $model->getWareSchetDetailProvider(Yii::$app->request->get());
         return $this->render('ware-schet-detail', ['model' => $model, 'provider' => $provider, ]);
    }


        
    public function actionGoodCard()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new WareGoodForm();                
        $model->id=$request->get('id',0 );   
        $purchProvider = $model->getPurchProvider(Yii::$app->request->get());
        $supplyProvider= $model->getSupplyProvider(Yii::$app->request->get());
        return $this->render('good-card', ['model' => $model, 'purchProvider' => $purchProvider, 'supplyProvider' =>$supplyProvider ]);
    }


    /************************************/
/**
 * Show select ware type dialog 
 * @param  none 
 * @return try to exec parent.setSelectedType(typeRef)
 * typeRef Int - ref to type index
 * require user is not Guest
 * @throws Exception 
 */
    public function actionWareTypeSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();         
         $provider= $model->getWareTypesProvider(Yii::$app->request->get());
         return $this->render('ware-type-select', ['model' => $model, 'provider' => $provider]);
    }
 
 
 /**
 * Show select ware group dialog 
 * @param  none 
 * @return try to exec parent.setSelectedGroup(grpRef)
 * grpRef Int - ref to group index
 * require user is not Guest
 * @throws Exception 
 */
 
    public function actionWareGroupSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();         
         $provider= $model->getWareGroupProvider(Yii::$app->request->get());
         return $this->render('ware-group-select', ['model' => $model, 'provider' => $provider]);
    }
 
 
 /**
 * Show select producer dialog 
 * @param  none 
 * @return try to exec parent.setSelectedProducer(prodRef)
 * prodRef Int - ref to producer index
 * require user is not Guest
 * @throws Exception 
 */
    
    public function actionWareProducerSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();         
         $provider= $model->getWareProducerProvider(Yii::$app->request->get());
         return $this->render('ware-producer-select', ['model' => $model, 'provider' => $provider]);
    }
    
 /**
 * Show select ware ed dialog for ware-names table
 * @param  none 
 * @return try to exec parent.setSelectedWareEd(edValue) 
 * edValue String with ed value
 * require user is not Guest
 * @throws Exception 
 */
    public function actionWareNameEdSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareNames();         
         $provider= $model->getWareNameEdProvider(Yii::$app->request->get());
         return $this->render('ware-name-ed-select', ['model' => $model, 'provider' => $provider]);
    }

    
 /**
 * Show select ware form dialog for ware-names table
 * @param  none 
 * @return try to exec parent.setSelectedWareForm(formRef) 
 * formRef Int - reference to ware_form table
 * require user is not Guest
 * @throws Exception 
 */
    public function actionWareFormSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareNames();         
         $provider= $model->getWareFormProvider(Yii::$app->request->get());
         return $this->render('ware-form-select', ['model' => $model, 'provider' => $provider]);
    }
            
    /************************************/
    public function actionSaveGoodCard()
    {    
        $request = Yii::$app->request;    
        $model = new WareGoodForm();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveGoodCard();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    

    
    public function actionSaveWarehouseDetail()
    {    
        $request = Yii::$app->request;    
        $model = new WareForm();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveWarehouseDetail();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
 /*********************************************************************************/    
    public function actionWareConfig()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();                  
         return $this->render('ware-config', ['model' => $model]);
    }

    public function actionWareConfigType()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
         $model = new WareForm();
         return $this->render('ware-config-type', ['model' => $model]);
    }

    public function actionWareConfigGroup()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
         $model = new WareForm();
         return $this->render('ware-config-group', ['model' => $model]);
    }

    public function actionWareConfigProducer()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
         $model = new WareForm();
         return $this->render('ware-config-producer', ['model' => $model]);
    }

    public function actionWareConfigFormat()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
         $model = new WareForm();
         return $this->render('ware-config-format', ['model' => $model]);
    }
    
    public function actionSaveWarehouseCfg()
    {    
        $request = Yii::$app->request;    
        $model = new WareForm();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveWarehouseCfg();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
    
 /*********************************************************************************/    
    public function actionWareList()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareForm();        
         $model->errOnly = $request->get('errOnly',0 );   
         $provider= $model->getWareProvider(Yii::$app->request->get());
         return $this->render('ware-list', ['model' => $model, 'provider' => $provider]);
    }


    public function actionSaveWareListDetail()
    {    
        $request = Yii::$app->request;    
        $model = new WareForm();
    
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveWareListDetail();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    


    public function actionSaveNomenklaturaDetail()
    {
        $request = Yii::$app->request;
        $model = new WareNomenklatura();

        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
                $sendArray = $model->saveWareListDetail();
                echo json_encode($sendArray);
                return;
            }
        }
    }


    public function actionWareSelect()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;          
        $model = new WareNames();         
        $model->isActive = 1;
        
        
        $model->wareTypeName = intval($request->get('wareType',0 ));
        $model->wareGrpTitle= intval($request->get('wareGrp',0 ));
       $model->wareProdTitle= intval($request->get('wareProd',0 ));
        
        $model->format  = $request->get('format',"" );
     //   $model->wareWidth  = $request->get('wareWidth',"" );
//        $model->wareLength = $request->get('wareLength',"" );
        $model->density = $request->get('density',"" );
        $model->wareSort= $request->get('wareSort',"" );
        $model->wareMark= $request->get('wareMark',"" );
        $model->showProdutcion= $request->get('showProdutcion',"" );

        $model->mode=$request->get('mode',"0" );
        $model->orgRef=$request->get('orgRef',0 );

        $model->refSchet = intval($request->get('refSchet',0));                          
        $model->refZakaz = intval($request->get('refZakaz',0));
                
//        $model->isActive=0;
        
  //      $model->saleType =$request->get('saleType',1 );
        
        $provider= $model->getWareSelectNameProvider(Yii::$app->request->get());
        return $this->render('ware-select', ['model' => $model, 'provider' => $provider]);
    }
    
    public function actionWarePrice()
    {
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;          
        $model = new WareNames();         
                
        $format=$request->get('format','html' );
        if ($format == 'print')
        {
            $dataArray= $model->getWarePriceArray(Yii::$app->request->get());
            return $this->render('ware-price-print', ['model' => $model, 'dataArray' => $dataArray]);
        }
        $provider= $model->getWarePriceProvider(Yii::$app->request->get());

        return $this->render('ware-price', ['model' => $model, 'provider' => $provider]);
    }





     public function actionGetWarePrice()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;          
        $model = new WareNames();         
        $wareRef=intval($request->get('id',0 ));        
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWarePriceData($wareRef);
                echo json_encode($sendArray);
                return;
        }
        
    }
        


    public function actionGetWareTypes()
    {
        $request = Yii::$app->request;
        $model = new WareNomenklatura();
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWareTypes();
                echo json_encode($sendArray);
                return;
        }
    }

    public function actionGetWareProducers()
    {
        $request = Yii::$app->request;
        $model = new WareNomenklatura();
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWareProducer();
                echo json_encode($sendArray);
                return;
        }
    }

    public function actionGetWareGroups()
    {
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        $model->wareType = intval($request->get('wareType',0 ));
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWareGroups();
                echo json_encode($sendArray);
                return;
        }       
    }

    public function actionGetFormatList()
    {
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        $model->saleType = intval($request->get('saleType',1 ));
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWareFormat();
                echo json_encode($sendArray);
                return;
        }       
    }
    
    public function actionGetFormatDetails()
    {
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        $model->wareFormatSel = intval($request->get('wareFormatSel',0 ));
       // if(Yii::$app->request->isAjax)
        {
            $model->loadWareSetPar();
            $sendArray = [
            'rolType'    => $model->rolType,
            'wareWidth'  => $model->wareWidth,
            'wareLength' => $model->wareLength,
            'wareFormat' => $model->wareFormat,
            'wareFormatSel' => $model->wareFormatSel,
            ];
                echo json_encode($sendArray);
                return;
        }       
    }
    
    
    public function actionWareSetFrame()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        
        $model->id = intval($request->get('id',0 ));

        $model->wareType = intval($request->get('wareType',0 ));
        $model->wareGroup= intval($request->get('wareGrp',0 ));
        $model->wareProducer= intval($request->get('wareProd',0 ));

        $model->wareFormat  = $request->get('wareFormat',"" );
        $model->wareDensity = $request->get('wareDensity',"" );
        $model->wareDensitySel = $model->wareDensity;

        $model->wareSort= $request->get('wareSort',"" );        
        $model->wareMark= $request->get('wareMark',"" );
        $model->addNote= $request->get('addNote',"" );                

        $model->wareWidth  = $request->get('wareWidth',"" );
        $model->wareLength = $request->get('wareLength',"" );
                
        $model->saleType =$request->get('saleType',1 );
                              
       // $provider= $model->getWareSetProvider(Yii::$app->request->get(),0);
          return $this->render('ware-set-frame', ['model' => $model, /*'provider' => $provider,*/ ]);
    }
    
        
    public function actionCreateWareTitle()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;    
        $model = new WareNomenklatura();
    
            if ($model->load(Yii::$app->request->post()) /*&& $model->validate()*/) 
            {                
            if(Yii::$app->request->isAjax)
             {       
                $sendArray = $model->createWareTitle();
                echo json_encode($sendArray);
                return;                
            }    
          }  

        echo "<pre>";
            print_r($model);
        echo "</pre>";
    }

    public function actionGetWareData()
    {
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        $model->id = intval($request->get('id',0 ));
       // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getWareData();
                echo json_encode($sendArray);
                return;
        }       
    }
    

    public function actionSaveWare()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
        $model = new WareNomenklatura();

            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
            $sendArray = $model->saveData();
            if(Yii::$app->request->isAjax)
             {
                echo json_encode($sendArray);
                return;
             }
             else
             
             
             $this->redirect(['store/ware-card', 'id' => $sendArray['id'], 'noframe' => 1]);
             return;
          }
    }

/**************************/
    public function actionLnkWare()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;    
        $model = new WareNomenklatura();
        $act= $request->get('act','1');
        $src= $request->get('src','sclad');
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {                
           // if(Yii::$app->request->isAjax)
             {

                $sendArray = $model->lnkWare($act, $src);
                echo json_encode($sendArray);
                return;                
            }    
          }  
        
        /*$sendArray = $model->lnkWare($act, $src);
        echo "<pre>";        
            print_r($model);
            print_r($sendArray);                        
        echo "</pre>";      
        */
    } 
        
    
  /*********************************/  
        
    public function actionWareCard()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();
        $model->id =   $request->get('id',0 );
        return $this->render('ware-card', ['model' => $model]);
    }


    public function actionWareCardRealize()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
        $model = new WareNames();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
             $sendArray = $model->saveData();
             if(Yii::$app->request->isAjax)
             {
                echo json_encode($sendArray);
                return;
             }
         $this->redirect(['store/ware-card-realize', 'id' => $sendArray['id'], 'noframe' => 1]);
         return;
        }

        $model->id =   $request->get('id',0 );
        return $this->render('ware-card-realize', ['model' => $model]);
    }


    /*=---------------------------*/  
    public function actionWareSet()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
        $model = new WareNomenklatura();  
        
        $model->id = intval($request->get('id',0 ));
        $model->refSclad= intval($request->get('refSclad',0 ));
        $model->refName = intval($request->get('refName',0 ));
        
        /*если конструктора нет, то через карточку*/        
        if ($model->useKonstructor==1) {             
            return $this->render('ware-set', ['model' => $model, ]);
        }

        $this->redirect(['store/ware-card', 'id' => $model->id, 'noframe' => 1]);
        
    }
  
    /*=---------------------------*/  
    
   public function actionSaveWareDetail()
    {    
        $request = Yii::$app->request;    
        $model = new WareForm();
    
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveWareDetail();
                echo json_encode($sendArray);
                return;
            }    
        }
    }    
    
 /*********************************************************************************/    
    public function actionWareNames()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }      
        $request = Yii::$app->request;  
         $model = new WareNames();               
         $model->mode =   $request->get('mode',0 );     
         $provider= $model->getWareNameProvider(Yii::$app->request->get());
         return $this->render('ware-names', ['model' => $model, 'provider' => $provider]);
    }


   public function actionSaveWareNameDetail()
    {    
        $request = Yii::$app->request;    
        $model = new WareNames();
    
       // if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveWareNameDetail();
                echo json_encode($sendArray);
                return;
            }    
        }
    }           
    
/*******************************************************************************/       
        
   public function actionDstNoteSelect()
    {   
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new DstNoteForm();
        $model->refOrg = $request->get('refOrg','0'); 
        $provider = $model->getDstNoteProvider(Yii::$app->request->get());
           return $this->render('dst-note-select', ['model' => $model, 'provider'=> $provider]);         

    }    

   public function actionGetDstNote()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        $request = Yii::$app->request;  
        $model = new DstNoteForm();
        $id = $request->get('id','0');         
        // if(Yii::$app->request->isAjax)
        {
                $sendArray = $model->getDstNote($id);
                echo json_encode($sendArray);
                exit (0);
                return;
        }
    }    
 
    
/*******************************************************************************/       
    

    public function actionRecalcCostValue()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $id =   $request->get('id',0 );        
        $model = new PurchesForm();
    
        //if(Yii::$app->request->isAjax)
        {
           $sendArray = $model->recalcCostValue($id);
           echo json_encode($sendArray);
           return;
        }
    } 
    
    public function actionRecalcCostControlValue()
    {   
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $id =   $request->get('id',0 );        
        $model = new PurchesForm();
    
        //if(Yii::$app->request->isAjax)
        {
           $sendArray = $model->recalcCostControlValue($id);
          // print_r($sendArray);
           echo json_encode($sendArray);           
           exit(0);
           return;
        }
    } 
    
/*******************************************************************************/   
/*******************************************************************************/       
}
