<?php

namespace app\modules\zadarma\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\zadarma\models\TblAtsState;
use app\modules\zadarma\models\TblAtsLog;
use app\modules\zadarma\models\TblAts;
use app\modules\zadarma\models\TblAtsRedirection;
use app\modules\zadarma\models\ZadarmaMainForm;
use app\modules\zadarma\models\TblPhones;
use app\modules\zadarma\models\TblUser;
/*
    Принимает и сохраняет данные от АТС zadarma.com

*/
 
 class ZadarmaAtsState extends Model
 {
    
    public $refRaw = 0;
    public $internal = 0;
    
    public $external_num ="";  // номер внешнего вбонента
    public $internal_num ="";  // номер внутреннего абонента
    
    public $caller_id ="";  // номер звонящий
    public $called_did =""; // номер принимающий
    
    public $destination ="";
    public $event ="";
    public $call_start ="";
    public $pbx_call_id ="";
    public $duration =0;
    public $disposition =""; //состояние звонка:
    public $is_recorded = 0;
    public $call_id_with_rec =""; 
    public $status_code ="";

    public $debug;
    
    public function rules()
    {
        return [            
            [['internal','$external_num','internal_num','destination','event','call_start', 'caller_id', 'called_did',
            'pbx_call_id','duration','disposition','is_recorded','call_id_with_rec', 'status_code' ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

  public function rescanAllLog()
  {

     $rawLogList = Yii::$app->db->createCommand('SELECT id FROM   {{%ats}} order by id')->queryAll();	
     $N= count ($rawLogList);
     for ($i=0;$i<$N; $i++ )
     {
       $this->registerLog($rawLogList[$i]['id']);        
       if ($i%100==0) echo $rawLogList[$i]['id']." from $N \n"; 
     }
  }

  public function prepareLogRecord($data)
  {
      
    $this->event = $data['event'];
    $this->pbx_call_id= $data['pbx_call_id'];      
    
    $this->internal = 0;
    $this->call_start = date('Y-m-d H:i:s');
    $this->caller_id = 0;
    $this->destination= 0;
    $this->duration= 0;
    $this->disposition = 0;
    $this->called_did  = 0;
    $this->called_did  = 0;
    $this->status_code = 0; 
    $this->call_id_with_rec  = 0;
    $this->is_recorded = 0;
    
    if (isset($data['internal']))    $this->internal    = $data['internal'];
    if (isset($data['call_start']))  $this->call_start  = date('Y-m-d H:i:s', strtotime($data['call_start']));
    if (isset($data['caller_id']))   $this->caller_id   = $data['caller_id'];
    if (isset($data['destination'])) $this->destination = $data['destination'];
    if (isset($data['duration']))    $this->duration    = intval($data['duration']);
    if (isset($data['disposition'])) $this->disposition = $data['disposition'];
    if (isset($data['called_did']))  $this->called_did  = $data['called_did'];
    if (isset($data['status_code'])) $this->status_code = $data['status_code'];
    if (isset($data['call_id_with_rec'])) $this->call_id_with_rec  = $data['call_id_with_rec'];
    
    if (isset($data['is_recorded'])) $this->is_recorded    = $data['is_recorded'];
  
    if (empty ($this->internal)) $this->internal = 0; 
  }
  public function registerLog($id)
  {
    $record= TblAts::findOne($id);    
    if (empty($record)) { return false ;}
    $this->refRaw=$id;
    
    $parse=explode ( "\n" , $record->data);

    $N= count($parse);
    for ($i=2;$i<$N-2;$i++) 
    {        
     $sp = explode("=>", $parse[$i]);
     $key=preg_replace("/[\[\]\s+]/","",$sp[0]);
     $value=trim($sp[1]);
     $data[$key]=$value;  
    }        
    
    if (empty ($data['event'])) return false;        
    $this->prepareLogRecord($data);
    
    
    return $this->saveData();

  }
  
  
  
  /**************************/
  public function saveData()
  {
   /*$atsRaw = new TblAts();
   $atsRaw->data = print_r($POST,true);
   $atsRaw->save();*/
   
   if (empty ($this->event)) return false;    
   
   /*Звонок внутрь - старт*/
   if ($this->event == 'NOTIFY_START')     $this->register_NOTIFY_START();   
   /*Звонок внутрь - завершение*/
   if ($this->event == 'NOTIFY_END')       $this->register_NOTIFY_END();   

   /*Звонок наружу - старт*/
   if ($this->event == 'NOTIFY_OUT_START') $this->register_NOTIFY_OUT_START();
     /*Звонок наружу - завершение*/
   if ($this->event == 'NOTIFY_OUT_END')   $this->register_NOTIFY_OUT_END();
   
   /*Звонок наружу - старт*/
   if ($this->event == 'NOTIFY_INTERNAL') $this->register_NOTIFY_INTERNAL();
      
   
    return true;
  }
  
  /**************************/
  public function savePost($POST)
  {
   
    $this->prepareLogRecord($POST);
    if (empty ($this->event)) return false;    
   
    return $this->saveData();
  }
 
 /**************************/ 
  public function getOrgByPhone($phone)
  {
   $record = TblPhones::findOne([
   'phone' => $phone,
   ]);
   if (empty($record)) return 0;
   
   return  $record->ref_org;
  }

  public function getUserById($id)
  {
   $record = TblUser::findOne([
   'phoneInternаl' => $id,
   ]);
   if (empty($record)) return 0;   
   return  $record->id;
  }
  
 /**************************/ 
  public function getInternalId()
  {     
  return;  
    if (!empty($this->internal) ) return;
    $recordRedirection = TblAtsRedirection::FindOne(['redirect' => $this->called_did ]);     
     if (empty ($recordRedirection))
     {
        $modelApi = new ZadarmaMainForm(); 
        $modelApi->getRedirections(); // синхронизируем заново       
       // $recordRedirection = TblAtsRedirection::FindOne(['redirect' => $this->called_did ]);
     } 
     $recordRedirection = TblAtsRedirection::FindOne(['redirect' => $this->called_did ]);     
     if (empty ($recordRedirection)) $this->internal = 0;
     else $this->internal = $recordRedirection->ats_id;      
  }
  
 /**************************/
 /*добавлем лог, обьединяя события на один id*/  
  public function getRecordLog()
  {     
   $recordLog = TblAtsState::FindOne(['pbx_call_id' => $this->pbx_call_id]);
   if(empty($recordLog)) 
   {
       $recordLog = new TblAtsLog();
       $recordLog->pbx_call_id = $this->pbx_call_id;
   }   
   return $recordLog;
  }

  
  /**************************/    
  public function register_NOTIFY_START()     
  {

     $recordLog = new TblAtsLog();
     if(empty($recordLog)) return false; //не смогли записать
     
     $recordLog->refRaw = $this->refRaw;
     $recordLog->event    = $this->event;  
     $recordLog->call_start = $this->call_start; 
     $recordLog->pbx_call_id = $this->pbx_call_id;
     $recordLog->caller_id    = $this->caller_id; 
     $recordLog->called_did   = $this->called_did; 
     
     //Номер контрагента
     $recordLog->external_num = $this->caller_id ;
     
     //Номер наш телефона
     $recordLog->internal_num = $this->called_did;
      
     /*привязываем к сущностям базы*/ 
     $recordLog->orgRef = $this->getOrgByPhone($recordLog->external_num);
     //$recordLog->managerRef = $this->getUserById($recordLog->internal_id);
      
     $recordLog->save(); 
  }
  /**************************/    
  public function register_NOTIFY_END()     
  {
     
     $recordLog = new TblAtsLog();
     if(empty($recordLog)) return false; //не смогли записать
     
     $recordLog->refRaw = $this->refRaw;
     $recordLog->event = $this->event;  
     $recordLog->call_start = $this->call_start;      
     $recordLog->pbx_call_id = $this->pbx_call_id;
     $recordLog->caller_id    = $this->caller_id; 
     $recordLog->called_did   = $this->called_did;    
     $recordLog->internal = $this->internal;
     $recordLog->duration =  $this->duration;
     $recordLog->disposition =  $this->disposition;      
     $recordLog->status_code      = $this->status_code;
     $recordLog->is_recorded      = $this->is_recorded;
     $recordLog->call_id_with_rec = $this->call_id_with_rec;
     
      /*Определяем кто, когда, кому - вроде тут нет колбэка*/
      
     {     
        $recordLog->external_num = $this->caller_id; //Номер контрагента           
        $recordLog->internal_num = $this->called_did ;  //Номер наш телефона            
        $recordLog->internal_id  = $this->internal;    
     }

     /*привязываем к сущностям базы*/ 
     $recordLog->orgRef = $this->getOrgByPhone($recordLog->external_num);
     $recordLog->managerRef = $this->getUserById($recordLog->internal_id); 
     
     $recordLog->save();
     
  }

 /**************************/    
  public function register_NOTIFY_OUT_START()
   {
       
     $recordLog = new TblAtsLog();
     if(empty($recordLog)) return false; //не смогли записать
     

     /*Общая информация*/      
     $recordLog->refRaw = $this->refRaw;
     $recordLog->event = $this->event;
     $recordLog->call_start = $this->call_start; 
     $recordLog->pbx_call_id = $this->pbx_call_id;
     
     /*Как есть в ответе*/
     $recordLog->destination = $this->destination;
     $recordLog->caller_id   = $this->caller_id; 
      //Номер наш в АТС
     $recordLog->internal = $this->internal;
      
      
     /*Определяем кто, когда, кому*/
     if ($this->caller_id == 0) /*callback*/  
     {
       $recordLog->external_num = $this->internal; //Номер контрагента  - звонит как  бы он           
       $recordLog->internal_id  = $this->destination; // на внутренний номер АТС     
       $recordLog->internal_num = $this->destination ;  //Номер наш телефона - совпадает с номером в атс             
     }
     else
     {     
        $recordLog->external_num = $this->destination; //Номер контрагента           
        $recordLog->internal_num = $this->caller_id;  //Номер наш телефона            
        $recordLog->internal_id  = $this->internal;    
     }
    
     /*привязываем к сущностям базы*/ 
     $recordLog->orgRef = $this->getOrgByPhone($recordLog->external_num);
     $recordLog->managerRef = $this->getUserById($recordLog->internal_id); 

    
     $recordLog->save();     
   }
  
  
   /**************************/    
   public function register_NOTIFY_OUT_END()
   {

     $recordLog = new TblAtsLog();
     if(empty($recordLog)) return false; //не смогли записать
     
     $recordLog->refRaw = $this->refRaw;
     $recordLog->event = $this->event;    
     $recordLog->call_start = $this->call_start;      
     $recordLog->pbx_call_id = $this->pbx_call_id;
     $recordLog->caller_id = $this->caller_id; 
     $recordLog->destination = $this->destination;
     $recordLog->internal = $this->internal;
     $recordLog->duration =  $this->duration;
     $recordLog->disposition =  $this->disposition;      
     $recordLog->status_code      = $this->status_code;
     $recordLog->is_recorded      = $this->is_recorded;
     $recordLog->call_id_with_rec = $this->call_id_with_rec;
     
     /*Определяем кто, когда, кому*/
     if ($this->caller_id == 0) /*callback*/  
     {
       $recordLog->external_num = $this->internal; //Номер контрагента  - звонит как  бы он           
       $recordLog->internal_id  = $this->destination; // на внутренний номер АТС     
       $recordLog->internal_num = $this->destination ;  //Номер наш телефона - совпадает с номером в атс             
     }
     else
     {     
        $recordLog->external_num = $this->destination; //Номер контрагента           
        $recordLog->internal_num = $this->caller_id;  //Номер наш телефона            
        $recordLog->internal_id  = $this->internal;    
     }

     /*привязываем к сущностям базы*/ 
     $recordLog->orgRef = $this->getOrgByPhone($recordLog->external_num);
     $recordLog->managerRef = $this->getUserById($recordLog->internal_id); 
          
     $recordLog->save();      
   }
  
  
  
   /**************************/    
   /*
   начало входящего звонка на внутренний номер АТС. - ???
   */
   public function register_NOTIFY_INTERNAL()
   {   
  
 
     $recordLog = new TblAtsLog();     
     if(empty($recordLog)) return false; //не смогли записать
     $recordLog->refRaw = $this->refRaw;
     $recordLog->pbx_call_id = $this->pbx_call_id;

     $recordLog->event = $this->event;    
     $recordLog->call_start = $this->call_start; 
          
     $recordLog->caller_id = $this->caller_id; 
     $recordLog->called_did = $this->called_did; 
     
     $recordLog->internal = $this->internal; 
     
     $recordLog->save();      
   }
  
   /**************************/    
   /*
   ответ при звонке на внутренний или на внешний номер. - ???
   */
   public function register_NOTIFY_ANSWER()
   {   
 
    $recordLog = new TblAtsLog();
    if(empty($recordLog)) return false; //не смогли записать
    $recordLog->refRaw = $this->refRaw;
    $recordLog->pbx_call_id = $this->pbx_call_id;

    $recordLog->event = $this->event;      
    $recordLog->caller_id = $this->caller_id; 
    $recordLog->destination  = $this->destination ; 
    $recordLog->call_start = $this->call_start; 
    $recordLog->internal = $this->internal; 
    $recordLog->save();      
   }
    
  
  
  /************End of model*******************/ 
 }
