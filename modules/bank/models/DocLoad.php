<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\TblDocuments;
use app\modules\bank\models\TblDocHeader;
use app\modules\bank\models\TblOrgList;
use app\modules\bank\models\TblOrgAccounts;

use app\modules\bank\models\ClientBankExchange;
use app\modules\bank\models\ClientBankDocument;

use app\modules\bank\models\TblSupply;
use app\modules\bank\models\TblPurchControl;
use app\modules\bank\models\TblClientSchetHeader;
use app\modules\bank\models\TblSupplierSchetHeader;

use app\modules\yandex\models\DiskApi;
/**
 * DocLoad - модель работы первичкой
 */
 
 class DocLoad extends Model
{

/*Поля*/


public $id = 0; //'Номер регистрации документа',

public $docIntNum = ""; //'Номер регистрации документа',
  
public $orgTitle = ""; //  'Контрагент',  
public $refOrg = 0; // 
public $orgINN = ""; // 
public $orgKPP = ""; // 
public $orgRef = 0;

public $docTitle = ""; //  'Название',  
public $docType = ""; //  'тип документа',
 
public $docURI = ""; //  'Путь к документам',

public $docOrigStatus = ""; // '0-оригинал\r\n1-копия\r\n2-скан',
public $docOrigNum = ""; //  'номер документа (оригинальная нумерация)',
public $docOrigDate = ""; //  'Оригинальная дата',
public $docSum = ""; //  'сумма если есть',
public $docNDS= ""; //  'НДС если есть',

public $docURIType="";
 
public $isFinance = ""; //  'финансовый',
public $isOplate = ""; //  'Оплачиваемый',
 
public $docNote = ""; // 'комментарий',
public $ref1C_input = ""; //
public $ref1C_schet = "";

public $docGoal="";
public $refDocHeader = ""; // 'ссылка на отчет об оцифровки',
public $refManager = ""; // 'ссылка на менеджера',
public $regDateTime = ""; // 'Дата и время регистрации',
public $docOwner ="";
public $contagentAccount="";
public $docSRC ="";

public $showDate = ""; //'',
public $flt ="";


public $command;
public $count;

public $isTTN=0;
public $isUTR=0;      
    
public $refAccount=0;
public $orgBIK='';
public $orgRS='';
public $orgBank='';
public $orgKS='';

public $loadFile;

public $orgType;
public $orgDeal;
public $docArticleRef=0;

public $docTypeTitle  ="";
public $docClassTitle ="";

public $docClassifyRef=0;
public $docTypeRef=0;


public $fromDate;
public $toDate;

public $month;
public $year;

public $debug=[];


public $ref1C;
    /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 
    
/*Obsoleted*/    
public $contragentType=0;
public $operationType=0;

    public function rules()
    {
        return [                              
            [['docIntNum','orgTitle','refOrg','docTitle','docType','docURI','docOrigStatus','docOrigNum', 'docOrigDate', 'docGoal', 'docOwner',
            'docSum','isFinance','isOplate','docNote','ref1C_input','refDocHeader','refManager','regDateTime', 'id','orgKPP','orgINN',
            'dataId', 'recordId', 'dataVal', 'dataType','ref1C_schet', 'contagentAccount', 'docSRC', 'isTTN', 'isUTR', 'contragentType','operationType',     
            'refAccount', 'orgBIK', 'orgRS' , 'orgBank',  'docNDS',    'orgKS', 'loadFile', 'docArticleRef','docClassifyRef','docTypeRef'
             ], 'default'],                        

             [['loadFile'], 'file'],
            
//              [['docOrigDate'], 'date', 'format'=>'php:d.m.Y'],
//            [['docOrigDate'], 'default', 'value' => function ($model, $attribute) {return date('d.m.Y');   }],
             [['docIntNum', 'orgTitle', 'ref1C'], 'safe'],
             [['refAccount','refOrg','docArticleRef', 'docClassifyRef' ], 'integer'],     

        ];
    }

    public function loadData()
    {

      /*Это не id! Но надо бы заменить на автогенерацию*/  
      $strSql = "SELECT max(docIntNum) from {{%documents}} ORDER BY id"; 
      $maxNum =  Yii::$app->db->createCommand($strSql)->queryScalar();                    
      $maxNum++;
     
     if (empty($this->id))  
     {
       //$record= new TblDocuments();  
       //$record-save()
       $this->docOrigDate=date('d.m.Y');       
       $this->docIntNum = $maxNum;
       return;
     }
     
     $record= TblDocuments::findOne($this->id);
     if (empty($record))     
     {
       $this->docOrigDate=date('d.m.Y');
       $this->docIntNum = $maxNum;
       return;
     }         
        $this->docIntNum = $record->docIntNum;
        $this->orgTitle = $record->orgTitle;
        $this->refOrg = $record->refOrg;          
       // $this->docTitle = $record->docTitle;
        $this->docClassifyRef= $record->docClassifyRef;
        $this->docType = $record->docType;
        $this->docURI = $record->docURI;
        $this->docOrigStatus  = $record->docOrigStatus ;
        $this->docOrigNum = $record->docOrigNum ;
        $this->docOrigDate  = date('d.m.Y', strtotime($record->docOrigDate));
        $this->docSum = floatval($record->docSum);
        $this->docNDS= floatval($record->docNDS);
        $this->isFinance =$record->isFinance    ;
        $this->isOplate = $record->isOplate;
        $this->docNote = $record->docNote ; 
        $this->ref1C_input = $record->ref1C_input ;
        $this->ref1C_schet = $record->ref1C_schet;    
        $this->orgINN = $record->orgINN;
        $this->orgKPP = $record->orgKPP;
        $this->docGoal = $record->docGoal;
        $this->docOwner = $record->docOwner;
//        $record->docNum = $this->docNum;
        //$this->contagentAccount = $record->contagentAccount;
        $this->isTTN = $record->isTTN;
        $this->isUTR = $record->isUTR;
        
        $this->docURIType= $record->docURIType;
/*  Obsoleted*/
        $this->contragentType = $record->contragentType;
        $this->operationType = $record->operationType;
        
        $this->docArticleRef = $record->docArticleRef;
        if (!empty ($this->docArticleRef)){
        $strSql = "SELECT {{%bank_op_article}}.article, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id and {{%bank_op_article}}.id =:docArticleRef ORDER BY {{%bank_op_article}}.id"; 
         $list = Yii::$app->db->createCommand($strSql,[ ':docArticleRef' => intval($this->docArticleRef),])->queryAll();         
         if(count($list) > 0) {
            $this->orgType = $list[0]['grpTitle'];
            $this->orgDeal = $list[0]['article'];
        } else{
            $this->orgType = "N/A";
            $this->orgDeal = "N/A";        
        }
        }
        
        $this->docClassifyRef = $record->docClassifyRef;
        $this->docTypeRef = $record->docTypeRef;
        if (!empty ($this->docClassifyRef)){
        $strSql = "SELECT docType from {{%doc_classify}} where id =:docClassifyRef"; 
         $this->docClassTitle = Yii::$app->db->createCommand($strSql,[ ':docClassifyRef' => intval($this->docClassifyRef),])->queryScalar();         
        } else{
            $this->docClassTitle = "N/A";            
        }
        
        if (!empty ($this->docTypeRef)){
        $strSql = "SELECT docGrpTitle from {{%doc_group}} where id =:docTypeRef"; 
         $this->docTypeTitle = Yii::$app->db->createCommand($strSql,[ ':docTypeRef' => intval($this->docTypeRef),])->queryScalar();         
        } else{
            $this->docTypeTitle = "N/A";            
        }       
        
      
   if (empty($record->refAccount))       
   {
   
   $strSql = "SELECT id, orgBIK,  orgRS, orgBank, orgKS
    from {{%org_accounts}} where isActive =1 AND isDefault = 1
    AND refOrg=:refOrg ORDER BY id"; 
   $list = Yii::$app->db->createCommand($strSql,
   [    
    ':refOrg' => intval($this->refOrg),
   ])->queryAll(); 
   } else
   {
    $strSql = "SELECT id, orgBIK,  orgRS, orgBank, orgKS
    from {{%org_accounts}} where id =:refAccount"; 
   $list = Yii::$app->db->createCommand($strSql,
   [
    ':refAccount' => $record->refAccount,
   ])->queryAll();    

   }
   
   if (count($list) != 0) 
   {
    $this->refAccount= $list[0]['id'];   
    $this->orgBIK  = $list[0]['orgBIK'];
    $this->orgRS   = $list[0]['orgRS'];
    $this->orgBank = $list[0]['orgBank'];
    $this->orgKS   = $list[0]['orgKS'];
   }
}

public $orgShowTitle="";

