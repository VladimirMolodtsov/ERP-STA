<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

use app\modules\tasks\models\TblTasks;
use app\modules\tasks\models\TblOrgList;
use app\modules\tasks\models\TblUser;
use app\models\CalendarList;
/**
 * TasksEditForm - создание/редактирование задачи
 */
 
 class TasksAcceptForm extends Model
{
    
   public $debug;
   
   public $id = 0;   

  
    
    public function rules()
    {
        return [            
            [[ 'id', ], 'default'],                        
            [['id'], 'integer'],                        

        ];
    }

    public function setTaskDone($eventid, $execstate )
    {
       if(!empty($this->id)) $record = TblTasks::findOne($this->id);
       if(empty($record))    return false;
        
        
    }

/***************************************************/
    public function rejectEventToTask($eventid )
    {
      $curUser=Yii::$app->user->identity;    
      $eventRecord = CalendarList::findOne($eventid);
      if (empty($eventRecord)) return false;
      
      $record = TblTasks::findOne(['refCalendar' => $eventid ]);
      if (empty($record)){
          $record = new TblTasks();
          if (empty($record)) return false;
          $record->startDate    = date("Y-m-d H:i:s");
          $record->planDate     =  $eventRecord->event_date." ".$eventRecord->eventTime;
          $record->deadline     =  $eventRecord->event_date." ".$eventRecord->eventTime;
                    
          $record->creatorRef   = $curUser->id;
          $record->creationDate = date("Y-m-d H:i:s");
          $record->executorRef  = $curUser->id;
          $record->note         = $eventRecord ->eventNote;
          $record->orderType    = 0;                          // '0 -  самостоятельно взято\r\n1 - назначено руководством',
          $record->refOrg       = $eventRecord ->ref_org; 
            
      }
       
        $record->refCalendar=0; 
        $record->save();
        $eventRecord -> delete();
    }    
/************************//************************/         
    
    public function removeCurrentTask ()
    {
     if(!empty($this->id)) $record = TblTasks::findOne($this->id);
     if(empty($record))    return false;
    
     $record->delete();
    }   
/************************//************************/     
/************************//************************/     
    
    public function getOrgTitle($orgRef)
    {
      $record = TblOrgList::findOne($orgRef);
      if (empty($record)) return "";
      return $record->title;      
     }

    public function getUserFIO($userRef)
    {
      $record = TblUser::findOne($userRef);
      if (empty($record)) return "";
      return $record->userFIO;      
     }
     
     
    public function  getTimeList()
    {
    $timeList = array();
        $startTime=8.5*3600;
        for ($i=0; $i <64; $i++) 
        {
            $curTime=$startTime+ $i*600;
            $timeList [$i]=date("H:i", $curTime-3*3600);
        }            
     return $timeList;
    }
    
  /************End of model*******************/ 
 }
