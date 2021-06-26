<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use app\modules\bank\models\ClientBankDocument;
use app\modules\bank\models\ClientBankAccHeader;
use app\modules\bank\models\TblControlBankUse;

/**
 * ClientBankExchange - Парсинг и формирование формата 1CClientBankExchange
 
 */
 
 class ClientBankExchange extends Model
{
    public $documentArray =[];
    public $accountData;
    
    public $errCode =0;
    public $errString="";
    
    public $errKeys = [];
    
    public $version;
    public $encode;
    public $startDate = "";
    public $endDate = "";
    public $sender  = "";
    public $acceptor= "";
    public $creationDate= "";
    public $creationTime= "";
    public $account = "";

  static $keysArray = [
            'ДатаНачала' => 'startDate',
            'ДатаКонца' => 'endDate',
            'Отправитель' => 'sender',
            'Получатель' => 'acceptor',
            'ДатаСоздания' => 'creationDate',
            'ВремяСоздания' => 'creationTime',
            'ВерсияФормата' => 'version',
            'Кодировка'    => 'encode',
            'РасчСчет'     => 'account',
        ];

    
    public function rules()
    {
        return [                              
             [[], 'default'],                        
             [[], 'safe'],            
        ];
    }


  public function loadFileExchange ($fname)
  {
    $parse = file ($fname);      
    $N=count($parse);  
    if ($N == 0)                   
                   {$this->errCode=1; $this->errString="Empty File"; return false; }
    if (rtrim($parse[0])!= "1CClientBankExchange")  
               {$this->errCode=2; $this->errString="Invalid Format"; return false; }
    $strParse=explode('=',rtrim($parse[1])); 
    $this->version = $strParse[1]; 
    
    $strParse=explode('=',rtrim($parse[2])); 
    $this->encode = $strParse[1]; 
    if ($this->encode == 'Windows')
                        {
                          /*for ($i=1;$i<$N;$i++ )      
                              $parse[$i] = iconv( "Windows-1251","UTF-8",$parse[$i]);*/
                          //mb_convert_encoding($parse[2], "UTF-8", "Windows-1251");
                          $parse = mb_convert_encoding($parse, "UTF-8", "Windows-1251");
                        }
    $docid=0;
    for ($i=1;$i<$N;$i++ )    
    {
      $str = rtrim($parse[$i]);      
      if (empty($str))continue;
      $strParse = explode('=',$str); 
      
      /*Парсим секции*/  
      if ($strParse[0] == 'СекцияРасчСчет')
      {
          $i = $this->parseBankAccount($parse, $i, $N);
          continue;
      }

      if ($strParse[0] == 'СекцияДокумент')
      {
          $i = $this->parseBankDocument($parse, $i, $N);
          continue;
      }
      
      if ($strParse[0] == 'КонецФайла') break;
            
      if(count ($strParse)== 1){$this->errKeys[]=$str; continue; } //неизвестный нам раздел
      
      /*Значения вне секций*/
      if ($this->set($strParse[0], $strParse[1]) == false) {
          $this->errKeys[]="[$i] ".$str; 
          continue; } //Не распознан ключ
      
  
    }
  }  
  
   public function parseBankAccount($parse, $start, $N) 
   {    
     $accountData = new ClientBankAccHeader();     
     for ($i=$start+1; $i<$N; $i++ )
     {
      $str = rtrim($parse[$i]);   
      if (empty($str))continue;
      $strParse = explode('=',$str);    
      if ($strParse[0] == $accountData->finishKey) {
          $this->accountData = $accountData;
          return $i;                
      }
      if (count($strParse)<2) { 
       $this->errKeys[]="$i- ".$parse[$i]; 
       continue;}             
      if (count($strParse) >2) 
          for ($j=2; $j< count($strParse); $j++)$strParse[1].="=".$strParse[$j];
                                            //знаки = в значении...

      if (!$accountData->set($strParse[0], $strParse[1])) {
          $this->errKeys[]="$i- ".$parse[$i]; 
          continue; 
          } //Не распознан ключ
     }
     return $i;     
   }

