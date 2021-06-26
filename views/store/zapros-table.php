<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;
$this->title = 'Запросы цены';

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

.btn-small {    
    padding: 2px;     
    font-size: 10pt;    
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

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.table-local
{
    padding: 2px;
    font-size: 10pt;
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

th, td {
    padding: 5px;
}
</style>

<script type="text/javascript">
</script>    


    <h3><?= Html::encode($this->title) ?></h3>
    
<?php $leafValue=$model->getLeafValue(); ?>           
<div class="row">  

   <div class="col-md-8" >

   <table border='0' width='100%' > 
        <tr>        
            <td>
            <!--style='background:Brown; color:White;' -->
            <a  class='btn btn-primary leaf  <?PHP if ($model->mode==1) echo "leaf-selected"; ?>' 
                href='index.php?r=store/zapros-table&mode=1#detail_list'>
                <div class='leaf-txt'> Все запросы <br> в работе: </div>
                <div class='leaf-val'><?= ($leafValue['orders']) ?> </div> </a>
            </td>
                
  </table>      

   </div>   

   <div class="col-md-3" style='margin-top:20px;'>
        <input class="btn btn-primary"  style="width:200px;" type="button" value="Новый запрос" onclick="javascript:openWin('store/purchase-zakaz&type=1&noframe=1','storeWin');"/>       
    </div>   

    <div class="col-md-1" >
    <!--
    <a href='#' onclick='openWin("help/purchase-table","helpWin");'><span class='glyphicon glyphicon-question-sign' aria-hidden='true'></span></a>
   --></div>   

</div>     
<br>&nbsp;<br> 

  

<a name='detail_list'> </a>
<?php  
echo \yii\grid\GridView::widget(
    [
                    
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            
            [
                'attribute' => 'creationDate',
                'label'     => 'Дата',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                 return "№ ".$model['zaprosId']."<br> от ".$model['creationDate']; 
                }
            ],        
        
            [
                'attribute' => 'wareTitle',
                'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                /*Нет запроса - это только товар*/
                if (empty ($model['variantWareTitle']))  $showTitle = $model['wareTitle'];
                else $showTitle = "<b>".$model['variantWareTitle']."</b><br>(".mb_substr($model['wareTitle'],0,25,'utf-8')."..)"; 
                if (empty($model['zaprosId']))  $ret = $showTitle;                           
                  
                if (!empty($model['zaprosId']))
                {
                   $curUser=Yii::$app->user->identity;                       
                   if ($curUser->roleFlg & 0x0020|0x0100) 
                       $ret = "<a href='#' onclick='openWin(\"\store/head-purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$showTitle ."</a>";     
                   else
                       $ret = "<a href='#' onclick='openWin(\"\store/purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$showTitle."</a>";     
                }
                
                  return "<div style='width:300px;'>".$ret."</div>";
                }
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
                'attribute' => 'refZakaz',
                'label'     => 'Заказ клиента',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                $strSql = 'SELECT formDate, isFormed, isActive, userFIO, title, 
                {{%schet}}.schetNum, {{%schet}}.schetDate, ifnull({{%schet}}.isReject,0) as schetReject 
                FROM ({{%zakaz}},{{%user}},{{%orglist}}) 
                LEFT JOIN {{%schet}} ON {{%schet}}.refZakaz = {{%zakaz}}.id  where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
                if(empty($dataList)) return "";                 
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret .= $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                if ( ($dataList[0]['isFormed'] == 0 && $dataList[0]['isActive'] == 0) || $dataList[0]['schetReject']) $ret = "<s>".$ret."</s>"; 
                
                return $ret;                
                }
            ],        

            [
                'attribute' => 'zaprosStatus',
                'label'     => 'Статус',
                'format' => 'raw',
                'filter'=>array("0"=>"Все","1"=>"На одобрении" ,"2"=>"Одобрено","3"=>"Выполнено","4"=>"На доработке",),
                'value' => function ($model, $key, $index, $column) {    
                switch ($model['zaprosStatus'])
                {
                   case 1:
                   return "Одобрено";

                   break;
                   case 2:
                   return "Выполнено";
                   
                   break;
                   case 4:
                    return "На доработке";
                   break;
                    
                }
                  return "<div style='width:100px;height: 100%;display: block; background: yellow;'>Ожидает <br>одобрения</div>";
                }
            ],

            
            
           /*[
                'attribute' => 'zaprosDate',
                'label'     => 'Запрос цены/<br>Цена',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                if (empty($model['zaprosId'])) 
                {
                    
                    $ret ="<a style='color:Green;' href='#' onclick=\"openWin('store/purchase-create-from-request&id=".$model['requestID']."','storeWin'); openSwitchWin('site/success');\"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                    $ret .= "&nbsp;&nbsp;&nbsp;&nbsp;<a style='color:Crimson;' href='#' onclick=\"openSwitchWin('store/rm-from-request&id=".$model['requestID']."'); \"> <span class='glyphicon glyphicon-remove' aria-hidden='true'></span> </a>";
                      return $ret;
                }                      
                $add ="";    

                
                if (!empty($model['variantSchetNum'])) 
                {
                $varSchet = $model['variantSchetNum']." от ".date("d.m", strtotime($model['variantSchetDate'])); 
                $add.=  "<div  style='padding:2px;width:80px;'>".number_format($model['variantValue'],2,'.','&nbsp;')." руб.<br>".$varSchet."</div>";
                }
                elseif(!empty($model['variantValue'])) $add.= number_format($model['variantValue'],2,'.','&nbsp;');

                $bg = "";                
                if ( empty ($model['purchaseId']) ) {                    
                    $bg = "background:Blue; color:White; font-weight:bold; padding:2px;";
                }
                                
                if ($model['zaprosIsActive'] == 0) $add ="<br><font color='White'> Завершен</font>";        

                $curUser=Yii::$app->user->identity; 
                if ($curUser->roleFlg & 0x0020|0x0100)   
                    {
                    return "<div style='".$bg.";'><nobr><a style='".$bg.";' href='#' 
                    onclick='openWin(\"\store/head-purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>"
                    .$model['zaprosId']." от ".date('d.m', strtotime($model['creationDate']))."</a></nobr>".$add."</div>";     
                    }
                return "<div style='".$bg.";'><nobr><a style='".$bg.";' href='#' onclick='openWin(\"\store/purchase-zakaz&noframe=1&id=".$model['zaprosId']."\",\"storeWin\");'>".$model['zaprosId']." от ".date('d.m', strtotime($model['creationDate']))."</a></nobr>".$add."</div>";     
                }
            ],        */
       
            [
                'attribute' => 'variantValue',
                'label'     => 'Цена <br>закупки',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['variantValue'],2,'.','&nbsp;');
                }
                
            ],        

            [
                'attribute' => 'relizeValue',
                'label'     => 'Цена <br>реализации',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['relizeValue'],2,'.','&nbsp;');
                }

            ],        
       
            [
                'attribute' => 'isFinishedPurchase',
                'label'     => 'Актив.',
                'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['zaprosIsActive'] >0 ){ $isFlg = true;}
                    else                      { $isFlg = false;}
                    return  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
                        );

                }
            ],        
     
        ],
    ]
    );

     
 

?>   
<br>   
<div class="row">  
   
</div>      
    

