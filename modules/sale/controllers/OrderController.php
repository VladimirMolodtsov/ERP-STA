<?php

namespace app\modules\sale\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//use yii\web\UploadedFile;

use app\modules\sale\models\OrderForm;

use app\models\ExportToWord;

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
        $session = Yii::$app->session;
        $session->open();

         $model = new OrderForm();
         $request = Yii::$app->request;    
         $model->id = $request->get('id',0);
         $model->email = $session->get('saleOrderEmail', '');

         $provider = $model->getWarePriceProvider(Yii::$app->request->get());         
         return $this->render('new-order', ['model' => $model, 'provider' => $provider]);
    }
    
    /**
     * Получить данные об организации через Аякс
     * $email  - почта
     * $orgId  - идентификатор
     * @return JSON массив с данными
     */    
    public function actionGetOrg()
    {
    
        $model = new OrderForm();
        $request = Yii::$app->request;    
        $email = $request->get('email','');
        $orgId = intval($request->get('orgId',0));
        $session = Yii::$app->session;
        $session->open();
        //if(Yii::$app->request->isAjax)

         if(!empty($email)){
           $session->set('saleOrderEmail', $email);
           $sendArray = $model->getOrgByEmail($email);
           echo json_encode($sendArray);
           return;
         }
         
         if(!empty($orgId)){
           $sendArray = $model->getOrgById($email, $orgId);
           echo json_encode($sendArray);
           return;
         }
         
         return json_encode(['N' =>0]);         
    }
    
       
    /**
     * Изменить данные о заказе через Аякс
     * $email  - почта
     * $orgId  - идентификатор
     * @return JSON массив с результатом изменения
     */    
    public function actionSaveOrderDetail()
    {
        $model = new OrderForm();
//         if(Yii::$app->request->isAjax)
        {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) 
            {
                $sendArray = $model->saveOrderDetail();
                echo json_encode($sendArray);
                return;
            }    
        }
    }


    /**
     * Сформировать коммерческое предложение
     * @return
     */
   public function actionGetOrder()
    {
        $model = new OrderForm();
        $request = Yii::$app->request;
        $model->id = $request->get('id', 0);
        $model->email = $request->get('email','');
        
        $css= $model->getOrderCss();
        $html = $model->prepareOrderDoc();


        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
        $fname = 'order_'.\Yii::$app->security->generateRandomString(5).".doc";

        $mask = realpath(dirname(__FILE__))."/../uploads/order_"."*.doc";
        array_map("unlink", glob($mask));

        $filePath = $uploadPath.$fname;
        ExportToWord::htmlToDoc($html, $css, $filePath, 'UTF-8', 0);

        
        //$model->subject =iconv("UTF-8", "Windows-1251","Коммерческое предложение");
        //$model->body =iconv("UTF-8", "Windows-1251","Заказанное Вами коммерческое предложение прикреплено к письму.");

        $model->subject ="Коммерческое предложение";
        $model->body ="Заказанное Вами коммерческое предложение прикреплено к письму.";
                
        $model->html = $html;
        $model->attachDoc = $filePath;
        $model->sendMail();
        
        return $this->render('get-order', ['model' => $model, 'html' => $html, ]);         
    }

   public function actionDownloadOrder()
    {
        $model = new OrderForm();
        $request = Yii::$app->request;
        $model->id = $request->get('id', 0);

        $css= $model->getOrderCss();
        $html = $model->prepareOrderDoc();


        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
        $fname = 'order_'.\Yii::$app->security->generateRandomString();

        $mask = realpath(dirname(__FILE__))."/../uploads/order_"."*.doc";
        array_map("unlink", glob($mask));

        $filePath = $uploadPath.$fname;
        ExportToWord::htmlToDoc($html, $css, $filePath, 'UTF-8', 0);
        $url = Yii::$app->request->baseUrl."/../uploads/".$fname;

        $this->redirect(['/site/download', 'url' => $url]);
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
