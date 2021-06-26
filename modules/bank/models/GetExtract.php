<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\ClientBankExchange;
use app\modules\bank\models\BankExtractAssign;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;



use unyii2\imap\ImapConnection;
use unyii2\imap\Mailbox;

/**
 * GetExtract - модель работы с выписками из банка - получаем из почты
 
 */
 
 class GetExtract extends Model
{
    
    public $timeshift = 4*3600; //сдвиг по времени   
    public $showDate = 0;
    public $extractRef = 0;
    public $debug;
    public $maxRowDetail=20;
        
    public function rules()
    {
        return [                              
            //[[ ], 'default'],                        
            //[['userFIO'], 'safe'],            
        ];
    }
/*

Имя пользователя
sta@erp-system.ru
Пароль
UPtBjjM9cj

Сервер POP3/IMAP
mail.erp-system.ru

Сервер SMTP
smtp.erp-system.ru
ALTER TABLE `rik_bank_extract` ADD COLUMN `refContent` BIGINT DEFAULT 0 COMMENT 'Откуда взяли';
*/
    
/*************************************/  
public function getExtractAttach()
   {
    
    $ret= [
        'res' => false,
        'fileAttach' => [],
        'extractLoaded' => 0
    ]; 
    
    $host = 'mail.erp-system.ru';
    $protocol = 'imap/novalidate-cert';
    $port = 143;
    $userid = 'sta@erp-system.ru';
    $userpasswd = 'UPtBjjM9cj';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $msgFolder = 'INBOX';
    //$imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder, $userid, $userpasswd) or die(imap_last_error());


    $imapConnection = new ImapConnection();
    $imapConnection->imapPath = '{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder;
    $imapConnection->imapLogin = $userid;
    $imapConnection->imapPassword = $userpasswd;
    $imapConnection->serverEncoding = 'utf-8'; // utf-8 default.
    $imapConnection->attachmentsDir = (realpath(dirname(__FILE__)))."/../uploads/";

    $mailbox = new Mailbox($imapConnection);
    $mailbox->saveAttach = true;
//    $mailbox->readMailParts = false; 
/*
    Получим список полученных сообщений
*/
    $extractModel= new ClientBankExchange();
     
    $mailIds = $mailbox->searchMailBox('ALL');
    foreach($mailIds as $mailId)
    {
    // Returns Mail contents
    $mail = $mailbox->getMail($mailId); 
    //echo $mail->textHtml;     
     
    $type=1;
 /*   $parse=explode (" ",$mail->textPlain);
    if ($parse[0] == "Выписка")      $type= 1; //По требованию
    else
    {              
      $this->uploadExtraxt($mail->textHtml); 
    }*/

    // Returns mail attachements if any or else empty array
    $attachments = $mail->getAttachments(); 
    foreach($attachments as $attachment)
    {                               
      $fname = $attachment->filePath;
      if ($type == 1)
      {        
        $extractModel -> loadFileExchange($fname);
      }
        // Delete attachment file
        $ret['fileAttach'][] = $attachment->filePath;  
      if (file_exists ($attachment->filePath) )unlink($attachment->filePath);
    }
      $mailbox->deleteMail($mailId); // Deletes all marked mails
    }
        $mailbox->expungeDeletedMails();   
      $ret['extractLoaded'] = count($extractModel->documentArray);
      $extractModel->save();   
      
      
           
      $ret['res'] = true;
      
      return $ret;
 }   
  

