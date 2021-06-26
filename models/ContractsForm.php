<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;

use app\models\TblContracts;
/**
 * ContractsForm  - модель 
 */


class ContractsForm extends Model
{
   public $id="";

   public $refOrg =0;
   
   public $debug;    
   public $clientTitle; 
    
   
    public function rules()
    {
        return [
            [['id', ], 'default'],
            ['id', 'integer'],
            [['refOrg', 'clientTitle' ], 'safe'],
        ];
    }
   
   
   public function getContractsListProvider($params)
   {

    $query  = new Query();
    $query->select ([
        '{{%contracts}}.id',
        'creationTime',
        'clientTitle',
        'clientAdress',
        '{{%contracts}}.orgINN',
        '{{%contracts}}.orgKPP',
        'bankRekvesits',
        'contactorFull',
        'contractorShort',
        'contractorPost',
        'contractorReason',
        'oplatePeriod',
        'oplateStart',
        'dopCondition',
        'userFormer',
        'dateEnd',
        'phonesList',
        '{{%contracts}}.email',
        'siteUrl',
        'dateStart',
        'predoplata',
        'docUrl',
        'internalNumber',
        'contractNumber',
        'refOrg',
        'postoplate',
        '{{%orglist}}.title as orgTitle'
            ])
            ->from("{{%contracts}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%contracts}}.refOrg" )
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%contracts}}.id)")
            ->from("{{%contracts}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%contracts}}.refOrg" )
            ;            


     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['LIKE', 'clientTitle', $this->clientTitle]);
       $countquery->andFilterWhere(['LIKE', 'clientTitle',  $this->clientTitle]);       
       
       switch ($this->refOrg)
       {
         case 1:
            $query->andFilterWhere(['>', 'refOrg', 0]);
            $countquery->andFilterWhere(['>', 'refOrg',  0]);       
         break;         
           
         case 2:
            $query->andFilterWhere(['=', 'refOrg', 0]);
            $countquery->andFilterWhere(['=', 'refOrg',  0]);       
         break;         
           
       }
       
 
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
        '{{%contracts}}.id',
        'creationTime',
        'clientTitle',
        '{{%contracts}}.orgINN',
        '{{%contracts}}.orgKPP',
        'contractorPost',
        'contractorReason',
        'oplatePeriod',
        'oplateStart',
        'userFormer',
        'dateEnd',
        'phonesList',        
        'predoplata',
        'docUrl',
        'internalNumber',
        'refOrg',
        'postoplate',
        '{{%orglist}}.title as orgTitle'    
            ],            
            
            'defaultOrder' => [  'creationTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   

    
    
   }   
  
  /*****************************************/




/**/    
 }
 
