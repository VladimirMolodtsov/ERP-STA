<?php

/* @var $this yii\web\View */

//use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Работа с заявкой';
//$this->params['breadcrumbs'][] = $this->title;

$zakazRecord=$model->getZakazRecord();

  if ($zakazRecord['isActive'] == 1) $model->status=3; /*Отложено - на согласовании*/
  if ($zakazRecord['isActive'] == 0)
  { 
   if ($zakazRecord['isFormed'] == 0) $model->status=2; /*не активна и не сформирована == отказано*/
                                else  $model->status=1; /*не активна и сформирована == согласовано*/   
  }

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<link rel="stylesheet" type="text/css" href="css/zvonki-common.css" />
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="tcal.js"></script> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
<script type="text/javascript" src="phone.js"></script> 

<style>
.button {
    width: 150px;
    font-size: 10pt;    
} 
 .btn-block{
    padding: 2px;     
 }
 
.gridcell {
    width: 100%;        
    height: 100%;
    /*background:DarkSlateGrey;*/
}    

.nonActiveCell {
    width: 100%;        
    height: 100%;    
    color:Gray;
    text-decoration: line-through;
}    


.gridcell:hover{
    background:DarkSlateGrey;
    color:#FFFFFF;
}
 
#add_zakaz_form {
    width: 500px;     
    height: 300px; /* Рaзмеры дoлжны быть фиксирoвaны */
    font-size: 12pt;
    border-radius: 8px;
    border: 3px #000 solid;
    background: #fff;
    position: fixed; /* чтoбы oкнo былo в видимoй зoне в любoм месте */
    top: 45%; /* oтступaем сверху 45%, oстaльные 5% пoдвинет скрипт */
    left: 50%; /* пoлoвинa экрaнa слевa */
    margin-top: -150px;
    margin-left: -150px; /* тут вся мaгия центрoвки css, oтступaем влевo и вверх минус пoлoвину ширины и высoты сooтветственнo =) */
    display: none; /* в oбычнoм сoстoянии oкнa не дoлжнo быть */
    opacity: 0; /* пoлнoстью прoзрaчнo для aнимирoвaния */
    z-index: 5; /* oкнo дoлжнo быть нaибoлее бoльшем слoе */
    padding: 20px 10px;
}
/* Кнoпкa зaкрыть для тех ктo в тaнке) */
#add_zakaz_form #add_zakaz_close {
    width: 21px;
    height: 21px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    display: block;
}

#edit_zakaz_form {
    width: 500px;     
    height: 300px; /* Рaзмеры дoлжны быть фиксирoвaны */
    font-size: 12pt;
    border-radius: 8px;
    border: 3px #000 solid;
    background: #fff;
    position: fixed; /* чтoбы oкнo былo в видимoй зoне в любoм месте */
    top: 45%; /* oтступaем сверху 45%, oстaльные 5% пoдвинет скрипт */
    left: 50%; /* пoлoвинa экрaнa слевa */
    margin-top: -150px;
    margin-left: -150px; /* тут вся мaгия центрoвки css, oтступaем влевo и вверх минус пoлoвину ширины и высoты сooтветственнo =) */
    display: none; /* в oбычнoм сoстoянии oкнa не дoлжнo быть */
    opacity: 0; /* пoлнoстью прoзрaчнo для aнимирoвaния */
    z-index: 5; /* oкнo дoлжнo быть нaибoлее бoльшем слoе */
    padding: 20px 10px;
}
/* Кнoпкa зaкрыть для тех ктo в тaнке) */
#edit_zakaz_form #edit_zakaz_close {
    width: 21px;
    height: 21px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    display: block;
}

