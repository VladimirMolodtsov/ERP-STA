<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper; 
/**
 * EventRegForm 
 */
class EventRegForm extends Model
{

   CONST  INTERVAL = 5*60; //интервал для задач пусть будет пять минут
   CONST  STARTH   = 8.5;  //стартовое время в часах 8:30
   CONST  ENDH     = 19.5;  //стартовое время в часах 8:30
   CONST  TIMESHIFT = 3*3600;//сдвиг по часовому времени
   
   //Инициализируем в конструкторе
   public $pN=0;      // Число периодов
   public $startTime; // стартовое время в секундах
   public $endTime=0;

   //Переменные
   
   public $managerCount=0;
   
   
   

   public function __construct( )
   {
    $this->startTime = self::STARTH * 3600;
    $this->endTime   = self::ENDH * 3600;
    $this->pN        = ($this->endTime-$this->startTime) / self::INTERVAL;    
    parent::__construct();
   }
   
    public function getAllTimeList($strDate)       
    {
      //Получим список менеджеров
      
        $managerList = Yii::$app->db->createCommand( 'SELECT id, userFIO from {{%user}}         
        where (roleFlg & 0x0004) ' 
        )->queryAll();
   
        $N = count($managerList); 
        $this->managerCount = $N;
        $list= array();
        for ($i=0; $i <$N; $i++) 
        {
            $list[$i]['timeList']=$this->getFreeTimeList($strDate, $managerList[$i]['id']);
            $list[$i]['userData']=$managerList[$i];
        }
        
        return $list;        
    }


    public function getFreeTimeList($strDate, $userId)
    {
        $date = date("Y-m-d", strtotime($strDate));        
        $startTime=$this->startTime;
        
        for ($i=0; $i <$this->pN ; $i++) 
        {
            //$h=intval($i/10+$this->startH);
            //$m=($i*10+60*$this->startH)-$h*60;
            $curTime=$startTime+ $i*self::INTERVAL;
            $freeTimeList [$i]['eventRef']=0;          
            $freeTimeList [$i]['orgTitle']="";      
            $freeTimeList [$i]['strTime']=date("H:i", $curTime-self::TIMESHIFT);            
            $freeTimeList [$i]['time']= $curTime-self::TIMESHIFT;
            
        }            
    
        $usedTime = Yii::$app->db->createCommand( 'SELECT {{%calendar}}.id, eventTime, {{%calendar}}.ref_org, {{%orglist}}.title, ref_zakaz,
        eventNote, eventStatus, preevent.note as preNote,postcontact.note as postNote, creatorRef, executorRef,
        refExecute, executeDateTime, {{%calendar}}.ref_contact, taskPriority, {{%tasks}}.taskTitle,
        {{%tasks}}.isPhone, {{%tasks}}.isDocument, {{%tasks}}.execNote 
        from {{%calendar}}         
        left join {{%contact}} as preevent on preevent.id = {{%calendar}}.ref_contact
        left join {{%contact}} as postcontact on postcontact.id = {{%calendar}}.refExecute
        left join {{%orglist}} on {{%orglist}}.id = {{%calendar}}.ref_org
        left join {{%tasks}}   on {{%tasks}}.refCalendar = {{%calendar}}.id
        where {{%calendar}}.ref_user=:refUser and event_date =:eventDate', 
        [
          ':refUser' => $userId,
          ':eventDate' => $date        
        ])->queryAll();

          
        $N= count($usedTime);
        
   /*   echo "<pre>";
      print_r($usedTime);
      echo "</pre>";*/

    //return $freeTimeList;    
    
        for ($i=0; $i <$N; $i++) 
        {
          $curTime = strtotime($usedTime[$i]['eventTime']) - strtotime(date("Y-m-d"));
          $idx = intval( ($curTime - $startTime)/self::INTERVAL ); //в какой интервал...
          if ($idx<0)$idx=0;
     //     echo $idx."\n";
          
          $freeTimeList [$idx]['eventRef']      =$usedTime[$i]['id'];          
          $freeTimeList [$idx]['eventStatus']   =$usedTime[$i]['eventStatus'];          
          $freeTimeList [$idx]['orgTitle']      =$usedTime[$i]['title'];      
          if ( empty($usedTime[$i]['taskTitle'])) $freeTimeList [$idx]['taskTitle']  =$usedTime[$i]['title'];      
                                            else  $freeTimeList [$idx]['taskTitle']  =$usedTime[$i]['taskTitle'];      
          
          $freeTimeList [$idx]['orgRef']        =$usedTime[$i]['ref_org'];
          $freeTimeList [$idx]['eventTime']     =$usedTime[$i]['eventTime'];
          $freeTimeList [$idx]['taskPriority']  =$usedTime[$i]['taskPriority'];
          $freeTimeList [$idx]['refExecute']    =$usedTime[$i]['refExecute']; 
          $freeTimeList [$idx]['creatorRef']    =$usedTime[$i]['creatorRef']; 
          $freeTimeList [$idx]['executorRef']   =$usedTime[$i]['executorRef'];
          $freeTimeList [$idx]['isPhone']       =$usedTime[$i]['isPhone']; 
          $freeTimeList [$idx]['isDocument']    =$usedTime[$i]['isDocument'];
          $freeTimeList [$idx]['ref_zakaz']     =$usedTime[$i]['ref_zakaz'];    
          
          
          if ($usedTime[$i]['ref_contact'] == 0)   $freeTimeList [$idx]['eventNote']=$usedTime[$i]['eventNote'];
          else                                     $freeTimeList [$idx]['eventNote']=$usedTime[$i]['preNote'];          
          if ($usedTime[$i]['refExecute'] > 0)     $freeTimeList [$idx]['execNote'] =$usedTime[$i]['postNote'];          
          else                                     $freeTimeList [$idx]['execNote'] =$usedTime[$i]['execNote']; 
          
          $freeTimeList [$idx]['executeDateTime']=$usedTime[$i]['executeDateTime'];     
          $freeTimeList [$idx]['curTime']=$curTime;
          
          
        }
                
      //echo "</pre>";
        
      return $freeTimeList;
    }
 /*
 ALTER TABLE `rik_tasks` ADD COLUMN `isPhone` SMALLINT DEFAULT 0 COMMENT 'Звонок';
 ALTER TABLE `rik_tasks` ADD COLUMN `isDocument` SMALLINT DEFAULT 0 COMMENT 'Работа с документами';
 ALTER TABLE `rik_tasks` ADD COLUMN `execNote` TEXT;
 */   
    
