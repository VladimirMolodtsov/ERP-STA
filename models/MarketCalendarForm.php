<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;

use app\models\CalendarList;
use app\models\OrgList;
 
/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class MarketCalendarForm extends Model
{
    
    public $debug;
  
    public $detail = 1;
    public $tab = 1;
    public $mode = 1;
    public $show = 0;
    public $type = 0;
    public $filtDate = "";
    public $fltGood = "";
    public $balance = 0;

    public $strSearch = "";    
    
    public $selectDate = ""; 
    public $newDate = "";  
    public $newUser = "";
    public $newRefContact = "";
    public $newRefEvent = "";
    public $newEventNote = "";
    
    public $title = "";    
    public $eventTitle = "";
    public $eventStatus = 0;
    public $contactDate = "";
    public $contactFIO = "";        
    public $_event_date = "";
    public $refEvent ="";
    
    public $userFIO="";
    public $operator="";
    public $nextContactDate="";

    public $userShow =0;
    public $d=1;
    public $m=1;
    public $y=2018;
    

    public $command;
    public $count;
    
    
    public function rules()
    {
        return [

            [['strSearch'], 'default'],
    //        [['newDate', 'newUser','newRefContact', 'newRefEvent', 'newEventNote' ], 'default'],
            [['title', 'eventTitle', 'eventStatus', 'contactDate', 'nextContactDate', 'contactFIO', '_event_date', 'refEvent', 
            'userFIO', 'operator', 'fltGood', 'balance' ], 'safe'],
    //        [['newEventNote', 'newDate'], 'trim'],
    //        ['newUser', 'integer'],
    //        ['newRefContact', 'integer'],
    //        ['newRefEvent', 'integer'],
    //        ['selectDate', 'date',  'format' => 'php:d.m.Y'],            
    //        ['newDate', 'date',  'format' => 'php:d.m.Y'],            
            
        ];
    }

/* Функция генерации календаря */
/* СПОСОБ ПРИМЕНЕНИЯ 
echo '<h2>Июнь 2012</h2>';
echo draw_calendar(6,2012);
*/    
    
  public function draw_calendar($day, $month, $year){

 $day=intval($day);
 $month=intval($month);
 $year=intval($year);
 
 //&tab=3&detail=1&filtDate=9-11-2017
   $ref = "index.php?r=market/market-start";

 $prev_y = $year;
 $prev_m = $month -1;
 if($prev_m == 0){$prev_m = 12; $prev_y--;}
 
 $next_y = $year;
 $next_m = $month +1;
 if($next_m == 13){$next_m = 1; $next_y++;}
   
   
$prevRef ="<a class='btn btn-primary btncal' href='index.php?r=market/market-start&tab=".$this->tab."&detail=".$this->detail."&mode=3&m=".$prev_m."&y=".$prev_y."'> << </a>&nbsp;&nbsp;&nbsp;&nbsp;";
$nextRef ="&nbsp;&nbsp;&nbsp;&nbsp;<a class='btn btn-primary btncal' href='index.php?r=market/market-start&tab=".$this->tab."&detail=".$this->detail."&mode=3&m=".$next_m."&y=".$next_y."'> >> </a>";
   
   
   if (!empty($this->tab))
   {
    $ref.="&tab=".intval($this->tab);   
   }

   if (!empty($this->detail))
   {
    $ref.="&detail=".intval($this->detail);   
   }
   
   
  echo "
<script>
    function switchDate(filtDate)
    {    
    window.open('".$ref."&mode=3&filtDate='+filtDate,'_parent');
    }
</script> 
    ";
 
//        window.open(\"".$ref.    
    
    
    $curUser=Yii::$app->user->identity;
    $event_list = Yii::$app->db->createCommand(
            'SELECT  day(event_date) as d, eventStatus from {{%calendar}} where ref_user = :ref_user  AND month(event_date)=:month and year(event_date)=:year AND eventStatus=1 ', 
            [        
            ':ref_user' => $curUser->id,
            ':month'    => $month,
            ':year'     => $year,
            ]
            )->queryAll();
    

    $event_array = array();
    for ($i=0; $i<= 31; $i++)    
    {        
        $event_array[$i] =0;
    }

    for ($i=0; $i< count($event_list); $i++)    
    {
        $d = $event_list[$i]['d'];        
        $event_array[$d]++;
    }
    
 
  $monthTitles = array(
    "1" => "январь",
    "2" => "февраль",
    "3" => "март",
    "4" => "апрель",
    "5" => "май",
    "6" => "июнь",
    "7" => "июль",
    "8" => "август",
    "9" => "сентябрь",
    "10" => "октябрь",
    "11" => "ноябрь",
    "12" => "декабрь"); 
 
  $cur_d = date("d");  
  $cur_m = date("m");
  $cur_y = date("Y");
  
  if (empty($this->filtDate)) {      $sel_d = $cur_d;     }
                         else {      $sel_d = date("d", strtotime($this->filtDate));  }
    
  if($cur_m != $month) {$cur_d =0;}
  if($cur_y != $year)  {$cur_d =0;}
  
  /* Начало таблицы */
  $calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';
  $calendar .= '<tr><td colspan="7" class="calendar-month">'.$prevRef.' '.$monthTitles[$month].' '.$year.' '.$nextRef.'</td></tr>'; 
  /* Заглавия в таблице */
  //$headings = array('Понедельник','Вторник','Среда','Четверг','Пятница','Субота','Воскресенье');
  $headings = array('Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вск.');
  $calendar.= '<tr class="header-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';
  /* необходимые переменные дней и недель... */
  //$running_day = date('w',mktime(0,0,0,$month,1,$year));
  //$running_day = $running_day - 1;
  $running_day = date('N',mktime(0,0,0,$month,1,$year));
  
  $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
  $days_in_this_week = 1;
  $day_counter = 0;
  $dates_array = array();
  /* первая строка календаря */
  $calendar.= '<tr class="calendar-row">';
  /* вывод пустых ячеек в сетке календаря */
  for($x = 1; $x < $running_day; $x++):
    $calendar.= '<td class="calendar-day-np"> </td>';
    $days_in_this_week++;
  endfor;
  /* дошли до чисел, будем их писать в первую строку */
  for($list_day = 1; $list_day <= $days_in_month; $list_day++):
 

   $td_style="calendar-day";     
   if ($list_day == $sel_d) {$td_style="selected-day";} 
       
   $scripDate = $list_day.".".$month.".".$year;
   $calendar.= "<td onclick=\"javascript:switchDate('".$scripDate."');\" class=\"".$td_style."\">";
      /* Пишем номер в ячейку */
      
      $nm_style="day-number";      
      if ($list_day == $sel_d ) {$nm_style="selected-day-number";}         
      if ($list_day == $cur_d ) {$nm_style="cur-day-number";}                                                                                         
      $calendar.= "<div class=\"".$nm_style."\">".$list_day."</div>";
                              
                              
        if ($event_array[$list_day] >0 )
        {
         if ($list_day<$cur_d) {$style="fail-event";}
                          else {$style="norm-event";}
            $out=$event_array[$list_day];
        }    
        else {$style="no-event"; $out="&nbsp;";}
        
        $calendar.= '<div class="'.$style.'">'.$out.'</div>';
            
      /** ЗДЕСЬ МОЖНО СДЕЛАТЬ MySQL ЗАПРОС К БАЗЕ ДАННЫХ! ЕСЛИ НАЙДЕНО СОВПАДЕНИЕ ДАТЫ СОБЫТИЯ С ТЕКУЩЕЙ - ВЫВОДИМ! **/
      $calendar.= str_repeat('<p> </p>',2);
      
    $calendar.= '</td>';
    if($running_day == 7)
    {    
      $calendar.= '</tr>';
      if(($day_counter+1) != $days_in_month)
      {
        $calendar.= '<tr class="calendar-row">';
      }
      $running_day = 0;
      $days_in_this_week = 0;
    }
    $days_in_this_week++; $running_day++; $day_counter++;
  endfor;
  /* Выводим пустые ячейки в конце последней недели */
  if($days_in_this_week < 8):
    for($x = 1; $x <= (8 - $days_in_this_week); $x++):
      $calendar.= '<td class="calendar-day-np"> </td>';
    endfor;
  endif;
  /* Закрываем последнюю строку */
  $calendar.= '</tr>';
  /* Закрываем таблицу */
  $calendar.= '</table>';
  
  /* Все сделано, возвращаем результат */
  return $calendar;
 }

   public function getDetailProvider($params)
   {
    
    if ( empty($this->filtDate) )    
    {
       $day = date("d");
    $month = date("m");
    $year = date("Y");
    }
    else
    {
       $day   = date("d", strtotime($this->filtDate));
    $month = date("m", strtotime($this->filtDate));
    $year  = date("Y", strtotime($this->filtDate));
    $this->detail = 1;
    }

    

    
    $curUser=Yii::$app->user->identity;
        
    $countquery  = new Query();
    $query       = new Query();
            
    $countquery->select (" count({{%calendar}}.id)")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%phones}}','{{%phones}}.id = {{%contact}}.ref_phone')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%contact}}.ref_org');
        
    $countquery->andWhere("({{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id.")"  );    
        
    
    $query->select (" {{%calendar}}.id as id, event_date, eventNote, {{%calendar}}.ref_event, eventStatus, {{%event}}.eventTitle,  
                      {{%contact}}.contactFIO, {{%contact}}.contactDate, {{%contact}}.note, {{%phones}}.phone,
                      {{%orglist}}.title, {{%orglist}}.id as orgId, {{%calendar}}.ref_zakaz as zakazId ")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%phones}}','{{%phones}}.id = {{%contact}}.ref_phone')    
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%calendar}}.ref_org'); 

    $query->andWhere("({{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id.")"  );    

   
   switch ($this->type)
   {
    /* Для  моды 2 и 3*/   
    case 0:     
    break;    
    case 1:   
        /*Счета и заявки*/
        $query->andFilterWhere(['>', 'ref_event', 2]);
        $countquery->andFilterWhere(['>', 'ref_event', 2]);
        $query->andFilterWhere(['<', 'ref_event', 8]);
        $countquery->andFilterWhere(['<', 'ref_event', 8]);
    break;    
    case 2:   
        /*Произвольный контакт*/    
        $query->andFilterWhere(['>', 'ref_event', 7]);
        $countquery->andFilterWhere(['>', 'ref_event', 7]);
    break;    
    case 3:   
        /*Холодная база*/    
        $query->andFilterWhere(['<', 'ref_event', 3]);
        $countquery->andFilterWhere(['<', 'ref_event', 3]);
    break;    
    
    /* Для моды  1*/   
    case 7:   
        $query->andFilterWhere(['=', 'eventStatus', 1]);
        $countquery->andFilterWhere(['=', 'eventStatus', 1]);
        $query->andFilterWhere(['<', 'ref_event', 3]);
        $countquery->andFilterWhere(['<', 'ref_event', 3]);

    break;    
    
    case 9:   
        /*Выполнены  на сегодня*/  
        $query->andFilterWhere(['=', 'DATE(event_date)', date("Y-m-d")]);
        $countquery->andFilterWhere(['=', 'DATE(event_date)', date("Y-m-d")]);    
    
        $query->andFilterWhere(['=', 'eventStatus', 2]);
        $countquery->andFilterWhere(['=', 'eventStatus', 2]);
    break;    

    
   }
   
   if ($this->mode > 1)
   switch ($this->detail)   
   {
    case 0:     
        /*Не завершенные*/
        $query->andFilterWhere(['=', 'eventStatus', 1]);
        $countquery->andFilterWhere(['=', 'eventStatus', 1]);
    break;    
    case 1:     
        /*На сегодня*/
        $countquery->andFilterWhere(['=', 'month(event_date)', $month]);    
        $countquery->andFilterWhere(['=', 'year(event_date)', $year]);    

        $query->andFilterWhere(['=', 'month(event_date)', $month]);    
        $query->andFilterWhere(['=', 'year(event_date)', $year]);            
  
        $countquery->andFilterWhere(['=', 'day(event_date)', $day]);    
        $query     ->andFilterWhere(['=', 'day(event_date)', $day]);    
    break;    
    
    case 2:     
        /*Просрочены*/
        $countquery->andFilterWhere(['<', 'month(event_date)', $month]);    
        $countquery->andFilterWhere(['<=', 'year(event_date)', $year]);    

        $query->andFilterWhere(['<=', 'month(event_date)', $month]);    
        $query->andFilterWhere(['<=', 'year(event_date)', $year]);            
  
       
        $countquery->andFilterWhere(['<', 'day(event_date)', $day]);    
        $query     ->andFilterWhere(['<', 'day(event_date)', $day]);           
    break;    
   
    case 3:     
        /*все*/
    break;    

    case 4:     
        /*Не выполнены и не на сегодня*/  
        $cond = "(year(event_date) <> ".$year." OR month(event_date) <> ". $month." OR day(event_date) <> ".$day.")";
        $countquery->andWhere($cond);    
        $query     ->andWhere($cond);    
        $query->andFilterWhere(['=', 'eventStatus', 1]);
        $countquery->andFilterWhere(['=', 'eventStatus', 1]);

    break;    

    case 5:     
        /*Выполнены  на сегодня*/  
        $countquery->andFilterWhere(['=', 'month(event_date)', $month]);    
        $countquery->andFilterWhere(['=', 'year(event_date)', $year]);    

        $query->andFilterWhere(['=', 'month(event_date)', $month]);    
        $query->andFilterWhere(['=', 'year(event_date)', $year]);            
  
        $countquery->andFilterWhere(['=', 'day(event_date)', $day]);    
        $query     ->andFilterWhere(['=', 'day(event_date)', $day]);    
        
        $query->andFilterWhere(['=', 'eventStatus', 2]);
        $countquery->andFilterWhere(['=', 'eventStatus', 2]);

    break;    

    case 6:     
        /*Не выполнены и не на сегодня*/  
        $query->andFilterWhere(['>', 'DATE(event_date)', date("Y-m-d")]);
        $countquery->andFilterWhere(['>', 'DATE(event_date)', date("Y-m-d")]);
        $query->andFilterWhere(['=', 'eventStatus', 1]);
        $countquery->andFilterWhere(['=', 'eventStatus', 1]);

    break;    

    
    case 7:     
        /*На сегодня и пропущенные*/        
    //    $cond = "(DATE(event_date) < ".date("Y-m-d").")";
    //    $countquery->andWhere($cond);    
    //    $query     ->andWhere($cond);    
        
        $query->andFilterWhere(['<=', 'DATE(event_date)', date("Y-m-d")]);
        $countquery->andFilterWhere(['<=', 'DATE(event_date)', date("Y-m-d")]);
        
    
        $query->andFilterWhere(['=', 'eventStatus', 1]);
        $countquery->andFilterWhere(['=', 'eventStatus', 1]);
        //$this->debug[] = $cond;
        //$this->debug[] = $query;
    break;    

    
   }
   
    
  $refEventArray=[
                "0" => "Продолжить контакт",
                "1" => "Выяснение потребностей",                
                "3" => "Согласовать заявку",                
                "4" => "Резерв товара",
                "5" => "Выписать счет",
                "6" => "Счет получен клиентом",
                "7" => "Оплата произведена",
                "8" => "Гарантийные документы получены",
                "9" => "Деньги дошли",
                "10" => "Задание на отгрузку",
                "11" => "Поставка произведена",
                "12" => "Клиент подвердил поставку",
                "13" => "Отзыв получен",
                "14" => "Работа со счетом завершена",
        ];
    
    if (($this->load($params) && $this->validate())) {
        /* Фильтр есть */
     $query->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
     $countquery->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     
     
     if ($this->refEvent!="") { 
     $query->andFilterWhere(['like', 'eventNote', $refEventArray[$this->refEvent] ]);
     $countquery->andFilterWhere(['like', 'eventNote',$refEventArray[$this->refEvent] ]);
     }  
     
     //$query->andFilterWhere(['like', '{{%contact}}.contactFIO', $this->contactFIO]);
     //$countquery->andFilterWhere(['like', '{{%contact}}.contactFIO', $this->contactFIO]);
          
     
     //$query->andFilterWhere(['=', 'eventStatus', $this->eventStatus]);
     //$countquery->andFilterWhere(['=', 'eventStatus', $this->eventStatus]);

/*      $countquery->andFilterWhere(['=', 'date(event_date)', $event_date]);    
     $query     ->andFilterWhere(['=', 'date(event_date)', $event_date]);    */

     
    } 
    if (empty($this->eventStatus))
    {
        /* Фильтра нет */
       if ($this->detail == 1) 
       {
           $this->eventStatus = 1;
            $query->andFilterWhere(['=', 'eventStatus', '1']);
            $countquery->andFilterWhere(['=', 'eventStatus', '1']);           
       }

       
       if ($this->detail == 2) 
       {
           $this->eventStatus = 1;
            $query->andFilterWhere(['=', 'eventStatus', '1']);
            $countquery->andFilterWhere(['=', 'eventStatus', '1']);           
       }
    }
    
    
    $count = $countquery->createCommand()->queryScalar();
    $command = $query->createCommand();    
        
        $provider = new SqlDataProvider(
        [   'sql' => $command ->sql, 
            'params' => $command->params,    
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'event_date',
            'eventTitle',
            'eventStatus',
            'contactDate',
            'contactFIO',    
            'title',            
            'eventNote',
            'defaultOrder' => [    'event_date' => SORT_DESC ],
            ],
            ],
        ]);
    return $provider;
   }   

    
   public function getInWorkProvider()
   {
        $curUser=Yii::$app->user->identity;
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND (ref_user =:ref_user OR refManager=:ref_user) ', 
            [':ref_user' => $curUser->id])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => ' SELECT id, title, contactDate  FROM   {{%orglist}} 
            where isPreparedForSchet=1  AND isInWork=1 AND isSchetFinished=0 AND (ref_user =:ref_user OR refManager=:ref_user)',         
            'params' => [':ref_user' => $curUser->id],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            ],
            ],
        ]);
    return $provider;
   }   

    public function getNotInWorkProvider()
   {
        $curUser=Yii::$app->user->identity;
        $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1  AND isInWork=:isInWork AND isSchetFinished=0', 
            [':isInWork' => 0])->queryScalar();
            
        $provider = new SqlDataProvider(['sql' => 
            ' SELECT id, title, isSchetReject, contactDate, nextContactDate FROM   {{%orglist}}                   
                WHERE isPreparedForSchet=1  AND isInWork=:isInWork AND isSchetFinished=0 ',
            'params' => [':isInWork' => 0],
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            ],
            ],
        ]);
    return $provider;
   }   
