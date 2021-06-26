<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\models\ConfigTable;

/**
 * CfgForm  - Настройки
 */
class CfgForm extends Model
{
    
    
    /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    
    public $paramId=0;
    
    public $debug=[];
    
    public function rules()
    {
        return [
            
            [[ 'recordId','dataType','dataVal'], 'default'],         

        ];
    }   
    
   /**********************************/
   public function saveData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
           
      $record= ConfigTable::findOne($this->recordId);     
     if (empty($record)) return;
           
           
    switch ($this->dataType)
    {
        case 'keyValue':
           $record->keyValue = $this->dataVal;
           $record->save(); 
           $res['val'] =  $record->keyValue ;
           break;
        case 'keyTitle':
           $record->keyTitle = $this->dataVal;
           $record->save(); 
           $res['val'] =  $record->keyTitle ;
           break;
     }      
           
    $res['res'] = true;    
    return $res;

    }
   
   /**end of class**/

  /*********************************************************/
  public function getLeadParamProvider($params)
   {
    $query  = new Query();
    $query->select ([
            '{{%config}}.id',
            'keyTitle',
            'keyValue',
            ])
            ->from("{{%config}}")
            ->distinct();
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%config}}.id))")            
            ->from("{{%config}}")
            ->distinct();
            ;

     $query->andWhere(['>=', 'id', 2100]);
     $countquery->andWhere(['>=', 'id',  2100]);     
       
     $query->andWhere(['<', 'id', 2200]);
     $countquery->andWhere(['<', 'id',  2200]);     
            
    if (($this->load($params) && $this->validate())) {
             
     }

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
            'keyTitle',
            'keyValue',
            ],
            'defaultOrder' => [ 'id' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/

} 
