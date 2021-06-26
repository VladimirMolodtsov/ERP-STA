<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class NeedList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%need}}';
    }	
	
}
