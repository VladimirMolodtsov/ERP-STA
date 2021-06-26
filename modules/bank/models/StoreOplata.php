<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\TblDocuments;
use app\modules\bank\models\TblDocOpata;
use app\modules\bank\models\TblDocPayorder;
use app\modules\bank\models\TblDocPaydetail;
use app\modules\bank\models\TblDocExtractLnk;
use app\modules\bank\models\TblDocSupplierLnk;

use app\modules\bank\models\ClientBankExchange;
use app\modules\bank\models\ClientBankDocument;

use app\models\TblPurchSchetLnk;
/**
 * StoreOplata - Оплата Поставщику
   1. Регистрация документа (Банк-Оператор)
   2. Выбор документа на оплату 
 
 */
 
class StoreOplata extends Model
{

/*Поля*/

    public $id = 0; //'Номер регистрации документа',
    public $docIntNum = ""; //'Номер регистрации документа',  
    public $orgTitle = ""; //  'Контрагент',  
    public $refOrg = ""; // 

    public $docOrigDate = ""; //  'Оригинальная дата',
    public $refDocOplata = 0;
  
    public $docOrigNum = ""; //  'номер документа (оригинальная нумерация)',
    public $dateToOplata =0;
    public $totalSum =0;
    public $totalCount =0;
    public $curPayer =0;

    public $isSend = 0;

    public $extractStatus=0;


    public $y_from = 0;
    public $m_from = 0;
    public $y_to = 0;
    public $m_to = 0;
    public $fromDate="";
    public $toDate="";
    public $refSuppSchet =0;
    public $refSupplierOplata =0;
    public $suppSchetNum  ="";   
    public $suppSchetDate ="";  
    public $suppOrgTitle  =""; 
    public $suppSchetSum  ="";      
    public $docToOplataSum = 0;
    public $docShowNum = 0;

    public $flt ="";

    public $command;
    public $count;


    public $creditOrgTitle;
    public $extractSelSum;                 

    /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 

    public $overdueVal =0; 
    public $todayVal   =1; 
    public $tomorrowVal  =0; 
    public $furtherVal  =0; 




/*************************************/
    public function rules()
    {
        return [                              
            [['recordId', 'dataType', 'dataVal', 'dataId','overdueVal','todayVal','tomorrowVal','furtherVal' ], 'default'],                                   
            [['docIntNum', 'orgTitle', 'docOrigDate', 'refDocOplata', 'isSend', 'extractStatus', 
             'creditOrgTitle', 'extractSelSum', 'dateToOplata'], 'safe'],            
        ];
    }

/*************************************/    
/*************************************/
    public function saveAjaxData ()
    {
    $curUser=Yii::$app->user->identity;
    
    $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'isSwitch' => 0, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId, 
           ];   
        
    switch ($this->dataType)
    {
          case 'addPurch':
            $docRef = intval($this->recordId);
            $purchRef= intval($this->dataVal);
            $recordDoc = TblDocuments::findOne($docRef);
            if(empty($recordDoc)) return $res;
            $schetRef = $recordDoc->refSupplierSchet;
            
            $record = TblPurchSchetLnk::findOne([
            'schetRef' => $schetRef,
            'purchRef' => $purchRef,
            ]);
            if (empty($record)) $record = new TblPurchSchetLnk();
            if (empty($record)) return $res;
            $record->schetRef = $schetRef;
            $record->purchRef = $purchRef;
            $record->save();           
            $res['isSwitch'] = 0;            
            break;   
    
      case 'remove':
            $recordDoc = TblDocuments::findOne($this->recordId);
            if(empty($recordDoc)) return $res;
            $recordDoc->isOplate = 0;
            $res['isSwitch'] = 1;
            $recordDoc->save();            
            break;   
      case 'refOrg':
            $recordDoc = TblDocuments::findOne($this->recordId);
            if(empty($recordDoc)) return $res;
            $recordDoc->refOrg = intval($this->dataVal);
            $recordDoc->save();            
            break;
       case 'refDocOplata':            
            $recordDoc = TblDocuments::findOne($this->recordId);
            if(empty($recordDoc)) return $res;
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) {
                $record = new TblDocOpata();
                $record->refDocument = $this->recordId;  
                $record ->docOplateStatus = 1;
                $record ->extractStatus = 1; // ставим принято
                $record ->sumToOplate = $recordDoc -> docSum;
                $record ->NDS = $recordDoc -> docNDS;
                $record ->dateToOplata = date("Y-m-d", time());//на сегодня
                
                $docOrigTime=strtotime($recordDoc->docOrigDate);
                if (!empty($recordDoc->payPurpouse) ) {
                    $record ->payPurpose = $recordDoc->payPurpouse;
                }
                else {
                    if ($recordDoc->docTitle == 'счет')                   
                      $record ->payPurpose = "Оплата по счету № ".$recordDoc->docOrigNum." от ".date('d.m.Y',$docOrigTime);  
                    else
                      $record ->payPurpose = "Оплата по документу: ".$recordDoc->docTitle." № ".$recordDoc->docOrigNum." от ".date('d.m.Y',$docOrigTime);
                }
            } else
            {
              if ($record  ->docOplateStatus == 1){
                  $record ->docOplateStatus = 0; 
                  $record ->extractStatus = 0; 
              }
              else {
                  $record ->docOplateStatus = 1;             
                  $record ->extractStatus = 1; 
              }              
            }
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 1;           
            $res['val'] = $record ->docOplateStatus;             
       break;      
       case 'dateToOplata':            
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) return $res;
            $record ->dateToOplata = date("Y-m-d", strtotime($this->dataVal));
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 0;           
            $res['val'] = $record ->dateToOplata;             
            $res['val'] = date("Y-m-d", strtotime($this->dataVal));            
       break;
       case 'removeData':            
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) return $res;
            $record->delete();       
            $res['isSwitch'] = 1;              
       break;
       case 'payPurpose':            
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) return $res;
            $record ->payPurpose = mb_substr($this->dataVal,0, 150,'utf-8');
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 0;           
            $res['val'] = $record ->payPurpose;             
            
       break;

       case 'NDS':            
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) return $res;
            $record ->NDS = intval($this->dataVal);
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 0;           
            $res['val'] = $record ->NDS;             
            
       break;
       
       case 'extractStatus':            
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record)) return $res;               
              if ($record  ->extractStatus == 1) $record ->extractStatus = 0; 
              elseif ($record  ->extractStatus == 0) $record ->extractStatus = 1;               
              elseif ($record  ->extractStatus == 3) $record ->extractStatus = 1;               
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 1;           
            $res['val'] = $record ->extractStatus;             
       break;
       
       case 'sumToOplate':            
            $recordDoc = TblDocuments::findOne($this->recordId);
            if(empty($recordDoc)) return $res;
            $record = TblDocOpata::findOne($this->dataId);
            if(empty($record))  return $res;            
            $recordsOther = TblDocOpata::findAll([
             'refDocument' =>  $this->recordId
            ]);
            $sum =0;
            $find=count($recordsOther);
            for ($i=0;$i<count($recordsOther);$i++) {//Ищем другую не оплаченную запись
            if ($recordsOther[$i]->id == $this->dataId) continue; //мы ее обрабатываем
            $sum +=$recordsOther[$i]->sumToOplate;
                        if($recordsOther[$i]->extractStatus <2) $find = $i; //нашли                           
            }
            if ($find<count($recordsOther)) $recOther=$recordsOther[$find]; //Нашли
            else { // не нашли
                $recOther = new TblDocOpata();
                $recOther->refDocument = $this->recordId;  
                $recOther ->docOplateStatus = 1;
                $recOther ->extractStatus = 0; // ставим не принято
                $record ->dateToOplata = date("Y-m-d", time());
                $recOther ->NDS = $record ->NDS;
                
                $docOrigTime=strtotime($recordDoc->docOrigDate);
                if ($recordDoc->docTitle == 'счет') 
                  $recOther ->payPurpose = "Оплата по счету № ".$recordDoc->docOrigNum." от ".date('d.m.Y',$docOrigTime);  
                else
                  $recOther ->payPurpose = "Оплата по документу: ".$recordDoc->docTitle." № ".$recordDoc->docOrigNum." от ".date('d.m.Y',$docOrigTime);
            } 
            $recOther ->sumToOplate += $recordDoc->docSum - $sum - floatval($this->dataVal); //доплюсуем разницу
            $recOther->refManager =  $curUser->id;
            $recOther->save();    
            $record ->sumToOplate= floatval($this->dataVal);
            $record->refManager =  $curUser->id;
            $record->save();    
            $res['isSwitch'] = 1;           
            $res['val'] = $recOther ->sumToOplate;             
       break;             
     }

    
    $res['res'] = true;    
    return $res;
        
    }