    public function loadShortData()
    {
     
     if (empty($this->id))  
       return;     
     $record= TblDocuments::findOne($this->id);
     if (empty($record))     
       return;

           $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}"; 
           $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
           $operationArray =  ArrayHelper::map($list,'id','docType');       
           $operationArray[0]='Не задан';
                $this->docTitle = $operationArray[$record->docClassifyRef];
           if (empty($record->docClassifyRef)) $this->docTitle = $record->docTitle;
        

        $this->docIntNum = $record->docIntNum;
        $this->orgShowTitle = $record->orgTitle;
        $this->refOrg = $record->refOrg;          
        $this->docClassifyRef= $record->docClassifyRef;
        $this->docType = $record->docType;
        $this->docURI = $record->docURI;
        $this->docOrigStatus  = $record->docOrigStatus ;
        $this->docOrigNum = $record->docOrigNum ;
        $this->docOrigDate  = date('d.m.Y', strtotime($record->docOrigDate));
        $this->docSum = floatval($record->docSum);
        $this->docNDS= floatval($record->docNDS);
        $this->isFinance =$record->isFinance    ;
        $this->isOplate = $record->isOplate;
        $this->docNote = $record->docNote ; 
        
        $this->ref1C_input = $record->ref1C_input ;
        $this->ref1C_schet = $record->ref1C_schet;    
        $this->orgINN = $record->orgINN;
        $this->orgKPP = $record->orgKPP;
        $this->docGoal = $record->docGoal;
        $this->docOwner = $record->docOwner;
        $this->isTTN = $record->isTTN;
        $this->isUTR = $record->isUTR;
        
