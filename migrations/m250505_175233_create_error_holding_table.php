<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%error_holding}}`.
 */
class m250505_175233_create_error_holding_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%error_holding}}', [
            'id'               => $this->primaryKey(),
            'asset_class'      => $this->string(100)->notNull(),
            'asset_type'       => $this->string(100)->notNull(),
            'ticker'           => $this->string(100)->notNull(),
            'quantity'         => $this->integer()->notNull(),
            'currency'         => $this->char(3),
            'net_amount'       => $this->decimal(15,2)->notNull(),
            'transaction_date' => $this->date()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%error_holding}}');
    }
}
