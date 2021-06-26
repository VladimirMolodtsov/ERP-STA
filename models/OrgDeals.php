<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 

use app\models\OrgList;
use app\models\TblOrgDeals;
use app\models\TblBankOpArticle;
use app\models\TblBankOpGrp;

class OrgDeals extends Model 
{
    
   public $orgId; //текущий контрагент
   public $selectedDeal = 0; //текущая выписка
   public $orgRecord; 

   public $mainDeal=0;
     
    public $dataRequestId;
    public $dataType;
    public $dataVal  ;      
        
    public $orgTypeArray=[];
    public $orgDealArray=[];
      
    public $orgTitle;
        
    public $fltOther;
    public $fltSupplier;
    public $fltService;
    public $fltBank;
    public $fltClient;
    public $fltArticle;    

    public $debug = [];                          
        
    public function rules()
    {
        return [
            [['mainDeal', 'dataRequestId', 'dataType', 'dataVal', ], 'default'],
            
            [['orgTitle', 'fltOther', 'fltSupplier', 'fltService', 'fltBank', 'fltClient', 'fltArticle',], 'safe'],            
            
            
        ];
    }
    

/*********************************************************/
/*
Переключаем статус статьи у организации по циклу

    не используется - используется - основная - не используется

*/
 public function switchOrgDeal($orgRef, $actionRef, $grpCode){

 $actionRef = intval($actionRef);
 $orgRef = intval($orgRef);
 $grpCode= intval($grpCode);
 $res = [ 'res' => false, 
             'orgRef'  => $orgRef, 
             'actionRef' => $actionRef, 
             'grpCode' => $grpCode,
             'value'    => 0,
             'grpValue' => 0,
             'mainValue'=> 0,
           ];   

  if (empty($actionRef)) return $res;        
  if (empty($orgRef)) return $res;
  
  $record= TblOrgDeals::findOne ([
  'refOrg'     => $orgRef,
  'articleRef' => $actionRef,  
  ]);
  if (empty($record)){
      $record = new TblOrgDeals();
      if (empty($record)) return $res;
      $record ->refOrg     = $orgRef;
      $record ->articleRef = $actionRef;        
      $record ->state = 1;
  } else
  {
    /*
    Сбрасывание остальных в 1
    if ($record ->state == 1)
    {
        //сбросим остальные в 1
        Yii::$app->db->createCommand("UPDATE {{%org_deals}} SET state = 1 where state = 2 and refOrg=:refOrg",
        [  ':refOrg' => $orgRef   ]  )->execute();
    }*/
    $record ->state++;        
    if ($record ->state >= 3) $record ->state =0;    
  }      
  $record ->save(); 
  $res['value'] = $record ->state;
  
  /*Определим статус группы*/
   $strSql="select count(d.`id`) from 	{{%org_deals}} as d
                left Join {{%bank_op_article}} as c on c.id=d.articleRef where
                state > 0 and c.grpRef = :grp and d.refOrg = :refOrg";                
    $cnt = Yii::$app->db->createCommand($strSql, 
                [                    
                    ':grp'   => $grpCode,
                    ':refOrg' => $orgRef,
                ])->queryScalar();
                
    /*Определим новый статус*/
    if ($cnt > 0)  $res ['grpValue'] = 1;
    if ($cnt == 0) $res ['grpValue'] = 0;

   
  $res ['res'] = true;
   return  $res;
 }

/***********/
/* 
Переулючение по типу
Определяем включена или нет хотя бы одна статья типа
если да, то выключим все, если нет то включим все
*/
 public function switchOrgType($orgRef, $grpCode){

 
 $orgRef = intval($orgRef);
 $grpCode= intval($grpCode);
 $res = [ 'res' => false, 
             'orgRef'  => $orgRef, 
             'grpCode' => $grpCode,
             'typeValue'    => 0,
             'mainValue' => 0,
             'action'   => 0,
             'changedList' => [],
             'changedValue' => [],
           ];   

  if (empty($grpCode)) return $res;        
  if (empty($orgRef)) return $res;        
    

  // Оценим текущее состояние  
    
    $strSql="select count(d.`id`) from 	{{%org_deals}} as d
                left Join {{%bank_op_article}} as c on c.id=d.articleRef where
                state > 0 and c.grpRef = :grp and d.refOrg = :refOrg";                
    $cnt = Yii::$app->db->createCommand($strSql, 
                [                    
                    ':grp'   => $grpCode,
                    ':refOrg' => $orgRef,
                ])->queryScalar();
                
    /*Определим новый статус*/
    if ($cnt > 0)  $newState = 0;
    if ($cnt == 0) $newState = 1;
    
   
    /*Получим список вариантов ролей*/ 
    $varQuery  = new Query();
    $varQuery->select (['id'])->from("{{%bank_op_article}}");                     
    $varQuery->andWhere("grpRef = ".$grpCode);    
    $varList = $varQuery->createCommand()->queryAll();     
    $vN=count($varList);
    
    for ($i=0;$i<$vN;$i++ )
    {
      $actionRef = $varList[$i]['id'];
      $record= TblOrgDeals::findOne ([
          'refOrg'     => $orgRef,
          'articleRef' => $actionRef,  
          ]);
      if (empty($record)){
          $record = new TblOrgDeals();
          if (empty($record)) continue;
          $record ->refOrg     = $orgRef;
          $record ->articleRef = $actionRef;  
       }    
      if ($newState == 0) $record ->state = 0;
      else if ($record ->state == 0 ) $record ->state = 1;
            
      $res['changedList'][]=$record ->articleRef;
      $res['changedValue'][]=$record ->state;
      
      $record -> save();
    }
        
   $res['typeValue'] = $newState ;
   $res ['res'] = true;
   return  $res;
 }    
    
/******************************************************/    
 public function initData()    
 {
   $this->orgId = intval($this->orgId);
   $this->orgRecord= OrgList::findOne ($this->orgId);
   if (empty($this->orgRecord)) return false;

   
   $strSql = "(Select count({{%org_deals}}.id) as N, {{%bank_op_article}}.grpRef 
   from {{%org_deals}},{{%bank_op_article}}
   where {{%org_deals}}.articleRef = {{%bank_op_article}}.id   
   and {{%org_deals}}.state > 0
   and {{%org_deals}}.refOrg=".$this->orgId." 
   group by grpRef
   ) as d";    
   
