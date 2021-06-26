<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Collapse;


$this->title = 'Товар на складе (наименования по документам)';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


$record = $model->loadData();
if (empty($record )) return;
?>

<style>

</style>
  

<script type="text/javascript">
function setAvRashod()
{
  val = document.getElementById('avRashod').value;  
  openSwitchWin('store/set-av-rashod&id=<?= $model->id ?>&val='+val);
  window.opener.location.reload(false);   
 // window.close();
}


/*************/
function saveData(val)
{
    document.getElementById('dataVal').value=val;    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-good-card',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

/*************/
function openWareList()
{   
    document.getElementById('recordId').value=<?= $record->id ?>; 
    document.getElementById('dataType').value='wareTitle';
    $('#selectWareDialog').modal('show');   
}


function addSelectedWare(wareRef,edRef)
{
   $('#selectWareDialog').modal('hide');      
   saveData(wareRef);   
}

/*************/



</script >

<table width=95% class='table'>
<tr>
    <td colspan=3><h4><?= $record->title?></h4></td>
    <td>    
    <div>
    <?php   
       $action = "switchData('inPrice')";   
       if ($record->isActive == 1)  $style = 'background:DarkBlue';
                               else $style = 'background:White';
                   
       echo  \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => 'supplierTitle',
                     'onclick' => $action,
                     'style'  => 'margin-top:0px;'
                   ]);

       echo  \yii\helpers\Html::tag( 'div', 'В прайс ', 
                   [
                     'style'  => 'display:inline; position:relative; font-size:12px; margin-left:10px;'
                   ]);

    ?>
    </div>    
    </td>
    
   <td>        
    <?php   
       $action = "";   
       echo  \yii\helpers\Html::tag( 'div', 'Дубликаты', 
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'supplierTitle',
                     'onclick' => $action,
                     'style'  => 'background:LightBlue; width:100px;  margin-top:0px;'
                   ]);

   
    ?>    
    </td>
    
</tr>

<tr>
    <td>
    <input class='form-control'  name='wareTitle' id='wareTitle' value='<?= $model->wareTitle ?>'></td>

    <td colspan=2>
    <?php   
       $action = "openWareList()";   
                   
       echo  \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-search',
                     'id'      => 'openWareListBtn',
                     'onclick' => $action,
                     'style'  => 'margin-top:10px;'
                   ]);

       

    ?>
    </td>    
   
   <td>        
    
    </td>
   
   
    <td>        
    <?php   
       $action = "";   
       echo  \yii\helpers\Html::tag( 'div', 'Аналоги', 
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'supplierTitle',
                     'onclick' => $action,
                     'style'  => 'background:Green; width:100px; margin-top:0px;'
                   ]);

   
    ?>    
    </td>
   
</tr>