/* Пoдлoжкa */
#overlay {
    z-index:3; /* пoдлoжкa дoлжнa быть выше слoев элементoв сaйтa, нo ниже слoя мoдaльнoгo oкнa */
    position:fixed; /* всегдa перекрывaет весь сaйт */
    background-color:#000; /* чернaя */
    opacity:0.8; /* нo немнoгo прoзрaчнa */
    -moz-opacity:0.8; /* фикс прозрачности для старых браузеров */
    filter:alpha(opacity=80);
    width:100%; 
    height:100%; /* рaзмерoм вo весь экрaн */
    top:0; /* сверху и слевa 0, oбязaтельные свoйствa! */
    left:0;
    cursor:pointer;
    display:none; /* в oбычнoм сoстoянии её нет) */
}
    
table.menu    { border-left:0px solid; border-spacing: 15px;     border-collapse: separate; }
tr.menu-row   { height:30px; }
td.menu-point { background:DarkSlateGrey; color:#FFFFFF; font-weight:bold; text-align:center; padding:10px; border:0px}
a.menu-point  {  color:#FFFFFF; font-weight:bold;  font-style: normal; }
a.menu-point:hover {  color:#FFFFFF; font-weight:bold; }    

</style>

<script type="text/javascript">
function submitMainForm()
{
    window.parent.chngLinked(<?= $zakazRecord['id'] ?>, 1);
    window.parent.closeZakazFrame();
}

function cancelMainForm()
{
    window.parent.closeZakazFrame();
}


function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["Mainform"]["marketzakazform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone;   
}

function doMail()
{      
  win=window.open("index.php?r=site/mail&orgId=<?= Html::encode($model->id)?>&email="+document.forms["Mainform"]["marketzakazform-contactemail"].value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
}

function doCall()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["Mainform"]["marketzakazform-contactphone"].value,'doCall','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function sendKP()
{      
  w=window.open("index.php?r=market/send-zakaz&zakazId=<?= Html::encode($zakazRecord['id']) ?>&email="+document.forms["Mainform"]["marketzakazform-contactemail"].value,'send','toolbar=no,scrollbars=yes,resizable=yes,top=95,left=550,width=1150,height=700');     
  window.w.focus();
  //window.location.href="index.php?r=market/send-zakaz&zakazId=<?= Html::encode($zakazRecord['id']) ?>&email="+document.forms["Mainform"]["marketzakazform-contactemail"].value;
}


function showAdd()
{
    if (document.getElementById("addRequest").style.visible == "hidden")
    { document.getElementById("addRequest").style.visible = "visible"; }
    else document.getElementById("addRequest").style.visible = "hidden";
}

function showDialog(id, fnum, rowid)
{
//alert(id);
   
    switch(fnum)
    {
        case 1:
            document.getElementById('dialogTitle').innerHTML= "Товар";
            break;
        case 2:
            document.getElementById('dialogTitle').innerHTML= "Спецификация";
            break;
        case 3:
            document.getElementById('dialogTitle').innerHTML= "Ед.изм";
            break;
        case 4:
            document.getElementById('dialogTitle').innerHTML= "Цена";
            break;
        case 5:
            document.getElementById('dialogTitle').innerHTML= "Количество";
            break;
        case 6:
            document.getElementById('dialogTitle').innerHTML= "Доп. условия";
            break;
        case 7:
            document.getElementById('dialogTitle').innerHTML= "Доставка";
            break;
        default:
            document.getElementById('dialogTitle').innerHTML= "Заказ";            
    }
    document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value=document.getElementById(id).innerHTML;
    if (document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value == "- ")
        { document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value ="";}
    document.forms["editZakazForm"]["actionType"].value=fnum;
    document.forms["editZakazForm"]["id"].value=rowid;

//Показ диалога
        $('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
             function(){ // после выполнения предъидущей анимации
                $('#edit_zakaz_form') 
                    .css('display', 'block') // убираем у модального окна display: none;
                    .animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
        document.forms["editZakazForm"]["edit_zakaz_form-proposal"].focus();    
        });
    
 
    //document.getElementById('edit_zakaz_form-proposal').focus();        
}

</script>

<script type="text/javascript">
$(document).ready(
function() 
{ // вся магия после загрузки страницы
    $('a#add_zakaz').click( 
    function(event)
    { // ловим клик по ссылки с id="go"
        event.preventDefault(); // выключаем стандартную роль элемента
        $('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
             function(){ // после выполнения предъидущей анимации
                $('#add_zakaz_form') 
                    .css('display', 'block') // убираем у модального окна display: none;
                    .animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
        document.getElementById('marketzakazform-initialzakaz').focus();    
        });        
    }
    );
    
    $('a#edit_zakaz').click( 
    function(event)
    { // ловим клик по ссылки с id="go"
        event.preventDefault(); // выключаем стандартную роль элемента
        $('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
             function(){ // после выполнения предъидущей анимации
                $('#edit_zakaz_form') 
                    .css('display', 'block') // убираем у модального окна display: none;
                    .animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
        });
    }
    );

    /* Закрытие модального окна, тут делаем то же самое но в обратном порядке */
    $('#add_zakaz_close, #overlay').click( 
    function()
    { // ловим клик по крестику или подложке
        $('#add_zakaz_form')
            .animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
                function(){ // после анимации
                    $(this).css('display', 'none'); // делаем ему display: none;
                    $('#overlay').fadeOut(400); // скрываем подложку
                }
            );
    }
    );
    
    $('#edit_zakaz_close, #overlay').click( 
    function()
    { // ловим клик по крестику или подложке
        $('#edit_zakaz_form')
            .animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
                function(){ // после анимации
                    $(this).css('display', 'none'); // делаем ему display: none;
                    $('#overlay').fadeOut(400); // скрываем подложку
                }
            );
    }
    );
}

);
</script>
<div style='width:940px;'>
    <div class="page-header">     
    Наименование компании <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($model->id)?>"><?= Html::encode($zakazRecord['title'])?></a></strong></u>
    <div class="page-title"><?= Html::encode($this->title)?></div>        
    <div style="font-size:10px"><?= Html::encode($zakazRecord['shortComment'])?></div>
    </div>    
    <div style=" padding: 5px; text-align:right;"> Заявка номер  <?= Html::encode($zakazRecord['id'])?>  от <?= Html::encode($zakazRecord['formDate'])?>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a  class='btn btn-primary button' style="width: 125px;" href="#" id="add_zakaz">Добавить товар</a> 
    <a  class='btn btn-primary button' style="width: 125px;" href="index.php?r=store/market-price&noframe=1&zakazId=<?=$zakazRecord['id']?>&orgId=<?=$model->id?>" >Прайс</a>
    <a  class='btn btn-primary button' style="width: 125px;" href="index.php?r=store/google-price&noframe=1&zakazId=<?=$zakazRecord['id']?>&orgId=<?=$model->id?>" >ТП</a>
    <!--<a  class='btn btn-primary button' href="#" onclick="openWin('market/market-good-request-create&zakazId=<?=$zakazRecord['id']?>','purchWin');">Запрос цены</a>-->
    <!--<input disabled class="btn btn-primary button" style="width: 150px; " type="button" value="Выслать КП" onclick="javascript:sendKP();"/>-->        
    </div>
    
<div style='height:200px; overflow: auto;'>    
    <?php    
    /* <a href='#' id='edit_zakaz'> </a>*/
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getZakazDetailProvider(),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
                'attribute' => 'initialZakaz',
                'label'     => 'Начальный заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if (empty(trim($model['initialZakaz']))){$val="-";}
                    else {$val=$model['initialZakaz'];}
                    if ($model['isActive'] == 1) 
                    {
                    return $val;
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";
                    
                },
            ],        

            [
                'attribute' => 'good',
                'label'     => 'Предложенный товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "good_".$model['id'];
                    if (empty(trim($model['good']))){$val="-";}
                                         else {$val=$model['good'];}
                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 1, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";
                    
                },
            ],        

            [
                'attribute' => 'spec',
                'label'     => 'Спецификация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "spec_".$model['id'];
                    if (empty(trim($model['spec']))){$val="-";}
                                         else {$val=$model['spec'];}
                                         
                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 2, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";                                                            
                },
            ],        

            [
                'attribute' => 'count',
                'label'     => 'К-во',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "count_".$model['id'];
                    if (empty($model['count'])){$val="-";}
                                         else {$val=$model['count'];}

                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 5, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";                                                            

                },
            ],        
            
            [
                'attribute' => 'ed',
                'label'     => 'Ед.изм',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "ed_".$model['id'];
                    if (empty(trim($model['ed']))){$val="-";}
                                         else {$val=$model['ed'];}
                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 3, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";                                                            
                },
            ],        
            
            [
                'attribute' => 'value',
                'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "value_".$model['id'];
                    if (empty($model['value'])){$val="-";}
                                         else {$val=$model['value'];}
                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 4, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";                                                            
                },
            ],        
                            
      /*      [
                'attribute' => 'dopRequest',
                'label'     => 'Доп. условия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "dopRequest_".$model['id'];
                    if (empty(trim($model['dopRequest']))){$val="-";}
                                         else {$val=$model['dopRequest'];}
                    if ($model['isActive'] == 1) 
                    {
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 6, ".$model['id'].");\">".$val." </div>";
                    }
                    return "<div class='nonActiveCell'>".$val." </div>";                                                            
                },
            ],     */   

              [
                'attribute' => 'id',
                'label'     => 'Запрос',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  
                $strSql= "select id, zaprosType,relizeValue FROM {{%purchase_zakaz}} WHERE refZakazContent =:refZakazContent ORDER BY zaprosType ASC";
                $inPurch = Yii::$app->db->createCommand($strSql, [':refZakazContent' => $model['id'],])->queryAll();
                 
                if (count($inPurch) > 0)  
                { 
                   if ($inPurch[0]['zaprosType'] == 0) return "В закупке";
                   
                    if ($inPurch[0]['zaprosType'] == 1)
                    {
                       if ($inPurch[0]['relizeValue'] >0 ) return number_format($inPurch[0]['relizeValue'],2,'.','');
                       return "Запрос цены";
                    }
                }   
                    
                $action = "openWin('store/purchase-create-from-client-zakaz&contentid=".$model['id']."','purchWin');";                        
                return "<a href='#' onclick=\"".$action."\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></a>";
                    
//                    return "<a href='index.php?r=market/market-zakaz-remove&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."' style='font-color:Crimson'><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },                
            ],        

    
            [
                'attribute' => 'id',
                'label'     => 'Актуален',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                if ($model['isActive'] == 1){
                        return "<span class='label label-success'>Yes</span> <a href='index.php?r=market/market-zakaz-delete&noframe=1&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'>Убрать</a>";
                    }
                        return "<span class='label label-danger'>No</span> <a href='index.php?r=market/market-zakaz-reverse&noframe=1&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'>Вернуть</a>";
                    
                },                
            ],        

            [
                'attribute' => 'id',
                'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=market/market-zakaz-remove&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."' style='color:Crimson'><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },                
            ],        
            
        ],
    ]
    );
    ?>
