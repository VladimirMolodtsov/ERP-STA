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
/**
 * ManagerActivity  - модель активности менеджеров
 */
class ManagerActivityForm extends Model
{
   
   public $detail=0;
  
    
   public $orgTitle="";
   public $userFIO="";
   
 
   public $monthShift=0;
   public $format='html'; 
   
   public $command;    
   public $count=0;
   public $dataArray=array();  

   public $userId = 0;       
   public $month = 0;       
   public $year = 0;       
   public $day = 0;       
 
   public $period=60; 
 

   public $debug=[];
  
   public function rules()
   {
        return [
            [['period'], 'default'],
            [['orgTitle','userFIO', ], 'safe'],

        ];
    }
    
    
/************************************/   
/*********  Complex Manager Activity ***************/   
/************************************/
   
 public function fixDate ($src)
 {
   $res=
   [
     'm' => $src['m'],
     'y' => $src['y'],
   ];
   
  // $this->debug[] = $res;
   while ($res['m'] <=0)
    {
       $res['m']+=12; 
       $res['y']--;
    }
   return $res;
 } 
   
   public function getManagerActivityData ($params)		
   {
        $this->prepareManagerActivityData($params);		

        $dataList=$this->dataArray;
     
    
    
    $mask = realpath(dirname(__FILE__))."/../uploads/headManagerActivityReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headManagerActivityReport".time().".csv";

    
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
   
   public function getManagerActivityProvider($params)		
   {

        $this->prepareManagerActivityData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id', 
            'userFIO',
            'schetN', 
            'schetS',
            'oplateS',
            'contactN',
            'inZakaz',
            'inLead',
            'inContact',
            ],
			
            'defaultOrder' => [    'userFIO' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
  
  /*****************************/
  /*****************************/

  
   public function getManagerRecord($userId)	
   {
       
       return UserList::FindOne($userId);
   }

 public function prepareManagerDayActivityData()
   {

    /*определим месяц на который мы выводим отчет*/	    
    $cur = $this->fixDate (['m' => (date('n')-$this->monthShift),   'y' => date('Y')]);
    
    $nDay = date('t', mktime(0, 0, 0, $cur['m'], 1, $cur['y']));
   
  
  /*список пользователей*/
      /**/
    $list= Yii::$app->db->createCommand('SELECT id, userFIO FROM {{%user}} 
    where roleFlg & (0x0002|0x0004|0x0080) 
    ORDER BY userFIO' )
    ->queryAll();    

    
    
    for ($i=0; $i<count($list); $i++)
    {
      $id= $list[$i]['id'];    
      $userActivity = $this->prepareUserActivityArray($id);
      $activityDayList[$i]['id']= $id;
      $activityDayList[$i]['userFIO']= $list[$i]['userFIO'];    
      $activityDayList[$i]['S'] = 0;
      for($j=1; $j<= $nDay; $j++)
      {
   //       $activityDayList[$i]['S'] +=$userActivity[$j]['allActivity'];
          $activityDayList[$i][$j]=$userActivity[$j]['allActivity'];
      }
    }  
   
     return $activityDayList;
   }
   


public function prepareManagerActivityData($params)
   {
    
       
    /*список пользователей*/
    /**/
    $this->dataArray= Yii::$app->db->createCommand('SELECT id, userFIO FROM {{%user}} 
    where  roleFlg & (0x0002|0x0004|0x0080)
      ORDER BY userFIO' )    
    ->queryAll();    

  
    $cur = $this->fixDate (['m' => (date('n')-$this->monthShift),   'y' => date('Y')]);
    $m=$cur['m'];
    $pr1 = $this->fixDate (['m' => (date('n')-$this->monthShift-1), 'y' => date('Y')]);
    $pr2 = $this->fixDate (['m' => (date('n')-$this->monthShift-2), 'y' => date('Y')]);
    $dayInMonth=date("t", strtotime($cur['y']."-".$cur['m']));               
    $nDay = date('t', mktime(0, 0, 0, $cur['m'], 1, $cur['y']));
    
    for ($i=0; $i<count($this->dataArray); $i++)
    {
      $id= $this->dataArray[$i]['id'];    
      $userActivity = $this->prepareUserActivityArray($id);
      
     $listArray = array();
     $list= Yii::$app->db->createCommand("Select DISTINCT ref_org from {{%contact}} 
     where month(contactDate)=".$cur['m']." and year(contactDate)=".$cur['y']."  and ref_user = ".$id )->queryAll();    
     for ($j=0; $j< count($list); $j++)$listArray[] = $list[$j]['ref_org'];
           
     $list= Yii::$app->db->createCommand("Select DISTINCT refOrg from {{%zakaz}} 
     where month(formDate)=".$cur['m']." and year(formDate)=".$cur['y']."  and ref_user = ".$id )->queryAll();    
     for ($j=0; $j< count($list); $j++)$listArray[] = $list[$j]['refOrg'];     

     $list= Yii::$app->db->createCommand("Select DISTINCT refOrg from {{%schet}}
     where month(schetDate)=".$cur['m']." and year(schetDate)=".$cur['y']."  and refManager = ".$id )->queryAll();    
     for ($j=0; $j< count($list); $j++)$listArray[] = $list[$j]['refOrg'];     

     
     $this->dataArray[$i]['uniqClient'] = count( array_unique ($listArray) );
      
      $this->dataArray[$i]['allActivity']  = $userActivity[$m+$dayInMonth]['allActivity'];
      $this->dataArray[$i]['inLead']       = $userActivity[$m+$dayInMonth]['leadsReg']/*+$userActivity[$m+$dayInMonth]['leadsProcessed']*/;
      $this->dataArray[$i]['inContact']    = $userActivity[$m+$dayInMonth]['contactsEmpty'];
      $this->dataArray[$i]['inDeals']      = $userActivity[$m+$dayInMonth]['allActivity']-$this->dataArray[$i]['inLead'] - $this->dataArray[$i]['inContact'];
      $this->dataArray[$i]['oplateS']      = $userActivity[$m+$dayInMonth]['oplataSum'];
      $this->dataArray[$i]['schetS']       = $userActivity[$m+$dayInMonth]['schetSum']; 
      $this->dataArray[$i]['schetN']       = $userActivity[$m+$dayInMonth]['schetNew']; 
      
      $this->dataArray[$i]['month'][0]     = $userActivity[$pr2['m']+$dayInMonth]['allActivity']; 
      $this->dataArray[$i]['month'][1]     = $userActivity[$pr1['m']+$dayInMonth]['allActivity']; 
      $this->dataArray[$i]['month'][2]     = $userActivity[$m+$dayInMonth]['allActivity'];
       
      
      
       if ($this->dataArray[$i]['allActivity'] > 0) 
           {
           $this->dataArray[$i]['efficient']  =   $this->dataArray[$i]['oplateS']/$this->dataArray[$i]['allActivity'];
           
           $this->dataArray[$i]['inLeadP'] = 100*$this->dataArray[$i]['inLead']/$this->dataArray[$i]['allActivity'];
           
           $this->dataArray[$i]['inContactP'] =(100*$this->dataArray[$i]['inContact'])/$this->dataArray[$i]['allActivity'];
           
           $this->dataArray[$i]['inZakazP'] = 100*$this->dataArray[$i]['inDeals']/$this->dataArray[$i]['allActivity'];
           
           $this->dataArray[$i]['contatToSchetP'] = 100*$this->dataArray[$i]['schetN']/$this->dataArray[$i]['allActivity'];
           }
           else
           {
            $this->dataArray[$i]['efficient']  = 0;
            $this->dataArray[$i]['inZakazP'] = 0;
            $this->dataArray[$i]['inLeadP'] = 0;
            $this->dataArray[$i]['inContactP'] = 0;
            $this->dataArray[$i]['contatToSchetP'] =0;
           }
       
       if ($this->dataArray[$i]['schetS'] > 0) 
       {
           $this->dataArray[$i]['schetToOplataP'] =100*$this->dataArray[$i]['oplateS']/$this->dataArray[$i]['schetS'];
       }
       else
       {
           $this->dataArray[$i]['schetToOplataP'] =0;
       }
        
       if ($this->dataArray[$i]['schetN'] > 0) 
       {
           $this->dataArray[$i]['mediumSchet'] =$this->dataArray[$i]['schetS']/$this->dataArray[$i]['schetN'];
       }
       else
       {
           $this->dataArray[$i]['mediumSchet']  =0;
       }
      
    
    }
    
    
    //$this->debug[] = $cur;
	
  }
   
   /*****************************/
   public function prepareUserActivityArray($userId)		
   {       
       $activityList = array();


       $cur = $this->fixDate (['m' => (date('n')-$this->monthShift),   'y' => date('Y')]);

       $curM = $cur["m"];
       $curY = $cur["y"];
       
       $prev = $this->fixDate (['m' => $curM-2,   'y' => $curY ]);

       $dayInMonth=date("t", strtotime($curY."-".$curM));              
       
       if ($this->monthShift == 0) $curD = date ("d");
       else $curD = $dayInMonth;

       
       
       /*Зануляем*/
       for ($i=1;$i<=$dayInMonth+12; $i++)
       {
         $dataArray=array();
         $dataArray['allActivity']=0; //
         $dataArray['allContacts']=0; //
         $dataArray['leadsReg']=0;  //
         $dataArray['leadsProcessed']=0; //
         $dataArray['contactsEmpty']=0; //
         $dataArray['zakazNew']=0;  //
         $dataArray['zakazWork']=0; //
         $dataArray['schetNew']=0;  //
         $dataArray['oplatNum']=0;  //
         $dataArray['supplyNum']=0; //          
         $dataArray['requestNum']=0; //           
         $dataArray['schetFinit']=0;           
         $dataArray['schetSum']=0;  //          
         $dataArray['oplataSum']=0; //          
         $dataArray['supplySum']=0; //          
         $dataArray['clientNum']=0;          
         
         $activityList[$i]=$dataArray;  
       }
       
       /*По всем дням текущего месяца, будущие события нам не интересны*/

       /* Контакты */       
       $strCount = "Select count(id) as C, ifnull(eventType,0) as eType, day(contactDate) as contactDay, if(refZakaz=0,0,1) as inZakaz 
                    from {{%contact}} where month(contactDate)=".$curM." and year(contactDate)=".$curY." and 
                    ref_user = ".$userId." GROUP BY eType, contactDay, inZakaz";        
                    
                    
//$this->debug[]= $strCount;
       $contactList = Yii::$app->db->createCommand($strCount)->queryAll();        
              
       for ($i=0;$i<count($contactList); $i++ )
       {
          $d = $contactList[$i]['contactDay'];
          /*Всего лидов сегодня*/
          if ($contactList[$i]['eType']>=10) $activityList[$d]['leadsReg']+=$contactList[$i]['C'];
          /*Из них расшифровано*/
          if ($contactList[$i]['eType']>10) $activityList[$d]['leadsProcessed']+=$contactList[$i]['C'];
          /*Контакты - не лиды*/
          if ($contactList[$i]['eType']<10)
          {
            /*Контакты без заявок*/  
            if ($contactList[$i]['inZakaz']==0) $activityList[$d]['contactsEmpty']+=$contactList[$i]['C'];  
            /*Контакты связаные с заявками*/  
            if ($contactList[$i]['inZakaz']==1) $activityList[$d]['zakazWork']+=$contactList[$i]['C'];  
          }
          $activityList[$d]['allContacts'] += $contactList[$i]['C'];   
          $activityList[$d]['allActivity']+= $contactList[$i]['C'];  
       }

       
       /*По месяцам*/
       $from = "STR_TO_DATE('01,".$prev["m"].",".$prev["y"]."','%d,%m,%Y')";
       $to   = "STR_TO_DATE('".$dayInMonth.",".$cur["m"].",".$cur["y"]."','%d,%m,%Y')";
       
       $strCount = "Select count(id) as C, ifnull(eventType,0) as eType, MONTH(contactDate) as contactMonth, if(refZakaz=0,0,1) as inZakaz 
                    from {{%contact}} where 
                    DATE(contactDate) >= ".$from." and DATE(contactDate) <=  ".$to." 
                    and ref_user = ".$userId." GROUP BY eType, contactMonth, inZakaz";        
       $contactList = Yii::$app->db->createCommand($strCount)->queryAll();        
//$this->debug[]=$contactList;              
       for ($i=0;$i<count($contactList); $i++ )
       {
          $d = $contactList[$i]['contactMonth']+$dayInMonth;
          /*Всего лидов сегодня*/
          if ($contactList[$i]['eType']>=10) $activityList[$d]['leadsReg']+=$contactList[$i]['C'];
          /*Из них расшифровано*/
          if ($contactList[$i]['eType']>10) $activityList[$d]['leadsProcessed']+=$contactList[$i]['C'];
          /*Контакты - не лиды*/
          if ($contactList[$i]['eType']<10)
          {
            /*Контакты без заявок*/  
            if ($contactList[$i]['inZakaz']==0) $activityList[$d]['contactsEmpty']+=$contactList[$i]['C'];  
            /*Контакты связаные с заявками*/  
            if ($contactList[$i]['inZakaz']==1) $activityList[$d]['zakazWork']+=$contactList[$i]['C'];  
          }
          $activityList[$d]['allContacts'] += $contactList[$i]['C'];    
          $activityList[$d]['allActivity']+= $contactList[$i]['C'];  
       }
       
      
       
       
       /* Заказы */       
       $strCount= "select count(id) as C, day(formDate) as zakazDay from {{%zakaz}} where month(formDate)=".$curM." and 
                   year(formDate)=".$curY." and ref_user = ".$userId." GROUP BY zakazDay";
       $zakazList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($zakazList); $i++ )
       {
          $d = $zakazList[$i]['zakazDay'];
          /*Создано заказов сегодня*/
          $activityList[$d]['zakazNew']+=$zakazList[$i]['C'];
          $activityList[$d]['allActivity']+= $zakazList[$i]['C'];  
       }

       /*По месяцам*/
       $strCount= "select count(id) as C, MONTH(formDate) as zakazMonth from {{%zakaz}} where                     
                   DATE(formDate) >= ".$from." and DATE(formDate) <=  ".$to." 
                   and ref_user = ".$userId." GROUP BY zakazMonth";
       $zakazList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($zakazList); $i++ )
       {
          $d = $zakazList[$i]['zakazMonth']+$dayInMonth;
          /*Создано заказов сегодня*/
          $activityList[$d]['zakazNew']+=$zakazList[$i]['C'];
          $activityList[$d]['allActivity']+= $zakazList[$i]['C'];  
       }

       
       
       /* Счета */       
       $strCount= "select count(id) as C, sum(schetSumm) as S, day(schetDate) as schetDay from {{%schet}} where month(schetDate)=".$curM." and 
                   year(schetDate)=".$curY." and refManager = ".$userId."  GROUP BY schetDay";
       $schetList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($schetList); $i++ )
       {
          $d = $schetList[$i]['schetDay'];
          /*Создано счетов сегодня*/
          $activityList[$d]['schetNew']+=$schetList[$i]['C'];
          $activityList[$d]['schetSum']+=$schetList[$i]['S'];
          $activityList[$d]['allActivity']+= $schetList[$i]['C'];
       }
 




 /*По месяцам*/
       $strCount= "select count(id) as C, sum(schetSumm) as S, MONTH(schetDate) as schetMonth from {{%schet}} where  
                   DATE(schetDate) >= ".$from." and DATE(schetDate) <=  ".$to." 
                   and refManager = ".$userId."  GROUP BY schetMonth";
       $schetList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($schetList); $i++ )
       {
          $d = $schetList[$i]['schetMonth']+$dayInMonth;
          /*Создано счетов сегодня*/
          $activityList[$d]['schetNew']+=$schetList[$i]['C'];
          $activityList[$d]['schetSum']+=$schetList[$i]['S'];
          $activityList[$d]['allActivity']+= $schetList[$i]['C'];
       }

              
       
       /* Оплаты */       
       $strCount= "select count({{%oplata}}.id) as C, sum(oplateSumm) as S, day(oplateDate) as oplateDay 
                  from {{%oplata}} left join {{%schet}} on {{%oplata}}.refSchet = {{%schet}}.id
                   where month(oplateDate)=".$curM." and year(oplateDate)=".$curY." and {{%schet}}.refManager = ".$userId." GROUP BY oplateDay";
       $oplateList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($oplateList); $i++ )
       {
          $d = $oplateList[$i]['oplateDay'];
          /*Создано оплат сегодня*/
          $activityList[$d]['oplatNum']+=$oplateList[$i]['C'];
          $activityList[$d]['oplataSum']+=$oplateList[$i]['S'];
          $activityList[$d]['allActivity']+= $oplateList[$i]['C'];
       }
        /*По месяцам*/
       $strCount= "select count({{%oplata}}.id) as C, sum(oplateSumm) as S, MONTH(oplateDate) as oplateMonth 
                  from {{%oplata}} left join {{%schet}} on {{%oplata}}.refSchet = {{%schet}}.id
                   where 
                   DATE(oplateDate) >= ".$from." and DATE(oplateDate) <=  ".$to." 
                   and {{%schet}}.refManager = ".$userId." GROUP BY oplateMonth";
       $oplateList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($oplateList); $i++ )
       {
          $d = $oplateList[$i]['oplateMonth']+$dayInMonth;
          /*Создано оплат сегодня*/
          $activityList[$d]['oplatNum']+=$oplateList[$i]['C'];
          $activityList[$d]['oplataSum']+=$oplateList[$i]['S'];
          $activityList[$d]['allActivity']+= $oplateList[$i]['C'];
       }

              
       /* Поставки */       
       $strCount= "select count({{%supply}}.id) as C, sum(supplySumm) as S, day(supplyDate) as supplyDay 
                   from {{%supply}} left join {{%schet}} on {{%supply}}.refSchet = {{%schet}}.id
                    where month(supplyDate)=".$curM." and year(supplyDate)=".$curY." and {{%schet}}.refManager = ".$userId." GROUP BY supplyDay";
       $supplyList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($supplyList); $i++ )
       {
          $d = $supplyList[$i]['supplyDay'];
          /*Создано поставок сегодня*/
          $activityList[$d]['supplyNum']+=$supplyList[$i]['C'];
          $activityList[$d]['supplySum']+=$supplyList[$i]['S'];
          $activityList[$d]['allActivity']+= $supplyList[$i]['C'];
       }
       /*По месяцам*/
       $strCount= "select count({{%supply}}.id) as C, sum(supplySumm) as S, MONTH(supplyDate) as supplyMonth 
                   from {{%supply}} left join {{%schet}} on {{%supply}}.refSchet = {{%schet}}.id
                    where  
                    DATE(supplyDate) >= ".$from." and DATE(supplyDate) <=  ".$to." 
                    and {{%schet}}.refManager = ".$userId." GROUP BY supplyMonth";
       $supplyList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($supplyList); $i++ )
       {
          $d = $supplyList[$i]['supplyMonth']+$dayInMonth;
          /*Создано поставок сегодня*/
          $activityList[$d]['supplyNum']+=$supplyList[$i]['C'];
          $activityList[$d]['supplySum']+=$supplyList[$i]['S'];
          $activityList[$d]['allActivity']+= $supplyList[$i]['C'];
       }


              
              
