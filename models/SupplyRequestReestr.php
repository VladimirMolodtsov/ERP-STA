<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 

use app\models\TblRequestSupply;
use app\models\TblSupplyStatus;

use app\models\WarehouseForm;
/**
 * SupplyRequestReestr- реестр отгрузок
 */


class SupplyRequestReestr extends Model
{

    public $debug=[];      
    
    public $timeshift = 4*3600;   
 
    public $requestId = 0;
    public $noteType = 0;
    public $requestNote = "";

    
    public $switchRequestId =0;
    public $switchType =0;

    public $dataVal;
    public $dataType;
     
     /***/
    public $title;
    public $userFIO;
    public $isAccepted;
    public $marketNeedAcpt;
    public $supplyIsAccept;  
    
    public $isFinished;
    public $isHaveOriginal;
    public $productIsAccept;
    public $discussIsFinish;     
    
    public $readyPlan;
    public $supplyDate;
    
    public $isActive = 0;
    public $mode = 0;    
          
    public function rules()
    {
        return [
              [['requestId','noteType','requestNote','switchRequestId','switchType', 'dataVal', 'dataType'], 'default'],
              [['title', 'userFIO','isAccepted', 'marketNeedAcpt', 'supplyIsAccept', 'isFinished', 'isHaveOriginal',
              'productIsAccept', 'discussIsFinish', 'readyPlan', 'supplyDate', 'isActive'  ], 'safe'],
              
              [['requestId','noteType','switchRequestId','switchType', ], 'integer'],
              [['requestNote', 'dataVal', 'dataType'  ], 'trim'],
        ];
    }
  
  
public function saveSupplyRequestNote ()
{
  
  $record = TblRequestSupply::findOne($this->requestId);      
       if (empty($record)) return [ 'res' => false ];
     
  $curUser=Yii::$app->user->identity;
  $marker= "[".date("d.m H:i", time()+$this->timeshift)." ".$curUser->userFIO."] "; 
  
  switch($this->noteType)
  {
      case 1:
         $record->marketNote = $record->marketNote.$marker.$this->requestNote."\n";
         $resNote= $record->marketNote;
         break; 
  
      case 2:
         $record->discusNote = $record->discusNote.$marker.$this->requestNote."\n";
         if (!empty($record->discusNote)) $record->discussIsFinish  = 0;
         $resNote= $record->discusNote;
         break; 
    
  }
  $record->save();
  
  return [ 'res' => true,            
           'noteType' => $this->noteType,           
           'requestNote' => $resNote,
           'id'  => $this->requestId,
  ];
}


/*цифры в листок*/
public function  getLeafValue ()
{
 $m = new WarehouseForm();
 return $m->getLeafValue();
}

public function saveSupplyDataVal ()
{
  
  $record = TblRequestSupply::findOne($this->requestId);      
       if (empty($record)) return [ 'res' => false ];
  
  
  switch($this->dataType)
  {
      case 'readyPlan':         
         $record->readyPlan = date('Y-m-d',strtotime($this->dataVal));
         break; 
  
      case 'readyFact':
         $record->readyFact = date('Y-m-d',strtotime($this->dataVal));
         $statusRecord = TblSupplyStatus::findOne(['refSupply' => $this->requestId]);      
         if (empty($statusRecord)) {$statusRecord = new TblSupplyStatus(); }
         if (empty($statusRecord)) return [ 'res' => false ]; 
         $statusRecord->st11= date('Y-m-d',strtotime($this->dataVal));
         $statusRecord->refSupply = $record->id;
         $statusRecord->save();         
         break;       
         
      case 'productStart':
         $record->productStart = date('Y-m-d',strtotime($this->dataVal));
         break;          
      case 'docDate':
         $record->docDate= date('Y-m-d',strtotime($this->dataVal));
         break; 
      case 'supplyDate':
         $record->supplyDate= date('Y-m-d',strtotime($this->dataVal));
         break; 
      case 'finishDate':
         $statusRecord = TblSupplyStatus::findOne(['refSupply' => $this->requestId]);      
         if (empty($statusRecord)) {$statusRecord = new TblSupplyStatus(); }
         if (empty($statusRecord)) return [ 'res' => false ]; 
         $statusRecord->st17= date('Y-m-d',strtotime($this->dataVal));
         $statusRecord->refSupply = $record->id;
         $statusRecord->save();
         break;

       default:
        return [ 'res' => false, 'inputVal' => $this->dataVal, ];    
  }
  $record->save();
  
  return [ 'res' => true, 'inputVal' => $this->dataVal,  ];
}


public function switchInRequest ()
{
  
  $record = TblRequestSupply::findOne($this->requestId);      
       if (empty($record)) return [ 'res' => false ];
  $val=0;
  switch($this->switchType)
  {
      case 1:
         if($record->marketNeedAcpt == 0) $record->marketNeedAcpt = 1;
                                     else $record->marketNeedAcpt = 0;
         $val = $record->marketNeedAcpt;                           
         break; 
  
      case 2:
         if($record->marketIsAccept== 0)  $record->marketIsAccept = 1;
                                     else $record->marketIsAccept = 0;
         $val = $record->marketIsAccept;                            
         break; 
  
      case 3:
         if($record->isAccepted== 0)  $record->isAccepted = 1;
                                 else $record->isAccepted = 0;
         $val = $record->isAccepted;                                                    
         break; 
         
      case 4:
         if($record->supplyIsAccept== 0) {             
              $record->supplyIsAccept = 1;
              $value = date('Y-m-d');
              Yii::$app->db->createCommand("UPDATE {{%supply_status}} SET st1 =:val where refSupply=:id and st1 = '0000-00-00' ",
              [ ':val' => $value,  ':id'  =>$this->requestId])->execute();              
          }
          else {
              /*снимем дату*/
              $record->supplyIsAccept = 0;
              Yii::$app->db->createCommand("UPDATE {{%supply_status}} SET st1=:val where refSupply=:id",
               [ ':val' =>'0000-00-00', ':id'  =>$this->requestId]  )->execute();
          }
          
         $val = $record->supplyIsAccept;                             
         break; 
         
      case 5:
         if($record->productIsAccept== 0)  $record->productIsAccept= 1;
                                     else  $record->productIsAccept= 0;                                     
         $val = $record->productIsAccept;                             
         break; 

      case 6:
         if($record->discussIsFinish== 0)  $record->discussIsFinish= 1;
                                     else  $record->discussIsFinish= 0;
         $val = $record->discussIsFinish;                             
         break; 

         
      case 7:
         if($record->isHaveOriginal== 0)  $record->isHaveOriginal= 1;
                                    else  $record->isHaveOriginal= 0;
         $val = $record->isHaveOriginal;                            
         break; 

         
      case 8:
         if($record->isFinished== 0)  $record->isFinished= 1;
                                else  $record->isFinished= 0;
         $val = $record->isFinished;                        
         break; 
                             
  
      case 10:
         $record->isAccepted = 1;         
         $val = $record->isAccepted;                                                    
         break; 
         
      case 11:
         $record->isAccepted = -1;         
         $val = $record->isAccepted;                                                    
         break; 

  
  }
  $record->save();
  
  return [ 'res' => true ,
           'switchType' => $this->switchType,           
           'val' => $val,
           'id'  => $this->requestId,
  ];
}
 
    
/*****************************/
 public function getSupplyRequestReestrProvider($params)
   {
   
   /*Для совместимости*/
    $strSql ="update  {{%request_supply}}, {{%supply_status}} set supplyIsAccept = 1
            where  supplyIsAccept = 0 AND {{%request_supply}}.id = {{%supply_status}}.refSupply and {{%supply_status}}.st1 > '1970-01-01'";
    Yii::$app->db->createCommand($strSql)->execute();

    
    $strSql ="update  {{%request_supply}}, {{%supply_status}} set readyFact = {{%supply_status}}.st11
            where  readyFact is null AND {{%request_supply}}.id = {{%supply_status}}.refSupply and {{%supply_status}}.st11 > '1970-01-01'";
    Yii::$app->db->createCommand($strSql)->execute();

    $this->debug[] = Yii::$app->db->createCommand($strSql)->getRawSql();
                
    $query  = new Query();
    $query->select ([
            '{{%request_supply}}.id as requestId', 
            'requestDate', 
            'refSchet', 
            'supplyDate', 

            '{{%request_supply}}.isAccepted',
            '{{%request_supply}}.acceptDT',
            '{{%request_supply}}.marketNote',
            '{{%request_supply}}.marketNeedAcpt',
            '{{%request_supply}}.marketIsAccept',
            '{{%request_supply}}.supplyIsAccept',      
            '{{%request_supply}}.supplyIsAccept',      
            
            '{{%request_supply}}.supplyIsAccept',
            '{{%request_supply}}.productIsAccept',
            '{{%request_supply}}.productStart',
            '{{%request_supply}}.readyPlan',
            '{{%request_supply}}.readyFact',
            '{{%request_supply}}.docDate',
            '{{%request_supply}}.discusNote',
            '{{%request_supply}}.isHaveOriginal',
            '{{%request_supply}}.discussIsFinish',
            '{{%request_supply}}.isFinished',
            '{{%request_supply}}.isActive',
            
            
/**/
            'summOplata', 
            'schetSumm', 
            'supplyType', 
            'isSchetActive',
            'requestNote', 
            '{{%request_supply}}.supplyState', 
            'dstNote', 
            'finishDate', 
            'execNum', 
            'supplyNote', 
            'userFIO', 
            'title',
            'viewManagerRef',
            'execView',
            '{{%schet}}.refOrg',
            '{{%schet}}.refZakaz',
            'ifnull({{%supply_status}}.refScenario, 0) as refScenario',
            'st1','st2','st3','st4','st5','st6','st7','st8','st9','st10',
            'st11','st12','st13','st14','st15','st16','st17',
            ])
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;
            
    $countquery  = new Query();
    $countquery->select (" count({{%request_supply}}.id)")
            ->from("{{%request_supply}}")
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%request_supply}}.refSchet')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%schet}}.refManager')
            ->leftJoin('{{%supply_status}}','{{%supply_status}}.refSupply = {{%request_supply}}.id')            
            ;

 /*    $query->andWhere(['=', 'isSchetActive', 1]);
     $countquery->andWhere(['=', 'isSchetActive', 1]);*/

     
       switch ($this->mode)
        {
        /*Новые*/
            case 2:            
           // $query->andFilterWhere(['>', '{{%request_supply}}.id', 1453]);
            $query->andFilterWhere(['=', '{{%request_supply}}.supplyState', 0]);
            $query->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
            $query->andFilterWhere(['=', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
            $query->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);
            
           // $countquery->andFilterWhere(['>', '{{%request_supply}}.id', 1453]);
            $countquery->andFilterWhere(['=', '{{%request_supply}}.supplyState', 0]);
            $countquery->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
            $countquery->andFilterWhere(['=', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
            $countquery->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);            
            break;
            
          case 3:            
          /* ({{%request_supply}}.supplyState & (0x00001|0x00002)   and {{%request_supply}}.supplyState < 4) 
                                           OR  IFNULL({{%supply_status}}.st1,'0000-00-00') <> '0000-00-00') 
          AND  IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00' */

          
          $query->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
          $query->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);
          //$query->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
          $query->andFilterWhere(['=', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
            
          $countquery->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
          $countquery->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);                          
          //$countquery->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
          $countquery->andFilterWhere(['=', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
          break;
            
          case 4:            
          /* ({{%request_supply}}.supplyState & (0x00001|0x00002)   and {{%request_supply}}.supplyState < 4) 
                                           OR  IFNULL({{%supply_status}}.st1,'0000-00-00') <> '0000-00-00') 
          AND  IFNULL({{%supply_status}}.st17,'0000-00-00') = '0000-00-00' */

          
          $query->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
          $query->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);
          $query->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
          $query->andFilterWhere(['=', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
            
          $countquery->andFilterWhere(['=', '{{%request_supply}}.isAccepted', 1]);
          $countquery->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);                          
          $countquery->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st1,'0000-00-00')", '0000-00-00']);
          $countquery->andFilterWhere(['=', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
          break;
            

          case 5:            
          /* OR  IFNULL({{%supply_status}}.st17,'0000-00-00') <> '0000-00-00')          
                 AND  {{%schet}}.isSchetActive = 1 AND DATEDIFF(NOW(), finishDate)<2   */
                
          $query->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);
          $query->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
          $query->andWhere("DATEDIFF(NOW(), finishDate)<2");

            
          $countquery->andFilterWhere(['=', "{{%schet}}.isSchetActive", '1']);                          
          $countquery->andFilterWhere(['<>', "IFNULL({{%supply_status}}.st17,'0000-00-00')", '0000-00-00']);
          $countquery->andWhere("DATEDIFF(NOW(), finishDate)<2");
          break;
                        

            
        }

                 
            
     if (($this->load($params) && $this->validate())) {

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);

        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);     

        switch ($this->isAccepted)
        {
            case 1:            
                $query->andFilterWhere(['=', 'isAccepted', 1]);
                $countquery->andFilterWhere(['=', 'isAccepted', 1]);
            break;

            case 2:
                $query->andFilterWhere(['=', 'isAccepted', -1]);
                $countquery->andFilterWhere(['=', 'isAccepted', -1]);                        
            break;                    
            
            case 3:
                $query->andFilterWhere(['=', 'isAccepted', 0]);
                $countquery->andFilterWhere(['=', 'isAccepted', 0]);                        
            break;                    
            
        }
        
        switch ($this->marketNeedAcpt)
        {
            case 1:            
                $query->     andFilterWhere(['=', 'marketNeedAcpt', 0]);
                $countquery->andFilterWhere(['=', 'marketNeedAcpt', 0]);
            break;

            case 2:
                $query->     andFilterWhere(['=', 'marketNeedAcpt', 1]);
                $countquery->andFilterWhere(['=', 'marketNeedAcpt', 1]);
                
                $query->     andFilterWhere(['=', 'marketIsAccept', 0]);
                $countquery->andFilterWhere(['=', 'marketIsAccept', 0]);
            break;                    
            case 3:
                $query->     andFilterWhere(['=', 'marketNeedAcpt', 1]);
                $countquery->andFilterWhere(['=', 'marketNeedAcpt', 1]);
                
                $query->     andFilterWhere(['=', 'marketIsAccept', 1]);
                $countquery->andFilterWhere(['=', 'marketIsAccept', 1]);
                
                                
            break;                    
        }

        
        switch ($this->supplyIsAccept)
        {
            case 1:            
                $query->andFilterWhere(['=', 'supplyIsAccept', 1]);
                $countquery->andFilterWhere(['=', 'supplyIsAccept', 1]);
            break;

            case 2:
                $query->andFilterWhere(['=', 'supplyIsAccept', 0]);
                $countquery->andFilterWhere(['=', 'supplyIsAccept', 0]);                        
            break;                    
        }
                        
        switch ($this->isFinished)
        {
            case 1:            
                $query->andFilterWhere(['=', 'isFinished', 1]);
                $countquery->andFilterWhere(['=', 'isFinished', 1]);
            break;

            case 2:
                $query->andFilterWhere(['=', 'isFinished', 0]);
                $countquery->andFilterWhere(['=', 'isFinished', 0]);                        
            break;                    
        }
        switch ($this->isHaveOriginal)
        {
            case 1:            
                $query->andFilterWhere(['=', 'isHaveOriginal', 1]);
                $countquery->andFilterWhere(['=', 'isHaveOriginal', 1]);
            break;

            case 2:
                $query->andFilterWhere(['=', 'isHaveOriginal', 0]);
                $countquery->andFilterWhere(['=', 'isHaveOriginal', 0]);                        
            break;                    
        }
        switch ($this->productIsAccept)
        {
            case 1:            
                $query->andFilterWhere(['=', 'productIsAccept', 1]);
                $countquery->andFilterWhere(['=', 'productIsAccept', 1]);
            break;

            case 2:
                $query->andFilterWhere(['=', 'productIsAccept', 0]);
                $countquery->andFilterWhere(['=', 'productIsAccept', 0]);                        
            break;                    
        }
        switch ($this->discussIsFinish)
        {
            case 1:            
                $strSql ="update  {{%request_supply}}, {{%supply_status}} set discussIsFinish = 1
                where  discussIsFinish = 0 AND ifnull(discusNote,'') = '' ";
                Yii::$app->db->createCommand($strSql)->execute();

                $query->andFilterWhere(['=', 'discussIsFinish', 1]);
                $countquery->andFilterWhere(['=', 'discussIsFinish', 1]);
            break;

            case 2:
                $strSql ="update  {{%request_supply}}, {{%supply_status}} set discussIsFinish = 1
                where  discussIsFinish = 0 AND ifnull(discusNote,'') = '' ";
                Yii::$app->db->createCommand($strSql)->execute();
            
                $query->andFilterWhere(['<=', 'discussIsFinish', 0]);
                $countquery->andFilterWhere(['<=', 'discussIsFinish', 0]);                        
            break;                    
        }


        switch ($this->readyPlan)
        {
            case 1:            
                $query->andFilterWhere(['>', "ifnull(readyFact,'01.01.1970')", '01.01.1970']);
                $countquery->andFilterWhere(['>',  "ifnull(readyFact,'01.01.1970')", '01.01.1970']);
            break;

            case 2:
                $query->andFilterWhere(['<=', "ifnull(readyFact,'01.01.1970')", '01.01.1970']);
                $countquery->andFilterWhere(['<=',  "ifnull(readyFact,'01.01.1970')", '01.01.1970']);
            break;                    
        }

        switch ($this->supplyDate)
        {
            case 1:            
                $query->andFilterWhere(['>', "ifnull(st17,'01.01.1970')", '01.01.1970']);
                $countquery->andFilterWhere(['>',  "ifnull(st17,'01.01.1970')", '01.01.1970']);
            break;

            case 2:
                $query->andFilterWhere(['<=', "ifnull(st17,'01.01.1970')", '01.01.1970']);
                $countquery->andFilterWhere(['<=',  "ifnull(st17,'01.01.1970')", '01.01.1970']);
            break;                    
        }


// 'readyPlan', 'supplyDate'

                    
     }
        

        if (empty ($this->isActive)) $this->isActive = 2;
        switch ($this->isActive)
        {
            case 2:            
              /*  $query->andFilterWhere(['=', "isActive", 1]);
                $countquery->andFilterWhere(['=',  "isActive", 1]);*/
                $query->andWhere(['=', 'isSchetActive', 1]);
                $countquery->andWhere(['=', 'isSchetActive', 1]);
            break;

            case 3:            
                $query->andWhere(['=', 'isSchetActive', 0]);
                $countquery->andWhere(['=', 'isSchetActive', 0]);
            break;
        }


     
        
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
//$this->debug[] = $query->createCommand()->getRawSql(); 
  
if ($this->mode == 1)   $ps=10;
else $ps = 5; 
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $ps,
            ],
            
            'sort' => [
            
            'attributes' => [        
            'requestId', 
            'requestDate', 
            'supplyDate', 
            'supplyType', 
            'supplyState', 
            'finishDate', 
            'execNum',
            'userFIO',
            'title',
            'schetNum', 
            'schetDate', 
            'summOplata', 
            'schetSumm',
            'execView',
            'isAccepted',
            ],
            
            'defaultOrder' => [  'requestDate' => SORT_DESC ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 
/*******************************************/  
  
  
  
/**/    
 }
 
