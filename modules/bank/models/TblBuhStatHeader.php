<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblBuhStatHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%buh_stat_header}}';
    }
}
