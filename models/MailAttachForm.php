<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 , 'extensions' => 'csv'
 */
class MailAttachForm extends Model
{
	
	public $attachFile;
	public $isUpload=0;
	public $fname="";
	
	public function rules()
    {
        return [
            [['attachFile'], 'file', 'skipOnEmpty' => false],
        ];
    }
    
	public function upload()
    {
		
        if ($this->validate()) 
		{
			$uploadPath=(realpath(dirname(__FILE__)))."/../attach/";
			
			$this->attachFile->saveAs( $uploadPath.$this->attachFile->name);
			$isUpload = 1;
            return true;
        } else 
		{
			echo "<pre>";
			print_r ($this->attachFile);
			echo "</pre>";
			echo "Проблемы при загрузке файла [". $this->attachFile->baseName . '.' . $this->attachFile->extension."]\n";			
            return false;
        }
    }
	    
	
}
