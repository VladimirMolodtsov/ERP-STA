<?php
/**

 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\DataConsoleSync;
use app\models\DataSyncGoogle;
use app\models\DataSync;
use app\models\OrgContactForm;

use app\models\MailForm;
use app\models\ContractsEditForm;

use app\modules\bank\models\BankOperation;  
use app\modules\bank\models\BankExtractAssign;
use app\modules\google\models\DiskApi;

/**

 */
class DataController extends Controller
{
    public $st = 0;
    public $et = 0;
        
    public $sd ="";
    public $ed ="";
    
    public $fname ="";
    
    public $inited=0;
    
    public function myinit()
    {
        
        if ($this->inited > 0) return;
        
      //  echo "From ".$this->sd. " to ".$this->ed."\n";
        
        if (!empty($this->sd)) $this->st = strtotime($this->sd);
        if (!empty($this->ed)) $this->et = strtotime($this->ed);
        
        if (empty($this->st)) $this->st = time()-24*3600; //за предыдущий день
        if (empty($this->ed)) $this->et = time()+24*3600;
        
        
        $this->inited = 1;
    }
    
    public function options($actionID)
    {      
        return [
        'sd',
        'ed',
        'fname'
          ];

    }
    
    public function optionAliases()
    {
        return [
        's' => 'sd',
        'e' => 'ed',
        ];
    }

    public function actionHelp()
    {
        
       echo "USAGE yii data --sd=YYYY-mm-dd --ed=YYYY-mm-dd 
       --sd (-s) - start date (default now() ).
       --ed (-e) - end date   (default now()+1 ).       
       ";
        return ExitCode::OK;
    }
        
    
    public function actionIndex()
    {
        $this->myinit();
        $this->actionSyncPurch();
        $this->actionSyncSale();
        $this->actionSyncProfit();
        $this->actionSyncBankOp();
        
        $this->actionSyncSclad();
        $this->actionSyncSverka();
        $this->actionSyncBank();
        $this->actionSyncSupplierOplata();
        $this->actionSyncOplata();
        $this->actionSyncSupply();
        $this->actionSyncSchet();
        $this->actionSyncSupplierSchet();
        
        $model = new DataConsoleSync();   
        $model->startCheck($this->st);
        
        return ExitCode::OK;
    }
    
  
/*****************/    
    public function actionSyncPurch()
    {
       
        echo "sync Purch: \n";
        
        $this->myinit();
        $model = new DataSync();   
        $model->webSync = false;
        
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadPurchData(1, $ct);    
        }
       
        echo "sync complete\n";

        return ExitCode::OK;
    }
    
/*****************/    
    public function actionSyncSale()
    {
       
        echo "sync Sale: \n";
        
        $this->myinit();
        $model = new DataSync();   
        $model->webSync = false;

        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSaleData (1, $ct);    
        }
       
        echo "sync complete\n";

        return ExitCode::OK;
    }
    
/*****************/
    public function actionSyncProfit()
    {
        echo "sync Profit: \n";
        
        $this->myinit();
        $model = new DataConsoleSync();   

        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadProfitData(1, $ct);    
        }
       
        echo "sync complete\n";
        return ExitCode::OK;
    }

/*****************/
    public function actionSyncBankOp()
    {
        echo "sync Bank Operation: \n";
        
        $this->myinit();
        $model = new BankOperation();   
        $model->webSync = false;
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          echo "   ".date("d.m.Y", $ct)."\n";
          $start = $ct;//-24*3600;
          $end   = $ct+24*3600;
          $model-> syncOperations($start, $end, time());    
        }
       
        echo "sync complete\n";
        return ExitCode::OK;
    }
/*****************/
/*****************/
    public function actionSyncSchet()
    {
        echo "sync Schet list: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->syncAllUser = 1;
        $model->webSync = false;
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSchetBase(1, 0);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;

    }

    
/*****************/    
    public function actionSyncSupply()
    {
        echo "sync Supply list: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->syncAllUser = 1;
        $model->webSync = false;
        //for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          //$model->syncDate = date("Y-m-d", $ct);
          //echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSupplyBase(1, 0);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;
    }
/*****************/    

    public function actionSyncOplata()
    {
        echo "sync Oplata list: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->syncAllUser = 1;
        $model->webSync = false;
        //for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          //$model->syncDate = date("Y-m-d", $ct);
          //echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadOplataBase(1, 0);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;
    }
/*****************/    


public function actionSyncSupplierOplata()
    {         
        echo "sync Supplier Oplata list: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->syncAllUser = 1;
        $model->webSync = false;
        //for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          //$model->syncDate = date("Y-m-d", $ct);
          //echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSupplierOplata(1, 0);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }
/*****************/    

  public function actionSyncBank()
    {         
        echo "sync Bank data: \n";
        
        $this->myinit();
        $model = new DataSync();   
        $model->webSync = false;
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadBankData(1, $ct);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }

/*!*/    
  public function actionSyncSverka()
    {         
        echo "sync Sverka: \n";
        
        $this->myinit();
        $model = new DataSync();   
        $model->webSync = false;
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSverkaData(1, $ct);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }

/*!*/    
  public function actionSyncSclad()
    {         
        echo "sync Sclad: \n";
        
        $this->myinit();
        $model = new DataSync();   
        $model->webSync = false;
        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->syncSclad(1, $ct);
        }
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }
/***************/    
  public function actionSyncSupplierSchet()
    {         
        echo "sync Supplier Schet: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->webSync = false;

        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSupplierSchets(1, 1);
        }
 
          
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }

