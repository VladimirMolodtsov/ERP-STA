<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblDocPaydetail extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%doc_paydetail}}';
    }
    


}
