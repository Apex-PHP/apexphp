<?php

namespace Framework\Validation;

use Valitron\Validator;

class Validation extends Validator
{
    /**
     * Carregando __construct
     * @param mixed $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Exibe todos os erros em um array único
     * 
     * @return array
     */
    public function getErrors()
    {
        $erros = [];

        foreach ($this->errors() as $field => $error) {
            if (is_array($error)) {
                $erros[] = $error[0];
            } else {
                $erros[] = $error;
            }
        }
        return $erros;
    }

    /**
     * Exibe o primeiro erro encontrado
     * 
     * @return string
     */
    public function getFirstError()
    {
        $errors = $this->errors();

        return reset($errors)[0] ?? 'Dados inválidos';
    }
}