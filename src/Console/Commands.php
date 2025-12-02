<?php

namespace Framework\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Classe base para os futuros comandos
 */
class Commands extends BaseCommand
{
    /**
     * @var string|null Nome padrão do comando
     */
    protected static $defaultName;

    /**
     * Nome do comando
     */
    private $name;

    /**
     * Descrição do comando
     */
    public $description;

    /**
     * Ajuda do comando
     */
    public $help;

    /**
     * Exemplos de saída em caso de "-h" ou "--help"
     */
    public $example;

    /**
     * Dados das opções para o Helper
     * 
     * @var array
     */
    public $optionsData;

    /**
     * Dados do argumento para o Helper
     * 
     * @var array
     */
    public $argumentData;

    /**
     * Input do comando
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Output do comando
     *
     * @var OutputInterface
     */
    protected $output;

    public const SUCCESS = 0;
    public const FAILURE = 1;


    /**
     * Configura o comando
     */
    protected function configure(): void
    {
        $this->setDescription($this->description);

        $this->config();

        $this->setExampleUsage();
        $this->setHelp($this->help . $this->example);
    }

    /**
     * Executa as configurações vindas dos comandos que herdarão a classe Commands
     * 
     * @return void
     */
    protected function config()
    {
        //
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // Varrer todas as opções e argumentos a fim de verificar os que sao obrigatorios
        if (isset($this->argumentData) && is_array($this->argumentData)) {
            foreach ($this->argumentData as $argument) {
                if (($argument['mode'] == InputArgument::REQUIRED) && !$input->getArgument($argument['name'])) {
                    throw new RuntimeException("O argumento '{$argument['name']}' é obrigatório");
                }
            }
        }
        if (isset($this->optionsData) && is_array($this->optionsData)) {
            foreach ($this->optionsData as $option) {
                if (($option['mode'] == InputOption::VALUE_REQUIRED) && !$input->getOption($option['name'])) {
                    throw new RuntimeException("A opção '{$option['name']}' é obrigatória");
                }
            }
        }
    }

    /**
     * Executa o comando
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;



        return $this->handle();
    }

    /**
     * Executa o comando configurado
     *
     * Este metodo deve ser sobreescrito na classe que herdar a classe Commands
     *
     * @return int 0 se tudo funcionar, ou um código de saída
     *
     * @throws LogicException Quando este método abstrato não é implementado
     *
     * @see setCode()
     */
    protected function handle()
    {
        throw new LogicException('You must override the handle() method in the concrete command class.');
    }

    /**
     * Seta valores de exemplo de uso
     * 
     * @return void
     */
    protected function setExampleUsage(): void
    {
        $this->example = PHP_EOL . PHP_EOL . 'Examples:' . PHP_EOL;

        if (isset($this->argumentData) && is_array($this->argumentData)) {
            foreach ($this->argumentData as $argument) {
                $this->example .= PHP_EOL . sprintf("%-80s %s", "  <info>php apexphp-cli " . $this->getDefaultName() . "</info> <comment>{$argument['name']} </comment>", $argument['description']);
            }
        }

        if (isset($this->optionsData) && is_array($this->optionsData)) {
            foreach ($this->optionsData as $option) {
                $this->example .= PHP_EOL . sprintf("%-80s %s", "  <info>php apexphp-cli " . $this->getDefaultName() . "</info> <comment>--{$option['name']} </comment>", $option['description']);
            }
        }
    }

    // IO Features

    /**
     * Pega o argumento ou retorna como objeto de entrada
     *
     * @param string $data Nome do argumento
     */
    public function input($data = null)
    {
        if (!$data) {
            return $this->input;
        }

        return $this->argument($data);
    }

    /**
     * Pega o argumento ou retorna como objeto de saída
     *
     * @param string $data Nome do argumento
     */
    public function output($data = null)
    {
        if (!$data) {
            return $this->output;
        }

        return $this->writeln($data);
    }

    /**
     * Adiciona um novo argumento
     */
    public function setArgument($name, $mode = null, $description = '', $default = null)
    {
        if (strtoupper($mode) === 'OPTIONAL') {
            $mode = InputArgument::OPTIONAL;
        }

        if (strtoupper($mode) === 'REQUIRED') {
            $mode = InputArgument::REQUIRED;
        }

        if (strtoupper($mode) === 'IS_ARRAY') {
            $mode = InputArgument::IS_ARRAY;
        }

        $this->argumentData[$name] = ['name' => $name, 'mode' => $mode, 'description' => $description, 'default' => $default];

        return $this->addArgument($name, $mode, $description, $default);
    }

