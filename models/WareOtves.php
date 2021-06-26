<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use app\models\TblOtvesList;
use app\models\ScladList;

use app\models\TblSchetContent;
use app\models\ZakazContent;

use app\models\TblWareNames;


/**
 * WareOtves - модель работы с отвесами
 */

class WareOtves extends Model
{

    public $mode=0;

    public $id=0;

    public $wareScladRef=0;
    public $wareListRef=0;
    public $wareNameRef=0;
    
    public $wareProducer;

    public $wareTitle;
    
    public $wareScladTitle;
    public $wareListTitle;

    public $isConfirmed=0;
    public $isActive=1;

    public $lnkProd =0;
    public $wareRef =0;
    public $refSchet =0;
    public $refZakaz =0;
    
    public $onlyUsable =0;
    
    public $inUse=0;
    
    public $userFIO;
    public $debug=[];
    /***/

    public $wareParam;
    public $zakazParam;
    public $schetParam;

    /***/

        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;


    public function rules()
    {
        return [
            [[ 'wareTitle', 'userFIO', 'inUse' ], 'safe'],

            [['recordId', 'dataType','dataVal', 'wareNameRef' ], 'default'],





        ];
    }
/***************************/

public function loadSdelkaData()
{
  if (!empty($this->refSchet))
  {
    $strSql= "SELECT title, schetNum, {{%schet}}.schetDate FROM
    {{%schet}},{{%orglist}} WHERE {{%schet}}.refOrg={{%orglist}}.id AND {{%schet}}.id=:refSchet";
    $this->schetParam=  Yii::$app->db->createCommand($strSql,
                    [
                    ':refSchet' => $this->refSchet,
                    ])->queryOne();

  }
  if (!empty($this->refZakaz))
  {
    $strSql= "SELECT title, {{%zakaz}}.id, formDate FROM
    {{%zakaz}},{{%orglist}} WHERE {{%zakaz}}.refOrg={{%orglist}}.id AND {{%zakaz}}.id=:refZakaz";
    $this->zakazParam=  Yii::$app->db->createCommand($strSql,
                    [
                    ':refZakaz' => $this->refZakaz,
                    ])->queryOne();

  }


  if (!empty($this->wareNameRef))
  {
    $strSql= "SELECT wareTitle, wareEd FROM
    {{%ware_names}} WHERE {{%ware_names}}.id =:wareNameRef";
    $this->wareParam=  Yii::$app->db->createCommand($strSql,
                    [
                    ':wareNameRef' => $this->wareNameRef,
                    ])->queryOne();
  }


//use app\models\SchetList;
//use app\models\ZakazList;

}
/**************************/
    public function saveOtvesData ()
    {
      $res = [ 'res' => false,
             'dataVal'  => $this->dataVal,
             'recordId' => $this->recordId,
             'dataType' => $this->dataType,
             'wareNameRef' => $this->wareNameRef,
             'val' => '',
             'debug' => '',
             'reload' => false,
             'isSwitch' => false,
           ];

    $res['debug'] = 'Otves';
    $curUser=Yii::$app->user->identity;



    switch ($this->dataType)
    {
        case 'addInSchet':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->refSchet =  $this->dataVal;
            $record->reservDate = date("Y-m-d H:i");
            $record->refManager  = $curUser->id;
            $record->inUse        = 1;
            $record->save();
            $res['val'] =  $record->refSchet;            
            $this->recalcOtvesInSdelka($record->refWareList, 0, $record->refSchet) ;   
        break;
        
        case 'addInZakaz':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->refZakaz =  $this->dataVal;
            $record->reservDate = date("Y-m-d H:i");
            $record->refManager  = $curUser->id;
            $record->inUse        = 1;
            $record->save();
            $res['val'] =  $record->refZakaz;            
            $this->recalcOtvesInSdelka($record->refWareList, $record->refZakaz, 0);    
        break;

        case 'unLinkOtves':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $refWareList = $record->refWareList;
            $refZakaz = $record->refZakaz;
            $refSchet = $record->refSchet;
            $record->refZakaz    =  0;
            $record->refManager  = 0;
            $record->refSchet =  0;
            $record->inUse =  0;
            $record->save();
            $res['val'] =  $record->id;
            $this->recalcOtvesInSdelka($refWareList, $refZakaz, $refSchet);    
        break;

        case 'switchOtves':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            if ($record->isAvaivable == 1) $record->isAvaivable = 0;
                                      else $record->isAvaivable = 1;
            $record->save();
            $res['val'] =  $record->isAvaivable;
            $res['isSwitch'] = true;

        break;
        
        case 'switchOtvesUse':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            if ($record->inUse == 1) $record->inUse = 0;
                                      else $record->inUse = 1;
            $record->save();
            $res['val'] =  $record->inUse;
            $res['isSwitch'] = true;
        break;        
        
        case 'size':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->size =  $this->dataVal;
            $record->save();
            $res['val'] =  $record->size;
            $res['reload'] = false;             
        break;

        case 'note':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->note =  $this->dataVal;            
            if (!empty($record->note))$record->inUse = 1;            
            $record->save();
            $res['val'] =  $record->inUse;
            $res['reload'] = false;
            $res['isSwitch'] = true;
            $res['dataType'] = 'switchOtvesUse';
        break;

        case 'otvesComment':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->otvesComment =  $this->dataVal;
            $record->save();
            $res['val'] =  $record->otvesComment;
            $res['reload'] = false;

        break;

        case 'otvesSupplier':
            $record= TblOtvesList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->supplier =  mb_substr($this->dataVal, 0 ,75,'utf-8');
            $record->save();
            $res['val'] =  $record->supplier;
            $res['reload'] = false;
        break;

        
        case 'addOtves':
            $scldRec=ScladList::findOne(intval($this->recordId));
            if (empty($scldRec)) return $res;
            for ($i=0;  $i<intval($this->dataVal); $i++)
            {
             $record= new TblOtvesList();
             if (empty($record)) return $res;
             $record->refWareList  = $scldRec->wareListRef;
             $record->refWarehouse = $scldRec->id;
             $record->isAvaivable  = 0;
             $record->addDate      = date('Y-m-d');
             $record->save();
            } 
            $res['reload'] = true;             
        break;
                
        default:
        
        return $res;
     }
     
 
    $res['wareNameRef']=$this->wareNameRef;
    $res['res'] = true;
    return $res;
    }
 
 
 
 
      public function recalcOtvesInSdelka($refWareList, $refZakaz, $refSchet)    
      {

          $record = TblWareNames::findOne(intval($this->wareNameRef));
          if (empty ($record)) return false;

          if(!empty($refZakaz)){
           $size= Yii::$app->db->createCommand('Select sum(size) from  {{%otves_list}}
           WHERE {{%otves_list}}.refWareList = :refWareList and refZakaz =:refZakaz ', 
           [
               ':refWareList' => $refWareList,
               ':refZakaz'    => $refZakaz,
           ])->queryScalar(); 

   
           if (empty($size)) $v = $record->v3;
           else {   
           $v = $record->v1;
           if ($size>100) $v = $record->v2;
           if ($size>400) $v = $record->v3;
           if ($size>3000)$v = $record->v4;
           } 
           
           
           $listSize= Yii::$app->db->createCommand('Select size from  {{%otves_list}}
           WHERE {{%otves_list}}.refWareList = :refWareList and refZakaz =:refZakaz ',
           [
               ':refWareList' => $refWareList,
               ':refZakaz'    => $refZakaz,
           ])->queryAll();
           $dopRequest="";
           for ($i=0; $i< count($listSize); $i++)
           {
           $dopRequest.= $listSize[$i]['size']." ";
           }
           $dopRequest.= $record->wareTitle;

           $sdRecord= ZakazContent::findOne([
              'refZakaz' =>$refZakaz,
              'wareNameRef' =>$this->wareNameRef 
           ]);



           if (empty($sdRecord)){                     
               $sdRecord = new ZakazContent();
               if (empty($sdRecord)) return false;
               $sdRecord->refZakaz = $refZakaz;
               $sdRecord->wareNameRef = $this->wareNameRef;                
               $sdRecord->good = $record->wareTitle;
               $sdRecord->ed = $record->wareEd;
               $sdRecord->value = $v;
           }
           $sdRecord->isActive = 1;
           $sdRecord->count = $size;
           $sdRecord->value = $v;
           $sdRecord->dopRequest = $dopRequest;
           $sdRecord->save();
          }
           
          if(!empty($refSchet)){ 
           $size= Yii::$app->db->createCommand('Select sum(size) from  {{%otves_list}}
           WHERE {{%otves_list}}.refWareList = :refWareList and refSchet =:refSchet', 
           [
               ':refWareList' => $refWareList,
               ':refSchet'    => $refSchet,
           ])->queryScalar(); 


           $listSize= Yii::$app->db->createCommand('Select size from  {{%otves_list}}
           WHERE {{%otves_list}}.refWareList = :refWareList and refSchet =:refSchet ',
           [
               ':refWareList' => $refWareList,
               ':refSchet'    => $refSchet,
           ])->queryAll();
           $dopRequest="";
           for ($i=0; $i< count($listSize); $i++)
           {
           $dopRequest.= $listSize[$i]['size']." ";
           }

           if (empty($size)) $v = $record->v3;
           else {   
           $v = $record->v1;
           if ($size>100) $v = $record->v2;
           if ($size>400) $v = $record->v3;
           if ($size>3000)$v = $record->v4;
           } 

           $sdRecord= TblSchetContent::findOne([
              'refSchet' =>$refSchet,
               'wareNameRef' =>$this->wareNameRef 
           ]);
           if (empty($sdRecord)){                     
               $sdRecord = new TblSchetContent();
               if (empty($sdRecord)) return $res;
               $sdRecord->refSchet = $refSchet;
               $sdRecord->wareNameRef = $this->wareNameRef;                
               $sdRecord->wareTitle = $record->wareTitle;
               $sdRecord->wareEd = $record->wareEd;
               $sdRecord->warePrice = $v;

           }
           $sdRecord->wareCount = $size;
           $sdRecord->dopRequest = $dopRequest;
           $sdRecord->warePrice = $v;          
           $sdRecord->save();           
         }

         return true;
    }    
    
public function getWareFormat()
{

    $query  = new Query();
    $query->select ([ 'id',
                       'formatString',
                    ])
            ->from("{{%ware_format}}")
            ->orderBy ('formatString')
            ;
    $isProduct = intval($this->saleType) - 1;
    if(!empty($this->saleType))   $query ->andWhere("isProduct = ".$isProduct);

   $listStatus = $query->createCommand() ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'formatString');
}


  public function getWareDensity()
  {

    $query  = new Query();
    $query->select ([
                       'wareDensity',
                    ])
            ->from("{{%ware_list}}")
            ->orderBy ('wareDensity')
            ->distinct()
            ;
    $query->andWhere("ifnull(wareDensity,0) > 0");

   $list= $query->createCommand() ->queryAll();
  return  ArrayHelper::map($list, 'wareDensity', 'wareDensity');

  }


