<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;

use yii\db\Expression;

use app\models\TblProfitHeader;
use app\models\TblProfitContent;

use app\models\TblControlBankHeader;
use app\models\TblControlBank;

use app\models\TblControlSverkaHeader;
use app\models\TblControlSverkaDolga;
use app\models\TblControlSverkaUse;

use app\models\TblControlPurchHeader;
use app\models\TblControlPurchContent;

use app\models\TblBuhSchetContent;
use app\models\TblBuhSchetHeader;

use app\models\TblControlSaleHeader;
use app\models\TblControlSaleContent;

use app\models\TblWareHeader;
use app\models\TblWareContent;
use app\models\TblWareUse;
use app\models\TblWareList;
use app\models\TblOtvesList;

use app\models\TblTransportTarif;

/**
 * DataSync - синхронизация данных с 1C
 */
class DataSync extends Model
{
     
     public $syncDate = "";

     public $webSync = true;
     public $verbeouse = true;
    
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
         
        $curUserId = 0;
        if ($this->webSync )   
         {
           $session = Yii::$app->session;          
           $session->open();
           $curUser=Yii::$app->user->identity;
           $curUserId = $curUser->id;
         }
         
        
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


$strSql = " DELETE FROM {{%control_purch_content}} WHERE DATE(purchDate) = :syncDate";
Yii::$app->db->createCommand($strSql, [':syncDate' => date("Y-m-d",$syncTime)])->execute();  

          if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[$i],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;                      
            if ($this->webSync )   
            {
               $session->set('purchSyncTime', $syncTime);
               $session->set('purchAllRecords', $allRecords);
            }
          }                    
          else
          {
            if ($this->webSync )   
            {             
              $syncTime   = $session->get('purchSyncTime');
              $allRecords = $session->get('purchAllRecords');
            }  
          }

          $isUpdated = 0;        
          $nC = count($content);
          $syncDate = date('Y-m-d',$syncTime);
          
              /*Заголовок*/      

          $headRecord = new TblControlPurchHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUserId;
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
                $inTime= strtotime($parse[12]);
                $inDate=date("Y-m-d", $inTime);
                $record->inDate       = $inDate;
                $record->inNum       = mb_substr(trim($parse[13]),0,20,'utf-8');

                $record->save();
             $updated++;  
          }//
                            



// Собственники            
  $strSql = "UPDATE {{%control_purch_content}}, {{%control_sverka_filter}} SET 
      {{%control_purch_content}}.ownerOrgRef={{%control_sverka_filter}}.id where 
      {{%control_purch_content}}.ownerOrgTitle = {{%control_sverka_filter}}.owerOrgTitle
      and {{%control_purch_content}}.ownerOrgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     
    
    /*    
    INSERT INTO rik_warehouse(title, ed, initPrice)
    SELECT DISTINCT a.purchTitle, a.purchEd, purchSum/purchCount 
    from rik_control_purch_content as a  LEFT JOIN rik_warehouse as b on   (b.title = a.purchTitle and b.ed = a.purchEd)
    where
    typeRef = 1 and b.id is null 
        
    */
/*Разнесение по статьям*/
 $this->purchClassify($headRecord->id);

/*Вытащим уникальные*/
$strSql = " DELETE FROM {{%supplier_wares}} WHERE DATE(requestDate) = :syncDate";
Yii::$app->db->createCommand($strSql, [':syncDate' => date("Y-m-d",$syncTime)])->execute();                                     


$strSql = "
INSERT INTO {{%supplier_wares}}
(orgTitle, orgINN, orgKPP, refOrg, ref1C, requestDate, wareTitle, wareEd,  wareCount, wareSumm,
typeRef, wareRef, wareEdValueRef , wareCostValue , wareCostPrice , wareCostCount , wareCostAdd , isAdditionWare, inDate, inNum)
(
SELECT   a.orgTitle,   a.orgINN,   a.orgKPP,   a.orgRef,   a.ref1C,   a.purchDate,   a.purchTitle,   a.purchEd ,   a.purchCount ,
  a.purchSum ,   a.typeRef,   a.goodRef,   a.wareEdValueRef ,   a.wareCostValue ,   a.wareCostPrice ,   a.wareCostCount ,   a.wareCostAdd ,
  a.isAdditionWare,   a.inDate,   a.inNum
FROM   {{%control_purch_content}} as a
LEFT JOIN  {{%control_sverka_filter}}  as b on  a.ownerOrgRef = b.id
LEFT JOIN  {{%supplier_wares}} as c on 
(   a.purchTitle = c.wareTitle AND  a.ref1C  = c.ref1C AND  a.purchDate = c.requestDate AND   a.orgINN = c.orgINN )
where b.isFilter = 1 and c.id is null  AND DATE(a.purchDate) =:syncDate)";

    Yii::$app->db->createCommand($strSql, [':syncDate' => date("Y-m-d",$syncTime)])->execute();                                     

