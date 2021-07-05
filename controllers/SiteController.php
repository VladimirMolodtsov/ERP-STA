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
use app\models\DataUploadCsvForm;
use app\models\DataUploadProposalForm;
use app\models\UserListForm;
use app\models\ModuleListForm;
use app\models\UserList;
use app\models\StatusForm;
use app\models\PersonalForm;
use app\models\MarketingForm;
use app\models\FinForm;
use app\models\OrgDetail;
use app\models\OrgDeals;
use app\models\OrgContactForm;
use app\models\MailForm;
use app\models\MailAttachForm;
use app\models\HeadForm;
use app\models\ColdForm;
use app\models\ColdInitSelectForm;
use app\models\ConfigForm;
use app\models\ManagerActivityForm;
use app\models\EventRegForm;
use app\models\OrgContactsDetail;
use app\models\PhoneForm;
use app\models\AdressForm;
use app\models\EmailForm;
use app\models\LeadDetailForm;



class SiteController extends Controller
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            $curUser=Yii::$app->user->identity;
            
            if ($curUser->roleFlg & (0x0080|0x0002|0x0004))
            {
                    
                if ( !($curUser->roleFlg & (0x0001|0x0008|0x0004|0x0010|0x0020|0x0040|0x0100|0x0200)) )
                {
                    $this->redirect(['market/market-start']);            
                }
                
            }
        
        }
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    public function actionOrgInuse()
    {
          $model = new OrgDetail();         
           $request = Yii::$app->request;    
         $id = $request->get('id');                            

         return $this->render('org-inuse', ['model' => $model, 'id' => $id ]);
    }
    
/**********************************/    
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $session = Yii::$app->session;
        $session->close();
        Yii::$app->db->createCommand("INSERT INTO {{%log}} (refUser,actionType,actionText) VALUES (:refUser, 2, 'Выход из системы') ", 
        [':refUser' => Yii::$app->user->id]) ->execute();  
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {        
       if (Yii::$app->user->isGuest){ $this->redirect(['site/index']); return;}

        $curUser=Yii::$app->user->identity;
        
        $model = new ContactForm();
        $model->name=$curUser->userFIO;
        $model->email='Y3su@rik-nsk.ru';
        
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            
        //    $this->redirect(['site/index']);
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSuccess()
    {
        return $this->render('success');
    }
    public function actionClose()
    {
        return $this->render('close');
    }
    
/********************************/

/********************************/
    public function actionHeadStart()
    {
         $curUser=Yii::$app->user->identity;
         if (!($curUser->roleFlg & 0x0020)) { return $this->render('index');}
         
         $request = Yii::$app->request; 
         $model = new HeadForm();
         $cold_model = new ColdForm();
         $cold_view_model = new ColdInitSelectForm();

    
         $detail = intval($request->get('detail',1));
         $format = $request->get('format','html');
         
         $model -> detail = $detail;
         
        if ($format == 'phone')
        {
            $detailFile = $model->getOrgPhoneData(Yii::$app->request->get());     
            
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
         
        if ($format == 'csv')
        {
            if      ($detail < 9)   $detailFile = $model->getCurrentDealData(Yii::$app->request->get());     
            elseif ($detail == 9)   $detailFile = $model->getContactListData(Yii::$app->request->get()); 
            elseif ($detail == 10)  $detailFile = $model->getLostListData(Yii::$app->request->get()); 
            elseif ($detail == 11)  $detailFile = $model->getContactListData(Yii::$app->request->get());     
            elseif ($detail == 12)  $detailFile = $cold_view_model->getData(Yii::$app->request->get());
            elseif ($detail == 13)  $detailFile = $model->getCurrentDealData(Yii::$app->request->get());     
            elseif ($detail == 15)  $detailFile = $model->getSavedClientReestrData(Yii::$app->request->get());                 

            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        if ($detail == 10) $detailProvider = $model->getLostListProvider(Yii::$app->request->get());
        elseif ($detail == 11) $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        elseif ($detail == 12) $detailProvider = $cold_view_model->search(Yii::$app->request->get());
        elseif ($detail == 15) $detailProvider = $model->getSavedClientReestrProvider(Yii::$app->request->get());
        else
        {
         $detailProvider = $model->getContactListProvider(Yii::$app->request->get());
        }    
         return $this->render('head-start', ['model' => $model, 'detail'=> $detail, 'cold_model' => $cold_model, 'cold_view_model' => $cold_view_model, 'detailProvider' => $detailProvider]); 
    }
    
    
public function actionUpdateReestrClient()    
{
  $this->redirect(['data/progress', 'nextForm' => 'site/load-reestr-client']);         
}

public function actionLoadReestrClient()    
{
  $model = new HeadForm();
  $model->fillClientReestrData();
  $this->redirect(['site/head-start', 'detail' => 15]);         
}
    
/*************************************************/    
    public function actionOrgSetcategory()
    {
         $request =Yii::$app->request;
         $id= intval($request->get('id',0));             
         $category= intval($request->get('count',0));             

         $orgModel = new OrgDetail();
         $orgModel->setOrgCategory($id,$category);         
        
        $this->redirect(['site/success']);
    }

    
    /*************************************************/
    /************* Статистика ************************/
    /*************************************************/
    public function actionStatContacts()
    {
       if (Yii::$app->user->isGuest){ $this->redirect(['site/index']); return;}
        
          $model = new HeadForm();         

         return $this->render('stat-contacts', ['model' => $model, 'provider' => $model->getClientContactActivityProvider()]);
    }
    /*************************************************/
    public function actionStatOrgs()
    {
    
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
            return;
        }


         $model = new HeadForm();         
         $session = Yii::$app->session;        
         $session->open();
         
         $request =Yii::$app->request;
         $fltId = $request->get('fltId',0);    
         $period= $request->get('period',60);             
         if (!empty($fltId))
         {
            $filtPar =     $model->loadFltStatOrg ($fltId);
            $session->set('statOrgFilt', $filtPar);         
            return $this->render('stat-orgs', ['model' => $model, 'provider' => $model->getOrgContactActivityProvider($filtPar), 'fltId' => $fltId]);     
         }
         $model ->period = $period;
         $filtPar=Yii::$app->request->get();
         $session->set('statOrgFilt', $filtPar);        
 
         return $this->render('stat-orgs', ['model' => $model, 'provider' => $model->getOrgContactActivityProvider(Yii::$app->request->get()), 'fltId' => $fltId]);
    }
/***/

    public function actionChngOrgManager()
    {
       if (Yii::$app->user->isGuest){ $this->redirect(['site/index']); return;}
         
        $request =Yii::$app->request;
        $orgId= $request->get('orgId',0);             
        $session = Yii::$app->session;        
        $session->open();
        $session->set('ChngOrgManagerOrgId', $orgId);         
         
         if ($orgId==0){
            $problemMessage    ="Вероятно не задан идентификатор организации";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;
         }

        $model = new HeadForm();         
        $provider = $model->getManagerList();
        
         return $this->render('select-manager', ['model' => $model, 'provider' => $provider]);
    }
/***/  
    public function actionSetOrgManager()
    {
       if (Yii::$app->user->isGuest){ $this->redirect(['site/index']); return;}
        
        $session = Yii::$app->session;        
        $session->open();
         
        $request =Yii::$app->request;
        $managerId= $request->get('managerId',0);             
        if ($managerId==0){
            $problemMessage    ="Вероятно не задан идентификатор менеджера";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;
         }

        
        $orgId= $session->get('ChngOrgManagerOrgId');         
         
         if ($orgId==0){
            $problemMessage    ="Вероятно не задан идентификатор организации";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;
         }

        $model = new HeadForm();

        
        $ret =$model->setOrgManager($orgId, $managerId);
        
        if ($ret == false)
        {
            $problemMessage    ="Ошибка в процедуре смены менеджера! <br>".$orgId." ".$managerId;
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;
            
        }
        
         $this->redirect(['site/success']);
    }
    /**********************************************************
            Статистика за год
    ***********************************************************/
    
    public function actionStatYearSales()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }

    
          $model = new HeadForm();         
         $request =Yii::$app->request;
         $format = $request->get('format','html');
         $model->format =$format;
        if ($format == 'csv')
        {
            $detailFile = $model->getStatYearData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
         
        $provider = $model->getStatYearProvider(Yii::$app->request->get());        
        return $this->render('stat-year-sales', ['model' => $model, 'provider' => $provider]);
    }    
    
    public function actionStatYearOplata()
    {
         if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; } 
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $format = $request->get('format','html');
         $model->format =$format;
        if ($format == 'csv')
        {
            $detailFile = $model->getStatOplataData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
         
        $provider = $model->getStatOplataProvider(Yii::$app->request->get());        
        return $this->render('stat-year-oplata', ['model' => $model, 'provider' => $provider]);
    }    
    
    public function actionStatYearContacts()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $format = $request->get('format','html');
         $model->format =$format;
        if ($format == 'csv')
        {
            $detailFile = $model->getContactsYearData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         

        $provider = $model->getContactsYearProvider(Yii::$app->request->get());        
        return $this->render('stat-year-contacts', ['model' => $model, 'provider' => $provider]);
    }    
    
    

    public function actionStatYearGoods()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $format = $request->get('format','html');
         $model->format =$format;
        if ($format == 'csv')
        {
            $detailFile = $model->getGoodYearData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         

        $provider = $model->getGoodYearProvider(Yii::$app->request->get());        
        return $this->render('stat-year-goods', ['model' => $model, 'provider' => $provider]);
    }    

    /*************************************************/

    public function actionManagerOrgStat()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $model->period= intval($request->get('period',30));             
         $format= $request->get('format','html');             
        
        if ($format == 'csv')
        {
            $detailFile = $model->getManagerOrgStatData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
        
         $provider = $model->getManagerOrgStatProvider(Yii::$app->request->get());        
         //return;
         return $this->render('manager-org-stat', ['model' => $model, 'provider' => $provider]);
        
    }
    /*************************************************/

    public function actionNoContactOrgStat()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $model->period= intval($request->get('period',30));             
         $model->userId= intval($request->get('userId',0));             
         $format= $request->get('format','html');    
        

        if ($format == 'csv')
        {
            $detailFile = $model->getOrgNoContactData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         

         $provider = $model->getOrgNoContactProvider(Yii::$app->request->get());        
                 //return;
         return $this->render('no-contact-org-stat', ['model' => $model, 'provider' => $provider]);
        
    }

    public function actionDetailOrgStat()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $model->period= intval($request->get('period',30));             
         $model->userId= intval($request->get('userId',0));             
         $format= $request->get('format','html');    
        

        if ($format == 'csv')
        {
            $detailFile = $model->getOrgStatContactsData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        
         $provider = $model->getOrgStatContactsProvider(Yii::$app->request->get());                
         return $this->render('detail-org-stat', ['model' => $model, 'provider' => $provider]);
        
    }
/*************************************************/
    public function actionNoSchetStat()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $model->period= intval($request->get('period',30));             
         $format= $request->get('format','html');             
         $model->userId= intval($request->get('userId',0));                      

        if ($format == 'csv')
        {
            $detailFile = $model->getNoSchetData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        
         $provider = $model->getNoSchetProvider(Yii::$app->request->get());        
  
         return $this->render('detail-schet-stat', ['model' => $model, 'provider' => $provider]);
        
    }

    
    
    
/*************************************************/
    public function actionDetailSchetStat()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $model->period= intval($request->get('period',30));             
         $format= $request->get('format','html');             
         $model->userId= intval($request->get('userId',0));                      

        if ($format == 'csv')
        {
            $detailFile = $model->getOrgStatSchetData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         
        
         $provider = $model->getOrgStatSchetProvider(Yii::$app->request->get());        
  
         return $this->render('detail-schet-stat', ['model' => $model, 'provider' => $provider]);
        
    }

    
    
/*************************************************/
/*Obsoleted*/
    public function actionOrgDealReestr()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $request =Yii::$app->request;
         $orgId= intval($request->get('orgId',0));             
        
         if ($orgId == 0)    
         {
            $session = Yii::$app->session;        
            $session->open();
            $problemMessage    ="Не задан идентификатор организации!";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;

         }
         $orgModel = new OrgDetail();
         $orgModel->orgId=$orgId;         
         $orgRecord=$orgModel->loadOrgRecord();         

         $provider = $model->getDocReestrProvider(Yii::$app->request->get(), $orgId);        
         return $this->render('orgs-deals-reestr', ['model' => $model, 'provider' => $provider, 'orgRecord' => $orgRecord]);
        
    }
