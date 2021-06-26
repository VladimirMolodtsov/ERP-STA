<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use app\models\TblWareUse;
use app\models\TblWareContent;
use app\models\TblWareGrp;
use app\models\TblWareType;
use app\models\TblWareProducer;
use app\models\TblWareList;
use app\models\TblWarehouse;
use app\models\TblWareProdLnk;
use app\models\TblWareFormat;
use app\models\TblOtvesList;
use app\models\TblWareNames;


use app\models\TblSchetContent;
use app\models\ZakazContent;

/**
 * WareNomenklatura - модель конструктора номенклатуры
 */
/*
ALTER TABLE `rik_ware_list` ADD COLUMN `addNote` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `rik_ware_format` ADD COLUMN `intSize` INTEGER DEFAULT 0;
ALTER TABLE `rik_ware_list` ADD COLUMN `warePackSize` DOUBLE DEFAULT 0 COMMENT 'размер упаковки';
ALTER TABLE `rik_ware_list` ADD COLUMN `outStatus` INTEGER DEFAULT 0;
ALTER TABLE `rik_ware_list` ADD COLUMN `warePackWeight` DOUBLE DEFAULT 0 COMMENT 'вес упаковки';
ALTER TABLE `rik_ware_list` ADD COLUMN `wareGrpProduce` BIGINT DEFAULT 0 COMMENT 'Ссылка на сырье для производства';
ALTER TABLE `rik_ware_list` ADD COLUMN `wareRefProduce` BIGINT DEFAULT NULL;

ALTER TABLE `rik_ware_list` ADD COLUMN `wareProductionNote` MEDIUMTEXT;
ALTER TABLE `rik_ware_list` ADD COLUMN `wareNote` MEDIUMTEXT;

ALTER TABLE `rik_ware_list` ADD COLUMN `produceType` TINYINT DEFAULT 0;
ALTER TABLE `rik_ware_list` ADD COLUMN `rolType` INTEGER DEFAULT 0;
*/

class WareNomenklatura extends Model
{

    public $id=0;

    public $requestTitle ="";
    public $scladWareTitle="";
    public $nameWareTitle="";

    public $wareType=0;
    public $wareGroup=0;
    public $saleType =1;

    public $wareDensitySel=0;
    public $wareDensity='';

    public $wareFormatSel =0;
    public $wareFormat ='';

    public $wareWidth='';
    public $wareLength='';

    public $wareWidth_r='';
    public $wareLength_r='';

    public $wareWidth_l='';
    public $wareLength_l='';


    public $wareProducer;
    public $wareMark='';
    public $wareSort='';

    public $wareNote='';
    public $wareTitle;
    public $addNote;

    /*производство*/
    public $produceType=0;
    public $isToPage = 0;

    public $rolType=0; //внутренний диаметр

    //public $warePack;
    public $producerRef ;

    public $grpRef ;
    public $state;

    public $density ;
    public $format ;
    public $wareEd ;
    public $wareTypeRef ;
    public $store;


    public $outStatus= 0; //отгрузка
    public $warePackSize=''; //размер упаковки
    public $warePackWeight='';//вес упаковки

    public $wareGrpProduce;
    public $wareRefProduce;
    public $wareProductionNote='';

    public $refSclad=0;
    public $lnkSclad=0;

    public $refName=0;
    public $lnkName=0;



    public $isConfirmed=0;
    public $isActive=1;

    public $lnkProd =0;
    public $wareRef =0;
    public $refSchet =0;
    public $refZakaz =0;
    public $wareNameRef=0;
    
    
    
/*

wareStore




    public $id=0;
    public $orgTitle;
    public $orgTitleUse;
    public $scladTitle;
    public $goodTitle;
    public $isActive=1;
    public $strDate;
    public $isProduction;

    public $sumValue=0;
    public $syncDateTime=0;
    public $isFiltered;
    public $isInUse;

    public $goodAmount;
    public $fltScladTitle;
    public $fltOrgTitle;
    public $errOnly=0;

    public $wareTypeName="";
    public $wareGrpTitle="";
    public $wareProdTitle="";
    public $edTitle;*/

    public $debug=[];

    /***/

        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;

  
     
   /*Настройка*/
    public $useKonstructor = 1; 
    
    public function rules()
    {
        return [
            [['id','wareType','wareGroup','saleType','wareDensitySel','wareDensity','wareFormat','wareFormatSel', 'wareWidth',
        'wareLength','wareProducer','wareMark','wareSort','wareNote','wareTitle','addNote', 'produceType',
        'isToPage','rolType','warePack','producerRef','grpRef','state','density','format','wareEd',
        'wareTypeRef','store','outStatus','warePackSize','warePackWeight','wareGrpProduce','wareRefProduce','wareProductionNote',
        'refSclad', 'refName', 'wareWidth_l','wareLength_l', 'wareWidth_r','wareLength_r',
           ], 'safe'],

            [['recordId', 'dataType','dataVal', 'wareNameRef' ], 'default'],





        ];
    }
  /***************************/


