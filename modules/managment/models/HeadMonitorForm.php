<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 


use app\modules\bank\models\BuhStatistics;

use app\modules\managment\models\TblMonitorRow;
use app\modules\managment\models\TblMonitorRowCfg;


/**
 * HeadMonitorForm  - монитор собственника
 */


class HeadMonitorForm extends Model
{

    public $id=0;
    

    public $stDate=0;
    public $enDate=0;
    public $syncDateTime=0;
           
    public $rowRef    = 0;       
    public $rowTitle    = "Новый параметр";
    public $rowType = 0;

    public $debug=[];   
    public $err=[];   
     
    public $dataRequestId="";
    public $dataRowId="";
    public $dataType="";
    public $dataVal ="";
    public $dataValType =0;
    
    public $orgTitle ="";
    public $owerOrgTitle ="";
    public $multFlt = 0;
    public $multOwn = 0;
    
    public $stTime = 0;
    public $enTime = 0;  

    
    public $globalSum=array();
    
    public $ownerRowN = 20; //число отображаемых строк в списке организаций собственника
    
    public $rowSum =0; 
        
    public function rules()
    {
        return [
              [['rowTitle', 'rowType', 'dataRequestId', 'dataRowId', 'dataType', 'dataVal', 'dataValType'], 'default'],
              [[ 'orgTitle', 'owerOrgTitle', 'multOwn', 'multFlt'], 'safe'],
        ];
    }
    
   /***************************/     
   /* Считаем значение строки 
   ***************************/
  public function getTest($rowType, $rowRef) 
  {
      
    return $this->rowType." ".$rowRef;  
  }
  public function calcRow($rowHeaderRef, $rowId)
  {
    //определим стартовую и конечную дату в unixtime
    
    $this->stTime = strtotime($this->stDate);
    $this->enTime = strtotime($this->enDate);

    if (empty($this->globalSum[$rowId]))$this->globalSum[$rowId] = 0;
    //Итого у нас 3 источника, прибыль, ДДС, закупки
    
    $sum=0;
    
    $sum+=$this->calcRowProfit($rowHeaderRef, $rowId);
    
    //Источник 2 - ДДС     
    $sum+=$this->calcRowDDS($rowHeaderRef, $rowId);
    
    //Источник 3 - закупки 
    $sum+=$this->calcRowPurch($rowHeaderRef, $rowId);

        $res['vs']=0;
        $res['ve']=0;
        $res['v']=$sum;          
    return $res;    
  }

/********************/  
  public function calcRowProfit($rowHeaderRef, $rowId)
  {
      
    $stTime = $this->stTime;
    $enTime = $this->enTime;
  
      //Получим общие настройки 
    $cfgList = Yii::$app->db->createCommand(
            'SELECT id, srcType, mult, filteString, filterRef, valType  from {{%monitor_row_cfg}} WHERE rowHeaderRef =:rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
    $Ncfg = count ($cfgList);
    $sum = 0;  
      
        /*Источник 1 - прибыль*/
    
    //Получим тип данных 
    //он же колонка    
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef, mult from {{%monitor_row_cfg}} WHERE srcType = 7 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
            
//$this->debug[] = $dataValType;           
    $Nvtype = count($dataValType); 
    //******* Соберем данные по прибыли ***********
    // для этого суммируем колонку определяемую  valType 
    // для всех источников {{profit_content}} {{profit_header}} в заданном периоде
    
    //сформируем условие для отбора положительных и отрицательных значений по собственнику
    $condP ="-1,";
    $condM ="-1,";
    for ($i=0; $i<$Ncfg; $i++)
    {
        if ($cfgList[$i]['srcType'] != 1) continue;        
        if ($cfgList[$i]['mult'] > 0) $condP .= $cfgList[$i]['filterRef'].",";
        if ($cfgList[$i]['mult'] < 0) $condM .= $cfgList[$i]['filterRef'].",";
    }
    //уберем лишнюю запятую
    //    if ($condP !="") $condP_profit = mb_substr($condP, 0, -1, 'utf-8');
    //    if ($condM !="") $condM_profit = mb_substr($condM, 0, -1, 'utf-8');
    if ($condP !="") $condP = substr($condP, 0, -1);
    if ($condM !="") $condM = substr($condM, 0, -1);
    
    //Соберем запрос
    //Пройдем по периодам

    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {
        //Последний заголовок
        $headerRef= Yii::$app->db->createCommand(
            'SELECT max(id) from {{%profit_header}} WHERE onDate =:onDate', 
            [':onDate' => date('Y-m-d', $ct) ])->queryScalar();  
        if (empty($headerRef))continue;    
        
       $sumP=0;
       $sumM=0;       
       for ($j=0; $j< $Nvtype; $j++)
       {          
       $dataType = $dataValType[$j]['filterRef'];  
     
        switch ($dataType){
        case 2:
        //число подозрительных значений
        $strSql="SELECT count(id) from {{%profit_content}} where headerRef= :headerRef and (profitability>60)";
        break;
        case 3:
        //сумма подозрительных значений
        $strSql="SELECT sum(profit) from {{%profit_content}} where headerRef= :headerRef and (profitability>60)";        
        break;
        case 4:
        //сумма по себестоимости
        $strSql="SELECT sum(initPrice) from {{%profit_content}} where headerRef= :headerRef ";        
        break;

        case 5:
        //стоимость продажи *goodAmount
        $strSql="SELECT sum(sellPrice) from {{%profit_content}} where headerRef= :headerRef ";        
        break;
        
        default:
        //По умолчанию маржа
        $strSql="SELECT sum(profit) from {{%profit_content}} where headerRef= :headerRef";
       }
        /*Для организаций в плюсе*/   
        if ($condP !="")            
        {
            $s = Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condP.")", 
                [':headerRef' => $headerRef ])->queryScalar();  
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumP+=$s;
        }    
        /*Для организаций в минусе*/   
        if ($condM !="")
        {
            $s =Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condM.")", 
            [':headerRef' => $headerRef ])->queryScalar();  
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumM+=$s;            
        }      
       }    
       $sum += ($sumP - $sumM); //соберем со всех организаций
    }    // по всем периодам
  
//     $this->debug[]=$rowHeaderRef." ".$rowId." ".$sum;     
     return  $sum;
  }
