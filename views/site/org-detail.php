<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Collapse;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Карточка контрагента';
//$this->params['breadcrumbs'][] = $this->title;
$record=$model->loadOrgRecord();
$phoneList=$model->getCompanyPhones();
$adressList=$model->getCompanyAdress();
$adress=$city="&nbsp;";
if (count ($adressList) > 0){
    $city =Html::encode($adressList[0]["city"]);
    $adress=Html::encode($adressList[0]["adress"]);
}

/*
   <a  class="btn btn-primary"  href='index.php?r=site/reg-contact&id=<?=  Html::encode($record->id)  ?>'>Регистрация контакта</a>
   <a  class="btn btn-primary"  href='index.php?r=market/market-zakaz-create&id=<?=  Html::encode($record->id)  ?>'>Регистрация заказа</a>   
   
   
*/


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/site/org-card.js');

$this->registerCssFile('@web/phone.css');

?>

<?php $s="font-weight:bold;"; if ($record->isOrgActive == 0) $s="font-weight:bold;text-decoration: line-through;" ?>
 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


<style> 

.child {
  padding:5px;
  text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}

td{
padding:4px;    
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

</style>
<script>

function showDubKPP()
{
   $('#showDubKPPDialog').modal('show');       
}

function showDubINN()
{
   $('#showDubINNDialog').modal('show');       
}

function showDubTitle()
{
   $('#showDubTitleDialog').modal('show');       
}

function setManager ()
{
   $('#setManagerDialog').modal('show');       
}


function showResetContractDialog ()
{
   $('#resetContractDialog').modal('show');       
}


function linkContract(contractId)
{
   openSwitchWin('site/org-link-contract&orgId=<?= $record->id ?>&contractId='+contractId);       
}

function addNewDostavka()
{    
  saveField(<?=$record->id ?>, 'dostavkaAdd');
}

function syncContract()
{
    openWin('/data/sync-google-contract','syncWin');           
}

function printContract(id,format)
{

  openWin('head/print-contract&id='+id+'&format='+format,'contractWin');
}


</script>

 
 <div class='row'>
    <div class='col-md-7'>
    <font size="+1"> <?= Html::encode($this->title) ?> <span style='<?=$s?>' ><?= Html::encode($record->title) ?></span></font>    
    </div>     
    <div class='col-md-5' style='text-align:right; padding-right:20px;'>
    <?php 
    if($record->isReject == 0)  {$t= "Выключить"; $c="Brown"; }
                         else   {echo "<b><font color='Crimson'>Организация выключена из работы&nbsp;</font></b>"; $t= "Включить";  $c="DarkGrey"; }
    ?> 
    <div  class="btn btn-primary" style='width:100px;background-color:<?=$c?>;'  href='#' onclick="switchReject(<?=$record->id?>);" > <?= $t ?></div>
    </div>    
 </div>

<br>



    

  <table border='0' width='100%'><tr>
  <td width='50%'><div style='width=100%; border:1px LightGray solid; padding:1px;'>
  
    <table border='0' width='100%'><tr>
    <td colspan=2><nobr>Наименование контрагента:</nobr></td>
    <td > 
    <?php
        $content = "";
        $n =count($model->dublicateTitle); 
        if ($n>0) $s='color:Crimson;';
            else  $s='color:Black;';
        if($record->isOrgActive == 0){$t= "text-decoration:line-through;"; }    
        else $t="";
        $content .= "<a href='#' onclick='showDubTitle()' title='Зарегестрировано организаций с таким же именем еще ".$n."' style='".$s.$t."'>";
        $content .="<b>". Html::encode($record->title)." </b>";
        $content .= "</a>"; 
        echo $content;        
        if($record->isOrgActive == 0){echo "<br><b><font color='Crimson'>Организация не действующая&nbsp;</font></b>"; }    
    ?>
    </td></tr>
    
    <tr> 
    <td colspan=2 valign='top'><nobr>Юридическое наименование:</nobr></td>
    <td rowspan=5 valign='top'> 
        <b><?= Html::encode($record->orgFullTitle) ?></b>
    </td></tr>
    
    <tr> 
    <td >ИНН:</td>
    <td> 
    <?php
      $content="";
      $n =count($model->dublicateINN); 
      if($n>0) $content.= "<a href='#' onclick='showDubINN()' title='Зарегестрировано организаций с таким же ИНН еще ".$n."' style='color:Crimson; '>";
      $content .= "<b>". Html::encode($record->schetINN)."</b>";
      if($n>0) $content .= "</a>";
      echo  $content;  
    ?>
    
    </td></tr>

    <tr> 
    <td >КПП:</td>
    <td> 
    <?php
      $content="";
      $n =count($model->dublicateKPP); 
      if($n>0) $content .= "<a href='#' onclick='showDubKPP()' title='Зарегестрировано организаций с таким же КПП еще ".$n."' style='color:Crimson;'>";
      $content .= " <b>". Html::encode($record->orgKPP)."</b>";
      if($n>0) $content .= "</a>";
      echo  $content;  
    ?>    
    </td></tr>

<!--    <tr>
    <td >ОКАТО:</td>
    <td> 
        <b><?= $model->orgOKATO?></b>
    </td></tr>

    <tr> 
    <td >ОКПО:</td>
    <td> 
        <b><?= $model->orgOKPO?></b>
    </td></tr> -->

    <tr> 
    <td >р/с:</td>
    <td> 
        <b><?= $model->orgAccount ?></b>
    </td>
    <td rowspan='2' valign='top'> <div>БИК: <b><?= $model->orgBIK?> </b></div>
    <div style='margin-top:7px;'> <?= $model->orgBank?> </div>
    </td>  
    </tr>

    <tr> 
    <td >к/с:</td>
    <td> 
        <b><?= $model->orgKS ?></b>
    </td>
    </tr>
    <tr> 
    <td valign = 'top'>адрес:</td>
    <td colspan=2><?=$adress ?></td>
    </tr>
    </table>  
  </div>  
  </td>
  <!---------------------------------------->  
  <td valign='top' width='35%'>
    <table border='0' width='100%'>
    <tr>
    <td width='100px'><nobr>Телефон:</nobr></td>
    <td > 
        <a  href='#' onclick="openWin('site/reg-contact&id=<?=$record->id ?>','contactWin');">
        <b><?= Html::encode($model->defContactPhone)?> </b></a>
    </td></tr>

    <tr>
    <td ><nobr>E-mail:</nobr></td>
    <td > 
        <a href='#' onclick="openWin('site/mail&email=<?=$model->contactEmail?>&orgId=<?=$record->id?>','childWin');">
        <b><?=Html::encode($model->defContactEmail)?> </b></a>
    </td></tr>

    <tr>
    <td ><nobr>ФИО:</nobr></td>
    <td >         
        <b><?=Html::encode($model->defContactFIO)?> </b>
    </td></tr>
    
    <tr>
    
    <td colspan = '2'>         
        <div class='btn btn-default' style='margin-top:10px;' 
        onclick="openWin('site/single-org-deals&orgId=<?=$record->id?>','dealWin')">Взаимодействие с предприятием</div>        
    </td></tr>

    <tr>
    <td valign='top'><nobr>Категория:</nobr></td>
    <td ><div style='width:100%; border:1px solid LightGray;'>               
      <b><?= $model->razdel  ?></b>
    </div></td></tr>

    <tr>
    <td valign='top' ><nobr>2 ГИС:</nobr></td>
    <td ><div style='width:100%; border:1px solid LightGray;'>                        
    <b><?= $model->dblGisLabel  ?></b>
    </div></td></tr>
    
    <tr>
    <td valign='top'>ОКВЭД</td>
    <td ><div style='width:100%; border:1px solid LightGray;'>                                 
        <b><?= $model->orgOKVED?></b>
    </div></td></tr>
  </table> 
  </td>
<!---------------------------------------->      
  <td align='right' valign='top'>
  <div>
     <a  class="btn btn-primary" style='width:100px;' href='#' onclick="openWin('site/org-dublicate&orgId=<?=  Html::encode($record->id)  ?>','dubWin');">Дубликаты</a><br>   
    <div style='text-align:right; font-size:12px; margin-top:15px;margin-bottom:10px;'>
    <?php if ($record->orgGrpRef == 0) echo "Не входит в <br>групповой контакт.";  
                                 else  echo "Группа компаний <br><b>".$model->orgGroupTitle."</b>";        
     ?>        
    </div>    
    
     <div >
        <a  class="btn btn-primary" style='width:100px;' href='#' onclick="openWin('site/org-in-grp&orgId=<?=  Html::encode($record->id)  ?>','childWin');">Изменить</a>
    </div>    

     <div style='margin-top:100px'>
        <a  class="btn btn-primary" style='width:150px;' href='#' onclick="openWin('site/org-card&orgId=<?=  Html::encode($record->id)  ?>','orgCard');">Редактировать</a>
    </div>    
  
  </div>
  </td>
  </tr></table>  

<hr>
<?php 
?> 

<?php Pjax::begin(); ?> 
<?php $form = ActiveForm::begin(); 

$content = $form->field($model, 'shortComment')->label('Комментарий - будет виден в счете и заявке. 150 символов.')."\n";

$content .= "<table border='0' width='100%'><tr>";
$content .= "<td width='40%'>";
$content .= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Заметки')."\n";
$content .= Html::submitButton('Сохранить', ['class' => 'btn btn-primary'])."\n";
$content .= "</td>";
$content .= "<td valign='top'>";

$content .= "<div style='height:25px;'>Комментарии по доставке:</div>";
$content.= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getDostavkaProvider(Yii::$app->request->get()),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
             [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:20px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isDostavkaDefault';                 
                 $action =  "saveField(".$model['id'].", 'isDostavkaDefault');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => 'note',
                'label'     => 'Комментарий',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'dostavkaNote';
                 $action =  "saveField(".$model['id'].", 'dostavkaNote');"; 
                 return Html::textArea( 
                          $id, 
                          $model['note'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:500px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],       

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:40px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'dostavkaDel';                 
                 $action =  "saveField(".$model['id'].", 'dostavkaDel');";                  
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  
        
            
            /****/
        ],
    ]
); 

