<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class NeedTitleList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%need_title}}';
    }	
	
}
