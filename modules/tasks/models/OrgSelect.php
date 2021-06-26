<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 

use app\models\OrgList;

class OrgSelect extends Model 
{
    public $orgGrpTitle;
    public $orgTitle;
    public $refManager=0;  
    public $userId=0;
    public $userFIO =0;
        
    public function rules()
    {
        return [            
            [['orgGrpTitle', 'orgTitle', 'refManager','userFIO' ], 'safe'],            
        ];
    }
    

   /*****************************************************************/
   
   public function getManagerList()
   {
    $statusTitles = Yii::$app->db->createCommand("Select id, userFIO FROM {{%user}} where (roleFlg & (0x0004|0x0002)) order by userFIO ")->queryAll();
    
    $list = ArrayHelper::map($statusTitles, 'id', 'userFIO');      
    return $list;
   }
   /*****************************************************************/
   public function getOrgSelectProvider($params)
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count({{%orglist}}.id)")
            ->from("{{%orglist}} ") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
            ;
    
     $query->select([
            '{{%orglist}}.id',
            'title as orgTitle',
            'refManager',
            'userFIO',     
            ])
            ->from("{{%orglist}}") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
            ;
        
     /*
        public $orgGrpTitle;
        public $orgTitle;
        public $refManager=0;    
     */   
      if (($this->load($params) && $this->validate())) 
      {
           $query->andFilterWhere(['Like', 'title', $this->orgTitle]);
           $countquery->andFilterWhere(['Like', 'title', $this->orgTitle]);          
           
               $this->refManager = $this->userFIO;
      }
      
      if (!empty($this->refManager))
      {
           $this->userFIO = $this->refManager;
           $query->andFilterWhere(['=', 'refManager', $this->refManager]);
           $countquery->andFilterWhere(['=', 'refManager', $this->refManager]);
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
            'orgTitle',
            'userFIO',     
            ],
            'defaultOrder' => ['orgTitle' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   

                        
/**********************/    
}
