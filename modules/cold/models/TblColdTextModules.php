<?php

namespace app\modules\cold\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblColdTextModules extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%cold_text_modules}}';
    }	
	
}
