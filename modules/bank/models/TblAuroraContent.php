<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblAuroraContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%aurora_content}}';
    }
}
