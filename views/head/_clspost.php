<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\OrgList;
use app\models\AdressList;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Collapse;
use yii\db\Query;

use app\models\HeadOrgSearch;


    $strSql  = "SELECT DISTINCT {{%phones}}.phone, {{%contact}}.contactFIO, {{%contact}}.note, {{%contact}}.contactDate from {{%contact}} left join {{%phones}}";
    $strSql .= "ON {{%contact}}.ref_phone={{%phones}}.id ";
    $strSql .= "where ifnull(note,'') != '' and  {{%contact}}.ref_org = :ref_org ORDER BY contactDate DESC LIMIT 1";
    $contactList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();


    $strSql  = "SELECT DISTINCT phone,phoneContactFIO, isDefault from {{%phones}}";
    $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY isDefault DESC";
    $phoneList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();


    $strSql  = "SELECT DISTINCT email,emailContactFIO, isDefault from {{%emaillist}}";
    $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";
    $mailList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();


    $adrRecord= AdressList::findOne(
      [
      'isOfficial' => 1,
      'ref_org'    => $model['refOrg']
      ]);
      if (empty($adrRecord))
    $adrRecord= AdressList::find()
        ->where (['ref_org'    => $model['refOrg']])
        ->andWhere (["!=","ifnull(adress,'')",""])
        ->one();

      $gridmodel = new HeadOrgSearch();

    $strSql  = "SELECT count(DISTINCT(id)) from {{%schet}}";
    $strSql .= "where refOrg = :ref_org";
    $sdelkaN = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryScalar();

      
?>

<style>

.btn-small{
margin:2px;
padding:2px;
height:15px;
width:20px;
}

.contactNote{
font-size:11px;
}

.collapse-toggle
{
  font-size:12px;  
}
</style>


<div class="post">
    <table class='table'>
    <tr>
    <td width='200px'>
       <?php
       if(!empty($curOrgJobList))
       {
       $n=Yii::$app->db->createCommand("SELECT count(id) FROM {{%org_job_lnk}} where orgRef=:orgRef AND jobListRef=:jobListRef ", 
       [':orgRef' => $model['refOrg'], ':jobListRef' => $curOrgJobList])->queryScalar();         
       if ($n ==0) $style='background-color:LightGray';
             else  $style='background-color:Green';
       
       $action = "switchInJobList(".$model['refOrg'].")";
       $id = $model['refOrg'].'switchJobList';
       echo \yii\helpers\Html::tag( 'div','',
                   [
                     'id' => $id,
                     'class'   => 'btn btn-small',
                     'onclick' => $action,
                     'style'   => $style,
                   ])."&nbsp;";
                          
       }
       
       $action = "openWin('site/org-detail&orgId=".$model['refOrg']."','orgWin')";
       echo \yii\helpers\Html::tag( 'span',$model['orgTitle'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'style'   => "font-weight:bold;",
                   ]);
       ?>
    </td>

    <td width='150px'>
       <?php
        echo "<i><b>".$model['managerFIO']."</b></i>";


       echo \yii\helpers\Html::tag( 'span',"Сделок ".$sdelkaN,
                   [
//                     'class'   => 'clickable',
//                     'onclick' => $action,
                     'style'   => "margin-left:30px;",
                     'title'   => 'Число сделок с отгрузкой'
                   ]);


       ?>
    </td>

    <td width='200px' rowspan='3'>

        <i> <?php                
             if (!empty($contactList)) {
             echo "<b>".date("d.m.Y",strtotime($contactList[0]['contactDate']))."</b>";    
             echo "<div class='contactNote'>".$contactList[0]['note']."</div>";
             }
            ?>
       </i>

    </td>
   
   </tr>

    <tr>
    <td width='150px'>
    <div class='adress'><?php
    if(!empty($adrRecord)) echo Html::encode($adrRecord->adress)
    ?></div>
    </td>

    <td width='150px'>
       <?php
             if (!empty($contactList)) echo $contactList[0]['contactFIO'];
        else if (!empty($phoneList)) echo $phoneList[0]['phoneContactFIO'];
        if (!empty($phoneList)) echo "<div>".$phoneList[0]['phone']."</div>";
        if (!empty($mailList)) echo "<div>".$mailList[0]['email']."</div>";
       ?>
    </td>

    </tr>
   </table>
   
