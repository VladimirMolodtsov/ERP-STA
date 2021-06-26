<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\models\OrgList;
use app\models\PhoneList;
use app\models\EmailList;
use app\models\SchetNeedList;
use app\models\ContactList;
use app\models\ZakazList;
use app\models\ModuleList;
use app\models\TblDocContactLnk;

/**
 * OrgContactForm- модель для работы с лидами и контактами
 */
class OrgContactForm extends Model
{
    //public $purchRef =0;
    public $contactEmail = "";
    public $contactPhone = "";
    public $contactFIO ="";
    public $contactOrgTitle="";
    public $orgTitle="";
    public $filtOrgTitle="";
    public $note= "";
    
    public $newAtsRef=0;
    public $atsRef = 0;
    public $status = 0;
    public $contactId = 0;
    public $orgId = 0;
    public $zakazId = 0;
    public $purchaseRef=0;
    public $action = "";
    public $noTask = 0;

    public $contactDate; 
    public $nextContactDate; 
    public $nextContactTime = "-"; 
    
    public $id = "";
    public $isSchetActive;
    public $zakazInfo;
    public $debug; 
    
    public $refDeal="";
    public $refSchet="";
    
    
    public $toDate;
    
    /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    
    public $moduleText;
    
    public $eventType=0;
    public $docList='';
    public $userFIO='';
    
    public function rules()
    {
        return [
            [['isSchetActive', 'eventType', 'userFIO', 'fltStatus'], 'safe'],
            
            [['orgId', 'orgTitle', 'contactId', 'contactFIO', 'contactEmail', 'contactPhone', 'note', 'status', 'nextContactDate', 'contactDate', 'contactOrgTitle', 'zakazId', 'nextContactTime', 'refDeal', 'refSchet', 'noTask','moduleText', 'purchaseRef',
            'recordId', 'dataType', 'dataVal','atsRef',
            ], 'default'],         

            
            [['contactFIO', 'contactEmail', 'contactPhone', 'note', 'orgTitle' ], 'trim'],
            ['contactFIO', 'string', 'length' => [1,150]],                        
            ['orgId', 'integer'],            
            ['contactEmail', 'email'],        
        ];
    }

    /*********************************
        Загрузим данные по лиду
    
    
    **********************************/
   public function loadTextModule()
   {
       $record= ModuleList::findOne([
       'id' => 3,
       ]);
       if (empty($record)) return;
       
      $this->moduleText = $record->moduleText;
   }    
   
   public function saveAjaxData()
   {
       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
    switch ($this->dataType)
    {
        case 'moduleText':
           $record= ModuleList::findOne(['id' => 3, ]);
        if (empty($record)) return $res;       
           $record->moduleText = $this->dataVal;
           $record->save(); 
           $res['val'] = $record->moduleText;
           break;
    }

    $res['res'] = true;    
    return $res;
   }    

