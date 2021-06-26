<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

/*
Реестр преполагаемых оплат.
*/

class ReestrOplat extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{%reestr_oplat}}';
    }	


    public function beforeDelete()
    {
    if (!parent::beforeDelete()) {
        return false;
    }
    
  $strSql="DELETE FROM {{%reestr_lnk}} where reestrId = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 	
  
  $strSql="UPDATE  {{%request_deliver}} set refOplateDrive = 0 where refOplateDrive = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute();        

 
  $strSql="UPDATE  {{%request_deliver}} set refOplateExpCost = 0 where refOplateExpCost = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 

  $strSql="UPDATE  {{%request_deliver}} set refOplateWrkExp = 0 where refOplateWrkExp = :id";
       Yii::$app->db->createCommand($strSql)->bindValue(':id', $this->id)->execute(); 

            
    return true;
    }
    
	
}

