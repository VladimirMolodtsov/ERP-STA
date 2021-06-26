<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\ClientData; 
use app\models\SchetList; 
use app\models\ZakazList; 
use app\models\ScladList;
use app\models\OplataList;
use app\models\SupplyList;
use app\models\User; 
use app\models\MarketSchetForm;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class DataSyncGoogle extends Model
{
	
	public $actionCode = 0;
	public $googleClientsUrl = "";
	public $updExistedClients = 0;
	public $importSchetUrl = "";
	public $importOplataUrl = "";
	public $importPostavkaUrl = "";
	public $importContactsUrl = "";
	public $priceKF =1.2;
	public $createNewOrg = 0; // добавлять организации
	public $syncAllUser = 0;  // Синхронизировать для всех пользователей
	public $createNewSchet = 0;  // Создавать счет
	public $updateExistedSchet = 1;  // апдейтить существующий
    public $forceUpdateSchet   = 0;  // апдейтить даже если уже синхронизирован
	public $syncDate = "";
	
	public $managerRefArray = array();

	
	public function rules()
    {
        return [
            [[ 'actionCode', 'updExistedClients','googleClientsUrl', 'importSchetUrl', 'importOplataUrl','importPostavkaUrl','importContactsUrl'], 'default'],			
        ];
    }

	public function saveUrls()
	{
		Yii::$app->db->createCommand('UPDATE {{%config}} SET keyValue =:keyValue where id=5')
		->bindValue(':keyValue', $this->googleClientsUrl)
		->execute();

		Yii::$app->db->createCommand('UPDATE {{%config}} SET keyValue =:keyValue where id=6')
		->bindValue(':keyValue', $this->importSchetUrl)
		->execute();
		
		Yii::$app->db->createCommand('UPDATE {{%config}} SET keyValue =:keyValue where id=7')
		->bindValue(':keyValue', $this->importOplataUrl)
		->execute();
		
		Yii::$app->db->createCommand('UPDATE {{%config}} SET keyValue =:keyValue where id=8')
		->bindValue(':keyValue', $this->importPostavkaUrl)
		->execute();
					
	}
	
	public function loadDefaultUrl()
	{
		$list = Yii::$app->db->createCommand(
            'SELECT id, keyValue FROM {{%config}} ORDER BY id')->queryAll();
			
		for ($i=0; $i<count($list); $i++ )
		{
			switch ($list[$i]['id'])
			{
			  case 5:
				$this->googleClientsUrl = $list[$i]['keyValue'];
			  break;		
				
			  case 6:
				$this->importSchetUrl = $list[$i]['keyValue'];
			  break;		
			
			  case 7:
				$this->importOplataUrl = $list[$i]['keyValue'];
			  break;		

			  case 8:
				$this->importPostavkaUrl = $list[$i]['keyValue'];
			  break;		

	
			
			}
		}
	}

	
	public function get_web_page( $url )
	{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
	}
/**************************************************/	
	public function loadNClientBase($updExistedClients, $startRow)
	{
		$clientData= new ClientData();
		$updatedClients=0;
		mb_internal_encoding("UTF-8");
		
		$strSql="UPDATE {{%orglist}} set isNew = 0 where isNew > 0";
		Yii::$app->db->createCommand($strSql)->execute();

		/*получим список менеджеров*/
		$list = Yii::$app->db->createCommand(
            'SELECT id, userFIO FROM {{%user}} order by id')->queryAll();	    
		for($i=0; $i < count ($list); $i++)
		{
			$this->managerRefArray[$list[$i]['userFIO']]=$list[$i]['id'];			
		}
	    /**добавим неопознанного **/
		$this->managerRefArray[""]="Null";			

		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 5')->queryScalar();
	
//	return	$url;
		$page = $this->get_web_page( $url.$startRow );	
		
//return	$page;
		$content = mb_split('\r\n', $page['content'] );		
//return 		$content;
		$res= array();
		$n=count($content);
		$i=0;
		if ($startRow == 1) 
		{
			/*Первый блок данных*/
				
			$parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

			
			$i=1;
		}	
		for ($i; $i<$n;$i++ )
		{
			
			$cnt = preg_match_all("/\"/", $content[$i], $matches);
			$cnt = ($cnt%2);
			$j=0;
			while ($cnt >0)
			{
				$j++;
				if ($i+$j < $n){$content[$i].=$content[$i+$j];}
				else {break;}
				$cnt = preg_match_all("/\"/", $content[$i], $matches);
  			    $cnt = ($cnt%2);
				
			}						
			
			//$res[]=	
			$updatedClients+=$this->parseClientRecord($content[$i], $updExistedClients);
			$i+=$j;		
		}
		
		
	   $ret['allRecords'] = $allRecords;
	   $ret['updatedClients'] = $updatedClients;	   
//	   $ret['lastLoaded'] = $res['numLoad'];
//	   $ret['loaded'] = $res;
//	   $ret['err'] = $page[err];

		
		$strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id )";
		Yii::$app->db->createCommand($strSql)->execute();

		return $ret;
		
	}	


	public function parseClientRecord($parse_string, $updExistedClients)
	{
			$clientData= new ClientData();
			mb_internal_encoding("UTF-8");
		

			$parse = str_getcsv($parse_string,",");	
//				return $parse;
				
			for($j=0; $j<count($parse); $j++) {$parse[$j]=trim($parse[$j]);}
			for($j=count($parse); $j<15; $j++){$parse[]="";}			

			/*Выставим названия*/
			if(empty($parse[1]) && empty($parse[2]) ) return;
			if(empty($parse[1])){$parse[1] = $parse[2];	}
			if(empty($parse[2])){$parse[2] = $parse[1];	}
			
			$orgArray = $clientData->getEmptyOrgArray();
			
			$orgArray['numLoad']      = $parse[0];
			$orgArray['orgTitle']     = $parse[1];
			$orgArray['orgFullTitle'] = mb_substr($parse[2],0,510);
			$orgArray['orgINN']       = $parse[3];
			$orgArray['orgKPP']       = $parse[4];
			$orgArray['orgRS']        = $parse[5];
			$orgArray['orgBIK']       = $parse[6];
			$orgArray['orgKS']       = $parse[7];
			$orgArray['orgBank']       = $parse[8];
			$managerFIO=$parse[9];
			
			if (array_key_exists ($managerFIO, $this->managerRefArray))
			{			
				$orgArray['orgManager']  = $this->managerRefArray[$managerFIO]; 
			}
			else
			{
				$userRecord = new User();
				$userRecord -> userFIO = $managerFIO;
				$userRecord -> username = $managerFIO;
				$userRecord -> save();
				$this->managerRefArray[$managerFIO]=$userRecord ->id;
				$orgArray['orgManager']  = $this->managerRefArray[$managerFIO]; 
			}

			$phoneSrc = $parse[10];			
			/*В комментах есть телефон*/
			if (preg_match("/\*/iu",$parse[14] ))
			{
				$phoneSrcList = mb_split("\*",$parse[14] );
				$phoneSrc.=",".$phoneSrcList[0];
			}			
			$orgArray['orgPhoneList']  = str_getcsv($phoneSrc,",");			
			/*чистим от сторонних символов*/
			for($j=0;$j<count($orgArray['orgPhoneList']);$j++){$orgArray['orgPhoneList'][$j]=preg_replace("/[\D]/","",$orgArray['orgPhoneList'][$j]);}
			
			if ( count ($orgArray['orgPhoneList']) > 0)		$orgArray['contactPhone']=$orgArray['orgPhoneList'][0];
			
			/*Юр адрес*/
			if (!empty($parse[11]))
			{
			$adresParse = str_getcsv($parse[11],",");			
			$lastField = count($adresParse)-1;
				$orgArray['orgAdress'][0]['adress'] =$parse[6];
			if ($lastField>3){$orgArray['orgAdress'][0]['area'] = $adresParse[1];}
							else  {$orgArray['orgAdress'][0]['area'] ="";}				
			if ($lastField>2){$orgArray['orgAdress'][0]['city'] =$adresParse[$lastField-2];}
						else {$orgArray['orgAdress'][0]['city'] = $adressArray['city'] ="";}
			$orgArray['orgAdress'][0]['index'] =$adresParse[0];
			$orgArray['orgAdress'][0]['isOfficial'] =1;
			}			 
						
			/*Почта*/
			$orgArray['orgEmailList']  = str_getcsv($parse[12],",");
			
			/*Факт адрес*/
			if (!empty($parse[13]) && (trim($parse[11])!=trim($parse[13]) ))
			{
			$adresParse = str_getcsv($parse[8],",");			
			$lastField = count($adresParse)-1;
				$orgArray['orgAdress'][1]['adress'] =$parse[8];
			if ($lastField>3){$orgArray['orgAdress'][1]['area'] = $adresParse[1];}
				            else  {$orgArray['orgAdress'][1]['area'] ="";}
			if ($lastField>2){$orgArray['orgAdress'][1]['city'] =$adresParse[$lastField-2];}
						else {$orgArray['orgAdress'][1]['city'] = $adressArray['city'] ="";}
			$orgArray['orgAdress'][1]['index'] =$adresParse[0];
			$orgArray['orgAdress'][1]['X']="";
			$orgArray['orgAdress'][1]['Y']="";
			$orgArray['orgAdress'][1]['district']="";
			$orgArray['orgAdress'][0]['isOfficial'] =0;
			}			 
			
			/*Остальное*/
			$orgArray['orgNote']  = $parse[14];
			$orgArray['orgSource']  = "google 1c";
			$orgArray['isFirstContact']  = 1;
			$orgArray['isFirstContactFinished']  = 1;
			$orgArray['isNeedFinished']  = 1;
			$orgArray['isPreparedForSchet']  = 1;

			//return	$orgArray;	
			$r=$clientData->saveFromArray($orgArray);			
			
			if ($r == 0 && $updExistedClients == 1)
			{
				$r=$clientData->updateFromArray($orgArray);							
			} 
			
			return $r;		
		
	}
		
	
	public function loadGoogleClientBase()
	{
		$clientData= new ClientData();
		mb_internal_encoding("UTF-8");
		
		$strSql="UPDATE {{%orglist}} set isNew = 0 where isNew > 0";
		Yii::$app->db->createCommand($strSql)->execute();

		/*получим список менеджеров*/
		$list = Yii::$app->db->createCommand(
            'SELECT id, userFIO FROM {{%user}} order by id')->queryAll();
	    $managerRefArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$managerRefArray[$list[$i]['userFIO']]=$list[$i]['id'];			
		}
	    /**добавим неопознанного **/
		$managerRefArray[""]="Null";			

		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 5')->queryScalar();
	
		$page = $this->get_web_page( $url );	
		
		$content = mb_split('\r\n', $page['content'] );
		$res=array();
		$next_id=1;
		for ($i=4;$i< count($content); $i++)
		{
//			if ($i ==10) break;
			$parse_string =$content[$i];
			
			/*****************/
			/*Боремся с переводами строки внутри одной записи*/
/*			if($i+1<count($content))
			{
			//Есть следующая строка
				$parse = str_getcsv($content[$i+1],",");		
				while ($parse[0] != $next_id)
				{
				  $i++;	
				  rtrim($parse_string);
				  $parse_string.=$content[$i];
				  
				  if($i+1>=count($content))	{break;}
				  $parse = str_getcsv($content[$i+1],",");		
				}
				
			}
return ($parse);*/
			/*****************/
						
			$parse = str_getcsv($parse_string,",");	

			$next_id = $parse[0]+1;
			if(empty($parse[3]) )
			{
				if(empty($parse[0]) ) continue;
				$parse[3] = $parse[0];
			}
			for($j=count($parse); $j<14; $j++){$parse[]="";}
			$orgArray = $clientData->getEmptyOrgArray();
			$orgArray['orgTitle']    = $parse[3];
			$orgArray['orgINN']      = $parse[5];

			$managerFIO=trim($parse[6]);
			
			if (array_key_exists ($managerFIO, $managerRefArray))
			{			$orgArray['orgManager']  = $managerRefArray[$managerFIO]; }
			else
			{
				$userRecord = new User();
				$userRecord -> userFIO = $managerFIO;
				$userRecord -> username = $managerFIO;
				$userRecord -> save();
				$managerRefArray[$managerFIO]=$userRecord ->id;
				$orgArray['orgManager']  = $managerRefArray[$managerFIO]; 
			}

			$phoneSrc = $parse[7];			
			/*В комментах есть телефон*/
			if (preg_match("/\*/iu",$parse[11] ))
			{
				$phoneSrcList = mb_split("\*",$parse[11] );
				$phoneSrc.=",".$phoneSrcList[0];
			}
			
			$orgArray['orgPhoneList']  = str_getcsv($phoneSrc,",");
			for($j=0;$j<count($orgArray['orgPhoneList']);$j++){$orgArray['orgPhoneList'][$j]=preg_replace("/[\D]/","",$orgArray['orgPhoneList'][$j]);}
			if ( count ($orgArray['orgPhoneList']) > 0)		$orgArray['contactPhone']=$orgArray['orgPhoneList'][0];
			
			if (!empty($parse[8]))
			{
			$adresParse = str_getcsv($parse[8],",");			
			$lastField = count($adresParse)-1;
				$orgArray['orgAdress'][0]['adress'] =$parse[8];
			if ($lastField>3){$orgArray['orgAdress'][0]['area'] = $adresParse[1];}
							else  {$orgArray['orgAdress'][0]['area'] ="";}				
			if ($lastField>2){$orgArray['orgAdress'][0]['city'] =$adresParse[$lastField-2];}
						else {$orgArray['orgAdress'][0]['city'] = $adressArray['city'] ="";}
			$orgArray['orgAdress'][0]['index'] =$adresParse[0];
			}			 
			
			$orgArray['orgEmailList']  = str_getcsv($parse[9],",");
			
			if (!empty($parse[10]) && (trim($parse[10])!=trim($parse[8]) ))
			{
			$adresParse = str_getcsv($parse[10],",");			
			$lastField = count($adresParse)-1;
				$orgArray['orgAdress'][1]['adress'] =$parse[10];
			if ($lastField>3){$orgArray['orgAdress'][1]['area'] = $adresParse[1];}
				            else  {$orgArray['orgAdress'][1]['area'] ="";}
			if ($lastField>2){$orgArray['orgAdress'][1]['city'] =$adresParse[$lastField-2];}
						else {$orgArray['orgAdress'][1]['city'] = $adressArray['city'] ="";}
			$orgArray['orgAdress'][1]['index'] =$adresParse[0];
			$orgArray['orgAdress'][1]['X']="";
			$orgArray['orgAdress'][1]['Y']="";
			$orgArray['orgAdress'][1]['district']="";
			}			 
			
			$orgArray['orgNote']  = $parse[11];
			$orgArray['orgSource']  = "google 1c";
			$orgArray['isFirstContactFinished']  = 1;
			$orgArray['isFirstContact']  = 1;
			$orgArray['isNeedFinished']  = 1;
			$orgArray['isPreparedForSchet']  = 1;
		
			$r=$clientData->saveFromArray($orgArray);
			//$res[]=$orgArray;
		}
		$strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id )";
		Yii::$app->db->createCommand($strSql)->execute();

		return ($res);
	}

