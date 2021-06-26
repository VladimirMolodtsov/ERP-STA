<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\ScladList;
use app\models\OtvesList;
use app\models\ZakazContent;
use app\models\RequestSupplyList;
use app\models\PurchaseZakaz;
use app\models\SupplyStatusList;  
use app\models\SupplyScenarioList;
use app\models\TblRequestLnk;
/**
 * MarketViewForm  - модель 
 */

class SupplyForm extends Model
{

    public $id=0;
    public $execNum="";
    public $isAcceptInWork=0;    
    public $isAcceptInDeliver=0;    
    public $isReject = 0;
    public $isFinished = 0;
    public $viewMode="";    
    public $supplyNote ="";
    public $finishDate ="";
    public $supplyDate =""; // предполагаемая дата
    public $execWeight = 0;
    public $execValue = 0;
    
    public $refSchet=0;
    public $debug=array();
    public $extSupplyData = array();
    public $detailList = array();

    public $inDeliverList;  
    public $scenario;

            /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 

    public $listSupplyType = [ 0 => 'Самовывоз',
                        1 => 'Доставка клиенту',
                        2 => 'Передать транспортной компании',
                        ];

                        

    
    public function rules()
    {
        return [
            [['id','isFinished', 'isAcceptInDeliver', 'execValue','execWeight','scenario',
              'supplyDate','finishDate', 'isReject', 'isAcceptInWork', 'execNum', 'viewMode', 'supplyNote' ,
              'recordId', 'dataType', 'dataVal', 'dataId',
              ], 'default'],
            ['id', 'integer'],
            ['isAcceptInWork', 'integer'],
            ['isAcceptInDeliver', 'integer'],
            ['isFinished', 'integer'],
            ['isReject', 'integer'],
            /*['execWeight', 'float'],
            ['execValue', 'float'],*/
        ];
    }

    
    
    
    
  public function savelnkDocRequest()    
  {
           $ret = [ 'res' => false, 
             'dataVal'  => $this->dataVal,
             'dataId'  => $this->dataId,  
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
             'isReload' => false
           ];   

      switch ($this->dataType)
      {
        case 'lnkDocRequest':
        $record = TblRequestLnk::findOne([
        'refRequest' => intval($this->recordId),
        'refDoc'     => intval($this->dataId),
        ]);
        if (!empty($record)){
            $ret['res'] = true;
            $ret['isReload'] = true;
            return $ret;
        }
        $record = new TblRequestLnk();
        $record->refRequest = intval($this->recordId);
        $record->refDoc     = intval($this->dataId);
        $record->lnkRole    = 1;
        $strSql = "SELECT sum(docSum) FROM {{%documents}} where {{%documents}}.id = :refDoc";
        $record->lnkSum     = Yii::$app->db->createCommand($strSql, [':refDoc' => intval($this->dataId)])->queryScalar();        ;
        $record->save();
        $ret['res'] = true;
        $ret['isReload'] = true;
        $ret['val'] = $record->id;
        break;
        
        case 'lnkRemove':
        $record = TblRequestLnk::findOne( intval($this->recordId) );
        if (empty($record)){
            return $ret;
        }
        $record->delete();
        $ret['res'] = true;
        $ret['isReload'] = true;
        break;

        case 'lnkSum':
        $record = TblRequestLnk::findOne( intval($this->recordId) );
        if (empty($record)){
            return $ret;
        }
        $lnkSum = (float)str_replace(',', '.',$this->dataVal); 
        $record->lnkSum = $lnkSum;          
        $record->save();
        $ret['res'] = true;
        $ret['isReload'] = false;
        break;
        
      }
  
      return $ret;
  
  }
    
  public function setViewed()
  {
    $supplyRecord= RequestSupplyList::findOne($this->id);      
    if (empty($supplyRecord)) return;
    $curUser=Yii::$app->user->identity;
    
    if (empty($supplyRecord->viewManagerRef)) 
    {
        $supplyRecord->execView = date('Y-m-d h:i');
        $supplyRecord->viewManagerRef =$curUser->id;        
        $supplyRecord-> save();
    }
  }   
  
  public function acceptSupplyRequest($id)
  {
      $supplyRecord= RequestSupplyList::findOne($id);            
      if (empty($supplyRecord)) return false;
      $supplyRecord->isAccepted = 1;
      $supplyRecord->supplyState = 0x00001;
      $supplyRecord->save();
    return true;
  }
  