/*************************************************/

    public function actionOrgsClientReestr()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request =Yii::$app->request;
         $orgId= intval($request->get('orgId',0));             
        
         if ($orgId == 0)    
         {
            $session = Yii::$app->session;        
            $session->open();
            $problemMessage    ="Не задан идентификатор организации!";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;

         }
         $orgModel = new OrgDetail();
         $orgModel->orgId=$orgId;         
         $orgRecord=$orgModel->loadOrgRecord();         

         $provider = $orgModel->getOrgsClientProvider(Yii::$app->request->get(), $orgId);        
         return $this->render('orgs-client-reestr', ['model' => $orgModel, 'provider' => $provider, 'orgRecord' => $orgRecord]);
        
    }


    public function actionOrgsSupplierReestr()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request =Yii::$app->request;
         $orgId= intval($request->get('orgId',0));             
        
         if ($orgId == 0)    
         {
            $session = Yii::$app->session;        
            $session->open();
            $problemMessage    ="Не задан идентификатор организации!";
            $session->set('problemMessage', $problemMessage);         
            $this->redirect(['site/problem']);
            return;

         }
         $orgModel = new OrgDetail();
         $orgModel->orgId=$orgId;         
         $orgRecord=$orgModel->loadOrgRecord();         

         $provider = $orgModel->getOrgsSupplierProvider(Yii::$app->request->get(), $orgId);        
         return $this->render('orgs-supplier-reestr', ['model' => $orgModel, 'provider' => $provider, 'orgRecord' => $orgRecord]);
        
    }

/*************************************************/    
 public function actionSchetRmRef()
    {
         $request =Yii::$app->request;
         $schetRef= intval($request->get('schetRef',0));             

         $model = new HeadForm();
         $model->schetRmRef($schetRef);         
        
        $this->redirect(['site/success']);
    }

/*************************************************/    
    
    public function actionLinkContactZakaz()
    {
         $request =Yii::$app->request;
         $contactId= intval($request->get('contactId',0));             
         $zakazId= intval($request->get('zakazId',0));             
         $model = new OrgContactsDetail();
         $sendArray =$model->linkContactZakaz($contactId,$zakazId);         
          if(Yii::$app->request->isAjax){
             echo json_encode($sendArray);
             return; 
         }
        $this->redirect(['site/success']);
    }
    
