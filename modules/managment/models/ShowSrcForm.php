<?php

namespace app\modules\managment\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 


/**
 */


class ShowSrcForm extends Model
{

    public $id=0;
    

    public $debug=[];   
    public $err=[];   
     
    public $dataRequestId="";
    public $dataRowId="";
    public $dataType="";
    public $dataVal ="";
    public $dataValType =0;
    
    public $typeTitle="";

    public $strDate ="";
    
    public $headerRef=0;
    public $ownerOrgTitle='';
    public $orgTitle='';
    public $purchTitle='';
    
    public $goodTitle='';
    
    public $article='';
    public $ownerTitle='';
    
    public $rowRef=0;
    public $rowTitle='';
    
    public $stDate="";
    public $enDate="";

    public $existDates=[];

    public $suspicious;
    
    public function rules()
    {
        return [
              [[ 'dataRequestId', 'dataRowId', 'dataType', 'dataVal', 'dataValType'], 'default'],
              [['typeTitle','ownerOrgTitle', 'orgTitle', 'purchTitle', 'goodTitle', 'article',
               'ownerTitle','suspicious',  ], 'safe'],
        ];
    }
    
/******************************************/  
/*
*/

  public function getPurchProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([ '{{%control_purch_content}}.id',  
                      'ownerOrgTitle',
                      'orgTitle',                      
                      'purchDate',                      
                      'purchTitle', 
                      'purchEd',                      
                      'purchCount', 
                      'purchSum',                      
                      'typeRef',
                      'ownerOrgRef',
                      'typeTitle'
                      ])
            ->from("{{%control_purch_content}}")
            ->leftjoin('{{%control_purch_type}}', "{{%control_purch_content}}.typeRef = {{%control_purch_type}}.id" )
            ->distinct();
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count({{%control_purch_content}}.id)")
            ->from("{{%control_purch_content}}")
            ->leftjoin('{{%control_purch_type}}', "{{%control_purch_content}}.typeRef = {{%control_purch_type}}.id" )
            ;     

     if (!empty($this->headerRef))
     {
             $query->andWhere("{{%control_purch_content}}.headerRef=".$this->headerRef); 
        $countquery->andWhere("{{%control_purch_content}}.headerRef=".$this->headerRef);
     }


     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $headersRef=$this->getPurchHeadersRef($stTime, $enTime);
//$this->debug[]=$headersRef;         
             $query->andWhere("{{%control_purch_content}}.headerRef IN(".$headersRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.headerRef IN(".$headersRef.")");         
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getPurchOwnersRef($this->rowRef);
             $query->andWhere("{{%control_purch_content}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.ownerOrgRef IN(".$ownersRef.")");         
        
        $typesRef=$this->getPurchTypesRef($this->rowRef);
             $query->andWhere("{{%control_purch_content}}.typeRef IN(".$typesRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.typeRef IN(".$typesRef.")");         
        
     }


     if (($this->load($params) && $this->validate())) {
             $query->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);
        $countquery->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);           

