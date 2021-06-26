<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;

use app\models\TblScladList;


/**
 * DeliverSclad - модель работы со складами отгрузки
 */

class DeliverSclad extends Model
{

    public $mode=0;

    public $id=0;

    public $sladTitle;
    public $scladAdress;
    public $useAsAdress=0;
    
    /***/

        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;


    public function rules()
    {
        return [
            [[ 'sladTitle' ], 'safe'],

            [['recordId', 'dataType','dataVal' ], 'default'],

        ];
    }
/***************************/

/**************************/
    public function saveData ()
    {
      $res = [ 'res' => false,
             'dataVal'  => $this->dataVal,
             'recordId' => $this->recordId,
             'dataType' => $this->dataType,
             'val' => '',
             'debug' => '',
             'reload' => false,
           ];

    $res['debug'] = 'Otves';
    $curUser=Yii::$app->user->identity;



    switch ($this->dataType)
    {
        case 'sladTitle':
            $record= TblScladList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->sladTitle =  $this->dataVal;
            $record->save();
            $res['val'] =  $record->sladTitle;
        break;

        case 'scladAdress':
            $record= TblScladList::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->scladAdress =  $this->dataVal;
            $record->save();
            $res['val'] =  $record->scladAdress;
        break;

        case 'rmSclad':
            $record= TblScladList::findOne(intval($this->recordId));
            $record->delete();
            $res['val'] =  $record->scladAdress;
            $res['reload'] =  true;
        break;


        case 'addSclad':
            $record= new TblScladList();
            if (empty($record)) return $res;
            $record->save();
            $res['val'] =  $record->id;
            $res['reload'] =  true;
        break;


        default:
        
        return $res;
     }

    $res['res'] = true;
    return $res;
    }
 
 
 
  public function getScladListProvider($params)
   {

    $query  = new Query();
    $query->select ([
        "id",
        "sladTitle",
        "scladAdress"])

        ->from("{{%scladlist}}");

    $countquery  = new Query();
    $countquery->select ("count(id)")->from("{{%scladlist}}");

    if (($this->load($params) && $this->validate())) {
     $query->andFilterWhere(['like', 'sladTitle', $this->sladTitle]);
     $countquery->andFilterWhere(['like', 'sladTitle', $this->sladTitle]);
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
                'sladTitle',
                'scladAdress'
            ],
            'defaultOrder' => [ 'sladTitle' => SORT_ASC ],
            ],

        ]);
    return  $dataProvider;
   }


/********************/



/**/
 }

