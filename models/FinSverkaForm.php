<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;        

use app\models\TblControlSverkaHeader;
use app\models\TblControlSverkaDolga;
use app\models\TblControlSverkaUse;
use app\models\TblControlSverkaFilter;

/**
 * FinSverkaForm - отображение и настройка сверки долга
 */
class FinSverkaForm extends Model
{
    
    public $count;
    public $command;
    public $query;


    public $balanceSum = array();
    
    public $syncDateTime;
    public $strDate;
    public $sumValue;
    
    public $isFilter;
    public $owerOrgTitle;
    public $orgTitle;
    
    public $isInUse=0;
    public $isClient=0;
    public $isSupplier=0;

    public $isBlack=0;    
    public $isOther=0;
    public $isService=0;
    public $isBank=0;

    public $dataValue;
    public $dataFix = 1;
    public $dataRequestId;
    public $dataId;


    public $useAll = 0;
    public $ftType ='showAll';

    public $col0;
    public $col1;
    public $col2;
    public $col3;
    public $col4;
    public $col5;
    public $col6;
    public $col7;
    public $col8;
    public $col9;
    public $col10;
        
    public $orgFlt = 0;
    public $fltOrgTitle = "Список организаций";
    
    public $debug=array();
    
    public function rules()
    {
        return [
            [['dataValue', 'dataFix', 'dataRequestId', 'dataId' ,'strDate' ], 'default'],
            [['owerOrgTitle','goodTitle', 'isFilter', 'orgTitle',
            'col1','col2','col3','col4','col5','col6','col7','col8','col9','col0',
            ], 'safe'],
        ];
    }
    

/***************************/     
// Переключаем использование организаций собственника
  public function switchSverkaFlt($id)  
   {
       $record= TblControlSverkaFilter::findOne($id);      
       if (empty($record)) return false;
       if ($record->isFilter == 1)$record->isFilter = 0;
       else                       $record->isFilter = 1;
       $record->save();
       return true;
   }  

// Переносим сумму в другой столбец
/*
    public $dataValue - сумма
    public $dataFix  - изменить по умолчанию
    public $dataRequestId - новый тип записи для организации
    public $dataId - id исходной записи

*/   
  public function  saveSverkaRecord ()
   {
       // Найдем запись
      $res = [ 'res' => false ,
                'dataId' => $this->dataId,   
                'val' => $this->dataValue,
                'dataRequestId'  => $this->dataRequestId,
                'dataFix'   => $this->dataFix
            ];      
      $record= TblControlSverkaDolga::findOne($this->dataId);      
      if (empty($record)) return $res; // нет такой - дальше бессмысленно
      //если всю сумму, то просто меняем тип
      if ($record->balanceSum == $this->dataValue )
      {
         $record->typeRef =  $this->dataRequestId;
         $record->save(); 
      }else
      {
        $record->balanceSum = $record->balanceSum -$this->dataValue;  
        $record->save();
        //Ищем уже имеющийся перенос
        $newRecord= TblControlSverkaDolga::findOne([
        'refOrg' => $record->refOrg,
        'headerRef' => $record->headerRef,
        'useRef' => $record->useRef,
        'typeRef' => $this->dataRequestId,
        ]);      
        if(empty($newRecord)){            
            //тогда новая запись
        $newRecord= new TblControlSverkaDolga();
        $newRecord->refOrg    = $record->refOrg;
        $newRecord->headerRef = $record->headerRef;
        $newRecord->useRef    = $record->useRef;
        $newRecord->typeRef   = $this->dataRequestId;
        $newRecord->usedOrgTitle = $record->usedOrgTitle;
        $newRecord->dogType      = $record->dogType;
        $newRecord->orgTitle     = $record->orgTitle;
        $newRecord->orgINN       = $record->orgINN;
        $newRecord->orgKPP       = $record->orgKPP;
        $newRecord->syncDate     = $record->syncDate;
        $newRecord->balanceSum =0;
        }
        $newRecord->balanceSum = $newRecord->balanceSum + $this->dataValue;     
        $newRecord->save();
      }
      // Если запомнить перенос
      if ($this->dataFix)
      {
        $recordUse= TblControlSverkaUse::findOne($record->useRef);        
        if (empty($recordUse)){$res['err']='No useRef'; $res['useRef']=$record->useRef; return $res;}
        $recordUse -> typeRef = $this->dataRequestId; 
        $recordUse -> save();  
      
        /*Yii::$app->db->createCommand(
            'UPDATE {{%control_sverka_dolga}} SET 
                   typeRef   = :typeRef
            WHERE useRef = :useRef', 
            [ ':typeRef' => $recordUse -> typeRef, ':useRef' => $recordUse -> id ])->execute();        
        */    
        }
     $res['res'] = true;
     return $res;
   }    
      
          
  /***************************/ 
  public function getSverkaFltProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_sverka_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%control_sverka_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        

            
  if (!empty($this->orgFlt))        
  {  
  $this->fltOrgTitle=  Yii::$app->db->createCommand(
            'SELECT owerOrgTitle FROM {{%control_sverka_filter}} WHERE id =:fltRef', 
            [ ':fltRef' => $this->orgFlt, ])->queryScalar();        
  
  }
            
                   
    $query  = new Query();
    $query->select ([ '{{%control_sverka_filter}}.id',  
                      'owerOrgTitle', 
                      'isFilter',
                      'ifnull(bSum,0) as balanceSum',
                      ])
            ->from("{{%control_sverka_filter}}")
            ->leftJoin("(SELECT SUM(balanceSum) as bSum, fltRef from {{%control_sverka_dolga}} as a, {{%control_sverka_dolga_use}} as b 
                         where a.useRef = b.id and a.headerRef = ".$headerRef." and a.isBlack = 0 group by fltRef) as c", "c.fltRef = {{%control_sverka_filter}}.id")
            ->distinct()
            ;
        
        
    $countquery  = new Query();
    $countquery->select (" count({{%control_sverka_filter}}.id)")
            ->from("{{%control_sverka_filter}}")            
            ;

            
    if (($this->load($params) && $this->validate())) {
        
     $query->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
     $countquery->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
     }
          