public function getWareEdList()
{

   $listStatus = Yii::$app->db->createCommand('Select id, edTitle from {{%ware_ed}}')
                    ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'edTitle');
}

public function getWareTypes()
{

   $listStatus = Yii::$app->db->createCommand('Select id, wareTypeName from {{%ware_type}}')
                    ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'wareTypeName');
}

public function getWareGroups()
{

    $query  = new Query();
    $query->select ([ 'id',
                       'wareGrpTitle',
                    ])
            ->from("{{%ware_grp}}")
            ->andWhere("wareGrpTitle != ''")
            ->orderBy ('wareGrpTitle')
            ;

    if(!empty($this->wareType))  $query ->andWhere("wareTypeRef = ".intval($this->wareType));

   $listStatus = $query->createCommand() ->queryAll();

   //print_r($query->createCommand() ->getRawSql());

   return  ArrayHelper::map($listStatus, 'id', 'wareGrpTitle');
}

public function getWareRefList()
{

    $query  = new Query();
    $query->select ([ 'id',
                       'wareTitle',
                    ])
            ->from("{{%ware_list}}")
            ->andWhere("wareTitle != ''")
            ->orderBy ('wareTitle')
            ;

    if(!empty($this->wareType))  $query ->andWhere("grpRef = ".intval($this->wareGrpProduce));

   $list = $query->createCommand() ->queryAll();
   return  ArrayHelper::map($list, 'id', 'wareTitle');


}

