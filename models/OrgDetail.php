<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 

use app\models\OrgList;
use app\models\PhoneList;
use app\models\TblPhone;
use app\models\EmailList;
use app\models\AdressList;
use app\models\UrlList;

use app\models\MarketCalendarForm;
use app\models\ContactList;

use app\models\TblOrgCategory;
use app\models\TblOrgAccounts;
use app\models\TblOrgRekvezit;
use app\models\TblOrgDeals;
use app\models\TblBankOpArticle;
use app\models\TblBankOpGrp;
use app\models\TblOrgOkved;
use app\models\TblOrgDblGis;
use app\models\TblOrgDostavka;

class OrgDetail extends Model 
{

    public $orgFullTitle ="";
    public $shortTitle  = "";
    public $orgTitle="";    
    public $title ="";

    
    public $orgDealTitle="N/A";
    public $status = 0;
    public $id = 0;

    public $grd_contactDate ="";
    public $grd_userFIO="";
    public $grd_contactFIO="";

    public $contactURL = "";
        
    public $contactFIO = "";
    public $contactDate ="";
    public $contactPhone="";
    public $userFIO ="";
    public $orgId ="";
    public $nextdate ="";
    public $note ="";
    public $orgTypeRef = 0;
    
    public $shortComment ="";
        
    public $adressId    ="";
    public $adressArea  ="";
    public $adressCity  ="";
    public $adressDistrict  ="";
    public $adress       ="";
    public $index       ="";
    public $schetINN    ="";
    public $orgKPP    ="";
    public $orgINN    ="";
    public $fltKPP ="";
    
    public $editOrgGroupTitle="";

    public $contactEmail ="";
    public $emailContactFIO ="";
    public $orgGrpTitle ="";
    
    public $orgGroupTitle ="";
    
    
    public $registartionDate ="";
    public $headFIO ="";
    public $isOrgActive = 1;
    
    public $orgBIK="";    
    public $orgBank="";    
    public $orgKS="";    
    public $orgAccount="";   
    
    public $debug=[];

    public $isSetNDS=0;
    public $NDS=0; 
    
    public $orgOGRN='';
    public $orgOKATO='';
    public $orgOKONH='';    
    public $orgOKPO=''; 
    public $orgOKVED='';  
    public $dblGisLabel='';    

    public $dogFIO='';    
    public $dogBase='';    
    public $dogHead='';    
    public $signFIO='';    
    public $signHead='';    
    public $orgBase='';
    public $razdel ="";  
    
    public $detail =0;
/*    public $isWareSupp=0;   //0x1
    public $isServiceSupp=0; //0x2
    public $isTransportSupp=0; //0x4
    public $isClient=0; //0x8
    public $mainDeal=0;*/

public $isWare=0;     
public $isService=0;
public $isOther=0;
public $isBank=0;
public $isClient=0;
          
public $fltOrgDeal=0;    
public $leadId=0;    


    public $contarctArray=array();
    public $dublicateTitle=array();
    public $dublicateINN=array();
    public $dublicateKPP=array();
        
    public $dataRequestId;
    public $dataType;
    public $dataVal  ;      
        

    public function rules()
    {
        return [
            [['orgId', 'title', 'orgTitle', 'shortTitle', 'orgFullTitle',
            'orgTypeRef', 'adressId', 'schetINN', 'adressArea', 'adressCity', 'adressDistrict', 'adress', 'contactEmail', 'contactPhone', 'index',
            'contactFIO', 'nextdate', 'note', 'shortComment', 'contactURL', 'editOrgGroupTitle', 'orgKPP',
            'registartionDate', 'headFIO', 'isOrgActive',
            'dataRequestId', 'dataType', 'dataVal', 'orgBIK', 'orgBank', 'orgKS', 'orgAccount',                
             'NDS', 'isSetNDS', 'razdel',
             'orgOGRN', 'orgOKATO', 'orgOKONH','orgOKPO',
             'dogFIO','dogBase','dogHead','signFIO','signHead','orgBase',
             ], 'default'],
            [['grd_contactFIO',  'grd_contactDate', 'grd_userFIO','orgGrpTitle', 'orgINN', 'orgKPP','fltKPP', 'orgTitle','title', 'fltOrgDeal'], 'safe'],            
            ['contactEmail', 'email'],
            
            
        ];
    }
/*************************************************************/

public function getCfgValue($key)          
   {
      $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
               ':key' => intval($key),               
               ])->queryOne();  
     if (!empty($record)) return $record['keyValue'];
     return 0;
}

public $cfgParam=[];
public function loadSelfOrgData(){
    $this->orgId = $this->getCfgValue(1100);
    if (empty($this->orgId)) {
        $record= new OrgList();
        $record->save();
        $this->orgId = $record->id;
        Yii::$app->db->createCommand(
            'UPDATE {{%config}} set keyValue=:keyValue WHERE id =1100', 
            [
               ':keyValue' => intval($this->orgId),               
            ])->execute();  
    }          

    $this->cfgParam['EMail-def'] = $this->getCfgValue(1000);
    $this->cfgParam['EMail-SALE'] = $this->getCfgValue(1001);
    $this->cfgParam['EMail-PURCH'] = $this->getCfgValue(1002);
    $this->cfgParam['EMail-Control'] = $this->getCfgValue(1003);
    $this->cfgParam['schetDuration'] = $this->getCfgValue(1200);
    $this->cfgParam['schetCondition'] = $this->getCfgValue(1201);
    $this->cfgParam['wareCondition'] = $this->getCfgValue(1202);
    $this->cfgParam['wareCondition'] = $this->getCfgValue(1202);
    $this->cfgParam['director'] = $this->getCfgValue(1203);
    $this->cfgParam['buhgalter'] = $this->getCfgValue(1204);
}


/**************/ 
public $defContactPhone;
public $defContactFIO;
public $defContactEmail;

  public function loadOrgRecord()
  {
      $this->orgId = intval($this->orgId);
      if (empty ($this->orgId)) {return; }
      $record = OrgList::findOne($this->orgId);
      if (empty($record)) return;
      
      
      $this->title        = $record->title;      
      $this->orgFullTitle = $record->orgFullTitle;
      $this->shortTitle = $record->shortTitle;
      if (!empty ($record->registartionDate) )
          $this->registartionDate = date("d.m.Y", strtotime($record->registartionDate)); 

      $this->headFIO = $record->headFIO;
      if (empty ($this->headFIO) && !empty($this->cfgParam['director'])) $this->headFIO = $this->cfgParam['director'];
      
       
      
      
      $this->isOrgActive = $record->isOrgActive;

      $this->NDS = $record->defNDS;
      $this->isSetNDS = $record->isSetNDS;

      $this->schetINN     = $record->schetINN;    
      $this->orgKPP       = $record->orgKPP;
      $this->orgOGRN      = $record->orgOGRN;    
      $this->orgOKATO     = $record->orgOKATO;
      $this->orgOKONH     = $record->orgOKONH;
      $this->orgOKPO      = $record->orgOKPO;
      $this->razdel       = $record->razdel;
      
      $this->contactPhone = $record->contactPhone;
      $this->contactFIO   = $record->contactFIO;
      
      $rekvRecord = TblOrgRekvezit::findOne(['refOrg' => $record->id]);
      if (!empty($rekvRecord))
      {
        $this->dogFIO   = $rekvRecord->dogFIO;
        $this->dogBase  = $rekvRecord->dogBase;
        $this->dogHead  = $rekvRecord->dogHead;
        $this->signFIO  = $rekvRecord->signFIO;
        $this->signHead = $rekvRecord->signHead;   
        $this->orgBase  = $rekvRecord->orgBase;   
      }
      
      $strSql  = "SELECT DISTINCT phone,phoneContactFIO, isDefault from {{%phones}}";
      $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $record->id,])->queryAll();     

