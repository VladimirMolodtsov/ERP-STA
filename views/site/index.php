<?php
use app\assets\AppAsset;
use yii\helpers\Html;
/* @var $this yii\web\View */
/*
0x0001 1  - Оператор по работе с исходными данными
0x0002 2  - Оператор холодных звонков  
0x0004 4  -     Менеджер активных продаж
0x0008 8  - Менеджер по кадрам
*/
$this->title = 'Автоматизация процесса обзвона клиентов';
?>
<style>
table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 15px;
}
.razdel{
    padding: 25px;
}

 .button_menu{
    padding: 15px;      
 }

 .part-header{
    padding: 10px;      
     color: white;
     text-align: left;
     background-color: blue;
     font-size: 14pt;
 }
 
 .rlead{
    padding: 10px;      
     color: black;
     text-align: right;     
     font-size: 14pt;
 }

  
 .clead{
    padding: 10px;      
     color: black;
     text-align: center;     
     font-size: 16pt;
 }
 
.disable{     
  background-color: LightGray;     
  width:250px;
}

.disable:hover{     
  background-color: LightGray;       
}
.enable{       
  width:250px;
}

 
</style>

    
<?php if (Yii::$app->user->isGuest){  ?>
<div class="jumbotron">          
        <p class="lead"> Для начала работы авторизуйтесь</p>
        <p><a class="btn btn-lg btn-success" href="index.php?r=site/login">Войти в систему</a></p>
</div>        
<?php } ?>
     
      
      
      
<?php if (Yii::$app->user->isGuest == false){  
      $curUser=Yii::$app->user->identity;
 ?>

<div class="body-content">
        <div class="clead"> Текущий пользователь: <b><?= Html::encode($curUser->userFIO) ?></b></div>

    <table border="0" width = 90%>
     <tr>     
          <td>
               <?php if 
               ( ($curUser->roleFlg & 0x0002) ||  ($curUser->roleFlg & 0x0004) || ($curUser->roleFlg & 0x0080)){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=market/market-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Отдел продаж" onclick="<?=$onclick?>"/>
          </td>               

          <td>
               <?php if 
               ($curUser->roleFlg & 0x0001){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=site/marketing-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Маркетинг" onclick="<?=$onclick?>"/>          
         </td>               
    
          <td>
               <?php if 
               ($curUser->roleFlg & 0x0100){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=head/market-head'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Коммерческий директор" onclick="<?=$onclick?>"/>          
         </td>               
       
    </tr>     

     <tr>     

     
          
          <td>
               <?php if 
               ($curUser->roleFlg & 0x0010){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=store/sclad-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Отдел снабжения" onclick="<?=$onclick?>"/>
         </td>               

       
       <td>
               <?php if 
               ($curUser->roleFlg & 0x0200){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=store/product-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Начальник производства" onclick="<?=$onclick?>"/>
         </td>               

     
          <td>
          </td>               
         
    </tr>     

         
     <tr>     
          <td>
               <?php if 
               ($curUser->roleFlg &  0x0008){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=site/personal-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Менеджер по кадрам" onclick="<?=$onclick?>"/>

          </td>               

          <td>
               <?php if 
               ($curUser->roleFlg & 0x0020){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=site/head-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Управление" onclick="<?=$onclick?>"/>
          </td>               

    </tr>     

     <tr>     
        <td colspan='3' align='center'><b>Бухгалтерия</b></td>
     </tr>     
            
     <tr>     
     
         <td>
               <?php if 
               ($curUser->roleFlg & 0x0040){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=fin/fin-start'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Финансы" onclick="<?=$onclick?>"/>
         </td>               

         <td>
               <?php if 
               ($curUser->roleFlg & 0x0400){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/op-day-detail'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Оператор" onclick="<?=$onclick?>"/>
         </td>               

         <td>
               <?php if 
               ($curUser->roleFlg & 0x0040){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/buh/buh-day-detail'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Ст. бухгалтер" onclick="<?=$onclick?>"/>
         </td>               
    </tr>     
    </table>     
    
    <table border="0" width = 85%>
    <tr>     
        <td colspan='4' align='center'><a href='index.php?r=bank/operator/doc-list&flt=all'><b>Входящая документация</b></a></td>
    </tr>     
            
     <tr>          
     
         <td>
            <b> В оплату: </b>  
         </td>               

         <td>
               <?php if
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=buh'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Бухгалтерия" onclick="<?=$onclick?>"/>
         </td>               

         <td>
               <?php if 
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=office'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Офис" onclick="<?=$onclick?>"/>
         </td>               

         <td>
               <?php if 
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=ware'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Закупки" onclick="<?=$onclick?>"/>
         </td>               
    </tr>     
    
     <tr>          
     
         <td>
           <b>  Передать: </b>
        </td>               

         <td>
               <?php if
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=buhdoc'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Бухгалтерия" onclick="<?=$onclick?>"/>
         </td>               

         <td>
               <?php if
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=officedoc'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Офис" onclick="<?=$onclick?>"/>
         </td>               
         
         <td>
               <?php if
               ($curUser->roleFlg > 0){ $style= "enable"; $onclick= "javascript:window.location='index.php?r=bank/operator/doc-list&flt=waredoc'";} 
                                      else {$style= "disable"; $onclick= "";} 
               ?>
               <input class="btn btn-primary <?=$style?>" type="button" value="Закупки" onclick="<?=$onclick?>"/>
         </td>               
         
    </tr>     
    
     
    </table>     
 
</div>
<?php } ?>

