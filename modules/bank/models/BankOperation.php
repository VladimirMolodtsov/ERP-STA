<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankOpHeader;
use app\modules\bank\models\TblBankOpContent;

use app\modules\bank\models\TblBankExtract;



/**
 * BankOperation - модель работы с  банковскими операциями из 1С
 */
 
 class BankOperation extends Model
{
    
    public $debug;
    
    public $orgTitle="";
    public $refOrg=0;
    
    public $from = 0;
    public $to = 0;
    
    public $refExtract=0;
    
    // фильтр
    public $userFIO;
    public $id=0;
    public $webSync = true;
    
    
    
    
    
    public function rules()
    {
        return [            
            
            //[[ ], 'default'],                        
            [['userFIO', 'orgTitle'], 'safe'],            
        ];
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
    
/*****************************************/
/********** Загрузка  ********************/     
/*****************************************/          
     public function syncOperations($start, $end, $now)
     {
          mb_internal_encoding("UTF-8");
          //$res=array();
    $curUserId = 0;
/*
Получим данные из 1с
http://a0202654.xsph.ru/rik/web/index.php?r=/bank/operator/sync-bank-operation&sd=16062019&ed=18062019
*/          
     if($this->webSync == true){          
          $curUser=Yii::$app->user->identity; 
          $curUserId = $curUser->id;
     }


     
if ($end <= $start)$end = $start+24*3600;
$sd=date('dmY', $start);
$ed=date('dmY', $end);


          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 31')->queryScalar();
          $url.="1"."&sd=".$sd."&ed=".$ed;
                    

//if($this->webSync == false){ echo  $url;}         
$this->debug[]=$url;
          
          $page = $this->get_web_page( $url);               
          
          $content = mb_split('\r\n', $page['content'] );          
$this->debug[]=$content;
          
          $parse = str_getcsv($content[0],",");     
          $rowNum=$parse[0];
                  
          $n=count($content);

          /*Заполним заголовок*/
          $syncDate=date("Y-m-d H:i:s");    
          
          $header = new TblBankOpHeader();
          $header->refUser = $curUserId;
          $header->syncDateTime = $syncDate;
          $header->onDate = date('Y-m-d', $end);
          $header->save();
          
          for ($i=1; $i<$n;$i++ )
          {
            $parse = str_getcsv($content[$i],",");     
            
            if (count($parse) < 12) {continue;} /*неполная запись*/
               
               /*Сумма*/               
            $summ=(float)str_replace(',', '.',$parse[10]);            
               /*дата регистратора*/
            $parse[5] = preg_replace("/[^(0-9.)]/","",$parse[5]);
            $regDate= date("Y-m-d", strtotime($parse[5]));
               
               /*дата сделки*/
            $parse[8] = preg_replace("/[^(0-9.)]/","",$parse[8]);
            $sdDate= date("Y-m-d", strtotime($parse[8]));
            if(strtotime($sdDate)< 1000) $sdDate = $regDate;

               /*дата операции*/
            $parse[13] = preg_replace("/[^(0-9.)]/","",$parse[13]);
            $opDate= date("Y-m-d", strtotime($parse[13]));
            if(strtotime($opDate)< 1000) $opDate = $regDate;
               
            $record = new TblBankOpContent();
            $record->ownerTitle  = mb_substr($parse[1],0,250,'utf-8');
            $record->orgTitle    = mb_substr($parse[2],0,250,'utf-8');
            $record->orgINN      = mb_substr($parse[3],0,20,'utf-8');
            $record->regNote     = mb_substr($parse[4],0,250,'utf-8');
            $record->regDate     = $regDate;
            $record->regNum      = mb_substr($parse[ 6],0,20,'utf-8');
            $record->operationNote = mb_substr($parse[7],0,250,'utf-8');
            $record->sdelkaDate =  $sdDate;
            $record->operationNum  = mb_substr($parse[9],0,20,'utf-8');
            $record->recordSum     = $summ;
            $record->orgKPP        = mb_substr($parse[11],0,20,'utf-8');
            $record->article       = mb_substr($parse[12],0,250,'utf-8');
            $record->refBankOpHeader = $header->id;
            $record->operationDate = $opDate;
            $record->ppNumber      = mb_substr($parse[14],0,20,'utf-8');
            $record->save();
            
//          
            
         }

// логируем         

  Yii::$app->db->createCommand("INSERT INTO {{%log}} (refUser,actionType,actionText) VALUES (:refUser, 11, 'Синхронизация операций по счету с 1С') ", 
  [':refUser' => $curUserId]) ->execute();       


/*Подчистим*/
    $strSql = "DELETE from {{%bank_operation}}
              where operationDate >= :sd and operationDate <= :ed ";
     $list = Yii::$app->db->createCommand($strSql,
     [
        ':sd' => date("Y-m-d",$start), 
        ':ed' => date("Y-m-d",$end-24*3600), 
     ] )->execute();                    
     
     
//выбираем уникальные     
  $strSql = "INSERT INTO {{%bank_operation}} ( ownerTitle,  orgTitle, orgINN, regNote, regDate, regNum, operationNote, operationDate, 
  operationNum, recordSum, orgKPP, article, sdelkaDate, ppNumber) ( SELECT DISTINCT a.ownerTitle,  a.orgTitle, a.orgINN, a.regNote, a.regDate, a.regNum, 
  a.operationNote, a.operationDate, a.operationNum, a.recordSum, a.orgKPP, a.article, a.sdelkaDate, a.ppNumber
  from {{%bank_op_content}} as a left join {{%bank_operation}} as b 
  on (a.orgINN=b.orgINN and a.regNum=b.regNum and a.regDate=b.regDate and  a.recordSum= b.recordSum) 
  where b.id is null and a.refBankOpHeader=:refBankOpHeader)";     
  Yii::$app->db->createCommand($strSql, [':refBankOpHeader' => $header->id])->execute();     
         
//связываем  
  $strSql = "UPDATE {{%bank_op_content}} as a1
  left join {{%bank_operation}} as b1 
  on (a1.orgINN=b1.orgINN and a1.regNum=b1.regNum and a1.regDate=b1.regDate and  a1.recordSum= b1.recordSum)  
  SET a1.refOperation = b1.id
  where b1.id is not null and a1.refOperation = 0 ";
  Yii::$app->db->createCommand($strSql)->execute();                       
   
  $strSql = "INSERT INTO {{%bank_op_article}} (article) ( SELECT DISTINCT a.article from 
        {{%bank_operation}} as a left join  {{%bank_op_article}} as b on b.article = a.article
            where b.id is null)";
  Yii::$app->db->createCommand($strSql)->execute();                         
  
