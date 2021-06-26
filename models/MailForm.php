<?php

namespace app\models;

use Yii;
use yii\base\Model;

use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;

use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

use unyii2\imap\ImapConnection;
use unyii2\imap\Mailbox;

use app\models\ContactList;
use app\models\OrgList;
use app\models\TblMail;
use app\models\User;
/**
    Отсылка почты
    
    5mhO6udBx4
    
    
Имя пользователя
sber@a0202654.xsph.ru
Пароль
emfWuxRs

extract@a0202654.xsph.ru
Пароль
5mhO6udBx4

Сервер POP3/IMAP
mail.a0202654.xsph.ru

Сервер SMTP
smtp.a0202654.xsph.ru
   
 */
class MailForm extends Model
{
    
    public $orgId = 0;
    public $email ="";
    public $subject="";
    public $body="";
    public $errMsg="";
    public $from="";

    public $isDel=0;
    public $isZakaz=0;
    public $isSupplier=0;

    
    public $userFIO;
    public $requestFIO=0;
    public $orgTitle;
    public $msgFolder;

    public $id;
    public $refContact =0;
    
    public $fromEmail;// = zakaz@rik-nsk.ru;
    //zakaz@rik-nsk.ru 
    //"vv@softberry.ru";
    //"Y3su@rik-nsk.ru";
    //"7648790@mail.ru";    
    public $listAttached = array();
    
    public $debug=array();
    
    public function rules()
    {
        return [
            [['email', 'subject', 'body', 'orgId'], 'default'],
            [['isDel', 'userFIO', 'orgTitle', 'msgFolder', 'refContact', 'isZakaz', 'isSupplier'], 'safe'],
            [['email', 'subject'], 'trim'],
            [['email'], 'email'],
            //[['attachFile'], 'file', 'skipOnEmpty' => false],
        ];
    }

/**********************************************/    
/****** Service functions *********************/    
/**********************************************/

/* Получить ключ из параметров*/
    public function getCfgValue($key)        
   {
     $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
            ':key' => intval($key),            
            ])->queryOne();  
            
    return $record['keyValue'];
   }

   
/* Регистрируем почтовое сообщение как контакт в базе */
    public function registerMail ()
    {
         
       if (!empty($this->orgId))
        {
          $record = OrgList::findOne($this->orgId);
          if(empty($record)) return false;
          $record->contactEmail = $this->email;
          $record->save();
            
          $curUser=Yii::$app->user->identity;
          $contact = new ContactList();
          $contact->ref_org = $this->orgId;
          $contact->ref_user = $curUser->id;
          $contact->contactDate = date("Y.m.d h:i:s");                    
          $contact->contactFIO = $this->subject;
          $contact->contactEmail= $this->email;
          $contact->note = $this->body;
          $contact->save();
         
          return true; 
        }

         return false;
    }

    /**********/    
    
    public function sendMail()
    {

    $curUser=Yii::$app->user->identity; 
    /*Crytical*/
    if (empty($this->email  )){  $this->errMsg = "Empty mail adress";    return false;}
    if (empty($this->subject)){  $this->errMsg = "Empty subject";    return false;}
    if (empty($this->body))   {  $this->errMsg = "Empty Body";    return false;}
    
    /*Допустимо*/
    if (empty($this->from  )) { 
        if (empty ($curUser->email)) $this->from = $this->getCfgValue(1000);
                                else $this->from = $curUser->email;
                              }

    /* Внутренняя кодировка приложения у нас utf-8, посколько часть почтовых программ 
    ее не воспринимают адекватно, то переведем в Win-1251  */    
    $pr = new HtmlPurifier;
    //$subject = iconv("UTF-8", "Windows-1251",$pr->process($this->subject));
    //$textBody    = iconv("UTF-8", "Windows-1251",$pr->process($this->body));
    $subject  = $pr->process($this->subject);
    $textBody = $pr->process($this->body);    
    
/*    echo $subject;    echo $textBody;*/
    
    $message = Yii::$app->mailer->compose()
        ->setFrom($this->from)
        ->setTo($this->email)
        ->setSubject($subject)
        ->setTextBody($textBody)
        ->setHtmlBody($this->body);
     
    $uploadPath=(realpath(dirname(__FILE__)))."/../attach/";    
    
    for ($i=0; $i<count($this->listAttached); $i++)
        {            
        $filename = $this->listAttached[$i];
        // название файла

        $filepath = $uploadPath.$this->listAttached[$i];
        // месторасположение файла
        
        $message->attach($filepath);    
        }
    
    $message->send();
    
    $this->registerMail();
    
    return true;
    
    
   }
     