  public function unAcceptSupplyRequest($id)
  {
      $supplyRecord= RequestSupplyList::findOne($id);            
      if (empty($supplyRecord)) return false;
      $supplyRecord->isAccepted = 0;
      $supplyRecord->save();
    return true;
  }

  public function rejectSupplyRequest($id)
  {
      $supplyRecord= RequestSupplyList::findOne($id);            
      if (empty($supplyRecord)) return false;
      $supplyRecord->isAccepted = -1;
      $supplyRecord->supplyState = 0x00004;
      $supplyRecord->save();
    return true;
  }
    
  public function loadSupplyData()    
  {
      
      $supplyRecord= RequestSupplyList::findOne($this->id);      
      if (empty($supplyRecord)) return;
      
      
      if ($supplyRecord->supplyState & 0x00001) $this->isAcceptInWork=1;                                        
      $this->execNum = $supplyRecord ->execNum;       
      $this->supplyNote = $supplyRecord ->supplyNote;
      if(!empty($supplyRecord ->supplyDate) ) $this->supplyDate = date ("d.m.Y", strtotime($supplyRecord ->supplyDate));
      if(!empty($supplyRecord ->finishDate) ) $this->finishDate = date ("d.m.Y", strtotime($supplyRecord ->finishDate));
      $this->execWeight = $supplyRecord ->execWeight;
      $this->execValue  = $supplyRecord ->execValue;
      if ($supplyRecord->supplyState & 0x00004) $this->isReject=1;      
      if ($supplyRecord->supplyState & 0x00002) $this->isAcceptInDeliver=1;
      
      $this->refSchet=$supplyRecord->refSchet;
      
    $query  = new Query();
    $this->extSupplyData = $query->select ([
    '{{%request_supply}}.id', 
    'refZakaz', 
    '{{%schet}}.schetNum', 
    '{{%schet}}.schetDate', 
    'summOplata', 
    'schetSumm', 
    '{{%schet}}.ref1C', 
    '{{%schet}}.refManager',
    '{{%user}}.userFIO', 
    'title', 
    'execNum as orgTitle',
    'view.userFIO as viewManager'
    ])
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%user}} as view','view.id = {{%request_supply}}.viewManagerRef')
            ->where(['=', '{{%request_supply}}.id', $this->id])
            ->one();


/*$list = Yii::$app->db->createCommand(
            'SELECT {{%schetContent}}.id, wareTitle,  wareEd, warePrice, wareCount, dopRequest
            FROM   {{%schetContent}}  where {{%schetContent}}.refSchet = :refSchet', 
        [':refSchet' => $this->id])->queryAll();        
*/
    
    
    
$strDeliver= "( 
            SELECT {{%request_deliver}}.refSchet, requestGoodTitle, SUM(requestCount) as sumCnt 
            from {{%request_deliver}}, {{%request_deliver_content}}
            where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef 
            AND {{%request_deliver}}.refSchet = ".$supplyRecord->refSchet." 
            GROUP BY {{%request_deliver}}.refSchet, requestGoodTitle ) as a 
            ";    