//$this->debug[]=Yii::$app->db->createCommand($strSql, [':ref_org' => $record->id,])->getRawSql();
//$this->debug[]=$resList;

      $N = count($resList);
      if ($N >0){
        if(empty($this->contactPhone)) $this->contactPhone  =  $resList[0]['phone']; 
        if(empty($this->contactFIO))   $this->contactFIO    =  $resList[0]['phoneContactFIO']; 
         $this->defContactPhone  =  $resList[0]['phone']; 
         $this->defContactFIO    =  $resList[0]['phoneContactFIO']; 
        
      }     
        if(empty($this->defContactFIO))   $this->defContactFIO    =  $this->contactFIO;

      
      $this->contactEmail = $record->contactEmail;    
      $strSql  = "SELECT DISTINCT email,emailContactFIO, isDefault from {{%emaillist}}";
      $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $record->id,])->queryAll();                                        
      $N = count($resList);
      if ($N >0){
        if(empty($this->contactEmail)) $this->contactEmail   =  $resList[0]['email']; 
        $this->defContactEmail  =  $resList[0]['email']; 
        $this->emailContactFIO=  $resList[0]['emailContactFIO']; 
      }     
          
      $accRecord= TblOrgAccounts::findOne(
      [
      'isDefault' => 1,
      'refOrg'    => $this->orgId
      ]);     
      if (empty($accRecord)) 
      $accRecord= TblOrgAccounts::findOne(
      [
      'refOrg'    => $this->orgId
      ]);                
      if (!empty($accRecord)) 
      {
        $this-> orgBIK = $accRecord->orgBIK;
        $this->orgBank = $accRecord->orgBank;
        $this->orgKS = $accRecord->orgKS;
        $this->orgAccount = $accRecord->orgRS;
      }
      

      $adrRecord= AdressList::findOne(
      [
      'isOfficial' => 1,
      'ref_org'    => $this->orgId
      ]);     
      if (empty($adrRecord)) 
      $adrRecord= AdressList::find()
        ->where (['ref_org'    => $this->orgId])
        ->andWhere (["!=","ifnull(adress,'')",""])
        ->one();                
      if (!empty($adrRecord)) 
      {
        $this->adress = $adrRecord->adress;
      }


      $adrRecord= AdressList::findOne(
      [
      'isOfficial' => 1,
      'ref_org'    => $this->orgId
      ]);     
      if (empty($adrRecord)) 
      $adrRecord= AdressList::find()
        ->where (['ref_org'    => $this->orgId])
        ->andWhere (["!=","ifnull(adress,'')",""])
        ->one();                
      if (!empty($adrRecord)) 
      {
        $this->adress = $adrRecord->adress;
      }


    /*  $okvedRecord= TblOrgOkved::findOne(
      [
      'isDefault' => 1,
      'refOrg'    => $this->orgId
      ]);     
      if (empty($adrRecord)) 
      $okvedRecord= TblOrgOkved::findOne(
      [
      'refOrg'    => $this->orgId
      ]);     
          
      if (!empty($okvedRecord)) 
      {
        $this->orgOKVED = $okvedRecord->OKVED;
      }*/

      $dealList=Yii::$app->db->createCommand("SELECT DISTINCT {{%bank_op_grp}}.grpTitle from {{%org_deals}},{{%bank_op_article}},{{%bank_op_grp}} where 
      {{%org_deals}}.articleRef = {{%bank_op_article}}.id
      AND {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id 
      AND {{%org_deals}}.state = 1
      AND {{%org_deals}}.refOrg = :refOrg"
      ,[':refOrg' =>  $this->orgId])
     ->queryAll(); 
      $N=count($dealList); 
      $this->orgDealTitle ="";
      for ($i=0;$i<$N;$i++)
      {
       if ($i>0)$this->orgDealTitle.= " / ";
       $this->orgDealTitle.=$dealList[$i]['grpTitle'];
      }      
      if ($N=0)$this->orgDealTitle="N/A";
      
      $okvedList=Yii::$app->db->createCommand('SELECT  OKVED, isDefault from {{%org_okved}}  
      where refOrg=:refOrg',[':refOrg' =>  $this->orgId])->queryAll();      
      $this->orgOKVED ="";
      $N=count($okvedList); 
      for ($i=0;$i<$N;$i++)
      {
        if($okvedList[$i]['isDefault'] != 1) continue;  
        $this->orgOKVED .=$okvedList[$i]['OKVED'].";";  
      }
      if (empty($this->orgOKVED) && $N> 0) $this->orgOKVED =$okvedList[0]['OKVED'].";";  



      $dblGisList=Yii::$app->db->createCommand('SELECT  dblGisLabel, isDefault from {{%org_dbl_gis}}  
      where refOrg=:refOrg',[':refOrg' =>  $this->orgId])->queryAll();      
      $this->dblGisLabel ="";
      $N=count($dblGisList); 
      for ($i=0;$i<$N;$i++)
      {
        if($dblGisList[$i]['isDefault'] != 1) continue;  
        $this->dblGisLabel .=$dblGisList[$i]['dblGisLabel'].";";  
      }
      if (empty($this->dblGisLabel) && $N> 0) $this->dblGisLabel =$dblGisList[0]['dblGisLabel'].";";  

     
      
    $this->note         = $record->orgNote;    
    $this->shortComment = $record->shortComment;    

    
      
    $this->dublicateTitle=Yii::$app->db->createCommand('SELECT id, title, orgINN, orgKPP from {{%orglist}}  where isOrgActive=1 AND  {{%orglist}}.title =:title and id <> :orgId',
                           [':title' => $record->title, ':orgId' =>  $record->id])->queryAll();

    $this->dublicateINN=Yii::$app->db->createCommand('SELECT id, title, orgINN, orgKPP from {{%orglist}}  where  isOrgActive=1 AND {{%orglist}}.orgINN =:orgINN and id <> :orgId',
                           [':orgINN' => $record->orgINN, ':orgId' =>  $record->id])->queryAll();
                           
    $this->dublicateKPP==Yii::$app->db->createCommand('SELECT id, title, orgINN, orgKPP from {{%orglist}}  where isOrgActive=1 AND {{%orglist}}.orgKPP =:orgKPP and id <> :orgId',
                           [':orgKPP' => $record->orgKPP, ':orgId' =>  $record->id])->queryAll();
 
      
      
      return $record;      
      
  }

/*************************************************************/


   public function saveData()        
   {
      $this->orgId = intval($this->orgId);
      $ret= [
      'id' => $this->orgId,
      'rekvRef' => 0,
      'res' => false,            
      ];
      
      if (empty($this->orgId)) $orgRecord   = new OrgList();
      else
      $orgRecord   = OrgList::findOne(['id'   => $this->orgId ]);     
      if (empty($orgRecord)) return $ret;

      $orgRecord->title = $this->title;
      $orgRecord->orgFullTitle = $this->orgFullTitle;
      $orgRecord->shortTitle = $this->shortTitle;
      if (!empty($this->registartionDate))

      $orgRecord->registartionDate = date ("Y-m-d", strtotime($this->registartionDate));
      $orgRecord->isOrgActive = $this->isOrgActive;
      $orgRecord->headFIO = $this->headFIO;

      $orgRecord->defNDS = floatval($this->NDS);
      $orgRecord->isSetNDS = $this-> isSetNDS;
  
      $orgRecord->schetINN = $this->schetINN;      
      $orgRecord->orgKPP = $this->orgKPP;
      $orgRecord->orgOGRN = $this->orgOGRN;
      $orgRecord->orgOKATO = $this->orgOKATO;
      $orgRecord->orgOKONH = $this->orgOKONH;
      $orgRecord->orgOKPO = $this->orgOKPO;
          
      $orgRecord->razdel= $this->razdel;
 
      $orgRecord->save();
      $ret['id'] = $orgRecord->id;
      
      $rekvRecord = TblOrgRekvezit::findOne(['refOrg' => $orgRecord->id]);
      if (empty($rekvRecord)) $rekvRecord =new  TblOrgRekvezit();
      if (empty($rekvRecord)) return $ret;
        $rekvRecord->refOrg   = $orgRecord->id;
        $rekvRecord->dogFIO   = $this->dogFIO;
        $rekvRecord->dogBase  = $this->dogBase;
        $rekvRecord->dogHead  = $this->dogHead;
        $rekvRecord->signFIO  = $this->signFIO;
        $rekvRecord->signHead = $this->signHead;      
        $rekvRecord->orgBase  = $this->orgBase;
     
     $rekvRecord->save(); 
     $ret['rekvRef'] = $rekvRecord->id;
     $ret['res'] = true;
      
     return $ret;
  }    
    

  public function createOrg($orgTitle)        
   {
      $ret = [
       'id' => 0,
       'orgTitle' => $orgTitle,
       'res' => false,
      ];
      
      $orgRecord   = new OrgList();     
      if (empty($orgRecord)) return $ret;

      $orgRecord->title = $orgTitle;
      $orgRecord->save();
      
      $ret['id'] = $orgRecord->id;
      $ret['res'] = true;
      
      return $ret;
  }    
     


/*************************************************************/

   public function getLeadText()
   {
   if (empty($this->leadId)) return "";
      $record   = ContactList::findOne(['id'   => $this->leadId ]);     
   if (empty($record)) return "";   
      
         $this->contactFIO = $record->contactFIO;
         if(!empty($record->ref_phone)){
             $phoneRecord =TblPhone::findOne('$record->ref_phone');
             if (!empty($phoneRecord)) $this->contactPhone=$phoneRecord->phone;             
         }
         if(empty( $this->contactPhone)) $this->contactPhone=$record->contactPhoneText;      
         $this->contactEmail=$record->contactEmail;
   return $record->note;
   }   
    
   public function saveNote()        
   {
      
      $orgRecord   = OrgList::findOne(['id'   => $this->orgId ]);     
      if (empty($orgRecord)) return;
        $orgRecord->orgNote = $this->note;    
      $orgRecord->shortComment = $this->shortComment;    
      $orgRecord->save();
   }    
/***************/
   public function setOrgCategory($id,$category)
   {
      $orgRecord   = OrgList::findOne(['id'   => $id ]);     
      if (empty($orgRecord)) return false;
      $orgRecord ->orgTypeRef = $category; 
      $orgRecord->save();                                     
      return true;

   }
/***************/      
   public function getCategory($id)   
   {
    $record   = TblOrgCategory::findOne(['id' => $id ]);  
    if (empty($record)) return "";      
    return $record->catTitle;
       
   }
/***************/   

   public function getOrgTypeList()
   {
    $statusTitles = Yii::$app->db->createCommand("Select id, catTitle FROM {{%org_category}} order by id")->queryAll();
    $list = ArrayHelper::map($statusTitles, 'id', 'catTitle');      
    array_unshift ($list,"Не установлен");
    return $list;
   }
/*************/   

   public function getScladList()
   {
    $statusTitles = Yii::$app->db->createCommand("Select id, scladAdress FROM {{%scladlist}} where useAsAdress=1 order by id")->queryAll();
    $list = ArrayHelper::map($statusTitles, 'id', 'scladAdress');          
    return $list;
   }


   public function getFltOrgDeal()
   {
    $statusTitles = Yii::$app->db->createCommand("Select id, grpTitle FROM {{%bank_op_grp}} order by grpTitle")->queryAll();
    $list = ArrayHelper::map($statusTitles, 'id', 'grpTitle');          
    $list[-1]='Не установлен'; 
    return $list;
   }


/*************/   
   public function switchOrgReject($id)
   {
      $id=intval($id); 
      $ret= [
        'res' => false,
        'id'  => $id,
        'val' => ''
      ];      
      $orgRecord   = OrgList::findOne(['id'   => $id ]);     
      if (empty($orgRecord)) return $ret;
       
       if ($orgRecord -> isReject == 0)
       {    
          $orgRecord -> isReject = 1;          
          //Все события на этого клиента  метим как неактуальные          
            Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET eventStatus =2, event_date = '". date('Y-m-d h:i:s', time())."' WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 ", [':ref_org'   =>  $orgRecord->id, ])->execute();
       }
       else 
       {    
          $orgRecord -> isReject = 0;
                /*Добавим запись в календарь*/
            $calendar = new MarketCalendarForm();
            $event_ref = 8;
            $eventNote = "Произвольный контакт";
            $calendar->createEvent(date("Y-m-d"),$event_ref , $orgRecord->id, 0, 0, $eventNote);      
       }                             
      $orgRecord->save();               
      $ret['val'] = $orgRecord -> isReject;      
      $ret['res'] = true;
      return true;
   }

/*************/   
   public function switchOrgActivity($id)
   {
      $orgRecord   = OrgList::findOne(['id'   => $id ]);     
      if (empty($orgRecord)) return false;
       
       if ($orgRecord -> isOrgActive == 1)
       {    
          $orgRecord -> isOrgActive = 0;
          //Все события на этого клиента  метим как неактуальные
          
              Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET eventStatus =2, event_date = '". date('Y-m-d h:i:s', time())."' WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 ", [':ref_org'   =>  $orgRecord->id, ])->execute();
       }
       else 
       {    
          $orgRecord -> isOrgActive = 1;
                /*Добавим запись в календарь*/
            $calendar = new MarketCalendarForm();
            $event_ref = 8;
            $eventNote = "Произвольный контакт";
            $calendar->createEvent(date("Y-m-d"),$event_ref , $orgRecord->id, 0, 0, $eventNote);      
       }                             
      $orgRecord->save();                                     
      return true;
   }
   
/****************/    
public function addGroupTitle()
{
    Yii::$app->db->createCommand('INSERT INTO {{%org_group}} (orgGrpTitle) VALUES (:orgGrpTitle)'
                                             ,[':orgGrpTitle'=>$this->editOrgGroupTitle])->execute();           
}
/****************/    
public function remGroupById($id)
{
    Yii::$app->db->createCommand('UPDATE  {{%orglist}} set orgGrpRef=0 where orgGrpRef=:grpId'
                                             ,[':grpId'=>$id])->execute();           
    Yii::$app->db->createCommand('DELETE FROM {{%org_group}} where id=:grpId'
                                             ,[':grpId'=>$id])->execute();           
}
/****************/    
public function addOrgToGroup($orgId, $grpId)
{
    Yii::$app->db->createCommand('UPDATE  {{%orglist}} set orgGrpRef=:grpId where id=:orgId'
                                             ,[':grpId'=>$grpId, ':orgId'=>$orgId])->execute();           
}

public function orgLinkContract($orgId, $contractId)
{
    Yii::$app->db->createCommand('UPDATE  {{%contracts}} set refOrg=:orgId where id=:contractId'
                                             ,[':contractId'=>$contractId, ':orgId'=>$orgId])->execute();           
}


/****************/    
public function orgDelFromGroup($orgId)
{
    Yii::$app->db->createCommand('UPDATE  {{%orglist}} set orgGrpRef=0 where id=:orgId'
                                             ,[':orgId'=>$orgId])->execute();           
}


    public function getUseData($id)
    {
        $ret = Yii::$app->db->createCommand('SELECT Title, ref_user, startTimeInWork, userFIO 
        from {{%orglist}}
        left join {{%user}} on {{%orglist}}.ref_user ={{%user}}.id
        where {{%orglist}}.id =:id')
           ->bindValue(':id', $id)
           ->queryOne();
        return $ret;
    }

  
   public function getCompanyPhones()
   {
        $ret =  Yii::$app->db->createCommand('SELECT id, phone, phoneContactFIO, status from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryAll();       
        return $ret;
   }   

   public function getCompanyUrls()
   {
        $ret =  Yii::$app->db->createCommand('SELECT id, url, from {{%urllist}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryAll();       
        if     (empty($ret)) return $ret;                                                     
        return $ret;
   }   
   
     
   public function getCompanyAdress()
   {
        $ret =  Yii::$app->db->createCommand('SELECT id, area, city, district, adress, {{%adreslist}}.index, isOfficial
        from {{%adreslist}} where ref_org=:ref_org ORDER BY isOfficial DESC ',[':ref_org'=>$this->orgId])->queryAll();       
                        
        if     (empty($ret)) return $ret;
        $this->adressId   = $ret[0]['id'];
        $this->adressArea = $ret[0]['area'];
        $this->adressCity = $ret[0]['city'];
        $this->adressDistrict = $ret[0]['district'];
        $this->adress      = $ret[0]['adress'];
        $this->index      = $ret[0]['index'];
        
        return $ret;
   }   


   public function getOrgSverka()
   {

    $result =
    [
     'managerFIO' => '',
     'oplataCnt' => 0,
     'oplataSum' => 0,
     'lastOplate' => '',
     'supplyCnt' => 0,
     'supplySum' => 0,
     'lastSupply' => '',
    ];
    
    if (empty($this->orgId)) return $result;
    
    $result['managerFIO'] = Yii::$app->db->createCommand('SELECT userFIO from {{%orglist}},{{%user}} where {{%orglist}}.refManager={{%user}}.id AND {{%orglist}}.id=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryScalar();       
     
    $oplataData= Yii::$app->db->createCommand('SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate 
     from {{%oplata}} where refOrg = :ref_org' ,[':ref_org'=>$this->orgId])->queryAll();       
     
    if (count ($oplataData) > 0)
    {        
        $result['oplataCnt'] = $oplataData[0]['oplataCnt'];
        $result['oplataSum'] = $oplataData[0]['oplataSum'];
        $result['lastOplate'] = date('d.m.Y',strtotime($oplataData[0]['lastOplate']));
        if ($result['oplataCnt']==0 ) $result['lastOplate']="";
    }else
    {
        $result['oplataCnt'] = 0;
        $result['oplataSum'] = 0;
        $result['lastOplate'] ="";
    }

    
    $supplyData= Yii::$app->db->createCommand('SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, max(supplyDate) as lastSupply 
     from {{%supply}} where refOrg = :ref_org' ,[':ref_org'=>$this->orgId])->queryAll();       

    if (count ($supplyData) > 0)
    {
        $result['supplyCnt'] = $supplyData[0]['supplyCnt'];
        $result['supplySum'] = $supplyData[0]['supplySum'];
        $result['lastSupply'] = date('d.m.Y',strtotime($supplyData[0]['lastSupply']));      
        if ($result['supplyCnt']==0 ) $result['lastSupply']="";
        
    }else
    {
        $result['supplyCnt'] = 0;
        $result['supplySum'] = 0;
        $result['lastSupply'] = "";
    }

/***/
    
    $supplierOplataData= Yii::$app->db->createCommand('SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate 
     from {{%supplier_oplata}} where refOrg = :ref_org' ,[':ref_org'=>$this->orgId])->queryAll();       
     
    if (count ($supplierOplataData) > 0)
    {        
        $result['supplier_oplataCnt'] = $supplierOplataData[0]['oplataCnt'];
        $result['supplier_oplataSum'] = $supplierOplataData[0]['oplataSum'];
        $result['supplier_lastOplate'] = date('d.m.Y',strtotime($supplierOplataData[0]['lastOplate']));
        if ($result['supplier_oplataCnt']==0 ) $result['supplier_lastOplate']="";
    }else
    {
        $result['supplier_oplataCnt'] = 0;
        $result['supplier_oplataSum'] = 0;
        $result['supplier_lastOplate'] ="";
    }

    
    $supplierSupplyData= Yii::$app->db->createCommand('SELECT count(id) as supplyCnt, SUM(wareSumm) as supplySum, max(requestDate) as lastSupply 
     from {{%supplier_wares}} where refOrg = :ref_org' ,[':ref_org'=>$this->orgId])->queryAll();       

    if (count ($supplyData) > 0)
    {
        $result['supplier_supplyCnt'] = $supplierSupplyData[0]['supplyCnt'];
        $result['supplier_supplySum'] = $supplierSupplyData[0]['supplySum'];
        $result['supplier_lastSupply'] = date('d.m.Y',strtotime($supplierSupplyData[0]['lastSupply']));      
        if ($result['supplier_supplyCnt']==0 ) $result['supplier_lastSupply']="";
    }else
    {
        $result['supplier_supplyCnt'] = 0;
        $result['supplier_supplySum'] = 0;
        $result['supplier_lastSupply'] ="";
    }

    
    
    return $result;
     
   }   
   
 /**********************************************************/  
   public function markUrls($id, $flg)
   {
       if ($flg == 0) $flg =1;
       else $flg = 0;
       

        if ($flg ==1)
        {
        Yii::$app->db->createCommand('DELETE FROM {{%urllist}} where id=:id AND (url IS NULL  OR url ="" )')
                                ->bindValue(':id', $id)
                                ->execute();               
        }
                       
        Yii::$app->db->createCommand('UPDATE {{%urllist}} SET isBad = :status where id=:id')
                                ->bindValue(':id', $id)
                                ->bindValue(':status', $flg)
                                ->execute();               
   }   

   public function markAdress($id, $flg)
   {
       if ($flg == 0) $flg =1;
       else $flg = 0;
       

        if ($flg ==1)
        {
        Yii::$app->db->createCommand('DELETE FROM {{%adreslist}} where id=:id AND (adress IS NULL  OR adress ="" )')
                                ->bindValue(':id', $id)
                                ->execute();               
        }
        
        Yii::$app->db->createCommand('UPDATE {{%adreslist}} SET isBad = :status where id=:id')
                                ->bindValue(':id', $id)
                                ->bindValue(':status', $flg)
                                ->execute();               
   }   

   
   public function markEmail($id, $flg)
   {
       if ($flg != 2) $flg =2;
       else $flg = 0;
       

        if ($flg ==2)
        {
        Yii::$app->db->createCommand('DELETE FROM {{%emaillist}} where id=:id AND (email IS NULL  OR email ="" )')
                                ->bindValue(':id', $id)
                                ->execute();               
        }
                    
        Yii::$app->db->createCommand('UPDATE {{%emaillist}} SET status = :status where id=:id')
                                ->bindValue(':id', $id)
                                ->bindValue(':status', $flg)
                                ->execute();               
                                
                                
                                
   }   
   
   public function markPhone($id, $flg)
   {
       if ($flg != 2) $flg =2;
       else $flg = 0;
       
        if ($flg ==2)
        {
        Yii::$app->db->createCommand('DELETE FROM {{%phones}} where id=:id AND (phone IS NULL  OR phone ="" )')
                                ->bindValue(':id', $id)
                                ->execute();               
        }
        
        Yii::$app->db->createCommand('UPDATE {{%phones}} SET status = :status where id=:id')
                                ->bindValue(':id', $id)
                                ->bindValue(':status', $flg)
                                ->execute();               
   }   
   /*****************************************************************/
 /*
 ALTER TABLE `rik_contact` ADD COLUMN `old_ref` BIGINT DEFAULT 0;
 ALTER TABLE `rik_schet` ADD COLUMN `old_ref` BIGINT DEFAULT 0;
 ALTER TABLE `rik_zakaz` ADD COLUMN `old_ref` BIGINT DEFAULT 0;
 */  
  public function mergeOrg($masterRef, $slaveRef)
  {
  
  
    $slaveRecord=OrgList::findOne($slaveRef);
    if (empty($slaveRecord)) return;     
    $dublRecord= new TblOrgDublicate();
    $dublRecord ->oldRef = $slaveRef;
    $dublRecord ->refOrg = $masterRef;
    $dublRecord ->orgTitle = $slaveRecord->title;
    $dublRecord ->orgINN = $slaveRecord->orgINN;
    $dublRecord ->orgKPP = $slaveRecord->orgKPP;
    $dublRecord ->save();  
    $slaveRecord->delete();
    
    Yii::$app->db->createCommand('UPDATE {{%phones}} SET ref_org = :masterRef where ref_org=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();               
    
    Yii::$app->db->createCommand('UPDATE {{%emaillist}} SET ref_org = :masterRef where ref_org=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();               
 
    Yii::$app->db->createCommand('UPDATE {{%adreslist}} SET ref_org = :masterRef where ref_org=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  

    Yii::$app->db->createCommand('UPDATE {{%calendar}} SET ref_org = :masterRef where ref_org=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  
                                
                                 
    Yii::$app->db->createCommand('UPDATE {{%contact}} SET old_ref=:slaveRef, ref_org = :masterRef where ref_org=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();     

    Yii::$app->db->createCommand('UPDATE {{%schet}} SET  old_ref=:slaveRef, refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  

    Yii::$app->db->createCommand('UPDATE {{%zakaz}} SET  old_ref=:slaveRef, refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();                                  
                                
   Yii::$app->db->createCommand('UPDATE {{%supply}} SET  refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();                              

   Yii::$app->db->createCommand('UPDATE {{%oplata}} SET  refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();                              
                                                               

   Yii::$app->db->createCommand('UPDATE {{%supplier_schet_header}} SET  refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();
                                                                                                 
   Yii::$app->db->createCommand('UPDATE {{%supplier_oplata}} SET  refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();                             

   Yii::$app->db->createCommand('UPDATE {{%supplier_wares}} SET  refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();     
    
                                                                    
    Yii::$app->db->createCommand('UPDATE {{%ats_log}} SET orgRef = :masterRef where orgRef=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  

    Yii::$app->db->createCommand('UPDATE {{%mail}} SET refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  
                                
                                
    Yii::$app->db->createCommand('UPDATE {{%contracts}} SET refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  

                                
    Yii::$app->db->createCommand('UPDATE {{%documents}} SET refOrg = :masterRef where refOrg=:slaveRef')
                                ->bindValue(':masterRef', $masterRef)
                                ->bindValue(':slaveRef',  $slaveRef)
                                ->execute();  
                                                                  
/*
         $phoneRecord = new PhoneList ();
         $phoneRecord->ref_org = $this->orgId;
         $phoneRecord->phone   = $this->contactPhone;
         $phoneRecord->save();
*/         

  
  }


   /*****************************************************************/
   /*****************************************************************/
   public function getOrgActivityProvider($params)
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count({{%zakaz}}.id)")
            ->from("{{%zakaz}} ") 
            ->leftJoin("{{%schet}}", "{{%zakaz}}.id = {{%schet}}.refZakaz")
            ;
    
     $query->select("{{%zakaz}}.id, {{%zakaz}}.refOrg, formDate, {{%zakaz}}.isActive, isFormed, isGoodReserved, {{%schet}}.id as schetId, schetNum, schetDate, isSchetActive, isOplata, isAlter")
            ->from("{{%zakaz}} ") 
            ->leftJoin("{{%schet}}", "{{%zakaz}}.id = {{%schet}}.refZakaz")
            ;


    $countquery->where("{{%zakaz}}.refOrg=:refOrg");
         $query->where("{{%zakaz}}.refOrg=:refOrg");            
        

       if (($this->load($params) && $this->validate())) 
    {
          
    }
    
    $query->addParams([':refOrg' => $this->orgId]);
    $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'formDate',            
            'schetNum',
            'schetDate',
            'isSchetActive', 
            'isOplata', 
            ],
            'defaultOrder' => [    'formDate' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   

    
    
   public function getOrgContactProvider($params)
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count({{%contact}}.id)")
                  ->from("{{%contact}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")
                 ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone");
    
     $query->select("{{%contact}}.id as grd_id, contactFIO as grd_contactFIO, contactDate as grd_contactDate, note as grd_note, userFIO as grd_userFIO, phone as grd_phone, contactEmail ")
            ->from("{{%contact}} ") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")
            ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone");


    $countquery->where("{{%contact}}.ref_org=:refOrg");
         $query->where("{{%contact}}.ref_org=:refOrg");            
        

       if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', 'userFIO', $this->grd_userFIO]);        
        $query->andFilterWhere(['like', 'contactFIO', $this->grd_contactFIO]);        
        $query->andFilterWhere(['=', 'contactDate', $this->grd_contactDate]);
        
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);        
        $countquery->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);        
        $countquery->andFilterWhere(['=', 'contactDate', $this->contactDate]);
     
    }
    
        $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 3,
            ],
            'sort' => [
            'attributes' => [
            'grd_contactFIO',            
            'grd_contactDate',
            'grd_note',
            'grd_userFIO',
            'grd_phone'
            ],
            'defaultOrder' => [    'grd_contactDate' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   

   
   public function getAdressListProvider()
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count(id)") ->from("{{%adreslist}}");
    
     $query->select("id, area, city, district, adress, streetAdres, index, x, y, isBad, isOfficial ")
            ->from("{{%adreslist}}") ;

    $countquery->where("{{%adreslist}}.ref_org=:refOrg");
         $query->where("{{%adreslist}}.ref_org=:refOrg");            
        

     $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'area', 
            'city', 
            'district', 
            'adress', 
            'index', 
            'streetAdres',
            'x', 
            'y', 
            'isBad'
            ],
            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   

   public function getEmailList()
   {
     $query  = new Query();      
     $query->select("id, email, status ")
            ->from("{{%emaillist}} ") ;
     $query->where("{{%emaillist}}.ref_org=:refOrg"); 
     $query->addParams([':refOrg' => $this->orgId]);  
     return $query->createCommand()->queryAll();     
   }
      
   public function getPhoneList()
   {
     $query  = new Query();      
     $query->select("id, phone, phoneContactFIO, status ")
            ->from("{{%phones}} ") ;
     $query->where("{{%phones}}.ref_org=:refOrg"); 
     $query->addParams([':refOrg' => $this->orgId]);  
     return $query->createCommand()->queryAll();     
   }
   
   public function getPhoneListProvider()
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count(id)") ->from("{{%phones}}");
    
     $query->select("id, phone, phoneContactFIO, status, isDefault ")
            ->from("{{%phones}} ") ;

    $countquery->where("{{%phones}}.ref_org=:refOrg");
         $query->where("{{%phones}}.ref_org=:refOrg");            
        

     $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'phone', 
            'phoneContactFIO', 
            'status',            
            ],
            'defaultOrder' => [    'id' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   

   public function getEmailListProvider()
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count(id)") ->from("{{%emaillist}}");
    
     $query->select("id, email, status, emailContactFIO, isDefault ")
            ->from("{{%emaillist}} ") ;

    $countquery->where("{{%emaillist}}.ref_org=:refOrg");
         $query->where("{{%emaillist}}.ref_org=:refOrg");            
        

     $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'email', 
            'status', 
            'emailContactFIO',            
            ],
            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   
                        
   public function getUrlListProvider()
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count(id)") ->from("{{%urllist}}");
    
     $query->select("id, url, isBad")
            ->from("{{%urllist}} ") ;

    $countquery->where("{{%urllist}}.ref_org=:refOrg");
         $query->where("{{%urllist}}.ref_org=:refOrg");            
        

     $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'url', 
            'isBad',            
            ],
            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   
                        


   public function getEventListProvider()
   {
               
    $countquery  = new Query();
    $query       = new Query();
            
    $countquery->select (" count({{%calendar}}.id)")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")            
            ;
        
        
    
    $query->select (" {{%calendar}}.id as id, event_date, eventNote, {{%calendar}}.ref_event, eventStatus, {{%event}}.eventTitle,  {{%calendar}}.ref_org as orgId,
                      {{%contact}}.contactFIO, {{%contact}}.contactDate, {{%contact}}.note, {{%calendar}}.ref_zakaz as zakazId,  userFIO ")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
                             ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")
            ; 

    $query->andFilterWhere(['=', '{{%calendar}}.ref_org', $this->orgId]);    
    $countquery->andFilterWhere(['=', '{{%calendar}}.ref_org', $this->orgId]);    

    $query->andFilterWhere(['=', '{{%calendar}}.eventStatus', 1]);    
    $countquery->andFilterWhere(['=', '{{%calendar}}.eventStatus', 1]);    
       
    
    $count = $countquery->createCommand()->queryScalar();
    $command = $query->createCommand();    
        
        $provider = new SqlDataProvider(
        [   'sql' => $command ->sql, 
            'params' => $command->params,    
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 15,
            ],
            'sort' => [
            'attributes' => [
            'event_date',
            'eventTitle',
            'eventStatus',
            'contactDate',
            'contactFIO',    
            'userFIO',            
            'eventNote',
            'defaultOrder' => [    'event_date' => SORT_DESC ],
            ],
            ],
        ]);
    return $provider;
   }   

/************************************************/
/*********************************************************/
  public function getSdelkiForOrgProvider($params)
   {
  
    $query  = new Query();
    $query->select ([
              '{{%zakaz}}.id as zakazId', 
              'formDate', 
              '{{%zakaz}}.refOrg',                          
              'max({{%contact}}.id) as conatctID', 
              'max({{%calendar}}.id) as eventId',
              '{{%schet}}.schetSumm'
              ])
            ->from("{{%zakaz}}")
            ->leftJoin("{{%schet}}","{{%zakaz}}.id = {{%schet}}.refZakaz")
            ->leftJoin("{{%contact}}","{{%zakaz}}.id = {{%contact}}.refZakaz")
            ->leftJoin("{{%calendar}}","{{%zakaz}}.id = {{%calendar}}.ref_zakaz")  
            ->where ("({{%zakaz}}.isActive = 1 OR {{%schet}}.isSchetActive = 1)")             
            ->groupBy("zakazId");
;

    $countquery  = new Query();
    $countquery->select ("count({{%zakaz}}.id) as zakazId")
            ->from("{{%zakaz}}")
            ->leftJoin("{{%schet}}","{{%zakaz}}.id = {{%schet}}.refZakaz")            
            ->where ("({{%zakaz}}.isActive = 1 OR {{%schet}}.isSchetActive)")             
            ;

    
    $query->andWhere(['=', '{{%zakaz}}.refOrg', $this->orgId]);
    $countquery->andWhere(['=', '{{%zakaz}}.refOrg',  $this->orgId]);     
       
    
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
            'zakazId', 
            'formDate', 
            'schetId', 
            'schetNum', 
            'schetDate', 
            'schetSumm',
            'conatctID', 
            'eventId'
            ],
            'defaultOrder' => [ 'zakazId' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

/*********************************************************/
   
  public function getZakupkiForOrgProvider($params)
   {
  
    $query  = new Query();
    $query->select ([
            '{{%purchase}}.id as purchaseRef',
            '{{%purchase}}.dateCreation',
            '{{%purchase}}.refOrg'
            ])
            ->from("{{%purchase}}")
            
            ;

    $countquery  = new Query();
    $countquery->select ("count({{%purchase}}.id)")
            ->from("{{%purchase}}")            
            ;

    
    $query->andWhere(['=', '{{%purchase}}.refOrg', $this->orgId]);
    $countquery->andWhere(['=', '{{%purchase}}.refOrg',  $this->orgId]);     
       
    
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
            'purchaseRef',
            'dateCreation',            
            ],
            'defaultOrder' => [ 'purchaseRef' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
/*********************************************************/

  public function getOrgGroupProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
             '{{%org_group}}.id',
             'orgGrpTitle'
             ])
            ->from("{{%org_group}}")
            ->distinct()
            ->leftJoin("{{%orglist}}","{{%org_group}}.id = {{%orglist}}.orgGrpRef")
;

     
    if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['Like', 'orgGrpTitle', $this->orgGrpTitle]);    

        $query->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);        
    }

    $command = $query->createCommand(); 
    $count = count($query->createCommand()->queryAll());
    
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
            'orgGrpTitle', 
            ],
            'defaultOrder' => [ 'orgGrpTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/
 /*********************************************************/

  public function getContractListProvider()
   {
   
    $query  = new Query();
    $query->select ([
             '{{%contracts}}.id',
             'creationTime',
             'oplatePeriod',
             'oplateStart',
             'dateEnd',
             'internalNumber',
             'dateStart',
             'predoplata',
             ])
            ->from("{{%contracts}}")
            ->distinct()           
;

    $query->andWhere("{{%contracts}}.refOrg = ".$this->orgId  );

    $command = $query->createCommand(); 
    
    $this->contarctArray =$query->orderBy(['creationTime' => SORT_DESC])->createCommand()->queryAll();  
    $count = count($this->contarctArray);
    
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
            'creationTime',
            'oplatePeriod',
            'oplateStart',
            'dateEnd',
            'internalNumber',
            'dateStart',
            'predoplata',
            ],
            'defaultOrder' => [ 'creationTime' => SORT_DESC ],
            ],
            
        ]);

              
        
    return  $dataProvider;   
   }   
 /*********************************************************/


 /*********************************************************/

  public function getResetContractListProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
             '{{%contracts}}.id',
             'orgINN',
             'orgKPP as fltKPP',
             'creationTime',
             'clientTitle as orgTitle',
             'dateEnd',
             'internalNumber',
             'dateStart'
             ])
            ->from("{{%contracts}}")
            ->distinct()           
    ;
    $countquery  = new Query();
    $countquery->select ([
             'COUNT({{%contracts}}.id)',
             ])
            ->from("{{%contracts}}")
    ;
    
    $query->andWhere("{{%contracts}}.refOrg <> ".$this->orgId  );
    $countquery->andWhere("{{%contracts}}.refOrg <> ".$this->orgId  );
    
    if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['Like', 'orgINN', $this->orgINN]);
        $query->andFilterWhere(['Like', 'orgKPP', $this->fltKPP]);        
        $query->andFilterWhere(['Like', 'clientTitle', $this->orgTitle]);        
        
        
        $countquery->andFilterWhere(['Like', 'orgINN', $this->orgINN]);
        $countquery->andFilterWhere(['Like', 'orgKPP', $this->orgKPP]);        
        $countquery->andFilterWhere(['Like', 'clientTitle', $this->orgTitle]);        
        
        
    }
    
    
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();  
    
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
             'orgINN',
             'fltKPP',
             'creationTime',
             'clientTitle as orgTitle',
             'dateEnd',
            ],
            'defaultOrder' => [ 'creationTime' => SORT_DESC ],
            ],
            
        ]);

              
        
    return  $dataProvider;   
   }   
 /*********************************************************/
 /*********************************************************/

  public function getOrgDublicateProvider($params)
   {
   
   $strSql = "(SELECT DISTINCT isOrgActive, orgGrpRef, o1.id, o1.title, o1.orgINN, o1.orgKPP, p1.phone, e2.email 
               from {{%orglist}} as o1
               left join {{%phones}} as p1 on p1.ref_org = o1.id 
               left join {{%emaillist}} as e2 on  e2.ref_org = o1.id 
               where
               (ifnull(p1.phone,'') <> '' or ifnull(e2.email,'') <> '')
               And o1.id <> :orgId               
               ) as b";

   
    $query  = new Query();
    $query->select ([
             'b.id as orgRef', 
             'b.orgGrpRef as orgGrpRef',
             'b.title as orgTitle', 
             'b.orgINN', 
             'b.orgKPP',
             'b.isOrgActive'
             ])
            ->from("{{%orglist}} as a")
            ->leftJoin("{{%phones}} as p", "p.ref_org = a.id")
            ->leftJoin("{{%emaillist}} as e", "e.ref_org = a.id")
            ->leftJoin($strSql , "(b.orgINN=a.orgINN  OR b.title=a.title or b.phone=p.phone or b.email = e.email)" )
            ->addParams([':orgId' => $this->orgId]) 
            ->distinct()           
    ;
    $countquery  = new Query();
    $countquery->select ([
             'COUNT(DISTINCT(b.id))',
             ])
            ->from("{{%orglist}} as a")
            ->leftJoin("{{%phones}} as p", "p.ref_org = a.id")
            ->leftJoin("{{%emaillist}} as e", "e.ref_org = a.id")
            ->leftJoin($strSql , "(b.orgINN=a.orgINN  OR b.title=a.title or b.phone=p.phone or b.email = e.email)" )
            ->addParams([':orgId' => $this->orgId]) 
            ->distinct()           
    ;
    
         $query->andWhere("b.title <> '' ");
    $countquery->andWhere("b.title <> '' ");
    
         $query->andWhere("a.id = ".$this->orgId  );
    $countquery->andWhere("a.id = ".$this->orgId  );
    
    if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['Like', 'b.orgINN', $this->orgINN]);
        $query->andFilterWhere(['Like', 'b.orgKPP', $this->fltKPP]);        
        $query->andFilterWhere(['Like', 'b.title', $this->orgTitle]);        
        
        
        $countquery->andFilterWhere(['Like', 'b.orgINN', $this->orgINN]);
        $countquery->andFilterWhere(['Like', 'b.orgKPP', $this->orgKPP]);        
        $countquery->andFilterWhere(['Like', 'b.title', $this->orgTitle]);        
    }
    
    
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();  
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'orgRef', 
             'orgTitle', 
             'orgINN', 
             'orgKPP'
            ],
            'defaultOrder' => [ 'orgRef' => SORT_ASC ],
            ],
            
        ]);

              
        
    return  $dataProvider;   
   }    

 /*********************************************************/
 /*********************************************************/
 public function getDblGisProvider()
 {

    $query  = new Query();
    $query->select ([
        '{{%org_dbl_gis}}.id',   
        'dblGisLabel',   
        'isDefault',   
        ])
       ->from("{{%org_dbl_gis}}");
            
    $countquery  = new Query();
    $countquery->select ("count({{%org_dbl_gis}}.id)")
            ->from("{{%org_dbl_gis}}");            
     
    $query->andWhere(['=', 'refOrg', $this->orgId]);
    $countquery->andWhere(['=', 'refOrg', $this->orgId]);     

  /*   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['=', 'orgBank', $this->orgBank]);
       $countquery->andFilterWhere(['=', 'orgBank', $this->orgBank]);                 
     }*/

   
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
 

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
                        
            'attributes' => [    
            'id',            
            'dblGisLabel',   
            'isDefault',               
            ],            
            
            'defaultOrder' => [  'id' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }  
                        
                        
/**********************/    
 public function getOkvedProvider()
 {

    $query  = new Query();
    $query->select ([
        '{{%org_okved}}.id',   
        'OKVED',   
        'isDefault',   
        ])
       ->from("{{%org_okved}}");
            
    $countquery  = new Query();
    $countquery->select ("count({{%org_okved}}.id)")
            ->from("{{%org_okved}}");            
     
    $query->andWhere(['=', 'refOrg', $this->orgId]);
    $countquery->andWhere(['=', 'refOrg', $this->orgId]);     

  /*   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['=', 'orgBank', $this->orgBank]);
       $countquery->andFilterWhere(['=', 'orgBank', $this->orgBank]);                 
     }*/

   
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
 

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
                        
            'attributes' => [  
            'id',            
            'OKVED',   
            'isDefault',               
            ],            
            
            'defaultOrder' => [  'id' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }  
                        
                        
/**********************/    

  
 public function getAccountsProvider()
 {

    $query  = new Query();
    $query->select ([
        '{{%org_accounts}}.id',   
        'orgBIK',   
        'orgBank',   
        'orgRS',   
        'orgKS',   
        'isActive',   
        'isDefault',   
        'flgKS',
        ])
         ->from("{{%org_accounts}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%org_accounts}}.id)")
            ->from("{{%org_accounts}}")
            ;            
     
        $query->andWhere(['=', 'refOrg', $this->orgId]);
        $countquery->andWhere(['=', 'refOrg', $this->orgId]);     

  /*   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['=', 'orgBank', $this->orgBank]);
       $countquery->andFilterWhere(['=', 'orgBank', $this->orgBank]);                 
     }*/

   
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
 

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'orgBank',   
            'orgRS',   
            'isActive',   
            'isDefault',               
            ],            
            
            'defaultOrder' => [  'orgRS' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }  
                        
                        
/**********************/    

   public function  savePhoneDetail ()
   {
    $res = ['res' => false, 'dataRequestId' => $this->dataRequestId, 'dataType' => $this->dataType, 'val' => $this->dataVal];   
    
        $record = PhoneList::findOne($this->dataRequestId);
        if (empty($record)) return $res;
        
        switch ($this->dataType)
        {
          case 'phone':
              $record->phone = $this->dataVal; 
              $record->save();              
              $val = $record->phone;
          break;
          case 'phoneContactFIO':
              $record->phoneContactFIO = $this->dataVal; 
              $record->save();              
              $val = $record->phoneContactFIO;
          break;
        
        }
        $record->save();

     $res['val'] = $val;              
     $res['res'] = true;
     return $res;
   }   
/***************/
   public function  saveDetail ()
   {
    $res = ['res' => false, 
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType, 
            'isSwitch' =>0,
            'val' => $this->dataVal
           ];   
    
        
        switch ($this->dataType)
        {            
          case 'dostavkaAdd':         
              $record = new TblOrgDostavka();              
              if (empty($record)) return $res;
              $record->refOrg = intval($this->dataRequestId); 
              $record->save();              
              $val = $record->id;
          break;

          case 'dostavkaNote':                       
              $record = TblOrgDostavka::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->note = $this->dataVal; 
              $record->save();              
              $val = $record->note ;
          break;

          case 'dostavkaDel':                       
              $record = TblOrgDostavka::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;             
              $record->delete();              
              $val =0;
          break;

          case 'isDostavkaDefault':                       
              $record = TblOrgDostavka::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              if ($record->isDefault == 1) $record->isDefault =0; 
                                      else $record->isDefault =1; 
              $record->save();              
              $val =  $record->isDefault;
                $res['isSwitch'] = 1;
          break;
            
            
            
            
            
          case 'orgBank':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->orgBank = mb_substr($this->dataVal,0,150,'utf-8'); 
              $record->save();              
              $val = $record->orgBank;
          break;

          case 'orgRS':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->orgRS = mb_substr($this->dataVal,0,50,'utf-8'); 
              $record->save();              
              $val = $record->orgRS;
          break;

          case 'orgKS':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->orgKS = mb_substr($this->dataVal,0,50,'utf-8'); 
              $record->save();              
              $val = $record->orgKS;
          break;

          case 'flgKS':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              if ($record->flgKS == 1) $record->flgKS = 0;
                                  else $record->flgKS = 1;                
              $record->save();              
              $res['isSwitch'] = 1;
              $val = $record->flgKS;
          break;
          
          
          case 'orgBIK':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->orgBIK = mb_substr($this->dataVal,0,20,'utf-8'); 
              $record->save();              
              $val = $record->orgBIK;
          break;

          case 'isActive':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              if ($record->isActive == 1) $record->isActive = 0; 
              else $record->isActive = 1; 
              $record->save();              
              $res['isSwitch'] = 1;
              $val = $record->isActive;
          break;

          case 'accountDel':
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              $record->delete();              
              $res['isSwitch'] = 0;
              $val = 0;
          break;

          case 'isDefault':              
              $record = TblOrgAccounts::findOne(intval($this->dataRequestId));
              if (empty($record)) return $res;
              if ($record->isDefault == 1) $record->isDefault = 0; 
              else { $record->isDefault = 1; 
              $record->save();              
              $strSql = "UPDATE {{%org_accounts}} set isDefault=0
              where refOrg =:refOrg AND id <> :id"; 
              Yii::$app->db->createCommand($strSql,
                [    
                    ':refOrg' => $record->refOrg,
                    ':id' => $record->id,
              ])->execute(); }
              $res['isSwitch'] = 1;
              $val = $record->isDefault;
          break;
          case 'phone':
              $record = PhoneList::findOne($this->dataRequestId);
              if (empty($record)) return $res;              
              $record->phone = $this->dataVal;               
              $val = $record->phone;
              $record->save();
          break;
          case 'phoneContactFIO':
              $record = PhoneList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->phoneContactFIO = $this->dataVal; 
              $record->save();              
              $val = $record->phoneContactFIO;
          break;

          case 'phoneStatus':
              $record = PhoneList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              if ($record->status == 2) $record->status = 0; 
              else                      $record->status = 2; 
              $record->save();              
              $val = $record->status;
          break;
          
          case 'isDefaultPhone':
              $record = PhoneList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              Yii::$app->db->createCommand( 'UPDATE {{%phones}}
              SET isDefault = 0  where  ref_org=:refOrg', 
              [':refOrg' => $record->ref_org])->execute(); //Сбросим                                                   
              $record->isDefault = 1; 
              $record->save();              
              $val = $record->isDefault;
          break;
          
          
          case 'phoneDel':
              $record = PhoneList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->delete();              
              $val=0;
          break;
          
          case 'area':
              $record = AdressList::findOne($this->dataRequestId);
              $record->area = $this->dataVal; 
              $record->save();              
              $val = $record->area;
          break;

          case 'city':
              $record = AdressList::findOne($this->dataRequestId);
              $record->city = $this->dataVal; 
              $record->save();              
              $val = $record->city;
          break;
          
          case 'district':
              $record = AdressList::findOne($this->dataRequestId);
              $record->district = $this->dataVal; 
              $record->save();              
              $val = $record->district;
          break;  

          case 'index':
              $record = AdressList::findOne($this->dataRequestId);
              $record->index = $this->dataVal; 
              $record->save();              
              $val = $record->index;
          break;  
                    
          case 'adress':
              $record = AdressList::findOne($this->dataRequestId);
              $record->adress = $this->dataVal; 
              $record->save();              
              $val = $record->adress;
          break;  

          case 'streetAdres':
              $record = AdressList::findOne($this->dataRequestId);
              $record->streetAdres = $this->dataVal; 
              $record->save();              
              $val = $record->streetAdres;
          break;  
          
          

          case 'isBadAdress':
              $record = AdressList::findOne($this->dataRequestId);
              if ($record->isBad == 1) $record->isBad = 0; 
                                 else  $record->isBad = 1; 
              $record->save();              
              $val = $record->isBad;
          break;  

          case 'isOfficialAdress':
              $record = AdressList::findOne($this->dataRequestId);              
              Yii::$app->db->createCommand( 'UPDATE {{%adreslist}}
              SET isOfficial = 0  where  ref_org=:refOrg', 
              [':refOrg' => $record->ref_org])->execute(); //Сбросим                                                   
              $record->isOfficial = 1; 
              $record->save();              
              $val = $record->isOfficial;
          break;  



          case 'adressDel':
              $record = AdressList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->delete();              
              $val=0;
          break;
         
         
          case 'emailContactFIO':
              $record = EmailList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->emailContactFIO = $this->dataVal; 
              $record->save();              
              $val = $record->emailContactFIO;
          break;
          case 'email':
              $record = EmailList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->email = $this->dataVal; 
              $record->save();              
              $val = $record->email;
          break;

          case 'isBadEmail':
              $record = EmailList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              if ($record->status == 2) $record->status = 0; 
              else                      $record->status = 2; 
              $record->save();              
              $val = $record->status;
          break;

          case 'isDefaultEmail':
              $record = EmailList::findOne($this->dataRequestId);
              if (empty($record)) return $res; 
              Yii::$app->db->createCommand( 'UPDATE {{%emaillist}}
              SET isDefault = 0  where  ref_org=:refOrg', 
              [':refOrg' => $record->ref_org])->execute(); //Сбросим                                     
              $record->isDefault = 1; 
              $record->save();              
              $val = $record->isDefault;
          break;
          
          
          
          case 'emailDel':
              $record = EmailList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->delete();              
              $val=0;
          break;


          case 'url':
              $record = UrlList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->url = $this->dataVal; 
              $record->save();              
              $val = $record->url;
          break;

          case 'isBadUrl':
              $record = UrlList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              if ($record->isBad == 1) $record->isBad = 0; 
              else                      $record->isBad = 1; 
              $record->save();              
              $val = $record->isBad;
          break;
          case 'urlDel':
              $record = UrlList::findOne($this->dataRequestId);
              if (empty($record)) return $res;                        
              $record->delete();              
              $val=0;
          break;
          
          case 'dblGisLabel':
              $record = TblOrgDblGis::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              $record->dblGisLabel = $this->dataVal; 
              $record->save();              
              $val=$record->dblGisLabel;
          break;
          
          case 'isdblGisDefault':
              $record = TblOrgDblGis::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              if ($record->isDefault == 1)  $record->isDefault =0; 
              else $record->isDefault = 1;
              $record->save();              
              $val=$record->isDefault;
              $res['isSwitch'] = 1;
          break;
          
          case 'dblGisDel':
              $record = TblOrgDblGis::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              $record->delete();              
              $val=0;
          break;
          

          case 'OKVED':
              $record = TblOrgOkved::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              $record->OKVED = $this->dataVal; 
              $record->save();              
              $val=$record->OKVED;
          break;
          
          case 'isOkvedDefault':
              $record = TblOrgOkved::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              if ($record->isDefault == 1)  $record->isDefault =0; 
              else $record->isDefault = 1;
              $record->save();              
              $val=$record->isDefault;
              $res['isSwitch'] = 1;
          break;
          
          case 'okvedDel':
              $record = TblOrgOkved::findOne($this->dataRequestId);
              if (empty($record)) return $res;    
              $record->delete();              
              $val=0;
          break;



          default:
              return $res;     
          break;              
        }

     $res['val'] = $val;              
     $res['res'] = true;
     return $res;
   }   
   
   
/***************/
   public function addNewDblGis($orgRef)
   {
     $record = new TblOrgDblGis();
     if (empty($record)) return false;
     $record->refOrg = $orgRef;  
     $record->dblGisLabel = 'новый';
     $record->save();
     return true;    
   }

/***************/
   public function addNewOkved($orgRef)
    {
     $record = new TblOrgOkved();
     if (empty($record)) return false;
     $record->refOrg = $orgRef;  
     $record->OKVED = 'новый';
     $record->save();
     return true;    
   }
/***************/
   public function addNewPhone($orgRef)
   {
     $record = new PhoneList();
     if (empty($record)) return false;
     $record->ref_org = $orgRef;  
     $record->phone = 'новый';
     $record->save();
     return true;    
   }
/***************/
   public function addNewAccount($orgRef)
   {
     $record = new TblOrgAccounts();
     if (empty($record)) return false;
     $record->refOrg = $orgRef;  
     $record->orgRS = 'новый счет';
     $record->save();
     return true;    
   }
/***************/
   public function addNewAdress($orgRef)
   {
     $record = new AdressList();
     if (empty($record)) return false;
     $record->ref_org = $orgRef;  
     $record->adress = 'новый адрес';
     $record->save();
     return true;    
   }
/***************/
   public function addNewEmail($orgRef)
   {
     $record = new EmailList();
     if (empty($record)) return false;
     $record->ref_org = $orgRef;  
     $record->email = 'новый адрес';
     $record->save();
     return true;    
   }
/***************/
/***************/
   public function addNewUrl($orgRef)
   {
     $record = new UrlList();
     if (empty($record)) return false;
     $record->ref_org = $orgRef;  
     $record->url = 'новый адрес';
     $record->save();
     return true;    
   }
/***************/
/*********************************************************/
/*********************************************************/
public $dataArray=[];
 public function prepareOrgsClientData($params, $refOrg)
   {

     $query  = new Query();
	 $querySub1  = new Query();
	 $querySub2  = new Query();
                       
     $query->select([ 
     '{{%zakaz}}.id as zakazNum',
     '{{%zakaz}}.formDate as zakazDate',
     '{{%zakaz}}.isActive as zakazIsActive',
     '{{%zakaz}}.isFormed as zakazIsFormed',
     '{{%schet}}.schetNum', 
     '{{%schet}}.schetDate', 
     '{{%schet}}.schetSumm',  
     '{{%schet}}.refOrg', 
     '{{%schet}}.ref1C',
     '{{%schet}}.isReject',
     'credit', 'creditDate', 
     'debet', 'debetDate', 
     '{{%schet}}.id as refSchet' ]) 
        ->from("{{%zakaz}}")
		->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakaz}}.id")
        ->leftJoin('(select ifnull(sum(oplateSumm),0) as credit, max(oplateDate) as creditDate,refSchet from  {{%oplata}} group by refSchet) as a', 'a.refSchet= {{%schet}}.id')
        ->leftJoin('(select ifnull(sum(supplySumm),0) as debet, max(supplyDate) as debetDate,refSchet from  {{%supply}} group by refSchet) as b', 'b.refSchet= {{%schet}}.id');
	$query->andWhere(['=', '{{%schet}}.refOrg', $refOrg]);  
	
	
    $querySub1->select([ 'schetNum', 'schetDate', 'refOrg', 'sum(oplateSumm) as credit', 'oplateDate  as creditDate', 
     'refSchet' ]) 
	->from("{{%oplata}}")
	 ->groupBy(['schetNum', 'schetDate', 'refOrg',  'oplateDate', 'refSchet']);	
	 
	$querySub1->andWhere(['=', 'refOrg', $refOrg]);  
	$querySub1->andWhere(['=', 'refSchet', 0]);  
	
	
	
	$querySub2->select([ 'schetNum', 'schetDate', 'refOrg', 'sum(supplySumm) as debet', 'supplyDate  as debetDate', 'refSchet' ]) 
		->from("{{%supply}}")  
		->groupBy(['schetNum', 'schetDate', 'refOrg', 'supplyDate', 'refSchet']);
	$querySub2->andWhere(['=', 'refOrg', $refOrg]);  
	$querySub2->andWhere(['=', 'refSchet', 0]);  



     if (($this->load($params) && $this->validate())) 
     {
     }

	$this->dataArray = $query->createCommand()->queryAll();  
  	for ($i=0; $i< count($this->dataArray); $i++)
	{
        $this->dataArray[$i]['operation']="Продажа";
    }
    
	$list=$querySub1->createCommand()->queryAll();  
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Продажа";
		$list[$i]['supplyDate']="";
		$list[$i]['debet']=0;
		$this->dataArray[]=$list[$i];
	}	

	$list=$querySub2->createCommand()->queryAll();  	
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Продажа";
		$list[$i]['oplateDate']="";
		$list[$i]['credit']=0;
		$this->dataArray[]=$list[$i];
	}	


  }
