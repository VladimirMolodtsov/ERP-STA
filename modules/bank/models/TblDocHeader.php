<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblDocHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%doc_header}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%documents}} where refDocHeader = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }


}
