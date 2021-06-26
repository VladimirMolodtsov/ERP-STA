<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSupplierSchetHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%supplier_schet_header}}';
    }	
	
}