/*****************************************************************************/	
//   Синхронизация счетов с 1С
//	public $createNewOrg = 0;  добавлять организации
//	public $syncAllUser = 0;   Синхронизировать для всех пользователей
//	public $createNewSchet = 0;  // Создавать счет
//	public $updateExistedSchet = 0;  // апдейтить существующий

/*****************************************************************************/	

	public function loadSchetActivity($startRow, $allRecords)
	{
 		$session = Yii::$app->session;		
		$session->open();
		mb_internal_encoding("UTF-8");		
		
		$fromTime = time() - 60*60*24*30; // период синхронизации
		$fromDate = date ("Y-m-d", $fromTime); /*За последний квартал*/
		
		/*Load data*/		
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 6')->queryScalar();
		$loadurl =  $url.$startRow."&sd=".date("dmY",$fromTime)."&ed=".date("dmY",time());
		$page = $this->get_web_page($loadurl );	
		
		$content = mb_split('\r\n', $page['content'] );
	
		$orgList = array();
	
	
		$err=array();	
        $lastLoaded=0;		
		$loadCounter=0;
		$i=0;
		$curRecord = "";
		$ig=0;
		if ($startRow == 1) 
		{
			/*Первый блок данных*/
// 			$parse = str_getcsv($content[$i],",");		
// 			$allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
			
            $parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

			$i=1;
		}
		else
		{			
			$orgList=$session->get('orgList');						
		}
		
		
		for ($i;$i< count($content); $i++)
		{
			
			if(empty ($content[$i])) {continue;}					
			$parse = str_getcsv($content[$i],",");					
			if (count($parse) < 10) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/			
			$lastLoaded	=$parse[0];
			$loadCounter++;
			$schetTime= strtotime(mb_substr($parse[4],1));
			if ($schetTime < $fromTime)	{continue;}		
			$orgINN      = trim($parse[2]);
		    if (!array_key_exists ($orgINN, $orgList)){$orgList[$orgINN] = $schetTime;}
			else 
			{	
				if($orgList[$orgINN] < $schetTime	){$orgList[$orgINN] = $schetTime;}
			}
			if ($loadCounter > 2500){break;}
		}
		 	/*************************************************************/
			if ($lastLoaded == $allRecords )
			{
				foreach ($orgList as $key => $val) 
				{
					echo $key." ".date('Y-m-d',$val);	
					Yii::$app->db->createCommand('UPDATE {{%orglist}} SET last1CDate=:last1CDate WHERE schetINN=:schetINN') 
					->bindValue(':schetINN', $key)
					->bindValue(':last1CDate', date('Y-m-d',$val))
					->execute();
				}	
				return;
			}
		 	/*************************************************************/
			
	   $session->set('orgList', $orgList);					
	   $ret['allRecords'] = $allRecords;
	   $ret['lastLoaded'] = $lastLoaded;
//$ret['loaded'] = $res;	   
	   $ret['err'] = $err;
	   return $ret;
	}
