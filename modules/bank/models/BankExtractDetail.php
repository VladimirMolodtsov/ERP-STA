<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankExtract;
use app\modules\bank\models\TblDocExtractLnk;
use app\modules\bank\models\TblOrgList;

/**
 * BankExtract - Детализация выписки
 */
 
 class BankExtractDetail extends Model
{
    
    public $debug;
    
    public $extractRef = 0;
    public $headerRec = 0;
    public $maxRowDetail = 20;
    
    public $extractType = 0; // 0 - все, 1- (поступление денег), 2-(расход денег)',
    
    public $orgTitle = "";
    public $debetOrgTitle= "";

    public $from = "";
    public $to = "";
    public $flt = "";    
    public $cutType = 0;
    public $balance = array();

    
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;


    public $refExtract=0;
    public $lnkOrgTitle="";
        
    public $y_from = 0;
    public $m_from = 0;
    public $y_to = 0;
    public $m_to = 0;
        
    public $docNum= '';
    public $creditOrgTitle= '';
    public $extractSum= 0;
    public $description="";

    public $fromDate ="";
    public $toDate ="";
    
    public $overdueVal=1;
    public $yesterdayVal=1;
    public $todayVal=1;
    
    public $command;
    public $count;

    
    public function rules()
    {
        return [            
            [['recordId', 'dataType', 'dataVal' ], 'default'],                        
            [['docNum', 'creditOrgTitle','debetOrgTitle','lnkOrgTitle', 'orgTitle'], 'safe'],            
        ];
    }

/*****************************/    
 public function saveData()
 {
 
    $record = TblBankExtract::findOne($this->recordId);
    if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->recordId];
    switch ($this->dataType)
    {
       case 'contragentType':
       $record->contragentType = intval($this->dataVal);
       $record->operationType = 0;
       break;
       case 'operationType':
       $record->operationType = intval($this->dataVal);
       break;
       case 'orgType':
       $record->orgType = intval($this->dataVal);
       $record->orgDeal = 0;
       break;
       case 'orgDeal':
       $record->orgDeal = intval($this->dataVal);
       break;
       
       case 'selectDeal':
       
       $orgDealId = intval($this->dataVal);
       $record->orgDeal = $orgDealId;
       break;
       
       case 'createOrg':
       $res['action'] = 'createOrg';
       $record->orgRef = $this->createOrgByExtract($this->recordId);
       $recOrg=TblOrgList::findOne($record->orgRef);
        if(!empty($recOrg) )$res['orgTitle'] = $recOrg->title;
                       else $res['orgTitle'] = "N/A";                       
       $this->dataVal = $record->orgRef;                
       break;
       
       case 'refOrg':
       $record->orgRef = intval($this->dataVal);
       $recOrg=TblOrgList::findOne($record->orgRef);
        if(!empty($recOrg) )$res['orgTitle'] = $recOrg->title;
                       else $res['orgTitle'] = "N/A";                       
       break;
       
              
     }
    $record->save();
    return ['res' => true, 'val' =>$this->dataVal, 'id' => $this->recordId, 'dataType' => $this->dataType];
  }

  public function createOrgByExtract($refExtract){

    $query  = new Query();
    $query->select ([
            'a.id',
            'a.debetOrgTitle',            
            'a.debetRS',
            'b.debetBIK',            
            'a.debetINN',
            'b.debetKPP',            
            'a.creditOrgTitle',            
            'a.creditRs',      
            'b.creditBIK',                        
            'a.creditINN',
            'b.creditKPP',            
            'a.debetSum',
            'a.creditSum',
            'a.contrAgentBank',
            'b.debetKS',
            'b.creditKS',
            'a.extractType'
            ])            
            ->from("{{%bank_extract}} as a")
            ->leftJoin ("{{%bank_content}} as b", "b.id = a.refContent")
            ;
    $query->andWhere("a.id = :refExtract");
   
    $query->addParams([
                       ':refExtract' => $refExtract,
                     ]);    
    $data = $query->createCommand()->queryOne();

  
    if($data['extractType']==1){
    $orgTitle= $data['debetOrgTitle'];
    $inn= $data['debetINN'];
    $kpp= $data['debetKPP'];    
    $rs= $data['debetRS'];
    $ks= $data['debetKS'];    
    $bik= $data['debetBIK'];
    $bank= $data['contrAgentBank'];
    }else
    {
    $orgTitle= $data['creditOrgTitle'];
    $inn= $data['creditINN'];
    $kpp= $data['creditKPP'];    
    $rs= $data['creditRs'];
    $ks= $data['creditKS'];    
    $bik= $data['creditBIK'];
    $bank= $data['contrAgentBank'];
    }

    
    
 //  echo  $inn."<br>/n";
 //  echo  $kpp."<br>/n";
    
    $record = TblOrgList::findOne([
        'schetINN' => $inn,
        'orgKPP'   => $kpp
    ]);    
    if ( !empty($record)) return $record->id;

    $record = new TblOrgList();
    if ( empty($record)) return 0;

    $record->title = $orgTitle; 
    $record->orgFullTitle= $orgTitle;
    $record->schetINN = $inn;
    $record->orgKPP   = $kpp;
    $record->save();
    
    $accRec = new TblOrgAccounts();
    if ( empty($accRec)) return $record->id;
    $accRec->orgRS = $rs;
    $accRec->orgKS = $ks;
    $accRec->orgBIK = $bik;
    $accRec->orgBank = $bank;
    $accRec->isDefault = 1;
    $accRec->refOrg =  $record->id;
    $accRec->save();
    return $record->id;
 }  
  
 public function getDealsListArray ($typeId, $extractRef)
 {
        $record = TblBankExtract::findOne($extractRef);
        if (empty($record)) $orgRef=0;
        else $orgRef=$record->orgRef;
       
    if (empty($orgRef))        
       $strSql = "SELECT {{%bank_op_article}}.id, {{%bank_op_article}}.article from {{%bank_op_article}}, {{%bank_op_grp}} where 
       {{%bank_op_article}}.actionType= {{%bank_op_grp}}.flg and
       {{%bank_op_grp}}.id = ".$typeId." ORDER BY article";        
    else
       $strSql = "SELECT {{%bank_op_article}}.id, article from {{%bank_op_article}},{{%bank_op_grp}}, {{%org_deals}} where                      
       {{%bank_op_grp}}.flg = {{%bank_op_article}}.actionType and {{%org_deals}}.articleRef= {{%bank_op_article}}.id
       and state >0  and {{%bank_op_grp}}.id=".$typeId." AND {{%org_deals}}.refOrg = ".$orgRef." 
       ORDER BY article";        

       
       
       $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
       //$list[]=$orgRef;
       //Yii::$app->db->createCommand($strSql)->getRawSql();       
       $res=ArrayHelper::map($list,'id','article');     
       return $res;
 }
  
