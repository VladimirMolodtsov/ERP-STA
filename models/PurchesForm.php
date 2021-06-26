<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\Purchase;
use app\models\PurchaseZakaz;
use app\models\PurchaseZakazWare;
use app\models\PurchaseVariant;
use app\models\PurchaseEtap;
use app\models\WarehouseForm;
use app\models\SupplierSchetContentList;
use app\models\ContactList;
use app\models\ScladList;
use app\models\OrgList;
use app\models\RequestGoodContent;
use app\models\ZakazContent;
use app\models\ReestrOplat;
use app\models\ReestrLnk;
use app\models\SupplierSchetHeaderList;
use app\models\PriceCategoryList;
use app\models\TblPurchSchetLnk;
use app\models\TblPurchControlLnk;
use app\models\TblControlPurchContent;
use app\models\TblSupplierWares;
use app\models\TblSchetContent;
/**
 * Модель - формы закупок
 */

 /*	
    supplyState
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
  0x00008 - 

*/  



class PurchesForm extends Model
{
    public $id= 0;
    public $mode= 0;

	public $grpGoodList = '';			
    public $grpGood="";
    public $goodTitle = '';
    public $suppliersN = 0;
    public $isFinishedPurchase; 
    
    public $goodCount = 0;
    public $goodEd = '';
    public $goodValue = 0;
  
    public $supplierTitle = '';
    public $supplierRef = 0;
   
    public $zakazNote ='';
    
    public $requestStatus =1;
    public $zakazStatus =1;
    public $oplateStatus =1;
    public $deliverStatus =1;
    public $docStatus =1;
    
    public $refWarehouse = 0;
     
    public $wareTitle = ''; 
    public $orgTitle= '';      
    
    /*service*/
    public $debug;	   
    public $count;
    public $command;

    public $statusVal;
    
    public $fltOrgTitle= '';      
    
    public $status;
    public $selVariant;

   public $Etap2IsSogl=0;
   public $Etap1IsSogl=0;

    
    public $ref1C;
    public $purchTitle;
    
    public $schetNum;  
    public $supplierRef1C;
    public $schetDate;  
    public $schetRef;  
    public $zakazRef;  
    public $informText;
    public $showList;
    public $isActive=1;
    public $zaprosIsActive;
    
    public $zaprosType=1;
    public $zaprosCategory=0;
    public $pcntVal1=0;
    public $pcntVal2=0;
    public $pcntVal3=0;
    public $pcntVal4=0;
    
    public $zaprosStatus = 0;
    
    public $fromDate;
    public $toDate;
    
    public $docOrigNum;
    
