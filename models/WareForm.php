<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\TblWareUse;
use app\models\TblWareContent;
use app\models\TblWareGrp;
use app\models\TblWareType;
use app\models\TblWareProducer;
use app\models\TblWareFormat;
use app\models\TblWareList;
use app\models\TblWarehouse;
use app\models\TblWareEdLnk;

/**
 * WareForm  - модель склады (новые)
 */


class WareForm extends Model
{

    public $id=0;
    public $orgTitle;
    public $orgTitleUse;
    public $scladTitle;
    public $goodTitle;
    public $isActive=1;
    public $strDate;
    public $isProduction = 1;
    
    public $sumValue=0;        
    public $syncDateTime=0;        
    public $isFiltered;
    public $isInUse;
    
    public $goodAmount;
    public $fltScladTitle;
    public $fltOrgTitle;
    public $errOnly=0;
    
    public $wareTypeName="";     
    public $wareGrpTitle=""; 
    public $wareProdTitle="";
    public $edTitle;
      
    public $debug=[];
    
    /***/
    public $wareTitle;
    public $grpRef ;    
    public $producerRef ;
    public $density ;
    public $format ;
    public $warePack;
    public $wareEd ;
    public $wareTypeRef ;
    public $store;
    public $state;
    public $saleType =0; //1 - в сырье 2 - в продукции
    
    public $wareLength='';
    public $wareWidth='';
    
    public $wareMark='';
    public $wareSort='';

    public $wareMarkGen ='';    
    public $wareWidthGen='';
    public $wareLengthGen='';
    public $saleTypeGen='';
    public $wareSortGen='';

    public $wareEdList=[];

        
        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;

    
    /*Dynamic atributes*/
    /* заменить на динамически создаваемые     
        class Foo
        { 
            public function createProperty($name, $value)
            {        
            $this->{$name} = $value; 
            } 
        } 
        
        $foo = new Foo(); 
        $foo->createProperty('hello', 'something');     
    */
    public $sclad_0=0;
    public $sclad_1=0;
    public $sclad_2=0;
    public $sclad_3=0;
    public $sclad_4=0;
    public $sclad_5=0;
    public $sclad_6=0;
    public $sclad_7=0;
    public $sclad_8=0;
    public $sclad_9=0;
    public $sclad_10=0;
    
    public function rules()
    {
        return [
            [['orgTitle','scladTitle', 'goodTitle', 'isActive', 'isFiltered', 'orgTitleUse', 'isInUse', 'isProduction', 
            'wareTypeName', 'wareGrpTitle',  'wareProdTitle',  'edTitle','goodAmount',
            'sclad_0', 'sclad_1','sclad_2','sclad_3','sclad_4','sclad_5','sclad_6','sclad_7','sclad_8','sclad_9','sclad_10',
            ], 'safe'],
            

    
               
            [['wareTitle','grpRef', 'producerRef', 'density', 'format', 'warePack',
            'isProduction', 'isActive', 'wareEd',  'wareTypeRef', 'store','id','wareEdList',
            'wareMarkGen', 'wareWidthGen','wareLengthGen','saleTypeGen', 'wareSortGen',        
            'recordId','dataType','dataVal'
            ], 'default'],
        ];
    }
  /***************************/ 
  
  
  public function loadData()
  {
    $record = TblWareList::findOne($this->id);
    if(empty($record)) return;
    
    $this->wareTitle = $record->wareTitle ;
    $this->grpRef = $record-> grpRef;
    $this->wareTypeRef = $record->wareTypeRef ;
    $this->producerRef = $record->producerRef ;
    $this->density = $record->wareDensity ;
    $this->format = $record-> wareFormat;
    $this->isProduction = $record->isProduction ;
    $this->isActive = $record-> isActive;
    
    $this->wareMark=$record-> wareMark;
    $this->wareSort=$record-> wareSort;

    //$this->store = $record->wareStore ;
  }
  
 
  
