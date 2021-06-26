<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 


use app\models\TblProfitHeader;
use app\models\TblProfitContent;

use app\models\TblControlBankHeader;
use app\models\TblControlBank;

use app\modules\bank\models\TblBankOpHeader;
use app\modules\bank\models\TblBankOpContent;
use app\modules\bank\models\TblBuhStatHeader;

///---
use app\models\TblControlSverkaHeader;
use app\models\TblControlSverkaDolga;
use app\models\TblControlSverkaUse;

use app\models\TblControlPurchHeader;
use app\models\TblControlPurchContent;

use app\models\TblBuhSchetContent;
use app\models\TblBuhSchetHeader;


use app\models\TblWareGrp;
use app\models\TblWareProducer;
use app\models\TblWareList;
use app\models\TblWareProdLnk; 
use app\models\TblWarehouse;

use app\models\OrgList;

/**
 * DataSync - синхронизация данных с 1C консольная версия
 */
class DataConsoleSync extends Model
{
     
    public $syncDate = "";
    public $syncDates=[];      
    
     public function rules()
    {
        return [            
            //[[ 'actionCode', 'updExistedClients','googleClientsUrl', 'importSchetUrl', 'importOplataUrl','importPostavkaUrl','importContactsUrl'], 'default'],               
        ];
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

/********************/     
/*****************************************/
/********** Закупка товара           *****/     
/*****************************************/          
/* */
public function loadPurchData ($startRow, $syncTime)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();     
        
         $sD=date("dmY",$syncTime);      
         $eD=date("dmY",$syncTime+24*3600);           
         
          /*Load data*/
          $url = $this->getCfgValue(33);          
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;          
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );          

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

          $isUpdated = 0;        
          $nC = count($content);
          $syncDate = date('Y-m-d',$syncTime);
          
              /*Заголовок*/      
          $headRecord = new TblControlPurchHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = 0;
          $headRecord->save();
          
          for ($i;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
               $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 12) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     
                                                                                              
               $purchAmount    =  (float)str_replace(',', '.',$parse[8]);    
               $purchSum       =  (float)str_replace(',', '.',$parse[10]);    
               $recordTime= strtotime(mb_substr($parse[5],1));
               $recordDate=date("Y-m-d H:i:s", $recordTime);
                /*Новая запись всегда сохранять*/
                $record = new TblControlPurchContent();                   
                if (empty ($record)) continue;  
                
                $record->headerRef     = $headRecord->id;
                $record->ownerOrgTitle = mb_substr(trim($parse[1]),0,250,'utf-8');
                $record->orgTitle      = mb_substr(trim($parse[2]),0,250,'utf-8');
                $record->orgINN        = mb_substr(trim($parse[3]),0,20 ,'utf-8');
                $record->ref1C         = mb_substr(trim($parse[4]),0,20 ,'utf-8');
                $record->purchDate     = $recordDate;
                $record->regRecord     = mb_substr(trim($parse[6]),0,150,'utf-8');
                $record->purchTitle    = mb_substr(trim($parse[7]),0,150,'utf-8');
                $record->purchCount    = $purchAmount;
                $record->purchEd       = mb_substr(trim($parse[9]),0,20,'utf-8');
                $record->purchDate     = $recordDate;
                $record->purchSum      = $purchSum;
                $record->orgKPP        = mb_substr(trim($parse[11]),0,20 ,'utf-8');
                $record->save();
             $updated++;  
          }//
                            
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;


// Собственники            
  $strSql = "UPDATE {{%control_purch_content}}, {{%control_sverka_filter}} SET 
      {{%control_purch_content}}.ownerOrgRef={{%control_sverka_filter}}.id where 
      {{%control_purch_content}}.ownerOrgTitle = {{%control_sverka_filter}}.owerOrgTitle
      and {{%control_purch_content}}.ownerOrgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     

/*Разнесение по статьям*/
 $this->purchClassify($headRecord->id);
  return $ret;
  }
          
/*Разнесение закупок по статьям*/     
public function  purchClassify($headRecord)
{
   $strSql = "SELECT mask, typeRef, useOrder FROM {{%control_purch_mask}} order BY useOrder ASC"; 
   $typeList = Yii::$app->db->createCommand($strSql)->queryAll();                                          
    
   if ($headRecord > 0)
   {
/*    $strSql = "UPDATE {{%control_purch_content}} set typeRef=0 WHERE headerRef=:headerRef";   
     Yii::$app->db->createCommand($strSql,
     [
       ':headerRef' => $headerRef,
     ])->execute();                                       
       */
    $strSql = "UPDATE {{%control_purch_content}} set typeRef=:typeRef
      WHERE purchTitle like :purchTitle and typeRef=0";
    for ($i=0; $i<count($typeList); $i++ )
    {
     Yii::$app->db->createCommand($strSql,
     [
       ':typeRef'   => $typeList[$i]['typeRef'],
       ':purchTitle' => $typeList[$i]['mask'],
     ])->execute();                                       
    }   
   }
   else {
    $strSql = "UPDATE {{%control_purch_content}} set typeRef=0";   
    Yii::$app->db->createCommand($strSql)->execute();                                       
    
    $strSql = "UPDATE {{%control_purch_content}} set typeRef=:typeRef
      WHERE purchTitle like :purchTitle and typeRef=0";
    for ($i=0; $i<count($typeList); $i++ )
    {
     Yii::$app->db->createCommand($strSql,
     [
       ':typeRef'   => $typeList[$i]['typeRef'],
       ':purchTitle' => $typeList[$i]['mask'],
     ])->execute();                                       
    }   
   }    
}
   

