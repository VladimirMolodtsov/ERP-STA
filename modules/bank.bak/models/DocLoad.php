<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\TblDocuments;
use app\modules\bank\models\TblDocHeader;
use app\modules\bank\models\TblOrgList;
/**
 * DocLoad - модель работы первичкой
 
 ALTER TABLE `rik_documents` MODIFY COLUMN `opertationType` BIGINT DEFAULT NULL COMMENT 'ссылка на doc_operation';
 ALTER TABLE `rik_documents` CHANGE COLUMN `opertationType` `operationType` BIGINT(20) DEFAULT NULL COMMENT 'ссылка на doc_operation';
 ALTER TABLE `rik_documents` MODIFY COLUMN `contragentType` BIGINT DEFAULT NULL COMMENT 'тип контрагента\r\n';
 update rik_documents set operationType=0, contragentType=contragentType+1
 */
 
 class DocLoad extends Model
{

/*Поля*/


public $id = 0; //'Номер регистрации документа',

public $docIntNum = ""; //'Номер регистрации документа',
  
public $orgTitle = ""; //  'Контрагент',  
public $refOrg = ""; // 
  

public $docTitle = ""; //  'Название',  
public $docType = ""; //  'тип документа',
 
public $docURI = ""; //  'Путь к документам',

public $docOrigStatus = ""; // '0-оригинал\r\n1-копия\r\n2-скан',
public $docOrigNum = ""; //  'номер документа (оригинальная нумерация)',
public $docOrigDate = ""; //  'Оригинальная дата',
public $docSum = ""; //  'сумма если есть',
 
public $isFinance = ""; //  'финансовый',
public $isOplate = ""; //  'Оплачиваемый',
 
public $docNote = ""; // 'комментарий',
public $ref1C_input = ""; //

public $docGoal="";
public $refDocHeader = ""; // 'ссылка на отчет об оцифровки',
public $refManager = ""; // 'ссылка на менеджера',
public $regDateTime = ""; // 'Дата и время регистрации',
public $docOwner ="";

public $showDate = ""; //'',
public $flt ="";


public $command;
public $count;


    
public $recordId = 0;
public $dataType = '';
public $dataVal = 0;


    public function rules()
    {
        return [                              
            [['docIntNum','orgTitle','refOrg','docTitle','docType','docURI','docOrigStatus','docOrigNum', 'docOrigDate', 'docGoal', 'docOwner',
            'docSum','isFinance','isOplate','docNote','ref1C_input','refDocHeader','refManager','regDateTime', 'id',
            'recordId', 'dataVal', 'dataType'
             ], 'default'],                        
            
              [['docOrigDate'], 'date', 'format'=>'php:d.m.Y'],
//            [['docOrigDate'], 'default', 'value' => function ($model, $attribute) {return date('d.m.Y');   }],
             [['docIntNum', 'orgTitle'], 'safe'],            
        ];
    }

    public function loadData()
    {

     if (empty($this->id))  
     {
       $this->docOrigDate=date('d.m.Y');
       return;
     }         
     $record= TblDocuments::findOne($this->id);
     if (empty($record))     
     {
       $this->docOrigDate=date('d.m.Y');
       return;
     }         

        $this->docIntNum      = $record->docIntNum;
        $this->orgTitle       = $record->orgTitle;
        //$this->refOrg         = record->refOrg;  
        $this->docTitle       = $record->docTitle;
        $this->docType        = $record->docType;
        $this->docURI         = $record->docURI;
        $this->docOrigStatus  = $record->docOrigStatus;
        $this->docOrigNum     = $record->docOrigNum;
        $this->docOrigDate    = date('d.m.Y', strtotime($record->docOrigDate));
        $this->docSum         = $record->docSum;
        $this->isFinance      = $record->isFinance;
        $this->isOplate       = $record->isOplate;
        $this->docNote        = $record->docNote; 
        $this->ref1C_input    = $record->ref1C_input;
        $this->refDocHeader   = $record->refDocHeader;
    }
  
