<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankOpHeader;
use app\modules\bank\models\TblBankOpContent;


/**
 * BankOperation - модель работы с  банковскими операциями из 1С
 */
 
 class BankOperation extends Model
{
    
    public $debug;
    
    // фильтр
    public $userFIO;
    public $id=0;
    
    public function rules()
    {
        return [            
            
            //[[ ], 'default'],                        
            [['userFIO'], 'safe'],            
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
     public function syncOperations($sd, $ed)
     {
          mb_internal_encoding("UTF-8");
          //$res=array();

/*
Получим данные из 1с
http://a0202654.xsph.ru/rik/web/index.php?r=/bank/operator/sync-bank-operation&sd=16062019&ed=18062019
*/          
          $url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 31')->queryScalar();
          $url.="1"."&sd=".$sd."&ed=".$ed;
                    
          
          $page = $this->get_web_page( $url);               
          
          $content = mb_split('\r\n', $page['content'] );          

          
          $parse = str_getcsv($content[0],",");     
          $rowNum=$parse[0];
                  
          $n=count($content);

          /*Заполним заголовок*/
          $syncDate=date("Y-m-d H:i:s");                            
          $curUser=Yii::$app->user->identity; 
          
          $header = new TblBankOpHeader();
          $header->refUser = $curUser->id;
          $header->syncDateTime = $syncDate;
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
            $opDate= date("Y-m-d", strtotime($parse[8]));
               
            $record = new TblBankOpContent();
            $record->ownerTitle  = mb_substr($parse[1],0,250,'utf-8');
            $record->orgTitle    = mb_substr($parse[2],0,250,'utf-8');
            $record->orgINN      = mb_substr($parse[3],0,20,'utf-8');
            $record->regNote     = mb_substr($parse[4],0,250,'utf-8');
            $record->regDate     = $regDate;
            $record->regNum      = mb_substr($parse[6],0,20,'utf-8');
            $record->operationNote = mb_substr($parse[7],0,250,'utf-8');
            $record->operationDate = $opDate;
            $record->operationNum  = mb_substr($parse[9],0,20,'utf-8');
            $record->recordSum     = $summ;
            $record->orgKPP        = mb_substr($parse[11],0,20,'utf-8');
            $record->article       = mb_substr($parse[12],0,250,'utf-8');
            $record->refBankOpHeader = $header->id;
            $record->save();
         }

// логируем         
  $curUser=Yii::$app->user->identity; 
  Yii::$app->db->createCommand("INSERT INTO {{%log}} (refUser,actionType,actionText) VALUES (:refUser, 11, 'Синхронизация операций по счету с 1С') ", 
  [':refUser' => $curUser->id]) ->execute();       
     
//выбираем уникальные     
  $strSql = "INSERT INTO {{%bank_operation}} ( ownerTitle,  orgTitle, orgINN, regNote, regDate, regNum, operationNote, operationDate, 
  operationNum, recordSum, orgKPP, article) ( SELECT DISTINCT a.ownerTitle,  a.orgTitle, a.orgINN, a.regNote, a.regDate, a.regNum, 
  a.operationNote, a.operationDate, a.operationNum, a.recordSum, a.orgKPP, a.article
  from {{%bank_op_content}} as a left join {{%bank_operation}} as b 
  on (a.orgINN=b.orgINN and a.regNum=b.regNum and a.regDate=b.regDate and  a.recordSum= b.recordSum)  where b.id is null )";     
  Yii::$app->db->createCommand($strSql)->execute();     
         
//связываем  
  $strSql = "UPDATE rik_bank_op_content as a1
  left join rik_bank_operation as b1 
  on (a1.orgINN=b1.orgINN and a1.regNum=b1.regNum and a1.regDate=b1.regDate and  a1.recordSum= b1.recordSum)  
  SET a1.refOperation = b1.id
  where b1.id is not null and a1.refOperation = 0 ";
                  
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



    
  /************End of model*******************/ 
 }
