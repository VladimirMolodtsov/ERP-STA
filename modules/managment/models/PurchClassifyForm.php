<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 



use app\modules\managment\models\TblControlPurchType;
use app\modules\managment\models\TblControlPurchMask;


/**
 * HeadMonitorForm  - монитор собственника
 */


class PurchClassifyForm extends Model
{

    public $id=0;
    

    public $debug=[];   
    public $err=[];   
     
    public $dataRequestId="";
    public $dataRowId="";
    public $dataType="";
    public $dataVal ="";
    public $dataValType =0;
    
    public $typeTitle="";    

    public $strData ="";
    
    public function rules()
    {
        return [
              [[ 'dataRequestId', 'dataRowId', 'dataType', 'dataVal', 'dataValType'], 'default'],
              [['typeTitle' ], 'safe'],
        ];
    }
    
   /***********************************/
   
   public function savePurchClassifyData ()
    {
      $res = ['res' => false, 
            'val' =>$this->dataVal, 
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType,
            ];
      
      $record = TblControlPurchMask::findOne($this->dataRequestId);
        if ( empty ($record )) return $res;

      switch ($this->dataType)
      {
        case 'typeRef': 
        $record -> typeRef = intval($this->dataVal);        
        $res['val'] = $record -> typeRef;                
        break; 

        case 'mask': 
        $record -> mask = $this->dataVal;        
        $res['val'] = $record -> mask;                
        break; 

        case 'useOrder': 
        $record -> useOrder = intval($this->dataVal);        
        $res['val'] = $record -> useOrder;                
        break; 
      }          
      $record -> save();        
      $res['res'] = true;
      return $res;
    }
    
   
   public function addPurchMask()
   {
     $record = new TblControlPurchMask();  
     if (empty($record )) return false;
     $record->typeRef =  Yii::$app->db->createCommand(
            'SELECT Max(id) from {{%control_purch_type}}')->queryScalar();
     $record->useOrder = 99999;
     $record->mask='%';
     $record->save();
     return true;  
   }
   
   public function removePurchMask($maskRef)
   {
     $record = TblControlPurchMask::findOne($maskRef);  
     if (empty($record )) return false;
     $record->delete();
     return true;  
   }
   
   public function getClassifyTypes()  
   {
       
    $list= Yii::$app->db->createCommand(
            'SELECT id, typeTitle from {{%control_purch_type}} ORDER BY id')->queryAll();  
            
    return ArrayHelper::map($list, 'id', 'typeTitle');    
   }
/******************************************/  
/*
*/
  public function getClassifyProvider($params)  
   {
                
           
    $query  = new Query();
    $query->select ([ '{{%control_purch_mask}}.id',  
                      '{{%control_purch_mask}}.typeRef',
                      'typeTitle',                      
                      'mask',                      
                      'useOrder',                      
                      ])
            ->from("{{%control_purch_type}}")
            ->leftjoin('{{%control_purch_mask}}', "{{%control_purch_mask}}.typeRef = {{%control_purch_type}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_purch_mask}}.id)")
            ->from("{{%control_purch_type}}")
            ->leftjoin('{{%control_purch_mask}}', "{{%control_purch_mask}}.typeRef = {{%control_purch_type}}.id" )
            ;     

     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);
        $countquery->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);            
     }

     
   $command = $query->createCommand();     
   $count   = $countquery->createCommand()->queryScalar();
    
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
                      'typeTitle',                      
                      'mask',                      
                      'useOrder',                      
            ],            
            'defaultOrder' => [ 'useOrder' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  

  
}
 
