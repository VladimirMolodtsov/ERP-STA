<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblOrgRekvezit extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%org_rekvezit}}';
    }
}
