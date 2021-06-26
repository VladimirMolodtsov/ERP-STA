<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;


/**
 * MarketViewForm  - модель 
 */


class MarketViewForm extends Model
{

    public $title= "";
    public $area= "";
    public $city= "";
    public $razdel = "";
    public $setSort    ="";
    public $contactPhone ="";
    public $schetINN="";
    public $userFIO    ="";
    public $mode=1;
    public $findString = "";
    
    public function rules()
    {
        return [
            [['title', 'area','razdel', 'city', 'contactPhone',    'schetINN', 'userFIO'], 'safe'],
        ];
    }

   public function search($params)
   {
    
    $query  = new Query();
       $query->select ("{{%orglist}}.id,  {{%orglist}}.title, {{%adreslist}}.area, {{%adreslist}}.city, razdel, {{%orglist}}.contactPhone, {{%orglist}}.schetINN, {{%user}}.userFIO,")
            ->from("{{%orglist}}")
            ->leftJoin('{{%adreslist}}','{{%orglist}}.id = {{%adreslist}}.ref_org')
            ->leftJoin('{{%user}}','{{%orglist}}.refManager = {{%user}}.id');

    $countquery  = new Query();
       $countquery->select (" count({{%orglist}}.id)")
            ->from("{{%orglist}}")
            ->leftJoin('{{%adreslist}}','{{%orglist}}.id = {{%adreslist}}.ref_org')
            ->leftJoin('{{%user}}','{{%orglist}}.ref_user = {{%user}}.id');

          $query->andWhere("{{%orglist}}.isOrgActive = 1");
     $countquery->andWhere("{{%orglist}}.isOrgActive = 1");
          
        
    if (($this->load($params) && $this->validate())) {
     
     $query->andFilterWhere(['like', 'title', $this->title]);
     $query->andFilterWhere(['like', 'area', $this->area]);
     $query->andFilterWhere(['like', 'city', $this->city]);
     $query->andFilterWhere(['like', 'razdel', $this->razdel]);
     $query->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);
     $query->andFilterWhere(['like', 'schetINN', $this->schetINN]);
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
    
    
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'area', $this->area]);
     $countquery->andFilterWhere(['like', 'city', $this->city]);
     $countquery->andFilterWhere(['like', 'razdel', $this->razdel]);
     $countquery->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);
     $countquery->andFilterWhere(['like', 'schetINN', $this->schetINN]);     
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     }

    $command = $query->createCommand();    
    $count = $countquery->createCommand()->queryScalar();

    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "title";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }


    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,            
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 8,
            ],
            
            'sort' => [
            
            'attributes' => [
            'title',
            'area',
            'city',
            'razdel',
            'contactPhone',
            'schetINN', 
            'userFIO'
             ],
            
            'defaultOrder' => [    $this->setSort => $order ],
            
            ],
            
        ]);


        
    return  $dataProvider;     
   }   
  
  
   public function findByString($params)
   {
    
    $query  = new Query();
       $query->select ("{{%orglist}}.id,  {{%orglist}}.title,  {{%orglist}}.contactPhone,{{%orglist}}.contactEmail, {{%orglist}}.schetINN, {{%user}}.userFIO, razdel, isOrgActive")
            ->from("{{%orglist}}")
            ->leftJoin('{{%adreslist}}','{{%orglist}}.id = {{%adreslist}}.ref_org')
            ->leftJoin('{{%emaillist}}','{{%orglist}}.id = {{%emaillist}}.ref_org')
            ->leftJoin('{{%phones}}','{{%orglist}}.id = {{%phones}}.ref_org')
            ->leftJoin('{{%user}}','{{%orglist}}.refManager = {{%user}}.id')
            ->distinct();                      
    //$query->groupBy("{{%orglist}}.id,  {{%orglist}}.title, {{%adreslist}}.area, {{%adreslist}}.city, razdel, {{%orglist}}.contactPhone,{{%orglist}}.contactEmail, {{%orglist}}.schetINN, {{%user}}.userFIO");        
            
    $countquery  = new Query();
       $countquery->select (" count(DISTINCT({{%orglist}}.id))")
            ->from("{{%orglist}}")
            ->leftJoin('{{%adreslist}}','{{%orglist}}.id = {{%adreslist}}.ref_org')
            ->leftJoin('{{%emaillist}}','{{%orglist}}.id = {{%emaillist}}.ref_org')
            ->leftJoin('{{%phones}}','{{%orglist}}.id = {{%phones}}.ref_org')
            ->leftJoin('{{%user}}','{{%orglist}}.ref_user = {{%user}}.id');
                    
   //$countquery->groupBy("{{%orglist}}.id,  {{%orglist}}.title, {{%adreslist}}.area, {{%adreslist}}.city, razdel, {{%orglist}}.contactPhone,{{%orglist}}.contactEmail, {{%orglist}}.schetINN, {{%user}}.userFIO");        
   
    if (!(empty($this->findString)))    
    {
        $fstr = urldecode($this->findString);
        $query->andWhere("(title LIKE '%".$fstr."%' OR {{%phones}}.phone LIKE '%".$fstr."%' OR {{%emaillist}}.email LIKE '%".$fstr."%' OR city  LIKE '%".$fstr."%' OR {{%orglist}}.contactEmail  LIKE '%".$fstr."%' OR {{%orglist}}.schetINN  LIKE '%".$fstr."%' OR {{%orglist}}.contactPhone  LIKE '%".$fstr."%')");
        $countquery->andWhere("(title LIKE '%".$fstr."%' OR {{%phones}}.phone LIKE '%".$fstr."%' OR {{%emaillist}}.email LIKE '%".$fstr."%' OR city  LIKE '%".$fstr."%' OR {{%orglist}}.contactEmail  LIKE '%".$fstr."%' OR {{%orglist}}.schetINN  LIKE '%".$fstr."%' OR {{%orglist}}.contactPhone  LIKE '%".$fstr."%')");
    }
        
    if (($this->load($params) && $this->validate())) {
     
     $query->andFilterWhere(['like', 'title', $this->title]);
     $query->andFilterWhere(['like', 'area', $this->area]);
     $query->andFilterWhere(['like', 'city', $this->city]);
     $query->andFilterWhere(['like', 'razdel', $this->razdel]);
     $query->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);
     $query->andFilterWhere(['like', 'schetINN', $this->schetINN]);
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
    
    
     $countquery->andFilterWhere(['like', 'title', $this->title]);
     $countquery->andFilterWhere(['like', 'area', $this->area]);
     $countquery->andFilterWhere(['like', 'city', $this->city]);
     $countquery->andFilterWhere(['like', 'razdel', $this->razdel]);
     $countquery->andFilterWhere(['like', 'contactPhone', $this->contactPhone]);
     $countquery->andFilterWhere(['like', 'schetINN', $this->schetINN]);     
     $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);
     }

    $command = $query->createCommand();    
    $count = $countquery->createCommand()->queryScalar();

    $order = SORT_ASC;
    if (isset($this->setSort) ==false )
    {
        $this->setSort = "title";
    }
    
    if (strstr($this->setSort,"-"))
    {
        $this->setSort = substr($this->setSort,1);
        $order = SORT_DESC;
    }


    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,            
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 8,
            ],
            
            'sort' => [
            
            'attributes' => [
            'title',
            'area',
            'city',
            'razdel',
            'contactPhone',
            'schetINN', 
            'contactEmail',
            'userFIO'
             ],
            
            'defaultOrder' => [    $this->setSort => $order ],
            
            ],
            
        ]);


        
    return  $dataProvider;     
   }   
  
 

  
 }
