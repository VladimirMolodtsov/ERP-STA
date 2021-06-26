<?php

namespace app\modules\zadarma\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


/**
 * ColdMainForm - модель стартовой формы менеджера холодных звонков
 */
 
 class ZadarmaStatForm extends Model
{
    

    public $debug;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

  /**************************/
  public function getStatistics($strDate)
  {
     $date=date("Y-m-d", strtotime($strDate));
      
     $list =  Yii::$app->db->createCommand("SELECT COUNT(DISTINCT(rik_ats_log.pbx_call_id)) as N, managerRef, userFIO, HOUR(call_start) as H 
     from {{%ats_log}} left join rik_user on {{%user}}.id = {{%ats_log}}.managerRef
     where DATE(call_start) =:date and duration > 7 and (event='NOTIFY_OUT_END' OR event='NOTIFY_END')
     GROUP BY managerRef, HOUR(call_start) ORDER BY managerRef, H " ,     [':date'=>$date,  ])->queryAll();                

    $fio=array();
    $data=array();
    $N=count($list);
    for ($i=0;$i<$N;$i++ )
    {
      $managerRef =$list[$i]['managerRef'];   
      $H = $list[$i]['H'];   
      $fio[$managerRef]=$list[$i]['userFIO'];
      $data[$managerRef][$H]=$list[$i]['N'];        
    }
    $listResult['keys']=array_keys ($fio);
    $listResult['fio']=$fio;
    $listResult['data']=$data;


     return  $listResult;
     
  }


  

  
  /**************************/    
  
  
  /************End of model*******************/ 
 }