   public function prepareDocList($contactId)
   {
   $query  = new Query();
   $query->select ([
            '{{%documents}}.id',
            'docIntNum',
            'docUri',
            'docOrigNum',
            'docOrigDate',
            'docTitle',
            'docClassifyRef',
            'orgTitle',
            '{{%doc_classify}}.docType'
            ])
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_classify}}","{{%doc_classify}}.id = {{%documents}}.docClassifyRef")
            ->leftJoin("{{%doc_contact_lnk}}","{{%documents}}.id = {{%doc_contact_lnk}}.refDoc");
            
    $query->andWhere(['=', '{{%doc_contact_lnk}}.refContact', $contactId]);    
    $list = $query->createCommand()->queryAll(); 
    
    $val = "<br><ul>";
     for ($i=0;$i< count($list);$i++)
     {

       if (empty($list[$i]['docClassifyRef'])) $v = $list[$i]['docTitle'];
                                          else $v = $list[$i]['docType'];
        $val .= "<li> <span class='clickable' ";
        $val .= " onclick='window.open(\"".$list[$i]['docUri']."\", "; 
        $val .= "\"docWin\",\"toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900\");'>";                                  
        $val .= $v." ".$list[$i]['docOrigNum'];   
        $val .= " ".$list[$i]['orgTitle']."</span> ";
        $val .= "<span onclick='removeDoc(".$list[$i]['id'].")' style='float:right;color:Brown;'  class='glyphicon glyphicon-remove-circle clickable'></span>";
        $val .= "</li>";
     }
     
     $val .= "</ul>";
    
   return $val;
   
   }
    public function addDocToLead($contactId, $docId)
    {
       $res = [ 'res' => false, 
             'contactId'  => $contactId, 
             'docId' => $docId, 
             'val' => '',
           ];   
    
       $record = new TblDocContactLnk();
       if (empty ($record)) return $res;
       $record ->refContact =  $contactId;
       $record ->refDoc =  $docId;
       $record ->save();
    $res['res'] = true;
    $res['val'] = $this->prepareDocList($contactId);       
    return $res;       
   }

    public function rmDocToLead($contactId, $docId)
    {
       $res = [ 'res' => false, 
             'contactId'  => $contactId, 
             'docId' => $docId, 
             'val' => '',
           ];   
    
       $record = TblDocContactLnk::findOne([
       'refContact' =>$contactId, 
       'refDoc'=> $docId, 
       ]);
       if (empty ($record)) return $res;
       $record ->delete();
    $res['res'] = true;
    $res['val'] = $this->prepareDocList($contactId);       
    return $res;       
   }
   
      
    public function loadLeadData($contactId)
    {
      if (empty($contactId) ) return;    
      $contact = ContactList::findOne($contactId);          
      if(empty($contact)) return;    
        
      $this->contactId = $contact->id;
      $this->note      = $contact->note;
      $this->contactFIO= $contact->contactFIO;
      $this->contactEmail= $contact->contactEmail;
      $this->zakazId   = $contact->refZakaz;
      $this->status    = $contact->eventType;
      $this->contactPhone= $contact->contactPhoneText;
      $this->atsRef     = $contact->refAts;
      
      if ($this->status <0 ) $this->status =0;
      if (!empty($this->zakazId))
      {
      $this->status = 12;
      $zakazInfoList = Yii::$app->db->createCommand(
            'SELECT {{%zakaz}}.id, formDate, schetNum, schetDate from {{%zakaz}} left join {{%schet}} on {{%zakaz}}.id={{%schet}}.refZakaz where {{%zakaz}}.id=:zakazId', 
            [':zakazId' => $this->zakazId,    ])->queryAll();
      if (count ($zakazInfoList) > 0){
          $this->zakazInfo = "заказ № ".$zakazInfoList[0]['id']." от ".$zakazInfoList[0]['formDate'];
          if (!empty($zakazInfoList[0]['schetNum'])) $this->zakazInfo .= "<br> счет №".$zakazInfoList[0]['schetNum']." от ".$zakazInfoList[0]['schetDate'];
        }
      }
      else $this->zakazId=0; //страхуемся от Null
      
      $this->orgId     = $contact->ref_org;          
      $this->getOrgInfo();
   
      if ($contact->ref_phone == -1) 
      {
          $this->contactPhone = '-';
      }
      
      else if(! empty( $contact->ref_phone) )
      { 
        $phoneRecord=PhoneList::find()->where(['id' => $contact->ref_phone])->one();            
        if (!empty($phoneRecord))    $this->contactPhone = $phoneRecord->phone;        
      }                
      
      if (!empty($contact->id))  
          $this->docList=$this->prepareDocList($contact->id);
    }
  /*

  */  
   public function getOrgInfoArray()    
   {
    $ret=[
    'orgRef' => 0,
    'orgTitle' => '',
    'orgINN' => '',
    'orgKPP' => '',
    'orgOKPO' => '',
    'orgOKTMO' => '',
    'orgOGRN' => '',
    
    'orgAdress' => '',
    
    'orgMainPhone' =>  '' ,
    'orgMainEmail' =>  ''  ,
    'orgPhones' => [],
    'orgEmails' => [],
    
    'orgContact' => '',
    'res' => false
    ];
   
    $record =  OrgList::findOne($this->orgId);  
    if (empty($record)) return $ret;
   
    $ret['orgRef'] = $record -> id;
    $ret['orgTitle'] = $record -> orgFullTitle;
    if (empty($ret['orgTitle']))$ret['orgTitle']= $record -> title;
    $ret['orgINN'] = $record -> orgINN;
    $ret['orgKPP'] = $record -> orgKPP;
    $ret['orgOKPO'] = $record -> orgOKPO;
    $ret['orgOKTMO'] = $record -> orgOKTMO;
    $ret['orgOGRN'] = $record -> orgOGRN;
    $ret['orgContact'] = $record -> contactFIO;

    $ret['orgMainPhone'] = $record -> contactPhone;
    $ret['orgMainEmail'] = $record -> contactEmail;

    
    $strSql =" SELECT phone FROM {{%phones}} where status != 2 AND  ref_org =:refOrg order by status DESC";
    $orgPhones = Yii::$app->db->createCommand($strSql,[':refOrg' => $this->orgId])->queryColumn();                    
    $ret['orgPhones'] ="";   
    
    $ret['orgPhones'] .="<ul>";
         if (!empty($ret['orgMainPhone']))
    $ret['orgPhones'] .="<li><b><span class='clickable' onclick='doLeadCall(".$ret['orgMainPhone'].")' >".$ret['orgMainPhone']."</span></b></li>";
         for ($i=0;$i<count($orgPhones);$i++)
         {
             $orgPhones[$i] = preg_replace("/[\D]/","",$orgPhones[$i]);//все пробел нафиг
             if(mb_strlen($orgPhones[$i],'utf-8') != 11) continue;
             if (mb_substr($orgPhones[$i],0, 1, 'utf-8') != '7') continue;
             if ($orgPhones[$i] == $ret['orgMainPhone']) continue;             
    $ret['orgPhones'] .="<li><span class='clickable' onclick='doLeadCall(".$orgPhones[$i].")' >".$orgPhones[$i]."</span></li>";
             if ($i >4) break;
         }
    $ret['orgPhones'] .="</ul>";
    
    $strSql =" SELECT email FROM {{%emaillist}} where status != 2 AND  ref_org =:refOrg  order by status DESC";
    $orgEmails = Yii::$app->db->createCommand($strSql,[':refOrg' => $this->orgId])->queryColumn();                    
    $ret['orgEmails'] ="";   
    $ret['orgEmails'] .="<ul>";
         if (!empty($ret['orgMainEmail']))
    $ret['orgEmails'] .="<li><b>".$ret['orgMainEmail']."</b></li>";
         for ($i=0;$i<count($orgEmails);$i++)
         {
             if ($orgEmails[$i] == $ret['orgMainEmail']) continue;
    $ret['orgEmails'] .="<li>".$orgEmails[$i]."</li>";
             if ($i >4) break;
         }
    $ret['orgEmails'] .=" </ul>";
   
    $ret['res'] = true;
    return $ret;
   }
    
    /**********************************
     Форма new-lead / в т.ч. обработка через аякс
     Сохраняем необработанный контакт как лид
     и пытаемся заполнить поля
    *************************************/
    /*    
Добрый день!
Прошу выставить счет или КП на: 
бумага ингибированная УНИ 22-80 ГОСТ 16295-93 в рулонах — 100 кг.
Заранее спасибо!
 
С уважением
менеджер
Ника
8 (3843) 33-10-11
8 952 171 01 27
 n3993@mail.ru
ООО «СКК)

654010, Россия, Кемеровская область, г. Новокузнецк, ул.Музейная 9, каб.24
Тел.: 8 (952) 171 01 27
ИНН 4217098951, КПП 421701001,
ОГРН 1074217009668,   ОКПО 82739516
р/с 40702810206000001556 , Банк ООО КБ «Кольцо Урала» 
БИК: 046577768,  
корр/счет 30101810500000000768  в Уральское ГУ Банка России  
*/
    public function processLead()
    {
     
     $curUser=Yii::$app->user->identity;

     $debug ="";
     // Если контакта eсть то найдем.
     if ($this->contactId != 0)
     {
         $contact = ContactList::findOne($this->contactId);      
        if(empty($contact))$this->contactId = 0;
     }     
     // Если контакта нет то создадим.
     if ($this->contactId == 0)
     {
        $contact = new ContactList(); 
        $contact->ref_user = $curUser->id;
        $contact->contactDate = date("Y.m.d H:i");      
        $contact->lastChngDate= date("Y.m.d H:i");      
        $contact->note = $this->note;
              
        $contact->contactFIO = $this->contactFIO;
        $contact->eventType =10; //лид в обработке
        
        $contact->save();                 
        $this->contactId = $contact->id;
     }
           
    //развалим все по пробелам / foreach не наш выбор
    $parse = preg_split ("/\s+/", $this->note);
    // Если телефона нет, то ищем в комменте
    if (empty($this->contactPhone))    
     {
        //Считаем телефоном любую строку длинны > 10  начинающуюся с 7        
        for ($i=0; $i< count($parse);$i++ )
        {  
          if (preg_match("/^[\+7|7|+8|8]/",$parse[$i])==0)   continue;          
          //уберем все не нужное
          $parse[$i] = preg_replace("/[\D]/u","",$parse[$i]);
          $len =mb_strlen($parse[$i],'utf-8');                          
          if ($len < 10) continue;
          $this->contactPhone = $parse[$i];        
          break;    
        }
      }
  
   
  // Если почты нет, то ищем в комменте
     if (empty($this->contactEmail))    
     {        
        for ($i=0; $i< count($parse);$i++ )
        {  
          $len =mb_strlen($parse[$i],'utf-8');
          if ($len < 6) continue;      
          if (preg_match("/\@/",$parse[$i])==0) continue;
          $this->contactEmail = $parse[$i];          
          break;    
        }
      }
      
       
    //Обработка телефона
    $phoneCount =0;
      if (!empty($this->contactPhone) && $this->contactPhone != '-')        
      {                
         $phoneCount=PhoneList::find()->where(['phone' => $this->contactPhone])->count();
        //Новый телефон                 
       if ($phoneCount==0) {
           //Организация известна - добавим в список телефонов
          if ($this->orgId > 0)    
          {
            $phoneRecord= new PhoneList();
            $phoneRecord ->phone = $this->contactPhone;
            $phoneRecord ->ref_org = $this->orgId;
            $phoneRecord ->save();
            $phoneCount = 1;
          }
       }
      if ($phoneCount >0){
        $phoneRecord=PhoneList::find()->where(['phone' => $this->contactPhone])->one();            
        $contact->ref_phone = $phoneRecord ->id;                
      }
   
      } // телефон

      if ($this->orgId > 0 )
      {
        $contact->ref_org = $this->orgId;          
        $this->getOrgInfo();
      }

      if  ($this->contactOrgTitle == 'Реклама/Прочее')  $contact->ref_org = -1;
      if  ($this->contactPhone == '-')     $contact->ref_phone = -1;      
        
        if (empty($this->orgId)){
           //если Организация неизвестна определим по телефону
           if (!empty($phoneRecord)){
             $this->orgId =    $phoneRecord ->ref_org;      
             $this->getOrgInfo();}           
        }                
        
        if (empty($this->orgId)){
            if (!empty($this->contactEmail)){
            $emailRecord=EmailList::find()->where(['email' => $this->contactEmail])->one();            
           //если Организация неизвестна определим по почте
           if (!empty($emailRecord) ) { $this->orgId =    $emailRecord ->ref_org;      
           $this->getOrgInfo();}
           }
        }                


      if ($this->orgId > 0 )
      {
        $contact->ref_org = $this->orgId;          
      }
      
      $contact->lastChngDate= date("Y.m.d H:i");      
      $contact->save();   
      $this->setLeadStatus($contact->id);
      
      
      $sendArray = 
      [     
        'contactId' => $this->contactId,
        'note'  => $this->note,
        'contactPhone' => $this->contactPhone,
        'contactOrgTitle' => $this->contactOrgTitle,
        'contactFIO' => $this->contactFIO,
        'contactEmail' => $this->contactEmail,                
        'orgId' => $this->orgId, 
        'debug' => $debug,       
      ];

      return $sendArray;
         
     
        
     //Инициализируем массив    и будем возвращать его в форму
        
    }
    
    /******************************
        Сохраним как есть - без обработки
    *******************************/
    public function saveLead()
    {
     
     $curUser=Yii::$app->user->identity;
     // Если контакт eсть то найдем.
     if ($this->contactId != 0)
     {
         $contact = ContactList::findOne($this->contactId);      
        if(empty($contact))$this->contactId = 0;
     }     
     // Если контакта нет то создадим.
     if ($this->contactId == 0)
     {
        $contact = new ContactList(); 
        $contact->save();                 
     }
        $contact->ref_user = $curUser->id;
        if(empty($contact->contactDate)) $contact->contactDate= date("Y.m.d H:i");    
        $contact->lastChngDate= date("Y.m.d H:i");      
        $contact->note = $this->note;
        $contact->contactFIO = $this->contactFIO;
        $contact->contactPhoneText = mb_substr($this->contactPhone,0,20,'utf-8');
        $contact->contactEmail = $this->contactEmail;
        $contact->refAts = $this->atsRef;
      if (!empty($this->zakazId)) $contact->refZakaz=$this->zakazId;          
                            else  $contact->refZakaz=0; 
      
      if ($this->orgId != 0 ) 
      {
          if ($this->orgId == -2)
          {
                $orgRecord   = new OrgList();                      
                $orgRecord -> title = trim($this->orgTitle);
                $orgRecord -> isOrgActive = 1;
                $orgRecord -> orgNote = 'Обработка лидов '.date("d.m.Y");
                $orgRecord -> source = 'lead proccessing';
//                $orgRecord ->isFirstContact = 1;
                $orgRecord ->contactPhone = $this->contactPhone;
                $orgRecord ->contactEmail = $this->contactEmail;
                $orgRecord ->contactFIO = $this->contactFIO;
                $orgRecord ->save();
                if (empty($orgRecord -> title) || $orgRecord -> title == "Создать автоматически") 
                {
                    $orgRecord -> title = "Организация ID=".$orgRecord ->id;
                    $orgRecord ->isFirstContactFinished = -1;
                    $orgRecord ->save();
                }    
                
                $this->orgId = $orgRecord ->id;
          }          
          
          $contact->ref_org = $this->orgId;          
          $this->getOrgInfo();
          
       if (!empty($this->contactPhone) && $this->contactPhone != '-' )        
      {          
           $phoneRecord=PhoneList::find()->where(['phone' => $this->contactPhone, 'ref_org' =>  $this->orgId])->one();            
           if(empty($phoneRecord)) {
               $phoneRecord =new PhoneList();
               $phoneRecord->phone = $this->contactPhone;
               $phoneRecord->ref_org = $this->orgId;          
               $phoneRecord->save();
           }    
           $contact->ref_phone = $phoneRecord ->id;          
       }     
 
          
      }



/*VV OBSOLETED ? */
      if  ($this->contactOrgTitle == 'Реклама/Прочее')  $contact->ref_org = -1;
      if  ($this->contactPhone == '-')     $contact->ref_phone = -1;      
            

        //берем назначенные статусы из формы лида - обрабатываем метки игнорировать и отложить       
        if (empty ($this->status)) $this->status = 10;                
        $contact->eventType = $this->status ;         
        $contact->save();                
        //теперь уточним статус и выставим дату просрочки
        $this->status = $this->setLeadStatus   ($contact->id);
        
        return $contact->id;
     }        

