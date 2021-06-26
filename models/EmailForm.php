<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;

use app\models\TblEmail;

/**
 * Модель - справочник электронной почты
 */


class EmailForm extends Model
{
    public $orgTitle="";
    public $phone="";
    
    public function rules()
    {
        return [
            [['orgTitle', 'phone' ], 'safe'],
        ];
    }
/*****************/  
public function rmEmail ($id)
{
   $record = TblEmail::FindOne($id);
   if(empty($record)) return;   
   $record->delete();
    
}
/***********************************************/ 
public function getEmailBookProvider($params)
   {


    $query  = new Query();
    $query->select ([
            '{{%emaillist}}.id',           
            'ref_org',
            'email',
            'status',
//            'phoneContactFIO',
            'title as orgTitle'
            ]) ->from("{{%emaillist}}")
               ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%emaillist}}.ref_org" )
               ->distinct()
            ;
    $countquery  = new Query();
    $countquery->select (
            "COUNT({{%emaillist}}.id)"
            ) ->from("{{%emaillist}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%emaillist}}.ref_org" )
            ;
                                   

     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);

        
        $query->andFilterWhere(['like', 'email', $this->email]);
        $countquery->andFilterWhere(['like', 'email', $this->email]);
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
            'email',
            'status',
    //'phoneContactFIO',
            'orgTitle'
            ],
            'defaultOrder' => ['orgTitle'=> SORT_ASC],
            ],            
        ]);
                
    return  $dataProvider;   
   } 
  

/*****************/ 
 
/**/    
 }
 