     if (empty ($this->isFilter)) $this->isFilter = 2;         
       if($this->isFilter == 2)
       {
         $query->andFilterWhere(['=', 'isFilter', 1]);
         $countquery->andFilterWhere(['=', 'isFilter', 1]);
       }  
       if($this->isFilter == 3)
       {
         $query->andFilterWhere(['=', 'isFilter', 0]);
         $countquery->andFilterWhere(['=', 'isFilter', 0]);
       }  
           
          
          
          
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    //$this->sumValue =  $sumquery->createCommand()->queryScalar();                                     
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'owerOrgTitle', 
                      'isFilter',
                      'balanceSum',
            ],
            
            'defaultOrder' => [ 'owerOrgTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/***********/
  public function getSverkaProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_sverka_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%control_sverka_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $list = Yii::$app->db->createCommand('SELECT id FROM {{%doc_type}} ORDER BY id')->queryAll();      
      
       
    $strSql = "(SELECT id as sverkaRef, balanceSum, useRef, typeRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef.") as c";  
       
       
    $query  = new Query();
    $query->select ([
                     'u.id',
                     'u.orgTitle',
                     'fu.orgRef',                    
                     'u.orgINN',
                     'u.orgKPP',
                     'fu.fltRef',
                     'u.id as sverkaRef', 
                     'u.balanceSum',
                     'u.typeRef',
                     ])
                    ->from("{{%control_sverka_dolga}} as u")
                    ->leftJoin("{{%control_sverka_dolga_use}} as fu","fu.id= u.useRef")                        
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= fu.fltRef")                        
                       ->distinct()
            ;
        
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(u.id))")
                    ->from("{{%control_sverka_dolga}} as u")
                    ->leftJoin("{{%control_sverka_dolga_use}} as fu","fu.id= u.useRef")                        
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= fu.fltRef")                        
                    ->leftJoin($strSql, "c.useRef = u.id")
            ;

    $query->andWhere("f.isFilter = 1");
    $countquery->andWhere("f.isFilter = 1") ;            

    $query->andWhere("u.headerRef = ".$headerRef );
    $countquery->andWhere("u.headerRef = ".$headerRef) ;            

   
   if (($this->load($params) && $this->validate())) {
                 
     $query->andFilterWhere(['like', 'u.orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);

     /*фильтруем по всем колонкам*/   
     for ($i=0;$i<count($list);$i++)  
        {            
            $col='col'.$i;
            if (!isset($params['FinSverkaForm'][$col])) continue;
            $this->ftType = 'fltAll';
            if ($params['FinSverkaForm'][$col] == 1){
                $query->andFilterWhere(['=', 'u.typeRef', $list[$i]['id']]);
                $countquery->andFilterWhere(['=', 'u.typeRef', $list[$i]['id']]);     
            }   
            if ($params['FinSverkaForm'][$col] == -1){                
                $query->andFilterWhere(['<>', 'u.typeRef', $list[$i]['id']]);
                $countquery->andFilterWhere(['<>', 'u.typeRef', $list[$i]['id']]);     
            }          
        }    
   }
     
   switch($this->ftType)
   {
      case 'isBank':
                $query->andFilterWhere(['=', 'u.typeRef', 3]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 3]);     
      break;  
      
      case 'isClientP':
                $query->andFilterWhere(['=', 'u.typeRef', 1]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 1]);     
                $query->andFilterWhere(['>', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['>', 'u.balanceSum', 0]);     
      break;  
      case 'isClientM':
                $query->andFilterWhere(['=', 'u.typeRef', 1]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 1]);     
                $query->andFilterWhere(['<', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['<', 'u.balanceSum', 0]);     
      break;  

      case 'isWareP':
                $query->andFilterWhere(['=', 'u.typeRef', 2]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 2]);     
                $query->andFilterWhere(['>', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['>', 'u.balanceSum', 0]);     
      break;  
      case 'isWareM':
                $query->andFilterWhere(['=', 'u.typeRef', 2]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 2]);     
                $query->andFilterWhere(['<', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['<', 'u.balanceSum', 0]);     
      break;  
      case 'isServiceP':
                $query->andFilterWhere(['=', 'u.typeRef', 4]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 4]);     
                $query->andFilterWhere(['>', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['>', 'u.balanceSum', 0]);     
      break;  
      case 'isServiceM':
                $query->andFilterWhere(['=', 'u.typeRef', 4]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 4]);     
                $query->andFilterWhere(['<', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['<', 'u.balanceSum', 0]);     
      break;  
       
      case 'isOtherP':
                $query->andFilterWhere(['=', 'u.typeRef', 5]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 5]);     
                $query->andFilterWhere(['>', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['>', 'u.balanceSum', 0]);     
      break;  
      case 'isOtherM':
                $query->andFilterWhere(['=', 'u.typeRef', 5]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 5]);     
                $query->andFilterWhere(['<', 'u.balanceSum', 0]);
                $countquery->andFilterWhere(['<', 'u.balanceSum', 0]);     
      break;  
       
      case 'isMove':
                $query->andFilterWhere(['=', 'u.typeRef', 6]);
                $countquery->andFilterWhere(['=', 'u.typeRef', 6]);     
      break;  
       
       
   }
     
 //  $this->debug[] = $params['FinSverkaForm'];
  
  for ($i=0;$i<count($list);$i++)  
    $this->balanceSum [$list[$i]['id']] = 0;        
    
   $qlist = $query->createCommand()->queryAll();
    $N=count($qlist);
    for($i=0;$i<$N;$i++)
    {
      $this->balanceSum[$qlist[$i]['typeRef']]+=$qlist[$i]['balanceSum'];      
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
                     'orgTitle',
                     'balanceSum',
            ],
            
            'defaultOrder' => [ 'orgTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/********************/
/*
UPDATE `rik_control_sverka_dolga` set typeRef = 1 where   isInUse = 1;
UPDATE `rik_control_sverka_dolga` set typeRef = 2 where   isInUse = 1 and balanceSum < 0;
UPDATE `rik_control_sverka_dolga` set typeRef = 3 where   isBank = 1;
UPDATE `rik_control_sverka_dolga` set typeRef = 4 where   isService = 1;
UPDATE `rik_control_sverka_dolga` set typeRef = 5 where   isOther = 1;
UPDATE `rik_control_sverka_dolga` set typeRef = 7 where   isBlack = 1;

UPDATE `rik_control_sverka_dolga_use` set typeRef = 1 where  isInUse = 1;
UPDATE `rik_control_sverka_dolga_use` set typeRef = 3 where  isBank = 1;
UPDATE `rik_control_sverka_dolga_use` set typeRef = 4 where  isService = 1;
UPDATE `rik_control_sverka_dolga_use` set typeRef = 5 where  isOther = 1;
UPDATE `rik_control_sverka_dolga_use` set typeRef = 7 where  isBlack = 1;


UPDATE rik_control_sverka_dolga_use, rik_control_sverka_dolga 
set rik_control_sverka_dolga_use.typeRef = 2 
where  
rik_control_sverka_dolga_use.id = rik_control_sverka_dolga.useRef 
and rik_control_sverka_dolga.typeRef = 2


ALTER TABLE `rik_control_sverka_dolga` ADD INDEX `rik_control_sverka_dolga_idx1` (`refOrg`);
ALTER TABLE `rik_control_sverka_dolga` ADD INDEX `rik_control_sverka_dolga_idx2` (`headerRef`);
ALTER TABLE `rik_control_sverka_dolga` ADD INDEX `rik_control_sverka_dolga_idx3` (`useRef`);
ALTER TABLE `rik_control_sverka_dolga` ADD INDEX `rik_control_sverka_dolga_idx4` (`typeRef`);
ALTER TABLE `rik_control_sverka_dolga_use` ADD INDEX `rik_control_sverka_dolga__use_idx1` (`typeRef`);
ALTER TABLE `rik_control_sverka_header` ADD INDEX `rik_control_sverka_header_idx1` (`onDate`);
*/
  public function getSverkaColumns()
  {  
   $columns= array();
  
  $now =strtotime($this->strDate);
  
  $columns[] =[
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){                
                return "<a href='#' onclick='openClient(".$model['orgRef'].")'>".$model['orgTitle']."</a>";
                }                
            ];

$list = Yii::$app->db->createCommand('SELECT id, typeTitle  FROM {{%doc_type}} ORDER BY id')->queryAll();      
  for ($i=0; $i<count($list); $i++)
  {
    $typeRef=$list[$i]['id'];  
    $typeTitle=$list[$i]['typeTitle'];  
    $title = preg_replace("/\s+/u", "<br>", $list[$i]['typeTitle']);
    $columns[] = [
                'attribute' => 'col'.$i,
                'label' => $title,
                'encodeLabel' =>false,
                'filter' => [0 => 'Все', 1 => 'Да', -1 => 'Нет'],
                'format' => 'raw',
                'footer' => '<div align="center">'.number_format($this->balanceSum[$typeRef],2,'.','&nbsp;').'</div>',                
                'value' => function ($model, $key, $index, $column) use($typeRef, $typeTitle) {                
                
                    if ($model['typeRef'] == $typeRef) 
                    return number_format($model['balanceSum'],2,'.','&nbsp;'); 
                    $action='setAsUse('.$model['sverkaRef'].','.$typeRef.','.$model['balanceSum'].',"'.$typeTitle.'")';    
                    return "<div style='text-align:right'><a href='#' onclick='".$action."' >&nbsp;.&nbsp;.&nbsp;.&nbsp;.&nbsp;.&nbsp;&nbsp;.&nbsp;.&nbsp;.&nbsp;.&nbsp;.&nbsp;</a></div>";                
                }                
                
            ];
      
  }
  
   return $columns;
  } 

