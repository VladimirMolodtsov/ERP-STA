<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;

/**
 * ClientBankDocument - Парсинг и формирование документа в формате 1CClientBankExchange
 
 */
 
 class ClientBankDocument extends Model
{
    
    public $startKey  ='СекцияДокумент';
    public $finishKey ='КонецДокумента';
    
  public $docType = "";
  public $docNum = "";
  public $docDate = "";
  public $summ = "";
  public $outDate = "";
  public $inDate = "";
  public $payerAccount = "";
  public $payerTitle = "";
  public $payerInn = "";
  public $payer1 = "";
  public $payerDealAccount = "";
  public $payerBank1 = "";
  public $payerBank2 = "";
  public $payerBik = "";
  public $payerCorrAccount = "";
  public $payerKpp = "";
            
  public $beneficiaryAccount = "";
  public $beneficiaryTitle = "";
  public $beneficiaryInn = "";
  public $beneficiary1 = "";
  public $beneficiaryDealAccount = "";
  public $beneficiaryBank1 = "";
  public $beneficiaryBank2 = "";
  public $beneficiaryBik = "";
  public $beneficiaryCorrAccount = "";
  public $beneficiaryKpp = "";
            
  public $payTitle = "";
  public $payType = "";
  public $cod = "";
            
  public $payPurpose = "";
  public $createrStatus = "";

  public $okato = "";
            
  public $statusKbk = "";            
  public $statusBase = "";
  public $statusPeriod = "";
  public $statusNumber = "";
  public $statusDate = "";
  public $statusType = "";

  public $termPayment = "";
  public $order = "";

  public $acceptPeriod= "";
  public $payCondition1= "";
  public $typeAccred= "";
  public $supplierAccount= "";
  public $payNeedDoc= "";
  public $addCondition= "";
  public $sendDocDate= "";

  public $codGoal="";
  
  public $NDS=0;
   
  /***/
  static $keysArray = [
            'СекцияДокумент' => 'docType',
            'Номер' => 'docNum',
            'Дата' => 'docDate',
            'Сумма' => 'summ',
            'ДатаСписано' => 'outDate',
            'ДатаПоступило' => 'inDate',
            'ПлательщикСчет' => 'payerAccount',
            'Плательщик' => 'payerTitle',
            'ПлательщикИНН' => 'payerInn',
            'Плательщик1' => 'payer1',
            'ПлательщикРасчСчет' => 'payerDealAccount', //
            'ПлательщикБанк1' => 'payerBank1', //
            'ПлательщикБанк2' => 'payerBank2',
            'ПлательщикБИК' => 'payerBik', //
            'ПлательщикКорсчет' => 'payerCorrAccount',
            'ПлательщикКПП' => 'payerKpp',
            
            'ПолучательСчет' => 'beneficiaryAccount',
            'Получатель' => 'beneficiaryTitle',
            'ПолучательИНН' => 'beneficiaryInn',
            'Получатель1' => 'beneficiary1',
            'ПолучательРасчСчет' => 'beneficiaryDealAccount',
            'ПолучательБанк1' => 'beneficiaryBank1',
            'ПолучательБанк2' => 'beneficiaryBank2',
            'ПолучательБИК' => 'beneficiaryBik',
            'ПолучательКорсчет' => 'beneficiaryCorrAccount',
            'ПолучательКПП' => 'beneficiaryKpp',
            
            'ВидПлатежа' => 'payTitle',
            'ВидОплаты' =>  'payType',
            'Код'       =>  'cod',
            
            'НазначениеПлатежа' => 'payPurpose',
            'СтатусСоставителя' => 'createrStatus',

            'ОКАТО' => 'okato',
            
            'ПоказательКБК' => 'statusKbk',            
            'ПоказательОснования' => 'statusBase',
            'ПоказательПериода' => 'statusPeriod',
            'ПоказательНомера' => 'statusNumber',
            'ПоказательДаты' => 'statusDate',
            'ПоказательТипа' => 'statusType',

            'СрокПлатежа' => 'termPayment',
            'Очередность' => 'order',
            
            
            'СрокАкцепта'    => 'acceptPeriod',
            'УсловиеОплаты1' => 'payCondition1',
            'ВидАккредитива' => 'typeAccred',
            'НомерСчетаПоставщика' => 'supplierAccount',
            'ПлатежПоПредст' => 'payNeedDoc',
            'ДополнУсловия'  => 'addCondition',
            'ДатаОтсылкиДок' => 'sendDocDate',

            'КодНазПлатежа'  => 'codGoal',
        ];

    
    public function rules()
    {
        return [                              
             [[], 'default'],                        
             [[], 'safe'],            
        ];
    }
      
