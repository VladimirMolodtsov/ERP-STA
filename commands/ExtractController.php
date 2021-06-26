<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\bank\models\ClientBankExchange;


/**

 */
class ExtractController extends Controller
{
            
    public $file ="";
    
    public function options($actionID)
    {      
        return [
        'file',
        'help'
        ];
    }
    
    public function optionAliases()
    {
        return [
        'h' => 'help',
        'f' => 'file'
        ];
    }

    public function actionHelp()
    {        
       echo "USAGE yii extract --file=extratct.txt \n";
       return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        if (empty($this->file)) {
            $this->actionHelp();
            return ExitCode::OK;
        }        
        $model = new ClientBankExchange();     
   //     $model -> loadFileExchange($this->file);        
  //      $model -> save();
        $model -> createTest();
        $model -> saveFileExchange("1c_to_kl.txt");
  
        if (count($model -> errKeys) > 0){
            echo "Error keys\n";
            print_r($model -> errKeys);
        }
        echo "data:\n";
        //print_r ($model->documentArray);
        return ExitCode::OK;
    }
}
