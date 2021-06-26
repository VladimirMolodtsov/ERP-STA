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
use app\models\SupplierSchetHeaderList;
use app\models\SupplierSchetContentList;
use app\models\SupplierOplataList;
use app\models\SupplierWaresList;
use app\models\User; 
use app\models\MarketSchetForm;
use app\models\PriceList;
use app\models\TblContracts;
use app\models\TblWareHeader;
use app\models\TblWareContent;
use app\models\TblWareUse;

use app\models\OrgList; 
use app\models\AdressList;
use app\models\TblOrgOkved;
use app\models\TblOrgAccounts;

use app\models\TblClientSchetHeader;
use app\models\TblClientSchetContent;


/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class DataSyncGoogle extends Model
{
     
     public $webSync = true;
     
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
/**************************/
   public function getCfgValue($key)          
   {
      $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
               ':key' => intval($key),               
               ])->queryOne();  
               
     return $record['keyValue'];
   }
/**************************/     
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
/**************************************************/     
     public function syncSingleOrg($id)
     {
         $id = intval($id);
         $ret= [
                'id' => $id,
                'client' => 'false', 
                'supplier' => 'false',
                'res' => 'false'
               ];
         
        $record = OrgList::findOne($id);
        if (empty($record)) return $ret;
        /*Search in client base*/
        $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 5')->queryScalar();
        $startRow = 1;
        $page = $this->get_web_page( $url.$startRow );     
        $content = mb_split('\r\n', $page['content'] );          
        $n=count($content);
        $parse = str_getcsv($content[0],",");          
        $tmp = explode("/", $parse[0]);/*на случай фигни*/  
        $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
        for ($i=1; $i<$n;$i++ )
          {
            $r=$this->singleOrgUpdate($record, $content[$i]);
            if ($r) {
                //найдено
             $ret['res']=true;   
             $ret['client']=true;
             return $ret;                 
            }
          }

        /*Search in supplier base*/
        $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 15')->queryScalar();
        $page = $this->get_web_page( $url.$startRow );     
        $content = mb_split('\r\n', $page['content'] );          
        $n=count($content);
        $parse = str_getcsv($content[0],",");          
        $tmp = explode("/", $parse[0]);/*на случай фигни*/  
        $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
        for ($i=1; $i<$n;$i++ )
          {
            $r=$this->singleOrgUpdate($record, $content[$i]);
            if ($r) {
                //найдено
             $ret['res']=true;   
             $ret['supplier']=true;
             return $ret;                 
            }
          }

        return $ret;                 
     }  
     
     public function singleOrgUpdate($record, $parse_string)
     {                
            $parse = str_getcsv($parse_string,",");     
            for($j=0; $j<count($parse); $j++) {$parse[$j]=trim($parse[$j]);}
            for($j=count($parse); $j<15; $j++){$parse[]="";}               
            /*Выставим названия*/
            if(empty($parse[1]) && empty($parse[2]) ) return false;
            if(empty($parse[1])){$parse[1] = $parse[2];     }
            if(empty($parse[2])){$parse[2] = $parse[1];     }
            $orgTitle     = $parse[1];
            $orgFullTitle = mb_substr($parse[2],0,510,'utf-8');
            $orgINN       = $parse[3];
            $orgKPP       = $parse[4];        
            
            if ($record->orgINN != $orgINN )  return false;
            if(!empty($record->orgKPP))
                if ($record->orgKPP != $orgKPP )  return false;

            $record ->title=$orgTitle;   
            $record ->orgFullTitle=$orgFullTitle;
            $record ->orgKPP = $orgKPP;
               
                
             if (!empty($parse[5]) ){  
                $accRecord= TblOrgAccounts::findOne([
                'orgRS' => $parse[5],
                'refOrg' =>  $record ->id,
                ]);
                if (empty($accRecord)) $accRecord = new TblOrgAccounts();
                if (!empty($accRecord))
                {
                 $accRecord->refOrg = $record ->id;   
                 $accRecord->orgRS  = $parse[5];
                 $accRecord->orgBIK = $parse[6];
                 $accRecord->orgKS  = $parse[7];
                 $accRecord->orgBank= $parse[8];
                 $accRecord->save();                    
                }             
             }
              
               /*Юр адрес*/
             if (!empty($parse[11]))
               {
                $adrRecord= AdressList::findOne([
                'adress' => $parse[11],
                'ref_org' =>$record ->id
                ]);
                if (empty($adrRecord)) $adrRecord = new AdressList();
                if (!empty($adrRecord))
                {
                 $adrRecord->ref_org = $record ->id;   
                 $adrRecord->adress  = $parse[11];
                 $adrRecord->save();                    
                }
             }                
             if (!empty($parse[13]))
               {
                $adrRecord= AdressList::findOne([
                'adress' => $parse[13],
                'ref_org' =>$record ->id
                ]);
                if (empty($adrRecord)) $adrRecord = new AdressList();
                if (!empty($adrRecord))
                {
                 $adrRecord->ref_org = $record ->id;   
                 $adrRecord->adress  = $parse[13];
                 $adrRecord->save();                    
                }
             }
             $record ->save();
             return true;                                         
     }
     
     
     public function loadClientBase($updExistedClients, $startRow)
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
          $page = $this->get_web_page( $url.$startRow );     
          $content = mb_split('\r\n', $page['content'] );          
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
               $updatedClients+=$this->parseClientRecord($content[$i], $updExistedClients,0);
               $i+=$j;          
          }
        $ret['allRecords'] = $allRecords;
        $ret['updatedClients'] = $updatedClients;        
//        $ret['lastLoaded'] = $res['numLoad'];
//        $ret['loaded'] = $res;
//        $ret['err'] = $page[err];
          $strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id )";
          Yii::$app->db->createCommand($strSql)->execute();

          return $ret;
     }     
/**************************/
     public function loadSupplierBase($updExistedClients, $startRow)
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
            'SELECT keyValue FROM {{%config}} WHERE id = 15')->queryScalar();
            
//       $ret['url'] =    $url;
          $page = $this->get_web_page( $url.$startRow );     
        
//        $ret['page'] = $page;
        
          $content = mb_split('\r\n', $page['content'] );     
  
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
               $updatedClients+=$this->parseClientRecord($content[$i], $updExistedClients, 1);
               $i+=$j;          
          }
        $ret['allRecords'] = $allRecords;
        $ret['updatedClients'] = $updatedClients;        
        $strSql="UPDATE {{%orglist}} set `have_phone` = (SELECT COUNT({{%phones}}.phone) from {{%phones}} where {{%phones}}.ref_org={{%orglist}}.id )";
        Yii::$app->db->createCommand($strSql)->execute();

        return $ret;
     }     


/**************************/
     public function parseClientRecord($parse_string, $updExistedClients, $isSupplier)
     {
               $clientData= new ClientData();
               mb_internal_encoding("UTF-8");
          
               $parse = str_getcsv($parse_string,",");     
//                    return $parse;
                    
               for($j=0; $j<count($parse); $j++) {$parse[$j]=trim($parse[$j]);}
               for($j=count($parse); $j<15; $j++){$parse[]="";}               

               /*Выставим названия*/
               if(empty($parse[1]) && empty($parse[2]) ) return;
               if(empty($parse[1])){$parse[1] = $parse[2];     }
               if(empty($parse[2])){$parse[2] = $parse[1];     }
               
               $orgArray = $clientData->getEmptyOrgArray();
               
               $orgArray['numLoad']      = $parse[0];
               $orgArray['orgTitle']     = $parse[1];
               $orgArray['orgFullTitle'] = mb_substr($parse[2],0,510,'utf-8');
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
               /*else
               {
                    $userRecord = new User();
                    $userRecord -> userFIO = $managerFIO;
                    $userRecord -> username = $managerFIO;
                    $userRecord -> save();
                    $this->managerRefArray[$managerFIO]=$userRecord ->id;
                    $orgArray['orgManager']  = $this->managerRefArray[$managerFIO]; 
               }*/

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
               
               if ( count ($orgArray['orgPhoneList']) > 0)          $orgArray['contactPhone']=$orgArray['orgPhoneList'][0];
               
               /*Юр адрес*/
               if (!empty($parse[11]))
               {
               $adresParse = str_getcsv($parse[11],",");               
               $lastField = count($adresParse)-1;                                   
                    $orgArray['orgAdress'][0]['adress'] =$parse[11];
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
                    $orgArray['orgAdress'][1]['adress'] =$parse[13];
               if ($lastField>3){$orgArray['orgAdress'][1]['area'] = $adresParse[1];}
                                else  {$orgArray['orgAdress'][1]['area'] ="";}
               if ($lastField>2){$orgArray['orgAdress'][1]['city'] =$adresParse[$lastField-2];}
                              else {$orgArray['orgAdress'][1]['city'] = $adressArray['city'] ="";}
               $orgArray['orgAdress'][1]['index'] =$adresParse[0];
               $orgArray['orgAdress'][1]['X']="";
               $orgArray['orgAdress'][1]['Y']="";
               $orgArray['orgAdress'][1]['district']="";
               $orgArray['orgAdress'][1]['isOfficial'] =0;
               }                
               
               /*Остальное*/
               $orgArray['orgNote']  = $parse[14];
               $orgArray['orgSource']  = "google 1c";
               $orgArray['isFirstContact']  = 1;
               $orgArray['isFirstContactFinished']  = 1;
               $orgArray['isNeedFinished']  = 1;
               $orgArray['isPreparedForSchet']  = 1;
               $orgArray['supplierType']       = $isSupplier;

               //return     $orgArray;     
              $r=$clientData->saveFromArray($orgArray);               
              if ($r == 0)
              {    
               if ($updExistedClients == 1) $r=$clientData->updateFromArray($orgArray);                                   
                  elseif  ($isSupplier > 0) $r=$clientData->setSupplier($orgArray);                                   
               } 
               
               return $r;          
          
     }
    
/**********************/
     
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
//               if ($i ==10) break;
               $parse_string =$content[$i];
               
               /*****************/
               /*Боремся с переводами строки внутри одной записи*/
/*               if($i+1<count($content))
               {
               //Есть следующая строка
                    $parse = str_getcsv($content[$i+1],",");          
                    while ($parse[0] != $next_id)
                    {
                      $i++;     
                      rtrim($parse_string);
                      $parse_string.=$content[$i];
                      
                      if($i+1>=count($content))     {break;}
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
               {               $orgArray['orgManager']  = $managerRefArray[$managerFIO]; }
              /* else
               {
                    $userRecord = new User();
                    $userRecord -> userFIO = $managerFIO;
                    $userRecord -> username = $managerFIO;
                    $userRecord -> save();
                    $managerRefArray[$managerFIO]=$userRecord ->id;
                    $orgArray['orgManager']  = $managerRefArray[$managerFIO]; 
               }*/

               $phoneSrc = $parse[7];               
               /*В комментах есть телефон*/
               if (preg_match("/\*/iu",$parse[11] ))
               {
                    $phoneSrcList = mb_split("\*",$parse[11] );
                    $phoneSrc.=",".$phoneSrcList[0];
               }
               
               $orgArray['orgPhoneList']  = str_getcsv($phoneSrc,",");
               for($j=0;$j<count($orgArray['orgPhoneList']);$j++){$orgArray['orgPhoneList'][$j]=preg_replace("/[\D]/","",$orgArray['orgPhoneList'][$j]);}
               if ( count ($orgArray['orgPhoneList']) > 0)          $orgArray['contactPhone']=$orgArray['orgPhoneList'][0];
               
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

