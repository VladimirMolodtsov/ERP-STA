<?php

namespace app\modules\sale\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


use app\models\OrgList;
use app\models\PhoneList;
use app\models\EmailList;
use app\models\ZakazList;
use app\models\ZakazContent;
use app\models\AdressList;

/**
 * OrderForm - Планирование - суммарные значения
 */
 
 class OrderForm extends Model
{
    
    
    public $email ='';
    public $id =0;
    
    public $orgTitle ='';
    public $orgInn ='';
    public $orgKpp ='';
    public $orgAdress ='';
    public $orgPhone ='';
    public $contactFIO ='';
    public $orgRef = 0;
    
    /*** Filter *******/    
    public $wareTitle ='';
    public $wareTypeName ='';
    public $wareGrpTitle =''; 
    
    /**** Service *****/
    public $debug;
    
       /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    public $dataId; 
        
    public function rules()
    {
        return [            
            [['email', 'orgTitle', 'orgInn', 'orgKpp', 'orgAdress', 'orgPhone', 'contactFIO', 'id', 'orgRef',
              'recordId','dataType','dataVal','dataId'            
             ], 'default'],                        
             
            [['wareTitle', 'wareGrpTitle', 'wareTypeName' ], 'safe'],            
            
            
            [['id' ], 'integer'],                        
            [['email' ], 'email'],                        
        ];
    }


/****************************************************************************************/
 /**
 * Загружаем сохраненные
 * @param  
 * @return true/false загружен/не загружен
 * @throws Exception none
 */   
   public function loadOrder()    
   {
     /* пробуем загрузить по почте предыдущий заказ */ 
     if(empty($this->id)) return false;
     $record= ZakazList::findOne($this->id);
     if(empty($record)) return false;
       
     $this->orgRef = $record->refOrg;
     $this->email   = $record->clientEmail;
     
     $res = $this->getOrgById($record->clientEmail, $record->refOrg);
     if ($res['N'] > 0)
     {
         $orgData = $res['orgData'][0];
         $this->orgTitle   = $orgData['orgTitle'];
         $this->orgInn     = $orgData['orgInn'];
         $this->orgKpp     = $orgData['orgKpp'];
         $this->orgAdress  = $orgData['orgAdress'];
         $this->orgPhone   = $orgData['orgPhone'];
         $this->contactFIO = $orgData['contactFIO'];
     }
    return true;        
   }
 /****************************************************************************************/
 /**
 * Поиск организации по email
 * @param  $email - String адрес электронной почты
 * @return массив с параметрами организации
 * @throws Exception none
 */  
 
   public function getOrgByEmail($email)
   {     
    $res = [ 
                'res' => false,        
                'orgData' => [],
                'N' => 0
              ];   
    $list = Yii::$app->db->createCommand("Select {{%emaillist}}.ref_org as  orgRef, {{%emaillist}}.emailContactFIO,  {{%orglist}}.contactFIO,
        {{%orglist}}.title as orgTitle, {{%orglist}}.orgINN, {{%orglist}}.orgKPP, {{%orglist}}.contactPhone as orgPhone
        FROM {{%emaillist}}, {{%orglist}}  where {{%emaillist}}.ref_org = {{%orglist}}.id and {{%orglist}}.isOrgActive =1 
        and {{%emaillist}}.email = :email", 
        [':email'=>$email])->queryAll();             
    
    $N = count($list);
    $res['N']=$N;
    for ($i=0; $i<$N; $i++){  
    
    $zakazId=0;
    $zakazRec= ZakazList::findOne([
    'refOrg' => $list[$i]['orgRef'],
    'isByClient' => 1
    ]);
    if(!empty($zakazRec)) $zakazId = $zakazRec->id;
    
    if (empty($list[$i]['emailContactFIO'])) $contactFIO = $list[$i]['contactFIO'];
                                        else $contactFIO = $list[$i]['emailContactFIO'];
    $adress = $this->getAdress($list[$i]['orgRef']);                                        
    $res['orgData'][] =[
                'orgTitle' =>$list[$i]['orgTitle'],
                'orgInn' =>$list[$i]['orgINN'],
                'orgKpp' =>$list[$i]['orgKPP'],
                'orgAdress' =>$adress,
                'orgPhone' =>$list[$i]['orgPhone'],
                'contactFIO' =>$contactFIO,
                'orgRef' =>$list[$i]['orgRef'],
                'zakazId' => $zakazId,
                ];
    }     
        
    $res['res'] = true;    
    return $res;
    }    


    
 /****************************************************************************************/
 /**
 * Поиск организации по id 
 * @param  $orgId - Integer идентифактор организации в базе 
 * @param  $email - String адрес электронной почты
 * @return массив с параметрами организации
 * @throws Exception none
 */  
 
