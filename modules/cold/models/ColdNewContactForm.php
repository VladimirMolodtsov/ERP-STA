<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\ContactList;

use app\models\TblCold;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class ColdNewContactForm extends Model
{
	
	public $note = "";
	public $orgTitle ="";
	public $contactFIO ="";
    public $contactEmail = "";
	public $contactPhone = "";
	
	public $area= "";
	public $city= "";
	public $adress= "";
	public $phoneList= "";
	public $urlList= "";
	public $razdelList= "";
	
	public function rules()
    {
        return [
		    [[ 'orgTitle', 'contactPhone'], 'required'],
            [[ 'orgTitle', 'note',  'contactFIO', 'contactEmail', 'contactPhone' ], 'default'],
			[[ 'area', 'city',  'adress', 'phoneList', 'urlList', 'razdelList' ], 'default'],
			[[ 'orgTitle', 'contactFIO', 'contactEmail', 'contactPhone', 'note'], 'trim'],
			['contactFIO', 'string', 'length' => [1,150]],
            ['contactEmail', 'email'],
        ];
    }
	
	public function saveData()		
   {
	  $record = new OrgList();
	  $curUser=Yii::$app->user->identity;
	  $record->title = $this->orgTitle;
	  $record->contactPhone = $this->contactPhone;
	  $record->contactEmail = $this->contactEmail;
	  $record->contactFIO = $this->contactFIO;
	  $record->contactDate = date("Y.m.d h:i:s");	  			  
		  
	  $record->ref_user = $curUser->id;		  
	  $record->isInWork = 0;		  		  
		  
	  $record->save();
		  
	  
  	  $phoneRecord = new PhoneList ();
	  $phoneRecord->ref_org = $record->id;
	  $phoneRecord->status = 1; /*помечаем телефон как надежный*/
	  $phoneRecord->phone   = $this->contactPhone;	  
	  $phoneRecord->save();
	  $phoneRef= $phoneRecord->id;
	  
	  if ($this->phoneList != "")
	  {
	  $phones=preg_split("/\,/iu",$this->phoneList);
	  for ($i=0;$i< count($phones); $i++)
	  {	  
		$phoneRecord = new PhoneList ();
		$phoneRecord->ref_org = $record->id;
		$phoneRecord->phone   = $phones[$i];
		$phoneRecord->save();
	  }
	  }
		  
	  $contact = new ContactList();
	  $contact->ref_phone = $phoneRef;
	  $contact->ref_org = $record->id;
	  $contact->ref_user = $curUser->id;		  
	  $contact->contactFIO = $this->contactFIO;
	  $contact->contactDate = date("Y.m.d h:i:s");	  			  		  
	  $contact->note = $this->note;
	  $contact->save();		  	  
	
    
      $coldRecord = new TblCold();
      $coldRecord->firstContactRef = $contact->id;
      $coldRecord->save();
      
    
	  if ($this->razdelList != "")
	  {
	  $list=preg_split("/\,/iu",$this->razdelList);
	  for ($i=0;$i< count($list); $i++)
	  {	  
			Yii::$app->db->createCommand()->insert('{{%razdellist}}', [
			'razdel' => $list[$i],
			'ref_org' =>  $record->id,
			])->execute();
		}		
	  }

	  if ($this->urlList != "")
	  {
	  $list=preg_split("/\,/iu",$this->urlList);
	  for ($i=0;$i< count($list); $i++)
	  {	  
			Yii::$app->db->createCommand()->insert('{{%urllist}}', [
			'url' => $list[$i],
			'ref_org' => $record->id,
			])->execute();
		}		
	  }

 	  Yii::$app->db->createCommand()->insert('{{%adreslist}}', [
			'area' => $this->area,
			'city' => $this->city,
			'adress' => $this->adress,
			'ref_org' => $record->id,
	  ])->execute();
		
	  
	  $strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id ) where {{%orglist}}.id=:id";
	  Yii::$app->db->createCommand($strSql, [
	  	'id' => $record->id,
	  ])->execute();
	  
   }
	
    
 }
