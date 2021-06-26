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
use app\models\TblWareNames;

use app\models\WareForm;
/**
 *  Товары в наименованиях реализации 
 */

class WareNames extends Model
{

    public $id=0;

    public $wareType=0;
    public $wareGroup=0;
    public $wareProducer=0;

    public $wareTitleShow="";
    
    public $wareEd ;
    public $wareTypeRef ;
    public $wareGrpRef;
    public $producerRef;
    
    public $wareTypeName;
    public $wareGrpTitle;
    public $wareProdTitle;
      
    public $isActive=1;
    public $isInPrice;
    public $showProdutcion;
      
    public $v1;
    public $v2;
    public $v3;
    public $v4;



    public $debug=[];
    public $wareTitle='';
    public $nomTitle='';
    public $nomNote ='';
    /***/
    
     /*Service*/
    public $mode=0;
    public $orgRef=0;
    public $orgTitle;

    public $format='';
    public $density = '';
    public $wareSort= '';
    public $wareMark= '';

    
    public $saleType=0;
    public $wareWidth  = '';
    public $wareLength = '';
    
    
    public $refSchet =0;
    public $refZakaz =0;


  public $command;
  public $count;

     /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;


    
    public function rules()
    {
        return [
            [['id','wareType','wareGroup','wareTitle','wareEd', 'nomTitle', 
            'wareTypeName','wareGrpTitle','wareProdTitle','isActive',
            'showProdutcion'
             ], 'safe'],
            
            [[ 'recordId','dataType','dataVal',

            'wareTitle', 'wareType', 'wareGroup','wareProducer', 'wareEd', 'isActive', 'isInPrice',
            'v1', 'v2', 'v3', 'v4'

            ], 'default'],
        ];
    }
    
/*****************************************/
public function saveData ()
{
  $this->id = intval($this->id);
   $ret =[
     'res' => false,
     'id'  => $this->id,
   ];

  if (empty($this->id))  $record = new TblWareNames();
                    else  $record = TblWareNames::findOne($this->id );

  if (empty($record)) return $ret;

  $record->wareTitle = $this->wareTitle;
  $record->wareGrpRef = $this->wareType;
  $record->wareTypeRef = $this->wareGroup;
  $record->producerRef = $this->wareProducer;
  $record->wareEd = $this->wareEd;
  $record->isInPrice = $this->isInPrice;
  $record->v1 = (float)str_replace(',', '.',$this->v1);
  $record->v2 = (float)str_replace(',', '.',$this->v2);
  $record->v3 = (float)str_replace(',', '.',$this->v3);
  $record->v4 = (float)str_replace(',', '.',$this->v4);

  $record->save();

  $ret['id'] = $record->id;
  $ret['res'] = true;
  return $ret;
}

public function loadData ()
{
   $record = TblWareNames::findOne($this->id );
   if (empty($record)) return false;
  $this->wareTitle = $record->wareTitle;
  $this->wareType = $record->wareGrpRef;
  $this->wareGroup = $record->wareTypeRef;
  $this->wareProducer = $record->producerRef;
  $this->wareEd = $record->wareEd;
  $this->isInPrice = $record->isInPrice;
  $this->v1 = $record->v1;
  $this->v2 = $record->v2;
  $this->v3 = $record->v3;
  $this->v4 = $record->v4;

  $nmRecord = TblWareList::findOne($record->wareListRef);
  if(!empty($nmRecord))
  {
    $this->nomTitle=$nmRecord->wareTitle;
    $this->nomNote =$nmRecord->wareNote;

   }

   return true;
}
/****************************************/
/**
 * Save data through Ajax request 
 * @param - by POST Method 'recordId','dataType','dataVal',
 * @return assosiated array with params for Ajax. 
 *      ['res']==true if successful 
 *      ['isReload']==true if need reload
 *      ['val']   value of the changed field
 * @throws Exception 
 */
public function saveWareNameDetail ()
{

$res = [     'res' => false,
             'dataVal'  => $this->dataVal,
             'recordId' => $this->recordId,
             'dataType' => $this->dataType,
             'val' => '',
             'debug' => '',
             'isReload' => false,
           ];

    $res['debug'] = 'here';

    $record= TblWareNames::findOne(intval($this->recordId));
    if (empty($record)) return $res;


    switch ($this->dataType)
    {
    
    
        case 'wareForm':
            $record->wareFormRef  = intval($this->dataVal);
            $record->save();
            $res['val'] =  $record->wareFormRef ;
            $res['isReload'] = true;
        break;
    
        case 'wareEd':
            $record->wareEd  = mb_substr($this->dataVal, 0, 20);
            $record->save();
            $res['val'] =  $record->wareEd ;
            $res['isReload'] = true;
        break;

        case 'wareTypeName':
            $record->wareTypeRef  = intval($this->dataVal);
            $record->save();
            $res['val'] =  $record->wareTypeRef ;
            $res['isReload'] = true;
        break;

        case 'wareGrpTitle':
            $record->wareGrpRef  = intval($this->dataVal);
            $record->save();
            $res['val'] =  $record->wareGrpRef ;
            $res['isReload'] = true;
        break;

    

        case 'isInPrice':
            if ($record->isInPrice == 0) $record->isInPrice =1 ;
                                   else $record->isInPrice =0 ;
            $record->save();
            $res['val'] =  $record->isInPrice ;
            $res['dataVal'] =  $record->wareTitle ;
            $res['isReload'] = true;
        break;

        case 'isActive':
            if ($record->isActive == 0) $record->isActive =1 ;
                                   else $record->isActive =0 ;
            $record->save();
            $res['val'] =  $record->isActive ;
            $res['dataVal'] =  $record->wareTitle ;
            $res['isReload'] = true;
        break;
        case 'isProduction':
            if ($record->isProduction == 0) $record->isProduction =1 ;
                                       else $record->isProduction =0 ;
            $record->save();
            $res['val'] =  $record->isProduction ;
            $res['isReload'] = true;
        break;

        default:
        return $res;
     }


    $res['res'] = true;
    return $res;


}
    
    
public function getWarePriceData($wareRef)
{
    $res = [ 'res' => false,
             'wareRef'  => $wareRef,
             'v1' => '',
             'v2' => '',
             'v3' => '',
             'v4' => '',
             'wareTitle' => '',
             'wareEd' => '',
           ];
    $record= TblWareNames::findOne($wareRef);
    if (empty($record)) return $res;
    $res['v1']=$record->v1;
    $res['v2']=$record->v2;
    $res['v3']=$record->v3;
    $res['v4']=$record->v4;
    $res['wareTitle']=$record->wareTitle;
    $res['wareEd']=$record->wareEd;
    
    
    $res['res']=true;
    
  return $res;        

}
  /***************************/ 
  
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
            
