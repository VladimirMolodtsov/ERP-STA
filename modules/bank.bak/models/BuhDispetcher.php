<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\BuhStatistics;

/**
 * BuhDispetcher - отслеживание обработки платежек
 */
 
 class BuhDispetcher extends Model
{
    
    public $timeshift = 4*3600; //сдвиг по времени   
    
    public $hourDiv = 2; // сколько периодов в часе => на сколько делим рабочий день
    
    public $showDate = 0;
        
        
        
    public $taskArray = [];
    public $nameArray = [];
    public $shedulArray = [];                
    public $borderArray= [];    
    
    public function rules()
    {
        return [                              
            //[[ ], 'default'],                        
            [['userFIO'], 'safe'],            
        ];
    }

    /***** Готовим массив с исполнением ****/
    public function prepareExecute()   
    {
//    echo "<pre>";
        //$execute = array();    
    $statModel = new BuhStatistics();
    if ($this->showDate == 0)   $this->showDate = strtotime(date("Y-m-d 00:00:00"));
    

    $checkDate=$this->showDate-24*3600;
    
    $dw = date('w', $checkDate);
    if ($dw == 0 ) $checkDate-=2*24*3600;
    elseif ($dw == 6 ) $checkDate-=24*3600;
    
    
    $strDate = date("Y-m-d",$this->showDate);

    //echo $strDate;
        
    $executeArray = $statModel->getExecute ($checkDate, $this->showDate);
    sort($executeArray); // заодно с нуля 
    $eN = count($executeArray);
    $pC = 24*$this->hourDiv; //Общее число периодов      
    /*Раскидываем по периодам - инициация*/    
     for ($i=0; $i<$pC; $i++ )
     {
       $h=intval($i/$this->hourDiv);
       $m=($this->hourDiv*30)*(($i/$this->hourDiv)-$h);  
       
//       echo $strDate." ".$h.":".$m.":00\n";
       
       $shedule[$i]['stTime'] = strtotime($strDate." ".$h.":".$m.":00");
       $shedule[$i]['enTime'] = $shedule[$i]['stTime']+(60/$this->hourDiv)*60;
       $shedule[$i]['nGrp']=0; //число глобальных задач (групп задач) приходящихся на период
       $shedule[$i]['nameList']=[];
       $shedule[$i]['nameList']=[];
       $shedule[$i]['execute']= 
                  [
                   'plan' => 0,  // Запланировано
                   'exec' => 0,  // Выполнено
                   'good' => 0   // Выполнено вовремя 
                  ]; 
       $shedule[$i]['detail']=[];             
     }
    
//   print_r  ($shedule);
    
    
    
    /*Перебираем все запланированные события в этой ветке и собираем их в один линейный массив с индексацией по получасу*/        
    /*   reestr*/                
    
    for ($k=0; $k<count($this->borderArray['keys']); $k++)
    {
       $key = $this->borderArray['keys'][$k];
       $planArray=$this->borderArray[$key];
        
       $titleArray=$this->borderArray['titles'];
       $N = $planArray['n'];
       for ($i=0;$i<$N;$i++) /* Для всех периодов этой задачи */
       {
         
         
         $posS = intval($this->hourDiv*$planArray['shift'][$i][0]);
         $posE = intval($this->hourDiv*$planArray['shift'][$i][1])+1;
    
//       echo $titleArray[$k]." $key $posS $posE \n";
    
         for($p=$posS; $p<$posE;$p++)
         {
        
//        print_r($shedule[$p]);
           $shedule[$p]['nGrp']++; //добавим группу
           $shedule[$p]['nameList'][]=$titleArray[$k];//добавим ее имя
           /*Пробегаемся по задачам и метим исполненные и не исполненные*/
           for($j=0; $j<$eN;$j++)
           {
             if($executeArray[$j]['group'] != $key) continue; //не отсюда 

//             echo $shedule[$p]['stTime']. " ".$shedule[$p]['enTime']."\n";              
//             echo date("H:i",$shedule[$p]['stTime']). " ".date("H:i",$shedule[$p]['enTime'])."\n";              
//             print_r ($executeArray[$j]);
             
             if($executeArray[$j]['timeStart']>=$shedule[$p]['stTime'] &&   $executeArray[$j]['timeStart']<$shedule[$p]['enTime'])
             {
               $shedule[$p]['execute']['plan']++; //запланировано!
               $shedule[$p]['detail'][]=$executeArray[$j];
//             echo "add!\n";  
               
               if ($executeArray[$j]['timeReal'] > 0) $shedule[$p]['execute']['exec']++;//выполнено
               if (
               ($executeArray[$j]['timeReal'] >= $executeArray[$j]['timeStart'])
                && 
               ($executeArray[$j]['timeReal'] <= $executeArray[$j]['timeDead'] ) 
               )    $shedule[$p]['execute']['good']++;//выполнено
             
             
              
             }//задача в этом отчетном периоде
                   
           }
             
             
         }
    
    
       }
    } 
    
      return $shedule;
    }    
     
    /*Получаем исполнение*/ 
    public function loadTaskList()
    {
    
    return [];
    /* fill by empty*/
    
    
     for ($i=0; $i<48; $i++ )
     {
         $this->taskArray[$i]=0;   
         $this->nameArray[$i]="";         
         //foreach ($this->borderArray['keys'] as $val) $execute[$val][$i]=0;
     }

     $execute = $this->prepareExecute();
         
    /*определим текущий получас */    
    $shift = time()-$this->showDate;
    $curpos= intval($shift/1800)+8+1;// в половинках часа

                
    //foreach ($this->borderArray['keys']  as $val)
    for($k=0;$k<count($this->borderArray['keys']);$k++)       
    {    
    $val   =  $this->borderArray['keys'][$k];
    $title =  $this->borderArray['titles'][$k];
    for($i=0;$i<$this->borderArray[$val]['n'];$i++)
    {            
      $s=intval(2*$this->borderArray[$val]['shift'][$i][0]);//началось
      $w=intval(2*$this->borderArray[$val]['shift'][$i][1]);//
      $e=intval(2*$this->borderArray[$val]['shift'][$i][2]);//про..ли
    
       $status = 4; // по умолчанию плохо плохо       
       // до начала события
       if($curpos < $s) $status = 1; // ждем начала
       elseif($curpos < $w ) $status = 3; // 
       
       //Ищем было ли выполнение
       for ($j=$s;$j<=$e; $j++){
           if ($execute[$val][$j] > 0)
           {
            $status = 2; // выполнили                      
            break;
           }
       }
   
       for ($j=$s;$j<=$w; $j++)
       {       
           if ($status > $this->taskArray[$j])
           {
           $this->taskArray[$j]=$status;           
           $this->nameArray[$j]=$title;           
           }       
       }
    }
    }
        
        
    }
    
   


  
  
  /************End of model*******************/ 
 }
