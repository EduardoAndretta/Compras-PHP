<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class UnidMedida extends CI_Controller {

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Inserir-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function inserir(){
        //Sigla e Descrição recebidos via JSON e alocados em variáveis
        //Retornos possíveis:
        //1 - Unidade cadastrada corretamente (Banco)
        //2 - Faltou informar a sigla (FrontEnd)
        //3 - Quantidade de caracteres é superior a 3 (FrontEnd)
        //4 - Descrição não informada (FrontEnd)
        //5 - Usuário não informado
        //6 - Houve algum problema no salvamento do LOG, mas a unidade foi inclusa (LOG)
        //7 - O usuário fornecido não existe na base de dados (Banco)
        //8 - O usuário fornecido está desativado (Banco)

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $sigla     = trim($resultado->sigla);
        $descricao = trim($resultado->descricao);
        $usuario   = trim($resultado->usuario);

        //Faremos a validação para sabermos se todos os dados foram enviados corretamente
        if ($sigla == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Sigla não informada');

        }elseif (strlen($sigla) > 3){
            $retorno = array('codigo' => 3,
                             'msg' => 'Sigla pode conter no máximo três caracteres');

        }elseif ($descricao == ''){
            $retorno = array('codigo' => 4,
                             'msg' => 'Descrição não informada');

        }elseif ($usuario == ''){
            $retorno = array('codigo' => 5,
                             'msg' => 'Usuário não informado');

        }else{
            //Realizo a instância da model
            $this->load->model('m_unidmedida');

            //Atributo $retorno recebe array com informações
            $retorno = $this->m_unidmedida->inserir($sigla, $descricao, $usuario);

        }
        //Retorno no formato JSON
        echo json_encode($retorno);
    
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///------------------------------------------Consultar------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function consultar(){
        //Código, Sigla e Descrição recebidos via JSON e alocados em variáveis
        //Retornos possíveis:
        //1 - Dados consultados corretamnte (Banco) 
        //2 - Quantiade de caracteres da sigla é superior a 3 (FrontEnd)
        //6 - Dados não encontrados (Banco)

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $codigo     = trim($resultado->codigo);
        $sigla      = trim($resultado->sigla);
        $descricao  = trim($resultado->descricao);

        //Verifico somente a quantiade de caracteres da sigla, pode ter até três
        //Ou também nenhum para resultados em um SELECT * FROM [table];
        if(strlen($sigla) > 3){
            $retorno = array('codigo' => 2,
                             'msg' => 'Sigla pode conter no máximo três caracteres ou nenhuma para todas');
    
        }else{
            //Realizo a instância da model
            $this->load->model('m_unidmedida');

            //Atributo $retorno recebe o array com informações da consulta dos dados [result()]
            $retorno = $this->m_unidmedida->consultar($codigo, $sigla, $descricao);

        }
        //Retorno no formato JSON
        echo json_encode($retorno);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Alterar-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function alterar(){
        //Código, Sigla e Descrição recebidos via JSON e colocados em variáveis
        //Retornos possíveis:
        // 1 - Dado(s) alterados(s) corretamente (Banco)
        // 2 - Faltou informar o código (FrontEnd)
        // 3 - Quantidade de caracteres da sigla é superior à 3 (FrontEnd)
        // 5 - Usuário não informado (FrontEnd)
        // 6 - Dados não encontrados (Banco)
        // 7 - Houve um problema no salvamento do LOG (Banco)
        // 8 - O usuário fornecido não existe na base de dados (Banco)
        // 9 - O usuário fornecido está desativado (Banco)
        // 10 - Não há atualização(ões) nos campo(os) informado(os)

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $codigo    = trim($resultado->codigo);
        $sigla     = strtoupper(trim($resultado->sigla));
        $descricao = trim($resultado->descricao);
        $usuario   = trim($resultado->usuario);

        //Validação dos Dados

        if($codigo == ''){
            $retorno = array('codigo' => 2,
                           'msg' => 'Usuário não informado');

        }elseif(strlen($sigla) > 3){
            $retorno = array('codigo' => 3,
                           'msg' => 'A Sigla não pode conter mais de três caracteres');
            
        }elseif($sigla == '' && $descricao == ''){
            $retorno = array('codigo' => 4,
                             'msg' => 'Sigla e Descrição não informadas. Não há campos para realizar alteração');    

        }elseif($usuario == ''){
            $retorno = array('codigo' => 5,
                           'msg' => 'Usuário do sistema não informado');

        }else{

            //Instância da Model
            $this->load->model('m_unidmedida');

            //Atributo $retorno recebe os dados do banco
            $retorno = $this->m_unidmedida->alterar($codigo, $sigla, $descricao, $usuario);

        }

        //Retorno no formato JSON
        echo json_encode($retorno);

    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Desativar-------------------------------------------///
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    public function desativar(){
        //Código da undiade recebido via JSON e alocado à uma varíavel
        //Retornos possíveis:
        // 1 - Unidade desativada corretamente (Banco)
        // 2 - Código não informado (FrontEnd)
        // 3 - Existem produtos cadastrados com a unidade de medida informada (Banco)
        // 5 - Usuário não informado (FrontEnd)
        // 6 - Dados não encontrados (Banco)
        // 7 - Salvamento do LOG não efetivado (Banco)
        // 8 - Houve um problema na desativação do usuário (Banco)
        // 10 - Verifica a integridade do código - Numeric (FrontEnd) 

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $codigo  = trim($resultado->codigo);
        $usuario = trim($resultado->usuario);

        if($usuario == ''){
            $retorno = array('codigo' => 5,
                             'msg' => 'Usuário não informado');
        }elseif($codigo == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Código não informado');
        }elseif(!is_numeric($codigo)){
            $retorno = array('codigo' => 10,
                             'msg' => 'O código deve ser um número, de preferência inteiro');
        }else{
            //Realizando a instância da model
            $this->load->model('m_unidmedida');

            //Atributo retorno - Recebe o resultado vindo do Banco
            $retorno = $this->m_unidmedida->desativar($codigo, $usuario);
        }
        //Retorno no formato JSON
        echo json_encode($retorno);
    }
}

?>