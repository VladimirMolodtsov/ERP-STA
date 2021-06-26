<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlSaleContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_sale_content}}';
    }	
	
}
