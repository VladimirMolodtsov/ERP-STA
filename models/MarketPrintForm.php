<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\models\OrgList;
use app\models\PhoneList;
use app\models\ContactList;
use app\models\ZakazList;
use app\models\ZakazContent;

use app\models\TblOrgAccounts;
use app\models\TblOrgRekvezit;
use app\models\AdressList;

use app\models\SchetList;
use app\models\TblSchetContent;
use app\models\TblSchetNote;

use app\models\TblWareList;
use app\models\TblWareEd;


/**
 * MarketZakazForm  - модель для работы с заказами
 */
class MarketPrintForm extends Model
{
    
    public $debug;          
    public $zakazId = 0;
    public $schetId = 0;
    public $actId = 0;

    public $orgId = 0;
    public $action = "";
      
    public $stamp =1 ;    
    public $mode  = 0 ; 
    public $showTransport=0;
    
    public function rules()
    {
        return [
            /*[['contactPhone'], 'required'],*/
            [[ ], 'safe'],
            [[ ], 'default'],
        ];
    }


/*************************************************************************************/
/*************************************************************************************/
   
   public function prepareZakazDetail()
   {
        
     $page = "";   
     $zakazId = intval($this->zakazId);

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
     

     
     $clientRecord = OrgList::findOne($zakazRecord->refOrg);
     if (empty($clientRecord)) return "Клиент не найден";

     $page = "<table border='0' style='width:1024px;'><tr>
     <td width='190px' valign='top'>
          <img src='img/logo.png' width=188 height=48>      
     </td>
     <td valign='top'>";
     $page .= "<b>".$ownerRecord->orgFullTitle."</b><br>";
     $page .= "<b> телефон: ".$ownerRecord->contactPhone."</b><br>";
     $page .= "<b> E-Mail: ".$ownerRecord->contactEmail."</b><br>";
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

         
     $page .= "<td valign='top'>";
     if (empty($clientRecord->orgFullTitle)) $orgFullTitle = $clientRecord->title;
                                        else $orgFullTitle = $clientRecord->orgFullTitle;
     $page .= "<b>".$orgFullTitle."</b><br>";
     $page .= "<b> телефон: ".$clientRecord->contactPhone."</b><br>";
     $page .= "<b> E-Mail: ".$clientRecord->contactEmail."</b><br>";
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
      $leadList = Yii::$app->db->createCommand(
            'SELECT note, id from {{%contact}} where 
            eventType = 20 and refZakaz =:zakazId ORDER BY contactDate LIMIT 1', 
            [
            ':zakazId' => intval($this->zakazId),            
            ])->queryAll();
      if(count($leadList)>0)      
          $initLead = preg_replace("/\n/","<br>", $leadList[0]['note']);   
      else    
          $initLead = "";          

        if($this->mode == 0){
          $page .= "<td colspan=3 style='padding:20px'>";                    
          if (!empty($leadList)) {      
          $page .= "<p> Начальный заказ: </p>";  
                  $page .=  $leadList[0]['note'] ;
          }
          $page .= "</td> \n";
          }
      
      
     if($this->mode == 1){
      $page .= "<td colspan=3 style='text-align:center;'>";          
      $page .= "<h4> Коммерческое предложение </h4>";  
      $page .= "</td> \n";
      }
      
      $page .= "</tr></table> \n";   
      
     $page .="<div style='padding:20px;'>\n";     
     $page .="<table border='1px' style='border-collapse: collapse; width:980px; border-width:1px; padding:5px;'>\n";     
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
    $transportList = Yii::$app->db->createCommand(
            'SELECT {{%zakazTransport}}.id,
            typeText, route,note, price, weight, val
            FROM   {{%zakazTransport}}  where
            refZakaz=:refZakaz' ,
        [':refZakaz' => $zakazRecord->id])->queryAll();


    for ($i=0; $i<count($transportList);$i++ )
    {
        $page .="<tr>\n";
        $page.="<td style=padding:5px;'>".($i+$p)."</td>\n";
        $page.="<td style=padding:5px;'>".$transportList[$i]['typeText']." ".$transportList[$i]['route']."</td>\n";
        $page.="<td style=padding:5px;'>".$transportList[$i]['weight']."</td>\n";
        $page.="<td style=padding:5px;'>"."кг"."</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
        if(!empty($transportList[$i]['price'])) $page.=number_format($transportList[$i]['price'],2,'.','&nbsp;');
        $page.="</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
        if(!empty($transportList[$i]['val'])) $page.=number_format($transportList[$i]['val'],2,'.','&nbsp;');
        $page.="</td>\n";

        $page.="</tr>\n";
        $sum+=$transportList[$i]['val'];
    }


     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'>Итого: ".number_format( $sum,2,'.','&nbsp;')." руб </td>\n";     
     $page.="</tr>\n";
     $page.=" </table> \n";      
     $page.=" </div> \n"; 
     return $page;  
   }
/*************************************************************************************/
/*************************************************************************************/
   