    $typeQuery  = new Query();
    $typeQuery->select ([
             'id', 
             'grpTitle',
             'isAllArticles',
             'flg',  
             'N'
             ])
            ->from("{{%bank_op_grp}}")
            ->leftJoin($strSql,"d.grpRef = {{%bank_op_grp}}.id")
            ->orderBy("sortOrder");                                              
    $this->orgTypeArray = $typeQuery->createCommand()->queryAll(); 
    
    for($i=0;$i<count($this->orgTypeArray);$i++)
    {
        if($this->orgTypeArray[$i]['N'] > 0) $this->orgTypeArray[$i]['isSet']=1;
                                        else $this->orgTypeArray[$i]['isSet']=0;
    }
    
    $strSql = "(Select articleRef, state from {{%org_deals}} where refOrg=".$this->orgId.") as org_deals";    
    $varQuery  = new Query();
    $varQuery->select ([
             'id', 
             'grpRef',
             'article',
             'signValue',
             'IFNULL(org_deals.state, 0) as status',
             ])
            ->from("{{%bank_op_article}}")
            ->leftJoin($strSql,"org_deals.articleRef = {{%bank_op_article}}.id")
            ->orderBy("article");                                              
            ;                     
       
   $this->orgDealArray = $varQuery->createCommand()->queryAll();  
   return true;
 }

 public function printHeadLine($i)
 {
     if(empty($this->orgTypeArray[$i])) return;
 
    echo "<div class='row'>\n";
     
    $action = "switchOrgType(".$this->orgTypeArray[$i]['id'].")"; 
    $bg='background-color:White;';
    if ($this->orgTypeArray[$i]['isSet']  == 1) $bg='background-color:LightGreen;';
    
    $id = 'grp_'.$this->orgTypeArray[$i]['id'];
    echo "  <div class=col-md-3>";
    echo  \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
 

    echo " <b>";
    echo $this->orgTypeArray[$i]['grpTitle'];
    echo "</b></div>\n"; 

    
    echo "  <div class=col-md-2>";
    
    echo "</div>\n"; 

    echo "  <div class=col-md-2>";
    
    echo "</div>\n"; 
             
    echo "</div>\n";
    
 }
 