public function getWareProducer()
{

   $listStatus = Yii::$app->db->createCommand('Select id, wareProdTitle from {{%ware_producer}} where wareProdTitle !=""  order By wareProdTitle ')
                    ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'wareProdTitle');
}


public function getWareInOtves()
{

   $listStatus = Yii::$app->db->createCommand('Select DISTINCT {{%ware_list}}.id, {{%ware_list}}.wareTitle from {{%ware_list}}, {{%otves_list}}
   WHERE {{%ware_list}}.id = {{%otves_list}}.refWareList')
                    ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'wareTitle');
}

public function getLnkOtves( $wareRef, $refZakaz, $refSchet)
{
 return Yii::$app->db->createCommand('Select sum(size) from  {{%otves_list}}
   WHERE {{%otves_list}}.refWareList = :refWareList and refZakaz =:refZakaz and refSchet =:refSchet', 
   [
   ':refWareList' => $wareRef,
   ':refZakaz'    => $refZakaz,
   ':refSchet'    => $refSchet,
   ]
 )->queryScalar();
}

public function getPriceOtves( $wareRef, $size)
{
 $liat= Yii::$app->db->createCommand('Select v1, v2, v3, v4 from  {{%ware_names}}
   WHERE {{%ware_names}}.wareListRef = :refWareList', 
   [
   ':refWareList' => $wareRef,
   ]
 )->queryOne();
 
 $v = $liat['v1'];
 if ($size>100) $v = $liat['v2'];
 if ($size>400) $v = $liat['v3'];
 if ($size>3000) $v = $liat['v4']; 
 return $v;
}


