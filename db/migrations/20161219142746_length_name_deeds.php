<?php

use Phinx\Migration\AbstractMigration;

class LengthNameDeeds extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(){
        $table = $this->table('Deeds');
        $table->changeColumn('name','string',array('length' => 256))
              ->update();
        $table = $this->table('Rating');
        $table->changeColumn('deedsName','string',array('length' => 256))
            ->update();
    }

    public function down(){
        $table = $this->table('Deeds');
        $table->changeColumn('name','string',array('length' => 64))
              ->update();
        $table = $this->table('Rating');
        $table->changeColumn('deedsName','string',array('length' => 64))
            ->update();
    }

}