/*Создадим заголовок*/
$strSql = "
INSERT INTO {{%supplier_wares_header}}
(orgTitle, orgINN, orgKPP, ref1C, requestDate, inDate, inNum )
(SELECT DISTINCT
a.orgTitle, a.orgINN, a.orgKPP, a.ref1C, a.requestDate, a.inDate, a.inNum
from {{%supplier_wares}} as a
Left join {{%supplier_wares_header}} as b
on ( a.orgINN = b.orgINN AND a.ref1C = b.ref1C AND a.requestDate = b.requestDate )
where b.id is NULL AND DATE(a.requestDate) =:syncDate)
";
    Yii::$app->db->createCommand($strSql, [':syncDate' => date("Y-m-d",$syncTime)])->execute();                                     

/*и привяжем к нему содержимое*/
$strSql = " UPDATE {{%supplier_wares}} as a, {{%supplier_wares_header}} as b
SET a.refHeader = b.id where a.orgINN = b.orgINN
AND a.ref1C = b.ref1C AND a.requestDate = b.requestDate AND a.refHeader = 0 AND DATE(a.requestDate) =:syncDate";

    Yii::$app->db->createCommand($strSql, [':syncDate' => date("Y-m-d",$syncTime)])->execute();                                     

    
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;
         

     return $ret;
     }

     
     
/*****************************************/
/********** Реализация товара        *****/     
/*****************************************/          
/* */
public function loadSaleData ($startRow, $syncTime)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();     
         
         $sD=date("dmY",$syncTime);      
         $eD=date("dmY",$syncTime+24*3600);           
         
          /*Load data*/
          $url = $this->getCfgValue(36);                  
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;          
//print_r($loadurl);
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

    /**************/      
    $curUserId = 0;      
    if ($this->webSync == true){         
         $session = Yii::$app->session;          
         $session->open();
         
      if ($startRow == 1) 
          {          
               $session->set('realizeSyncTime', $syncTime);
               $session->set('realizeAllRecords', $allRecords);
          }                    
          else
          {
              $syncTime   = $session->get('realizeSyncTime');
              $allRecords = $session->get('realizeAllRecords');
          }
         
         $curUser=Yii::$app->user->identity;
         $curUserId =$curUser->id;
    }        
    /************/      
          
          
              /*Заголовок*/      
          
          $headRecord = new TblControlSaleHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUserId;
          $headRecord->save();
          
          for ($i;$i<$nC; $i++)
          {     
            $loadCounter++;    
               if(empty ($content[$i])) {continue;}                         
               $parse = str_getcsv($content[$i],",");            
//print_r($parse);
               $lastLoaded     =$parse[0];
            if(empty ($lastLoaded)) {continue;}                                             
               if ($loadCounter > 2500){break;}                                   
               if (count($parse) < 16) 
               {
                    $err[] = $parse;
                    continue;
               }/*Not enough fields*/                                                                                     
                                                                                              
               $saleAmount    =  (float)str_replace(',', '.',$parse[11]);    
               $saleSum       =  (float)str_replace(',', '.',$parse[13]);    
               $saleTime= strtotime(mb_substr($parse[5],1));
               $saleDate=date("Y-m-d H:i:s", $saleTime);
               $zakazTime= strtotime(mb_substr($parse[8],1));
               $zakazDate=date("Y-m-d H:i:s", $zakazTime);
               
                /*Новая запись всегда сохранять*/
                $record = new TblControlSaleContent();                   
                if (empty ($record)) continue;  
                
                $record->headerRef     = $headRecord->id;
                $record->ownerOrgTitle = mb_substr(trim($parse[1]),0,250,'utf-8');
                $record->orgTitle      = mb_substr(trim($parse[2]),0,250,'utf-8');
                $record->orgINN        = mb_substr(trim($parse[3]),0,20 ,'utf-8');
                $record->orgKPP        = mb_substr(trim($parse[14]),0,20 ,'utf-8');                
                $record->ref1C         = mb_substr(trim($parse[6]),0,20 ,'utf-8');
                $record->saleDate     = $saleDate;
                $record->regRecord     = mb_substr(trim($parse[7]),0,150,'utf-8');
                $record->zakazRef1C         = mb_substr(trim($parse[9]),0,20 ,'utf-8');
                $record->zakazDate     = $zakazDate;
                $record->wareTitle    = mb_substr(trim($parse[10]),0,150,'utf-8');
                $record->wareEd       = mb_substr(trim($parse[12]),0,20,'utf-8');
                $record->wareCount    = $saleAmount;
                $record->wareSum      = $saleSum;                                
                $record->save();
             $updated++;  
          }//
                            
        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;


