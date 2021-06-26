<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use app\models\ZakazContent;
use app\models\ZakazList;
use app\models\OrgList;
/**
 * Модель - прайс
 */

 /*	
    supplyState
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
  0x00008 - 
*/  



class PriceForm extends Model
{
     
    public $wareTitle = ''; 
    public $wareType = ''; 
    public $setSort = '';
    
    public $command;
    public $count;
    public $zakazId = 0;
    
    public $orgTitle="";
    
    public function rules()
    {
        return [
            [['wareTitle', 'wareType' ], 'safe'],
        ];
    }
/*****************/  
public function preparePrice()
{
  $zakazRecord= ZakazList::findOne($this->zakazId);
  if (empty($zakazRecord)) return false;      
  $orgRecord= OrgList::findOne($zakazRecord->refOrg);
  if (empty($orgRecord)) return false;      

  $this->orgTitle= $orgRecord->title;
  
  return $zakazRecord;
}

public function isNeedRefresh()
{
    $lastUpdate = 	   Yii::$app->db->createCommand(
            'SELECT MAX(syncDateTime) from {{%price}}')->queryScalar();
    if (empty($lastUpdate)) $lastUpdate = 0;

    if ($lastUpdate+3600 < time()) return true;
    
    return false;
    
}

public function addToZakaz($zakazId, $priceid, $val, $ed)
{
    
    $priceRecord= PriceList::findOne($priceid);
    if (empty($priceRecord)) return;
    
    
    $zakazContentRecord = new ZakazContent();    
	$zakazContentRecord->refZakaz = $zakazId;    
	$zakazContentRecord->initialZakaz = $priceRecord->wareTitle;
    $zakazContentRecord->good    = $priceRecord->wareTitle;
    $zakazContentRecord->value = $val;
    $zakazContentRecord->ed = $ed;
    $zakazContentRecord->reserved = 0;
	$zakazContentRecord->save();	
   
   	$zakazHistoryRecord = new ZakazHistory;
	$zakazHistoryRecord->refZakaz = $zakazId;
    
	$zakazHistoryRecord->refParam =0;   
	$zakazHistoryRecord->proposal = $priceRecord->wareTitle;    
	$zakazHistoryRecord->propDate = date("Y-m-d h:i:s");
	$zakazHistoryRecord->save();
    
    
}

/***********************************************/ 
public function preparePriceData($params)
   {

    $countquery  = new Query();
    $countquery->select (
            "COUNT(id)"
            )
            ->from("{{%price}}")
            ;

   
    $query  = new Query();
    $query->select ([
            'id',
            'wareType',
            'wareTitle',            
            'wareWeight',
            'cntVal2',
            'cntVal3',
            'cntVal4',
            'weightVal2',
            'weightVal3',
            'weightVal4',            
       ])
            ->from("{{%price}}")
            ->distinct();
            			            
                                    

     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'wareTitle', $this->wareTitle]);
        $countquery->andFilterWhere(['like', 'wareTitle', $this->wareTitle]);

        
        $query->andFilterWhere(['like', 'wareType', $this->wareType]);
        $countquery->andFilterWhere(['like', 'wareType', $this->wareType]);
     }
   
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
   } 


  
 public function getPriceProvider($params)
   {
    
    $this->preparePriceData($params);    
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id',
            'wareType',
            'wareTitle',            
            'wareWeight',
            'cntVal2',
            'cntVal3',
            'cntVal4',
            'weightVal2',
            'weightVal3',
            'weightVal4',            
            ],
            'defaultOrder' => [	'wareType'=> SORT_ASC, 'wareTitle' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/*****************/ 
public function getZakazProvider()
   {
    
    
    $countquery  = new Query();
    $countquery->select ("COUNT(id)")->from("{{%zakazContent}}");

   
    $query  = new Query();
    $query->select ([
            'id',
            'good',
            'initialZakaz',            
            'count',
            'ed',
            'value',                   
       ])
            ->from("{{%zakazContent}}")
            ->distinct();
            			            
    $query->andWhere("isActive = 1");
    $query->andWhere("refZakaz = ".$this->zakazId);
    $countquery->andWhere("isActive = 1");
    $countquery->andWhere("refZakaz = ".$this->zakazId);
  
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id',
            'good',
            'initialZakaz',            
            'count',
            'ed',
            'value',                   
            ],
            'defaultOrder' => [	'id'=> SORT_ASC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   



/**/    
 }
 