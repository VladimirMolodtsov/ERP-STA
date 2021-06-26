<?php

namespace app\modules\zadarma\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\db\Expression;

use Zadarma_API\Api;
//require_once __DIR__.DIRECTORY_SEPARATOR.'include.php';
//require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Client.php';

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'zadarma'.DIRECTORY_SEPARATOR.'user-api-v1'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Client.php';

/**
 * ColdMainForm - модель стартовой формы менеджера холодных звонков
 */

 
 class ZadarmaMainForm extends Model
{
    
    public $key    = 'f780350c17f1d089f436';
    public $secret = 'e669adc605f477c06aa2';

    public $debug;
    
    public function rules()
    {
        return [            
            //[[ ], 'default'],                        
            //[['' ], 'safe'],            
        ];
    }

  /**************************/
  public function getRecord($callId, $pbxCallId)
  {
     $api = new \Zadarma_API\Client($this->key, $this->secret, false);
     $urltemplate= "https://my.zadarma.com/mypbx/stat/download/?id=5d89c715c31fae3d444e80ea&sn=sounds&name=";     
          
     $param= [
     'call_id' => $callId,
     'pbx_call_id' => $pbxCallId,
     'lifetime' => 3600,     
     ];
     
     $answer =   $api->call('/v1/pbx/record/request/', $param);
     $answerObject = json_decode($answer);
  
     if ($answerObject->status != 'success')   
     {
        Yii::$app->db->createCommand('UPDATE {{%ats_log}} set is_recorded = 0 where (pbx_call_id=:pbx_call_id OR call_id_with_rec =:call_id_with_rec)',
        [':pbx_call_id'=>$pbxCallId,':call_id_with_rec'=>$callId,  ])->queryScalar();                
        return  $answerObject;
     }
        
     $N = count($answerObject->links);
     for ($i=0; $i<$N; $i++ )
     {
        $parse=explode("/",$answerObject->links[$i]);    
        $pN=count($parse);
        if ($pN == 0) continue;        
        $answerObject->rawlinks[$i]=$urltemplate.$parse[$pN-1];         
     }
     
     return  $answerObject;
  }


  public function getBalance()
  {
     $api = new \Zadarma_API\Api($this->key, $this->secret, false);
     //$answer =   $api->call('/v1/info/balance/');
     //$answerObject = json_decode($answer);
     //if ($answerObject->status == 'success')   
         
     $answer =   $api->getBalance();
     return  $answer;
  }

  
  public function getDirectNumbersStatus()
  {
     $api = new \Zadarma_API\Client($this->key, $this->secret, false);
     $answer =   $api->call('/v1/direct_numbers/');

      $answerObject = json_decode($answer);

      if ($answerObject->status == 'success')   return  $answerObject;

     
    return false;
  }

 public function getPbxInternal()
  {
     $api = new Api($this->key, $this->secret, false);
     $answer =   $api->getPbxInternal();

    return ($answer); 
     
      $answerObject = json_decode($answer);

      
      
      if ($answerObject->status == 'success')   return  $answerObject;

     
    return false;
  }

  
  public function getPbxStatus($pbx)
  {
     $api = new Api($this->key, $this->secret, false);
     $answer =   $api->getPbxStatus($pbx);

    return ($answer); 
     
      $answerObject = json_decode($answer);

      
      
      if ($answerObject->status == 'success')   return  $answerObject;

     
    return false;
  }

  public function getRedirections()  
  {

     return 0;
  }



  
  /**************************/    
  
  
  /************End of model*******************/ 
 }
