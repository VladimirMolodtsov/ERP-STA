<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;


use app\modules\bank\models\TblBuhStatistics;
use app\modules\bank\models\TblBuhStatHeader;
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
    public $syncedList = [];
    public $finishedList = [];
    public $timeList = [];
    public $dtstart;
    public $balanceSum = [];
    
    public $debug=[];
    
    public $col;
    public $prv;

    public $manual=0;     
    public $idx;
    
    public $timeshift = 4*3600; //сдвиг по времени   

    public $buhStatPrepared=0;
    //public $dataListPrepared=0;
    
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
               '1Creport' => '1',
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
               '1Creport' => '3',               
               
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
               '1Creport' => '21',               
               
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
               '1Creport' => '2',                              
               
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
               '1Creport' => '13',               
               
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
               '1Creport' => '26',               
               
               'v0' => 0,               
            ],
            
        7 => [
               'idx'           => 7,
               'orderPosition' => 6,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                              
               'titleTask'   => 'Долг Банку',   
               'ref'         => 'fin/sverka-use&ftType=isBank&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                                             
               'v0' => 0,
            ],
        
        8 => [
               'idx'           => 8,
               'orderPosition' => 7,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Приход товара',   
               'ref'         => 'fin/purch-src&strDate=',               
               'group' =>  '1ctorg',
               '1Creport' => '32',               
               
               'v0' => 0,               
            ],


        9 => [
               'idx'           => 9, 
               'orderPosition' => 8,
               'startTime'    => '11:00',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Склад',   
               'ref'         => 'store/ware-use&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '22',                              
                              
               'v0' => 0,               
            ],

        10 => [
               'idx'           => 10,
               'orderPosition' => 9,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Клиенты нам должны',   
               'ref'         => 'fin/sverka-use&ftType=isClientP&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,
            ],
 


        11 => [
               'idx'           => 11,
               'orderPosition' => 10,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Мы должны Товар',   
               'ref'         => 'fin/sverka-use&ftType=isClientM&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',   
               
               'v0' => 0,
               
            ],

        12 => [
               'idx'           => 12, 
               'orderPosition' => 11,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Товар нам должны',   
               'ref'         => 'fin/sverka-use&ftType=isWareP&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,               
            ],


        13 => [
               'idx'           => 13, 
               'orderPosition' => 12,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'За Товар мы должны',   
               'ref'         => 'fin/sverka-use&ftType=isWareM&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,
            ],

        14 => [
               'idx'           => 14,
               'orderPosition' => 13,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Услуги нам должны',   
               'ref'         => 'fin/sverka-use&ftType=isServiceP&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',   
               
               'v0' => 0,               
            ],

        15 => [
               'idx'           => 15,
               'orderPosition' => 14,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'За услуги мы должны',   
               'ref'         => 'fin/sverka-use&ftType=isServiceM&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,
            ],

        16 => [
               'idx'           => 16,  
               'orderPosition' => 15,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Нам долг. Прочие',   
               'ref'         => 'fin/sverka-use&ftType=isOtherP&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,
            ],

        17=> [
               'idx'           => 17, 
               'orderPosition' => 16,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Мы должны. Прочие',   
               'ref'         => 'fin/sverka-use&ftType=isOtherM&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                  
                              
               'v0' => 0,
            ],

        18 => [
               'idx'           => 18, 
               'orderPosition' => 17,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Все склады',   
               'ref'         => 'store/ware-use&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '22',   
               
               'v0' => 0,              
            ],

        19 => [
               'idx'           => 19, 
               'orderPosition' => 18,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Перемещение',   
               'ref'         => 'fin/sverka-use&ftType=isMove&strDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '25',                                 
                              
               'v0' => 0,
            ],

        20 => [
               'idx'           => 20, 
               'orderPosition' => 19,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Приход всего',   
               'ref'         => 'fin/oplata-src&setDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '2' ,                                
                              
               'v0' => 0,
            ],
        21 => [
               'idx'           => 21, 
               'orderPosition' => 20,
               'startTime'    => '11:30',
               'execTime'    => '12:00',
               'deadTime'    => '12:30',                                             
               'titleTask'   => 'Расход всего',   
               'ref'         => 'fin/supplier-oplata-src&setDate=',  
               'group' =>  '1ctorg',
               '1Creport' => '13',                                 
                              
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
               'orderPosition' => 21,
               'startTime'    => '12:30',
               'execTime'    => '12:30',
               'deadTime'    => '13:00',                                             
               'titleTask'   => 'Завершение',   
               'ref'         => '',  
               'group' =>  21,                             
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
  
   
 //Получаем исполнение на указанную дату - в случае месячной статистики на месяц 
  $strSql = "SELECT max(id) from {{%buh_stat_header}} where DATE(checkDate) = :checkdate  and isMonth =0 ORDER BY id";   
  $headerRef = Yii::$app->db->createCommand($strSql)->bindValue(':checkdate', $checkdate )->queryScalar();                         
  if(empty($headerRef)) $headerRef = 0;
  
 
  $strSql = "SELECT {{%buh_statistics}}.id, row, checkdate, changed from 
              {{%buh_statistics}} where headerRef = :headerRef ORDER BY id";   

   $list = Yii::$app->db->createCommand($strSql)
                    ->bindValue(':headerRef', $headerRef )
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
               'idx'           => 22, 
               'orderPosition' => 22,
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
    
    $st_time = $start-6*24*3600; //на шесть дней назад                        
    
    for ($i=1;$i<8;$i++ )
    {
     $this->timeList[$i] = $st_time+$i*24*3600;     
     $this->dateList[$i] = date("d.m.Y",$this->timeList[$i]);          
    }     
   $lastDt =Yii::$app->db->createCommand('SELECT max(checkDate) FROM {{%buh_stat_header}} WHERE 
   isSynced=1 and checkDate <= :checkdate and isMonth =0',
            [ ':checkdate' => date("Y-m-d", $st_time) ] )->queryScalar();
   if (empty($lastDt)) $lastDt='1970-01-01';   
 
   $this->dateList[0] = $lastDt;
   $this->timeList[0] = strtotime($lastDt);
     
}

 
/********************/
/********************/ 
public function showCalc ()
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    

     $this->prepareBuhStatData ("");
     $this->prepareBuhControlData ("");
  
     //$this->col=6;
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
/* Начинаем заполнение 
  isChecked - начали заполнять
*/
public function setChecked($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col]);

     $record= TblBuhStatHeader::findOne([
        'checkDate'  => $checkdate,
        'isMonth'    => 0        
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatHeader();
       $record->checkDate  = $checkdate;                    
       $record->isChecked = 1;
     }
     else{
      if ($record->isChecked == 1) $record->isChecked = 0;
                            else   $record->isChecked = 1;
     }
     $record->checkChanged = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     
     $record->save();
     
}