  public function loadData()
  {

    if (empty ($this->id)) return;

    $record = TblWareList::findOne($this->id);
    if(empty($record)) return;


    $this->requestTitle = $record->wareTitle ;
    $this->wareTitle    = $record->wareTitle ;
    $this->wareType     = $record-> wareTypeRef;
    $this->wareGroup    = $record-> grpRef;

    $this->wareProducer = $record->producerRef ;
    $this->wareDensity  = $record->wareDensity ;
    $this->wareDensitySel = $record->wareDensity ;

    $this->saleType   = $record->isProduction+1;
    $this->wareFormatSel = $record-> refFormat;
    $this->wareFormat    = $record->wareFormat;
    if (empty ($this->wareFormatSel))
    {
       $formatList= $this->getWareFormat();
       foreach ($formatList as $key => $value) {
       if ($record->wareFormat == $value) $this->wareFormatSel = $key;
       }
    }



    $this->wareWidth  = $record->wareWidth;
    $this->wareLength = $record->wareLength;


    $this->wareMark  = $record->wareMark;
    $this->wareSort  = $record->wareSort;
    $this->wareNote  = $record->wareNote;
    $this->addNote   = $record->addNote;

    $this->produceType   = $record->produceType;

    if ($this->produceType == 1)
    {
    $this->wareWidth_l  = $record->wareWidth;
    $this->wareLength_l = $record->wareLength;
    }

    if ($this->produceType == 2)
    {
    $this->wareWidth_r  = $record->wareWidth;
    $this->wareLength_r = $record->wareLength;
    }


    $this->warePackSize  = $record->warePackSize;
    $this->outStatus     = $record->outStatus;
    $this->rolType       = $record->rolType;
    $this->warePackWeight       =  $record->warePackWeight;
    $this->wareGrpProduce       =  $record->wareGrpProduce;
    $this->wareProductionNote   =  $record->wareProductionNote;
    $this->isConfirmed = $record->isConfirmed;


  }

  public function loadScladData()
  {


    if (empty ($this->refSclad)) return;



    $record = TblWarehouse::findOne(intval($this->refSclad));
    if(empty($record)) return;


    $this->scladWareTitle   = $record->title ;
    $this->wareType     = $record-> wareTypeRef;
    $this->wareGroup    = $record-> grpRef;
    $this->wareProducer = $record->producerRef ;
    $this->wareDensity  = $record->wareDensity ;
    $this->wareDensitySel = $record->wareDensity ;
    $this->lnkSclad      = $record->wareListRef;

    $recordProd = TblWareProdLnk::findOne([
        'resRef' => $this->id,
        'srcRef' => $this->refSclad,
        ]);
    if(empty($recordProd)) $this->lnkProd = 0;
                    else $this->lnkProd = $recordProd->id;

    //$this->saleType   = $record->isProduction+1;
       $formatList= $this->getWareFormat();
       foreach ($formatList as $key => $value) {
       if ($record->wareFormat == $value) $this->wareFormat = $key;
       }
    if(empty($this->id))$this->id= $record->wareListRef;

  }

  public function loadNamesData()
  {


    if (empty ($this->refName)) return;



    $record = TblWareNames::findOne(intval($this->refName));
    if(empty($record)) return;


    $this->nameWareTitle   = $record->wareTitle ;
    $this->wareType     = $record-> wareTypeRef;
    $this->wareGroup    = $record-> wareGrpRef;
    $this->wareProducer = $record->producerRef ;
    $this->lnkName      = $record->wareListRef;

    if(empty($this->id))$this->id= $record->wareListRef;

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
           ];

    $res['debug'] = 'Otves';
    $curUser=Yii::$app->user->identity;



    switch ($this->dataType)
    {
        case 'addInSchet':
            $record= TblOtvesList::findOne($this->recordId);
            if (empty($record)) return $res;
            $record->refSchet =  $this->dataVal;
            $record->reservDate = date("Y-m-d H:i");
            $record->refManager  = $curUser->id;
            $record->save();
            $res['val'] =  $record->refSchet;            
            $this->recalcOtvesInSdelka($record->refWareList, 0, $record->refSchet) ;   
        break;
        
        case 'addInZakaz':
            $record= TblOtvesList::findOne($this->recordId);
            if (empty($record)) return $res;
            $record->refZakaz =  $this->dataVal;
            $record->reservDate = date("Y-m-d H:i");
            $record->refManager  = $curUser->id;
            $record->save();
            $res['val'] =  $record->refZakaz;            
            $this->recalcOtvesInSdelka($record->refWareList, $record->refZakaz, 0);    
        break;

        case 'unLinkOtves':
            $record= TblOtvesList::findOne($this->recordId);
            if (empty($record)) return $res;
            $refWareList = $record->refWareList;
            $refZakaz = $record->refZakaz;
            $refSchet = $record->refSchet;
            $record->refZakaz    =  0;        
            $record->refManager  = 0;
            $record->refSchet =  0;
            $record->save();
            $res['val'] =  $record->id;
            $this->recalcOtvesInSdelka($refWareList, $refZakaz, $refSchet);    
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
               $sdRecord->value = $record->v3;
           }
           $sdRecord->isActive = 1;
           $sdRecord->count = $size;
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
               $sdRecord->warePrice = $record->v3;
           }
           $sdRecord->wareCount = $size;
           $sdRecord->dopRequest = $dopRequest;
           $sdRecord->save();           
         }

         return true;
    }    
    
