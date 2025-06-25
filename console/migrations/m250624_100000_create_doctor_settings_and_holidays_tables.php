<?php

use yii\db\Migration;

class m250624_100000_create_doctor_settings_and_holidays_tables extends Migration
{
    public function up()
    {
        // Doctor Settings Table
        $this->createTable('{{%doctor_settings}}', [
            'id' => $this->primaryKey(),
            'doctor_id' => $this->integer()->notNull(),
            'working_days' => $this->string(100)->notNull(), // e.g. Mon,Tue,Wed
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'non_working_days' => $this->string(100), // e.g. Sun
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_doctor_settings_doctor', '{{%doctor_settings}}', 'doctor_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        // Holidays Table
        $this->createTable('{{%doctor_holidays}}', [
            'id' => $this->primaryKey(),
            'doctor_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'description' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_doctor_holidays_doctor', '{{%doctor_holidays}}', 'doctor_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_doctor_settings_doctor', '{{%doctor_settings}}');
        $this->dropTable('{{%doctor_settings}}');
        $this->dropForeignKey('fk_doctor_holidays_doctor', '{{%doctor_holidays}}');
        $this->dropTable('{{%doctor_holidays}}');
    }
}
