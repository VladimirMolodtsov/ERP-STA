<?php

namespace app\modules\google\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use app\modules\bank\models\TblDocuments;


require(__DIR__ . '/../../../vendor/autoload.php');

use google\apiclient;
//use google\apiclient-services;

use \Google_Client;
use \Google_Service_Drive;
//use \Google_Service_Drive_ParentReference;
use \Google_Service_Drive_DriveFile;
//require_once __DIR__.'/vendor/google-api-php-client/src/Google_Client.php';
//require_once __DIR__.'/vendor/google-api-php-client/src/contrib/Google_DriveService.php';

/**
 * DiskApi - доступ
*/
 
/**

986235411442-ora5no99cthpsrrsc1kjbs8acpqlaasg.apps.googleusercontent.com
ptZyVLJowB7-IBmT9eCaR_9S

*/ 
 
 class DiskApi extends Model
{
    public $debug;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

    
    
  public function test()
  {
    $session = Yii::$app->session;          
    $session->open();

  
    $client = new \Google_Client();
    $client->setClientId('986235411442-ora5no99cthpsrrsc1kjbs8acpqlaasg.apps.googleusercontent.com');
    $client->setClientSecret('ptZyVLJowB7-IBmT9eCaR_9S');
    $client->setRedirectUri('http://127.0.0.1/phone/web/index.php?r=/google/api');
    
    $client->addScope(\Google_Service_Drive::DRIVE);
    $client->setAuthConfig(__DIR__."/../credential.json");
        // Запрос на подтверждение работы с Google-диском
    if (isset($_REQUEST['code'])) {
          $token = $client->authenticate($_REQUEST['code']);
          $session->set('accessToken', $token);
          header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        } elseif (!isset($_SESSION['accessToken'])) {
          header('Location:' . filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));
    }
    // Присваиваем защитный токен для работы с Google-диском
        $token = $session->get('accessToken', '');
        $client->setAccessToken($token);
        /*$driveService = new \Google_Service_Drive($client);
        $listFiles = $driveService->files->listFiles([
          'fields' => 'nextPageToken, files(id, name, parents, fileExtension, mimeType, size, iconLink, thumbnailLink, webContentLink, webViewLink, createdTime)'
        ]);

     echo "<pre>";
      print_r($listFiles);        
     echo "</pre>";   */
     
     $service = new \Google_Service_Drive($client);
     // Print the names and IDs for up to 10 files.
     
$optParams = array(
  'pageSize' => 10,
  'fields' => 'nextPageToken, files(id, name)'
);
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) {
    print "No files found.\n";
} else {
    print "Files:\n";
    foreach ($results->getFiles() as $file) {
        printf("%s (%s)\n", $file->getName(), $file->getId());
    }
}
     
  }  
/*
$file = new Google_Service_Drive_DriveFile();
$file->setName(uniqid().'.jpeg');
$file->setParents(['1ahzeTOXf1PU6-Q6Vz3BpmxxsJnHXEs4S']); 
$createdFile = $service->files->create($file, array(
   'data' => file_get_contents('photo.jpeg');,
   'mimeType' => 'image/jpeg',
   'uploadType' => 'multipart'
));

  'fields' => 'nextPageToken, files(id, name, , fileExtension, mimeType, size, iconLink, thumbnailLink, webContentLink, webViewLink, createdTime)'
*/    
   public function gtest()
   {
   
   // Get the API client and construct the service object.
    $client = $this->getClient();
    $service = new Google_Service_Drive($client);
    $scanFolder ='1gE5sXaAFBVFDWdPK3M5d0e4QWyxyMB-P';
    $arcFolder  ='1gtKON6nVtFXRiBJFQWJz-nK2EeCENLvA';
    
  //  $parent = new Google_Service_Drive_ParentReference();
  //  $parent->setId('1gtKON6nVtFXRiBJFQWJz-nK2EeCENLvA');

   /*
    $parent = new Google_Service_Drive_ParentReference();
    $parent->setId(getParentDirectoryId($service, $destinationFolder));
    $file->setParents(array($parent));
   */

    // Print the names and IDs for up to 10 files.
    $optParams = array(
      'pageSize' => 1000,
   //   'q' => "mimeType='image/jpeg'",
      'q' => "parents='".$scanFolder."'",
      'fields' => 'nextPageToken, files(id,  parents, name)'
    );
    $results = $service->files->listFiles($optParams);

    
    
echo "<pre>";
//print_r($service);
   $emptyFileMetadata = new Google_Service_Drive_DriveFile();

    if (count($results->getFiles()) == 0) {
        print "No files found.\n";
    } else {
    print "Files:\n";
      foreach ($results->getFiles() as $file) {
        printf("%s (%s)\n",$file->getName(), $file->getId());        
//        $file->setParents(array($parent));


       $nfile = $service->files->update($file->getId(), $emptyFileMetadata, array(
        'addParents' => $arcFolder,
        'removeParents' => $scanFolder,
        'fields' => 'id, parents'));
        printf("New %s (%s)\n",$nfile->getName(), $nfile->getId());

      }
    }
    $pageToken = $results->getNextPageToken();
    echo "next:[".$pageToken."]\n";
    
  
    
echo "</pre>";    
   }

   