        /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 

    
    public function rules()
    {
        return [
			[['id','goodValue','goodTitle','refWarehouse', 'goodCount', 'goodEd', 'supplierTitle', 'zakazNote',
            'zaprosType', 'zaprosCategory', 'pcntVal1', 'pcntVal2', 'pcntVal3', 'pcntVal4', 
            'recordId', 'dataType', 'dataVal', 'dataId',
            ], 'default'],
            [['zaprosType', 'zaprosCategory'], 'integer'],
            [['goodCount', 'pcntVal1', 'pcntVal2','pcntVal3','pcntVal4',], 'double'],
            [['wareTitle', 'orgTitle', 'goodTitle', 'grpGoodList','grpGood','suppliersN', 
            'isFinishedPurchase', 'zaprosIsActive', 'zaprosStatus', 'schetNum', 'supplierRef1C', 'ref1C', 'purchTitle', 'docOrigNum'], 'safe'],
        ];
    }
/*****************/  

public function savePurchase ()
{
   $record = Purchase::FindOne($this->id);
   if (empty($record)) return;

    $record->purchaseNote = $this->zakazNote ;
    $record->save();
}    


public function preparePurchase()    
{

   $record = Purchase::FindOne($this->id);
   if (empty($record)) return false;
    
    $this->supplierRef = $record->refOrg;
    $this->supplierTitle = Yii::$app->db->createCommand("SELECT title from {{%orglist}} where id = :orgRef",
                          [':orgRef' => $this->supplierRef,])->queryScalar();
   
    $this->zakazNote = $record->purchaseNote;
    $this->schetRef = $record->supplierShetRef;  
    if ($this->schetRef > 0)
    {
    $list = Yii::$app->db->createCommand("SELECT schetNum, schetDate from {{%supplier_schet_header}} where id = :schetRef",
                          [':schetRef' => $this->schetRef,])->queryAll();
        
    $this->schetNum = $list[0]['schetNum'];  
    $this->schetDate = $list[0]['schetDate'];          
    }
    
   
  $list = Yii::$app->db->createCommand("SELECT stage, etap, execDate from {{%purchase_etap}} where purchaseRef = :purchaseRef",
                          [':purchaseRef' => $this->id,])->queryAll();
    
 $this->statusVal = array();
 //init
 for ($i=0;$i<9;$i++)
 {
   $this->statusVal['s1'][$i]="";
   $this->statusVal['s2'][$i]="";
   $this->statusVal['s3'][$i]="";
   $this->statusVal['s4'][$i]="";
 }

 
 for ($i=0;$i<count($list);$i++)
 {
   $stage="s".$list[$i]['stage'];  
   $etap = $list[$i]['etap'];  
   $this->statusVal[$stage][$etap]=date('d.m.Y',strtotime($list[$i]['execDate']));
   
   if ($list[$i]['stage'] == 1 && $list[$i]['etap'] == 2)
   {
    //согласование закупки
    $this->Etap1IsSogl = 1;
   }
   if ($list[$i]['stage'] == 2 && $list[$i]['etap'] == 3)
   {
    //согласование закупки
    $this->Etap2IsSogl = 1;
   }

   
 }

return $record ;    
}
/*****************/
   public function getLeafValue()
   {
    $curUser=Yii::$app->user->identity;
       
       /*Закупки */
   

   $leafValue['orders'] = 0;             
   $leafValue['requestInWork'] = 0;             
   $leafValue['requestInSogl'] = 0;             
   $leafValue['requestComplete'] = 0;             
   $leafValue['requestInPurchase'] = 0;             
   $leafValue['purchaseInWork'] = 0;             
   $leafValue['purchaseInSogl'] = 0;             
   $leafValue['purchaseInCash'] = 0;             
   $leafValue['purchaseInDeliver'] = 0;             
   $leafValue['purchaseInFinit'] = 0;             
   
   
   
   
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} 
   left join {{%purchase}} on {{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef
   where zaprosType = 1 AND  isActive =1 
   AND ifnull({{%purchase_zakaz}}.zaprosType,0) = 0 AND ifnull({{%purchase}}.isFinishedPurchase,0) = 0";            
   $leafValue['orders'] = Yii::$app->db->createCommand($strCount )->queryScalar();             

   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} 
   left join {{%purchase}} on {{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef   
   where zaprosType = 0 AND  {{%purchase_zakaz}}.status = 1 AND isActive =1    
   AND ifnull({{%purchase_zakaz}}.zaprosType,0) = 0 AND ifnull({{%purchase}}.id,0) = 0";               
   $leafValue['purchase_zakaz'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   $leafValue['requestInSogl'] = $leafValue['purchase_zakaz'];

   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} 
   left join {{%purchase}} on {{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef   
   where zaprosType = 0 AND  {{%purchase_zakaz}}.status = 2 AND isActive =1 
   AND ifnull({{%purchase_zakaz}}.zaprosType,0) = 0 AND ifnull({{%purchase}}.isFinishedPurchase,0) = 0";               
   $leafValue['requestComplete'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} 
   left join {{%purchase}} on {{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef   
   where  zaprosType = 0 AND {{%purchase_zakaz}}.status = 8 AND isActive =1 
   AND ifnull({{%purchase_zakaz}}.zaprosType,0) = 0 AND ifnull({{%purchase}}.isFinishedPurchase,0) = 0";               
   $leafValue['requestInPurchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}}
   left join {{%purchase}} on {{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef    
   where  zaprosType = 0 AND isActive =1 
   AND ifnull({{%purchase_zakaz}}.zaprosType,0) = 0 AND ifnull({{%purchase}}.isFinishedPurchase,0) = 0";               
   $leafValue['requestInWork'] =Yii::$app->db->createCommand($strCount )->queryScalar();             

   
   
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =1 AND stage=1 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =3 AND stage=2 group by purchaseRef) as b on b.purchaseRef = {{%purchase}}.id
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =0 AND stage=1 group by purchaseRef) as a1 on a1.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =2 AND stage=2 group by purchaseRef) as b1 on b1.purchaseRef = {{%purchase}}.id
   where ((ifnull(a.sN,0) =0 AND ifnull(a1.sN,0) >0) OR (ifnull(b.sN,0) =0 AND ifnull(b1.sN,0) >0 ))";            
   $leafValue['purchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   $leafValue['purchaseInSogl'] = $leafValue['purchase'] ;
   
   

   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where  isActive =1 ";            
   $leafValue['purchaseActive'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   $leafValue['purchase_zakaz_all'] = $leafValue['purchaseActive'];
    
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =4 AND stage=3 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id    
   where (ifnull(a.sN,0) =0 )"; 

   $leafValue['purchase_all'] = Yii::$app->db->createCommand($strCount )->queryScalar();             
      
   $leafValue['purchaseActive'] += $leafValue['purchase_all'];
                           
                           
                           
   $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%purchase}}.id))")
    ->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $leafValue['purchaseInWork'] = $countquery->createCommand()->queryScalar();
   

   $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;        
   $countquery->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
   $leafValue['purchaseInSogl']  = $countquery->createCommand()->queryScalar();
   

   $countquery  = new Query();
   $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=5 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=6 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
  $countquery->andWhere("(  (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
  $leafValue['purchaseInCash'] = $countquery->createCommand()->queryScalar();

  $countquery  = new Query();
  $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
  $countquery->leftJoin("(Select count(id) as s3_startN, purchaseRef from {{%purchase_etap}} where stage =3  group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s3_endN, purchaseRef from {{%purchase_etap}} where stage =3 and etap =8 group by purchaseRef) as s3_end ", 's3_end.purchaseRef = {{%purchase}}.id')                
        ;
  $countquery->andWhere("(  (ifnull(s3_startN,0) >0 AND ifnull(s3_endN,0)=0 )   )");
  $leafValue['purchaseInDeliver'] = $countquery->createCommand()->queryScalar();

   $countquery  = new Query();
   $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s1_N, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=4 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_N, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=7 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s3_N, purchaseRef from {{%purchase_etap}} where stage =3 AND etap=8 group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                            
            ->leftJoin("(Select count(id) as s4_startN, purchaseRef from {{%purchase_etap}} where stage =4  and etap =2 group by purchaseRef) as s4_start ", 's4_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s4_endN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =3 group by purchaseRef) as s4_end ", 's4_end.purchaseRef = {{%purchase}}.id')                
        ;
   $countquery->andWhere("(  ifnull(s1_N,0)>0 AND ifnull(s2_N,0)>0  AND ifnull(s3_N,0)>0 AND  (ifnull(s4_startN,0) >0 AND ifnull(s4_endN,0)=0 )   )");
   $leafValue['purchaseInFinit'] = $countquery->createCommand()->queryScalar();
                        
   return $leafValue;   
   }
 

/*****************/    
public function saveZakaz ()
{    
   if ($this->id == 0)
   {
   
    $record = new  PurchaseZakaz();           
    $record->zakazNote = $this->zakazNote;   
    if (!empty($model->zakazRef))$record->refZakaz = $model->zakazRef;    
     /*Начальный заказ сохраним*/   
    if ($this->refWarehouse == 0) 
    {    
        /*Абы что*/     
     $record->wareTitle = $this->goodTitle;
     $record->wareCount = $this->goodCount;
     $record->wareEd    = $this->goodEd;
     $record->refWarehouse =  $this->refWarehouse;
     $record->save();    
    }
    else
    {
      /*со склада*/  
     $scladRecord = ScladList::FindOne($this->refWarehouse);   
     $record->wareTitle = $scladRecord->title;
     $record->wareEd    = $scladRecord->ed;
     $record->wareCount = $this->goodCount;
     $record->refWarehouse =  $scladRecord->id;
     $record->save();
    /*Добавим в общий список*/       
    $this->addWareInZakaz($record->id,$this->refWarehouse);   
    }
    $this->id = $record->id;
    return;   
   }
   
   $record = PurchaseZakaz::FindOne($this->id);
   if (empty($record)) return;

   $record->zakazNote = $this->zakazNote;
   $record->save();
}
/****/

public $deliverList="";
public $deliverSum =0;
public function   getDeliverData()
{
   if (empty ($this->id)) return -1; // purchase is not set
   $record = Purchase::FindOne($this->id);
   if (empty($record)) return -2 ; // purchase is not exist
//   if (empty($record ->supplierShetRef)) return 0;   // счет не установлен - нормальная ситуация

    if (!empty($this->deliverList))return 1;      

    $this->deliverList =  Yii::$app->db->createCommand(
	'Select sum(requestGoodValue*requestCount) as sumGood,
	sum(request_exp_value + factValue) as sumDeliver,
    {{%request_deliver}}.id as deliverId
    from {{%request_deliver}}, {{%request_deliver_content}} 
    where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef 
    and {{%request_deliver}}.refPurchase= :purchRef
    GROUP BY {{%request_deliver}}.id
    ')->bindValue(':purchRef', $this ->id)->queryAll();

//    $this->debug[] = $this->deliverList;
    
    for ($i=0; $i< count($this->deliverList); $i++)
	{
       $this->deliverSum +=$this->deliverList[$i]['sumDeliver'];
//       $this->debug[] = $this->deliverSum;
	}    
   return 0;
}

public function  getPurchTotalSum()
{
    $sum = $this->deliverSum;

     $sum += Yii::$app->db->createCommand('Select sum(purchSum) as totalSum  from {{%purch_schet_lnk}}
     where purchRef = :purchRef')
     ->bindValue(':purchRef', $this->id)->queryScalar();
    
    return $sum;
}

public function  getPurchControlSum()
{
    $sum = $this->deliverSum;

     $sum += Yii::$app->db->createCommand('Select sum(purchSum) as totalSum  from {{%purch_control_lnk}}
     where purchRef = :purchRef')
     ->bindValue(':purchRef', $this->id)->queryScalar();
    
    return $sum;
}


public function  getPurchControlWareSum()
{
     $sum = Yii::$app->db->createCommand('Select sum(purchSum) as totalSum  from {{%purch_control_lnk}}
     where purchRole=0 AND purchRef = :purchRef')
     ->bindValue(':purchRef', $this->id)->queryScalar();
    
    return $sum;
}

public function  getPurchControlAddSum()
{
     $sum = Yii::$app->db->createCommand('Select sum(purchSum) as totalSum  from {{%purch_control_lnk}}
     where purchRole>0 AND purchRef = :purchRef')
     ->bindValue(':purchRef', $this->id)->queryScalar();
    
    return $sum;
}


public function   printDeliverList()
{
/*    $this->getDeliverData();
    print_r($this->deliverList);*/
if (!empty($this->deliverList)){
    echo "<b>Перевозка</b>:</br>";                
    for ($i=0; $i< count($this->deliverList); $i++)
          echo  "<a href='#' onclick='javascript:openWin(\"store/deliver-zakaz&id=".$this->deliverList[$i]['deliverId']."\", \"deliverWin\");'>
                        <nobr>Cумма затрат: ".number_format($this->deliverList[$i]['sumDeliver'], 2, '.', '&nbsp;')."</nobr></a><br>" ;
    }
    echo "<div style='width:100%; text-align:right;' ><a href='#' 
    onclick='javascript:openWin(\"store/deliver-zakaz&action=create&type=supplier&refPurchase=".$this->id."\", 
    \"deliverWin\");'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a></div>";       
   return 0;
}


public function   purchaseZakazAddLink($requestId, $zaprosId)
{
    
   $requestRecord =   RequestGoodContent::FindOne($requestId);
   if (empty($requestRecord)) return -1;
   
   $requestRecord->refPurchaseZakaz = $zaprosId;
   $requestRecord->isInWork  = 1;
   $requestRecord->save();     
   
}

public function   purchaseZaprosFinit($id, $relizeVal)
{
   $record = PurchaseZakaz::FindOne($id);
   if (empty($record)) return false;

   $record->relizeValue = $relizeVal;
   $record->isActive  = 0;
   $record->save();
}
/***********************************************************************/


public function   zaprosSwitchInList ($variantId)
{
   $record = PurchaseVariant::FindOne($variantId);
   if (empty($record)) return false;

   if ($record->isInList == 1) $record->isInList =0;
   else $record->isInList =1;   
   $record->save();
}


public function   getZaprosVariantInList ()
{
$list=array();    
    
    $query  = new Query();
    $query->select ([
            '{{%purchase_variant}}.id',
            'refSchet',
            'refOrg',            
            'lastSchetDate',
            'curentValue',
            'isRequestSend',
            'requestDate',
            'refWare',
            '{{%orglist}}.title as orgTitle',
            '{{%warehouse}}.title as wareTitle',
            'isInList'
            ])
            ->from("{{%purchase_variant}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%purchase_variant}}.refOrg")            
            ->leftJoin("{{%warehouse}}","{{%warehouse}}.id = {{%purchase_variant}}.refWare")            
            ->where ('{{%purchase_variant}}.refPurchaseZakaz ='.$this->id )            
            ->distinct();
            
      $query->andWhere("isInList = 1");      
      $list['data'] = $query->createCommand()->queryAll();
      $list['wareTitle'] = Yii::$app->db->createCommand("Select DISTINCT {{%warehouse}}.title, {{%purchase_variant}}.refWare 
      FROM {{%purchase_variant}}, {{%warehouse}} WHERE {{%purchase_variant}}.refWare =  {{%warehouse}}.id 
      AND {{%purchase_variant}}.refPurchaseZakaz =".$this->id." AND isInList = 1
      order by {{%warehouse}}.title")->queryAll();

      $list['orgTitle'] = Yii::$app->db->createCommand("Select DISTINCT {{%orglist}}.title,  {{%purchase_variant}}.refOrg
      FROM {{%purchase_variant}}, {{%orglist}} WHERE {{%purchase_variant}}.refOrg =  {{%orglist}}.id 
      AND {{%purchase_variant}}.refPurchaseZakaz =".$this->id." AND isInList = 1
      order by {{%orglist}}.title")->queryAll();
      
    return $list;  
      
}



/***********************************************************************/



public function   prepareZaprosOdobrenie()
{
   if(empty($this->id)) return false; 
   $record = PurchaseZakaz::FindOne($this->id);
   if (empty($record)) return false;
   
    $this->wareTitle=$record->wareTitle;
    $this->zaprosType=$record->zaprosWareType;
    $this->zaprosCategory=$record->zaprosCatRef;
    $this->pcntVal1=$record->pcntVal1;
    $this->pcntVal2=$record->pcntVal2;
    $this->pcntVal3=$record->pcntVal3;
    $this->pcntVal4=$record->pcntVal4;
   return $record;
}


public function   saveZaprosOdobrenie()
{
   if(empty($this->id)) return false; 
   $record = PurchaseZakaz::FindOne($this->id);
   if (empty($record)) return false;
   
    $record->wareTitle = $this->wareTitle;
    $record->zaprosWareType = $this->zaprosType;
    $record->zaprosCatRef = $this->zaprosCategory;
    $record->pcntVal1 = $this->pcntVal1;
    $record->pcntVal2 = $this->pcntVal2;
    $record->pcntVal3 = $this->pcntVal3;
    $record->pcntVal4 = $this->pcntVal4;
    $record->status=1; //одобрен
    $record->save();
}



public function   chngCategoryType ($zaprosId, $categoryId, $zaprosWareType, $wareTitle)
{

    $categoryRecord =PriceCategoryList::FindOne($categoryId);    
    if (empty($categoryRecord)) return false;    
    $record = PurchaseZakaz::FindOne($zaprosId);
    if (empty($record)) return false;
    
    if (!empty($wareTitle)) $this->wareTitle = $wareTitle;
    $record->zaprosCatRef = $categoryId;
    $record->zaprosWareType = $zaprosWareType;
    $record->pcntVal1 = $categoryRecord ->pcntVal1;
    $record->pcntVal2 = $categoryRecord ->pcntVal2;
    $record->pcntVal3 = $categoryRecord ->pcntVal3;
    $record->pcntVal4 = $categoryRecord ->pcntVal4;
    
    
    
    $record->save();
    
}

public function   getCategoryType ()
{
    $list = Yii::$app->db->createCommand("Select id, categoryName FROM {{%price_category}}")->queryAll();
    
    return ArrayHelper::map($list, 'id', 'categoryName');
}

public function   getEdList()
{
    $list = Yii::$app->db->createCommand("Select id, edTitle FROM {{%ware_ed}}")->queryAll();
    
    return ArrayHelper::map($list, 'id', 'edTitle');
}

public function  getDefEd()
{
    $def= Yii::$app->db->createCommand("Select Min(id) FROM {{%ware_ed}} where isDef=1")->queryScalar();
    if (empty($def))$def= Yii::$app->db->createCommand("Select Min(id) FROM {{%ware_ed}} ")->queryScalar();
    return $def;
}




/***********************************************************************/
/******* Удалить привязку запроса к закупке                     ********/
/***********************************************************************/

public function   purchaseZakazRmLink($requestId)
{
    
   $requestRecord =   RequestGoodContent::FindOne($requestId);
   if (empty($requestRecord)) return -1;
   
   $requestRecord->refPurchaseZakaz = 0;
   $requestRecord->isInWork  = 0;
   $requestRecord->save();     
   
}
/***********************************************************************/
/******* Создать закупку из запроса менеджера продаж ******************/
/***********************************************************************/


public function  purchaseCreateFromClientZakaz($zakazContentId)
{
   //из содержимого заявки   
  $zakazContentRecord =   ZakazContent::FindOne($zakazContentId);
  if (empty($zakazContentRecord)) return -1;
  
  $record = new  PurchaseZakaz();           
  $record->wareTitle    = $zakazContentRecord->good;
  $record->wareEd       = $zakazContentRecord->ed;
  $record->wareCount    = $zakazContentRecord->count;
  $record->refZakaz     = $zakazContentRecord->refZakaz;
  $record->refZakazContent= $zakazContentRecord->id;
  $record->refWarehouse =  $zakazContentRecord->warehouseRef;
  $record->zakazNote = $zakazContentRecord->spec;
  $record->zaprosType = 1;
  $record->save();    
     
  /*Добавим в общий список*/       
   if ($record->refWarehouse > 0)
   {       
    $this->addWareInZakaz($record->id,$record->refWarehouse);   
   }
   
   return $record->id;
}

/***/


public function  rmFromRequest($requestId)
{
    $requestRecord =   RequestGoodContent::FindOne($requestId);
    if(empty($requestRecord)) return;
    $requestRecord->delete();
}

public function  purchaseCreateFromRequest($requestId)
{
   //return -1;/* Obsoleted Vv 15.12.2018 - механизм у менеджера продаж отключаем */
    
   $requestRecord =   RequestGoodContent::FindOne($requestId);
   if (empty($requestRecord)) return -1;

    
$strSql = "SELECT good, count, refZakaz, refWare, goodEd from {{%request_good_content}}, {{%request_good}}
           where {{%request_good_content}}.refRequest = {{%request_good}}.id and {{%request_good_content}}.id = :requestId LIMIT 1";

     $list = Yii::$app->db->createCommand($strSql,[':requestId' => $requestId,] )->queryAll();                    
     if (count($list)==0) return;

  $record = new  PurchaseZakaz();           
  $record->wareTitle    = $list[0]['good'];
  $record->wareEd       = $list[0]['goodEd'];
  $record->wareCount    = $list[0]['count'];
  $record->refZakaz     = $list[0]['refZakaz'];
  $record->refWarehouse =  $list[0]['refWare'];
  $record->save();    
     
   
   $requestRecord->refPurchaseZakaz = $record->id;
   $requestRecord->isInWork  = 1;
   $requestRecord->save();  
   
     /*Добавим в общий список*/       
   if ($record->refWarehouse > 0)
   {       
    $this->addWareInZakaz($record->id,$record->refWarehouse);   
   }
   
   return $record->id;
}

/***/

/***********************************************************************/
/******* Создать закупку по ссылке на содержимое счета клиента ********/
/****** Вызов реализован из формы заявка на отгрузку (supply-request) **/
/***********************************************************************/
/*

*/
public function  createZaprosFromZakazGood($zakazref)
{
   $zakazRecord =   TblSchetContent::FindOne($zakazref);
   if (empty($zakazRecord)) return -1;

  $record = new  PurchaseZakaz();           
  $record->wareTitle        = $zakazRecord->wareTitle ;  
  $record->wareEd           = $zakazRecord->wareEd;
  $record->wareCount        = $zakazRecord->wareCount;    
  $record->refSchet         = $zakazRecord->refSchet;
  $record->refSchetContent  = $zakazref;  
  $record->refWarehouse     = $zakazRecord->warehouseRef;
  $record->save();    
   
   /*Добавим товар в список*/       
   if ($record->refWarehouse > 0)
   {       
    $this->addWareInZakaz($record->id,$record->refWarehouse);   
   }
   
   return $record->id;
}
/*
список вариантов закупки товара
*/
public function addWareInZakaz($id,$wareId)
{
   
    
   $scladRecord = ScladList::FindOne($wareId);
   if (empty($scladRecord)) {echo " noWare ";  return; }
   if ($id == 0) return;
   
   $recordZakaz = PurchaseZakaz::FindOne($id);           
   if(!empty($recordZakaz)) $wareCount =$recordZakaz->wareCount;
   //echo  $id." ". $wareCount."";
   
    $record = new  PurchaseZakazWare();
 
    $record->refPurchaseZakaz = $id;
    $record->wareTitle = $scladRecord->title;
    $record->wareEd    = $scladRecord->ed;
    $record->refWarehouse =  $scladRecord->id;
    $record->wareCount = $wareCount;    
    $record->save();    
    
    /*Фикс неопознанных организаций */
    $strSql="SELECT {{%supplier_schet_header}}.orgINN, {{%supplier_schet_header}}.orgKPP, 
    {{%supplier_schet_header}}.orgTitle,  {{%supplier_schet_header}}.id
    from {{%supplier_schet_header}},{{%supplier_schet_content}}
    where  
    {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef 
    AND  wareRef = ".$wareId." AND {{%supplier_schet_header}}.refOrg = 0";
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll();
    
  //  print_r($list);
    
    for ($i=0; $i < count($list); $i++)
    {
        //заведем их как поставщиков
        $orRecord= new OrgList();        
        $orRecord->title    = $list[$i]['orgTitle'];
        $orRecord->orgFullTitle    = $list[$i]['orgTitle'];
        $orRecord->schetINN = $list[$i]['orgINN']; 
        $orRecord->orgKPP   = $list[$i]['orgKPP'];
        $orRecord->contragentType |= 0x01;
        $orRecord->save();
        
        
        $strSqlUp = "UPDATE {{%supplier_schet_header}} SET refOrg = :refOrg 
        WHERE {{%supplier_schet_header}}.orgINN =:orgINN AND {{%supplier_schet_header}}.orgKPP =:orgKPP and refOrg = 0";
                
        Yii::$app->db->createCommand($strSqlUp, [
        ':refOrg' => $orRecord->id,
        ':orgINN' => $list[$i]['orgINN'],
        ':orgKPP' => $list[$i]['orgKPP'],
        ])->execute();

    }
        
    $strSql= "INSERT INTO {{%purchase_variant}} (refSchet, refOrg, refPurchaseZakaz, lastSchetDate, refWare, curentValue) 
    select DISTINCT b.id, b.reforg, :refPurchaseZakaz, b.schetDate, wareRef,  goodSumm/goodCount  
    from( SELECT  reforg, max({{%supplier_schet_header}}.schetDate) as md from  {{%supplier_schet_header}},{{%supplier_schet_content}}  
    where  {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef AND  wareRef = :wareRef group by reforg ) as a
    join {{%supplier_schet_header}} as b on a.refOrg=b.refOrg and a.md = b.schetDate
    join {{%supplier_schet_content}} as c on b.id = c.schetRef where wareRef = :wareRef  ORDER BY b.schetDate DESC";
    
    
//    echo Yii::$app->db->createCommand($strSql, [':wareRef' => $wareId,':refPurchaseZakaz' => $id,])->getRawSql();
    
	Yii::$app->db->createCommand($strSql, [':wareRef' => $wareId,':refPurchaseZakaz' => $id,])->execute();
    
    return;

    
}

public function addWareFromSchet($id, $supplierWareId)
{
  $suppRecord = SupplierSchetContentList::FindOne($supplierWareId);
  if (empty($supplierWareId)) return;
  
  if ($suppRecord->wareRef == 0)
  {
    //добавим товар
    $scladRecord = new ScladList();
    $scladRecord -> title = $suppRecord ->goodTitle;
    $scladRecord -> ed    = $suppRecord ->goodEd;
    $scladRecord -> isValid = 0;
    $scladRecord -> save();
    $suppRecord->wareRef = $scladRecord -> id;
    $suppRecord->save();
    
  }
   
    $strSql = "update {{%supplier_schet_content}}, {{%warehouse}} set {{%supplier_schet_content}}.wareRef = {{%warehouse}}.id 
    where {{%supplier_schet_content}}.goodTitle = {{%warehouse}}.title and {{%supplier_schet_content}}.wareRef = 0"; 
    Yii::$app->db->createCommand($strSql)->execute();
      
     
   
   $this->addWareInZakaz($id, $suppRecord->wareRef);
    
}


public function rmWareFromZakaz($wareId)
{
    $strSql= "DELETE FROM {{%purchase_zakaz_ware}} where id= :wareId";    
	Yii::$app->db->createCommand($strSql, [':wareId' =>$wareId])->execute();    
}

public function setWareZakazCount($wareId,$wareCount)
{

   $record =  PurchaseZakazWare::FindOne($wareId);
   if (empty($record)) return; 
   $record->wareCount = $wareCount;
   $record->save();    
}

/*****************/

  public function addOrgToZakaz($id, $orgRef, $orgTitle)
  {
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    if ($orgRef == -2)
    {
        $orRecord= new OrgList();        
        $orRecord->title=$orgTitle;
        $orRecord->contragentType |= 0x01;
        $orRecord->save();
        $orgRef =$orRecord->id;
    }
    
    $varRecord = new PurchaseVariant();    
    if (empty($varRecord)) return;
    
    $varRecord->refPurchaseZakaz = $id;   
    $varRecord->refOrg = $orgRef;
    $varRecord->refWare = $record->refWarehouse;
    $varRecord->save();      

      
  }
/*****************/
public function purchaseZakazDel($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->isActive = 0;
    $record->save();
    
}
/*****************/
public function setVariantActive($id,$variantId)
{
    /*Сбросим*/
    $strSql= "UPDATE{{%purchase_variant}} SET isActiveVariant =0  where refPurchaseZakaz = :refPurchaseZakaz";    
	Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $id,])->execute();

    /*Установим*/
    $strSql= "UPDATE{{%purchase_variant}} SET isActiveVariant =1  where refPurchaseZakaz = :refPurchaseZakaz and id = :variantId";    
	Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $id,':variantId' =>$variantId])->execute();
    
    return;
}   
/*****************/

public function setPurchaseZakazSchet($variantId,$schetId)
{
    /*Найдем*/
    
    $record = PurchaseVariant::FindOne($variantId);
    if (empty ($record)) return -1;//нет такого варианта
    
    $strSql= "SELECT goodCount, goodSumm, schetDate FROM {{%supplier_schet_content}} where
    goodCount > 0 and  goodSumm > 0 AND schetRef = :schetRef AND wareRef = :wareRef LIMIT 1";    
	$list=Yii::$app->db->createCommand($strSql, [':schetRef' => $schetId, ':wareRef' => $record->refWare ])->queryAll();
    
    /*Установим*/
    $record->curentValue= round($list[0]['goodSumm']/$list[0]['goodCount'],2);    
    $record->lastSchetDate = $list[0]['schetDate'];
    $record->refSchet   = $schetId;
    $record->save();
    return 0;
}

/*****************/
//Направить на  Согласование
public function setPurchaseZakazPermit($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 1;
    $record->save();
}

//Отозвать с согласования
public function setPurchaseZakazRecall($id){

    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 0;
    $record->save();
    
    /*Сбросим согласование*/
    $strSql= "UPDATE{{%purchase_variant}} SET isActiveVariant =0  where refPurchaseZakaz = :refPurchaseZakaz";    
	Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $id,])->execute();
}

//Согласовать
public function setPurchaseZakazPermited($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 2;
    $record->save();
}
//Отменить Согласование
public function setPurchaseZakazUnPermited($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 1;
    $record->save();
}
/********************************/
// В работу
public function setPurchaseZakazInWork($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 1;
    $record->zaprosType = 1;
    $record->save();
}
//Убрать
public function setPurchaseZakazNoWork($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 0;
    $record->zaprosType = 1;
    $record->save();
}

// Выполнено
public function setPurchaseZakazWorkDone($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 2;
    $record->zaprosType = 1;
    $record->save();
}
//Невыполнено
public function setPurchaseZakazWorkUnDone($id)
{
    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 1;
    $record->zaprosType = 1;
    $record->save();
}

//Отказать
public function setPurchaseZakazDeny($id){

    $record = PurchaseZakaz::FindOne($id);
    if (empty($record)) return;
    $record->status = 4;
    $record->save();
    /*Сбросим согласование*/
    $strSql= "UPDATE{{%purchase_variant}} SET isActiveVariant =0  where refPurchaseZakaz = :refPurchaseZakaz";    
	Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $id,])->execute();
    
}
//Удалить вариант из запроса
public function purchaseZakazRmVariant($variantid){

    $record = PurchaseVariant::FindOne($variantid);
    if (empty($record)) return; 
    $record->delete();
}

//сохранить вариант
public function savePurchaseRequest($id, $editVariantId){
    $id = intval($id);
    $editVariantId= intval($editVariantId);
    
    $ret= [
    'res' => false,
    'purchaseId' => $id, 
    'variantId' => $editVariantId,
    ];
    
    
    $record = PurchaseVariant::FindOne($editVariantId);
    if (empty ($record)) return $ret;//нет такого варианта
    $record->isRequestSend = 1;
    $record->save();

    $ret['res']=true;
    return $ret;
   // $record->refContact = $contact->id;
   // $record->requestDate = $contact->contactDate;
         
   /* $curUser=Yii::$app->user->identity;
    $contact= new ContactList ();
    $contact->ref_org = $record->refOrg;
    $contact->note = $editRequestNote;
    $contact->ref_user= $curUser->id;
    $contact->contactDate = date('Y-m-d', strtotime($editRequestDate));
    $contact->eventType = 201;
    $contact->save();*/
    
}
/*****************/
public function preparePurchaseZakaz()
{

 if ($this->id == 0) return false;
    
    $record = PurchaseZakaz::FindOne($this->id);
    if (empty($record)) return  false;
    $this->goodTitle    = $record->wareTitle ;
    $this->goodEd       = $record->wareEd   ;
    $this->refWarehouse = $record->refWarehouse;
    $this->zakazNote    = $record->zakazNote ;
    $this->goodCount    = $record->wareCount;
    $this->isActive     = $record->isActive;
    
    $this->status       = $record->status;    
    $strSql= "SELECT id from {{%purchase_variant}} where refPurchaseZakaz = :refPurchaseZakaz AND isActiveVariant = 1 LIMIT 1";    
    $this->selVariant   = Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $this->id])->queryScalar();
    
    if (empty($this->selVariant)) $this->selVariant = 0; //если NULL
    else
    {        
      if ($record->zaprosType == 1)
      {
          /*Для запроса цены*/
       // if($record->status != 2)
        {
          /*цена есть метим как выполнено*/
          $record->status =2;
          $record->save();
        }
        $this->status  = 2;    
      }
    }
    
    
    
    
$this->showList = [
        'permit' => 0, //согласовать
        'change' => 0, // изменить запрос 
        'recall'  => 0, // отозвать
        'isAgreed'=> 0, // согласовано
        ];
//Получилось громоздко - переделать Vv!!!!!
$this->informText="Запрос в работе";
if ( $record-> zaprosType == 1)
{
$this->informText="Ожидает одобрения";    
        $this->showList['change'] = 1;
        $this->showList['permit'] = 1;   

switch ($this->status) 
{
   case  1:
        $this->informText = "Запрос направлен в работу";
   break;

   case  2:
        $this->informText = "Запрос выполнен";        
   break;

   case  4:
        $this->informText ="Запрос возвращен на доработку";
   break;
    
   case  8:
        $this->informText = "Запрос завершен";
  break;
 
   default:

   break;   
}
    
    
}
else
{
switch ($this->status) 
{
   case  1:
        $this->informText = "Запрос отправлен на согласование";
        $this->showList['recall'] = 1;
        $this->showList['isAgreed'] = 1;
   break;

   case  2:
        $this->informText = "Запрос согласован";
        $this->showList['isAgreed'] = 2;
        $this->showList['recall'] = 1;
   break;

   case  4:
        $this->informText ="Запрос возвращен на доработку";
        $this->showList['change'] = 1;
        $this->showList['permit'] = 1;
   break;
    
   case  8:
        $this->informText = "Запрос включен в активную закупку";
        $this->showList['isAgreed'] = 3;
  break;
 
   default:
        $this->showList['change'] = 1;
        $this->showList['permit'] = 1;   
   break;   
}
}

if ($record->isActive == 0) $this->informText  = "Работа с запросом завершена!";

if ($this->selVariant == 0)
{
    $this->showList['permit'] =  0;
    $this->showList['permitInfo'] = "Выберите поставщика";
}
    
 return $record;    
}

/****************************/
/****************************/
  public function setSchet($id, $schetId, $schetType)
  {
   $id = intval($id);   
   $schetId = intval($schetId);   
   $schetType  = intval($schetType);  

    $strSql= "SELECT sum(goodSumm) FROM {{%supplier_schet_content}}  
    where schetRef = :schetRef"; 
    $schetSum=Yii::$app->db->createCommand($strSql, [':schetRef' => $schetId])->queryScalar();        
      
   $lnkRecord = TblPurchSchetLnk::findOne([
   'schetRef' => $schetId,
   'purchRef' => $id,
   ]);
   if (empty($lnkRecord)) $lnkRecord = new TblPurchSchetLnk();
   if (empty($lnkRecord)) return;
   $lnkRecord->purchRef = $id;
   $lnkRecord->schetRef = $schetId;
   $lnkRecord->purchRole = $schetType;
   $lnkRecord->purchSum = $schetSum;
   $lnkRecord->save();
   if ($schetType == 0)
   {
    $record = Purchase::findOne($id);
    if (empty($record)) return;
    $record->supplierShetRef = $schetId;    
    $record->save();      
   }
  }

public function unlinkSchet($lnkid)
{
   $lnkid = intval($lnkid);   
   
      
   $lnkRecord = TblPurchSchetLnk::findOne( $lnkid   );

   if (empty($lnkRecord )) return;
   
   if ($lnkRecord ->purchRole == 0)
   {
    $record = Purchase::findOne($lnkRecord ->purchRef);
    if (empty($record)) return;
    if ($record->supplierShetRef == $lnkRecord ->schetRef){
    $record->supplierShetRef = 0;    
    $record->save();      }
   }       
   $lnkRecord ->delete();   
}
 public function saveLnkAjax  ()
    {
        
   $this->recordId = intval($this->recordId);
   $this->dataId   = intval($this->dataId);      
        
    $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId, 
             'reload'  => false
           ];   
                       
    switch ($this->dataType)
    {


      case 'ErpDocControl':
      $res['act'] ='ErpDocControl';
      $res['reload'] = true;
      $this->dataVal = intval($this->dataVal);
        if ($this->dataVal > 0)
        $lnkRecord = TblPurchControlLnk::findOne($this->dataVal);
        else {
        $lnkRecord = TblPurchControlLnk::findOne([
         'purchRef' => intval($this->recordId),
         'docRef' => intval($this->dataId)
        ]);
        }
      if (empty($lnkRecord)) $lnkRecord = new TblPurchControlLnk();
      if (empty($lnkRecord)) return $res;
        $lnkRecord->purchRef = intval($this->recordId);
        $lnkRecord->docRef = intval($this->dataId);
        $strSql= "SELECT docSum FROM {{%documents}}  where id = :controlRef"; 
        $lnkRecord->purchSum = Yii::$app->db->createCommand($strSql, [':controlRef' =>$lnkRecord->docRef])->queryScalar();        
        $lnkRecord->purchRole = 0;
        $lnkRecord->save();         
      
      break;            
      case 'purchaseControl':
      $res['act'] ='purchaseControl';
      $res['reload'] = true;
      $this->dataVal = intval($this->dataVal);
        if ($this->dataVal > 0)
        $lnkRecord = TblPurchControlLnk::findOne($this->dataVal);
        else {
        $lnkRecord = TblPurchControlLnk::findOne([
         'purchRef' => intval($this->recordId),
         'controlRef' => intval($this->dataId)
        ]);
        }
        if (empty($lnkRecord)) $lnkRecord = new TblPurchControlLnk();
        if (empty($lnkRecord)) return $res;
        $lnkRecord->purchRef = intval($this->recordId);
        $lnkRecord->controlRef = intval($this->dataId);

        $lnkRecord = TblPurchControlLnk::findOne([
         'purchRef' => intval($this->recordId),
         'controlRef' => intval($this->dataId)
        ]);

        $controRec= TblControlPurchContent::findOne(intval($this->dataId));
        if(!empty($controRec))
        {
        $strSql= "SELECT sum(purchSum) FROM{{%control_purch_content}}
                    where ref1C = :ref1C AND purchDate=:purchDate";
        $lnkRecord->purchSum = Yii::$app->db->createCommand($strSql, [
                     ':ref1C' => $controRec->ref1C,
                     ':purchDate' => $controRec->purchDate,
                    ])->queryScalar();
        }
        $lnkRecord->purchRole = intval($this->dataVal);
        $lnkRecord->save();         
      break;            

      case 'unlinkControl':
      $res['act'] ='unlinkControl';
      $res['reload'] = true;
        $lnkRecord = TblPurchControlLnk::findOne(intval($this->dataId));
        if (empty($lnkRecord)) return $res;
        $lnkRecord->delete();         
      break;            

      case 'purchControlSum':
      $res['act'] ='link';
        $lnkRecord = TblPurchControlLnk::findOne(
            $this->dataId
        );
          if(empty($lnkRecord)) return $res;
            $lnkRecord -> purchSum = floatval(str_replace(',', '.',$this->dataVal));
            $lnkRecord->save();            
      break;            


      case 'purchControlRole':
      $res['act'] ='link';
        $lnkRecord = TblPurchControlLnk::findOne(
            $this->dataId
        );
          if(empty($lnkRecord)) return $res;
            $lnkRecord -> purchRole = intval($this->dataVal);
            $lnkRecord->save();            
      break;               
/*************/
      case 'switchWareControlAddition':
      $res['reload'] = true;
        $wareRecord = TblSupplierWares::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
            if ( $wareRecord ->isAdditionWare == 0)  $wareRecord ->isAdditionWare = 1;
                                               else  $wareRecord ->isAdditionWare = 0;            
            $wareRecord->save();            
      break;            

      case 'wareEdControlValueRef':
      $res['reload'] = false;
        $wareRecord = TblSupplierWares::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareEdValueRef = intval($this->dataVal);
            $wareRecord->save();            
      break;            

      case 'wareCostControlValue':
      $res['reload'] = false;
        $wareRecord = TblSupplierWares::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareCostValue = floatval($this->dataVal);
            $wareRecord->save();            
      break;            
      
      case 'wareCostControlCount':
      $res['reload'] = false;
        $wareRecord = TblSupplierWares::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareCostCount = floatval($this->dataVal);
            $wareRecord->save();            
      break;            

/***********/
      case 'ErpDoc':
      $res['act'] ='ErpDoc';
      $res['reload'] = true;
        $docRecord = TblDocuments::findOne(intval($this->dataId));
        if(empty($docRecord)) return $res;
        $docRecord ->purchaseErpRef = intval($this->recordId);
        $docRecord ->save();
        if (!empty($docRecord ->refSupplierSchet)){
        $lnkRecord = TblPurchSchetLnk::findOne([
         'purchRef' => $docRecord ->purchaseErpRef,
         'schetRef' => $docRecord ->refSupplierSchet
        ]);
        if (empty($lnkRecord)) $lnkRecord = new TblPurchSchetLnk();
        $lnkRecord->purchRef = $docRecord ->purchaseErpRef;
        $lnkRecord->schetRef = $docRecord ->refSupplierSchet;
        $strSql= "SELECT sum(goodSumm) FROM {{%supplier_schet_content}}  where schetRef = :schetRef"; 
        $lnkRecord->purchSum = Yii::$app->db->createCommand($strSql, [':schetRef' => $docRecord ->refSupplierSchet])->queryScalar();        
        $lnkRecord->purchRole = intval($this->dataVal);
        $lnkRecord->save(); }          
      break;            

      case 'supplierSchet':
      $res['act'] ='supplierSchet';
      $res['reload'] = true;

        $lnkRecord = TblPurchSchetLnk::findOne([
         'purchRef' => intval($this->recordId),
         'schetRef' => intval($this->dataId)
        ]);
        if (empty($lnkRecord)) $lnkRecord = new TblPurchSchetLnk();
        $lnkRecord->purchRef = intval($this->recordId);
        $lnkRecord->schetRef = intval($this->dataId);
        $strSql= "SELECT sum(goodSumm) FROM {{%supplier_schet_content}}  where schetRef = :schetRef"; 
        $lnkRecord->purchSum = Yii::$app->db->createCommand($strSql, [':schetRef' => $lnkRecord->schetRef])->queryScalar();        
        $lnkRecord->purchRole = intval($this->dataVal);
        $lnkRecord->save();            
       
        $docRecord = TblDocuments::findOne([
        'refSupplierSchet' => $lnkRecord->schetRef
        ]);
        if(!empty($docRecord)) {
        $docRecord ->purchaseErpRef = intval($this->recordId);
        $docRecord ->save();
        }                
      break;            

        
        
      case 'purchSum':
      $res['act'] ='link';
        $lnkRecord = TblPurchSchetLnk::findOne(
            $this->dataId
        );
          if(empty($lnkRecord)) return $res;
            $lnkRecord -> purchSum = floatval(str_replace(',', '.',$this->dataVal));
            $lnkRecord->save();            
      break;            

      case 'purchRole':
      $res['act'] ='link';
        $lnkRecord = TblPurchSchetLnk::findOne(
            $this->dataId
        );
          if(empty($lnkRecord)) return $res;
            $lnkRecord -> purchRole = intval($this->dataVal);
            $lnkRecord->save();            
      break;               
      
      
      case 'switchWareAddition':
      $res['reload'] = true;
        $wareRecord = SupplierSchetContentList::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
            if ( $wareRecord ->isAdditionWare == 0)  $wareRecord ->isAdditionWare = 1;
                                               else  $wareRecord ->isAdditionWare = 0;            
            $wareRecord->save();            
      break;            

      case 'wareEdValueRef':
      $res['reload'] = false;
        $wareRecord = SupplierSchetContentList::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareEdValueRef = intval($this->dataVal);
            $wareRecord->save();            
      break;            

      case 'wareCostValue':
      $res['reload'] = false;
        $wareRecord = SupplierSchetContentList::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareCostValue = floatval($this->dataVal);
            $wareRecord->save();            
      break;            
      
      case 'wareCostCount':
      $res['reload'] = false;
        $wareRecord = SupplierSchetContentList::findOne(
            $this->dataId
        );
          if(empty($wareRecord)) return $res;
             $wareRecord -> wareCostCount = floatval($this->dataVal);
            $wareRecord->save();            
      break;            
      
     }
    $res['res'] = true;    
    return $res;
        
  }

public function getRecalcControlCostData ($id)
{
    $id = intval($id);
    $res = [ 'res' => false, 
             'id'  => $id, 
             'deliverSum' => 0,
             'addSumm' => 0,
             'addCost' => 0,
             'wareCount'=> 0,
           ];   
    
    //Считаем сумму, которую требуется размазать и число единиц на которое требуется размазать
    $addSumm=0;
    $wareCount=0;
    
    $this->id= $id;
    $this->getDeliverData();
    $addSumm = $this->deliverSum;
    $res['deliverSum']=$addSumm;
    
    //Счета с накладными расходами      
    $strSql= "SELECT {{%purch_control_lnk}}.purchRole, {{%purch_control_lnk}}.purchSum FROM {{%purch_control_lnk}}  where 
    purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    $N = count($list);
    for ($i=0; $i<$N; $i++)
    {
      if ($list[$i]['purchRole'] != 0 )
      {
      //Это накладные
        $addSumm+=$list[$i]['purchSum'];      
      }    
    }
    
   // print_r($list);
  //  echo Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->getRawSql();                               )
    
    //Теперь по поставкам
    $strSql= "SELECT {{%control_purch_content}}.id, purchCount, wareCostCount, {{%control_purch_content}}.purchSum as goodSumm,
    {{%control_purch_content}}.isAdditionWare, {{%purch_control_lnk}}.purchRole, {{%purch_control_lnk}}.purchSum 
    FROM {{%purch_control_lnk}}, {{%control_purch_content}}  where 
    {{%purch_control_lnk}}.controlRef = {{%control_purch_content}}.id and purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    

    $N = count ($list);
//$this->debug[] =     $list;
    for ($i=0; $i<$N; $i++)
    {
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
      if ( $list[$i]['isAdditionWare'] ==1)
      {
      //Это внутри счета накладные
        $addSumm+=$list[$i]['goodSumm'];            
      }
      else {
      //это товар
      if ($list[$i]['wareCostCount']> 0.00001)$wareCount+=$list[$i]['wareCostCount'];            
                                         else $wareCount+=$list[$i]['purchCount'];            
      }
    }

    $res['wareCount'] = $wareCount;  
    //наценка на единицу товара
    if ($wareCount > 0)    $addCost = $addSumm/$wareCount;
                      else $addCost = 0;

    $res['addCost'] = $addCost;    
    $res['addSumm'] = $addSumm;    
    $res['res'] = true;    
    return $res;
        
        
}

/******************/
public function recalcCostControlValue($id)  
{
    $id = intval($id);
    $res = [ 'res' => false, 
             'id'  => $id, 
             'deliverSum' => 0,
             'addSumm' => 0,
             'addCost' => 0,
           ];   
    
    
    //Считаем сумму, которую требуется размазать и число единиц на которое требуется размазать
    $addSumm=0;
    $wareCount=0;
    
    $this->id= $id;
    $this->getDeliverData();
    $addSumm = $this->deliverSum;
    $res['deliverSum']=$addSumm;
    
    //Счета с накладными расходами      
    $strSql= "SELECT {{%purch_control_lnk}}.purchRole, {{%purch_control_lnk}}.purchSum FROM {{%purch_control_lnk}}  where 
    purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    $N = count($list);
    for ($i=0; $i<$N; $i++)
    {
      if ($list[$i]['purchRole'] != 0 )
      {
      //Это накладные
        $addSumm+=$list[$i]['purchSum'];      
      }    
    }
    
   // print_r($list);
  //  echo Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->getRawSql();                               )
    
    
        //Теперь по поставкам
    $strSql= "SELECT {{%supplier_wares}}.id, wareCount as goodCount, wareCostCount, {{%supplier_wares}}.wareSumm as goodSumm,
    {{%supplier_wares}}.isAdditionWare, {{%purch_control_lnk}}.purchRole, {{%purch_control_lnk}}.purchSum 
    FROM {{%purch_control_lnk}}, {{%supplier_wares}}  where 
    {{%purch_control_lnk}}.controlRef = {{%supplier_wares}}.refHeader and purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();  
    

    $N = count ($list);
    for ($i=0; $i<$N; $i++)
    {
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
      if ( $list[$i]['isAdditionWare'] ==1)
      {
      //Это внутри счета накладные
        $addSumm+=$list[$i]['goodSumm'];            
      }
      else {
      //это товар
      if ($list[$i]['wareCostCount']> 0.00001)$wareCount+=$list[$i]['wareCostCount'];            
                                         else $wareCount+=$list[$i]['goodCount'];            
      }
    }

    //наценка на единицу товара
    if ($wareCount == 0) $addCost = 1;
    else $addCost = $addSumm/$wareCount;
    
    for ($i=0; $i<$N; $i++)
    {
   
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
    if ($list[$i]['isAdditionWare'] ==1)  continue; //Уже учли
      //это товар
   
      
      $record = TblSupplierWares::findOne($list[$i]['id'] );
      if (empty($record)) continue;
      if ($record->wareCostCount > 0.00001) $wareCount=$record->wareCostCount;
      else $wareCount=$record->purchCount;
      $record->wareCostValue = $record->wareSumm + $wareCount*$addCost;
      $record->wareCostAdd = $addCost;
      $record->save();
    }
         

    $res['addCost'] = $addCost;    
    $res['addSumm'] = $addSumm;    
    $res['res'] = true;    
    return $res;
    
       
}
/**/



/*******************/  
public function recalcCostValue($id)  
{
    $id = intval($id);
    $res = [ 'res' => false, 
             'id'  => $id, 
             'deliverSum' => 0,
             'addSumm' => 0,
             'addCost' => 0,
           ];   
    
     //Считаем сумму, которую требуется размазать и число единиц на которое требуется размазать
    $addSumm=0;
    $wareCount=0;
    
    $this->id= $id;
    $this->getDeliverData();
    $addSumm = $this->deliverSum;
    $res['deliverSum']=$addSumm;
    
    //Счета с накладными расходами      
    $strSql= "SELECT {{%purch_schet_lnk}}.purchRole, {{%purch_schet_lnk}}.purchSum FROM {{%purch_schet_lnk}}  where 
    purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    $N = count($list);
    for ($i=0; $i<$N; $i++)
    {
      if ($list[$i]['purchRole'] != 0 )
      {
      //Это накладные
        $addSumm+=$list[$i]['purchSum'];      
      }    
    }
    
   // print_r($list);
  //  echo Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->getRawSql();                               )
    
    //Теперь по счетам
    $strSql= "SELECT {{%supplier_schet_content}}.id, goodCount, wareCostCount, {{%supplier_schet_content}}.goodSumm, {{%supplier_schet_content}}.isAdditionWare, {{%purch_schet_lnk}}.purchRole, {{%purch_schet_lnk}}.purchSum FROM {{%purch_schet_lnk}}, {{%supplier_schet_content}}  where 
    {{%purch_schet_lnk}}.schetRef = {{%supplier_schet_content}}.schetRef and purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    

    $N = count ($list);
    for ($i=0; $i<$N; $i++)
    {
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
      if ( $list[$i]['isAdditionWare'] ==1)
      {
      //Это внутри счета накладные
        $addSumm+=$list[$i]['goodSumm'];            
      }
      else {
      //это товар
      if ($list[$i]['wareCostCount']> 0.00001)$wareCount+=$list[$i]['wareCostCount'];            
                                         else $wareCount+=$list[$i]['goodCount'];            
      }
    }

    //наценка на единицу товара
    if ($wareCount > 0)  $addCost = $addSumm/$wareCount;
                  else   $addCost = 0;
    
    for ($i=0; $i<$N; $i++)
    {
   
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
    if ($list[$i]['isAdditionWare'] ==1)  continue; //Уже учли
      //это товар
   
      
      $record = SupplierSchetContentList::findOne($list[$i]['id'] );
      if (empty($record)) continue;
      if ($record->wareCostCount > 0.00001) $wareCount=$record->wareCostCount;
      else $wareCount=$record->goodCount;
      $record->wareCostValue = $record->goodSumm + $wareCount*$addCost;
      $record->wareCostAdd = $addCost;
      $record->save();
    }

    $res['addCost'] = $addCost;    
    $res['addSumm'] = $addSumm;    
    $res['res'] = true;    
    return $res;
        
}
/**/
public function getRecalcCostData($id)  
{
    $id = intval($id);
    $res = [ 'res' => false, 
             'id'  => $id, 
             'deliverSum' => 0,
             'addSumm' => 0,
             'addCost' => 0,
             'wareCount'=> 0,
           ];   
    
    //Считаем сумму, которую требуется размазать и число единиц на которое требуется размазать
    $addSumm=0;
    $wareCount=0;
    
    $this->id= $id;
    $this->getDeliverData();
    $addSumm = $this->deliverSum;
    $res['deliverSum']=$addSumm;
    
    //Счета с накладными расходами      
    $strSql= "SELECT {{%purch_schet_lnk}}.purchRole, {{%purch_schet_lnk}}.purchSum FROM {{%purch_schet_lnk}}  where 
    purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    $N = count($list);
    for ($i=0; $i<$N; $i++)
    {
      if ($list[$i]['purchRole'] != 0 )
      {
      //Это накладные
        $addSumm+=$list[$i]['purchSum'];      
      }    
    }
    
   // print_r($list);
  //  echo Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->getRawSql();                               )
    
    //Теперь по счетам
    $strSql= "SELECT {{%supplier_schet_content}}.id, goodCount, wareCostCount, {{%supplier_schet_content}}.goodSumm, {{%supplier_schet_content}}.isAdditionWare, {{%purch_schet_lnk}}.purchRole, {{%purch_schet_lnk}}.purchSum FROM {{%purch_schet_lnk}}, {{%supplier_schet_content}}  where 
    {{%purch_schet_lnk}}.schetRef = {{%supplier_schet_content}}.schetRef and purchRef = :purchaseRef "; 
    $list = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->queryAll();                               
    

    $N = count ($list);
    for ($i=0; $i<$N; $i++)
    {
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
      if ( $list[$i]['isAdditionWare'] ==1)
      {
      //Это внутри счета накладные
        $addSumm+=$list[$i]['goodSumm'];            
      }
      else {
      //это товар
      if ($list[$i]['wareCostCount']> 0.00001)$wareCount+=$list[$i]['wareCostCount'];            
                                         else $wareCount+=$list[$i]['goodCount'];            
      }
    }

    $res['wareCount'] = $wareCount;  
    //наценка на единицу товара
    $addCost = $addSumm/$wareCount;
    
/*    for ($i=0; $i<$N; $i++)
    {
   
    if ($list[$i]['purchRole'] != 0 ) continue; //Уже учли
    if ($list[$i]['isAdditionWare'] ==1)  continue; //Уже учли
      //это товар
   
      
      $record = SupplierSchetContentList::findOne($list[$i]['id'] );
      if (empty($record)) continue;
      if ($record->wareCostCount > 0.00001) $wareCount=$record->wareCostCount;
      else $wareCount=$record->goodCount;
      $record->wareCostValue = $record->goodSumm + $wareCount*$addCost;
      $record->wareCostAdd = $addCost;
      $record->save();
    }*/

    $res['addCost'] = $addCost;    
    $res['addSumm'] = $addSumm;    
    $res['res'] = true;    
    return $res;
        
}


/****************************/

  
public function setPurchaseUnreject  ($id)
{
    $record = Purchase::FindOne($id);
    if (empty($record)) return false;
    $record->isRejectPurchase=0;
    $record->save();      

    /*Удалим запись об отказе*/    
    $strSql= "DELETE FROM {{%purchase_etap}}  where purchaseRef = :purchaseRef and stage=4 and etap IN (8,3)"; 

    Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->execute();        

    return true;
}
/*****************/  
  public function setStageSatus($id, $step, $dateValue )
  {
    $stageList=explode("e",$step);    
    if (count($stageList)<2) return -1; //system error
    
    $stage = substr($stageList[0],1);
    $etap = $stageList[1];
    
//echo "<br>S".$stage." <br>E".$etap;    
    
    $record = PurchaseEtap::FindOne([
    'purchaseRef' =>$id,
    'stage' =>$stage,
    'etap' =>$etap,
    ]);  
    if (empty($record)) 
    {
        $record = new PurchaseEtap();    
        if (empty($record)) return -2; //record not found    
        $record->purchaseRef = $id;
        $record->stage = $stage;
        $record->etap =  $etap;
        
    }
    $record->execDate = date("Y-m-d", strtotime($dateValue)); 
    $record->save();

    /*Завершено*/
   if ($stage ==4 AND $etap ==3)       
   {
    $recordP = Purchase::FindOne($id);
    if (empty($recordP)) return -2;  //record not found      
    $recordP->isFinishedPurchase=1;
    $strSql= "UPDATE {{%purchase_zakaz}} SET isActive=0  where purchaseRef = :purchaseRef ";    
    Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->execute();        
    $recordP->save();
   }
   
    /*Отказ*/
   if ($stage ==4 AND $etap ==8)       
   {
    $recordP = Purchase::FindOne($id);
    if (empty($recordP)) return -2; //record not found        
    $recordP->isFinishedPurchase=1;
    $recordP->isRejectPurchase=1;
    $strSql= "UPDATE {{%purchase_zakaz}} SET isActive=0  where purchaseRef = :purchaseRef ";    
    Yii::$app->db->createCommand($strSql, [':purchaseRef' => $id])->execute();        
    $recordP->save();
   }
   
   
   // В реестр платежей все связанное с оплатой
   if ($stage == 2 AND ($etap >=5 ) )       
   {
       
        $recordP = Purchase::FindOne($id);
        if (empty($recordP)) return -2;   //record not found     
        $schetRecord = SupplierSchetHeaderList::findOne($recordP->supplierShetRef);
        if (empty ($schetRecord) ) return -2;        
        
        
        $recordReestr = ReestrOplat::FindOne(['refSchet' => $schetRecord->id ]);        
        if (empty($recordReestr) )  
        {  
            /* Создадим если нет */
            $curUser=Yii::$app->user->identity;
            $recordReestr = new ReestrOplat();        
            if (empty($recordReestr )) return -2;
        
            $strSql="SELECT  SUM(goodSumm) from {{%supplier_schet_content}}
                where schetRef = ".$schetRecord->id ;        
            $recordReestr->formDate =  date('Y-m-d h:i');   
            $recordReestr->orgTitle =  $schetRecord->orgTitle;
            $recordReestr->refOrg   =  $schetRecord->refOrg;
            $recordReestr->summRequest =  Yii::$app->db->createCommand($strSql)->queryScalar();	
            $recordReestr->oplateType = 8;
            $recordReestr->refZakupka = $id;
            $recordReestr->refSchet = $schetRecord->id;
            $recordReestr->save();    
        }
        else
        {
            /*привяжем */
            if ( empty($recordReestr->refZakupka ) )
            {                
                $recordReestr->refZakupka = $id;
                $recordReestr->save();                
            }
            if ($recordReestr->refZakupka  != $id) return -3;
            //- наличие более одной закупки по счету ошибка                
        }
    }

     return 0;   
  }
  
  
    public function printOplateSum  ()
    {
       // Уже оплачено по реестру
       $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%purch_schet_lnk}}
       where {{%supplier_oplata}}.supplierSchetRef = {{%purch_schet_lnk}}.schetRef AND  {{%purch_schet_lnk}}.purchRef =:purchRef ",                  
                 [':purchRef' => $this->id,])->queryScalar();              
        

        
    if (empty ($oplSum)) return "";
     return "Оплачено:".number_format($oplSum,2,'.','&nbsp;');
    }
/*****************/
  public function getGrpGroup()
  {
      $wareModel = new WarehouseForm();
      $listGrp = $wareModel->getGrpGroup();	 
      return $listGrp;
  }

/*****************/

public function getGoodinZakaz ($wareId)
{
  return  Yii::$app->db->createCommand("Select reserved from {{%warehouse}} where id =:wareRef", 
    [':wareRef' => $wareId,])->queryScalar();    
}
/*****************/

public function getGoodInSclad ($wareId)
{
  return  Yii::$app->db->createCommand("Select amount+inTransit from {{%warehouse}} where id =:wareRef ", 
    [':wareRef' => $wareId,])->queryScalar();
}
/*****************/

public function getGoodinMonth ($wareId)
{

  $l = Yii::$app->db->createCommand("Select ifnull(SUM(supplyCount),0) as N, TIMESTAMPDIFF(DAY, MIN(supplyDate), NOW()) as P 
   from {{%supply}}  where wareRef =:wareRef ", 
    [':wareRef' => $wareId,])->queryAll();    
   if ($l[0]['P'] == 0 ) return  0 ; 
  
    return 30*$l[0]['N']/$l[0]['P'];  
}

/*****************/
/*Создадим закупку из запроса(запросов)*/
public function createPurches($srclist)
{
  $varList=explode(",",$srclist);
 
  $record = new Purchase();
  $record->dateCreation = date('Y-m-d');
  $record->save();
  
  
  for ($i=0; $i< count($varList); $i++ )
  {
    if (empty($varList[$i])) continue;
    $zakazRecord= PurchaseZakaz::FindOne($varList[$i]);
    if (empty ($zakazRecord)) continue;    
    if ($record->refOrg ==0 )
    {
      $refOrg = Yii::$app->db->createCommand("Select refOrg from {{%purchase_variant}} where isActiveVariant=1 AND  refPurchaseZakaz =:refPurchaseZakaz", 
     [':refPurchaseZakaz' => $zakazRecord->id,])->queryScalar();      
      if (!empty($refOrg)){ $record->refOrg = $refOrg; $record->save(); }
    }
    $zakazRecord->status = 8; //включен в закупку
    $zakazRecord->purchaseRef = $record->id;
    $zakazRecord->save();    
  }
  
}

/*****************/
/*Присоединим закупку в запрос*/
public function setPurchaseZaprosLink($purchaseId, $refPurchaseZakaz)
{
    $zakazRecord= PurchaseZakaz::FindOne($refPurchaseZakaz);
    if (empty ($zakazRecord)) return false;    
    $zakazRecord->status = 8; //включен в закупку
    $zakazRecord->purchaseRef = $purchaseId;
    $zakazRecord->save();    
    return true;    
}

/*****************/
/*Отсоединим закупку от запроса*/
public function rmPurchaseZaprosLink($purchaseId, $refPurchaseZakaz)
{
    $zakazRecord= PurchaseZakaz::FindOne($refPurchaseZakaz);
echo "here 1<br>";        
    if (empty ($zakazRecord)) return false;    
    
echo "here 2<br>";    
    $zakazRecord->status = 2; //согласован
    $zakazRecord->purchaseRef = 0;
    $zakazRecord->save();    
    return true;    
}

/*****************/
/*****************/    
/***********************************************/ 
public function prepareVariantsData($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%purchase_variant}}.id',
            'refSchet',
            'refOrg',            
            'lastSchetDate',
            'curentValue',
            'isRequestSend',
            'requestDate',
            '{{%orglist}}.title as orgTitle',
            'isActiveVariant',
            '{{%warehouse}}.title as wareTitle',
            'isInList'
            ])
            ->from("{{%purchase_variant}}")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%purchase_variant}}.refOrg")            
            ->leftJoin("{{%warehouse}}","{{%warehouse}}.id = {{%purchase_variant}}.refWare")            
            ->where ('{{%purchase_variant}}.refPurchaseZakaz ='.$this->id )
            ->distinct();
            			            
                                    

     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);
   } 


  
 public function getVariantsProvider($params)
   {
    
    $this->prepareVariantsData($params);    
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id,',
            'refOrg',            
            'lastSchetDate',
            'curentValue',
            'isRequestSend',
            'requestDate',            
            'orgTitle',
            'isActiveVariant',
            'wareTitle'
            ],
            'defaultOrder' => [	'isActiveVariant'=> SORT_DESC, 'lastSchetDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/*****************/ 
/*****************/ 

public function getOrgTitleList()
{
    
   $strSql = "SELECT DISTINCT title from {{%purchase_variant}}, {{%orglist}} where {{%orglist}}.id = {{%purchase_variant}}.refOrg AND {{%purchase_variant}}.isActiveVariant = 1 "; 
   
   $list = Yii::$app->db->createCommand($strSql)->queryColumn();  
   array_unshift ($list,"Все");
   
   return  $list; 
    
   
}
public function getWareTitleList()
{
    
   $strSql = "Select DISTINCT wareTitle FROM {{%purchase_zakaz}} ORDER BY wareTitle "; 
   
   $list = Yii::$app->db->createCommand($strSql)->queryColumn();  
   array_unshift ($list,"Все");
   
   return  $list;
}


public function preparePurchaseZakazListData($params)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'wareTitle',            
            'wareEd',
            'wareCount',
            'zakazDate',
            'zakazNote',
            'status',
            'currentValue',
            'a.title as orgTitle',
            'a.curentValue',
            'refZakaz'
            ])
            ->from("{{%purchase_zakaz}}")            
            ->leftJoin("(SELECT curentValue, refOrg, title, refPurchaseZakaz from {{%purchase_variant}}, {{%orglist}} where {{%orglist}}.id = {{%purchase_variant}}.refOrg AND {{%purchase_variant}}.isActiveVariant = 1) as a",
            "a.refPurchaseZakaz = {{%purchase_zakaz}}.id")                                    
            ->where ('isActive = 1')
            ->distinct();
            			   

     
                           
     if (($this->load($params) && $this->validate())) {

        if (!empty ($this->orgTitle))
        {
            $orgTitleList= $this->getOrgTitleList();
            $query->andFilterWhere(['like', 'a.title', $orgTitleList[$this->orgTitle]]);
        }
        
        
        
        if (!empty ($this->wareTitle))
        {
           $wareTitleList= $this->getWareTitleList();
        $query->andFilterWhere(['like', 'wareTitle', $wareTitleList[$this->wareTitle]]);
        }
     }
     
     switch ($this->mode) 
     {
         case 2:
            /*в работе или на доработке*/
           // $query->andWhere("(status=0)");         
         break;
         
         case 3:
            /*на согласовании*/
            $query->andWhere("status=1");                  
         break;
         
         case 4:
             /*Согласован*/
            $query->andWhere("status=2");                  
         break;
         
         case 5:
             /*В закупке*/
            $query->andWhere("status=8");                  
         break;
         
         
     }
     
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);
   } 


 /*******/ 
 public function getPurchaseZakazListProvider($params)
   {
    
    $this->preparePurchaseZakazListData($params);    
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id',
            'wareTitle',            
            'wareEd',
            'wareCount',
            'zakazDate',
            'zakazNote',
            'status',
            'currentValue',
            ],
            'defaultOrder' => [	'zakazDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
 
/**/

public function printPurchaseZakazList($provider)
{ 
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $this,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'zakazDate',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                $list =  Yii::$app->db->createCommand(' SELECT userFIO, {{%zakaz}}.id, formDate from {{%zakaz}}, {{%user}}
                 where {{%zakaz}}.ref_user={{%user}}.id and  {{%zakaz}}.id = :ref_zakaz', [':ref_zakaz' => $model['refZakaz'], ])->queryAll(); 
                
                $ret = "<nobr><a href='#' onclick='openWin(\"\store/purchase-zakaz&noframe=1&id=".$model['id']."\",\"storeWin\");'>".$model['id']." от ".date('d.m.Y', strtotime($model['zakazDate']))."</a></nobr>";     
                if (count($list) > 0)
                {
                $ret.="<br><nobr>".$list[0]['id']." от ".date('d.m.Y', strtotime($list[0]['formDate']))."</nobr>";         
                $ret.="<br><nobr>".$list[0]['userFIO']."</nobr>";             
                }
                  return $ret;
                }
            ],		
            
            
			[
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'filter' => $this->getWareTitleList(),
                'value' => function ($model, $key, $index, $column) {
                    return "<div style='font-size:12px;'>".$model['wareTitle']."</div>";
                }
            ],		
            
            [
                'attribute' => 'wareEd',
				'label'     => 'Ед.изм.',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		


            [
                'attribute' => 'status',
				'label'     => 'Статус',
                'format' => 'raw',
                //'options' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {
                    
                    $retVal="N/A";
                    switch ($model['status'])
                    {
                        
                        case 0: 
                            $retVal ="<div class ='gridcell' style=''>В работе</div>";
                            break;
                        case 1: 
                            $retVal ="<div class ='gridcell' style='background:Yellow'>На согласов.</div>";
                            break;
                        case 2: 
                            $retVal ="<div class ='gridcell' style=''><b>Согласован</b></div>";
                            break;
                        case 4: 
                            $retVal ="<div class ='gridcell' style='background:DarkOrange;'>На доработке</div>";
                            break;
                        case 8: 
                            $retVal ="<div class ='gridcell' style='background:DarkGreen; color:white;'>В закупке</div>";
                            break;
                    }
                    
                    return "".$retVal."</div>"; 
                 }                
			],

            [
                'attribute' => 'Note',
				'label'     => 'Коментарий',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return mb_substr($model['zakazNote'],0,32,'UTF-8');
                }
            ],		

            [
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
                'filter' => $this->getOrgTitleList(),
            ],		

            [
                'attribute' => 'curentValue',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return number_format($model['curentValue'],2,".","&nbsp;");
                }
   
            ],		
            
        	[	
                'attribute' => 'Выбрать',
				'label'     => 'Выбрать',
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                  if ($model['status'] >= 4) return "&nbsp;"; 
                  if ($model['status'] != 2) return "&nbsp;"; 
                  
                    $id = $model['id'];
                    $script="<script>idList.push('".$id."');</script>";	
                 return	"<input type=checkbox id='".$id."'>".$script;
					
				}				
            ],
            
        ],
    ]
	);

}
/*******/
public function prepareWareInZakazProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'wareTitle',            
            'wareEd',
            'wareCount',
            ])
            ->from("{{%purchase_zakaz_ware}}")                       
            ->where ('refPurchaseZakaz = '.$this->id )
            ->distinct();
            			            
     if (($this->load($params) && $this->validate())) {   
     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);
   } 

  
 public function getWareInZakazProvider($params)
   {
    
    $this-> prepareWareInZakazProvider($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id',
            'wareTitle',            
            'wareEd',
            'wareCount',       
            ],
            'defaultOrder' => [	'id' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/**************/    
     public function getGoodListProvider($params)
   {

    $query  = new Query();
    $query->select ("id, grpGood, title as wareTitle, price, ed, a.n")
    -> from("{{%warehouse}}")
    -> leftJoin("(SELECT COUNT(id) as n, wareRef from {{%supplier_schet_content}} group BY wareRef) as a", "a.wareRef = {{%warehouse}}.id")
    ;

    $countquery  = new Query();
    $countquery->select ("count(id)")->from("{{%warehouse}}")
    -> leftJoin("(SELECT COUNT(id) as n, wareRef from {{%supplier_schet_content}} group BY wareRef) as a", "a.wareRef = {{%warehouse}}.id")
    ;


    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', 'grpGood', $this->grpGood]);
     $countquery->andFilterWhere(['like', 'grpGood', $this->grpGood]);	 

     if (!empty ($this->grpGoodList))
        {
            $listGrp = $this->getGrpGroup();	 
            $query->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGoodList] ]);
            $countquery->andFilterWhere(['like', "ifnull(grpGood,'Нет группы')", $listGrp[$this->grpGoodList]]);	                 
        }

    
     $query->andFilterWhere(['like', 'grpGood', $this->grpGood]);
     $countquery->andFilterWhere(['like', 'grpGood', $this->grpGood]);	 
	  
     $query->andFilterWhere(['like', 'title', $this->wareTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->wareTitle]);	 
	 
     }

     if (empty ($this->suppliersN))$this->suppliersN = 2;
     
     if ($this->suppliersN == 2)
        {            
            $query->andFilterWhere(['>', "ifnull(a.n,0)", 0 ]);
            $countquery->andFilterWhere(['>', "ifnull(a.n,0)", 0 ]);	                 
        }

     if ($this->suppliersN == 3)
        {            
            $query->andFilterWhere(['=', "ifnull(a.n,0)", 0 ]);
            $countquery->andFilterWhere(['=', "ifnull(a.n,0)", 0 ]);	                 
        }
     
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 6,
            ],
            
            'sort' => [
            
            'attributes' => [
				'id', 
				'grpGood', 
				'wareTitle', 
				'price', 
				'ed',
                'n'
	        ],
            'defaultOrder' => [ 'wareTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

/*********************/

/**************/    
   public function getGoodRequestProvider($params)
   {

   
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%request_good_content}}.id))")
    ->from("{{%request_good_content}}, {{%request_good}} ")
    ->leftJoin("{{%zakaz}}", '{{%zakaz}}.id = {{%request_good}}.refZakaz')    
    ->leftJoin("{{%user}}", '{{%user}}.id = {{%zakaz}}.ref_user')    
    ->leftJoin("{{%orglist}}", '{{%orglist}}.id = {{%zakaz}}.refOrg')
    ->where ("{{%request_good_content}}.refRequest = {{%request_good}}.id AND {{%request_good}}.isFormed =1 AND refPurchaseZakaz = 0");
    
    $query  = new Query();
    $query->select ("{{%request_good_content}}.id, good as goodTitle, count, sklad,  {{%zakaz}}.refOrg, {{%request_good}}.id as requestNum, {{%request_good}}.formDate,  title as orgTitle, {{%user}}.userFIO ")
    ->from("{{%request_good_content}}, {{%request_good}} ")
    ->leftJoin("{{%zakaz}}", '{{%zakaz}}.id = {{%request_good}}.refZakaz')
    ->leftJoin("{{%user}}", '{{%user}}.id = {{%zakaz}}.ref_user')    
    ->leftJoin("{{%orglist}}", '{{%orglist}}.id = {{%zakaz}}.refOrg')
    ->where ("{{%request_good_content}}.refRequest = {{%request_good}}.id AND {{%request_good}}.isFormed =1  AND refPurchaseZakaz = 0") 
    ->distinct();


    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', 'title', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);	 

     $query->andFilterWhere(['like', 'good', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'good', $this->goodTitle]);	 
    
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
				'id', 
                'goodTitle', 
                'count', 
                'sklad',                
                'requestNum', 
                'formDate',
                'refOrg', 
                'orgTitle',
                'userFIO'                                
	        ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