/********************/	

  public function getSchetSyncStatus($schetId)
  {	 
    $statusArray=[
	'schetRef1C'=>0, 
	'schetDate'=>0, 
	'schetKey'=>0, 
	'oplateSync'=>0, 
	'oplataSum'=>0, 
	'supplySync'=>0,
	'supplySum'=>0, 	
	];
	
	$schetRec = SchetList::findOne($schetId);
			if (empty ($schetRec) ) {return false;} 			
	
	// Создаем идентификатор счета - номер_инн_дата  (Y-m-d)
	if (!empty($schetRec->schetINN)) $schetINN = $schetRec->schetINN;
								  else $schetINN = "-";
	$key = $schetRec->schetNum."_".$schetINN."_".$schetRec->schetDate;	
	$statusArray['schetKey']=$key;	
	$statusArray['schetDate']=$schetRec->schetDate;
	
	if (empty ($schetRec->ref1C) ) {return $statusArray;} 					
			
	$statusArray['schetRef1C']=$schetRec->ref1C;
			
	$statusArray['oplataSum'] = Yii::$app->db->createCommand( 'SELECT sum(oplateSumm) from {{%oplata}} where refSchet=:refSchet', 
		[':refSchet' => $schetId])->queryScalar(); 				
	
	if ($statusArray['oplataSum'] >= $schetRec->schetSumm) $statusArray['oplateSync'] = 1;
	$schetRec->summOplata = $statusArray['oplataSum'];
	
	$statusArray['supplySum'] = Yii::$app->db->createCommand( 'SELECT sum(supplySumm) from {{%supply}} where refSchet=:refSchet', 
		[':refSchet' => $schetId])->queryScalar(); 				
	
	if ($statusArray['supplySum'] >= $schetRec->schetSumm) $statusArray['supplySync'] = 1;
	$schetRec->summSupply = $statusArray['supplySum'];
	
	$schetRec->save();
	return $statusArray;
			
  }

  public function getSchetOrgRef($schetRecord)
  {
	  $retArray=array();
			  if (!empty($schetRecord['schetINN']) )
			  {
				$list = Yii::$app->db->createCommand(
				'SELECT id, refManager FROM {{%orglist}} where schetINN=:schetINN AND title like :orgTitle order by id')
				->bindValue(':schetINN',$schetRecord['schetINN'] )
				->bindValue(':orgTitle',$schetRecord['orgTitle'])
				->queryAll();
			  }else
			  {
				$list = Yii::$app->db->createCommand(
				'SELECT id, refManager FROM {{%orglist}} where  title like :orgTitle order by id')				
				->bindValue(':orgTitle',$schetRecord['orgTitle'])
				->queryAll();		  
			  }
	if (count ($list) == 0 ) return false;
		
		$retArray['refOrg'] = $list[0]['id'];
		$retArray['refMan'] = $list[0]['refManager'];				
	return 	$retArray;
  }
  
/********************/	  
/* Возвращает массив счетов с датой создания от-до*/
	public function getSchetList($fromTime, $toTime, $refOrg)
	{
		$curUser=Yii::$app->user->identity;
		$fromDate = date ("Y-m-d", $fromTime); 
		$toDate   = date ("Y-m-d", $toTime); 

		$schetRecord=array();		
		$schetList=array();		

		
		$clientData= new ClientData();
		
		$loadCounter = 0;
/*Список префиксов*/		
		$list = Yii::$app->db->createCommand(
		            "SELECT id, prefix,  orgTitle, isActive FROM {{%schet_prefix}} where isActive > 0 order by id")->queryAll();				
	    $schetPrefixArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$key = $list[$i]['prefix'];
			$key = $list[$i]['prefix'];
			$schetPrefixArray[$key]=$list[$i]['orgTitle'];			
		}			

		$addCondition = "";	
		//уже синхронизированные 
			$addCondition = "AND (ref1C IS NOT NULL AND ref1C <> '')";
		/*Только текущий*/
		$list = Yii::$app->db->createCommand(
		            "SELECT id, schetNum,  schetINN, schetDate, ref1C FROM {{%schet}} where refManager = ".$curUser->id." AND DATE(schetDate) >= '".$fromDate."'  AND DATE(schetDate) <= '".$toDate."' ".$addCondition." order by id")->queryAll();		
		
	    $schetRefArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			// Создаем идентификатор счета - номер_инн_дата  (Y-m-d)
			if (!empty($list[$i]['schetINN'])) $schetINN = $list[$i]['schetINN'];
										  else $schetINN = "-";
			$key = $list[$i]['schetNum']."_".$schetINN."_".$list[$i]['schetDate'];
			$schetRefArray[$key]=$list[$i]['id'];			
		}

			
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 6')->queryScalar();
		$loadurl =  $url."1&sd=".date("dmY",$fromTime)."&ed=".date("dmY",$toTime);
		$page = $this->get_web_page($loadurl );	
		if ($page['errno'] >0)  return $schetList;
	
		$content = mb_split('\r\n', $page['content'] );		

		/*Первый блок данных*/
			$parse = str_getcsv($content[0],",");		
			
			if (count($parse) < 10 || $parse[1]!= 'Контрагент')  return $schetList; /*Не оно*/		
			
            $parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

// 			$allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
			$schetRecord['sum']=0;
			$schetRecord['schetKey'] = "";
			$schetKey="";
			$curRecord="";
			
		for ($i=1;$i< count($content); $i++)
		{
			
			if(empty ($content[$i])) {continue;}					
			$parse = str_getcsv($content[$i],",");					

			if (count($parse) < 10) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/											
			$lastLoaded	=$parse[0];
			$loadCounter++;
			if ($loadCounter > 2500){break;}
			
			/*1C референс текущего счета*/
			if ($curRecord == "")$curRecord=$parse[3];			
			
		if ($curRecord!=$parse[3] )			
		{
			
			/*Добавление в список*/
			// пропустим 
			//не имеющих номер счета
			//с неправильным префиксом
			//с неcуществующей организацией							
			if(array_key_exists ('schetNum', $schetRecord)  && array_key_exists ($schetRecord['schetPrefix'], $schetPrefixArray) )
			{ 
				
				
			  $list = $this->getSchetOrgRef($schetRecord);		
			  if ($list != false) 
			  {
				  $schetRecord['refOrg'] = $list['refOrg'];			
			      $schetRecord['refMan'] = $list['refMan'];				
				  if ($refOrg == $schetRecord['refOrg'] || $refOrg == 0) $schetList[]=$schetRecord;            
			  }	
		    } /*Добавление в список*/
		
			  /* Выставляем значения по умолчанию */	
				$curRecord=$parse[3];
                unset ($schetRecord);	
				$schetRecord=array();		
				$schetRecord['sum']=0;
				$ig=0;	
				
		}
			
			/*Определяем номер счета*/			
			$schetPos =  mb_strstr( $curRecord,"0");				
			if($schetPos == false) 
		    {				
				$schetNum =preg_replace("/[\D]/u","",$curRecord);			
			}
			else
			{
			   $schetNum = preg_replace("/^0+/u","", $schetPos );
			}
			
			$schetPrefix=preg_replace("/$schetNum/u","", $curRecord);
			$schetPrefix = preg_replace("/0+$/u","", $schetPrefix );
			$schetTime= strtotime(mb_substr($parse[4],1));
 			
			/*Определим идентификатор считанного счета*/
			if (!empty($parse[2])) $schetINN = trim($parse[2]);
										  else $schetINN = "-";
			$schetKey = $schetNum."_".$schetINN."_".date("Y-m-d",strtotime(mb_substr($parse[4],1)));
					
			$sum =  (float)str_replace(',', '.',$parse[9]); 
			$cnt =  (float)str_replace(',', '.',$parse[7]); 
			if ($cnt == 0 ){$cnt = 1;} 
			$schetRecord['schetNum'] = $schetNum;
					
			$schetRecord['schetINN'] = $schetINN;			
			$schetRecord['orgTitle'] = trim($parse[1]);												
			$schetRecord['ed']   = trim($parse[8]);
			$schetRecord['id']   = $curRecord;				
			$schetRecord['sum'] += $sum;
            $schetRecord['schetPrefix'] = $schetPrefix;
            $schetRecord['schetKey'] = $schetKey;
			if (array_key_exists ($schetKey, $schetRefArray)) $schetRecord['refSchet'] = $schetRefArray[$key];
														 else $schetRecord['refSchet'] = 0;
			$schetRecord['date'] = date("Y-m-d", $schetTime);
			$schetRecord['good'][]  = $parse[6];
			$schetRecord['count'][] = $cnt;
			$schetRecord['val'][] = ceil( (100*$sum)/$cnt)/100;			
		}
		
	  if (!empty($schetRecord['schetKey']))	
	  {
	  $list = $this->getSchetOrgRef($schetRecord);		
	  if ($list != false) 
	  {
		  $schetRecord['refOrg'] = $list['refOrg'];			
	      $schetRecord['refMan'] = $list['refMan'];				
	      if ($refOrg == $schetRecord['refOrg'] || $refOrg == 0)  $schetList[]=$schetRecord; //Добавим последнюю           
	  }
	  }
      return $schetList;
		
	}

