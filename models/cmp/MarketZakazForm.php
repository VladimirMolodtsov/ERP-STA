<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\SchetNeedList;
use app\models\ContactList;
use app\models\ZakazList;
use app\models\ZakazContent;
use app\models\ZakazHistory;
use app\models\CalendarList;
use app\models\MarketCalendarForm; 

use app\models\TblWareNames;
use app\models\TblWareEd;

/**
 * MarketZakazForm  - модель для работы с заказами
 */
class MarketZakazForm extends Model
{
    
    public $contactEmail = "";
    public $contactPhone = "";
    public $contactFIO ="";
    
    public $debug;
        
    public $nextContactDate = "";
    public $nextContactTime = "-";   
    public $nextdate = "";
    public $note= "";
    
    public $status = 0;
//    public $id = 0;
    public $zakazId = 0;
    public $orgId = 0;
    public $action = "";
    
    public $title;
    public $contactDate;     
    public $userFIO; 
    
    public $LastContact;
    public $last1CDate;
    public $isAvailableForHelper;
    public $activeZakaz;
    public $activeSchet;
    public $isOrgActive;

    public $orgTitle;
    
    
    public $showMyClient=0; //только мои клиенты
    
    public $email ="";
    public $subject="";
    public $body="";
    public $fromEmail = "zakaz@rik-nsk.ru";

    
    public $resrveStatus=0;    
    public $initialZakaz = "";
    
    public $sumZakaz=0;
    
    /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    
    
    public function rules()
    {
        return [
            /*[['contactPhone'], 'required'],*/
            [['title', 'userFIO','activeZakaz','activeSchet','LastContact','last1CDate','isAvailableForHelper', 'isOrgActive'], 'safe'],
            [['email', 'subject', 'body', 'initialZakaz', 'resrveStatus', 'orgId', 'zakazId',  'action', 'contactFIO', 
            'nextContactDate', 'nextContactTime','contactEmail', 'contactPhone', 'note', 'status', 'nextdate',  'orgTitle',          
            'recordId','dataType','dataVal'
            ], 'default'],
            [['initialZakaz', 'contactFIO', 'contactEmail', 'contactPhone', 'note', ], 'trim'],
            ['contactFIO', 'string', 'length' => [1,150]],                        
            ['initialZakaz', 'string', 'length' => [1,250]],                                    
            ['status', 'in', 'range' => [0,1,2,3]],            
            ['orgId', 'integer'],            
            ['zakazId', 'integer'],
/*            ['nextdate', 'date',  'format' => 'php:d.m.Y'],            */
            ['contactEmail', 'email'],
        ];
    }

/*************************************************************************************/
/*************************************************************************************/
/* Ajax */

   /**********************************/
   public function saveAjaxData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
           
      $record= ZakazContent::findOne($this->recordId);     
     if (empty($record)) return;
           
           
    switch ($this->dataType)
    {
        case 'wareTitle':
           $record->good = mb_substr($this->dataVal, 0, 100, 'utf-8');
           $record->initialZakaz = $record->good;           
           $record->save(); 
           $res['val'] =  $record->good ;
           break;
        case 'count':
            $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->count = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->count ;
           break;
        case 'ed':
           $record->ed = mb_substr($this->dataVal, 0, 20, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->ed ;
           break;
        case 'value':
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->value = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->value ;
           break;
        case 'dopRequest':
           $record->dopRequest = mb_substr($this->dataVal, 0, 150, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->dopRequest ;
           break;
           
           
     }      
     
    $res['res'] = true;    
    return $res;

    }

   /**********************************/
   public function saveAjaxStatus()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
           
    $record= ZakazList::findOne($this->recordId);     
    if (empty($record)) return;
    switch ($this->dataType)
    {
        case 'status':
           if ($this->dataVal == 3){
                $record->isActive = 1;
                $record->isFormed = 0;
           }
           if ($this->dataVal == 2){
                $record->isActive = 0;
                $record->isFormed = 0;
           }
           if ($this->dataVal == 1){
                $record->isActive = 0;
                $record->isFormed = 1;
           }           
           $record->save(); 
           $res['val'] =  $record->status;
           break;           
     }      
     
    $res['res'] = true;    
    return $res;

    }


   public function addWareZakaz($zakazId, $wareRef, $wareEd)
   {
       $ret=[
        'zakazId' => $zakazId, 
        'wareRef' => $wareRef, 
        'wareEd' => $wareEd,      
        'res' => false
    ];
   
   if ($wareRef==0)
   {
       $record = new ZakazContent();   
       $record->refZakaz = intval($zakazId);
       $record->save();    
       $ret['res'] = true;
       return $ret;       
   }
   
    $wareRecord = TblWareNames::findOne($wareRef);
    if(empty($wareRecord)) return $ret;
    $wareRecord->useCount++;
    $wareRecord->save();

    
    $record = new ZakazContent();
    if(empty($record)) return $ret;
        
      $record->refZakaz = intval($zakazId);
      $record->initialZakaz = $wareRecord->wareTitle;
      $record->good = $wareRecord->wareTitle;
      $record->wareListRef = $wareRecord->wareListRef;
      $record->wareNameRef = $wareRecord->id;
      $record->count = 0;
      $record->ed = $wareRecord->wareEd;
      $record->value = $wareRecord->v3;
      $record->save();    
   $ret['res'] = true;
   return $ret;       
   }
/*************************************************************************************/
/*************************************************************************************/

