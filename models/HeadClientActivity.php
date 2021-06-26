<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use yii\helpers\Html;


use app\models\FltList;
use app\models\OrgList;
use app\models\UserList;
use app\models\MarketSchetForm;
use app\models\WarehouseForm;
use app\models\TmpOrgReestr;
/**
 * HeadClientActivity  - модель формы контроля активности клиентов
 */
class HeadClientActivity extends Model
{

   public $format='html'; 
   public $debug=[];
  
   public $command;    
   public $count=0;
    

   public $orgTitle ="";
   public $userFIO="";
   public $managerFIO="";
   public $operator="";
   public $period="";
   public $fltGood="";
    
  public $scale1;
  public $scale2;     
  public $scale3;      
  public $scale4;      
  public $scale5;      
  public $scale6;             
    
  public function rules()
   {
        return [
            [['orgTitle','managerFIO', 'period', 'scale1', 'scale2', 'scale3', 'scale4', 'scale5', 'scale6'], 'safe'],

        ];
    }
    
    
/************************************/   
/*********  Providers ***************/   
/************************************/

/****************************************************************************************/
 public function prepareSavedClientActivity($params)
 {
         
     $query  = new Query();
     $countquery  = new Query();

    
    /* Список клиентов с которыми были финансовые взаимоотношения */
    
    $countquery->select ("count(distinct {{%tmp_reestr}}.refOrg)")
                 ->from("{{%tmp_reestr}}")
                 ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")
                 ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
                  ;
                  
     $query->select([
        '{{%tmp_reestr}}.refOrg',
        'orgTitle',
        '{{%orglist}}.schetINN',        
        'managerFIO',
        'mainActivity',
        'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'regState',
        'period',
        'plan',
        'fact',
        'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastActiveSchet',
        'lastSdelka',
        'lastContact',
        'lastZakaz',      
        'lastActiveZakaz',
        'city',
        'district',
        'adress',          
        'category',
        'categoryTitle',
        '{{%orglist}}.contactPhone',
        '{{%orglist}}.contactEmail',
        'periodStart',
        'sumSup', 
        'cntSup',
        'DATEDIFF(NOW(), lastSupply) as delaySupply',  
                
     ]) ->from("{{%tmp_reestr}}")
        ->leftJoin("{{%orglist}}", "{{%orglist}}.id = {{%tmp_reestr}}.refOrg")
        ->leftJoin("(Select sum(supplySumm) as sumSup, count(id) as cntSup, refOrg from {{%supply}} group by refOrg  ) as supplys", "supplys.refOrg = {{%orglist}}.id ")
        ->leftJoin("(SELECT DISTINCT {{%schet}}.refOrg, good as goodlist from {{%schet}}, {{%zakazContent}} where  {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%schet}}.summSupply > 0)  as goods ", "goods.refOrg =  {{%orglist}}.id ")
        ->distinct()
        ;
             
     if (($this->load($params) && $this->validate())) 
     {
     
        $query->andFilterWhere(['like', 'managerFIO', $this->managerFIO]); 
        $countquery->andFilterWhere(['like', 'managerFIO', $this->managerFIO]);

        $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
          
        $query->andFilterWhere(['like', 'goodlist', $this->fltGood]);
        $countquery->andFilterWhere(['like', 'goodlist', $this->fltGood]);

        if (!empty($this->catTitle))
        {
        $query->andFilterWhere(['like', 'categoryTitle', $this->catTitle]);
        $countquery->andFilterWhere(['like', 'categoryTitle', $this->catTitle]);
        }
        
        switch ($this->scale1)
        {
            case 1: 
               $query     ->andWhere('period > DATEDIFF(NOW(), lastSupply)');
               $countquery->andWhere('period > DATEDIFF(NOW(), lastSupply)');

               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.0');
                                                                           
            break;
            
            case 2:
               $query     ->andWhere('period <= DATEDIFF(NOW(), lastSupply)');
               $countquery->andWhere('period <= DATEDIFF(NOW(), lastSupply)');
            break;
        
        }
  
        switch ($this->scale2)
        {
            case 1: 
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period > 0.75');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period > 0.75');

               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.0');
                              
               
            break;
            
            case 2:
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period < 0.75');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period < 0.75');
            break;
        
        }
  