   public function switchWareActive($id)
   {
       //$curUser=Yii::$app->user->identity;
       
       
       $wareRecord = TblWareContent::findOne($id);      
       if (empty($wareRecord)) return false;
       
       $headerRef = $wareRecord->headerRef;
       
       $record= TblWareUse::findOne($wareRecord->useRef);      
       if (empty($record)) return false;
       
       if ($record->isInUse == 1)$record->isInUse = 0;
       else                      $record->isInUse = 1;
                     
       $record->save();


       Yii::$app->db->createCommand(
            'UPDATE {{%ware_content}},{{%ware_use}} SET {{%ware_content}}.isActive = {{%ware_use}}.isInUse 
             where  {{%ware_content}}.useRef = {{%ware_use}}.id  AND headerRef =:headerRef;', 
            [ ':headerRef' => $headerRef , ])->execute();    
       
       return true;
   }
   
   
   public function switchWareWareFiltered ($org, $filterVal)
   {
   
       $record= TblWareUse::findOne($org);      
       if (empty($record)) return false;
   
   
       Yii::$app->db->createCommand(
            'UPDATE {{%ware_use}} SET isFiltered = :filterVal
             where  orgTitle = :orgTitle;', 
            [ ':orgTitle' => $record->orgTitle,
              ':filterVal' => $filterVal ,
             ])->execute();    
      
   }
  /***************************/ 
   public function switchWareUse($id,$strDate)  
   {
       
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%ware_header}} WHERE DATE(onDate) =:syncDate', 
            [ ':syncDate' => $strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
       
       $record= TblWareUse::findOne($id);      
       if (empty($record)) return false;
       
       if ($record->isInUse == 1)$record->isInUse = 0;
       else                      $record->isInUse = 1;
                     
       $record->save();

       Yii::$app->db->createCommand(
            'UPDATE {{%ware_content}},{{%ware_use}} SET {{%ware_content}}.isActive = {{%ware_use}}.isInUse 
             where  {{%ware_content}}.useRef = {{%ware_use}}.id  AND headerRef =:headerRef;', 
            [ ':headerRef' => $headerRef , ])->execute();    
       
       return true;
   }    
  /***************************/ 
   public function switchWareWareInSum($id)
   {
       $record= TblWareUse::findOne($id);      
       if (empty($record)) return false;
       
       if ($record->useInSum == 1)$record->useInSum = 0;
       else                       $record->useInSum = 1;                     
       $record->save();       
       return true;
   }    
   
  /***************************/ 
  public function getWareListProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%ware_header}} WHERE DATE(onDate) =:syncDate', 
            [ ':syncDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
//$this->debug[] = $headerRef; 
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%ware_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
   
    
    
    $query  = new Query();
    $query->select ([ '{{%ware_content}}.id',  
                      'orgTitle', 
                      'scladTitle',
                      'articul',                      
                      'grpGood',
                      'goodTitle',
                      'goodEd',
                      'goodAmount',
                      'initPrice',
                      'isActive',
                      'useRef',
                      ])
            ->from("{{%ware_content}}")
            ->andWhere("headerRef = ".$headerRef)
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_content}}.id)")
               ->from("{{%ware_content}}")
               ->andWhere("headerRef = ".$headerRef)
            ;
    $sumquery  = new Query();        
    $sumquery->select (" sum(goodAmount*initPrice) ")
               ->from("{{%ware_content}}")
               ->andWhere("headerRef = ".$headerRef)
            ;
            

     
            
    if (($this->load($params) && $this->validate())) {
    
    
     $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $sumquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     
     $query->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     $countquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     $sumquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);

     $query->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     $sumquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     
     }
        
     if (empty($this->scladTitle) ) 
     {
     $this->scladTitle=$this->fltScladTitle; 
     $query->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     $countquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     $sumquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     }
       
     if (empty($this->orgTitle) ) 
     {
     $this->orgTitle=$this->fltOrgTitle; 
     $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $sumquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     }
              
     if($this->errOnly==1)
     {
              $query->andWhere(('goodAmount < 0 OR  initPrice < 0 '));
         $countquery->andWhere(('goodAmount < 0 OR  initPrice < 0 '));
           $sumquery->andWhere(('goodAmount < 0 OR  initPrice < 0 '));
     }
          
     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', 'isActive', 1]);
         $countquery->andFilterWhere(['=', 'isActive', 1]);
         $sumquery->andFilterWhere(['=', 'isActive', 1]);
       }  

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', 'isActive', 0]);
         $countquery->andFilterWhere(['=', 'isActive', 0]);
         $sumquery->andFilterWhere(['=', 'isActive', 0]);
       }  
     }
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    $this->sumValue =  $sumquery->createCommand()->queryScalar();                                     
    
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
            'scladTitle',
            'articul',
            'grpGood',
            'goodTitle',
            'goodEd',
            'goodAmount',
            'initPrice',
            'isActive',           
            ],
            
            'defaultOrder' => [ 'orgTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/***********/


  /***************************/ 
  public function getWareUseProvider($params)
   {

    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%ware_header}} WHERE DATE(onDate) =:syncDate', 
            [ ':syncDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки

    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%ware_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
    
    $query  = new Query();
    $query->select ([ '{{%ware_use}}.id',  
                      '{{%ware_use}}.orgTitle',  
                      '{{%ware_use}}.scladTitle',  
                      '{{%ware_use}}.isInUse',
                      '{{%ware_use}}.useInSum',                      
                      'ifnull(vSum,0) as initSum',
                      'ifnull(b.eSum,0) as errSum',
                      'ifnull(b.eNum,0) as errNum',
                      ])
            ->from("{{%ware_use}}")
            ->leftJoin('(SELECT SUM(goodAmount*initPrice) as vSum, useRef FROM {{%ware_content}} where goodAmount > 0 AND initPrice > 0  AND headerRef = '.$headerRef." Group by useRef) as a", 'a.useRef = {{%ware_use}}.id')
            ->leftJoin('(SELECT count(id) as eNum, SUM(goodAmount*initPrice) as eSum, useRef FROM {{%ware_content}} where (goodAmount < 0 OR initPrice < 0)  AND headerRef = '.$headerRef." Group by useRef) as b", 'b.useRef = {{%ware_use}}.id')
            ;
        

        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT({{%ware_use}}.id))")
               ->from("{{%ware_use}}")
               
            ;
               
    $sumquery  = new Query();        
    $sumquery->select (" sum(goodAmount*initPrice) ")
               ->from("{{%ware_content}}")
               ->andWhere("goodAmount > 0 AND initPrice > 0 AND headerRef = ".$headerRef);
     $sumquery->andWhere(['=', 'isActive', 1]);


    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', '{{%ware_use}}.orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', '{{%ware_use}}.orgTitle', $this->orgTitle]);
    // $sumquery->andFilterWhere(['like', '{{%ware_use}}.orgTitle', $this->orgTitle]);

     $query->andFilterWhere(['like', '{{%ware_use}}.scladTitle', $this->scladTitle]);
     $countquery->andFilterWhere(['like', '{{%ware_use}}.scladTitle', $this->scladTitle]);
    // $sumquery->andFilterWhere(['like', '{{%ware_use}}.scladTitle', $this->scladTitle]);
         
     }
     
     if (empty ($this->isInUse)) $this->isInUse = 2;     
     
     
       if($this->isInUse == 1)
       {
         $query->andWhere("isFiltered = 1 OR isInUse = 1");
         $countquery->andWhere("isFiltered = 1  OR isInUse = 1");
       }     
       if($this->isInUse == 2)
       {
         $query->andFilterWhere(['=', 'isInUse', 1]);
         $countquery->andFilterWhere(['=', 'isInUse', 1]);
       }  
       if($this->isInUse == 3)
       {
         $query->andWhere("isFiltered = 1");
         $countquery->andWhere("isFiltered = 1");
         $query->andFilterWhere(['=', 'isInUse', 0]);
         $countquery->andFilterWhere(['=', 'isInUse', 0]);
       }  
     

     
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    $this->sumValue =  $sumquery->createCommand()->queryScalar();                                     