/********************/     
/*****************************************/
/********** Прибыль - рентабельность *****/     
/*****************************************/          
/* */
public function loadProfitData ($startRow, $syncTime)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();     

         $sD=date("dmY",$syncTime);      
         $eD=date("dmY",$syncTime+24*3600);           
         
          /*Load data*/
          $url = $this->getCfgValue(32);          
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;          
//$ret['loadurl'][] =$loadurl;                         
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

          $isUpdated = 0;        
          $nC = count($content);
          $syncDate = date('Y-m-d',$syncTime);
          
              /*Заголовок*/      
          $headRecord = new TblProfitHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = 0;
          $headRecord->save();
          
          for ($i;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
               $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 12) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     
                                                                                              
               $goodAmount    =  (float)str_replace(',', '.',$parse[7]);    
               $sellPrice     =  (float)str_replace(',', '.',$parse[8]);    
               $initPrice     =  (float)str_replace(',', '.',$parse[9]);    
               $profit        =  (float)str_replace(',', '.',$parse[10]);    
               $profitability =  (float)str_replace(',', '.',$parse[11]);    
                
               $recordTime= strtotime(mb_substr($parse[3],1));
               $recordDate=date("Y-m-d H:i:s", $recordTime);
                /*Новая запись всегда сохранять*/
                $record = new TblProfitContent();                   
                if (empty ($record)) continue;  
                
                $record->headerRef     = $headRecord->id;
                $record->ownerOrgTitle = mb_substr(trim($parse[1]),0,250,'utf-8');
                $record->regRecord     = mb_substr(trim($parse[2]),0,150,'utf-8');
                $record->recordDate    = $recordDate;
                $record->recordNumber  = mb_substr(trim($parse[4]),0,75,'utf-8');
                $record->goodTitle     = mb_substr(trim($parse[5]),0,150,'utf-8');
                $record->goodEd        = mb_substr(trim($parse[6]),0,20,'utf-8');
                $record->goodAmount    = $goodAmount;
                $record->sellPrice     = $sellPrice;
                $record->initPrice     = $initPrice;
                $record->profit        = $profit;
                $record->profitability = $profitability;                
                $record->save();
             $updated++;  
          }//
                            
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;

/*Обновим ссылки*/

    $strSql = "INSERT INTO {{%control_sverka_filter}} (owerOrgTitle)
    (SELECT DISTINCT ownerOrgTitle from {{%profit_content}} as a left join 
    {{%control_sverka_filter}} as b on b.owerOrgTitle = a.ownerOrgTitle where b.id is null)";
    Yii::$app->db->createCommand($strSql) ->execute();  
    
    
    $strSql = "UPDATE {{%profit_content}} as a left join  {{%control_sverka_filter}} as b
    on b.owerOrgTitle = a.ownerOrgTitle set a.ownerOrgRef = b.id where a.ownerOrgRef = 0
    and b.id is not null";
    Yii::$app->db->createCommand($strSql) ->execute();  
     return $ret;
     }
     
/********************/ 
/*****************************************/
/********** Движение денежных средств ****/     
/*****************************************/          

     public function loadBankOpData ($syncTime)
     {
          mb_internal_encoding("UTF-8");
          //$res=array();

/*
Получим данные из 1с
http://a0202654.xsph.ru/rik/web/index.php?r=/bank/operator/sync-bank-operation&sd=16062019&ed=18062019
*/          

         $sD=date("dmY",$syncTime);      
         $eD=date("dmY",$syncTime+24*3600);           

          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 31')->queryScalar();
          $url.="1"."&sd=".$sD."&ed=".$eD;
                    
          
          $page = $this->get_web_page( $url);               
          
          $content = mb_split('\r\n', $page['content'] );          

          
          $parse = str_getcsv($content[0],",");     
          $rowNum=$parse[0];
                  
          $n=count($content);

          /*Заполним заголовок*/
          $syncDate=date("Y-m-d H:i:s");                            
          
          $header = new TblBankOpHeader();
          $header->syncDateTime = $syncDate;
          $header->onDate = date('Y-m-d', $syncTime);
          $header->save();
          
          for ($i=1; $i<$n;$i++ )
          {
            $parse = str_getcsv($content[$i],",");     
            
            if (count($parse) < 12) {continue;} /*неполная запись*/
               
               /*Сумма*/               
            $summ=(float)str_replace(',', '.',$parse[10]);            
               /*дата регистратора*/
            $parse[5] = preg_replace("/[^(0-9.)]/","",$parse[5]);
            $regDate= date("Y-m-d", strtotime($parse[5]));
               
               /*дата сделки*/
            $parse[8] = preg_replace("/[^(0-9.)]/","",$parse[8]);
            $opDate= date("Y-m-d", strtotime($parse[8]));
            if(strtotime($opDate)< 1000) $opDate = $regDate;
               
            $record = new TblBankOpContent();
            $record->ownerTitle  = mb_substr($parse[1],0,250,'utf-8');
            $record->orgTitle    = mb_substr($parse[2],0,250,'utf-8');
            $record->orgINN      = mb_substr($parse[3],0,20,'utf-8');
            $record->regNote     = mb_substr($parse[4],0,250,'utf-8');
            $record->regDate     = $regDate;
            $record->regNum      = mb_substr($parse[6],0,20,'utf-8');
            $record->operationNote = mb_substr($parse[7],0,250,'utf-8');
            $record->operationDate = $opDate;
            $record->operationNum  = mb_substr($parse[9],0,20,'utf-8');
            $record->recordSum     = $summ;
            $record->orgKPP        = mb_substr($parse[11],0,20,'utf-8');
            $record->article       = mb_substr($parse[12],0,250,'utf-8');
            $record->refBankOpHeader = $header->id;
            $record->save();
         }

// логируем         

  Yii::$app->db->createCommand("INSERT INTO {{%log}} (refUser,actionType,actionText) VALUES (:refUser, 11, 'Синхронизация операций по счету с 1С') ", 
  [':refUser' => 0]) ->execute();       
     
//выбираем уникальные     
  $strSql = "INSERT INTO {{%bank_operation}} ( ownerTitle,  orgTitle, orgINN, regNote, regDate, regNum, operationNote, operationDate, 
  operationNum, recordSum, orgKPP, article) ( SELECT DISTINCT a.ownerTitle,  a.orgTitle, a.orgINN, a.regNote, a.regDate, a.regNum, 
  a.operationNote, a.operationDate, a.operationNum, a.recordSum, a.orgKPP, a.article
  from {{%bank_op_content}} as a left join {{%bank_operation}} as b 
  on (a.orgINN=b.orgINN and a.regNum=b.regNum and a.regDate=b.regDate and  a.recordSum= b.recordSum)  where b.id is null )";     
  Yii::$app->db->createCommand($strSql)->execute();     
         
