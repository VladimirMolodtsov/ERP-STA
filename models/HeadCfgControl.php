<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use yii\helpers\Html;

use app\models\TblControlRemainsUse;
use app\models\TblControlSverkaBlack;
use app\models\TblControlSverkaUse;
use app\models\TblControlBankUse;
 

/**
 * HeadCfgControl  - настройка контрольных форм
 */
 
class HeadCfgControl extends Model
{

 /*фильтры*/
  public $usedOrgTitle;
  public $scladTitle;
  public $orgTitle;
  public $bankAccount;
    
   public function rules()
   {

        return [
            [['usedOrgTitle',  'scladTitle',  'orgTitle', 'bankAccount' ], 'safe'],
        ];
    }
 
 /***************************************************************/
 /***************** Контроль остатков ***************************/
 /***************************************************************/
 
 /*Переключим использование real*/    
 public function switchControlRemainReal($id)
 {                  
      $record= TblControlRemainsUse::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->scladIsUsedReal == 1) $record->scladIsUsedReal = 0;
                                    else $record->scladIsUsedReal = 1;
      $record->save();
    return true;
 }  

 /*Переключим использование all*/   
 public function switchControlRemainAll($id)
 {                  
      $record= TblControlRemainsUse::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->scladIsUsedAll == 1) $record->scladIsUsedAll = 0;
                                   else $record->scladIsUsedAll = 1;
      $record->save();
    return true;
 }  
 
 
 
 /*****************************/
 public function getControlRemainsCfgProvider($params)
   {
    
    /*Обновим список*/
    $strSql ="insert into {{%control_remains_use}} (usedOrgTitle, scladTitle)
   (SELECT a.usedOrgTitle, a.scladTitle  FROM
   (SELECT DISTINCT usedOrgTitle, scladTitle FROM {{%control_remains}}) as a
    LEFT JOIN {{%control_remains_use}} as b 
    on (a.usedOrgTitle =b.usedOrgTitle and a.scladTitle =b.scladTitle )
    where `b`.id is null )";
    
    Yii::$app->db->createCommand($strSql)->execute();
    
    
    $query  = new Query();
    $query->select ([            
            'id',
            'usedOrgTitle', 
            'scladTitle', 
            'scladIsUsedAll', 
            'scladIsUsedReal' 
            ])->from("{{%control_remains_use}}")            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%control_remains_use}}.id)")
            ->from("{{%control_remains_use}}")
            ;
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);
        $countquery->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);

        $query->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);
        $countquery->andFilterWhere(['like', 'scladTitle', $this->scladTitle]);     
                 
     }
                
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
            'usedOrgTitle', 
            'scladTitle', 
            'scladIsUsedAll', 
            'scladIsUsedReal', 
            ],
            
            'defaultOrder' => [ 'usedOrgTitle' => SORT_ASC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 

 /***************************************************************/
 /***************** Контроль сверки ***************************/
 /***************************************************************/
 
 /*Переключим использование */    
 public function switchControlSverkaUse($id)
 {                  
      $record= TblControlSverkaUse::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->inUse == 1) $record->inUse = 0;
                          else $record->inUse= 1;
      $record->save();
    return true;
 }  
 /*****************************/

 /*Переключим black list */    
 public function switchControlSverkaBlack ($id)
 {                  
      $record= TblControlSverkaBlack::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->isBlack == 1) $record->isBlack= 0;
                            else $record->isBlack= 1;
      $record->save();
    return true;
 }  
 /*****************************/
  public function getControlSverkaUseCfgProvider($params)
   {
    
    /*Обновим список*/
    $strSql ="insert into {{%control_sverka_dolga_use}} (usedOrgTitle)
   (SELECT a.usedOrgTitle  FROM
   (SELECT DISTINCT usedOrgTitle FROM {{%control_sverka_dolga}}) as a
    LEFT JOIN {{%control_sverka_dolga_use}} as b 
    on (a.usedOrgTitle =b.usedOrgTitle )
    where `b`.id is null )";
    
    Yii::$app->db->createCommand($strSql)->execute();
    
    
    $query  = new Query();
    $query->select ([            
            'id',
            'usedOrgTitle', 
            'inUse'
            ])->from("{{%control_sverka_dolga_use}}")            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%control_sverka_dolga_use}}.id)")
            ->from("{{%control_sverka_dolga_use}}")
            ;
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);
        $countquery->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);
     }
                
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();

    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'usedOrgTitle', 
            'inUse'
            ],
            
            'defaultOrder' => [ 'inUse' => SORT_DESC, 'usedOrgTitle' => SORT_ASC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
 
 /*****************************/
 public function getControlSverkaBlackCfgProvider($params)
   {
    
    /*Обновим список*/
    $strSql ="insert into {{%control_sverka_dolga_black}} (orgTitle)
   (SELECT a.orgTitle  FROM
   (SELECT DISTINCT orgTitle FROM {{%control_sverka_dolga}}) as a
    LEFT JOIN {{%control_sverka_dolga_black}} as b 
    on (a.orgTitle =b.orgTitle )   where `b`.id is null )";
    
    Yii::$app->db->createCommand($strSql)->execute();
    
//выключим все, что не в базе
    $strSql ="UPDATE {{%control_sverka_dolga_black}} as b
        LEFT JOIN {{%control_sverka_dolga}} as a 
        on (a.orgTitle =b.orgTitle )
        LEFT JOIN {{%control_sverka_dolga_use}} as c 
        on (a.usedOrgTitle =c.usedOrgTitle )    
        SET b.isBlack = 1
    where ifnull(c.inUse,0) = 0 ";
    
    Yii::$app->db->createCommand($strSql)->execute();

        
    $query  = new Query();
    $query->select ([            
            '{{%control_sverka_dolga_black}}.id',
            '{{%control_sverka_dolga_black}}.orgTitle', 
            'isBlack',
            'sum({{%control_sverka_dolga}}.balanceSum) as S'
            ])->from("{{%control_sverka_dolga_black}}")            
            ->leftJoin("{{%control_sverka_dolga}}", "{{%control_sverka_dolga_black}}.orgTitle={{%control_sverka_dolga}}.orgTitle")
            ->leftJoin("{{%control_sverka_dolga_use}}", "{{%control_sverka_dolga_use}}.usedOrgTitle={{%control_sverka_dolga}}.usedOrgTitle")                        
            ->groupBy(['orgTitle', 'id', 'isBlack' ])
            ->distinct()
            ;
            
    $countquery  = new Query();
    $countquery->select (" count(DISTINCT({{%control_sverka_dolga_black}}.id))")
            ->from("{{%control_sverka_dolga_black}}")
            ->leftJoin("{{%control_sverka_dolga}}", "{{%control_sverka_dolga_black}}.orgTitle={{%control_sverka_dolga}}.orgTitle")
            ->leftJoin("{{%control_sverka_dolga_use}}", "{{%control_sverka_dolga_use}}.usedOrgTitle={{%control_sverka_dolga}}.usedOrgTitle")                        
            ;

        $query->andWhere(['=', 'inUse', 1]);
        $countquery->andWhere(['=', 'inUse', 1]);
            
                        
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
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
            'orgTitle', 
            'isBlack', 
            'S'
            ],
            
            'defaultOrder' => [ 'orgTitle' => SORT_ASC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
 
 /***************************************************************/
 /***************** Контроль состояния банковских счетов ********/
 /***************************************************************/
 
 /*Переключим использование real*/    
 public function switchControlBankReal($id)
 {                  
      $record= TblControlBankUse::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->inUseReal == 1) $record->inUseReal = 0;
                              else $record->inUseReal = 1;
      $record->save();
    return true;
 }  

 /*Переключим использование all*/   
 public function switchControlBankAll($id)
 {                  
      $record= TblControlBankUse::findOne($id);            
      if (empty($record)) return false;
      /*Не люблю я не читаемые конструкции типа ? : */
      if ($record->inUseAll == 1) $record->inUseAll = 0;
                             else $record->inUseAll = 1;
      $record->save();
    return true;
 }  
 
 /*****************************/
 public function getControlBankCfgProvider($params)
   {
    
    /*Обновим список*/
    $strSql ="insert into {{%control_bank_use}} (usedOrgTitle, bankAccount, accountNumber)
   (SELECT a.usedOrgTitle, a.bankAccount, a.accountNumber  FROM
   (SELECT DISTINCT usedOrgTitle, bankAccount, accountNumber FROM {{%control_bank}}) as a
    LEFT JOIN {{%control_bank_use}} as b 
    on (a.usedOrgTitle =b.usedOrgTitle and a.bankAccount =b.bankAccount  and a.accountNumber =b.accountNumber)
    where `b`.id is null )";
    
    Yii::$app->db->createCommand($strSql)->execute();
    
    
    $query  = new Query();
    $query->select ([            
            'id',
            'usedOrgTitle', 
            'bankAccount', 
            'accountNumber', 
            'inUseAll', 
            'inUseReal' 
            ])->from("{{%control_bank_use}}")            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%control_bank_use}}.id)")
            ->from("{{%control_bank_use}}")
            ;
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);
        $countquery->andFilterWhere(['like', 'usedOrgTitle', $this->usedOrgTitle]);

        $query->andFilterWhere(['like', 'bankAccount', $this->bankAccount]);
        $countquery->andFilterWhere(['like', 'bankAccount', $this->bankAccount]);     
                 
     }
                
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
            'usedOrgTitle', 
            'bankAccount', 
            'accountNumber', 
            'inUseAll', 
            'inUseReal' 
            ],
            
            'defaultOrder' => [ 'usedOrgTitle' => SORT_ASC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
  
  /*****************************/
  /*****************************/

  
  
   /** end of object **/     
 }
