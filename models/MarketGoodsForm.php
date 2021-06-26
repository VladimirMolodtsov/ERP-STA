<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


use app\models\RequestGood; 
use app\models\RequestGoodContent; 
use app\models\ZakazList;
use app\models\OrgList;
use app\models\MailForm;


/**
 * MarketGoodsForm  - модель для работы с заявками на закупку товара
 */
class MarketGoodsForm extends Model
{
	
    public $requestId;

	public $isFormed =0; 
    public $wareTitle="";
    public $orgTitle="";
			
/*, 'good', 'count', 'marketDate', 'sclad'*/		
	public function rules()
    {
        return [
	        [['requestId',  'isFormed' ], 'default'],
			['requestId', 'integer'],
			['isFormed', 'integer'],			
            [['wareTitle',  'orgTitle' ], 'safe'],
        ];
    }
	 
   public function loadRequestData($id)
   {
	   $this->requestId = $id;
	   $requesRecord = RequestGood::findOne($id);
	   return $requesRecord;	  
   }
	 
   public function getRequestListProvider($params)
   {
	    $curUser=Yii::$app->user->identity;

    $countquery  = new Query();
    $countquery->select ([
    'COUNT({{%request_good_content}}.id)',    
     ])
     ->from("{{%request_good}}")
     ->leftJoin("{{%request_good_content}}","{{%request_good_content}}.refRequest = {{%request_good}}.id")
     ->leftJoin("{{%orglist}}","{{%orglist}}.id =  {{%request_good}}.refOrg")
     ->andWhere(['=','{{%request_good}}.refManager',$curUser->id])
     ->andWhere(['=','{{%request_good_content}}.isFinished',0])
     ;
     
    $query  = new Query();
    $query->select ([
        '{{%request_good}}.id as requestId', 
        'formDate', 
        'isFormed',  
        'refOrg', 
        '{{%request_good_content}}.isInWork', 
        '{{%request_good_content}}.isFinished',
        '{{%orglist}}.id as orgId', 
        '{{%orglist}}.title as orgTitle',
        '{{%request_good_content}}.good as wareTitle',
        '{{%request_good_content}}.refPurchaseZakaz'
     ])
     ->from("{{%request_good}}")
     ->leftJoin("{{%request_good_content}}","{{%request_good_content}}.refRequest = {{%request_good}}.id")
     ->leftJoin("{{%orglist}}","{{%orglist}}.id =  {{%request_good}}.refOrg")     
     ->andWhere(['=','{{%request_good}}.refManager',$curUser->id])
     ->andWhere(['=','{{%request_good_content}}.isFinished',0])
     ;

      if (($this->load($params) && $this->validate())) {

            $query->andFilterWhere(['like', 'title', $this->orgTitle]);
            $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);

            $query->andFilterWhere(['like', '{{%request_good_content}}.good', $this->wareTitle]);
            $countquery->andFilterWhere(['like', '{{%request_good_content}}.good', $this->wareTitle]);
     }

