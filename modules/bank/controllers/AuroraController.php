<?php

namespace app\modules\bank\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\bank\models\AuroraExtract;

/**
 * Default controller for the `bank` module
 */
class AuroraController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
                
       $this->redirect(['extract-list']); 
    }
 
 /*******************************************/
/********* Aurora **************************/
/*******************************************/
    public function actionAuroraLoadExtract()
    {
        $model = new AuroraExtract();     
        $model -> getExtractAttach();        
        return $this->render('success');
    }
            
    public function actionAuroraGetExtract()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        $model = new AuroraExtract();     
        
        $request = Yii::$app->request;        
        
        $sd = $request->get('sd', date("Y-m-d") );
        $ed = $request->get('ed', date("Y-m-d") );
        $out = $model -> getExtractData($sd, $ed);        
        
        \Yii::$app->response->data = $out;
        /*echo $out;*/
        Yii::$app->end();
        return;
    }
 /*******************************************/   
            
    
    public function actionExtractList()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new AuroraExtract();     

        $provider=$model->getExtractListProvider(Yii::$app->request->get());      
        return $this->render('extract-list', ['model' => $model, 'provider' => $provider]);        
    }
 /*******************************************/       
    
    public function actionShowExtract()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $model = new AuroraExtract();     
        
        $request = Yii::$app->request;          
        $model->extractRef = intval($request->get('id',0));
            
        $provider=$model->getExtractDetailProvider(Yii::$app->request->get()); 
        
        //return;     
        return $this->render('extract-detail', ['model' => $model, 'provider' => $provider]);        
    }

    /*******************************************/                          


    public function actionSuccess()
    {
        return $this->render('success');
    }
}