        if (!empty($record->docArticleRef)){
         $strSql = "SELECT {{%bank_op_article}}.article, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id and {{%bank_op_article}}.id =:docArticleRef ORDER BY {{%bank_op_article}}.id"; 
         $list = Yii::$app->db->createCommand($strSql,[ ':docArticleRef' => $record->docArticleRef,])->queryAll();         
         if(count($list) > 0) {
            $this->orgType = $list[0]['grpTitle'];
            $this->orgDeal = $list[0]['article'];
        }
        }  
        
    }
  
    public function actionGetDealParam($dealRef)  
    {
       $res=[
       'id' => $dealRef,
       'orgType' => "N/A",
       'orgDeal' => "N/A",       
       ];
          $strSql = "SELECT {{%bank_op_article}}.article, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id and {{%bank_op_article}}.id =:docArticleRef ORDER BY {{%bank_op_article}}.id"; 
         $list = Yii::$app->db->createCommand($strSql,[ ':docArticleRef' => intval($dealRef),])->queryAll();         
         if(count($list) > 0) {
            $res['orgType'] = $list[0]['grpTitle'];
            $res['orgDeal'] = $list[0]['article'];
        }
    return $res;
    }

    
           
    
    public function actionSetDealParam()  
    {
       $id = intval($this->recordId);
       $val= intval($this->dataVal);
       $ret= ['res' => false, 
              'val' =>$this->dataVal, 
              'id' => $id, 
              'orgType' => "N/A",
              'orgDeal' => "N/A",                     
             ];   
                      
       $record=TblDocuments::findOne($id);           
       if (empty($record)) return $ret;
       $record->docArticleRef = $val;
       $record->save();
                                    
        $strSql = "SELECT {{%bank_op_article}}.article, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
              where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id and {{%bank_op_article}}.id =:docArticleRef ORDER BY {{%bank_op_article}}.id"; 
         $list = Yii::$app->db->createCommand($strSql,[ ':docArticleRef' => $val,])->queryAll();         
         if(count($list) > 0) {
            $ret['orgType'] = $list[0]['grpTitle'];
            $ret['orgDeal'] = $list[0]['article'];
        }
  
    $ret['res']=true;
    return $ret;
    }
    
      
  public function duplicateDoc($srcRef)
  {
     $record= new TblDocuments();    
     if (empty($record)){ return 0;  }           
     $srcRecord= TblDocuments::findOne($srcRef);
     if(!empty($srcRecord)) 
     {
     $curUser=Yii::$app->user->identity;
      /*Это не id! Но надо бы заменить на автогенерацию*/  
      $strSql = "SELECT max(docIntNum) from {{%documents}} ORDER BY id"; 
      $maxNum =  Yii::$app->db->createCommand($strSql)->queryScalar();                    
      $maxNum++;
      $record->docIntNum = $maxNum ;
      $record->orgTitle  = $srcRecord->orgTitle;
      $record->refOrg    = $srcRecord->refOrg;          
      $record->docClassifyRef = $srcRecord->docClassifyRef;
        
       $record->docType = $srcRecord->docType;
   
       $record->refManager =  $curUser->id;
       $record->docOrigStatus = $srcRecord->docOrigStatus  ;
        
       $record->docNDS = $srcRecord->docNDS;
       $record->isFinance = $srcRecord->isFinance     ;
       $record->isOplate = $srcRecord->isOplate;
       $record->docNote = $srcRecord->docNote; 
       $record->orgINN = $srcRecord->orgINN;
       $record->orgKPP =$srcRecord->orgKPP; 
       $record->docGoal = $srcRecord->docGoal;
       $record->docOwner = $srcRecord->docOwner;
       $record->isTTN= $srcRecord->isTTN;
       $record->isUTR= $srcRecord->isUTR;
/*      Obsoleted  */
        $record->contragentType = $srcRecord->contragentType;
        $record->operationType  = $srcRecord->operationType;
        $record->docArticleRef = $srcRecord->docArticleRef;
        $record->docClassifyRef=  $srcRecord->docClassifyRef ;
        $record->docTypeRef  = $srcRecord->docTypeRef ;
        $record->refAccount = $srcRecord->refAccount;        
     }
      $record->save();
      return $record->id;
  }
  
  
    public function saveData()
    {
     if (empty($this->id)) {
         $record= new TblDocuments();     }    
     else $record= TblDocuments::findOne($this->id);
     
     //if (empty($record))$record= new TblDocuments();
     if (empty($record)){ return false;  }         
     
     $curUser=Yii::$app->user->identity;

        $record->docIntNum = $this->docIntNum ;
        $record->orgTitle  = $this->orgTitle;
        $record->refOrg      = $this->refOrg;          
        //$record->docTitle = $this->docTitle;
        $record->docClassifyRef = intval($this->docClassifyRef);
        
        $record->docType = $this->docType;
        $record->docURI  = $this->docURI;
        $record->refManager =  $curUser->id;
        $record->docOrigStatus = $this->docOrigStatus  ;
        $record->docOrigNum = $this->docOrigNum;
        $record->docOrigDate    = date('Y-m-d', strtotime($this->docOrigDate));
        $this->docSum = str_replace(',', '.',$this->docSum); 
        $record->docSum = floatval($this->docSum);
        $record->docNDS = floatval($this->docNDS);
        $record->isFinance = $this->isFinance     ;
        $record->isOplate = $this->isOplate;
        $record->docNote = $this->docNote; 
        $record->ref1C_input = $this->ref1C_input;
        $record->ref1C_schet = $this->ref1C_schet;    
     
       
        $record->orgINN = $this->orgINN;
        $record->orgKPP =$this->orgKPP; 
        $record->docGoal = $this->docGoal;
        if (empty($this->docOwner)) $this->docOwner =  $this->docGoal;
        if (empty($this->docOwner)) $this->docOwner = '-';
        $record->docOwner = $this->docOwner;
//        $record->docNum = $this->docNum;
        //$record->contagentAccount = $this->contagentAccount;
        $record->isTTN= $this->isTTN;
        $record->isUTR= $this->isUTR;
/*      Obsoleted  */
        $record->contragentType = $this->contragentType;
        $record->operationType  = $this->operationType;
        $record->docArticleRef = $this->docArticleRef;
        $record->docClassifyRef=  $this->docClassifyRef ;
         $record->docTypeRef  = $this->docTypeRef ;
          
        if ((empty($this->refAccount) || $this->refAccount == -1) && !empty($this->refOrg))       
        {
            
        if ($this->refAccount != -1 && empty($recAccount->orgRS) ){
        /*Если нет явного указания создать новый, пробуем задать по умолчанию*/
        $strSql = "SELECT id, orgBIK,  orgRS, orgBank, orgKS
            from {{%org_accounts}} where isActive =1 
            and isDefault = 1 AND refOrg=:refOrg ORDER BY id"; 
        $list = Yii::$app->db->createCommand($strSql,
        [
            ':refOrg' => intval($this->refOrg),
        ])->queryAll(); 
               
        if (count($list) > 0)$this->refAccount = $list[0]['id'];
        else $this->refAccount = -1;
        }
        
        if($this->refAccount == -1){ 
            //а вдруг найдем, тогда просто перепишем
            $recAccount = TblOrgAccounts::findOne(['orgRS'=>$this->orgRS, 'refOrg'=>$this->refOrg, 'isDefault' => 1 ]);
            if (empty ($recAccount)) $recAccount = new TblOrgAccounts(); //не получилось
            if (empty ($recAccount)) return $res; //сбой базы
            $recAccount->refOrg = $this->refOrg;      
            $recAccount->orgBIK = $this->orgBIK;
            $recAccount->orgRS =$this->orgRS ;
            $recAccount->orgBank = $this->orgBank ;
            $recAccount->orgKS = $this->orgKS ;
            $recAccount->save();
            $this->refAccount = $recAccount->id;
          }        
        }
        
        $record->refAccount = $this->refAccount;        
        $record->save();

      /*цепляем ссылку на организацию*/
      if (empty($this->refOrg))
      {                  
      $strsql= "UPDATE {{%documents}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} where isOrgActive =1 group by orgINN) as org
            set refOrg = org.id where  ifnull(refOrg,0) = 0 AND org.orgINN = {{%documents}}.orgINN AND org.n = 1;";
      Yii::$app->db->createCommand($strsql)->execute();           
      }
      if ($this->refOrg == -2)
      {
        $newOrg=new TblOrgList();
        if(!empty($newOrg)){
          if(empty($this->orgTitle) || $this->orgTitle =='Создать автоматически')
          {
            $newOrg->save();
            $this->orgTitle ="Организация ID=".$newOrg->id;            
          } 
          $newOrg->title  = $this->orgTitle;
          $newOrg->schetINN = $this->orgINN;
          $newOrg->orgKPP =$this->orgKPP; 
          $newOrg->save();          
          $record->refOrg   = $newOrg->id;         
          $record->orgTitle  = $this->orgTitle;
          $record->save();
        }
      }  
      //Проверим на установленный НДС в организации
      if (!empty($this->refOrg) && $this->isOplate == 1)
      {
        $recOrg=TblOrgList::findOne($this->refOrg);
        if(!empty($recOrg) && ($recOrg->isSetNDS ==0) ){
          $recOrg->defNDS =  $this->docNDS;   
          $recOrg->isSetNDS = 1;
          $recOrg->save();
        }

      }          
        
     return true;
    }
/*************************************/


   public function getClassifier()
   {
   
      $strSql = "SELECT id, docType from {{%doc_classify}} ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      $ret = ArrayHelper::map($list,'id','docType');
      $ret[0]='Не задан';
      return $ret;
   }  


   public function getTypeArray()
   {
      $strSql = "SELECT id, typeTitle from {{%doc_type}} ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      return ArrayHelper::map($list,'id','typeTitle');
   }  
 
   public function getOperationArray($typeRef)
   {
       $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$typeRef." ORDER BY id"; 
       $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
       return ArrayHelper::map($list,'id','operationTitle');       
   }  

  public function getAllOperationArray()
   {
       $strSql = "SELECT id, operationTitle from {{%doc_operation}}  ORDER BY id"; 
       $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
       return ArrayHelper::map($list,'id','operationTitle');       
   }  

    
/*************************************/
    public function saveAjaxData ()
    {
     $record = TblDocuments::findOne($this->recordId);
      $isSwitch =0;
    if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => 0, 'dataType' => $this->dataType];
    switch ($this->dataType)
    {
       case 'contragentType':
       $record->contragentType = $this->dataVal;
       $record->operationType = 0;
       break;
       case 'operationType':
       $record->operationType = $this->dataVal;
       break;
       case 'isTTN':
       if ($record->isTTN ==0) $record->isTTN = 1;
                          else $record->isTTN = 0;        
       $this->dataVal = $record->isTTN;
       $isSwitch = 1;
       break;
       
       case 'isUTR':
       if ($record->isUTR ==0) $record->isUTR = 1;
                          else $record->isUTR = 0;        
       $this->dataVal = $record->isUTR;
       $isSwitch = 1;
       break;

       case 'isOplate':
       if ($record->isOplate ==0) $record->isOplate = 1;
                             else $record->isOplate = 0;        
       $this->dataVal = $record->isOplate;
       $isSwitch = 1;
       break;
       
       
       
       case 'refOrg':
       $refOrg=intval($this->dataVal);
       $recordOrg=TblOrgList::findOne($refOrg);
       if (empty ($recordOrg)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => 0, 'dataType' => $this->dataType];       
       $record->refOrg = $refOrg;
       $record->orgTitle = $recordOrg->title;
       $record->save();       
           return ['res' => true, 
                    'val' =>$this->dataVal, 
                    'id' => $this->recordId, 
                    'isSwitch' => $isSwitch, 
                    'dataType' => $this->dataType,
                    'orgINN' =>   $recordOrg->orgINN, 
                    'orgTitle' => $recordOrg->title
                  ];   
       break;
       
     }
    $record->save();    
    return ['res' => true, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => $isSwitch, 'dataType' => $this->dataType];    
        
    }
    
    
