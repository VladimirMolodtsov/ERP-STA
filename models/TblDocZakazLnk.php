<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblDocZakazLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%doc_zakaz_lnk}}';
    }
  
}