             $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
        $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);           

             $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);           

             $query->andFilterWhere(['like', 'purchTitle', $this->purchTitle]);
        $countquery->andFilterWhere(['like', 'purchTitle', $this->purchTitle]);           
     }

     

     
   $command = $query->createCommand();     
   $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'ownerOrgTitle',
                      'orgTitle',                      
                      'purchDate',                      
                      'purchTitle', 
                      'purchEd',                      
                      'purchCount', 
                      'purchSum',                      
                      'typeRef',
                      'ownerOrgRef',
                      'typeTitle'                    
            ],            
            'defaultOrder' => [ 'purchDate' => 'SORT_DESC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   

/**************/   
  public function getPurchSumProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([ '{{%control_purch_content}}.id',  
                      'ownerOrgTitle',
                      'SUM(purchSum) as purchSum',                      
                      'typeRef',
                      'ownerOrgRef',
                      'typeTitle'
                      ])
            ->from("{{%control_purch_content}}")
            ->leftjoin('{{%control_purch_type}}', "{{%control_purch_content}}.typeRef = {{%control_purch_type}}.id" )
            ->distinct()
            ->groupBy(" typeRef,ownerOrgRef")
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count(*)")
            ->from("{{%control_purch_content}}")
            ->leftjoin('{{%control_purch_type}}', "{{%control_purch_content}}.typeRef = {{%control_purch_type}}.id" )
            ->groupBy(" typeRef,ownerOrgRef")
            ->distinct()
            ;     

     if (!empty($this->headerRef))
     {
             $query->andWhere("{{%control_purch_content}}.headerRef=".$this->headerRef); 
        $countquery->andWhere("{{%control_purch_content}}.headerRef=".$this->headerRef);
     }


     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $headersRef=$this->getPurchHeadersRef($stTime, $enTime);
//$this->debug[]=$headersRef;         
             $query->andWhere("{{%control_purch_content}}.headerRef IN(".$headersRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.headerRef IN(".$headersRef.")");         
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getPurchOwnersRef($this->rowRef);
             $query->andWhere("{{%control_purch_content}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.ownerOrgRef IN(".$ownersRef.")");         
        
        $typesRef=$this->getPurchTypesRef($this->rowRef);
             $query->andWhere("{{%control_purch_content}}.typeRef IN(".$typesRef.")"); 
        $countquery->andWhere("{{%control_purch_content}}.typeRef IN(".$typesRef.")");         
        
     }


     if (($this->load($params) && $this->validate())) {
             $query->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);
        $countquery->andFilterWhere(['like', 'typeTitle', $this->typeTitle]);           

             $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
        $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);           

     }

     

     
   $command = $query->createCommand();     
   $count   = count($query->createCommand()->queryAll());
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'ownerOrgTitle',
                      'purchSum',                      
                      'typeTitle'
            ],            
            'defaultOrder' => [ 'ownerOrgTitle' => 'SORT_ASC', 'typeTitle' => 'SORT_ASC'],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
/**************/

  public function getPurchHeadersRef($stTime, $enTime)
  {
    $headerRefs='-1,';
    $i=0;
    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {        
    //Последний заголовок
     $this->existDates[$i]['time']=$ct;    
     $this->existDates[$i]['date']=date('d.m.y',$ct);    
     $this->existDates[$i]['exist'] = 0;     
        $headerRef = Yii::$app->db->createCommand(
            'SELECT ifnull(max(id),0) from {{%control_purch_header}} WHERE onDate =:onDate', 
            [':onDate' => date('Y-m-d', $ct) ])->queryScalar();  
            
        if(!empty($headerRef)) $headerRefs .=$headerRef.",";   
     $this->existDates[$i]['exist'] = $headerRef;   
     $i++;
    }         
    $headerRefs=substr($headerRefs, 0, -1);    
    return $headerRefs;     
  }

  public function getPurchOwnersRef($rowRef)     
  {
    $refs='-1,';
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef from {{%monitor_row_cfg}} WHERE srcType = 5 and mult <> 0 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowRef), ])->queryAll();  
   for ($i=0;$i<count($dataValType); $i++)
   {
    $refs.=$dataValType[$i]['filterRef'].",";   
   }   
    
    $refs=substr($refs, 0, -1);    
    return $refs;           
  }

  public function getPurchTypesRef($rowRef)     
  {
    $refs='-1,';
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef from {{%monitor_row_cfg}} WHERE srcType = 6 and mult <> 0 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowRef), ])->queryAll();  
   for ($i=0;$i<count($dataValType); $i++)
   {
    $refs.=$dataValType[$i]['filterRef'].",";   
   }   
    
    $refs=substr($refs, 0, -1);    
    return $refs;           
  }