/*************************************/
    public function saveRefData ()
    {
     
     $ret = ['res' => false, 
             'val' =>'',
             'dataVal' => $this->dataVal, 
             'recordId' => $this->recordId,                     
             'dataType' => $this->dataType,
             ];   
     $record = TblDocuments::findOne($this->recordId);
     if (empty($record)) return $ret;
    
    switch ($this->dataType)
    {
       case 'clientSchet':
       $refRecord = TblClientSchetHeader::findOne(intval($this->dataVal));
       if (empty($refRecord)) return $ret;       

       $record->refClientSchet = $refRecord->id;
       $record->refSupplierSchet = 0;
       $record->refSupply = 0;
       $record->refPurch  = 0;
       $record->ref1C_input = $refRecord->schetRef1C;
       $record->save();
       $ret['val'] = $record->ref1C_input;       
       break;

       case 'supplierSchet':
       $refRecord = TblSupplierSchetHeader::findOne(intval($this->dataVal));
       if (empty($refRecord)) return $ret;       
       $record->refClientSchet = 0;
       $record->refSupplierSchet = $refRecord->id;
       $record->refSupply = 0;
       $record->refPurch  = 0;       
       $record->ref1C_input = $refRecord->supplierRef1C;
       $record->save();
       $ret['val'] = $record->ref1C_input;       
       break;

       case 'purchRef':
       $refRecord = TblPurchControl::findOne(intval($this->dataVal));
       if (empty($refRecord)) return $ret;       
       $record->refClientSchet = 0;
       $record->refSupplierSchet = 0;
       $record->refSupply = 0;
       $record->refPurch  = $refRecord->id;       
       $record->ref1C_input = $refRecord->ref1C;
       $record->save();
       $ret['val'] = $record->ref1C_input;       
       break;

       case 'supplyRef':
       $refRecord = TblSupply::findOne(intval($this->dataVal));
       if (empty($refRecord)) return $ret;       
       $record->refClientSchet = 0;
       $record->refSupplierSchet = 0;
       $record->refSupply = $refRecord->id;
       $record->refPurch  = 0;       
       $record->ref1C_input = $refRecord->ref1C;
       $record->save();
       $ret['val'] = $record->ref1C_input;       
       break;
                         
       default:
       return $res;   
     }
    $ret['res'] = true;    
    return $ret;    
        
    }
    
    
    
/*****************************/    
/**** Providers **************/    
/*****************************/
/* Список загруженных документов */
 public function getTotalErrors()
 {
  $strSql=" SELECT Count({{%documents}}.id) FROM {{%documents}} 
   left Join {{%doc_classify}} on {{%documents}}.docClassifyRef = {{%doc_classify}}.id
    where (ifnull(isRef1C,0)=1 and (refSupplierSchet+refClientSchet+refSupply+refPurch) = 0) 
    or IFNULL(docURI,'') =''  or docArticleRef = 0";
    
  return Yii::$app->db->createCommand($strSql)->queryScalar();
 }

  public function getTotalDoc()
 {
  $strSql=" SELECT Count({{%documents}}.id) FROM {{%documents}}";
    
  return Yii::$app->db->createCommand($strSql)->queryScalar();
 }

 
public $detail=0;
 public function prepareDocLoadList($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%documents}}.id',   
        'docIntNum',
        'regDateTime',
        'orgTitle',
        '{{%documents}}.docType',
        'docTitle',
        'docURI',
        'refManager',
        'refDocHeader',
        'docOrigStatus',
        'docOrigNum',
        'docOrigDate',
        'docSum',
        'isFinance',
        'isOplate',
        'docNote', 
        'userFIO', 
        'docGoal',
        'orgINN',
        'ref1C_input',
        'ref1C_schet',
        'docOwner',
        '{{%documents}}.contragentType',
        'operationType',
        'isTTN',
        'isUTR',
        'refOrg',
        'docClassifyRef',
       'docArticleRef',
        'refClientSchet',
        'refPurch',
        'refSupply',
        'refSupplierSchet',
        'isRef1C' 
            ])
            ->from("{{%documents}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ->leftJoin('{{%doc_classify}}','{{%doc_classify}}.id = {{%documents}}.docClassifyRef')
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%documents}}.id)")
            ->from("{{%documents}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ->leftJoin('{{%doc_classify}}','{{%doc_classify}}.id = {{%documents}}.docClassifyRef')
            ;            
     
     if (!empty($this->orgRef))
     {
        $query->andFilterWhere(['=', '{{%documents}}.refOrg', intval($this->orgRef)]);
        $countquery->andFilterWhere(['=', '{{%documents}}.refOrg', intval($this->orgRef)]);     
     }
            

    if ($this->detail > 0)
    {
    $query->andWhere("(ifnull(isRef1C,0)=1 AND (refSupplierSchet+refClientSchet+refSupply+refPurch) = 0 )
                       or IFNULL(docURI,'') =''  or docArticleRef = 0");
                       
    $countquery->andWhere("(ifnull(isRef1C,0)=1 AND (refSupplierSchet+refClientSchet+refSupply+refPurch) = 0 )
                       or IFNULL(docURI,'') =''  or docArticleRef = 0");


     if (!empty($this->showDate))
     {
       if ($this->detail == 1){
        $query->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);
        $countquery->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);     
        }
        elseif ($this->detail == 2) {
        $query->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);
        $countquery->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);     
        }        
     }

     
                                        
    }        

    if (!empty($this->flt))
    {
        
       switch  ($this->flt)
       {
           case 'buh' :
               $query->andFilterWhere(['like', 'docGoal', 'бухгалтерия']);
               $countquery->andFilterWhere(['like', 'docGoal', 'бухгалтерия']);      
           break;
       
           case 'office' :
               $query->andFilterWhere(['like', 'docGoal', 'офис']);
               $countquery->andFilterWhere(['like', 'docGoal', 'офис']);                            
           break;

           case 'ware' :
               $query->andFilterWhere(['like', 'docGoal', 'производство']);
               $countquery->andFilterWhere(['like', 'docGoal', 'производство']);                                      
           break;

           case 'buhdoc' :
               $query->andFilterWhere(['like', 'docOwner', 'бухгалтерия']);
               $countquery->andFilterWhere(['like', 'docOwner', 'бухгалтерия']);      
           break;
       
           case 'officedoc' :
               $query->andFilterWhere(['like', 'docOwner', 'офис']);
               $countquery->andFilterWhere(['like', 'docOwner', 'офис']);                            
           break;

           case 'waredoc' :
               $query->andFilterWhere(['like', 'docOwner', 'производство']);
               $countquery->andFilterWhere(['like', 'docOwner', 'производство']);                                      
           break;
           
           case 'newOnly' :
                $query->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d")]);
                $countquery->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d")]);     
                    
                $query->andFilterWhere(['=', '{{%documents}}.refOrg', 0]);
                $countquery->andFilterWhere(['=', '{{%documents}}.refOrg', 0]);     
                
           break;
           
           
                  
       }
    }
