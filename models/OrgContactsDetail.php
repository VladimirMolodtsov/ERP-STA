<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\ContactList;


/**
 * OrgContactsDetail- модель для работы со списком контактов
 */
class OrgContactsDetail extends Model
{
	
    
	public $id = 0;


	public $debug; 
	
	public function rules()
    {
        return [
            [['contactOrgTitle','isSchetActive', 'id'], 'safe'],
        ];
    }
/********************/

 public function setContactStatus($id,$task){
     $id= intval($id);
     $ret = [
            'id'  => $id,
            'task' => $task,
            'val'  => 0,
            'res' => false,
          ] ;  
   $record = ContactList::findOne($id);
   if (empty($record)) return $ret;

   switch($task){

   case 'ignore':
   if ($record->eventType == 5 )$record->eventType = 0;
                           else $record->eventType = 5;
   $record->save();                        
   $ret['val'] = $record->eventType;
   $ret['res'] = true;
   break; 

   case 'lead':
   if ($record->eventType == 20 )$record->eventType = 0;
                            else $record->eventType = 20;
   $record->save();                        
   $ret['val'] = $record->eventType;
   $ret['res'] = true;
   break; 
     
   }  
   
   return $ret;  
 }

 
 public function linkContactZakaz($contactId,$zakazId)
 {
     $contactId= intval($contactId);
     $zakazId= intval($zakazId);
     
     $ret = [
            'contactId'  => $contactId,
            'zakazId' => $zakazId,
            'res' => false,
            'val' => 0,
          ] ;  
   $record = ContactList::findOne($contactId);
   if (empty($record)) return $ret;
   if ($record->refZakaz == $zakazId) $record->refZakaz = 0;
   else $record->refZakaz = $zakazId;
   $record->save();
   return  $ret;
 }
 public function getOrgTitle()
 {
     $record = OrgList::findOne($this->id);
     if (empty($record)) return "";
     else return $record->title;
     
 }


  public function getTasksListProvider($params)
   {


    $count = 1;
    /*Yii::$app->db->createCommand(
            'SELECT count({{%tasks}}.id) from {{%tasks}} 
                left join {{%user}} on {{%tasks}}.executorRef={{%user}}.id  
				 where   refCalendar = 0 AND refOrg=:ref_org', 
            [':ref_org' => $this->id])->queryScalar();
			
      */         
            
            
    $provider = new SqlDataProvider(['sql' => 
            'Select {{%tasks}}.id, startDate, planDate, deadline, {{%user}}.userFIO, {{%tasks}}.note, taskPriority
			from {{%tasks}} 
                left join {{%user}} on {{%tasks}}.executorRef={{%user}}.id  
				 where   refCalendar = 0 AND refOrg=:ref_org',
                
			'params' => [':ref_org' => $this->id],
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 5,
			],
			'sort' => [
			'attributes' => [
            'id',
			'startDate', 
            'planDate', 
            'deadline', 
            'userFIO', 
            'note', 
            'taskPriority'
			],
			'defaultOrder' => [	'id' => SORT_DESC ],
			],
		]);

    
    return $provider;

   }   


  public function getEventListProvider($params)
   {


    $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%calendar}} where   eventStatus != 2 AND ref_org=:ref_org ', 
            [':ref_org' => $this->id])->queryScalar();
			
               
            
            
    $provider = new SqlDataProvider(['sql' => 
            'Select {{%calendar}}.id AS id, contactFIO, contactEmail,  phone,  
            {{%user}}.userFIO, {{%calendar}}.event_date,  eventTime, {{%calendar}}.eventNote, {{%calendar}}.eventStatus,
            {{%tasks}}.note, deadline, ref_zakaz, {{%schet}}.id as ref_schet, {{%zakaz}}.formDate as zakazDate, {{%schet}}.schetNum, {{%schet}}.schetDate,
            ifnull({{%tasks}}.id,0) as refTask
			from {{%calendar}} 
                left join {{%user}} on {{%calendar}}.ref_user={{%user}}.id  
        		left join {{%contact}} on {{%calendar}}.ref_contact={{%contact}}.id 
				left join {{%phones}} on {{%contact}}.ref_phone={{%phones}}.id  				
                left join {{%tasks}} on {{%calendar}}.id={{%tasks}}.refCalendar  				
                left join {{%zakaz}} on {{%zakaz}}.id={{%calendar}}.ref_zakaz  				
                left join {{%schet}} on {{%zakaz}}.id={{%schet}}.refZakaz  				
				where {{%calendar}}.ref_org=:ref_org AND (eventStatus != 2  
                OR ( {{%tasks}}.сurrentState = 0 AND ifnull({{%tasks}}.id,0) > 0) and {{%tasks}}.creatorRef != {{%tasks}}.executorRef) 
                ',
                
			'params' => [':ref_org' => $this->id],
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 5,
			],
			'sort' => [
			'attributes' => [
			'contactDate',
			'contactFIO',
			'phone',	
			'userFIO',
			'event_date',
            'eventTime',
            'id',
            'deadline'
			],
			'defaultOrder' => [	'id' => SORT_DESC ],
			],
		]);

    
    return $provider;

   }   

