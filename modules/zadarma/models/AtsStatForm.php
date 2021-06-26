<?php

namespace app\modules\zadarma\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\zadarma\models\TblPhones;
use app\modules\zadarma\models\TblAtsLog;

use app\models\ContactList;

/**
 * ColdMainForm - модель стартовой формы менеджера холодных звонков
 */
 
 class AtsStatForm extends Model
{
    

    public $debug;
    public $orgTitle;
    public $external_num;
    public $internal_num;
    public $call_start;    
    public $event;
    public $orgINN;
    public $orgKPP;
    public $disposition;
    public $duration;
    public $internal_id;

    public $selOrgTitle;
    public $selOrgINN;
    public $selOrgKPP;
             
    public $fltDetail=0;
    public $fltDate;
    
    public $orgRef;       

             
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            [['orgTitle', 'external_num', 'internal_id', 'internal_num', 'call_start', 'event', 'orgINN', 'orgKPP', 'disposition', 'duration',
            'selOrgTitle','selOrgINN', 'selOrgKPP'
             ], 'safe'],            
        ];
    }
/**************************/
public function setOrgToRecord($id, $orgRef)
{
   $recordLog = TblAtsLog::FindOne($id);
   if(empty($recordLog)) return;
   $recordLog->orgRef = $orgRef;
   $recordLog->save();
   
   $recodPhone= TblPhones::FindOne(['ref_org' => $orgRef, 'phone' => $recordLog->external_num ]);  
   if (!empty($recodPhone)) return;
   else $recodPhone = new  TblPhones();
   $recodPhone->ref_org = $orgRef;
   $recodPhone->phone = $recordLog->external_num ;
   $recodPhone->save();
}


public function setContactToRecord($id, $contactRef)
{
   $ret=[
   'res' => false,
   'val' => '',
   ];

   $record = ContactList::FindOne($contactRef);
   if(empty($record)) return $ret;
   $record ->refAts = $id;
   $record ->save();

    $ret['res'] = true;
    $ret['val'] = $record ->refAts;
    return $ret;
}

/******************/
public function getMonthAtsList($year){
    
     $year = intval($year);    

    $n = 12;
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'MONTH(call_start) as m',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['MONTH(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere("( (event = 'NOTIFY_END')  )");   
         
    $list = $query->createCommand()->queryAll();    
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $m=$list[$i]['m'];
       $res[$m]['all']=$list[$i]['N'] ; 
    }


    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'MONTH(call_start) as m',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['MONTH(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere("( (event = 'NOTIFY_END')  )");   
    $query->andWhere("( orgRef = 0 || disposition = 'cancel'  )");   
         
    $list = $query->createCommand()->queryAll();    
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $m=$list[$i]['m'];
       $res[$m]['err']=$list[$i]['N'] ; 
    }

    
   /* echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  
/******************/
public function getDayAtsList($month, $year){
    
    $year = intval($year);
    $month = intval($month);

    $n = date('t',strtotime($year."-".$month."-01"));
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['cancel']=0; $res[$i]['in']=0; $res[$i]['out']=0;}       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(call_start) as d',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['DATE(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere ('MONTH(call_start) = '.$month);
    $query->andWhere("( (event = 'NOTIFY_END')  )");   

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['in']=$list[$i]['N'] ; 
    }


    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(call_start) as d',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['DATE(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere ('MONTH(call_start) = '.$month);
    $query->andWhere("( (event = 'NOTIFY_OUT_END')  )");   

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['out']=$list[$i]['N'] ; 
    }


    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(call_start) as d',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['DATE(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere ('MONTH(call_start) = '.$month);
    $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END')  )");   
    $query->andWhere(" orgRef = 0 ");   

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['err']=$list[$i]['N'] ; 
    }


    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(call_start) as d',           
        ])
         ->from("{{%ats_log}}")
         ->distinct()
         ->groupBy(['DATE(call_start)']);
    $query->andWhere ('YEAR(call_start) = '.$year);
    $query->andWhere ('MONTH(call_start) = '.$month);
    $query->andWhere("( (event = 'NOTIFY_END') )");   
    $query->andWhere("disposition = 'cancel' ");   

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['err']=$list[$i]['N'] ; 
    }
    
 /*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  
/********************/