/**************/    
  public function printGoodRequestList ($provider)
  {
  
   echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $this,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                        
			[
                'attribute' => 'goodTitle',
				'label' => 'Товар',
                'format' => 'raw',
            ],	

			[
                'attribute' => 'count',
				'label' => 'К-во',
                'format' => 'raw',
            ],	
     
			[
                'attribute' => 'sklad',
				'label' => 'Склад',
                'format' => 'raw',
            ],	

			
			[
                'attribute' => 'requestNum',
				'label' => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                
				return $model['requestNum']." от ".$model['formDate'] ;
                },
                
            ],
			

            [
                'attribute' => 'orgTitle',
				'label' => 'Клиент',
                'format' => 'raw',                
            ],	


            [
                'attribute' => 'userFIO',
				'label' => 'Менеджер',
                'format' => 'raw',                
            ],	
            
			[
                'attribute' => 'requestNum',
				'label' => 'Запросы',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                   
                   return "<a class='btn btn-primary'  href='#' onclick=\"openWin('store/purchase-create-from-request&id=".$model['id']."','storeWin'); openSwitchWin('site/success');\"> В запрос </a>";
                },
                
            ],
			

            
        ],
    ]
);
 
}
 
/**************/    
     public function getPurchesListProvider($params)
   {

   
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%purchase}}.id))")
    ->from("{{%purchase}}")
    ;
    
    $query  = new Query();
    $query->select ([
    '{{%purchase}}.id', 
    'dateCreation', 
    'requestStatus', 
    'supplierShetRef', 
    'schetStatus', 
    'transportStatus',
    'docStatus', 
    'refOrg', 
    'title as orgTitle',    
    ])
    ->from("{{%purchase}}")
    ->leftJoin("{{%orglist}}", '{{%orglist}}.id = {{%purchase}}.refOrg')
    ->distinct();

    
         switch ($this->mode)
     {
        case 6:
        /*Закупка в процессе работы: */
        
        
        break;

        case 7:
        /* Закупки в ходе согласования: */
        $query
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        $countquery
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        
        $query->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");
        $countquery->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
        
        break;
        case 8:
        /* Закупки в ходе согласования: */
        $query
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=5 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=6 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        $countquery
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=5 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=6 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        
        $query->andWhere("(  (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");
        $countquery->andWhere("(  (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
        
        
        break;
        case 9:
        
        /* Закупки в ходе доставки: */
        $query
            ->leftJoin("(Select count(id) as s3_startN, purchaseRef from {{%purchase_etap}} where stage =3  group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s3_endN, purchaseRef from {{%purchase_etap}} where stage =3 and etap =8 group by purchaseRef) as s3_end ", 's3_end.purchaseRef = {{%purchase}}.id')                
        ;
        $countquery
            ->leftJoin("(Select count(id) as s3_startN, purchaseRef from {{%purchase_etap}} where stage =3  group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s3_endN, purchaseRef from {{%purchase_etap}} where stage =3 and etap =8 group by purchaseRef) as s3_end ", 's3_end.purchaseRef = {{%purchase}}.id')                
        ;
        
        $query->andWhere("(  (ifnull(s3_startN,0) >0  AND ifnull(s3_endN,0)=0 )  )");
        $countquery->andWhere("(  (ifnull(s3_startN,0) >0 AND ifnull(s3_endN,0)=0 )   )");
        
        
        break;
        case 10:

        /* Закупки в ходе завершения: */
        $query
            ->leftJoin("(Select count(id) as s1_N, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=4 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_N, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=7 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s3_N, purchaseRef from {{%purchase_etap}} where stage =3 AND etap=8 group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                            
            ->leftJoin("(Select count(id) as s4_startN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =2 group by purchaseRef) as s4_start ", 's4_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s4_endN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =3 group by purchaseRef) as s4_end ", 's4_end.purchaseRef = {{%purchase}}.id')                
        ;
        $countquery
            ->leftJoin("(Select count(id) as s1_N, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=4 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_N, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=7 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s3_N, purchaseRef from {{%purchase_etap}} where stage =3 AND etap=8 group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                            
            ->leftJoin("(Select count(id) as s4_startN, purchaseRef from {{%purchase_etap}} where stage =4  and etap =2 group by purchaseRef) as s4_start ", 's4_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s4_endN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =3 group by purchaseRef) as s4_end ", 's4_end.purchaseRef = {{%purchase}}.id')                
        ;
        
        $query->andWhere("( ifnull(s1_N,0)>0 AND ifnull(s2_N,0)>0  AND ifnull(s3_N,0)>0 AND (ifnull(s4_startN,0) >0  AND ifnull(s4_endN,0)=0 )  )");
        $countquery->andWhere("(  ifnull(s1_N,0)>0 AND ifnull(s2_N,0)>0  AND ifnull(s3_N,0)>0 AND  (ifnull(s4_startN,0) >0 AND ifnull(s4_endN,0)=0 )   )");
        break;
        
      }

    
   $query->andWhere("(  isFinishedPurchase =0  )");
   $countquery->andWhere("(  isFinishedPurchase =0  )");
    

    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', 'title', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);	 

     }

     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 6,
            ],
            
            'sort' => [
            
            'attributes' => [
				'id', 
                'dateCreation', 
                'requestStatus', 
                'supplierShetRef', 
                'schetStatus', 
                'transportStatus',
                'docStatus', 
                'refOrg', 
                'orgTitle'
	        ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
/*********************/
   
public function printPurchesList($provider)
   {
       
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $this,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
            [
                'attribute' => 'dateCreation',
				'label'     => 'Закупка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                  return "<nobr><a href='#' onclick='openWin(\"\store/purchase&noframe=1&id=".$model['id']."#status\",\"storeWin\");'>".$model['id']." от ".date('d.m.Y', strtotime($model['dateCreation']))."</a></nobr>";     
                }
            ],		

            [
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',

            ],		
         
			[
                'attribute' => 'Счет',
				'label'     => 'Счет',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                    
                    
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =2 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		
                    if (count($statusList) == 0) return "Нет";
                    else{
                        $retVal ="";
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Запрошен ";
                            break;
                        case 1: 
                            $retVal ="Счет получен";
                            break;
                        case 2: 
                            $retVal ="На согласов";                            
                            break;
                        case 3: 
                            $retVal ="Согласован ";                        
                            break;
                        case 4: 
                            $retVal ="Подтверждено ";                               
                            break;
                        case 5: 
                            $retVal ="В бухгалтер. ";                               
                            break;
                        case 6: 
                            $retVal ="В реестре ";                               
                            break;
                        case 7: 
                            $retVal ="Оплачен ";                               
                            break;
                            
                    }
                    
                    }

                    if ($model['supplierShetRef'] != 0)
                    {
                $strSql = "SELECT {{%supplier_schet_header}}.schetNum, {{%supplier_schet_header}}.schetDate, sum({{%supplier_schet_content}}.goodSumm) as schetSum   
                FROM {{%supplier_schet_header}}, {{%supplier_schet_content}} 
                where {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef and {{%supplier_schet_header}}.id =:refSchet order by {{%supplier_schet_header}}.id DESC LIMIT 1";                   
                    $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['supplierShetRef'],])->queryAll();                                        
                    
                    return $schetData[0]['schetNum']; 
                    
                    }
                  
                }
            ],		


			[
                'attribute' => 'Сумма',
				'label'     => 'Сумма',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
               
                    if ($model['supplierShetRef'] != 0)
                    {
                $strSql = "SELECT {{%supplier_schet_header}}.schetNum, {{%supplier_schet_header}}.schetDate, sum({{%supplier_schet_content}}.goodSumm) as schetSum   
                FROM {{%supplier_schet_header}}, {{%supplier_schet_content}} 
                where {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef and {{%supplier_schet_header}}.id =:refSchet order by {{%supplier_schet_header}}.id DESC LIMIT 1";                   
                    $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['supplierShetRef'],])->queryAll();                                        
                    
                    return  number_format($schetData[0]['schetSum'],2,".","&nbsp;"); 
                    
                    }
                  
                }
            ],		


			[
                'attribute' => 'Номенклатура',
				'label'     => 'Номенклатура',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
               
                $strSql = "SELECT wareTitle  
                FROM {{%purchase_zakaz}}  
                where {{%purchase_zakaz}}.purchaseRef  =:purchaseRef order by wareCount DESC LIMIT 1";                   
                    $purchData = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
                    
                     
                return $purchData[0]['wareTitle'];
                     
                }
            ],		
            
            
            
            [
                'attribute' => 'requestStatus',
				'label'     => 'Согласование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =1 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Формирование";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Cформирован ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="На согласов ".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="<div class='gridcell' style='background-color:PaleGreen ;' >Согласован ".$statusList[0]['execDate'] ."</div>";                        
                            break;
                        case 3: 
                            $retVal ="Отправлен ".$statusList[0]['execDate'];                            
                            break;
                        case 4: 
                            $retVal ="Получен ответ ".$statusList[0]['execDate'];                               
                            break;
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                
			],

            
            [
                'attribute' => 'transportStatus',
				'label'     => 'Доставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =3 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Неизвестно";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="В производстве ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="Готов к отгр. ".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="Трансп. заказан ".$statusList[0]['execDate'];                            
                            break;
                        case 3: 
                            $retVal ="Трансп. на загр. ".$statusList[0]['execDate'];                            
                            break;
                        case 4: 
                            $retVal ="Загрузка начата ".$statusList[0]['execDate'];                               
                            break;
                        case 5: 
                            $retVal ="Загружено ".$statusList[0]['execDate'];                               
                            break;
                        case 6: 
                            $retVal ="Отправлен ".$statusList[0]['execDate'];                               
                            break;
                        case 7: 
                            $retVal ="На разгрузке ".$statusList[0]['execDate'];                               
                            break;
                        case 8: 
                            $retVal ="На складе ".$statusList[0]['execDate'];                               
                            break;
                            
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                
                
			],
            
           [
                'attribute' => 'docStatus',
				'label'     => 'Документы',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
               
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =4 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Неизвестно";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Получены транспортные док ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="Документы зарегестрированы ".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="Получены документы на товар ".$statusList[0]['execDate'];                            
                            break;
                        case 3: 
                            $retVal ="Поставка закрыта ".$statusList[0]['execDate'];                            
                            break;
                            
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                

			],
            
        ],
    ]
	);
   
   
   }
