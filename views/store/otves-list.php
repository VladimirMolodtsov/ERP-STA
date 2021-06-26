<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Отвесы';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


?>

<style>

.btn-small{
margin:2px;
font-size: 10pt;
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



</style>
  
<script>


/*************/
function syncOtves()
{
    //document.getElementById('dataVal').value=val;    
    //var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});   
    $.ajax({
        url: 'index.php?r=data/sync-otves',
        type: 'GET',
        dataType: 'json',
        //data: data,
        success: function(res){     
            console.log(res);
            $(document.body).css({'cursor' : 'default'});            
            document.location.reload(true); 
            
        },
        error: function(){
            alert('Error while sync data!');
            $(document.body).css({'cursor' : 'default'});                        
        }
    });	
}
/*************/


function chngSwitch(res){

    var idx=res.recordId+res.dataType;
    console.log(idx);
    if (res.val == 1 )document.getElementById(idx).style.background='DarkBlue';
                else document.getElementById(idx).style.background='White';

}

function switchActive(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='switchOtves';
    saveData(0);   
}

function switchInUse(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='switchOtvesUse';
    saveData(0);   
}

/*************/
function switchProduct(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='isProduction';
    saveData(0);   
}

/*************/
function opentTypeList(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareTypeName';
    $('#selectTypeDialog').modal('show');   
}

function setSelectedType(typeRef)
{
   $('#selectTypeDialog').modal('hide');   
   saveData(typeRef);   
}
/*************/
function opentGroupList(id)
{   
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareGrpTitle';
    $('#selectGroupDialog').modal('show');   
}

function setSelectedGroup(grpRef)
{
   $('#selectGroupDialog').modal('hide');   
   saveData(grpRef);   
}

/*************/
function openProducerList(id)
{   
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareProdTitle';
    $('#selectProducerDialog').modal('show');   
}

function setSelectedProducer(prodRef)
{
   $('#selectProducerDialog').modal('hide');   
   saveData(prodRef);   
}


/*************/
function opentWareList(id)
{       
  openWin('store/ware-set&refSclad='+id,'wareSetWin');  
}


function addSelectedWare(wareRef,edRef)
{
   $('#selectWareDialog').modal('hide');   
   saveData(wareRef);   
}


function openGoodCard(id)
{
    openWin('store/good-card&id='+id,'wareCard');

}


function showErr(err)
{
    document.location.href='index.php?r=store/ware-show&errOnly='+err;
}



function switchAvailable(id)
{
    document.getElementById('recordId').value=id;    
    document.getElementById('dataVal').value=0;    
    document.getElementById('dataType').value='switchOtves';    
    saveData ();
}

function saveField(id, type)
{
    document.getElementById('recordId').value=id;  
    var idx= id+type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;    
    document.getElementById('dataType').value=type;    
    saveData ();
}
 

function saveData ()
{
    var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});
    $.ajax({
        url: 'index.php?r=store/save-otves-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
            if(res.reload==true)document.location.reload(true);
            if(res.isSwitch==true)chngSwitch(res);
        },
        error: function(){
            alert('Error while save data!');
            $(document.body).css({'cursor' : 'default'});
        }
    });

}

function  openRequestSupply(id){
  openWin("store/supply-request-new&id="+id,"supplyWin");
}


function  setMode(mode)
{
 url = 'index.php?r=store%2Fotves-list&noframe=<?= $noframe ?>&mode='+mode;
 document.location.href=url;

}
</script>


<div class='spacer'></div>


<?php

$leafValue=$model->getOtvesLeaf();

?>