/********************/		
	public function createSingleSchet($schetKey, $schetList, $zakazId, $eventId, $refOrg )
	{
		$curUser=Yii::$app->user->identity;		 		
		$schetRecord = new SchetList;		
		$schetRecord->refOrg   = $refOrg;
		$schetRecord->refManager   = $curUser->id;
		$schetRecord->refZakaz = $zakazId;
		$schetRecord->save();	
				
	    $zakazRecord = ZakazList::findOne($zakazId);
		if (empty ($zakazRecord)) return 0;
		$zakazRecord->isActive=0;	 
		$zakazRecord->save();
	 		
		
	   /*Добавим запись в календарь*/
	   $calendar = new MarketCalendarForm();
	   $event_ref = 6;
	   $eventNote = "Передать счет клиенту";
	   $calendar->createEvent(date("Y-m-d",time()+60*60*24*1),$event_ref , $schetRecord->refOrg, $schetRecord->refZakaz, 0, $eventNote);	  
	
	   $r= $this->updateSingleSchet($schetRecord->id, $schetKey, $schetList);	 
	   if ($r == 0) return 0;
	  return	$schetRecord->id;		
	}	
	
/********************/	
	
	public function updateSingleSchet($schetId, $schetKey, $schetList )
	{
		
	 $schetRecord=array();
	 $this->forceUpdateSchet == 1;
	 for ($i=0; $i<count($schetList); $i++)
	 {		 
		if ($schetList[$i]['schetKey']==$schetKey)	
		{
		 $schetRecord = $schetList[$i];
		 $r=$this->updateSchetRecordToBase ($schetRecord, $schetId);									
		 return $r;				
		} 
	 }
	
	 return 0;
	}	
/********************/	


	public function loadSchetBase($startRow, $allRecords)
	{
 		$session = Yii::$app->session;		
		$session->open();
		$curUser=Yii::$app->user->identity;
		$res=array();	
		
		$updatedSchet=0;
		$notFindedSchet=0;
		
		$clientData= new ClientData();
		mb_internal_encoding("UTF-8");		
		
		/*получим список префиксов счетов в 1C */

		$list = Yii::$app->db->createCommand(
		            "SELECT id, prefix,  orgTitle, isActive FROM {{%schet_prefix}} where isActive > 0 order by id")->queryAll();
					
	    $schetPrefixArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$key = $list[$i]['prefix'];
			$schetPrefixArray[$key]=$list[$i]['orgTitle'];			
		}
      $ret['schetPrefixArray'] =$schetPrefixArray;		

		/*получим список уже существующих счетов*/

       if ( empty($this->syncDate) )		
	   {
		$fromTime = time() - 60*60*24*90;  /*За последний квартал*/ 
		$toTime = time() ; // до сегодня
	   }
	   else
	   {
		$fromTime = strtotime ($this->syncDate) - 60*60*24;  /*в диапазоне суток*/
		$toTime = $fromTime + 2*60*60*24; /*в диапазоне суток*/
	   }		
		$fromDate = date ("Y-m-d", $fromTime); 
		$toDate = date ("Y-m-d", $toTime); 
			
		$addCondition = "";	
		if ($this->forceUpdateSchet == 0  )
		{
			//уже синхронизированные не трогаем
			$addCondition = " AND (ref1C IS NULL OR ref1C ='') ";
		}
		
		if ( $this->syncAllUser == 1)
		{
			/*для всех пользователей*/
		$list = Yii::$app->db->createCommand(
		            "SELECT id, schetNum,  schetINN, schetDate, ref1C FROM {{%schet}} where schetDate >= '".$fromDate."' and schetDate <= '".$toDate."' ".$addCondition." order by id")->queryAll();
		}
		else{
			/*Только текущий*/
		$list = Yii::$app->db->createCommand(
		            "SELECT id, schetNum,  schetINN, schetDate, ref1C FROM {{%schet}} where refManager = ".$curUser->id." AND schetDate >= '".$fromDate."' and schetDate <= '".$toDate."' ".$addCondition." order by id")->queryAll();		
		}
		
	    $schetRefArray=array();
		$schetRef1CArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			// Создаем идентификатор счета - номер_инн_дата  (Y-m-d)
			if (!empty($list[$i]['schetINN'])) $schetINN = $list[$i]['schetINN'];
										  else $schetINN = "-";
			$key = $list[$i]['schetNum']."_".$schetINN."_".$list[$i]['schetDate'];
			$schetRefArray[$key]=$list[$i]['id'];			
			$schetRef1CArray[$key]=$list[$i]['ref1C'];			
		}
				
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 6')->queryScalar();
		
		/*Load data*/		
		//$loadurl =  $url.$startRow;
		$loadurl =  $url.$startRow."&sd=".date("dmY",$fromTime)."&ed=".date("dmY",$toTime);
		$page = $this->get_web_page($loadurl );	
		
		$content = mb_split('\r\n', $page['content'] );

		
	
		$schetRecord=array();		
		$err=array();	
        $lastLoaded=0;		
		$loadCounter=0;
		$i=0;
		$curRecord = "";
		$ig=0;
		if ($startRow == 1) 
		{
			/*Первый блок данных*/
// 			$parse = str_getcsv($content[$i],",");		
// 			$allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
			$parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

			$i=1;
			$schetRecord['sum']=0;
			$schetKey="";
		}
		else
		{
			/*Что там у нас в предыдущем блоке не записано*/
			$schetRecord=$session->get('schetRecord');			
			if (isset($schetRecord['id']))	{$curRecord = $schetRecord['id']; }
			if (isset($schetRecord['schetKey'])) {$schetKey = $schetRecord['schetKey'] ;}
			else {$schetKey = "";}
		}
		
		for ($i;$i< count($content); $i++)
		{
			
			if(empty ($content[$i])) {continue;}					
			$parse = str_getcsv($content[$i],",");					

			if (count($parse) < 10) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/											
			$lastLoaded	=$parse[0];
			$loadCounter++;
			if ($loadCounter > 2500){break;}
			
			/*1C референс текущего счета*/
			if ($curRecord == ""){$curRecord=$parse[3];}			
						
			 /* считанная запись имеет другой 1C референс - пора сохранять*/				
		  if ($curRecord!=$parse[3] )			
		  {
			if ($this->saveSchetRecord($schetRecord,$schetPrefixArray, $schetRefArray))$updatedSchet++;
			
			  /* Выставляем значения по умолчанию */	
				$curRecord=$parse[3];
                unset ($schetRecord);	
				$schetRecord=array();		
				$schetRecord['sum']=0;
				$ig=0;
  		  } /*Save to base*/
			
			/*Определяем номер счета*/			
			$schetPos =  mb_strstr( $curRecord,"0");				
			if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$curRecord);	}
			else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );	}			
			
			$schetPrefix=preg_replace("/$schetNum/u","", $curRecord);
			$schetPrefix = preg_replace("/0+$/u","", $schetPrefix );

			$schetTime= strtotime(mb_substr($parse[4],1));
 			
			/*Определим идентификатор считанного счета*/
			if (!empty($parse[2])) $schetINN = trim($parse[2]);
										  else $schetINN = "-";
			$schetKey = $schetNum."_".$schetINN."_".date("Y-m-d",strtotime(mb_substr($parse[4],1)));
						
			$sum =  (float)str_replace(',', '.',$parse[9]); 
			$cnt =  (float)str_replace(',', '.',$parse[7]); 
			if ($cnt == 0 ){$cnt = 1;} 
			$schetRecord['orgTitle'] = trim($parse[1]);
			$schetRecord['schetNum'] = $schetNum;
			$schetRecord['schetINN'] = $schetINN;			
			$schetRecord['ed']   = trim($parse[8]);
			$schetRecord['id']   = $curRecord;
			$schetRecord['sum'] += $sum;
            $schetRecord['schetPrefix'] = $schetPrefix;
            $schetRecord['schetKey'] = $schetKey;
			$schetRecord['date'] = date("Y-m-d", $schetTime);
			$schetRecord['good'][$ig]  = $parse[6];
			$schetRecord['count'][$ig] = $cnt;
			$schetRecord['val'][$ig] = ceil( (100*$sum)/$cnt)/100;
			$ig++;
		}
			
		 	/*************************************************************/
			if ($lastLoaded == $allRecords )
			{
				if ($this->saveSchetRecord($schetRecord,$schetPrefixArray, $schetRefArray))$updatedSchet++;				
				//$strSql="UPDATE {{%schet}} set isSchetActive =0 where ref1c IS NOT NULL AND schetDate < DATE_SUB(CURDATE(),Interval 90 DAY)";
				//Yii::$app->db->createCommand($strSql)->execute();
			}
		 	/*************************************************************/			
	   $session->set('schetRecord', $schetRecord);					
	   $ret['allRecords'] = $allRecords;
	   $ret['lastLoaded'] = $lastLoaded;
	   $ret['updatedSchet'] = $updatedSchet;
	   $ret['notFindedSchet'] = $notFindedSchet;
	   