/*
  10 - новый
  11 - отложить
  
  12 - пойдет в сделку
  //13 - резервный код
  14 - квалификация товара начата  
  15 - в заявку / квалификация товара закончена заявки нет 

  16- начата обработка, контрагент не определен.
  17- контрагент определен, нужно определение товара 
  
  
  20 - работа завершена - привязана заявка 
  21 - игнорировать
*/     
/*
ALTER TABLE `rik_contact` ADD COLUMN `overDueDate` DATETIME DEFAULT DATE_ADD('contactDate',Interval 90 DAY);
*/     

    public function createCalendarEvent ()
    {
        switch ($this->status) 
           {
                case 11:            
                 /*Добавим запись в календарь*/
                $calendar = new MarketCalendarForm();
                $event_ref = 8;
                $eventNote = "Произвольный контакт";
                $calendar->createEvent(date("Y-m-d", strtotime($this->nextContactDate)),$event_ref , $this->orgId, 0, $contact->id, $eventNote);      
                break;
                
                case 12:
                // Лид в заказ
                if (!empty($this->zakazId))
                {
                  $contact->eventType = $this->status ; //лид в заказ                    
                  $calendar = new MarketCalendarForm();                    
                  
                  $zakazInfoList = Yii::$app->db->createCommand(
                    'SELECT {{%zakaz}}.id, formDate, schetNum, schetDate, isActive from {{%zakaz}} left join {{%schet}} on {{%zakaz}}.id={{%schet}}.refZakaz where {{%zakaz}}.id=:zakazId', 
                    [':zakazId' => $this->zakazId,    ])->queryAll();
                    $event_ref = 2;
                    if (count ($zakazInfoList) == 0) 
                    {
                        $contact->save();        
                        return $contact->id;

                    }
                    $event_ref = 3;
                    if (!empty($zakazInfoList[0]['schetNum'])) $event_ref = 6;
                    /*Добавим запись в календарь*/
                    if($zakazInfoList[0]['isActive'] == 1)
                    {
                        $eventNote = "Обработать заявку";                                            
                        $calendar->createEvent(date("Y-m-d", strtotime($this->nextContactDate)),$event_ref , $this->orgId, $this->zakazId, $contact->id, $eventNote);      
                    }
                //Предыдущее событие выполнено       
                $calendar->markRefEvent( $this->orgId, 0);       
                }
                break;
           }
     }

    public function resetLeadStatus ()
    {
        $strSql = "SELECT id FROM {{%contact}} where eventType >=10 AND  eventType < 25";
        $list = Yii::$app->db->createCommand($strSql)->queryAll();
        $N=count($list);
        for ($i=0; $i<$N; $i++ )
        {
            $this->setLeadStatus($list[$i]['id']);
        }
    }    
    public function setLeadStatus   ($contactId)
    {
       $contactId = intval($contactId);
       $leadRecord = ContactList::findOne($contactId);     
       if (empty($leadRecord))           return;
       if ($leadRecord->eventType < 10 || $leadRecord->eventType > 25)  return; // не лид
        
       if (!empty($leadRecord->refZakaz)){
        $leadRecord->eventType = 20;  //ушло в сделку
        $leadRecord->save();          
        return $leadRecord->eventType;               
       }

       
       if ($leadRecord->eventType == 11) {
            $addDate=$this->getCfgValue(2105);            
            $leadRecord->overDueDate = date('Y-m-d H:i:s',strtotime($leadRecord->contactDate)+$addDate*24*3600);
            $leadRecord->save();                  
            return $leadRecord->eventType; // отложенные
            }
       if ($leadRecord->eventType == 21)  return $leadRecord->eventType; // игнорируемые
       if ($leadRecord->eventType == 20)  return $leadRecord->eventType; // законченные

            
       //Контрагент не определен
       if (empty($leadRecord->ref_org))
       {
         
         if (empty($leadRecord->contactPhoneText) && empty($leadRecord->contactEmail)) {
            $leadRecord->eventType = 10;  //Ничего не заполнено - новый
            $addDate=$this->getCfgValue(2100);            
            $leadRecord->overDueDate = date('Y-m-d H:i:s',strtotime($leadRecord->contactDate)+$addDate*24*3600);            
            $leadRecord->save();
            return $leadRecord->eventType;
          }
          
        $leadRecord->eventType = 16;  //начата обработка, контрагент не определен
        $addDate=$this->getCfgValue(2101);            
        $leadRecord->overDueDate = date('Y-m-d H:i:s',strtotime($leadRecord->contactDate)+$addDate*24*3600);            
        $leadRecord->save();          
        return $leadRecord->eventType;          
       }
     
       $detailRecord = TblLeadDetail::findOne(['refContact' => $contactId]);
       if (empty ($detailRecord)){       
        $leadRecord->eventType = 17;  //контрагент определен, нужно определение товара
        $addDate=$this->getCfgValue(2102);            
        $leadRecord->overDueDate = date('Y-m-d H:i:s',strtotime($leadRecord->contactDate)+$addDate*24*3600);                   
        $leadRecord->save();          
        return $leadRecord->eventType;                 
       }
       
       
       $status=15;       
        /*проверяем на полноту заполнения товара*/
        if(empty($detailRecord->leadWareName)) $status=14;
        if(empty($detailRecord->leadWareDetail)) $status=14;
        if(empty($detailRecord->leadWareSize)) $status=14;
        if(empty($detailRecord->leadWareCount)) $status=14;
        if(empty($detailRecord->leadTargetCity)) $status=14;

       if ($status ==14)  $addDate=$this->getCfgValue(2103);            
       if ($status ==15)  $addDate=$this->getCfgValue(2104);            
        $leadRecord->overDueDate = date('Y-m-d H:i:s',strtotime($leadRecord->contactDate)+$addDate*24*3600);                    
        $leadRecord->eventType = $status;  //контрагент определен, нужно определение товара
        $leadRecord->save();         
        
        return $leadRecord->eventType;                 
    }      
        
    ########################################
    # Сохраним обработку управленческого лида
    # 
    ########################################     
    public function saveHeadLead   ()
    {
     $curUser=Yii::$app->user->identity;
     // Если контакт eсть то найдем.
     if ($this->contactId != 0)
     {
         $contact = ContactList::findOne($this->contactId);      
        if(empty($contact)) return false;
     }     
        $contact->note = $this->note;
        $contact->save();                 
    }
    ########################################
    # Выставим статус контакта
    # 
    ########################################
    public function markContact ($contactId, $status)
    {
         $contact = ContactList::findOne($contactId);      
        if(empty($contact)) return;
        echo $status;
        $contact->eventType = $status;
        $contact->save();
    }

    ########################################
    # Получим название организации
    # 
    ########################################

   public function getOrgInfo()
   {        
    
    if  ($this->orgId == -1) 
    {
            $this->contactOrgTitle ='Реклама/Прочее';
            $this->contactPhone = '-';
            if (empty($this->contactFIO))  $this->contactFIO = '-';
    }
        
            $orgTiltles = Yii::$app->db->createCommand(
            'SELECT title, contactFIO,contactPhone, contactEmail from {{%orglist}} where id=:ref_org  LIMIT 1', 
            [
            ':ref_org' => $this->orgId,
            ])->queryAll();

            if (count($orgTiltles) == 0) return "";    
            $this->contactOrgTitle = $orgTiltles[0]['title'];
            if (empty($this->contactFIO))  $this->contactFIO = $orgTiltles[0]['contactFIO'];
            if (empty($this->contactPhone))$this->contactPhone = $orgTiltles[0]['contactPhone'];
            if (empty($this->contactEmail))$this->contactEmail = $orgTiltles[0]['contactEmail'];
   }    

    ########################################
    # Создадим лид для заказа
    # 
    ########################################
   
   public function createLeadFromZakaz($zakazId)
   {
     $zakazRecord = ZakazList::findOne($zakazId);
     if (empty($zakazRecord)) return 0;
     $curUser=Yii::$app->user->identity;
     
     $orgRecord = OrgList::findOne($zakazRecord->refOrg );
         
     $contact = new ContactList(); 
      $contact->ref_user = $curUser->id;
      $contact->contactDate= date("Y.m.d H:i");    
      $contact->lastChngDate= date("Y.m.d H:i");      
      $contact->refZakaz = $zakazRecord->id;
      $contact->eventType = 20;
     if (!empty($orgRecord)){      
      $contact->contactFIO = $orgRecord->contactFIO;
      $contact->contactPhoneText = $orgRecord->contactPhone;
      $contact->contactEmail = $orgRecord->contactEmail;
      $contact->ref_org = $orgRecord->id;
     }
     
    $contact->save();                 
   
   
   }
    ########################################
    # Создадим пустой заказ
    # 
    ########################################
   public function createNewZakaz()
   {    

    $this->orgId = intval($this->dataVal);
    $curUser=Yii::$app->user->identity;

     $zakazRecord = new ZakazList();
        $zakazRecord->ref_user  = $curUser->id;
        $zakazRecord->formDate  = date ("Y-m-d",time());
        $zakazRecord->refOrg    = $this->orgId;    
        $zakazRecord->isActive=1;
     $zakazRecord ->save();
      $sendArray = 
      [     
        'zakazId' => $zakazRecord->id,
        'formDate'  => $zakazRecord->formDate,
        'contact'  => $this->recordId,
      ];
     
     $contact = ContactList::findOne(intval($this->recordId)); 
     if(empty($contact)) return $sendArray;
        $contact->refZakaz = $zakazRecord->id;
        $contact->eventType = 20;
     $contact->save();   
     
      $sendArray = 
      [     
        'zakazId' => $zakazRecord->id,
        'formDate'  => $zakazRecord->formDate,
        'contact'  => $contact->id,
      ];
     
     return $sendArray;
   }
  
    ########################################
    # Загрузим существующий контакт
    # 
    ########################################
   
   
