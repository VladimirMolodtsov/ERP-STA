<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblFinCheckBuhCfg extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%fin_check_buh_cfg}}';
    }
}
