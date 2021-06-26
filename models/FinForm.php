<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;        
use app\models\SchetList;
use app\models\OplataList;
use app\models\SupplyList;
use app\models\UserList;
use app\models\OrgList;
use app\models\ReestrOplat;
use app\models\ReestrNorma;
use app\models\ReestrLnk;
use app\models\SupplierSchetHeaderList;
use app\models\Purchase;
use app\models\PurchesForm;
use app\models\TblBankExtract;
use app\models\TblSchetExtractLnk;

/**
 * FinForm  - модель финансовых операций
 */
class FinForm extends Model
{
	
	
	public $schetStatus; 
	public $title;
	public $userFIO;
	public $isConfirmed;
	public $schetDate;
	public $schetNum;	
	public $isOplata;	
	public $isSupply;	
    public $oplateType = 0;
    public $inReestr = 0;
    public $sdelkaNum;
    public $isActive = 0;
    public $reestExtData = array();
	
    public $count;
    public $command;
    public $query;
	
    public $orgTitle;

    public $userId = 0;
    public $oplateYear = 0;
    public $oplateMonth = 0;
    
    public $supplyYear = 0;
    public $supplyMonth = 0;
    
    public $day = 0;

    public $debug;
    
    public $y_from = 0;
    public $m_from = 0;
    public $y_to = 0;
    public $m_to = 0;
    public $fromDate="";
    public $toDate="";
    public $orgRef=0;
    public $id = 0;    
	
    public $reestrId= 0; 
    
    public $setMonth = 0;
    public $setDate =0;
    public $isSynced =0;
    
    public $refSuppSchet =0;
    public $suppSchetNum  ="";   
    public $suppSchetDate ="";  
    public $suppOrgTitle  =""; 
    public $suppSchetSum  ="";  

    public $wareListRef=0;    
    
   public $ref1C='';
   public $refSchet=0;


   /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataId;
    public $dataVal;

    
	public function rules()
    {
        return [
            [['schetStatus'], 'default'],
            [['supplyYear', 'supplyMonth', 'oplateYear', 'oplateMonth', 'orgTitle', 'schetNum', 'schetDate', 
            'isConfirmed','userFIO','title','isSupply', 'isOplata', 'oplateType', 'inReestr', 'sdelkaNum', 
            'recordId','dataType','dataVal','dataId',              
            'isActive','isSynced'], 'safe'],
        ];
    }
    
/**************************/
   public function getCfgValue($key)          
   {
      $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
               ':key' => intval($key),               
               ])->queryOne();  
               
     return $record['keyValue'];
   }
    
  /***********************************************/
public function loadSchetData($schetId)    
{
    
  $strSql = "SELECT {{%schet}}.*, {{%orglist}}.title from {{%schet}} left join {{%orglist}} on {{%schet}}.refOrg = {{%orglist}}.id
             where {{%schet}}.id = :id "  ;
    
   $schetData = Yii::$app->db->createCommand($strSql)
				->bindValue(':id',$schetId)
				->queryOne();  
   
   $schetData['summExtract'] =  Yii::$app->db->createCommand( 'SELECT sum(lnkSum) from  {{%schet_extract_lnk}}
        where
        {{%schet_extract_lnk}}.schetRef=:refSchet', [':refSchet' => $schetId])->queryScalar();
   return  $schetData;              
}
/********************/		
/* Добавим в форму реестр оплат - (разрешение на оплату)*/
public function addNewInReestr()
{ 
  $curUser=Yii::$app->user->identity;
 
  $record = new  ReestrOplat();           
  if (empty($record )) return -1;
  $record->formDate =  date('Y-m-d h:i');  
  $record->refManager = $curUser->id ;
  $record->save();    
  return $record->id;
}
/********************/		
/* Удалим из реестр оплат - (разрешение на оплату)*/
public function rmFromReestr($id)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->delete();    
  	
}
/********************/		
/* Пометим как завершеный в реестр оплат - (разрешение на оплату)*/
public function reestrFinit($id)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->isActive = 0;    
  $record->save();	

}



//редактирование
public function setReestrSetFormdate($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->formDate =  date('Y-m-d', strtotime($val));
  $record->save();    
  return $record->id;
}
//редактирование
public function setReestrSetOplatedate($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->oplateDate =  date('Y-m-d', strtotime($val));
  $record->save();    
   
   return $record->id;
  
}

public function setReestrSetNote($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->note =  $val;
  $record->save();    
  return $record->id;
}

public function setReestrSetOplateType($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->oplateType =  $val;
  $record->save();    
  return $record->id;
}
		
public function setReestrPlan($id, $val)
{    
  $record = ReestrNorma::FindOne($id);
  if (empty($record )) return -1;
  $record->plan =  $val;
  $record->save();    
  return $record->id;
}

        
public function setReestrSetSummOplate($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->summOplate =  floatval($val);
  $record->save();    
  
  if (!empty($record->refZakupka))
  {
    $purchModel = new PurchesForm();
    $purchModel -> setStageSatus($record->refZakupka, 's2e6',  date("Y-m-d") );
  }
    
  return $record->id;
}
		 
public function setReestrSetSummRequest($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->summRequest =  floatval($val);
  $record->save();    
  return $record->id;
}
		 
public function setReestrSetOrg($id, $val)
{    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;
  $record->orgTitle =  $val;
  $record->save();    
  return $record->id;
}
		 		 
public function setReestrSchet($id, $schetId)
{
  $schetRecord = SupplierSchetHeaderList::findOne($schetId);
  if (empty ($schetRecord) ) return -1;
    
  $record = ReestrOplat::FindOne($id);
  if (empty($record )) return -1;

  $strSql="SELECT  SUM(goodSumm) from {{%supplier_schet_content}}
		where schetRef = ".$schetId ;
  
  $record->orgTitle =  $schetRecord->orgTitle;
  $record->refOrg   =  $schetRecord->refOrg;
  $record->summRequest =  Yii::$app->db->createCommand($strSql)->queryScalar();	
  $record->refSchet = $schetId;
  $record->save();    
  
  
  return $record->id;
}

public function setReestrMultiSchet($schetListId)
{
   $schetList = explode(",",$schetListId);
   for ($i=0; $i < count($schetList); $i++)
   {
    if (empty($schetList[$i])) continue;
    $schetRecord = SupplierSchetHeaderList::findOne($schetList[$i]);
    if (empty ($schetRecord) ) return false;
    $record = new ReestrOplat();
    if (empty($record )) return false;
    
    $strSql="SELECT  SUM(goodSumm) from {{%supplier_schet_content}}
		where schetRef = ".$schetList[$i] ;
  
    $record->orgTitle =  $schetRecord->orgTitle;
    $record->formDate = date ('Y-m-d');
    $record->refOrg   =  $schetRecord->refOrg;
    $record->summRequest =  Yii::$app->db->createCommand($strSql)->queryScalar();	
    $record->refSchet = $schetList[$i] ;
    $record->save();    
   }       
}
       
public function setReestrOplata($reestrId, $oplateId)
{
   
    $record = new ReestrLnk ();
    $record->reestrId = $reestrId;
    $record->oplataId = $oplateId;
    $record->linkDate = date('Y-m-d');
    
     //Общая сумма в реестре
     $reestrSum= Yii::$app->db->createCommand("Select summRequest from  {{%reestr_oplat}} 
                 where {{%reestr_oplat}}.id =:reestrId",                  
                 [':reestrId' => $reestrId,])->queryScalar();
     
     // Уже оплачено по реестру
     $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $reestrId,])->queryScalar();                 
     
     // Остаток к оплате
     $remainSum = max($reestrSum-$oplSum,0);            
                 
    //Сумма в в платежке              
    $platSum= Yii::$app->db->createCommand("Select oplateSumm from {{%supplier_oplata}} 
                 where {{%supplier_oplata}}.id = :oplataId",                  
                 [':oplataId' => $oplateId,])->queryScalar();
                     
    //уже потрачено по платежке
    $usedSum= Yii::$app->db->createCommand("Select Sum(lnkOplate) from  {{%reestr_lnk}} 
                 where {{%reestr_lnk}}.oplataId =:oplataId",                  
                 [':oplataId' => $oplateId,])->queryScalar();

    // Остаток в платежке
     $remainPlat = max($platSum-$usedSum, 0);            
                 
                  
    $record->lnkOplate = min($remainSum, $remainPlat );
                    
    $record->save();    
    
    
    
  $recordReestr = ReestrOplat::FindOne($reestrId);
  if (empty($recordReestr )) return false;
  $recordReestr->summOplate =  0;
  $recordReestr->save();    

  
  // Пересчитаем сколько оплачено по реестру
     $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $reestrId,])->queryScalar();                 
    
  $notOplated = $reestrSum - $oplSum;
    
  if (!empty($recordReestr->refZakupka) && $notOplated <  0.01)
  {
 
    $purchModel = new PurchesForm();
    $purchModel -> setStageSatus($recordReestr->refZakupka, 's2e7',  date("Y-m-d") );
  }
  
  return true;
  
}


public function setReestrLnkOplateVal($lnkId, $val)
{
    $record =  ReestrLnk::FindOne($lnkId);
    if (empty($record) ) return;
    $record->lnkOplate = $val;
    $record->linkDate = date('Y-m-d');
    $record->save();    
    
}


 
public function detachReestrOplata($reestrId, $oplateId)
{
    $record =  ReestrLnk::findOne($oplateId);    
    if (empty($record)) return;
    $record->delete();    
}