/*

*/
     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);
       $countquery->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);                 
       
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);     

       $query->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]);
       $countquery->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]);     
       
       if (!empty($this->docOrigDate))
       {
       $query->andFilterWhere(['=', 'docOrigDate', date("Y-m-d",strtotime($this->docOrigDate))]);
       $countquery->andFilterWhere(['=', 'docOrigDate',  date("Y-m-d",strtotime($this->docOrigDate))]);       
       }

       if (!empty($this->regDateTime))
       {
       $query->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",strtotime($this->regDateTime))]);
       $countquery->andFilterWhere(['=', 'DATE(regDateTime)',  date("Y-m-d",strtotime($this->regDateTime))]);       
       }
       
              
       if (!empty($this->docOrigStatus))
       {
       $val = intval($this->docOrigStatus)-1;    
       $query->andFilterWhere(['=', 'docOrigStatus', $val]);
       $countquery->andFilterWhere(['=', 'docOrigStatus',  $val]);       
       }

       if (!empty($this->docGoal))
       {
       $val="";    
//       Бухгалтерия',  2 => 'Офис', 3 => 'Производство
       switch ($this->docGoal)    
       {
          case 1:
           $val="ухгалтерия";
          break;          
          case 2:
           $val="фис";
          break;          
          case 3:
           $val="роизводство";
          break;                     
       }
       $query->andFilterWhere(['Like', 'docGoal', $val]);
       $countquery->andFilterWhere(['Like', 'docGoal', $val]);     
   
       }
       
       if (!empty($this->docOwner))
       {
       $val="";    
//       Бухгалтерия',  2 => 'Офис', 3 => 'Производство
       switch ($this->docOwner)    
       {
          case 1:
           $val="ухгалтерия";
          break;          
          case 2:
           $val="фис";
          break;          
          case 3:
           $val="роизводство";
          break;                     
       }
       
       
        $query->andFilterWhere(['Like', 'docOwner', $val]);
        $countquery->andFilterWhere(['Like', 'docOwner', $val]);     
       }

       
       if (!empty($this->isFinance))
       {
       if (intval($this->isFinance) == 2)    $val = 0;    
       else       $val = 1;    
       $query->andFilterWhere(['=', 'isFinance', $val]);
       $countquery->andFilterWhere(['=', 'isFinance',  $val]);       
       }

       if (!empty($this->isOplate))
       {
       if (intval($this->isOplate) == 2)    $val = 0;    
       else       $val = 1;    
           
       $query->andFilterWhere(['=', 'isOplate', $val]);
       $countquery->andFilterWhere(['=', 'isOplate',  $val]);       
       }
     }
        
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
 
 
 }

 public function getDocLoadListData($params)
  {
  
    $this->prepareDocLoadList($params);    
    $dataList=$this->command->queryAll();

    
    $mask = realpath(dirname(__FILE__))."/../uploads/docLoaded*.csv";
    array_map("unlink", glob($mask));       
    $fname = "uploads/docLoaded".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Внутренний №"),
        iconv("UTF-8", "Windows-1251","Загружен"),        
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Документ"),
        iconv("UTF-8", "Windows-1251","Документ №"),
        iconv("UTF-8", "Windows-1251","Дата"),
        iconv("UTF-8", "Windows-1251","На сумму"),        
        iconv("UTF-8", "Windows-1251","Статус"),
        iconv("UTF-8", "Windows-1251","Комментарий"),
        iconv("UTF-8", "Windows-1251","Тип"),
        iconv("UTF-8", "Windows-1251","Статья"),
        iconv("UTF-8", "Windows-1251","Оплата"),
        iconv("UTF-8", "Windows-1251","Документ в 1C"),                
        iconv("UTF-8", "Windows-1251","счет в 1C"), 

        iconv("UTF-8", "Windows-1251","Статус"),
        iconv("UTF-8", "Windows-1251","Операция"),  
        
        iconv("UTF-8", "Windows-1251","ТТН"),
        iconv("UTF-8", "Windows-1251","УТР"),
                
        iconv("UTF-8", "Windows-1251","Ответств."),
        iconv("UTF-8", "Windows-1251","Передать"),
        iconv("UTF-8", "Windows-1251","URI"),
        
     );
     fputcsv($fp, $col_title, ";"); 
    
         $typeArray = $this->getTypeArray();
         $typeArray[0]='не задан';
     
          $strSql = "SELECT {{%bank_op_article}}.id, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','grpTitle');       
                $operationArray[0]='не задан'; 

           $strSql = "SELECT {{%bank_op_article}}.id, article from {{%bank_op_article}}"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $articleArray =  ArrayHelper::map($list,'id','article');        
                $articleArray[0]='не задан';    

           $strSql = "SELECT id, operationTitle from {{%doc_operation}} "; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $opArray =  ArrayHelper::map($list,'id','operationTitle');       
                $opArray[0]='не задан';
           
           
           $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $classifyArray =  ArrayHelper::map($list,'id','docType');       
                $classifyArray[0]='Не задан';
                 
     for ($i=0; $i< count($dataList); $i++)
    {        
        
        
        $regTime = strtotime($dataList[$i]['regDateTime']);
        if ($regTime > 100) $regDate = date("d.m.y H:i",$regTime +4*3600);
        else  $regDate ="";
        
        $docOrigTime = strtotime($dataList[$i]['docOrigDate']);
        if ($docOrigTime  > 100) $docOrigDate = date("d.m.y", $docOrigTime);
        else  $docOrigDate ="";
        
        $doc = $dataList[$i]['docTitle']." ".$dataList[$i]['docOrigNum'];

       switch ($dataList[$i]['docOrigStatus']){
                case  1: $docOrigStatus = "Копия"; break;
                case  2: $docOrigStatus = "Скан"; break;
                default: $docOrigStatus = "Оригинал"; break;
        }
        
        
        if ($dataList[$i]['isOplate'] == 1) $isOplate = "да";
                                      else  $isOplate = "";
        
        if (empty($dataList[$i]['docClassifyRef'])) $v =  $dataList[$i]['docTitle'];
            else                                    $v = $classifyArray[$dataList[$i]['docClassifyRef']];
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docIntNum']),    
            iconv("UTF-8", "Windows-1251",$regDate),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']),
            
                
            iconv("UTF-8", "Windows-1251",$v),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docOrigNum']),
            iconv("UTF-8", "Windows-1251",$docOrigDate),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docSum']),
            iconv("UTF-8", "Windows-1251",$docOrigStatus ),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docNote']),
            iconv("UTF-8", "Windows-1251",$operationArray[$dataList[$i]['docArticleRef']]),
            iconv("UTF-8", "Windows-1251",$articleArray[$dataList[$i]['docArticleRef']]),
            iconv("UTF-8", "Windows-1251",$isOplate), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C_input']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C_schet']),
            
            //iconv("UTF-8", "Windows-1251",$typeArray[$dataList[$i]['contragentType']]),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contragentType']),                                             
            iconv("UTF-8", "Windows-1251",$dataList[$i]['operationType']),                                                               
            //iconv("UTF-8", "Windows-1251",$opArray[$dataList[$i]['operationType']]),                                    
            
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['isTTN']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['isUTR']),
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docGoal']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docOwner']),
            $dataList[$i]['docURI']
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return "/modules/bank/".$fname;        

 
 
  }
 
 public function getDocLoadListProvider($params)
   {
    
$this->prepareDocLoadList($params);

    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'docIntNum',
            'orgTitle',            
            'docTitle',
            'docOrigStatus',
            'docOrigNum',
            'docOrigDate',
            'docSum',
            'isFinance',
            'isOplate',            
            'regDateTime',
            'docGoal',
            'orgINN',  
            'docOwner' , 
            'ref1C_input',
            'ref1C_schet',
        'contragentType',
        'operationType',
        'isTTN',
        'isUTR',
        'refClientSchet',
        'refPurch',
        'refSupply',
        'refSupplierSchet'
     
            ],            
            
            'defaultOrder' => [  'regDateTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  /*******************************************/ 

 public function getAccountsProvider($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%org_accounts}}.id',   
        'orgBIK',   
        'orgBank',   
        'orgRS',   
        'orgKS',   
        ])
         ->from("{{%org_accounts}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%org_accounts}}.id)")
            ->from("{{%org_accounts}}")
            ;            
     
        $query->andWhere(['=', 'refOrg', $this->refOrg]);
        $countquery->andWhere(['=', 'refOrg', $this->refOrg]);     

     if (($this->load($params) && $this->validate())) {
  /*     $query->andFilterWhere(['=', 'orgBank', $this->orgBank]);
       $countquery->andFilterWhere(['=', 'orgBank', $this->orgBank]);                 */
     }

   
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
            ],            
            
            'defaultOrder' => [  'orgRS' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }   
  /*******************************************/ 