       /* Заявки на доставку */       
       $strCount= "select count({{%request_supply}}.id) as C, day(requestDate) as requestDay 
                    from {{%request_supply}}  left join {{%schet}} on {{%request_supply}}.refSchet = {{%schet}}.id
                    where month(requestDate)=".$curM." and year(requestDate)=".$curY." and {{%schet}}.refManager = ".$userId." GROUP BY requestDay ";
       $requestList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($requestList); $i++ )
       {
          $d = $requestList[$i]['requestDay'];
          /*Создано заявок сегодня*/
          $activityList[$d]['requestNum']+=$requestList[$i]['C'];
          $activityList[$d]['allActivity']+= $requestList[$i]['C'];          
       }
              /*По месяцам*/
       $strCount= "select count({{%request_supply}}.id) as C, MONTH(requestDate) as requestMonth 
                    from {{%request_supply}}  left join {{%schet}} on {{%request_supply}}.refSchet = {{%schet}}.id
                    where 
                    DATE(requestDate) >= ".$from." and DATE(requestDate) <=  ".$to." 
                    and {{%schet}}.refManager = ".$userId." GROUP BY requestMonth ";
       $requestList  =  Yii::$app->db->createCommand($strCount)->queryAll();        

       for ($i=0;$i<count($requestList); $i++ )
       {
          $d = $requestList[$i]['requestMonth']+$dayInMonth;
          /*Создано заявок сегодня*/
          $activityList[$d]['requestNum']+=$requestList[$i]['C'];
          $activityList[$d]['allActivity']+= $requestList[$i]['C'];          
       }

       
      /*Уникальные клиенты*/ 
     $list= Yii::$app->db->createCommand(" SELECT COUNT(DISTINCT org) as C, M, D
     FROM      
     (
     Select ref_org as org, MONTH(contactDate) as M, day(contactDate) as D from {{%contact}}   
     where MONTH(contactDate) = ".$cur['m']." AND year(contactDate)=".$cur['y']."  and ref_user = ".$userId."
     UNION
     Select  refOrg as org, MONTH(formDate)    as M, day(formDate)    as D from {{%zakaz}} 
     where MONTH(formDate) = ".$cur['m']." AND year(formDate)=".$cur['y']."  and ref_user = ".$userId."
     UNION
     Select  refOrg as org, MONTH(schetDate)   as M, day(schetDate)   as D from {{%schet}} 
     where MONTH(schetDate) = ".$cur['m']." AND year(schetDate)=".$cur['y']."  and refManager = ".$userId."
     ) as a
     group by M, D
     "
     )->queryAll();    
      
     for ($j=0; $j< count($list); $j++)
     {
         $d = $list[$j]['D'];
         $activityList[$d]['clientNum']+=$list[$j]['C'];
     }
          
             
             /*По месяцам*/      
     $list= Yii::$app->db->createCommand(" SELECT COUNT(DISTINCT org) as C, M
     FROM      
     (
     Select ref_org as org, MONTH(contactDate) as M from {{%contact}}   where 
     DATE(contactDate) >= ".$from." and DATE(contactDate) <=  ".$to." 
     and ref_user = ".$userId."
     UNION
     Select refOrg as org, MONTH(formDate) as M  from {{%zakaz}} where  
     DATE(formDate) >= ".$from." and DATE(formDate) <=  ".$to." 
     and ref_user = ".$userId."
     UNION
     Select refOrg as org, MONTH(schetDate) as M from {{%schet}} where  
     DATE(schetDate) >= ".$from." and DATE(schetDate) <=  ".$to." 
     and refManager = ".$userId."
     ) as a
     group by M
     "
     )->queryAll();    
     for ($j=0; $j< count($list); $j++)
     {
         $d = $list[$j]['M']+$dayInMonth;
         $activityList[$d]['clientNum']+=$list[$j]['C'];
     }
                 
        return $activityList;
   }   

