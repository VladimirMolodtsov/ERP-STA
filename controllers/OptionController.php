<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

use app\models\CfgForm;

class OptionController extends Controller
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
         $this->redirect(['site/index']);
    }

/*******************************************************************************/   
/*******************************************************************************/                     
    
    public function actionLeadConfig()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['site/index']);  return; }
   
         $model = new CfgForm();         
         $request =Yii::$app->request;
         
        $provider = $model->getLeadParamProvider(Yii::$app->request->get());        
        return $this->render('lead-config', ['model' => $model, 'provider' => $provider]);
    }    
    
     public function actionSaveConfig()
    {    
        $request = Yii::$app->request;    
        $model=  new CfgForm();                
       

        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveData();
                echo json_encode($sendArray);
                return;
            }    
       }
      
       $this->redirect(['site/success']);            
    }   
        
    
/*******************************************************************************/   
/*******************************************************************************/                     
}
