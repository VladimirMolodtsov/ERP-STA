<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class AdressList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%adreslist}}';
    }	
	
}