//связываем  
  $strSql = "UPDATE {{%bank_op_content}} as a1
  left join {{%bank_operation}} as b1 
  on (a1.orgINN=b1.orgINN and a1.regNum=b1.regNum and a1.regDate=b1.regDate and  a1.recordSum= b1.recordSum)  
  SET a1.refOperation = b1.id
  where b1.id is not null and a1.refOperation = 0 ";
  Yii::$app->db->createCommand($strSql)->execute();                       
   
  $strSql = "INSERT INTO rik_bank_op_article (article) ( SELECT DISTINCT a.article from 
        {{%bank_operation}} as a left join  {{%bank_op_article}} as b on b.article = a.article
            where b.id is null)";
  Yii::$app->db->createCommand($strSql)->execute();                         
    
// Статьи    
  $strSql = "UPDATE {{%bank_operation}} as a left join  {{%bank_op_article}} as b
            on b.article = a.article set a.articleRef = b.id where a.articleRef = 0
            and b.id is not null";
  Yii::$app->db->createCommand($strSql)->execute();                         
            
// Собственники            
  $strSql = "UPDATE {{%bank_operation}}, {{%control_sverka_filter}} SET 
      {{%bank_operation}}.ownerOrgRef={{%control_sverka_filter}}.id where 
      {{%bank_operation}}.ownerTitle = {{%control_sverka_filter}}.owerOrgTitle
      and {{%bank_operation}}.ownerOrgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     
   
    return    $header->id;     
   }     
     


    
/********************/     
/********************/     
/********************/     
/********************/     
/********************/     
/********************/     



/*****************************************/
/**********  Счета ***********************/     
/*****************************************/          
/********************/     
/* Грузим сверку по банковским счетам*/
public function loadBankData($startRow, $syncTime)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();    
          
         $session = Yii::$app->session;          
         $session->open();            
         $sD=date("dmY",$syncTime);      
         $eD=date("dmY",$syncTime+24*3600);           
         
          /*Load data*/
          $url = $this->getCfgValue(21);          
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;          

          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );


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
          
              $session->set('bankSyncTime', $syncTime);
              $session->set('bankAllRecords', $allRecords);
          }                    
          else
          {
              $syncTime   = $session->get('bankSyncTime');
              $allRecords = $session->get('bankAllRecords');
          }

          $isUpdated = 0;        
          $nC = count($content);
          $syncDate = date('Y-m-d',$syncTime);
          
              /*Заголовок*/      
          $curUser=Yii::$app->user->identity;
          $headRecord = new TblControlBankHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUser->id;
          $headRecord->save();
          
          for ($i;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
            $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 6) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     
                                                                                              
               $summ =  (float)str_replace(',', '.',$parse[6]);                         
               $usedOrgTitle = mb_substr(trim($parse[1]),0,250,'utf-8');               
                                               $cashType =0;
               if(trim($parse[2])=='Наличные') $cashType =1;
               $accountNumber = mb_substr(trim($parse[4]),0,50,'utf-8');                              
               $cashEd        = mb_substr(trim($parse[5]),0,20,'utf-8');                              
                               
                /*Новая запись всегда сохранять*/
                $record = new TblControlBank();   
                if (empty ($record)) continue;  
                $record->usedOrgTitle = $usedOrgTitle;
                $record->cashType      = $cashType;
                $record->bankAccount   = mb_substr(trim($parse[3]),0,150,'utf-8');
                $record->accountNumber = $accountNumber;
                $record->cashEd        = $cashEd;                   
                $record->cashSum       = $summ;                                            
                $record->syncDate      = $syncDate;                         
                $record->headerRef     = $headRecord->id;
                $record->save();
         
             $updated++;  
          }//
                            
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;

        /*Добавляем счета*/        
        $strSql = "INSERT INTO {{%control_bank_use}} (usedOrgTitle, bankAccount, accountNumber, cashType )
            SELECT DISTINCT a.usedOrgTitle, a.bankAccount, a.accountNumber, a.cashType FROM rik_control_bank as a
            left join rik_control_bank_use as b on (a.usedOrgTitle = b.usedOrgTitle and
            a.bankAccount = b.bankAccount and a.accountNumber = b.accountNumber) where b.id is null ";
          
        Yii::$app->db->createCommand($strSql) ->execute();      

        /*Вяжем счета и записи*/        
        $strSql = "UPDATE rik_control_bank as a left join rik_control_bank_use as b on  
        (a.usedOrgTitle = b.usedOrgTitle and a.bankAccount = b.bankAccount and a.accountNumber = b.accountNumber)
        SET a.useRef = b.id where a.useRef = 0";
            
       Yii::$app->db->createCommand($strSql) ->execute();      
        
     return $ret;
     }
     
