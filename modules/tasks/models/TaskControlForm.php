<?php

namespace app\modules\tasks\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper; 
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * TaskControlForm
 */
class TaskControlForm extends Model
{
public $debug=[];
public $dataArray=[];
public $curTime=0;

public $orgTitle;                   
public $userFIO;
public $userRef=0;      
public $calN; 
public $cntN;
public $mailN;
public $atsN; 
public $atsD;             

public $sumData=[];
            
    public function rules()
    {
        return [
            [['orgTitle','userFIO','calN','mailN','atsN','atsD','cntN' ], 'safe'],
        ];
    }
  

   
public function getUserList()		      
{
    
    if ($this->curTime == 0) $this->curTime = time();
    $strDate = date('Y-m-d', $this->curTime);
    
    $strSqlCalendar = "(SELECT count(id) as calN, ref_org from {{%calendar}} WHERE date(event_date) =:DATE GROUP BY ref_org ) as cal\n";
    $strSqlContact  = "(SELECT count(id) as cntN, ref_org from {{%contact}} WHERE date(contactDate) =:DATE GROUP BY ref_org ) as cnt\n";
    $strSqlMail     = "(SELECT count(id) as mailN, refOrg from {{%mail}} WHERE date(msgDate) =:DATE  GROUP BY refOrg ) as mail\n";
    $strSqlAts      = "(SELECT count(id) as atsN, orgRef from {{%ats_log}} WHERE 
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    disposition = 'answered' AND
    date(call_start) =:DATE  GROUP BY orgRef ) as ats";
    
    $query  = new Query();    
    $query->select ([
            '{{%user}}.id',
            'userFIO',   
            ])
            ->from("{{%user}}")
            ->leftJoin("{{%orglist}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%orglist}}.id = cal.ref_org")                                    
            ->leftJoin($strSqlContact, "{{%orglist}}.id = cnt.ref_org")                        
            ->leftJoin($strSqlMail, "{{%orglist}}.id = mail.refOrg")                        
            ->leftJoin($strSqlAts, "{{%orglist}}.id = ats.orgRef")  
            ->addParams([':DATE' => $strDate])            
            ->distinct();
            ;   
    $query->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");         
    $list = $query->createCommand()->queryAll();                       
   
    $res = ['-1' => 'Все', '-2' => 'Непривязанные'];
    
    for ($i = 0; $i<count($list); $i++ )
    {    
        $res[$list[$i]['id']]= $list[$i]['userFIO'];
    }
    return $res;
}

public function getTaskControlProvider($params)		      
{
    
    if ($this->curTime == 0) $this->curTime = time();
    $strDate = date('Y-m-d', $this->curTime);
    
    $strSqlCalendar = "(SELECT count(id) as calN, ref_org from {{%calendar}} WHERE date(event_date) =:DATE GROUP BY ref_org ) as cal\n";
    
    $strSqlContact  = "(SELECT count(id) as cntN, sum(isChangeStatus) as chngStatus, ref_org from {{%contact}} WHERE date(contactDate) =:DATE GROUP BY ref_org ) as cnt\n";
    
    $strSqlMail     = "(SELECT count(id) as mailN, refOrg from {{%mail}} WHERE date(msgDate) =:DATE  GROUP BY refOrg ) as mail\n";
/*
SELECT * from `rik_ats_log` WHERE 
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    `internal` = `internal_id` AND disposition = 'answered'
    date(call_start) ='2020-03-06' GROUP BY orgRef
*/
    $strSqlAts      = "(SELECT count(id) as atsN, orgRef from {{%ats_log}} WHERE 
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    disposition = 'answered' AND
    date(call_start) =:DATE  GROUP BY orgRef ) as ats";
