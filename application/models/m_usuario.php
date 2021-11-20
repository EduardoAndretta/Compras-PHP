<?php
defined('BASEPATH')OR exit('No direct script access allowed');

class M_usuario extends CI_Model {
   
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Inserir-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function inserir($usuario, $senha, $nome, $tipo_usuario, $usu_sistema){

        //Verificação da existência do usuário informado para inserção

        $this->load->model('m_verificacaoGeral');

        $ver_user = $this->m_verificacaoGeral->verificacaoUser($usuario);

        if($ver_user['codigo'] == 13){
            $this->db->trans_begin(); //Iniciando o translação de dados [Transaction]

            //Armazenando o log para a model m_log
            $sql = "insert into usuarios (usuario, senha, nome, tipo)
            values ('$usuario',md5('$senha'),'$nome','$tipo_usuario')";

            //Query de inserção dos dados
            $this->db->query($sql);

        }else { $sql = ""; }

        //Realizando a verificação (Inserção ocorrida com sucesso)
        if($this->db->affected_rows() > 0 && $sql != ""){
            //Fazemos a inserção do LOG na nuvem
            //Fazemos a instância da model M_log
            $this->load->model('m_log');

            //Chamando o método de inserção do LOG
            //Requisitando e enviando dados ($retorno_log irá receber um JSON com o resultado do envio do LOG)
            $retorno_log = $this->m_log->inserir_log($usu_sistema, $sql);

            if($retorno_log['codigo'] == 1){
                $this->db->trans_commit(); //COMMIT
                $dados = array('codigo' => 1,
                           'msg' => 'Usuário cadastrado corretamente');

            }else{
                $this->db->trans_rollback(); //ROLLBACK
                $dados = array('codigo' => 8,
                           'msg' => 'Houve algum problema no salvamento do Log. A inserção do usuário foi cancelada');
            }

        }elseif($ver_user['codigo'] == 12){
            $dados = array('codigo' => 10,
                           'msg' => $ver_user['msg']);
        
        }else{
            $dados = array('codigo' => 6,
                           'msg' => 'Houve algum problema na inserção de dados na tabela de usuários');
        }
        //Envia o array $dados com as informações tratadas acima pela estrutura de decisão If
        //Importante, o atributo $retorno irá receber $dados e passar para o formato JSON
        return $dados;

    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Consulta-------------------------------------------///
    ////////////////////////////////////////////////////////////////////////////////////////////////////

    public function consultar($usuario, $nome, $tipo_usuario, $usu_sistema){
        /**********************************************
         * Função que servirá para quatro tipos de consulta:
         * 
         *  * Para todos os usuário (SELECT * FROM usuarios where estatus = '')
         *  
         *  * Para um determinado usuário (SELECT * FROM usuarios where estatus = '' and usuario = '$usuario')
         * 
         *  * Para um tipo de usuário (SELECT * FROM usuarios where estatus = '' and tipo = '$tipo_usuario')
         * 
         *  * Para nomes de usuários (SELECT * FROM usuario where estatus = '' and nome like '%$nome%')
         * 
         *  * Além disso, é possível ativar os três de uma vez, filtrando a busca de usuário
         * 
         */
        //Query Dinâmica - Consulta de dados com os parâmetros obtidos
        //Apenas usuários com o estatus ativo ''

        $sql = "select * from usuarios where estatus = '' ";

        if($usuario != '') {  $sql = $sql . "and usuario = '$usuario' "; }
        if($tipo_usuario != '') { $sql = $sql . "and tipo = '$tipo_usuario' "; }
        if($nome != '') { $sql = $sql . "and nome like '%$nome%' "; }

        //Observação [retorno] (Está variável retorno não é a mesma do front)

        $retorno = $this->db->query($sql);

        //Verificação (Consulta realizada com sucesso ou não)
        //result() -> Função que cria um array com os resultado obtidos no Banco de Dados (Tráz o query automaticamente)

        //Caso a consutla tenha ocorrido corretamente
        if($retorno->num_rows() > 0){

            //Realizando a instãncia da model m_log
            $this->load->model('m_log');
            
            //Requisitando e enviando dados ($retorno_log irá receber um JSON com o resultado do envio do LOG)
            $retorno_log = $this->m_log->inserir_log($usu_sistema, $sql);

            if($retorno_log['codigo'] == 1){
                $dados = array('codigo' => 1,
                               'msg' => 'Consulta realizada com sucesso',
                               'dados' => $retorno->result());
            }else{
                $dados = array('codigo' => 8,
                               'msg' => 'Houve algum problema no salvamento do Log, porém,
                                         consulta realizada com sucesso');
            }

        }else{    
            $dados = array('codigo' => 6,
                           'msg' => 'Dados não encontrados');
        }

        //Envia o array $dados com as informações tratadas acima pela estrutura de decisão if
        //Importante, o atributo $retorno irá receber $dados e passar para o formato JSON
        return $dados;

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Alterar-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function alterar($usuario, $nome, $senha, $tipo_usuario, $usu_sistema){
        //Query de atualização de dados (O campo usuário não pode ser atualizado)
        
        //Inciando Query Dinâmica no update

        $sql = "update usuarios set ";

        if ($nome != '') $sql = $sql . "nome = '$nome'"; 
        
        if ($senha != '' && $nome != '') $sql = $sql . ", senha = md5('$senha')";
        elseif($senha != '') $sql = $sql . "senha = md5('$senha')";

        if (($tipo_usuario != '') && ($nome != '' || $senha != '')) $sql = $sql . ", tipo = '$tipo_usuario' ";
        elseif($tipo_usuario != '') $sql = $sql . "tipo = '$tipo_usuario'";

        $sql = $sql . " where usuario = '$usuario'";
        $this->db->query($sql);

        //Verificação (Atualização ocorreu com sucesso ou não)
        //Caso tenha ocorrido, a inserção do LOG será iniciada
        if($this->db->affected_rows() > 0){
           
            //Enviando os dados para o Banco LOG
            //Realizando a instância da model M_log
            $this->load->model('m_log');

            //Requisitando e enviando dados ($retorno_log irá receber um JSON com o resultado do envio do LOG)
            $retorno_log = $this->m_log->inserir_log($usu_sistema, $sql);

            //Verificação da inserção na tabela LOG
            if($retorno_log['codigo'] == 1){
                $dados = array('codigo' => 1,
                               'msg' => 'Usuário alterado corretamente');
            
            //Caso houver erros na inserção na tabela LOG
            }else{
                $dados = array('codigo' => 8,
                               'msg' => 'Houve algum problema no salvamento do Log, porém,
                                         alteração realizada com sucesso');
            }

        }else{
            $dados = array('codigo' => 6,
                           'msg'  => 'Ocorreu um problema na atualização na tabela de usuários');
          
        }
        //Envia o array $dados com as informações tratadas anteriormente pela estrutura de decisão if
        return $dados;

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///------------------------------------------Desativar------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function desativar($usuario, $usu_sistema){
        /******************************************************************************************************
         * Em relação a deletar dados de um Banco de Dados (Comércial em empresas)
         * 
         *  1 - Não se deve deletar, isso porque, o mesmo usuário/consumidor pode alegar algo contra a empres
         *      e os dados devem estar todos armazenados caso haja alguma investigação ou algo relacionado.
         * 
         * 2 - Deste modo, altera-se apenas o estatus do usuário, que pode ser ativo '' ou desativado 'D',
         *     mantendo deste modo, a integridade de todos usuários.
         * 
         */
        
        //Verificação - Usuário desabilitado ou ativado ['' or 'D']

        $this->load->model('m_verificacaoGeral');

        $ver_user = $this->m_verificacaoGeral->verificacaoUserDesativado($usuario);
        
        if($ver_user['codigo'] == 15){
            //Variável com o código
            $sql = "update usuarios set estatus = 'D' where usuario = '$usuario'";

            //Query de atualização dos dados                  
            $this->db->query($sql);
            
        }else{ $sql = ""; }

        //Verificação (Atualização decorreu com sucesso ou não)
        //Caso tenha ocorrido, a inserção do LOG será iniciada
        if($this->db->affected_rows() > 0 && $sql != ""){
            
            //Realizando a instância da model M_log
            $this->load->model('m_log');

            //Requisitando e enviando dados ($retorno_log irá receber um JSON com o resultado do envio do LOG)
            $retorno_log = $this->m_log->inserir_log($usu_sistema, $sql);
            
            //Caso tenha inserido o LOG corretamente
            if($retorno_log['codigo'] == 1){
                $dados = array('codigo' => 1,
                        'msg' => 'Usuário desativado com sucesso');                

            //Caso haja algum problema na inserção do LOG
            }else{
                $dados = array('codigo' => 8,
                               'msg' => 'Houve algum problema no salvamento do Log, porém,
                                         desativação realizada com sucesso');
            }
      
        }elseif($ver_user['codigo'] == 14){
            $dados = array('codigo' => 9,
                           'msg' => 'O usuário informado já está desativado');
        
        //Caso haja algum problema na desativação do usuário no Banco de Dados
        }else{
            $dados = array('codigo' => 6,
                           'msg' => 'Houve algum problema durante a desativação do usuário');
        }
        //Envia o array $dados com as informações tratadas acima pela estrutura de decisão if
        return $dados;

    }

}

?>