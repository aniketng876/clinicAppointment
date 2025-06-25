<?php
use yii\db\Migration;

class m250624_110000_add_appointment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('appointments', [
            'id' => $this->primaryKey(),
            'doctor_id' => $this->integer()->notNull(),
            'patient_id' => $this->integer()->notNull(),
            'phone' => $this->string(32)->notNull(),
            'date' => $this->date()->notNull(),
            'time' => $this->time()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('pending'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_appointments_doctor', 'appointments', 'doctor_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk_appointments_patient', 'appointments', 'patient_id', 'user', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_appointments_doctor', 'appointments');
        $this->dropForeignKey('fk_appointments_patient', 'appointments');
        $this->dropTable('appointments');
    }
}