/**********/
     
     public function sendMailInternal()
     {
         
        if (!empty($this->orgId))
        {
          $record = OrgList::findOne($this->orgId);
          $record->contactEmail = $this->email;
          $record->save();
            
          $curUser=Yii::$app->user->identity;
          $contact = new ContactList();
          $contact->ref_org = $this->orgId;
          $contact->ref_user = $curUser->id;
          $contact->contactDate = date("Y.m.d h:i:s");                    
          $contact->contactFIO = $this->subject;
          $contact->contactEmail= $this->email;
          $contact->note = $this->body;
          $contact->save();
          
        }
       
        if ($this->email == "") {return;}
        $subject = $this->subject; 
        // текст сообщения, здесь вы можете вставлять таблицы, рисунки, заголовки, оформление цветом и т.п.
        
        $message  = "<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body><pre>\n";
        $message .=$this->body; 
        $message .="</pre></body></html>"; 
        
        $user_email = $this->getCfgValue(1001);//$this->fromEmail; ;
        
        $uploadPath=(realpath(dirname(__FILE__)))."/../attach/";
        

        //Письмо с вложением состоит из нескольких частей, которые разделяются разделителем

        $boundary = "--".md5(uniqid(time())); 
        // генерируем разделитель

        $mailheaders = "MIME-Version: 1.0;\r\n"; 
        $mailheaders .="Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"; 
        // разделитель указывается в заголовке в параметре boundary 

        $mailheaders .= "From: $user_email <$user_email>\r\n"; 
        $mailheaders .= "Reply-To: $user_email\r\n"; 

        $multipart = "--$boundary\r\n"; 
        $multipart .= "Content-Type: text/html; charset=utf8\r\n";
        $multipart .= "Content-Transfer-Encoding: base64\r\n";    
        $multipart .= "\r\n";
        //$multipart .= chunk_split(base64_encode(iconv("utf8", "windows-1251", $message)));
        $multipart .= chunk_split(base64_encode($message));
        // первая часть само сообщение
 
        // Закачиваем файлы
        for ($i=0; $i<count($this->listAttached); $i++)
        {            
        $filename = $this->listAttached[$i];
        // название файла

        $filepath = $uploadPath.$this->listAttached[$i];
        // месторасположение файла

        $fp = fopen($filepath,"r"); 
        if (!$fp) 
        { 
            print "Не удается открыть файл ".$this->listAttached[$i];             
            exit(); 
        } 
        $file = fread($fp, filesize($filepath)); 
        fclose($fp); 
        unlink ($filepath);
        // чтение файла


        $message_part = "\r\n--$boundary\r\n"; 
        $message_part .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";  
        $message_part .= "Content-Transfer-Encoding: base64\r\n"; 
        $message_part .= "Content-Disposition: attachment; filename=\"$filename\"\r\n"; 
        $message_part .= "\r\n";
        $message_part .= chunk_split(base64_encode($file));
        $message_part .= "\r\n--$boundary--\r\n";
        // второй частью прикрепляем файл, можно прикрепить два и более файла
        $multipart .= $message_part;
        }

        //$to = $this->email.", ".$this->getCfgValue(1003);
        //$success =mail($to,$subject,$multipart,$mailheaders);

        $success =mail($this->email,$subject,$multipart,$mailheaders);
        if (!$success) {
            $errorMessage = error_get_last()['message'];
            
            $session = Yii::$app->session;        
            $session->open();
                $problemMessage    ="Электронная почта не может быть отправлена:<pre>".$errorMessage."</pre>";
                $session->set('problemMessage', $problemMessage);                         
        return $success;        
        }
        
        $success =mail($this->getCfgValue(1003),$subject,$multipart,$mailheaders);
        if (!$success) {
            $errorMessage = error_get_last()['message'];
            
            $session = Yii::$app->session;        
            $session->open();
                $problemMessage    ="Электронная почта не может быть отправлена:<pre>".$errorMessage."</pre>";
                $session->set('problemMessage', $problemMessage);                         
        }

        return $success;        
        
   }


     public function sendExtMail($email, $subject, $body, $fromEmail, $listAttached)
     {
   
   
        if (!empty($this->orgId))
        {
          $record = OrgList::findOne($this->orgId);
          
          $curUser=Yii::$app->user->identity;
          $contact = new ContactList();
          $contact->ref_org = $this->orgId;
          $contact->ref_user = $curUser->id;
          $contact->contactDate = date("Y.m.d h:i:s");                    
          $contact->contactFIO = $subject;
          $contact->contactEmail= $email;
          $contact->note = $subject;
          $contact->save();          
        }
       
   
        if ($email == "") {return;}
        $subject = $subject; 
        // текст сообщения, здесь вы можете вставлять таблицы, рисунки, заголовки, оформление цветом и т.п.
        $message =$body; 
        
        $user_email = $fromEmail; 
        
        $uploadPath=(realpath(dirname(__FILE__)))."/../attach/";
        

        //Письмо с вложением состоит из нескольких частей, которые разделяются разделителем

        $boundary = "--".md5(uniqid(time())); 
        // генерируем разделитель

        $mailheaders = "MIME-Version: 1.0;\r\n"; 
        $mailheaders .="Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"; 
        // разделитель указывается в заголовке в параметре boundary 

        $mailheaders .= "From: $user_email <$user_email>\r\n"; 
        $mailheaders .= "Reply-To: $user_email\r\n"; 

        $multipart = "--$boundary\r\n"; 
        $multipart .= "Content-Type: text/html; charset=utf8\r\n";
        $multipart .= "Content-Transfer-Encoding: base64\r\n";    
        $multipart .= "\r\n";
        //$multipart .= chunk_split(base64_encode(iconv("utf8", "windows-1251", $message)));
        $multipart .= chunk_split(base64_encode($message));
        // первая часть само сообщение
 
        // Закачиваем файлы
        for ($i=0; $i<count($listAttached); $i++)
        {            
        $filename = $listAttached[$i];
        // название файла

        $filepath = $uploadPath.$listAttached[$i];
        // месторасположение файла

        $fp = fopen($filepath,"r"); 
        if (!$fp) 
        { 
            print "Не удается открыть файл ".$listAttached[$i];             
            exit(); 
        } 
        $file = fread($fp, filesize($filepath)); 
        fclose($fp); 
        unlink ($filepath);
        // чтение файла


        $message_part = "\r\n--$boundary\r\n"; 
        $message_part .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";  
        $message_part .= "Content-Transfer-Encoding: base64\r\n"; 
        $message_part .= "Content-Disposition: attachment; filename=\"$filename\"\r\n"; 
        $message_part .= "\r\n";
        $message_part .= chunk_split(base64_encode($file));
        $message_part .= "\r\n--$boundary--\r\n";
        // второй частью прикрепляем файл, можно прикрепить два и более файла
        $multipart .= $message_part;
        }

        $to = $email;
        $success = mail($to,$subject,$multipart,$mailheaders);
        if (!$success) {
            $errorMessage = error_get_last()['message'];
            
            $session = Yii::$app->session;        
            $session->open();
                $problemMessage    ="Электронная почта не может быть отправлена:<pre>".$errorMessage."</pre>";
                $session->set('problemMessage', $problemMessage);                         
        }
        return $success;
   }

