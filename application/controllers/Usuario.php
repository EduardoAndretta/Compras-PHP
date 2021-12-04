<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Inserir-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function inserir(){
        //Usuário, senha, nome, tipo(Administrador ou Comum)  [Valores Requsitados]  
        //Recebidos via JSON e armazenados em variáveis
        //Retornos Possíveis:
        //1 - Usuário cadastrado corretamente (Verificação - Banco)
        //2 - Faltou informar o usuário (Verificação - FrontEnd)
        //3 - Faltou informar a senha (Verificação - FrontEnd)
        //4 - Faltou informar o nome (Verificação - FrontEnd)
        //5 - Faltou informar o tipo de usuário (Verificação - FrontEnd)
        //6 - Houve algum problema no insert da tabela (Verificação - Banco)
        //7 - Usuário do sistema não informado (Verificação - FrontEnd)
        //8 - Houve problema no salvamento do LOG, mas o usuário foi incluso (LOG)
        //9 - Usuário já existente na base de dados (Banco)
        
        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        //Armazenando os elementos do JSON em suas determinadas variáveis

        $usuario      = $resultado->usuario;
        $senha        = $resultado->senha;
        $nome         = $resultado->nome;
        $tipo_usuario = strtoupper($resultado->tipo_usuario);
        $usu_sistema   = $resultado->usu_sistema;

        //Faremos uma validação para sabermos se todos os dados foram enviados
        //trim() -> Remove os espaços em branco da direita e esquerda
        //strtoupper() -> Transforma todos os caracteres em Maiusculo

        if (trim($usuario) == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Usuário não informado');

        }elseif (trim($senha) == ''){
            $retorno = array('codigo' => 3,
                             'msg' => 'Senha não informada');

        }elseif (trim($nome) == ''){
            $retorno = array('codigo' => 4,
                             'msg' => 'Nome não informado');

        }elseif ((trim($tipo_usuario) != 'COMUM' && 
                 trim($tipo_usuario) != 'ADMINISTRADOR') || 
                 trim($tipo_usuario) == ''){
            $retorno = array('codigo' => 5,
                             'msg' => 'Tipo de usuário inválido');

        }elseif (trim($usu_sistema) == ''){   
            $retorno = array('codigo' => 7,
                             'msg' => 'Usuário do sistema não informado');
        
        }else{

            //Realizo a instância da Model (Inicia a Model)
            $this->load->model('m_usuario');

            //Atributo $retorno recebe array com informações da validação do acesso
            //importante citar, inserir é uma função criada na classe M_usuario dentro da model m_usuario
            $retorno = $this->m_usuario->inserir($usuario, $senha, $nome, $tipo_usuario, $usu_sistema);

        }

        //Retorno no formato JSON  
        echo json_encode($retorno);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///------------------------------------------Consultar------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function consultar(){
        //Usuário, nome e tipo (Administrador ou Comum)
        //Recebidos via JSON em variáveis
        //Retornos possíveis:
        //1 - Dados consultados corretamente (Verificação - Banco)
        //5 - Tipo de usário Inválido (Verificação - FrontEnd)
        //6 - Dados não encontrados (Verificação - Banco)
        //7 - Usuário do Sistema não informado (Verificação - FrontEnd)
        //8 - Dados consultados corretamente na tabela usuário apenas (Verificação - Banco)

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $usuario      = $resultado->usuario;
        $nome         = $resultado->nome;
        $tipo_usuario = strtoupper($resultado->tipo_usuario);
        $usu_sistema  = $resultado->usu_sistema;

        //Validação para tipo de usuário que deverá ser ADMINISTRADOR, COMUM ou VAZIO
        if(trim($tipo_usuario) != 'ADMINISTRADOR' && 
           trim($tipo_usuario) != 'COMUM' &&
           trim($tipo_usuario) != ''){
                $retorno = array('codigo' => 5,
                                 'msg' => 'Tipo de usuário inválido');

        //Validação - Usuário do sistema
        }elseif(trim($usu_sistema) == ''){
                $retorno = array('codigo' => 7,
                                 'msg' => 'Usuário do sistema não informado');
        }else{

            //Realizando a instância da Model m_usuario
            $this->load->model('m_usuario');

            //Atributo $retorno recebe array com informações da consulta dos dados
            $retorno = $this->m_usuario->consultar($usuario, $nome, $tipo_usuario, $usu_sistema);

        }

        //Retorno no formato JSON
        echo json_encode($retorno);


    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Alterar-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function alterar(){
        //Usuário, nome e tipo (Administrador ou Comum)
        //Recebidos via JSON e armazenados em variáveis
        //Retornos possíveis:
        //1 - Dado(s) alterados(s) corretamente (Juntamente com a tabela LOG) (Verificação - Banco)
        //2 - Usuário em Branco ou Zerado (Verificação - FrontEnd)
        //3 - Senha em Branco (Verificação - FrontEnd)
        //5 - Tipo de usuário inválido (Verificação - FrontEnd)
        //6 - Dados não encontrados (Verificação - FrontEnd)
        //7 - Usuário do Sistema não informado (Verificação - FrontEnd)
        //8 - Dados alterados corretamente na tabela usuário apenas (Verificação - Banco)
        //9 - Dados não informados para realizar a atualização (Verificação - FrontEnd)
        //10 - Usuário desativado (Banco)
        //11 - Dados repetidos - Dados do Banco == Dados informados (Banco)
        //12 - O usuário informado não existe (Banco)
     
        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $usuario      = $resultado->usuario;
        $senha        = trim($resultado->senha);
        $nome         = trim($resultado->nome);
        $tipo_usuario = strtoupper($resultado->tipo_usuario);
        $usu_sistema  = $resultado->usu_sistema;

        //Validação (Tipo de usuário)
        //Deverá ser ADMINISTRADOR, COMUM ou VAZIO
        if (trim($tipo_usuario) != 'ADMINISTRADOR' &&
            trim($tipo_usuario) != 'COMUM' &&
            trim($tipo_usuario) != ''){
            $retorno = array('codigo' => 5,
                             'msg' => 'Tipo de usuário inválido');

        //Validação para usuário
        }elseif (trim($usuario) == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Usuário não informado');

        //Validação do usuário do banco
        }elseif(trim($usu_sistema) == ''){
            $retorno = array('codigo' => 7,
                             'msg' => 'Usuário do banco não informado');

        //Verificando a integridade dos campos
        }elseif($usuario != '' && $nome == '' && $senha == '' && $tipo_usuario == ''){
            $retorno = array('codigo' => 9,
                             'msg' => 'Não há campos informados para realizar a alteração');
      
        }else{

            //Instância da model m_usuario
            $this->load->model('m_usuario');

            //Atributo $retorno recebe array com informações da alteração de dados
            $retorno = $this->m_usuario->alterar($usuario, $nome, $senha, $tipo_usuario, $usu_sistema);

        }

        //Retorno no formato JSON
        echo json_encode($retorno);   
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///------------------------------------------Desativar------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function desativar(){
        //Usuário recebido via JSON e alocado em uma variável
        //Retornos possíveis:
        //1 - Usuário desativado corretamente (Verificação - Banco)
        //2 - Usuário em Branco (Verificação - FrontEnd)
        //6 - Dados não encontrados (Verificação - Banco)
        //7 - Usuário do Sistema não informado (Verificação - FrontEnd)
        //8 - Dados desativados corretamente na tabela usuário apenas (Verificação - Banco)
        //9 - Usuário já está desativado na base de dados (Banco)

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $usuario     = $resultado->usuario;
        $usu_sistema = $resultado->usu_sistema;

        //Validação para o usuário, que não deverá ser branco(em caracteres)
        if(trim($usuario) == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Usuário não informado');

        //Validação - Usuário do sistema
        }elseif(trim($usu_sistema) == ''){
            $retonro = array('codigo' => 7,
                             'msg' => 'Usuário do sistema não informado');

        }else{

            //Instância da Model m_usuario
            $this->load->model('m_usuario');

            //Atributo $retorno recebe o resultado da verificação no Banco
            $retorno = $this->m_usuario->desativar($usuario, $usu_sistema);

        }

        //Retorno no formato JSON
        echo json_encode($retorno);

    }



}




?>