/*************************************************/    
    public function actionSwitchOrg()
    {
         $request =Yii::$app->request;
         $id= intval($request->get('id',0));             

         $orgModel = new OrgDetail();
         $sendArray =$orgModel->switchOrgReject($id);         
          if(Yii::$app->request->isAjax){
             echo json_encode($sendArray);
             return; 
         }
        $this->redirect(['site/success']);
    }

/*************************************************/    

    public function actionHeadManagerActivity()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new ManagerActivityForm();         

         $request =Yii::$app->request;
         $model->monthShift= intval($request->get('monthShift',0));             
         
         $model->prepareManagerActivityData(Yii::$app->request->get());        
         
         return $this->render('head-manager-activity', ['model' => $model, ]);             
    }

    /*************************************************/
    public function actionManagerOplataList()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }     
        $request =Yii::$app->request;      
        $model = new ManagerActivityForm();         
        
        $model->userId = intval($request->get('uid',0));                                
        $model->month  = intval($request->get('m',0));                                
        $model->year   = intval($request->get('y',0));                                
        $model->day    = intval($request->get('d',0));                

        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOplataListData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
        
        $provider=$model->getOplataListProvider(Yii::$app->request->get());        
        return $this->render('manager-oplata-list', ['model' => $model, 'provider' => $provider, 'userId' => $model->userId]);
    }
    /*************************************************/
    public function actionManagerOrgActivity()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }  
        $request =Yii::$app->request;      
        $model = new ManagerActivityForm();         

        $model->userId = intval($request->get('uid',0));                                
        $model->month  = intval($request->get('m',0));                                
        $model->year   = intval($request->get('y',0));                                
        $model->day    = intval($request->get('d',0));                                     
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOrgActivityData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    

        
        $provider=$model->getOrgActivityProvider(Yii::$app->request->get());        
        return $this->render('manager-org-activity', ['model' => $model, 'provider' => $provider, 'userId' => $model->userId]);
    }
    /*************************************************/
    public function actionManagerResult()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }   
        $request =Yii::$app->request;      
        $model = new ManagerActivityForm();         

        $model->userId = intval($request->get('uid',0));                                
        $model->month  = intval($request->get('m',0));                                
        $model->year   = intval($request->get('y',0));                                
        $model->day    = intval($request->get('d',0));                                     
        $model->monthShift= intval($request->get('monthShift',0));                                     
               
        return $this->render('manager-result', ['model' => $model, 'userId' => $model->userId]);
    }
    
    /*************************************************/
    public function actionManagerSchetActivity()
    {
      if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }  
        $request =Yii::$app->request;      
        $model = new ManagerActivityForm();         
                 
        $model->userId = intval($request->get('uid',0));                                
        $model->month  = intval($request->get('m',0));                                
        $model->year   = intval($request->get('y',0));                                
        $model->day    = intval($request->get('d',0));                                     
        
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getSchetActivityData(Yii::$app->request->get());     
   /*         echo "<pre>";
            print_r($detailFile);
            echo "</pre>";
            return;*/
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    

        $provider=$model->getSchetActivityProvider(Yii::$app->request->get());        
        return $this->render('manager-schet-activity', ['model' => $model, 'provider' => $provider, 'userId' => $model->userId]);
    }
    
    /*************************************************/
    
    public function actionStatDetail()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new ManagerActivityForm();         
         $request =Yii::$app->request;
         $userId = intval($request->get('id',0));    
         $model->monthShift=intval($request->get('monthShift',0));    

         
         return $this->render('stat-detail', ['model' => $model, 'userId' => $userId ]);
    }

    
/*************************************************/    

    public function actionDownloadStatOrgs()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $session = Yii::$app->session;        
         $session->open();
         $request =Yii::$app->request;
         
        $filtPar=$session->get('statOrgFilt');        
        $period= $request->get('period',60);             
        $model ->period = $period;
        $fname=$model->prepareDownloadStatOrgs($filtPar); 
         if ( $fname!= false )             
         return $this->render('download-stat-orgs', ['model' => $model, 'fname' => $fname]);
         else
         {
             $session->set('problemMessage','Ошибка при формировании файла!');        
            $this->redirect(['site/problem']);         
         }
             
    }

    
    public function actionSaveFltStatOrgs()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new HeadForm();         
         $session = Yii::$app->session;        
         $session->open();
         
         $request =Yii::$app->request;
         $fltName = $request->get('fltName');                            
         if (!empty($fltName))
         {
            $filtPar=$session->get('statOrgFilt');        
            $model->addFltStatOrg($fltName, $filtPar)    ;
            $this->redirect(['site/success']);
            return;
         }
            $session->set('problemMessage','Не корректно задано имя фильтра');        
            $this->redirect(['site/problem']);         
    }
    
    
/********************************/    
    public function actionProblem()
    {
        $session = Yii::$app->session;        
        $session->open();
        $problemMessage=$session->get('problemMessage');    
        if (empty ($problemMessage)) {$problemMessage = "Ошибка. Что-то пошло не так";}        
         return $this->render('problem', ['name' => "Ошибка при выполнении операции", 'message' => $problemMessage]);
    }