//$this->debug[]=$query->createCommand()->getRawSql();             
        
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
            'scladTitle',
            'isInUse',
            'initSum',
            'errSum',
            'errNum',
            'useInSum'
            ],
            
            'defaultOrder' => [ 'orgTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   

  /***************************/ 

  public function getWareOrgProvider($params)
   {
    //
   /* $strSql ="UPDATE {{%warehouse}} as a,  {{%ware_grp}} as b  SET a.grpRef = b.id
     where   a.title like b.wareGrpTemplate   and   a.grpRef = 0";
    Yii::$app->db->createCommand($strSql)     ->execute();  */
    
    
    $query  = new Query();    
    $query->select ([ '{{%ware_use}}.orgTitle as orgTitleUse',  
                      '{{%ware_use}}.isFiltered',                       
                      'MIN(id) as useRef'
                      ])
            ->from("{{%ware_use}}")            
            ->distinct()
            ->groupBy('{{%ware_use}}.orgTitle')
            ;

    if (($this->load($params) && $this->validate())) {
         $query->andFilterWhere(['like', '{{%ware_use}}.orgTitle', $this->orgTitleUse]);         
     }
     
     if (empty ($this->isFiltered)) $this->isFiltered = 2;     
       if($this->isFiltered == 2)
       {
         $query->andFilterWhere(['=', 'isFiltered', 1]);
       }  
       if($this->isFiltered == 3)
       {
         $query->andFilterWhere(['=', 'isFiltered', 0]);
       }  
     
     
     
    $command = $query->createCommand(); 
    
    $list = $query->createCommand()->queryAll(); 
    $count = count($list);                 
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            
            'sort' => [            
            'attributes' => [
            'orgTitleUse', 
            'isFiltered',
            ],            
            'defaultOrder' => [ 'orgTitleUse' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/*************************/
/*
ALTER TABLE `rik_warehouse` ADD COLUMN `isProduction` TINYINT DEFAULT 0 AFTER `isActive`;
ALTER TABLE `rik_warehouse` MODIFY COLUMN `isAnalyse` TINYINT DEFAULT 1 COMMENT 'Участвует в анализе';
UPDATE rik_warehouse set isProduction = 1 where title LIKE '%*%';
UPDATE rik_warehouse set isProduction = 0 where title LIKE '%Мешки%';
UPDATE rik_warehouse set isProduction = 0 where title LIKE '%Картон%';
*/

/***************************/ 
  public function getWareUseList()
  {
   $list = Yii::$app->db->createCommand('Select id, scladTitle from {{%ware_use}} where isInUse = 1')                    
                    ->queryAll();                
   
   
   $res =  ArrayHelper::map($list, 'id', 'scladTitle');     
   $res[0]='Все используемые';
   $res[-1]='Все склады';
   return  $res;
  }
  public function getWareShowProvider($params)
   {
    
    $query  = new Query();
    $query->select ([ '{{%warehouse}}.id',  
                      '{{%ware_use}}.orgTitle', 
                      '{{%ware_use}}.scladTitle',
                      '{{%warehouse}}.articul',                                            
                      '{{%warehouse}}.title as goodTitle',
                      '{{%warehouse}}.ed as goodEd',
                      '{{%warehouse}}.amount as goodAmount',
                      '{{%warehouse}}.initPrice',
                      '{{%warehouse}}.isActive',
                      '{{%warehouse}}.useRef',
                      '{{%warehouse}}.wareTypeRef',
                      'wareTypeName',
                      '{{%warehouse}}.grpRef',
                      'wareGrpTitle',
                      '{{%warehouse}}.producerRef',
                      'wareProdTitle',                      
                      'wareListRef',                      
                      '{{%ware_list}}.wareTitle',
                      '{{%warehouse}}.wareDensity',
                      '{{%warehouse}}.wareFormat',
                      '{{%warehouse}}.wareEdRef',
                      '{{%warehouse}}.warePack',                                            
                      '{{%warehouse}}.isProduction'
                      ])
            ->from("{{%warehouse}}")            
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id= {{%warehouse}}.useRef")
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%warehouse}}.wareListRef")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")
            
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
            ->from("{{%warehouse}}")            
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id= {{%warehouse}}.useRef")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")

            ;
/*    $sumquery  = new Query();        
    $sumquery->select (" sum(goodAmount*initPrice) ")
               ->from("{{%ware_content}}")
               ->andWhere("headerRef = ".$headerRef)
            ;*/


            

    if($this->errOnly ==1){
     
     $query->andWhere(['=', 'ifnull(wareListRef,0)', 0]);
     $countquery->andWhere(['=', 'ifnull(wareListRef,0)', 0]);
     
     }
        
            
    if (($this->load($params) && $this->validate())) {
    
    
     $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     //$sumquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     

     $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->goodTitle]);
     $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->goodTitle]);
     //$sumquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
     
     $query->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     
     $query->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);

     $query->andFilterWhere(['=', '{{%warehouse}}.producerRef', $this->wareProdTitle]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.producerRef', $this->wareProdTitle]);
     
     }

     switch($this->isProduction) 
     {
     case 1:
          $query->andWhere(['=', '{{%warehouse}}.isProduction', 0]);
     $countquery->andWhere(['=', '{{%warehouse}}.isProduction', 0]);
     break;
     case 2:
          $query->andWhere(['=', '{{%warehouse}}.isProduction', 1]);
     $countquery->andWhere(['=', '{{%warehouse}}.isProduction', 1]);
     break;
     
     }
          
     if ($this->scladTitle != -1 )         
     {
        $query->andWhere(['=', '{{%ware_use}}.isInUse', 1]);
        $countquery->andWhere(['=', '{{%ware_use}}.isInUse', 1]);
     //$sumquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
     }
     if ($this->scladTitle > 0 ){         
        $query->andFilterWhere(['=', '{{%warehouse}}.useRef', $this->scladTitle]);
        $countquery->andFilterWhere(['=', '{{%warehouse}}.useRef', $this->scladTitle]);
     }



    switch ($this->goodAmount)
    {
        
     case 1:   
          $query->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     break;
     
     case 2:   
          $query->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     break;
     
     case 3:   
          $query->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     break;
    }
               
     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
       }  

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
       }  
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
                'scladTitle',
                'articul',                                            
                'goodTitle',
                'goodEd',
                'goodAmount',
                'initPrice',
                'isActive',
                'wareGrpTitle',
                'wareProdTitle',
                'wareTypeName',
                'wareDensity',
                'wareFormat',                  
          
            ],            
            'defaultOrder' => [ 'goodTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 
  
public function getWareTypesProvider($params)
   {
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'wareTypeName',
                      ])
            ->from("{{%ware_type}}")            
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_type}}.id)")
            ->from("{{%ware_type}}")            
            ;
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
               'wareTypeName',
            ],            
            'defaultOrder' => [ 'wareTypeName' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
    
/*********************/
public function getWareGroupProvider($params)
   {
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'wareGrpTitle',
                      'wareTypeRef',
                      ])
            ->from("{{%ware_grp}}")            
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_grp}}.id)")
            ->from("{{%ware_grp}}")            
            ;
            
    if (!empty($this->twareTypeRef))
    {
     //свяжем с типом
    }

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
               'wareGrpTitle',
               'wareTypeRef'
            ],            
            'defaultOrder' => [ 'wareGrpTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
    
/*********************/
public function getWareProducerProvider($params)
   {
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'wareProdTitle',
                      ])
            ->from("{{%ware_producer}}")            
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_producer}}.id)")
            ->from("{{%ware_producer}}")            
            ;
            
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
               'wareProdTitle',
            ],            
            'defaultOrder' => [ 'wareProdTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
 /***********/ 
  
public function getWareFormatsProvider($params)
   {
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'formatString',
                      'width',
                      'length',
                      'isProduct',
                      'intSize',
                      ])
            ->from("{{%ware_format}}")            
            ;
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_format}}.id)")
            ->from("{{%ware_format}}")            
            ;
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
                  'formatString',
                   'width',
                   'length',
                   'isProduct',
                   'intSize',
            ],            
            'defaultOrder' => [ 'formatString' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
    
/***************************/ 
/*

*/

public function getWareEdList()
{

   $listStatus = Yii::$app->db->createCommand('Select id, edTitle from {{%ware_ed}}')                    
                    ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'edTitle');      
}   

