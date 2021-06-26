<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;
use app\models\OrgList;
use app\models\PhoneList;
use app\models\ContactList;


use app\modules\cold\models\TblCold;
use app\modules\cold\models\TblColdTextModules;

//use app\models\MarketCalendarForm;

/**
 * ColdInitForm  - модель формы первого звонка менеджера холодных звонков
 */
 
class ColdInitForm extends Model 
{
    public $id = 0;
    public $orgTitle;
    
    public $name = "";
    public $email = "";
    public $currentPhone = "";    
    public $contactPhone = "";
    public $contactFIO   = "";
    public $firstContactPosition   = "";
    public $note = "";
    public $speak_res = 0;
    
    public $prevContactText = "";
    public $prevContactFIO = "";
    public $prevContactDate = "";        
    
    public function rules()
    {
        return [            
            [['currentPhone'], 'required'],
            [['name', 'email', 'currentPhone', 'contactFIO', 'firstContactPosition', 'contactPhone', 'note', 'speak_res'], 'default'],
            [['name', 'email', 'currentPhone', 'contactFIO', 'firstContactPosition', 'contactPhone', 'note'], 'trim'],
            ['id', 'integer'],
            ['name', 'string', 'length' => [1,150]],
            ['contactFIO', 'string', 'length' => [1,150]],
            ['firstContactPosition', 'string', 'length' => [1,150]],
            ['email', 'email'],
        ];
    }

   public function loadData()        
   {
      $record = OrgList::findOne($this->id);
      if(empty($record)) return;
      $this->currentPhone =  $record->currentPhone;
      $this->contactPhone =  $record->contactPhone;
      $this->contactFIO   =  $record->contactFIO;
      $this->email        =  $record->contactEmail;

      $this->orgTitle     =  $record->title;
      
      $coldRecord =  TblCold::findOne(['refOrg' => $this->id]);
      if (empty($coldRecord)) return; 

      $this->name                   =  $coldRecord->supplyManagerFIO;
      $this->firstContactPosition   =  $coldRecord->firstContactPosition;

      $contactRecord = ContactList::findOne($coldRecord->firstContactRef);      
      if (empty($contactRecord)) return; 
      $this->prevContactText = $contactRecord ->note;      
      $this->prevContactFIO = $contactRecord ->contactFIO;
      $this->prevContactDate = date("d.m.Y h:i", strtotime( $contactRecord ->contactDate));            
      
      
   }
   
   
   public function saveData()        
   {
      $record = OrgList::findOne($this->id);
      if(empty($record )){return false;}      
      

       $phoneRecord = PhoneList::findOne([
      'ref_org' => $this->id,
      'phone'   => $this->contactPhone,
      ]);      

      if(empty($phoneRecord )){
         $phoneRecord = new PhoneList ();
         $phoneRecord->ref_org = $this->id;
         $phoneRecord->phone   = $this->contactPhone;               
      }      
      
      $phoneRecord->phoneContactFIO= $this->name;      
      $phoneRecord->save();
      
      $phoneRef= $phoneRecord->id;

     
      $curUser=Yii::$app->user->identity;

      $record->contactDate     = date("Y-m-d");
      $record->currentPhone    = $this->currentPhone;
      $record->contactPhone    = $this->contactPhone;
      $record->contactEmail    = $this->email;
      $record->contactFIO      = $this->contactFIO;
      $record->isFirstContact  = 1; 
      $record->ref_user        = $curUser->id;          
      $record->isInWork        = 0;                    
      $record->nextContactDate = date("Y-m-d", time()+60*60*24*2); //В течении 2 дней          
      $record->save();      
      
 
 //      $record->isFirstContactFinished = 1;               
      /*сохраним контакт*/
      $contact = new ContactList();
      $contact->ref_phone   = $phoneRef;
      $contact->ref_org     = $this->id;
      $contact->ref_user    = $curUser->id;    
      $contact->eventType   = 0;    
      $contact->contactDate = date("Y-m-d H:i:s");    
      $contact->contactFIO  = $this->contactFIO;
      $contact->note        = $this->note;
      $contact->save();
      
      
      $coldRecord =  TblCold::findOne(['refOrg' => $this->id]);
      if (empty($coldRecord)) $coldRecord = new TblCold();

      $coldRecord->refOrg               = $this->id;
      $coldRecord->firstContactRef      = $contact->id;
      $coldRecord->firstContactPosition = $this->firstContactPosition;
      $coldRecord->firstContactFIO      = $this->contactFIO;
      $coldRecord->supplyManagerFIO     = $this->name;
      $coldRecord->save();
      
 
      return true;
   }
        
