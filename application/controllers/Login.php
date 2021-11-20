<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function logar() {
	    /////////////////////////////////////////////////////////////////////
        //Recebimento via JSON (Usuário e Senha)
        //Retornos possíveis:
        //1 - [Possíbilitado] Usuário e senha validados corretamente (Banco)
        //2 - [Impossíbilitado] Faltou informar o usuário (FrontEnd)
        //3 - [Impossíbilitado] Faltou informar a senha (FrontEnd)
        //4 - [Impossíbilitado] Usuário correto e senha inválida (Banco)
        //5 - [Impossíbilitado] Usuário e senha incorretos (Banco)
        //6 - [Impossíbilitado] Usuário desativado (Banco)
        /////////////////////////////////////////////////////////////////////

        //usuário e senha recebidos via JSON
        //e colocados em atributos

        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        $usuario = $resultado->usuario;
        $senha   = $resultado->senha;

        //trim (Remove os espaços em branco) 
        //Observação -> Ele só remove os espaços nas extremidades [___Eduardo___]

        if(trim($usuario) == ''){
            $retorno = array('codigo' => 2,
                             'msg' => 'Usuário não informado');
          
        }elseif (trim($senha) == ''){
            $retorno = array('codigo' => 3,
                             'msg' => 'Senha não informada');

        }else{

            //Realizo a instância da Model
            $this->load->model('m_acesso');

            //Atributo $retorno recebe array com informações
            //da validação do acesso
            $retorno = $this->m_acesso->validalogin($usuario, $senha);


        }

        //Retorno no formato JSON
        echo json_encode($retorno);


	}
        
        //Problema de Segurança
        //Não se deve retornar se a senha ou usuário está errado, isso porque o indivíduo
        //Pode tentar descobrir o usuário caso erre a senha ou o contrário
        //Deve-se informar Senha ou Usuário errado

      
}

?>