public function getWareTypes()
{

   $listStatus = Yii::$app->db->createCommand('Select id, wareTypeName from {{%ware_type}} ORDER BY wareTypeName')                    
                    ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'wareTypeName');      
}   

public function getWareGroups()
{

    $query  = new Query();
    $query->select ([ 'id', 
                       'wareGrpTitle',
                    ])
            ->from("{{%ware_grp}}")            
            ->andWhere("wareGrpTitle != ''")
            ->orderBy ('wareGrpTitle')
            ;
            
    if(!empty($this->wareTypeName))  $query ->andWhere("wareTypeRef = ".intval($this->wareTypeName));

   $listStatus = $query->createCommand() ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'wareGrpTitle');      
}   


public function getWareFormat()
{

    $query  = new Query();
    $query->select ([ 'id', 
                       'formatString',
                    ])
            ->from("{{%ware_format}}")            
            ->orderBy ('formatString')
            ;
    $isProduct = intval($this->saleType) - 1;
    if(!empty($this->saleType))   $query ->andWhere("isProduct = ".$isProduct);

   $listStatus = $query->createCommand() ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'formatString');      
}   


public function getWareProducer()
{

   $listStatus = Yii::$app->db->createCommand("Select id, wareProdTitle from {{%ware_producer}}
   where wareProdTitle !='' ORDER BY wareProdTitle
   ")                    
                    ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'wareProdTitle');      
}   
   
   
  public function createNomenklature($wareTypeRef, $grpRef, $producerRef, $wareFormat, $wareWidth, $wareLength, $wareDensity, $warePack)
  {
    $this->wareTitle="";
    
    $wareTypeRef = intval ($wareTypeRef); 
    $grpRef      = intval ($grpRef);
    $producerRef = intval ($producerRef);
        
    if (!empty($wareTypeRef))
        $wareType = Yii::$app->db->createCommand('Select wareTypeName from {{%ware_type}} where id=:ref',
        [':ref' =>$wareTypeRef ])->queryScalar();                
    else return false;    

    if (!empty($grpRef))
        $wareGroup = Yii::$app->db->createCommand('Select wareGrpTitle from {{%ware_grp}} where id=:ref',
        [':ref' =>$grpRef ])->queryScalar();                
    else return false;   

    
   $this->wareTitle = $wareType." ".$wareGroup;
   if (!empty($wareFormat)){
    $formatList = $this->getWareFormat();
    $this->wareTitle .= " ф.".$formatList[$wareFormat];    
   }  
   else{
        if (!empty($wareWidth)){            
         $this->wareTitle .= " ф.".$wareWidth;       
         if (!empty($wareLength))$this->wareTitle .= "*".$wareLength;       
        }
   }       
   if (!empty($wareDensity))
        $this->wareTitle .= ", пл.".$wareDensity." г/кв.м";    


    if (!empty($producerRef)){
        $wareProdTitle = Yii::$app->db->createCommand('Select wareProdTitle from {{%ware_producer}} where id=:ref',
        [':ref' =>$producerRef ])->queryScalar();                
     $this->wareTitle .= ", пр-во ".$wareProdTitle;    
    }

   if (!empty($warePack))
        $this->wareTitle .= ", (".$warePack.")";    
      
  }

  public function loadWareSetPar()
  {
    if (!empty($this->format))
    {
     $strSql="SELECT  formatString, width,length FROM {{%ware_format}} where id =:refFormat";
     $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->format)])->queryOne();                
     
     if (empty($this->wareWidth)  && !empty($formatParam['width'])  )$this->wareWidth=$formatParam['width'];
     if (empty($this->wareLength) && !empty($formatParam['length']) )$this->wareLength=$formatParam['length']; 
    
    }
  
  
  }
  
  public function getWareProvider($params)
   {
   
    $query  = new Query();
    $query->select ([ '{{%ware_list}}.id',  
                      'wareTitle as goodTitle',
                      'wareEd as goodEd',
                      'wareDensity',
                      'wareFormat',
                      '{{%ware_list}}.isActive',
                      'isProduction',
                      'grpRef',
                      'wareStore',
                      'producerRef',
                      'wareGrpTitle',
                      'wareProdTitle',
                      'wareTypeName',
//                      'refWareEd',
                      'isConfirmed',
//                      'edTitle'
                      ])
            ->from("{{%ware_list}}")                        
//            ->leftJoin("{{%ware_ed_lnk}}","{{%ware_ed_lnk}}.refWareList= {{%ware_list}}.id")
//            ->leftJoin("{{%ware_ed}}","{{%ware_ed_lnk}}.refWareEd= {{%ware_ed}}.id")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_list}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_list}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_list}}.wareTypeRef")
            ->distinct()
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(({{%ware_list}}.id))")
            ->from("{{%ware_list}}")                        
//            ->leftJoin("{{%ware_ed_lnk}}","{{%ware_ed_lnk}}.refWareList= {{%ware_list}}.id")
//            ->leftJoin("{{%ware_ed}}","{{%ware_ed_lnk}}.refWareEd= {{%ware_ed}}.id")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_list}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_list}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_list}}.wareTypeRef")
            ;

        
    if (($this->load($params) && $this->validate())) {
    
     $query->andFilterWhere(['like', 'wareTitle', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'wareTitle', $this->goodTitle]);

    
     $query->andFilterWhere(['=', '{{%ware_ed_lnk}}.refWareEd', $this->edTitle]);
     $countquery->andFilterWhere(['=', '{{%ware_ed_lnk}}.refWareEd', $this->edTitle]);

    
     }
     
     if(!empty ($this->wareTypeName)){          
     $query->andFilterWhere(['=', '{{%ware_list}}.wareTypeRef', $this->wareTypeName]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareTypeRef', $this->wareTypeName]);
     }
     
     if(!empty ($this->wareGrpTitle)){          
     $query->andFilterWhere(['=', '{{%ware_list}}.grpRef', $this->wareGrpTitle]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.grpRef', $this->wareGrpTitle]);
     }

     if(!empty ($this->wareProdTitle)){          
     $query->andFilterWhere(['=', '{{%ware_list}}.producerRef', $this->wareProdTitle]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.producerRef', $this->wareProdTitle]);
     }
     
     if(!empty ($this->format)){       
     $formatList = $this->getWareFormat();
     $format=$formatList[$this->format];   
     $query->andFilterWhere(['=', '{{%ware_list}}.wareFormat', $format]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareFormat', $format]);
     }else{
     $query->andFilterWhere(['=', '{{%ware_list}}.wareWidth', $this->wareWidth]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareWidth', $this->wareWidth]);
     
     $query->andFilterWhere(['=', '{{%ware_list}}.wareLength', $this->wareLength]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareLength', $this->wareLength]);
     
     }
     
     if(!empty ($this->density)){          
     $query->andFilterWhere(['=', '{{%ware_list}}.wareDensity', $this->density]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareDensity', $this->density]);
     }

     if(!empty ($this->warePack)){          
     $query->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->warePack]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->warePack]);
     }

     if(!empty ($this->saleType)){          
     $saleType=$this->saleType-1;
     $query->andFilterWhere(['=', '{{%ware_list}}.isProduction', $saleType]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.isProduction', $saleType]);
     }
 
 
     if(!empty ($this->wareSort)){          
     $query->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->wareSort]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->wareSort]);
     }

     if(!empty ($this->wareMark)){          
     $query->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->wareMark]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->wareMark]);
     }

 
      
      
     $this->createNomenklature(
     $this->wareTypeName, $this->wareGrpTitle, $this->wareProdTitle, 
     $this->format, $this->wareWidth, $this->wareLength, $this->density, $this->warePack);        
          
     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', '{{%ware_list}}.isActive', 1]);
         $countquery->andFilterWhere(['=', '{{%ware_list}}.isActive', 1]);
       }  

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', '{{%ware_list}}.isActive', 0]);
         $countquery->andFilterWhere(['=', '{{%ware_list}}.isActive', 0]);
       }  
     }

     
     if (!empty ($this->isProduction))
     {
       if($this->isProduction == 1)
       {
         $query->andFilterWhere(['=', 'isProduction', 0]);
         $countquery->andFilterWhere(['=', 'isProduction', 0]);
       }  

       if($this->isProduction == 2)
       {
         $query->andFilterWhere(['=', 'isProduction', 1]);
         $countquery->andFilterWhere(['=', 'isProduction', 1]);
       }  
     }
     