    $count = $countquery->createCommand()->queryScalar();
	$command = $query->createCommand();	

            
		$provider = new SqlDataProvider([
            'sql' => $command ->sql, 
			'params' => $command->params,	
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'requestId',
			'isFormed',			
			'formDate',
			'orgTitle',
			'isInWork', 
			'isFinished',
            'wareTitle'
			],
			],
		]);
	return $provider;
   }   

   
   public function getRequestContentListProvider()
   {
	    $curUser=Yii::$app->user->identity;
		$count = Yii::$app->db->createCommand(
            'SELECT count({{%request_good_content}}.id) FROM {{%request_good_content}} where 
			refRequest =:refRequest ', 
            [':refRequest' => $this->requestId])->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 'SELECT {{%request_good_content}}.id as id, good, count, marketDate, sklad, refRequest			
		    FROM {{%request_good_content}}where 
			refRequest =:refRequest ',            		
			'params' => [':refRequest' => $this->requestId],
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 5,
			],
			'sort' => [
			'attributes' => [
			'id', 
			'good', 
			'count', 
			'marketDate', 
			'sklad'
			],
			],
		]);
	return $provider;
   }   

   
   public function  createGoodRequest($zakazId)
   {
	  $requestGoodRecord = RequestGood::find()
		->where(['refZakaz' => $zakazId])
		->one();
	  
	  if (!empty($requestGoodRecord)) return $requestGoodRecord->id;
	  
	   
	  $zakazRecord = ZakazList::findOne($zakazId);
	  if (empty($zakazRecord)){return -1;}

	   
	$curUser=Yii::$app->user->identity;
	$requestGoodRecord = new RequestGood;
	$requestGoodRecord->refZakaz = $zakazId;
	$requestGoodRecord->refManager = $curUser->id;
	$requestGoodRecord->formDate = date("Y-m-d");
	$requestGoodRecord->refOrg = $zakazRecord->refOrg;	
	$requestGoodRecord->save();
	
	/*Добавим из заявки содержимое*/
    
    Yii::$app->db->createCommand(
            'UPDATE {{%zakazContent}}, {{%warehouse}} set {{%zakazContent}}.warehouseRef = {{%warehouse}}.id            
			where {{%zakazContent}}.warehouseRef =0 AND {{%zakazContent}}.id=:refZakaz  AND {{%zakazContent}}.good = {{%warehouse}}.title', 
			 [
			':refZakaz' => $zakazId,				
			])->execute();		
    
	/*Предложен товар*/
	Yii::$app->db->createCommand(
            'INSERT INTO {{%request_good_content}} (good, {{%request_good_content}}.count, refRequest, refWare, goodEd)
			select good, count, :refRequest, warehouseRef, ed from {{%zakazContent}} where refZakaz=:refZakaz  AND {{%zakazContent}}.good is NOT null', 
			 [
			':refZakaz' => $zakazId,	
			":refRequest" => $requestGoodRecord->id,	
			])->execute();		
	/*Товара нет*/
	Yii::$app->db->createCommand(
            'INSERT INTO {{%request_good_content}} (good, {{%request_good_content}}.count, refRequest, refWare, goodEd)
			select initialZakaz, count, :refRequest, warehouseRef, ed  from {{%zakazContent}} where refZakaz=:refZakaz  AND {{%zakazContent}}.good is null', 
			 [
			':refZakaz' => $zakazId,	
			':refRequest' => $requestGoodRecord->id,	
			])->execute();		
	
	return $requestGoodRecord->id;
   }
   
   
   
   public function  delFromRequest($requestContentId)		 
   {
	$requestGoodContentRecord  = RequestGoodContent::findOne($requestContentId);
	if (empty ($requestGoodContentRecord )) {return;}	
	$requestGoodContentRecord->delete();
   }

   public function  addToRequest($good, $count, $marketDate, $sclad)
   {
	$requestGoodContentRecord = new RequestGoodContent;
	$requestGoodContentRecord->refRequest = $this->requestId;
	$requestGoodContentRecord->good = $good;	
	$requestGoodContentRecord->count =$count;   
	$requestGoodContentRecord->sklad = $sclad;
	$requestGoodContentRecord->marketDate = date("Y-m-d", strtotime($marketDate));
	$requestGoodContentRecord->save();
   }

   public function  editRequest($requestContentId, $good, $count, $marketDate, $sclad)
   {
	$requestGoodContentRecord = RequestGoodContent::findOne($requestContentId);
	if (empty ($requestGoodContentRecord )) {return;}	
	$requestGoodContentRecord->good = $good;	
	$requestGoodContentRecord->count =$count;   
	$requestGoodContentRecord->sklad = $sclad;
    if (empty($marketDate)) $requestGoodContentRecord->marketDate = date("Y-m-d");
	else                    $requestGoodContentRecord->marketDate = date("Y-m-d", strtotime($marketDate));
	$requestGoodContentRecord->save();
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
	   
   
	public function saveData()		
   {
	  
	  $requestGoodRecord = RequestGood::findOne($this->requestId);	  
	  if (empty($requestGoodRecord)){return;}
	  $orgRecord = OrgList::findOne($requestGoodRecord->refOrg);
	  if (empty($orgRecord)){return;}	  
	  $curUser=Yii::$app->user->identity;
	  	  
   	  $requestGoodRecord->isFormed = 1;
	  $requestGoodRecord->save();

return;
      
 		$detailList = Yii::$app->db->createCommand(
            'SELECT {{%request_good_content}}.id, good, count, marketDate, sklad 
			FROM   {{%request_good_content}}  where  refRequest=:refRequest', 
        [':refRequest' =>$this->requestId ])->queryAll();		

	 
	 
  	 $blank ="<html lang=\"en-US\"><head><meta charset=\"UTF-8\"></head><body>\n";
	 $blank.="<div style='align:center;width:800px'><h2>Заявка на закупку товара №".$requestGoodRecord->id." от ".date ("d.m.Y", strtotime($requestGoodRecord->formDate) )."</h2>\n";	
	 $blank.="<p align='right'> от ".$curUser->userFIO."</p>\n";
	 $blank.="<p> Клиент".$orgRecord->title."</p>\n";
	 
	 $blank.="<hr>\n";	

	 $blank.="<table border='1' width='800px'>";
	 $blank.="<tr><td style='padding:3px'>Наименование продукции</td> 
	 <td style='padding:3px'>Тоннаж, кг </td> 
	 <td style='padding:3px'>Дата закупки </td>
	 <td style='padding:3px'>Куда выгружать </td>
	 </tr>\n";
	 
	for ($i=0; $i<count($detailList);$i++ )
	{
		$blank.="<tr>\n";
		$blank.="<td style=padding:3px'>".$detailList[$i]['good']."</td>\n";
		$blank.="<td style=padding:3px'>".$detailList[$i]['count']."</td>\n";
		$blank.="<td style=padding:3px'>".$detailList[$i]['marketDate']."</td>\n";
		$blank.="<td style=padding:3px'>".$detailList[$i]['sklad']."</td>\n";
		$blank.="</tr>\n";
	}
 	 $blank.=" </table>  <hr>"; 
	
	 $blank.="</div></body></html>"; 
	 
	 $mailer = new MailForm ();
	 
/*	 $fromEmail = "zakaz@rik-nsk.ru";
	 $email = "3630829@mail.ru,rik-nsk@mail.ru"; */
	 
 	 $fromEmail = $this->getCfgValue(1001);
	 $email = $this->getCfgValue(1002).",".$this->getCfgValue(1003); 
	 
/*Test only*/	 
	 
	 $subject = "Заявка на закупку товара №".$requestGoodRecord ->id." от ".date ("d.m.Y", strtotime($requestGoodRecord->formDate) );	 
	 $mailer->sendExtMail($email, $subject, $blank, $fromEmail, array());
	 
	 
    return $blank;
	  
   }

  

   
 }
