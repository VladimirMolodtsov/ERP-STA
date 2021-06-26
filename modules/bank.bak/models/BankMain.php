<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 



/**
 * BankMain - модель работы с выписками из банка -главная страница
 */
 
 class BankMain extends Model
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

/*************************************/

   public function getTypeArray()
   {
      $strSql = "SELECT id, typeTitle from {{%doc_type}} ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      return ArrayHelper::map($list,'id','typeTitle');
   }  
 
   public function getOperationArray($typeRef)
   {
       $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$typeRef." ORDER BY id"; 
       $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
       return ArrayHelper::map($list,'id','operationTitle');       
   }  

    
    
  /**************************/    
  
  
  /************End of model*******************/ 
 }