   public function parseBankDocument($parse, $start, $N) 
   { 
     $doc = new ClientBankDocument();     
     for ($i=$start; $i<$N; $i++ )
     {
      $str = rtrim($parse[$i]);      
      if (empty($str))continue;
      $strParse = explode('=',$str);    
      if ($strParse[0] == $doc->finishKey) {
           $this->documentArray[]=$doc;  
           return $i; 
      }  
      if (count($strParse) <2) { 
       $this->errKeys[]="$i- ".$parse[$i]; 
       continue;}             
      if (count($strParse) >2) 
          for ($j=2; $j< count($strParse); $j++)$strParse[1].="=".$strParse[$j];
                                            //знаки = в значении...

      if (!$doc->set($strParse[0], $strParse[1])) {
          $this->errKeys[]="$i- ".$parse[$i]; 
          continue; 
          } //Не распознан ключ
     }
     $this->documentArray[]=$doc;
     return $i;
   }
/*****************/

  public function saveFileExchange ($fname)
  {
        
    $outArray=[];
    
    $outArray = array_merge($outArray,$this->getExchangeTitle() );
    
    $N = count($this->documentArray);
    for($i=0;$i<$N;$i++)
        $outArray= array_merge($outArray,$this->documentArray[$i]->getTextArray() );    
    
    $outArray[]='КонецФайла';
    $N= count($outArray);

    
    if (!$fw = fopen($fname, 'w'))  return false;    
    for ($i=0;$i<$N;$i++)
    {        
 //       echo $outArray[$i]."\n";          
          $out = iconv("UTF-8", "Windows-1251",$outArray[$i])."\r\n";
         fwrite($fw, $out);          
    }
    fclose($fw);

  }
  public function getExchangeTitle() 
  {
    $data[] = "1CClientBankExchange";
    $data[] = "ВерсияФормата=1.02";
    $data[] = "Кодировка=Windows";
    $data[] = "Отправитель=WebClient";
    $data[] = "Получатель=";
    $data[] = "ДатаСоздания=".date("d.m.Y");
    $data[] = "ВремяСоздания=".date("H.i.s");
    $data[] = "ДатаНачала=".$this->startDate;
    $data[] = "ДатаКонца=".$this->endDate;
    $data[] = "РасчСчет=".$this->account;
        
    return $data;
  }
  /****************/
    public function   loadActivePayer()
    {
        $record = TblControlBankUse::findOne(['isCurrent' => 1]);
        if (empty($record)) return false;
  
        $this->startDate = date("d.m.Y");
        $this->endDate   = date("d.m.Y");
        $this->account   = $record->accountNumber;
    }
  
  /****************/
  /* Сохраняем в базе */
    public function save()
    {        
       if (!empty($this->accountData)) {
               $headerRef=$this->accountData->save($this->creationDate);      
               $ownerAccount= $this->accountData->account;      
        }       
       $N = count($this->documentArray);
     //  echo $N."\n";
       for($i=0;$i<$N;$i++)
       {
          // print_r($this->documentArray[$i]);
             $this->documentArray[$i]->save($headerRef, $ownerAccount);                                   
        }     
    }   


  /***************/  
    public function set($param, $value) 
     { 
        if (!isset(self::$keysArray[$param])) return false; 
        $key = self::$keysArray[$param];
        $this->$key=$value;
       return true;        
     }

