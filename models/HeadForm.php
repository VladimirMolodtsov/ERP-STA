<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use yii\helpers\Html;


use app\models\FltList;
use app\models\OrgList;
use app\models\UserList;
use app\models\MarketSchetForm;
use app\models\WarehouseForm;
use app\models\TmpOrgReestr;
use app\models\TblOrgCategory;
/**
 * HeadForm  - модель стартовой формы управления
 */
class HeadForm extends Model
{
    

    public $title ="";
    public $userFIO="";
    public $managerFIO="";
    public $operator="";
    public $isNeedFinished=""; 
    public $isPreparedForSchet=""; 
    public $isAvailableForHelper="";
    public $all_contacts="";             
    public $prev_month=""; 
    public $cur_month="";
    public $zakaz="";
    public $schet="";        
    public $fltGood=""; 
    public $supplyGood="";     
    public $balance = 0;
    public $execution =0;
    public $regular = 0;
    public $active = 0;
    public $mode = 2;
    public $detail=0;
    public $catTitle="";
    public $lastContact=0;
    public $lastSupply=0;
  
   public $dataArray=array();  
    
   public $dealStatus="";
   public $orgTitle="";
   public $schetNumber="";
   public $schetStatus="";
   public $nextContactDate="";
   
 
    public $monthShift=0;
   
   public $format='html'; 
  
   public $command;    
   public $count=0;
   public $razdel="";

   public $debug=array();
    
   public $period=60; 
   public $userId=60;
   
   public $isAccepted = 0;
   public $fltCategory=0;
    
        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    public $dataId;
    
    
   public function rules()
   {
        return [
            [['period',
            'recordId','dataType','dataVal','dataId',
            ], 'default'],
            [['dealStatus','title','userFIO', 'isAvailableForHelper', 'all_contacts', 'prev_month', 'cur_month',
            'schetStatus','userFIO','schetNumber','orgTitle', 'zakaz','nextContactDate' ,'operator', 'razdel', 
            'schet', 'fltGood', 'balance', 'active', 'supplyGood', 'catTitle','managerFIO','regular', 'execution',
            'lastContact', 'lastSupply', 'isAccepted' ], 'safe'],

        ];
    }
    
    
   public function saveCfgData ()
   {
    
       $this->recordId = intval($this->recordId);
       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
             'isReload' => false
           ];   
           
    switch ($this->dataType)
    {
        case 'catTitle':
         $record=  TblOrgCategory::findOne($this->recordId);     
         if (empty($record)) return $res;
           $record->catTitle = $this->dataVal;          
           $record->save(); 
           $res['val'] =  $record->catTitle ;
           break;
           
     }      
     
    $res['res'] = true;    
    return $res;
   }      
   /*****************************/
    public function getClientContactActivityProvider()
   {
   
   
   $year = date("Y",time());
   $prv_year=$year;
   $month = date("m",time());
   $prv_month = $month -1;
   if ($prv_month == 0){$prv_month = 12;$prv_year--;}
   
   
   
    $strSql = "select {{%user}}.id, userFIO, all_contacts, a.ref_user, b.prev_month, c.cur_month from {{%user}}
    left join (SELECT COUNT(id) as all_contacts, ref_user from {{%contact}} group by ref_user) as a
    on a.ref_user = {{%user}}.id left join 
    (SELECT COUNT(id) as prev_month, ref_user from {{%contact}} where (month(contactDate) = ".$prv_month." and year(contactDate) = ".$prv_year.")
    group by ref_user) as b   on b.ref_user = a.ref_user  left join 
    (SELECT COUNT(id) as cur_month, ref_user from {{%contact}} where (month(contactDate) = ".$month." and year(contactDate) = ".$year.")
    group by ref_user) as c    on c.ref_user = a.ref_user 
    where ({{%user}}.roleFlg & (0x0002|0x0004|0x0080))";
 
   
   
        $count = Yii::$app->db->createCommand(
            'SELECT count({{%user}}.id) FROM {{%user}} where ({{%user}}.roleFlg & (0x0002|0x0004|0x0080))')->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => $strSql,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',            
            'userFIO', 
            'all_contacts', 
            'ref_user', 
            'prev_month', 
            'cur_month'    
            ],
            ],
        ]);
    return $provider;
   }   

public function getOrgContactActivityProvider($params)
   {
   
   
   $year = date("Y",time());
   $prv_year=$year;
   $month = date("m",time());
   $prv_month = $month -1;
   if ($prv_month == 0){$prv_month = 12;$prv_year--;}

  
   $rangeTime = time() - 60*60*24*$this->period;
   $rangeDate = date ('Y-m-d', $rangeTime);

//$this->debug[]=$rangeDate;
    
    $countquery  = new Query();
    $query       = new Query();
  
   
      $query->select ("a.id, title, userFIO, isNeedFinished, isPreparedForSchet, isAvailableForHelper, all_contacts, cur_month, zakaz, 
      schet ")
            ->from("{{%orglist}} as a")
            ->leftJoin('{{%user}}','{{%user}}.id = refManager')
            ->leftJoin('(SELECT COUNT(id) as all_contacts, ref_org from {{%contact}} group by ref_org) as b','a.id = b.ref_org')
            ->leftJoin("(SELECT COUNT(id) as cur_month, ref_org from {{%contact}} where (contactDate > '".$rangeDate."') group by ref_org) as c",'a.id = c.ref_org')
            ->leftJoin("(SELECT COUNT(id) as zakaz, refOrg from {{%zakaz}} where (formDate > '".$rangeDate."') group by refOrg) as d",'a.id = d.refOrg')
            ->leftJoin("(SELECT COUNT(id) as schet, refOrg from {{%schet}} where (schetDate > '".$rangeDate."') group by refOrg) as e",'a.id = e.refOrg')
            ; 

//$this->debug[]=$query;
            
            
   $countquery->select ("count(a.id)")
            ->from("{{%orglist}} as a")
            ->leftJoin('{{%user}}','{{%user}}.id = refManager')
            ->leftJoin('(SELECT COUNT(id) as all_contacts, ref_org from {{%contact}} group by ref_org) as b','a.id = b.ref_org')
            ->leftJoin("(SELECT COUNT(id) as cur_month, ref_org from {{%contact}} where (contactDate > '".$rangeDate."') group by ref_org) as c",'a.id = c.ref_org')
            ->leftJoin("(SELECT COUNT(id) as zakaz, refOrg from {{%zakaz}} where (formDate > '".$rangeDate."') group by refOrg) as d",'a.id = d.refOrg')
            ->leftJoin("(SELECT COUNT(id) as schet, refOrg from {{%schet}} where (schetDate > '".$rangeDate."') group by refOrg) as e",'a.id = e.refOrg')
            ;

            
        $query->andWhere(['=', 'isPreparedForSchet', 1]); 
        $countquery->andWhere(['=', 'isPreparedForSchet', 1]);
        $query->andWhere(['>', 'refManager', 0]);             
        $countquery->andWhere(['>', 'refManager', 0]);             
        
            
            
    if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);
        
        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        

        $query->andFilterWhere(['=', 'isPreparedForSchet', $this->isPreparedForSchet]);
        $countquery->andFilterWhere(['=', 'isPreparedForSchet', $this->isPreparedForSchet]);
        
        $query->andFilterWhere(['=', 'isAvailableForHelper', $this->isAvailableForHelper]);
        $countquery->andFilterWhere(['=', 'isAvailableForHelper', $this->isAvailableForHelper]);
        
        
        $cond="=";
        $flt=$this->all_contacts;
        if (preg_match("/[\>\<\=]/iu",$this->all_contacts))
//        $this->debug[]=$matches;    
        {
            $cond = substr($this->all_contacts,0,1); 
            $flt=substr($this->all_contacts,1);
        }        
        $query->andFilterWhere([$cond, 'all_contacts', $flt]);
        $countquery->andFilterWhere([$cond, 'all_contacts', $flt]);
        


        $cond="=";
        $flt=$this->cur_month;
        if (preg_match("/[\>\<\=]/iu",$this->cur_month))
        {
            $cond = substr($this->cur_month,0,1); 
            $flt=substr($this->cur_month,1);
        }        
        $query->andFilterWhere([$cond, 'cur_month', $flt]);
        $countquery->andFilterWhere([$cond, 'cur_month', $flt]);
    
        $cond="=";
        $flt=$this->zakaz;
        if (preg_match("/[\>\<\=]/iu",$this->zakaz))
        {
            $cond = substr($this->zakaz,0,1); 
            $flt=substr($this->zakaz,1);
        }        
        $query->andFilterWhere([$cond, 'zakaz', $flt]);
        $countquery->andFilterWhere([$cond, 'zakaz', $flt]);
    
        $cond="=";
        $flt=$this->schet;
        if (preg_match("/[\>\<\=]/iu",$this->schet))
        {
            $cond = substr($this->schet,0,1); 
            $flt=substr($this->schet,1);
        }        
        $query->andFilterWhere([$cond, 'schet', $flt]);
        $countquery->andFilterWhere([$cond, 'schet', $flt]);
        
        
     }
            
    //$query    
            
    $count = $countquery->createCommand()->queryScalar();
    $command = $query->createCommand();    
//      $command    ->bindValue(':period', $this->period); 
      
        $provider = new SqlDataProvider(
        [   'sql' => $command ->sql, 
            'params' => $command->params,    
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',    
            'title',
            'userFIO', 
            'isNeedFinished', 
            'isPreparedForSchet', 
            'isAvailableForHelper',
            'all_contacts',             
            'prev_month', 
            'cur_month',
            'zakaz', 
            'schet'            
            ],
            ],
        ]);
    return $provider;
   }   

public function prepareDownloadStatOrgs($params)   
{
  
   $rangeTime = time() - 60*60*24*$this->period;
   $rangeDate = date ('Y-m-d', $rangeTime);

//$this->debug[]=$rangeDate;
    
    $query       = new Query();
   
      $query->select ("a.id, title, userFIO, a.contactPhone, a.contactEmail, a.contactFIO, isNeedFinished, isPreparedForSchet, isAvailableForHelper, all_contacts, cur_month, zakaz, schet, phones, emails ")
            ->from("{{%orglist}} as a")
            ->leftJoin('{{%user}}','{{%user}}.id = refManager')
            ->leftJoin('(SELECT COUNT(id) as all_contacts, ref_org from {{%contact}} group by ref_org) as b','a.id = b.ref_org')
            ->leftJoin("(SELECT COUNT(id) as cur_month, ref_org from {{%contact}} where (contactDate > '".$rangeDate."') group by ref_org) as c",'a.id = c.ref_org')
            ->leftJoin("(SELECT COUNT(id) as zakaz, refOrg from {{%zakaz}} where (formDate > '".$rangeDate."') group by refOrg) as d",'a.id = d.refOrg')
            ->leftJoin("(SELECT COUNT(id) as schet, refOrg from {{%schet}} where (schetDate > '".$rangeDate."') group by refOrg) as e",'a.id = e.refOrg')
            ->leftJoin("(SELECT GROUP_CONCAT(phone SEPARATOR ' | ') as phones, ref_org  from {{%phones}}  group by ref_org) as f ",'a.id = f.ref_org')            
            ->leftJoin("(SELECT GROUP_CONCAT(email SEPARATOR ' | ') as emails, ref_org  from {{%emaillist}}  group by ref_org) as g ",'a.id = g.ref_org')            
            ; 

//$this->debug[]=$query;

        $query->andWhere(['=', 'isPreparedForSchet', 1]); 
        $query->andWhere(['>', 'refManager', 0]);             
            
            
    if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]); 
    
        $query->andFilterWhere(['like', 'title', $this->title]);

        $query->andFilterWhere(['=', 'isPreparedForSchet', $this->isPreparedForSchet]);
        
        $query->andFilterWhere(['=', 'isAvailableForHelper', $this->isAvailableForHelper]);
        
        $cond="=";
        $flt=$this->all_contacts;
        if (preg_match("/[\>\<\=]/iu",$this->all_contacts))
        {
            $cond = substr($this->all_contacts,0,1); 
            $flt=substr($this->all_contacts,1);
        }        
        $query->andFilterWhere([$cond, 'all_contacts', $flt]);

        $cond="=";
        $flt=$this->cur_month;
        if (preg_match("/[\>\<\=]/iu",$this->cur_month))
        {
            $cond = substr($this->cur_month,0,1); 
            $flt=substr($this->cur_month,1);
        }        
        $query->andFilterWhere([$cond, 'cur_month', $flt]);
    
        $cond="=";
        $flt=$this->zakaz;
        if (preg_match("/[\>\<\=]/iu",$this->zakaz))
        {
            $cond = substr($this->zakaz,0,1); 
            $flt=substr($this->zakaz,1);
        }        
        $query->andFilterWhere([$cond, 'zakaz', $flt]);
    
        $cond="=";
        $flt=$this->schet;
        if (preg_match("/[\>\<\=]/iu",$this->schet))
        {
            $cond = substr($this->schet,0,1); 
            $flt=substr($this->schet,1);
        }        
        $query->andFilterWhere([$cond, 'schet', $flt]);
        
     }
            
    $dataList = $query->createCommand()->queryAll();    
    
    
    $mask = realpath(dirname(__FILE__))."/../uploads/statorgs*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/statorgs".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;

    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251", "Id"),
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Менеджер"),
        iconv("UTF-8", "Windows-1251","Назначен помошник"),
        iconv("UTF-8", "Windows-1251","Контактов всего"),
        iconv("UTF-8", "Windows-1251","Контактов за период"),
        iconv("UTF-8", "Windows-1251","Заказов"),
        iconv("UTF-8", "Windows-1251","Счетов"),
        iconv("UTF-8", "Windows-1251","Телефон"),
        iconv("UTF-8", "Windows-1251","E-mail"),
        iconv("UTF-8", "Windows-1251","ФИО"),
        );
        fputcsv($fp, $col_title, ";"); 
    
    for ($i=0; $i< count($dataList); $i++)
    {        
    
    
    $valCntMonth = "";
    if ($dataList[$i]['cur_month'] != 0)
    {
     $resList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND ({{%contact}}.contactDate > :rangeDate) AND  {{%contact}}.ref_org = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $dataList[$i]['id'],
				])->queryAll();
                for ($id=0; $id < count ($resList); $id++)
                {
                $valCntMonth .= $resList[$id]['userFIO'].": ". $resList[$id]['cur']." ";
                }
                
                $valCntMonth .= "Всего: ".$dataList[$i]['cur_month'];
      }
    
    $valCntZakaz = "";
    if ($dataList[$i]['zakaz'] != 0) 
    {
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%zakaz}}.id) as cur, userFIO from  {{%zakaz}},  {{%user}}  
                where    {{%user}}.id = {{%zakaz}}.ref_user AND ({{%zakaz}}.formDate  > :rangeDate) AND  {{%zakaz}}.refOrg = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $dataList[$i]['id'],
				])->queryAll();
                for ($i=0; $i < count ($resList); $i++)
                {
                $valCntZakaz .= "".$resList[$i]['userFIO'].": ". $resList[$i]['cur']." ";
                }
                $valCntZakaz .= "Всего: ".$dataList[$i]['zakaz'];
      }
    
    
    $valCntSchet = "";
    if ($dataList[$i]['schet'] != 0) {
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%schet}}.id) as cur, userFIO from  {{%schet}},  {{%user}}  
                where    {{%user}}.id = {{%schet}}.refManager  AND ({{%schet}}.schetDate   > :rangeDate) AND  {{%schet}}.refOrg = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $dataList[$i]['id'],
				])->queryAll();
                for ($i=0; $i < count ($resList); $i++)
                {
                $valCntSchet .= "".$resList[$i]['userFIO'].": ". $resList[$i]['cur']." ";
                }
                $valCntSchet .= "Всего: ".$dataList[$i]['schet'];
      }
    
    
    
    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['id']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['title']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['isAvailableForHelper']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['all_contacts']), 
            iconv("UTF-8", "Windows-1251",$valCntMonth ), 
            iconv("UTF-8", "Windows-1251",$valCntZakaz), 
            iconv("UTF-8", "Windows-1251",$valCntSchet), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contactFIO']), 
            );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
            
  return "../".$fname;
   
}  
public function addFltStatOrg ($filtName, $filtPar)
{
    $fltRecord = new FltList;    
    $fltRecord->fltType = 1;
    $fltRecord->fltContent = serialize ($filtPar);
    $fltRecord->fltName = $filtName;
    $fltRecord->save();    
}
   
public function loadFltStatOrg ($fltId)
{
     $filtPar = Yii::$app->db->createCommand(
            'SELECT fltContent FROM {{%fltList}} where id=:fltId')
            ->bindValue(':fltId', $fltId)                    
            ->queryScalar();
    return unserialize ($filtPar);
}


public function getListFltStatOrg ()
{
     $filtList = Yii::$app->db->createCommand(
            'SELECT id, fltName FROM {{%fltList}} where fltType=1')            
            ->queryAll();    
    return $filtList;
}

public function setOrgManager($orgId, $managerId)
{
    Yii::$app->db->createCommand(
            'UPDATE {{%orglist}} set refManager=:refManager where id=:orgId')            
            ->bindValue(':refManager', $managerId)                    
            ->bindValue(':orgId', $orgId)                    
            ->execute();    
    
    return true;    
}

public function schetRmRef($schetRef)
{
    Yii::$app->db->createCommand(
            'UPDATE {{%schet}} set ref1C=-1 where id=:schetRef')            
            ->bindValue(':schetRef', $schetRef)                                
            ->execute();    
    
    return true;    
}

public function getManagerList()
{

        $count = Yii::$app->db->createCommand(
            'SELECT count({{%user}}.id) FROM {{%user}} where ({{%user}}.roleFlg & (0x0002|0x0004|0x0080))')->queryScalar();

        $strSql = "select {{%user}}.id, userFIO FROM {{%user}} where ({{%user}}.roleFlg & (0x0002|0x0004|0x0080))";
            
        $provider = new SqlDataProvider(['sql' => $strSql,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',            
            'userFIO', 
            ],
            ],
        ]);
    return $provider;
    
    
}

/*******************************************************************************/
/***************************       EVENTS  CALC   ******************************/
/*******************************************************************************/
/*
ALTER TABLE `tbl_calendar` MODIFY COLUMN `event_date` DATETIME NOT NULL;
*/   
/*Получим  на сегодняшнюю дату*/
   public function getCurrentEvents($type)
   {            
   
    $cond = "";
   /*счета и заявки*/    
   if ($type == 1) { $cond = " AND (ref_event >2 AND ref_event <8)";}
   /*Произвольный контакт
       запланированы контакты, несвязанные со счетом/заявкой*/    
   if ($type == 2) { $cond = " AND (ref_event >7)";}
   /*Холодная база*/    
   if ($type == 3) { $cond = " AND (ref_event <3)";}    
   
        $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=1 ".$cond;    
          return  Yii::$app->db->createCommand($strCount)->queryScalar();                
   }   
/*************************/   
/*Получим  Дальнейшие*/
   public function getOtherEvents($type)
   {            

    $cond = "";

  /*счета и заявки*/    
   if ($type == 1) { $cond = " AND (ref_event >2 AND ref_event <8)";}
    
   /*Произвольный контакт*/       
   if ($type == 2) 
   { 
    /*все по которым нет дальнейшей активной работы

    */
     $countquery  = new Query();
     $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 AND event_date <= '".date("Y-m-d", time())."' group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")

                 ;
    $countquery->where(" ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");            
    return $countquery->createCommand()->queryScalar();
     }
  
   /*Холодная база*/    
   if ($type == 3) { $cond = " AND (ref_event <3)";}    

    $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=1 ".$cond."  and DATE(event_date) > :event_date";
    
          $ret =  Yii::$app->db->createCommand($strCount,[ ':event_date' => date("Y-m-d", time())] )->queryScalar();       
        return $ret;
   }   
/*************************/   
   
   
   public function getNoEvents()
   {            

    $cond = "";

   /*Произвольный контакт*/       
    /*все по которым нет активной работы*/
     $countquery  = new Query();
     $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")
                 /* AND event_date <= '".date("Y-m-d", time())."'*/
                 ;
    $countquery->where("isFirstContact =1 AND ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");            
    return $countquery->createCommand()->queryScalar();
   }   