 /*
    Назначение второго менеджера
 */
   public function helperSetEnable($id)
   {
     Yii::$app->db->createCommand('UPDATE {{%orglist}} SET isAvailableForHelper=1 WHERE id=:id') 
                    ->bindValue(':id', $id)                    
                    ->execute();  
       
   }

   public function helperSetDisable($id)
   {
     Yii::$app->db->createCommand('UPDATE {{%orglist}} SET isAvailableForHelper=0 WHERE id=:id') 
                    ->bindValue(':id', $id)                    
                    ->execute();  
       
   }

   
   public function getClientManagmentProvider($params)
   {

     $query  = new Query();
     $countquery  = new Query();
     $from = date("Y-m-d", time()-60*60*24*30);
    
    
     $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("(SELECT MAX(contactDate) AS LastContact, ref_org from {{%contact}}  group by ref_org) as c ", "c.ref_org = {{%orglist}}.id")
                 ;
                 
    
     $query->select("{{%orglist}}.id, title, contactDate, {{%user}}.userFIO, LastContact, last1CDate, isAvailableForHelper")
            ->from("{{%orglist}} ") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin("(SELECT MAX(contactDate) AS LastContact, ref_org from {{%contact}}  group by ref_org) as c ", "c.ref_org = {{%orglist}}.id")
            ;
    

/*
    без назначения и так доступны
*/    
    $query->andFilterWhere(['>', '{{%orglist}}.refManager', 0]);
    $countquery->andFilterWhere(['>', '{{%orglist}}.refManager', 0]);
        
    

       if (($this->load($params) && $this->validate())) 
    {
        /* Организация */
        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        
        /* Менеджер */        
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);
 
        if($this->LastContact == 1)
        {
          $query->andFilterWhere(['>', 'LastContact', $from]);
          $countquery->andFilterWhere(['>', 'LastContact', $from]);
        }
        if($this->LastContact == 2)
        {
/*          $query->andFilterWhere(['<', 'LastContact', $from]);
          $countquery->andFilterWhere(['<', 'LastContact', $from]);            
*/          
            $query->andWhere("(LastContact < $from  OR LastContact IS NULL)");
            $countquery->andWhere("(LastContact < $from  OR LastContact IS NULL)");

          
        }
        
        if($this->last1CDate == 1)
        {
            $query->andWhere("(last1CDate > $from  )");
            $countquery->andWhere("(last1CDate > $from )");
        }
        if($this->last1CDate == 2)
        {            
            $query->andWhere("(last1CDate < $from  OR last1CDate IS NULL)");
            $countquery->andWhere("(last1CDate < $from  OR last1CDate IS NULL)");
            
          /*$query->andFilterWhere(['<', 'last1CDate', $from]);
          $countquery->andFilterWhere(['<', 'last1CDate', $from]);            */
        }
        
        
        
        if($this->isAvailableForHelper == 1)
        {
          $query->andFilterWhere(['=', 'isAvailableForHelper', 1]);
          $countquery->andFilterWhere(['=', 'isAvailableForHelper', 1]);
        }
        else if($this->isAvailableForHelper == 2)
        {
          $query->andFilterWhere(['=', 'isAvailableForHelper', 0]);
          $countquery->andFilterWhere(['=', 'isAvailableForHelper', 0]);            
        }
        else 
        {
          $query->andFilterWhere(['>', 'isAvailableForHelper', -1]);
          $countquery->andFilterWhere(['>', 'isAvailableForHelper', -1]);            
        }
            
     
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
            'contactDate',
            'nextContactDate',
            'userFIO',
            'LastContact', 
            'last1CDate', 
            'isAvailableForHelper',
            ],
            'defaultOrder' => [    'last1CDate' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   


/************************************************************************************/    
    public function getNeedList()
    {
      $strSql = "Select ref_org, ref_need_title, {{%need_title}}.`Title`    from {{%schet_need}}, {{%need_title}}
                  where {{%schet_need}}.`ref_need_title` = {{%need_title}}.`id` and {{%schet_need}}.ref_org =:ref_org";
      $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->orgId])->queryAll();             
      return $ret;

    
    }


    public function getContactDetail($limit)
    {
    $ret = array();
    
    if ($this->zakazId > 0)
    {
      $strSql = "Select contactFIO, note, contactDate, phone, status from {{%contact}} left join {{%phones}} 
                 on {{%contact}}.ref_phone={{%phones}}.id  where {{%contact}}.refZakaz = :refZakaz
                 AND ifnull(eventType,0) <5
                 order by {{%contact}}.id DESC";                 
      if (!empty($limit)) $strSql .=" LIMIT ".intval($limit); 
      $ret =  Yii::$app->db->createCommand($strSql, [':refZakaz' =>$this->zakazId ])->queryAll();                   
     }
/*     if (count($ret) == 0)
     {
           $strSql = "Select contactFIO, note, contactDate, phone, status from {{%contact}} left join {{%phones}} 
                 on {{%contact}}.ref_phone={{%phones}}.id  where {{%contact}}.ref_org=:ref_org 
                 order by {{%contact}}.id DESC LIMIT 3";
    
          $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->orgId ])->queryAll();     
     }
*/      
      
      return $ret;
    }    
    
    
    
    public function getCompanyPhones()
   {
          $ret =  Yii::$app->db->createCommand('SELECT DISTINCT phone, status, phoneContactFIO from {{%phones}} 
          left join {{%contact}} ON {{%contact}}.ref_phone = {{%phones}}.id 
          where {{%phones}}.ref_org=:ref_org ORDER BY contactDate DESC, {{%phones}}.status ASC '
          ,[':ref_org'=>$this->orgId])->queryAll();       
        return $ret;
   }   
   
    
    public function getCurrentlyInWork()
   {
        $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND  {{%orglist}}.ref_user=:ref_user '
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
            'SELECT count({{%zakaz}}.id) FROM {{%zakaz}}, {{%orglist}} where {{%zakaz}}.refOrg = {{%orglist}}.id 
            AND isActive = 1 AND {{%orglist}}.ref_user =:ref_user ', 
            [':ref_user' => $curUser->id])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 'SELECT {{%zakaz}}.id as zakazId, formDate, isActive, {{%zakaz}}.isFormed, {{%zakaz}}.isGoodReserved,
            {{%orglist}}.id as orgId, {{%orglist}}.`title`, {{%orglist}}.contactDate 
            FROM {{%zakaz}}, {{%orglist}} where {{%zakaz}}.refOrg = {{%orglist}}.id 
            AND isActive = 1 AND {{%orglist}}.ref_user =:ref_user ',                    
            'params' => [':ref_user' => $curUser->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'zakazId',
            'title',            
            'formDate',
            'contactDate',
            ],
            ],
        ]);
    return $provider;
   }   

 /*
    Все организации закрепленные за менеджером   
 */
   public function getClientListProvider($params)
   {
     
     $curUser=Yii::$app->user->identity;

     $query  = new Query();
     $countquery  = new Query();

    
    
     $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as d ", "d.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ;
                 
    
     $query->select("{{%orglist}}.id, title, contactDate, nextContactDate, {{%user}}.userFIO, activeSchet, activeZakaz, isOrgActive" )
            ->from("{{%orglist}} ") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
            ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as d ", "d.refOrg = {{%orglist}}.id")
            ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
            ;
               
      if ($this->showMyClient == 1)
      {
             $countquery->where("refManager=:refUser ");
            $query->where("refManager=:refUser ");                 
      
      }
      else
      {
        if ($curUser->roleFlg & 0x0080) //Если менеджер тип 2
        {
            //$dateOfFree = date('Y-m-d', time() - 60*60*24*90); //90 дней
            
            $countquery->where("(refManager=:refUser OR refManager is NULL OR refManager =0 OR isAvailableForHelper =1)");
            $query->where("(refManager=:refUser OR refManager is NULL OR refManager =0 OR isAvailableForHelper =1)");

         }
        else
        {
            $countquery->where("refManager=:refUser OR refManager is NULL OR refManager =0");
                 $query->where("refManager=:refUser OR refManager is NULL OR refManager =0 ");            
        }
      }
  

       if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        
        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        
        if (!empty($this->nextContactDate))
        {
        $countquery->andFilterWhere(['=', 'nextContactDate', date("Y-m-d",strtotime($this->nextContactDate))]);
        $query->andFilterWhere(['=', 'nextContactDate', date("Y-m-d",strtotime($this->nextContactDate))]);
         }
         
         
        $query->andFilterWhere(['>=', 'activeSchet', $this->activeSchet]);
        $countquery->andFilterWhere(['>=', 'activeSchet', $this->activeSchet]);
        

        $query->andFilterWhere(['>=', 'activeZakaz', $this->activeZakaz]);
        $countquery->andFilterWhere(['>=', 'activeZakaz', $this->activeZakaz]);
        
        if ($this->isOrgActive == 1)
        {
            $query->andFilterWhere(['=', 'isOrgActive', 1]);
            $countquery->andFilterWhere(['=', 'isOrgActive', 1]);            
        }
        if ($this->isOrgActive == 2)
        {
            $query->andFilterWhere(['=', 'isOrgActive', 0]);
            $countquery->andFilterWhere(['=', 'isOrgActive', 0]);            
        }
        
     }

        $query->addParams([':refUser' => $curUser->id]);
        $countquery->addParams([':refUser' => $curUser->id]);

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
            'contactDate',
            'nextContactDate',
            'userFIO',
            'activeSchet', 
            'activeZakaz',
            ],
            'defaultOrder' => [    'nextContactDate' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   

/*Доступные заказы*/
    
    public function getAvailiableZakazListProvider()
   {
        $curUser=Yii::$app->user->identity;
        //Если менеджер активных продаж
/*        if ($curUser->roleFlg & 0x0004)
        {*/
            
      $strCount = 'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1 AND refManager=0';
      $strSql   = 'SELECT id, title, isSchetReject, contactDate, nextContactDate FROM   {{%orglist}}
                 WHERE isPreparedForSchet=1 AND refManager=0';
/*        }
*/
                 
        //Если менеджер тип 2
        if ($curUser->roleFlg & 0x0080)
        {
            //$dateOfFree = date('Y-m-d', time() - 60*60*24*90); //90 дней
            
            $strCount = "SELECT count(id) from {{%orglist}} where isPreparedForSchet=1 AND (refManager=0 OR isAvailableForHelper =1)";
            $strSql   = "SELECT {{%orglist}}.id, title, isSchetReject, contactDate, nextContactDate, userFIO FROM   {{%orglist}}
                         left join  {{%user}} on {{%user}}.id = {{%orglist}}.refManager
                 WHERE isPreparedForSchet=1 AND (refManager=0 OR isAvailableForHelper =1)";            
        }
                
        
        $count = Yii::$app->db->createCommand($strCount,    [':isInWork' => 0])->queryScalar();            
        $provider = new SqlDataProvider(['sql' => $strSql,
            'params' => [':isInWork' => 0],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            'nextContactDate',
            'userFIO'
            ],
            ],
        ]);
    return $provider;
   }   

   
   public function getZakazDetailProvider()
   {
       $this->sumZakaz=Yii::$app->db->createCommand(
            'SELECT sum(value*count) from {{%zakazContent}} where refZakaz=:zakazId', 
            [':zakazId' => $this->zakazId])->queryScalar();
            
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%zakazContent}} where refZakaz=:zakazId', 
            [':zakazId' => $this->zakazId])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            'SELECT {{%zakazContent}}.id, {{%zakazContent}}.isActive,{{%zakaz}}.refOrg as orgId, {{%zakaz}}.id AS zakazId, initialZakaz, good, spec, ed, value, count,  dopRequest, dostavka, wareNameRef  FROM   {{%zakazContent}}, {{%zakaz}}                   
               where {{%zakazContent}}.refZakaz = {{%zakaz}}.id                   
                 AND  refZakaz=:zakazId ',
            'params' => [':zakazId' => $this->zakazId],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'initialZakaz',
            'good', 
            'spec', 
            'ed', 
            'value', 
            'count',  
            'dopRequest', 
            'dostavka', 
            'isActive'
            ],
            'defaultOrder' => [    'isActive' => SORT_DESC, 'id'  => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   

  public function getZakazHistoryProvider()
   {
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%zakazHistory}}             
            where refZakaz=:zakazId', 
            [':zakazId' => $this->zakazId])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT {{%zakazHistory}}.id,  proposal, propDate, title, {{%zakazContent}}.initialZakaz  
            FROM   {{%zakazHistory}} 
            LEFT JOIN {{%zakazHistoryParam}} on {{%zakazHistory}}.refParam={{%zakazHistoryParam}}.id
            LEFT JOIN {{%zakazContent}} on {{%zakazHistory}}.zakazContentRef = {{%zakazContent}}.id
                  where  {{%zakazHistory}}.refZakaz=:zakazId ',
            'params' => [':zakazId' => $this->zakazId],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'proposal',
            'propDate', 
            'title', 
            'initialZakaz'
            ],
            'defaultOrder' => [    'propDate' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   


   public function  delZakaz($zakazId)         
   {


    $zakazContentRecord  = ZakazContent::findOne($zakazId);
    if (empty ($zakazContentRecord )) {return;}
    $zakazContentRecord->isActive = 0;    
    $zakazContentRecord->save();
    
    $zakazHistoryRecord = new ZakazHistory;
    $zakazHistoryRecord->refZakaz = $zakazContentRecord->refZakaz;
    $zakazHistoryRecord->zakazContentRef = $zakazContentRecord->id;
    $zakazHistoryRecord->refParam = 8;   
    $zakazHistoryRecord->proposal = "-";
    $zakazHistoryRecord->propDate = date("Y-m-d h:i:s");
    $zakazHistoryRecord->save();



   }

   public function  reverseZakaz($zakazId)         
   {
    $zakazContentRecord  = ZakazContent::findOne($zakazId);
    if (empty ($zakazContentRecord )) {return;}
    $zakazContentRecord->isActive = 1;    
    $zakazContentRecord->save();
    
    $zakazHistoryRecord = new ZakazHistory;
    $zakazHistoryRecord->refZakaz = $zakazContentRecord->refZakaz;
    $zakazHistoryRecord->zakazContentRef = $zakazContentRecord->id;
    $zakazHistoryRecord->refParam = 9;   
    $zakazHistoryRecord->proposal = "-";
    $zakazHistoryRecord->propDate = date("Y-m-d h:i:s");
    $zakazHistoryRecord->save();
    
   }

   public function  removeZakaz($id)
   {

    $zakazContentRecord  = ZakazContent::findOne($id);
    if (empty ($zakazContentRecord )) {return;}

    Yii::$app->db->createCommand('UPDATE {{%otves_list}}, {{%ware_names}} set refZakaz = 0
           WHERE  {{%otves_list}}.refWareList =  {{%ware_names}}.wareListRef  AND {{%ware_names}}.id = :refWareList and refZakaz =:refZakaz',
           [
               ':refWareList' => $zakazContentRecord ->wareNameRef,
               ':refZakaz'    => $zakazContentRecord ->refZakaz,
           ])->execute();

    $zakazContentRecord->delete();
   }

   

   
   public function  addToZakaz($inZakaz)
   {
    $zakazContentRecord = new ZakazContent;
    $zakazContentRecord->refZakaz = $this->zakazId;
    $zakazContentRecord->initialZakaz = $inZakaz;
    $zakazContentRecord->save();    
   
       $zakazHistoryRecord = new ZakazHistory;
    $zakazHistoryRecord->refZakaz = $this->zakazId;
    $zakazHistoryRecord->refParam =0;   
    $zakazHistoryRecord->proposal = $inZakaz;
    $zakazHistoryRecord->propDate = date("Y-m-d h:i:s");
    $zakazHistoryRecord->save();
   }
   
   public function  addRecToZakaz($zakazId, $GoodTitle,$count, $ed, $Val)
   {    

        $strSql="SELECT id from {{%warehouse}} 
                where {{%warehouse}}.title = '".$GoodTitle."'";                
        $warehouseRef = Yii::$app->db->createCommand($strSql)->queryScalar();        
        if (empty($warehouseRef)) $warehouseRef = 0;
   
        $zakazContentRecord = new ZakazContent;
        $zakazContentRecord->refZakaz = $zakazId;
        $zakazContentRecord->initialZakaz = $GoodTitle;
        $zakazContentRecord->good = $GoodTitle;
        $zakazContentRecord->warehouseRef = $warehouseRef;
        $zakazContentRecord->count = $count;
        $zakazContentRecord->ed = $ed;
        $zakazContentRecord->value = $Val;
        $zakazContentRecord->save();    
       
   }
   
   
   public function  addProposal($zakazContentId, $proposal, $actionType)
   {
    $zakazContentRecord = ZakazContent::findOne($zakazContentId);
    
    switch ($actionType) {
    case 0:
        $zakazContentRecord->initialZakaz = $proposal; //Начальный заказ
        break;
    case 1:
        $zakazContentRecord->good = $proposal; //Предложение товара
        $strSql="SELECT id from {{%warehouse}} where {{%warehouse}}.title = '".$proposal."'";                
        $warehouseRef = Yii::$app->db->createCommand($strSql)->queryScalar();        
        if (empty($warehouseRef)) $warehouseRef = 0;
        $zakazContentRecord->warehouseRef = $warehouseRef;
        break;
    case 2:
        $zakazContentRecord->spec = $proposal; //Выяснение спецификации
        break;
    case 3:
        $zakazContentRecord->ed = $proposal; //Единицы измерения
        break;
    case 4:
        $zakazContentRecord->value = $proposal; //Согласование цены
        break;
    case 5:
        $zakazContentRecord->count = $proposal; //Соглосование количества
        break;
    case 6:
        $zakazContentRecord->dopRequest = $proposal; //Дополнительные условия
        break;
    case 7:
        $zakazContentRecord->dostavka = $proposal; //Доставка
        break;        
    }
    $zakazContentRecord->save();    
   
       $zakazHistoryRecord = new ZakazHistory;
    $zakazHistoryRecord->refZakaz = $zakazContentRecord->refZakaz;
    $zakazHistoryRecord->zakazContentRef = $zakazContentId;
    $zakazHistoryRecord->refParam = $actionType;   
    $zakazHistoryRecord->proposal = $proposal;
    $zakazHistoryRecord->propDate = date("Y-m-d  h:i:s");
    $zakazHistoryRecord->save();
   }
   
   
   
   public function getNewZakaz($orgId)        
   {
     $curUser=Yii::$app->user->identity;

     
     $record = OrgList::findOne($orgId);
     if (!empty($record))
     {
        $dopGood = $record->otherGood;
        if ($record->refManager ==0){ $record->refManager = $curUser->id; }        
        $record->ref_user = $curUser->id;          
        $record->save();
     }
           
     $record = new ZakazList;
     $record->refOrg=$orgId;
     $record->ref_user = $curUser->id;
     $record->isActive=1;
     $record->formDate = new Expression("NOW()");
     $record->save();
     
      $this->zakazId = $record->id;
        $needList =  Yii::$app->db->createCommand('
      SELECT {{%need_title}}.id, {{%need_title}}.row, {{%need_title}}.Title
      from {{%need_title}}, {{%schet_need}} 
      where {{%need_title}}.id = {{%schet_need}}.ref_need_title
      AND ref_org=:ref_org
      order by row', 
      [            
        ':ref_org' => $orgId,
      ])->queryAll();                   
      
        for ($i=0; $i<count ($needList); $i++)
      {
          $this->addToZakaz($needList[$i]['Title']);
      }
      if (!empty($dopGood) && ($dopGood!="") )
      {
          $this->addToZakaz($dopGood);
      }

      $eventNote = "Согласовать заявку";
      $event_ref = 3;
      
          
      /*Добавим запись в календарь*/
            $calendar = new MarketCalendarForm();
            //Предыдущее событие выполнено       
            $calendar->markRefEvent( $record->refOrg, 0);
            $calendar->createEvent($record->formDate, $event_ref, $record->refOrg, $record->id, 0, $eventNote);      
      
     return $record->id;   
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
   
   /*
   'id', 'orgId', 'zakazId',  'action', 'contactFIO', 
            'nextContactDate', 'nextContactTime','contactEmail', 'contactPhone', 'note', 'status', 'nextdate',  'orgTitle',          
   
   */
   
   public $docList='';
   public $schetList='';
   public $initLead='';
   public $initLeadRef=0;
   public function getZakazRecord()        
   {     
     $zakazRecord = Yii::$app->db->createCommand(
            'SELECT {{%zakaz}}.id, {{%zakaz}}.formDate, {{%zakaz}}.isFormed, {{%zakaz}}.isActive, {{%orglist}}.title, {{%orglist}}.shortComment, {{%orglist}}.contactPhone, {{%orglist}}.contactEmail, {{%orglist}}.contactFIO, {{%zakaz}}.refOrg  
            from {{%zakaz}} left join {{%orglist}} on {{%orglist}}.id = {{%zakaz}}.refOrg where {{%zakaz}}.id =:zakazId', 
            [
            ':zakazId' => intval($this->zakazId),            
            ])->queryOne();
   
     if (empty($zakazRecord)) return $zakazRecord;
     
     if (empty($zakazRecord['formDate'])) $zakazRecord['formDate'] = date(Y-m-d);
     
        $this->orgId = intval($zakazRecord['refOrg']);
        $this->zakazId = intval($zakazRecord['id']);        
        $this->contactEmail = $zakazRecord['contactEmail'];
        $this->contactPhone= $zakazRecord['contactPhone'];
        $this->contactFIO= $zakazRecord['contactFIO'];
        
        if ($zakazRecord['isFormed'] == 1) $this->status = 1;
        elseif($zakazRecord['isActive'] == 0) $this->status = 2;
        else  $this->status = 3;
        
        $this->nextdate = date("d.m.Y", time()+60*60*24);
   
      $leadList = Yii::$app->db->createCommand(
            'SELECT note, id from {{%contact}} where 
            eventType = 20 and refZakaz =:zakazId ORDER BY contactDate LIMIT 1', 
            [
            ':zakazId' => intval($this->zakazId),            
            ])->queryAll();
      if (!empty($leadList)) {
          $this->initLead=$leadList[0]['note'];    
          $this->initLeadRef=$leadList[0]['id'];    
      }

      $this->schetList = Yii::$app->db->createCommand(
            'SELECT id, schetNum, schetSumm, schetDate, isReject, isSchetActive from {{%schet}} where 
            refZakaz =:zakazId ORDER BY id DESC', 
            [
            ':zakazId' => intval($this->zakazId),            
            ])->queryAll();

     if (!empty($this->zakazId)){  
     //$this->debug[]=$this->zakazId;
          $this->docList=$this->prepareDocList($this->zakazId);
     }
   return $zakazRecord;          
   }
   
    public function saveData()        
   {
   
   
       if ($this->orgId == -2)
          {
                $orgRecord   = new OrgList();                      
                $orgRecord -> title = trim($this->orgTitle);
                $orgRecord -> isOrgActive = 1;
                $orgRecord -> orgNote = 'создание заказа '.date("d.m.Y");
                $orgRecord -> source = 'sdelka proccessing';
                $orgRecord ->isFirstContact = 1;
                $orgRecord ->save();
                if (empty($orgRecord -> title) || $orgRecord -> title == "Создать автоматически") 
                {
                    $orgRecord -> title = "Организация ID=".$orgRecord ->id;
                    $orgRecord ->isFirstContactFinished = -1;
                    $orgRecord ->save();
                }    
                
        $this->orgId = $orgRecord ->id;
         }
   
      $orgRecord   = OrgList::findOne($this->orgId);
      $zakazRecord = ZakazList::findOne($this->zakazId);      
      $oldOrgRef = $zakazRecord->refOrg;
      $calendar = new MarketCalendarForm();  
    
      if (empty($orgRecord))  {$calendar->markRefEvent( $this->id, $this->zakazId); return;}
      if (empty($zakazRecord)){$calendar->markRefEvent( $this->id, $this->zakazId); return;}
      
      $curUser=Yii::$app->user->identity;
      
      $phoneCount = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%phones}} where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone' => $this->contactPhone,
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
      
      $phoneRef= $phoneRecord->id;
     
      $orgRecord ->contactDate = date("Y-m-d h:i:s");      
      $orgRecord ->nextContactDate =  date("Y-m-d", strtotime($this->nextdate));
      $orgRecord ->isInWork = 0;          
      
      switch ($this->status)
      {
        case 0:  
          /*Контакт не состоялся*/  
          $event_ref = 3;
          $eventNote = "Согласовть заявку";
          $orgRecord ->save();                                  
          $phoneRecord->save();
          return;
          break;
        case 1:  
          /*Заказ согласован*/  
          $orgRecord->isSchetReject = 0;                    
          $orgRecord->isSchetFinished = 0;
          $orgRecord->isInWork = 0;                    
 
          $zakazRecord ->isFormed = 1;
          /*$eventNote = "Резерв товара";
          $event_ref = 4;*/
          $zakazRecord->isGoodReserved = 1;
  
            $eventNote = "Зарезервировано. Выписать счет.";
            $event_ref = 5;
        
          break;
        case 2:  
          /*Отказ*/  
          $orgRecord ->isSchetReject = 1;          
          $orgRecord ->schetRejectDate = date("Y-m-d");          
          $orgRecord->nextContactDate = date("Y-m-d", time()+60*60*24*30);                
          $orgRecord ->isInWork = 0;          
          $zakazRecord ->isFormed = 0;
          $zakazRecord ->isActive = 0;
          
          /* Освободим резервирование */
           Yii::$app->db->createCommand(
            'UPDATE {{%otves_list}} SET inUse =0 WHERE refZakaz =:refZakaz ', 
            [
            ':refZakaz' => $this->zakazId,            
            ])->execute();          
            
          $eventNote = "Отказ по заявке";
          $event_ref = 8;
          break;
        case 3:  
          /*Контакт не завершен*/  
          $orgRecord ->isSchetReject = 0;                    
          $orgRecord ->isSchetFinished = 0;
          $orgRecord ->isInWork = 1;
          $zakazRecord ->isFormed = 0;        
          $eventNote = "Согласовать заявку";          
          $event_ref = 3;
          break;
      }
      
          $orgRecord ->ref_user = $curUser->id;                
          $orgRecord ->contactPhone = $this->contactPhone;
          $orgRecord ->contactEmail = $this->contactEmail;
          $orgRecord ->contactFIO = $this->contactFIO;      
          $orgRecord ->save();
          
          
          $zakazRecord -> refOrg = intval($this->orgId);
          $zakazRecord ->save();
          
          $phoneRecord->status = 1; /*помечаем телефон как надежный*/
          $phoneRecord->phoneContactFIO= $this->contactFIO;                
          $phoneRecord->save();
          
          $contact = new ContactList();
          $contact->ref_phone = $phoneRef;
          $contact->ref_org = $this->orgId;
          $contact->ref_user = $curUser->id;
          $contact->refZakaz =  $zakazRecord->id;
          $contact->contactDate = date("Y-m-d h:i:s");                    
          $contact->contactFIO = $this->contactFIO;
          $contact->note = $this->note;
          $contact->save();
                
         if ( $oldOrgRef != $zakazRecord -> refOrg)
         {  
            $calendar->markRefEvent( $oldOrgRef, $this->zakazId); //уберем отсылку к старому сочетанию
         }
         
        $calendar->createEventTime($this->nextContactDate, $this->nextContactTime, $event_ref, $orgRecord->id, 
        $this->zakazId, $contact->id, $eventNote, $contact->id);      

   }

   public function saveReserve()
   {
         $curUser=Yii::$app->user->identity;
      if ($this->resrveStatus < 1) {return;}
      $zakazRecord  = ZakazList::findOne($this->zakazId);
       if (empty($zakazRecord)){return;}
      $zakazRecord->isGoodReserved = 1;
      $zakazRecord->save();
  
      $eventNote = "Зарезервировано. Выписать счет.";
      $calendar = new MarketCalendarForm();
      $calendar->createEvent( date("Y-m-d") , 5, $zakazRecord->refOrg, $this->zakazId, 0, $eventNote);      
  }
  
   public function sendProposal($page)
   {
        $letter="<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
        $letter.=$page;
        $letter.="<pre>".$this->body."</pre>";
        $letter.="</body></html>";    
        
        $mailer = new MailForm ();
        $mailer ->orgId = $this->orgId;
        
     $fromEmail = $this->getCfgValue(1001);
     //"zakaz@rik-nsk.ru";
     //$fromEmail ="Y3su@rik-nsk.ru";
     $email = $this->email.",".$this->getCfgValue(1003);
     $sucess = $mailer->sendExtMail($email, $this->subject, $letter, $fromEmail, array());
     
     return $sucess;        
   }
   
   
   public function formRequestZakaz ()        
   {

    $zakazRecord = ZakazList::findOne($this->zakazId);   
    if (empty($zakazRecord)) return false;
    $orgRecord   = OrgList::findOne($zakazRecord ->refOrg);
    if (empty($orgRecord)) return false;

    $phoneList=$this->getCompanyPhones();
    $this->orgId =     $orgRecord->id;
    $curUser=Yii::$app->user->identity;
            
$detailList = Yii::$app->db->createCommand(
            'SELECT {{%zakazContent}}.id, {{%zakazContent}}.isActive, {{%zakaz}}.refOrg as orgId, {{%zakaz}}.id AS zakazId, 
            initialZakaz, good, spec, ed, value, count, dopRequest, dostavka  
            FROM   {{%zakazContent}}, {{%zakaz}}  where {{%zakazContent}}.refZakaz = {{%zakaz}}.id  
            AND  refZakaz=:refZakaz', 
        [':refZakaz' => $zakazRecord->id])->queryAll();                    
            
            
     //return $detailList;
     
    $this->subject="Коммерческое предложение ".$this->zakazId." от ". date("d-m-Y");
    
//     $page ="<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
     $page ="<div style='width:800px'>";
     $page .="<font style='size:14px'><b>Коммерческое предложение</b> ".$this->zakazId." от ". date("d-m-Y")."</font> <br>\n" ;
     $page .="<hr noshade>\n";          
     $page .="<table style='width:800px; border-width:0px; padding:5px;'>\n";
     $page .="<tr><td valign='top' style='padding:5px' width='100px'>Поставщик:</td><td style='padding:5px'>".$this->getCfgValue(108)."</td></tr>\n";
     $page .="<tr><td style='padding:5px' width='100px'>Покупатель:</td><td style='padding:5px'>".$orgRecord->title;
     if (!empty($orgRecord->schetINN)){$page .= " ИНН:".$orgRecord->schetINN;}
     $page .="</td></tr>\n";
     $page .=" </table>  <br>\n"; 
     
     $page .="<table border='1px' style='border-collapse: collapse; width:800px; border-width:1px; padding:5px;'>\n";     
     $page .="<tr>
     <td style='padding:3px'><b> № </b></td>
     <td style='padding:3px'>Товары (работы, услуги)</td> 
     <td style='padding:3px'>Кол-во </td> 
     <td style='padding:3px'>Цена</td>
     <td style='padding:3px'>Ед.</td>
     <td style='padding:3px'>Сумма</td>
     </tr>\n";
    
    $sum=0;
    for ($i=0; $i<count($detailList);$i++ )
    {
        if ($detailList[$i]['isActive'] == 0) {continue;}
        $page .="<tr>\n";
        $page.="<td style=padding:3px'>".($i+1)."</td>\n";
        $page.="<td style=padding:3px'>".$detailList[$i]['good']."</td>\n";
        $page.="<td style=padding:3px'>".$detailList[$i]['count']."</td>\n";
        $page.="<td style=padding:3px'>".$detailList[$i]['ed']."</td>\n";
        $page.="<td style=padding:3px'>".$detailList[$i]['value']."</td>\n";
        $page.="<td style=padding:3px'>".$detailList[$i]['count']*$detailList[$i]['value']."</td>\n";
        $page.="</tr>\n";
        $sum+=$detailList[$i]['count']*$detailList[$i]['value'];
    }
      $page.=" </table> \n";      
     $page.="<div style='text-align:right;'>Итого: ".$sum." руб </div>\n";     
     $page.="</div>";
//</body></html>"; 
     
    return $page;
   }    
   

   /*************************************/
    public function prepareDocList($zakazId)
   {
   $query  = new Query();
   $query->select ([
            '{{%documents}}.id',
            'docIntNum',
            'docUri',
            'docOrigNum',
            'docOrigDate',
            'docTitle',
            'docClassifyRef',
            'orgTitle',
            '{{%doc_classify}}.docType'
            ])
            ->from("{{%documents}}")
            ->leftJoin("{{%doc_classify}}","{{%doc_classify}}.id = {{%documents}}.docClassifyRef")
            ->leftJoin("{{%doc_zakaz_lnk}}","{{%documents}}.id = {{%doc_zakaz_lnk}}.refDoc");
            
    $query->andWhere(['=', '{{%doc_zakaz_lnk}}.refZakaz', $zakazId]);    
    $list = $query->createCommand()->queryAll(); 
    
    $val = "<br><ul>";
     for ($i=0;$i< count($list);$i++)
     {

       if (empty($list[$i]['docClassifyRef'])) $v = $list[$i]['docTitle'];
                                          else $v = $list[$i]['docType'];
        $val .= "<li> <span class='clickable' ";
        $val .= " onclick='window.open(\"".$list[$i]['docUri']."\", "; 
        $val .= "\"docWin\",\"toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900\");'>";                                  
        $val .= $v." ".$list[$i]['docOrigNum'];   
        $val .= " ".$list[$i]['orgTitle']."</span> ";
        $val .= "<span onclick='removeDoc(".$list[$i]['id'].")' style='float:right;color:Brown;'  class='glyphicon glyphicon-remove-circle clickable'></span>";
        $val .= "</li>";
     }
     
     $val .= "</ul>";
    
   return $val;   
   }
   
    public function addDocToZakaz($zakazId, $docId)
    {
       $res = [ 'res' => false, 
             'zakazId'  => $zakazId, 
             'docId' => $docId, 
             'val' => '',
           ];   
    
       $record = new TblDocZakazLnk();
       if (empty ($record)) return $res;
       $record ->refZakaz =  $zakazId;
       $record ->refDoc =  $docId;
       $record ->save();
    $res['res'] = true;
    $res['val'] = $this->prepareDocList($zakazId);       
    return $res;       
   }

    public function rmDocToZakaz($zakazId, $docId)
    {
       $res = [ 'res' => false, 
             'zakazId'  => $zakazId, 
             'docId' => $docId, 
             'val' => '',
           ];   
    
       $record = TblDocZakazLnk::findOne([
       'refZakaz' =>$zakazId, 
       'refDoc'=> $docId, 
       ]);
       if (empty ($record)) return $res;
       $record ->delete();
    $res['res'] = true;
    $res['val'] = $this->prepareDocList($zakazId);       
    return $res;       
   }

  public function getOrgInfo($orgId)        
   {
       $res = [ 'res' => false, 
             'orgId'  => $orgId, 
             'contactPhone' =>'',
             'contactFIO' =>'',
             'contactEmail' =>'',
           ];   
     $record = OrgList::findOne($orgId);
     if (empty($record)) return $res;
     $res['contactPhone'] = $record ->contactPhone;
     $res['contactFIO'] = $record ->contactFIO;
     $res['contactEmail'] = $record ->contactEmail;
     $res['res'] = true;
   
    return $res;
  } 




/****************************/

}
