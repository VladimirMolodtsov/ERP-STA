<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlSverkaUse extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_sverka_dolga_use}}';
    }	
	
}
