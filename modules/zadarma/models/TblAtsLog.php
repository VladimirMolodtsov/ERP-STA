<?php

namespace app\modules\zadarma\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblAtsLog extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%ats_log}}';
    }	
	
}
