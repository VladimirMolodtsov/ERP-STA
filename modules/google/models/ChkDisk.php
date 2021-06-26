<?php

namespace app\modules\yandex\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\yandex\models\DiskApi;
use app\modules\bank\models\TblDocuments;

/**
 * ChkDisk - проверяем upload и перемещаем
 https://snipp.ru/php/disk-yandex
 */

 
 class ChkDisk extends Model
{
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

  /**************************/
  
  public function chkUpload()
  {
   $res = [
     'res' => false,
     'docFind'  => 0,
     'docMoved' => 0,
     'docCreated' => 0,
     'destFolder' => '',   
     'err' => [],
     'errArray' => [],
   ];
   
   
    $strSql = "SELECT max(docIntNum) from {{%documents}} ORDER BY id"; 
    $maxNum =  Yii::$app->db->createCommand($strSql)->queryScalar();                    
    $maxNum++;

   $diskApi= new DiskApi();
   $curY=date("Y");
   $curM=date("m");
   
   $destFolder = '/DOCUMENTS/'.$curY.'/'.$curM.'/';
   $res['destFolder']=$destFolder;
   /*Проверяем год*/
   $yearList = $diskApi->getFolderContent('/DOCUMENTS/');   
   $N=count($yearList['_embedded']['items']);
   $find=false;   
   for($i=0;$i<$N;$i++)
   {
    $yearFolder= $yearList['_embedded']['items'][$i];
    if ($yearFolder['name'] == $curY ) {$find=true; break;}   
    
   }     
   if(!$find) {
       $r=$diskApi->createFolder('/DOCUMENTS/'.$curY);
       if(!empty($r['error'])) {$res['err'][]='Error while create year folder'; return $res;}
   }
   
   //проверяем месяц
   $monthList = $diskApi->getFolderContent('/DOCUMENTS/'.$curY.'/');   
   $N=count($monthList['_embedded']['items']);
   $find=false;   
   for($i=0;$i<$N;$i++)
   {
    $monthFolder= $monthList['_embedded']['items'][$i];
    if ($monthFolder['name'] == $curM ) {$find=true; break;}   
    
   }     
   if(!$find) {
       $r=$diskApi->createFolder('/DOCUMENTS/'.$curY.'/'.$curM);
       if(!empty($r['error'])) {$res['err'][]='Error while create month folder'; return $res;}
   }
   

   /*проверяем папку UPLOAD*/   
   $uploadList = $diskApi->getFolderContent('/UPLOAD/');   
   $N=count($uploadList['_embedded']['items']);   
   for($i=0;$i<$N;$i++)
   {
    $fileList= $uploadList['_embedded']['items'][$i];
    if($fileList['type']!='file') continue;    
    $res['docFind']++;            
       $srcPath ='/UPLOAD/'.$fileList['name'];
       $dstPath =$destFolder.$fileList['name'];
       $r=$diskApi->moveFile($srcPath, $dstPath);       
    if (empty($r['error']))  {
    $res['docMoved']++;       
    $docRec = new TblDocuments();
    if ( empty($docRec) ) {$res['err'][]='Error while document creation'; $res['errArray'][]=$r; continue;  }
        $docRec->docURIType = 1;
        $docRec->docURI = $dstPath;
        $docRec->docIntNum = $maxNum;
        $maxNum++;
        $docRec->save();
    $res['docCreated']++;                
    }   
    else {$res['err'][]='Error while move file'; $res['errArray'][]=$r;}
   }     
    return  $res;  
  }

  /************End of model*******************/ 
 }