function moveFile($service, $fileId, $newParentId) {
  try {

    $emptyFileMetadata = new Google_Service_Drive_DriveFile();
    // Retrieve the existing parents to remove
    $file = $service->files->get($fileId, array('fields' => 'parents'));
    $previousParents = join(',', $file->parents);
    // Move the file to the new folder
    $file = $service->files->update($fileId, $emptyFileMetadata, array(
      'addParents' => $newParentId,
      'removeParents' => $previousParents,
      'fields' => 'id, parents'));

    return $file;
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
}
   
   public function scanDisk()
   {
      $ret = [      
      'res' => false,
      'docAdd' => 0,
      'docScan' => 0,
      ]; 
       
      $strSql = "SELECT max(docIntNum) from {{%documents}} ORDER BY id"; 
      $maxNum =  Yii::$app->db->createCommand($strSql)->queryScalar();                    
      $maxNum++;
         
   // Get the API client and construct the service object.
    $client = $this->getClient();
    $service = new Google_Service_Drive($client);
    $scanFolder ='1gE5sXaAFBVFDWdPK3M5d0e4QWyxyMB-P';
    $arcFolder  ='1gtKON6nVtFXRiBJFQWJz-nK2EeCENLvA';
    $emptyFileMetadata = new Google_Service_Drive_DriveFile();

    
    // Print the names and IDs for up to 10 files.
    $optParams = array(
      'pageSize' => 1000,
   //   'q' => "mimeType='image/jpeg'",
      'q' => "parents='".$scanFolder."'",
      'fields' => 'nextPageToken, files(id,  parents, name)'
    );
    
    $results = $service->files->listFiles($optParams);
    if (count($results->getFiles()) == 0) return $ret;
    /*Vv known problem - paging!!!! */
    foreach ($results->getFiles() as $file) {
        $uri= "https://drive.google.com/open?id=".$file->getId();
        $ret['docScan']++;
         $record= TblDocuments::findOne([
         'docURI' => $uri,
         ]);        
        if (!empty($record)) continue;
         $record= new TblDocuments();
         if (empty($record)) continue;
         $record->docURI = $uri;
         $record->docOrigDate=date('Y-m-d');       
         $record->docIntNum = $maxNum;
         $record->docNum = "";
         $record->docOwner = "";
         $maxNum++;
         $record->save();

       $nfile = $service->files->update($file->getId(), $emptyFileMetadata, array(
        'addParents' => $arcFolder,
        'removeParents' => $scanFolder,
        'fields' => 'id, parents'));

         $ret['docAdd']++;        
    }
        
    $ret['res']= true;
    return $ret;  
   }

  
   
   
/******************/
function retrieveAllFiles($service) {
  $result = array();
  $pageToken = NULL;

  do {
    try {
      $parameters = array();
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $files = $service->files->listFiles($parameters);

      $result = array_merge($result, $files->getItems());
      $pageToken = $files->getNextPageToken();
    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
      $pageToken = NULL;
    }
  } while ($pageToken);
  return $result;
}   
   
   
   
/******************/   
   public function getClient()
  {
    
    $client = new \Google_Client();
    $client->setApplicationName('Erp systems');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig(__DIR__."/../credential.json");
    $client->setRedirectUri('http://127.0.0.1/phone/web/index.php?r=/google/api');

    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = __DIR__."/../upload/token.json";
    /*if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }*/

    
    $client->setAccessType("offline");
    if($client->isAccessTokenExpired()){
    $client->revokeToken();
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    // If there is no previous token or it's expired.
    /*if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }*/
    return $client;
}


   public function getClientO()
  {
    
    $client = new Google_Client();
    $client->setApplicationName('Erp systems');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig(__DIR__."/../credential.json");
   /* $redirect_uri = "http://{$_SERVER['HTTP_HOST']}/";
    $client->setRedirectUri($redirect_uri);*/
    $client->setRedirectUri('http://127.0.0.1/phone/web/index.php?r=/google/api');

    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = __DIR__."/../upload/token.json";
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    
    $client->setAccessType("offline");
    if($client->isAccessTokenExpired()){
    $client->revokeToken();
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}
    
    
    

    public function subtest()
    {

        $client = new \Google_Client();
      //  $client =new \Google\Client();
        $client->setApplicationName("ERP Systems");
        $client->addScope(\Google_Service_Drive::DRIVE);
      //  $client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);

      $client->setAuthConfig(__DIR__."/../credential.json");
      //echo __DIR__;
      //$lines=file (__DIR__."/../credential.json");
        $driveService = new \Google_Service_Drive($client);
        $listFiles = $driveService->files->listFiles([
          'fields' => 'nextPageToken, files(id, name, parents, fileExtension, mimeType, size, iconLink, thumbnailLink, webContentLink, webViewLink, createdTime)'
        ]);

        
        if (isset($_REQUEST['code'])) {
          $token = $client->authenticate($_REQUEST['code']);
          $_SESSION['accessToken'] = $token;
          header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        } elseif (!isset($_SESSION['accessToken'])) {
          header('Location:' . filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));
        }
        
        
        echo "<pre>";
        print_r($listFiles);
        
        echo "</pre>";
        
    }

    function sample()
    {
  /*  $request = $service->files->insert($file); // Create a media file upload to represent our upload process. 
    $media = new Google_Http_MediaFileUpload( $client, $request, 'text/plain', null, true, $chunkSizeBytes );
     $media->setFileSize(filesize(TESTFILE)); // Upload the various chunks. $status will be false until the process is // complete. 
     $status = false; 
     $handle = fopen(TESTFILE, "rb");
      while (!$status && !feof($handle)) 
      {
       $chunk = fread($handle, $chunkSizeBytes); 
       $status = $media->nextChunk($chunk); 
      } //Here you will get the new created folder's id echo "<pre>";
       
      var_dump($status->id); exit; 
  
      if ($client->getAccessToken()) 
      { 
      $file = new Google_Service_Drive_DriveFile(); $file->title = "New File By Bikash 111"; 
      // To create new folder 
      $file->setMimeType('application/vnd.google-apps.folder'); 
      // To set parent folder 
      
      $parent = new Google_Service_Drive_ParentReference(); 
      $parent->setId('0B5kdKIFfgdfggyTHpEQlpmcExXRW8'); 
      $file->setParents(array($parent)); 
      
      $chunkSizeBytes = 1 * 1024 * 1024; 
      // Call the API with the media upload, defer so it doesn't immediately return. 
      $client->setDefer(true); 
      $request = $service->files->insert($file); 
      // Create a media file upload to represent our upload process.
       $media = new Google_Http_MediaFileUpload( $client, $request, 'text/plain', null, true, $chunkSizeBytes ); 
       $media->setFileSize(filesize(TESTFILE)); 
       // Upload the various chunks. 
       $status will be false until the process is // complete. 
       $status = false;
        $handle = fopen(TESTFILE, "rb"); 
        while (!$status && !feof($handle))
         { 
         $chunk = fread($handle, $chunkSizeBytes); 
         $status = $media->nextChunk($chunk); 
         } 
         //Here you will get the new created folder's id 
        echo "<pre>";
        var_dump($status->id); 
        exit; 
     } 
    
    */
    }
    

  /************End of model*******************/ 
 }