/*`internal` = `internal_id`  AND */
    $strSqlDuration = "(SELECT count(id) as atsD, orgRef from {{%ats_log}} WHERE 
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    disposition = 'answered' AND duration > 30 AND
    date(call_start) =:DATE  GROUP BY orgRef ) as dur";
      
     
      
    
    $query  = new Query();    
    $query->select ([
            '{{%orglist}}.id as refOrg',                   
            '{{%orglist}}.title as orgTitle',                   
            'userFIO',   
            'ifnull(calN,0) as calN', 
            'ifnull(cntN,0) as cntN',
            'ifnull(mailN,0) as  mailN',
            'ifnull(atsN,0) as atsN', 
            'ifnull(atsD,0) as atsD', 
            'ifnull(chngStatus,0) as chngStatus',            
            ])
            ->from("{{%orglist}}")
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%orglist}}.id = cal.ref_org")                                    
            ->leftJoin($strSqlContact, "{{%orglist}}.id = cnt.ref_org")                        
            ->leftJoin($strSqlMail, "{{%orglist}}.id = mail.refOrg")                        
            ->leftJoin($strSqlAts, "{{%orglist}}.id = ats.orgRef")  
            ->leftJoin($strSqlDuration, "{{%orglist}}.id = dur.orgRef")  
            ->addParams([':DATE' => $strDate])            
            ->distinct();
            ;            
            
    $query->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");        
   
    $countquery  = new Query();
    $countquery->select ("count({{%orglist}}.id)")
            ->from("{{%orglist}}")
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%orglist}}.id = cal.ref_org") 
           ->leftJoin($strSqlContact, "{{%orglist}}.id = cnt.ref_org")            
            ->leftJoin($strSqlMail, "{{%orglist}}.id = mail.refOrg")                        
            ->leftJoin($strSqlAts, "{{%orglist}}.id = ats.orgRef")  
            ->leftJoin($strSqlDuration, "{{%orglist}}.id = dur.orgRef")  
            ->addParams([':DATE' => $strDate])            
            ->distinct();
            ;            


    $sumquery  = new Query();    
    $sumquery->select ([
            'sum(ifnull(calN,0)) as calN', 
            'sum(ifnull(cntN,0)) as cntN',
            'sum(ifnull(mailN,0)) as  mailN',
            'sum(ifnull(atsN,0)) as atsN', 
            'sum(ifnull(atsD,0)) as atsD', 
            'sum(ifnull(chngStatus,0)) as chngStatus',            
            ])
            ->from("{{%orglist}}")
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%orglist}}.id = cal.ref_org")                                    
            ->leftJoin($strSqlContact, "{{%orglist}}.id = cnt.ref_org")                        
            ->leftJoin($strSqlMail, "{{%orglist}}.id = mail.refOrg")                        
            ->leftJoin($strSqlAts, "{{%orglist}}.id = ats.orgRef")  
            ->leftJoin($strSqlDuration, "{{%orglist}}.id = dur.orgRef")  
            ->addParams([':DATE' => $strDate])            
            ->distinct();
            ;            
            
    $sumquery->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");        
                        
            
                        
    $countquery->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");        

    if (($this->load($params) && $this->validate() )) 
     {
          
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
        $sumquery->andFilterWhere(['like', 'title', $this->orgTitle]);
    
        switch ($this->calN)
        {
         case '2':
            $query->andFilterWhere(['>', 'ifnull(calN,0)', 0]);
            $countquery->andFilterWhere(['>', 'ifnull(calN,0)', 0]);
            $sumquery->andFilterWhere(['>', 'ifnull(calN,0)', 0]);
         break;                       
         case '3':
            $query->andFilterWhere(['=', 'ifnull(calN,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(calN,0)', 0]);
            $sumquery->andFilterWhere(['=', 'ifnull(calN,0)', 0]);
         break;                        
        }

        switch ($this->cntN)
        {
         case '2':
            $query->andFilterWhere(['>', 'ifnull(cntN,0)', 0]);
            $countquery->andFilterWhere(['>', 'ifnull(cntN,0)', 0]);
            $sumquery->andFilterWhere(['>', 'ifnull(cntN,0)', 0]);
         break;                       
         case '3':
            $query->andFilterWhere(['=', 'ifnull(cntN,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(cntN,0)', 0]);
            $sumquery->andFilterWhere(['=', 'ifnull(cntN,0)', 0]);
         break;                        
        }
                
        

        switch ($this->atsN)
        {
         case '2':
            $query->andFilterWhere(['>', 'ifnull(atsN,0)', 0]);
            $countquery->andFilterWhere(['>', 'ifnull(atsN,0)', 0]);
            $sumquery->andFilterWhere(['>', 'ifnull(atsN,0)', 0]);
         break;                       
         case '3':
            $query->andFilterWhere(['=', 'ifnull(atsN,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(atsN,0)', 0]);
            $sumquery->andFilterWhere(['=', 'ifnull(atsN,0)', 0]);
         break;                        
        }

        switch ($this->atsD)
        {
         case '2':
            $query->andFilterWhere(['>', 'ifnull(atsD,0)', 0]);
            $countquery->andFilterWhere(['>', 'ifnull(atsD,0)', 0]);
            $sumquery->andFilterWhere(['>', 'ifnull(atsD,0)', 0]);
         break;                       
         case '3':
            $query->andFilterWhere(['=', 'ifnull(atsD,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(atsD,0)', 0]);
            $sumquery->andFilterWhere(['=', 'ifnull(atsD,0)', 0]);
         break;                        
        }
                
        switch ($this->mailN)
        {
         case '2':
            $query->andFilterWhere(['>', 'ifnull(mailN,0)', 0]);
            $countquery->andFilterWhere(['>', 'ifnull(mailN,0)', 0]);
            $sumquery->andFilterWhere(['>', 'ifnull(mailN,0)', 0]);
         break;                       
         case '3':
            $query->andFilterWhere(['=', 'ifnull(mailN,0)', 0]);
            $countquery->andFilterWhere(['=', 'ifnull(mailN,0)', 0]);
            $sumquery->andFilterWhere(['=', 'ifnull(mailN,0)', 0]);
         break;                        
        }    
     }
   
    //$this->debug[] = $this->userRef; 
    
     if (empty($this->userFIO))$this->userFIO=$this->userRef;
     if ($this->userFIO > 0)
     {
        $query->andFilterWhere(['=', '{{%user}}.id', $this->userFIO]); 
        $countquery->andFilterWhere(['=', '{{%user}}.id', $this->userFIO]); 
        $sumquery->andFilterWhere(['=', '{{%user}}.id', $this->userFIO]); 
     } elseif ($this->userFIO == -2)
     {
        $query->andFilterWhere(['=', 'ifnull({{%user}}.id,0)', 0]); 
        $countquery->andFilterWhere(['=', 'ifnull({{%user}}.id,0)', 0]); 
        $sumquery->andFilterWhere(['=', 'ifnull({{%user}}.id,0)', 0]); 
     }

    
    
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
    $this->sumData =$sumquery->createCommand()->queryOne();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'refOrg',                   
            'orgTitle',                   
            'userFIO',   
            'calN', 
            'cntN',
            'mailN',
            'atsN', 
            'atsD',             
            'userFIO'
            ],

            'defaultOrder' => [    'orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $dataProvider;
   }   