    /**
     * Pega o argumento pelo nome
     */
    public function argument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Pega todos os argumentos
     */
    public function arguments()
    {
        return $this->input->getArguments();
    }

    /**
     * Adiciona uma nova opção
     */
    public function setOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        if (strtoupper($mode) === 'OPTIONAL') {
            $mode = InputOption::VALUE_OPTIONAL;
        }

        if (strtoupper($mode) === 'REQUIRED') {
            $mode = InputOption::VALUE_REQUIRED;
        }

        if (strtoupper($mode) === 'NONE') {
            $mode = InputOption::VALUE_NONE;
        }

        if (strtoupper($mode) === 'IS_ARRAY') {
            $mode = InputOption::VALUE_IS_ARRAY;
        }

        $this->optionsData[$name] = ['name' => $name, 'shortcut' => $shortcut, 'mode' => $mode, 'description' => $description, 'default' => $default];

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * Pega a opção pelo nome
     */
    public function option(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Pega todas as opções
     */
    public function options()
    {
        return $this->input->getOptions();
    }

    /**
     * Pede uma pergunta
     */
    public function ask(string $question, $default = null)
    {
        $helper = $this->getHelper('question');
        $question = new Question("$question ", $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma pergunta sem trim
     */
    public function askRaw(string $question, $default = null)
    {
        $helper = $this->getHelper('question');
        $question = new Question("$question ", $default);

        $question->setTrimmable(false);

        return $helper->ask($this->input, $this->output, $question);
    }


    /**
     * Pede uma pergunta com multiplas linhas
     */
    public function askMultiline(string $question)
    {
        $helper = $this->getHelper('question');
        $question = new Question("$question ");

        // $question->setMultiline(true);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma pergunta com auto completion
     */
    public function autoComplete(string $question, array $potentialAnswers, $default = null)
    {
        $helper = $this->getHelper('question');
        $question = new Question("$question ", $default);

        $question->setAutocompleterValues($potentialAnswers);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma pergunta com possíveis respostas
     */
    public function choice(string $question, array $choices, string $errorMessage = 'Invalid choice', $default = 0)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("$question ", $choices, $default);

        $question->setErrorMessage($errorMessage);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma pergunta com possíveis respostas + múltipla escolha
     */
    public function multiChoice(string $question, array $choices, string $errorMessage = 'Invalid choice', $default = 0)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("$question ", $choices, $default);

        $question->setMultiselect(true);
        $question->setErrorMessage($errorMessage);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma senha
     */
    public function secret(string $question, bool $useFallback = false)
    {
        $helper = $this->getHelper('question');
        $question = new Question("$question ");

        $question->setHidden(true);
        $question->setHiddenFallback($useFallback);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Pede uma confirmação
     */
    public function confirm($question, $param = false, $regex = '/^y/i')
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("$question ", $param, $regex);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Escreve uma mensagem
     */
    public function write($messages, $options = 0)
    {
        return $this->output->write($messages, $options);
    }

    /**
     * Escreve uma mensagem
     */
    public function writeln($messages, $options = 0)
    {
        return $this->output->writeln($messages, $options);
    }

    // Estilos de output

    /**
     * Escreve uma mensagem como comentário
     */
    public function comment($messages, $options = 0)
    {
        return $this->writeln("<comment>$messages</comment>", $options);
    }

    /**
     * Escreve uma mensagem como info
     */
    public function info($messages, $options = 0)
    {
        return $this->writeln("<info>$messages</info>", $options);
    }

    /**
     * Escreve uma mensagem como erro
     */
    public function error($messages, $options = 0)
    {
        return $this->writeln("<error>$messages</error>", $options);
    }

    /**
     * Escreve uma mensagem como pergunta
     */
    public function question($messages, $options = 0)
    {
        return $this->writeln("<question>$messages</question>", $options);
    }

    /**
     * Escreve uma mensagem como link
     */
    public function link($link, $display, $options = 0)
    {
        return $this->writeln("<href=$link>$display</>", $options);
    }


}