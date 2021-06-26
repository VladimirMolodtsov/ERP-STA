<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class RequestGoodContent  extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%request_good_content}}';
    }	
	
	
}