public function loadContact($contactId)
   {
      $contactRecord = ContactList::findOne($contactId);      
      if (empty($contactRecord)) return;
            
      $this->contactId = $contactRecord->id;      
      $this->orgId     = $contactRecord->ref_org;
      $this->zakazId   = $contactRecord->refZakaz;
      $this->purchaseRef   = $contactRecord->refPurchase;
      $this->note      = $contactRecord->note;
      $this->status    = $contactRecord->contactStatus;
      $this->contactFIO   = $contactRecord->contactFIO;
      $this->contactEmail = $contactRecord->contactEmail;
      
      $phoneRecord = PhoneList::findOne($contactRecord->ref_phone);      
      if (!empty($phoneRecord))  $this->contactPhone = $phoneRecord->phone;
      else $this->contactPhone =$contactRecord->contactPhoneText; 
     
   }
    ########################################
    # Регистрация контакта на известную организацию
    # 
    ########################################
    public function regContact()
    {
      $orgRecord   = OrgList::findOne($this->orgId);      
      $curUser=Yii::$app->user->identity;


      if (empty($this->contactPhone)) $phoneRef =0;
      else    
      {
      $phoneCount = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%phones}} where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone' => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ])->queryScalar();

            
      if ($phoneCount > 0)
      {          
            Yii::$app->db->createCommand(
            'UPDATE {{%phones}} SET phoneContactFIO=:phoneContactFIO  where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone' => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ':phoneContactFIO' => $this->contactFIO,
            ])->execute();
      }
            
      if ($phoneCount == 0)
      {          
            Yii::$app->db->createCommand(
            'INSERT INTO {{%phones}}  (ref_org, phone, phoneContactFIO) VALUES ( :ref_org ,:phone, :phoneContactFIO )', 
            [
            ':phone' => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ':phoneContactFIO' => $this->contactFIO,
            ])->execute();

      }
      
      
      
     $phoneRef= Yii::$app->db->createCommand(
            'SELECT MAX(id) from {{%phones}} where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone' => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ])->queryScalar();
     }
    
      $orgRecord ->contactDate = date("Y-m-d H:i");      
      $orgRecord ->nextContactDate =  date("Y-m-d", strtotime($this->nextContactDate));
      $orgRecord ->isInWork = 0;          

      if (!empty($this->contactPhone))$orgRecord ->contactPhone = $this->contactPhone;
      if (!empty($this->contactEmail))$orgRecord ->contactEmail = $this->contactEmail;      
      if (!empty($this->contactFIO) && !(empty($phoneRef)) )$orgRecord ->contactFIO = $this->contactFIO;      
      $orgRecord ->save();

      $contact = ContactList::findOne($this->contactId);      
      if (empty($contact) ) $contact = new ContactList();      

      if($this->status == 2)
      {
          /*Контакт не состоялся*/  
//          return;
        $contact->contactStatus = 2;
      }      

      $contact->ref_phone = $phoneRef;
      $contact->ref_org = $this->orgId;
      $contact->ref_user = $curUser->id;
      if(empty($contact->contactDate)) $contact->contactDate= date("Y.m.d H:i");    
     $contact->lastChngDate= date("Y.m.d H:i");      

      $contact->contactFIO = $this->contactFIO;
      $contact->note = $this->note;
      $contact->refZakaz = $this->zakazId;
      $contact->refPurchase = $this->purchaseRef;
      $contact->refAts = $this->atsRef;
      $contact->save();


      if ($this->noTask == 0)
      {
      
      /*Добавим запись в календарь*/
       $calendar = new MarketCalendarForm();
       $event_ref = 8;
       $eventNote = "Произвольный контакт";
       $calendar->createEventTime($this->nextContactDate, $this->nextContactTime, $event_ref , $orgRecord->id, 
       0, $contact->id, $eventNote, $contact->id);      
      }
        
   }

   
 