/*****************************/    
/**** Providers **************/    
/*****************************/
public function getPayOrderDetailProvider  ($params)
{
   $query  = new Query();
    $query->select ([
        'id',
        'refOrder',
        'refDocOplata',
        'docType',
        'docNum',
        'docDate',
        'summ',
        'NDS',
        'beneficiaryTitle',
        'beneficiaryInn',
        'beneficiaryAccount',
        'beneficiaryBank1',
        'beneficiaryBik',
        'beneficiaryCorrAccount',
        'beneficiaryKpp',
        'payPurpose',
        'order',
        'refOrg',
        ])
         ->from("{{%doc_paydetail}}")
          ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%doc_paydetail}}.id)")
              ->from("{{%doc_paydetail}}")
            ;            

             $query->andWhere("refOrder =".intval($this->id));
        $countquery->andWhere("refOrder =".intval($this->id));


     if (($this->load($params) && $this->validate())) {

     }

        
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();


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
        'docType',
        'docNum',
        'docDate',
        'summ',
        'NDS',
        'beneficiaryTitle',
        'beneficiaryInn',
        'beneficiaryAccount',
        'beneficiaryBank1',
        'beneficiaryBik',
        'beneficiaryCorrAccount',
        'beneficiaryKpp',
        'payPurpose',
        'order',
            ],            
            
            'defaultOrder' => [  'id' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   


}
public function getPayOrdersProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
          '{{%doc_payorder}}.id',
          'creationDateTieme',
          'totalSum',
          'isSend',
          'sendDate',
          'fname',
          'refManager',
          'userFIO',  
          'haveDetail'
          ])
          ->from("{{%doc_payorder}}")
          ->leftJoin('{{%user}}','{{%user}}.id = {{%doc_payorder}}.refManager')
          ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%doc_payorder}}.id)")
          ->from("{{%doc_payorder}}")
          ->leftJoin('{{%user}}','{{%user}}.id = {{%doc_payorder}}.refManager')
            ;            

     if (($this->load($params) && $this->validate())) {

     }

     switch ($this->isSend)
     {
       case '1':
             $query->andWhere("isSend > 0");
        $countquery->andWhere("isSend > 0");
       break;
       case '2':
             $query->andWhere("isSend = 0");
        $countquery->andWhere("isSend = 0");
       break;
     }         

        
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();


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
            'creationDateTieme',
            'totalSum',
            'isSend',
            'sendDate',
            'userFIO',  
            ],            
            
            'defaultOrder' => [  'id' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
  }   
/***********************/

