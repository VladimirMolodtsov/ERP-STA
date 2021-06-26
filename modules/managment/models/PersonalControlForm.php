<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 


/**
 */


class PersonalControlForm extends Model
{

    public $id=0;
    

    public $debug=[];   
    public $err=[];   
     
    public $dataRequestId="";
    public $dataRowId="";
    public $dataType="";
    public $dataVal ="";
    public $dataValType =0;
    
    public $stDate="";
    public $enDate="";
        
    public function rules()
    {
        return [
              [[ 'dataRequestId', 'dataRowId', 'dataType', 'dataVal', 'dataValType'], 'default'],
              [[ ], 'safe'],
        ];
    }
    
/******************************************/  
/*
*/
  public function getBuhStatAcivityProvider($params)  
   {
                         
    $dataArray=array();
    $idxArray=array();

    $stTime = strtotime($this->stDate);
    $enTime = strtotime($this->enDate);
         
    $i=0;
    for ($cn=$stTime; $cn<=$enTime; $cn+=24*3600)
    {
        
        $dataArray[$i]['cn'] = $cn; //текущий день
        $dataArray[$i]['checkDate'] = date('Y-m-d', $cn);
        $dataArray[$i]['id'] = 0;
        $dataArray[$i]['auto'] = 0;
        $dataArray[$i]['manual'] = 0;        
        $dataArray[$i]['isFinished'] = 0;
        $dataArray[$i]['syncDateTime'] = null;
        $dataArray[$i]['isChecked'] = 0;
        $dataArray[$i]['isSynced'] = 0;
        
        $idxArray[$dataArray[$i]['checkDate']]=$i;
        $i++;
    }        
    $count =$i;         

    $query  = new Query();    
    $query->select ([ 'id',
                      'syncDateTime',  
                      'checkDate',
                      'finishDateTme',                      
                      'isFinished',                      
                      'isChecked', 
                      'isSynced',                      
                      ])
            ->from("{{%buh_stat_header}}")            
            ->orderBy("id DESC")  //свежие в начало
            ->distinct()
            ;
   $query->andWhere("checkDate >= :stDate");
   $query->andWhere("checkDate <= :enDate");
  
     
   
   $query->addParams([':stDate' => $this->stDate]);
   $query->addParams([':enDate' => $this->enDate]);
   
   $list = $query->createCommand()->queryAll();
   
   for ($i=0;$i<count($list); $i++)
   {
     $cDate=$list[$i]['checkDate'];  
     if(!isset($idxArray[$cDate])) continue; 
     $idx=$idxArray[$cDate];
     if($dataArray[$idx]['id'] !=0) continue; //есть более свежая запись  
     $dataArray[$idx]['id'] = $list[$i]['id'];  
     $dataArray[$idx]['syncDateTime'] = $list[$i]['syncDateTime'];
     $dataArray[$idx]['isFinished'] = $list[$i]['isFinished'];
     $dataArray[$idx]['isChecked'] = $list[$i]['isChecked'];
     $dataArray[$idx]['isSynced'] = $list[$i]['isSynced'];
   }
      
    $queryData  = new Query();    
    $queryData->select ([ 
                      'sum(auto) as auto',  
                      'sum(val)  as manual',
                      'checkdate',                      
                      ])
            ->from("{{%buh_statistics}}")            
            ->groupBy("checkdate")
            ;
   $queryData->andWhere("checkdate >= :stDate");
   $queryData->andWhere("checkdate <= :enDate");
  
     
   
   $queryData->addParams([':stDate' => $this->stDate]);
   $queryData->addParams([':enDate' => $this->enDate]);
   
   $list = $queryData->createCommand()->queryAll();
   
   for ($i=0;$i<count($list); $i++)
   {
     $cDate=$list[$i]['checkdate'];  
     if(!isset($idxArray[$cDate])) continue; 
     $idx=$idxArray[$cDate];
     if ($list[$i]['auto'] > 0 ) $dataArray[$idx]['auto'] = 1;
     if ($list[$i]['manual'] > 0 ) $dataArray[$idx]['manual'] = 1;
   }
   
   $dataProvider = new ArrayDataProvider([
            'allModels' => $dataArray,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 31,
            ],
            'sort' => [
            'attributes' => [
			'cn', 
            'checkDate',
            'isFinished',
            'syncDateTime',
            'isChecked',
            'isSynced',
			'manual', 
			'auto' 
            ],
			
            'defaultOrder' => [    'cn' => SORT_ASC ],
            ],
        ]);
    return  $dataProvider;   
   }   



  
}
 
