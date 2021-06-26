<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;



use app\models\OrgList;
use app\models\PhoneList;
use app\models\SchetNeedList;
use app\models\ContactList;
use app\models\SchetList;

use app\models\ZakazList;
use app\models\ZakazContent;

use app\models\CalendarList;
use app\models\SchetStatusList;
use app\models\MarketCalendarForm; 
use app\models\RequestSupplyList;
use app\models\MailForm;
use app\models\EventRegForm;
use app\models\TblSchetContent;
use app\models\TblSchetNote;
use app\models\TblClientSchetHeader;
use app\models\TblOrgDostavka;

/**
 * 
 */
class MarketSchetForm extends Model
{
    /*Настройка*/    
    public $sendRequestSupply=0; /*отсылать заявку на отгрузку почтой*/
    
    /*Данные по контакту*/
    public $contactEmail = "";
    public $contactPhone = "";
    public $contactFIO ="";
    public $orgTitle ="";
    
    public $nextContactDate = "";
    public $nextContactTime = "";
    public $note= "";

    public $orgRecord;

   /*данные по заказу*/ 
    public $zakazSum =0;
    public $zakazDate ="";
    public $zakazId = "";
    
    public $sumExtract = 0;
          
    /*данные по счету*/
    public $schetINN ="";
    public $schetNumber = "";
    public $schetDate = "";
    public $leadData = array();
    public $showTransport=0;
    /*данные по 1c*/
    

    /*Статус счета*/    
    public $docStatus = 0;    
    public $cashState = 0;    
    public $supplyState = 0;    
    public $schetStatus = 0;    
    
    /*Ссылки на связи */
    public $supplyRequestId = 0; /* заявка на поставку*/    
    public $supplyRequestStatus = 0; /* Текущий статус поставки */    
    public $isFinishSupplyRequest = false;
    public $status = 0;
    public $id = 0;
    public $orgId = "";
    public $eventId = "";
    
    public $schetNote;
    
    /*Поставка*/    
    public $delay    = "";
    public $txtSupllyFull    = "";
    public $supplyDate    = "";
    public $dstType= "0";
    public $adress= "";
    public $dstNote ="";   
    public $dstRef;    
    public $isToTerminal = 0;
    public $transportName  ="";
    public $consignee  ="";
    public $payer = "";
    public $scladRef='';
    
    /* старый вариант*/
    public $statusRef = 0;
    public $statusText = "";    
    public $statusText2 = "";    
    public $schetStatus2 = 0; 
    public $isAlter = 0; 
    public $eventTime ="";
    
    /*служебные*/
    public $src ="none";
    public $changed = 0;    
    /*'schetStatus', 'schetStatus2', 'statusRef',*/
    
   /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataId;
    public $dataVal;
    
    public $debug;  
    
    public function rules()
    {
        return [
            [['id','eventId', 'zakazId', 'orgId',  'schetNumber','schetDate', 'schetINN',  
              'contactFIO', 'contactEmail', 'contactPhone', 'note', 'status', 'nextContactDate', 'nextContactTime','docStatus', 'cashState', 'supplyState',
              'delay', 'txtSupllyFull', 'supplyDate', 'dstType', 'dstNote',  'dstRef', 'adress',
              'isToTerminal',  'transportName',  'consignee', 'payer', 'src', 'scladRef', 'showTransport',
              'recordId','dataType','dataVal','dataId'              
              ], 'default'],
  
            [['docStatus', 'cashState', 'supplyState','schetNumber','orgTitle', 'schetStatus'], 'safe'],
            [['contactFIO', 'contactEmail', 'contactPhone', 'note', 'schetINN', 'schetNumber','schetDate'], 'trim'],
            ['contactFIO', 'string', 'length' => [1,150]],                        
            ['status', 'in', 'range' => [0,1,2,3]],
            ['showTransport', 'in', 'range' => [0,1,2,3]],                                    
            ['dstType', 'in', 'range' => [0,1,2]],                
            ['docStatus', 'in', 'range' => [0,1,2,3,4,5,6]],                
            ['cashState', 'in', 'range' => [0,1,2,3,4,5,6]],                
            ['supplyState', 'in', 'range' => [0,1,2,3,4,5,6]],                
            ['zakazId', 'integer'],            
            ['orgId', 'integer'],
            ['eventId', 'integer'],            
            ['id', 'integer'],
            ['contactEmail', 'email'],
        ];
    }

/*************************************************************************************/
/*************************************************************************************/
/* Ajax */