/* Список загруженных документов */
/*
 
UPDATE rik_documents, rik_supplier_schet_header
set rik_documents.refSupplierSchet = rik_supplier_schet_header.id
where rik_documents.refSupplierSchet = 0
AND rik_documents.ref1C_input = rik_supplier_schet_header.supplierRef1C
AND rik_documents.orgINN = rik_supplier_schet_header.orgINN 
 
*/
 public function prepareDocLoadList($params)
 {

  //Проверяем привязку за квартал
    $strSql = 'UPDATE {{%documents}}, {{%supplier_schet_header}}
    set {{%documents}}.refSupplierSchet = {{%supplier_schet_header}}.id
    where {{%documents}}.refSupplierSchet = 0
    AND {{%documents}}.ref1C_input = {{%supplier_schet_header}}.supplierRef1C
    AND {{%documents}}.orgINN = {{%supplier_schet_header}}.orgINN
    AND DATEDIFF(docOrigDate,NOW())<= 90';    
    Yii::$app->db->createCommand($strSql)->execute();                    


  //Проверяем исполнение платежа через 1С - Нужно для подшивки старых платежей
  /*  $strSql = 'insert into {{%doc_oplata}}
    (refDocument,  dateToOplata,  sumToOplate,  extractStatus,  refSupplierOplata,  refSupplierSchet)
    Select DISTINCT a.id, MAX(b.oplateDate),SUM(b.oplateSumm),4,MAX(b.id),a.refSupplierSchet
    FROM {{%supplier_oplata}} as b
    LEFT join {{%documents}} as a on a.refSupplierSchet =  b.supplierSchetRef
    where ifnull(a.refSupplierSchet,0) > 0 
    and     
    (SELECT COUNT(id) from {{%doc_oplata}} where refDocument = a.id) = 0
    and 
    (SELECT sum(oplateSumm) from {{%supplier_oplata}} where {{%supplier_oplata}}.supplierSchetRef = a.refSupplierSchet) > 0   
    AND DATEDIFF(docOrigDate,NOW())<= 90
    group by (a.id) ';       
    Yii::$app->db->createCommand($strSql)->execute();
    $strSql = "INSERT INTO  {{%doc_supplier_lnk}} (docOplataRef,supplierOplataRef,lnkSum)
    SELECT  id, refSupplierOplata, sumToOplate FROM {{%doc_oplata}} as a
    where
    ifnull(refSupplierOplata,0) > 0 and
    (SELECT COUNT(id) from {{%doc_supplier_lnk}} as b where `b`.docOplataRef = a.`id` ) = 0";
    Yii::$app->db->createCommand($strSql)->execute();                    
    */                    

   
    //создадим связи с платежками в 1с за последние 30 дней

    $strSql = "insert into {{%doc_supplier_lnk}} (docOplataRef,supplierOplataRef,lnkSum, isLnk)
    Select c.id, b.id, oplateSumm, 1  FROM {{%documents}} as a
    LEFT join {{%doc_oplata}} as c on (a.id = c.refDocument)
    LEFT join {{%supplier_oplata}} as b on b.supplierSchetRef = a.refSupplierSchet
    wherea.refSupplierSchet >0 AND DATEDIFF(NOW(),docOrigDate) > 0 and  DATEDIFF(NOW(),docOrigDate) < 30
    and  ifnull(c.id,0) > 0 and  ifnull(b.id,0) > 0
    and  (SELECT COUNT(id) from {{%doc_supplier_lnk}} where 
       {{%doc_supplier_lnk}}.docOplataRef = c.id 
       and {{%doc_supplier_lnk}}.supplierOplataRef = b.id) = 0 ";

/*
Select docIntNum, a.docNum, c.id, b.id, b.`debetSum`-b.`creditSum`, 1, 
(SELECT COUNT(id) from rik_doc_extract_lnk where 
       rik_doc_extract_lnk.docOplataRef = c.id 
       and rik_doc_extract_lnk.extractRef = b.id) as LnkRef,
       DATEDIFF(NOW(),docOrigDate), 
       ifnull(c.id,0) as oplataId,
       ifnull(b.id,0) as bankId, 
       c.id+10000 as extractDocNum       
   FROM rik_documents as a       
  LEFT join rik_doc_oplata as c  on (a.id = c.refDocument)
  LEFT join rik_bank_extract as b on (b.docNum = CAST((c.id+10000) as CHAR) And b.orgRef = a.refOrg )
  
   
  where    docIntNum in (6810,6794,5611,5541)
  
  13194
  13197
  
  SELECT * FROM rik_bank_extract where docNum in (13194,
  13197)
  
*/

//Проверяем исполнение платежа через выписку

// создадим связи за последние 30 дней
  $strSql = "insert into {{%doc_extract_lnk}} ( docOplataRef, extractRef, lnkSum, isLnk )
  Select c.id, b.id, b.`debetSum`-b.`creditSum`, 1  FROM {{%documents}} as a
  LEFT join {{%doc_oplata}} as c on (a.id = c.refDocument)
  LEFT join {{%bank_extract}} as b on (b.docNum = CAST((c.id+10000) as CHAR) And b.orgRef = a.refOrg )
  where  DATEDIFF(NOW(),docOrigDate) > 0 and  DATEDIFF(NOW(),docOrigDate) < 90
  and  ifnull(c.id,0) > 0 and  ifnull(b.id,0) > 0
  and  (SELECT COUNT(id) from {{%doc_extract_lnk}} where 
       {{%doc_extract_lnk}}.docOplataRef = c.id 
       and {{%doc_extract_lnk}}.extractRef = b.id) = 0 ";
  Yii::$app->db->createCommand($strSql)->execute();                           

// Пометим исполненым
/*
extractStatus < 5 AND
*/


  $strSql = 'UPDATE {{%doc_oplata}},  {{%doc_supplier_lnk}}
    SET extractStatus = 4 where extractStatus < 4 AND 
    isLnk = 1 AND
    {{%doc_oplata}}.id = {{%doc_supplier_lnk}}.docOplataRef';       
    Yii::$app->db->createCommand($strSql)->execute();          

    $strSql = 'UPDATE {{%doc_oplata}},  {{%doc_extract_lnk}}
    SET extractStatus = 5 where extractStatus < 5 AND 
    isLnk = 1 AND
    {{%doc_oplata}}.id = {{%doc_extract_lnk}}.docOplataRef';       
    Yii::$app->db->createCommand($strSql)->execute();          

    $strSql = 'UPDATE {{%doc_oplata}}, {{%doc_supplier_lnk}}, {{%doc_extract_lnk}}
    SET extractStatus = 6 where extractStatus < 6 AND 
    {{%doc_supplier_lnk}}.isLnk = 1 and {{%doc_extract_lnk}}.isLnk = 1
    AND {{%doc_oplata}}.id = {{%doc_supplier_lnk}}.docOplataRef
    AND {{%doc_oplata}}.id = {{%doc_extract_lnk}}.docOplataRef'
    ;       
    Yii::$app->db->createCommand($strSql)->execute();          

/*$strSql = 'UPDATE {{%doc_oplata}}, {{%documents}} ,  {{%bank_extract}}
    SET extractStatus = 4 
    where 
    extractStatus < 4
    AND 
    {{%doc_oplata}}.refDocument = {{%documents}}.id
    AND CAST(({{%doc_oplata}}.id+10000) as CHAR) = {{%bank_extract}}.docNum
    and {{%documents}}.refOrg= {{%bank_extract}}.orgRef ';       
    Yii::$app->db->createCommand($strSql)->execute();                    
*/

    $query  = new Query();
    $query->select ([
        '{{%documents}}.id',   
        'docIntNum',
        'regDateTime',
        'orgTitle',
        'docType',
        'docTitle',
        'docURI',
        'refDocHeader',
        'docOrigStatus',
        'docOrigNum',
        'docOrigDate',
        'docSum',
        'isFinance',
        'isOplate',
        'docNote', 
        'docGoal',
        'docClassifyRef',
        '{{%documents}}.orgINN',
        'ref1C_input',
        'ref1C_schet',
        'docOwner',
        '{{%documents}}.contragentType',
        'operationType',
        'isTTN',
        'isUTR',
        '{{%documents}}.refOrg',
        'ifnull({{%doc_oplata}}.id,0) as refDocOplata',
        'ifnull({{%doc_oplata}}.docOplateStatus,0) as docOplateStatus',        
        'dateToOplata',
        'sumToOplate',
        'payPurpose',
        'NDS',
        'ifnull(extractStatus,0) as extractStatus',
        'refSupplierOplata',
        '{{%documents}}.refSupplierSchet',
        '{{%doc_oplata}}.refSupplierSchet as oplRefSupplierSchet',
        '{{%orglist}}.orgFullTitle',        
        '{{%orglist}}.orgKPP',        
        '{{%org_accounts}}.orgBIK',        
        '{{%org_accounts}}.orgRS as orgAccount',        
        '{{%org_accounts}}.orgBank',        
        '{{%org_accounts}}.orgKS',
        '{{%org_accounts}}.flgKS',                                
        '{{%doc_oplata}}.dateToOplata',
        '{{%doc_oplata}}.refManager',
        'userFIO',
        'refAccount'
            ])
            ->from("{{%documents}}")
            ->leftJoin('{{%doc_oplata}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%documents}}.refOrg')            
            ->leftJoin('{{%org_accounts}}','{{%org_accounts}}.id = {{%documents}}.refAccount')            
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ->distinct()
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%documents}}.id)")
            ->from("{{%documents}}")
            ->leftJoin('{{%doc_oplata}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%documents}}.refOrg')
            ;            

        $query->andWhere(['=', 'isOplate', 1]);
        $countquery->andWhere(['=', 'isOplate', 1]);     

     
     if($this->flt=='pp')
     {
         $this->overdueVal = intval($this->overdueVal);
         $this->todayVal = intval($this->todayVal);
         $this->tomorrowVal = intval($this->tomorrowVal);
         $this->furtherVal = intval($this->furtherVal);

             $query->andWhere("{{%doc_oplata}}.extractStatus = 1");
        $countquery->andWhere("{{%doc_oplata}}.extractStatus = 1");
         
        $strWhere = "1=0"; 
        if ($this->overdueVal == 1 )       
        {
            $strWhere.=" OR dateToOplata < DATE(NOW())";
        }

        if ($this->todayVal == 1 )       
        {
            $strWhere.=" OR DATE(NOW())=dateToOplata ";
        }
  
        if ($this->tomorrowVal == 1 )       
        {
            $strWhere.=" OR DATEDIFF(dateToOplata, NOW()) = 1";
        }

        if ($this->furtherVal == 1 )       
        {
            $strWhere.=" OR DATEDIFF(dateToOplata, NOW()) > 1";
        }

             $query->andWhere($strWhere);
        $countquery->andWhere($strWhere);
         
     }
     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);
       $countquery->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);                 
       
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);     

     /*  $query->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]);
       $countquery->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]); */    
        
     /*  if (!empty($this->docOrigDate))
       {
       $query->andFilterWhere(['=', 'docOrigDate', date("Y-m-d",strtotime($this->docOrigDate))]);
       $countquery->andFilterWhere(['=', 'docOrigDate',  date("Y-m-d",strtotime($this->docOrigDate))]);       
       } */
     }
              
              
              
     switch ($this->docOrigDate)
     {
       case '1':
             $query->andWhere("DATEDIFF(NOW(), docOrigDate) < 3");
        $countquery->andWhere("DATEDIFF(NOW(), docOrigDate) < 3");
/*        $this->toDate   = date('Y-m-d');
        $this->fromDate = date('Y-m-d', time()-3*24*3600);*/
       break;
       case '2':
             $query->andWhere("DATEDIFF(NOW(), docOrigDate) < 10");
        $countquery->andWhere("DATEDIFF(NOW(), docOrigDate) < 10");
        
       break;
       case '3':
             $query->andWhere("DATEDIFF(NOW(), docOrigDate) < 30");
        $countquery->andWhere("DATEDIFF(NOW(), docOrigDate) < 30");

       break;
       case '4':
             $query->andWhere("DATEDIFF(NOW(), docOrigDate) < 90");
        $countquery->andWhere("DATEDIFF(NOW(), docOrigDate) < 90");        
       break;
     }         


     switch ($this->dateToOplata)
     {
       case '1':
             $query->andWhere(" DATEDIFF(dateToOplata,NOW() ) < 0");
        $countquery->andWhere(" DATEDIFF(dateToOplata,NOW() ) < 0");
       break;
       case '2':
             $query->andWhere("DATEDIFF(dateToOplata,NOW() )  = 0");
        $countquery->andWhere("DATEDIFF(dateToOplata,NOW() )  = 0");        
       break;
       case '3':
             $query->andWhere("DATEDIFF(dateToOplata,NOW() )  = 1");
        $countquery->andWhere("DATEDIFF(dateToOplata,NOW() )  = 1");

       break;
       case '4':
             $query->andWhere("DATEDIFF(dateToOplata,NOW() )  > 1");
        $countquery->andWhere("DATEDIFF(dateToOplata,NOW() )  > 1");        
       break;
       
       case '5':
             $query->andWhere("dateToOplata IS NULL");
        $countquery->andWhere("dateToOplata IS NULL");        
       break;
       
     }               


      if (!empty($this->fromDate))
       {
       $query->andFilterWhere(['>=', 'DATE(regDateTime)', date("Y-m-d",strtotime($this->fromDate))]);
       $countquery->andFilterWhere(['>=', 'DATE(regDateTime)',  date("Y-m-d",strtotime($this->fromDate))]);       
       } 

      if (!empty($this->toDate))
       {
       $query->andFilterWhere(['<', 'DATE(regDateTime)', date("Y-m-d",strtotime($this->toDate)+24*3600)]);
       $countquery->andFilterWhere(['<', 'DATE(regDateTime)',  date("Y-m-d",strtotime($this->toDate)+24*3600)]);       
       } 


     switch ($this->refDocOplata       )
     {
       case '1':
             $query->andWhere("ifnull({{%doc_oplata}}.id,0) > 0");
        $countquery->andWhere("ifnull({{%doc_oplata}}.id,0) > 0");
       break;
       case '2':
             $query->andWhere("ifnull({{%doc_oplata}}.id,0) = 0");
        $countquery->andWhere("ifnull({{%doc_oplata}}.id,0) = 0");
       break;
     }         

     switch ($this->extractStatus       )
     {
       case '1':
             $query->andWhere("{{%doc_oplata}}.extractStatus = 0");
        $countquery->andWhere("{{%doc_oplata}}.extractStatus = 0");
       break;
       case '2':
             $query->andWhere("{{%doc_oplata}}.extractStatus = 1");
        $countquery->andWhere("{{%doc_oplata}}.extractStatus = 1");
       break;
       case '3':
             $query->andWhere("{{%doc_oplata}}.extractStatus = 3");
        $countquery->andWhere("{{%doc_oplata}}.extractStatus = 3");
       break;
       case '4':
             $query->andWhere("{{%doc_oplata}}.extractStatus = 4");
        $countquery->andWhere("{{%doc_oplata}}.extractStatus = 4");
       break;
       
     }         


     
     /*switch ($this->docIntNum)
     {
       case '1':
             $query->andWhere("DATEDIFF(NOW(), regDateTime) < 3");
        $countquery->andWhere("DATEDIFF(NOW(), regDateTime) < 3");
       break;
       case '2':
             $query->andWhere("DATEDIFF(NOW(), regDateTime) < 10");
        $countquery->andWhere("DATEDIFF(NOW(), regDateTime) < 10");
       break;
       case '3':
             $query->andWhere("DATEDIFF(NOW(), regDateTime) < 30");
        $countquery->andWhere("DATEDIFF(NOW(), regDateTime) < 30");
       break;
       case '4':
             $query->andWhere("DATEDIFF(NOW(), regDateTime) < 90");
        $countquery->andWhere("DATEDIFF(NOW(), regDateTime) < 90");
       break;
     }     */    



    $sumquery  = new Query();
    $sumquery ->select ([
        'sum(sumToOplate)',
         ])
        ->from("{{%documents}}")
        ->leftJoin('{{%doc_oplata}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')      
         ;           
       $sumquery->andWhere("{{%doc_oplata}}.extractStatus = 1");
       $sumquery->andWhere("{{%documents}}.refOrg > 0");
       
    $this->totalSum = $sumquery->createCommand()->queryScalar(); 

    $cntquery  = new Query();
    $cntquery ->select ([
        'count({{%documents}}.id)',
         ])
        ->from("{{%documents}}")
        ->leftJoin('{{%doc_oplata}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')      
         ;           
       $cntquery->andWhere("{{%doc_oplata}}.extractStatus = 1");
       $cntquery->andWhere("{{%documents}}.refOrg > 0");
       
    $this->totalCount = $cntquery->createCommand()->queryScalar(); 


        
    $this->command = $query->createCommand(); 
    $this->count   = $countquery->createCommand()->queryScalar();
 
 
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
            'dateToOplata',  
            'docClassifyRef',          
            ],            
            
            'defaultOrder' => [  'docIntNum' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
  public function getPayerList ()
  {
      $strSql = "SELECT id, usedOrgTitle, bankAccount, remainSum, isCurrent from {{%control_bank_use}} 
                where isUsable = 1 ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      
      $resArray=[];
      
      for ($i=0;$i<count($list); $i++)
      {
        $resArray[$list[$i]['id']] = $list[$i]['usedOrgTitle']." ".$list[$i]['bankAccount']." (".$list[$i]['remainSum']." руб.)";   
          if($list[$i]['isCurrent'] == 1) $this->curPayer=$list[$i]['id'];
      }      
      return $resArray;
  }      
   
    
  /*******************************************/ 
  public function preparePaymentOrder()
   {
    $query  = new Query();
    $query->select ([
        '{{%documents}}.id',
        'ifnull({{%doc_oplata}}.id,0) as refDocOplata',
        'docOrigNum',
        'docOrigDate',
        'docNote',
        'docSum',
        'dateToOplata',
        'payStatusKbk',
        'payStatusBase',
        'payStatusPeriod',
        'payStatusNumber',
        'payStatusDate',
        'payStatusType',
        'payCreaterStatus',
        'payType',
        'payOkato',
        'payCod',
        '{{%doc_oplata}}.payPurpose',
        'NDS',
        '{{%documents}}.refOrg',
        'refAccount',
        '{{%doc_oplata}}.sumToOplate',
        '{{%documents}}.contagentAccount',        
        '{{%orglist}}.orgFullTitle',
        '{{%orglist}}.orgINN',
        '{{%orglist}}.orgKPP',
         ])
         ->from("{{%documents}}")
         ->leftJoin('{{%doc_oplata}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')
         ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%documents}}.refOrg')
         ;           
       $query->andWhere("{{%doc_oplata}}.extractStatus = 1");
       $query->andWhere("{{%documents}}.refOrg > 0");

       
       
       
      $list = $query->createCommand()->queryAll(); 
      
      $exchange = new ClientBankExchange();
      $exchange->loadActivePayer();
      
      $N= count($list);
      $totalSum =0;
