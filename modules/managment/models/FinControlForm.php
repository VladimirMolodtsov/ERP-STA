<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 

use app\modules\managment\models\TblFinCheckHeader;
use app\modules\managment\models\TblFinCheckContent;
use app\modules\managment\models\TblFinCheckRow;
use app\modules\managment\models\TblFinCheckBuhCfg;
use app\modules\managment\models\TblFinCheckUtCfg;
use app\modules\managment\models\TblFinCheckDocCfg;

use app\modules\bank\models\BuhStatistics;


/**
 * FinControlForm  - модель контроля финансовой активности
 */


class FinControlForm extends Model
{

    public $id=0;
    
    
    public $controlTime=0;
    public $controlDate=0;
    public $headerRef=0;
   
    public $syncDateTime=0;        
        
    public $rowTitle    = "Новый параметр";

    public $debug=[];   
    public $err=[];   
        
    public $docTypeControl; 
    public $extractType;   
    public $docType;   
        
        
    public $dataRequestId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataRowId =0;
        
        
    public function rules()
    {
        return [
              [['rowTitle',  'docTypeControl', 'extractType', 'docType', 'dataRequestId', 'dataVal', 'dataType', 'dataRowId'], 'default'],
              [[ ], 'safe'],
        ];
    }
    
   /***************************/     
    

