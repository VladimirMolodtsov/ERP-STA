<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankOperation;
use app\modules\bank\models\TblBankExtract;
use app\modules\bank\models\TblBankCheck;
use app\models\UserList;
/**
 * BankExtractCheck
 
 {{%bank_content}} - содержание подгруженной выписки
 {{%bank_extract}} - таблица с уникальными записями по выписке - сюда привязываемся для работы с финансовыми данными и 
 {{%bank_check}} - 
 */
 
 
 class BankExtractCheck extends Model
{
    
    public $debug;
    public $timeshift = 4*3600; //сдвиг по времени   
      
    public $refBankHeader = 0;
    public $refOpHeader = 0;    
    public $reportMonth = 0;
    public $reportYear = 0;
    
    public $dataArray = array();
    public $extractArray = array();
    public $operationArray = array();

    public $op_ownerTitle = "";
    public $op_orgTitle= "";

            
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['op_ownerTitle','op_orgTitle'], 'safe'],            
        ];
    }
/***********************/    
    
public function getCheckInfo ($extract_id, $operation_id)
{
    $record= TblBankCheck::findOne(['refBankHeader' => $extract_id, 'refOpHeader' =>$operation_id]);
    if (empty($record)) return false;
 
     
        
   return true;
}

/***********************/    
    
public function setExtractOperationLnk ($extract_id, $operation_id)
{
    $record= TblBankOperation::findOne( $operation_id);
    if (empty($record)) return false;
    
    $record->refBankExtract = $extract_id;
    $record->save();
   return true;
}
/***********************/    
    
public function setExtractChkStatus($extract_id,$status)
{
    $record= TblBankExtract::findOne( $extract_id);
    if (empty($record)) return false;
    
    $record->checkStatus = $status;
    $record->save();
   return true;
}

    /***********************/
public function resetReportDataTime()
{
     if (empty($this->reportMonth))$this->reportMonth = date("n"); 
     if (empty($this->reportYear))$this->reportYear = date("Y");

}

public function finalizeCheck()
{
   $curUser=Yii::$app->user->identity; 
   $record= TblBankCheck::findOne([
   'refBankHeader' => $this->refBankHeader,
   'refOpHeader'   => $this->refOpHeader,
   ] );
   
    if (empty($record)) 
    {
        $record= new TblBankCheck();
        $record->refBankHeader = $this->refBankHeader; 
        $record->refOpHeader   = $this->refOpHeader; 
    }
    $record->refManager = $curUser->id;
    $record->checkDateTime = date("Y-m-d h:i:s");
    $record->save();
}


public function getCheckedSum()
{
  

  $strSql="Select sum({{%bank_content}}.creditSum) as income, sum({{%bank_content}}.debetSum) as outcome from {{%bank_content}}, {{%bank_extract}}
           where {{%bank_content}}.refExtract = {{%bank_extract}}.id and  {{%bank_extract}}.checkStatus = 1 
           AND {{%bank_content}}.refBankHeader =:ref";                   
           
  return Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $this->refBankHeader)                    
                           ->queryAll();
}


public function getUnchecked()
  {
  
    $strsql = "SELECT count({{%bank_content}}.id) from {{%bank_content}} 
           left join  {{%bank_extract}} on {{%bank_extract}}.id = {{%bank_content}}.refExtract 
           where ifnull({{%bank_extract}}.checkStatus,0) = 0 AND {{%bank_content}}.refBankHeader=:refBankHeader"; 
   
     $extract= Yii::$app->db->createCommand($strsql)
     ->bindValue(':refBankHeader', $this->refBankHeader)->queryScalar();
     
     $strsql = "SELECT count({{%bank_op_content}}.id) from {{%bank_op_content}} 
           left join  {{%bank_operation}} on {{%bank_operation}}.id = {{%bank_op_content}}.refOperation 
           where ifnull({{%bank_operation}}.refBankExtract,0) = 0 and refBankOpHeader =:refOpHeader"; 
     
     $operation= Yii::$app->db->createCommand($strsql)
     ->bindValue(':refOpHeader', $this->refOpHeader)->queryScalar();
     
     return [
     'extract'   =>$extract,
     'operation' =>$operation,
     ];
     
  }
        
