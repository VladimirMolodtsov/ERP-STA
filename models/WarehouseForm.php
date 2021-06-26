<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\ScladList;
use app\models\OtvesList;
use app\models\ZakazContent;
use yii\bootstrap\Modal;

/**
 * MarketViewForm  - модель 
 */

 /*    
    supplyState
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
  0x00008 - 
*/  



class WarehouseForm extends Model
{

    public $id=0;
    public $otvesId=0;
    public $size=0;
    public $actionType=0;
    public $zakazId=0;
    public $zakazContentId=0;
    public $marketPrice=0;


    public $goodSclad="";
    public $goodValue=0;
    public $goodSchet="";
    public $good="";
    public $isValid = 1;
    public $grpGood;
    
    /*Заявки на отгрузку*/
    public $schetNum="";
    public $supplyDate="";
    public $userFIO="";
    //public $supplyState="";
    
    
    public $setSort="title";
    public $title="";
    public $orgTitle="";
    public $grpTitle="";
    public $inOtves=0;
    public $noOtves=0;
    public $isAnalyse = 1;

    public $detail=0;    
    public $view=0;        
    
    public $debug;    
    public $maxScore;
    public $sumScore;
    public $rateScore;

    public $sumRemain=0;
    public $monthRash=0;

    public $fltDeliver="";
    public $fltView="";
    public $fltStatus="";
    public $fltFinish="";
    public $fltSum=0;
    
    public $count;
    public $command;
    
    public $dFrom;
    public $dTo;

    public $userRestrict=0;


    public $rashodDate = array();
    
    public function rules()
    {
        return [
            [['id', 'size', 'actionType', 'otvesId', 'zakazId', 'zakazContentId', 'goodSclad', 'goodValue', 'goodSchet','marketPrice' ], 'default'],
            ['id', 'integer'],
            ['actionType', 'integer'],            
            ['size', 'double'],
            ['marketPrice', 'double'],            
            [['orgTitle','title', 'goodSclad', 'grpGood', 'isValid', 'good', 'schetNum', 'supplyDate', 'userFIO', 'isAnalyse', 'grpTitle',
            'fltDeliver', 'fltFinish', 'fltStatus', 'fltView', 'fltSum' ], 'safe'],
        ];
    }

  public function loadStoreRecord()    
  {
      $scladRecord= ScladList::findOne($this->id);      
      if (empty($scladRecord)) return false;
      $this->inOtves= Yii::$app->db->createCommand(
                    'SELECT sum(size) FROM {{%otves_list}} where refWarehouse=:refWarehouse ')
                    ->bindValue(':refWarehouse', $scladRecord->id)                    
                    ->queryScalar();
                          
      $this->noOtves=$scladRecord->amount - $this->inOtves;
      //$this->size = $this->noOtves;
      return $scladRecord;      
  }
  
  
  public function saveMarketprice()    
  {
      $scladRecord= ScladList::findOne($this->id);      
      if (empty($scladRecord)) return false;
      
      $scladRecord->marketPrice = $this->marketPrice;
      $scladRecord->save();
     return true;
  }      


