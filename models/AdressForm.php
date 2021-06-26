<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;

use app\models\AdressList;

/**
 * Модель - справочник адресов
 */


class AdressForm extends Model
{
    public $orgTitle="";
    public $city="";
    public $adress="";    
    public $index='';    
    
    public $refOrg=0;
    
    public function rules()
    {
        return [
            [['city', 'adress', 'index', 'orgTitle' ], 'safe'],
        ];
    }
/*****************/  
public function rmAdress ($id)
{
   $record = AdressList::FindOne($id);
   if(empty($record)) return;
   $record->delete();
}

public function getAdressDetail ($id)
{
  $res = [ 'res' => false, 
             'id'  => $id, 
             'index' => "", 
             'adress' => '', 
             'city' => '',              
             'isOfficial' => '',
        ];   

    $record = AdressList::FindOne(intval($id));
    if (empty($record)) return $res;
    
    $res['res'] = true;
    $res['index'] = $record->index;
    $res['adress'] = $record->adress;
    $res['city'] = $record->city;
    $res['isOfficial'] = $record->isOfficial;
           
  
    return $res;
}
/***********************************************/ 
public function getAdressBookProvider($params)
   {


    $query  = new Query();
    $query->select ([
            '{{%adreslist}}.id',           
            'ref_org',
            'adress',
            'isOfficial',
            'index',
            'city',
            'title as orgTitle'
            ]) ->from("{{%adreslist}}")
               ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%adreslist}}.ref_org" )
               ->distinct()
            ;
    $countquery  = new Query();
    $countquery->select (
            "COUNT({{%adreslist}}.id)"
            )  ->from("{{%adreslist}}")
               ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%adreslist}}.ref_org" )
            ;
                                   

     if(!empty($this->refOrg))
     {
        $query->andWhere(['=', '{{%adreslist}}.ref_org', $this->refOrg]);
        $countquery->andWhere(['=', '{{%adreslist}}.ref_org', $this->refOrg]);
     }

     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
        
        $query->andFilterWhere(['like', 'adress', $this->adress]);
        $countquery->andFilterWhere(['like', 'adress', $this->adress]);
        
        $query->andFilterWhere(['like', 'city', $this->city]);
        $countquery->andFilterWhere(['like', 'city', $this->city]);
        
        $query->andFilterWhere(['like', 'index', $this->index]);
        $countquery->andFilterWhere(['like', 'index', $this->index]);
        
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
            'id',           
            'adress',
            'isOfficial',
            'index',
            'city',
            'orgTitle'
            ],
            'defaultOrder' => ['id'=> SORT_ASC],
            ],            
        ]);
                
    return  $dataProvider;   
   } 
  

/*****************/ 
 
/**/    
 }
 