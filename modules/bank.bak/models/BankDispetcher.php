<?php

namespace app\modules\bank\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblBankHeader;
use app\modules\bank\models\TblBankContent;

use SimpleXLSX;
/**
 * BankExtract - модель работы с выписками из банка
 */
 
 class BankDispetcher extends Model
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
     for ($i=0; $i<48; $i++ )
     {
         foreach ($this->borderArray['keys'] as $val) $execute[$val][$i]=0;
     }

    
    if ($this->showDate == 0)   $this->showDate = strtotime(date("Y-m-d 00:00:00"));
        
    /*   load   */                
    if (in_array('load', $this->borderArray['keys']))
    {
      $strsql = "SELECT {{%bank_header}}.id, uploadTime from {{%bank_header}} where DATE(uploadTime) = :showDate ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strsql) ->bindValue(':showDate', date("Y-m-d", $this->showDate))->queryAll();                          
      for ($i=0; $i < count($list); $i++){           
       $shift = strtotime($list[$i]['uploadTime'])-$this->showDate;
       $shift= intval($shift/1800)+8;// в половинках часа    
       $execute['load'][$shift] = $list[$i]['id']; // сделано в это период                   
      }   
    }    

    /*sync*/                
    if (in_array('sync', $this->borderArray['keys']))
    { 
      $strsql = "SELECT {{%bank_op_header}}.id, syncDateTime from {{%bank_op_header}} where DATE(syncDateTime) = :showDate ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strsql) ->bindValue(':showDate', date("Y-m-d", $this->showDate))->queryAll();                
      for ($i=0; $i < count($list); $i++){           
       $shift = strtotime($list[$i]['syncDateTime'])-$this->showDate;
       $shift= intval($shift/1800)+8;// в половинках часа    
       $execute['sync'][$shift] = $list[$i]['id']; // сделано в это период                   
     }   
    }

    /*check*/                
    if (in_array('check', $this->borderArray['keys']))
    {    
      $strsql = "SELECT {{%bank_check}}.id, checkDateTime from {{%bank_check}} where DATE(checkDateTime) = :showDate ORDER BY id"; 
      $list = Yii::$app->db->createCommand($strsql) ->bindValue(':showDate', date("Y-m-d", $this->showDate))->queryAll();                    
// echo Yii::$app->db->createCommand($strsql) ->bindValue(':showDate', date("Y-m-d", $this->showDate))->getRawSql();                    
      for ($i=0; $i < count($list); $i++){           
        $shift = strtotime($list[$i]['checkDateTime'])-$this->showDate;
        $shift= intval($shift/1800)+8;// в половинках часа    
        $execute['check'][$shift] = $list[$i]['id']; // сделано в это период                   
      }   
    }
    
      return $execute;
    }    
     
    /*Получаем исполнение*/ 
    public function loadTaskList()
    {
    
     for ($i=0; $i<48; $i++ )
     {
         $this->taskArray[$i]=0;   
         $this->nameArray[$i]="";         
     }

    
    $execute = $this->prepareExecute();
    
//echo "<pre>";
//print_r($execute);
    
    /* fill by empty*/
    
    /*определим текущий получас */    
    $shift = time()-$this->showDate;
    $curpos= intval($shift/1800)+8;// в половинках часа

            
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
   
//echo "$val $s $e $status \n";
   
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
        
//echo "</pre>";    
    }
    
   


  
  
  /************End of model*******************/ 
 }
