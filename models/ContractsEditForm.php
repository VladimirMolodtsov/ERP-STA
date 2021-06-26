<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;

use app\models\TblContracts;
use app\models\TblOrgRekvezit;
use app\models\OrgList;
use app\models\TblOrgAccounts;
use app\models\AdressList;
/**
 * ContractsEditForm  - модель 
 */


class ContractsEditForm extends Model
{
   public $id="";

  public $mode=0;

  public $creationTime;    //` DATETIME DEFAULT NULL,
  public $clientTitle;     //` VARCHAR(500) COLLATE utf8_general_ci DEFAULT NULL,
  
  public $clientAdress;    
  public $clientAdressRef=0;
      
  public $orgINN;          //` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  public $orgKPP;          //` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  
  public $bankRekvesits;   //` TEXT COLLATE utf8_general_ci,
  public $bankRef=0;  
  
  public $contactorFull;   
  public $contractorShort; 
  public $contractorPost;  
  public $contractorReason;
  
  public $userFormer;
  public $userRef;          
  
  public $isDopCondition;
  
  public $oplatePeriod = 5;   
  public $oplateStart = 'счёта';     
  public $dopCondition;    //` VARCHAR(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'при условии',
  
  public $dateStart;       //` DATE DEFAULT NULL,
  public $dateEnd;         //` DATE NOT NULL,
  
  public $phonesList;      
  public $email;           
  public $siteUrl;         
  
  public $predoplata = 100;      //` DOUBLE DEFAULT NULL COMMENT 'предоплата в %',
  public $docUrl;          //` VARCHAR(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ссылка на документ',
  public $internalNumber;  //` INTEGER(11) DEFAULT NULL COMMENT 'Номер',
  public $contractNumber;
  public $refOrg;          //` BIGINT(20) DEFAULT 0,
  
  public $postoplate; 
  public $isPostPplate = 0;

   
   
    public function rules()
    {
        return [
            [['id', 'creationTime', 'clientTitle', 'clientAdress', 'orgINN', 'orgKPP', 'bankRekvesits', 'contactorFull', 
            'contractorShort', 'contractorPost', 'contractorReason', 'oplatePeriod', 'oplateStart', 'dopCondition', 
             'userFormer', 'dateEnd', 'phonesList', 'email', 'siteUrl', 'dateStart', 'predoplata', 'docUrl', 'internalNumber', 'postoplate', 
             'bankRef', 'clientAdressRef', 'isDopCondition', 'isPostPplate', 'contractNumber',
              ], 'default'],
            ['id', 'integer'],   
            
        ];
    }
   