/**********************************/
 /*
    Все организации закрепленные за менеджером  не в активной работе, те нет событий связанных со счетами и заявками
 */
   public function getNonActiveClientListProvider($params)
   {
     
     $curUser=Yii::$app->user->identity;

     $query  = new Query();
     $countquery  = new Query();

//$this->debug[]=     $this->type;

   if ($this->type == 4 )/* Произвольные контакты сейчас */
   {
     $countquery->select ("count(DISTINCT {{%calendar}}.id)")
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user");
                 

     $query->select("{{%orglist}}.id, title, contactDate, DATE(event_date) as nextContactDate, {{%user}}.userFIO, b.userFIO as operator, {{%calendar}}.id as activeEvent " )
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")
                 ->distinct();
   
        if ($curUser->roleFlg & 0x0080 && $this->userShow ==1 ) /*Для помошникиков могут быть открыты все*/
        {
            $countquery->where("( {{%calendar}}.ref_user =:refUser OR {{%orglist}}.refManager  = :refUser OR isAvailableForHelper =1) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) <= CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");
            $query->where("( {{%calendar}}.ref_user  =:refUser  OR {{%orglist}}.refManager  = :refUser OR isAvailableForHelper =1) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) <= CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");            
        }
        else
        {
            $countquery->where("({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager  = :refUser) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) <= CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");            
            $query->where("({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager  = :refUser)   
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) <= CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");                        
        }       
   }
     
   if ($this->type == 5 )/* Произвольные контакты далее */
   {
     $countquery->select ("count({{%calendar}}.id)")
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")
                 ->distinct();

     $query->select("{{%orglist}}.id, title, contactDate, DATE(event_date) as nextContactDate, {{%user}}.userFIO, b.userFIO as operator, {{%calendar}}.id as activeEvent " )
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%calendar}}.ref_user")
                 ->distinct();
   
        if ($curUser->roleFlg & 0x0080 && $this->userShow ==1 ) /*Для помошникиков открыты все*/
        {
            $countquery->where("( {{%calendar}}.ref_user =:refUser  OR {{%orglist}}.refManager  = :refUser OR isAvailableForHelper =1) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) > CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");
            $query->where("( {{%calendar}}.ref_user  =:refUser  OR {{%orglist}}.refManager  = :refUser OR isAvailableForHelper =1) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) > CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");            
        }
        else
        {
            $countquery->where("({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager  = :refUser) 
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) > CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");            
            $query->where("({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager  = :refUser)   
            AND eventStatus=1  AND ref_event >7 AND  DATE(event_date) > CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");                        
        }       
   }
     
     
   if ($this->type ==10 )                 
   {
    /* Произвольные контакты - нет запланированной активности*/
     $countquery->select ("count({{%orglist}}.id)")
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")                 
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 AND event_date >= CURRENT_DATE() group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")                 
                 ->distinct()
                 ;

     $query->select("{{%orglist}}.id, title, contactDate, nextContactDate, {{%user}}.userFIO, b.userFIO as operator, activeSchet, activeZakaz, activeEvent" )
                  ->from("{{%orglist}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")                 
                 ->leftJoin("(SELECT count(id) as activeSchet, refOrg  from {{%schet}} where isSchetActive=1 group by refOrg) as a ", "a.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT count(id) as activeZakaz, refOrg  from {{%zakaz}} where isActive=1 group by refOrg) as c ", "c.refOrg = {{%orglist}}.id")
                 ->leftJoin("(SELECT MAX(id) as activeEvent, ref_org  from {{%calendar}} where eventStatus=1 AND event_date >= CURRENT_DATE() group by ref_org) as d ", "d.ref_org = {{%orglist}}.id")
                 ->distinct()
                 ;

        if ($curUser->roleFlg & 0x0080 && $this->userShow ==1 )
        {
            $countquery->where("(refManager=:refUser  OR isAvailableForHelper =1) and ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");
            $query->where("(refManager=:refUser  OR isAvailableForHelper =1) and ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");            
        }
        else
        {
            $countquery->where("(refManager=:refUser)   and ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");
            $query->where("(refManager=:refUser)   and ifnull(activeEvent,0)=0  and ifnull(activeZakaz,0)=0 and ifnull(activeSchet,0)=0 ");
        }
   }


    

 if ($this->type == 6 )
   {
       /* Произвольные контакты выполнено */

     $countquery->select ("count({{%calendar}}.id)")
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")
                 ->distinct()
                 ;

     $query->select("{{%orglist}}.id, title, contactDate, nextContactDate, {{%user}}.userFIO, b.userFIO as operator, {{%calendar}}.id as activeEvent " )
                  ->from("({{%orglist}},{{%calendar}})")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")                 
                 ->leftJoin("{{%user}} as b", "b.id = {{%orglist}}.ref_user")
                 ->distinct()
                 ;

          /* Выполнено только мной*/                 
            $countquery->where("({{%calendar}}.ref_user=:refUser) 
            AND eventStatus=2  AND ref_event >7 AND  DATE(event_date) = CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");            
            $query->where("({{%calendar}}.ref_user=:refUser)   
            AND eventStatus=2  AND ref_event >7 AND  DATE(event_date) = CURRENT_DATE() and {{%calendar}}.ref_org= {{%orglist}}.id");                        
   }
    
    
       if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'b.userFIO', $this->operator]); 
        $countquery->andFilterWhere(['like', 'b.userFIO', $this->operator]);

        
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);

        
        $countquery->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['=', 'nextContactDate', $this->nextContactDate]);
     
     }

        $query->addParams([':refUser' => $curUser->id]);
        $countquery->addParams([':refUser' => $curUser->id]);

       $command = $query->createCommand();    
       
       $count = $countquery->createCommand()->queryScalar();
        