public function getTaskGlobalControlProvider($params)		      
{
    
    if ($this->curTime == 0) $this->curTime = time();
    $strDate = date('Y-m-d', $this->curTime);
    
    $strSqlCalendar = "(SELECT count(DISTINCT(ref_org)) as calN, {{%orglist}}.refManager from {{%calendar}},{{%orglist}}  WHERE {{%calendar}}.ref_org={{%orglist}}.id AND date(event_date) =:DATE GROUP BY {{%orglist}}.refManager ) as cal\n";
    
    $strSqlContact  = "(SELECT count(DISTINCT(ref_org)) as cntN,  {{%orglist}}.refManager from {{%contact}},{{%orglist}} WHERE 
    {{%contact}}.ref_org={{%orglist}}.id and    date({{%contact}}.contactDate) =:DATE GROUP BY {{%orglist}}.refManager ) as cnt\n";
    
    $strSqlProgress  = "(SELECT count(DISTINCT(ref_org)) as chngStatus,  {{%orglist}}.refManager from {{%contact}},{{%orglist}} WHERE 
    {{%contact}}.ref_org={{%orglist}}.id and isChangeStatus =1 AND  date({{%contact}}.contactDate) =:DATE GROUP BY {{%orglist}}.refManager ) as progr\n";
    
    
    $strSqlMail     = "(SELECT count(DISTINCT(refOrg)) as mailN, {{%orglist}}.refManager  from {{%mail}},{{%orglist}} WHERE 
    {{%mail}}.refOrg={{%orglist}}.id and     date(msgDate) =:DATE  GROUP BY {{%orglist}}.refManager ) as mail\n";