/**************************/   
        
public function getServerMailProvider()		
   {
   
     
   //  $imap = imap_open('{imap.yandex.ru:993/imap/ssl}INBOX', 'vvmol-nsk', 'Anatolica');
   //     $imap = imap_open('{imap.mail.ru:993/imap/ssl}INBOX', 'rik-nsk', '777Rut3630829');  
   


    $host = 'imap.mail.ru';
    $protocol = 'imap/ssl/novalidate-cert';
    $port = 993;
    $userid = 'rik-nsk@mail.ru';
    $userpasswd = '777Rut3630829';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}INBOX', $userid, $userpasswd) or die(imap_last_error());
   
     
     $mails_id = imap_search($imap, 'SINCE "2020-02-20"');
     
$dataArray = [];     
     foreach ($mails_id as $num) {

        // Заголовок письма
        $header = imap_header($imap, $num);
        $header = json_decode(json_encode($header), true);
        if (isset ($header['subject']))$header['subject'] = mb_decode_mimeheader($header['subject']);
        else continue;
        if (isset ($header['fromaddress']))$header['fromaddress'] =mb_decode_mimeheader($header['fromaddress']);
        else continue;

     $dataArray[] = $header;
        // Тело письма
        //$body = imap_body($imap, $num);
        //var_dump($body);

    }
    imap_close($imap);

