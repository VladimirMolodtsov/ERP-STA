<?php

namespace app\modules\managment\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class TblControlPurchType extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%control_purch_type}}';
    }
    
    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {  return false;  }

      $strSql="DELETE FROM {{%control_purch_mask}} where typeRef = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	


    return true;
    }

}
