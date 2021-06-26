<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class SupplierOplataList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_oplata}}';
    }	
	
}
