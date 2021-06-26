<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\BuhStatistics;

/**
 * BuhEventForm - события глав. бух. 
 */
 
 
 
 
class BuhEventForm extends Model
{

   public $shiftTime = 3600;
   public $startTime = 8.5*3600;
   public $startH = 8.5;
   public $managerCount=0;

    public function getAllTimeList($strDate)       
    {
      //Получим список менеджеров
      
        $managerList = Yii::$app->db->createCommand( 'SELECT id, userFIO from {{%user}}         
        where (roleFlg & 0x0400) ' 
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
        for ($i=0; $i <64; $i++) 
        {
            //$h=intval($i/10+$this->startH);
            //$m=($i*10+60*$this->startH)-$h*60;
            $curTime=$startTime+ $i*600;
            $freeTimeList [$i]['eventRef']=0;          
            $freeTimeList [$i]['orgTitle']="";      
            $freeTimeList [$i]['strTime']=date("H:i", $curTime-3*3600);
            
            $freeTimeList [$i]['time']= $curTime-3*3600;
            
        }            
    
        $usedTime = Yii::$app->db->createCommand( 'SELECT {{%calendar}}.id, eventTime, {{%calendar}}.ref_org, {{%calendar}}.eventTitle, {{%orglist}}.title, 
        eventNote, eventStatus, preevent.note as preNote,postcontact.note as postNote, creatorRef, executorRef,
        refExecute, executeDateTime, {{%calendar}}.ref_contact, taskPriority 
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
        
/*      echo "<pre>";
      print_r($usedTime);
      echo "</pre>";
*/
    //return $freeTimeList;    
    
        for ($i=0; $i <$N; $i++) 
        {
          $curTime = strtotime($usedTime[$i]['eventTime']) - strtotime(date("Y-m-d"));
          $idx = intval( ($curTime - $startTime)/600 );
     //     echo $idx."\n";
          
          $freeTimeList [$idx]['eventRef']      =$usedTime[$i]['id'];          
          $freeTimeList [$idx]['eventStatus']   =$usedTime[$i]['eventStatus'];          
          $freeTimeList [$idx]['orgTitle']      =$usedTime[$i]['title'];
          $freeTimeList [$idx]['taskTitle']     =$usedTime[$i]['eventTitle'];            
          $freeTimeList [$idx]['orgRef']        =$usedTime[$i]['ref_org'];
          $freeTimeList [$idx]['eventTime']     =$usedTime[$i]['eventTime'];
          $freeTimeList [$idx]['taskPriority']  =$usedTime[$i]['taskPriority'];
          $freeTimeList [$idx]['refExecute']    =$usedTime[$i]['refExecute']; 
          $freeTimeList [$idx]['creatorRef']    =$usedTime[$i]['creatorRef']; 
          $freeTimeList [$idx]['executorRef']   =$usedTime[$i]['executorRef']; 
          
          if ($usedTime[$i]['ref_contact'] == 0)   $freeTimeList [$idx]['eventNote']=$usedTime[$i]['eventNote'];
          else                                     $freeTimeList [$idx]['eventNote']=$usedTime[$i]['preNote'];          
          if ($usedTime[$i]['refExecute'] > 0)     $freeTimeList [$idx]['execNote'] =$usedTime[$i]['postNote'];          
          else                                     $freeTimeList [$idx]['execNote'] =""; 
          
          $freeTimeList [$idx]['executeDateTime']=$usedTime[$i]['executeDateTime'];     
          $freeTimeList [$idx]['curTime']=$curTime;
        }

        
        $statModule= new BuhStatistics();
        $statModule->dtstart = $strDate;
        $statModule->prepareBuhStatData("");
        $N= count($statModule->dataArray);
        for ($i=1; $i <19; $i++) 
        {
        
          $curTime = strtotime(date("Y-m-d")." ".$statModule->dataArray[$i]['startTime']) - strtotime(date("Y-m-d"));
          $idx = intval( ($curTime - $startTime)/600 );

        
          $freeTimeList [$idx]['eventRef']      =0;          
          if ($statModule->dataArray[$i]['v6'] > 0) $freeTimeList [$idx]['eventStatus']   = 2;
          else $freeTimeList [$idx]['eventStatus']   = 1;
          $freeTimeList [$idx]['taskTitle']     =$statModule->dataArray[$i]['titleTask'];            
          $freeTimeList [$idx]['taskPriority']  =2;
          
          $freeTimeList [$idx]['creatorRef']    =0;//$usedTime[$i]['creatorRef']; 
          $freeTimeList [$idx]['executorRef']   =0;//$usedTime[$i]['executorRef']; 
          
          $freeTimeList [$idx]['refExecute']    =0;           
          
          $freeTimeList [$idx]['orgTitle']      ="";
          $freeTimeList [$idx]['orgRef']        =-1;
          $freeTimeList [$idx]['eventNote']     ="";          
          if ($statModule->dataArray[$i]['v6'] > 0)$freeTimeList [$idx]['execNote']      =$statModule->dataArray[$i]['v6'] ; 
          else $freeTimeList [$idx]['execNote'] ="";
                    
          $freeTimeList [$idx]['eventTime']     =$statModule->dataArray[$i]['execTime'];
                   
          $freeTimeList [$idx]['executeDateTime']=0;     
          $freeTimeList [$idx]['curTime']=$curTime;                
        
        }
        
        
        
             
        
        
                        
      //echo "</pre>";
        
      return $freeTimeList;
    }
    
    
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
        $list = Yii::$app->db->createCommand("Select {{%tasks}}.id, {{%tasks}}.note, planDate, {{%orglist}}.title as orgTitle, creatorRef, taskPriority
        FROM {{%tasks}} left join {{%orglist}} on {{%orglist}}.id = {{%tasks}}.refOrg 
        where executorRef = :userId  AND refCalendar= 0 Order by planDate ", [':userId' => $userId])->queryAll();
     return $list;

  }
  
  public function getMangerFIO($userId )
  {      
      return Yii::$app->db->createCommand("Select userFIO FROM {{%user}} where id = :userId", [':userId' => $userId])->queryScalar();
  }
/*****/
}
