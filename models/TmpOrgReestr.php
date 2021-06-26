<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TmpOrgReestr extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%tmp_reestr}}';
    }	
	
}
