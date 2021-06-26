<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblDocContactLnk extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%doc_contact_lnk}}';
    }
  
}