//echo "<pre>";    
//print_r($list);



      $orderRecord = new TblDocPayorder();
      if(empty($orderRecord)) return $res;

      $curUser=Yii::$app->user->identity;            
      
      $orderRecord->refManager=        $curUser->id;
      $orderRecord->creationDateTieme  = date("Y-m-d H:i");
      $orderRecord->save();
      $res = [ 'res' => false, 
             'chngRecord'  =>  [], 
             'totalSum'  => 0,
           ];   

      for ($i=0; $i<$N; $i++)
      {
      if (!empty($list[$i]['refAccount']))
          $accRecord= TblOrgAccounts::findOne($list[$i]['refAccount']);
      else
        $accRecord= TblOrgAccounts::findOne([
            'isDefault' => 1,
            'refOrg'    => $list[$i]['refOrg']]);     
    
        if (empty($accRecord)) continue;
        
        if(empty($list[$i]['orgFullTitle'])) continue;                  
        if(empty($list[$i]['orgINN'])) continue;                    
        if(empty($list[$i]['orgKPP']) && mb_strlen($list[$i]['orgINN'],'utf-8') != 12) continue;                            
        

        
        $now = strtotime(date('Y-m-d'));
        $tomorrow = $now + 24*3600;        
        $further  = $tomorrow + 24*3600;        
        $current = strtotime($list[$i]['dateToOplata']);
        
        if ($current < $now && $this->overdueVal != 1)continue; 
        if ($current >= $now && $current < $tomorrow && $this->todayVal != 1)continue;     
        if ($current >= $tomorrow && $current < $further && $this->tomorrowVal != 1)continue;     
        if ($current >= $further  && $this->furtherVal != 1)continue;     
        
        
        $doc=new ClientBankDocument();
        $doc->loadActivePayer();
        
        $doc->docType = 'Платежное поручение';
        $doc->docNum = 10000+$list[$i]['refDocOplata'];
        $doc->docDate=date("d.m.Y");    
        $doc->summ =$list[$i]['sumToOplate'];
        $totalSum+=$list[$i]['sumToOplate'];
        $doc->NDS  =$list[$i]['NDS'];
        $doc->beneficiaryTitle       ="ИНН ".$list[$i]['orgINN']." ".$list[$i]['orgFullTitle'];      
        $doc->beneficiary1           =$list[$i]['orgFullTitle'];      
        $doc->beneficiaryInn         =$list[$i]['orgINN'];        
        $doc->beneficiaryAccount     =$accRecord->orgRS;
        $doc->beneficiaryDealAccount =$accRecord->orgRS;
        $doc->beneficiaryBank1       =$accRecord->orgBank;
        $doc->beneficiaryBank2       =$accRecord->bankCity;
        $doc->beneficiaryBik         =$accRecord->orgBIK;               
        $doc->beneficiaryCorrAccount =$accRecord->orgKS;
        if(!empty($list[$i]['orgKPP']))$doc->beneficiaryKpp         =$list[$i]['orgKPP'];                
                
        $doc->statusKbk =$list[$i]['payStatusKbk'];        
        $doc->statusBase =$list[$i]['payStatusBase'];
        $doc->statusPeriod =$list[$i]['payStatusPeriod'];  
        $doc->statusNumber =$list[$i]['payStatusNumber'];        
        $doc->statusDate =$list[$i]['payStatusDate'];
        $doc->statusType =$list[$i]['payStatusType'];                
        $doc->createrStatus=$list[$i]['payCreaterStatus'];                
        $doc->payType=$list[$i]['payType'];                
        $doc->okato=$list[$i]['payOkato'];
        $doc->cod=$list[$i]['payCod'];                                

        
        $doc->payPurpose             =$list[$i]['payPurpose'];                
        $doc->order = 5;    
        $exchange->documentArray[]=$doc; 
    
        $this->storePayDoc($doc, $orderRecord->id, $list[$i]['refDocOplata']);    
        
        $record = TblDocOpata::findOne($list[$i]['refDocOplata']);
        if(empty($record)) continue;        
        $record ->extractStatus = 3; 
        $record->save();    

        $opl_record = TblDocOpata::findOne($list[$i]['refDocOplata']);
        if(empty($opl_record)) continue;        
        $opl_record->payorderRef=$orderRecord->id;
        $opl_record->save();    

        
        $res['chngRecord']['docId']         = $list[$i]['id'];           
        $res['chngRecord']['docOplataId']   = $list[$i]['refDocOplata'];        
        $res['chngRecord']['extractStatus'] = $record ->extractStatus;             
      }
      
    /*$mask = realpath(dirname(__FILE__))."/../uploads/kl_to_1c*.txt";    
    array_map("unlink", glob($mask));*/  
    $fname = "uploads/kl_to_1c".time().".txt";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;

    $exchange->saveFileExchange($fnamePath);
    $content = file_get_contents($fnamePath);
    $orderRecord->orderBody=mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
    $orderRecord->totalSum           =$totalSum;
    $orderRecord->fname              =$fname;
    $orderRecord->haveDetail=1;
    $orderRecord->save();           
  
    unlink ($fnamePath);