/******************************************/  
/*
*/

  public function getProfitProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([ '{{%profit_content}}.id',  
                      'ownerOrgTitle',                  
                      'recordDate',                      
                      'goodTitle', 
                      'goodEd',                      
                      'goodAmount', 
                      'sellPrice',                      
                      'initPrice',
                      'profit',                      
                      'profitability',                            
                      'ownerOrgRef',
                      'if(profitability>60, 1, 0) as suspicious'
                      ])
            ->from("{{%profit_content}}")
            ->distinct();
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count({{%profit_content}}.id)")
            ->from("{{%profit_content}}")
            ;     

     if (!empty($this->headerRef))
     {
             $query->andWhere("{{%profit_content}}.headerRef=".$this->headerRef); 
        $countquery->andWhere("{{%profit_content}}.headerRef=".$this->headerRef);
     }


     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $headersRef=$this->getProfitHeadersRef($stTime, $enTime);     
             $query->andWhere("{{%profit_content}}.headerRef IN(".$headersRef.")"); 
        $countquery->andWhere("{{%profit_content}}.headerRef IN(".$headersRef.")");         
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getProfitOwnersRef($this->rowRef);
             $query->andWhere("{{%profit_content}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%profit_content}}.ownerOrgRef IN(".$ownersRef.")");         
     }


     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
        $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);           

             $query->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);
        $countquery->andFilterWhere(['like', 'goodTitle', $this->goodTitle]);           

             $query->andFilterWhere(['=', 'recordDate', $this->fltDate]);
        $countquery->andFilterWhere(['=', 'recordDate', $this->fltDate]);           

        
        
        
        switch ($this->suspicious)
        {
          case 1:
               $query->andFilterWhere(['>=', 'profitability', 60]);
          $countquery->andFilterWhere(['>=', 'profitability', 60]);
          break;          
          case 2:
               $query->andFilterWhere(['<', 'profitability', 60]);           
          $countquery->andFilterWhere(['<', 'profitability', 60]);           
          break;                                 
        }        
     }
     
   $command = $query->createCommand();     
   $count   = $countquery->createCommand()->queryScalar();
$this->debug=  $query->createCommand()->getRawSql();        
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'ownerOrgTitle',                  
                      'recordDate',                      
                      'goodTitle', 
                      'goodEd',                      
                      'goodAmount', 
                      'sellPrice',                      
                      'initPrice',
                      'profit',                      
                      'profitability',                                               
                      'suspicious'
            ],            
            'defaultOrder' => [ 'recordDate' => 'SORT_DESC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
 /***********/  
  public function getProfitSumProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([   
                      'ownerOrgTitle',                                        
                      'sum(sellPrice) as sellPrice',                      
                      'sum(initPrice) as initPrice',
                      'sum(profit) as profit',                      
                      'ownerOrgRef'
                      ])
            ->from("{{%profit_content}}")
            ->distinct()
            ->groupBy('ownerOrgRef')
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count(DISTINCT(ownerOrgRef))")
            ->from("{{%profit_content}}")
            ->groupBy('ownerOrgRef');
            ;     

     if (!empty($this->headerRef))
     {
             $query->andWhere("{{%profit_content}}.headerRef=".$this->headerRef); 
        $countquery->andWhere("{{%profit_content}}.headerRef=".$this->headerRef);
     }


     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $headersRef=$this->getProfitHeadersRef($stTime, $enTime);     
             $query->andWhere("{{%profit_content}}.headerRef IN(".$headersRef.")"); 
        $countquery->andWhere("{{%profit_content}}.headerRef IN(".$headersRef.")");         
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getProfitOwnersRef($this->rowRef);
             $query->andWhere("{{%profit_content}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%profit_content}}.ownerOrgRef IN(".$ownersRef.")");         
     }


     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);
        $countquery->andFilterWhere(['like', 'ownerOrgTitle', $this->ownerOrgTitle]);           
     }
     
   $command = $query->createCommand();     
   $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' =>10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'ownerOrgTitle',                  
                      'sellPrice',                      
                      'initPrice',
                      'profit',                      
            ],            
            'defaultOrder' => [ 'ownerOrgTitle' => 'SORT_DESC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
/**************/

  public function getProfitHeadersRef($stTime, $enTime)
  {
    $headerRefs='-1,';
    $i=0;
    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {        
    //Последний заголовок
     $this->existDates[$i]['time']=$ct;    
     $this->existDates[$i]['date']=date('d.m.y',$ct);    
     $this->existDates[$i]['exist'] = 0;     
        $headerRef = Yii::$app->db->createCommand(
            'SELECT ifnull(max(id),0) from {{%profit_header}} WHERE onDate =:onDate', 
            [':onDate' => date('Y-m-d', $ct) ])->queryScalar();  
            
        if(!empty($headerRef)) $headerRefs .=$headerRef.",";   
     $this->existDates[$i]['exist'] = $headerRef;   
     $i++;
    }         
    $headerRefs=substr($headerRefs, 0, -1);    
    return $headerRefs;     
  }

  public function getProfitOwnersRef($rowRef)     
  {
    $refs='-1,';
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef from {{%monitor_row_cfg}} WHERE srcType = 1  and mult <> 0 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowRef), ])->queryAll();  
   for ($i=0;$i<count($dataValType); $i++)
   {
    $refs.=$dataValType[$i]['filterRef'].",";   
   }   
    
    $refs=substr($refs, 0, -1);    
    return $refs;           
  }
