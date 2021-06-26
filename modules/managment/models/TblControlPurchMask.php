<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlPurchMask extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_purch_mask}}';
    }
    

}
