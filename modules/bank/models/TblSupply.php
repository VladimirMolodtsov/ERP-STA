<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSupply extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supply}}';
    }	
	
}
