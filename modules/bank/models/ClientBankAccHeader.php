<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;

/**
 * ClientBankDocument - Парсинг и формирование документа в формате 1CClientBankExchange
 
 */
 
 class ClientBankAccHeader extends Model
{
    public $startKey  ='СекцияРасчСчет';
    public $finishKey ='КонецРасчСчет';

    public $startDate = "";
    public $endDate = "";
    public $startSumm = "";
    public $endSumm = "";        
    public $account = "";
    public $expense = "";
    public $admission = "";

  /***/
  static $keysArray = [
            'ДатаНачала' => 'startDate',
            'ДатаКонца' => 'endDate',
            'НачальныйОстаток' => 'startSumm',
            'КонечныйОстаток'  => 'endSumm',            
            'РасчСчет' => 'account',
            'ВсегоСписано' => 'expense',
            'ВсегоПоступило' => 'admission',
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
 

 public function save($creationDT = "", $refManager = 0) 
     { 
        $record = new TblBankHeader();
        if (empty($record)) return 0;

       $record->uploadTime = date("Y-m-d H:i");
       if (empty($creationDT)) $creationDT = date("Y-m-d H:i");
                         else  $creationDT = date("Y-m-d H:i", strtotime($creationDT));
       $record->creationDate = $creationDT;
       $record->refManager = $refManager;  

       $record->startDate = date ("Y-m-d",strtotime($this->startDate)); //ДатаНачала
       $record->endDate   = date ("Y-m-d",strtotime($this->endDate));   //ДатаКонца
       $record->startSumm     = floatval($this->startSumm); //НачальныйОстаток
       $record->endSumm       = floatval($this->endSumm); //КонечныйОстаток
       $record->debetTurn     = floatval($this->expense); //ВсегоСписано
       $record->creditTurn    = floatval($this->admission);//ВсегоПоступило
       $record->account       = $this->account;  //РасчСчет     

       if ($record->endSumm<0) $record->debetRemain   = -($record->endSumm); //Задолженность
                          else $record->creditRemain  = $record->endSumm; //КонечныйОстаток
       $record->save();
       return $record->id;                
     }
  
  /************End of model*******************/ 
 }
