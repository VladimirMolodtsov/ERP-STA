<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;


use app\modules\bank\models\TblBuhStatistics;
use app\modules\bank\models\TblCalendarEvent;
use app\modules\bank\models\TblBuhCheckCalc;
/**
 * BuhStatisics - заполнение бухгалтерской статистики за день
 */
 
 class BuhStatistics extends Model
{
    public $dataArray = [];
    public $controlArray = [];    
    public $dateList = [];
    public $checkedList = [];
    public $finishedList = [];
    public $timeList = [];
    public $dtstart;
    public $balanceSum = [];
    
    public $debug=[];
    
    public $col;
    public $idx;
    
    public $timeshift = 4*3600; //сдвиг по времени   
    
    public function rules()
    {
        return [                              
            //[[ ], 'default'],                        
            [['userFIO'], 'safe'],            
        ];
    }

 /******* Стандартные функции для расписания *************/
 /********************/
 /* Получить расписание 
     обязательные поля
     idx  - индекс задачи
     startTime   начало исполнения - до этого нельзя
     execTime    ожидаемое время исполнения - отображаем пользователю
     deadTime    время провала исполнения - после этого задача провалена
     titleTask   название задачи
     
 */
 
 
 public function getSheduling ()
 {
 
    return  [
     
        1 => [
               'idx'           => 1,
               'orderPosition' => 0,
               'startTime'    => '9:00',
               'execTime'    => '9:30',
               'deadTime'    => '9:50',
               'titleTask'   => 'Счета', 
               'ref'         => 'fin/client-schet-src&schetDate=',                 
               'group' =>  'reestr',
               
               'v0' => 0,               
            ],

        2 => [
               'idx'           => 2,
               'orderPosition' => 1,
               'startTime'    => '9:00',
               'execTime'    => '9:30',
               'deadTime'    => '9:50',               
               'titleTask'   => 'Отгрузки', 
               'ref'         => 'fin/supply-src&setDate=',  
               'group' =>  'reestr',
               
               'v0' => 0,               
            ],

        3 => [
               'idx'           => 3, 
               'orderPosition' => 2,
               'startTime'    => '9:00',
               'execTime'    => '9:30',
               'deadTime'    => '9:50',               
               'titleTask'   => 'Прибыль',   
               'ref'         => 'fin/profit-src&strDate=',  
               'group' =>  'reestr',
               
               'v0' => 0,               
            ],

        4 => [
               'idx'           => 4,
               'orderPosition' => 3,
               'startTime'    => '9:30',
               'execTime'    => '10:00',
               'deadTime'    => '10:30',                              
               'titleTask'   => 'Приход денег',   
               'ref'         => 'fin/oplata-src&setDate=',  
               'group' =>  'control',
               
               'v0' => 0,               
            ],
                
        5 => [
               'idx'           => 5,
               'orderPosition' => 4,
               'startTime'    => '9:30',
               'execTime'    => '10:00',
               'deadTime'    => '10:30',                              
               'titleTask'   => 'Траты',   
               'ref'         => 'fin/supplier-oplata-src&setDate=',                 
               'group' =>  'control',

               'v0' => 0,
            ],

        6 => [
               'idx'           => 6,
               'orderPosition' => 5,
               'startTime'    => '9:30',
               'execTime'    => '10:00',
               'deadTime'    => '10:30',                              
               'titleTask'   => 'На счету',   
               'ref'         => 'fin/bank-src&strDate=',                   
               'group' =>  'control',
                              
               'v0' => 0,               
            ],
            
        7 => [
               'idx'           => 7,
               'orderPosition' => 6,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                              
               'titleTask'   => 'долг Банку',   
               'ref'         => 'fin/sverka-use&isBank=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],
        
        8 => [
               'idx'           => 8,
               'orderPosition' => 7,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'приход товара',   
               'ref'         => 'fin/purch-src&strDate=',               
               'group' =>  '1ctorg',
                              
               'v0' => 0,               
            ],


        9 => [
               'idx'           => 9, 
               'orderPosition' => 8,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'склад',   
               'ref'         => 'store/ware-use&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,               
            ],

        10 => [
               'idx'           => 10,
               'orderPosition' => 9,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Клиенты нам должны',   
               'ref'         => 'fin/sverka-use&isInUse=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],
 


        11 => [
               'idx'           => 11,
               'orderPosition' => 10,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'мы должны Товар',   
               'ref'         => 'fin/sverka-use&isInUse=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
               
            ],

        12 => [
               'idx'           => 12, 
               'orderPosition' => 11,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Товар нам должны',   
               'ref'         => 'fin/sverka-use&isInUse=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,               
            ],


        13 => [
               'idx'           => 13, 
               'orderPosition' => 12,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'За Товар мы должны',   
               'ref'         => 'fin/sverka-use&isInUse=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],

        14 => [
               'idx'           => 14,
               'orderPosition' => 13,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Услуги нам должны',   
               'ref'         => 'fin/sverka-use&isService=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,               
            ],

        15 => [
               'idx'           => 15,
               'orderPosition' => 14,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'За услуги мы должны',   
               'ref'         => 'fin/sverka-use&isService=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],

        16 => [
               'idx'           => 16,  
               'orderPosition' => 15,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'нам долг Прочие',   
               'ref'         => 'fin/sverka-use&isOther=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],

        17=> [
               'idx'           => 17, 
               'orderPosition' => 16,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'мы должны Прочие',   
               'ref'         => 'fin/sverka-use&isOther=1&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],

        18 => [
               'idx'           => 18, 
               'orderPosition' => 17,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'ВСЕ склады',   
               'ref'         => 'store/ware-use&strDate=',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,              
            ],

        19 => [
               'idx'           => 19, 
               'orderPosition' => 18,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Перемещение',   
               'ref'         => '',  
               'group' =>  '1ctorg',
                              
               'v0' => 0,
            ],

        ]; 
 
 }
 
 /********************/
 /*Возвращает исполнение расписания на заданное время*/
 public function getExecute ($checkTime, $showTime) //На какую дату вносили, когда вносили
 {
     
   //Определяемся с датой на которую ведем проверку
   $checkdate = date("Y-m-d", $checkTime);
   $showdate  = date("Y-m-d", $showTime);
   //Получаем что должно быть  
   $sheduleArray = $this->getSheduling ();
   $sN = count($sheduleArray);
   $sN++; //У нас 0 зарезервирован
   /*Добавим завершение */
   $sheduleArray[$sN]= [   
               'idx'           => 0, 
               'orderPosition' => 19,
               'startTime'    => '12:30',
               'execTime'    => '12:30',
               'deadTime'    => '13:00',                                             
               'titleTask'   => 'Завершение',   
               'ref'         => '',  
               'group' =>  19,                             
    ]; 
   $sN++;
    
    
  
   /*Индексируем и инициализируем массив */
   $idxArray=[];
   for ($i=0; $i < $sN; $i++){ 
    if (!isset($sheduleArray[$i]['idx'])) continue; // не на что ссылаться
   
    $sheduleArray[$i]['realExecute']='';
    $sheduleArray[$i]['timeStart'] = strtotime($showdate." ".$sheduleArray[$i]['startTime'].":00");
    $sheduleArray[$i]['timeExec' ] = strtotime($showdate." ".$sheduleArray[$i]['execTime'].":00");
    $sheduleArray[$i]['timeDead' ] = strtotime($showdate." ".$sheduleArray[$i]['deadTime'].":00");
    $sheduleArray[$i]['timeReal']=0;
   
    $idx = $sheduleArray[$i]['idx'];
    $idxArray[$idx] = $i;   
   }
  
   
 //Получаем исполнение на указанную дату  
  $strSql = "SELECT {{%buh_statistics}}.id, row, checkdate, changed from 
              {{%buh_statistics}} where DATE(checkdate) = :checkdate ORDER BY id";   

   $list = Yii::$app->db->createCommand($strSql)
                    ->bindValue(':checkdate', $checkdate )
                    ->queryAll();                         
  
    
//$shift = strtotime($list[$i]['changed'])-$this->showDate;
//$shift= intval($shift/1800)+8;// в половинках часа    
//$execute['reestr'][$shift] = $list[$i]['id']; // сделано в это период                          
  //$sheduleArray

  for ($i=0; $i < count($list); $i++){                  
  //Определим индекс задачи
    
    $idx = $list[$i]['row'];
    if ($idx <= 0) continue; //пропустим не нужное
  //Определим положение задачи в массиве шедулинга
    if (!isset($idxArray[$idx])) continue; //а вот ничего то мы не знаем о таких значениях
    $k   = $idxArray[$idx];
    $sheduleArray[$k]['timeReal' ] = strtotime($list[$i]['changed'])+$this->timeshift;      
    $sheduleArray[$k]['realExecute'] = date("d H:i",$sheduleArray[$k]['timeReal' ]);
    //В секундах
    
   }   
   
   
 //Получаем свободные задачи
 
   $query  = new Query();
   $query->select ([ 'c.id', 'eventNote', 'executeDateTime', 'eventTitle', ])
                    ->from("{{%calendar}} as c")
                    ->leftJoin("{{%user}} as u","u.id= c.ref_user")                    
                    ->distinct()
                    ->andWhere('u.roleFlg & 0x0400')
                    ->andWhere("c.event_date = '".$showdate."'")   
                    ;   

   $list = $query->createCommand()->queryAll();
   $N=count($list);
   for($i=0;$i<$N; $i++)
   {

        $execTime = strtotime($list[$i]['executeDateTime']);
        $strTime= date("H:i", $execTime);
   
   
         $sheduleArray[]= [   
               'idx'           => 20, 
               'orderPosition' => 20,
               'startTime'    => $strTime,
               'execTime'    =>  $strTime,
               'deadTime'    =>  $strTime,                                             
               'titleTask'   =>  $list[$i]['eventTitle'],   
               'ref'         => '',  
               'group' =>  'freetask',                             
               
               'realExecute' => $strTime,
               'timeStart'   => $execTime-10,
               'timeExec'    => $execTime,
               'timeDead'    => $execTime+10,
               'timeReal'    => $execTime,
               
     ]; 
   
    

   
    
   }
              
  return $sheduleArray;   
 
 }//
 
 
 /********************/
 /********************/
 public function prepareDateList()
 {
     
   if (empty($this->dtstart)) { $start= time(); $this->dtstart=date("Y-m-d", $start);}              
                          else  $start= strtotime($this->dtstart);               
    for ($i=0;$i<8;$i++ )
    {
     $this->dateList[$i] = date("d.m.Y",$start+($i-5)*24*3600);
     $this->timeList[$i] = $start+($i-5)*24*3600;
    } 
     
 }

 
/********************/
/********************/ 
public function showCalc ()
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    

     $this->prepareBuhStatData ("");
     $this->prepareBuhControlData ("");
  
     
     $s='s'.$this->col;
     
     $N= count($this->controlArray);
    for($i=0; $i< $N ; $i++ )
    {
    if ($this->controlArray[$i]['idx']==$this->idx)  
    {     
     echo  $this->controlArray[$i][$s];
     break; 
    }
     
   }  
    
 //   echo $i."<br>".$this->controlArray[$i]['idx'];          
}
/********************/ 

public function setDataUse($refCheck, $opType, $refSrc, $mult )
{
     $record= TblBuhCheckCalc::findOne([
     'refCheck' => $refCheck,
     'opType'  => $opType,
     'refSrc'  => $refSrc              
     ]);     
     if ($mult == 0)
     {
         if (!empty($record)) $record->delete();
         return;
     }
     if (empty($record))
     {
      $record=  new TblBuhCheckCalc();
      $record->refCheck = $refCheck;
      $record->opType   = $opType;
      $record->refSrc   = $refSrc;              
     }
     
    $record->mult  = $mult;              
    
    $record->save();
}
/********************/ 
public function setChecked($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col-1]);

     $record= TblBuhStatistics::findOne([
     'checkdate'  => $checkdate,
     'row'        => 0              
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatistics();
       $record->checkdate  = $checkdate;
       $record->row        = 0;                      
       $record->val = 1;
     }
     else{
      if ($record->val == 1)   $record->val = 0;
                        else   $record->val = 1;
     }
     $record->changed = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     
     $record->save();
     
}

/********************/ 
public function setFinished($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col-1]);

     $record= TblBuhStatistics::findOne([
     'checkdate'  => $checkdate,
     'row'        => -1              
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatistics();
       $record->checkdate  = $checkdate;
       $record->row        = -1;                      
       $record->val = 2;
     }
     else{
      if ( $record->val == 2)$record->val = 1;
      else $record->val = 2;
          

     }
     $record->changed = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     
     $record->save();
     
}
/********************/ 
public function startCheck($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col-1]);
     
     $record= TblBuhStatistics::findOne([
     'checkdate'  => $checkdate,
     'row'        => -1              
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatistics();
       $record->checkdate  = $checkdate;
       $record->row        = -1;                      
       $record->val = 1;
     }
     else{
       $record->val = 1;
     }
     $record->changed = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     
     $record->save();
     return  $this->timeList[$col-1];
}

/********************/

public function setStatistics($col, $row, $val)
 {
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
     $val=preg_replace("/\,/",".",$val );
     
     $checkdate = date("Y-m-d", $this->timeList[$col-1]);

     $record= TblBuhStatistics::findOne([
     'checkdate'  => $checkdate,
     'row'        => $row              
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatistics();
       $record->checkdate  = $checkdate;
       $record->row        = $row;                      
     }
     
     $record->changed = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     $record->val = floatval($val);
     $record->save();
     
 }
 
 
 
 
 
/********************/

 public function prepareBuhStatData($params)
 {
     
     $this->prepareDateList();    
     
     
     $this->dataArray = $this->getSheduling();
/*Инит*/
 for ($row=1; $row<=19; $row++){
      for ($col=0; $col<8; $col++)
      {
          $v='v'.$col;
          $c='c'.$col;
          $s='s'.$col;
          $this->dataArray[$row][$v]=0;            
          $this->dataArray[$row][$c]=0;            
          $this->dataArray[$row][$s]=false;            
      }
 }      
/* на показываемые 7 дней */
      for ($col=1; $col<8; $col++)
      {
        $this->checkedList[$col-1]=0;
        $this->finishedList [$col-1]=0;
        $list = Yii::$app->db->createCommand(
            'SELECT  val, row FROM {{%buh_statistics}} WHERE checkdate = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryAll();
        $key = 'v'.$col;    
        $skey= 's'.$col;    

    
        $N = count($list);
        for ($i=0;$i<$N;$i++)
        {
            $row=$list[$i]['row']; 
            if ($row > 0)         {$this->dataArray[$row][$key] = $list[$i]['val'];  $this->dataArray[$row][$skey] = true;}                      
            if ($row == 0)         $this->checkedList[$col-1]   = $list[$i]['val'];            
            if ($row == -1)        $this->finishedList[$col-1]  = $list[$i]['val'];                                        
        }

         $key= 'c'.$col;    
        
        
        //счета
        $this->dataArray[1][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(schetSumm) FROM {{%schet}} WHERE schetDate = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();
        if (empty($this->dataArray[1][$key])) $this->dataArray[1][$key] = 0;   
        
        //Отгрузки
        $this->dataArray[2][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(supplySumm) FROM {{%supply}} WHERE supplyDate = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();
        if (empty($this->dataArray[2][$key])) $this->dataArray[2][$key] = 0;
            
        //Прибыль
        $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%profit_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();            
        $this->dataArray[3][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(profit) FROM {{%profit_content}} WHERE  headerRef = :headerRef',
            [ ':headerRef' => $headerRef  ] )->queryScalar();    
        if (empty($this->dataArray[3][$key])) $this->dataArray[3][$key] = 0;    
            
            
        //Приход денег    
        $this->dataArray[4][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(oplateSumm) FROM {{%oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();
         if (empty($this->dataArray[4][$key])) $this->dataArray[4][$key] = 0;   
        
         //Траты    
        $this->dataArray[5][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(oplateSumm) FROM {{%supplier_oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();    
        if (empty($this->dataArray[5][$key])) $this->dataArray[5][$key] = 0;    
             
             
         //На счету    
        $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%control_bank_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();            
        $this->dataArray[6][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(cashSum) FROM {{%control_bank}} 
        LEFT JOIN {{%control_bank_use}} on {{%control_bank_use}}.id = {{%control_bank}}.useRef
        WHERE  inUseReal= 1 AND  headerRef = :headerRef',   [ ':headerRef' => $headerRef  ] )->queryScalar();    
        if (empty($this->dataArray[6][$key])) $this->dataArray[6][$key] = 0;    
             

         //Приход товара    
        $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%control_purch_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();            
        $this->dataArray[8][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(purchSum) FROM {{%control_purch_content}}       
        WHERE headerRef = :headerRef',   [ ':headerRef' => $headerRef  ] )->queryScalar();    
        if (empty($this->dataArray[8][$key])) $this->dataArray[8][$key] = 0;    
                          
         //Склад    
        $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%ware_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => date("Y-m-d", $this->timeList[$col-1])  ] )->queryScalar();            
        $this->dataArray[9][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(goodAmount*initPrice) FROM {{%ware_content}} WHERE
        goodAmount > 0 AND initPrice > 0 AND isActive = 1 AND headerRef = :headerRef',
            [ ':headerRef' => $headerRef  ] )->queryScalar();    
        if (empty($this->dataArray[9][$key])) $this->dataArray[9][$key] = 0;    
             
        $this->dataArray[18][$key] =  Yii::$app->db->createCommand( 'SELECT  SUM(goodAmount*initPrice) FROM {{%ware_content}} WHERE
        headerRef = :headerRef',
            [ ':headerRef' => $headerRef  ] )->queryScalar();    
        if (empty($this->dataArray[9][$key])) $this->dataArray[9][$key] = 0;    
        
        
        //долги
        $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_sverka_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => date("Y-m-d", $this->timeList[$col-1]), ])->queryScalar();  
        if (empty ($headerRef)) $headerRef = 0;
        $query  = new Query();
        $query->select ([
                     'u.id',
                     'c.isInUse',
                     'c.isBlack',
                     'c.isOther',
                     'c.isService',
                     'c.isBank',
                     'ifnull(clientSum,0) as clientSum',
                     'ifnull(supplSum,0)  as supplSum',
                     'ifnull(otherSum,0)  as otherSum',
                          ])
                    ->from("{{%control_sverka_dolga_use}} as u")
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= u.fltRef")                    
                    ->leftJoin("(SELECT isInUse, isBlack, isOther, isService, isBank, useRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef." group by useRef) as c", "c.useRef = u.id")
                    ->leftJoin("(SELECT SUM(balanceSum) as clientSum, useRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef." and dogType = 1 group by useRef) as client", "client.useRef = u.id")
                    ->leftJoin("(SELECT SUM(balanceSum) as supplSum, useRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef." and dogType = 2 group by useRef) as suppl", "suppl.useRef = u.id")
                    ->leftJoin("(SELECT SUM(balanceSum) as otherSum, useRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef." and dogType = 0 group by useRef) as other", "other.useRef = u.id")                         
                    ->distinct()
                    ->andWhere('f.isFilter = 1')
            ;
     $this->balanceSum = 
      [
         'clientDebetSum' =>0,
         'clientCreditSum' =>0,
         'supplDebetSum' =>0,
         'supplCreditSum' =>0,
         'isBlack'=>0,
         'isOtherDebet'=>0,
         'isOtherCredit'=>0,
         'isServiceDebet'=>0,
         'isServiceCredit'=>0,
         'isBank'=>0,
         'isBankDebet'=>0,
         'isBankCredit'=>0,
  ];        
        
    $list = $query->createCommand()->queryAll();
    //$this->debug[]= $list;
    
//$this->debug[]= $query->createCommand()->getRawSql();   
    $N=count($list);
    for($i=0;$i<$N;$i++)
    {
      if ($list[$i]['isInUse'] == 1)  {
      if ($list[$i]['clientSum'] > 0)  $this->balanceSum['clientCreditSum']+=$list[$i]['clientSum'];
      if ($list[$i]['clientSum'] < 0)  $this->balanceSum['clientDebetSum']+=$list[$i]['clientSum'];
      if ($list[$i]['supplSum'] > 0)   $this->balanceSum['supplCreditSum']+=$list[$i]['supplSum'];
      if ($list[$i]['supplSum'] < 0)   $this->balanceSum['supplDebetSum']+=$list[$i]['supplSum'];            
      
      if ($list[$i]['isOther'] > 0)    $this->balanceSum['isOtherCredit']+=$list[$i]['otherSum'];
      if ($list[$i]['isOther'] < 0)    $this->balanceSum['isOtherDebet']+=$list[$i]['otherSum'];
    }
    
      if ($list[$i]['isOther'] == 1)  {
      if ($list[$i]['isOther'] > 0)    $this->balanceSum['isOtherCredit']+=$list[$i]['otherSum'];
      if ($list[$i]['isOther'] < 0)    $this->balanceSum['isOtherDebet']+=$list[$i]['otherSum'];

      if ($list[$i]['clientSum'] > 0)    $this->balanceSum['isOtherCredit']+=$list[$i]['clientSum'];
      if ($list[$i]['clientSum'] < 0)    $this->balanceSum['isOtherDebet']+=$list[$i]['clientSum'];
 
      if ($list[$i]['supplSum'] > 0)    $this->balanceSum['isOtherCredit']+=$list[$i]['supplSum'];
      if ($list[$i]['supplSum'] < 0)    $this->balanceSum['isOtherDebet']+=$list[$i]['supplSum'];
                  
      }
      
      if ($list[$i]['isService'] == 1){
      $sum = ($list[$i]['clientSum']+$list[$i]['supplSum']+$list[$i]['otherSum']);
          if ($sum > 0)    $this->balanceSum['isServiceCredit']+=$sum;
          if ($sum < 0)    $this->balanceSum['isServiceDebet']+=$sum;
      }
      if ($list[$i]['isBank'] == 1)   {
      $sum = $list[$i]['clientSum']+$list[$i]['supplSum']+$list[$i]['otherSum'];
//      $this->debug[]= ['Банк', $i, $sum, $list[$i]['clientSum'],$list[$i]['supplSum'], $list[$i]['otherSum']];
//      $this->debug[]= $list[$i];
      $this->balanceSum['isBank']+=$sum;
          if ($sum > 0)    $this->balanceSum['isBankCredit']+=$sum;
          if ($sum < 0)    $this->balanceSum['isBankDebet']+=$sum;
      }
    }    
    $this->dataArray[7][$key] = -$this->balanceSum['isBank'];
    $this->dataArray[10][$key] = $this->balanceSum['clientCreditSum'];
    $this->dataArray[11][$key] = -$this->balanceSum['clientDebetSum'];        
        
    $this->dataArray[12][$key] = $this->balanceSum['supplCreditSum'];
    $this->dataArray[13][$key] = -$this->balanceSum['supplDebetSum'];        
        
    $this->dataArray[14][$key] = $this->balanceSum['isServiceCredit'];
    $this->dataArray[15][$key] = -$this->balanceSum['isServiceDebet'];        
             
    $this->dataArray[16][$key] = $this->balanceSum['isOtherCredit'];
    $this->dataArray[17][$key] = -$this->balanceSum['isOtherDebet'];        
             
      }

      
      //$this->debug[]=$this->dataArray;
/* Предыдущая закрытая дата */      

        $lastDt =Yii::$app->db->createCommand('SELECT max(checkdate) FROM {{%buh_statistics}} WHERE checkdate < :checkdate AND row =0 AND val =1',
            [ ':checkdate' => date("Y-m-d", $this->timeList[0])  ] )->queryScalar();
            
        if (empty($lastDt))$lastDt='1970-01-01';   


        $list = Yii::$app->db->createCommand(
            'SELECT  val, row FROM {{%buh_statistics}} WHERE checkdate = :checkdate',
            [ ':checkdate' => $lastDt ] )->queryAll();
        $key= 'v0';    
        
        $N = count($list);
        for ($i=0;$i<$N;$i++)
        {
            $row=$list[$i]['row']; 
            if ($row > 0)  $this->dataArray[$row][$key]=$list[$i]['val'];            
        }
     
        $this->timeList[-1] = strtotime($lastDt);
        $this->dateList[-1] = date("Y-m-d", $this->timeList[-1]);
        
        

 }


public function getBuhStatProvider($params)		
   {
   
     $this->prepareBuhStatData($params);
   
      $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            "orderPosition",
            ],

            'defaultOrder' => [    'orderPosition' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
        
/********************/

 public function prepareBuhControlData($params)
 {
     
     $this->prepareDateList();    
     
 
 $this->controlArray = Yii::$app->db->createCommand("Select id as idx, orderPosition, titleTask, isMarkNonZero FROM {{%buh_check}} ORDER BY orderPosition")->queryAll();

$N = count($this->controlArray); 
 
 /*Инит*/
//$N++;
$refArray=[];
 for ($row=0; $row<$N; $row++){
 
 /*$this->controlArray[$row]['idx'] = $list[$row-1]['idx'];
 $this->controlArray[$row]['orderPosition'] = $list[$row-1]['orderPosition'];
 $this->controlArray[$row]['titleTask'] = $list[$row-1]['titleTask'];
 $this->controlArray[$row]['isMarkNonZero'] = $list[$row-1]['isMarkNonZero'];
 */
       $idx = $this->controlArray[$row]['idx'];
       $refArray[$idx]=$row;       
      for ($col=1; $col<8; $col++)
      {
          $v='v'.$col;
          $c='c'.$col;
          $s='s'.$col;
          $this->controlArray[$row][$v]=0;
          $this->controlArray[$row][$c]=0;      
          $this->controlArray[$row][$s]="";
         //чтобы был не пустой массив          
         $this->controlArray[$row]['curDataUse']       =['0' => 0];
         $this->controlArray[$row]['prevDataUse']      =['0' => 0];
         $this->controlArray[$row]['curControl']       =['0' => 0];                               
      }
 }      
 
 
/******************/
/*Задаем ход вычислений*/

 $list = Yii::$app->db->createCommand("Select id, refCheck, opType, refSrc, mult  FROM {{%buh_check_calc}} ")->queryAll();

 $Nl = count($list);  
 
 for ($i=0;$i< $Nl; $i++)
 {
     $idx= $list[$i]['refCheck'];
     $row=$refArray[$idx];
     $src= $list[$i]['refSrc'];
     $mult=$list[$i]['mult'];
     switch($list[$i]['opType'])
     {
         case 1:
            $this->controlArray[$row]['prevDataUse'][$src]=$mult;                 
         break;
 
         case 2:
            $this->controlArray[$row]['curControl'][$src]=$mult;                 
         break;
         
         default:
           $this->controlArray[$row]['curDataUse'][$src]=$mult;                 
         break;
     }
   
 } 
    
    
/******************/
     // $this->debug[]=$this->checkedList;  
      for ($col=1; $col<8; $col++)
      {
        $key  = 'v'.$col;
        $ckey = 'c'.$col;        
        $s    = 's'.$col;
        
        $j=$col-1;
        while($j>1)
        {
         if($this->checkedList[$j-1] == 1) break;
         $j--;         
        }    
        if ($j < 1 ) { $pkey='v0'; $pckey = 'c0'; }
        else         { $pkey='v'.$j; $pckey = 'c'.$j;} 
        
    /*    $this->debug[]=
        [
        'col'     => $col,
        'key'     => $key,
        'current' => "Сегодняшний день ".$this->dateList[$col-1],
        'pkey'    => $pkey,
        'j'       => $j,
        'previous' => "Предыдущий день ".$this->dateList[$j-1],
        ];
     */   
      for ($row=0; $row<$N; $row++){
       $sum =0;
       $csum =0;
       
       $this->controlArray[$row][$s].= "<p>".$this->controlArray[$row]['titleTask']."</p><table class='table table-stripped'>";
       $this->controlArray[$row][$s].= "<tr><td></td><td>Ручной ввод</td><td>Из 1С</td></tr>";
       $this->controlArray[$row][$s].= "<tr><td colspan='3' align='center'><b>Сегодняшний день ".$this->dateList[$col-1]."</b></td></tr>";
       
       $curDataUse=$this->controlArray[$row]['curDataUse'];        
       foreach($curDataUse as $idx => $value)
       {
           if($idx == 0) continue; //пропустим дефолтный
           $val=$value*$this->dataArray[$idx][$key];
           $sum+= $val;

           $cval=$value*$this->dataArray[$idx][$ckey];
           $csum+= $cval;
           
           $this->controlArray[$row][$s].= "<tr><td>".$this->dataArray[$idx]['titleTask']." </td><td> ".
           number_format($val,'2','.','&nbsp;')."</td><td>".number_format($cval,'2','.','&nbsp;')."</td></tr>\n" ;                                 
       }
       $curControl=$this->controlArray[$row]['curControl'];        
       foreach($curControl as $idx => $value)
       {
         //$r=$this->controlArray[$row]
         if($idx == 0) continue; //пропустим дефолтный
         $r=$refArray[$idx];
           
           $val=$value*$this->controlArray[$r][$key];
           $sum+= $val;
           
           $cval=$value*$this->controlArray[$r][$ckey];
           $csum+= $cval;
           
           $this->controlArray[$row][$s].= "<tr><td>".$this->controlArray[ $r]['titleTask']." </td><td> ".
           number_format($val,'2','.','&nbsp;')."</td><td>".number_format($cval,'2','.','&nbsp;')."</td></tr>\n" ;
       }

       $this->controlArray[$row][$s].= "<tr><td colspan='3' align='center' ><b>Предыдущий день ".$this->dateList[$j-1]."</b></td></tr>";      
       $prevDataUse=$this->controlArray[$row]['prevDataUse'];               
       foreach($prevDataUse as $idx => $value)
       {
           if($idx == 0) continue; //пропустим дефолтный
           $val=$value*$this->dataArray[$idx][$pkey];
           $sum+= $val;

           $cval=$value*$this->dataArray[$idx][$pckey];
           $csum+= $cval;
                      
           $this->controlArray[$row][$s].= "<tr><td>".$this->dataArray[$idx]['titleTask']." </td><td> ".
           number_format($val,'2','.','&nbsp;')."</td><td>".number_format($cval,'2','.','&nbsp;')."</td></tr>\n" ;
                           
       }
       

           $this->controlArray[$row][$s].= "<tr><td> ИТОГО: </td><td> ".
           number_format($sum,'2','.','&nbsp;')."</td><td>".number_format($csum,'2','.','&nbsp;')."</td></tr>\n" ;
           
           $this->controlArray[$row][$key]  =$sum;
           $this->controlArray[$row][$ckey] =$csum;
    
        $this->controlArray[$row][$s].= "</table>";

      // $this->debug[]=$this->controlArray[$row][$s];       
       
      }          
 
        
       if (!empty($this->dataArray[2][$key]))  
           $this->controlArray[5][$key] = 
           100*$this->dataArray[3][$key]/$this->dataArray[2][$key];         
       
      }
      

  //$this->debug[]=$this->controlArray;           
 }


public function getBuhControlProvider($params)		
   {
   
     $this->prepareBuhControlData($params);
   
      $provider = new ArrayDataProvider([
            'allModels' => $this->controlArray,
            'totalCount' => count($this->controlArray),
            'pagination' => [
            'pageSize' => 21,
            ],
            'sort' => [
            'attributes' => [
            //"orderPosition",
            'idx', 'orderPosition', 'titleTask', 'isMarkNonZero' 
            ],

            'defaultOrder' => [    'orderPosition' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
        
        
/**************/
public $eventArray=[];
public function prepareBuhEventData($params)		
{
      $query  = new Query();
      $query->select ([
                     'c.id',
                     'eventNote',
                     'executeDateTime',
                     'eventTitle',
                          ])
                    ->from("{{%calendar}} as c")
                    ->leftJoin("{{%user}} as u","u.id= c.ref_user")                    
                    ->distinct()
                    ->andWhere('u.roleFlg & 0x0400')
                    ->andWhere("c.event_date = '".$this->dtstart."'")   
                    ;   
       //$query->addParams([':dtstart', $this->dtstart]);       
        
    
    $st=12; 
    $ref=[];
    for ($i=0; $i<12; $i++ )
    {
        $h= $st+intval($i/2);
        $m=($i/2-intval($i/2))*2*30;
        $strStart=sprintf("%02d:%02d",$h,$m);        
        $this->eventArray[$i]['idx'] = $i;
        $this->eventArray[$i]['strStart'] = $strStart;        
        $this->eventArray[$i]['timeStart'] = strtotime( $this->dtstart." ".$strStart);
        $ref[$this->eventArray[$i]['timeStart']] = $i;
        $this->eventArray[$i]['timeEnd']   = $this->eventArray[$i]['timeStart'] + 30*60;//+30 минут
        $this->eventArray[$i]['strEnd'] = date ("H:i", $this->eventArray[$i]['timeEnd']);
        $this->eventArray[$i]['eventTitle'] ="";
        $this->eventArray[$i]['eventNote']  ="";
    } 
       
    $list = $query->createCommand()->queryAll();
//    $this->debug[]= $ref;
//    $this->debug[]= $list;
//    $this->debug[]= $query->createCommand()->getRawSql();   
    $N=count($list);
    for ($i=0;$i<$N; $i++ )   
    {
      $timeExec=strtotime($list[$i]['executeDateTime']);
      if(empty($timeExec)) $timeExec =0;
      if (!array_key_exists ($timeExec, $ref)) continue;             
      $idx=$ref[$timeExec];
      $this->eventArray[$idx]['eventTitle'] .=$list[$i]['eventTitle']." <br>";
      $this->eventArray[$idx]['eventNote']  .=$list[$i]['eventNote']." <br>";      
    }  
}
public function getBuhEventProvider($params)		
   {
        
     $this->prepareBuhEventData($params);  

      $provider = new ArrayDataProvider([
            'allModels' => $this->eventArray,
            'totalCount' => count($this->eventArray),
            'pagination' => [
            'pageSize' => 12,
            ],
            'sort' => [
            'attributes' => [
            "idx",
            ],
            'defaultOrder' => [    'idx' => SORT_ASC ],
            ],
        ]);
        
    return $provider;


   }
 
 public function setEventTitle($timestart, $val)
 {
     $curUser=Yii::$app->user->identity;    
     
     $record = TblCalendarEvent::findOne(
     [
       'executeDateTime' => date("Y-m-d H:i:s", $timestart),
       'ref_user' =>$curUser->id     
     ]);
     
     if (empty($record))
     {
       $record = new TblCalendarEvent();
       if (empty($record)) return false;     
       $record->executeDateTime = date("Y-m-d H:i:s", $timestart);
       $record->event_date = date("Y-m-d", $timestart);
       $record->eventTime  = date("H:i:s", $timestart);
       $record->ref_user  = $curUser->id;
       $record->ref_event =9;
       
       $record->ref_org=0;
       $record->ref_contact=0;
     }
    
     $record->eventTitle = $val; 
     $record->save();
 }  

 
public function setEventNote($timestart, $val)
 {
     $curUser=Yii::$app->user->identity;    
     
     $record = TblCalendarEvent::findOne(
     [
       'executeDateTime' => date("Y-m-d H:i:s", $timestart),
       'ref_user' =>$curUser->id     
     ]);
     
     if (empty($record))
     {
       $record = new TblCalendarEvent();
       if (empty($record)) return false;     
       $record->executeDateTime = date("Y-m-d H:i:s", $timestart);
       $record->event_date = date("Y-m-d", $timestart);
       $record->eventTime  = date("H:i:s", $timestart);
       $record->ref_user  = $curUser->id;
       $record->ref_event =9;
       
       $record->ref_org=0;
       $record->ref_contact=0;
     }
     $record->eventStatus = 2; 
     $record->eventNote = $val; 
     $record->save();
 }  
 
  
  /************End of model*******************/ 
 }