    public function saveData()
    {

     if (empty($this->id))  $record= new TblDocuments();
     else                   $record= TblDocuments::findOne($this->id);
     if (empty($record)){ return false;  }         
     $curUser=Yii::$app->user->identity;


        $record->docIntNum = $this->docIntNum ;
        $record->orgTitle = $this->orgTitle;
        //$this->refOrg         = record->refOrg;  
        $record->docTitle = $this->docTitle;
        $record->docType = $this->docType;
        $record->docURI  = $this->docURI;
        $record->docOrigStatus = $this->docOrigStatus  ;
        $record->docOrigNum = $this->docOrigNum;
        $record->docOrigDate    = date('Y-m-d', strtotime($this->docOrigDate));
        $record->docSum = floatval($this->docSum);
        $record->isFinance = $this->isFinance     ;
        $record->isOplate = $this->isOplate;
        $record->docNote = $this->docNote; 
        $record->ref1C_input = $this->ref1C_input;
    
        $record->refManager =  $curUser->id;
        $record->refDocHeader   = $this->refDocHeader;
        $record->save();
     return true;
    }
/*************************************/

   public function getTypeArray()
   {
      $strSql = "SELECT id, typeTitle from {{%doc_type}} ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
      return ArrayHelper::map($list,'id','typeTitle');
   }  
 
   public function getOperationArray($typeRef)
   {
       $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$typeRef." ORDER BY id"; 
       $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
       return ArrayHelper::map($list,'id','operationTitle');       
   }  
    
/*************************************/
    public function saveAjaxData ()
    {
     $record = TblDocuments::findOne($this->recordId);
      $isSwitch =0;
    if (empty($record)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => 0, 'dataType' => $this->dataType];
    switch ($this->dataType)
    {
       case 'contragentType':
       $record->contragentType = $this->dataVal;
       $record->operationType = 0;
       break;
       case 'operationType':
       $record->operationType = $this->dataVal;
       break;
       case 'isTTN':
       if ($record->isTTN ==0) $record->isTTN = 1;
                          else $record->isTTN = 0;        
       $this->dataVal = $record->isTTN;
       $isSwitch = 1;
       break;
       
       case 'isUTR':
       if ($record->isUTR ==0) $record->isUTR = 1;
                          else $record->isUTR = 0;        
       $this->dataVal = $record->isUTR;
       $isSwitch = 1;
       break;
       
       case 'refOrg':
       $refOrg=intval($this->dataVal);
       $recordOrg=TblOrgList::findOne($refOrg);
       if (empty ($recordOrg)) return ['res' => false, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => 0, 'dataType' => $this->dataType];       
       $record->refOrg = $refOrg;
       $record->save();       
           return ['res' => true, 
                    'val' =>$this->dataVal, 
                    'id' => $this->recordId, 
                    'isSwitch' => $isSwitch, 
                    'dataType' => $this->dataType,
                    'orgINN' =>   $recordOrg->orgINN, 
                    'orgTitle' => $recordOrg->title
                  ];   
       break;
       
       
       
       
       
     }
    $record->save();    
    return ['res' => true, 'val' =>$this->dataVal, 'id' => $this->recordId, 'isSwitch' => $isSwitch, 'dataType' => $this->dataType];    
        
    }
/*****************************/    
/**** Providers **************/    
/*****************************/
/* Список загруженных документов */