/*Меняем статус*/
      
      $res['fname'] = "modules/bank/".$fname; 
      $res['res'] = true; 
      $res['totalSum'] = $totalSum;       
      return $res;       
   }
   
public function getPayFile($id)
{
  $orderRecord = TblDocPayorder::findOne(intval($id));
  $orderRecord->isSend=1;
   $orderRecord->save();
  if(empty($orderRecord)) return $res;
  $content = mb_convert_encoding($orderRecord->orderBody, 'Windows-1251', 'UTF-8');
  $mask = realpath(dirname(__FILE__))."/../uploads/payfile*.txt";    
    array_map("unlink", glob($mask));
    $fname = "uploads/payfile".time().".txt";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    file_put_contents ( $fnamePath, $content );   
  
  return "modules/bank/".$fname;
}
   
/***************************************/
/* сохраним в базе */
public function storePayDoc($doc, $orderRecordId, $refDocOplata)
{
   $docRecord=new TblDocPaydetail();
   if (empty ($docRecord)) return false;

   $docRecord->refOrder = $orderRecordId;
   $docRecord->refDocOplata = $refDocOplata;
   
   $docRecord->docType=   $doc->docType;
   $docRecord->docNum =   $doc->docNum;
   $docRecord->docDate=   date("Y-m-d",strtotime($doc->docDate));    
   $docRecord->summ =     $doc->summ;
   $docRecord->NDS  =     $doc->NDS;
   
   $docRecord->beneficiaryTitle       =     $doc->beneficiaryTitle;      
   $docRecord->beneficiaryInn         =     $doc->beneficiaryInn;        
   $docRecord->beneficiaryAccount     =     $doc->beneficiaryAccount;
   $docRecord->beneficiaryBank1       =     $doc->beneficiaryBank1;
   $docRecord->beneficiaryBik         =     $doc->beneficiaryBik;               
   $docRecord->beneficiaryCorrAccount =     $doc->beneficiaryCorrAccount;
   $docRecord->beneficiaryKpp         =     $doc->beneficiaryKpp;                
   $docRecord->payPurpose             =     $doc->payPurpose;                
   $docRecord ->order =  $doc->order;    
   
   $docRecord -> save();   
   return true;
}  