if ($this->type >4) $pageSize = 5;
              else  $pageSize = 10;

$commandC = $countquery->createCommand();
//$this->debug[]= $commandC->sql;
//$this->debug[]=$command->params;
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'contactDate',
            'nextContactDate',
            'userFIO',
            'operator',
            'activeSchet', 
            'activeZakaz',
            'activeEvent'
            ],
            'defaultOrder' => [    'nextContactDate' => SORT_ASC ],
            ],
        ]);
    return $provider;
   }   


   
/**********************/   
   public function markEvent($eventId)
   {
   
       
   
     $eventRecord = CalendarList::findOne($eventId);
     if (empty($eventRecord)) return false;      
       
     if (!empty($eventRecord->ref_zakaz)){
     $list = Yii::$app->db->createCommand("SELECT docStatus, cashState, supplyState from {{%schet}} where refZakaz =:refZakaz",
     [':refZakaz' => $eventRecord ->ref_zakaz])->queryAll(); 
     if (count($list) > 0) 
       $eventRecord ->docStatus  =$list[0]['docStatus'];
       $eventRecord ->cashState  =$list[0]['cashState'];
       $eventRecord ->supplyState=$list[0]['supplyState'];
     }       
       
       $eventRecord ->eventStatus=2;
       $eventRecord ->executeDateTime=date('Y-m-d H:i:s', time());
       $eventRecord ->save();       
       
       
       
   }

   public function shiftArbitraryEvent($ref_org,$shift)
   {
       $curUser=Yii::$app->user->identity;
              
       $orgRecord = OrgList::findOne($ref_org);
       if (empty ($orgRecord)) return false;
       
       $nextDate = date ('Y-m-d', time() + 60*60*24*$shift);
       $orgRecord -> nextContactDate = $nextDate;
       $orgRecord -> save();
       
       $eventRecord = CalendarList::findOne([
       'ref_org' => $ref_org,
       'ref_event' => 8,
       'ref_zakaz' => 0,
       'eventStatus' => 1,       
       ]);
       
       if (!empty($eventRecord) )
       {
         $eventNote = $eventRecord->eventNote;
       }else
       {
         $eventNote = "Произвольный контакт";
       }
       

       /*метим все не привязанное к заказу как выполненное*/
        $this->markRefEvent($ref_org, 0);        

        $this->createEvent($nextDate, 8, $ref_org, 0, 0, $eventNote);
    
    return true;
   }

   public function shiftDealEvent($eventId, $shift)
   {
       $curUser=Yii::$app->user->identity;
       $nextDate = date ('Y-m-d', time() + 60*60*24*$shift);

       $eventRecord = CalendarList::findOne($eventId);
       if (empty($eventRecord) ) return false;
        $eventRecord->event_date = $nextDate;
       $eventRecord->save();

       
       $orgRecord = OrgList::findOne($eventRecord->ref_org);
       if (empty ($orgRecord)) return false;
       
       $orgRecord -> nextContactDate = $nextDate;
       $orgRecord -> save();
       
    return true;
   }

   
   public function markRefEvent($ref_org, $ref_zakaz)
   {
   
   
       $docStatus  =0;
       $cashState  =0;
       $supplyState=0;
     if (!empty($ref_zakaz)){ 
      $list = Yii::$app->db->createCommand("SELECT docStatus, cashState, supplyState from {{%schet}} where refZakaz =:refZakaz",
     [':refZakaz' => $ref_zakaz])->queryAll(); 
     if (count($list) > 0){ 
       $docStatus  =$list[0]['docStatus'];
       $cashState  =$list[0]['cashState'];
     $supplyState=$list[0]['supplyState'];}
     }       

   
       $curUser=Yii::$app->user->identity;       
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET eventStatus =2,  docStatus=".$docStatus.", cashState=".$cashState.", supplyState=".$supplyState.",
            executeDateTime = '". date('Y-m-d H:i:s', time())."' WHERE ref_org=:ref_org  AND  eventStatus <> 2 AND (ref_zakaz=:ref_zakaz)", 
            [
            ':ref_org'   => $ref_org,            
            ':ref_zakaz' => $ref_zakaz,            
        ])->execute();
    
    
      /*отказы по счетам  */        
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET eventStatus =2, executeDateTime = '". date('Y-m-d H:i:s', time())."' WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 AND ref_event=8  AND ref_zakaz>0 and event_date < CURRENT_DATE()", 
            [
            ':ref_org'   => $ref_org,                        
            ])->execute();


      /* 
        Произвольные события - останется только один 
        Произвольным сочтем все у кого нет привязки к заказу
      */
       if ($ref_zakaz == 0 )      
       {
        /* дата актуальности произвольного события - все что раньше умрет для всех пользователей */  
        $actualityDate = date('Y-m-d', time()-7*(360*24) );    
           
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET eventStatus =2, executeDateTime = '". date('Y-m-d H:i:s', time())."' WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 AND ref_zakaz=0 and (event_date < :actualityDate or event_date is null)", 
            [
            ':ref_org'   => $ref_org,                        
            ':actualityDate'=> $actualityDate,                        
            ])->execute();
       }

       
       /*Пометим все события связанные с закрытыми счетами*/
       Yii::$app->db->createCommand(
         " update {{%calendar}}, {{%schet}} set {{%calendar}}.eventStatus = 2 where {{%calendar}}.ref_zakaz = {{%schet}}.refZakaz
           AND eventStatus=1 and {{%schet}}.isSchetActive = 0")->execute();       
            
       /*Пометим все события связанные с закрытыми по отказу заявками*/            
       Yii::$app->db->createCommand(
         " update {{%calendar}}, {{%zakaz}} set eventStatus=2 where  {{%calendar}}.ref_zakaz = {{%zakaz}}.id AND eventStatus=1 
        AND  {{%zakaz}}.isActive=0 AND  {{%zakaz}}.isFormed=0 AND  {{%calendar}}.ref_event =3 ")->execute();                  
            
    }

   public function  setEventsToUser ($userId, $orgListId,$eventNote)
   {
   
   $list = explode(',',$orgListId);
   for ($i=0;$i< count($list);$i++)
   {
   if (empty($list[$i]))continue;
       $eventRecord = new CalendarList();
       $eventRecord ->ref_user= $userId;
       $eventRecord ->event_date = date('Y-m-d H:i:s', time());
       $eventRecord ->eventStatus=1;//назначено
       $eventRecord ->ref_contact=0;
       $eventRecord ->ref_event=8;
       $eventRecord ->ref_org=$list[$i];
       $eventRecord ->ref_zakaz=0;
       $eventRecord ->eventNote=$eventNote;
       $eventRecord ->save();            
    }
   
   }
   
   /*Дата события, тип события, орг-ция, заказ   НЕ УЧИТЫВАЕТ ВРЕМЯ - obsoleted */
   public function createEvent($eventDate, $ref_event, $ref_org, $ref_zakaz, $ref_contact, $eventNote)
   {
       $curUser=Yii::$app->user->identity;
       if (Yii::$app->user->isGuest) {return;} //гостям тут не место
       
       //Предыдущее событие выполнено       
       $this->markRefEvent($ref_org, $ref_zakaz);
        
       // Создадим новое                     
       $eventRecord = new CalendarList();
       $eventRecord ->ref_user= $curUser->id;
       if (empty($eventDate))$eventRecord ->event_date = date('Y-m-d H:i:s');
                        else $eventRecord ->event_date = date('Y-m-d H:i:s', strtotime($eventDate));
       $eventRecord ->eventStatus=1;//назначено
       $eventRecord ->ref_contact=$ref_contact;
       $eventRecord ->ref_event=$ref_event;
       $eventRecord ->ref_org=$ref_org;
       $eventRecord ->ref_zakaz=$ref_zakaz;
       $eventRecord ->eventNote=$eventNote;
       $eventRecord ->save();           
       return     $eventRecord->id;
   }

  /****************************************************************************/
  /****************************************************************************/

  /*Пометим как неисполненное */
   public function markSingleEventWait($eventId)
   {
       $eventRecord = CalendarList::findOne($eventId);
       if (empty($eventRecord)) return false;
       $eventRecord ->eventStatus=1;
       $eventRecord ->executeDateTime=date('Y-m-d H:i:s', time());
       $eventRecord ->refExecute=0;
       $eventRecord ->save();   
       return true;       
   }

   /*Пометим как исполненное */
   public function markSingleEventExecute($eventId,  $execute_contact)
   {
       $eventRecord = CalendarList::findOne($eventId);
       if (empty($eventRecord)) return false;
       $eventRecord ->eventStatus=2;
       $eventRecord ->executeDateTime=date('Y-m-d H:i:s', time());
       $eventRecord ->refExecute=$execute_contact;
       $eventRecord ->save();       
       return true;       
   }

  /*Пометим как исполненное предыдущие - подотрем старые события*/
   public function markAllEventsExecute($ref_org, $ref_zakaz, $execute_contact)
   {

       
     if ($ref_zakaz > 0)
     {
       $curUser=Yii::$app->user->identity;       
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET 
            eventStatus =2, 
            executeDateTime = '". date('Y-m-d H:i:s', time())."', 
            refExecute = :refExecute
            WHERE ref_org=:ref_org  AND  eventStatus <> 2 AND (ref_zakaz=:ref_zakaz)", 
            [
            ':ref_org'   => $ref_org,            
            ':ref_zakaz' => $ref_zakaz,            
            ':refExecute' => $execute_contact
        ])->execute();
        
    
      /*отказы по счетам  */        
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET 
            eventStatus =2, 
            executeDateTime = '". date('Y-m-d H:i:s', time())."', 
            refExecute = :refExecute
            WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 AND ref_event=8  AND ref_zakaz>0 and event_date < CURRENT_DATE()", 
            [
            ':ref_org'   => $ref_org,        
            ':refExecute' => $execute_contact            
            ])->execute();
            
       return;
     }

      /* 
        Произвольные события - останется только один 
        Произвольным сочтем все у кого нет привязки к заказу
      */
       if ($ref_zakaz == 0 )      
       {
        /* дата актуальности произвольного события - все что раньше умрет для всех пользователей - за 7 дней до этого*/  
        $actualityDate = date('Y-m-d', time());    
           
       Yii::$app->db->createCommand(
            "UPDATE {{%calendar}} SET 
            eventStatus =2, 
            executeDateTime = '". date('Y-m-d H:i:s', time())."',
            refExecute = :refExecute
            WHERE  ref_org=:ref_org  
            AND  eventStatus <> 2 AND ref_zakaz=0 and (event_date <= :actualityDate or event_date is null)", 
            [
            ':ref_org'   => $ref_org,                        
            ':actualityDate'=> $actualityDate,           
            ':refExecute' => $execute_contact                        
            ])->execute();
       }

       
       /*Пометим все события связанные с закрытыми счетами*/
       Yii::$app->db->createCommand(
         " update {{%calendar}}, {{%schet}} set {{%calendar}}.eventStatus = 2 where {{%calendar}}.ref_zakaz = {{%schet}}.refZakaz
           AND eventStatus=1 and {{%schet}}.isSchetActive = 0")->execute();       
            
       /*Пометим все события связанные с закрытыми по отказу заявками*/            
       Yii::$app->db->createCommand(
         " update {{%calendar}}, {{%zakaz}} set eventStatus=2 where  {{%calendar}}.ref_zakaz = {{%zakaz}}.id AND eventStatus=1 
        AND  {{%zakaz}}.isActive=0 AND  {{%zakaz}}.isFormed=0 AND  {{%calendar}}.ref_event =3 ")->execute();                  
            
    }

   /*Дата события, время события, тип события, орг-ция, заказ*/
   public function createEventTime($eventDate, $eventTime, $ref_event, $ref_org, $ref_zakaz, $ref_contact, $eventNote, $execute_contact)
   {
       $curUser=Yii::$app->user->identity;
       if (Yii::$app->user->isGuest) {return;} //гостям тут не место
       
       //Предыдущие события выполнены       
       $this->markAllEventsExecute($ref_org, $ref_zakaz, $execute_contact);
        
       // Создадим новое                     
       $eventRecord = new CalendarList();
       $eventRecord ->ref_user= $curUser->id;
       if (empty($eventDate))$eventRecord ->event_date = date('Y-m-d H:i:s');
                        else $eventRecord ->event_date = date('Y-m-d H:i:s', strtotime($eventDate));
       $eventRecord ->eventStatus=1;//назначено
       $eventRecord ->ref_contact=$ref_contact;
       $eventRecord ->ref_event=$ref_event;
       $eventRecord ->ref_org=$ref_org;
       $eventRecord ->ref_zakaz=$ref_zakaz;
       $eventRecord ->eventTime = date('H:i', strtotime($eventTime)); 
       $eventRecord ->eventNote=$eventNote;
       $eventRecord ->save();           
       return     $eventRecord->id;
   }

   
   
  public function getCfgValue($key)        
   {
     $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
            ':key' => intval($key),            
            ])->queryOne();  
            
    return $record['keyValue'];
   }