public function getOrgTypeArray()
   {
      $strSql = "SELECT id, grpTitle from {{%bank_op_grp}} ORDER BY sortOrder"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      $arr  = ArrayHelper::map($list,'id','grpTitle');
      $arr [0] = 'Не задан';
      return $arr;
   }  
  
  
/*****************************/    
 public function saveLnk()
 {
  $res =  ['res' => false, 'val'=>'-1', 'dataVal' =>$this->dataVal, 'id' => $this->recordId];

        $this->recordId = intval($this->recordId);
        $this->dataVal  = intval($this->dataVal);
    
    
    switch ($this->dataType)
    {
        
       case 'linkOperation':
       $record = TblBankOperation::findOne($this->dataVal);    
       if (empty($record)) return $res;
       $record->refBankExtract = $this->recordId;       
       $record->save();
       $res['val'] = $record->refBankExtract;
       break;
       
       case 'unLinkOperation':
       $record = TblBankOperation::findOne($this->dataVal);    
       if (empty($record)) return $res;
       $record->refBankExtract = 0;       
       $record->save();
       $res['val'] = $record->refBankExtract;
       break;
 
        
       case 'linkDoc':
       $record = TblDocExtractLnk::findOne(
       [
        'extractRef' => $this->recordId,
        'docOplataRef' => $this->dataVal
       ]);    

       if (empty($record)){
        $record = new TblDocExtractLnk();
        if (empty($record)) return $res;
        $record ->extractRef = $this->recordId;
        $record ->docOplataRef = $this->dataVal;
        }      
       $record->isLnk = 1;       
       $record->save();
       $res['val'] = $record->isLnk;
       break;
       
       case 'unLinkDoc':
       $record = TblDocExtractLnk::findOne(
       [
        'extractRef' => $this->recordId,
        'docOplataRef' => $this->dataVal
       ]);    
       if (empty($record)) return $res;
       $record->isLnk = 0;       
       $record->save();       
       $res['val'] = $record->isLnk;
       break;
       /*************/
       case 'linkSchet':
       $record = TblBankExtract::findOne($this->recordId);    
       if (empty($record)) return $res;
       $record->refSchet = $this->dataVal;       
       $record->save();
       $res['val'] = $record->refSchet ;
       break;
       
       case 'unLinkSchet':
       $record = TblBankExtract::findOne($this->recordId);    
       if (empty($record)) return $res;
       $record->refSchet  = 0;       
       $record->save();       
       $res['val'] = $record->refSchet ;
       break;       
     }
     
    $res['res'] = true;
    return $res;
  }
  