public function getNormTitle() 
{
       
		$d=  Yii::$app->db->createCommand('SELECT DISTINCT id, normTitle FROM {{%reestr_norma}}  ORDER BY id')
					->queryAll();        

        return ArrayHelper::map($d, 'id', 'normTitle');
}

public function getEditTitle() 
{
       
		$d=  Yii::$app->db->createCommand('SELECT DISTINCT id, normTitle FROM {{%reestr_norma}} where isEdit=1 ORDER BY id')
					->queryAll();        

        return ArrayHelper::map($d, 'id', 'normTitle');
}

/*********************************************/
/********  Информационная часть  *************/
/*********************************************/

public function getSyncValue()
{
    $res = [
        'clientSchet' => '1970-01-01',
        'clientOplata' => '1970-01-01',
        'clientSupply' => '1970-01-01',
        'supplierSchet' => '1970-01-01',
        'supplierOplata' => '1970-01-01',
        'supplierSupply' => '1970-01-01',
        'warehouse' => '1970-01-01',
    ];
    
    $strCount = "SELECT id, keyValue from {{%config}} where id > 100 AND id < 200";			

    $list=  Yii::$app->db->createCommand($strCount)->queryAll();        

    $cnt = count($list);
    for ($i=0; $i<$cnt; $i++)    
    {
        switch($list[$i]['id'])
        {
           case 106:
            $res['clientSchet']=$list[$i]['keyValue'];
           break;
           case 107:
            $res['clientOplata']=$list[$i]['keyValue'];
           break;
           case 108:
            $res['clientSupply']=$list[$i]['keyValue'];
           break;
           case 110:
            $res['warehouse']=$list[$i]['keyValue'];
           break;
           case 116:
            $res['supplierSupply']=$list[$i]['keyValue'];
           break;
           case 117:
            $res['supplierSchet']=$list[$i]['keyValue'];
           break;
           case 118:
            $res['supplierOplata']=$list[$i]['keyValue'];
           break;
            
        }
    }
    
 return $res;
}
 public function  getLastSuplierSchet ()
 {
    $strSql = "SELECT max(id) from {{%supplier_schet_header}}";			
    $id=  Yii::$app->db->createCommand($strSql)->queryScalar();        
     
    $strSql = "SELECT supplierRef1C from {{%supplier_schet_header}} where id =".$id;			 
     return Yii::$app->db->createCommand($strSql)->queryScalar();        
 }


   /*цифры в листок*/
 public function  getLeafValue ()
  {

    /*Реестр оплат*/  
    
    //В работе            
    $strCount = "SELECT count({{%reestr_oplat}}.id) from {{%reestr_oplat}} where isActive = 1 ";			
	$leafValue['activeOplate'] =  Yii::$app->db->createCommand($strCount)->queryScalar();       	   

    //Новые счета: 
    $strCount = "SELECT count({{%supplier_schet_header}}.id) from {{%supplier_schet_header}} 
                 LEFT JOIN {{%reestr_oplat}} on {{%reestr_oplat}}.refSchet = {{%supplier_schet_header}}.id 
                 where {{%reestr_oplat}}.id IS NULL AND {{%supplier_schet_header}}.schetDate > '".date('Y-m-d', time()- 60*60*24*15)."'" ;			
	$leafValue['schetNew'] =  Yii::$app->db->createCommand($strCount)->queryScalar();       	   

    //Оплачено    
     $leafValue['oplateFinish'] = Yii::$app->db->createCommand("Select ifnull(Sum(oplateSumm),0) from {{%supplier_oplata}}, {{%reestr_lnk}}, {{%reestr_oplat}}  
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_oplat}}.id = {{%reestr_lnk}}.reestrId  
                 AND  MONTH({{%reestr_oplat}}.formDate) =:month  AND  YEAR({{%reestr_oplat}}.formDate) =:year",                  
                 [':month' => date('n'),':year' => date('Y'),])->queryScalar();      
       
  /*  echo Yii::$app->db->createCommand("Select ifnull(Sum(oplateSumm),0) from {{%supplier_oplata}}, {{%reestr_lnk}}, {{%reestr_oplat}}  
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_oplat}}.id = {{%reestr_lnk}}.reestrId  
                 AND  MONTH({{%reestr_oplat}}.formDate) =:month  AND  YEAR({{%reestr_oplat}}.formDate) =:year",                  
                 [':month' => date('n'),':year' => date('Y'),])->getRawSql();         */
       
	return $leafValue;   
	  
  }
/***********************************/






/*********************************************/
 
/********************/		
/* Список оплат - !!!!названия функций черезчур схожи!!!!  */	
public function prepareOplataReestrData($params, $schetId)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count({{%oplata}}.id)")
                  ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                 ;
                  
     $query->select([ '{{%oplata}}.id', 'oplateDate',  'oplateSumm', 'orgTitle', '{{%oplata}}.oplateNum', '{{%oplata}}.orgINN', '{{%oplata}}.orgKPP' ])
                   ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ;
                    
      if ($schetId > 0){
        $countquery->where('{{%oplata}}.refSchet = '.$schetId);
        $query->where('{{%oplata}}.refSchet = '.$schetId);
      }

      if ($schetId == 0){
        $countquery->where('{{%oplata}}.refSchet = 0');
        $query->where('{{%oplata}}.refSchet = 0');
      }
             
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }
/*********************************************************************************/
public function getOplataReestrProvider($params, $schetId)
   {

        $this->prepareOplataReestrData($params, $schetId);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'oplateDate',  
            'oplateSumm', 
            'orgTitle', 
            'oplateNum', 
            'orgINN', 
            'orgKPP'     
            ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
     
   public function oplataAttach($schetId, $oplataId)   
   {
	   $oplataRecord = OplataList::findOne($oplataId);
	   if (empty ($oplataRecord) ) return false;
	   $schetRecord = SchetList::findOne($schetId);
	   if (empty ($schetRecord) ) return false;
       
       $oplataRecord->refSchet =$schetRecord ->id;
       $oplataRecord->refZakaz =$schetRecord ->refZakaz;
       $oplataRecord->refOrg =$schetRecord ->refOrg;
        
       $oplataRecord->save();
    
     /* Сохраним сумму в счете - для упрощения запроса*/ 
		$strSql="update {{%schet}}  set {{%schet}}.summOplata = ifnull((SELECT SUM(oplateSumm) from {{%oplata}} where refSchet = {{%schet}}.id),0)
		where {{%schet}}.id = ".$schetId ;
       Yii::$app->db->createCommand($strSql)->execute();	
 
     return  true;
   }
    
   public function oplataDetach($oplataId)   
   {
	   $oplataRecord = OplataList::findOne($oplataId);
	   if (empty ($oplataRecord) ) return false;
       $schetId = $oplataRecord->refSchet;
       
       $oplataRecord->refSchet =0;
       $oplataRecord->refZakaz =0;
       $oplataRecord->save();

     /* Сохраним сумму в счете - для упрощения запроса*/ 
		$strSql="update {{%schet}}  set {{%schet}}.summOplata = ifnull((SELECT SUM(oplateSumm) from {{%oplata}} where refSchet = {{%schet}}.id),0)
		where {{%schet}}.id = ".$schetId ;
       Yii::$app->db->createCommand($strSql)->execute();	

       
     return  true;
   }
/*********************************************************************************/
/*********************************************************************************/
/*
*/
public function prepareExtractReestrData($params, $schetId)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count({{%bank_extract}}.id)")
                  ->from("{{%bank_extract}}")
                  ->leftJoin("{{%schet_extract_lnk}}", "{{%schet_extract_lnk}}.extractRef = {{%bank_extract}}.id")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
                 ;
                  
     $query->select([ 
                    '{{%bank_extract}}.id', 
                    'recordDate',  
                    '(creditSum) as oplateSumm', 
                    'title as orgTitle', 
                    'orgRef',
                    'creditOrgTitle',
                    '{{%bank_extract}}.docNum', 
                    '{{%bank_extract}}.description', 
                    '{{%orglist}}.orgINN', 
                    '{{%orglist}}.orgKPP', 
                    '{{%schet_extract_lnk}}.id as lnkRef',
                    '{{%schet_extract_lnk}}.schetRef',
                    '{{%schet_extract_lnk}}.lnkSum',
                    ])
                   ->from("{{%bank_extract}}")
                  ->leftJoin("{{%schet_extract_lnk}}", "{{%schet_extract_lnk}}.extractRef = {{%bank_extract}}.id")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
                  ;
                    
      if ($schetId > 0){
        $countquery->where('{{%schet_extract_lnk}}.schetRef = '.intval($schetId));
        $query->where('{{%schet_extract_lnk}}.schetRef = '.intval($schetId));
      }

/*      if ($schetId == 0){
        $countquery->where('ifnull({{%schet_extract_lnk}}.schetRef,0) <> '.intval($schetId)) );
        $query->where('ifnull({{%schet_extract_lnk}}.schetRef,0) = 0<> '.intval($schetId)) );
      }
*/
        $countquery->andWhere('{{%bank_extract}}.creditSum > 0 ');
        $query->andWhere('{{%bank_extract}}.creditSum > 0 ');

             
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }
    