  /**************************/
 public function set($param, $value) 
     { 
        if (!isset(self::$keysArray[$param])) return false; 
        $key = self::$keysArray[$param];
        $this->$key=$value;    
       return true;                
     }
 
  
  /**************************/
 public function save($headerRef, $account) 
     { 
        // echo $this->docNum." [".$this->docType."] "."\n";
        //  print_r($this);
        //if ($this->docType=='Платежное поручение')
        {
            return $this->saveExtract($headerRef, $account);
        }
       return false;                
     }

 public function saveExtract($headerRef=0, $account) 
    {  
        $recordContent = new TblBankContent();
        if (empty($recordContent)) return false;
        $recordContent->refBankHeader = $headerRef;
        $recordContent->docDate =  date("Y-m-d",strtotime($this->docDate));  //Номер
        $recordContent->docNum  =  $this->docNum;    //Дата
        //плательщик с нашего счета
        if ($account == $this->payerAccount){
            //мы платим
            $recordContent->debetSum  =  floatval($this->summ); //Сумма             
            $recordContent->contrAgentBank =  $this->beneficiaryBik.", ".$this->beneficiaryBank1;//ПолучательБИК, ПолучательБанк1    
            $recordContent->recordDate    = date("Y-m-d",strtotime($this->outDate)); //ДатаСписано    
        }else{
           // нам платят    
            $recordContent->creditSum  =  floatval($this->summ);//Сумма  
            $recordContent->contrAgentBank =  $this->payerBik.", ".$this->payerBank1;//ПлательщикБИК, ПлательщикБанк1  
            $recordContent->recordDate    =  date("Y-m-d",strtotime($this->inDate));//ДатаПоступило    
        }
        
        if (!empty($this->outDate))
        $recordContent->dateOutput     =  date("Y-m-d",strtotime($this->outDate));  //ДатаСписано
        
        if (empty($this->payerTitle)) $recordContent->debetOrgTitle=  $this->payer1;  //
        else $recordContent->debetOrgTitle =  $this->payerTitle;  // Плательщик
        
        $recordContent->debetINN      =  $this->payerInn;  // ПлательщикИНН
        $recordContent->debetKPP      =  $this->payerKpp;  // ПлательщикКПП
        $recordContent->debetRS       =  $this->payerAccount;  // ПлательщикСчет   ПлательщикРасчСчет
        $recordContent->debetBank     =  $this->payerBank1;  // ПлательщикБанк1
        $recordContent->debetBIK      =  $this->payerBik;  // ПлательщикБИК
        $recordContent->debetKS       =  $this->payerCorrAccount;  // ПлательщикКорсчет

        if (!empty($this->inDate))
        $recordContent->dateInput     =  date("Y-m-d",strtotime($this->inDate));  //ДатаПоступило
        if (empty($this->beneficiaryTitle)) $recordContent->creditOrgTitle=  $this->beneficiary1;  //Получатель
        else $recordContent->creditOrgTitle=  $this->beneficiaryTitle;  //Получатель
        
        $recordContent->creditINN     =  $this->beneficiaryInn;  //ПолучательИНН 
        $recordContent->creditKPP     =  $this->beneficiaryKpp;  //ПолучательКПП
        $recordContent->creditRs      =  $this->beneficiaryAccount;  //ПолучательСчет   ПолучательРасчСчет
        $recordContent->creditBank    =  $this->beneficiaryBank1;  //ПолучательБанк1  
        $recordContent->creditBIK     =  $this->beneficiaryBik;  //ПолучательБИК
        $recordContent->creditKS      =  $this->beneficiaryCorrAccount;  //ПолучательКорсчет
    
        $recordContent->payType       =  $this->payTitle;  //ВидПлатежа   
        $recordContent->VO            =  $this->payType;  //ВидОплаты
        $recordContent->payCode       =  $this->cod;  //Код
        $recordContent->description   =  $this->payPurpose;  //НазначениеПлатежа
            
        $recordContent->termPayment   = date("Y-m-d", strtotime($this->termPayment));  //СрокПлатежа
        $recordContent->order         =  $this->order;  //Очередность
        $recordContent->docType       =  mb_substr($this->docType,0,75,'utf-8');  //тип документа

    $recordContent->save();

     return true;
    }

public function loadActivePayer() 
{
  $record = TblControlBankUse::findOne(['isCurrent' => 1]);
  if (empty($record)) return false;

  $this->payerDealAccount  =  $record->accountNumber;//       'ПлательщикРасчСчет='.,
  $this->payerAccount      =  $record->accountNumber;//       'ПлательщикСчет='.,
  $this->payerTitle        =  "ИНН ".$record->INN." ".$record->usedOrgTitle; //       'Плательщик='., 
  $this->payer1            =  $record->usedOrgTitle; //       'Плательщик1='.,
  $this->payerInn          =  $record->INN;          //       'ПлательщикИНН='., 
  $this->payerBank1        =  $record->Bank;         //       'ПлательщикБанк1='.,                
  $this->payerCorrAccount  =  $record->KS;           //       'ПлательщикКорсчет='.,
  $this->payerKpp          =  $record->KPP;          //       'ПлательщикКПП='.,
  $this->payerBik          =  $record->BIK;          //       'ПлательщикБИК='.,            
}