/***************/  
  public function calcRowDDS($rowHeaderRef, $rowId)
  {
      
    $stTime = $this->stTime;
    $enTime = $this->enTime;  
      //Получим общие настройки 
    $cfgList = Yii::$app->db->createCommand(
            'SELECT id, srcType, mult, filteString, filterRef, valType  from {{%monitor_row_cfg}} WHERE rowHeaderRef =:rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
    $Ncfg = count ($cfgList);
    $sum = 0;  
    
    //Источник 2 - ДДС 
    //******* Соберем данные по движению банковских средств тип 2 ***********
    // для этого суммируем колонку определяемую  valType 
    // для всех источников {{bank_operation}} <- {{bank_op_content}} {{bank_op_header}} в заданном периоде
    
    //Получим тип данных он же статьи
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef, mult from {{%monitor_row_cfg}} WHERE srcType = 2 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
    $Nvtype = count($dataValType); 
    //******* Соберем данные ***********    
    //сформируем условие для отбора положительных и отрицательных значений
    $condPOrg ="-1,";
    $condMOrg ="-1,";
    
    for ($i=0; $i<$Ncfg; $i++)
    {
        if ($cfgList[$i]['srcType'] != 8) continue;        
        if ($cfgList[$i]['mult'] > 0) $condPOrg .= $cfgList[$i]['filterRef'].",";
        if ($cfgList[$i]['mult'] < 0) $condMOrg .= $cfgList[$i]['filterRef'].",";
    }    
    //уберем лишнюю запятую
    if ($condPOrg !="") $condPOrg = substr($condPOrg, 0, -1);
    if ($condMOrg !="") $condMOrg = substr($condMOrg, 0, -1);

    //Соберем запрос
    //Пройдем по периодам
    $strSql="SELECT sum(recordSum) from {{%bank_operation}} where regDate= :operationDate and articleRef=:articleRef";
    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {        
       $sumP=0;
       $sumM=0;       
       // По всем статьям
       for ($j=0; $j< $Nvtype; $j++)
       {   
        if ($dataValType[$j]['mult'] == 0) continue;  
        $dataType = $dataValType[$j]['filterRef'];               
        /*Для организаций в плюсе*/   
        if (!empty($condPOrg ) && $condPOrg !="-1")            
        {
            $s = Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condPOrg.")", 
                [':operationDate' => date('Y-m-d', $ct), 
                 ':articleRef' => $dataType, 
                ])->queryScalar();  //получим сумму по статье за день
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumP+=$s;
        }    
        /*Для организаций в минусе*/   
        if (!empty($condMOrg ) && $condMOrg !="-1")            
        {         
            $s = Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condMOrg.")", 
                [':operationDate' => date('Y-m-d', $ct), 
                 ':articleRef' => $dataType, 
                ])->queryScalar();  //получим сумму по статье за день
                
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumM+=$s;                   
        }      
       }    
       $sum += ($sumP - $sumM); //соберем со всех организаций
    }    // по всем периодам

    
    
    return $sum;
  }  
