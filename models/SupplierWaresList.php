<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class SupplierWaresList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_wares}}';
    }	
	
}
