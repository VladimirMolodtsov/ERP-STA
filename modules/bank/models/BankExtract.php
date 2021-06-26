<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;
use app\modules\bank\models\BankExtractAssign;
use yii\data\ArrayDataProvider;

use SimpleXLSX;
/**
 * BankExtract - модель работы с выписками из банка
 
 */
 
 class BankExtract extends Model
{
    
    public $timeshift = 4*3600; //сдвиг по времени   
    public $showDate = 0;
    
    public $debug;
    public $xlsxFile;

    public $dataArray;
    public $syncArray;
    // фильтр
    public $userFIO;

    public $webSync = true;
    public $curYear="";
    
    public $fromDate = '';
    public $toDate = '';
    
   public function behaviors(){
    return [
        'access' => [
            'class' => \yii\filters\AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ],
    ];
  }    
    
    public function rules()
    {
        return [            
                  
             [['xlsxFile'], 'file', 'skipOnEmpty' => false],
            //[[ ], 'default'],                        
            [['userFIO'], 'safe'],            
        ];
    }

    
   /*********/
    
  /**************************/
 public static function ExcelToPHP($dateValue = 0) 
     { 
 /*  
 уберем - у нас очевидно виндовый файл  
     if (self::$ExcelBaseDate == self::CALENDAR_WINDOWS_1900) 
     { 
         $myExcelBaseDate = 25569; // Adjust for the spurious 29-Feb-1900 (Day 60) 
         if ($dateValue < 60) { --$myExcelBaseDate; } 
     } 
     else 
     { 
         $myExcelBaseDate = 24107; 
     } 
*/     
         $myExcelBaseDate = 25569; // Adjust for the spurious 29-Feb-1900 (Day 60) 
         if ($dateValue < 60) { --$myExcelBaseDate; } 
     
     // Perform conversion 
     if ($dateValue >= 1) 
     { 
         $utcDays = $dateValue - $myExcelBaseDate; 
         $returnValue = round($utcDays * 86400); 
         if (($returnValue <= PHP_INT_MAX) && ($returnValue >= -PHP_INT_MAX)) 
             {
              $returnValue = (integer) $returnValue; 
              } 
      } 
      else 
      { 
          $hours = round($dateValue * 24); 
          $mins  = round($dateValue * 1440) - round($hours * 60); 
          $secs  = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60); 
          $returnValue = (integer) gmmktime($hours, $mins, $secs); 
      } // Return
      
       return $returnValue; 
      } // function ExcelToPHP() 

 public static function ExcelToPHPObject($dateValue = 0) 
 {     
     /*Иногда в ячейках и правда дата видимо текстом?*/       
     if(preg_match("/\:/", $dateValue))
     {
        if (  !empty(strtotime($dateValue)))   return date_create($dateValue);     
     }
 
     $dateTime = self::ExcelToPHP($dateValue); 
     $days     = floor($dateTime / 86400); 
     $time     = round((($dateTime / 86400) - $days) * 86400); 
     $hours    = round($time / 3600); 
     $minutes  = round($time / 60) - ($hours * 60); 
     $seconds  = round($time) - ($hours * 3600) - ($minutes * 60);
     $dateObj  = date_create('1-Jan-1970+'.$days.' days'); 
     $dateObj->setTime($hours,$minutes,$seconds); 
     return $dateObj; 
 } // function ExcelToPHPObject()
  
/*************************************/  
public function upload()
 {
   if ($this->validate()) 
   {
      /*'/srv/www/htdocs/phone/uploads/'*/
      $uploadPath=__DIR__."/../uploads/";      
      $this->xlsxFile->saveAs( $uploadPath. $this->xlsxFile->baseName . '.' . $this->xlsxFile->extension);
      return true;
    } else 
    {
      return false;
    }
  }
  
