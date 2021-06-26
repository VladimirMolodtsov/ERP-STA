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

   public $note = "";

   /*Время и дата нативные*/
   public $startDate = "";
   public $acceptDate = "";
   public $planDate  = "";
   public $deadDate  = "";

   public $startTime = "";
   public $acceptTime = "";
   public $planTime  = "";
   public $deadTime  = "";
    
   public $orgTitle ="";
   /*В UNIX time*/
   public $startDT = "";
   public $planDT  = "";
   public $deadDT  = "";
   
   public $dt  = "";
   public $tm  = "";
   public $creatorFIO  = "";
  
    
    public function rules()
    {
        return [            
            [[ 'id',  'acceptDate',  'acceptTime', ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
            [['id'], 'integer'],                        
            [['note' ], 'string'],   
            [['note' ], 'trim'],   
        ];
    }


    public function acceptMarketTask()
    {
        
       if(!empty($this->id)) $record = TblTasks::findOne($this->id);
       if(empty($record))    return false;
       
       $curUser=Yii::$app->user->identity;
       if (Yii::$app->user->isGuest) {return false;} //гостям тут не место
      
       // Создадим новое                     
       $eventRecord = new CalendarList();
       $eventRecord ->ref_user= $record ->executorRef;
              
       $eventRecord ->event_date = date('Y-m-d', strtotime($this->acceptDate));
       $eventRecord ->eventTime  = date('H:i'  , strtotime($this->acceptTime)); 
       
       $eventRecord ->eventStatus=1;//назначено
       $eventRecord ->ref_contact=0;
       $eventRecord ->ref_event=8;
       $eventRecord ->ref_org=$record->refOrg;
       
       $eventRecord ->ref_zakaz=0;
       
       $eventRecord ->eventNote=$record->note;
       $eventRecord ->save();     

       $record -> refCalendar =  $eventRecord ->id;
       $record -> save();
       
       return     $eventRecord->id;
       
    }
    public function loadMarketTask()
    {
     $record=0;
     $curUser=Yii::$app->user->identity;  
     if(!empty($this->id)) $record = TblTasks::findOne($this->id);
     if(empty($record))    return false;
     
     $this->startDT = strtotime($record->startDate);  
     $this->planDT  = strtotime($record->planDate) ;  
     $this->deadDT  = strtotime($record->deadline) ;  
     
    
     $this->startDate = date("d.m.Y",$this->startDT);
     $this->planDate  = date("d.m.Y",$this->planDT);
     $this->deadDate  = date("d.m.Y",$this->deadDT);
     
     $this->startTime = date("H:i:s",$this->startDT);
     $this->planTime  = date("H:i:s",$this->planDT);
     $this->deadTime  = date("H:i:s",$this->deadDT);
     
     $this->note = $record->note;
     
     $this->creatorFIO = $this->getUserFIO($record->creatorRef);
     $this->orgTitle = $this->getOrgTitle($record->refOrg);

     $this->acceptDate  = $this->dt;
     $this->acceptTime  = $this->tm;

          
     return true;
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
