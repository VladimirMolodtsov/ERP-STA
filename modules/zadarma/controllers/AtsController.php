<?php

namespace app\modules\zadarma\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\modules\zadarma\models\AtsStatForm;

/**
 * Default controller for the `zadarma` module
 * Operations with internet telephon zadarma.com
 */
class AtsController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
         $this->redirect(['show-log']);
    }



    public function actionShowLog()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        
        $request = Yii::$app->request; 
        $model = new AtsStatForm();                    
        
        $model->fltDate=$request->get('fltDate',date('Y-m-d'));
        $model->fltDetail=intval($request->get('fltDetail',0));
        
        $provider = $model->getPhoneStatisticsProvider(Yii::$app->request->get());
        $orgProvider = $model->getOrgListProvider(Yii::$app->request->get());    
        return $this->render('show-log', ['model' => $model, 'provider' => $provider, 'orgProvider' =>$orgProvider]);
          
    }

    public function actionSelectContact()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        
        $request = Yii::$app->request; 
        $model = new AtsStatForm();                    
        $model ->orgRef =  $request->get('orgRef',0);
        $provider = $model->getContactListProvider(Yii::$app->request->get());
        return $this->render('select-contact', ['model' => $model, 'provider' => $provider]);
          
    }

    
    
    public function actionAtsCalendar()
    {
        if (Yii::$app->user->isGuest) { $this->redirect(['/site/index']);  return; }
        
        $request = Yii::$app->request; 
        $model = new AtsStatForm();                    
        
        $month = $request->get('month',date('n'));
        $year = $request->get('year',date('Y'));        
           
        return $this->render('ats-calendar', ['model' => $model, 'month' => $month, 'year' => $year  ]);                       
          
    }

    public function actionSetOrg()
    {
        
        $request = Yii::$app->request; 
        $model = new AtsStatForm();                    
        $id=intval($request->get('id',0));
        $orgRef=intval($request->get('orgRef',0));
        
        $model->setOrgToRecord($id, $orgRef);
        
         $this->redirect(['success']);
          
    }

    public function actionSetContact()
    {

        $request = Yii::$app->request;
        $model = new AtsStatForm();
        $id=intval($request->get('id',0));
        $contactRef=intval($request->get('contactRef',0));

        $sendArray=$model->setContactToRecord($id, $contactRef);

        if(Yii::$app->request->isAjax)
        {
         echo json_encode($sendArray);
         return;
        }

         $this->redirect(['success']);

    }




/*******************************************/
/********* Service  ************************/
/*******************************************/

    public function actionSuccess()
    {
        return $this->render('success');
    }

    
}