/*****************************************/
/******* апдейт поставщиков **************/

  public function updateSupplierList()
  {

      $url='https://docs.google.com/spreadsheets/d/e/2PACX-1vSQjnchK3xe2Jymm9kqM77DKW5KDWCqcoQc93rsiSPN0-OBr67bRM6sFDKf8__rbGrFMkIyehOdCrnu/pub?output=csv';

      $page = $this->get_web_page( $url );     
      $content = mb_split('\r\n', $page['content'] );
        
      $list = Yii::$app->db->createCommand(
            'SELECT id, article FROM {{%bank_op_article}} order by id')->queryAll();
      $articleRef=array();
      for ($i=0;$i< count($list); $i++)
      {
        $key=$list[$i]['article'];
        $articleRef[$key]=$list[$i]['id'];         
      }
     for ($i=1;$i< count($content); $i++)
     {

        $parse_string =$content[$i];
        $parse = str_getcsv($parse_string,",");     
        
        $orgINN=trim($parse[3]);
        $orgKPP=trim($parse[4]);
        
        if(empty($orgINN)) continue;
        
        $record = OrgList::findOne([
        'orgINN' =>$orgINN,
        'orgKPP' =>$orgKPP
        ]);
        if (empty($record)) continue;
        
        $operation=trim($parse[0]);
        if ( !isset($articleRef[$operation]) ) {
         $artRec= new TblBankOpArticle();
         $artRec->article = $operation;
         $artRec->save();
         $articleRef[$operation]=$artRec->id;
        } 
        
        $record->defSupOperation = $articleRef[$operation];
        $record->supplyDetail=mb_substr(trim($parse[1]),0,250,'utf-8');
        $record->save();
     }
  }
/*****************************************/
/******* апдейт клиентов **************/

  public function updateClientList()
  {

      $url='https://docs.google.com/spreadsheets/d/e/2PACX-1vSQjnchK3xe2Jymm9kqM77DKW5KDWCqcoQc93rsiSPN0-OBr67bRM6sFDKf8__rbGrFMkIyehOdCrnu/pub?gid=967123564&single=true&output=csv';

      $page = $this->get_web_page( $url );     
      $content = mb_split('\r\n', $page['content'] );
        
      $list = Yii::$app->db->createCommand(
            'SELECT id, article FROM {{%bank_op_article}} order by id')->queryAll();
      $articleRef=array();
      for ($i=0;$i< count($list); $i++)
      {
        $key=$list[$i]['article'];
        $articleRef[$key]=$list[$i]['id'];         
      }
     for ($i=1;$i< count($content); $i++)
     {
echo "$i ..";
        $parse_string =$content[$i];
        $parse = str_getcsv($parse_string,",");     
        
        $orgINN=trim($parse[2]);
        $orgKPP=trim($parse[3]);
        
        if(empty($orgINN)) continue;
        
        $record = OrgList::findOne([
        'orgINN' =>$orgINN,
        'orgKPP' =>$orgKPP
        ]);
        if (empty($record)) continue;
        
        $operation=trim($parse[0]);
        if ( !isset($articleRef[$operation]) ) {
         $artRec= new TblBankOpArticle();
         $artRec->article = $operation;
         $artRec->save();
         $articleRef[$operation]=$artRec->id;
        } 
        
        
        $addrRecord=AdressList::findOne([
          'ref_org' => $record->id,
          'isOfficial' => 1
        ]);
        if (empty($addrRecord))
            $addrRecord=new AdressList();
        if (!empty($addrRecord))
        {
        $addrRecord->area=mb_substr(trim($parse[14]),0,150,'utf-8');//` VARCHAR(150) COLLATE utf8_general_ci DEFAULT NULL,
        
        $addrRecord->district=mb_substr(trim($parse[15]),0,150,'utf-8');//` VARCHAR(150) COLLATE utf8_general_ci DEFAULT NULL,
        $addrRecord->adress=mb_substr(trim($parse[6]),0,250,'utf-8');//` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
        $addrRecord->ref_org=$record->id;
        $addrRecord->isBad=0;
        $addrRecord->index=mb_substr(trim($parse[13]),0,20,'utf-8');
        $addrRecord->isOfficial=1;
        
        $addrRecord->city=mb_substr(trim($parse[16]),0,250,'utf-8');//` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
        $addrRecord->street=mb_substr(trim($parse[17]),0,75,'utf-8');
        $addrRecord->house=mb_substr(trim($parse[18]),0,20,'utf-8');
        $addrRecord->room=mb_substr(trim($parse[19]),0,75,'utf-8');
        $addrRecord->save();
        }


        $okvedRecord=TblOrgOkved::findOne([
          'refOrg' => $record->id,
          'isDefault' => 1
        ]);
        if (empty($okvedRecord))
            $okvedRecord=new TblOrgOkved    ();
        if (!empty($okvedRecord))
        {
        $okvedRecord->refOrg=$record->id;
        $okvedRecord->isDefault=1;
        $okvedRecord->OKVED=mb_substr(trim($parse[7]),0,250,'utf-8');
        $okvedRecord->save();
        }
        
        
        
        $record->razdel=mb_substr(trim($parse[8]),0,150,'utf-8');
        $record->subrazdel=mb_substr(trim($parse[10]),0,75,'utf-8');
        $record->nadrazdel=mb_substr(trim($parse[9]),0,75,'utf-8');
        $record->checkUrl=mb_substr(trim($parse[5]),0,250,'utf-8');
        $record->defSupOperation = $articleRef[$operation];
        $record->supplyDetail=mb_substr(trim($parse[1]),0,250,'utf-8');
        $record->save();
echo "saved\n";        
     }
  }

/*****************************************************************************/     
//   Синхронизация счетов с 1С
//     public $createNewOrg = 0;  добавлять организации
//     public $syncAllUser = 0;   Синхронизировать для всех пользователей
//     public $createNewSchet = 0;  // Создавать счет
//     public $updateExistedSchet = 0;  // апдейтить существующий