/*************************/   

   
/*Получим  выполненные сегодня*/
   public function getFinishedTodayEvents($type)
   {            

    $cond = "";
   /*счета и заявки*/    
   if ($type == 1) { $cond = " AND (ref_event >2 AND ref_event <8)";}
   /*Произвольный контакт*/    
   if ($type == 2) { $cond = " AND (ref_event >7)";}    
   /*Холодная база*/    
   if ($type == 3) { $cond = " AND (ref_event <3)";}    
       
        $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=2 ".$cond."  AND  DATE(event_date) = :event_date";
    
          $ret =  Yii::$app->db->createCommand($strCount,[':event_date' => date("Y-m-d", time())] )->queryScalar();       
        return $ret;
   }   

/***/   
   public function getMarketDirectoryLeafValue()
   {
       /*Всего не согласованных запросов на отгрузку */
      $countquery  = new Query();
      $countquery->select (" count({{%request_supply}}.id)")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;
      $countquery->andWhere(['=', 'isSchetActive', 1]);      
      $countquery->andFilterWhere(['=', 'isAccepted', 0]);         
              
         $leafValue['supplyRequestNotAccepted'] = $countquery->createCommand()->queryScalar();    
         
         $strCount = "SELECT count({{%purchase_zakaz}}.id)  from {{%purchase_zakaz}}  where (isActive=1 And status = 0 AND zaprosType = 1)";            
         $leafValue['zaprosNotAccepted'] = Yii::$app->db->createCommand($strCount)->queryScalar();    
         
         
                   
   return $leafValue;
   
   }   
   