public function getPhoneDayStatistics()
{
 $res=[
   'phoneNum' => 0,
   'dayError' => 0,
   'dayGood' => 0,
   'dayAll' => 0,  
   'dayNow' => 0,  
 ];
 
 $fltDate = date('Y-m-d', strtotime($this->fltDate));

    $query  = new Query();
    $query->select ('COUNT({{%ats_log}}.id)')
                ->from("{{%ats_log}}")           
                ->andWhere(['=', 'DATE({{%ats_log}}.call_start)', date('Y-m-d')])
                ;    
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
                
    $res['dayNow']=$query->createCommand()->queryScalar();                

 
    $query  = new Query();
    $query->select ('COUNT({{%ats_log}}.id)')
                ->from("{{%ats_log}}")           
                ->andWhere(['=', '{{%ats_log}}.orgRef', 0])
                ->andWhere(['=', 'DATE({{%ats_log}}.call_start)', $fltDate])
                ;    
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
                
    $res['dayError']=$query->createCommand()->queryScalar();                
    
    $query  = new Query();
    $query->select ('COUNT({{%ats_log}}.id)')
                ->from("{{%ats_log}}")           
                ->andWhere(['>', '{{%ats_log}}.orgRef', 0])
                ->andWhere(['=', 'DATE({{%ats_log}}.call_start)', $fltDate])
                ;        
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
                
    $res['dayGood']=$query->createCommand()->queryScalar();                
    
    $res['dayAll']=$res['dayGood']+$res['dayError'];

    
    $query  = new Query();
    $query->select ('COUNT(DISTINCT ({{%ats_log}}.external_num) )')
                ->from("{{%ats_log}}")           
                ->andWhere(['=', 'DATE({{%ats_log}}.call_start)', $fltDate])
                ;    
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
                
    $res['phoneNum']=$query->createCommand()->queryScalar();                
     
    return $res;
}    
    
  /**************************/
public function getPhoneStatisticsProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
             '{{%ats_log}}.id',
             'call_start',
             'external_num',
             'internal_num',
             'internal_id',
             'disposition',
             'duration',
             'event',
             'orgRef',
             '{{%orglist}}.title as orgTitle',
             'orgINN',
             'orgKPP',         
             'userFIO',
             '{{%contact}}.id as refContact',
             '{{%contact}}.eventType',
             ])
            ->from("{{%ats_log}}")            
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%ats_log}}.orgRef")
            ->leftJoin("{{%contact}}","{{%contact}}.refAts = {{%ats_log}}.id")
            ->leftJoin("{{%user}}","{{%user}}.phoneInternаl = {{%ats_log}}.internal_id")
            ->distinct()
;

    $countquery  = new Query();
    $countquery->select ([
             'Count(DISTINCT({{%ats_log}}.id))',
             ])
            ->from("{{%ats_log}}")            
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = {{%ats_log}}.orgRef")
            ->leftJoin("{{%user}}","{{%user}}.phoneInternаl = {{%ats_log}}.internal_id")
            ->distinct()
;
    
    $fltDate = date('Y-m-d', strtotime($this->fltDate));
            $query->andWhere(['=', 'DATE({{%ats_log}}.call_start)', $fltDate]);    
            $countquery->andWhere(['=', 'DATE({{%ats_log}}.call_start)', $fltDate]);    
    
    
        
        switch($this->fltDetail)
        {
          case 1:
            $query->andFilterWhere(['>', '{{%ats_log}}.orgRef', 0]);    
            $countquery->andFilterWhere(['>', '{{%ats_log}}.orgRef', 0]);    
          break;

          case 2:
            $query->andFilterWhere(['=', '{{%ats_log}}.orgRef', 0]);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.orgRef', 0]);    
          break;
        
        }          	      




    
             
    if (($this->load($params) && $this->validate())) {
        
/*        $query->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);    
        $countquery->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);        */

        
        switch($this->duration)
        {
        
          case 1:
            $query->andFilterWhere(['=', '{{%ats_log}}.duration', 0]);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.duration', 0]);    
          break;
          
          case 2:
            $query->andFilterWhere(['>', '{{%ats_log}}.duration', 0]);    
            $countquery->andFilterWhere(['>', '{{%ats_log}}.duration', 0]);    
          
            $query->andFilterWhere(['<', '{{%ats_log}}.duration', 30]);    
            $countquery->andFilterWhere(['<', '{{%ats_log}}.duration', 30]);    
          break;

          case 3:
            $query->andFilterWhere(['>', '{{%ats_log}}.duration', 30]);    
            $countquery->andFilterWhere(['>', '{{%ats_log}}.duration', 30]);    
          break;
        
        }          	      
        
        switch($this->disposition)
        {
          case 1:
            $query->andFilterWhere(['=', '{{%ats_log}}.disposition', 'answered']);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.disposition', 'answered']);    
          break;

          case 2:
            $query->andFilterWhere(['=', '{{%ats_log}}.disposition', 'cancel']);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.disposition', 'cancel']);    
          break;

          case 3:
            $query->andFilterWhere(['=', '{{%ats_log}}.disposition', 'busy']);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.disposition', 'busy']);    
          break;
          
          case 4:
            $query->andFilterWhere(['=', '{{%ats_log}}.disposition', 'failed']);    
            $countquery->andFilterWhere(['=', '{{%ats_log}}.disposition', 'failed']);    
          break;
                  
        }          	      
        
          $query->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);    
        $countquery->andFilterWhere(['Like', '{{%orglist}}.title', $this->orgTitle]);        

  
                