/*********************************************************/
  public function getLeadListProvider($params)
   {

    $query  = new Query();
    $query->select ("{{%contact}}.id, {{%contact}}.contactDate, {{%contact}}.ref_org, note, title as contactOrgTitle, userFIO")
           ->from("{{%contact}}")
           ->leftJoin("{{%orglist}}","{{%contact}}.ref_org = {{%orglist}}.id")           
           ->leftJoin("{{%user}}","{{%contact}}.ref_user = {{%user}}.id");

    $countquery  = new Query();
    $countquery->select ("count({{%contact}}.id)")
           ->from("{{%contact}}")
           ->leftJoin("{{%orglist}}","{{%contact}}.ref_org = {{%orglist}}.id")           
           ->leftJoin("{{%user}}","{{%contact}}.ref_user = {{%user}}.id");

     $query->andWhere(['=', 'eventType',10]);
     $countquery->andWhere(['=', 'eventType', 10]);     
           

           
    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'title', $this->contactOrgTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->contactOrgTitle]);     
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'contactDate',  
                'note', 
                'contactOrgTitle', 
                'userFIO'
            ],
            'defaultOrder' => [ 'id' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

   
public $leadLeafStatus;
public $leadDuration=10000;  
public $fltStatus = 0;        
public $fltOverdue=0;
public $fltToday=0;
public $fltTomorrow=0;

public function getManagerLeadList()
{

   $listStatus = Yii::$app->db->createCommand('Select DISTINCT {{%user}}.id, userFIO from {{%user}},{{%contact}}
   WHERE {{%user}}.id = {{%contact}}.ref_user and  {{%contact}}.eventType >=10 AND  {{%contact}}.eventType < 100')                    
                    ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'userFIO');      
}   


/**************************/     
     
   public function getCfgValue($key)          
   {
      $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
               ':key' => intval($key),               
               ])->queryOne();  
               
     return $record['keyValue'];
   }