//wareSyncDate, 
/**
Связь по товару - плохо Vv
*/
    
    $this->detailList = Yii::$app->db->createCommand(
            "SELECT {{%schetContent}}.id, wareTitle,  wareEd, warePrice, wareCount,            
            dopRequest, dostavka,
            warehouseRef,
            amount as wareRemain,
            a.sumCnt 
            FROM   {{%schetContent}}   
            LEFT JOIN ".$strDeliver." on a.requestGoodTitle = {{%schetContent}}.wareTitle
            LEFT JOIN {{%warehouse}} on {{%warehouse}}.id   = {{%schetContent}}.warehouseRef
            where {{%schetContent}}.refSchet = :refSchet", 
           [':refSchet' => $supplyRecord->refSchet])->queryAll();        
   
    
 /*   $this->debug[]  = Yii::$app->db->createCommand(
            "SELECT {{%schetContent}}.id, wareTitle,  wareEd, warePrice, wareCount,            
            dopRequest, dostavka,
            warehouseRef,
            amount as wareRemain,
            a.sumCnt 
            FROM   {{%schetContent}}   
            LEFT JOIN ".$strDeliver." on a.requestGoodTitle = {{%schetContent}}.wareTitle
            LEFT JOIN {{%warehouse}} on {{%warehouse}}.id   = {{%schetContent}}.warehouseRef
            where {{%schetContent}}.refSchet = :refSchet", 
           [':refSchet' => $supplyRecord->refSchet])->getRawSql();  
*/           
               
               
    $cnt = count($this->detailList);
    for ($i=0; $i<$cnt; $i++ )
    {
      /*ищем по привязке к строке счета/заявки клиента*/  
      $purchZakazRecord = PurchaseZakaz::findOne(['refSchetContent' => $this->detailList[$i]['id']]);
      if (empty($purchZakazRecord)) 
      {
          $this->detailList[$i]['zaprosRef'] = 0;
          $this->detailList[$i]['zaprosDate'] = 0;          
          $this->detailList[$i]['zaprosActive'] = 0;
          $this->detailList[$i]['status']    = 0; 
          $this->detailList[$i]['purchaseRef'] = 0;
          $this->detailList[$i]['purchaseCreation'] = 0;          
          $this->detailList[$i]['purchaseisFinished'] = 0;
          $this->detailList[$i]['purchaseisReject'] = 0;          
     
          continue;
      }      
          $this->detailList[$i]['zaprosRef'] = $purchZakazRecord->id;
          $this->detailList[$i]['zaprosDate'] = $purchZakazRecord->zakazDate;              
          $this->detailList[$i]['zaprosActive'] = $purchZakazRecord->isActive;
          $this->detailList[$i]['status']    = $purchZakazRecord->status;

        /*Ищем закупку*/        
          
      $purchRecord = Purchase::findOne(['id' => $purchZakazRecord->purchaseRef]);     
      if (empty($purchRecord)) 
      {
          $this->detailList[$i]['purchaseRef'] = 0;
          $this->detailList[$i]['purchaseCreation'] = 0;          
          $this->detailList[$i]['purchaseisFinished'] = 0;
          $this->detailList[$i]['purchaseisReject'] = 0;          
          continue;
      }      
          
          $strSql= "SELECT COUNT({{%purchase_etap}}.id) FROM {{%purchase_etap}}  where purchaseRef = :purchaseRef ";    
          $pN=Yii::$app->db->createCommand($strSql, [':purchaseRef' => $purchRecord->id])->queryScalar();        
          if ($pN > 0) $this->detailList[$i]['purchaseisReject'] = 1;                    
          else          $this->detailList[$i]['purchaseisReject'] = 0;                    

          $this->detailList[$i]['purchaseRef'] = $purchRecord->id;
          $this->detailList[$i]['purchaseCreation'] = $purchRecord->dateCreation;          
          $this->detailList[$i]['purchaseisFinished'] = $purchRecord->isFinishedPurchase;

    }
  //  $this->debug[]  =   $this->detailList ;
    
    /* Что там у нас в доставке */
    $this->inDeliverList = Yii::$app->db->createCommand(
    'Select factWeight, factValue, request_exp_value,  sum(requestGoodValue*requestCount) as sumDeliver, {{%request_deliver}}.id as deliverId, creationDate as deliverDate, factDate
     from {{%request_deliver}}, {{%request_deliver_content}} where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef
     and {{%request_deliver}}.refSchet = :refSchet
     GROUP BY {{%request_deliver}}.id ORDER BY factDate
     ')    ->bindValue(':refSchet', $this->refSchet)->queryAll();
    
    if (count($this->inDeliverList ) > 0 ) $this->isAcceptInDeliver=1; //по любому что-то в доставке есть
    
    
    
    if(empty($supplyRecord ->finishDate) || $supplyRecord ->finishDate ='1970-01-01')
    {    
        for ($i=count($this->inDeliverList)-1; $i>=0; $i--)
        {
          if(!empty($this->inDeliverList[$i]['factDate']))
          {            
            $this->finishDate = date ("d.m.Y", strtotime($this->inDeliverList[$i]['factDate']));
            break;
          }
        }   
    }
        
        
    //     $this->debug[]  = $this->detailList;
     
      return $supplyRecord;      
  }
/********************************/    
public function setRequestScenario($requestId,$scenId )
{
    $statusRecord = SupplyStatusList::findOne([
     'refSupply' => $requestId, 
    ]);     
    if (empty ($statusRecord) ) return false;
      
    $statusRecord->refScenario = $scenId;
    if ($statusRecord->st1 == '0000-00-00') $statusRecord->st1 = date ('Y-m-d');
    $statusRecord-> save();
    return true;
       
    /*Для совместимости*/
    $strSql ="update  {{%request_supply}}, {{%supply_status}} set supplyIsAccept = 1
            where    supplyIsAccept = 0 AND  {{%request_supply}}.id = {{%supply_status}}.refSupply and {{%supply_status}}.st1 > '1970-01-01'";
    Yii::$app->db->createCommand($strSql)->execute();

}    