<table border='0' width='100%' > 
        <tr>        
            <td width='250px'>
                <h3><?= Html::encode($this->title) ?></h3>
            </td>

            <td width='100px'> 
            <?php
                $action="setMode(0);";
                if ($model->mode==0) $class = "leaf-selected";
                                else $class ="";
               ?>
            
                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
                 onclick='<?= $action ?>'   title='Нет связанного завершенного счета'>
                <div class='leaf-txt' style='font-size:17px;'> Все </div>
                <div class='leaf-val' style='color:Blue'><?= $leafValue['all'] ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>                

            <td width='10px'>
            </td>
                        

            <td width='100px'> 
            <?php
                $action="setMode(1);";
                if ($model->mode==1) $class = "leaf-selected";
                                else $class ="";
            ?>
            
                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
                 onclick='<?= $action ?>'  title='Отвесы забронированные (отгрузка не начата)'>
                <div class='leaf-txt' >Забронировано</div>
                <div class='leaf-val' style='color:Crimson'><?= $leafValue['inWork']  ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>                

            <td width='10px'>
            </td>


            <td width='100px'>
            <?php
                $action="setMode(2);";
                if ($model->mode==2) $class = "leaf-selected";
                                else $class ="";
            ?>

                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:LightYellow ; color:Blue;'
                 onclick='<?= $action ?>' title='Отвесы связанные со счетами для которых начата отгрузка'>
                <div class='leaf-txt' >В отгрузке</div>
                <div class='leaf-val' style='color:Blue'><?= $leafValue['inSupply']  ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>

            <td width='10px'>
            </td>

            <td width='100px'>
            <?php
                $action="setMode(3);";
                if ($model->mode==3) $class = "leaf-selected";
                                else $class ="";
            ?>

                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:LightYellow ; color:Blue;'
                 onclick='<?= $action ?>' title='Отвесы не забронированны и помечены как доступные'>
                <div class='leaf-txt' >Доступно</div>
                <div class='leaf-val' style='color:Blue'><?= $leafValue['isAvailable']  ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>

            <td width='10px'>
            </td>

            <td width='100px'>
            <?php
                $action="setMode(4);";
                if ($model->mode==4) $class = "leaf-selected";
                                else $class ="";
            ?>

                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:LightYellow ; color:Blue;'
                 onclick='<?= $action ?>' title='Все не забронированные отвесы' >
                <div class='leaf-txt' >Свободно</div>
                <div class='leaf-val' style='color:Blue'><?= $leafValue['inSclad']  ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>



            <td width='100px'>
            </td>
                        
            <td width='100px'> 
            <?php
                $action="openWin('store/otves-svod','svodWin')";
            ?>
            
                <div  class='btn btn-primary leaf  <?= $class ?>' style='background:WhiteSmoke ; color:Blue;'                  
                 onclick="<?= $action ?>"> 
                <div class='leaf-txt' > Сводная</div>
                <div class='leaf-val' style='color:Crimson'></div> 
                <div class='leaf-sub' ></div>
                </div>
            </td>                
            
            <td align='right'>

            <!-- <div align='right'><span onclick='syncOtves()' class='clickable glyphicon glyphicon-refresh'></span></div> -->
            
            </td>
            
    </tr>
</table>    

