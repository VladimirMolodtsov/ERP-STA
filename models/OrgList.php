<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class OrgList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%orglist}}';
    }	
	
	
}