/********************/ 
public function startCheck($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col]);
     
     $record= TblBuhStatHeader::findOne([
        'checkDate'  => $checkdate, 
        'isMonth'    => 0        
     ]);

     if (empty($record))
     {
       $record = new TblBuhStatHeader();
       $record->checkDate  = $checkdate;                    
       $record->isSynced = 1;
       $record->syncDateTime = date("Y-m-d H:i");
     }
     else{
       $record->isSynced = 1;
       $record->syncDateTime = date("Y-m-d H:i");
     }
     $record->editor = $curUser->id;     
     $record->loadData = 0;//сбрасываем - пусть перечитает
     $record->save();
     
     return  $this->timeList[$col];
}

/********************/

/********************/ 
public function setFinished($col)
{
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
         
     $checkdate = date("Y-m-d", $this->timeList[$col]);
     
     $record= TblBuhStatHeader::findOne([
        'checkDate'  => $checkdate,    
        'isMonth'    => 0        
     ]);

     if (empty($record))
     {
       $record = new TblBuhStatHeader();
       $record->checkDate  = $checkdate;                    
       $record->isSynced = 1;
       $record->isFinished = 1;
     }
     else{
       $record->isSynced = 1;
       if ($record->isFinished == 1)  $record->isFinished = 0;
                                else $record->isFinished = 1;
     }
     $record->editor = $curUser->id;     
     $record->checkChanged = date("Y-m-d H:i:s");
     $record->save();
     
     return  $this->timeList[$col];

}


public function setStatistics($col, $row, $val)
 {
     $curUser=Yii::$app->user->identity;    
     $this->prepareDateList();    
     $val=preg_replace("/\,/",".",$val );
     
     $checkdate = date("Y-m-d", $this->timeList[$col]);

     $header= TblBuhStatHeader::findOne([
        'checkDate'  => $checkdate,    
        'isMonth'    => 0
     ]);
    
     if (empty($header)) return false;   

     $record= TblBuhStatistics::findOne([
       'headerRef' => $header->id,
       'row'        => $row,
     ]);
     
     if (empty($record))
     {
       $record = new TblBuhStatistics();
       $record->headerRef  = $header->id;
       $record->checkdate  = $checkdate;
       $record->row        = $row;                      
     }
     
     $record->changed = date("Y-m-d H:i:s");
     $record->editor = $curUser->id;
     $record->val = floatval($val);
     $record->save();
     
 }
 
 
 
 
 