/***/   
   public function getLeafValue()
   {
    $curUser=Yii::$app->user->identity;
    $wareModel = new WarehouseForm();     
    $leafValue['storeStatus'] = $wareModel->getStoreFullnes();
   
       /*Всего не закрытых сделок*/
         $strCount = "SELECT count({{%zakaz}}.id) as C, sum(schetSumm) AS S from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where (isActive = 1 OR {{%schet}}.isSchetActive = 1 )";            
        $val= Yii::$app->db->createCommand($strCount)->queryOne();              
       $leafValue['allDeal'] = $val['C'];
       $leafValue['allDealSumm'] = number_format($val['S'],0,'.','&nbsp;');              

       
       /*Заявки, новые*/       
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%schet}}.id is null and (c.contactNumber < 2 or c.contactNumber is null)";            
        $leafValue['newZakaz'] = Yii::$app->db->createCommand($strCount)->queryScalar();              
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%schet}}.id is null and (c.contactNumber < 2 or c.contactNumber is null) 
           and {{%zakaz}}.ref_user=".$curUser->id;            
        $leafValue['newZakazMy'] = Yii::$app->db->createCommand($strCount)->queryScalar();              
        
        
        $strCount = "SELECT sum(count*value)  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN {{%zakazContent}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%zakazContent}}.isActive = 1 and {{%schet}}.id is null and c.contactNumber < 2";                    
        $leafValue['newZakazSumm'] =  number_format(Yii::$app->db->createCommand($strCount)->queryScalar(),0,'.','&nbsp;');              
                      
       /*Заявки в работе*/
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where isActive = 1 AND {{%schet}}.id is null and c.contactNumber > 1";            
        $leafValue['zakazInWork'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where isActive = 1 AND {{%schet}}.id is null and c.contactNumber > 1
           and {{%zakaz}}.ref_user=".$curUser->id;                     
        $leafValue['zakazInWorkMy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

        $strCount = "SELECT sum(count*value)  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN {{%zakazContent}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%zakazContent}}.isActive = 1 and {{%schet}}.id is null and c.contactNumber > 1";                    
        $leafValue['zakazInWorkSumm'] =  number_format(Yii::$app->db->createCommand($strCount)->queryScalar(),0,'.','&nbsp;');              


       
       /*Счета, нет оплаты и отгрузки*/
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%schet}} where {{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0 
          and docStatus = 0 and cashState =0 and supplyState = 0    ";            
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['newSchet'] = $val['C'];
       $leafValue['newSchetSumm'] = number_format($val['S'],0,'.','&nbsp;');              
       $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%schet}} where {{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0 
          and docStatus = 0 and cashState =0 and supplyState = 0  
          and {{%schet}}.refManager=".$curUser->id;                               
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['newSchetMy'] = $val['C'];
       
       
       /*Счета, нет оплаты и отгрузки */
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1   
         and docStatus > 0 AND  cashState =0 AND supplyState = 0 ";            
       $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['schetInWork']  = $val['C'];
       $leafValue['schetInWorkSumm']  = number_format($val['S'],0,'.','&nbsp;');              
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1   
         and docStatus > 0 AND  cashState =0 AND supplyState = 0 
         and {{%schet}}.refManager=".$curUser->id;                                        
       $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['schetInWorkMy']  = $val['C'];

       /*Ожидает отгрузки*/
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND supplyState = 1 ";            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyWait']  = $val['C'];
       $leafValue['supplyWaitSumm']  = number_format($val['S'],0,'.','&nbsp;');              

       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND supplyState = 1 
                 and {{%schet}}.refManager=".$curUser->id;                               
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyWaitMy']  = $val['C'];
       
       /*В процессе отгрузки*/
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4";            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyProc']  = $val['C'];
       $leafValue['supplyProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4
                 and {{%schet}}.refManager=".$curUser->id;                               
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyProcMy']  = $val['C'];
       
       /*В  оплате */
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 
       AND cashState > 1 AND cashState < 4 ";            
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['cashProc']  = $val['C'];
       $leafValue['cashProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 
       AND cashState > 1 AND cashState < 4 and {{%schet}}.refManager=".$curUser->id;                               
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['cashProcMy']  = $val['C'];


       /*В  работе */
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 
       AND (cashState > 1 AND cashState < 4) OR (supplyState > 1 AND supplyState < 4)  ";            
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['inWorkProc']  = $val['C'];
       $leafValue['inWorkProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              

       
       /*Ожидаем завершения */
        $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND cashState =4  
        AND supplyState > 3 ";            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['finitProc']  = $val['C'];
       $leafValue['finitProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
        $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND cashState =4  
        AND supplyState > 3 and {{%schet}}.refManager=".$curUser->id;                               
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['finitProcMy']  = $val['C'];
       
       /*Отгружено, в процессе оплаты*/
  /*       $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND summSupply >= schetSumm AND summOplata > 0 AND summOplata < schetSumm and {{%schet}}.ref1C IS NOT NULL ";            
       $leafValue[8] =Yii::$app->db->createCommand($strCount )->queryScalar();              */
       

       /*Число оплат в реестре на которые не назначена оплата*/
       $strCount = "SELECT count({{%reestr_oplat}}.id) as C, sum(ifnull(summRequest,0) -ifnull(summOplate,0)) as S from {{%reestr_oplat}} where isActive = 1 AND (summOplate < summRequest ) ";            
       $list=Yii::$app->db->createCommand($strCount )->queryAll();             
        
       $leafValue['oplateNInWok']  = $list[0]['C'];
       $leafValue['oplateSInWok']  = $list[0]['S'];

       

       
       
       /*Не совпадают суммы счетов оплат и поставок*/
         $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND (summSupply > schetSumm OR summOplata > schetSumm)  and {{%schet}}.ref1C IS NOT NULL ";            
       $leafValue[9] =Yii::$app->db->createCommand($strCount )->queryScalar();             

	   
	   $leafValue[10] =0;
	   
       /*Реестр клиентов*/
       $strCount = "
	   SELECT count(org.`id`)
FROM {{%orglist}} as org LEFT JOIN {{%user}} as b ON b.id = org.refManager 

LEFT JOIN (SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg 
from {{%oplata}} group by refOrg) as opl ON opl.refOrg = org.id 

LEFT JOIN (SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply
from {{%supply}} group by refOrg) as supl ON supl.refOrg = org.id 

WHERE   isOrgActive =1 AND	(ifnull(oplataSum,0)>0 OR ifnull(supplySum,0)>0)
";
   $leafValue[11] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   
   
 /*Число лидов со статусом обратить внимание*/
   $strCount = "SELECT MAX(syncTime) from {{%tmp_reestr}}";            
   $leafValue['lastReestrForm'] =Yii::$app->db->createCommand($strCount)->queryScalar();             
   $leadDuration = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
               ':key' => 2105,               
               ])->queryScalar();  
    $timeCond=" AND DATEDIFF(NOW(),{{%contact}}.contactDate) < ".$leadDuration;
    $strCount = "SELECT count({{%contact}}.id) from {{%contact}} where eventType > 10 && eventType < 20 ".$timeCond;            
    $leafValue['leadHeadCount'] =Yii::$app->db->createCommand($strCount)->queryScalar();             

   
   
   

   
       /*Закупки */
       
       
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =1 AND stage=1 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =3 AND stage=2 group by purchaseRef) as b on b.purchaseRef = {{%purchase}}.id
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =0 AND stage=1 group by purchaseRef) as a1 on a1.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =2 AND stage=2 group by purchaseRef) as b1 on b1.purchaseRef = {{%purchase}}.id
   where ((ifnull(a.sN,0) =0 AND ifnull(a1.sN,0) >0) OR (ifnull(b.sN,0) =0 AND ifnull(b1.sN,0) >0 ))";            
   $leafValue['purchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
       

   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where  isActive =1 ";            
   $leafValue['purchaseActive'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
    
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =4 AND stage=3 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id    
   where (ifnull(a.sN,0) =0 )";            
   $leafValue['purchaseActive'] +=Yii::$app->db->createCommand($strCount )->queryScalar();             
                    
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =1 AND stage=1 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =3 AND stage=2 group by purchaseRef) as b on b.purchaseRef = {{%purchase}}.id
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =0 AND stage=1 group by purchaseRef) as a1 on a1.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =2 AND stage=2 group by purchaseRef) as b1 on b1.purchaseRef = {{%purchase}}.id
   where ((ifnull(a.sN,0) =0 AND ifnull(a1.sN,0) >0) OR (ifnull(b.sN,0) =0 AND ifnull(b1.sN,0) >0 ))";            
   $leafValue['purchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             

   $countquery  = new Query();
   $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;        
   $countquery->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
   $leafValue['purchaseInSogl']  = $countquery->createCommand()->queryScalar();
   

   
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where {{%purchase_zakaz}}.status = 1 AND isActive =1 ";            
   $leafValue['purchase_zakaz'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   $leafValue['requestInSogl'] = $leafValue['purchase_zakaz'];    
       
   return $leafValue;   
   }
   
/*************************/   
   public function getStats()
   {
       //текущий
        $y=date('Y');
        $m=date('m');
        $d=date('d');
       
       //предыдущий 
        $py = $y;
        $pm = $m -1;
        if ($pm <= 0) {
            $pm=12;
            $py = $y-1;
        }
        
        
        $stats= array();              
        
        $curUser=Yii::$app->user->identity;
          $stats['m_events'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%calendar}} where  eventStatus=2
                                                              and year(event_date)=:y And month(event_date)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       
         $stats['p_events'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%calendar}} where  eventStatus=2
                                                              and year(event_date)=:y And month(event_date)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();                                                    
          
         $stats['d_events'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%calendar}} where  eventStatus=2
                                                              and year(event_date)=:y And month(event_date)=:m And day(event_date)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
           

                                                       
        /**/
        $stats['m_contacts'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  
        refZakaz = 0 And    year(contactDate)=:y And month(contactDate)=:m and ifnull(eventType,0) = 0'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_contacts'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  
        refZakaz = 0 And    year(contactDate)=:y And month(contactDate)=:m and ifnull(eventType,0) = 0'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();                                                    
          
        $stats['d_contacts'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where   
        refZakaz = 0 And    year(contactDate)=:y And month(contactDate)=:m And day(contactDate)=:d and ifnull(eventType,0) = 0'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
                                             
        /**/
        $stats['m_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where  
                                                              year(formDate)=:y And month(formDate)=:m'
                                             ,[':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where  
                                                              year(formDate)=:y And month(formDate)=:m'
                                             ,[':y'=>$py,':m'=>$pm, ] )->queryScalar();       

                                             
        $stats['d_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where   
                                                              year(formDate)=:y And month(formDate)=:m And day(formDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        /**/                                     
        $stats['m_schet'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%schet}} where  
                                                              year(schetDate)=:y And month(schetDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_schet'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%schet}} where  
                                                              year(schetDate)=:y And month(schetDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();       

                                             
        $stats['d_schet'] =  Yii::$app->db->createCommand('SELECT count(id)   from {{%schet}} where 
                                                              year(schetDate)=:y And month(schetDate)=:m And day(schetDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        
        /**/
        $stats['m_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from  {{%oplata}}  where  year(oplateDate)=:y And month(oplateDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from  {{%oplata}}  where  year(oplateDate)=:y And month(oplateDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();       

                                             
        $stats['d_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from {{%oplata}}  where year(oplateDate)=:y And month(oplateDate)=:m And day(oplateDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        
        /**/    
        $stats['m_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from  {{%supply}}  where    year(supplyDate)=:y And month(supplyDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from  {{%supply}}  where    year(supplyDate)=:y And month(supplyDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();       

        $stats['d_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from {{%supply}}  where  year(supplyDate)=:y And month(supplyDate)=:m And day(supplyDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        /**/
        $stats['m_supplier'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(wareSumm),0) as wareSumm
        from  {{%supplier_wares}}  where    year(requestDate)=:y And month(requestDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       

        $stats['p_supplier'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(wareSumm),0) as wareSumm
        from  {{%supplier_wares}}  where    year(requestDate)=:y And month(requestDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();       

        $stats['d_supplier'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(wareSumm),0) as wareSumm
        from {{%supplier_wares}}  where  year(requestDate)=:y And month(requestDate)=:m And day(requestDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        /**/
        
        $stats['m_activity'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  
          year(contactDate)=:y And month(contactDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();                                                    
        $stats['m_activity'] +=  $stats['m_zakaz']+ $stats['m_schet'];
        $stats['m_activity'] +=  Yii::$app->db->createCommand('SELECT  count(id) from  {{%oplata}} 
        where  year(oplateDate)=:y And month(oplateDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       
        $stats['m_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%supply}}.id) from  {{%supply}} 
        where  year(supplyDate)=:y And month(supplyDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();                                                
        $stats['m_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%request_supply}}.id) from  {{%request_supply}} 
        where  year(requestDate)=:y And month(requestDate)=:m'
                                             ,[ ':y'=>$y,':m'=>$m, ] )->queryScalar();       
        

        $stats['p_activity'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  
         year(contactDate)=:y And month(contactDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();                                                                                                
        $stats['p_activity'] +=  $stats['p_zakaz']+ $stats['p_schet'];
        $stats['p_activity'] +=  Yii::$app->db->createCommand('SELECT  count(id) from  {{%oplata}} 
        where  year(oplateDate)=:y And month(oplateDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();       
        $stats['p_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%supply}}.id) from  {{%supply}} 
        where  year(supplyDate)=:y And month(supplyDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar();                                                
       $stats['p_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%request_supply}}.id) from  {{%request_supply}} 
        where  year(requestDate)=:y And month(requestDate)=:m'
                                             ,[ ':y'=>$py,':m'=>$pm, ] )->queryScalar(); 



                                             
        $stats['d_activity'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where   
         year(contactDate)=:y And month(contactDate)=:m And day(contactDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        $stats['d_activity'] +=  $stats['d_zakaz']+ $stats['d_schet'];
        $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  count(id) from  {{%oplata}} 
        where  year(oplateDate)=:y And month(oplateDate)=:m And day(oplateDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%supply}}.id) from  {{%supply}} 
        where  year(supplyDate)=:y And month(supplyDate)=:m And day(supplyDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        $stats['d_activity'] +=  Yii::$app->db->createCommand('SELECT  COUNT({{%request_supply}}.id) from  {{%request_supply}} 
        where  year(requestDate)=:y And month(requestDate)=:m And day(requestDate)=:d'
                                             ,[ ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
       


        $stats['p_extract'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} LEFT JOIN `rik_orglist` ON `rik_orglist`.id = `rik_bank_extract`.orgRef 
        where  year(recordDate)=:y And month(recordDate)=:m 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО%") 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКИЙ БАНК ПАО СБЕРБАНК%") 
        AND (`rik_bank_extract`.extractType = 1)'
                                             ,[':y'=>$py,':m'=>$pm, ] )->queryScalar();       
              

       $stats['m_extract'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} LEFT JOIN `rik_orglist` ON `rik_orglist`.id = `rik_bank_extract`.orgRef 
        where  year(recordDate)=:y And month(recordDate)=:m 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО%") 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКИЙ БАНК ПАО СБЕРБАНК%") 
        AND (`rik_bank_extract`.extractType = 1)'
                                             ,[':y'=>$y,':m'=>$m, ] )->queryScalar();       
       
     /*  echo  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} where  year(recordDate)=:y And month(recordDate)=:m 
        AND (`rik_bank_extract`.extractType = 1) 
        and debetOrgTitle not like "СИБИРСКИЙ БАНК ПАО СБЕРБАНК" and debetOrgTitle not like "СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО"'
                                             ,[':y'=>$y,':m'=>$m, ] )->getRawSql();  
*/

        $stats['d_extract'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} LEFT JOIN `rik_orglist` ON `rik_orglist`.id = `rik_bank_extract`.orgRef 
        where  year(recordDate)=:y And month(recordDate)=:m And day(recordDate)=:d
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО%") 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКИЙ БАНК ПАО СБЕРБАНК%") 
        AND (`rik_bank_extract`.extractType = 1)'
                                             ,[':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       

        $stats['last_extract'] =  Yii::$app->db->createCommand('SELECT  max(creationDate) from {{%bank_header}}' )->queryScalar();                                                    
                   
           

      /**/

        
        return $stats;
   }   
/************************************/   
/*********  Providers ***************/   
/************************************/

 public function prepareClientReestrData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов с которыми были финансовые взаимоотношения */
	
    $countquery->select ("count(distinct org.id)")
                 ->from("{{%orglist}} as org")
                 ->leftJoin("{{%user}}", "{{%user}}.id = org.refManager")
                 ->leftJoin("(SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg from {{%oplata}} group by refOrg) as opl", "opl.refOrg = org.id")
                 ->leftJoin("(SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = org.id ")
                 ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  
                 {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0) as goods ", "goods.refOrg =  org.id ")                 
                 ->leftJoin("(SELECT count({{%zakaz}}.id) as activity, {{%zakaz}}.refOrg from {{%zakaz}} left join {{%schet}} on  {{%zakaz}}.id={{%schet}}.refZakaz where (isActive=1 OR isSchetActive = 1) GROUP BY refOrg) as act ", "act.refOrg =  org.id ")                 
                 ->leftJoin("(SELECT COUNT(ID) as N, refOrg  from {{%oplata}} where ({{%oplata}}.oplateDate is not null) group by refOrg) as reg", "reg.refOrg =  org.id  ")                                          
                 ->leftJoin("{{%org_category}}", "{{%org_category}}.id = org.orgTypeRef")              
              ;
                  
     $query->select([
	 '{{%orglist}}.id',
	 'title', 
	 'userFIO', 
	 'oplataCnt', 
	 'supplyCnt', 
	 'ifnull(oplataSum, 0) as oplata',
	 'ifnull(supplySum, 0) as supply',	 
	 '(ifnull(supplySum, 0) - ifnull(oplataSum, 0)) as balance', 
	'lastOplate', 
	'lastSupply',	 
    '{{%orglist}}.contactPhone',
    '{{%orglist}}.contactEmail',
    'ifnull(activity,0) as active',
//    'adr.city','adr.district', 'adr.adress',
    'reg.N as regN',
    'orgTypeRef',
    'catTitle',
	 ]) ->from("{{%orglist}}")
        ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
        ->leftJoin("(SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg from {{%oplata}} group by refOrg) as opl", "opl.refOrg = {{%orglist}}.id")
        ->leftJoin("(SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = {{%orglist}}.id ")
        ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
        ->leftJoin("(SELECT count({{%zakaz}}.id) as activity, {{%zakaz}}.refOrg from {{%zakaz}} left join {{%schet}} on  {{%zakaz}}.id={{%schet}}.refZakaz where (isActive=1 OR isSchetActive = 1) GROUP BY refOrg) as act ", "act.refOrg =  {{%orglist}}.id ")                 
//        ->leftJoin("(SELECT city, district, adress, ref_org from {{%adreslist}}  where isBad =0 group by ref_org ) as adr ", "adr.ref_org =  {{%orglist}}.id ")                         
        ->leftJoin("(SELECT COUNT(ID) as N, refOrg  from {{%oplata}} where ({{%oplata}}.oplateDate is not null) group by refOrg) as reg", "reg.refOrg =  {{%orglist}}.id ")                         
        ->leftJoin("{{%org_category}}", "{{%org_category}}.id = {{%orglist}}.orgTypeRef")
        ->distinct()
        ;
            
      $countquery->where(" isOrgActive =1 AND	(ifnull(oplataSum,0)>0 OR ifnull(supplySum,0)>0)");            
      $query->where("  isOrgActive =1 AND (ifnull(oplataSum,0)>0 OR ifnull(supplySum,0)>0) ");            
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
          
        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        $countquery->andFilterWhere(['like', 'goodlist', $this->fltGood]);

        if (!empty($this->catTitle))
        {
        $query->andFilterWhere(['like', 'catTitle', $this->catTitle]);
        $countquery->andFilterWhere(['like', 'catTitle', $this->catTitle]);
        }
        
        /*"1" => "Все",*/
        if ($this->balance == 1 ) 
        {
/*          $query->andFilterWhere(['>', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);
            $countquery->andFilterWhere(['>', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);            */
        }
        /*"2" => "Регулярные",				*/
        if ($this->balance == 2 ) 
        {

           $query->andFilterWhere(['>', 'reg.N', 2]);
           $countquery->andFilterWhere(['>', 'reg.N', 2]);           
    
/*    $query->andFilterWhere(['<=', 'ABS(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);
            $countquery->andFilterWhere(['<=', 'ABS(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);            */
        }
        /*"3" => "Мы должны",*/
        if ($this->balance == 3 ) 
        {
           $query->andFilterWhere(['<', 'reg.N', 3]);
           $countquery->andFilterWhere(['<', 'reg.N', 3]);           

/*    $query->andFilterWhere(['<', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', -0.9]);
            $countquery->andFilterWhere(['<', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', -0.9]);            */
        }
        
        
        /*"1" => "Нет сделок",*/
        if ($this->active == 1 ) 
        {
            $query->andFilterWhere(['=', 'ifnull(activity,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(activity,0)', 0]);            
        }

        /*"2" => "Есть сделки",	*/
        if ($this->active == 2 ) 
        {
            $query->andFilterWhere(['>', 'activity', 0]);
            $countquery->andFilterWhere(['>', 'activity', 0]);            
        }
        
     }
     
//$this->debug = $query->createCommand()->getRawSql();     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

   }
   
   public function getClientReestrData ($params)
   {
        $this->prepareClientReestrData($params);

        $dataList=$this->command->queryAll();

    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;

    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Менеджер основной"), 
        iconv("UTF-8", "Windows-1251","Менеджер активность"), 

        iconv("UTF-8", "Windows-1251","Сверка"),
        iconv("UTF-8", "Windows-1251","Чек"),
        iconv("UTF-8", "Windows-1251","Регулярность"),
        iconv("UTF-8", "Windows-1251","Период"),
        iconv("UTF-8", "Windows-1251","План на период"),
        iconv("UTF-8", "Windows-1251","Факт за период"),
        
        iconv("UTF-8", "Windows-1251","Средняя периодичность"),
        iconv("UTF-8", "Windows-1251","Дней до оплаты (ср)"),
        iconv("UTF-8", "Windows-1251","Активных сделок"),
        
        iconv("UTF-8", "Windows-1251","Контактов на сделку"),

        iconv("UTF-8", "Windows-1251","Дата отгрузки"),        
        iconv("UTF-8", "Windows-1251","Дата оплаты"),

        iconv("UTF-8", "Windows-1251","Товары"),
        
        iconv("UTF-8", "Windows-1251","Телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        

        iconv("UTF-8", "Windows-1251","Город"),        
        iconv("UTF-8", "Windows-1251","Район"),        
        iconv("UTF-8", "Windows-1251","Адрес"),        
        iconv("UTF-8", "Windows-1251","Категория"), 

		
        );
        fputcsv($fp, $col_title, ";"); 
    for ($i=0; $i< count($dataList); $i++)
    {    
        $list =  $this->getClientReestrRow($dataList[$i]);    
        fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }


    public function getClientReestrRow ($dataRow)  {
    

     $contactList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND 
                (contactDate) <= :rangeDate AND  {{%contact}}.ref_org = :ref_org group by userFIO
                ORDER BY cur DESC", 
				[
				':rangeDate' =>$dataRow['lastOplate'],
				':ref_org' => $dataRow['id'],
				])->queryAll();

      $period = "";
      $plan  = "";
      $fact = "";
                
     $val    ="" ;      
     $valOther ="";
     for ($j=0; $j < count ($contactList); $j++)
                {
                if ($contactList[$j]['userFIO'] == $dataRow['userFIO'])    
                    $val = $contactList[$j]['userFIO'].": ". $contactList[$j]['cur']." | ";
                else
                    $valOther .= $contactList[$j]['userFIO'].": ". $contactList[$j]['cur']." | ";
                }
      $managerActivity= $val.$valOther; 
       
       
      /* средний чек */ 
      $avgCheck = Yii::$app->db->createCommand("SELECT AVG(summSupply) as av_schet from {{%schet}} where refOrg = :ref_org", 
                [':ref_org' => $dataRow['id'],])->queryScalar();
    
    /*Получим список оплат*/
    if ($dataRow['regN'] < 3) 
    {
       $regularity= "Разовый";
    }
    else 
    {
      $regularity= "Регулярный";
      
      $listOplat = Yii::$app->db->createCommand("SELECT oplateDate, oplateSumm  from {{%oplata}} 
                where refOrg = :ref_org ORDER BY oplateDate ASC", 
                [':ref_org' => $dataRow['id'],])->queryAll();
      /*Ищем первую и последнюю даты*/
      $start=0;
      $startD=0;
      $end=0;
      $cntOpl = count($listOplat);
      for ($i=0; $i<$cntOpl;$i++) { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $start=$i;    
                    $startD = $listOplat[$i]['oplateDate'];
                    break; }
                
      for ($i=$cntOpl-1; $i>$start;$i--) { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $end=$i;    
                    $endD = $listOplat[$i]['oplateDate'];
                    break;}
                
                if ($startD == 0 || $endD == 0)  $regularity = "Данные не валидны";                
                elseif ($end - $start < 2) $regularity = "Мало данных";
                else 
                {                
                //Средний период между оплатами
                    $avDT =  (strtotime($endD) - strtotime($startD))/($end - $start);
                    $trash = 60*60*24*90;
                    if ($avDT < $trash) $avDT = $trash;
                    /*Учитываем, что часть данных была уже выкинута сканируем на наличие черезчур длинных промежутков*/
                    $fD = strtotime($startD); 
                    $validN=-1;
                    $validT=0;
                    $sumOp = 0;
                    for ($i=$start; $i<=$end;$i++)
                    { 
                        $sumOp+= $listOplat[$i]['oplateSumm'];   
                        if (empty($listOplat[$i]['oplateDate']))continue;                                        
                        $cur =  strtotime($listOplat[$i]['oplateDate']);
                        $period = $cur - $fD;
                        $fD = $cur;                                      
                        if ($period <= $avDT) { $validT += $period; $validN ++;  }
                    }
                    if ($validN > 0) {
                     $dT = $validT/$validN;       
                     $dT = intval($dT/(60*60*24));
                     if ($dT <= 0) $regularity = "Данные не валидны";          
                     else
                     {
                       $period =$dT;   
                       $plan   = number_format($sumOp/$validN,2,'.',' ');    
                       $sumPer = Yii::$app->db->createCommand("SELECT SUM(oplateSumm)  from {{%oplata}} 
                            where refOrg = :ref_org AND (TO_DAYS(NOW()) - TO_DAYS(oplateDate)) < :period",
                            [':ref_org' => $dataRow['id'],
                            ':period'  => $dT
                            ])->queryScalar();
                        $fact = "Факт:".number_format($sumPer,2,'.',' ');
                     }
                   }//$validN > 0
                }//данных достаточно
    }//Регулярный клиент
                 
       /*активность*/          
      if ($dataRow['active'] ==0) $activity = "Нет сделок";
                                  else $activity = "В раб.: ".$dataRow['active'];
                 
      $strSql  = 'SELECT count(id) as N, to_days(max(schetDate)) - to_days(min(schetDate)) AS l, max(schetDate) as max_d, min(schetDate) as min_d ';
      $strSql .= 'from {{%schet}} where summSupply > 0 AND  refOrg = :ref_org';  
      $periodData = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryOne();
        
      /*периодичность, дней до оплаты*/        
      $per= " ";
      $op =" ";
      if ($periodData['N'] > 0) 
      {
         $per = intval($periodData['l']/$periodData['N']);

        $strSql  = "SELECT schetDate, MIN(oplateDate), to_days(MIN(oplateDate)) - to_days(schetDate) AS l from {{%oplata}} ";
        $strSql .= "where  schetDate is not NULL and   schetDate >'1980-01-01'  and  oplateDate is not NULL and   schetDate >'1980-01-01' ";
        $strSql .= "and  {{%oplata}}.refOrg = :ref_org  group by schetNum";
        $oplateData = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryAll();
        $N =count($oplateData); 
        if ($N > 0){ $sper=0;  for($j=0; $j < $N; $j++)$sper+=$oplateData[$j]['l'];   $op=intval($sper/$N);  }                 
      }

      /*Сред число = число контактов/ число счетов с поставками*/
       $strSql  = 'SELECT count(id) as N, date(MIN(ContactDate)) as minD from {{%contact}} where ref_org = :ref_org';
       $contactData = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryOne();
       $strSql ='SELECT count(id) from {{%schet}} where summSupply > 0 and schetDate > :minD and refOrg = :ref_org';
       $schetNum = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'], ':minD' => $contactData['minD'] ])->queryScalar();                 
       if ($schetNum == 0) $avVal = "N/A";
                      else $avVal = intval($contactData['N']/$schetNum);
 
       /*Товары*/ 
   
       $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
       $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
       $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";
                                  
	   $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryAll();
       $glist="";  for($j=0;$j<count($resList);$j++) {  $glist.= $resList[$j]['good']." | ";  }

        

       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataRow['title']),  
        iconv("UTF-8", "Windows-1251",$dataRow['userFIO']),
        iconv("UTF-8", "Windows-1251",$managerActivity),
		
        iconv("UTF-8", "Windows-1251",number_format($dataRow['balance'], 2, '.', ' ')),    		
        iconv("UTF-8", "Windows-1251",number_format($avgCheck,2,'.',' ')),    		
        iconv("UTF-8", "Windows-1251",$dataRow['regN']),
        iconv("UTF-8", "Windows-1251",$period),
        iconv("UTF-8", "Windows-1251",$plan),
        iconv("UTF-8", "Windows-1251",$fact),
        
        
        iconv("UTF-8", "Windows-1251",$per),
        iconv("UTF-8", "Windows-1251",$op),
        iconv("UTF-8", "Windows-1251",$activity),

        iconv("UTF-8", "Windows-1251",$avVal),
        

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastOplate']))), 
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSupply']))), 

        iconv("UTF-8", "Windows-1251",$glist),
        
        iconv("UTF-8", "Windows-1251",$dataRow['contactPhone']),    
        iconv("UTF-8", "Windows-1251",$dataRow['contactEmail']),    		
        
     /*   iconv("UTF-8", "Windows-1251",$dataRow['city']),    
        iconv("UTF-8", "Windows-1251",$dataRow['district']),    		
        iconv("UTF-8", "Windows-1251",$dataRow['adress']),    		*/
        iconv("UTF-8", "Windows-1251",$dataRow['catTitle']),    		
        
        );
     
  return $list;       
       
   }
/*****************************************************************************************/

     public function fillClientReestrData ()
   {
        $this->prepareClientReestrData(array());
        $dataList=$this->command->queryAll();
        
        Yii::$app->db->createCommand("DELETE FROM {{%tmp_reestr}}")->execute(); 
        for ($i=0; $i< count($dataList); $i++)
        {    
            $list =  $this->getClientReestrRowFill($dataList[$i]);    
            $record = new TmpOrgReestr();
            
            $record->refOrg = $list['refOrg'];
            $record->orgTitle= $list['orgTitle'];
            $record->managerFIO= $list['managerFIO'];
            $record->mainActivity= $list['mainActivity'];
            $record->otherActivity= $list['otherActivity'];
            $record->avgCheck= $list['avgCheck'];
            $record->balance= $list['balance'];
            $record->regular= $list['regular'];
            $record->regState= $list['regState'];            
            $record->period= $list['period'];
            $record->periodStart= date ('Y-m-d', time()-$list['period']*24*60*60) ;
            $record->plan= $list['plan'];
            $record->fact= $list['fact'];
            $record->lastOplate= $list['lastOplate'];
            $record->lastSupply= $list['lastSupply'];
                        
            $record->lastSchet= $list['lastSchet'];
            $record->lastActiveSchet= $list['lastActiveSchet'];
            $record->lastSdelka= $list['lastSdelka'];
            
            $record->lastContact= $list['lastContact'];
            $record->lastZakaz= $list['lastZakaz'];
            $record->lastActiveZakaz= $list['lastActiveZakaz'];
            
            $record->city= $list['city'];
            $record->district= $list['district'];
            $record->adress= $list['adress'];
                        
            $record->category= $list['category'];
            $record->categoryTitle= $list['catTitle'];
            $record->save();
     
     }
        
   }

    public function getClientReestrRowFill ($dataRow)  {
    

     $contactList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND 
                (contactDate) <= :rangeDate AND  {{%contact}}.ref_org = :ref_org group by userFIO
                ORDER BY cur DESC", 
				[
				':rangeDate' =>$dataRow['lastOplate'],
				':ref_org' => $dataRow['id'],
				])->queryAll();

      $period = 14;
      $plan  = "";
      $fact = "";
                
     $val    ="" ;      
     $valOther ="";
     $mainActivity=0;
     for ($j=0; $j < count ($contactList); $j++)
                {
                if ($contactList[$j]['userFIO'] == $dataRow['userFIO'])    
                {
                    $mainActivity = $contactList[$j]['cur'];
                }
                else
                    $valOther .= $contactList[$j]['userFIO'].": ". $contactList[$j]['cur']." <br> ";
                }
      $managerActivity= $val.$valOther; 
       
       
      /* средний чек */ 
      $avgCheck = Yii::$app->db->createCommand("SELECT AVG(summSupply) as av_schet from {{%schet}} where refOrg = :ref_org", 
                [':ref_org' => $dataRow['id'],])->queryScalar();
                
                
    $strSql  = 'SELECT MAX(schetDate) FROM {{%schet}} where refOrg = :ref_org and isSchetActive = 1';
    $lastActiveSchet = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryScalar();

    $strSql  = 'SELECT MAX(schetDate) FROM {{%schet}} where refOrg = :ref_org';
    $lastSchet = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryScalar();


    
    $strSdelka  = 'SELECT MAX({{%schet}}.schetDate) FROM {{%schet}}  where refOrg = :ref_org and (summSupply >0 OR summOplata > 0)';
    $lastSdelka = Yii::$app->db->createCommand($strSdelka, [':ref_org' => $dataRow['id'],])->queryScalar();
    
    $strSql  = 'SELECT MAX(contactDate) FROM {{%contact}}  where ref_org = :ref_org ';
    $lastContact = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryScalar();

    $strSql  = 'SELECT MAX(formDate) FROM {{%zakaz}} left join {{%schet}} on {{%zakaz}}.id =  {{%schet}}.refZakaz
    where {{%zakaz}}.refOrg = :ref_org and (isActive =1 OR isSchetActive = 1)';
    $lastActiveZakaz = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryScalar();    
    $strSql  = 'SELECT MAX(formDate) FROM {{%zakaz}}  where refOrg = :ref_org ';
    $lastZakaz = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryScalar();
    
    
    
    $strSql  = 'SELECT city, district, adress from {{%adreslist}}  where isBad =0 AND ref_org = :ref_org  ORDER BY isOfficial DESC LIMIT 1'; 
    $adresList = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['id'],])->queryAll();
      
    if(empty($adresList))
    {
        $adresList[0]['city'] = "";
        $adresList[0]['district'] = "";
        $adresList[0]['adress'] = "";        
    }
      
    /*Получим список оплат*/
    $regN  = $dataRow['regN'];
    if ($dataRow['regN'] < 3) 
    {
       $regularity= "Разовый";
    }
    else 
    {
      $regularity= "Регулярный";
      
      $listOplat = Yii::$app->db->createCommand("SELECT oplateDate, oplateSumm  from {{%oplata}} 
                where refOrg = :ref_org ORDER BY oplateDate ASC", 
                [':ref_org' => $dataRow['id'],])->queryAll();
      /*Ищем первую и последнюю даты*/
      $start=0;
      $startD=0;
      $end=0;
      $cntOpl = count($listOplat);
      for ($i=0; $i<$cntOpl;$i++) { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $start=$i;    
                    $startD = $listOplat[$i]['oplateDate'];
                    break; }
                
      for ($i=$cntOpl-1; $i>$start;$i--) { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $end=$i;    
                    $endD = $listOplat[$i]['oplateDate'];
                    break;}
     $regN = $end - $start;           
                if ($startD == 0 || $endD == 0)  $regularity = "Данные не валидны";                
                elseif ($regN  < 2) $regularity = "Мало данных";
                else 
                {                
                //Средний период между оплатами
                    $avDT =  (strtotime($endD) - strtotime($startD))/($end - $start);
                    $trash = 60*60*24*90;
                    if ($avDT < $trash) $avDT = $trash;
                    /*Учитываем, что часть данных была уже выкинута сканируем на наличие черезчур длинных промежутков*/
                    $fD = strtotime($startD); 
                    $validN=-1;
                    $validT=0;
                    $sumOp = 0;
                    for ($i=$start; $i<=$end;$i++)
                    { 
                        $sumOp+= $listOplat[$i]['oplateSumm'];   
                        if (empty($listOplat[$i]['oplateDate']))continue;                                        
                        $cur =  strtotime($listOplat[$i]['oplateDate']);
                        $period = $cur - $fD;
                        $fD = $cur;                                      
                        if ($period <= $avDT) { $validT += $period; $validN ++;  }
                    }
                    if ($validN > 0) {
                     $dT = $validT/$validN;       
                     $dT = intval($dT/(60*60*24));
                     if ($dT <= 0) $regularity = "Данные не валидны";          
                     else
                     {
                       $period =max($dT, 14);   
                       $plan   = $sumOp/$validN; //number_format($sumOp/$validN,2,'.',' ');    
                       $sumPer = Yii::$app->db->createCommand("SELECT SUM(oplateSumm)  from {{%oplata}} 
                            where refOrg = :ref_org AND (TO_DAYS(NOW()) - TO_DAYS(oplateDate)) < :period",
                            [':ref_org' => $dataRow['id'],
                            ':period'  => $dT
                            ])->queryScalar();
                        $fact = $sumPer;//"Факт:".number_format($sumPer,2,'.',' ');
                     }
                   }//$validN > 0
                }//данных достаточно
    }//Регулярный клиент
                 
    $list = array 
        (
        'refOrg' => $dataRow['id'],  
        'orgTitle' => $dataRow['title'],  
        'managerFIO' => $dataRow['userFIO'],
        'mainActivity' => $mainActivity,
        'otherActivity' => $managerActivity,
        'avgCheck' => $avgCheck,    		
        'balance' => $dataRow['balance'],    		
        'regular' => $regN,
        'regState' => $regularity,
        'period' => $period,
        'plan' => $plan,
        'fact' => $fact,               
        'lastOplate' => $dataRow['lastOplate'], 
        'lastSupply' => $dataRow['lastSupply'],           
        'lastSchet'  => $lastSchet,
        'lastActiveSchet'  => $lastActiveSchet,
        'lastSdelka' => $lastSdelka,
        'lastContact' =>$lastContact,
        'lastZakaz' => $lastZakaz,      
        'lastActiveZakaz' => $lastActiveZakaz,      
        'city' => $adresList[0]['city'],
        'district' => $adresList[0]['district'],
        'adress' => $adresList[0]['adress'],          
        'catTitle'  => $dataRow['catTitle'],    		        
        'category'  => $dataRow['orgTypeRef'],  


        
        );
     
  return $list;       
       
   }
 /****************************************************************************************/
 public function prepareSavedClientReestr($params)
 {
         
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов с которыми были финансовые взаимоотношения */
	
    $countquery->select ("count(distinct {{%tmp_reestr}}.refOrg)")
                 ->from("{{%tmp_reestr}}")
                 ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")
                 ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
                  ;
                  
     $query->select([
        '{{%tmp_reestr}}.refOrg',
        'orgTitle',
        '{{%orglist}}.schetINN',        
        'managerFIO',
        'mainActivity',
        'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'regState',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastActiveSchet',
        'lastSdelka',
        'lastContact',
        'lastZakaz',      
        'lastActiveZakaz',
        'city',
        'district',
        'adress',          
        'category',
        'categoryTitle',
        //'orgTypeRef',
        '{{%orglist}}.contactPhone',
        '{{%orglist}}.contactEmail',
        'periodStart',
    
	 ]) ->from("{{%tmp_reestr}}")
        ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")
        ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
        ->distinct()
        ;
            
            
     if(!empty($this->fltCategory))
     {
        $query->andFilterWhere(['=', 'category', $this->fltCategory]);
        $countquery->andFilterWhere(['=', 'category', $this->fltCategory]);
     }       
            
$this->debug[] = $this->fltCategory;             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', 'managerFIO', $this->managerFIO]); 
        $countquery->andFilterWhere(['like', 'managerFIO', $this->managerFIO]);

        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
          
        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        $countquery->andFilterWhere(['like', 'goodlist', $this->fltGood]);

        if (!empty($this->catTitle))
        {
        $query->andFilterWhere(['like', 'categoryTitle', $this->catTitle]);
        $countquery->andFilterWhere(['like', 'categoryTitle', $this->catTitle]);
        }
        
        /*"1" => "Все",*/
        if ($this->regular == 1 ) 
        {

        }
        /*"2" => "Регулярные",				*/
        if ($this->regular == 2 ) 
        {
           $query->andFilterWhere(['>', 'regular', 2]);
           $countquery->andFilterWhere(['>', 'regular', 2]);           
        }
        /*"2" => "Разовые",				*/
        if ($this->regular == 3 ) 
        {
           $query->andFilterWhere(['<', 'regular', 3]);
           $countquery->andFilterWhere(['<', 'regular', 3]);           
        }
        
        /*"1" => "Все",*/
        if ($this->execution == 1 ) 
        {

        }
        /*"2" => "Не исполнен",				*/
        if ($this->execution == 2 ) 
        {
           $query->andFilterWhere(['<', '[[fact]]-[[plan]]', 0]);
           $countquery->andFilterWhere(['<', '[[fact]]-[[plan]]', 0]);           
        }
        /*"2" => "Выполнен",				*/
        if ($this->execution == 3 ) 
        {
           $query->andFilterWhere(['>=', '[[fact]]-[[plan]]', 0]);
           $countquery->andFilterWhere(['>=', '[[fact]]-[[plan]]', 0]);           
        }
        
        
        /*Активные контакты*/
        if ($this->lastContact == 2 ) 
        {
           $query->andWhere('[[lastContact]] >= [[periodStart]]');
           $countquery->andWhere('[[lastContact]] >= [[periodStart]]');
        }
        
        /*Активные Заявки*/
        if ($this->lastContact == 3 ) 
        {
            
           $query->andWhere('[[lastActiveZakaz]] >= [[periodStart]]');
           $countquery->andWhere('[[lastActiveZakaz]] >= [[periodStart]]');            
        }
        

        /*Активные отгрузки*/
        if ($this->lastSupply == 2 ) 
        {
           $query->andWhere('[[lastSupply]] >= [[periodStart]]');
           $countquery->andWhere('[[lastSupply]] >= [[periodStart]]');
        }
        
        /*Активные оплаты*/
        if ($this->lastSupply == 3 ) 
        {
            
           $query->andWhere('[[lastOplate]] >= [[periodStart]]');
           $countquery->andWhere('[[lastOplate]] >= [[periodStart]]');            
        }

        /*Активные счета*/
        if ($this->lastSupply == 4 ) 
        {
            
           $query->andWhere('[[lastActiveSchet]] >= [[periodStart]]');
           $countquery->andWhere('[[lastActiveSchet]] >= [[periodStart]]');            
        }
        
        
     }
     
//$this->debug = $query->createCommand()->getRawSql();     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();
       //echo $countquery->createCommand()->getRawSql(0);
 }
 /****************************************************************************************/
   public function getSavedClientReestrProvider($params)
   {

        $this->prepareSavedClientReestr($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
     
        'orgTitle',
        'managerFIO',
        'mainActivity',
        'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastSdelka',
        'category',
        'categoryTitle',

            ],
            'defaultOrder' => [    'orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/************************************************************/   
   
   public function getSavedClientReestrData($params)
   {
        $this->prepareSavedClientReestr($params);
        $dataList=$this->command->queryAll();     
    
   
    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","ИНН"),
        iconv("UTF-8", "Windows-1251","Менеджер основной"), 
        iconv("UTF-8", "Windows-1251","Менеджер основной активность"), 
        iconv("UTF-8", "Windows-1251","Менеджеры активность"), 

        
        iconv("UTF-8", "Windows-1251","Чек"),
        iconv("UTF-8", "Windows-1251","Регулярность"),
        iconv("UTF-8", "Windows-1251","Период"),
        iconv("UTF-8", "Windows-1251","План на период"),
        iconv("UTF-8", "Windows-1251","Факт за период"),
                
        iconv("UTF-8", "Windows-1251","Начало периода"),     
        
        iconv("UTF-8", "Windows-1251","Дата отгрузки"),        
        iconv("UTF-8", "Windows-1251","Дата оплаты"),

        iconv("UTF-8", "Windows-1251","Последний счет"),        
        iconv("UTF-8", "Windows-1251","Последняя сделка"),

        iconv("UTF-8", "Windows-1251","Последний заказ"),        
        iconv("UTF-8", "Windows-1251","Последний контакт"),

        iconv("UTF-8", "Windows-1251","Активный заказ"),        
        iconv("UTF-8", "Windows-1251","Активный счет"),

        
        iconv("UTF-8", "Windows-1251","Товары"),
        
        iconv("UTF-8", "Windows-1251","Сверка"),
        
        iconv("UTF-8", "Windows-1251","Телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),
        
        iconv("UTF-8", "Windows-1251","Город"),        
        iconv("UTF-8", "Windows-1251","Район"),        
        iconv("UTF-8", "Windows-1251","Адрес"),  
        
        iconv("UTF-8", "Windows-1251","Категория"), 

		
        );
        fputcsv($fp, $col_title, ";"); 
    for ($i=0; $i< count($dataList); $i++)
    {    
        $list =  $this->getSavedClientReestrRow($dataList[$i]);    
        fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }


    public function getSavedClientReestrRow ($dataRow)  {
    

     
       /*Товары*/ 
   
       $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
       $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
       $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";
                                  
	   $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['refOrg'],])->queryAll();
       $glist="";  for($j=0;$j<count($resList);$j++) {  $glist.= $resList[$j]['good']." | ";  }
       
    $list = array 
        (



        iconv("UTF-8", "Windows-1251",$dataRow['orgTitle']),
        iconv("UTF-8", "Windows-1251",$dataRow['schetINN']),
        iconv("UTF-8", "Windows-1251",$dataRow['managerFIO']), 
        iconv("UTF-8", "Windows-1251",$dataRow['mainActivity']), 
        iconv("UTF-8", "Windows-1251",preg_replace("/\<br\>/"," | ",$dataRow['otherActivity'])), 

        
        iconv("UTF-8", "Windows-1251",$dataRow['avgCheck']),
        iconv("UTF-8", "Windows-1251",$dataRow['regular']),
        iconv("UTF-8", "Windows-1251",$dataRow['period']),
        iconv("UTF-8", "Windows-1251",number_format($dataRow['plan'], 2, '.', ' ')),
        iconv("UTF-8", "Windows-1251",number_format($dataRow['fact'], 2, '.', ' ')),
        
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['periodStart']))),        
        
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSupply']))),        
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastOplate']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSchet']))),        
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSdelka']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastZakaz']))),        
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastContact']))),
                
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastActiveZakaz']))),
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastActiveSchet']))),
        
        iconv("UTF-8", "Windows-1251",$glist),
        
        iconv("UTF-8", "Windows-1251",$dataRow['balance']),
        
        iconv("UTF-8", "Windows-1251",$dataRow['contactPhone']),    
        iconv("UTF-8", "Windows-1251",$dataRow['contactEmail']),    		
        
        iconv("UTF-8", "Windows-1251",$dataRow['city']),    
        iconv("UTF-8", "Windows-1251",$dataRow['district']),    		
        iconv("UTF-8", "Windows-1251",$dataRow['adress']),    		
       
        iconv("UTF-8", "Windows-1251",$dataRow['categoryTitle']), 
 
        );
     
  return $list;       
       
   }

public function getOrgPhoneData ($params)
{
   
   $query  = new Query();
                  
     $query->select([
        '{{%tmp_reestr}}.refOrg',
        '{{%orglist}}.title as orgTitle',
        'phone'        
	 ]) ->from("{{%tmp_reestr}}")
        ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")
        ->leftJoin("{{%phones}}", "{{%phones}}.ref_org = {{%tmp_reestr}}.refOrg")
        ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
        ->distinct()
        ;
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', 'managerFIO', $this->managerFIO]); 
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
   
        if (!empty($this->catTitle))
            $query->andFilterWhere(['like', 'categoryTitle', $this->catTitle]);
                
        /*"2" => "Регулярные",				*/
        if ($this->regular == 2 ) 
        {
           $query->andFilterWhere(['>', 'regular', 2]);
        }
        /*"2" => "Разовые",				*/
        if ($this->regular == 3 ) 
        {
           $query->andFilterWhere(['<', 'regular', 3]);
        }
        
        
        /*"2" => "Не исполнен",				*/
        if ($this->execution == 2 ) 
        {
           $query->andFilterWhere(['<', '[[fact]]-[[plan]]', 0]);
        }
        /*"2" => "Выполнен",				*/
        if ($this->execution == 3 ) 
        {
           $query->andFilterWhere(['>=', '[[fact]]-[[plan]]', 0]);
        }
        
        
        /*Активные контакты*/
        if ($this->lastContact == 2 ) 
        {
           $query->andWhere('[[lastContact]] >= [[periodStart]]');
        }
        
        /*Активные Заявки*/
        if ($this->lastContact == 3 ) 
        {            
           $query->andWhere('[[lastActiveZakaz]] >= [[periodStart]]');
        }
        /*Активные отгрузки*/
        if ($this->lastSupply == 2 ) 
        {
           $query->andWhere('[[lastSupply]] >= [[periodStart]]');
        }
        
        /*Активные оплаты*/
        if ($this->lastSupply == 3 ) 
        {
           $query->andWhere('[[lastOplate]] >= [[periodStart]]');
        }

        /*Активные счета*/
        if ($this->lastSupply == 4 ) 
        {
           $query->andWhere('[[lastActiveSchet]] >= [[periodStart]]');
        }
        
        
     }
     
    $dataList= $query->createCommand()->queryAll();  
    
    $mask = realpath(dirname(__FILE__))."/../uploads/headClientPhonesReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headClientPhonesReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;

    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Телефон"),
		
        );
        fputcsv($fp, $col_title, ";"); 
        for ($i=0; $i< count($dataList); $i++)
        {    
            $list = array 
            (
                iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']),
                iconv("UTF-8", "Windows-1251",$dataList[$i]['phone']),
            );            
            fputcsv($fp, $list, ";");  
        }
        
        fclose($fp);
        return $fname;           
}
   
/************************************************************/   
public function orgSetCategory($orgRef, $cat){

   $ret = [
   'res' => false,
   'orgRef' => $orgRef,
   'cat' => $cat,
   'val' => 0,
   ];

    $record = OrgList::FindOne($orgRef);
    if (empty($record)) return $ret;

    $tmpRecord = TmpOrgReestr::FindOne(['refOrg' => $orgRef]);
    if (empty($tmpRecord)) return $ret;


    if ($tmpRecord->category == $cat) $tmpRecord->category = 0;
                                else $tmpRecord->category = $cat;
    $tmpRecord -> save();

    $record->orgTypeRef = $tmpRecord->category;
    $record -> save();

    $ret['val'] = $tmpRecord->category;

    return $ret;
}

/*
ALTER TABLE `rik_tmp_reestr` MODIFY COLUMN `refOrg` BIGINT(20) NOT NULL PRIMARY KEY;
*/

public function printSavedClientReestr($provider, $model)
 {

$lastSync =	 Yii::$app->db->createCommand('SELECT MAX(syncTime) FROM {{%tmp_reestr}}')
     ->queryScalar(); 
     
 
if (strtotime($lastSync) < time()-8*60*60) {$style='color:Crimson;'; $text="Обновить";}
                                  else     {$style=''; $text="";}
 
$grid ="<div style='text-align:right;".$style."'> Актуален на: <b>". date("d.m.Y h:m", strtotime($lastSync)) ."
<a href='#' onclick='document.location.href=\"index.php?r=site/update-reestr-client\"'> 
 <span class='glyphicon glyphicon-refresh' aria-hidden='true'></span>&nbsp;".$text."</a></b></div>";
 
$curUrl =preg_replace("/&fltCategory=./","",Yii::$app->request->url);

//$grid .=$this->fltCategory;
 
$grid .="
<script>
idList=new Array();
function chngSelectAllGrid()
{

 for (i=0; i<idList.length; i++)
 {
   document.getElementById(idList[i]).checked = true; 
  }
}

function cfgFltCategory ()
{
    $('.modal-dialog').width(600);
    $('#catListForm').modal('show');    
}

function setSelectGrid ()
{
 var strRequest = 'market/set-remind-to-user&userId='+document.getElementById('manager').value+'&orgListId=';
 for (i=0; i<idList.length; i++)
 {
   if (document.getElementById(idList[i]).checked)  strRequest = strRequest +idList[i]+',';
  }
  openSwitchWin (strRequest);
}

function setFltCategory(fltCategory)
{
    if (fltCategory == ".$this->fltCategory.") fltCategory = 0;
    document.location.href = '".$curUrl."'+'&fltCategory='+fltCategory;

}

function showEditCategory(Id)
{

 showId = 'catTitleValShow_'+Id;
 editId = 'catTitleValEdit_'+Id;   
           
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
}

function closeEditCategory(Id)
{

 showId = 'catTitleValShow_'+Id;
 editId = 'catTitleValEdit_'+Id;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    
}


function setCategory(orgRef, cat)
{

  var URL = 'index.php?r=head/org-set-category&orgRef='+orgRef+'&cat='+cat;
  console.log(URL);
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
        success: function(res){
           refreshCat(res);
        },
        error: function(){
            alert('Error while write data!');
        }
    });

}

function refreshCat(res){

document.location.reload(true);
return;
  var colorList=[
                'LightGray',
                'Green',
                'Lime',
                'Orange',
                'Crimson',
                'Brown',
                ];

    idx = res.orgRef+'_'+res.cat+'_category';

    console.log(res);
    console.log(idx);
    console.log(colorList[res.cat]);
    if (res.val==0){
        document.getElementById(idx).style.background.color='LightGray';
        console.log(document.getElementById(idx).style.background.color);
    }

    if (res.val==1){
        document.getElementById(idx).style.background.color=colorList[res.cat];
        console.log(document.getElementById(idx).style.background.color);
    }


}

</script>	
<style>

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.grd_date_val
{
    padding: 2px;
    font-size: 11pt;
    width: 80px;
}

.grd_small_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 15px;
    height: 15px;
}


</style>
	";

    $listCategory =	 Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($listCategory, 'Не задана');                    
	$fltCategory =	 Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($fltCategory, 'Все');                    

    $color=[

                0 => 'LightGray',
                1 => 'Green',
                2 => 'Lime',
                3 => 'Orange',
                4 => 'Crimson',
                5 => 'Brown',
                ];

    $catFlt ="<div style='width:135px;white-space: nowrap'>";
    for ($i=1; $i<6; $i++)
     {


         if ($this->fltCategory == $i )
            $style='background-color:'.$color[$i];
         else
            $style='background-color:LightGray';
           $action = "setFltCategory(".$i.");";
           $catFlt.= \yii\helpers\Html::tag( 'div'," ",
                   [
                     'class'   => 'btn grd_small_btn',
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => $listCategory[$i],
                   ]);

           $catFlt.= "&nbsp;";
       }
       $catFlt.= "&nbsp;";
       $catFlt.= "&nbsp;";
           $action = "cfgFltCategory();";
           $catFlt.= \yii\helpers\Html::tag( 'span',"",
                   [
                     'class'   => 'glyphicon glyphicon-cog clickable',
                     'onclick' => $action,
                   ]);
       
    $catFlt .="</div>";                            

    $this->debug[] = $this->fltCategory;        
	$grid .=\yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		        
			[
                'attribute' => 'orgTitle',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\", \"childwin\")' >".$model['orgTitle']."</a>";
                },
            ],		
	
    
    	   [
	            'attribute' => 'managerFIO',
				'label'     => 'Менеджер/<br> Активность', 
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                    return  "<div style='font-size:14px;width:125px;' ><b>".$model['managerFIO']." ".$model['mainActivity']."</b><br>".$model['otherActivity']."</div>"; 
                }    
                
            ],
 
     	  [
	            'attribute' => 'regular',
				'label'     => 'Регулярность',                
                'encodeLabel' => false,
                'format' => 'raw',
                'filter'=>array(
				"1" => "Все",
				"2" => "Регулярный",				
				"3" => "Разовый",				
				),
                'value' => function ($model, $key, $index, $column)  {
                $ret = "Ср.&nbsp;чек:&nbsp;".number_format($model['avgCheck'],0,'.','&nbsp;');
                /*Меньше трех - не регулярный клиент*/                                                
                if ( $model['regular']< 3 ) return $ret."<br> Разовый: (".$model['regular'].")";                
                $ret.= "<br> Регулярный: (".$model['regular'].") <br> ";
                $ret.= "Период ".$model['period']." дней <br>";
                return $ret;
                }    
           ],
            

     	  [
	            'attribute' => 'execution',
				'label'     => 'Исполнение<br> за период ',                
                'encodeLabel' => false,
                'format' => 'raw',
                'filter'=>array(
				"1" => "Все",
				"2" => "Не исполнен",				
				"3" => "Исполнен",				
				),
                'value' => function ($model, $key, $index, $column)  {
                
                if ( $model['regular']< 3 ) return "Разовый";                
                $ret=  "План:&nbsp;".number_format($model['plan'],0,'.','&nbsp;')."<br>";
                $ret.= "Факт:&nbsp;".number_format($model['fact'],0,'.','&nbsp;');
                return $ret;
                }    
           ],

			[	
                'attribute' => 'lastContact',
				'label'     => 'Контакт/<br>Заказ',
                'filter'=>array(
				"1" => "Все",
				"2" => "Контакт",				
				"3" => "Заказ",				
				),

                
                'encodeLabel' => false,
                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {
                    
                    if ( empty($model['period']) ) $period = 14;
                                              else $period = $model['period'];
                     $period = $period*60*60*24;//в секунды
                     
                   if (empty($model['lastContact'])) $cont ="Нет конт.<br>";  
                   else 
                   {    
                    $contTime =strtotime($model['lastContact']);
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    if ($contTime > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $cont = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $contTime)."</div>";                                           
                   }
                   
                   if (empty($model['lastZakaz'])) $zak ="Нет заказов";                   
                   else { 
                    $bg = "";
                    $cl="color:DarkGray;"; 

                   $zakTime =  strtotime($model['lastZakaz']);
                   if (!empty($model['lastActiveZakaz']))
                   {
                       /*Есть активные заказы*/
                       $zakTime =  strtotime($model['lastActiveZakaz']);
                       if ($zakTime > time() -  $period) 
                        {
                            /*В периоде*/                    
                            $cl="color:White;"; 
                            $bg="background-color:DarkGreen;"; 
                        }                               
                   }
                    $zak = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $zakTime)."</div>";
                        }
                 return $cont.$zak;
                 
				}
            ],

            
			[	
                'attribute' => 'lastSupply',
				'label'     => 'Отгрузка/<br>оплата/<br>счет',
                'filter'=>array(
				"1" => "Все",
				"2" => "Отгрузка",				
				"3" => "Оплата",				
                "4" => "Счет",				
				),

                'encodeLabel' => false,
                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {
                     
                    if ( empty($model['period']) ) $period = 14;
                                              else $period = $model['period'];
                    $period = $period*60*60*24;//в секунды
   
                     
                   if (empty($model['lastSupply'])) $sup ="Нет отгрузки";                     
                   else
                   {
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    $supTime =  strtotime($model['lastSupply']);
                    if ($supTime  > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $sup = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $supTime)."</div>";                                                                
                   }
                   
                   
                   if (empty($model['lastOplate'])) $op ="Нет оплаты";  
                   else 
                   {    
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    $opTime =  strtotime($model['lastOplate']);
                    if ($opTime  > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $op = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $opTime)."</div>";                                                                               
                   }
                   
                   
                   if (empty ($model['lastSchet'])) $sch = "Нет счета";
                   else 
                   {    
                    $bg = "";
                    $cl="color:DarkGray;"; 

                   $schTime =  strtotime($model['lastSchet']);
                   if (!empty($model['lastActiveSchet']))
                   {
                       /*Есть активные заказы*/
                       $schTime =  strtotime($model['lastActiveSchet']);
                       if ($schTime > time() -  $period) 
                        {
                            /*В периоде*/                    
                            $cl="color:White;"; 
                            $bg="background-color:DarkGreen;"; 
                        }                               
                   }
                   $wt = "";
                   //if ($schTime <> strtotime($model['lastSdelka'])) $wt="font-weight:bold";                   
                   $sch = "<div class='grd_date_val' style='".$cl.$bg.$wt."'>".date('d.m.Y', $schTime)."</div>";

               /*
                        $sch = date('d.m.Y', strtotime($model['lastSchet']));                 
                        if ($model['lastSchet'] <> $model['lastSdelka'])
                        $sch = "<font color='Green'>".$sch."</font>";*/
                   }
                 return $sup."".$op."".$sch."";
                 
				}
            ],
           
			[
	            'attribute' => 'fltGood',
				'label'     => 'Товары',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
                $strSql .= "where good not like 'Возмещение%' AND {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
                $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";
                                  
				$resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){
                    $ret.= mb_substr($resList[$i]['good'],0,35,'utf-8');
                    if(mb_strlen($resList[$i]['good'],'utf-8') > 35)$ret.="...";
                    $ret.="<br>\n";}
                return "<div style='font-size:12px;width:250px;' >".$ret."</div>";
				}
            ],

			[
                'attribute' => 'category',
				'label'     => 'Категория',
				'encodeLabel' => false,
				'format' => 'raw',
				'filter' =>$catFlt,
				
				'contentOptions' => ['width' => '150px'],                
				'value' => function ($model, $key, $index, $column) use($color)  {
                   
                // return $model['categoryTitle'];

                $val ="";
                for ($i=1; $i<6; $i++)
                {

                     if ($model['category'] == $i )
                        $style='background-color:'.$color[$i];
                    else
                        $style='background-color:'.$color[0];


                   $id = $model['refOrg']."_".$i."_category";
                   $action = "setCategory(".$model['refOrg'].",".$i.");";
                   $val.= \yii\helpers\Html::tag( 'div'," ",
                   [
                     'class'   => 'btn grd_small_btn',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                   ]);

                   $val.= "&nbsp;";
                }

                return $val;
				}
            ],

			
        ],
    ]
); 


