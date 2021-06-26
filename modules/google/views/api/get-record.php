<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Ip- телефония';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<h3> Список сохраненных переговоров </h3>

<?PHP 
if ($answerObject->status != 'success') echo "Запрашиваемая запись не сохранена или была удалена";
else
{
echo "<ul>";    
  $N = count($answerObject->links);
     for ($i=0; $i<$N; $i++ )
     {
        $parse=explode("=",$answerObject->links[$i]);    
        $pN=count($parse);
        if ($pN == 0) continue;        
        echo "<li> <a href='".$answerObject->links[$i]."'>".$parse[$pN-1]."</a>";        
     }
     
echo "</ul>";     

echo "<p>Cсылки валидны до ".$answerObject->lifetime_till."</p>";
}
?>
<pre>
<?php //print_r($answerObject); ?>
</pre>

