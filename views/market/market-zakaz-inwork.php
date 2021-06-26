<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Заявки в работе';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>

<script>
function openWin(url)
{
  wid=window.open("index.php?r="+url,'zakazwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}

</script>  

<?php
echo \yii\grid\GridView::widget(
    [
			
        'dataProvider' => $model->getInWorkProvider(),
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		   [
                'attribute' => 'title',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['orgId']."'>".$model['title']."</a>";
                },
            ],			
			
			'zakazId:raw:Номер заказа',            
            [
                'attribute' => 'formDate',
				'label'     => 'Дата заказа',
                'format' => ['datetime', 'php:d-m-Y'],
            ],			

			[
                'attribute' => 'isFormed',
				'label'     => 'Согласована',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isFormed'] >0 )    { $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		

			[
                'attribute' => 'isGoodReserved',
				'label'     => 'Товар в резерве',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isFormed'] == 0) return "&nbsp;"; 
					if ($model['isGoodReserved'] >0 ){ $isFlg = true;}
					else                             { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
			
     		[
                'attribute' => 'id',
				'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isGoodReserved'] ==0)
					{
					return "<input class='btn btn-primary' style='width: 75px;'  type='button' value='Заявка'  onclick='javascript:openWin(\"market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."\")'/>";						
					//return "<a class='btn btn-primary' href='index.php?r=market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'>Заявка</a>";
					}					
                    return "&nbsp;";
                },
            ],		
			
     		[
                'attribute' => 'id',
				'label'     => 'Резерв',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if ($model['isGoodReserved'] ==0  && $model['isFormed'] == 1)
					{
						return "<a class='btn btn-primary' href='index.php?r=market/market-reserve-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'>Резерв</a>";
					}					
					return "&nbsp;";
                },
            ],		
			
     		[
                'attribute' => 'id',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if ($model['isGoodReserved'] >0 )
					{
					return "<a class='btn btn-primary' href='index.php?r=market/market-reg-schet&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'>Счёт</a>";
					}					
					return "&nbsp;";
                },
            ],		
			
			/*[
                'attribute' => 'Отказаться',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=market/market-zakaz-reject&zakazId=".$model['zakazId']."'>Отказаться</a>";
                },
            ],*/		
			
        ],
    ]
);
 
?>
   
<script type="text/javascript">
window.opener.location.reload(false); 
</script>