//  $this->debug[]= $query->createCommand()->getRawSql();         
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
                  'goodTitle',
                  'edTitle',
                  'wareDensity',
                  'wareFormat',
                  'isActive',
                  'isProduction',
                  'wareGrpTitle',
                  'wareProdTitle' ,
                  'store',
                  'wareTypeName',
                  'isConfirmed'                  
                  ],            
            'defaultOrder' => [ 'goodTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   

   
/*****************/   
    public function saveWarehouseDetail()
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
           

           //все дубликаты с таким же названием
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
        case 'isProduction':           
            if ($record->isProduction == 0) $record->isProduction =1 ;
                                       else $record->isProduction =0 ;
            $record->save();                    
            $res['val'] =  $record->isProduction ;            
        break;            



        /*Обновим связанные наименования реализации*/
       Yii::$app->db->createCommand('UPDATE {{%ware_names}} SET
           wareTypeRef =:wareTypeRef,
           wareGrpRef =:grpRef,
           producerRef =:producerRef,
           wareListRef =:wareListRef
           WHERE warehouseRef = :warehouseRef',
           [
             ':grpRef' => $record->grpRef,
             ':wareTypeRef' => $record->wareTypeRef,
             ':producerRef' => $record->producerRef,
             ':wareListRef' => $record->wareListRef,
            ':warehouseRef' =>  $record->id,
           ])->execute();



        
        default:
            return $res;         
     }      
     




          
    $res['res'] = true;    
    return $res;
    }