/*********************************************************************************/
public function getExtractReestrProvider($params, $schetId)
   {

        $this->prepareExtractReestrData($params, $schetId);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                'id', 
                'recordDate',  
                'oplateSumm', 
                'orgTitle', 
                'creditOrgTitle',
                'docNum', 
     
            ],
            'defaultOrder' => ['recordDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

   public function extractAttach($schetId, $oplataId)   
   {
       $schetId = intval($schetId);
       $oplataId= intval($oplataId);
       
       $ret= [
          'res' => false,
          'schetRef' => $schetId,
          'extractRef' => $oplataId,
          'val'       => 0
       ];
       
       $extractRecord = TblBankExtract::findOne($oplataId);
       if (empty ($extractRecord)) return $ret;
       
	   $lnkRecord = TblSchetExtractLnk::findOne([
       'extractRef' => $oplataId,
       'schetRef'   => $schetId       
       ]);
	   if (empty ($lnkRecord) ) 
       $lnkRecord = new TblSchetExtractLnk ();       
       if (empty ($lnkRecord) ) return $ret;
       
       $lnkRecord -> extractRef = $oplataId;
       $lnkRecord -> schetRef   = $schetId;       
       $lnkRecord -> lnkSum     = $extractRecord ->debetSum+ $extractRecord ->creditSum;
       $lnkRecord -> save();
       
       
	   $schetRecord = SchetList::findOne($schetId);
	   if (empty ($schetRecord) ) return $ret;
       
       //$extractRecord->refSchet =$schetRecord ->id;
       if (empty($extractRecord->orgRef)){
           $extractRecord->orgRef =$schetRecord ->refOrg;
           $extractRecord->save();
       }

       $ret['val'] = $lnkRecord -> lnkSum;
       $ret['res'] = true; 
     return  $ret;
   }
    
   public function extractDetach($schetId, $oplataId)   
   {

       $schetId = intval($schetId);
       $oplataId= intval($oplataId);
       $ret= [
          'res' => false,
          'schetRef' => $schetId,
          'extractRef' => $oplataId,
          'val'       => 0
       ];

	   $lnkRecord = TblSchetExtractLnk::findOne([
       'extractRef' => $oplataId,
       'schetRef'   => $schetId       
       ]);
       if (empty ($lnkRecord) ) return $ret;
       $lnkRecord -> delete();
       $ret['res'] = true; 
     return  $ret;
   }

   public function saveExtractLnkData()   
   {
       $this->recordId = intval( $this->recordId);
       $this->dataVal   = (float)str_replace(',', '.',$this->dataVal);    
       $ret= [
          'res' => false,
          'recordId' => $this->recordId,
          'dataVal' => $this->dataVal,
          'val'       => $this->dataVal,
       ];

	   $lnkRecord = TblSchetExtractLnk::findOne($this->recordId);
       if (empty ($lnkRecord) ) return $ret;
       $lnkRecord -> lnkSum = $this->dataVal;
       $lnkRecord -> save();
       $ret['val'] = $lnkRecord -> lnkSum; 
       $ret['res'] = true; 
     return  $ret;
   }


/********************/		
/* Список поставок*/	
public function prepareSupplyReestrData($params, $schetId)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count({{%supply}}.id)")
                  ->from("{{%supply}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supply}}.refOrg")
                 ;
                  
     $query->select([ '{{%supply}}.id', 'supplyDate',  'supplySumm', 'supplyGood', 'supplyCount', 'orgTitle', '{{%supply}}.supplyNum', '{{%supply}}.orgINN', '{{%supply}}.orgKPP'	 ])->from("{{%oplata}}")
                  ->from("{{%supply}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supply}}.refOrg")
                  ;
                    
      if ($schetId > 0){
        $countquery->where('{{%supply}}.refSchet = '.$schetId);
        $query->where('{{%supply}}.refSchet = '.$schetId);
      }

      if ($schetId == 0){
        $countquery->where('{{%supply}}.refSchet = 0');
        $query->where('{{%supply}}.refSchet = 0');
      }
             
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }

public function getSupplyReestrProvider($params, $schetId)
   {

        $this->prepareSupplyReestrData($params, $schetId);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'supplyDate',  
            'supplySumm', 
            'supplyGood', 
            'supplyCount', 
            'orgTitle', 
            'supplyNum',
            'orgINN', 
            'orgKPP',
            'supplyGood', 
            'supplyCount'            
            ],
            'defaultOrder' => ['supplyDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
     
   public function supplyAttach($schetId, $supplyId)   
   {
	   $supplyRecord = SupplyList::findOne($supplyId);
	   if (empty ($supplyRecord) ) return false;
	   $schetRecord = SchetList::findOne($schetId);
	   if (empty ($schetRecord) ) return false;
       
       $supplyRecord->refSchet =$schetRecord ->id;
       $supplyRecord->refZakaz =$schetRecord ->refZakaz;
       $supplyRecord->refOrg =$schetRecord ->refOrg;
        
       $supplyRecord->save();
    
     /* Сохраним сумму в счете - для упрощения запроса*/ 
		$strSql="update {{%schet}}  set {{%schet}}.summSupply = ifnull((SELECT SUM(supplySumm) from {{%supply}} where refSchet = {{%schet}}.id),0)
		where {{%schet}}.id = ".$schetId ;
       Yii::$app->db->createCommand($strSql)->execute();	
 
     return  true;
   }
    
   public function supplyDetach($supplyId)   
   {
	   $supplyRecord = SupplyList::findOne($supplyId);
	   if (empty ($supplyRecord) ) return false;
       $schetId = $supplyRecord->refSchet;
       
       $supplyRecord->refSchet =0;
       $supplyRecord->refZakaz =0;
       $supplyRecord->save();

     /* Сохраним сумму в счете - для упрощения запроса*/ 
		$strSql="update {{%schet}}  set {{%schet}}.summSupply = ifnull((SELECT SUM(supplySumm) from {{%supply}} where refSchet = {{%schet}}.id),0)
		where {{%schet}}.id = ".$schetId ;
       Yii::$app->db->createCommand($strSql)->execute();	

       
     return  true;
   }
   
  /***********************************************/
   public function setConfirmStatus($schetId, $status)
   {
	   $schetRecord = SchetList::findOne($schetId);
	   if (empty ($schetRecord) ) return false;
	   $curUser=Yii::$app->user->identity;		 		
	   
	   $schetRecord->isConfirmed = $status;
	   $schetRecord->confurmUserRef = $curUser->id;
	   $schetRecord->confirmDate = date("Y-m-d h:i:s", time());
	   $schetRecord-> save();
	   return true;
   }
	
   public function getSchetStateProvider($params)
   {
   
     $query  = new Query();
	 $countquery  = new Query();	 

	 
	 $countquery->select("count({{%schet}}.id)")
			->from("{{%schet}}") 
			->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
			->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%schet}}.refOrg");	

	
	 $query->select("{{%orglist}}.title, userFIO, {{%schet}}.id as schetId, {{%schet}}.schetNum, {{%schet}}.summOplata, {{%schet}}.summSupply,  {{%schet}}.schetDate, {{%schet}}.schetSumm, {{%schet}}.ref1C, {{%schet}}.refZakaz, {{%schet}}.isConfirmed")
			->from("{{%schet}}") 
			->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
			->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%schet}}.refOrg");	

	

	
   	if (($this->load($params) && $this->validate())) 
	{
     
		$query->andFilterWhere(['like', 'userFIO', $this->userFIO]);		
		$countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);		

		$query->andFilterWhere(['like', 'title', $this->title]);		
		$countquery->andFilterWhere(['like', 'title', $this->title]);		

		$query->andFilterWhere(['like', 'schetNum', $this->schetNum]);		
		$countquery->andFilterWhere(['like', 'schetNum', $this->schetNum]);		

		$query->andFilterWhere(['=', 'isConfirmed', $this->isConfirmed]);		
		$countquery->andFilterWhere(['=', 'isConfirmed', $this->isConfirmed]);			 

		$query->andFilterWhere(['>=', '{{%schet}}.schetDate', date("Y-m-d", strtotime($this->schetDate))]);		
		$countquery->andFilterWhere(['>=', '{{%schet}}.schetDate', date("Y-m-d", strtotime($this->schetDate))]);				
	
		if ($this->isOplata ==1)
		{
		$query->andFilterWhere(['>', '{{%schet}}.summOplata', 0]);		
		$countquery->andFilterWhere(['>', '{{%schet}}.summOplata', 0]);		
		}	
		if ($this->isOplata ==0)
		{
		$query->andFilterWhere(['=', '{{%schet}}.summOplata', 0]);		
		$countquery->andFilterWhere(['=', '{{%schet}}.summOplata', 0]);		
		}
	
		if ($this->isSupply ==1)
		{
		$query->andFilterWhere(['>', '{{%schet}}.summSupply', 0]);		
		$countquery->andFilterWhere(['>', '{{%schet}}.summSupply', 0]);		
		}	
		if ($this->isSupply ==0)
		{
		$query->andFilterWhere(['=', '{{%schet}}.summSupply', 0]);		
		$countquery->andFilterWhere(['=', '{{%schet}}.summSupply', 0]);		
		}

		
    }
	else
	{
	
	if (empty($this->isConfirmed) )$this->isConfirmed = 0;
	
		$query->andFilterWhere(['=', 'isConfirmed', $this->isConfirmed]);		
		$countquery->andFilterWhere(['=', 'isConfirmed', $this->isConfirmed]);			 

	if (empty($this->schetDate))$this->schetDate = date ("d.m.Y", time()-60*24*60*60);	
		$query->andFilterWhere(['>=', '{{%schet}}.schetDate', date("Y-m-d", strtotime($this->schetDate))]);		
		$countquery->andFilterWhere(['>=', '{{%schet}}.schetDate', date("Y-m-d", strtotime($this->schetDate))]);				
	
	}
		
	    $command = $query->createCommand();	
	    $count = $countquery->createCommand()->queryScalar();
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'title',
			'schetNum', 
			'schetDate', 
			'schetSumm', 
			'isConfirmed',
			'userFIO',			
			],
			'defaultOrder' => [	'schetDate' => SORT_ASC ],
			],
		]);
	return $provider;
   }   
