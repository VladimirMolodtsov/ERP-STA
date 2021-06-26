<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\bank\models\GetExtract;


/**

 */
class BankController extends Controller
{
    
    
    public function options($actionID)
    {      
        return [
        
        ];
    }
    
    public function optionAliases()
    {
        return [
        ];
    }

    public function actionHelp()
    {
        
       echo "USAGE yii bank \n";
        return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        $model = new GetExtract();     
        $ret=$model -> getExtractAttach();        
        $model -> postProcessExtract();        
        //print_r($ret);
        return ExitCode::OK;
    }
}