    if(!empty($this->wareTypeName))  $query ->andWhere("wareTypeRef = ".intval($this->wareTypeName));

   $listStatus = $query->createCommand() ->queryAll();                
   return  ArrayHelper::map($listStatus, 'id', 'wareGrpTitle');      
}  

public function getWareProducer()
{

   $listStatus = Yii::$app->db->createCommand('Select id, wareProdTitle from {{%ware_producer}} where wareProdTitle !=""  order By wareProdTitle')                    
                    ->queryAll();             
   $list = ArrayHelper::map($listStatus, 'id', 'wareProdTitle');      
   $list[0]='Не задан';
   return   $list;
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


/*****************/   
public $wareStat=[];
public function getStat()
  {
   
    $countquery  = new Query();
    $countquery->select (" count({{%ware_names}}.id)")
            ->from("{{%ware_names}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%ware_names}}.wareListRef")                        
            ;                 
    $this->wareStat['Active'] = $countquery->createCommand()->queryScalar();              

     $countquery->andFilterWhere(['=', '{{%ware_names}}.isActive', 1]);
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareListRef', 0]);
   $this->wareStat['ErrNum'] = $countquery->createCommand()->queryScalar();       
       
       
       
                    
  }    

  /***************************/ 

  public function getWareNameProvider($params )
   {
    
    $query  = new Query();
    $query->select ([ '{{%ware_names}}.id',  
                      '{{%ware_names}}.wareTitle',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',  
                      '{{%ware_names}}.wareEd',
                      '{{%ware_names}}.lastUse',
                      '{{%ware_names}}.isActive',
                      '{{%ware_names}}.isInPrice',
                      '{{%ware_names}}.isProduction as showProdutcion',
                      '{{%ware_names}}.useCount',
                      '{{%ware_names}}.wareListRef',
                      '{{%ware_names}}.wareTypeRef',
                      '{{%ware_names}}.wareGrpRef',
                      '{{%ware_list}}.wareTitle as nomTitle',
                      '{{%ware_names}}.wareFormRef',
                      '{{%ware_form}}.formTitle'

                      ])
            ->from("{{%ware_names}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%ware_names}}.wareListRef")
            ->leftJoin("{{%ware_form}}","{{%ware_form}}.id= {{%ware_names}}.wareFormRef")                                                
            ;
        
        
        
    $countquery  = new Query();
    $countquery->select (" count({{%ware_names}}.id)")
            ->from("{{%ware_names}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%ware_names}}.wareListRef")                        
            ;
                 
      if ($this->mode==1)
      {    
          $query->andFilterWhere(['=', '{{%ware_names}}.wareListRef', 0]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareListRef', 0]);         
      }   
          
          
        if (($this->load($params) && $this->validate())) {
        
          $query->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
   
          $query->andFilterWhere(['like', '{{%ware_list}}.wareTitle', $this->nomTitle]);         
     $countquery->andFilterWhere(['like', '{{%ware_list}}.wareTitle', $this->nomTitle]);         

          $query->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
                   
              
          $query->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         

     
          $query->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);         

     
          $query->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);         
                        
      }


     switch($this->showProdutcion)
     {
        case 1:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        break;

        case 2:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        break;
     }


     switch($this->isActive)
     {
        case 2:
             $query->andFilterWhere(['=', '{{%ware_names}}.isActive', 0]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isActive', 0]);
        break;

        case 1:
             $query->andFilterWhere(['=', '{{%ware_names}}.isActive', 1]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isActive', 1]);
        break;
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
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',  
                      'wareEd',
                      'lastUse',
                      'isActive',
                      'useCount',
                      'showProdutcion',
                      'nomTitle',
                      'formTitle'
            ],            
            'defaultOrder' => [ 'wareTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 

   public function createName($wareTypeRef, $grpRef, $producerRef , $wareFormat, $wareDensity, $wareSort, $wareMark)
  {
    $this->wareTitleShow="";
    
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

    
   $this->wareTitleShow = $wareType." ".$wareGroup;
   
   if (!empty($wareFormat)){
    $formatList = $this->getWareFormat();
    $this->wareTitleShow .= " ф.".$formatList[$wareFormat];    
   }  

    if (!empty($wareDensity))
        $this->wareTitleShow .= ", пл.".$wareDensity." г/кв.м";    

    if (!empty($wareMark))
        $this->wareTitleShow .= ", марка ".$wareMark."";    
        

    if (!empty($producerRef)){
        $wareProdTitle = Yii::$app->db->createCommand('Select wareProdTitle from {{%ware_producer}} where id=:ref',
        [':ref' =>$producerRef ])->queryScalar();                
     $this->wareTitleShow .= ", пр-во ".$wareProdTitle;    
    }

    
    if (!empty($wareSort))
        $this->wareTitleShow .= ", сорт ".$wareSort."";    
    
      
  }

  public function loadWareSetPar()
  {
    if (!empty($this->format))
    {
     $strSql="SELECT  formatString, width,length FROM {{%ware_format}} where id =:refFormat";
     $formatParam= Yii::$app->db->createCommand($strSql, [':refFormat' =>intval($this->format)])->queryOne();                
     
     if (empty($this->wareWidth)  && !empty($formatParam['width'])  )$this->wareWidth=$formatParam['width'];
     if (empty($this->wareLength) && !empty($formatParam['length']) )$this->wareLength=$formatParam['length']; 
    
    }
  
  
  }

 
 
  /***************************/ 

  public function getWareSelectNameProvider($params )
   {
    
    $query  = new Query();
    $query->select ([ '{{%ware_names}}.id',  
                      '{{%ware_names}}.wareTitle',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',  
                      '{{%ware_names}}.wareEd',
                      '{{%ware_names}}.lastUse',
                      '{{%ware_names}}.isActive',
                      '{{%ware_names}}.useCount',
                      '{{%ware_names}}.isProduction',
                      '{{%ware_names}}.warehouseRef',
                      '{{%ware_names}}.wareListRef',
                      '{{%ware_list}}.wareTitle as nomTitle',
                      '{{%ware_names}}.v1',
                      '{{%ware_names}}.v2',
                      '{{%ware_names}}.v3',
                      '{{%ware_names}}.v4',

                      ])
            ->from("{{%ware_names}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%ware_names}}.wareListRef")           
            ->distinct();
            //->leftJoin("{{%supply}}","{{%supply}}.wareNameRef= {{%ware_names}}.id")            
            //->groupBy('{{%ware_names}}.id');
            ;
        
    $countquery  = new Query();
    $countquery->select (" count( DISTINCT( {{%ware_names}}.id) )")
            ->from("{{%ware_names}}")                                    
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
            ->leftJoin("{{%ware_list}}","{{%ware_list}}.id= {{%ware_names}}.wareListRef")       
            //->leftJoin("{{%supply}}","{{%supply}}.wareNameRef= {{%ware_names}}.id")                
           // ->groupBy('{{%ware_names}}.id');
            ;
                 
             $query->andWhere(['=', '{{%ware_names}}.isActive', 1]);
        $countquery->andWhere(['=', '{{%ware_names}}.isActive', 1]);


     if(!empty($this->orgRef)){
        $strSql="SELECT title FROM {{%orglist}} where id =:refOrg";
        $this->orgTitle=Yii::$app->db->createCommand($strSql, [':refOrg' =>intval($this->orgRef)])->queryScalar();
        }
        
       if(!empty($this->orgRef) && $this->mode==1)
       {

            $query->leftJoin("{{%supply}}","{{%supply}}.wareNameRef= {{%ware_names}}.id");            
       $countquery->leftJoin("{{%supply}}","{{%supply}}.wareNameRef= {{%ware_names}}.id");
       
       
             $query->andWhere(['=', '{{%supply}}.refOrg', $this->orgRef]);
        $countquery->andWhere(['=', '{{%supply}}.refOrg', $this->orgRef]);

       }

      if (($this->load($params) && $this->validate()))
      {
             $query->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);
        $countquery->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);

             $query->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);
        $countquery->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);
       }

     if(!empty($this->wareTypeName)){
             $query->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);
     }
     
     if(!empty($this->wareGrpTitle)){
             $query->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);
     }              
     
     if(!empty($this->wareProdTitle)){
             $query->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);
     }

     switch($this->showProdutcion)
     {
        case 1:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        break;

        case 2:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        break;
     }

      
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();             
    
    
    $this->createName($this->wareTypeName, $this->wareGrpTitle, $this->wareProdTitle, $this->format, $this->density, $this->wareSort, $this->wareMark);      
    
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
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',  
                      'wareEd',
                      'lastUse',
                      'isActive',
                      'useCount',
                      'v1'
            ],            
            'defaultOrder' => ['useCount'=> 'SORT_DESC', 'wareTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 
 


   public function prepareWarePrice($params )
   {

    $query  = new Query();
    $query->select ([ '{{%ware_names}}.id',
                      '{{%ware_names}}.wareTitle',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',
                      '{{%ware_names}}.wareEd',
                      '{{%ware_names}}.lastUse',
                      '{{%ware_names}}.isActive',
                      '{{%ware_names}}.useCount',
                      '{{%ware_names}}.isProduction',
                      '{{%ware_names}}.warehouseRef',
                      '{{%ware_names}}.wareListRef',
                      '{{%ware_names}}.v1',
                      '{{%ware_names}}.v2',
                      '{{%ware_names}}.v3',
                      '{{%ware_names}}.v4',
                      'wareFormRef'
                      ])
            ->from("{{%ware_names}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")
            ->groupBy('{{%ware_names}}.id');
            ;


 $countquery  = new Query();
    $countquery->select (" count({{%ware_names}}.id)")
            ->from("{{%ware_names}}")
            ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
            ->leftJoin("{{%ware_producer}}","{{%ware_producer}}.id= {{%ware_names}}.producerRef")
            ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")
            ->groupBy('{{%ware_names}}.id');
            ;

             $query->andWhere(['=', '{{%ware_names}}.isInPrice', 1]);
        $countquery->andWhere(['=', '{{%ware_names}}.isInPrice', 1]);


       if(!empty($this->orgRef)){
        $strSql="SELECT title FROM {{%orglist}} where id =:refOrg";
        $this->orgTitle=Yii::$app->db->createCommand($strSql, [':refOrg' =>intval($this->orgRef)])->queryScalar();
        }
       if(!empty($this->orgRef) && $this->mode==1)
       {

             $query->andWhere(['=', '{{%supply}}.refOrg', $this->orgRef]);
        $countquery->andWhere(['=', '{{%supply}}.refOrg', $this->orgRef]);

       }

      if (($this->load($params) && $this->validate()))
      {
             $query->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);
        $countquery->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);

             $query->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);
        $countquery->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);
       }

     if(!empty($this->wareTypeName)){
             $query->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);
     }

     if(!empty($this->wareGrpTitle)){
             $query->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);
     }

     if(!empty($this->wareProdTitle)){
             $query->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.producerRef', $this->wareProdTitle]);
     }

     switch($this->showProdutcion)
     {
        case 1:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 0]);
        break;

        case 2:
             $query->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.isProduction', 1]);
        break;
     }

    $this->query= $query;
    $this->command = $query->createCommand();
    $this->count = $countquery->createCommand()->queryScalar();


    }

    public $query;
  /***************************/ 
  public function getWarePriceArray($params )
   {
     $this->prepareWarePrice($params );

     $this->query->orderBy('wareTitle');
     return $this->query->createCommand()->queryAll();
   }
  /***************************/
  public function getWarePriceProvider($params )
   {
    
    $this->prepareWarePrice($params );
    
    $this->createName($this->wareTypeName, $this->wareGrpTitle, $this->wareProdTitle, $this->format, $this->density, $this->wareSort, $this->wareMark);      
    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 25,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'wareTitle',
                      'wareTypeName',
                      'wareGrpTitle',
                      'wareProdTitle',  
                      'wareEd',
                      'lastUse',
                      'isActive',
                      'useCount'
            ],            
            'defaultOrder' => ['useCount'=> 'SORT_DESC', 'wareTitle' => 'SORT_ASC' ],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 


 
 
 /**
 * Provider for units of measurement in wares (наименования реализации) 
 * @param  $params  - http request string
 * @return provider
 * @throws Exception none
 */   
  public function getWareNameEdProvider($params )
   {
    
    $query  = new Query();
    $query->select ([ '{{%ware_names}}.wareEd',  
                      ])
            ->from("{{%ware_names}}")                                              
            ->distinct();
            ;
        
   
   
    $command = $query->createCommand(); 
    $count = count($query->createCommand()->queryAll());             
    
    
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'wareEd',
            ],            
            'defaultOrder' => ['wareEd'=> 'SORT_DESC'],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/ 

 /**
 ALTER TABLE `rik_ware_names` ADD COLUMN `wareFormRef` BIGINT DEFAULT 0 COMMENT 'ссылка на форму (штучная, пачки и т.д.)';
 ALTER TABLE `rik_ware_names` ADD INDEX `rik_ware_names_idx7` (`wareFormRef`);
 */
 
  /**
 * Provider for units of measurement in wares (наименования реализации) 
 * @param  $params  - http request string
 * @return provider
 * @throws Exception none
 */   
  public function getWareFormProvider($params )
   {
    
    $query  = new Query();
    $query->select ([
                      'id',
                      'formTitle',  
                      ])
            ->from("{{%ware_form}}")                                              
            ->distinct();
            ;
        
   
   
    $command = $query->createCommand(); 
    $count = count($query->createCommand()->queryAll());             
    
    
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'formTitle',
            ],            
            'defaultOrder' => ['formTitle'=> 'SORT_ASC'],
            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/
 
/**/    
 }
 