//$ret['loaded'] = $res;	   
//$ret['err'] = $err;
	   return $ret;
	}
	
	
   public function saveSchetRecord($schetRecord,$schetPrefixArray, $schetRefArray)
   {
																		// пропустим 
	  if(!array_key_exists ('schetNum', $schetRecord)) return false; 	//не имеющих номер счета
	  if(!array_key_exists ($schetRecord['schetPrefix'], $schetPrefixArray)) return false; //с неправильным префиксом
			
	  //Определим ссылки связанные с таблицей клиентов
	  $list = $this->getSchetOrgRef($schetRecord);		
	  if ($list == false) return false; //с неcуществующей организацией
	  $schetRecord['refOrg'] = $list['refOrg'];			
	  $schetRecord['refMan'] = $list['refMan'];				
	              
	  $schetKey =$schetRecord['schetKey'];
  	  if (array_key_exists ($schetKey, $schetRefArray))
	  {
		/*Счет есть в базе*/
		if ($this->updateExistedSchet != 1) return false; // Не апдейтить счета
		if ($this->forceUpdateSchet == 1 || empty($schetRef1CArray[$schetKey]) )
		{					
			$r=$this->updateSchetRecordToBase ($schetRecord, $schetRefArray[$schetKey]);									
			if ($r==1) return true;
		}
	  }
	  else
	  {		
       /*Счета нет в базе*/		
		if ($this->createNewSchet == 1)
	    {
		   $r=$this->addSchetRecordToBase ($schetRecord);									  
		   if ($r == 1) return true;
		}
	  }
	  return false;	
   }   
/********************/	
	public function updateSchetRecordToBase ($schetRecord, $schetId)
	{
			
			/*Save last to base*/			
			/*Ищем запись заказа*/

  		    $schetRec = SchetList::findOne($schetId);
			if (empty ($schetRec) ) {return 0;} 
			
			$zakazRec = ZakazList::findOne($schetRec->refZakaz);
			if (empty ($zakazRec)) 
			{
				/*Если не нашли то создали*/
				   $zakazRec = new ZakazList();
				   $zakazRec->formDate =$schetRecord['date'];
				   $zakazRec->refOrg =$schetRecord['refOrg'];
				   $zakazRec->isActive=0;
				   $zakazRec->isFormed=1;
				   $zakazRec->isGoodReserved=1;
				   $zakazRec->save();
			}	   
			
			$uid= $zakazRec->id;
			unset ($zakazRec);
				/*Содержимое заявки*/
			if ($this->forceUpdateSchet == 1 || empty ($schetRec->ref1C))
			{
				// Снесем текущее состояние, оставим итоговый вариант, но сохраним историю согласования
				$strSql="DELETE FROM {{%zakazContent}} WHERE isActive =1 AND  refZakaz = ".$uid;
				Yii::$app->db->createCommand($strSql)->execute();
				$list = array();			
			
				for($k=0; $k< count($schetRecord['good']);$k++)
				{
				 array_push($list, [$uid,$schetRecord['good'][$k],$schetRecord['good'][$k],$schetRecord['count'][$k],$schetRecord['count'][$k],$schetRecord['val'][$k],'1', $schetRecord['ed']  ]);						
				}
				Yii::$app->db->createCommand()->batchInsert('{{%zakazContent}}', ['refZakaz', 'initialZakaz','good','count','reserved', 'value','isActive','ed'  ], $list)->execute();    	  
				unset ($list);
            }  
				
				/*Обновим*/
				$schetRec->schetNum = $schetRecord['schetNum'];
				$schetRec->ref1C = $schetRecord['id'];
				$schetRec->refOrg= $schetRecord['refOrg'];
				$schetRec->schetDate= $schetRecord['date'];
				$schetRec->schetINN = $schetRecord['schetINN'];				
				$schetRec->refZakaz= $uid;
				if (empty($schetRec->refManager) ){$schetRec->refManager= $schetRecord['refMan'];}
				$schetRec->schetSumm= $schetRecord['sum'];				
				$schetRec->save();	
				
                unset ($schetRec);
				

		return 1;
	}
/********************/	
/********************/	
	public function addSchetRecordToBase ($schetRecord)
	{
		
			/*Save last to base*/
				$zakazRec = new ZakazList();
				$zakazRec->formDate =$schetRecord['date'];
				$zakazRec->refOrg =$schetRecord['refOrg'];
				$zakazRec->isActive=0;
				$zakazRec->isFormed=1;
				$zakazRec->isGoodReserved=1;
				$zakazRec->save();
				$uid= $zakazRec->id;
				unset ($zakazRec);

				$list = array();			
				for($k=0; $k< count($schetRecord['good']);$k++)
				{
				 array_push($list, [$uid,$schetRecord['good'][$k],$schetRecord['good'][$k],$schetRecord['count'][$k],$schetRecord['count'][$k],$schetRecord['val'][$k],'1', $schetRecord['ed']  ]);						
				}
				Yii::$app->db->createCommand()->batchInsert('{{%zakazContent}}', ['refZakaz', 'initialZakaz','good','count','reserved','value','isActive','ed'  ], $list)->execute();    	  
				unset ($list);
				
				$schetRec = new SchetList();
					$schetRec->schetNum = $schetRecord['schetNum'];
					$schetRec->ref1C = $schetRecord['id'];
					$schetRec->refOrg= $schetRecord['refOrg'];
					$schetRec->schetDate= $schetRecord['date'];
					$schetRec->schetINN = $schetRecord['schetINN'];				
					$schetRec->refZakaz= $uid;
					$schetRec->refManager= $schetRecord['refMan'];
					$schetRec->schetSumm= $schetRecord['sum'];
				$schetRec->save();	
                unset ($schetRec);
				
		return 1;
	}

/********************/	
public function fixOplataToBase()
	{
		mb_internal_encoding("UTF-8");		
		$session = Yii::$app->session;		
		$session->open();
		$schetRefArray=$session->get('schetRefArray');			
		$schetSumArray=$session->get('schetSumArray');			

		foreach ($schetRefArray as $key => $id) 
		{					
		$strSql="UPDATE {{%schet}} set summOplata =:summOplata where id=:id";
				Yii::$app->db->createCommand($strSql)
				->bindValue(':summOplata',$schetSumArray[$key] )
				->bindValue(':id',$id)
				->execute(); 
		}	
	 
		$strSql="UPDATE {{%schet}} set isOplata = 1 where   isOplata = 0 AND ifnull(schetSumm,0) <= ifnull(summOplata,0) ";
				Yii::$app->db->createCommand($strSql)
				->execute();		
	}

	/********************/	
	
