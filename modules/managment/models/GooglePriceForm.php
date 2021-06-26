<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 

use app\models\TblWareNames;
/**
 * PriceForm  - прайс из 1С
 */

// 
class GooglePriceForm extends Model
{

    public $id=0;
    
    public $syncPriceArray = array();
    public $syncProductionArray = array();
    
    public $srcProductionURL="https://docs.google.com/spreadsheets/d/e/2PACX-1vQwwspH0qt50RdbagKatuIwEKs75ul1I-Kh92uQlQwrQE3Hn3xzsJ2jTb6jtUS39LFncBejOGQ3BVGd/pub?gid=982046380&single=true&output=csv";
    
    public $srcPriceURL="https://docs.google.com/spreadsheets/d/e/2PACX-1vQwwspH0qt50RdbagKatuIwEKs75ul1I-Kh92uQlQwrQE3Hn3xzsJ2jTb6jtUS39LFncBejOGQ3BVGd/pub?gid=2038343986&single=true&output=csv";
    
    public $debug=[];   
    public $err=[];   
     
 public $wareGroup;
 public $wareProducer;

    public function rules()
    {
        return [
              [[], 'default'],
              [['wareGroup', 'wareProducer' ], 'safe'],
        ];
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

    
   /***************************/     
   /* Берем из гугла и парсим 
   ***************************/
/*
ALTER TABLE `rik_ware_names` ADD COLUMN `v1` DOUBLE DEFAULT NULL;
ALTER TABLE `rik_ware_names` ADD COLUMN `v2` DOUBLE DEFAULT NULL;
ALTER TABLE `rik_ware_names` ADD COLUMN `v3` DOUBLE DEFAULT NULL;
ALTER TABLE `rik_ware_names` ADD COLUMN `v4` DOUBLE DEFAULT NULL;
ALTER TABLE `rik_ware_names` ADD COLUMN `isInPrice` TINYINT DEFAULT 0;
*/   
public function syncPriceGogleData($params)
{

    //TblWareNames
    //Сбрасываем использование
    Yii::$app->db->createCommand("UPDATE {{%ware_names}} SET isInPrice = 0")->execute();
        
    $page = $this->get_web_page( $this->srcPriceURL );     
    $content = mb_split('\r\n', $page['content'] );
    
  
$fltGroup="";
$fltProducer="";    
if (($this->load($params)  && $this->validate())) {
    $fltGroup = $this->wareGroup;
    $fltProducer = $this->wareProducer;
}   
    
  $idx=0;    
  for ($i=1;$i< count($content); $i++)
  {
               $parse_string =$content[$i];                              
               $parse = str_getcsv($parse_string,",");     
               if (count($parse)<7) continue;

     if(!empty($fltGroup))
     {         
       if(preg_match ("/".$fltGroup."/iu",$parse[0]) != 1 )  continue;      
     }

     if(!empty($fltProducer))
     {   
       if(preg_match ("/".$fltProducer."/iu",$parse[1]) != 1 )  continue;      
     }
     
               $this->syncPriceArray[$idx]['id']           = $idx;
               $this->syncPriceArray[$idx]['wareGroup']    = $parse[0];
               $this->syncPriceArray[$idx]['wareProducer'] = $parse[1];
               $this->syncPriceArray[$idx]['wareTitle']    = $parse[2];
               $this->syncPriceArray[$idx]['v1']           = (float)str_replace(',', '.',$parse[3]); 
               $this->syncPriceArray[$idx]['v2']           = (float)str_replace(',', '.',$parse[4]); 
               $this->syncPriceArray[$idx]['v3']           = (float)str_replace(',', '.',$parse[5]); 
               $this->syncPriceArray[$idx]['v4']           = (float)str_replace(',', '.',$parse[6]); 
               $this->syncPriceArray[$idx]['isFind'] =0;
            
        $record = TblWareNames::findOne([
        'wareTitle' => trim($parse[2]),       
        ]);    
            $this->syncPriceArray[$idx]['isFind'] =1;        
           if (empty($record))             
           {
               $record = new TblWareNames();
               if (empty($record)) {
                   $this->syncPriceArray[$idx]['isFind'] =0;
                   continue;
               }            
               $this->syncPriceArray[$idx]['isFind'] =2;
               $record->wareTitle = trim($parse[2]);               
           }
               
               $record->v1 = (float)str_replace(',', '.',$parse[3]); 
               $record->v2 = (float)str_replace(',', '.',$parse[4]); 
               $record->v3 = (float)str_replace(',', '.',$parse[5]); 
               $record->v4 = (float)str_replace(',', '.',$parse[6]); 
               $record->isInPrice = 1;
               $record->save();
              
            
    $idx++; 
    }
    
            $provider = new ArrayDataProvider([
            'allModels' => $this->syncPriceArray,
            'totalCount' => count($this->syncPriceArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
                 'id',
                'wareGroup',
                'wareProducer',
                'wareTitle',
                'isFind'
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
    return $provider;
}
   
      
public function preparePriceGogleData($params)
{
    $page = $this->get_web_page( $this->srcPriceURL );     
    $content = mb_split('\r\n', $page['content'] );
    
  
$fltGroup="";
$fltProducer="";    
if (($this->load($params)  && $this->validate())) {
    $fltGroup = $this->wareGroup;
    $fltProducer = $this->wareProducer;
}   
    
  $idx=0;    
  for ($i=1;$i< count($content); $i++)
  {
               $parse_string =$content[$i];                              
               $parse = str_getcsv($parse_string,",");     
               if (count($parse)<7) continue;

     if(!empty($fltGroup))
     {         
       if(preg_match ("/".$fltGroup."/iu",$parse[0]) != 1 )  continue;      
     }

     if(!empty($fltProducer))
     {   
       if(preg_match ("/".$fltProducer."/iu",$parse[1]) != 1 )  continue;      
     }
     
               $this->syncPriceArray[$idx]['id']           = $idx;
               $this->syncPriceArray[$idx]['wareGroup']    = $parse[0];
               $this->syncPriceArray[$idx]['wareProducer'] = $parse[1];
               $this->syncPriceArray[$idx]['wareTitle']    = $parse[2];
               $this->syncPriceArray[$idx]['v1']           = $parse[3];
               $this->syncPriceArray[$idx]['v2']           = $parse[4];
               $this->syncPriceArray[$idx]['v3']           = $parse[5];
               $this->syncPriceArray[$idx]['v4']           = $parse[6];
               $this->syncPriceArray[$idx]['isFind']       = 1;    
                                                                                                      
            $idx++;
    }
        
}

   public function getPriceGogleDataProvider($params)		
   {

        $this->preparePriceGogleData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->syncPriceArray,
            'totalCount' => count($this->syncPriceArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
                 'id',
                'wareGroup',
                'wareProducer',
                'wareTitle',
                'isFind'
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/***************************/
   
public function prepareProductGogleData($params)
{
    $page = $this->get_web_page( $this->srcProductionURL );     
    $content = mb_split('\r\n', $page['content'] );
    
/*  
$fltGroup="";
$fltProducer="";    
if (($this->load($params)  && $this->validate())) {
    $fltGroup = $this->wareGroup;
    $fltProducer = $this->wareProducer;
}   */
    
  $idx=0;    
  for ($i=1;$i< count($content); $i++)
  {
               $parse_string =$content[$i];                              
               $parse = str_getcsv($parse_string,",");     
               if (count($parse)<7) continue;

     if(!empty($fltGroup))
     {         
       if(preg_match ("/".$fltGroup."/iu",$parse[0]) != 1 )  continue;      
     }

     if(!empty($fltProducer))
     {   
       if(preg_match ("/".$fltProducer."/iu",$parse[1]) != 1 )  continue;      
     }
     
               $this->syncProductionArray[$idx]['id']           = $idx;
               $this->syncProductionArray[$idx]['wareGroup']    = $parse[0];
               $this->syncProductionArray[$idx]['wareProducer'] = $parse[1];
               $this->syncProductionArray[$idx]['serviceTitle']    = $parse[2];
               $this->syncProductionArray[$idx]['v1']           = $parse[3];
               $this->syncProductionArray[$idx]['v2']           = $parse[4];
               $this->syncProductionArray[$idx]['v3']           = $parse[5];
               $this->syncProductionArray[$idx]['v4']           = $parse[6];                                                                                          
            $idx++;
    }
}

   public function getProductGogleDataProvider($params)		
   {

        $this->prepareProductGogleData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->syncProductionArray,
            'totalCount' => count($this->syncProductionArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                 'id',
                'serviceTitle',
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

   
   
 }
 
