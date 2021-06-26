<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class DeliverList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%request_deliver}}';
    }	
	
	
}
