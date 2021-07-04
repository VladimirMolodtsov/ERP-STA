<?php

use yii\db\Migration;

/**
 * Class m210702_115627_zakaz
 */
class m210702_115627_zakaz extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
       $this->addColumn('{{%zakaz}}', 'isByClient', $this->integer(4)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%zakaz}}', 'isByClient');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210702_115627_zakaz cannot be reverted.\n";

        return false;
    }
    */
}