  public function setAvRashod($id, $val)
  {
         $scladRecord= ScladList::findOne($id);      
      if (empty($scladRecord)) return false;
      $scladRecord->avRashod = $val;
      $scladRecord->save();
  }
  public function loadAvRashod()
  {
         $scladRecord= ScladList::findOne($this->id);      
      if (empty($scladRecord)) return false;
   
      $supplyList = Yii::$app->db->createCommand(
        'SELECT requestDate, wareCount, wareSumm  FROM {{%supplier_wares}} where wareRef=:wareRef ORDER BY requestDate DESC  LIMIT 5 ')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryALL();
      
      $this->rashodDate['supplyN'] = count ($supplyList);
      if ($this->rashodDate['supplyN'] > 0)
      { 
        $this->rashodDate['curSupplyDate']= $supplyList[0]['requestDate']; 
        $this->rashodDate['curSupplyCount']= $supplyList[0]['wareCount']; 
        $this->rashodDate['curSupplySumm']= $supplyList[0]['wareSumm']; 
        
       if ($supplyList[0]['requestDate'] != date('Y-m-d'))  
       {
        $this->rashodDate['cur'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[0]['requestDate'],
                    ':supplyNow' => date('Y-m-d'),                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['cur'] = 0;
       
       
       $lastSupply = Yii::$app->db->createCommand(
        'SELECT MAX(supplyDate)  FROM {{%supply}} where wareRef=:wareRef')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryScalar();
       
       if (strtotime($supplyList[0]['requestDate']) < strtotime($lastSupply))  
       {
        $this->rashodDate['last'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[0]['requestDate'],
                    ':supplyNow' => $lastSupply,                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['last'] = 0;

      }

      $this->rashodDate['lastSupplyDate'] = $lastSupply;
      
      if ($this->rashodDate['supplyN'] > 1)
      { 
        $this->rashodDate['prevSupplyDate']= $supplyList[1]['requestDate']; 
        $this->rashodDate['prevSupplyCount']= $supplyList[1]['wareCount']; 
        $this->rashodDate['prevSupplySumm']= $supplyList[1]['wareSumm']; 

        
       if ($supplyList[1]['requestDate'] != $supplyList[0]['requestDate'])  
       {
        $this->rashodDate['prev'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[1]['requestDate'],
                    ':supplyNow' => $supplyList[0]['requestDate'],                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['prev'] = 0;
       
      }

      $this->rashodDate['180d'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount) FROM {{%supply}} where wareRef=:wareRef AND DATEDIFF(NOW(), supplyDate ) <= 180')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryScalar() /180;

      
      return $scladRecord;  
  }
  
   public function loadRequestData($good)
   {
        
    $query  = new Query();
    $query->select ("good, SUM(count) as sumCount, goodSclad, goodSchet, min(marketDate) as minDate")
            ->from("{{%request_good_content}}")
            ->groupBy("good, goodSclad, goodSchet");
     $query->andWhere(['=', 'isActive', 1]);
     $query->andWhere(['like', 'good', $good]);
     $requestData = $query->createCommand()->queryAll(); 
 
    return $requestData[0];      
   }

   public function loadOtvesData ()
   {
       
       $strSql = "select inUse, size, {{%warehouse}}.title, price, ed, edPrice, userFIO, refManager, reservDate
                    from {{%otves_list}}
                    left join {{%warehouse}} on {{%otves_list}}.refWarehouse = {{%warehouse}}.id
                    left join {{%user}} on {{%otves_list}}.refManager = {{%user}}.id
                    where {{%otves_list}}.id=:id";

       
       $list= Yii::$app->db->createCommand($strSql)
                    ->bindValue(':id', $this->otvesId)                    
                    ->queryAll();
       
       return $list;
       
   }
  
  public function editOtves()
  {  
      $otvesRecord = OtvesList::findOne($this->otvesId);      
      if ($this->actionType == 1)
      {
          $otvesRecord->inUse = 0;;
          $otvesRecord->save();
      }
      if ($this->actionType == 2)
      {
        $otvesRecord->delete();  
      }
      
      
  }

  public function addOtves()
  {
      $this->loadStoreRecord();      
      $otvesRecord = new OtvesList();
      $this->size = (float)str_replace(',', '.',$this->size);
      //$otvesRecord->size=$this->size;
      if ($this->size <= $this->noOtves)  $otvesRecord->size=$this->size;
                                    else  $otvesRecord->size=$this->noOtves;                                                                                                              
      $otvesRecord->refWarehouse = $this->id;
      if ($otvesRecord->size == 0 ){return 0;}
      $otvesRecord->save();
      return $otvesRecord->size;
  }
    
  public function setEnableOtves($id)   
   {
      $scladRecord= ScladList::findOne($id);
      if (empty($scladRecord))return;
      $scladRecord->isOtves=1;
      $scladRecord->save();
   }

  public function setDisableOtves($id)   
   {
      $scladRecord= ScladList::findOne($id);
      if (empty($scladRecord))return;
      $scladRecord->isOtves=0;
      $scladRecord->save();
   }

   public function setReserveOtves($zakazId, $id, $reserved)
   {
       $curUser=Yii::$app->user->identity;
       $otvesRecord = OtvesList::findOne($id);      
       $otvesRecord->inUse = 1;
       $otvesRecord->refManager = $curUser->id;
       $otvesRecord->refZakaz = $zakazId;
       $otvesRecord->reservDate = date("Y-m-d");
       $otvesRecord->save();
       
       $this->id = $otvesRecord->refWarehouse;
       $this->zakazId = $zakazId;
       $this->size = $reserved+$otvesRecord->size;
       
/*print_r ('otves finished');       
print_r ("<pre>");
print_r ($this);*/

       $this->setReserveSize ();
   }
     
   public function unSetReserveOtves($zakazId, $id, $reserved)
   {
       $curUser=Yii::$app->user->identity;
       $otvesRecord = OtvesList::findOne($id);      
       $otvesRecord->inUse = 0;
       $otvesRecord->refManager = 0;
       $otvesRecord->refZakaz = 0;
       //$otvesRecord->reservDate = date("Y-m-d");
       $otvesRecord->save();

          $this->id = $otvesRecord->refWarehouse;
       $this->zakazId = $zakazId;
       $this->size = $reserved-$otvesRecord->size;
       $this->setReserveSize ();

   }


   public function setReserveSize ()
   {
       if(empty ($this->id)) return;
       if(empty ($this->zakazId)) return;
       
       $scladRecord= ScladList::findOne($this->id);
       if (empty ($scladRecord) ) return;
       
/*print_r ('Sclad finded');              
print_r ($scladRecord);*/
       
       /*Новый вариант предпочтителен - теперь, когда есть наш склад через ссылку */
       $zakazContentRecord = ZakazContent::findOne([
       'warehouseRef' => $this->id,
       'refZakaz' => $this->zakazId
       ]);       
       if (empty ($zakazContentRecord))
       {
        /*не нашли - пробуем старый вариант - через название*/
        $zakazContentRecord = ZakazContent::findOne([
            'refZakaz' => $this->zakazId,
            'good' => $scladRecord -> title
        ]);       
       }
       
       if (empty ($zakazContentRecord))
       {
         /*Все равно не нашли - заводим новую запись*/
            $zakazContentRecord = new ZakazContent ();
            if (empty ($zakazContentRecord)) return; /*вообще фигня*/    
            
            $zakazContentRecord->refZakaz = $this->zakazId;
            $zakazContentRecord->initialZakaz = $scladRecord -> title;
            $zakazContentRecord->good= $scladRecord -> title;
            $zakazContentRecord->ed = $scladRecord -> ed;
            $zakazContentRecord->value = $scladRecord -> price;
            $zakazContentRecord->warehouseRef = $scladRecord -> id;
       }

//print_r ('Zakaz finded');              
       
          $zakazContentRecord->isActive = 1;
       $zakazContentRecord->warehouseRef = $scladRecord -> id;
       $zakazContentRecord->count = $this->size;
       $zakazContentRecord->reserved = $this->size;
       $zakazContentRecord->save();
   }

    public function getGrpGroup() {
        
        $d=array();
        
        $d=  Yii::$app->db->createCommand('SELECT DISTINCT grpGood FROM {{%warehouse}} where grpGood <> "" ORDER BY grpGood')
                    ->queryColumn();        
        array_unshift ($d, 'Нет группы');                                        
        array_unshift ($d, 'Все');
        
        return $d;
    }
/*******************************/  
/* Переключим участие в анализе*/    
 public function switchGoodAnalyze($id)
 {
      $scladRecord= ScladList::findOne($id);      
      if (empty($scladRecord)) return false;
     
      if ($scladRecord->isAnalyse == 0) $scladRecord->isAnalyse = 1;
                                   else $scladRecord->isAnalyse = 0;
      $scladRecord->save();       
 }
    
    
/*******************************/  
  public function getStats()
  {
      
  //Поступило заявок
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW())";            
  $stats['m_accept'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())";            
  $stats['d_accept'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  // Отказов
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW()) AND (supplyState & 0x00004)";            
  $stats['m_reject'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())  AND (supplyState & 0x00004)";            
  $stats['d_reject'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              


  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW()) AND (supplyState & 0x00008)";            
  $stats['m_supplied'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())  AND (supplyState & 0x00008)";            
  $stats['d_supplied'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
  

  //За день
  $strCount = "SELECT count({{%purchase}}.id) from {{%purchase}} 
  LEFT JOIN (SELECT execDate, purchaseRef FROM {{%purchase_etap}} WHERE stage=4 and etap IN (3,8)) as a on a.purchaseRef = {{%purchase}}.id
  where DATE(a.execDate) = DATE(NOW())  AND isFinishedPurchase = 1";            
  $stats['d_buy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
    
  //За месяц
  $strCount = "SELECT count({{%purchase}}.id) from {{%purchase}} 
  LEFT JOIN (SELECT execDate, purchaseRef FROM {{%purchase_etap}} WHERE stage=4 and etap IN (3,8)) as a on a.purchaseRef = {{%purchase}}.id
  where MONTH(a.execDate) = MONTH(NOW())  AND isFinishedPurchase = 1";            
  $stats['m_buy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
  
  
  
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where 
  (MONTH(factDate) = MONTH(NOW()) OR MONTH(requestDatePlanned) = MONTH(NOW()) )
  AND (requestStatus = 6)";            
  $stats['m_delivered'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where 
  (DATE(factDate) = DATE(NOW()) OR DATE(requestDatePlanned) = DATE(NOW()) )
  AND (requestStatus = 6)";            ;            
  $stats['d_delivered'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
   
  
  return $stats;
  }
  /***************************/ 
  public function getWareListProvider($params)
   {
    
    
    $query  = new Query();
    $query->select ("{{%warehouse}}.id,  {{%warehouse}}.title, articul, grpGood ,amount, price, relizePrice, marketPrice, ed, edPrice, isOtves, reserved, isValid ")
            ->from("{{%warehouse}}");
    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
            ->from("{{%warehouse}}");
                

    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     
     if (!empty ($this->grpGood))
     {
        $listGrp = $this->getGrpGroup();             
        $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
        $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);        
     }
     
        if ($this->isValid == 0) $this->isValid = 1;
     }

     /*Фильтр на валидность применим всегда*/
     if ($this->isValid != 2)
     {
        $query->andFilterWhere(['=', 'isValid', $this->isValid]);
        $countquery->andFilterWhere(['=', 'isValid', $this->isValid]);        
     }
     
   

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    
    
    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "title";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }


    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
            'id',  
            'title', 
            'articul', 
            'amount', 
            'price', 
            'ed', 
            'edPrice',
            'isOtves',
            'reserved',
            'isValid',
            'grpGood',            
            ],
            
            'defaultOrder' => [ $this->setSort => $order ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /************************/
  /****** Reserve  ********/
  /************************/
 
 public function getReserveListProvider()
   {
    
    /*если поле пустое заполним*/
           Yii::$app->db->createCommand(
            'UPDATE {{%zakazContent}} SET good = initialZakaz WHERE good IS NULL AND refZakaz =:refZakaz', 
            [
            ':refZakaz' => $this->zakazId,            
            ])->execute();    
    
    $query  = new Query();
    $query->select ("{{%zakazContent}}.id as zakazContentId, good,  count, refZakaz, isOtves, {{%zakazContent}}.reserved as zakazReserved, {{%warehouse}}.id as scladId,  {{%warehouse}}.title, articul, amount, price, {{%warehouse}}.ed, edPrice, isOtves, {{%warehouse}}.reserved ")
            ->from("{{%zakazContent}}")
            ->leftJoin('{{%warehouse}}','{{%warehouse}}.title = {{%zakazContent}}.good')
    ;
    
    $countquery  = new Query();
    $countquery->select (" count({{%zakazContent}}.id)")
            ->from("{{%zakazContent}}")
            ->leftJoin('{{%warehouse}}','{{%warehouse}}.title = {{%zakazContent}}.good')
    ;                
    
          $query->andWhere(['=', 'refZakaz', $this->zakazId]);
     $countquery->andWhere(['=', 'refZakaz', $this->zakazId]);

    
/*
    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     }
*/   

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "good";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }


    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
          
            'attributes' => [
            'good',  
            'count', 
            'zakazReserved',
            'scladId',
            'articul', 
            'amount', 
            'price', 
            'ed', 
            'edPrice',
            'isOtves',
            'reserved'
            ],
            
            'defaultOrder' => [ $this->setSort => $order ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
 
/********************/
/*** Good request ***/
/********************/

 public function getGoodRequestProvider($params)
   {
    
    $query  = new Query();
    $query->select ("good, SUM(count) as sumCount, goodSclad, goodSchet, min(marketDate) as minDate")
            ->from("{{%request_good_content}}")
            ->groupBy("good, goodSclad, goodSchet");
    $countquery  = new Query();
    $countquery->select (" count(good)")
            ->from("{{%request_good_content}}")
            ->groupBy("good, goodSclad, goodSchet");

     $query->andWhere(['=', 'isActive', 1]);
     $countquery->andWhere(['=', 'isActive', 1]);
            
     if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'good', $this->good]);
     $countquery->andFilterWhere(['like', 'good', $this->good]);

     $query->andFilterWhere(['like', 'goodSclad', $this->goodSclad]);
     $countquery->andFilterWhere(['like', 'goodSclad', $this->goodSclad]);     
     }
   

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "good";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }


    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'good', 
            'sumCount', 
            'goodSclad', 
            'goodSchet', 
            'minDate',
            ],
            
            'defaultOrder' => [ $this->setSort => $order ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
  /* список на поставку */

  /*
  Статус поставки
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
  
  
  
  */
  
  /********************************/   
  /* Оценим наполненность склада */
  
  
  public function  getStoreFullnes()   
  {
   /* берем среднемесячный расход с начала продаж за сумму которую должны продать */
   /* Вычитаем что уже в заказах и счетах без поставки */   
   /* Берем к-во на складе, добавляем то что в пути  */
   /* 
        отклонение < 10% = +2
        отклонение < 20% = +1
        отклонение > 20% = -1
        отклонение > 40% = -2
        *вес товара в анализе 
   */
   
 
    $strSql = "Select {{%warehouse}}.id, title, price, amount, inTransit, isAnalyse, zakaz.inZakazN, 
        ifnull(SUM(supplyCount),0) as N, TIMESTAMPDIFF(DAY, MIN(supplyDate), NOW()) as P
        from {{%warehouse}} left join {{%supply}} on  {{%supply}}.wareRef = {{%warehouse}}.id 
        left join (
        Select sum({{%zakazContent}}.[[count]]) as inZakazN, warehouseRef FROM {{%zakazContent}} 
        left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
        left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
        where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
        group by warehouseRef
        ) as zakaz on zakaz.warehouseRef = {{%warehouse}}.id
        group by {{%warehouse}}.id, title";
      
   $listGood = Yii::$app->db->createCommand($strSql)->queryAll();      
   
   $score = 0;
   $maxScore = 0;
   for ($i=0; $i < count($listGood); $i++ )
   {
       if ($listGood[$i]['N'] == 0) continue; // нет продаж нет статистики
       if ($listGood[$i]['isAnalyse'] == 0) continue; // нафиг нам нулевой коэффициент?
       if ($listGood[$i]['P'] == 0) continue; // Перестрахуемся, все равно тогда данные не верны
       
       $avInDay = $listGood[$i]['N'] / $listGood[$i]['P'];//Средняя убыль в день
       if ($avInDay == 0)  continue; // Нет прогноза
       
       $waitInMonth = $avInDay*30 ; //Ожидаем в этом месяце 

       $curV = $listGood[$i]['amount'] + $listGood[$i]['inTransit'] ;
       $rate = $curV/($waitInMonth);
              
       $maxScore ++;


       if     ($rate == 0)                   $score += 0;       
       elseif ($rate > 0 && $rate <= 1.0  )  $score += $rate;
       elseif ($rate <2.0  )                 $score += 2.0-$rate;
       elseif ($rate >=2.0  )                $score += 0;
       
   }
   
  // if ($score < 0) $score = 0;

$this->maxScore = $maxScore;
$this->sumScore = $score;
$this->rateScore = $score/$maxScore;


  
   return 100*$score/$maxScore;
      
}
   
   /*цифры в листок*/
  public function  getLeafValue ()
  {
      for($i=0;$i<12;$i++)$leafValue[$i]=0;

      
    /*Заявки на доставку*/  
       /*Всего новых заявок*/
         $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} 
       LEFT JOIN {{%schet}} on {{%request_supply}}.refSchet = {{%schet}}.id 
       LEFT JOIN {{%supply_status}} on {{%supply_status}}.refSupply ={{%request_supply}}.id
       where  {{%request_supply}}.supplyState=0 AND  {{%schet}}.isSchetActive = 1 AND {{%request_supply}}.isAccepted = 1
       and IFNULL(`rik_supply_status`.st1,'0000-00-00') = '0000-00-00'
       ";            
       $leafValue['requestNew'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

       /*Заявки, в процессе исполнения */
            $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} 
          LEFT JOIN {{%schet}} on {{%request_supply}}.refSchet = {{%schet}}.id 
          LEFT JOIN {{%supply_status}} on {{%supply_status}}.refSupply ={{%request_supply}}.id
          where  (
            ({{%request_supply}}.supplyState & (0x00001|0x00002)   and {{%request_supply}}.supplyState < 4) 
                                           OR  IFNULL({{%supply_status}}.st1,'0000-00-00') <> '0000-00-00') 
          AND  IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00' 
          AND {{%request_supply}}.isAccepted = 1
          AND  {{%schet}}.isSchetActive = 1 ";            
       $leafValue['requestInExec'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
              
             
              
       /*Заявки, выполнены */
            $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}}
          LEFT JOIN {{%schet}} on {{%request_supply}}.refSchet = {{%schet}}.id 
          LEFT JOIN {{%supply_status}} on {{%supply_status}}.refSupply ={{%request_supply}}.id          
          where  ({{%request_supply}}.supplyState & (0x00004|0x00008) 
                 OR  IFNULL({{%supply_status}}.st17,'0000-00-00') <> '0000-00-00')          
                 AND  {{%schet}}.isSchetActive = 1 AND DATEDIFF(NOW(), finishDate)<2 ";            
       $leafValue['requestFinished'] =  Yii::$app->db->createCommand($strCount )->queryScalar();              

                 
/*Задания экспедитору*/            
       /*Новые*/
         $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus <= 1";            
       $leafValue['deliverNew'] =  Yii::$app->db->createCommand($strCount )->queryScalar();              
       
       /*Переданы в доставку*/
         $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus >= 2 AND requestStatus <= 3";            
       $leafValue['deliverProcess'] =  Yii::$app->db->createCommand($strCount )->queryScalar();              
       

       /*Доставлено*/
         $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus >= 4 AND requestStatus <= 5";            
       $leafValue['deliverFinit'] =   Yii::$app->db->createCommand($strCount )->queryScalar();              

       
/*Склад*/       
       /*Отложено - в резерве для заказов, счетов*/
       $strCount = "Select sum({{%zakazContent}}.[[count]] * {{%warehouse}}.price) from {{%warehouse}} 
       left join {{%zakazContent}} on {{%zakazContent}}.warehouseRef = {{%warehouse}}.id
       left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
       left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
       left Join {{%request_supply}} on {{%request_supply}}.refSchet = {{%schet}}.id
       where ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0)  AND ({{%request_supply}}.supplyState < 4 ))";         
       $leafValue['otlozheno'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

 
         /*Остаток на складе*/
       $strCount = "Select sum({{%warehouse}}.[[amount]] * {{%warehouse}}.price) from {{%warehouse}}";         
       $leafValue['amount'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

       /*Товар в пути*/
       $strCount = "Select sum({{%warehouse}}.[[inTransit]] * {{%warehouse}}.price) from {{%warehouse}}";         
       $leafValue['inTransit'] =Yii::$app->db->createCommand($strCount )->queryScalar();              
       
       /*Товар в пути*/       
       $leafValue['storeStatus'] = $this->getStoreFullnes();
       
       
       /*Отгружено, в процессе оплаты*/
         $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND summSupply >= schetSumm AND summOplata > 0 AND summOplata < schetSumm and {{%schet}}.ref1C IS NOT NULL ";            
       $leafValue[8] =Yii::$app->db->createCommand($strCount )->queryScalar();              
       

/*Закупка*/       
       /*Число поставщиков*/
         $strCount = "SELECT count(id) from {{%orglist}} where isOrgActive = 1 AND ({{%orglist}}.contragentType & 0x1) ";            
       $leafValue['supplierCount'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

       /*Число активных закупок*/

        $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where isActive =1 and zaprosType=0 ";            
        $leafValue['supplierActiveSchet'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

        /*Число запросов цены*/        
        $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where isActive =1 and zaprosType=1 ";            
        $leafValue['activeZapros'] =Yii::$app->db->createCommand($strCount )->queryScalar();              

        $strCount = "SELECT count({{%request_good_content}}.id) from {{%request_good}}, {{%request_good_content}} where 
        {{%request_good}}.id =  {{%request_good_content}}.refRequest AND 
        {{%request_good_content}}.refPurchaseZakaz = 0 AND {{%request_good_content}}.isFinished =0 and {{%request_good}}.isFormed = 1";            
        $leafValue['supplierActiveSchet'] +=Yii::$app->db->createCommand($strCount )->queryScalar();              

        
       /*Число товарных позиций*/
         $strCount = "select  DISTINCT wareTitle from  {{%supplier_wares}} ";            
        $r=Yii::$app->db->createCommand($strCount )->queryAll();              
       $leafValue['goodPositions']= count($r);
       
       /*Отгружено, не оплачено*/


       

       $leafValue[12] = 0;
       
    return $leafValue;   
      
  }
/***********************************/

 public function getSupplyRequestProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%request_supply}}.id as requestId', 
            'requestDate', 
            'refSchet', 
            'supplyDate', 
            '{{%schet}}.schetNum', 
            '{{%schet}}.schetDate', 
            'summOplata', 
            'schetSumm', 
            'supplyType', 
            '{{%request_supply}}.contactPhone', 
            '{{%request_supply}}.contactFIO', 
            '{{%request_supply}}.contactEmail', 
            '{{%request_supply}}.adress', 
            'requestNote', 
            '{{%request_supply}}.supplyState', 
            'dstNote', 
            'finishDate', 
            'execNum', 
            'supplyNote', 
            'userFIO', 
            'title',
            'viewManagerRef',
            'execView',
            '{{%schet}}.refOrg',
            'st1','st2','st3','st4','st5','st6','st7','st8','st9','st10',
            'st11','st12','st13','st14','st15','st16','st17',
            ])
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%request_supply}}.id)")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;

     $query->andWhere(['=', 'isAccepted', 1]);
     $countquery->andWhere(['=', 'isAccepted', 1]);
            
     $query->andWhere(['=', 'isSchetActive', 1]);
     $countquery->andWhere(['=', 'isSchetActive', 1]);

    if(!empty ($this->userRestrict))        
    {
     $query->andWhere(['=', '{{%schet}}.refManager', $this->userRestrict]);
     $countquery->andWhere(['=', '{{%schet}}.refManager', $this->userRestrict]);        
    }
        
    
    if(!empty ($this->dFrom))        
    {
     $query->andWhere(['>=', 'DATE([[finishDate]])', date("Y-m-d", strtotime($this->dFrom))]);
     $countquery->andWhere(['>=', 'DATE([[finishDate]])', date("Y-m-d", strtotime($this->dFrom))]);     
    }

    if(!empty ($this->dTo))        
    {
     $query->andWhere(['<=', 'DATE([[finishDate]])', date("Y-m-d", strtotime($this->dTo))]);
     $countquery->andWhere(['<=', 'DATE([[finishDate]])', date("Y-m-d", strtotime($this->dTo))]);     
    }


            
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     
     
     
     
        $query->andFilterWhere(['like', 'schetNum', $this->schetNum]);
        $countquery->andFilterWhere(['like', 'schetNum', $this->schetNum]);     
     
        if (!empty($this->supplyDate))
        {
        $query->andFilterWhere(['or',
            ['=','supplyDate',date("Y-m-d",strtotime($this->supplyDate))],
            ['=','finishDate',date("Y-m-d",strtotime($this->supplyDate))]
            ]);
        $countquery->andFilterWhere(['or',
            ['=','supplyDate',date("Y-m-d",strtotime($this->supplyDate))],
            ['=','finishDate',date("Y-m-d",strtotime($this->supplyDate))]
            ]);
            
        }

        if ($this->fltView == 1)
        {
            $this->detail = 0;
        }    

        
        if ($this->fltView == 2)
        {
            $query->andFilterWhere(['>', 'viewManagerRef', 0]);
            $countquery->andFilterWhere(['>', 'viewManagerRef', 0]);     
            $this->detail = 0;
        }    
        
     
        if ($this->fltView == 3)
        {
            $query->andFilterWhere(['=', 'ifnull(viewManagerRef,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(viewManagerRef,0)', 0]);     
            $this->detail = 0;
        }    


        if (!empty($this->fltStatus))
        {
          $fld="st".$this->fltStatus ;
          $next = $this->fltStatus+1 ;
          $fldNext="st".$next;
          
            $query->andWhere( new Expression ( "IFNULL({{%supply_status}}.".$fld.",'0000-00-00') <> '0000-00-00'") );
            $countquery->andWhere( new Expression ( "IFNULL({{%supply_status}}.".$fld.",'0000-00-00') <> '0000-00-00'") );        
          if($this->fltStatus < 17) 
          {
            $query->andWhere( new Expression ( "IFNULL({{%supply_status}}.".$fldNext.",'0000-00-00') = '0000-00-00'") );
            $countquery->andWhere( new Expression ( "IFNULL({{%supply_status}}.".$fldNext.",'0000-00-00') = '0000-00-00'") );        
          
          } 
          
            
        }
        
        
        if ($this->fltFinish == 1)
        {
            $this->detail = 0;
        }    
 
                
        if ($this->fltFinish == 2)
        {
            $query->andWhere( new Expression ( '{{%request_supply}}.supplyState & (0x00004|0x00008)') );
            $countquery->andWhere( new Expression ( '{{%request_supply}}.supplyState & (0x00004|0x00008)') );
            $this->detail = 0;
        }    
     
        if ($this->fltFinish == 3)
        {
            $query->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );
            $countquery->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );

            $query->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            $countquery->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            
            $this->detail = 0;
        }    
        
        
        
     }
   
   
      switch ($this->detail)
     {
        case 1: 
            $query->andWhere("({{%request_supply}}.supplyState = 0 AND {{%request_supply}}.id < 1453)
            OR ( {{%request_supply}}.id >= 1453 AND IFNULL({{%supply_status}}.st1,'0000-00-00') = '0000-00-00')");
            $countquery->andWhere("({{%request_supply}}.supplyState = 0 AND {{%request_supply}}.id < 1453)
            OR ( {{%request_supply}}.id >= 1453 AND IFNULL({{%supply_status}}.st1,'0000-00-00') = '0000-00-00')");
//            $this->fltAccept =4;            
        break;        
         
         /*Заявки, в процессе исполнения */
        case 2: 
            $query->andWhere( new Expression ( "{{%request_supply}}.supplyState & (0x00001|0x00002) and {{%request_supply}}.supplyState < 4 
            OR  IFNULL({{%supply_status}}.st1,'0000-00-00') <> '0000-00-00'"));
            $countquery->andWhere( new Expression ( "{{%request_supply}}.supplyState & (0x00001|0x00002) and {{%request_supply}}.supplyState < 4 
            OR  IFNULL({{%supply_status}}.st1,'0000-00-00') <> '0000-00-00'"));
            
            $query->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );
            $countquery->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );
            
            $query->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            $countquery->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            
            $this->fltFinish = 3;
                        
        break;        
         

         /*Заявки, активные */
        case 10: 
            
            $query->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );
            $countquery->andWhere( new Expression ( '!({{%request_supply}}.supplyState & (0x00004|0x00008))') );
            
            $query->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            $countquery->andWhere( new Expression ( "IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00'") );
            
            $this->fltFinish = 3;
                        
        break;        
         
         
         /*Заявки, выполнены */
        case 3:         
            $query->andWhere( new Expression ( "({{%request_supply}}.supplyState & (0x00004|0x00008)
            OR  IFNULL({{%supply_status}}.st17,'0000-00-00') <> '0000-00-00')"));
            $query->andWhere( new Expression ("DATEDIFF(NOW(), finishDate)<2 "));


            
            $countquery->andWhere( new Expression ( "({{%request_supply}}.supplyState & (0x00004|0x00008)
            OR  IFNULL({{%supply_status}}.st17,'0000-00-00') <> '0000-00-00')"));
            $countquery->andWhere( new Expression ("DATEDIFF(NOW(), finishDate)<2 "));
                        
            $this->fltFinish = 2;
        break;                        
     }


//echo  $query->createCommand()->getRawSql();    
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

  /* 
    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "good";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }
*/

    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'requestId', 
            'requestDate', 
            'supplyDate', 
            'supplyType', 
            'supplyState', 
            'finishDate', 
            'execNum',
            'userFIO',
            'title',
            'schetNum', 
            'schetDate', 
            'summOplata', 
            'schetSumm',
            'execView'
            ],
            
            //'defaultOrder' => [ $this->setSort => $order ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 

 
 public function getSupplyRequestStatusArray()
  {
   $listStatus = Yii::$app->db->createCommand('Select id, statusTitle from {{%supply_status_title}}')                    
                    ->queryAll();                
   return ArrayHelper::map($listStatus, 'id', 'statusTitle');                      
  }
 /****************************************/
 /* Заявки от менеджеров */
  public function printSupplyRequest($provider)
  {
 
   $listStatus = Yii::$app->db->createCommand('Select id, statusTitle from {{%supply_status_title}}')                    
                    ->queryAll();                
   $listArray= ArrayHelper::map($listStatus, 'id', 'statusTitle');                      
 
 $url="";
  
  switch ($this->view )
  {
      case 0:
        $url = "index.php?r=store/sclad-start2/";
      break;
      case 1:
        $this->alterPrintSupplyRequest($provider);
      return;
      break;
      case 2:
        $url = "index.php?r=store/head-sclad/";
      break;
      case 3:
        $this->alterPrintSupplyRequest($provider);
      return;      
      break;
  }
      
  echo 
  "
  <div class='row'>
    <div class='col-md-2'><div style='padding-top: 10px; text-align:right;'> Назначено от </div></div>
    <div class='col-md-2'><input type='date' class='form-control' name='dFrom' id='dFrom' value='".$this->dFrom."' > </div>
    <div class='col-md-1'><div style='padding-top: 10px; text-align:right;'>до </div></div>
    <div class='col-md-2'><input type='date' class='form-control' name='dTo' id='dTo'  value='".$this->dTo."' > </div>
    <div class='col-md-3' style='text-align:left;'><input type='button' class='btn btn-primary' onclick='setDatePeriod();' value='Отфильтровать'>
        <input type='button' class='btn btn-primary' onclick='unSetDatePeriod();' value='Сбросить'>
    </div> 
    <div class='col-md-2'>
        <a href='".$url."&detail=".$this->detail."&view=".($this->view+1)."' class='btn btn-primary'>Этапы исполнения</a>
    </div> 
  </div>   
  <div class='spacer'></div>
  ".
  \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            /*[
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],*/    

            
            [
                'attribute' => 'userFIO',
                'label' => 'Заявка от',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $val ="<nobr><b>".$model['requestId']."</b>"." от ".date("d.m",strtotime($model['requestDate']))."</nobr>";
                 //if (!empty ($model['execNum'])) $val.="<br><nobr>Доставка № :".$model['execNum']."</nobr>";
                 $url= "store/supply-request";
                 if ($model['requestId'] >= 1453) $url= "store/supply-request-new";
                 
                 /*по моему разделение действий уже не нужно - проверить*/
                    if ($model['supplyState'] & 0x00004) 
                    {
                        return "<a href='#' onclick='javascript:openWin(\"".$url."&actor=2&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a><br>".$model['userFIO'];
                    }  

                    if ($model['supplyState'] & 0x00008) 
                    {
                        return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=deliver&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a><br>".$model['userFIO'];
                    }  
                    
                    if ($model['supplyState'] & 0x00002) 
                    {
                        return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=deliver&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a><br>".$model['userFIO'];
                    }  
                    
                    if ($model['supplyState'] & 0x00001) 
                    {
                       return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=accepted&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a><br>".$model['userFIO'];
                    }  

                return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a> <br>".$model['userFIO'] ;
                }
            ],    
            
            [
                'attribute' => 'schetNum',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 return $model['schetNum']." от ".date("d.m.y",strtotime($model['schetDate']))."<br><nobr> на сумму: ".number_format($model['schetSumm'], 2, '.', '&nbsp;')."</nobr>";
                }
            ],    
            
            
            [
                'attribute' => 'supplyDate',
                'label'     => 'Дата отгрузки',
                'format' => 'raw',
                //'format' => ['datetime', 'php:d.m.y'],
                
                'value' => function ($model, $key, $index, $column) {
                 
                 $ret="";
                 if (!empty($model['supplyDate'])) $ret.= " План: ".date("d.m.Y",strtotime($model['supplyDate']))."<br>";
                 if (!empty($model['finishDate'])) $ret.= " Факт: ".date("d.m.Y",strtotime($model['finishDate']));   
                 return $ret;
                }
                
            ],            

            [
                'attribute' => 'title',
                'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },
            ],        

            [
                'attribute' => 'fltView',
                'label'     => 'Просмотрено',                
                'format' => 'raw',
                'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),
                'value' => function ($model, $key, $index, $column) {
                    $val ="";
            
                    if ($model['viewManagerRef'] == 0) 
                    {
                        return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' onclick='javascript:openWin(\"store/supply-request-new&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>
                        ";
                    }                                                                  
                return date("d.m.y h:i", 7*60*60+ strtotime($model['execView']) );
                }
                
            ],

            [
                'attribute' => 'fltStatus',
                'label'     => 'Статус',                
                'format' => 'raw',
                'filter'=>$listArray,

                'value' => function ($model, $key, $index, $column) {
                    $val ="";
                   if ($model['requestId'] < 1453) {
                       $url= "store/supply-request";                                      
                    if ($model['supplyState'] == 0) 
                        return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' 
                        onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>";
                    if ($model['supplyState'] & 0x00004) 
                        return "<input class='btn btn-danger local_btn'  type=button value='Отказ' 
                        onclick='javascript:openWin(\"".$url."&actor=2&id=".$model['requestId']."\", \"supplyWin\");'>";                    
                    if ($model['supplyState'] & 0x00001)                     
                       return "<input class='btn btn-success local_btn'  style='background:ForestGreen;' type=button value='Принято' 
                       onclick='javascript:openWin(\"".$url."&viewMode=accepted&id=".$model['requestId']."\", \"supplyWin\");'>";
                    }                      
                 
                 $listStatus = Yii::$app->db->createCommand('Select id, statusTitle from {{%supply_status_title}}')                    
                    ->queryAll();                
                 $listArray= ArrayHelper::map($listStatus, 'id', 'statusTitle');                   
                 $url= "store/supply-request-new";                                               
                 for ($i=17; $i>0; $i--)
                 {
                  $fld="st".$i;     
                  if(strtotime($model[$fld])> 1)         
                  {
                    return "<a href='#' onclick='javascript:openWin(\"".$url."&id=".$model['requestId']."\", \"supplyWin\");'>".$listArray[$i]."</a>";   
                    break;                   
                  }
                 }
                                                                
                return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' 
                        onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>";
                }
                
            ],
            
            [
                'attribute' => 'fltDeliver',
                'label'     => 'Доставки',                
                'format' => 'raw',
                 'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),

                'value' => function ($model, $key, $index, $column) {
                    $val ="";
                    
                    
                    if ($model['supplyType'] == 0 ) $val .= "Самовывоз"."<br>";
                    
                    $inDeliverList = Yii::$app->db->createCommand(
                    'Select sum(requestGoodValue*requestCount) as sumDeliver,
                    {{%request_deliver}}.id as deliverId
                    from {{%request_deliver}}, {{%request_deliver_content}} 
                    where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef and refSchet = :refSchet
                    GROUP BY {{%request_deliver}}.id
                    ')
                    ->bindValue(':refSchet', $model['refSchet'])                                        
                    ->queryAll();

                    if (empty ($inDeliverList) ) $val= "<nobr>Нет доставок</nobr>";
                    
                    $cnt = count($inDeliverList); 
                    $sum =0;                    
                    for ($i=0; $i<$cnt; $i++ )
                    {
                        $val .="<a href='#' onclick='javascript:openWin(\"store/deliver-zakaz&id=".$inDeliverList[$i]['deliverId']."\", \"deliverWin\");'>
                        <nobr>На сумму:".number_format($inDeliverList[$i]['sumDeliver'], 2, '.', '&nbsp;')."</nobr></a><br>";                            
                        $sum+=$inDeliverList[$i]['sumDeliver'];
                    }
                    
                    if($cnt > 0)
                    {
                    if ($sum == $model['schetSumm'])     {$color ="ForestGreen";}
                    elseif ($sum > $model['schetSumm'])  {$color ="Orange";}
                                                    else {$color ="Crimson";}
                      
                    $val .="<div style='text-align:right;font-weight:bold; color:".$color."'>ВСЕГО: ".number_format($sum, 2, '.', '&nbsp;')."</div> ";
                    }
                    
                    if ( ($model['supplyState'] & 0x00008) || $model['supplyState'] & 0x00004) 
                        return $val; 

                    
                    return $val."<div style='width:100%; text-align:right;' ><a href='#' onclick='javascript:openWin(\"store/deliver-zakaz&action=create&requestId=".$model['requestId']."&schetId=".$model['refSchet']."\", \"deliverWin\");' >
                     "."<span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a></div>";
                    
                return $val;
                }
                
            ],



            [
                'attribute' => 'fltFinish',
                'label'     => 'Доставлено',                
                'format' => 'raw',
                'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),
                'value' => function ($model, $key, $index, $column) {
                    $val ="";

                    if ($model['supplyState'] & 0x00008) 
                    {
                        return "<input class='btn btn-success local_btn' style='background-color: ForestGreen;' type=button value='Доставлен' onclick='javascript:openWin(\"store/supply-request&viewMode=deliver&id=".$model['requestId']."\", \"supplyWin\");'>
                        ";
                    }  
                    
                return "";
                }
                
            ],


            
            
            
        ],
    ]
);
  
      
  }
  
  
 public function alterPrintSupplyRequest($provider)
  {
  
 $url="";

  switch ($this->view )
  {
      case 0:
        $this->printSupplyRequest($provider);
        return;        
      break;
      case 1:
        $url = "index.php?r=store/sclad-start2/";
      break;
      case 2:
        $this->printSupplyRequest($provider);
        return;      
      break;
      case 3:        
        $url = "index.php?r=store/head-sclad/";
      
      break;
  }
  
  echo 
  "
  <div class='row'>
  <div class='col-md-10'>
   </div> 
   <div class='col-md-2'>
    <a href='".$url."&detail=".$this->detail."&view=".($this->view-1)."' class='btn btn-primary'>Таблица</a>
   </div> 
   </div> 
  ". \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
            [
                'attribute' => 'userFIO',
                'label' => 'Заявка от',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $val ="<nobr><b>".$model['requestId']."</b>"." от ".date("d.m",strtotime($model['requestDate']))."</nobr>";
                 //if (!empty ($model['execNum'])) $val.="<br><nobr>Доставка № :".$model['execNum']."</nobr>";
                 $url= "store/supply-request";
                 if ($model['requestId'] >= 1453) $url= "store/supply-request-new";
                                  
                return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a> <br>".$model['userFIO'] ;
                }
            ],    

            [
                'attribute' => 'title',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },
            ],                    
            [
                'attribute' => 'schetNum',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 return $model['schetNum']." от ".date("d.m.y",strtotime($model['schetDate']))."<br><nobr> на сумму: ".number_format($model['schetSumm'], 2, '.', '&nbsp;')."</nobr>";
                }
            ],    
            