$content.= " <div class='clickable glyphicon glyphicon-plus' onclick='addNewDostavka();'></div>";
$content .= "</td>";
$content .= "</tr></table>";
$content .= $form->field($model, 'id')->hiddenInput()->label(false)."\n";
 
$label = "Комментарии: ". mb_substr($model->shortComment,0,75)."..."; 
 echo Collapse::widget([
    'items' => [
        [
            'label' => $label ,
            'content' => $content,
            'contentOptions' => [],
            'options' => []
        ]
    ]
]); 
/*Затычка*/
$idx = $record->id."dostavkaAdd";
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => $idx ])->label(false);
ActiveForm::end(); ?>

<?php Pjax::end(); ?>

<?php Pjax::begin(); ?>
<?php

$content= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getContractListProvider(),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
              
            [
                'attribute' => 'creationTime',
                'label'     => 'Создан',
                'format' => ['datetime', 'php:d.m.Y h:i:s'],
            ],            

            [
                'attribute' => 'internalNumber',
                'label'     => 'Договор',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {
                 $val = "№ ".$model['internalNumber']." от ".$model['dateStart'];    
                 $action = "openWin('head/contract-edit&id=".$model['id']."','contractWin')";   
                   return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                    
               }
                       
            ],            
            
            [
                'attribute' => 'oplatePeriod',
                'label'     => 'Оплата в течении',
                'format' => 'raw',            
            ],            
            
            [
                'attribute' => 'oplateStart',
                'label'     => 'после получения',
                'format' => 'raw',            
            ],            

            [
                'attribute' => 'predoplata',
                'label'     => 'Предоплата',
                'format' => 'raw',            
            ],            

            [
                'attribute' => 'dateEnd',
                'label'     => 'Действует до',
                'format' => ['datetime', 'php:d.m.Y'],
            ],            

            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 return "<a href='#' onclick='printContract(".$model['id'].",\"doc\")'><span class='glyphicon glyphicon-print' aria-hidden='true'></span></a>";

                },

            ],

            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 return "<a href='#' onclick='printContract(".$model['id'].",\"html\")'><span class='glyphicon glyphicon-search' aria-hidden='true'></span></a>";

                },

            ],
            /****/
        ],
    ]
); 


