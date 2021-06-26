<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper; 



class SaleForm extends Model
{
    public $id= 0;
    public $mode= 0;
    
    public $statSale;
    
        /*Ajax save fields*/
    public $recordId = 0;
    public $dataType = '';
    public $dataVal = 0;
    public $dataId  =0; 

    public $fltDate =0;
       
  public $curOwner =0;   
  
  public $command;
  public $count;

  public $from;
  public $to;
  
  public $wareTitle="";
  public $orgTitle="";
  public $goodTitle="";
    
    public function rules()
    {
        return [
			[['recordId', 'dataType', 'dataVal', 'dataId',
            ], 'default'],


            [['wareTitle', 'orgTitle', 'goodTitle', ], 'safe'],
        ];
    }

/***********************************************/ 
public function getOwnerArray()
{
      //$strSql =" SELECT id, owerOrgTitle FROM {{%control_sverka_filter}}";
      $strSql =" SELECT DISTINCT ownerOrgTitle FROM {{%control_sale_progres}} ORDER BY ownerOrgTitle";
      $list = Yii::$app->db->createCommand($strSql)->queryColumn();

      array_unshift ($list, 'Все');

      return $list;
      //return ArrayHelper::map($list,'id','owerOrgTitle');
}
public function getDefOwner()
{
      $strSql =" SELECT id FROM {{%control_sverka_filter}} where isFilter =1";
      return Yii::$app->db->createCommand($strSql)->queryScalar();                          
}


/*
*/

public function prepareSaleData($params)
   {
   
   // Собственники            
  $strSql = "UPDATE {{%control_sale_progres}}, {{%orglist}} SET 
      {{%control_sale_progres}}.orgRef={{%orglist}}.id where 
      {{%control_sale_progres}}.orgINN = {{%orglist}}.orgINN
      and {{%control_sale_progres}}.orgKPP = {{%orglist}}.orgKPP 
      and {{%control_sale_progres}}.orgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     

  $strSql = "UPDATE {{%control_sale_progres}}, {{%orglist}} SET 
      {{%control_sale_progres}}.orgRef={{%orglist}}.id where 
      {{%control_sale_progres}}.orgINN = {{%orglist}}.orgINN       
      and {{%control_sale_progres}}.orgRef = 0";
    Yii::$app->db->createCommand($strSql)->execute();                                     
/*and (ifnull({{%orglist}}.orgKPP, '') = ''*/
    
        
    $strSql =" UPDATE {{%control_sale_progres}} as a,  {{%documents}} as b SET a.refDocumentOrig= b.id
    where a.refDocumentOrig=0 and a.orgRef = b.refOrg and a.ref1C = b.ref1C_input and a.saleDate=b.docOrigDate 
    and b.docOrigStatus = 0";
    Yii::$app->db->createCommand($strSql)->execute();
    
    $strSql =" UPDATE {{%control_sale_progres}} as a,  {{%documents}} as b SET a.refDocumentCopy= b.id
    where a.refDocumentCopy=0 and a.orgRef = b.refOrg and a.ref1C = b.ref1C_input and a.saleDate=b.docOrigDate 
    and b.docOrigStatus > 0";
    Yii::$app->db->createCommand($strSql)->execute();

    
        
    $query  = new Query();
    $query->select ([
            'a.id',
            'a.ownerOrgTitle', 
            'a.orgTitle', 
            'a.orgINN', 
            'a.orgKPP', 
            'a.orgRef', 
            'a.ref1C', 
            'a.saleDate',
            'a.regRecord', 
            'a.zakazRef1C', 
            'a.zakazDate', 
            'a.wareSum',
            'a.saleNote',
            'a.refDocumentCopy',
            'a.refDocumentOrig'
            ])
            ->from("{{%control_sale_progres}} as a")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = a.orgRef")            
            ->distinct();
            			            
           
    $countquery  = new Query();
    $countquery->select ("count(a.id)")
            ->from("{{%control_sale_progres}} as a")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = a.orgRef")            
            ;            
     
    $errquery  = new Query();
    $errquery->select ("count(a.id)")
            ->from("{{%control_sale_progres}} as a")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = a.orgRef")            
            ;            
    $errquery->andWhere(['=', '(a.refDocumentCopy+a.refDocumentOrig)', 0]);

    
    $allquery  = new Query();
    $allquery->select ("count(a.id)")
            ->from("{{%control_sale_progres}} as a")
            ->leftJoin("{{%orglist}}","{{%orglist}}.id = a.orgRef")            
            ;            
    
         
     
        $query->andWhere(['>=', 'a.saleDate', date("Y-m-d",$this->from)]);
        $countquery->andWhere(['>=', 'a.saleDate', date("Y-m-d",$this->from)]);
        $errquery->andWhere(['>=', 'a.saleDate', date("Y-m-d",$this->from)]);
        $allquery->andWhere(['>=', 'a.saleDate', date("Y-m-d",$this->from)]);

        $query->andWhere(['<=', 'a.saleDate', date("Y-m-d",$this->to)]);
        $countquery->andWhere(['<=', 'a.saleDate', date("Y-m-d",$this->to)]);
        $errquery->andWhere(['<=', 'a.saleDate', date("Y-m-d",$this->to)]);
        $allquery->andWhere(['<=', 'a.saleDate', date("Y-m-d",$this->to)]);

        if (!empty($this->curOwner)){
        $ownerList=$this->getOwnerArray();
        $ownerTitle= $ownerList[$this->curOwner];
        
        $query->andWhere(['Like', 'a.ownerOrgTitle', $ownerTitle]);
        $countquery->andWhere(['Like', 'a.ownerOrgTitle', $ownerTitle]);
        $errquery->andWhere(['Like', 'a.ownerOrgTitle', $ownerTitle]);
        $allquery->andWhere(['Like', 'a.ownerOrgTitle', $ownerTitle]);        
        }

     
        if ($this->mode == 1 ){
        
        $query->andWhere(['=', '(a.refDocumentCopy+a.refDocumentOrig)', 0]);
        $countquery->andWhere(['=', '(a.refDocumentCopy+a.refDocumentOrig)', 0]);
        
        }
        
        
     if (($this->load($params) && $this->validate())) {
        
        $query->andFilterWhere(['like', 'a.orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'a.orgTitle', $this->orgTitle]);   
     }
   
   
    $this->command = $query->createCommand(); 
    $this->count = $countquery->createCommand()->queryScalar();
    
    $this->statSale['err']= $errquery->createCommand()->queryScalar();
    $this->statSale['all']= $allquery->createCommand()->queryScalar();
   } 
  
 public function getSaleProvider($params)
   {
    
    $this->prepareSaleData($params);    
    $pageSize = 10;    
    $dataProvider = new SqlDataProvider([
            'sql' => $this->command ->sql,
            'params' => $this->command->params,
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => $pageSize,
            ],
            
            'sort' => [
            'attributes' => [	            
            'id,',
            'ownerOrgTitle', 
            'orgTitle', 
            'orgINN', 
            'orgKPP', 
            'orgRef', 
            'ref1C', 
            'saleDate',
            'refDocumentCopy',
            'refDocumentOrig'
            ],
            'defaultOrder' => [	'saleDate'=> SORT_DESC],
            ],            
        ]);
                
    return  $dataProvider;   
   }   

/*****************/ 

/******************/
public function getDaySaleList($month, $year){
    
    $year = intval($year);
    $month = intval($month);

    $n = date('t',strtotime($year."-".$month."-01"));
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['sale']=0; }       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(saleDate) as d',           
        ])
         ->from("{{%control_sale_progres}}")
         ->distinct()
         ->groupBy(['DATE(saleDate)']);
    $query->andWhere ('YEAR(saleDate) = '.$year);
    $query->andWhere ('MONTH(saleDate) = '.$month);


    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['sale']=$list[$i]['N'] ; 
    }

    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'DAYOFMONTH(saleDate) as d',           
        ])
         ->from("{{%control_sale_progres}}")
         ->distinct()
         ->groupBy(['DATE(saleDate)']);
    $query->andWhere ('YEAR(saleDate) = '.$year);
    $query->andWhere ('MONTH(saleDate) = '.$month);    
    $query->andWhere(" (refDocumentCopy+refDocumentOrig) = 0 ");   

    $list = $query->createCommand()->queryAll();    
    $n = date('t',strtotime($year."-".$month."-01"));
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $d=$list[$i]['d'];
       $res[$d]['err']=$list[$i]['N'] ; 
    }

    
 /*   echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  
/********************/
 public function getMonthSaleList($year){
    
     $year = intval($year);    

    $n = 12;
    for ($i=0;$i<=$n; $i++ ) {$res[$i]['err']=0; $res[$i]['all']=0; }       
    
    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'MONTH(saleDate) as m',           
        ])
         ->from("{{%control_sale_progres}}")
         ->distinct()
         ->groupBy(['MONTH(saleDate)']);
    $query->andWhere ('YEAR(saleDate) = '.$year);
    
         
    $list = $query->createCommand()->queryAll();    
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $m=$list[$i]['m'];
       $res[$m]['all']=$list[$i]['N'] ; 
    }


    $query  = new Query();
    $query->select ([
        'COUNT(id) as N',   
        'MONTH(saleDate) as m',           
        ])
         ->from("{{%control_sale_progres}}")
         ->distinct()
         ->groupBy(['MONTH(saleDate)']);
    $query->andWhere ('YEAR(saleDate) = '.$year);
    $query->andWhere(" (refDocumentCopy+refDocumentOrig) = 0 ");   
         
    $list = $query->createCommand()->queryAll();    
            
    for ($i=0;$i<count($list) ; $i++ )
    {
       $m=$list[$i]['m'];
       $res[$m]['err']=$list[$i]['N'] ; 
    }

    
   /* echo "<pre>";
    echo $query->createCommand()->getRawSql();
    print_r($list);
    echo "</pre>";*/
    return $res;
   
}  

/********************/
 
 
 
/**/    
 }
 
