<?php

use \yii\db\Migration;

class m190124_110200_add_verification_token_role_column_to_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'verification_token', $this->string()->defaultValue(null));
        $this->addColumn('{{%user}}', 'role', $this->string(20)->notNull()->defaultValue('patient'));
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'verification_token');
        $this->dropColumn('{{%user}}', 'role');
    }
}
