<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use app\models\OrgList;
use app\models\UserList;
use app\models\DeliverList;
use app\models\DeliverContentList;
use app\models\RequestSupplyList;
use app\models\WarehouseForm;
use app\models\ReestrOplat;
use app\models\ConfigTable;
/**
 * MarketViewForm  - модель 
 */


class DeliversForm extends Model
{
    public $id="";
    public $schetId="";
    
    public $requestNum="";
       
    public $requestDatePlanned="";
    public $requestAdress="";
    public $requestPhone="";
    public $requestContact="";
    public $requestNote="";
    public $requestSclad="";
    public $requestScladAdress="";
    public $requestExecutor="";
    public $refOrg="";
    public $refFromOrg="";
    public $requestStatus="";
    public $requestSupplyId=0;
    public $scladRef="";
    public $requestUPD="";
    public $requestTime=0;
    public $requestExpValue=0;
    public $requestExecutorType = 1;
    
    
    
    public $requestCashReal =0;
    public $requestRealSize =0;
    public $requestDateReal ="";
    public $supplyType=0;
    
    /*не редактируемые*/
    public $orgTitle="";
    public $orgFromTitle="";
    public $orgINN="";
    public $orgAdress="";
    public $orgFromAdress="";
    public $sladTitle="";
    public $statusList=array();
    public $statusListJS="";
        
    public $transportName="";
    public $consignee   ="";
    public $payer ="";
    public $isToTerminal ="";

    
    public $title="";
    public $userFIO="";
    public $goodTitle="";
    public $grpGood="";
    public $grpGoodList ="";

    public $setSort="id";
    
    public $detail=0;
    public $type="";
    
    public $requestCategory="";
    public $requestPlaces=0;
    public $requestVolume=0;
    public $requestTotalWeight=0;

    public $factDate;
    public $factWeight=0;
    public $factValue=0;

    public $refPurchase =0;
/*Расчетное*/    
    public $itogoWeight=0;
    public $itogoValue=0;
    public $itogoTime=0;
    
    public $valWeight=0;
    public $valTime=0;
    
    public $expWrkItog =0;
    public $expCostItog =0;
    public $driveItog =0;

    
    public $y_from = 0;
    public $m_from = 0;
    public $y_to = 0;
    public $m_to = 0;

    public $dTo = 0;
    public $dFrom = 0;

    
    public $isActive = 2;
    public $requestDate ="";
    public $creationDate="";
    public $requestDateFact="";

   public $command;    
   public $count=0;
   public $intSort = 0;
   
   public $refOplate =0; 
   public $refOplateDrive=0; 
   public $refOplateExpCost=0; 
   public $refOplateWrkExp=0; 

   public $deliverContentList;
   
   public $debug;    
    
    
   
    public function rules()
    {
        return [
            [['id','schetId','requestNum','requestDatePlanned','requestAdress','requestPhone','requestContact',
            'requestNote','requestSclad','requestExecutor','refOrg', 'requestStatus', 'requestScladAdress', 'scladRef',
            'requestCashReal', 'requestRealSize','requestDateReal', 'requestTime', 'requestUPD', 'requestExpValue',
            'requestCategory', 'requestPlaces', 'requestVolume', 'requestTotalWeight', 'factDate', 'refFromOrg',
            'factWeight','factValue', 'supplyType', 'requestExecutorType' ], 'default'],
            ['id', 'integer'],
            ['refOrg', 'integer'],            
            ['refFromOrg', 'integer'],            
            ['schetId', 'integer'],
            ['scladRef', 'integer'],
         //   ['supplyType', 'integer'],            
            ['requestTime', 'integer'],            
             ['requestExpValue', 'double'],
            ['requestRealSize', 'double'],
            ['requestCashReal', 'double'],
            
            ['requestStatus', 'integer'],            
            [['title', 'userFIO', 'goodTitle', 'grpGood', 'sladTitle', 'grpGoodList', 'isActive', 'requestDate', 'creationDate',
            'requestDateFact', 'refOplate', 'refOplateDrive', 'refOplateExpCost', 'refOplateWrkExp', 'requestScladAdress'], 'safe'],
            
            //[ ['requestAdress', 'requestScladAdress', ], 'required'],
        ];
    }

  public function addGoodInRequest($deliverRef,$requestGoodTitle, $requestCount,$requestMeasure,$requestGoodRef )
  {
    $deliverContentRecord= new DeliverContentList();      
    
    $deliverContentRecord ->requestDeliverRef = $deliverRef;
    $deliverContentRecord ->requestGoodTitle = $requestGoodTitle;
    $deliverContentRecord ->requestCount = $requestCount;
    $deliverContentRecord ->requestMeasure = $requestMeasure; 
    $deliverContentRecord ->requestGoodRef = $requestGoodRef;
    $deliverContentRecord->save();
      
  }
  
  public function  setDeliverStatus($id, $status)
  {
      $deliverRecord= DeliverList::findOne($id);    
    if (empty($deliverRecord)) return false;
    $deliverRecord -> requestStatus = $status;
    $deliverRecord -> save();
    
    $statusRecord = DeliverStatusList::findOne([
    'refRequestDeliver' => $id,
    'status' =>$status
    ]);
    if (empty($statusRecord))
    {
        $statusRecord = new DeliverStatusList();
        if (empty($statusRecord)) return false;
        $statusRecord->refRequestDeliver = $id;
        $statusRecord->status = $status;
    }
    $statusRecord->statusChange = date('Y-m-d H:i');
    $statusRecord->save();
    return true;
  }

    
  public function  deleteDeliver($id)
  {
       Yii::$app->db->createCommand(
            'DELETE FROM {{%request_deliver_content}} where requestDeliverRef=:id', 
            [':id' => $id])->execute();

       Yii::$app->db->createCommand(
            'DELETE FROM {{%request_deliver}} where id=:id', 
            [':id' => $id])->execute();
      
  }
  
  public function  delGoodFromRequest($goodId)
  {
      $deliverContentRecord= DeliverContentList::findOne($goodId);    
      if (empty($deliverContentRecord)) return false;
      $deliverContentRecord->delete();
      return true;
  }
    
  public function  setRequestGood ($goodId, $proposal)
  {
      $deliverContentRecord= DeliverContentList::findOne($goodId);    
      if (empty($deliverContentRecord)) return false;
      $deliverContentRecord->requestGoodTitle = $proposal;
      $deliverContentRecord->save();
      return true;
  }

  public function  setRequestCount ($goodId, $proposal)
  {
      $deliverContentRecord= DeliverContentList::findOne($goodId);    
      if (empty($deliverContentRecord)) return false;
      $deliverContentRecord->requestCount = $proposal;
      $deliverContentRecord->save();
      return true;
  }

  public function  setRequestMeasure ($goodId, $proposal)
  {
      $deliverContentRecord= DeliverContentList::findOne($goodId);    
      if (empty($deliverContentRecord)) return false;
      $deliverContentRecord->requestMeasure = $proposal;
      $deliverContentRecord->save();
      return true;
  }

