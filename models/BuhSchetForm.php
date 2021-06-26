<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\TblBuhSchetHeader;
use app\models\TblBuhSchetContent;
use app\models\TblBuhSchetReport;
use app\models\TblBuhSchetCfg;
/**
 * BuhSchetForm  - модель контроль счетов 1C
 */


class BuhSchetForm extends Model
{

    public $id=0;
    
    
    public $stDate;
    public $enDate;

    
    public $syncDateTime=0;        
        
    public $reportTitle    = "Новый параметр";
    public $reportIsCredit = 0;                
        
        
    public $schet;
    public $subSchet;
    public $subSubSchet;
    
    public $debug=[];      
        
    public function rules()
    {
        return [
              [['reportTitle','reportIsCredit'  ], 'default'],
              [['schet','subSchet','subSubSchet'  ], 'safe'],
        ];
    }
  /***************************/ 
  public function addNewRow($isCredit)
  {
     $record = new TblBuhSchetReport();
     $record->reportTitle = 'Новый параметр';
     $record->isCredit = $isCredit;
     $record->save();
  }

  /***************************/ 
  public function saveData()
  {
     $record = TblBuhSchetReport::findOne($this->id);
     if (empty($record)) return false;
     $record->reportTitle = $this->reportTitle;     
     $record->save();
     return true;
  }
    
  /***************************/ 
  public function loadData()
  {
     $record = TblBuhSchetReport::findOne($this->id);
     if (empty($record)) return false;
     $this->reportTitle = $record->reportTitle;     
     $record->save();
  }
  
  public function switchSchetUse($reportRef, $rowRef )  
  {
  
     $record = TblBuhSchetCfg::findOne([
     'reportRef' => $reportRef,
     'rowRef'    => $rowRef     
     ]);
     if (empty($record)) 
     {
     $record = new TblBuhSchetCfg();
     $record->reportRef = $reportRef;
     $record->rowRef    = $rowRef;          
     $record->save();
     }
     else  $record->delete();
  
  }

  
  /***************************/ 

    public function checkData()
   {
        
    if ($this->stDate == 0 || $this->enDate == 0)
    {
        $headerRef =  Yii::$app->db->createCommand('SELECT MAX(id) FROM {{%buh_schet_header}}')->queryScalar();            
        $list      =  Yii::$app->db->createCommand('SELECT stDate, enDate FROM {{%buh_schet_header}} WHERE  id= :headRef', 
                      [ ':headRef' => $headerRef]       )->queryOne();            
                      
        $this->stDate = $list['stDate'];
        $this->enDate = $list['enDate'];
        
    } else
    {       
        $this->stDate = date('Y-m-d', strtotime($this->stDate));
        $this->enDate = date('Y-m-d', strtotime($this->enDate));

    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%buh_schet_header}} WHERE DATE(stDate) =:stDate and DATE(enDate) =:enDate', 
            [ ':stDate' => $this->stDate, ':enDate' => $this->enDate,])->queryScalar();        
            
    }        
    if (empty($headerRef))$headerRef=0; //от пустой строки
    
    //$this->debug[] = $headerRef;
    return $headerRef;

   }    
  /***************************/ 
  public function getBuhSchetData()
   {
        
    $headerRef = $this -> checkData();    
    
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%buh_schet_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    
    $strSubSql = "(SELECT schet, subSchet, subSubSchet, SNDT, SNKT, OBDT, OBKT, SKDT, SKKT, rowRef 
                   FROM {{%buh_schet_content}} WHERE headerRef = ".$headerRef.") as c";
    
    $query  = new Query();
    $query->select ([ 'a.id as reportId',  
                      'a.reportTitle',
                      'a.isCredit',
                      'c.schet',
                      'c.subSchet',
                      'c.subSubSchet',
                      'c.SNDT',
                      'c.SNKT',
                      'c.OBDT',
                      'c.OBKT',
                      'c.SKDT',
                      'c.SKKT',
                      ])
            ->from("{{%buh_schet_report}} as a")
            ->leftJoin("{{%buh_schet_cfg}} as b", "b.reportRef = a.id")
            ->leftJoin($strSubSql, "c.rowRef = b.rowRef")
            ->distinct();
            ;
        
                    
    return $query->createCommand()->queryAll(); 
    
   }   
  
  
  /***************************/ 
  public function getBuhSchetCfgProvider($params)
   {
           
   $strSubSql = "(SELECT id, rowRef FROM {{%buh_schet_cfg}} WHERE reportRef = ".$this->id.") as b";
               
           
    $query  = new Query();
    $query->select ([ 'a.id',  
                      'a.schet',
                      'a.subSchet',
                      'a.subSubSchet',
                      'ifnull(b.id,0) as lnkRef' 
                      ])
            ->from("{{%buh_schet_row}} as a")
            ->leftJoin("$strSubSql", "a.id=b.rowRef")            
            ->distinct();
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(a.id))")
            ->from("{{%buh_schet_row}} as a")
            ->leftJoin("$strSubSql", "a.id=b.rowRef")            
            ;
            
    if (($this->load($params) && $this->validate())) {
    
          $query->andFilterWhere(['like', 'a.schet', $this->schet]);
     $countquery->andFilterWhere(['like', 'a.schet', $this->schet]);

          $query->andFilterWhere(['like', 'a.subSchet', $this->subSchet]);
     $countquery->andFilterWhere(['like', 'a.subSchet', $this->subSchet]);

          $query->andFilterWhere(['like', 'a.subSubSchet', $this->subSubSchet]);
     $countquery->andFilterWhere(['like', 'a.subSubSchet', $this->subSubSchet]);
         
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
               'id',
               'schet',
               'subSchet',
               'subSubSchet',
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
  
  
/**/    
 }
 
