<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlRemainsUse extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_remains_use}}';
    }	
	
	
}