</div>    

</div>    
<!---<a  class='btn btn-primary button' target="_blank" href="index.php?r=store/reserved&zakazId=<?=$zakazRecord['id']?>">Резерв</a>-->
 <hr noshade size='5'>
 <!--- Регистрация контакта старт--->    
  <?php $form = ActiveForm::begin(['id' => 'Mainform',
        'layout'=>'horizontal',
        'options' => ['class' => 'form-inline'],
        'fieldConfig' => [
             'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            'horizontalCssClasses' => [
                'label' =>   'col-sm-4',
                'offset' =>  'col-sm-offset-4',
                'wrapper' => 'col-sm-8',
                'error' => '',
                'hint' => '',
            ],
        ],
  ]); ?>
  
  
<div class='row'> 
  
  <div class='col-sm-7'>  
    <!-- Выводим последний контакт -->
    <div style='width:500px;  height:125px; background-color: BlanchedAlmond; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%; padding:5px;'>
    <?php

     $contactsDetail=$model->getContactDetail();
     //print_r ($contactsDetail);
     $cnt = count ($contactsDetail);
     if ($cnt> 1) $cnt = 1;
     for ($i=0;$i<$cnt;$i++)
     {    
        echo "<div class='contact_title'> <b>";
        echo date("d-m-Y",strtotime( $contactsDetail[$i]['contactDate']))." </b> ";
        echo $contactsDetail[$i]['contactFIO']."  ".$contactsDetail[$i]['phone']."</div>\n";
        if (mb_strlen($contactsDetail[$i]['note'])> 260){echo "<div>".Html::encode(mb_substr($contactsDetail[$i]['note'],0,260, 'utf-8'))."...</div>\n";}               
        else {echo "<div>".$contactsDetail[$i]['note']."</div>\n";}               
     }
     ?>  
     </div>
 </div>  
   
  <!-- Статусы -->
  <div class='col-sm-5'>    
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 3, 'uncheck' => null]) ?><span style='position:relative;left:20px;'>В работе</span> </nobr><br>      
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 1, 'uncheck' => null]) ?><span style='position:relative;left:20px;'>Заказ согласован </span><br>
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 2, 'uncheck' => null]) ?><span style='position:relative;left:20px;'>Отказ</span></nobr><br>
  </div>  
  
  
 </div>

 <hr>
 <div style="position:relative; top:0px; display:inline-block; float:right; margin-right:0px">
         <?php 
                if ($model->status==3)                
                //echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform'])  ;
                echo "<input type='button' class='btn btn-primary' href='#' onclick='javascript: submitMainForm();' style ='background-color: ForestGreen;' value ='Сохранить' />";
                else echo "<div style='padding: 5px;margin-left:10px; display:inline; border-style: double;'> Работа с заявкой прекращена. </div> &nbsp;";
        ?>
 

   <a class='btn btn-primary' href="#" onclick="javascript: if (confirm('Не сохраненные изменения будут потеряны! Выйти?'))
   {cancelMainForm();} "> Выйти </a>
   
 </div>
  
      

