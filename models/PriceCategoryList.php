<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class PriceCategoryList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%price_category}}';
    }	
	
}