        switch ($this->scale3)
        {
            case 1: 
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period > 1.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period > 1.0');

               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.5');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 1.5');
                              
                              
            break;
            
            case 2:
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period < 1.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period < 1.0');
            break;
        
        }
  
        switch ($this->scale4)
        {
            case 1: 
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period > 1.5');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period > 1.5');
               
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 2.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 2.0');
                              
               
            break;
            
            case 2:
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period < 1.5');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period < 1.5');
            break;
        
        }

        switch ($this->scale5)
        {
            case 1: 
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period > 2.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period > 2.0');
               
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 4.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period <= 4.0');
                              
               
            break;
            
            case 2:
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period < 2.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period < 2.0');
            break;
        
        }

        switch ($this->scale6)
        {
            case 1: 
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period > 4.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period > 4.0');
            break;
            
            case 2:
               $query     ->andWhere('DATEDIFF(NOW(), lastSupply)/period < 4.0');
               $countquery->andWhere('DATEDIFF(NOW(), lastSupply)/period < 4.0');
            break;
        
        }
                        
        
     }

        /*"1" => "Все",*/
        /*"2" => "Регулярные",                */
        /*"3" => "Разовые",        */
        
        if (empty($this->period)) $this->period =2;       
        switch ($this->period)
        {
            case 2: 
               $query->andFilterWhere(['>', 'regular', 2]);
               $countquery->andFilterWhere(['>', 'regular', 2]);                       
            break;
            
            case 3:
               $query->andFilterWhere(['<', 'regular', 3]);
               $countquery->andFilterWhere(['<', 'regular', 3]);                       
            break;
        
        }
     
          
//$this->debug = $query->createCommand()->getRawSql();     
       $this->command = $query->createCommand();    
       $this->count = $countquery->createCommand()->queryScalar();
       //echo $countquery->createCommand()->getRawSql(0);
 }
 
 /*********************************************************************************/

   public function getSavedClientActivityProvider($params)
   {

        $this->prepareSavedClientActivity($params);
                
        $provider = new SqlDataProvider(['sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
     
            'orgTitle',
            'managerFIO',
            'mainActivity',
            'otherActivity',
        'avgCheck',
        'balance',
        'regular',
        'period',
        'plan',
        'fact',
        'lastContact',
     /*   'lastOplate',
        'lastSupply',
        'lastSchet',
        'lastSdelka',
        'category',
        'categoryTitle',*/
        'sumSup', 
        'cntSup',
        'delaySupply',
        
            ],
            'defaultOrder' => [    'orgTitle' => SORT_ASC ],
            ],
        ]);
        
    return $provider;
   }   

