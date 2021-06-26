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
 
 class TasksMain extends Model
{
    
    public $debug;
    
    
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
        ];
    }

   public function getSchetData($id)
   {
      
    $list = Yii::$app->db->createCommand("Select id, docStatus,cashState,supplyState   FROM {{%schet}} where id=:schetRef",
    [':schetRef' => $id])->queryOne();    
       return  $list; 
   }
  /************End of model*******************/ 
 }
