<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblClientSchetContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%client_schet_content}}';
    }	
	
}