<?php
  
$refOrg = $model['refOrg'];
 Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $gridmodel->getWareOrgProvider($refOrg ),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:70px;'],
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareGrpTitle',
                'label'     => 'Вид',
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareTitle',
                'label'     => 'Товар',
                
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px;'],
                'format' => 'raw',
            ],
            [
                'attribute' => '',
                
                'label'     => "<span style='font-size:11px;'>Последняя <br>дата отгрузки</span>",
                'encodeLabel' => false,
                
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($refOrg) {
                $query = new Query();
                $d=$query->select(['MAX(supplyDate)', ])
                ->from("{{%supply}}")
                ->leftJoin("{{%ware_names}}","{{%ware_names}}.id={{%supply}}.wareNameRef")
                ->andWhere(['=','{{%ware_names}}.wareListRef', $model['id'] ])
                ->andWhere(['=','{{%supply}}.refOrg', $refOrg ])
                ->createCommand()->queryScalar();
                if (empty($d) ) return;

                    $action = "openSupplyList(".$refOrg.",".$model['id'].")";
                    $id = $model['id'].'showSupplyDate';
                return  \yii\helpers\Html::tag( 'div',date("d.m.Y", strtotime($d)),
                   [
                     'id' => $id,
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);

                }
                
            ],
            [
                'attribute' => '',
                'label'     => "<span style='font-size:11px;'>Общая сумма <br> отгрузки</span>",
                'encodeLabel' => false,
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($refOrg) {
                $query = new Query();
                $d=$query->select(['Sum(supplySumm)', ])
                ->from("{{%supply}}")
                ->leftJoin("{{%ware_names}}","{{%ware_names}}.id={{%supply}}.wareNameRef")
                ->andWhere(['=','{{%ware_names}}.wareListRef', $model['id'] ])
                ->andWhere(['=','{{%supply}}.refOrg', $refOrg ])
                ->createCommand()->queryScalar();
                
                if (empty($d) ) return;

                    $action = "openSupplyList(".$refOrg.",".$model['id'].")";
                    $id = $model['id'].'showSupplySumm';
                return  \yii\helpers\Html::tag( 'div',number_format($d,0,'.','&nbsp;'),
                   [
                     'id' => $id,
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);


                }
                
            ],
            [
                'attribute' => 'wareValMax',
                'label'     => "<span style='font-size:11px;'>Сумма счета <br>максимальная</span>",
                'encodeLabel' => false,
                'options' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return number_format($model['wareValMax'],0,'.','&nbsp;');
                }
            ],
            [
                'attribute' => 'wareValAvg',
                'label'     => "<span style='font-size:11px;'>Сумма счета<br> средняя</span>",
                'encodeLabel' => false,
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return number_format($model['wareValAvg'],0,'.','&nbsp;');
                }
                
            ],

        ],
    ]
);
Pjax::end();  

if(!empty($wareTypes) )
{
Pjax::begin();

$content =  \yii\grid\GridView::widget(
    [
        'dataProvider' => $gridmodel->getRealizeWareOrgProvider($refOrg, $wareTypes, $wareGrp),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:70px;'],
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareGrpTitle',
                'label'     => 'Вид',
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px; width:100px;'],
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareTitle',
                'label'     => 'Товар',
                
                'contentOptions' =>['style' =>'padding: 2px; font-size:11px;'],
                'format' => 'raw',
            ],           

        ],
    ]
);


 echo Collapse::widget([
    'items' => [
        [
            'label' => "Число товаров в наименования реализации удовлетворяющих фильтру: ".$gridmodel->count ,
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 


Pjax::end();  
}
//print_r($gridmodel->debug);   
?>
<hr size='3px'>   
</div>