/**********/
 public function getOrgInfo()
 {
    $res = [ 'result' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId, 
             
             'defNDS' => '',
             'refOrg' => '',
             'orgINN' => '',
             'orgKPP' => '',
             'orgTitle' => '',
             'orgType'=> 'N/A',
             'orgDeal'=> 'N/A',
             'refDeal' => 0
           ];   

    if (empty($this->dataVal)) return $res;

    switch ($this->dataType)
    {
        case 'findOrg':
            $record= TblOrgList::findOne(intval($this->dataVal));
            if (empty($record))return $res;
            
            $res ['refOrg']   = $record->id;
            $res ['orgINN']   = $record->orgINN;
            $res ['orgKPP']   = $record->orgKPP;
            $res ['orgTitle'] = $record->title;
            if ($record->isSetNDS)
                $res ['defNDS'] = $record->defNDS;                
            $res ['orgAccount'] = mb_substr($record->orgAccount,-6,6,'utf-8');
            
            $strSql = "SELECT id, articleRef   from {{%org_deals}} where state =1  AND refOrg=:refOrg";
            $list = Yii::$app->db->createCommand($strSql, [':refOrg' => $record->id,])->queryAll(); 
            
            if (count($list) == 1)
            {
                $deal=$this->actionGetDealParam($list[0]['articleRef']);
                $res ['orgType']   = $deal ['orgType'];
                $res ['orgDeal']   = $deal ['orgDeal'];
                $res ['refDeal']   = $deal ['id'];                
            }
               
        break;
    }    
    $res['result'] = true;    
    return $res;
 }

 public function getAccountInfo()
 {
    $res = [ 'result' => false, 
             'dataVal'  => $this->dataVal, 
             'dataId'   => $this->dataId, 
             
             'refAccount' => 0,
             'orgBIK' => '',
             'orgRS' => '',
             'orgBank' => '',
             'orgKS' => '',
           ];   

   if ($this->recordId == -1)
   {
    $res ['refAccount']  = -1;   
    $res ['orgBIK']  = '';
    $res ['orgRS']   = '';
    $res ['orgBank'] = '';
    $res ['orgKS']   = '';
    $res['result']   = true;                
   }

   switch ($this->dataType)   
   {
   case 'findByOrg':     
   $strSql = "SELECT id, orgBIK,  orgRS, orgBank, orgKS
    from {{%org_accounts}} where isActive =1 
    and isDefault=1 AND refOrg=:refOrg ORDER BY id"; 
   $list = Yii::$app->db->createCommand($strSql,
   [
    ':refOrg' => intval($this->dataId),
   ])->queryAll(); 
   break;
   case 'findById':     
   $strSql = "SELECT id, orgBIK,  orgRS, orgBank, orgKS
    from {{%org_accounts}} where id=:refAcc ORDER BY id"; 
   $list = Yii::$app->db->createCommand($strSql,
   [
    ':refAcc' => intval($this->recordId),
   ])->queryAll(); 
   break;   
   }
   
   
   
   
   if (count($list) == 0) return $res;
   
    $res ['refAccount']  = $list[0]['id'];   
    $res ['orgBIK']  = $list[0]['orgBIK'];
    $res ['orgRS']   = $list[0]['orgRS'];
    $res ['orgBank'] = $list[0]['orgBank'];
    $res ['orgKS']   = $list[0]['orgKS'];
    $res['result']   = true;    
    return $res;
 }
/****************/

public function upload()
 {
   if ($this->validate()) 
   {
      /*'/srv/www/htdocs/phone/uploads/'*/
      $uploadPath=__DIR__."/../uploads/";      
      $this->loadFile->saveAs( $uploadPath. $this->loadFile->baseName . '.' . $this->loadFile->extension);
      return true;
    } else 
    {
      return false;
    }
  }
