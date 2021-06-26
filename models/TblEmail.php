<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblEmail extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%emaillist}}';
    }	
	
}
