<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper; 
use yii\db\Expression;

use yii\helpers\Html;


/**
 Отображение активности по сделкам - таблицы
 */
class SdelkaForm extends Model
{


    public $title ="";
    public $userFIO="";
    public $zakaz="";
    public $mode = 2;
    public $detail=0;
    public $schetNumber="";
  
   public $dataArray=array();  
    
   public $dealStatus="";
   public $orgTitle="";

   
   public $dateFrom='';
   public $dateTo=''; 


   public $format='html'; 
  
   public $command;    
   public $count=0;

   public $frm_time=0;
   public $to_time=0;
   
   public $debug=array();
   
   public $fltSchet='';
        
   public function rules()
   {
        return [
            [['dealStatus', 'userFIO', 'orgTitle', 'zakaz', 'schetNumber', 'fltSchet' ], 'safe'],
        ];
    }
    

public function getUserList(){
    
   $list = Yii::$app->db->createCommand('Select id, userFIO from {{%user}} where roleFlg > 0')                    
                    ->queryAll();                
   
   
   $res =  ArrayHelper::map($list, 'id', 'userFIO');     
   $res[0]='Все';   
   return  $res;
    
    
}

/******* CurrentDeal ********/

    public function getCurrentDealPrepare($params)
    {

    $curUser=Yii::$app->user->identity;

    $marketModel = new MarketSchetForm();             
    /*Получим спистки статусов*/        
     $listStatus = $marketModel-> getListStatus();
     $schetStatus=$listStatus['schet_status'];
     $maxSchetStatus=$schetStatus[count($schetStatus)-1]['razdelOrder'];
     $cashStatus=$listStatus['cash_status'];
     $maxCashStatus=$cashStatus[count($cashStatus)-1]['razdelOrder'];
     $supplyStatus=$listStatus['supply_status'];
     $maxSupplyStatus=$supplyStatus[count($supplyStatus)-1]['razdelOrder'];


       $query  = new Query();
       $query->select ([
       '{{%zakaz}}.id AS zakazId',
       '{{%zakaz}}.formDate', 
       '{{%zakaz}}.refOrg as orgId', 
       '{{%zakaz}}.isFormed', 
       '{{%orglist}}.title as orgTitle', 
       '{{%user}}.userFIO',
       '{{%schet}}.id AS schetId', 
       '{{%schet}}.schetNum', 
       '{{%schet}}.schetDate', 
       'docStatus',
       'cashState', 
       'supplyState', 
       'ref1C', 
       '{{%orglist}}.contactPhone', 
       '{{%orglist}}.contactEmail', 
        'schetSumm', 
        'summOplata', 
        'summSupply',
        ])
            ->from("{{%zakaz}}")            
            ->leftJoin("{{%user}}",'{{%zakaz}}.ref_user = {{%user}}.id')
            ->leftJoin("{{%schet}}",'{{%schet}}.refZakaz = {{%zakaz}}.id')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%zakaz}}.refOrg')
            ->distinct();
            ;

    $countquery  = new Query();
       $countquery->select (" count(DISTINCT {{%zakaz}}.id)")
            ->from("{{%zakaz}}")            
            ->leftJoin("{{%user}}",'{{%zakaz}}.ref_user = {{%user}}.id')
            ->leftJoin("{{%schet}}",'{{%schet}}.refZakaz = {{%zakaz}}.id')
            ->leftJoin('{{%orglist}}','{{%orglist}}.id = {{%schet}}.refOrg')
            ;     
     
        $where="(isSchetActive =1 or {{%zakaz}}.isActive=1)";
        switch ($this->detail)
        {

            case 1:  
           /*Заявки, нет привязанного счета и не начата работа
               $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz 
        (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
               where isActive = 1 AND {{%schet}}.id is null AND (c.contactNumber < 2 or c.contactNumber is null)";*/   
            $query->leftJoin('(select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c', 'c.refZakaz = {{%zakaz}}.id ' );   
            $countquery->leftJoin('(select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c', 'c.refZakaz = {{%zakaz}}.id ' );   
            $where=' isActive = 1 AND {{%schet}}.id is null AND (c.contactNumber < 2 or c.contactNumber is null)';            
            break;
    
            case 2:  
          /*Заявки = начато согласование
                $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where isActive = 1 AND {{%schet}}.id is null and c.contactNumber > 1";   */
           
            $query->leftJoin('(select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c', 'c.refZakaz = {{%zakaz}}.id ' );   
            $countquery->leftJoin('(select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c', 'c.refZakaz = {{%zakaz}}.id ' );   
            $where=' isActive = 1 AND {{%schet}}.id is null AND contactNumber > 1';            
            break;

            case 3:  
            /*Счета, новые
                          $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0 
          and docStatus = 0 and cashState =0 and supplyState = 0    ";*/                         
            $where="{{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0  and docStatus = 0 and cashState =0 and supplyState = 0 ";            
            break;
            
            case 4:  
            /*Счета, в работе нет оплаты и отгрузки
                     $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1  
            and (docStatus > 0 OR cashState =0 OR supplyState = 0) ";     */       

            $where=" {{%schet}}.isSchetActive = 1 and docStatus > 0 AND cashState =0 AND  supplyState = 0 ";
            break;

            case 5:  
            /*Отгрузка ждем
            $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where {{%schet}}.isSchetActive = 1 AND supplyState = 1 ";    */
            $where="{{%schet}}.isSchetActive = 1  AND supplyState = 1 ";
            break;
            
            case 6:  
           /*В процессе отгрузки
            $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} where 
            {{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4";            */
            $where="{{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4";
            break;

            case 7:  
            /*В  оплате 
            $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}}
            where {{%schet}}.isSchetActive = 1 AND cashState > 1 AND cashState < 4 ";            
            */            
            $where="{{%schet}}.isSchetActive = 1 AND cashState > 1 AND cashState < 4  ";
            break;
            
            case 8:  
            /*Ожидаем завершения 
            $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%schet}} 
            where {{%schet}}.isSchetActive = 1 AND cashState =4  AND supplyState > 3 ";            
            */            
            $where="{{%schet}}.isSchetActive = 1  AND cashState =4  AND supplyState > 3";            
            break;

            case 9:  
            /*Ожидаем регистрации
            */            
            $where="{{%schet}}.isSchetActive = 1  AND docStatus =2 ";            
            break;
                        
            case 13:  
            /*Не совпадают суммы счетов оплат и поставок
            $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND (summSupply > schetSumm OR summOplata > schetSumm)  and {{%schet}}.ref1C IS NOT NULL ";  */       
            $where="{{%schet}}.isSchetActive = 1 AND (summSupply > schetSumm OR summOplata > schetSumm)  and {{%schet}}.ref1C IS NOT NULL ";            
            break;
            
        }     

       if ($this->mode == 1)    
       {
        $where.= " AND {{%zakaz}}.ref_user = ". $curUser->id;
       }

       $query->where($where);
       $countquery->where($where);


    
     if (($this->load($params) && $this->validate())) {     
     
        $query->andFilterWhere(['like', 'title', $this->orgTitle]);
        $countquery->andFilterWhere(['like', 'title', $this->orgTitle]);
    
        if (!empty($this->userFIO))
        {
        $query->andFilterWhere(['=', '{{%zakaz}}.ref_user', $this->userFIO]);
        $countquery->andFilterWhere(['=', '{{%zakaz}}.ref_user', $this->userFIO]);
        }
        
        $query->andFilterWhere(['like', 'schetNum', $this->schetNumber]);
        $countquery->andFilterWhere(['like', 'schetNum', $this->schetNumber]);



        switch ($this->fltSchet)
        {
            
            case 1:  
            $query->andFilterWhere(['=', '{{%schet}}.docStatus', 1]);
            $countquery->andFilterWhere(['=', '{{%schet}}.docStatus', 1]);
            break;
    
            case 2:  
            $query->andFilterWhere(['=', '{{%schet}}.docStatus', 2]);
            $countquery->andFilterWhere(['=', '{{%schet}}.docStatus', 2]);            
            break;
            
            case 3:  
            $query->andFilterWhere(['=', '{{%schet}}.docStatus', 3]);
            $countquery->andFilterWhere(['=', '{{%schet}}.docStatus', 3]);           
            break;
            
            case 4:  
            $query->andFilterWhere(['=', '{{%schet}}.docStatus', 4]);
            $countquery->andFilterWhere(['=', '{{%schet}}.docStatus', 4]);

            break;
        }     
        
        
        switch ($this->dealStatus)
        {
            
            case 1:  
            $query->andFilterWhere(['=', '{{%zakaz}}.isFormed', 0]);
            $countquery->andFilterWhere(['=', '{{%zakaz}}.isFormed', 0]);
            break;
    
            case 2:  
            $query->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);
            $countquery->andFilterWhere(['<', 'docStatus', $maxSchetStatus]);

            $query->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);
            $countquery->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);
            
            break;
            case 3:  
            $query->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            $countquery->andFilterWhere(['<', 'cashState', $maxCashStatus]);
            
            $query->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);
            $countquery->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);
            
            break;
            case 4:  
            $query->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);
            $countquery->andFilterWhere(['<', 'supplyState', $maxSupplyStatus]);        

            $query->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);
            $countquery->andFilterWhere(['=', '{{%zakaz}}.isFormed', 1]);

            break;
        }     
        
     }
    /*schetStatus в любом случае*/
                                     


     
     
     
     $query->andWhere ("formDate <= '".date("Y-m-d", $this->to_time)."'");
     $query->andWhere ("formDate >= '".date("Y-m-d", $this->frm_time)."'");
       
     $countquery->andWhere ("formDate <= '".date("Y-m-d", $this->to_time)."'");
     $countquery->andWhere ("formDate >= '".date("Y-m-d", $this->frm_time)."'");

     $this->debug[]=$query->createCommand()->getRawSql();
     
    $this->command = $query->createCommand();    
    $this->count   = $countquery->createCommand()->queryScalar();
            
    }

    public function getCurrentDealData($params)
    {        
        $this->getCurrentDealPrepare($params);    
        $dataList=$this->command->queryAll();
        
        
    $mask = realpath(dirname(__FILE__))."/../uploads/headDealReport*.csv";
    array_map("unlink", glob($mask));     
    $fname = "uploads/headDealReport".time().".csv";
    $fnamePath=(realpath(dirname(__FILE__)))."/../".$fname;
    if ( ($fp = fopen($fnamePath, 'w')) == false) return -1;
    
    $col_title = array (
        iconv("UTF-8", "Windows-1251","Клиент"),
        iconv("UTF-8", "Windows-1251","Менеджер"),
        iconv("UTF-8", "Windows-1251","Заявка №"),
        iconv("UTF-8", "Windows-1251","Заявка Дата"),        
        
        iconv("UTF-8", "Windows-1251","Счет №"),
        iconv("UTF-8", "Windows-1251","Счет дата"),
        iconv("UTF-8", "Windows-1251","Счет сумма"),
        iconv("UTF-8", "Windows-1251","Счет в 1С"),
        
        iconv("UTF-8", "Windows-1251","Оплата сумма"),        
        iconv("UTF-8", "Windows-1251","Оплата дата"),        
        
        iconv("UTF-8", "Windows-1251","Заявка на отгрузку"),
        iconv("UTF-8", "Windows-1251","Поставка сумма"),        
        iconv("UTF-8", "Windows-1251","Поставка дата"),        
        
        iconv("UTF-8", "Windows-1251","Статус оформления"),        
        iconv("UTF-8", "Windows-1251","Статус оплаты"),        
        iconv("UTF-8", "Windows-1251","Статус поставки"),        

        iconv("UTF-8", "Windows-1251","Телефон"),        
        iconv("UTF-8", "Windows-1251","E-mail"),        
        

        );
        fputcsv($fp, $col_title, ";"); 

/*Получим массив статусов*/
      $listSupplyStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =3 order BY razdelOrder')->queryAll();        
      $maxSupplyStatus = count($listSupplyStatus)-1;
      $listCashStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =2 order BY razdelOrder')->queryAll();        
      $maxCashStatus = count($listCashStatus)-1;
      $listDocStatus = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =1 order BY razdelOrder')->queryAll();                      
      $maxDocStatus = count($listDocStatus)-1;
        
    for ($i=0; $i< count($dataList); $i++)
    {        

        $currentStatus1=$currentStatus2=$currentStatus3="";     
        $supplyStatus  = " Нет запроса ";
        $deliverStatus =" Нет отгрузки ";
        
        $oplataSum   = " ";
        $oplataDate  = " ";
 
        $supplyRequest= " ";
        $supplySum = " ";
        $supplyDate = " ";
  
            if (empty($dataList[$i]['schetId']))
            {
                        if ($dataList[$i]['isFormed'] == 0) $currentStatus1= "Заявка не согласована";
                        if ($dataList[$i]['isFormed'] == 1) $currentStatus1= "Заявка согласована счет не выписан";
            }
            else {

            $lastOp = Yii::$app->db->createCommand(
                    'SELECT max(refOp) as max_refOp from {{%schet_status}}
                      where refSchet=:refSchet', 
                    [':refSchet' => $dataList[$i]['schetId'] ])->queryOne();

                
                if ($dataList[$i]['docStatus']>0)
                {
                  if ($dataList[$i]['supplyState']>0)    
                  {
                    if (count ($listSupplyStatus) > $dataList[$i]['supplyState']) { $currentStatus3.=" Ожидается:  ".$listSupplyStatus[$dataList[$i]['supplyState']]['Title'];}
                    else  $currentStatus3 = $listSupplyStatus[$maxSupplyStatus]['Title'];
                  }                                  
                  if ($dataList[$i]['cashState']>0)    
                  {
                    if (count ($listCashStatus) > $dataList[$i]['cashState']) { $currentStatus2.=" Ожидается:  ".$listCashStatus[$dataList[$i]['cashState']]['Title'];}                    
                    else  $currentStatus2 = $listCashStatus[$maxCashStatus]['Title'];
                    
                  }                     
                    if (count ($listDocStatus) > $dataList[$i]['docStatus']) { $currentStatus1.=" Ожидается:  ".$listDocStatus[$dataList[$i]['docStatus']]['Title'];}                    
                    else  $currentStatus1 = $listDocStatus[$maxDocStatus]['Title'];
                }
            }//currentStatus*
            
            if (!empty($dataList[$i]['schetId'])) 
            {

                /*  $supplyRequest, $supplySum,  $supplyDate,        */                    
                $listRequest = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet LIMIT 1',
                    [':refSchet' => $dataList[$i]['schetId'] ])->queryAll();

                if (count ($listRequest) == 0 )  $supplyRequest = "Нет запроса ";
                           else                  $supplyRequest = "Запрос на поставку № ".$listRequest[0]['id']." от ".date ('d.m.Y', strtotime($listRequest[0]['requestDate']));                 
                    
                    
                $listData= Yii::$app->db->createCommand(
                    'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet   LIMIT 1', 
                    [':refSchet' => $dataList[$i]['schetId'],])->queryAll();
             
                if (count ($listData) > 0 && $listData[0]['sumSupply'] > 0 )   
                $supplySum = number_format($listData[0]['sumSupply'],2,'.','');
                $supplyDate = date("d.m.Y", strtotime($listData[0]['lastSupply']));
        
                /*  Oplata  */      
               $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}} where refSchet=:refSchet   LIMIT 1', 
                [':refSchet' => $dataList[$i]['schetId'],])->queryAll();
                 
                 if (count($listData)!=0 && $listData[0]['sumOplata'] != 0)
                 {
                    $oplataSum   = number_format($listData[0]['sumOplata'],2,'.','');
                    $oplataDate  = date("d.m.Y", strtotime($listData[0]['lastOplate']));
                 }
            }
                
                

    $list = array 
            (
            iconv("UTF-8", "Windows-1251",$dataList[$i]['orgTitle']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['userFIO']),                 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['zakazId']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['formDate']), 
        
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetNum']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['schetDate']), 
            iconv("UTF-8", "Windows-1251",number_format($dataList[$i]['schetSumm'],2,'.','')), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['ref1C']), 
            
            
            iconv("UTF-8", "Windows-1251",$oplataSum),      
            iconv("UTF-8", "Windows-1251",$oplataDate),      

            iconv("UTF-8", "Windows-1251",$supplyRequest),             
            iconv("UTF-8", "Windows-1251",$supplySum),      
            iconv("UTF-8", "Windows-1251",$supplyDate),      

            
            iconv("UTF-8", "Windows-1251",$currentStatus1), 
            iconv("UTF-8", "Windows-1251",$currentStatus2), 
            iconv("UTF-8", "Windows-1251",$currentStatus3), 
            
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contactPhone']), 
            iconv("UTF-8", "Windows-1251",$dataList[$i]['contactEmail']), 

           );
     
    fputcsv($fp, $list, ";");  
    }
        
        fclose($fp);
        return $fname;        
    }
    
    public function getCurrentDealProvider($params)
    {
        
    $this->getCurrentDealPrepare($params);    
    
    $provider = new SqlDataProvider([
            'sql' =>     $this->command->sql,
            'params' => $this->command->params,            
            'totalCount' => $this->count,
            'pagination' => [
            'pageSize' => 10,
            ],
            'sort' => [
            'attributes' => [
            'zakazId',
            'orgTitle',
            'schetDate',
            'schetNum',
            'isOplata',
            'ref1C',
            'summOplata',
            'summSupply',
            'userFIO',
            'formDate'
            ],
            'defaultOrder' => [ 'zakazId' => SORT_DESC ],
            ],
        ]);
        return $provider;        
    }
   /*********************/
      
    /**/ 
   public function getLeafValue()
   {
    $curUser=Yii::$app->user->identity;
    $wareModel = new WarehouseForm();     
    $leafValue['storeStatus'] = $wareModel->getStoreFullnes();
   
   
    $frmDate=date('Y-m-d',  $this->frm_time);
    $toDate =date('Y-m-d',  $this->to_time);
    $dateCond=" AND ( DATE({{%zakaz}}.formDate) >= '".$frmDate."' AND  DATE({{%zakaz}}.formDate) <= '".$toDate."')";
   
       /*Всего не закрытых сделок*/
         $strCount = "SELECT count({{%zakaz}}.id) as C, sum(schetSumm) AS S from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where (isActive = 1 OR {{%schet}}.isSchetActive = 1 )".$dateCond;            
        $val= Yii::$app->db->createCommand($strCount)->queryOne();              
       $leafValue['allDeal'] = $val['C'];
       $leafValue['allDealSumm'] = number_format($val['S'],0,'.','&nbsp;');              

       
       /*Заявки, новые*/       
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%schet}}.id is null and (c.contactNumber < 2 or c.contactNumber is null)".$dateCond;            
        $leafValue['newZakaz'] = Yii::$app->db->createCommand($strCount)->queryScalar();              
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%schet}}.id is null and (c.contactNumber < 2 or c.contactNumber is null) 
           and {{%zakaz}}.ref_user=".$curUser->id.$dateCond;            
        $leafValue['newZakazMy'] = Yii::$app->db->createCommand($strCount)->queryScalar();              
        
        
        $strCount = "SELECT sum(count*value)  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN {{%zakazContent}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%zakazContent}}.isActive = 1 and {{%schet}}.id is null and c.contactNumber < 2".$dateCond;                    
        $leafValue['newZakazSumm'] =  number_format(Yii::$app->db->createCommand($strCount)->queryScalar(),0,'.','&nbsp;');              
                      
       /*Заявки в работе*/
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where isActive = 1 AND {{%schet}}.id is null and c.contactNumber > 1".$dateCond;            
        $leafValue['zakazInWork'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              
        $strCount = "SELECT count({{%zakaz}}.id) from {{%zakaz}} LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where isActive = 1 AND {{%schet}}.id is null and c.contactNumber > 1
           and {{%zakaz}}.ref_user=".$curUser->id.$dateCond;                     
        $leafValue['zakazInWorkMy'] =  Yii::$app->db->createCommand($strCount)->queryScalar();              

        $strCount = "SELECT sum(count*value)  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           LEFT JOIN {{%zakazContent}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
           LEFT JOIN (select count(id) as contactNumber, refZakaz FROM {{%contact}} group by refZakaz ) as c   on c.refZakaz = {{%zakaz}}.id  
           where {{%zakaz}}.isActive = 1 AND {{%zakazContent}}.isActive = 1 and {{%schet}}.id is null and c.contactNumber > 1".$dateCond;                    
        $leafValue['zakazInWorkSumm'] =  number_format(Yii::$app->db->createCommand($strCount)->queryScalar(),0,'.','&nbsp;');              


       
       /*Счета, нет оплаты и отгрузки*/
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz
           where {{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0 
          and docStatus = 0 and cashState =0 and supplyState = 0    ".$dateCond;            
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['newSchet'] = $val['C'];
       $leafValue['newSchetSumm'] = number_format($val['S'],0,'.','&nbsp;');              
       $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz  where {{%schet}}.isSchetActive = 1 AND {{%schet}}.summOplata = 0 AND {{%schet}}.summSupply = 0 
          and docStatus = 0 and cashState =0 and supplyState = 0  
          and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['newSchetMy'] = $val['C'];
       
        /*Счета, ожидают регистрации 1c docStatus=2*/
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz  
           where {{%schet}}.isSchetActive = 1  
          and docStatus = 2   ".$dateCond;            
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['waitSchet'] = $val['C'];
       $leafValue['waitSchetSumm'] = number_format($val['S'],0,'.','&nbsp;');              
       $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz  
       where {{%schet}}.isSchetActive = 1 
          and docStatus = 2 
          and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
          $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['waitSchetMy'] = $val['C'];       
       
              
       /*Счета, нет оплаты и отгрузки */
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1   
         and docStatus > 0 AND  cashState =0 AND supplyState = 0 ".$dateCond;            
       $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['schetInWork']  = $val['C'];
       $leafValue['schetInWorkSumm']  = number_format($val['S'],0,'.','&nbsp;');              
         $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1   
         and docStatus > 0 AND  cashState =0 AND supplyState = 0 
         and {{%schet}}.refManager=".$curUser->id;                                        
       $val= Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['schetInWorkMy']  = $val['C'];

       /*Ожидает отгрузки*/
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND supplyState = 1 ".$dateCond;            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyWait']  = $val['C'];
       $leafValue['supplyWaitSumm']  = number_format($val['S'],0,'.','&nbsp;');              

       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND supplyState = 1 
                 and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyWaitMy']  = $val['C'];
       
       /*В процессе отгрузки*/
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4".$dateCond;            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyProc']  = $val['C'];
       $leafValue['supplyProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
        $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND supplyState > 1 AND supplyState < 4
                 and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['supplyProcMy']  = $val['C'];
       
       /*В  оплате */
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 
       AND cashState > 1 AND cashState < 4 ".$dateCond;            
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['cashProc']  = $val['C'];
       $leafValue['cashProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S   from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz  where {{%schet}}.isSchetActive = 1 
       AND cashState > 1 AND cashState < 4 and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['cashProcMy']  = $val['C'];


       /*В  работе */
       $strCount = "SELECT count(DISTINCT {{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 
       AND (cashState > 1 AND cashState < 4) OR (supplyState > 1 AND supplyState < 4)  ".$dateCond;            
       $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['inWorkProc']  = $val['C'];
       $leafValue['inWorkProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              

       
       /*Ожидаем завершения */
        $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND cashState =4  
        AND supplyState > 3 ".$dateCond;            
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['finitProc']  = $val['C'];
       $leafValue['finitProcSumm']  = number_format($val['S'],0,'.','&nbsp;');              
        $strCount = "SELECT count({{%schet}}.id) AS C, sum(schetSumm) AS S  from  {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz  where {{%schet}}.isSchetActive = 1 AND cashState =4  
        AND supplyState > 3 and {{%schet}}.refManager=".$curUser->id.$dateCond;                               
        $val =Yii::$app->db->createCommand($strCount )->queryOne();              
       $leafValue['finitProcMy']  = $val['C'];
       
       /*Отгружено, в процессе оплаты*/
  /*       $strCount = "SELECT count({{%schet}}.id) from {{%schet}} where {{%schet}}.isSchetActive = 1 AND summSupply >= schetSumm AND summOplata > 0 AND summOplata < schetSumm and {{%schet}}.ref1C IS NOT NULL ";            
       $leafValue[8] =Yii::$app->db->createCommand($strCount )->queryScalar();              */
       

       /*Число оплат в реестре на которые не назначена оплата*/
       $strCount = "SELECT count({{%reestr_oplat}}.id) as C, sum(ifnull(summRequest,0) -ifnull(summOplate,0)) as S from {{%reestr_oplat}} where isActive = 1 AND (summOplate < summRequest ) ";            
       $list=Yii::$app->db->createCommand($strCount )->queryAll();             
        
       $leafValue['oplateNInWok']  = $list[0]['C'];
       $leafValue['oplateSInWok']  = $list[0]['S'];

       

       
       
       /*Не совпадают суммы счетов оплат и поставок*/
         $strCount = "SELECT count({{%schet}}.id) from {{%zakaz}} 
           LEFT JOIN {{%schet}} on {{%zakaz}}.id = {{%schet}}.refZakaz where {{%schet}}.isSchetActive = 1 AND (summSupply > schetSumm OR summOplata > schetSumm)  and {{%schet}}.ref1C IS NOT NULL ".$dateCond;            
       $leafValue[9] =Yii::$app->db->createCommand($strCount )->queryScalar();             

	   
	   $leafValue[10] =0;
	   
       /*Реестр клиентов*/
       $strCount = "
	   SELECT count(org.`id`)
FROM {{%orglist}} as org LEFT JOIN {{%user}} as b ON b.id = org.refManager 

LEFT JOIN (SELECT count(id) as oplataCnt, SUM(oplateSumm) as oplataSum, max(oplateDate) as lastOplate, refOrg 
from {{%oplata}} group by refOrg) as opl ON opl.refOrg = org.id 

LEFT JOIN (SELECT count(id) as supplyCnt, SUM(supplySumm) as supplySum, refOrg , max(supplyDate) as lastSupply
from {{%supply}} group by refOrg) as supl ON supl.refOrg = org.id 

WHERE   isOrgActive =1 AND	(ifnull(oplataSum,0)>0 OR ifnull(supplySum,0)>0)
";
   $leafValue[11] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   
   
 
   $strCount = "SELECT MAX(syncTime) from {{%tmp_reestr}}";            
   $leafValue['lastReestrForm'] =Yii::$app->db->createCommand($strCount)->queryScalar();             

   
       /*Число лидов со статусом обратить внимание*/
         $strCount = "SELECT count({{%contact}}.id) from {{%contact}} where eventType = 10";            
       $leafValue['leadHeadCount'] =Yii::$app->db->createCommand($strCount)->queryScalar();             

   
   
   

   
       /*Закупки */
       
       
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =1 AND stage=1 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =3 AND stage=2 group by purchaseRef) as b on b.purchaseRef = {{%purchase}}.id
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =0 AND stage=1 group by purchaseRef) as a1 on a1.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =2 AND stage=2 group by purchaseRef) as b1 on b1.purchaseRef = {{%purchase}}.id
   where ((ifnull(a.sN,0) =0 AND ifnull(a1.sN,0) >0) OR (ifnull(b.sN,0) =0 AND ifnull(b1.sN,0) >0 ))";            
   $leafValue['purchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
       

   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where  isActive =1 ";            
   $leafValue['purchaseActive'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
    
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =4 AND stage=3 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id    
   where (ifnull(a.sN,0) =0 )";            
   $leafValue['purchaseActive'] +=Yii::$app->db->createCommand($strCount )->queryScalar();             
                    
   $strCount = "SELECT count(DISTINCT({{%purchase}}.id)) from {{%purchase}} 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =1 AND stage=1 group by purchaseRef) as a on a.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =3 AND stage=2 group by purchaseRef) as b on b.purchaseRef = {{%purchase}}.id
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =0 AND stage=1 group by purchaseRef) as a1 on a1.purchaseRef = {{%purchase}}.id 
   LEFT JOIN (SELECT count(id) as sN, purchaseRef from {{%purchase_etap}} where etap =2 AND stage=2 group by purchaseRef) as b1 on b1.purchaseRef = {{%purchase}}.id
   where ((ifnull(a.sN,0) =0 AND ifnull(a1.sN,0) >0) OR (ifnull(b.sN,0) =0 AND ifnull(b1.sN,0) >0 ))";            
   $leafValue['purchase'] =Yii::$app->db->createCommand($strCount )->queryScalar();             

   $countquery  = new Query();
   $countquery->select ("count(DISTINCT({{%purchase}}.id))")->from("{{%purchase}}")->where("isFinishedPurchase = 0");                           
   $countquery->leftJoin("(Select count(id) as s1_startN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=1 group by purchaseRef) as s1_start ", 's1_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s1_finN, purchaseRef from {{%purchase_etap}} where stage =1 AND etap=2 group by purchaseRef) as s1_fin ", 's1_fin.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_startN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=2 group by purchaseRef) as s2_start ", 's2_start.purchaseRef = {{%purchase}}.id')    
            ->leftJoin("(Select count(id) as s2_finN, purchaseRef from {{%purchase_etap}} where stage =2 AND etap=3 group by purchaseRef) as s2_fin ", 's2_fin.purchaseRef = {{%purchase}}.id')    
        ;        
   $countquery->andWhere("( (ifnull(s1_startN,0) =1 AND ifnull(s1_finN,0)=0 ) OR (ifnull(s2_startN,0) =1 AND ifnull(s2_finN,0)=0 )   )");
   $leafValue['purchaseInSogl']  = $countquery->createCommand()->queryScalar();
   

   
   $strCount = "SELECT count({{%purchase_zakaz}}.id) from {{%purchase_zakaz}} where {{%purchase_zakaz}}.status = 1 AND isActive =1 ";            
   $leafValue['purchase_zakaz'] =Yii::$app->db->createCommand($strCount )->queryScalar();             
   $leafValue['requestInSogl'] = $leafValue['purchase_zakaz'];    
       
   return $leafValue;   
   }
   
 

}