  public function postProcessExtract()
  {
  /*Выдергиваем уникальные*/
  $strsql= "INSERT INTO {{%bank_extract}}
 ( recordDate, debetRS, debetINN, debetOrgTitle, creditRs, creditINN, creditOrgTitle, debetSum,
  creditSum, docNum, contrAgentBank, description, VO,   
  reasonDocType,  reasonDocNum,   reasonDocDate,  reasonText, refContent)
 ( SELECT DISTINCT  a.recordDate, a.debetRS, a.debetINN, a.debetOrgTitle, a.creditRs, a.creditINN, a.creditOrgTitle,
 a.debetSum, a.creditSum, a.docNum, a.contrAgentBank, a.description, a.VO,
 a.reasonDocType,  a.reasonDocNum,   a.reasonDocDate,  a.reasonText, a.id
 from {{%bank_content}} as a  left join {{%bank_extract}} as b on 
 (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )  where b.id is null )";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*связываем с уникальными*/
   $strsql= "Update {{%bank_content}}  as a left join {{%bank_extract}} as b on 
   (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )
   SET a.refExtract = b.id  where   a.refExtract =0 and  b.id is not null"; 
   Yii::$app->db->createCommand($strsql)->execute();    
  
  
  /*метим Доходы*/
  $strsql= "update {{%bank_extract}} set extractType = 1  where creditSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*метим расходы*/
  $strsql= "update {{%bank_extract}} set extractType = 2,  contragentType = 1 where debetSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*цепляем ссылку на организацию*/
  $strsql= "UPDATE {{%bank_extract}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} group by orgINN) as org
    set orgRef = org.id where  ifnull(orgRef,0) = 0 AND org.orgINN = {{%bank_extract}}.debetINN AND org.n = 1 AND extractType = 1;";
  Yii::$app->db->createCommand($strsql)->execute();    
  
  $strsql= "UPDATE {{%bank_extract}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} group by orgINN) as org
    set orgRef = org.id where  ifnull(orgRef,0) = 0 AND org.orgINN = {{%bank_extract}}.creditINN AND org.n = 1 AND extractType = 2;";
  Yii::$app->db->createCommand($strsql)->execute();    

  /*пытаемся распределить */
  
  $assignModel= new BankExtractAssign();
  $assignModel -> scanExtract();

  }    
  
  
public function uploadExtraxt($textHtml)
{
       $url="";
    
        //$dom = new \DOMDocument;	//создаем объект		
		//$dom->loadHTML($mail->textHtml);	//не работает - а потому что html корявый
      $parse=preg_split("/<a|<\/a>/",$textHtml); 
  //print_r($parse);    
      for ($i=0; $i< count($parse); $i++)
      { 
        if (preg_match("/Скачать/", $parse[$i]))
        {
//          echo "\n".$parse[$i]."\n";  
          $subParse=explode (" ",$parse[$i]);         
          for($j=0; $j<count($subParse); $j++)
              if (preg_match("/href/",$subParse[$j])) {$url = $subParse[$j]; break;}
          $url = preg_replace('/href/','', $url);
          $url = preg_replace('/\=/','', $url);
          $url = preg_replace('/\"/','', $url);          
 		}
      }
    //$url = "http://"."10.6.5.193/test/Выписка РТБ СБ.zip";  
    //$url = "http://"."a0202654.xsph.ru/rik/test/Выписка РТБ СБ.zip";  
    //echo "\n".$url."\n";
    /*$urlList = explode ("/",$url);  
    //Кодируем русские символы
    $url = $urlList[0];    
    for ($i=1;$i<count($urlList);$i++)
    {
      $url .= rawurlencode($urlList[$i])."/";        
    }
    $url = substr($url, 0, -1); //откусим последний слеш
    */    
    echo $url."\n";
    //return;
    if (empty ($url)) return;
    $ch = curl_init($url );
    $fname = realpath(dirname(__FILE__))."/../uploads/extract".time().".zip";
    echo $fname;
    $fp = fopen($fname, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    $zip = new \ZipArchive();    
    $folder=realpath(dirname(__FILE__))."/../uploads/".time();
    mkdir($folder);
    $zip->open($fname, \ZipArchive::CREATE);
    $zip->extractTo($folder);
    $zip->close();
    unlink ($fname);


  /*  $xlsExtractModel= new BankExtract();
    $xlsExtractModel -> webSync = false;  
    $filelist = glob($folder."/*.xls*");      
    foreach ($filelist as $fname){        
        $xlsExtractModel -> loadBankExtract($fname);        
    }
    
   $this->removeDirectory($folder); */
} 

public  function removeDirectory($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
       }
    }
    rmdir($dir);
  }