/*****************/
    public function saveWareListDetail()
    {
      $res = [ 'res' => false,
             'dataVal'  => $this->dataVal,
             'recordId' => $this->recordId,
             'dataType' => $this->dataType,
             'val' => '',
             'debug' => '',
           ];

    $res['debug'] = 'here';


    switch ($this->dataType)
    {

        case 'createRealize':
        $record= TblWareList::findOne($this->recordId);
        if (empty($record)) return $res;

        $recordRelize= new TblWareNames();
        if (empty($recordRelize)) return $res;
        $recordRelize->wareTitle   =  trim($this->dataVal);
        $recordRelize->wareGrpRef  = $record->grpRef;
        $recordRelize->wareTypeRef = $record->wareTypeRef;
        $recordRelize->producerRef = $record->producerRef;
        $recordRelize->wareListRef = $record->id;
        $recordRelize->save();
        break;

        case 'createSupplierGood':
        $record= TblWareList::findOne($this->recordId);
        if (empty($record)) return $res;

        $recWarehouse= new TblWarehouse();
        if (empty($recWarehouse)) return $res;
        $recWarehouse->title   =  trim($this->dataVal);
        $recWarehouse->grpRef  = $record->grpRef;
        $recWarehouse->wareTypeRef = $record->wareTypeRef;
        $recWarehouse->producerRef = $record->producerRef;
        $recWarehouse->wareListRef = $record->id;
        $recWarehouse->save();
        break;

        case 'createEd':
            $record= TblWareEd::findOne([
            'edTitle' => trim($this->dataVal),
            ]);
            if (empty($record)) {
                $record= new TblWareEd();
                $record ->edTitle = trim($this->dataVal);
                $record ->save();
            }
            $recordLnk= TblWareEdLnk::findOne([
                'refWareList' =>$this->recordId,
                'refWareEd' =>$record ->id,
            ]);
            if (empty($recordLnk)) {
            $recordLnk= new TblWareEdLnk();
            $recordLnk->refWareList = $this->recordId;
            $recordLnk->refWareEd   = $record ->id;
            $recordLnk->isActive    = 1;
            }
            else
            {

            if ($recordLnk->isActive == 0) $recordLnk->isActive =1 ;
                                   else $recordLnk->isActive =0 ;
            }
            $recordLnk->save();
            $res['val'] =  $record ->id ;
        break;
        case 'isActive':
            $record= TblWareList::findOne($this->recordId);
            if (empty($record)) return $res;

            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            $record->save();
            $res['val'] =  $record->isActive ;
            $res['dataVal'] =  $record->wareTitle ;
        break;
        case 'isProduction':
            $record= TblWareList::findOne($this->recordId);
            if (empty($record)) return $res;

            if ($record->isProduction == 0) $record->isProduction =1 ;
                                       else $record->isProduction =0 ;
            $record->save();
            $res['val'] =  $record->isProduction ;
        break;
        case 'isEdActive':
            $record= TblWareEdLnk::findOne([
            'refWareList' =>$this->recordId,
            'refWareEd' =>$this->dataVal,
            ]
            );
            if (empty($record)) {
            $record= new TblWareEdLnk();
            $record->refWareList = $this->recordId;
            $record->refWareEd   = $this->dataVal;
            $record->isActive    = 1;
            }
            else
            {

            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            }
            $record->save();
            $res['val'] =  $record->isActive ;
        break;

        case 'isEdMain':
            $record= TblWareEdLnk::findOne([
            'refWareList' =>$this->recordId,
            'refWareEd' =>$this->dataVal,
            ]
            );
            if (empty($record)) {
            $record= new TblWareEdLnk();
            $record->refWareList = $this->recordId;
            $record->refWareEd   = $this->dataVal;
            $record->isActive    = 1;
            $record->isMain      = 1;
            }
            else
            {

            if ($record->isMain == 0) {
                $record->isMain    =1;
                $record->isActive  =1;
                }
                else $record->isMain =0 ;
            }
            $record->save();
            $res['val'] =  $record->isMain ;
        break;


        default:
        return $res;
     }


    $res['res'] = true;
    return $res;
    }