  public function syncControl($controlTime)
  {
     $this->controlTime=$controlTime; 
     $headerRef= $this->checkData();
     $curUser=Yii::$app->user->identity;
 
     /*Если нет заголовка*/
     if ($headerRef == 0) {
         $record = new TblFinCheckHeader();
         $record->refUser = $curUser->id;
         $record->onDate  = $this->controlDate;
         $record->syncDate = date("Y-m-d H:i:s", time());         
         $record->save();
         $headerRef = $record->id;
     }

      $strSql= "INSERT INTO {{%fin_check_content}} (headerRef, refRow)
         SELECT :headerRef, a.id from {{%fin_check_row}} as a 
         left join (SELECT id, headerRef, refRow FROM {{%fin_check_content}}  where headerRef=:headerRef) as b 
         on b.refRow = a.id
         where b.id is null";
     
/*     
INSERT INTO rik_control_sverka_dolga_use (orgTitle, orgINN, orgKPP)

SELECT a.orgTitle, a.orgINN, a.orgKPP
from rik_control_sverka_dolga as a
left join  rik_control_sverka_dolga_use as b
on (a.orgINN = b.orgINN and a.orgKPP = b.orgKPP)
where b.id is null     
*/         
         Yii::$app->db->createCommand(
            $strSql, [ ':headerRef' => $headerRef,])->execute();          

     
     
     $strSql= "Select {{%fin_check_content}}.id, {{%fin_check_content}}.refRow, {{%fin_check_row}}.dataType from {{%fin_check_content}}, {{%fin_check_row}} where {{%fin_check_content}}.refRow = {{%fin_check_row}}.id AND headerRef = :headerRef";
     $list = Yii::$app->db->createCommand(
            $strSql, [ ':headerRef' => $headerRef,])->queryAll();          

$this->debug[]=$list;
                 
     /*готовим что и как плюсовать*/
     $strSql= "Select rowRef, statRef, mult, isPrev  from {{%fin_check_ut_cfg}} order by rowRef";
     $utList = Yii::$app->db->createCommand($strSql)->queryAll();          
     $utCfgArray=[];
     $nU= count($utList); 
     for ($i=0;$i<$nU; $i++)
     {
       $rowRef = $utList[$i]['rowRef'];
       $utCfgArray[$rowRef][]=[
       'statRef'  => $utList[$i]['statRef'],
       'mult'     => $utList[$i]['mult'],
       'isPrev'   => $utList[$i]['isPrev'],    
       ];
     
     }  
     
     
     //Получим данные статистики
     $buhmodel   = new BuhStatistics();
     $buhmodel->dtstart = date("Y-m-d", $this->controlTime);
     $buhmodel->prepareBuhStatData("");
      
      $j=5;
        while($j>1)
        {
         if($buhmodel->checkedList[$j-1] == 1) break;
         $j--;         
        }    
        if ($j < 1 ) { $pkey='v0'; $pckey = 'c0'; }
        else         { $pkey='v'.$j; $pckey = 'c'.$j;} 
  
//$this->debug[]=$buhmodel->dataArray; 
           
    $nL= count($list); 
    for ($i=0;$i<$nL; $i++)
    {
      // пропустим для которых считать нечего
      if (!array_key_exists($list[$i]['refRow'], $utCfgArray ))continue;
    
      $rec= TblFinCheckContent::findOne($list[$i]['id']);
      if (empty($rec)) continue;      
       
      $cfgRowArray=$utCfgArray[$list[$i]['refRow']]; 
      $valM =0; $v="v6";
      $valA =0; $c="c6";             
      for ($j=0; $j < count($cfgRowArray); $j++ )
      {
      if ($cfgRowArray[$j]['mult'] == 0) continue;
         if ($cfgRowArray[$j]['isPrev']==1 ) {$v=$pkey;$c=$pckey;}
                                        else {$v='v6';$c='c6';}
         $statRef=$cfgRowArray[$j]['statRef'];                               
         $valM += $cfgRowArray[$j]['mult']*$buhmodel->dataArray[$statRef][$v];  
         $valA += $cfgRowArray[$j]['mult']*$buhmodel->dataArray[$statRef][$c];  
      }
       $rec->valUTm= $valM;      
       $rec->valUTa= $valA;
       $rec->save();
    
    }      
   /* пошла синхронизация с 1С */     
//  echo "<pre>";        
      $sD= date("dmY",$controlTime);
      $eD= date("dmY",$controlTime);
      $url = $this->getCfgValue(35);          
      $loadurl =  $url."&sd=".$sD."&ed=".$eD;          

     /*готовим что и как плюсовать*/
     $strSql= "Select rowRef, mult, accdt, acckt  from {{%fin_check_buh_cfg}} order by rowRef";
     $buhList = Yii::$app->db->createCommand($strSql)->queryAll();    
     $buhCfgArray=[];
     $nB= count($buhList); 
     for ($i=0;$i<$nB; $i++)
     {
       $rowRef = $buhList[$i]['rowRef'];
       if ($buhList[$i]['mult'] == 0) continue;       
       $buhCfgArray[$rowRef][]=[
          'mult'    => $buhList[$i]['mult'],
          'accdt'  => $buhList[$i]['accdt'],
          'acckt'  => $buhList[$i]['acckt'],    
       ];
     
     }  

    for ($i=0;$i<$nL; $i++)
    {
      // пропустим для которых считать нечего
      if (!array_key_exists($list[$i]['refRow'], $buhCfgArray ))continue;
    
      $rec= TblFinCheckContent::findOne($list[$i]['id']);
      if (empty($rec)) continue;      
       
      $cfgRowArray=$buhCfgArray[$list[$i]['refRow']]; 
      $val =0;      
      for ($j=0; $j < count($cfgRowArray); $j++ )
      {
      $url = $loadurl;
        if (!empty($cfgRowArray[$j]['accdt'])) $url .= "&accdt=".$cfgRowArray[$j]['accdt'];
        if (!empty($cfgRowArray[$j]['acckt'])) $url .= "&acckt=".$cfgRowArray[$j]['acckt'];        
        $page= $this->get_web_page($url);
        
        if (!empty($page['errno']) ) {
            $this->err[]=$url;        
            continue;        
        }
        $page['content']=str_replace(',', '.', $page['content']);            
        $loadedVal=floatval(preg_replace('/[^\d.]/','',$page['content']));
        
/*$this->debug[]=
[
'refRow' =>$list[$i]['refRow'],
'url' => $url,
'content' => $page['content'],
'val'  => $loadedVal
];*/
        
        //print_r($loadedVal);
        $val += $cfgRowArray[$j]['mult']*$loadedVal;  
         
      }
       $rec->valBuh_a= $val;      
       $rec->save();    
    }      

     
/* Документы */            
    for ($i=0;$i<$nL; $i++)
    {
      if ($list[$i]['dataType'] == 0) continue; // Пропустим то что заносится вручную
      
      $rec= TblFinCheckContent::findOne($list[$i]['id']);
      if (empty($rec)) continue;      

      if ($list[$i]['dataType'] == 1)
      {
        //Из выписки
      
        $strSql = "Select sum(debetSum*mult+creditSum*mult) from {{%bank_extract}}, {{%fin_check_doc_cfg}} 
                    where  {{%bank_extract}}.operationType = {{%fin_check_doc_cfg}}.refOperation
                    and {{%fin_check_doc_cfg}}.refRowReport = :refRowReport
                    and DATE({{%bank_extract}}.recordDate)  = :docDate";
      
      
       $rec->valDoc = Yii::$app->db->createCommand(
            $strSql, [ 
            ':refRowReport' => $list[$i]['refRow'],
            ':docDate' => date("Y-m-d", $this->controlTime),
            ])->queryScalar(); 
            
      $this->debug[]=Yii::$app->db->createCommand(
            $strSql, [ 
            ':refRowReport' => $list[$i]['refRow'],
            ':docDate' => date("Y-m-d", $this->controlTime),
            ])->getRawSql();
      $this->debug[]=$rec->valDoc;                  
            
      
      }
      
      if ($list[$i]['dataType'] == 2)
      {
        //Из документов
        $strSql = "Select sum(docSum*mult) from {{%documents}}, {{%fin_check_doc_cfg}} 
                    where  {{%documents}}.operationType = {{%fin_check_doc_cfg}}.refOperation
                    and {{%fin_check_doc_cfg}}.refRowReport = :refRowReport
                    and {{%documents}}.docOrigDate  = :docDate";
      
      
       $rec->valDoc = Yii::$app->db->createCommand(
            $strSql, [ 
            ':refRowReport' => $list[$i]['refRow'],
            ':docDate' => date("Y-m-d", $this->controlTime),
            ])->queryScalar(); 

            
      
      }
       
        $rec->save();    
    }        
  // print_r($buhmodel->dataArray); 
    
  }


    
  /***************************/ 
  public function addNewControlRow()
  {
     $record = new TblFinCheckRow();
     $record->rowTitle = 'Новый параметр';
     $record->save();
  }