/***************************
Число необработанных лидов
****************************/      
   public function getLeadsInWork()
   {      
    $leadDuration = Yii::$app->db->createCommand('SELECT keyValue from {{%config}} WHERE id =:key', 
            [':key' => 2105, ])->queryScalar();  
    $timeCond=" AND DATEDIFF(NOW(),{{%contact}}.contactDate) < ".$leadDuration;
    $strCount = "SELECT count({{%contact}}.id) from {{%contact}} where eventType > 10 && eventType < 20 ".$timeCond;            
    return Yii::$app->db->createCommand($strCount)->queryScalar();                
   }   
   
/***************************
Всего не закрытых сделок
****************************/      
   public function getZakazInWork()
   {      
         $strCount = "SELECT count({{%zakaz}}.id) as allDeal, sum(schetSumm) AS allDealSumm from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where (isActive = 1 OR {{%schet}}.isSchetActive = 1 )";            
         
    return Yii::$app->db->createCommand($strCount)->queryOne();                
   }   


   
/***/   
   public function getCurrentlyNotInWork()
   {
            $curUser=Yii::$app->user->identity;
        if ($curUser->roleFlg & 0x0080) $srtSql = 'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1 AND isSchetFinished=0 AND  (refManager=0 OR isAvailableForHelper =1)';
                                   else $srtSql = 'SELECT count(id) from {{%orglist}} where isPreparedForSchet=1 AND isSchetFinished=0 AND refManager=0';

        
        
        $curUser=Yii::$app->user->identity;

          $ret =  Yii::$app->db->createCommand( $srtSql )->queryScalar();       
        
        return $ret;
   }   

   public function getCurrentlyInWork()
   {
        $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand('SELECT count({{%orglist}}.id) from {{%orglist}} left join {{%zakaz}} on {{%zakaz}}.refOrg= {{%orglist}}.id  where  {{%zakaz}}.isActive=1 AND {{%orglist}}.ref_user=:ref_user '
                                             ,[':ref_user'=>$curUser->id] )->queryScalar();       
        return $ret;
   }   
   
   public function getSchetInSupply()
   {
           $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%schet}} where  {{%schet}}.isSchetActive=1  AND isSupply =1 AND refManager=:ref_user '
                                             ,[':ref_user'=>$curUser->id] )->queryScalar();       
        return $ret;
   }
   
   public function getSchetInCash()
   {
           $curUser=Yii::$app->user->identity;
         
          $ret =  Yii::$app->db->createCommand('SELECT count(id) from {{%schet}} where isOplata =0 AND isSchetActive =1 AND refManager=:refUser' 
                                             ,[':refUser'=>$curUser->id] )->queryScalar();       
        return $ret;
   }
   
   public function getSchetNoRef1C()
   {
           $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand("SELECT count(id) from {{%schet}} where (ref1C IS NULL OR ref1C ='') AND isSchetActive =1 AND refManager=:refUser" 
                                             ,[':refUser'=>$curUser->id] )->queryScalar();    
        return $ret;
   }

   public function getSchetNo1COplata()
   {
           $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand("SELECT count(id) from {{%schet}} where (ref1C IS NOT NULL) AND (ref1C <> '')   AND ifnull(summOplata,0) < ifnull(schetSumm,0) AND  isSchetActive =1 AND refManager=:refUser" 
                                             ,[':refUser'=>$curUser->id] )->queryScalar();    
        return $ret;
   }

   public function getSchetNo1CSupply()
   {
           $curUser=Yii::$app->user->identity;
          $ret =  Yii::$app->db->createCommand("SELECT count(id) from {{%schet}} where isSupply = 1 And (ref1C IS NOT NULL) AND (ref1C <> '')   AND ifnull(summSupply,0) < ifnull(schetSumm,0) AND  isSchetActive =1 AND refManager=:refUser" 
                                             ,[':refUser'=>$curUser->id] )->queryScalar();    
        return $ret;
   }

   
   public function getAllAvailableClients()
   {            
        $curUser=Yii::$app->user->identity;
             
        //Если менеджер тип 2
        $strCount = "SELECT count(id) from {{%orglist}} where (refManager=:refUser OR refManager is NULL OR refManager =0 )";
        if ($curUser->roleFlg & 0x0080)
        {
            //$dateOfFree = date('Y-m-d', time() - 60*60*24*90); //90 дней
            $strCount = "SELECT count(id) from {{%orglist}} where (refManager=:refUser OR refManager is NULL OR refManager =0 OR isAvailableForHelper =1)";
        }
        
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id] )->queryScalar();       
        return $ret;
   }   

   public function getMyClients()
   {            
        $curUser=Yii::$app->user->identity;
             
            $strCount = "SELECT count(id) from {{%orglist}} where refManager=:refUser ";
    
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id] )->queryScalar();       
        return $ret;
   }   
   

/*******************************************************************************/
/***************************       ATS   ******************************/
/*******************************************************************************/


public function getPhoneDayStatistics()
{
 $res=[
   'dayCancel' => 0,   
 ];
  
    $query  = new Query();
    $query->select ('COUNT({{%ats_log}}.id)')
                ->from("{{%ats_log}}")           
                ->andWhere(['=', '{{%ats_log}}.orgRef', 0])
                ->andWhere(['=', 'DATE({{%ats_log}}.call_start)', date('Y-m-d')])
                ;    
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
            $query->andWhere(" disposition = 'cancel'  ");        
                
    $res['dayCancel']=$query->createCommand()->queryScalar();                
    
     
    return $res;
}    


/*******************************************************************************/
/***************************       EVENTS  CALC   ******************************/
/*******************************************************************************/
/*
ALTER TABLE `tbl_calendar` MODIFY COLUMN `event_date` DATETIME NOT NULL;
*/   
                 
/*Получим  на сегодняшнюю дату*/
   public function getCurrentEvents($type)
   {            
   
    $curUser=Yii::$app->user->identity;
    $cond = "";
   /*счета и заявки*/    
   if ($type == 1) { $cond = " AND (ref_event >2 AND ref_event <8)";}
   /*Произвольный контакт*/    
   if ($type == 2) { $cond = " AND (ref_event >7)";}
   /*Холодная база*/    
   if ($type == 3) { $cond = " AND (ref_event <3)";}    

   if ($type == 4) { $cond = " AND (ref_event >2 AND ref_event <8)";}
   /*Произвольный контакт*/    
   if ($type == 5) { $cond = " AND (ref_event >7)";}
   /*Холодная база*/    
   if ($type == 6) { $cond = " AND (ref_event <3)";}    


    $strCount = "SELECT count(DISTINCT {{%calendar}}.id) from {{%calendar}},{{%orglist}} 
                 WHERE {{%calendar}}.ref_org = {{%orglist}}.id  and eventStatus=1 AND  DATE(event_date) <= CURRENT_DATE() ".$cond;
                
        if (/*$curUser->roleFlg & 0x0080 &&*/ $type < 4) 
        {           
            $strCount .= " AND ({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager=:refUser OR isAvailableForHelper =1)";
        }    
        else
        {
         $strCount .= " AND ({{%calendar}}.ref_user=:refUser OR {{%orglist}}.refManager=:refUser)";
        }
    
   $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id] )->queryScalar();       
   return $ret;
   }   
/*************************/   
   
/*Получим  выполненные сегодня*/
   public function getFinishedTodayEvents($type)
   {            
        $curUser=Yii::$app->user->identity;

    $cond = "";
   /*счета и заявки*/    
   if ($type == 1) { $cond = " AND (ref_event >2 AND ref_event <8)";}
   /*Произвольный контакт*/    
   if ($type == 2) { $cond = " AND (ref_event >7)";}    
   /*Холодная база*/    
   if ($type == 3) { $cond = " AND (ref_event <3)";}    
       /*Выполнено мной!*/
        $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=2 ".$cond."  AND ref_user=:refUser and DATE(event_date) = :event_date";
    
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id, ':event_date' => date("Y-m-d", time())] )->queryScalar();       
        return $ret;
   }   
   
/*************************/   
   public function noContactCount()
   {

    $strSql = "SELECT COUNT(DISTINCT({{%orglist}}.id)) from {{%orglist}} left join {{%contact}}
    on {{%contact}}.ref_org = {{%orglist}}.id where {{%contact}}.id is null AND {{%orglist}}.isOrgActive =1
    ";
   return Yii::$app->db->createCommand($strSql)->queryScalar();

   }
