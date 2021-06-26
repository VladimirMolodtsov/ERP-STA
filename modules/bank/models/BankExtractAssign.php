<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankExtract;
use app\modules\bank\models\TblDocExtractLnk;
use app\modules\bank\models\BankExtractAssign;
/**
 * BankExtract - Детализация выписки
 */
 
 class BankExtractAssign extends Model
{
    
    public $debug;
    

    
    public function rules()
    {
        return [            
        ];
    }

/*****************************/    
 public function scanExtract()
 {
    //простые случаи
    $this->assignDeals ();
    
 }
 function assignDeals ()
 {
   //Составим список не распределенных
    $strSql= "SELECT debetOrgTitle, creditOrgTitle, id,  orgRef, debetSum, creditSum  from {{%bank_extract}} where orgRef > 0 and orgDeal = 0 ";        
    $list = Yii::$app->db->createCommand($strSql)->queryAll();
 

    $strSql= "SELECT articleRef, state from {{%org_deals}}, {{%bank_op_article}}
         where {{%org_deals}}.articleRef = {{%bank_op_article}}.id
         and refOrg=:refOrg and state > 0 and (signValue = :signValue OR signValue = 0)";       
  
    for ($i=0; $i<count($list); $i++)
    {
       $orgDeal =0;
       
       if($list[$i]['debetSum'] > 0 ) $signValue = -1;
       else $signValue = +1;
    
        $dealList = Yii::$app->db->createCommand($strSql, [
        ':refOrg' => $list[$i]['orgRef'],
        ':signValue' => $signValue,        
        ])->queryAll();
  
  /*if ($list[$i]['orgRef'] ==4964){
  echo Yii::$app->db->createCommand($strSql, [
        ':refOrg' => $list[$i]['orgRef'],
        ':signValue' => $signValue,        
        ])->getRawSql();
        
  print_r ($dealList);
  
  }*/
  
        if(count($dealList) == 0) continue; //не нашли        
        if(count($dealList) == 1) $orgDeal = $dealList[0]['articleRef'];
        else
        {
          for ($j=0;$j< count($dealList); $j++)
          {  
            if($dealList[$j]['state'] == 2) $orgDeal = $dealList[$j]['articleRef'];
          }
        }       
     
       if ($orgDeal == 0 ) continue;
       
       $record = TblBankExtract::findOne($list[$i]['id']);
       if (empty ($record)) continue;
       $record->orgDeal = $orgDeal;
       $record->save();       
       
    }
 
 } 
  /************End of model*******************/ 
 }
