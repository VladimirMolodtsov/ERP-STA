<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/*use app\modules\bank\models\TblAuroraHeader;
use app\modules\bank\models\TblAuroraContent;*/

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;


use unyii2\imap\ImapConnection;
use unyii2\imap\Mailbox;

/**
 * AuroraExtract - модель работы с выписками из банка - получаем из почты
 
 */
 
 class AuroraExtract extends Model
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
extract@a0202654.xsph.ru
Пароль
5mhO6udBx4

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
    $userid = 'extract@a0202654.xsph.ru';
    $userpasswd = '5mhO6udBx4';
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
     
    $mailIds = $mailbox->searchMailBox('ALL');
    foreach($mailIds as $mailId)
    {
    // Returns Mail contents
    $mail = $mailbox->getMail($mailId); 
    
 
                    
    // Returns mail attachements if any or else empty array
    $attachments = $mail->getAttachments(); 
    foreach($attachments as $attachment)
    {            
      $fname = $attachment->filePath;
echo $fname;
      $this -> loadBankExtract($fname, $mail->subject);
        // Delete attachment file
      if (file_exists ($attachment->filePath) )unlink($attachment->filePath);
    }
        $mailbox->deleteMail($mailId); // Deletes all marked mails
    }
        $mailbox->expungeDeletedMails();   
 }   
  
/*************************************/ 

