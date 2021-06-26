<?php

use yii\db\Migration;

/**
 * Class m210621_101525_bank_header
 */
class m210621_101525_bank_header extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
    //Schema::TYPE_INTEGER."(11) DEFAULT 1"
        //$this->addColumn('{{%bank_header}}', 'position', $this->float());
        $this->addColumn('{{%bank_header}}', 'inputRemain', $this->double()->defaultValue(0));
        $this->addColumn('{{%bank_header}}', 'outputRemain', $this->double()->defaultValue(0));
        $this->addColumn('{{%bank_header}}', 'srcFile', $this->string(250));
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('{{%bank_header}}', 'inputRemain');
         $this->dropColumn('{{%bank_header}}', 'outputRemain');
         $this->dropColumn('{{%bank_header}}', 'srcFile');
        
     }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210621_101525_bank_header cannot be reverted.\n";

        return false;
    }
    */
}
