<?php
/* view форма нового заказа */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\grid\GridView;
use yii\bootstrap\Alert;
use yii\bootstrap\Collapse;

$this->title = 'Завершение заказа';

?>
<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>
<script>
</script>

<h3>Заказ сформирован</h3>

<p>Заказ сформирован, в ближайшее время с Вами свяжется менеджер для уточнения порядка исполнения заказа.</p>
<p>На указанный Вами почтовый адрес выслано коммерческое предложение.
Вы можете также <a target='_blank' href='index.php?r=sale/order/download-order&id=<?= $model->id ?>' > загрузить коммерческое предложение </a> непосредственно с сайта.
</p>

<?php

 echo Collapse::widget([
    'items' => [
        [
            'label' => 'Содержимое заказа' ,
            'content' => $html,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 

echo $model->errMsg;
?>

 

