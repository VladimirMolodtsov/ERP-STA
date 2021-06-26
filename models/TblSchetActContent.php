<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSchetActContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%schet_actContent}}';
    }	
	
}
