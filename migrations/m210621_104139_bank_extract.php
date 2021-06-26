<?php

use yii\db\Migration;

/**
 * Class m210621_104139_bank_extract
 */
 
/*
  `debetKPP` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `creditKPP` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `debetBIK` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `creditBIK` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
*/ 
class m210621_104139_bank_extract extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bank_extract}}', 'debetKPP', $this->string(20));
        $this->addColumn('{{%bank_extract}}', 'creditKPP', $this->string(20));
        $this->addColumn('{{%bank_extract}}', 'debetBIK', $this->string(20));
        $this->addColumn('{{%bank_extract}}', 'creditBIK', $this->string(20));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('{{%bank_extract}}', 'debetKPP');
         $this->dropColumn('{{%bank_extract}}', 'creditKPP');
         $this->dropColumn('{{%bank_extract}}', 'debetBIK');
         $this->dropColumn('{{%bank_extract}}', 'creditBIK');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210621_104139_bank_extract cannot be reverted.\n";

        return false;
    }
    */
}
