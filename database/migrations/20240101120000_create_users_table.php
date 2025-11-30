<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');

        $table->addColumn('name', 'string', ['limit' => 100])
              ->addColumn('email', 'string', ['limit' => 100])
              ->addColumn('password', 'string')
              ->addColumn('role', 'string', ['limit' => 20, 'default' => 'user'])
              ->addColumn('email_verified_at', 'timestamp', ['null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', [
                  'null' => true,
                  'default' => null,
                  'update' => 'CURRENT_TIMESTAMP'
              ])
              ->addIndex(['email'], ['unique' => true])
              ->create();
    }
}
