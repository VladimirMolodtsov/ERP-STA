<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use yii\helpers\Html;

use app\models\TblControlRemainsUse;


/**
 * HeadCfgControl  - настройка контрольных форм
 */
 
class HeadControl extends Model
{

 /*фильтры*/
  public $usedOrgTitle;
  public $scladTitle;

    
   public function rules()
   {

        return [
            [['usedOrgTitle',  'scladTitle' ], 'safe'],
        ];
    }
 
 /*********************************************/    

 public function getTotalControlData()    
   {
       $controlArray = array();
       
      $strSql= "SELECT id FROM  {{%control_sverka_header}}  ORDER BY onDate DESC, id DESC LIMIT 1";
      $list  =Yii::$app->db->createCommand($strSql)->queryAll();   
      if (count($list) == 0 ) $headerRef = 0;
                                     else $headerRef=$list[0]['id'];
        if (empty ($headerRef)) $headerRef = 0; 
       
       $headerPrevRef = 0;
       
       $strSql ="SELECT sum(initPrice) FROM {{%control_remains}} as a left join  {{%control_remains_use}} as b 
       on (a.scladTitle = b.scladTitle and a.usedOrgTitle = b.usedOrgTitle)";
       //  WHERE isPrevious = 0  and  b.scladIsUsedAll = 1
       
       
       
       $controlArray['scladReal']  = Yii::$app->db->createCommand($strSql."WHERE headerRef = ".$headerRef." and  b.scladIsUsedReal = 1")->queryScalar();
       $controlArray['scladAll']   = Yii::$app->db->createCommand($strSql."WHERE headerRef = ".$headerRef." and  b.scladIsUsedAll = 1")->queryScalar();
       $controlArray['scladDate']  = Yii::$app->db->createCommand('SELECT MAX(syncDate)        FROM {{%control_remains}} WHERE headerRef = ".$headerRef."  ')->queryScalar();

       $controlArray['scladRealPrev']  = Yii::$app->db->createCommand($strSql."WHERE headerRef = ".$headerPrevRef." and  b.scladIsUsedReal = 1")->queryScalar();
       $controlArray['scladAllPrev']   = Yii::$app->db->createCommand($strSql."WHERE headerRef =".$headerPrevRef." and  b.scladIsUsedAll = 1")->queryScalar();
       $controlArray['scladDatePrev']  = Yii::$app->db->createCommand('SELECT MAX(syncDate)        FROM {{%control_remains}} WHERE headerRef =".$headerPrevRef."   ')->queryScalar();
       
       
          
       $controlArray['supplyDate']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 108')->queryScalar();
       $controlArray['supplyReal'] = Yii::$app->db->createCommand('SELECT sum(supplySumm) FROM {{%supply}} WHERE supplyDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['scladDate']) ->queryScalar();       
       $controlArray['supplyAll'] = $controlArray['supplyReal'];

       $controlArray['supplyDatePrev']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 108')->queryScalar();
       $controlArray['supplyRealPrev'] = Yii::$app->db->createCommand('SELECT sum(supplySumm) FROM {{%supply}} WHERE supplyDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['scladDatePrev']) ->queryScalar();       
       $controlArray['supplyAllPrev'] = $controlArray['supplyReal'];
 
       

       $strSql ="SELECT sum(balanceSum) FROM {{%control_sverka_dolga}} as a left join  {{%control_sverka_dolga_use}} as b 
       on (a.usedOrgTitle = b.usedOrgTitle) LEFT JOIN {{%control_sverka_dolga_black}} as c on (a.orgTitle = c.orgTitle)";
                      
       $controlArray['clientDate']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_sverka_dolga}} WHERE dogType = 1 and headerRef = ".$headerRef." ")->queryScalar();       
       $controlArray['clientDebet'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum > 0 AND dogType = 1 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerRef." ")->queryScalar();
       $controlArray['clientCredit'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum < 0 AND dogType = 1 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerRef." ")->queryScalar();       
       $controlArray['clientAll'] = Yii::$app->db->createCommand($strSql." WHERE  dogType = 1 and b.inUse = 1 and headerRef = ".$headerRef." ")->queryScalar();
       
       $controlArray['clientDatePrev']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_sverka_dolga}} WHERE dogType = 1 and headerRef = ".$headerPrevRef." ")->queryScalar();       
       $controlArray['clientDebetPrev'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum > 0 AND dogType = 1 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerPrevRef." ")->queryScalar();
       $controlArray['clientCreditPrev'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum < 0 AND dogType = 1 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerPrevRef." ")->queryScalar();       
       $controlArray['clientAllPrev'] = Yii::$app->db->createCommand($strSql." WHERE  dogType = 1 and b.inUse = 1 and headerRef = ".$headerPrevRef." ")->queryScalar();
              

              
       $controlArray['supplierDate']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_sverka_dolga}} WHERE dogType = 2 and headerRef = ".$headerRef." ")->queryScalar();       
       $controlArray['supplierDebet'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum > 0 AND dogType = 2 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerRef." ")->queryScalar();
       $controlArray['supplierCredit'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum < 0 AND dogType = 2 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerRef." ")->queryScalar();       
       $controlArray['supplierAll'] = Yii::$app->db->createCommand($strSql." WHERE  dogType = 2 and b.inUse = 1 and headerRef = ".$headerRef." ")->queryScalar();
                            

       $controlArray['supplierDatePrev']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_sverka_dolga}} WHERE dogType = 2 and headerRef = ".$headerPrevRef." ")->queryScalar();       
       $controlArray['supplierDebetPrev'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum > 0 AND dogType = 2 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerPrevRef = ".$headerRef." ")->queryScalar();
       $controlArray['supplierCreditPrev'] = Yii::$app->db->createCommand($strSql." WHERE balanceSum < 0 AND dogType = 2 and b.inUse = 1 and ifnull(c.isBlack,0)=0 and headerRef = ".$headerPrevRef." ")->queryScalar();       
       $controlArray['supplierAllPrev'] = Yii::$app->db->createCommand($strSql." WHERE  dogType = 2 and b.inUse = 1 and headerRef = ".$headerPrevRef." ")->queryScalar();
                                          

       $strSql ="SELECT sum(cashSum) FROM {{%control_bank}} as a left join  {{%control_bank_use}} as b 
       on (a.usedOrgTitle = b.usedOrgTitle)";
                                   
       
       $controlArray['cashDate']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_bank}} WHERE headerRef = ".$headerRef." ")->queryScalar();
       $controlArray['cashReal']  = Yii::$app->db->createCommand($strSql." WHERE b.inUseReal = 1 and headerRef = ".$headerRef." ")->queryScalar();
       $controlArray['cashAll']   = Yii::$app->db->createCommand($strSql." WHERE b.inUseAll = 1 and headerRef = ".$headerRef." ")->queryScalar();

       $controlArray['cashDatePrev']  = Yii::$app->db->createCommand("SELECT max(syncDate) FROM {{%control_bank}} WHERE headerRef = ".$headerPrevRef." ")->queryScalar();
       $controlArray['cashRealPrev']  = Yii::$app->db->createCommand($strSql." WHERE b.inUseReal = 1 and headerRef = ".$headerPrevRef." ")->queryScalar();
       $controlArray['cashAllPrev']   = Yii::$app->db->createCommand($strSql." WHERE b.inUseAll = 1 and headerRef = ".$headerPrevRef." ")->queryScalar();
                                          
