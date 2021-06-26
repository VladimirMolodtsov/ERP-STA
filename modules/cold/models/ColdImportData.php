<?php

namespace app\modules\cold\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\ClientData; 
use app\models\PhoneList;
use app\models\ContactList;

use app\modules\cold\models\TblCold;
use app\modules\cold\models\TblColdHeader;
use app\modules\cold\models\TblColdContent;


/**
 * ColdImportData  - Импорт данных
 */
//Тест
//https://docs.google.com/spreadsheets/d/e/2PACX-1vRzS0sz_bi2v5JgAlsEtTB7m3GqYJDwiO61V_2yko_QhpFl26jD8XIdy_h8ScxrCqriXsq0BnSR8ou3/pub?output=csv
 
 class ColdImportData extends Model
{
    
    public $csvUrl = "";    
    public $csvFile= "";    
    public $description= "";    
    public $debug;
    
    public $refHeader="";
    
    public function rules()
    {
        return [            
            [[ 'csvUrl','csvFile', 'description' ], 'default'],            
            [['csvUrl','description'], 'trim'],
            [['description'], 'required'],            
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
    
/*************************************/  
public function upload()
 {
   if ($this->validate()) 
   {   
      $uploadPath=__DIR__."/../uploads/";      
      $this->csvFile->saveAs( $uploadPath. $this->csvFile->baseName . '.' . $this->csvFile->extension);
      return true;
    } else 
    {
      return false;
    }
  }
/**********************/  
    public function saveHeader()
    {
        $record = new TblColdHeader();
        $curUser=Yii::$app->user->identity;
        
        $record -> importDate = date("Y-m-d H:i:s");
        $record -> refManager = $curUser->id;
        $record -> decription = $this->description;
        $record -> save();
        $this->refHeader = $record -> id;
    }
/**********************/
     public function loadClientFromFile($fname)
     {
        $content = file($fname);
       
        $this->saveHeader();
        
        $n = count($content);
        for ($i=1;$i< $n; $i++)
        {
          $this->processClientRecord($content[$i]);       
        }
     
     
     }    
     
/**********************/
     public function loadClientFromUrl()        
    {
        $clientModel= new ClientData();
        mb_internal_encoding("UTF-8");
        
        $this->debug=[];    
    /*    $strSql="UPDATE {{%orglist}} set isNew = 0 where isNew > 0";
        Yii::$app->db->createCommand($strSql)->execute();*/

     // $this->debug[]=$this->csvUrl;
        
        $page = $this->get_web_page( $this->csvUrl );    
        $content = mb_split('\r\n', $page['content'] );
        
        $this->saveHeader();

        $n = count($content);        
        for ($i=1;$i< $n; $i++)
        {
          $this->processClientRecord($content[$i]);       
        }
    }


    
 /**************************/

    public function processClientRecord($parse_string)
    {
            $clientData= new ClientData();
            mb_internal_encoding("UTF-8");
            $parse = str_getcsv($parse_string,",");    
            
            $n = count($parse);
            if ($n < 10) return false;
            
            for($j=0; $j< $n; $j++) {$parse[$j]=trim($parse[$j]);}
            /*Нет названия*/
            if(empty($parse[1])) return false;

            $record = new TblColdContent();
            $record -> orgHeaderRef = $this->refHeader;
            
            $record -> orgINN        = mb_substr($parse[0],0,20,'utf-8');
            $record -> orgTitle      = mb_substr($parse[1],0,250);
            $record -> orgRazdel     = mb_substr($parse[2],0,150);
            $record -> orgSubRazdel  = mb_substr($parse[3],0,150);
            $record -> orgRubrica    = mb_substr($parse[4],0,150);
            $record -> orgArea       = mb_substr($parse[5],0,150);
            $record -> orgCity       = mb_substr($parse[6],0,250);
            $record -> orgDistrict   = mb_substr($parse[7],0,150);
            $record -> orgAdress     = mb_substr($parse[8],0,250);
            $record -> orgX          = floatval ($parse[9]);
            $record -> orgY          = floatval ($parse[10]);
            $record -> orgIndex      = mb_substr($parse[11],0,20); 
            $record -> orgEMail      = mb_substr($parse[12],0,150);
            $record -> orgPhoneList  = mb_substr($parse[13],0,250);
            $record -> orgFAXList    = mb_substr($parse[14],0,250);
            $record -> orgURL        = mb_substr($parse[15],0,250);
  
            $record -> save();
            return true;
            
/*** Пока отключено ********************/
            /*Первый контакт*/
            if (empty($parse[16])) return true;            
            if (empty ($coldRecord->firstContactRef))
            {              
                //Ищем телефон
                if (!empty($parse[17]))                
                    $phoneRef = $this->getPhoneRef ($clientData->orgId, trim($parse[17]));
                else  $phoneRef = 0;                 
                
            /*сохраним контакт*/
                $contact = new ContactList();
                $contact->ref_phone = $phoneRef;
                $contact->ref_org = $clientData->orgId;
                $contact->ref_user = 0;	
                $contact->eventType = 1;	
                $contact->contactDate = date("Y.m.d h:i:s", strtotime($parse[16]));	  			  		  
                $contact->note        = mb_substr($parse[18],0,250);   
                $contact->contactFIO  = mb_substr($parse[19],0,75);   
                $contact->save();
                $coldRecord ->firstContactRef      = $contact->id;
            }            
            $coldRecord ->refOrg               = $clientData->orgId;           
            $coldRecord ->firstContactFIO      = mb_substr($parse[19],0,75);   
            $coldRecord ->firstContactPosition = mb_substr($parse[20],0,75);
            $coldRecord ->firstEmail           = mb_substr($parse[21],0,50);
            $coldRecord ->supplyManagerFIO     = mb_substr($parse[22],0,75);   
            $coldRecord ->supplyPhone          = mb_substr($parse[23],0,50);   
            
            /*Второй контакт*/
            if (empty($parse[24])) {$coldRecord -> save(); 
            return true;}
         
         
            if (empty ($coldRecord->secondContactRef))
            {              
                //Ищем телефон
                if (!empty($parse[25]))                
                    $phoneRef = $this->getPhoneRef ($clientData->orgId, trim($parse[25]));
                else  $phoneRef = 0;                 
                
            /*сохраним контакт*/
                $contact = new ContactList();
                $contact->ref_phone = $phoneRef;
                $contact->ref_org = $clientData->orgId;
                $contact->ref_user = 0;	
                $contact->eventType = 1;	
                $contact->contactDate = date("Y.m.d h:i:s", strtotime($parse[24]));	  			  		  
                $contact->note        = mb_substr($parse[26],0,250);   
                $contact->contactFIO  = mb_substr($parse[27],0,75);   
                $contact->save();
            }
            
            $coldRecord ->secondEmail = mb_substr($parse[28],0,50);
            if (!empty($parse[29]))
            {
                $coldRecord ->isInteres   = 1 ;
                $coldRecord ->interesDescription = mb_substr($parse[29],0,250);
            }
            $coldRecord -> save();
            return true;
    }
    
/**/

    public function getPhoneRef ($orgId, $phone)
    {              
                $phoneRecord = PhoneList::findOne(
                [
                    'ref_org' => $orgId,
                    'phone'   => $phone,            
                ]);                    
                if (empty ($phoneRecord)) 
                {
                    $phoneRecord = new PhoneList();
                    $phoneRecord ->ref_org =  $orgId;
                    $phoneRecord ->phone   =  $phone;
                    $phoneRecord ->status = 1;
                }                
       return   $phoneRecord->id;        
    }
    
   
    
  /************End of model*******************/ 
 }