public function syncExecution($requestId)
{
/* Ищем запрос на поставку */
    $record = RequestSupplyList::findOne([
     'id' => $requestId, 
    ]);     
    if (empty ($record) ) return false;
    
    
 /*ищем текущий статус*/    
   $statusRecord = SupplyStatusList::findOne([
     'refSupply' => $requestId, 
    ]);     
 /*если нет статуса то и ничего сделать не можем */  
    if (empty ($statusRecord) ) return false;
    //if (strtotime($statusRecord->st11) > 100)
    
        $record->readyFact =  $statusRecord->st11;
        $record->save();
    
}

public function setRequestSupplyStatus ($requestId,$statusId,$val)
{

    $fld = "st".$statusId."";
    
    
    if ($val == 0 )$value = '0000-00-00';
            else  $value = date('Y-m-d', strtotime($val));
    
    Yii::$app->db->createCommand("UPDATE {{%supply_status}} SET $fld=:val where refSupply=:id",
    [
    ':val' =>$value,
    ':id'  =>$requestId, 
    ]
    )->execute();

    $this->syncExecution($requestId);

return true;    
}

/********************************/

public function getScenarioVariant ()
{
    $scenarioVar = Yii::$app->db->createCommand("Select id, scenarioTitle FROM {{%supply_scenario}} order by id")->queryAll();
    return ArrayHelper::map($scenarioVar, 'id', 'scenarioTitle');   
}

  public function getStatusTitles()      
  {
    $statusTitles = Yii::$app->db->createCommand("Select id, statusTitle FROM {{%supply_status_title}} order by id")->queryAll();
    return ArrayHelper::map($statusTitles, 'id', 'statusTitle');
  }
/********************************/
  public function getStatus()      
  {
    if (empty($this->id)) return false;
    
    $statusRecord = SupplyStatusList::findOne([
     'refSupply' => $this->id, 
    ]);        
    
    if (empty ($statusRecord) )
    {
     $statusRecord = new SupplyStatusList();   
     $statusRecord->refSupply = $this->id;
     $statusRecord->save();
    }

    if (empty($statusRecord->refScenario)) $refScenario = 1;
    else $refScenario = $statusRecord->refScenario;

    $this->scenario=$refScenario;       
   
   $statusList   = Yii::$app->db->createCommand("Select * FROM {{%supply_status}}   where refSupply =".$this->id )->queryAll();
   $scenarioList = Yii::$app->db->createCommand("Select * FROM {{%supply_scenario}} where id =".$refScenario )->queryAll();
   
    $statusArray= array();
    $statusTitles = $this->getStatusTitles();      
   
 for ($i=1; $i<=17; $i++ )
 {     
    $fld="st".$i;
    $fld_time=$fld."_time"; 
    
    $statusArray[$i]['title'] = $statusTitles[$i];
    $statusArray[$i]['value'] = $statusList[0][$fld];
    $statusArray[$i]['inUse'] = $scenarioList[0][$fld];
    if ($scenarioList[0][$fld] == 0) $statusArray[$i]['wait'] = 0;
    else                           $statusArray[$i]['wait'] = $scenarioList[0][$fld_time];
 }  

 
 
 
 return $statusArray;
  }

  public function getScenarioList  ()  
  {

    $statusArray = array();
    $scenarioTitles = array();
    $statusTitles = $this->getStatusTitles();    

    $scenario = Yii::$app->db->createCommand("Select * FROM {{%supply_scenario}} order by id")->queryAll();
    
    $n = count($scenario);
    
    for ($i=1; $i<=17; $i++ )
    {      
        $fld="st".$i;
        $fldT="st".$i."_time";
        $statusArray[$i]['name'] = $statusTitles[$i];
        
        for($j=0; $j<$n;$j++ )        
        {            
         $statusArray[$i][$j]['inUse'] = $scenario[$j][$fld];     
         $statusArray[$i][$j]['time']  = $scenario[$j][$fldT];     
        }
    }  
 
        for($j=0; $j<$n;$j++ )        
        {            
            $scenarioTitles[$j]['name']= $scenario[$j]['scenarioTitle'];
            $scenarioTitles[$j]['id']= $scenario[$j]['id'];
        }
 
     $res['scenarioTitles'] =$scenarioTitles;
     $res['statusArray'] =$statusArray;
      
    return $res; 
  }
