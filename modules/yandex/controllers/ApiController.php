<?php

namespace app\modules\yandex\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\yandex\models\DiskApi;
use app\modules\yandex\models\ChkDisk;

/**
 * Default controller for the `yandex` module
 * Operations with storage
 */
class ApiController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */

     
    public function actionIndex()
    {
      

    }


    public function actionGetChkDisk()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
        /*$model = new DiskApi();             
        $r=$model ->moveFile('/UPLOAD/123.jpg','/DOCUMENTS/2021/02/123.jpg');*/
        echo "<pre>";
        
        $model = new ChkDisk();
        $sendArray = $model->chkUpload();
         echo json_encode($sendArray);
        return;         
    }
    

    public function actionChkDisk()
    {
    if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        $request = Yii::$app->request; 
      
        $model = new ChkDisk();
        return $this->render('chk-disk', ['model' => $model, ]);        
    }

    
/******************************/    

    public function actionShareFile()
    {
        $model = new DiskApi(); 
        $request = Yii::$app->request;
        $path=$request->get('path', '/');
        $path ='/DOCUMENTS/2021/02/test.jpg';
        //$sendArray = $model->getFileShare($path);   
        $sendArray = $model->publicFile($path, 1);
        echo "<pre>";
        print_r($sendArray);  
        echo "</pre>";
        //echo json_encode($sendArray);  
        return;         
    }
//     


/******************************/    
    public function actionGetFile()
    {
        $model = new DiskApi(); 
        $request = Yii::$app->request;        
        $path=$request->get('path', '/');
        $path ='/DOCUMENTS/2021/02/test.jpg';
        $sendArray = $model->getFile($path); 
        
        echo "<pre>";
        print_r($sendArray);  
        echo "</pre>";
        return;         
        echo json_encode($sendArray);  
        return;         
    }
    
/******************************/    

    public function actionGetUri()
    {
        $model = new DiskApi(); 
        $request = Yii::$app->request;
        $path=$request->get('path', '/');
        
        $sendArray = $model->getFileUri($path);   
        echo json_encode($sendArray);  
        return;         
    }
    
/******************************/    
            
    public function actionGetInfo()
    {
     $model = new DiskApi();   
        $res = $model->getInfo();   
     echo "<pre>";
        print_r ($res);     
     echo "</pre>";
    }

    public function actionGetFolderContent()
    {
     $model = new DiskApi();   
        $res = $model->getFolderContent('/');   
     echo "<pre>";
        print_r ($res);     
     echo "</pre>";
    }

    public function actionCreateFolder()
    {
     $model = new DiskApi();   
        $res = $model->createFolder('/test');   
     echo "<pre>";
        print_r ($res);     
     echo "</pre>";
    }

    public function actionPlaceFile()
    {
        $model = new DiskApi(); 
        $srcPath=(realpath(dirname(__FILE__)))."/../uploads/";    
        $fname= $srcPath."vip.xlsx";
    
        $res = $model->placeFile($fname, '/test');   
     echo "<pre>";
        print_r ($res);     
     echo "</pre>";
    }



    
/*******************************************/
/********* Service  ************************/
/*******************************************/


    
}
