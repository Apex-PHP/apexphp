<?php

namespace Framework\Console\Commands;

use Framework\Console\Commands;
use Phinx\Console\PhinxApplication;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Comandos a serem executoados pelo migrate a partir do phinx
 * Eles serao carregados todos a fim de diminuir o overhead do console
 * 
 * @package Framework\Console\Commands
 */
class MigrationCommands
{
    /**
     * Registra todos os comandos a serem utilizados pelo migrate
     * Eles serão carregados no arquivo de configuração do console
     *
     * @return Commands[]
     */
    public static function getCommands(): array
    {
        if (!class_exists('Phinx\Console\PhinxApplication')) {
            return [];
        }

        return [
            // Migrate
            new class extends Commands {
                protected static $defaultName = 'migrate:run';
                public $description = 'Executa as migrations';
                public $help = 'Executa as migrations';

                protected function config(): void
                {
                    $this->setOption('target', null, 'optional', 'Versão alvo');
                    $this->setOption('environment', null, 'optional', 'Ambiente', 'development');
                }

                protected function handle()
                {
                    $this->comment('Preparando para executar as migrations');

                    $phinx = new PhinxApplication();

                    // Comando para execuçao do migrate
                    $args = ['command' => 'migrate'];
                    if ($env = $this->option('environment')) {
                        $args['-e'] = $env;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },

            // Migrate: Status
            new class extends Commands {
                protected static $defaultName = 'migrate:status';
                public $description = 'Mostra o status das migrations';
                public $help = 'Mostra o status das migrations';

                protected function config(): void
                {
                    $this->setOption('format', null, 'optional', 'Formato de saída');
                    $this->setOption('environment', null, 'optional', 'Ambiente', 'development');
                }

                protected function handle()
                {
                    $this->comment('Exibindo o status das migrations');

                    $phinx = new PhinxApplication();

                    // Comando para exibiçao do status
                    $args = ['command' => 'status'];
                    if ($env = $this->option('environment')) {
                        $args['-e'] = $env;
                    }
                    if ($format = $this->option('format')) {
                        $args['--format'] = $format;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },

            // Migrate: Rollback
            new class extends Commands {
                protected static $defaultName = 'migrate:rollback';
                public $description = 'Reverte as migrations';
                public $help = 'Reverte as migrations';

                protected function config(): void
                {
                    $this->setOption('target', null, 'optional', 'Versão alvo');
                    $this->setOption('environment', null, 'optional', 'Ambiente', 'development');
                }

                protected function handle()
                {
                    $this->comment('Revertendo as migrations');

                    $phinx = new PhinxApplication();

                    // Comando para reverter as migrations
                    $args = ['command' => 'rollback'];
                    if ($env = $this->option('environment')) {
                        $args['-e'] = $env;
                    }
                    if ($target = $this->option('target')) {
                        $args['--target'] = $target;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },

            // Migrate: Seed
            new class extends Commands {
                protected static $defaultName = 'migrate:seed';
                public $description = 'Executa as seeds';
                public $help = 'Executa as seeds';

                protected function config(): void
                {
                    $this->setOption('seed', null, 'optional', 'Seed');
                    $this->setOption('environment', null, 'optional', 'Ambiente', 'development');
                }

                protected function handle()
                {
                    $this->comment('Executando as seeds');

                    $phinx = new PhinxApplication();

                    // Comando para executar as seeds
                    $args = ['command' => 'seed:run'];
                    if ($seed = $this->option('seed')) {
                        $args['-s'] = $seed;
                    }
                    if ($env = $this->option('environment')) {
                        $args['-e'] = $env;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },

            // Migrate: create
            new class extends Commands {
                protected static $defaultName = 'migrate:create';
                public $description = 'Cria uma migration';
                public $help = 'Cria uma migration';

                protected function config(): void
                {
                    $this->setArgument('name', 'required', 'Nome da migration a ser criada');
                }

                protected function handle()
                {
                    $this->comment('Criando as seeds');

                    $phinx = new PhinxApplication();

                    // Comando para executar as seeds
                    $args = ['command' => 'create'];
                    if ($name = $this->argument('name')) {
                        $args['name'] = $name;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },

            // Migrate: seed: create
            new class extends Commands {
                protected static $defaultName = 'migrate:seed:create';
                public $description = 'Cria uma seed';
                public $help = 'Cria uma seed';

                protected function config(): void
                {
                    $this->setArgument('name', 'required', 'Nome da seed a ser criada');
                }

                protected function handle()
                {
                    $this->comment('Criando as seeds');

                    $phinx = new PhinxApplication();

                    // Comando para executar as seeds
                    $args = ['command' => 'seed:create'];
                    if ($seed = $this->argument('name')) {
                        $args['name'] = $seed;
                    }

                    $input = new ArrayInput($args);
                    $output = new ConsoleOutput();

                    $phinx->run($input, $output);

                    return self::SUCCESS;
                }
            },
        ];
    }
}