/*$grid.= "<div class='row'>";
$grid.= "<div class='col-md-6'></div>";
$grid.= "<div class='col-md-4'>
<select id='manager'>".$this->getManagerOptions()."</select>
<input type='button' class='btn btn-primary grd_menu_btn' onClick='setSelectGrid()' value='Назначить'>
</div>";
$grid.= "<div class='col-md-2'><input type='button' class='btn btn-primary grd_menu_btn' onClick='chngSelectAllGrid()' value='Выбрать все'></div>";

$grid.= "</div>";*/

return $grid;

}
   
 

/****************************************************************************************/
   public function getClientReestrProvider($params)
   {

        $this->prepareClientReestrData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'userFIO',			
			'oplata', 
			'supply',
            'balance',
            'lastOplate',			
            'lastSupply', 
            'active',
            'orgTypeRef',
            'catTitle'
            ],
            'defaultOrder' => [    'title' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/************************************************************/   
   
public function printClientReestr($provider, $model)
 {
	

$grid ="
<script>
idList=new Array();
function chngSelectAllGrid()
{

 for (i=0; i<idList.length; i++)
 {
   document.getElementById(idList[i]).checked = true; 
  }
}

function setSelectGrid ()
{
 var strRequest = 'market/set-remind-to-user&userId='+document.getElementById('manager').value+'&orgListId=';
 for (i=0; i<idList.length; i++)
 {
   if (document.getElementById(idList[i]).checked)  strRequest = strRequest +idList[i]+',';
  }
  openSwitchWin (strRequest);
}


function showEditCategory(Id)
{

 showId = 'catTitleValShow_'+Id;
 editId = 'catTitleValEdit_'+Id;   
           
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
}

function closeEditCategory(Id)
{

 showId = 'catTitleValShow_'+Id;
 editId = 'catTitleValEdit_'+Id;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    
}


function setCategory(Id)
{
   editId = 'catTitleVal_'+Id;      
   value= document.getElementById(editId).options.selectedIndex;
   openSwitchWin('site/org-setcategory&id='+Id+'&count='+value);
}


</script>	
<style>

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

</style>
	";

    $listCategory =	 Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($listCategory, 'Не задана');                    
	$fltCategory =	 Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($fltCategory, 'Все');                    

    
    
	$grid .=\yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		        
			[
                'attribute' => 'title',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],		
	
        	[
	            'attribute' => 'userFIO',
				'label'     => 'Менеджер/<br> Активность', 
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                    

                //$rangeDate = 30;    
                //(TO_DAYS(NOW()) - TO_DAYS(contactDate) <=  :rangeDate)
				$val = "";
                $valOther = "";
				
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND 
                (contactDate) <= :rangeDate AND  {{%contact}}.ref_org = :ref_org group by userFIO
                ORDER BY cur DESC;
                ", 
				[
				':rangeDate' =>$model['lastOplate'],
				':ref_org' => $model['id'],
				])->queryAll();
                if (count($resList) == 0) return "<nobr><b>".$model['userFIO'].": 0</b></nobr>";
                
                for ($i=0; $i < count ($resList); $i++)
                {
                if ($resList[$i]['userFIO'] == $model['userFIO'])    
                    $val .= "<nobr><b>".$resList[$i]['userFIO'].": ". $resList[$i]['cur']."</b></nobr><br>";
                else
                    $valOther .= "<nobr>".$resList[$i]['userFIO'].": ". $resList[$i]['cur']."</nobr><br>";
                }
                if (empty($val)) $val = "<nobr><b>".$model['userFIO'].": 0</b></nobr><br>";
                return $val.$valOther;
                   }    
                
            ],
 
			[
	            'attribute' => 'balance',
				'label'     => 'Регулярность',                
                'encodeLabel' => false,
                'format' => 'raw',
                'filter'=>array(
				"1" => "Все",
				"2" => "Регулярный",				
				"3" => "Разовый",				
				),
                
				'value' => function ($model, $key, $index, $column) {
//             $ret =	"<a href=# onclick='openWin(\"site/org-deal-reestr&orgId=".$model['id']."\", \"childwin\")' >".number_format($model['balance'], 2, '.', '&nbsp;')."</a>";
                /*Средний чек*/            
                $avgCheck = Yii::$app->db->createCommand("SELECT AVG(summSupply) as av_schet from {{%schet}} where refOrg = :ref_org", 
                [':ref_org' => $model['id'],])->queryScalar();
                 $ret = "Ср.&nbsp;чек:&nbsp;".number_format($avgCheck,0,'.','&nbsp;');

                 /*Получим список оплат*/
                $listOplat = Yii::$app->db->createCommand("SELECT oplateDate, oplateSumm  from {{%oplata}} 
                where refOrg = :ref_org ORDER BY oplateDate ASC", 
                [':ref_org' => $model['id'],])->queryAll();
    
                /*Меньше трех - не регулярный клиент*/
                $cntOpl = count ($listOplat);
                if ( $cntOpl< 3 ) return $ret."<br> Разовый: (".$cntOpl.")";
                
                /*Ищем первую и последнюю даты*/
                $start=0;
                $startD=0;
                $end=0;
                for ($i=0; $i<$cntOpl;$i++)
                { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $start=$i;    
                    $startD = $listOplat[$i]['oplateDate'];
                    break;
                }
                
                for ($i=$cntOpl-1; $i>$start;$i--)
                { 
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    $end=$i;    
                    $endD = $listOplat[$i]['oplateDate'];
                    break;
                }
                
                if ($startD == 0 || $endD == 0)  return $ret."<br> Данные не валидны: (".$cntOpl.")";
                if ($end - $start < 2)return $ret."<br> Мало данных: (".$cntOpl.")";
                                
                $ret.= "<br> Регулярный: (".($end - $start).") <br> ";
                
                //$ret.= $start."-".$end."<br>";
                
                //Средний период между оплатами
                $avDT =  (strtotime($endD) - strtotime($startD))/($end - $start);
                $trash = 60*60*24*90;
                if ($avDT < $trash) $avDT = $trash;
                 //= intval($dT / (60*60*24));
                //$ret.="Среднее ".($avDT/(60*60*24))."<br";
                /*Учитываем, что часть данных была уже выкинута
                сканируем на наличие черезчур длинных промежутков
                */
                $fD = strtotime($startD); 
                $validN=-1;
                $validT=0;
                $sumOp = 0;
                for ($i=$start; $i<=$end;$i++)
                { 
                    $sumOp+= $listOplat[$i]['oplateSumm'];   
                    if (empty($listOplat[$i]['oplateDate']))continue;                    
                    
                   $cur =  strtotime($listOplat[$i]['oplateDate']);
                    $period = $cur - $fD;
                   $fD = $cur;
                                         
                    if ($period <= $avDT) { /*continue; // Не валидный период*/
                    $validT += $period;
                    $validN ++;           }

                  //  $ret.=  $i." ".($period/(60*60*24))." ".$validN."<br>";
                }
                //Валидный промежуток
                //$ret.= "Промежутков:".$validN."<br>";
                if ($validN > 0) {

                $dT = $validT/$validN;
                // В днях
                $dT = intval($dT/(60*60*24));
                
                if ($dT <= 0) return $ret."<br> Данные не валидны: (".$cntOpl.")";
                $ret.= "Период ".$dT." дней <br>План на период: ".number_format($sumOp/$validN,2,'.','&nbsp;')."<br>";

                /*Сумма за период*/            
                $sumPer = Yii::$app->db->createCommand("SELECT SUM(oplateSumm)  from {{%oplata}} 
                where refOrg = :ref_org AND (TO_DAYS(NOW()) - TO_DAYS(oplateDate)) < :period",
                [':ref_org' => $model['id'],
                 ':period'  => $dT
                ])->queryScalar();
                 $ret.= "Факт:".number_format($sumPer,2,'.','&nbsp;');
                 
                }
                 return $ret;
				}
            ],

            [	
                'attribute' => 'active',
				'label'     => 'Периодич./<br>Дн. до опл./<br>Сделок',
                'encodeLabel' => false,
                'filter'=>array(
				"1" => "Нет сделок",
				"2" => "Есть сделки",				
				),

                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {
                 
                 if ($model['active'] ==0) $activity = "<font color='Brown'>Нет сделок</font>";
                                      else $activity = "<nobr><font color='DarkGreen'>В раб.: ".$model['active']."</font></nobr>";
                 
                 $strSql  = 'SELECT count(id) as N, to_days(max(schetDate)) - to_days(min(schetDate)) AS l, max(schetDate) as max_d, min(schetDate) as min_d ';
                 $strSql .= 'from {{%schet}} where summSupply > 0 AND  refOrg = :ref_org';  
                 $periodData = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryOne();
                 
                 $ret= " ";
                 $op =" ";
                 if ($periodData['N'] > 0) 
                 {
                    $ret = intval($periodData['l']/$periodData['N']);

                    $strSql  = "SELECT schetDate, MIN(oplateDate), to_days(MIN(oplateDate)) - to_days(schetDate) AS l from {{%oplata}} ";
                    $strSql .= "where  schetDate is not NULL and   schetDate >'1980-01-01'  and  oplateDate is not NULL and   schetDate >'1980-01-01' ";
                    $strSql .= "and  {{%oplata}}.refOrg = :ref_org  group by schetNum";
                    $oplateData = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryAll();
                 
                    $N =count($oplateData); 
                    $op=" ";
                    if ($N > 0){
                        $sper=0;
                        for($i=0; $i < $N; $i++)$sper+=$oplateData[$i]['l'];
                        $op="<br>".intval($sper/$N);
                    }
                 
                 }
                  return $ret.$op."<br>".$activity;
				}
            ],


            [	
                'attribute' => 'contactToSchet',
				'label'     => 'Контактов <br> на сделку ',
                'encodeLabel' => false,
                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {

                 /*Последний контакт*/
                 $strSql  = 'SELECT date(MAX(ContactDate)) FROM {{%contact}} where ref_org = :ref_org';
                 $lastСontactData = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryScalar();
                  
                 
                 /*Сред число = число контактов/ число счетов с поставками*/
                 $strSql  = 'SELECT count(id) as N, date(MIN(ContactDate)) as minD from {{%contact}} where ref_org = :ref_org';
                 $contactData = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryOne();
                 
                 $strSql ='SELECT count(id) from {{%schet}} where summSupply > 0 and schetDate > :minD and refOrg = :ref_org';
                 $schetNum = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'], ':minD' => $contactData['minD'] ])->queryScalar();
                 
                 if ($schetNum == 0) $avVal = "N/A";
                                else $avVal = intval($contactData['N']/$schetNum);
                 
                 $lastTime = strtotime($lastСontactData);
                 
                 if ($lastTime == 0 ) return $avVal."<br> Нет контактов";
                 if (time()-$lastTime > 60*60*24*30 ) return $avVal."<br> <font color='Brown'>".date("d.m.Y", $lastTime)."</font>";
                 return $avVal."<br>".date("d.m.Y", $lastTime);
				}
            ],



			
			/*[	
                'attribute' => 'lastOplate',
				'label'     => 'Дата оплаты',
                'format' => ['datetime', 'php:d.m.Y'],
            ],*/

			[	
                'attribute' => 'lastSupply',
				'label'     => 'Отгрузка/<br>оплата/<br>счет',
                'encodeLabel' => false,
                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {
                   if (empty($model['lastSupply'])) $sup ="Нет отгрузки";  
                   else $sup = date('d.m.Y', strtotime($model['lastSupply']));
                   if (empty($model['lastOplate'])) $op ="Нет оплаты";  
                   else $op = date('d.m.Y', strtotime($model['lastOplate']));

                 $strSql  = 'SELECT MAX(schetDate) FROM {{%schet}} where refOrg = :ref_org';
                 $lastSchetData = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryScalar();
                 if (empty ($lastSchetData)) $sch = "Нет счета";
                 else $sch = date('d.m.Y', strtotime($lastSchetData));                 
                 return $sup."<br>".$op."<br>".$sch;
                                   
				}
            ],


            
            
			[
	            'attribute' => 'fltGood',
				'label'     => 'Товары',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
                $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
                $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";
                                  
				$resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryAll();
                /*echo "<pre>";
                print_r($resList);
                echo "</pre>";*/
				$ret="";
				for($i=0;$i<count($resList);$i++)
                {
                    $ret.= $resList[$i]['good']."<br>\n";
                }
                        
                 return $ret;
				}
            ],

			[
                'attribute' => 'catTitle',
				'label'     => 'Категория',
				'format' => 'raw',
                'filter' => $fltCategory,
				'value' => function ($model, $key, $index, $column) use($listCategory)  {
                    
                   $name="catTitleVal_".$model['id'];  
                   
                   $dropDown = Html::dropDownList(
                        $name, 
                        $model['orgTypeRef'], 
                        $listCategory,
                        ['id' => $name]
                   );     
                   
                   $ret="<div id='catTitleValEdit_".$model['id']."' class='editcell' style='width:150px;'>
                    <nobr>".$dropDown."                  
                    <a href ='#' onclick=\"javascript:setCategory('".$model['id']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>
                    <a href ='#' onclick=\"javascript:closeEditCategory('".$model['id']."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>
                    </nobr></div>";
                    
                    $ret.="<div id='catTitleValShow_".$model['id']."' class='gridcell' onclick=\"javascript:showEditCategory('".$model['id']."');\">".$model['catTitle']."&nbsp;</div>";                                   
                    return $ret;					
				}
            ],
            
			[
	
                'attribute' => 'Выбрать',
				'label'     => '*',
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                
                    $id = $model['id'];
                    $script="<script>idList.push('".$id."');</script>";	
                 return	"<input type=checkbox id='".$id."'>".$script;
					
				}
				
            ],

			
        ],
    ]
); 