/********************************/    

    public function actionDownload()
    {      
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;
        $url=$request->get('url');
        $redirect=$request->get('redirect',1);
        if (empty ($url))
        {
                $session = Yii::$app->session;        
                $session->open();
                $problemMessage    ="Вероятно не задана ссылка для выгрузки";
                $session->set('problemMessage', $problemMessage);         
                $this->redirect(['site/problem']);
        }
        
        return $this->render('download',['url' => $url, 'redirect' => $redirect]);
    }
    
    
    public function actionRole()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new UserListForm();
        $request = Yii::$app->request;
        
        
      // if(Yii::$app->request->isAjax)
       {
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {            
           $sendArray = $model->saveAjaxRole();                      
           echo json_encode($sendArray);
           exit (0);            
        }
      }  
        
        
        return $this->render('role', ['model' => $model]);     
                
    }


    public function actionRoleEdit()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new UserListForm();
        $request = Yii::$app->request;
        
      // if(Yii::$app->request->isAjax)
       {
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $sendArray = $model->saveAjaxData();                      
           echo json_encode($sendArray);
           exit (0);            
        }
      }  
         $id = $request->get('id',0);
         if ($id != 0 )
         {
            $model->id = intval($id);
            $record = UserList::findOne($id);                  
            $model->userName = $record->username;
            $model->userFio = $record->userFIO;
            $model->password = $record->password;         
            $model->phoneLink = $record->phoneLink;
            $model->phoneInternаl = $record->phoneInternаl;
            $model->userNote = $record->userNote;                  
            
            
            if ($record->roleFlg & 0x0001) {$model->isDataOp = 1;}
            if ($record->roleFlg & 0x0002) {$model->isColdOp = 1;}
            if ($record->roleFlg & 0x0004) {$model->isSchetOp = 1;}
            if ($record->roleFlg & 0x0008) {$model->isPersonalOp = 1;}
            if ($record->roleFlg & 0x0010) {$model->isScladOp = 1;}
            if ($record->roleFlg & 0x0020) {$model->isHead = 1;}
            if ($record->roleFlg & 0x0040) {$model->isFinOp = 1;}
            if ($record->roleFlg & 0x0080) {$model->isSchet2Op = 1;}
            if ($record->roleFlg & 0x0100) {$model->isHeadMarket = 1;}
            if ($record->roleFlg & 0x0200) {$model->isHeadSclad = 1;}
            if ($record->roleFlg & 0x0400) {$model->isBankOp = 1;}
            
            
            
              return $this->render('role-edit', ['model' => $model, 'record' => $record]);
         }
        $model->id = 0; 
        return $this->render('role-edit', ['model' => $model]);     
               
    }
    
    /*********************************************************/
    public function actionUploadProposal()
    {    
        $model = new DataUploadProposalForm();

        if (Yii::$app->request->isPost) {
            $model->proposalFile = UploadedFile::getInstance($model, 'proposalFile');
            if ($model->upload()) 
            {
                $this->redirect(['site/modules']); 
            }
        }
        else 
        {
            return $this->render('upload-proposal', ['model' => $model]);
        }
    }

   /*********************************************************/
    public function actionModules()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
        $model = new ModuleListForm();
        $request = Yii::$app->request;
        
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveData();                      
        }
         else 
        {                  
            $model->initData();
        }        
        return $this->render('modules', ['model' => $model]);     
                
    }
    /*********************************************************/

    public function actionDownloadStatus()
    {
        $model = new StatusForm();
                
        $fname = $model->prepareStatus();    
        return $this->render('download-status', ['fname' => $fname]);     
                
    }

   /*********************************************************/

    public function actionMarketingStart()
    {
     
        $curUser=Yii::$app->user->identity;
        if (!($curUser->roleFlg & 0x0001)) { return $this->render('index');}
        $model = new MarketingForm();                    
        return $this->render('marketing-start', ['model' => $model]);     
    }

   /*********************************************************/
    
    public function actionMarketingSchet()
    {         
         $model = new MarketingForm();         
          return $this->render('marketing-schet', ['model' => $model, 'provider' => $model->getSchetListProvider()]);
    }
    
   /*********************************************************/

   public function actionMarketingZakaz()
    {         
         $model = new MarketingForm();         
          return $this->render('marketing-zakaz', ['model' => $model, 'provider' => $model->getZakazListProvider()]);
    }
    
   /*********************************************************/
    

    public function actionPersonalStart()
    {
        $curUser=Yii::$app->user->identity;
        if (!($curUser->roleFlg & 0x0008)) { return $this->render('index');}
        $model = new PersonalForm();                    
        return $this->render('personal-start', ['model' => $model]);     
    }

  /*********************************************************/

   public function actionPersonalMarket()
    {         
         $model = new PersonalForm();                    
          return $this->render('personal-market', ['model' => $model, 'provider' => $model]);
    }
    

    
    /*********************************************************/

    public function actionNeedtitleRm()
    {
        $model = new ModuleListForm();
        $request = Yii::$app->request;
        
        $id = intval($request->get('id'));
         
         $model->needTitleRm($id);
        
        $this->redirect(['site/modules']); 
        return;     
                
    }

    
    /*********************************************************/
    
    public function actionContactsDetail()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
         $model = new OrgContactsDetail();         
         $request = Yii::$app->request;    
         $model->id = intval($request->get('id'));

         $tasksProvider     = $model->getTasksListProvider(Yii::$app->request->get());      
         $contactsProvider  = $model->getContactsListProvider(Yii::$app->request->get());         
         $eventProvider     = $model->getEventListProvider(Yii::$app->request->get());         
         return $this->render('contacts-detail', ['model' => $model, 'contactsProvider' => $contactsProvider, 'eventProvider' => $eventProvider, 'tasksProvider' => $tasksProvider ]);
    }

    public function actionShowContact()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
         $model = new OrgContactForm();         
         $request = Yii::$app->request;    
         $id = intval($request->get('id'));
         return $this->render('show-contact', ['model' => $model, 'id' => $id]);
    }
    
    public function actionZakazContactsDetail()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
         $model = new OrgContactsDetail();         
         $request = Yii::$app->request;    
         $model->refZakaz = intval($request->get('refZakaz',0));

         $contactsProvider  = $model->getZakazContactsListProvider(Yii::$app->request->get());         
         return $this->render('zakaz-contacts-detail', ['model' => $model, 'contactsProvider' => $contactsProvider,]);
    }

    public function actionSetContactStatus()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
         $model = new OrgContactsDetail();         
         $request = Yii::$app->request;    
         $id = intval($request->get('id',0));
         $task = $request->get('task','');
        //if(Yii::$app->request->isAjax)
        {
           $sendArray = $model->setContactStatus($id,$task);
           echo json_encode($sendArray);
           return;
        }
    }

    
    /*********************************************************/
    public function actionOrgCard()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId',0));
         $model->orgId=$orgId;
         $model->leadId= intval($request->get('leadId',0));
         $mode = intval($request->get('mode', 0));
         $noframe= intval($request->get('noframe', 0));
         $viewmode = $request->get('viewmode', 'h');
         
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveData();                      
           $this->redirect(['site/org-card', 'orgId' => $orgId, 'noframe' => $noframe, 'mode' => $mode, 'viewmode' => $viewmode]);
        }
    
        $adressProvider    = $model->getAdressListProvider();
        $accProvider    = $model->getAccountsProvider();
        $view = 'org-card';
        if ($viewmode=='v')$view = 'org-card-v';
         else $view = 'org-card';

          return $this->render($view, ['model' => $model,'adressProvider' => $adressProvider, 'noframe' => $noframe,
          'accProvider' => $accProvider, 'mode' => $mode]);
    
    }

    
    /*********************************************************/
    public function actionSelfCard()
    {
    
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDetail();
         $request = Yii::$app->request;             
         $noframe= intval($request->get('noframe', 0));
         
        $model->loadSelfOrgData();                  
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveData();                      
           $this->redirect(['site/self-card',  'noframe' => $noframe ]);
        }
    
        $adressProvider    = $model->getAdressListProvider();
        $accProvider    = $model->getAccountsProvider();
          return $this->render('self-card', ['model' => $model,'adressProvider' => $adressProvider, 'noframe' => $noframe, 
          'accProvider' => $accProvider]);
    
    }
    
        
    /*********************************************************/
    /*********** Реестр ******************************/
    /*********************************************************/
    
    public function actionOrgReestr()
    {
       if (Yii::$app->user->isGuest) $this->redirect(['site/index']);                         
       $request =Yii::$app->request;      
       $model = new OrgDetail();      
       $model->detail =$request->get('detail',0);
       $provider = $model->getOrgReestrProvider(Yii::$app->request->get());       
       return $this->render('org-reestr', ['model' => $model,'provider' => $provider]);
    }
    
    /*********************************************************/
    /*********** Взаимодействие ******************************/
    /*********************************************************/
    
    public function actionOrgDeals()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDeals();
        $request = Yii::$app->request; 
        $format = $request->get('format','html');
        if ($format == 'csv')
        {
            $detailFile = $model->getOrgDealsData(Yii::$app->request->get());     
            $url = Yii::$app->request->baseUrl."/../".$detailFile;
            $this->redirect(['site/download', 'url' => $url]);             
            return;
        }    
         $provider    = $model->getOrgDealsProvider(Yii::$app->request->get());      
         return $this->render('org-deals', ['model' => $model,'provider' => $provider, ]);    
    }

    public function actionSwitchOrgDeal()
    {   
        $request = Yii::$app->request;        
        $orgRef = $request->get('orgRef', 0);                          
        $actionRef = $request->get('actionRef', 0);                     
        $grpCode  = $request->get('grpCode', 0);                        

       // if(Yii::$app->request->isAjax)
        {
                $model = new OrgDeals();   
                $sendArray = $model->switchOrgDeal($orgRef, $actionRef, $grpCode);
                echo json_encode($sendArray);
                return;
        }
    }    

    public function actionSwitchOrgMainDeal()
    {   
        $request = Yii::$app->request;        
        $orgRef = $request->get('orgRef', 0);                          
        $grpCode  = $request->get('grpCode', 0);                        

        if(Yii::$app->request->isAjax)
        {
                $model = new OrgDeals();   
                $sendArray = $model->switchOrgType($orgRef, $grpCode);
                echo json_encode($sendArray);
                return;
        }
    }    
    
    /*********************************************************/
    public function actionOrgDealsCfg()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDeals();
         
         $provider    = $model->getOrgDealsCfgProvider(Yii::$app->request->get());      
         return $this->render('org-deals-cfg', ['model' => $model,'provider' => $provider, ]);    
    }

    public function actionSwitchDealCfg()
    {   
        $request = Yii::$app->request;        
        $id = $request->get('id', 0);                          
        $grpCode  = $request->get('grpCode', 0);                        

        if(Yii::$app->request->isAjax)
        {
                $model = new OrgDeals();   
                $sendArray = $model->switchActionType($id, $grpCode);
                echo json_encode($sendArray);
                return;
        }
    }    
    public function actionSwitchDealSign()
    {   
        $request = Yii::$app->request;        
        $id = $request->get('id', 0);                          
        $val  = $request->get('val', 0);                        

        if(Yii::$app->request->isAjax)
        {
                $model = new OrgDeals();   
                $sendArray = $model->switchActionSign($id, $val);
                echo json_encode($sendArray);
                return;
        }
    }    

    
    
    /*********************************************************/
    
    public function actionSingleOrgDeals()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new OrgDeals();
        $request = Yii::$app->request; 
        $model->orgId = $request->get('orgId',0 );        
        return $this->render('single-org-deals', ['model' => $model]);    
    }

    public function actionOrgDealSelect()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $model = new OrgDeals();
        $request = Yii::$app->request; 
        $model->orgId = $request->get('orgId',0 );        
        $model->selectedDeal = $request->get('selectedDeal',0 );        
        return $this->render('select-org-deal', ['model' => $model]);    
    }
    /*********************************************************/    
    /*********************************************************/    
    
    
    
    

    /*********************************************************/
    public function actionConfig()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
       $model = new ConfigForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveData();                   
            return $this->goHome();                      
        }
            
          return $this->render('config', ['model' => $model]);
    
    }
    /*********************************************************/
    public function actionOrgDetail()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId'));
         $model->orgId=$orgId;
         
        $session = Yii::$app->session;        
        $session->open();
        $session->set('ChngOrgManagerOrgId', $orgId);         

        $headModel = new HeadForm();         
        $managerListprovider = $headModel->getManagerList();    

         
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->saveNote();                      
           $this->redirect(['site/org-detail', 'orgId' => $orgId]);           
        }
         
         $activityProvider = $model->getSdelkiForOrgProvider(Yii::$app->request->get());         
         $eventListProvider = $model->getEventListProvider();
         $resetContractProvider = $model->getResetContractListProvider(Yii::$app->request->get());
         return $this->render('org-detail', ['model' => $model, 'activityProvider' => $activityProvider, 'eventListProvider' =>$eventListProvider,'managerListprovider' => $managerListprovider, 'resetContractProvider' =>$resetContractProvider ]);
    }
    /*********************************************************/
    public function actionOrgDublicate()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId'));
         $model->orgId=$orgId;
         $provider = $model->getOrgDublicateProvider(Yii::$app->request->get());         
         return $this->render('org-dublicate', ['model' => $model, 'provider' => $provider, ]);
    }
    /*********************************************************/
    public function actionMergeOrg()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $masterRef = intval($request->get('masterRef', 0));
         $slaveRef  = intval($request->get('slaveRef', 0));
         
         $model->mergeOrg($masterRef, $slaveRef);       
         $this->redirect(['site/success']);           
    }
    
     public function actionAddOrgInGrp()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId',0));
         $grpId = intval($request->get('grpId',0));                  
            $model->addOrgToGroup($orgId, $grpId);        
        $this->redirect(['site/success']);           
    }  

    public function actionCreateOrg()
    {   
        $request = Yii::$app->request;        
        $orgTitle = $request->get('orgTitle', '');                          
        //if(Yii::$app->request->isAjax)
        {
                $model = new OrgDetail();   
                $sendArray = $model->createOrg($orgTitle);
                echo json_encode($sendArray);
                return;
        }
    }        

    /*********************************************************/
       public function actionOrgLinkContract()
       {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId'), 0);
         $contractId = intval($request->get('contractId'), 0);            
         $model->orgLinkContract($orgId, $contractId);       
         $this->redirect(['site/success']);           
       }
       
       
    
    /*********************************************************/
    public function actionOrgInGrp()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }        
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId'));
         $model->orgId=$orgId;
         
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->addGroupTitle();                                 
        }
         $provider = $model->getOrgGroupProvider(Yii::$app->request->get());         
          return $this->render('org-in-grp', ['model' => $model, 'provider' => $provider]);
    }
    /*********************************************************/
    public function actionOrgAddGrp()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId',0));
         $grpId = intval($request->get('grpId',0));
                  
            $model->addOrgToGroup($orgId, $grpId);
        
        $this->redirect(['site/org-in-grp', 'orgId' => $orgId]);                            
    }  
    /*********************************************************/
    public function actionOrgRmGrp()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId',0));
                  
            $model->orgDelFromGroup($orgId);
        
        $this->redirect(['site/org-in-grp', 'orgId' => $orgId]);                            
    }  
    /*********************************************************/
    public function actionOrgGrpDel()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId',0));
         $grpId = intval($request->get('grpId',0));
             
              $model->remGroupById($grpId);
        
        $this->redirect(['site/org-in-grp', 'orgId' => $orgId]);                            
    }  

        /*********************************************************/
    
    public function actionChngUrlStat()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $id = intval($request->get('id'));
         $stat = intval($request->get('stat'));
         $model->markUrls($id, $stat);                           
            $this->redirect(['site/success']);                   
    }
