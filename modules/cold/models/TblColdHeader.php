<?php

namespace app\modules\cold\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblColdHeader extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%cold_header}}';
    }	
	
/**/
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%cold_content}} where orgHeaderRef = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	

    return true;
    }
    
}