/***************/    
  public function actionSyncSupplierWares()
    {         
        echo "sync Supplier Wares document: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->webSync = false;

        for ($ct=$this->st ; $ct<$this->et; $ct+=24*3600 )
        { 
          $model->syncDate = date("Y-m-d", $ct);
          echo "   ".date("d.m.Y", $ct)."\n";
          $model->loadSupplierWares(1, 0);
        }
 
          
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }
    
  public function actionUpdateOrgSupplier()
    {         
        $model = new DataSyncGoogle();   
        $model->webSync = false;
 
          $model->updateSupplierList();
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }
  public function actionUpdateOrgClient()
    {         
        $model = new DataSyncGoogle();   
        $model->webSync = false;
 
          $model->updateClientList();
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }

/*****************/
    public function actionSyncBuhMonthStat()
    {
        $model = new DataConsoleSync();   
        $model->getBuhStatSyncDate();    
        echo "sync start\n";
        for ($i=0; $i<count($model->syncDates); $i++)
        {
          $this->st = $model->syncDates[$i]['st'];  
          $this->et = $model->syncDates[$i]['et']+24*3600;
        
        $this->actionSyncSchet();          
        $this->actionSyncSupply();
        $this->actionSyncSupplierSchet();
        $this->actionSyncOplata();
        $this->actionSyncSupplierOplata();
        $this->actionSyncProfit();
        $this->actionSyncBankOp();
        $this->actionSyncPurch();
        
        
        /**/
        $this->st = $model->syncDates[$i]['et'];  
        $this->et = $model->syncDates[$i]['et']+24*3600;
        $this->actionSyncSclad();
        $this->actionSyncSverka();
        $this->actionSyncBank();
        
        $this->actionScanExtractOp();
        }
        echo "sync complete\n";
        return ExitCode::OK;
    }

    
/*****************/
    public function actionScanExtractOp()
    {
        echo "start Scan: \n";
        $model = new BankExtractAssign();   
        $model->scanExtract(); 
        return ExitCode::OK;
    }    


    public function actionCleanSclad()
    {
        echo "start Scan: \n";
        $model = new DataSync();   
        $model->cleanSclad(); 
        return ExitCode::OK;
    }    
/*****************/
    public function actionImportWares()
    {
        
        if (empty ($this->fname)) {
            echo "No file\n";
            return ExitCode::OK;
        }
        
        $model = new DataConsoleSync();   
        $ret = $model->importWareList($this->fname); 
        if ($ret) echo "Success\n";
        else echo "Problem\n";
        return ExitCode::OK;
    }    

    public function actionImportProduction()
    {
        
        if (empty ($this->fname)) {
            echo "No file\n";
            return ExitCode::OK;
        }
        
        $model = new DataConsoleSync();   
        $ret = $model->importProdList($this->fname); 
        if ($ret) echo "Success\n";
        else echo "Problem\n";
        return ExitCode::OK;
    }    

    public function actionClassifyWare()
    {
        $model = new DataConsoleSync();   
        $ret = $model->classifyWare();        
        return ExitCode::OK;
    }    

    public function actionParseWare()
    {
        $model = new DataConsoleSync();   
        $ret = $model->parseWare();        
        return ExitCode::OK;
    }    

    public function actionLnkWare()
    {
        $model = new DataConsoleSync();   
        $ret = $model->lnkWare();        
        return ExitCode::OK;
    }    
    
    public function actionLeadStatus()
    {
        $model = new OrgContactForm();   
        $ret = $model->resetLeadStatus();        
        return ExitCode::OK;
    }    

/***************/    

  public function actionSyncSupplier()
    {         
        echo "sync Supplier: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->webSync = false;
 
          $model->loadSupplierBase(1, 1);
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }
    
  public function actionSyncClient()
    {         
        echo "sync Client: \n";
        
        $this->myinit();
        $model = new DataSyncGoogle();   
        $model->webSync = false;
 
          $model->loadClientBase(1, 1);
       
        echo "sync complete\n";
        return ExitCode::OK;    
    }

  public function actionImportOrgData()
    {
        
        if (empty ($this->fname)) {
            echo "No file\n";
            return ExitCode::OK;
        }
        
        $model = new DataConsoleSync();   
        $ret = $model->importOrgList($this->fname); 
        if ($ret) echo "Success\n";
        else echo "Problem\n";
        return ExitCode::OK;
    }    

  public function actionSyncMail()
  {
            $model = new MailForm();
                       
            $model->getInboxMailList();            
            $model->getSentMailList();    
    
  }    
    /*********/
    public function actionSyncOtves()
    {
        $model = new DataSync();
        $model ->loadGoogleOtvesData();
        return ExitCode::OK;

       }

/*********/
    public function actionScanDisk()
    {
        $model = new DiskApi();   
        $ret = $model ->scanDisk();
        print_r($ret);        
        return ExitCode::OK;
    }    

    /*********/
    public function actionSyncTransportTarif()
    {
        $model = new DataSync();
        $model ->loadTransportTarifData();
        return ExitCode::OK;
    }
    
/*********/
    public function actionScanContracts()
    {
        $model = new ContractsEditForm();
        $model ->scanContract();
        return ExitCode::OK;
    }


}