$content.= " <div>
  <div class='btn btn-default' onclick='showResetContractDialog()' title='Привязка договоров'> <span class='glyphicon glyphicon-link'> </div>
  <div class='btn btn-default' onclick='syncContract()' title='Синхронизировать'> <span class='glyphicon glyphicon-refresh'></div>  
</div>";


 if (count($model->contarctArray) > 0)
 {
     $label = "Договор № ".$model->contarctArray[0]['internalNumber'].". Оплата в течении ".$model->contarctArray[0]['oplatePeriod']." дней, после получения " ; 
     $label .= $model->contarctArray[0]['oplateStart'].".";
     $label .= " Действует до ".$model->contarctArray[0]['dateEnd'].".";
     $label .= " Предоплата ".$model->contarctArray[0]['predoplata'].".";
 }
 else
 {
     $label = "Договор не найден. "; 
 }
 
?> 
<div class='row'>
    <div class='col-md-11'> 
<?php
 echo Collapse::widget([
    'items' => [
        [
            'label' => $label ,

            'content' => $content,
            'contentOptions' => [],
            'options' => []
        ]
    ]
]); 

?>
    </div>
    <div class='col-md-1' >     
    <!--<a href='https://docs.google.com/forms/d/e/1FAIpQLSe9cNjsjigI2inwRv2LviKcPwG3gX_P6sB4H_UfYPNkMbkaGA/viewform' class='btn btn-default' style='height:35px;margin-top:5px;' target='_blank'><span class='glyphicon glyphicon-plus'></span></a>-->   
    
    <div class='btn btn-default' style='height:35px;width:40px;margin-top:5px;' onclick="openWin('head/contract-new&refOrg=<?= $record->id?>','contractWin')" >N</div>   

    </div>