// Собственники            
  $strSql = "UPDATE {{%control_sale_content}}, {{%control_sverka_filter}} SET 
      {{%control_sale_content}}.ownerOrgRef={{%control_sverka_filter}}.id where 
      {{%control_sale_content}}.ownerOrgTitle = {{%control_sverka_filter}}.owerOrgTitle
      and {{%control_sale_content}}.ownerOrgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     

    
// Собственники            
  $strSql = "UPDATE {{%control_sale_content}}, {{%orglist}} SET 
      {{%control_sale_content}}.orgRef={{%orglist}}.id where 
      {{%control_sale_content}}.orgINN = {{%orglist}}.orgINN
      and {{%control_sale_content}}.orgKPP = {{%orglist}}.orgKPP 
      and {{%control_sale_content}}.orgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     

  $strSql = "UPDATE {{%control_sale_content}}, {{%orglist}} SET 
      {{%control_sale_content}}.orgRef={{%orglist}}.id where 
      {{%control_sale_content}}.orgINN = {{%orglist}}.orgINN
      and ifnull({{%orglist}}.orgKPP, '') = '' 
      and {{%control_sale_content}}.orgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     

    
    
 //Уникальные   
  $strSql = "INSERT INTO {{%control_sale}} ( ownerOrgTitle, orgTitle, orgINN, orgKPP, orgRef, ref1C, saleDate,
  regRecord, zakazRef1C, zakazDate, wareTitle, wareEd, wareCount, wareSum, articul, headerRef, ownerOrgRef)
  (SELECT a.ownerOrgTitle, a.orgTitle, a.orgINN, a.orgKPP, a.orgRef, a.ref1C, a.saleDate,
  a.regRecord, a.zakazRef1C, a.zakazDate, a.wareTitle, a.wareEd, a.wareCount, a.wareSum, a.articul, a.headerRef, a.ownerOrgRef
  FROM {{%control_sale_content}} as a left join {{%control_sale}} as b on
  (a.orgINN = b.orgINN and a.saleDate = b.saleDate and a.ref1C = b.ref1C)  where b.id is null)";    
    Yii::$app->db->createCommand($strSql)->execute();                                           
    
 //Уникальные без товара  - прогресс по реализациям
  $strSql = "INSERT INTO {{%control_sale_progres}} ( ownerOrgTitle, orgTitle, orgINN, orgKPP, orgRef, ref1C, saleDate,
  regRecord, zakazRef1C, zakazDate, headerRef, ownerOrgRef)
  (SELECT DISTINCT a.ownerOrgTitle, a.orgTitle, a.orgINN, a.orgKPP, a.orgRef, a.ref1C, a.saleDate,
  a.regRecord, a.zakazRef1C, a.zakazDate, a.headerRef, a.ownerOrgRef
  FROM {{%control_sale_content}} as a left join {{%control_sale_progres}} as b on
  (a.orgINN = b.orgINN and a.saleDate = b.saleDate and a.ref1C = b.ref1C)  where b.id is null)";    
    Yii::$app->db->createCommand($strSql)->execute();                                           
    
    
  // связываем по товарные с прогрессом   
  $strSql = " update {{%control_sale}} as b, {{%control_sale_progres}} as a
    set b.progresRef = a.id  where a.orgINN = b.orgINN and a.saleDate = b.saleDate and a.ref1C = b.ref1C
    and  b.progresRef = 0";
  Yii::$app->db->createCommand($strSql)->execute();                                           
  // считаем суммы
  $strSql = " update {{%control_sale_progres}} as a set a.wareSum = (SELECT SUM(wareSum) from {{%control_sale}} where progresRef =a.id )
    where a.wareSum  = 0 "; 
  Yii::$app->db->createCommand($strSql)->execute();                                             
