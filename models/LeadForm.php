<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper; 


use app\models\TblLeadDetail;
use app\models\ContactList;

use app\models\TblDocContactLnk;

/**
 * LeadForm  - модель для работы с лида
 */
class LeadDetailForm extends Model
{
    
    public $leadId=0;
    public $refContact=0;
    public $leadWareName="";
    public $leadWareDetail="";
    public $leadWareSize=""; //РАЗМЕРЫ
    
    public $isLeaf=0; //листы
    public $isRolls=0; //ролики
    public $isBobine=0; //бобины
    
    public $leadWareCount=''; //к-во
    public $leadWareEd=''; //
    public $leadWareSum=0; //
    public $leadWareDate=""; //
    public $leadWareTime = 0;
    
    public $leadFrequency=""; //
    public $leadTargetPlace=""; //
    public $leadTargetCity=""; //
    
    public $leadUse=""; //
    public $leadPrevAnalog=""; //
    public $leadCompanyGoal=""; //
    
    
    /*Ajax save*/
    public $recordId;
    public $dataType;
    public $dataVal;
    
    
    public $debug=[];
    
    public function rules()
    {
        return [
            
            [[ 'leadId','refContact','leadWareName','leadWareDetail','leadWareSize',    
        'isLeaf','isRolls','isBobine',    'leadWareCount','leadWareEd','leadWareSum','leadWareDate',
        'leadFrequency','leadTargetPlace','leadTargetCity',    'leadUse','leadPrevAnalog','leadCompanyGoal',
            ], 'default'],         

        ];
    }

    /*********************************
        Загрузим данные по лиду
    
    
    
    
    **********************************/
   public function loadForm()
   {
     if (!empty($this->leadId))   
       $record= TblLeadDetail::findOne($this->leadId);
     else  
      $record= TblLeadDetail::findOne([
      'refContact' => $this->refContact,
      ]);
      
       if (empty($record)) return;

      $this->leadId = $record->id;
      $this->refContact = $record->refContact;
      $this->leadWareName = $record->leadWareName;
      $this->leadWareDetail = $record->leadWareDetail;
      $this->leadWareSize = $record->leadWareSize;
    
      $this->isLeaf = $record->isLeaf;
      $this->isRolls = $record->isRolls;
      $this->isBobine = $record->isBobine;
    
      $this->leadWareCount = $record->leadWareCount;
      $this->leadWareEd = $record->leadWareEd;
      $this->leadWareSum = $record->leadWareSum;
      $this->leadWareTime = strtotime($record->leadWareDate);
      if ($this->leadWareTime > 100)
        $this->leadWareDate =date('d.m.Y',$this->leadWareTime);
      else  
        $this->leadWareDate = "";
    
      $this->leadFrequency = $record->leadFrequency;
      $this->leadTargetPlace = $record->leadTargetPlace;
      $this->leadTargetCity = $record->leadTargetCity;
    
      $this->leadUse = $record->leadUse;
      $this->leadPrevAnalog = $record->leadPrevAnalog;
      $this->leadCompanyGoal = $record->leadCompanyGoal;

   }    

   public function saveForm()
   {    
       $record= TblLeadDetail::findOne($this->leadId);
       if (empty($record)) $record = new TblLeadDetail();
       if (empty($record)) return;
        
      $record->refContact = $this->refContact;
      $record->leadWareName = $this->leadWareName;
      $record->leadWareDetail = $this->leadWareDetail;
      $record->leadWareSize = $this->leadWareSize;
    
      $record->isLeaf = $this->isLeaf;
      $record->isRolls = $this->isRolls;
      $record->isBobine = $this->isBobine;
    
      $record->leadWareCount = $this->leadWareCount;
      $record->leadWareEd = $this->leadWareEd;
      $record->leadWareSum = $this->leadWareSum;
      $this->leadWareTime = strtotime($this->leadWareDate);
      if ($this->leadWareTime > 100)
        $record->leadWareDate =date('Y-m-d',$this->leadWareTime);
      
      $record->leadFrequency = $this->leadFrequency;
      $record->leadTargetPlace = $this->leadTargetPlace;
      $record->leadTargetCity = $this->leadTargetCity;
    
      $record->leadUse = $this->leadUse;
      $record->leadPrevAnalog = $this->leadPrevAnalog;
      $record->leadCompanyGoal = $this->leadCompanyGoal;
        
     $record->save();
     
     /*проверяем на полноту заполнения товара*/
     if(empty($record->leadWareName)) return;
     if(empty($record->leadWareDetail)) return;
     if(empty($record->leadWareSize)) return;
     if(empty($record->leadWareCount)) return;
     if(empty($record->leadTargetCity)) return;
     
     $contactRecord = ContactList::findOne($record->refContact);
     if (empty($contactRecord)) return;
     $contactRecord->eventType = 15;
     $contactRecord->save();
   }    
   

