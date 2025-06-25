<?php
use yii\db\Migration;

class m250624_120000_add_duration_to_appointments extends Migration
{
    public function safeUp()
    {
        $this->addColumn('appointments', 'duration', $this->integer()->notNull()->defaultValue(10));
    }

    public function safeDown()
    {
        $this->dropColumn('appointments', 'duration');
    }
}
