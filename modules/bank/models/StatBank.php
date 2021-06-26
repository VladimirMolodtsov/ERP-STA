<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;
use yii\data\ArrayDataProvider;

/**
 * StatBank - Сводная статистика по банку
 
 */
 
 class StatBank extends Model
{
    
    public $showDate = 0;
    
    public $debug;

    public $dataArray;
    public $syncArray;
    // фильтр
    public $userFIO;

    public $webSync = true;
    public $curYear="";
    public $curMonth="";
    
    public $command;
    
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
    
/*****************************/    
/**** Providers **************/    
/*****************************/

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
    $this->dataArray[7]['title'] ='по 1С (остаток на конец)';// = остаток + приход - расход)';
   // $this->dataArray[8]['title'] ='остаток на конец (из 1С) (отчёт № 26)';

    for ($j=0; $j<8; $j++){
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
    $query->andWhere(['=', 'ownerOrgRef', 2]);  
    
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
     $query->andWhere(['=', 'ownerOrgRef', 2]);  

    if (($this->load($params) && $this->validate())) {         

    }

	$list = $query->createCommand()->queryAll();  
    for ($i=0; $i< count($list); $i++)
    {
        $m=$list[$i]['recordMonth'];
        $this->dataArray[6][$m] = -1*$list[$i]['debetSum']; 
    }
    
    
    
    for ($i=1; $i<13; $i++){      
      $this->dataArray[7][$i]=$this->dataArray[5][$i]-$this->dataArray[6][$i]+$this->dataArray[4][$i];        
      if ($i<12) $this->dataArray[4][$i+1]=$this->dataArray[7][$i];        
    }   


for ($i=1; $i<13; $i++){      

   $this->dataArray[0]["c_".$i] = intval(100*($this->dataArray[0][$i]-$this->dataArray[4][$i]))/100;
   $this->dataArray[1]["c_".$i] = intval(100*($this->dataArray[1][$i]-$this->dataArray[5][$i]))/100;
   $this->dataArray[2]["c_".$i] = intval(100*($this->dataArray[2][$i]-$this->dataArray[6][$i]))/100;
   $this->dataArray[3]["c_".$i] = intval(100*($this->dataArray[3][$i]-$this->dataArray[7][$i]))/100;

   $this->dataArray[4]["c_".$i] = intval(100*($this->dataArray[0][$i]-$this->dataArray[4][$i]))/100;
   $this->dataArray[5]["c_".$i] = intval(100*($this->dataArray[1][$i]-$this->dataArray[5][$i]))/100;
   $this->dataArray[6]["c_".$i] = intval(100*($this->dataArray[2][$i]-$this->dataArray[6][$i]))/100;
   $this->dataArray[7]["c_".$i] = intval(100*($this->dataArray[3][$i]-$this->dataArray[7][$i]))/100;

   //$this->dataArray[8]["c_".$i] = 0;
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
       //$this->dataArray[8][$i]=$sum;
       
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
   /************************/  
   
   

  public function prepareBankExtract($params)
   {

     if ( empty($this->curYear))   $this->curYear  = date('Y');  
     if ( empty($this->curMonth))  $this->curMonth = date('n');  

    $query  = new Query();    
    $query->select ([
            '{{%bank_extract}}.id', 
            'recordDate', 
            'debetRS', 
            'debetINN', 
            'debetOrgTitle', 
            'creditRs', 
            'creditINN', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'docNum', 
            'contrAgentBank', 
            'description', 
            'VO', 
            'refOplata as refClientOplata',
            'refSupplierOplata',
            'extractType',
            'orgRef',
            '{{%bank_extract}}.contragentType',
            'operationType',
            '{{%orglist}}.title as orgTitle',
            'description'
            ])
            ->from("{{%bank_extract}}")
            ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%bank_extract}}.orgRef")
            ->leftJoin("{{%doc_extract_lnk}}", "{{%doc_extract_lnk}}.extractRef = {{%bank_extract}}.id")
            ;
            
            
     if (($this->load($params) && $this->validate())) {

     }
     

     $query->andWhere(['=', 'YEAR(recordDate)', $this->curYear]);  
     $query->andWhere(['=', 'MONTH(recordDate)', $this->curMonth]);  
     
    print_r($query->createCommand()->getRawSql()); 
    $this->command = $query->createCommand(); 

   }
   
   
  public function getBankExtract ($params)		
   {
        $this->prepareBankExtract($params);   		
        $dataList=$this->command->queryAll() ;
 
    $mask = realpath(dirname(__FILE__))."/../uploads/bankExtractShow*.csv";
    array_map("unlink", glob($mask));        
    $fname = "uploads/bankExtractShow".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Проведено"),
        iconv("UTF-8", "Windows-1251","П/П"),
        iconv("UTF-8", "Windows-1251","Плательщик"),
        iconv("UTF-8", "Windows-1251","ИНН Плательщика"),
        iconv("UTF-8", "Windows-1251","Получатель"),     
        iconv("UTF-8", "Windows-1251","ИНН Получателя"),        
        iconv("UTF-8", "Windows-1251","Расход"), 
        iconv("UTF-8", "Windows-1251","Приход"), 

        iconv("UTF-8", "Windows-1251","Назначение"),  
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
       
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['recordDate']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['docNum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetOrgTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetINN']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditOrgTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditINN']),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['debetSum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['creditSum']),  