/*****************************************************************************/     

     public function loadSchetActivity($startRow, $allRecords)
     {
           $session = Yii::$app->session;          
          $session->open();
          mb_internal_encoding("UTF-8");          
          
        
        $period=$this->getCfgValue(2001);
        
          $fromTime = time() - 60*60*24*$period; // период синхронизации
          $fromDate = date ("Y-m-d", $fromTime); /*За последний квартал*/
          
          /*Load data*/          
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 6')->queryScalar();
          $loadurl =  $url.$startRow."&sd=".date("dmY",$fromTime)."&ed=".date("dmY",time()+24*60*60); 
        
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
//                $parse = str_getcsv($content[$i],",");          
//                $allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
               
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
               $lastLoaded     =$parse[0];
               $loadCounter++;
               $schetTime= strtotime(mb_substr($parse[4],1));
               if ($schetTime < $fromTime)     {continue;}          
               $orgINN      = trim($parse[2]);
              if (!array_key_exists ($orgINN, $orgList)){$orgList[$orgINN] = $schetTime;}
               else 
               {     
                    if($orgList[$orgINN] < $schetTime     ){$orgList[$orgINN] = $schetTime;}
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
               
     $statusArray['oplataSum'] = Yii::$app->db->createCommand( 'SELECT ifnull(sum(oplateSumm),0) from {{%oplata}} where refSchet=:refSchet', 
          [':refSchet' => $schetId])->queryScalar();                     
     
     if ($statusArray['oplataSum'] >= $schetRec->schetSumm) $statusArray['oplateSync'] = 1;
   
     $schetRec->summOplata = $statusArray['oplataSum'];
     
     $statusArray['supplySum'] = Yii::$app->db->createCommand( 'SELECT ifnull(sum(supplySumm),0) from {{%supply}} where refSchet=:refSchet', 
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
     return      $retArray;
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
                      "SELECT id, prefix,  orgTitle, {{%schet_prefix}}.isActive FROM {{%schet_prefix}} where {{%schet_prefix}}.isActive > 0 order by id")->queryAll();                    
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
          $loadurl =  $url."1&sd=".date("dmY",$fromTime)."&ed=".date("dmY",$toTime+24*60*60);
          $page = $this->get_web_page($loadurl );     
          if ($page['errno'] >0)  return $schetList;
     
          $content = mb_split('\r\n', $page['content'] );          

          /*Первый блок данных*/
               $parse = str_getcsv($content[0],",");          
               
               if (count($parse) < 10 || $parse[1]!= 'Контрагент')  return $schetList; /*Не оно*/          
               
            $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

//                $allRecords=intval(preg_replace("/[\D]/","",$parse[0]));
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
               $lastLoaded     =$parse[0];
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
       return     $schetRecord->id;          
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
         
        $curUserId=0; 
        if ($this->webSync)
        { 
          $session = Yii::$app->session;          
          $session->open();
          $curUser=Yii::$app->user->identity;
          $curUserId=$curUser->id;
        }  
          $res=array();     
          
          $updatedSchet=0;
          
          $clientData= new ClientData();
          mb_internal_encoding("UTF-8");          
          
          /*получим список префиксов счетов в 1C */

          $list = Yii::$app->db->createCommand(
                      "SELECT id, prefix,  orgTitle, isActive FROM {{%schet_prefix}} where isActive > 0 order by id")->queryAll();
                         
         $schetPrefixArray=array();
          for($i=0; $i < count ($list); $i++){
               $key = $list[$i]['prefix'];
               $schetPrefixArray[$key]=$list[$i]['orgTitle'];               
          }
          $ret['schetPrefixArray'] =$schetPrefixArray;          

          /*получим список уже существующих счетов*/
       $period=$this->getCfgValue(2001);

       if ( empty($this->syncDate) )          
        {
          $fromTime = time() - 10*60*24*$period;  /*За последний квартал*/ 
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
                    
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 6')->queryScalar();
          
          /*Load data*/          
          //$loadurl =  $url.$startRow;
          $loadurl =  $url.$startRow."&sd=".date("dmY",$fromTime)."&ed=".date("dmY",$toTime);
          $page = $this->get_web_page($loadurl );     
          
          $content = mb_split('\r\n', $page['content'] );

          $err=array();     
          $lastLoaded=0;          
          $loadCounter=0;
          $i=0;
          $schetRef1C = "";
          $ig=0;

               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;
          
          for ($i=0;$i< count($content); $i++)
          {
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                         

               if (count($parse) < 12) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                       
               
               $lastLoaded     =$parse[0];
               $loadCounter++;
               if ($loadCounter > 2500){break;}

                                          
               $orgINN = mb_substr(trim($parse[2]),0,20,'utf-8');
               $orgKPP = mb_substr(trim($parse[10]),0,20,'utf-8');      
               $schetDate = str_replace("'", "",$parse[4]);
            /* считанная запись имеет другой 1C референс - пора сохранять заголовок*/                    
            if ($schetRef1C!=mb_substr(trim($parse[3]),0,20,'utf-8'))               
            {
                $schetRef1C=mb_substr(trim($parse[3]),0,20,'utf-8');
                $headerRecord=TblClientSchetHeader::findOne([
                'orgINN' => $orgINN,
                'orgKPP' => $orgKPP,
                'schetRef1C'=>$schetRef1C,
                ]);
                if (empty($headerRecord)){
                    $headerRecord = new TblClientSchetHeader();
                    if (empty($headerRecord)) break;
                    $headerRecord->orgINN = $orgINN;
                    $headerRecord->orgKPP = $orgKPP;
                    $headerRecord->orgTitle = mb_substr(trim($parse[1]),0,250,'utf-8');
                    $headerRecord->schetRef1C = $schetRef1C;
                    $headerRecord->schetDate  = date("Y-m-d", strtotime($schetDate)); 
                    $headerRecord->save();
                }else{                    
                  $strSql="DELETE FROM  {{%client_schet_content}} where refHeader=:refHeader";
                    Yii::$app->db->createCommand($strSql,[':refHeader' => $headerRecord->id])->execute();                 
                }
             } /*Save header to base*/  
                $record = new TblClientSchetContent();
                $wareCount =  (float)str_replace(',', '.',$parse[7]);
                $wareSum   =  (float)str_replace(',', '.',$parse[9]);
                if (empty($record)) break;
                $record ->refHeader = $headerRecord->id;
                $record ->wareTitle= mb_substr(trim($parse[6]),0,250,'utf-8');
                $record ->wareCount= $wareCount;
                $record ->wareEd = mb_substr(trim($parse[8]),0,20,'utf-8');
                $record ->wareSum = $wareSum;
                $record ->wareArticul = mb_substr(trim($parse[11]),0,20,'utf-8');
                $record ->save();
          }
           /*************************************************************/
           /*************************************************************/               
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedSchet'] = $updatedSchet;



       $strSql="update {{%client_schet_header}},{{%orglist}}  
       set {{%client_schet_header}}.refOrg = {{%orglist}}.id
       where  {{%client_schet_header}}.refOrg = 0
       and {{%client_schet_header}}.orgINN = {{%orglist}}.orgINN  
       AND {{%client_schet_header}}.orgKPP = {{%orglist}}.orgKPP
       AND ifnull({{%orglist}}.orgINN,'') != ''";
       Yii::$app->db->createCommand($strSql)->execute();

       $strSql="update {{%client_schet_header}},{{%orglist}}  
       set {{%client_schet_header}}.refOrg = {{%orglist}}.id
       where  {{%client_schet_header}}.refOrg = 0
       and {{%client_schet_header}}.orgINN = {{%orglist}}.orgINN  
       AND ifnull({{%orglist}}.orgINN,'') != ''";
       Yii::$app->db->createCommand($strSql)->execute();

       $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 106";
       Yii::$app->db->createCommand($strSql)->execute();

       return $ret;
     }
     
     
   public function saveSchetRecord($schetRecord,$schetPrefixArray, $schetRefArray)
   {
                                                                                          // пропустим 
       if(!array_key_exists ('schetNum', $schetRecord)) return false;      //не имеющих номер счета
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
         /*Obsoleted*/     
return;              
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

                /*апдейтим связь со складом*/
                    $strSql="update {{%zakazContent}}, {{%warehouse}} set {{%zakazContent}}.warehouseRef = {{%warehouse}}.id
                where {{%zakazContent}}.good = {{%warehouse}}.title and {{%zakazContent}}.warehouseRef = 0 AND {{%zakazContent}}.isActive =1 AND refZakaz = ".$uid;
                    Yii::$app->db->createCommand($strSql)->execute();
                
                
            }  
                    
                    /*Обновим*/
                 //   $schetRec->schetNum = $schetRecord['schetNum'];
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
                /*апдейтим связь со складом*/
                    $strSql="update {{%zakazContent}}, {{%warehouse}} set {{%zakazContent}}.warehouseRef = {{%warehouse}}.id
                where {{%zakazContent}}.good = {{%warehouse}}.title and {{%zakazContent}}.warehouseRef = 0 AND {{%zakazContent}}.isActive =1 AND refZakaz = ".$uid;
                    Yii::$app->db->createCommand($strSql)->execute(); 
                    
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
/***************************************************/             
/*********** Оплаты  *******************************/         
/***************************************************/         

/* Попытка автоматически связать */    
public function linkOplataToSchet()
{
     
    
        /* Скинем ошибочно привязанных*/
        $strSql="update {{%oplata}}, {{%schet}} set {{%oplata}}.refSchet = 0 where {{%oplata}}.refSchet = {{%schet}}.id and {{%schet}}.[[isReject]] = 1";
        Yii::$app->db->createCommand($strSql)     ->execute();     
    
    
          $curUser=Yii::$app->user->identity;     
         $strSql=" update {{%oplata}} as a, {{%schet}} as b set a.refOrg = b.refOrg, a.refSchet = b.id, a.refZakaz = b.refZakaz
          where a.schetRef1C = b.ref1C and a.schetDate = b.schetDate and ( a.refSchet IS NULL or a.refSchet =0) and b.[[isReject]] = 0";     
          //Только свои если не задано для всех
          if ( $this->syncAllUser == 0) $strSql.=" and b.refManager = ".$curUser->id;          
          if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                                  else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
 
          Yii::$app->db->createCommand($strSql)     ->execute();     
          
          
          /*по сочетанию инн и кпп*/
          $strSql=" update {{%oplata}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0";     
          
          Yii::$app->db->createCommand($strSql)     ->execute();     
          
          /*где кпп не задан*/
          $strSql=" update {{%oplata}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0";     
          
          Yii::$app->db->createCommand($strSql)     ->execute();     
 

        /* Сохраним сумму в счете - для упрощения запроса*/ 
          $strSql="update {{%schet}} as a set a.summOplata = ifnull((SELECT SUM(oplateSumm) from {{%oplata}} where refSchet = a.id),0)
          where a.isSchetActive = 1 ";     
          if ( $this->syncAllUser == 0) $strSql.=" and a.refManager = ".$curUser->id;          
          if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                                  else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
        Yii::$app->db->createCommand($strSql)     ->execute();          


        /* пометим активные счета как оплаченные  */ 
          $strSql="UPDATE {{%schet}} set isOplata = 1 where   isOplata = 0 AND ifnull(schetSumm,0) <= ifnull(summOplata,0) AND isSchetActive = 1 ";     
          if ( $this->syncAllUser == 0) $strSql.=" and refManager = ".$curUser->id;          
          if (!empty ($this->syncDate))  $strSql.=" and schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                                  else   $strSql.=" and schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
        Yii::$app->db->createCommand($strSql)     ->execute();          
          
}     
/********************/     
/* Подгрузим из 1C в базу */
     public function loadOplataBase($startRow, $allRecords)
     {
          mb_internal_encoding("UTF-8");          
          
          $res=array();     
     
        if($this->webSync){   
          $session = Yii::$app->session;          
          $session->open();
        } 
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
          $period=$this->getCfgValue(2001);           
          $lastOplata = Yii::$app->db->createCommand(
                      "SELECT ifnull(max(oplateDate),'2010-01-01') FROM {{%oplata}}")->queryScalar();                    
          $lastOplataTime = strtotime ($lastOplata) - 60*60*24*$period; // период синхронизации

     
        /*Получим список уже занесенных сегодня оплат */          
          $list = Yii::$app->db->createCommand(
                      "SELECT id, oplateDate, ref1C, orgINN, oplateSumm FROM {{%oplata}} where oplateDate >= :oplateDate order by id ")
                         ->bindValue(':oplateDate',date('Y-m-d', $lastOplataTime ))
                         ->queryAll();                              

                         
          $oplataRefArray = array();               
          for($i=0; $i < count ($list); $i++)
          {
               // Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)          
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
                                                  else  $orgINN = "-";
               $key = $list[$i]['ref1C']."_".$list[$i]['oplateDate']."_".$orgINN."_".$list[$i]['oplateSumm'];
            
            if(array_key_exists ($key, $oplataRefArray)) 
            {
                $record = OplataList::findOne($list[$i]['id']);
                if (empty ($record)) continue;
                $record->delete();
            }
            else $oplataRefArray[$key]=$list[$i]['id'];                              
          }
        
           if($this->webSync){        
             $session->set('oplataRefArray', $oplataRefArray);
             $session->set('lastOplataTime', $lastOplataTime);
           }
          }
          else 
          {
            if($this->webSync){     
               $oplataRefArray = $session->get('oplataRefArray');
               $lastOplataTime = $session->get('lastOplataTime');
            }    
               if (empty ($lastOplataTime)) return false;
               if (empty ($oplataRefArray)) $oplataRefArray = array();
          }                         
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 7')->queryScalar();
          //$loadurl =  $url.$startRow;
          $loadurl =  $url.$startRow."&sd=".date("dmY",$lastOplataTime)."&ed=".date("dmY",time()+24*60*60);
          
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
          $isUpdated = 0;
          for ($i;$i< count($content); $i++)
          {                         
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                         
               $lastLoaded     =$parse[0];

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
               if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$schetRef1C);     }
               else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );     }               

               $schetTime= strtotime(mb_substr($parse[7],1));
               $schetDate=date("Y-m-d", $schetTime);
               
               
               /*Определяем номер платежки*/               
               $oplatePos =  mb_strstr( $ref1C,"0");                    
               if($oplatePos == false)   {$oplateNum = preg_replace("/[\D]/u","",$ref1C);     }
               else                      {$oplateNum = preg_replace("/^0+/u","", $oplatePos );     }               
               
               $schetPrefix=preg_replace("/$oplateNum/u","", $ref1C);
               $schetPrefix = preg_replace("/0+$/u","", $schetPrefix );

               if(!array_key_exists ($schetPrefix, $schetPrefixArray)) continue; //с неправильным префиксом
               
               
               $oplataTime= strtotime(mb_substr($parse[4],1));
               $oplateDate=date("Y-m-d", $oplataTime);
               
               if (!empty ($parse[2])) $orgINN = trim($parse[2]);
                                     else  $orgINN = "-";
                                     
               if (!empty ($parse[10])) $orgKPP = trim($parse[10]);
                                     else  $orgKPP = "-";
                                     
                                     
               $oplateSumm =  (float)str_replace(',', '.',$parse[9]);
               
               $key = $ref1C."_".$oplateDate."_".$orgINN."_".$oplateSumm;               
               if(array_key_exists ($key, $oplataRefArray)) 
            {
                   $oplataRecord =  OplataList::findOne($oplataRefArray[$key]);     
                
                if ($oplataRecord->orgTitle   != trim($parse[1])) {$oplataRecord->orgTitle   = trim($parse[1]); $isUpdated = 1;}
                if ($oplataRecord->orgKPP     != $orgKPP        ) {$oplataRecord->orgKPP     = $orgKPP; $isUpdated = 1;}
                if ($oplataRecord->schetNum   != $schetNum      ) {$oplataRecord->schetNum   = $schetNum; $isUpdated = 1;}
                if ($oplataRecord->schetDate  != $schetDate     ) {$oplataRecord->schetDate  = $schetDate; $isUpdated = 1;}                
                if ( $isUpdated == 1)
                {
                    $oplataRecord->save();
                    $updatedOplata++;
                }
                $oplataRefArray[$key]=0;
                continue;  //уже есть
            }
               //$scanKey[]=$key;

               $oplataRecord = new OplataList();     
               $oplataRecord->ref1C =$ref1C; 
               $oplataRecord->oplateDate = $oplateDate;
               $oplataRecord->oplateSumm = $oplateSumm;
               $oplataRecord->oplateNum = $oplateNum;
               $oplataRecord->schetRef1C = $schetRef1C;
               $oplataRecord->orgTitle   = trim($parse[1]);
               $oplataRecord->orgINN     = $orgINN;
               $oplataRecord->orgKPP     = $orgKPP;
               $oplataRecord->schetNum   = $schetNum;
               $oplataRecord->schetDate  = $schetDate;
               $oplataRecord->save();
               $updatedOplata++;
               
          }//
               
        if($lastLoaded >= $allRecords && $updatedOplata > 0)
        {
          //Сосканировали все - удаляем отсутствующие           
          foreach ($oplataRefArray as $key => $value) {
            if ($value > 0  )
            {
                $record = OplataList::findOne($value);
                if (empty ($record)) continue;
                $record->delete();
            }
          }              
        }

       $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 107";
       Yii::$app->db->createCommand($strSql)->execute();
            
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedOplata'] = $updatedOplata;
        $ret['err'] = $err;
        //$ret['scanKey'] =$scanKey;
        return $ret;
     }

/***************************************************/             
/*********** Поставки  *******************************/         
/***************************************************/         
     