$grid.= "<div class='row'>";
$grid.= "<div class='col-md-6'></div>";
$grid.= "<div class='col-md-4'>
<select id='manager'>".$this->getManagerOptions()."</select>
<input type='button' class='btn btn-primary grd_menu_btn' onClick='setSelectGrid()' value='Назначить'>
</div>";
$grid.= "<div class='col-md-2'><input type='button' class='btn btn-primary grd_menu_btn' onClick='chngSelectAllGrid()' value='Выбрать все'></div>";
$grid.= "</div>";

return $grid;

}
/*********************************************/

 public function prepareComplexClientReestrData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов с которыми были финансовые взаимоотношения */
	                  
     $query->select([
	 '{{%orglist}}.id',
	 'title', 
	 'userFIO', 
	 'oplataCnt', 
	 'supplyCnt', 
	 'ifnull(oplataSum, 0) as oplata',
	 'ifnull(supplySum, 0) as supply',	 
	 '(ifnull(oplataSum, 0)-ifnull(supplySum, 0)) as balance', 
	'lastOplate', 
	'lastSupply',	 
    '{{%orglist}}.contactPhone',
    '{{%orglist}}.contactEmail',
	 ]) ->from("{{%orglist}}")
        ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
        ->leftJoin("(SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg from {{%oplata}} group by refOrg) as opl", "opl.refOrg = {{%orglist}}.id")
        ->leftJoin("(SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = {{%orglist}}.id ")
        ->leftJoin("(SELECT AVG(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = {{%orglist}}.id ")
        ;
            
     $query->where(" (ifnull(oplataSum,0)>0 OR ifnull(supplySum,0)>0) ");            
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
          
     }
     
     
       $this->command = $query->createCommand();    


       
       
       //$this->count = 

   }
   
   
   
   
   
   




