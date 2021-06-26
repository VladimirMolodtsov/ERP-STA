<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class ZakazHistory extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%zakazHistory}}';
    }	
	
}