 public function prepareDocLoadList($params)
 {

    $query  = new Query();
    $query->select ([
        '{{%documents}}.id',   
        'docIntNum',
        'regDateTime',
        'orgTitle',
        'docType',
        'docTitle',
        'docURI',
        'refManager',
        'refDocHeader',
        'docOrigStatus',
        'docOrigNum',
        'docOrigDate',
        'docSum',
        'isFinance',
        'isOplate',
        'docNote', 
        'userFIO', 
        'docGoal',
        'orgINN',
        'ref1C_input',
        'ref1C_schet',
        'docOwner',
        'contragentType',
        'operationType',
        'isTTN',
        'isUTR',
        'refOrg',
            ])
            ->from("{{%documents}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ;
            
    $countquery  = new Query();
    $countquery->select ("count({{%documents}}.id)")
            ->from("{{%documents}}")
            ->leftJoin('{{%user}}','{{%user}}.id = {{%documents}}.refManager')
            ;            
     
  /*   if (!empty($this->showDate))
     {
        $query->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);
        $countquery->andFilterWhere(['=', 'DATE(regDateTime)', date("Y-m-d",$this->showDate)]);     
     }
*/            


    if (!empty($this->flt))
    {
        
       switch  ($this->flt)
       {
           case 'buh' :
               $query->andFilterWhere(['like', 'docGoal', 'бухгалтерия']);
               $countquery->andFilterWhere(['like', 'docGoal', 'бухгалтерия']);      
           break;
       
           case 'office' :
               $query->andFilterWhere(['like', 'docGoal', 'офис']);
               $countquery->andFilterWhere(['like', 'docGoal', 'офис']);                            
           break;

           case 'ware' :
               $query->andFilterWhere(['like', 'docGoal', 'производство']);
               $countquery->andFilterWhere(['like', 'docGoal', 'производство']);                                      
           break;

           case 'buhdoc' :
               $query->andFilterWhere(['like', 'docOwner', 'бухгалтерия']);
               $countquery->andFilterWhere(['like', 'docOwner', 'бухгалтерия']);      
           break;
       
           case 'officedoc' :
               $query->andFilterWhere(['like', 'docOwner', 'офис']);
               $countquery->andFilterWhere(['like', 'docOwner', 'офис']);                            
           break;

           case 'waredoc' :
               $query->andFilterWhere(['like', 'docOwner', 'производство']);
               $countquery->andFilterWhere(['like', 'docOwner', 'производство']);                                      
           break;
                  
       }
    }
/*

*/
     if (($this->load($params) && $this->validate())) {

       $query->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);
       $countquery->andFilterWhere(['=', 'docIntNum', $this->docIntNum]);                 
       
       $query->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['Like', 'orgTitle', $this->orgTitle]);     

       $query->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]);
       $countquery->andFilterWhere(['Like', 'docOrigNum', $this->docOrigNum]);     
       
       if (!empty($this->docOrigDate))
       {
       $query->andFilterWhere(['=', 'docOrigDate', date("Y-m-d",strtotime($this->docOrigDate))]);
       $countquery->andFilterWhere(['=', 'docOrigDate',  date("Y-m-d",strtotime($this->docOrigDate))]);       
       }
       
       if (!empty($this->docOrigStatus))
       {
       $val = intval($this->docOrigStatus)-1;    
       $query->andFilterWhere(['=', 'docOrigStatus', $val]);
       $countquery->andFilterWhere(['=', 'docOrigStatus',  $val]);       
       }

       if (!empty($this->docGoal))
       {
       $val="";    
//       Бухгалтерия',  2 => 'Офис', 3 => 'Производство
       switch ($this->docGoal)    
       {
          case 1:
           $val="ухгалтерия";
          break;          
          case 2:
           $val="фис";
          break;          
          case 3:
           $val="роизводство";
          break;                     
       }
       $query->andFilterWhere(['Like', 'docGoal', $val]);
       $countquery->andFilterWhere(['Like', 'docGoal', $val]);     
   
       }
       
       if (!empty($this->docOwner))
       {
       $val="";    
//       Бухгалтерия',  2 => 'Офис', 3 => 'Производство
       switch ($this->docOwner)    
       {
          case 1:
           $val="ухгалтерия";
          break;          
          case 2:
           $val="фис";
          break;          
          case 3:
           $val="роизводство";
          break;                     
       }
       
       
        $query->andFilterWhere(['Like', 'docOwner', $val]);
        $countquery->andFilterWhere(['Like', 'docOwner', $val]);     
       }

       
       if (!empty($this->isFinance))
       {
       if (intval($this->isFinance) == 2)    $val = 0;    
       else       $val = 1;    
       $query->andFilterWhere(['=', 'isFinance', $val]);
       $countquery->andFilterWhere(['=', 'isFinance',  $val]);       
       }

       if (!empty($this->isOplate))
       {
       if (intval($this->isOplate) == 2)    $val = 0;    
       else       $val = 1;    
           
       $query->andFilterWhere(['=', 'isOplate', $val]);
       $countquery->andFilterWhere(['=', 'isOplate',  $val]);       
       }
       
       
           
 
     }
        
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
 
 
 }

 public function getDocLoadListData($params)
  {
  
    $this->prepareDocLoadList($params);    
    $dataList=$this->command->queryAll();

    $mask = realpath(dirname(__FILE__))."/../uploads/docLoaded*.csv";
    array_map("unlink", glob($mask));       
    $fname = "uploads/docLoaded".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","№"),
        iconv("UTF-8", "Windows-1251","Загружен"),        
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","Документ"),
        iconv("UTF-8", "Windows-1251","Дата"),
        iconv("UTF-8", "Windows-1251","На сумму"),
        iconv("UTF-8", "Windows-1251","Статус"),
        iconv("UTF-8", "Windows-1251","Комментарий"),
        iconv("UTF-8", "Windows-1251","Ответств."),
        iconv("UTF-8", "Windows-1251","Передать"),
        iconv("UTF-8", "Windows-1251","В оплату"),
        iconv("UTF-8", "Windows-1251","№ в 1C"),                
        iconv("UTF-8", "Windows-1251","счет в 1C"), 
     );
     fputcsv($fp, $col_title, ";"); 

     for ($i=0; $i< count($dataList); $i++)
    {        
        
        $regTime = strtotime($dataList[$i]['regDateTime']);
        if ($regTime > 100) $regDate = date("d.m.y H:i",$regTime +4*3600);
        else  $regDate ="";
        
        $docOrigTime = strtotime($dataList[$i]['docOrigDate']);
        if ($docOrigTime  > 100) $docOrigDate = date("d.m.y", $docOrigTime);
        else  $docOrigDate ="";
        
        $doc = $dataList[$i]['docTitle']." ".$dataList[$i]['docOrigNum'];

       switch ($dataList[$i]['docOrigStatus']){
                case  1: $docOrigStatus = "Копия"; break;
                case  2: $docOrigStatus = "Скан"; break;
                default: $docOrigStatus = "Оригинал"; break;
        }
        
        
        if ($dataList[$i]['isOplate'] == 1) $isOplate = "да";
                                      else  $isOplate = "";
        
        
        $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docIntNum']),    
            iconv("UTF-8", "Windows-1251",$regDate),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']),
            iconv("UTF-8", "Windows-1251",$doc),
            iconv("UTF-8", "Windows-1251",$docOrigDate),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docSum']),
            iconv("UTF-8", "Windows-1251",$docOrigStatus ),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docNote']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docGoal']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['docOwner']),
            iconv("UTF-8", "Windows-1251",$isOplate), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C_input']),
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C_schet']),                  
           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return "/modules/bank/".$fname;        

 
 
  }
 
 public function getDocLoadListProvider($params)
   {
    
$this->prepareDocLoadList($params);

    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
                        
            'attributes' => [        
            'docIntNum',
            'orgTitle',            
            'docTitle',
            'docOrigStatus',
            'docOrigNum',
            'docOrigDate',
            'docSum',
            'isFinance',
            'isOplate',            
            'regDateTime',
            'docGoal',
            'orgINN',  
            'docOwner' , 
            'ref1C_input',
            'ref1C_schet',
        'contragentType',
        'operationType',
        'isTTN',
        'isUTR',
     
            ],            
            
            'defaultOrder' => [  'regDateTime' => SORT_DESC ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  /*******************************************/ 

    
    
  
  /************End of model*******************/ 
 }
