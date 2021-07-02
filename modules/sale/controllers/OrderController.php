<?php

namespace app\modules\sale\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//use yii\web\UploadedFile;

use app\modules\sale\models\OrderForm;

/**
 * Controller - Заказы клиента через интернет
 */
class OrderController extends Controller
{

   /* public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => '@app/views/site/custom-error-view.php'
            ],
        ];
    }
    */

    /**
     * Renders the index view 
     * Вызов по умролчанию переадресует на форму заполнения заказа
     * @return string
     */
    public function actionIndex()
    {
        $this->redirect(['sale/order/new-order']); 
    }


    /**
     * Renders the index view 
     * Стартовая форма заполнения заказа
     * @return string
     */    
    public function actionNewOrder()
    {
         $model = new OrderForm();
         $provider = $model->getWarePriceProvider(Yii::$app->request->get());         
         return $this->render('new-order', ['model' => $model, 'provider' => $provider]);
    }
    
    
    public function actionGetOrg()
    {
    
        $model = new OrderForm();
        $request = Yii::$app->request;    
        $email = $request->get('email','');
        //if(Yii::$app->request->isAjax)

         if(!empty($email)){
           $sendArray = $model->getOrgByEmail($email);
           echo json_encode($sendArray);
           return;
         }
         else {
         return json_encode(['N' =>0]);
         
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