  /***************************/ 
  public function saveData()
  {
     $record = TblFinCheckRow::findOne($this->id);
     if (empty($record)) return false;
     $record->rowTitle = $this->rowTitle;     
     $record->save();
     return true;
  }
    
  /***************************/ 
  public function loadData()
  {
     $record = TblFinCheckRow::findOne($this->id);
     if (empty($record)) return false;
     $this->rowTitle = $record->rowTitle;     
     $this->docType = $record->dataType;    
     $record->save();
  }
  
  
  /*************************************/
    public function saveCfgData ()
    {
    
    $res = ['res' => false, 
            'val' =>$this->dataVal, 
            'dataRowId' => $this->dataRowId,  
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType];
          
     switch ($this->dataType)
     {
       case 'docType':
        $record = TblFinCheckRow::findOne($this->dataRequestId);      
        if (empty($record)) return $res;       
        $record->dataType = $this->dataVal;
        $record->save();    
       break;       
       
       case 'docCfgForm':
        $record = TblFinCheckDocCfg::findOne([
        'refOperation' => $this->dataRowId,
        'refRowReport' => $this->dataRequestId,
        ]);      
        if (empty($record)){
        $record = new TblFinCheckDocCfg();      
        $record -> refOperation = $this->dataRowId;
        $record -> refRowReport = $this->dataRequestId;
        }
        if (empty($record)) return $res;       
        $record->mult = $this->dataVal;
        $record->save();    
       break;       

       case 'utDivCurrent':
        if ($this->addStatRow($this->dataRequestId, $this->dataRowId, $this->dataVal, 0 ) == false) return $res;               
       break;       
       
       case 'utDivPrev':
        if ($this->addStatRow($this->dataRequestId, $this->dataRowId, $this->dataVal, 1 ) == false) return $res;               
       break;       
             
     }
     
     $res['res'] = true;      
      return $res;
    }

    
//                
  /***************************/ 
  /*************************************/
    public function saveControlData ()
    {
    
    $res = ['res' => false, 
            'val' =>$this->dataVal, 
            'dataRowId' => $this->dataRowId,  
            'dataRequestId' => $this->dataRequestId, 
            'dataType' => $this->dataType];
          
     switch ($this->dataType)
     {
       case 'docEdit':
        $record = TblFinCheckContent::findOne($this->dataRequestId);      
        if (empty($record))  return $res;       
        $record->valDoc = floatval($this->dataVal);
        $record->save();    
       break;       
       
     }
     
     $res['res'] = true;      
      return $res;
    }

    
//                
  /***************************/ 

