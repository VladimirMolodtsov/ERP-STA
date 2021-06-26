<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\zadarma\models\ZadarmaAtsState;


/**

 */
class AtsController extends Controller
{
    
    
    public function options($actionID)
    {      
        return [
        'id',
        ];
    }
    
    public function optionAliases()
    {
        return [
        'i' => 'id',
        ];
    }

    public function actionHelp()
    {
        
       echo "USAGE yii ats --id=id
       --id id of record \n";
        return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        $model = new ZadarmaAtsState();        
        $model->rescanAllLog();        
        return ExitCode::OK;
    }
}