/**********************/


/********************/		
/* Список оплат */	

public function removeOplata($oplataId, $refSchet)
    {
      
		$strSql="DELETE FROM {{%oplata}}  where   id = :id ";
				Yii::$app->db->createCommand($strSql)
                ->bindValue(':id',$oplataId)
				->execute();		

    if ($refSchet > 0)
    {    
        $strSql="update {{%schet}} as a set a.summOplata = ifnull((SELECT SUM(oplateSumm) from {{%oplata}} where refSchet = a.id),0)
            where a.id = :refSchet";	                
		Yii::$app->db->createCommand($strSql)
				->bindValue(':refSchet',$refSchet)
				->execute(); 
	 
		$strSql="UPDATE {{%schet}} set isOplata = 0 where ifnull(schetSumm,0) <= ifnull(summOplata,0) and id=:refSchet";
		Yii::$app->db->createCommand($strSql)
            ->bindValue(':refSchet',$refSchet)
			->execute();		
      
    }
   
    }

public function prepareOplataListData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%oplata}}.id)")
                  ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                 ;
                  
     $query->select([ '{{%oplata}}.id', 'YEAR(oplateDate) as oplateYear','MONTH(oplateDate) as oplateMonth', 'oplateDate',  'oplateSumm', 'orgTitle',
     '{{%oplata}}.oplateNum', '{{%oplata}}.orgINN', '{{%oplata}}.orgKPP', '{{%oplata}}.refSchet', '{{%schet}}.schetNum', '{{%schet}}.schetDate', 'userFIO', '{{%orglist}}.title'	 ])
                   ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                  ->distinct
                  ;
                  
                     
      
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
     }
             
     if($this->setDate == 0)
     {
     if ($this->oplateYear == 0){$this->oplateYear = date('Y');}      
     if ($this->setMonth > 0) $this->oplateMonth = $this->setMonth;
     if ($this->oplateMonth == 0){$this->oplateMonth = date('n')-1; }
      
     $countquery->andFilterWhere(['=', 'YEAR(oplateDate)', $this->oplateYear]);
     $query->andFilterWhere(['=','YEAR(oplateDate)', $this->oplateYear]);
 
     $countquery->andFilterWhere(['=', 'MONTH(oplateDate)', $this->oplateMonth]);
     $query->andFilterWhere(['=','MONTH(oplateDate)', $this->oplateMonth]);
     }
     else
     {
        $this->setDate = date('Y-m-d', strtotime($this->setDate));
        $this->oplateYear= date('Y', strtotime($this->setDate));
        $this->oplateMonth= date('n', strtotime($this->setDate));
        
        $countquery->andFilterWhere(['=', 'oplateDate', $this->setDate]);
        $query->andFilterWhere(['=','oplateDate', $this->setDate]);
     } 
     
      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
    
    public function getOplataListData($params)
    {        
        $this->prepareOplataListData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/OrgActivityData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Сумма"),
        iconv("UTF-8", "Windows-1251","Дата платежа"),
        iconv("UTF-8", "Windows-1251","Номер платежа"),
        iconv("UTF-8", "Windows-1251","Плательщик"),        
        iconv("UTF-8", "Windows-1251","Привязан"), 
        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['oplateSumm'],2,'.','')), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['oplateDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['oplateNum']),                 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']." ИНН: ".$dataList[$i]['orgINN']." КПП:".$dataList[$i]['orgKPP']), 
            iconv("UTF-8", "Windows-1251", "счет № ".$dataList[$i]['schetNum']." от: ".$dataList[$i]['schetDate']." клиент: ".$dataList[$i]['title']." Менеджер: ".$dataList[$i]['userFIO']),  
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
public function getOplataListProvider($params)
   {

        $this->prepareOplataListData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'oplateDate',  
            'oplateSumm', 
            'orgTitle', 
            'oplateNum', 
            'orgINN', 
            'orgKPP' ,
            'oplateMonth',
            'oplataYear',
            'refSchet'
            ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

/**********************************/
/******** Выгрузки ****************/
/**********************************/

public function fixPeriod()
{

$m = date('n');
$y = date('Y');

  if ($this->m_from < 1 || $this->m_from > 12) $this->m_from = $m;
  if ($this->m_to   < 1 || $this->m_to   > 12) $this->m_to = $m;

  if ($this->y_from < 1970 || $this->y_from > 3000) $this->y_from = $y;
  if ($this->y_to   < 1970 || $this->y_to   > 3000) $this->y_to = $y;
        

}

/*Поступление оплат от клиентов */

public function prepareOplataSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
    
     
     $countquery->select ("count(distinct {{%oplata}}.id)")
                  ->from("{{%oplata}}")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                 ;
                  
     $query->select([ 'YEAR(oplateDate) as oplateYear','MONTH(oplateDate) as oplateMonth',  'oplateDate',  'oplateSumm', 'orgTitle', '{{%oplata}}.schetNum', '{{%oplata}}.schetDate',  '{{%oplata}}.orgTitle',  
     '{{%oplata}}.oplateNum', '{{%oplata}}.orgINN', '{{%oplata}}.orgKPP', '{{%oplata}}.refSchet', 'userFIO', 'contactPhone','contactEmail','contactFIO' ])
                   ->from("{{%oplata}}")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ->distinct
                  ;      

    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);                      

        
     }

//     if ($this->oplateYear == 0){$this->oplateYear = date('Y');}      
     //if ($this->oplateMonth == 0){$this->oplateMonth = date('n')-1; }
      
     if ($this->setDate !=0)
     {
        $this->setDate = date('Y-m-d', strtotime($this->setDate));
        $countquery->andFilterWhere(['=', 'oplateDate', $this->setDate]);
        $query->andFilterWhere(['=','oplateDate', $this->setDate]);
     }else
     {     
     
     $query->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);    
     
     