/***************/    
/***************/  
  public function calcRowPurch($rowHeaderRef, $rowId)
  {
      
    $stTime = $this->stTime;
    $enTime = $this->enTime;  
      //Получим общие настройки 
    $cfgList = Yii::$app->db->createCommand(
            'SELECT id, srcType, mult, filteString, filterRef, valType  from {{%monitor_row_cfg}} WHERE rowHeaderRef =:rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
    $Ncfg = count ($cfgList);      
    $sum = 0;  
    
      //Источник 3 - закупки 
    // собственник - 5
    // тип статьи  - 6
    //******* Соберем данные по движению банковских средств тип 2 ***********
    // для этого суммируем колонку определяемую  valType 
    // для всех источников {{control_purch_content}} {{control_purch_header}} в заданном периоде
    
    //Получим тип данных он же статьи
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef, mult from {{%monitor_row_cfg}} WHERE srcType = 6 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowHeaderRef), ])->queryAll();  
    $Nvtype = count($dataValType); 
    //******* Соберем данные ***********    
    //сформируем условие для отбора положительных и отрицательных значений
    $condPOrg ="-1,";
    $condMOrg ="-1,";
    
    for ($i=0; $i<$Ncfg; $i++)
    {
        if ($cfgList[$i]['srcType'] != 5) continue;        
        if ($cfgList[$i]['mult'] > 0) $condPOrg .= $cfgList[$i]['filterRef'].",";
        if ($cfgList[$i]['mult'] < 0) $condMOrg .= $cfgList[$i]['filterRef'].",";
    }
    //уберем лишнюю запятую
    if ($condPOrg !="") $condPOrg = substr($condPOrg, 0, -1);
    if ($condMOrg !="") $condMOrg = substr($condMOrg, 0, -1);
    
    //Соберем запрос
    //Пройдем по периодам
    $strSql="SELECT sum(purchSum) from {{%control_purch_content}} where headerRef= :headerRef and typeRef=:typeRef";
    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {        

    //Последний заголовок
        $headerRef= Yii::$app->db->createCommand(
            'SELECT max(id) from {{%control_purch_header}} WHERE onDate =:onDate', 
            [':onDate' => date('Y-m-d', $ct) ])->queryScalar();  
        if (empty($headerRef))continue;    

       $sumP=0;
       $sumM=0;       
       // По всем статьям
       for ($j=0; $j< $Nvtype; $j++)
       {         
        if ($dataValType[$j]['mult'] == 0) continue;     
        $dataType = $dataValType[$j]['filterRef'];               
        /*Для организаций в плюсе*/   
        if (!empty($condPOrg ) && $condPOrg !="-1")            
        {
            $s = Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condPOrg.")", 
                [':headerRef' => $headerRef, 
                 ':typeRef' => $dataType, 
                ])->queryScalar();  //получим сумму по статье за день
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumP+=$s;
        }    
        /*Для организаций в минусе*/   
        if (!empty($condMOrg ) && $condMOrg !="-1")            
        {
            $s = Yii::$app->db->createCommand($strSql." AND ownerOrgRef IN (".$condMOrg.")", 
                [':headerRef' => $headerRef, 
                 ':typeRef' => $dataType, 
                ])->queryScalar();  //получим сумму по статье за день
            if (empty($s))$s=0;    
            $s=$s*$dataValType[$j]['mult']; // и умножим на множитель показателя
            $sumM+=$s;            
        }      
       }    
       $sum += ($sumP - $sumM); //соберем со всех организаций
    }    // по всем периодам
    
    return $sum;
  }
  
