<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblClientSchetHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%client_schet_header}}';
    }	
	
}