  // Вяжем с выпиской
  $strSql = "update {{%bank_operation}} as a, {{%bank_extract}} as b
    set a.`refBankExtract` = b.id where a.`orgINN` = b.`debetINN`
    and a.ppNumber = b.docNum and a.ppNumber is not null
    and a.`recordSum` =  b.`creditSum` and a.`regDate` = DATE(b.`recordDate`)
    and a.`refBankExtract` = 0";
  Yii::$app->db->createCommand($strSql)->execute();                         

  $strSql = "update {{%bank_operation}} as a, {{%bank_extract}} as b
    set a.`refBankExtract` = b.id where a.`orgINN` = b.`creditINN`
    and a.ppNumber = b.docNum and a.ppNumber is not null
    and a.`recordSum` =  -b.`debetSum` and a.`regDate` = DATE(b.`recordDate`)
    and a.`refBankExtract` = 0";
  Yii::$app->db->createCommand($strSql)->execute();                         
    
// Статьи    
  $strSql = "UPDATE {{%bank_operation}} as a left join  {{%bank_op_article}} as b
            on b.article = a.article set a.articleRef = b.id where a.articleRef = 0
            and b.id is not null";
  Yii::$app->db->createCommand($strSql)->execute();                         
            
// Собственники            
  $strSql = "UPDATE {{%bank_operation}}, {{%control_sverka_filter}} SET 
      {{%bank_operation}}.ownerOrgRef={{%control_sverka_filter}}.id where 
      {{%bank_operation}}.ownerTitle = {{%control_sverka_filter}}.owerOrgTitle
      and {{%bank_operation}}.ownerOrgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     
   
    return    $header->id;     
   }     
     
     
/****************/

   public function getBankOperationProvider($params)
   {
                     
     $query  = new Query();
        $query->select ([
        'id',
        'ownerTitle',
        'orgTitle',
        'orgINN',
        'regNote',
        'regDate',
        'regNum',
        'operationNote',
        'operationDate',
        'operationNum',
        'recordSum',
        'orgKPP',
        'article',
        'refBankOpHeader',
        ])
        ->from("{{%bank_op_content}}")
        ->distinct() 
         ;
     $countquery  = new Query();
     $countquery->select (" count({{%bank_op_content}}.id)")
                     ->from("{{%bank_op_content}}")        ;
      
      $query->andFilterWhere(['=', 'refBankOpHeader', $this->id]);
      $countquery->andFilterWhere(['=', 'refBankOpHeader', $this->id]);
             
     if (($this->load($params) && $this->validate())) {
     /*
      $query->andFilterWhere(['like', 'title', $this->title]);
      $countquery->andFilterWhere(['like', 'city', $this->city]);
      */
     }

     $command = $query->createCommand();     
     $count = $countquery->createCommand()->queryScalar();

     
     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 8,
               ],
               
               'sort' => [
               
               'attributes' => [
                'ownerTitle',
                'orgTitle',
                'orgINN',
                'regDate',
                'regNum',
                'operationDate',
                'operationNum',
                'recordSum',
                'orgKPP',
                ],
               
               ],
               
          ]);


          
     return  $dataProvider;      
   }   

