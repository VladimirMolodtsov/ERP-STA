<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblTransportTarif extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%transport_tarif}}';
    }
}