/*****************************/      
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
  
/*****************************/    
/**** Providers **************/    
/*****************************/
/* Выписка - детализация */
 public function getBankExtractionDetailProvider($params)
   {
   
   //не задана ссылка на выписку - открываем последнюю
   if ($this->extractRef == 0)
   {
      $this->extractRef = Yii::$app->db->createCommand('SELECT MAX(id) from {{%bank_header}}')->queryScalar();    
   }
   
   if (empty($this->extractRef)) $this->extractRef = 0;

  //ищем заголовок от выписки
  $this->headerRec = TblBankHeader::findOne($this->extractRef);
// if(empty($headerRec)) return false; // не нашли - тогда упс
// echo $this->extractRef;      
  
    $query  = new Query();    
    $query->select ([
            '{{%bank_content}}.id', 
            'refBankHeader', 
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'docNum', 
            'contrAgentBank', 
            'description', 
            'VO', 
            
            ])
            ->from("{{%bank_content}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%bank_content}}.id)")
            ->from("{{%bank_content}}")
            ;            
         $query->andWhere('{{%bank_content}}.refBankHeader = '.$this->extractRef );
    $countquery->andWhere('{{%bank_content}}.refBankHeader = '.$this->extractRef );
            
                        
     if (($this->load($params) && $this->validate())) {

        /*$query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]); */
             
     }

        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'docNum'
              ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
/*****************************/
/* Выписка по записям (уникальные записи только)- детализация */
/*****************************/
 public function getBankExtractionRecordsProvider($params)
   {

   
//   $this->debug[]=$this->flt;
   
   if ($this->flt == 'month') 
   {
     $this->from=date("Y-m")."-01";  
     $this->to=date("Y-m-d");         
   }
   if ($this->flt == 'now') 
   {
     $this->from=date("Y-m-d");  
     $this->to=date("Y-m-d");  
   }
   
//   $this->debug[]=$this->from;
//   $this->debug[]=$this->to;
   
    $query  = new Query();    
    $query->select ([
            '{{%bank_extract}}.id', 
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'docNum', 
            'contrAgentBank', 
            'description', 
            'VO', 
            'refOplata as refClientOplata',
            'refSupplierOplata',
            'extractType',
            'orgRef',
            '{{%bank_extract}}.contragentType',
            'operationType',
            '{{%orglist}}.title as orgTitle',
            'reasonDocNum',
            'reasonDocDate',
            'reasonText'
            ])
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
            ;
           
            
    $countquery  = new Query();
    $countquery->select ("count({{%bank_extract}}.id)")
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
            ;            

   $sumQuery  = new Query();    
   $sumQuery->select ("sum(creditSum) as credit, sum(debetSum) as debet")
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
            ;            
          
   if ($this->cutType == 1) 
   {    
     /*ограничение на контрагентов*/

        $query->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО"]);
        $countquery->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО"]);           
        $sumQuery->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО"]);           

        $query->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКИЙ БАНК ПАО СБЕРБАНК"]);
        $countquery->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКИЙ БАНК ПАО СБЕРБАНК"]);                   
        $sumQuery->andFilterWhere(['not like', 'debetOrgTitle', "СИБИРСКИЙ БАНК ПАО СБЕРБАНК"]);            
        
   }         
                        
   if ($this->extractType > 0) 
   {    
     // 0 - все, 1- (поступление денег), 2-(расход денег)',            
         $query->andWhere('{{%bank_extract}}.extractType = '.$this->extractType );
    $countquery->andWhere('{{%bank_extract}}.extractType = '.$this->extractType );           
    $sumQuery  ->andWhere('{{%bank_extract}}.extractType = '.$this->extractType );
   }                        

   if (!empty($this->from))
   {
             $query->andFilterWhere(['>=', 'DATE(recordDate)', $this->from]);
        $countquery->andFilterWhere(['>=', 'DATE(recordDate)', $this->from]);                  
        $sumQuery->andFilterWhere(['>=', 'DATE(recordDate)', $this->from]);                  
   }
 
   if (!empty($this->to))
   {
             $query->andFilterWhere(['<=', 'DATE(recordDate)', $this->to]);
        $countquery->andFilterWhere(['<=', 'DATE(recordDate)', $this->to]);                  
        $sumQuery->andFilterWhere(['<=', 'DATE(recordDate)', $this->to]);                  
   }

   
     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);
        $countquery->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);           
        $sumQuery->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);           

     
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);           
        $sumQuery->andFilterWhere(['like', 'title', $this->orgTitle]);           
     }

    //$this->debug[]=$query->createCommand()->getRawSql();
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    //$this->debug[]=$sumQuery->createCommand()->getRawSql();    
    $this->balance = $sumQuery->createCommand()->queryAll();
    
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
            'attributes' => [        
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'orgTitle',
            'docNum'
              ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/***************************/
public function getBankExtractErrors()
{
   $countquery  = new Query();
   $countquery->select ("count({{%bank_extract}}.id)")
            ->from("{{%bank_extract}}")
            ->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id')
            ->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id')
            ;
  
  $countquery->andWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);           
  $countquery->andWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);           

  
  $countquery->andWhere("ifnull({{%bank_extract}}.orgRef,0) = 0 
                   or ifnull({{%bank_operation}}.id,0)   = 0  
                   or ifnull({{%doc_extract_lnk}}.isLnk,0) = 0");
  
  $res['all']= $countquery->createCommand()->queryScalar();
  /*$res['doc']= $countquery->createCommand()->queryScalar();
  
  
   $countquery  = new Query();
   $countquery->select ("count({{%bank_extract}}.id)")
            ->from("{{%bank_extract}}")
            ->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id')
            ->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id')
            ;
  $countquery->andWhere(['=', 'ifnull({{%bank_operation}}.id,0)', 0]);                      
  $countquery->andFilterWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);           
  $countquery->andFilterWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);           

  $res['all']+= $countquery->createCommand()->queryScalar();
  $res['op']= $countquery->createCommand()->queryScalar();

  
  
   $countquery  = new Query();
   $countquery->select ("count({{%bank_extract}}.id)")
            ->from("{{%bank_extract}}")
            ->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id')
            ->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id')
            ;
  $countquery->andWhere(['=', 'ifnull({{%bank_extract}}.orgRef,0)', 0]);                      
  $countquery->andFilterWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);           
  $countquery->andFilterWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);           

  $res['all']+= $countquery->createCommand()->queryScalar();
  $res['org']= $countquery->createCommand()->queryScalar();
  */  

  //$this->debug[]=$res;

  
  return $res;
}

 public $detail=0;
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
        'COUNT({{%bank_extract}}.id) as errN',   
        'DAYOFMONTH(recordDate) as d',           
        ])
         ->from("{{%bank_extract}}")
         ->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id')
         ->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id')
        ->distinct()
        ->groupBy(['recordDate']);
  
    $query->andWhere ('YEAR(recordDate) = '.$year);
    $query->andWhere ('MONTH(recordDate) = '.$month);
    $query->andWhere("ifnull({{%bank_extract}}.orgRef,0) = 0 
                   or ifnull({{%bank_operation}}.id,0)   = 0  
                   or ifnull({{%doc_extract_lnk}}.isLnk,0) = 0");

                   
                   
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
        'DAYOFMONTH(recordDate) as d',           
        ])
         ->from("{{%bank_extract}}")         
         ->distinct()
         ->groupBy(['recordDate']);
    $query->andWhere ('YEAR(recordDate) = '.$year);
    $query->andWhere ('MONTH(recordDate) = '.$month);
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


