<?php
/**
 * @link http://erp-system.ru/
 * @copyright Copyright (c) 2012 STA
 * @license http://erp-system.ru/license/
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 

use yii\db\Expression;

use yii\helpers\Html;


use app\models\FltList;
use app\models\OrgList;
use app\models\UserList;
use app\models\MarketSchetForm;
use app\models\WarehouseForm;
use app\models\TmpOrgReestr;
use app\models\TblOrgCategory;
use app\models\TblOrgJob;
use app\models\TblOrgJobLnk;
/**
 * HeadOrgSearch  - модель сложного поиска клиентов ("магазин" клиентов)
 *
 * @include 
 *
 * @author V.V. Molodtsov <vladimir.molodtsov@gmail.com>
 * @since 1.0
 */
class HeadOrgSearch extends Model
{
    

   public $curOrgJobList = 0;

   public $fltManager="";
   public $orgFilter="";
   public $fltForm="";
   
   public $wareTypes ="";
   public $wareTypeArray=[];
   
   public $wareGrp ="";
   public $wareGrpArray=[];

   public $wareList   ="";
   public $wareListArray=[];
   
   public $fltCategory=0;


        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    public $dataNote;
    public $dataId;


   public $command;
   public $count=0;
   public $debug=array();

    
   public function rules()
   {
        return [
            [[
            'recordId','dataType','dataVal','dataId','dataNote'
            ], 'default'],
            [[ ], 'safe'],
        ];
    }

    
    
/**
 * Save data through Ajax request 
 * @param - by POST Method 'recordId','dataType','dataVal','dataId','dataNote'
 * @return assotiated array with params for Ajax. 
 *      ['res']==true if successful 
 *      ['isReload']==true if need reload
 *      ['val']   value of the changed field
 * @throws Exception 
 */    
   public function saveOrgJobData ()
   {
    
       $this->recordId = intval($this->recordId);
       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'dataNote' => $this->dataNote, 
             'val' => '',
             'isReload' => false
           ];   
           
    switch ($this->dataType)
    {
        case 'addOrgJob':
         $record=  new TblOrgJob();     
         if (empty($record)) return $res;
           $record->jobTitle = $this->dataVal;          
           $record->jobNote = $this->dataNote;  
           $record->dateCreation =date("Y-m-d H:i");       
           $record->save(); 
           $res['val'] =  $record->id ;
           $res['isReload'] =  true;            
           break;           
           
        case 'switchJobList':   
             $this->dataVal = intval($this->dataVal);
             $this->recordId = intval($this->recordId);
         
         $record=  TblOrgJobLnk::findOne([
         'orgRef' => $this->dataVal,
         'jobListRef'=> $this->recordId,         
         ]);     
         if (!empty($record)){
             $record->delete(); 
             $res['val'] =  0;
             $res['isReload'] =  false;            
             break;
         }
          $record=  new TblOrgJobLnk();
          if (empty($record)) return $res;
           $record->orgRef = $this->dataVal;          
           $record->jobListRef = $this->recordId;  
           $record->save(); 
           $res['val'] =  1;
           $res['isReload'] =  false;            
           break;           
           
        default:
        return $res;   
     }      
     
