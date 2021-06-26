<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 

use app\modules\managment\models\TblDocType;
use app\modules\managment\models\TblDocOperation;



/**
 * DocTypeCfgForm- настройка документов
 */


class DocTypeCfgForm extends Model
{

    public $id=0;
    
    
    public $controlTime=0;
    public $controlDate=0;
    public $headerRef=0;
   
    public $syncDateTime=0;        
        
    public $rowTitle    = "Новый параметр";

    public $debug=[];   
  
        
    public $dataRequestId = 0;
    public $dataType = '';
    public $dataVal = 0;
    
    
    public function rules()
    {
        return [
              [['rowTitle',  'dataRequestId', 'dataType', 'dataVal'], 'default'],
              [[ ], 'safe'],
        ];
    }

   /***************************/     
  public function rmDocType ($id)
  {
     $record = TblDocType::findOne($id);
     $record->delete();
  }  
  
  public function rmDocOperation ($id)
  {
     $record = TblDocOperation::findOne($id);
     $record->delete();
  }  

    
   /***************************/     
  public function addNewDocType ()
  {
     $record = new TblDocType();
     $record->typeTitle = 'Новый тип';    
     $record->save();
  }  
  
   /***************************/     
  public function addNewDocOperation ()
  {
     $record = new TblDocOperation();
     $record->operationTitle = 'Новая операция';    
     $record->save();
  }  
   /***************************/     
  public function saveData ()
  {
    switch ($this->dataType)
    {
       case 'typeTitleEdit':
        $record = TblDocType::findOne($this->dataRequestId);
        if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->dataRequestId];
        $record->typeTitle = $this->dataVal;
        $record->save();
       break;
       case 'typeTitleSelect':
        $record = TblDocOperation::findOne($this->dataRequestId);
        if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->dataRequestId];
        $record->refDocType = $this->dataVal;
        $record->save();
       break;
       case 'operationTitleEdit':
        $record = TblDocOperation::findOne($this->dataRequestId);
        if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->dataRequestId];
        $record->operationTitle = $this->dataVal;
        $record->save();
       break;
     }
    
    return ['res' => true, 'val' =>$this->dataVal, 'id' => $this->dataRequestId];
  }


  public function getTypeArray()
  {
      $strSql = "SELECT id, typeTitle from {{%doc_type}} ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      return ArrayHelper::map($list,'id','typeTitle');
}  

  

  public function getDocTypeProvider($params)
   {
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'typeTitle',
                      ])
            ->from("{{%doc_type}}")
            ->distinct();
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(id))")
            ->from("{{%doc_type}}")
            ;            
          
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
               'typeTitle'
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /***************************/     
     
  public function getDocOperationProvider($params)
   {
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'refDocType',                  
                      'operationTitle',
                      ])
            ->from("{{%doc_operation}}")            
            ->distinct();
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(id))")
            ->from("{{%doc_operation}}")
            ;            
          
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
               'id',  
               'refDocType',                  
               'operationTitle',
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

    
        
    
    
    
    
 }
 