/***************/    
/***************/    
  public function getMonitorDetailRow($rowId)  
  {
     $list = Yii::$app->db->createCommand(
            'SELECT id, rowTitle, isMark, mult from {{%monitor_row}} WHERE rowType =:key', 
            [':key' => intval($rowId), ])->queryAll();  
 
     $N = count ($list);
     $strRes = "";     
     $strRes .= "<tr>\n";         
     $strRes .= "<th>Параметр</th>";
     $strRes .= "<th> </th>";
     $strRes .= "<th> </th>";
     $strRes .= "<th>Изменение</th>";
     $strRes .= "</tr>\n";         
     
     $this->rowSum = 0;
     for ($i=0;$i<$N; $i++)
     {
         
        $calcArray = $this->calcRow($list[$i]['id'], $rowId); 
        $list[$i]['vs']=$calcArray['vs'];
        $list[$i]['ve']=$calcArray['ve'];
        $list[$i]['v']=$calcArray['v'];
        
        $this->rowSum += $calcArray['v']*$list[$i]['mult'];
         
        $strRes .= "<tr>\n";         
        /*Название*/
        $strRes .= "<td>";
        if ($list[$i]['isMark'] == 1) $strRes .= "<div class='marked'>";
                               else $strRes .= "<div class='simple'>";        
        $strRes .= $list[$i]['rowTitle'];
        $strRes .= "</div>";                  
        $strRes .= "</td>\n";
        
        /*На начало периода*/
/*        if ($list[$i]['vs'] > 0) $vs = number_format($list[$i]['vs'],2,'.','&nbsp;');
                            else $vs = "&nbsp;";
        $strRes .= "<td>".$vs."</td>\n"; */           
        
        
        /*На конец периода периода*/        
/*        if ($list[$i]['ve'] > 0) $ve = number_format($list[$i]['ve'],2,'.','&nbsp;');
                            else $ve = "&nbsp;";

        $strRes .= "<td>".$ve."</td>\n"; */

        $strRes .= "<td colspan=2>";

        //$action ='openWin("/managment/head/show-profit-by-row&noframe=1&stDate='.$this->stDate.'&enDate='.$this->enDate.'&rowRef='.$list[$i]['id'].'","detailWin");';         
        //$strRes .= "<span onclick='".$action."' title='Прибыль' class='glyphicon glyphicon-usd clickable'></span>&nbsp;&nbsp;\n";

//        $action ='openWin("/managment/head/show-bank-op-by-row&noframe=1&stDate='.$this->stDate.'&enDate='.$this->enDate.'&rowRef='.$list[$i]['id'].'","detailWin");';         
//        $strRes .= "<span onclick='".$action."' title='ДДС'     class='glyphicon glyphicon-folder-open clickable'></span>&nbsp;&nbsp;\n";
        
//       $action ='openWin("/managment/head/show-purch-by-row-ref&noframe=1&stDate='.$this->stDate.'&enDate='.$this->enDate.'&rowRef='.$list[$i]['id'].'","detailWin");'; 
//       $strRes .= "<span onclick='".$action."' title='Закупки' class='glyphicon glyphicon-shopping-cart clickable'></span>&nbsp;&nbsp;\n";
        
        $strRes .= "</td>\n"; 
        
        
        /*Изменение*/        
        
        $strRes .= "<td>";
       if ($list[$i]['isMark'] == 1) $strRes .= "<div class='marked'>";
                               else $strRes .= "<div class='simple'>";        
        $strRes .= number_format($list[$i]['v'],2,'.','&nbsp;')."</div></td>\n";            
        
        
        $strRes .= "</tr>\n";       
        
     }         
     
     return $strRes;     
  }
  
