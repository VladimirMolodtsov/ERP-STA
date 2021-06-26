<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use app\models\OrgList;
use app\models\UserList;
use app\models\TblOrgDostavka;
/**
 * DstNoteForm  - модель управления комментариями к доставке
 */


class DstNoteForm extends Model
{

    public $refOrg;

   /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataId;
    public $dataVal;
    
    public $debug;    
    
    
   
    public function rules()
    {
        return [
            [['recordId','dataType','dataVal','dataId'], 'default'],

            [[], 'safe'],
        ];
    }
/********************/
  public function getDstNote($id)
  {
    $ret=[
        'res' => false,
        'dstRef' => $id,
        'dstNote' => ''
    ];

    $record = TblOrgDostavka::findOne($id);
    if (empty($record)) return $ret;

    $ret['dstNote'] = $record->note;
    $ret['res'] = true;
    return $ret;
  }
/********************/
 
  public function getDstNoteProvider($params)
   {
    $query  = new Query();
    $countquery  = new Query();

 
    if ( empty($deliverRecord->reason) )
    {
    $query->select ([
        '{{%org_dostavka}}.id',
        'note',
        'isDefault',
        ])
    ->from("{{%org_dostavka}}")    
    ;
    $countquery->select ("count({{%org_dostavka}}.id)")
    ->from("{{%org_dostavka}}")    
    ;
    }    

  $query->andWhere(['=', 'refOrg', $this->refOrg]);
  $countquery->andWhere(['=', 'refOrg', $this->refOrg]);     
        

    
 if (($this->load($params) && $this->validate())) {
/*   $query->andFilterWhere(['like', 'grpGood', $this->grpGood]);
     $countquery->andFilterWhere(['like', 'grpGood', $this->grpGood]);     
*/          
     }

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],
            
            'sort' => [
            
            'attributes' => [
                'id', 
                'isDefault'
            ],
            'defaultOrder' => [ 'isDefault'=> SORT_DESC, 'id' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
  
  /*****************************************/

/* Ajax */

   /**********************************/
   public function saveDstData()
   {     

       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataId'   => $this->dataId,              
             'val' => '',
           ];   
           
           
           
    switch ($this->dataType)
    {        
        case 'dstNote':
          $this->dataVal = trim($this->dataVal);
          if (empty($this->dataVal)) return $res;            
          if (empty($this->recordId)){
            $record= new TblOrgDostavka();
            if (empty($record)) return $res;            
            $record->refOrg = intval($this->dataId);
          } else 
          {
            $record= TblOrgDostavka::findOne(intval($this->recordId));     
            if (empty($record)) return $res;            
          }             
           $record->note = $this->dataVal;              
           $record->save(); 
           $res['val'] =  $record->note;
           $res['recordId'] =  $record->id;
           break;
     }      
     
    $res['res'] = true;    
    return $res;
    }

   /**********************************/



/**/    
 }
 