  /*
  ALTER TABLE `rik_request_deliver` ADD COLUMN `refPurchase` BIGINT DEFAULT 0 COMMENT 'ссылка на закупку';
  ALTER TABLE `rik_request_deliver` ADD COLUMN `refRequestSupply` BIGINT DEFAULT 0;
  */
  public function createNewDeliver()
  {
  
    $curUser=Yii::$app->user->identity;   
    $deliverRecord= new DeliverList();    
    $deliverRecord->creationDate = date ("Y-m-d H:i:s");
    $deliverRecord ->isActive=1;        
    $deliverRecord ->refUser=$curUser->id;       
    
    /* Указано из какого задания на отгрузку сделано*/     
    if(!empty($this->requestSupplyId))
    {
        
        $requestSupplyRecord= RequestSupplyList::findOne($this->requestSupplyId);
        if(!empty($requestSupplyRecord)) 
        {
        $deliverRecord->requestPhone= $requestSupplyRecord->contactPhone;
        $deliverRecord->requestContact = $requestSupplyRecord->contactFIO;            
        $deliverRecord ->refRequestSupply= $requestSupplyRecord->id;   
        if ($deliverRecord->supplyType != 3)
            $deliverRecord->supplyType = intval($requestSupplyRecord->supplyType);
        }
     }        
    $deliverRecord ->save();

    /*Стартовый статус*/
    $statusRecord = new DeliverStatusList();
        if (empty($statusRecord)) return false;         
    $statusRecord->refRequestDeliver = $deliverRecord ->id;
    $statusRecord->status = 0;
    $statusRecord->statusChange = date('Y-m-d H:i');
    $statusRecord->save();

      
    if (empty($this->type) && $this->schetId  >0)
    {   
        // счет клиента
      $deliverRecord ->refOrg= Yii::$app->db->createCommand(
            'SELECT refOrg from {{%schet}} where id =:schetId',   [':schetId' => $this->schetId])->queryScalar();            

        $deliverRecord ->    refSchet=$this->schetId;        
        $deliverRecord ->supplyType = 1;
        $deliverRecord ->save();
    
      //получим id заказа
      $zakazId = Yii::$app->db->createCommand(
            'SELECT refZakaz from {{%schet}} where id =:schetId',   [':schetId' => $this->schetId])->queryScalar();
    
      //получим содержание счета   
      /*$zakazList= Yii::$app->db->createCommand(
            'SELECT id, warehouseRef, good, count, ed, value  from {{%zakazContent}} where refZakaz =:zakazId', 
            [':zakazId' => $zakazId])->queryAll();*/

       $schetList= Yii::$app->db->createCommand(
            'SELECT id, warehouseRef, wareTitle, wareCount, wareEd, warePrice  from {{%schetContent}} where refSchet =:refSchet', 
            [':refSchet' => $this->schetId])->queryAll();


      //получим уже отправленное в доставку
      $deliverSrcList= Yii::$app->db->createCommand(
            'SELECT requestGoodTitle, requestGoodRef, requestCount, requestGoodValue  from {{%request_deliver_content}}, {{%request_deliver}} 
            where {{%request_deliver_content}}.requestDeliverRef={{%request_deliver}}.id AND refSchet =:schetId', 
            [':schetId' => $this->schetId])->queryAll();
            
      //Переведем в ассоциативный массив
      $deliverList=array();
      for ($i=0; $i<count($schetList); $i++)
      {          
         $deliverList[$schetList[$i]['wareTitle']]= $schetList[$i]['wareCount'];
      }

      /*Не доставленное */
      for ($i=0; $i<count($deliverSrcList); $i++)
      {
          $key = $deliverSrcList[$i]['requestGoodTitle'];
          if (array_key_exists($key, $deliverList ))
          {
              $deliverList[$key]-=$deliverSrcList[$i]['requestCount'];
          }
      }
      
      for ($i=0; $i<count($schetList); $i++)
      {          
         $key = $schetList[$i]['wareTitle'];
         if ($deliverList[$key]<=0) continue;
         
         Yii::$app->db->createCommand(
            'INSERT INTO {{%request_deliver_content}} (requestGoodTitle, requestGoodRef, requestCount, requestMeasure, requestGoodValue, requestDeliverRef) 
            VALUES (:requestGoodTitle, :requestGoodRef, :requestCount, :requestMeasure, :requestGoodValue, :requestDeliverRef)', 
            [':requestGoodTitle' => $key,
            ':requestGoodRef'    => $schetList[$i]['warehouseRef'],
            ':requestCount'      => $deliverList[$key],
            ':requestMeasure'    => $schetList[$i]['wareEd'],
            ':requestGoodValue'  => $schetList[$i]['warePrice'],
            ':requestDeliverRef' => $deliverRecord ->id,            
            ])->execute();         
      }

    } // Это был счет клиента


    if ($this->type == 'supplier')
    {    

      $purchRecord= Purchase::FindOne($this->refPurchase);
      if (!empty ($purchRecord))
      {
        $deliverRecord ->refFromOrg  = $purchRecord->refOrg;
        $deliverRecord ->refSupplierSchet=$purchRecord->supplierShetRef;
      }
        $deliverRecord ->refPurchase = $this->refPurchase;    
        $deliverRecord ->reason = 2;
        $deliverRecord ->supplyType = 5;        
        $deliverRecord ->save();

      //получим содержание заказа    
      
      $contentList= Yii::$app->db->createCommand(
            'SELECT {{%supplier_schet_content}}.id, wareRef as warehouseRef, goodTitle as good, goodCount as count, goodEd as ed, goodSumm/goodCount as value  
            from {{%supplier_schet_content}}, {{%purch_schet_lnk}} 
            where {{%supplier_schet_content}}.schetRef ={{%purch_schet_lnk}}.schetRef AND {{%purch_schet_lnk}}.purchRef = :purchRef', 
            [':purchRef' => $this->refPurchase])->queryAll();

      //получим уже отправленное в доставку
      $deliverSrcList= Yii::$app->db->createCommand(
            'SELECT requestGoodTitle, requestGoodRef, requestCount, requestGoodValue  from {{%request_deliver_content}}, {{%request_deliver}} 
            where {{%request_deliver_content}}.requestDeliverRef={{%request_deliver}}.id AND {{%request_deliver}}.refPurchase =:purchRef', 
            [':purchRef' => $this->refPurchase])->queryAll();
            
      //Переведем в ассоциативный массив
      $deliverList=array();
      for ($i=0; $i<count($contentList); $i++)
      {          
         $deliverList[$contentList[$i]['good']]= $contentList[$i]['count'];
      }

      /*Не доставленное */
      for ($i=0; $i<count($deliverSrcList); $i++)
      {
          $key = $deliverSrcList[$i]['requestGoodTitle'];
          if (array_key_exists($key, $deliverList ))
          {
              $deliverList[$key]-=$deliverSrcList[$i]['requestCount'];
          }
      }
      
      for ($i=0; $i<count($contentList); $i++)
      {          
         $key = $contentList[$i]['good'];
         if ($deliverList[$key]<=0) continue;
         
         Yii::$app->db->createCommand(
            'INSERT INTO {{%request_deliver_content}} (requestGoodTitle, requestGoodRef, requestCount, requestMeasure, requestGoodValue, requestDeliverRef) 
            VALUES (:requestGoodTitle, :requestGoodRef, :requestCount, :requestMeasure, :requestGoodValue, :requestDeliverRef)', 
            [':requestGoodTitle' => $key,
            ':requestGoodRef'    => $contentList[$i]['warehouseRef'],
            ':requestCount'      => $deliverList[$key],
            ':requestMeasure'    => $contentList[$i]['ed'],
            ':requestGoodValue'  => $contentList[$i]['value'],
            ':requestDeliverRef' => $deliverRecord ->id,            
            ])->execute();         
      }
    } // Это был счет поставщика
      

    return $deliverRecord ->id;  
  }
/*********************/  
 public function setUPD($id, $upd)
  {
    $deliverRecord= DeliverList::findOne($id);    
    if (empty($deliverRecord)) return false;
    $deliverRecord->requestUPD = $upd;      
    $deliverRecord->save();
      return true;
  }
/*********************/  
  public function prepareDeliver()
  {
     
    $deliverRecord= DeliverList::findOne($this->id);    
    if (empty($deliverRecord)) return false;

    
    $supplyList= Yii::$app->db->createCommand(
            'SELECT COUNT(id) AS C ,MAX(refOrg) as ref from {{%supply}} 
            where supplyNum = :supplyNum AND refSchet=:refSchet', 
            [':supplyNum' => $deliverRecord->requestUPD,
             ':refSchet'  => $deliverRecord->refSchet        
            ])->queryAll();    
    
  /* print_r($supplyList);
   print_r($deliverRecord->requestUPD);
   print_r($deliverRecord->refSchet);*/
   
    if (empty ($deliverRecord->isRefSupply) || $deliverRecord->isRefSupply == -1)    
    {     
        if ($supplyList[0]['C']>0)
        {
        $deliverRecord->isRefSupply=1;        
        $deliverRecord->save();
        }

        if ($supplyList[0]['C']==0)
        {
        $deliverRecord->isRefSupply=-1;        
        $deliverRecord->save();            
        }
    }
    
   /*Если нет ссылки на требование на отгрузку в ERP 
     ищем по счетам
   */
   if(empty ($deliverRecord->refRequestSupply))  
   {
     //если это счет клиенту  
     if (!empty($deliverRecord->refSchet))
     {
      $requestSupplyRecord = RequestSupplyList::FindOne([ 'refSchet' => $deliverRecord->refSchet ]);         
      $deliverRecord->refRequestSupply = $requestSupplyRecord->id;   
      $deliverRecord->save();
     }
     /* Для закупки такого требования нет
     elseif (!empty($deliverRecord->refSupplierSchet))
     {
     
         
     }
     */
   }else
   {
       $requestSupplyRecord = RequestSupplyList::FindOne($deliverRecord->refRequestSupply);                
   }

   if (!empty ($requestSupplyRecord)) 
   {
     $this->transportName =    $requestSupplyRecord ->transportName;
     $this->consignee     =    $requestSupplyRecord ->consignee;
     $this->payer         =    $requestSupplyRecord ->payer;
     $this->isToTerminal  =    $requestSupplyRecord ->isToTerminal;
   }
    
    
    
   // echo $supplyList[0]['C']." ".$deliverRecord->isRefSupply;
    
    $this->id = $deliverRecord->id;
    $this->schetId = $deliverRecord->refSchet;    
    $this->supplyType = $deliverRecord->supplyType;
    if (empty($deliverRecord->requestNum)) {$this->requestNum =$deliverRecord->id;}
                              else         {$this->requestNum = $deliverRecord->requestNum;}
    
    if (empty ($deliverRecord->requestDatePlanned)) {$this->requestDatePlanned =date ('d.m.Y', time()+3*24*3600);}
    else $this->requestDatePlanned = date ('d.m.Y', strtotime($deliverRecord->requestDatePlanned));
        
    $this->requestAdress = $deliverRecord->requestAdress;
    $this->requestPhone = $deliverRecord->requestPhone;
    $this->requestContact = $deliverRecord->requestContact;
    $this->requestNote = $deliverRecord->requestNote;
    $this->requestSclad = $deliverRecord->requestSclad;
    $this->requestExecutor= $deliverRecord->requestExecutor;
    $this->requestStatus= $deliverRecord->requestStatus;
    $this->requestScladAdress = $deliverRecord->requestScladAdress ;    
    $this->requestExecutorType = $deliverRecord->requestExecutorType;    
    $this->requestCategory = $deliverRecord->requestCategory ;
    $this->requestPlaces = $deliverRecord->requestPlaces ;
    $this->requestVolume = $deliverRecord->requestVolume ;
    $this->requestTotalWeight = $deliverRecord->requestTotalWeight;    
    $this->requestCashReal =$deliverRecord->requestCashReal;
    $this->requestRealSize =$deliverRecord->requestRealSize;            
    $this->factDate   = date ('d.m.Y', strtotime($deliverRecord->factDate));
    $this->factWeight =$deliverRecord->factWeight;
    $this->factValue  =$deliverRecord->factValue;    
    $this->requestTime =$deliverRecord->request_time;
    $this->requestExpValue =$deliverRecord->request_exp_value;    
    $this->requestUPD =$deliverRecord->requestUPD;
   
    
    
    if (empty ($deliverRecord->requestDateReal)) {$this->requestDateReal =date ('d.m.Y');}    
    else $this->requestDateReal = date ('d.m.Y', strtotime($deliverRecord->requestDateReal));
    
    if (!empty($deliverRecord->refUser))
    $userRecord = UserList::findOne($deliverRecord->refUser);        
    if (!empty($userRecord)) $this->userFIO = $userRecord->userFIO;
    else $this->userFIO ="";
    
    if (!empty($deliverRecord->refOrg)){$orgRecord = OrgList::findOne($deliverRecord->refOrg);    }
    else
    {
      if ($supplyList[0]['C']>0)
      {
        $orgRecord = OrgList::findOne($supplyList[0]['ref']);    
      }  
        
    }
    
    $orgRecord = OrgList::findOne($deliverRecord->refOrg);    
    if (!empty($orgRecord))
    {
        $this->refOrg = $deliverRecord->refOrg;
        if(!empty($orgRecord->shortTitle)) $this->orgTitle = $orgRecord->shortTitle;
                                     else  $this->orgTitle = $orgRecord->title;
        $this->orgINN   = $orgRecord -> schetINN;
        
        $adressList = Yii::$app->db->createCommand(
            'SELECT id, [[index]], area, city, district, adress, isOfficial from {{%adreslist}} where ref_org =:refOrg and isBad=0 order by isOfficial DESC', 
            [':refOrg' => $orgRecord->id])->queryAll();
        
        if (count ($adressList)>0)
        {
        $this->orgAdress=    "Индекс:".$adressList[0]["index"]." ";        
        $this->orgAdress .=  "Область:".$adressList[0]["area"]." ";
        $this->orgAdress .=  "Город:".$adressList[0]["city"]." ";    
        $this->orgAdress .=  "Адрес:".$adressList[0]["adress"];        
        }
    }

    $orgFromRecord = OrgList::findOne($deliverRecord->refFromOrg);    
    if (!empty($orgFromRecord))
    {
        $this->refFromOrg = $deliverRecord->refFromOrg;
        //$this->orgFromTitle = $orgFromRecord -> title;
        if(!empty($orgFromRecord->shortTitle)) $this->orgFromTitle = $orgFromRecord->shortTitle;
                                         else  $this->orgFromTitle = $orgFromRecord->title;

        
        $adressList = Yii::$app->db->createCommand(
            'SELECT id, [[index]], area, city, district, adress, isOfficial from {{%adreslist}} where ref_org =:refOrg and isBad=0 order by isOfficial DESC', 
            [':refOrg' => $orgFromRecord->id])->queryAll();
        
        if (count ($adressList)>0)
        {
        $this->orgFromAdress=    "Индекс:".$adressList[0]["index"]." ";        
        $this->orgFromAdress .=  "Область:".$adressList[0]["area"]." ";
        $this->orgFromAdress .=  "Город:".$adressList[0]["city"]." ";    
        $this->orgFromAdress .=  "Адрес:".$adressList[0]["adress"];        
        }


    }

     $statusList = Yii::$app->db->createCommand(
            'SELECT status, statusChange from {{%request_deliver_status}} where refRequestDeliver =:refRequestDeliver 
            order by status ASC', 
            [':refRequestDeliver' => $this->id])->queryAll();
 
    $cnt =count($statusList); 
    $this->statusListJS = "var statusCngList=new Array();\n";   
    
    for ($i=0; $i<$cnt; $i++ )
    {
        $ind=$statusList[$i]['status'];
        $this->statusList[$ind]= date("d.m.Y h:i", strtotime($statusList[$i]['statusChange'])) ;        
        
    }
    
    for ($i=0; $i<8; $i++ )
    {
        if(empty($this->statusList[$i]))
        {
            $this->statusListJS.=" statusCngList[".$i."]= ' ';\n";    
          continue;
        }
        $this->statusListJS.=" statusCngList[".$i."]= '".$this->statusList[$i]."';\n";                   
    }
        
        
        
    $this->deliverContentList= Yii::$app->db->createCommand(
            'SELECT requestGoodTitle, requestGoodRef, requestCount, requestGoodValue,requestMeasure  from {{%request_deliver_content}} 
            where {{%request_deliver_content}}.requestDeliverRef=:requestDeliverRef', 
            [':requestDeliverRef' => $deliverRecord->id])->queryAll();        
    return $deliverRecord;        
  }

 
/****************************************************************************************/
 /**
 * Get array with our sclad list 
 * @param 
 * @return array with sladTitle
 * @throws 
 */       
  
