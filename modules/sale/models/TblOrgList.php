<?php

namespace app\modules\market\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblOrgList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%orglist}}';
    }
}
