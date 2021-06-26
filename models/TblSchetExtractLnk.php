<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblSchetExtractLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%schet_extract_lnk}}';
    }
}