/*****************/   

/*****************/   
    public function saveWareListDetail()
    {
      $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
             'debug' => '',
           ];   
           
    $res['debug'] = 'here';       
           
           
           
    switch ($this->dataType)
    {
        case 'isActive':           
            $record= TblWareList::findOne($this->recordId);
            if (empty($record)) return $res;

            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            $record->save();                    
            $res['val'] =  $record->isActive ;            
            $res['dataVal'] =  $record->wareTitle ;            
        break;            
        case 'isProduction':           
            $record= TblWareList::findOne($this->recordId);
            if (empty($record)) return $res;

            if ($record->isProduction == 0) $record->isProduction =1 ;
                                       else $record->isProduction =0 ;
            $record->save();                    
            $res['val'] =  $record->isProduction ;            
        break;            
        case 'isEdActive':
            $record= TblWareEdLnk::findOne([
            'refWareList' =>$this->recordId,
            'refWareEd' =>$this->dataVal,
            ]
            );
            if (empty($record)) {
            $record->refWareList = $this->recordId;
            $record->refWareEd   = $this->dataVal;
            $record->isActive    = 1;
            }
            else
            {

            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            }
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

    public function saveWareDetail()
    {
      $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'wareTitle' => $this->wareTitle,
             'grpRef' => $this->grpRef,
             'producerRef' => $this->producerRef,
             'density' => $this->density,
             'format' => $this->format,
             'wareTypeRef' => $this->wareTypeRef,
             'warePack' => $this->warePack,
             'wareWidth'=> $this->wareWidthGen,
             'wareLength'=> $this->wareLengthGen,
             'saleType'=> $this->saleTypeGen,
             'wareMark'=> $this->wareMarkGen,
             'wareSort'=> $this->wareSortGen,                         
             'val' => '',
           ];   


           
    switch ($this->dataType)
    {
        case 'createWare':
           $record= TblWareList::findOne(['wareTitle' => $this->wareTitle]);     
           if (empty($record) )
           {
            $record= new TblWareList();
            if (empty($record) ) return $res;           
            $record->wareTypeRef = intval($this->wareTypeRef);           
            $record->grpRef = intval($this->grpRef);           
            $record->producerRef = intval($this->producerRef);           
           
            $record->wareDensity = $this->density;           

            if (!empty( $this->format)){
                $formatList = $this->getWareFormat();
                $record->wareFormat = $formatList[$this->format];    
            }  
            
            $record->warePack = $this->warePack;                                  
            $record->wareTitle = $this->wareTitle;                       
            
            $record->wareWidth = $this->wareWidthGen;
            $record->wareLength = $this->wareLengthGen;
            
            $record->wareMark = $this->wareMarkGen;
            $record->wareSort = $this->wareSortGen;                         
            
            if ($this->saleTypeGen == 2 )  $record->isProduction =1;
            else  $record->isProduction =0;            
           }
           $record->isConfirmed=0;
           $record->isActive= 1;
           $record->save(); 
           $res['val'] =  $record->id ;
           Yii::$app->db->createCommand('INSERT INTO {{%ware_ed_lnk}} (refWareList,refWareEd, isActive)
           Select :wareRef, id, 1 from {{%ware_ed}}',
           [':wareRef' => $record->id])->execute();            
           break;
     }      

    $res['res'] = true;    
    return $res;
    }


/*****************/   
    public function saveWarehouseCfg()
    {
      $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
           
    switch ($this->dataType)
    {
        case 'wareTypeName':
        $record= TblWareType::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->wareTypeName = $this->dataVal;           
           $record->save(); 
           $res['val'] =  $record->wareTypeName ;
           break;
        case 'wareTypeAdd':
        $record= new TblWareType();     
        if (empty($record)) return $res;
        $record->save(); 
           break;
           
        case 'grpTypeRef':
        $record= TblWareGrp::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->wareTypeRef = intval($this->dataVal); 
           $record->save(); 
           $res['val'] =  $record->wareTypeRef ;
           break;
        case 'wareGrpTitle':
        $record= TblWareGrp::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->wareGrpTitle = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->wareTypeRef ;
           break;
        case 'wareGrpAdd':
        $record= new TblWareGrp();     
        if (empty($record)) return $res;
        $record->save(); 
           break;
           
        case 'wareProdTitle':
        $record= TblWareProducer::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->wareProdTitle = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->wareTypeRef ;
           break;
        case 'wareProdAdd':
        $record= new TblWareProducer();     
        if (empty($record)) return $res;
        $record->save(); 
           break;

        case 'formatString':
        $record= TblWareFormat::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->formatString = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->formatString ;
           break;
           
        case 'formatWidth':
        $record= TblWareFormat::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->width = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->width ;
           break;
           
        case 'formatLength':
        $record= TblWareFormat::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->length = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->length ;
           break;
           
        case 'formatIntSize':
        $record= TblWareFormat::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           $record->intSize = $this->dataVal; 
           $record->save(); 
           $res['val'] =  $record->intSize ;
           break;                      
           
        case 'formatIsProduct':
        $record= TblWareFormat::findOne(intval($this->recordId));     
        if (empty($record)) return $res;
           if($record->isProduct == 1)$record->isProduct = 0; 
                                 else $record->isProduct = 1;
           $record->save(); 
           $res['val'] =  $record->intSize ;
           break;                      
   
        case 'wareFormatAdd':
        $record= new TblWareFormat();     
        if (empty($record)) return $res;
        $record->save(); 
           break;
   
     }      
     
          
    $res['res'] = true;    
    return $res;
    }
/*****************/   
public $wareStat=[];
public function getStat()
  {
   
   $sqlStr = "(SELECT goodRef, MAX(isInUse) as u FROM 
    {{%ware_content}} left join {{%ware_use}} on {{%ware_content}}.useRef = {{%ware_use}}.id GROUP BY goodRef) as u";
            ;
    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
            ->from("{{%warehouse}}")            
            ->leftJoin("{{%ware_use}}","{{%ware_use}}.id= {{%warehouse}}.useRef")
            ;
     $countquery->andWhere(['=', 'ifnull(wareListRef,0)', 0]);            
     $countquery->andWhere(['=', '{{%warehouse}}.isProduction', 0]);
     $countquery->andWhere(['=', '{{%ware_use}}.isInUse', 1]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
   $this->wareStat['ErrNum'] = $countquery->createCommand()->queryScalar();       
                    
  }    
    
