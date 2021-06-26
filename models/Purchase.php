<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class Purchase extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%purchase}}';
    }	
	
}
