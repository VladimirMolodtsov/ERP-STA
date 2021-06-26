<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\WarehouseForm;
use app\modules\bank\models\BankDispetcher;
use app\modules\bank\models\PlatDispetcher;
use app\modules\bank\models\DocDispetcher;
use app\modules\bank\models\ShipDispetcher;
use app\modules\bank\models\BuhDispetcher;

use app\modules\tasks\models\TaskTiming;

/**
 * DispetcherForm - модель основной формы диспетчера
 * ( отоброжение прогресса исполнения задач )
 */
 
 class DispetcherForm extends Model
{
    
    public $debug;
    
    
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
        ];
    }

  /**************************************/      
  public function getBuhState()
  {
    $buhModel = new BuhDispetcher();     
    $buhModel->borderArray = TaskTiming::getTiming(7);
    
    $buhModel->showDate = strtotime(date("Y-m-d"));
    $taskArray[] = $buhModel->loadTaskList();
    
   /* echo "<pre>";
    print_r ($taskArray);
    print_r ($buhModel->taskArray);
    
    echo "</pre>";
    return;*/

    /*определим текущий получас */    
    for ($i=0; $i<48; $i++ )
    {
        $h = intval(($i+1)/2);
        $m= 60*(($i+1)/2-$h);

        $hp = intval(($i)/2);
        $mp= 60*(($i)/2-$hp);
 
        $period = sprintf('%02d', $hp).":".sprintf('%02d', $mp)."-".sprintf('%02d', $h).":".sprintf('%02d', $m);
       if ($buhModel->taskArray[$i] > 2) 
       {
         return [
                  'status' => $buhModel->taskArray[$i],
                  'name'   => $buhModel->nameArray[$i],
                  'period' => $period
                ];
       }   



       if ($buhModel->taskArray[$i] == 1) 
       {
         return [
                  'status' => $buhModel->taskArray[$i],
                  'name'   => $buhModel->nameArray[$i],
                  'period' => $period
                ];
       }   


       
    }
    
         return [
                  'status' => "",
                  'name'   => "",
                  'period' => ""
                ];

  }
  
  
  /**************************************/      
  
  public function getBankState()
  {
    $bankModel = new BankDispetcher();     
    $bankModel->borderArray = TaskTiming::getTiming(2);
    $bankModel->showDate = strtotime(date("Y-m-d"));
    $bankModel->loadTaskList();

  
        /*определим текущий получас */    
   // $shift = time()-$this->showDate;
   // $curpos= intval($shift/1800)+8+1;// в половинках часа
    for ($i=0; $i<48; $i++ )
    {
        $h = intval(($i+1)/2);
        $m= 60*(($i+1)/2-$h);

        $hp = intval(($i)/2);
        $mp= 60*(($i)/2-$hp);
 
        $period = sprintf('%02d', $hp).":".sprintf('%02d', $mp)."-".sprintf('%02d', $h).":".sprintf('%02d', $m);
       if ($bankModel->taskArray[$i] > 2) 
       {
         return [
                  'status' => $bankModel->taskArray[$i],
                  'name'   => $bankModel->nameArray[$i],
                  'period' => $period
                ];
       }   



       if ($bankModel->taskArray[$i] == 1) 
       {
         return [
                  'status' => $bankModel->taskArray[$i],
                  'name'   => $bankModel->nameArray[$i],
                  'period' => $period
                ];
       }   


       
    }
    
         return [
                  'status' => "",
                  'name'   => "",
                  'period' => ""
                ];

  }
  
  
  /**************************************/      
  public function getDocState()
  {
    $docModel = new DocDispetcher();     
    $docModel->borderArray = TaskTiming::getTiming(4);
    $docModel->showDate = strtotime(date("Y-m-d"));
    $docModel->loadTaskList();
    
    $platModel = new PlatDispetcher();     
    $platModel->borderArray = TaskTiming::getTiming(5);
    $platModel->showDate = strtotime(date("Y-m-d"));
    $platModel->loadTaskList();

    $shipModel = new ShipDispetcher();     
    $shipModel->borderArray = TaskTiming::getTiming(6);
    $shipModel->showDate = strtotime(date("Y-m-d"));
    $shipModel->loadTaskList();
 
        /*определим текущий получас */    
   // $shift = time()-$this->showDate;
   // $curpos= intval($shift/1800)+8+1;// в половинках часа
    for ($i=0; $i<48; $i++ )
    {
        $h = intval(($i+1)/2);
        $m= 60*(($i+1)/2-$h);

        $hp = intval(($i)/2);
        $mp= 60*(($i)/2-$hp);
 
        $period = sprintf('%02d', $hp).":".sprintf('%02d', $mp)."-".sprintf('%02d', $h).":".sprintf('%02d', $m);
       if ($docModel->taskArray[$i] > 2) 
       {
         return [
                  'status' => $docModel->taskArray[$i],
                  'name'   => $docModel->nameArray[$i],
                  'period' => $period
                ];
       }   

       if ($platModel->taskArray[$i] > 2) 
       {
         return [
                  'status' => $platModel->taskArray[$i],
                  'name'   => $platModel->nameArray[$i],
                  'period' => $period
                ];
       }   

       if ($shipModel->taskArray[$i] > 2) 
       {
         return [
                  'status' => $shipModel->taskArray[$i],
                  'name'   => $shipModel->nameArray[$i],
                  'period' => $period
                ];
       }   


       if ($docModel->taskArray[$i] == 1) 
       {
         return [
                  'status' => $docModel->taskArray[$i],
                  'name'   => $docModel->nameArray[$i],
                  'period' => $period
                ];
       }


       if ($platModel->taskArray[$i] == 1) 
       {
         return [
                  'status' => $platModel->taskArray[$i],
                  'name'   => $platModel->nameArray[$i],
                  'period' => $period
                ];
       }   

       if ($shipModel->taskArray[$i] == 1) 
       {
         return [
                  'status' => $shipModel->taskArray[$i],
                  'name'   => $shipModel->nameArray[$i],
                  'period' => $period
                ];
       }   
       
              
       
    }
    
             return [
                  'status' => "",
                  'name'   => "",
                  'period' => ""
                ];
    
  }
  
  
  
  public function getPurchaseState()
  {
   
   $wareModel = new WarehouseForm();     
   $leafValue['storeStatus'] = $wareModel->getStoreFullnes();

   /*Остаток на складе*/
   $strCount = "Select sum({{%warehouse}}.[[amount]] * {{%warehouse}}.price) from {{%warehouse}}";         
   $leafValue['amount'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

      
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where {{%purchase_zakaz}}.status = 1 AND isActive =1 ";            
   $leafValue['requestInSogl']  =Yii::$app->db->createCommand($strCount )->queryScalar();             

   $countquery  = new Query();
   $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;        
   $countquery->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
   $leafValue['purchaseInSogl']  = $countquery->createCommand()->queryScalar();
   
    return $leafValue;
   
  }
  
  
   
  /*************************/   
  /* Состояние отдела продаж см HeadForm getStats*/
   public function getMarketState()
   {
       //текущий
        $y=date('Y');
        $m=date('m');
        $d=date('d');
       
       //предыдущий 
        $py = $y;
        $pm = $m -1;
        if ($pm <= 0) {
            $pm=12;
            $py = $y-1;
        }
        
        
        $stats= array();              
        
        /*Активность */
               
        $stats['d_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where   
                                                              year(formDate)=:y And month(formDate)=:m And day(formDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        $stats['d_schet'] =  Yii::$app->db->createCommand('SELECT count(id)   from {{%schet}} where 
                                                              year(schetDate)=:y And month(schetDate)=:m And day(schetDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       

               
       $stats['d_activity'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where   
         year(contactDate)=:y And month(contactDate)=:m And day(contactDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
       $stats['d_activity'] +=  $stats['d_zakaz']+ $stats['d_schet'];
       $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  count(id) from  {{%oplata}} 
        where  year(oplateDate)=:y And month(oplateDate)=:m And day(oplateDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
       $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%supply}}.id) from  {{%supply}} 
        where  year(supplyDate)=:y And month(supplyDate)=:m And day(supplyDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
       $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%request_supply}}.id) from  {{%request_supply}} 
        where  year(requestDate)=:y And month(requestDate)=:m And day(requestDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        
        /*Оплаты */
        $stats['d_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from {{%oplata}}  where year(oplateDate)=:y And month(oplateDate)=:m And day(oplateDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        /*Отгрузки*/
        $stats['d_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from {{%supply}}  where  year(supplyDate)=:y And month(supplyDate)=:m And day(supplyDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
      /**/

        
        return $stats;
   }   

  
  
  
  
    
  /************End of model*******************/ 
 }