public function prepareCheckData($param)
{   
 
 /*
 SELECT DISTINCT  a.recordDate, a.debetINN, a.debetOrgTitle,  a.creditINN, a.creditOrgTitle,
 a.debetSum, a.creditSum,  b.id as extractId, IF(a.debetSum> 0,a.debetSum, -1*a.creditSum) as extractSum
 from rik_bank_content as a  left join rik_bank_extract as b on 
 (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )
 where a.refBankHeader = 19 and  b.id is not null
 order by a.recordDate

 */
 
 /*Берем банковскую выписку */
 $extractQuery  = new Query();
 
        $extractQuery->select ([
        'DATE(a.recordDate) as D', 
        'a.recordDate', 
        'a.debetINN', 
        'a.debetOrgTitle', 
        'a.creditINN', 
        'a.creditOrgTitle',
        'a.debetSum', 
        'a.creditSum',  
        'b.id as bankExtractRef',
        'b.extractType',
        'b.refChecker',
        '{{%user}}.userFIO',  
        'b.checkStatus'      
        ])
        ->from("{{%bank_content}} as a")                       
        ->leftJoin("{{%bank_extract}} as b","(a.refExtract = b.id)")
        ->leftJoin("{{%user}}","(b.refChecker =  {{%user}}.id )")
        ->where ("b.id is not null")
        ->andWhere(['=','a.refBankHeader', $this->refBankHeader])
        ->orderBy('a.recordDate ASC')
         ;
    
    
  
  
  $this->extractArray= $extractQuery->createCommand()->queryAll();  
        
/*
SELECT DISTINCT a1.ownerTitle,  a1.orgTitle, a1.orgINN, a1.regDate, a1.regNum, 
   a1.operationDate, a1.operationNum, a1.recordSum, a1.orgKPP, b1.id as operationId, b1.ref_bank_extract 
  from rik_bank_op_content as a1
  left join rik_bank_operation as b1 
  on (a1.refOperation =  b1.id )  
  where b1.id is not null
*/    
    
  $operationQuery  = new Query();
 
        $operationQuery->select ([
        'a1.id as opRef',  
        'a1.ownerTitle',  
        'a1.orgTitle', 
        'a1.orgINN', 
        'a1.regDate', 
        'a1.regNum', 
        'a1.operationDate', 
        'a1.operationNum', 
        'a1.recordSum', 
        'a1.orgKPP', 
        'b1.id as operationId', 
        'b1.refBankExtract'         
        ])
        ->from("{{%bank_op_content}} as a1")                       
        ->leftJoin("{{%bank_operation}} as b1","(a1.refOperation =  b1.id )")        
        ->where ("b1.id is not null")
        ->andWhere(['=','a1.refBankOpHeader', $this->refOpHeader])
        ->orderBy('a1.regDate ASC')
         ;
  
  $eN =count($this->extractArray);  
  if ($eN == 0) return false;   
  $this->operationArray = $operationQuery->createCommand()->queryAll();  
  $oN =count($this->operationArray);

  //заинитим
  for ($i=0;$i< $oN; $i++)
  {
      $this->operationArray[$i]['check']=0;  
  }
    
  /*Хитро сопоставим. Формально можно нагородить запросов, но походу на данном обьеме так эффективнее  */

  
          
  for ($iE=0;$iE< $eN; $iE++)
  {
  
    /*текущую в результирующий массив, пока полностью*/
    $this->dataArray[] = $this->extractArray[$iE];
    
    $this->dataArray[$iE]['opRef'] = 0;
    $this->dataArray[$iE]['opSum'] = 0;
    $this->dataArray[$iE]['opOrgTitle'] ="";
    $this->dataArray[$iE]['opRegDate'] = "";

   
    /*Ищем сопоставление*/           
      for ($iO=0;$iO< $oN; $iO++)
      {
        if ($this->operationArray[$iO]['check'] != 0 ) continue; /* уже использовано */
        if ($this->extractArray[$iE]['bankExtractRef'] == $this->operationArray[$iO]['refBankExtract']  )
        {
           /*Найдено подтвержденное сопоставление*/
            $this->operationArray[$iO]['check'] = 2;
        }
        else {
        
                        
        /*Расход*/
            if( $this->extractArray[$iE]['D'] != $this->operationArray[$iO]['regDate'] ) continue;
            if ($this->extractArray[$iE]['debetSum'] > 0)
            {

              if( $this->extractArray[$iE]['creditINN'] != $this->operationArray[$iO]['orgINN'] ) continue;              
              if( $this->extractArray[$iE]['debetSum'] != (-1)*$this->operationArray[$iO]['recordSum'] ) continue;        
              
            }
            if ($this->extractArray[$iE]['creditSum'] > 0)
            {
              if( $this->extractArray[$iE]['debetINN'] != $this->operationArray[$iO]['orgINN'] ) continue;
              if( $this->extractArray[$iE]['creditSum'] != $this->operationArray[$iO]['recordSum'] ) continue;            
            }

           $this->operationArray[$iO]['check'] = 1;                
        }
            
            $this->dataArray[$iE]['opRef'] = $this->operationArray[$iO]['opRef']; //
            $this->dataArray[$iE]['operationId'] = $this->operationArray[$iO]['operationId']; //
            $this->dataArray[$iE]['opSum'] = $this->operationArray[$iO]['recordSum']; //
            $this->dataArray[$iE]['opOrgTitle'] = $this->operationArray[$iO]['orgTitle']; //
            $this->dataArray[$iE]['opRegDate'] = $this->operationArray[$iO]['regDate']; // 
            break;           
      }//iO
        
   }//iE 
        
}        
        