/*Разнесение по статьям*/
// $this->purchClassify($headRecord->id);

  // Выдергиваем номенклатуру реализации
  $strSql = "INSERT INTO {{%ware_names}} (wareTitle, lastUse, wareEd, isProduction)
    SELECT a.wareTitle, MAX(a.zakazDate) as dt, a.wareEd, 1  FROM {{%control_sale}} as a
    left join {{%ware_names}} as b on b.wareTitle = a.wareTitle
    where b.id is null GROUP BY a.wareTitle";
  Yii::$app->db->createCommand($strSql)->execute();                                           
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
         $session = Yii::$app->session;          
         $session->open();
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
          $headRecord = new TblProfitHeader();
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
/**********  Счета ***********************/     
/*****************************************/          
/********************/     
/* Грузим сверку по банковским счетам*/
public function loadBankData($startRow, $syncTime)
     {
         mb_internal_encoding("UTF-8");          
         $res=array();    
         
         $curUserId=0;
         if ($this->webSync){                                    
            $session = Yii::$app->session;          
            $session->open(); 
            $curUser=Yii::$app->user->identity;            
            $curUserId = $curUser->id;
         }
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
          if ($this->webSync){                                   
              $session->set('bankSyncTime', $syncTime);
              $session->set('bankAllRecords', $allRecords);
          }
          }                    
          else
          {
            if ($this->webSync){                                     
              $syncTime   = $session->get('bankSyncTime');
              $allRecords = $session->get('bankAllRecords');
            }  
          }

          $isUpdated = 0;        
          $nC = count($content);
          $syncDate = date('Y-m-d',$syncTime);
          
              /*Заголовок*/      
          
          $headRecord = new TblControlBankHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUserId;
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
            SELECT DISTINCT a.usedOrgTitle, a.bankAccount, a.accountNumber, a.cashType FROM {{%control_bank}} as a
            left join {{%control_bank_use}} as b on (a.usedOrgTitle = b.usedOrgTitle and
            a.bankAccount = b.bankAccount and a.accountNumber = b.accountNumber) where b.id is null ";
          
        Yii::$app->db->createCommand($strSql) ->execute();      

        /*Вяжем счета и записи*/        
        $strSql = "UPDATE {{%control_bank}} as a left join {{%control_bank_use}} as b on  
        (a.usedOrgTitle = b.usedOrgTitle and a.bankAccount = b.bankAccount and a.accountNumber = b.accountNumber)
        SET a.useRef = b.id where a.useRef = 0";
        
        /*Пишем остаток*/        
        $strSql = "UPDATE {{%control_bank}} as a, {{%control_bank_use}} as b 
        SET b.remainSum=a.cashSum
        WHERE a.headerRef = ".$headRecord->id." and  a.useRef = b.id         
        ";
            
       Yii::$app->db->createCommand($strSql) ->execute();      
        
     return $ret;
     }
    /*
    */ 
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
        $syncDate=date("dmY",$syncTime);     
        $sD=date("dmY",$syncTime);      
        $eD=date("dmY",$syncTime+24*3600);           
        
        $curUserId=0;
        if ($this->webSync){                                       
            $session = Yii::$app->session;          
            $session->open(); 
            $curUser=Yii::$app->user->identity;            
            $curUserId=$curUser->id;
        }
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
            if ($this->webSync){   
              $session->set('sverkaSyncTime', $syncTime);
              $session->set('sverkaAllRecords', $allRecords);
            }
          }                    
          else
          {
            if ($this->webSync)  $allRecords = $session->get('sverkaAllRecords');
          }
          

          $isUpdated = 0;        
          $nC = count($content);
          $onDate = date('Y-m-d',$syncTime);        
          
              /*Заголовок*/                
          $headRecord = new TblControlSverkaHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = $onDate;
          $headRecord->userRef = $curUserId;
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
            {{%control_sverka_dolga}}.typeRef = {{%control_sverka_dolga_use}}.typeRef
             where  {{%control_sverka_dolga}}.useRef = {{%control_sverka_dolga_use}}.id and 
             {{%control_sverka_dolga}}.headerRef= ".$headRecord->id.";";              
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
    public function cleanSclad()
    {
    //Получим список синхронизаций давностью более 90 дней не помеченных как дата инвентаризации
    
    $strSql = " SELECT id, onDate from {{%ware_header}}
    where DATEDIFF(NOW(),onDate) > 90 and isInv = 0 
    ORDER BY onDate ASC, syncDate DESC ";
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll();                          
    
    $curTime = strtotime($list[0]['onDate']);
    $curYear = date("Y", $curTime);
    $curWeek =  date("W", $curTime);
    
    $id_lst = "0";   
    for ($i=1;$i<count($list); $i++)
    {
        $nTime = strtotime($list[$i]['onDate']);
        $nYear = date("Y", $nTime);
        $nWeek =  date("W", $nTime);
        
        if( $this->verbeouse) echo $list[$i]['onDate']." : $nWeek неделя  $nYear года\n";        
        //новый год и новую неделю пропускаем
        if ($curYear != $nYear || $curWeek != $nWeek) {         
        if( $this->verbeouse) echo " -- $nWeek неделя  $nYear года\n";        
            $curYear = $nYear;
            $curWeek = $nWeek;
            $this->deleteList($id_lst);
            $id_lst = "0";
            continue;
            }
        $id_lst .= ", ".$list[$i]['id'];    
     }
     //финалочка
     $this->deleteList($id_lst);
    }

     public function deleteList($id_lst){

        //Удаляем. Параметры обрабатывать накладно.
        Yii::$app->db->createCommand("DELETE FROM {{%ware_content}} where headerRef  IN (".$id_lst.")")->execute();                          
        Yii::$app->db->createCommand("DELETE FROM {{%ware_header}} where id  IN (".$id_lst.")")->execute();                          
     
     /*     
     delete a.*
    from rik_ware_content as a
    left join rik_ware_use as b on a.`useRef` = b.id
    where IFNULL(b.`isInUse`,0) = 0
    */
     
     //echo Yii::$app->db->createCommand("DELETE FROM {{%ware_header}} where id  IN (".$id_lst.")")->getRawSql();                          
     }
    
    
     public function syncSclad($startRow, $syncTime)
     {
          mb_internal_encoding("UTF-8");
          $res=array();
          
        $curUserId=0;  
        if ($this->webSync){
            $session = Yii::$app->session;          
            $session->open();
            $curUser=Yii::$app->user->identity;
            $curUserId = $curUser->id;
       }
    /*Список активных складов*/    
       $useArray=array();    
       $scladArray=array();              
       $list = Yii::$app->db->createCommand(
                      "SELECT id, orgTitle, scladTitle, isInUse FROM {{%ware_use}} order by id")
                         ->queryAll();                    
           
           for($i=0; $i < count ($list); $i++)
               {                
                    $orgTitle=$list[$i]['orgTitle'];
                    $scladTitle =$list[$i]['scladTitle'];
                    $scladArray[$orgTitle][$scladTitle]=$list[$i]['id'];
                    $useArray[$orgTitle][$scladTitle]=$list[$i]['isInUse'];                                                            
               }          
        unset ($list);          
                    
     /*Получим данные из 1с*/          
          $sD=date("dmY",$syncTime);      
          $eD=date("dmY",$syncTime+24*3600+600);                                     
          /*Load data*/
          $url = $this->getCfgValue(10);    
          $loadurl =  $url.$startRow."&sd=".$sD."&ed=".$eD;
          
//          echo $loadurl;
          
          $page = $this->get_web_page( $loadurl);               
          $content = mb_split('\r\n', $page['content'] );          



    /*Заголовок*/      
          $headRecord = new TblWareHeader();
          $headRecord->syncDate = date("Y-m-d H:i:s");
          $headRecord->onDate = date("Y-m-d", $syncTime);
          $headRecord->userRef = $curUserId;
          $headRecord->save();
          
          
        if ($startRow == 1) 
          {
               /*Первый блок данных*/
               $parse = str_getcsv($content[0],",");          
               $tmp = explode("/", $parse[0]);/*на случай фигни*/  
               $allRecords=intval(preg_replace("/[\D]/","",$tmp[0]));
               $i=1;  
            if ($this->webSync){               
              $session->set('scladSyncTime', $syncTime);
              $session->set('scladAllRecords', $allRecords);
            }
          }                    
          else
          {
              if ($this->webSync)$allRecords = $session->get('sverkaAllRecords');
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
                $useArray[$orgTitle][$scladTitle] = 0;
              }
               
             if ($useArray[$orgTitle][$scladTitle] != 1) continue;
              
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

    /*Собираем в единую таблицу номенклатуру*/    
    $strSql="INSERT INTO {{%warehouse}} (title, articul, amount,  ed, grpGood, initPrice, isValid, useRef)
    (SELECT DISTINCT a.goodTitle, a.articul, a.goodAmount, a.goodEd, a.grpGood, a.initPrice, 1, a.useRef
    from {{%ware_content}} as a  LEFT JOIN {{%warehouse}} as b on   (b.title = a.goodTitle and b.ed = a.goodEd)
    LEFT JOIN rik_ware_use as c on c.id = a.useRef  where
    c.isInUse = 1 and b.id is null and headerRef=:headerRef) ";

    Yii::$app->db->createCommand($strSql,['headerRef'=> $headRecord->id])->execute();    
    
    
    /*Связываем с номенклатурой*/    
    $strSql="UPDATE {{%ware_content}}, {{%warehouse}} 
        set {{%ware_content}}.goodRef= {{%warehouse}}.id
        WHERE {{%ware_content}}.goodRef = 0  AND  {{%ware_content}}.goodTitle= {{%warehouse}}.title
        AND {{%ware_content}}.goodEd = {{%warehouse}}.ed
       and headerRef=:headerRef";
        
    Yii::$app->db->createCommand($strSql,['headerRef'=> $headRecord->id])->execute();        


    /*

INSERT INTO rik_ware_names
   (warehouseRef, wareTitle,wareEd, isProduction, wareTypeRef, wareGrpRef, producerRef, wareListRef)
    SELECT a.id, a.title, a.ed, a.isProduction, a.wareTypeRef, a.grpRef, a.producerRef, a.wareListRef
    FROM rik_warehouse as a
    left join rik_ware_names as b on b.wareTitle = a.title
	left Join rik_ware_use as u on u.id= a.useRef
    where b.id is null  AND a.isActive = 1 AND u.isInUse=1

    */


    /*
    UPDATE
{{%warehouse}} as a,
 {{%ware_content}} as b
 SET a.`useRef` = b.`useRef`
  where 
  a.`wareContentRef` = b.id
  
  
  UPDATE
{{%warehouse}} as a,
 {{%ware_content}} as b
 SET a.`useRef` = b.`useRef`
  where 
  a.`title` = b.`goodTitle`
  and 
  a.`useRef` = 0
    */
    
/*

*/    
    
    /*Обновим к-во*/    
    
    $lastDate=  Yii::$app->db->createCommand("Select max(onDate) FROM {{%ware_header}} where onDate <= NOW()")->queryScalar();    
    
    if (strtotime($lastDate) <= $syncTime)
    {
    
    $lastRef=  Yii::$app->db->createCommand("Select max(id) FROM {{%ware_header}} where onDate =:onDate",[':onDate' =>$lastDate])->queryScalar();        
        
    $strSql="UPDATE  {{%warehouse}} SET amount = 0";
    Yii::$app->db->createCommand($strSql)->execute();    
    
    
    $strSubSql ="(SELECT sum(goodAmount) as goodAmount, goodTitle, goodEd FROM {{%ware_content}}, {{%ware_use}} WHERE  
    {{%ware_content}}.useRef = {{%ware_use}}.id AND {{%ware_use}}.useInSum=1  AND {{%ware_use}}.isInUse=1
    AND {{%ware_content}}.headerRef = :headerRef 
    GROUP BY goodTitle, goodEd)";
    
    $strSql="UPDATE  {{%warehouse}} as a left join ".$strSubSql." as b on 
    (a.title = b.goodTitle and a.ed = b.goodEd)    
    SET a.amount = b.goodAmount 
    ";
    
    $ret['updateSQL'] = Yii::$app->db->createCommand($strSql,['headerRef'=> $lastRef])->getRawSql();    
    
    Yii::$app->db->createCommand($strSql,['headerRef'=> $headRecord->id])->execute();    
    }
    
    
    $ret['allRecords'] = $allRecords;
    $ret['lastLoaded'] = $lastLoaded;
    $ret['updatedRecord'] = $updated;
  
    if ($this->webSync){
        $this->verbeouse = false;    
    }
        $this->cleanSclad();
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
    if ($this->webSync){         
         $session = Yii::$app->session;          
         $session->open();
    }
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
public $syncDataArray;
public $errcnt=0;