/**


[statusKbk] => 18210301000011000110
[statusBase] => ТП
[statusPeriod] => КВ.01.2020
[statusNumber] => 0
[statusDate] => 0
[statusType] => 
*/  
public function loadFileDocument($fname)
{
      $exchange = new ClientBankExchange();
      $exchange->loadFileExchange ($fname);    
      $curUser=Yii::$app->user->identity;
      $strSql = "SELECT max(docIntNum) from {{%documents}} ORDER BY id"; 
      $maxNum =  Yii::$app->db->createCommand($strSql)->queryScalar();                    
      $maxNum++;

      /*Определим собственника*/ 
      $ownRecord = TblControlBankUse::findOne(['isCurrent' => 1]);
      if (empty($ownRecord)) return false;
      $ownAccount   = $ownRecord->accountNumber;
      
    for($i=0;$i<count($exchange->documentArray);$i++)
    {
        $doc = $exchange->documentArray[$i];
/*echo "<pre>";
print_r ($doc);   
echo "</pre>";     
return; 
*/       
        $record= new TblDocuments();        
        if (empty($record)){ continue;  }         
        $record->refManager =  $curUser->id;

        $record->docIntNum = $maxNum;
        $maxNum++;

        if ($doc->payerAccount == $ownAccount)
        {
            $orgData = $this->loadOrg($doc, 1);  //Мы платим        
            $docSum =  floatval($doc->summ);
        }
        else
        {
            $orgData = $this->loadOrg($doc, 2);  //Нам платят        
            $docSum =  floatval($doc->summ);
        }

        
        $record->docTitle  = "Платежное поручение";

        
        $record->docOrigStatus = 2 ;
        $record->docOrigNum = $doc->docNum;
        $record->regDateTime =date ('Y-m-d H:i');       
        $record->docOrigDate    = date('Y-m-d', strtotime($doc->docDate));
        $record->docSum = $docSum;
        $record->docNDS = floatval($doc->NDS);
        $record->isFinance = 1;
        $record->isOplate = 1;
        $record->docNote = $doc->payPurpose; 

        /*Специфичные для импортируемых платежек*/        
        $record->payPurpouse = $doc->payPurpose; 
        $record->payStatusKbk = $doc->statusKbk; 
        $record->payStatusBase = $doc->statusBase; 
        $record->payStatusPeriod = $doc->statusPeriod; 
        $record->payStatusNumber = $doc->statusNumber; 
        $record->payStatusDate = $doc->statusDate; 
        $record->payStatusType = $doc->statusType;
        $record->payCreaterStatus= $doc->createrStatus;
        $record->payOkato= $doc->okato;
        $record->payType= $doc->payType;
        $record->payCod= $doc->cod;
        
        /*Организация*/     
        $record->refOrg    = $orgData['id'];          
        $record->orgTitle  = $orgData['orgTitle'];
        $record->orgINN    = $orgData['orgINN'];
        $record->orgKPP    = $orgData['orgKPP'];
        $record->contagentAccount=  mb_substr($orgData['contagentAccount'],-6,6,'utf-8');
        $record->refAccount =  $orgData['refAccount'];
        
       
        
        $record->save();
    }  
 }        
 
 public function loadOrg($doc, $payerFlg)
 {
     
     $orgData=[
                'id' => 0,
                'orgTitle' => '',
                'orgINN' => '',
                'orgKPP' => '',
                'refAccount' => 0,
                'contagentAccount' => '',
             ];

     
     if ( $payerFlg == 1){
         //Мы платим                 
         $orgTitle  = $doc->beneficiary1;
         $orgInn    = $doc->beneficiaryInn;
         $orgKpp    = $doc->beneficiaryKpp;
         
         $bankAcc  = $doc->beneficiaryAccount;
         $bank     = $doc->beneficiaryBank1;
         $bankCity = $doc->beneficiaryBank2;
         $bankBIK  = $doc->beneficiaryBik;
         $bankKS   = $doc->beneficiaryCorrAccount;
     }
     
     if ( $payerFlg == 2){
         //Нам платят                 
         $orgTitle  = $doc->payer1;
         $orgInn    = $doc->payerInn;
         $orgKpp    = $doc->payerKpp;
         
         $bankAcc  = $doc->payerAccount;
         $bank     = $doc->payerBank1;
         $bankCity = $doc->payerBank2;
         $bankBIK  = $doc->payerBik;
         $bankKS   = $doc->payerCorrAccount;
     }

        $orgData['orgTitle'] = $orgTitle;
        $orgData['orgINN']   = $orgInn;
        $orgData['orgKPP']   = $orgKpp;
        $orgData['contagentAccount'] = $bankAcc;

        if (!empty($orgKpp) && trim($orgKpp) != '' )    
            $orgRecord= TblOrgList::findOne(['orgINN'=>$orgInn, 'orgKPP'=>$orgKpp, 'isOrgActive' => 1]);
        else
            $orgRecord= TblOrgList::findOne(['orgINN'=>$orgInn, 'isOrgActive' => 1]);
        
         if (empty($orgRecord)){
            $orgRecord = new TblOrgList();    
            if (empty($orgRecord))return $orgData;             
            $orgRecord->title=$orgTitle;
            $orgRecord->schetINN=$orgData['orgINN'];
            $orgRecord->orgKPP=$orgData['orgKPP'];             
            $orgRecord->save();
         }                  
         $orgData['id']=$orgRecord->id;          
         
         $recAccount = TblOrgAccounts::findOne(['orgRS'=>$bankAcc, 'refOrg'=>$orgRecord->id]);
         if (empty ($recAccount)){
            $recAccount = new TblOrgAccounts(); //не получилось             
            if (empty ($recAccount)) return $orgData;
         }
            $recAccount->refOrg = $orgRecord->id;      
            $recAccount->orgBIK = $bankBIK;
            $recAccount->orgRS  = $bankAcc ;
            $recAccount->orgBank = $bank ;
            $recAccount->orgKS  = $bankKS;
            $recAccount->bankCity = $bankCity;            
            $recAccount->save();
         
         $orgData['refAccount']= $recAccount->id;
/*      echo "<pre>";   
      print_r ($doc);   
      print_r ($bankKS);   
      print_r ($orgData);   
      echo "</pre>";
*/
      return $orgData;
}

