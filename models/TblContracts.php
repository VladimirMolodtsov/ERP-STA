<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblContracts extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%contracts}}';
    }
}