/************************/
  public function getContactsListProvider($params)
   {


   
    $query  = new Query();
    $query->select ([
            '{{%contact}}.id AS id', 
            'contactFIO', 
            'contactEmail', 
            'note', 
            'contactDate', 
            'phone',  
            '{{%user}}.userFIO', 
            '{{%calendar}}.event_date', 
            '{{%calendar}}.eventNote', 
            '{{%calendar}}.eventStatus',
            'refZakaz',
            'refPurchase',            
            ])
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%contact}}.ref_phone={{%phones}}.id  ")
            ->leftJoin("{{%user}}", "{{%contact}}.ref_user={{%user}}.id")
            ->leftJoin("{{%calendar}}", "{{%calendar}}.ref_contact={{%contact}}.id")
            ->distinct();
            			            
           
    $countquery  = new Query();
    $countquery->select ("count({{%contact}}.id)")
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%contact}}.ref_phone={{%phones}}.id  ")
            ->leftJoin("{{%user}}", "{{%contact}}.ref_user={{%user}}.id")
            ->leftJoin("{{%calendar}}", "{{%calendar}}.ref_contact={{%contact}}.id")            
            ;            

        $query->andWhere(["=","{{%contact}}.ref_org", $this->id]);
   $countquery->andWhere(["=","{{%contact}}.ref_org", $this->id]);
   
    $count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%contact}} where ref_org=:ref_org ', 
            [':ref_org' => $this->id])->queryScalar();
			
    $command = $query->createCommand(); 
    $count   = $countquery->createCommand()->queryScalar();
			
    $provider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 25,
			],
			'sort' => [
			'attributes' => [
			'contactDate',
			'contactFIO',
			'contactEmail',
			'phone',	
			'userFIO',
			'event_date',
			'id',
            'refZakaz',
            'refPurchase',            			
			],
			'defaultOrder' => [	'contactDate' => SORT_DESC ],
			],
		]);

    
    return $provider;

   }   
   
/************************/
public $refZakaz=0;

public function getZakazContactsListProvider($params)
   {
    $query  = new Query();
    $query->select ([
            '{{%contact}}.id',
            '{{%contact}}.contactFIO',
            '{{%contact}}.contactEmail',
            '{{%contact}}.contactDate',
            '{{%contact}}.eventType',
            '{{%contact}}.note',
            '{{%user}}.userFIO',                        
            'phone',            
            ])
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%contact}}.ref_phone = {{%phones}}.id")
            ->leftJoin("{{%user}}", "{{%contact}}.ref_user = {{%user}}.id")
            ->distinct();
            ;


    $countquery  = new Query();
    $countquery->select ("count(DISTINCT({{%contact}}.id))")            
            ->from("{{%contact}}")
            ->leftJoin("{{%phones}}", "{{%contact}}.ref_phone = {{%phones}}.id")
            ->leftJoin("{{%user}}", "{{%contact}}.ref_user = {{%user}}.id");            ;

     $query->andWhere(['=', '{{%contact}}.refZakaz', $this->refZakaz]);
     $countquery->andWhere(['=', '{{%contact}}.refZakaz',  $this->refZakaz]);     
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
            'id',
            'contactFIO',
            'contactEmail',
            'contactDate',
            'eventType',
            'note',
            'userFIO',                        
            'phone',            
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

 
   
   /**end of class**/
 }
