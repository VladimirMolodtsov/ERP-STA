<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;

use app\models\TblPhone;

/**
 * Модель - телефонный справочник
 */


class PhoneForm extends Model
{
    public $orgTitle="";
    public $phone="";    
    public $phoneContactFIO='';    
    
    public $refOrg=0;
    
    public function rules()
    {
        return [
            [['orgTitle', 'phone', 'phoneContactFIO' ], 'safe'],
        ];
    }
/*****************/  
public function rmPhone ($id)
{
   $record = TblPhone::FindOne($id);
   if(empty($record)) return;
   
   $cnt = Yii::$app->db->createCommand('SELECT COUNT(DISTINCT(id) ) from {{%contact}}
                    where ref_phone=:ref_phone',
                    [':ref_phone' => $id,])->queryScalar();

   if ($cnt > 1) return;
   $record->delete();
    
}

public function getPhoneDetail ($id)
{
  $res = [ 'res' => false, 
             'id'  => $id, 
             'phone' => "", 
             'status' => '', 
             'phoneContactFIO' => '',              
             'isDefault' => '',
        ];   

    $record = TblPhone::FindOne(intval($id));
    if (empty($record)) return $res;
    
    $res['res'] = true;
    $res['phone'] = $record->phone;
    $res['status'] = $record->status;
    $res['phoneContactFIO'] = $record->phoneContactFIO;
    $res['isDefault'] = $record->isDefault;
           
  
    return $res;
}
/***********************************************/ 
public function getPhoneBookProvider($params)
   {


    $query  = new Query();
    $query->select ([
            '{{%phones}}.id',           
            'ref_org',
            'phone',
            '{{%orglist}}.isOrgActive',
            'status',
            'phoneContactFIO',
            'title as orgTitle'
            ]) ->from("{{%phones}}")
               ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%phones}}.ref_org" )
               ->distinct()
            ;
    $countquery  = new Query();
    $countquery->select (
            "COUNT({{%phones}}.id)"
            ) ->from("{{%phones}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%phones}}.ref_org" )
            ;
                                   

     if(!empty($this->refOrg))
     {
        $query->andWhere(['=', '{{%phones}}.ref_org', $this->refOrg]);
        $countquery->andWhere(['=', '{{%phones}}.ref_org', $this->refOrg]);
     }

     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
        
        $query->andFilterWhere(['like', 'phone', $this->phone]);
        $countquery->andFilterWhere(['like', 'phone', $this->phone]);
        
        $query->andFilterWhere(['like', 'phoneContactFIO', $this->phoneContactFIO]);
        $countquery->andFilterWhere(['like', 'phoneContactFIO', $this->phoneContactFIO]);
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
            'phone',
            'status',
            'phoneContactFIO',
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
 