  public function  getScladList()
  {
  

   $srcList = Yii::$app->db->createCommand(
            'SELECT DISTINCT requestScladAdress  from {{%request_deliver}}
            where DATEDIFF(NOW(), requestDatePlanned) < 90
            ')->queryAll();          
   $N = count ($srcList);
   $list=[];
   for ($i=0;$i < $N; $i++)
   {
       $k=$srcList[$i]['requestScladAdress']; 
       $list[$k]=$k;   
   }      
   return $list;   
  }  
    
  
 /****************************************************************************************/
 /**
 * Output TTN 
 * @param $this->id must be set
 * @return HTML code for TTN
 * @throws 
 */       
public function printReestrTTN ($deliversListData)
{

 echo "     
    <h4>Реестр ТТН</h4>

    <table border='1' width='100%'>

<tr>
	<td><b>№</b></td>
	<td><b>Со склада</b></td>
	<td><b>Адрес склада</b></td>
	<td><b>Дата</b></td>
  	<td><b>№ УПД</b></td>
    <td><b>Получатель</b></td>
    <td><b>Обьем</b></td>
</tr>	
";

$N= count($deliversListData);
$sum=0;
for ($i=0;$i<$N;$i++)
{
echo "
<tr>
	<td>".($i+1)."</td>
	<td>".$deliversListData[$i]['requestSclad']."</td>
	<td>".$deliversListData[$i]['requestScladAdress']."</td>
  	<td>".date("d.m.Y", strtotime($deliversListData[$i]['requestDateReal']))."</td>
    <td>".$deliversListData[$i]['requestUPD']."</td>
    <td>".$deliversListData[$i]['title']."</td>
    <td>".$deliversListData[$i]['sumCount']."</td>    
</tr>	
";
$sum+=$deliversListData[$i]['sumCount'];
}
echo "
</table>    

<div  style='text-align:right;'>
<b> Итого </b> &nbsp;&nbsp; ". number_format($sum,2,'.','&nbsp;')."
</div>
<hr noshade style='height:3px;border:none;color:#333;background-color:Black;'>  
";        
}
 
    
 /****************************************************************************************/
 /**
 * Output TTN 
 * @param $this->id must be set
 * @return HTML code for TTN
 * @throws 
 */       
public function printSingleTTN ($deliverRecord)
{


 echo "     
<div style='font-size:15pt; font-weight:bold'>Расходная накладная №  ".$deliverRecord->requestUPD." от ".date("d.m.Y", strtotime($deliverRecord->requestDateReal))." </div>
<hr noshade style='height:3px;border:none;color:#333;background-color:Black;'>  
<table width=100% border=0>
<tr>
    <td width='200px'>Поставщик</td>
    <td><b>".$this->orgFromTitle."</b></td>
<tr>

<tr>
    <td width='200px'>Покупатель</td>
    <td><b>".$this->orgTitle."</b></td>
<tr>

</table>
<br>


<table border='1' width='100%'>

<tr>
	<td><b>№:</b></td>
	<td><b>Артикул:</b></td>
	<td><b>Товар:</b></td>
  	<td><b>Количество:</b></td>
    <td><b>Ед. изм.</b></td>
</tr>	
";

$N= count($this->deliverContentList);
$sum=0;
for ($i=0;$i<$N;$i++)
{
echo "
<tr>
	<td>".($i+1)."</td>
	<td></td>
  	<td width='75%'>".$this->deliverContentList[$i]['requestGoodTitle']."</td>
    <td>".$this->deliverContentList[$i]['requestCount']."</td>
    <td>".$this->deliverContentList[$i]['requestMeasure']."</td>
    
</tr>	
";
$sum+=$this->deliverContentList[$i]['requestCount'];
}
echo "
</table>    

<div  style='text-align:right;'>
<b> Итого </b> &nbsp;&nbsp; ". number_format($sum,2,'.','&nbsp;')."
</div>
<hr noshade style='height:3px;border:none;color:#333;background-color:Black;'>  
<table border='0' width='75%'>
<tr>
	<td width='5%' style='padding:10px;'>Отпустил</td>
	<td width='45%' style='padding:10px;'><div style='height:20px'></div><hr noshade style='height:2px;border:none;color:#333;background-color:Black;'>  </td>
	<td width='5%' style='padding:10px;'>Получил</td>
  	<td width='45%' style='padding:10px;'><div style='height:20px'></div><hr noshade style='height:2px;border:none;color:#333;background-color:Black;'>  </td>
</tr>	
</table>      
";        
}
 
/****************************************************************************************/
 /**
 * Output two copies of TTN 
 * @param init by $this->prepareDeliver()
 * @return HTML code for TTN
 * @throws 
 */      
public function printTTNPage ()
{

$deliverRecord = $this-> prepareDeliver();
    $this->printSingleTTN($deliverRecord);
    $this->printTTNSpacer();
    $this->printSingleTTN($deliverRecord);

}        

/****************************************************************************************/
 /**
 * Output spacer between two copies of TTN
 * @param init by $this->prepareDeliver()
 * @return HTML code for TTN
 * @throws 
 */      
public function printTTNSpacer()
{

echo "<div style='height:100px;'></div>";
}        


/****************************************************************************************/
 /**
 * Output spacer between two copies of TTN
 * @param init by $this->prepareDeliver()
 * @return HTML code for TTN
 * @throws 
 */      
public function printAllTTN ($deliversListData)
{

 echo "
<style> 
 @media print {
    .pbreak {
     page-break-after: always;
    } 
   } 

   table {   
   border-collapse: collapse;   
   }
   td {
   padding:2px;
   }
</style>
";

    $this->printReestrTTN($deliversListData);
    echo "<div class='pbreak'></div>";
    for ($i=0;$i<count($deliversListData);$i++)
    {
        $this->id= $deliversListData[$i]['id'];
        $this->printTTNPage ();
        echo "<div class='pbreak'></div>";
    }
}
  
/*************************************************************/  
  public function saveFinalizeData    ()
  {
      $deliverRecord= DeliverList::findOne($this->id);    
    if (empty($deliverRecord)) return false;
    
    $deliverRecord->requestCashReal = $this->requestCashReal;
    $deliverRecord->requestRealSize = $this->requestRealSize;
    $deliverRecord->requestDateReal = date ('Y-m-d', strtotime($this->requestDateReal));
    $deliverRecord->requestNote = $this->requestNote ;
    $deliverRecord->requestStatus= 6;    
    $deliverRecord->save();
    
    $statusRecord = DeliverStatusList::findOne([
    'refRequestDeliver' => $deliverRecord->id,
    'status' =>$deliverRecord->requestStatus
    ]);
    if (empty($statusRecord))
    {
        $statusRecord = new DeliverStatusList();
        if (empty($statusRecord)) return false;
        $statusRecord->refRequestDeliver = $deliverRecord->id;
        $statusRecord->status = $deliverRecord->requestStatus;
    }
    $statusRecord->statusChange = date('Y-m-d H:i');
    $statusRecord->save();

    return true;
  }
  
  public function saveData    ()
  {
      $deliverRecord= DeliverList::findOne($this->id);    
    if (empty($deliverRecord)) return false;

    $deliverRecord->requestNum = $this->requestNum;
    $deliverRecord->requestDatePlanned = date ('Y-m-d', strtotime($this->requestDatePlanned));
    $deliverRecord->requestDateReal    = date ('Y-m-d', strtotime($this->requestDateReal));
    $deliverRecord->requestAdress = $this->requestAdress;
    $deliverRecord->requestPhone = $this->requestPhone;
    $deliverRecord->requestContact = $this->requestContact;
    $deliverRecord->requestNote = $this->requestNote;
    $deliverRecord->requestSclad = $this->requestSclad;
    $deliverRecord->requestScladAdress = $this->requestScladAdress;
    $deliverRecord->requestCategory = $this->requestCategory;
    $deliverRecord->requestPlaces = intval($this->requestPlaces );
    $deliverRecord->requestVolume = floatval($this->requestVolume)  ;
    $deliverRecord->requestTotalWeight = floatval($this->requestTotalWeight) ;
    $deliverRecord->supplyType = intval($this->supplyType);
    $deliverRecord->requestExecutorType = intval($this->requestExecutorType);

    $deliverRecord->factDate = date ('Y-m-d', strtotime($this->factDate));;
    $deliverRecord->factWeight =  floatval($this->factWeight);
    $deliverRecord->factValue =  floatval($this->factValue);

    
    $deliverRecord->request_time = $this->requestTime;
    $deliverRecord->request_exp_value = $this->requestExpValue;
    
    $deliverRecord->requestUPD =$this->requestUPD;
    
    /*Запомним склад*/
    if (!empty($this->requestSclad))
    {
    $scladCnt = Yii::$app->db->createCommand(
                    'Select count(id) from  {{%scladlist}} where sladTitle = :sladTitle')
                    ->bindValue(':sladTitle', $this->requestSclad)                                        
                    ->queryScalar();
    
        if ($scladCnt == 0)
        {            
        Yii::$app->db->createCommand(
                    'INSERT INTO {{%scladlist}} (sladTitle, scladAdress) VALUES (:requestSclad, :requestScladAdress )')
                    ->bindValue(':requestSclad', $this->requestSclad)                                        
                    ->bindValue(':requestScladAdress', $this->requestScladAdress)                                        
                    ->execute();
        }
    }
    
    $deliverRecord->requestExecutor = $this->requestExecutor;
    $deliverRecord->requestPhone = $this->requestPhone;
    $deliverRecord->refOrg= $this->refOrg;
    $deliverRecord->refFromOrg= $this->refFromOrg;
    
    /*Запомним старый статус*/
    $oldStatus = $deliverRecord->requestStatus;
    $deliverRecord->requestStatus = $this->requestStatus;

    /*Пройдем по всем*/
    for($i=1;$i<=$deliverRecord->requestStatus; $i++)
    { 
    //Ищем статус
    $statusRecord = DeliverStatusList::findOne([
        'refRequestDeliver' => $deliverRecord->id,
        'status' =>$i
    ]);
    if (empty($statusRecord))
    {
        $statusRecord = new DeliverStatusList();
        if (empty($statusRecord)) return false;
        $statusRecord->refRequestDeliver = $deliverRecord->id;
        $statusRecord->status = $i;
        $statusRecord->statusChange = date('Y-m-d H:i');
        $statusRecord->save();
    }
    }
    /*Уберем все что дальше текущего*/
    for($i=$deliverRecord->requestStatus+1;$i<=$oldStatus; $i++)
    {
    $statusRecord = DeliverStatusList::findOne([
        'refRequestDeliver' => $deliverRecord->id,
        'status' =>$i
    ]);
    if (!empty($statusRecord))
    {
     $statusRecord->delete();   
    }   
    }
    
    
    $deliverRecord->deliverSum = Yii::$app->db->createCommand(
                    'Select sum(requestGoodValue*requestCount) from  {{%request_deliver_content}} where requestDeliverRef = :requestDeliverRef')
                    ->bindValue(':requestDeliverRef', $deliverRecord->id)                                        
                    ->queryScalar();
                    
    $deliverRecord->save();
    $this->id = $deliverRecord->id;
    return $deliverRecord->id;
  }
  

/*
  Потоварное содержание заявки на доставку
*/  
    public function getContentDeliverProvider()
    {
        
    if (empty ($this->id) )$this->id =0;
    $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%request_deliver_content}} where requestDeliverRef=:requestDeliverRef', 
            [':requestDeliverRef' => $this->id])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT id, requestGoodTitle, requestGoodRef, requestCount, requestMeasure,requestDeliverRef, requestGoodValue  from {{%request_deliver_content}} 
               where requestDeliverRef=:requestDeliverRef',                  
            'params' => [':requestDeliverRef' => $this->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'requestGoodTitle', 
            'requestGoodRef', 
            'requestCount', 
            'requestMeasure',
            'requestGoodValue'
            ],
            
            ],
        ]);
    return $provider;    
    }

  public function getGrpGroup()
  {
      $wareModel = new WarehouseForm();
      $listGrp = $wareModel->getGrpGroup();     
      return $listGrp;
  }
 

 
  public function getGoodListProvider($params)
   {
       
    $deliverRecord= DeliverList::findOne($this->id);    
    if (empty($deliverRecord)) $this->schetId = 0;
    else{
    $this->schetId = $deliverRecord->refSchet;
    }

    $query  = new Query();
    $countquery  = new Query();

 
    if ( empty($deliverRecord->reason) )
    {
    $query->select ([
        '{{%schetContent}}.id',
        'warehouseRef as refWare',
        'wareTitle as goodTitle', 
        'wareCount as count',
        'warePrice as price', 
        '(warePrice*wareCount) as goodSumm', 
        'wareEd as ed',
        ])
    ->from("{{%schetContent}}")
    ->where("refSchet =".$deliverRecord->refSchet)
    ;
    
    $countquery->select ("count({{%schetContent}}.id)")
    ->from("{{%schetContent}}")
    ->where("refSchet =".$deliverRecord->refSchet)
    ;
    }    
    
    if ( $deliverRecord->reason == 2 )
    {
    $query->select ([
        '{{%supplier_schet_content}}.id',
        '{{%supplier_schet_content}}.wareRef as refWare',
        '{{%supplier_schet_content}}.goodTitle as goodTitle', 
        '{{%supplier_schet_content}}.goodCount   as count',
        '{{%supplier_schet_content}}.goodSumm/{{%supplier_schet_content}}.goodCount as price', 
        '{{%supplier_schet_content}}.goodEd as ed',
        '{{%supplier_schet_content}}.goodSumm'
        ])
    ->from("{{%supplier_schet_content}}")
    ->where("{{%supplier_schet_content}}.schetRef =".$deliverRecord->refSupplierSchet)
    ;
    $countquery->select ("count({{%supplier_schet_content}}.id)")
    ->from("{{%supplier_schet_content}}")
    ->where("{{%supplier_schet_content}}.schetRef =".$deliverRecord->refSupplierSchet)
    ;
    
    
    }    
    
 
 if (($this->load($params) && $this->validate())) {

/*     if (!empty ($this->grpGoodList))
        {
            $listGrp = $this->getGrpGroup();     
            $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGoodList] ]);
            $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGoodList]]);     
        }

    
     $query->andFilterWhere(['like', 'grpGood', $this->grpGood]);
     $countquery->andFilterWhere(['like', 'grpGood', $this->grpGood]);     

     $query->andFilterWhere(['like', 'title', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->goodTitle]);     
*/           
     }

//$this->debug[]=     $query->createCommand()->getRawSql();
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'goodTitle', 
                'price', 
                'ed',
                'goodSumm'
            ],
            'defaultOrder' => [ 'goodTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

  public function getScladListProvider($params)
   {

    $query  = new Query();
    $query->select ("id, sladTitle, scladAdress")->from("{{%scladlist}}");

    $countquery  = new Query();
    $countquery->select ("count(id)")->from("{{%scladlist}}");

    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'sladTitle', $this->sladTitle]);
     $countquery->andFilterWhere(['like', 'sladTitle', $this->sladTitle]);     
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'sladTitle', 
                'scladAdress'
            ],
            'defaultOrder' => [ 'sladTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
    
  public function getOrgListProvider($params)
   {

    $query  = new Query();
    $query->select ("id, title, schetINN")->from("{{%orglist}}");

    $countquery  = new Query();
    $countquery->select ("count(id)")->from("{{%orglist}}");

    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);     
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'title', 
            ],
            'defaultOrder' => [ 'title' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
    
    public function getStatusList()
    {
    return array(
                "0" => "Создано",
                "1" => "Подгот.  к отгр.",                
                "3" => "Выдано  эксп.",                
                "4" => "В доставке",
                "5" => "Доставлено",
                "6" => "Отчет сдан",
                "7" => "Завершено",
                );
    }
    
   public function prepareDeliversListData($params)
   {

    $query  = new Query();
    $query->select ([
    '{{%request_deliver}}.id', 
    'requestNum', 
    'supplyType',
    'creationDate',
    'requestDatePlanned', 
    'requestDateReal',
    '{{%request_deliver}}.refOrg', 
    'requestExecutor', 
    'requestStatus', 
    'deliverSum', 
    '{{%orglist}}.title', 
    '{{%user}}.userFIO', 
    '{{%schet}}.schetNum', 
    '{{%schet}}.schetDate', 
    '{{%schet}}.schetSumm', 
    'reason', 
    'factDate as requestDateFact',
    '{{%request_deliver}}.isActive',
    '{{%supplier_schet_header}}.schetNum as supplierSchetNum', 
    '{{%supplier_schet_header}}.schetDate as supplierSchetDate',  
    'ifnull(factDate,requestDatePlanned) as requestDate',
    'requestAdress',
    'requestUPD',
    'requestScladAdress',
    'requestSclad',
    'requestTotalWeight',
    'requestNote',
    'requestExecutorType',
    'isRefSupply',
    '{{%request_deliver}}.refSchet',
    'requestCategory',
    'requestPlaces',
    'sum({{%request_deliver_content}}.requestCount) as sumCount'
    ])
            ->from("{{%request_deliver}}")
            ->leftJoin('{{%request_deliver_content}}','{{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_deliver}}.refSchet')
            ->leftJoin('{{%supplier_schet_header}}','{{%supplier_schet_header}}.id = {{%request_deliver}}.refSupplierSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%request_deliver}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%request_deliver}}.refUser')
            ->groupby("{{%request_deliver}}.id")
            ;

    if ( $this->intSort == 1)
    {
        $query->orderBy([
            '{{%request_deliver}}.id' => SORT_DESC,
    
        ]);            
        
    }  
            
    $countquery  = new Query();
    $countquery->select (" count({{%request_deliver}}.id)")
            ->from("{{%request_deliver}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_deliver}}.refSchet')
            ->leftJoin('{{%supplier_schet_header}}','{{%supplier_schet_header}}.id = {{%request_deliver}}.refSupplierSchet')            
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%request_deliver}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%request_deliver}}.refUser');

            
    if(!empty ($this->dFrom))        
    {
     $query->andWhere(['>=', 'DATE([[requestDateReal]])', date("Y-m-d", strtotime($this->dFrom))]);
     $countquery->andWhere(['>=', 'DATE([[requestDateReal]])', date("Y-m-d", strtotime($this->dFrom))]);     
    }

    if(!empty ($this->dTo))        
    {
     $query->andWhere(['<=', 'DATE([[requestDateReal]])', date("Y-m-d", strtotime($this->dTo))]);
     $countquery->andWhere(['<=', 'DATE([[requestDateReal]])', date("Y-m-d", strtotime($this->dTo))]);     
    }

    
    if (($this->load($params) && $this->validate())) {            
     
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);
 
     $query->andFilterWhere(['like', 'requestExecutor', $this->requestExecutor]);
     $countquery->andFilterWhere(['like', 'requestExecutor', $this->requestExecutor]);
          
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     
     
     
     $query->andFilterWhere(['=', '{{%request_deliver}}.id', $this->id]);
     $countquery->andFilterWhere(['=', '{{%request_deliver}}.id', $this->id]);

     

          $query->andFilterWhere(['like', 'requestScladAdress', $this->requestScladAdress]);
     $countquery->andFilterWhere(['like', 'requestScladAdress', $this->requestScladAdress]);

          
     if(!empty($this->supplyType))
     {
     
     if($this->supplyType == 6)
     {
          $query->andFilterWhere(['!=', '{{%request_deliver}}.supplyType', 4]);
     $countquery->andFilterWhere(['!=', '{{%request_deliver}}.supplyType', 4]);
     }
     else{
          $query->andFilterWhere(['=', '{{%request_deliver}}.supplyType', $this->supplyType]);
     $countquery->andFilterWhere(['=', '{{%request_deliver}}.supplyType', $this->supplyType]);
     }
    }
    
            

     if (!empty($this->requestDateFact)){
        $query->andFilterWhere(['=', 'DATE([[factDate]])', date("Y-m-d", strtotime($this->requestDateFact))]);
        $countquery->andFilterWhere(['=', 'DATE([[factDate]])',  date("Y-m-d", strtotime($this->requestDateFact))]);
     }

     if (!empty($this->requestDateReal)){
        $query->andFilterWhere(['=', 'DATE([[requestDateReal]])', date("Y-m-d", strtotime($this->requestDateReal))]);
        $countquery->andFilterWhere(['=', 'DATE([[requestDateReal]])',  date("Y-m-d", strtotime($this->requestDateReal))]);
     }
     
            
     
     if (!empty($this->creationDate)){
        $query->andFilterWhere(['=', 'DATE([[creationDate]])', date("Y-m-d", strtotime($this->creationDate))]);
        $countquery->andFilterWhere(['=', 'DATE([[creationDate]])',  date("Y-m-d", strtotime($this->creationDate))]);
     }

     
    /* if (!empty($this->requestDate)){
        $query->andFilterWhere(['=', 'ifnull(factDate,requestDatePlanned)', date("Y-m-d", strtotime($this->requestDate))]);
        $countquery->andFilterWhere(['=', 'ifnull(factDate,requestDatePlanned)',  date("Y-m-d", strtotime($this->requestDate))]);
     }*/

     
     }
          
          
          
