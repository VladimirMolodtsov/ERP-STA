<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class OtvesList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%otves_list}}';
    }	
	
}
