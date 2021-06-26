<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblMonitorRowCfg extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%monitor_row_cfg}}';
    }

}
