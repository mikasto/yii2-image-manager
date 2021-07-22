<?php

use yii\db\Migration;

class m170223_113221_addBlameableBehavior extends Migration
{
    public function up()
    {
        $this->addColumn('{{%image_manager}}', 'createdBy', $this->integer(10)->unsigned()->null()->defaultValue(null));
        $this->addColumn('{{%image_manager}}', 'modifiedBy', $this->integer(10)->unsigned()->null()->defaultValue(null));
    }

    public function down()
    {
    	$this->dropColumn('{{%image_manager}}', 'createdBy');
    	$this->dropColumn('{{%image_manager}}', 'modifiedBy');
    }
}
