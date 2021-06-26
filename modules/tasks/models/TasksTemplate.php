<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;



/**
 * TasksMain - модель работы с задачами -старт
 */
 
 class TasksTemplate extends Model
{
    
    public $debug;
    
    
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
        ];
    }

  /**************************/


 public function getTaskTemplateListProvider ($params) 
 {

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%task_template_header}}.id)")
                  ->from("{{%task_template_header}}")                                
                  ->leftJoin('{{%modules}}','{{%task_template_header}}.moduleRef = {{%modules}}.id')                  
                 ;
                  
     $query->select([ 
                    '{{%task_template_header}}.id',
                    'templateTitle',
                    'weekDay',
                    'moduleTitle'
                  ])
                  ->from("{{%task_template_header}}")                                
                  ->leftJoin('{{%modules}}','{{%task_template_header}}.moduleRef = {{%modules}}.id')                  
                  ->distinct();      

                  
     if (($this->load($params) && $this->validate())) 
     {
          
        
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
                    'templateTitle',
                    'weekDay',
                    'moduleTitle'
            ],
            'defaultOrder' => ['moduleTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;     

 
 
    
 }

  
    
    
  /**************************/    
  
  
  /************End of model*******************/ 
 }