   /**********************************/
   public function saveDstData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId,              
             'val' => '',
           ];   
           
           
           
    switch ($this->dataType)
    {        
        case 'dstNote':
          $this->dataVal = trim($this->dataVal);
          if (empty($this->dataVal)) return $res;            
          if (empty($this->recordId) || $this->recordId<0){
            $record= new TblOrgDostavka();
            if (empty($record)) return $res;            
            $record->refOrg = intval($this->dataId);
          } else 
          {
            $record= TblOrgDostavka::findOne(intval($this->recordId));     
            if (empty($record)) return $res;            
          }             
           $record->note = $this->dataVal;              
           $record->save(); 
           $res['val'] =  $record->note;
           $res['recordId'] =  $record->id;
           break;
     }      
     
    $res['res'] = true;    
    return $res;
    }

   /**********************************/
   public function saveAjaxData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId' => $this->dataId, 
             'isReload' => false,
             'val' => '',
           ];   
           
           
           
    switch ($this->dataType)
    {
        case 'wareTitle':
           $record= TblSchetContent::findOne(intval($this->recordId));     
           if (empty($record)) return;
           $record->wareTitle = mb_substr($this->dataVal, 0, 100, 'utf-8');
           $record->save(); 
           $res['val'] =  $record->wareTitle ;
           break;
        case 'wareCount':
           $record= TblSchetContent::findOne(intval($this->recordId));     
           if (empty($record)) return;        
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->wareCount = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->wareCount ;
           break;
        case 'wareEd':
           $record= TblSchetContent::findOne(intval($this->recordId));     
           if (empty($record)) return;       
           $record->wareEd = mb_substr($this->dataVal, 0, 20, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->wareEd ;
           break;
        case 'warePrice':
           $record= TblSchetContent::findOne(intval($this->recordId));     
           if (empty($record)) return;        
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->warePrice = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->warePrice ;
           break;
        case 'dopRequest':
           $record= TblSchetContent::findOne(intval($this->recordId));     
           if (empty($record)) return;        
           $record->dopRequest = mb_substr($this->dataVal, 0, 150, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->dopRequest ;
           break;
           
        case 'addWareFromPrice':
         $priceRecord= TblWareNames::findOne(intval($this->dataId));          
         if (empty($priceRecord)) return $res;
         $record= new TblSchetContent();              
         if (empty($record)) return $res;
         
           $record->refSchet = intval($this->recordId);          
           $record->wareTitle = $priceRecord->wareTitle;
           $record->wareEd   = $priceRecord->wareEd;           
           $record->wareListRef   = $priceRecord->wareListRef;
           $record->wareNameRef   = $priceRecord->id;
           $record->warehouseRef  = $priceRecord->warehouseRef;
           
           switch($this->dataVal)
           {
            case 'v1':
               $record->warePrice = $priceRecord->v1;           
            break;
            case 'v2':
               $record->warePrice = $priceRecord->v2;           
            break;
            case 'v3':
               $record->warePrice = $priceRecord->v3;           
            break;
            case 'v4':
               $record->warePrice = $priceRecord->v4;           
            break;
           }
                      
           $record->save(); 
           $res['val'] =  $record->value ;
           $res['isReload'] = true;
           break;

        case 'showTransport':
           $record= SchetList::findOne(intval($this->recordId));     
           if (empty($record)) return;
           $record->showTransport = intval($this->dataVal);          
           $record->save(); 
           $res['val'] =  $record->showTransport;
           break;
                 
                      
     }      
     
    $res['res'] = true;    
    return $res;
    }

   public function saveAjaxParam()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'needReload' => 1,
             'val' => '',
           ];   
           
     $record= SchetList::findOne(intval($this->recordId));     
     if (empty($record)) return;
           
           
    switch ($this->dataType)
    {
        case 'ref1C':
           $ref = intval($this->dataVal);
           $clientRec= TblClientSchetHeader::findOne($ref);
           if (empty($clientRec)) return $res;
           $record->ref1C = $clientRec->schetRef1C;
           $record->refClientSchet = $clientRec->id ;
           if ($record->docStatus <3) $record->docStatus =3;
           $record->save(); 
           $res['val'] =  $clientRec->schetRef1C;
           break;
           
           
           case 'schetNote':               
           $noteRecord = TblSchetNote::findOne(['refSchet' => $record->id ]);        
           if (empty($noteRecord)) $noteRecord = new TblSchetNote();           
           if (empty($noteRecord)) return $res;
           $noteRecord->refSchet  = $record->id; 
           $noteRecord->schetNote = $this->dataVal;           
           $noteRecord->save(); 
           $res['needReload'] = 0;
           $res['val'] =  $noteRecord->schetNote;
           break;           
           
    }    
    $res['res'] = true;    
    return $res;

    }
   /**********************************************/    

   public function copyWareInSchet($schetId)
   {
       $ret=[
        'schetId' => $schetId, 
        'res' => false
    ];
       $record = SchetList::findOne($schetId);   
       if (empty($record )) return $ret;
       if (empty($record->refClientSchet ))return $ret;

       Yii::$app->db->createCommand( 'DELETE FROM {{%schetContent}} where  refSchet=:refSchet', 
        [':refSchet' => $record->id])->execute();

       Yii::$app->db->createCommand( 'INSERT INTO {{%schetContent}}
       (refSchet, wareTitle, wareCount, wareEd, warePrice)
       SELECT 
       :refSchet, wareTitle, wareCount, wareEd, wareSum/wareCount                
       FROM {{%client_schet_content}}
       where  refHeader=:refHeader ', 
        [':refSchet' => $record->id,
         ':refHeader' =>$record->refClientSchet        
        ])->execute();

       $ret['res'] = true;
       return $ret;       
   }

   public function rmWareSchet($schetId, $wareRef)
   {
       $ret=[
        'schetId' => $schetId, 
        'wareRef' => $wareRef, 
        'res' => false
    ];


       $record = TblSchetContent::findOne($wareRef);
       if (empty($record )) return $ret;

    Yii::$app->db->createCommand('UPDATE {{%otves_list}}, {{%ware_names}} set refSchet = 0, inUse=0
           WHERE  {{%otves_list}}.refWareList =  {{%ware_names}}.wareListRef  AND {{%ware_names}}.id = :refWareList and refSchet =:refSchet',
           [
               ':refWareList' => $record ->wareNameRef,
               ':refSchet'    => $record ->refSchet,
           ])->execute();
       $record->delete();
       $ret['res'] = true;
       return $ret;       
   }
   public function addWareSchet($schetId, $wareRef, $wareEd)
   {
       $ret=[
        'schetId' => $schetId,
        'wareRef' => $wareRef,
        'wareEd' => $wareEd,
        'res' => false
    ];
   
   if ($wareRef==0)
   {
       $record = new TblSchetContent();   
       $record->refSchet = intval($schetId);
       $record->save();    
       $ret['res'] = true;
       return $ret;       
   }
   
    $wareRecord = TblWareNames::findOne($wareRef);
    if(empty($wareRecord)) return $ret;
    $wareRecord->useCount++;
    $wareRecord->save();

    $record = new TblSchetContent();
    if(empty($record)) return $ret;
        
      $record->refSchet = intval($schetId);
      $record->wareTitle = $wareRecord->wareTitle;
      $record->wareCount = 0;
      $record->wareEd = $wareRecord->wareEd;
      $record->wareListRef = $wareRecord->wareListRef;
      $record->wareNameRef = $wareRecord->id;
      $record->warePrice = $wareRecord->v3;
      $record->save();    
   $ret['res'] = true;
   return $ret;       
   }

/******************/

   public function getActList ()
   {

        return Yii::$app->db->createCommand('SELECT id, actNum, actDate  FROM  {{%schet_act}} where refSchet =:refSchet',
           [
               ':refSchet'    => $this->id,
           ])->queryAll();
       
   }
   




