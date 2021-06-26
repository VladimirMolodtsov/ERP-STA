<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\bank\models\AuroraExtract;


/**

 */
class AuroraController extends Controller
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
        
       echo "USAGE yii aurora \n";
        return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        $model = new AuroraExtract();     
        $model -> getExtractAttach();        
        return ExitCode::OK;
    }
}
