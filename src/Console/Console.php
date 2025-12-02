<?php

namespace Framework\Console;

use Symfony\Component\Console\Application;

/**
 * Console interativo para melhor experiência com o desenvolvedor
 */
class Console
{
    /**
     * Instancia do consobe baseado no Symfony Console
     * @var Application
     */
    private Application $app;
    public function __construct($version = '1.0.0')
    {
        $this->app = new Application('ApexPHP', $version);

        // Carrega todos os comandos do diretório do framework
        // Na qual serão utilizados por padrao
        $this->loadCommands(__DIR__ . '/Commands', 'Framework\\Console\\Commands\\');

    }

    /**
     * Carrega todos os comandos do diretório do framework
     * 
     * @param string $dir Diretório do framework
     * @param string $namespace Namespace do framework
     * 
     * @return void
     */
    private function loadCommands(string $appCommandsDir, string $namespace): void
    {

        if (is_dir($appCommandsDir)) {

            // Carrega comando a comando do diretório do framework
            $commandFiles = glob($appCommandsDir . '/*Command.php');

            foreach ($commandFiles as $file) {
                $className = $namespace . basename($file, '.php');

                if (class_exists($className)) {
                    try {
                        $reflection = new \ReflectionClass($className);

                        // Pulando classes abstratas e interfaces
                        if ($reflection->isAbstract() || $reflection->isInterface()) {
                            continue;
                        }

                        // Verifica se a classe herda de Symfony Console Command
                        if ($reflection->isSubclassOf('Symfony\\Component\\Console\\Command\\Command')) {
                            $command = $reflection->newInstance();
                            $this->app->add($command);
                        }
                    } catch (\Exception $e) {
                        // Silencia erros por não serem importantes
                    }
                }
            }

            // Carrga array de comandos contidos no diretorio do framework
            $commandProviders = glob($appCommandsDir . '/*Commands.php');

            foreach ($commandProviders as $file) {
                $className = $namespace . basename($file, '.php');

                if (class_exists($className) && method_exists($className, 'getCommands')) {
                    try {
                        $commands = $className::getCommands();
                        if (is_array($commands)) {
                            foreach ($commands as $command) {
                                $this->app->add($command);
                            }
                        }
                    } catch (\Exception $e) {
                        // Silencia erros por não serem importantes
                    }
                }
            }
        }
    }


    /**
     * Executa o app
     */
    public function run(): void
    {
        $this->app->run();
    }
}