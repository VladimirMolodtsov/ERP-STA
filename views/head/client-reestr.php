<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;

$this->title = 'Реестр клиентов';
//if (Yii::$app->user->isGuest == true){ return;}
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>


<script type="text/javascript">


</script> 
 
<style>



</style>


<?php 
echo $model->printSavedClientReestr($provider, $model);

?>

<?php
Modal::begin([
    'id' =>'catListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='catListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=head/org-cfg-category&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>
