<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class ReestrLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%reestr_lnk}}';
    }	
	
}