/*************************************/  
/* парсим */
public function loadBankExtract($fname) 
{
   $curUserId =0; 
   if ($this->webSync)
   {
    $curUser=Yii::$app->user->identity;      
    $curUserId =$curUser->id; 
   }
 
   // Первый лист
  if (!($xlsx = SimpleXLSX::parse($fname)) ) 
  {    
     echo SimpleXLSX::parseError();    
     return false; 
  }
  
  
  $rows=$xlsx->rows(); 
  $cnt = count($rows);
//  $startRow = 11;
//  if ($rows[9][18] == 'Расчетных документов') $startRow = 16;
   /*честно найдем начало*/
  for ($startRow=2; $startRow < $cnt; $startRow++)
  {
    if ($rows[$startRow][1] == 'Дата проводки') break;
  }
  
  $headerRow = $rows[$startRow];
  $n = count($headerRow);
  
  $recordDateCol = 1;
  $debetArrayCol = 4;
  $creditArrayCol = 8;
  $debetSumCol = 9;
  $creditSumCol = 13;
  $docNumCol = 14;
  $VOCol = 16;
  $contrAgentBankCol = 17;
  $descriptionCol = 20;
  
  for ($i=2; $i < $n; $i++)
  {
    if ($rows[$startRow][$i] == 'Дата проводки') $recordDateCol= $i;
    if ($rows[$startRow+1][$i] == 'Дебет') $debetArrayCol= $i;
    if ($rows[$startRow+1][$i] == 'Кредит') $creditArrayCol= $i;
    if ($rows[$startRow][$i] == 'Сумма по дебету') $debetSumCol= $i;
    if ($rows[$startRow][$i] == 'Сумма по кредиту') $creditSumCol= $i;
    if ($rows[$startRow][$i] == '№ документа') $docNumCol= $i;
    if ($rows[$startRow][$i] == 'ВО') $VOCol= $i;
    if ($rows[$startRow][$i] == 'Банк (БИК и наименование)') $contrAgentBankCol= $i;
    if ($rows[$startRow][$i] == 'Назначение платежа') $descriptionCol= $i;
  }
  
  $startRow+=2;
  
  
  
 /*  
 for ($i=$startRow; $i<$cnt-7; $i++ )
  {
  if (empty($rows[$i][1]) ) continue;          
  echo $rows[$i][1]." ";
  $dateObj = $this->ExcelToPHPObject($rows[$i][1]);
  echo "Дата проводки: ".$dateObj->format('Y-m-d H:i:s')."<br>";         
  }  
  exit();
*/  
  //$this->debug = print_r ($rows, true);  

  
//20, 10    
  
  Yii::$app->db->createCommand("INSERT INTO {{%log}} (refUser,actionType,actionText) VALUES (:refUser, 10, 'Загрузка банковской выписки') ", 
  [':refUser' => $curUserId]) ->execute();  
  
  $recordHeader = new TblBankHeader();   
  $recordHeader->uploadTime= date("Y-m-d H:i:s", time());
  $recordHeader->refManager =$curUserId;
  //$this->debug .="Время выписки:  ".$this->ExcelToPHPObject($rows[1][1])->format('Y-m-d H:i:s')."\n";
    
  $recordHeader->creationDate = $this->ExcelToPHPObject($rows[1][1])->format('Y-m-d H:i:s');
  //$this->debug .="Остаток дебет:  ".$rows[$cnt-2][7]."\n";
  $debetRemain=intval(preg_replace("/[\D]/","",$rows[$cnt-2][7]));
  $recordHeader->debetRemain = floatval($debetRemain/100);
  // $this->debug .="Остаток кредит: ".$rows[$cnt-2][11]."\n";  
  $creditRemain=intval(preg_replace("/[\D]/","",$rows[$cnt-2][11]));
  $recordHeader->creditRemain = floatval($creditRemain/100);

  $i=$cnt-1;
  while(trim($rows[$i][1]) != 'Итого оборотов')
  {
  $i--;
  if ($i == 0)break;
  }
  //$debetTurn=intval(preg_replace("/[\D]/","",$rows[$cnt-3][7]));
  //$recordHeader->debetTurn = floatval($rows[$cnt-4][7]);  
  $recordHeader->debetTurn = floatval($rows[$i][7]);  
  //$creditTurn=intval(preg_replace("/[\D]/","",$rows[$cnt-3][11]));
  //$recordHeader->creditTurn = floatval($rows[$cnt-4][11]);
  $recordHeader->creditTurn = floatval($rows[$i][11]);
  $recordHeader->save();

  for ($i=$startRow; $i<$cnt-7; $i++ )
  {
  if ($rows[$i][1] == 'б/с') break;
  if (empty($rows[$i][$recordDateCol]) ) continue;        
       
   
  $recordContent = new TblBankContent();
  $recordContent->refBankHeader = $recordHeader->id;
  
  $dateObj = $this->ExcelToPHPObject($rows[$i][$recordDateCol]);
  //$this->debug .= "Дата проводки: ".$dateObj->format('Y-m-d H:i:s')."\n";         
  $recordContent->recordDate = $dateObj->format('Y-m-d H:i:s');
      
  $debetArray = preg_split('/\n/', $rows[$i][$debetArrayCol]);
  if (count ($debetArray) == 3 )
  {
      $recordContent->debetRS = mb_substr($debetArray[0],0,50,'utf-8');   
      $recordContent->debetINN = mb_substr($debetArray[1],0,20,'utf-8');   
      $recordContent->debetOrgTitle = mb_substr($debetArray[2],0,250,'utf-8');   
  }
  elseif (count ($debetArray) == 2 )
  {
    if (mb_strlen($debetArray[0],'utf-8')> 15 ) $recordContent->debetRS  = mb_substr($debetArray[0],0,50,'utf-8');   
                                           else $recordContent->debetINN = mb_substr($debetArray[0],0,20,'utf-8');                                              
                                                $recordContent->debetOrgTitle = mb_substr($debetArray[1],0,250,'utf-8');   
  }
  else
  {
    $recordContent->debetOrgTitle = mb_substr($debetArray[0],0,250,'utf-8');   
  }

  
  $creditArray = preg_split('/\n/', $rows[$i][$creditArrayCol]);  
  if (count ($creditArray) == 3 )
  {
      $recordContent->creditRs = mb_substr($creditArray[0],0,50,'utf-8');   
      $recordContent->creditINN = mb_substr($creditArray[1],0,20,'utf-8');   
      $recordContent->creditOrgTitle = mb_substr($creditArray[2],0,250,'utf-8');   
  }
  elseif (count ($creditArray) == 2 )
  {
    if (mb_strlen($creditArray[0],'utf-8')> 15 ) $recordContent->creditRs  = mb_substr($creditArray[0],0,50,'utf-8');   
                                            else $recordContent->creditINN = mb_substr($creditArray[0],0,20,'utf-8');                                              
                                                 $recordContent->creditOrgTitle = mb_substr($creditArray[1],0,250,'utf-8');   
  }
  else
  {
    $recordContent->creditOrgTitle = mb_substr($creditArray[0],0,250,'utf-8');   
  }

  
  //$this->debug .= "Сумма по дебету: ".$rows[$i][9]."\n";
  $recordContent->debetSum = floatval($rows[$i][$debetSumCol]);
  //  $this->debug .= "Сумма по кредиту: ".$rows[$i][13]."\n";
  $recordContent->creditSum = floatval($rows[$i][$creditSumCol]);
  //$this->debug .= "№ документа: ".$rows[$i][14]."\n";
  $recordContent->docNum = $rows[$i][$docNumCol];
  //$this->debug .= "ВО: ".$rows[$i][16]."\n";
  $recordContent->VO = $rows[$i][$VOCol];
  //$this->debug .="Банк (БИК и наименование): ".$rows[$i][17]."\n";
  $recordContent->contrAgentBank = mb_substr($rows[$i][$contrAgentBankCol],0,250,'utf-8');   
  //$this->debug .="Назначение платежа: ".$rows[$i][20]."\n";
  $recordContent->description  = $rows[$i][$descriptionCol];

    
/* 
 $recordDateCol = 1;
  $debetArrayCol = 4;
  $creditArrayCol = 8;
  $debetSumCol = 9;
  $creditSumCol = 13;
  $docNumCol = 14;
  $VOCol = 16;
  $contrAgentBankCol = 17;
  $descriptionCol = 20;

  reasonDocType
  reasonDocNum
  reasonDocDate
  reasonText
*/  
  
  $recordContent->save();
  }
  
  


  /*Выдергиваем уникальные*/
  $strsql= "INSERT INTO {{%bank_extract}}
 ( recordDate, debetRS, debetINN, debetOrgTitle, creditRs, creditINN, creditOrgTitle, debetSum,
  creditSum, docNum, contrAgentBank, description, VO,   
  reasonDocType,  reasonDocNum,   reasonDocDate,  reasonText)
 ( SELECT DISTINCT  a.recordDate, a.debetRS, a.debetINN, a.debetOrgTitle, a.creditRs, a.creditINN, a.creditOrgTitle,
 a.debetSum, a.creditSum, a.docNum, a.contrAgentBank, a.description, a.VO,
 a.reasonDocType,  a.reasonDocNum,   a.reasonDocDate,  a.reasonText
 from {{%bank_content}} as a  left join {{%bank_extract}} as b on 
 (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )  where b.id is null )";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*связываем с уникальными*/
   $strsql= "Update {{%bank_content}}  as a left join rik_bank_extract as b on 
   (a.recordDate = b.recordDate AND a.debetINN = b.debetINN AND a.creditINN = b.creditINN )
   SET a.refExtract = b.id  where   a.refExtract =0 and  b.id is not null"; 
   Yii::$app->db->createCommand($strsql)->execute();    
  
  
  /*метим Доходы*/
  $strsql= "update {{%bank_extract}} set extractType = 1  where creditSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*метим расходы*/
  $strsql= "update {{%bank_extract}} set extractType = 2,  contragentType = 1 where debetSum > 0 AND extractType = 0";  
  Yii::$app->db->createCommand($strsql)->execute();    
  
  /*цепляем ссылку на организацию*/
  $strsql= "UPDATE {{%bank_extract}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} group by orgINN) as org
    set orgRef = org.id where  ifnull(orgRef,0) = 0 AND org.orgINN = {{%bank_extract}}.debetINN AND org.n = 1 AND extractType = 1;";
  Yii::$app->db->createCommand($strsql)->execute();    
  
  $strsql= "UPDATE {{%bank_extract}}, (SELECT COUNT(id) as n, title, orgINN, id from {{%orglist}} group by orgINN) as org
    set orgRef = org.id where  ifnull(orgRef,0) = 0 AND org.orgINN = {{%bank_extract}}.creditINN AND org.n = 1 AND extractType = 2;";
  Yii::$app->db->createCommand($strsql)->execute();    

  /*пытаемся распределить */
  
  $assignModel= new BankExtractAssign();
  $assignModel -> scanExtract();
    
  unlink($fname);
}



 public function getLogData()
 {
     $strsql = "SELECT refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = 10 and actionDateTime > '".date("Y-m-d 04:04")."' ORDER BY actionDateTime"; 
     $list = Yii::$app->db->createCommand($strsql)->queryAll();    
     
     return $list;
 }
