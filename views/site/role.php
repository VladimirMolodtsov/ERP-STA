<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */


/*
 1 0x0001     Маркетинг
 2 0x0002     Холодные звонки
 4 0x0004     Активные продажи
 8 0x0008     Кадры
 
 16 0x0010     Снабжение
 32 0x0020     Управление
 64 0x0040     Финансы
 128 0x0080     Менеджер 2ур.
 
 256 0x0100     Коммерческий директор
 512 0x0200     Начальник производства
 1024 0x0400     Оператор банка
 2048 0x0800     Глав Бух?

*/

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;


$this->title = 'Пользователи и роли';
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<style>
.lbl {
    font-size:11px;
}
     
</style>
    
<script>

function saveData(id, role, type)
{
   
   document.getElementById('recordId').value=id;
   document.getElementById('dataVal').value=role;
   document.getElementById('dataType').value=type;
   
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=site/role',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){                       
        console.log(res); 
                document.location.reload(true);               
        },
        error: function(){
            alert("Error while saving");
        }
    });	
}


function editUser (id)
{
   document.getElementById('editUserDialogFrame').src="index.php?r=site/role-edit&noframe=1&id="+id;   
   $('#editUserDialog').modal('show');       
}

function newUser()
{
   document.getElementById('editUserDialogFrame').src="index.php?r=site/role-edit&noframe=1&id=0";   
   $('#editUserDialog').modal('show');       
}

function successSave(res)
{
 $('#editUserDialog').modal('hide');          
 console.log(res); 
 document.location.reload(true);               
}

function errorSave()
{
 $('#editUserDialog').modal('hide');          
 alert("Error while saving");
}

</script>
    <h1><?= Html::encode($this->title) ?></h1>
 <div>
 
 <?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getUserListProvider(),
        'columns' => [
  
            [
                'attribute' => 'userFIO',                
                'label'     => "ФИО",                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='#' onclick='editUser(".$model['id'].")'>".$model['userFIO']."</a>";
                },
            ],        
            

           [
                'attribute' => 'phoneInternаl',                
                'label'     => "<div class='lbl'>Внутр. <br>номер</div>",                
                'encodeLabel' => false,
                'format' => 'raw',            
            ],  
            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Коммерческий <br>директор</div>",                
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0100 ){ $isOp = true;}
                    else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",256,'switch')",
                        ]
                        );
                },
            ],        
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Маркетинг</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0001 ){ $isOp = true;}
                    else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",1,'switch')",
                        ]
                        );
                },
            ],        
            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Холодные <br>звонки</div>",
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0002 ){ $isOp = true;}
                    else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",2,'switch')",
                        ]
                        );
                },
            ],        

            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Активные <br>продажи</div>",
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0004 ) { $isOp = true;}
                    else                             { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",4,'switch')",
                        ]
                        );
                },
            ],        

            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Менеджер 2ур.</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0080 ) { $isOp = true;}
                    else                             { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",128,'switch')",
                        ]
                        );
                },
            ],        

            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Начальник <br>производства</div>",
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0200 ){ $isOp = true;}
                    else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",512,'switch')",
                        ]
                        );
                },
            ],        
            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Снабжение</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0010 ) {$isOp = true;}
                    else                             {$isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",16,'switch')",
                        ]
                        );
                },
            ],        

            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Финансы</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0040 ) {$isOp = true;}
                    else                             {$isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",64,'switch')",
                        ]
                        );
                },
            ],        
            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Оператор<br>банка</div>",
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0400 ) {$isOp = true;}
                    else                             {$isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",1024,'switch')",
                        ]
                        );
                },
            ],        

            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Управление</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0020 ) {$isOp = true;}
                    else                             {$isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",32,'switch')",
                        ]
                        );
                },
            ],        
            
            [
                'attribute' => 'roleFlg',
                'label'     => "<div class='lbl'>Кадры</div>",
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['roleFlg'] & 0x0008 ) {$isOp = true;}
                    else                             {$isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'clickable label label-' . ($isOp ? 'success' : 'danger'),
                            'onclick' => "saveData(".$model['id'].",8,'switch')",
                        ]
                        );
                },
            ],        
            
            [
                'attribute' => '-',
                'label'     => "",                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $action = "rmUser(".$model['id'].")";
                    return \yii\helpers\Html::tag(
                        'span', '',                       
                        [
                            'class' => 'clickable glyphicon glyphicon-trash',
                            'onclick' => "saveData(".$model['id'].",0,'del')",
                        ]
                        );
                },
            ],        
            
        ],
    ]
);

?>

<div class="form-group">   
    <input class='btn btn-primary' type="button" value="Новый пользователь" onclick="newUser();"/> 
</DIV>

<?php
Modal::begin([
    'id' =>'editUserDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],    
]);?><div style='width:600px'>
    <iframe id='editUserDialogFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=site/role-edit&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=site/role']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