/*     $countquery->andFilterWhere(['<=', 'YEAR(oplateDate)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR(oplateDate)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR(oplateDate)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR(oplateDate)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH(oplateDate)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH(oplateDate)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH(oplateDate)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH(oplateDate)', $this->m_from]);*/
     }
     
     
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
    
    public function getOplataSrcData($params)
    {        
        $this->prepareOplataSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/OplataSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата платежа"),
        iconv("UTF-8", "Windows-1251","Номер платежа"),
        iconv("UTF-8", "Windows-1251","Счет №"),
        iconv("UTF-8", "Windows-1251","Счет дата"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Плательщик"),        
        iconv("UTF-8", "Windows-1251","ИНН"),        
        iconv("UTF-8", "Windows-1251","КПП"),        
        
        iconv("UTF-8", "Windows-1251","Привязан"), 
        iconv("UTF-8", "Windows-1251","Менеджер"), 

        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        
        
        
        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['oplateDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['oplateNum']),  
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetNum']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetDate']), 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['oplateSumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 
            
            iconv("UTF-8", "Windows-1251", $dataList[$i]['refSchet']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['userFIO']),
            
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
public function getOplataSrcProvider($params)
   {

    
       
        $this->prepareOplataSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'oplateDate',  
            'oplateSumm', 
            'orgTitle', 
            'oplateNum', 
            'orgINN', 
            'orgKPP' ,
            'oplateMonth',
            'oplataYear',
            'refSchet',
            'schetDate',
            'schetNum',
            'userFIO'
            ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   


/*Отпуск товара клиентам */

public function prepareSupplySrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supply}}.id)")
                  ->from("{{%supply}}")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%supply}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supply}}.refOrg")
                 ;
                  
     $query->select([ 'YEAR(supplyDate) as supplyYear','MONTH(supplyDate) as supplyMonth',  'supplyDate',  'supplySumm', 
     'supplyCount','supplyEd',    'supplyGood',  'contactPhone','contactEmail','contactFIO', 
     'orgTitle', '{{%supply}}.schetNum', '{{%supply}}.schetDate',  '{{%supply}}.orgTitle',  
     '{{%supply}}.supplyNum', '{{%supply}}.orgINN', '{{%supply}}.orgKPP', '{{%supply}}.refSchet', 'userFIO'])
                  ->from("{{%supply}}")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%supply}}.refSchet")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%schet}}.refManager")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supply}}.refOrg")                  
                  ->distinct
                  ;      

                  
    if (!empty($this->wareListRef)){
    
             $query->leftJoin("{{%ware_names}}", "{{%ware_names}}.id = {{%supply}}.wareNameRef");
        $countquery->leftJoin("{{%ware_names}}", "{{%ware_names}}.id = {{%supply}}.wareNameRef");
    
        $countquery->andWhere(['=','{{%ware_names}}.wareListRef', $this->wareListRef]);
             $query->andWhere(['=','{{%ware_names}}.wareListRef', $this->wareListRef]);    
    }        
    
              
    if (!empty($this->orgRef)){
    
        $countquery->andWhere(['=','{{%supply}}.refOrg', $this->orgRef]);
             $query->andWhere(['=','{{%supply}}.refOrg', $this->orgRef]);    
         $orgRec=OrgList::findOne($this->orgRef);
         if(!empty($orgRec))  $this->orgTitle=$orgRec->title;
    }        
    
    
    
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);                      
        
     }

     if ($this->setDate !=0)
     {
     
        $this->fromDate = date('Y-m-d', strtotime($this->fromDate));
        $this->toDate = date('Y-m-d', strtotime($this->toDate));
        $countquery->andFilterWhere(['>=', 'supplyDate', $this->fromDate]);
        $query->andFilterWhere(['>=','supplyDate', $this->fromDate]);
        $countquery->andFilterWhere(['<=', 'supplyDate', $this->toDate]);
        $query->andFilterWhere(['<=','supplyDate', $this->toDate]);
        
        
     }else
     {     

     $query->andFilterWhere(['<=','supplyDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','supplyDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','supplyDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','supplyDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
 /*    $countquery->andFilterWhere(['<=', 'YEAR(supplyDate)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR(supplyDate)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR(supplyDate)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR(supplyDate)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH(supplyDate)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH(supplyDate)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH(supplyDate)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH(supplyDate)', $this->m_from]);
*/
     
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();
     }
      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();
     
     
     
    }
    
    
    public function getSupplySrcData($params)
    {        
        $this->prepareSupplySrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/SupplySrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата поставки"),
        iconv("UTF-8", "Windows-1251","Номер поставки"),
        iconv("UTF-8", "Windows-1251","Счет №"),
        iconv("UTF-8", "Windows-1251","Счет дата"),
        
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","К-во"),
        iconv("UTF-8", "Windows-1251","Ед. изм"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Клиент"),        
        iconv("UTF-8", "Windows-1251","ИНН"), 
        iconv("UTF-8", "Windows-1251","КПП"), 
        
        iconv("UTF-8", "Windows-1251","Привязан"), 
        iconv("UTF-8", "Windows-1251","Менеджер"), 
        
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        

        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyNum']),                 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetNum']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetDate']), 
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyGood']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyCount']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyEd']),                 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['supplySumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 
            
            iconv("UTF-8", "Windows-1251", $dataList[$i]['refSchet']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['userFIO']),
            
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
public function getSupplySrcProvider($params)
   {

    
       
        $this->prepareSupplySrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'supplyDate',  
            'supplySumm', 
            'orgTitle', 
            'oplateNum', 
            'orgINN', 
            'orgKPP' ,
            'supplyMonth',
            'supplyYear',
            'refSchet',
            'schetDate',
            'schetNum',
            'userFIO',
            'supplyCount',
            'supplyEd',   
            'supplyGood',  
            ],
            'defaultOrder' => ['supplyDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
   
/***************************************************/
public function loadSuppSchet()
{
    $record = SupplierSchetHeaderList::findOne($this->refSuppSchet);
    if (empty($record))return;
    $this->suppSchetNum=$record->schetNum;
    $this->suppSchetDate=$record->schetDate;
    
    $strSql="SELECT title from {{%orglist}} where id=:id";
    $this->suppOrgTitle = Yii::$app->db->createCommand($strSql)
				->bindValue(':id',$record->refOrg)->queryScalar();  
    
    $strSql="SELECT SUM(goodSumm) from {{%supplier_schet_content}} where schetRef=:id";            
    $this->suppSchetSum = Yii::$app->db->createCommand($strSql)
				->bindValue(':id',$record->id)->queryScalar();              
                
}
/*Оплата поставщикам */
public function prepareSupplierOplataSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supplier_oplata}}.id)")
                  ->from("{{%supplier_oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supplier_oplata}}.refOrg")
                 ;
                  
     $query->select([ 'YEAR(oplateDate) as oplateYear','MONTH(oplateDate) as oplateMonth',  'oplateDate',  'oplateSumm', 'orgTitle', 
     '{{%supplier_oplata}}.sdelkaNum', '{{%supplier_oplata}}.sdelkaDate',  'contactPhone','contactEmail','contactFIO',
     '{{%supplier_oplata}}.orgTitle',  '{{%supplier_oplata}}.orgINN', '{{%supplier_oplata}}.orgKPP', '{{%supplier_oplata}}.refOrg'])
                   ->from("{{%supplier_oplata}}")
                   ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supplier_oplata}}.refOrg")
                  ->distinct
                  ;      

    $this->refSuppSchet=intval($this->refSuppSchet);
    if ($this->refSuppSchet > 0)
    {
        $query->andWhere(['=', 'supplierSchetRef', $this->refSuppSchet]);
        $countquery->andWhere(['=', 'supplierSchetRef', $this->refSuppSchet]);                      
        $this->loadSuppSchet();
    }

    if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
     }

    if ($this->setDate !=0)
     {
        $this->setDate = date('Y-m-d', strtotime($this->setDate));
        $countquery->andFilterWhere(['=', 'oplateDate', $this->setDate]);
        $query->andFilterWhere(['=','oplateDate', $this->setDate]);
     }else
     {     
     

    if ($this->refSuppSchet == 0){
     $query->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','oplateDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','oplateDate',  $this->y_from."-".$this->m_from."-"."01"]);    
    }
    
    }     
/*     $countquery->andFilterWhere(['<=', 'YEAR(oplateDate)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR(oplateDate)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR(oplateDate)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR(oplateDate)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH(oplateDate)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH(oplateDate)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH(oplateDate)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH(oplateDate)', $this->m_from]);*/
      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
   
   
    public function getSupplierOplataSrcData($params)
    {        
        $this->prepareSupplierOplataSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/SupplierOplataSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата платежа"),
        iconv("UTF-8", "Windows-1251","Счет №"),
        iconv("UTF-8", "Windows-1251","Счет дата"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Контрагент"),        
        iconv("UTF-8", "Windows-1251","ИНН"),        
        iconv("UTF-8", "Windows-1251","КПП"),        

        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        
        
        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['oplateDate']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['sdelkaNum']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['sdelkaDate']), 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['oplateSumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
public function getSupplierOplataSrcProvider($params)
   {
        $this->prepareSupplierOplataSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'oplateDate',  
            'oplateSumm', 
            'orgTitle', 
            'orgINN', 
            'orgKPP' ,
            'oplateMonth',
            'oplataYear',
            'refSchet',
            'sdelkaDate',
            'sdelkaNum',            
            ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   


/*Отпуск товара клиентам */

public function prepareSupplierWareSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supplier_wares}}.id)")
                  ->from("{{%supplier_wares}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supplier_wares}}.refOrg")
                 ;
                  
     $query->select([ 'YEAR(requestDate) as supplyYear','MONTH(requestDate) as supplyMonth',  'requestDate',  'wareSumm', 
     'wareCount','wareEd',    'wareTitle', 'contactPhone','contactEmail','contactFIO', 
     'orgTitle', 'requestNum', 'requestDate',  '{{%supplier_wares}}.orgTitle',  
      '{{%supplier_wares}}.orgINN', '{{%supplier_wares}}.orgKPP'])
                  ->from("{{%supplier_wares}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supplier_wares}}.refOrg")
                  ->distinct
                  ;      

    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
        
     }

     
     $query->andFilterWhere(['<=','requestDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','requestDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','requestDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','requestDate',  $this->y_from."-".$this->m_from."-"."01"]);    
     

/*     $countquery->andFilterWhere(['<=', 'YEAR(requestDate)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR(requestDate)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR(requestDate)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR(requestDate)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH(requestDate)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH(requestDate)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH(requestDate)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH(requestDate)', $this->m_from]);*/

     
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();
    }

   public function getSupplierWaresSrcData($params)
    {        
        $this->prepareSupplierWareSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/SupplierWaresSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата счета"),
        iconv("UTF-8", "Windows-1251","Номер счета"),
        
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","К-во"),
        iconv("UTF-8", "Windows-1251","Ед. изм"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Поставщик"),        
        iconv("UTF-8", "Windows-1251","ИНН"), 
        iconv("UTF-8", "Windows-1251","КПП"), 
        
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        

        
        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['requestDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['requestNum']),                 
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['wareTitle']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['wareCount']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['wareEd']),                 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['wareSumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 
 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
 
 
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
public function getSupplierWaresSrcProvider($params)
   {

        $this->prepareSupplierWareSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'wareSumm', 
            'wareCount',
            'wareEd',    
            'wareTitle',  
            'orgTitle', 
            'requestNum', 
            'requestDate',  
            ],
            'defaultOrder' => ['requestDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
/***************************************************/
/*Счета от поставщиков */
public function prepareSupplierSchetSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supplier_schet_header}}.id)")
                  ->from("{{%supplier_schet_header}}, {{%supplier_schet_content}}")
                  ->where("{{%supplier_schet_content}}.schetRef = {{%supplier_schet_header}}.id")
                  ->distinct
                 ;
                  
     $query->select([ '{{%supplier_schet_header}}.id', 'YEAR({{%supplier_schet_header}}.schetDate) as schetYear','MONTH({{%supplier_schet_header}}.schetDate) as schetMonth',  
     '{{%supplier_schet_header}}.schetDate',  '{{%supplier_schet_header}}.schetNum', 'contactPhone','contactEmail','contactFIO',
    'goodTitle', 'goodCount', 'goodEd', 'goodSumm', '{{%supplier_schet_header}}.supplierRef1C',
     '{{%supplier_schet_header}}.orgTitle',  '{{%supplier_schet_header}}.orgINN', '{{%supplier_schet_header}}.orgKPP', '{{%supplier_schet_header}}.refOrg'])
                   ->from("({{%supplier_schet_header}}, {{%supplier_schet_content}})")
                   ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%supplier_schet_header}}.refOrg")
                   ->where("{{%supplier_schet_content}}.schetRef = {{%supplier_schet_header}}.id")
                  ->distinct
                  ;      

     

    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);                      

        $query->andFilterWhere(['=', '{{%supplier_schet_header}}.schetNum', $this->schetNum]);
        $countquery->andFilterWhere(['=', '{{%supplier_schet_header}}.schetNum', $this->schetNum]);                      
        
        
        if (!empty($this->schetDate))
        {
            $countquery->andFilterWhere(['=', '({{%supplier_schet_header}}.schetDate)', date('Y-m-d', strtotime($this->schetDate)) ]);
            $query->andFilterWhere(['=','({{%supplier_schet_header}}.schetDate)', date('Y-m-d', strtotime($this->schetDate)) ]);
        }
        
     }
     
     if (!empty($this->refSuppSchet))     
     {
     $query->andFilterWhere(['=','{{%supplier_schet_header}}.id', $this->refSuppSchet ]);
     $countquery->andFilterWhere(['=','{{%supplier_schet_header}}.id', $this->refSuppSchet ]);    
         
     }
     else {   
     $query->andFilterWhere(['<=','{{%supplier_schet_header}}.schetDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','{{%supplier_schet_header}}.schetDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','{{%supplier_schet_header}}.schetDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','{{%supplier_schet_header}}.schetDate',  $this->y_from."-".$this->m_from."-"."01"]);    
     }
      