/************/   
   public function getOrgsClientData ($params, $orgId)		
   {
        $this->prepareOrgsClientData($params, $orgId);		

        $dataList=$this->dataArray;

    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));     
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Менеджер"), 

        iconv("UTF-8", "Windows-1251","Оплаты"),
        iconv("UTF-8", "Windows-1251","Отгрузки"),
        iconv("UTF-8", "Windows-1251","Сверка"), 

        iconv("UTF-8", "Windows-1251","Дата оплаты"),
        iconv("UTF-8", "Windows-1251","Дата отгрузки"), 
		
        );
        fputcsv($fp, $col_title, ";"); 
    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),
		
        iconv("UTF-8", "Windows-1251",$dataList[$i]['oplata']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['supply']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['balance']),    		

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastOplate']))), 
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastSupply']))), 

        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
   public function getOrgsClientProvider($params, $orgId)		
   {

        $this->prepareOrgsClientData($params, $orgId);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'zakazNum',
            'zakazDate',
            'zakazIsActive',
            'zakazIsFormed',
			'schetNum', 
			'schetDate', 
			'refOrg', 
			'credit', 
			'creditDate', 
			'debet',
			'debetDate', 
			'refSchet'
            ],
			
            'defaultOrder' => [    'schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

 public function prepareOrgsSupplierData($params, $refOrg)
   {

     
     $querySupplier  = new Query();
	 $querySupplierSub1  = new Query();
	 $querySupplierSub2  = new Query();


     $querySupplier->select([ 
     '{{%purchase}}.id as refPurchase',
     'dateCreation',
     '{{%supplier_schet_header}}.schetNum', 
     '{{%supplier_schet_header}}.schetDate',      
     '{{%supplier_schet_header}}.id as refSchet', 
     'supplierRef1C',
     'sum(oplateSumm) as debet',      
     'sum(wareSumm) as credit',           
     ])
    ->from("{{%purchase}}")          
    ->leftJoin ('{{%purch_schet_lnk}}', '{{%purch_schet_lnk}}.purchRef={{%purchase}}.id')
    ->leftJoin ('{{%supplier_schet_header}}','{{%supplier_schet_header}}.id={{%purch_schet_lnk}}.schetRef')
    ->leftJoin ('{{%supplier_oplata}}','{{%supplier_schet_header}}.id={{%supplier_oplata}}.supplierSchetRef')
    ->leftJoin ('{{%supplier_wares}}','{{%supplier_schet_header}}.id={{%supplier_wares}}.supplierSchetRef')
    ->groupBy (['{{%purchase}}.id','{{%supplier_schet_header}}.schetNum'])
    ;
    $querySupplier->andWhere("{{%supplier_schet_header}}.refOrg = ".$refOrg);
	   	
   	$this->dataArray=$querySupplier->createCommand()->queryAll();  	
	for ($i=0; $i< count($this->dataArray); $i++)
	{
        $this->dataArray[$i]['operation']="Закупка";
	}	


    $querySupplierSub1->select([ 'sdelkaNum as schetNum', 'sdelkaDate as schetDate', 'refOrg', 'sum(oplateSumm) as debet', 'oplateDate  as debetDate', 
     'supplierSchetRef as refSchet' ]) 
	->from("{{%supplier_oplata}}")
	 ->groupBy(['schetNum', 'schetDate', 'refOrg',  'oplateDate', 'supplierSchetRef']);	
	 
	$querySupplierSub1->andWhere(['=', 'refOrg', $refOrg]);  
	$querySupplierSub1->andWhere(['=', 'supplierSchetRef', 0]);  
	
	
	$querySupplierSub2->select([ 'requestNum as schetNum', 'requestDate as schetDate', 'refOrg', 'sum(wareSumm) as credit', 'requestDate  as creditDate', 'supplierSchetRef as refSchet' ]) 
		->from("{{%supplier_wares}}")  
		->groupBy(['requestNum', 'requestDate', 'refOrg', 'requestDate', 'supplierSchetRef']);
	$querySupplierSub2->andWhere(['=', 'refOrg', $refOrg]);  
	$querySupplierSub2->andWhere(['=', 'supplierSchetRef', 0]);  


    $list=$querySupplierSub1->createCommand()->queryAll();  
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Приход";
		$list[$i]['supplyDate']="";
		$list[$i]['credit']=0;
		$this->dataArray[]=$list[$i];
	}	

	$list=$querySupplierSub2->createCommand()->queryAll();  	
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Оплата";
		$list[$i]['oplateDate']="";
		$list[$i]['debet']=0;
		$this->dataArray[]=$list[$i];
	}	
	
  }
