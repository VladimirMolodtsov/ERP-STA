<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;

use app\models\TblTranportTarif;




/**
 * TranportTarif- модель работы с транспортным тарифом
 */

class TransportTarif extends Model
{

    
    public $city;
    public $company;
    
    
    public $debug=[];

    /***/

        /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;


    public function rules()
    {
        return [
            [[ 'city', 'company',  ], 'safe'],

            [['recordId', 'dataType','dataVal',  ], 'default'],





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

    $curUser=Yii::$app->user->identity;

    switch ($this->dataType)
    {
        case 'addInSchet':
            $record= TblTranportTarif::findOne(intval($this->recordId));
            if (empty($record)) return $res;
            $record->refSchet =  $this->dataVal;
            $record->reservDate = date("Y-m-d H:i");
            $record->refManager  = $curUser->id;
            $record->inUse        = 1;
            $record->save();
            $res['val'] =  $record->refSchet;            
        break;
        
                
        default:
        
        return $res;
     }
    $res['res'] = true;
    return $res;
    }
 
 
public function getTransportTarifProvider($params)
   {

   
    $query  = new Query();
    $query->select ([
                      '{{%transport_tarif}}.id',
                      '{{%transport_tarif}}.city',                      
                      '{{%transport_tarif}}.company',
                      '{{%transport_tarif}}.v1',
                      '{{%transport_tarif}}.v2',
                      '{{%transport_tarif}}.v3',
                      '{{%transport_tarif}}.v4',
                      '{{%transport_tarif}}.v5',
                      '{{%transport_tarif}}.v6',
                      '{{%transport_tarif}}.v7',
                      '{{%transport_tarif}}.timeNote',
                      
                      ])                      
            ->from("{{%transport_tarif}}")
            ->distinct()
            ;

    $countquery  = new Query();
    $countquery->select ("count(DISTINCT ({{%transport_tarif}}.id) )")
            ->from("{{%transport_tarif}}")            ;     
     
    if (($this->load($params) && $this->validate())) {

         $query->andFilterWhere(['Like', '{{%transport_tarif}}.city', $this->city]);
         $countquery->andFilterWhere(['Like', '{{%transport_tarif}}.city', $this->city]);

         $query->andFilterWhere(['Like', '{{%transport_tarif}}.company', $this->company]);
         $countquery->andFilterWhere(['Like', '{{%transport_tarif}}.company', $this->company]);
         
     }



    $command = $query->createCommand();
    $count = $countquery->createCommand()->queryScalar();

    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 15,
            ],

            'sort' => [

            'attributes' => [
                      'id',
                      'city',                      
                      'company',
                      'v1',
                      'v2',
                      'v3',
                      'v4',
                      'v5',
                      'v6',
                      'v7',
             ],
            'defaultOrder' => [ 'id'  => 'SORT_DESC'],
            ],

        ]);
    return  $dataProvider;
   }
/********************/


/**/
 }