<tr>
    <td colspan=3>
        <table width=100% class='table'>
        <tr>
            <td>Cредний расход в день</td>
            <td><input class='form-control'  style='width:100px;' name='avRashod' id='avRashod' value='<?= $record->avRashod ?>'></td>    
        </tr>
        <tr>
        <td colspan=4>

        <?php
        
        
        $remainContent= \yii\grid\GridView::widget(
        [
        'dataProvider' => $model->getWareInScladProvider("", $record->id),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
        
             [
                'attribute' => 'scladTitle',                
                'label'     => 'Склад',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:2px;text-align:left;'],                
            ],  

             [
                'attribute' => 'orgTitle',                
                'label'     => 'Орг-ция',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:2px;text-align:left;'],                
            ],  

             [
                'attribute' => 'goodAmount',                
                'label'     => 'К-во',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:2px;text-align:left;'],                
            ],  
            
             [
                'attribute' => 'initPrice',                
                'label'     => 'Цена',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:2px;text-align:left;'],                
            ],  
            
            
        ],
    ]
    );
   
   $remainlabel = "Текущий остаток: ".$record->amount." ".$record->ed ;
   
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' =>  $remainlabel,
            'content' => $remainContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
        
    ?>
    </td>
        </tr>

        </table>
        
    <p> Расход </p>
    <table class='table table-stripped table-small'>
    <?php
    $curY = date("Y");      
    echo "<tr>";
    echo "<th> Месяц</th>";
        for ($y=$curY-2;$y<=$curY;$y++) echo "<th>".$y."</th>";
        echo "<td>На начало</td>\n";
        echo "<td>Приход</td>\n";
        echo "<td>На конец</td>\n";
        echo "<td>В день</td>\n";
        echo "<td>дата</td>\n";
    echo "</tr>\n";
    $prihod = 0;
    $rashod=[0,0,0,0];
    for ($m=1;$m<13;$m++)    
    {
      echo "<tr>";         
      echo "<td>".$m."</td>";
      $i=0;
      for ($y=$curY-2;$y<=$curY;$y++) { 
        echo "<td>".$model->rashodDetail[$y][$m]['v']."</td>"; 
        $rashod[$i]+=$model->rashodDetail[$y][$m]['v'];
        $i++; 
        }
      
        echo "<td>".$model->rashodDetail[$curY][$m]['next']."</td>\n";
        echo "<td>".$model->rashodDetail[$curY][$m]['prihod']."</td>\n";                
        echo "<td>".$model->rashodDetail[$curY][$m]['cur']."</td>\n";        
        echo "<td>".number_format($model->rashodDetail[$curY][$m]['v']/date('t',strtotime($y."-".$m."-01")),2,'.','&nbsp;' )."</td>\n";
        echo "<td>".$model->rashodDetail[$curY][$m]['onDate']."</td>\n";
        $prihod+=$model->rashodDetail[$curY][$m]['prihod'];        
     echo "</tr>\n";    
    }  
    
    
      echo "<tr>";         
      echo "<td>ИТОГО</td>";
      for ($i=0;$i<3;$i++)  echo "<td>".$rashod[$i]."</td>";        
        echo "<td>".$model->rashodDetail[$curY][1]['next']."</td>\n";
        echo "<td>".$prihod."</td>\n";                
        echo "<td>".$model->rashodDetail[$curY][12]['cur']."</td>\n";        
        echo "<td>".number_format($rashod[2]/365,2,'.','&nbsp;' )."</td>\n";
        echo "<td>"."</td>\n";
     echo "</tr>\n";    
    
    ?>
    </table>
        
    </td>   
    
    <td >
        <table width=100% >
        <tr>
           <td>Применение</td>
           <td><?php 
                echo  Html::textarea('goodUse',  '',                                
                [
                'class' => 'form-control',
                'style' => 'width:250px; height:60px; font-size:11px;padding:1px;', 
                'id' => 'goodUse', 
                //'placeholder' => 'формат',
            ]);             
            ?>
<br>
        </tr>
    
        <tr>
            <td>Тех. хар-ки</td>
            <td><?php 
                echo  Html::textarea('techParam',  '',                                
                [
                'class' => 'form-control',
                'style' => 'width:250px; height:60px; font-size:11px;padding:1px;', 
                'id' => 'techParam', 
                //'placeholder' => 'формат',
            ]);             
            ?>
          </td>    
        </tr>
       </table>
    
    
    </td>   
</tr>


</table>




<h4> Поступление товара</h4>
<?php
echo GridView::widget(
    [
        'dataProvider' => $purchProvider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
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
                'class' => \yii\grid\SerialColumn::class,
            ],


			[
                'attribute' => 'requestDate',
				'label'     => 'Поставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
		
					return $model['requestNum']." ".date("d.m.Y", strtotime($model['requestDate']));
                },
            ],		

			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'wareEd',
				'label'     => 'Ед.',
                'format' => 'raw',
             ],		

             [
                'attribute' => '-',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['wareSumm']/$model['wareCount'],2,'.','&nbsp;');
                },
             ],		
             
             [
                'attribute' => 'wareSumm',
				'label'     => 'На сумму',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['wareSumm'],2,'.','&nbsp;');
                },
             ],		

        ],
    ]
	);   
?>

<h4> Отпуск товара</h4>
<?php
echo GridView::widget(
    [
        'dataProvider' => $supplyProvider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
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
                'class' => \yii\grid\SerialColumn::class,
            ],


			[
                'attribute' => 'supplyDate',
				'label'     => 'Отгрузка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
		
					return date("d.m.Y", strtotime($model['supplyDate']));
                },
            ],		

			[
                'attribute' => 'schetDate',
				'label'     => 'По счету',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return $model['schetNum']." от ".date("d.m.Y", strtotime($model['schetDate']));
                },
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Клиент',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'supplyCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'supplyEd',
				'label'     => 'Ед.',
                'format' => 'raw',
             ],		

             [
                'attribute' => '-',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['supplySumm']/$model['supplyCount'],2,'.','&nbsp;');
                },
             ],		
             
             [
                'attribute' => 'supplySumm',
				'label'     => 'На сумму',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['supplySumm'],2,'.','&nbsp;');
                },
             ],		



        ],
    ]
	);
    
    
    
    
?>

 

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectWareDialog'  src='index.php?r=store/ware-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-good-card']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo "<input type='submit'>";
ActiveForm::end(); 
?>

<pre>


<?php

print_r($model->debug);

echo "---------------------------------------------------\n";
//print_r($model->rashodDetail);
?>

</pre>