$this->debug[] = $dataArray;
       
      $provider = new ArrayDataProvider([
            'allModels' => $dataArray,
            'totalCount' => count($dataArray),
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [            
            'date',            
            'subject'
            ],

            'defaultOrder' => [    'date' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
/*********************************/   
public function getMailProvider($params)		
   {
     $query  = new Query();
        $query->select ([
            '{{%mail}}.id',
            'msgDate',
            'msgSubject',
            'message_id',
            'msgFrom',
            'email',
            'msgTime',
            'msgFolder',
            'msgBody',
            '{{%mail}}.refContact',
            'refOrg',
            'title as orgTitle',
            'userFIO',
            'refZakaz', 
            'isDel',
            'isZakaz',
            'isSupplier'
            ])
             ->from("{{%mail}}")               
             ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%mail}}.refOrg')
             ->leftJoin('{{%user}}','{{%user}}.id = {{%mail}}.refManager')
             ->leftJoin('{{%contact}}','{{%contact}}.id = {{%mail}}.refContact')
             ;
               

     $countquery  = new Query();
        $countquery->select (" count({{%mail}}.id)")
             ->from("{{%mail}}")               
             ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%mail}}.refOrg')
             ->leftJoin('{{%user}}','{{%user}}.id = {{%mail}}.refManager')
            ;

       
     if (($this->load($params) && $this->validate())) {
     
      $query->andFilterWhere(['like', 'email', $this->email]);
      $countquery->andFilterWhere(['like', 'email', $this->email]);

      $query->andFilterWhere(['like', 'title', $this->orgTitle]);
      $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
            
     }
      $curUser=Yii::$app->user->identity;     
      
      if (empty($this->userFIO) ) 
      if ($this->requestFIO >0) $this->userFIO = $this->requestFIO ;
                           else $this->userFIO = $curUser->id;
      if ($this->userFIO > 0){
        $query->andFilterWhere(['=', '{{%mail}}.refManager', $this->userFIO]);
        $countquery->andFilterWhere(['=', '{{%mail}}.refManager', $this->userFIO]);
      }elseif ($this->userFIO == -2)
      {
        $query->andFilterWhere(['=', 'ifnull({{%mail}}.refManager,0)', 0]);
        $countquery->andFilterWhere(['=', 'ifnull({{%mail}}.refManager,0)', 0]);
      }
      
    
    /* помечен для удаления*/     
     if (empty($this->isDel)) $this->isDel = 2;
     switch ($this->isDel)
     {
      case 2:    
        $query->andFilterWhere(['=', 'isDel',  0]);
        $countquery->andFilterWhere(['=', 'isDel',  0]);
      break;
      
      case 3:    
        $query->andFilterWhere(['=', 'isDel',  1]);
        $countquery->andFilterWhere(['=', 'isDel',  1]);
      break;      
     }

    /* помечен как поставщик*/     
     if (empty($this->isSupplier)) $this->isSupplier = 2;
     switch ($this->isSupplier)
     {
      case 2:    
        $query->andFilterWhere(['=', 'isSupplier',  0]);
        $countquery->andFilterWhere(['=', 'isSupplier',  0]);
      break;
      
      case 3:    
        $query->andFilterWhere(['=', 'isSupplier',  1]);
        $countquery->andFilterWhere(['=', 'isSupplier',  1]);
      break;      
     }
     
     
    /* помечен как заказ */          
     switch ($this->isZakaz)
     {
      case 2:    
        $query->andFilterWhere(['=', 'isZakaz',  0]);
        $countquery->andFilterWhere(['=', 'isZakaz',  0]);
      break;
      
      case 3:    
        $query->andFilterWhere(['=', 'isZakaz',  1]);
        $countquery->andFilterWhere(['=', 'isZakaz',  1]);
      break;      
     }
     
     
     
     switch ($this->refContact)
     {
      case 1:    
        $query->andFilterWhere(['=', 'ifnull({{%mail}}.refContact,0)',  0]);
        $countquery->andFilterWhere(['=', 'ifnull({{%mail}}.refContact,0)',  0]);
      break;
      
      case 2:    
        $query->andFilterWhere(['>', 'ifnull({{%mail}}.refContact,0)',  0]);
        $countquery->andFilterWhere(['>', 'ifnull({{%mail}}.refContact,0)',  0]);
      break;      
     }


     switch ($this->msgFolder     )
     {
      case 1:    
        $query->andFilterWhere(['=', 'msgFolder',  'INBOX']);
        $countquery->andFilterWhere(['=', 'msgFolder',  'INBOX']);
      break;
      
      case 2:    
        $query->andFilterWhere(['=', 'msgFolder',  'SENT']);
        $countquery->andFilterWhere(['=', 'msgFolder',  'SENT']);
      break;      
     }

     
     

     

     $command = $query->createCommand();     
     $count = $countquery->createCommand()->queryScalar();

   
     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 20,
               ],
               
               'sort' => [
               
               'attributes' => [
                'msgSubject',                                
                'email',
                'msgTime',
                'msgFolder',
                'orgTitle',
                'userFIO',
                'isDel',
                'isZakaz',
                'isSupplier'                
                ], 
                    'defaultOrder' => [ 'msgTime' => SORT_DESC ],                
               ],
               
          ]);


          
     return  $dataProvider;      
   }
/*********************************/
public function getMailData()		
   {
        $record = TblMail::findOne($this->id);
        if (empty($record)) return; //уже есть - не все попадают в список, а поиск тяжелый

    /*Загрузим тело*/ 
    $host = 'imap.mail.ru';
    $protocol = 'imap/ssl/novalidate-cert';
    $port = 993;
    $userid = 'rik-nsk@mail.ru';
    $userpasswd = '777Rut3630829';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $msgFolder = 'INBOX';
    $imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder, $userid, $userpasswd) or die(imap_last_error());
   
    $body = imap_body($imap, $num);
    $body = quoted_printable_decode($body);   

    imap_close($imap);

   }   