</div>
<?php Pjax::end(); ?>




    
<div class='row'>
 <div class='col-md-2'>
    <a  class="btn btn-primary"  href='#' onclick="openWin('site/contacts-detail&id=<?=  Html::encode($record->id)?>','contactWin');">История контактов</a>
 </div>

 <div class='col-md-2'>
    <a  class="btn btn-primary"  href='#' onclick="openWin('site/org-deal-reestr&orgId=<?=  Html::encode($record->id)  ?>','orgCard');">Реестр сделок</a>
 </div>

<div class='col-md-2'> 
 <a  class="btn btn-primary"  href='#' onclick="openWin('market/market-zakaz-create&id=<?=  Html::encode($record->id)  ?>','zakazWin');">Рег. заказа</a>   
</div>

<div class='col-md-2'> 
   <a  class="btn btn-primary"  href='#' onclick="openWin('site/reg-contact&id=<?=  Html::encode($record->id)  ?>','contactWin');">Рег. контакта</a>
</div>

<div class='col-md-2' > 
   <a  class="btn btn-primary"  href='#' onclick="openWin('bank/operator/doc-list&orgRef=<?=  Html::encode($record->id)  ?>','docWin');">Документооборот</a>
</div>

<div class='col-md-2'> 
   
</div>

 
</div>  
<?php
$svrData = $model->getOrgSverka();
$balance =$svrData['oplataSum'] - $svrData['supplySum'];
$color = "Black";
if ($balance<0) $color = "Crimson";
else $color = "DarkGreen";

?>
<br>
<p><b><a href='#' onclick='setManager();'> Назначен менеджер: </a> <?= $svrData['managerFIO']?></b> </p>
<div class="part-header"> Текущая сверка</div>      

<table width='100%' border='1'>
<tr style='    background-color: Silver ;'>
   <td style='padding:5px;'></td>   
   <td style='padding:5px;'>Выручено</td> 
   <td style='padding:5px;'>Отгружено</td> 
   <td style='padding:5px;'>Сверка</td> 
   <td style='padding:5px;'>Дата Оплаты</td> 
   <td style='padding:5px;'>Дата Отгрузки</td> 
</tr>