public function printArticles($idx)
{
 if(empty($this->orgTypeArray[$idx])) return;
   $grp = $this->orgTypeArray[$idx]['id'];
  
  for ($i=0;$i<count($this->orgDealArray); $i++)
  {
      if ($this->orgDealArray[$i]['grpRef'] == $grp) $this->printArticleLine($i,$grp);
  }
}

 public function printArticleLine($i,$grp)
 {
     if(empty($this->orgDealArray[$i])) return;
     if(empty($this->orgDealArray[$i]['article'])) return;
    echo "<div class='row'>\n";
    echo "  <div class=col-md-1></div>";
 
    echo "  <div class=col-md-4>";
     
    echo $this->orgDealArray[$i]['article'];
        if ($this->orgDealArray[$i]['signValue'] == -1) echo "&nbsp;<span class='glyphicon glyphicon-minus-sign'></span>";
        if ($this->orgDealArray[$i]['signValue'] == +1) echo "&nbsp;<span class='glyphicon glyphicon-plus-sign'></span>";
    echo "</div>\n"; 
     
    $bg='background-color:White;';
        //if ($this->orgDealArray[$i]['status']  == 0) $bg='background-color:LightGray;';
        if ($this->orgDealArray[$i]['status']  == 1) $bg='background-color:LightGreen;';
        if ($this->orgDealArray[$i]['status']  == 2) $bg='background-color:Blue;';
    
    $action = "switchOrgDeal(".$this->orgDealArray[$i]['id'].", ".$grp.")";
    $id = 'article_'.$this->orgDealArray[$i]['id'];
    echo "  <div class=col-md-2>";
    echo  \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
    echo "</div>\n"; 

    echo "  <div class=col-md-2>";
    
    echo "</div>\n"; 


             
    echo "</div>\n";
    
 }

/*******************************/
/*Для выбора*/
 
public function printHeadLineSelect($i)
 {
   if(empty($this->orgTypeArray[$i])) return;
   
 
    echo "<div class='row'>\n";
    
    $id = 'grp_'.$this->orgTypeArray[$i]['id'];
    echo "  <div class=col-sm-12>";    
    echo " <b>";
    echo $this->orgTypeArray[$i]['grpTitle'];
    echo "</b></div>\n"; 
             
    echo "</div>\n";
    
 }     
 
public function printArticlesSel($idx)
{
 if(empty($this->orgTypeArray[$idx])) return;
   $grpRef = $this->orgTypeArray[$idx]['id'];
  
  for ($i=0;$i<count($this->orgDealArray); $i++)
  {
     echo "<table border='0' width='390px'>";      
      if ($this->orgDealArray[$i]['grpRef'] == $grpRef) $this->printArticleLineSelect($i,$grpRef);
     echo "</table>";  
  }
}
 
 public function printArticleLineSelect($i,$grpRef)
 {
     if(empty($this->orgDealArray[$i])) return;
     if(empty($this->orgDealArray[$i]['article'])) return;
     if ($this->orgDealArray[$i]['status']  == 0) return;
    echo "<tr>\n";
    echo "  <td width='50px'></td>"; 
    echo "  <td>";     
    echo $this->orgDealArray[$i]['article'];
    //echo $this->orgDealArray[$i]['id'];
        if ($this->orgDealArray[$i]['signValue'] == -1) echo "&nbsp;<span class='glyphicon glyphicon-minus-sign'></span>";
        if ($this->orgDealArray[$i]['signValue'] == +1) echo "&nbsp;<span class='glyphicon glyphicon-plus-sign'></span>";
    echo "</td>\n"; 
             
    $bg='background-color:White;';
        //if ($this->orgDealArray[$i]['status']  == 0) $bg='background-color:LightGray;';
    if ($this->orgDealArray[$i]['id']  == $this->selectedDeal)  $bg='background-color:Blue;';
    
    $action = "switchOrgDeal(".$this->orgDealArray[$i]['id'].", ".$grpRef.")";
    $id = 'article_'.$this->orgDealArray[$i]['id'];
    echo "  <td width='30px'>";
    echo  \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
    echo "</td>\n"; 
    echo "</tr>\n";
    
 }

 
 
 
 public $rawSql;