   public function preparePrintSpecify()
   {

     $page = "";   
     $schetId = intval($this->schetId);
     $ownerId = $this->getCfgValue(1100);
     $ownerRecord = OrgList::findOne($ownerId);
     $ownerRegDoc = "Устава";     
     $ownerHead = "директора";
     
     if (empty($ownerRecord)) return "Собственник не найден";

     $schetRecord  = SchetList::findOne($schetId);
     if (empty($schetRecord)) return "Счет не найден";
     
     $ownerHeadFIO = $ownerRecord->headFIO;
     $ownerSignFIO = $this->getCfgValue(1203);
         
     $clientRecord = OrgList::findOne($schetRecord->refOrg);
     if (empty($clientRecord)) return "Клиент не найден";
     $clientRegDoc= "Устава";
     $clientHead="директора";
     $clientHeadFIO=$clientRecord->headFIO;
     $clientSignFIO=$clientRecord->headFIO;

        
     $oplataStart="предоплату в размере 50%";   
     $oplataFin="оставшиеся 50% перечисляются Покупателем на расчетный счет Поставщика в течение 30 (тридцати) календарных дней с момента передачи Товара в Транспортную компанию";
        
     $dogNum =  "    "; 
     $dogDate= ""; 
     $spDate =  date('d.m.Y');
     $spNum  ="    ";  
    
    
      $rekvOwner = TblOrgRekvezit::findOne(['refOrg' => $ownerId]);
      if (!empty($rekvOwner))
      {
        if(!empty($rekvOwner->dogFIO)  )$ownerHeadFIO  = $rekvOwner->dogFIO;
        if(!empty($rekvOwner->dogBase) )$ownerRegDoc   = $rekvOwner->dogBase;
        if(!empty($rekvOwner->dogHead) )$ownerHead     = $rekvOwner->dogHead;
        if(!empty($rekvOwner->signFIO) )$ownerSignFIO  = $rekvOwner->signFIO;
      }
    
      $rekvClient = TblOrgRekvezit::findOne(['refOrg' => $schetRecord->refOrg]);
      if (!empty($rekvClient))
      {
        if(!empty($rekvClient->dogFIO)  )$clientHeadFIO  = $rekvClient->dogFIO;
        if(!empty($rekvClient->dogBase) )$clientRegDoc   = $rekvClient->dogBase;
        if(!empty($rekvClient->dogHead) )$clientHead     = $rekvClient->dogHead;
        if(!empty($rekvClient->signFIO) )$clientSignFIO  = $rekvClient->signFIO;
      }
    
    $page .= " <style> 
          @media print {
            html, body{
                height: 297mm;
                width: 210mm;
            }
          }
          
        html,body{
        height:297mm;
        width:210mm;
        }  
     </style>
     ";     
    
      $page .= "<div align='right' style='font-size:10pt'>";         
          $page .= "Приложение № 1 к договору поставки<br>";   
          $page .= "№ ".$dogNum." от ".$dogDate." г.";         
      $page .= "</div>";   
   
      $page .= "<div style='text-align:center; font-size:12pt; font-weight:bold'>";         
          $page .= "СПЕЦИФИКАЦИЯ № ".$spNum."  от ".$spDate." г.<br>";
          $page .= "к договору поставки № ".$dogNum." от ".$dogDate." г.";         
      $page .= "</div>";   
         
      $page .= "<div style='text-align:justify; font-size:12pt;'>"; 
          $page .= "<b>".$ownerRecord->orgFullTitle."</b>, именуемое в дальнейшем «Поставщик», в лице ".$ownerHead." ";        
          $page .= $ownerHeadFIO;
          $page .= ", действующего на основании ".$ownerRegDoc.", с одной стороны, и";          
      $page .= "</div>";   
     
      $page .= "<div style='text-align:justify; font-size:12pt;'>"; 
          $page .= "<b>".$clientRecord->orgFullTitle."</b>, именуемое в дальнейшем «Покупатель», в лице ".$clientHead." ";        
          $page .= $clientHeadFIO;
          $page .= ", действующего на основании ".$clientRegDoc.", с другой стороны, далее именуемые «Стороны», подписали настоящую спецификацию:";          
      $page .= "</div>";   
   
   
    $page .="<table border='1' style='width:1024px; border-collapse:collapse;'>\n";
    $page .="<tr>\n";
        $page .="<td style='padding:5px; width:50px; font-weight:bold;'>п/п</td>\n";        
        $page .="<td style='padding:5px; font-weight:bold;'>Наименование товара</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Срок поставки</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Количество, кг</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Цена товара, руб/кг<br>с НДС 20%</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Сумма товара<br>(в рублях РФ)<br>с НДС </td>\n";
        $page .="<tr>\n";
    $page .="</tr>\n";
    
    $detailList = Yii::$app->db->createCommand(
            'SELECT id, wareTitle, wareCount, wareEd, warePrice            
            FROM   {{%schetContent}} where {{%schetContent}}.refSchet =:refSchet' , 
        [':refSchet' => $schetRecord->id])->queryAll();       
    
    $sum=0;
    $num=0;
    for ($i=0; $i<count($detailList);$i++ )
    {
        //if ($detailList[$i]['isActive'] == 0) {continue;}
        $page .="<tr>\n";
        $page.="<td style='padding:5px;'>".($i+1)."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareTitle']."</td>\n";
        $page.="<td style='padding:5px;'></td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareCount']."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format($detailList[$i]['warePrice'],2,'.','&nbsp;')."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format(($detailList[$i]['wareCount']*$detailList[$i]['warePrice']),2,'.','&nbsp;')."</td>\n";
        $page.="</tr>\n";
        $sum+=$detailList[$i]['wareCount']*$detailList[$i]['warePrice'];
        $num++;
    }
   
     $page.="<tr><td colspan=6 style='text-align:right;padding:5px'><b>Итого: </b>".number_format( $sum,2,'.','&nbsp;')." руб </td></tr>\n";     
     $page.="</table>\n";

     
    $page .= "<div align='left' style='font-size:10pt'>";         
    $page .="<ol>";
          $page .= "<li>Срок поставки товара исчисляется с момента: передачи товара в Транспортную компанию. Стоимость доставки включена в цену товара.\n";   
          $page .= "<li>Порядок оплаты: Покупатель осуществляет ".$oplataStart." стоимости Товара (партии Товара) путем перечисления денежных средств на расчетный счет Поставщика в течение 5 (пяти) банковских дней с даты получения выставленного Поставщиком счета на оплату Товара (партии Товара),".$oplataFin.".\n";   
          $page .= "<li>Настоящая спецификация составлена в двух экземплярах, имеющих равную юридическую силу, по одному для каждой из сторон и является неотъемлемой частью договора.\n";   
    $page .="</ol>";      
    $page .= "</div>";   
 
   
    $page .="<table border='0' style='width:1024px; border-collapse:collapse;'>\n";
    $page .="<tr>\n";      
    $page.="<td style='font-weight:bold;'>ПОДПИСИ СТОРОН:</td>";
    $page .="</tr>\n";

    $page .="<tr>\n";      
    $page.="<td style='font-weight:bold;'>Поставщик:".$ownerRecord->orgFullTitle."</td>";
    $page.="<td style='font-weight:bold;'></td>";
    $page.="<td style='font-weight:bold;' align='right'>Покупатель:".$clientRecord->orgFullTitle."</td>";
    $page .="</tr>\n";

    $page .="<tr>\n";      
    $page.="<td style='font-weight:bold;'>_____________".$ownerSignFIO."</td>";
    $page.="<td style='font-weight:bold;'></td>";
    $page.="<td style='font-weight:bold;' align='right'>________________".$clientSignFIO."</td>";
    $page .="</tr>\n";
   
   return $page;
   }

/*************************************************************************************/
/*************************************************************************************/
   
