<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblOrgList;

class DocOrgList extends Model
{
    public $orgINN='';
    public $orgKPP='';
    public $title;

    public $searchINN ="";
    public $searchTitle="";
    public $count;
    public $command;

    /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 



    public function rules()
    {
        return [          
             [['recordId', 'dataType', 'dataVal', 'dataId',], 'default'],            
             [['orgINN', 'title', 'orgKPP'], 'safe'],            
        ];
    }




public function prepareDocOrgList($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%orglist}}.id',   
        'title',
        'orgINN',
        'orgKPP',
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


            
            

       $query->andWhere('isOrgActive = 1');
       $countquery->andWhere('isOrgActive = 1');

     
     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['LIKE', 'orgKPP', $this->orgKPP]);
       $countquery->andFilterWhere(['LIKE', 'orgKPP', $this->orgKPP]);                 


       $this->searchTitle = $this->title; 
       $this->searchINN = $this->orgINN;
     }       

if (empty($this->title)) $this->title = $this->searchTitle;
   if (!empty($this->title)){
       $query->andFilterWhere(['LIKE', 'title', $this->title]);
       $countquery->andFilterWhere(['LIKE', 'title', $this->title]);                 
   }else 
       $this->orgINN ="";


if (empty($this->orgINN)) $this->orgINN = $this->searchINN;
   if (!empty($this->orgINN)){
       $query->andFilterWhere(['LIKE', 'orgINN', $this->orgINN]);
       $countquery->andFilterWhere(['LIKE', 'orgINN', $this->orgINN]);           
   }else 
       $this->orgINN ="";
   
   
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
                'orgKPP',
                'orgTitle',   
                'sdelok'  
            ],            
            
            'defaultOrder' => [  'sdelok' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
	
}