/*****************************/
public function prepareBankExtractShowProvider($params)
   {

    $query  = new Query();    
    $query->select ([
            '{{%bank_extract}}.id', 
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'docNum', 
            'contrAgentBank', 
            'description', 
            'VO', 
            'refOplata as refClientOplata',
            'refSupplierOplata',
            'extractType',
            'orgRef',
            '{{%bank_extract}}.contragentType',
            'operationType',
            '{{%orglist}}.title as orgTitle',
            'reasonDocNum',
            'reasonDocDate',
            'reasonText',
            'description',
            'orgType',
            'orgDeal',
            'refSchet'
            ])
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
         //   ->leftJoin("{{%doc_extract_lnk}}", "{{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id")
            ;
           
            
    $countquery  = new Query();
    $countquery->select ("count({{%bank_extract}}.id)")
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
        //    ->leftJoin("{{%doc_extract_lnk}}", "{{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id")
            ;            
                   
    if ($this->detail == 1)
    {
         $countquery->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id');
         $countquery->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id');

         $query->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id');
         $query->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id');
     
         $countquery->andWhere("ifnull({{%bank_extract}}.orgRef,0) = 0 
                   or ifnull({{%bank_operation}}.id,0)   = 0  
                   or ifnull({{%doc_extract_lnk}}.isLnk,0) = 0");

         $query->andWhere("ifnull({{%bank_extract}}.orgRef,0) = 0 
                   or ifnull({{%bank_operation}}.id,0)   = 0  
                   or ifnull({{%doc_extract_lnk}}.isLnk,0) = 0");
            
    }
   
     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);
        $countquery->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);           

             $query->andFilterWhere(['like', 'creditOrgTitle', $this->creditOrgTitle]);
        $countquery->andFilterWhere(['like', 'creditOrgTitle', $this->creditOrgTitle]);           

             $query->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);           
        
        
             $query->andFilterWhere(['=', 'docNum', $this->docNum]);
        $countquery->andFilterWhere(['=', 'docNum', $this->docNum]);           


     }
     
             $query->andFilterWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);
        $countquery->andFilterWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);           
     
             $query->andFilterWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);
        $countquery->andFilterWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);           
     
     
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();

   }
   
   
   
  public function getBankExtractShowData ($params)		
   {
        $this->prepareBankExtractShowProvider($params);   		
        $dataList=$this->command->queryAll() ;
 
    $mask = realpath(dirname(__FILE__))."/../uploads/bankExtractShow*.csv";
    array_map("unlink", glob($mask));        
    $fname = "uploads/bankExtractShow".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    
   $orgTypesList= Yii::$app->db->createCommand("Select id, grpTitle   from  {{%bank_op_grp}}")->queryAll(); 
   $orgTypes = ArrayHelper::map($orgTypesList,'id','grpTitle');
   $orgTypes [0] = 'Не задан';

   $orgDealsList= Yii::$app->db->createCommand("Select id, article   from  {{%bank_op_article}}")->queryAll(); 
   $orgDeals = ArrayHelper::map($orgDealsList,'id','article');
   $orgDeals [0] = 'Не задан';
        
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Проведено"),
        iconv("UTF-8", "Windows-1251","П/П"),
        iconv("UTF-8", "Windows-1251","Плательщик"),
        iconv("UTF-8", "Windows-1251","ИНН Плательщика"),
        iconv("UTF-8", "Windows-1251","Получатель"),     
        iconv("UTF-8", "Windows-1251","ИНН Получателя"),        
        iconv("UTF-8", "Windows-1251","Расход"), 
        iconv("UTF-8", "Windows-1251","Приход"), 
        iconv("UTF-8", "Windows-1251","Назначение"),  
        iconv("UTF-8", "Windows-1251","Тип"), 
        iconv("UTF-8", "Windows-1251","Статья"),         
        iconv("UTF-8", "Windows-1251","№ в 1С"),       
        iconv("UTF-8", "Windows-1251","Статья в 1С"),       
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
           
        $strSql = "SELECT regNum, article from {{%bank_operation}} where refBankExtract =".$dataList[$i]['id']; 
        $val1C = Yii::$app->db->createCommand($strSql)->queryOne();                    
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['recordDate']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['docNum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetOrgTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetINN']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditOrgTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditINN']),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetSum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditSum']),  

		iconv("UTF-8", "Windows-1251",$dataList[$i]['description']),

        iconv("UTF-8", "Windows-1251",$orgTypes [$dataList[$i]['orgType']]),  
        iconv("UTF-8", "Windows-1251",$orgDeals[$dataList[$i]['orgDeal']]),  
  		
        iconv("UTF-8", "Windows-1251",$val1C['regNum']),  
        iconv("UTF-8", "Windows-1251",$val1C['article']),  
  		
  		
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return "/modules/bank/".    $fname;           
   }
   