/***************************************/
/*Привязка к поставщикам */
public function fixPeriod()
{

$m = date('n');
$y = date('Y');

  if ($this->y_from < 1970 || $this->y_from > 3000) $this->y_from = $y;
  if ($this->y_to   < 1970 || $this->y_to   > 3000) $this->y_to = $y;


  if ($this->m_from < 1 || $this->m_from > 12){
      $this->m_from = $m-1;
      if ($this->m_from <1) {
          $this->m_from = 12;
          $this->y_from--;
      }    
  }
  if ($this->m_to   < 1 || $this->m_to   > 12) $this->m_to = $m;

        

}


public function prepareExtractOplataData($params)
   {
       
 $this->getInfoByOplata();       
     $query  = new Query();
     $countquery  = new Query();


    $this->refDocOplata=intval($this->refDocOplata);
    if ($this->flt == 'showSel')
    {
     $countquery->select ("count(distinct {{%bank_extract}}.id)")
                   ->from("{{%bank_extract}}")
                   ->leftJoin('{{%doc_extract_lnk}}','{{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id')
                   ->leftJoin('{{%doc_oplata}}','{{%doc_extract_lnk}}.docOplataRef = {{%doc_oplata}}.id')
                   ->leftJoin('{{%documents}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')                  
                 ;
                  
     $query->select([ 
     '{{%bank_extract}}.id',
     '{{%bank_extract}}.docNum', 
     '{{%bank_extract}}.recordDate',
     '{{%bank_extract}}.creditSum',    
     '{{%bank_extract}}.debetSum', 
     '{{%bank_extract}}.creditOrgTitle', 
     ])
      ->from("{{%bank_extract}}")
      ->leftJoin('{{%doc_extract_lnk}}','{{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id')
      ->leftJoin('{{%doc_oplata}}','{{%doc_extract_lnk}}.docOplataRef = {{%doc_oplata}}.id')
      ->leftJoin('{{%documents}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')
      ->distinct()
      ;      
        
        
        $query->andWhere(['=', 'docOplataRef', $this->refDocOplata]);
        $countquery->andWhere(['=', 'docOplataRef', $this->refDocOplata]);                            
    } else {
  
     $countquery->select ("count(distinct {{%bank_extract}}.id)")
                   ->from("{{%bank_extract}}")
                 ;
                  
     $query->select([ 
     '{{%bank_extract}}.id',
     '{{%bank_extract}}.docNum', 
     '{{%bank_extract}}.recordDate',
     '{{%bank_extract}}.creditSum',    
     '{{%bank_extract}}.debetSum', 
     '{{%bank_extract}}.creditOrgTitle', 
     ])
      ->from("{{%bank_extract}}")
      ->distinct()
      ;      
    
    }
    $query->andWhere(['>', 'debetSum', 0]);
    $countquery->andWhere(['>', 'debetSum', 0]);                            
     
    if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['=', 'debetSum', $this->extractSelSum]);
        $countquery->andFilterWhere(['=', 'debetSum', $this->extractSelSum]);                              
        
        $query->andFilterWhere(['like', 'creditOrgTitle', $this->creditOrgTitle]);
        $countquery->andFilterWhere(['like', 'creditOrgTitle', $this->creditOrgTitle]);                      
        
     }

     
    if ($this->flt == 'showAll'){
     $query->andFilterWhere(['<=','recordDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','recordDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','recordDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','recordDate',  $this->y_from."-".$this->m_from."-"."01"]);    
    }
    
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
        
