<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

use kartik\grid\GridView;
use kartik\date\DatePicker;

$this->title = 'Реестр реализаций';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');

    $fltDate = date('Y-m-d',$model->from);


?>
<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}



.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-txt {    
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}

td {

padding:5px;
}
</style>

<script>

var mode = <?= $model->mode ?>;

function setMode(m)
{
    mode=m;
    applyFilter();
}

function applyFilter()
{

 dFrom=   document.getElementById('from_date').value;
 dTo=   document.getElementById('to_date').value;
 curOwner=   document.getElementById('curOwner').value;

 var url= 'index.php?r=store/sale-list&noframe=1';
 var url=url + '&curOwner='+ curOwner;
 var url=url + '&from_date='+ dFrom;
 var url=url + '&to_date='+dTo;
  var url=url + '&mode='+mode;

 document.location.href = url;

}
function openDoc(docURI){
    openExtWin(docURI,'childWin');
}



function setCalendaFilter(d,m,y)
{
    var fltDate = y+'-'+m+'-'+d;
//alert(fltDate);
    
    var url = 'index.php?r=/store/sale-list&noframe=1&mode=0&fltDate='+fltDate;
    document.location.href=url;
}

function showCalendar(){
    $('#selectCalendarDialog').modal('show');    
    
}

function selectNow()
{
    var url = 'index.php?r=/store/sale-list';
    document.location.href=url;
}



function selectError()
{
    var url = 'index.php?r=/store/sale-list&noframe=1&mode=1&fltDate=<?= $model->fltDate ?>';
    document.location.href=url;
}


</script>



<div class='spacer'></div>