   /***************************************/  
   /***************************************/
   /***************************************/
  /* Сохраняем в базе */
    public function createTest()
    {
    $this->startDate = date("d.m.Y");
    $this->endDate   = date("d.m.Y");
    $this->account   = '40702810344050004274';
    $doc=new ClientBankDocument();
    
    $doc->docType = 'Платежное поручение';
    $doc->docNum = '5002';
    $doc->docDate=date("d.m.Y");
    $doc->summ =28023.79;
    
    $doc->payerDealAccount= '40702810344050004274';
    $doc->payerTitle='Рутенберг ООО';
    $doc->payerInn = '5408241227';
    $doc->payer1='Рутенберг ООО';
    $doc->payerAccount= '40702810344050004274';
    $doc->payerBank1 ='Новосибирское отделение № 8047 ПАО "Сбербанк России"';
    $doc->payerBank2 ='г. Новосибирск';            
    $doc->payerBik ='045004641';
    $doc->payerCorrAccount='30101810500000000641';
            
    $doc->beneficiaryAccount='40702810704500007921';    
    $doc->beneficiaryTitle='Общество с ограниченной ответственностью "Аврора"';
    $doc->beneficiaryInn= '5408023130';
    $doc->beneficiary1='Общество с ограниченной ответственностью "Аврора"';
    $doc->beneficiaryDealAccount= '40702810704500007921';
    $doc->beneficiaryBank1='ТОЧКА ПАО БАНКА "ФК ОТКРЫТИЕ"';
    $doc->beneficiaryBank2='Г. МОСКВА';            
    $doc->beneficiaryBik='044525999';
    $doc->beneficiaryCorrAccount='30101810845250000999';
            
    $doc->payPurpose='Услуги по размотке и порезке бумаги, сч. №60 от 21.05.2020';            
 
    $doc->payerKpp='540801001';
    $doc->beneficiaryKpp='540801001';
    $doc->order = 5;
    $doc->statusKbk='';
    $doc->createrStatus='';                   
    $doc->okato='';
    $doc->statusBase='';
    $doc->statusPeriod='';
    $doc->statusNumber='';
    $doc->statusDate='';

    $this->documentArray[]=$doc;


    $doc=new ClientBankDocument();
    
    $doc->docType = 'Платежное поручение';
    $doc->docNum = '5003';
    $doc->docDate=date("d.m.Y");
    $doc->summ =120000.00;
    
    $doc->payerDealAccount= '40702810344050004274';
    $doc->payerTitle='Рутенберг ООО';
    $doc->payerInn = '5408241227';
    $doc->payer1='Рутенберг ООО';
    $doc->payerAccount= '40702810344050004274';
    $doc->payerBank1 ='Новосибирское отделение № 8047 ПАО "Сбербанк России"';
    $doc->payerBank2 ='г. Новосибирск';            
    $doc->payerBik ='045004641';
    $doc->payerCorrAccount='30101810500000000641';
            
    $doc->beneficiaryAccount='40702810123120000223';    
    $doc->beneficiaryTitle='ООО "Сибирское технологическое агентство"';
    $doc->beneficiaryInn= '5408267271';
    $doc->beneficiary1='ООО "Сибирское технологическое агентство"';
    $doc->beneficiaryDealAccount= '40702810123120000223';
    $doc->beneficiaryBank1='ФИЛИАЛ "НОВОСИБИРСКИЙ" АО "АЛЬФА-БАНК"';
    $doc->beneficiaryBank2='Г. НОВОСИБИРСК';            
    $doc->beneficiaryBik='045004774';
    $doc->beneficiaryCorrAccount='30101810600000000774';
            
    $doc->payPurpose='Услуги по договору автоматизации бизнес-процесса  №СТА/194 от 01.08.2017г.';            
    
    $doc->payerKpp='540801001';
    $doc->beneficiaryKpp='540801001';
    $doc->order = 5;
    $doc->statusKbk='';
    $doc->createrStatus='';                   
    $doc->okato='';
    $doc->statusBase='';
    $doc->statusPeriod='';
    $doc->statusNumber='';
    $doc->statusDate='';

    $this->documentArray[]=$doc;
  }
     
     
  /************End of model*******************/ 
 }