   public function prepareSchetPrint()
   {
    
     if ($this->stamp == 3) return $this->preparePrintSpecify();
         
     $page = "";   
     $schetId = intval($this->schetId);

//echo $this->showTransport;

     $ownerId = $this->getCfgValue(1100);
     $ownerRecord = OrgList::findOne($ownerId);
     if (empty($ownerRecord)) return "Собственник не найден";

     $ownerAcc=   TblOrgAccounts::findOne([
     'refOrg' => $ownerId,
     'isDefault' => 1
     ]);
     if (empty($ownerAcc)) return "Реквезиты Собственника не найдены";

     $ownerAdresRecord = AdressList::findOne([
     'ref_org' => $ownerId,
     'isOfficial' => 1
     ]);
     if (empty($ownerAdresRecord)) $ownerAdresRecord = AdressList::findOne([
     'ref_org' => $ownerId,
     ]);
   
    
      $ownerPhone ="";
      $strSql  = "SELECT DISTINCT phone from {{%phones}}";
      $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $ownerId,])->queryAll();                                        
      $N = count($resList);
      if ($N >0) $ownerPhone =  $resList[0]['phone']; 

      $ownerEmail ="";
      $strSql  = "SELECT DISTINCT email from {{%emaillist}}";
      $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $ownerId,])->queryAll();                                        
      $N = count($resList);
      if ($N >0)$ownerEmail =  $resList[0]['email']; 
        

    $noteRecord = TblSchetNote::findOne(['refSchet' => $schetId ]);    
   
        
   
   
    $head[0]= $this->getCfgValue(1200);
    $head[1]= $this->getCfgValue(1201);
    $head[2]= $this->getCfgValue(1202);

    $sign[0]= $this->getCfgValue(1203);
    $sign[1]= $this->getCfgValue(1204);
    $sign[2]= $this->getCfgValue(1205);


     $schetRecord  = SchetList::findOne($schetId);
     if (empty($schetRecord)) return "Счет не найден";
     
     $clientAcc=   TblOrgAccounts::findOne([
     'refOrg' => $schetRecord->refOrg,
     'isDefault' => 1
     ]);
     if (empty($clientAcc)) $clientAcc= TblOrgAccounts::findOne([
     'refOrg' => $schetRecord->refOrg,
     ]);    
     //if (empty($clientAcc)) return "Реквезиты Клиента не найдены";

     
     $clientRecord = OrgList::findOne($schetRecord->refOrg);
     if (empty($clientRecord)) return "Клиент не найден";

     $clientAdresRecord = AdressList::findOne([
     'ref_org' => $schetRecord->refOrg,
     'isOfficial' => 1
     ]);
     if (empty($clientAdresRecord)) $clientAdresRecord = AdressList::findOne([
     'ref_org' => $schetRecord->refOrg,
     ]);
     
     

     $page .= "<table border='0' style='width:1024px;'>
     <tr>
     <td colspan='3' align='right'>
          <img src='img/logo.png' width=188 height=48>      
     </td></tr>";
     
     if ($this->stamp != 2 ){
     for ($i=0;$i<count($head); $i++){
     $page .= "<tr><td colspan='3' align='center'><b>";     
     $page .= $head[$i];
     $page .= "</b></td></tr>\n";
     }}
     $page .= "</table>\n";    
     if (empty($clientRecord->orgFullTitle)) $orgFullTitle = $clientRecord->title;
                                        else $orgFullTitle = $clientRecord->orgFullTitle;

     $page .= "<br><table border='1' style='width:1024px;border-collapse:collapse;'>\n";
         $page .= "<tr>";
         $page .= "<td  colspan=2 rowspan=2 width='500px;height:50px;'>";     
         $page .= "<div style='height:30px;'>".$ownerAcc ->orgBank."</div>";        
         $page .= "<div style='font-size=11px;'>Банк получателя</div>";
         $page .= "</td>";     
         
         $page .= "<td width='75px'>БИК</td>";     
         $page .= "<td>"; 
         $page .= $ownerAcc ->orgBIK;        
         $page .= "</tr>";
         
         $page .= "<tr>";        
         $page .= "<td>Сч. №</td>";     
         $page .= "<td>"; 
         $page .= $ownerAcc ->orgKS;        
         $page .= "</tr>";

         $page .= "<tr>";
         $page .= "<td >"; 
         $page .= "ИНН ".$ownerRecord->orgINN ;
         $page .= "</td>";     
         
         $page .= "<td >";     
         $page .= "КПП ".$ownerRecord->orgKPP ;
         $page .= "</td>";     
         
         $page .= "<td rowspan='2'>Сч. №</td>";     
         $page .= "<td rowspan='2'>"; 
         $page .= $ownerAcc ->orgRS;        
         $page .= "</tr>";
         
         $page .= "<tr>";
         $page .= "<td  colspan=2>";     
         $page .= "<div style='height:30px;'>".$ownerRecord->orgFullTitle."</div>";        
         $page .= "<div style='font-size=11px;'>Получатель</div>";
         $page .= "</td>";     
         $page .= "</tr>";
         
     $page .= "</table>\n";
     $page .="<div style='height:20px;'></div>";
   
     $page .="<div style='font-size:17px; font-weight:bold;'>";
     if ($this->stamp == 2)
     $page .="Коммерческое предложение"; 
     else {
     $page .="Счет на оплату №".$schetRecord->schetNum; 
     $page .=" от ".date("d.m.Y", strtotime($schetRecord->schetDate)); 
     }
     $page .="<div>";
     $page .="<hr align='left' width='1024px'>";

     $page .= "<table border='0' style='width:1024px;'>\n";
     $page .= "<tr>";
         $page .= "<td width='200px'>"; 
         $page .= "Поставщик:";        
         $page .= "</td>";

         $page .= "<td style='width:824px;'>"; 
         $page .= $ownerRecord->orgFullTitle.", ИНН ".$ownerRecord->orgINN;        
         $page .= ", КПП ".$ownerRecord->orgKPP;        
         if(!empty($ownerAdresRecord)) $page .= ", ".$ownerAdresRecord->adress;        
         if(!empty($ownerPhone)) $page .= ", телефон:".$ownerPhone;        
         if(!empty($ownerEmail)) $page .= ", E-mail:".$ownerEmail;        
         $page .= "</td>";     
     $page .= "</tr>";

     $page .= "<tr>";
         $page .= "<td>"; 
         $page .= "Грузоотправитель:";        
         $page .= "</td>";

         $page .= "<td >"; 
         $page .= "</td>";     
     $page .= "</tr>";

     
     $page .= "<tr>";
         $page .= "<td>"; 
         $page .= "Покупатель:";        
         $page .= "</td>";

         $page .= "<td width='200px'>"; 
         $page .= $orgFullTitle.", ИНН ".$clientRecord->orgINN;        
         $page .= ", КПП ".$clientRecord->orgKPP;        
         
         if(!empty($clientAdresRecord)) $page .= ", ".$clientAdresRecord->adress;        
         $page .= "</td>";     
     $page .= "</tr>";     
     $page .= "</table>";
     
  
     $page .= "<div>Грузополучатель:</div>";
     
    $page .="<table border='1' style='width:1024px; border-collapse:collapse;'>\n";
    $page .="<tr>\n";
        $page .="<td style='padding:5px; width:50px; font-weight:bold;'>№</td>\n";
        $page .="<td style='padding:5px; width:100px; font-weight:bold;'>Артикул</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Товары (работы, услуги)</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Кол-во</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Ед.</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Цена</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Сумма</td>\n";
        $page .="<tr>\n";
    $page .="</tr>\n";
    
    $detailList = Yii::$app->db->createCommand(
            'SELECT id, wareTitle, wareCount, wareEd, warePrice            
            FROM   {{%schetContent}} where {{%schetContent}}.refSchet =:refSchet' , 
        [':refSchet' => $schetRecord->id])->queryAll();       
    
    $sum=0;
    $num=0;
    for ($i=0; $i<count($detailList);$i++ )
    {
        //if ($detailList[$i]['isActive'] == 0) {continue;}
        $page .="<tr>\n";
        $page.="<td style='padding:5px;'>".($i+1)."</td>\n";
        $page.="<td style='padding:5px;'></td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareTitle']."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareCount']."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareEd']."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format($detailList[$i]['warePrice'],2,'.','&nbsp;')."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format(($detailList[$i]['wareCount']*$detailList[$i]['warePrice']),2,'.','&nbsp;')."</td>\n";
        $page.="</tr>\n";
        $sum+=$detailList[$i]['wareCount']*$detailList[$i]['warePrice'];
        $num++;
    }
    
    
    if ($this->showTransport == 2)    
    {
      $transportList = Yii::$app->db->createCommand(
            'SELECT {{%schetTransport}}.id,
            typeText, route,note, price, weight, val
            FROM   {{%schetTransport}}  where
            refSchet=:refSchet' ,
        [':refSchet' => $schetRecord->id])->queryAll();


     for ($i=0; $i<count($transportList);$i++ )
     {
        $page .="<tr>\n";
        $page.="<td style=padding:5px;'>".($i+$num)."</td>\n";
        $page.="<td style='padding:5px;'></td>\n";        
        $page.="<td style=padding:5px;'>".$transportList[$i]['typeText']." ".$transportList[$i]['route']."</td>\n";
        $page.="<td style=padding:5px;'>".$transportList[$i]['weight']."</td>\n";
        $page.="<td style=padding:5px;'>"."кг"."</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
        if(!empty($transportList[$i]['price'])) $page.=number_format($transportList[$i]['price'],2,'.','&nbsp;');
        $page.="</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
        if(!empty($transportList[$i]['val'])) $page.=number_format($transportList[$i]['val'],2,'.','&nbsp;');
        $page.="</td>\n";

        $page.="</tr>\n";
        $sum+=$transportList[$i]['val'];
     }
   }
    

  if ($this->showTransport == 1)    
    {
      $transportList = Yii::$app->db->createCommand(
            'SELECT Sum(weight) as W, Sum(val) as V
            FROM   {{%schetTransport}}  where
            refSchet=:refSchet' ,
        [':refSchet' => $schetRecord->id])->queryAll();
       if (!empty($transportList)){
        $page .="<tr>\n";
        $page.="<td style=padding:5px;'>".($num)."</td>\n";
        $page.="<td style='padding:5px;'></td>\n";        
        $page.="<td style=padding:5px;'>Доставка</td>\n";
        $page.="<td style=padding:5px;'>".$transportList[0]['W']."</td>\n";
        $page.="<td style=padding:5px;'>"."кг"."</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
        $page.="</td>\n";

        $page.="<td style=padding:5px;text-align:right;'>";
            if(!empty($transportList[0]['V'])) $page.=number_format($transportList[0]['V'],2,'.','&nbsp;');
        $page.="</td>\n";

        $page.="</tr>\n";
        $sum+=$transportList[0]['V'];}
   }  
    
     $page.=" </table> \n";      

    
    $page .="<table border='0' style='width:1024px;'>\n";    
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'><b>Итого: </b>".number_format( $sum,2,'.','&nbsp;')." руб </td>\n";     
     $page.="</tr>\n";
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'><b>В том числе НДС: </b>".number_format( ($sum/1.2)*0.2,2,'.','&nbsp;')." руб </td>\n";     
     $page.="</tr>\n";
     
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:left;padding:5px'>";
     
     $page.="<b>Всего к выплате:</b><br>";
     $page.="Всего наименований ".$num.",";
     $page.="на сумму ".number_format( $sum,2,'.','&nbsp;')." руб";
     $page.="</td>\n";     ;
     
     $page.="</tr>\n";
     
     $page.=" </table> \n";  
     $page .="<hr align='left' width='1024px'>";
     
     if(!empty($noteRecord)){
     $page .="<pre style='font-weigh:bold;font-size:110%;font-family:\"Times New Roman\", Times, serif;'>";
     $page .=$noteRecord->schetNote; 
     $page .="</pre>";
     $page .="<hr align='left' width='1024px'>";
     }
     
     $page .="<table border='0' style='width:1024px;'>\n";   
     $page .="<tr>\n";
     $page.="<td width='250px;'>";     
        $page.="<div style='height:40px;'>Руководитель</div>";
     $page.="</td>\n";    
     $page.="<td >";    
     $page.="</td>\n";        

     $page.="<td rowspan='4' align=right>";     
     if ($this->stamp==1) $page.=" <img src='img/sign.jpg' width=115 height=171>";          
     $page.="</td>\n";    
     $page.="<td>";     
        $page.="<div style='height:20px;width:250px;text-align:center;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color: Black;'>".$sign[0]."</div>";
        $page.="<div style='height:11px;width:250px;text-align:center;font-size:11px;'>Расшифровка подписи</div>";
     $page.="</td>\n";    
   $page .="</tr>\n";
     
     $page .="<tr>\n";
     $page.="<td><div style='height:10px;'></div></td>\n";    
     $page.="<td >";    
     $page.="</td>\n";    
     
     $page.="<td >";    
     $page.="</td>\n";    

     $page.="</tr>\n";

     $page .="<tr>\n";
     $page.="<td>";     
    $page.="<div style='height:40px;'>Главный (старший) бухгалтер</div>";
     $page.="</td>\n";       
     $page.="<td rowspan='3'>"; 
     if ($this->stamp==1) $page.=" <img src='img/stamp.jpg' width='191' height='179'>";               
     $page.="</td>\n";        
     $page.="<td width='170px;'>";     
       $page.="<div style='height:20px;width:250px;
          text-align:center;           
          border-bottom-width: 2px; 
          border-bottom-style: solid; 
          border-bottom-color: Black;        
        '>".$sign[1]."</div>";
        $page.="<div style='height:11px;width:250px;
          text-align:center;           
          font-size:11px;
        '>Расшифровка подписи</div>";
     $page.="</td>\n";    
     $page.="</tr>\n";
     
     $page .="<tr>\n";
     $page.="<td><div style='height:10px;'></div></td>\n"; 
     $page.="</tr>\n";

     $page .="<tr>\n";
     $page.="<td width='200px;'>";     
        $page.="<div style='height:40px;'>Ответственный</div>";
     $page.="</td>\n";    
     $page.="<td>";          
     $page.="</td>\n";    
     $page.="<td width='170px;'>";     
        $page.="<div style='height:20px;width:250px;
          text-align:center;           
          border-bottom-width: 2px; 
          border-bottom-style: solid; 
          border-bottom-color: Black;        
        '>".$sign[2]."</div>";
        $page.="<div style='height:11px;width:250px;
          text-align:center;           
          font-size:11px;
        '>Расшифровка подписи</div>";
     $page.="</td>\n";    
     $page.="</tr>\n";
     
     $page.=" </table> \n";  
     return $page;  
   }