    $res['res'] = true;    
    return $res;
   }      
    
        
    
   public function saveCfgData ()
   {
    
       $this->recordId = intval($this->recordId);
       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
             'isReload' => false
           ];   
           
    switch ($this->dataType)
    {
        case 'catTitle':
         $record=  TblOrgCategory::findOne($this->recordId);     
         if (empty($record)) return $res;
           $record->catTitle = $this->dataVal;          
           $record->save(); 
           $res['val'] =  $record->catTitle ;
           break;
           
     }      
     
    $res['res'] = true;    
    return $res;
   }      

  /************************************************************************/

  public function getOrgJobList()
  {
   $list = Yii::$app->db->createCommand('Select id, jobTitle from {{%org_job}} where isActive = 1')                    
                    ->queryAll();                   
   $res =  ArrayHelper::map($list, 'id', 'jobTitle');        
  
   $res[0]='Не задан';
    /*if (empty ($this->curOrgJobList)  && !empty($res) ){
      reset($res);     
     $this->curOrgJobList=  current($res); 
    }*/
    
    return $res;
  }
        
  public function getManagerList()
  {
   $list = Yii::$app->db->createCommand('Select id, userFIO from {{%user}} where (roleFlg & 0x0004) ')                    
                    ->queryAll();                   
   $res =  ArrayHelper::map($list, 'id', 'userFIO');        
  
   $res[0]='Все';
   $res[-1]='Не задан';
    /*if (empty ($this->curOrgJobList)  && !empty($res) ){
      reset($res);     
     $this->curOrgJobList=  current($res); 
    }*/
    
    return $res;
  }

  public function getFltFormList()
  {
   $list = Yii::$app->db->createCommand('Select id, formTitle from {{%ware_form}} ')                    
                    ->queryAll();                   
   $res =  ArrayHelper::map($list, 'id', 'formTitle');        
  
   $res[0]='Все';
   $res[-1]='Не задан';
    /*if (empty ($this->curOrgJobList)  && !empty($res) ){
      reset($res);     
     $this->curOrgJobList=  current($res); 
    }*/
    
    return $res;
  }
        

        
 /****************************************************************************************/
 /****************************************************************************************/
 /****************************************************************************************/
 /**
 * Prepare data for  client search provider
 
 * @param  $params - request GET params
 * @return none, set  $this->command & $this->count
 * @throws Exception none
 */  
 
 public function prepareSavedClientReestr($params)
 {

   $query  = new Query();
   $countquery  = new Query();
   
   $strSubSQL="(SELECT DISTINCT {{%schet}}.refOrg, wareListRef,  good as goodlist, {{%zakazContent}}.wareNameRef
         from {{%schet}}, {{%zakazContent}}        
         where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ";
   
    $countquery->select ("count(distinct {{%orglist}}.id)")
        ->from("{{%orglist}}")        
        ->leftJoin($strSubSQL, "goods.refOrg =  {{%orglist}}.id ")
        ->leftJoin("{{%ware_list}}", "{{%ware_list}}.id = goods.wareListRef")
        ->leftJoin("{{%ware_names}}", "{{%ware_names}}.id = goods.wareNameRef")
        ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                  
        ->leftJoin("{{%zakaz}}", "{{%zakaz}}.refOrg = {{%orglist}}.id")                  
        ->distinct()
                  ;

     $query->select([
        '{{%orglist}}.id as refOrg',
        '{{%orglist}}.title as orgTitle',
        '{{%orglist}}.schetINN',
        '{{%user}}.userFIO as  managerFIO',
        '{{%orglist}}.contactPhone',
        '{{%orglist}}.contactEmail',
        ]) ->from("{{%orglist}}")        
        ->leftJoin($strSubSQL, "goods.refOrg =  {{%orglist}}.id ")
         ->leftJoin("{{%ware_list}}", "{{%ware_list}}.id = goods.wareListRef")
         ->leftJoin("{{%ware_names}}", "{{%ware_names}}.id = goods.wareNameRef")
         ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                
         ->leftJoin("{{%zakaz}}", "{{%zakaz}}.refOrg = {{%orglist}}.id")                    
        ->distinct()
        ;

        $query->andWhere('{{%zakaz}}.id is not null');
        $countquery->andWhere('{{%zakaz}}.id is not null');

        
        $query->andWhere('isOrgActive = 1');
        $countquery->andWhere('isOrgActive = 1');
        
    if(!empty($this->fltManager))        
     {
         if ($this->fltManager < 0){
             $query->andFilterWhere(['=', '{{%orglist}}.refManager', 0]);
        $countquery->andFilterWhere(['=', '{{%orglist}}.refManager', 0]);
         }
         else {
             $query->andFilterWhere(['=', '{{%orglist}}.refManager', $this->fltManager]);
        $countquery->andFilterWhere(['=', '{{%orglist}}.refManager', $this->fltManager]);
        }
     }
            
            
    if(!empty($this->fltForm))        
     {
         if ($this->fltForm < 0){
             $query->andFilterWhere(['=', '{{%ware_names}}.wareFormRef', 0]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareFormRef', 0]);
         }
         if ($this->fltForm > 0) {
             $query->andFilterWhere(['=', '{{%ware_names}}.wareFormRef', $this->fltForm]);
        $countquery->andFilterWhere(['=', '{{%ware_names}}.wareFormRef', $this->fltForm]);
        }
     }
        

     if(!empty($this->orgFilter))        
     {
             $query->andFilterWhere(['Like', 'title', $this->orgFilter]);
        $countquery->andFilterWhere(['Like', 'title', $this->orgFilter]);
     }

     if(!empty($this->wareTypes))        
     {     
        $query->andWhere('{{%ware_list}}.wareTypeRef IN ('.$this->wareTypes.') OR {{%ware_names}}.wareTypeRef IN ('.$this->wareTypes.')');
        $countquery->andWhere('{{%ware_list}}.wareTypeRef IN ('.$this->wareTypes.') OR {{%ware_names}}.wareTypeRef IN ('.$this->wareTypes.')');
     }
        
     if(!empty($this->wareGrp))        
     {     
        $query->andWhere('{{%ware_list}}.grpRef IN ('.$this->wareGrp.') OR {{%ware_names}}.wareGrpRef IN ('.$this->wareGrp.')');
        $countquery->andWhere('{{%ware_list}}.grpRef IN ('.$this->wareGrp.') OR {{%ware_names}}.wareGrpRef IN ('.$this->wareGrp.')');
                
     }
        
  
//$this->debug[] = $this->fltCategory;
     if (($this->load($params) && $this->validate()))
     {

        $query->andFilterWhere(['like', 'managerFIO', $this->managerFIO]);
        $countquery->andFilterWhere(['like', 'managerFIO', $this->managerFIO]);

        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);

        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        $countquery->andFilterWhere(['like', 'goodlist', $this->fltGood]);

     }

//$this->debug = $query->createCommand()->getRawSql();
       $this->command = $query->createCommand();
       $this->count = $countquery->createCommand()->queryScalar();
       //echo $countquery->createCommand()->getRawSql(0);
 }
 /****************************************************************************************/
 /**
 * Provider for client search
 * @param  $params - request GET params
 * @return provider
 * @throws Exception none
 */  
   public function getSavedClientReestrProvider($params)
   {

        $this->prepareSavedClientReestr($params);

        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [

        'orgTitle',
        'managerFIO',
        'mainActivity',
        'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastSdelka',
        'category',
        'categoryTitle',

            ],
            'defaultOrder' => [    'orgTitle' => SORT_ASC ],
            ],
        ]);

    return $provider;
   }

