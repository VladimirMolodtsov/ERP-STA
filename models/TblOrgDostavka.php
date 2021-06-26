<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblOrgDostavka extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%org_dostavka}}';
    }
}
