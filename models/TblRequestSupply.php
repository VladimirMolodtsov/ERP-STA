<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblRequestSupply extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%request_supply}}';
    }
}