<!--- Контакт финиш--->  

   <?= $form->field($model, 'id')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>    
   <?php ActiveForm::end(); ?>
   
   
</div>   
<!--- ******************************************************  --->  
<!--- ******************************************************  --->     
<!--- Форма добавления ----->    
    <div id="add_zakaz_form">
    <span id="add_zakaz_close">X</span>
    <form action="index.php" method="GET">    
    <input type="hidden" name="orgId" value="<?=$record->id?>" />
        <input type="hidden" name="zakazId" value="<?=$zakazRecord['id']?>" />
        <input type="hidden" name="r" value="market/market-zakaz" />
        <input type="hidden" name="action" value="addZakaz" />
        <br>
            <div class="form-header"> Добавить в заявку </div>                                    
            <input type="text" id="marketzakazform-initialzakaz" class="form-control" name="initialZakaz" value="">    
            <p style="text-align: right; padding-bottom: 10px;">
            <br>
            
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actAddZakaz']) ?>
            </p>
       </form>
  </div>
  
  <!-------------->

   <!--- Форма Редактирования ----->    
  <div id="edit_zakaz_form">
    <span id="edit_zakaz_close">X</span>         
        <form action="index.php" method="GET" id="editZakazForm">
        <input type="hidden" name="orgId" value="<?=$record->id?>" />
        <input type="hidden" name="zakazId" value="<?=$zakazRecord['id']?>" />
        <input type="hidden" name="action" value="editZakaz" />
        <input type="hidden" name="r" value="market/market-zakaz" />
        <input type="hidden" name="actionType" value="" />
        <input type="hidden" name="id" value="" />
        <br>
            <div id="dialogTitle" class="form-header"> Редактировать заявку </div>                                
            <input type="text" id="edit_zakaz_form-proposal" class="form-control" name="proposal" value="">    
            <p style="text-align: right; padding-bottom: 10px;">
            <br>
            
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actAddZakaz']) ;   ?>
            </p>
        </form>
  </div>
  <div id="overlay"></div>
  <!-------------->