/************************************************************/   
   
   public function getClientActivityCSV($params)
   {
        $this->prepareSavedClientActivity($params);
        $dataList=$this->command->queryAll();     
    
   
    $mask = realpath(dirname(__FILE__))."/../uploads/headClientActivityReport*.csv";
    array_map("unlink", glob($mask));
    $fname = "uploads/headClientActivityReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
    
        iconv("UTF-8", "Windows-1251","Контрагент"),
        iconv("UTF-8", "Windows-1251","ИНН"),
        iconv("UTF-8", "Windows-1251","Менеджер"), 
        iconv("UTF-8", "Windows-1251","Активность"), 
        iconv("UTF-8", "Windows-1251","Период"),
        iconv("UTF-8", "Windows-1251","Средний чек"),       
        iconv("UTF-8", "Windows-1251","Ожидаемая дата"),
        iconv("UTF-8", "Windows-1251","Ок"),
        iconv("UTF-8", "Windows-1251","Пора"),
                
        iconv("UTF-8", "Windows-1251","До 50%"),     
        
        iconv("UTF-8", "Windows-1251","50%"),        
        iconv("UTF-8", "Windows-1251","x2"),

        iconv("UTF-8", "Windows-1251","x4"),        
        iconv("UTF-8", "Windows-1251","Последний контакт"),
        
        );
       fputcsv($fp, $col_title, ";"); 
       for ($i=0; $i< count($dataList); $i++)
       {    
        $list =  $this->getSavedClientActivityRow($dataList[$i]);    
        fputcsv($fp, $list, ";");  
       }
        
        fclose($fp);
        return $fname;           
   }


    public function getSavedClientActivityRow ($dataRow)  {
    

     
       
       $scale =  $dataRow['delaySupply'] / $dataRow['period'];
       $scaleVal =[0,0,0,0,0,0,0];
       if ( $scale < 1 ) $scaleVal[0]=1;
       if ($scale > 0.75 && $scale <= 1.0 ) $scaleVal[1]=1;
       if ($scale > 1.0  && $scale <= 1.5 ) $scaleVal[2]=1;
       if ($scale > 1.5  && $scale <= 2.0 ) $scaleVal[3]=1;
       if ($scale > 2    && $scale <= 4.0 ) $scaleVal[4]=1;
       if ($scale > 4                     ) $scaleVal[5]=1;
                           
    $list = array 
        (



        iconv("UTF-8", "Windows-1251",$dataRow['orgTitle']),
        iconv("UTF-8", "Windows-1251",$dataRow['schetINN']),        
        iconv("UTF-8", "Windows-1251",$dataRow['managerFIO']), 
        iconv("UTF-8", "Windows-1251",$dataRow['mainActivity']), 
        iconv("UTF-8", "Windows-1251",$dataRow['period']),
        iconv("UTF-8", "Windows-1251",$dataRow['avgCheck']),
        iconv("UTF-8", "Windows-1251",date("d.m.y", strtotime($dataRow['lastSupply'])+ $dataRow['period']*24*3600) ),        
        $scaleVal[0],
        $scaleVal[1],
        $scaleVal[2],
        $scaleVal[3],
        $scaleVal[4],
        $scaleVal[5],
        iconv("UTF-8", "Windows-1251",date("d.m.Y", strtotime($dataRow['lastContact']))),
 
        );
     
  return $list;       
       
   }

/************************************************************/   
   