/*****************************/    
/**** Providers **************/    
/*****************************/
/* Список загруженных выписок */
 public function getBanctExtractionListProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            '{{%bank_header}}.id', 
            'creationDate', 
            'uploadTime', 
            'refManager', 
            'debetRemain', 
            'creditRemain', 
            'debetTurn',
            'creditTurn',
            'userFIO', 
            ])
            ->from("{{%bank_header}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%bank_header}}.refManager')
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%bank_header}}.id)")
            ->from("{{%bank_header}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%bank_header}}.refManager')
            ;            
     
     if (!empty($this->showDate))
     {
        $query->andFilterWhere(['=', 'DATE(uploadTime)', date("Y-m-d",$this->showDate)]);
        $countquery->andFilterWhere(['=', 'DATE(uploadTime)', date("Y-m-d",$this->showDate)]);     
     }
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     
             
     }

             $query->andFilterWhere(['>=', 'DATE(creationDate)', date('Y-m-d',strtotime($this->fromDate))]);
        $countquery->andFilterWhere(['>=', 'DATE(creationDate)', date('Y-m-d',strtotime($this->fromDate))]);           
     
             $query->andFilterWhere(['<=', 'DATE(creationDate)', date('Y-m-d',strtotime($this->toDate))]);
        $countquery->andFilterWhere(['<=', 'DATE(creationDate)', date('Y-m-d',strtotime($this->toDate))]);           

        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 15,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'id', 
            'creationDate', 
            'uploadTime', 
            'refManager', 
            'debetRemain', 
            'creditRemain', 
            'userFIO', 
            ],            
            
            'defaultOrder' => [  'uploadTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  /*******************************************/ 
  public function showDeny()
  {
    return "<font color='Crimson'><span class='glyphicon glyphicon-ban-circle'></span></font>";  
  }

  public function showGood()
  {
    return "<font color='Green'><span class='glyphicon glyphicon-ok-circle'></span></font>";  
  }

  
  public function getSverka($syncId, $extractId )
  {
    $strsql = "SELECT count({{%bank_content}}.id) from {{%bank_content}} 
           left join  {{%bank_extract}} on {{%bank_extract}}.id = {{%bank_content}}.refExtract 
           where ifnull({{%bank_extract}}.checkStatus,0) = 0 AND {{%bank_content}}.refBankHeader=:extractId"; 
   
     $extract= Yii::$app->db->createCommand($strsql)
     ->bindValue(':extractId', $extractId)->queryScalar();
     
     $strsql = "SELECT count({{%bank_op_content}}.id) from {{%bank_op_content}} 
           left join  {{%bank_operation}} on {{%bank_operation}}.id = {{%bank_op_content}}.refOperation 
           where ifnull({{%bank_operation}}.refBankExtract,0) = 0 and refBankOpHeader =:syncId"; 
     
     $operation= Yii::$app->db->createCommand($strsql)
     ->bindValue(':syncId', $syncId)->queryScalar();
     
     return [
     'extract'   =>$extract,
     'operation' =>$operation,
     ];
     
  }
  
  public function getExtractRef($from, $to)
  {
    if ($this->showDate == 0)$this->showDate = strtotime(date("Y-m-d"));
  
     $strsql = "SELECT {{%bank_header}}.id, creationDate, {{%user}}.userFIO from {{%bank_header}} 
           left join  {{%user}} on {{%user}}.id = refManager
           where uploadTime >= :from and uploadTime < :to ORDER BY id"; 
    
    /*вернем к серверному времени*/
     $fromDataTime = strtotime(date("Y-m-d", $this->showDate)." ".$from)-$this->timeshift;
     $toDataTime   = strtotime(date("Y-m-d", $this->showDate)." ".$to)-$this->timeshift;
    
       $list = Yii::$app->db->createCommand($strsql)
       ->bindValue(':from', date("Y-m-d H:i:s", $fromDataTime))
       ->bindValue(':to'  , date("Y-m-d H:i:s",$toDataTime))
       ->queryAll();    
    
   /*  echo   Yii::$app->db->createCommand($strsql)
       ->bindValue(':from', date("Y-m-d H:i:s", $fromDataTime))
       ->bindValue(':to'  , date("Y-m-d H:i:s",$toDataTime))
       ->getRawSql();*/
       
    if (count($list)>0) 
    {
      $dc = Yii::$app->db->createCommand("Select SUM(debetSum) as D, SUM(creditSum) as C from {{%bank_content}} where refBankHeader=:refBankHeader")
      ->bindValue(':refBankHeader', $list[0]['id'])
      ->queryAll();    
      
      return [
              'id'=>$list[0]['id'], 
              'creationDate' => $list[0]['creationDate'],
              'debet' => $dc[0]['D'],
              'credit' => $dc[0]['C'],
              'userFIO' => $list[0]['userFIO'],
              ];
    }
                  else    return ['id'=>0, 'creationDate' => '1970-01-01 00:00:00'];
  }
  
    
  public function getOperationRef($from, $to)
  {
     if ($this->showDate == 0)$this->showDate = strtotime(date("Y-m-d"));
     $strsql = "SELECT {{%bank_op_header}}.id, syncDateTime, {{%user}}.userFIO from {{%bank_op_header}}
           left join  {{%user}} on {{%user}}.id = refUser           
      where syncDateTime >= :from and syncDateTime < :to ORDER BY id"; 
    
        
     $fromDataTime = strtotime(date("Y-m-d", $this->showDate)." ".$from)-$this->timeshift;
     $toDataTime   = strtotime(date("Y-m-d", $this->showDate)." ".$to)-$this->timeshift;
    
       $list = Yii::$app->db->createCommand($strsql)
       ->bindValue(':from', date("Y-m-d H:i:s", $fromDataTime))
       ->bindValue(':to'  , date("Y-m-d H:i:s",$toDataTime))
       ->queryAll();    
    
     if (count($list)==0) return ['id'=>0, 'creationDate' => '1970-01-01 00:00:00'];
    
      $refBankOpHeader =$list[0]['id'];
    
      $strsql = "SELECT SUM(recordSum)  from {{%bank_op_content}} 
      where refBankOpHeader=:refBankOpHeader and recordSum > 0 ORDER BY id"; 
      
      $credit = Yii::$app->db->createCommand($strsql)
       ->bindValue(':refBankOpHeader', $refBankOpHeader)->queryScalar();     

     $strsql = "SELECT SUM(recordSum)  from {{%bank_op_content}} 
      where refBankOpHeader=:refBankOpHeader and recordSum < 0 ORDER BY id"; 
      
     $debet = Yii::$app->db->createCommand($strsql)
       ->bindValue(':refBankOpHeader', $refBankOpHeader)->queryScalar();     
   
     $debet = $debet*-1;
       
    
    if (count($list)>0) return [        
             'id'=>$list[0]['id'], 
              'creationDate' => $list[0]['syncDateTime'],
              'debet'  => $debet,
              'credit' => $credit,
              'userFIO' => $list[0]['userFIO'],
              
    ];
    
    
  }

  
  public function showAction($actionId, $strTime)
 {
     
     if ($this->showDate == 0)$this->showDate = strtotime(date("Y-m-d"));
     
     $strDataTime= date("Y-m-d", $this->showDate)." ".$strTime;
     
     $strsql = "SELECT refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = :actionType and actionDateTime >= :actionDateTime ORDER BY actionDateTime"; 
     
     switch ($actionId)
     {
       case 1:
       /*вход в систему*/
       $list = Yii::$app->db->createCommand($strsql)
       ->bindValue(':actionType', $actionId)
       ->bindValue(':actionDateTime', $strDataTime)
       ->queryAll();    
       
           if (count ($list) == 0) return $this->showDeny(); 
           else return $this->showGood();
       
       break;
     
     
     
     }
     
     
     return $list;
 }
/* Статистика по выпискам**/
/**************************************************************/
 public function prepareStatBankData($params)
   {
     if ( empty($this->curYear))  $this->curYear = date('Y');  

    $this->dataArray=[];
    
    $this->dataArray[0]['title'] ='по выписке(на начало)';
    $this->dataArray[1]['title'] ='по выписке (приход)';
    $this->dataArray[2]['title'] ='по выписке (Расходы)';
    $this->dataArray[3]['title'] ='по выписке(на конец)';
    $this->dataArray[4]['title'] ='по 1С:УТ (остаток на начало)';
    $this->dataArray[5]['title'] ='по 1С (приход) (отчёт № 31)';
    $this->dataArray[6]['title'] ='по 1С (расход) (отчёт № 31)';
    $this->dataArray[7]['title'] ='по 1С (остаток на конец = остаток + приход - расход)';
    $this->dataArray[8]['title'] ='остаток на конец (из 1С) (отчёт № 26)';

    for ($j=0; $j<9; $j++){
        $this->dataArray[$j]['id'] = $j;
        for ($i=0; $i<13; $i++) {            
            $this->dataArray[$j][$i]=0;//Инициируем
        }
    }
     $query  = new Query();                  
     $query->select([ 
        'SUM(creditSum) as creditSum', 
        'SUM(debetSum) as debetSum', 
        'MONTH(recordDate) as recordMonth'
        ]) 
		->from("{{%bank_extract}}")        
		->groupBy(['MONTH(recordDate)']);
	 
     $query->andWhere(['=', 'YEAR(recordDate)', $this->curYear]);  

    if (($this->load($params) && $this->validate())) {         

    }

	$list = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($list); $i++)
    {
        $m=$list[$i]['recordMonth'];
        $this->dataArray[1][$m] = $list[$i]['creditSum']; //'по выписке (приход)';
        $this->dataArray[2][$m] = $list[$i]['debetSum']; //'по выписке (Расходы)';

    }
    for ($i=1; $i<13; $i++){      
      $this->dataArray[3][$i]=$this->dataArray[1][$i]-$this->dataArray[2][$i]+$this->dataArray[0][$i];        
      if ($i<12) $this->dataArray[0][$i+1]=$this->dataArray[3][$i];        
    }   


     $query  = new Query();                  
     $query->select([ 
        'SUM(recordSum) as creditSum',         
        'MONTH(operationDate) as recordMonth'
        ]) 
		->from("{{%bank_operation}}")        
		->groupBy(['MONTH(operationDate)']);
	 
     $query->andWhere(['>', 'recordSum', 0]);  
     $query->andWhere(['=', 'YEAR(operationDate)', $this->curYear]);  

    if (($this->load($params) && $this->validate())) {         

    }

	$list = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($list); $i++)
    {
        $m=$list[$i]['recordMonth'];
        $this->dataArray[5][$m] = $list[$i]['creditSum']; 
    }
    
     $query  = new Query();                  
     $query->select([ 
        'SUM(recordSum) as debetSum',         
        'MONTH(operationDate) as recordMonth'
        ]) 
		->from("{{%bank_operation}}")        
		->groupBy(['MONTH(operationDate)']);
	 
     $query->andWhere(['<', 'recordSum', 0]);  
     $query->andWhere(['=', 'YEAR(operationDate)', $this->curYear]);  

    if (($this->load($params) && $this->validate())) {         

    }

	$list = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($list); $i++)
    {
        $m=$list[$i]['recordMonth'];
        $this->dataArray[6][$m] = -1*$list[$i]['debetSum']; 
    }
    
    
    
    for ($i=1; $i<13; $i++){      
      $this->dataArray[7][$i]=$this->dataArray[5][$i]-$this->dataArray[6][$i]+$this->dataArray[0][$i];        
      if ($i<12) $this->dataArray[4][$i+1]=$this->dataArray[3][$i];        
    }   