<tr>
   <td style='padding:5px;'><span class='clickable' onclick="openWin('site/orgs-client-reestr&orgId=<?= $model->orgId ?>','reestrWin');">Продажа</span></td>   
   <td style='padding:5px;'><?= number_format($svrData['oplataSum'],2,'.','&nbsp;') ?></td> 
   <td style='padding:5px;'><?= number_format($svrData['supplySum'],2,'.','&nbsp;') ?></td> 
   <td style='padding:5px;'><font color='<?= $color ?>'><?= number_format($balance,2,'.','&nbsp;') ?></font></td> 
   <td style='padding:5px;'><?= $svrData['lastOplate']?></td> 
   <td style='padding:5px;'><?= $svrData['lastSupply']?></td> 
</tr>

<?php
$supplier_balance = $svrData['supplier_supplySum'] - $svrData['supplier_oplataSum'];
$color = "Black";
if ($supplier_balance<0) $color = "Crimson";
else $color = "DarkGreen";

?>

<tr style='    background-color: Silver ;'>
   <td style='padding:5px;'></td>   
   <td style='padding:5px;'>Получено</td> 
   <td style='padding:5px;'>Оплачено</td>    
   <td style='padding:5px;'>Сверка</td> 
   <td style='padding:5px;'>Дата Получения</td> 
   <td style='padding:5px;'>Дата Оплаты</td> 
   
</tr>

<tr>
   <td style='padding:5px;'><span class='clickable' onclick="openWin('site/orgs-supplier-reestr&orgId=<?= $model->orgId ?>','reestrWin');">Закупка</span></td>   
   <td style='padding:5px;'><?= number_format($svrData['supplier_supplySum'],2,'.','&nbsp;') ?></td> 
   <td style='padding:5px;'><?= number_format($svrData['supplier_oplataSum'],2,'.','&nbsp;') ?></td> 
   <td style='padding:5px;'><font color='<?= $color ?>'><?= number_format($supplier_balance,2,'.','&nbsp;') ?></font></td> 
   <td style='padding:5px;'><?= $svrData['supplier_lastSupply']?></td> 
   <td style='padding:5px;'><?= $svrData['supplier_lastOplate']?></td> 
   
</tr>

<?php
    $itogo_balance = $balance + $supplier_balance;
    $color = "Black";
    if ($itogo_balance < 0) $color = "Crimson";
    else $color = "DarkGreen";
?>

<tr style='    background-color: Silver ;'>
   <td style='padding:5px;'></td>   
   <td style='padding:5px;'>Кредит</td> 
   <td style='padding:5px;'>Дебет</td>    
   <td style='padding:5px;'>Сверка</td> 
   <td style='padding:5px;'></td> 
   <td style='padding:5px;'></td> 
</tr>
<tr>
   <td style='padding:5px;'><b>Итого</b></td>   
   <td style='padding:5px;'><b><?= number_format($svrData['oplataSum']+$svrData['supplier_supplySum'],2,'.','&nbsp;') ?></b></td> 
   <td style='padding:5px;'><b><?= number_format($svrData['supplySum']+$svrData['supplier_oplataSum'],2,'.','&nbsp;') ?></b></td> 
   <td style='padding:5px;'><b><font color='<?= $color ?>'><?= number_format($itogo_balance,2,'.','&nbsp;') ?></font></b></td> 
   <td style='padding:5px;'></td> 
   <td style='padding:5px;'></td> 
</tr>


</table>  
  
 <br>&nbsp;<br> 