/*         'st1','st2','st3','st4','st5','st6','st7','st8','st9','st10',
            'st11','st12','st13','st14','st15','st16','st17',
            */
            [
                'attribute' => 'Просмотр',
                'label' => 'Просм.',
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if ($model['viewManagerRef'] > 0)   
                    return "<div style='width:70px;height:55px;background:BurlyWood;'>&nbsp; </div>";   
                 return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";   
                }
            ],    
            
            
            [
                'attribute' => 'Принят',
                'label' => 'Принят',
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    

                 return "<div style='width:70px;height:55px;background:BurlyWood;'>&nbsp; </div>";   
                 
                
                }
            ],    
            
            
            [
                'attribute' => 'Закупка',
                'label' => 'Закуп.',
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st2'] =='0000-00-00') &&  ( $model['st3'] =='0000-00-00')) 
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 
                 return "<div style='width:70px;height:55px;background:BurlyWood;'>&nbsp; </div>";   
                }
            ],    
            
            [
                'attribute' => 'Производство',
                'label' => 'Произ.',
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st9'] !='0000-00-00') || ($model['st10'] !='0000-00-00')|| ($model['st8'] !='0000-00-00') || ($model['st7'] !='0000-00-00')) 
                     return "<div style='width:70px;height:55px;background:BurlyWood;'>&nbsp; </div>";   
                 
                 return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                }
            ],    
            
            [
                'attribute' => 'Готов',
                'label' => 'Товар<br>готов',
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st10'] !='0000-00-00') || ($model['st11'] !='0000-00-00')) 
                     return "<div style='width:70px;height:55px;background:BurlyWood;'>&nbsp; </div>";   
                 
                 return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                }
            ],    
            
            
            [
                'attribute' => 'Отгрузка',
                'label' => 'Отгруз.',
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st11'] !='0000-00-00') || ($model['st12'] !='0000-00-00')) 
                     return "<div style='width:70px;height:55px;background:Green;'>&nbsp; </div>";   
                 
                 return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                }
            ],    


            [
                'attribute' => 'Получен',
                'label' => 'Получен<br>клиент.',
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:75px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st14'] !='0000-00-00') || ($model['st15'] !='0000-00-00')) 
                     return "<div style='width:75px;height:55px;background:Green;'>&nbsp; </div>";   
                 
                 return "<div style='width:75px;height:55px;background:White;'>&nbsp; </div>";    
                }
            ],    
            
            
            [
                'attribute' => 'Документы',
                'label' => 'Докум.<br>сданы',
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;width:70px;'],
                'value' => function ($model, $key, $index, $column) {
                 if (empty($model['st1']) || $model['st1'] =='0000-00-00')   
                     return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                 if ( ($model['st17'] !='0000-00-00') ) 
                     return "<div style='width:70px;height:55px;background:Green;'>&nbsp; </div>";   
                 
                 return "<div style='width:70px;height:55px;background:White;'>&nbsp; </div>";    
                }
            ],    
            
        ],
    ]
);
  
      
  }  