    public function getFreeTime($date, $userId)
    {
      
      $freeTimeList=$this->getFreeTimeList($date, $userId);  
      $N = count($freeTimeList);
  //    echo "<pre>";
  //    print_r($freeTimeList);
   //   echo "</pre>";

      for ($i=3; $i<$N; $i++)
      {
       if ($i>=27 && $i<=32) continue;
        if ($freeTimeList[$i]['eventRef']==0 || $freeTimeList [$i]['eventStatus'] == 2)
           return $freeTimeList[$i]['strTime'];
      }    
      $i--;
      
      
      return $freeTimeList[$i]['strTime']; //Если все плохо то в конец.
    }
    

   /********************************/
  public function getFreeTasks($userId)
  {
        $list = Yii::$app->db->createCommand("Select {{%tasks}}.id, {{%tasks}}.note, planDate, ifnull({{%tasks}}.tasktitle, {{%orglist}}.title) as taskTitle, {{%orglist}}.title as orgTitle, creatorRef, taskPriority
        FROM {{%tasks}} left join {{%orglist}} on {{%orglist}}.id = {{%tasks}}.refOrg 
        where executorRef = :userId  AND refCalendar= 0 Order by planDate ", [':userId' => $userId])->queryAll();
     return $list;

  }
  
  public function getMangerFIO($userId )
  {      
      return Yii::$app->db->createCommand("Select userFIO FROM {{%user}} where id = :userId", [':userId' => $userId])->queryScalar();
  }

/* Показываем маркер ***  */
  public function showMarker($id, $action, $isShowNext, $isExec, $showClass, $title)
  {
    
    if ($isShowNext == 0)
    {
        $class = 'hidden';
        $style ='';
    }
    else{                
            if ($isExec){                              
                $class = 'btn btn-primary circle';
                $style='background:Green;';             
               }    
               else    {                   
                   $class = $showClass.' clickable';
                   $style = "color:blue;";
               }
    }            
                  $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => $class.' clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => $title,
                   ]);
           
