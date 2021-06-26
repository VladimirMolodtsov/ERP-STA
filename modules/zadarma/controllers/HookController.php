<?php

namespace app\modules\zadarma\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\zadarma\models\ZadarmaAtsState;
use app\modules\zadarma\models\TblAts;
/**
 * Default controller for the `zadarma` module
 * Operations with internet telephon zadarma.com
 */
class HookController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
  public $enableCsrfValidation = false;
 /*******************************************/   
/* мониторинг АТС - сюда сбрасываются события*/
    public function actionWebHook()
    {
       /*Проверка от атс*/
       if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']); 

         if (Yii::$app->request->isPost) {
         
         $record= new TblAts();
         $record->data=  print_r($_POST, true); 
         $record->save();
       // echo "here";     
          
          $model = new ZadarmaAtsState(); 
          $model->refRaw = $record->id;       
          $model->savePost($_POST);                      
    
       //print_r($model);         
       //echo $model->event;
    
    
         }
       
 
      exit(); 
    }

    
}