    public function checkData()
   {
        
    if ($this->controlTime == 0 )
    {
        $headerRef =  Yii::$app->db->createCommand('SELECT MAX(id) FROM {{%fin_check_header}}')->queryScalar();            
        $this->controlDate =   Yii::$app->db->createCommand('SELECT onDate FROM {{%buh_schet_header}} WHERE  id= :headRef', 
                      [ ':headRef' => $headerRef]       )->queryScalar();            
        $this->controlTime= strtotime($this->controlDate);
        
    } else
    {       
        $this->controlDate = date('Y-m-d', $this->controlTime);

    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%fin_check_header}} WHERE DATE(onDate) =:onDate', 
            [ ':onDate' => $this->controlDate,])->queryScalar();        
            
    }        
    
    if (empty($headerRef))$headerRef=0; //от пустой строки
    
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%fin_check_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $this->headerRef =  $headerRef;
    return $headerRef;
   }    
   
  /***************************/ 
  public function getFinControlProvider($param)
   {
        
    $headerRef = $this->checkData();    
   
    $strSubSql = "(SELECT refRow, valDoc, valUTm, valUTa, valBuh_m, valBuh_a, id
                   FROM {{%fin_check_content}} WHERE headerRef = ".$headerRef.") as c";
    
    $query  = new Query();
    $query->select ([ 'a.id as id',  
                      'a.rowTitle',
                      'a.dataType',
                      'ifnull(c.valDoc,0) as valDoc',
                      'ifnull(c.valUTm,0) as valUTm',
                      'ifnull(c.valUTa,0) as valUTa',
                      'ifnull(c.valBuh_m,0) as valBuh_m',
                      'ifnull(c.valBuh_a,0) as valBuh_a',
                      'ifnull(c.id,0) as contentRef',
                      
                      ])
            ->from("{{%fin_check_row}} as a")
            ->leftJoin($strSubSql, "c.refRow = a.id")
            ->distinct();
            ;
        
       
    $command = $query->createCommand(); 
    $count =  count($query->createCommand()->queryAll());             
    
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
               'rowTitle',
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
                                 
   }   
  /***************************/ 
  /*  Конфигурирование       */
  /***************************/ 
  
  public function addBuhRow($rowRef )
    {
     
     $record = new TblFinCheckBuhCfg();  
     $record->rowRef = $rowRef;
     $record->save();        
    }
    
  public function setDt($id, $accdt)
  {  
     $record = TblFinCheckBuhCfg::findOne($id);
     if (empty($record)) return false;
     $record->accdt = mb_substr($accdt,0, 20,'utf-8');
     $record->save();        
     return true;
  }
  public function setKt($id, $acckt)
  {  
     $record = TblFinCheckBuhCfg::findOne($id);
     if (empty($record)) return false;
     $record->acckt = mb_substr($acckt,0, 20,'utf-8');
     $record->save();        
     return true;
  }
  public function setDiv($id, $div)
  {  
     $record = TblFinCheckBuhCfg::findOne($id);
     if (empty($record)) return false;
     $record->mult = $div;
     $record->save();        
     return true;
  }
  public function setNote($id, $note)
  {  
     $record = TblFinCheckBuhCfg::findOne($id);
     if (empty($record)) return false;
     $record->note = mb_substr($note,0, 150,'utf-8');
     $record->save();        
     return true;
  }
    
    
  /****/  
  public function  addStatRow($rowRef, $statRef, $div, $isPrev )            
  {
  
    $record = TblFinCheckUtCfg::findOne([
    'rowRef'  => $rowRef, 
    'statRef' => $statRef,
    'isPrev'  => $isPrev,
    ]);  
    if (empty($record))  $record = new TblFinCheckUtCfg();       
    if (empty($record)) return false;
    
    $record->rowRef  = $rowRef;
    $record->statRef = $statRef;
    $record->mult     = $div;
    $record->isPrev  = $isPrev;
    
    $record->save();
    return true;
  }
  
  public function getFinControlUtCfgProvider($params)
   {
           
    $query  = new Query();
    $query->select ([ 'id',  
                      'accdt',
                      'acckt', 
                      'note',
                      'mult as div',                       
                      ])
            ->from("{{%fin_check_buh_cfg}}")
            ->distinct();
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(id))")
            ->from("{{%fin_check_buh_cfg}}")            
            ;            
         $query->andWhere('rowRef = '.$this->id);
    $countquery->andWhere('rowRef = '.$this->id);
    
    /*if (($this->load($params) && $this->validate())) {
     }*/
          
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
              'accdt',
              'acckt', 
              'note',
            ],            
            'defaultOrder' => [ 'id' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  public function getDocProvider($params)
   {
           
    $query  = new Query();
    $query->select ([ 'refDocType',  
                      'typeTitle',
                      '{{%doc_operation}}.id as refOperation',
                      'operationTitle',
                      //'mult as div',                       
                      ])
            ->from("{{%doc_type}}")
            ->leftJoin("{{%doc_operation}}", "{{%doc_type}}.id = {{%doc_operation}}.refDocType")
            ->distinct();
            ;
        
    $list = $query->createCommand()->queryAll(); 

              
    $command = $query->createCommand(); 
    $count = count($list);
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 30,
            ],
            
            'sort' => [
            
            'attributes' => [
              'typeTitle',  
              'operationTitle',
            ],            
            'defaultOrder' => [ 'typeTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
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
 