//     if (empty($this->requestStatus) )$this->requestStatus=8;                 
     if ($this->requestStatus < 7)    
     {
        $query->andFilterWhere(['=', 'requestStatus', $this->requestStatus]);
        $countquery->andFilterWhere(['=', 'requestStatus', $this->requestStatus]);
        $this->detail = 0;
     }
  /*   if ($this->requestStatus >= 6)
     {
        if ($this->requestStatus == 6)
        {
        $query->andWhere(['=', 'requestStatus', 6]);
        $countquery->andWhere(['=', 'requestStatus', 6]);
        }
     }*/
     if (empty($this->requestStatus) )
     {    
     /*Пока счет активен*/              
        switch ($this->detail)
        {
        case 4:
        /*новые*/
        $query->andWhere(['<=', 'requestStatus', 1]);
        $countquery->andWhere(['<=', 'requestStatus', 1]);
        break;

        case 5:
        /*Переданы в доставку*/
         //$strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus > 1 AND requestStatus < 3";            
        $query->andWhere(['<=', 'requestStatus', 3]);
        $countquery->andWhere(['<=', 'requestStatus', 3]);
        $query->andWhere(['>=', 'requestStatus', 2]);
        $countquery->andWhere(['>=', 'requestStatus', 2]);

        break;

        case 6:
       /*Доставлено*/
         //$strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus > 2 AND requestStatus < 5";            
        $query->andWhere(['<=', 'requestStatus', 5]);
        $countquery->andWhere(['<=', 'requestStatus', 5]);
        $query->andWhere(['>=', 'requestStatus', 4]);
        $countquery->andWhere(['>=', 'requestStatus', 4]);        
        break;


        default:
       /*Все активные в работе*/
         //$strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where requestStatus > 2 AND requestStatus < 5";            
        $query->andWhere(['<=', 'requestStatus', 5]);
        $countquery->andWhere(['<=', 'requestStatus', 5]);
        break;        
    }    
        
   }
   
   $this->command = $query->createCommand(); 
   $this->count = $countquery->createCommand()->queryScalar();

   }   
  
   public function getDeliversListData($params)
   {

    $this->intSort = 1;
    $this->prepareDeliversListData($params);
    
    $list = $this->command->queryAll();
    
   // print_r($this->command->getRawSql());
    
    $cnt = count($list);
    for ($i=0;$i<$cnt;$i++)
    {
        $goodList=Yii::$app->db->createCommand(
            'SELECT requestGoodTitle,requestCount,requestMeasure  from {{%request_deliver_content}} where requestDeliverRef=:requestDeliverRef Order By requestGoodTitle', 
            [':requestDeliverRef' => $list[$i]['id'] ])->queryAll();
            $good ="";
        for ($j=0;$j<count($goodList);$j++)
            $good .= $goodList[$j]['requestGoodTitle']." ".$goodList[$j]['requestCount']." ".$goodList[$j]['requestMeasure']."<br>";        
        $list[$i]['wareList'] = $good;        
   }
    
    return $list;
   }

   public function getDeliversListProvider($params)
   {

    $this->prepareDeliversListData($params);
   
   
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'creationDate',
                'requestDateReal', 
                'requestDateFact',
                'requestExecutor',
                'title', 
                'requestStatus', 
                'deliverSum', 
                'requestTotalWeight',
                'requestNum', 
                'refOrg',
                'requestExecutor', 
                'supplyType',
                'requestScladAdress'                
            ],
            
            'defaultOrder' => [ 'id' => 'DESC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /*****************************************/
 /* Задания экспедитору */
 
 public function printDeliverRequest($provider)
 {
 
echo
"
<div class='row'>
<div class='col-md-2'><div style='padding-top: 10px; text-align:right;'> Назначено от </div></div>
<div class='col-md-2'><input type='date' class='form-control' name='dFrom' id='dFrom' value='".$this->dFrom."' > </div>
<div class='col-md-1'><div style='padding-top: 10px; text-align:right;'>до </div></div>
<div class='col-md-2'><input type='date' class='form-control' name='dTo' id='dTo'  value='".$this->dTo."' > </div>
<div class='col-md-3' style='text-align:left;'><input type='button' class='btn btn-primary' onclick='setDatePeriod();' value='Отфильтровать'>
<input type='button' class='btn btn-primary' onclick='unSetDatePeriod();' value='Сбросить'>
</div>
</div><div class='spacer'></div>
". \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $this,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'attribute' => 'id',
                'label' => 'Номер',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) 
                {                    
                
                   $val= "<a href='#' onclick='openWin(\"store/deliver-zakaz&id=".$model['id']."\", \"deliverZakazWin\");' >";
                                                $val.=$model['id'];                   
                   $val.="</a>";
                   if($model['isRefSupply'] ==-1)$val.="&nbsp;<font color='Crimson'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span></font>";
                   if($model['isRefSupply'] ==1) $val.="&nbsp;<font color='Green'>  <span class='glyphicon glyphicon-ok-sign' aria-hidden='true'></span></font>";
                   return $val;
                },
            ],        

            [
                'attribute' => 'creationDate',
                'label' => 'Создана',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) 
                {                                    
                   $val ="";
                   if  (!empty($model['creationDate'])) $val=date("d.m.Y H:i", 7*60*60 + strtotime($model['creationDate'])); 
                   return $val;
                },
            ],        

            
            [
                'attribute' => 'requestDateReal',
                'label' => 'Назначена',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) 
                {        
                   $val="";
                   if  (strtotime($model['requestDateReal']) > strtotime("1970-01-01")) 
                       $val=date("d.m.Y", strtotime($model['requestDateReal']))."<br>";                    
/*                 if  (strtotime($model['requestDatePlanned']) > strtotime("1970-01-01")) 
                   {
                     $val.="Не&nbsp;позднее&nbsp;<br>".date("d.m.Y", strtotime($model['requestDatePlanned']));                      
                   }
  */                 
                   return $val;
                },
            ],        


       /*     [
                'attribute' => 'requestDateFact',
                'label' => 'Факт',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) 
                {            
                    $val ="";
                   if  (strtotime($model['requestDateFact']) > strtotime("1970-01-01")) $val="".date("d.m.Y", strtotime($model['requestDateFact']));                    
                   return $val;
                },
            ],      */  

            [
                'attribute' => 'requestScladAdress',
                'label' => 'Склад',
                'filter' => $this->getScladList(),
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:11px'],
            ],        

            
            
            [
                'attribute' => 'requestExecutor',
                'label' => 'Исполнитель',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
            ],        

            
            [
                'attribute' => 'Способ',
                'label' => 'Способ',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {                        
                    switch ($model['requestExecutorType'])
                    {
                      case 1:
                        return "Экспедитор";
                      break;
                      case 2:
                        return "Самовывоз";
                      break;
                      case 3:
                        return "Трансп. комп.";
                      break;
                      default:
                      return "&nbsp;";
                      break;
                    }
                
                },
                
            ],        
            

            
            [
                'attribute' => 'title',
                'label' => 'Организация',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },
            ],        
            
            [
                'attribute' => 'По счету',
                'label' => 'По счету',
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) 
                {
                if (empty($model['reason']))
                {
                    /*клиенту*/
                   if (empty($model['schetNum'])) {return;}    
                   $val= "<nobr>Счет клиенту № ".$model['schetNum']." </nobr>";
                   $val.="<br> от:".date("d.m.Y", strtotime($model['schetDate'])); 
                   $val.="<br> на сумму:".number_format($model['schetSumm'], 2, '.', ' ') ;
                   return $val;
                }elseif ($model['reason'] == 2 )
                {
                    /*поставщика*/
                   if (empty($model['supplierSchetNum'])) {return;}    
                   $val= "<nobr>Счет постав. № ".$model['supplierSchetNum']." </nobr>";
                   $val.="<br> от:".date("d.m.Y", strtotime($model['supplierSchetDate']));                    
                   return $val;
                }   
   
                  return; 
                },
            ],        
            
            [
                'attribute' => 'deliverSum',
                'label'     => 'Товаров <br>на сумму',
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {
                $v = "";
                if (!empty($model['refSchet']))
                {
                $oplataList=Yii::$app->db->createCommand(
                'SELECT MAX(oplateNum) as oplateNum,sum(oplateSumm) as oplateSumm, max(oplateDate) as oplateDate  from {{%oplata}} where refSchet=:refSchet ', 
                    [':refSchet' => $model['refSchet'] ])->queryOne();
                
                if (!empty($oplataList) && $oplataList['oplateSumm'] > 0){
                 $v = "Оплачено ".number_format($oplataList['oplateSumm'], 2, '.', '&nbsp;')." <br>";
                 $v .= "№ ".$oplataList['oplateNum']." <br>";
                 $v .=date("d.m.Y", strtotime($oplataList['oplateDate'])) ; 
                 }
                                    else $v = "";
                }                    
                  return number_format($model['deliverSum'], 2, '.', '&nbsp;')."<br>".$v ;
                }
            ],

            [
                'attribute' => 'requestTotalWeight',
                'label'     => 'Вес',
                'encodeLabel' => false,             
                 'filter' => false,   
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {
                 return number_format($model['requestTotalWeight'], 0, '.', '') ;
                }
            ],
            

           [
                'attribute' => 'supplyType',
                'label'     => 'Что<br>делать',
                'encodeLabel' => false,             
                 'filter' => [0=>'Все', 6 => 'Все перевозки' ,1 =>'Доставка клиенту', 2 =>'Перемещение', 4 =>'Документы', 5 => 'Доставка от поставщика'],   
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {
                
                    switch ($model['supplyType'])
                    {
                      case 1:
                        return "Доставка клиенту";
                      break;
                      case 2:
                        return "Перемещение";
                      break;
                      
                      case 4:
                        return "Документы";
                      break;
                      case 5:
                        return "Доставка от поставщика";
                      break;
                      default:
                        return "&nbsp;";
                      break;
                    }                                 
                }
            ],
            
            
            
            [
                'attribute' => 'requestStatus',
                'label'     => 'Статус',                
                'filter'=>array(
                "7" => "Все",
                "0" => "Создано",
                "1" => "Подгот.  к отгр.",                
                "2" => "Выдано  эксп.",                
                "3" => "В доставке",
                "4" => "Доставлено",
                "5" => "Отчет сдан",
                "6" => "Завершено",                
                ),

                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],

                'value' => function ($model, $key, $index, $column) {
                    $val ="";
                    $action = "openWin(\"store/deliver-zakaz&id=".$model['id']."\", \"deliverZakazWin\");";                    
                    
                    $stateDateList =  Yii::$app->db->createCommand(
                    'SELECT statusChange FROM {{%request_deliver_status}} where refRequestDeliver = :refRequestDeliver 
                    AND  status=:status', [':refRequestDeliver' =>$model['id'], ':status' =>$model['requestStatus'],])->queryAll();        
                    
                    $stateDate ="";
                    if (count($stateDateList) > 0)
                        $stateDate = "<br>".date("d.m.Y H:i", 7*60*60+strtotime($stateDateList[0]['statusChange']));
                    
                    switch ($model['requestStatus']) 
                    {
                    case 0:
                        $val = "<input class='btn btn-info local_btn' style='background:White; color:Black' 
                        type=button value='Создано' onclick='javascript:".$action."'>";
                        break;
                    case 1:
                        $val = "<input class='btn btn-info local_btn' style='background:LightSeaGreen' 
                        type=button value='Подгот.  к отгр.' onclick='".$action."'> ";
                        break;
                    case 2:
                        $val = "<input class='btn btn-info local_btn' style='background:LimeGreen' 
                        type=button value='Выдано  эксп.' onclick='".$action."'>";
                        break;

                    case 3:
                        $val = " <input class='btn btn-default local_btn' style='background:Blue; color:White' 
                        type=button value='В доставке' onclick='".$action."'>";
                        break;

                    case 4: 
                        $val = "<input class='btn btn-info local_btn' style='background:Green' 
                        type=button value='Доставлено' onclick='".$action."'>";
                        break;

                    case 5:
                        $val = "<input class='btn btn-info local_btn' style='background:DarkGreen;font-weight:bold' 
                        type=button value='Отчет сдан' onclick='".$action."'>";
                        break;

                    case 6:
                        $val = "<input class='btn btn-info local_btn' style='background:DarkGreen;font-weight:bold' 
                        type=button value='Завершено' onclick='".$action."'>";
                        break;
                    }    
                        

                return $val.$stateDate;
                }
                
            ],            


             [
                'attribute' => 'Действие',
                'label'     => '',                
                'format' => 'raw',
                'contentOptions' => ['style' =>'font-size:12px'],
                'value' => function ($model, $key, $index, $column) {
                            return " <a href='#'  onclick='openWin(\"store/deliver-zakaz-print&noframe=1&id=".$model['id']."\", \"deliverZakazWin\");' >
                            <span class='glyphicon glyphicon-print' aria-hidden='true'></span></a>
                            
                            <a href='#'  onclick='openWin(\"store/deliver-print-ttn&noframe=1&id=".$model['id']."\", \"deliverZakazWin\");' >
                            ТТН</a>
                            
                            <a href='#' style='color:Crimson;'  onclick='javascript:openSwitchWin(\"store/deliver-delete&id=".$model['id']."\", \"deliverWin\");' >
                            <span class='glyphicon glyphicon-remove' aria-hidden='true'></span></a>";                                
                            ;                    
                    }    

            ],

    

            /*[
                'attribute' => 'Действие',
                'label'     => '',                
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
                        return " <a href='#' style='color:Crimson;'  onclick='javascript:openSwitchWin(\"store/deliver-delete&id=".$model['id']."\", \"deliverWin\");' >
                            <span class='glyphicon glyphicon-remove' aria-hidden='true'></span></a>";                    
                        
                    }    

            ],*/
            
            
        ],
    ]
)."<div style='padding:10px;'>
<a href='#' onclick=\"openExtWin('index.php?".Yii::$app->request->queryString."&format=print&noframe=1', 'printWin');\"> Реестр доставок</a>
&nbsp;&nbsp;&nbsp;
<a href='#' onclick=\"openExtWin('index.php?".Yii::$app->request->queryString."&format=print2&noframe=1', 'printWin');\"> Маршрутный лист</a>
&nbsp;&nbsp;&nbsp;
<a href='#' onclick=\"openExtWin('index.php?".Yii::$app->request->queryString."&format=csv&noframe=1', 'printWin');\"> Выгрузить маршрутный лист</a>
&nbsp;&nbsp;&nbsp;
<a href='#' onclick=\"openExtWin('index.php?".Yii::$app->request->queryString."&format=ttn&noframe=1', 'printWin');\"> список ТТН</a>