/*******************/
   public function copyTransportFromZakaz($schetId)
   {
       $ret=[
        'schetId' => $schetId,
        'res' => false
    ];
       $record = SchetList::findOne($schetId);
       if (empty($record )) return $ret;

       Yii::$app->db->createCommand( 'DELETE FROM {{%schetTransport}} where  refSchet=:refSchet',
        [':refSchet' => $record->id])->execute();

       Yii::$app->db->createCommand( 'INSERT INTO {{%schetTransport}}
       (refSchet, type, route, note, price, typeText, weight, val)
       SELECT
       :refSchet, type, route, note, price, typeText, weight, val
       FROM {{%zakazTransport}}
       where  refZakaz=:refZakaz',
        [':refSchet' => $record->id,
         ':refZakaz'  => $record->refZakaz
        ])->execute();

       $ret['res'] = true;
       return $ret;
   }
   /**********************************************/    
    public function load1CSchetData($ref)
    {
      $schetData =  Yii::$app->db->createCommand( 'SELECT sum(wareSum) as schetSumm, schetRef1C,  schetDate  
                from {{%client_schet_header}} left join {{%client_schet_content}} on {{%client_schet_header}}.id={{%client_schet_content}}.refHeader
                where {{%client_schet_header}}.id =:refClientSchet', 
                [':refClientSchet' => $ref ])->queryOne();
        
      return  $schetData;      
    }
    public function loadSchetData()
    {

        $this->orgRecord = $this->getOrgRecordBySchet();
        $this->contactPhone = $this->orgRecord->contactPhone;
        $this->contactEmail = $this->orgRecord->contactEmail;
        $this->contactFIO   = $this->orgRecord->contactFIO;      
        
        $this->status = 1;                 
        
        $eventRegModel = new EventRegForm(); 
        //$this->nextContactDate = date("d.m.Y", time()+60*60*24);
        $this->nextContactDate = "";
        $curUser=Yii::$app->user->identity;                     
        //$this->nextContactTime = $eventRegModel->getFreeTime($this->nextContactDate, $curUser->id);
        $this->nextContactTime = "-";
            
        $schetRecord = SchetList::findOne($this->id);
        if (empty ($schetRecord)) {return false;}
        $this->schetNumber = $schetRecord->schetNum;
        $this->schetINN = $schetRecord->schetINN;
        $this->isAlter =  $schetRecord->isAlter;
        $this->schetDate = date("d.m.Y", strtotime($schetRecord->schetDate));    
        
        $this->docStatus =  $schetRecord->docStatus;
        if ($schetRecord->docStatus < 4 && !empty($schetRecord->ref1C)) $schetRecord->docStatus = 3;
        
        $this->showTransport=  $schetRecord->showTransport;
        $this->cashState =  $schetRecord->cashState;
        $this->supplyState =  $schetRecord->supplyState;
        $this->orgId = $schetRecord->refOrg;
        $this->zakazId = $schetRecord->refZakaz;
        $zakazRecord = ZakazList::findOne($schetRecord->refZakaz); 
        if (!empty($zakazRecord)) $this->zakazDate = $zakazRecord->formDate;
        $this->zakazSum = Yii::$app->db->createCommand( 'SELECT sum(count*value) from {{%zakazContent}} where
        isActive = 1 AND refZakaz=:refZakaz ', 
        [':refZakaz' => $this->zakazId])->queryScalar();
        if(empty($this->zakazSum))$this->zakazSum = 0;
        $this->zakazSum += Yii::$app->db->createCommand( 'SELECT sum(val) from {{%zakazTransport}} where
        refZakaz=:refZakaz ', 
        [':refZakaz' => $this->zakazId])->queryScalar();
 
 
        $this->status =1;
        if ($schetRecord->isSchetActive == 0)    $this->status =3;                   
        if ($schetRecord->isReject == 1)$this->status =2;
        
        /*    Считаем сумму  */
        //if ($schetRecord->schetSumm == 0)
        {
         $schetSumm = Yii::$app->db->createCommand( 'SELECT sum(wareCount*warePrice) from {{%schetContent}} 
        where refSchet=:refSchet', [':refSchet' => $this->id])->queryScalar();
        if ($schetRecord->schetSumm != $schetSumm){        
            $schetRecord->schetSumm = $schetSumm;
            $schetRecord->save();}        
        
        }
        
        $this->sumExtract = Yii::$app->db->createCommand( 'SELECT sum(lnkSum) from  {{%schet_extract_lnk}}
        where
        {{%schet_extract_lnk}}.schetRef=:refSchet', [':refSchet' => $this->id])->queryScalar();
        
        /*    Ищем ссылку на поставку  и статус поставки */        
         $supplyList = Yii::$app->db->createCommand( 'SELECT {{%request_supply}}.id, {{%request_supply}}.supplyState, st17  from {{%request_supply}} 
         left join {{%supply_status}} on {{%supply_status}}.refSupply = {{%request_supply}}.id
          where  refSchet=:refSchet', 
        [':refSchet' => $schetRecord->id])->queryAll();
        $this->supplyRequestId = 0;//Подстрахуемся
        $this->isFinishSupplyRequest= 0;//два раза подстрахуемся
        if (count ($supplyList) >0 )
        {
         /* пофиг берем первую - если больше одной то вообще говоря была ошибка - потрется потом*/
         $this->supplyRequestId = $supplyList[0]['id']; /* заявка на поставку*/             
         $this->supplyRequestStatus = $supplyList[0]['supplyState']; /* Текущий статус поставки */             
         //$this->isFinishSupplyRequest=strtotime($supplyList[0]['st17']);
        if (strtotime($supplyList[0]['st17']) > 100) $this->isFinishSupplyRequest= 1;         
        }

 

        /*Ищем лид*/
        $this->leadData = Yii::$app->db->createCommand( 'SELECT id, contactDate from {{%contact}} where refZakaz=:refZakaz AND eventType >=10 AND eventType <100 ', 
        [':refZakaz' => $schetRecord->refZakaz])->queryOne();
        if (empty($this->leadData)) {$this->leadData['id']=0;}
        
        if ($schetRecord->docStatus == 0)
        {
            $this->setCompatabilityToOldSchet($schetRecord);
        }/*совместимость*/
        
        $noteRecord = TblSchetNote::findOne(['refSchet' => $schetRecord->id ]);    
        if(!empty($noteRecord))$this->schetNote =$noteRecord->schetNote; 
        
        
      return $schetRecord;
    }
    
    public function copyFromZakaz ($refSchet, $refZakaz)
    {
    
           /*Проверяем содержимое   */
        if ($countWare == 0) {
          Yii::$app->db->createCommand(
            'INSERT INTO {{%schetContent}}
            (refSchet, zakazContentRef, wareTitle, wareCount, wareEd, warePrice,
            dopRequest, dostavka, warehouseRef, wareListRef)
            SELECT :refSchet, id, good, count, ed, value, 
            dopRequest, dostavka, warehouseRef, wareListRef
            from {{%zakazContent}}  where isActive=1 AND refZakaz=:refZakaz', 
            [
            ':refSchet' => $refSchet,            
            ':refZakaz' => $refZakaz,            
            ])->execute(); 
        }
    
    }
    
        
    /************************************************************************
                Совместимость со старой версией    
    ************************************************************************/
    public function setCompatabilityToOldSchet($schetRecord)
    {
    
        $count = Yii::$app->db->createCommand( 'SELECT count(id) from {{%schet_status}} where refSchet=:refSchet', 
        [':refSchet' => $this->id])->queryScalar();
           if ($count == 0) {return;}
        
        $statusCur = Yii::$app->db->createCommand('SELECT max(refOp) from {{%schet_status}} where refSchet=:refSchet', 
           [':refSchet' => $this->id])->queryScalar();

        if($schetRecord->isAlter == 0)        
        {
         switch ($statusCur)
         {
           case 0:
           /*Счет зарегистрирован*/
            $this->docStatus = 1;
            $schetRecord->docStatus= 1;           
           break;
           case 1:
           /*Счет получен клиентом*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
           break;
           case 2:
           /*Оплата произведена*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 2;  
            $schetRecord->cashState = 2;
           break;
           case 3:
           /*Деньги дошли*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
           break;            
           case 4:
           /*Задание на отгрузку*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 1;
            $schetRecord->supplyState = 1;
           break;
           case 5:
           /*Поставка произведена*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 2;
            $schetRecord->supplyState = 2;
           break;
           case 6:
           /*Клиент подвердил поставку*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 3;
            $schetRecord->supplyState = 3;
           break;
           case 7:
           /*Отзыв получен*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 4;
            $schetRecord->supplyState = 4;
           break;        
           case 8:
           /*Работа со счетом завершена /Документы сданы*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 5;
            $schetRecord->supplyState = 5;
           break;
         }
        } else
        {
         switch ($statusCur)
         {
           case 0:
           /*Счет зарегистрирован*/
            $this->docStatus = 1;
            $schetRecord->docStatus= 1;           
           break;
           case 1:
           /*Счет получен клиентом*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
           break;
           case 2:
           /*Гарантийные документы получены*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 1;  
            $schetRecord->cashState = 1;
           break;
           case 3:
           /*Задание на отгрузку*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 1;
            $schetRecord->supplyState = 1;
           break;
           case 4:
           /*Поставка произведена*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 2;
            $schetRecord->supplyState = 2;
           break;
           case 5:
           /*Клиент подвердил поставку*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 3;
            $schetRecord->supplyState = 3;
           break;
           case 6:
           /*Деньги дошли*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
           break;            
           case 7:
           /*Отзыв получен*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 4;
            $schetRecord->supplyState = 4;
           break;        
           case 8:
           /*Работа со счетом завершена /Документы сданы*/
            $this->docStatus = 2;
            $schetRecord->docStatus= 2;           
            $this->cashState = 4;  
            $schetRecord->cashState = 4;
            $this->supplyState = 5;
            $schetRecord->supplyState = 5;
           break;
         }        
        }//Alter
        
        $schetRecord->save();
    }
 /***********************/    
    public function createSchet($zakazId, $orgId)
    {
        $zakazId = intval($zakazId); 
        $orgId   = intval($orgId); 
        
      $curUser=Yii::$app->user->identity;                   
      $res = [ 'res' => false, 
                'zakazId'  => $zakazId, 
                'orgId'    => $orgId,  
                'schetId'  => 0,
             ];   
        
        if (!empty($zakazId))
        {
          $zakazRecord= ZakazList::findOne($zakazId);              
          if (empty($zakazRecord)) return $res;            
          $orgId = $zakazRecord->refOrg; 
        }
        $res['orgId'] = $orgId;  
        $orgRecord = OrgList::findOne($orgId);              
        if (empty($orgRecord)) return $res;            

        /*Создаем*/
        $schetRecord = new SchetList;

        $schetRecord->schetINN   = $orgRecord->orgINN;
        $schetRecord->schetDate  = date("Y-m-d");        
        $schetRecord->refOrg   = $orgRecord->id;
        $schetRecord->refManager   = $curUser->id;
        $schetRecord->refZakaz = $zakazId;
        $schetRecord->save();    
        

        /*Копируем содержимое*/
        if (!empty($zakazRecord))
        {
        $zakazRecord->isFormed = 1; //заявку считаем согласованной          
        $zakazRecord->save();              
        Yii::$app->db->createCommand(
            'INSERT INTO {{%schetContent}}
            (refSchet, zakazContentRef, wareTitle, wareCount, wareEd, warePrice,
            dopRequest, dostavka, warehouseRef, wareListRef, wareNameRef)
            SELECT :refSchet, id, good, count, ed, value, 
            dopRequest, dostavka, warehouseRef, wareListRef, wareNameRef
            from {{%zakazContent}}  where isActive=1 AND refZakaz=:refZakaz', 
            [
            ':refSchet' => $schetRecord->id,
            ':refZakaz' => $zakazRecord->id,
            ])->execute();


       Yii::$app->db->createCommand( 'INSERT INTO {{%schetTransport}}
            (refSchet, type, route, note, price, typeText, weight, val)
            SELECT
            :refSchet, type, route, note, price, typeText, weight, val
            FROM {{%zakazTransport}}
            where  refZakaz=:refZakaz',
            [':refSchet' => $schetRecord->id,
            ':refZakaz'  => $zakazRecord->id,
            ])->execute();
        }

        /*Связываем отвесы*/
        Yii::$app->db->createCommand('UPDATE {{%otves_list}} set refSchet = :refSchet
           WHERE refZakaz =:refZakaz',
           [
               ':refSchet' => $schetRecord->id,
               ':refZakaz' => $zakazId,
           ])->execute();


       /*Добавим запись в календарь*/
       $calendar = new MarketCalendarForm();
       $event_ref = 6;
       $eventNote = "Передать счет клиенту";
       $eventRegModel = new EventRegForm(); 
       $this->nextContactTime = $eventRegModel->getFreeTime($this->nextContactDate, $curUser->id);
       $calendar->createEventTime(date("Y-m-d",time()+60*60*24*1), $this->nextContactTime, $event_ref ,
       $schetRecord->refOrg, $schetRecord->refZakaz, 0, $eventNote, 0);      
    
       $schetRecord->schetSumm = Yii::$app->db->createCommand(
            'SELECT SUM(ifnull(wareCount,0)*ifnull(warePrice,0)) FROM {{%schetContent}}
            where refSchet=:refSchet', 
            [
            ':refSchet' => $schetRecord->id,            
            ])->queryScalar(); 
              /*сгенерили id*/
              
       $prefix = $this->getCfgValue(1206);              
       $suffix = $this->getCfgValue(1208);              
       $shift = intval($this->getCfgValue(1207));              
              $num= $schetRecord->id+$shift;
       $schetRecord->schetNum   = $prefix.$num.$suffix;             
       $schetRecord->save();    
      
    
       $res['schetId'] = $schetRecord->id;
       return $res;           
    }

   /**********************************************/    

    public function regSchet()
    {
        $curUser=Yii::$app->user->identity;                 

        $schetRecord = new SchetList;
        $schetRecord->schetNum   = $this->schetNumber;
        $schetRecord->schetINN   = $this->schetINN;
        $schetRecord->schetDate  = date("Y-m-d", strtotime($this->schetDate));
        
        $schetRecord->refOrg   = $this->orgId;
        $schetRecord->refManager   = $curUser->id;
        $schetRecord->refZakaz = $this->zakazId;
        $schetRecord->save();    
                
        $zakazRecord = ZakazList::findOne($this->zakazId);
        $zakazRecord->isActive=0;     
        $zakazRecord->save();
             
        
       /*Добавим запись в календарь*/
       $calendar = new MarketCalendarForm();
       $event_ref = 6;
       $eventNote = "Передать счет клиенту";
        $eventRegModel = new EventRegForm(); 
        $this->nextContactTime = $eventRegModel->getFreeTime($this->nextContactDate, $curUser->id);
       $calendar->createEventTime(date("Y-m-d",time()+60*60*24*1), $this->nextContactTime, $event_ref ,
       $schetRecord->refOrg, $schetRecord->refZakaz, 0, $eventNote, 0);      
    
     return    $schetRecord->id;
        
    }
   
    public function showSupplyStatus()    
    {
          //$schetRecord = SchetList::findOne($this->id);
          //if (empty ($schetRecord)) {return "Заявка на отгрузку";}

       return "Заявка на отгрузку";
    } 
    public function getSchetStatusProvider()
    {
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%schet_status}} where refSchet=:refSchet', 
            [':refSchet' => $this->id])->queryScalar();
            
        if ($this->isAlter ==1)
        {            
            $strSql = 'SELECT {{%schet_status}}.id, {{%schet_status}}.refSchet, {{%schet_status}}.refOp, {{%schetop}}.opAlter as opTitle,
                     {{%schet_status}}.dateOp, {{%schet_status}}.refContact, 
                     {{%contact}}.contactFIO, {{%contact}}.note as contactNote, {{%contact}}.contactDate
               FROM   {{%schet_status}}
               LEFT JOIN {{%schetop}} on {{%schetop}}.id = {{%schet_status}}.refOp
               LEFT JOIN {{%contact}} on {{%contact}}.id = {{%schet_status}}.refContact
               where refSchet=:refSchet ';
        }
        else
        {
            $strSql =     'SELECT {{%schet_status}}.id, {{%schet_status}}.refSchet, {{%schet_status}}.refOp, {{%schetop}}.opTitle,
                     {{%schet_status}}.dateOp, {{%schet_status}}.refContact, 
                     {{%contact}}.contactFIO, {{%contact}}.note as contactNote, {{%contact}}.contactDate
               FROM   {{%schet_status}}
               LEFT JOIN {{%schetop}} on {{%schetop}}.id = {{%schet_status}}.refOp
               LEFT JOIN {{%contact}} on {{%contact}}.id = {{%schet_status}}.refContact
               where refSchet=:refSchet ';               
        }
        
        
          $provider = new SqlDataProvider(['sql' => $strSql,
            'params' => [':refSchet' => $this->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'opTitle',
            'dateOp', 
            'contactFIO', 
            'contactDate', 
            ],
            ],
        ]);
        return $provider;
    }
    
    
