<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlSverkaHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_sverka_header}}';
    }	
	
}
