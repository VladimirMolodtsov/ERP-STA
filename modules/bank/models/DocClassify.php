<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 
use yii\data\ArrayDataProvider;

use app\modules\bank\models\TblDocuments;
use app\modules\bank\models\TblDocHeader;
use app\modules\bank\models\TblDocGrpClassLnk;
use app\modules\bank\models\TblDocClassify;
/**
 * DocClassify - модель классификации документов
 
 */
 
 class DocClassify extends Model
{

/*Поля*/

    public $id = 0;
    public $docType = '';

    /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 
    
    public $docId =0;
    
    public $dataArray=[];
    public $groupList=[];
    
    public $safeRules=[];
    
    public function rules()
    {
        return [                              
            [['dataId', 'recordId', 'dataVal', 'dataType',
            'id', 'docType', 
            ], 'default'],                                                  
            [$this->safeRules, 'safe']
        ];
    }

    
 public function prepareDocClassifyData($params)
   {
   

   /*Получим основу*/ 
    $query  = new Query();
    $query->select ([
             'a.id as classRef', 
             'a.docType as docType', 
             'isRef1C'
             ])
            ->from("{{%doc_classify}} as a")
            ->distinct()           
    ;
 

    if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['Like', 'a.docType', $this->docType]);                        
    }


    $this->dataArray = $query->createCommand()->queryAll(); 
    $N = count($this->dataArray);
    
    /*Получим список групп*/ 
    $varQuery  = new Query();
    $varQuery->select ([
             'id', 
             'docGrpTitle',
             ])
            ->from("{{%doc_group}}");                     
    $this->groupList = $varQuery->createCommand()->queryAll(); 
    
    $vN = count($this->groupList);
    
    
    /*индексируем и попутно инициализируем двумерный массив */
    $classInd=[]; //массив индексов ссылка на организацию -> номер в массиве
    for ($i=0;$i<$N;$i++ )
    {
        $classInd[$this->dataArray[$i]['classRef']] = $i;
        for($j=0;$j<$vN;$j++)
        {            
            $grpRef = $this->groupList[$j]['id'];
            $this->dataArray[$i][$grpRef]=0;    
        }
    }
    
    /*Получим связанный список ролей*/ 
    $lnkQuery  = new Query();
    $lnkQuery->select ([
             'id', 
             'grpRef',
             'classRef',
             'state'
             ])
            ->from("{{%doc_grp_class_lnk}}");    
    $lnkList = $lnkQuery->createCommand()->queryAll(); 
    $rN=count($lnkList);
    for ($i=0;$i<$rN;$i++ )
    {
      $classRef = $lnkList[$i]['classRef'];
      if (!isset($classInd[$classRef])) continue;
        $ind=$classInd[$classRef];
        $grpRef =$lnkList[$i]['grpRef'];
        $this->dataArray[$ind][$grpRef] = $lnkList[$i]['state'];         
    }
  }
    
    
public function getDocClassifyProvider($params)
   {
   
    $this-> prepareDocClassifyData($params);
    $dataProvider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'docType', 
            ],
            'defaultOrder' => [ 'docType' => SORT_ASC ],
            ],
            
        ]);

              
        
    return  $dataProvider;   
   }       
   
public $attr=[];
public $grpList=[];   

public function loadGrpList()
{
    $grpQuery  = new Query();
    $grpQuery->select ([
    'id',
    'docGrpTitle',
    'fltAtr'
    ])->from("{{%doc_group}}")
    ->orderBy('sortOrder');                         
    
    $this->grpList = $grpQuery->createCommand()->queryAll();     
    $vN=count($this->grpList);
    for ($i=0;$i<$vN;$i++ )
    {
     $idx=$this->grpList[$i]['id'];
     $atr=$this->grpList[$i]['fltAtr'];
     $this->attr[$idx]=$atr;
//     $this->{$atr}="";
     //$this->safeRules[]=$atr;
    }    
}   
   
 /*********************************************************/   