/*
[['docStatus', 'cashState', 'supplyState','schetNumber','orgTitle', 'schetStatus'], 'safe'],
*/    
    public function getActiveSchetListProvider($params)
    {
        
        $curUser=Yii::$app->user->identity;

    /*Получим спистки статусов*/        
     $listStatus = $this-> getListStatus();
     $schetStatus=$listStatus['schet_status'];
     $maxSchetStatus=$schetStatus[count($schetStatus)-1]['razdelOrder'];
     $cashStatus=$listStatus['cash_status'];
     $maxCashStatus=$cashStatus[count($cashStatus)-1]['razdelOrder'];
     $supplyStatus=$listStatus['supply_status'];
     $maxSupplyStatus=$supplyStatus[count($supplyStatus)-1]['razdelOrder'];
                 
    $query  = new Query();
       $query->select ("{{%schet}}.id, {{%schet}}.refOrg as orgId, {{%schet}}.refZakaz AS zakazId, {{%schet}}.schetNum, 
            {{%schet}}.schetDate, {{%orglist}}.title as orgTitle, isOplata, isAlter, docStatus, cashState, supplyState, ref1C, 
            schetSumm, summOplata, summSupply")
            ->from("{{%schet}}")            
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->where("isSchetActive =1 AND {{%schet}}.refManager=:refUser" );
            

    $countquery  = new Query();
       $countquery->select (" count({{%schet}}.id)")
            ->from("{{%schet}}")                        
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->where("isSchetActive =1 AND {{%schet}}.refManager=:refUser" );

    
     if (($this->load($params) && $this->validate())) {     
     
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
    
        $query->andFilterWhere(['like', 'schetNum', $this->schetNumber]);
        $countquery->andFilterWhere(['like', 'schetNum', $this->schetNumber]);


        switch ($this->schetStatus)
        {
            case 1:  
            $query->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);
            $countquery->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);
            break;
            case 2:  
            $query->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            $countquery->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            break;
            case 3:  
            $query->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);
            $countquery->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);        
            break;
        }     

        
     }
    /*schetStatus в любом случае*/
    else {
        switch ($this->schetStatus)
        {
            case 1:  
            $query->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);
            $countquery->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);
            break;
            case 2:  
            $query->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            $countquery->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            break;
            case 3:  
            $query->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);
            $countquery->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);        
            break;
        }     
    }    
    $query->addParams([':refUser' => $curUser->id]);
    $countquery->addParams([':refUser' => $curUser->id]);
    
    $command = $query->createCommand();    
    $count = $countquery->createCommand()->queryScalar();
        
    
    $provider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,            
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'orgTitle',
            'schetDate',
            'schetNum',
            'isOplata',
            'ref1C',
            'summOplata',
            'summSupply',
            ],
            ],
        ]);
        return $provider;        
    }
    

     public function getActiveSupplyListProvider()
    {
        
        $curUser=Yii::$app->user->identity;
        
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%schet}} where refManager=:refUser AND isSupply =1    AND isSchetActive =1', 
            [':refUser' => $curUser->id])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT {{%schet}}.id, {{%schet}}.refOrg as orgId, {{%schet}}.refZakaz AS zakazId, {{%schet}}.schetNum, 
            {{%schet}}.schetDate, {{%orglist}}.title, isOplata, isAlter, docStatus, cashState, supplyState, ref1C,  ifnull(schetSumm,0) as schetSumm, ifnull(summOplata,0) as summOplata, ifnull(summSupply,0) as summSupply
            FROM {{%schet}}, {{%orglist}}
            WHERE  {{%schet}}.refOrg = {{%orglist}}.id AND  {{%schet}}.refManager=:refUser AND isSupply =1    AND isSchetActive =1    
            ',
            'params' => [':refUser' => $curUser->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'schetDate',
            'schetNum',
            'isOplata',
            'ref1C',
            'summOplata',
            'summSupply',
            ],
            ],
        ]);
        return $provider;        
    }
    



    

   public function getZakazDetailProvider()
   {
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%zakazContent}} where refZakaz=:zakazId', 
            [':zakazId' => $this->zakazId])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT {{%zakazContent}}.id, {{%zakaz}}.refOrg as orgId, {{%zakazContent}}.isActive, {{%zakaz}}.id AS zakazId, initialZakaz, good, spec, ed, value, count,  dopRequest, dostavka  FROM   {{%zakazContent}}, {{%zakaz}}                   
               where {{%zakazContent}}.refZakaz = {{%zakaz}}.id                   
                 AND  refZakaz=:zakazId ',
            'params' => [':zakazId' => $this->zakazId],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'initialZakaz',
            'good', 
            'spec', 
            'ed', 
            'value',             'dopRequest', 
            'dostavka', 
            'isActive',
            ],
            'defaultOrder' => [    'isActive' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }  
   
    public function getNeedList()
    {
      $strSql = "Select ref_org, ref_need_title, {{%need_title}}.`Title`    from {{%schet_need}}, {{%need_title}}
                  where {{%schet_need}}.`ref_need_title` = {{%need_title}}.`id` and {{%schet_need}}.ref_org =:ref_org";
      $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->orgId])->queryAll();             
      return $ret;
    }
    
    public function getContactDetail()
    {
    $ret = array();
    if ($this->id > 0)
    {
      $schetRecord=$this->getSchetRecord();
      $strSql = "Select contactFIO, note, contactDate, phone, status from {{%contact}} left join {{%phones}} 
                 on {{%contact}}.ref_phone={{%phones}}.id  where {{%contact}}.ref_org=:ref_org AND
                 {{%contact}}.refZakaz = :refZakaz
                 order by {{%contact}}.id DESC";
            
      $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->orgId, ':refZakaz' =>$schetRecord->refZakaz ])->queryAll();             
     }
     if (count($ret) == 0)
     {
           $strSql = "Select contactFIO, note, contactDate, phone, status from {{%contact}} left join {{%phones}} 
                 on {{%contact}}.ref_phone={{%phones}}.id  where {{%contact}}.ref_org=:ref_org 
                 order by {{%contact}}.id DESC LIMIT 3";
    
          $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->orgId ])->queryAll(); 
     
     }
      
      
      return $ret;
    }

   public function getCfgValue($key)        
   {
     $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
            ':key' => intval($key),            
            ])->queryOne();  
            
    return $record['keyValue'];
   }
    
    
    public function getCompanyPhones()
   {
          $ret =  Yii::$app->db->createCommand('SELECT DISTINCT phone, status, phoneContactFIO from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryAll();       
        return $ret;
   }   
   
    
    public function getCurrentlyInWork()
   {
        $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND  ref_user=:ref_user '
                                             ,[':ref_user'=>$curUser->id] )->queryScalar();       
        return $ret;
   }   

    public function getCurrentlyNotInWork()
   {
        $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%orglist}} 
                                              where isPreparedForSchet=1 AND isSchetFinished=0 AND isInWork=0'
                                              )->queryScalar();       
        
        return $ret;
   }   
   
   public function getInWorkProvider()
   {
        $curUser=Yii::$app->user->identity;
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND ref_user =:ref_user ', 
            [':ref_user' => $curUser->id])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => ' SELECT id, title, contactDate  FROM   {{%orglist}} 
            where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND ref_user =:ref_user',         
            'params' => [':ref_user' => $curUser->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            ],
            ],
        ]);
    return $provider;
   }   

    public function getNotInWorkProvider()
   {
        $curUser=Yii::$app->user->identity;
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=:isInWork AND isSchetFinished=0', 
            [':isInWork' => 0])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT id, title, isSchetReject, contactDate, nextContactDate FROM   {{%orglist}}                   
                WHERE isPreparedForSchet=1  AND isInWork=:isInWork AND isSchetFinished=0 ',
            'params' => [':isInWork' => 0],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            ],
            ],
        ]);
    return $provider;
   }   

   
   public function getSchetRecord()
    {
        $schetRecord   = SchetList::findOne($this->id);
        
        return $schetRecord;
    }

       public function getOrgRecordBySchet()
    {
        $schetRecord   = SchetList::findOne($this->id);
        $this->orgId   = $schetRecord->refOrg;            
        $orgRecord = OrgList::findOne($this->orgId);
        return $orgRecord;
    }

       public function getZakazRecordBySchet()
    {
        $schetRecord   = SchetList::findOne($this->id);
        $this->zakazId   = $schetRecord->refZakaz;            
        $zakazRecord = ZakazList::findOne($this->zakazId);
        return $zakazRecord;
    }

    
    public function getListStatus()
    {
        $schetRecord   = SchetList::findOne($this->id);
        $schetStatusList = array();
        
        $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =1 order BY razdelOrder')->queryAll();        
        $schetStatusList['schet_status'] = $list;
        $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =2 order BY razdelOrder')->queryAll();        
        $schetStatusList['cash_status'] = $list;
        $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =3 order BY razdelOrder')->queryAll();        
        $schetStatusList['supply_status'] = $list;

        return $schetStatusList;        
    }    
    
    
    public function getZakazDetailBySchet()
    {
        //$schetRecord   = SchetList::findOne($this->id);
        $list = Yii::$app->db->createCommand(
            'SELECT {{%schetContent}}.id, wareTitle,  wareEd, warePrice, wareCount, dopRequest
            FROM   {{%schetContent}}  where {{%schetContent}}.refSchet = :refSchet', 
        [':refSchet' => $this->id])->queryAll();        
        return $list;        
    }    

    
    public function getSupplyRequestProvider($params)
   {
    
    $curUser=Yii::$app->user->identity;
    $query  = new Query();
    $query->select ("{{%request_supply}}.id, requestDate, refSchet, supplyDate, {{%schet}}.schetNum, {{%schet}}.schetDate, summOplata, schetSumm, supplyType, {{%request_supply}}.contactPhone, {{%request_supply}}.contactFIO, {{%request_supply}}.contactEmail, {{%request_supply}}.adress, requestNote, {{%request_supply}}.supplyState, dstNote, finishDate, execNum, supplyNote, userFIO, title ")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->where(['=', '{{%schet}}.refManager', $curUser->id]);
            
    $countquery  = new Query();
    $countquery->select (" count({{%request_supply}}.id)")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->where(['=', '{{%schet}}.refManager', $curUser->id]);

             
     $query->andWhere(['=', 'isSchetActive', 1]);
     $countquery->andWhere(['=', 'isSchetActive', 1]);
            
     if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);

     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     
     }
   

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

  /* 
    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "good";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }
*/

    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'id', 
            'requestDate', 
            'supplyDate', 
            'supplyType', 
            'supplyState', 
            'finishDate', 
            'execNum',
            'userFIO',
            'title',
            'schetNum', 
            'schetDate', 
            'summOplata', 
            'schetSumm',
            
            ],
            
            //'defaultOrder' => [ $this->setSort => $order ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
   public function loadDostavkaData()
   {

     $strSql = "SELECT {{%org_dostavka}}.id, {{%org_dostavka}}.note, {{%org_dostavka}}.isDefault FROM {{%org_dostavka}}, {{%schet}} 
              where {{%org_dostavka}}.refOrg = {{%schet}}.refOrg AND {{%schet}}.id =:refSchet Order BY isDefault DESC, id  "; 
     
     return Yii::$app->db->createCommand($strSql,
                [    
                    ':refSchet' => $this->id,
              ])->queryAll(); 
       
      //return ArrayHelper::map($list, 'id', 'note');      
   }
  
   public function loadRequestData()
  {
     $lst = $this->loadDostavkaData(); 
        
     $requestSupplyRecord = RequestSupplyList::FindOne
     ([
       'refSchet' => $this->id,       
     ]);       
     if (empty($requestSupplyRecord)) 
     {        
        if (count($lst)>0)
        {    
            $this->dstNote= $lst[0]['note'];
            $this->dstRef= $lst[0]['id'];
        }    
        return;
     }     
         

         
     $this->supplyDate = date("d.m.Y", strtotime($requestSupplyRecord ->supplyDate));     
     $this->dstType =$requestSupplyRecord ->supplyType;
     $this->contactPhone = $requestSupplyRecord ->contactPhone ;
     $this->contactFIO = $requestSupplyRecord ->contactFIO;
     $this->contactEmail = $requestSupplyRecord ->contactEmail;
     $this->adress = $requestSupplyRecord ->adress ;
     $this->note = $requestSupplyRecord ->requestNote;
     $this->dstNote = $requestSupplyRecord ->dstNote;
     $this->dstRef= $requestSupplyRecord ->dstRef;
     $this->scladRef = $requestSupplyRecord ->scladRef;
/*     if (empty($this->dstNote) && empty($this->dstRef)){
         
        if (count($lst)>0)
        {    
            $this->dstNote= $lst[0]['note'];
            $this->dstRef= $lst[0]['id'];
        }    
     }
*/     

     $this->isToTerminal  = $requestSupplyRecord ->isToTerminal;
     $this->transportName = $requestSupplyRecord ->transportName;       
     $this->consignee     = $requestSupplyRecord ->consignee;
     $this->payer         = $requestSupplyRecord ->payer;
  }
   public function regRequestSupply()        
   {
    $schetRecord = $this->getSchetRecord();
    $phoneList=$this->getCompanyPhones();
    $detailList= $this->getZakazDetailBySchet();
    $orgRecord = $this->getOrgRecordBySchet();
    $curUser=Yii::$app->user->identity;
            
    
    $listSupplyType = [ 0 => 'Самовывоз',
                        1 => 'Доставка клиенту',
                        2 => 'Передать транспортной компании',
                        ];
    
     $requestSupplyRecord = RequestSupplyList::FindOne
     ([
       'refSchet' => $this->id,       
     ]);       
     if (empty($requestSupplyRecord)) $requestSupplyRecord = new RequestSupplyList();
     if (empty($requestSupplyRecord)) return;
     
     $requestSupplyRecord ->requestDate = date("Y-m-d h:i:s");      
     $requestSupplyRecord ->supplyDate  = date("Y-m-d", strtotime($this->supplyDate));
     $requestSupplyRecord ->refSchet    = $this->id;
     $requestSupplyRecord ->supplyType  = $this->dstType;
     $requestSupplyRecord ->contactPhone = $this->contactPhone;
     $requestSupplyRecord ->contactFIO   = $this->contactFIO;
     $requestSupplyRecord ->adress       = $this->adress;
     $requestSupplyRecord ->requestNote  = $this->note;
     $requestSupplyRecord ->dstNote      = $this->dstNote;
     $requestSupplyRecord ->dstRef       = $this->dstRef;
     $requestSupplyRecord ->contactEmail = $this->contactEmail;
          
     $requestSupplyRecord ->scladRef      = $this->scladRef;
     
     $requestSupplyRecord ->isToTerminal  = $this->isToTerminal;
     $requestSupplyRecord ->transportName = mb_substr($this->transportName,0,150,'utf-8')  ;       
     $requestSupplyRecord ->consignee     = mb_substr($this->consignee,0,150,'utf-8')  ;
     $requestSupplyRecord ->payer         = mb_substr($this->payer,0,75,'utf-8')  ;
     
     $requestSupplyRecord ->save();
     
     if ($this->sendRequestSupply == 0) return true;
     
     $blank ="<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
    /* $blank .="
    <style> 
        table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 14pt;
        }
    </style>";*/
     $blank.="<div style='align:center;width:800px'><h2>Заявка на ОТГРУЗКУ №".$requestSupplyRecord ->id." от ".date ("d.m.Y", strtotime($requestSupplyRecord ->requestDate) )."</h2>\n";    
     $blank.="<p align='right'> от ".$curUser->userFIO."</p>\n";
     $blank.="<p> Клиент".$orgRecord->title."</p>\n";
     $blank.="<table border='0' style='border: 0px' width='80%'>\n";
     $blank.="<tr><td>Счёт № ".$schetRecord->schetNum." от  ".date ("d.m.Y", strtotime($schetRecord->schetDate) )."</td>"; 
     $blank.="<td>Сумма счета:". $schetRecord->schetSumm."</td> <td>Оплачено: ".$schetRecord->summOplata."</td></tr>\n";
     $blank.="</tr> </table>\n";
     $blank.="<hr>\n";    

     $blank.="<table border='1' style='border-collapse: collapse;' width='800px'>";
     $blank.="<tr><td style='padding:3px'>Наименование</td> <td style='padding:3px'>К-во </td> <td style='padding:3px'>ед.изм </td></tr>\n";
    for ($i=0; $i<count($detailList);$i++ )
    {
        if ($detailList[$i]['isActive'] == 0){continue;}
        $blank.="<tr>\n";
        if (empty($detailList[$i]['good'])){        $blank.="<td style=padding:3px'> ".$detailList[$i]['initialZakaz']."</td>\n"; }
                                      else {        $blank.="<td style=padding:3px'> ".$detailList[$i]['good']."</td>\n"; }
        $blank.="<td style=padding:3px'>".$detailList[$i]['count']."</td>\n";
        $blank.="<td style=padding:3px'>".$detailList[$i]['ed']."</td>\n";
        $blank.="</tr>\n";
    }
      $blank.=" </table>  <hr>"; 
     
     $blank.="<p>Дата отгрузки: <b>".$this->supplyDate."</b></p>\n";
     $blank.="<p>Доставка: <b>".$listSupplyType[$this->dstType]."</b> ".$this->dstNote."</p>\n";
     $blank.="<table width=100% border='0'><tr>";
     $blank.="<td style='padding:5px'>Контактный телефон: <b>".$this->contactPhone."</b> E-mail: <b>".$this->contactEmail."</b>  </td> ";
     $blank.="<td style='padding:5px'>Контактное лицо: <b>".$this->contactFIO."</b></td> </tr></table>\n";
       $blank.="<p>Адрес: <u>".$this->adress."</u></p>\n";
     $blank.="<hr>\n";
     $blank.="<p>Дополнения: <b>".$this->note."</b></p>\n";        
    
     $blank.="</div></body></html>"; 
     
     $mailer = new MailForm ();
     
     $fromEmail = $this->getCfgValue(1001);
     $email = $this->getCfgValue(1002).",".$this->getCfgValue(1003); 
     $subject = "Заявка на ОТГРУЗКУ №".$requestSupplyRecord ->id." от ".date ("d.m.Y", strtotime($requestSupplyRecord ->requestDate) );     
     $mailer->sendExtMail($email, $subject, $blank, $fromEmail, array());
     
     
    return $blank;
   }    
   /*****************************/
    public function finishSchet()
    {
         $schetRecord   = SchetList::findOne($this->id);
      if (empty($schetRecord)) {return false;} //Что-то пошло не так    
      $schetRecord->isSchetActive = 0;    
      $schetRecord->save(); 
        /*Пометим как выполнено в календаре*/
        $calendar = new MarketCalendarForm();
        $calendar->markRefEvent( $schetRecord->refOrg, $schetRecord->refZakaz);
        /*Добавим событие*/ 
           $eventRegModel = new EventRegForm(); 
        $this->nextContactTime = $eventRegModel->getFreeTime($this->nextContactDate, $curUser->id);                
        $calendarRecordId = $calendar->createEventTime(date('Y-m-d', $this->nextContactTime, time()+10*24*60*60), 8, $schetRecord->refOrg, 0, 0,
        "Продолжить контакт", 0);      
        return true; 
    }
   /******************************/
    public function saveData()        
   {
      $schetRecord   = SchetList::findOne($this->id);
      if (empty($schetRecord)) {return -1;} //Что-то пошло не так
   
      $orgRecord   = $this->getOrgRecordBySchet();
      $zakazRecord = $this->getZakazRecordBySchet();
      $curUser=Yii::$app->user->identity;

      $calendar = new MarketCalendarForm();
      
      
      
      $phoneCount = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%phones}} where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone'   => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ])->queryScalar();

      if ($phoneCount == 0)
      {          
         $phoneRecord = new PhoneList ();
         $phoneRecord->ref_org = $this->orgId;
         $phoneRecord->phone   = $this->contactPhone;
         $phoneRecord->save();
      }
      
      $phoneRecord = PhoneList::findOne([
      'ref_org' => $this->orgId,
      'phone'   => $this->contactPhone,
      ]);      
      
      if (empty($phoneRecord)) {return -2;} //Что-то пошло не так
      $phoneRef= $phoneRecord->id;
      

      
      /* общая часть */
      //$orgRecord->isInWork = 0;          
      $orgRecord->contactDate =  date("Y-m-d h:i:s");                                          
      $orgRecord->contactPhone = $this->contactPhone;
      $orgRecord->contactEmail = $this->contactEmail;
      $orgRecord->contactFIO = $this->contactFIO;
      $orgRecord->ref_user = $curUser->id;          
      $orgRecord->nextContactDate = date ("Y-m-d",strtotime($this->nextContactDate));
      $orgRecord->save();
          
      $phoneRecord->status = 1; /*помечаем телефон как надежный*/
      $phoneRecord->phoneContactFIO= $this->contactFIO;      
      $phoneRecord->save();


     $listStatus = $this-> getListStatus();
     $schetStatus=$listStatus['schet_status'];
     $maxSchetStatus=$schetStatus[count($schetStatus)-1]['razdelOrder'];
     $cashStatus=$listStatus['cash_status'];
     $maxCashStatus=$cashStatus[count($cashStatus)-1]['razdelOrder'];
     $supplyStatus=$listStatus['supply_status'];
     $maxSupplyStatus=$supplyStatus[count($supplyStatus)-1]['razdelOrder'];
 
     $isChangeStatus = 0;
     if ($schetRecord->docStatus   != $this->docStatus  ) $isChangeStatus = 1;
     if ($schetRecord->cashState   != $this->cashState  ) $isChangeStatus = 1;
     if ($schetRecord->supplyState != $this->supplyState) $isChangeStatus = 1;
            
      $schetRecord->docStatus =$this->docStatus; 
      $schetRecord->cashState =$this->cashState;
      $schetRecord->supplyState=$this->supplyState;
      $schetRecord->showTransport = $this->showTransport;
     if ( $this->supplyState == $maxSupplyStatus )  $this->status = 3;
      
     if ( $this->status == 3 )
     {
         /*счет завершен*/
        $schetRecord->docStatus = $maxSchetStatus;
        $schetRecord->cashState = $maxCashStatus;
        $this->supplyState = $maxSupplyStatus;
        $schetRecord->isSchetActive = 0;                       
     }         
      
     /* запоминаем контакт */    
     $ref_contact = 0;
     if ( !empty($this->contactPhone) || !empty($this->note) )
     {
      $contact = new ContactList();
      $contact->ref_phone = $phoneRef;
      $contact->ref_org = $this->orgId;
      $contact->ref_user = $curUser->id;
      $contact->refZakaz = $schetRecord->refZakaz;
      $contact->contactDate = date("Y-m-d  h:i:s");              
      $contact->contactFIO = $this->contactFIO;
      $contact->note = $this->note;

      $contact->docStatus  =$schetRecord->docStatus; 
      $contact->cashStatus  =$schetRecord->cashState;
      $contact->supplyStatus=$schetRecord->supplyState;
      $contact->isChangeStatus=$isChangeStatus;
            
      $contact->save();      
      $ref_contact = $contact->id;      
     }
     
     /* в работу */     
     if ( $this->status == 1)
     {
         $schetRecord->isReject = 0;       
         $schetRecord->isSchetActive =1;      
     }
     
     /* отказ */     
     if ( $this->status == 2)
     {
        $schetRecord->isReject = 1;       
         $schetRecord->isSchetActive = 0;      
          /* Освободим резервирование */
        Yii::$app->db->createCommand(
            'UPDATE {{%otves_list}} SET inUse =0, refSchet=0  WHERE refSchet =:refSchet ', 
            [
            ':refSchet' =>$schetRecord->id,            
            ])->execute();                  
          
            /* Освободим оплаты */            
        Yii::$app->db->createCommand(
            'UPDATE {{%oplata}} SET refSchet=0 WHERE refSchet =:refSchet ', 
            [
            ':refSchet' => $schetRecord->id,            
            ])->execute();                  

            /* Освободим поставки */            
        Yii::$app->db->createCommand(
            'UPDATE {{%supply}} SET refSchet=0 WHERE refSchet =:refSchet ', 
            [
            ':refSchet' => $schetRecord->id,            
            ])->execute();                  
     }
          
     if ($this->status > 1)
     {            
        /*со счетом покончено*/        
        $schetRecord->save(); 
        /*Пометим как выполнено в календаре*/
        $calendar->markRefEvent( $schetRecord->refOrg, $schetRecord->refZakaz);
        /*Добавим событие*/   
                      
        $calendarRecordId = $calendar->createEventTime($this->nextContactDate, $this->nextContactTime, 8,
        $schetRecord->refOrg, 0, $ref_contact, "Продолжить контакт", $ref_contact);      
        return 2; 
     }
     
     /*Еще активен*/
     
     /*Добавим запись в календарь*/
      $eventNote="";
        if ($this->docStatus < $maxSchetStatus)
      {          
          $eventNote="Ожидается:  ".$schetStatus[$this->docStatus]['Title']; 
          $event_ref = 6; /*ведение счета*/
      }
      else  if ($this->cashState < $maxCashStatus)
      {          
          $eventNote="Ожидается:  ".$cashStatus[$this->cashState]['Title']; 
          $event_ref = 6; /*ведение счета*/
      }
      
      if ( ($this->supplyState > 0 || $this->cashState == $maxCashStatus ) && $this->supplyState < $maxSupplyStatus)
      {          
          $eventNote="Ожидается:  ".$supplyStatus[$this->supplyState]['Title']; 
          $event_ref = 7; /*поставка*/
      }
        $calendarRecordId = $calendar->createEventTime($this->nextContactDate, $this->nextContactTime, 
        $event_ref, $schetRecord->refOrg, $schetRecord->refZakaz, $ref_contact, $eventNote, $ref_contact);      
                        
     

     /*Оплачено*/
     if ($this->cashState == $maxCashStatus) $schetRecord -> isOplata = 1; 
     else $schetRecord -> isOplata = 0;

     /*В процессе поставки*/      
     if ($this->supplyState > 0 || $this->cashState == $maxCashStatus ) $schetRecord -> isSupply = 1; 
     else $schetRecord->isSupply = 0;
        /*Текущий статус*/
        

      for ($i=0;$i<3;$i++)
      {                          
         $schetStatusRecord= new SchetStatusList();
         $schetStatusRecord->refSchet=$schetRecord->id;
         $schetStatusRecord->refZakaz=$schetRecord->refZakaz;
        // $schetStatusRecord->refOp=$this->statusRef;         
         $schetStatusRecord->dateOp=date("Y-m-d  H:i:s");        
         $schetStatusRecord->refContact =    $ref_contact ;
         $schetStatusRecord->refManager = $curUser->id;
         $schetStatusRecord->refEvent   = $calendarRecordId;
         /*Временное решение*/
         switch ($i){
          case 0:
              $schetStatusRecord->refStatusVal = $schetRecord->docStatus;
              $schetStatusRecord->refStatusGrp = 1; 
          break;
          case 1:
              $schetStatusRecord->refStatusVal = $schetRecord->cashState;
              $schetStatusRecord->refStatusGrp = 2;
          break;
          case 2:
              $schetStatusRecord->refStatusVal = $schetRecord->cashState;
              $schetStatusRecord->refStatusGrp = 3;
          break;
         }
         /**/
         
         $schetStatusRecord->save();
      }  

        $schetRecord -> save();    
     
   
     
     return 1;     
   }
      
  public function getWareInSchetProvider($params)
   {

    $query  = new Query();
    $query->select ([
            'id',
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',
            'dopRequest',
            'dostavka',
            'wareNameRef'
                ])
           ->from("{{%schetContent}}")
           ->distinct();

    $countquery  = new Query();
    $countquery->select ("count({{%schetContent}}.id)")
           ->from("{{%schetContent}}")
           ->distinct();

     $query->andWhere(['=', 'refSchet',$this->id]);
     $countquery->andWhere(['=', 'refSchet',$this->id]);     

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
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',
            'dopRequest',
            'dostavka',
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
/************/   
  public function getClientSchetProvider($params)
   {

    $query  = new Query();
    $query->select ([
            'id',
            'orgINN',
            'orgKPP',
            'orgTitle',
            'schetRef1C',
            'schetDate',
                ])
           ->from("{{%client_schet_header}}")
           ->distinct();

    $countquery  = new Query();
    $countquery->select ("count({{%client_schet_header}}.id)")
           ->from("{{%client_schet_header}}")
           ->distinct();

     $query->andWhere(['=', 'orgINN', $this->id]);
     $countquery->andWhere(['=', 'orgINN', $this->id]);     

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
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',
            'dopRequest',
            'dostavka',
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   


/*********/
public $sumTransport=0;
   public function getSchetTransportProvider($params)
   {

   $this->sumTransport=Yii::$app->db->createCommand(
            'SELECT sum(val) from {{%schetTransport}} where refSchet=:refSchet',
            [':refSchet' => $this->id])->queryScalar();
if(empty($this->sumTransport))$this->sumTransport = 0;

    $query  = new Query();
    $query->select ([
            'a.id',
            'a.refSchet',
            'a.typeText',
            'a.type',
            'a.route',
            'a.note',
            'a.price',
            'a.weight',
            'a.val',
            ])
            ->from("{{%schetTransport}} as a")
            ->distinct();


    $countquery  = new Query();
    $countquery->select ("count(a.id)")
            ->from("{{%schetTransport}} as a")
            ;

     if (($this->load($params) && $this->validate())) {
     }


            $query->andFilterWhere(['=', 'refSchet', $this->id]);
         $countquery->andFilterWhere(['=', 'refSchet', $this->id]);


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
            'type',
            'route',
            'note',
            'price',
            'weight',
            'val',
            ],
            'defaultOrder' => [	'id'=> SORT_DESC],
            ],
        ]);

    return  $dataProvider;
   }

   /** end of object **/    
   
 }