/************************************************************/

   public function getSavedClientReestrData($params)
   {
        $this->prepareSavedClientReestr($params);
        $dataList=$this->command->queryAll();


    $mask = realpath(dirname(__FILE__))."/../uploads/headClientReestrReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headClientReestrReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;

    $col_title = array (

        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","ИНН"),
        iconv("UTF-8", "Windows-1251","Менеджер основной"),
        iconv("UTF-8", "Windows-1251","Менеджер основной активность"),
        iconv("UTF-8", "Windows-1251","Менеджеры активность"),


        iconv("UTF-8", "Windows-1251","Чек"),
        iconv("UTF-8", "Windows-1251","Регулярность"),
        iconv("UTF-8", "Windows-1251","Период"),
        iconv("UTF-8", "Windows-1251","План на период"),
        iconv("UTF-8", "Windows-1251","Факт за период"),

        iconv("UTF-8", "Windows-1251","Начало периода"),

        iconv("UTF-8", "Windows-1251","Дата отгрузки"),
        iconv("UTF-8", "Windows-1251","Дата оплаты"),

        iconv("UTF-8", "Windows-1251","Последний счет"),
        iconv("UTF-8", "Windows-1251","Последняя сделка"),

        iconv("UTF-8", "Windows-1251","Последний заказ"),
        iconv("UTF-8", "Windows-1251","Последний контакт"),

        iconv("UTF-8", "Windows-1251","Активный заказ"),
        iconv("UTF-8", "Windows-1251","Активный счет"),


        iconv("UTF-8", "Windows-1251","Товары"),

        iconv("UTF-8", "Windows-1251","Сверка"),

        iconv("UTF-8", "Windows-1251","Телефон"),
        iconv("UTF-8", "Windows-1251","E-mail"),

        iconv("UTF-8", "Windows-1251","Город"),
        iconv("UTF-8", "Windows-1251","Район"),
        iconv("UTF-8", "Windows-1251","Адрес"),

        iconv("UTF-8", "Windows-1251","Категория"),


        );
        fputcsv($fp, $col_title, ";");
    for ($i=0; $i< count($dataList); $i++)
    {
        $list =  $this->getSavedClientReestrRow($dataList[$i]);
        fputcsv($fp, $list, ";");
    }

        fclose($fp);
        return $fname;
   }

   /***************************************/
   public function grpSet($params)
   {
       $this->curOrgJobList = intval($this->curOrgJobList);       
       if(empty($this->curOrgJobList)) return;
        
        $this->prepareSavedClientReestr($params);
   
        $dataList=$this->command->queryAll();
        $N = count($dataList);
   
        for ($i=0;$i<$N;$i++)
        {
        $record=  TblOrgJobLnk::findOne([
         'orgRef' => $dataList[$i]['refOrg'],
         'jobListRef'=> $this->curOrgJobList,         
         ]);     
         if (empty($record)) $record=  new TblOrgJobLnk();         
         if (empty($record)) return;
           
           $record->orgRef = $dataList[$i]['refOrg'];          
           $record->jobListRef = $this->curOrgJobList;  
           $record->save(); 
        }
   
   }
   /***************************************/
    public function getSavedClientReestrRow ($dataRow)  {



       /*Товары*/

       $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
       $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
       $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";

	   $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $dataRow['refOrg'],])->queryAll();
       $glist="";  for($j=0;$j<count($resList);$j++) {  $glist.= $resList[$j]['good']." | ";  }

    $list = array
        (



        iconv("UTF-8", "Windows-1251",$dataRow['orgTitle']),
        iconv("UTF-8", "Windows-1251",$dataRow['schetINN']),
        iconv("UTF-8", "Windows-1251",$dataRow['managerFIO']),
        iconv("UTF-8", "Windows-1251",$dataRow['mainActivity']),
        iconv("UTF-8", "Windows-1251",preg_replace("/\<br\>/"," | ",$dataRow['otherActivity'])),


        iconv("UTF-8", "Windows-1251",$dataRow['avgCheck']),
        iconv("UTF-8", "Windows-1251",$dataRow['regular']),
        iconv("UTF-8", "Windows-1251",$dataRow['period']),
        iconv("UTF-8", "Windows-1251",number_format($dataRow['plan'], 2, '.', ' ')),
        iconv("UTF-8", "Windows-1251",number_format($dataRow['fact'], 2, '.', ' ')),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['periodStart']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSupply']))),
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastOplate']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSchet']))),
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastSdelka']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastZakaz']))),
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastContact']))),

        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastActiveZakaz']))),
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastActiveSchet']))),

        iconv("UTF-8", "Windows-1251",$glist),

        iconv("UTF-8", "Windows-1251",$dataRow['balance']),

        iconv("UTF-8", "Windows-1251",$dataRow['contactPhone']),
        iconv("UTF-8", "Windows-1251",$dataRow['contactEmail']),

        iconv("UTF-8", "Windows-1251",$dataRow['city']),
        iconv("UTF-8", "Windows-1251",$dataRow['district']),
        iconv("UTF-8", "Windows-1251",$dataRow['adress']),

        iconv("UTF-8", "Windows-1251",$dataRow['categoryTitle']),

        );

  return $list;

   }

 /***********************************************************************/

 /*****************************/ 

   public function  getOrgCategoryProvider($params)		
   {

     $query  = new Query();
     $countquery  = new Query();
   
     $query->select([ 
        '{{%org_category}}.id', 
        '{{%org_category}}.catTitle',
       ])        
		->from("{{%org_category}}")
		;

    $countquery ->select("COUNT(DISTINCT({{%org_category}}.id))" )
       ->from("{{%org_category}}")
		;
               
   
    if (($this->load($params) && $this->validate())) {     
    }

    $this->command = $query->createCommand();    
    $this->count = $countquery->createCommand()->queryScalar();    
       
        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'id',             
            'catTitle', 
            ],
            'defaultOrder' => ['id' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   
 

 /*****************************/
  public function  chngFilter($setType, $setId, $setValue)
  {

  if(empty($setId) || empty($setType)) return false;

//   print_r($setType);

    $this->parseFilter();
    switch($setType)
    {

        case 'markAll':
        $this->grpSet(Yii::$app->request->get());    
        break;
    
        
        
        case 'fltForm':
        $this->fltForm= $setValue;
        break;
        
        case 'cngManagerFilter':
        $this->fltManager = $setValue;
        break;

        case 'cngOrgFilter':
        $this->orgFilter = $setValue;
        break;
        case 'wareType':
        $N=count($this->wareTypeArray);
        $f=false;
        $this->wareTypes="";
        for ($i=0;$i<$N;$i++)
        {
            if($this->wareTypeArray[$i] == $setId)
            {
               $f = true;
               if($setValue==0) continue;
            }
            $this->wareTypes .= $this->wareTypeArray[$i].",";
        }
        $this->wareTypes = substr($this->wareTypes, 0, -1);
        if(!$f) {
        if(!empty($this->wareTypes))$this->wareTypes .= ",";
        $this->wareTypes .= $setId;
        }
        break;
        
       case 'wareGrp':
        $N=count($this->wareGrpArray);
        $f=false;
        $this->wareGrp="";
        for ($i=0;$i<$N;$i++)
        {
            if($this->wareGrpArray[$i] == $setId)
            {
               $f = true;
               if($setValue==0) continue;
            }
            $this->wareGrp .= $this->wareGrpArray[$i].",";
        }
        $this->wareGrp = substr($this->wareGrp, 0, -1);
        if(!$f) {
        if(!empty($this->wareGrp))$this->wareGrp .= ",";
        $this->wareGrp .= $setId;
        }
        break;
        
        case 'grpAll':
        if (!empty($this->wareGrp)){$this->wareGrp="";return true;}
        
        $this->wareGrp="";
        $list = Yii::$app->db->createCommand('Select DISTINCT id from {{%ware_grp}} where wareTypeRef IN ('.$this->wareTypes.')')
                    ->queryAll(); 
        $N=count($list);        
        for ($i=0;$i<$N;$i++)
        {
            $this->wareGrp .= $list[$i]['id'].",";
        }
        $this->wareGrp = substr($this->wareGrp, 0, -1);
        break;
        
        case 'chngList':
        $this->curOrgJobList=$setId;
        
        break;
    }



   /*$listStatus = Yii::$app->db->createCommand('Select DISTINCT {{%user}}.id, userFIO from {{%user}},{{%contact}}
   WHERE {{%user}}.id = {{%contact}}.ref_user and  {{%contact}}.eventType >=10 AND  {{%contact}}.eventType < 100')
                    ->queryAll();
   return  ArrayHelper::map($listStatus, 'id', 'userFIO');      */
//}

    return true;
  }


   public function  parseFilter(){

    $this->wareTypeArray = str_getcsv($this->wareTypes, ",");
    $this->wareGrpArray = str_getcsv($this->wareGrp, ",");
   }
 /********************/
   public function  getWareTypeProvider($params)
   {

     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        'id',
        'wareTypeName',
       ])
		->from("{{%ware_type}}")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_type}}.id))" )
      ->from("{{%ware_type}}")
		;


    if (($this->load($params) && $this->validate())) {
    }

    $this->command = $query->createCommand();
    $this->count = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,

            'pagination' => [
            'pageSize' => 30,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'wareTypeName',
            ],
        //    'defaultOrder' => ['wareTypeRef' => SORT_ASC ],
            ],
        ]);

    return $provider;
   }



   public function  getWareGrpProvider($params)
   {

     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%ware_grp}}.id',
        'wareGrpTitle',
        'wareTypeRef',
        'wareTypeName',
       ])
		->from("{{%ware_grp}}")
		->leftJoin("{{%ware_type}}","{{%ware_grp}}.wareTypeRef = {{%ware_type}}.id")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_grp}}.id))" )
      ->from("{{%ware_grp}}")
      ->leftJoin("{{%ware_type}}","{{%ware_grp}}.wareTypeRef = {{%ware_type}}.id")
		;

	if(!empty($this->wareTypes))	{
         $query->andWhere("wareTypeRef in (".$this->wareTypes.")");
    $countquery->andWhere("wareTypeRef in (".$this->wareTypes.")");
    }
    else {
             $query->andWhere("wareTypeRef = 0");
    $countquery->andWhere("wareTypeRef =0");

    }


    if (($this->load($params) && $this->validate())) {
    }



    $this->command = $query->createCommand();
    $this->count = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,

            'pagination' => [
            'pageSize' => 30,
            ],
            'sort' => [
            'attributes' => [
            'id',
            'wareTypeName',
            ],
        //    'defaultOrder' => ['wareTypeRef' => SORT_ASC ],
            ],
        ]);

    return $provider;
   }
