<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankExtract;

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

        
    public function rules()
    {
        return [            
            [['recordId', 'dataType', 'dataVal' ], 'default'],                        
            [['orgTitle','debetOrgTitle'], 'safe'],            
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
       $record->contragentType = $this->dataVal;
       $record->operationType = 0;
       break;
       case 'operationType':
       $record->operationType = $this->dataVal;
       break;
     }
    $record->save();
    return ['res' => true, 'val' =>$this->dataVal, 'id' => $this->recordId, 'dataType' => $this->dataType];
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

   
   $this->debug[]=$this->flt;
   
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
   
   $this->debug[]=$this->from;
   $this->debug[]=$this->to;
   
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
            'orgRef',
            'contragentType',
            'operationType',
            '{{%orglist}}.title as orgTitle'
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
    $this->debug[]=$sumQuery->createCommand()->getRawSql();    
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
            'orgTitle'
              ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /************End of model*******************/ 
 }