/***********************/

public function setPeriod($fromDate, $toDate)
{
 if (!empty($fromDate))    $this->from = strtotime($fromDate);
 if (!empty($toDate))    $this->to   = strtotime($toDate );
 
 if (!empty($this->refExtract))
    {        
      $record = TblBankExtract::findOne($this->refExtract);
      if (!empty($record )){
           $extractDT= strtotime($record->recordDate);
        if (empty($this->from))$this->from = $extractDT-24*3600;
        if (empty($this->to))  $this->to   = $extractDT+24*3600;  
      }    
    }
    
}  

   public function getBankOperationSelectProvider($params)
   {
                     
     $query  = new Query();
        $query->select ([
        'id',
        'orgTitle',
        'orgINN',
        'regNote',
        'regDate',
        'regNum',
        'operationNote',
        'operationDate',
        'operationNum',
        'recordSum',
        'orgKPP',
        'article',
        'refBankExtract',
        'ppNumber',
        'sdelkaDate'
        ])
        ->from("{{%bank_operation}}")
        ->distinct() 
         ;
         
     $countquery  = new Query();
     $countquery->select (" count({{%bank_operation}}.id)")
                     ->from("{{%bank_operation}}")        ;
      
             
     if (($this->load($params) && $this->validate())) {
     
      $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
      $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
      
     }

     if (empty($this->orgTitle) && !empty($this->refOrg) ){
       $strSql = "SELECT title from {{%orglist}} where id=:refOrg"; 
       $this->orgTitle =  Yii::$app->db->createCommand($strSql,['refOrg' => $this->refOrg])->queryScalar();                    
       $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
       $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);      
     }
      
    
    if (!empty($this->from))
    {        
      $query->andFilterWhere(['>=', 'regDate', date("Y-m-d",$this->from)]);
      $countquery->andFilterWhere(['>=', 'regDate', date("Y-m-d",$this->from)]);
    }
    
    if (!empty($this->to))
    {        
      $query->andFilterWhere(['<=', 'regDate', date("Y-m-d",$this->to)]);
      $countquery->andFilterWhere(['<=', 'regDate', date("Y-m-d",$this->to)]);
    }
    
   



     $command = $query->createCommand();     
     $count = $countquery->createCommand()->queryScalar();

     
     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 8,
               ],
               
               'sort' => [
               
               'attributes' => [
                'ownerTitle',
                'orgTitle',
                'orgINN',
                'regDate',
                'regNum',
                'operationDate',
                'operationNum',
                'recordSum',
                'orgKPP',
                'ppNumber',
                'sdelkaDate'
                ],               
               ],               
          ]);
          
     return  $dataProvider;      
   }   


/*
    для всех операций по банку
*/
   public function getBankOpCheckProvider($params)
   {
                     
     $query  = new Query();
        $query->select ([
        'id',
        'ownerTitle',
        'orgTitle',
        'orgINN',
        'regNote',
        'regDate',
        'regNum',
        'operationNote',
        'operationDate',
        'operationNum',
        'recordSum',
        'orgKPP',
        'refBankExtract',
        'articleRef',
        'ownerOrgRef',
        'sdelkaDate',
        ])
        ->from("{{%bank_operation}}")
        ->distinct() 
         ;
     $countquery  = new Query();
     $countquery->select (" count({{%bank_operation}}.id)")
                     ->from("{{%bank_operation}}")        ;
      
     if (($this->load($params) && $this->validate())) {
     /*
      $query->andFilterWhere(['like', 'title', $this->title]);
      $countquery->andFilterWhere(['like', 'city', $this->city]);
      */
     }

     $command = $query->createCommand();     
     $count = $countquery->createCommand()->queryScalar();

     
     $dataProvider = new SqlDataProvider([
               'sql' => $command ->sql,
               'params' => $command->params,               
               'totalCount' => $count,
               'pagination' => [
               'pageSize' => 8,
               ],
               
               'sort' => [
               
               'attributes' => [
                'ownerTitle',
                'orgTitle',
                'orgINN',
                'regDate',
                'regNum',
                'operationDate',
                'operationNum',
                'recordSum',
                'orgKPP',
                ],
               
               ],
               
          ]);


          
     return  $dataProvider;      
   }   

   
   
    
  /************End of model*******************/ 
 }
