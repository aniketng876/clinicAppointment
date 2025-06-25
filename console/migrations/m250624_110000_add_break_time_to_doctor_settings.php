<?php

use yii\db\Migration;

class m250624_110000_add_break_time_to_doctor_settings extends Migration
{
    public function up()
    {
        $this->addColumn('{{%doctor_settings}}', 'break_time_start', $this->time()->null());
        $this->addColumn('{{%doctor_settings}}', 'break_time_end', $this->time()->null());
    }

    public function down()
    {
        $this->dropColumn('{{%doctor_settings}}', 'break_time_start');
        $this->dropColumn('{{%doctor_settings}}', 'break_time_end');
    }
}
