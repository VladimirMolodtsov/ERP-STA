<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\ScladList;
use app\models\OtvesList;
use app\models\ZakazContent;

use app\models\TblWareList;
use app\models\TblWarehouse;


/**
 * MarketViewForm  - модель 
 */

 /*    
    supplyState
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
  0x00008 - 
*/  



class WareGoodForm extends Model
{

    public $id=0;

    public $rashodDate = array();
    
    public $wareTitle = "";//Номенклатурное название

    public $debug=[];

        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    
        
    public function rules()
    {
        return [
            [['recordId',  'dataType', 'dataVal'], 'default'],
            [['id',  ], 'safe'],
        ];
    }



  public function setAvRashod($id, $val)
  {
         $scladRecord= ScladList::findOne($id);      
      if (empty($scladRecord)) return false;
      $scladRecord->avRashod = $val;
      $scladRecord->save();
  }
  
  
public $rashodDetail=array();  
  public function loadData()
  {
      $scladRecord= ScladList::findOne($this->id);      
      if (empty($scladRecord)) return false;

        $this->rashodDate['curSupplyDate']= 0; 
        $this->rashodDate['curSupplyCount']= 0; 
        $this->rashodDate['curSupplySumm']= 0; 
        $this->rashodDate['prevSupplyDate']= 0; 
        $this->rashodDate['prevSupplyCount']= 0; 
        $this->rashodDate['prevSupplySumm']= 0; 


      $lastSupply='1970-01-01';
  

  
      $supplyList = Yii::$app->db->createCommand(
        'SELECT requestDate, wareCount, wareSumm  FROM {{%supplier_wares}} where wareRef=:wareRef ORDER BY requestDate DESC  LIMIT 5 ')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryALL();
      
      $this->rashodDate['supplyN'] = count ($supplyList);
      
      if (!empty($scladRecord->wareListRef)){
          
        $wareRecord = TblWareList::findOne($scladRecord->wareListRef);
            if (!empty($wareRecord)) $this->wareTitle = $wareRecord->wareTitle;
      }
      
      if ($this->rashodDate['supplyN'] > 0)
      { 
        $this->rashodDate['curSupplyDate']= $supplyList[0]['requestDate']; 
        $this->rashodDate['curSupplyCount']= $supplyList[0]['wareCount']; 
        $this->rashodDate['curSupplySumm']= $supplyList[0]['wareSumm']; 
        
       if ($supplyList[0]['requestDate'] != date('Y-m-d'))  
       {
        $this->rashodDate['cur'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[0]['requestDate'],
                    ':supplyNow' => date('Y-m-d'),                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['cur'] = 0;
       
       
       $lastSupply = Yii::$app->db->createCommand(
        'SELECT MAX(supplyDate)  FROM {{%supply}} where wareRef=:wareRef')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryScalar();
       if(empty($lastSupply))$lastSupply='1970-01-01';
       
       if (strtotime($supplyList[0]['requestDate']) < strtotime($lastSupply))  
       {
        $this->rashodDate['last'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[0]['requestDate'],
                    ':supplyNow' => $lastSupply,                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['last'] = 0;

      }

      $this->rashodDate['lastSupplyDate'] = $lastSupply;
      
      if ($this->rashodDate['supplyN'] > 1)
      { 
        $this->rashodDate['prevSupplyDate']= $supplyList[1]['requestDate']; 
        $this->rashodDate['prevSupplyCount']= $supplyList[1]['wareCount']; 
        $this->rashodDate['prevSupplySumm']= $supplyList[1]['wareSumm']; 

        
       if ($supplyList[1]['requestDate'] != $supplyList[0]['requestDate'])  
       {
        $this->rashodDate['prev'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount)/DATEDIFF(:supplyNow, :supplyPrev) FROM {{%supply}} where wareRef=:wareRef AND supplyDate >=:supplyPrev AND supplyDate <=:supplyNow')
                    ->bindValues([
                    ':wareRef' => $this->id,
                    ':supplyPrev' => $supplyList[1]['requestDate'],
                    ':supplyNow' => $supplyList[0]['requestDate'],                    
                    ])                    
                    ->queryScalar();                    
       }else $this->rashodDate['prev'] = 0;
       
      }

      $this->rashodDate['180d'] = Yii::$app->db->createCommand(
        'SELECT sum(supplyCount) FROM {{%supply}} where wareRef=:wareRef AND DATEDIFF(NOW(), supplyDate ) <= 180')
                    ->bindValue(':wareRef', $this->id)                    
                    ->queryScalar() /180;

      /*Помесячный расход */
      
      /*Инит*/
      $curY = date("Y");      
      for ($y=$curY-2;$y<=$curY+1;$y++)
      {
        for ($m=0;$m<13;$m++)  
        {
          $this->rashodDetail[$y][$m]['syncRef']=0;
          $this->rashodDetail[$y][$m]['v']=0;
          $this->rashodDetail[$y][$m]['prihod']=0;
          $this->rashodDetail[$y][$m]['next']=0;
          $this->rashodDetail[$y][$m]['cur']=0;
          $this->rashodDetail[$y][$m]['onDate']='';
          
        }
      }
      
      /*обновим связи*/
      $strSql = "UPDATE rik_control_purch_content as a, rik_warehouse as b SET a.goodRef = b.id
            where a.goodRef = 0  and  a.purchTitle =  b.title  and a.purchEd = b.ed"; 
      Yii::$app->db->createCommand($strSql)->execute();

      $strSql = "UPDATE rik_ware_content as a, rik_warehouse as b SET  a.goodRef = b.id
        where a.goodRef = 0  and  a.goodTitle =  b.title  and a.goodEd = b.ed"; 
      Yii::$app->db->createCommand($strSql)->execute();

      
     
       /*определяем последние синхронизации для склада*/
    

      $strSql = "SELECT  ifnull(MAX(id),0) as ref, MONTH(onDate) as m, YEAR(onDate) as y
      FROM rik_ware_header as h
      where YEAR(onDate)> (YEAR(NOW())-3) 
      GROUP BY YEAR(onDate), MONTH(onDate)
      ORDER BY YEAR(onDate), MONTH(onDate)";
      $uchetList = Yii::$app->db->createCommand($strSql)->queryALL();

$this->debug['uchetList']=$uchetList;  
      
      $strSql =   "SELECT SUM(a.goodAmount) as pC FROM rik_ware_content as a, rik_ware_use as b         
      WHERE a.useRef = b.id AND useInSum =1 and headerRef=:headerRef  and  goodRef= ".$this->id;  
      
      
      $strSql2 = "SELECT sum(b.pC) as s FROM
        (SELECT  MAX(id) as ref,onDate FROM rik_control_purch_header as a GROUP BY onDate ) as a
        LEFT JOIN 
        (SELECT SUM(purchCount) as pC, headerRef from rik_control_purch_content where purchDate> :stDate and  purchDate <= :enDate 
        AND goodRef=".$this->id." GROUP BY headerRef) as b
        ON a.ref = b.headerRef where  ifnull(b.pC,0) > 0   AND a.onDate> :stDate AND a.onDate <= :enDate";      
      $y=date('Y')-4;
      $stDate=$y.'-12-31';        
      for ($i=0;$i<count($uchetList);$i++)
      {          
//$this->debug['request'][$i][]=Yii::$app->db->createCommand($strSql,['headerRef' => $uchetList[$i]['ref']])->getRawSql();
        $uchetList[$i]['s'] = Yii::$app->db->createCommand($strSql,['headerRef' => $uchetList[$i]['ref']])->queryScalar();
        $uchetList[$i]['ondate'] = Yii::$app->db->createCommand("SELECT onDate from {{%ware_header}}        
        where id=:headerRef",['headerRef' => $uchetList[$i]['ref']])->queryScalar();
        if (empty($uchetList[$i]['s']))$uchetList[$i]['s']=0;
        if (!empty($uchetList[$i]['ondate'])){        
        $enDate = $uchetList[$i]['ondate'];
        
$this->debug['requestPrihod'][$i][]=Yii::$app->db->createCommand($strSql2,['stDate' => $stDate, 'enDate' => $enDate ])->getRawSql();        
        $uchetList[$i]['prihod'] = Yii::$app->db->createCommand($strSql2,['stDate' => $stDate, 'enDate' => $enDate ])->queryScalar();
        $stDate=$enDate;
        }
      }
$this->debug['sclad']=$uchetList;    
/*      $strSql = "SELECT MONTH(a.onDate) as m, YEAR(a.onDate) as y, sum(b.pC) as s FROM
        (SELECT  MAX(id) as ref,onDate FROM rik_ware_header as a GROUP BY onDate ) as a
        LEFT JOIN 
        (	SELECT SUM(goodAmount) as pC, headerRef from {{%ware_content}}, {{%ware_use}}         
        where {{%ware_content}}.useRef ={{%ware_use}}.id and useInSum=1 and  goodRef=".$this->id." GROUP BY headerRef) as b
        ON a.ref = b.headerRef where  ifnull(b.pC,0) > 0 AND YEAR(a.onDate)> (YEAR(NOW())-3) 
        GROUP BY MONTH(a.onDate), YEAR(a.onDate)";
      $uchetList = Yii::$app->db->createCommand($strSql)->queryALL();
*/         

      /*Соберем приход за 3 года*/
      
      /**/
      

        $strSql = "SELECT  MAX(id) as ref, MONTH(onDate) as m, YEAR(onDate) as y FROM {{%control_purch_header}}
        GROUP BY YEAR(onDate), MONTH(onDate)
        ORDER BY YEAR(onDate), MONTH(onDate)";
        
        $prihodHList = Yii::$app->db->createCommand($strSql)->queryALL();
          $strSql = "SELECT SUM(purchCount) as pC from {{%control_purch_content}} where goodRef=".$this->id." 
          AND headerRef =:headerRef";
        for ($i=0;$i<count($prihodHList); $i++)
        {
          $prihodHList[$i]['s'] = Yii::$app->db->createCommand($strSql,['headerRef' => $prihodHList[$i]['ref']])->queryScalar();
      
        }  
      
      $strSql = "SELECT MONTH(a.onDate) as m, YEAR(a.onDate) as y, sum(b.pC) as s FROM
        (SELECT  MAX(id) as ref,onDate FROM rik_control_purch_header as a GROUP BY onDate ) as a
        LEFT JOIN 
        (SELECT SUM(purchCount) as pC, headerRef from rik_control_purch_content where goodRef=".$this->id." GROUP BY headerRef) as b
        ON a.ref = b.headerRef where  ifnull(b.pC,0) > 0   AND YEAR(a.onDate)> (YEAR(NOW())-3)
        GROUP BY MONTH(a.onDate), YEAR(a.onDate) ";      
  
        
        $prihodList = Yii::$app->db->createCommand($strSql)->queryALL();
      
$this->debug['prihod']=$prihodList;
$this->debug['prihodH']=$prihodHList;            

       $strSql = "SELECT SUM(purchCount) as pC from {{%control_purch_content}} where goodRef=".$this->id." 
       AND headerRef =:headerRef";
//      for ($i=0;$i<count($prihodHList); $i++)
      {
//          $y= $prihodHList[$i]['y'];
//          $m= $prihodHList[$i]['m'];          
/*          $prihodList[$i]['s'] = Yii::$app->db->createCommand($strSql,['headerRef' => $prihodList[$i]['ref']])->queryScalar();
          if (empty($prihodList[$i]['s']))$prihodList[$i]['s']=0;
          $this-> rashodDetail[$y][$m]['prih_onDate'] = Yii::$app->db->createCommand("SELECT onDate from {{%control_purch_header}}
        where id=:headerRef",['headerRef' => $prihodList[$i]['ref']])->queryScalar();*/
        
    /*      $this-> rashodDetail[$y][$m]['v']+= $prihodHList[$i]['s'];
          $this-> rashodDetail[$y][$m]['prihod'] = $prihodHList[$i]['s'];   */       
      }
      /*Соберем расход за 3 года*/ 
       
      //  было + приход -Стало
        
      for ($i=0;$i<count($uchetList); $i++)
      {
          $y= $uchetList[$i]['y']; //стало
          $m= $uchetList[$i]['m'];    
            
          $yn = $y;  //стало
          $mn = $m+1;         
          if ($mn>12){$mn=1;$yn++;}
            
          $this-> rashodDetail[$yn][$mn]['v'] += $uchetList[$i]['s'];
          $this-> rashodDetail[$y][$m]['v']   -= $uchetList[$i]['s'];
          
            
          $this-> rashodDetail[$y][$m]['v']+= $uchetList[$i]['prihod'];
          $this-> rashodDetail[$y][$m]['prihod'] = $uchetList[$i]['prihod'];     
            
          $this-> rashodDetail[$yn][$mn]['next'] = $uchetList[$i]['s'];
          $this-> rashodDetail[$y][$m]['cur'] = $uchetList[$i]['s'];          
          $this-> rashodDetail[$y][$m]['onDate']= $uchetList[$i]['ondate'];          
     }
   
      return $scladRecord;  
  }

/******************/
/*

*/
/*****************/   
    public function saveGoodCard()
    {
      $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
             'debug' => '',
           ];   
           
    $record= TblWarehouse::findOne($this->recordId);     
    if (empty($record)) return;
           
           
    switch ($this->dataType)
    {
        case 'wareTypeName':
           $record->wareTypeRef = intval($this->dataVal);           
           $record->save(); 
           $res['val'] =  $record->wareTypeRef ;
           
           /*$res['debug'] = Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET wareTypeRef =:wareTypeRef
           WHERE title = :wareTitle',
           [':wareTypeRef' => $record->wareTypeRef,
            ':wareTitle' =>  $record->title,
           ])->getRawSql(); */           
           /*И все похожие на него*/   
           Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET wareTypeRef =:wareTypeRef
           WHERE title = :wareTitle',
           [':wareTypeRef' => $record->wareTypeRef,
            ':wareTitle' =>  $record->title,
           ])->execute();
           break;
        case 'wareGrpTitle':
           $record->grpRef = intval($this->dataVal); 
           $record->save(); 
           $res['val'] =  $record->grpRef ;
           /*И все похожие на него*/   
           Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET grpRef =:ref
           WHERE title = :wareTitle',
           [':ref' => $record->grpRef,
            ':wareTitle' =>  $record->title,
           ])->execute(); 
           break;

        case 'wareProdTitle':
           $record->producerRef = intval($this->dataVal); 
           $record->save(); 
           $res['val'] =  $record->grpRef ;
           /*И все похожие на него*/   
           Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET producerRef =:ref
           WHERE title = :wareTitle',
           [':ref' => $record->producerRef,
            ':wareTitle' =>  $record->title,
           ])->execute(); 
           break;
           
           
        case 'wareTitle':
           $ref= intval($this->dataVal);
           $list = Yii::$app->db->createCommand('Select id, grpRef, producerRef, wareTypeRef from {{%ware_list}} where id = :wareRef',
           [':wareRef' => $ref])->queryOne();            
           if (!empty($list['grpRef'])) $record->grpRef = $list['grpRef'];
           if (!empty($list['producerRef'])) $record->producerRef = $list['producerRef'];
           if (!empty($list['wareTypeRef'])) $record->wareTypeRef = $list['wareTypeRef']; 
           $record->wareListRef = $ref;           
           $record->save(); 
           
           Yii::$app->db->createCommand('UPDATE {{%warehouse}} SET 
           wareTypeRef =:wareTypeRef,
           grpRef =:grpRef,
           producerRef =:producerRef,
           wareListRef =:wareListRef
           WHERE title = :wareTitle',
           [
             ':grpRef' => $record->grpRef,
             ':wareTypeRef' => $record->wareTypeRef,
             ':producerRef' => $record->producerRef,
             ':wareListRef' => $record->wareListRef,
            ':wareTitle' =>  $record->title,
           ])->execute();

           
           $res['val'] =  $record->wareListRef ;
           break;   
        case 'isActive':           
            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            $record->save();                    
            $res['val'] =  $record->isActive ;            
        break;            
        default:
            return $res;         
     }      
     
          
    $res['res'] = true;    
    return $res;
    }
/*****************/   


/*******************************/  
  public function getStats()
  {
      
  //Поступило заявок
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW())";            
  $stats['m_accept'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())";            
  $stats['d_accept'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  // Отказов
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW()) AND (supplyState & 0x00004)";            
  $stats['m_reject'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())  AND (supplyState & 0x00004)";            
  $stats['d_reject'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              


  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where MONTH(requestDate) = MONTH(NOW()) AND (supplyState & 0x00008)";            
  $stats['m_supplied'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_supply}}.id) from {{%request_supply}} where DATE(requestDate) = DATE(NOW())  AND (supplyState & 0x00008)";            
  $stats['d_supplied'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
  

  //За день
  $strCount = "SELECT count({{%purchase}}.id) from {{%purchase}} 
  LEFT JOIN (SELECT execDate, purchaseRef FROM {{%purchase_etap}} WHERE stage=4 and etap IN (3,8)) as a on a.purchaseRef = {{%purchase}}.id
  where DATE(a.execDate) = DATE(NOW())  AND isFinishedPurchase = 1";            
  $stats['d_buy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
    
  //За месяц
  $strCount = "SELECT count({{%purchase}}.id) from {{%purchase}} 
  LEFT JOIN (SELECT execDate, purchaseRef FROM {{%purchase_etap}} WHERE stage=4 and etap IN (3,8)) as a on a.purchaseRef = {{%purchase}}.id
  where MONTH(a.execDate) = MONTH(NOW())  AND isFinishedPurchase = 1";            
  $stats['m_buy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
  
  
  
  //За месяц   SELECT * from rik_request_supply where MONTH(requestDate) = MONTH(NOW())
  $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where 
  (MONTH(factDate) = MONTH(NOW()) OR MONTH(requestDatePlanned) = MONTH(NOW()) )
  AND (requestStatus = 6)";            
  $stats['m_delivered'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

  //За день
  $strCount = "SELECT count({{%request_deliver}}.id) from {{%request_deliver}} where 
  (DATE(factDate) = DATE(NOW()) OR DATE(requestDatePlanned) = DATE(NOW()) )
  AND (requestStatus = 6)";            ;            
  $stats['d_delivered'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
   
  
  return $stats;
  }



 /***************************************/
  public function getPurchProvider($params)
   {
     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supplier_wares}}.id)")
                  ->from("{{%supplier_wares}}")                  
                 ;
                  
     $query->select([ 
        'requestDate',
        'wareSumm', 
        'wareCount',
        'wareEd',    
        'wareTitle', 
        'orgTitle', 
        'requestNum',         
        ])->from("{{%supplier_wares}}")                  
                  ->distinct();      

                  
      $query->andWhere(['=', '{{%supplier_wares}}.wareRef', $this->id]);
      $countquery->andWhere(['=', '{{%supplier_wares}}.wareRef', $this->id]);
                    
                  
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
        
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
                'requestDate',
                'wareSumm', 
                'wareCount',
                'wareEd',    
                'wareTitle', 
                'orgTitle', 
                'requestNum',         
            ],
            'defaultOrder' => ['requestDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
 } 

public function getSupplyProvider($params) 
{

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%supply}}.id)")
                  ->from("{{%supply}}")                                
                 ;
                  
     $query->select([ 
        'supplyDate',
        'supplySumm', 
        'supplyCount',
        'supplyEd',    
        'supplyGood', 
        'orgTitle', 
        'schetNum',         
        'schetDate',         
        ])->from("{{%supply}}")                  
                  ->distinct();      

                  
      $query->andWhere(['=', '{{%supply}}.wareRef', $this->id]);
      $countquery->andWhere(['=', '{{%supply}}.wareRef', $this->id]);
                    
                  
    if (($this->load($params) && $this->validate())) 
     {
  
        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);                      
        
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
                'supplyDate',
                'supplySumm', 
                'supplyCount',
                'supplyEd',    
                'supplyGood', 
                'orgTitle', 
                'schetNum',         
                'schetDate',         
            ],
            'defaultOrder' => ['supplyDate' => SORT_DESC ],
            ],
        ]);
        
    return $provider;     
         
    
}
 