/*Получим  Дальнейшие события*/
   public function getOtherEvents($type)
   {            
    $curUser=Yii::$app->user->identity;

      if ($type < 4 /*$curUser->roleFlg & 0x0080*/) /*Для помошникиков открыты все*/
        {
            $cond = "( {{%calendar}}.ref_user =:refUser  OR {{%orglist}}.refManager  = :refUser OR isAvailableForHelper =1) AND eventStatus=1 
                     AND  DATE(event_date) > :cur_date and {{%calendar}}.ref_org= {{%orglist}}.id";
        }
        else
        {
            $cond = "( {{%calendar}}.ref_user =:refUser OR {{%orglist}}.refManager  = :refUser) AND eventStatus=1 
                     AND  DATE(event_date) > :cur_date and {{%calendar}}.ref_org= {{%orglist}}.id";
        }      

             
    switch ($type)    
    {
       
      case 1:
      /*счета и заявки*/    
             
      $cond .= " AND (ref_event >2 AND ref_event <8)";
      $strCount = "SELECT  count(DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}}  where eventStatus=1 AND ".$cond." ";
      break;      
        
      case 2:
      /*Произвольный контакт*/       
      $cond .= " AND (ref_event =8)";
      $strCount = "SELECT count( DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}} where eventStatus=1 AND ".$cond." ";
      break;      
        
      case 3:
      /*Холодная база*/    
      $cond .= " AND (ref_event <3)";
      $strCount = "SELECT count(DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}} where eventStatus=1 AND ".$cond." ";
      break;              
    
     case 4:
      /*счета и заявки*/    
             
      $cond .= " AND (ref_event >2 AND ref_event <8)";
      $strCount = "SELECT  count(DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}}  where eventStatus=1 AND ".$cond." ";
      break;      
        
      case 5:
      /*Произвольный контакт*/       
      $cond .= " AND (ref_event =8)";
      $strCount = "SELECT count(DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}} where eventStatus=1 AND ".$cond." ";
      break;      
        
      case 6:
      /*Холодная база*/    
      $cond .= " AND (ref_event <3)";
      $strCount = "SELECT count(DISTINCT {{%calendar}}.id) from {{%calendar}}, {{%orglist}} where eventStatus=1 AND ".$cond." ";
      break;              
    }
    
    
//$this->debug[] = $strCount;
    
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id,':cur_date'=> date('Y-m-d')] )->queryScalar();       
        return $ret;
   }   
   
/*************************/   
   public function getFailedEvents()
   {            
        $curUser=Yii::$app->user->identity;
             
        $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=1 AND ref_user=:refUser and DATE(event_date) < :event_date";
    
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id, ':event_date' => date("Y-m-d", time()-60*60*24*2)] )->queryScalar();       
        return $ret;
   }   
