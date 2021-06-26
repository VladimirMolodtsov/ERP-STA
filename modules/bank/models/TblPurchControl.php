<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblPurchControl extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_purch_content}}';
    }	
	
}