/********************/
  
  /* Данные по выписке */
 public function getExtractData($sd, $ed)
   {
    
    $query  = new Query();
    $query->select ([
            'a.id',
            'a.recordDate',
            'a.debetOrgTitle',            
            'a.debetRS',
            'b.debetBIK',            
            'a.debetINN',
            'b.debetKPP',            
            'a.creditOrgTitle',            
            'a.creditRs',      
            'b.creditBIK',                        
            'a.creditINN',
            'b.creditKPP',            
            'a.debetSum',
            'a.creditSum',
            'a.docNum',
            'b.docDate',
            'a.contrAgentBank',
            'a.description',
            'a.extractType',
            'a.reasonDocNum',
            'a.reasonDocDate',
            'a.reasonText',
            'b.docType'
            ])            
            ->from("{{%bank_extract}} as a")
            ->leftJoin ("{{%bank_content}} as b", "b.id = a.refContent")
            ;
    $query->andWhere("a.recordDate >= :sd");
    $query->andWhere("a.recordDate <  :ed");
    
    $query->addParams([
                       ':sd' => date('Y-m-d', strtotime($sd)),
                       ':ed' => date('Y-m-d', strtotime($ed))
                     ]);
    
    $list = $query->createCommand()->queryAll();
    
    return $this->utfExtractPrint($list);
    
    //$this->debug[]=$list;
    
   }
   
   public function utfExtractPrint($list)
   {

     //$fp = fopen('php://output', 'w');
     $fp = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

        /*$mask = realpath(dirname(__FILE__))."/../uploads/extractAuroraData*.csv";
        array_map("unlink", glob($mask));       
        $fname = "uploads/extractAuroraData".time().".csv";
        $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
        if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;*/
     
        $col_title = array (
        
        "Дата проводки",	
        "Дата документа",	
        "Номер документа",	

        
        "Сумма входящая",	
        "Сумма исходящая",	
        "Назначение платежа",	
        
        "Наименование плательщика",	
        "Счет плательщика",	
        "ИНН плательщика",	
        "КПП плательщика",	
        "Бик банка плательщика",	
        
        "Наименование получателя",	
        "Счет получателя",	
        "ИНН получателя",	
        "КПП получателя",	
        "Бик банка получателя",	

        "Наименование банка контрагента",	
        "Тип документа",	
        );
        
        fputcsv($fp, $col_title, ","); 
        
        
        for ($i=0;$i< count($list);$i++)
        {
            
            $list[$i]['creditSum'] = preg_replace("/\./",",",$list[$i]['creditSum']);            
            $list[$i]['debetSum']= preg_replace("/\./",",",$list[$i]['debetSum']);
    
    if (empty($list[$i]['reasonText'])) $reasonText =   $list[$i]['description'];
                                  else $reasonText =   $list[$i]['reasonText'];
    
         $rowArray = array 
            (
            $list[$i]['recordDate'],
            $list[$i]['docDate'],
            $list[$i]['docNum'],

            $list[$i]['creditSum'],
            $list[$i]['debetSum'],
            $reasonText,

            $list[$i]['debetOrgTitle'],
            $list[$i]['debetRS'],
            $list[$i]['debetINN'],
            $list[$i]['debetKPP'],                       
            $list[$i]['debetBIK'],           
            
            $list[$i]['creditOrgTitle'],
            $list[$i]['creditRs'],
            $list[$i]['creditINN'],
            $list[$i]['creditKPP'],                       
            $list[$i]['creditBIK'],           
            
            $list[$i]['contrAgentBank'],
            $list[$i]['docType'],
            
             );
        fputcsv($fp, $rowArray,","); 
        }
      rewind($fp);
     //fclose($fp);  
     $output = stream_get_contents($fp);
     //$output = file($fnamePath);
     //echo $output;
     return $output;

   }
   

  
  
  /************End of model*******************/ 
 }