/*******************************************************/
/************** Листок склада **************************/
/*******************************************************/

public function prepareGoodsInOrderData($params)
{

    $query  = new Query();
    $query->select ([
            '{{%warehouse}}.id', 
            '{{%warehouse}}.title', 
            'grpGood',
            '{{%warehouse}}.price', 
            'sum({{%zakazContent}}.[[count]] * {{%warehouse}}.price) as V', 
            'sum({{%zakazContent}}.count) as N', 
            'COUNT({{%zakaz}}.id) as zakazN', 
            'COUNT({{%schet}}.id) as schetN', 
            '{{%warehouse}}.ed'    
             ])
            ->from("{{%warehouse}}")
            ->leftJoin('{{%zakazContent}}','{{%zakazContent}}.warehouseRef = {{%warehouse}}.id')
            ->leftJoin('{{%zakaz}}','{{%zakazContent}}.refZakaz = {{%zakaz}}.id')
            ->leftJoin('{{%schet}}','{{%zakazContent}}.refZakaz = {{%schet}}.refZakaz')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%schet}}.id')
            ->where ('({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))  AND ({{%request_supply}}.supplyState < 4 )')
            ->groupBy ('{{%warehouse}}.id, {{%warehouse}}.title, {{%warehouse}}.price, {{%zakazContent}}.good, {{%warehouse}}.ed');
            
    $countquery  = new Query();
    $countquery->select ("count({{%warehouse}}.id)")             
            ->from("{{%warehouse}}")
            ->leftJoin('{{%zakazContent}}','{{%zakazContent}}.warehouseRef = {{%warehouse}}.id')
            ->leftJoin('{{%zakaz}}','{{%zakazContent}}.refZakaz = {{%zakaz}}.id')
            ->leftJoin('{{%schet}}','{{%zakazContent}}.refZakaz = {{%schet}}.refZakaz')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%schet}}.id')
            ->where ('({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0)) AND ({{%request_supply}}.supplyState < 4 )')
            ;
           
            
     if (($this->load($params) && $this->validate())) {
     
        $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);

        if (!empty ($this->grpGood))
        {
            $listGrp = $this->getGrpGroup();     
            $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
            $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
        }

        
     }
   

    $this->command = $query->createCommand(); 
    $listAll = $query->createCommand()->queryAll(); // Фиг знает почему сбоит запрос на count
    //$count = $countquery->createCommand()->queryScalar();
    $this->count = count ($listAll);

    
}



 public function getGoodsInOrderData($params)
{
    $this->prepareGoodsInOrderData($params);        
    $dataList=$this->command->queryAll();  
     
    $fname = "uploads/headGoodsInOrderReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Товарная группа"),
        iconv("UTF-8", "Windows-1251","Товар"), 

        iconv("UTF-8", "Windows-1251","Заказано"), 
        iconv("UTF-8", "Windows-1251","Ед.изм"),
        
        iconv("UTF-8", "Windows-1251","Заявок"),        
        iconv("UTF-8", "Windows-1251","Cчетов"), 

        iconv("UTF-8", "Windows-1251","Цена"),
        iconv("UTF-8", "Windows-1251","На Сумму"), 
        
        );
        fputcsv($fp, $col_title, ";"); 
        
    for ($i=0; $i< count($dataList); $i++)
    {        
         
    $list = array 
        (
        

        iconv("UTF-8", "Windows-1251",$dataList[$i]['grpGood']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  

        iconv("UTF-8", "Windows-1251",$dataList[$i]['N']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['ed']),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['zakazN']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['schetN']),  

        iconv("UTF-8", "Windows-1251",$dataList[$i]['price']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['V']),  
        
        );
                
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
}

