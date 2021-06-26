<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

class DocOrgList extends Model
{
    public $orgINN;
    public $title;

    public $searchINN ="";

    public $count;
    public $command;


    public function rules()
    {
        return [                              
             [['orgINN', 'title'], 'safe'],            
        ];
    }




public function prepareDocOrgList($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%orglist}}.id',   
        'title',
        'orgINN',
        'userFIO',
        'count({{%zakaz}}.id) as sdelok'
        ])
            ->from("{{%orglist}}")         
            ->leftJoin('{{%user}}','{{%user}}.id = {{%orglist}}.refManager')
            ->leftJoin('{{%zakaz}}','{{%zakaz}}.refOrg = {{%orglist}}.id')
            ->groupBy('{{%orglist}}.id')
            ;
            
    $countquery  = new Query();
    $countquery->select ("count(id)")
                ->from("{{%orglist}}");            
     
     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['LIKE', 'title', $this->title]);
       $countquery->andFilterWhere(['LIKE', 'title', $this->title]);                 
        
       $this->searchINN = $this->orgINN;
     }       

if (empty($this->orgINN)) $this->orgINN = $this->searchINN;
       $query->andFilterWhere(['LIKE', 'orgINN', $this->orgINN]);
       $countquery->andFilterWhere(['LIKE', 'orgINN', $this->orgINN]);           
        
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
 }


 public function getDocOrgListProvider($params)
   {
    $this-> prepareDocOrgList($params);
       
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
                        
            'attributes' => [        
                'title',
                'orgINN',
                'orgTitle',   
                'sdelok'  
            ],            
            
            'defaultOrder' => [  'sdelok' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

	
	
}