/*****************/   
public $syncDate="";
public $onDate="";
public $lastRef=0;
/***************************/ 

  public function getLastSync()
  {
   
   $refList = Yii::$app->db->createCommand('Select MAX(id) as ref, onDate, MAX(syncDate) as  syncDate   from {{%ware_header}} 
   where onDate <= NOW() GROUP BY onDate  ORDER BY onDate  DESC LIMIT 2')
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
  public function getWareScladColumns()
  {
   $list = Yii::$app->db->createCommand('Select id, scladTitle, useInSum from {{%ware_use}} where isInUse = 1 ORDER BY showOrder ASC, scladTitle , id')                    
                    ->queryAll();                
    if (empty($this->lastRef)) $this->getLastSync();   
    return $list;
  }
  

  public function getWareScladProvider($params)
   {
    
    $sqlStr = "(SELECT goodRef, MAX(isInUse) as u FROM 
    {{%ware_content}} left join {{%ware_use}} on {{%ware_content}}.useRef = {{%ware_use}}.id GROUP BY goodRef) as u";
    
    $query  = new Query();
    $query->select ([ '{{%warehouse}}.id',  
                      '{{%warehouse}}.title as goodTitle',
                      '{{%warehouse}}.ed as goodEd',
                      '{{%warehouse}}.amount as goodAmount',
                      '{{%warehouse}}.initPrice',
                      '{{%warehouse}}.isActive',
                      '{{%warehouse}}.useRef',
                      '{{%warehouse}}.wareTypeRef',
                      'wareTypeName',
                      '{{%warehouse}}.grpRef',
                      'wareGrpTitle',
                      '{{%warehouse}}.producerRef',
                      'wareProdTitle',                      
                      'wareListRef',                      
                      '{{%warehouse}}.wareDensity',
                      '{{%warehouse}}.wareFormat',
                      '{{%warehouse}}.wareEdRef',
                      '{{%warehouse}}.warePack',                  
                      ])
            ->from("{{%warehouse}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")                        
            ->leftJoin($sqlStr,"u.goodRef = {{%warehouse}}.id");               
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
    ->from("{{%warehouse}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")                   
            ->leftJoin($sqlStr,"u.goodRef = {{%warehouse}}.id");
            ;

    if(!empty($this->grpRef))
    {    
     $query->andWhere(['=', '{{%warehouse}}.grpRef', $this->grpRef]);
     $countquery->andWhere(['=', '{{%warehouse}}.grpRef', $this->grpRef]);    
    } 
                                  
                    
     $query->andWhere(['=', 'ifnull(u.u,0)', 1]);
     $countquery->andWhere(['=', 'ifnull(u.u,0)', 1]);

            
    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', '{{%warehouse}}.title', $this->goodTitle]);
     $countquery->andFilterWhere(['like', '{{%warehouse}}.title', $this->goodTitle]);
     
     $query->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     
     $query->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);

     $query->andFilterWhere(['=', '{{%warehouse}}.producerRef', $this->wareProdTitle]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.producerRef', $this->wareProdTitle]);
     
     }
     
    
    
     $scaldList = $this->getWareScladColumns();
    
    $n = count($scaldList);
    for ($i=0; $i<$n; $i++)
    {    
        $param="sclad_".$i;   
        if ($this->{$param} > 0)
        {
        $alias = "s".$i;        
        $sqlStr = "(Select goodAmount, goodRef from {{%ware_content}} where useRef = ".$scaldList[$i]['id']."  AND headerRef =".$this->lastRef.") as ".$alias;        
             $query->leftJoin($sqlStr, $alias.".goodRef = {{%warehouse}}.id");
        $countquery->leftJoin($sqlStr, $alias.".goodRef = {{%warehouse}}.id");
        
        switch ($this->{$param}) 
            {
            case 1:   
                $query->andFilterWhere(['>', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['>', $alias.'.goodAmount', 0]);
            break;
     
            case 2:   
                $query->andFilterWhere(['=', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['=', $alias.'.goodAmount', 0]);
            break;
     
            case 3:   
                $query->andFilterWhere(['<', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['<', $alias.'.goodAmount', 0]);
            break;
            }
        }    
    }

     
     
    switch ($this->goodAmount)
    {
        
     case 1:   
          $query->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     break;
     
     case 2:   
          $query->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     break;
     
     case 3:   
          $query->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     break;
    }
               
     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
       }  

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
       }  
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
                'goodTitle',
                'goodEd',
                'goodAmount',
                'initPrice',
                'isActive',
                'wareGrpTitle',
                'wareProdTitle',
                'wareTypeName',
                'wareDensity',
                'wareFormat',                  
            ],            
            'defaultOrder' => [ 'goodTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 

public function getWareScladEdList() 
{
   $list = Yii::$app->db->createCommand('Select DISTINCT ed from {{%warehouse}}')                    
                    ->queryAll();                
   
   $N = count($list);
   $res['Все']='Все'; 
   for ($i=0; $i<$N; $i++)
   {
     $ed= $list[$i]['ed'];
       $res[$ed]=$ed; 
    }   
   return $res;
}
 
public function getWareGrpScladProvider($params)
   {
    
    $sqlStr = "(SELECT goodRef, MAX(isInUse) as u FROM 
    {{%ware_content}} left join {{%ware_use}} on {{%ware_content}}.useRef = {{%ware_use}}.id GROUP BY goodRef) as u";
    
    $query  = new Query();
    $query->select ([ 
                      'sum({{%warehouse}}.amount) as goodAmount',
                      'MAX({{%warehouse}}.initPrice) as initPrice',
                      'wareTypeName',
                      '{{%warehouse}}.grpRef',
                      '{{%warehouse}}.ed as goodEd',
                      'wareGrpTitle',
                      ])
            ->from("{{%ware_grp}}")                                    
            ->leftJoin("{{%warehouse}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")                        
            ->groupBy("{{%ware_grp}}.id, {{%warehouse}}.ed")                        
 //->leftJoin($sqlStr,"u.goodRef = {{%warehouse}}.id");               
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%ware_grp}}.id) )")
            ->from("{{%ware_grp}}")                                    
            ->leftJoin("{{%warehouse}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")                        
