<?php

/**
    Операции по работе с клиентскими данными
**/
namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\AdressList;
use app\models\TblOrgAccounts;

use app\models\TblRazdelList;

class ClientData extends Model 
{
    public $status = 0;
    public $id = 0;
        
    public $contactFIO = "";
    public $contactDate ="";
    public $contactPhone="";
    public $userFIO ="";
    public $orgId ="";
    public $title ="";
    
    public $adressId    ="";
    public $adressArea  ="";
    public $adressCity  ="";
    public $adressDistrict  ="";
    public $adress       ="";

    public $contactEmail ="";

    public $orgRecord;
    
    public function rules()
    {
        return [
            [['orgId', 'title', 'adressId', 'adressArea', 'adressCity', 'adressDistrict', 'adress', 'contactEmail', 'contactPhone', 'contactFIO' ], 'default'],
            [['contactFIO',  'contactDate', 'userFIO' ], 'safe'],            
        ];
    }
/*********************************************************************************/
/*                Редактирование и добавление                                    */    
/*********************************************************************************/

/*******************************************************/
/*************** Сохранение из формы *******************/    
/*******************************************************/
   public function saveData()        
   {
      
      $orgRecord   = OrgList::findOne(['id'   => $this->orgId ]);     
      $orgRecord->contactPhone = $this->contactPhone;
      $orgRecord->contactEmail = $this->contactEmail;
      $orgRecord->title = $this->title;
      $orgRecord->save();
  
      $phoneCount = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%phones}} where phone=:phone AND ref_org=:ref_org  ', 
            [
            ':phone'   => $this->contactPhone,
            ':ref_org' => $this->orgId,
            ])->queryScalar();

      if ($phoneCount == 0)
      {          
         $phoneRecord = new PhoneList ();
         $phoneRecord->ref_org = $this->orgId;
         $phoneRecord->phone   = $this->contactPhone;
         $phoneRecord->save();
      }

      
      $adressRecord   = AdressList::findOne(['id'   => $this->adressId ]);           
      $adressRecord->area = $this->adressArea;
      $adressRecord->city = $this->adressCity;
      $adressRecord->district = $this->adressDistrict;
      $adressRecord->adress = $this->adress;      
      $adressRecord->save();      
  }    

  
/*******************************************************/
/*************** Сохранение из массива *****************/    
/*******************************************************
    Структура массива
    
    
    
*/
public function getEmptyOrgArray()    
    {
    $orgArray = array(
    'orgTitle' => '',
    'orgFullTitle' => '',
    'orgINN' => '',
    'orgKPP' => '',
    'orgRS' => '',
    'orgBIK' => '',
    'orgKS' => '',
    'orgBank' => '',    
    'orgManager' => '',
    'orgRazdel' => '',
    'orgRazdelList' => array(),
    'orgPodRazdelList'=> array(),
    'orgRubrikList'=> array(),
    'contactPhone' => '',
    'contactFIO' => '',        
    'orgPhoneList' => array(),
    'orgAdress' => [['adress' => '',
                  'city' => '',
                  'area' => '',
                  'district' => '',
                  'X' => '',
                  'Y' => '',
                  'index' => '',
                  ], ],
    'orgEmailList' => array(),
    'orgUrlList' => array(),
    'orgNote' => '',                  
    'orgSource' => '',
    'isFirstContactFinished' => 0,
    'isFirstContact' => 0,
    'isReject' => 0,
    'isNeedFinished' => 0,
    'isPreparedForSchet' => 0,        
    'supplierType' => 0,    
    );
    
    return $orgArray;
    }
    
