<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblBuhSchetContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%buh_schet_content}}';
    }
}
