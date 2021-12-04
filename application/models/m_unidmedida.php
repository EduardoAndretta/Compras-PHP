<?php
defined('BASEPATH') OR exit ('No direct script access allowed');

class M_unidmedida extends CI_Model{

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Inserir-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function inserir($sigla, $descricao, $usuario){
 
        //Verificação - Integridade do campo usuário

        $this->load->model('m_verificacaoGeral');

        $ver_user = $this->m_verificacaoGeral->verificacaoUser($usuario); 
        $ver_userDes = $this->m_verificacaoGeral->verificacaoUserDesativado($usuario);

        if($ver_user['codigo'] == 12 && $ver_userDes['codigo'] == 15){
            //Query de inserção dos dados
            $sql = "insert into unid_medida (sigla, descricao, usucria)
                    values ('$sigla', '$descricao','$usuario')";
            $this->db->query($sql);
        }else{ $sql = ""; }

        //Verificação (Inserção ocorreu com sucesso ou não)
        if($this->db->affected_rows() > 0 && $sql != ""){
            //Fazemos a inserção no Log na nuvem
            //Fazemos a instância da model M_log
            $this->load->model('m_log');

            //Fazemos a chamado do método de inserção do Log
            //$retorno_log irá receber um JSON com o resultado (código)
            $retorno_log = $this->m_log->inserir_log($usuario, $sql);

            if($retorno_log['codigo'] == 1){
                $dados = array('codigo' => 1,
                               'msg' => 'Unidade de medida cadastrada corretamente');

            }else{
                $dados = array('codigo' => 7,
                               'msg' => 'Houve algum problema no salvamento do Log, porém,
                                         Unidade de Medida cadastrada corretamente');
            }
        }elseif($ver_user['codigo'] == 13){
            $dados = array('codigo' => 7,
                           'msg' => $ver_user['msg']);

        }elseif($ver_userDes['codigo'] == 14){
            $dados = array('codigo' => 7,
                           'msg' => $ver_userDes['msg']);
        }else{
            $dados = array('codigo' => 6,
                           'msg' => 'Houve algum problema na inserção na tabela unidade de media');
        }

    //Envia o array dados com as informações tratadas acima pela estrutura de decisão IF
    return $dados;

    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    ///------------------------------------------Consulta------------------------------------------///
    //////////////////////////////////////////////////////////////////////////////////////////////////

    public function consultar($codigo, $sigla, $descricao){
        
        //Query de seleção
        $sql = "select * from unid_medida where estatus = ''";
        
        if ($codigo != '' && $codigo != 0) $sql = $sql . " and cod_unidade = '$codigo'";
        if ($sigla != '') $sql = $sql . " and sigla = '$sigla'";
        if ($descricao != '') $sql = $sql . " and descricao like '%$descricao%'";
        
        $retorno = $this->db->query($sql);

        if($retorno->num_rows() > 0){
            $dados = array('codigo' => 1,
                           'msg' => 'Dados consultados corretamente',
                           'resultado' => $retorno->result());

        }else{
            $dados = array('codigo' => 6,
                           'msg' => 'Dados não encontrados');

        }
    //Retornando o resultado tratado pela estrutura if de decisão acima
    return $dados;

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Alterar-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function alterar($codigo, $sigla, $descricao, $usuario){
        
        //Verificação - Integridade do campo usuário

        $this->load->model('m_verificacaoGeral');

        $ver_user = $this->m_verificacaoGeral->verificacaoUser($usuario); 
        $ver_userDes = $this->m_verificacaoGeral->verificacaoUserDesativado($usuario);
       
        //Verificação das informações submetidas

        if($ver_user['codigo'] == 12 && $ver_userDes['codigo'] == 15){
           
            $ver_unidMedUpdate = $this->m_verificacaoGeral->verificaCamposUnidMedida($codigo, $sigla, $descricao);

            if($ver_unidMedUpdate['codigo'] == 17){
                //Begin - Transaction
                $this->db->trans_begin();
                
                $sql = "update unid_medida ";

                if($sigla != '') $sql = $sql . "set sigla = '$sigla'";

                if($descricao != '' && $sigla != '') $sql = $sql . ", descricao = '$descricao'";
                elseif($descricao != '') $sql = $sql . "set descricao = '$descricao'";

                $sql = $sql . " where cod_unidade = '$codigo' and estatus = ''";
                $this->db->query($sql);

            }else{ $sql = ""; }
        }else{ $sql = ""; }

        //Inserção ocorreu com sucesso ou não (Unidade de Medida)
        if($this->db->affected_rows() > 0 && $sql != ""){

            //Banco de LOG
            $this->load->model('m_log');

            $retorno_log = $this->m_log->inserir_log($usuario, $sql);

            if($retorno_log['codigo'] == 1){
                $this->db->trans_commit(); //COMMIT
                $dados = array('codigo' => 1,
                               'msg' => 'Unidade de Medida atualizada com sucesso');
            }else{
                $this->db->trans_rollback(); //ROLLBACK
                $dados = array('codigo' => 7,
                               'msg' => 'O LOG não foi efetivado');
            }

        }elseif($ver_user['codigo'] == 13){
            $dados = array('codigo' => 8,
                           'msg' => 'O usuário fornecido não existe na base de dados');

        }elseif($ver_userDes['codigo'] == 14){
            $dados = array('codigo' => 9,
                           'msg' => 'O usuário fornecido está desativado');

        }elseif($ver_unidMedUpdate['codigo'] == 16){
            $dados = array('codigo' => 10,
                           'msg' => 'Não há atualizações nos campos informados');
        }else{
            $dados = array('codigo' => 6,
                           'msg' => 'Houve um problema na atualização da Unidade de Medida');
        }
        
        //Retorna o Array $dados com informações tratadas pela estrutura IF acima
        return $dados;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Desativar-------------------------------------------///
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    public function desativar($codigo, $usuario){

        //Necessidade de Verificação
        $sqlVerification = "select * from produtos where unid_medida = '$codigo' and estatus=''";
        
        $resultVerification = $this->db->query($sqlVerification);

        if($resultVerification->num_rows() > 0){
            $dados = array('codigo' => 3,
                           'msg' => 'A unidade informada não pode se desativada porque produtos possuem depêndencias com esta.');
        }else{
            
            //Begin - Transaction
            $this->db->trans_begin();

            $sql = "update unid_medida set estatus = 'D' where cod_unidade = '$codigo'";

            $this->db->query($sql);

            if($this->db->affected_rows() > 0){
            
                $this->load->model('m_log');

                $retorno_log = $this->m_log->inserir_log($usuario, $sql);

                if($retorno_log['codigo'] == 1){
                    $this->db->trans_commit(); //COMMIT
                    $dados = array('codigo' => 1,
                                   'msg' => 'Unidade de Medida desativada com sucesso');
                }else{
                    $this->db->trans_rollback(); //ROLLBACK
                    $dados = array('codigo' => 7,
                                   'msg' => 'O LOG não foi efetivado');
                }
        
            }else{
                $dados = array('codigo' => 8,
                               'msg' => 'Não foi possível desativar a Unidade de Medida');
            }
            return $dados;
        }
    }
}




















?>