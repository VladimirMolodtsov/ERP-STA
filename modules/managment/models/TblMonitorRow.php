<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblMonitorRow extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%monitor_row}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%monitor_row_cfg}} where rowHeaderRef = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	


    return true;
    }

}
