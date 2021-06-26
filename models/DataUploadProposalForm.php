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
class DataUploadProposalForm extends Model
{
	
	public $proposalFile;
	public $isUpload=0;
	public $fname="";
	
	public function rules()
    {
        return [
            [['proposalFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf'],
        ];
    }
    
	public function upload()
    {
		
        if ($this->validate()) 
		{
			$uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
			
			$this->proposalFile->saveAs( $uploadPath."/proposal.pdf");
			$isUpload = 1;
            return true;
        } else 
		{
			echo "<pre>";
			print_r ($this->proposalFile);
			echo "</pre>";
			echo "Проблемы при загрузке файла [". $this->proposalFile->baseName . '.' . $this->proposalFile->extension."]\n";			
            return false;
        }
    }
	    
}
