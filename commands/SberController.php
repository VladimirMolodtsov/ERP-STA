<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\bank\models\SberExtract;


/**

 */
class SberController extends Controller
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
        
       echo "USAGE yii sber \n";
        return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        $model = new SberExtract();     
        $model -> getExtractAttach();        
        return ExitCode::OK;
    }
}
