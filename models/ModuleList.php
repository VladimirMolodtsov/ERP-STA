<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class ModuleList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%text_modules}}';
    }	
	
}