public function getOtvesInWork()
{

   return Yii::$app->db->createCommand('Select count({{%otves_list}}.id) from {{%otves_list}}
   WHERE {{%otves_list}}.refZakaz> 0 OR  {{%otves_list}}.refSchet > 0 ')
                    ->queryScalar();   
}

public $wareProducerTitle="";
/***************/
public function loadWareData()
{       
  $record = ScladList::findOne($this->wareScladRef);
  if (empty($record)) return;
  $this->wareScladTitle = $record->title;
  $this->wareListTitle =  Yii::$app->db->createCommand('Select wareTitle from {{%ware_list}}
   WHERE id =:wareListRef', [':wareListRef' => $record->wareListRef])->queryScalar();
  $this->wareRef =  $record->wareListRef;                 
  $this->wareProducer   = $record->producerRef;
  $this->wareProducerTitle=  Yii::$app->db->createCommand('Select wareProdTitle from {{%ware_producer}}
   WHERE id =:wareProducerRef', [':wareProducerRef' => $record->producerRef])->queryScalar();
}
/****************/


public function getWareOtvesEditProvider($params)
   {

   
    $query  = new Query();
    $query->select ([
                      '{{%otves_list}}.id',
                      '{{%ware_list}}.wareTitle',
                      '{{%otves_list}}.refWareList',
                      '{{%otves_list}}.refWarehouse',                      
                      '{{%otves_list}}.size',
                      '{{%otves_list}}.refZakaz',
                      '{{%otves_list}}.refSchet',
                      '{{%otves_list}}.note', 
                      '{{%otves_list}}.inUse',                     
                      '{{%otves_list}}.isAvaivable',
                      '{{%otves_list}}.otvesComment',
                      '{{%otves_list}}.addDate',
                      '{{%otves_list}}.supplier',
                      '{{%user}}.userFIO',
                      ])                      
            ->from("{{%ware_list}}")
            ->leftJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%otves_list}}.refManager')
            ->distinct()
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%otves_list}}.id) )")
            ->from("{{%ware_list}}")
            ->leftJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ;

          $query->andWhere(['=','ifnull({{%otves_list}}.inUse,0)',0]);
     $countquery->andWhere(['=','ifnull({{%otves_list}}.inUse,0)',0]);

     if (!empty($this->wareRef)){
          $this->wareTitle = $this->wareRef;
          $query->andWhere(['=','{{%otves_list}}.refWareList',$this->wareRef]);
     $countquery->andWhere(['=','{{%otves_list}}.refWareList',$this->wareRef]);
     
     }
     
     
    if (($this->load($params) && $this->validate())) {

//         $query->andFilterWhere(['Like', '{{%ware_list}}.wareTitle', $this->wareTitle]);
//         $countquery->andFilterWhere(['Like', '{{%ware_list}}.wareTitle', $this->wareTitle]);
         $query->andFilterWhere(['=', '{{%otves_list}}.refWareList', $this->wareTitle]);
         $countquery->andFilterWhere(['=', '{{%otves_list}}.refWareList', $this->wareTitle]);

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
                      'id',
                      'wareTitle',
                      'inuse', 
                      'size',
                      'addDate',                      
             ],
          //  'defaultOrder' => [ 'id'  => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/

