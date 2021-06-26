<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class SupplyStatusList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supply_status}}';
    }	
	
}