   public function getOrgById($email, $orgId)
   {     
    $res = [ 
                'res' => false,        
                'orgData' => [],
                'N' => 0
              ];   

              
      $list = Yii::$app->db->createCommand("Select {{%orglist}}.id as orgRef, {{%emaillist}}.emailContactFIO as contactFIO,
        {{%orglist}}.title as orgTitle, {{%orglist}}.orgINN, {{%orglist}}.orgKPP, {{%orglist}}.contactPhone as orgPhone
        FROM {{%emaillist}}, {{%orglist}}  where {{%emaillist}}.ref_org = {{%orglist}}.id and {{%orglist}}.isOrgActive =1 
        and {{%emaillist}}.email = :email and {{%emaillist}}.ref_org = :orgRef", 
        [':email' => $email, ':orgRef' => intval($orgId)])->queryAll();             
    
    $N = count($list);    
    $res['N']=$N;

    if ($N == 0) return $res;
    if (empty($list[0]['emailContactFIO'])) $contactFIO = $list[0]['contactFIO'];
                                       else $contactFIO = $list[0]['emailContactFIO'];
    $adress = $this->getAdress($list[0]['orgRef']);
    $zakazId=0;
    $zakazRec= ZakazList::findOne([
    'refOrg' => $list[0]['orgRef'],
    'isByClient' => 1
    ]);
    if(!empty($zakazRec)) $zakazId = $zakazRec->id;

        
    $res['orgData'][0] =[
                'orgTitle' =>$list[0]['orgTitle'],
                'orgInn' =>$list[0]['orgINN'],
                'orgKpp' =>$list[0]['orgKPP'],
                'orgAdress' =>$adress,
                'orgPhone' =>$list[0]['orgPhone'],
                'contactFIO' =>$contactFIO,
                'orgRef' =>$list[0]['orgRef'],
                'zakazId' => $zakazId,                
                ];
         
    $res['res'] = true;    
    return $res;
    }    
 
 /****************************************************************************************/
 /**
 * Поиск адреса организации по id организации
 * @param  $orgId - Integer идентифактор организации в базе 
 * @return массив с параметрами организации
 * @throws Exception none
 */  
 
   public function getAdress($orgId)
   {     
     $adress = "";
     $adrRecord= AdressList::findOne(
      [
      'isOfficial' => 1,
      'ref_org'    => $orgId
      ]);     
      if (empty($adrRecord)) 
      $adrRecord= AdressList::find()
        ->where (['ref_org'    => $orgId])
        ->andWhere (["!=","ifnull(adress,'')",""])
        ->one();                
      if (!empty($adrRecord)) 
      {
        $adress = $adrRecord->adress;
      }
    return $adress;
    }    
      
