<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/**
 * ColdLoadedForm - модель форм для показа списка загруженных данных
 */
 
 class ColdLoadedForm extends Model
{
    public $debug;
    
    public $decription="";
    public $userFIO;
    public $orgTitle;
    
    
    public $orgHeaderRef=0;
    
    public function rules()
    {
        return [            
            [[ 'decription',  'userFIO', 'orgTitle' ], 'safe'],            
        ];
    }
  /**************************/
  public function getColdHeadersListProvider($params) 
 {

     $query  = new Query();
     $countquery  = new Query();

     
     
     $countquery->select ("count(distinct {{%cold_header}}.id)")
                  ->from("{{%cold_header}}")                                
                  ->leftJoin('{{%user}}','{{%user}}.id = {{%cold_header}}.refManager')                  
                 ;
                  
     $query->select([ 
                    '{{%cold_header}}.id',
                    'importDate',
                    'refManager',
                    'decription',     
                    'userFIO', 
                  ])
                  ->from("{{%cold_header}}")                                
                  ->leftJoin('{{%user}}','{{%user}}.id = {{%cold_header}}.refManager')                  
                  ->distinct();      
                  
    if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);                      
        $query->andFilterWhere(['like', 'decription', $this->decription]);
        $countquery->andFilterWhere(['like', 'decription', $this->decription]);                              
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
             'importDate',
             'decription',     
             'userFIO', 
            ],
            'defaultOrder' => ['importDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
    
}
  /**************************/    
  public function getColdContentProvider($params) 
 {
     $query  = new Query();
     $countquery  = new Query();

     $countquery->select ("count(distinct {{%cold_content}}.id)")
                  ->from("{{%cold_content}}");
                  
    $query->select([ 
        'orgINN',
        'orgTitle',
        'orgRazdel',
        'orgSubRazdel',
        'orgRubrica',
        'orgArea',
        'orgCity',
        'orgDistrict',
        'orgAdress',
        'orgX',
        'orgY',
        'orgIndex',
        'orgEMail',
        'orgPhoneList',
        'orgFAXList',
        'orgURL',
        'orgHeaderRef',
        'refManager',
        'startDate',
        'isUniqe',
     ])->from("{{%cold_content}}")->distinct();      
                  
         $query->andWhere("orgHeaderRef = ". $this->orgHeaderRef);
    $countquery->andWhere("orgHeaderRef = ". $this->orgHeaderRef);
                  
    if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
      
     
     
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
        'orgINN',
        'orgTitle',
        'orgRazdel',
        'orgSubRazdel',
        'orgRubrica',
        'orgArea',
        'orgCity',
        'orgDistrict',
        'orgAdress',
        'orgX',
        'orgY',
        'orgIndex',
        'orgEMail',
        'orgPhoneList',
        'orgFAXList',
        'orgURL',
        'orgHeaderRef',
        'refManager',
        'startDate',
        'isUniqe', 
            ],
            'defaultOrder' => ['orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;     
    
}
  /**************************/  
  
  /************End of model*******************/ 
 }
