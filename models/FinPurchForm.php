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
class FinPurchForm extends Model
{
    
    public $count;
    public $command;
    public $query;

    public $ownerOrgTitle;

    public $syncDateTime;
    public $strDate;
    public $sumValue;
    public $purchTitle;
    
    public function rules()
    {
        return [
           // [['schetStatus'], 'default'],
            [['ownerOrgTitle','purchTitle'], 'safe'],
        ];
    }
    

    
  /***************************/ 
  public function getPurchSrcProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_purch_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%control_purch_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $query  = new Query();
    $query->select ([ '{{%control_purch_content}}.id',  
                    'ownerOrgTitle',
                    'orgTitle',
                    'orgINN',
                    'orgKPP',
                    'orgRef',
                    'ref1C',
                    'purchDate',
                    'regRecord',
                    'purchTitle',
                    'purchEd',
                    'purchCount',
                    'purchSum',
                    ])
            ->from("{{%control_purch_content}}")
            ->andWhere("headerRef = ".$headerRef)
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%control_purch_content}}.id)")
            ->from("{{%control_purch_content}}")
            ->andWhere("headerRef = ".$headerRef)
            ;
    $sumquery  = new Query();        
    $sumquery->select (" sum(purchSum) ")
               ->from("{{%control_purch_content}}")
               ->andWhere("headerRef = ".$headerRef)
            ;
            
    if (($this->load($params) && $this->validate())) {
    
    
     $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
     $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
     $sumquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);

     $query->andFilterWhere(['like', 'purchTitle', $this->purchTitle]);
     $countquery->andFilterWhere(['like', 'purchTitle', $this->purchTitle]);
     $sumquery->andFilterWhere(['like', 'purchTitle', $this->purchTitle]);
     
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
                    'orgTitle',
                    'orgINN',
                    'orgKPP',
                    'ref1C',
                    'purchDate',
                    'purchTitle',
            ],
            
            'defaultOrder' => [ 'purchDate' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/***********/

    
    

 /** end of object **/    
 }

 
 
 
 