/*     $countquery->andFilterWhere(['<=', 'YEAR({{%supplier_schet_header}}.schetDate)', $this->y_to]);
     $query->andFilterWhere(['<=','YEAR({{%supplier_schet_header}}.schetDate)', $this->y_to]);

     $countquery->andFilterWhere(['>=', 'YEAR({{%supplier_schet_header}}.schetDate)', $this->y_from]);
     $query->andFilterWhere(['>=','YEAR({{%supplier_schet_header}}.schetDate)', $this->y_from]);

     
     $countquery->andFilterWhere(['<=', 'MONTH({{%supplier_schet_header}}.schetDate)', $this->m_to]);
     $query->andFilterWhere(['<=','MONTH({{%supplier_schet_header}}.schetDate)', $this->m_to]);
     
     $countquery->andFilterWhere(['>=', 'MONTH({{%supplier_schet_header}}.schetDate)', $this->m_from]);
     $query->andFilterWhere(['>=','MONTH({{%supplier_schet_header}}.schetDate)', $this->m_from]);*/
      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
    
    public function getSupplierSchetSrcData($params)
    {        
        $this->prepareSupplierSchetSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/SupplierSchetSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата счета"),
        iconv("UTF-8", "Windows-1251","Номер счета"),
        
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","К-во"),
        iconv("UTF-8", "Windows-1251","Ед. изм"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Поставщик"),        
        iconv("UTF-8", "Windows-1251","ИНН"), 
        iconv("UTF-8", "Windows-1251","КПП"), 
        
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        

        
        );
        fputcsv($fp, $col_title, ";");     
                
        for ($i=0; $i< count($dataList); $i++)
        {        

        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetNum']),                 
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodTitle']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodCount']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodEd']),                 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['goodSumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),

            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    

/*

                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")

                  'contactPhone','contactEmail','contactFIO'
                  
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        


            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
             


*/    
 
    
public function getSupplierSchetSrcProvider($params)
   {
        $this->prepareSupplierSchetSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',                      
            'schetDate', 
            'schetNum',  
            'goodTitle', 
            'goodCount', 
            'goodEd', 
            'goodSumm', 
            'supplierRef1C',
            'orgTitle',  
            'orgINN', 
            'orgKPP', 
            'refOrg'            
            ],
            'defaultOrder' => ['schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   


/***************************************************/
/*Реестр оплат */
public function prepareOplateReestr($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%reestr_oplat}}.id)")
                   ->from("{{%reestr_oplat}}")
                   ->leftJoin("{{%supplier_schet_header}}", "{{%supplier_schet_header}}.id = {{%reestr_oplat}}.refSchet")
                   ->leftJoin("{{%supplier_oplata}}", "{{%supplier_oplata}}.id = {{%reestr_oplat}}.refOplate")
                   ->leftJoin("{{%reestr_norma}}", "{{%reestr_norma}}.id = {{%reestr_oplat}}.oplateType")
                 ;
                  
     $query->select([
     '{{%reestr_oplat}}.id',
     '{{%reestr_oplat}}.formDate',
     '{{%reestr_oplat}}.oplateDate',
     '{{%reestr_oplat}}.orgTitle',
     'oplateType',
     '{{%reestr_oplat}}.refOrg',
     '{{%reestr_oplat}}.summRequest',
     '{{%reestr_oplat}}.summOplate',
     '{{%reestr_oplat}}.note',
     '{{%supplier_schet_header}}.schetNum as schNum',
     '{{%supplier_schet_header}}.schetDate as schDate',
     '{{%supplier_oplata}}.oplateSumm as plSumm',
     '{{%supplier_oplata}}.oplateDate as plDate',
     '{{%reestr_norma}}.normTitle',
     '{{%reestr_oplat}}.isActive'
        
     ])
                   ->from("{{%reestr_oplat}}")
                   ->leftJoin("{{%supplier_schet_header}}", "{{%supplier_schet_header}}.id = {{%reestr_oplat}}.refSchet")
                   ->leftJoin("{{%supplier_oplata}}", "{{%supplier_oplata}}.id = {{%reestr_oplat}}.refOplate")
                   ->leftJoin("{{%reestr_norma}}", "{{%reestr_norma}}.id = {{%reestr_oplat}}.oplateType")
                  ->distinct
                  ;      

    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);                      

        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.schetNum', $this->schetNum]);
        $countquery->andFilterWhere(['like', '{{%supplier_schet_header}}.schetNum', $this->schetNum]);                      

        $query->andFilterWhere(['=', '{{%reestr_oplat}}.oplateType', $this->oplateType]);
        $countquery->andFilterWhere(['=', '{{%reestr_oplat}}.oplateType', $this->oplateType]);                      

        if (!empty($this->schetDate))
        {
        $query->andFilterWhere(['=', '{{%supplier_schet_header}}.schetDate', date('Y-m-d',strtotime($this->schetDate)) ]);
        $countquery->andFilterWhere(['=', '{{%supplier_schet_header}}.schetDate', date('Y-m-d',strtotime($this->schetDate)) ]);
        }
        
     }
     if (empty($this->isActive)) $this->isActive = 2;
     if($this->isActive == 2)
     {
        $query->andFilterWhere(['=', '{{%reestr_oplat}}.isActive', 1]);
        $countquery->andFilterWhere(['=', '{{%reestr_oplat}}.isActive', 1]);                      
     }
     if($this->isActive == 3)
     {
        $query->andFilterWhere(['=', '{{%reestr_oplat}}.isActive', 0]);
        $countquery->andFilterWhere(['=', '{{%reestr_oplat}}.isActive', 0]);                      
     }
     
     
     $this->query =$query;  
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
public function getOplateReestrPrint($params)
{
        $this->prepareOplateReestr($params);                    
        $dataList = $this->query->orderBy(['{{%reestr_oplat}}.id' => SORT_DESC])->createCommand()->queryAll();  

return $dataList;
}