/*

*/

 
/* парсим */
public function loadBankExtract($fname, $src) 
{
   $row = 1;
   if (($handle = fopen($fname, "r")) == FALSE) return false;
   
   $str = fgets($handle, 1000);
   $stru= iconv("Windows-1251", "UTF-8", $str);
   
   $parse = str_getcsv($stru,";");  
   if (count($parse) < 40) return false; //не наша выписка
   
   $recordHeader = new TblBankHeader();   
   $recordHeader->uploadTime= date("Y-m-d H:i:s", time());  
   $recordHeader->srcFile = mb_substr($src,0,250,'utf-8');              
    //$recordHeader->creationDate = $this->ExcelToPHPObject($rows[1][1])->format('Y-m-d H:i:s');
    //$this->debug .="Остаток дебет:  ".$rows[$cnt-2][7]."\n";
    //$debetRemain=intval(preg_replace("/[\D]/","",$rows[$cnt-2][7]));
    //$recordHeader->debetRemain = floatval($debetRemain/100);
    // $this->debug .="Остаток кредит: ".$rows[$cnt-2][11]."\n";  
    //$creditRemain=intval(preg_replace("/[\D]/","",$rows[$cnt-2][11]));
    //$recordHeader->creditRemain = floatval($creditRemain/100);
    $recordHeader->save();

    $inputSumm=0;    
    $outputSumm=0;
    $iRow=0;
    while (($str = fgets($handle, 1000 )) !== FALSE) {
        
        $stru= iconv("Windows-1251", "UTF-8", $str);            
        $parse = str_getcsv($stru,";");  
        if (count($parse) < 40) continue; //не валидная строка
        
   if ($inputSumm==0)$inputSumm=floatval($parse[8]);
   if (!empty($parse[9]))$outputSumm=floatval($parse[9]);
   
        $recordContent = new TblBankContent();
        $recordContent->refBankHeader = $recordHeader->id;
         
        $recordContent->recordDate = date("Y-m-d", strtotime($parse[0]));
        
        $recordContent->reasonDocDate= date("Y-m-d", strtotime($parse[1]));
        $recordContent->reasonDocNum=$parse[4];
        $operationSum = floatval($parse[7]);
        if ($parse[5]=='Входящий'){
           /*Кредит*/ 
           $recordContent->creditSum = $operationSum;  
           $recordContent->contrAgentBank = mb_substr($parse[16],0,250,'utf-8');              
        }else
        {
         /*Дебет*/    
           $recordContent->debetSum = $operationSum;                        
           $recordContent->contrAgentBank = mb_substr($parse[24],0,250,'utf-8');              
        }
        $recordContent->reasonText = mb_substr($parse[10],0,150,'utf-8');   
        
         /*Плательщик*/
        $recordContent->debetOrgTitle = mb_substr($parse[11],0,250,'utf-8');   
        $recordContent->debetRS = mb_substr($parse[12],1,50,'utf-8');   
        $recordContent->debetINN = mb_substr($parse[13],1,20,'utf-8');   
        $recordContent->debetKPP = mb_substr($parse[14],1,20,'utf-8');   
        $recordContent->debetBIK = mb_substr($parse[15],1,20,'utf-8');   
         /*Получатель*/
        $recordContent->creditOrgTitle = mb_substr($parse[19],0,250,'utf-8');   
        $recordContent->creditRs = mb_substr($parse[20],1,50,'utf-8');   
        $recordContent->creditINN = mb_substr($parse[21],1,20,'utf-8');   
        $recordContent->creditKPP = mb_substr($parse[22],1,20,'utf-8');           
        $recordContent->creditBIK = mb_substr($parse[23],1,20,'utf-8');   
    $recordContent->save();
    }
    fclose($handle);  
    

  $recordHeader->inputRemain = $inputSumm;
  $recordHeader->outputRemain = $outputSumm;
  $recordHeader->save();
  
  /*Выдергиваем уникальные*/
  $strsql= "INSERT INTO {{%aurora_extract}}
 ( recordDate, debetRS, debetINN, debetOrgTitle, creditRs, creditINN, creditOrgTitle, debetSum,
  creditSum, docNum, contrAgentBank, description, creditBIK, creditKPP, debetKPP, debetBIK,
  reasonDocType,  reasonDocNum,   reasonDocDate,  reasonText)
 ( SELECT DISTINCT  a.recordDate, a.debetRS, a.debetINN, a.debetOrgTitle, a.creditRs, a.creditINN, a.creditOrgTitle,
 a.debetSum, a.creditSum, a.docNum, a.contrAgentBank, a.description,  a.creditBIK, a.creditKPP, a.debetKPP, a.debetBIK,
 a.reasonDocType,  a.reasonDocNum,   a.reasonDocDate,  a.reasonText
 from {{%aurora_content}} as a  left join {{%aurora_extract}} as b on 
 (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN AND a.reasonDocNum = b.reasonDocNum ) 
 where b.id is null )";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*связываем с уникальными*/
   $strsql= "Update {{%aurora_content}}  as a left join {{%aurora_extract}} as b on 
   (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )
   SET a.refExtract = b.id  where   a.refExtract =0 and  b.id is not null"; 
   Yii::$app->db->createCommand($strsql)->execute();    
  
  
  /*метим Доходы*/
  $strsql= "update {{%aurora_extract}} set extractType = 1  where creditSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*метим расходы*/
  $strsql= "update {{%aurora_extract}} set extractType = 2,  contragentType = 1 where debetSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  unlink($fname);
}


 public function getLogData()
 {
     $strsql = "SELECT refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = 10 and actionDateTime > '".date("Y-m-d 04:04")."' ORDER BY actionDateTime"; 
     $list = Yii::$app->db->createCommand($strsql)->queryAll();    
     
     return $list;
 }
