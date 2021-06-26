<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblWareNames extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%ware_names}}';
    }
}
