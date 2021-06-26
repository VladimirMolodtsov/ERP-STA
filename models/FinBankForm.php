<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 
use app\models\TblControlBankUse;
use app\models\TblControlBank;

/**
 * FinBankForm  - модель контроль банковских счетов
 */


class FinBankForm extends Model
{

    public $id=0;
    
    public $usedOrgTitle;
    
    public $strDate;
    
    public $sumValue=0;        
    public $syncDateTime=0;        
    public $inUseReal; 
    
    public function rules()
    {
        return [
            [['usedOrgTitle','inUseReal'  ], 'safe'],
        ];
    }
  /***************************/ 
  
   public function switchBankUse($id)
   {
       //$curUser=Yii::$app->user->identity;
      
       $record = TblControlBankUse::findOne($id);      
       if (empty($record)) return false;
       
       if ($record->inUseReal == 1)$record->inUseReal = 0;
       else                      $record->inUseReal = 1;
                     
       $record->save();

/*       Yii::$app->db->createCommand(
            'UPDATE {{%ware_content}},{{%ware_use}} SET {{%ware_content}}.isActive = {{%ware_use}}.isInUse 
             where  {{%ware_content}}.useRef = {{%ware_use}}.id  AND headerRef =:headerRef;', 
            [ ':headerRef' => $headerRef , ])->execute();    */
       
       return true;
   }
   
  /***************************/ 
  public function getBankControlProvider($params)
   {
    
    if (empty($this->strDate))$this->strDate=date('Y-m-d');
    
    $headerRef =  Yii::$app->db->createCommand(
            'SELECT MAX(id) FROM {{%control_bank_header}} WHERE DATE(onDate) =:opDate', 
            [ ':opDate' => $this->strDate, ])->queryScalar();        
    if (empty($headerRef))$headerRef=0; //от пустой строки
   
    $this->syncDateTime=  Yii::$app->db->createCommand(
            'SELECT syncDate FROM {{%control_bank_header}} WHERE id =:headerRef', 
            [ ':headerRef' => $headerRef, ])->queryScalar();        
       
    $query  = new Query();
    $query->select ([ 'a.id as useRef',  
                      'a.usedOrgTitle',
                      'a.bankAccount',
                      'a.accountNumber',
                      'a.inUseReal',             
                      'a.cashType',         
                      'ifnull(b.cashSum,0) as cashSum',                      
                      'b.cashEd',
                      'b.id as id'
                      ])
            ->from("{{%control_bank_use}} as a")
            ->leftJoin("(SELECT id, cashSum, cashEd, useRef FROM {{%control_bank}} WHERE headerRef = ".$headerRef.") as b", "a.id=b.useRef")            
            ->distinct();
            ;
        
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT(a.id))")
            ->from("{{%control_bank_use}} as a")
            ->leftJoin("(SELECT id, cashSum, cashEd, useRef FROM {{%control_bank}} WHERE headerRef = ".$headerRef.") as b", "a.id=b.useRef")            
            ;
    $sumquery  = new Query();        
    $sumquery->select (" sum(cashSum) ")
            ->from("{{%control_bank}} as b")
            ->leftJoin("{{%control_bank_use}} as a","a.id=b.useRef")
            ->andWhere("headerRef = ".$headerRef)
            ;
            
    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'a.usedOrgTitle', $this->usedOrgTitle]);
     $countquery->andFilterWhere(['like', 'a.usedOrgTitle', $this->usedOrgTitle]);
     $sumquery->andFilterWhere(['like', 'a.usedOrgTitle', $this->usedOrgTitle]);
     
     }
          
     if (empty ($this->inUseReal)) $this->inUseReal = 2;
     if (!empty ($this->inUseReal))
     {
       if($this->inUseReal == 2)
       {
         $query->andFilterWhere(['=', 'inUseReal', 1]);
         $countquery->andFilterWhere(['=', 'inUseReal', 1]);
         $sumquery->andFilterWhere(['=', 'inUseReal', 1]);
       }  

       if($this->inUseReal == 3)
       {
         $query->andFilterWhere(['=', 'inUseReal', 0]);
         $countquery->andFilterWhere(['=', 'inUseReal', 0]);
         $sumquery->andFilterWhere(['=', 'inUseReal', 0]);
       }  
     }
     
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    $this->sumValue =  $sumquery->createCommand()->queryScalar();                                     
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                'usedOrgTitle',
                'cashType',
                'bankAccount',
                'accountNumber',
                'cashEd',
                'cashSum',
                'inUseReal',
            ],            
            'defaultOrder' => [ 'usedOrgTitle' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  
/**/    
 }
 
