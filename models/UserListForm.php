<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\models\UserList;
use yii\data\SqlDataProvider;

/**
 * ColdForm  - модель стартовой формы менеджера холодных звонков
 */
class UserListForm extends Model
{

    public $id= 0;
	
	public $userName = "";
	public $userFio= "";
	public $password= "";
	public $phoneLink = "cb.zadarma.com/xxxxxx/?n=";
	public $phoneInternаl ="100";

	
	public $userNote="";
	
	public $isDataOp= 0; /*Маркетинг*/
	public $isColdOp= 0; /*Оператор холодных звонков*/
	public $isSchetOp= 0; /*Менеджер активных продаж*/
	public $isSchet2Op= 0; /*Менеджер 2 уровня*/
	public $isPersonalOp= 0; /*Менеджер по кадрам*/
	
	public $isScladOp= 0; /*Склад/снабжение*/
	public $isFinOp= 0; /*Финансы*/
	public $isHead= 0; /*Управление*/

	public $isHeadMarket= 0; /*Финансы*/
	public $isHeadSclad= 0; /*Управление*/
   	public $isBankOp= 0; /*Оператор банка*/
	
	
     /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;

    
	public function rules()
    {
        return [
		   // [['userName','userFio', 'password'], 'required'],
            [['id','userName','userFio', 'password','isDataOp','isColdOp','isSchetOp','isPersonalOp', 'phoneLink', 
			  'phoneInternаl', 'isScladOp', 'isFinOp', 'isHead', 'isSchet2Op', 'isHeadSclad', 'isHeadMarket','isBankOp','userNote',
              'recordId','dataType', 'dataVal'             
              ], 'default'],
			[['userName', 'userFio', 'password', 'phoneLink', ], 'trim'],
			['id', 'integer'],						
			['userName', 'string', 'length' => [1,50]],						
			['userFio', 'string', 'length' => [1,150]],						
			['password', 'string', 'length' => [1,50]],						
        ];
    }

	public function getUserListProvider()		
    {
			$count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%user}}' )->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 
            ' SELECT id, username, userFIO, roleFlg,phoneInternаl FROM   {{%user}}',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'username',
			'userFIO',
			],
			],
		]);
	return $provider;

		
    }


	public function saveAjaxRole()	
    {      
    $this->recordId = intval($this->recordId);
        $ret =[
            'res' => false,
            'recordId' => $this->recordId,        
            'dataType' => $this->dataType,        
            'dataVal' => $this->dataVal,        
            'val' => ''
        ];

      $record = User::findOne($this->recordId);	  	  
	  if (empty($record)) return $ret;
      
      $ret['debug'] = 1;
      switch ($this->dataType)
      {
        case 'del':
          $record->delete();
        break;        

        case 'switch':
          if ($record->roleFlg & $this->dataVal) $record->roleFlg = $record->roleFlg -($this->dataVal);
          else $record->roleFlg |= $this->dataVal;
          $record->save();
          $ret['val'] =$record->roleFlg;
        break;                  
      }
	  
	  
       
      $ret['res'] = true;
      
      return $ret;
    }


	
	public function saveAjaxData()	
    {
        $this->id = intval($this->id);
        $ret =[
            'res' => false,
            'id' => $this->id,        
        ];

      $record = User::findOne($this->id);	  
	  if (empty($record)) $record = new User();	    
	  if (empty($record)) return $ret;
      
	  $roleFlg = 0;
	  if ($this->isDataOp > 0)    {$roleFlg |= 0x0001;}
	  if ($this->isColdOp > 0)    {$roleFlg |= 0x0002;}
	  if ($this->isSchetOp > 0)   {$roleFlg |= 0x0004;}	  
	  if ($this->isPersonalOp > 0){$roleFlg |= 0x0008;}

	  if ($this->isScladOp > 0){$roleFlg |= 0x0010;}
  	  if ($this->isHead > 0){$roleFlg |= 0x0020;}
	  if ($this->isFinOp > 0){$roleFlg |= 0x0040;}
	  
	  if ($this->isSchet2Op > 0)   {$roleFlg |= 0x0080;}
	  
      if ($this->isHeadMarket > 0)   {$roleFlg |= 0x0100;}
	  if ($this->isHeadSclad > 0)   {$roleFlg |= 0x0200;}
	           
	  if ($this->isBankOp > 0)   {$roleFlg |= 0x0400;}         
	           
	  $record->phoneLink =$this->phoneLink;
	  $record->phoneInternаl=$this->phoneInternаl;	  
	  $record->username = $this->userName;
	  $record->userFIO = $this->userFio;
	  $record->password = $this->password;
	  $record->roleFlg = $roleFlg;
	  $record->userNote= $this->userNote;
	  
	  $record->save();
       
      $ret['res'] = true;
      return $ret;
    }
	  
}

