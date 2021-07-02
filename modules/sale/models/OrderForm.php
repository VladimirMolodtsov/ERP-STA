<?php

namespace app\modules\sale\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


use app\models\OrgList;
use app\models\PhoneList;
use app\models\EmailList;
use app\models\ZakazList;
use app\models\ZakazContent;

/**
 * OrderForm - Планирование - суммарные значения
 */
 
 class OrderForm extends Model
{
    
    
    public $email ='';
    public $id =0;
    
    public $orgTitle ='';
    public $orgInn ='';
    public $orgKpp ='';
    public $orgAdress ='';
    public $orgPhone ='';
    public $contactFIO ='';
    public $orgRef = 0;
    
    /*** Filter *******/    
    public $wareTitle ='';
    public $wareTypeName ='';
    public $wareGrpTitle =''; 
    
    /**** Service *****/
    public $debug;
    
    
        
    public function rules()
    {
        return [            
            [['email', 'orgTitle', 'orgInn', 'orgKpp', 'orgAdress', 'orgPhone', 'contactFIO', 'id', 'orgRef' ], 'default'],                        
            [['wareTitle', 'wareGrpTitle', 'wareTypeName' ], 'safe'],            
            
            
            [['id' ], 'integer'],                        
            [['email' ], 'email'],                        
        ];
    }


    
 /****************************************************************************************/
 /**
 * Поиск организации по email
 * @param  $email - String адрес электронной почты
 * @return массив с параметрами организации
 * @throws Exception none
 */  
 
   public function getOrgByEmail($email)
   {     
    $res = [ 
                'res' => false,        
                'orgData' => [],
                'N' => 0
              ];   
    $list = Yii::$app->db->createCommand("Select {{%emaillist}}.ref_org as  orgRef, {{%emaillist}}.emailContactFIO as contactFIO,
        {{%orglist}}.title as orgTitle, {{%orglist}}.orgINN, {{%orglist}}.orgKPP, {{%orglist}}.contactPhone as orgPhone
        FROM {{%emaillist}}, {{%orglist}}  where {{%emaillist}}.ref_org = {{%orglist}}.id and {{%orglist}}.isOrgActive =1 
        and {{%emaillist}}.email = :email", 
        [':email'=>$email])->queryAll();             
    
    $N = count($list);
    $res['N']=$N;
    for ($i=0; $i<$N; $i++){  
    $res['orgData'][] =[
                'orgTitle' =>$list[$i]['orgTitle'],
                'orgInn' =>$list[$i]['orgINN'],
                'orgKpp' =>$list[$i]['orgKPP'],
                'orgAdress' =>'',
                'orgPhone' =>$list[$i]['orgPhone'],
                'contactFIO' =>$list[$i]['contactFIO'],
                'orgRef' =>$list[$i]['orgRef'],
                ];
    }     
    $res['res'] = true;    
    return $res;
    }    

  
 /****************************************************************************************/
 /**
 * Provider - список товаров (прайс)
 * @param  $params - request GET params (filter)
 * @return provider
 * @throws Exception none
 */  
 public function getWarePriceProvider ($params) 
 {

     $query  = new Query();
     $countquery  = new Query();

     
          $query->select([ 
                    '{{%ware_names}}.id',
                    '{{%ware_names}}.wareGrpRef',
                    '{{%ware_names}}.wareTypeRef',
                    'wareTitle',
                    'wareEd',
                    'v1',
                    'v2',
                    'v3',
                    'v4',
                    '{{%ware_type}}.wareTypeName',
                    '{{%ware_grp}}.wareGrpTitle'
                  ])
                  ->from("{{%ware_names}}")                                                  
                  ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
                  ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
                  ->distinct();      

     $countquery->select ("count(distinct {{%ware_names}}.id)")
                  ->from("{{%ware_names}}")                                                  
                  ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
                  ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
                 ;

                 
     $countquery->andWhere("ifnull({{%ware_names}}.wareTitle,'') != ''" );
          $query->andWhere("ifnull({{%ware_names}}.wareTitle,'') != ''" );
            
     $countquery->andFilterWhere(['=', '{{%ware_names}}.isInPrice', 1]);
          $query->andFilterWhere(['=', '{{%ware_names}}.isInPrice', 1]);
                  
     if (($this->load($params) && $this->validate())) 
     {
               $query->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
   
          $query->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
                   
          $query->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         
    
          $query->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);                 
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                    'id',
                    'wareTitle',
                    'wareTypeName',
                    'wareGrpTitle',
            ],
            'defaultOrder' => ['wareTypeName' => SORT_ASC , 'wareGrpTitle' => SORT_ASC , 'wareTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;     
    
 }

  
  
  /************End of model*******************/ 
 }
