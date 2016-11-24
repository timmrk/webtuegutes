<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddUserTextsTable extends AbstractMigration
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
        $usertexts = $this->table('UserTexts', array('id'=> false,'primary_key' => array('idUserTexts')));
        $usertexts->addColumn('idUserTexts','integer')
                  ->addColumn('avatar','text',array('limit' => MysqlAdapter::TEXT_REGULAR,'null' => true))
                  ->addColumn('hobbys','text',array('limit' => MysqlAdapter::TEXT_TINY,'null' => true))
                  ->addColumn('description','text',array('limit' => MysqlAdapter::TEXT_REGULAR,'null' => true))
                  ->addForeignKey('idUserTexts','User','idUser',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
                  ->create();
    }
}