public function getOplateReestrCSV($params)
{

    $this->prepareOplateReestr($params);
    $dataList = $this->command->queryAll();
   
    $fname = "uploads/SupplierSchetSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата счета"),
        iconv("UTF-8", "Windows-1251","Номер счета"),
        
        iconv("UTF-8", "Windows-1251","Дата платежа"),
        iconv("UTF-8", "Windows-1251","Компания"),
        iconv("UTF-8", "Windows-1251","Сумма счета"),
        
        iconv("UTF-8", "Windows-1251","Сверка/Оплаты"),
        
        iconv("UTF-8", "Windows-1251","Оплачено"),        
        iconv("UTF-8", "Windows-1251","Остаток"), 
        iconv("UTF-8", "Windows-1251","Оплатить"), 
        
        iconv("UTF-8", "Windows-1251","Статья"),        
        iconv("UTF-8", "Windows-1251","Комментарий"),        
        iconv("UTF-8", "Windows-1251","Завершен"),        

        
        );
        fputcsv($fp, $col_title, ";");     
                
        for ($i=0; $i< count($dataList); $i++)
        {        
        if (empty($dataList[$i]['schNum'])) 
        {
            $dateVal = ' ';
            $numVal  = ' ';                                      
        }
        else 
        {
            $dateVal = date("d.m.Y", strtotime($dataList[$i]['schDate']));                    
            $numVal = $model['schNum'];  
        }

        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dateVal), 
            iconv("UTF-8", "Windows-1251",$numVal),                 
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodTitle']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodCount']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['goodEd']),                 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['goodSumm'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),

            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        

}

   
public function getOplateReestrProvider($params)
   {
        $this->prepareOplateReestr($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [    
            'id',                      
            'formDate',
            'oplateDate',
            'orgTitle',
            'refOrg',
            'summRequest',
            'summOplate',
            'oplateType',
            'schNum',
            'schDate',
            'plSumm',
            'plDate'     
            ],
            'defaultOrder' => ['id' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
/**************/
/****************************/
public function prepareSchetListReestr($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%supplier_schet_header}}.id',
            '{{%supplier_schet_header}}.schetNum',
            '{{%supplier_schet_header}}.schetDate',            
            '{{%supplier_schet_header}}.orgTitle',
            '{{%supplier_schet_header}}.refOrg',
            ])
            ->from("{{%supplier_schet_header}}")                       
            ->leftJoin("{{%reestr_oplat}}", "{{%supplier_schet_header}}.id = {{%reestr_oplat}}.refSchet")
            ->where("{{%reestr_oplat}}.id is null and schetDate > '".date("Y-m-d", time() - 60*60*24*90)."'")
            ->distinct();
    
     //$query->andWhere("refOrg = ".$this->supplierRef);
    
     if (($this->load($params) && $this->validate())) {   
          
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.orgTitle', $this->orgTitle]);
        $query->andFilterWhere(['like', '{{%supplier_schet_header}}.schetNum', $this->schetNum]);
        if(!empty($this->schetDate) )
        $query->andFilterWhere(['=', '{{%supplier_schet_header}}.schetDate', date('Y-m-d', strtotime($this->schetDate)) ]);
        
     }
   
  // echo $query->createCommand()->getRawSql();
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

  
 public function getSchetListReestr($params)
   {
    
    $this-> prepareSchetListReestr($params);    
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
public function prepareOplateListReestr($params)
   {
    
    
    
    $query  = new Query();
    $query->select ([
            'id',
            'sdelkaNum',
            'sdelkaDate',            
            'orgTitle',
            'refOrg',
            'oplateDate',
            'oplateSumm',
            'ifnull(inReestr,0) as inReestr'
            ])
            ->from( "{{%supplier_oplata}}" )
            ->leftJoin(" (SELECT COUNT({{%reestr_lnk}}.id) as inReestr, oplataId from {{%reestr_lnk}} GROUP BY oplataId) as a", "a.oplataId = {{%supplier_oplata}}.id")
            ;
    
     if (($this->load($params) && $this->validate())) {   
     
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle ]);
        $query->andFilterWhere(['like', 'sdelkaNum', $this->sdelkaNum ]);
     }

        if (empty($this->inReestr) ) $this->inReestr = 2;
        if ($this->inReestr == 2){
            $query->andFilterWhere(['=', 'ifnull(inReestr,0)', 0 ]);
        }
                if ($this->inReestr == 3){
            $query->andFilterWhere(['>', 'ifnull(inReestr,0)', 0 ]);
        }

     
     if ($this->reestrId > 0 && empty($this->orgTitle) )
    {
       $this->orgTitle = Yii::$app->db->createCommand("Select orgTitle
                 from {{%reestr_oplat}} where  {{%reestr_oplat}}.id =:reestrId",                  
                 [':reestrId' => $this->reestrId,])->queryScalar();      
                 
       $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle ]);         
                 
    }
    
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

  
 public function getOplateListReestr($params)
   {
    
    $this-> prepareOplateListReestr($params);    
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
            'sdelkaNum',
            'sdelkaDate',            
            'orgTitle',
            'refOrg',
            'oplateDate',
            'oplateSumm',
            'inReestr'
              ],
            'defaultOrder' => [	'oplateDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/****************************/
public function prepareAttachedOplateListReestr($params)
   {
    
    
    
    $query  = new Query();
    $query->select ([
            '{{%reestr_lnk}}.id',
            'lnkOplate',
            'sdelkaNum',
            'sdelkaDate',            
            'orgTitle',
            'refOrg',
            'oplateDate',
            'oplateSumm',
            ])
            ->from( "{{%supplier_oplata}}, {{%reestr_lnk}}" )
            ->where ("{{%supplier_oplata}}.id ={{%reestr_lnk}}.oplataId and {{%reestr_lnk}}.reestrId = ".$this->reestrId )             
            ;

            
   
    $this->command = $query->createCommand(); 
    $list = $query->createCommand()->queryAll();
    $this->count = count($list);    
   } 

  
 public function getAttachedOplateListReestr($params)
   {
    
    $this-> prepareAttachedOplateListReestr($params);    
    $pageSize = 3;    
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
            'lnkOplate',
            'sdelkaNum',
            'sdelkaDate',            
            'orgTitle',
            'refOrg',
            'oplateDate',
            'oplateSumm',
              ],
            'defaultOrder' => [	'oplateDate' => SORT_DESC ],
            ],            
        ]);
                
    return  $dataProvider;   
   }   
   
/***********************************/
   public function getNormProvider()
   {
   
     $query  = new Query();
	 $countquery  = new Query();	 

	 
	 $countquery->select("count({{%reestr_norma}}.id)")
			->from("{{%reestr_norma}}");	

	
	 $query->select("id,normTitle, plan")->from("{{%reestr_norma}}");		

	
   	
	    $command = $query->createCommand();	
	    $count = $countquery->createCommand()->queryScalar();
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'id',
            'normTitle', 
            'plan'		
			],
			'defaultOrder' => [	'id' => SORT_ASC ],
			],
		]);
	return $provider;
   }   
/**********************/


public function getReestrData()
{
  if (empty($this->id) ) return false;
  $reestrRecord = ReestrOplat::FindOne($this->id);
  if (empty($reestrRecord)) return false;
  if (!empty($reestrRecord->refManager))
  {
    $userRecord = UserList::FindOne($reestrRecord->refManager);
    $this->reestExtData['manager'] = $userRecord->userFIO;
  }
  return $reestrRecord;
}

public function getReestrSchetData($id)
{
  if (empty($id) ) return false;
  $schetRecord = SupplierSchetHeaderList::FindOne($id);
  if (empty($schetRecord)) return false;
  return $schetRecord;
}

public function getReestrSchetProvider($id)
{
     $query  = new Query();
	 $countquery  = new Query();	 

	 
	 $countquery->select("count(id)")
			->from("{{%supplier_schet_content}}")
            ->where("schetRef = ".$id)
          ;		

	
	 $query->select("goodTitle ,goodCount, goodSumm, goodEd")
          ->from("{{%supplier_schet_content}}")
          ->where("schetRef = ".$id)
          ;		

	
	    $command = $query->createCommand();	
	    $count = $countquery->createCommand()->queryScalar();
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'goodTitle' ,
            'goodCount', 
            'goodSumm', 
            'goodEd'
			],
			'defaultOrder' => [	'goodTitle' => SORT_ASC ],
			],
		]);
	return $provider;
}
 

public function getReestrPurchaseData($id)
{
  if (empty($id) ) return false;
  $purchaseRecord = Purchase::FindOne($id);
  if (empty($purchaseRecord)) return false;
  return $purchaseRecord;
}


public function getReestrPurchaseProvider($id)
{
     $query  = new Query();
	 $countquery  = new Query();	 

	 
	 $countquery->select("count(DISTINCT({{%purchase_zakaz}}.id))")
          ->from("{{%purchase_zakaz}}")
          ->leftJoin("{{%zakaz}}","{{%zakaz}}.id = {{%purchase_zakaz}}.refZakaz")
          ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%zakaz}}.refOrg")
          ->leftJoin("{{%user}}","{{%user}}.id = {{%zakaz}}.ref_user")
          ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%purchase_zakaz}}.refZakaz")
          ->where("purchaseRef = ".$id)
          ;		

	
	 $query->select([
     '{{%purchase_zakaz}}.id as id',
     'zakazDate',
     'zakazNote',
     'currentValue',
     'wareTitle',
     'wareCount',
     '{{%purchase_zakaz}}.refZakaz',
     '{{%zakaz}}.formDate',
     '{{%zakaz}}.refOrg',
     '{{%orglist}}.title',
     '{{%user}}.userFIO',
     '{{%schet}}.id as refSchet',
     '{{%schet}}.schetNum',
     '{{%schet}}.schetDate',     
     ])
          ->from("{{%purchase_zakaz}}")
          ->leftJoin("{{%zakaz}}","{{%zakaz}}.id = {{%purchase_zakaz}}.refZakaz")
          ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%zakaz}}.refOrg")
          ->leftJoin("{{%user}}","{{%user}}.id = {{%zakaz}}.ref_user")
          ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%purchase_zakaz}}.refZakaz")
          ->where("purchaseRef = ".$id)
          ->distinct()
          ;		

	
	    $command = $query->createCommand();	
	    $count = $countquery->createCommand()->queryScalar();
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
            'id',
            'zakazDate',
            'currentValue',
            'wareTitle',
            'wareCount',
            'refZakaz',
            'refOrg',
            'title',
            'userFIO',
            'refSchet',
            'schetNum',
            'schetDate',     
			],
			'defaultOrder' => [	'id' => SORT_ASC ],
			],
		]);
	return $provider;
}

/******************/