public function getOtvesLeaf()
{
    $query  = new Query();
    $query->select ([
                      'count({{%otves_list}}.id)',
                      ])
            ->from('{{%otves_list}}')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ;
    $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
    //$query->andWhere(['=','ifnull(isReject,0)','0']);
    $leafValue['all'] = $query->createCommand()->queryScalar();


    $query  = new Query();
    $query->select ([
                      'count({{%otves_list}}.id)',
                      ])
            ->from('{{%otves_list}}')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ;
    $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
    $query->andWhere(['=','ifnull(isReject,0)','0']);
    $query->andWhere(['=','inUse','1']);
    $query->andWhere(['=','ifnull({{%request_supply}}.id,0)','0']);
    $leafValue['inWork'] = $query->createCommand()->queryScalar();



    $query  = new Query();
    $query->select ([
                      'count({{%otves_list}}.id)',
                      ])
            ->from('{{%otves_list}}')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ->leftJoin('{{%supply_status}}','{{%request_supply}}.id = {{%supply_status}}.refSupply')
            ;
    $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
    $query->andWhere(['=','ifnull(isReject,0)','0']);
    $query->andWhere(['=','inUse','1']);
    $query->andWhere(['>','ifnull({{%request_supply}}.id,0)','0']);
    $query->andWhere(['=',"ifnull({{%supply_status}}.st17,'0000-00-00')",'0000-00-00']);
    $leafValue['inSupply'] = $query->createCommand()->queryScalar();

    $query  = new Query();
    $query->select ([
                      'count({{%otves_list}}.id)',
                      ])
            ->from('{{%otves_list}}')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ;
    $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
    $query->andWhere(['=','ifnull(isReject,0)','0']);
    $query->andWhere(['=','isAvaivable','1']);
    $query->andWhere(['=','inUse','0']);
    $leafValue['isAvailable'] = $query->createCommand()->queryScalar();



    $query  = new Query();
    $query->select ([
                      'count({{%otves_list}}.id)',
                      ])
            ->from('{{%otves_list}}')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ;
    $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
    $query->andWhere(['=','ifnull(isReject,0)','0']);
    $query->andWhere(['=','inUse','0']);
    $leafValue['inSclad'] = $query->createCommand()->queryScalar();


  return $leafValue;
}
/****************/

