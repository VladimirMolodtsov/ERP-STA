<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblDocuments;
use app\modules\bank\models\TblDocHeader;


/**
 * DocImportData  - Импорт данных из таблицы гугл
 
 Скачиваем csv,
 сканируем -сравнивая с имеющейся таблицей.
 Изменившиеся записи правим
 Новые добавляем
 
 образ счета (ссылка гугл) считаем уникальным идентификатором в гугловской таблицей (берем md5 как ключ)
 
 изменением строки считаем изменением сигнатуры (берем md5 от строки и сохраняем сигнатуру в базе) 

 Новые записи импортируем со ссылкой на заголовок.
    
 */
 
//https://docs.google.com/spreadsheets/d/e/2PACX-1vT9szMVvJdsdNZ2kCktIULOuIsdoY3dGVDztOBcGwCB7sjMoIexqc8JwTm6U3VnEUsCauJrsoItnECE/pub?output=csv
 
 class DocImportData extends Model
{
    
    // Решение временное, поэтому пробиваем сюда
    public $csvUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vT9szMVvJdsdNZ2kCktIULOuIsdoY3dGVDztOBcGwCB7sjMoIexqc8JwTm6U3VnEUsCauJrsoItnECE/pub?gid=1569700515&single=true&output=csv';    
    public $debug;   
    public $refHeader=0;
    public $baseData;
    public $refManager;
    
    
    public function rules()
    {
        return [            
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
    

/**********************/  
    public function saveHeader()
    {
        $record = new TblDocHeader();
        $curUser=Yii::$app->user->identity;
        
        
        $record -> docDateTime = date("Y-m-d H:i:s");
        $record -> refManager = $curUser->id;
        $record -> isFinished = 1;
        $record -> save();
        $this->refHeader = $record -> id;
        $this->refManager= $curUser-> id;
    }

/**********************/
    public function loadDocsFromUrl()        
    {
        mb_internal_encoding("UTF-8");        
        $this->debug=[];    
        
       /* Получим список загруженных документов */ 
        $strSql="SELECT id, docIntNum, docURI, docSignature FROM   {{%documents}}";
        $list= Yii::$app->db->createCommand($strSql)->queryAll();
        /*разворачиваем в хэш, где ключ urlSignature */
        $N= count($list);
        $this->baseData=array();
        for ($i=0; $i< $N; $i++)
        {
          $key=$list[$i]['docIntNum'].$list[$i]['docURI'];
            $this->baseData[$key]['id'] =$list[$i]['id'];
            $this->baseData[$key]['md5']=$list[$i]['docSignature'];        
        }
     // $this->debug[]=$this->csvUrl;        
        $this->saveHeader();
        $page = $this->get_web_page( $this->csvUrl );    
        $content = mb_split('\r\n', $page['content'] );
        $n = count($content);        
        for ($i=1;$i< $n; $i++)
        { 
          $this->processDocRecord($content[$i], $i);       
        }
        
        
         /*цепляем ссылку на организацию*/
      $strsql= "UPDATE {{%documents}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} group by orgINN) as org
            set refOrg = org.id where  ifnull(refOrg,0) = 0 AND org.orgINN = {{%documents}}.orgINN AND org.n = 1;";
      Yii::$app->db->createCommand($strsql)->execute();    
 
    }

 /**************************/

    public function processDocRecord($parse_string, $str_index)
    {    
            mb_internal_encoding("UTF-8");
            
            $parse = str_getcsv($parse_string,",");    
            $n = count($parse);
            if (empty($parse[3])) return false; // строка не содержит обязательного поля
            $md5 = md5($parse_string);            
            if ($n < 15) return false; // нехватка полей
            for($j=0; $j< $n; $j++) {$parse[$j]=trim($parse[$j]);}
            
            return $this->recordProcess($parse, $md5);
            //return true;
     }       

     public function recordProcess($parse, $md5)
     {                
            $urlSignature = trim($parse[0]).trim($parse[8]);
            /*проверяем наличие и необходимость апдейтить*/
            if (isset($this->baseData[$urlSignature]['id']))
            {
              if ($this->baseData[$urlSignature]['md5']==$md5) return true;//шансы на случайное совпадение у изменившихся документов ничтожны
               $record = TblDocuments::findOne($this->baseData[$urlSignature]['id']);
               if (empty($record))$record = new TblDocuments();
            }
            else  $record = new TblDocuments();
            if (empty($record)) return false;
            
            $record -> refDocHeader = $this->refHeader;
            $record -> refManager   = $this->refManager;
            
            $record -> docIntNum    = mb_substr($parse[0],0,20,'utf-8');    //` VARCHAR(20)   'Номер регистрации документа',
            $regTimeStr =mb_substr($parse[1],0,10,'utf-8');  //                 уберем точку сзади          
            $regTimeStr.=" ".preg_replace("/\-/u",":",$parse[2]).":00"; // добавили часы и минуты
            $regTime=strtotime($regTimeStr);          
            $record -> regDateTime  = date("Y-m-d H:i:s", $regTime);                  //` DATETIME      'Дата и время регистрации',            
            $record -> orgTitle     = mb_substr($parse[3],0,150,'utf-8');   //` VARCHAR(150)  'наименование контрагента',            
            $record -> docTitle     = mb_substr($parse[4],0,150,'utf-8');   //` VARCHAR(150)  'Название' например 'счет'
            $record -> docOrigNum   = mb_substr($parse[5],0,50,'utf-8');    //` VARCHAR(50)   'номер документа (оригинальная нумерация)',
            $docTime=strtotime(mb_substr($parse[6],0,10,'utf-8'));          //                 уберем точку сзади
            $record -> docOrigDate  = date('Y-m-d', $docTime);              //` DATE          'Оригинальная дата',
            //$record -> docSum       = preg_replace("/[\D|\.]/u","",$parse[7]); //             'сумма если есть',
              
            $sum =  (float)str_replace(',', '.',$parse[7]); 
            $record -> docSum       = $sum; //             'сумма если есть',
            $record -> docURI       = mb_substr($parse[8],0,1024,'utf-8');  //` VARCHAR(1024) 'Путь к документам',
            $record -> docGoal      = mb_substr($parse[9],0,50,'utf-8');    //` VARCHAR(50)   'целевое подразделение - куда отнести счет',
            $record -> docOwner     = mb_substr($parse[10],0,50,'utf-8');    //` VARCHAR(50)   'отдел - владелец - куда первичку', 
            //если есть хозяин счета то финансовый                       
            if(empty($parse[10]))   $record -> isFinance = 0;               //` TINYINT(4)  'финансовый',
                              else  $record -> isFinance = 1;
           //если есть хозяин первички то оплачиваемый
            if(empty($parse[11]))   $record -> isOplate = 0;                //` TINYINT(4)  'Оплачиваемый',
                              else  $record -> isOplate = 1;

            if ($parse[12] == 'копия') $record -> docOrigStatus = 1;        //` INTEGER(11) DEFAULT 0 COMMENT '0-оригинал\r\n1-копия\r\n2-скан', 
            if ($parse[12] == 'скан')  $record -> docOrigStatus = 2;             
            $record -> orgINN       = mb_substr($parse[13],0,20,'utf-8');   //            
            $record -> docNote      = mb_substr($parse[14],0,250,'utf-8');  //` VARCHAR(250)  'комментарий',            
            $record -> ref1C_input  = mb_substr($parse[15],0,50,'utf-8');   //` VARCHAR(50)   'ссылка на входящий номер в 1с',
            $record -> ref1C_schet  = mb_substr($parse[16],0,50,'utf-8');   //` VARCHAR(50)   'номер счета в 1с',
            
            if (trim($parse[18])== 'поставщик') $record -> contragentType = 2;
            if (trim($parse[18])== 'банк') $record -> contragentType = 3;
            
            if (trim($parse[19])== 'услуги') $record -> contragentType = 4;
/*            if (trim($parse[19])== 'кредит погашение') $record -> contragentType = 3;
            if (trim($parse[19])== 'кредит-проценты') $record -> contragentType = 3;

            if (trim($parse[19])== 'услуги') $record -> opertationType = 1;
            if (trim($parse[19])== 'кредит погашение') $record -> contragentType = 2;
            if (trim($parse[19])== 'кредит-проценты') $record -> contragentType = 3;
*/            
                        
            $record -> isTTN = intval($parse[20]);
            $record -> isUTR = intval($parse[21]);
            
            $record -> urlSignature = $urlSignature;
            $record -> docSignature = $md5;
            $record -> save();

            //$record -> refOrg       = mb_substr($parse[0],0,20);  //` BIGINT(20)   'ссылка на контрагента',
            //$record -> docType      = mb_substr($parse[3],0,20);    //` BIGINT(20) DEFAULT NULL COMMENT 'тип документа',

            return true;            
    }
    
    
    
        
/**/
    
  /*
      ALTER TABLE `rik_documents` CHANGE COLUMN `ref1C` `ref1C_input` VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ссылка на входящий номер в 1с';
      ALTER TABLE `rik_documents` ADD COLUMN `ref1C_schet` VARCHAR(50) DEFAULT NULL COMMENT 'номер счета в 1с' AFTER `ref1C`;
      ALTER TABLE `rik_documents` ADD COLUMN `contragentType` INTEGER(11) DEFAULT 0 COMMENT 'тип контрагента\r\n0- клиент\r\n1-поставщик\r\n2-банк';
      ALTER TABLE `rik_documents` ADD COLUMN `opertationType` INTEGER(11) DEFAULT 0 COMMENT '0-товар\r\n1-услуги\r\n2-кредит погашение\r\n3-кредит-проценты';
      ALTER TABLE `rik_documents` ADD COLUMN `isTTN` TINYINT DEFAULT 0 COMMENT 'ТТН/акт';
      ALTER TABLE `rik_documents` ADD COLUMN `isUTR` TINYINT DEFAULT 0 COMMENT 'УТР';
      ALTER TABLE `rik_documents` MODIFY COLUMN `urlSignature` VARCHAR(1024) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'сигнатура для ссылки на догумент';
      ALTER TABLE `rik_documents` MODIFY COLUMN `orgINN` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL;      
  */ 
    
  /************End of model*******************/ 
 }
