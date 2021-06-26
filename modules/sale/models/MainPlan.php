<?php

namespace app\modules\market\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


/**
 * MainMain - Планирование - суммарные значения
 */
 
 class MainPlan extends Model
{
    
    public $debug;
    
    public $m = 0; // текущий месяц
    public $y = 0; // текущий год
    
        
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['city', 'orgTitle', 'userFIO' ], 'safe'],            
        ];
    }



  /**************************/
  public function prepareCurrentMonth()
  {
     /*Для простоты считаем начальной неделей ту куда приходится - первая среда месяца*/
     if (empty($this->m))$this->m=date("n");
     if (empty($this->y))$this->y=date("Y");
     
     for ($this->m =1; $this->m<=12; $this->m++)
     {
         $markerday = $this->compute_day(1, 3, $this->m, $this->y);         
         $markertime = mktime(0, 0, 0, $this->m, $markerday, $this->y);
         
         $nextyear  = $this->y;
         $nextmonth = $this->m+1;
         if ($nextmonth == 13) {$nextmonth = 1; $nextyear++;}         
         $nextstart = $this->compute_day(1, 3, $nextmonth, $nextyear);         
         $nexttime = mktime(0, 0, 0,  $nextmonth, $nextstart, $nextyear);         
         
         $startweek[0] = $markertime -2*24*3600;
         $endweek[0]   = $startweek[0]+6*24*3600;
         echo date("D d.m.Y", $startweek[0])." - ".date("D d.m.Y", $endweek[0])."<br>  ";         
         $i=1; 
         while (1)
         {
            $startweek[$i] = $endweek[$i-1]+24*3600;
            $endweek[$i]   = $startweek[$i]+6*24*3600;
            if ($endweek[$i] > $nexttime) break;            
            echo date("D d.m.Y", $startweek[$i])." - ".date("D d.m.Y", $endweek[$i])."<br> ";         
            $i++;
         }
         echo date("D d.m.Y", $nexttime)." $nextstart $nextmonth $nextyear <BR>";         
         echo "<hr>";

         
         
         
     }
     
 
     
     return;
  }
 /**************************/
 public function getTaskTemplateListProvider ($params) 
 {

     $query  = new Query();
     $countquery  = new Query();

     
     $countquery->select ("count(distinct {{%task_template_header}}.id)")
                  ->from("{{%task_template_header}}")                                
                  ->leftJoin('{{%modules}}','{{%task_template_header}}.moduleRef = {{%modules}}.id')                  
                 ;
                  
     $query->select([ 
                    '{{%task_template_header}}.id',
                    'templateTitle',
                    'weekDay',
                    'moduleTitle'
                  ])
                  ->from("{{%task_template_header}}")                                
                  ->leftJoin('{{%modules}}','{{%task_template_header}}.moduleRef = {{%modules}}.id')                  
                  ->distinct();      

                  
     if (($this->load($params) && $this->validate())) 
     {
          
        
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

      

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                    'templateTitle',
                    'weekDay',
                    'moduleTitle'
            ],
            'defaultOrder' => ['moduleTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;     

 
 
    
 }

  
    
    
  /**************************/    
public function compute_day($weekNumber, $dayOfWeek, $monthNumber, $year)
{
    // порядковый номер дня недели первого дня месяца $monthNumber
    $dayOfWeekFirstDayOfMonth = date('w', mktime(0, 0, 0, $monthNumber, 1, $year));
    
    if ($dayOfWeekFirstDayOfMonth <= 0)
    $dayOfWeekFirstDayOfMonth = 7;
 
    // сколько дней осталось до дня недели $dayOfWeek относительно дня недели $dayOfWeekFirstDayOfMonth
    $diference = 0;
 
    // если нужный день недели $dayOfWeek только наступит относительно дня недели $dayOfWeekFirstDayOfMonth
    if ($dayOfWeekFirstDayOfMonth <= $dayOfWeek)
    {
        $diference = $dayOfWeek - $dayOfWeekFirstDayOfMonth;
    }
    // если нужный день недели $dayOfWeek уже прошёл относительно дня недели $dayOfWeekFirstDayOfMonth
    else
    {
        $diference = 7 - $dayOfWeekFirstDayOfMonth + $dayOfWeek;
    }
 
    return 1 + $diference + ($weekNumber - 1) * 7;
}  
  
  /************End of model*******************/ 
 }