public $syncDate="";
public $onDate="";
public $lastRef=0;
/***************************/ 

  public function getLastSync()
  {
   
   $refList = Yii::$app->db->createCommand('Select MAX(id) as ref, onDate, MAX(syncDate) as  syncDate   from {{%ware_header}} 
   GROUP BY onDate  ORDER BY onDate DESC LIMIT 2')
                    ->queryAll();  
   if (count($refList) > 0) {
       $this->lastRef = $refList[0]['ref'];
       $this->onDate=$refList[0]['onDate'];
       $this->syncDate=$refList[0]['syncDate'];
   } else
   {
       $this->lastRef = 0;
       $this->onDate="";
       $this->syncDate="";
   }

  }
  
 
 
   public function getWareInScladProvider($params, $wareRef)
   {
    
     $this->getLastSync();
    
    $query  = new Query();
    $query->select ([ '{{%ware_content}}.id',  
                      '{{%ware_content}}.orgTitle',
                      '{{%ware_content}}.scladTitle' ,
                      'goodAmount',
                      'initPrice',
                      'showOrder'
                      ])
            ->from("{{%ware_content}}")                                    
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id = {{%ware_content}}.useRef");               
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_content}}.id)")
            ->from("{{%ware_content}}")                                    
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id = {{%ware_content}}.useRef");               
            ;
                    
     $query->andWhere(['=', 'ifnull({{%ware_use}}.isInUse  ,0)', 1]);
     $countquery->andWhere(['=', 'ifnull({{%ware_use}}.isInUse,0)', 1]);
     
     
     $query->andWhere(['=', 'headerRef', $this->lastRef]);
     $countquery->andWhere(['=', 'headerRef', $this->lastRef]);

     
     $query->andWhere(['=', 'goodRef', $wareRef]);
     $countquery->andWhere(['=', 'goodRef', $wareRef]);
     
     
    if (($this->load($params) && $this->validate())) {
     
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
                    'orgTitle',
                    'scladTitle' ,
                    'goodAmount',
                    'initPrice',
                    'showOrder'
            ],            
            'defaultOrder' => [ 'showOrder' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 

  
  
/**/    
 }
 
