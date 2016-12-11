<?php

use Phinx\Migration\AbstractMigration;

class AddFbidJoinUserid extends AbstractMigration
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
    public function change()
    {
        $table = $this->table('FacebookUser');
        $table->addColumn('user_id','integer')
              ->addColumn('facebook_id','integer')
              ->addIndex(array('user_id', 'facebook_id'), array('unique' => true))
              ->addForeignKey('user_id','User','idUser',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->create();
    }
}