/****************************************************************************************/
 /**
 * Выводит подготовленную для печати таблицу с выписками
 * @param $params - GET  строка с параметрами фильтрации для провайдера
 * @return $html - строка с HTML разметкой
 * @throws
 */
public function printBankExtractShowData ($params)		
   {
        $this->prepareBankExtractShowProvider($params);   		
        $dataList=$this->command->queryAll() ;
 

    
   $orgTypesList= Yii::$app->db->createCommand("Select id, grpTitle   from  {{%bank_op_grp}}")->queryAll(); 
   $orgTypes = ArrayHelper::map($orgTypesList,'id','grpTitle');
   $orgTypes [0] = 'Не задан';

   $orgDealsList= Yii::$app->db->createCommand("Select id, article   from  {{%bank_op_article}}")->queryAll(); 
   $orgDeals = ArrayHelper::map($orgDealsList,'id','article');
   $orgDeals [0] = 'Не задан';

   
   $html = "";

   $html .= "<p>Период с ".date("d.m.y",strtotime($this->fromDate))." по ".date('d.m.Y',strtotime($this->toDate))."</p>";

         
   $html .="<table class='table table-bordered' style='border:solid 2px; border-collapse: collapse' border='1'  >\n";       
   $html .="<tr>\n";        
   
       $html .="<th>Проведено</th>\n";        
       $html .="<th>П/П</th>\n";        
       $html .="<th>Плательщик</th>\n";
       $html .="<th>ИНН Плательщика</th>\n";
       $html .="<th>Получатель</th>\n";
       $html .="<th>ИНН Получателя</th>\n";
       $html .="<th>Расход</th>\n";
       $html .="<th>Приход</th>\n";                                                                                                                                                                                                                                                                              
       $html .="<th>Назначение</th>\n";
       $html .="<th>Тип</th>\n";                                                                                                                                                                                                                                                                              
       $html .="<th>Статья</th>\n";
       $html .="<th>№ в 1С</th>\n";
       $html .="<th>Статья в 1С</th>\n";                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
   $html .="</tr>\n";        
                                                                                                                                                                                                                                                                                     
    $debetSum=0;
    $creditSum=0;
    for ($i=0; $i< count($dataList); $i++)
    {        
        
           
        $strSql = "SELECT regNum, article from {{%bank_operation}} where refBankExtract =".$dataList[$i]['id']; 
        $val1C = Yii::$app->db->createCommand($strSql)->queryOne();     
        if(empty($val1C)){$val1C['regNum']=""; $val1C['article']="";}              
        
       $html .="<tr>\n";          
       $html .="<td>".$dataList[$i]['recordDate']."</td>\n";
       $html .="<td>".$dataList[$i]['docNum']."</td>\n";
       $html .="<td>".$dataList[$i]['debetOrgTitle']."</td>\n";
       $html .="<td>".$dataList[$i]['debetINN']."</td>\n";
       $html .="<td>".$dataList[$i]['creditOrgTitle']."</td>\n";
       $html .="<td>".$dataList[$i]['creditINN']."</td>\n";
       $html .="<td>".$dataList[$i]['debetSum']."</td>\n";
       $html .="<td>".$dataList[$i]['creditSum']."</td>\n";
       $html .="<td>".$dataList[$i]['description']."</td>\n";
       $html .="<td>".$orgTypes [$dataList[$i]['orgType']]."</td>\n";
       $html .="<td>".$orgDeals[$dataList[$i]['orgDeal']]."</td>\n";
       $html .="<td>".$val1C['regNum']."</td>\n";
       $html .="<td>".$val1C['article']."</td>\n";
       $html .="</tr>\n";        
        
       
       $debetSum  += $dataList[$i]['debetSum'];
       $creditSum += $dataList[$i]['creditSum'];
    }
    
       $html .="<tr>\n";          
       $html .="<td colspan='6' align='right'>Итого:</td>\n";
       $html .="<td>".$debetSum."</td>\n";
       $html .="<td>".$creditSum."</td>\n";
       $html .="<td></td>\n";
       $html .="<td></td>\n";
       $html .="<td></td>\n";
       $html .="<td></td>\n";
       $html .="<td></td>\n";
       $html .="</tr>\n";        
       

       $html .="</table>\n";           
        
        return $html;
   }      