<table border='0' width='100%'>
<tr>
    <td width='250px'>
    <p><?= Html::encode($this->title) ?></p>
    <?php
    echo  Html::dropDownList(
                          'ownerSelector',
                          $model->curOwner,
                          $model->getOwnerArray(),
                              [
                              'class' => 'form-control',
                              //'style' => 'width:70px;font-size:12px; padding:1px;'.$c,
                              'id' => 'curOwner',
    //                          'onchange' => 'changeOwner();'
                              ]);

    ?>
    </td>       


    <td  width='250px' style='text-align:left'>
    Реализовано за период:
    <div class='spacer'></div>     
    <?php   
   echo DatePicker::widget([
    'name' => 'from_date',
    'id' => 'from_date',
    'value' => date("d.m.Y",$model->from),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => [
     //'onchange' => 'changeShowDate();',
     ],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>            
    <?php   
   echo DatePicker::widget([
    'name' => 'to_date',
    'id' => 'to_date',
    'value' => date("d.m.Y",$model->to),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'options' => [
    //'onchange' => 'changeShowDate();',
    ],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
   </td>       
    <td style='text-align:right'  width='100px'>
    <div class='spacer' style='height:60px;'></div>
    <?php echo \yii\helpers\Html::tag( 'div', 'Применить',
                   [
                     'class'   => 'btn btn-primary',
                     'onclick' => 'applyFilter();',
                     'title'   => 'Применить фильтр',
                   ]);
    ?>
    </td>       
    
    <td></td>

    <td style='text-align:right'   width='120px'>
                <div  class='btn btn-primary leaf  <?PHP if ($model->mode==0) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'                  
                 onclick='setMode(0)'> 
                <div class='leaf-txt' >Все</div>
                <div class='leaf-val' ><?= $model->statSale['all'] ?></div> 
                <div class='leaf-sub' ></div>
                </div>
        
    </td>       

    <td style='text-align:right' width='120px'>
                <div  class='btn btn-primary leaf  <?PHP if ($model->mode==1) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'                  
                 onclick='setMode(1)'> 
                <div class='leaf-txt' >Ошибки</div>
                <div class='leaf-val' style='color:Crimson'><?= $model->statSale['err'] ?></div> 
                <div class='leaf-sub' ></div>
                </div>
        
    </td>       
    
    <td  style='text-align:right' width='120px'>
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='showCalendar();'>
        <div class='leaf-txt' > Календарь  </div>
        <div class='leaf-val' ></div> 
        <div class='leaf-sub'></div>
    </a>
    </td> 
        
</tr></table>
<br>
<?php


echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

           [
                'attribute' => 'ownerOrgTitle',
                'label' => 'Организация <br>собственник',
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:150px; font-size:10px;']

         ],
            [
                'attribute' => 'saleDate',
                'label' => 'Дата <br>реализации',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                return date("d.m.Y", strtotime($model['saleDate']));
                }
                
            ],                   

            [
                'attribute' => 'ref1C',
                'label' => 'Номер <br>реализации',
                'encodeLabel' => false,
                'format' => 'raw',
            ],                   
            
                        
            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                 if (empty($model['orgRef'])) return $model['orgTitle']."<br>".$model['orgINN'];
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgRef']."\")' >".$model['orgTitle']."<br>".$model['orgINN']."</a>";
                },
            ],        

            [
                'attribute' => 'wareSum',
                'label' => 'Сумма',
                'format' => 'raw',
            ],                   
            
                        
            
            [
                'attribute' => 'refDocumentOrig',                
                'label'     => 'УПД ориг',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."refDocumentOrig";
                $style="";    
                $val ="&nbsp;";                
                $url="";
                $action = "";
                $v =0;  
                           
               if ($model['refDocumentOrig'] >0 ) {
                 $strSql =" SELECT docURI FROM {{%documents}} where id =:refDoc";
                 $url = Yii::$app->db->createCommand($strSql,[':refDoc' => $model['refDocumentOrig']])->queryScalar();     
                 $action =  "openDoc('".$url."');";                
                 $style='background:Green;color:Green;'; 
                 $v=1;
               }                 
               else   {$style='background:White;color:White;';}    
            
               $val = \yii\helpers\Html::tag( 'div', $v, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],                                         

            
            [
                'attribute' => 'refDocumentCopy',                
                'label'     => 'УПД скан',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."refDocumentCopy";
                $style="";    
                $v =0;                
                $url="";
                $action = "";
                           
               if ($model['refDocumentCopy'] >0 ) {
                 $strSql =" SELECT docURI FROM {{%documents}} where id =:refDoc";
                 $url = Yii::$app->db->createCommand($strSql,[':refDoc' => $model['refDocumentCopy']])->queryScalar();     
                 $action =  "openDoc('".$url."');";                
                 $style='background:Green;color:Green;'; 
                 $v =1;                
               }                 
               else   {$style='background:White;color:White;';}    
            
               $val = \yii\helpers\Html::tag( 'div', $v, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],                                         
            
/*
         [
                'attribute' => 'saleNote',                
                'label'     => 'Комментарий',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'saleNote';                 
                 $action =  "saveField(".$model['id'].", 'saleNote');"; 
                                  
                 $val= Html::textInput( 
                          $id, 
                          $model['saleNote'],                                
                              [
                              'title'    => $model['saleNote'],
                              'class' => 'form-control',
                              'style' => 'width:150px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                return $val;              
               }                
            ],            


            
            [
                'attribute' => '-',                
                'label'     => 'экспед.<br> расписка ',
                'encodeLabel' => false,
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."refUpd";
                $style="";    
                $val ="&nbsp;";                
                           
               //if ($model['Upd'] >0 ) {$style='background:Green;color:White;'; }                 
                                              {$style='background:White;';}    
                     
               $action =  "switchData(".$model['id'].",refUpd);"; 
            
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],                                         
                                    
                                    
            [
                'attribute' => '-',                
                'label'     => 'счет ТК',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."refUpd";
                $style="";    
                $val ="&nbsp;";                
                           
               //if ($model['Upd'] >0 ) {$style='background:Green;color:White;'; }                 
                                              {$style='background:White;';}    
                     
               $action =  "switchData(".$model['id'].",refUpd);"; 
            
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],                                         

            [
                'attribute' => '-',                
                'label'     => 'Дата факт.',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."fact";
                $style="";    
                $val ="";                
                           
               //if ($model['Upd'] >0 ) {$style='background:Green;color:White;'; }                 
                                              {$style='background:White;';}    
                     
               $action =  "saveData(".$model['id'].",refUpd);"; 
            
                   
                   return    DatePicker::widget([
                        'name' => $id,
                        'id'   => $id,
                        'value' => $val,    
                        'type' => DatePicker::TYPE_INPUT,
                        'options' => [
                        'onchange' => $action,
                        'style' => 'width:65px;font-size:11px; padding:1px',
                        ],
                        'pluginOptions' => [    
                        'autoclose'=>true,
                        'format' => 'dd.mm.yyyy'        
                        ]
                    ]);                    
               }
                               
            ],                                         
*/                        
                        
        ],
    ]
);

?>


<pre>
<?php
//echo $model->curOwner;
?>
</pre>




<?php
Modal::begin([
    'id' =>'selectCalendarDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectCalendarDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/store/sale-calendar&noframe=1&month=<?=date('m',($model->from))?>&year=<?=date('Y',($model->from))?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>