 public function getTextArray() 
 {
         if(!( preg_match("/\bНДС\b/u",$this->payPurpose))){ 
         if ($this->NDS == 0) {             
             $this->payPurpose.=', без НДС';
         }
                          else 
                          { 
                            $ndsSum = $this->summ/(1+$this->NDS/100);
                            $ndsSum = $ndsSum*($this->NDS/100);
                            $ndsShowSum = intval($ndsSum *100)/100;
                            if ($ndsShowSum<$ndsSum)$ndsShowSum+=0.01;
                            $this->payPurpose.=', В том числе НДС '.$this->NDS.'% - '.number_format($ndsShowSum,2,'.',"").' руб.';
                          }
         }                
 return  [  
            'СекцияДокумент='.$this->docType,
            'Номер='.$this->docNum,
            'Дата='.$this->docDate,
            'Сумма='.$this->summ,
            'ПлательщикРасчСчет='.$this->payerDealAccount,
            'Плательщик='.$this->payerTitle,
            'ПлательщикИНН='.$this->payerInn,
            'Плательщик1='.$this->payer1,
            'ПлательщикСчет='.$this->payerAccount,
            'ПлательщикБанк1='.$this->payerBank1,
            'ПлательщикБанк2='.$this->payerBank2,            
            'ПлательщикБИК='.$this->payerBik,
            'ПлательщикКорсчет='.$this->payerCorrAccount,
            
            'НазначениеПлатежа='.$this->payPurpose,            
            
            'ПолучательСчет='.$this->beneficiaryAccount,
            'Получатель='.$this->beneficiaryTitle,
            'ПолучательИНН='.$this->beneficiaryInn,
            'Получатель1='.$this->beneficiary1,
            'ПолучательРасчСчет='.$this->beneficiaryDealAccount,
            'ПолучательБанк1='.$this->beneficiaryBank1,
            'ПолучательБанк2='.$this->beneficiaryBank2,
            'ПолучательБИК='.$this->beneficiaryBik,
            'ПолучательКорсчет='.$this->beneficiaryCorrAccount,
            
            'СтатусСоставителя='.$this->createrStatus,
            'ПлательщикКПП='.$this->payerKpp,
            'ПолучательКПП='.$this->beneficiaryKpp,
            'ПоказательКБК='.$this->statusKbk,            
            'ОКАТО='.$this->okato,
            'ПоказательОснования='.$this->statusBase,
            'ПоказательПериода='.$this->statusPeriod,
            'ПоказательНомера='.$this->statusNumber,
            'ПоказательДаты='.$this->statusDate,

            'ВидОплаты='.$this->payType,
            'Код='.$this->cod,
            'ПоказательТипа='.$this->statusType,
            
            'Очередность='.$this->order,

            'КонецДокумента',
        ];
 }

  /************End of model*******************/ 
 }