/*******************************************/
 public function prepareLostListData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

    
    /* Потерянные - нет активной работы и назанченных контактов  */


    $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")
                 /* AND event_date <= '".date("Y-m-d", time())."'*/
                 ;
                  
     $query->select("{{%orglist}}.id, title, contactDate, nextContactDate, razdel, {{%user}}.userFIO, b.userFIO as operator, {{%orglist}}.contactPhone, {{%orglist}}.contactEmail  " )
        ->from("{{%orglist}}")
        ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
        ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")   
        ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
        ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
        ->leftJoin("(SELECT count(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")
        ;

             
      $countquery->where(" 	isFirstContact =1 AND  ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");            
      $query->where(" isFirstContact =1 AND ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");            
 

            
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'b.userFIO', $this->operator]); 
        $countquery->andFilterWhere(['like', 'b.userFIO', $this->operator]);

        
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);

        
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);
     
        $countquery->andFilterWhere(['like', 'razdel', $this->razdel]);
        $countquery->andFilterWhere(['=', 'razdel', $this->razdel]);
     
     
     }
     
     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

   }
   
     public function getLostListData ($params)
   {
        $this->prepareLostListData($params);

        $dataList=$this->command->queryAll();

    $mask = realpath(dirname(__FILE__))."/../uploads/headLostListReport*.csv";
    array_map("unlink", glob($mask));   
    $fname = "uploads/headLostListReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
            
        iconv("UTF-8", "Windows-1251","Менеджер"), 
        iconv("UTF-8", "Windows-1251","Оператор"), 

        iconv("UTF-8", "Windows-1251","Последний Контакт"),
        iconv("UTF-8", "Windows-1251","Последняя назначеная дата"),
        iconv("UTF-8", "Windows-1251","Раздел"), 
        
        iconv("UTF-8", "Windows-1251","Телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        

        );
        fputcsv($fp, $col_title, ";"); 
    
    for ($i=0; $i< count($dataList); $i++)
    {        
       
        $resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
		[':ref_org' => $dataList[$i]['id'],])->queryAll();
		$ret="";
		for($j=0;$j<count($resList);$j++){$ret= date("d.m.Y", strtotime($resList[$j]['contactDate']))." ".$resList[$j]['contactFIO']." ".$resList[$j]['note']."   ";}
 
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']), 
 
        iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),
        iconv("UTF-8", "Windows-1251",$dataList[$i]['operator']),    


                
        iconv("UTF-8", "Windows-1251",$ret), 
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['contactDate']))), 
    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['razdel']),    
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']),    		

        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        

   
   }
   
   
   public function getLostListProvider($params)
   {

        $this->prepareLostListData($params);
        
        
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            'nextContactDate',
            'userFIO',
            'operator',
            'activeSchet', 
            'activeZakaz',
            'activeEvent',
            'razdel'
            ],
            'defaultOrder' => [    'userFIO' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
 
 public function getManagerOptions()
 {
   $userList =  Yii::$app->db->createCommand('SELECT  id, userFIO from {{%user}} where roleFlg & (0x0080|0x0002|0x0004) ' )->queryAll();	
     
  $options="<option value='0'>  </option>";;
  for ($i=0;$i<count($userList);$i++)
  {
     $options.="<option value='".$userList[$i]['id']."'> ".$userList[$i]['userFIO']." </option>";
  }
	
  return $options;
 }
 
 public function printLostList($provider, $model)
 {
	

$grid ="
<script>
idList=new Array();
function chngSelectAllGrid()
{

 for (i=0; i<idList.length; i++)
 {
   document.getElementById(idList[i]).checked = true; 
  }
}

function setSelectGrid ()
{
 var strRequest = 'market/set-remind-to-user&userId='+document.getElementById('manager').value+'&orgListId=';
 for (i=0; i<idList.length; i++)
 {
   if (document.getElementById(idList[i]).checked)  strRequest = strRequest +idList[i]+',';
  }
  openSwitchWin (strRequest);
}
</script>	
<style>

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

</style>
	";
	
	$grid .=\yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		        
			[
                'attribute' => 'title',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],		
						        
			[
	            'attribute' => 'razdel',
				'label'     => 'Раздел',                
            ],

			[
	            'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
            ],
            
			[
	            'attribute' => 'operator',
				'label'     => 'Оператор',                
            ],
		        

            [
                'attribute' => 'contactDate',
				'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

			[
	
                'attribute' => 'nextContactDate',
				'label'     => 'Последняя назначеная дата',
                //'format' => ['datetime', 'php:d.m.Y'],
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {

                 return	date ('d.m.Y', strtotime($model['nextContactDate']));
					
				}
				
            ],

			[
	
                'attribute' => 'Выбрать',
				'label'     => 'Выбрать',
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                
                    $id = $model['id'];
                    $script="<script>idList.push('".$id."');</script>";	
                 return	"<input type=checkbox id='".$id."'>".$script;
					
				}
				
            ],

			
        ],
    ]
); 


$grid.= "<div class='row'>";
$grid.= "<div class='col-md-6'></div>";
$grid.= "<div class='col-md-4'>
<select id='manager'>".$this->getManagerOptions()."</select>
<input type='button' class='btn btn-primary grd_menu_btn' onClick='setSelectGrid()' value='Назначить'>
</div>";
$grid.= "<div class='col-md-2'><input type='button' class='btn btn-primary grd_menu_btn' onClick='chngSelectAllGrid()' value='Выбрать все'></div>";
$grid.= "</div>";

return $grid;

}

   
 /****************************************************/           
  
   
/***************************************************/

   public function prepareContactListData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

    
    /* Произвольные контакты  */

     $countquery->select ("count({{%orglist}}.id)")
                 ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")  
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")                                  
                 ;

     $query->select("{{%orglist}}.id, title, contactDate, nextContactDate, {{%user}}.userFIO, b.userFIO as operator, {{%calendar}}.id as activeEvent, {{%orglist}}.contactPhone, {{%orglist}}.contactEmail " )
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")                                  
                 ;

 if ($this-> detail == 11)
 {
    $countquery->where("eventStatus=1  AND ref_event <3 AND   {{%calendar}}.ref_org= {{%orglist}}.id");            
    $query->where("eventStatus=1  AND ref_event <3 AND  {{%calendar}}.ref_org= {{%orglist}}.id");                        
 }
 else
 {
     $countquery->where(" eventStatus=1  AND ref_event >7 AND   {{%calendar}}.ref_org= {{%orglist}}.id");            
     $query->where("eventStatus=1  AND ref_event >7 AND  {{%calendar}}.ref_org= {{%orglist}}.id");                        
 }

            
    if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'b.userFIO', $this->operator]); 
        $countquery->andFilterWhere(['like', 'b.userFIO', $this->operator]);

        
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);

        
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);
     
     }
     
     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

   }
   
   public function getContactListData ($params)
   {
        $this->prepareContactListData($params);

        $dataList=$this->command->queryAll();

    $mask = realpath(dirname(__FILE__))."/../uploads/headContactListRepor*.csv";
    array_map("unlink", glob($mask));                
    $fname = "uploads/headContactListReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Клиент"),
         
        iconv("UTF-8", "Windows-1251","Менеджер"), 
        iconv("UTF-8", "Windows-1251","Оператор"), 
       


        iconv("UTF-8", "Windows-1251","Последний Контакт"),
        iconv("UTF-8", "Windows-1251","Назначеная дата"),

        iconv("UTF-8", "Windows-1251","Телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        


        );
        fputcsv($fp, $col_title, ";"); 
    
    for ($i=0; $i< count($dataList); $i++)
    {        
       
        $resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
		[':ref_org' => $dataList[$i]['id'],])->queryAll();
		$ret="";
		for($j=0;$j<count($resList);$j++){$ret= date("d.m.Y", strtotime($resList[$j]['contactDate']))." ".$resList[$j]['contactFIO']." ".$resList[$j]['note']."   ";}
 
    $list = array 
            (
          iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),
          
          iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),
          iconv("UTF-8", "Windows-1251",$dataList[$i]['operator']),    
                
          iconv("UTF-8", "Windows-1251",$ret), 
          iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['contactDate']))), 
                  
          iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']),    
          iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']),    		

          
            );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        

   
   }
   
   
   public function getContactListProvider($params)
   {

        $this->prepareContactListData($params);
        
        
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            'nextContactDate',
            'userFIO',
            'operator',
            'activeSchet', 
            'activeZakaz',
            'activeEvent'
            ],
            'defaultOrder' => [    'userFIO' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

public function printContactEventList($provider, $model)
 {
	return \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		        
			[
                'attribute' => 'title',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],	
            
			[
	            'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
            ],

			[
	            'attribute' => 'operator',
				'label'     => 'Оператор',                
            ],
		        
	
				
            [
                'attribute' => 'contactDate',
				'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

			[
	
                'attribute' => 'nextContactDate',
				'label'     => 'Назначеная дата',
                //'format' => ['datetime', 'php:d.m.Y'],
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {

                 return	date ('d.m.Y', strtotime($model['nextContactDate']));
					
				}
				
            ],


/*
			[
                'attribute' => 'Далее',
				'label' => 'Далее',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				$commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
				return "<input ".$commStr." value='Контакт'  onclick=\"javascript:openWin('site/reg-contact&singleWin=1&id=".$model['id']."');\" />";
                },
            ],		

			
			[
                'attribute' => 'Сдвинуть',
				'label' => 'Запланировать через:',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				$val = "<nobr>";
				$val .="<input class='btn btn-primary local_btn' style='margin-right:10px; background:ForestGreen' type=button value=' Ok ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=0&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn'  type=button value=' +1 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=1&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn'  type=button value=' +7 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=7&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn' style='margin-left:10px; background:Maroon' type=button value=' +30 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=30&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .= "</nobr>";
				return  $val;
                },
            ],		
*/

			
        ],
    ]
); 
}

   
 /****************************************************/           
/*********getDetail********/
public function getDetailPrepare($params)
   {


    $countquery  = new Query();
    $query       = new Query();
            
    $countquery->select (" count({{%calendar}}.id)")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%calendar}}.ref_user')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%phones}}','{{%phones}}.id = {{%contact}}.ref_phone')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%contact}}.ref_org');
    

        
    
    $query->select (" {{%calendar}}.id as id, event_date, eventNote, {{%calendar}}.ref_event, eventStatus, {{%event}}.eventTitle, {{%contact}}.contactFIO, {{%contact}}.contactDate, {{%contact}}.note, {{%phones}}.phone,
    {{%orglist}}.title, {{%orglist}}.id as orgId, {{%calendar}}.ref_zakaz as zakazId, userFIO ")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%calendar}}.ref_user')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%phones}}','{{%phones}}.id = {{%contact}}.ref_phone')    
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%calendar}}.ref_org'); 

            
    if ($this->refManager != 0)
    {
        $countquery->andWhere("({{%calendar}}.ref_user=".$this->refManager." OR {{%orglist}}.refManager =".        $this->refManager.")"  );    
        
        $query->andWhere("({{%calendar}}.ref_user=".$this->refManager." OR {{%orglist}}.refManager =".        $this->refManager.")"  );    
          
    }


    $query->andFilterWhere(['>', 'ref_event', 7]);
    $countquery->andFilterWhere(['>', 'ref_event', 7]);
      

    if (($this->load($params) && $this->validate())) 
    {
        /* Фильтр есть */
     $query->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
     $countquery->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
     
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);
         
     if ($this->refEvent!="") { 
     $query->andFilterWhere(['like', 'eventNote', $refEventArray[$this->refEvent] ]);
     $countquery->andFilterWhere(['like', 'eventNote',$refEventArray[$this->refEvent] ]);
     }  
          
    } 
    
    
    $count = $countquery->createCommand()->queryScalar();
    $command = $query->createCommand();    
        
        
    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();

   }   


  public function getDetailProvider($params)
   {
   
    $this -> getDetailPrepare($params);
    $provider = new SqlDataProvider([
            'sql' =>     $this->command->sql,
            'params' => $this->command->params,            
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'event_date',
            'eventTitle',
            'eventStatus',
            'contactDate',
            'contactFIO',    
            'title',            
            'eventNote',
            'defaultOrder' => [    'event_date' => SORT_DESC ],
            ],
            ],
        ]);
    return $provider;
   }  
   
  public function   printContactDetailList($provider, $model) 
  {
  return \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                    
            [
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],        
                
            [
                'attribute' => 'title',
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],        
                
            [
                'attribute' => 'contactDate',
                'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
                [':ref_org' => $model['id'],])->queryAll();
                $ret="";
                for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],        

            [
    
                'attribute' => 'nextContactDate',
                'label'     => 'Назначеная дата',
                //'format' => ['datetime', 'php:d.m.Y'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                 if(strtotime($model['nextContactDate']) < time()-8*60*60*24) return "";
                 return    date ('d.m.Y', strtotime($model['nextContactDate']));
                    
                }
                
            ],


        ],
    ]
);    
     
     
     }

 
/************************************/   
/*********  Supply-Oplata ***************/   
/************************************/

 public function prepareDocReestrData($params, $refOrg)
   {
    
     $query  = new Query();
	 $querySub1  = new Query();
	 $querySub2  = new Query();
     
     $querySupplier  = new Query();
	 $querySupplierSub1  = new Query();
	 $querySupplierSub2  = new Query();

                  
     $query->select([ 
     '{{%zakaz}}.id as zakazNum',
     '{{%zakaz}}.formDate as zakazDate',
     '{{%zakaz}}.isActive as zakazIsActive',
     '{{%zakaz}}.isFormed as zakazIsFormed',
     '{{%schet}}.schetNum', 
     '{{%schet}}.schetDate', 
     '{{%schet}}.schetSumm',  
     '{{%schet}}.refOrg', 
     '{{%schet}}.ref1C',
     '{{%schet}}.isReject',
     'credit', 'creditDate', 
     'debet', 'debetDate', 
     '{{%schet}}.id as refSchet' ]) 
        ->from("{{%zakaz}}")
		->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakaz}}.id")
        ->leftJoin('(select ifnull(sum(oplateSumm),0) as credit, max(oplateDate) as creditDate,refSchet from  {{%oplata}} group by refSchet) as a', 'a.refSchet= {{%schet}}.id')
        ->leftJoin('(select ifnull(sum(supplySumm),0) as debet, max(supplyDate) as debetDate,refSchet from  {{%supply}} group by refSchet) as b', 'b.refSchet= {{%schet}}.id');
	$query->andWhere(['=', '{{%schet}}.refOrg', $refOrg]);  
	
	
    $querySub1->select([ 'schetNum', 'schetDate', 'refOrg', 'sum(oplateSumm) as credit', 'oplateDate  as creditDate', 
     'refSchet' ]) 
	->from("{{%oplata}}")
	 ->groupBy(['schetNum', 'schetDate', 'refOrg',  'oplateDate', 'refSchet']);	
	 
	$querySub1->andWhere(['=', 'refOrg', $refOrg]);  
	$querySub1->andWhere(['=', 'refSchet', 0]);  
	
	
	
	$querySub2->select([ 'schetNum', 'schetDate', 'refOrg', 'sum(supplySumm) as debet', 'supplyDate  as debetDate', 'refSchet' ]) 
		->from("{{%supply}}")  
		->groupBy(['schetNum', 'schetDate', 'refOrg', 'supplyDate', 'refSchet']);
	$querySub2->andWhere(['=', 'refOrg', $refOrg]);  
	$querySub2->andWhere(['=', 'refSchet', 0]);  


     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $query->andFilterWhere(['like', 'title', $this->title]);
     }

	$this->dataArray = $query->createCommand()->queryAll();  
  	for ($i=0; $i< count($this->dataArray); $i++)
	{
        $this->dataArray[$i]['operation']="Продажа";
    }
    
	$list=$querySub1->createCommand()->queryAll();  
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Продажа";
		$list[$i]['supplyDate']="";
		$list[$i]['debet']=0;
		$this->dataArray[]=$list[$i];
	}	

	$list=$querySub2->createCommand()->queryAll();  	
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Продажа";
		$list[$i]['oplateDate']="";
		$list[$i]['credit']=0;
		$this->dataArray[]=$list[$i];
	}	


     $querySupplier->select([ 
     '{{%supplier_schet_header}}.schetNum', 
     '{{%supplier_schet_header}}.schetDate', 
     'sum({{%supplier_schet_content}}.goodSumm) as schetSumm',  
     '{{%supplier_schet_header}}.refOrg', 
     'sum(oplateSumm) as debet', 
     'oplateDate  as debetDate', 
     'sum(wareSumm) as credit', 
     'requestDate  as creditDate', 
     '{{%supplier_schet_header}}.id as refSchet' 
     ]) 
	->from("{{%supplier_oplata}}, {{%supplier_wares}}, {{%supplier_schet_header}}, {{%supplier_schet_content}}")
        ->where("{{%supplier_oplata}}.supplierSchetRef= {{%supplier_schet_header}}.id 
                   AND {{%supplier_wares}}.supplierSchetRef= {{%supplier_schet_header}}.id
                   AND {{%supplier_schet_content}}.schetRef= {{%supplier_schet_header}}.id
                   ")
		->groupBy(['{{%supplier_schet_header}}.schetNum', '{{%supplier_schet_header}}.schetDate', '{{%supplier_schet_header}}.refOrg', 
        'oplateDate', 'requestDate', '{{%supplier_schet_header}}.id']);
	$querySupplier->andWhere(['=', '{{%supplier_schet_header}}.refOrg', $refOrg]);  
	
   	$list=$querySupplier->createCommand()->queryAll();  	
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Закупка";
		$this->dataArray[]=$list[$i];
	}	


    $querySupplierSub1->select([ 'sdelkaNum as schetNum', 'sdelkaDate as schetDate', 'refOrg', 'sum(oplateSumm) as debet', 'oplateDate  as debetDate', 
     'supplierSchetRef as refSchet' ]) 
	->from("{{%supplier_oplata}}")
	 ->groupBy(['schetNum', 'schetDate', 'refOrg',  'oplateDate', 'supplierSchetRef']);	
	 
	$querySupplierSub1->andWhere(['=', 'refOrg', $refOrg]);  
	$querySupplierSub1->andWhere(['=', 'supplierSchetRef', 0]);  
	
	
	$querySupplierSub2->select([ 'requestNum as schetNum', 'requestDate as schetDate', 'refOrg', 'sum(wareSumm) as credit', 'requestDate  as creditDate', 'supplierSchetRef as refSchet' ]) 
		->from("{{%supplier_wares}}")  
		->groupBy(['requestNum', 'requestDate', 'refOrg', 'requestDate', 'supplierSchetRef']);
	$querySupplierSub2->andWhere(['=', 'refOrg', $refOrg]);  
	$querySupplierSub2->andWhere(['=', 'supplierSchetRef', 0]);  


    $list=$querySupplierSub1->createCommand()->queryAll();  
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Закупка";
		$list[$i]['supplyDate']="";
		$list[$i]['credit']=0;
		$this->dataArray[]=$list[$i];
	}	

	$list=$querySupplierSub2->createCommand()->queryAll();  	
	for ($i=0; $i< count($list); $i++)
	{
        $list[$i]['operation']="Закупка";
		$list[$i]['oplateDate']="";
		$list[$i]['debet']=0;
		$this->dataArray[]=$list[$i];
	}	

	
//    $this->debug[] = $this->dataArray;
	
  }
/************/   
   public function getDocReestrData ($params, $orgId)		
   {
        $this->prepareDocReestrData($params, $orgId);		

        $dataList=$this->dataArray;

    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));     
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Менеджер"), 

        iconv("UTF-8", "Windows-1251","Оплаты"),
        iconv("UTF-8", "Windows-1251","Отгрузки"),
        iconv("UTF-8", "Windows-1251","Сверка"), 

        iconv("UTF-8", "Windows-1251","Дата оплаты"),
        iconv("UTF-8", "Windows-1251","Дата отгрузки"), 
		
        );
        fputcsv($fp, $col_title, ";"); 
    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),
		
        iconv("UTF-8", "Windows-1251",$dataList[$i]['oplata']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['supply']),    
        iconv("UTF-8", "Windows-1251",$dataList[$i]['balance']),    		

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastOplate']))), 
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['lastSupply']))), 

        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
   public function getDocReestrProvider($params, $orgId)		
   {

        $this->prepareDocReestrData($params, $orgId);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'zakazNum',
            'zakazDate',
            'zakazIsActive',
            'zakazIsFormed',
			'schetNum', 
			'schetDate', 
			'refOrg', 
			'credit', 
			'creditDate', 
			'debet',
			'debetDate', 
			'refSchet'
            ],
			
            'defaultOrder' => [    'schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/
   

/****************************************/   
/*********  Statistics ******************/   
/****************************************/

 public function prepareStatYearData($params)
   {
    
     $query  = new Query();
                                    
     $query->select([ 'SUM(supplySumm) as S', 'refOrg', 'title', '{{%orglist}}.contactPhone', '{{%orglist}}.contactEmail', '{{%orglist}}.contactFIO', ]) 
		->from("{{%supply}}, {{%orglist}}")
        ->where("{{%supply}}.refOrg = {{%orglist}}.id and TIMESTAMPDIFF(MONTH, supplyDate, NOW() ) < 11")
		->groupBy(['refOrg', 'title' ]);
	 //$query->andWhere(['=', '{{%schet}}.refOrg', $refOrg]);  

    if (($this->load($params) && $this->validate())) {     
     
        $query->andFilterWhere(['like', 'title', $this->title]);
    }

    $idxArray= array();
    //Занулим помесячно    
	$this->dataArray = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($this->dataArray); $i++)
    {
      $idxArray[$this->dataArray[$i]['refOrg']] = $i;  
      for ($j=1; $j<=12; $j++)  $this->dataArray[$i]['m_'.$j]=0;
    }

    $strSql= "SELECT SUM(supplySumm) as S, year(supplyDate) as Y, month(supplyDate) as M, refOrg from {{%supply}}
    where TIMESTAMPDIFF(MONTH, supplyDate, NOW() ) < 11 group by month(supplyDate), refOrg, year(supplyDate)";
	
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll(); 
	for ($i=0; $i< count($list); $i++)
	{
        $m = $list[$i]['M'];
        $s = $list[$i]['S'];
        if (array_key_exists($list[$i]['refOrg'],$idxArray ))
        {
            $idx = $idxArray[$list[$i]['refOrg']];
            if ($this->format == 'csv') $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','');
                                   else $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','&nbsp;');
            
        }
	}	

	
  }

  /************/      
  public function getStatYearData ($params)		
   {
        $this->prepareStatYearData($params);		
        $dataList=$this->dataArray;

  $monthTitles = array(
	"1" => "январь",
	"2" => "февраль",
	"3" => "март",
	"4" => "апрель",
	"5" => "май",
	"6" => "июнь",
	"7" => "июль",
	"8" => "август",
	"9" => "сентябрь",
	"10" => "октябрь",
	"11" => "ноябрь",
	"12" => "декабрь"); 

/*Предыдущий год*/    
$cur_m = date('n');
$cur_y = date('Y');
$j=1;
for($i=$cur_m+1;$i<=12;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y-1);
$j++;
}

