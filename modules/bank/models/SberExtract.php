<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\GetExtract;

use unyii2\imap\ImapConnection;
use unyii2\imap\Mailbox;

/**
 * AuroraExtract - модель работы с выписками из банка - получаем из почты
 
 */
 
 class SberExtract extends Model
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
sber@a0202654.xsph.ru
Пароль
emfWuxRs

Сервер POP3/IMAP
mail.a0202654.xsph.ru

Сервер SMTP
smtp.a0202654.xsph.ru
*/
    
/*************************************/  
public function getExtractAttach()
   {

    $host = 'mail.a0202654.xsph.ru';
    $protocol = 'imap/novalidate-cert';
    $port = 143;
    $userid = 'sber@a0202654.xsph.ru';
    $userpasswd = 'emfWuxRs';
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
    $xlsExtractModel= new BankExtract();
    $xlsExtractModel -> webSync = false;
     
    $mailIds = $mailbox->searchMailBox('ALL');
    foreach($mailIds as $mailId)
    {
    // Returns Mail contents
    $mail = $mailbox->getMail($mailId); 
    //echo $mail->textHtml;     
    
     
    $type=0;
    $parse=explode (" ",$mail->textPlain);
    if ($parse[0] == "Выписка")      $type= 1; //По требованию
    else
    {              
      $this->uploadExtraxt($mail->textHtml); 
    }

    // Returns mail attachements if any or else empty array
    $attachments = $mail->getAttachments(); 
    foreach($attachments as $attachment)
    {                               
      $fname = $attachment->filePath;
      if ($type == 1)
      {        
        $xlsExtractModel -> loadBankExtract($fname);
      }
        // Delete attachment file
      //echo $attachment->filePath;  
      if (file_exists ($attachment->filePath) )unlink($attachment->filePath);
    }
      $mailbox->deleteMail($mailId); // Deletes all marked mails
    }
        $mailbox->expungeDeletedMails();   
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


    $xlsExtractModel= new BankExtract();
    $xlsExtractModel -> webSync = false;  
    $filelist = glob($folder."/*.xls*");      
    foreach ($filelist as $fname){        
        $xlsExtractModel -> loadBankExtract($fname);        
    }
    
   $this->removeDirectory($folder); 
} 

public  function removeDirectory($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
       }
    }
    rmdir($dir);
  }

  /************End of model*******************/ 
 }