/********************/     
/********************/     
public function fixSupplyToBase()
     {
          mb_internal_encoding("UTF-8");          
          $session = Yii::$app->session;          
          $session->open();
          $schetRefArray=$session->get('schetRefArray');               
          $schetSumArray=$session->get('schetSumArray');               

          foreach ($schetRefArray as $key => $id) 
          {          
               
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
    
    /* Скинем ошибочно привязанных*/
    $strSql="update {{%supply}}, {{%schet}} set {{%supply}}.refSchet = 0 where {{%supply}}.refSchet = {{%schet}}.id and {{%schet}}.[[isReject]] = 1";
    Yii::$app->db->createCommand($strSql)     ->execute();     
    
     $curUser=Yii::$app->user->identity;

    $strSql=" update {{%supply}} as a, {{%schet}} as b set a.refOrg = b.refOrg, a.refSchet = b.id, a.refZakaz = b.refZakaz
    where a.schetRef1C = b.ref1C and a.schetDate = b.schetDate and ( a.refSchet IS NULL or a.refSchet =0) and b.[[isReject]] = 0";
     
     if ( $this->syncAllUser == 0) $strSql.=" and b.refManager = ".$curUser->id;
     if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                              else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";                                   
    Yii::$app->db->createCommand($strSql)     ->execute();          
     
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%supply}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL) and b.[[isReject]] = 0";     
          
     Yii::$app->db->createCommand($strSql)     ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%supply}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0";     
          
     Yii::$app->db->createCommand($strSql)     ->execute();     
 
     
     $strSql="update {{%schet}} as a set a.summSupply =  ifnull((SELECT SUM(supplySumm) from {{%supply}} where refSchet = a.`id`),0)
     where a.isSchetActive = 1 ";     
     if ( $this->syncAllUser == 0)  $strSql.=" and a.refManager = ".$curUser->id;          
     if (!empty ($this->syncDate))  $strSql.=" and a.schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                             else   $strSql.=" and a.schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
      Yii::$app->db->createCommand($strSql)     ->execute();          

     $strSql="UPDATE {{%schet}} set isSupply = 1 where   isSupply = 0 AND ifnull(schetSumm,0) <= ifnull(summSupply,0) AND isSchetActive = 1 ";     
     if ( $this->syncAllUser == 0) $strSql.=" and refManager = ".$curUser->id;          
     if (!empty ($this->syncDate))  $strSql.=" and schetDate >= '".date("Y-m-d",strtotime($this->syncDate)-10*60*60*24)."'";
                             else   $strSql.=" and schetDate >= '".date("Y-m-d",time()-90*60*60*24)."'";
     Yii::$app->db->createCommand($strSql)     ->execute();          

     
}
/******************/     
public function loadSupplyBase($startRow, $allRecords)
     {
          mb_internal_encoding("UTF-8");          
          
          $res=array();     
        
        if ($this->webSync){
         $session = Yii::$app->session;          
         $session->open();
        }
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
          /*Последняя поставка*/
        $period=$this->getCfgValue(2001);

        
          $lastSupply = Yii::$app->db->createCommand(
                      "SELECT ifnull(max(supplyDate),'2010-01-01') FROM {{%supply}}")->queryScalar();                    
          $lastSupplyTime = strtotime ($lastSupply) - 60*60*24*$period;

        if ( empty($this->syncDate) )          
        {
          $fromTime = $lastSupplyTime;  /*За последний квартал*/ 
          $toTime = time() +24*3600; // до сегодня
        }
        else
        {
          $fromTime = strtotime ($this->syncDate) - 60*60*24;  /*в диапазоне суток*/
          $toTime = $fromTime + 1*60*60*24; /*в диапазоне суток*/
        }          
          $fromDate = date ("Y-m-d", $fromTime); 
          $toDate = date ("Y-m-d", $toTime); 

     
        /*Получим список уже занесенных поставок */          
          $list = Yii::$app->db->createCommand(
                      "SELECT id, supplyDate, ref1C, orgINN, supplyGood, supplySumm FROM {{%supply}} where 
                      supplyDate >= :fromDate 
                      AND supplyDate <= :toDate 
                      order by id")
                         ->bindValue(':fromDate',$fromDate)
                         ->bindValue(':toDate',$toDate)
                         ->queryAll();                              

          $supplyRefArray = array();               
          for($i=0; $i < count ($list); $i++)
          {
               // Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)          
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
                                                  else  $orgINN = "-";
               $key = $list[$i]['ref1C']."_".md5($list[$i]['supplyDate']."_".$orgINN."_".$list[$i]['supplySumm']."_".$list[$i]['supplyGood']);

            if(array_key_exists ($key, $supplyRefArray)) 
            {
                $record = SupplyList::findOne($list[$i]['id']);
                if (empty ($record)) continue;
                $record->delete();
            }
            else $supplyRefArray[$key]=$list[$i]['id'];                              
          }

          if ($this->webSync) {
            $session->set('supplyRefArray', $supplyRefArray);
            $session->set('lastSupplyTime', $lastSupplyTime);
          }
          }
          else 
          {
            if ($this->webSync){  
               $supplyRefArray = $session->get('supplyRefArray');
               $lastSupplyTime = $session->get('lastSupplyTime');
            }   
               if (empty ($lastSupplyTime)) return false;
               if (empty ($supplyRefArray)) $supplyRefArray = array();
          }      
          
          
                   
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 8')->queryScalar();
          //$loadurl =  $url.$startRow;
          $loadurl =  $url.$startRow."&sd=".date("dmY",$fromTime)."&ed=".date("dmY",$toTime);
          
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
               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));

               $i=1;
          }                    
          
          $scanKey=array();
          $isUpdated = 0;            
          for ($i;$i< count($content); $i++)
          {                         
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                         
               $lastLoaded     =$parse[0];
        $ret['content'][] = $parse;
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
               if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$schetRef1C);     }
               else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );     }               

               $schetTime= strtotime(mb_substr($parse[7],1));
                $schetDate=date("Y-m-d", $schetTime);
               
               
               /*Определяем номер */               
               $supplyPos =  mb_strstr( $ref1C,"0");                    
               if($supplyPos == false)   {$supplyNum = preg_replace("/[\D]/u","",$ref1C);     }
               else                      {$supplyNum = preg_replace("/^0+/u","", $supplyPos );     }               
               
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
               $supplyGood =  trim($parse[9]);
             
            //$key = $list[$i]['ref1C']."_".md5($list[$i]['supplyDate']."_".$orgINN."_".$list[$i]['supplySumm']."_".$list[$i]['supplyGood']);               
               $key = $ref1C."_".md5($supplyDate."_".$orgINN."_".$supplySumm."_".$supplyGood);     
               $isUpdated = 0;            

            if(array_key_exists ($key, $supplyRefArray))
            {
                //уже есть
                $supplyRecord = SupplyList::findOne($supplyRefArray[$key]);     
                if (empty($supplyRecord)) continue;
                
                if ($supplyRecord->schetNum   != $schetNum)      {$supplyRecord->schetNum   = $schetNum;         $isUpdated = 1;}
                if ($supplyRecord->orgKPP     != $orgKPP)        {$supplyRecord->orgKPP     = $orgKPP;           $isUpdated = 1;}
                if ($supplyRecord->orgTitle   != trim($parse[1])){$supplyRecord->orgTitle   = trim($parse[1]);     $isUpdated = 1;}
                if ($supplyRecord->supplyDate != $supplyDate)    {$supplyRecord->supplyDate = $supplyDate;       $isUpdated = 1;}
                if ($supplyRecord->supplyEd     != trim($parse[11])) {$supplyRecord->supplyEd     = trim($parse[11]); $isUpdated = 1;}          
                if ($supplyRecord->supplyNum != $supplyNum)      {$supplyRecord->supplyNum = $supplyNum;         $isUpdated = 1;}
                
                $supplyRefArray[$key]=0;
                if ($isUpdated == 1)
                {
               
                    $supplyRecord->supplyCount = $supplyCount; 
                    $supplyRecord->save();
                    $updatedSupply++;
                }      
                continue;
            }
               //$scanKey[]=$key;
               
               $supplyRecord = new SupplyList();     
               $supplyRecord->schetNum   = $schetNum;
               $supplyRecord->schetDate  = $schetDate;
               $supplyRecord->schetRef1C = $schetRef1C;
               $supplyRecord->orgINN     = $orgINN;
               $supplyRecord->orgKPP     = $orgKPP;
               $supplyRecord->orgTitle   = trim($parse[1]);          
               $supplyRecord->ref1C =$ref1C; 
               $supplyRecord->supplyDate = $supplyDate;
               $supplyRecord->supplySumm = $supplySumm;
               $supplyRecord->supplyGood = $supplyGood;
               $supplyRecord->supplyCount = $supplyCount;
               $supplyRecord->supplyEd     = trim($parse[11]);          
               $supplyRecord->supplyNum = $supplyNum;

               $supplyRecord->save();
               $updatedSupply++;

          }//
                                 
        if($lastLoaded >= $allRecords && $updatedSupply > 0)
        {
          //Сосканировали все - удаляем отсутствующие           
          foreach ($supplyRefArray as $key => $value) {
            if ($value > 0 )
            {
 $ret['del'][]=$key;
                $record = SupplyList::findOne($value);
                if (empty ($record)) continue;
                $record->delete();
            }
          }              
        }
              
       $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 108";             
       Yii::$app->db->createCommand($strSql)->execute();
        
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedSupply'] = $updatedSupply;
        $ret['err'] = $err;
      
        /*привяжемся к складу все что с последней синхронизации*/    
      $strSql = "update {{%supply}}, {{%warehouse}} set {{%supply}}.wareRef = {{%warehouse}}.id 
      where {{%supply}}.supplyGood = {{%warehouse}}.title and {{%supply}}.wareRef = 0 AND supplyDate >= :supplyDate"; 
      Yii::$app->db->createCommand($strSql)
          ->bindValue(':supplyDate',date("Y-m-d",$lastSupplyTime))
       ->execute();  /* Problem Vv : лучше бы по id */
       
       /*привяжемся к списку исходящему все что с последней синхронизации*/
      $strSql = "update {{%supply}} as a , {{%ware_names}} as b
        set a.wareNameRef = b.id    where a.supplyGood = b.wareTitle and a.wareNameRef = 0
        AND supplyDate >= :supplyDate";
      Yii::$app->db->createCommand($strSql)
          ->bindValue(':supplyDate',date("Y-m-d",$lastSupplyTime))
       ->execute();


        return $ret;
     }
     
/********************/     

