<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\RazdelList;
use app\models\PodrazdelList;
use app\models\RubrikList;
use app\models\AdressList;
use app\models\EmailList;
use app\models\UrlList;


/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 , 'extensions' => 'csv'
 */
class DataUploadCsvForm extends Model
{
	
	public $csvFile;
	public $isUpload=0;
	public $delimiter= "\t";

	public $from=0;
	public $count=50;
	public $fname="";
	
	
	public function rules()
    {
        return [
            [['csvFile'], 'file', 'skipOnEmpty' => false],
        ];
    }
    
	public function upload()
    {
        if ($this->validate()) 
		{
			/*'/srv/www/htdocs/phone/uploads/'*/
			$uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
			$this->csvFile->saveAs( $uploadPath. $this->csvFile->baseName . '.' . $this->csvFile->extension);
			$isUpload = 1;
            return true;
        } else 
		{
            return false;
        }
    }
	
    public function getData()
	{
		return $data;
	}


	/*
		Готовим индексный массив	
	*/
	   	
   /*
     Парсим файл $fname, с номера uid $from, число записей $count

   */				
   
	public function parseCsv($fname, $from, $count)
    {
		if ($fname == ""){return -1;}
		if ( ($fh = fopen($fname, 'r')) == false) 
		{
			echo "File is not exist!<br> ";
			echo "Error while open ".$fname;
			exit (-1);
		}		
		$row = array();
		$str="";
		$row = fgetcsv($fh, 5000, "=");		
		if ( (trim($row[0]) !="asep") && ( !(strpos($row[0], "asep")) ) ) 
		{
			echo "Invalid format!<br> <pre>";
                        print_r($row);
			echo "</pre>Error while open ".$fname;
			exit (-1);			
		}
		$this->delimiter = $row[1];
/*
    0   out << phRecord->orgTitle <<";";
    1   out << phRecord->razdelList.join(",")  <<";";
    2   out <<  phRecord->podrazdelList.join(",") <<";";
    3   out <<  phRecord->rubrikList.join(",") <<";";
    4   out <<  phRecord->adressList[0].area <<";";
    5   out <<  phRecord->adressList[0].city <<";";
    6   out <<  phRecord->adressList[0].X <<";";
    7   out <<  phRecord->adressList[0].Y <<";";
    8   out <<  phRecord->emailList.join(",") <<";";
    9   out <<  phRecord->phonesList.join(",") <<";";
    10  out <<  phRecord->urlsList.join(",") << endl;
*/
        $counter =0;
		while (1) 
		{			
			if (($row = fgetcsv($fh, 5000, $this->delimiter)) == false) 
			{
				$counter = -$counter; 
				if ($counter==0){$counter =-1;} 
				return $counter;
			}
			
			if ($counter <$from) {$counter++; continue;}	
			if ($counter >= $from+$count){break;}

			$org_title=$row[0];		    
			$city=$row[5];
		    $phonelist=preg_split("/\,/iu",$row[9]);
			for ($i=0; $i<count($phonelist); $i++ )
			{
				$phonelist[$i]=preg_replace ("/\\D/iu","",$phonelist[$i]);
			}
			
			$find=0;
		
			$cnt= Yii::$app->db->createCommand('SELECT count({{%orglist}}.id) FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org            
			where {{%orglist}}.title=:title AND {{%adreslist}}.city=:city
            ')
			->bindValue(':title', $org_title)
			->bindValue(':city', $city)
			->queryScalar();

            /*Есть с таким же названием в этом городе*/  
			if ($cnt > 0)
			{
			$existPhones= Yii::$app->db->createCommand('SELECT phone FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org    
            left join {{%phones}}  on {{%orglist}}.id = {{%phones}}.`ref_org`
			where {{%orglist}}.title=:title AND {{%adreslist}}.city=:city
            ')
			->bindValue(':title', $org_title)
			->bindValue(':city', $city)			
			->queryAll();
			/*Есть с таким же телефоном в этом городе*/  
			for ($i=0; $i<count($phonelist); $i++ )
			{
			  if (trim($phonelist[$i])==""){continue;}
			  if (ArrayHelper::isIn($phonelist[$i], $existPhones))
			  {
				 $find=1;
				 break;
			  }
			}			
			}
			
			/* такая организация уже есть*/
			if ($find ==1) {continue;}
			
			/*Добавляем организацию*/
			$orgRecord = new OrgList (); 
			$orgRecord->title =  $org_title;
			$orgRecord->razdel = trim($row[1]);
			$orgRecord->save();
		    $uid = $orgRecord->id;

			
			/*9 Телефоны	*/
	        for ($j=0;$j<count($phonelist);$j++)
				{
					if (trim($phonelist[$j])==""){continue;}
					$record=new PhoneList();
					$record->ref_org=$uid;
					$record->phone =trim($phonelist[$j]);
					$record->save();
				}
			

			$record=new AdressList();
			$record->ref_org=$uid;
			$record->area =trim($row[4]);
			$record->city =trim($row[5]);
			$record->x =trim($row[6]);
			$record->y =trim($row[7]);			
			$record->save();
			
			/*1 Разделы	*/
	        if ($row[1]!="")
			{
				$list=preg_split("/\,/iu",$row[1]);
				for ($j=0;$j<count($list);$j++)
				{
					if (trim($list[$j])==""){continue;}
					$record=new RazdelList();
					$record->ref_org=$uid;
					$record->razdel =trim($list[$j]);
					$record->save();
				}
			}

			/*2 ПодРазделы	*/
	        if ($row[2]!="")
			{
				$list=preg_split("/\,/iu",$row[2]);
				for ($j=0;$j<count($list);$j++)
				{
					if (trim($list[$j])==""){continue;}
					$record=new PodrazdelList();
					$record->ref_org=$uid;
					$record->podrazdel =trim($list[$j]);
					$record->save();
				}
			}

			/*3 Рубрики*/
	        if ($row[3]!="")
			{
				$list=preg_split("/\,/iu",$row[3]);
				for ($j=0;$j<count($list);$j++)
				{
					if (trim($list[$j])==""){continue;}
					$record=new RubrikList();
					$record->ref_org=$uid;
					$record->rubrika =trim($list[$j]);
					$record->save();
				}
			}
			
			/*8 EMail*/
	        if ($row[8]!="")
			{
				$list=preg_split("/\,/iu",$row[8]);
				for ($j=0;$j<count($list);$j++)
				{
					if (trim($list[$j])==""){continue;}
					$record=new EmailList();
					$record->ref_org=$uid;
					$record->email =trim($list[$j]);
					$record->save();					
				}
			}
			
			/*10 Url*/
	        if ($row[10]!="")
			{
				$list=preg_split("/\,/iu",$row[10]);
				for ($j=0;$j<count($list);$j++)
				{
					if (trim($list[$j])==""){continue;}
					$record=new UrlList();
					$record->ref_org=$uid;
					$record->url =trim($list[$j]);
					$record->save();					
				}
			}
    		
			/*держим коннект живым*/
			echo ".\n";
			$counter++;
	   }   /*Обрабатывеам все идентификаторы*/					
	  
	  if ($counter < $from+$count)
	  {
		$counter = -$counter; 
		if ($counter==0){$counter =-1;} 
		return $counter;		
	  }	  
	  return $counter;
	  
	}
    
	
}