<div class="part-header"> Список активных сделок</div>   
<?php Pjax::begin(); ?>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $activityProvider,
        //'filterModel' => $model,    
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],            
                                    
            [
                'attribute' => 'zakazId',
                'label'     => 'Заказ',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    

                $action=" onclick=\"openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['zakazId']."','zakazWin');\"";
                return "<div class='child' ".$action." >".$model['zakazId']." от ". date("d.m.Y", strtotime($model['formDate']))."</div>";                                           
                },
            ],            

            [
                'attribute' => 'schetId',
                'label'     => 'Счета',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {                                    
                                                   
                $list = Yii::$app->db->createCommand(
                'SELECT id, schetNum, schetDate from {{%schet}} where refZakaz=:refZakaz ', 
                    [':refZakaz' => $model['zakazId'] ])->queryAll();
            
               $v="";
                for ($i=0;$i<count($list);$i++)
                {
                $action="openWin('market/market-schet&id=".$list[$i]['id']."','schetWin')";
                $v.= \yii\helpers\Html::tag( 'div', $list[$i]['schetNum']." ".$list[$i]['schetDate'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                
                }
            
               return $v;
                },
            ],            
    
            [
                'attribute' => 'Оплата',
                'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}},{{%schet}}
                 WHERE {{%oplata}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                [':refZakaz' => $model['zakazId'] ])->queryAll();
                 
                 //return $model['schetId'];
                 if (count($listData)==0) return "&nbsp;";                 
                 if($listData[0]['sumOplata'] == 0)return "&nbsp;";                 
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div  style='padding:5px;background-color:LightGreen'>"; 
                                                            else $ret= "<div  style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));                                                              
                  $ret.="<br>&nbsp;</div>";                  
                 return $ret;                  
                },
            ],            
            
            [
                'attribute' => 'lastSupply',
                'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}},{{%schet}} where 
                {{%supply}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                 [':refZakaz' => $model['zakazId'] ])->queryAll();

                if (count($listData)==0) return "&nbsp;";                 
                if($listData[0]['sumSupply'] == 0)return "&nbsp;";                 

                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div style='padding:5px;background-color:LightGreen'>"; 
                                                                 else $ret= "<div style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));                                                              
                  $ret.="<br>&nbsp;</div>";
                 return $ret;                  
                },
            ],            

             [
                'attribute' => 'conatctID',
                'label'     => 'Последний',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                 
                if (empty ($model['conatctID'])) return "";
                 
                $listData= Yii::$app->db->createCommand(
                'SELECT contactDate,contactFIO,note  from {{%contact}} where id=:conatctID  ', 
                [':conatctID' => $model['conatctID'],])->queryAll();
                                 
                 
                 $r="<div >";
                 if (strtotime($listData[0]['contactDate']) < time () - 60*60*24*10)  $r="<div style='background-color:Yellow'>";
                 if (strtotime($listData[0]['contactDate']) < time () - 60*60*24*30)  $r="<div style='background-color:Red'>";
                 
                 if (!empty($listData[0]['contactDate'])){$r =  date("d.m.Y", strtotime($listData[0]['contactDate']))."&nbsp;";}
                 if (!empty($listData[0]['contactFIO'])){$r.= $listData[0]['contactFIO']."<br>";}
                 if (!empty($listData[0]['note'])){$r.= $listData[0]['note'];}
                 
                 $r.="</div>";
                 return $r;
                }
            ],    

             [
                'attribute' => 'eventId',
                'label'     => 'План',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {
    
                if (empty ($model['eventId'])) return "";
                $listData= Yii::$app->db->createCommand(
                'SELECT event_date, eventNote  from {{%calendar}} where id=:eventId  ', 
                [':eventId' => $model['eventId'],])->queryAll();
                 
                 $r="<div >";
                 if (strtotime($listData[0]['event_date']) > time () )               $r="<div style='background-color:LightGreen'>";
                 if (strtotime($listData[0]['event_date']) < time () - 60*60*24*10)  $r="<div style='background-color:Yellow'>";
                 if (strtotime($listData[0]['event_date']) < time () - 60*60*24*30)  $r="<div style='background-color:Red'>";
                 
                 $r =  date("d.m.Y", strtotime($listData[0]['event_date']))."<br>";
                 $r.= $listData[0]['eventNote'];
                 $r.="</div>";
                 return $r;
                }
            ],    
            
            /****/
        ],
    ]
); 
?>

<?php Pjax::end(); ?>

<br>&nbsp;<br>   

<?php Pjax::begin(); ?>
<?php


