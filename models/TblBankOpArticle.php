<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblBankOpArticle extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%bank_op_article}}';
    }	
	
}