/*****************************************/  
  
  public function getRowCfgProvider($params)
   {
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'rowTitle',
                      'isMark',
                      'mult',                      
                      ])
            ->from("{{%monitor_row}}")
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%monitor_row}}.id)")
                     ->from("{{%monitor_row}}")        ;

    $query->andFilterWhere(['=', 'rowType', $this->rowType]);
    $countquery->andFilterWhere(['=', 'rowType', $this->rowType]);

              
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'rowTitle',
                      'isMark',
                      'mult',
            ],            
            'defaultOrder' => [ 'rowTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
/*****************************************/  
/*

*/  
/******************************************/  
  public function getOwnerProfitProvider($params)
   {
           
   /* $this->dataValType = Yii::$app->db->createCommand(
            'SELECT max(valType) from {{%monitor_row_cfg}} WHERE srcType = 1 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();  
     if (empty($this->dataValType) )$this->dataValType = 1;*/

    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 1 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'owerOrgTitle',                      
                      'ifnull(b.mult,0 ) as multOwn',                      
                      ])
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_sverka_filter}}.id)")
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" );
            

     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
        $countquery->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);            
     }

        if (empty($this->multOwn)) $this->multOwn = 2;
        switch ($this->multOwn)
        {
          case 2:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 5:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }

     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->ownerRowN,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'owerOrgTitle',                      
                      'multOwn',
            ],            
            'defaultOrder' => [ 'owerOrgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
/********/   
/* Определим какую колонку брать
/***/
  public function getSrcTypeProfitProvider($params)
   {
           
    
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 7 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'typeTitle',                      
                      'ifnull(b.mult,0 ) as mult',                      
                      ])
            ->from("{{%monitor_val_type}}")
            ->leftjoin($strSql, "b.filterRef = {{%monitor_val_type}}.id" )
            ->distinct();
            ;
                    
     $countquery  = new Query();
     $countquery->select (" count({{%monitor_val_type}}.id)")
            ->from("{{%monitor_val_type}}")
            ->leftjoin($strSql, "b.filterRef = {{%monitor_val_type}}.id" );
    
         $query->andWhere("{{%monitor_val_type}}.srcType = 7");
    $countquery->andWhere("{{%monitor_val_type}}.srcType = 7");
    
              
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->ownerRowN,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'typeTitle',                      
                      'mult',
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  