/**************************************************/


/**
 * Provider for nomenklatures in clients deals 
 * @param  $orgRef client id in {{%orglist}}
 * @return provider
 * @throws Exception none
 */    
   public function  getWareOrgProvider($orgRef)
   {

   $orgRef = intval($orgRef);
   
     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%ware_list}}.id',
        '{{%zakazContent}}.good as wareTitle',
        'wareGrpTitle',
        'wareTypeName',
        'max(count) as wareCountMax',
        'avg(count) as wareCountAvg',
        'max(count*value) as wareValMax',
        'avg(count*value) as wareValAvg',        
       ])
		->from("{{%ware_list}}")		
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_list}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_list}}.grpRef")
        ->leftJoin("{{%zakazContent}}","{{%zakazContent}}.wareListRef = {{%ware_list}}.id")
        ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakazContent}}.refZakaz")
        ->groupBy("{{%ware_list}}.id")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_list}}.id))" )
		->from("{{%ware_list}}")		
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_list}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_list}}.grpRef")
        ->leftJoin("{{%zakazContent}}","{{%zakazContent}}.wareListRef = {{%ware_list}}.id")
        ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakazContent}}.refZakaz")
		;

         $query->andWhere("{{%zakazContent}}.isActive = 1");
    $countquery->andWhere("{{%zakazContent}}.isActive = 1");
		
		
         $query->andWhere("{{%schet}}.refOrg = ".$orgRef);
    $countquery->andWhere("{{%schet}}.refOrg = ".$orgRef);
  
 //  $this->debug[] = $query->createCommand()->getRawSql();
    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 5,
            ],
          'sort' => [
            'attributes' => [
            'wareTitle',
            'wareGrpTitle',
            'wareTypeName',
            'wareCountMax',
            'wareCountAvg',
            'wareValMax',
            'wareValAvg',                 
            ],
            'defaultOrder' => ['wareValMax' => SORT_DESC ],
          ],
        ]);

    return $provider;
   }
