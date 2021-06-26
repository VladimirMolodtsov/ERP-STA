<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class PurchaseVariant extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%purchase_variant}}';
    }	
	
}
