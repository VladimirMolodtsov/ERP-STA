<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlBank extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_bank}}';
    }	
	
}