/*********************/
public $wareInPurchesCount = 0;
public function prepareWareInPurcheProvider($params)
   {
    
    $query  = new Query();
    $query->select ([            
            '{{%purchase_zakaz_ware}}.id',
            '{{%purchase_zakaz_ware}}.wareTitle',            
            '{{%purchase_zakaz_ware}}.wareEd',
            '{{%purchase_zakaz_ware}}.wareCount',
            '{{%purchase_zakaz_ware}}.refPurchaseZakaz',
            '{{%purchase_zakaz}}.refZakaz',
            ])
            ->from("{{%purchase_zakaz_ware}},{{%purchase_zakaz}}")
            ->where ('{{%purchase_zakaz_ware}}.refPurchaseZakaz = {{%purchase_zakaz}}.id' )
            ->distinct();
    
    $query->andWhere("{{%purchase_zakaz}}.purchaseRef = ".$this->id);
    
     if (($this->load($params) && $this->validate())) {   
     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->wareInPurchesCount = count($list);
   } 

  
 public function getWareInPurcheProvider($params)
   {
    
    $this-> prepareWareInPurcheProvider($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->wareInPurchesCount,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'wareTitle',            
            'wareEd',
            'wareCount',       
            ],
            'defaultOrder' => [	'wareTitle' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
/****************************/

public function prepareWareInSchetProvider($params, $isMain)
   {
    
    $query  = new Query();
    $query->select ([            
            '{{%supplier_schet_content}}.id',
            '{{%supplier_schet_content}}.goodTitle',            
            '{{%supplier_schet_content}}.goodSumm',
            '{{%supplier_schet_content}}.goodCount',
            '{{%supplier_schet_content}}.goodEd',
            '{{%supplier_schet_content}}.isAdditionWare',
            '{{%purch_schet_lnk}}.purchRole',
            '{{%supplier_schet_content}}.wareEdValueRef',
            '{{%supplier_schet_content}}.wareCostValue',
            '{{%supplier_schet_content}}.wareCostPrice',
            '{{%supplier_schet_content}}.wareCostCount',
            '{{%supplier_schet_content}}.wareCostAdd'
            
            ])
            ->from("{{%supplier_schet_header}},{{%supplier_schet_content}}, {{%purch_schet_lnk}} ")                       
            ->andWhere ('{{%supplier_schet_content}}.schetRef = {{%supplier_schet_header}}.id ' )
            ->andWhere ('{{%supplier_schet_header}}.id = {{%purch_schet_lnk}}.schetRef' )
            ->distinct();
    
    $query->andWhere("{{%purch_schet_lnk}}.purchRef = ".$this->id);
    if ($isMain){
        $query->andWhere("{{%purch_schet_lnk}}.purchRole = 0");
        $query->andWhere("{{%supplier_schet_content}}.isAdditionWare = 0");
        }
    else{
        $query->andWhere("({{%purch_schet_lnk}}.purchRole > 0 OR  {{%supplier_schet_content}}.isAdditionWare =1)");
        }
     if (($this->load($params) && $this->validate())) { 

     $query->andFilterWhere(['like', 'title', $this->orgTitle]);
     }

    
     
     
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();

    
    
    $this->count = count($list);
   } 


 public $mainWareSum = 0;   
 public $wareCostAdd = 0;   
 public function getMainWareInSchetProvider($params)
   {
    
    $query  = new Query();
    $query->select ([            
            'sum({{%supplier_schet_content}}.goodSumm) as goodSumm',            
            'MAX({{%supplier_schet_content}}.wareCostAdd) as wareCostAdd',
            ])
            ->from("{{%supplier_schet_header}},{{%supplier_schet_content}}, {{%purch_schet_lnk}} ")                       
            ->andWhere ('{{%supplier_schet_content}}.schetRef = {{%supplier_schet_header}}.id ' )
            ->andWhere ('{{%supplier_schet_header}}.id = {{%purch_schet_lnk}}.schetRef' )
            ->distinct();    
    $query->andWhere("{{%purch_schet_lnk}}.purchRef = ".$this->id);
    $query->andWhere("{{%purch_schet_lnk}}.purchRole = 0");
    $query->andWhere("{{%supplier_schet_content}}.isAdditionWare = 0");
     $res  = $query->createCommand()->queryOne();
    
    $this->mainWareSum = $res['goodSumm'];
    $this->wareCostAdd = $res['wareCostAdd'];
    
    
    $this-> prepareWareInSchetProvider($params, true);    
    $pageSize = 50;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'goodTitle',            
            'goodSumm',
            'goodCount',
            'goodEd',
                 ],
            'defaultOrder' => [	'goodTitle' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
   
public $addWareSum =0;    
 public function getWareInSchetProvider($params)
   {
    
    
    $query  = new Query();
    $query->select ([            
            'sum({{%supplier_schet_content}}.goodSumm)',
            ])
            ->from("{{%supplier_schet_header}},{{%supplier_schet_content}}, {{%purch_schet_lnk}} ")                       
            ->andWhere ('{{%supplier_schet_content}}.schetRef = {{%supplier_schet_header}}.id ' )
            ->andWhere ('{{%supplier_schet_header}}.id = {{%purch_schet_lnk}}.schetRef' )
            ->distinct();    
    $query->andWhere("{{%purch_schet_lnk}}.purchRef = ".$this->id);
    $query->andWhere("({{%purch_schet_lnk}}.purchRole > 0 OR  {{%supplier_schet_content}}.isAdditionWare =1)");
    $this->addWareSum =  $query->createCommand()->queryScalar();
    
    $this-> prepareWareInSchetProvider($params, false);    
    $pageSize = 50;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'goodTitle',            
            'goodSumm',
            'goodCount',
            'goodEd',
                 ],
            'defaultOrder' => [	'goodTitle' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

   
   
/****************************/
public function preparePurchaseWareSchet($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%supplier_schet_content}}.id',
            '{{%supplier_schet_header}}.schetNum',
            '{{%supplier_schet_header}}.schetDate',            
            '{{%supplier_schet_header}}.orgTitle as orgTitle',
            'goodTitle as wareTitle',
            'goodEd',
            'goodSumm',
            'goodCount',
            'refOrg',
            ])
            ->from("{{%supplier_schet_header}}, {{%supplier_schet_content}}")                       
            ->where("{{%supplier_schet_header}}.id={{%supplier_schet_content}}.schetRef ")
            ->distinct();
     
     if (($this->load($params) && $this->validate())) {   

        $query->andFilterWhere(['like', 'goodTitle', $this->wareTitle]);
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle',  $this->orgTitle]);

     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

  
 public function getPurchaseWareSchet($params)
   {
    
    $this-> preparePurchaseWareSchet($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'schetNum',
            'schetDate',            
            'orgTitle',
            'wareTitle',
            'goodEd',
            'goodSumm',
            'goodCount',
            'refOrg',
              ],
            'defaultOrder' => [	'schetDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
   
/****************************/

public function preparePurchaseSchetList($params)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'schetNum',
            'schetDate',            
            'orgTitle',
            'refOrg',
            'purchaseRef',
            'purchaseRole'
            ])
            ->from("{{%supplier_schet_header}}")                       
            ->distinct();
    
    if (!empty($this->supplierRef))
    $query->andWhere("refOrg = ".$this->supplierRef);
    
     if (($this->load($params) && $this->validate())) {   
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);
        $query->andFilterWhere(['=', 'schetNum', $this->schetNum]);
     }
   
   
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'schetDate', $from]);
//        $countquery->andWhere(['>=', 'schetDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'schetDate', $to]);
//        $countquery->andWhere(['<=', 'schetDate', $to]);
     }

    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 
   
  
 public function getPurchaseSchetList($params)
   {
    
    $this-> preparePurchaseSchetList($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'schetNum',
            'schetDate',            
            'orgTitle',
            'refOrg',
              ],
            'defaultOrder' => [	'schetDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
  
/****************************/
/****************************/
public function getDocTypes () {
    $strSql = "SELECT id, docGrpTitle from {{%doc_group}}"; 
    $docTypeList = Yii::$app->db->createCommand($strSql)->queryAll();         
    return ArrayHelper::map($docTypeList,'id','docGrpTitle');               
}
public function preparePurchaseDocList($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%documents}}.id',
            'docOrigNum',
            'docOrigDate',            
            '{{%documents}}.orgTitle',
            '{{%documents}}.refOrg',
            'refSupplierSchet',
            'purchaseErpRef',
            'purchaseRef',
            'purchaseRole',
            'schetNum',
            'schetDate',
            'supplierRef1C',
            'docClassifyRef',
            'docTypeRef',
            'docTitle'
            ])
            ->from("{{%documents}}")                       
            ->leftJoin("{{%supplier_schet_header}}", "{{%documents}}.refSupplierSchet={{%supplier_schet_header}}.id")                       
            ->distinct();
            
    $countquery  = new Query();
    $countquery->select ("COUNT(DISTINCT({{%documents}}.id))")
            ->from("{{%documents}}")                       
            ->leftJoin("{{%supplier_schet_header}}", "{{%documents}}.refSupplierSchet={{%supplier_schet_header}}.id")                       
            ->distinct();
    
    if (!empty($this->supplierRef))
    {
        $query->andWhere("refOrg = ".$this->supplierRef);
        $countquery->andWhere("refOrg = ".$this->supplierRef);
        
    }
    
     if (($this->load($params) && $this->validate())) {   
        $query->andFilterWhere(['like', '{{%documents}}.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%documents}}.orgTitle', $this->orgTitle]);
        
/*        $query->andFilterWhere(['=', 'schetNum', $this->schetNum]);
        $countquery->andFilterWhere(['=', 'schetNum', $this->schetNum]);*/

        $query->andFilterWhere(['Like', 'supplierRef1C', $this->supplierRef1C]);
        $countquery->andFilterWhere(['Like', 'supplierRef1C', $this->supplierRef1C]);


        $query->andFilterWhere(['=', 'docTypeRef', $this->docOrigNum]);
        $countquery->andFilterWhere(['=', 'docTypeRef', $this->docOrigNum]);
        
        
        
     }
   
   
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'schetDate', $from]);
        $countquery->andWhere(['>=', 'schetDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'schetDate', $to]);
        $countquery->andWhere(['<=', 'schetDate', $to]);
     }

   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = $countquery->createCommand()->queryScalar();;    
   } 
   
  
 public function getPurchaseDocList($params)
   {
    
    $this-> preparePurchaseDocList($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	                       
            'docOrigNum',
            'docOrigDate',            
            'orgTitle',
            'refOrg',
            'refSupplierSchet',
            'purchaseRef',
            'purchaseRole',
            'schetNum',
            'supplierRef1C'
              ],
            'defaultOrder' => [	'docOrigDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
 /***************************************/
public function preparePurchaseZaprosList($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%purchase_zakaz_ware}}.id as purchaseWareId',
            '{{%purchase_zakaz_ware}}.wareTitle',
            '{{%purchase_zakaz_ware}}.wareCount',            
            '{{%purchase_zakaz_ware}}.wareEd',
            '{{%purchase_zakaz_ware}}.refPurchaseZakaz',
            '{{%purchase_zakaz}}.refZakaz',
            '{{%purchase_zakaz}}.purchaseRef'
            ])
            ->from(["{{%purchase_zakaz_ware}}","{{%purchase_zakaz}}"])              
            ->where("{{%purchase_zakaz_ware}}.refPurchaseZakaz = {{%purchase_zakaz}}.id 
            
            AND {{%purchase_zakaz}}.status = 2 
            AND {{%purchase_zakaz}}.isActive = 1 ")       
            ->distinct();
        
/*AND {{%purchase_zakaz}}.purchaseRef = 0 */

     if (($this->load($params) && $this->validate())) {  

        $query->andFilterWhere(['like', '{{%purchase_zakaz_ware}}.wareTitle', $this->wareTitle]);     
     }
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

 public function getPurchaseZaprosList($params)
   {
    
    $this->preparePurchaseZaprosList($params);    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'purchaseWareId',
            'wareTitle',
            'wareCount',            
            'wareEd',
            'refPurchaseZakaz',
            'refZakaz',
            'purchaseRef'
            ],
            'defaultOrder' => [	'wareTitle' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
/****************************/   
 
/****************************/
public function preparePurchaseRequestList()
   {
    
  $query  = new Query();
/*  $query->select ([
            '{{%zakaz}}.id',
            '{{%zakaz}}.formDate',
            'userFIO',            
            '{{%orglist}}.title as orgTitle',            
            '{{%request_good_content}}.refPurchaseZakaz',
            '{{%request_good_content}}.id as requestId',
            '{{%request_good_content}}.good as requestGood',            
            '{{%request_good_content}}.count as requestCount',            
            '{{%zakaz}}.refOrg'            
            ])
            ->from([
            '{{%request_good_content}}',
            '{{%request_good}}',
            '{{%zakaz}}',
            '{{%orglist}}',
            '{{%user}}',
            ])                       
            ->where("{{%request_good_content}}.refRequest = {{%request_good}}.id
            and {{%request_good}}.refZakaz = {{%zakaz}}.id
            and {{%zakaz}}.ref_user={{%user}}.id and {{%zakaz}}.refOrg = {{%orglist}}.id ")
            ->distinct();
*/

$query->select ([
            '{{%schet}}.id',
            '{{%schet}}.schetNum',
            '{{%schet}}.schetDate',
            'userFIO',            
            '{{%orglist}}.title as orgTitle',            
            '{{%schet}}.refOrg' ,
            'wareTitle',
            'wareCount',
            'wareEd'
                       
            ])
            ->from([
            '{{%purchase_zakaz}}',
            '{{%schet}}',
            '{{%orglist}}',
            '{{%user}}',
            ])                       
            ->where("{{%purchase_zakaz}}.refSchet = {{%schet}}.id
            and {{%schet}}.refManager={{%user}}.id 
            and {{%schet}}.refOrg = {{%orglist}}.id ")
            ->distinct();
            

    
    $query->andWhere("{{%purchase_zakaz}}.id = ".$this->id);  
     
     $this->debug[] = $query->createCommand()->getRawSql();
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

  
 public function getPurchaseRequestListProvider()
   {
    
    $this-> preparePurchaseRequestList();    
    $pageSize = 5;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            
            'sort' => [
            'attributes' => [	                        
            'id',
            'schetNum',
            'schetDate',
            'userFIO',            
            'orgTitle',
              ],
            'defaultOrder' => [	'schetDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
/****************************/
public function printInOplate()
{
    
  $strSql=" SELECT {{%doc_oplata}}.id as refDocOplata, {{%doc_oplata}}.sumToOplate,  extractStatus, {{%purch_schet_lnk}}.purchRole
   from {{%purch_schet_lnk}}
   left join {{%documents}} on {{%documents}}.refSupplierSchet = {{%purch_schet_lnk}}.schetRef
   left join {{%doc_oplata}} on {{%doc_oplata}}.refDocument = {{%documents}}.id
   where {{%purch_schet_lnk}}.purchRef =:purchRef
   ";

  $oplList= Yii::$app->db->createCommand($strSql,                  
                 [':purchRef' => $this->id,])->queryAll();   

   $N = count($oplList);
    if ( $N == 0) 
    {
     echo "<div style='color:Black;text-align:left;font-size:12px;' >Нет в оплатах</div>";   
     return;   
    }
 
    $oplSum = 0;
    $oplSumConfirm = 0;
    for ($i=0;i<$N ;$i++ )
    {
        $oplSum+=$oplList[$i]['sumToOplate']; // в требованиях
        if ($oplList[$i]['extractStatus'] > 4) $oplSumConfirm+=$oplList[$i]['sumToOplate'];
        if ($i > 10) break; 
    }        

                 
    if(empty($oplSum) )
    {
     echo "<div style='color:Black;text-align:left;font-size:12px;' >Платеж не назначен</div>";   
     return;   
    }
    
    $record = PurchaseEtap::FindOne([
    'purchaseRef' =>$this->id,
    'stage' =>2,
    'etap' =>6,
    ]);  
    if (empty($record)) 
    {
        $record = new PurchaseEtap();    
        if (empty($record)) return -2; //record not found    
        $record->purchaseRef = $this->id;
        $record->stage = 2;
        $record->etap =  6;
        
    }
    $record->execDate = $recordReestr->formDate; 
    $record->save();


    echo "<div class='nonActiveCell'> Счет в оплате <div class='dval' style='background-color:LightGray;'>".$this->statusVal['s2'][6]."</div> </div> ";
}  
/****************************/
public function printFinalOplate()
{
    
    $recordReestr = ReestrOplat::FindOne(['refSchet' => $this->schetRef  ]);        
    if (empty($recordReestr)) 
    {
     echo "<div style='color:Black;text-align:left;font-size:12px;' >Нет в реестре платежей</div>";   
     return;   
    }


      
    $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $recordReestr->id,])->queryScalar();

    if(empty($oplSum))
    {
     echo "<div style='color:Black;text-align:left;font-size:12px;' >Не оплачен</div>";   
     return;   
    }

                 
    $val = $recordReestr->summRequest-$oplSum;

    if ($val > 0.1)
    {
     echo "<div style='color:Black;text-align:left;font-size:12px;' >Оплачено частично</div>";   
     return;       
    }
    
    $record = PurchaseEtap::FindOne([
    'purchaseRef' =>$this->id,
    'stage' =>2,
    'etap' =>7,
    ]);  
    if (empty($record)) 
    {
        $record = new PurchaseEtap();    
        if (empty($record)) return -2; //record not found    
        $record->purchaseRef = $id;
        $record->stage = 2;
        $record->etap =  7;
        
    }
   $oplDate= Yii::$app->db->createCommand("Select Max(oplateDate) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $recordReestr->id,])->queryScalar();

    $record->execDate = $oplDate; 
    $record->save();

    echo "<div style='background-color:LightGray;font-size:12px;'> Счет оплачен <div class='dval' style='background-color:LightGray;'>".$this->statusVal['s2'][7]."</div> </div> ";
}  

/****************************/
//второй столбец - счета
 public function printShetStat($statusList, $num)
 {
      $prefix ='s2';    
      $id=$prefix."e".$num;    
    
   if ($num == 0){          
     if (empty($this->statusVal[$prefix][$num]))
       $val= "<div >".$statusList[$prefix][$num]."<div class='dval' id='dateBox_".$id."'> &nbsp; </div></div> ";
     else
       $val= "<div style='background-color:LightGray;'>".$statusList[$prefix][$num]." <div class='dval' style='background-color:LightGray;' id='dateBox_".$id."'>".$this->statusVal[$prefix][$num]."</div> </div> ";
    $action="setMarked";     
    $ret ="<div id='viewBox_".$id."' class='gridcell' onclick=\"javascript:".$action."('".$id."');\">".$val."</div>"; 
    $ret.="<div id='editBox_".$id."' class='editcell'><nobr>";
    $ret.="<input  id='edit_".$id."' class='tcal' value='".date('d.m.Y')."'>";
    $ret.="<a href ='#' onclick=\"javascript:setDate('".$id."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
    $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
    $ret.="</nobr></div>";  
   return $ret;    
   }
       
   if (empty($this->schetRef)) return "Нет счета";  

   $strSql=" SELECT {{%doc_oplata}}.id as refDocOplata, {{%doc_oplata}}.sumToOplate,  
   extractStatus, dateToOplata
   from  {{%documents}} 
   left join {{%doc_oplata}} on {{%doc_oplata}}.refDocument = {{%documents}}.id
   where  {{%documents}}.refSupplierSchet = :refSupplierSchet
   and extractStatus >= :extractStatus
   order by extractStatus ASC, dateToOplata DESC
   ";

   
    switch ($num)
    {
      case 1:  
        $record = SupplierSchetHeaderList::findOne($this->schetRef);
        if (empty($record))$val="";
        else $val= "<div class='dval' id='dateBox_".$id."'>".date("d.m.Y", strtotime($record->schetDate))."</div>";
        return  "<div class='doneCell'>".$statusList['s2'][$num].$val."</div>";
      break;
      case 2: 
      $oplList= Yii::$app->db->createCommand($strSql,                  
                 [':refSupplierSchet' => $this->schetRef,
                  ':extractStatus' => 1,
                 ])->queryAll();      
                 
      if (count($oplList) > 0) {
         $val= "<div class='dval' id='dateBox_".$id."'>".date("d.m.Y", strtotime($oplList[0]['dateToOplata']))."</div>";
      return  "<div class='doneCell'>".$statusList['s2'][$num].$val."</div>";}
      break;
      case 3: 
      $oplList= Yii::$app->db->createCommand($strSql,                  
                 [':refSupplierSchet' => $this->schetRef,
                  ':extractStatus' => 3,
                 ])->queryAll();        
      if (count($oplList) > 0) {
         $val= "<div class='dval' id='dateBox_".$id."'>".date("d.m.Y", strtotime($oplList[0]['dateToOplata']))."</div>";
      return  "<div class='doneCell'>".$statusList['s2'][$num].$val."</div>";}
      break;
      case 4: 
      $oplList= Yii::$app->db->createCommand($strSql,                  
                 [':refSupplierSchet' => $this->schetRef,
                  ':extractStatus' => 4,
                 ])->queryAll();        
      if (count($oplList) > 0) {
         $val= "<div class='dval' id='dateBox_".$id."'>".date("d.m.Y", strtotime($oplList[0]['dateToOplata']))."</div>";
      return  "<div class='doneCell'>".$statusList['s2'][$num].$val."</div>";}
      break;

      
    }
    return  "<div class='waitCell'>".$statusList['s2'][$num]."</div>";
 }
 public function printEditBox($prefix,$num)
{ 


 $statusList = array();

 $statusList['s1'][0]="Заказ сформирован";		
 $statusList['s1'][1]="На согласовании";		
 $statusList['s1'][2]="Согласовано";		
 $statusList['s1'][3]="Отправлен поставщику";		
 $statusList['s1'][4]="Получен ответ";		


 $statusList['s2'][0]="Счет запрошен";		
 $statusList['s2'][1]="Счет получен";		
/* $statusList['s2'][2]="На согласовании";		
 $statusList['s2'][3]="Согласовано";		
 $statusList['s2'][2]="Подтвердить заказ";	*/	
 $statusList['s2'][2]="Счет в реес. платежей";		
 $statusList['s2'][3]="Счет в оплате";		
 $statusList['s2'][4]="Счет оплачен";		 
 
 $statusList['s3'][0]="В производстве";		
 $statusList['s3'][1]="Готов к отгрузке";		
 $statusList['s3'][2]="Транспорт заказан";		
 $statusList['s3'][3]="Транс. прибыл на загр.";		
 $statusList['s3'][4]="Загрузка начата";		
 $statusList['s3'][5]="Загрузка завершена";		
 $statusList['s3'][6]="Товар отправлен";		
 $statusList['s3'][7]="Товар на разгрузке";		
 $statusList['s3'][8]="Товар на складе";		 

 $statusList['s4'][0]="Пол. транспортные док.";		
 $statusList['s4'][1]="Док. зарегестрированы";		
 $statusList['s4'][2]="Пол. док. на товар";		
 $statusList['s4'][3]="Поставка закрыта";			
 $statusList['s4'][8]="Отказ от закупки";	
 
 $id=$prefix."e".$num;

 $curUser=Yii::$app->user->identity;
 
 if ($prefix == 's2') return $this->printShetStat($statusList, $num);
 
  $action="showEditBox"; 
 
  $actDisable=0; 
 
 if(!($prefix == 's4' && $num == 8) )
 {
 if ($this->Etap1IsSogl == 0)
 {
    if ($prefix!='s1')
    {
    $action="alertPurch";
    $actDisable =1;
     //return "<div style='color:Gray;'>Закупка не согласована </div>";
    }
 }
 }
 
 $this->Etap2IsSogl = 1; //в текущей версии всегда согласован
 if ($this->Etap2IsSogl == 0)
 {
   if ($prefix=='s3')
   {
       $actDisable =1;
       $action="alertSchet";     
   } 
 }
 
 
  if (empty($this->statusVal[$prefix][$num]))
     {
       $val= "<div >".$statusList[$prefix][$num]."<div class='dval' id='dateBox_".$id."'> &nbsp; </div></div> ";
       if ($actDisable ==0)$action="setMarked";
       
       if ($prefix=='s4' && $num == 8)
           $val= "<div style='color:Crimson;'>".$statusList[$prefix][$num]."<div class='dval' id='dateBox_".$id."'> &nbsp; </div></div> ";
     }       
     else
     {
        $val= "<div style='background-color:LightGray;'>".$statusList[$prefix][$num]." <div class='dval' style='background-color:LightGray;' id='dateBox_".$id."'>".$this->statusVal[$prefix][$num]."</div> </div> ";
        if ($prefix=='s4' && $num == 8)
        $val= "<div style='background-color:LightGray;color:Crimson;'>".$statusList[$prefix][$num]." <div class='dval' style='background-color:LightGray;' id='dateBox_".$id."'>".$this->statusVal[$prefix][$num]."</div> </div> ";
     }
  
/*    if (empty($this->statusVal[$prefix][$num]))
     {
       $val= "<div id='dateBox_".$id."'>".$statusList[$prefix][$num]." &nbsp; </div>";
       $action="setMarked";
     }       
     else  $val= "<div style='background-color:LightGray;' id='dateBox_".$id."'>".$statusList[$prefix][$num]." ".$this->statusVal[$prefix][$num]."</div>";
*/
  
  if ( ($prefix == 's1' && $num == 2) && (!($curUser->roleFlg & 0x0020) && !($curUser->roleFlg & 0x0200) ) ) 
  {
   $ret ="<div id='viewBox_".$id."' class='gridcell' >".$val."</div>";           
  }elseif (  ($prefix == 's2' && $num == 3) && (!($curUser->roleFlg & 0x0020) && !($curUser->roleFlg & 0x0200) ))
  {
   $ret ="<div id='viewBox_".$id."' class='gridcell' >".$val."</div>";           
  }
  else
  {
  $ret ="<div id='viewBox_".$id."' class='gridcell' onclick=\"javascript:".$action."('".$id."');\">".$val."</div>"; 
  $ret.="<div id='editBox_".$id."' class='editcell'><nobr>";
  $ret.="<input  id='edit_".$id."' class='tcal' value='".date('d.m.Y')."'>";
  $ret.="<a href ='#' onclick=\"javascript:setDate('".$id."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
  $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
  $ret.="</nobr></div>";
  }
  
  return $ret;
}
 /***********************************************/ 
 /***********************************************/ 
public function preparePurchTableData($params)
   {
    
    /*$query  = new Query();
    $countquery  = new Query();*/
    
/*
В изначальном варианте предусмотренны заявки от менеджера.
В текущей реализации не нужны.
*/    
    

$strGoodList ="
(SELECT wareTitle, wareCount as sumWareCount, wareEd, id as zaprosId, refZakaz from {{%purchase_zakaz}} 
UNION Select good, count, goodEd, refPurchaseZakaz, refZakaz  from {{%request_good_content}}, {{%request_good}} 
where {{%request_good_content}}.refRequest =  {{%request_good}}.id  AND refPurchaseZakaz = 0 ) as goodList
";

$strSelectedVariant ="
( SELECT refPurchaseZakaz, curentValue as variantValue, refSchet, schetNum, schetDate, orgTitle, {{%supplier_schet_header}}.refOrg  
FROM {{%purchase_variant}}, {{%supplier_schet_header}}
where {{%purchase_variant}}.refSchet =  {{%supplier_schet_header}}.id and isActiveVariant = 1) as selectedVariant        
";

$strPurchase ="
(
select  {{%purchase}}.id , {{%purchase}}.dateCreation, {{%purchase}}.supplierShetRef as purchSchetRef,
 {{%purchase}}.refOrg as purchRefOrg, isFinishedPurchase,  {{%supplier_schet_header}}.schetNum as purchSchetNum , 
 {{%supplier_schet_header}}.schetDate as purchSchetDate, {{%supplier_schet_header}}.orgTitle as purchOrgTitle  
 from {{%purchase}} left join {{%supplier_schet_header}} on {{%purchase}}.supplierShetRef = {{%supplier_schet_header}}.id
) as purchase 
";
    
$query  = new Query();

//SELECT wareTitle, wareCount as sumWareCount, wareEd, id as zaprosId, refZakaz from {{%purchase_zakaz}

    
$query->select ([
        'goodList.zakazDate as creationDate',
        'goodList.wareTitle',
        'goodList.sumWareCount', 
        'goodList.wareEd', 
        'goodList.zaprosId as zaprosId', 
        'goodList.refZakaz',  
        '{{%purchase_zakaz}}.zakazDate as zaprosDate',  
        '{{%purchase_zakaz}}.isActive as zaprosIsActive',        
        '{{%purchase_zakaz}}.status as zaprosStatus',        
        '{{%purchase_zakaz}}.zakazNote as zaprosNote',        
        '{{%purchase_zakaz}}.zaprosType',
        '{{%purchase}}.purchaseNote',                
        '{{%request_good_content}}.id as requestID',
        '{{%purchase}}.id as purchaseId' , 
        '{{%purchase}}.dateCreation', 
        '{{%purchase}}.supplierShetRef as purchSchetRef',
        '{{%purchase}}.refOrg as purchRefOrg',         
        'isFinishedPurchase',  
        'variantValue', 
        'selectedVariant.refSchet as variantSchet', 
        'selectedVariant.schetNum as variantSchetNum', 
        'selectedVariant.schetDate as variantSchetDate', 
        'variantWareTitle',
        'selectedVariant.refOrg as variantRefOrg', 
        'selectedVariant.orgTitle as variantOrgTitle',         
      ])->
      from('(SELECT {{%purchase_zakaz}}.zakazDate, wareTitle, wareCount as sumWareCount, wareEd, id as zaprosId, refZakaz from {{%purchase_zakaz}}) as goodList')
     ->leftJoin("{{%purchase_zakaz}}","goodList.zaprosId = {{%purchase_zakaz}}.id")            
     ->leftJoin("{{%request_good_content}}","goodList.zaprosId = {{%request_good_content}}.id")            
     ->leftJoin("{{%purchase}}","{{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef")  
     ->leftJoin("(SELECT refPurchaseZakaz, curentValue as variantValue, refSchet, schetNum, schetDate, title as variantWareTitle,
                {{%supplier_schet_header}}.refOrg, {{%supplier_schet_header}}.orgTitle  
                FROM {{%purchase_variant}}, {{%supplier_schet_header}}, {{%warehouse}}
                where {{%purchase_variant}}.refSchet =  {{%supplier_schet_header}}.id and isActiveVariant = 1
                AND {{%warehouse}}.id =   {{%purchase_variant}}.refWare) as selectedVariant", "selectedVariant.refPurchaseZakaz = {{%purchase_zakaz}}.id")
     ;


$queryU  = new Query();      
$queryU->select ([
        '{{%request_good}}.formDate as creationDate',
        'good', 
        'count', 
        'goodEd', 
        '{{%request_good_content}}.refPurchaseZakaz', 
        '{{%request_good}}.refZakaz',
        '{{%purchase_zakaz}}.zakazDate as zaprosDate',  
        '{{%purchase_zakaz}}.isActive as zaprosIsActive',    
        '{{%purchase_zakaz}}.status as zaprosStatus', 
        '{{%purchase_zakaz}}.zakazNote as zaprosNote',        
        '{{%purchase_zakaz}}.zaprosType',
        '{{%purchase}}.purchaseNote',                        
        '{{%request_good_content}}.id as requestID',
        '{{%purchase}}.id as purchaseId' ,  
        '{{%purchase}}.dateCreation', 
        '{{%purchase}}.supplierShetRef as purchSchetRef',
        '{{%purchase}}.refOrg as purchRefOrg', 
        'isFinishedPurchase',        
        'variantValue', 
        'selectedVariant.refSchet as variantSchet', 
        'selectedVariant.schetNum as variantSchetNum', 
        'selectedVariant.schetDate as variantSchetDate', 
        'variantWareTitle',
        'selectedVariant.refOrg as variantRefOrg', 
        'selectedVariant.orgTitle as variantOrgTitle', 
      ])->from(['{{%request_good_content}}', '{{%request_good}}'] )
      ->leftJoin("{{%purchase_zakaz}}", "{{%request_good}}.refZakaz = {{%purchase_zakaz}}.id")                 
      ->leftJoin("{{%purchase}}","{{%purchase}}.id = {{%purchase_zakaz}}.purchaseRef")  
     ->leftJoin("(SELECT refPurchaseZakaz, curentValue as variantValue, refSchet, schetNum, schetDate, title as variantWareTitle,
                {{%supplier_schet_header}}.refOrg, {{%supplier_schet_header}}.orgTitle  
                FROM {{%purchase_variant}}, {{%supplier_schet_header}}, {{%warehouse}}
                where {{%purchase_variant}}.refSchet =  {{%supplier_schet_header}}.id 
                and {{%purchase_variant}}.isActiveVariant = 1
                AND {{%warehouse}}.id =   {{%purchase_variant}}.refWare) as selectedVariant", "selectedVariant.refPurchaseZakaz = {{%purchase_zakaz}}.id")      
      ->where ('{{%request_good_content}}.refRequest =  {{%request_good}}.id  AND {{%request_good_content}}.refPurchaseZakaz = 0')      
      ;
    if (($this->load($params) && $this->validate())) {
         
  /*      $query->andFilterWhere(['like', 'goodList.wareTitle', $this->wareTitle]);
        $queryU->andFilterWhere(['like', 'good', $this->wareTitle]);*/
        
        $query->andFilterWhere(['or',
            ['like','goodList.wareTitle',$this->wareTitle],
            ['like','variantWareTitle',$this->wareTitle]]);

        $queryU->andFilterWhere(['or',
            ['like','good',$this->wareTitle],
            ['like','variantWareTitle',$this->wareTitle]]);
            
     }
    if(empty($this->isFinishedPurchase)) $this->isFinishedPurchase = 2;
    switch ($this->isFinishedPurchase)    
    {
       case 2:
             $query->andFilterWhere(['=', 'ifnull(isFinishedPurchase,0)', 0]);
            $queryU ->andFilterWhere(['=', 'ifnull(isFinishedPurchase,0)', 0]);

            
            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 1]);
            $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 1]);
            
       break;
  
       case 3:

            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 0]);
            $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 0]);

       
