<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblOrgDublicate extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%org_dublicate}}';
    }
}