//                                          
                                                        
       $controlArray['oplateDate']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 107')->queryScalar();
       $controlArray['oplateReal'] = Yii::$app->db->createCommand('SELECT sum(oplateSumm) FROM {{%oplata}} WHERE oplateDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['clientDate']) ->queryScalar();       
       $controlArray['oplateAll'] = $controlArray['oplateReal'];


       $controlArray['oplateDatePrev']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 107')->queryScalar();
       $controlArray['oplateRealPrev'] = Yii::$app->db->createCommand('SELECT sum(oplateSumm) FROM {{%oplata}} WHERE oplateDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['clientDatePrev']) ->queryScalar();       
       $controlArray['oplateAllPrev'] = $controlArray['oplateReal'];
               

               
       $controlArray['supOplateDate']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 118')->queryScalar();
       $controlArray['supOplateReal'] = Yii::$app->db->createCommand('SELECT sum(oplateSumm) FROM {{%supplier_oplata}} WHERE oplateDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['supplierDate']) ->queryScalar();       
       $controlArray['supOplateAllP'] = $controlArray['supOplateReal'];
                      
       
       $controlArray['supOplateDatePrev']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 118')->queryScalar();
       $controlArray['supOplateRealPrev'] = Yii::$app->db->createCommand('SELECT sum(oplateSumm) FROM {{%supplier_oplata}} WHERE oplateDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['supplierDatePrev']) ->queryScalar();       
       $controlArray['supOplateAllPrev'] = $controlArray['supOplateReal'];
   
   
   
       $controlArray['supSupplyDate']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 116')->queryScalar();
       $controlArray['supSupplyReal'] = Yii::$app->db->createCommand('SELECT sum(wareSumm) FROM {{%supplier_wares}} WHERE requestDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['supplierDate']) ->queryScalar();       
       $controlArray['supSupplyAll'] = $controlArray['supplyReal'];

       $controlArray['supSupplyDatePrev']  = Yii::$app->db->createCommand('SELECT keyValue FROM {{%config}} WHERE id = 116')->queryScalar();
       $controlArray['supSupplyRealPrev'] = Yii::$app->db->createCommand('SELECT sum(wareSumm) FROM {{%supplier_wares}} WHERE requestDate >= DATE(:date) ' )
       ->bindValue(':date', $controlArray['supplierDatePrev']) ->queryScalar();       
       $controlArray['supSupplyAllPrev'] = $controlArray['supplyReal'];
       
          
   return $controlArray;
   }    
/*********************************************/    

 
 
 
  
  /*****************************/
  /*****************************/

  
  
   /** end of object **/     
 }