public function fixSupplyBase($startRow, $allRecords)
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
          
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 8')->queryScalar();
          //$loadurl =  $url.$startRow;
          $loadurl =  $url.$startRow/*."&sd=".date("dmY",$lastSupplyTime)."&ed=".date("dmY",time()+24*3600)*/;
                    
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
               $lastLoaded     =$parse[0];

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
               if($schetPos == false)   {$schetNum = preg_replace("/[\D]/u","",$schetRef1C);     }
               else                     {$schetNum = preg_replace("/^0+/u","", $schetPos );     }               

               $schetTime= strtotime(mb_substr($parse[7],1));
                $schetDate=date("Y-m-d", $schetTime);
               
               $supplyPos =  mb_strstr( $ref1C,"0");                    
               if($supplyPos == false)   {$supplyNum = preg_replace("/[\D]/u","",$ref1C);     }
               else                      {$supplyNum = preg_replace("/^0+/u","", $supplyPos );     }               
               
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
               
               
               $supplyRecord =SupplyList::findOne([
            'supplyDate' => $supplyDate,
            'orgINN' => $orgINN,
            'ref1C' => $ref1C,
            'supplySumm' => $supplySumm,
            ]);
            
            if (empty($supplyRecord)) continue;
               
            $supplyRecord->orgKPP     = $orgKPP;
               $supplyRecord->supplyGood =  trim($parse[9]);
               $supplyRecord->supplyCount = $supplyCount;
               $supplyRecord->supplyEd     = trim($parse[11]);          
               $supplyRecord->save();
               $updatedSupply++;

          }//
                    
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedSupply'] = $updatedSupply;
        $ret['err'] = $err;
        //$ret['scanKey'] =$scanKey;
      
        /*привяжемся к складу все что с последней синхронизации*/    
      $strSql = "update {{%supply}}, {{%warehouse}} set {{%supply}}.wareRef = {{%warehouse}}.id 
      where {{%supply}}.supplyGood = {{%warehouse}}.title and {{%supply}}.wareRef = 0 "; 
      Yii::$app->db->createCommand($strSql)         
       ->execute();         
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
/********************************/
     public function syncSclad1C()
     {
          mb_internal_encoding("UTF-8");
          $res=array();
/*
Получим текущий склад
*/          
     
          $scladArray=array();
        $usedArray=array();
          
           $list = Yii::$app->db->createCommand(
                      "SELECT id, title,  articul FROM {{%warehouse}}  order by id")
                         ->queryAll();
                    
               for($i=0; $i < count ($list); $i++)
               {
               if (empty($list[$i]['title'])){continue;}
                    $key = trim($list[$i]['title']);
                    $scladArray[$key]=$list[$i]['id'];                              
                $usedArray[$key]=0;
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
          
//Yii::$app->db->createCommand("UPDATE `rik_config` SET keyValue = 'Склад Машкомплект РУТЕНБЕРГ' WHERE `id` = 12")->execute();
          
          $val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 12')->queryScalar();
          $scladList = str_getcsv($val,",");          
//$res[]=$scladList;                         

          /*список складов для товара в пути через ','*/
          $val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 13')->queryScalar();
          $transitList = str_getcsv($val,",");          
//$res[]=$transitList;                         
          
        $page = $this->get_web_page( $url);               
          $content = mb_split('\r\n', $page['content'] );          

//$res[]=$page;                                   
//$res[]=$content;                                             
          
          $parse = str_getcsv($content[0],",");     
          $rowNum=$parse[0];
//$res[]=$rowNum;                    
          $n=count($content);
          
        /* Сбросим все в пути и на складе*/        
        Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET amount=0, inTransit = 0')->execute(); 
        
          for ($i=1; $i<$n;$i++ )
          {
               $parse = str_getcsv($content[$i],",");     
               if (count($parse) < 8) {continue;} /*не полная запись*/
               
               //
  
            
               if ( !in_array($parse[1], $orgList)   ) {continue;} /*не наша организация*/
            
            $flg =0;
               if (in_array($parse[2], $scladList)) $flg = 1;
            else
                if (in_array($parse[2], $transitList)) $flg = 2;
                           
//            if ( !in_array($parse[2], $scladList) ) {continue;} /*не наш склад*/            

//$res[]=[$parse[2] => $flg];                        
            if ( $flg ==0 ) {continue;} /*не наш склад*/


            
              /*наименование*/
               $key = trim($parse[5]);
               
               /*К-во*/
               $parse[7] = preg_replace("/[^(0-9.,\-)]/","",$parse[7]);
            $amount=(float)str_replace(',', '.',$parse[7]);            
               /*Цена*/
               $parse[8] = preg_replace("/[^(0-9.,)]/","",$parse[8]);
            $price=(float)str_replace(',', '.',$parse[8]);             
               /*Себестоимость*/
               $parse[9] = preg_replace("/[^(0-9.,)]/","",$parse[9]);
               $initPrice=(float)str_replace(',', '.',$parse[9]); 
            
               if (!array_key_exists ($key, $scladArray) )
               {
//$parse[10]='add';                    
//$res[]=$parse;          
                    /*Создадим товар*/               
                    $scladRecord=     new ScladList();
                    $scladRecord->grpGood=trim($parse[4]);
                    $scladRecord->title = trim($parse[5]);
                    $scladRecord->ed =trim($parse[6]);
                if ($flg ==1 )   $scladRecord->amount = $amount; //Уже на складе
                if ($flg ==2 )   $scladRecord->inTransit = $amount; //Едет
                    $scladRecord->price  = $price; 
//если цена реализации не известна                
                if (empty($scladRecord->relizePrice)) 
//                      $scladRecord->relizePrice  = round($price*$this->priceKF, 2); 
//фигня тут               $scladRecord->initPrice =(float)str_replace(',', '.',$parse[9]); 
                
                    $scladRecord->isValid = 1;
                    $scladRecord->save();
                    $usedArray[$scladRecord->title]=1;
               }
               else
               {                     
                 /*Обновим товар*/
                 $scladRecord= ScladList::findOne($scladArray[$key]);
                 if (empty($scladRecord)) {continue;}
                if ($flg == 2)
               {
                   /*Только количество в пути*/
                    $scladRecord->inTransit += $amount; // схлапываем товар
                    $scladRecord->isValid = 1;
                    $scladRecord->save();
                    $usedArray[$key] =1;//использован                    
                    continue;
                }
    
                    if ( ($scladRecord->amount != $amount) || ($scladRecord->relizePrice != round($price*$this->priceKF, 2)) || ($scladRecord->price != $price) || $scladRecord->isValid == 0)
                    {
                
                    $scladRecord->grpGood=trim($parse[4]);
                    $scladRecord->title = trim($parse[5]);
                    $scladRecord->ed =trim($parse[6]);
                    $scladRecord->amount += $amount; // схлапываем товар
                    $scladRecord->price =$price;                     
                    $scladRecord->relizePrice  = round($price*$this->priceKF, 2); 
//фигня тут                    $scladRecord->initPrice =$initPrice ; 
                    $scladRecord->isValid = 1;
                    $scladRecord->save();               
                    }
              if ($price == 0) continue;
                 $usedArray[$key] =1;//использован
                 
               }
          }
          /*Если не нашли в 1C*/
/*          как оказалось плохая практика удалять */
               foreach ($scladArray as $key => $val) 
               {          
               
                 if ($usedArray[$key] == 0) /*не было в синхронизации*/
                 {
                    
                    /*Yii::$app->db->createCommand('DELETE FROM {{%otves_list}} where refWarehouse=:refWarehouse')
                         ->bindValue(':refWarehouse', $scladArray[$key])
                         ->execute();*/                                
                         $scladRecord= ScladList::findOne($scladArray[$key]);
                         $scladRecord->isValid = 0;
                         $scladRecord->save();
               
                 }
               }

    $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 110";              
    Yii::$app->db->createCommand($strSql)->execute();
    
    
    /*Выделяем новые группы и фиксим связи*/    
    $strSql="INSERT INTO {{%ware_group}} (wareGrp) SELECT DISTINCT grpGood FROM {{%warehouse}} 
             LEFT JOIN {{%ware_group}} on wareGrp = grpGood where grpGood IS NOT NULL AND wareGrp IS NULL;";              
    Yii::$app->db->createCommand($strSql)->execute();
    
    $strSql="UPDATE {{%warehouse}},{{%ware_group}} SET {{%warehouse}}.wareGrpRef = {{%ware_group}}.id 
             where  wareGrp = grpGood;";              
    Yii::$app->db->createCommand($strSql)->execute();
    
    
return      $res;     
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
               
               $parse[1] = preg_replace("/[^(0-9.,)]/","",$parse[1]);
               $parse[3] = preg_replace("/[^(0-9.,)]/","",$parse[3]);
               
               if (!array_key_exists ($key, $scladArray))
               {
                 $scladRecord=     new ScladList();
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
               
return true;          
     }     
     
/*******************************************/

// Получить склад из таблички гугл

public function syncPriceByGoogle()
     {
          mb_internal_encoding("UTF-8");
          
          $scladArray=array();
          
           $list = Yii::$app->db->createCommand(
                      "SELECT id, wareTitle FROM {{%price}}  order by id")
                         ->queryAll();
     /*Делаем индексацию */               
               for($i=0; $i < count ($list); $i++)
               {
               if (empty($list[$i]['wareTitle'])){continue;}
                    $key = md5($list[$i]['wareTitle']);
                    $scladArray[$key]=$list[$i]['id'];                              
               }          
               unset ($list);                    

         //ссылка   
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 19')->queryScalar();
          //$url.="&single=true&output=csv";

        $page = $this->get_web_page( $url);               
          $content = mb_split('\r\n', $page['content'] );          

          $n=count($content);
          $res=array();

          for ($i=3; $i<$n;$i++ )
          {
               $parse = str_getcsv($content[$i],",");     
               if (count ($parse) < 8) {continue;}
               if (trim($parse[1]) == ""){continue;} //товар не задан
               $key = md5(trim($parse[1]));
               for ($j=2; $j < count($parse); $j++)
            {
               $parse[$j] = preg_replace("/[^(0-9.,)]/","",$parse[$j]);
            $parse[$j] =str_replace(',', '.',$parse[$j]);
               }
               
               if (!array_key_exists ($key, $scladArray))
               {
                 $priceRecord=     new PriceList();
              if (empty($priceRecord)) {continue;}
                    $priceRecord->wareType = trim($parse[0]);
                $priceRecord->wareTitle = trim($parse[1]);
                    $priceRecord->wareWeight = (float)$parse[2];
                $priceRecord->othod = (float)$parse[3];
                $priceRecord->wrkVal = (float)$parse[4];
                $priceRecord->cost= (float)$parse[5];
                
                $priceRecord->rawVal1= (float)$parse[6];
                $priceRecord->rawVal2= (float)$parse[7];
                $priceRecord->rawVal3= (float)$parse[8];
                $priceRecord->rawVal4= (float)$parse[9];
                
                $priceRecord->cntVal1 = (float)$parse[10];
                $priceRecord->cntVal2 = (float)$parse[11];
                $priceRecord->cntVal3 = (float)$parse[12];
                $priceRecord->cntVal4 = (float)$parse[13];

                $priceRecord->weightVal1 = (float)$parse[14];
                $priceRecord->weightVal2 = (float)$parse[15];
                $priceRecord->weightVal3 = (float)$parse[16];
                $priceRecord->weightVal4 = (float)$parse[17];

                $priceRecord->pcntVal1 = (float)$parse[18];
                $priceRecord->pcntVal2 = (float)$parse[19];
                $priceRecord->pcntVal3 = (float)$parse[20];
                $priceRecord->pcntVal4 = (float)$parse[21];
                
                $priceRecord->syncDateTime = time();
                $priceRecord->save();
               }
               else
               {                     
                 $priceRecord= PriceList::findOne($scladArray[$key]);
              
                 if (empty($priceRecord)) {continue;}

                $priceRecord->wareType = trim($parse[0]);
                    $priceRecord->wareType = trim($parse[0]);
                $priceRecord->wareTitle = trim($parse[1]);
                    $priceRecord->wareWeight = (float)$parse[2];
                $priceRecord->othod = (float)$parse[3];
                $priceRecord->wrkVal = (float)$parse[4];
                $priceRecord->cost= (float)$parse[5];

                $priceRecord->rawVal1= (float)$parse[6];
                $priceRecord->rawVal2= (float)$parse[7];
                $priceRecord->rawVal3= (float)$parse[8];
                $priceRecord->rawVal4= (float)$parse[9];
                
                $priceRecord->cntVal1 = (float)$parse[10];
                $priceRecord->cntVal2 = (float)$parse[11];
                $priceRecord->cntVal3 = (float)$parse[12];
                $priceRecord->cntVal4 = (float)$parse[13];

                $priceRecord->weightVal1 = (float)$parse[14];
                $priceRecord->weightVal2 = (float)$parse[15];
                $priceRecord->weightVal3 = (float)$parse[16];
                $priceRecord->weightVal4 = (float)$parse[17];

                $priceRecord->pcntVal1 = (float)$parse[18];
                $priceRecord->pcntVal2 = (float)$parse[19];
                $priceRecord->pcntVal3 = (float)$parse[20];
                $priceRecord->pcntVal4 = (float)$parse[21];
                
                $priceRecord->syncDateTime = time();
              
                $priceRecord->save();
                //$priceRecord[$key] =0;//использован                 
               }
          }
     }     
     
/*******************************************/


/*****************************************/     
/******   Поставщики  **************/     
/*****************************************/
/*

*/
/* Грузим счета поставщиков */

public function loadSupplierSchets ($startRow, $allRecords)
     {
          mb_internal_encoding("UTF-8");          
          
          $res=array();     
     
          $startId = PHP_INT_MAX;
     
        $curUserId=0; 
        if ($this->webSync)
        { 
          $session = Yii::$app->session;          
          $session->open();
          $curUser=Yii::$app->user->identity;
          $curUserId=$curUser->id;
        }  
     
        /*Получим список валидных префиксов*/
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
          
        //$period=$this->getCfgValue(2001);
          $period=1;//на один день назад
          if (empty($this->syncDate))
          {
          $lastSchet = Yii::$app->db->createCommand(
                      "SELECT ifnull(max(schetDate),'2001-01-01') FROM {{%supplier_schet_header}}")->queryScalar();                    
          $syncTime= strtotime ($lastSchet);
          $enTime =time()+24*60*60;          
          }
          else 
          {           
            $syncTime= strtotime ($this->syncDate);
            $enTime = $syncTime+60*60*24;
          }

            $stTime= $syncTime-60*60*24*$period;
            $lastSupplyTime =$stTime;
            
          
          
        /*Получим список уже занесенных за период синхронизации записей */          
          $list = Yii::$app->db->createCommand(
                      "SELECT id FROM {{%supplier_schet_content}} where schetDate >= :stDate AND schetDate <= :enDate")
                         ->bindValue(':stDate',date('Y-m-d',$stTime))
                         ->bindValue(':enDate',date('Y-m-d',$enTime))
                         ->queryAll();                              
                         
          $refArray = array();               
          for($i=0; $i < count ($list); $i++)
          {
               // Создаем идентификатор записи -                
               $refArray[$list[$i]['id']] = 0;
          }
          
          if ($this->webSync)
          {                          
            $session->set('supplierSchetRefArray', $refArray);
            $session->set('lastSupplierSchetTime', $lastSupplyTime);
          }
        }
        else 
          {
           if ($this->webSync)
            { 
               $refArray = $session->get('supplierSchetRefArray');
               $lastSupplyTime = $session->get('lastSupplierSchetTime');
               $stTime= $lastSupplyTime;
               if (empty($this->syncDate))   $enTime =time()+24*60*60;          
                                    else $enTime =$stTime+24*60*60*($period+1);          
               if (empty ($lastSupplyTime)) return false;
               if (empty ($refArray)) $refArray = array();
            }   
          }                         
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 17')->queryScalar();
          $loadurl =  $url.$startRow."&sd=".date("dmY",$stTime)."&ed=".date("dmY",$enTime);

//echo "<pre>";
//print_r ($loadurl);
        
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );
//print_r ($content);
          $err=array();     
          $lastLoaded=0;          
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updatedSupply = 0;
          
          if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;
          }                    
          