/*********************************/
public function createContactByMail($mailId)
{
  $curUser=Yii::$app->user->identity;
  $record = TblMail::findOne($mailId);  
   if (empty($record)) return 0;   
   if (!empty($record->refContact)) return $record->refContact;   
  
        $contact = new ContactList();         
        $contact->ref_user = $curUser->id;
        $contact->ref_org  = $record->refOrg;        
        $contact->note     = "[".$record->msgSubject."]\n".$record->msgBody;
        $contact->contactEmail = $record->email;
        $contact->contactFIO = $record->msgFrom;
        $contact->contactDate = date('Y-m-d H:i:s', $record->msgTime);
        $contact->contactStatus = 2;
        $contact->save();                 
    $record->refContact = $contact->id;
    $record->save();
    return     $record->refContact;
        
}
/*********************************/
public function getInboxMailList()		
   {

   //  $imap = imap_open('{imap.yandex.ru:993/imap/ssl}INBOX', 'vvmol-nsk', 'Anatolica');
   //  $imap = imap_open('{imap.mail.ru:993/imap/ssl}INBOX', 'rik-nsk', '777Rut3630829');  
   $ret=[
       'res' => false,
       'syncStart' => '',
       'synced'    => 0 ,  
   ];

    $host = 'imap.mail.ru';
    $protocol = 'imap/ssl/novalidate-cert';
    $port = 993;
    $userid = 'rik-nsk@mail.ru';
    $userpasswd = 'PTB17M$@ham';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $msgFolder = 'INBOX';
    //$imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder, $userid, $userpasswd) or die(imap_last_error());



    $imapConnection = new ImapConnection();

    $imapConnection->imapPath = '{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder;
    $imapConnection->imapLogin = $userid;
    $imapConnection->imapPassword = $userpasswd;
    $imapConnection->serverEncoding = 'utf-8'; // utf-8 default.
    $imapConnection->attachmentsDir = (realpath(dirname(__FILE__)))."/../incoming/";

    $mailbox = new Mailbox($imapConnection);
//    $mailbox->readMailParts = false; 
/*
    Получим список полученных сообщений
*/
    $maxLast = time()-10*24*3600;
    $lastTime = Yii::$app->db->createCommand("SELECT MAX(msgTime) FROM {{%mail}} WHERE msgFolder like 'INBOX' ") ->queryScalar();    
    if ($lastTime < $maxLast)$lastTime = $maxLast;
                     
    if (empty($lastTime)) $lastDate = '2020-01-01';
                    else  $lastDate = date('Y-m-d', $lastTime);
    $ret['syncStart'] = $lastDate;                
                    
    $refArray=array();
    $list = Yii::$app->db->createCommand("SELECT id, message_id FROM {{%mail}} where DATE(msgDate) >=:msgDate",
    [':msgDate' => $lastDate ]   ) ->queryAll();                    
               for($i=0; $i < count ($list); $i++)
               {               
                    $refArray[$list[$i]['message_id']]=$list[$i]['id'];                                              
               }          
    //$this->debug[]=$list;               
               unset ($list);          
    //$this->debug[]=$refArray;
     
    $mailIds = $mailbox->searchMailBox('SINCE "'.$lastDate.'"');
   
    foreach($mailIds as $mailId)
    {
    echo "\ncheck ";
    // Returns Mail contents
    $mail = $mailbox->getMail($mailId); 
    if (array_key_exists ($mail->messageId, $refArray) )  continue;       
        /*Пошло сохранение*/ 
        $record = TblMail::findOne(['message_id' => $mail->messageId]);        
        if (!empty($record)) continue; //уже есть - не все попадают в список, а поиск тяжелый
    echo " start ";        
        $record = new TblMail();
        $record->msgDate = $mail->date;
        
        /*Освободимся от смайликов и прочей хрени*/
        
        $subject = iconv("UTF-8", "Windows-1251//IGNORE",$mail->subject);        
        $subject = mb_substr($subject, 0 , 250,'utf-8');
        $record->msgSubject = iconv("Windows-1251", "UTF-8",$subject);
        
        $record->message_id = mb_substr($mail->messageId, 0 , 250,'utf-8');
        $record->msgFrom    = mb_substr($mail->fromName, 0 , 250,'utf-8');
        //чистый адрес отправителя == первый отправитель
        $record->email      = $mail->fromAddress;
        $record->msgTime    = strtotime($mail->date);
        $record->msgFolder  = $msgFolder;
        if (empty ( $mail->textPlain))  $mail->textPlain = ' ';
        $record->msgBody = substr($mail->textPlain,0,65000);
        $record->save();
        $ret['synced']++;
    echo $ret['synced']."\n";
    // Returns mail attachements if any or else empty array
  /*  $attachments = $mail->getAttachments(); 
    foreach($attachments as $attachment){
        echo ' Attachment:' . $attachment->name . PHP_EOL;        
        // Delete attachment file
       unlink($attachment->filePath);
    }*/
    
    }
 
    
//    imap_close($imap);

    $strSql = "UPDATE  {{%mail}} left join (SELECT DISTINCT email, ref_org FROM {{%emaillist}} ) as a 
    on a.email = {{%mail}}.email left join {{%orglist}} on {{%orglist}}.id = a.ref_org
    left join {{%user}} on {{%user}}.`id` ={{%orglist}}.refManager
    SET {{%mail}}.refOrg = a.ref_org, {{%mail}}.refManager = {{%orglist}}.refManager
    where {{%mail}}.refOrg = 0 and DATE(msgDate) >=:msgDate";

    Yii::$app->db->createCommand($strSql, [':msgDate' => $lastDate ])->execute();                     
    
    $ret['res']=true;
    return $ret;    
  }   