/************/   
   public function getOrgsSupplierData ($params, $orgId)		
   {
        $this->prepareOrgsSupplierData($params, $orgId);		

        $dataList=$this->dataArray;

    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));     
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Менеджер"), 

        iconv("UTF-8", "Windows-1251","Оплаты"),
        iconv("UTF-8", "Windows-1251","Отгрузки"),
        iconv("UTF-8", "Windows-1251","Сверка"), 

        iconv("UTF-8", "Windows-1251","Дата оплаты"),
        iconv("UTF-8", "Windows-1251","Дата отгрузки"), 
		
        );
        fputcsv($fp, $col_title, ";"); 
    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),
		
        iconv("UTF-8", "Windows-1251",$dataList[$i]['oplata']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['supply']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['balance']),    		

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastOplate']))), 
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastSupply']))), 

        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
   public function getOrgsSupplierProvider($params, $orgId)		
   {

        $this->prepareOrgsSupplierData($params, $orgId);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'refPurchase',
            'schetNum', 
            'schetDate',                  
            'debet',      
            'credit',  
            'dateCreation',            
            ],
			
            'defaultOrder' => [    'schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

public function getDostavkaProvider($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%org_dostavka}}.id',   
        'isDefault',
        'note',   
        ])
         ->from("{{%org_dostavka}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%org_dostavka}}.id)")
            ->from("{{%org_dostavka}}")
            ;            
     
        $query->andWhere(['=', 'refOrg', $this->orgId]);
        $countquery->andWhere(['=', 'refOrg', $this->orgId]);     

     if (($this->load($params) && $this->validate())) {
      /* $query->andFilterWhere(['=', 'orgBank', $this->orgBank]);
       $countquery->andFilterWhere(['=', 'orgBank', $this->orgBank]);                 */
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
            'isDefault' ,   
            'id',   
            ],            
            
            'defaultOrder' => [  'id' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }  