public function createColumns()
{
$columns=[];
for ($idx=0;$idx< count($this->grpList); $idx++){

    $grp=$this->grpList[$idx]['id'];
    $columns[]= [
                'attribute' => $this->grpList[$idx]['fltAtr'],
                'label'     => "<div style='width:70px'>".$this->grpList[$idx]['docGrpTitle']."</div>",
                'encodeLabel' => false,
                'format' => 'raw', 
               // 'filter' => [0 => 'Все', 1=> 'Да', 2=> 'Нет', 3=>'Основной'],
                'contentOptions' => [ 'align' => 'center' ],                
                'value' => function ($model, $key, $index, $column)use($grp){	                                                
                $action ="switchBox(".$model['classRef'].",".$grp.")";
                $id=$model['classRef'].'_'.$grp;
             
                    
                $bg="";
                if ($model[$grp] == 0 ) $bg='background-color:White;';
                if ($model[$grp] == 1 ) $bg='background-color:LightGreen;';
                                                 
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
   

public function switchClassGrp($classRef,  $grpRef){

 $classRef = intval($classRef);
 
 $res = [ 'res' => false, 
             'classRef'  => $classRef, 
             'grpRef' => $grpRef, 
             'value'    => 0,
           ];   

  if (empty($classRef)) return $res;        
  if (empty($grpRef)) return $res;
  
  if ($grpRef == 'DEL')
  {
    /*Снесем до основания*/
      $strSql = "DELETE FROM {{%doc_classify}} WHERE id=:classRef";
      Yii::$app->db->createCommand($strSql,[ ':classRef' => $classRef,])->execute(); 
      $strSql = "DELETE FROM {{%doc_grp_class_lnk}} WHERE classRef=:classRef";
      Yii::$app->db->createCommand($strSql,[ ':classRef' => $classRef,])->execute();       
      $res ['res'] = true;
      return  $res;
  }
  
  if ($grpRef == 'ref1C')
  {
    /*Снесем до основания*/
  $record= TblDocClassify::findOne ($classRef);
  if (empty($record)) return  $res;
  if ($record->isRef1C == 0) $record->isRef1C = 1;
                      else   $record->isRef1C = 0;
  $record ->save(); 
  $res['value'] = $record ->isRef1C;  
  $res ['res'] = true;  
  }
  
  $grpRef = intval($grpRef); 
  $record= TblDocGrpClassLnk::findOne ([
             'classRef'  => $classRef, 
             'grpRef' => $grpRef, 
  ]);
  if (empty($record)){
      $record = new TblDocGrpClassLnk();
      if (empty($record)) return $res;
      $record ->classRef     = $classRef;
      $record ->grpRef = $grpRef;        
      $record ->state = 1;
  } else
  {
    $record ->state++;        
    if ($record ->state >= 2) $record ->state =0;    
  }      
  $record ->save(); 
  $res['value'] = $record ->state;
  
  $res ['res'] = true;
   return  $res;
 }   
   
 public function saveEditForm() 
 {
  $res = [ 'res' => false, 
             'id'  => $this->id, 
             'docType' => $this->docType,              
           ];   

   if (!empty($this->id))
        $record =  TblDocClassify::findOne($this->id);        
   if(empty($record)) $record =  new TblDocClassify();
   
   if(empty($record)) return  $res;   
   
   $record->docType = $this->docType; 
   $record->save();

   $res ['res'] = true;
   return  $res;   
 }

  public function getEditFormData($id) 
 {
  $res = [ 'res' => false, 
             'id'  => $id, 
             'docType' => "",              
           ];   
  
   $record =  TblDocClassify::findOne($this->id);        
   if(empty($record)) return  $res;   
   
   $res['id'] = $record->id; 
   $res['docType'] = $record->docType; 
   $res ['res'] = true;
   return  $res;   
 }
 
/******************/
/*
ALTER TABLE `rik_documents` ADD COLUMN `docTypeRef` BIGINT DEFAULT 0;
*/
 public $classArray=[];
 public $typeArray=[];
 public function loadClassify()    
 {
   $this->docId = intval($this->docId);
   
   $docRecord= TblDocuments::findOne ($this->docId);
   if (empty($docRecord)) {
   $docTypeRef=0;
   $docClassifyRef=0; 
   }
   else{
   $docTypeRef=$docRecord->docTypeRef;
   $docClassifyRef=$docRecord->docClassifyRef; 
   }
   
     
    $typeQuery  = new Query();
    $typeQuery->select ([
             'a.id as grpRef', 
             'docGrpTitle',             
             ])
            ->from("{{%doc_group}} as a")
            ->orderBy("sortOrder");                                              
    $this->typeArray = $typeQuery->createCommand()->queryAll(); 
    
    for($i=0;$i<count($this->typeArray); $i++)
    {
      if ($this->typeArray[$i]['grpRef'] == $docTypeRef)$this->typeArray[$i]['isSet']=1;
      else $this->typeArray[$i]['isSet']=0;    
    }
  
    $classQuery  = new Query();
    $classQuery->select ([
             'd.grpRef', 
             'c.id as classRef',
             'docType',               
             ])
            ->from("{{%doc_grp_class_lnk}} as d")            
            ->leftJoin("{{%doc_classify}} as c","d.classRef = c.id")            
            ->orderBy("grpRef");                                              
    $classQuery->andWhere(['=', 'd.state', 1]);        
    $this->classArray = $classQuery->createCommand()->queryAll(); 
    for($i=0;$i<count($this->classArray); $i++)
    {
      if ($this->classArray[$i]['classRef'] == $docClassifyRef)$this->classArray[$i]['isSet']=1;
      else $this->classArray[$i]['isSet']=0;    
    }
         
   return true;
 }

/*******************************/


 public function printHeadLine($i)
 {
     if(empty($this->typeArray[$i])) return;
 
    echo "<div class='row'>\n";
     
    $action = "switchType(".$this->typeArray[$i]['grpRef'].")"; 
    $bg='background-color:White;';
    if ($this->typeArray[$i]['isSet']  == 1) $bg='background-color:LightGreen;';
    
    $id = 'grp_'.$this->typeArray[$i]['grpRef'];
    echo "  <div class=col-sm-4>";
    /*echo  \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'style'  => 'padding:5px;'.$bg,
                     'id'  => $id,
                   ]);
 
   */
    echo " <b>";
    echo $this->typeArray[$i]['docGrpTitle'];
    echo "</b></div>\n"; 
    
    echo "</div>\n"; 
    
 }
 
 

public function printClasses($idx)
{
 if(empty($this->typeArray[$idx])) return;
   $grp = $this->typeArray[$idx]['grpRef'];
  
  for ($i=0;$i<count($this->classArray); $i++)
  {
       echo "<table border='0' width='380px'>";      
      if ($this->classArray[$i]['grpRef'] == $grp) $this->printClassLine($i,$grp);
      echo "</table>";  
  }
}

 public function printClassLine($i,$grp)
 {
     if(empty($this->classArray[$i])) return;
     if(empty($this->classArray[$i]['docType'])) return;
    echo "<tr>\n";
    echo "  <td width='50px'></td>"; 
    echo "  <td>";     
        echo $this->classArray[$i]['docType'];
    echo "</td>\n"; 
     
    $bg='background-color:White;';        
        if ($this->classArray[$i]['isSet']  == 1) $bg='background-color:LightGreen;';
        
    $action = "switchClass(".$this->classArray[$i]['classRef'].", ".$grp.")";
    $id = 'article_'.$this->classArray[$i]['classRef'];
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

/*******************************/

public function getClassifParam($classRef, $grpRef)
{
  $grpRef = intval($grpRef);
  $classRef= intval($classRef);
  $res = [ 'res' => false, 
           'classRef'  => $classRef, 
           'grpRef' => $grpRef,              
           'docTypeTitle' => 'N/A',
           'docClassTitle' => 'N/A',
         ];   

  if (!empty ($classRef)){
        $strSql = "SELECT docType from {{%doc_classify}} where id =:docClassifyRef"; 
        $res['docClassTitle'] = Yii::$app->db->createCommand($strSql,[ ':docClassifyRef' => $classRef,])->queryScalar();         
    } 
        
  if (!empty ($grpRef)){
        $strSql = "SELECT docGrpTitle from {{%doc_group}} where id =:docTypeRef"; 
        $res['docTypeTitle'] = Yii::$app->db->createCommand($strSql,[ ':docTypeRef' => $grpRef,])->queryScalar();         
    }       
       
   $res ['res'] = true;
   return  $res;   
}

 
  /************End of model*******************/ 
 }
