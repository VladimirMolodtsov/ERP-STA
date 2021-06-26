<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblWareEdLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%ware_ed_lnk}}';
    }
}