/***************************************************/
/*Счета клиентам */
public function prepareClientSchetSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%client_schet_content}}.id)")
                   ->from("{{%client_schet_content}}, {{%client_schet_header}}")
                  ->where("{{%client_schet_content}}.refHeader = {{%client_schet_header}}.id")
                  ->distinct()
                 ;
                  
     $query->select([ 
                    '{{%client_schet_header}}.schetDate',
                    '{{%client_schet_header}}.schetRef1C as schetNum',
                    'wareTitle',
                    'wareCount',
                    'wareEd',
                    'wareSum',
                    '{{%client_schet_header}}.orgTitle',
                    '{{%client_schet_header}}.id as refSchet',
                    '{{%client_schet_header}}.orgKPP',
                    '{{%client_schet_header}}.orgINN',
                    'contactPhone',
                    'contactFIO',
                    'contactEmail',
                    ])
                   ->from("{{%client_schet_content}}, {{%client_schet_header}}")
                   ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%client_schet_header}}.refOrg")
                  ->where("{{%client_schet_content}}.refHeader = {{%client_schet_header}}.id")
                  ->distinct()
                  ;      
    if (($this->load($params) && $this->validate())) 
     {

        $query->andFilterWhere(['like', '{{%client_schet_header}}.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%client_schet_header}}.orgTitle', $this->orgTitle]);                              
        
        $query->andFilterWhere(['=', '{{%client_schet_header}}.schetRef1C', $this->schetNum]);
        $countquery->andFilterWhere(['=', '{{%client_schet_header}}.schetRef1C', $this->schetNum]);                      
                            
     }
     
     
     if (!empty($this->refSchet))
        {
          $query->andWhere(['=', '{{%client_schet_header}}.id', $this->refSchet]);
          $countquery->andWhere(['=', '{{%client_schet_header}}.id', $this->refSchet]);                      
        }
     else 
     {
     if (!empty($this->ref1C))
        {
          $query->andWhere(['=', 'schetRef1C', $this->ref1C]);
          $countquery->andWhere(['=', 'schetRef1C', $this->ref1C]);                      
        }
        
        
     if (!empty($this->refSchet))
        {
          $query->andWhere(['=', '{{%client_schet_header}}.id', $this->refSchet]);
          $countquery->andWhere(['=', '{{%client_schet_header}}.id', $this->refSchet]);                      
        }
        
     
     if (!empty($this->schetDate))
        {
            $countquery->andFilterWhere(['=', '{{%client_schet_header}}.schetDate', date('Y-m-d', strtotime($this->schetDate)) ]);
            $query->andFilterWhere(['=','{{%client_schet_header}}.schetDate', date('Y-m-d', strtotime($this->schetDate)) ]);
            
            $this->m_to = date('m', strtotime($this->schetDate));
            $this->m_from = $this->m_to;
            
            $this->y_to = date('Y', strtotime($this->schetDate));
            $this->y_from = $this->y_to;
            
        }
        else {      
            $query->andFilterWhere(['<=','{{%client_schet_header}}.schetDate', $this->y_to."-".$this->m_to."-"."31"]);
            $query->andFilterWhere(['>=','{{%client_schet_header}}.schetDate',  $this->y_from."-".$this->m_from."-"."01"]);     
            $countquery->andFilterWhere(['<=','{{%client_schet_header}}.schetDate', $this->y_to."-".$this->m_to."-"."31"]);
            $countquery->andFilterWhere(['>=','{{%client_schet_header}}.schetDate',  $this->y_from."-".$this->m_from."-"."01"]);    
            }      
     }
     
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    
    
    public function getClientSchetSrcData($params)
    {        
        $this->prepareClientSchetSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/ClientSchetSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата счета"),
        iconv("UTF-8", "Windows-1251","Номер счета"),
        
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","К-во"),
        iconv("UTF-8", "Windows-1251","Ед. изм"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Клиент"),        
        iconv("UTF-8", "Windows-1251","ИНН"), 
        iconv("UTF-8", "Windows-1251","КПП"), 
        
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        

        
        );
        fputcsv($fp, $col_title, ";");     

        for ($i=0; $i< count($dataList); $i++)
        {            
            $good =$dataList[$i]['wareTitle'];                 
                

    
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetNum']),                 
            
            iconv("UTF-8", "Windows-1251",$good),                 
             
            iconv("UTF-8", "Windows-1251",$dataList[$i]['wareCount']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['wareEd']),                 
            
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['wareSum'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
   
    
public function getClientSchetSrcProvider($params)
   {
        $this->prepareClientSchetSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [           
             'schetDate',
             'schetNum',
             'good',
             'initialZakaz',
             'count',
             'ed',
             'value',
             'orgTitle',
             'userFIO',
             'ref1C',
            ],
            'defaultOrder' => ['schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

/***************************************************/
/*Заявки клиентов */
public function prepareZakazSrcData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%zakazContent}}.id)")
                   ->from("{{%zakazContent}}, {{%zakaz}}")
                   ->leftJoin("{{%schet}}", "{{%zakaz}}.id = {{%schet}}.refZakaz")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%zakaz}}.refOrg")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%zakaz}}.ref_user")
                  ->where("{{%zakaz}}.id = {{%zakazContent}}.refZakaz")
                  ->distinct
                 ;
                  
                  
     $query->select([ 
                    '{{%zakaz}}.formDate',
                    '{{%zakaz}}.id',
                    'good',
                    'initialZakaz',
                    'count',
                    'ed',
                    'value',
                    '{{%orglist}}.title as orgTitle',
                    'userFIO',
                    'orgKPP',
                    'contactFIO',
                    'contactPhone',
                    'contactEmail',
                    '{{%zakazContent}}.isActive as contentActive',
                    '{{%zakaz}}.isActive as zakazActive',                                        
                    '{{%schet}}.schetNum'
                    ])
                   ->from("{{%zakazContent}}, {{%zakaz}}")
                   ->leftJoin("{{%schet}}", "{{%zakaz}}.id = {{%schet}}.refZakaz")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%zakaz}}.refOrg")
                  ->leftJoin("{{%user}}", "{{%user}}.id = {{%zakaz}}.ref_user")
                  ->where("{{%zakaz}}.id = {{%zakazContent}}.refZakaz")
                  ->distinct
                  ;      
                  

    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);                      

        $query->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', '{{%orglist}}.title', $this->orgTitle]);                              
                
        $query->andFilterWhere(['like', 'schetNum', $this->schetNum]);
        $countquery->andFilterWhere(['like', 'schetNum', $this->schetNum]);                              
                
/*        if (!empty($this->formDate))
        {
            $countquery->andFilterWhere(['=', 'DATE(formDate)', date('Y-m-d', strtotime($this->formDate)) ]);
            $query->andFilterWhere(['=','DATE(formDate)', date('Y-m-d', strtotime($this->formDate)) ]);
        }*/
        
     }
      
     $query->andFilterWhere(['<=','formDate', $this->y_to."-".$this->m_to."-"."31"]);
     $query->andFilterWhere(['>=','formDate',  $this->y_from."-".$this->m_from."-"."01"]);    
 
     $countquery->andFilterWhere(['<=','formDate', $this->y_to."-".$this->m_to."-"."31"]);
     $countquery->andFilterWhere(['>=','formDate',  $this->y_from."-".$this->m_from."-"."01"]);    
      
      
     $this->command = $query->createCommand();    
     $this->count = $countquery->createCommand()->queryScalar();

    }
    

public function getZakazSrcData($params)
    {        
        $this->prepareZakazSrcData($params);    
        $dataList=$this->command->queryAll();
   
    $fname = "uploads/ClientSchetSrcData.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    $col_title = array (
        iconv("UTF-8", "Windows-1251","Дата "),
        iconv("UTF-8", "Windows-1251","Номер "),
        
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","К-во"),
        iconv("UTF-8", "Windows-1251","Ед. изм"),
        
        iconv("UTF-8", "Windows-1251","Сумма"),
        
        iconv("UTF-8", "Windows-1251","Клиент"),        
        iconv("UTF-8", "Windows-1251","ИНН"), 
        iconv("UTF-8", "Windows-1251","КПП"), 

        iconv("UTF-8", "Windows-1251","Товар активен"), 
        iconv("UTF-8", "Windows-1251","Заказ активен"), 
        iconv("UTF-8", "Windows-1251","Счет"), 
                
        iconv("UTF-8", "Windows-1251","Контактный телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        iconv("UTF-8", "Windows-1251","Контактное лицо"),        

        
        );
        fputcsv($fp, $col_title, ";");     


        
        for ($i=0; $i< count($dataList); $i++)
        {        
    
               if (!empty($dataList[$i]['good']))
                $good =$dataList[$i]['good'];                 
            else
                $good =$dataList[$i]['initialZakaz'];                 
                

    
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['formDate']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['id']),                 
            
            iconv("UTF-8", "Windows-1251",$good),                 
             
            iconv("UTF-8", "Windows-1251",$dataList[$i]['count']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ed']),          
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['count']*$dataList[$i]['value'],2,'.','')), 
                        
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetINN']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['orgKPP']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contentActive']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['zakazActive']), 
            iconv("UTF-8", "Windows-1251", $dataList[$i]['schetNum']), 

            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactPhone']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactEmail']),
            iconv("UTF-8", "Windows-1251", $dataList[$i]['contactFIO']),
            
           );
           
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
   
    
public function getZakazSrcProvider($params)
   {
        $this->prepareZakazSrcData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [           
                    'formDate',
                    'id',
                    'good',
                    'initialZakaz',
                    'count',
                    'ed',
                    'value',
                    'orgTitle',
                    'userFIO',
                    'zakazActive',
                    'contentActive',
                    'schetNum'
            ],
            'defaultOrder' => ['formDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   



 /** end of object **/	
 }

 
 
 
 