/************************/
    public function actionChngPhoneStat()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $id = intval($request->get('id'));
         $stat = intval($request->get('stat'));
         $model->markPhone($id, $stat);                           
         $this->redirect(['site/success']);                   
    }

    public function actionAddNewPhone()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewPhone($orgRef);        
         $this->redirect(['site/success']);                   
    }
        
     public function actionSavePhoneDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new OrgDetail();                
       

        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->savePhoneDetail();
                echo json_encode($sendArray);
                return;
            }    
       }
      
       $this->redirect(['site/success']);            
    }   
    
     public function actionSaveDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new OrgDetail();                
       
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveDetail();
                echo json_encode($sendArray);
                return;
            }    
       }          
    }   

     public function actionSaveSelfDetail()
    {    
        $request = Yii::$app->request;    
        $model=  new OrgDetail();                
       
        //if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveSelfDetail();
                echo json_encode($sendArray);
                return;
            }    
       }          
    }   
    
           
    public function actionAddNewOkved()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewOkved($orgRef);        
         $this->redirect(['site/success']);                   
    }

    public function actionAddNewDblGis()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewDblGis($orgRef);        
         $this->redirect(['site/success']);                   
    }
    
    public function actionAddNewAcc()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewAccount($orgRef);        
         $this->redirect(['site/success']);                   
    }

    public function actionAddNewAdress()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewAdress($orgRef);        
         $this->redirect(['site/success']);                   
    }

    public function actionAddNewEmail()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewEmail($orgRef);        
         $this->redirect(['site/success']);                   
    }
    
    public function actionAddNewUrl()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $orgRef = intval($request->get('orgRef'), 0);         
         $model->addNewUrl($orgRef);        
         $this->redirect(['site/success']);                   
    }
    
