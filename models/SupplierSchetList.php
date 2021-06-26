<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class SupplierSchetList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_schet}}';
    }	
	
}
