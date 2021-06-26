<?php

namespace app\modules\yandex\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;


/**
 * DiskApi - доступ к яндекс диску
 https://snipp.ru/php/disk-yandex
 */

 
 class DiskApi extends Model
{
   public $login='avrora-cta-rtb@yandex.ru';
   //2133893AB$@
   public $passwd = 'GT6gT3Czrs';
   
   //https://avrora-cta-rtb:GT6gT3Czrs@webdav.yandex.ru/DOCUMENTS/2021/02/123.jpg"
    
    //public $token    = 'AgAAAAAhKGICAAbYDH9X7HrJM0cTr-Z4tw7wxQw';
    public $token    = 'AgAAAABQQYwNAAbYDMmsb-4BQUOplYBFXeQ_kbQ';    
    //https://oauth.yandex.ru/authorize?response_type=token&client_id=45ea708c59d1430eb2ec020b0beab383
    public $id       = '45ea708c59d1430eb2ec020b0beab383';

    public $debug;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

  /**************************/
  
  public function getInfo()
  {
 
    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
 
    $res = json_decode($res, true);
    return  $res;
      
  }
    
/**************************/    
public function getFolderContent($path)
  {
 
  // Выведем список корневой папки.

    //$path = '/';
    // Оставим только названия и тип.
    $fields = '_embedded.items.name,_embedded.items.type';
    $limit = 100;

    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources?path=' . urlencode($path) . '&fields=' . $fields . '&limit=' . $limit);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($res, true);
    return  $res;
      
  }
  
  /**************************/    
  public function createFolder($path)
  {
    
    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/?path=' . urlencode($path));
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
 
    $res = json_decode($res, true);
    return  $res;
      
  }


/**************************/    
  public function copyFile($srcPath, $dstPath)
  {
    
    /*$srcurl = 'https://cloud-api.yandex.net/v1/disk/resources/copy?from='.urlencode($srcPath).'&path='.urlencode($dstPath);
    $ch = curl_init($srcurl ); 
       
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    
    */
  //  $from = '/photo/123.jpg';
  //  $path = '/111/123.jpg';
    $from = urlencode($srcPath);
    $path = urlencode($dstPath);
    $token =  $this->token;

    $curl2 = curl_init();
    curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
    curl_setopt($curl2, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources/copy?from='.$from.'&path='.$path);
    curl_setopt($curl2, CURLOPT_POST, true);
    curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($curl2);
    curl_close($curl2);
 
 
    $res = json_decode($result , true);
 //   $res['srcurl']=$srcurl ;
    return  $res;
      
  }  
 
  
 /**************************/    
  public function moveFile($srcPath, $dstPath)
  {
    
    //$srcurl = 'https://cloud-api.yandex.net/v1/disk/resources/move?from='.urlencode($srcPath).'&path='.urlencode($dstPath)
    //$ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/move'); 
  /*  $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/move'); 
   $data =[
     'from'=>$srcPath,
     'path'=>$dstPath
   ];
    
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);*/
    
    /*$from = '/photo/123.jpg';
    $path = '/111/123.jpg';*/
    $from = urlencode($srcPath);
    $path = urlencode($dstPath);
    $token =  $this->token;

    $curl2 = curl_init();
    curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
    curl_setopt($curl2, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources/move?from='.$from.'&path='.$path);
    curl_setopt($curl2, CURLOPT_POST, true);
    curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($curl2);
    curl_close($curl2);
 
    
 
    $res = json_decode($result, true);    
    //$res['srcurl']=$srcurl ;
    return  $res;
      
  }  
  /**************************/    
  public function placeFile($file, $path)
  {
    
    // Запрашиваем URL для загрузки.
    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/upload?path=' . urlencode($path ."/".basename($file)));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch); 
    $res = json_decode($res, true);
    if (empty($res['error'])) {
	// Если ошибки нет, то отправляем файл на полученный URL.
	$fp = fopen($file, 'r');
 
 	$ch = curl_init($res['href']);
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_UPLOAD, true);
	curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
	curl_setopt($ch, CURLOPT_INFILE, $fp);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
 
	if ($http_code == 201) {
		echo 'Файл успешно загружен.';
	}
    }
    return $res;    
    
  }
 

 /**************************/    
  public function getFileUri($yd_file)
  {
    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/download?path=' . urlencode($yd_file));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    $ret = json_decode($res, true);
    if (empty($ret['error']))$ret['res'] = true;
    else $ret['res'] = false;
    return $ret;
  }
  /**************************/      
  public function getFileShare($yd_file)
  {
  
    $curl2 = curl_init();
    curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$this->token));
    curl_setopt($curl2, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources/publish?path='.urlencode($yd_file));
    curl_setopt($curl2, CURLOPT_PUT, true);
    curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($curl2);
    $info = curl_getinfo($curl2);    
    curl_close($curl2);
    $ret = json_decode($result, true);
    $ret['info']=$info;
    return $ret;
  }
  
/*
Через webDav
$do = 1; //действие над файлом: 1 для publish и 0 для unpublish	
*/  
  
public function publicFile($file, $do){
	//логин и пароль от Яндекса
	$login = $this->login; //можно и без @yandex.ru
	$password =  $this->passwd;
	
	$headers = array("Authorization: Basic " . base64_encode($login . ":" . $password)); //формируем заголовки для успешной авторизации
	$dothis = $do ? 'publish' : 'unpublish';
	 
	$file = str_replace(' ', '%20', $file);
	$curl = curl_init('https://webdav.yandex.ru'.$file.'?'.$dothis);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl,CURLOPT_HEADER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $file.'?'.$do);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_exec($curl);
	    
	 $info = curl_getinfo($curl);
	
	$ret=[
	'res' => false,
	'share' => 0,
	'url' => '',
	'info' =>[],
	];
	$ret['info']=$info;
	
	if ($do && $info['http_code'] == 302) {
        $ret['res']=true;
        $ret['share']=1;
        $ret['url']=$info['redirect_url'];	   
	} elseif ($info['http_code'] == 200) {
        $ret['res']=true;
        $ret['share']=0;
	}

    return $ret;
	
}

  
  
  /**************************/    
  public function getFile($yd_file)
  {
   $ret = [
     'res' => false,
     'error' => '',
     'href'  => '', 
     'yandex_ref' => '',
   ];
    $ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources/download?path=' . urlencode($yd_file));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($res, true);
    if (!empty($res['error'])) {
         $ret ['error'] =  $res['error'];
         return $ret;
    }
        $path = realpath(dirname(__FILE__))."/../uploads/";
        //$mask=$path.'*';
        //array_map("unlink", glob($mask));       
        $file_name = $path . basename($yd_file);
        //$file = @fopen($file_name, 'w'); 
        $file = @fopen($file_name, 'w'); 
        $ch = curl_init($res['href']);
        $ret ['yandex_ref'] =  $res['href'];
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' .  $this->token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($file);

        if($http_code == 200 ){
          $ret ['res'] =  true;
          $ret ['href'] = "/yandex/../uploads/".basename($yd_file);          
         }
         else {
             $ret ['error'] ='Bad ref';            
             }
         
    return $ret;
  }
  
/**************************/    
  public function delFile($file, $path)
  {
    
  
// Файл или папка на Диске.
$path = $path.'/'.$file;
 
$ch = curl_init('https://cloud-api.yandex.net/v1/disk/resources?path=' . urlencode($path) . '&permanently=true');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
 
    $res = json_decode($res, true);
    return $res;
    
  }
  
  
  /************End of model*******************/ 
 }
