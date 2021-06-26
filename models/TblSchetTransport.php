<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSchetTransport extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%schetTransport}}';
    }
}
