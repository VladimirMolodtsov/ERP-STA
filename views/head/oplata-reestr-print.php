<?php
use yii\helpers\Html;
$this->title = 'Оплаты по счетам. Управление';
?>
<h3><?= Html::encode($this->title) ?></h3>
<?php  
   $sync = $model->getSyncValue();
   $utr = $model->getLastSuplierSchet ();      
 ?>  
<div>    
    <font size='-1'> Последняя синхронизация счета: <br><?= $sync['supplierSchet']."&nbsp;(".$utr.")" ?></font>
</div>    

<table class='table table-bordered'>
<thead>
 <tr>
        <th>Дата счета</th>
        <th>Номер счета</th>
        <th>Дата платежа</th>
        <th>Компания</th>
        <th>Сумма счета</th>        
        <th>Оплачено</th>
        <th>Остаток</th>
        <th>Сверка/Оплаты</th>
        <th>Оплатить</th>
        <th>Статья</th>
        <th>Комментарий</th>
        <th>Завершен</th>
</thead>    
<tbody>
<?php
       $strSql= "SELECT id FROM  {{%control_sverka_header}}  ORDER BY onDate DESC, id DESC LIMIT 1";
             $list  =Yii::$app->db->createCommand($strSql)->queryAll();   
             if (count($list) == 0 ) $headerRef = 0;
                                     else $headerRef=$list[0]['id'];
        if (empty ($headerRef)) $headerRef = 0;                    

$normTitleList = $model->getNormTitle();
$cN = count($dataList);
for ($i=0; $i<$cN ;$i++)
{
    echo " <tr>";
        if (empty($dataList[$i]['schNum'])) 
        {
            $dateVal = '&nbsp;';
            $numVal = '&nbsp;';                                      
            $orgTitle= '&nbsp;';                                      
            $summRequest= '&nbsp;';                                      
        }
        else 
        {
            $dateVal = date("d.m.Y", strtotime($dataList[$i]['schDate']));                    
            $numVal = $dataList[$i]['schNum'];  
            $orgTitle =$dataList[$i]['orgTitle'];  
            $summRequest =$dataList[$i]['summRequest'];  
            
        }
        echo "  <td>".$dateVal."</td>";
        echo "  <td>".$numVal."</td>";  

        $oplateTime =  strtotime($dataList[$i]['oplateDate']);
        if ($oplateTime > 0) 
        {
            $oplateDate = date("d.m.Y",$oplateTime);                
            if($oplateTime <= time()) $back = "	background-color: Silver ;"; 
        }
        else  $oplateDate = "  ";
        echo "  <td><div style='width:75px; font-weight: bold; ".$back."'".$oplateDate."</div>";  
        echo "  <td>".$orgTitle."</td>";  
        echo "  <td>".$summRequest."</td>";  

        $oplSumm = Yii::$app->db->createCommand("Select Sum(lnkOplate) from  {{%reestr_lnk}} 
                 where  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $dataList[$i]['id'],])->queryScalar();      

        echo "  <td>".number_format($oplSumm,0,'.','&nbsp;')."</td>";
        
        $val = $dataList[$i]['summRequest']-$oplSumm;
        echo "  <td>".number_format($val,0,'.','&nbsp;')."</td>";
        

        $strSql= "SELECT sum(balanceSum) FROM  {{%control_sverka_dolga}} as a, {{%control_sverka_dolga_use}} as b where 
                 a.useRef = b.id AND 
                 headerRef =:headerRef AND b.orgRef = :refOrg";
        $sverka=Yii::$app->db->createCommand($strSql, [':refOrg' => $dataList[$i]['refOrg'],':headerRef' => $headerRef])->queryScalar();   
        if ($sverka >= 0) $add="<font color='DarkGreen'>". number_format($sverka,0,'.',"&nbsp")."</font>";
                          else $add="<font color='Crimson'>". number_format($sverka,0,'.',"&nbsp")."</font>";

        if (empty($dataList[$i]['schNum'])) $opalte= '&nbsp;';                 
        else
        {
        $strSql= "SELECT sum(oplateSumm) FROM  {{%supplier_oplata}} where refOrg = :refOrg AND oplateDate >= :oplateDate";
        $opalte=Yii::$app->db->createCommand($strSql, [':refOrg' => $dataList[$i]['refOrg'], ':oplateDate' =>$dataList[$i]['schDate']])->queryScalar();   
        $opalte=number_format($opalte,0,'.',"&nbsp");
        }
        echo "   <td>".$add."<br>". $opalte."</td>";
                               
        echo "   <td>".number_format($dataList[$i]['summOplate'],0,'.','&nbsp;')."</td>";
        echo "   <td>".$dataList[$i]['normTitle']."</td>";                
        echo "   <td>".$dataList[$i]['note']."</td>";                
        if ($dataList[$i]['isActive'] == 0)  echo "   <td> </td>";                
                                        else echo "   <td> Не оплачен </td>";                
    echo " </tr>";
}

?>
</tbody>
</table>

            

