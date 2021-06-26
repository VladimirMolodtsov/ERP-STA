<?php

namespace app\modules\bank\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblBankOpHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%bank_op_header}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%bank_op_content}} where refBankOpHeader = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }


}