public function findOrgByArray($orgArray)    
{
    if (empty ($orgArray['orgTitle'])) {return 1;}


    /*По ИНН*/
    if (!empty ($orgArray['orgINN']))
     {         
        if (!empty ($orgArray['orgKPP']))
        {
            $orgRecord = OrgList::findOne(['schetINN' => $orgArray['orgINN'], 'orgKPP' => $orgArray['orgKPP']]);
        }
        else
        {
            $orgRecord = OrgList::findOne(['schetINN' => $orgArray['orgINN'], 'title' => $orgArray['orgTitle']]);
        }
        
        if(!empty($orgRecord))
        {
            $this->orgRecord = $orgRecord;
            $this->orgId = $orgRecord->id;
            return 1;      
        }
     }

    /*По названию */
    $isFind=0;
    
    /*Ищем название*/

     $list= Yii::$app->db->createCommand('SELECT {{%orglist}}.id FROM {{%orglist}}             
            where {{%orglist}}.title=:title 
            ')
            ->bindValue(':title',$orgArray['orgTitle'])
            ->queryAll();
            if (count($list) == 0)
            {
            /*Названия нет - не нашли*/    
                return 0;
            }    

    /*Есть ли такая в городе ?*/    
    $city="";
     for ($i=0; $i< count($orgArray['orgAdress']);$i++)
     {
         if (empty($orgArray['orgAdress'][$i]['city'])){ continue;}
            $city=$orgArray['orgAdress'][$i]['city'];
            break;
     }
    
     
        if ( empty($city))
        {
            /*Тогда название есть вот и чудно*/
            if (count($list) > 0)
            {
                $this->orgId = $list[0]['id'];
                $isFind=1;
                return $isFind;    
            }    
        }    
        
        
        /*У нас адрес есть*/
        
        /*Если в базе есть с таким названием и без адреса, то это оно*/
         $list= Yii::$app->db->createCommand('SELECT {{%orglist}}.id FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org            
            where {{%orglist}}.title=:title AND ( {{%adreslist}}.city IS NULL )
            ')
            ->bindValue(':title',$orgArray['orgTitle'])
            ->queryAll();
        if (count($list) > 0)
        {
            $this->orgId = $list[0]['id'];
            $isFind=1;
            return $isFind;    
        }    

    
       /* С указанием города */       
        $cnt= Yii::$app->db->createCommand('SELECT count({{%orglist}}.id) FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org            
            where {{%orglist}}.title=:title AND ( {{%adreslist}}.city=:city )
            ')
            ->bindValue(':title',$orgArray['orgTitle'])
            ->bindValue(':city', $city)
            ->queryScalar();

            
        if ($cnt > 0)
            {
                
            /*А нет у нас телефона - n*/    
            
            $cntArray = count($orgArray['orgPhoneList']);
            if ( $cntArray == 1 && empty($orgArray['orgPhoneList'][0]))  {$cntArray = 0;}
        
        if ($cntArray == 0 )
        {
            $list=  Yii::$app->db->createCommand('SELECT {{%orglist}}.id as id FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org            
            where {{%orglist}}.title=:title AND ( {{%adreslist}}.city=:city )
            ')
            ->bindValue(':title',$orgArray['orgTitle'])
            ->bindValue(':city', $city)
            ->queryAll();


            
             if (count($list) > 0)    
             {
                $this->orgId = $list[0]['id'];
                return 1;                     
             }
             else return 0;                     
        }
                
   /* Есть все! */                

            $existPhones= Yii::$app->db->createCommand('SELECT phone FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org    
            left join {{%phones}}  on {{%orglist}}.id = {{%phones}}.`ref_org`
            where {{%orglist}}.title=:title AND {{%adreslist}}.city=:city
            ')
            ->bindValue(':title', $orgArray['orgTitle'])
            ->bindValue(':city', $city)            
            ->queryAll();
            /*Есть с таким же телефоном в этом городе*/  
            
            
            for ($i=0; $i<count($orgArray['orgPhoneList']); $i++ )
            {
            $orgArray['orgPhoneList'][$i] = trim($orgArray['orgPhoneList'][$i]);
              if ($orgArray['orgPhoneList'][$i]==""){continue;}
              
              for ($j=0; $j<count($existPhones); $j++ )
              {
                  if ($existPhones[$j]['phone'] == $orgArray['orgPhoneList'][$i]){$isFind=1; break;}
              }
              
              //if (ArrayHelper::isIn(trim($orgArray['orgPhoneList'][$i]), $existPhones))
              //if (in_array(trim($orgArray['orgPhoneList'][$i]), $existPhones))
               if($isFind==1)
              {
                 $phoneFinded = trim($orgArray['orgPhoneList'][$i]);
              }              
              
                              
              
            }            
            }
            
    if ($isFind==1)            
    {
        $this->orgId = Yii::$app->db->createCommand('SELECT {{%orglist}}.id  FROM {{%orglist}} 
            LEFT JOIN {{%adreslist}}  ON {{%orglist}}.id = {{%adreslist}}.ref_org    
            left join {{%phones}}  on {{%orglist}}.id = {{%phones}}.`ref_org`
            where {{%orglist}}.title=:title AND {{%adreslist}}.city=:city
            AND {{%phones}}.phone=:phone
            ')
            ->bindValue(':title', $orgArray['orgTitle'])
            ->bindValue(':city',  $city)            
            ->bindValue(':phone', $phoneFinded)    
            ->queryScalar();
            
        //$this->orgRecord = OrgList::findOne($this->orgId);
    }        
    return $isFind;    
}

public function setSupplier($orgArray)
{
    $isFind= $this->findOrgByArray($orgArray);
    if ($isFind == 0) return 0;
        
    $orgRecord = OrgList::findOne($this->orgId);
    $orgRecord->contragentType=$orgArray['supplierType'];
    $orgRecord->save();
    return 1;
}

/*
ALTER TABLE `rik_orglist` ADD COLUMN `orgBIK` VARCHAR(20) DEFAULT NULL;
ALTER TABLE `rik_orglist` ADD COLUMN `orgAccount` VARCHAR(50) DEFAULT NULL;
ALTER TABLE `rik_orglist` ADD COLUMN `orgBank` VARCHAR(150) DEFAULT NULL;
ALTER TABLE `rik_orglist` ADD COLUMN `orgKS` VARCHAR(50) DEFAULT NULL;
ALTER TABLE `rik_orglist` ADD INDEX `rik_orglist_idx1` (`schetINN`);
ALTER TABLE `rik_orglist` ADD INDEX `rik_orglist_idx2` (`orgKPP`);
ALTER TABLE `rik_orglist` ADD INDEX `rik_orglist_idx3` (`orgAccount`);
*/
public function updateFromArray($orgArray)
{
    $isFind= $this->findOrgByArray($orgArray);
    if ($isFind == 0) return 0;
        
    $orgRecord = OrgList::findOne($this->orgId);
    if (empty($orgRecord)) {
    echo "Can not find ".$this->orgId." - ".$orgArray['orgTitle']."\n";; 
    return 0;}
    $orgRecord->title      =  mb_substr($orgArray['orgTitle'],0,245);
    if (!empty($orgArray['orgINN']))$orgRecord->schetINN =  $orgArray['orgINN'];
    if (!empty($orgArray['orgKPP']))$orgRecord->orgKPP =  $orgArray['orgKPP'];
    $orgRecord->razdel      = $orgArray['orgRazdel'];
    if (empty($orgRecord->refManager)) $orgRecord->refManager = $orgArray['orgManager'];
    if (empty($orgRecord->orgNote)) $orgRecord->orgNote= mb_substr($orgArray['orgNote'],0,500);;
    $orgRecord->source= $orgArray['orgSource'];
    if (empty($orgRecord->contactPhone)) $orgRecord->contactPhone= $orgArray['contactPhone'];
    if (empty($orgRecord->contactFIO)) $orgRecord->contactFIO= $orgArray['contactFIO'];
    $orgRecord->isFirstContactFinished= $orgArray['isFirstContactFinished'];
    $orgRecord->isFirstContact= $orgArray['isFirstContact'];
    $orgRecord->isReject= $orgArray['isReject'];
    $orgRecord->isNeedFinished= $orgArray['isNeedFinished'];
    $orgRecord->isPreparedForSchet= $orgArray['isPreparedForSchet'];
    $orgRecord->orgFullTitle= $orgArray['orgFullTitle'];
    //$orgRecord->contragentType=$orgArray['supplierType'];

    $orgRecord->orgBIK=$orgArray['orgBIK'];
    $orgRecord->orgAccount=$orgArray['orgRS'];
    $orgRecord->orgBank=$orgArray['orgBank'];
    $orgRecord->orgKS=$orgArray['orgKS'];
    
    
    $orgRecord->isNew=1;
    $orgRecord->save();
    $uid = $orgRecord->id;
    $this->orgId = $uid;
    
    
//    print_r($orgArray['orgAdress']);
//    exit (0);
    
    for($i=0; $i<count($orgArray['orgAdress']); $i++)
    {
      $adressRecord = AdressList::findOne([
          'adress' => $orgArray['orgAdress'][$i]['adress'],
          'ref_org' => $uid,
      ]);
      if (empty($adressRecord))
      {
          $adressRecord = new AdressList();
      }
      if(empty($adressRecord->area))$adressRecord->area = $orgArray['orgAdress'][$i]['area'];
      if(empty($adressRecord->city))$adressRecord->city= $orgArray['orgAdress'][$i]['city'];
     // $adressRecord->isOfficial= $orgArray['orgAdress'][$i]['isOfficial'];
      $adressRecord->adress= $orgArray['orgAdress'][$i]['adress'];
      $adressRecord->ref_org = $uid;
     // if(empty($adressRecord->index))$adressRecord->index= $orgArray['orgAdress'][$i]['index'];
      $adressRecord->save();    
    }
    
    if (!empty($orgArray['orgRS'])){
    $accRecord = TblOrgAccounts::findOne([
          'orgRS' => $orgArray['orgRS'],
          'orgKS' => $orgArray['orgKS'],
          'orgBIK' => $orgArray['orgBIK'],
          'refOrg' => $uid,
      ]);
     if (empty($accRecord))$accRecord=new TblOrgAccounts();
     //if (empty($accRecord)) continue;
     $accRecord->orgBIK = $orgArray['orgBIK'];
     $accRecord->orgBank = $orgArray['orgBank'];
     $accRecord->orgRS=$orgArray['orgRS'];
     $accRecord->orgKS = $orgArray['orgKS'];
     $accRecord->isActive=1;
     $accRecord->refOrg=$uid;
     $accRecord->save();
     }
     
     
    return 1;
    
    
}
    
public function saveFromArray($orgArray)
    {            
        mb_internal_encoding("UTF-8");
        
        /*Ищем данную организацию*/
        $isFind= $this->findOrgByArray($orgArray);

        
        /* такая организация уже есть*/
        if ($isFind ==1) {return 0;}
            
//print_r($orgArray);

            /*Добавляем организацию*/
            $orgRecord = new OrgList (); 
            $orgRecord->title      =  mb_substr($orgArray['orgTitle'],0,245);
            $orgRecord->orgFullTitle      = $orgArray['orgFullTitle'];
            if (!empty($orgArray['orgINN']))$orgRecord->schetINN =  $orgArray['orgINN'];
            if (!empty($orgArray['orgKPP']))$orgRecord->orgKPP =  $orgArray['orgKPP'];
            $orgRecord->razdel      = $orgArray['orgRazdel'];
            $orgRecord->refManager = $orgArray['orgManager'];
            $orgRecord->orgNote= mb_substr($orgArray['orgNote'],0,500);;
            $orgRecord->source= $orgArray['orgSource'];
            $orgRecord->contactPhone= $orgArray['contactPhone'];
            $orgRecord->contactFIO= $orgArray['contactFIO'];
            $orgRecord->isFirstContactFinished= $orgArray['isFirstContactFinished'];
            $orgRecord->isFirstContact= $orgArray['isFirstContact'];
            $orgRecord->isReject= $orgArray['isReject'];
            $orgRecord->isNeedFinished= $orgArray['isNeedFinished'];
            $orgRecord->isPreparedForSchet= $orgArray['isPreparedForSchet'];
            $orgRecord->contragentType=$orgArray['supplierType'];
            
            
            $orgRecord->orgBIK=$orgArray['orgBIK'];
            $orgRecord->orgAccount=$orgArray['orgRS'];
            $orgRecord->orgBank=$orgArray['orgBank'];
            $orgRecord->orgKS=$orgArray['orgKS'];

            $orgRecord->isNew=1;
            $orgRecord->save();
            $uid = $orgRecord->id;
            $this->orgId = $uid;
            //$this->orgRecord = OrgList::findOne($this->orgId);
            
            /*Добавляем Телефоны*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgPhoneList']);$j++)
                {
                    if (trim($orgArray['orgPhoneList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgPhoneList'][$j]]);
                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%phones}}', ['ref_org', 'phone'], $list)->execute();
                    
            /*Добавляем Адрес*/            
            $list = array();
            for ($j=0;$j<count($orgArray['orgAdress']);$j++)
                {
                    $curAdress = $orgArray['orgAdress'][$j];                    
                    if (trim($curAdress['city'])==""){continue;}
                    array_push($list, [$uid, $curAdress['area'], $curAdress['city'],$curAdress['district'],$curAdress['X'],$curAdress['Y'],$curAdress['adress'], ]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%adreslist}}', ['ref_org', 'area', 'city', 'district', 'x', 'y', 'adress'], $list)->execute();                
            
            /*Добавляем Разделы*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgRazdelList']);$j++)
                {
                    if (trim($orgArray['orgRazdelList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgRazdelList'][$j]]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%razdellist}}', ['ref_org', 'razdel'], $list)->execute();
            
            /*Добавляем ПодРазделы*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgPodRazdelList']);$j++)
                {
                    if (trim($orgArray['orgPodRazdelList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgPodRazdelList'][$j]]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%podrazdellist}}', ['ref_org', 'podrazdel'], $list)->execute();

            /*Добавляем Рубрики*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgRubrikList']);$j++)
                {
                    if (trim($orgArray['orgRubrikList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgRubrikList'][$j]]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%rubriklist}}', ['ref_org', 'rubrika'], $list)->execute();

            /*Добавляем Почту*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgEmailList']);$j++)
                {
                    if (trim($orgArray['orgEmailList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgEmailList'][$j]]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%emaillist}}', ['ref_org', 'email'], $list)->execute();

            /*Добавляем Url*/
            $list = array();
            for ($j=0;$j<count($orgArray['orgUrlList']);$j++)
                {
                    if (trim($orgArray['orgUrlList'][$j])==""){continue;}
                     array_push($list, [$uid, $orgArray['orgUrlList'][$j]]);                    
                   }
            Yii::$app->db->createCommand()->batchInsert('{{%urllist}}', ['ref_org', 'url'], $list)->execute();          
      
      return 1;
    }
    
    
/***********************************************************************/
/********************* отображение *************************************/    
  public function loadOrgRecord()
  {
      if (empty ($this->orgId)) {return; }
        $record = OrgList::findOne($this->orgId);
      $this->title=$record->title;
      $this->contactEmail = $record->contactEmail;
      $this->contactPhone = $record->contactPhone;
      $this->contactFIO   = $record->contactFIO;
      return $record;      
      
  }
    
   public function getCompanyPhones()
   {
        $ret =  Yii::$app->db->createCommand('SELECT id, phone, status from {{%phones}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryAll();       
        return $ret;
   }   
    
   public function getCompanyAdress()
   {
        $ret =  Yii::$app->db->createCommand('SELECT id, area, city, district,adress from {{%adreslist}} where ref_org=:ref_org'
                                             ,[':ref_org'=>$this->orgId])->queryAll();       
                                             
        $this->adressId   = $ret[0]['id'];
        $this->adressArea = $ret[0]['area'];
        $this->adressCity = $ret[0]['city'];
        $this->adressDistrict = $ret[0]['district'];
        $this->adress      = $ret[0]['adress'];
        
        return $ret;
   }   
        
   public function getOrgContactProvider($params)
   {
     
     $query  = new Query();
     $countquery  = new Query();     

     
     $countquery->select ("count({{%contact}}.id)")
                  ->from("{{%contact}}")
                 ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")
                 ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone");
    
     $query->select("{{%contact}}.id, contactFIO, contactDate, note, userFIO ")
            ->from("{{%contact}} ") 
            ->leftJoin("{{%user}}", "{{%user}}.id = {{%contact}}.ref_user")
            ->leftJoin("{{%phones}}", "{{%phones}}.id = {{%contact}}.ref_phone");


    $countquery->where("{{%contact}}.ref_org=:refOrg");
         $query->where("{{%contact}}.ref_org=:refOrg");            
        

       if (($this->load($params) && $this->validate())) 
    {
     
        $query->andFilterWhere(['like', 'userFIO', $this->userFIO]);        
        $query->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);        
        $query->andFilterWhere(['=', 'contactDate', $this->contactDate]);
        
        $countquery->andFilterWhere(['like', 'userFIO', $this->userFIO]);        
        $countquery->andFilterWhere(['like', 'contactFIO', $this->contactFIO]);        
        $countquery->andFilterWhere(['=', 'contactDate', $this->contactDate]);
     
    }
    
        $query->addParams([':refOrg' => $this->orgId]);
        $countquery->addParams([':refOrg' => $this->orgId]);

       $command = $query->createCommand();    
       $count = $countquery->createCommand()->queryScalar();
        
        $provider = new SqlDataProvider(['sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 5,
            ],
            'sort' => [
            'attributes' => [
            'contactFIO',            
            'contactDate',
            'note',
            'userFIO'
            ],
            'defaultOrder' => [    'contactDate' => SORT_DESC ],
            ],
        ]);
    return $provider;
   }   

    
}