/*******************************************************/

/**
 * Provider for ware in sales (наименования реализации) in clients deals 
 * @param  
 *  $orgRef client id in {{%orglist}}
 *  $typeRefList filter type values f.e. 2,3,5
 *  $groupRefList filter groups value f.e. 2,13,15
 * @return provider
 * @throws Exception none
 */    
   public function  getRealizeWareOrgProvider($orgRef, $typeRefList, $groupRefList)
   {

   $orgRef = intval($orgRef);
   
     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%ware_names}}.id',
        '{{%zakazContent}}.good as wareTitle',
        'wareGrpTitle',
        'wareTypeName',
       ])
		->from("{{%ware_names}}")		
        ->leftJoin("{{%zakazContent}}","{{%zakazContent}}.wareNameRef = {{%ware_names}}.id")
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_names}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_names}}.wareGrpRef")
        ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakazContent}}.refZakaz")
        ->distinct()
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_names}}.id))" )
		->from("{{%ware_names}}")		
        ->leftJoin("{{%zakazContent}}","{{%zakazContent}}.wareNameRef = {{%ware_names}}.id")
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_names}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_names}}.wareGrpRef")
        ->leftJoin("{{%schet}}","{{%schet}}.refZakaz = {{%zakazContent}}.refZakaz")
		;

         $query->andWhere("{{%zakazContent}}.isActive = 1");
    $countquery->andWhere("{{%zakazContent}}.isActive = 1");
		

         $query->andWhere("{{%ware_names}}.wareTypeRef IN (".$typeRefList.") ");
    $countquery->andWhere("{{%ware_names}}.wareTypeRef IN (".$typeRefList.") ");
	
	if(!empty($groupRefList) )	
	{
         $query->andWhere("{{%ware_names}}.wareGrpRef IN (".$groupRefList.") ");
    $countquery->andWhere("{{%ware_names}}.wareGrpRef IN (".$groupRefList.") ");
	}
				
         $query->andWhere("{{%schet}}.refOrg = ".$orgRef);
    $countquery->andWhere("{{%schet}}.refOrg = ".$orgRef);
  
   
 //  $this->debug[] = $query->createCommand()->getRawSql();
    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();