public function prepareSyncData()
{
   /*
    id
    title
    lastSync
   */ 

   $this->syncDataArray=[   
       [
          'id' => 0,
          'title' => 'Счета клиентов', //$this->actionSyncSchet();
          'lastSync' => [],
       ] ,   

       [
          'id' => 1,
          'title' => 'Счета поставщиков', //$this->actionSyncSchet();
          'lastSync' => [],
       ] ,   
   
          
       [
          'id' => 2,
          'title' => 'Расход (Оплаты поставщикам)', //$this->actionSyncSupplierOplata();
          'lastSync' => 0,
       ],    
   
       [
          'id' => 3,
          'title' => 'Приход (Оплаты клиентов)', //$this->actionSyncOplata();
          'lastSync' => 0,
       ],    

       [
          'id' => 4,
          'title' => 'Состояние банковского счета', //$this->actionSyncBank();
          'lastSync' => 0,
       ],    

      [
          'id' => 5,
          'title' => 'Движение по банку', //$this->actionSyncBankOp();
          'lastSync' => 0,
       ],    

      [
          'id' => 6,
          'title' => 'Доход', //$this->actionSyncProfit();
          'lastSync' => 0,
       ],    
       
      [
          'id' => 7,
          'title' => 'Закупки', //$this->actionSyncPurch();
          'lastSync' => 0,
       ],    
       
      [
          'id' => 8,
          'title' => 'Поставки', //$this->actionSyncSupply();
          'lastSync' => 0,
       ],    
       
      [
          'id' => 9,
          'title' => 'Склад', //$this->actionSyncSclad();
          'lastSync' => 0,
       ],    
       
      [
          'id' => 10,
          'title' => 'Сверка', // $this->actionSyncSverka();
          'lastSync' => 0,
       ],    
               
   ] ;    
        
        $strSql="SELECT keyValue FROM  {{%config}} where id = 106;";
        $this->syncDataArray[0]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());
        
        $strSql="SELECT keyValue FROM  {{%config}} where id = 117;";
        $this->syncDataArray[1]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT keyValue FROM  {{%config}} where id = 118;";
        $this->syncDataArray[2]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT keyValue FROM  {{%config}} where id = 107;";
        $this->syncDataArray[3]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());
        
        $strSql="SELECT max(syncDate) FROM  {{%control_bank_header}};";
        $this->syncDataArray[4]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT max(syncDateTime) FROM  {{%bank_op_header}};";
        $this->syncDataArray[5]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());
        
        $strSql="SELECT max(syncDate) FROM  {{%profit_header}};";
        $this->syncDataArray[6]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT max(dateCreation) FROM  {{%purchase}};";
        $this->syncDataArray[7]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT keyValue FROM  {{%config}} where id = 108;";
        $this->syncDataArray[8]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());

        $strSql="SELECT keyValue FROM  {{%config}} where id = 110;";
        $this->syncDataArray[9]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());
                
        $strSql="SELECT max(syncDate) FROM  {{%control_sverka_header}};";
        $this->syncDataArray[10]['lastSync'] = strtotime(Yii::$app->db->createCommand($strSql)->queryScalar());
        
        
        $dt=strtotime(date('Y-m-d'))-5*24*3600;
        $this->errcnt=0;
        for ($i=0;$i<11;$i++)
        {
           if ($this->syncDataArray[$i]['lastSync'] < $dt)     
           $this->errcnt++;        
        }
        
        
}

   public function getSyncDataProvider()		
   {

        $this->prepareSyncData();
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->syncDataArray,
            'totalCount' => count($this->syncDataArray),
            'pagination' => [
            'pageSize' => 15,
            ],
            'sort' => [
            'attributes' => [
              'id',
              'title',
              'lastSync',
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   


// 


/*****************************************/
/********** Отвесы           *****/
/*****************************************/
/* 
ALTER TABLE `rik_otves_list` ADD COLUMN `note` MEDIUMTEXT;
*/
public function loadGoogleOtvesData ()
     {
         mb_internal_encoding("UTF-8");

     $ret =[
     'allRecords' => 0,
     'lastLoaded' => 0,
     'updatedRecord' => 0,
     'err' => '',
     ];


          /*Load data*/
          $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTFqrjlYA1KvzKLMOqrgXR1FZ4oNbUuy8A6Xjnv3i-BX32xUklrIuKA9ssGYsQhabg-f6GzVDY-IqEW/pub?gid=1507692962&single=true&output=csv";
          $loadurl =  $url;
          $page = $this->get_web_page($loadurl);
          $content = mb_split('\r\n', $page['content'] );

          $err=array();
          $lastLoaded=0;
          $allRecords =0;
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updated = 0;

        $strSql = " DELETE FROM {{%otves_list}}";
        Yii::$app->db->createCommand($strSql)->execute();

        $wareArray=[];
//        $idxArray=[];
        $parse = str_getcsv($content[1],",");
        /*Определяем товары*/
        $N = count($parse);
        for($j=1; $j<$N;$j++ )
        {
         $strName = trim($parse[$j]);
         if(empty($strName)) continue;
//         echo $strName."\n";
         $wareRecord= TblWareList::findOne(
         [
         'wareTitle' => $strName,
         ]
         );
         if(empty($wareRecord)) continue;
         $wareArray[]=[
         'col' => $j,
         'wareListRef' => $wareRecord->id,
         ];
        }


        $N = count($content);
        $wN = count($wareArray);
        for($i=3; $i<$N;$i++ )
        {
            $parse = str_getcsv($content[$i],",");
            for($j=0; $j<$wN;$j++ )
            {

             $col=$wareArray[$j]['col'];
             //echo $wareArray[$j]['wareListRef']." ".$parse[$col]."\n";
             if(empty($parse[$col])) continue;
             $record = new TblOtvesList();
             $record->refWareList = $wareArray[$j]['wareListRef'];
             $record->size=$parse[$col];
             if(!empty($parse[$col+1]))$record->note=$parse[$col+1];
             $record->save();
            }
        }



      //  print_r($wareArray);

        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;


     return $ret;
     }

public function loadTransportTarifData ()
     {
         mb_internal_encoding("UTF-8");

     $ret =[
     'allRecords' => 0,
     'lastLoaded' => 0,
     'updatedRecord' => 0,
     'err' => '',
     ];


          /*Load data*/
          $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTCCuMM9nqTlRISGXq62pDYvIKc9BKo7zisNXr88sIC1fvJanOQ9jCRzMrRtGlj7Qoi8yS-jG5xlRtl/pub?gid=0&single=true&output=csv";
          $loadurl =  $url;
          $page = $this->get_web_page($loadurl);
          $content = mb_split('\r\n', $page['content'] );
          $N = count($content);
            
          $err=array();
          $lastLoaded=0;
          $allRecords =0;
          $loadCounter=0;
          $i=0;
          $curRecord = "";
          $updated = 0;

        $strSql = " DELETE FROM {{%transport_tarif}}";
        Yii::$app->db->createCommand($strSql)->execute();
        $allRecords = $N-4;
        for($i=4; $i<$N;$i++ )
        {
            $parse = str_getcsv($content[$i],",");
            if (empty($parse[0])) continue;
            $record = new TblTransportTarif();
            if (empty($record)) return $ret;
            
             $record->city = mb_substr($parse[0],0,75, 'utf-8');
             $record->company = mb_substr($parse[1],0,75, 'utf-8');
             $record->v1 = (float)str_replace(',', '.',$parse[2]);  
             $record->v2 = (float)str_replace(',', '.',$parse[3]);  
             $record->v3 = (float)str_replace(',', '.',$parse[4]);  
             $record->v4 = (float)str_replace(',', '.',$parse[5]);  
             $record->v5 = (float)str_replace(',', '.',$parse[6]);  
             $record->v6 = (float)str_replace(',', '.',$parse[7]);  
             $record->v7 = (float)str_replace(',', '.',$parse[8]);  
             $record->timeNote = mb_substr($parse[10],0,75, 'utf-8');
             $record->save();
           $updated++;
           $lastLoaded = $i;
        }

        $ret['allRecords'] = $allRecords;
        $ret['lastLoaded'] = $lastLoaded;
        $ret['updatedRecord'] = $updated;
        $ret['err'] = $err;
     return $ret;
     }


     

/****************/     
  /************End of model*******************/ 
 }
