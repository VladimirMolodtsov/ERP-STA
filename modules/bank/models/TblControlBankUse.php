<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlBankUse extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_bank_use}}';
    }
}
