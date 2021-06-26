<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

 

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class MarketingForm extends Model
{
	
	public $id = 0;
	public $razdel = "";
	public $areas = "";
	public $citys = "";
	
	public function rules()
    {
        return [
			[['razdel', 'areas', 'citys'], 'safe'],
            [[ 'id', ], 'default'],			
        ];
    }

		public function getOrgCount()
		{
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%orglist}}.id) FROM {{%orglist}}')->queryScalar();
			
		return $count;	
		}
	
		public function getZakazAllCount()
		{
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%zakaz}}.id) FROM {{%zakaz}}')->queryScalar();
			
		return $count;	
		}
		
		public function getZakazMonthCount()
		{
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%zakaz}}.id) FROM {{%zakaz}}  WHERE MONTH(formDate) = MONTH(CURRENT_DATE())')->queryScalar();
			
		return $count;	
		}

		public function getSchetAllCount()
		{
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%schet}}.id) FROM {{%schet}}')->queryScalar();
		return $count;		
		}

		public function getSchetMonthCount()
		{
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%schet}}.id) FROM {{%schet}}  WHERE MONTH(schetDate) = MONTH(CURRENT_DATE())')->queryScalar();
			
		return $count;					
		}

   /************************************
	ИНТЕГРАЛЬНЫЕ
   **************************************/
	public function getInteresSummary()  
	{
		$resList= [
		'haveContact' => 0,
		'haveInteres' => 0,
		'haveReject'  => 0,
		'conversion'  => 0,		
		];
		
		$resList['haveContact'] = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%orglist}} where isFirstContact=1')->queryScalar();
		$resList['haveInteres'] = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%orglist}} where isFirstContactFinished=1')->queryScalar();
		$resList['haveReject'] = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%orglist}} where isReject=1')->queryScalar();
		if ($resList['haveContact'] > 0) 
		{
			$resList['conversion'] = number_format((100*$resList['haveInteres'])/$resList['haveContact'], 2, ',', ' ')."%";			
		}
		
		return $resList;
		
	}		
   
	public function getZakazSummary()  
	{
		$resList= [		
		'haveInteres' => 0,
		'haveZakaz'   => 0,		
		'conversion'  => 0,		
		];

		$resList['haveInteres'] = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%orglist}} where isFirstContactFinished=1')->queryScalar();
		$resList['haveZakaz'] = Yii::$app->db->createCommand('Select COUNT(a.ID) from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz  from 
		{{%orglist}} left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0')->queryScalar();
		if ($resList['haveInteres'] > 0) 
		{
			$resList['conversion'] = number_format((100*$resList['haveZakaz'])/$resList['haveInteres'], 2, ',', ' ')."%";			
		}
		
		return $resList;
		
	}		
   
	public function getSchetSummary()  
	{
		$resList= [		
		'haveSchet' => 0,
		'haveZakaz'   => 0,		
		'conversion'  => 0,		
		];

		$resList['haveSchet'] = Yii::$app->db->createCommand('Select COUNT(a.ID) from (SELECT {{%orglist}}.id as ID , count({{%schet}}.id) as cz  from 
		{{%orglist}} left join {{%schet}} on {{%schet}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0')->queryScalar();
		$resList['haveZakaz'] = Yii::$app->db->createCommand('Select COUNT(a.ID) from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz  from 
		{{%orglist}} left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0')->queryScalar();
		if ($resList['haveZakaz'] > 0) 
		{
			$resList['conversion'] = number_format((100*$resList['haveSchet'])/$resList['haveZakaz'], 2, ',', ' ')."%";			
		}
		
		return $resList;
		
	}		
   
   
   
   /************************************
		ПРОВАЙДЕРЫ
   **************************************/
   
   public function getZakazListProvider()
   {
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%zakaz}}.id)  as zakazCount FROM  {{%orglist}}
			left join {{%adreslist}} on {{%adreslist}}.ref_org={{%orglist}}.id
			left join {{%zakaz}} on {{%zakaz}}.refOrg ={{%orglist}}.id 

			HAVING zakazCount >0 ')->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'SELECT 
		{{%orglist}}.id, {{%orglist}}.title, have_phone, {{%orglist}}.isFirstContact,  {{%orglist}}.isFirstContactFinished, {{%orglist}}.razdel,
		{{%adreslist}}.area, {{%adreslist}}.city, count({{%zakaz}}.id) as zakazCount, max({{%zakaz}}.formDate) as zakazLastDate
		from {{%orglist}}
		left join {{%adreslist}} on {{%adreslist}}.ref_org={{%orglist}}.id
		left join {{%zakaz}} on {{%zakaz}}.refOrg ={{%orglist}}.id 
		GROUP BY	
		{{%orglist}}.title, have_phone, {{%orglist}}.isFirstContact,  {{%orglist}}.isFirstContactFinished, {{%orglist}}.razdel,
		{{%adreslist}}.area, {{%adreslist}}.city
		HAVING zakazCount >0',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'title',			
			'have_phone',
			'isFirstContact',
			'isFirstContactFinished',
			'razdel',
			'area',
			'city',
			'zakazCount',
			'zakazLastDate'
			],
			],
		]);
	return $provider;
   }   

   
   public function getSchetListProvider()
   {
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%orglist}}.id),  count({{%schet}}.id) as schetCount FROM  {{%orglist}}
			left join {{%adreslist}} on {{%adreslist}}.ref_org={{%orglist}}.id
			left join {{%schet}} on {{%schet}}.refOrg ={{%orglist}}.id 
			HAVING schetCount >0 ')->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'SELECT 
		{{%orglist}}.id, {{%orglist}}.title, have_phone, {{%orglist}}.isFirstContact,  {{%orglist}}.isFirstContactFinished, {{%orglist}}.razdel,
		{{%adreslist}}.area, {{%adreslist}}.city, count({{%schet}}.id) as schetCount, max({{%schet}}.schetDate) as schetLastDate
		from {{%orglist}}
		left join {{%adreslist}} on {{%adreslist}}.ref_org={{%orglist}}.id
		left join {{%schet}} on {{%schet}}.refOrg ={{%orglist}}.id 
		GROUP BY	
		{{%orglist}}.title, have_phone, {{%orglist}}.isFirstContact,  {{%orglist}}.isFirstContactFinished, {{%orglist}}.razdel,
		{{%adreslist}}.area, {{%adreslist}}.city
		HAVING schetCount >0',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'title',			
			'have_phone',
			'isFirstContact',
			'isFirstContactFinished',
			'razdel',
			'area',
			'city',
			'schetCount',
			'schetLastDate'
			],
			],
		]);
	return $provider;
   }   
   
  
 /**************************************************************
    Интерес  
 ***************************************************************/

   public function getInteresByRazdelProvider($params)
   {
	 
     $query  = new Query();
		
	 $query->select("{{%razdellist}}.razdel , count(a.id) as haveContact, count(b.id) as haveInteres, count(c.id) as haveReject, (100*count(b.id)/count(a.id)) as conv ")
			->from("{{%razdellist}}") 
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContact=1) as a", "{{%razdellist}}.ref_org = a.id")
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContactFinished=1) as b", "{{%razdellist}}.ref_org = b.id")
			->leftJoin("(SELECT id from {{%orglist}}  where isReject=1) as c", "{{%razdellist}}.ref_org = c.id")
			->groupBy("{{%razdellist}}.razdel");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', '{{%razdellist}}.razdel', $this->razdel]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'razdel',
			'haveContact',
			'haveInteres',
			'haveReject', 
			'conv'
			],
			'defaultOrder' => [	'haveInteres' => SORT_DESC ],
			],
		]);
	return $provider;
   }   
   
 

 /* --- */

   public function getInteresByCityProvider($params)
   {
	 
     $query  = new Query();
		
	 $query->select("trim(area) AS areas, trim(city) AS citys, count(a.id) as haveContact, count(b.id) as haveInteres, count(c.id) as haveReject, (100*count(b.id)/count(a.id)) as conv")
			->from("{{%adreslist}}") 
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContact=1) as a", "{{%adreslist}}.ref_org = a.id")
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContactFinished=1) as b", "{{%adreslist}}.ref_org = b.id")
			->leftJoin("(SELECT id from {{%orglist}}  where isReject=1) as c", "{{%adreslist}}.ref_org = c.id")
			->groupBy("trim(area)", "trim(city)");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', 'city', $this->citys]);
		$query->andFilterWhere(['like', 'area', $this->areas]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'areas',
			'citys',
			'haveContact',
			'haveInteres',
			'haveReject', 
			'conv'
			],
			'defaultOrder' => [	'haveInteres' => SORT_DESC ],
			],
		]);
	return $provider;
   }   
   

 /***************************************************
                     Заказы
 ****************************************************/

   public function getZakazByRazdelProvider($params)
   {
	 
     $query  = new Query();
		
	 $query->select("{{%razdellist}}.razdel, count(b.id) as haveInteres,  count(d.ID) as haveZakaz, (100*count(d.ID)/count(b.id)) as conv ")
			->from("{{%razdellist}}") 			
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContactFinished=1) as b", "{{%razdellist}}.ref_org = b.id")
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz   from {{%orglist}} 
			 left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0) as d", "{{%razdellist}}.ref_org = d.id")
			->groupBy("{{%razdellist}}.razdel");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', '{{%razdellist}}.razdel', $this->razdel]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'razdel',
			'haveZakaz',
			'haveInteres',
			'conv'
			],
			'defaultOrder' => [	'haveInteres' => SORT_DESC ],
			],
		]);
	return $provider;
   }    
 /* ---- */
   public function getZakazByCityProvider($params)
   {
	 
     $query  = new Query();
		
		
	 $query->select("trim(area) AS areas, trim(city) AS citys, count(b.id) as haveInteres,  count(d.ID) as haveZakaz, (100*count(d.ID)/count(b.id)) as conv ")
			->from("{{%adreslist}}") 			
			->leftJoin("(SELECT id from {{%orglist}}  where isFirstContactFinished=1) as b", "{{%adreslist}}.ref_org = b.id")
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz   from {{%orglist}} 
			 left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0) as d", "{{%adreslist}}.ref_org = d.id")
			->groupBy("trim(area)", "trim(city)");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', 'city', $this->citys]);
		$query->andFilterWhere(['like', 'area', $this->areas]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'areas',
			'citys',
			'haveZakaz',
			'haveInteres',
			'conv'
			],
			'defaultOrder' => [	'haveInteres' => SORT_DESC ],
			],
		]);
	return $provider;
   }   

 /***************************************************
                     Счета
 ****************************************************/

   public function getSchetByRazdelProvider($params)
   {
	 
     $query  = new Query();
		
	 $query->select("{{%razdellist}}.razdel, count(b.id) as haveSchet,  count(d.ID) as haveZakaz, (100*count(b.ID)/count(d.id)) as conv ")
			->from("{{%razdellist}}") 			
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%schet}}.id) as cs  from {{%orglist}} 
			 left join {{%schet}} on {{%schet}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cs > 0) as b", "{{%razdellist}}.ref_org = b.id")
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz   from {{%orglist}} 
			 left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0) as d", "{{%razdellist}}.ref_org = d.id")
			->groupBy("{{%razdellist}}.razdel");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', '{{%razdellist}}.razdel', $this->razdel]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'razdel',
			'haveZakaz',
			'haveSchet',
			'conv'
			],
			'defaultOrder' => [	'haveSchet' => SORT_DESC ],
			],
		]);
	return $provider;
   }    
 /* ---- */
   public function getSchetByCityProvider($params)
   {
	 
     $query  = new Query();
		
		
	 $query->select("trim(area) AS areas, trim(city) AS citys, count(b.id) as haveSchet,  count(d.ID) as haveZakaz, (100*count(b.ID)/count(d.id)) as conv ")
			->from("{{%adreslist}}") 			
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%schet}}.id) as cs  from {{%orglist}} 
			 left join {{%schet}} on {{%schet}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cs > 0) as b", "{{%adreslist}}.ref_org = b.id")
			->leftJoin("(Select ID from (SELECT {{%orglist}}.id as ID , count({{%zakaz}}.id) as cz   from {{%orglist}} 
			 left join {{%zakaz}} on {{%zakaz}}.refOrg = {{%orglist}}.id group by {{%orglist}}.id) as a where cz > 0) as d", "{{%adreslist}}.ref_org = d.id")
			->groupBy("trim(area)", "trim(city)");
						
   	if (($this->load($params) && $this->validate())) 
	{     
		$query->andFilterWhere(['like', 'city', $this->citys]);
		$query->andFilterWhere(['like', 'area', $this->areas]);
     }
	
	   $command = $query->createCommand();	
	   
	   $res = $command->queryAll();
	   $count = count($res);
		
		$provider = new SqlDataProvider(['sql' => $command ->sql,
			'params' => $command->params,
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 20,
			],
			'sort' => [
			'attributes' => [
			'areas',
			'citys',
			'haveZakaz',
			'haveSchet',
			'conv'
			],
			'defaultOrder' => [	'haveSchet' => SORT_DESC ],
			],
		]);
	return $provider;
   }   

   
  /************End of model*******************/ 
 }
