<?php

namespace app\modules\tasks\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblTaskTemplateContent extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%task_template_content}}';
    }
}
