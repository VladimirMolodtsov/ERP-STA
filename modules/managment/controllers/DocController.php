<?php

namespace app\modules\managment\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

use app\modules\managment\models\DocTypeCfgForm;



/**
 * Finance controller for the `head` module
 */
class DocController extends Controller
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
 /* Тип документов */
    public function actionDocTypeCfg()
    {

        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }         
                
        $model = new DocTypeCfgForm();
            
        $typeProvider        = $model->getDocTypeProvider(Yii::$app->request->get());
        $operationProvider   = $model->getDocOperationProvider(Yii::$app->request->get());                
                        
        return $this->render('doc-type-cfg', 
        [
        'model'            =>  $model, 
        'typeProvider'      => $typeProvider,
        'operationProvider' => $operationProvider
        ]);
        
        
    }
 /*******************************************/
    public function actionSaveDocData()
    {
        $model = new DocTypeCfgForm();
        if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveData();    
                echo json_encode($sendArray);
                return;
            }    
        }        
    }
 /*******************************************/
    public function actionAddDocType()
    {
        $model = new DocTypeCfgForm();
        $model->addNewDocType();    
        $this->redirect(['/managment/doc/doc-type-cfg']);  return;    
    }
    public function actionAddDocOperation()
    {
        $model = new DocTypeCfgForm();
        $model->addNewDocOperation();    
        $this->redirect(['/managment/doc/doc-type-cfg']);  return;    
    }
    public function actionRmDocType()
    {
        $request    = Yii::$app->request;    
        $id  = intval($request->get('id',0)); 

        $model = new DocTypeCfgForm();
        $model->rmDocType($id);    
        $this->redirect(['/managment/doc/doc-type-cfg']);  return;    
    }
    public function actionRmDocOperation()
    {
        $request    = Yii::$app->request;    
        $id  = intval($request->get('id',0)); 
        
        $model = new DocTypeCfgForm();
        $model->rmDocOperation($id);    
        $this->redirect(['/managment/doc/doc-type-cfg']);  return;    
    }

    
  }  
    
