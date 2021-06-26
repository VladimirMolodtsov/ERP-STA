<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;



/**
 * TasksList - Список выданных задач
 */
 
 class TasksList extends Model
{
    
    public $debug;
    
    public $eventStatus;
    public $userFIO;
    public $orgTitle;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['orgTitle', 'eventStatus', 'userFIO' ], 'safe'],            
        ];
    }

  
  /******/
    public function getIssuedTasksProvider($params)
   {
     $curUser=Yii::$app->user->identity;
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count({{%tasks}}.id)")
                  ->from("{{%tasks}}")
                  ->leftJoin("{{%user}}", "{{%user}}.id = executorRef")
                  ->leftJoin("{{%calendar}}", "{{%calendar}}.id = refCalendar")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tasks}}.refOrg")                                    
                  ;
    
     $query->select([     
                 '{{%tasks}}.id as taskId', 
                  'creationDate',
                  'startDate',
                  'planDate',
                  'deadline',                  
                  'creatorRef',
                  'executorRef',
                  'сurrentState',
                  'currentDate',
                  'endDate',
                  '{{%tasks}}.refOrg',
                  'taskTitle',
                  'refCalendar',
                  'userFIO',
                  'eventStatus',
                  'eventTime',
                  'event_date',
                  'refExecute',
                  '{{%orglist}}.title as orgTitle'
                  ])
            ->from("{{%tasks}}") 
            ->leftJoin("{{%user}}", "{{%user}}.id = executorRef")
            ->leftJoin("{{%calendar}}", "{{%calendar}}.id = refCalendar")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tasks}}.refOrg")                                    
            ;


    $countquery->where("{{%tasks}}.creatorRef=".$curUser->id);
         $query->where("{{%tasks}}.creatorRef=".$curUser->id);            
        

    if (($this->load($params) && $this->validate())) 
    {     
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);        
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);        
     
        $query->andFilterWhere(['=', 'eventStatus', $this->eventStatus]);        
        $countquery->andFilterWhere(['=', 'eventStatus', $this->eventStatus]);        
        
        $query->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);        
        $countquery->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);        
        
     
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
             'creationDate',
             'startDate',
             'planDate',
             'deadline',                  
             'taskTitle',
             'userFIO',
             'eventStatus',
             'orgTitle'
                        ],
            'defaultOrder' => [    'creationDate' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   

   

  
  
  
  
  /************End of model*******************/ 
 }