/*Товар в заказах*/
 public function getGoodsInOrderProvider($params)
   {
    
    $this->prepareGoodsInOrderData($params);
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'id', 
            'grpGood',
            'title', 
            'price', 
            'V', 
            'N', 
            'ed',
            'zakazN',
            'schetN',            
            ],

            ],
            
        ]);
    return  $dataProvider;   
   }   
 

 
 /****************************************/
 /* Заявки от менеджеров */
  public function printGoodsInOrder($provider)
  {

  echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                     
             [
                'attribute' => 'grpGood',
                'label' => 'Товарная группа',
                'format' => 'raw',
                'filter' => $this->getGrpGroup(),
                
            ],        
                     
            [
                'attribute' => 'title',
                'label' => 'Товар',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'N',
                'label' => 'Заказано',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'ed',
                'label' => 'Ед.изм',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'zakazN',
                'label' => 'Заявок',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'schetN',
                'label' => 'Счетов',
                'format' => 'raw',
            ],    
            

            
            [
                'attribute' => 'price',
                'label' => 'Цена',
                'format' => 'raw',
                
            ],    
            
            [
                'attribute' => 'V',
                'label' => 'На сумму',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                 return number_format($model['V'], 2, '.', '&nbsp;');
                }
            ],    
            
            
        ],
    ]
);
  
      
  }

/***************************/
  /*Товар на складе*/
public function prepareGoodsInStoreData($params)
{
    
    $query  = new Query();
    $query->select ([
            '{{%warehouse}}.id', 
             'grpGood',
            '{{%warehouse}}.title', 
            '{{%warehouse}}.price', 
            '{{%warehouse}}.[[amount]]',
            '{{%warehouse}}.[[inTransit]]',
            '({{%warehouse}}.[[amount]] * {{%warehouse}}.price) as Ve', 
            '{{%warehouse}}.ed'    
             ])
            ->from("{{%warehouse}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%warehouse}}.id)")             
            ->from("{{%warehouse}}")            
            ;
    
        $query->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
        $countquery->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
            
            
     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);

        if (!empty ($this->grpGood))
        {
            $listGrp = $this->getGrpGroup();     
            $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
            $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
        }


     }
   

    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
}


public function getGoodsInStoreData($params)  
{
    $this->prepareGoodsInStoreData($params);
    
    $dataList=$this->command->queryAll();  
     
    $fname = "uploads/headGoodsInStoreReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Товарная группа"),
        iconv("UTF-8", "Windows-1251","Товар"), 

        iconv("UTF-8", "Windows-1251","На складе"),  
        iconv("UTF-8", "Windows-1251","В пути"),         
        iconv("UTF-8", "Windows-1251","Заказано"), 

        iconv("UTF-8", "Windows-1251","Ед.изм"),

        iconv("UTF-8", "Windows-1251","Цена"),
        iconv("UTF-8", "Windows-1251","Стоимось"), 
        
        );
        fputcsv($fp, $col_title, ";"); 
        
    for ($i=0; $i< count($dataList); $i++)
    {        
         
         
     $strSql=   "Select sum({{%zakazContent}}.[[count]]) as N FROM {{%zakazContent}} 
                left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
                left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
                where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
                And warehouseRef = ".$dataList[$i]['id'];
     $inZakaz =  Yii::$app->db->createCommand($strSql)->queryScalar();              
         
    $list = array 
        (
        

        iconv("UTF-8", "Windows-1251",$dataList[$i]['grpGood']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  

        iconv("UTF-8", "Windows-1251",$dataList[$i]['amount']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['inTransit']),  
        iconv("UTF-8", "Windows-1251",$inZakaz),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['ed']),  
        

        iconv("UTF-8", "Windows-1251",$dataList[$i]['price']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['Ve']),  
        
        );
                
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
}
  
 public function getGoodsInStoreProvider($params)
   {
    
    $this->prepareGoodsInStoreData($params);
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'id', 
            'grpGood',
            'title', 
            'price', 
            'Ve', 
            'amount',            
            'ed',
            'inTransit'
            ],

            ],
            
        ]);
    return  $dataProvider;   
   }   
 

 
 /****************************************/
 /*  */
  public function printGoodsInStore($provider)
  {

  echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],


             [
                'attribute' => 'grpGood',
                'label' => 'Товарная группа',
                'format' => 'raw',
                'filter' => $this->getGrpGroup(),
                
            ],        

            
            [
                'attribute' => 'title',
                'label' => 'Товар',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'amount',
                'label' => 'На складе',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'inTransit',
                'label' => 'В пути',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'N',
                'label' => 'Заказано',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    
                $strSql=   "Select sum({{%zakazContent}}.[[count]]) as N FROM {{%zakazContent}} 
                left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
                left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
                where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
                And warehouseRef = ".$model['id'];
                 $val =  Yii::$app->db->createCommand($strSql)->queryScalar();              
                 return number_format($val, 2, '.', '&nbsp;');
                }

                
            ],    
            
            [
                'attribute' => 'ed',
                'label' => 'Ед.изм',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'price',
                'label' => 'Цена',
                'format' => 'raw',
                
            ],    
            
            [
                'attribute' => 'Vе',
                'label' => 'Стоимость',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                  return number_format($model['Ve'], 2, '.', '&nbsp;');
                }
            ],    
            
            
        ],
    ]
);
  
      
  }

/*************/  
 
  
/***************************/
  /*Товар в пути*/
public function prepareGoodsInTransitData($params)
   {
    $query  = new Query();
    $query->select ([
            '{{%warehouse}}.id', 
            '{{%warehouse}}.title', 
            'grpGood',
            '{{%warehouse}}.price', 
            '{{%warehouse}}.[[amount]]',
            '{{%warehouse}}.[[inTransit]]',
            '({{%warehouse}}.[[inTransit]] * {{%warehouse}}.price) as Vt', 
            '{{%warehouse}}.ed'    
             ])
            ->from("{{%warehouse}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%warehouse}}.id)")             
            ->from("{{%warehouse}}")            
            ;
    

//$this->debug[]= $this->detail;
    
        $query->andWhere(['!=', '{{%warehouse}}.inTransit', 0]);
        $countquery->andWhere(['!=', '{{%warehouse}}.inTransit', 0]);
            
     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        
         if (!empty ($this->grpGood))
        {
            $listGrp = $this->getGrpGroup();     
            $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
            $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
        }

        
     }

    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();


   
   } 
  
public function getGoodsInTransitData($params)
   {
    $this->prepareGoodsInTransitData($params);
   
       $dataList=$this->command->queryAll();  
     
    $fname = "uploads/headGoodsInTransitReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Товарная группа"),
        iconv("UTF-8", "Windows-1251","Товар"), 

        iconv("UTF-8", "Windows-1251","На складе"),  
        iconv("UTF-8", "Windows-1251","В пути"),         
        iconv("UTF-8", "Windows-1251","Заказано"), 

        iconv("UTF-8", "Windows-1251","Ед.изм"),

        iconv("UTF-8", "Windows-1251","Цена"),
        iconv("UTF-8", "Windows-1251","Стоимось"), 
        
        );
        fputcsv($fp, $col_title, ";"); 
        
    for ($i=0; $i< count($dataList); $i++)
    {        
         
         
     $strSql=   "Select sum({{%zakazContent}}.[[count]]) as N FROM {{%zakazContent}} 
                left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
                left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
                where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
                And warehouseRef = ".$dataList[$i]['id'];
     $inZakaz =  Yii::$app->db->createCommand($strSql)->queryScalar();              
         
    $list = array 
        (
        

        iconv("UTF-8", "Windows-1251",$dataList[$i]['grpGood']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  

        iconv("UTF-8", "Windows-1251",$dataList[$i]['amount']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['inTransit']),  
        iconv("UTF-8", "Windows-1251",$inZakaz),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['ed']),  
        

        iconv("UTF-8", "Windows-1251",$dataList[$i]['price']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['Vt']),  
        
        );
                
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           

   
   } 
   
 public function getGoodsInTransitProvider($params)
   {
    
    $this->prepareGoodsInTransitData($params);
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'id', 
            'title', 
            'grpGood',
            'price', 
            'Vt', 
            'amount',            
            'ed',
            'inTransit'
            ],

            ],
            
        ]);
    return  $dataProvider;   
   }   
 

 
 /****************************************/
 /*  */
  public function printGoodsInTransit($provider)
  {

  echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                   
             [
                'attribute' => 'grpGood',
                'label' => 'Товарная группа',
                'format' => 'raw',
                'filter' => $this->getGrpGroup(),
                
            ],        
                   
            [
                'attribute' => 'title',
                'label' => 'Товар',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'amount',
                'label' => 'На складе',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'inTransit',
                'label' => 'В пути',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'N',
                'label' => 'Заказано',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    
                $strSql=   "Select sum({{%zakazContent}}.[[count]]) as N FROM {{%zakazContent}} 
                left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
                left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
                where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
                And warehouseRef = ".$model['id'];
                 $val =  Yii::$app->db->createCommand($strSql)->queryScalar();              
                 return number_format($val, 2, '.', '&nbsp;');
                }

                
            ],    
            
            [
                'attribute' => 'ed',
                'label' => 'Ед.изм',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'price',
                'label' => 'Цена',
                'format' => 'raw',
                
            ],    
            
            [
                'attribute' => 'Vt',
                'label' => 'На сумму',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                  return number_format($model['Vt'], 2, '.', '&nbsp;');
                }
            ],    
            
            
        ],
    ]
);
  
      
  }