/***********************************************************/
/*****************************/
public function prepareBankExtractShort($params)
   {

    $query  = new Query();    
    $query->select ([
            'DATE(recordDate) as recordDate', 
            'sum(debetSum) as debetSum', 
            'sum(creditSum) as creditSum', 
            ])
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
            ->groupBy('DATE(recordDate)')
            ;
           
                   
    if ($this->detail == 1)
    {

         $query->leftJoin('{{%bank_operation}}', '{{%bank_operation}}.refBankExtract={{%bank_extract}}.id');
         $query->leftJoin('{{%doc_extract_lnk}}', '{{%doc_extract_lnk}}.extractRef={{%bank_extract}}.id');
         $query->andWhere("ifnull({{%bank_extract}}.orgRef,0) = 0 
                   or ifnull({{%bank_operation}}.id,0)   = 0  
                   or ifnull({{%doc_extract_lnk}}.isLnk,0) = 0");
            
    }
   
     if (($this->load($params) && $this->validate())) {
             $query->andFilterWhere(['like', 'debetOrgTitle', $this->debetOrgTitle]);
             $query->andFilterWhere(['like', 'creditOrgTitle', $this->creditOrgTitle]);
             $query->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
             $query->andFilterWhere(['=', 'docNum', $this->docNum]);
     }
     
             $query->andFilterWhere(['>=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->fromDate))]);
             $query->andFilterWhere(['<=', 'DATE(recordDate)', date('Y-m-d',strtotime($this->toDate))]);
     
    $this->command = $query->createCommand(); 
   }  
   
public function printBankExtractShort ($params)		
   {
        $this->prepareBankExtractShort($params);   		
        $dataList=$this->command->queryAll() ;
   
   $html = "";
   $html .= "<p>Период с ".date("d.m.y",strtotime($this->fromDate))." по ".date('d.m.Y',strtotime($this->toDate))."</p>";

   $html .="<table class='table table-bordered' style='border:solid 2px; border-collapse: collapse' border='1'  >\n";       
   $html .="<tr>\n";        
   
       $html .="<th>Проведено</th>\n";        
       $html .="<th>Расход</th>\n";
       $html .="<th>Приход</th>\n";                                                                                                                                                                                                                                                                              
   $html .="</tr>\n";        
                                                                                                                                                                                                                                                                                     
    $debetSum=0;
    $creditSum=0;
    for ($i=0; $i< count($dataList); $i++)
    {        
       $html .="<tr>\n";          
       $html .="<td  style='padding:4px;'>".$dataList[$i]['recordDate']."</td>\n";
       $html .="<td  style='text-align:right; padding:4px;'>".number_format($dataList[$i]['debetSum'],2,'.','&nbsp;')."</td>\n";
       $html .="<td  style='text-align:right; padding:4px;'>".number_format($dataList[$i]['creditSum'],2,'.','&nbsp;')."</td>\n";
       $html .="</tr>\n";          
       
       $debetSum  += $dataList[$i]['debetSum'];
       $creditSum += $dataList[$i]['creditSum'];
    }
    
       $html .="<tr>\n";          
       $html .="<td style='text-align:right; padding:4px;'>Итого:</td>\n";
       $html .="<td style='text-align:right; padding:4px;'>".number_format($debetSum,2,'.','&nbsp;')."</td>\n";
       $html .="<td  style='text-align:right; padding:4px;'>".number_format($creditSum,2,'.','&nbsp;')."</td>\n";
       $html .="</tr>\n";          

       $html .="</table>\n";           
        
        return $html;
   }      
   
/*****************************/
 public function getBankExtractShowProvider($params)
   {

      $this->prepareBankExtractShowProvider($params);   
     
    
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
            'attributes' => [        
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'orgTitle',
            'docNum',
            'orgRef'
              ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   


/*****************************/
/* Ручная привязка документа к Выписке */
/*****************************/
 public function getDocExtractionLnkProvider($params)
   {
   
    $query  = new Query();    
    $query->select ([
            '{{%documents}}.id', 
            'orgTitle',
            'docTitle',
            'docOrigNum', 
            'docOrigDate', 
            '{{%doc_oplata}}.id as refDocOplata',            
            'sumToOplate',
            'payPurpose',
            'isLnk',
            'extractRef'
            ])
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_oplata}}", "{{%documents}}.id = {{%doc_oplata}}.refDocument")
            ->leftJoin("{{%doc_extract_lnk}}", "{{%doc_oplata}}.id = {{%doc_extract_lnk}}.docOplataRef")
            ;

           
    
    $countquery  = new Query();
    $countquery->select ("count({{%documents}}.id)")
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_oplata}}", "{{%documents}}.id = {{%doc_oplata}}.refDocument")
            ->leftJoin("{{%doc_extract_lnk}}", "{{%doc_oplata}}.id = {{%doc_extract_lnk}}.docOplataRef")
            ;            

        $query->andWhere(['>', 'ifnull({{%doc_oplata}}.id,0)', 0]);
        $countquery->andWhere(['>', 'ifnull({{%doc_oplata}}.id,0)', 0]);                            

        $query->andWhere(['=', 'isOplate', 1]);
        $countquery->andWhere(['=', 'isOplate', 1]);                            


    $this->refExtract=intval($this->refExtract);
    if ($this->flt == 'showSel')
    {
        $query->andWhere(['=', 'extractRef', $this->refExtract]);
        $countquery->andWhere(['=', 'extractRef', $this->refExtract]);                            
        $query->andWhere(['=', 'isLnk', 1]);
        $countquery->andWhere(['=', 'isLnk', 1]);                            

    }

   if (!empty($this->from))
   {
             $query->andFilterWhere(['>=', 'DATE(docOrigDate)', $this->from]);
        $countquery->andFilterWhere(['>=', 'DATE(docOrigDate)', $this->from]);                  
    }
 
   if (!empty($this->to))
   {
             $query->andFilterWhere(['<=', 'DATE(docOrigDate)', $this->to]);
        $countquery->andFilterWhere(['<=', 'DATE(docOrigDate)', $this->to]);                  
   }

   
     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'orgTitle', $this->lnkOrgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->lnkOrgTitle]);           
     }

    //$this->debug[]=$query->createCommand()->getRawSql();
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
            'attributes' => [                   
            'orgTitle',
            'docTitle',
            'docOrigNum', 
            'docOrigDate', 
            'refDocOplata',
            'sumToOplate',
              ],                       
            'defaultOrder' => [  'docOrigDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
/***************************************/
/*****************************/
/* Ручная привязка счета клиента к Выписке */
/*****************************/
 public function getSchetExtractionLnkProvider($params)
   {
   
    $query  = new Query();    
    $query->select ([
            '{{%schet}}.id', 
            'schetNum',
            '{{%schet}}.schetDate',
            'schetSumm', 
            'ref1C', 
            '{{%orglist}}.title as orgTitle',
            '{{%bank_extract}}.id as refExtract',
            'docNum'
            ])
            ->from("{{%schet}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%schet}}.refOrg")
            ->leftJoin("{{%bank_extract}}", "{{%bank_extract}}.refSchet = {{%schet}}.id")
            ;
    
    $countquery  = new Query();
    $countquery->select ("count({{%schet}}.id)")
            ->from("{{%schet}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%schet}}.refOrg")
            ->leftJoin("{{%bank_extract}}", "{{%bank_extract}}.refSchet = {{%schet}}.id")
            
            ;            


    $this->refExtract=intval($this->refExtract);
    if ($this->flt == 'showSel')
    {
        $query->andWhere(['=', '{{%bank_extract}}.id', $this->refExtract]);
        $countquery->andWhere(['=', '{{%bank_extract}}.id', $this->refExtract]);                            
    }

   if (!empty($this->from))
   {
             $query->andFilterWhere(['>=', 'DATE({{%schet}}.schetDate)', $this->from]);
        $countquery->andFilterWhere(['>=', 'DATE({{%schet}}.schetDate)', $this->from]);                  
    }
 
   if (!empty($this->to))
   {
             $query->andFilterWhere(['<=', 'DATE({{%schet}}.schetDate)', $this->to]);
        $countquery->andFilterWhere(['<=', 'DATE({{%schet}}.schetDate)', $this->to]);                  
   }

   
     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', '{{%orglist}}.title', $this->lnkOrgTitle]);
        $countquery->andFilterWhere(['like', '{{%orglist}}.title', $this->lnkOrgTitle]);           
     }

    //$this->debug[]=$query->createCommand()->getRawSql();
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
            'attributes' => [                               
            'schetNum',
            'schetDate',
            'schetSumm', 
            'ref1C', 
            'orgTitle',
            'refExtract',
            'docNum'
              ],                       
            'defaultOrder' => [  'schetDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   


/**************************************/
 public function loadExtractData($refExtract)
 {
   $record= TblBankExtract::findOne(intval($refExtract));     
   if (empty($record)) return;
   
  $this->docNum=$record->docNum;
  $this->debetOrgTitle=$record->debetOrgTitle;
  $this->creditOrgTitle=$record->creditOrgTitle;
  $this->extractSum=$record->debetSum+$record->creditSum;
  $this->description = $record->description; 
     
 }
 
 public function getStatPP ()
{
    $strSql= "SELECT COUNT({{%bank_extract}}.id) as N, SUM(creditSum)as S FROM {{%bank_extract}}
    left join {{%doc_extract_lnk}} on {{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id
    WHERE ifnull({{%doc_extract_lnk}}.isLnk,0) = 0 AND DATEDIFF(DATE(NOW()), DATE(recordDate)) > 1
    AND DATEDIFF(DATE(NOW()), DATE(recordDate)) < 91";
    $res['overdue'] = Yii::$app->db->createCommand($strSql)->queryOne();
				  
    $strSql= "SELECT COUNT({{%bank_extract}}.id) as N, SUM(creditSum)as S FROM {{%bank_extract}}
    left join {{%doc_extract_lnk}} on {{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id
    WHERE ifnull({{%doc_extract_lnk}}.isLnk,0) = 0 AND DATEDIFF(DATE(NOW()), DATE(recordDate)) = 1";
    $res['yesterday'] = Yii::$app->db->createCommand($strSql)->queryOne();
    
    $strSql= "SELECT COUNT({{%bank_extract}}.id) as N, SUM(creditSum)as S FROM {{%bank_extract}}
    left join {{%doc_extract_lnk}} on {{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id
    WHERE ifnull({{%doc_extract_lnk}}.isLnk,0) = 0 AND DATEDIFF(DATE(NOW()), DATE(recordDate)) = 0";
    $res['today'] = Yii::$app->db->createCommand($strSql)->queryOne();

    
  /*  $N=0;
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
    $res['itogo']['S']=$S;*/
    
    return $res;    
}

 
  /************End of model*******************/ 
 }
