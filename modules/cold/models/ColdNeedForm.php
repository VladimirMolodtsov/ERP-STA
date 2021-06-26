<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\NeedList;
use app\models\ContactList;
use app\models\ModuleList;

use app\modules\cold\models\TblCold;
use app\modules\cold\models\TblColdTextModules;

//use app\models\MarketCalendarForm;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class ColdNeedForm extends Model
{
    public $id = 0;
    public $orgTitle = 0;
    public $note = "";
    public $speak_res = 0;
    public $contactDate = "";
    public $contactEmail ="";
    public $contactFIO ="";
    public $contactPhone="";
    public $currentPhone="";
    public $period = 0;
    
    public $prevContactText = "";
    public $prevContactFIO = "";
    public $prevContactDate = "";        
    

    public $regular ="";
    public $interes ="";

        
    public function rules()
    {
        return [            
            
            [[ 'note', 'period', 'contactFIO', 'contactEmail', 'contactPhone', 'regular', 'interes' ], 'default'],            
            [['contactFIO', 'contactEmail', 'contactPhone', 'note', 'interes'], 'trim'],
            ['regular', 'integer'],            
            ['id', 'integer'],
            ['contactEmail', 'email'],
        ];
    }
    

   public function getCompanyPhones()
   {
          $ret =  Yii::$app->db->createCommand('SELECT phone, status from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->id])->queryAll();       
        return $ret;
   }   
   
   
   public function getNeedListN()
   {       
       $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%need_title}}' )->queryScalar();           
       return $ret;
   }
    public function getNeedList()
    {
      $ret =  Yii::$app->db->createCommand('SELECT Title, row from {{%need_title}} order by row')->queryAll();             
      return $ret;
    }
    
    
    public function saveData()        
   {
      $record = OrgList::findOne($this->id);
      if (empty($record)) return;
      
      $curUser=Yii::$app->user->identity;
    
    
      $record->ref_user = $curUser->id;          
      $record->contactDate = date("Y.m.d");
      $record->contactPhone = $this->contactPhone;
      $record->contactEmail = $this->contactEmail;
      $record->contactFIO = $this->contactFIO;
      $record->nextContactDate = date("Y-m-d", time()+60*60*24*2); //В течении 2 дней                    
     
      $record->save();
    
      
      $phoneRecord = PhoneList::findOne([
      'ref_org' => $this->id,
      'phone'   => $this->contactPhone,
      ]);                  
      if (empty($phoneRecord))
      {          
         $phoneRecord = new PhoneList ();
         $phoneRecord->ref_org = $this->id;
         $phoneRecord->phone   = $this->contactPhone;
      }
      
      $phoneRecord->phoneContactFIO= $this->contactFIO;      
      $phoneRecord->status = 1; /*помечаем телефон как надежный*/
      $phoneRecord->save();
      $phoneRef= $phoneRecord->id;
     
     /*сохраним контакт*/
     $contact = new ContactList();
     $contact->ref_phone = $phoneRef;
     $contact->ref_org = $this->id;
     $contact->ref_user = $curUser->id;    
     $contact->eventType = 1;    
     $contact->contactDate = date("Y.m.d h:i:s");                              
     $contact->contactFIO = $this->contactFIO;
     $contact->note = $this->note;
     $contact->save();           
 
     $coldRecord =  TblCold::findOne(['refOrg' => $this->id]);
     if (empty($coldRecord)) $coldRecord =  new TblCold();
     $coldRecord->refOrg = $this->id;
     $coldRecord->regularity = intval($this->regular);
     $coldRecord->mainWareGroup = $this->interes;
     $coldRecord->secondContactRef = $contact->id;
     $coldRecord->save();

   }
    
  public function loadData()    
  {
      
      $record = OrgList::findOne($this->id);
      if(empty($record)) return;
      $this->currentPhone =  $record->currentPhone;
      $this->contactPhone =  $record->contactPhone;
      $this->contactEmail =  $record->contactEmail;
      $this->orgTitle     =  $record->title;

      $this->contactFIO    = $record->contactFIO ;
      $this->contactEmail  = $record->contactEmail;
    
      $coldRecord =  TblCold::findOne(['refOrg' => $this->id]);
      if (empty($coldRecord)) return; 

      $this->regular = $coldRecord->regularity;
      $this->interes = $coldRecord->mainWareGroup;

      $contactRecord = ContactList::findOne($coldRecord->secondContactRef);      
      if (empty($contactRecord)) return; 
      $this->prevContactText = $contactRecord ->note;      
      $this->prevContactFIO  = $contactRecord ->contactFIO;
      $this->prevContactDate = date("d.m.Y h:i", strtotime( $contactRecord ->contactDate));            
      
      return;
  }
    
    
   public function getModuleText()
   {
       
      $record = OrgList::findOne($this->id);
      
      $qPhone =  Yii::$app->db->createCommand('SELECT phone from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->id])->queryScalar();       
    
      
      $modTitle = "<b>".$this->orgTitle."</b>";
      $modPhone = "<b><span id='cphone'>".$qPhone."</span></b>";
      $modEmail = "<b>".$this->contactEmail."</b>";
      $modFIO   = "<b>".$this->contactFIO."</b>";
      $modDate  = "<b>".$this->contactDate."</b>";
      
       $moduleRecord = TblColdTextModules::findOne([
      'number' => 2,
      ]);    
      
      $ret= preg_replace ("/\[ORGITLE\]/iu",$modTitle,$moduleRecord->moduleText);      
      $ret= preg_replace ("/\[FIO\]/iu",$modFIO,$ret);
      $ret= preg_replace ("/\[PHONE\]/iu",$modPhone,$ret);
      $ret= preg_replace ("/\[EMAIL\]/iu",$modEmail,$ret);
      $ret= preg_replace ("/\[CONTACTDATE\]/iu",$modDate,$ret);
      $ret= preg_replace ("/\n/iu","<br>",$ret);
       
    return $ret;
   }
    

  
 }