/*************/  
/**
    $strSql = "Select 
    {{%warehouse}}.id, 
    title, 
    price, 
    amount, 
    inTransit, 
    isAnalyse, 
    zakaz.inZakazN, 
    ifnull(SUM(supplyCount),0) as N, 
    TIMESTAMPDIFF(DAY, MIN(supplyDate), NOW()) as P
    
        from {{%warehouse}} 
        
    left join {{%supply}} on  {{%supply}}.wareRef = {{%warehouse}}.id     

    left join (
        Select sum({{%zakazContent}}.[[count]]) as inZakazN, warehouseRef FROM {{%zakazContent}} 
        left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
        left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
        where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
        group by warehouseRef
        ) as zakaz on zakaz.warehouseRef = {{%warehouse}}.id
        group by {{%warehouse}}.id, title";
**/

 
 /*Наполнение склада*/
 
  public function prepareGoodsInPredictData($params)
   {

    $strJoin = "(Select sum({{%zakazContent}}.[[count]]) as inZakazN, warehouseRef FROM {{%zakazContent}} 
        left join {{%zakaz}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
        left join {{%schet}} on {{%zakazContent}}.refZakaz = {{%schet}}.refZakaz
        where  ({{%zakaz}}.isActive = 1 or ({{%schet}}.isSchetActive= 1 AND {{%schet}}.summSupply = 0))
        group by warehouseRef
        ) as zakaz";
    
    $query  = new Query();
    $query->select ([
            '{{%warehouse}}.id', 
            '{{%warehouse}}.title', 
            '{{%warehouse}}.price', 
            'grpGood',
            'amount', 
            'inTransit', 
            'isAnalyse', 
            'zakaz.inZakazN', 
            '{{%warehouse}}.ed',
            'ifnull(SUM(supplyCount),0) as N',
            'TIMESTAMPDIFF(DAY, MIN(supplyDate), NOW()) as P',
            'TIMESTAMPDIFF(DAY, MAX(supplyDate), NOW()) as PM',
            'MAX(supplyDate) as LASTP',
            'avRashod'
             ])
            ->from("{{%warehouse}}")
            ->leftJoin('{{%supply}}','{{%supply}}.wareRef = {{%warehouse}}.id ')            
            ->leftJoin($strJoin,'zakaz.warehouseRef = {{%warehouse}}.id')
            ->groupBy ('{{%warehouse}}.id, {{%warehouse}}.title, {{%warehouse}}.price, {{%warehouse}}.ed');
            
    $countquery  = new Query();
    $countquery->select ("count({{%warehouse}}.id)")             
            ->from("{{%warehouse}}")
            ;
    
            
            

     if (($this->load($params) && $this->validate())) {

     
        $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->title]);
        
        if (!empty ($this->grpGood))
        {
            $listGrp = $this->getGrpGroup();     
            $query->andFilterWhere(['=', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
            $countquery->andFilterWhere(['=', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGood] ]);
        }
        
        
        if (!empty($this->isAnalyse))
        {
        switch ($this->isAnalyse)
        {
          case 2:
                 $query->andFilterWhere(['<>', 'isAnalyse', 0]);
            $countquery->andFilterWhere(['<>', 'isAnalyse', 0]);
          break;  

          case 3:
                 $query->andFilterWhere(['=', 'isAnalyse', 0]);
            $countquery->andFilterWhere(['=', 'isAnalyse', 0]);
          break;  
              
        }
        }
        
        
     }else
     {
           $this->isAnalyse=2; 
                 $query->andFilterWhere(['<>', 'isAnalyse', 0]);
            $countquery->andFilterWhere(['<>', 'isAnalyse', 0]);
     }
   
   
//$this->debug[]= $query->createCommand()->getRawSql();
    $this->command = $query->createCommand(); 
    //$listAll = $query->createCommand()->queryAll(); // Фиг знает почему сбоит запрос на count
    $this->count = $countquery->createCommand()->queryScalar();
    //$count = count ($listAll);

   $dataList=$query->createCommand()->queryAll();  
   $this->sumRemain=0;
   $this->monthRash=0;
    for ($i=0; $i< count($dataList); $i++)
    {        

        $this->sumRemain+= $dataList[$i]['amount'] + $dataList[$i]['inTransit'] ;
        if ($dataList[$i]['P'] > 0)
        {
            
         if (empty($dataList[$i]['avRashod']))    
            $this->monthRash+= 30*($dataList[$i]['N']/$dataList[$i]['P']);
        else
            $this->monthRash+= 30*$dataList[$i]['avRashod'];
        }

    }
     
    

   } 
/***********************************************/   

   public function getGoodsInPredictData ($params,$codePage)        
   {
        $this->prepareGoodsInPredictData($params);        

        $dataList=$this->command->queryAll();  
     
    $fname = "uploads/headGoodsInPredictReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", $codePage,"Товарная группа"),
        iconv("UTF-8", $codePage,"Товар"), 

        iconv("UTF-8", $codePage,"На складе"),
        iconv("UTF-8", $codePage,"В Пути"),
        iconv("UTF-8", $codePage,"В Заказах"), 

        iconv("UTF-8", $codePage,"Расход"),
        iconv("UTF-8", $codePage,"Ед.изм"), 

        iconv("UTF-8", $codePage,"Цена"),
        iconv("UTF-8", $codePage,"Наполнение"),
        iconv("UTF-8", $codePage,"За 30 дней (руб)"),
        iconv("UTF-8", $codePage,"Факт (руб)"),  
        iconv("UTF-8", $codePage,"Скорость (руб)"),  
        iconv("UTF-8", $codePage,"Значимость"), 

        
        );
        fputcsv($fp, $col_title, ";"); 
        
    for ($i=0; $i< count($dataList); $i++)
    {        
       $rashod ="";
       if ($dataList[$i]['P'] == 0 || $dataList[$i]['N'] == 0) $rashod= "Нет отгрузок";                     
       else $rashod = number_format(($dataList[$i]['N'])/$dataList[$i]['P'], 2, '.', '&nbsp;');

       $napol ="";
       $napol_price = "";

       if ($dataList[$i]['N'] == 0 || $dataList[$i]['P'] == 0) $napol ="";
       else {
             $avInDay = $dataList[$i]['N']/$dataList[$i]['P'];//Средняя убыль в день                        
             if ($avInDay == 0)  $napol ="";
             else {
                        $waitInMonth = $avInDay*30 ; //Ожидаем в этом месяце 

                        $curV = $dataList[$i]['amount'] + $dataList[$i]['inTransit'] ;
                        if ($waitInMonth > 0)$rate = $curV/($waitInMonth );
                        else $rate =0;
                     
/*                        if     ($rate <= 0)   $score = 0;
                        elseif ($rate > 0 && $rate <= 1.0  )   $score = $rate;
                        elseif ($rate < 2.0  )                 $score = 2.0-$rate;
                        elseif ($rate > 2.0)   $score = 0;*/
                        
                        if (empty($dataList[$i]['avRashod']))
                        {
                            $napol  ="*В месяц: ".number_format($waitInMonth, 2, '.', '');
                            $napol .=" Запас ".$curV." на: ".number_format($curV/$avInDay, 0, '.', '')." дней";                        
                        
                        }
                        else
                        {
                        $napol ="В месяц: ".number_format($dataList[$i]['avRashod']*30, 2, '.', '');
                        $napol .=" Запас ".$curV." на: ".number_format($curV/$dataList[$i]['avRashod'], 0, '.', '')." дней";                        
                        }
                        $napol_price = $dataList[$i]['avRashod']*30*$dataList[$i]['price'];
             }
             
           if (empty($dataList[$i]['avRashod']))                                               
                            $avInDay = $dataList[$i]['N']/$dataList[$i]['P'];//Средняя убыль в день                        
                        else     
                            $avInDay =$dataList[$i]['avRashod'];
                        
                        $fact = min(($dataList[$i]['amount'] + $dataList[$i]['inTransit']), $avInDay);             
                        $vel  = $fact*$dataList[$i]['price'];
                        $fact  =$fact*30*$dataList[$i]['price'];
                        
                        

       }
       

       
    $list = array 
        (
        

        iconv("UTF-8", $codePage,$dataList[$i]['grpGood']),  
        iconv("UTF-8", $codePage,$dataList[$i]['title']),  

        iconv("UTF-8", $codePage,$dataList[$i]['amount']),  
        iconv("UTF-8", $codePage,$dataList[$i]['inTransit']),  
        iconv("UTF-8", $codePage,$dataList[$i]['inZakazN']),  

        iconv("UTF-8", $codePage,$dataList[$i]['avRashod']),        
        iconv("UTF-8", $codePage,$dataList[$i]['ed']),  

        iconv("UTF-8", $codePage,$dataList[$i]['price']),  
        iconv("UTF-8", $codePage,$napol),
        iconv("UTF-8", $codePage,$napol_price),  
        iconv("UTF-8", $codePage,$fact),  
        iconv("UTF-8", $codePage,$vel),    
        iconv("UTF-8", $codePage,$dataList[$i]['isAnalyse']),  
        
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
   

/***********************************************/   

   public function getGoodsInPredictXML ()        
   {
        $this->prepareGoodsInPredictData("");        
        $dataList=$this->command->queryAll();  
    
    $xmlRes="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xmlRes.="<wharehouse>\n";
      
    for ($i=0; $i< count($dataList); $i++)
    {        
       $rashod ="";
       if ($dataList[$i]['P'] == 0 || $dataList[$i]['N'] == 0) $rashod= "Нет отгрузок";                     
       else $rashod = number_format(($dataList[$i]['N'])/$dataList[$i]['P'], 2, '.', '&nbsp;');

       $napol ="";
       $napol_price = "";

       if ($dataList[$i]['N'] == 0 || $dataList[$i]['P'] == 0) $napol ="";
       else {
             $avInDay = $dataList[$i]['N']/$dataList[$i]['P'];//Средняя убыль в день                        
             if ($avInDay == 0)  $napol ="";
             else {
                        $waitInMonth = $avInDay*30 ; //Ожидаем в этом месяце 

                        $curV = $dataList[$i]['amount'] + $dataList[$i]['inTransit'] ;
                        if ($waitInMonth > 0)$rate = $curV/($waitInMonth );
                        else $rate =0;
                     
/*                        if     ($rate <= 0)   $score = 0;
                        elseif ($rate > 0 && $rate <= 1.0  )   $score = $rate;
                        elseif ($rate < 2.0  )                 $score = 2.0-$rate;
                        elseif ($rate > 2.0)   $score = 0;*/
                        
                        if (empty($dataList[$i]['avRashod']))
                        {
                            $napol  ="*В месяц: ".number_format($waitInMonth, 2, '.', '');
                            $napol .=" Запас ".$curV." на: ".number_format($curV/$avInDay, 0, '.', '')." дней";                        
                        
                        }
                        else
                        {
                        $napol ="В месяц: ".number_format($dataList[$i]['avRashod']*30, 2, '.', '');
                        $napol .=" Запас ".$curV." на: ".number_format($curV/$dataList[$i]['avRashod'], 0, '.', '')." дней";                        
                        }
                        $napol_price = $dataList[$i]['avRashod']*30*$dataList[$i]['price'];
             }
             
           if (empty($dataList[$i]['avRashod']))                                               
                            $avInDay = $dataList[$i]['N']/$dataList[$i]['P'];//Средняя убыль в день                        
                        else     
                            $avInDay =$dataList[$i]['avRashod'];
                        
                        $fact = min(($dataList[$i]['amount'] + $dataList[$i]['inTransit']), $avInDay);             
                        $vel  = $fact*$dataList[$i]['price'];
                        $fact  =$fact*30*$dataList[$i]['price'];
                        
                        

       }
       
       $xmlRes.="<ware>\n";    
       $xmlRes.= "  <id>".$dataList[$i]['id']."</id>\n";
       $xmlRes.= "  <waregroup>".$dataList[$i]['grpGood']."</waregroup>\n";       
       $xmlRes.= "  <waretitle>".$dataList[$i]['title']."</waretitle>\n";
       $xmlRes.= "  <amount>".$dataList[$i]['amount']."</amount>\n";
       $xmlRes.= "  <inTransit>".$dataList[$i]['inTransit']."</inTransit>\n";
       $xmlRes.= "  <inZakazN>".$dataList[$i]['inZakazN']."</inZakazN>\n";
       $xmlRes.= "  <avRashod>".$dataList[$i]['avRashod']."</avRashod>\n";
       $xmlRes.= "  <ed>".$dataList[$i]['ed']."</ed>\n";       
       $xmlRes.= "  <price>".$dataList[$i]['price']."</price>\n";  
       
       $xmlRes.= "  <napol>".$napol."</napol>\n";
       $xmlRes.= "  <napolcash>".$napol_price."</napolcash>\n";

       $xmlRes.= "  <fact>".$fact."</fact>\n";
       $xmlRes.= "  <velocity>".$vel."</velocity>\n";
       $xmlRes.= "  <isAnalyse>".$dataList[$i]['isAnalyse']."</isAnalyse>\n";
       
       $xmlRes.="</ware>\n";    
       
     
   }
   $xmlRes.="</wharehouse>\n";   
       return $xmlRes;           
   }
   
      
/***********************************************/   
 public function getGoodsInPredictProvider($params)
   {
    
    $this->prepareGoodsInPredictData($params);
    
    $pageSize = 10;
    
      $curUser=Yii::$app->user->identity;
      if (($curUser->roleFlg & 0x0020)) {$pageSize = 50;}

    
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [                    
            'id', 
            'grpGood',
            'title', 
            'price', 
            'amount', 
            'ed',
            'inTransit', 
            'isAnalyse', 
            'inZakazN',  
            'avRashod'            
            ],
            'defaultOrder' => [    'grpGood' => SORT_ASC ],
            ],
            
        ]);
        
        
        
    return  $dataProvider;   
   }   
 /****************************************/
 /* Наполнение склада -  отобразить */
  public function printGoodsInPredict($provider)
  {

  
      $curUser=Yii::$app->user->identity;
//      if (($curUser->roleFlg & 0x0020)) {$pageSize = 50;}
//$('#modalEditAvValue').modal('show');
/*
$('#modalEditAvValue').modal({
      show:true,
      width:800px
    });

   <style>
    .autoModal.modal .modal-body{
    max-height: 100%;
    }
 </style>

    document.getElementById('ware_rashod_frame').src = 'index.php?r=store/ware-av-rashod&noframe=1&id='+id;
   $('#modalEditAvValue').modal('show');
    
*/

  echo "
  <script>  
  function openEditAvValue(id)
  {         
    url = 'store/ware-av-rashod&noframe=1&id='+id;
    openWin(url,'childwin');
  }  
  </script>
  ";
/*
" <b>Максимальный скор:</b> ".number_format($this->maxScore,'3','.','').
" &nbsp;<b>Накопленный скор: </b> ".number_format($this->sumScore,'3','.','').
" &nbsp;<b>Отношение:</b> ".number_format($this->rateScore,'3','.','').
*/

echo "<p>". 
" &nbsp;<b>Суммарный остаток:</b> ".number_format($this->sumRemain,'2','.','&nbsp;').
" &nbsp;<b>Суммарный расход:</b> ".number_format($this->monthRash,'2','.','&nbsp;').
"</p>";
  
  
   echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
             [
                'attribute' => 'grpGood',
                'label' => 'Товарная группа',
                'headerOptions' => [
                    'style' => 'width: 95px;',
                ], 
                'format' => 'raw',
                'filter' => $this->getGrpGroup(),
                
            ],        

            [
                'attribute' => 'title',
                'label' => 'Товар',
                'format' => 'raw',
            ],    

            [
                'attribute' => 'amount',
                'label' => 'На складе',
                'format' => 'raw',
            ],    
     
            [
                'attribute' => 'inTransit',
                'label' => 'В Пути',
                'format' => 'raw',
            ],    
     
            
            [
                'attribute' => 'inZakazN',
                'label' => 'В Заказах',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'avRashod',
                'label' => 'Расход',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                 
                 $action ="openEditAvValue(".$model['id'].")";
                 if (empty($model['avRashod'])) return "<div class='gridcell' onclick='".$action."'>&nbsp;</div>";   
                 return "<div class='gridcell' onclick='".$action."'>".number_format(($model['avRashod']), 2, '.', '&nbsp;')."</div>";
                }
            ],    
            
            
            [
                'attribute' => 'ed',
                'label' => 'Ед.изм',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'price',
                'label' => 'Цена',
                'format' => 'raw',
                
            ],
            
            [
                'attribute' => '',
                'label' => 'Наполнение',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                  
                  
                        if ($model['N'] == 0) return "&nbsp;"; // нет продаж нет статистики                        
                        if ($model['P'] == 0) return "&nbsp;";  // Перестрахуемся, все равно тогда данные не верны
       
                        $avInDay = $model['N'] / $model['P'];//Средняя убыль в день                        
                        if ($avInDay == 0)  return "&nbsp;";  // Нет прогноза       
                        $waitInMonth = $avInDay*30 ; //Ожидаем в этом месяце 

                        $curV = $model['amount'] + $model['inTransit'] ;
                        if ($waitInMonth > 0)$rate = $curV/($waitInMonth );
                        else $rate =0;
                     
                        if     ($rate <= 0)   $score = 0;
                        elseif ($rate > 0 && $rate <= 1.0  )   $score = $rate;
                        elseif ($rate < 2.0  )                 $score = 2.0-$rate;
                        elseif ($rate > 2.0)   $score = 0;
                        
                        if (empty($model['avRashod']))
                        {
                            $ret ="В&nbsp;месяц:&nbsp;<font color='Brown'>".number_format($waitInMonth, 2, '.', '&nbsp;')."&nbsp;".$model['ed']."</font><br>";
                            $ret .="На&nbsp;".number_format(($waitInMonth*$model['price']), 0, '.', '&nbsp;')."руб.";
                            $ret .="<br>Запас&nbsp;".$curV."&nbsp;на:&nbsp;".number_format($curV/$avInDay, 0, '.', '&nbsp;')."&nbsp;дней";                        
                        return $ret;
                        }

                        $ret ="В&nbsp;месяц:&nbsp;<b><font color='green'>".number_format($model['avRashod']*30, 2, '.', '&nbsp;')."&nbsp;".$model['ed']."</font></b><br>";
                        $ret .="На&nbsp;".number_format(($model['avRashod']*30*$model['price']), 0, '.', '&nbsp;')."руб.";
                        $ret .="<br>Запас&nbsp;".$curV."&nbsp;на:&nbsp;".number_format($curV/$model['avRashod'], 0, '.', '&nbsp;')."&nbsp;дней";                        
                        return $ret;
                        
                }
            ],    

            [
                'attribute' => '',
                'label' => 'Факт',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                  
                  /*Min( (На складе + В пути) * цена ; месяц )  */
                        if ($model['N'] == 0) return "&nbsp;"; // нет продаж нет статистики                        
                        if ($model['P'] == 0) return "&nbsp;";  // Перестрахуемся, все равно тогда данные не верны
       
                        if (empty($model['avRashod']))                                               
                            $avInDay = $model['N'] / $model['P'];//Средняя убыль в день                        
                        else     
                            $avInDay =$model['avRashod'];
                        
                        $fact = min(($model['amount'] + $model['inTransit']), $avInDay);             
                        $ret  ="В месяц<br>&nbsp;".number_format(($fact*30*$model['price']), 0, '.', '&nbsp;')."руб.<br>";
                        $ret .="Скорость<br>&nbsp;".number_format(($fact*$model['price']), 0, '.', '&nbsp;')."руб.";
                        
                        return $ret;
                        
                }
            ],    
            
                        
            [
                'attribute' => 'isAnalyse',
                'label' => 'Значимость',
                'format' => 'raw',
                'filter'=>array("1" => "Все", "2"=>"В анализе","3" => "Не анализ.",),
                 'value' => function ($model, $key, $index, $column) use ($curUser)
                 {
                    if (!($curUser->roleFlg & 0x0020)) {return number_format($model['isAnalyse'], 2, '.', '&nbsp;');} 

                    if ($model['isAnalyse'] == 0) { $val = "Не исполь."; $lbl = 'Вкл.';}
                                             else { $val = number_format($model['isAnalyse'], 2, '.', '&nbsp;');  $lbl = 'Выкл.';}
                    return "<nobr>".$val."&nbsp;<input class='btn btn-primary small_btn' 
                    type=button value='".$lbl."' onclick='javascript:switchGoodAnalyze(".$model['id'].");'></nobr>";
                 }                 
            ],
                        
        ],
    ]
);
  