/************************/

    public function actionChngAdressStat()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $id = intval($request->get('id'));
         $stat = intval($request->get('stat'));
         $model->markAdress($id, $stat);                           
            $this->redirect(['site/success']);                   
    }
    
    public function actionChngEmailStat()
    {
         $model = new OrgDetail();
         $request = Yii::$app->request;    
         $id = intval($request->get('id'));
         $stat = intval($request->get('stat'));
         $model->markEmail($id, $stat);                           
            $this->redirect(['site/success']);                   
    }
    
    
    /*********************************************************/
    public function actionRegContactByMail(){
        $request = Yii::$app->request;        
        $model = new MailForm();
        $mailId = intval($request->get('id'));
        $contactId = $model->createContactByMail($mailId);
        
        $this->redirect(['site/reg-contact', 'contactId' => $contactId, 'singleWin' => 1, 'noframe' => 1]);        
    }

   
    
    public function actionRegContact()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $orgModel = new OrgDetail();
        $model    = new OrgContactForm();
        
        $request = Yii::$app->request;    
        $singleWin = intval($request->get('singleWin',1));
        $phone = $request->get('phone',0);
        $contactFIO = $request->get('contactFIO','');
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->regContact();  
       
           if ($singleWin == 1)
           {
             $this->redirect(['site/success']);  
             return;
           }
           else 
           {
            $this->redirect(['site/org-detail', 'orgId' => $orgId]);
            return;
           }
          
        }


        $model->purchaseRef= intval($request->get('purchaseRef', 0));        
        $contactId= intval($request->get('contactId', 0));        
        if (empty($contactId)){
         /* создаем новый*/
         $orgId = intval($request->get('id', 0));
         $orgModel->orgId=$orgId;         
         $record=$orgModel->loadOrgRecord();         
         $model->orgId=$orgId;
         $model->atsRef=intval($request->get('atsRef', 0));

         $model->contactEmail =$record->contactEmail;
         if (empty($phone)) $model->contactPhone =$record->contactPhone;
                      else  $model->contactPhone =$phone;
                     
         if (empty($contactFIO))$model->contactFIO   =$record->contactFIO;     
                           else $model->contactFIO   =$contactFIO;     
         
         $model->nextContactDate = date("d.m.Y", time()+60*60*24);
         $model->status=1;        
        }
        else {
           $model->loadContact($contactId);
           $orgId = $model->orgId;
           $orgModel->orgId=$orgId;         
           $record=$orgModel->loadOrgRecord();         
           
        }
         

         
         $phoneProvider   = $model->getPhoneProvider(Yii::$app->request->get());
         $contactProvider = $orgModel->getOrgContactProvider(Yii::$app->request->get());
         $activityProvider = $orgModel->getSdelkiForOrgProvider(Yii::$app->request->get());                  
         
          return $this->render('reg-contact', ['model' => $model, 'orgModel' => $orgModel, 
          'phoneProvider'    =>  $phoneProvider,
          'contactProvider'  => $contactProvider,
          'activityProvider' => $activityProvider ,          
          ]);
    }

    /*********************************************************/
    
    public function actionActiveSdelka()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $orgModel = new OrgDetail();
        $request = Yii::$app->request;    
        $orgId = intval($request->get('id'));
         $orgModel->orgId=$orgId;         
         $activityProvider = $orgModel->getSdelkiForOrgProvider(Yii::$app->request->get());                  
         
          return $this->render('active-sdelka', ['orgModel' => $orgModel, 
          'activityProvider' => $activityProvider ,          
          ]);
    }
    
    
    public function actionRegContactNew()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $orgModel = new OrgDetail();
        $model    = new OrgContactForm();
        
        $request = Yii::$app->request;    
        $orgId = intval($request->get('id'));
        $singleWin = intval($request->get('singleWin',0));
        $phone = $request->get('phone',0);
        $contactFIO = $request->get('contactFIO','');
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
           $model->regContact();                                 
           if ($singleWin == 1)
           {
             $this->redirect(['site/success']);  
             return;
           }
           else 
           {
            $this->redirect(['site/org-detail', 'orgId' => $orgId]);
            return;
           }
          
        }
         
         $orgModel->orgId=$orgId;         
         $record=$orgModel->loadOrgRecord();         

         $model->orgId=$orgId;
         $model->contactEmail =$record->contactEmail;
         if (empty($phone)) $model->contactPhone =$record->contactPhone;
                      else  $model->contactPhone =$phone;
                     
         if (empty($contactFIO))$model->contactFIO   =$record->contactFIO;     
                           else $model->contactFIO   =$contactFIO;     
         
         $model->nextContactDate = date("d.m.Y", time()+60*60*24);
         $model->status=1;
         
         $phoneProvider   = $model->getPhoneProvider(Yii::$app->request->get());
         $contactProvider = $orgModel->getOrgContactProvider(Yii::$app->request->get());
         $activityProvider = $orgModel->getSdelkiForOrgProvider(Yii::$app->request->get());                  
         
          return $this->render('reg-contact-new', ['model' => $model, 'orgModel' => $orgModel, 
          'phoneProvider'    =>  $phoneProvider,
          'contactProvider'  => $contactProvider,
          'activityProvider' => $activityProvider ,          
          ]);
    }
        
/*******************************************************************************/       

public function actionShowPhoneContact()
{
    $request = Yii::$app->request;    
    $model    = new OrgContactForm();
    $model->orgId = intval($request->get('refOrg',0));
    $model->contactFIO = $request->get('contactFIO',"");

    $provider   = $model->getPhoneContactProvider(Yii::$app->request->get());

    
    return $this->render('show-phone-contact', ['model' => $model, 'provider'=>$provider ]);         
}

/*******************************************************************************/   
 public function actionLeadList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgContactForm();
         $leadListProvider = $model->getLeadListProvider(Yii::$app->request->get());
         return $this->render('lead-list', ['model' => $model, 'leadListProvider'=>$leadListProvider ]);         
    }

 public function actionHeadLeadsList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;    
        $model = new OrgContactForm();
        $model->fltStatus = intval($request->get('fltStatus',0));
        
        $model->fltOverdue = intval($request->get('fltOverdue',1));
        $model->fltToday = intval($request->get('fltToday',1));
        $model->fltTomorrow = intval($request->get('fltTomorrow',1));
        $model->toDate = $request->get('toDate',date("Y-m-d"));

        $leadListProvider = $model->getHeadLeadListProvider(Yii::$app->request->get());
        return $this->render('head-lead-list', ['model' => $model, 'leadListProvider'=>$leadListProvider ]);         
    }
    