$content= \yii\grid\GridView::widget(
    [
        'dataProvider' => $eventListProvider,
        //'filterModel' => $model,    
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],            
            [
                'attribute' => 'event_date',
                'label'     => 'Дедлайн',
                'format' => ['datetime', 'php:d-m-Y'],
            ],            
            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',            
            ],            
            

            [
                'attribute' => 'eventTitle',
                'label'     => 'Тип события',
                'format' => 'raw',            
            ],            
            
            [
                'attribute' => 'refEvent',
                'label'     => 'Событие',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 return $model['eventNote'];                          
                },
            ],    
                
            [
                'attribute' => 'note',
                'label'     => 'Комментарий',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {
                    $r="";
                 if (!empty($model['contactDate'])){$r =  date("d.m.Y", strtotime($model['contactDate']))."<br>";}
                 if (!empty($model['contactFIO'])){$r.= $model['contactFIO']."<br>";}
                 if (!empty($model['note'])){$r.= $model['note'];}
                 return $r;
                }
            ],    

            [
                'attribute' => 'id',
                'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['eventStatus'] == 2) {return "<font color='ForestGreen'><b>Выполнено</b></font";}
                    $commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
                    switch ($model['ref_event'])
                     {
                        case 0: 
                        /*Холодный звонок*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-init&id=28409                            
                            return "<input ".$commStr." value='Продолжить'  onclick=\"javascript:openWin('cold/cold-init&id=".$model['orgId']."','childwin');\" />";    
                        break;

                        case 1: 
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-need&id=28417
                        /*Выяснение потребностей*/                            
                            return "<input ".$commStr." value='Потребности'  onclick=\"javascript:openWin('cold/cold-need&id=".$model['orgId']."','childwin');\" />";    
                        break;

                        case 2:                         
                        /*Первичная Заявка на счет*/
                        //http://192.168.1.53/phone/web/index.php?r=cold/cold-schet&id=27153
                        return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('cold/cold-schet&id=".$model['orgId']."','childwin');\" />";    
                        break;

                        case 3:                         
                        /*Заявка на счет*/
                        if ($model['zakazId'] == 0)
                        {
                        ////http://192.168.1.53/phone/web/index.php?r=market/market-zakaz-create&id=27153
                            return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('market/market-zakaz-create&id=".$model['orgId']."','childwin');\" />";    
                        }        
                        //http://192.168.1.53/phone/web/index.php?r=market/market-zakaz&orgId=29136&zakazId=8                        
                        return "<input ".$commStr." value='К заявке'  onclick=\"javascript:openWin('market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."','childwin');\" />";
                        break;
                        
                        case 4:                         
                        /*Резервирование товара*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-reserve-zakaz&orgId=28417&zakazId=12
                        return "<input ".$commStr." value='Резерв.'  onclick=\"javascript:openWin('market/market-reserve-zakaz&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childwin');\" />";
                        break;
                        
                        case 5:                         
                        /*Регистрация счета*/
                        return "<input ".$commStr." value='К счету'  onclick=\"javascript:openWin('market/market-reg-schet&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childwin');\" />";
                        break;

                        case 6: 
                        /*Ведение счета*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childwin');\" />";
                        break;

                        case 7: 
                        /*Поставка*/
                        //http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12                    
                         $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
                                            [':refZakaz' => $model['zakazId'] ])->queryOne();
                         if (empty ($schetId)) {return "&nbsp;";}
                         return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childwin');\" />";
                        break;

                    }
                                        
                },
            ],            
            
            /****/
        ],
    ]
); 

 echo Collapse::widget([
    'items' => [
        [
            'label' => 'Список активных событий' ,
            'content' => $content,
            'contentOptions' => [],
            'options' => []
        ]
    ]
]); 

?>
<?php Pjax::end(); ?>


<input class="btn btn-primary"  style="width: 150px;" type="button" value="Закрыть" onclick="javascript:window.close();"/> 




<?php
Modal::begin([
    'id' =>'resetContractDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Привязать договор </h4>',
]);?>
<?php Pjax::begin(); ?>  
<?php            
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $resetContractProvider,
        'filterModel' => $model,  
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'creationTime',
                'label'     => 'Создан',
                'format' => ['datetime', 'php:d.m.Y h:i:s'],
            ],            

            [
                'attribute' => 'internalNumber',
                'label'     => 'Договор',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {
                    
                return "№ ".$model['internalNumber']." от ".$model['dateStart'];
               }                       
            ],            
                        
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',                            
            ],            
            
            [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',                            
            ],            
            
            [
                'attribute' => 'fltKPP',
                'label'     => 'КПП',
                'format' => 'raw',                            
            ],            
            
            [
                'attribute' => '',
                'label'     => '',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                return "<a href='#' onclick='linkContract(".$model['id'].")';><span class='glyphicon glyphicon-paperclip'></span></a>" ;
               }                       
            ],            
            
       ]
    ]         
);
?>
  
