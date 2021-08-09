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
use app\models\TblWareNames;
use app\models\AdressList;

use app\models\TblOrgAccounts;
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
    public $errMsg;
    
    /**** Mail *****/
    //CD4zcQYH
    /*
    Имя пользователя
        aurora@erp-system.ru
    Пароль
        CD4zcQYH
    Сервер POP3/IMAP
        mail.erp-system.ru
    Сервер SMTP
        smtp.erp-system.ru
    */
    public $from ="aurora@erp-system.ru";
    public $subject="Order";
    public $body="Order";
    public $html="";
    public $attachDoc="";
    public $orderSum;
    
    
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
    if (empty($list[$i]['orgPhone']))
        $phone  = $this->getPhone($list[$i]['orgRef']);
    else
        $phone  = $list[$i]['orgPhone'];

    $res['orgData'][] =[
                'orgTitle' =>$list[$i]['orgTitle'],
                'orgInn' =>$list[$i]['orgINN'],
                'orgKpp' =>$list[$i]['orgKPP'],
                'orgAdress' =>$adress,
                'orgPhone' =>$phone,
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


        $phone  = $this->getPhone($list[0]['orgRef']);

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
                'orgPhone' =>$phone,
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
 * @return String адрес
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
 * Поиск телефона организации по id организации
 * @param  $orgId - Integer идентифактор организации в базе
 * @return String телефон
 * @throws Exception none
 */

   public function getPhone($orgId)
   {
     $phone = "";
     $phoneRecord= PhoneList::findOne(
      [
      'isDefault' => 1,
      'ref_org'    => $orgId
      ]);
      if (empty($phoneRecord))
      $phoneRecord= PhoneList::find()
        ->where (['ref_org'    => $orgId])
        ->andWhere (["!=","ifnull(phone,'')",""])
        ->andWhere (["!=","status",2])
        ->one();
      if (!empty($phoneRecord))
      {
        $phone = $phoneRecord->phone;
      }
    return $phone;

  }

/****************************************************************************************/
 /**
 * Поиск Email организации по id организации
 * @param  $orgId - Integer идентифактор организации в базе
 * @return String телефон
 * @throws Exception none
 */

   public function getEmail($orgId)
   {
     $email = "";
     $emailRecord= EmailList::findOne(
      [
      'isDefault' => 1,
      'ref_org'    => $orgId
      ]);
      if (empty($emailRecord))
      $emailRecord= EmailList::find()
        ->where (['ref_org'    => $orgId])
        ->andWhere (["!=","ifnull(email,'')",""])
        ->andWhere (["!=","ifnull(status,'')",2])
        ->one();
      if (!empty($emailRecord))
      {
        $email = $emailRecord->email;
      }
    return $email;
  }
 /****************************************************************************************/
 /**
 * Настройка css для коммерческого
 * @param
 * @return String код css
 * @throws Exception none
 */

   public function  getOrderCss ()
   {
    $fpath=realpath(dirname(__FILE__))."/".'orderTemplate.css';
    $html = file_get_contents ($fpath);
    return $html;
   }


/****************************************************************************************/
 /**
 * Получить значение из конфига
 * @param  Integer key - номер ключа
 * @return String значение
 * @throws Exception none
 */

   public function getCfgValue($key)
   {
     $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key',
            [
            ':key' => intval($key),
            ])->queryOne();

    return $record['keyValue'];
   }

