<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSupplierWares extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_wares}}';
    }	
	
}
