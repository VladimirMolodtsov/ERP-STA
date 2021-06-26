<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;


class UserInfoForm extends Model
{
    
 /*использование в отчетах по обработке почты */   
 public function switchUserRptMail ($userid)
 { 
    $record = User::findOne($userid);
    if(empty($record)) return ['res' => false, 'userid' => $userid];
    
    if ($record->usageFlag & User::U_RPT_MAIL) {$record->usageFlag  &= ~ User::U_RPT_MAIL; $val=0;}
                                        else   {$record->usageFlag |=  User::U_RPT_MAIL; $val = 1;}
    
    $record->save(); 
    
    
    return ['res' => true, 'id' => $userid, 'val' => $val, 'switchType' =>'U_RPT_MAIL'];
}
    
    
/*
 Возвращает списко доступных ролей
*/
    public function getAvailableRole()
    {
        if (Yii::$app->user->isGuest) return false;
        return $ret;
    }


}