/*
Modal::begin([
    'id' =>'modalEditAvValue',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Средний расход</h4>',
    'class' =>'modal-lg autoModal'

]);

echo "
   
    <iframe id='ware_rashod_frame' width='525px' height='600px' frameborder='no'   src='index.php?r=store/ware-av-rashod&noframe=1&id=0' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
     </iframe>      

";


Modal::end();
 */

  
 
}

 /*************************************************************/
 /************* Документы поставщика **************************/
 
   public function prepareSupplierSchetListData($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%supplier_schet_header}}.id', 
            '{{%supplier_schet_header}}.schetNum', 
            '{{%supplier_schet_header}}.schetDate', 
            '{{%supplier_schet_header}}.orgTitle', 
            '{{%supplier_schet_header}}.refOrg'
             ])
            ->from("{{%supplier_schet_header}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%supplier_schet_header}}.refOrg")
            ->leftJoin("{{%supplier_schet_content}}","{{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef")
            ->distinct()
            ->where ('isActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
         
            
    $countquery  = new Query();
    $countquery->select ("count({{%supplier_schet_header}}.id)")               
            ->from("{{%supplier_schet_header}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%supplier_schet_header}}.refOrg")
            ->leftJoin("{{%supplier_schet_content}}","{{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef")
            ->distinct()
            ->where ('isActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
            ;
                
     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        
        $query->andFilterWhere(['like', 'goodTitle', $this->good]);
        $countquery->andFilterWhere(['like', 'goodTitle', $this->good]);

        
     }
   
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
   } 

/***********************************************/   
 public function getSupplierSchetListProvider($params)
   {
    
    $this->prepareSupplierSchetListData($params);
    
    $pageSize = 10;
    
      $curUser=Yii::$app->user->identity;
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [                    
            'id', 
            'schetNum', 
            'schetDate', 
            'orgTitle', 
  
            ],
            'defaultOrder' => [    'schetDate' => SORT_DESC ],
            ],
            
        ]);
                
    return  $dataProvider;   
   }   
 /****************************************/
 /* отобразить */
  public function printSupplierSchetList ($provider)
  {
  
   echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
           [
                'attribute' => 'Счет',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $schet_sum = Yii::$app->db->createCommand('SELECT sum(goodSumm) from {{%supplier_schet_content}} where schetRef=:refSchet', 
                [':refSchet' => $model['id'],])->queryScalar();
        
                $ret = "№ ".$model['schetNum']." от ".date ('d.m.Y', strtotime($model['schetDate']));                 
                $ret .= "<br>на сумму: ".number_format($schet_sum,2,'.','&nbsp;');
                return $ret;
                },
            ],        

            
            [
                'attribute' => 'orgTitle',
                'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    if ($model['refOrg'] > 0)
                    {                    
                        $ret= "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['orgTitle']."</a>";
                        //if ($model['isOrgActive'] == 0) $ret = "<del>".$ret."</del>";
                    }
                    else $ret =  $model['orgTitle'];
                    
                    return $ret;
                },
            ],        

            [
                'attribute' => 'good',
                'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $res = Yii::$app->db->createCommand('SELECT goodTitle, goodCount, goodEd from {{%supplier_schet_content}} where schetRef=:refSchet ORDER BY goodSumm ', 
                [':refSchet' => $model['id'],])->queryAll();
               
                $ret="";
                for ($i=0; $i< count($res); $i++ )
                {
                   $ret.= $res[$i]['goodTitle']."<br>"; 
                   if ($i > 2) break;
                }
                if ($i < count($res))$ret .= "...";                
                return $ret;
                },
                
            ],    
            
            
            [
                'attribute' => 'Поставлено',
                'label' => 'Поставлено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $res = Yii::$app->db->createCommand('SELECT sum(wareSumm)  from {{%supplier_wares}} where supplierSchetRef=:supplierSchetRef ', 
                    [':supplierSchetRef' => $model['id'],])->queryScalar();
                    $ret = "".number_format($res,2,'.','&nbsp;');
                return $ret;
                },
            ],    

            [
                'attribute' => 'Оплачено',
                'label' => 'Оплачено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $res = Yii::$app->db->createCommand('SELECT sum(oplateSumm)  from {{%supplier_oplata}} where supplierSchetRef=:supplierSchetRef ', 
                    [':supplierSchetRef' => $model['id'],])->queryScalar();
                    $ret = " ".number_format($res,2,'.','&nbsp;');
                return $ret;
                },                
            ],
            
                        
        ],
    ]
);
}

/*************************************************************/
public function prepareSupplierListDataOld($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%orglist}}.id', 
            'title as orgTitle', 
            'isOrgActive',
            'if(orgGrpRef = 0,title, {{%org_group}}.orgGrpTitle) as grpTitle'
            ])
            ->from("{{%orglist}}")
            ->leftJoin("{{%supplier_wares}}","{{%orglist}}.id = {{%supplier_wares}}.refOrg")            
            ->leftJoin("{{%org_group}}","{{%orglist}}.orgGrpRef = {{%org_group}}.id")            
            ->distinct()
            ->where ('isOrgActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
         
            
    $countquery  = new Query();
    $countquery->select ("count({{%orglist}}.id)")               
            ->from("{{%orglist}}")
            ->leftJoin("{{%supplier_wares}}","{{%orglist}}.id = {{%supplier_wares}}.refOrg")            
            ->distinct()
            ->where ('isOrgActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
            ;
                
     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);

        $query->andFilterWhere(['like', 'WareTitle', $this->good]);
        $countquery->andFilterWhere(['like', 'WareTitle', $this->good]);
        
     }
   
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
   } 

