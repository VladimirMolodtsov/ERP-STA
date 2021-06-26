<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblAuroraExtract extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%aurora_extract}}';
    }
  
}