/*Этот год*/        
for($i=1;$i<=$cur_m;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y);
$j++;
}
 
    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));     
    $fname = "uploads/headStatYearReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Телефон"),
        iconv("UTF-8", "Windows-1251","E-Mail"),
        iconv("UTF-8", "Windows-1251","Контакт ФИО"),        
        
        iconv("UTF-8", "Windows-1251","Всего"), 

        iconv("UTF-8", "Windows-1251",$lbl[1]),
        iconv("UTF-8", "Windows-1251",$lbl[2]),
        iconv("UTF-8", "Windows-1251",$lbl[3]),
        iconv("UTF-8", "Windows-1251",$lbl[4]),
        iconv("UTF-8", "Windows-1251",$lbl[5]),
        iconv("UTF-8", "Windows-1251",$lbl[6]),
        iconv("UTF-8", "Windows-1251",$lbl[7]),
        iconv("UTF-8", "Windows-1251",$lbl[8]),
        iconv("UTF-8", "Windows-1251",$lbl[9]),
        iconv("UTF-8", "Windows-1251",$lbl[10]),
        iconv("UTF-8", "Windows-1251",$lbl[11]),
        iconv("UTF-8", "Windows-1251",$lbl[12]),
        );
        fputcsv($fp, $col_title, ";"); 
    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactFIO']),  
        
        iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['S'],0,'.','')),
        
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[1]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[2]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[3]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[4]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[5]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[6]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[7]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[8]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[9]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[10]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[11]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[12]]),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
/************/      
   public function getStatYearProvider($params)		
   {

        $this->prepareStatYearData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
			'title', 
			'S', 
			'refOrg' 
            ],
			
            'defaultOrder' => [    'S' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

 public function prepareGoodYearData($params)
   {
    
     $query  = new Query();
                  
     $query->select([ 'SUM(supplySumm) as S', 'supplyGood', ]) 
		->from("{{%supply}}")
        ->where("TIMESTAMPDIFF(MONTH, supplyDate, NOW() ) < 11")
		->groupBy([ 'supplyGood' ]);
	 //$query->andWhere(['=', '{{%schet}}.refOrg', $refOrg]);  

     if (($this->load($params) && $this->validate())) {     
     
        $query->andFilterWhere(['like', 'supplyGood', $this->supplyGood]);
    }


    $idxArray= array();
    //Занулим помесячно    
	$this->dataArray = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($this->dataArray); $i++)
    {
      $idxArray[$this->dataArray[$i]['supplyGood']] = $i;  
      for ($j=1; $j<=12; $j++)  $this->dataArray[$i]['m_'.$j]=0;
    }

    $strSql= "SELECT SUM(supplySumm) as S, year(supplyDate) as Y, month(supplyDate) as M, supplyGood from {{%supply}}
    where TIMESTAMPDIFF(MONTH, supplyDate, NOW() ) < 11 group by month(supplyDate), supplyGood, year(supplyDate)";
	
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll(); 
	for ($i=0; $i< count($list); $i++)
	{
        $m = $list[$i]['M'];
        $s = $list[$i]['S'];
        if (array_key_exists($list[$i]['supplyGood'],$idxArray ))
        {
            $idx = $idxArray[$list[$i]['supplyGood']];
            if ($this->format == 'csv') $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','');
                                   else $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','&nbsp;');
        }
	}	

	
  }
/************/      
  public function getGoodYearData ($params)		
   {
        $this->prepareGoodYearData($params);		
        $dataList=$this->dataArray;

  $monthTitles = array(
	"1" => "январь",
	"2" => "февраль",
	"3" => "март",
	"4" => "апрель",
	"5" => "май",
	"6" => "июнь",
	"7" => "июль",
	"8" => "август",
	"9" => "сентябрь",
	"10" => "октябрь",
	"11" => "ноябрь",
	"12" => "декабрь"); 

/*Предыдущий год*/    
$cur_m = date('n');
$cur_y = date('Y');
$j=1;
for($i=$cur_m+1;$i<=12;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y-1);
$j++;
}

/*Этот год*/        
for($i=1;$i<=$cur_m;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y);
$j++;
}

    $mask = realpath(dirname(__FILE__))."/../uploads/headGoodYearReport*.csv";
    array_map("unlink", glob($mask));      
    $fname = "uploads/headGoodYearReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Товар"),
        iconv("UTF-8", "Windows-1251","Всего"), 

        iconv("UTF-8", "Windows-1251",$lbl[1]),
        iconv("UTF-8", "Windows-1251",$lbl[2]),
        iconv("UTF-8", "Windows-1251",$lbl[3]),
        iconv("UTF-8", "Windows-1251",$lbl[4]),
        iconv("UTF-8", "Windows-1251",$lbl[5]),
        iconv("UTF-8", "Windows-1251",$lbl[6]),
        iconv("UTF-8", "Windows-1251",$lbl[7]),
        iconv("UTF-8", "Windows-1251",$lbl[8]),
        iconv("UTF-8", "Windows-1251",$lbl[9]),
        iconv("UTF-8", "Windows-1251",$lbl[10]),
        iconv("UTF-8", "Windows-1251",$lbl[11]),
        iconv("UTF-8", "Windows-1251",$lbl[12]),
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['supplyGood']),  
        iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['S'],0,'.','')),
        
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[1]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[2]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[3]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[4]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[5]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[6]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[7]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[8]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[9]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[10]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[11]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[12]]),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
/************/      

   public function getGoodYearProvider($params)		
   {

        $this->prepareGoodYearData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
			'supplyGood', 
			'S', 
            ],
			
            'defaultOrder' => ['S' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

   

 public function prepareContactsYearData($params)
   {
    
     $query  = new Query();
                  
     $query->select([ 'count({{%contact}}.id) as S', 'ref_org as refOrg', 'title', '{{%orglist}}.contactPhone', '{{%orglist}}.contactEmail', '{{%orglist}}.contactFIO',]) 
		->from("{{%contact}}, {{%orglist}}")
        ->where("{{%contact}}.ref_org = {{%orglist}}.id and TIMESTAMPDIFF(MONTH, {{%contact}}.contactDate, NOW() ) < 11")
		->groupBy(['ref_org', 'title' ]);

    if (($this->load($params) && $this->validate())) {     
        $query->andFilterWhere(['like', 'title', $this->title]);
    }

    $idxArray= array();
    //Занулим помесячно    
	$this->dataArray = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($this->dataArray); $i++)
    {
      $idxArray[$this->dataArray[$i]['refOrg']] = $i;  
      for ($j=1; $j<=12; $j++)  $this->dataArray[$i]['m_'.$j]=0;
    }

    $strSql= "SELECT count({{%contact}}.id) as S, year({{%contact}}.contactDate) as Y, month({{%contact}}.contactDate) as M, 
    ref_org as refOrg from {{%contact}}
    where TIMESTAMPDIFF(MONTH, {{%contact}}.contactDate, NOW() ) < 11 group by month({{%contact}}.contactDate), ref_org, year({{%contact}}.contactDate)";
	
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll(); 
	for ($i=0; $i< count($list); $i++)
	{
        $m = $list[$i]['M'];
        $s = $list[$i]['S'];
        if (array_key_exists($list[$i]['refOrg'],$idxArray ))
        {
            $idx = $idxArray[$list[$i]['refOrg']];
            if ($this->format == 'csv') $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','');
                                   else $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','&nbsp;');
            
        }
	}	

	
  }

  /************/      
  public function getContactsYearData ($params)		
   {
        $this->prepareContactsYearData($params);		
        $dataList=$this->dataArray;

  $monthTitles = array(
	"1" => "январь",
	"2" => "февраль",
	"3" => "март",
	"4" => "апрель",
	"5" => "май",
	"6" => "июнь",
	"7" => "июль",
	"8" => "август",
	"9" => "сентябрь",
	"10" => "октябрь",
	"11" => "ноябрь",
	"12" => "декабрь"); 

/*Предыдущий год*/    
$cur_m = date('n');
$cur_y = date('Y');
$j=1;
for($i=$cur_m+1;$i<=12;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y-1);
$j++;
}

/*Этот год*/        
for($i=1;$i<=$cur_m;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y);
$j++;
}

    $mask = realpath(dirname(__FILE__))."/../uploads/headContactsYearReport*.csv";
    array_map("unlink", glob($mask));       
    $fname = "uploads/headContactsYearReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Телефон"),
        iconv("UTF-8", "Windows-1251","E-Mail"),
        iconv("UTF-8", "Windows-1251","Контакт ФИО"),        
        
        iconv("UTF-8", "Windows-1251","Всего"), 

        iconv("UTF-8", "Windows-1251",$lbl[1]),
        iconv("UTF-8", "Windows-1251",$lbl[2]),
        iconv("UTF-8", "Windows-1251",$lbl[3]),
        iconv("UTF-8", "Windows-1251",$lbl[4]),
        iconv("UTF-8", "Windows-1251",$lbl[5]),
        iconv("UTF-8", "Windows-1251",$lbl[6]),
        iconv("UTF-8", "Windows-1251",$lbl[7]),
        iconv("UTF-8", "Windows-1251",$lbl[8]),
        iconv("UTF-8", "Windows-1251",$lbl[9]),
        iconv("UTF-8", "Windows-1251",$lbl[10]),
        iconv("UTF-8", "Windows-1251",$lbl[11]),
        iconv("UTF-8", "Windows-1251",$lbl[12]),
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactFIO']),  
        
        iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['S'],0,'.','')),
        
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[1]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[2]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[3]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[4]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[5]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[6]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[7]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[8]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[9]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[10]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[11]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[12]]),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
/************/        
/************/      
   public function getContactsYearProvider($params)		
   {

        $this->prepareContactsYearData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
			'title', 
			'S', 
			'refOrg' 
            ],
			
            'defaultOrder' => [    'S' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

 public function prepareOplataData($params)
   {
    
     $query  = new Query();
                  
     $query->select([ 'SUM(oplateSumm) as S', 'refOrg', 'title', '{{%orglist}}.contactPhone', '{{%orglist}}.contactEmail', '{{%orglist}}.contactFIO',]) 
		->from("{{%oplata}}, {{%orglist}}")
        ->where("{{%oplata}}.refOrg = {{%orglist}}.id and TIMESTAMPDIFF(MONTH, oplateDate, NOW() ) < 11")
		->groupBy(['refOrg', 'title' ]);
	 //$query->andWhere(['=', '{{%schet}}.refOrg', $refOrg]);  

    if (($this->load($params) && $this->validate())) {     
     
        $query->andFilterWhere(['like', 'title', $this->title]);
    }

    $idxArray= array();
    //Занулим помесячно    
	$this->dataArray = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($this->dataArray); $i++)
    {
      $idxArray[$this->dataArray[$i]['refOrg']] = $i;  
      for ($j=1; $j<=12; $j++)  $this->dataArray[$i]['m_'.$j]=0;
    }

    $strSql= "SELECT SUM(oplateSumm) as S, year(oplateDate) as Y, month(oplateDate) as M, refOrg from {{%oplata}}
    where TIMESTAMPDIFF(MONTH, oplateDate, NOW() ) < 11 group by month(oplateDate), refOrg, year(oplateDate)";
	
    
    $list = Yii::$app->db->createCommand($strSql)->queryAll(); 
	for ($i=0; $i< count($list); $i++)
	{
        $m = $list[$i]['M'];
        $s = $list[$i]['S'];
        if (array_key_exists($list[$i]['refOrg'],$idxArray ))
        {
            $idx = $idxArray[$list[$i]['refOrg']];
            if ($this->format == 'csv') $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','');
                                   else $this->dataArray[$idx]['m_'.$m]=number_format($s,0,'.','&nbsp;');
            
        }
	}	

	
  }

  /************/      
  public function getStatOplataData ($params)		
   {
        $this->prepareOplataData($params);		
        $dataList=$this->dataArray;

  $monthTitles = array(
	"1" => "январь",
	"2" => "февраль",
	"3" => "март",
	"4" => "апрель",
	"5" => "май",
	"6" => "июнь",
	"7" => "июль",
	"8" => "август",
	"9" => "сентябрь",
	"10" => "октябрь",
	"11" => "ноябрь",
	"12" => "декабрь"); 

/*Предыдущий год*/    
$cur_m = date('n');
$cur_y = date('Y');
$j=1;
for($i=$cur_m+1;$i<=12;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y-1);
$j++;
}

/*Этот год*/        
for($i=1;$i<=$cur_m;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y);
$j++;
}
 
    $mask = realpath(dirname(__FILE__))."/../uploads/headStatOplataReport*.csv";
    array_map("unlink", glob($mask));        
    $fname = "uploads/headStatOplataReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Телефон"),
        iconv("UTF-8", "Windows-1251","E-Mail"),
        iconv("UTF-8", "Windows-1251","Контакт ФИО"),        
       
        iconv("UTF-8", "Windows-1251","Всего"), 

        iconv("UTF-8", "Windows-1251",$lbl[1]),
        iconv("UTF-8", "Windows-1251",$lbl[2]),
        iconv("UTF-8", "Windows-1251",$lbl[3]),
        iconv("UTF-8", "Windows-1251",$lbl[4]),
        iconv("UTF-8", "Windows-1251",$lbl[5]),
        iconv("UTF-8", "Windows-1251",$lbl[6]),
        iconv("UTF-8", "Windows-1251",$lbl[7]),
        iconv("UTF-8", "Windows-1251",$lbl[8]),
        iconv("UTF-8", "Windows-1251",$lbl[9]),
        iconv("UTF-8", "Windows-1251",$lbl[10]),
        iconv("UTF-8", "Windows-1251",$lbl[11]),
        iconv("UTF-8", "Windows-1251",$lbl[12]),
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['contactFIO']),  

        iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['S'],0,'.','')),
        
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[1]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[2]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[3]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[4]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[5]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[6]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[7]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[8]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[9]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[10]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[11]]),
		iconv("UTF-8", "Windows-1251",$dataList[$i][$atr[12]]),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;           
   }
   
/************/      
   public function getStatOplataProvider($params)		
   {

        $this->prepareOplataData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
			'title', 
			'S', 
			'refOrg' 
            ],
			
            'defaultOrder' => [    'S' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

  
  
  
 public function prepareManagerOrgStatData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

if (empty($this->period) ) $this->period = 30;    
     $query->select(
     [ 
       '{{%user}}.id as userId', 
       'userFIO',
       'count(DISTINCT(c1.ref_org)) as c1N',
       'count(DISTINCT(c5.ref_org)) as c5N',
       '(Select count(DISTINCT (refOrg)) from {{%schet}} 
        where  TO_DAYS(NOW()) - TO_DAYS(schetDate) <= '.$this->period.' 
        and refManager =  {{%user}}.id
        )  as schN', 

        '(Select  count(DISTINCT ({{%schet}}.refOrg)) from {{%schet}}  
            left join {{%supply}} on {{%supply}}.refSchet={{%schet}}.id
            left join {{%oplata}} on {{%oplata}}.refSchet={{%schet}}.id
            where  (ifnull(oplateSumm,0)+ifnull(supplySumm,0)) >0 
            AND (TO_DAYS(NOW()) - TO_DAYS(oplateDate) <= '.$this->period.'  
             OR  TO_DAYS(NOW()) - TO_DAYS(supplyDate) <= '.$this->period.'  
             )
            and refManager =  {{%user}}.id) as sdlN',
       ]) 
		->from("{{%user}}")        
        ->leftJoin ("(Select count(id) as c1N, ref_org, ref_user from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_user, ref_org having count(id) >=1 ) as c1", "c1.ref_user =  {{%user}}.id")
         ->leftJoin ("(Select count(id) as c2N, ref_org, ref_user from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period."  
                     group by ref_user, ref_org having count(id) >=5 ) as c5","c5.ref_user =  {{%user}}.id")         
        ->where("{{%user}}.roleFlg & (0x0004|0x0080)")
		->groupBy(['{{%user}}.id' ]);
	 

     $countquery ->select( "COUNT(DISTINCT({{%user}}.id))" )
                 ->from("{{%user}}")
                 ->where("{{%user}}.roleFlg & (0x0004|0x0080)");
                 
                 
    if (($this->load($params) && $this->validate())) {     
    
    }

//    echo $query->createCommand()->getRawSql();
//return;
    $this->command = $query->createCommand();    
    //$list = $query->createCommand()->queryAll();
    $this->count = $countquery->createCommand()->queryScalar();    
            
	
  }

  /************/      
    

    public function getManagerOrgStatData($params)
    {        
        $this->prepareManagerOrgStatData($params);    
        $dataList=$this->command->queryAll();
        
        
    $mask = realpath(dirname(__FILE__))."/../uploads/managerOrgStatReport*.csv";
    array_map("unlink", glob($mask));                
    $fname = "uploads/managerOrgStatReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Менеджер"),
        iconv("UTF-8", "Windows-1251",">1"),
        iconv("UTF-8", "Windows-1251",">5"),        
        
        iconv("UTF-8", "Windows-1251","Счетов"),
        iconv("UTF-8", "Windows-1251","Сделок"),
        );
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c1N']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c5N']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schN']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['sdlN']), 
        
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       
/************/      
   public function getManagerOrgStatProvider($params)		
   {

        $this->prepareManagerOrgStatData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'userId', 
            'userFIO',
            'c1N',
            'c5N',
            'schN',
            'sdlN' 
            ],
			
            'defaultOrder' => [    'userFIO' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

 
/************/        
/**** Нет контактов, организация привязана к менеджеру ********/      

public function prepareOrgNoContactData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

     
     if (empty($this->period) ) $this->period = 30;    
 
     
     $query->select([ 
        '{{%orglist}}.id',
        '{{%orglist}}.title',
        'ifnull(c1.c1N,0) as c1N', 
        '(SELECT MAX(contactDate) FROM {{%contact}} where ref_org = {{%orglist}}.id ) AS lastContact',
        '{{%user}}.userFIO',
       ]) 
		->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id") 
        ->where("ifnull(c1.c1N,0) =0  AND isOrgActive = 1 ");

       $countquery->select( "COUNT(DISTINCT({{%orglist}}.id))" )
                 ->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")       
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id") 
        ->where("ifnull(c1.c1N,0) =0 AND isOrgActive = 1");

     
      /*Если не задан пользователь, то для всех*/               
        if (!empty($this->userId))       
        {
            /*Определим задан ли статус помошника*/         
            $userRecord = UserList::FindOne($this->userId);
            $this->userFIO = $userRecord->userFIO;
/*            if (!empty($userRecord) && ($userRecord->roleFlg & 0x0080) )
            {
    
                $query->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
           $countquery->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
            }
            else
            {
                $query->andWhere("{{%orglist}}.refManager = ".$this->userId);     
           $countquery->andWhere("{{%orglist}}.refManager = ".$this->userId);          
            }
*/           

                $query->andWhere("{{%orglist}}.refManager = ".$this->userId);     
           $countquery->andWhere("{{%orglist}}.refManager = ".$this->userId);          

        }
               
                 
    if (($this->load($params) && $this->validate())) {     
    
    }

    //echo $query->createCommand()->getRawSql();
    
    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
            
	
  }

