<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;



use app\models\OrgList;

use app\models\SchetList;
use app\models\TblSchetAct;
use app\models\TblSchetActContent;


/**
 * 
 */
class MarketSchetAct extends Model
{
    /*Настройка*/    
    
    public $id = 0;
           
    public $actNote;
    public $actNum;       
    public $actDate;
    
    public $schetNum;       
    public $schetDate;

    public $orgTitle;
    public $orgINN; 
    public $orgRef; 
    
               
   /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataId;
    public $dataVal;
    
    
    public $debug;  
    
    public function rules()
    {
        return [
            [['recordId','dataType','dataVal','dataId'              
              ], 'default'],
  
        ];
    }

   public function loadData()    
   {
   $this->id = intval($this->id);
       if(empty($this->id)) return;       
       $record= TblSchetAct::findOne($this->id);
       if(empty($record)) return;
       $this->actNote = $record->actNote;
       $this->actNum  = $record->actNum;       
       $this->actDate = $record->actDate;
       
       $schetRecord= SchetList::findOne($record->refSchet);
       if(empty($schetRecord)) return;
          
       $this->schetNum  = $schetRecord->schetNum;       
       $this->schetDate = $schetRecord->schetDate;
   
       $orgRecord= OrgList::findOne($schetRecord->refOrg);
   
       $this->orgTitle = $orgRecord->title;
       $this->orgINN   = $orgRecord->schetINN;
       $this->orgRef   = $orgRecord->id;
        
   }
    
/*************************************************************************************/
/*************************************************************************************/
/* Ajax */


   /**********************************/
   public function saveAjaxData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId' => $this->dataId, 
             'isReload' => false,
             'val' => '',
           ];   
           
           
    switch ($this->dataType)
    {
        case 'wareTitle':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $record->wareTitle = mb_substr($this->dataVal, 0, 100, 'utf-8');
           $record->save(); 
           $res['val'] =  $record->wareTitle ;
           break;
        case 'wareCount':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->wareCount = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->wareCount ;
           break;
        case 'wareEd':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $record->wareEd = mb_substr($this->dataVal, 0, 20, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->wareEd ;
           break;
        case 'warePrice':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->warePrice = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->warePrice ;
           break;                      
        case 'warePrice':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $this->dataVal = (float)str_replace(',', '.',$this->dataVal); 
           $record->warePrice = floatval($this->dataVal);
           $record->save(); 
           $res['val'] =  $record->warePrice ;
           break;     

        case 'isActive':
           $record= TblSchetActContent::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           
           if (empty($record->isActive)) $record->isActive =1;
                                    else $record->isActive =0;
           $record->save(); 
           $res['val'] =  $record->isActive ;
           $res['isReload']= true;
           break;     
           
        case 'addNewWare':
           $record= new TblSchetActContent();     
           if (empty($record)) return $res;
           $record->refAct = intval($this->recordId);
           $record->save(); 
           $res['val'] =  $record->isActive ;
           $record->isActive =1;
           $res['isReload']= true;
           break;     
           
        case 'actNote':
           $record= TblSchetAct::findOne(intval($this->recordId));     
           if (empty($record)) return $res;
           $record->actNote = mb_substr($this->dataVal, 0, 2056, 'utf-8');          
           $record->save(); 
           $res['val'] =  $record->actNote ;
           break;

           
                                 
     }      
     
    $res['res'] = true;    
    return $res;
    }
/***************************************************/

   public function createAct($schetId)
   {
       $schetId = intval($schetId);
       $ret=[
        'schetId' => $schetId,
        'actId' => 0,  
        'res' => false,
        'err' => '',
    ];
      
       $schetRecord= SchetList::findOne($schetId);     
       if (empty($schetRecord )) return $ret;
      
       $record = new TblSchetAct();   
       if (empty($record )) return $ret;
       $record->refSchet =  $schetId;
       $record->actNum =  $schetRecord->schetNum."/акт";
       $record->actDate = date("Y-m-d");
       $record->save();

       Yii::$app->db->createCommand( 'INSERT INTO {{%schet_actContent}}
       (refAct, wareTitle, wareCount, wareEd, warePrice)
       SELECT 
       :refAct, wareTitle, wareCount, wareEd, warePrice
       FROM {{%schetContent}}
       where  refSchet=:refSchet', 
        [':refSchet' => $schetRecord->id,
         ':refAct' =>$record->id        
        ])->execute();

       $ret['actId'] = $record->id; 
       $ret['res'] = true;
       return $ret;       
   }


/***************************************************/      
  public function getWareInActProvider($params)
   {

    $query  = new Query();
    $query->select ([
            'id',
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',           
            'isActive'
                ])
           ->from("{{%schet_actContent}}")
           ->distinct();

    $countquery  = new Query();
    $countquery->select ("count({{%schet_actContent}}.id)")
           ->from("{{%schet_actContent}}")
           ->distinct();

     $query->andWhere(['=', 'refAct',$this->id]);
     $countquery->andWhere(['=', 'refAct',$this->id]);     

    if (($this->load($params) && $this->validate())) {
    
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
            'id',
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',           
            'isActive'
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
/************/   

   /** end of object **/    
   
 }
