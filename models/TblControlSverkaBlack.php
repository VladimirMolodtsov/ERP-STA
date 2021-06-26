<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlSverkaBlack extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_sverka_dolga_black}}';
    }	
	
}