/************/        
    public function getOrgNoContactData($params)
    {        
        $this->prepareOrgNoContactData($params);    
        $dataList=$this->command->queryAll();
        
        
    $mask = realpath(dirname(__FILE__))."/../uploads/getOrgNoContactData*.csv";
    array_map("unlink", glob($mask));                        
    $fname = "uploads/getOrgNoContactData".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Контрагент"),                
        iconv("UTF-8", "Windows-1251","Менеджер"),        
        iconv("UTF-8", "Windows-1251","Последний контакт"),        
        iconv("UTF-8", "Windows-1251","Контактов"),
        );
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),  
            iconv("UTF-8", "Windows-1251",$dataList[$i]['lastContact']),              
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c1N']), 
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       

  /************/      
   public function getOrgNoContactProvider($params)		
   {

        $this->prepareOrgNoContactData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'title',
            'c1N',
            'lastContact',
            'userFIO',
            ],
            'defaultOrder' => [    'title' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
 /************************************/      
 /*** Есть контакты, но мало *********/      
 /************************************/
 
public function prepareOrgStatContactsData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

     if (empty($this->period) ) $this->period = 30;    
 
     
     $query->select([ 
        '{{%orglist}}.id',
        '{{%orglist}}.title',
        'c1N', 
        '(SELECT MAX(contactDate) FROM {{%contact}} where ref_org = {{%orglist}}.id ) AS lastContact',
        '{{%user}}.userFIO',        
        '{{%user}}.id AS userId',        
       ]) 
		->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                      
        ->where("c1.c1N >0 AND c1.c1N < 5 AND isOrgActive = 1");

      $countquery ->select( "COUNT(DISTINCT({{%orglist}}.id))" )
                 ->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org  ) as c1", "c1.ref_org =  {{%orglist}}.id")       
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                      
        ->where("c1.c1N >0 AND c1.c1N < 5 AND isOrgActive = 1");
               
       /*Если не задан пользователь, то для всех*/               
        if (!empty($this->userId))       
        {
            /*Определим задан ли статус помошника*/         
            $userRecord = UserList::FindOne($this->userId);
            $this->userFIO = $userRecord->userFIO;            
            if (!empty($userRecord) && ($userRecord->roleFlg & 0x0080) )
            {
    
                $query->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
           $countquery->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
            }
            else
            {
                $query->andWhere("{{%orglist}}.refManager = ".$this->userId);     
           $countquery->andWhere("{{%orglist}}.refManager = ".$this->userId);          
            }
           
        }
                 
    if (($this->load($params) && $this->validate())) {     
    
    }

    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
            
	
  }

  
    public function getOrgStatContactsData($params)
    {        
        $this->prepareOrgStatContactsData($params);    
        $dataList=$this->command->queryAll();
        
        
    $mask = realpath(dirname(__FILE__))."/../uploads/orgStatContactsReport*.csv";
    array_map("unlink", glob($mask));                                
    $fname = "uploads/orgStatContactsReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Контрагент"),        
        iconv("UTF-8", "Windows-1251","Контактов"),
        iconv("UTF-8", "Windows-1251","Менеджер"),        
        iconv("UTF-8", "Windows-1251","Последний контакт"),        

        );
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c1N']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['lastContact']),         
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       
       
  /************/      
   public function getOrgStatContactsProvider($params)		
   {

        $this->prepareOrgStatContactsData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'title',
            'c1N',
            'lastContact',
            'userFIO',
            ],
            'defaultOrder' => [    'c1N' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
            
  /************/      
 
/***************************************/      
 /*** Есть контакты, нет счета *********/      
 /**************************************/
 
public function prepareNoSchetData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

     if (empty($this->period) ) $this->period = 30;    
 
     
     $query->select([ 
        '{{%orglist}}.id',
        '{{%orglist}}.title',
        'c1N', 
        '{{%user}}.userFIO',           
        'schN',              
       ]) 
		->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")   
        ->leftJoin ("(Select count(DISTINCT (id)) as schN, refOrg from {{%schet}} 
        where  TO_DAYS(NOW()) - TO_DAYS(schetDate) <= ".$this->period." 
        group by refOrg ) as s1", "s1.refOrg =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                                                   
        ->where("c1.c1N >= 5  AND isOrgActive = 1 AND ifnull(schN,0) = 0");

      $countquery ->select( "COUNT(DISTINCT({{%orglist}}.id))" )
                 ->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")       
        ->leftJoin ("(Select count(DISTINCT (id)) as schN, refOrg from {{%schet}} 
        where  TO_DAYS(NOW()) - TO_DAYS(schetDate) <= ".$this->period." 
        group by refOrg ) as s1", "s1.refOrg =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                                                   
        ->where("c1.c1N >= 5  AND isOrgActive = 1 AND ifnull(schN,0) =0");
               
    
      /*Если не задан пользователь, то для всех*/               
        if (!empty($this->userId))       
        {
            /*Определим задан ли статус помошника*/         
            $userRecord = UserList::FindOne($this->userId);
            $this->userFIO = $userRecord->userFIO;
            if (!empty($userRecord) && ($userRecord->roleFlg & 0x0080) )
            {
    
                $query->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
           $countquery->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
            }
            else
            {
                $query->andWhere("{{%orglist}}.refManager = ".$this->userId);     
           $countquery->andWhere("{{%orglist}}.refManager = ".$this->userId);          
            }
           
        }
  
    
    if (($this->load($params) && $this->validate())) {     
    
    }

  echo $query->createCommand()->getRawSql();
    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
            
	
  }

/************/        
    public function getNoSchetData($params)
    {        
        $this->prepareNoSchetData($params);    
        $dataList=$this->command->queryAll();
        
        
    $mask = realpath(dirname(__FILE__))."/../uploads/getNoSchetData*.csv";
    array_map("unlink", glob($mask));                                        
    $fname = "uploads/getNoSchetData".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Менеджер"),        
        iconv("UTF-8", "Windows-1251","Контактов"),
        iconv("UTF-8", "Windows-1251","Счетов"),
        );
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),    
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),             
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c1N']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schN']),         
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       

  /************/      
   public function getNoSchetProvider($params)		
   {

        $this->prepareNoSchetData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'title',
            'c1N',
            'schN',
            'userFIO',
            ],
            'defaultOrder' => [    'schN' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/*****************************/
/*****************************/
/*****************************/
 
public function prepareOrgStatSchetData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

     if (empty($this->period) ) $this->period = 30;    
 
     
     $query->select([ 
        '{{%orglist}}.id',
        '{{%orglist}}.title',
        'c1N', 
        'schN', 
        '{{%user}}.userFIO',  

        '(Select  sum(oplateSumm) from {{%schet}}              
            left join {{%oplata}} on {{%oplata}}.refSchet={{%schet}}.id
            where (TO_DAYS(NOW()) - TO_DAYS(oplateDate) <= '.$this->period.'  
             )
            and {{%schet}}.refOrg =  {{%orglist}}.id) as oplata',

        '(Select  Sum(supplySumm) from {{%schet}}  
            left join {{%supply}} on {{%supply}}.refSchet={{%schet}}.id
            where (TO_DAYS(NOW()) - TO_DAYS(supplyDate) <= '.$this->period.'  
             )
            and {{%schet}}.refOrg =  {{%orglist}}.id) as supply',
        
       ]) 
       
		->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")   
        ->leftJoin ("(Select count(DISTINCT (id)) as schN, refOrg from {{%schet}} 
        where  TO_DAYS(NOW()) - TO_DAYS(schetDate) <= ".$this->period." 
        group by refOrg ) as s1", "s1.refOrg =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                                                   
        ->where("c1.c1N > 5  AND isOrgActive = 1 AND ifnull(schN,0) > 0");

      $countquery ->select( "COUNT(DISTINCT({{%orglist}}.id))" )
                 ->from("{{%orglist}}")
        ->leftJoin ("(Select count(id) as c1N, ref_org from {{%contact}} 
                     where  TO_DAYS(NOW()) - TO_DAYS(contactDate) <= ".$this->period." 
                     group by ref_org ) as c1", "c1.ref_org =  {{%orglist}}.id")       
        ->leftJoin ("(Select count(DISTINCT (id)) as schN, refOrg from {{%schet}} 
        where  TO_DAYS(NOW()) - TO_DAYS(schetDate) <= ".$this->period." 
        group by refOrg ) as s1", "s1.refOrg =  {{%orglist}}.id")                            
        ->leftJoin ("{{%user}}","{{%orglist}}.refManager = {{%user}}.id")                                                   
        ->where("c1.c1N >= 5  AND isOrgActive = 1 AND ifnull(schN,0) > 0");
               
    
      /*Если не задан пользователь, то для всех*/               
        if (!empty($this->userId))       
        {
            /*Определим задан ли статус помошника*/         
            $userRecord = UserList::FindOne($this->userId);
            if (!empty($userRecord) && ($userRecord->roleFlg & 0x0080) )
            {
    
                $query->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
           $countquery->andWhere("({{%orglist}}.refManager = ".$this->userId." OR {{%orglist}}.isAvailableForHelper = 1 ) ");     
            }
            else
            {
                $query->andWhere("{{%orglist}}.refManager = ".$this->userId);     
           $countquery->andWhere("{{%orglist}}.refManager = ".$this->userId);          
            }
           
        }
  
    
    if (($this->load($params) && $this->validate())) {     
    
    }

 // echo $query->createCommand()->getRawSql();
    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
            
	
  }

/************/        
    public function getOrgStatSchetData($params)
    {        
        $this->prepareOrgStatSchetData($params);    
        $dataList=$this->command->queryAll();
        
        
        
    $fname = "uploads/orgStatContactsReport.csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Менеджер"),        
        iconv("UTF-8", "Windows-1251","Контактов"),
        iconv("UTF-8", "Windows-1251","Счетов"),
        );
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['title']),    
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),             
            iconv("UTF-8", "Windows-1251",$dataList[$i]['c1N']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schN']),         
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       

  /************/      
   public function getOrgStatSchetProvider($params)		
   {

        $this->prepareOrgStatSchetData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'title',
            'c1N',
            'schN',
            'userFIO',
            'supply',
            'oplata',
            ],
            'defaultOrder' => [    'schN' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

 /*****************************/
 public function getSupplyRequestListProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%request_supply}}.id as requestId', 
            'requestDate', 
            'refSchet', 
            'supplyDate', 
            '{{%schet}}.schetNum', 
            '{{%schet}}.schetDate', 
            'summOplata', 
            'schetSumm', 
            'supplyType', 
            '{{%request_supply}}.contactPhone', 
            '{{%request_supply}}.contactFIO', 
            '{{%request_supply}}.contactEmail', 
            '{{%request_supply}}.adress', 
            'requestNote', 
            '{{%request_supply}}.supplyState', 
            'dstNote', 
            'finishDate', 
            'execNum', 
            'supplyNote', 
            'userFIO', 
            'title',
            'viewManagerRef',
            'execView',
            '{{%schet}}.refOrg',
            '{{%schet}}.refZakaz',
            'st1','st2','st3','st4','st5','st6','st7','st8','st9','st10',
            'st11','st12','st13','st14','st15','st16','st17',
            '{{%request_supply}}.isAccepted',
            ])
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%request_supply}}.id)")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;

     $query->andWhere(['=', 'isSchetActive', 1]);
     $countquery->andWhere(['=', 'isSchetActive', 1]);
            
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     
     
        $query->andFilterWhere(['like', 'schetNum', $this->schetNumber]);
        $countquery->andFilterWhere(['like', 'schetNum', $this->schetNumber]);     
     
/*        if (!empty($this->supplyDate))
        {
        $query->andFilterWhere(['or',
            ['=','supplyDate',date("Y-m-d",strtotime($this->supplyDate))],
            ['=','finishDate',date("Y-m-d",strtotime($this->supplyDate))]
            ]);
        $countquery->andFilterWhere(['or',
            ['=','supplyDate',date("Y-m-d",strtotime($this->supplyDate))],
            ['=','finishDate',date("Y-m-d",strtotime($this->supplyDate))]
            ]);
            
        }
*/
        
     }
        
        if (empty($this->isAccepted)) $this->isAccepted = 3;
        
        switch ($this->isAccepted)
        {
            case 2:            
                $query->andFilterWhere(['=', 'isAccepted', 1]);
                $countquery->andFilterWhere(['=', 'isAccepted', 1]);
            break;

            case 3:
                $query->andFilterWhere(['<=', 'isAccepted', 0]);
                $countquery->andFilterWhere(['<=', 'isAccepted', 0]);                        
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
            'requestId', 
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
            'execView',
            'isAccepted',
            ],
            
            'defaultOrder' => [  'requestDate' => SORT_DESC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
/*******************************************/
/*****************************/
/*****************************/
/*****************************/
 
public function prepareActiveSdelkaData($params)
   {
    
     $query  = new Query();
     $countquery  = new Query();

    
     $query->select([ 
        'zakaz.formDate as zakazdate', 
        'zakaz.id as refZakaz',
        'schet.schetDate', 
        'schet.id as refSchet',
        'max(oplateDate) as oplDate', 
        'sum(oplateSumm) as oplSum',
        'max(supply.supplyDate) as supDate', 
        'sum(supplySumm) as supSum',
        'orglist.title as orgTitle',  
        'zakaz.refOrg',
        'users.userFIO', 
        'orglist.refManager',
        'request.requestDate',       
        'request.id as requestRef',
       ])        
		->from("{{%zakaz}} as zakaz")
        ->leftJoin ("{{%schet}}   as schet"  ,"zakaz.id = schet.refZakaz")                                                   
        ->leftJoin ("{{%oplata}}  as oplata" ,"oplata.refSchet = schet.id")              
        ->leftJoin ("{{%supply}}  as supply" ,"supply.refSchet = schet.id")              
        ->leftJoin ("{{%orglist}} as orglist","orglist.id = zakaz.refOrg")
        ->leftJoin ("{{%user}}    as users"  ,"orglist.refManager = users.id")
        ->leftJoin ("{{%request_supply}}    as request"  ,"request.refSchet = schet.id")        
        ->where("zakaz.isActive = 1 or schet.isSchetActive = 1")
        ->groupBy("zakaz.id");

    $countquery ->select("COUNT(DISTINCT(zakaz.id))" )
        ->from("{{%zakaz}} as zakaz")
        ->leftJoin ("{{%schet}}   as schet"  ,"zakaz.id = schet.refZakaz")                                                   
        ->leftJoin ("{{%oplata}}  as oplata" ,"oplata.refSchet = schet.id")              
        ->leftJoin ("{{%supply}}  as supply" ,"supply.refSchet = schet.id")              
        ->leftJoin ("{{%orglist}} as orglist","orglist.id = zakaz.refOrg")                      
        ->leftJoin ("{{%user}}    as users"  ,"orglist.refManager = users.id")                                                   
        ->where("zakaz.isActive = 1 or schet.isSchetActive = 1")
        ;
               
   
    if (($this->load($params) && $this->validate())) {     
         $query->andFilterWhere(['Like', 'orglist.title', $this->orgTitle]);
         $countquery->andFilterWhere(['Like', 'orglist.title', $this->orgTitle]);    
         
         $query->andFilterWhere(['Like', 'userFIO', $this->userFIO]);
         $countquery->andFilterWhere(['Like', 'userFIO', $this->userFIO]);             
    }

    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
    $this->debug[]=   $this->count;      
    $this->debug[]=$countquery->createCommand()->getRawSql();
	
  }

/************/        
    public function getActiveSdelkaData($params)
    {        
        $this->prepareActiveSdelkaData($params);    
        $dataList=$this->command->queryAll();



        $mask = realpath(dirname(__FILE__))."/../uploads/activeSdelkaReport*.csv";
        array_map("unlink", glob($mask));       
        $fname = "uploads/activeSdelkaReport".time().".csv";
        $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
        if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
        $col_title = array (
        iconv("UTF-8", "Windows-1251","Заявка"),
        iconv("UTF-8", "Windows-1251","Счет"),        
        iconv("UTF-8", "Windows-1251","Оплата"),
        iconv("UTF-8", "Windows-1251","Приход"),
        iconv("UTF-8", "Windows-1251","Заяв. отгр."),
        iconv("UTF-8", "Windows-1251","Отгрузка"),
        iconv("UTF-8", "Windows-1251","Отгружено"),
        iconv("UTF-8", "Windows-1251","Договор"),
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Менеджер"),
        );
                
        fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
         $contracTlist = Yii::$app->db->createCommand(" SELECT internalNumber, dateStart
                FROM {{%contracts}}  where refOrg = :refOrg ORDER BY internalNumber DESC",
                [':refOrg' => $dataList[$i]['refOrg']]
                )->queryAll();       
         $contract = "";                                       
         if (count ($contracTlist) > 0 ) {
                $stTime = strtotime($contracTlist[0]['dateStart']);
                if ($stTime < 1000) $contract = $contracTlist[0]['internalNumber']." n/a ";                
                $contract = $contracTlist[0]['internalNumber']." ".date("d.m.Y", strtotime($contracTlist[0]['dateStart']));                
                }
                
         $schetDate = ""; 
         if (!empty ($dataList[$i]['refSchet'])) $schetDate = date("d.m.Y", strtotime($dataList[$i]['schetDate']));

         $oplDate ="";
         $oplSum  ="";
         if ($dataList[$i]['oplSum'] > 0.1){
            $oplDate =date("d.m.Y", strtotime($dataList[$i]['oplDate']));
            $oplSum  =number_format($dataList[$i]['oplSum'],2,"."," ");
         }

         $requestTime=strtotime($dataList[$i]['supDate']);
         if ($requestTime < 1000 ) $requestDate ="";
                             else  $requestDate = date("d.m.Y", $requestTime);               
         $supDate ="";
         $supSum  ="";
         if ($dataList[$i]['supSum'] > 0.1){
             $supDate = date("d.m.Y", strtotime($dataList[$i]['supDate']));
             $supSum  = number_format($dataList[$i]['supSum'],2,"."," ");
         }
 
 

        $list = array 
            (
            iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataList[$i]['zakazdate'])) ),    
            iconv("UTF-8", "Windows-1251",$schetDate),
            iconv("UTF-8", "Windows-1251",$oplDate),
            iconv("UTF-8", "Windows-1251",$oplSum),
            iconv("UTF-8", "Windows-1251",$requestDate),
            iconv("UTF-8", "Windows-1251",$supDate),
            iconv("UTF-8", "Windows-1251",$supSum),
            iconv("UTF-8", "Windows-1251",$contract),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),         
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
       

  /************/      
   public function getActiveSdelkaProvider($params)		
   {

        $this->prepareActiveSdelkaData($params);
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'zakazdate',             
            'schetDate', 
            'oplDate', 
            'oplSum',
            'supDate', 
            'supSum',
            'orgTitle',  
            'userFIO', 
            'requestDate',
            ],
            'defaultOrder' => ['zakazdate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   

 /*****************************/ 

   public function  getOrgCategoryProvider($params)		
   {

     $query  = new Query();
     $countquery  = new Query();
   
     $query->select([ 
        '{{%org_category}}.id', 
        '{{%org_category}}.catTitle',
       ])        
		->from("{{%org_category}}")
		;

    $countquery ->select("COUNT(DISTINCT({{%org_category}}.id))" )
       ->from("{{%org_category}}")
		;
               
   
    if (($this->load($params) && $this->validate())) {     
    }

    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',             
            'catTitle', 
            ],
            'defaultOrder' => ['id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
 

 /*****************************/

   public function  getWareTypeProvider($params)
   {

     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        'id',
        'wareTypeName',
       ])
		->from("{{%ware_type}}")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_type}}.id))" )
      ->from("{{%ware_type}}")
		;


    if (($this->load($params) && $this->validate())) {
    }

    $this->command = $query->createCommand();
    $this->count = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,

            'pagination' => [
            'pageSize' => 30,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'wareTypeName',
            ],
        //    'defaultOrder' => ['wareTypeRef' => SORT_ASC ],
            ],
        ]);

    return $provider;
   }



   public function  getWareGrpProvider($params)
   {

     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%ware_grp}}.id',
        'wareGrpTitle',
        'wareTypeRef',
        'wareTypeName',
       ])
		->from("{{%ware_grp}}")
		->leftJoin("{{%ware_type}}","{{%ware_grp}}.wareTypeRef = {{%ware_type}}.id")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_grp}}.id))" )
      ->from("{{%ware_grp}}")
      ->leftJoin("{{%ware_type}}","{{%ware_grp}}.wareTypeRef = {{%ware_type}}.id")
		;


    if (($this->load($params) && $this->validate())) {
    }

    $this->command = $query->createCommand();
    $this->count = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,

            'pagination' => [
            'pageSize' => 30,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'wareTypeName',
            ],
        //    'defaultOrder' => ['wareTypeRef' => SORT_ASC ],
            ],
        ]);

    return $provider;
   }






   /** end of object **/     
 }