public function getBankCheckProvider($params)		
   {
   
     $this->prepareCheckData($params);
   
      $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            "recordDate",
            "debetINN",
            "debetOrgTitle",
            "creditINN",
            "creditOrgTitle",
            "debetSum",
            "creditSum",
            "bankExtractRef",
            "extractType",
            "opRef",
            "opSum",
            "opOrgTitle",
            "opRegDate",            
            ],

            'defaultOrder' => [    'recordDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
        


 public function getFreeOperationProvider($params)
   {
    $query  = new Query();      
    $query->select ([
        'a1.id as op_opRef',  
        'a1.ownerTitle as op_ownerTitle',  
        'a1.orgTitle as op_orgTitle', 
        'a1.orgINN as op_orgINN', 
        'a1.regDate as op_regDate', 
        'a1.regNum as op_regNum', 
        'a1.operationDate as op_operationDate', 
        'a1.operationNum as op_operationNum', 
        'a1.recordSum as op_recordSum', 
        'a1.orgKPP as op_orgKPP', 
        'b1.id as operationId', 
        'b1.refBankExtract'         
        ])
        ->from("{{%bank_op_content}} as a1")                       
        ->leftJoin("{{%bank_operation}} as b1","(a1.refOperation =  b1.id )")        
        ->where ("refBankExtract =0 ")
        ->andWhere(['=','a1.refBankOpHeader', $this->refOpHeader])
        ->orderBy('a1.regDate ASC')
         ;

          
     
     $countquery  = new Query();
     $countquery->select (" count(a1.id)")
                ->from("{{%bank_op_content}} as a1")                       
                ->leftJoin("{{%bank_operation}} as b1","(a1.refOperation =  b1.id )")        
                ->where ("refBankExtract =0 ")
                ->andWhere(['=','a1.refBankOpHeader', $this->refOpHeader])
                ;

        
     if (($this->load($params) && $this->validate())) {
     
      $query->andFilterWhere(['like', 'a1.ownerTitle', $this->op_ownerTitle]);
      $countquery->andFilterWhere(['like', 'a1.ownerTitle', $this->op_ownerTitle]);

      $query->andFilterWhere(['like', 'a1.orgTitle', $this->op_orgTitle]);
      $countquery->andFilterWhere(['like', 'a1.orgTitle', $this->op_orgTitle]);
            
     }

     $command = $query->createCommand();     
     $count = $countquery->createCommand()->queryScalar();

     
     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 8,
               ],
               
               'sort' => [
               
               'attributes' => [
                'op_opRef',  
                'op_ownerTitle',  
                'op_orgTitle', 
                'op_orgINN', 
                'op_regDate', 
                'op_regNum', 
                'op_operationDate', 
                'op_operationNum', 
                'op_recordSum', 
                'op_orgKPP', 
                ],
               
               ],
               
          ]);


          
     return  $dataProvider;      
   }   
        
        
          
  /************End of model*******************/ 
 }