//$scanKey=array();
        $isUpdated = 0;   
        $curINN ="";
        $curKPPP="";
        $curSchetRef1C ="";
        $curSchetDate = "";
          for ($i;$i< count($content); $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                         
               $lastLoaded     =$parse[0];
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 10) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                       
               
               /*Определяем номер счета*/               
               $schetRef1C= trim($parse[3]);
            
             /*1. Все плохо - как занесли номер мы не знаем, но надеемся*/
             
            //Уберем все не цифровые c начала текста            
            $schetNum = preg_replace("/^[\D]+/u","",$schetRef1C);     
            //И предшествующие нули тоже
            $schetNum = preg_replace("/^0+/u","", $schetNum);     
               
            //Уберем все цифровые и идущий за ним текст - префикс буквенный
            $schetPrefix = preg_replace("/[\d]+.*/u","", $schetRef1C);

            
               $schetTime= strtotime(mb_substr($parse[4],1));
                $schetDate=date("Y-m-d", $schetTime);

            // Пока пропустим всех
                             
               if (!empty ($parse[2])) $orgINN = trim($parse[2]);
                                     else  $orgINN = "-";
               if (!empty ($parse[10])) $orgKPP = trim($parse[10]);
                                     else  $orgKPP = "-";                                     
               $goodSumm =  (float)str_replace(',', '.',$parse[9]);
               $goodCount=  (float)str_replace(',', '.',$parse[7]);
               $goodTitle=trim($parse[6]);
            //Считаем что не получим два одинаковых товара в одном счете на одну организацию в один день - иначе сольем их в один
            //$key = $list[$i]['supplierRef1C']."_".md5($list[$i]['schetDate']."_".$orgINN."_".$list[$i]['goodTitle']);
            // $key = $schetRef1C."_".md5($schetDate."_".$orgINN."_".$goodTitle);               
            /*Ищем хедер*/
            
             if( $curINN != $orgINN || $curKPPP != $orgKPP || $curSchetRef1C != $schetRef1C || $curSchetDate !=$schetDate)
             {
             /*если оно новое*/
              $curINN  = $orgINN;
              $curKPPP = $orgKPP;
              $curSchetRef1C = $schetRef1C;
              $curSchetDate  = $schetDate;
              
             $recordHeader = SupplierSchetHeaderList::findOne([
                    'orgINN' => $orgINN,
                    'orgKPP' => $orgKPP,
                    'supplierRef1C' => $schetRef1C,
                    'schetDate' => $schetDate,            
                ]);
            
            /*Если не нашли, то сделаем*/
            if (empty($recordHeader))
            {
                $recordHeader = new SupplierSchetHeaderList();
                $recordHeader->schetNum   = $schetNum;
                $recordHeader->schetDate  = $schetDate;
                $recordHeader->supplierRef1C = $schetRef1C;
                $recordHeader->schetPrefix = $schetPrefix;
                $recordHeader->orgINN     = $orgINN;               
                $recordHeader->orgKPP     = $orgKPP;            
                $recordHeader->orgTitle   = trim($parse[1]);                         
                $recordHeader->save();
            } else
            {
            /*
            Увы попытка корректно обновлять приводить только к большим проблемам. 
            поскольку счетов не много при пересинхронизации прибиваем содержимое и создаем заново
            */           
             Yii::$app->db->createCommand(
            'DELETE FROM {{%supplier_schet_content}} where schetRef=:schetRef ', 
            [
            ':schetRef' => $recordHeader->id,
            ])->execute();
            }
            }
            
            if (empty($recordHeader)) continue; // Problem

            /*            
            попытка апнуть
            $isUpdated = 0;
            
            $record = SupplierSchetContentList::FindOne([
            'schetRef' => $recordHeader-> id,
            'goodTitle' => $goodTitle,
            'goodSumm'  => $goodSumm
            ]); //потенциально две одинаковых строчки в одном счете вызовут проблему.
                        
            if(!empty ($record)){    

                $refArray[$record->id]++;//обработано
                if( $record->schetPrefix != $schetPrefix)  {$record->schetPrefix = $schetPrefix; $isUpdated = 1;}
                if( $record->orgKPP      != $orgKPP)        {$record->orgKPP     = $orgKPP; $isUpdated = 1;}            
                if( $record->orgTitle    != trim($parse[1])){$record->orgTitle   = trim($parse[1]); $isUpdated = 1;}                         
                 if( $record->goodCount   != $goodCount)     {$record->goodCount  = $goodCount; $isUpdated = 1;}
                if( $record->goodEd          != trim($parse[8])){$record->goodEd         = trim($parse[8]); $isUpdated = 1;}

                if ($isUpdated == 1)
                {
                    $record->schetNum   = $schetNum;
                    $record->schetRef   = $recordHeader->id;
                    $record->save();
                    $updatedSupply++;
                }
        
            }else*/
            {
               $record = new SupplierSchetContentList();     
               if (empty($record)) continue; // Problem
               $record->schetNum   = $schetNum;
               $record->schetDate  = $schetDate;
               $record->supplierRef1C = $schetRef1C;
               $record->schetPrefix = $schetPrefix;
               $record->orgINN     = $orgINN;               
               $record->orgKPP     = $orgKPP;            
               $record->orgTitle   = trim($parse[1]);                         
               $record->goodSumm   = $goodSumm;
               $record->goodTitle  =  trim($parse[6]);
               $record->goodCount  = $goodCount;
               $record->goodEd         = trim($parse[8]);     
               $record->schetRef   = $recordHeader->id;
               $record->save();
               $updatedSupply++;            
            }
            
            $startId = min($startId,$record->id);
          }//
                    
      
        if($lastLoaded >= $allRecords)
        {
            //Сосканировали все - удаляем отсутствующие           
/*          foreach ($refArray as $key => $value) {
            if ($value == 0)
            {
                $record = SupplierSchetContentList::findOne($key);
                if (empty ($record)) continue;
                $record->delete();
            }
          }              
*/          
        }
                     
                
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updatedSupply;
        $ret['err'] = $err;
    
    
    /*Классифицируем товар*/    
    $strSql = "UPDATE {{%supplier_schet_content}} as a,  {{%ware_grp}} as b  SET a.wareGrpRef = b.id
    where    a.goodTitle like b.wareGrpTemplate   and    a.wareGrpRef = 0";
    Yii::$app->db->createCommand($strSql)   ->execute();  
        
    /*Подтянем в склад все что классифицируется и отсутствует там*/          
    $strSql = "INSERT INTO {{%warehouse}} (title,    ed,  grpRef)
    (SELECT DISTINCT a.goodTitle,   a.goodEd, a.wareGrpRef 
    from   {{%supplier_schet_content}} as a 
    LEFT JOIN {{%warehouse}} as b on (b.title = a.goodTitle and b.ed = a.goodEd)
    where    a.wareGrpRef > 0   and  b.id is null   and a.id >= :startId )";
    Yii::$app->db->createCommand($strSql, [':startId' => $startId])    ->execute();  
      
    /*привяжемся к складу все что с последней синхронизации*/    
    $strSql = "update {{%supplier_schet_content}}, {{%warehouse}} set {{%supplier_schet_content}}.wareRef = {{%warehouse}}.id 
    where {{%supplier_schet_content}}.goodTitle = {{%warehouse}}.title and {{%supplier_schet_content}}.wareRef = 0 
    and {{%supplier_schet_content}}.id >= :startId"; 
    
      Yii::$app->db->createCommand($strSql, [':startId' => $startId])  ->execute();  
       
    /*привяжемся к организации все что с последней синхронизации*/    
       
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%supplier_schet_header}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL) and b.[[isReject]] = 0
     ";      
    Yii::$app->db->createCommand($strSql)     ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%supplier_schet_header}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0
          "; 

    Yii::$app->db->createCommand($strSql)     ->execute();     

    $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 117";              
    Yii::$app->db->createCommand($strSql)->execute(); 
    
    return $ret;
     }
     
     
     
/********************/     

/* Грузим оплаты поставщикам */
public function loadSupplierOplata ($startRow, $allRecords)
     {
          mb_internal_encoding("UTF-8");          
          
          $res=array();     
        
        if ($this->webSync){
          $session = Yii::$app->session;          
          $session->open();
        }

        /*Получим список валидных префиксов*/
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
          /*Последняя поставка*/
          $lastDate = Yii::$app->db->createCommand(
                      "SELECT ifnull(max(oplateDate),'2001-01-01') FROM {{%supplier_oplata}}")->queryScalar();                    
          $period=$this->getCfgValue(2001);
                    
          $lastTime = strtotime ($lastDate)-60*60*24*$period;

     
        /*Получим список уже занесенных за период синхронизации записей */          
          $list = Yii::$app->db->createCommand(
                      "SELECT id, oplateDate, ref1C, orgINN, oplateSumm FROM {{%supplier_oplata}} where oplateDate >= :oplateDate")
                         ->bindValue(':oplateDate',date('Y-m-d',$lastTime))
                         ->queryAll();                              
                         
          $refArray = array();               
          for($i=0; $i < count ($list); $i++)
          {
               // Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)          
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
                                                  else  $orgINN = "-";            
//               $key = $list[$i]['ref1C']."_".md5($list[$i]['oplateDate']."_".$orgINN."_".$list[$i]['oplateSumm']);
            $key = $list[$i]['ref1C']."_".md5($list[$i]['oplateDate']."_".$orgINN);                        
//echo  $list[$i]['ref1C']."_".$list[$i]['oplateDate']."_".$orgINN."\n";                
//echo  $key."\n";
               $refArray[$key]=$list[$i]['id'];                              
          }
          
          if ($this->webSync){                                   
            $session->set('supplierOplataRefArray', $refArray);
            $session->set('lastSupplierOplataTime', $lastTime);
          }
//$ret['supplierSchetRefArray'] =$refArray;                  
          }
          else 
          {
           if ($this->webSync){                                       
               $refArray = $session->get('supplierOplataRefArray');
               $lastTime = $session->get('lastSupplierOplataTime');
           }    
               if (empty ($lastTime)) return false;
               if (empty ($refArray)) $refArray = array();
          }                         
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 18')->queryScalar();
          $loadurl =  $url.$startRow."&sd=".date("dmY",$lastTime)."&ed=".date("dmY",time()+24*60*60);
          