$this->count = $count;
        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 5,
            ],
          'sort' => [
            'attributes' => [
            'id',
            'wareTitle',
            'wareGrpTitle',
            'wareTypeName',               
            ],
            'defaultOrder' => ['id' => SORT_DESC ],
          ],
        ]);

    return $provider;
   }
/*******************************************************/   
   
   
/***/
public function  getWareFilterProvider($params,$mode)
   {

   
     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%ware_list}}.id',
        'wareTitle',
        'wareGrpTitle',
        'wareTypeName',
       ])
		->from("{{%ware_list}}")		
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_list}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_list}}.grpRef")
		;

    $countquery ->select("COUNT(DISTINCT({{%ware_list}}.id))" )
		->from("{{%ware_list}}")		
		->leftJoin("{{%ware_type}}","{{%ware_type}}.id = {{%ware_list}}.wareTypeRef")
        ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id = {{%ware_list}}.grpRef")
		;

	if ($mode == 0 )	
	{
	if ( !empty($this->wareTypes) )	
	{
         $query->andWhere("{{%ware_list}}.wareTypeRef in (".$this->wareTypes.")");
    $countquery->andWhere("{{%ware_list}}.wareTypeRef in (".$this->wareTypes.")");
    }
		
	if ( !empty($this->wareGrp) )	
	{
         $query->andWhere("{{%ware_list}}.grpRef IN (".$this->wareGrp.")");
    $countquery->andWhere("{{%ware_list}}.grpRef IN (".$this->wareGrp.")");
	}
	}
	
	
	if ($mode == 1 )
	{
	if (empty($this->wareList) )$this->wareList="0";	
         $query->andWhere("{{%ware_list}}.id IN (".$this->wareList.")");
    $countquery->andWhere("{{%ware_list}}.id IN (".$this->wareList.")");
	}
		
		
    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 5,
            ],
          'sort' => [
            'attributes' => [
            'id',
            'wareTitle',
            'wareGrpTitle',
            'wareTypeName',
            ],
            'defaultOrder' => ['wareTitle' => SORT_ASC ],
          ],
        ]);

    return $provider;
   }

   /*****************/
   
  public function  getOrgJobListReestrProvider($params)
   {
   
     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        'id',
        'jobTitle',
        'jobNote',
        'dateCreation',
        'isActive',
       ])
		->from("{{%org_job}}")		
		;

    $countquery ->select("COUNT(DISTINCT({{%org_job}}.id))" )
		->from("{{%org_job}}")		
		;

    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 15,
            ],
          'sort' => [
            'attributes' => [
            'id',
            'jobTitle',
            'jobNote',
            'dateCreation',
            'isActive',
            ],
            'defaultOrder' => ['id' => SORT_ASC ],
          ],
        ]);

    return $provider;
   }

