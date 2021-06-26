<?php

use yii\db\Migration;

/**
 * Class m210626_123135_request_deliver
 */
class m210626_123135_request_deliver extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('{{%request_deliver}}', 'requestUPDDate', $this->date());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%request_deliver}}', 'requestUPDDate');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210626_123135_request_deliver cannot be reverted.\n";

        return false;
    }
    */
}