public $dataArray=[];
public $articleList=[];

/*

update rik_orglist set supplierType = 8 where IFNULL(supplierType,0) = 0
ALTER TABLE `rik_orglist` CHANGE COLUMN `supplierType` `contragentType` INTEGER(11) DEFAULT 0 COMMENT 'Тип поставщика\r\n0x1 - поставщик товара\r\n0x2 - поставщик услуг \r\n0x4 - поставщик транспорта';
ALTER TABLE `rik_orglist` ADD COLUMN `supplierType` INTEGER GENERATED ALWAYS AS ('contragentType') VIRTUAL;
ALTER TABLE `rik_bank_operation` ADD INDEX `rik_bank_operation_idx1` (`orgINN`);

INSERT INTO rik_org_deals
(refOrg,articleRef,state)
SELECT DISTINCT rik_orglist.id , rik_bank_operation.`articleRef`, 1
from rik_orglist, rik_bank_operation where rik_orglist.`orgINN` = rik_bank_operation.orgINN

*/

 public function prepareOrgDealsData($params)
   {
   

   /*Получим основу*/ 
    $query  = new Query();
    $query->select ([
             'a.id as orgRef', 
             'a.title as orgTitle', 
             'a.orgINN',
             'a.orgKPP',             
             'a.contactDate'
             ])
            ->from("{{%orglist}} as a")
            ->leftJoin('{{%org_deals}} as d', "d.refOrg=a.id")
            ->leftJoin('{{%bank_op_article}} as c', "c.id=d.articleRef")
            ->leftJoin('{{%bank_op_grp}} as b', "b.id=c.grpRef")
            ->distinct()           
    ;
 
    $query->andWhere('isOrgActive = 1');
    
    if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['Like', 'a.title', $this->orgTitle]);                        
    }

    $filter = "";
    
    switch($this ->fltClient)
    {
        case 1:
            $filter .= "(b.fltAtr = 'fltClient')";
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltClient']);
            
        break;                 
    }    

    switch($this ->fltOther)
    {
        case 1:
            if(!empty($filter)) $filter .= " || "; 
            $filter .= "(b.fltAtr = 'fltOther')";
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltOther']);

        break;                 
    }    
    switch($this ->fltSupplier)
    {
        case 1:
            if(!empty($filter)) $filter .= " || "; 
            $filter .= "(b.fltAtr = 'fltSupplier')";
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltSupplier']);
            
        break;        }    
    switch($this ->fltService)
    {
        case 1:
            if(!empty($filter)) $filter .= " || "; 
            $filter .= "(b.fltAtr = 'fltService')";
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltService']);
            
        break;        }    
    switch($this ->fltBank)
    {
        case 1:
            if(!empty($filter)) $filter .= " || "; 
            $filter .= "(b.fltAtr = 'fltBank')";
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltBank']);

        break;    
    }    
            if(!empty($filter)) 
            {
            $query->andWhere($filter);
            $query->andFilterWhere(['>', 'ifnull(d.state,0)',  0]);
            }
    
    
    
    $this->debug[]= $query->createCommand()->getRawSql();
    
    $this->dataArray = $query->createCommand()->queryAll(); 
    $N = count($this->dataArray);
    
    /*Получим список вариантов ролей*/ 
    $varQuery  = new Query();
    $varQuery->select ([
             'id', 
             'grpRef',
             'article'
             ])
            ->from("{{%bank_op_article}}");                     
    $this->articleList = $varQuery->createCommand()->queryAll(); 
    
    $vN = count($this->articleList);
    
    /*индексируем и попутно инициализируем*/
    $orgInd=[]; //массив индексов ссылка на организацию -> номер в массиве
    for ($i=0;$i<$N;$i++ )
    {
        $orgInd[$this->dataArray[$i]['orgRef']] = $i;
        for($j=0;$j<$vN;$j++)
        {            
            $articleRef = $this->articleList[$j]['id'];
            $this->dataArray[$i][$articleRef]=0;    
        }
    }
    
    /*Получим связанный список ролей*/ 
    $roleQuery  = new Query();
    $roleQuery->select ([
             'id', 
             'articleRef',
             'refOrg', 
             'state'
             ])
            ->from("{{%org_deals}} as a");    
    $roleList = $roleQuery->createCommand()->queryAll(); 
    $rN=count($roleList);
    for ($i=0;$i<$rN;$i++ )
    {
      $refOrg = $roleList[$i]['refOrg'];
      if (!isset($orgInd[$refOrg])) continue;
        $ind=$orgInd[$refOrg];
        $articleRef =$roleList[$i]['articleRef'];
        $this->dataArray[$ind][$articleRef] = $roleList[$i]['state'];         
    }
    
            
    }
  public function getOrgDealsData($params)
   {
        $this-> prepareOrgDealsData($params);
        $this->loadGrpArticle();
       
        $mask = realpath(dirname(__FILE__))."/../uploads/orgDealsList*.csv";
        array_map("unlink", glob($mask));       
        $fname = "uploads/orgDealsList".time().".csv";
        $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
        if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
        $col_title = array (
        iconv("UTF-8", "Windows-1251","Контрагент"),
        );
    
      $N= count($this->articleList);    
      for($idx=0;$idx<count($this->grpArticles);$idx++)
      {
        $col_title[]= iconv("UTF-8", "Windows-1251",$this->grpArticles[$idx]['grpTitle']);            
        $grp=$this->grpArticles[$idx]['id'];      
        for ($i=0; $i<$N; $i++)
        {
            if (!($this->articleList[$i]['grpRef'] == $grp )) continue;            
            $col_title[]= iconv("UTF-8", "Windows-1251",$this->articleList[$i]['article']);            
        }
        
      }
 
        fputcsv($fp, $col_title, ";"); 

    for ($j=0; $j< count($this->dataArray); $j++)
    {        
      $list = array 
          (
            iconv("UTF-8", "Windows-1251",$this->dataArray[$j]['orgTitle']),    
          );
      for($idx=0;$idx<count($this->grpArticles);$idx++)
      {
        $grp=$this->grpArticles[$idx]['id'];      
        $val="";
        for ($i=0; $i<$N; $i++)
        {
            if (!($this->articleList[$i]['grpRef'] == $grp )) continue;
            $val =1;
            break;
        }        
        $list[]= $val ;        
        for ($i=0; $i<$N; $i++)
        {
            if (!($this->articleList[$i]['grpRef'] == $grp )) continue;
            $articleRef= $this->articleList[$i]['id'];
            
            $val = $this->dataArray[$j][$articleRef];
            if (empty($val))$val="";
            $list[]= $val ;
        }
      }
    fputcsv($fp, $list, ";");  
    }
        fclose($fp);
        return $fname;        
   }
   
  public function getOrgDealsProvider($params)
   {
   
    $this-> prepareOrgDealsData($params);
    $dataProvider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 10,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'orgTitle', 
             'orgINN',
             'orgKPP',
             'contactDate'
            ],
            'defaultOrder' => [ 'orgTitle' => SORT_ASC ],
            ],
            
        ]);

              
        
    return  $dataProvider;   
   }    
