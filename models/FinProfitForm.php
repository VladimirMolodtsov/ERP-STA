<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;        

/**
 * FinForm  - модель финансовых операций
 */
class FinProfitForm extends Model
{
    
    public $count;
    public $command;
    public $query;

    public $ownerOrgTitle;

    public $syncDateTime;
    public $strDate;
    public $sumValue;
    public $goodTitle;
    
    public function rules()
    {
        return [
           // [['schetStatus'], 'default'],
            [['ownerOrgTitle','goodTitle'], 'safe'],
        ];
    }
    

    
  /***************************/ 
  public function getProfitSrcProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%profit_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%profit_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $query  = new Query();
    $query->select ([ '{{%profit_content}}.id',  
                      'ownerOrgTitle', 
                      'regRecord',
                      'recordDate',
                      'recordNumber',
                      'goodTitle',
                      'goodEd',
                      'goodAmount',
                      'sellPrice',
                      'initPrice',
                      'profit',
                      'profitability',
                      ])
            ->from("{{%profit_content}}")
            ->andWhere("headerRef = ".$headerRef)
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%profit_content}}.id)")
            ->from("{{%profit_content}}")
            ->andWhere("headerRef = ".$headerRef)
            ;
    $sumquery  = new Query();        
    $sumquery->select (" sum(profit) ")
               ->from("{{%profit_content}}")
               ->andWhere("headerRef = ".$headerRef)
            ;
            
    if (($this->load($params) && $this->validate())) {
    
    
     $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
     $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
     $sumquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);

     $query->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     $sumquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     
     }
          
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    $this->sumValue =  $sumquery->createCommand()->queryScalar();                                     
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'ownerOrgTitle', 
                      'regRecord',
                      'recordDate',
                      'recordNumber',
                      'goodTitle',
                      'goodEd',
                      'goodAmount',
                      'sellPrice',
                      'initPrice',
                      'profit',
                      'profitability',         
            ],
            
            'defaultOrder' => [ 'recordDate' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/***********/

    
    

 /** end of object **/    
 }

 
 
 
 