/******************************************/  
/*
*/

  public function getBankOpProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([ 
                      '{{%bank_operation}}.id',  
                      'ownerTitle',
                      'orgTitle',                      
                      'orgINN',                      
                      'orgKPP', 
                      'regNote',
                      'regDate',  
                      'recordSum',                     
                      'ownerOrgRef',
                      '{{%bank_op_article}}.article'
                      ])
            ->from("{{%bank_operation}}")
            ->leftjoin('{{%bank_op_article}}', "{{%bank_operation}}.articleRef = {{%bank_op_article}}.id" )
            ->distinct();
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count({{%bank_operation}}.id)")
            ->from("{{%bank_operation}}")
            ->leftjoin('{{%bank_op_article}}', "{{%bank_operation}}.articleRef = {{%bank_op_article}}.id" )
            ;     

     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $this->getBankOpHeadersRef($stTime, $enTime);  
             $query->andWhere("regDate >= '".date('Y-m-d',$stTime)."'"); 
        $countquery->andWhere("regDate >= '".date('Y-m-d',$stTime)."'"); 
             $query->andWhere("regDate <= '".date('Y-m-d',$enTime)."'"); 
        $countquery->andWhere("regDate <= '".date('Y-m-d',$enTime)."'");        
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getBankOpOwnersRef($this->rowRef);
             $query->andWhere("{{%bank_operation}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%bank_operation}}.ownerOrgRef IN(".$ownersRef.")");         
        
        $typesRef=$this->getBankOpTypesRef($this->rowRef);
        $this->debug[]=$typesRef;
             $query->andWhere("{{%bank_operation}}.articleRef IN(".$typesRef.")"); 
        $countquery->andWhere("{{%bank_operation}}.articleRef IN(".$typesRef.")");         

      
     }
                     

     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'ownerTitle', $this->ownerTitle]);
        $countquery->andFilterWhere(['like', 'ownerTitle', $this->ownerTitle]);           

             $query->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'orgTitle', $this->orgTitle]);           

             $query->andFilterWhere(['like', '{{%bank_op_article}}.article', $this->article]);
        $countquery->andFilterWhere(['like', '{{%bank_op_article}}.article', $this->article]);           
     }

     

     
   $command = $query->createCommand();     
   $count   = $countquery->createCommand()->queryScalar();
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 20,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'article',
                      'ownerTitle',
                      'orgTitle',                      
                      'orgINN',                      
                      'orgKPP', 
                      'regNote',
                      'operationDate',  
                      'recordSum', 
            ],            
            'defaultOrder' => [ 'operationDate' => 'SORT_DESC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
/******************************************/  
/*
*/

  public function getBankOpSumProvider($params)  
   {
                         
    $query  = new Query();
    $query->select ([                       
                      'ownerTitle',
                      '{{%bank_op_article}}.article',
                      'sum(recordSum) as recordSum',                     
                      'ownerOrgRef',
                      '{{%bank_operation}}.articleRef'
                      ])
            ->from("{{%bank_operation}}")
            ->leftjoin('{{%bank_op_article}}', "{{%bank_operation}}.articleRef = {{%bank_op_article}}.id" )
            ->groupBy("ownerOrgRef, articleRef")
            ->distinct();
            ;
  
  
     $countquery  = new Query();
     $countquery->select (" count({{%bank_operation}}.id)")
            ->from("{{%bank_operation}}")
            ->leftjoin('{{%bank_op_article}}', "{{%bank_operation}}.articleRef = {{%bank_op_article}}.id" )
            ;     

     if (!empty ($this->stDate) && !empty ($this->enDate))
     {
         $stTime = strtotime($this->stDate);
         $enTime = strtotime($this->enDate);
         $this->getBankOpHeadersRef($stTime, $enTime);  
             $query->andWhere("operationDate >= '".date('Y-m-d',$stTime)."'"); 
        $countquery->andWhere("operationDate >= '".date('Y-m-d',$stTime)."'"); 
             $query->andWhere("operationDate <= '".date('Y-m-d',$enTime)."'"); 
        $countquery->andWhere("operationDate <= '".date('Y-m-d',$enTime)."'");        
     }

     if (!empty ($this->rowRef) )
     {
       $this->rowTitle = Yii::$app->db->createCommand(
            'SELECT rowTitle from {{%monitor_row}} WHERE id = :rowHeaderRef', 
            [':rowHeaderRef' => intval($this->rowRef), ])->queryScalar();
         
        $ownersRef=$this->getBankOpOwnersRef($this->rowRef);
             $query->andWhere("{{%bank_operation}}.ownerOrgRef IN(".$ownersRef.")"); 
        $countquery->andWhere("{{%bank_operation}}.ownerOrgRef IN(".$ownersRef.")");         
        
        $typesRef=$this->getBankOpTypesRef($this->rowRef);
        $this->debug[]=$typesRef;
             $query->andWhere("{{%bank_operation}}.articleRef IN(".$typesRef.")"); 
        $countquery->andWhere("{{%bank_operation}}.articleRef IN(".$typesRef.")");         
        
     }
                     

     if (($this->load($params) && $this->validate())) {

             $query->andFilterWhere(['like', 'ownerTitle', $this->ownerTitle]);
        $countquery->andFilterWhere(['like', 'ownerTitle', $this->ownerTitle]);           
            
             $query->andFilterWhere(['like', '{{%bank_op_article}}.article', $this->article]);
        $countquery->andFilterWhere(['like', '{{%bank_op_article}}.article', $this->article]);           
     }

     

     
   $command = $query->createCommand();     
   $count   = count($query->createCommand()->queryAll());
    
    $dataProvider = new SqlDataProvider([
            'sql' => $command ->sql,
            'params' => $command->params,
            'totalCount' => $count,
            'pagination' => [
            'pageSize' => 10,
            ],
            
            'sort' => [
            
            'attributes' => [
                      'article',
                      'ownerTitle',
                      'recordSum', 
            ],            
            'defaultOrder' => [ 'ownerTitle' => 'SORT_ASC', 'article' => 'SORT_ASC' ],            
            ],
            
        ]);
    return  $dataProvider;   
   }   
   
/**************/

  public function getBankOpHeadersRef($stTime, $enTime)
  {
    $headerRefs='-1,';
    $i=0;
    for ($ct=$stTime; $ct<=$enTime; $ct+=24*3600 )
    {        
    //Последний заголовок
    $this->existDates[$i]['time'] =$ct;    
     $this->existDates[$i]['date']=date('d.m.y',$ct);    
     $this->existDates[$i]['exist'] = 0;     
        $headerRef = Yii::$app->db->createCommand(
            'SELECT ifnull(max(id),0) from {{%bank_op_header}} WHERE onDate =:onDate', 
            [':onDate' => date('Y-m-d', $ct) ])->queryScalar();  
            
     if(!empty($headerRef)) $headerRefs .=$headerRef.",";   
     $this->existDates[$i]['exist'] = $headerRef;   
     $i++;
    }         
    $headerRefs=substr($headerRefs, 0, -1);    
    return $headerRefs;     
  }

  public function getBankOpOwnersRef($rowRef)     
  {
    $refs='-1,';
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef from {{%monitor_row_cfg}} WHERE srcType = 8  and mult <> 0 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowRef), ])->queryAll();  
   for ($i=0;$i<count($dataValType); $i++)
   {
    $refs.=$dataValType[$i]['filterRef'].",";   
   }   
    
    $refs=substr($refs, 0, -1);    
    return $refs;           
  }

  public function getBankOpTypesRef($rowRef)     
  {
    $refs='-1,';
    $dataValType = Yii::$app->db->createCommand(
            'SELECT filterRef from {{%monitor_row_cfg}} WHERE srcType = 2 and mult <> 0 and rowHeaderRef = :rowHeaderRef', 
            [':rowHeaderRef' => intval($rowRef), ])->queryAll();  
   for ($i=0;$i<count($dataValType); $i++)
   {
    $refs.=$dataValType[$i]['filterRef'].",";   
   }   
    $this->debug[]=$refs;
    $refs=substr($refs, 0, -1);    
    return $refs;           
  }



  
}
 
