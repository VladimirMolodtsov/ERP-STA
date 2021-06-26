<hr>
<div class ='pbreak'></div>
<br>



<table border='1' width="100%">

<tr>
	<td><b>№:</b></td>
	<td><b>Артикул:</b></td>
	<td><b>Товар:</b></td>
  	<td><b>Количество:</b></td>
    <td><b>Ед. изм.</b></td>
</tr>	

 <?php		


$N= count($model->deliverContentList);
$sum=0;
for ($i=0;$i<$N;$i++)
{
echo "
<tr>
	<td>".($i+1)."</td>
	<td></td>
  	<td width='75%'>".$model->deliverContentList[$i]['requestGoodTitle']."</td>
    <td>".$model->deliverContentList[$i]['requestCount']."</td>
    <td>".$model->deliverContentList[$i]['requestMeasure']."</td>
    
</tr>	
";
$sum+=$model->deliverContentList[$i]['requestCount'];
}
?>    

</table>    

<div  style="text-align:right; padding-right:250px">
<b> Итого </b> &nbsp;&nbsp; <?= $sum ?>
</div>
<hr noshade style="height:3px;border:none;color:#333;background-color:Black;">  
  
<table border='0' width="75%">

<tr>
	<td width="5%" style='padding:10px;'>Отпустил</td>
	<td width="45%" style='padding:10px;'><div class='spacer'></div><hr noshade style="height:2px;border:none;color:#333;background-color:Black;">  </td>
	<td width="5%" style='padding:10px;'>Получил</td>
  	<td width="45%" style='padding:10px;'><div class='spacer'></div><hr noshade style="height:2px;border:none;color:#333;background-color:Black;">  </td>
</tr>	
</table>      