public function getTotalSum()
{
   $res=array(); 

   if ($this->year  == 0)$this->year = date('Y');
   if ($this->month == 0)$this->month = date('n');
   
   $strCount= "select sum(oplateSumm) as S from {{%oplata}} 
                   where month(oplateDate)=".$this->month." and year(oplateDate)=".$this->year;
                   
   $res['oplata'] =  Yii::$app->db->createCommand($strCount)->queryScalar();
       
 return $res;    
}
   
   
public function getUserActivityArray($userId)		
   {       

      $activityList = $this->prepareUserActivityArray($userId);
   
       $cur = $this->fixDate (['m' => (date('n')-$this->monthShift),   'y' => date('Y')]);

       $curM = $cur["m"];
       $curY = $cur["y"];
       $dayInMonth=date("t", strtotime($curY."-".$curM));         
       
       for ($i=1;$i<=$dayInMonth+12; $i++)
       {
         
         if ($activityList[$i]['allActivity']==0) $activityList[$i]['allActivity'] = "&nbsp;";
         if ($activityList[$i]['leadsReg']==0) $activityList[$i]['leadsReg'] = "&nbsp;";
         if ($activityList[$i]['leadsProcessed']==0) $activityList[$i]['leadsProcessed'] = "&nbsp;";
         if ($activityList[$i]['contactsEmpty']==0) $activityList[$i]['contactsEmpty'] = "&nbsp;";
         if ($activityList[$i]['zakazNew']==0) $activityList[$i]['zakazNew'] = "&nbsp;";
         if ($activityList[$i]['zakazWork']==0) $activityList[$i]['zakazWork'] = "&nbsp;";
         if ($activityList[$i]['schetNew']==0) $activityList[$i]['schetNew'] = "&nbsp;";
         if ($activityList[$i]['oplatNum']==0) $activityList[$i]['oplatNum'] = "&nbsp;";
         if ($activityList[$i]['supplyNum']==0) $activityList[$i]['supplyNum'] = "&nbsp;";
         if ($activityList[$i]['requestNum']==0) $activityList[$i]['requestNum'] = "&nbsp;";
         if ($activityList[$i]['schetFinit']==0) $activityList[$i]['schetFinit'] = "&nbsp;";
         if ($activityList[$i]['schetSum']==0) $activityList[$i]['schetSum'] = "&nbsp;";
                                            else $activityList[$i]['schetSum'] = number_format($activityList[$i]['schetSum']/1000,0,'.','&nbsp;');
         if ($activityList[$i]['oplataSum']==0) $activityList[$i]['oplataSum'] = "&nbsp;";
                                            else $activityList[$i]['oplataSum'] = number_format($activityList[$i]['oplataSum']/1000,0,'.','&nbsp;');
         if ($activityList[$i]['supplySum']==0) $activityList[$i]['supplySum'] = "&nbsp;";
                                           else $activityList[$i]['supplySum'] = number_format($activityList[$i]['supplySum']/1000,0,'.','&nbsp;');
         if ($activityList[$i]['clientNum']==0) $activityList[$i]['clientNum'] = "&nbsp;";
         
         
       }


        return $activityList;
   }   
   /*************************************/
   

