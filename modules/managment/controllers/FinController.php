<?php

namespace app\modules\managment\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\managment\models\FinControlForm;
use app\modules\bank\models\BuhStatistics;


/**
 * Finance controller for the `managment` module
 */
class FinController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
     if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $curUser=Yii::$app->user->identity; 
        
        $this->redirect(['/managment/fin/fin-control']);  return;         
    }
 
 
 /*******************************************/   
 /* Контроль финансовых данных */
    public function actionFinControl()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinControlForm();
        $model->controlTime= $request->get('controlTime',0); //на когда            
        $showDate= $request->get('showDate',date('Y-m-d'));
        if (empty($model->controlTime))$model->controlTime=strtotime($showDate); 
            
        $provider    = $model->getFinControlProvider(Yii::$app->request->get());        
                        
        return $this->render('fin-control', ['model' => $model, 'provider' =>$provider]);
    }

    
/*******************************************/    

    public function actionSaveControlData()    
    {
        $model = new FinControlForm();
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveControlData ();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }
/*******************************************/       
   public function actionControlRowAdd()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $model = new FinControlForm();        
        $model ->addNewControlRow();        
        $this->redirect(['success']);
        return;
    }
  /******************/  
     public function actionFinControlCfg()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
                
        $request    = Yii::$app->request;    
        $model      = new FinControlForm();        
        $model->id  = intval($request->get('id',0)); 
        $controlTime= $request->get('controlTime', time() );
        
        $buhmodel   = new BuhStatistics();
        $buhmodel->dtstart = date("Y-m-d", $controlTime);
        
        $model->loadData();
        
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveData();         
                if ($res == false)    {$this->redirect(['problem']);return;} // ошибка сохранения 
                $this->redirect(['success']);
                return; // успешно завершена работа 
         }
        $provider    = $model->getFinControlUtCfgProvider(Yii::$app->request->get());
        $buhProvider = $buhmodel->getBuhStatProvider     (Yii::$app->request->get());
        $docProvider = $model->getDocProvider     (Yii::$app->request->get());

        return $this->render('fin-control-cfg', ['model' => $model, 'buhmodel' => $buhmodel, 'docProvider' => $docProvider,
                                                 'provider' =>$provider, 'buhProvider' => $buhProvider, 'controlTime' => $controlTime]);
    }
 /*******************************************/    
 /*******************************************/
 /***** Сохраняем настройки по финансовому контролю - столбец документов **/
    public function actionSaveCfgData()    
    {
        $model = new FinControlForm();
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveCfgData ();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }

/*******************************************/    
/******************/
     public function actionFinUtCfg()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
                
        $request    = Yii::$app->request;    
        $model      = new FinControlForm();        
        $model->id  = intval($request->get('id',0)); 
        $controlTime= $request->get('controlTime', time() );
        
        $buhmodel   = new BuhStatistics();
        $buhmodel->dtstart = date("Y-m-d", $controlTime);
        
        $model->loadData();
        
         if ($model->load(Yii::$app->request->post()) && $model->validate()) 
         {
           $res = $model->saveData();         
                if ($res == false)    {$this->redirect(['problem']);return;} // ошибка сохранения 
                $this->redirect(['success']);
                return; // успешно завершена работа 
         }
        $provider    = $model->getFinControlUtCfgProvider(Yii::$app->request->get());
        $buhProvider = $buhmodel->getBuhStatProvider     (Yii::$app->request->get());

        return $this->render('fin-ut-cfg', ['model' => $model, 'buhmodel' => $buhmodel,
                                                 'provider' =>$provider, 'buhProvider' => $buhProvider]);
    }
    
    public function actionAddBuhRow()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $rowRef     = intval($request->get('rowRef',0));
        $model = new FinControlForm();        
        $model ->addBuhRow($rowRef );        
        $this->redirect(['success']);
        return;
    }


    public function actionSetDt()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $id     = intval($request->get('id',0));
        $accdt  = $request->get('accdt',0);
        $model = new FinControlForm();        
        $model ->setDt($id, $accdt);             
        $this->redirect(['success']);
        return;
    }

        
    public function actionSetKt()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $id     = intval($request->get('id',0));
        $acckt  = $request->get('acckt',0);
        $model = new FinControlForm();        
        $model ->setKt($id, $acckt);        
        $this->redirect(['success']);
        return;
    }

    public function actionSetDiv()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $id     = intval($request->get('id',0));
        $div  = intval($request->get('div',0));
        $model = new FinControlForm();        
        $model ->setDiv($id, $div);        
        $this->redirect(['success']);
        return;
    }
    
    public function actionSetNote()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $id     = intval($request->get('id',0));
        $note   = $request->get('note',0);
        $model = new FinControlForm();        
        $model ->setNote($id, $note);        
        $this->redirect(['success']);
        return;
    }
 
    public function actionAddStatRow()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        
        $request = Yii::$app->request;  
        $rowRef     = intval($request->get('rowRef',0));
        $statRow    = intval($request->get('statRow',0));
        $div        = intval($request->get('div',0));
        $isPrev     = intval($request->get('isPrev',0));
        
        $model = new FinControlForm();        
        $res= $model ->addStatRow($rowRef, $statRow, $div, $isPrev );        
        if ($res) {$this->redirect(['success']);    return;}
        $this->redirect(['problem']);
    }

/*******************************************/        
    public function actionSyncControl()
    {
       if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
        $request        = Yii::$app->request;  
        $controlTime    = intval($request->get('controlTime',time()));
        $model = new FinControlForm();        
        $model->syncControl($controlTime);
        /*echo "<pre>";
        print_r ($model->debug);
        echo "</pre>";*/
        $this->redirect(['fin-control', 'controlTime' => $controlTime]);
    }       

    
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    public function actionClose()
    {
        return $this->render('close');
    }
    
}
