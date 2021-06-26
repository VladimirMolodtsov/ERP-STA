<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use app\models\PriceList;
use app\models\PurchaseZakaz;

/**
 * Модель - Категория для товара 
 */


class CategoryForm extends Model
{
    
    public $zakazId = 0;
    
    public $type = 1; 
    public $category = 0; 
   
    public $pcntVal1= 0; 
    public $pcntVal2= 0; 
    public $pcntVal3= 0; 
    public $pcntVal4= 0; 
    
    public function rules()
    {
        return [
            [['type', 'category','pcntVal1', 'pcntVal2', 'pcntVal3', 'pcntVal4'  ], 'default'],
        ];
    }
/*****************/  
public function prepareZapros()
{
  $zaprosRecord= PurchaseZakaz::findOne([
  'id' => $this->zakazId,
  ]);
  if (empty($zakazRecord)) return false;      
  
  $priceRecord= PriceZakaz::findOne([
  'wareTitle' => $zaprosRecord->wareTitle,
  ]);
  if (empty($zakazRecord)) return false;      
  
  
  $orgRecord= OrgList::findOne($zakazRecord->refOrg);
  if (empty($orgRecord)) return false;      

  $this->orgTitle= $orgRecord->title;
  
  return $zakazRecord;
}


public function getCategoryType ()
{
  $list = Yii::$app->db->createCommand(
            'SELECT DISTINCT wareType  FROM {{%price}} ORDER BY wareType')->queryAll();
            
  $res=array();          
  for($i = 0 $i<count($list); $i++)
  {
   $k = $list[$i]['wareType'];   
   $res[$k]=$k;
  }    
    
}

/**/    
 }
 