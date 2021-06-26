<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblAuroraHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%aurora_header}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%aurora_content}} where refBankHeader = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }


}