/*************************/   
   
   public function getFutureEvents()
   {            
        $curUser=Yii::$app->user->identity;
             
        $strCount = "SELECT count(id) from {{%calendar}} where eventStatus=1 AND  ref_user=:refUser and DATE(event_date) > :event_date";
    
          $ret =  Yii::$app->db->createCommand($strCount,[':refUser'=>$curUser->id, ':event_date' => date("Y-m-d", time())] )->queryScalar();       
        return $ret;
   }   



    public function getMyStats()
   {
        $y=date('Y');
        $m=date('m');
        $d=date('d');
        
        $stats= array();              
        
        $curUser=Yii::$app->user->identity;
          $stats['m_events'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%calendar}} where  ref_user=:ref_user and eventStatus=2
                                                              and year(event_date)=:y And month(event_date)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_events'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%calendar}} where  ref_user=:ref_user  and eventStatus=2
                                                              and year(event_date)=:y And month(event_date)=:m And day(event_date)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       

        
          $stats['m_contacts'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  ref_user=:ref_user 
                                                              and year(contactDate)=:y And month(contactDate)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_contacts'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%contact}} where  ref_user=:ref_user 
                                                              and year(contactDate)=:y And month(contactDate)=:m And day(contactDate)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
                                             

          $stats['m_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where  ref_user=:ref_user 
                                                              and year(formDate)=:y And month(formDate)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_zakaz'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%zakaz}} where  ref_user=:ref_user 
                                                              and year(formDate)=:y And month(formDate)=:m And day(formDate)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
                                             
          $stats['m_schet'] =  Yii::$app->db->createCommand('SELECT count(id) from {{%schet}} where  refManager=:ref_user 
                                                              and year(schetDate)=:y And month(schetDate)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_schet'] =  Yii::$app->db->createCommand('SELECT count(id)   from {{%schet}} where  refManager=:ref_user 
                                                              and year(schetDate)=:y And month(schetDate)=:m And day(schetDate)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        
        
        $stats['m_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from {{%schet}} left join {{%oplata}} on {{%oplata}}.refSchet = {{%schet}}.id where  refManager=:ref_user 
                                                              and year(oplateDate)=:y And month(oplateDate)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_oplata'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(oplateSumm),0) as summOplata
        from {{%schet}} left join {{%oplata}} on {{%oplata}}.refSchet = {{%schet}}.id where  refManager=:ref_user 
                                                              and year(oplateDate)=:y And month(oplateDate)=:m And day(oplateDate)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       
        

        $stats['m_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from {{%schet}} left join {{%supply}} on {{%supply}}.refSchet = {{%schet}}.id where  refManager=:ref_user 
                                                              and year(supplyDate)=:y And month(supplyDate)=:m'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m, ] )->queryScalar();       
          
        $stats['d_supply'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(supplySumm),0) as summSupply
        from {{%schet}} left join {{%supply}} on {{%supply}}.refSchet = {{%schet}}.id where  refManager=:ref_user 
                                                              and year(supplyDate)=:y And month(supplyDate)=:m And day(supplyDate)=:d'
                                             ,[':ref_user'=>$curUser->id, ':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       

    

        $stats['m_extract'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} LEFT JOIN {{%orglist}} ON {{%orglist}}.id = {{%bank_extract}}.orgRef
        where  year(recordDate)=:y And month(recordDate)=:m 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО%") 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКИЙ БАНК ПАО СБЕРБАНК%") 
        AND ({{%bank_extract}}.extractType = 1)'
                                             ,[':y'=>$y,':m'=>$m, ] )->queryScalar();       
       
     /*  echo  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} where  year(recordDate)=:y And month(recordDate)=:m 
        AND ({{%bank_extract}}.extractType = 1)
        and debetOrgTitle not like "СИБИРСКИЙ БАНК ПАО СБЕРБАНК" and debetOrgTitle not like "СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО"'
                                             ,[':y'=>$y,':m'=>$m, ] )->getRawSql();  
*/

        $stats['d_extract'] =  Yii::$app->db->createCommand('SELECT  ifnull(sum(creditSum),0) 
        from {{%bank_extract}} LEFT JOIN {{%orglist}} ON {{%orglist}}.id = {{%bank_extract}}.orgRef
        where  year(recordDate)=:y And month(recordDate)=:m And day(recordDate)=:d
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКОЕ ТЕХНОЛОГИЧЕСКОЕ АГЕНТСТВО%") 
        AND (`debetOrgTitle` NOT LIKE "%СИБИРСКИЙ БАНК ПАО СБЕРБАНК%") 
        AND ({{%bank_extract}}.extractType = 1)'
                                             ,[':y'=>$y,':m'=>$m,':d'=>$d, ] )->queryScalar();       

        $stats['last_extract'] =  Yii::$app->db->createCommand('SELECT  max(creationDate) from {{%bank_header}}' )->queryScalar();                                                    
    
        return $stats;
   }   

   
/*************************/   
/*************************/   
   
       public function getNeedList()
    {
      $strSql = "Select ref_org, ref_need_title, {{%need_title}}.`Title`    from {{%schet_need}}, {{%need_title}}
                  where {{%schet_need}}.`ref_need_title` = {{%need_title}}.`id` and {{%schet_need}}.ref_org =:ref_org";
      $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->id])->queryAll();             
      return $ret;
    }
/*************************/   

    public function getContactDetail()
    {
      $strSql = "Select contactFIO, note, contactDate, phone, status from {{%contact}} left join {{%phones}} 
                 on {{%contact}}.`ref_phone`={{%phones}}.id  where {{%contact}}.ref_org=:ref_org order by contactDate DESC LIMIT 3";
       
      $ret =  Yii::$app->db->createCommand($strSql, [':ref_org'=>$this->id])->queryAll();             
      return $ret;
    }
/*************************/   
    
    public function getCompanyPhones()
   {
          $ret =  Yii::$app->db->createCommand('SELECT phone, status from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->id])->queryAll();       
        return $ret;
   }   
   
/*************************/   
  public function getCurrentStatus()
  {
     $curUser=Yii::$app->user->identity;
      
     $res = [ 'cold_all'   => 0,
              'cold_now'   => 0,
              'cold_fail'  => 0, 
              'zakaz_all'  => 0, 
              'zakaz_now'  => 0, 
              'zakaz_fail' => 0, 
              'schet_all'  => 0, 
              'schet_now'  => 0, 
              'schet_fail' => 0, 
            ];

            


    $res['schet_fail'] =  Yii::$app->db->createCommand('SELECT count({{%schet}}.id) from {{%schet}} left join {{%calendar}} on {{%schet}}.refZakaz = {{%calendar}}.ref_zakaz
    where refManager=:refUser  AND DATE(event_date) < CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
     
    
    $res['schet_now'] =  Yii::$app->db->createCommand('SELECT count({{%schet}}.id) from {{%schet}} left join {{%calendar}} on {{%schet}}.refZakaz = {{%calendar}}.ref_zakaz
    where refManager=:refUser  AND DATE(event_date) = CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
    

    $res['schet_all'] =   Yii::$app->db->createCommand('SELECT count({{%schet}}.id) from {{%schet}} 
    where refManager=:refUser  AND isSchetActive = 1',[':refUser'=>$curUser->id] )->queryScalar();                   


    $res['zakaz_fail'] =  Yii::$app->db->createCommand('SELECT count({{%zakaz}}.id) from {{%zakaz}} left join {{%calendar}} on {{%zakaz}}.id = {{%calendar}}.ref_zakaz
    left join {{%orglist}} on {{%orglist}}.id = {{%zakaz}}.refOrg
    where refManager=:refUser  AND DATE(event_date) < CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
     
    
    $res['zakaz_now'] =  Yii::$app->db->createCommand('SELECT count({{%zakaz}}.id) from {{%zakaz}} left join {{%calendar}} on {{%zakaz}}.id = {{%calendar}}.ref_zakaz
    left join {{%orglist}} on {{%orglist}}.id = {{%zakaz}}.refOrg
    where refManager=:refUser  AND DATE(event_date) = CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
    

    $res['zakaz_all'] =   Yii::$app->db->createCommand('SELECT count({{%zakaz}}.id) from {{%zakaz}} 
    left join {{%orglist}} on {{%orglist}}.id = {{%zakaz}}.refOrg
    where refManager=:refUser  AND isActive = 1',[':refUser'=>$curUser->id] )->queryScalar();                   


    $res['cold_fail'] =  Yii::$app->db->createCommand('SELECT count({{%calendar}}.id) from {{%calendar}} 

    where ref_zakaz=0 AND ref_user=:refUser  AND DATE(event_date) < CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
     

    $res['cold_now'] =  Yii::$app->db->createCommand('SELECT count({{%calendar}}.id) from {{%calendar}} 

    where ref_zakaz=0 AND ref_user=:refUser  AND DATE(event_date) = CURRENT_DATE() and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   

    $res['cold_all'] =  Yii::$app->db->createCommand('SELECT count({{%calendar}}.id) from {{%calendar}} 

    where ref_zakaz=0 AND ref_user=:refUser  and eventStatus =1',[':refUser'=>$curUser->id] )->queryScalar();                   
    
        
    return   $res;
  }
/************************************************************/
public function printSimpleEventList($detailProvider, $model)
{

 if($this->detail == 0) $filterEventList = array(     "1" => "назначено",    "3" => "просрочено");
 if($this->detail == 1) $filterEventList = array(    "1" => "назначено",    "2" => "выполнено",    "3" => "просрочено");
 if($this->detail == 2) $filterEventList = array(    "1" => "назначено",    "3" => "просрочено");
 if($this->detail == 3) $filterEventList = array(    "1" => "назначено",    "2" => "выполнено",    "3" => "просрочено");
 
 
return \yii\grid\GridView::widget(
    [
        'dataProvider' => $detailProvider,
        'filterModel' => $model,    
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],            
            [
                'attribute' => 'event_date',
                'label'     => 'Дедлайн',
                'format' => ['datetime', 'php:d-m-Y'],
            ],            

            [
                'attribute' => 'eventTitle',
                'label'     => 'Тип события',
                'format' => 'raw',            
            ],            
            
            [
                'attribute' => 'refEvent',
                'filter'=>array(
                "0" => "Продолжить контакт",
                "1" => "Выяснение потребностей",                
                "3" => "Согласовать заявку",                
                "4" => "Резерв товара",
                "5" => "Выписать счет",
                "6" => "Ожидается: Счет получен клиентом",
                "7" => "Ожидается: Оплата произведена",
                "8" => "Ожидается: Гарантийные документы получены",
                "9" => "Ожидается: Деньги дошли",
                "10" => "Ожидается: Задание на отгрузку",
                "11" => "Ожидается: Поставка произведена",
                "12" => "Ожидается: Клиент подвердил поставку",
                "13" => "Ожидается: Отзыв получен",
                "14" => "Ожидается: Работа со счетом завершена",
                ),
                'label'     => 'Событие',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 return $model['eventNote'];                          
                },
            ],    

/*            [
                'attribute' => 'title',
                'label'     => 'Организация',
                'format' => 'raw',
            ],    */
            [
                'attribute' => 'title',
                'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                    
                  return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgId']."\",'childWin')' >".$model['title']."</a>";                          
                },
            ],        

            
            
/*            [
                'attribute' => 'eventStatus',
                'label'     => 'Статус',
                'format' => 'raw',
                'filter'=> $filterEventList,
                'value' => function ($model, $key, $index, $column) {
        
                 $statusTitles = array(    "1" => "назначено",    "2" => "выполнено",    "3" => "просрочено"    ); 
                                             
                 return $statusTitles[$model['eventStatus']];
                },
            ],        */
                
            [
                'attribute' => 'note',
                'label'     => 'Комментарий',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {
                    $r="";
                 if (!empty($model['contactDate'])){$r =  date("d.m.Y", strtotime($model['contactDate']))."<br>";}
                 if (!empty($model['contactFIO'])){$r.= $model['contactFIO']."<br>";}
                 if (!empty($model['note'])){$r.= $model['note'];}
                 return $r;
                }
            ],    

            [
                'attribute' => 'id',
                'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['eventStatus'] == 2) {return "<font color='ForestGreen'><b>Выполнено</b></font";}
                    $commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
                    switch ($model['ref_event'])
                     {
                        case 0: 
                        /*Холодный звонок*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-init&id=28409                            
                            return "<input ".$commStr." value='Продолжить'  onclick=\"javascript:openWin('cold/cold-init&id=".$model['orgId']."','childWin');\" />";    
                        break;

                        case 1: 
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-need&id=28417
                        /*Выяснение потребностей*/                            
                            return "<input ".$commStr." value='Потребности'  onclick=\"javascript:openWin('cold/cold-need&id=".$model['orgId']."','childWin');\" />";    
                        break;

                        case 2:                         
                        /*Первичная Заявка на счет*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-schet&id=27153
                        return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('cold/cold-schet&id=".$model['orgId']."','childWin');\" />";    
                        break;

                        case 3:                         
                        /*Заявка на счет*/
                        if ($model['zakazId'] == 0)
                        {
                        ////http://192.168.1.53/phone/web/index.php?r=market/market-zakaz-create&id=27153
                            return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('market/market-zakaz-create&id=".$model['orgId']."','childWin');\" />";    
                        }        
                        //http://192.168.1.53/phone/web/index.php?r=market/market-zakaz&orgId=29136&zakazId=8                        
                        return "<input ".$commStr." value='К заявке'  onclick=\"javascript:openWin('market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."','childWin');\" />";
                        break;
                        
                        case 4:                         
                        /*Резервирование товара*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-reserve-zakaz&orgId=28417&zakazId=12
                        return "<input ".$commStr." value='Резерв.'  onclick=\"javascript:openWin('market/market-reserve-zakaz&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />";
                        break;
                        
                        case 5:                         
                        /*Регистрация счета*/
                        return "<input ".$commStr." value='К счету'  onclick=\"javascript:openWin('market/market-reg-schet&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />";
                        break;

                        case 6: 
                        /*Ведение счета*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />";
                        break;

                        case 7: 
                        /*Поставка*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />";
                        break;
                    
                        case 8: 
                        /*Произвольный*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                                             
                         return "<input ".$commStr." value='Контакт'  onclick=\"javascript:openWin('site/reg-contact&singleWin=1&id=".$model['orgId']."','childWin');\" />";
                        break;


                    }
                                        
                },
            ],        
            [
                'attribute' => 'id',
                'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ($model['eventStatus'] == 2) return "&nbsp;";
                    return "<a class='btn btn-default' href='index.php?r=market/event-mark&id=".$model['id']."'>x</a>";
                  },
            ],        

        ],
    ]
); 
}  
/*******************************************/ 
 
 public function printContactEventList($provider, $model)
 {
    return \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                        
            [
                'attribute' => 'title',
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],        
                
            [
                'attribute' => 'contactDate',
                'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
                [':ref_org' => $model['id'],])->queryAll();
                $ret="";
                for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],        

            [
    
                'attribute' => 'nextContactDate',
                'label'     => 'Назначеная дата',
                //'format' => ['datetime', 'php:d.m.Y'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                 if(strtotime($model['nextContactDate']) < time()-8*60*60*24) return "";
                 return    date ('d.m.Y', strtotime($model['nextContactDate']));
                    
                }
                
            ],

            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',                
            ],

            [
                'attribute' => 'operator',
                'label'     => 'Оператор',                
            ],


            [
                'attribute' => 'Далее',
                'label' => 'Далее',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
                return "<input ".$commStr." value='Контакт'  onclick=\"javascript:openWin('site/reg-contact&singleWin=1&id=".$model['id']."','childWin');\" />";
                },
            ],        

            
           /* [
                'attribute' => 'Сдвинуть',
                'label' => 'Запланировать через:',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $val = "<nobr>";
                $val .="<input class='btn btn-primary local_btn' style='margin-right:10px; background:ForestGreen' type=button value=' Ok ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=0&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +1 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=1&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +7 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=7&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn' style='margin-left:10px; background:Maroon' type=button value=' +30 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=30&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .= "</nobr>";
                return  $val;
                },
            ],        
*/

            
        ],
    ]
); 
}
/*********************************************************************/
/************** Список событий связаннных со сделками ****************/
/*********************************************************************/
   public function getCurrentDealProvider($params)
   {

    $curUser=Yii::$app->user->identity;
        
    $countquery  = new Query();
    $query       = new Query();
            
/*            ->leftJoin('{{%phones}}','{{%phones}}.id = {{%contact}}.ref_phone')            */            
            
    $countquery->select (" count({{%calendar}}.id)")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%calendar}}.ref_org')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%calendar}}.ref_user')            
            ->distinct();
            
    
    $query->select (" {{%calendar}}.id as id, event_date, eventNote, {{%calendar}}.ref_event, eventStatus, {{%event}}.eventTitle,  
                      {{%contact}}.contactFIO, {{%contact}}.contactDate, {{%contact}}.note, 
                      {{%orglist}}.title, {{%orglist}}.id as orgId, {{%calendar}}.ref_zakaz as zakazId, userFIO ")
            ->from("{{%calendar}}")
            ->leftJoin('{{%event}}','{{%event}}.id = {{%calendar}}.ref_event')
            ->leftJoin('{{%contact}}','{{%contact}}.id = {{%calendar}}.ref_contact')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%calendar}}.ref_org')
            ->leftJoin('{{%user}}','{{%user}}.id = {{%calendar}}.ref_user')
            ->distinct(); 

    /*Cобытия либо мои, либо 'моих' клиентов */    
    
    if (($curUser->roleFlg & 0x0080) && ($this->type != 3) && ($this->userShow ==1)) 
    {
        /*Помошник, не сделано, показывать общее*/
        $query->andWhere("(isAvailableForHelper =1 OR {{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id.")"  );
        $countquery->andWhere("(isAvailableForHelper =1 OR {{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id.")"  );            
    }
    else
    {
        /*только мое */
        $query->andWhere("({{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id." )"  );    
        $countquery->andWhere("({{%calendar}}.ref_user=".$curUser->id." OR {{%orglist}}.refManager =". $curUser->id.")"  );    
    }
    /* Тип событий - Счета и заявки */
    $query->andWhere(['>', 'ref_event', 2]);
    $countquery->andWhere(['>', 'ref_event', 2]);
    $query->andWhere(['<', 'ref_event', 8]);
    $countquery->andWhere(['<', 'ref_event', 8]);

    if (empty ($this->type)) $this->type = 1; /*По умолчанию на сегодня*/
    
    switch ($this->type)
    {
    case 1:    
           /*Не выполнено на сегодня*/        
        $countquery->andWhere(['<=', 'DATE(event_date)', date('Y-m-d')/*'CURRENT_DATE()'*/]);    
             $query->andWhere(['<=', 'DATE(event_date)', date('Y-m-d')/*'CURRENT_DATE()'*/]);    
        $countquery->andWhere(['=', 'eventStatus', 1]);
             $query->andWhere(['=', 'eventStatus', 1]);        
        break;        
    case 2:    
        /*Потом не выполнено*/
        $countquery->andWhere(['>', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
             $query->andWhere(['>', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
        $countquery->andWhere(['=', 'eventStatus', 1]);
             $query->andWhere(['=', 'eventStatus', 1]);

        break;
    case 3:    
          /*Сегодня выполнено*/
        $countquery->andWhere(['=', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
             $query->andWhere(['=', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
        $countquery->andWhere(['=', 'eventStatus', 2]);           
             $query->andWhere(['=', 'eventStatus', 2]);        
        break;
    
    default: 
           /*Не выполнено на сегодня*/        
        $countquery->andWhere(['<=', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
             $query->andWhere(['<=', 'DATE(event_date)',  date('Y-m-d')/*'CURRENT_DATE()'*/]);    
        $countquery->andWhere(['=', 'eventStatus', 1]);
             $query->andWhere(['=', 'eventStatus', 1]);        
        break;        
        
    }
    
    
   $refEventArray=[
                "0" => "Продолжить контакт",
                "1" => "Выяснение потребностей",                
                "3" => "Согласовать заявку",                
                "4" => "Резерв товара",
                "5" => "Выписать счет",
                "6" => "Счет получен клиентом",
                "7" => "Оплата произведена",
                "8" => "Гарантийные документы получены",
                "9" => "Деньги дошли",
                "10" => "Задание на отгрузку",
                "11" => "Поставка произведена",
                "12" => "Клиент подвердил поставку",
                "13" => "Отзыв получен",
                "14" => "Работа со счетом завершена",
        ];
    
    if (($this->load($params) && $this->validate())) {
     /* Фильтр есть */
     $query->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
     $countquery->andFilterWhere(['like', '{{%event}}.eventTitle', $this->eventTitle]);
 
     $query->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'title', $this->title]);


     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     
      
     
     if ($this->refEvent!="") 
     { 
       $query->andFilterWhere(['like', 'eventNote', $refEventArray[$this->refEvent] ]);
       $countquery->andFilterWhere(['like', 'eventNote',$refEventArray[$this->refEvent] ]);
     }  
 
    } 
    
    
    $count = $countquery->createCommand()->queryScalar();
    $command = $query->createCommand();    

    
 //   $this->debug[] = $countquery->createCommand()->sql;

        $provider = new SqlDataProvider(
        [   'sql' => $command ->sql, 
            'params' => $command->params,    
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'event_date',
            'eventTitle',
            'eventStatus',
            'contactDate',
            'contactFIO',    
            'title',            
            'eventNote',
            'userFIO',
            'defaultOrder' => [    'event_date' => SORT_DESC ],
            ],
            ],
        ]);
    return $provider;
   }   
/*****************/   
/*******************************************/  
 public function printCurrentDealEventList($provider, $model)
 {
    return \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                                        
            [
                'attribute' => 'event_date',
                'label'     => 'Дата',
                'format' => ['datetime', 'php:d.m.Y'],                
            ],

            [
                'attribute' => 'title',
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgId']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],        

            [
                'attribute' => 'userFIO',
                'label' => 'Оператор',
                'format' => 'raw',
            ],        

    
            [
                'attribute' => 'Товар',
                'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $resList = Yii::$app->db->createCommand('SELECT good from {{%zakazContent}} where refZakaz=:refZakaz AND isActive=1 order by  id DESC LIMIT 2 ', 
                [':refZakaz' => $model['zakazId'],])->queryAll();
                $ret="";
                for($i=0;$i<count($resList);$i++){$ret.= $resList[$i]['good']."<br>\n";}
                return $ret;
                },
            ],        
            
            [
                'attribute' => 'Сумма',
                'label' => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $res = Yii::$app->db->createCommand('SELECT schetSumm, ref1C  from {{%schet}} where refZakaz=:refZakaz  order by  id DESC LIMIT 1 ', 
                [':refZakaz' => $model['zakazId'],])->queryAll();
                $ret="";
                
                if (count($res) == 0 || empty($res[0]['schetSumm'])) {
                $schetSumm = Yii::$app->db->createCommand('SELECT (count*value)  from {{%zakazContent}} where refZakaz=:refZakaz AND isActive=1', 
                [':refZakaz' => $model['zakazId'],])->queryScalar();
                
                return "По&nbsp;заявке: <font color='GoldenRod'>".$schetSumm."</font>";
                }                            
                else if  (empty($res[0]['ref1C'])) {return "По&nbsp;счету: <font color='red'>".round($res[0]['schetSumm'],2)."</font>";}
                else                                   {return "По&nbsp;счету: <font color='green'>".$res[0]['schetSumm']."</font>";}
                },
            ],        
                            

          /*  [
                'attribute' => 'eventTitle',
                'label'     => 'Тип события',
                'format' => 'raw',            
            ],            */
            
            [
                'attribute' => 'refEvent',
                'filter'=>array(
                "0" => "Продолжить контакт",
                "1" => "Выяснение потребностей",                
                "3" => "Согласовать заявку",                
                "4" => "Резерв товара",
                "5" => "Выписать счет",
                "6" => "Ожидается: Счет получен клиентом",
                "7" => "Ожидается: Оплата произведена",
                "8" => "Ожидается: Гарантийные документы получены",
                "9" => "Ожидается: Деньги дошли",
                "10" => "Ожидается: Задание на отгрузку",
                "11" => "Ожидается: Поставка произведена",
                "12" => "Ожидается: Клиент подвердил поставку",
                "13" => "Ожидается: Отзыв получен",
                "14" => "Ожидается: Работа со счетом завершена",
                ),
                'label'     => 'Статус',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 return $model['eventNote'];                          
                },
            ],    
                            
           /* [
                'attribute' => 'contactDate',
                'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
                [':ref_org' => $model['id'],])->queryAll();
                $ret="";
                for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                if(strtotime($model['nextContactDate']) > time()-8*60*60*24) 
                {
                    $ret.="Назначеная дата: ".date ('d.m.Y', strtotime($model['nextContactDate']));
                }
                    return $ret;
                },
            ],*/        
        
            [
                'attribute' => 'Счет',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $res = Yii::$app->db->createCommand('SELECT schetNum, schetDate  from {{%schet}} where refZakaz=:refZakaz  LIMIT 1 ', 
                [':refZakaz' => $model['zakazId'],])->queryAll();
                $ret="";
                
                if (count($res) == 0 ) {return "&nbsp;";}                            
                $ret = "№ ".$res[0]['schetNum']." от ".date ('d.m.Y', strtotime($res[0]['schetDate']));                 
                return $ret;
                },
            ],        


            [
                'attribute' => 'Оплата',
                'label'     => 'Оплата:',
                'format' => 'raw',            
                
                'value' => function ($model, $key, $index, $column) {
                
                $res = Yii::$app->db->createCommand('SELECT summOplata  from {{%schet}} where refZakaz=:refZakaz  LIMIT 1 ', 
                [':refZakaz' => $model['zakazId'],])->queryAll();
                $ret="";
                
                if (count($res) == 0 ) {return "&nbsp;";}                            
                $ret = $res[0]['summOplata'];                 
                return $ret;
                },

            ],            
        
                
            [
                'attribute' => 'Доставка',
                'label' => 'Доставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $refSchet = Yii::$app->db->createCommand('SELECT id  from {{%schet}} where refZakaz=:refZakaz  ', 
                [':refZakaz' => $model['zakazId'],])->queryScalar();
                
                if (empty($refSchet)) return "&nbsp;";
                $ret="";
                
                $supplyRequestList = Yii::$app->db->createCommand('SELECT id, requestDate
                from {{%request_supply}} where refSchet=:refSchet  ', 
                [':refSchet' => $refSchet])->queryAll();
                
                if(count($supplyRequestList) > 0)
                {
                $ret.="<a href='#' onclick=\"openWin('store/supply-request-new&id=".$supplyRequestList[0]['id']."&noframe=1','childwin');\">
                Отгрузка&nbsp;№&nbsp;".$supplyRequestList[0]['id']."&nbsp;".date("d.m", strtotime($supplyRequestList[0]['requestDate']))."</a><br>";
                }
                
                $deliveryList = Yii::$app->db->createCommand('SELECT id, requestNum,  requestStatus, requestDatePlanned, requestDateReal, 
                requestExecutor, requestStatus, deliverSum
                from {{%request_deliver}} where refSchet=:refSchet  ', 
                [':refSchet' => $refSchet])->queryAll();
                
                
                
                
                if (count($deliveryList) == 0 ) {return "&nbsp;";}        
                
                        
                
                for ($i=0; $i <count($deliveryList); $i++ )
                {
                $status="&nbsp;"; 
                switch ($deliveryList[$i]['requestStatus']) 
                    {
                    case 0:
                        $status = "<div class='local_lbl'>Создано</div> ";
                        break;
                    case 1:
                        $status = "<div class='local_lbl' style='border-color:#5bc0de;' >Подгот.  к отгр.</div> ";
                        break;
                    case 2:
                        $status = "<div class='local_lbl' style='border-color:LightSeaGreen;'>Выдано  эксп.</div> ";
                        break;
                    case 3:
                        $status = "<div class='local_lbl'  style='border-color:LimeGreen;'>В доставке</div> ";
                        break;
                    case 4: 
                        $status = "<div class='local_lbl' style='border-color:#449d44;'>Доставлено</div> ";
                        break;
                    }
                if ($deliveryList[$i]['requestStatus'] == 4)
                {    
                     $ret .= "<nobr>№ ".$deliveryList[0]['requestNum']." на сумму:".$deliveryList[$i]['deliverSum']. "</nobr><br>";                 
                     $ret .= "<nobr> дата ".date ('d.m', strtotime($deliveryList[0]['requestDateReal']))."</nobr><br> Исполнитель: ".$deliveryList[0]['requestExecutor']."<br> ".$status;
                }
                else            
                {    
                     $ret .= "<nobr>№ ".$deliveryList[0]['requestNum']." на сумму:".$deliveryList[$i]['deliverSum']. "</nobr><br>";                 
                     $ret .= "<nobr> дата ".date ('d.m', strtotime($deliveryList[0]['requestDatePlanned']))."</nobr><br> Исполнитель: ".$deliveryList[0]['requestExecutor']."<br> ".$status;

                }
            
                }
                return $ret;
                },
            ],        
        
    
        
            [
                'attribute' => 'id',
                'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                $val="";
/*                $val = "</div><br><div style='padding-top:4px;'><nobr>";
                $val .="<input class='btn btn-primary local_btn' style='margin-right:10px; background:ForestGreen' type=button value=' Ok ' onclick='javascript:openSwitchWin(\"market/deal-event-shift&shift=0&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +1 ' onclick='javascript:openSwitchWin(\"market/deal-event-shift&shift=1&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +7 ' onclick='javascript:openSwitchWin(\"market/deal-event-shift&shift=7&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn' style='margin-left:10px; background:Maroon' type=button value=' +30 ' onclick='javascript:openSwitchWin(\"market/deal-event-shift&shift=30&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .= "</nobr></div>";*/

                    
                    if ($model['eventStatus'] == 2) {return "<font color='ForestGreen'><b>Выполнено</b></font";}
                    $commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
                    switch ($model['ref_event'])
                     {
                        case 0: 
                        /*Холодный звонок*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-init&id=28409                            
                            return "<div align='center'><input ".$commStr." value='Продолжить'  onclick=\"javascript:openWin('cold/cold-init&id=".$model['orgId']."','childWin');\" />".$val;    
                        break;

                        case 1: 
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-need&id=28417
                        /*Выяснение потребностей*/                            
                            return "<div align='center'><input ".$commStr." value='Потребности'  onclick=\"javascript:openWin('cold/cold-need&id=".$model['orgId']."','childWin');\" />".$val;    
                        break;

                        case 2:                         
                        /*Первичная Заявка на счет*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-schet&id=27153
                        return "<div align='center'><input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('cold/cold-schet&id=".$model['orgId']."','childWin');\" />".$val;    
                        break;

                        case 3:                         
                        /*Заявка на счет*/
                        if ($model['zakazId'] == 0)
                        {
                        ////http://192.168.1.53/phone/web/index.php?r=market/market-zakaz-create&id=27153
                        return "<div align='center'><input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('market/market-zakaz-create&id=".$model['orgId']."','childWin');\" />".$val;    
                        }        
                        //http://192.168.1.53/phone/web/index.php?r=market/market-zakaz&orgId=29136&zakazId=8                        
                        return "<div align='center'><input ".$commStr." value='К заявке'  onclick=\"javascript:openWin('market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."','childWin');\" />".$val;
                        break;
                        
                        case 4:                         
                        /*Резервирование товара*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-reserve-zakaz&orgId=28417&zakazId=12
                        return "<div align='center'><input ".$commStr." value='Резерв.'  onclick=\"javascript:openWin('market/market-reserve-zakaz&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />".$val;
                        break;
                        
                        case 5:                         
                        /*Регистрация счета*/
                        return "<div align='center'><input ".$commStr." value='К счету'  onclick=\"javascript:openWin('market/market-reg-schet&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />".$val;
                        break;

                        case 6: 
                        /*Ведение счета*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<div align='center'><input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />".$val;
                        break;

                        case 7: 
                        /*Поставка*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<div align='center'><input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />".$val;
                        break;
                    
                        case 8: 
                        /*Произвольный*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                                             
                         return "<div align='center'><input ".$commStr." value='Контакт'  onclick=\"javascript:openWin('site/reg-contact&singleWin=1&id=".$model['orgId']."','childWin');\" />".$val;
                        break;
                    }
                                        
                },
            ],        


/*            [
                'attribute' => 'Сдвинуть',
                'label' => 'Запланировать через:',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $val = "<nobr>";
                $val .="<input class='btn btn-primary local_btn' style='margin-right:10px; background:ForestGreen' type=button value=' Ok ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=0&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +1 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=1&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn'  type=button value=' +7 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=7&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .="<input class='btn btn-primary local_btn' style='margin-left:10px; background:Maroon' type=button value=' +30 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=30&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
                $val .= "</nobr>";
                return  $val;
                },
            ],        
*/
            
            
            
        ],
    ]
); 
}
/********************/
public function prepareClientListData($params)
   {
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов */
    
    $countquery->select ("count(distinct org.id)")
                 ->from("{{%orglist}} as org")
                 ->leftJoin("{{%user}}", "{{%user}}.id = org.refManager")
                 ->leftJoin("(SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg from {{%oplata}} group by refOrg) as opl", "opl.refOrg = org.id")
                 ->leftJoin("(SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = org.id ")
                 ->leftJoin("(SELECT DISTINCT rik_schet.refOrg, good as goodlist from rik_schet, rik_zakazContent where  rik_schet.refZakaz = rik_zakazContent.refZakaz AND rik_schet.summSupply > 0) as goods ", "goods.refOrg =  org.id ")                 
                 ->leftJoin("(SELECT count({{%zakaz}}.id) as activity, {{%zakaz}}.refOrg from {{%zakaz}} left join {{%schet}} on  {{%zakaz}}.id={{%schet}}.refZakaz where (isActive=1 OR isSchetActive = 1) GROUP BY refOrg) as act ", "act.refOrg =  org.id ")                 
                 
                 ;
                  
     $query->select([
     '{{%orglist}}.id',
     'title', 
     'userFIO', 
     'oplataCnt', 
     'supplyCnt', 
     'ifnull(oplataSum, 0) as oplata',
     'ifnull(supplySum, 0) as supply',     
     '(ifnull(supplySum, 0) - ifnull(oplataSum, 0)) as balance', 
    'lastOplate', 
    'lastSupply',     
    '{{%orglist}}.contactPhone',
    '{{%orglist}}.contactEmail',
    'ifnull(activity,0) as active'
     ]) ->from("{{%orglist}}")
        ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")
        ->leftJoin("(SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg from {{%oplata}} group by refOrg) as opl", "opl.refOrg = {{%orglist}}.id")
        ->leftJoin("(SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply from {{%supply}} group by refOrg) as supl ", "supl.refOrg = {{%orglist}}.id ")
        ->leftJoin("(SELECT DISTINCT rik_schet.refOrg, good as goodlist from rik_schet, rik_zakazContent where  rik_schet.refZakaz = rik_zakazContent.refZakaz AND rik_schet.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
        ->leftJoin("(SELECT count({{%zakaz}}.id) as activity, {{%zakaz}}.refOrg from {{%zakaz}} left join {{%schet}} on  {{%zakaz}}.id={{%schet}}.refZakaz where (isActive=1 OR isSchetActive = 1) GROUP BY refOrg) as act ", "act.refOrg =  {{%orglist}}.id ")                 
        ->distinct()
        ;
            
      $countquery->where(" isOrgActive =1 ");            
      $query->where("  isOrgActive =1 ");            
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]);

        $query->andFilterWhere(['like', 'title', $this->title]);
        $countquery->andFilterWhere(['like', 'title', $this->title]);
          
        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        $countquery->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        
        /*"1" => "Нам должны",*/
        if ($this->balance == 1 ) 
        {
            $query->andFilterWhere(['>', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);
            $countquery->andFilterWhere(['>', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);            
        }
        /*"2" => "Нет долгов",                */
        if ($this->balance == 2 ) 
        {
            $query->andFilterWhere(['<=', 'ABS(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);
            $countquery->andFilterWhere(['<=', 'ABS(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', 0.9]);            
        }
        /*"3" => "Мы должны",*/
        if ($this->balance == 3 ) 
        {
            $query->andFilterWhere(['<', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', -0.9]);
            $countquery->andFilterWhere(['<', '(ifnull(supplySum, 0) - ifnull(oplataSum, 0))', -0.9]);            
        }
        
        
     }
     
     /*только свое*/
     if ($this->type == 11)
     {
            $curUser=Yii::$app->user->identity;         
            $query->andFilterWhere(['=', '{{%user}}.id', $curUser->id]);
            $countquery->andFilterWhere(['=', '{{%user}}.id', $curUser->id]);            
         
     }

     
     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();

   }

   public function getClientListProvider($params)
   {

        $this->prepareClientListData($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'title',
            'userFIO',            
            'oplata', 
            'supply',
            'balance',
            'lastOplate',            
            'lastSupply', 
            'active'
            ],
            'defaultOrder' => [    'title' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

 
public function printClientList($provider, $model)
 {
        
    $grid =\yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                
            [
                'attribute' => 'title',
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],        
    
            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                $m = $model['userFIO'];
                if (empty($model['userFIO'])) $m = "N/A";
                $ret = "<b>Назначен: ".$model['userFIO']."</b></br>";
                
                
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO, MAX(contactDate) AS lastContact from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND {{%contact}}.ref_org = :ref_org group by userFIO ORDER BY lastContact DESC", 
                [                
                ':ref_org' => $model['id'],
                ])->queryAll();
                
                $ret .= "<table border=0>";
                $cntL = count ($resList);
                $cn=0;
                for ($i=0; $i < $cntL ; $i++)
                {
                    if ($i >= 3) $cn+=$resList[$i]['cur'];
                    else    
                    $ret .= "<tr><td><nobr>".$resList[$i]['userFIO']."</nobr></td><td>". $resList[$i]['cur']."</td><td><nobr>".date("d-m-Y",strtotime($resList[$i]['lastContact']))."</nobr></td></tr>";
                }                
                $ret .= "</table>";
                if ($cntL > 3) $ret .="<i> Еще ".$cn." контактов </i>";  
                return $ret;
                },

                
            ],
 
            [
                'attribute' => 'balance',
                'label'     => 'Сверка/<br>Чек',                
                'encodeLabel' => false,
                'format' => 'raw',
                'filter'=>array(
                "1" => "Нам должны",
                "2" => "Нет долгов",                
                "3" => "Мы должны",                
                ),
                'value' => function ($model, $key, $index, $column) {
                    
                $avgCheck = Yii::$app->db->createCommand("SELECT AVG(summSupply) as av_schet from rik_schet where refOrg = :ref_org", 
                [':ref_org' => $model['id'],])->queryScalar();
    
                 $ret =    "<a href=# onclick='openWin(\"site/org-deal-reestr&orgId=".$model['id']."\", \"childwin\")' >".number_format($model['balance'], 2, '.', '&nbsp;')."</a>";
                 $ret .= "<br>".number_format($avgCheck,2,'.','&nbsp;');
                 return $ret;
                }
            ],

            
            [
                'attribute' => 'Sdelka',
                'label'     => 'Сделка',                
                'encodeLabel' => false,
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                    
                $strSql = "SELECT {{%zakaz}}.id, formDate, IFNULL(SUM({{%zakazContent}}.count * {{%zakazContent}}.value), 0) as zakazSum,
                           schetNum, schetSumm, schetDate, ifnull(ref1C, 0) as ref, ifnull({{%schet}}.refZakaz, '-') as refZakaz  
                           from {{%zakaz}} left join {{%zakazContent}} on {{%zakazContent}}.refZakaz = {{%zakaz}}.id
                           left join {{%schet}} on {{%schet}}.refZakaz = {{%zakaz}}.id
                           where ({{%zakaz}}.isActive  = 1 or {{%schet}}.isSchetActive) and {{%zakaz}}.refOrg = :ref_org 
                           group by {{%zakaz}}.id, {{%schet}}.id
                           ORDER BY formDate DESC
                           " ;
                           
                $resList = Yii::$app->db->createCommand($strSql,[':ref_org' => $model['id'],])->queryAll();
                $ret = "<table border=0>";
                $cntL= count ($resList);
                for ($i=0; $i < $cntL; $i++)
                {
                    $style =" style='background: LightGreen;'";
                    if ($resList[$i]['ref'] =='-') $style =" style='background: Yellow;'";
                            
                    if ($resList[$i]['refZakaz'] ==0)
                    $ret .= "<div ><nobr> Заказ № ".$resList[$i]['id']." на сумму ".$resList[$i]['zakazSum']."</nobr></div>";
                    else 
                    $ret .= "<div".$style."><nobr> Счет № ".$resList[$i]['schetNum']." на сумму ".$resList[$i]['schetSumm']."</nobr></div>";
                  if ($i >= 5) break;
                }
                $ret .= "</table>";
                if ($i < $cntL) $ret .= "<i> Еще ".($cntL - $i). "...</i>";
                return $ret;
                }
            ],


            [    
                'attribute' => 'lastSupply',
                'label'     => 'Отгрузка/<br>оплата',
                'encodeLabel' => false,
                'format' => 'raw',                            
                 'value' => function ($model, $key, $index, $column) {
                   if (empty($model['lastSupply'])) $sup ="Нет отгрузки";  
                   else $sup = date('d.m.Y', strtotime($model['lastSupply']));
                   if (empty($model['lastOplate'])) $op ="Нет оплаты";  
                   else $op = date('d.m.Y', strtotime($model['lastOplate']));
                  return $sup."<br>".$op;
                }
            ],


            
            
            [
                'attribute' => 'fltGood',
                'label'     => 'Товары',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $strSql  = "SELECT DISTINCT good, count(good) as C, SUM({{%zakazContent}}.count) as S from {{%schet}}, {{%zakazContent}} ";
                $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0 AND  {{%schet}}.refOrg = :ref_org ";
                $strSql .= "group by {{%schet}}.refOrg,  {{%zakazContent}}.good order by {{%schet}}.refOrg, count(good) DESC, SUM({{%zakazContent}}.count) DESC LIMIT 3";
                                  
                $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['id'],])->queryAll();
                /*echo "<pre>";
                print_r($resList);
                echo "</pre>";*/
                $ret="";
                for($i=0;$i<count($resList);$i++)
                {
                    $ret.= $resList[$i]['good']."<br>\n";
                }
                        
                 return $ret;
                }
            ],
            
           
            
        ],
    ]
); 

return $grid;

}
/*********************************************/


 
 
 
 
 /***/
 }