/*******************************************/ 
 public function getSupplierSchetProvider($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%supplier_schet_header}}.id',   
        'orgINN',   
        'orgKPP',   
        'orgTitle',   
        'supplierRef1C',   
        'schetDate',   
        ])
         ->from("{{%supplier_schet_header}}");
            
    $countquery  = new Query();
    $countquery->select ("count({{%supplier_schet_header}}.id)")
            ->from("{{%supplier_schet_header}}")
            ;            
    
     $this->id = intval($this->id);
     if(!empty($this->id))$record=TblDocuments::findOne($this->id);
     if(!empty($record))
     {
        $docDT= strtotime($record->docOrigDate);   
        if (empty($this->fromDate)){            
            $fromDT = $docDT-24*3600;
            $this->fromDate = date("d.m.Y",$fromDT);
        }         
        
         if (empty($this->toDate)){  
            $toDT = $docDT+24*3600;         
            $this->toDate = date("d.m.Y",$toDT);
        }         
        
     }
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'schetDate', $from]);
        $countquery->andWhere(['>=', 'schetDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'schetDate', $to]);
        $countquery->andWhere(['<=', 'schetDate', $to]);
     }

     
   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);                 
       
         $query->andFilterWhere(['Like', 'supplierRef1C', $this->ref1C]);
       $countquery->andFilterWhere(['Like', 'supplierRef1C', $this->ref1C]);                 
       
     }

   
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
                'orgTitle',   
                'supplierRef1C',   
                'schetDate',   
            ],            
            
            'defaultOrder' => [  'supplierRef1C' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }   



 public function getClientSchetProvider($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%client_schet_header}}.id',   
        'orgINN',   
        'orgKPP',   
        'orgTitle',   
        'schetRef1C',   
        'schetDate',   
        ])
         ->from("{{%client_schet_header}}");
            
    $countquery  = new Query();
    $countquery->select ("count({{%client_schet_header}}.id)")
            ->from("{{%client_schet_header}}")
            ;            
    
     $this->id = intval($this->id);
     if(!empty($this->id))$record=TblDocuments::findOne($this->id);
     if(!empty($record))
     {
        $docDT= strtotime($record->docOrigDate);   
        if (empty($this->fromDate)){            
            $fromDT = $docDT-24*3600;
            $this->fromDate = date("d.m.Y",$fromDT);
        }         
        
         if (empty($this->toDate)){  
            $toDT = $docDT+24*3600;         
            $this->toDate = date("d.m.Y",$toDT);
        }         
        
     }
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'schetDate', $from]);
        $countquery->andWhere(['>=', 'schetDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'schetDate', $to]);
        $countquery->andWhere(['<=', 'schetDate', $to]);
     }

     
   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);   
       
         $query->andFilterWhere(['Like', 'schetRef1C', $this->ref1C]);
       $countquery->andFilterWhere(['Like', 'schetRef1C', $this->ref1C]);                 
       
                     
     }

   
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
                'orgTitle',   
                'schetRef1C',   
                'schetDate',   
            ],            
            
            'defaultOrder' => [  'schetRef1C' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }   

 public function getSupplyProvider($params)
 {

    $query  = new Query();
    $query->select ([
        'MIN({{%supply}}.id) as id',   
        'orgINN',   
        'orgKPP',   
        'orgTitle',   
        'ref1C',   
        'supplyDate',   
        ])
         ->from("{{%supply}}")
         ->distinct()
         ->groupBy(['ref1C', 'supplyDate']);
            
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT(ref1C) )")
            ->from("{{%supply}}")
            ;            
    
     $this->id = intval($this->id);
     if(!empty($this->id))$record=TblDocuments::findOne($this->id);
     if(!empty($record))
     {
        $docDT= strtotime($record->docOrigDate);   
        if (empty($this->fromDate)){            
            $fromDT = $docDT-24*3600;
            $this->fromDate = date("d.m.Y",$fromDT);
        }         
        
         if (empty($this->toDate)){  
            $toDT = $docDT+24*3600;         
            $this->toDate = date("d.m.Y",$toDT);
        }         
        
     }
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'supplyDate', $from]);
        $countquery->andWhere(['>=', 'supplyDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'supplyDate', $to]);
        $countquery->andWhere(['<=', 'supplyDate', $to]);
     }

     
   if (($this->load($params) && $this->validate())) {
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);                 
       
         $query->andFilterWhere(['Like', 'ref1C', $this->ref1C]);
       $countquery->andFilterWhere(['Like', 'ref1C', $this->ref1C]);                 
       
       
     }

   
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
                'orgTitle',   
                'ref1C',   
                'supplyDate',   
            ],            
            
            'defaultOrder' => [  'ref1C' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }   
     

 public function getPurchProvider($params)
 {

    $query  = new Query();
    $query->select ([
        'MAX({{%control_purch_content}}.id) as id',   
        'orgINN',   
        'orgKPP',   
        'orgTitle',   
        'ref1C',   
        'purchDate',   
        ])
         ->from("{{%control_purch_content}}")
         ->distinct()
         ->groupBy(['ref1C', 'purchDate']);
            
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT(ref1C) )")
            ->from("{{%control_purch_content}}")
            ;            
    
     $this->id = intval($this->id);
     if(!empty($this->id))$record=TblDocuments::findOne($this->id);
     if(!empty($record))
     {
        $docDT= strtotime($record->docOrigDate);   
        if (empty($this->fromDate)){            
            $fromDT = $docDT-24*3600;
            $this->fromDate = date("d.m.Y",$fromDT);
        }         
        
         if (empty($this->toDate)){  
            $toDT = $docDT+24*3600;         
            $this->toDate = date("d.m.Y",$toDT);
        }         
        
     }
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'purchDate', $from]);
        $countquery->andWhere(['>=', 'purchDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'purchDate', $to]);
        $countquery->andWhere(['<=', 'purchDate', $to]);
     }

     
   if (($this->load($params) && $this->validate())) {
         $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);                 
       
         $query->andFilterWhere(['Like', 'ref1C', $this->ref1C]);
       $countquery->andFilterWhere(['Like', 'ref1C', $this->ref1C]);                 
       
   }

   
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
                'orgTitle',   
                'ref1C',   
                'purchDate',   
            ],            
            
            'defaultOrder' => [  'ref1C' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }   

     
/*
ALTER TABLE `rik_documents` ADD COLUMN `refClientSchet` BIGINT DEFAULT 0;
ALTER TABLE `rik_documents` ADD COLUMN `refSupply` BIGINT DEFAULT 0;
ALTER TABLE `rik_documents` ADD COLUMN `refPurch` BIGINT DEFAULT 0;

*/
 
 public $monthErr=0;
 public $monthAll=0;
 public function getErrorList($month, $year)
 {
    $year = intval($year);
    $month = intval($month);
    
    $this->monthErr=0;
    $this->monthAll=0;
    
    $query  = new Query();
    $query->select ([
        'COUNT({{%documents}}.id) as errN',   
        'DAYOFMONTH(regDateTime) as d',           
        ])
         ->from("{{%documents}}")
         ->leftJoin("{{%doc_classify}}", "{{%documents}}.docClassifyRef = {{%doc_classify}}.id" )
         ->distinct()
         ->groupBy(['regDateTime']);
    $query->andWhere ('YEAR(regDateTime) = '.$year);
    $query->andWhere ('MONTH(regDateTime) = '.$month);
    $query->andWhere("(ifnull(isRef1C,0)=1 and (refSupplierSchet+refClientSchet+refSupply+refPurch) = 0) 
                       or IFNULL(docURI,'') =''  or docArticleRef = 0");

    $list = $query->createCommand()->queryAll();
    $res=array();
    $n = date('t',strtotime($year."-".$month."-01"));

    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
        
    for ($i=0;$i<count($list) ; $i++ )
    {
      $d=$list[$i]['d'];
      $res[$d]['err']=$list[$i]['errN'] ; 
      $this->monthErr+=$list[$i]['errN'] ; 
    }

    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(regDateTime) as d',           
        ])
         ->from("{{%documents}}")         
         ->distinct()
         ->groupBy(['DATE(regDateTime)']);
    $query->andWhere ('YEAR(regDateTime) = '.$year);
    $query->andWhere ('MONTH(regDateTime) = '.$month);
    ;
    
    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['all']=$list[$i]['N'] ; 
       $this->monthAll+=$list[$i]['N'] ; 
    }
    
 /*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
  }

  
 public function getControlList($month, $year)
 {
    
    $year = intval($year);
    $month = intval($month);
    
    $this->monthErr=0;
    $this->monthAll=0;
    
    $query  = new Query();
    $query->select ([
        'COUNT({{%documents}}.id) as errN',   
        'DAYOFMONTH(regDateTime) as d',           
        ])
         ->from("{{%documents}}")
         ->leftJoin("{{%doc_classify}}", "{{%documents}}.docClassifyRef = {{%doc_classify}}.id" )
         ->distinct()
         ->groupBy(['DATE(regDateTime)']);

    $query->andWhere ('YEAR(regDateTime) = '.$year);
    $query->andWhere ('MONTH(regDateTime) = '.$month);
    
    $query->andWhere("(ifnull(isRef1C,0)=1 and (refSupplierSchet+refClientSchet+refSupply+refPurch) = 0) 
                       or IFNULL(docURI,'') =''  or docArticleRef = 0");

    $list = $query->createCommand()->queryAll();
    $res=array();
    $n = date('t',strtotime($year."-".$month."-01"));

    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
        
    for ($i=0;$i<count($list) ; $i++ )
    {
      $d=$list[$i]['d'];
      $res[$d]['err']=$list[$i]['errN'] ; 
      $this->monthErr+=$list[$i]['errN'] ; 
    }

    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(regDateTime) as d',           
        ])
         ->from("{{%documents}}")         
         ->distinct()
         ->groupBy(['DATE(regDateTime)']);
    $query->andWhere ('YEAR(regDateTime) = '.$year);
    $query->andWhere ('MONTH(regDateTime) = '.$month);
    ;

    $list = $query->createCommand()->queryAll();    
              
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['all']=$list[$i]['N'] ; 
       $this->monthAll+=$list[$i]['N'] ; 
    }

    $this->debug[]=$query->createCommand()->getRawSql();    
/*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
  }
/****************/
  
 public function yandexDiskUpload($fname)
 {
 
      return false;
  }

      
  /************End of model*******************/ 
 }