/*******************************/    
  public function addScenario($name)
  {
    $record = new SupplyScenarioList();
    if (empty($record)) return false;
    $record ->scenarioTitle = $name;
    $record -> save();
    return true;
  }  
  
  public function setScenarioName($id,$name) 
  {
    $record = SupplyScenarioList::findOne($id);
    if (empty($record)) return false;
    $record ->scenarioTitle = $name;
    $record -> save();
    return true;
  }  
  
  public function setScenarioStatus($id,$etap,$status) 
  {
    if ($etap < 1) return false;
    if ($etap > 17) return false;

    $fld = "st".$etap;
    Yii::$app->db->createCommand("UPDATE {{%supply_scenario}} SET $fld=:val where id=:id",
    [
    ':val' =>$status,
    ':id'  =>$id, 
    ]
    )->execute();


    return true;
  }  

  public function setScenarioTime($id,$etap,$val)
  {
    if ($etap < 1) return false;
    if ($etap > 17) return false;

    $fld = "st".$etap."_time";
    Yii::$app->db->createCommand("UPDATE {{%supply_scenario}} SET $fld=:val where id=:id",
    [
    ':val' =>$val,
    ':id'  =>$id, 
    ]
    )->execute();


    return true;
  }  
  

 
/*******************************/  
  public function saveData()    
  {
     $flg=0;
 
    $supplyRecord= RequestSupplyList::findOne($this->id);      
    if (empty($supplyRecord)) return;
    
        $supplyRecord ->supplyNote = $this->supplyNote;          
       if (empty($this->execNum))$this->execNum = " ";
       $supplyRecord ->execNum = $this->execNum;       
       $supplyRecord ->finishDate = date('Y-m-d', strtotime($this->finishDate));    
       $supplyRecord ->supplyDate = date('Y-m-d', strtotime($this->supplyDate));           
       $supplyRecord ->execWeight = floatval($this->execWeight);
       $supplyRecord ->execValue = floatval($this->execValue);
    
     if ($this->isAcceptInWork==1)    $flg|= 0x00001;
     if ($this->isAcceptInDeliver==1) $flg|= 0x00002;
     if ($this->isReject==1)          $flg|= 0x00004;
     if ($this->isFinished == 1)    { $flg|= 0x00008; $supplyRecord -> isActive = 0; }    
     $supplyRecord->supplyState = $flg;
     $supplyRecord->save();
    
  }
  
  public function resyncRemain($zakazid)
  {
        $strSql= "UPDATE {{%zakazContent}}, {{%warehouse}} set 
        {{%zakazContent}}.wareRemain =  {{%warehouse}}.amount,
        {{%zakazContent}}.wareSyncDate = NOW()
        where 
        {{%zakazContent}}.warehouseRef =  {{%warehouse}}.id
        and {{%zakazContent}}.refZakaz = ".$zakazid;
        
        Yii::$app->db->createCommand($strSql)->execute();

  }  
  
  /*Получим список доставок связанных с отгрузкой*/
  public function getDeliversList()
  { 
      $inDeliverList = Yii::$app->db->createCommand(
    'Select sum(requestGoodValue*requestCount) as sumDeliver, {{%request_deliver}}.id as deliverId, creationDate as deliverDate
     from {{%request_deliver}}, {{%request_deliver_content}} where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef
     and {{%request_deliver}}.refSchet = :refSchet
     GROUP BY {{%request_deliver}}.id
     ')    ->bindValue(':refSchet', $this->refSchet)->queryAll();
     
     
     $val ="<p>Перечень доставок:</p> <ul>";
     if (empty ($inDeliverList) ) return "<nobr>Нет доставок</nobr>";
                    
     $cnt = count($inDeliverList); 
     $sum =0;                    
     for ($i=0; $i<$cnt; $i++ )
     {
       $val .="<li><a href='#' onclick='javascript:openWin(\"store/deliver-zakaz&id=".$inDeliverList[$i]['deliverId']."\", \"deliverWin\");'>
        № ".$inDeliverList[$i]['deliverId']." на сумму: ".number_format($inDeliverList[$i]['sumDeliver'], 2, '.', '&nbsp;')."</a><br>";                            
       $sum+=$inDeliverList[$i]['sumDeliver'];
     }
   return $val."</ul>";
}
  
  public function printRequestSupply()        
   {
    $supplyRecord= $this->loadSupplyData();    
         
     $blank ="<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
    /* $blank .="
    <style> 
        table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 14pt;
        }
    </style>";*/
     $blank.="<div style='align:center;width:800px'><h2>Заявка на ОТГРУЗКУ №".$supplyRecord ->id." от ".date ("d.m.Y", strtotime($supplyRecord ->requestDate) )."</h2>\n";    
     $blank.="<p align='right'> от ".$this->extSupplyData['userFIO']."</p>\n";
     $blank.="<p> Клиент".$this->extSupplyData['title']."</p>\n";
     $blank.="<table border='0' style='border: 0px' width='80%'>\n";
     $blank.="<tr><td>Счёт № ".$this->extSupplyData['schetNum']." от  ".date ("d.m.Y", strtotime($this->extSupplyData['schetDate']) )."</td>"; 
     $blank.="<td>Сумма счета:". $this->extSupplyData['schetSumm']."</td> <td>Оплачено: ".$this->extSupplyData['summOplata']."</td></tr>\n";
     $blank.="</tr> </table>\n";
     $blank.="<hr>\n";    

     $blank.="<table border='1' style='border-collapse: collapse;' width='800px'>";
     $blank.="<tr><td style='padding:3px'>Наименование</td> <td style='padding:3px'>К-во </td> <td style='padding:3px'>ед.изм </td></tr>\n";
    for ($i=0; $i<count($this->detailList);$i++ )
    {
        $blank.="<tr>\n";
        $blank.="<td style=padding:3px'> ".$this->detailList[$i]['wareTitle']."</td>\n"; 
        $blank.="<td style=padding:3px'>".$this->detailList[$i]['wareCount']."</td>\n";
        $blank.="<td style=padding:3px'>".$this->detailList[$i]['wareEd']."</td>\n";
        $blank.="</tr>\n";
    }
      $blank.=" </table>  <hr>"; 
     
     $blank.="<p>Дата отгрузки: <b>".$supplyRecord->supplyDate."</b></p>\n";
     $blank.="<p>Доставка: <b>".$this->listSupplyType[$supplyRecord->supplyType]."</b> ".$supplyRecord->dstNote."</p>\n";
     $blank.="<table width=100% border='0'><tr>";
     $blank.="<td style='padding:5px'>Контактный телефон: <b>".$supplyRecord->contactPhone."</b> E-mail: <b>".$supplyRecord->contactEmail."</b>  </td> ";
     $blank.="<td style='padding:5px'>Контактное лицо: <b>".$supplyRecord->contactFIO."</b></td> </tr></table>\n";
       $blank.="<p>Адрес: <u>".$supplyRecord->adress."</u></p>\n";
     $blank.="<hr>\n";
     $blank.="<p>Дополнения: <b>".$supplyRecord->requestNote."</b></p>\n";        
    
     $blank.="</div></body></html>"; 
               
    return $blank;
   }    

   /*****************************/

   
   
public function getTransportDocProvider($params)
   {
    $query  = new Query();
    $query->select ([ 
                      'a.id',  
                      'a.docIntNum',
                      'a.docOrigNum',
                      'a.docOrigDate',
                      'b.lnkSum',  
                      'b.id as lnkRef',        
                      'a.refOrg',             
                      '{{%orglist}}.title as orgTitle'
                      ])
            ->from("{{%documents}} as a")                                    
            ->leftJoin("{{%request_lnk}} as b","b.refDoc= a.id")            
            ->leftJoin("{{%orglist}}","a.refOrg= {{%orglist}}.id")                                                                                  
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT(a.id) )")
            ->from("{{%documents}} as a")                                    
            ->leftJoin("{{%request_lnk}} as b","b.refDoc= a.id")            
            ->leftJoin("{{%orglist}}","a.refOrg= {{%orglist}}.id")                                                                                  
            ;

//Только счета ТК                                  
          $query->andWhere(['=', 'b.lnkRole', 1]);
     $countquery->andWhere(['=', 'b.lnkRole', 1]);
            
          $query->andWhere(['=', 'b.refRequest', $this->id]);
     $countquery->andWhere(['=', 'b.refRequest', $this->id]);
            
    if (($this->load($params) && $this->validate())) {
     }
     
     
    
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 30,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'docOrigNum',
                      'docOrigDate',
                      'lnkSum',
                      'orgTitle',
             ],            
            'defaultOrder' => [ 'docOrigDate'  => 'SORT_ASC'],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

      

  /**/  
 }
 