/*************/
  public function getSverkaOldProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_sverka_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%control_sverka_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $list = Yii::$app->db->createCommand('SELECT id FROM {{%doc_type}} ORDER BY id')->queryAll();      
      
       
    $strSql = "(SELECT id as sverkaRef, balanceSum, useRef, typeRef from {{%control_sverka_dolga}} 
                         where headerRef = ".$headerRef.") as c";  
       
       
    $query  = new Query();
    $query->select ([
                     'u.id',
                     'u.orgTitle',
                     'u.orgRef',                    
                     'u.orgINN',
                     'u.orgKPP',
                     'u.fltRef',
                     'c.sverkaRef', 
                     'c.balanceSum',
                     'c.typeRef',
                     ])
                    ->from("{{%control_sverka_dolga}} as u")
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= u.fltRef")                        
                    ->leftJoin($strSql, "c.useRef = u.id")
                    ->distinct()
            ;
        
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(u.id))")
            ->from("{{%control_sverka_dolga_use}} as u")            
                    ->leftJoin("{{%control_sverka_filter}} as f","f.id= u.fltRef")                        
                    ->leftJoin($strSql, "c.useRef = u.id")
            ;

    $query->andWhere("f.isFilter = 1");
    $countquery->andWhere("f.isFilter = 1") ;            
  
  if (empty($this->useAll))        
  {
             $query->andWhere("ifnull(c.sverkaRef,0) > 0");
        $countquery->andWhere("ifnull(c.sverkaRef,0) > 0");
   }
   

   if (($this->load($params) && $this->validate())) {
                 
     $query->andFilterWhere(['like', 'u.orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);

     /*фильтруем по всем колонкам*/   
     for ($i=0;$i<count($list);$i++)  
        {            
            $col='col'.$i;
            if (!isset($params['FinSverkaForm'][$col])) continue;
            if ($params['FinSverkaForm'][$col] == 1){
                $query->andFilterWhere(['=', 'c.typeRef', $list[$i]['id']]);
                $countquery->andFilterWhere(['=', 'c.typeRef', $list[$i]['id']]);     
            }   
            if ($params['FinSverkaForm'][$col] == -1){
                $query->andFilterWhere(['<>', 'c.typeRef', $list[$i]['id']]);
                $countquery->andFilterWhere(['<>', 'c.typeRef', $list[$i]['id']]);     
            }          
        }    
   }
     
 //  $this->debug[] = $params['FinSverkaForm'];
  
  for ($i=0;$i<count($list);$i++)  
    $this->balanceSum [$list[$i]['id']] = 0;        
    
/*   $qlist = $query->createCommand()->queryAll();
    $N=count($qlist);
    for($i=0;$i<$N;$i++)
    {
      $this->balanceSum[$qlist[$i]['typeRef']]+=$qlist[$i]['balanceSum'];      
    }    
  */  
          
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
                     'orgTitle',
                     'balanceSum',
            ],
            
            'defaultOrder' => [ 'orgTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   


 /** end of object **/    
 }

 
 
 
 