/****************************************************************************************/
 /**
 * Настройка css для коммерческого
 * @param
 * @return String код html
 * @throws Exception none
 */
   public function prepareOrderDoc()
   {

     $page = "";
     $zakazId = intval($this->id);



     $ownerId = $this->getCfgValue(1100);
     $ownerRecord = OrgList::findOne($ownerId);
     if (empty($ownerRecord)) return "Собственник не найден";

     $ownerAcc=   TblOrgAccounts::findOne([
     'refOrg' => $ownerId,
     'isDefault' => 1
     ]);
     if (empty($ownerAcc)) return "Реквезиты Собственника не найдены";

     $zakazRecord  = ZakazList::findOne($zakazId);
     if (empty($zakazRecord)) return "Заказ не найден";
     $zakazRecord -> isByClient = 0;
     $zakazRecord -> save();


     $clientRecord = OrgList::findOne($zakazRecord->refOrg);
     if (empty($clientRecord)) return "Клиент не найден";

     $page = "<table border='0' style='width:100%;'><tr>
     <td width='190px' valign='top'>";
     //$page .="<img src='img/logo.png' width=188 height=48>";
     $page .="</td>
     <td valign='top'>";
     $page .= "<b>".$ownerRecord->orgFullTitle."</b><br>";
     $page .= "<b> телефон: ".$this->getPhone($ownerId)."</b><br>";
     $page .= "<b> E-Mail: ".$this->getEmail($ownerId)."</b><br>";
     $page .= "<b> ИНН: ".$ownerRecord->orgINN." КПП ".$ownerRecord->orgKPP."</b><br>&nbsp;<br>";
     $page .= "</td>";
     $page .= "<td valign='top'>";
     $page .= " Банк: ".$ownerAcc->orgBank."<br>";
     $page .= " БИК: ".$ownerAcc->orgBIK."<br>";
     $page .= " Р/С: ".$ownerAcc->orgRS."<br>";
     $page .= " К/C: ".$ownerAcc->orgKS."<br>";
     $page .= "</td> \n";

     $page .= "</tr><tr> \n";

     $page .= "<td colspan = 3> &nbsp; </td></tr><tr> \n";

     $page .= "</tr><tr> \n";
     $page .= "<td width='190px'> </td> \n";

     $ownerAcc=   TblOrgAccounts::findOne([
     'refOrg' => $ownerId,
     'isDefault' => 1
     ]);

     $this->orgTitle= $clientRecord->title;

     $page .= "<td valign='top'>";
     if (empty($clientRecord->orgFullTitle)) $orgFullTitle = $clientRecord->title;
                                        else $orgFullTitle = $clientRecord->orgFullTitle;
     $page .= "<b>".$orgFullTitle."</b><br>";


    $phone  = $this->getPhone($clientRecord->id);

     $page .= "<b> телефон: ".$phone."</b><br>";
     if (empty ($zakazRecord->clientEmail) )  $email = $clientRecord->contactEmail;
                                        else  $email = $zakazRecord->clientEmail;

     $page .= "<b> E-Mail: ".$email."</b><br>";
     $page .= "<b> ИНН: ".$clientRecord->orgINN." КПП ".$clientRecord->orgKPP."</b><br>&nbsp;<br>";
     $page .= "</td> \n";
     $page .= "<td valign='top'>";

     $clientAcc=   TblOrgAccounts::findOne([
     'refOrg' => $clientRecord->id,
     'isDefault' => 1
     ]);
     if (empty($clientAcc))
     $clientAcc=   TblOrgAccounts::findOne([
     'refOrg' => $clientRecord->id,
     ]);
     if (!empty($clientAcc)){
     $page .= " Банк: ".$clientAcc->orgBank."<br>";
     $page .= " БИК: ".$clientAcc->orgBIK."<br>";
     $page .= " Р/С: ".$clientAcc->orgRS."<br>";
     $page .= " К/C: ".$clientAcc->orgKS."<br>";
     $page .= "</td> \n";
     }

     $page .= "</tr><tr> \n";




      $page .= "<td colspan=3 style='text-align:center;'>";
      $page .= "<h4> Коммерческое предложение </h4>";
      $page .= "</td> \n";


      $page .= "</tr></table> \n";

     $page .="<div style='padding:20px;'>\n";
     $page .="<table border='1px' style='border-collapse: collapse; width:100%; border-width:1px; padding:5px;'>\n";
     $page .="<tr>
     <td style='padding:5px;'><b> № </b></td>
     <td style='padding:5px;'>Товары (работы, услуги)</td>
     <td style='padding:5px;'>Кол-во </td>
     <td style='padding:5px;'>Ед.</td>
     <td style='padding:5px;'>Цена</td>
     <td style='padding:5px;'>Сумма</td>
     </tr>\n";

    $detailList = Yii::$app->db->createCommand(
            'SELECT {{%zakazContent}}.id, {{%zakazContent}}.isActive, {{%zakaz}}.refOrg as orgId, {{%zakaz}}.id AS zakazId,
            initialZakaz, good, spec, ed, value, count, dopRequest, dostavka
            FROM   {{%zakazContent}}, {{%zakaz}}  where {{%zakazContent}}.refZakaz = {{%zakaz}}.id
            AND {{%zakazContent}}.isActive = 1 AND  refZakaz=:refZakaz' ,
        [':refZakaz' => $zakazRecord->id])->queryAll();

    $sum=0;
    for ($i=0; $i<count($detailList);$i++ )
    {
        //if ($detailList[$i]['isActive'] == 0) {continue;}
        $page .="<tr>\n";
        $page.="<td style=padding:5px;'>".($i+1)."</td>\n";
        $page.="<td style=padding:5px;'>".$detailList[$i]['good']."</td>\n";
        $page.="<td style=padding:5px;'>".$detailList[$i]['count']."</td>\n";
        $page.="<td style=padding:5px;'>".$detailList[$i]['ed']."</td>\n";
        $page.="<td style=padding:5px;text-align:right;'>".number_format($detailList[$i]['value'],2,'.','&nbsp;')."</td>\n";
        $page.="<td style=padding:5px;text-align:right;'>".number_format(($detailList[$i]['count']*$detailList[$i]['value']),2,'.','&nbsp;')."</td>\n";
        $page.="</tr>\n";
        $sum+=$detailList[$i]['count']*$detailList[$i]['value'];
    }
    $p=$i;

    $this->orderSum=$sum;
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'>Итого: ".number_format( $sum,2,'.','&nbsp;')." руб </td>\n";
     $page.="</tr>\n";
     $page.=" </table> \n";
     $page.=" </div> \n";
     return $page;
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
               'isReload'   => false,
               'isSwitch'   => false,
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
        $ret['isReload'] = true;
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
            $wareRecord = TblWareNames::findOne($this->dataId);
            if (empty($wareRecord)) return $ret;

            if(empty($contentRecord)){

                $contentRecord = new ZakazContent();
                if (empty($contentRecord )) return $ret;
                $contentRecord ->refZakaz = $record->id;
                $contentRecord ->wareNameRef = intval($this->dataId);
                $contentRecord ->initialZakaz = $wareRecord->wareTitle;
                $contentRecord ->good = $wareRecord->wareTitle;
                $contentRecord ->value = $wareRecord->v1;
                $contentRecord ->ed = $wareRecord->wareEd;
                if (empty($contentRecord ->count))$contentRecord ->count = 1;
                $contentRecord ->isActive = 1;
            }            
            else {
                if ($contentRecord ->isActive == 1) $contentRecord ->isActive = 0;
                                               else $contentRecord ->isActive = 1;            
                $contentRecord ->value = $wareRecord->v1;

            }
            $contentRecord ->save();
            $ret['val'] = $contentRecord ->isActive;
            $ret['isSwitch'] = true;
        break;

        case 'wareCount' :
            $contentRecord = ZakazContent::findOne([
            'refZakaz' => $record->id,
            'wareNameRef' => intval($this->dataId)
            ]);
            $wareRecord = TblWareNames::findOne($this->dataId);
            if (empty($wareRecord)) return $ret;

            if(empty($contentRecord)){

                $contentRecord = new ZakazContent();
                if (empty($contentRecord )) return $ret;
                $contentRecord ->refZakaz = $record->id;
                $contentRecord ->wareNameRef = intval($this->dataId);
                $contentRecord ->initialZakaz = $wareRecord->wareTitle;
                $contentRecord ->good = $wareRecord->wareTitle;
                $contentRecord ->ed = $wareRecord->wareEd;
                $contentRecord ->value = $wareRecord->v1;
                $contentRecord ->count = (float)str_replace(',', '.',$this->dataVal); 
                $contentRecord ->isActive = 1;
            }            
            else {
                $contentRecord ->count = (float)str_replace(',', '.',$this->dataVal); 
                $contentRecord ->isActive = 1;
                $contentRecord ->value = $wareRecord->v1;
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


    public function sendMail()
    {

    $curUser=Yii::$app->user->identity; 
    /*Crytical*/
    if (empty($this->email  )){  $this->errMsg = "Empty mail adress";    return false;}
    if (empty($this->subject)){  $this->errMsg = "Empty subject";    return false;}
    if (empty($this->body))   {  $this->errMsg = "Empty Body";    return false;}
    
    /*Допустимо*/
    if (empty($this->from  )) { 
        if (empty ($curUser->email)) $this->from = $this->getCfgValue(1000);
                                else $this->from = $curUser->email;
                              }

    /* Внутренняя кодировка приложения у нас utf-8, посколько часть почтовых программ 
    ее не воспринимают адекватно, то переведем в Win-1251  */    

    $subject  = $this->subject;
    $textBody = $this->body;    
    
/*    echo $subject;    echo $textBody;*/
    
    $message = Yii::$app->mailer->compose()
        ->setFrom($this->from)
        ->setTo($this->email)
        ->setSubject($subject)
        ->setTextBody($textBody)
        ->setHtmlBody($this->html);
     
    $uploadPath=(realpath(dirname(__FILE__)))."/../uploads";    
        
    $message->attach($this->attachDoc);
    
    
    $message->send();
    
    $this->registerMail();
    
    return true;
    
    
   } 

    public function registerMail ()
    {
         
       if (!empty($this->orgRef))
        {
          $record = OrgList::findOne($this->orgId);
          if(empty($record)) return false;
          $record->contactEmail = $this->email;
          $record->save();
            
          $curUser=Yii::$app->user->identity;
          $contact = new ContactList();
          $contact->ref_org = $this->orgId;
          $contact->ref_user = $curUser->id;
          $contact->contactDate = date("Y.m.d h:i:s");                    
          $contact->contactFIO = $this->subject;
          $contact->contactEmail= $this->email;
          $contact->note = $this->body;
          $contact->save();
         
          return true; 
        }

         return false;
    }
      
  
  /************End of model*******************/ 
 }