/********************/
/*
ALTER TABLE `rik_buh_statistics` ADD COLUMN `headerRef` BIGINT DEFAULT 0;
ALTER TABLE `rik_buh_statistics` MODIFY COLUMN `val` DOUBLE DEFAULT 0 COMMENT 'Ручное заполнение';
ALTER TABLE `rik_buh_statistics` ADD COLUMN `auto` DOUBLE DEFAULT 0 COMMENT 'Автоматическое наполнение' AFTER `val`;

DELETE FROM rik_buh_stat_header;
INSERT INTO rik_buh_stat_header (checkDate, isChecked)
SELECT checkdate, val from rik_buh_statistics where row=0 ;

UPDATE rik_buh_stat_header,  
(SELECT checkdate, val from rik_buh_statistics where row=-1) as a 
SET isSynced = 1
WHERE a.checkdate = rik_buh_stat_header.checkDate
AND a.val > 0;

UPDATE rik_buh_stat_header,  
(SELECT checkdate, val from rik_buh_statistics where row=-1) as a 
SET isFinished = 1
WHERE a.checkdate = rik_buh_stat_header.checkDate
AND a.val = 2;

UPDATE rik_buh_stat_header, rik_buh_statistics
set rik_buh_statistics.`headerRef` = rik_buh_stat_header.id
where
rik_buh_statistics.`checkdate` = rik_buh_stat_header.`checkDate`;

*/

 public function prepareBuhStatData($params)
 {
     
     $this->prepareDateList();    
     
     if ($this->buhStatPrepared==1) return;
     $this->buhStatPrepared = 1;
     $this->dataArray = $this->getSheduling();
     
/*Инит*/
 for ($row=1; $row<=21; $row++){
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
/* на показываемые 7 дней + 1 день нулевой - предыдущий */

      for ($col=0; $col<8; $col++)
      {
//echo "$col\n";          
        $this->checkedList[$col]=0;
        $this->finishedList [$col]=0;
        
       $checkdate = date("Y-m-d", $this->timeList[$col]); 
       $header = TblBuhStatHeader::findOne
       ([
       'checkDate'  => $checkdate,
       'isMonth'    => 0,
       ]);
       if (empty($header) || ($header->loadData ==0)) {
           $header=$this->fillAutoBuhStatData($this->timeList[$col]);
          if ($header == false ) continue;
       }        
       $headerRef = $header->id;

       $this->checkedList[$col]  = $header->isChecked;            
       $this->syncedList[$col]   = $header->isSynced;            
       $this->finishedList[$col] = $header->isFinished;                                      
        
        $list = Yii::$app->db->createCommand(
            'SELECT  val, auto, row FROM {{%buh_statistics}} WHERE headerRef = :headerRef',
            [ ':headerRef' => $headerRef ] )->queryAll();
        $vkey = 'v'.$col;    
        $ckey = 'c'.$col;    
        $skey = 's'.$col;    
    
        $N = count($list);
        for ($i=0;$i<$N;$i++)
        {
            $row=$list[$i]['row']; 
            if ($row > 0){
                $this->dataArray[$row][$vkey] = $list[$i]['val'];  
                $this->dataArray[$row][$ckey] = $list[$i]['auto'];  
                $this->dataArray[$row][$skey] = true;}                      
            
        }        
      }         
 
  //print_r($this->checkedList);
 }
/*
 Заполняем auto поля
*/
 public function fillAutoBuhStatData($dateTime)
{
  $checkdate = date("Y-m-d", $dateTime);

     $header = TblBuhStatHeader::findOne([
     'checkDate'  => $checkdate,
     'isMonth'    => 0,
     ]);
     if (empty($header)) {
         $header = new  TblBuhStatHeader();     
         if (empty($header)) return false;
         $header->checkDate = $checkdate;
     }    
         $header->loadData = 1;
         $header->save();
     
     //счета
     $record= TblBuhStatistics::findOne([
     'row' => 1,
     'headerRef' => $header->id
     ]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 1;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(schetSumm) FROM {{%schet}} WHERE schetDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
        
     //Отгрузки
     $record= TblBuhStatistics::findOne([
     'headerRef' => $header->id,
     'row' => 2
     ]);
     
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 2;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(supplySumm) FROM {{%supply}} WHERE supplyDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
        
            
     //Прибыль
     $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%profit_header}} WHERE DATE(onDate) = :checkdate',
             [ ':checkdate' => $checkdate  ] )->queryScalar();            
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 3]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 3;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(profit) FROM {{%profit_content}} WHERE  headerRef = :headerRef',
            [ ':headerRef' => $headerRef  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
             
     //Приход денег    
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 4]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 4;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(oplateSumm) FROM {{%oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
        
     //Траты    
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 5]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 5;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand('SELECT  SUM(oplateSumm) FROM {{%supplier_oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();

     //На счету    
     $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%control_bank_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => $checkdate  ] )->queryScalar();            
            
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 6]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 6;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(cashSum) FROM {{%control_bank}} 
        LEFT JOIN {{%control_bank_use}} on {{%control_bank_use}}.id = {{%control_bank}}.useRef
        WHERE  inUseReal= 1 AND  headerRef = :headerRef',   [ ':headerRef' => $headerRef  ] )->queryScalar();    
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
             
    //Приход товара    
    $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%control_purch_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => $checkdate  ] )->queryScalar();            
    
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 8]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 8;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(purchSum) FROM {{%control_purch_content}}       
        WHERE headerRef = :headerRef',   [ ':headerRef' => $headerRef  ] )->queryScalar();    
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
    
    
    //Склад    
    $headerRef = Yii::$app->db->createCommand( 'SELECT  MAX(id) FROM {{%ware_header}} WHERE DATE(onDate) = :checkdate',
            [ ':checkdate' => $checkdate ] )->queryScalar();            

     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 9]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 9;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(goodAmount*initPrice) FROM {{%ware_content}} WHERE
        goodAmount > 0 AND initPrice > 0 AND isActive = 1 AND headerRef = :headerRef',
            [ ':headerRef' => $headerRef  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
         
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 18]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 18;
     $record->headerRef= $header->id; 
     $record->auto = Yii::$app->db->createCommand( 'SELECT  SUM(goodAmount*initPrice) FROM {{%ware_content}} WHERE
        headerRef = :headerRef', [ ':headerRef' => $headerRef  ] )->queryScalar(); 
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
        
     //долги
     $headerRef =  Yii::$app->db->createCommand('SELECT MAX(id) FROM {{%control_sverka_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $checkdate, ])->queryScalar();  
     if (empty ($headerRef)) $headerRef = 0;

    $query  = new Query();
    $query->select ([
                     'u.balanceSum',
                     'u.typeRef',
                     ])
                    ->from("{{%control_sverka_dolga}} as u")
                    ->leftJoin("{{%control_sverka_dolga_use}} as fu","fu.id= u.useRef")                        
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= fu.fltRef")                        
                    ->distinct()
                   ->andWhere('f.isFilter = 1')
                   ->andWhere('u.headerRef = '.$headerRef)
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
         
         'isMigrate'=>0,
  ];        
        
    $list = $query->createCommand()->queryAll();    
    $N=count($list);
    for($i=0;$i<$N;$i++)
    {
      switch ($list[$i]['typeRef'])  
      {
          
      case 1:  /*Клиент*/
        if ($list[$i]['balanceSum'] > 0)  $this->balanceSum['clientCreditSum']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] < 0)  $this->balanceSum['clientDebetSum']+=$list[$i]['balanceSum'];
      break;
      
      case 2:  /*Поставщик*/      
        if ($list[$i]['balanceSum'] > 0)   $this->balanceSum['supplCreditSum']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] < 0)   $this->balanceSum['supplDebetSum']+=$list[$i]['balanceSum'];            
      break;        

      case 3:  /*Банк*/      
        $this->balanceSum['isBank']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] > 0)    $this->balanceSum['isBankCredit']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] < 0)    $this->balanceSum['isBankDebet']+=$list[$i]['balanceSum'];
      break;        

      case 4:  /*Услуги*/      
        if ($list[$i]['balanceSum'] > 0)    $this->balanceSum['isServiceCredit']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] < 0)    $this->balanceSum['isServiceDebet']+=$list[$i]['balanceSum'];
      break;        
      
      case 5:  /*Прочее*/      
        if ($list[$i]['balanceSum'] > 0)    $this->balanceSum['isOtherCredit']+=$list[$i]['balanceSum'];
        if ($list[$i]['balanceSum'] < 0)    $this->balanceSum['isOtherDebet']+=$list[$i]['balanceSum'];
      break;        
      
      case 6:  /*Перемещение*/      
        $this->balanceSum['isMigrate']+=$list[$i]['balanceSum'];        
      break;        

      case 7:  /*Черный список*/      
           $this->balanceSum['isBlack']+=$list[$i]['balanceSum'];        
      break;        
      
    }
   }    
   
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 7]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 7;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['isBank'];
    $record->save();
     
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 10]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 10;
     $record->headerRef= $header->id; 
     $record->auto =  $this->balanceSum['clientCreditSum'];
    $record->save();
    

    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 11]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 11;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['clientDebetSum'];        
    $record->save();



    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 12]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 12;
     $record->headerRef= $header->id; 
     $record->auto =  $this->balanceSum['supplCreditSum'];
    $record->save();

    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 13]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 13;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['supplDebetSum'];        
    $record->save();
        
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 14]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 14;
     $record->headerRef= $header->id; 
     $record->auto =  $this->balanceSum['isServiceCredit'];
    $record->save();
        
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 15]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 15;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['isServiceDebet'];        
    $record->save();

    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 16]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 16;
     $record->headerRef= $header->id; 
     $record->auto =  $this->balanceSum['isOtherCredit'];
    $record->save();
    
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 17]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 17;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['isOtherDebet'];        
    $record->save();
    
    $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 19]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 19;
     $record->headerRef= $header->id; 
     $record->auto =  -$this->balanceSum['isMigrate'];        
    $record->save();
    

     //Приход Всего    
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 20]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 4;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand( 'SELECT  SUM(oplateSumm) FROM {{%oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
        
     //Расход всего
     $record= TblBuhStatistics::findOne(['headerRef' => $header->id,'row' => 21]);
     if (empty($record)) $record = new  TblBuhStatistics();     
     $record->checkdate = $checkdate;
     $record->row = 5;
     $record->headerRef= $header->id; 
     $record->auto =  Yii::$app->db->createCommand('SELECT  SUM(oplateSumm) FROM {{%supplier_oplata}} WHERE oplateDate = :checkdate',
            [ ':checkdate' =>  $checkdate  ] )->queryScalar();
     if (empty($record->auto)) $record->auto = 0;   
     $record->save();
    
    
    return $header;
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
     $this->prepareBuhStatData("");
     
     $this->controlArray = Yii::$app->db->createCommand("Select id as idx, orderPosition, titleTask, isMarkNonZero 
     FROM {{%buh_check}} ORDER BY orderPosition")->queryAll();

     $N = count($this->controlArray); 
 
     /*Инит*/

     $refArray=[];
     for ($row=0; $row<$N; $row++){
 
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

    $list = Yii::$app->db->createCommand("Select id, refCheck, opType, refSrc, mult  
                                          FROM {{%buh_check_calc}} ")->queryAll();

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
 //     $this->debug[]=$this->dateList; 
//     $this->debug[]=$this->checkedList;  

      for ($col=1; $col<8; $col++)
      {
        $key  = 'c'.$col;
        $ckey = 'c'.$col;        
        $s    = 's'.$col;
        
        $j=$col-1;
        while($j>1)
        {
         if($this->checkedList[$j] == 1) break;
         $j--;         
        }    
        if ($j < 1 ) { $pkey='v0'; $pckey = 'c0'; }
        else         { $pkey='v'.$j; $pckey = 'c'.$j;} 
        
        $this->debug[]=
        [
        'col'     => $col,
        'key'     => $key,
        'current' => "Сегодняшний день ".$this->dateList[$col],
        'pkey'    => $pkey,
        'j'       => $j,
        'previous' => "Предыдущий день ".$this->dateList[$j],
        ];
        

      for ($row=0; $row<$N; $row++){
       $sum =0;
       $csum =0;
       
       $this->controlArray[$row][$s].= "<p>".$this->controlArray[$row]['titleTask']."</p><table class='table table-stripped'>";
       $this->controlArray[$row][$s].= "<tr><td></td><td>Ручной ввод</td><td>Из 1С</td></tr>";
       $this->controlArray[$row][$s].= "<tr><td colspan='3' align='center'><b>Сегодняшний день ".$this->dateList[$col]."</b></td></tr>";
       
       $curDataUse=$this->controlArray[$row]['curDataUse'];        
       //$this->debug[]=$this->dataArray;
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

       $this->controlArray[$row][$s].= "<tr><td colspan='3' align='center' ><b>Предыдущий день ".$this->dateList[$j]."</b></td></tr>";      
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
  
       

      }          
 
        
       if (!empty($this->dataArray[2][$key]))  
           $this->controlArray[5][$key] = 
           100*$this->dataArray[3][$key]/$this->dataArray[2][$key];         
    
      }
      
     
     $this->debug[]=$this->controlArray;
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
/*        $query  = new Query();
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
            ;*/