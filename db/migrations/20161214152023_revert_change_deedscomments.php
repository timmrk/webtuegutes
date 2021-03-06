<?php

use Phinx\Migration\AbstractMigration;

class RevertChangeDeedscomments extends AbstractMigration
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
        $table = $this->table('DeedComments');
        $table->dropForeignKey('user_id_creator');
        $table->changeColumn('user_id_creator','integer',array('null' => false))
              ->update();
        $table->addForeignKey('user_id_creator','User','idUser',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->update();
    }

    public function down(){
        $table = $this->table('DeedComments');
        $table->dropForeignKey('user_id_creator');
        $table->changeColumn('user_id_creator','integer',array('null' => true))
              ->update();
        $table->addForeignKey('user_id_creator','User','idUser',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->update();
    }
}