  public function getDetailText()
   {
      $this->loadForm();
        
    $TXT = "    
    <table  class='table table-bordered table-striped'>
    <tr>  <td width='250px'>Что (Наименование)<br>
    вид бумаги</td>
    <td>".$this->leadWareName;
    if (!empty($this->leadWareName)) $TXT .=" <span id='leadWareNameSign' style='float:right;color:Green;font-size:14px' class='glyphicon glyphicon-star'></span>";
                                else $TXT .=" <span id='leadWareNameSign' style='float:right;color:Crimson;font-size:14px' class='glyphicon glyphicon-star-empty'></span>";
    $TXT .="</td></tr>  
    <tr>
    <td>Характеристики <span style='font-size:11px;'>(плотность, прочность, цвет
    мягкость, влаго- и жиропроницаемость) </span</td>
    <td>".$this->leadWareDetail;
    
    if (!empty($this->leadWareDetail)) $TXT .=" <span id='leadWareNameSign' style='float:right;color:Green;font-size:14px' class='glyphicon glyphicon-star'></span>";
                                else $TXT .=" <span id='leadWareNameSign' style='float:right;color:Crimson;font-size:14px' class='glyphicon glyphicon-star-empty'></span>";
    $TXT .="</td></tr>  
    <tr>
    <td><table border='0'><tr><td>РАЗМЕРЫ:</td><td> <ul>";
    if ($this->isLeaf == 1) $TXT .= '<li>Листы</li>';
    if ($this->isRolls == 1) $TXT .= '<li>Ролики</li>';
    if ($this->isBobine == 1) $TXT .= '<li>Бобины</li>';
    $TXT .= "</ul></td></tr></table></td>
    <td>".$this->leadWareSize;
    if (!empty($this->leadWareSize)) $TXT .=" <span id='leadWareNameSign' style='float:right;color:Green;font-size:14px' class='glyphicon glyphicon-star'></span>";
                                else $TXT .=" <span id='leadWareNameSign' style='float:right;color:Crimson;font-size:14px' class='glyphicon glyphicon-star-empty'></span>";
    $TXT .="</td></tr>  
    <tr> <td>Сколько </td>
    <td><div class='row'>
    <div class='col-sm-4'>".$this->leadWareCount;

    $TXT .="</div> 
    <div class='col-sm-4'>".$this->leadWareEd."</div>    
   
    ";
    
        
     	
    $TXT .="</div>";

     if (!empty($this->leadWareCount)) $TXT .=" <span id='leadWareNameSign' style='float:right;color:Green;font-size:14px' class='glyphicon glyphicon-star'></span>";
                             else $TXT .=" <span id='leadWareNameSign' style='float:right;color:Crimson;font-size:14px' class='glyphicon glyphicon-star-empty'></span>";

    $TXT .="</td></tr>  
    <tr> <td>На сумму</td>
    <td>".$this->leadWareSum."</td></tr>  
    <tr><td>Когда</td>
    <td>".$this->leadWareDate."</td></tr> 
    <tr><td>Как часто делается закупка
    </td><td>".$this->leadFrequency."</td></tr>  
    <tr>  <td>Место отгрузки <span style='font-size:11px;'>
    (самовывоз, доставка, ТК, до терминала, до двери)</span></td>
    <td>".$this->leadTargetPlace."</td></tr>     
    <tr><td>КУДА город</td>
    <td>".$this->leadTargetCity;
    if (!empty($this->leadTargetCity)) $TXT .=" <span id='leadWareNameSign' style='float:right;color:Green;font-size:14px' class='glyphicon glyphicon-star'></span>";
                                else $TXT .=" <span id='leadWareNameSign' style='float:right;color:Crimson;font-size:14px' class='glyphicon glyphicon-star-empty'></span>";
    $TXT .="</td></tr>  
    <tr><td>Для чего используется бумага</td>
    <td>".$this->leadUse."</td></tr>
    <tr><td>Что использовали раньше</td>
    <td>".$this->leadPrevAnalog."</td></tr>
    <tr><td>Чем занимается компания</td>
    <td>".$this->leadCompanyGoal."</td></tr>
    </table>";

    return  $TXT;   
   }    

   
   
   public function saveAjaxData()
   {
       $res = [ 'res' => false, 
             'dataVal'  => $this->dataVal, 
             'recordId' => $this->recordId, 
             'dataType' => $this->dataType, 
             'val' => '',
           ];   
    switch ($this->dataType)
    {
        case 'moduleText':
           break;
    }

    $res['res'] = true;    
    return $res;
   }    


   
   /**end of class**/
 }
