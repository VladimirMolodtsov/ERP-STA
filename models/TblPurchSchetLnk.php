<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblPurchSchetLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%purch_schet_lnk}}';
    }	
	
}