/*****************/


  /*
    1. У подтвержденных наименований не должно совпасть название!
    2. Если ссылка на склад не пустая привязываем товар поставщика к номенклатуре
    3. Если номенклатура будет исправлена то изменения сохранятся
    4. если название сброшено, то сохранения не будет
  */

  public function saveData()
  {
     $res =[
        'ret'=> false,
        'err'=> '',
        'wareTitle ' => $this->wareTitle,
        'id' => 0,
        'isNew' => true,
     ];

    if(empty($this->wareTitle)) return $res;

    if (empty ($this->id)) {
        $record = TblWareList::findOne([
            'wareTitle' => $this->wareTitle,
            'isConfirmed' => 1
        ]);
        if(!empty($record)) {
            $res['isNew'] = false;
        }
        else $record  = new TblWareList();
    }
    else $record = TblWareList::findOne($this->id);
    if(empty($record)) return $res;


    $record->wareTitle =$this->wareTitle;
    $record-> wareTypeRef = $this->wareType;
    $record-> grpRef = $this->wareGroup;
    $record->producerRef = $this->wareProducer;

    if (empty ($this->wareDensity)) $record->wareDensity = $this->wareDensitySel;
                                    $record->wareDensity = $this->wareDensity;


    $record->isProduction = $this->saleType-1;

    /*Ищем в выбранном*/
    if (!empty ($this->wareFormatSel)){
        $formatRec = TblWareFormat::findOne($this->wareFormatSel);
    }elseif( !empty($this->wareFormat))
    { /* ищем по названию*/
       $formatRec = TblWareFormat::findOne([
        'formatString' => $this->wareFormat,
        'isProduct' => $record->isProduction,
        ]);
        if (empty($formatRec))
        { /*создаем если нет*/
         $formatRec = new TblWareFormat();
         $formatRec->formatString = $this->wareFormat;
         $formatRec->isProduct = $record->isProduction;
         $formatRec->save();
        }
     }
    if (!empty($formatRec))
    {
        $record-> refFormat = $formatRec->id;
        $record->wareFormat = $formatRec->formatString;
    }


    $record->wareMark = $this->wareMark;
    $record->wareSort = $this->wareSort;
    $record->wareNote = $this->wareNote;
    $record->addNote = $this->addNote;

    $record->produceType = $this->produceType;
    $record->warePackSize = $this->warePackSize ;
    $record->outStatus = $this->outStatus;
    $record->rolType = $this->rolType;
    $record->warePackWeight = $this->warePackWeight ;
    $record->wareGrpProduce = $this->wareGrpProduce;
    $record->wareProductionNote = $this->wareProductionNote;
    $record->isConfirmed = 1;
    $record->isActive = 1;


 if ($this->produceType == 1)
    {
    $record->wareWidth   = $this->wareWidth_l ;
    $record->wareLength  = $this->wareLength_l;
    }

    if ($this->produceType == 2)
    {
    $record->wareWidth   = $this->wareWidth_r;
    $record->wareLength  = $this->wareLength_r;
    }

    $record->save();


   /* if(!empty($this->refSclad)){
        $recordSclad = TblWarehouse::findOne($this->id);
        if(empty($recordSclad)) return;
        $recordSclad->wareListRef = $record->id;
        $recordSclad->save();
    }*/
    $res['id'] = $record->id;
    $res['ret']=true;
    return $res;
  }
  /*******************************************************/


  public function lnkWare($act, $src)
  {
     $res =[
        'ret'=> false,
        'id' => $this->id,
        'refSclad' => $this->refSclad,
        'refName' => $this->refName,
        'src'      => $src
     ];
       $record = TblWareList::findOne($this->id);
       if(empty($record)) return $res;


    if ($src=='sclad')
    {

        if ($record->isProduction == 0)
        {
            if(!empty($this->refSclad)){
                $recordSclad = TblWarehouse::findOne($this->refSclad);
                if(empty($recordSclad)) return $res;
                if ($act == 0) $recordSclad->wareListRef = 0;
                      else $recordSclad->wareListRef = $record->id;
                $recordSclad->save();


        /*Обновим связанные наименования реализации


        */
      Yii::$app->db->createCommand('UPDATE {{%ware_names}} SET
           wareTypeRef =:wareTypeRef,
           wareGrpRef =:grpRef,
           producerRef =:producerRef,
           wareListRef =:wareListRef
           WHERE warehouseRef = :warehouseRef',
           [
             ':grpRef' => $recordSclad->grpRef,
             ':wareTypeRef' => $recordSclad->wareTypeRef,
             ':producerRef' => $recordSclad->producerRef,
             ':wareListRef' => $recordSclad->wareListRef,
             ':warehouseRef'=> $recordSclad->id,
           ])->execute();


                }
        }
        else
        {
            $recordScald = TblWareProdLnk::findOne([
                'resRef' => $this->id,
                'srcRef' => $this->refSclad,
            ]);
            if($act == 0){
                $recordScald->delete();
                $res['ret']=true;
                return $res;
            }
            $recordScald = new TblWareProdLnk();
            if(empty($recordSclad)) return $res;
            $recordScald->resRef = $this->id;
            $recordScald->srcRef = $this->refSclad;
            $recordScald->save();
        }



    }
    if ($src=='name')
    {
        $recordName = TblWareNames::findOne($this->refName);
        if(empty($recordName)) return $res;
        if ($act == 0) $recordName->wareListRef = 0;
                  else $recordName->wareListRef = $record->id;
        $recordName->save();
    }
    $res['ret']=true;
    return $res;
  }


  /******************************************************/
  public function getWareData()
  {

    $ret= [
            'res' => false,
            'wareTitle' =>'',
            'wareType' =>'',
            'wareGroup' =>'',
            'wareProducer' =>'',
            'wareDensity' =>'',
            'saleType' =>'',
            'wareFormat' =>'',
            'wareFormatSel' =>'',
            'wareWidth' =>'',
            'wareLength' =>'',
            'wareMark' =>'',
            'wareSort' =>'',
            'wareNote' =>'',
            'addNote' =>'',
            'produceType' =>'',
            'warePackSize' =>'',
            'outStatus' =>'',
            'rolType' =>'',
            'warePackWeight' =>'',
            'wareGrpProduce' =>'',
            'wareProductionNote' =>'',
           ];

    if (empty ($this->id)) return $ret;

    $record = TblWareList::findOne($this->id);
    if(empty($record)) return $ret;

    $ret['wareTitle']    = $record->wareTitle ;
    $ret['wareType']     = $record-> wareTypeRef;
    $ret['wareGroup']    = $record-> grpRef;
    $ret['wareProducer'] = $record->producerRef ;
    $ret['wareDensity']  = $record->wareDensity ;
    $ret['saleType']   = $record->isProduction+1;
    $ret['wareFormatSel'] = $record-> refFormat;
    $ret['wareFormat'] = $record-> wareFormat;
    if (empty ($ret['wareFormatSel']))
    {
       $formatList= $this->getWareFormat();
       foreach ($formatList as $key => $value) {
       if ($ret['wareFormat'] == $value) $ret['wareFormatSel'] = $key;
       }
    }
    $ret['wareWidth']  = $record->wareWidth;
    $ret['wareLength'] = $record->wareWidth;

    $ret['wareMark']  = $record->wareMark;
    $ret['wareSort']  = $record->wareSort;
    $ret['wareNote']  = $record->wareNote;
    $ret['addNote']   = $record->addNote;

    $ret['produceType']   = $record->produceType;
    $ret['warePackSize']  = $record->warePackSize;
    $ret['outStatus']    = $record->outStatus;
    $ret['rolType']       = $record->rolType;
    $ret['warePackWeight']       =  $record->warePackWeight;
    $ret['wareGrpProduce']       =  $record->wareGrpProduce;
    $ret['wareProductionNote']   =  $record->wareProductionNote;
    $ret['res']=true;
    return $ret;
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

/*

            [['id','wareDensitySel','wareDensity','','wareWidth',
        'wareLength','', 'produceType',
        'isToPage','rolType','warePack','producerRef','grpRef','state','density','format','wareEd',
        'wareTypeRef','store','outStatus','warePackSize','warePackWeight','wareGrpProduce','wareRefProduce','wareProductionNote',
           ], 'safe'],



*/


 public function createWareTitle ()
 {
    $ret= [
     'res' => false,
     'wareTitle'    => '',
     'wareType'     => $this->wareType,
     'wareGrp'      => $this->wareGroup,
     'saleType'     => $this->saleType,
     'wareFormat'   => $this->wareFormat,
     'wareFormatSel'   => $this->wareFormatSel,
     'wareProducer' => $this->wareProducer,
     'wareDensity'  => $this->wareDensity,

     'wareMark'     => $this->wareMark,
     'wareSort'     => $this->wareSort,
     'addNote'      => $this->addNote,

     'produceType'       => $this->produceType,
     'warePackSize'      => $this->warePackSize,
     'outStatus'         => $this->outStatus,

     'wareWidth_r'       => $this->wareWidth_r,
     'wareLength_r'      => $this->wareLength_r,
     'wareWidth_l'       => $this->wareWidth_l,
     'wareLength_l'      => $this->wareLength_l,
     'rolType'         => $this->rolType,
     'warePackWeight'  => $this->warePackWeight,

    ];

    $wareTitle="";

    $wareTypeRef = intval ($this->wareType);
    if (empty($wareTypeRef)) return $ret;
    $wareType = Yii::$app->db->createCommand('Select wareTypeName from {{%ware_type}} where id=:ref',
                                            [':ref' =>$wareTypeRef ])->queryScalar();


    $grpRef = intval($this->wareGroup);
    //if (empty($grpRef)) return $ret;
        $wareGroup = Yii::$app->db->createCommand('Select wareGrpTitle from {{%ware_grp}} where id=:ref',
        [':ref' =>$grpRef ])->queryScalar();

    $wareTitle = $wareType." ".$wareGroup;

   if ($this->saleType==1){
   if (!empty($this->wareFormat))
        $wareTitle .= " ф.".$this->wareFormat;
    else{
      $strSql="SELECT  formatString, width,length FROM {{%ware_format}} where id =:refFormat";
      $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->wareFormat)])->queryOne();
      $wareTitle .= " ф.".$formatParam['formatString'];
    }
    }


   if ($this->saleType==2){

    if ($this->produceType==1){

        if (!empty($this->wareWidth_l) && !empty($this->wareLength_l))
        $wareTitle .= " ф.".$this->wareWidth_l."*".$this->wareLength_l;
        else{
        $strSql="SELECT  formatString, width,length FROM {{%ware_format}} where id =:refFormat";
        $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->wareFormat)])->queryOne();
        $wareTitle .= " ф.".$formatParam['formatString'];
        }

            switch ($this->outStatus){
            case 1:
            if (!empty($this->warePackSize)) $wareTitle .= ", (".$this->warePackSize."л.)";
            break;
            }

       }
    if ($this->produceType==2){
        if (!empty($this->wareWidth_r) && !empty($this->wareLength_r))
        $wareTitle .= " ф.".$this->wareWidth_r."*".$this->wareLength_r;
        else{
        $strSql="SELECT  formatString, width,length FROM {{%ware_format}} where id =:refFormat";
        $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->wareFormat)])->queryOne();
        $wareTitle .= " ф.".$formatParam['formatString'];
        }
    }

   }


   if (!empty($this->wareDensity))
        $wareTitle .= ", пл.".$this->wareDensity." г/кв.м";

    if (!empty($this->wareMark))
        $wareTitle .= ", марка ".$this->wareMark."";


    $producerRef = intval($this->wareProducer);
    $ret['producerRef'] =$producerRef;
    if (!empty($producerRef)){
        $wareProdTitle = Yii::$app->db->createCommand('Select wareProdTitle from {{%ware_producer}} where id=:ref',
        [':ref' =>$producerRef ])->queryScalar();
        $wareTitle .= ", пр-во ".$wareProdTitle;
    }

    if (!empty($this->wareSort))
        $wareTitle .= ", сорт ".$this->wareSort."";



   if (!empty($this->addNote))
        $wareTitle .= ", ".trim($this->addNote);

   if ($this->saleType==2){

    switch ($this->rolType){
            case 20:
                $wareTitle .= ", вн.диаметр 20 мм без втулки";
            break;

            case 50:
                $wareTitle .= ", вн.диаметр 50 мм со втулкой";
            break;

            case 50:
                $wareTitle .= ", вн.диаметр 76 мм со втулкой";
            break;

    }

    switch ($this->outStatus){
            case 2:
                $wareTitle .= ", листы";
            break;
            case 3:
                $wareTitle .= ", вес";
            break;

            case 4:
                $wareTitle .= ", рол.";
            break;

            case 5:
            if (!empty($this->warePackWeight)) $wareTitle .= ", (".$this->warePackWeight."кг.)";
                                        else    $wareTitle .= ", вес";
            break;
    }

   }







    $ret['res'] = true;
    $ret['wareTitle'] =$wareTitle;

   return $ret;
 }

  public function createNomenklature($wareTypeRef, $grpRef, $producerRef, $wareFormat, $wareDensity, $warePack)
  {
    $this->wareTitle="";

    $wareTypeRef = intval ($wareTypeRef);
    $grpRef      = intval ($grpRef);
    $producerRef = intval ($producerRef);

    if (!empty($wareTypeRef))
        $wareType = Yii::$app->db->createCommand('Select wareTypeName from {{%ware_type}} where id=:ref',
        [':ref' =>$wareTypeRef ])->queryScalar();
    else return false;

    if (!empty($grpRef))
        $wareGroup = Yii::$app->db->createCommand('Select wareGrpTitle from {{%ware_grp}} where id=:ref',
        [':ref' =>$grpRef ])->queryScalar();
    else return false;


   $this->wareTitle = $wareType." ".$wareGroup;
   if (!empty($wareFormat))
        $this->wareTitle .= " ф.".$wareFormat;
   if (!empty($wareDensity))
        $this->wareTitle .= ", пл.".$wareDensity." г/кв.м";


    if (!empty($producerRef)){
        $wareProdTitle = Yii::$app->db->createCommand('Select wareProdTitle from {{%ware_producer}} where id=:ref',
        [':ref' =>$producerRef ])->queryScalar();
     $this->wareTitle .= ", пр-во ".$wareProdTitle;
    }

   if (!empty($warePack))
        $this->wareTitle .= ", (".$warePack.")";


  }