//            ->leftJoin($sqlStr,"u.goodRef = {{%warehouse}}.id");               
            ;

                                  
                    
 /*    $query->andWhere(['=', 'ifnull(u.u,0)', 1]);
     $countquery->andWhere(['=', 'ifnull(u.u,0)', 1]);
*/
            
    if (($this->load($params) && $this->validate())) {

     
     $query->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.wareTypeRef', $this->wareTypeName]);
     
     $query->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.grpRef', $this->wareGrpTitle]);
     
     }
     
     if (empty($this->wareEd)) $this->wareEd = 'кг';
     if($this->wareEd != 'Все'){
         $query->andFilterWhere(['=', '{{%warehouse}}.ed', $this->wareEd]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.ed', $this->wareEd]);     
     }    
     
    
    $scaldList = $this->getWareScladColumns();
    
/*    $n = count($scaldList);
    for ($i=0; $i<$n; $i++)
    {    
        $param="sclad_".$i;   
        if ($this->{$param} > 0)
        {
        $alias = "s".$i;        
        $sqlStr = "(Select goodAmount, goodRef from {{%ware_content}} where useRef = ".$scaldList[$i]['id']."  AND headerRef =".$this->lastRef.") as ".$alias;        
             $query->leftJoin($sqlStr, $alias.".goodRef = {{%warehouse}}.id");
        $countquery->leftJoin($sqlStr, $alias.".goodRef = {{%warehouse}}.id");
        
        switch ($this->{$param}) 
            {
            case 1:   
                $query->andFilterWhere(['>', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['>', $alias.'.goodAmount', 0]);
            break;
     
            case 2:   
                $query->andFilterWhere(['=', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['=', $alias.'.goodAmount', 0]);
            break;
     
            case 3:   
                $query->andFilterWhere(['<', $alias.'.goodAmount', 0]);
                $countquery->andFilterWhere(['<', $alias.'.goodAmount', 0]);
            break;
            }
        }    
    }*/

     
    if (empty($this->goodAmount))$this->goodAmount=1; 
    switch ($this->goodAmount)
    {
        
     case 1:   
          $query->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['>', '{{%warehouse}}.amount', 0]);
     break;
     
     case 2:   
          $query->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['=', '{{%warehouse}}.amount', 0]);
     break;
     
     case 3:   
          $query->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     $countquery->andFilterWhere(['<', '{{%warehouse}}.amount', 0]);
     break;
    }
/*               
              
     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 1]);
       }  

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
         $countquery->andFilterWhere(['=', '{{%warehouse}}.isActive', 0]);
       }  
     }
 */    
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
                'goodAmount',
                'initPrice',
                'wareGrpTitle',
                'wareTypeName',
                'goodEd',
            ],            
            'defaultOrder' => [ 'wareGrpTitle' => 'SORT_ASC', 'goodEd'  => 'SORT_ASC'],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 

public function getWareSchetDetailProvider($params)
   {
    $query  = new Query();
    $query->select ([ 
                      '{{%schetContent}}.wareTitle',
                      '{{%schetContent}}.wareCount',
                      '{{%schetContent}}.wareEd',
                      '{{%schetContent}}.warePrice',
                      '{{%schetContent}}.refSchet',
                      '{{%warehouse}}.grpRef',
                      '{{%schet}}.schetNum',
                      '{{%schet}}.schetDate',
                      '{{%schet}}.refOrg',
                      '{{%orglist}}.title as orgTitle'
                      ])
            ->from("{{%schetContent}}")                                    
            ->leftJoin("{{%warehouse}}","{{%schetContent}}.warehouseRef= {{%warehouse}}.id")            
            ->leftJoin("{{%schet}}","{{%schet}}.id= {{%schetContent}}.refSchet")
            ->leftJoin("{{%orglist}}","{{%schet}}.refOrg= {{%orglist}}.id")                                                                                  
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%schetContent}}.id) )")
            ->from("{{%schetContent}}")                                    
            ->leftJoin("{{%warehouse}}","{{%schetContent}}.warehouseRef= {{%warehouse}}.id")            
            ->leftJoin("{{%schet}}","{{%schet}}.id= {{%schetContent}}.refSchet")                                         
            ;

                                  
   if (!empty($this->grpRef)){                 
          $query->andWhere(['=', 'grpRef', $this->grpRef]);
     $countquery->andWhere(['=', 'grpRef', $this->grpRef]);
   }
   switch ($this->state)
   {
     case 1:
          $query->andWhere('{{%schet}}.docStatus IN (1,2,3) and {{%schet}}.cashState=0 ');
     $countquery->andWhere('{{%schet}}.docStatus IN (1,2,3) and {{%schet}}.cashState=0 ');     
     break;
     
     case 2:
          $query->andWhere('{{%schet}}.docStatus =4 and {{%schet}}.cashState=0 ');
     $countquery->andWhere('{{%schet}}.docStatus =4 and {{%schet}}.cashState=0 ');     
     break;
     
     case 3:
          $query->andWhere('{{%schet}}.cashState IN (1,2)');
     $countquery->andWhere('{{%schet}}.cashState IN (1,2)');
     break;
   }          
            
            
    if (($this->load($params) && $this->validate())) {


     }
     
     
    
    $scaldList = $this->getWareScladColumns();
    
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
                      'wareTitle',
                      'wareCount',
                      'wareEd',
                      'warePrice',
                      'schetNum',
                      'schetDate',
                      'orgTitle'
             ],            
            'defaultOrder' => [ 'schetNum'  => 'SORT_ASC'],            
            ],
            
        ]);
    return  $dataProvider;   
   }   



/***********/ 



/**/    
 }
 
