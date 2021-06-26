<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class SupplierSchetHeaderList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_schet_header}}';
    }	
	
}