/************************************************************************************/

   public function prepareActPrint()
   {
             
     $page = "";   
     $actId = intval($this->actId);

//echo $this->showTransport;

     $ownerId = $this->getCfgValue(1100);
     $ownerRecord = OrgList::findOne($ownerId);
     if (empty($ownerRecord)) return "Собственник не найден";

     $ownerAcc=   TblOrgAccounts::findOne([
     'refOrg' => $ownerId,
     'isDefault' => 1
     ]);
     if (empty($ownerAcc)) return "Реквезиты Собственника не найдены";

     $ownerAdresRecord = AdressList::findOne([
     'ref_org' => $ownerId,
     'isOfficial' => 1
     ]);
     if (empty($ownerAdresRecord)) $ownerAdresRecord = AdressList::findOne([
     'ref_org' => $ownerId,
     ]);
   
    
      $ownerPhone ="";
      $strSql  = "SELECT DISTINCT phone from {{%phones}}";
      $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $ownerId,])->queryAll();                                        
      $N = count($resList);
      if ($N >0) $ownerPhone =  $resList[0]['phone']; 

      $ownerEmail ="";
      $strSql  = "SELECT DISTINCT email from {{%emaillist}}";
      $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";                                 
      $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $ownerId,])->queryAll();                                        
      $N = count($resList);
      if ($N >0)$ownerEmail =  $resList[0]['email']; 
        
   
    $head[0]= $this->getCfgValue(1200);
    $head[1]= $this->getCfgValue(1201);
    $head[2]= $this->getCfgValue(1202);

    $sign[0]= $this->getCfgValue(1203);
    $sign[1]= $this->getCfgValue(1204);
    $sign[2]= $this->getCfgValue(1205);


     $actRecord  = TblSchetAct::findOne($actId);
     if (empty($actRecord)) return "Акт не найден ".$actId;

     $schetRecord= SchetList::findOne($actRecord->refSchet);
     if(empty($schetRecord)) return;

     
     $clientAcc=   TblOrgAccounts::findOne([
     'refOrg' => $schetRecord->refOrg,
     'isDefault' => 1
     ]);
     if (empty($clientAcc)) $clientAcc= TblOrgAccounts::findOne([
     'refOrg' => $schetRecord->refOrg,
     ]);    
     //if (empty($clientAcc)) return "Реквезиты Клиента не найдены";

     
     $clientRecord = OrgList::findOne($schetRecord->refOrg);
     if (empty($clientRecord)) return "Клиент не найден";

     $clientAdresRecord = AdressList::findOne([
     'ref_org' => $schetRecord->refOrg,
     'isOfficial' => 1
     ]);
     if (empty($clientAdresRecord)) $clientAdresRecord = AdressList::findOne([
     'ref_org' => $schetRecord->refOrg,
     ]);
     
     if (empty($clientRecord->orgFullTitle)) $orgFullTitle = $clientRecord->title;
                                        else $orgFullTitle = $clientRecord->orgFullTitle;

     $page .="<div style='font-size:15pt; font-weight:bold'>";
     $page .="Акт № ".$actRecord->actNum." от ". date("d.m.Y", strtotime($actRecord->actDate) )  ."г.";
     $page .="</div>";
    
     $page .= "<table border='0' style='width:1024px;'>\n";
     $page .= "<tr>";
         $page .= "<td width='200px'>"; 
         $page .= "Исполнитель:";        
         $page .= "</td>";

         $page .= "<td width='800px'>"; 
         $page .= $ownerRecord->orgFullTitle.", ИНН ".$ownerRecord->orgINN;        
         $page .= ", КПП ".$ownerRecord->orgKPP;    
         
         if(!empty($ownerAdresRecord)) $page .= ", ".$ownerAdresRecord->adress;        
         if(!empty($ownerPhone)) $page .= ", телефон:".$ownerPhone;        
         if(!empty($ownerEmail)) $page .= ", E-mail:".$ownerEmail;        
         
         if(!empty($ownerAcc))
         {
             $page .= ", р/с ".$ownerAcc ->orgRS;            
             $page .= ", в банке ".$ownerAcc ->orgBank;        
             $page .= ", БИК ".$ownerAcc ->orgBIK;
             $page .= ", к/с ".$ownerAcc ->orgKS;                
         }    
         $page .= "</td>";     
     $page .= "</tr>";

     $page .= "<tr><td><div style='height:5px;'></div></td></tr>";
     
     $page .= "<tr>";
         $page .= "<td>"; 
         $page .= "Заказчик:";        
         $page .= "</td>";

         $page .= "<td width='200px'>"; 
         $page .= $orgFullTitle.", ИНН ".$clientRecord->orgINN;        
         $page .= ", КПП ".$clientRecord->orgKPP;                 
         if(!empty($clientAdresRecord)) $page .= ", ".$clientAdresRecord->adress;   

         if(!empty($clientAcc))
         {
             $page .= ", р/с ".$clientAcc ->orgRS;            
             $page .= ", в банке ".$clientAcc ->orgBank;        
             $page .= ", БИК ".$clientAcc ->orgBIK;
             $page .= ", к/с ".$clientAcc ->orgKS;                
         }    
         $page .= "</td>";     
     $page .= "</tr>";     
     $page .= "<tr><td><div style='height:5px;'></div></td></tr>";
    $page .= "<tr>";
         $page .= "<td>"; 
         $page .= "Основание:";        
         $page .= "</td>";
         $page .= "<td>"; 
         $page .= "<b>Основной договор</b>"; 
         $page .= "</td>";     
     $page .= "</tr>";     
     
     $page .= "</table>";
     
  $page .= "<div style='height:10px;'></div>";
    
     
    $page .="<table border='1' style='width:1024px; border-collapse:collapse;'>\n";
    $page .="<tr>\n";
        $page .="<td style='padding:5px; width:50px; font-weight:bold;'>№</td>\n";        
        $page .="<td style='padding:5px; font-weight:bold;'>Наименование работ, услуг</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Кол-во</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Ед.</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Цена</td>\n";
        $page .="<td style='padding:5px; font-weight:bold;'>Сумма</td>\n";
        $page .="<tr>\n";
    $page .="</tr>\n";
    
    $detailList = Yii::$app->db->createCommand(
            'SELECT id, wareTitle, wareCount, wareEd, warePrice            
            FROM   {{%schet_actContent}} where {{%schet_actContent}}.refAct =:refAct and {{%schet_actContent}}.isActive = 1' , 
        [':refAct' => $actRecord->id])->queryAll();       
    
    $sum=0;
    $num=0;
    for ($i=0; $i<count($detailList);$i++ )
    {
        //if ($detailList[$i]['isActive'] == 0) {continue;}
        $page .="<tr>\n";
        $page.="<td style='padding:5px;'>".($i+1)."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareTitle']."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareCount']."</td>\n";
        $page.="<td style='padding:5px;'>".$detailList[$i]['wareEd']."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format($detailList[$i]['warePrice'],2,'.','&nbsp;')."</td>\n";
        $page.="<td style='padding:5px;text-align:right;'>".number_format(($detailList[$i]['wareCount']*$detailList[$i]['warePrice']),2,'.','&nbsp;')."</td>\n";
        $page.="</tr>\n";
        $sum+=$detailList[$i]['wareCount']*$detailList[$i]['warePrice'];
        $num++;
    }

     $page.=" </table> \n";      

    
    $page .="<table border='0' style='width:1024px;'>\n";    
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'><b>Итого: </b>".number_format( $sum,2,'.','&nbsp;')." руб </td>\n";     
     $page.="</tr>\n";
     $page .="<tr>\n";
     //$page.="<td colspan=6 style='text-align:right;padding:5px'><b>В том числе НДС: </b>".number_format( ($sum/1.2)*0.2,2,'.','&nbsp;')." руб </td>\n";
     $page.="<td colspan=6 style='text-align:right;padding:5px'><b>Без налога (НДС) </b></td>\n";          
     $page.="</tr>\n";
     
     $page .="<tr>\n";
     $page.="<td colspan=6 style='text-align:left;padding:5px'>";
          
     $page.="Всего оказано услуг ".$num.",";
     $page.="Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.";
     $page.="</td>\n";     ;
     
     $page.="</tr>\n";
     $page.=" </table> \n";  
     
     
     $page .="<hr align='left' width='1024px'>";
     $page .="<table border='0' style='width:1024px;'>\n";   
     $page .="<tr>\n";
     $page.="<td width='250px;'>";     
        $page.="<div style='height:40px;'><b>ИСПОЛНИТЕЛЬ</b></div>";
        
        $page.="<div style='height:40px;'></div>";
     $page .="<hr align='left' width='250px'>";        
         $page.= $sign[0];
     $page.="</td>\n";    
     
     $page.="<td>\n";    
     $page.="</td>\n";    
     
     $page.="<td width='250px;'>";     
        $page.="<div style='height:40px;'><b>ЗАКАЗЧИК</b></div>";

        $page.="<div style='height:40px;'></div>";
     $page .="<hr align='left' width='250px'>";           
     $page.="</td>\n";    
     
     $page.="</tr>\n";
     $page.=" </table> \n";  
     return $page;  
   }

   
/*************************************************************************************/
/*************************************************************************************/
   
   public function getCfgValue($key)        
   {
     $record = Yii::$app->db->createCommand(
            'SELECT keyValue from {{%config}} WHERE id =:key', 
            [
            ':key' => intval($key),            
            ])->queryOne();  
            
    return $record['keyValue'];
   }
   
 /***/  
}
