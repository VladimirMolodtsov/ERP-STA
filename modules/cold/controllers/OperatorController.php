<?php

namespace app\modules\cold\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\cold\models\ColdMainForm;
use app\modules\cold\models\ColdImportData;
use app\modules\cold\models\ColdNewContactForm;
use app\modules\cold\models\ColdInitForm;
use app\modules\cold\models\ColdNeedForm;


/**
 * Default controller for the `cold` module
 */
class OperatorController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        if (Yii::$app->user->isGuest)
        {
            $this->redirect(['site/index']);         
        }
        
        $model = new ColdMainForm();					
		$provider = $model->getColdListProvider(Yii::$app->request->get());
		return $this->render('index', ['model' => $model, 'provider' => $provider ] );
    }
 /*******************************************/   
    
    public function actionLoadByUrl()
    {
        $model = new ColdImportData();     
        
       
       if (Yii::$app->request->isPost) {
       /*сначала файл*/
            $model->csvFile = UploadedFile::getInstance($model, 'xlsxFile');            
            if (!empty ($model->csvFile))
            {
            $uploadPath=__DIR__."/../uploads/";            
            echo "$uploadPath"; 
            return;
             if ($model->upload()) 
             {
                
                $fname = $uploadPath.$model->csvFile->name;
                $model->loadClientFromFile($fname);
                $this->redirect(['load-bank']); 
                return;
             }
            } 
            
        /*если нет то по ссылке*/
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            
                $model->loadClientFromUrl();                           
                $this->redirect(['index']);			
                return;        
        }
        
        }

        return $this->render('load-by-url', ['model' => $model]);
        
    }
/*******************************************/
    
    public function actionColdNew()
    {
		 $model = new ColdNewContactForm();		 
		 $request = Yii::$app->request;	
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
           $model->saveData();		   		   
		   $this->redirect(['success']);		   
		   return;
		 }
         else 
		 {		 		 
 		   return $this->render('cold-new', ['model' => $model]);
		 }
    }
    
/*******************************************/
    
    public function actionColdInit()
    {
		$model = new ColdInitForm();		 
		$request = Yii::$app->request;	
		 
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
           $model->saveData();		   		   
		   $this->redirect(['success']);		   
		   return;
		 }
         else 
		 {		 		 
		 $id = $request->get('id',0);
		 $model->id = intval($id);
 		 return $this->render('cold-init', ['model' => $model]);
		 }
    }


/*******************************************/
    public function actionColdNeed()
    {
         $model = new ColdNeedForm();
		 $request = Yii::$app->request;	
		 
 		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
           $model->saveData();		   
		   $this->redirect(['success']); 		   
		   return;
		 }
         else 
		 {		 		 
		 $id = $request->get('id');
		 $model->id = intval($id);		 
 		 return $this->render('cold-need', ['model' => $model]);
		 }
    }
    
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    
}
