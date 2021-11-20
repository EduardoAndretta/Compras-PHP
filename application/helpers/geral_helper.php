
<?php
defined('BASEPATH') or exit('No direct script access allowed');

    //Função para trocar carcteres ' (aspas simples) por ` (acento agudo)
    //(Objetivo) -> Não causar problemas ao banco com as aspas simples
    function troca_caractere($value){
        $retorno = str_replace("'", "`", $value);
        return $retorno;
    }



?>