$this->syncArray=[];
//последняя строка  13  это рутенберг
    for ($i=1; $i<13; $i++){      
    $this->syncArray[$i] = "";
       $headerList =  Yii::$app->db->createCommand("SELECT id from {{%control_bank_header}}
       where  month(ondate) = :month and year(ondate) =".$this->curYear." order by onDate DESC, id DESC LIMIT 1")->bindValue(':month', $i)->queryAll();
       if (count($headerList) == 0) continue;
       $headerId= $headerList[0]['id'];

       $sum = Yii::$app->db->createCommand("SELECT sum(cashSum) from {{%control_bank}}
       where useRef = 13 and headerRef = :headerRef")->bindValue(':headerRef', $headerId )->queryScalar();
       $this->dataArray[8][$i]=$sum;
       
       $this->syncArray[$i] =  Yii::$app->db->createCommand("SELECT onDate from {{%control_bank_header}}
       where  id = :headerRef")->bindValue(':headerRef', $headerId )->queryScalar();
       
       
    }   



	
  }


   public function getStatBankProvider($params)		
   {
       
        $this->prepareStatBankData($params);
                
        $provider = new ArrayDataProvider([
            'allModels' => $this->dataArray,
            'totalCount' => count($this->dataArray),
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
			'title', 
			'id',
            ],
			
            'defaultOrder' => [    'id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
  
  
  /************End of model*******************/ 
 }