     return $val;             
  }

  public function showPrevStatus($timeList, $now)
  {            
      $sdelkaData['refSchet']=0;  
      $sdelkaData['docStatus']=0;  
      $sdelkaData['cashState']=0;  
      $sdelkaData['supplyState']=0;  
      $sdelkaData['isSchetActive']=0;
                
      if (!empty($timeList['ref_zakaz']))                
      {
        // Ищем последнее состояние перед сегодня
        $statList =  Yii::$app->db->createCommand( 'SELECT refStatusGrp, refStatusVal, refSchet, isSchetActive
            from {{%schet_status}} left join {{%schet}}  on {{%schet}}.refZakaz = {{%schet_status}}.refZakaz  
            where {{%schet_status}}.refZakaz=:refZakaz and dateOp < :DATE ORDER BY dateOp DESC LIMIT 3', 
            [  ':refZakaz' => intval($timeList['ref_zakaz']),   
               ':DATE' => date("Y-m-d", $now)   
            ])->queryAll();
        
        if (count($statList) == 3)
        {
            $sdelkaData['refSchet'] = $statList[0]['refSchet'];
            for ($i=0;$i<3; $i++){
              switch ($statList[$i]['refStatusGrp'])    
              {
                 case 1:
                    $sdelkaData['docStatus']=$statList[$i]['refStatusVal']; 
                 break;
                 case 2:
                    $sdelkaData['cashState']=$statList[$i]['refStatusVal']; 
                 break;   
                 case 3:                  
                    $sdelkaData['supplyState']=$statList[$i]['refStatusVal']; 
                 break;                   
              }                
            }                
        }else {
        $statList =  Yii::$app->db->createCommand( 'SELECT {{%contact}}.docStatus, {{%contact}}.cashStatus, {{%contact}}.supplyStatus, 
        {{%schet}}.id as refSchet, isSchetActive
            from {{%contact}} left join {{%schet}}  on {{%schet}}.refZakaz = {{%contact}}.refZakaz  
            where {{%contact}}.refZakaz=:refZakaz and contactDate < :DATE ORDER BY {{%contact}}.id DESC LIMIT 3', 
            [  ':refZakaz' => intval($timeList['ref_zakaz']),   
               ':DATE' => date("Y-m-d", $now)                   
            ])->queryAll();     
           if (count($statList ) > 0) 
           {
                $sdelkaData['refSchet']=$statList[0]['refSchet'];  
                $sdelkaData['docStatus']=$statList[0]['docStatus'];  
                $sdelkaData['cashState']=$statList[0]['cashStatus'];  
                $sdelkaData['supplyState']=$statList[0]['supplyStatus'];  
                $sdelkaData['isSchetActive']=$statList[0]['isSchetActive'];               
           }
            
        }    


      }          
      
                
      return $this->showCurrentStatus($timeList,$sdelkaData);                
  }

  public function showCurrentStatus($timeList,$sdelkaData)
  {
      $strOutPut ="";
      $isShowNext = 0;
/*Спрос**/
               $id = "isSpros".$timeList['eventRef'];                       
               if (empty($timeList['orgRef'])) $action ="";
                                         else  $action = "regContact(".$timeList['orgRef'].");";                                                                  
               if (!empty($timeList['ref_zakaz']))
               {
                $class = 'btn btn-primary circle';
                $isShowNext = 1;   
               }
               else    {                        
                $class = 'glyphicon glyphicon-question-sign clickable';                  
                $style="";
                 $isShowNext = 0;   
               //$val= "<span onclick='".$action."' style='color:Blue;' class='glyphicon glyphicon-question-sign clickable' title='Спрос'></span>"; 
              }
               $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => $class,
                   ]);

if ($isShowNext == 0)
                $strOutPut .= "<td align='left'  class='info_cell' >".$val."&nbsp;Спрос</td>";
        

/*Заявка**/
if ($isShowNext != 0){    
               $id = "isZayavka".$timeList['eventRef'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showZakaz(".$timeList['orgRef'].",".$timeList['ref_zakaz'].");";                                  

               $isShowCur = $isShowNext; 
               if (!empty($sdelkaData['refSchet']))  $isExec= true;
               else {$isExec= false; $isShowNext = 0;
                $sdelkaData['refSchet']=0;  
                $sdelkaData['docStatus']=0;  
                $sdelkaData['cashState']=0;  
                $sdelkaData['supplyState']=0;  
                $sdelkaData['isSchetActive']=0;}
                                         
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-file',
                   ]);
               
if ($isShowNext == 0)
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;Заявка</td>\n";
}                
/*Счет**/
if ($isShowNext != 0){    
               $id = "isSchet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                  

               $isShowCur = $isShowNext;                
               if ($sdelkaData['docStatus'] >= 2)  $isExec= true;
                                             else {$isExec= false; $isShowNext = 0; }
               
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-list-alt',
                   ]);
               
if ($isShowNext == 0)
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;Счет</td>\n";
}                                         
                                         
/*Деньги**/
if ($isShowNext != 0){    
               $id = "isCashGarant".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                        else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                   

               $isShowCur = $isShowNext;                
               if ($sdelkaData['cashState'] >= 1)  $isExec= true;
                                             else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-check',
                   ]);
               
if ($isShowNext == 0)               
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;П/О</td>\n";
}                                                    
/*Т-**/
if ($isShowNext != 0){    
               $id = "isSupply".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    
               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 1)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-shopping-cart',
                   ]);
               
if ($isShowNext == 0)               
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;Т-</td>\n";
}                               
/*$+**/
if ($isShowNext != 0){    
               $id = "isCashGet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

               $isShowCur = $isShowNext; 
               if ($sdelkaData['cashState'] >= 4)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-usd',
                   ]);
               
if ($isShowNext == 0)               
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;$+</td>\n";
}                   
/*Док.**/
if ($isShowNext != 0){    
               $id = "isDocGet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 4)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }

               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-folder-open',
                   ]);
               
if ($isShowNext == 0)                              
               $strOutPut .= "<td align='left'  class='info_cell'> ".$val."&nbsp;Документы</td>\n";
}                   
                                         
/*Стоп.**/
if ($isShowNext != 0){              
               $id = "isFinished".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

                                         
               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 5)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                          
               $val = $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-ok-circle',
                   ]);
               
               $strOutPut .= "<td align='left'  class='info_cell'>".$val."&nbsp;Стоп</td>\n";
                }                          

      
      return $strOutPut ;
  }
  
  
  /*****/
}
