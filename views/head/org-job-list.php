<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\bootstrap\Collapse;
use yii\bootstrap\ActiveForm;
use yii\db\Query;


$this->title = 'Организации в списке';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
    
 ?>


<script type="text/javascript">


function switchInJobList(refOrg)
{
    var curOrgJobList= <?=$model->curOrgJobList?>;
    $('#dataType').val('switchJobList');
    $('#recordId').val(curOrgJobList);
    $('#dataVal').val(refOrg);
    
    saveData();
}

function saveData()
{
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=head/save-org-job-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload==true)document.location.reload(true); 
            else showRes(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function showRes(res)
{
 if (res.dataType == 'switchJobList'){
  var idx = res.dataVal+'switchJobList';
  if (res.val == 1)
      document.getElementById(idx).style.backgroundColor ='Green';
  else    
       document.getElementById(idx).style.backgroundColor ='LightGray';
 }
}


</script> 
 
<style>



</style>
<?php 

  echo  GridView::widget(
    [

        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],

        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => false,
        'panel' => ['type'=>'success',],

        'responsive'=>true,
        'hover'=>true,

        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [


                    [
                'attribute' => '-',
                'label' => 'В списке',
                'format' => 'raw',
                'contentOptions' => ['width' => '50px'],
                 'value' => function ($model, $key, $index, $column) {	                    
                    $action = "switchInJobList(".$model['orgRef'].")";
                    $id = $model['orgRef'].'switchJobList';
                    
                     //if ($n ==0) $style='background-color:LightGray';
                     //else  
                     $style='background-color:Green';

                    
                    return \yii\helpers\Html::tag( 'div','',
                    [
                     'id' => $id,
                     'class'   => 'btn btn-small',
                     'onclick' => $action,
                     'style'   => $style,
                   ])."&nbsp;";
                },

             ],   
            
            [
                'attribute' => 'title',
                'label' => 'Контрагент',
                 'contentOptions' => ['width' => '150px'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgRef']."\", \"childwin\")' >".$model['title']."</a>";
                },

             ],   

            [
                'attribute' => '-',
                'label' => 'Сделок',
                 'contentOptions' => ['width' => '50px', 'style' => 'font-size:10pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    $strSql  = "SELECT count(DISTINCT(id)) from {{%schet}}";
                    $strSql .= "where refOrg = :ref_org ";
                    $sdelkaN = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryScalar();
                    return $sdelkaN;
                },

             ],


            [
                'attribute' => '-',
                'label' => 'Товар (Всего)',
                'contentOptions' => ['width' => '550px'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {	                    

                    $query  = new Query();
    
                    $query->select([
                        '{{%supply}}.id',
                        'supplyGood as wareTitle',
                        'supplyEd',
                        'sum(supplySumm) as wareSumAvg',
                        'sum(supplyCount) as wareCountAvg',
                       ])
                ->from("{{%supply}}")		
                ->groupBy("wareTitle, supplyEd")
                ->orderBy("sum(supplySumm) DESC")
                ->limit(3)
                ->andWhere(['=',"refOrg", $model['orgRef']])
                ;
                 
                 $list=$query->createCommand()->queryAll();
                 $N=count($list);
                 $ret="<div style='margin:2px;'><table class='table table-stripped'>";
                 for($i=0;$i<$N; $i++)
                 {
                  $ret.="<tr>";   
                  $ret.="<td>".$list[$i]['wareTitle']."</div>";   
                  $ret.="<td>".number_format($list[$i]['wareSumAvg'],0,".","&nbsp;")." руб</div>";
                  $ret.="<td>".number_format($list[$i]['wareCountAvg'],0,".","&nbsp;")." ".$list[$i]['supplyEd']."</div>";
                  $ret.="</tr>";   
                 }                 
                 $ret.="</table></div>";
                 return $ret;
                },
                
             ],   
             
             
             
            [
                'attribute' => 'shortComment',
                'label' => 'Примечание',
                'format' => 'raw',
                'contentOptions' => ['width' => '250px'],
            ],   

            [
                'attribute' => '-',
                'label' => 'Контакт',
                 'contentOptions' => ['width' => '100px', 'style' => 'font-size:8pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    $strSql  = "SELECT MAX(contactDate) from {{%contact}}";
                    $strSql .= "where ref_org = :ref_org";
                    $lastContact = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryScalar();

                    if(!empty($lastContact)) return date("d.m.Y",strtotime($lastContact));
                },

             ],

            [
                'attribute' => '-',
                'label' => 'Сделка',
                 'contentOptions' => ['width' => '100px', 'style' => 'font-size:8pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    /*$strSql  = "SELECT MAX(schetDate) from {{%schet}}";
                    $strSql .= "where refOrg = :ref_org";
                    $lastSchet = strtotime(Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryScalar());*/

                    $strSql  = "SELECT MAX(formDate) from {{%zakaz}}";
                    $strSql .= "where refOrg = :ref_org";
                    $lastZakaz = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryScalar();
                    
                    if(!empty($lastZakaz)) return date("d.m.Y",strtotime($lastZakaz));
                },

             ],                         
             
            [
                'attribute' => '-',
                'label' => 'План',
                 'contentOptions' => ['width' => '100px', 'style' => 'font-size:8pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    $strSql  = "SELECT MAX(event_date) from {{%calendar}}";
                    $strSql .= "where ref_org = :ref_org";
                    $lastEvent = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryScalar();
                    if(empty($lastEvent)) return;
                    $style = "";
                    $lastEvent = strtotime($lastEvent);
                    if (time()-$lastEvent > 5*24*3600) $style="color:Crimson";
                   
                    return \yii\helpers\Html::tag( 'div',date("d.m.Y",$lastEvent),
                    [                     
                     
                     'style'   => $style,
                   ]);
                    
                    
                    
                },

             ],     
                          
            [
                'attribute' => '-',
                'label' => 'Телефон',
                 'contentOptions' => ['width' => '100px', 'style' => 'font-size:8pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    $strSql  = "SELECT DISTINCT phone,phoneContactFIO, isDefault from {{%phones}}";
                    $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC LIMIT 1";
                    $phoneList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryAll();

                    if(!empty($phoneList)) return $phoneList[0]['phone'];
                },

             ],
                                       
            [
                'attribute' => '-',
                'label' => 'Email',
                 'contentOptions' => ['width' => '75px', 'style' => 'font-size:8pt'],
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column) {
                    $strSql  = "SELECT DISTINCT email,emailContactFIO, isDefault from {{%emaillist}}";
                    $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC LIMIT 1";
                    $mailList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['orgRef'],])->queryAll();

                    if (!empty($mailList)) return $mailList[0]['email'];
                },

             ],

        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=head/save-org-job-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal', ])->label(false);
echo $form->field($model, 'dataNote' )->textArea(['id' => 'dataNote', 'style' =>'display:none' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
