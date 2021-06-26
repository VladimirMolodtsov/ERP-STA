<?php

namespace app\modules\tasks\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblTaskTemplateHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%task_template_header}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%task_template_content}} where refHeader = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }
}