public function actionLeadCalendar()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new OrgContactForm();     
        $request = Yii::$app->request; 
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));

        $request = Yii::$app->request; 
           
        return $this->render('lead-calendar', ['model' => $model, 'month' => $month, 'year' => $year  ]);        
    }
    
    
 public function actionIgnoreLead()
    {   
         $model = new OrgContactForm();
         $request = Yii::$app->request;    
         $contactId = intval($request->get('contactId',0));

         $model->markContact($contactId, 21);
       
        $this->redirect(['site/success']);
        return;

    }
    
 public function actionAcceptLead()
    {   
        $model = new OrgContactForm();
        $request = Yii::$app->request;    
        $contactId = intval($request->get('contactId',0));

        $model->markContact($contactId, 102);
         
        $this->redirect(['site/success']);
        return;

    }

 public function actionLeadDocList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgContactForm();
         $provider = $model->getDocListProvider(Yii::$app->request->get());
         return $this->render('lead-doclist', ['model' => $model, 'provider'=>$provider]);         
    }
   
 public function actionLeadOrgList()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgContactForm();
         $orgListProvider = $model->getOrgListProvider(Yii::$app->request->get());
         return $this->render('lead-orglist', ['model' => $model, 'orgListProvider'=>$orgListProvider]);         
    }
 public function actionLeadZakazList()
    {   
         $request = Yii::$app->request;    
         $orgId = intval($request->get('orgId'));
         $model = new OrgContactForm();
         $model->orgId = $orgId;
         $model->getOrgInfo();
         $orgListProvider = $model->getZakazListProvider(Yii::$app->request->get());
         return $this->render('lead-zakazlist', ['model' => $model, 'orgListProvider'=>$orgListProvider]);         
    }
/*******************************************************************************/       
    
 public function actionSelectOrg()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new OrgContactForm();
         $orgListProvider = $model->getOrgListProvider(Yii::$app->request->get());
         return $this->render('select-org', ['model' => $model, 'orgListProvider'=>$orgListProvider]);         
    }
    
/*******************************************************************************/       
 public function actionGetNewZakaz()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $model = new OrgContactForm();

        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->createNewZakaz();
                echo json_encode($sendArray);
                return;
            }    
        }
    }

 public function actionAddDocToLead()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $leadid= intval($request->get('leadid', 0));
         $docid = intval($request->get('docid', 0));
         $model = new OrgContactForm();

        if(Yii::$app->request->isAjax)    
        {            
            {
                $sendArray = $model->addDocToLead($leadid, $docid);
                echo json_encode($sendArray);
                return;
            }    
        }
    }
 public function actionRmDocToLead()
  {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $leadid= intval($request->get('leadid', 0));
         $docid = intval($request->get('docid', 0));
         $model = new OrgContactForm();

        if(Yii::$app->request->isAjax)    
        {            
            {
                $sendArray = $model->rmDocToLead($leadid, $docid);
                echo json_encode($sendArray);
                return;
            }    
        }
    }            
/*********************************************************/
    
    public function actionHeadShowLead()
    {
        $model    = new OrgContactForm();        
        $request = Yii::$app->request;    
        $contactId = intval($request->get('contactId', 0));

        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                
                $id = $model->saveHeadLead();                
                $this->redirect(['site/head-show-lead','noframe' => '1', 'contactId' => $model->contactId]);         
            }                
        }                
        if ($contactId > 0)    $model->loadLeadData($contactId);                              
         return $this->render('head-show-lead', ['model' => $model,]);
    }
    
/*********************************************************/
    
    public function actionNewLead()
    {
        $model    = new OrgContactForm();        
        $request = Yii::$app->request;    
        $contactId = intval($request->get('contactId', 0));
        $openOrgCard = intval($request->get('openOrgCard', 0));
        $model->atsRef= intval($request->get('atsRef', 0));
        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $orgId=$model->orgId; 
                $id = $model->saveLead();                                
                if ( empty($orgId) || $orgId < 0 ) $this->redirect(['site/new-lead', 'contactId' => $id, 'noframe' => 1, 'openOrgCard' => $orgId]);                                                        
                                else $this->redirect(['site/lead-process', 'contactId' => $id, 'noframe' => 1]);      
              return;                                                                    
            }                
        }
        
        if ($contactId > 0)    $model->loadLeadData($contactId);              
        return $this->render('new-lead', ['model' => $model, 'openOrgCard' =>$openOrgCard ]);
    }

    public function actionLeadProcess()
    {
        $model    = new OrgContactForm(); 
        $detailModel    = new LeadDetailForm();        
        $request = Yii::$app->request;    
        $contactId = intval($request->get('contactId', 0));
        $zakazId   = intval($request->get('zakazId', 0));

        if ($contactId == 0 && $zakazId == 0) {            
            $this->redirect(['site/new-lead', 'noframe' => 1]);                                                           
            return;
            }
            
       if ($contactId == 0 && $zakazId != 0) {                
        $contactId = $model->createLeadFromZakaz($zakazId );            
       }     
        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $orgId=$model->orgId; 
                $id = $model->saveLead();                                
                $this->redirect(['site/lead-process', 'contactId' => $id, 'noframe' => 1]);                                                        
            }                
        }
        
        $model->loadLeadData($contactId);              
        if (empty($model->orgId)) {
            $this->redirect(['site/new-lead', 'noframe' => 1, 'contactId' => $contactId, ]);                                                           
            return;
         }
        $detailModel->refContact = $model->contactId;
        $detailText=$detailModel->getDetailText();
        return $this->render('lead-process', ['model' => $model, 'detailText' => $detailText ]);
    }

    
    public function actionLeadQualify()
    {
        $model    = new LeadDetailForm();        
        $request = Yii::$app->request;    
        $model->leadId = intval($request->get('leadId', 0));
        $model->refContact = intval($request->get('refContact', 0));

        if (empty($model->refContact)) {
            echo "Контакт не задан!";
            return;
            }
            
        if(Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $model->saveForm();                                
                $this->redirect(['site/success']);
                return;
                //$this->redirect(['site/lead-qualify', 'leadId' => $model->leadId, 'refContact' => $model->refContact,'noframe' => 1]);                                                        
            }                
        }
        
        $model->loadForm();                      
        return $this->render('lead-qualify', ['model' => $model ]);
    }

        
 public function actionProcessNewLead()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $model = new OrgContactForm();

     //  if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->processLead();
                echo json_encode($sendArray);
                return;
            }   
        }
    }

    
    
 public function actionSaveLeadData()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $model = new OrgContactForm();

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

    
 public function actionGetLeadOrgInfo()
    {   
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $request = Yii::$app->request;    
         $model = new OrgContactForm();
         $model ->orgId = intval($request->get('orgRef', 0)); 

      // if(Yii::$app->request->isAjax)
        {
            {
                $sendArray = $model->getOrgInfoArray();
                echo json_encode($sendArray);
                return;
            }    
        }
    }

    