   public function loadOrgData()
   {
   
     $curUser=Yii::$app->user->identity;  
     $this->userRef=$curUser->id;
     $this->userFormer=$curUser->userFIO;
     $this->creationTime = date('Y-m-d H:i');
   
     $this->refOrg = intval($this->refOrg);
   
     $ret = [
     'res' => false,
     'err' => '',
     'id'  => 0,
     'refOrg' => $this->refOrg,
     ];
   
      if (empty($this->refOrg) ) {
         $ret['err'] = "OrgNotSet";
         return $ret;
      }
      
      $orgRecord=OrgList::findOne($this->refOrg);
      if (empty($orgRecord) ) {
         $ret['err'] = "OrgNotFound";
         return $ret;
      }
     
     if(!empty($orgRecord->shortTitle))
         $this->clientTitle = $orgRecord->shortTitle;  
     else
         $this->clientTitle = $orgRecord->orgFullTitle;  
         
     $this->orgINN  = $orgRecord->orgINN; 
     $this->orgKPP  = $orgRecord->orgKPP; 
     
     
     $strSql  = "SELECT DISTINCT phone,phoneContactFIO from {{%phones}}";
     $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC";                                 
     $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $orgRecord->id,])->queryAll();     
      $N = min(count($resList),2);
      $this->phonesList  = "";
      
       for ($i=0; $i<$N; $i++){
         $this->phonesList  .=  $resList[0]['phone']; 
         if ($i<($N-1)) $this->phonesList.=", ";
      }     

     
      $strSql  = "SELECT DISTINCT email,emailContactFIO from {{%emaillist}}";
      $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $orgRecord->id,])->queryAll();                                        
      $N = count($resList);
      $this->email="";
      if ($N >0){
        $this->email = $resList[0]['email']; 
      }       
       
       
      $rekvRecord= TblOrgRekvezit::findOne(['refOrg'=> $this->refOrg]);
      if (!empty ($rekvRecord)){
        $this->contactorFull= $rekvRecord->dogHead." ".$rekvRecord->dogFIO;
        $this->contractorShort= $rekvRecord->signFIO;
        $this->contractorPost= $rekvRecord->signHead;
        $this->contractorReason= $rekvRecord->dogBase;
      }
   
      $bankRecord= TblOrgAccounts::findOne(['refOrg'=> $this->refOrg, 'isDefault' => 1]); 
      if (empty ($bankRecord)) $bankRecord= TblOrgAccounts::findOne(['refOrg'=> $this->refOrg, 'isActive' => 1]); 
      if (!empty ($bankRecord)){
      //р/с 40602810850340111985, к/с 30101810600000000608, Дальневосточный банк ОАО " Сбербанк России" г.Хабаровск, БИК 040813608
        $this->bankRekvesits  = "р/с ".$bankRecord->orgRS;
        $this->bankRekvesits .= ", к/с ".$bankRecord->orgKS;
        $this->bankRekvesits .= ", ".$bankRecord->orgBank;
        $this->bankRekvesits .= ", БИК ".$bankRecord->orgBIK;        
        $this->bankRef = $bankRecord->id;        
      }

      $adressRecord= AdressList::findOne(['ref_org'=> $this->refOrg, 'isOfficial' => 1]); 
      if (empty ($adressRecord)) $adressRecord= AdressList::findOne(['ref_org'=> $this->refOrg, 'isBad' => 0]); 
      if (!empty ($adressRecord)){
      //р/с 40602810850340111985, к/с 30101810600000000608, Дальневосточный банк ОАО " Сбербанк России" г.Хабаровск, БИК 040813608
        $this->clientAdress  = $adressRecord->adress;
        $this->clientAdressRef = $adressRecord->id;        
      }

   }
      
   public function loadData ()
   {
     
     if(empty($this->id)) return;
     $record=TblContracts::findOne($this->id);
     if(empty($record)) return;

     
     
          
     
     $this->creationTime = $record->creationTime ;
     $this->clientTitle = $record->clientTitle ;
     $this->clientAdress = $record->clientAdress ;
     $this->orgINN = $record->orgINN ;
     $this->orgKPP = $record->orgKPP ;
     $this->bankRekvesits = $record->bankRekvesits ;
     $this->contactorFull = $record->contactorFull ;
     $this->contractorShort = $record->contractorShort ;
     $this->contractorPost = $record->contractorPost ;
     $this->contractorReason = $record->contractorReason ;
     $this->oplatePeriod = $record->oplatePeriod ;
     $this->oplateStart = $record->oplateStart ;
     $this->dopCondition = $record->dopCondition ;
     $this->userFormer = $record->userFormer ;
     $this->dateEnd = $record->dateEnd ;
     $this->phonesList = $record->phonesList ;
     $this->email = $record->email ;
     $this->siteUrl = $record->siteUrl ;
     $this->dateStart = $record->dateStart ;
     $this->predoplata = $record->predoplata ;
     $this->docUrl = $record->docUrl ;

     $this->internalNumber = $record->internalNumber ;
     $this->contractNumber= $record->contractNumber ;
     if (empty($this->contractNumber))$this->contractNumber= $record->internalNumber ;
     
     $this->postoplate = $record->postoplate ;
       
   }
      
   public function saveData ()
   {
       
     if(empty($this->id)) return;
     $record=TblContracts::findOne($this->id);
     if(empty($record)) return;
     
     $record->creationTime  = date("Y-m-d h:i:s", strtotime($this->creationTime)) ;
     $record->clientTitle   = $this->clientTitle ;
     $record->clientAdress  = $this->clientAdress ;
     $record->orgINN        = $this->orgINN ;
     $record->orgKPP        = $this->orgKPP ;
     $record->bankRekvesits = $this->bankRekvesits ;
     $record->contactorFull = $this->contactorFull ;
     $record->contractorShort  = $this->contractorShort ;
     $record->contractorPost   = $this->contractorPost ;
     $record->contractorReason = $this->contractorReason ;
     $record->oplatePeriod  = $this->oplatePeriod ;
     $record->oplateStart   = $this->oplateStart ;
     $record->dopCondition  = $this->dopCondition ;
     $record->userFormer    = $this->userFormer ;
     $record->dateEnd       = date("Y-m-d",  strtotime($this->dateEnd ));
     $record->phonesList    = $this->phonesList ;
     $record->email         = $this->email ;
     $record->siteUrl       = $this->siteUrl ;
     $record->dateStart     = date("Y-m-d",  strtotime($this->dateStart)) ;
     $record->predoplata    = $this->predoplata ;
     $record->docUrl        = $this->docUrl ;
     if (empty($this->internalNumber ) ) 
     {
     $record->internalNumber =$this->contractNumber;
     }
     else
     {
     $record->internalNumber = $this->internalNumber ;     
     }
     $record->contractNumber = $this->contractNumber;
     $record->postoplate    = $this->postoplate ;
            
     $record->save();       
   }

   public function saveDataNew ()
   {

     if(empty($this->id)) $record=new TblContracts();
                 else      $record=TblContracts::findOne($this->id);
     if(empty($record)) return;

     $record->refOrg = $this->refOrg;
     $record->creationTime  = date("Y-m-d h:i:s") ;
     $record->clientTitle   = $this->clientTitle ;
     $record->clientAdress  = $this->clientAdress ;
     $record->orgINN        = $this->orgINN ;
     $record->orgKPP        = $this->orgKPP ;
     $record->bankRekvesits = $this->bankRekvesits ;
     $record->contactorFull = $this->contactorFull ;
     $record->contractorShort  = $this->contractorShort ;
     $record->contractorPost   = $this->contractorPost ;
     $record->contractorReason = $this->contractorReason ;
     $record->oplatePeriod  = $this->oplatePeriod ;
     $record->oplateStart   = $this->oplateStart ;


     $record->userFormer    = $this->userFormer ;
     $record->dateEnd       = date("Y-m-d",  strtotime($this->dateEnd ));
     $record->phonesList    = $this->phonesList ;
     $record->email         = $this->email ;
     $record->siteUrl       = $this->siteUrl ;
     $record->dateStart     = date("Y-m-d",  strtotime($this->dateStart)) ;
     $record->predoplata    = $this->predoplata ;
     $record->docUrl        = $this->docUrl ;
     
     if (empty($this->internalNumber ) ) 
     {
     $record->internalNumber =$this->contractNumber;
     }
     else
     {
     $record->internalNumber = $this->internalNumber ;     
     }
     $record->contractNumber = $this->contractNumber;
    
     if ($this->isPostPplate == 1) $record->postoplate = ', а оставшиеся 50% ';
     //$record->postoplate    = $this->postoplate ;

     if ($this->isDopCondition == 1) $record->dopCondition = 'и при условии предоставления Поставщиком документов, указанных в п. 2.1 настоящего договора. ';
     //$record->dopCondition  = $this->dopCondition ;



     $record->save();
   }


   public function  getContructNumber()
   {
     $ret = [
     'res' => false,
     'val' => ''
     ];
     $record = ConfigTable::findOne(2200);   
     if (empty($record)) return  $ret;
     $ret['val']= intval($record->keyValue)+1;
     $record->keyValue = $ret['val'];
     $record->save();   
     $ret['res'] = true;
     return $ret;   
   }
   
  /*****************************************/

   public function  getContractCss ()
   {
    $fpath=realpath(dirname(__FILE__))."/".'contractTemplate.css';
    $html = file_get_contents ($fpath);
    return $html;
   }
   public function prepareContractDoc($id)
   {
    $fpath=realpath(dirname(__FILE__))."/".'contractTemplate.html';

    $record=TblContracts::findOne($id);
    if(empty($record)) {
        $html = file_get_contents ($fpath);
        return $html;
    }

    $lines = file($fpath);
    $html="";
    $N = count($lines);
    for($i=0;$i<$N;$i++)
    {
       /* if (!preg_match("/\[\[/",$lines[$i])){
            $html.=$lines[$i]."\n";
            continue;
        }*/

        $dogNumber=$record->contractNumber;
        if(empty($dogNumber))$dogNumber = $record->internalNumber;
        
        $lines[$i]=preg_replace("/\[\[dogNumber\]\]/iu",$dogNumber,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[dogDate\]\]/iu",date("d.m.Y",strtotime($record->dateStart)),$lines[$i]);
        $lines[$i]=preg_replace("/\[\[clientTitle\]\]/iu",$record->clientTitle,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[contractorFull\]\]/iu",$record->contactorFull,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[contractorReason\]\]/iu",$record->contractorReason,$lines[$i]);

        if(empty($record->predoplata))
            $lines[$i]=preg_replace("/\[\[predoplata\]\]/iu","",$lines[$i]);
        else
            $lines[$i]=preg_replace("/\[\[predoplata\]\]/iu",$record->predoplata."%",$lines[$i]);

        $lines[$i]=preg_replace("/\[\[postoplata\]\]/iu",$record->postoplate,$lines[$i]);

        $lines[$i]=preg_replace("/\[\[oplatePeriod\]\]/iu",$record->oplatePeriod,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[oplateStart\]\]/iu",$record->oplateStart,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[dopCondition\]\]/iu",$record->dopCondition,$lines[$i]);

        $lines[$i]=preg_replace("/\[\[clientAdress\]\]/iu",$record->clientAdress,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[orgINN\]\]/iu",$record->orgINN,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[orgKPP\]\]/iu",$record->orgKPP,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[bankRekvesits\]\]/iu",$record->bankRekvesits,$lines[$i]);

        $lines[$i]=preg_replace("/\[\[phonesList\]\]/iu",$record->phonesList,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[email\]\]/iu",$record->email,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[siteUrl\]\]/iu",$record->siteUrl,$lines[$i]);


        $lines[$i]=preg_replace("/\[\[contractorPost\]\]/iu",$record->contractorPost,$lines[$i]);
        $lines[$i]=preg_replace("/\[\[contractorShort\]\]/iu",$record->contractorShort,$lines[$i]);

        $lines[$i]=preg_replace("/\[\[userFormer\]\]/iu",$record->userFormer,$lines[$i]);

        $html.=$lines[$i]."\n";
    }

     return $html;
   }


   public function scanContract()
   {
      // bankRekvesits,
      $strSql="select DISTINCT contactorFull, contractorReason, contractorShort,
                contractorPost, a.refOrg, clientAdress, clientTitle
             from rik_contracts as a left join rik_org_rekvezit as b on a.refOrg = b.refOrg
             where b.id is null";
      $list = Yii::$app->db->createCommand($strSql)->queryAll();

      $N = count($list);
      for ($i=0;$i<$N; $i++)
      {
        $orgRecord = OrgList::findOne($list[$i]['refOrg']);
        if (empty($orgRecord)) continue;
        /*if (empty($orgRecord->shortTitle) && mb_strlen($list[$i]['clientTitle'])< 75) {
            $orgRecord->shortTitle  = $list[$i]['clientTitle'];
            $orgRecord->save();
        }*/

        $record = TblOrgRekvezit::findOne(['refOrg' => $list[$i]['refOrg']]);
        if (empty($record)){
          $record = new TblOrgRekvezit ();
          if (empty($record)) continue;

         $record->refOrg = $list[$i]['refOrg'];


         $dogHead ="";
         $dogFIO=$list[$i]['contactorFull'];
       /*  if (preg_match("/директора/iu",$list[$i]['contactorFull'])){
            $dogHead ="директора";
            $dogFIO=preg_replace("/директора/iu","",$list[$i]['contactorFull']);
         }*/
         $record->dogFIO = $dogFIO;
         $record->dogHead = $dogHead;

         $record->dogBase= $list[$i]['contractorReason'];

         $record->signFIO  = $list[$i]['contractorShort'];
         $record->signHead = $list[$i]['contractorPost'];
       //  $record->orgBase= 'на основании '.$list[$i]['contractorReason'];
         $record->save();

        }

         $adresRec= AdressList::findOne(['ref_org' =>  $list[$i]['refOrg'], 'isOfficial' => 1]);
         if (empty($adresRec))
         {
            $adresRec= new AdressList();
            if (empty($adresRec)) continue;
            $adresRec->ref_org =$list[$i]['refOrg'];
            $adresRec->isOfficial = 1;
            $adresRec->adress = $list[$i]['clientAdress'];
            $adresRec->save();
         }


      }


   }
/*ALTER TABLE `rik_org_rekvezit` MODIFY COLUMN `dogBase` MEDIUMTEXT COLLATE utf8_general_ci COMMENT 'действующего на основании';
ALTER TABLE `rik_org_rekvezit` MODIFY COLUMN `signHead` VARCHAR(150) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'должность как в подписе';
*/
 }
 