public function linkOplataToSchet()
{
	
		$curUser=Yii::$app->user->identity;	
    	$strSql=" update {{%oplata}} as a, {{%schet}} as b set a.refOrg = b.refOrg, a.refSchet = b.id, a.refZakaz = b.refZakaz
		where a.schetRef1C = b.ref1C and a.schetDate = b.schetDate and ( a.refSchet IS NULL or a.refSchet =0)";	
		if ( $this->syncAllUser == 0) $strSql.=" and b.refManager = ".$curUser->id;		
		if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
		                        else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
 
		$strSql="update {{%schet}} as a set a.summOplata = (SELECT SUM(oplateSumm) from {{%oplata}} where refSchet = a.`id`)
		where a.isSchetActive = 1 ";	
		if ( $this->syncAllUser == 0) $strSql.=" and a.refManager = ".$curUser->id;		
		if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
		                        else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
        Yii::$app->db->createCommand($strSql)	->execute();		


		$strSql="UPDATE {{%schet}} set isOplata = 1 where   isOplata = 0 AND ifnull(schetSumm,0) <= ifnull(summOplata,0) AND isSchetActive = 1 ";	
		if ( $this->syncAllUser == 0) $strSql.=" and refManager = ".$curUser->id;		
		if (!empty ($this->syncDate))  $strSql.=" and schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
		                        else   $strSql.=" and schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
        Yii::$app->db->createCommand($strSql)	->execute();		
		
}	
/********************/	
	public function loadOplataBase($startRow, $allRecords)
	{
		mb_internal_encoding("UTF-8");		
		
		$res=array();	
	
 		$session = Yii::$app->session;		
		$session->open();

	    $list = Yii::$app->db->createCommand(
		            "SELECT id, prefix,  orgTitle, isActive FROM {{%schet_prefix}} where isActive > 0 order by id")->queryAll();
	    $schetPrefixArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$key = $list[$i]['prefix'];
			$schetPrefixArray[$key]=$list[$i]['orgTitle'];			
		}
		
		$ret['schetPrefixArray'] =$schetPrefixArray;		

        if ($startRow == 1)	
	    {
		/*Последняя полученная оплата*/
		$lastOplata = Yii::$app->db->createCommand(
		            "SELECT ifnull(max(oplateDate),'2010-01-01') FROM {{%oplata}}")->queryScalar();				
		$lastOplataTime = strtotime ($lastOplata);

	
        /*Получим список уже занесенных сегодня оплат */		
		$list = Yii::$app->db->createCommand(
		            "SELECT id, oplateDate, ref1C, orgINN, oplateSumm FROM {{%oplata}} where oplateDate=:oplateDate")
					->bindValue(':oplateDate',$lastOplata)
					->queryAll();						
					
		$oplataRefArray = array();			
		for($i=0; $i < count ($list); $i++)
		{
			// Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)		
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
										else  $orgINN = "-";
			$key = $list[$i]['ref1C']."_".$list[$i]['oplateDate']."_".$orgINN."_".$list[$i]['oplateSumm'];
			$oplataRefArray[$key]=$list[$i]['id'];						
		}
							
		$session->set('oplataRefArray', $oplataRefArray);
		$session->set('lastOplataTime', $lastOplataTime);
		}
		else 
		{
			$oplataRefArray = $session->get('oplataRefArray');
			$lastOplataTime = $session->get('lastOplataTime');
			if (empty ($lastOplataTime)) return false;
			if (empty ($oplataRefArray)) $oplataRefArray = array();
		}					
		
		/*Load data*/
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 7')->queryScalar();
		//$loadurl =  $url.$startRow;
		$loadurl =  $url.$startRow."&sd=".date("dmY",$lastOplataTime)."&ed=".date("dmY",time());
		
		$ret['loadurl'][] =$loadurl;					
		
		$page = $this->get_web_page($loadurl );	
		$content = mb_split('\r\n', $page['content'] );

		$err=array();	
        $lastLoaded=0;		
		$loadCounter=0;
		$i=0;
		$curRecord = "";
		$updatedOplata = 0;
		
		if ($startRow == 1) 
		{
			/*Первый блок данных*/
			$parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
			$i=1;
		}				
		
		$scanKey=array();
		for ($i;$i< count($content); $i++)
		{		               
			if(empty ($content[$i])) {continue;}					
			$parse = str_getcsv($content[$i],",");					
			$lastLoaded	=$parse[0];

			$loadCounter++;
			if ($loadCounter > 2500){break;}				
			
			if (count($parse) < 9) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/											
			
			
			$ref1C=trim($parse[5]);
			$schetRef1C= trim($parse[8]);
			/*Определяем номер счета*/			
			$schetPos =  mb_strstr( $schetRef1C,"0");				
			if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$schetRef1C);	}
			else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );	}			

			$schetTime= strtotime(mb_substr($parse[7],1));
 			$schetDate=date("Y-m-d", $schetTime);
			
			
			/*Определяем номер платежки*/			
			$oplatePos =  mb_strstr( $ref1C,"0");				
			if($oplatePos == false)   {$oplateNum = preg_replace("/[\D]/u","",$ref1C);	}
			else                      {$oplateNum = preg_replace("/^0+/u","", $oplatePos );	}			
			
			$schetPrefix=preg_replace("/$oplateNum/u","", $ref1C);
			$schetPrefix = preg_replace("/0+$/u","", $schetPrefix );

			if(!array_key_exists ($schetPrefix, $schetPrefixArray)) continue; //с неправильным префиксом
			
			
			$oplataTime= strtotime(mb_substr($parse[4],1));
			$oplateDate=date("Y-m-d", $oplataTime);
			
			if (!empty ($parse[2])) $orgINN = trim($parse[2]);
							  else  $orgINN = "-";
			$oplateSumm =  (float)str_replace(',', '.',$parse[9]);
			
			$key = $ref1C."_".$oplateDate."_".$orgINN."_".$oplateSumm;			
			if(array_key_exists ($key, $oplataRefArray)) continue;  //уже есть
			//$scanKey[]=$key;
			
			$oplataRecord = new OplataList();	
			$oplataRecord->ref1C =$ref1C; 
			$oplataRecord->oplateDate = $oplateDate;
			$oplataRecord->oplateSumm = $oplateSumm;
			$oplataRecord->oplateNum = $oplateNum;
			$oplataRecord->schetRef1C = $schetRef1C;
			$oplataRecord->orgTitle   = trim($parse[1]);
			$oplataRecord->orgINN     = $orgINN;
			$oplataRecord->schetNum   = $schetNum;
			$oplataRecord->schetDate  = $schetDate;
			$oplataRecord->save();
			$updatedOplata++;
			
		}//
				
	   $ret['allRecords'] = $allRecords;
	   $ret['lastLoaded'] = $lastLoaded;
	   $ret['updatedOplata'] = $updatedOplata;
	   $ret['err'] = $err;
	   //$ret['scanKey'] =$scanKey;
	   return $ret;
	}
/********************/		
	

	
/********************/	
/********************/	
public function fixSupplyToBase()
	{
		mb_internal_encoding("UTF-8");		
		$session = Yii::$app->session;		
		$session->open();
		$schetRefArray=$session->get('schetRefArray');			
		$schetSumArray=$session->get('schetSumArray');			

//print_r($schetRefArray);
//print_r($schetSumArray);
		foreach ($schetRefArray as $key => $id) 
		{		
echo "id=".$id." sum=".$schetSumArray[$key]."\n";
			
		$strSql="UPDATE {{%schet}} set summSupply =:summSupply where id=:id";
				Yii::$app->db->createCommand($strSql)
				->bindValue(':summSupply',$schetSumArray[$key] )
				->bindValue(':id',$id)
				->execute(); 
				
		}	
	 
		$strSql="UPDATE {{%schet}} set isSupply = 1 where   isSupply = 0 AND ifnull(schetSumm,0) <= ifnull(summSupply,0) ";
				Yii::$app->db->createCommand($strSql)
				->execute();		
	}
/********************/	
public function linkSupplyToSchet()
{
	$curUser=Yii::$app->user->identity;

    $strSql=" update {{%supply}} as a, {{%schet}} as b set a.refOrg = b.refOrg, a.refSchet = b.id, a.refZakaz = b.refZakaz
    where a.schetRef1C = b.ref1C and a.schetDate = b.schetDate and ( a.refSchet IS NULL or a.refSchet =0)";
	
	if ( $this->syncAllUser == 0) $strSql.=" and b.refManager = ".$curUser->id;
	if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
		                    else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";							
    Yii::$app->db->createCommand($strSql)	->execute();		
	
	
	$strSql="update {{%schet}} as a set a.summSupply = (SELECT SUM(supplySumm) from {{%supply}} where refSchet = a.`id`)
	where a.isSchetActive = 1 ";	
	if ( $this->syncAllUser == 0)  $strSql.=" and a.refManager = ".$curUser->id;		
	if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
	                        else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
      Yii::$app->db->createCommand($strSql)	->execute();		

	$strSql="UPDATE {{%schet}} set isSupply = 1 where   isSupply = 0 AND ifnull(schetSumm,0) <= ifnull(summSupply,0) AND isSchetActive = 1 ";	
	if ( $this->syncAllUser == 0) $strSql.=" and refManager = ".$curUser->id;		
	if (!empty ($this->syncDate))  $strSql.=" and schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
	                        else   $strSql.=" and schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
     Yii::$app->db->createCommand($strSql)	->execute();		

	
}
	
