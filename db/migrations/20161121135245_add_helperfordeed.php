<?php

use Phinx\Migration\AbstractMigration;

class AddHelperfordeed extends AbstractMigration
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
        $helperfordeed = $this->table('HelperForDeed',array('id' => false,'primary_key' => array('idUser','idGuteTat')));
        $helperfordeed->addColumn('idUser', 'integer')
              ->addColumn('idGuteTat', 'integer')
              ->addColumn('rating','integer',array('null' => true))
              ->addIndex(array('idUser'))
              ->addIndex(array('idGuteTat'))
              ->addForeignKey('idUser','User','idUser',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->addForeignKey('idGuteTat','Deeds','idGuteTat',array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->create();
    }
}
