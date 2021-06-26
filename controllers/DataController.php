<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

use app\models\DataUploadCsvForm;
use app\models\DataSyncGoogle;
use app\models\DataSync;
use app\models\DataConsoleSync;   
use app\modules\bank\models\BankOperation;  
/*
Загрузка - выгрузка
*/
class DataController extends Controller
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

    /**********************************************************
        Start form
    ***********************************************************/
    
    
    public function actionDataStart()
    {         
        $model = new DataSyncGoogle();
        $model->loadDefaultUrl();
           $request = Yii::$app->request;    
         $session = Yii::$app->session;        
        $session->open();

        $session->set('createNewOrg', 0); // добавлять организации
        $session->set('syncAllUser',  1);  // Синхронизировать для всех пользователей        
        $session->set('createNewSchet',  0);  // Создавать счет
        $session->set('updateExistedSchet',  1);  // апдейтить существующий
        $session->set('forceUpdateSchet',  0);  // апдейтить даже если уже синхронизирован        
        $session->set('syncDate',   '2010-01-01' ); // период синхронизации


        if ($request->isPost) 
         {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveUrls();           
                
                switch ($model->actionCode)
                {
                 case 1:    
                    $this->redirect(['data/sync-client','noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'updExistedClients' => 0]);
                    return;
                 case 2:    
                    $this->redirect(['data/sync-schet','noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0 ]);
                    return;
                 case 3:    
                    $this->redirect(['data/sync-oplata','noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);
                    return;
                 case 4:    
                    $this->redirect(['data/sync-supply','noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);
                    return;
                 case 5:    
                    $this->redirect(['data/sync-all','noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);
                    return;
                    
                }
            }
         }

        return $this->render('data-start',['model' => $model]);
    }
   /*********************************************************/
    public function actionFlashResult()
    {         

        $session = Yii::$app->session;      
        $session->open();   
        
        $session->set('syncClientResult',array());
        $session->set('syncSchetResult',array());
        $session->set('syncOplataResult', array());        
        $session->set('syncSupplyResult', array());        
        
        $session->set('syncSupplierResult',array());       
        $session->set('syncSupplierSchetResult', array());        
        $session->set('syncSupplierOplataResult',array());
        $session->set('syncSupplierWaresResult', array());        

    }    
     /**********************************************************
        Load and parse link from 1C
    ***********************************************************/

    /* Выведем результат синхронизации */    
    public function actionSyncResult()
    {         
        $session = Yii::$app->session;        
        $session->open();
        $session->set('parentForm', 'data/sync-result');
        $session->set('actionType', 0);
      
        
        $resultArray['syncClientResult'] = $session->get('syncClientResult');        
        $resultArray['syncSchetResult'] = $session->get('syncSchetResult');        
        $resultArray['syncOplataResult'] = $session->get('syncOplataResult');
        $resultArray['syncSupplyResult'] = $session->get('syncSupplyResult');

        $resultArray['syncSupplierResult'] = $session->get('syncSupplierResult');                
        $resultArray['syncSupplierSchetResult'] = $session->get('syncSupplierSchetResult');                
        $resultArray['syncSupplierOplataResult'] = $session->get('syncSupplierOplataResult');                
        $resultArray['syncSupplierWaresResult'] = $session->get('syncSupplierWaresResult');                




        
        $session->set('createNewOrg', 0); // добавлять организации
        $session->set('syncAllUser',  0);  // Синхронизировать для всех пользователей        
        $session->set('createNewSchet',  0);  // Создавать счет
        $session->set('updateExistedSchet',  1);  // апдейтить существующий
        $session->set('forceUpdateSchet',  1);  // апдейтить даже если уже синхронизирован        
        $session->set('syncDate',   '' ); // период синхронизации

    
        return $this->render('sync-result', ['resultArray' => $resultArray] );

    }    
    /***********************************************************/

     public function actionSyncAll()
     {         
        
        $request = Yii::$app->request;                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
                
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                
        
        $session = Yii::$app->session;      
        $session->open();

        $actionType = $session->get('actionType', 1);
        if (empty($actionType)) {$actionType=1;}                

        /*Начнем*/
        if ($actionType == 1)
        {    
           $this->actionFlashResult();
         /*Клиенты*/    
            $session->set('actionName', 'data/sync-client');
            $session->set('parentForm', 'data/sync-all');
            $session->set('actionType', '2');
            $session->set('syncSubtitle', 'Список клиентов...');                        
            $this->redirect(['data/sync-client' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'updExistedClients' => 0]);            
            return;
        }
        
        if ($actionType == 2)
        {    
            /*Счета*/    
            $session->set('actionName', 'data/sync-schet');
            $session->set('parentForm', 'data/sync-all');
            $session->set('actionType', '3');
            $session->set('syncSubtitle', 'Список счетов...');
                        
            $this->redirect(['data/sync-schet' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }
        if ($actionType == 3)
        {    
            /*Счета*/    
            $session->set('actionName', 'data/sync-oplata');
            $session->set('parentForm', 'data/sync-all');
            $session->set('actionType', '4');
            $session->set('syncSubtitle', 'Список оплат...');
                        
            $this->redirect(['data/sync-oplata' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }

        if ($actionType == 4)
        {    
            /*Счета*/    
            $session->set('actionName', 'data/sync-supply');
            $session->set('parentForm', 'data/sync-all');
            $session->set('actionType', '5');
            $session->set('syncSubtitle', 'Список отгрузок...');
                        
            $this->redirect(['data/sync-supply' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }
        
        $session->set('actionType', '0');
        $this->redirect(['data/sync-result']);        
    }
    /***********************************************************/
    /*Вызов обновления клиентов*/
     public function actionSyncClient()
     {         
        $request = Yii::$app->request;
        $model = new DataSyncGoogle();        
                    
         $session = Yii::$app->session;        
        $session->open();

        
                    
        $startRow = $request->get('startRow',1);
        $updExistedClients = $request->get('updExistedClients',0);

        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-client');
        $session->set('syncSubtitle', 'Список клиентов...');                        
        
         
        if ($mode == 0)
        {            
        $session->set('updExistedClients', $updExistedClients);                        
        $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
        return;        
        }
        
        $updExistedClients = $session->get('updExistedClients', 0);                        
        
        
        $retSync=$model->loadNClientBase($updExistedClients, $startRow);    
        $session->set('syncClientResult', $retSync);
            
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
        return;
        
    }
/***********************************************************/
    
    public function actionSyncSchetById()
    {
        $request = Yii::$app->request;
        $model = new DataSyncGoogle();                                
        $model->syncAllUser=1;
        $model->createNewSchet = 1;  // Создавать счет
        $model->updateExistedSchet = 1;  // апдейтить существующий
        $model->forceUpdateSchet   = 1;  // апдейтить даже если уже синхронизирован
        $schetId   = $request->get('schetId',0);    
        $refOrg    = $request->get('refOrg',0);            
        if ($schetId == 0)
        {

                $problemMessage    ="Вероятно не задан идентификатор счета в системе";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;                
        }

        $schetSyncStatus = $model->getSchetSyncStatus($schetId);

        if ($schetSyncStatus == false)
        {
                $problemMessage    ="Вероятно не верно задан идентификатор счета в системе";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;                
        }

        
        $model->syncDate = $schetSyncStatus['schetDate'];        
        /*Пробуем синхронизировать */                
        $model->loadSchetBase(1, 0);
        /*Не было установленного соответствия - пробуем еще раз*/        
        if (empty($schetSyncStatus['schetRef1C']))    $schetSyncStatus = $model->getSchetSyncStatus($schetId);
        
        if (empty($schetSyncStatus['schetRef1C']))
        {             
            /*Так и нет установленного соответствия */        
            $this->redirect(['data/get-sync-schet', 'schetId' => $schetId, 'schetTime' => strtotime($schetSyncStatus['schetDate']), 'refOrg' =>$refOrg ]);            
            return;
        }         
        
        $model->loadOplataBase(1, 0);
        $model->linkOplataToSchet();
        $model->loadSupplyBase(1, 0);
        $model->linkSupplyToSchet();
        $model->getSchetSyncStatus($schetId);
        $this->redirect(['site/success']);
        return;                

    }
    /***********************************************************/    
    /*Формируем список cчетов*/
    public function actionGetSyncSchet()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;        
        $session->open();
        
        $model = new DataSyncGoogle();    
        $schetId   = $request->get('schetId',0);    
        
        if ($schetId == 0)
        {
                $problemMessage    ="Вероятно не задан идентификатор счета в системе";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;                
        }
        
        $refOrg    = $request->get('refOrg',0);        
        $schetTime = $request->get('schetTime',time());        
        
        $fromTime = $schetTime - 60*60*24*3;
        $toTime   = $schetTime + 60*60*24*3;

            $schetList = $model->getSchetList($fromTime, $toTime, $refOrg);
            $session = Yii::$app->session;        
            $session->open();
            $session->set('syncShetList', $schetList);
            $session->set('syncShetId', $schetId);
            return $this->render('sync-select-schet', ['model' => $model, 'schetList' => $schetList, 'schetId' => $schetId ]);    
    }    
    
    /***********************************************************/    
    /*Формируем список cчетов для регистрации*/
    public function actionCreateSyncSchetSelect()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;        
        $session->open();
        
        $model = new DataSyncGoogle();    
        
        $refOrg   = $request->get('refOrg',0);        
        $zakazId  = $request->get('zakazId',0);        
        $eventId = $request->get('eventId',0);        
        $schetTime = $request->get('schetTime',time());        
        
        $fromTime = $schetTime - (60*60*24)*30;
        $toTime   = $schetTime + (60*60*24)*3;

           $schetList = $model->getSchetList($fromTime, $toTime, $refOrg);
                        
            $session = Yii::$app->session;        
            $session->open();
            $session->set('syncShetList', $schetList);            
            $session->set('syncZakazId', $zakazId);            
            $session->set('syncEventId', $eventId);            
            $session->set('syncRefOrg', $refOrg);            
            return $this->render('create-sync-schet-select', ['model' => $model, 'schetList' => $schetList, 'zakazId' =>$zakazId, 'refOrg'=>$refOrg, 'eventId'=> $eventId ]);    
    }    
    /***********************************************************/    
    
    public function actionCreateSyncSingleSchet()
    {
         $session = Yii::$app->session;        
        $session->open();
        $request = Yii::$app->request;
        $schetKey   = $request->get('schetKey',0);                    
        
        $schetList = $session->get('syncShetList', array());            
        $zakazId = $session->get('syncZakazId',0);            
        $eventId = $session->get('syncEventId',0);            
        $refOrg = $session->get('syncRefOrg',0);            
        
        $model = new DataSyncGoogle();        
        $schetId =$model->createSingleSchet($schetKey, $schetList, $zakazId, $eventId, $refOrg );
        if ($schetId == 0) 
         {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Ошибка при синхронизации счета. Попробуйте завести счет вручную.";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;
         }
        $this->redirect(['market/market-schet', 'id' => $schetId  ]);            
        return;        
    }    
    
    /***********************************************************/    
    
    public function actionSyncSingleSchet()
    {
         $session = Yii::$app->session;        
        $session->open();
        
        $session->set('actionName', 'data/sync-single-schet');
        $session->set('syncSubtitle', 'Синхронизация счета...');                        
        
        $request = Yii::$app->request;
        $schetKey   = $request->get('schetKey',0);                    
        $mode       = $request->get('mode',0);                    
        if ($mode == 0)
        {    
     /*Начнем*/
            $session->set('schetKey', $schetKey);
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }

        
        $schetKey   = $session->get('schetKey',0);                    
        $schetList = $session->get('syncShetList');
        $schetId = $session->get('syncShetId', 0);
        
        
        
        if ($schetKey == 0 || $schetId == 0)
        {
            
                $problemMessage    ="Вероятно не заданы параметры счета для синхронизации " ."<br>".$schetId."<br>".$schetKey;
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;                
        }

        $model = new DataSyncGoogle();        
        
        $model->updateSingleSchet($schetId, $schetKey, $schetList );
        
        $session->set('syncShetList', array());
        $session->set('syncShetId', 0);
        
        $schetSyncStatus = $model->getSchetSyncStatus($schetId);
        if ($schetSyncStatus == false )
        {
                $problemMessage    ="Вероятно не верно задан идентификатор счета в системе";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
                return;                
        }
        if (empty($schetSyncStatus['schetDate']))
        {
            $schetSyncStatus['schetDate'] = date('Y-m-d', time()-90*24*60*60);
        }

        $model->syncDate = $schetSyncStatus['schetDate'];        
        $model->loadOplataBase(1, 0);
        $model->linkOplataToSchet();
        $model->loadSupplyBase(1, 0);
        $model->linkSupplyToSchet();
        $model->getSchetSyncStatus($schetId);
        $this->redirect(['site/success']);
        return;                

    }    
    
    /***********************************************************/    
    /*Вызов обновления cчетов*/
    public function actionSyncSchet()
    {         
        $model = new DataSyncGoogle();        
        
        $model->createNewOrg = 1; // добавлять организации
        
        $curUser=Yii::$app->user->identity;    
        if ($curUser->roleFlg & 0x0080|0x0020) $model->syncAllUser = 1;  // Синхронизировать для всех пользователей
        else                                      $model->syncAllUser = 0;  // Синхронизировать для всех пользователей
        
        $model->createNewSchet = 0;  // Создавать счет
        $model->updateExistedSchet = 1;  // апдейтить существующий
        $model->forceUpdateSchet   = 0;  // апдейтить даже если уже синхронизирован
        
         $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-schet');
        $session->set('syncSubtitle', 'Список счетов...');                        
        
        if ($mode == 0)
        {    
     /*Начнем*/
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }

        if ($startRow ==1)
            {
                $retSync=$model->loadSchetBase($startRow, 0);
                $session->set('allRecords', $retSync['allRecords']);        
                $allRecords = intval($retSync['allRecords'] );
                $session->set('syncSchetResult', $retSync);        
            }
        else
            {
                $syncSchetResult = $session->get('syncSchetResult');                
                $allRecords=intval($session->get('allRecords'));                    
                $retSync=$model->loadSchetBase($startRow, $allRecords);                
                $syncSchetResult['updatedSchet'] += $retSync['updatedSchet'];
                $session->set('syncSchetResult', $retSync);        
            }        
        
            $startRow= intval($retSync['lastLoaded'])+2;                
            $lastLoaded = intval($retSync['lastLoaded'] );

        if ($lastLoaded >= $allRecords )
            {            
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список счетов...');                
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
                    

        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);

    }



    /***********************************************************/

    public function actionLoadSchetActivity()
    {         
        $this->redirect(['data/synced-schet']);
    }

    /***********************************************************/

    
    public function actionImportSchet()
    {         
        $this->redirect(['data/synced-schet']);
    }

    public function actionSyncedSchet()
    {         
        $model = new DataSyncGoogle();    
        $provider = $model->getSchetImportedProvider (Yii::$app->request->get());        
        return $this->render('synced-schet', ['model' => $model, 'provider' => $provider ] );
    }

    public function actionSyncSchetProgress()
    {         
        $model = new DataSyncGoogle();        
        $request = Yii::$app->request;
                    $startRow   = $request->get('startRow');
                    $allRecords = $request->get('allRecords');
                    $mode       = $request->get('mode'); 
                    if (empty($mode)){$mode = 1;}
        return $this->render('sync-schet-progress', ['startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => $mode] );        
    }

    /***********************************************************/
    /*** Оплата **/
    /***********************************************************/
    /***********************************************************/
    
        public function actionSyncOplata()
    {         
      if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $model = new DataSyncGoogle();        
        
        $curUser=Yii::$app->user->identity;    
        if ($curUser->roleFlg & 0x0080|0x0020) $model->syncAllUser = 1;  // Синхронизировать для всех пользователей
        else                                   $model->syncAllUser = 0;  // Синхронизировать для всех пользователей
    
         $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        
        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-oplata');
        $session->set('syncSubtitle', 'Список оплат...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
    
        if ($startRow ==1)
            {
                $session->set('oplataRefArray', "");
                $session->set('lastOplataTime', "");
                $retSync=$model->loadOplataBase($startRow, 0);
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об оплате счетов";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }

                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncOplataResult', $retSync);        
                        
            }
        else
            {
                
                $syncOplataResult = $session->get('syncOplataResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->loadOplataBase($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об оплате счетов";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                
                $retSync['updatedOplata'] += $syncOplataResult['updatedOplata'];
                $session->set('syncOplataResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    
            
        if ($lastLoaded >= $allRecords )
            {            
                $model->linkOplataToSchet();
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $session->set('oplataRefArray', "");
                $session->set('lastOplataTime', "");
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список оплат...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
    }

    /*****************/
    
    public function actionImportOplata()
    {         
        $this->redirect(['sync-oplata']);        
    }

    
    /***********************************************************/
    public function actionLoadOplataActivity()
    {         
        $this->redirect(['sync-oplata']);        
    }
    
    /***********************************************************/

    public function actionSyncedOplata()
    {         
        $model = new DataSyncGoogle();    
        $provider = $model->getOplataImportedProvider (Yii::$app->request->get());        
        return $this->render('synced-oplata', ['model' => $model, 'provider' => $provider ] );
    }
    
    /***********************************************************/
    

    /***********************************************************/
    /*** Отгрузка - поставка **/
    /***********************************************************/
    /***********************************************************/
    
    
    public function actionSyncSupply()
    {         
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $model = new DataSyncGoogle();        
        $curUser=Yii::$app->user->identity;    
        if ($curUser->roleFlg  & 0x0080|0x0020) $model->syncAllUser = 1;  // Синхронизировать для всех пользователей
        else                                    $model->syncAllUser = 0;  // Синхронизировать для всех пользователей
            
         $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        
        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-supply');
        $session->set('syncSubtitle', 'Список отгрузок...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
    
        if ($startRow ==1)
            {
                $session->set('supplyRefArray', "");
                $session->set('lastSupplyTime', "");
                $retSync=$model->loadSupplyBase($startRow, 0);
/*print_r ("<pre>");
print_r ($retSync);
print_r ("</pre>");                
return;
*/
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об отгрузке";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }

                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncSupplyResult', $retSync);        
                        
            }
        else
            {                
                $syncSupplyResult = $session->get('syncSupplyResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->loadSupplyBase($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об отгрузке";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                
                $retSync['updatedSupply'] += $syncSupplyResult['updatedSupply'];
                $session->set('syncSupplyResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    
            
        if ($lastLoaded >= $allRecords )
            {            
                $model->linkSupplyToSchet();
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $session->set('supplyRefArray', "");
                $session->set('lastSupplyTime', "");
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список отгрузок...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
    }

 /***********************************************************/
    public function actionFixSupply()
    {         
        $model = new DataSyncGoogle();        
            
         $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/fix-supply');
        $session->set('syncSubtitle', 'Фикс Списка отгрузок...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
    
        if ($startRow ==1)
            {
                $session->set('supplyRefArray', "");
                $session->set('lastSupplyTime', "");
                $retSync=$model->fixSupplyBase($startRow, 0);
                
            if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об отгрузке";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }

                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncSupplyResult', $retSync);        
                        
            }
        else
            {                
                $syncSupplyResult = $session->get('syncSupplyResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->fixSupplyBase($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных об отгрузке";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                
                $retSync['updatedSupply'] += $syncSupplyResult['updatedSupply'];
                $session->set('syncSupplyResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    
            
        if ($lastLoaded >= $allRecords )
            {            
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $session->set('supplyRefArray', "");
                $session->set('lastSupplyTime', "");
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список отгрузок...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
    }
    
    
    /***************************************************/
    public function actionImportPostavka()
    {                 
        $this->redirect(['sync-supply']);        
    }

    /***********************************************************/

    public function actionSyncedSupply()
    {         
        $model = new DataSyncGoogle();    
        $provider = $model->getSupplyImportedProvider (Yii::$app->request->get());        
        return $this->render('synced-supply', ['model' => $model, 'provider' => $provider ] );
    }
    /***********************************************************/
    /***********************************************************/
    public function actionLoadSupplyActivity()
    {         
        $this->redirect(['sync-supply']);        
    }
    
    /***********************************************************/
    /*************** Документы поставщика **********************/
    /***********************************************************/
    
    
        
      /***********************************************************/

     public function actionSyncSupplierAll()
     {         
        
        $request = Yii::$app->request;                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
                
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                
        
        $session = Yii::$app->session;      
        $session->open();

        $actionType = $session->get('actionType', 5);
        if (empty($actionType)) {$actionType=5;}                

        /*Начнем*/
        if ($actionType == 5)
        {    
           $this->actionFlashResult();
         /*Поставщики*/    
             $session->set('actionName', 'data/sync-supplier');
            $session->set('parentForm', 'data/sync-supplier-all');
            $session->set('actionType', '6');
            $session->set('syncSubtitle', 'Список Поставщиков...');                        
                        
            $this->redirect(['data/sync-supplier' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'updExistedClients' => 0]);            
            return;
        }
        
        if ($actionType == 6)
        {    
            /*Счета*/    
             $session->set('actionName', 'data/sync-supplier-schets');
            $session->set('parentForm', 'data/sync-supplier-all');
            $session->set('actionType', '7');
            $session->set('syncSubtitle', 'Список счетов от поставщика...');
                        
            $this->redirect(['data/sync-supplier-schets' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }
        if ($actionType == 7) 
        {    
            /*Оплата*/    
             $session->set('actionName', 'data/sync-supplier-oplata');
            $session->set('parentForm', 'data/sync-supplier-all');
            $session->set('actionType', '8');
            $session->set('syncSubtitle', 'Список оплат поставщику...');
                        
            $this->redirect(['data/sync-supplier-oplata' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }

        if ($actionType == 8)
        {    
            /*Поступление товара*/    
             $session->set('actionName', 'data/sync-supplier-wares');
            $session->set('parentForm', 'data/sync-supplier-all');
            $session->set('actionType', '9');
            $session->set('syncSubtitle', 'Поступление товара...');
                        
            $this->redirect(['data/sync-supplier-wares' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);            
            return;
    
        }
        
        
        $session->set('actionType', '0');
        $this->redirect(['data/sync-result']);        
    }
    /***********************************************************/

    /*Вызов обновления поставщиков*/
     public function actionSyncSupplier()
     {         
       if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }

        $request = Yii::$app->request;
        $model = new DataSyncGoogle();        
                    
         $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);
        $updExistedClients = $request->get('updExistedClients',0);

        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session->set('actionName', 'data/sync-supplier');
        $session->set('syncSubtitle', 'Список поставщиков...');                        
            
         
        if ($mode == 0)
        {       
        $session->set('updExistedClients', $updExistedClients);                        
        $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
        return;        
        }
        
        $updExistedClients = $session->get('updExistedClients', 0);                        
        
        
        $retSync=$model->loadSupplierBase($updExistedClients, $startRow);    
        $session->set('syncSupplierResult', $retSync);        
        
        /*если не задано куда вернутся, то завершим */        
        $nextForm = $session->get('parentForm', 'data/sync-result');        

        $this->redirect([$nextForm, 'noframe' => 1]);
        return;        
    }

    /***********************************************************/    
    
    /* Счета поставщика */    
    public function actionSyncSupplierSchets()
    {        
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); } 
      $model = new DataSyncGoogle();        
               
         $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                
        $parentForm = $request->get('parentForm', '');        
        if (!empty($parentForm)) $session->set('parentForm', $parentForm);
        $session->set('actionName', 'data/sync-supplier-schets');
        $session->set('syncSubtitle', 'Список счетов от поставщиков...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
    
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
    
        if ($startRow ==1)
            {
                $session->set('supplierSchetRefArray', "");
                $session->set('lastSupplierSchetTime', "");
                $retSync=$model->loadSupplierSchets($startRow, 0);

                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по счетам поставщиков";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }

                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncSupplierSchetResult', $retSync);        
                        
            }
        else
            {                
                $syncSupplierSchetResult = $session->get('syncSupplierSchetResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->loadSupplierSchets($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по счетам поставщиков";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                if (empty($syncSupplierSchetResult['updatedRecord'])) $syncSupplierSchetResult['updatedRecord'] =0;
                if (empty($retSync['updatedRecord'])) $retSync['updatedRecord'] =0;
                $retSync['updatedRecord'] += $syncSupplierSchetResult['updatedRecord'];
                $session->set('syncSupplierSchetResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    


//return;            
        if ($lastLoaded >= $allRecords )
            {            
                $nextForm = $session->get('parentForm', 'data/sync-result');                                
                $session->set('supplierSchetRefArray', "");
                $session->set('lastSupplierSchetTime', "");

                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список счетов поставщиков...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
        $nextForm = $session->get('parentForm', 'data/sync-result');
        


        $this->redirect([$nextForm, 'noframe' => 1]);
    }


    /***********************************************************/

    /* Оплаты поставщику */    
    public function actionSyncSupplierOplata()
    {         
    
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $model = new DataSyncGoogle();        
        $session = Yii::$app->session;        
        $session->open();
        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}     
        $parentForm = $request->get('parentForm', '');        
        if (!empty($parentForm)) $session->set('parentForm', $parentForm);
        $session->set('actionName', 'data/sync-supplier-oplata');
        $session->set('syncSubtitle', 'Список оплат  поставщикам...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
        }
    
        if ($startRow ==1)
            {
                $session->set('supplierOplataRefArray', "");
                $session->set('lastSupplierOplataTime', "");
                $retSync=$model->loadSupplierOplata($startRow, 0);
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по оплатам поставщику";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }
                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncSupplierOplataResult', $retSync);        
            }
        else
            {                
                $syncSupplierOplataResult = $session->get('syncSupplierOplataResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->loadSupplierOplata($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по оплатам поставщику";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                if (empty($syncSupplierOplataResult['updatedRecord'])) $syncSupplierOplataResult['updatedRecord'] =0;
                if (empty($retSync['updatedRecord'])) $retSync['updatedRecord'] =0;
                $retSync['updatedRecord'] += $syncSupplierOplataResult['updatedRecord'];
                $session->set('syncSupplierOplataResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    
            
        if ($lastLoaded >= $allRecords )
            {            
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $session->set('supplierOplataRefArray', "");
                $session->set('lastSupplierOplataTime', "");
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Список оплат поставщикам...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }            

        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm, 'noframe' => 1]);
    }

    /***********************************************************/
    /* Приход товаров от поставщика */    
    public function actionSyncSupplierWares()
    {         
    
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $model = new DataSyncGoogle();        

               
        $session = Yii::$app->session;        
        $session->open();

        $request = Yii::$app->request;
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session->set('actionName', 'data/sync-supplier-wares');
        $session->set('syncSubtitle', 'Приход товаров от поставщика...');                        
        
        if ($mode == 0)
        {    
         /*Начнем*/
       
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
    
        if ($startRow ==1)
            {
                $session->set('supplierWaresRefArray', "");
                $session->set('lastSupplierWaresTime', "");
                $retSync=$model->loadSupplierWares($startRow, 0);

                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по приходу товаров от поставщика";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                }

                $session->set('allRecords', $retSync['allRecords']);    
                $allRecords=$retSync['allRecords'];                        
                $session->set('syncSupplierWaresResult', $retSync);        
            }
        else
            {                
                $syncSupplierWaresResult = $session->get('syncSupplierWaresResult');                
                $allRecords=intval($session->get('allRecords'));        
                $retSync=$model->loadSupplierWares($startRow, $allRecords);            
                
                if ($retSync==false)
                {
                  $problemMessage    ="Ошибка при импорте данных по приходу товаров от поставщика";
                  $session->set('problemMessage', $problemMessage);         
                  $this->redirect(['site/problem']);
                  return;
                }
                if (empty($syncSupplierWaresResult['updatedRecord'])) $syncSupplierWaresResult['updatedRecord'] =0;
                if (empty($retSync['updatedRecord'])) $retSync['updatedRecord'] =0;
                $retSync['updatedRecord'] += $syncSupplierWaresResult['updatedRecord'];
                $session->set('syncSupplierWaresResult', $retSync);        
            }        
            $lastLoaded = intval($retSync['lastLoaded'] );
            $startRow= $lastLoaded +2;    
            
        if ($lastLoaded >= $allRecords )
            {            
                //$model->linkSupplyToSchet();
                $nextForm = $session->get('parentForm', 'data/sync-result');
                $session->set('supplierWaresRefArray', "");
                $session->set('lastSupplierWaresTime', "");
                $this->redirect([$nextForm]);
            return;
            }            
        else
            {            
                $session->set('syncSubtitle', 'Приход товаров от поставщика...');    
                $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => 0]);        
            return;
            }                        
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm, 'noframe' => 1]);
    }


    /***********************************************************/

    /***********************************************************/        

    public function actionImportContacts()
    {         
        $model = new DataSyncGoogle();        
        
        $page=$model->loadContactsBase();
        
        //$provider = $model->getClientImportedProvider (Yii::$app->request->get());
        return $this->render('sync-contacts', ['model' => $model, 'page' => $page ] );        
        //return;
        
        //$this->redirect(['data/sync-google-clients']);
        
    }

    
    /***********************************************************/        

    public function actionSyncPrice()
    {         
        
        $request = Yii::$app->request;  
        $mode = $request->get('mode', 0);                          

        if ($mode == 0)
        {    
            $session = Yii::$app->session;      
            $session->open();
            $session->set('actionName', 'data/sync-price');
            $this->redirect(['data/sync-progress' , 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);
        }
        else 
        {            
            $model = new DataSyncGoogle();                
            $page = $model->syncSclad1C();
            //$page = $model->syncScladByGoogle();
            /*echo "<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
                print_r ("<pre>");
                print_r ($page);
                print_r ("</pre>");
            return;*/
        $this->redirect(['store/warehouse']);
        return;
        }
    }

    /***********************************************************/        

    
    public function actionSyncOrders()
    {         
        return $this->render('sync-orders');        
    }

    
    /***********************************************************/        

    public function actionSyncGooglePrice()
    {         
        
        $request = Yii::$app->request;  
        $mode = $request->get('mode', 0);                          

        $session = Yii::$app->session;      
        $session->open();
        
        $session->set('actionName', 'data/sync-google-price');
        $session->set('syncSubtitle', 'Обновление прайса...');                        

         
        

        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        if ($mode == 0)
        {            
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }


        $request = Yii::$app->request;  
        $model = new DataSyncGoogle();

         $zakazId = $session->get('GooglePriceZakazId',0 );
         $orgId   = $session->get('GooglePriceOrgId',0 );

        $page = $model->syncPriceByGoogle();
        $this->redirect(['store/google-price', 'orgId' => $orgId, 'zakazId' => $zakazId, 'noframe' => '1' ]);
        return;
        
    }
    
    
    /**********************************************************
        Load and parse link to google table
    ***********************************************************/

    public function actionSyncTest()
    {         
        $model = new DataSyncGoogle();                
        $page=$model->loadNClientBase(0,1);    
        return $this->render('sync-test', ['model' => $model, 'page' => $page ] );                        
    }    
    
    
    public function actionImportGoogleClients()
    {         
        $model = new DataSyncGoogle();        
        
        $request = Yii::$app->request;
                    $updExistedClients = $request->get('updExistedClients');
        if (empty($updExistedClients)) {$updExistedClients=0;}                
                    $startRow = $request->get('startRow');
        if (empty($startRow)) {$startRow=1;}                

        $page=$model->loadNClientBase($updExistedClients, $startRow);
        
        //$provider = $model->getClientImportedProvider (Yii::$app->request->get());
        
        //return $this->render('sync-test', ['model' => $model, 'page' => $page ] );                
        //return;        
        $this->redirect(['data/sync-google-clients']);
        
    }    
    
    public function actionSyncGoogleClients()
    {         
        $model = new DataSyncGoogle();                    
        $provider = $model->getClientImportedProvider (Yii::$app->request->get());
        return $this->render('sync-google-clients', ['model' => $model, 'provider' => $provider ] );
        
    }

    /**********************************************************
        Load and parse csv-file
    ***********************************************************/
    
    public function actionCsvUpload()
    {
        $model = new DataUploadCsvForm();
        $parse_count = 0;

        if (Yii::$app->request->isPost) {
                        $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
            if ($model->upload()) 
            {
                $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
        //        $model->indexCsv($uploadPath.$model->csvFile->name);
        //.'.dat'
                $this->redirect(['site/csv-parse', 'fname' => $model->csvFile->name,  'from' => 0]); 
            }
        }
        else 
        {
            return $this->render('csv-upload', ['model' => $model]);
        }
    }
    
    public function actionCsvUploaded()
    {
        $model = new DataUploadCsvForm();
        $request = Yii::$app->request;    
        $fname = $request->get('fname');
        $parsed = -$request->get('parsed');

        return $this->render('csv-uploaded', ['model' => $model, 'fname' => $fname, 'parsed' => $parsed]);
    }
    
    public function actionCsvParse()
    {
        $model = new DataUploadCsvForm();
        $request = Yii::$app->request;
            
        $fname = $request->get('fname');
        $from = $request->get('from');
        
            
        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";

        
        if ($from < 0)
        {
            $strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id )";
            Yii::$app->db->createCommand($strSql)->execute();
//            $this->redirect(['site/csv-uploaded']);         
            $this->redirect(array('site/csv-uploaded','fname' => $fname, 'parsed' => $from));         
        }
        else
        {
        $from= $model->parseCsv($uploadPath.$fname, $from, 75);
        }
        if ($from < 0)
        {
              $this->redirect(['cold/cold-start']);            
            $this->redirect(array('site/csv-uploaded','fname' => $fname, 'parsed' => $from)); 
        }
        else
        {
        return $this->render('csv-parse', ['model' => $model,'fname' => $fname, 'from' => $from]);
        }
    }

/***********************************************************/
/***********************************************************/
//http://127.0.0.1/phone/web/index.php?r=data/sync-buh-stat&syncTime=1569974400&noframe=1
     public function actionSyncBuhStat()
     {         
        
        $request = Yii::$app->request;                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);        
        $syncTime = $request->get('syncTime', 0);
                
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                
        
        $session = Yii::$app->session;      
        $session->open();

        $actionType = $session->get('actionType', 8);
        if (empty($actionType)) {$actionType=1;}                
        
        //если не задана дата, то возьмем из предыдущего запуска
        if (empty($syncTime))  $syncTime = $session->get('syncTime', 0);
        // ну или сегодня
        if (empty($syncTime))  $syncTime = time();

        /*Начнем*/
        if ($actionType == 1)
        {    
           $this->actionFlashResult();
//Счета
            $session->set('actionName', 'data/sync-schet');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '2');
            $session->set('syncTime', $syncTime);
            $session->set('syncSubtitle', 'Список счетов...');
                        
            $this->redirect(['data/sync-schet' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;
    
        }
        if ($actionType == 2)
        {    
//Отгрузки        
            $session->set('actionName', 'data/sync-supply');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '3');
            $session->set('syncSubtitle', 'Список отгрузок...');                        
            
            $this->redirect(['data/sync-supply' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;
    
        }
        
        if ($actionType == 3)
        {    
//Прибыль        
            $session->set('actionName', 'data/sync-profit');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '4');
            $session->set('syncSubtitle', 'Прибыль и рентабельность...');                        
            
            $this->redirect(['data/sync-profit' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;
    
        }
        
        if ($actionType == 4)
        {    
//Приход денег
            $session->set('actionName', 'data/sync-oplata');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '5');
            $session->set('syncSubtitle', 'Поступление денег...');
                        
            $this->redirect(['data/sync-oplata' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;
    
        }

        if ($actionType == 5)
        {    
//Траты
            $session->set('actionName', 'data/sync-supplier-oplata');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '6');
            $session->set('syncSubtitle', 'Оплаты поставщику (расход денег)...');
                        
            $this->redirect(['data/sync-supplier-oplata' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;
    
        }
        
        if ($actionType == 6)
        {    
//На счету
            $session->set('actionName', 'data/sync-bank');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '7');
            $session->set('syncSubtitle', 'Состояние банковского счета...');
                        
            $this->redirect(['data/sync-bank' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;    
        }
        
        if ($actionType == 7)
        {    
//Долги
            $session->set('actionName', 'data/sync-sverka');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '8');
            $session->set('syncSubtitle', 'Сверка долгов...');
                        
            $this->redirect(['data/sync-sverka' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;    
        }
        
        if ($actionType == 8)
        {    
//Склады
            $session->set('actionName', 'data/sync-sclad');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '9');
            $session->set('syncSubtitle', 'Склады...');
                        
            $this->redirect(['data/sync-sclad' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;    
        }
        
        if ($actionType == 10)
        {    
//Приход товара
            $session->set('actionName', 'data/sync-purch');
            $session->set('parentForm', 'data/sync-buh-stat');
            $session->set('actionType', '10');
            $session->set('syncSubtitle', 'Приход товара...');
                        
            $this->redirect(['data/sync-purch' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0, 'syncTime' => $syncTime]);            
            return;    
        }
        
        
        
        $session->set('actionType', '0');
        $session->set('syncTime', '0');
        $session->set('parentForm', '');
        $this->redirect(['site/success']);        
    }
    /***********************************************************/
   /***********************************************************/        
/*Вызов синхронизации прибыль и рентабельность*/
     public function actionSyncProfit()
     {        
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); } 
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);

        $parentForm = $request->get('parentForm', '');        
        if ($parentForm == 'self') $parentForm = 'fin/profit-src';

        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-profit');
        $session->set('syncSubtitle', 'Прибыль и рентабельность...');
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                                        
                 
        if ($mode == 0)
        {      
            $defTime = time();
            $syncTime = $request->get('syncTime', $defTime);
            $param['syncTime']= $syncTime;    
            $session->set('SyncProfitControlParam', $param);
              
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
            
        $param    = $session->get('SyncProfitControlParam');
        $syncTime = $param['syncTime'];    
     
        $retSync=$model->loadProfitData($startRow, $syncTime, 0);    
        $session->set('syncProfitResult', $retSync);

        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'fin/profit-src');
        $this->redirect([$nextForm, 'strDate' => date("Y-m-d", $syncTime)]);
        return;
        
    }

/***********************************************************/
    /*Вызов синхронизации контроля банка*/
     public function actionSyncBank()
     {         
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);

        $mode = $request->get('mode', 0);
        $parentForm = $request->get('parentForm', '');        
        if ($parentForm == 'self') $parentForm = 'fin/bank-src';
        
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-bank');
        $session->set('syncSubtitle', 'Банк...');
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                
                 
        if ($mode == 0)
        {      
            $defTime = time();
            $syncTime = $request->get('syncTime', $defTime);
            $param['syncTime']= $syncTime;    
            $session->set('SyncBankControlParam', $param);
              
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
            
        $param    = $session->get('SyncBankControlParam');
        $syncTime = $param['syncTime'];   
        if(!empty($syncTime))   $strDate= date('Y-m-d',$syncTime);
        else                    $strDate= date('Y-m-d');
     
        $retSync=$model->loadBankData($startRow, $syncTime);         
        $session->set('syncBankResult', $retSync);
            
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'fin/bank-src');
        $this->redirect([$nextForm, 'strDate' => $strDate, 'noframe' => 1]);
        return;
        
    }

/***********************************************************/
    /*Вызов синхронизации сверки*/
     public function actionSyncSverka()
     {         
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow   = $request->get('startRow',1);
        $mode       = $request->get('mode', 0);
        $parentForm = $request->get('parentForm', '');        
        
        if ($parentForm == 'self') $parentForm = 'fin/sverka-use';
        if (empty($startRow))     {$startRow=1;}                
        if (empty($mode))         {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-sverka');
        $session->set('syncSubtitle', 'Контроль сверки долга...');                        
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                
                         
        if ($mode == 0)
        {       
            $defTime = time();   
            $syncTime = $request->get('syncTime', $defTime);
            $param['syncTime']= $syncTime;    
            $session->set('SyncSverkaControlParam', $param);
            
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
           
        $param    = $session->get('SyncSverkaControlParam');
        $syncTime = $param['syncTime'];           
        $retSync=$model->loadSverkaData($startRow, $syncTime);    
        $session->set('syncSverkaResult', $retSync);
                   
        $strDate = date('Y-m-d', $syncTime);
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'fin/sverka-use');
        $this->redirect([$nextForm, 'strDate' => $strDate, 'noframe' => 1]);
        return;
        
    }
    
    
    /***********************************************************/      
//http://127.0.0.1/phone/web/index.php?r=data/sync-sclad&syncTime=1569974400&noframe=1      
    public function actionSyncSclad()
    {      
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }   
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);
        $mode = $request->get('mode', 0);
        $parentForm = $request->get('parentForm', '');        
        
        if ($parentForm == 'self') $parentForm = 'fin/bank-src';
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-sclad');
        $session->set('syncSubtitle', 'Состояние склада...');                        
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                                 
        
        if ($mode == 0)
        {      
            $defTime = time();
            $syncTime = $request->get('syncTime', $defTime);
            $param['syncTime']= $syncTime;    
            $session->set('SyncScladParam', $param);
              
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
            
        $param    = $session->get('SyncScladParam');
        $syncTime = $param['syncTime'];    
        $retSync=$model->syncSclad($startRow, $syncTime);    
        $session->set('syncScladResult', $retSync);
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'store/ware-use');
        $this->redirect([$nextForm, 'strDate' => date("Y-m-d", $syncTime), 'noframe' => 1]);
        return;        
    } 

    
    public function actionSyncScladAjax()
    {      
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }   
        $request = Yii::$app->request;
        $model = new DataSync();        

        $defTime = time();
        $syncTime = $request->get('syncTime', $defTime);
        
        //if(Yii::$app->request->isAjax)
        {
                $model->webSync = true;
                $sendArray = $model->syncSclad(0, $syncTime); 
                echo json_encode($sendArray);                
                exit(0);
                return;
        }
                
        return;        
    } 

    
/***********************************************************/
    /*Вызов синхронизации контроля закупки товара*/
     public function actionSyncPurch()
     {         
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);

        $mode = $request->get('mode', 0);
        $parentForm = $request->get('parentForm', '');        
        if ($parentForm == 'self') $parentForm = 'fin/purch-src';
        
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-purch');
        $session->set('syncSubtitle', 'Закупка товара...');
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                
                 
        if ($mode == 0)
        {      
            $defTime = time();
            $syncTime = $request->get('syncTime', $defTime);
            $param['syncTime']= $syncTime;    
            $session->set('SyncPurchControlParam', $param);
              
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
            
        $param    = $session->get('SyncPurchControlParam');
        $syncTime = $param['syncTime'];   
        if(!empty($syncTime))   $strDate= date('Y-m-d',$syncTime);
        else                    $strDate= date('Y-m-d');
     
        $retSync=$model->loadPurchData($startRow, $syncTime);         
        $session->set('syncPurchResult', $retSync);
            
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'fin/purch-src');
        $this->redirect([$nextForm, 'strDate' => $strDate, 'noframe' => 1]);
        return;
        
    }
   
   /***********************************************************/        
   /***********************************************************/         
    
   /***********************************************************/        
/*Вызов синхронизации прибыль и рентабельность*/
     public function actionSyncBuhSchet()
     {        
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); } 
        $request = Yii::$app->request;
        $model = new DataSync();        
                    
        $session = Yii::$app->session;        
        $session->open();
                        
        $parentForm = $request->get('parentForm', '');        
        if ($parentForm == 'self') $parentForm = 'fin/buh-schet';

        $mode = $request->get('mode', 0);
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-buh-schet');
        $session->set('syncSubtitle', 'Данные бухгалтерских счетов ...');
        if(!empty($parentForm))$session->set('parentForm', $parentForm);                                                                        
                 
        if ($mode == 0)
        {      
            $defTime = time();

            $stDate  = $request->get('stDate', date('Y-m-d', $defTime));        
            $enDate  = $request->get('enDate', date('Y-m-d', $defTime+7*24*3600));        

            $param['st']= strtotime($stDate);
            $param['en']= strtotime($enDate);        
            $session->set('SyncBuhSchetParam', $param);     
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
            
        $param    = $session->get('SyncBuhSchetParam');        
        $st = $param['st'];            
        $en = $param['en'];    

        $retSync=$model->loadBuhSchetData($st, $en);    
        $session->set('syncBuhSchetResult', $retSync);
//return;
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'fin/buh-schet');
        $this->redirect([$nextForm, 'stDate' => date("Y-m-d", $st), 'enDate' => date("Y-m-d", $en), ]);
        return;
        
    }

    
   /***********************************************************/        
   /***********************************************************/         
    
/***********************************************************/
    /*Вызов синхронизации контроля складов*/
     public function actionSyncScladControl()
     {         
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }
        $request = Yii::$app->request;
        $model = new DataSyncGoogle();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);

        $mode = $request->get('mode', 0);
               
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-sclad-control');
        $session->set('syncSubtitle', 'Контроль состояния склада');                        
                 
        if ($mode == 0)
        {                    
            $isPrev = $request->get('isPrev', 0);
            $defTime = time();
            if ($isPrev == 1) $defTime = time()-60*60*24;       
            $syncTime = $request->get('syncTime', $defTime);
            $param['isPrev']= $isPrev;
            $param['syncTime']= $syncTime;    
            $session->set('SyncScladControlParam', $param);
            
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
        
       $param    = $session->get('SyncScladControlParam');
       $isPrev   = $param['isPrev'];
       $syncTime = $param['syncTime'];    
       
       $retSync=$model->syncScladControl($syncTime, $isPrev);    
       $session->set('syncScladControlResult', $retSync);
       
       if ($isPrev == 0)
       {
           $this->redirect(['data/sync-sclad-control' ,'isPrev'=>1]);  
           return;  
       }
                    
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
        return;
        
    }
    
/***********************************************************/           
/***********************************************************/
    /*Вызов синхронизации контроля договоров*/
     public function actionSyncGoogleContract()
     {    
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']); }     
        $request = Yii::$app->request;
        $model = new DataSyncGoogle();        
                    
        $session = Yii::$app->session;        
        $session->open();
                    
        $startRow = $request->get('startRow',1);

        $mode = $request->get('mode', 0);
               
        if (empty($startRow)) {$startRow=1;}                
        if (empty($mode)) {$mode=0;}                

        $session = Yii::$app->session;      
        $session->open();
        $session->set('actionName', 'data/sync-google-contract');
        $session->set('syncSubtitle', 'Загрузка списка договоров');                        
                 
        if ($mode == 0)
        {                    
            $this->redirect(['data/sync-progress' ,'noframe'=>1, 'startRow' => 0, 'allRecords' => 0, 'mode' => 0]);    
            return;        
        }
        

//echo "<pre>";       
       $retSync=$model->syncGoogleContract();   
//echo "</pre>";       
//return; 
       $session->set('syncGoogleContractResult', $retSync);
       
     
        /*если не задано куда вернутся, то завершим */
        $nextForm = $session->get('parentForm', 'data/sync-result');
        $this->redirect([$nextForm]);
        return;
        
    }
    
/***********************************************************/    
   public function actionPurchClassify()
   {
    $model = new DataSync();        
    $model->purchClassify(0); 
    echo "Classify Finished";
    return;        
   }
    
/***********************************************************/    


    /***********************************************************/        
    /*********** Служебные - прогресс  *************************/        
    /***********************************************************/        
    public function actionProgress()
    {         
        $request = Yii::$app->request;  
        $nextForm = $request->get('nextForm', 'site/index');  
        return $this->render('progress', ['nextForm' => $nextForm,]);
    }    
    
    public function actionSyncProgress()
    {         
        $request = Yii::$app->request;  
        $mode = $request->get('mode', 0);                          

        $startRow = $request->get('startRow', 0);                          
        $allRecords = $request->get('allRecords', 0);                      

        $session = Yii::$app->session;      
        $session->open();        
        $syncSubtitle  = $session->get('syncSubtitle','');                          

        
        /*показываем окно прогресса*/
        if ($mode == 0)
        {
            return $this->render('sync-progress', ['startRow' => $startRow, 'allRecords' => $allRecords, 'syncSubtitle'=>$syncSubtitle ] );        
        }        
        else
        {
            $actionName=$session->get('actionName');
            /*возвращаем для считывания*/
            $this->redirect([$actionName, 'startRow' => $startRow, 'allRecords' => $allRecords, 'mode' => $mode]);
        }
    
    }

    /*********************************************************/    
    
     public function actionSyncStatus()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new DataSync();

        $provider    = $model->getSyncDataProvider();
        return $this->render('sync-status', ['model' => $model, 'provider' => $provider]);
    }
    
    
    
     public function actionForceSync()
    {   
        $request = Yii::$app->request;        
        $id = $request->get('id', 0);                          
        $box = $request->get('box', 0);                          
    
        
        $st= time()-5*24*3600;
        $et= time()+1*24*3600;
    
        //if(Yii::$app->request->isAjax)
        {
            
            switch ($id)
            {
              case 0:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = true;
                    $model->loadSchetBase(1, 0);
              break;  
                
              case 1:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = true;
                    $model->loadSupplierSchets(1, 0);
              break;  
                
              case 2:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = true;
                $model->loadSupplierOplata(1, 0);
              break;  
              
              case 3:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = true;
                $model->loadOplataBase(1, 0);
              break;  

              case 4:
                $model = new DataSync();   
                $model->webSync = false;
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                { 
                    $model->syncDate = date("Y-m-d", $ct);            
                    $model->loadBankData(1, $ct);
                }
              break;  


              case 5:
                $model = new BankOperation();   
                $model->webSync = false;
                
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                {           
                    $start = $ct;//-24*3600;
                    $end   = $ct+24*3600;
                    $model-> syncOperations($start, $end, time());    
                }
              break;  

            case 6:
                $model = new DataConsoleSync();   
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                {         
                $model->loadProfitData(1, $ct);    
                }
              break;  

            case 7:
                $model = new DataConsoleSync();   
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                {         
                $model->loadPurchData(1, $ct);    
                }
              break;  


            case 8:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = false;
                $model->loadSupplyBase(1, 0);
        
            break;  

            case 9:
                $model = new DataSync();   
                $model->webSync = true;
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                { 
                $model->syncDate = date("Y-m-d", $ct);
                $model->syncSclad(1, $ct);
                }
            break;  

            case 10:
                $model = new DataSync();   
                $model->webSync = true;
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                { 
                $model->syncDate = date("Y-m-d", $ct);
                $model->loadSverkaData(1, $ct);
                }
            break;  

            case 11:
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = false;
                $model->loadNClientBase(1, 0);
            break;  
            
                            
            }
            
            
            
                $sendArray['id'] = $id;
                $sendArray['box'] = $box;
                $sendArray['newbox'] = 1;
                echo json_encode($sendArray);
                return;
        }
    }    

    public function actionSyncSingleOrg()
    {   
        $request = Yii::$app->request;        
        $id = $request->get('id', 0);                          
        //if(Yii::$app->request->isAjax)
        {
                $model = new DataSyncGoogle();   
                $model->syncAllUser = 1;
                $model->webSync = true;
                $sendArray = $model->syncSingleOrg($id);            
                echo json_encode($sendArray);                
                return;
        }
    }    


    
    
    public function actionAjaxSync()
    {   
        $request = Yii::$app->request;        
        $actionid = $request->get('actionid', 0);                          
        
        $fromDate= $request->get('fromDate', date('Y-m-d'));                              
        $toDate= $request->get('toDate', date('Y-m-d'));                              
        
        $st= strtotime($fromDate);
        $et= strtotime($toDate);

         $sendArray['actionid'] = $actionid;
         $sendArray['fromDate'] = $fromDate;
         $sendArray['toDate']   = $toDate;

            
        //if(Yii::$app->request->isAjax)
        {
            
            switch ($actionid)
            {
              case 0:
              $model = new DataSyncGoogle();   
              $model->syncAllUser = 1;
              $model->webSync = true;
              $i=0;
               for ($ct=$st ; $ct<=$et; $ct+=24*3600 )
                { 
                    $model->syncDate = date("Y-m-d", $ct);            
                    $ret=$model->loadSchetBase(1, 0);
                    $sendArray[$i]['syncDate']=$model->syncDate;
                    $sendArray[$i]['ret']=$ret;
                    $i++;
                }
              break;  
                
              case 1:
              $model = new DataSyncGoogle();   
              $model->syncAllUser = 1;
              $model->webSync = true;
              $i=0;
               for ($ct=$st ; $ct<=$et; $ct+=24*3600 )
                { 
                    $model->syncDate = date("Y-m-d", $ct);            
                    $ret=$model->loadSupplierSchets(1, 0);
                    $sendArray[$i]['syncDate']=$model->syncDate;
                    $sendArray[$i]['ret']=$ret;
                    $i++;
                }
              break;  
              
              case 2:
              $model = new DataSyncGoogle();   
              $model->syncAllUser = 1;
              $model->webSync = true;
              $i=0;
               for ($ct=$st ; $ct<=$et; $ct+=24*3600 )
                { 
                    $model->syncDate = date("Y-m-d", $ct);            
                    $ret=$model->loadSupplyBase(1, 0);
                    $sendArray[$i]['syncDate']=$model->syncDate;
                    $sendArray[$i]['ret']=$ret;
                    $i++;
                }
              break;      

              case 3:
              $model = new DataConsoleSync();   
              $i=0;
               for ($ct=$st ; $ct<=$et; $ct+=24*3600 )
                {                     
                    $ret=$model->loadPurchData(1, $ct);    
                    $sendArray[$i]['syncDate']=$model->syncDate;
                    $sendArray[$i]['ret']=$ret;
                    $i++;
                }
              break;      
              
              
              
              case 5:
                $model = new BankOperation();   
                $model->webSync = false;
                
                for ($ct=$st ; $ct<$et; $ct+=24*3600 )
                {           
                    $start = $ct;//-24*3600;
                    $end   = $ct+24*3600;
                    $model-> syncOperations($start, $end, time());    
                }
              break;  

              
            } 
            
/*            echo "<pre>";
            print_r($sendArray);
            echo "</pre>";*/
            echo json_encode($sendArray);
            exit(0);
            return;
        }
    }    

    
    
    public function actionSyncOtves()
    {
        $model = new DataSync();
        $sendArray = $model ->loadGoogleOtvesData();
            echo json_encode($sendArray);
            exit(0);
            return;
    }
        

    public function actionSyncTransportTarif()
    {
        $model = new DataSync();
        $sendArray = $model ->loadTransportTarifData();
            echo json_encode($sendArray);
            exit(0);
            return;
    }
    
    /*********************************************************/
}








/*echo "<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
echo "<pre>";        
echo "here";

print_r ($syncSubtitle);
            
echo "</pre>";
return;
*/