/******************************************/  
  public function getOwnerBankOpProvider($params)
   {
           
   /* $this->dataValType = Yii::$app->db->createCommand(
            'SELECT max(valType) from {{%monitor_row_cfg}} WHERE srcType = 1 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();  
     if (empty($this->dataValType) )$this->dataValType = 1;*/

    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 8 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
    
    $query  = new Query();
    $query->select ([ 'id',  
                      'owerOrgTitle',                      
                      'ifnull(b.mult,0 ) as multOwn',                      
                      ])
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_sverka_filter}}.id)")
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" );
            

     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
        $countquery->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);            
     }

        if (empty($this->multOwn)) $this->multOwn = 2;
        switch ($this->multOwn)
        {
          case 2:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 5:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }

     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->ownerRowN,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'owerOrgTitle',                      
                      'multOwn',
            ],            
            'defaultOrder' => [ 'owerOrgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }     
/******************************************/  
  public function getRowBankOpProvider($params)
   {
           
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 2 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'article',                      
                      'ifnull(b.mult,0 ) as mult',                      
                      ])
            ->from("{{%bank_op_article}}")
            ->leftjoin($strSql, "b.filterRef = {{%bank_op_article}}.id" )
            ->distinct();
            
        
     $countquery  = new Query();
     $countquery->select (" count({{%bank_op_article}}.id)")
            ->from("{{%bank_op_article}}")
            ->leftjoin($strSql, "b.filterRef = {{%bank_op_article}}.id" );
              
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'article',                      
                      'mult',
            ],            
            'defaultOrder' => [ 'article' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/******************************************/  
/*
    Учет собственника для сверки
*/
  public function getRowOwnerProvider($params)
   {
           
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 3 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'owerOrgTitle',                      
                      'ifnull(b.mult,0 ) as multOwn',                      
                      ])
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_sverka_filter}}.id)")
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" );


     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
        $countquery->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);            
     }

        if (empty($this->multOwn)) $this->multOwn = 2;
        switch ($this->multOwn)
        {
          case 2:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 5:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }


              
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->ownerRowN,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'owerOrgTitle',                      
                      'multOwn',
            ],            
            'defaultOrder' => [ 'owerOrgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
/******************************************/  
/*
    Учет контрагента для сверки
*/
  public function getRowDolgiProvider($params)  
   {
           
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 4 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'title as orgTitle',                      
                      'ifnull(b.mult,0 ) as mult',                      
                      ])
            ->from("{{%orglist}}")
            ->leftjoin($strSql, "b.filterRef = {{%orglist}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%orglist}}.id)")
            ->from("{{%orglist}}")
            ->leftjoin($strSql, "b.filterRef = {{%orglist}}.id" );
     

     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);            
     }

        switch ($this->multFlt)
        {
          case 1:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 2:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }

     
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'orgTitle',                      
                      'mult',
            ],            
            'defaultOrder' => [ 'orgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/******************************************/  
/*
    Учет собственника для закупки товра
*/
  public function getRowWareOwnerProvider($params)
   {
           
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 5 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'owerOrgTitle',                      
                      'ifnull(b.mult,0 ) as multOwn',                      
                      ])
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_sverka_filter}}.id)")
            ->from("{{%control_sverka_filter}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_sverka_filter}}.id" );


     if (($this->load($params) && $this->validate())) {
        $query->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);
        $countquery->andFilterWhere(['like', 'owerOrgTitle', $this->owerOrgTitle]);            
     }

        if (empty($this->multOwn)) $this->multOwn = 2;
        switch ($this->multOwn)
        {
          case 2:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 5:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }


              
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $this->ownerRowN,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'owerOrgTitle',                      
                      'multOwn',
            ],            
            'defaultOrder' => [ 'owerOrgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  

/******************************************/  
/*
    Учет типа затрат для закупки
*/
  public function getRowWareProvider($params)  
   {
      
     
    $strSql = "(SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
    WHERE srcType = 6 and rowHeaderRef = ". intval($this->rowRef).") as b" ;   
           
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'typeTitle',                      
                      'ifnull(b.mult,0 ) as mult',                      
                      ])
            ->from("{{%control_purch_type}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_purch_type}}.id" )
            ->distinct();
            ;
        
     $countquery  = new Query();
     $countquery->select (" count({{%control_purch_type}}.id)")
            ->from("{{%control_purch_type}}")
            ->leftjoin($strSql, "b.filterRef = {{%control_purch_type}}.id" );
     

     if (($this->load($params) && $this->validate())) {
//        $query->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);
//        $countquery->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);            
     }

        switch ($this->multFlt)
        {
          case 1:
                $query->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 2:
                $query->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['=', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 3:
                $query->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['>', 'ifnull(b.mult,0 )', 0]);            
          break;          
          case 4:
                $query->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);
                $countquery->andFilterWhere(['<', 'ifnull(b.mult,0 )', 0]);            
          break;          
                        
        }

     
     $command = $query->createCommand();     
     $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'id',  
                      'typeTitle',                      
                      'mult',
            ],            
            'defaultOrder' => [ 'typeTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  

  
/*****************************************/  
    public function addRow()
    {
        $record = new TblMonitorRow();
        if (empty($record )) return false;
        $record->rowTitle= $this->rowTitle;
        $record->rowType= $this->rowType;        
        $record->save();
        return true;
    }

    public function removeRow($rowRef)
    {
        $record = TblMonitorRow::findOne($rowRef);
        if (empty($record )) return false;        
        $record->delete();
        return true;
    }
    
    
/*****************************************/  
/*
    Настройка строки
*/
    public function saveRowCfg ()
    {
      $res = ['res' => false, 
            'val' =>$this->dataVal, 
            'dataRowId' => $this->dataRowId,  
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType,
            ];
      
      $record = TblMonitorRow::findOne($this->dataRowId);
        if ( empty ($record )) return $res;

      if ($this->dataType == 1){ 
        $record -> mult = intval($this->dataVal);        
        $res['val'] = $record -> mult;                
      }
      
      if ($this->dataType == 2){ 
        if ($record -> isMark == 0) $record -> isMark =1;
        else  $record -> isMark = 0;
        $res['val'] = $record -> isMark;      
      }          
      $record -> save();        
      
      $res['res'] = true;

      return $res;
    }
    
/*****************************************/  
/*
    Настройка конкретной клетки
*/
    public function saveRowCfgData    ()
    {
      $res = ['res' => false, 
            'val' =>$this->dataVal, 
            'dataRowId' => $this->dataRowId,  
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType,
            'dataValType' => $this->dataValType            
            ];
      
      $record = TblMonitorRowCfg::findOne(
      [
        'srcType' => $this->dataType,
        'rowHeaderRef' => $this->dataRequestId,
        'filterRef' => $this->dataRowId,
      ]
      );
      if ( empty ($record ))
      {
      $record = new TblMonitorRowCfg();
        if ( empty ($record )) return $res;
        $record -> srcType = $this->dataType;
        $record -> rowHeaderRef = $this->dataRequestId;
        $record -> filterRef = $this->dataRowId;
      }
      $record -> mult = $this->dataVal;
      $record -> valType =  intval($this->dataValType);            
      $record -> save();
      
      $res['res'] = true;
      $res['val'] = $record -> mult;

      return $res;
    }
    
/*****************************************/
/********** Загрузка из 1С по http   *****/     
/*****************************************/          
/*Service*/

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
/**************************/     
     public function get_web_page( $url )
     {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
     }


/* грузим */
public function syncBuhData ($syncTime)
     {

     /**/    
     }
 }
 
