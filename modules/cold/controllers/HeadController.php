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
use app\modules\cold\models\ColdLoadedForm;

/**
 * Head controller for the `cold` module
 */
class HeadController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        $model = new ColdLoadedForm();					
		$provider = $model->getColdHeadersListProvider(Yii::$app->request->get());
		return $this->render('index', ['model' => $model, 'provider' => $provider ] );
    }
 /*******************************************/   
   
    public function actionLoadByUrl()
    {
        $model = new ColdImportData();     
       
       if ($model->load(Yii::$app->request->post()) && $model->validate()) {
       /*сначала файл*/
            $model->csvFile = UploadedFile::getInstance($model, 'csvFile');            
            $uploadPath=__DIR__."/../uploads/";            

            
          if (!empty($model->csvFile))
          { 
             if ($model->upload()) 
             {
                $fname = $uploadPath.$model->csvFile->name;
                $model->loadClientFromFile($fname);
                unlink($fname);
             }

             $this->redirect(['index']); 
             return;
          } 
        /*если нет то по ссылке*/
        else
        {
                $model->loadClientFromUrl();                           
                $this->redirect(['index']);			
                return;        
        }
        
        }

        return $this->render('load-by-url', ['model' => $model]);
        
    }
 /*******************************************/   

    public function actionDetailLoad()
    {
        $request = Yii::$app->request;	        
        $model = new ColdLoadedForm();	
		$id = $request->get('id');
		$model->orgHeaderRef = intval($id);        
		$provider = $model->getColdContentProvider(Yii::$app->request->get());
		return $this->render('detail-load', ['model' => $model, 'provider' => $provider ] );
    }
 /*******************************************/   

    
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    
}
