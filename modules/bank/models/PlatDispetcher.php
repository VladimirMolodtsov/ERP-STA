<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/**
 * PlatDispetcher - отслеживание обработки платежек
 */
 
 class PlatDispetcher extends Model
{
    
    public $timeshift = 4*3600; //сдвиг по времени   
    public $showDate = 0;
        
    public $taskArray = [];
    public $nameArray = [];
    public $resultArray = [];                
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

    $execute = array();    
    if ($this->showDate == 0)   $this->showDate = strtotime(date("Y-m-d 00:00:00"));
        
    /*   regdoc   */                
   /* if (in_array('regdoc', $this->borderArray['keys']))
    {
      $strsql = "SELECT {{%doc_header}}.id, docDateTime from {{%doc_header}} where DATE(docDateTime) = :showDate ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strsql) ->bindValue(':showDate', date("Y-m-d", $this->showDate))->queryAll();                          
      for ($i=0; $i < count($list); $i++){           
       $shift = strtotime($list[$i]['uploadTime'])-$this->showDate;
       $shift= intval($shift/1800)+8;// в половинках часа    
       $execute['load'][$shift] = $list[$i]['id']; // сделано в это период                   
      }   
    }    
*/
  
      return $execute;
    }    
     
    /*Получаем исполнение*/ 
    public function loadTaskList()
    {
    
    $execute = $this->prepareExecute();
    /* fill by empty*/
     for ($i=0; $i<48; $i++ )
     {
         $this->taskArray[$i]=0;   
         $this->nameArray[$i]="";         
         foreach ($this->borderArray['keys'] as $val) $execute[$val][$i]=0;
     }
    
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