/************* Перечень поставщиков **************************/
 
   public function prepareSupplierListData($params)
   {
    
    $query  = new Query();
    $query->select ([
            'if(orgGrpRef = 0,{{%orglist}}.id,orgGrpRef) as id', 
            'orgGrpRef',
            'if(orgGrpRef = 0,title, {{%org_group}}.orgGrpTitle) as grpTitle'
            ])
            ->from("{{%orglist}}")
            ->leftJoin("{{%control_purch_content}}","{{%orglist}}.id = {{%control_purch_content}}.orgRef")            
            ->leftJoin("{{%org_group}}","{{%orglist}}.orgGrpRef = {{%org_group}}.id")
            ->leftJoin("{{%org_deals}}","{{%orglist}}.id = {{%org_deals}}.refOrg")
             ->leftJoin("{{%bank_op_article}}","{{%bank_op_article}}.id = {{%org_deals}}.articleRef")
            ->distinct()
            ->where ('isOrgActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
         
            
    /*$countquery  = new Query();
    $countquery->select ("count({{%orglist}}.id)")               
            ->from("{{%orglist}}")
            ->leftJoin("{{%supplier_wares}}","{{%orglist}}.id = {{%supplier_wares}}.refOrg")            
            ->distinct()
            ->where ('isOrgActive = 1 AND ({{%orglist}}.contragentType & 0x1)');
            ;*/
                
     $query->andFilterWhere(['=', '{{%org_deals}}.state', 1]);
     $query->andFilterWhere(['=', '{{%bank_op_article}}.grpRef', 2]);
                
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'if(orgGrpRef = 0,title, {{%org_group}}.orgGrpTitle)', $this->grpTitle]);
        //$countquery->andFilterWhere(['like', 'grpTitle', $this->grpTitle]);

     
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
//        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);

        $query->andFilterWhere(['like', 'purchTitle', $this->good]);
        //$countquery->andFilterWhere(['like', 'WareTitle', $this->good]);
        
     }
   
   
   
    $this->command = $query->createCommand(); 
    //$this->count = $countquery->createCommand()->queryScalar();
    $this->count = count ($query->createCommand()->queryAll());
   } 

/***********************************************/   
 public function getSupplierListProvider($params)
   {
    
    $this->prepareSupplierListData($params);
    
    $pageSize = 10;
    
      $curUser=Yii::$app->user->identity;
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [                    
            'id',   
            'grpTitle'
            ],
            'defaultOrder' => [    'grpTitle' => SORT_ASC ],
            ],
            
        ]);
                
    return  $dataProvider;   
   }   
 /****************************************/
 /*  отобразить */
  public function printSupplierList ($provider)
  {
  
   echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
                'attribute' => 'grpTitle',
                'label' => 'Группа компаний',
                'format' => 'raw'                                    
            ],        

            
            [
                'attribute' => 'orgTitle',
                'label' => 'Поставщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $ret= "<ul>";
                    if ($model['orgGrpRef']==0)
                      {             
                  
                        $ret.= "<li><a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\")' >".$model['grpTitle']."</a>";
                      }
                      else
                      {
                       $ret= "<ul>";
                       $list = Yii::$app->db->createCommand('SELECT id, title  from {{%orglist}} where orgGrpRef=:orgGrpRef  ORDER BY id DESC ', 
                       [':orgGrpRef' => $model['id'],])->queryAll();           
                       for($i=0;$i<count($list);$i++ )
                          $ret .= "<li><a href='#' onclick='openWin(\"site/org-detail&orgId=".$list[$i]['id']."\")' >".$list[$i]['title']."</a>";
                      }
                   $ret.= "</ul>";      
                   return $ret;
                },
            ],        
         
            
            [
                'attribute' => 'good',
                'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
               if ($model['orgGrpRef']==0)
               {                 
                $res = Yii::$app->db->createCommand('SELECT purchTitle, sum(purchSum) as S from {{%control_purch_content}} where orgRef=:refOrg group by purchTitle  ORDER BY S DESC ', 
                [':refOrg' => $model['id'],])->queryAll();
               }else{
                $res = Yii::$app->db->createCommand('SELECT purchTitle, sum(purchSum) as S from {{%control_purch_content}}
                left join {{%orglist}} on {{%orglist}}.id = {{%control_purch_content}}.orgRef
                where orgGrpRef=:orgGrpRef group by purchTitle  ORDER BY S DESC ', 
                [':orgGrpRef' => $model['orgGrpRef']])->queryAll();                   
               }
               
               
                $ret="";
                for ($i=0; $i< count($res); $i++ )
                {
                   $ret.= $res[$i]['purchTitle']."<br>"; 
                   if ($i > 3) break;
                }
                if ($i < count($res))$ret .= "...";                
                return $ret;
                },
                
            ],    
            
            
            [
                'attribute' => '-',
                'label' => 'Поставлено',
                'format' => 'raw',
                //'filter' => [0 => 'Все', 1 => '>0', 2 => '=0'],
                'value' => function ($model, $key, $index, $column) {
                if ($model['orgGrpRef']==0)
                {                       
                    $res = Yii::$app->db->createCommand('SELECT sum(purchSum)  from {{%control_purch_content}} where orgRef=:refOrg ', 
                    [':refOrg' => $model['id'],])->queryScalar();                
                }else{    
                $res = Yii::$app->db->createCommand('SELECT sum(purchSum) as S from {{%control_purch_content}}
                left join {{%orglist}} on {{%orglist}}.id = {{%control_purch_content}}.orgRef
                where orgGrpRef=:orgGrpRef ', 
                [':orgGrpRef' => $model['orgGrpRef']])->queryScalar();                                
                }
                $ret = "".number_format($res,2,'.','&nbsp;');
                return $ret;
                },
            ],    

            [
                'attribute' => 'Оплачено',
                'label' => 'Оплачено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                if ($model['orgGrpRef']==0)
                {                                          
                    $res = Yii::$app->db->createCommand('SELECT sum(oplateSumm)  from {{%supplier_oplata}} where refOrg=:refOrg ', 
                    [':refOrg' => $model['id'],])->queryScalar();
 
                }else{    
                $res = Yii::$app->db->createCommand('SELECT sum(oplateSumm)  from {{%supplier_oplata}} 
                left join {{%orglist}} on {{%orglist}}.id = {{%supplier_oplata}}.refOrg
                where orgGrpRef=:orgGrpRef', 
                [':orgGrpRef' => $model['orgGrpRef']])->queryScalar();                                
                
                }                
                $ret = " ".number_format($res,2,'.','&nbsp;');
                return $ret;
                },                
            ],
            
                        
        ],
    ]
);
 
}
 
/*************************************************************/
/************* Перечень товаров **************************/
 
   public function prepareSupplierGoodsData($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%warehouse}}.id',
            'title as good',            
            'amount',
            'ed',
            'wareGrpTitle', 
            'isActive',       
            ])
            ->from("{{%warehouse}}")            
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id= {{%warehouse}}.useRef")            
            ->leftJoin("{{%control_purch_content}}","{{%control_purch_content}}.goodRef = {{%warehouse}}.id")                                                
            ->groupby('{{%warehouse}}.id')
            ->distinct();
            
             $query->andWhere(['=', '{{%ware_use}}.isInUse', 1]);

       $query->andWhere("(isActive =1 OR {{%control_purch_content}}.id is not null )");                             
       //$query->andWhere("isActive =1 ");                             
     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'title', $this->good]);
        $query->andFilterWhere(['like', '{{%control_purch_content}}.orgTitle', $this->orgTitle]);
     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);
   } 

/***********************************************/   
 public function getSupplierGoodsProvider($params)
   {
    
    $this->prepareSupplierGoodsData($params);
    
    $pageSize = 10;
    
      $curUser=Yii::$app->user->identity;
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [                    
            'id,',
            'good',            
            'amount',
            'price',
            'relizePrice',
            'ed',
            'grpGood',
            'id'
                        ],
            'defaultOrder' => [    'grpGood' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
 /****************************************/
 /*  отобразить */
  public function printSupplierGoods ($provider)
  {
  
   echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            
           [
                'attribute' => 'id',
                'label' => 'id',
                'format' => 'raw',
                'filter' => false                
            ],        
            
            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Товарная группа',
                'format' => 'raw',
                'filter' => $this->getGrpGroup(),
                
            ],        

            [
                'attribute' => 'good',
                'label' => 'Товар',
                'format' => 'raw',
                
            ],    

            [
                'attribute' => 'amount',
                'label' => 'На складе',
                'format' => 'raw',
            ],    
     
            [
                'attribute' => 'ed',
                'label' => 'Ед.изм',
                'format' => 'raw',
            ],    

            
            [
                'attribute' => 'price1',
                'label' => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                $strSql= "select  purchSum, purchCount, purchDate from {{%control_purch_content}} where                 
                purchCount > 0 AND purchSum > 0
                 AND   goodRef = :wareRef ORDER BY purchDate DESC LIMIT 1";
                $res = Yii::$app->db->createCommand($strSql, [':wareRef' => $model['id'],])->queryAll();
                if(empty($res)) return;
                $ret= number_format( $res[0]['purchSum']/$res[0]['purchCount'], '2','.','&nbsp;')."<br> ".date("d.m.Y", strtotime($res[0]['purchDate']));
                return $ret;
                },
                
            ],
            

            [
                'attribute' => 'orgTitle',
                'label' => 'Поставщики',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                $strSql= "select  distinct orgTitle, {{%orglist}}.id,{{%orglist}}.title from {{%control_purch_content}}
                left join  {{%orglist}} on {{%control_purch_content}}.orgRef = {{%orglist}}.id                 
                where                         
                         {{%control_purch_content}}.goodRef = :wareRef
                ORDER BY purchDate DESC";
                          
                          
                $res = Yii::$app->db->createCommand($strSql, [':wareRef' => $model['id'],])->queryAll();
              //  return print_r($res, true);
                $ret="";
                for ($i=0; $i< count($res); $i++ )
                {   
                   if ($i==0) $ret.="<b>";
                   if (empty($res[$i]['id']))
                       $ret.= "<div>".$res[$i]['orgTitle']."</div>";                    
                   else 
                       $ret.= "<div class='clickable' onclick='openWin(\"site/org-detail&orgId=".$res[$i]['id']."\")' >".$res[$i]['title']."</div>";                    
                   if ($i==0) $ret.="</b>";
                   
                }
                //if ($i < count($res))$ret .= "...";                
                return $ret;
                },
                
            ],    
                        
        ],
    ]
);
 
}
 /***************************************/
  public function getWarePrihodListProvider($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supplier_wares}}.id)")
                  ->from("{{%supplier_wares}}")                  
                 ;
                  
     $query->select([ 
        'requestDate',
        'wareSumm', 
        'wareCount',
        'wareEd',    
        'wareTitle', 
        'orgTitle', 
        'requestNum',         
        ])->from("{{%supplier_wares}}")                  
                  ->distinct();      

                  
      $query->andWhere(['=', '{{%supplier_wares}}.wareRef', $this->id]);
      $countquery->andWhere(['=', '{{%supplier_wares}}.wareRef', $this->id]);
                    
                  
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
        
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
                'requestDate',
                'wareSumm', 
                'wareCount',
                'wareEd',    
                'wareTitle', 
                'orgTitle', 
                'requestNum',         
            ],
            'defaultOrder' => ['requestDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
 } 

public function getWareRashodListProvider($params) 
{

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supply}}.id)")
                  ->from("{{%supply}}")                                
                 ;
                  
     $query->select([ 
        'supplyDate',
        'supplySumm', 
        'supplyCount',
        'supplyEd',    
        'supplyGood', 
        'orgTitle', 
        'schetNum',         
        'schetDate',         
        ])->from("{{%supply}}")                  
                  ->distinct();      

                  
      $query->andWhere(['=', '{{%supply}}.wareRef', $this->id]);
      $countquery->andWhere(['=', '{{%supply}}.wareRef', $this->id]);
                    
                  
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
        
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

      

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                'supplyDate',
                'supplySumm', 
                'supplyCount',
                'supplyEd',    
                'supplyGood', 
                'orgTitle', 
                'schetNum',         
                'schetDate',         
            ],
            'defaultOrder' => ['supplyDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
         
    
}
 
 
  
/**/    
 }
 