public $attr=[];
public $grpArticles=[];
  
public function loadGrpArticle()
{
    $grpQuery  = new Query();
    $grpQuery->select ([
    'id',
    'grpTitle',
    'isAllArticles',
    'flg',
    'fltAtr',         
    ])->from("{{%bank_op_grp}}")
    ->orderBy('sortOrder');                         
    
    $this->grpArticles = $grpQuery->createCommand()->queryAll();     
    $vN=count($this->grpArticles);
    for ($i=0;$i<$vN;$i++ )
    {
     $idx=$this->grpArticles[$i]['id'];
     $this->attr[$idx]=$this->grpArticles[$i]['fltAtr'];
    }    
}   
   
 /*********************************************************/   
public function createColumns($idx)
{
$columns=[];
$grp=$this->grpArticles[$idx]['id'];
$columns[]= [
                'attribute' => $this->grpArticles[$idx]['fltAtr'],
                'label'     => "<div style='width:70px'>".$this->grpArticles[$idx]['grpTitle']."</div>",
                'encodeLabel' => false,
                'format' => 'raw', 
                'filter' => [0 => 'Все', 1=> 'Да', 2=> 'Нет', 3=>'Основной'],
                'contentOptions' => ['style' => 'background-color:LightCyan;', 'align' => 'center' ],                
                'value' => function ($model, $key, $index, $column)use($grp){	                                                
                $action ="switchMainBox(".$model['orgRef'].",".$grp.")";
                $id='grp_'.$model['orgRef'].'_'.$grp;
             
                
                $strSql="select count(d.`id`) from 	{{%org_deals}} as d
                left Join {{%bank_op_article}} as c on c.id=d.articleRef where
                state > 0 and c.grpRef = :grp and d.refOrg = :refOrg";
                
                $cnt = Yii::$app->db->createCommand($strSql, 
                [                    
                    ':grp'   => $grp,
                    ':refOrg' => $model['orgRef'],
                ])->queryScalar();
                
                if ($cnt > 0 ) $bg='background-color:LightGreen;';
                          else $bg='background-color:White;';
                                 
                 return \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
                },                                
            ];
            
$N= count($this->articleList);

for ($i=0; $i<$N; $i++)
{
    if ($this->articleList[$i]['grpRef'] != $grp ) continue;
    $articleRef= $this->articleList[$i]['id'];
    
    //$labelContent = $this->articleList[$i]['article'];
    
    $len = mb_strlen($this->articleList[$i]['article'],'utf-8');
    $nl = $len/11;
    $labelContent = "";
    for ($j=0;$j<$nl;$j++){
        $labelContent .= mb_substr($this->articleList[$i]['article'],$j*11,11,'utf-8');
        $labelContent .="<br>";
    }
    
    //preg_replace("/\*/","",$this->articleList[$i]['article']);
    $labelContent = preg_replace("/\s+/"," ",$labelContent);
    $label =\yii\helpers\Html::tag( 'div', $labelContent, 
                   [
                     'class'   => 'localLabel',                          
                   ]);
//    $label= $labelContent;              
    $columns[]= [
                'attribute' => '-',                
                //'labelOptions' => ['style' => 'width: 65px; max-width: 65px;', 'align' =>'center'],
                'options' => ['width' => '65px'],        
                'contentOptions' => ['style' => 'width: 65px; max-width: 65px; word-wrap: break-word;',  'align' =>'center'],         
                'encodeLabel' => false,
                'label'     => $label,
                
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use($articleRef, $grp) {	                                                
                
                $action = 'switchBox ('.$model['orgRef'].', '.$articleRef.', '.$grp.')';
                $id=$model['orgRef'].'_'.$articleRef;
                
                $bg="";
                if ($model[$articleRef] == 0 ) $bg='background-color:White;';
                if ($model[$articleRef] == 1 ) $bg='background-color:LightGreen;';
                if ($model[$articleRef] == 2 ) $bg='background-color:Blue;';
                 
                 return \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
                },                                
            ];        
}            

   return   $columns;      
}
   
 
/*********************/
 public function getOrgDealsCfgProvider($params)
   {
  
    $query  = new Query();
    $query->select ([
             'a.id', 
             'a.article', 
             'a.actionTypeFlg',  
             'a.signValue',
             'a.grpRef', 
             'a.isRef1C'
             ])
            ->from("{{%bank_op_article}} as a")
            ->leftJoin ("{{%bank_op_grp}} as b", "b.id=a.grpRef")
            ->distinct()           
    ;

    $countquery  = new Query();
    $countquery->select ("count(a.id)")
            ->from("{{%bank_op_article}} as a")
            ->leftJoin ("{{%bank_op_grp}} as b", "b.id=a.grpRef")
            ;            
    
    if (($this->load($params) && $this->validate())) {        
        $query->andFilterWhere(['Like', 'a.article', $this->fltArticle]);
        $countquery ->andFilterWhere(['Like', 'a.article', $this->fltArticle]);
    }


    switch($this ->fltClient)
    {
        case 1:
            $query->andFilterWhere(['=', 'b.fltAtr', 'fltClient']);
            $countquery ->andFilterWhere(['=', 'b.fltAtr', 'fltClient']);
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltClient']);
            $countquery ->andFilterWhere(['!=', 'b.fltAtr', 'fltClient']);
        break;                 
    }    

    switch($this ->fltOther)
    {
        case 1:
            $query->andFilterWhere(['=', 'b.fltAtr', 'fltOther']);
            $countquery ->andFilterWhere(['=', 'b.fltAtr', 'fltOther']);
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltOther']);
            $countquery ->andFilterWhere(['!=', 'b.fltAtr', 'fltOther']);
        break;                 
    }    
    switch($this ->fltSupplier)
    {
        case 1:
            $query->andFilterWhere(['=', 'b.fltAtr', 'fltSupplier']);
            $countquery ->andFilterWhere(['=', 'b.fltAtr', 'fltSupplier']);
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltSupplier']);
            $countquery ->andFilterWhere(['!=', 'b.fltAtr', 'fltSupplier']);
        break;        }    
    switch($this ->fltService)
    {
        case 1:
            $query->andFilterWhere(['=', 'b.fltAtr', 'fltService']);
            $countquery ->andFilterWhere(['=', 'b.fltAtr', 'fltService']);
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltService']);
            $countquery ->andFilterWhere(['!=', 'b.fltAtr', 'fltService']);
        break;        }    
    switch($this ->fltBank)
    {
        case 1:
            $query->andFilterWhere(['=', 'b.fltAtr', 'fltBank']);
            $countquery ->andFilterWhere(['=', 'b.fltAtr', 'fltBank']);
        break;     
        case 2:
            $query->andFilterWhere(['!=', 'b.fltAtr', 'fltBank']);
            $countquery ->andFilterWhere(['!=', 'b.fltAtr', 'fltBank']);
        break;    
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
             'article', 
             'actionType',
             'signValue' ,
             'isRef1C'            
            ],            
            
            'defaultOrder' => ['article' => SORT_ASC ],            
            ],            
        ]);
    return  $dataProvider;   
   }    