/******/

  public function  getOrgJobListProvider($params)
   {
   
     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%org_job_lnk}}.id',
        'orgRef',
        'jobListRef',
        'title',
        'shortComment',
       ])
		->from("{{%org_job_lnk}}")		
        ->leftJoin("{{%orglist}}","{{%orglist}}.id={{%org_job_lnk}}.orgRef")				
		;

    $countquery ->select("COUNT(DISTINCT({{%org_job_lnk}}.id))" )
		->from("{{%org_job_lnk}}")
		->leftJoin("{{%orglist}}","{{%orglist}}.id={{%org_job_lnk}}.orgRef")				
		;

		$this->curOrgJobList = intval($this->curOrgJobList);
         $query->andWhere("{{%org_job_lnk}}.jobListRef = ".$this->curOrgJobList);
    $countquery->andWhere("{{%org_job_lnk}}.jobListRef = ".$this->curOrgJobList);
		
		
    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 15,
            ],
          'sort' => [
            'attributes' => [
            'id',
            'jobTitle',
            'jobNote',
            'dateCreation',
            'isActive',
            ],
            'defaultOrder' => ['id' => SORT_ASC ],
          ],
        ]);

    return $provider;
   }

/*******************************/

  public function  getOrgWareSupplyProvider($refOrg, $refWare)
   {

     $query  = new Query();
     $countquery  = new Query();

     $query->select([
        '{{%supply}}.id',
        'schetNum',
        'schetDate',
        'orgTitle',
        'supplyDate',
        'supplySumm',
        'supplyGood',
        'supplyCount',
        'supplyEd',
        'supplyNum',
        'ref1C'
       ])
		->from("{{%supply}}")
        ->leftJoin("{{%ware_names}}","{{%ware_names}}.id={{%supply}}.wareNameRef")
        ->distinct()
		;

    $countquery ->select("COUNT(DISTINCT({{%supply}}.id))" )
		->from("{{%supply}}")
        ->leftJoin("{{%ware_names}}","{{%ware_names}}.id={{%supply}}.wareNameRef")
		;

    if(!empty($refOrg))
    {
         $query->andWhere(['=',"{{%supply}}.refOrg",$refOrg]);
    $countquery->andWhere(['=',"{{%supply}}.refOrg",$refOrg]);
    }

    if(!empty($refWare))
    {
         $query->andWhere(['=',"{{%ware_names}}.wareListRef",$refWare]);
    $countquery->andWhere(['=',"{{%ware_names}}.wareListRef",$refWare]);
    }



    $command = $query->createCommand();
    $count   = $countquery->createCommand()->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => $command->sql,
            'params' => $command->params,
            'totalCount' => $count,
          'pagination' => [
            'pageSize' => 15,
            ],
          'sort' => [
            'attributes' => [
            'id',
            'schetNum',
            'schetDate',
            'orgTitle',
            'supplyDate',
            'supplySumm',
            'supplyGood',
            'supplyCount',
            'supplyEd',
            'supplyNum',
            'ref1C'
            ],
            'defaultOrder' => ['id' => SORT_DESC ],
          ],
        ]);

    return $provider;
   }



   /** end of object **/     
 }