public function getWareOtvesListProvider($params)
   {

   
    $query  = new Query();
    $query->select ([
                      '{{%otves_list}}.id',
                      '{{%ware_list}}.wareTitle',
                      '{{%otves_list}}.refWareList',
                      '{{%otves_list}}.size',
                      '{{%otves_list}}.refZakaz',
                      '{{%otves_list}}.refSchet',
                      '{{%otves_list}}.note',
                      '({{%otves_list}}.refZakaz+{{%otves_list}}.refSchet) as inReserv',
                      '{{%otves_list}}.inUse',
                      '{{%otves_list}}.isAvaivable',
                      '{{%otves_list}}.otvesComment',
                      '{{%otves_list}}.addDate',
                      '{{%otves_list}}.supplier',
                      '{{%user}}.userFIO',
                      ])
            ->from("{{%ware_list}}")
            ->leftJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%otves_list}}.refManager')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ->leftJoin('{{%supply_status}}','{{%request_supply}}.id = {{%supply_status}}.refSupply')
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%otves_list}}.id) )")
            ->from("{{%ware_list}}")
            ->leftJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%otves_list}}.refManager')
            ->leftJoin('{{%schet}}','{{%schet}}.id = {{%otves_list}}.refSchet')
            ->leftJoin('{{%request_supply}}','{{%request_supply}}.refSchet = {{%otves_list}}.refSchet')
            ->leftJoin('{{%supply_status}}','{{%request_supply}}.id = {{%supply_status}}.refSupply')
            ;

          $query->andWhere(['=',"ifnull({{%supply_status}}.st17,'0000-00-00')",'0000-00-00']);
     $countquery->andWhere(['=',"ifnull({{%supply_status}}.st17,'0000-00-00')",'0000-00-00']);
            
            
     if (!empty($this->onlyUsable))
     {        
          $query->andWhere(['>','ifnull({{%otves_list}}.size,0)',0]);
     $countquery->andWhere(['>','ifnull({{%otves_list}}.size,0)',0]);

          $query->andWhere(['>','ifnull({{%otves_list}}.isAvaivable,0)',0]);
     $countquery->andWhere(['>','ifnull({{%otves_list}}.isAvaivable,0)',0]);
          
     }
     
     
     if (!empty($this->wareRef)){
          $this->wareTitle = $this->wareRef;
          $query->andWhere(['=','{{%otves_list}}.refWareList',$this->wareRef]);
     $countquery->andWhere(['=','{{%otves_list}}.refWareList',$this->wareRef]);
     
     }
     
     
    if (($this->load($params) && $this->validate())) {

//         $query->andFilterWhere(['Like', '{{%ware_list}}.wareTitle', $this->wareTitle]);
//         $countquery->andFilterWhere(['Like', '{{%ware_list}}.wareTitle', $this->wareTitle]);
         $query->andFilterWhere(['=', '{{%otves_list}}.refWareList', $this->wareTitle]);
         $countquery->andFilterWhere(['=', '{{%otves_list}}.refWareList', $this->wareTitle]);

         $query->andFilterWhere(['Like', 'userFIO', $this->userFIO]);
         $countquery->andFilterWhere(['Like', 'userFIO', $this->userFIO]);
     
        switch ($this->inUse){
        
        case 1:
               $query->andWhere(['=','ifnull({{%otves_list}}.inUse,0)',0]);
          $countquery->andWhere(['=','ifnull({{%otves_list}}.inUse,0)',0]);        
        break;
        case 2:
               $query->andWhere(['>','ifnull({{%otves_list}}.inUse,0)',0]);
          $countquery->andWhere(['>','ifnull({{%otves_list}}.inUse,0)',0]);        
        break;
        }
         
     }

   
   switch ($this->mode)
   {

        case 0:
            $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
            //$query->andWhere(['=','ifnull(isReject,0)','0']);

            $countquery->andWhere(['=','ifnull(isSchetActive,1)','1']);
            //$countquery->andWhere(['=','ifnull(isReject,0)','0']);
        break;

        case 1:
            $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $query->andWhere(['=','ifnull(isReject,0)','0']);
            $query->andWhere(['=','inUse','1']);
            $query->andWhere(['=','ifnull({{%request_supply}}.id,0)','0']);

            $countquery->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $countquery->andWhere(['=','ifnull(isReject,0)','0']);
            $countquery->andWhere(['=','inUse','1']);
            $countquery->andWhere(['=','ifnull({{%request_supply}}.id,0)','0']);

          $this->inUse = 2;
        break;

        case 2:
            $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $query->andWhere(['=','ifnull(isReject,0)','0']);
            $query->andWhere(['=','inUse','1']);
            $query->andWhere(['>','ifnull({{%request_supply}}.id,0)','0']);

            $countquery->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $countquery->andWhere(['=','ifnull(isReject,0)','0']);
            $countquery->andWhere(['=','inUse','1']);
            $countquery->andWhere(['>','ifnull({{%request_supply}}.id,0)','0']);
        break;

        case 3:
            $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $query->andWhere(['=','ifnull(isReject,0)','0']);
            $query->andWhere(['=','inUse','0']);
            $query->andWhere(['=','isAvaivable','1']);

            $countquery->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $countquery->andWhere(['=','ifnull(isReject,0)','0']);
            $countquery->andWhere(['=','inUse','0']);
            $countquery->andWhere(['=','isAvaivable','1']);
        break;

        case 4:
            $query->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $query->andWhere(['=','ifnull(isReject,0)','0']);
            $query->andWhere(['=','inUse','0']);

            $countquery->andWhere(['=','ifnull(isSchetActive,1)','1']);
            $countquery->andWhere(['=','ifnull(isReject,0)','0']);
            $countquery->andWhere(['=','inUse','0']);
        break;


        case 5:
            $query->andWhere("(ifnull({{%request_supply}}.id,0) = 0 OR {{%otves_list}}.refSchet =".intval($this->refSchet).
            " OR {{%otves_list}}.refZakaz = ".intval($this->refZakaz).")");
            $query->andWhere(['=','isAvaivable','1']);

            $countquery->andWhere("(ifnull({{%request_supply}}.id,0) = 0 OR {{%otves_list}}.refSchet =".intval($this->refSchet).
            " OR {{%otves_list}}.refZakaz = ".intval($this->refZakaz).")");
            $countquery->andWhere(['=','isAvaivable','1']);
        break;

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
                      'id',
                      'wareTitle',
                      'inuse', 
                      'size',
                      'addDate',
                      'refZakaz',
                      'refSchet',
                      'userFIO'
             ],
            //'defaultOrder' => [ 'id'  => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/