//            $query->andFilterWhere(['=', 'ifnull(isFinishedPurchase,0)', 1]);
//            $queryU ->andFilterWhere(['=', 'ifnull(isFinishedPurchase,0)', 1]);
            
       break;

       default:
        //все
       break;
    }

    
if ($this->mode == 1)    
{
           $curUser=Yii::$app->user->identity; 
           $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.zaprosType,0)', 1]);
           $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.zaprosType,0)', 1]);    
        
}
else
{
           $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.zaprosType,0)', 0]);
           $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.zaprosType,0)', 0]);

}
    
switch ($this->mode)
{
    case 1:    

    break;    
    
    case 2:    
     
            $query->andFilterWhere(['>', 'ifnull(goodList.zaprosId,0)', 0]);
            $queryU ->andFilterWhere(['>', 'ifnull({{%request_good_content}}.refPurchaseZakaz,0)', 0]);
    break;    

    case 3:    

            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 1]);
            $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 1]);
    break;    

    case 4:    

            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 2]);
            $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 2]);
    break;    

    case 5:    

            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 8]);
            $queryU ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.status,0)', 8]);
    break;    


    case 6:    

            $query->andFilterWhere(['>', 'ifnull({{%purchase}}.id,0)', 0]);
            $queryU ->andFilterWhere(['>', 'ifnull({{%purchase}}.id,0)', 0]);
    break;    
 

     case 37:    
            /* Закупки в ходе согласования: */
        $query
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        $queryU
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
    
           $query ->andWhere("( (ifnull({{%purchase_zakaz}}.status,0) =1) OR (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");
           $queryU->andWhere("( (ifnull({{%purchase_zakaz}}.status,0) =1) OR (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");

    break;    

 
    case 7:    
            /* Закупки в ходе согласования: */
        $query
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        $queryU
            ->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
    
           $query ->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");
           $queryU->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");

    break;    

        case 8:
        /* Закупки в ходе оплаты: */
        $query
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=5 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=6 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        $queryU
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=5 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=6 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;
        
        $query->andWhere("(  (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )  )");
        $queryU->andWhere("(  (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
        
        
        break;
        case 9:
        
        /* Закупки в ходе доставки: */
        $query
            ->leftJoin("(Select count(id) as s3_startN, purchaseRef from {{%purchase_etap}} where stage =3  group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s3_endN, purchaseRef from {{%purchase_etap}} where stage =3 and etap =8 group by purchaseRef) as s3_end ", 's3_end.purchaseRef = {{%purchase}}.id')                
        ;
        $queryU
            ->leftJoin("(Select count(id) as s3_startN, purchaseRef from {{%purchase_etap}} where stage =3  group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s3_endN, purchaseRef from {{%purchase_etap}} where stage =3 and etap =8 group by purchaseRef) as s3_end ", 's3_end.purchaseRef = {{%purchase}}.id')                
        ;
        
        $query->andWhere("(  (ifnull(s3_startN,0) >0  AND ifnull(s3_endN,0)=0 )  )");
        $queryU->andWhere("(  (ifnull(s3_startN,0) >0 AND ifnull(s3_endN,0)=0 )   )");
        
        
        break;
        case 10:

        /* Закупки в ходе завершения: */
        $query
            ->leftJoin("(Select count(id) as s1_N, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=4 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_N, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=7 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s3_N, purchaseRef from {{%purchase_etap}} where stage =3 AND etap=8 group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                            
            ->leftJoin("(Select count(id) as s4_startN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =2 group by purchaseRef) as s4_start ", 's4_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s4_endN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =3 group by purchaseRef) as s4_end ", 's4_end.purchaseRef = {{%purchase}}.id')                
        ;
        $queryU
            ->leftJoin("(Select count(id) as s1_N, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=4 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_N, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=7 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s3_N, purchaseRef from {{%purchase_etap}} where stage =3 AND etap=8 group by purchaseRef) as s3_start ", 's3_start.purchaseRef = {{%purchase}}.id')                            
            ->leftJoin("(Select count(id) as s4_startN, purchaseRef from {{%purchase_etap}} where stage =4  and etap =2 group by purchaseRef) as s4_start ", 's4_start.purchaseRef = {{%purchase}}.id')                
            ->leftJoin("(Select count(id) as s4_endN, purchaseRef from {{%purchase_etap}} where stage =4 and etap =3 group by purchaseRef) as s4_end ", 's4_end.purchaseRef = {{%purchase}}.id')                
        ;
        
        $query->andWhere("( ifnull(s1_N,0)>0 AND ifnull(s2_N,0)>0  AND ifnull(s3_N,0)>0 AND (ifnull(s4_startN,0) >0  AND ifnull(s4_endN,0)=0 )  )");
        $queryU->andWhere("(  ifnull(s1_N,0)>0 AND ifnull(s2_N,0)>0  AND ifnull(s3_N,0)>0 AND  (ifnull(s4_startN,0) >0 AND ifnull(s4_endN,0)=0 )   )");
        
        break;

}
    
    
    
$query->union($queryU);

    
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
   /*echo "<pre>";
    print_r($query->createCommand()->getRawSql());
   echo "</pre>";*/
    $this->count = count($list);
    //$this->count = $countquery->createCommand()->queryScalar();
   } 


  
 public function getPurchTableProvider($params)
   {
    
    $this->preparePurchTableData($params);    
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	 
        'creationDate',
        'wareTitle',
        'sumWareCount', 
        'wareEd', 
        'zaprosId', 
        'refZakaz', 
        //'isActive',
        'zaprosDate', 
        'zaprosIsActive',
      /*  'variantValue', 
        'refSchet', 
        'schetNum', 
        'schetDate', 
        'orgTitle',
        'refOrg',
        'dateCreation', 
        'purchSchetRef',
        'purchRefOrg', 
        'isFinishedPurchase',  
        'purchSchetNum' , 
        'purchSchetDate', 
        'purchOrgTitle'   */       
            ],
            'defaultOrder' => [	'creationDate'=> SORT_DESC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/*****************/ 
 
 function printPurchaseTable ($provider, $model)
 {

 return \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            
            [
                'attribute' => 'creationDate',
				'label'     => 'Дата',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                 return $model['creationDate']; 
                }
            ],		
        
            [
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                /*Нет запроса - это только товар*/
                if (empty ($model['variantWareTitle']))  $showTitle = $model['wareTitle'];
                else $showTitle = "<b>".$model['variantWareTitle']."</b><br>(".mb_substr($model['wareTitle'],0,25,'utf-8')."..)"; 
                if (empty($model['zaprosId']))  $ret = $showTitle; 
                          
                  /*Есть закупка*/         
                if (! empty ($model['purchaseId']))                 
                    $ret = "<a href='#' onclick='openWin(\"\store/purchase&noframe=1&id=".$model['purchaseId']."#status\",\"storeWin\");'>".$showTitle."</a>";     
                 else                
                 {                     
                   $curUser=Yii::$app->user->identity; 
                  /*Только запрос*/  
                  if (!empty($model['zaprosId']))
                  {
                   if ($curUser->roleFlg & 0x0020 || $curUser->roleFlg & 0x00200) 
                       $ret = "<a href='#' onclick='openWin(\"\store/head-purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$showTitle ."</a>";     
                   else
                       $ret = "<a href='#' onclick='openWin(\"\store/purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$showTitle."</a>";     
                  }
                 }
                  return "<div style='width:300px;'>".$ret."</div>";
                }
            ],		

            [
                'attribute' => 'sumWareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'wareEd',
				'label'     => 'Ед.',
                'format' => 'raw',
 
            ],		

     
            [
                'attribute' => 'refZakaz',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                $strSql = 'SELECT formDate, isFormed, isActive, userFIO, title, 
                {{%schet}}.schetNum, {{%schet}}.schetDate, ifnull({{%schet}}.isReject,0) as schetReject 
                FROM ({{%zakaz}},{{%user}},{{%orglist}}) 
                LEFT JOIN {{%schet}} ON {{%schet}}.refZakaz = {{%zakaz}}.id  where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
                if(empty($dataList)) return "";                 
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret .= $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                if ( ($dataList[0]['isFormed'] == 0 && $dataList[0]['isActive'] == 0) || $dataList[0]['schetReject']) $ret = "<s>".$ret."</s>"; 
                
                
                
                return $ret;                
                }
            ],		

           [
                'attribute' => 'zaprosDate',
				'label'     => 'Запрос цены/<br>Цена',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    

                
                if (empty($model['zaprosId'])) 
                {
                    
                    $ret ="<a style='color:Green;' href='#' onclick=\"openWin('store/purchase-create-from-request&id=".$model['requestID']."','storeWin'); openSwitchWin('site/success');\"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                    $ret .= "&nbsp;&nbsp;&nbsp;&nbsp;<a style='color:Crimson;' href='#' onclick=\"openSwitchWin('store/rm-from-request&id=".$model['requestID']."'); \"> <span class='glyphicon glyphicon-remove' aria-hidden='true'></span> </a>";
                      return $ret;
                }                      
                $add ="";    

                
                if (!empty($model['variantSchetNum'])) 
                {
                $varSchet = $model['variantSchetNum']." от ".date("d.m", strtotime($model['variantSchetDate'])); 
                $add.=  "<div  style='padding:2px;width:80px;'>".number_format($model['variantValue'],2,'.','&nbsp;')." руб.<br>".$varSchet."</div>";
                }
                elseif(!empty($model['variantValue'])) $add.= number_format($model['variantValue'],2,'.','&nbsp;');

                $bg = "";                
                if ( empty ($model['purchaseId']) ) {                    
                    $bg = "background:Gray; color:White; font-weight:bold; padding:2px;";
                    if ($model['zaprosType'] == 1) $bg = "background:Blue; color:White; font-weight:bold; padding:2px;";
                }
                                
                if ($model['zaprosIsActive'] == 0) $add ="<br><font color='DarkGreen'> Завершен</font>";        

                $curUser=Yii::$app->user->identity; 
                if ($curUser->roleFlg & 0x0020)   
                    return "<div style='".$bg.";'><nobr><a style='".$bg.";' href='#' onclick='openWin(\"\store/head-purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$model['zaprosId']." от ".date('d.m', strtotime($model['zaprosDate']))."</a></nobr>".$add."</div>"; 
                       
                    return "<div style='".$bg.";'><nobr><a style='".$bg.";' href='#' onclick='openWin(\"\store/purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$model['zaprosId']." от ".date('d.m', strtotime($model['zaprosDate']))."</a></nobr>".$add."</div>";     
                }
            ],		

     /*       [
                'attribute' => 'variantValue', 
				'label'     => 'Цена',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {    
                if (empty ($model['variantValue']))  return "";
                
                if (!empty($model['variantSchetNum'])) 
                {
                $varSchet = $model['variantSchetNum']." от ".date("d.m", strtotime($model['variantSchetDate'])); 
                return  "<div  style='padding:2px;width:80px;'>".number_format($model['variantValue'],2,'.','&nbsp;')." руб.<br>".$varSchet."</div>";
                }

                
                return number_format($model['variantValue'],2,'.','&nbsp;');
                
                
                }

            ],	*/	
            
            
            
            [
                'attribute' => 'dateCreation',
				'label'     => 'Закупка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if ($model['zaprosType'] == 1) 
                    return "Мониторинг цен";
                
                if (empty($model['zaprosId'])) return "&nbsp;";   
                if (empty ($model['purchaseId'])) 
                {
                  if ($model['zaprosStatus'] == 2)     
                        return "<a class='btn btn-primary'  href='#' onclick=\"openWin('store/purchase-create&varlist=".$model['zaprosId'].",','storeWin'); openSwitchWin('site/success');\"> Создать </a>";
                   return "&nbsp;";
                }            

                $bg = "background:Gray; color:White; font-weight:bold; padding:2px;";

                return "<div style='".$bg.";'><nobr><a href='#' style='".$bg.";' onclick='openWin(\"\store/purchase&noframe=1&id=".$model['purchaseId']."#status\",\"storeWin\");'>".$model['purchaseId']." от ".date('d.m', strtotime($model['dateCreation']))."</a></nobr></div>";     
                }
            ],		

        
        

            [
                'attribute' => 'Счет',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                
                if (empty($model['zaprosId'])) return "&nbsp;";   
                
                if (!empty($model['purchSchetRef'])) 
                {
                $strSql = "select  schetNum , schetDate ,  orgTitle 
                from {{%supplier_schet_header}} Where id = :supplierShetRef";                
                $dataList = Yii::$app->db->createCommand($strSql, [':supplierShetRef' => $model['purchSchetRef'],])->queryAll();                                        
                if (count($dataList) > 0)
                return "<div  style='padding:2px;width:80px; color:DarkGreen;'>".$dataList[0]['schetNum']." от ".date("d.m", strtotime($dataList[0]['schetDate']))."</div>";    
                }
                               
                 return "&nbsp;";   
                }
            ],		


            [
                'attribute' => 'Исполнение',
				'label'     => 'Исполнение',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
           
                 if (empty($model['purchaseId'])) {
                 if (empty($model['zaprosId'])) return "&nbsp;";    
                 
                 if ($model['zaprosType'] == 1) 
                 {
                     /* Для мониторинга цены*/
                 $retVal = "";
                 switch ($model['zaprosStatus'])
                    {
                        
                        case 0: 
                            $retVal .="<div  style='padding:2px;width:85px;background:Yellow'>Ожидает одобрения</div>";
                            break;
                        case 1: 
                            $retVal .="<div  style='padding:2px;width:85px;'>В работе</div>";
                            break;
                        case 2: 
                            $retVal .="<div style='padding:2px;width:85px;'><b>Выполнен</b></div>";                           
                            break;
                        case 4: 
                            $retVal .="<div style='padding:2px;width:85px;background:DarkOrange;'>На доработке</div>";
                            break;
                       default:
                            $retVal .="<div style='padding:2px;width:85px;'>В работе</div>";
                            break;                       
                    }
                     
                  return "".$retVal."";    
                 }
                 
                 
                 $retVal = "Запрос<br>";
                 switch ($model['zaprosStatus'])
                    {
                        
                        case 0: 
                            $retVal .="В работе";
                            break;
                        case 1: 
                            $retVal .="<div  style='padding:2px;width:85px;background:Yellow'>На согласов.</div>";
                            break;
                        case 2: 
                            $retVal .="<div style='padding:2px;width:85px;'><b>Согласован</b></div>";
                           // return "<a class='btn btn-primary'  href='#' onclick=\"openWin('store/purchase-create&varlist=".$model['zaprosId'].",','storeWin'); openSwitchWin('site/success');\"> Создать </a>";
                            break;
                        case 4: 
                            $retVal .="<div style='padding:2px;width:85px;background:DarkOrange;'>На доработке</div>";
                            break;
                        case 8: 
                            $retVal .="<div style='padding:2px;width:85px;'>В закупке</div>";
                            break;
                       default:
                            $retVal .="<div style='padding:2px;width:85px;'>В работе</div>";
                            break;                       
                    }
                    
                    return "".$retVal.""; 
                 }                
    
                      
            $strSql = "Select count(id) as s1_startN from {{%purchase_etap}} where stage =:stage AND etap=:etap and purchaseRef= :purchaseRef";    
            $s1_startN = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':stage' => 1,':etap' => 1,])->queryScalar();                                                    
            $strSql = "Select count(id) as s1_startN from {{%purchase_etap}} where stage =:stage AND etap=:etap and purchaseRef= :purchaseRef";    
            $s1_finN   = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':stage' => 1,':etap' => 2,])->queryScalar();                                                    

            //return   "$s1_startN   $s1_finN ";
                
            if($s1_startN ==1 AND $s1_finN == 0)  return "Закупка: <div  style='padding:2px;width:75px;background:Yellow'>На согласов.</div>";    
            
            $strSql = "Select count(id) as s1_startN from {{%purchase_etap}} where stage =:stage AND etap=:etap and purchaseRef= :purchaseRef";    
            $s2_startN = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':stage' => 2,':etap' => 2,])->queryScalar();                                                    
            $strSql = "Select count(id) as s1_startN from {{%purchase_etap}} where stage =:stage AND etap=:etap and purchaseRef= :purchaseRef";    
            $s2_finN   = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':stage' => 2,':etap' => 3,])->queryScalar();                                                    
                
            if($s2_startN ==1 AND $s2_finN == 0)  return "Закупка:<div  style='padding:2px;width:75px;background:Yellow'>На согласов.</div>";    
            
                
                
                $etaps = array();
                $strSql = "SELECT ifnull(MAX(etap),0) from {{%purchase_etap}} where stage =:stage AND purchaseRef=:purchaseRef";
                for ($i=1; $i <=4; $i++)
                {
                  $etaps[$i] = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':stage' => $i,])->queryScalar();                                        
                }
                $strSql = "SELECT ifnull(COUNT(id),0) from {{%purchase_etap}} where etap =:etap AND stage =:stage  AND purchaseRef=:purchaseRef";
                
                //Согласование закупки
                $soglZin = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 1,':stage' => 1,])->queryScalar();  
                $soglZout = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 1,':stage' => 2,])->queryScalar();                                                        
                
                //Согласование счета
                $soglSin  = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 2,':stage' => 2,])->queryScalar();  
                $soglSout = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 2,':stage' => 3,])->queryScalar();                                                        
                
                //Согласование оплаты
                $reestrIn  = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 2,':stage' => 5,])->queryScalar();  
                $reestrOut = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 2,':stage' => 6,])->queryScalar();                                                        
                //Завершен
                $finit = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 3,':stage' => 4,])->queryScalar();                                                        
                //отказ
                $reject = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['purchaseId'],':etap' => 8,':stage' => 4,])->queryScalar();                                                        
                
                if($reject > 0)  return "Закупка: <div  style='padding:2px;width:75px; color: Crimson;'>Отказ</div>";         
                if($finit > 0 )  return "Закупка: <div  style='padding:2px;width:75px;'>Завершено</div>";         
                //if($soglZin > 0 AND $soglZout < 1)  return "<div  style='padding:2px;width:75px;background:Yellow'>На согласов.</div>";         
                //if($soglSin > 0 AND $soglSout < 1)  return "<div  style='padding:2px;width:75px;background:Yellow'>На согласов.</div>";                
                if($reestrIn > 0 AND $reestrOut < 1) return  "<div  style='padding:2px;width:85px;background:Yellow'>В оплате</div>";                

                if($etaps[4] > 0)
                {
                    return  "Закупка: <div  style='padding:2px;width:85px;'>Оформление</div>";                
                }
                if($etaps[3] > 0)
                {
                    return  "Закупка: <div  style='padding:2px;width:85px;'>Доставка</div>";                
                }
                if($etaps[2] > 0)
                {
                    return  "Закупка: <div  style='padding:2px;width:85px;'>Счет</div>";                
                }
                if($etaps[1] > 0)
                {
                    return  "Закупка: <div  style='padding:2px;width:85px;'>Формиров.</div>";                
                }
                
                 return "Закупка: Формируется";   
                }
                
            ],		

            

           [
                'attribute' => 'Коммент',
				'label'     => 'Коммент.',                
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {                    
                $note = "";
                if (!empty($model['purchaseNote']))$note .= $model['purchaseNote']." ";
                if (mb_strlen($note.'utf-8') < 25)
                {
                    if (!empty($model['zaprosNote']))$note .= $model['zaprosNote'];
                }
                $add ="";
                if (mb_strlen($note.'utf-8') > 40)$add ="...";
                return mb_substr($note,0,40,'utf-8').$add;
                }
            ],		
        
            [
                'attribute' => 'isFinishedPurchase',
				'label'     => 'Заверш.',
                'filter'=>array("1"=>"Все","2"=>"Нет","3"=>"Да",),
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {                    
				    if ($model['isFinishedPurchase'] >0 ){ $isFlg = true;}
					else                      { $isFlg = false;}
                    return  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);

                }
            ],		
     
        ],
    ]
	);

     
 }
 /***********************************/
 
