<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 


use app\modules\bank\models\BuhStatistics;

use app\modules\managment\models\TblMonitorRow;
use app\modules\managment\models\TblMonitorRowCfg;


/**
 * HeadMonitorForm  - монитор собственника
 */

// 
class PlanProductionForm extends Model
{

    public $id=0;
    
    public $syncDataArray = array();
    /*План продукции*/
    public $srcURL="https://docs.google.com/spreadsheets/d/e/2PACX-1vSTTv9UgGunOKPdNHd0tM93ks401VGhMrvwPXOjfhN7rOC2zvJ133g2iFh7FQ-RvyrfFJugfsQJCrDA/pub?gid=1887284216&single=true&output=csv";
    
    /*Готовая продукция*/
    public $srcReadyURL="https://docs.google.com/spreadsheets/d/e/2PACX-1vSTTv9UgGunOKPdNHd0tM93ks401VGhMrvwPXOjfhN7rOC2zvJ133g2iFh7FQ-RvyrfFJugfsQJCrDA/pub?gid=2070833521&single=true&output=csv";
    public $debug=[];   
    public $err=[];   
     
   public $note;  

    public function rules()
    {
        return [
              [[], 'default'],
              [['note'], 'safe'],
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
   
public function prepareGogleData($params)
{
    $page = $this->get_web_page( $this->srcURL );     
    $content = mb_split('\r\n', $page['content'] );
    
    $idx=0;
  
$fltNote="";  
if (($this->load($params)  && $this->validate())) {
    $fltNote = $this->note;
    $this->debug[]=$this->note;
    $this->debug[]=$fltNote;
}   
    for ($i=1;$i< count($content); $i++)
          {
               $parse_string =$content[$i];                              
               $parse = str_getcsv($parse_string,",");     
               if (count($parse)<15) continue;


 if(!empty($fltNote))
 {
   $this->debug[]= $parse[10]; 
   
   //if(mb_strstr ($fltNote,$parse[10], 'utf-8' ) == false )  continue;
   if(preg_match ("/".$fltNote."/iu",$parse[10]) != 1 )  continue;
   
   $this->debug[]= 'success';  
 }
               
               $this->syncDataArray[$idx]['id'] = $idx;
               $this->syncDataArray[$idx]['productionDate'] = $parse[0];
               $this->syncDataArray[$idx]['productionNum']  = $parse[1];
               $this->syncDataArray[$idx]['schetNum']       = $parse[2];
               $this->syncDataArray[$idx]['wareType']       = $parse[3];
               $this->syncDataArray[$idx]['srcType']        = $parse[4];
               $this->syncDataArray[$idx]['wareTitle']      = $parse[5];
               $this->syncDataArray[$idx]['wareCount']      = $parse[6];
               $this->syncDataArray[$idx]['wareEd']         = $parse[7];
               $this->syncDataArray[$idx]['warePak']        = $parse[8];
               $this->syncDataArray[$idx]['srcTitle']       = $parse[9];
               $this->syncDataArray[$idx]['note']           = $parse[10];
               $this->syncDataArray[$idx]['inWorkNum']      = $parse[11];
               $this->syncDataArray[$idx]['inWorkDate']     = $parse[12];
               $this->syncDataArray[$idx]['stage1Date']     = $parse[13];
               $this->syncDataArray[$idx]['stage2Date']     = $parse[14];
               $this->syncDataArray[$idx]['finishDate']     = $parse[15];               
            $idx++;
          }
}

   public function getGogleDataProvider($params)		
   {

        $this->prepareGogleData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->syncDataArray,
            'totalCount' => count($this->syncDataArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                 'id',
                'productionDate',
                'productionNum',
                'schetNum',
                'wareType',
                'srcType',
                'wareTitle',
                'inWorkDate',
                'stage1Date',
                'stage2Date',
                'finishDate',
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/*****************/


public function prepareGogleReadyData($params)
{
    $page = $this->get_web_page( $this->srcReadyURL );     
    $content = mb_split('\r\n', $page['content'] );
    
    $idx=0;
  
$fltNote="";  
if (($this->load($params)  && $this->validate())) {
    $fltNote = $this->note;
    $this->debug[]=$this->note;
    $this->debug[]=$fltNote;
}   
    for ($i=1;$i< count($content); $i++)
          {
               $parse_string =$content[$i];                              
               $parse = str_getcsv($parse_string,",");     
               if (count($parse)<9) continue;


 if(!empty($fltNote))
 {
   $this->debug[]= $parse[10]; 
   
   //if(mb_strstr ($fltNote,$parse[10], 'utf-8' ) == false )  continue;
   if(preg_match ("/".$fltNote."/iu",$parse[10]) != 1 )  continue;
   
   $this->debug[]= 'success';  
 }
               
               $this->syncDataArray[$idx]['id'] = $idx;               
               $this->syncDataArray[$idx]['productionNum']  = $parse[0];
               $this->syncDataArray[$idx]['schetNum']       = $parse[1];
               $this->syncDataArray[$idx]['wareType']       = $parse[2];               
               $this->syncDataArray[$idx]['wareTitle']      = $parse[3];
               $this->syncDataArray[$idx]['wareCount']      = $parse[4];
               $this->syncDataArray[$idx]['wareEd']         = $parse[5];
               $this->syncDataArray[$idx]['note']           = $parse[6];
               $this->syncDataArray[$idx]['finishDate']     = $parse[7];
               $this->syncDataArray[$idx]['status']         = $parse[8];                              
            $idx++;
          }
}

   public function getGogleReadyProvider($params)		
   {

        $this->prepareGogleReadyData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->syncDataArray,
            'totalCount' => count($this->syncDataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
                 'id',
                'productionNum',
                'schetNum',
                'wareType',
                'wareTitle',
                'finishDate',
                'status',
            ],

            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

   
   
 }
 
