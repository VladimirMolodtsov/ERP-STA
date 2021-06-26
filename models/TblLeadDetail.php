<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblLeadDetail extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%lead_detail}}';
    }
}
