<?php

use yii\db\Migration;

/**
 * Class m210703_101405_zakaz
 */
class m210703_101405_zakaz extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
       $this->addColumn('{{%zakaz}}', 'clientEmail', $this->string(75));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('{{%zakaz}}', 'clientEmail');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210703_101405_zakaz cannot be reverted.\n";

        return false;
    }
    */
}
