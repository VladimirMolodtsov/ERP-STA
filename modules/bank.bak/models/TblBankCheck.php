<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblBankCheck extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%bank_check}}';
    }
  
}