   public function getCompanyPhone($id)
   {
        $ret =  Yii::$app->db->createCommand('SELECT phone, status from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$id])->queryAll();       
        return $ret;
   }   
       
    public function getCurTime()
   {
     return time()+7*60*60;   
   }

/*
      <li> [ORGITLE] - будет заменено на название организации </li>
      <li> [FIO]     - будет заменено на ФИО контакта </li>
      <li> [PHONE]   - будет заменено на телефон контакта</li>
      <li> [EMAIL]   - будет заменено на электронную почту контакта </li>
      <li> [CONTACTDATE]   - будет заменено на дату последнего контакта </li>

*/
   public function getModuleText()
   {
      $record = OrgList::findOne($this->id);
      if (empty($record)) return;
      $qPhone =  Yii::$app->db->createCommand('SELECT phone from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->id])->queryScalar();       
    
      
      $modTitle = "<b>".$record->title."</b>";
      $modPhone = "<b><span id='cphone'>".$qPhone."</span></b>";
      $modEmail = "<b>".$record->contactEmail."</b>";
      $modFIO   = "<b>".$record->contactFIO."</b>";
      $modDate  = "<b>".$record->contactDate."</b>";
      
       $moduleRecord = TblColdTextModules::findOne([
      'number' => 1,
      ]);    
      
      $ret= preg_replace ("/\[ORGITLE\]/iu",$modTitle,$moduleRecord->moduleText);      
      $ret= preg_replace ("/\[FIO\]/iu",$modFIO,$ret);
      $ret= preg_replace ("/\[PHONE\]/iu",$modPhone,$ret);
      $ret= preg_replace ("/\[EMAIL\]/iu",$modEmail,$ret);
      $ret= preg_replace ("/\[CONTACTDATE\]/iu",$modDate,$ret);
      $ret= preg_replace ("/\n/iu","<br>",$ret);
       
    return $ret;
   }

 /*  public function sendProposal()
   {
   
        if ($this->email == "") {return;}
        $subject = "Коммерческое предложение"; 
        // текст сообщения, здесь вы можете вставлять таблицы, рисунки, заголовки, оформление цветом и т.п.
        $message ="Коммерческое предложение в аттаче."; 
        
        $user_email = "7648790@mail.ru";
        
        $uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
        
        $filename = "proposal.pdf";
        // название файла

        $filepath = $uploadPath.$filename ;
        // месторасположение файла

        //Письмо с вложением состоит из нескольких частей, которые разделяются разделителем

        $boundary = "--".md5(uniqid(time())); 
        // генерируем разделитель

        $mailheaders = "MIME-Version: 1.0;\r\n"; 
        $mailheaders .="Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"; 
        // разделитель указывается в заголовке в параметре boundary 

        $mailheaders .= "From: $user_email <$user_email>\r\n"; 
        $mailheaders .= "Reply-To: $user_email\r\n"; 

        $multipart = "--$boundary\r\n"; 
        $multipart .= "Content-Type: text/html; charset=utf8\r\n";
        $multipart .= "Content-Transfer-Encoding: base64\r\n";    
        $multipart .= "\r\n";
        //$multipart .= chunk_split(base64_encode(iconv("utf8", "windows-1251", $message)));
        $multipart .= chunk_split(base64_encode($message));
        // первая часть само сообщение
 
        // Закачиваем файл 
        $fp = fopen($filepath,"r"); 
        if (!$fp) 
        { 
            print "Не удается открыть файл22"; 
            exit(); 
        } 
        $file = fread($fp, filesize($filepath)); 
        fclose($fp); 
        // чтение файла


        $message_part = "\r\n--$boundary\r\n"; 
        $message_part .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";  
        $message_part .= "Content-Transfer-Encoding: base64\r\n"; 
        $message_part .= "Content-Disposition: attachment; filename=\"$filename\"\r\n"; 
        $message_part .= "\r\n";
        $message_part .= chunk_split(base64_encode($file));
        $message_part .= "\r\n--$boundary--\r\n";
        // второй частью прикрепляем файл, можно прикрепить два и более файла

        $multipart .= $message_part;

        mail($this->email,$subject,$multipart,$mailheaders);

   }
*/   
 }
