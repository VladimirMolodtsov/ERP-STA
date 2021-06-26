<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;

use app\models\OrgList;
use app\models\SchetList;


/**
 * 
 */
class ClientSchetForm extends Model
{
    public $debug;  
    public $orgINN;
    public $orgKPP;
    
    public $refOrg;  //ссылка на организацию 
    public $refSchet;   //ссылка на счет системы ({{%schet}})       
    public $fltOrg=1;    
    
    public $orgTitle ="";  //ссылка на организацию
    public $showOrgTitle ="";  //ссылка на организацию  
    
    public $refClientData;   //текущий привязанный счет
    public $refClientDetail;   //текущий привязанный счет
    
    public function rules()
    {
        return [
  
            [['orgINN', 'orgKPP', 'orgTitle'], 'safe'],
        ];
    }

/*************************************************************************************/
/*************************************************************************************/
/************/   

 public function initData()
 {
    if (!empty($this->refOrg)){
      $orgRecord= OrgList::findOne(intval($this->refOrg));
      if (!empty($orgRecord)) $this->showOrgTitle = $orgRecord->title;
    }
    if (!empty($this->refSchet)){

      $schetRecord= SchetList::findOne(intval($this->refSchet));      
      if (empty($schetRecord)) return;
      if (empty($schetRecord->refClientSchet)) return;

      $this->refClientData =  Yii::$app->db->createCommand( 'SELECT sum(wareSum) as sum, schetRef1C, schetDate  
      from {{%client_schet_header}} left join {{%client_schet_content}} on {{%client_schet_header}}.id={{%client_schet_content}}.refHeader
       where {{%client_schet_header}}.id =:refClientSchet', 
        [':refClientSchet' => $schetRecord->refClientSchet])->queryOne();
      
      $this->refClientDetail =  Yii::$app->db->createCommand( 'SELECT wareTitle, wareCount, wareEd, wareSum  
      from {{%client_schet_content}} where refHeader =:refClientSchet', 
        [':refClientSchet' => $schetRecord->refClientSchet])->queryAll();
            
    }

 }

  public function getClientSchetProvider($params)
   {
    
    $query  = new Query();
    $query->select ([
            'id',
            'orgINN',
            'orgKPP',
            'orgTitle',
            'schetRef1C',
            'schetDate',
                ])
           ->from("{{%client_schet_header}}")
           ->distinct();

    $countquery  = new Query();
    $countquery->select ("count({{%client_schet_header}}.id)")
           ->from("{{%client_schet_header}}")
           ->distinct();

//$this->debug[]=$this->refOrg;
//$this->debug[]=$this->fltOrg;
     if (!empty($this->refOrg) && $this->fltOrg==1){
          $query->andWhere(['=', 'refOrg', $this->refOrg]);
     $countquery->andWhere(['=', 'refOrg', $this->refOrg]);

     }

    if (($this->load($params) && $this->validate())) {
          $query->andFilterWhere(['like', 'orgINN', $this->orgINN]);
     $countquery->andFilterWhere(['like', 'orgINN', $this->orgINN]);
     
          $query->andFilterWhere(['like', 'orgKPP', $this->orgKPP]);
     $countquery->andFilterWhere(['like', 'orgKPP', $this->orgKPP]);

          $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
     $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);


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
            'id', 
            'wareTitle',
            'wareCount',
            'wareEd',
            'warePrice',
            'dopRequest',
            'dostavka',
            ],
            'defaultOrder' => [ 'id' => SORT_DESC ],
            ],
            
        ]);
    return  $dataProvider;   
   }   

   
   /** end of object **/    
   
 }