		iconv("UTF-8", "Windows-1251",$dataList[$i]['description']),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return "/modules/bank/".    $fname;           
   }
   /******************************/
   
  public function prepareBankOp($params)
   {

     if ( empty($this->curYear))   $this->curYear  = date('Y');  
     if ( empty($this->curMonth))  $this->curMonth = date('n');  

    $query  = new Query();    
    $query->select ([
            'operationDate', 
            'regNum',
            'ownerTitle',
            'orgTitle',
            'orgINN',
            'orgKPP',
            'recordSum',
            'article',
            'regNote',
            'sdelkaDate',
            'operationNum'
            ])
            ->from("{{%bank_operation}}")
            ;
            
            
     if (($this->load($params) && $this->validate())) {

     }
     

     $query->andWhere(['=', 'YEAR(operationDate)', $this->curYear]);  
     $query->andWhere(['=', 'MONTH(operationDate)', $this->curMonth]);  
     $query->andWhere(['=', 'ownerOrgRef', 2]);  

    $this->command = $query->createCommand(); 

   }
   
   
  public function getBankOp ($params)		
   {
        $this->prepareBankOp($params);   		
        $dataList=$this->command->queryAll() ;
 
    $mask = realpath(dirname(__FILE__))."/../uploads/bankOperation*.csv";
    array_map("unlink", glob($mask));        
    $fname = "uploads/bankOperation".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
    
        iconv("UTF-8", "Windows-1251","Дата"),                
        iconv("UTF-8", "Windows-1251","Номер"),
        iconv("UTF-8", "Windows-1251","Дата сделки"),        
        iconv("UTF-8", "Windows-1251","Номер сделки"),
        iconv("UTF-8", "Windows-1251","Собственник"),
        iconv("UTF-8", "Windows-1251","Контрагент"),     
        iconv("UTF-8", "Windows-1251","ИНН Контрагента"),        
        iconv("UTF-8", "Windows-1251","КПП Контрагента"),     
        iconv("UTF-8", "Windows-1251","Сумма"), 
        iconv("UTF-8", "Windows-1251","Статья"), 
        iconv("UTF-8", "Windows-1251","Примечание"),  
        );
        fputcsv($fp, $col_title, ";"); 

    	
    for ($i=0; $i< count($dataList); $i++)
    {        
     
    $list = array 
        (
        iconv("UTF-8", "Windows-1251",$dataList[$i]['operationDate']),          
        iconv("UTF-8", "Windows-1251",$dataList[$i]['regNum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['sdelkaDate']), 
        iconv("UTF-8", "Windows-1251",$dataList[$i]['operationNum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['ownerTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['orgINN']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['orgKPP']),  
        
        iconv("UTF-8", "Windows-1251",$dataList[$i]['recordSum']),  
        iconv("UTF-8", "Windows-1251",$dataList[$i]['article']),  

		iconv("UTF-8", "Windows-1251",$dataList[$i]['regNote']),
        
        );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return "/modules/bank/".    $fname;           
   }
   
/***********/

   

 
  
  /************End of model*******************/ 
 }
