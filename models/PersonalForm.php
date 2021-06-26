<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;

/**
 * PersonalForm  - модель стартовой формы отдела кадров
 */
class PersonalForm extends Model
{
    public $title= "";
	
	
	public function rules()
    {
        return [
			[['title'], 'safe'],
        ];
    }


	
   public function getMarketPersonalProvider()
   {
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%user}}.id) FROM {{%user}} where (roleFlg &  0x0001 OR roleFlg &  0x0002)')->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'SELECT  {{%user}}.id,  {{%user}}.userFIO 
		,(SELECT COUNT({{%zakaz}}.id) from {{%zakaz}}, {{%orglist}} where {{%zakaz}}.refOrg= {{%orglist}}.id AND {{%orglist}}.refManager= {{%user}}.id ) AS zakazAll
		,(SELECT COUNT({{%zakaz}}.id) from {{%zakaz}}, {{%orglist}} where {{%zakaz}}.refOrg= {{%orglist}}.id AND {{%orglist}}.refManager= {{%user}}.id 
		AND MONTH({{%zakaz}}.formDate) = MONTH(CURRENT_DATE()) ) AS zakazMonth
		,(SELECT COUNT({{%schet}}.id) from {{%schet}}, {{%orglist}} where {{%schet}}.refOrg= {{%orglist}}.id AND {{%orglist}}.refManager= {{%user}}.id ) AS schetAll
		,(SELECT COUNT({{%schet}}.id) from {{%schet}}, {{%orglist}} where {{%schet}}.refOrg= {{%orglist}}.id AND {{%orglist}}.refManager= {{%user}}.id 
		AND MONTH({{%schet}}.schetDate) = MONTH(CURRENT_DATE()) ) AS schetMonth
		FROM {{%user}}
		where (roleFlg &  0x0001 OR roleFlg &  0x0002)',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'id',			
			'userFIO',
			'zakazAll',
			'zakazMonth',
			'schetAll',
			'schetMonth',
			],
			],
		]);
	return $provider;
   }   
   
		
   public function getZakazStateProvider()
   {
   
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%zakaz}}.id) FROM {{%zakaz}}')->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'Select {{%zakaz}}.id, {{%zakaz}}.formDate, 
		{{%orglist}}.title, {{%user}}.userFIO, {{%zakaz}}.isActive, {{%orglist}}.nextContactDate, {{%orglist}}.id as orgId
		from {{%zakaz}}, {{%orglist}}, {{%user}}
		where {{%zakaz}}.refOrg =  {{%orglist}}.id and {{%orglist}}.ref_user = {{%user}}.id
		',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'id',			
			'formDate',
			'title',
			'userFIO',
			'isActive',
			'nextContactDate',
			],
			],
		]);
	return $provider;
   }   
   

   public function getSchetStateProvider()
   {
   
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%schet}}.id) FROM {{%schet}}')->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'Select {{%schet}}.id, {{%schet}}.schetDate, {{%schet}}.schetNum, 
		{{%orglist}}.title, {{%user}}.userFIO, {{%schet}}.isSchetActive, {{%orglist}}.nextContactDate, {{%orglist}}.id as orgId
		from {{%schet}}, {{%orglist}}, {{%user}}
		where {{%schet}}.refOrg =  {{%orglist}}.id and {{%orglist}}.ref_user = {{%user}}.id
		',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'id',			
			'schetDate',
			'title',
			'userFIO',
			'isSchetActive',
			'nextContactDate',
			],
			],
		]);
	return $provider;
   }   

   
	
}
