<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblFinCheckRow extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%fin_check_row}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%fin_check_buh_cfg}} where rowRef = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

      $strSql="DELETE FROM {{%fin_check_ut_cfg}} where rowRef = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }

}