</div>

";
;
  
     
     
 }
/*****************/
public function fixPeriod()
{

$m = date('n');
$y = date('Y');

  if ($this->m_from < 1 || $this->m_from > 12) $this->m_from = $m;
  if ($this->m_to   < 1 || $this->m_to   > 12) $this->m_to = $m;

  if ($this->y_from < 1970 || $this->y_from > 3000) $this->y_from = $y;
  if ($this->y_to   < 1970 || $this->y_to   > 3000) $this->y_to = $y;
        

}
/*****************/
  public function getFinishedDeliversListProvider($params)
   {

    $query  = new Query();
    $query->select ([
    '{{%request_deliver}}.id', 
    'requestNum', 
    'requestDatePlanned',   
    'creationDate',    
    '{{%request_deliver}}.refOrg', 
    'requestExecutor', 
    'requestStatus', 
    'deliverSum', 
    '{{%orglist}}.title', 
    '{{%user}}.userFIO', 
    '{{%schet}}.schetNum', 
    '{{%schet}}.schetDate', 
    '{{%schet}}.schetSumm', 
    'reason', 
    '{{%supplier_schet_header}}.schetNum as supplierSchetNum', 
    '{{%supplier_schet_header}}.schetDate as supplierSchetDate',        
    '{{%supplier_schet_header}}.orgTitle',
    'requestAdress',
    'requestSclad',
    'factDate',
    'requestGoodTitle',
    'requestCount',
    'requestMeasure'    
    ])
            ->from("{{%request_deliver}}")            
            ->leftJoin('{{%request_deliver_content}}','{{%request_deliver_content}}.requestDeliverRef = {{%request_deliver}}.id')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_deliver}}.refSchet')
            ->leftJoin('{{%supplier_schet_header}}','{{%supplier_schet_header}}.id = {{%request_deliver}}.refSupplierSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%request_deliver}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%request_deliver}}.refUser');

    $countquery  = new Query();
    $countquery->select (" count({{%request_deliver}}.id)")
            ->from("{{%request_deliver}}")
            ->leftJoin('{{%request_deliver_content}}','{{%request_deliver_content}}.requestDeliverRef = {{%request_deliver}}.id')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_deliver}}.refSchet')
            ->leftJoin('{{%supplier_schet_header}}','{{%supplier_schet_header}}.id = {{%request_deliver}}.refSupplierSchet')            
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%request_deliver}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%request_deliver}}.refUser');

     $query->andWhere(['=', 'requestStatus', 6]);
     $countquery->andWhere(['=', 'requestStatus', 6]);

            
    if (($this->load($params) && $this->validate())) {
         
    /* $query->andFilterWhere(['=', 'requestStatus', $this->requestStatus]);
     $countquery->andFilterWhere(['=', 'requestStatus', $this->requestStatus]);
         
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);*/

     $query->andFilterWhere(['like', 'requestGoodTitle', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'requestGoodTitle', $this->goodTitle]);
     
     $query->andFilterWhere(['like', 'requestExecutor', $this->requestExecutor]);
     $countquery->andFilterWhere(['like', 'requestExecutor', $this->requestExecutor]);
          
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);

     if (!empty($this->requestDate)){
        $query->andFilterWhere(['=', 'ifnull(factDate,requestDatePlanned)', date("Y-m-d", strtotime($this->requestDate))]);
        $countquery->andFilterWhere(['=', 'ifnull(factDate,requestDatePlanned)',  date("Y-m-d", strtotime($this->requestDate))]);
     }
     
     }

     $countquery->andFilterWhere(['<=', 'YEAR({{%request_deliver}}.requestDatePlanned)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR({{%request_deliver}}.requestDatePlanned)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR({{%request_deliver}}.requestDatePlanned)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR({{%request_deliver}}.requestDatePlanned)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH({{%request_deliver}}.requestDatePlanned)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH({{%request_deliver}}.requestDatePlanned)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH({{%request_deliver}}.requestDatePlanned)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH({{%request_deliver}}.requestDatePlanned)', $this->m_from]);
 
     
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
                'id', 
                'requestNum', 
                'requestDatePlanned', 
                'refOrg'. 
                'requestExecutor', 
                'requestStatus', 
                'deliverSum', 
                'title', 
                'userFIO',
                'schetNum',
                'schetDate',
                'schetSumm',
                'requestExecutor',
                'requestGoodTitle',

            ],
            
         
            
            ],
            
        ]);
    return  $dataProvider;        
 }
 /*************************************/
 /********** Расчет *******************/
 public function prepareDeliverExecuteData($params)
   {

    $query  = new Query();
    $query->select ([
        'id',
        'requestNum',
        'factDate',
        'refSchet',
        'refSupplierSchet',
        'factValue',
        'factWeight',
        'requestExecutor',
        'supplyType',
        'requestAdress',
        'requestScladAdress',
        'requestNote',
        'refOplateWrkExp',
        'refOplateExpCost',
        'refOplateDrive',
        'request_time',
        'request_exp_value',
        'ifnull(a.s,0) as sumOplateWrkExp',
        'ifnull(b.s,0) as sumOplateExpCost',
        'ifnull(c.s,0) as sumOplateDrive',
    ])
      ->from("{{%request_deliver}}")
      ->leftJoin("(Select Sum(oplateSumm) as s, reestrId from {{%oplata}}, {{%reestr_lnk}} 
                   where {{%oplata}}.id ={{%reestr_lnk}}.oplataId GROUP BY reestrId) as a", "a.reestrId = refOplateWrkExp" )
      ->leftJoin("(Select Sum(oplateSumm) as s, reestrId from {{%oplata}}, {{%reestr_lnk}} 
                   where {{%oplata}}.id ={{%reestr_lnk}}.oplataId GROUP BY reestrId) as b", "b.reestrId = refOplateExpCost" )
     ->leftJoin("(Select Sum(oplateSumm) as s, reestrId from {{%oplata}}, {{%reestr_lnk}} 
                   where {{%oplata}}.id ={{%reestr_lnk}}.oplataId GROUP BY reestrId) as c", "c.reestrId = refOplateDrive" )                   
        ;

        
        
    $query->andWhere(['=','requestStatus', '6']);
        
    if (($this->load($params) && $this->validate())) {
        
     switch ($this->refOplateWrkExp)
     {
         case 2:
            $query->andFilterWhere(['=','refOplateWrkExp', 0]);      
         break;
         case 3:
            $query->andFilterWhere(['>','refOplateWrkExp', 0]);               
         break;
     } 

     switch ($this->refOplateExpCost)
     {
         case 2:
            $query->andFilterWhere(['=','refOplateExpCost', 0]);      
         break;
         case 3:
            $query->andFilterWhere(['>','refOplateExpCost', 0]);               
         break;
     } 

     switch ($this->refOplateDrive)
     {
         case 2:
            $query->andFilterWhere(['=','refOplateDrive', 0]);      
         break;
         case 3:
            $query->andFilterWhere(['>','refOplateDrive', 0]);               
         break;
     } 

     
    if (!empty($this->factDate))  
    {    
        $query->andFilterWhere(['=','factDate', date('Y-m-d', strtotime($this->factDate))]);      
    }
     $query->andFilterWhere(['=','requestNum', $this->requestNum]);               
     $query->andFilterWhere(['Like','requestAdress', $this->requestAdress]);               
     $query->andFilterWhere(['Like','requestScladAdress', $this->requestScladAdress]);               
     $query->andFilterWhere(['Like','requestNote', $this->requestNote]);               
     
     }
 
    if (!empty($this->dTo)) 
    {     
     $query->andFilterWhere(['>=','factDate', date('Y-m-d', strtotime($this->dFrom))]);      
    }
 
    if (!empty($this->dFrom)) 
    {     
     $query->andFilterWhere(['<=','factDate', date('Y-m-d', strtotime($this->dTo))]);      
    }

   $this->command = $query->createCommand(); 
   
   //echo $query->createCommand()->getRawSql();   
   $query->orderBy('factDate DESC');
   $list= $query->createCommand()->queryAll();   
   $this->count = count($list);




   $valWRec = ConfigTable::FindOne(150);
   if (empty($valWRec))$valWeight=0;
   else $this->valWeight=floatval($valWRec->keyValue);

   $valTRec = ConfigTable::FindOne(151);
   if (empty($valTRec))$valTime=0;
   else $this->valTime=floatval($valTRec->keyValue);
   
   $sum=0;
   $this->itogoWeight=0;
   $this->driveItog=0;
   $this->expCostItog=0;
   $this->expWrkItog=0;
   
   for ($i=0; $i< $this->count; $i++)
   { 
   
    if ($list[$i]['supplyType'] != 4) $this->itogoWeight+=$list[$i]['factWeight'];
    $this->itogoTime+=$list[$i]['request_time'];
    $this->expCostItog+=$list[$i]['request_exp_value'];
    $this->driveItog+=$list[$i]['factValue'];
   }

    $this->expWrkItog = $this->itogoWeight*$this->valWeight + $this->itogoTime*$this->valTime;
    return $list;
  }   
  
  public function setDeliverValues($valTime, $valWeight)
  {
   $valWRec = ConfigTable::FindOne(150);
   if (!empty($valWRec))
   {
    $valWRec->keyValue = $valWeight;
    $valWRec->save();
   }
   $valTRec = ConfigTable::FindOne(151);
   if (!empty($valTRec))
   {
    $valTRec->keyValue = $valTime;   
     $valTRec->save();
   }
  }

   public function oplateDeliverExecute($params, $sum, $actionType)
   {  
      $list = $this->prepareDeliverExecuteData($params);  
      
      $cnt = count($list);
      /*Создадим оплату*/
      $curUser=Yii::$app->user->identity; 
      
       $record = new  ReestrOplat();           
       if (empty($record )) return -1;
       $record->formDate =  date('Y-m-d h:i');  
       $record->refManager = $curUser->id ;
       
        $sumn= $sum*1.1;

       switch ($actionType)
       {
       case 1:    
        $record->oplateType = 1;
        $record->note ='Работа экспедитора: '.number_format($sum,2,'.','').', итого сумма счёта, включая оплату налогов: '.number_format($sumn,2,'.','');
        $strSql = "UPDATE {{%request_deliver}} SET refOplateWrkExp= :refOplate where id=:refDeliver "; //AND refOplateWrkExp=0
       break;
       case 2:    
        $record->oplateType = 2;
        $record->note ='Затраты экспедитора: '.number_format($sum,2,'.','').', итого сумма счёта, включая оплату налогов: '.number_format($sumn,2,'.','');
        $strSql = "UPDATE {{%request_deliver}} SET refOplateExpCost= :refOplate where id=:refDeliver"; // AND refOplateExpCost=0
       break;
       case 3:    
        $record->oplateType = 2;
        $record->note ='Затраты водителя: '.number_format($sum,2,'.','').', итого сумма счёта, включая оплату налогов: '.number_format($sumn,2,'.','');
        $strSql = "UPDATE {{%request_deliver}} SET refOplateDrive= :refOplate where id=:refDeliver"; // AND refOplateDrive=0
       break;
       }
       $record->save();    
      /*Пометим как оплаченное - только еще не оплаченные*/
      for ($i=0;$i<$cnt;$i++)
      {
        Yii::$app->db->createCommand($strSql, [':refDeliver' => $list[$i]['id'],':refOplate' => $record->id,])->execute();                                        
      }
      
      //print_r($list);
   } 

  
  
   public function getDeliverExecuteData($params)
   {  
      return $this->prepareDeliverExecuteData($params);  
   } 
   
   
   public function getDeliverExecuteProvider($params)
   {

    $this->prepareDeliverExecuteData($params);
   
   
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
            'id',
            'requestNum',
            'factDate',
            'refSchet',
            'refSupplierSchet',
            'factValue',
            'factWeight',
            'requestExecutor',
            'supplyType',
            'requestAdress',
            'requestScladAdress',
            'requestNote',
            'refOplateWrkExp',
            'refOplateExpCost',
            'refOplateDrive',
            'request_time',
            'request_exp_value'

            ],
            
            'defaultOrder' => [ 'factDate' => SORT_DESC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /*****************************************/

 public function getDeliverRouteFile($deliversListData)
 {
 
        $mask = realpath(dirname(__FILE__))."/../uploads/deliverRoute*.csv";
        array_map("unlink", glob($mask));       
        $fname = "uploads/deliverRoute".time().".csv";
        $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
        if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

        $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата доставки"),
        iconv("UTF-8", "Windows-1251","Счет"),        
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Адрес отгрузки"),
        iconv("UTF-8", "Windows-1251","Адрес доставки"),
        iconv("UTF-8", "Windows-1251","Что делать"),
        iconv("UTF-8", "Windows-1251","Номер УПД"),
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","Вид/Категория"),
        iconv("UTF-8", "Windows-1251","К-во мест"),
        iconv("UTF-8", "Windows-1251","Вес, кг"),        
        );
                
        fputcsv($fp, $col_title, ";"); 

    $cnt = count($deliversListData); 
    $itogoWeight=0;
    for ($i=0; $i < $cnt; $i++){
    $itogoWeight+=floatval($deliversListData[$i]['requestTotalWeight']);

    switch ($deliversListData[$i]['supplyType'])
                    {
                      case 1:
                        $supplyType= "Доставка клиенту";
                      break;
                      case 2:
                        $supplyType= "Перемещение";
                      break;                      
                      case 4:
                        $supplyType= "Документы";
                      break;
                      case 5:
                        $supplyType= "Доставка от поставщика";
                      break;
                      default:
                        $supplyType= "";
                      break;
                    }

    
    
    $requestDateReal = date("d.m.y", strtotime($deliversListData[$i]['requestDateReal']));
            $list = array 
            (
        iconv("UTF-8", "Windows-1251",$requestDateReal),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestNum']),        
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['title']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestScladAdress']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestAdress'] ),
        iconv("UTF-8", "Windows-1251",$supplyType),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestUPD']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['wareList']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestCategory']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestPlaces']),
        iconv("UTF-8", "Windows-1251",$deliversListData[$i]['requestTotalWeight']),        
           );
     
    fputcsv($fp, $list, ";");  
    }
    
            $list = array 
            (
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),        
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251",""),
        iconv("UTF-8", "Windows-1251","Итого вес:"),
        iconv("UTF-8", "Windows-1251",$itogoWeight),        
           );
     
    fputcsv($fp, $list, ";");  
        
        fclose($fp);
        return $fname;     
  
 }



/**/    
 }
 