/****************************************/   
/*********  Details ******************/   
/****************************************/

/********************/		
/* Список оплат */	

public function prepareOplataListData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%oplata}}.id)")
                  ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  
                 ;
                  
     $query->select([ '{{%oplata}}.id', 'oplateDate',  'oplateSumm', 'orgTitle', '{{%oplata}}.oplateNum', '{{%oplata}}.orgINN', '{{%oplata}}.orgKPP'	 ])
                   ->from("{{%oplata}}")
                  ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%oplata}}.refOrg")
                  ->leftJoin("{{%schet}}", "{{%schet}}.id = {{%oplata}}.refSchet")
                  ->distinct
                  ;
                  
                  
      if ($this->userId > 0){
        $countquery->andWhere('{{%schet}}.refManager = '.$this->userId);
             $query->andWhere('{{%schet}}.refManager = '.$this->userId);
      }

      if ($this->year == 0){$this->year = date('Y');}
        $countquery->andWhere('YEAR(oplateDate) = '.$this->year);
             $query->andWhere('YEAR(oplateDate) = '.$this->year);
      
      if ($this->month == 0){$this->month = date('n'); }
        $countquery->andWhere('MONTH(oplateDate) = '.$this->month);
             $query->andWhere('MONTH(oplateDate) = '.$this->month);
      
      if ($this->day > 0){
        $countquery->andWhere('DAY(oplateDate) = '.$this->day);
             $query->andWhere('DAY(oplateDate) = '.$this->day);
      }



      
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }
    
    
    public function getOplataListData($params)
    {        
        $this->prepareOplataListData($params);    
        $dataList=$this->command->queryAll();
   
    $mask = realpath(dirname(__FILE__))."/../uploads/orgActivityData*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/orgActivityData".time().".csv";

    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Сумма"),
        iconv("UTF-8", "Windows-1251","Дата платежа"),
        iconv("UTF-8", "Windows-1251","Номер платежа"),
        iconv("UTF-8", "Windows-1251","Плательщик"),        
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
            'orgKPP'     
            ],
            'defaultOrder' => ['oplateDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   


/********************/		
/* Список контрагентов */	

public function prepareOrgActivityData($params)
   {

      if ($this->userId == 0){$curUser=Yii::$app->user->identity; $this->userId = $curUser->id;}
      
      if ($this->year == 0){$this->year = date('Y');}      
      if ($this->month == 0){$this->month = date('n'); }

      $y = $this->year;
      $m = $this->month;
      $u = $this->userId;

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%orglist}}.id)")
                  ->from("{{%orglist}}")
                  ->leftJoin("(SELECT count(id) as C1, ref_org from {{%contact}}  where ref_user = ".$u." and year(contactDate)=".$y." And month(contactDate)=".$m." group by ref_org) as a", "{{%orglist}}.id = a.ref_org")
                  ->leftJoin("(SELECT count(id) as Z1, refOrg from {{%zakaz}}  where ref_user = ".$u." and year(formDate)=".$y." And month(formDate)=".$m." group by refOrg) as b", "{{%orglist}}.id = b.refOrg")                  
                  ->leftJoin("(SELECT count(id) as S1, refOrg from {{%schet}}  where refManager = ".$u." and year(schetDate)=".$y." And month(schetDate)=".$m." group by refOrg) as с", "{{%orglist}}.id = с.refOrg")                  
                  ->where ("((ifnull(C1,0)+ifnull(Z1,0)+ifnull(S1,0)) >0)")
                 ;
                  
     $query->select([ '{{%orglist}}.id', 'title as orgTitle',  'ifnull(C1,0) as C', 'ifnull(Z1,0) as Z', 'ifnull(S1,0) as S', ])
                   ->from("{{%orglist}}")
                  ->leftJoin("(SELECT count(id) as C1, ref_org from {{%contact}}  where ref_user = ".$u." and year(contactDate)=".$y." And month(contactDate)=".$m." group by ref_org) as a", "{{%orglist}}.id = a.ref_org")
                  ->leftJoin("(SELECT count(id) as Z1, refOrg from {{%zakaz}}  where ref_user = ".$u." and year(formDate)=".$y." And month(formDate)=".$m." group by refOrg) as b", "{{%orglist}}.id = b.refOrg")                  
                  ->leftJoin("(SELECT count(id) as S1, refOrg from {{%schet}}  where refManager = ".$u." and year(schetDate)=".$y." And month(schetDate)=".$m." group by refOrg) as с", "{{%orglist}}.id = с.refOrg")                  
                  ->where ("((ifnull(C1,0)+ifnull(Z1,0)+ifnull(S1,0)) >0)")
                  ->distinct
                  ;

      
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }


    public function getOrgActivityData($params)
    {        
        $this->prepareOrgActivityData($params);    
        $dataList=$this->command->queryAll();
 
    $mask = realpath(dirname(__FILE__))."/../uploads/orgActivity*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/orgActivity".time().".csv";

    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
        			
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Число контактов"),
        iconv("UTF-8", "Windows-1251","Число заказов"),
        iconv("UTF-8", "Windows-1251","Число счетов"),        
 
        );
        fputcsv($fp, $col_title, ";"); 

    for ($i=0; $i< count($dataList); $i++)
    {        

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['C']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['Z']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['S']),   
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
public function getOrgActivityProvider($params)
   {

        $this->prepareOrgActivityData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'id',  
            'C',  
            'S', 
            'orgTitle', 
            'Z', 
            ],
            'defaultOrder' => ['orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   



/********************/		
/* Список счетов */	

public function prepareSchetActivityData($params)
   {

      if ($this->userId == 0){$curUser=Yii::$app->user->identity; $this->userId = $curUser->id;}
      
      if ($this->year == 0){$this->year = date('Y');}      
      if ($this->month == 0){$this->month = date('n'); }

      $y = $this->year;
      $m = $this->month;
      $u = $this->userId;

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count({{%schet}}.id)")
                  ->from("{{%orglist}},  {{%schet}} ")
                  ->where ("{{%schet}}.refManager = ".$u." and year({{%schet}}.schetDate)=".$y." And month({{%schet}}.schetDate)=".$m." and {{%orglist}}.id = {{%schet}}.refOrg")
                 ;
                  
     $query->select([ '{{%orglist}}.id as orgId', 'title as orgTitle', '{{%schet}}.schetNum', '{{%schet}}.id as schetId', '{{%schet}}.schetDate', 'ref1C', 'schetSumm', 'summOplata', 'summSupply' ])
                  ->from("{{%orglist}},  {{%schet}} ")
                  ->where ("{{%schet}}.refManager = ".$u." and year({{%schet}}.schetDate)=".$y." And month({{%schet}}.schetDate)=".$m." and {{%orglist}}.id = {{%schet}}.refOrg")
                  ;

      
     if (($this->load($params) && $this->validate())) 
     {
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
     }
          
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

    }


public function getSchetActivityData($params)
    {        
        $this->prepareSchetActivityData($params);    
        $dataList=$this->command->queryAll();
        
 //  return $dataList;

        
    
    $mask = realpath(dirname(__FILE__))."/../uploads/headSchetActivity*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headSchetActivity".time().".csv";
   
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        
        iconv("UTF-8", "Windows-1251","Организация"),
        iconv("UTF-8", "Windows-1251","Счет"),
        iconv("UTF-8", "Windows-1251","Оплата"),
        iconv("UTF-8", "Windows-1251","Поставка"),        
        

        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
      $listSupplyStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =3 order BY razdelOrder')->queryAll();        
      $maxSupplyStatus = count($listSupplyStatus)-1;
      $listCashStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =2 order BY razdelOrder')->queryAll();        
      $maxCashStatus = count($listCashStatus)-1;
      $listDocStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =1 order BY razdelOrder')->queryAll();                      
      $maxDocStatus = count($listDocStatus)-1;
        
    for ($i=0; $i< count($dataList); $i++)
    {        

        $currentStatus1=$currentStatus2=$currentStatus3="";     
        $supplyStatus  = " Нет запроса ";
        $deliverStatus =" Нет отгрузки ";
        
        $oplataSum   = " ";
        $oplataDate  = " ";
 
        $supplyRequest= " ";
        $supplySum = " ";
        $supplyDate = " ";
  

                /*  $supplyRequest, $supplySum,  $supplyDate,        */                    
                $listRequest = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet LIMIT 1',
                    [':refSchet' => $dataList[$i]['schetId'] ])->queryAll();

                if (count ($listRequest) == 0 )  $supplyRequest = "Нет запроса ";
                           else                  $supplyRequest = "Запрос на поставку № ".$listRequest[0]['id']." от ".date ('d.m.Y', strtotime($listRequest[0]['requestDate']));                 
                    
                    
                $listData= Yii::$app->db->createCommand(
                    'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet   LIMIT 1', 
                    [':refSchet' => $dataList[$i]['schetId'],])->queryAll();
             
                if (count ($listData) > 0 && $listData[0]['sumSupply'] > 0 )   
                $supplySum = number_format($listData[0]['sumSupply'],2,'.','');
                $supplyDate = date("d.m.Y", strtotime($listData[0]['lastSupply']));
        
                /*  Oplata  */      
               $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}} where refSchet=:refSchet   LIMIT 1', 
                [':refSchet' => $dataList[$i]['schetId'],])->queryAll();
                 
                 if (count($listData)!=0 && $listData[0]['sumOplata'] != 0)
                 {
                    $oplataSum   = number_format($listData[0]['sumOplata'],2,'.','');
                    $oplataDate  = date("d.m.Y", strtotime($listData[0]['lastOplate']));
                 }
                              
                
    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']), 
        
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetNum']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetDate']), 
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['schetSumm'],2,'.','')), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C']), 
            
            
            iconv("UTF-8", "Windows-1251",$oplataSum),      
            iconv("UTF-8", "Windows-1251",$oplataDate),      

            iconv("UTF-8", "Windows-1251",$supplyRequest),             
            iconv("UTF-8", "Windows-1251",$supplySum),      
            iconv("UTF-8", "Windows-1251",$supplyDate),      

            
            iconv("UTF-8", "Windows-1251",$currentStatus1), 
            iconv("UTF-8", "Windows-1251",$currentStatus2), 
            iconv("UTF-8", "Windows-1251",$currentStatus3), 

           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    
    
public function getSchetActivityProvider($params)
   {

        $this->prepareSchetActivityData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'orgId', 
            'orgTitle', 
            'schetNum', 
            'schetId', 
            'schetDate', 
            'ref1C', 
            'schetSumm', 
            'summOplata', 
            'summSupply'

            ],
            'defaultOrder' => ['schetDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;
   }   







  /*****************************/
  /*****************************/

   /** end of object **/     
 }
