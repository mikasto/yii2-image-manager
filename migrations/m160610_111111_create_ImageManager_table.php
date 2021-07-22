<?php

use yii\db\Migration;

/**
 * Handles the creation for table `ImageManager`.
 */
class m160610_111111_create_ImageManager_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
		//ImageManager: create table
        $this->createTable('{{%image_manager}}', [
            'id' => $this->primaryKey(),
			'tag' => $this->string(128),
			'fileName' => $this->string(128)->notNull(),
			'fileHash' => $this->string(32)->notNull(),
			'created' => $this->datetime()->notNull(),
			'modified' => $this->datetime(),
        ]);

		//ImageManager: alter id column
		$this->alterColumn('{{%image_manager}}', 'id', 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%image_manager}}');
    }
}
