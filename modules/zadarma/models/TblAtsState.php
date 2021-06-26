<?php

namespace app\modules\zadarma\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblAtsState extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%ats_state}}';
    }	
	
}
