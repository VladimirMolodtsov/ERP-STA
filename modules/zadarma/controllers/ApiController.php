<?php

namespace app\modules\zadarma\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\zadarma\models\ZadarmaMainForm;
use app\modules\zadarma\models\ZadarmaAtsState;
use app\modules\zadarma\models\ZadarmaStatForm;
use app\modules\zadarma\models\TblAts;
/**
 * Default controller for the `zadarma` module
 * Operations with internet telephon zadarma.com
 */
class ApiController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        $model = new ZadarmaMainForm();                    
        return $this->render('index', ['model' => $model ] );
    }


    public function actionBalance()
    {

        $model = new ZadarmaMainForm();                    
        print_r ($model->getBalance());
        return;
    }

    public function actionGetStat()
    {
        $request = Yii::$app->request; 
        $date = $request->get('date', date('Y-m-d'));
        $model = new ZadarmaStatForm();                    
        
        
        return $this->render('stat-form', ['model' => $model, 'date' => $date ] );
    }


    public function actionGetRecord()
    {
        
        $request = Yii::$app->request; 
        $callId = $request->get('callId', null);
        $pbxCallId = $request->get('pbxCallId', null);
        $model = new ZadarmaMainForm();                    
        
        $answerObject=$model->getRecord($callId, $pbxCallId);
        
        return $this->render('get-record', ['answerObject' => $answerObject, 'noframe' => 1] );
        
    }


 
    public function actionRegisterLog()
    {       
       $model = new ZadarmaAtsState();                      
       $request = Yii::$app->request; 
       $id = intval($request->get('id', 0));
       if (empty($id)) {
           echo "Not valid id";
           return;
       }
       
        $record= TblAts::findOne($id);
        if (empty($record)) {
           echo "record $id is not find";
           return;
        }

        echo "<pre>";         
         print_r($record->data);
        echo "</pre>";         

        $model->registerLog($id); 
        return;
    }
 
    
/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    
}
