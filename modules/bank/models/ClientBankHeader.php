<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;

/**
 * ClientBankDocument Заголовок
 
 */
 
 class ClientBankHeader extends Model
{
    public $startKey  ='';
    public $finishKey ='';

    public $startDate = "";
    public $endDate = "";
    public $startSumm = "";
    public $endSumm = "";        
    public $account = "";
    public $expense = "";
    public $admission = "";

  /***/
    public function rules()
    {
        return [                              
             [[] 'default'],                        
             [[], 'safe'],            
        ];
    }
      
  /**************************/
 public function set($param, $value) 
     { 
        if (!isset($this->keyArray[$param])) return false; 
        $key = $this->keyArray[$param];
        $this->$key=$value;    
     }
 
  
  /************End of model*******************/ 
 }