public function getMonthLeadList($year){
    
     $year = intval($year);    

    $n = 12;
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'MONTH(contactDate) as m',           
        ])
         ->from("{{%contact}}")
         ->distinct()
         ->groupBy(['MONTH(contactDate)']);
    $query->andWhere ('YEAR(contactDate) = '.$year);
     $query->andWhere(['>=', 'eventType',10]);     
     $query->andWhere(['<', 'eventType',100]);
         
    ;

    $list = $query->createCommand()->queryAll();    
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $m=$list[$i]['m'];
       $res[$m]['all']=$list[$i]['N'] ; 
    }
    
 /*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  
   
public function getDayLeadList($month, $year){
    
     $year = intval($year);
    $month = intval($month);

    $n = date('t',strtotime($year."-".$month."-01"));
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(contactDate) as d',           
        ])
         ->from("{{%contact}}")
         ->distinct()
         ->groupBy(['DATE(contactDate)']);
    $query->andWhere ('YEAR(contactDate) = '.$year);
    $query->andWhere ('MONTH(contactDate) = '.$month);

     $query->andWhere(['>=', 'eventType',10]);     
     $query->andWhere(['<', 'eventType',100]);
         
    ;

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['all']=$list[$i]['N'] ; 
    }
    
 /*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  
/*********************************************************/
  public function getHeadLeadListProvider($params)
   {

   /*актуальность лида*/
   $this->leadDuration = $this->getCfgValue(2105);    

     $timeCond="DATEDIFF(NOW(),{{%contact}}.contactDate) < 10000";    
     $timeActual="DATEDIFF(NOW(),{{%contact}}.contactDate) < ".$this->leadDuration;    

   
    $query  = new Query();
    $query->select ([
                '{{%contact}}.id', 
                '{{%contact}}.contactDate', 
                '{{%contact}}.ref_org', 
                '{{%contact}}.contactFIO',
                '{{%contact}}.contactPhoneText',
                '{{%contact}}.contactEmail',
                'lastChngDate',
                'overDueDate',
                '{{%contact}}.note', 
                '{{%contact}}.refZakaz',
                'eventType',
                'phone',                
                'title as contactOrgTitle', 
                'userFIO',
                '{{%lead_detail}}.id as refDetail'
                ])
           ->from("{{%contact}}")
           ->leftJoin("{{%orglist}}","{{%contact}}.ref_org = {{%orglist}}.id")           
           ->leftJoin("{{%phones}}","{{%phones}}.id = {{%contact}}.ref_phone")
           ->leftJoin("{{%lead_detail}}","{{%contact}}.id = {{%lead_detail}}.refContact")           
           ->leftJoin("{{%user}}","{{%contact}}.ref_user = {{%user}}.id")
           ->distinct();

    $countquery  = new Query();
    $countquery->select ("count({{%contact}}.id)")
           ->from("{{%contact}}")
           ->leftJoin("{{%orglist}}","{{%contact}}.ref_org = {{%orglist}}.id")           
           ->leftJoin("{{%lead_detail}}","{{%contact}}.id = {{%lead_detail}}.refContact")           
           ->leftJoin("{{%user}}","{{%contact}}.ref_user = {{%user}}.id");

     $query->andWhere(['>=', 'eventType',10]);
     $countquery->andWhere(['>=', 'eventType', 10]);     

     $query->andWhere(['<', 'eventType',100]);
     $countquery->andWhere(['<', 'eventType', 100]);     
     

     $query->andWhere($timeCond);
     $countquery->andWhere($timeCond);
          
    if (($this->load($params) && $this->validate())) {
    
     $query->andFilterWhere(['like', 'title', $this->contactOrgTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->contactOrgTitle]);    

     $query->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);
     $countquery->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);    

     
          $query->andFilterWhere(['=', '{{%contact}}.ref_user', $this->userFIO]);
     $countquery->andFilterWhere(['=', '{{%contact}}.ref_user', $this->userFIO]);    

  
     }

   /*if(!empty($this->toDate))
   {
       
          $query->andFilterWhere(['=', '{{%contact}}.contactDate', date("Y-m-d",strtotime($this->toDate))]);
     $countquery->andFilterWhere(['=', '{{%contact}}.contactDate', date("Y-m-d",strtotime($this->toDate))]);
       
   }*/