public function getExtractOplataProvider($params)
   {
        $this->prepareExtractOplataData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'docNum', 
            'recordDate',  
            'creditSum', 
            'debetSum', 
            'creditOrgTitle', 
                 ],
            'defaultOrder' => ['recordDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

/***************************************************/
public function prepareSupplierOplataData($params)
   {

 $this->getInfoByOplata();       

     $query  = new Query();
     $countquery  = new Query();

     
    $this->refDocOplata=intval($this->refDocOplata);     
    if ($this->flt == 'showSel')
    {
 
     $countquery->select ("count(distinct {{%supplier_oplata}}.id)")
                  ->from("{{%supplier_oplata}}")                       
        ->leftJoin('{{%doc_supplier_lnk}}','{{%doc_supplier_lnk}}.supplierOplataRef = {{%supplier_oplata}}.id')
        ->leftJoin('{{%doc_oplata}}','{{%doc_supplier_lnk}}.docOplataRef = {{%doc_oplata}}.id')
        ->leftJoin('{{%documents}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')       
                  
                 ;
                  
     $query->select([
        '{{%supplier_oplata}}.id',
        '{{%supplier_oplata}}.sdelkaNum', 
        '{{%supplier_oplata}}.sdelkaDate',   
        '{{%supplier_oplata}}.orgTitle', 
        '{{%supplier_oplata}}.ref1C',  
        '{{%supplier_oplata}}.oplateDate',  
        '{{%supplier_oplata}}.oplateSumm',  
        
        ])
        ->from("{{%supplier_oplata}}")                   
        ->leftJoin('{{%doc_supplier_lnk}}','{{%doc_supplier_lnk}}.supplierOplataRef = {{%supplier_oplata}}.id')
        ->leftJoin('{{%doc_oplata}}','{{%doc_supplier_lnk}}.docOplataRef = {{%doc_oplata}}.id')
        ->leftJoin('{{%documents}}','{{%doc_oplata}}.refDocument = {{%documents}}.id')       
        ->distinct();      

        $query->andWhere(['=', 'docOplataRef', $this->refDocOplata]);
        $countquery->andWhere(['=', 'docOplataRef', $this->refDocOplata]);                            
        
        
    } else {

     $countquery->select ("count(distinct {{%supplier_oplata}}.id)")
                  ->from("{{%supplier_oplata}}")                       
                 ;
                  
     $query->select([
        '{{%supplier_oplata}}.id',
        '{{%supplier_oplata}}.sdelkaNum', 
        '{{%supplier_oplata}}.sdelkaDate',   
        '{{%supplier_oplata}}.orgTitle', 
        '{{%supplier_oplata}}.ref1C',  
        '{{%supplier_oplata}}.oplateDate',  
        '{{%supplier_oplata}}.oplateSumm',  
        ])
        ->from("{{%supplier_oplata}}")                   
        ->distinct();      
    }        
    
    

    if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['=', 'oplateSumm', $this->extractSelSum]);
        $countquery->andFilterWhere(['=', 'oplateSumm', $this->extractSelSum]);
        
        $query->andFilterWhere(['like', '{{%supplier_oplata}}.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%supplier_oplata}}.orgTitle', $this->orgTitle]);                      
     }

    if ($this->flt == 'showAll'){    
     $query->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);        
    }


    
    
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
        
