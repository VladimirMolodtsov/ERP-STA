<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblDocOperation extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%doc_operation}}';
    }

}