/*********************************/
public function getSentMailList()		
   {

   $ret=[
       'res' => false,
       'syncStart' => '',
       'synced'    => 0 ,  
   ];
   //  $imap = imap_open('{imap.yandex.ru:993/imap/ssl}INBOX', 'vvmol-nsk', 'Anatolica');
   //  $imap = imap_open('{imap.mail.ru:993/imap/ssl}INBOX', 'rik-nsk', '777Rut3630829');  

    $host = 'imap.mail.ru';
    $protocol = 'imap/ssl/novalidate-cert';
    $port = 993;
    $userid = 'rik-nsk@mail.ru';
    $userpasswd = 'PTB17M$@ham';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $msgFolder = '&BB4EQgQ,BEAEMAQyBDsENQQ9BD0ESwQ1-'; //Отправленные 
    //$imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder, $userid, $userpasswd) or die(imap_last_error());


    $imapConnection = new ImapConnection();

    $imapConnection->imapPath = '{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder;
    $imapConnection->imapLogin = $userid;
    $imapConnection->imapPassword = $userpasswd;
    $imapConnection->serverEncoding = 'utf-8'; // utf-8 default.
    $imapConnection->attachmentsDir = (realpath(dirname(__FILE__)))."/../incoming/";

    $mailbox = new Mailbox($imapConnection);
    //$this->debug[] = $mailbox;
//    $mailbox->readMailParts = false; 
/*
    Получим список полученных сообщений
*/
    $maxLast = time()-10*24*3600;
    $lastTime = Yii::$app->db->createCommand("SELECT MAX(msgTime) FROM {{%mail}} WHERE msgFolder like 'SENT' ") ->queryScalar();                     
    if ($lastTime < $maxLast)$lastTime = $maxLast;
    if (empty($lastTime)) $lastDate = '2020-03-01';
                    else  $lastDate = date('Y-m-d', $lastTime);
    
    $ret['syncStart'] = $lastDate;                
    $refArray=array();
    $list = Yii::$app->db->createCommand("SELECT id, message_id FROM {{%mail}} where DATE(msgDate) >=:msgDate",
    [':msgDate' => $lastDate ]   ) ->queryAll();                    
               for($i=0; $i < count ($list); $i++)
               {               
                    $refArray[$list[$i]['message_id']]=$list[$i]['id'];                                              
               }          
               unset ($list);          
    $mailIds = $mailbox->searchMailBox('SINCE "'.$lastDate.'"');
     
    foreach($mailIds as $mailId)
    {
    // Returns Mail contents
    $mail = $mailbox->getMail($mailId); 
     if (array_key_exists ($mail->messageId, $refArray) )  continue;       
   
        /*Пошло сохранение*/ 
                /*Освободимся от смайликов и прочей хрени*/
                $subject = mb_substr($mail->subject, 0 , 250,'utf-8');
                $subject = iconv("UTF-8", "Windows-1251",$subject);        
       
        foreach ($mail->to as $email => $val) 
        {       
            $record = TblMail::findOne(['message_id' => $mail->messageId]);
            if (!empty($record)) continue; //уже есть - не все попадают в список, а поиск тяжелый

            $record = new TblMail();
            $record->msgDate = $mail->date;
       
                $record->msgSubject = iconv("Windows-1251", "UTF-8",$subject);
        
                $record->message_id = mb_substr($mail->messageId, 0 , 250,'utf-8');
                $record->msgFrom    = mb_substr($val, 0 , 250,'utf-8');
                //чистый адрес получателя 
                $record->email      = $email;
                $record->msgTime    = strtotime($mail->date);
                $record->msgFolder  = 'SENT';
                if (empty ( $mail->textPlain))  $mail->textPlain = ' ';
                $record->msgBody = substr($mail->textPlain,0,65000);
            $record->save();
            $ret['synced']++;
//$this->debug[]=$record;                                    
        }
    // Returns mail attachements if any or else empty array
  /*  $attachments = $mail->getAttachments(); 
    foreach($attachments as $attachment){
        echo ' Attachment:' . $attachment->name . PHP_EOL;        
        // Delete attachment file
       unlink($attachment->filePath);
    }*/
    
    }
 
    
//    imap_close($imap);

    $strSql = "UPDATE  {{%mail}} left join (SELECT DISTINCT email, ref_org FROM {{%emaillist}} ) as a 
    on a.email = {{%mail}}.email left join {{%orglist}} on {{%orglist}}.id = a.ref_org
    left join {{%user}} on {{%user}}.`id` ={{%orglist}}.refManager
    SET {{%mail}}.refOrg = a.ref_org, {{%mail}}.refManager = {{%orglist}}.refManager
    where {{%mail}}.refOrg = 0 and DATE(msgDate) >=:msgDate";

    Yii::$app->db->createCommand($strSql, [':msgDate' => $lastDate ])->execute();                     
    
    $ret['res']=true;
    return $ret;

  }   
      
