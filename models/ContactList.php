<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class ContactList extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%contact}}';
    }	
	
}