 /****************************************************************************************/
 /**
 * Сохранение параметров заказа
 * @param  POST переменные 'recordId', 'dataType', 'dataVal', 'dataId'
 * @return массив с результатом изменения
 * @throws Exception none
 */  
   public function saveOrderDetail()
   {     
    $ret = [ 
               'res' => false,        
               'id' => $this->id, 
               'dataType' => $this->dataType, 
               'dataVal'  => $this->dataVal, 
               'dataId'   => $this->dataId,
               'val' => '',            
            ];   
      
      /*Должно быть известно*/
      if (empty($this->orgRef)) return $ret;
      //Нет заказа - создадим
      if (empty($this->id)) {
        $record=new ZakazList();        
        $record->refOrg = $this->orgRef;
        $record->isActive = 1;
        $record->ref_user = 0;
        $record->formDate = date("Y-m-d");
        $record->isByClient=1;
        $record->clientEmail = $this->email;        
        $record->save();
        $this->id = $record->id;
      }else{
         $record=ZakazList::findOne( intval($this->id));
      }
      if (empty($record)) return $ret;
    switch( $this->dataType)  
    {
        case 'wareInOrder' :
            $contentRecord = ZakazContent::findOne([
            'refZakaz' => $record->id,
            'wareNameRef' => intval($this->dataId)
            ]);
            if(empty($contentRecord)){
                $contentRecord = new ZakazContent();
                if (empty($contentRecord )) return $ret;
                $contentRecord ->refZakaz = $record->id;
                $contentRecord ->wareNameRef = intval($this->dataId);
                $contentRecord ->count = 1;
                $contentRecord ->isActive = 1;
            }            
            else {
                if ($contentRecord ->isActive == 1) $contentRecord ->isActive = 0;
                                               else $contentRecord ->isActive = 1;            
            }
            $contentRecord ->save();
            $ret['val'] = $contentRecord ->isActive;
        break;
    
        case 'wareCount' :
            $contentRecord = ZakazContent::findOne([
            'refZakaz' => $record->id,
            'wareNameRef' => intval($this->dataId)
            ]);
            if(empty($contentRecord)){
                $contentRecord = new ZakazContent();
                if (empty($contentRecord )) return $ret;
                $contentRecord ->refZakaz = $record->id;
                $contentRecord ->wareNameRef = intval($this->dataId);
                $contentRecord ->count = (float)str_replace(',', '.',$this->dataVal); 
                $contentRecord ->isActive = 1;
            }            
            else {
                $contentRecord ->count = (float)str_replace(',', '.',$this->dataVal); 
                $contentRecord ->isActive = 1;
            }
            $contentRecord ->save();
            $ret['val'] = $contentRecord ->count;
        break;
    
    }
   
    $ret['id'] = $this->id;
    $ret['res'] = true;
    return $ret;
   }   
   
/*use app\models\ZakazList;
use app\models\ZakazContent;   */
 /****************************************************************************************/
 /**
 * Provider - список товаров (прайс)
 * @param  $params - request GET params (filter)
 * @return provider
 * @throws Exception none
 */  
 public function getWarePriceProvider ($params) 
 {

     $query  = new Query();
     $countquery  = new Query();

     
          $query->select([ 
                    '{{%ware_names}}.id',
                    '{{%ware_names}}.wareGrpRef',
                    '{{%ware_names}}.wareTypeRef',
                    'wareTitle',
                    'wareEd',
                    'v1',
                    'v2',
                    'v3',
                    'v4',
                    '{{%ware_type}}.wareTypeName',
                    '{{%ware_grp}}.wareGrpTitle'
                  ])
                  ->from("{{%ware_names}}")                                                  
                  ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
                  ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
                  ->distinct();      

     $countquery->select ("count(distinct {{%ware_names}}.id)")
                  ->from("{{%ware_names}}")                                                  
                  ->leftJoin("{{%ware_grp}}","{{%ware_grp}}.id= {{%ware_names}}.wareGrpRef")
                  ->leftJoin("{{%ware_type}}","{{%ware_type}}.id= {{%ware_names}}.wareTypeRef")                        
                 ;

                 
     $countquery->andWhere("ifnull({{%ware_names}}.wareTitle,'') != ''" );
          $query->andWhere("ifnull({{%ware_names}}.wareTitle,'') != ''" );
            
     $countquery->andFilterWhere(['=', '{{%ware_names}}.isInPrice', 1]);
          $query->andFilterWhere(['=', '{{%ware_names}}.isInPrice', 1]);
                  
     if (($this->load($params) && $this->validate())) 
     {
               $query->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareTitle', $this->wareTitle]);         
   
          $query->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
     $countquery->andFilterWhere(['like', '{{%ware_names}}.wareEd', $this->wareEd]);         
                   
          $query->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareTypeRef', $this->wareTypeName]);         
    
          $query->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);         
     $countquery->andFilterWhere(['=', '{{%ware_names}}.wareGrpRef', $this->wareGrpTitle]);                 
     }
     
     $command = $query->createCommand();    
     $count = $countquery->createCommand()->queryScalar();

     $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
                    'id',
                    'wareTitle',
                    'wareTypeName',
                    'wareGrpTitle',
            ],
            'defaultOrder' => ['wareTypeName' => SORT_ASC , 'wareGrpTitle' => SORT_ASC , 'wareTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;     
    
 }

  
  
  /************End of model*******************/ 
 }