/*
rray
(
    [0] => Array
        (
            [0] => 0
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}INBOX
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}INBOX
        )

    [1] => Array
        (
            [0] => 1
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBcEFQQv- &BBEEOwQwBDMEPgQyBDUESQQ1BD0EQQQ6-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}ЗЕЯ Благовещенск
        )

    [2] => Array
        (
            [0] => 2
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BCEEQQRLBDsEOgQ4-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Ссылки
        )

    [3] => Array
        (
            [0] => 3
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BDEEQwRFBDMEMAQ7BEIENQRABDgETw-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}бухгалтерия
        )

    [4] => Array
        (
            [0] => 4
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBQEPgQzBD4EMgQ+BEAESw-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Договоры
        )

    [5] => Array
        (
            [0] => 5
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBcEMARPBDIEOgQ4-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Заявки
        )

    [6] => Array
        (
            [0] => 6
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BB8EQAQ1BEIENQQ9BDcEOAQ4-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Претензии
        )

    [7] => Array
        (
            [0] => 7
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBoEHw- &BCUEMARABDAEOwQzBDgEPQQw-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}КП Харалгина
        )

    [8] => Array
        (
            [0] => 8
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBgEIQQl-. &BB8EGAQhBCwEHAQQ-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}ИСХ. ПИСЬМА
        )

    [9] => Array
        (
            [0] => 9
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BDwEMAQ7BE8EQAQ9BDAETw-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}малярная
        )

    [10] => Array
        (
            [0] => 10
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BC4EQAQ4BEEEQg-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Юрист
        )

    [11] => Array
        (
            [0] => 11
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBAEPQQwBEEEQgQwBEEEOARP-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Анастасия
        )

    [12] => Array
        (
            [0] => 12
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BCIEMARCBEwETwQ9BDA-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Татьяна
        )

    [13] => Array
        (
            [0] => 13
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BCEEPwQwBDw-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Спам
        )

    [14] => Array
        (
            [0] => 14
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BB4EQgQ,BEAEMAQyBDsENQQ9BD0ESwQ1-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Отправленные
        )

    [15] => Array
        (
            [0] => 15
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BCcENQRABD0EPgQyBDgEOgQ4-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Черновики
        )

    [16] => Array
        (
            [0] => 16
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}&BBoEPgRABDcEOAQ9BDA-
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Корзина
        )

    [17] => Array
        (
            [0] => 17
            [1] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Archive
            [2] => {imap.mail.ru:993/imap/ssl/novalidate-cert}Archive
        )

)
   
*/        
/*********************************/

public function getMailListRaw()		
   {

   //  $imap = imap_open('{imap.yandex.ru:993/imap/ssl}INBOX', 'vvmol-nsk', 'Anatolica');
   //  $imap = imap_open('{imap.mail.ru:993/imap/ssl}INBOX', 'rik-nsk', '777Rut3630829');  

    $host = 'imap.mail.ru';
    $protocol = 'imap/ssl/novalidate-cert';
    $port = 993;
    $userid = 'rik-nsk@mail.ru';
    $userpasswd = '777Rut3630829';
    //$imap = imap_open("\{$host:$port/$protocol}INBOX", $userid, $userpasswd) or die(imap_last_error());
    $msgFolder = 'Отправленные';
    $imap = imap_open('{'.$host.':'.$port.'/'.$protocol.'}'.$msgFolder, $userid, $userpasswd) or die(imap_last_error());

/*
    Получим список полученных сообщений
*/
    $lastDate = date('Y-m-d');
    $lastDate = '2010-03-02';

    
/*    $list= imap_getmailboxes($imap, '{'.$host.':'.$port.'/'.$protocol.'}', '*');
     foreach ($list as $key => $val) {
     $a=[];
        $a[]= $key;
        $a[]=$val->name;
        $a[]= mb_convert_encoding("$val->name", "UTF-8", "UTF7-IMAP"); 
    $this->debug[]=$a;
    }
*/    

      $mails_id = imap_search($imap, 'SINCE "'.$lastDate.'"');
//    $this->debug[]= $mails_id;  
     foreach ($mails_id as $num) {
        // Заголовок письма
        $header = imap_header($imap, $num);
        $header = json_decode(json_encode($header), true);
        if (isset ($header['subject']))$header['subject'] = mb_decode_mimeheader($header['subject']);
        else continue;
        if (isset ($header['fromaddress']))$header['fromaddress'] =mb_decode_mimeheader($header['fromaddress']);
        else continue;        
        if (!isset($header['message_id']))  continue;       
        if (!isset($header['message_id']))  continue;       
        if ($header['Deleted'] == 1)  continue;       
   $this->debug[]= $num;     
   $this->debug[]= $header;  
   
   
  return; 
   continue;
        //пропустим уже выкаченные 
        
    //    $body = imap_body($imap, $num);
    //    $body = quoted_printable_decode($body);      
    }
    imap_close($imap);

    
  }   
      
