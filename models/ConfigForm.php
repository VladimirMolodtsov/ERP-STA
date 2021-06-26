<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\ConfigTable;
/**
 * Настройка
 */
class ConfigForm extends Model
{
	public $emailOP ="";
	public $emailSUP ="";
	public $emailCTRL ="";


	public function rules()
    {
        return [
		    [['emailOP','emailSUP','emailCTRL'], 'required'],
            [['emailOP','emailSUP','emailCTRL'], 'default'],
			[['emailOP','emailSUP','emailCTRL'], 'trim'],
			['emailOP', 'email'],
			['emailSUP', 'email'],
			['emailCTRL', 'email'],
			
        ];
    }
	
	
	public function loadData()	
    {
	  
	  $configRecord = ConfigTable::findOne('1001');	
	  $this->emailOP= $configRecord->keyValue;
	  
	  $configRecord = ConfigTable::findOne('1002');	
	  $this->emailSUP= $configRecord->keyValue;

	  $configRecord = ConfigTable::findOne('1003');	
	  $this->emailCTRL= $configRecord->keyValue;	  
			
	}

	
	public function saveData()	
    {

	  $configRecord = ConfigTable::findOne('1001');	
	  if(empty ($configRecord)) return;
	  $configRecord->keyValue = $this->emailOP ;
	  $configRecord->save();
	  
	  $configRecord = ConfigTable::findOne('1002');	
	  if(empty ($configRecord)) return;
	  $configRecord->keyValue = $this->emailSUP ;
	  $configRecord->save();
	  

	  $configRecord = ConfigTable::findOne('1003');	
	  if(empty ($configRecord)) return;
	  $configRecord->keyValue = $this->emailCTRL ;
	  $configRecord->save();

	}
	

	
}