/*
SELECT * from `rik_ats_log` WHERE 
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    `internal` = `internal_id` AND disposition = 'answered'
    date(call_start) ='2020-03-06' GROUP BY orgRef
*/
//`internal` = `internal_id` AND 
//disposition = 'answered' AND
    $strSqlAts      = "(SELECT count(DISTINCT(orgRef)) as atsN, {{%orglist}}.refManager  from {{%ats_log}},{{%orglist}} WHERE 
    {{%ats_log}}.orgRef={{%orglist}}.id and
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND    
    date(call_start) =:DATE  GROUP BY {{%orglist}}.refManager ) as ats";
    
    $strSqlDuration = "(SELECT count(DISTINCT(orgRef)) as atsD,  {{%orglist}}.refManager from {{%ats_log}},{{%orglist}} WHERE 
    {{%ats_log}}.orgRef={{%orglist}}.id and
    (event ='NOTIFY_OUT_END' || event ='NOTIFY_END') AND
    disposition = 'answered' AND duration > 30 AND
    date(call_start) =:DATE  GROUP BY {{%orglist}}.refManager ) as dur";
      
    
    $query  = new Query();    
    $query->select ([
            '{{%user}}.id',
            'userFIO',   
            'count(DISTINCT({{%orglist}}.id)) as orgN',                   
            'ifnull(calN,0) as calN', 
            'ifnull(cntN,0) as cntN',
            'ifnull(chngStatus,0) as chngStatus',
            'ifnull(mailN,0) as  mailN',
            'ifnull(atsN,0) as atsN', 
            'ifnull(atsD,0) as atsD',             
            ])
            ->from("{{%orglist}}")
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%user}}.id = cal.refManager")                                    
            ->leftJoin($strSqlContact, "{{%user}}.id = cnt.refManager")
            ->leftJoin($strSqlProgress, "{{%user}}.id = progr.refManager")                                                            
            ->leftJoin($strSqlMail, "{{%user}}.id = mail.refManager")                        
            ->leftJoin($strSqlAts, "{{%user}}.id = ats.refManager")                        
            ->leftJoin($strSqlDuration, "{{%user}}.id = dur.refManager")                        
            ->addParams([':DATE' => $strDate])            
            ->groupBy(['{{%user}}.id']);
            ;            
            
    $query->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");        
   
    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%user}}.id))")
            ->from("{{%user}}")
            ->leftJoin("{{%orglist}}", "{{%user}}.id = {{%orglist}}.refManager")            
            ->leftJoin($strSqlCalendar, "{{%user}}.id = cal.refManager")                                    
            ->leftJoin($strSqlContact, "{{%user}}.id = cnt.refManager")                        
            ->leftJoin($strSqlMail, "{{%user}}.id = mail.refManager")                        
            ->leftJoin($strSqlAts, "{{%user}}.id = ats.refManager")                        
            ->leftJoin($strSqlDuration, "{{%user}}.id = dur.refManager")                        
            ->addParams([':DATE' => $strDate])            
            ->groupBy(['{{%user}}.id']);
       
            ;            
            
    $countquery->andWhere("(ifnull(calN,0)+ifnull(mailN,0)+ifnull(atsN,0)) >0 ");        

    if (($this->load($params) && $this->validate() )) 
     {
     
        $query->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
        $countquery->andFilterWhere(['like', '{{%user}}.userFIO', $this->userFIO]); 
     }
   
    $this->debug[] = $query->createCommand()->getRawSql(); 
    
    $command = $query->createCommand(); 
    $count = $countquery->createCommand()->queryScalar();
 
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            'sort' => [
            'attributes' => [
            'orgN',                   
            'userFIO',   
            'calN', 
            'cntN',
            'mailN',
            'atsN', 
            'atsD',             
            ],

            'defaultOrder' => [    'userFIO' => SORT_ASC ],
            ],
        ]);
        
    return $dataProvider;
   }   
   
     
  /*****/
}