public function getUserList()		      
{
    $strSql = "SELECT DISTINCT {{%user}}.id, userFIO from {{%user}}, {{%mail}}
    where {{%user}}.id = {{%mail}}.refManager";    
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll();                          
    
    $res = ['-1' => 'Все', '-2' => 'Непривязанные'];
    
    for ($i = 0; $i<count($list); $i++ )
    {    
        $res[$list[$i]['id']]= $list[$i]['userFIO'];
    }
    return $res;
}
 
   
/*число не привязанных сообщений на меня*/ 
public function getMyNonLinkMail()		
   {
    $curUser=Yii::$app->user->identity;   
    $strSql = "SELECT count({{%mail}}.id) from {{%mail}}
    where ifnull(refContact,0) = 0 
    and {{%mail}}.isSupplier =0 and {{%mail}}.isDel =0 
    and refManager=:refManager";        
    return Yii::$app->db->createCommand($strSql, [':refManager' => $curUser->id])->queryScalar();                          
   }   

/*число не привязанных сообщений на всех*/ 
public function getAllNonLinkMail()		
   {
    $curUser=Yii::$app->user->identity;   
    $strSql = "SELECT count({{%mail}}.id) from {{%mail}} 
    left join {{%user}} on {{%user}}.id = {{%mail}}.refManager
    where ifnull({{%mail}}.refContact,0)=0 and  ifnull({{%mail}}.refManager,0)>0 
    and {{%mail}}.isSupplier =0 and {{%mail}}.isDel =0 
    and {{%user}}.usageFlag & ".User::U_RPT_MAIL ;  
    
          
    return Yii::$app->db->createCommand($strSql, [':refManager' => $curUser->id])->queryScalar();                          
   }   
   
/*число не привязанных сообщений на меня*/ 
public function getNonRefMail()		
   {
    $strSql = "SELECT count({{%mail}}.id) from {{%mail}}
    where ifnull(refManager,0) = 0 
    and {{%mail}}.isSupplier =0 and {{%mail}}.isDel =0 
    ";        
    return Yii::$app->db->createCommand($strSql )->queryScalar();                          
   }   
/*********************************/   
public function switchMailParam($id, $paramType) 
{
$res = ['res' => false, 'id' => $id, 'paramType' => $paramType];
$val = 0;
        $record = TblMail::findOne($id);
        if (empty($record)) return $res;
        
        switch ($paramType)
        {
          case 'isDel':
              if ($record->isDel == 1) $record->isDel = 0;
              else                     $record->isDel = 1; 
              $record->save();              
              $val = $record->isDel;
          break;
        
          case 'isSupplier':
              if ($record->isSupplier == 1) $record->isSupplier = 0;
              else                     $record->isSupplier = 1; 
              $record->save();
              $val = $record->isSupplier;         
          break;

          case 'isZakaz':
              if ($record->isZakaz == 1) $record->isZakaz = 0;
              else                     $record->isZakaz = 1; 
              $record->save();
              $val = $record->isZakaz;              
          break;
        }
        $record->save();

     $res['val'] = $val;              
     $res['res'] = true;
     return $res;
}

/*********************************/   
public function getProcessMailProvider($params)		
   {
     $query  = new Query();
        $query->select ([
            '{{%user}}.id', 
            'userFIO',
            'usageFlag' 
            ])
             ->from("{{%user}}, {{%mail}}")               
             ->andWhere("{{%user}}.id = {{%mail}}.refManager")
             ->distinct();
             ;
               
       
     if (($this->load($params) && $this->validate())) {
      if ($this->userFIO > 0){
        $query->andFilterWhere(['like', '{{%mail}}.refManager', $this->userFIO]);
      }
            
     }

     $command = $query->createCommand();     
     $list = $query->createCommand()->queryAll();     
     $count = count($list);

     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 20,
               ],
               
               'sort' => [
               
               'attributes' => [
                'userFIO',                                
                ], 
                    'defaultOrder' => [ 'userFIO' => SORT_ASC ],                
               ],
               
          ]);
          
     return  $dataProvider;      
   }
   
/****/      

}
