<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Прайс';
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}

.suspicious {
  color:Crimson;  
}  


.notsuspicious {
  color:DarkGreen;  
}  
</style>
  
<script>

</script>

<table class='table table-bordered' border='2px;'>
<tr>
    <th> Вид товара </th>
    <th> Производитель </th>
    <th> Наименование </th>
    <th> до 100 </th>
    <th> до 400 </th>
    <th> 400-3000 </th>
    <th> 3000+ </th>
</tr>
<?php

$N = count ($dataArray);
for($i=0;$i<$N;$i++)
{

if (empty($dataArray[$i]['wareTitle'])) continue;

echo "<tr>\n";

echo "<td>";
    echo $dataArray[$i]['wareGrpTitle'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['wareProdTitle'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['wareTitle'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['v1'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['v2'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['v3'];
echo "</td>";

echo "<td>";
    echo $dataArray[$i]['v4'];
echo "</td>";


echo "</tr>\n";
}



?>
</table>