$ret['loadurl'][] =$loadurl;                         
          
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );

//$ret['content'][] =$content;                                 

          $err=array();     
        $lastLoaded=0;          
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updated = 0;
        
//print_r($refArray);          
          if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;
          }                    
          
//$scanKey=array();
        $isUpdated = 0;     
          for ($i;$i< count($content); $i++)
          {                         
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                         
            
               $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               $loadCounter++;
               if ($loadCounter > 2500){break;}                    
               
               if (count($parse) < 10) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                       
               
               /*Определяем номер счета*/               
               $ref1C= trim($parse[5]);
            
             /*1. Все плохо - как занесли номер мы не знаем, но надеемся*/
             
            //Уберем все не цифровые c начала текста            
            //$schetNum = preg_replace("/^[\D]+/u","",$ref1C);     
            //И предшествующие нули тоже
            //$schetNum = preg_replace("/^0+/u","", $schetNum);     
               
            //Уберем все цифровые и идущий за ним текст - префикс буквенный
            $prefix = preg_replace("/[\d]+.*/u","", $ref1C);

            
               $oplateTime= strtotime(mb_substr($parse[4],1));
                $oplateDate=date("Y-m-d", $oplateTime);

            //if(!array_key_exists ($prefix, $schetPrefixArray)) continue; //с неправильным префиксом
                              
               if (!empty ($parse[2])) $orgINN = trim($parse[2]);
                                     else  $orgINN = "-";
               if (!empty ($parse[10])) $orgKPP = trim($parse[10]);
                                     else  $orgKPP = "-";                                     
               $summ =  (float)str_replace(',', '.',$parse[9]);

//echo  $ref1C."_".$oplateDate."_".$orgINN."\n";                

               $key = $ref1C."_".md5($oplateDate."_".$orgINN);               
//print_r($key."\n");             
            $sdelkaTime = strtotime(mb_substr($parse[7],1));
            if ($sdelkaTime < 100 ) $sdelkaTime = time();
                $sdelkaDate = date('Y-m-d',$sdelkaTime);
          
//$scanKey[]=$key;    
            $isUpdated = 0;        
               if(array_key_exists ($key, $refArray))
            {
                $refArray[$key] = 0; //метим как использовано               
                $record = SupplierOplataList::findOne($refArray[$key]);
                if (empty ($record)) continue;
 
                if ($record->sdelkaNum   != trim($parse[8]))
                    {$record->sdelkaNum   = trim($parse[8]); $isUpdated = 1;}
                if($record->sdelkaDate   != $sdelkaDate) 
                    {$record->sdelkaDate  = $sdelkaDate; $isUpdated = 1;}
                if($record->orgKPP       != $orgKPP) 
                    { $record->orgKPP     = $orgKPP;   $isUpdated = 1;    }
                if($record->orgTitle      = trim($parse[1])               )
                    {$record->orgTitle    = trim($parse[1]); $isUpdated = 1;}                         
                
             if ($isUpdated == 1)
                {
//echo $key." updated\n";                    
                    $record->save();
                    $updated++;
                }                                //уже есть
            }else
            {
               
               $record = new SupplierOplataList();     
//echo $key." inserted\n";
               $record->sdelkaNum   = trim($parse[8]);
               $record->sdelkaDate  = $sdelkaDate;
               $record->ref1C = $ref1C;
               $record->orgINN     = $orgINN;               
            $record->orgKPP     = $orgKPP;            
               $record->orgTitle   = trim($parse[1]);                         
               $record->oplateSumm   = $summ;
               $record->oplateDate  =  $oplateDate;
               $record->save();
               $updated++;
            }
          }//
                    
//print_r($refArray);                          
        if($lastLoaded >= $allRecords && $updated > 0)
        {
            //Сосканировали все - удаляем отсутствующие           
          foreach ($refArray as $key => $value) {
            if ($value > 0)
            {
                $record = SupplierOplataList::findOne($value);
                if (empty ($record)) continue;
                $record->delete();
            }
          }              
        }
                
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;

      
    /*привяжемся к организации все что с последней синхронизации*/    
       
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%supplier_oplata}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL) and b.[[isReject]] = 0";      //AND supplyDate >= :supplyDate";           
     /*->bindValue(':supplyDate', date("Y-m-d",$lastSupplyTime))*/
    Yii::$app->db->createCommand($strSql)     ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%supplier_oplata}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0";      //AND supplyDate >= :supplyDate"; 
     /*->bindValue(':supplyDate', date("Y-m-d",$lastSupplyTime))*/
    Yii::$app->db->createCommand($strSql)     ->execute();     
       
    $strSql="UPDATE {{%supplier_oplata}} left join  {{%supplier_schet_header}}
    on ( sdelkaNum = supplierRef1C and sdelkaDate=schetDate and {{%supplier_oplata}}.orgINN = {{%supplier_schet_header}}.orgINN)
    SET  {{%supplier_oplata}}.supplierSchetRef = {{%supplier_schet_header}}.id
    where YEAR(schetDate) >= YEAR(:lastDate)
    and {{%supplier_oplata}}.sdelkaNum is not null 
    and {{%supplier_schet_header}}.id is not null
    and {{%supplier_oplata}}.supplierSchetRef = 0
    ";
    Yii::$app->db->createCommand($strSql)     
    ->bindValue(':lastDate', date("Y-m-d",$lastTime))
    ->execute();        
       
    $strSql="update {{%supplier_oplata}} as a,  {{%supplier_schet_header}} as c  set a.supplierSchetRef = c.id
    where  a.refOrg =  c.refOrg AND  a.oplateDate = c.schetDate  AND  a.supplierSchetRef = 0 AND  a.oplateDate >= :lastDate";
    Yii::$app->db->createCommand($strSql)     
    ->bindValue(':lastDate', date("Y-m-d",$lastTime))
    ->execute();     
       
       
    $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 118";              
    Yii::$app->db->createCommand($strSql)->execute();
   
        return $ret;
     }
     
/********************/     

/* Грузим поступление товара */
public function loadSupplierWares ($startRow, $allRecords)
     {
          mb_internal_encoding("UTF-8");          
          
          $res=array();     
     
       if ($this->webSync){
          $session = Yii::$app->session;          
          $session->open();
        }
        
        if ( empty($this->syncDate))
        {
          $lastDate = Yii::$app->db->createCommand(
                      "SELECT ifnull(max(requestDate),'2010-01-01') FROM {{%supplier_wares}}")->queryScalar();
          $period=$this->getCfgValue(2001);
          $lastTime = strtotime ($lastDate) - 60*60*24*$period;
          $this->syncDate = date('Y-m-d',$lastTime);
        }
        else
        {
            $lastTime = strtotime ($this->syncDate) - 60*60*24;        
        }


        /*Получим список валидных префиксов*/
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
          /*Последняя поставка*/
        /*Получим список уже занесенных сегодня записей */          
          $list = Yii::$app->db->createCommand(
                      "SELECT id, requestDate, ref1C, orgINN, wareSumm, wareTitle FROM {{%supplier_wares}} where requestDate >= :requestDate ORDER BY requestDate")
                         ->bindValue(':requestDate',$this->syncDate )
                         ->queryAll();                              
                         
          $refArray = array();               
          for($i=0; $i < count ($list); $i++)
          {
               // Создаем идентификатор оплаты - 1С-ссылка_дата_инн_сумма  (Y-m-d)          
            if (!empty ($list[$i]['orgINN'])) $orgINN = $list[$i]['orgINN'];
                                                  else  $orgINN = "-";            
               $key = $list[$i]['ref1C']."_".md5($list[$i]['requestDate']."_".$orgINN."_".$list[$i]['wareSumm']."_".$list[$i]['wareTitle']);

               $refArray[$key]=$list[$i]['id'];                              
          }

          if ($this->webSync){
              $session->set('supplierWaresRefArray', $refArray);
              $session->set('lastSupplierWaresTime', $lastTime);
          }
//$ret['supplierSchetRefArray'] =$refArray;                  
          }
          else 
          {
             if ($this->webSync){
               $refArray = $session->get('supplierWaresRefArray');
               $lastTime = $session->get('lastSupplierWaresTime');
               if (empty ($lastTime)) return false;
               if (empty ($refArray)) $refArray = array();
              }
          }                         
          
          /*Load data*/
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 16')->queryScalar();
          $loadurl =  $url.$startRow."&sd=".date("dmY",$lastTime)."&ed=".date("dmY",time()+24*60*60);
          
$ret['loadurl'][] =$loadurl;                         
          
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );
            
//$ret['content'][] =$content;                                 

          $err=array();     
          $lastLoaded=0;          
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updated = 0;
          
          if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;            
          }                    
          