/*****************/

  public function loadWareSetPar()
  {
    if (!empty($this->wareFormatSel))
    {
     $strSql="SELECT  formatString, width,length, intSize FROM {{%ware_format}} where id =:refFormat";
     $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->wareFormatSel)])->queryOne();

     if (empty($this->wareFormat)  && !empty($formatParam['formatString'])  )$this->wareFormat=$formatParam['formatString'];
     if (empty($this->wareWidth)  && !empty($formatParam['width'])  )$this->wareWidth=$formatParam['width'];
     if (empty($this->wareLength) && !empty($formatParam['length']) )$this->wareLength=$formatParam['length'];
     if (empty($this->rolType) && !empty($formatParam['rolType']) )$this->intSize=$formatParam['intSize'];
    }
  }
/*****************/

  public function getWareSetProvider($params, $confirm )
   {

    $query  = new Query();
    $query->select ([ '{{%ware_list}}.id',
                      'wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
                      'wareSort',
                      'wareMark',
                      'isConfirmed',
                      'isActive'
                      ])
            ->from("{{%ware_list}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_list}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_list}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_list}}.wareTypeRef")
            ;



    $countquery  = new Query();
    $countquery->select (" count({{%ware_list}}.id)")
      ->from("{{%ware_list}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_list}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_list}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_list}}.wareTypeRef")
                 ;

     $query->andWhere(['=', '{{%ware_list}}.isActive', $this->isActive]);
     $countquery->andWhere(['=', '{{%ware_list}}.isActive', $this->isActive]);


    switch ($confirm) {
     case 1:
        $query->andWhere(['=', '{{%ware_list}}.isConfirmed', 1]);
        $countquery->andWhere(['=', '{{%ware_list}}.isConfirmed', 1]);
     break;
     case 2:
        $query->andWhere(['=', '{{%ware_list}}.isConfirmed', 0]);
        $countquery->andWhere(['=', '{{%ware_list}}.isConfirmed', 0]);
     break;
    }


    if(!empty($this->grpRef))
    {
     $query->andWhere(['=', '{{%ware_list}}.grpRef', $this->grpRef]);
     $countquery->andWhere(['=', '{{%ware_list}}.grpRef', $this->grpRef]);
    }


    if (($this->load($params) && $this->validate())) {

     $query->andFilterWhere(['like', 'wareTitle', $this->goodTitle]);
     $countquery->andFilterWhere(['like', 'wareTitle', $this->goodTitle]);


     $query->andFilterWhere(['=', '{{%ware_ed_lnk}}.refWareEd', $this->edTitle]);
     $countquery->andFilterWhere(['=', '{{%ware_ed_lnk}}.refWareEd', $this->edTitle]);


     }

     if(!empty ($this->wareType)){
     $query->andFilterWhere(['=', '{{%ware_list}}.wareTypeRef', $this->wareType]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareTypeRef', $this->wareType]);
     }

     if(!empty ($this->wareGroup)){
     $query->andFilterWhere(['=', '{{%ware_list}}.grpRef', $this->wareGroup]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.grpRef', $this->wareGroup]);
     }


     if(!empty ($this->wareProducer)){
     $query->andFilterWhere(['=', '{{%ware_list}}.producerRef', $this->wareProducer]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.producerRef', $this->wareProducer]);
     }


     /*if(!empty ($this->wareFormatSel)){
     $formatList = $this->getWareFormat();
     $format=$formatList[$this->wareFormat];   */
     if(!empty ($this->wareFormat)){
     $query->andFilterWhere(['=', '{{%ware_list}}.wareFormat', $this->wareFormat]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareFormat', $this->wareFormat]);
     }


     if(!empty ($this->wareDensity)){
     $query->andFilterWhere(['=', '{{%ware_list}}.wareDensity', $this->wareDensity]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.wareDensity', $this->wareDensity]);
     }

     if(!empty ($this->warePack)){
     $query->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->warePack]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.warePack', $this->warePack]);
     }

     if(!empty ($this->saleType)){
     $saleType=$this->saleType-1;
     $query->andFilterWhere(['=', '{{%ware_list}}.isProduction', $saleType]);
     $countquery->andFilterWhere(['=', '{{%ware_list}}.isProduction', $saleType]);
     }


     if(!empty ($this->wareSort)){
     $query->andFilterWhere(['Like', '{{%ware_list}}.wareSort', $this->wareSort]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.wareSort', $this->wareSort]);
     }

     if(!empty ($this->wareMark)){
     $query->andFilterWhere(['Like', '{{%ware_list}}.wareMark', $this->wareMark]);
     $countquery->andFilterWhere(['Like', '{{%ware_list}}.wareMark', $this->wareMark]);
     }




     /*$this->createNomenklature(
     $this->wareTypeName, $this->wareGrpTitle, $this->wareProdTitle,
     $this->format, $this->wareWidth, $this->wareLength, $this->density, $this->warePack);        */

     if (!empty ($this->isActive))
     {
       if($this->isActive == 1)
       {
         $query->andFilterWhere(['=', '{{%ware_list}}.isActive', 1]);
         $countquery->andFilterWhere(['=', '{{%ware_list}}.isActive', 1]);
       }

       if($this->isActive == 2)
       {
         $query->andFilterWhere(['=', '{{%ware_list}}.isActive', 0]);
         $countquery->andFilterWhere(['=', '{{%ware_list}}.isActive', 0]);
       }
     }


     if (!empty ($this->isProduction))
     {
       if($this->isProduction == 1)
       {
         $query->andFilterWhere(['=', 'isProduction', 1]);
         $countquery->andFilterWhere(['=', 'isProduction', 1]);
       }

       if($this->isProduction == 2)
       {
         $query->andFilterWhere(['=', 'isProduction', 0]);
         $countquery->andFilterWhere(['=', 'isProduction', 0]);
       }
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
                      'wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
            ],
            'defaultOrder' => [ 'wareTitle' => 'SORT_ASC' ],

            ],

        ]);
    return  $dataProvider;
   }
 /***********/


  public function getWarehouseSetProvider($params)
   {

    $query  = new Query();
    $query->select ([ '{{%warehouse}}.id',
                      'title as wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
                      ])
            ->from("{{%warehouse}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")
            ;



    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
             ->from("{{%warehouse}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")
                 ;

     $query->andWhere(['=', '{{%warehouse}}.wareListRef', $this->id]);
     $countquery->andWhere(['=', '{{%warehouse}}.wareListRef', $this->id]);



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
                      'wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
            ],
            'defaultOrder' => [ 'wareTitle' => 'SORT_ASC' ],

            ],

        ]);
    return  $dataProvider;
   }
 /***********/

  public function getWarehouseProdProvider($params)
   {

    $query  = new Query();
    $query->select ([ '{{%warehouse}}.id',
                      'title as wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
                      ])
            ->from("{{%warehouse}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")
            ->leftJoin("{{%ware_prod_lnk}}","{{%ware_prod_lnk}}.srcRef= {{%warehouse}}.id")
            ;



    $countquery  = new Query();
    $countquery->select (" count({{%warehouse}}.id)")
             ->from("{{%warehouse}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%warehouse}}.grpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%warehouse}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%warehouse}}.wareTypeRef")
            ->leftJoin("{{%ware_prod_lnk}}","{{%ware_prod_lnk}}.srcRef= {{%warehouse}}.id")
                 ;

     $query->andWhere(['=', '{{%ware_prod_lnk}}.resRef', $this->id]);
     $countquery->andWhere(['=', '{{%ware_prod_lnk}}.resRef', $this->id]);



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
                      'wareTitle',
                      'wareFormat',
                      'wareDensity',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
            ],
            'defaultOrder' => [ 'wareTitle' => 'SORT_ASC' ],

            ],

        ]);
    return  $dataProvider;
   }
 /***********/