/*     public $fltOverdue=0;
public $fltToday=0;
public $fltTomorrow=0;
*/
     
     /*Просрочено*/
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType >= 10 AND  eventType < 20";
     $strSql .= " AND overDueDate < NOW()";
     $this->leadLeafStatus['overdue'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     
     /*Сегодня*/
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType >= 10 AND  eventType < 20";
     $strSql .= " AND DATEDIFF({{%contact}}.overDueDate, NOW()) in (0,1) ";
     $this->leadLeafStatus['today'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     /*Завтра*/
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType >= 10 AND  eventType < 20 AND  eventType != 11 ";
     $strSql .= " AND DATEDIFF({{%contact}}.overDueDate, NOW()) > 1";
     $this->leadLeafStatus['tomorrow'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     $addTimeCond ="";
    /* if($this->fltStatus > 0){
      $addTimeCond="AND DATEDIFF(NOW(),{{%contact}}.contactDate) < ".$this->leadDuration." ";
    }*/

     if ($this->fltOverdue == 0)
     {     
          $query->andWhere("NOT (overDueDate < NOW())");
     $countquery->andWhere("NOT (overDueDate < NOW())");

     $addTimeCond.=" AND (NOT (overDueDate < NOW()) )";
     }
     
     if ($this->fltToday == 0)
     {     
          $query->andWhere("NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) in (0,1) )");
     $countquery->andWhere("NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) in (0,1) )");

     $addTimeCond.=" AND (NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) in (0,1)  ))";
     }
     
     if ($this->fltTomorrow == 0)
     {     
          $query->andWhere("NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) >1 )");
     $countquery->andWhere("NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) >1 )");

     $addTimeCond.=" AND (NOT (DATEDIFF({{%contact}}.overDueDate, NOW()) >1  ))";
     }
     
     
/*
  10 - новый
  11 - отложить
  
  12 - пойдет в сделку
  //13 - резервный код
  14 - квалификация товара начата  
  15 - в заявку / квалификация товара закончена заявки нет 

  16- начата обработка, контрагент не определен.
  17- контрагент определен, нужно определение товара 
  
  
  20 - работа завершена - привязана заявка 
  21 - игнорировать
*/     
     
     
     
     switch ($this->fltStatus)
     {
     
     /*Новые*/
      case 15:          
          $query->andWhere("eventType >= 10");
     $countquery->andWhere("eventType >= 10");          
          $query->andWhere("eventType < 20");
     $countquery->andWhere("eventType < 20");          
     $query->andWhere($timeActual);
     $countquery->andWhere($timeActual);


      break;

     
     /*Новые*/
      case 1:          
          $query->andWhere("eventType = 10");
     $countquery->andWhere("eventType = 10");          
          if ($this->fltOverdue == 0) { $add = "DATEDIFF(NOW(),{{%contact}}.contactDate) > ".$this->getCfgValue(2105);    }      
      break;

      
     /*Контакты*/
      case 2:          
          $query->andWhere("eventType = 16");
      $countquery->andWhere("eventType = 16");
      break;

      /*Клиенты*/
      case 6:          
          $query->andWhere("eventType = 17");
      $countquery->andWhere("eventType = 17");
      break;
      
       /*Товар*/
      case 5:          
          $query->andWhere("eventType = 14 ");
      $countquery->andWhere("eventType = 14 ");
      break;
      
      /*Создать сделку*/
      case 11:          
           $query->andWhere(" eventType = 15 ");
      $countquery->andWhere(" eventType = 15 ");      
      break;
      /*В сделке*/
      case 8:          
           $query->andWhere(" eventType = 20 ");
      $countquery->andWhere(" eventType = 20 ");
      break;

/*
           $query->andWhere(" eventType > 10 and eventType < 21  and ifnull(refZakaz,0) > 0");
      $countquery->andWhere(" eventType > 10 and eventType < 21  and ifnull(refZakaz,0) > 0");

*/      
      
      case 3:          
          $query->andWhere("eventType = 10 and {{%contact}}.ref_org > 0");
      $countquery->andWhere("eventType = 10 and {{%contact}}.ref_org > 0");
      break;
      
      /*Рассмотренно */
      case 4:          
          $query->andWhere("eventType >= 20 and eventType < 25 ");
      $countquery->andWhere("eventType >= 20 and eventType < 25 ");
      break;
            
       
      /*В работе*/
      case 7:          
           $query->andWhere("eventType >= 10 AND eventType < 20 ");
      $countquery->andWhere("eventType >= 10 AND eventType < 20");
      break;
      
      /*Игнорировать*/
      case 9:          
           $query->andWhere(" eventType = 21 ");
      $countquery->andWhere(" eventType = 21");
      break;
      
      /*Отложить*/
      case 10:          
           $query->andWhere(" eventType = 11 ");
      $countquery->andWhere(" eventType = 11 ");
       break;
       
      
      break;
       
       
     }
     
 //  $this->debug[] = $timeCond;  
 //  $this->debug[] = $addTimeCond;

 
 
      /*всего*/
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType >= 10 and eventType < 20";
     $this->leadLeafStatus['actual'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

     /*всего*/
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType >= 10 and eventType < 100";
     $this->leadLeafStatus['all'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

        /*Новые*/     
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType = 10 ".$addTimeCond;
     $this->leadLeafStatus['noScan'] = Yii::$app->db->createCommand($strSql)->queryScalar();    
     
        /*Отложить*/
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType = 11 ";
     $this->leadLeafStatus['wait'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

          /*Контакты*/
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}} where ".$timeActual;
     $strSql .= " AND eventType = 16 ".$addTimeCond;
     $this->leadLeafStatus['inProgress'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

     /*Клиенты*/
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}}  where ".$timeActual;
     $strSql .= " AND eventType = 17 ".$addTimeCond;
     $this->leadLeafStatus['client'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

      /*Товар*/     
     $strSql ="SELECT count(DISTINCT({{%contact}}.id)) FROM {{%contact}}  where ".$timeActual;
     $strSql .= " AND eventType = 14 ".$addTimeCond;
     $this->leadLeafStatus['ware'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

     /*Создать сделку*/  
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType = 15 ".$addTimeCond;
     $this->leadLeafStatus['sdelkaPrepared'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     /*Сделка */
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " and eventType = 20 ";
     $this->leadLeafStatus['zakaz'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    

     /*Рассмотрено*/
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType >= 20 AND  eventType < 25";
     $this->leadLeafStatus['Finished'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType = 10 AND ref_org > 0";
     $this->leadLeafStatus['Scan'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     $strSql ="SELECT count(id) FROM {{%contact}} where ".$timeCond;
     $strSql .= " AND eventType = 21 ";
     $this->leadLeafStatus['ignore'] = Yii::$app->db->createCommand($strSql)->queryScalar();                    
     
     
     
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'contactDate',  
                'note', 
                'contactOrgTitle', 
                'userFIO',
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
/*********************************************************/
  public function getOrgListProvider($params)
   {

    $query  = new Query();
    $query->select ("id, title as contactOrgTitle, contactPhone,  schetINN")->from("{{%orglist}}");

    $countquery  = new Query();
    $countquery->select ("count(id)")->from("{{%orglist}}");

    /*изза note required*/
    if (($this->load($params) /*&& $this->validate()*/)) {
       
     $query->andFilterWhere(['like', 'title', $this->contactOrgTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->contactOrgTitle]);     
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'contactOrgTitle', 
            ],
            'defaultOrder' => [ 'contactOrgTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/
 /*********************************************************/
  public function getDocListProvider($params)
   {
    $query  = new Query();
    $query->select ([
            '{{%documents}}.id',
            'docIntNum',
            'docOrigNum',
            'docOrigDate',
            'docTitle',
            'docClassifyRef',
            'orgTitle',
            '{{%doc_classify}}.docType'
            ])
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_classify}}","{{%doc_classify}}.id = {{%documents}}.docClassifyRef");

    $countquery  = new Query();
    $countquery->select ("count({{%documents}}.id)")            
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_classify}}","{{%doc_classify}}.id = {{%documents}}.docClassifyRef");
            
    if (($this->load($params) /*&& $this->validate()*/)) {
         $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
         $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);     
    
      }
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
            'id', 
            'docIntNum',
            'docOrigDate',
            'docClassifyRef',
            'docType',
            'orgTitle'
            ],
            'defaultOrder' => [ 'docOrigDate' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***************/ 
  public function getZakazListProvider($params)
   {

   
    $query  = new Query();
    $query->select ("{{%zakaz}}.id, formDate, schetNum, schetDate, schetSumm, isSchetActive")
            ->from("{{%zakaz}}")
            ->leftJoin("{{%schet}}","{{%zakaz}}.id = {{%schet}}.refZakaz");

    $countquery  = new Query();
    $countquery->select ("count({{%zakaz}}.id)")            
            ->from("{{%zakaz}}")
            ->leftJoin("{{%schet}}","{{%zakaz}}.id = {{%schet}}.refZakaz");

     $query->andWhere(['=', '{{%zakaz}}.refOrg', $this->orgId]);
     $countquery->andWhere(['=', '{{%zakaz}}.refOrg',  $this->orgId]);     
       
            
    if (($this->load($params) /*&& $this->validate()*/)) {
        
        if ($this->id == 1)   
        {
            $query->andFilterWhere(['=', 'ifnull(schetNum,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(schetNum,0)', 0]);
        }
        if ($this->id == 2)   
        {
            $query->andFilterWhere(['<>', 'ifnull(schetNum,0)', 0]);
            $countquery->andFilterWhere(['<>', 'ifnull(schetNum,0)', 0]);
        }
 
        if ($this->isSchetActive == 1)   
        {
        $query->andFilterWhere(['=', 'isSchetActive', 1]);
        $countquery->andFilterWhere(['=', 'isSchetActive', 1]);     
        }
        if ($this->isSchetActive == 2)   
        {
        $query->andFilterWhere(['=', 'isSchetActive', 0]);
        $countquery->andFilterWhere(['=', 'isSchetActive', 0]);     
        }
     
     }
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'formDate', 
                'schetNum', 
                'schetDate', 
                'schetSumm', 
                'isSchetActive'
            ],
            'defaultOrder' => [ 'formDate' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
 /*********************************************************/
  public function getPhoneProvider($params)
   {
  
    $query  = new Query();
    $query->select ([
            '{{%phones}}.id',
            'phoneContactFIO',
            'phone',
            'status',
            'lastD'   
            ])
            ->from("{{%phones}}")
            ->leftJoin("(SELECT MAX(contactDate) as lastD, ref_phone FROM {{%contact}} group by ref_phone) as contact", "contact.ref_phone = {{%phones}}.id")
            ->distinct();
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%phones}}.id))")            
            ->from("{{%phones}}")            
            ->leftJoin("(SELECT MAX(contactDate) as lastD, ref_phone FROM {{%contact}} group by ref_phone) as contact", "contact.ref_phone = {{%phones}}.id")
            ;

     $query->andWhere(['=', '{{%phones}}.ref_org', $this->orgId]);
     $countquery->andWhere(['=', '{{%phones}}.ref_org',  $this->orgId]);     
       
            
    if (($this->load($params) && $this->validate())) {     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            
            'sort' => [
            
            'attributes' => [
            'phoneContactFIO',
            'phone',
            'status',
            'lastD'   
            ],
            'defaultOrder' => [ 'lastD' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/
  public function getPhoneContactProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
            'phoneContactFIO',
            '{{%phones}}.id',
            'phone',
            'status',
            'lastD'   
            ])
            ->from("{{%phones}}")
            ->leftJoin("(SELECT MAX(contactDate) as lastD, ref_phone FROM {{%contact}} group by ref_phone) as contact", "contact.ref_phone = {{%phones}}.id")
            ->distinct();
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%phones}}.id))")            
            ->from("{{%phones}}")            
            ->leftJoin("(SELECT MAX(contactDate) as lastD, ref_phone FROM {{%contact}} group by ref_phone) as contact", "contact.ref_phone = {{%phones}}.id")
            ;

     $query->andWhere(['=', '{{%phones}}.ref_org', $this->orgId]);
     $countquery->andWhere(['=', '{{%phones}}.ref_org',  $this->orgId]);     
       
     $query->andWhere(['=', '{{%phones}}.phoneContactFIO', $this->contactFIO]);
     $countquery->andWhere(['=', '{{%phones}}.phoneContactFIO',  $this->contactFIO]);     
                   
    if (($this->load($params) && $this->validate())) {
             
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
            'phone',
            'status',
            'lastD'   
            ],
            'defaultOrder' => [ 'lastD' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   



   
   /**end of class**/
 }
