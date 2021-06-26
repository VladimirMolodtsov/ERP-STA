<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactRegForm отображения полей и регистрация контакта 
 */
class ContactRegForm extends Model
{




    public function getFreeTimeList($date, $userId)
    {
        $startTime=8.5*3600;
        for ($i=0; $i <64; $i++) 
        {
            $curTime=$startTime+ $i*600;
            $freeTimeList [$i]['eventRef']=0;          
            $freeTimeList [$i]['orgTitle']="";      
            $freeTimeList [$i]['strTime']=date("H:i", $curTime);
            $freeTimeList [$i]['time']= $curTime;
            
        }            
    
        $usedTime = Yii::$app->db->createCommand( 'SELECT {{%calendar}}.id, eventTime, {{%orglist}}.title from {{%calendar}} 
        left join {{%orglist}} on {{%orglist}}.id = {{%calendar}}.ref_org
        where {{%calendar}}.ref_user=:refUser and event_date =:eventDate', 
        [
          ':refUser' => $userId,
          ':eventDate' => $date        
        ])->queryAll();

        $N= count($usedTime);
        for ($i=0; $i <$N; $i++) 
        {
          $curTime= strtotime($usedTime[$i]['eventTime']);
          $idx = intval( ($curTime - 8.5*3600)/600 );
          $freeTimeList [$idx]['eventRef']=$usedTime[$i]['id'];          
          $freeTimeList [$idx]['orgTitle']=$usedTime[$i]['title'];      
        }
      return $freeTimeList;
    }
    
    
    public function getFreeTime($date, $userId)
    {
      
      $freeTimeList=$this->getFreeTimeList($date, $userId);  
   /*   echo "<pre>";
      print_r($freeTimeList);
      echo "</pre>";*/
      $N = count($freeTimeList);
      for ($i=0; $i<$N; $i++)
      {
        if ($freeTimeList[$i]['eventRef']==0)
           return $freeTimeList[$i]['strTime'];
      }    
      $i--;
      return $freeTimeList[$i]['strTime']; //Если все плохо то в конец.
    }


  

}
