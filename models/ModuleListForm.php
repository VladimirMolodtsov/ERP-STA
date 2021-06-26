<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\models\ModuleList;
use app\models\NeedTitleList;
use yii\data\SqlDataProvider;

/**
 *
 */
class ModuleListForm extends Model
{
	public $module1 = "";
	public $module2= "";	
	public $needTitle= "";
    public $needRow= "";
    public $needId= "";

	
	public function rules()
    {
        return [
		    [['module1','module2' ], 'required'],
            [['module1','module2','needTitle','needRow','needId' ], 'default'],
			[['module1','module2','needTitle','needId'], 'trim'],
        ];
    }
	public function initData()	
    {
	  $record1 = ModuleList::findOne([
	  'number' => 1,
      ]);	
	  
	  $this->module1= $record1->moduleText;
	  
	  $record2 = ModuleList::findOne([
	  'number' => 2,
      ]);	
	  
	 $this->module2= $record2->moduleText;	
			
	}

	
	public function saveData()	
    {

	  $record1 = ModuleList::findOne([
	  'number' => 1,
      ]);	
	  
	  $record1->moduleText = $this->module1;
	  $record1->save();
	  
	  $record2 = ModuleList::findOne([
	  'number' => 2,
      ]);	
	  
	  $record2->moduleText = $this->module2;	
	  $record2->save();
	
	
	if (!isset($this->needTitle)  || $this->needTitle =="") {return;}
	if (!isset($this->needRow)  || $this->needRow =="") {return;}
	if (isset($this->needId) && $this->needId !="")	
	{
	  $recNeedTitle = NeedTitleList::findOne(['id'=> $this->needId]);	
	}
	else
	{
	  $recNeedTitle = new NeedTitleList();	
	}

	$recNeedTitle->Title =$this->needTitle;
	$recNeedTitle->row =$this->needRow;
	$recNeedTitle->save();
		

	}
	
	public function needTitleRm ($id)
	{
		    Yii::$app->db->createCommand()->delete('{{%need_title}}', [
			'id' => $id,
			])->execute();			
	}
   public function getNeedTitleProvider()
   {
	    
		$count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%need_title}}' )->queryScalar();
			
		$provider = new SqlDataProvider(['sql' => 
            ' SELECT id, Title, row FROM {{%need_title}}',
			'totalCount' => $count,
			'pagination' => [
			'pageSize' => 10,
			],
			'sort' => [
			'attributes' => [
			'Title',
			'row',
			],
			],
		]);
	return $provider;
   }   

	
}