/************/   
    

    
public function actionSelectEventTime()
{    
        $request = Yii::$app->request;    
        $model=  new EventRegForm();
        $userid = intval($request->get('userid', 0));
        $date = $request->get('date', 0);

        return $this->render('select-event-time', ['model' => $model, 'userid' => $userid, 'date' =>$date]);
//        
}   
/*********************************************************/
/*********************************************************/
    
    public function actionSupport()
    {
        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
        $model = new MailForm();        
        $model->email = "vvmol.nsk@ya.ru";
                         
           if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {    
            $post = Yii::$app->request->post();            
            $success = $model->sendMail();        
            
            //return;
            if ($success)    {$this->redirect(['site/index']); return;}
                      else  {$this->redirect(['site/problem']);return;}
        }                          
     
        return $this->render('support', ['model' => $model]);
    }
    
    
        /*********************************************************/
    
    public function actionMail()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        
        $model = new MailForm();        
        $session = Yii::$app->session;        
        $session->open();
        $listAttached=$session->get('listAttached');                
        if (empty($listAttached)){$listAttached=array();}    
        
        
           if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {    
            $post = Yii::$app->request->post();
            if (array_key_exists ('attach-button', $post))
            {
              $mailParam['email']    = $model->email;
              $mailParam['subject']    = $model->subject;
              $mailParam['body']    = $model->body;
              $mailParam['orgId']    = $model->orgId;          
              $session->set('mailParam', $mailParam);                                        
              $this->redirect(['site/mail-attach']);         
              return;
            }
            
            $listAttached=$session->get('listAttached');
            $model->listAttached =$listAttached;                    
            $success =$model->sendMail();                
            $listAttached=array();            
            $mailParam=array();            
            $session->set('listAttached', $listAttached);            
            $session->set('mailParam', $mailParam);    
            
            if ($success)    {$this->redirect(['site/close']); return;}
                      else  {$this->redirect(['site/problem']);return;}

            $this->redirect(['site/close']);         
            return;
        }                  
        $mailParam=$session->get('mailParam');        
        
        if (Yii::$app->request->isGet) 
        { 
            $request = Yii::$app->request;    

            if (!empty($request->get('email')))
            {
                $listAttached=array();            
                $mailParam=array();            
                $session->set('listAttached', $listAttached);            
                $model->email = $request->get('email');
            }
            if (!empty($request->get('subject')))$model->subject = $request->get('subject');
            if (!empty($request->get('body')))$model->body  = $request->get('body');                    
            if (!empty($request->get('orgId')))    $model->orgId = intval($request->get('orgId'));
        }

        $listAttached=$session->get('listAttached');        
        if (empty($listAttached)){$listAttached=array();}                  
        $model->listAttached =$listAttached;        

        if (!empty($mailParam))
        {
              if (array_key_exists ('email', $mailParam)  ) { $model->email   = $mailParam['email'];  }
              if (array_key_exists ('subject', $mailParam)) { $model->subject = $mailParam['subject'];}
              if (array_key_exists ('body', $mailParam)   ) { $model->body    = $mailParam['body'];     }         
                if (array_key_exists ('orgId', $mailParam)   ) { $model->orgId    = $mailParam['orgId'];     }         

        }    
          return $this->render('mail', ['model' => $model]);
    }

    public function actionMailAttach()
    {        
        $model = new MailAttachForm();
        $session = Yii::$app->session;        
        $session->open();
        $listAttached=$session->get('listAttached');        
        if (empty($listAttached)){$listAttached=array();}                       
                
        if (Yii::$app->request->isPost) {
                        $model->attachFile = UploadedFile::getInstance($model, 'attachFile');

                        
            if ($model->upload()) 
            {
                $listAttached[0]=$model->attachFile->name;
//                array_push ($listAttached, $model->attachFile->name);
                $session->set('listAttached', $listAttached);
                $this->redirect(['site/mail']);         
            }
        }
        else 
        {
            return $this->render('mail-attach', ['model' => $model]);
        }
    }

    
    
    /**********************************************************
    
        Load and parse cvs
    
    ***********************************************************/
    
    public function actionCsvUpload()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
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
//            $this->redirect(['site/csv-uploaded']);         
            $this->redirect(array('site/csv-uploaded','fname' => $fname, 'parsed' => $from)); 
        }
        else
        {
        return $this->render('csv-parse', ['model' => $model,'fname' => $fname, 'from' => $from]);
        }
    }

    
    
    
    /**********************************************************
    
        Load and parse External Data
    
    ***********************************************************/

   public function actionSyncOrders()
    {         
              return $this->render('sync-orders');
    }
    
   /*********************************************************/
    public function actionProcessMail()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
         $model = new MailForm();
         $provider = $model->getProcessMailProvider(Yii::$app->request->get());
         return $this->render('process-mail', ['model' => $model, 'provider' => $provider]);
    }


    public function actionGetMail()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new MailForm();
         $model->requestFIO = $request->get('requestFIO', 0);
         $provider = $model->getMailProvider(Yii::$app->request->get());
         return $this->render('get-mail', ['model' => $model, 'provider' => $provider]);
    }


    public function actionSyncMail()
    {         
            $model = new MailForm();
            $model->getInboxMailList();            
            $model->getSentMailList();    
             //$model->getMailListRaw ();       
           $this->redirect(array('site/get-mail'));         
    }


    public function actionSwitchMailParam()
    {    
        $request = Yii::$app->request;    
        $model=  new MailForm();                
        $id   = intval($request->get('id', 0 ));
        $paramType   = $request->get('paramType', 'none' );
        $sendArray = $model->switchMailParam($id, $paramType);

        if(Yii::$app->request->isAjax)
        {
                echo json_encode($sendArray);
                return;
        }
      
       $this->redirect(['site/success']);            
    }   
   /*********************************************************/
    public function actionPhoneBook()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new PhoneForm();         
         $provider = $model->getPhoneBookProvider(Yii::$app->request->get());
         return $this->render('phone-book', ['model' => $model, 'provider' => $provider]);
    }

    public function actionPhoneSelect()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new PhoneForm();         
         $model->refOrg=intval($request->get('refOrg', 0 ));
         $provider = $model->getPhoneBookProvider(Yii::$app->request->get());
         return $this->render('phone-select', ['model' => $model, 'provider' => $provider]);
    }

    public function actionGetPhoneDetail()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new PhoneForm();         
         $id=intval($request->get('id', 0 ));
                 //if(Yii::$app->request->isAjax)
        {

                $sendArray = $model->getPhoneDetail($id);
                echo json_encode($sendArray);
                exit(0);
                return;
        }

    }

    public function actionRmPhone()
    {    
        $request = Yii::$app->request;    
        $model=  new PhoneForm();                
        $id   = intval($request->get('id', 0 ));
        $model->rmPhone($id);
        
        $this->redirect(['site/success']);            
    }   

   /*********************************************************/
    
    public function actionAdressSelect()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new AdressForm();         
         $model->refOrg=intval($request->get('refOrg', 0 ));
         $provider = $model->getAdressBookProvider(Yii::$app->request->get());
         return $this->render('adress-select', ['model' => $model, 'provider' => $provider]);
    }

    public function actionGetAdressDetail()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new AdressForm();         
         $id=intval($request->get('id', 0 ));
                 //if(Yii::$app->request->isAjax)
        {

                $sendArray = $model->getAdressDetail($id);
                echo json_encode($sendArray);
                exit(0);
                return;
        }

    }
    
    
   /*********************************************************/
    public function actionEmailBook()
    {         
    if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
        $request = Yii::$app->request;

         $model = new EmailForm();         
         $provider = $model->getEmailBookProvider(Yii::$app->request->get());
         return $this->render('email-book', ['model' => $model, 'provider' => $provider]);
    }

    public function actionRmEmail()
    {    
        $request = Yii::$app->request;    
        $model=  new EmailForm();                
        $id   = intval($request->get('id', 0 ));
        $model->rmEmail($id);
        
        $this->redirect(['site/success']);            
    }       

   
    
    
/*******************************************************************************/   
/*******************************************************************************/                     
}
