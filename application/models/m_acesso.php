<?php
defined('BASEPATH')OR exit('No direct script access allowed');

class M_acesso extends CI_Model {
    
    /////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Login-------------------------------------------///
    /////////////////////////////////////////////////////////////////////////////////////////////////

    public function validalogin($usuario, $senha){
        //Atributo retorno recebe o resultado do SELECT
        //realizado na tabela de usuários (Lembrando da função MD5())
        //por causa da criptografia.
        //Observação (Está variável retorno não é a mesma do front)

        $retorno_user = $this->db->query("select * from usuarios
                                        where usuario = '$usuario'");

        $retorno_senha = $this->db->query("select * from usuarios
                                        where usuario = '$usuario'                        
                                        and senha   = md5('$senha')");

        //Verifica se a quantidade de linhas trazidas na consulta é superior a 0,
        //isso quer dizer que foi encontrado o usuário e senha passados pela 
        //Controller

        //Criado um array com dois elementos para retorno do resultado
        //1 - Código da mensagem
        //2 - Descrição da Mensagem

        //num_rows() > 0 => Caso houver mais de 0 rows encontradas no Banco de dados

        if($retorno_user->num_rows() > 0 && $retorno_senha->num_rows() > 0){
            
            //Verificação - Usuário desabilitado ou ativado ['' or 'D']

            $this->load->model('m_verificacaoGeral');

            $ver_user = $this->m_verificacaoGeral->verificacaoUserDesativado($usuario);
            
            if($ver_user['codigo'] == 14){
                $dados = array('codigo' => 6,
                               'msg' => 'Usuário desabilitado para acesso');
            }else{
                $dados = array('codigo' => 1,
                               'msg' => 'Usuário e senha corretos');
            }
        
        }elseif($retorno_user->num_rows() > 0 && $retorno_senha->num_rows() == 0){
            $dados = array('codigo' => 4,
                           'msg' => 'Usuário correto e senha incorreta');
        
        }else{
            $dados = array('codigo' => 5,
                           'msg' => 'Usuário e senha inválidos');
        }
   
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if

        return $dados;
    }
}

?>