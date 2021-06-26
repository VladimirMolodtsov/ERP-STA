<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\ContactList;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class MarketNewOrgForm extends Model
{
	public $id = 0;
	
	public $note = "";
	public $orgTitle ="";
	public $contactFIO ="";
    public $contactEmail = "";
	public $contactPhone = "";
	public $orgRazdel = "";
	
	public $area= "";
	public $city= "";
	public $adress= "";
	public $phoneList= "";
	public $urlList= "";
	public $razdelList= "";
	
	public $period = 0;
	
	public $needList_0= 0;
	public $needList_1= 0;
	public $needList_2= 0;
	public $needList_3= 0;
	public $needList_4= 0;
	public $needList_5= 0;
	public $needList_6= 0;
	public $needList_7= 0;
	public $needList_8= 0;
	public $needList_9= 0;

		
	public function rules()
    {
        return [
		    [[ 'orgTitle', 'contactPhone'], 'required'],
            [[ 'orgTitle', 'orgRazdel', 'note',  'contactFIO', 'contactEmail', 'contactPhone' ], 'default'],
			[[ 'period', 'needList_0','needList_1', 'needList_2', 'needList_3', 'needList_4', 'needList_5', 'needList_6', 'needList_7', 'needList_8', 'needList_9'], 'default'],
			[[ 'area', 'city',  'adress', 'phoneList', 'urlList', 'razdelList' ], 'default'],
			[[ 'orgTitle', 'contactFIO', 'contactEmail', 'contactPhone', 'note'], 'trim'],
			['contactFIO', 'string', 'length' => [1,150]],
            ['contactEmail', 'email'],
        ];
    }
	
	
   public function getNeedListN()
   {	   
	   $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%need_title}}' )->queryScalar();       	
	   return $ret;
   }
	
   public function getNeedList()
   {
	  $ret =  Yii::$app->db->createCommand('SELECT Title, row from {{%need_title}} order by row')->queryAll();       	  
      return $ret;
   }
	
	
	
	
	public function saveData()		
   {
	  $record = new OrgList();
	  $curUser=Yii::$app->user->identity;
	  $record->title = $this->orgTitle;
	  $record->contactPhone = $this->contactPhone;
	  $record->contactEmail = $this->contactEmail;
	  $record->contactFIO = $this->contactFIO;
	  $record->contactDate =  date("Y.m.d h:i:s");	  			  		  
	  $record->needFreq = $this->period; 
		  
	  $record->ref_user = $curUser->id;		  
	  $record->isInWork = 0;		  		  
		  
	  $record->save();
		  
	  
  	  $phoneRecord = new PhoneList ();
	  $phoneRecord->ref_org = $record->id;
	  $phoneRecord->status = 1; /*помечаем телефон как надежный*/
	  $phoneRecord->phone   = $this->contactPhone;
	  $phoneRecord->phoneContactFIO= $this->contactFIO;	  
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
	  $contact->note = $this->note;
	  $contact->save();		  	  
	
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
	  
	  $this->id = $record->id;
	  $this->saveNeed(); 
	  
	  return $record->id;
   }
	
	
  public function saveNeed()
  {
	  Yii::$app->db->createCommand(
            'DELETE from {{%need}} where {{%need}}.ref_org=:ref_org', 
            [':ref_org' => $this->id,])->execute();
			
	   $needTitleList = Yii::$app->db->createCommand('SELECT id, row from {{%need_title}} order by row ')->queryAll(); 
	   $needTitleArray = array();
	   for($i=0; $i<count($needTitleList); $i++)
	   {
		$needTitleArray[$i]=   $needTitleList[$i]['id'];
	   }
	  
	  /*0*/
	  
	  if ($this->needList_0 > 0)
	  {
	  $rowNum=0; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_0;	  
	  }
	  
	  /*1*/
	  if ($this->needList_1 > 0)
	  {
	  $rowNum=1; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_1;	  
	  }

	  /*2*/
	  if ($this->needList_2 > 0)
	  {
	  $rowNum=2; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_2;	  
	  }

	  /*3*/
	  if ($this->needList_3 > 0)
	  {
	  $rowNum=3; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_3;	  
	  }

	  /*4*/
	  if ($this->needList_4 > 0)
	  {
	  $rowNum=4; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_4;	  
	  }

	  /*5*/
	  if ($this->needList_5 > 0)
	  {
	  $rowNum=5; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_5;	  
	  }

	  /*6*/
	  if ($this->needList_6 > 0)
	  {
	  $rowNum=6; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_6;	  
	  }

	  /*7*/
	  if ($this->needList_7 > 0)
	  {
	  $rowNum=7; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_7;	  
	  }

	  /*9*/
	  if ($this->needList_8 > 0)
	  {
	  $rowNum=8; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_8;	  
	  }

	  /*9*/
	  if ($this->needList_9 > 0)
	  {
	  $rowNum=9; 	  
	  $needRecord = new NeedList (); 
	  $needRecord->ref_org=$this->id; 
	  $needRecord->need_title_id=$needTitleArray[$rowNum]; 	  
	  $needRecord->need_size=$this->needList_9;	  
	  }
  }
	
    
 }