public function getWareScladEdList()
{
   $list = Yii::$app->db->createCommand('Select DISTINCT ed from {{%warehouse}}')
                    ->queryAll();

   $N = count($list);
   $res['Все']='Все';
   for ($i=0; $i<$N; $i++)
   {
     $ed= $list[$i]['ed'];
       $res[$ed]=$ed;
    }
   return $res;
}



public function getEdListProvider($params)
   {



    $query  = new Query();
    $query->select ([
                      '{{%ware_ed}}.id',
                      '{{%ware_ed}}.edTitle',
                      ])
            ->from("{{%ware_ed}}")
            ->distinct()
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%ware_ed}}.id) )")
            ->from("{{%ware_ed}}")
            ;

/*          $query->andWhere(['=','{{%ware_ed_lnk}}.refWareList',$this->id]);
     $countquery->andWhere(['=','{{%ware_ed_lnk}}.refWareList',$this->id]);
*/

    if (($this->load($params) && $this->validate())) {


     }



    $command = $query->createCommand();
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 30,
            ],

            'sort' => [

            'attributes' => [
                      'edTitle',
                      'isActive',
                      'isMain'
             ],
            'defaultOrder' => [ 'edTitle'  => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/



public function getWarehouseListProvider($params)
   {



    $query  = new Query();
    $query->select ([
                      '{{%warehouse}}.id',
                      '{{%warehouse}}.title',
                      '{{%warehouse}}.ed',
                      'amount',
                      ])
            ->from("{{%warehouse}}")
            ->distinct()
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%warehouse}}.id) )")
            ->from("{{%warehouse}}")
            ;

          $query->andWhere(['=','{{%warehouse}}.wareListRef',$this->id]);
     $countquery->andWhere(['=','{{%warehouse}}.wareListRef',$this->id]);


    if (($this->load($params) && $this->validate())) {


     }



    $command = $query->createCommand();
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 30,
            ],

            'sort' => [

            'attributes' => [
                      'title',
                      'amount',
             ],
            'defaultOrder' => [ 'title'  => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/



public function getWareNameListProvider($params)
   {



    $query  = new Query();
    $query->select ([
                      '{{%ware_names}}.id',
                      '{{%ware_names}}.wareTitle',
                      '{{%ware_names}}.wareEd',
                      ])
            ->from("{{%ware_names}}")
            ->distinct()
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%ware_names}}.id) )")
            ->from("{{%ware_names}}")
            ;

          $query->andWhere(['=','{{%ware_names}}.wareListRef',$this->id]);
     $countquery->andWhere(['=','{{%ware_names}}.wareListRef',$this->id]);


    if (($this->load($params) && $this->validate())) {


     }



    $command = $query->createCommand();
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 30,
            ],

            'sort' => [

            'attributes' => [
                      'wareTitle',
                      'wareEd',
             ],
            'defaultOrder' => [ 'wareTitle'  => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/


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


public function getOtvesInWork()
{

   return Yii::$app->db->createCommand('Select count({{%otves_list}}.id) from {{%otves_list}}
   WHERE {{%otves_list}}.refZakaz> 0 OR  {{%otves_list}}.refSchet > 0 ')
                    ->queryScalar();   
}

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
                      '({{%otves_list}}.refZakaz+{{%otves_list}}.refSchet) as inuse',
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

          $query->andWhere(['>','ifnull({{%otves_list}}.size,0)',0]);
     $countquery->andWhere(['>','ifnull({{%otves_list}}.size,0)',0]);

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
                      'wareTitle',
                      'inuse', 
                      'size'
             ],
            'defaultOrder' => [ 'inuse'  => 'SORT_DESC', 'size' => 'SORT_ASC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/



/**/
 }