public function loadSupplyBase($startRow, $allRecords)
	{
		mb_internal_encoding("UTF-8");		
		
		$res=array();	
	
 		$session = Yii::$app->session;		
		$session->open();

	    $list = Yii::$app->db->createCommand(
		            "SELECT id, prefix,  orgTitle, isActive FROM {{%schet_prefix}} where isActive > 0 order by id")->queryAll();
	    $schetPrefixArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$key = $list[$i]['prefix'];
			$schetPrefixArray[$key]=$list[$i]['orgTitle'];			
		}
		
		$ret['schetPrefixArray'] =$schetPrefixArray;		

        if ($startRow == 1)	
	    {
		/*Последняя полученная оплата*/
		$lastSupply = Yii::$app->db->createCommand(
		            "SELECT ifnull(max(supplyDate),'2010-01-01') FROM {{%supply}}")->queryScalar();				
		$lastSupplyTime = strtotime ($lastSupply);

	
        /*Получим список уже занесенных сегодня оплат */		
		$list = Yii::$app->db->createCommand(
		            "SELECT id, supplyDate, ref1C, orgINN, supplySumm FROM {{%supply}} where supplyDate=:supplyDate")
					->bindValue(':supplyDate',$lastSupply)
					->queryAll();						
					
		$supplyRefArray = array();			
		for($i=0; $i < count ($list); $i++)
		{
			// Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)		
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
										else  $orgINN = "-";
			$key = $list[$i]['ref1C']."_".$list[$i]['supplyDate']."_".$orgINN."_".$list[$i]['supplySumm'];
			$supplyRefArray[$key]=$list[$i]['id'];						
		}
							
		$session->set('supplyRefArray', $supplyRefArray);
		$session->set('lastSupplyTime', $lastSupplyTime);
		}
		else 
		{
			$supplyRefArray = $session->get('supplyRefArray');
			$lastSupplyTime = $session->get('lastSupplyTime');
			if (empty ($lastSupplyTime)) return false;
			if (empty ($supplyRefArray)) $supplyRefArray = array();
		}					
		
		/*Load data*/
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 8')->queryScalar();
		//$loadurl =  $url.$startRow;
		$loadurl =  $url.$startRow."&sd=".date("dmY",$lastSupplyTime)."&ed=".date("dmY",time());
		
		$ret['loadurl'][] =$loadurl;					
		
		$page = $this->get_web_page($loadurl );	
		$content = mb_split('\r\n', $page['content'] );

		$err=array();	
        $lastLoaded=0;		
		$loadCounter=0;
		$i=0;
		$curRecord = "";
		$updatedSupply = 0;
		
		if ($startRow == 1) 
		{
			/*Первый блок данных*/
// 			$parse = str_getcsv($content[$i],",");		
// 			$allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
			$parse = str_getcsv($content[$i],",");		
			$tmp = explode("/", $parse[0]);/*на случай фигни*/  
			$allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

			$i=1;
		}				
		
		$scanKey=array();
		for ($i;$i< count($content); $i++)
		{		               
			if(empty ($content[$i])) {continue;}					
			$parse = str_getcsv($content[$i],",");					
			$lastLoaded	=$parse[0];

			$loadCounter++;
			if ($loadCounter > 2500){break;}				
			
			if (count($parse) < 13) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/											
			
			
			$ref1C=trim($parse[5]);
			$schetRef1C= trim($parse[8]);
			/*Определяем номер счета*/			
			$schetPos =  mb_strstr( $schetRef1C,"0");				
			if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$schetRef1C);	}
			else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );	}			

			$schetTime= strtotime(mb_substr($parse[7],1));
 			$schetDate=date("Y-m-d", $schetTime);
			
			
			/*Определяем номер платежки*/			
			$supplyPos =  mb_strstr( $ref1C,"0");				
			if($supplyPos == false)   {$supplyNum = preg_replace("/[\D]/u","",$ref1C);	}
			else                      {$supplyNum = preg_replace("/^0+/u","", $supplyPos );	}			
			
			/*$supplyRep = preg_replace("/\//u","\\/",$supplyNum );
			/$schetPrefix=preg_replace("/$supplyRep/u","", $ref1C);*/
			$schetPrefix=str_replace($supplyNum, "", $ref1C);
			$schetPrefix = preg_replace("/0+$/u","", $schetPrefix );

			if(!array_key_exists ($schetPrefix, $schetPrefixArray)) continue; //с неправильным префиксом
			
			
			$supplyTime= strtotime(mb_substr($parse[4],1));
			$supplyDate=date("Y-m-d", $supplyTime);
			
			if (!empty ($parse[2])) $orgINN = trim($parse[2]);
							  else  $orgINN = "-";
			if (!empty ($parse[13])) $orgKPP = trim($parse[13]);
							  else  $orgKPP = "-";							  
			$supplySumm =  (float)str_replace(',', '.',$parse[12]);
			$supplyCount=  (float)str_replace(',', '.',$parse[10]);
			
			$key = $ref1C."_".$supplyDate."_".$orgINN."_".$supplySumm;			
			if(array_key_exists ($key, $supplyRefArray)) continue;  //уже есть
			//$scanKey[]=$key;
			
			$supplyRecord = new SupplyList();	
			
			$supplyRecord->schetNum   = $schetNum;
			$supplyRecord->schetDate  = $schetDate;
			$supplyRecord->schetRef1C = $schetRef1C;
			$supplyRecord->orgINN     = $orgINN;
			
//ALTER TABLE `tbl_supply` ADD `orgKPP` VARCHAR(15) NOT NULL , ADD INDEX `supply_orgkpp` (`orgKPP`);	
//            $supplyRecord->orgKPP     = orgKPP;
            
			$supplyRecord->orgTitle   = trim($parse[1]);		
			$supplyRecord->ref1C =$ref1C; 
			
			$supplyRecord->supplyDate = $supplyDate;
			$supplyRecord->supplySumm = $supplySumm;
			$supplyRecord->supplyGood =  trim($parse[9]);
			$supplyRecord->supplyCount = $supplyCount;
			$supplyRecord->supplyEd	= trim($parse[11]);		
			$supplyRecord->supplyNum = $supplyNum;


			$supplyRecord->save();
			$updatedSupply++;
//ALTER TABLE `tbl_supply` ADD `orgKPP` VARCHAR(15) NOT NULL , ADD INDEX `supply_orgkpp` (`orgKPP`);			
		}//
				
	   $ret['allRecords'] = $allRecords;
	   $ret['lastLoaded'] = $lastLoaded;
	   $ret['updatedSupply'] = $updatedSupply;
	   $ret['err'] = $err;
	   //$ret['scanKey'] =$scanKey;
	   return $ret;
	}
	
	
	
/********************/	

	public function loadContactsBase()
	{
		
		/*получим список уже импортированных счетов*/
		$list = Yii::$app->db->createCommand(
            'SELECT id, ref1C FROM {{%schet}} where ref1C is not null order by id')->queryAll();
	    $schetRefArray=array();
		for($i=0; $i < count ($list); $i++)
		{
			$schetRefArray[$list[$i]['ref1C']]=$list[$i]['id'];			
		}

		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 9')->queryScalar();
	
	
	    $isReadNext = 1;
	     
		$startRow=0; 
		/*Load data*/
		while ($isReadNext == 1)
		{
	     		
		$loadurl =  $url.$startRow;
		$page = $this->get_web_page($loadurl );	
		
		$content = mb_split('\r\n', $page['content'] );
		$res=array();		
		$err=array();	
        $loaded=0;		
		for ($i=4;$i< count($content); $i++)
		{
			
			$parse = str_getcsv($content[$i],",");		
			if (count($parse) < 4) 
			{
				$err[] = $parse;
				continue;
			}/*Not enough fields*/
			$loaded++;
			
			
			$res[]=$parse;		
		}
		
	    }/*load all part*/
	
	   $ret['loaded'] = $res;
	   $ret['err'] = $err;
	   return $ret;
	}
/*****************************************/
/**********  Склад ***********************/	
/*****************************************/		
	public function syncSclad1C()
	{
		mb_internal_encoding("UTF-8");
		$res=array();
/*
Получим текущий склад
*/		

		
		$scladArray=array();
		
 		$list = Yii::$app->db->createCommand(
		            "SELECT id, title,  articul FROM {{%warehouse}}  order by id")
					->queryAll();
				
			for($i=0; $i < count ($list); $i++)
			{
			if (empty($list[$i]['title'])){continue;}
				$key = trim($list[$i]['title']);
				$scladArray[$key]=$list[$i]['id'];						
			}		
			unset ($list);		
				
/*
Получим данные из 1с
*/		
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 10')->queryScalar();
		$url.="1";
		
//$res[]=$url;
		
		/*список организаций через ','*/
		$val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 11')->queryScalar();
		$orgList = str_getcsv($val,",");		
			
//$res[]=$orgList;
		/*список складов через ','*/
		$val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 12')->queryScalar();
		$scladList = str_getcsv($val,",");		
//$res[]=$scladList;					
		
        $page = $this->get_web_page( $url);			
		$content = mb_split('\r\n', $page['content'] );		

//$res[]=$page;							
//$res[]=$content;									
		
		$parse = str_getcsv($content[0],",");	
		$rowNum=$parse[0];
//$res[]=$rowNum;				
		$n=count($content);
		
		for ($i=1; $i<$n;$i++ )
		{
			$parse = str_getcsv($content[$i],",");	
			if (count($parse) < 8) {continue;} /*не полная запись*/
			$amount=(float)str_replace(',', '.',$parse[7]);
			$price=(float)str_replace(',', '.',$parse[8]); 
		  if ($amount <=0 ) continue; //нах не надо
		  if ($price  <=0 )	continue; //нах не надо

			
			if ( !in_array($parse[1], $orgList)   ) {continue;} /*не наша организация*/
			if ( !in_array($parse[2], $scladList) ) {continue;} /*не наш склад*/
		
		    /*наименование*/
			$key = trim($parse[5]);
			
			/*К-во*/
			$parse[7] = preg_replace("/[^(0-9.,)]/","",$parse[7]);
			/*Цена*/
			$parse[8] = preg_replace("/[^(0-9.,)]/","",$parse[8]);
			/*Себестоимость*/
			$parse[9] = preg_replace("/[^(0-9.,)]/","",$parse[9]);
			
			if (!array_key_exists ($key, $scladArray) )
			{
//$parse[10]='add';				
//$res[]=$parse;					
				$scladRecord=	new ScladList();
				$scladRecord->grpGood=trim($parse[4]);
				$scladRecord->title = trim($parse[5]);
				$scladRecord->ed =trim($parse[6]);
				$scladRecord->amount = $amount;				
				$scladRecord->price  = $price; 
				$scladRecord->relizePrice  = round($price*$this->priceKF, 2); 
//фигня тут			$scladRecord->initPrice =(float)str_replace(',', '.',$parse[9]); 
				$scladRecord->isValid = 1;
				$scladRecord->save();
				$scladArray[$scladRecord->title]=0;
			}
			else
			{			 	
		     
			  $scladRecord= ScladList::findOne($scladArray[$key]);
			  if (empty($scladRecord)) {continue;}
	
				if ( ($scladRecord->amount != $amount) || ($scladRecord->relizePrice != round($price*$this->priceKF, 2)) || ($scladRecord->price != $price) || $scladRecord->isValid == 0)
				{
				$scladRecord->grpGood=trim($parse[4]);
				$scladRecord->title = trim($parse[5]);
				$scladRecord->ed =trim($parse[6]);
				$scladRecord->amount = $amount;
				$scladRecord->price =$price; 				
				$scladRecord->relizePrice  = round($price*$this->priceKF, 2); 
//фигня тут				$scladRecord->initPrice =$initPrice ; 
				$scladRecord->isValid = 1;
				$scladRecord->save();			
				}
			  $scladArray[$key] =0;//использован
			  
			}
		}
		/*Если не нашли в 1C*/
/*		как оказалось плохая практика удалять */
			foreach ($scladArray as $key => $val) 
			{					
			  if ($val > 0)
			  {

				/*Yii::$app->db->createCommand('DELETE FROM {{%otves_list}} where refWarehouse=:refWarehouse')
					->bindValue(':refWarehouse', $scladArray[$key])
					->execute();*/			  			
					$scladRecord= ScladList::findOne($scladArray[$key]);
					$scladRecord->isValid = 0;
					$scladRecord->save();
			
			  }
			}	
return 	$res;	
	}	
	
	