/********************/     
/*****************************************/
/**********  Сверка долга  ***************/     
/*****************************************/          
/********************/     
/* Грузим сверку */
public function loadSverkaData($startRow, $syncTime)
     {
        mb_internal_encoding("UTF-8");          
        $res=array();     
        $session = Yii::$app->session;          
        $session->open();
        $syncDate=date("dmY",$syncTime);     
        $sD=date("dmY",$syncTime);      
        $eD=date("dmY",$syncTime+24*3600);           
    
    
    /*Список активных фильтраций*/    
       $fltArray=array();    
       $list = Yii::$app->db->createCommand(
                      "SELECT id, owerOrgTitle FROM {{%control_sverka_filter}} order by id")
                         ->queryAll();                    
           
           for($i=0; $i < count ($list); $i++)
               {                
                    $orgTitle=$list[$i]['owerOrgTitle'];
                    $fltArray[$orgTitle]=$list[$i]['id'];                                                            
               }          
        unset ($list);          
                    
    

       /*Список контрагентов*/    
       $useArray=array();    
       $list = Yii::$app->db->createCommand(
                      "SELECT id, fltRef, orgINN, orgKPP FROM {{%control_sverka_dolga_use}} order by id")
                         ->queryAll();                    
           
           for($i=0; $i < count ($list); $i++)
               {                
                    $orgINN=$list[$i]['orgINN'];
                    $orgKPP=$list[$i]['orgKPP'];
                    $fltRef=$list[$i]['fltRef'];
                    $useArray[$fltRef][$orgINN][$orgKPP]=$list[$i]['id'];                                                            
               }          
        unset ($list);          
                    

    
                              
          /*Load data*/
          $url = $this->getCfgValue(20);    
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );

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
              $session->set('sverkaSyncTime', $syncTime);
              $session->set('sverkaAllRecords', $allRecords);
          }                    
          else
          {
              $allRecords = $session->get('sverkaAllRecords');
          }
          

          $isUpdated = 0;        
          $nC = count($content);
          $onDate = date('Y-m-d',$syncTime);        
          
              /*Заголовок*/      
          $curUser=Yii::$app->user->identity;
          $headRecord = new TblControlSverkaHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = $onDate;
          $headRecord->userRef = $curUser->id;
          $headRecord->save();
            
          for ($i;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
               
             $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                                  
               if (count($parse) < 8) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     

                              
               $usedOrgTitle = trim($parse[1]);
               $dogType =0;
               if    (trim($parse[2]) =='С покупателем') $dogType =1;
               elseif(trim($parse[2]) =='С поставщиком') $dogType =2;
               $orgTitle     = trim($parse[3]);                                                   
               $summ =  (float)str_replace(',', '.',$parse[5]);
               if (!empty ($parse[6])) $orgINN = trim($parse[6]);
                                 else  $orgINN = "-";
               if (!empty ($parse[7])) $orgKPP = trim($parse[7]);
                                 else  $orgKPP = "-";                                     

                                 
              if (!isset($fltArray[$usedOrgTitle])) 
              {
                $fltRecord= new TblControlSverkaFilter();
                $fltRecord->owerOrgTitle = $usedOrgTitle;
                $fltRecord->save();
                $fltArray[$usedOrgTitle]=$fltRecord->id;
              }
              $fltRef = $fltArray[$usedOrgTitle];              

              if (!isset($useArray[$fltRef][$orgINN][$orgKPP])) 
              {
                $useRecord= new TblControlSverkaUse();
                $useRecord->orgTitle = $orgTitle;
                $useRecord->orgINN = $orgINN;
                $useRecord->orgKPP = $orgKPP;
                $useRecord->fltRef = $fltRef;
                $useRecord->save();
                $useArray[$fltRef][$orgINN][$orgKPP]=$useRecord->id;
              }
              $useRef = $useArray[$fltRef][$orgINN][$orgKPP];              
                             
            $isUpdated = 0;
                /*Новая запись всегда сохранять*/
                $record = new TblControlSverkaDolga();   
                if (empty ($record)) continue;  
                $isUpdated = 1;
                $record->usedOrgTitle = $usedOrgTitle;
                $record->dogType      = $dogType;
                $record->orgTitle     = $orgTitle;
                $record->managerFIO   = trim($parse[4]);
                $record->orgINN       = $orgINN;                   
                $record->orgKPP       = $orgKPP;                            
                $record->balanceSum   = $summ;                         
                $record->syncDate     = $onDate;
                $record->headerRef    = $headRecord->id;                
                $record->useRef       = $useRef;                
                $record->save();
         
             $updated+=$isUpdated;  
          }//
                                     
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;
 
 

    /*$strSql=" INSERT INTO {{%control_sverka_filter}} (owerOrgTitle) SELECT DISTINCT a.usedOrgTitle   from {{%control_sverka_dolga}} as a 
    left join  {{%control_sverka_filter}} as b on a.usedOrgTitle = b.owerOrgTitle where b.id is null and {{%control_sverka_dolga}}.headerRef =" ;    
     Yii::$app->db->createCommand($strSql) ->execute();     */

 
    /*Выделяем новые группы и фиксим связи*/    
    $strSql="UPDATE {{%control_sverka_dolga}},{{%control_sverka_dolga_use}} SET 
            {{%control_sverka_dolga}}.isInUse = {{%control_sverka_dolga_use}}.isInUse,
            {{%control_sverka_dolga}}.isBlack = {{%control_sverka_dolga_use}}.isBlack,
            {{%control_sverka_dolga}}.isOther = {{%control_sverka_dolga_use}}.isOther,
            {{%control_sverka_dolga}}.isService = {{%control_sverka_dolga_use}}.isService,
            {{%control_sverka_dolga}}.isBank = {{%control_sverka_dolga_use}}.isBank             
             where  {{%control_sverka_dolga}}.useRef = {{%control_sverka_dolga_use}}.id;";              
    Yii::$app->db->createCommand($strSql)->execute();    
        
    /*привяжемся к организации все что с последней синхронизации*/           
     /*по сочетанию инн и кпп*/
     $strSql=" update {{%control_sverka_dolga_use}} as a, {{%orglist}} as b set a.orgRef = b.id 
          where a.orgINN = b.schetINN AND a.orgKPP = b.orgKPP AND (a.orgRef =0 or a.orgRef IS NULL) and b.[[isReject]] = 0";      //AND supplyDate >= :supplyDate";           
     Yii::$app->db->createCommand($strSql) ->execute();     
          
     /*где кпп не задан*/
     $strSql=" update {{%control_sverka_dolga_use}} as a, {{%orglist}} as b set a.orgRef = b.id 
          where a.orgINN = b.schetINN AND (a.orgRef =0 or a.orgRef IS NULL)  and b.[[isReject]] = 0 ";      
     Yii::$app->db->createCommand($strSql) ->execute();     
   
     /*нет инн и кпп */
     $strSql=" update {{%control_sverka_dolga_use}} as a, {{%orglist}} as b set a.orgRef = b.id 
          where a.orgTitle = b.title AND (a.orgRef =0 or a.orgRef IS NULL)  and b.[[isReject]] = 0 and a.orgINN = '-'  ";      
     Yii::$app->db->createCommand($strSql) ->execute();     

     
     
                             
     return $ret;
     }

