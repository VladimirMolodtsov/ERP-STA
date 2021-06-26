<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblWareType extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%ware_type}}';
    }
}