/****************/

	
	public function syncScladByGoogle()
	{
		mb_internal_encoding("UTF-8");

/*
Получим текущий склад
*/		
		
		
		$scladArray=array();
		
 		$list = Yii::$app->db->createCommand(
		            "SELECT id, title,  articul FROM {{%warehouse}}  order by id")
					->queryAll();
				
			for($i=0; $i < count ($list); $i++)
			{
			// Создаем идентификатор счета - ref1C_инн_дата  (Y-m-d) - ref1C тут определен для всех
			if (empty($list[$i]['title'])){continue;}
				$key = $list[$i]['title'];
				$scladArray[$key]=$list[$i]['id'];						
			}		
			unset ($list);		
		
		
/*
Получим данные из 1с
*/		
		
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 1')->queryScalar();
		$url.="&single=true&output=csv";

        $page = $this->get_web_page( $url);			
		$content = mb_split('\r\n', $page['content'] );		
		
		$n=count($content);
		$res=array();

		for ($i=1; $i<$n;$i++ )
		{
			$parse = str_getcsv($content[$i],",");	
			if (count ($parse) < 4) {continue;}
			if (trim($parse[3]) == "#N/A"){continue;} //Цена не задана
			$key = trim($parse[0]);
			/*Уберем пробел который гугл ставит*/
			//$parse[1] = str_replace(' ', '',$parse[1]);
			//$parse[3] = str_replace(' ', '',$parse[3]);
			
			$parse[1] = preg_replace("/[^(0-9.,)]/","",$parse[1]);
			$parse[3] = preg_replace("/[^(0-9.,)]/","",$parse[3]);
			
/*
echo "<pre>";
print_r($parse);			
echo "</pre>";			
*/
			if (!array_key_exists ($key, $scladArray))
			{
			  $scladRecord=	new ScladList();
				$scladRecord->title = trim($parse[0]);
				$scladRecord->amount = (float)str_replace(',', '.',$parse[1]);
				$scladRecord->ed =trim($parse[2]);
				$scladRecord->price =(float)str_replace(',', '.',$parse[3]); 
				$scladRecord->edPrice = trim($parse[4]);
				$scladRecord->save();
			}
			else
			{			 	
			  $scladRecord= ScladList::findOne($scladArray[$key]);
			  if (empty($scladRecord)) {continue;}
				$amount=(float)str_replace(',', '.',$parse[1]);
				$price=(float)str_replace(',', '.',$parse[3]); 
	
				if ($scladRecord->amount <> $amount || $scladRecord->price <> $price)
				{
					$scladRecord->amount = $amount;
					$scladRecord->ed =trim($parse[2]);
					$scladRecord->price =$price; 
					$scladRecord->edPrice = trim($parse[4]);
					$scladRecord->save();			
				}
			  $scladArray[$key] =0;//использован
			  
			}
		}
		
		foreach ($scladArray as $key => $val) 
			{					
			  if ($val > 0)
			  {
				$scladRecord= ScladList::findOne($scladArray[$key]);
				$scladRecord->delete();
			  }
			}	
			
		
	}	
	
	
	
/*****************************************/	
/******   Провайдеры данных **************/	
/*****************************************/

public function getOplataImportedProvider($params)
   {
				 
	$query  = new Query();
   	$query->select ("title, {{%schet}}.id, {{%schet}}.schetSumm, {{%schet}}.schetNum, {{%schet}}.schetDate,  {{%schet}}.refManager, ref1C")
	        ->from("{{%schet}}")			
	        ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
			->where("isOplata = 1 AND ref1C is not null" );
			

	$countquery  = new Query();
   	$countquery->select (" count({{%schet}}.id)")
	        ->from("{{%schet}}")			
	        ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
			->where("isOplata = 1 AND ref1C is not null" );

   	
	if (($this->load($params) && $this->validate())) {
     /*
	 $query->andFilterWhere(['like', 'title', $this->title]);
	 $countquery->andFilterWhere(['like', 'city', $this->city]);
	 */
     }

	$command = $query->createCommand();	
	$count = $countquery->createCommand()->queryScalar();

	
	$dataProvider = new SqlDataProvider([
			'sql' => $command ->sql,
			'params' => $command->params,			
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 8,
			],
			
			'sort' => [
			
			'attributes' => [
			'title', 
			'schetNum', 
			'schetDate',  
			'refManager', 
			'schetSumm',
			'ref1C'	
			 ],
			
			],
			
		]);


		
	return  $dataProvider;	 
   }   


public function getSchetImportedProvider($params)
   {
				 
	$query  = new Query();
   	$query->select ("title, {{%schet}}.id, {{%schet}}.schetSumm, {{%schet}}.schetNum, {{%schet}}.schetDate,  {{%schet}}.refManager, ref1C")
	        ->from("{{%schet}}")			
	        ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
			->where("ref1C is not null" );
			

	$countquery  = new Query();
   	$countquery->select (" count({{%schet}}.id)")
	        ->from("{{%schet}}")			
	        ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
			->where("ref1C is not null" );

   	
	if (($this->load($params) && $this->validate())) {
     /*
	 $query->andFilterWhere(['like', 'title', $this->title]);
	 $countquery->andFilterWhere(['like', 'city', $this->city]);
	 */
     }

	$command = $query->createCommand();	
	$count = $countquery->createCommand()->queryScalar();

	
	$dataProvider = new SqlDataProvider([
			'sql' => $command ->sql,
			'params' => $command->params,			
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 8,
			],
			
			'sort' => [
			
			'attributes' => [
			'title', 
			'schetNum', 
			'schetDate',  
			'refManager', 
			'schetSumm',
			'ref1C'	
			 ],
			
			],
			
		]);


		
	return  $dataProvider;	 
   }   



   public function getClientImportedProvider($params)
   {
				 
	$query  = new Query();
   	$query->select ("{{%orglist}}.id, {{%orglist}}.title, {{%orglist}}.have_phone, schetINN, area, a.city, x, y, razdel, refManager, isNew, orgNote ")
	        ->from("{{%orglist}}")			
	        ->leftJoin('(SELECT  area, city, x, y, ref_org from {{%adreslist}} group by ref_org) as a','{{%orglist}}.id = a.ref_org')
			->where("source LIKE 'google 1c' " );
			

	$countquery  = new Query();
   	$countquery->select (" count({{%orglist}}.id)")
	        ->from("{{%orglist}}")			
	        ->leftJoin('{{%adreslist}}','{{%orglist}}.id = {{%adreslist}}.ref_org')
			 ->where("source LIKE 'google 1c' " );

   	
	if (($this->load($params) && $this->validate())) {
     /*
	 $query->andFilterWhere(['like', 'title', $this->title]);
	 $countquery->andFilterWhere(['like', 'city', $this->city]);
	 */
     }

	$command = $query->createCommand();	
	$count = $countquery->createCommand()->queryScalar();

	
	$dataProvider = new SqlDataProvider([
			'sql' => $command ->sql,
			'params' => $command->params,			
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 8,
			],
			
			'sort' => [
			
			'attributes' => [
			'schetINN',
			'title',
			'have_phone',
			'area',
			'city',
			'isNew',
			 ],
			
			],
			
		]);


		
	return  $dataProvider;	 
   }   

	
  /************End of model*******************/ 
 }