/*****************************************/
/**********  Склад ***********************/     
/*****************************************/          
     public function syncSclad($startRow, $syncTime)
     {
          mb_internal_encoding("UTF-8");
          $res=array();
    
        $session = Yii::$app->session;          
        $session->open();

    /*Список активных складов*/    
       $useArray=array();    
       $scladArray=array();       
       $list = Yii::$app->db->createCommand(
                      "SELECT id, orgTitle, scladTitle FROM {{%ware_use}} order by id")
                         ->queryAll();                    
           
           for($i=0; $i < count ($list); $i++)
               {                
                    $orgTitle=$list[$i]['orgTitle'];
                    $scladTitle =$list[$i]['scladTitle'];
                    $scladArray[$orgTitle][$scladTitle]=$list[$i]['id'];                                                            
               }          
        unset ($list);          
                    
     /*Получим данные из 1с*/          
          $sD=date("dmY",$syncTime);      
          $eD=date("dmY",$syncTime+0*3600);                                     
          /*Load data*/
          $url = $this->getCfgValue(10);    
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;
               
          $page = $this->get_web_page( $loadurl);               
          $content = mb_split('\r\n', $page['content'] );          

//print_r ($content);

    /*Заголовок*/      
          $curUser=Yii::$app->user->identity;
          $headRecord = new TblWareHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUser->id;
          $headRecord->save();
          
          
        if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[0],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;                      
              $session->set('scladSyncTime', $syncTime);
              $session->set('scladAllRecords', $allRecords);
          }                    
          else
          {
              $allRecords = $session->get('sverkaAllRecords');
          }
             
          
   /*для всех позиций склада*/                  
   $updated=0;
   $lastLoaded = 0;
   $loadCounter = 0;
   $parse = str_getcsv($content[0],",");     
   $allRecords = $parse[0];    
   $N=count($content);
          for ($i=1; $i<$N;$i++ )
          {
              
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
             
            $lastLoaded =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                                  

               if (count($parse) < 9) {continue;} /*не полная запись*/

          /*Сохранить пару организация-склад*/     
              $orgTitle   = trim($parse[1]);
              $scladTitle = trim($parse[2]);              
              $useRef=0;
              $flgAddSclad=0;
              if (!isset($scladArray[$orgTitle][$scladTitle])) 
              {
                $useRecord= new TblWareUse();
                $useRecord->orgTitle = $orgTitle;
                $useRecord->scladTitle = $scladTitle;
                $useRecord->save();
                $scladArray[$orgTitle][$scladTitle]=$useRecord->id;
              }
              $useRef = $scladArray[$orgTitle][$scladTitle];              
               /*К-во*/
               $parse[7] = preg_replace("/[^(0-9.,\-)]/","",$parse[7]);
               $goodAmount=(float)str_replace(',', '.',$parse[7]);            
               /*Цена*/
               $parse[8] = preg_replace("/[^(0-9.,)-]/","",$parse[8]);
               $initPrice=(float)str_replace(',', '.',$parse[8]);             
               /*сумма по себестоимости*/
               $parse[9] = preg_replace("/[^(0-9.,)-]/","",$parse[9]);
               $sumPrice=(float)str_replace(',', '.',$parse[9]); 
            
               /*Создадим товар*/               
                 $scladRecord=     new TblWareContent();
                     $scladRecord->orgTitle   = $orgTitle;
                     $scladRecord->scladTitle = $scladTitle;
                     $scladRecord->articul    = trim($parse[3]);
                     $scladRecord->grpGood    = trim($parse[4]);
                     $scladRecord->goodTitle  = trim($parse[5]);
                     $scladRecord->goodEd     = trim($parse[6]);
                     $scladRecord->goodAmount = $goodAmount;
                     $scladRecord->initPrice  = $initPrice;
                     $scladRecord->headerRef  = $headRecord->id;
                     $scladRecord->useRef     = $useRef;
                 $scladRecord->save();
                 $updated++;
            }
                    
    $strSql="UPDATE {{%config}} set keyValue = NOW() where id = 110";              
    Yii::$app->db->createCommand($strSql)->execute();
    
    /*Выделяем новые группы и фиксим связи*/    
    $strSql="UPDATE {{%ware_content}},{{%ware_use}} SET {{%ware_content}}.isActive = {{%ware_use}}.isInUse 
             where  {{%ware_content}}.useRef = {{%ware_use}}.id;";              
    Yii::$app->db->createCommand($strSql)->execute();    

    $ret['allRecords'] = $allRecords;
    $ret['lastLoaded'] = $lastLoaded;
    $ret['updatedRecord'] = $updated;

    return      $ret;     
     }     
     
          
/****************/

    
/*****************************************/
/********** Данные по бух счетам *****/     
/*****************************************/          
/* */
public function loadBuhSchetData($st, $en)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();     
         $session = Yii::$app->session;          
         $session->open();
         $sD=date("dmY",$st);      
         $eD=date("dmY",$en);           
         
          /*Load data*/
          $url = $this->getCfgValue(34);          
          $loadurl =  $url."&sd=".$sD."&ed=".$eD;          
//$ret['loadurl'][] =$loadurl;                         
//print_r($loadurl );
//return;
//https://s3.arenda1c.ru/buh_e7981678a01e10d025b47bd0b730da1c/hs/report_orders?report=2&sd=23102019&ed=31102019
//https://s3.arenda1c.ru/buh_e7981678a01e10d025b47bd0b730da1c/hs/report_orders?report=2&sd=01112019&ed=30102019
          $page = $this->get_web_page($loadurl);     
          $content = mb_split('\r\n', $page['content'] );          
//$ret['content'][] =$content;                                 

