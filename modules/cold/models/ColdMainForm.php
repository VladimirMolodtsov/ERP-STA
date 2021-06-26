<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;



/**
 * ColdMainForm - модель стартовой формы менеджера холодных звонков
 */

 
 class ColdMainForm extends Model
{
    

    public $debug;
    
    public $city;
    public $orgTitle;
    public $userFIO;
    public $razdel;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['city', 'orgTitle', 'userFIO', 'razdel' ], 'safe'],            
        ];
    }

  /**************************/


public function getColdListProvider($params) 
{

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%orglist}}.id)")
                  ->from("{{%orglist}}")                                
                  ->leftJoin('{{%cold}}','{{%cold}}.refOrg = {{%orglist}}.id')                  
                  ->leftJoin('{{%adreslist}}','{{%adreslist}}.ref_org = {{%orglist}}.id')                  
                  ->leftJoin('{{%user}}','{{%user}}.id = {{%orglist}}.ref_user')                  
                 ;
                  
     $query->select([ 
                    '{{%orglist}}.id as orgRef',
                    'title as orgTitle',
                    '{{%cold}}.orgINN',
                    'city',
                    '{{%cold}}.id as coldRef',
                    'firstContactRef',
                    'firstEmail',
                    'firstContactPosition',
                    'supplyManagerFIO',
                    'contactEmail',
                    'secondContactRef',
                    'secondEmail',
                    'isInteres',
                    'userFIO', 
                    'orgFullTitle',
                    'headFIO',
                    'registartionDate',
                    'isOrgActive',
                    'regularity',
                    'razdel',
                    'mainWareGroup',
                  ])
                  ->from("{{%orglist}}")                                
                  ->leftJoin('{{%cold}}','{{%cold}}.refOrg = {{%orglist}}.id')                  
                  ->leftJoin('{{%adreslist}}','{{%adreslist}}.ref_org = {{%orglist}}.id')                  
                  ->leftJoin('{{%user}}','{{%user}}.id = {{%orglist}}.ref_user')                  
                  ->distinct();      

                  
      $query->andWhere(['=', 'isPreparedForSchet', 0]);
      $countquery->andWhere(['=', 'isPreparedForSchet', 0]);
                    
                  
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);                      

        $query->andFilterWhere(['like', 'city', $this->city]);
        $countquery->andFilterWhere(['like', 'city', $this->city]);                      

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);                      

        $query->andFilterWhere(['like', 'razdel', $this->razdel]);
        $countquery->andFilterWhere(['like', 'razdel', $this->razdel]);                      
        
        
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
                'orgRef',
                'orgTitle',
                'razdel',
                'city',
                //'coldRef',
                //'firstContactRef',
                //'firstEmailRef',
                //'firstContactPosition',
                'supplyManagerFIO',
                // 'secondContactRef',
                // 'secondEmailRef',
                'userFIO',                    
            ],
            'defaultOrder' => ['orgRef' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
         
    
}

  
    
    
  /**************************/    
  
  
  /************End of model*******************/ 
 }