<?php Pjax::end(); ?>  
<?php
Modal::end();
?>


<?php
Modal::begin([
    'id' =>'setManagerDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?>
<?php Pjax::begin(); ?>  
<?php            
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $managerListprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
               [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    return "<a href='#' onclick=\"openSwitchWin('site/set-org-manager&managerId=".$model['id']."');\" >".$model['userFIO']."</a>";
               },

            ],    
         ]
    ]         
);
?>
  
<?php Pjax::end(); ?>  
<?php
Modal::end();
?>


<?php
Modal::begin([
    'id' =>'showDubTitleDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Дубликаты по названию </h4>',
]);
echo "<table class='table table-striped'>\n";
echo "<thead>
<tr>
    <th>id</th>
    <th>Название</th>
    <th>ИНН</th>
    <th>КПП</th>
</tr>
</thead>
";
$n=count($model->dublicateTitle);
for($i=0; $i<$n;$i++)
{
echo "<tr>\n";
echo "<td>".$model->dublicateTitle[$i]['id']."</td>";
echo "<td><a href='index.php?r=site/org-detail&orgId=".$model->dublicateTitle[$i]['id']."&noframe=1'>".$model->dublicateTitle[$i]['title']."</a></td>";
echo "<td>".$model->dublicateTitle[$i]['orgINN']."</td>";
echo "<td>".$model->dublicateTitle[$i]['orgKPP']."</td>";
echo "</tr>\n";
};
echo "</table>\n";
Modal::end();
?>

<?php
Modal::begin([
    'id' =>'showDubINNDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Дубликаты по ИНН </h4>',
]);
echo "<table class='table table-striped'>\n";
echo "<thead>
<tr>
    <th>id</th>
    <th>Название</th>
    <th>ИНН</th>
    <th>КПП</th>
</tr>
</thead>
";
$n=count($model->dublicateINN);
for($i=0; $i<$n;$i++)
{
echo "<tr>\n";
echo "<td>".$model->dublicateINN[$i]['id']."</td>";
echo "<td><a href='index.php?r=site/org-detail&orgId=".$model->dublicateINN[$i]['id']."&noframe=1'>".$model->dublicateINN[$i]['title']."</a></td>";
echo "<td>".$model->dublicateINN[$i]['orgINN']."</td>";
echo "<td>".$model->dublicateINN[$i]['orgKPP']."</td>";
echo "</tr>\n";
};
echo "</table>\n";


Modal::end();
?>

<?php
Modal::begin([
    'id' =>'showDubKPPDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Дубликаты по КПП </h4>',
]);
echo "<table class='table table-striped'>\n";
echo "<thead>
<tr>
    <th>id</th>
    <th>Название</th>
    <th>ИНН</th>
    <th>КПП</th>
</tr>
</thead>
";

$n=count($model->dublicateKPP);
for($i=0; $i<$n;$i++)
{
echo "<tr>\n";
echo "<td>".$model->dublicateKPP[$i]['id']."</td>";
echo "<td><a href='index.php?r=site/org-detail&orgId=".$model->dublicateKPP[$i]['id']."&noframe=1'>ы".$model->dublicateKPP[$i]['title']."</a></td>";
echo "<td>".$model->dublicateKPP[$i]['orgINN']."</td>";
echo "<td>".$model->dublicateKPP[$i]['orgKPP']."</td>";
echo "</tr>\n";
};
echo "</table>\n";


Modal::end();
?>




<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/site/save-detail']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo "<div align='center'><input type='submit' ></div>";
ActiveForm::end(); 

/*echo "<pre>";
print_r($model->debug);
echo "</pre>";*/
?>