//print_r($content);
          $err=array();     
          $lastLoaded=0;          
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updated = 0;

          /*Первый блок данных*/
               
          $isUpdated = 0;        
          $nC = count($content);
         
              /*Заголовок*/      
          $curUser=Yii::$app->user->identity;
          $headRecord = new TblBuhSchetHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->stDate = date("Y-m-d", $st);
          $headRecord->enDate   = date("Y-m-d", $en);          
          $headRecord->userRef = $curUser->id;
          $headRecord->save();
          
          for ($i=1;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");                                       
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 9) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     
                                                                
               for($j=3;$j<9;$j++)
               {
                   $parse[$j]=str_replace(',', '.',$parse[$j]);    
                   $parse[$j]=preg_replace("/\s+/iu","",$parse[$j]);
               
               }                                                                        
               $sndt    =  (float)str_replace(',', '.',$parse[3]);    
               $snkt    =  (float)str_replace(',', '.',$parse[4]);    
               $obdt    =  (float)str_replace(',', '.',$parse[5]);    
               $obkt    =  (float)str_replace(',', '.',$parse[6]);    
               $skdt    =  (float)str_replace(',', '.',$parse[7]);
               $skkt    =  (float)str_replace(',', '.',$parse[8]);        
                
                /*Новая запись всегда сохранять*/
                $record = new TblBuhSchetContent();                   
                if (empty ($record)) continue;  
                $record->headerRef     = $headRecord->id;
                $record->schet         = mb_substr(trim($parse[0]),1,20,'utf-8');
                $record->subSchet      = mb_substr(trim($parse[1]),1,20,'utf-8');
                $record->subSubSchet   = mb_substr(trim($parse[2]),1,20,'utf-8');
                $record->SNDT          = $sndt;
                $record->SNKT          = $snkt;
                $record->OBDT          = $obdt;
                $record->OBKT          = $obkt;
                $record->SKDT          = $skdt;
                $record->SKKT          = $skkt;
                $record->save();
             $updated++;  
          }//
                            
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;

 
        
        $strSql="INSERT INTO {{%buh_schet_row}} (schet, subSchet, subSubSchet)
        ( Select   a.schet, a.subSchet, a.subSubSchet from {{%buh_schet_content}} as a
        left join {{%buh_schet_row}}  as b on (a.schet = b.schet AND a.subSchet = b.subSchet AND a.subSubSchet = b.subSubSchet)
        where b.id is null and a.headerRef = ".$headRecord->id.") ;";
        Yii::$app->db->createCommand($strSql)->execute();

        
        $strSql="UPDATE {{%buh_schet_content}} as a,  {{%buh_schet_row}}  as b SET a.rowRef = b.id 
                WHERE a.schet = b.schet AND a.subSchet = b.subSchet AND a.subSubSchet = b.subSubSchet
                AND a.rowRef = 0;"; 
        Yii::$app->db->createCommand($strSql)->execute();

                
     return $ret;
     }
     
/********************/     

public function startCheck($dt)
{
        
     $checkdate = date("Y-m-d", $dt);
     
     $record= TblBuhStatHeader::findOne([
        'checkDate'  => $checkdate,          
     ]);

     if (empty($record))
     {
       $record = new TblBuhStatHeader();
       $record->checkDate  = $checkdate;                    
       $record->isSynced = 1;
       $record->isChecked = 1;
       $record->syncDateTime = date("Y-m-d H:i");
     }
     else{
       $record->isSynced = 1;
       $record->isChecked = 1;
       $record->syncDateTime = date("Y-m-d H:i");
     }
     $record->editor = 0;    
     $record->loadData = 0;//сбрасываем - пусть перечитает
     $record->save();
     
     return  true;
}
 


/*
    Формирует список дат для пересинхронизации
*/
public function getBuhStatSyncDate()
{
    $strSql="SELECT id, checkDate FROM {{%buh_stat_header}}
                WHERE isChecked=1 and isMonth = 1 AND isSynced = 0;"; 
    $list  =Yii::$app->db->createCommand($strSql)->queryAll();  


    
    for ($i=0; $i < count($list); $i++)
    {
     $tm = strtotime($list[$i]['checkDate']);    
     $this->syncDates[$i]['st'] = $tm;
     $ed = date('Y-m-t', $tm);   
     $this->syncDates[$i]['et'] = strtotime($ed);   
    }


    //сбросим и поставим на пересчет
    $strSql="UPDATE {{%buh_stat_header}} SET isSynced = 1, loadData=0 
                   WHERE  isChecked=1 and isMonth = 1 AND isSynced = 0;";                 
    Yii::$app->db->createCommand($strSql)->execute();
     
     return  true;
}

     
/****************/     


/***********/
/*use app\models\TblWareGrp;
use app\models\TblWareProducer;
use app\models\TblWareList;
*/
//импортируем список товара из файла
  public function importWareList($fname)
  {
  
   if (($fr = fopen($fname, "r")) == false) {echo "No file"; return false;}
   
    $data = fgetcsv($fr, 2048, ",");//skip first
   
    while (($parse = fgetcsv($fr, 2048, ",")) !== FALSE) {
        $num = count($data);
        if ($num < 4) continue;
        $grpRecord= TblWareGrp::findOne(['wareGrpTitle' => $parse[4],]);
        if (empty($grpRecord)) {
            $grpRecord = new TblWareGrp();
            if (empty($grpRecord)) {"echo WareGrp"; return false;}
            $grpRecord -> wareGrpTitle = $parse[4];
            $grpRecord -> save();
            }
        
        $prodRecord= TblWareProducer::findOne(['wareProdTitle' => $parse[1],]);
        if (empty($prodRecord)) {
            $prodRecord = new TblWareProducer();
            if (empty($prodRecord)) {"echo WareProducer"; return false;}
            $prodRecord -> wareProdTitle = $parse[1];
            $prodRecord -> save();
            }
        
        $record = TblWareList::findOne(['wareTitle' => $parse[0],]);
        if (empty($record)) {
            $record = new TblWareList();
            if (empty($record)) {"echo WareList"; return false;}
            $record -> wareTitle = $parse[0];
         }   
            $record -> grpRef = $grpRecord -> id;
            $record -> producerRef = $prodRecord ->id;           
            $parse[2] = preg_replace("/[^(0-9.,\-)]/","",$parse[2]);
            $record -> wareDensity=(float)str_replace(',', '.',$parse[2]);            
            
            $record -> wareFormat = $parse[3];
            $record -> isProduction = 0;
            $record -> isActive = 1;
            $record -> save();
        
    }
    
    fclose($fr);   
    return true;
  }
  