/*********************************************************/
 public function getLastSync()
 {
 $lastSync =	 Yii::$app->db->createCommand('SELECT MAX(syncTime) FROM {{%tmp_reestr}}')
     ->queryScalar(); 

 return $lastSync;
}
public $command;
public $count;


 public function getOrgReestrErr()
 {
 $err =	 Yii::$app->db->createCommand("SELECT COUNT(DISTINCT a.id) FROM {{%orglist}} as a 
     where ( (select Count(id) from {{%org_deals}} where ({{%org_deals}}.refOrg = a.id)) = 0
     OR  ifnull(schetINN,'') = '' OR  ifnull(orgKPP,'') = '' OR ifnull(contactPhone,'') = '')
     AND isReject = 0 AND isOrgActive = 1
     ")
     ->queryScalar(); 

 return $err;
}
 
 public function getOrgInactive()
 {
 $err =	 Yii::$app->db->createCommand("SELECT COUNT(DISTINCT a.id) FROM {{%orglist}} as a 
     WHERE isReject = 1 OR isOrgActive = 0
     ")
     ->queryScalar(); 

 return $err;
}
 
 public function  getAllActiveOrg ()
 {
 $all =	 Yii::$app->db->createCommand("SELECT COUNT(DISTINCT a.id) FROM {{%orglist}} as a 
     WHERE isReject = 0 And isOrgActive = 1
     ")
     ->queryScalar(); 

 return $all;
}
 
 public function prepareOrgReestr($params)
 {
         
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов с которыми были финансовые взаимоотношения */
	
    $countquery->select ("count(distinct {{%orglist}}.id)")
                    ->from("{{%orglist}}")
                    ->leftJoin("{{%tmp_reestr}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")                          
     ;
     
     $query->select([
        '{{%orglist}}.id as refOrg',
        '{{%orglist}}.title as orgTitle',
        '{{%orglist}}.orgINN',        
        '{{%orglist}}.orgKPP',        
        '{{%orglist}}.isOrgActive',        
        '{{%orglist}}.isReject',        
        'managerFIO',
        'mainActivity',
        'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'regState',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastActiveSchet',
        'lastSdelka',
        'lastContact',
        'lastZakaz',      
        'lastActiveZakaz',
        '{{%orglist}}.contactPhone',
        '{{%orglist}}.contactEmail',
        '{{%orglist}}.contactFIO',
        
	 ]) ->from("{{%orglist}}")
        ->leftJoin("{{%tmp_reestr}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")                
        ->distinct()
        ;
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
          
        $query->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);
        $countquery->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);

        $query->andFilterWhere(['like', 'contactEmail', $this->contactEmail]);
        $countquery->andFilterWhere(['like', 'contactEmail', $this->contactEmail]);
     
        $query->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);
        $countquery->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);
        
        
     }

    
      if (!empty($this->fltOrgDeal) )
      {
         if ($this->fltOrgDeal == -1)
         {
            $strWhere ="(select Count({{%org_deals}}.id) from {{%org_deals}} where 
            {{%org_deals}}.refOrg = {{%orglist}}.id and state =1) =0  ";         
            $query->andWhere($strWhere);
            $countquery->andWhere($strWhere);         
         }             
         else {   
            $strWhere ="(select Count({{%org_deals}}.id) from {{%org_deals}}, {{%bank_op_article}} where 
            {{%org_deals}}.articleRef= {{%bank_op_article}}.id
            AND {{%bank_op_article}}.grpRef = ".$this->fltOrgDeal." and state =1
            And {{%org_deals}}.refOrg = {{%orglist}}.id) >0";         
            $query->andWhere($strWhere);
            $countquery->andWhere($strWhere);         
         }
      }          
    
     if ($this->detail == 1)
     {
         $strWhere ="(select Count({{%org_deals}}.id) from {{%org_deals}} where {{%org_deals}}.refOrg = {{%orglist}}.id) =0
                     OR ifnull(schetINN,'') = '' OR  ifnull(orgKPP,'') = '' OR ifnull(contactPhone,'') = ''";
         
        $query->andWhere($strWhere);
        $countquery->andWhere($strWhere);         
     }
     
     if ($this->detail != 2)
     {
        $query->andWhere('isReject =0');
        $countquery->andWhere('isReject =0');         

        $query->andWhere('isOrgActive =1');
        $countquery->andWhere('isOrgActive =1');                  
     }else
     {
             $query->andWhere('isReject =1 OR isOrgActive = 0');
        $countquery->andWhere('isReject =1 OR isOrgActive = 0');         

         
     }
     
//$this->debug = $query->createCommand()->getRawSql();     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();
       //echo $countquery->createCommand()->getRawSql(0);
 }
 /****************************************************************************************/
   public function getOrgReestrProvider($params)
   {

        $this->prepareOrgReestr($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
     
        'orgTitle',
        'avgCheck',
        'balance',
        'regular',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastSdelka',
        'lastContact',
        'contactPhone',
        'contactEmail',
        'contactFIO',

            ],
            'defaultOrder' => [    'orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
 

/*********************************************************/
/*********************************************************/

}


