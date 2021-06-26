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
use app\modules\tasks\models\TblCalendar;
/**
 * TasksEditForm - создание/редактирование задачи
 */
 
 class TasksEditForm extends Model
{
    
   public $debug;
   
   public $id = 0;   
   public $moduleRef = 0;
   public $taskCode = 0;
   public $note = "";
   public $executorRef = 0;
   public $repeater   = 0;
   public $taskPriority   = 0;

   /*Время и дата нативные*/
   public $startDate = "";
   public $planDate  = "";
   public $deadDate  = "";

   public $startTime = "";
   public $planTime  = "";
   public $deadTime  = "";
   
   /* действие */
   public $action = 'save';    
   
   /*Время и дата в таймштампе*/
   public $startDateTs = 0;
   public $planDateTs  = 0;
   public $deadlineTs  = 0;
   public $orgRef = 0;
   public $orgTitle ="";
   public $taskTitle ="";
  

   public $dataRequestId;
   public $dataType;
   public $dataVal;

    
    public function rules()
    {
        return [            
            [['action', 'id', 'orgRef', 'moduleRef', 'startDate', 'planDate', 'deadDate', 'startTime', 'planTime', 'deadTime', 
            'taskCode', 'note', 'executorRef', 'taskPriority', 'taskTitle',  'dataRequestId', 'dataType', 'dataVal' ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
            [['id', 'orgRef', 'moduleRef', 'executorRef', 'taskCode', 'repeater' ], 'integer'],                        
/*            [['startDate' ], 'datetime', 'format'=>'yyyy-MM-dd HH:mm:ss', 'message'=>'Начало исполнения задачи ', 'timestampAttribute'=>'startDateTs'],                        
            [['planDate'  ], 'datetime', 'timestampAttribute'=>'planDateTs'],                        
            [['deadline'  ], 'datetime', 'message'=>'Дата/время после которого задача считается проваленной', 'timestampAttribute'=>'deadlineTs'],   */                     
            [['note' ], 'string'],   
            [['note' ], 'trim'],   
        ];
    }

    public function getOrgTitle($orgRef)
    {
      $record = TblOrgList::findOne($orgRef);
      if (empty($record)) return "";
      return $record->title;      
     }
     
    public function getPriorityList ()
    {
        return [
            0 => 'Нормальный',
            1 => 'Поручение',
            2 => 'Приказ',        
        ];
    
    }
    public function saveMarketTask()
    {
     $record=0;
     $curUser=Yii::$app->user->identity;  
     if(!empty($this->id)) $record = TblTasks::findOne($this->id);     
     if(empty($record))    $record = new TblTasks();

     $record->startDate = date("Y-m-d",strtotime($this->startDate))." ".$this->startTime;     //` DATETIME DEFAULT NULL COMMENT 'дата старта исполнения задачи',
     $record->planDate  = date("Y-m-d",strtotime($this->planDate))." ".$this->planTime;      //` DATETIME DEFAULT NULL COMMENT 'плановое время исполнения',
     $record->deadline  = date("Y-m-d",strtotime($this->deadDate))." ".$this->deadTime;      //` DATETIME DEFAULT NULL COMMENT 'дедлайн - крайний срок, провалено, если не выполнено.',     
     $record->creatorRef    = $curUser->id; //` BIGINT(20) DEFAULT NULL COMMENT 'ссылка на постановщика задачи',
     $record->creationDate  = date("Y-m-d H:i:s");//` DATETIME DEFAULT NULL COMMENT 'Дата создания',
     $record->executorRef   =$this->executorRef; //` BIGINT(20) DEFAULT 0 COMMENT 'ссылка на исполнителя',
     $record->note          = $this->note; //` TEXT COLLATE utf8_general_ci,
     $record->orderType =1;
     $record->taskPriority =intval($this->taskPriority);
     $record->taskTitle = $this->taskTitle;     
     $record->refOrg = $this->orgRef;
           
    $record->save();
     
   /* echo "<pre>";
    print_r($record);
    echo "</pre>";    */
     return true;
    }
/***************/    
    public function saveAjaxData ()
    {
     $curUser=Yii::$app->user->identity;  
     $isSwitch = 1;
     $record = TblTasks::findOne(['refCalendar' => $this->dataRequestId]);     
     if(empty($record))     
     {   $record = new TblTasks();
         $record->creatorRef   =  $curUser->id;
         $record->creationDate =  date("Y-m-d H:i:s") ;
         $record->refCalendar =  $this->dataRequestId;             
     } 
     if(empty($record)) return [
           'res' => false,
           'dataType' => $this->dataType,           
           'dataRequestId'  => $this->dataRequestId,
     ];
        
     $val=0;       
     switch($this->dataType)
     {   
      case 'isPhone':
         if($record->isPhone == 0) $record->isPhone = 1;
                              else $record->isPhone = 0;
         $val = $record->isPhone;             
         break; 
  
      case 'isDocument':
         if($record->isDocument == 0) $record->isDocument = 1;
                                 else $record->isDocument = 0;
         $val = $record->isDocument;             
         break; 
       
     }
    
     $record->save();
     
     return [ 'res' => true ,
           'isSwitch' => $isSwitch,
           'dataType' => $this->dataType,           
           'val' => $val,
           'dataRequestId'  => $this->dataRequestId,
    ];     
    }
    
    
public function saveSetExec ()    
{
     $curUser=Yii::$app->user->identity;  
     $isSwitch = 1;
     $record = TblCalendar::findOne(['id' => $this->dataRequestId]);     

     if(empty($record)) return [
           'res' => false,
           'dataType' => $this->dataType,           
           'val' => $this->dataVal,
           'dataRequestId'  => $this->dataRequestId,
     ];
        
        $record->executeDateTime   =   date("Y-m-d H:i:s") ;
        $record->eventStatus       = 2;
        $record->save();
        
     $recordTask = TblTasks::findOne(['refCalendar' => $record->id]);     
     if(empty($record))     
     {   $recordTask = new TblTasks();
         $recordTask->creatorRef   =  $curUser->id;
         $recordTask->creationDate =  date("Y-m-d H:i:s") ;
         $recordTask->refCalendar =  $record->id;             
     } 
     if(!empty($recordTask)) 
     {
       $recordTask->execNote =  $this->dataVal;
       $recordTask->save();
     }
        
    return [
           'res' => true,
           'dataType' => $this->dataType,           
           'val' => $this->dataVal,
           'dataRequestId'  => $this->dataRequestId,
     ];        
}
    
/************************//************************/     
/************************//************************/     
    public function taskCreate()
    {
      $curUser=Yii::$app->user->identity;  
      $record = new TblTasks();
      if (empty($record)) return false;
      
      $record->creatorRef   =  $curUser->id;
      $record->creationDate =  date("Y-m-d H:i:s") ;
      $record->save();
      
      return $record->id;  
    }

    public function taskDelete($id)
    {
        $record = TblTasks::findOne($id);
        $record->delete();
    }
    
    public function loadData()
    {
        $curUser=Yii::$app->user->identity;  
        $record = TblTasks::findOne($this->id);
        if (empty($record))
        {
            if(empty($this->startDate)) $this->startDate = date ("d.m.Y");
            if(empty($this->planDate))  $this->planDate  = date ("d.m.Y");
            if(empty($this->deadDate))  $this->deadDate  = date ("d.m.Y");
            if(empty($this->startTime)) $this->startTime = date ("H:00",time()+3600);
            if(empty($this->planTime))  $this->planTime  = date ("H:00",time()+2*3600);
            if(empty($this->deadTime))  $this->deadTime  = "19:00";
            $this->taskPriority = 0;
            //$this->creatorRef   =  $curUser->id;            
         return false;         
        }
                      
        $startDT = strtotime($record->startDate);             
        $planDT  = strtotime($record->planDate);
        $deadDT  = strtotime($record->deadline);
        $this->startDate = date ("d.m.Y", $startDT);
        $this->planDate  = date ("d.m.Y", $planDT);
        $this->deadDate  = date ("d.m.Y", $deadDT);
        $this->startTime = date ("H:i:s", $startDT);
        $this->planTime  = date ("H:i:s", $planDT);
        $this->deadTime  = date ("H:i:s", $deadDT);
        
        $this->moduleRef = $record->moduleRef;
        $this->taskCode  = $record->taskCode;
        $this->note      = $record->note;
        $this->executorRef = $record->executorRef;
        $this->taskPriority = $record->taskPriority;
        $this->taskTitle = $record->taskTitle;     
        return reue;;         
    }
    
    
    public function saveData()
    {
      print_r($this->startDateTs);
    }

    public function getModulesList()    
    {        
     $list = Yii::$app->db->createCommand("Select id, moduleTitle FROM {{%modules}} order by id")->queryAll();
     return ArrayHelper::map($list, 'id', 'moduleTitle');   
    }

    public function getTasksList()    
    {
     if (empty($this->moduleRef) )   
        $list = Yii::$app->db->createCommand("Select id, taskTitle FROM {{%tasks_var}} order by id")->queryAll();
     else
        $list = Yii::$app->db->createCommand("Select id, taskTitle FROM {{%tasks_var}} where moduleRef=:moduleRef order by id")
        ->bindValue([':moduleRef' => $this->moduleRef])->queryAll();    
     return ArrayHelper::map($list, 'id', 'taskTitle');   
    }
    
    public function getManagerList()
    {        
     $list = Yii::$app->db->createCommand("Select id, userFIO FROM {{%user}} where roleFlg > 0  order by id")->queryAll();
     return ArrayHelper::map($list, 'id', 'userFIO');   
    }
    
    public function getRepeatList()
    {        
     $list = Yii::$app->db->createCommand("Select id, title FROM {{%task_repeat}}  order by id")->queryAll();
     return ArrayHelper::map($list, 'id', 'title');   
    }
    

    
  /************End of model*******************/ 
 }