//импортируем список продукции из файла
 
  public function importProdList($fname)
  {
  
   if (($fr = fopen($fname, "r")) == false) return false;
   
    $data = fgetcsv($fr, 2048, ",");//skip first
   
    while (($parse = fgetcsv($fr, 2048, ",")) !== FALSE) {

            $num = count($data);
        if ($num < 4) continue;
            
    //Ищем Товар-сырье
        $srcRecord = TblWareList::findOne(['wareTitle' => $parse[3],]);
        if (empty($srcRecord)) {
            $srcRecord = new TblWareList();
            if (empty($srcRecord)) return false;
            $srcRecord -> wareTitle = $parse[3];
            $srcRecord -> isProduction = 0;
            $srcRecord -> isActive = 0;
            $srcRecord -> save();
        }

    //Ищем Товар-продукцию
        $resRecord = TblWareList::findOne(['wareTitle' => $parse[2],]);
        if (empty($resRecord)) {
            $resRecord = new TblWareList();
            if (empty($resRecord)) return false;
            $resRecord -> wareTitle = $parse[2];
            $resRecord -> isProduction = 1;
            $resRecord -> isActive = 1;
            $resRecord -> save();
        }
        
     //Ищем связь продукция - сырье
        $lnkRecord = TblWareProdLnk::findOne([
        'resRef' => $resRecord->id,
        'srcRef' => $srcRecord->id,
        ]);
        if (empty($lnkRecord)) {
            $lnkRecord = new TblWareProdLnk();
            if (empty($lnkRecord)) return false;
            $lnkRecord -> resRef = $resRecord->id;
            $lnkRecord -> srcRef = $srcRecord->id;
            }
           $parse[4] = preg_replace("/[^(0-9.,\-)]/","",$parse[4]);
           $lnkRecord -> cost=(float)str_replace(',', '.',$parse[4]);            
           $lnkRecord -> save();
        
     //Ищем номенклатуру на складе и прописываем ей сопоставление со списком товара
     
     //для сырья
        $scladRecord= TblWarehouse::findOne(['title' => $parse[0],]);
        if (empty($scladRecord)) continue;
        $scladRecord -> wareListRef =  $srcRecord -> id;
        $scladRecord -> save();
        
     //для продукции
        $scladRecord= TblWarehouse::findOne(['title' => $parse[1],]);
        if (empty($scladRecord)) continue;
        $scladRecord -> wareListRef =  $resRecord -> id;
        $scladRecord -> save();
        
    }
    
    fclose($fr);   
    return true;
  }

  
public function classifyWare()
  {
        
      /*Тип*/  
      $strSql="SELECT id, wareTypeTemplate FROM {{%ware_type}} ORDER BY detectOrder ASC";
      $typelist=  Yii::$app->db->createCommand($strSql)->queryAll();                        
      for($i=0;$i<count($typelist); $i++) 
      {
          $strSql="UPDATE {{%warehouse}} SET wareTypeRef = :grpRef where wareTypeRef = 0 AND title LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $typelist[$i]['id'],
          ':wareTitle' => $typelist[$i]['wareTypeTemplate'],          
          ])->execute();                        
          
          
          $strSql="UPDATE {{%ware_list}} SET wareTypeRef = :grpRef where wareTypeRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $typelist[$i]['id'],
          ':wareTitle' => $typelist[$i]['wareTypeTemplate'],          
          ])->execute();                        
          
          $strSql="UPDATE {{%ware_names}} SET wareTypeRef = :grpRef where wareTypeRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $typelist[$i]['id'],
          ':wareTitle' => $typelist[$i]['wareTypeTemplate'],          
          ])->execute();                        

          
      }
          
     /*Вид*/
      $strSql="SELECT id, wareGrpTemplate FROM {{%ware_grp}} ORDER BY detectOrder ASC";
      $grplist=  Yii::$app->db->createCommand($strSql)->queryAll();                        
      for($i=0;$i<count($grplist); $i++) 
      {
          $strSql="UPDATE {{%warehouse}} SET grpRef = :grpRef where grpRef = 0 AND title LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $grplist[$i]['id'],
          ':wareTitle' => $grplist[$i]['wareGrpTemplate'],          
          ])->execute();                        
          
          
          $strSql="UPDATE {{%ware_list}} SET grpRef = :grpRef where grpRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $grplist[$i]['id'],
          ':wareTitle' => $grplist[$i]['wareGrpTemplate'],          
          ])->execute();                        
          
          
          $strSql="UPDATE {{%ware_names}} SET wareGrpRef = :grpRef where wareGrpRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $grplist[$i]['id'],
          ':wareTitle' => $grplist[$i]['wareGrpTemplate'],          
          ])->execute();               
      }
      
      /*Производитель*/
      $strSql="SELECT id, wareProdTemplate FROM {{%ware_producer}} ORDER BY detectOrder ASC";
      $prodlist=  Yii::$app->db->createCommand($strSql)->queryAll();                        
      for($i=0;$i<count($prodlist); $i++) 
      {
          $strSql="UPDATE {{%warehouse}} SET producerRef = :grpRef where producerRef = 0 AND title LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $prodlist[$i]['id'],
          ':wareTitle' => $prodlist[$i]['wareProdTemplate'],          
          ])->execute();                        
          
          
          $strSql="UPDATE {{%ware_list}} SET producerRef = :grpRef where producerRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $prodlist[$i]['id'],
          ':wareTitle' => $prodlist[$i]['wareProdTemplate'],          
          ])->execute();                        
          
          
          $strSql="UPDATE {{%ware_names}} SET producerRef = :grpRef where producerRef = 0 AND wareTitle LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':grpRef' => $prodlist[$i]['id'],
          ':wareTitle' => $prodlist[$i]['wareProdTemplate'],          
          ])->execute();                        
          
      }

      //$strSql="UPDATE {{%warehouse}} SET producerRef = 1 where producerRef = 0 AND title LIKE '%*%'";
      //Yii::$app->db->createCommand($strSql)->execute();                        
         
      $strSql="UPDATE {{%ware_list}} SET producerRef = 1 where producerRef = 0 AND isProduction = 1";
      Yii::$app->db->createCommand($strSql)->execute();                        
      
      
     /*  $edList = Yii::$app->db->createCommand('Select id, edTemplate from {{%ware_ed}} ORDER BY detectOrder ASC')                    
                    ->queryAll();         
                    
      for($i=0;$i<count($edList); $i++) 
      {
          $strSql="UPDATE {{%warehouse}} SET wareEdRef = :edRef where wareEdRef = 0 AND ed LIKE :wareTitle";
          Yii::$app->db->createCommand($strSql,[
          ':edRef' => $edList[$i]['id'],
          ':wareTitle' => $edList[$i]['edTemplate'],          
          ])->execute();     
          
      }*/
             
  }  

  
