<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;
use app\modules\tasks\models\TaskTiming;
/**
 * BankExtractLog - Детализация исполнения протокола
 */
 
 class BankExtractLog extends Model
{
    
    public $debug;
    public $timeshift = 4*3600; //сдвиг по времени   
      
    public $reportMonth = 0;
    public $reportYear = 0;

    /* границы периодов */
    public $borderArray = array();
  
            
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['orgTitle','debetOrgTitle'], 'safe'],            
        ];
    }
  
   /*********/
   function __construct() {
       /*Вызовем родительский*/
        parent::__construct();
       
       /*Получим тайминги*/          
       $this->borderArray = TaskTiming::getTiming(2);
   }
    
    /***********************/
public function resetReportDataTime()
{
     if (empty($this->reportMonth))$this->reportMonth = date("n"); 
     if (empty($this->reportYear))$this->reportYear = date("Y");

}
        
public function getLogCount($Month, $Year)
{


    $startDate = $Year."-".$Month."-"."01 00:00";
    $endDate   = $Year."-".$Month."-".date('t',strtotime($startDate))." 00:00";

    $strsql = "SELECT count(id) from {{%log}}      
     where actionType = 10 and actionDateTime >= '".$startDate."' and actionDateTime <= '".$endDate."'"; 
     
/*echo   Yii::$app->db->createCommand($strsql)->getRawSql(); 
echo "<br>";  */
     return Yii::$app->db->createCommand($strsql)->queryScalar();    
}

    
public function getLogData()
 {
     $retArray=array();
 
     if (empty($this->reportMonth))$this->reportMonth = date("n"); 
     if (empty($this->reportYear))$this->reportYear = date("Y");
 
     $startDate = $this->reportYear."-".$this->reportMonth."-"."01 00:00";
     $endDate   = $this->reportYear."-".$this->reportMonth."-".date('t',strtotime($startDate))." 23:00";

     $startTime = strtotime($startDate); 
 
     $executeList=array();
         
     $nD = date("t", $startTime ); 
     for($i=1; $i<=$nD; $i++)
     {
       $executeList[$i]['date']=$this->reportYear."-".$this->reportMonth."-".$i;
       $executeList[$i]['dateTime'] = strtotime($executeList[$i]['date']);  
       
       //инит
       

       //загрузка
       for ($j=0; $j< $this->borderArray['load']['n'];$j++)
       {
           $executeList[$i]['load'][$j]['v']= -1; //значение          
           $executeList[$i]['load'][$j]['s']= strtotime($executeList[$i]['date']." ".$this->borderArray['load'][$j][0]);
           $executeList[$i]['load'][$j]['w']= strtotime($executeList[$i]['date']." ".$this->borderArray['load'][$j][1]);
           $executeList[$i]['load'][$j]['e']= strtotime($executeList[$i]['date']." ".$this->borderArray['load'][$j][2]);
       }

       for ($j=0; $j< $this->borderArray['sync']['n'];$j++)
       {
           $executeList[$i]['sync'][$j]['v']= -1; //значение          
           $executeList[$i]['sync'][$j]['s']= strtotime($executeList[$i]['date']." ".$this->borderArray['sync'][$j][0]);
           $executeList[$i]['sync'][$j]['w']= strtotime($executeList[$i]['date']." ".$this->borderArray['sync'][$j][1]);
           $executeList[$i]['sync'][$j]['e']= strtotime($executeList[$i]['date']." ".$this->borderArray['sync'][$j][2]);
       }

       for ($j=0; $j< $this->borderArray['sync']['n'];$j++)
       {
           $executeList[$i]['check'][$j]['v']= -1; //значение          
           $executeList[$i]['check'][$j]['s']= strtotime($executeList[$i]['date']." ".$this->borderArray['check'][$j][0]);
           $executeList[$i]['check'][$j]['w']= strtotime($executeList[$i]['date']." ".$this->borderArray['check'][$j][1]);
           $executeList[$i]['check'][$j]['e']= strtotime($executeList[$i]['date']." ".$this->borderArray['check'][$j][2]);
       }

       for ($j=0; $j< $this->borderArray['login']['n'];$j++)
       {
           $executeList[$i]['login'][$j]['v']= -1; //значение          
           $executeList[$i]['login'][$j]['s']= strtotime($executeList[$i]['date']." ".$this->borderArray['login'][$j][0])+$this->timeshift;
           $executeList[$i]['login'][$j]['w']= strtotime($executeList[$i]['date']." ".$this->borderArray['login'][$j][1])+$this->timeshift;
           $executeList[$i]['login'][$j]['e']= strtotime($executeList[$i]['date']." ".$this->borderArray['login'][$j][2])+$this->timeshift;
       }
       
       
     }
 
     $strsql = "SELECT refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = 1 and actionDateTime >= '".$startDate."' and actionDateTime <= '".$endDate."' AND refUser= 37 ORDER BY actionDateTime"; 
     $listLogin = Yii::$app->db->createCommand($strsql)->queryAll();    
     $nL = count($listLogin);
     for($i=0; $i<$nL; $i++)
     {
       /* переведем дату/время события в анализируемое время  */       
       $curTime= strtotime($listLogin[$i]['actionDateTime'])+$this->timeshift;     
       /* анализируемый день  */       
       $d= date("j",$curTime);  
       for ($j=0; $j< $this->borderArray['login']['n'];$j++)
       {
         if ($curTime >= $executeList[$d]['login'][$j]['s'] && $curTime < $executeList[$d]['login'][$j]['e'])  
         {
           /*Проставим отметку о выполнении - как ссылку на массив данных $listLoad*/
           $executeList[$d]['login'][$j]['v']= $i; //    
           break;  
         }           
       }
     }
 
 
 

 

     /*загрузка выписки*/ 
     $strsql = "SELECT  refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = 10 and actionDateTime >= '".$startDate."' and actionDateTime <= '".$endDate."' ORDER BY actionDateTime"; 
     $listLoad = Yii::$app->db->createCommand($strsql)->queryAll();    

     $nL = count($listLoad);
     for($i=0; $i<$nL; $i++)
     {
       /* переведем дату/время события в анализируемое время  */       
       $curTime= strtotime($listLoad[$i]['actionDateTime'])+$this->timeshift;     
       $listLoad[$i]['actionTime'] = $curTime;
       /* анализируемый день  */       
       $d= date("j",$curTime);  
       for ($j=0; $j< $this->borderArray['load']['n'];$j++)
       {
         if ($curTime >= $executeList[$d]['load'][$j]['s'] && $curTime < $executeList[$d]['load'][$j]['e'])  
         {
           /*Проставим отметку о выполнении - как ссылку на массив данных $listLoad*/
           $executeList[$d]['load'][$j]['v']= $i; //    
           break;  
         }           
       }
     }
 
       
     /*Синхронизация*/ 
     $strsql = "SELECT refUser, actionDateTime, userFIO from {{%log}} 
     left join {{%user}} on {{%user}}.id = {{%log}}.refUser
     where actionType = 11 and actionDateTime >= '".$startDate."' and actionDateTime <= '".$endDate."' ORDER BY actionDateTime"; 
     $listSync = Yii::$app->db->createCommand($strsql)->queryAll();    
       
     /*загрузка выписки*/ 
     $nL = count($listSync);
     for($i=0; $i<$nL; $i++)
     {
       /* переведем дату/время события в анализируемое время  */       
       $curTime= strtotime($listSync[$i]['actionDateTime'])+$this->timeshift;     
       /* анализируемый день  */       
       $d= date("j",$curTime);  
       $executeList[$d]['sync']['find']=$curTime;
       for ($j=0; $j< $this->borderArray['sync']['n'];$j++)
       {
         if ($curTime >= $executeList[$d]['sync'][$j]['s'] && $curTime < $executeList[$d]['sync'][$j]['e'])  
         {
           /*Проставим отметку о выполнении - как ссылку на массив данных $listSync*/
           $executeList[$d]['sync'][$j]['v']= $i; //    
           break;  
         }           
       }
     }


     
     
     $retArray['nd']=$nD;
     $retArray['list']['load']=$listLoad;
     $retArray['list']['sync']=$listSync;
     $retArray['list']['login']=$listLogin;
     $retArray['execute']=$executeList;
     
     return $retArray;
 }    

      
  
  /************End of model*******************/ 
 }