public function printSavedClientActivity($provider, $model)
 {

 
$lastSync =     Yii::$app->db->createCommand('SELECT MAX(syncTime) FROM {{%tmp_reestr}}')
     ->queryScalar(); 
     

      
if (strtotime($lastSync) < time()-8*60*60) {$style='color:Crimson;'; $text="Пересчитать";}
                                  else     {$style=''; $text="";}
 
 
$grid ="<div style='text-align:right;".$style."'> За все время работы с контрагентом. Актуален на: <b>". date("d.m.Y h:m", strtotime($lastSync)) ."
<a href='#' onclick='document.location.href=\"index.php?r=head/update-client-activity\"'> 
 <span class='glyphicon glyphicon-refresh' aria-hidden='true'></span>&nbsp;".$text."</a></b></div>";
 
$grid .="
<script>
</script>    
<style>

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.grd_date_val
{
    padding: 2px;
    font-size: 8pt;
//     width: 67px;
     width: 100%;     
}

.grd_date_lbl
{
    padding: 2px;
    font-size:10pt;
    width: 50px;
}


</style>
    ";

    $listCategory =     Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($listCategory, 'Не задана');                    
    $fltCategory =     Yii::$app->db->createCommand('SELECT DISTINCT catTitle FROM {{%org_category}} ORDER BY id')
     ->queryColumn(); 
    array_unshift ($fltCategory, 'Все');                    

    
    $grid .=\yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                
            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\", \"childwin\")' >".$model['orgTitle']."</a>";
                },
            ],        
    
    
           [
                'attribute' => 'managerFIO',
                'label'     => 'Менеджер/<br> Активность', 
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                if (empty($model['managerFIO'])) return "&nbsp;";
                    return  "<div style='font-size:14px;width:125px;' ><b>".$model['managerFIO']."</b> (".$model['mainActivity'].")"; 
                }    
                
            ],
            
           [
                'attribute' => 'period',
                'label'     => 'Период',                
                'encodeLabel' => false,
                'format' => 'raw',
                'filter'=>array("1" => "Все","2" => "Регулярный", "3" => "Разовый", ),
                'value' => function ($model, $key, $index, $column)  {
                       
                if ( $model['regular']< 3 ) return "Разовый";                
                $ret= " ".$model['period']." <br>";
                return $ret;
                }    
           ],
                        
           [
                'attribute' => 'avgCheck',
                'label'     => 'Средний <br>чек', 
                'encodeLabel' => false,                
                'format' => 'raw',               
                'value' => function ($model, $key, $index, $column)  {
                return number_format($model['avgCheck'],0,".","&nbsp;");  
                }    
                
            ],

  
           [
                'attribute' => '',
                'label'     => 'Ожидаемая <br>дата', 
                'encodeLabel' => false,                
                'format' => 'raw',               
                'value' => function ($model, $key, $index, $column)  {
                return date("d.m.y", strtotime($model['lastSupply'])+ $model['period']*24*3600);  
                }    
                
            ],
                       
           [
                'attribute' => 'scale1',
                'label'     => '<div class="grd_date_lbl" title="Интервал с последней отгрузки в рамках периода"> Ок </div>',              
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                $scale =  $model['delaySupply'] / $model['period'];
                if ( $scale < 1 ) return "<div class='grd_date_val' style='background-color:DarkGreen;'>&nbsp;</div>";                
                
                }    
           ],

                        
           [
                'attribute' => 'scale2',
                'label'     => '<div class="grd_date_lbl" title="Интервал с последней отгрузки > 75% периода" >Пора</div>',                
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                if ($model['period'] == 0) return "&nbsp;";
                $scale =  $model['delaySupply'] / $model['period'];
                
                if ($scale > 0.75 && $scale <= 1.0 ) return "<div class='grd_date_val' style='background-color:Green;'>&nbsp;</div>";                
                }    
           ],


           [
                'attribute' => 'scale3',
                'label'     => '<div class="grd_date_lbl" title="Интервал превысил период"  >до 50%</div>',                
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                if ($model['period'] == 0) return "&nbsp;";
                $scale =  $model['delaySupply'] / $model['period'];
                
                if ($scale > 1.0 && $scale <= 1.5) return "<div class='grd_date_val' style='background-color:Khaki;'>&nbsp;</div>";                
                }    
           ],
           
           [
                'attribute' => 'scale4',
                'label'     => '<div class="grd_date_lbl" title="Интервал превысил 150% периода" >50%</div>',                
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                if ($model['period'] == 0) return "&nbsp;";
                $scale =  $model['delaySupply'] / $model['period'];                
                if ($scale > 1.5 && $scale <= 2.0) return "<div class='grd_date_val' style='background-color:Orange;'>&nbsp;</div>";                
                }    
           ],
                      

           [
                'attribute' => 'scale5',
                'label'     => '<div class="grd_date_lbl" title="Интервал превысил 200% периода" >x2</div>',                
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                if ($model['period'] == 0) return "&nbsp;";
                $scale =  $model['delaySupply'] / $model['period'];                
                if ($scale > 2 && $scale <= 4.0) return "<div class='grd_date_val' style='background-color:Red;'>&nbsp;</div>";                
                }    
           ],
                      

           [
                'attribute' => 'scale6',
                'label'     => '<div class="grd_date_lbl" title="Интервал превысил 400% периода"  >x4</div>',                
                'filter'=>array("1" => "Да","2" => "Нет" ),  
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)  {                
                if ($model['period'] == 0) return "&nbsp;";
                $scale =  $model['delaySupply'] / $model['period'];                
                if ($scale > 4 ) return "<div class='grd_date_val' style='background-color:Crimson;'>&nbsp;</div>";                
                }    
           ],
                                                       
           [
                'attribute' => 'lastContact',
                'label'     => 'Контакт',                
                'encodeLabel' => false,
                'format' => ['date', 'php:d.m.y'],
           ],
            
                       
        ],
    ]
); 


return $grid;

}
   
 

   
   

   
  /*****************************/
  /*****************************/

  
  
   /** end of object **/     
 }
