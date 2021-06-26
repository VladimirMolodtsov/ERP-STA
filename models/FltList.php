<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class FltList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%fltList}}';
    }	
	
}