public function getSupplierOplataProvider($params)
   {
        $this->prepareSupplierOplataData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
           'id',
           'sdelkaNum', 
           'sdelkaDate',   
           'ref1C', 
           'oplateDate',  
           'oplateSumm', 
           'orgTitle',    
       ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

/***************************************************/
public function loadSuppSchet()
{
    if (empty($this->refSuppSchet)) return;
    $record = TblSupplierSchetHeader::findOne($this->refSuppSchet);
    if (empty($record))return;
    $this->suppSchetNum=$record->schetNum;
    $this->suppSchetDate=$record->schetDate;
    
    $strSql="SELECT title from {{%orglist}} where id=:id";
    $this->suppOrgTitle = Yii::$app->db->createCommand($strSql)
				->bindValue(':id',$record->refOrg)->queryScalar();  
    
    $strSql="SELECT SUM(goodSumm) from {{%supplier_schet_content}} where schetRef=:id";            
    $this->suppSchetSum = Yii::$app->db->createCommand($strSql)
				->bindValue(':id',$record->id)->queryScalar();              
                
}
  
public function getInfoByOplata()
{
    if (empty($this->refDocOplata)) return;
    $recordOplata = TblDocOpata::findOne($this->refDocOplata);
    if (empty($recordOplata)) return;
    $recordDoc = TblDocuments::findOne($recordOplata ->refDocument);
    if (empty($recordDoc)) return;
    $this->refSuppSchet = $recordDoc->refSupplierSchet;
    $this->docShowNum   = $recordDoc->docIntNum;
    $this->loadSuppSchet();
    $this->docToOplataSum = $recordOplata->sumToOplate; 
    
}
  
 public function saveLnkOplata  ()
    {
        
   $this->recordId = intval($this->recordId);
   $this->dataId   = intval($this->dataId);      
        
    $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId, 
           ];   
                       
    $res['actz'] ='start';                   
    switch ($this->dataType)
    {
      case 'link':
      $res['act'] ='link';
            $record = TblDocSupplierLnk::findOne([
              'docOplataRef' => $this->recordId,
              'supplierOplataRef' => $this->dataId,
            ]);      
            if(empty($record)){
             $record = new TblDocSupplierLnk();
             if(empty($record)) return $res;
              $record -> docOplataRef = $this->recordId;
              $record -> supplierOplataRef = $this->dataId;                
            }                 
            $record->isLnk = 1;            
            $record->save();            
            break;
            
      case 'unlink':
      $res['act'] ='unlink';
            $record = TblDocSupplierLnk::findOne([
              'docOplataRef' => $this->recordId,
              'supplierOplataRef' => $this->dataId,
            ]);
            if(empty($record)) return $res;
            $record->isLnk = 0;            
            $record->save();            
            
            $recordDocOplata = TblDocOpata::findOne($this->recordId);
            $recordDocOplata->extractStatus=0;
            $recordDocOplata->save();
            break;

      case 'linkExtract':
            $record = TblDocExtractLnk::findOne(
            [
              'docOplataRef' => $this->recordId,
              'extractRef' => $this->dataId,
            ]);
            if(empty($record)){
             $record = new TblDocExtractLnk();
             if(empty($record)) return $res;
              $record -> docOplataRef = $this->recordId;
              $record -> extractRef = $this->dataId;                
            }                    
            
            $record->isLnk = 1;                       
            $record->save();                        
            break;
            
      case 'unlinkExtract':
      $res['act'] ='unlinkExtract';
            $record = TblDocExtractLnk::findOne(            [
              'docOplataRef' => $this->recordId,
              'extractRef' => $this->dataId,
            ]);
            if(empty($record)) return $res;
            $record->isLnk = 0;               
            $record->save();            
            $recordDocOplata = TblDocOpata::findOne($this->recordId);
            $recordDocOplata->extractStatus=0;
            $recordDocOplata->save();            
            break;
     }

    $res['res'] = true;    
    return $res;
        
    }

/*
       $query->andWhere(['=', 'isOplate', 1]);
        $countquery->andWhere(['=', 'isOplate', 1]);     
*/
public function getStatPP ()
{
    $strSql= "SELECT COUNT({{%doc_oplata}}.id) as N, SUM(sumToOplate)as S FROM {{%doc_oplata}},{{%documents}}
    WHERE {{%doc_oplata}}.refDocument={{%documents}}.id and isOplate =1
    and {{%doc_oplata}}.extractStatus = 1 and dateToOplata < DATE(NOW())";
    $res['overdue'] = Yii::$app->db->createCommand($strSql)->queryOne();
				  
    $strSql= "SELECT COUNT({{%doc_oplata}}.id) as N, SUM(sumToOplate) as S FROM {{%doc_oplata}},{{%documents}}
    WHERE {{%doc_oplata}}.refDocument={{%documents}}.id and isOplate =1
    and{{%doc_oplata}}.extractStatus = 1 and dateToOplata = DATE(NOW())";
    $res['today'] = Yii::$app->db->createCommand($strSql)->queryOne();
    
    $strSql= "SELECT COUNT({{%doc_oplata}}.id) as N, SUM(sumToOplate) as S FROM {{%doc_oplata}},{{%documents}}
    WHERE {{%doc_oplata}}.refDocument={{%documents}}.id and isOplate =1
    and {{%doc_oplata}}.extractStatus = 1 and DATEDIFF(dateToOplata,DATE(NOW())) = 1";
    $res['tomorrow'] = Yii::$app->db->createCommand($strSql)->queryOne();

    $strSql= "SELECT COUNT({{%doc_oplata}}.id) as N, SUM(sumToOplate) as S FROM {{%doc_oplata}},{{%documents}}
    WHERE {{%doc_oplata}}.refDocument={{%documents}}.id and isOplate =1
    and {{%doc_oplata}}.extractStatus = 1 and DATEDIFF(dateToOplata,DATE(NOW())) > 1";
    $res['further'] = Yii::$app->db->createCommand($strSql)->queryOne();
    
    $N=0;
    $S=0;

    if ($this->overdueVal == 1){
       $N+= $res['overdue']['N'];    
       $S+= $res['overdue']['S'];
    }
    if ($this->todayVal == 1){
       $N+= $res['today']['N'];    
       $S+= $res['today']['S'];
    }
    if ($this->tomorrowVal == 1){
       $N+= $res['tomorrow']['N'];    
       $S+= $res['tomorrow']['S'];
    }    
    if ($this->furtherVal == 1){
       $N+= $res['further']['N'];    
       $S+= $res['further']['S'];
    }
    $res['itogo']['N']=$N;
    $res['itogo']['S']=$S;
    
    return $res;    
}

 public function switchPP()
    {
    $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId, 
             'value'    => 0,
             'ppTxt'    => 'Всего принято платежных поручений 0 на сумму 0.00'
           ];   

    if ($this->dataVal == 1) $res['value'] = 0;
                       else  $res['value'] = 1; 

    switch ($this->dataType)
    {
        case 'overdue':
            $this->overdueVal = $res['value'];
            $res['overdueVal'] =$this->overdueVal;
        break;
        case 'today':
            $this->todayVal = $res['value'];
        break;
        case 'tomorrow':
            $this->tomorrowVal = $res['value'];
        break;
        case 'further':
            $this->furtherVal = $res['value'];
        break;        
    }
    
    $data= $this->getStatPP ();
    
    
    $res['ppTxt'] = "Всего оплат ".$data['itogo']['N']." на сумму ".number_format($data['itogo']['S'],2,'.','&nbsp')." руб.";
    
    $res['res'] = true;    
    return $res;
    }

   
  /************End of model*******************/ 
 }
/*
ALTER TABLE `rik_oplata` ADD INDEX `rik_oplata_idx4` (`ref1C`);
ALTER TABLE `rik_oplata` ADD INDEX `rik_oplata_idx5` (`schetRef1C`);
*/
