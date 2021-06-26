<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSupplyStatus extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supply_status}}';
    }
}