//$scanKey=array();
        $isUpdated = 0;        
          for ($i;$i< count($content); $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
               $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 10) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                       
               
               /*Определяем номер счета*/               
               $ref1C= trim($parse[3]);
            
             /*1. Все плохо - как занесли номер мы не знаем, но надеемся*/
             
            //Уберем все не цифровые c начала текста            
            //$schetNum = preg_replace("/^[\D]+/u","",$ref1C);     
            //И предшествующие нули тоже
            //$schetNum = preg_replace("/^0+/u","", $schetNum);     
               
            //Уберем все цифровые и идущий за ним текст - префикс буквенный
            $prefix = preg_replace("/[\d]+.*/u","", $ref1C);
           
              $requestTime= strtotime(mb_substr($parse[4],1));
              $requestDate=date("Y-m-d", $requestTime);
          
            // Пока пропустим всех
               //if(!array_key_exists ($prefix, $schetPrefixArray)) continue; //с неправильным префиксом
                              
               if (!empty ($parse[2])) $orgINN = trim($parse[2]);
                                     else  $orgINN = "-";
               if (!empty ($parse[10])) $orgKPP = trim($parse[10]);
                                     else  $orgKPP = "-";                                     
               $summ =  (float)str_replace(',', '.',$parse[9]);
               $count=  (float)str_replace(',', '.',$parse[7]);
            $wareTitle = trim($parse[6]);
               
            //Считаем что не получим две одинаковых суммы в одном счете - смело Problem Vv ?
               $key = $ref1C."_".$requestDate."_".$orgINN."_".$summ;               
            $key = $ref1C."_".md5($requestDate."_".$orgINN."_".$summ."_".$wareTitle);
            $isUpdated = 0;
               if(array_key_exists ($key, $refArray))
            {                
                if ($refArray[$key] == 0) continue; // уже апдейтили - по идее не должно быть!
                $refArray[$key] = 0; //метим как использовано               
                $record = SupplierWaresList::findOne($refArray[$key]);
                if (empty ($record)) continue;
                
                
                if ($record->wareCount  != $count )       {$record->wareCount   = $count; $isUpdated = 1;}     
                if ($record->wareEd   != trim($parse[8])) {$record->wareEd   = trim($parse[8]);$isUpdated = 1;}
                if ($record->wareSumm  != $summ)          {$record->wareSumm   = $summ;$isUpdated = 1;}
                if ($isUpdated == 1)
                {
                    $record->save();
                    $updated++;
                }                
            }else
            {
                /*Новая запись всегда сохранять*/
                $record = new SupplierWaresList();     
                $isUpdated = 1;
               // $record->requestNum   = trim($parse[3]);
                $record->requestDate  = $requestDate;
                $record->ref1C = $ref1C;
                $record->orgINN     = $orgINN;               
                $record->orgKPP     = $orgKPP;            
                $record->orgTitle   = trim($parse[1]);                         
                $record->wareTitle   = $wareTitle;                         
                $record->wareCount   = $count;                         
                $record->wareEd   = trim($parse[8]);                         
                $record->wareSumm   = $summ;
                $record->save();
                $updated++;
            }
               
          }//
        
        
        if($lastLoaded >= $allRecords && $updated > 0)
        {
        

            //Сосканировали все - удаляем отсутствующие           
          foreach ($refArray as $key => $value) {
            if ($value > 0)
            {
                $record = SupplierWaresList::findOne($value);
                if (empty ($record)) continue;
                $record->delete();
            }
          }              
        }
                    
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;
//$ret['scanKey'] =$scanKey;
      
    /*привяжемся к складу все что с последней синхронизации*/    
    
      $strSql = "update {{%supplier_wares}}, {{%warehouse}} set {{%supplier_wares}}.wareRef = {{%warehouse}}.id 
      where {{%supplier_wares}}.wareTitle = {{%warehouse}}.title and {{%supplier_wares}}.wareRef = 0"; //AND supplyDate >= :supplyDate"; 
      Yii::$app->db->createCommand($strSql)
          /*->bindValue(':supplyDate', date("Y-m-d",$lastSupplyTime))*/
       ->execute();  /* Problem Vv : лучше бы по id */
       
    /*привяжемся к организации все что с последней синхронизации*/    
       
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%supplier_wares}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL) and b.[[isReject]] = 0";      //AND supplyDate >= :supplyDate";           
     /*->bindValue(':supplyDate', date("Y-m-d",$lastSupplyTime))*/
    Yii::$app->db->createCommand($strSql)     ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%supplier_wares}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0 AND requestDate >= :lastDate"; 
     
    Yii::$app->db->createCommand($strSql)     
    ->bindValue(':lastDate', date("Y-m-d",$lastTime))
    ->execute();     
       
    /*привяжемся к счету - попытка не пытка ;) */    
     $strSql=" update rik_supplier_wares as a, rik_supplier_schet_content as b, rik_supplier_schet_header as c
    set a.supplierSchetRef = b.schetRef  where a.refOrg =  c.refOrg AND c.id = b.schetRef AND a.requestDate = b.schetDate
    AND a.wareTitle = b.goodTitle AND  a.supplierSchetRef = 0 AND  a.requestDate >= :lastDate";
    Yii::$app->db->createCommand($strSql)     
    ->bindValue(':lastDate', date("Y-m-d",$lastTime))
    ->execute();     
       
       
    $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 116";              
    Yii::$app->db->createCommand($strSql)->execute();
          
        return $ret;
     }
     

/*****************************************/
/**********  Склад ***********************/     
/*****************************************/          
     public function syncScladControl($syncTime, $isPrev)
     {
          mb_internal_encoding("UTF-8");
          $res=array();

/*
    Получим дату последней синхронизации
*/          
//       $lastDate = Yii::$app->db->createCommand("SELECT MAX (syncDate) FROM {{%control_remains}} where isPrevious = ''") ->queryScalar();        
//       https://s3.arenda1c.ru/trade_e7981678a01e10d025b47bd0b730da1c/hs/report_orders?start=1&report=22&sd=27032019&ed=27032019
/*
    Получим склад        
*/
        $scladArray=array();
           $list = Yii::$app->db->createCommand("SELECT id, usedOrgTitle, scladTitle, wareTitle FROM {{%control_remains}} WHERE isPrevious = ".$isPrev." order by id") ->queryAll();                    
               for($i=0; $i < count ($list); $i++)
               {
                    $key = md5($list[$i]['usedOrgTitle']."_".$list[$i]['scladTitle']."_".$list[$i]['wareTitle']);
                    $scladArray[$key]=$list[$i]['id'];                                              
               }          
               unset ($list);          
//$res[]=$scladArray;                    
/*
Получим данные из 1с
*/          
$syncDate=date("dmY",$syncTime);
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 10')->queryScalar();
          $url.="1"."&sd=".$syncDate."&ed=".$syncDate;
          
//$res[]=$url;
          
          $page = $this->get_web_page( $url);               
          $content = mb_split('\r\n', $page['content'] );          

//$res[]=$page;                                   
//$res[]=$content;                                             
          
          $parse = str_getcsv($content[0],",");     
          $rowNum=$parse[0];
          
//$res[]=$rowNum;                    
          $n=count($content);

$syncDate=date("Y-m-d",$syncTime);                            
          for ($i=1; $i<$n;$i++ )
          {
               $parse = str_getcsv($content[$i],",");     
               if (count($parse) < 9) {continue;} /*неполная запись*/

              /*наименование*/
               $key = md5(trim($parse[1])."_".trim($parse[2])."_".trim($parse[5]));
               
               /*К-во*/
               $parse[7] = preg_replace("/[^(0-9.,\-)]/","",$parse[7]);
               $amount=(float)str_replace(',', '.',$parse[7]);            
               
               /*Цена*/
               $parse[8] = preg_replace("/[^(0-9.,)]/","",$parse[8]);
               $price=(float)str_replace(',', '.',$parse[8]);             
               
               /*Себестоимость*/
               $parse[9] = preg_replace("/[^(0-9.,)]/","",$parse[9]);
               $initPrice=(float)str_replace(',', '.',$parse[9]); 
            
               if (!array_key_exists ($key, $scladArray) )
               {
                    /*Создадим товар*/     
                    $scladRecord=     new TblControlRemains();
                    
                    $scladRecord->usedOrgTitle= trim($parse[1]);
                    $scladRecord->scladTitle  = trim($parse[2]);
                    $scladRecord->articul     = trim($parse[3]);
                    $scladRecord->wareGroup   = trim($parse[4]);
                    $scladRecord->wareTitle   = trim($parse[5]);
                    $scladRecord->ed          = trim($parse[6]);
                    $scladRecord->amount      = $amount;
                    $scladRecord->relizePrice = $price;
                    $scladRecord->initPrice   = $initPrice;
                    $scladRecord->isPrevious  = $isPrev;
                    $scladRecord->syncDate    = $syncDate;                 
                    $scladRecord->save();
                    
               }
               else
               { 
               /*Обновим товар*/  
                 
                 if ( $scladArray[$key] == 0 ) continue; /* уже было - алерт по идее*/               
                 
                 $scladRecord= TblControlRemains::findOne($scladArray[$key]);
                 if (empty($scladRecord)) {continue;}    /* не найдено - алерт по идее*/               

                    $scladRecord->amount      = $amount;
                    $scladRecord->relizePrice = $price;
                    $scladRecord->initPrice   = $initPrice;
                    $scladRecord->isPrevious  = $isPrev;
                    $scladRecord->syncDate    = $syncDate;                 
                    $scladRecord->save();
                                    
                    $scladArray[$key] = 0; //использовано                    
                }
                 
            }
          
          /*Если не нашли в 1C*/
               foreach ($scladArray as $key => $val) {
                 if ($val != 0) /*не было в синхронизации*/
                 {
                         $scladRecord= ScladList::findOne($scladArray[$key]);
                         if (empty($scladRecord)) continue;
                         $scladRecord->amount = 0;
                         $scladRecord->save();               
                 }
               }
    return    $res;     
     }     
     
     
/****************/
   /*
   ALTER TABLE `rik_contracts` ADD COLUMN `contractNumber` VARCHAR(20) DEFAULT NULL COMMENT 'Отображаемый номер договора';
   
   */  
/**********************/
     
     public function syncGoogleContract()
     {
          mb_internal_encoding("UTF-8");
          $res=array();

/*
    Получим список занесенных договоров ИНН_КПП_номер
*/
        $refArray=array();
           $list = Yii::$app->db->createCommand("SELECT id, creationTime, orgINN, orgKPP, internalNumber FROM {{%contracts}} order by id") ->queryAll();                    
               for($i=0; $i < count ($list); $i++)
               {
                    $timeStamp= strtotime($list[$i]['creationTime']);
                    $key = md5($timeStamp."_".$list[$i]['orgINN']."_".$list[$i]['orgKPP']."_".$list[$i]['internalNumber']);
                    $refArray[$key]=$list[$i]['id'];                                              
               }          
               unset ($list);          
/*
Получим данные из гугл таблицы
*/          
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 119')->queryScalar();         
//$res[]=$url;
          
          $page = $this->get_web_page( $url);               
          $content = mb_split('\r\n', $page['content'] );          
          
//$res[]=$page;                                   
//$res[]=$content;                                             
          $n=count($content);
          for ($i=1; $i<$n;$i++ )
          {
               $parse = str_getcsv($content[$i],",");     
//$res[]=$parse;                     
             if (count($parse) < 23) {continue;} /*неполная запись*/
             
             $orgINN = trim($parse[3]);
             $orgKPP = trim($parse[4]);
             $internalNumber = intval($parse[22]);
             $timeStamp= strtotime($parse[0]);
             $key = md5($timeStamp."_".$orgINN."_".$orgKPP."_".$internalNumber);
           
             if (!array_key_exists ($key, $refArray) )
               {
                    /*Создадим товар*/     
                    $record=     new TblContracts();
                                        
                    $record->creationTime    = date("Y-m-d h:i:s", $timeStamp);
                    $record->clientTitle     = trim($parse[1]);
                    $record->clientAdress    = trim($parse[2]);
                    $record->orgINN          = $orgINN;
                    $record->orgKPP          = $orgKPP;
                    $record->bankRekvesits   = trim($parse[5]);
                    $record->contactorFull   = trim($parse[6]);
                    $record->contractorShort = trim($parse[7]);
                    $record->contractorPost  = trim($parse[8]);
                    $record->contractorReason= trim($parse[9]);
                    $record->oplatePeriod    = intval($parse[10]);
                    $record->oplateStart     = trim($parse[11]);
                    $record->dopCondition    = trim($parse[12]);
                    $record->userFormer      = trim($parse[13]);
                    if (!empty(trim($parse[14])))
                        $record->dateEnd     = date("Y-m-d", strtotime($parse[14]));
                    $record->phonesList      = trim($parse[15]);
                    $record->email           = trim($parse[16]);
                    $record->siteUrl         = trim($parse[17]);
                    if (!empty(trim($parse[18])))
                        $record->dateStart   = date("Y-m-d", strtotime($parse[18]));
                    
                    $record->predoplata      = intval(preg_replace("/[\D]/","",$parse[19]));
                    $record->postoplate          = trim($parse[20]);
                    $record->docUrl          = trim($parse[21]);
                    $record->internalNumber  = $internalNumber;

                    $record->save();
                    
               }
               else
               { 
               /*Обновим товар*/  
                 
                 if ( $refArray[$key] == 0 ) continue; /* уже было - алерт по идее*/               
                 
                 $record= TblContracts::findOne($refArray[$key]);
                 if (empty($record)) {continue;}    /* не найдено - алерт по идее*/               
                 $update=0;
                 /*Не обновляем*/                                   
                 $refArray[$key] = 0; //использовано                    
                }
                              

                                           
             
          }          

          
/*привяжемся к организации все что с последней синхронизации*/           
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%contracts}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.refOrg =0 or a.refOrg IS NULL) and b.[[isReject]] = 0";      //AND supplyDate >= :supplyDate";           
     Yii::$app->db->createCommand($strSql) ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%contracts}} as a, {{%orglist}} as b set a.refOrg = b.id 
          where a.orgINN = b.schetINN AND (a.refOrg =0 or a.refOrg IS NULL)  and b.[[isReject]] = 0 ";      
     Yii::$app->db->createCommand($strSql) ->execute();     
          
                    
return $res;
//print_r($res);                    
     
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