/*        $query->andFilterWhere(['Like', '{{%orglist}}.orgINN', $this->orgINN]);    
        $countquery->andFilterWhere(['Like', '{{%orglist}}.orgINN', $this->orgINN]);        
        
                
        $query->andFilterWhere(['Like', '{{%orglist}}.orgKPP', $this->orgKPP]);    
        $countquery->andFilterWhere(['Like', '{{%orglist}}.orgKPP', $this->orgKPP]);        
*/                
                
        $query->andFilterWhere(['Like', '{{%ats_log}}.external_num', $this->external_num]);    
        $countquery->andFilterWhere(['Like', '{{%ats_log}}.external_num', $this->external_num]);        

        $query->andFilterWhere(['Like', '{{%ats_log}}.internal_num', $this->internal_num]);    
        $countquery->andFilterWhere(['Like', '{{%ats_log}}.internal_num', $this->internal_num]);        
          
        $query->andFilterWhere(['=', 'DATE(call_start)', $this->call_start]);    
        $countquery->andFilterWhere(['=', 'DATE(call_start)', $this->call_start]);        
        
        $query->andFilterWhere(['=', 'internal_id', $this->internal_id]);    
        $countquery->andFilterWhere(['=', 'internal_id', $this->internal_id]);        
        
        
    }
    
        switch($this->event)
        {
          case 1:
            $query->andWhere("(event = 'NOTIFY_OUT_END')");    
            $countquery->andWhere("(event = 'NOTIFY_OUT_END')");    
          break;

          case 2:
            $query->andWhere("(event = 'NOTIFY_END')");    
            $countquery->andWhere("(event = 'NOTIFY_END')");    
          break;
                    
          default:
            $query->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
            $countquery->andWhere("( (event = 'NOTIFY_END') OR (event = 'NOTIFY_OUT_END') )");    
        
        }          	      


    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'call_start',
             'external_num',
             'internal_num',
             'internal_id',
             'disposition',
             'duration',
             'event',
             'orgTitle',
             'orgINN',
             'orgKPP',         
             'userFIO',            
             ],
            'defaultOrder' => [ 'call_start' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/

  /**************************/
public function getOrgListProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
             'id as selOrgRef',
             '{{%orglist}}.title as selOrgTitle',
             'orgINN  as selOrgINN',
             'orgKPP  as selOrgKPP',         
             ])
            ->from("{{%orglist}}")
            ->distinct()
    ;

    $countquery  = new Query();
    $countquery->select ([
             'Count(DISTINCT({{%orglist}}.id))',
             ])
            ->from("{{%orglist}}")
            ->distinct()
    ;
             
    if (($this->load($params) && $this->validate())) {
        

            $query->andFilterWhere(['Like', '{{%orglist}}.title', $this->selOrgTitle]);    
            $countquery->andFilterWhere(['Like', '{{%orglist}}.title', $this->selOrgTitle]);    
                
            $query->andFilterWhere(['Like', '{{%orglist}}.orgINN', $this->selOrgINN]);    
            $countquery->andFilterWhere(['Like', '{{%orglist}}.orgINN', $this->selOrgINN]);        
                        
            $query->andFilterWhere(['Like', '{{%orglist}}.orgKPP', $this->selOrgKPP]);    
            $countquery->andFilterWhere(['Like', '{{%orglist}}.orgKPP', $this->selOrgKPP]);        
                
                
    }
    

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'selOrgTitle',
             'selOrgINN',
             'selOrgKPP',  
            ],        
             'defaultOrder' => [ 'selOrgTitle' => SORT_ASC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/
  
  /**************************/
public function getContactListProvider($params)
   {
   
    $query  = new Query();
    $query->select ([
             '{{%contact}}.id as selContactRef',
             'contactDate',
             'note',
             'ref_phone',
             'phone',
             'eventType',
             '{{%contact}}.ref_org'
             ])
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone")
            ->distinct()
    ;

    $countquery  = new Query();
    $countquery->select ([
             'Count(DISTINCT({{%contact}}.id))',
             ])
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone")
            ->distinct()
    ;

                $query->andWhere(['=', '{{%contact}}.ref_org', $this->orgRef]);    
            $countquery->andWhere(['=', '{{%contact}}.ref_org', $this->orgRef]);    
                 
    if (($this->load($params) && $this->validate())) {
                
    }
    

    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 7,
            ],        
            
            'sort' => [            
            'attributes' => [            
             'selContactRef',
             'contactDate',            
             'phone',                  
            ],        
             'defaultOrder' => [ 'contactDate' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /*********************************************************/

  
  /**************************/    
  
  
  /************End of model*******************/ 
 }
