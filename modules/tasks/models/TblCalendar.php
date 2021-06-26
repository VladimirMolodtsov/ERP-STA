<?php

namespace app\modules\tasks\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblCalendar extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%calendar}}';
    }
}