public function createCfgColumn ($idx)
{

$title=$this->grpArticles[$idx]['grpTitle']; 
$grp=$this->grpArticles[$idx]['id'];

$column= [
                'attribute' => $this->attr[$grp],
                'label'     => "<div style='width:70px'>".$title."</div>",
                'encodeLabel' => false,
                'contentOptions' => ['align' => 'center'],
                'format' => 'raw', 
                'filter' => [0 => 'Все', 1=> 'Да', 2=> 'Нет'],                
                'value' => function ($model, $key, $index, $column)use($grp){	                                                
                $action ="switchType(".$model['id'].",".$grp.")";
                $id=$model['id'].'_'.$grp;
                $bg="background-color:Blue";                
                if (($model['grpRef'] != $grp) ) $bg='background-color:White;';                                 
                 return \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
                },                                
            ];
 return $column;           
}
 


 public function switchActionType($id, $grpCode){
 $id = intval($id);
 if ($grpCode != 'ref1C') $grpCode= intval($grpCode);//значение grpRef
 $res = [ 'res' => false, 
             'id'  => $id, 
             'grpCode' => $grpCode,
             'typeValue'    => 0,
           ];   

//  if (empty($grpCode)) return $res;        
  if (empty($id)) return $res;        
     
      $record= TblBankOpArticle::findOne ($id);
      if (empty($record)) return $res;
      if ($grpCode == 'ref1C') {
          if ($record->isRef1C == 0) $record->isRef1C=1;
                                else $record->isRef1C=0;      
        $res['typeValue'] =  $record->isRef1C;                        
      }
      else {
       $record ->grpRef     = $grpCode;
       $res['typeValue'] =  $record ->grpRef;
       }
      $record -> save();
   $res ['res'] = true;
   return  $res;
 }

public function switchActionSign($id, $val){
 $id = intval($id);
 $val= intval($val);
 $res = [ 'res' => false, 
             'id'  => $id, 
             'val' => $val,
           ];   

  if (empty($id)) return $res;        
      $record= TblBankOpArticle::findOne ($id);
      if (empty($record)) return $res;
      
      $record ->signValue     = $val;
      $record -> save();
        
   $res['val'] =  $record ->signValue;
   $res ['res'] = true;
   return  $res;
 } 
  
/*****/
 
/*****/
}