public function parseWare()
  {  
   $list = Yii::$app->db->createCommand('Select id, edTitle from {{%ware_ed}}')                    
                    ->queryAll();                
        $edList = ArrayHelper::map($list, 'id', 'edTitle');      

   $list = Yii::$app->db->createCommand('Select id, wareTypeName, wareTypeTemplate from {{%ware_type}}')                    
                    ->queryAll();                
        $typeList =  ArrayHelper::map($list, 'id', 'wareTypeName');      

   $list = Yii::$app->db->createCommand('Select id, wareGrpTitle from {{%ware_grp}}')                    
                    ->queryAll();                
        $grpList =  ArrayHelper::map($list, 'id', 'wareGrpTitle');      

   $listStatus = Yii::$app->db->createCommand('Select id, wareProdTitle from {{%ware_producer}}')                    
                    ->queryAll();                
        $prodList= ArrayHelper::map($list, 'id', 'wareProdTitle');      
 
  
      /*Все не распределенные*/  
      $strSql="SELECT id FROM {{%warehouse}} where wareListRef = 0";
      $list=  Yii::$app->db->createCommand($strSql)->queryAll();                        
      for($j=0;$j<count($list); $j++) 
      {
        $record= TblWarehouse::findOne($list[$j]['id']);
        if (empty($record) ) continue;        
        
      
         $parse = preg_split("/[\s+|\.\,]/",$record->title); //бьем по словам
         
       //  print_r($parse);
         
/*         continue;*/
         $N = count($parse); // число слов         
         $wareMap=[];
         for ($i=0;$i<$N;$i++) $wareMap[$i]=0; 
         
         for ($i=0;$i<$N;$i++) {
         
         /*формат*/
         if ($parse[$i] == "ф" ) 
         {
           $i++;
           while ($parse[$i] == "" && $i<$N)$i++;
           if ($i<$N)$record->wareFormat = $parse[$i];     
           continue;
         }
         /*плотность*/   
         if ($parse[$i] == "пл" && $i < $N) 
         {
           $i++;
           while ($parse[$i] == "" && $i<$N)$i++;
           if ($i<$N)$record->wareDensity = intval(preg_replace("/[г|р|\.]/u","",$parse[$i]));                
           continue;
         }
         /*листы*/            
         if (preg_match("/\d+л/",$parse[$i]))
         { 
         
           $record->warePack = preg_replace("/\(/","",$parse[$i]);                
           continue;
         }
         if ($parse[$i] == "л" && $i > 0) 
         {
         $k=0;
           while ($parse[$i-$k] == "" && $i>=0)$k++;
           $record->warePack = $parse[$i-$k];                
           continue;
         }
        }//сканируем
  //echo "\n";               
        
       $record->save();
      }
            
  }  
  

public function lnkWare()
 {  
 
    $strSql="UPDATE {{%warehouse}} as a, {{%ware_list}}  as b
            SET a.wareListRef =b.id
        where   a.wareListRef =0
        and a.wareTypeRef =b.wareTypeRef
        and a.grpRef =b.grpRef
        and a.producerRef =b.producerRef
        and a.wareDensity =b.wareDensity
        and a.wareFormat =b.wareFormat";
  
      Yii::$app->db->createCommand($strSql)->execute();                        
  
  }  
  

  public function importOrgList($fname)
  {
  
   if (($fr = fopen($fname, "r")) == false) return false;
   
    $parse = fgetcsv($fr, 2048, ",");//skip first
   
    while (($parse = fgetcsv($fr, 2048, ",")) !== FALSE) {
    
      for($j=0; $j<count($parse); $j++) {$parse[$j]=trim($parse[$j]);}
      for($j=count($parse); $j<15; $j++){$parse[]="";}               

      /*Выставим названия*/
      $orgINN = $parse[4];     
      if (empty($orgINN)) continue;
      
      $cnt = Yii::$app->db->createCommand("SELECT COUNT(id) from {{%orglist}} where orgINN =:orgINN",[':orgINN' =>$orgINN ])->queryScalar();                        
      
      if ($cnt > 1) continue; 
      if ($cnt == 1)   $orgRecord=  OrgList::findOne(['orgINN' => $orgINN]);
      else {
          $orgRecord= new OrgList();
          $orgRecord->schetINN = $orgINN;      
      }
      if (empty($orgRecord)) {
      echo "Problem with ".$orgINN."\n";
      continue; }    
        
      if (empty($orgRecord -> nadrazdel)) $orgRecord -> nadrazdel = mb_substr($parse[0],0,75,'utf-8'); 
      if ($parse[1] != '#N/A')
      if (empty($orgRecord -> razdel))    $orgRecord -> razdel    = mb_substr($parse[1],0,75,'utf-8');
      if (empty($orgRecord -> subrazdel)) $orgRecord -> subrazdel = mb_substr($parse[2],0,75,'utf-8');
      if (empty($orgRecord -> checkUrl))  $orgRecord -> checkUrl  = mb_substr($parse[2],0,250,'utf-8');

      $orgRecord->save();  
        
        /*Адрес*/
      $adressRecord = AdressList::findOne([
          'adress'  => $parse[7],
          'ref_org' => $orgRecord->id,
      ]);
      if (empty($adressRecord))
      {
          $adressRecord = new AdressList();
      }
      if(empty($adressRecord)) continue;
      
      $adressRecord->adress= $parse[7];
      if(empty($adressRecord->index))$adressRecord->index= preg_replace("/[\D]/","",$parse[9]); 
      if(empty($adressRecord->area))$adressRecord->area = $parse[10];
      if(empty($adressRecord->city))$adressRecord->city = $parse[11];
      if(empty($adressRecord->street))$adressRecord->street = $parse[12];
      if(empty($adressRecord->house))$adressRecord->house = $parse[13];
      if(empty($adressRecord->room))$adressRecord->room = $parse[14];
      if(empty($adressRecord->streetAdres))$adressRecord->streetAdres = $parse[12].", ".$parse[13].", ".$parse[14];
      $adressRecord->ref_org = $orgRecord->id;
      $adressRecord->save();      
    
     }    
    
  }

  
    
  /************End of model*******************/ 
 }
