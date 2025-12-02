<?php

namespace Framework\Console\Commands;

use Framework\Console\Commands;

/**
 * Classe destinada a testar a conexão com o banco de dados
 * 
 * @package Framework\Console\Commands
 */
class ServeCommand extends Commands
{
    protected static $defaultName = 'serve';
    public $description = 'Inicia o servidor de desenvolvimento';
    public $help = 'Inicia o servidor de desenvolvimento para testes locais';

    protected function config(): void
    {
        $this->setOption('host', null, 'optional', 'Host', '127.0.0.1');
        $this->setOption('port', null, 'optional', 'Porta', '8000');
    }

    protected function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("ApexPHP development server started on http://{$host}:{$port}");
        $this->comment("Press Ctrl+C to stop");

        passthru("php -S {$host}:{$port} -t public");

        return self::SUCCESS;
    }
}