<div class='spacer'></div>
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
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [


            [
                'attribute' => 'id',
                'label' => '#',

            ],
            [
                'attribute' => 'addDate',
                'label' => 'Дата',
                'format' => ['date', 'php:d.m.Y']  ,
            ],          
        
   /*         [
                'attribute' => '',
                'label' => 'Поставщик',
     //           'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'filter' => $model->getWareInOtves(),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            

                  if (!empty($model['refWarehouse'])) {
                  $val = Yii::$app->db->createCommand('SELECT wareProdTitle FROM  {{%ware_producer}}, {{%warehouse}}
                  WHERE  {{%ware_producer}}.id={{%warehouse}}.producerRef AND {{%warehouse}}.id =:refWarehouse',
                    [
                    ':refWarehouse' =>$model['refWarehouse'],
                    ])->queryScalar();
                   }
                   else
                   {
                    $val = Yii::$app->db->createCommand('SELECT wareProdTitle FROM  {{%ware_producer}}, {{%ware_list}}
                    WHERE  {{%ware_producer}}.id={{%ware_list}}.producerRef AND {{%ware_list}}.id =:refWareList',
                    [
                    ':refWareList' =>$model['refWareList'],
                    ])->queryScalar();
                   }                 
                   
                   return $val;
                }
            ],*/

            [
                'attribute' => 'supplier',
                'label' => 'Поставщик',
     //           'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'filter' => $model->getWareInOtves(),
                'contentOptions' => ['style' => 'width:220px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = $model['id']."otvesSupplier";
                    $action =  "saveField(".$model['id'].", 'otvesSupplier');";
                     return Html::textInput(
                          $id,
                          $model['supplier'],
                              [
                              'class' => 'form-control',
                              'style' => 'font-size:11px;padding:1px;',
                              'id' => $id,
                              'onchange' => $action,
                              ]);
                },
            ],

           [
                'attribute' => 'isAvaivable',
                'label' => 'Доступно',
                'filter' => [0 => 'Все', 1=> 'Да', 2 => 'Нет'],
                //'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 if ($model['isAvaivable'] == 1) $style = 'background:DarkBlue';
                                            else $style = 'background:White';

                 $action = "switchAvailable(".$model['id'].")";
                 $id = $model['id'].'switchOtves';
                   $val = \yii\helpers\Html::tag( 'div', "",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Доступность',
                     'style'   => $style,
                   ]);

                   return $val;
                }

            ],            
        
        
            [
                'attribute' => 'wareTitle',
                'label' => 'Номенклатура',
                'contentOptions' => ['style' => 'width:220px'],
                'filter' => $model->getWareInOtves(),
                'format' => 'raw',
            ],
           
            [
                'attribute' => 'size',
                'label' => 'Вес ',
                'format' => 'raw',
                'contentOptions'=>['style' => 'width:75px;padding-left:0px;padding-right:0px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = $model['id'].'size';                 
                 $action =  "saveField(".$model['id'].", 'size');"; 
                 return Html::textInput( 
                          $id, 
                          $model['size'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:75px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                }
            ],    
            
                    
            [
                'attribute' => 'inUse',
                'label' => 'Бронь',
                'format' => 'raw',
                'filter' => [0 => 'Все', 1 => 'Свободны', 2 => 'Бронь' ],
                'contentOptions'=>['style' => 'width:100px;padding-left:0px;padding-right:0px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = $model['id'].'note';                 
                 $action =  "saveField(".$model['id'].", 'note');"; 
                 $val1=Html::textInput( 
                          $id, 
                          $model['note'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);

                 if ($model['inUse'] == 1) $style = 'background:DarkBlue';
                                      else $style = 'background:White';
                 $action = "switchInUse(".$model['id'].")";
                 $id = $model['id'].'switchOtvesUse';
                   $val2 = \yii\helpers\Html::tag( 'div', "",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Бронь',
                     'style'   => $style,
                   ]);
                                                            
                  return "<table border='0'><tr><td>".$val1."</td><td>".$val2."</td></tr></table>";
                }
             
                
            ],          
            
            
            [
                'attribute' => '',
                'label' => 'Отгрузка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){

                 if (empty($model['refSchet'])) return "";
                 
                 $id = $model['id'].'supply';                 
                  $requestSupply = Yii::$app->db->createCommand('SELECT {{%request_supply}}.id, {{%request_supply}}.supplyDate FROM {{%request_supply}}
                  WHERE  {{%request_supply}}.refSchet =:refSchet',
                    [
                    ':refSchet' =>$model['refSchet'],
                    ])->queryOne();
                 if(empty($requestSupply)) return "";
                 
                 $action = "openRequestSupply(".$requestSupply['id'].")";                 
                 $val = \yii\helpers\Html::tag( 'div', "№".$requestSupply['id']." на ".$requestSupply['supplyDate'],
                 [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Отгрузка',
                 ]);

                 return $val;
                }
            ],

                        
            [
                'attribute' => 'otvesComment',
                'label' => 'Комментарий',
                'format' => 'raw',
                'contentOptions'=>['style' => 'width:100px;padding-left:0px;padding-right:0px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = $model['id'].'otvesComment';                 
                 $action =  "saveField(".$model['id'].", 'otvesComment');"; 
                 return Html::textInput( 
                          $id, 
                          $model['otvesComment'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                }
            ],                        
            

            [
                'attribute' => 'refZakaz',
                'label' => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 if (!empty($model['refZakaz'])) {
                  $list = Yii::$app->db->createCommand('SELECT {{%zakaz}}.id, {{%zakaz}}.formDate FROM  {{%zakaz}}
                  WHERE  {{%zakaz}}.id =:refZakaz',
                    [
                    ':refZakaz' =>$model['refZakaz'],
                    ])->queryAll();
                 if(!empty($list)){
                 $action="openWin('market/market-zakaz&zakazId=".$model['refZakaz']."','sdelkaWin')";
                 $id = 'zakaz'.$model['id'];
                 $val = \yii\helpers\Html::tag( 'div', "№".$list[0]['id']." от ".$list[0]['formDate'],
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
//                     'style'   => $style,
                   ]);


                 return $val;
                 }
                 }
                }

            ],

            [
                'attribute' => '',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 if (!empty($model['refSchet'])) {
                  $list = Yii::$app->db->createCommand('SELECT {{%schet}}.id, schetNum, {{%schet}}.schetDate FROM {{%schet}}
                  WHERE  {{%schet}}.id =:refSchet',
                    [
                    ':refSchet' =>$model['refSchet'],
                    ])->queryAll();

                if(!empty($list)){

                 $action="openWin('market/market-schet&id=".$model['refSchet']."','sdelkaWin')";
                 $id = 'schet'.$model['id'];
                 $val = \yii\helpers\Html::tag( 'div', "№".$list[0]['schetNum']." от ".$list[0]['schetDate'],
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                 return $val;
                 }
                 }
                }

            ],



            [
                'attribute' => '',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                  $title ="";
                 if (!empty($model['refZakaz'])) {
                  $title = Yii::$app->db->createCommand('SELECT {{%orglist}}.title FROM {{%orglist}}, {{%zakaz}}
                  WHERE  {{%orglist}}.id = {{%zakaz}}.refOrg and {{%zakaz}}.id =:refZakaz',
                    [
                    ':refZakaz' => $model['refZakaz'],
                    ])->queryScalar();
                 }


                 if (!empty($model['refSchet'])) {
                  $title = Yii::$app->db->createCommand('SELECT {{%orglist}}.title FROM {{%orglist}}, {{%schet}}
                  WHERE  {{%orglist}}.id = {{%schet}}.refOrg and {{%schet}}.id =:refSchet',
                    [
                    ':refSchet' => $model['refSchet'],
                    ])->queryScalar();
                 }

                    return $title;

                }

            ],


            [
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],            
                        
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-otves-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'wareNameRef' )->hiddenInput(['id' => 'wareNameRef' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
