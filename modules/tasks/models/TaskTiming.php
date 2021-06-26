<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/**
 * BankExtractLog - Тайминги протокола
 */
 
 class TaskTiming extends Model
{
    
    public $debug;
    public $timeshift = 4*3600; //сдвиг по времени   
      
    public $reportMonth = 0;
    public $reportYear = 0;
        
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['orgTitle','debetOrgTitle'], 'safe'],            
        ];
    }

static public function getTiming($refModule)
 {
 
   $keys=array();  
   $titles=array();  
   $borderArray = array(); 
 
 /* Настроим границы периодов */
    $strsql = "SELECT id, refModule, taskName, taskTitle, s, w, e from {{%tasks_day}} where refModule=:refModule AND  isActive = 1 ORDER BY id"; 
    $list = Yii::$app->db->createCommand($strsql)->bindValue(':refModule', $refModule) ->queryAll();                          

    $N = count ($list);
    /*посчитаемся*/
    for ($i=0; $i<$N;$i++ )
    {        
       $key=$list[$i]['taskName']; 
       if (!isset ($borderArray[$key]['n'])) $borderArray[$key]['n'] =1;
       else $borderArray[$key]['n']++;
       $borderArray[$key][]=[$list[$i]['s'], $list[$i]['w'], $list[$i]['e']];
       
       if (!in_array($key, $keys)) {$keys[]=$key; $titles[]= $list[$i]['taskTitle']; }   
    }

       $borderArray['keys']=$keys;
       $borderArray['titles']=$titles;
 
/*определим сдвиг от начала дня в часах*/ 
$zero=strtotime (date("Y-m-d")." 00:00:00"); //на начало дня 
foreach ($borderArray['keys'] as $val)
for($i=0;$i<$borderArray[$val]['n'];$i++)
{
  for ($j=0; $j<3;$j++)
  {
  $lst = explode(":", $borderArray[$val][$i][$j]);
  $shift=$lst[0]+intval($lst[1])/60;
  $borderArray[$val]['shift'][$i][$j] = $shift;
  }
}
    
  return  $borderArray;
 }    

      
  
  /************End of model*******************/ 
 }