public function getZaprosTableProvider($params)
   {
 
 $query  = new Query();
 $countquery  = new Query();
 
 $countquery->select ('COUNT(DISTINCT({{%purchase_zakaz}}.id))')-> from("{{%purchase_zakaz}}")
     ->leftJoin("(SELECT refPurchaseZakaz, curentValue as variantValue, refSchet, schetNum, schetDate, title as variantWareTitle,
                {{%supplier_schet_header}}.refOrg, {{%supplier_schet_header}}.orgTitle  
                FROM {{%purchase_variant}}, {{%supplier_schet_header}}, {{%warehouse}}
                where {{%purchase_variant}}.refSchet =  {{%supplier_schet_header}}.id and isActiveVariant = 1
                AND {{%warehouse}}.id =   {{%purchase_variant}}.refWare) as selectedVariant", "selectedVariant.refPurchaseZakaz = {{%purchase_zakaz}}.id")
     ->andWhere ("zaprosType = 1")     
     ; 
     
     
 $query->select ([
        '{{%purchase_zakaz}}.zakazDate as creationDate',
        '{{%purchase_zakaz}}.wareTitle',
        '{{%purchase_zakaz}}.wareCount', 
        '{{%purchase_zakaz}}.wareEd', 
        '{{%purchase_zakaz}}.id as zaprosId', 
        '{{%purchase_zakaz}}.refZakaz',  
        '{{%purchase_zakaz}}.isActive as zaprosIsActive',        
        '{{%purchase_zakaz}}.status as zaprosStatus',        
        '{{%purchase_zakaz}}.zakazNote as zaprosNote', 
        'relizeValue',    
        'variantValue', 
        'selectedVariant.refSchet as variantSchet', 
        'selectedVariant.schetNum as variantSchetNum', 
        'selectedVariant.schetDate as variantSchetDate', 
        'variantWareTitle',
        'selectedVariant.refOrg as variantRefOrg', 
        'selectedVariant.orgTitle as variantOrgTitle',         
      ])-> from("{{%purchase_zakaz}}")
     ->leftJoin("(SELECT refPurchaseZakaz, curentValue as variantValue, refSchet, schetNum, schetDate, title as variantWareTitle,
                {{%supplier_schet_header}}.refOrg, {{%supplier_schet_header}}.orgTitle  
                FROM {{%purchase_variant}}, {{%supplier_schet_header}}, {{%warehouse}}
                where {{%purchase_variant}}.refSchet =  {{%supplier_schet_header}}.id and isActiveVariant = 1
                AND {{%warehouse}}.id =   {{%purchase_variant}}.refWare) as selectedVariant", "selectedVariant.refPurchaseZakaz = {{%purchase_zakaz}}.id")
     ->andWhere ("zaprosType = 1")
     ->distinct()
     ;

/*
Отключим неодобренные для всех, кто не имеет права

*/     
    
    $curUser=Yii::$app->user->identity;                       
     if ($curUser->roleFlg & 0x0020|0x0100) 
     {
         $query->andWhere ("status >=0 ");
         $countquery->andWhere ("status >=0 ");
     
     }else
     {
         $query->andWhere ("status >0 ");
         $countquery->andWhere ("status >0 ");
     }
                  
                        
    if (($this->load($params) && $this->validate())) {
       
        $query->andFilterWhere(['or',
            ['like','wareTitle',$this->wareTitle],
            ['like','variantWareTitle',$this->wareTitle]]);

        $countquery->andFilterWhere(['or',
            ['like','wareTitle',$this->wareTitle],
            ['like','variantWareTitle',$this->wareTitle]]);

        if(!empty($this->zaprosStatus))
        {
            $status = $this->zaprosStatus-1;
            $query->andFilterWhere(['=', '{{%purchase_zakaz}}.status', $status]);
            $countquery ->andFilterWhere(['=', '{{%purchase_zakaz}}.status', $status]);
         }   
                        
     }
     
     
    if(empty($this->isFinishedPurchase)) $this->isFinishedPurchase = 2;
    switch ($this->isFinishedPurchase)    
    {
       case 2:
            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 1]);
            $countquery ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 1]);
       break;
  
       case 3:
            $query->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 0]);
            $countquery ->andFilterWhere(['=', 'ifnull({{%purchase_zakaz}}.isActive,1)', 0]);            
       break;

       default:
        //все
       break;
    }

    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();

      
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	 
        'creationDate',
        'wareTitle',
        'wareCount', 
        'wareEd', 
        'zaprosId', 
        'refZakaz',  
        'zaprosIsActive',        
        'zaprosStatus',        
        'zaprosNote',        
        'variantValue', 
        'variantSchet', 
        'variantSchetNum', 
        'variantSchetDate', 
        'variantWareTitle',
        'variantRefOrg', 
        'variantOrgTitle',         

            ],
            'defaultOrder' => [	'creationDate'=> SORT_DESC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
/*******************************/
/*
    
*/

public $puchaseRole=[
  0 => 'Товар',
  1 => 'Доставка',
  2 => 'Перемещение',
  3 => 'Услуги резки',
  4 => 'Прочее',
];

public function getPuchaseRoles()
{    
 return $this->puchaseRole;
}

public function getLinkSchetProvider($params)
   {
 
 $query  = new Query();
 $countquery  = new Query();
 
 $countquery->select ('COUNT(DISTINCT(lnk.id))')
     ->from("{{%purch_schet_lnk}} as lnk")      
     ->leftJoin("{{%supplier_schet_header}} as schet", "lnk.schetRef = schet.id")     
     ->leftJoin("{{%orglist}} as org", "org.id = schet.refOrg")     
    ; 
     
 $query->select ([
        'lnk.id', 
        'schet.id as schetRef',
        'schet.schetNum',
        'schet.schetDate',
        'schet.supplierRef1C',
        'org.title as orgTitle',    
        'purchRef',
        'purchRole',
        'purchSum'        
      ])
     ->from("{{%purch_schet_lnk}} as lnk")      
     ->leftJoin("{{%supplier_schet_header}} as schet", "lnk.schetRef = schet.id")     
     ->leftJoin("{{%orglist}} as org", "org.id = schet.refOrg")     
     ->distinct()
     ;
     $this->id = intval($this->id);
     $query     ->andWhere ("lnk.purchRef =".$this->id);
     $countquery->andWhere ("lnk.purchRef =".$this->id);

                        
    if (($this->load($params) && $this->validate())) {       
    }
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	 
            'd',
            'schetNum',
            'schetDate',
            'orgTitle',    
            'purchaseRole'        
            ],
            'defaultOrder' => [	'purchaseRole'=> SORT_ASC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   





public function getPurchaseControlProvider($params)
   {
 /*
 $query  = new Query();
 $countquery  = new Query();
     
$query->select ([
        '{{%supplier_wares_header}}.id', 
        'orgTitle',
        'ref1C',
        'requestDate',
      ])
     ->from("{{%supplier_wares_header}}")      
     ->distinct()
     ;*/


    $query  = new Query();
    $query->select ([
        'MAX({{%control_purch_content}}.id) as id',
        'orgINN',
        'orgKPP',
        'orgTitle',
        'ref1C',
        'purchDate',
        ])
         ->from("{{%control_purch_content}}")
         ->distinct()
         ->groupBy(['ref1C', 'purchDate']);

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT(ref1C) )")
            ->from("{{%control_purch_content}}")
            ;

    
    if (!empty($this->supplierRef))
    $query->andWhere("orgRef = ".$this->supplierRef);
    
     if (($this->load($params) && $this->validate())) {   
        
        $query->andFilterWhere(['like', 'ref1C', $this->ref1C]);
        $query->andFilterWhere(['like', 'orgTitle', $this->purchTitle]);
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        
     }

   if (empty($this->orgTitle)){               
     $this->orgTitle = $this->fltOrgTitle;
     $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
   }
   
     if (!empty($this->fromDate)){               
        $fromDT = strtotime($this->fromDate);   
        $from = date("Y-m-d",$fromDT );
        $query->andWhere(['>=', 'purchDate', $from]);
//        $countquery->andWhere(['>=', 'schetDate', $from]);
     }

     if (!empty($this->toDate)){               
        $toDT = strtotime($this->toDate);   
        $to = date("Y-m-d",$toDT );
        $query->andWhere(['<=', 'purchDate', $to]);
//        $countquery->andWhere(['<=', 'schetDate', $to]);
     }

//     $this->debug=$query->createCommand()->getRawSql();
    
    $list = $query->createCommand()->queryAll();
    $count = count($list);      
    $command = $query->createCommand();     
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	
        'id',     
        'orgTitle',
        'ref1C',
        'purchDate',
            ],
            'defaultOrder' => [	'id'=> SORT_DESC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

  /********/ 
public function getWareInControlProvider($params, $purchRole)
   {
 
 


 $query  = new Query();
 $countquery  = new Query();
 
 $countquery->select ('COUNT(DISTINCT(ware.id))')
     ->from("{{%control_purch_content}} as lnkWare")
     ->leftJoin("{{%purch_control_lnk}} as lnk", "lnk.controlRef = lnkWare.id")
     ->leftJoin("{{%control_purch_content}} as ware", "ware.ref1C = lnkWare.ref1C AND ware.purchDate = lnkWare.purchDate")

    ; 
     
        $query->select ([
        'ware.id', 
        'ware.purchTitle as wareTitle',
        'ware.purchSum as wareSumm',
        'ware.purchCount as wareCount',
        'ware.purchEd as wareEd',
        'ware.isAdditionWare',
        'lnk.purchRole',
        'ware.wareEdValueRef',
        'ware.wareCostValue',
        'ware.wareCostPrice',
        'ware.wareCostCount',
        'ware.wareCostAdd'
      ])
      
     ->from("{{%control_purch_content}} as lnkWare")
     ->leftJoin("{{%purch_control_lnk}} as lnk", "lnk.controlRef = lnkWare.id")
     ->leftJoin("{{%control_purch_content}} as ware", "ware.ref1C = lnkWare.ref1C AND ware.purchDate = lnkWare.purchDate")
     ->distinct()
     ;


     $this->id = intval($this->id);
     $query     ->andWhere ("lnk.purchRef =".$this->id);
     $countquery->andWhere ("lnk.purchRef =".$this->id);

    $purchRole = intval($purchRole);

     $query     ->andWhere ("lnk.purchRole =".$purchRole);
     $countquery->andWhere ("lnk.purchRole =".$purchRole);

                        
    if (($this->load($params) && $this->validate())) {       
    }
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	 
            'd',
            'ref1C',
            'purchDate',
            'orgTitle',    
            'purchRole'        
            ],
            'defaultOrder' => [	'purchRole'=> SORT_ASC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
   
    
 /****************************/
/**
ALTER TABLE `rik_control_purch_content` ADD COLUMN `wareEdValueRef` BIGINT DEFAULT 0;
ALTER TABLE `rik_control_purch_content` ADD COLUMN `wareCostValue` DOUBLE DEFAULT 0;
ALTER TABLE `rik_control_purch_content` ADD COLUMN `wareCostPrice` DOUBLE DEFAULT 0;
ALTER TABLE `rik_control_purch_content` ADD COLUMN `wareCostCount` DOUBLE DEFAULT 0;
ALTER TABLE `rik_control_purch_content` ADD COLUMN `wareCostAdd` DOUBLE DEFAULT 0;
ALTER TABLE `rik_control_purch_content` ADD COLUMN `isAdditionWare` TINYINT DEFAULT 0;
*/
public function prepareDocInControlProvider($params, $isMain)
   {
    
    $query  = new Query();
    $query->select ([         
            'doc.orgTitle as docOrgTitle',
            'supp.orgTitle as suppOrgTitle',
            'lnk.id',
            'lnk.controlRef',
            'lnk.docRef',
            'lnk.purchRole',
            'lnk.purchSum',
            'supp.ref1C',
            'supp.inNum',
            'supp.inDate',
            'doc.docIntNum',
            'doc.docOrigNum',
            'doc.docOrigDate',
            'doc.docURI'                
            ])
            ->from("{{%purch_control_lnk}} as lnk")                                   
            ->leftJoin(" {{%control_purch_content}} as supp", "supp.id = lnk.controlRef")
            ->leftJoin(" {{%documents}} as doc", "doc.id = lnk.docRef")                        
            ->distinct();
    
    $query->andWhere("lnk.purchRef = ".$this->id);
    
    
     if (($this->load($params) && $this->validate())) { 
      //  $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     }
     
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    
    $this->count = count($list);
   } 
  
    
 public function getDocInControlProvider($params)
   {
    
    $this-> prepareDocInControlProvider($params, true);    
    $pageSize = 50;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id'
                 ],
            'defaultOrder' => [	'id' => SORT_ASC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }       
/**/    
 }
 