public function getWareOtvesSvodProvider($params)
   {

   
    $query  = new Query();
    $query->select ([
                      '{{%warehouse}}.id',
                      '{{%warehouse}}.title as wareTitle',
                      '{{%warehouse}}.id as refWarehouse',
                      '{{%warehouse}}.wareListRef',
                      '{{%warehouse}}.amount',                      
                      'sum({{%otves_list}}.size) as sumInOtves',
                      ])                      
            ->from("{{%warehouse}}")
            ->innerJoin('{{%ware_list}}','{{%ware_list}}.id = {{%warehouse}}.wareListRef')
            ->innerJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ->groupby("{{%warehouse}}.id, {{%ware_list}}.id")
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%warehouse}}.id) )")
            ->from("{{%warehouse}}")
            ->innerJoin('{{%ware_list}}','{{%ware_list}}.id = {{%warehouse}}.wareListRef')
            ->innerJoin('{{%otves_list}}','{{%otves_list}}.refWareList = {{%ware_list}}.id')
            ->groupby("{{%warehouse}}.id, {{%ware_list}}.id")
      ;

   /*       $query->andWhere(['>','sum({{%otves_list}}.size)',0]);
     $countquery->andWhere(['>','sum({{%otves_list}}.size)',0]);*/

     
    if (($this->load($params) && $this->validate())) {
         $query->andFilterWhere(['Like', '{{%warehouse}}.title', $this->wareTitle]);
         $countquery->andFilterWhere(['Like', '{{%warehouse}}.title', $this->wareTitle]);
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
                      'wareTitle',
                      'refWarehouse',
                      'wareListRef',
                      'amount',                      
             ],
            'defaultOrder' => [ 'wareTitle'  => 'SORT_DESC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/



/**/
 }