/*****************************/    
/**** Providers **************/    
/*****************************/
/* Список загруженных выписок */
 public function getAuroraExtractListProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%aurora_header}}.id', 
            'creationDate', 
            'uploadTime', 
            'inputRemain',
            'outputRemain',
            'srcFile',            
            ])
            ->from("{{%aurora_header}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%aurora_header}}.id)")
            ->from("{{%aurora_header}}")
            ;            
     
            
     if (($this->load($params) && $this->validate())) {
             
     }
        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'creationDate', 
            'uploadTime', 
            'inputRemain',
            'outputRemain',
            'srcFile',  
            ],            
            
            'defaultOrder' => [  'uploadTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
/* Данные по выписке */
 public function getExtractData($sd, $ed)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'recordDate',
            'debetOrgTitle',            
            'debetRS',
            'debetBIK',            
            'debetINN',
            'debetKPP',            
            'creditOrgTitle',            
            'creditRs',      
            'creditBIK',                        
            'creditINN',
            'creditKPP',            
            'debetSum',
            'creditSum',
            'docNum',
            'contrAgentBank',
            'description',
            'extractType',
            'reasonDocNum',
            'reasonDocDate',
            'reasonText',
            ])            
            ->from("{{%aurora_extract}}")
            ;
    $query->andWhere("recordDate >= :sd");
    $query->andWhere("recordDate <  :ed");
    
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
     
        );
        
        fputcsv($fp, $col_title, ","); 
        
        
        for ($i=0;$i< count($list);$i++)
        {
            
            $list[$i]['creditSum'] = preg_replace("/\./",",",$list[$i]['creditSum']);            
            $list[$i]['debetSum']= preg_replace("/\./",",",$list[$i]['debetSum']);
    
         $rowArray = array 
            (
            $list[$i]['recordDate'],
            $list[$i]['reasonDocDate'],
            $list[$i]['reasonDocNum'],

            $list[$i]['creditSum'],
            $list[$i]['debetSum'],
            $list[$i]['reasonText'],

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
   
   
 public function getExtractProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'recordDate',
            'debetOrgTitle',            
            'debetRS',
            'debetBIK',            
            'debetINN',
            'debetKPP',            
            'creditOrgTitle',            
            'creditRs',      
            'creditBIK',                        
            'creditINN',
            'creditKPP',            
            'debetSum',
            'creditSum',
            'docNum',
            'contrAgentBank',
            'description',
            'extractType',
            'reasonDocNum',
            'reasonDocDate',
            'reasonText',
            ])            
            ->from("{{%aurora_extract}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%aurora_extract}}.id)")
            ->from("{{%aurora_extract}}")
            ;            
     
            
     if (($this->load($params) && $this->validate())) {
             
     }
        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'id',
            'recordDate',
            'debetOrgTitle',            
            'debetRS',
            'debetBIK',            
            'debetINN',
            'debetKPP',            
            'creditOrgTitle',            
            'creditRs',      
            'creditBIK',                        
            'creditINN',
            'creditKPP',            
            'debetSum',
            'creditSum',
            'docNum',
            'contrAgentBank',
            'description',
            'extractType',
            'reasonDocNum',
            'reasonDocDate',
            'reasonText',
            ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/*****************************/    
/**** Providers **************/    
/*****************************/
/* Список загруженных выписок */
 public function getExtractListProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%aurora_header}}.id', 
            'uploadTime', 
            'inputRemain', 
            'outputRemain', 
            'srcFile',             
            ])
            ->from("{{%aurora_header}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%aurora_header}}.id)")
            ->from("{{%aurora_header}}")
            ;            
            
     if (($this->load($params) && $this->validate())) {
             
     }
        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [                        
            'attributes' => [        
            'uploadTime', 
            'inputRemain', 
            'outputRemain', 
            'srcFile',             
            ],            
            
            'defaultOrder' => [  'uploadTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  /*******************************************/ 
public function getExtractDetailProvider($params)
   {
   
   //не задана ссылка на выписку - открываем последнюю
   if ($this->extractRef == 0)
   {
      $this->extractRef = Yii::$app->db->createCommand('SELECT MAX(id) from {{%bank_header}}')->queryScalar();    
   }
   
   if (empty($this->extractRef)) $this->extractRef = 0;

 
    $query  = new Query();    
    $query->select ([
            '{{%aurora_content}}.id', 
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'contrAgentBank', 
            'description', 
            'reasonDocType', 
            'reasonDocNum', 
            'reasonDocDate', 
            'reasonText', 
            'debetKPP', 
            'creditKPP', 
            'debetBIK', 
            'creditBIK', 
            ])
            ->from("{{%aurora_content}}")
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%aurora_content}}.id)")
            ->from("{{%aurora_content}}")
            ;            
         $query->andWhere('{{%aurora_content}}.refBankHeader = '.$this->extractRef );
    $countquery->andWhere('{{%aurora_content}}.refBankHeader = '.$this->extractRef );
            
                        
     if (($this->load($params) && $this->validate())) {

        /*$query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]); */
             
     }

        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->maxRowDetail,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'contrAgentBank', 
            'description', 
            'reasonDocType', 
            'reasonDocNum', 
            'reasonDocDate', 
            'reasonText', 
            'debetKPP', 
            'creditKPP', 
            'debetBIK', 
            'creditBIK',               ],            
            
            'defaultOrder' => [  'recordDate' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 


  
  /************End of model*******************/ 
 }
