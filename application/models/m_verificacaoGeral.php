<?php
defined('BASEPATH')OR exit('No direct script access allowed');

    class M_verificacaoGeral extends CI_Model{
        
        /////////////////////////////////////////////////////////////////////////////////
        //----------------//---------------//Usuário//---------------//----------------//
        /////////////////////////////////////////////////////////////////////////////////

        //---//------------------------------------------------------------------//---//
        //---//Verificação durante a inserção do usuário - Campo usuario repetido//---//
        //---//Verificação da integridade - Campo usuário na FK - Unidade Medida //---//
        //---//------------------------------------------------------------------//---//

        //Models utilitárias da função abaixo
        // * M_usuario -> inserir()
        // * M_unidMedida -> inserir()

        public function verificacaoUser($usuario){

            $result = $this->db->query("select * from usuarios where usuario = '$usuario'");
            
            if($result->num_rows() > 0){
                $ver_user = array('codigo' => 12,
                                  'msg' => 'O usuário já está cadastrado na base de dados');        
            }else{
                $ver_user = array('codigo' => 13,
                                  'msg' => 'O usuário não está cadastrado na base de dados');
            }       
            return $ver_user;     
        }

        //---//-------------------------------------------------------------------------//---//
        //---// Verificação durante o processo de Login do usuário - Usuário Desativado //---//
        //---//     Verificação da Desativação - Caso o usuário já esteja Desativado    //---//
        //---//-------------------------------------------------------------------------//---//

        //Models utilitárias da função abaixo
        // * M_acesso -> logar()
        // * M_usuario -> desativar()

        public function verificacaoUserDesativado($usuario){

            $result = $this->db->query("select * from usuarios where usuario = '$usuario' and estatus='D'");
            
            if($result->num_rows() > 0){
                $ver_user = array('codigo' => 14,
                                  'msg' => 'O usuário está desativado');        
            }else{
                $ver_user = array('codigo' => 15,
                                  'msg' => 'O usuário está ativado');
            }       
            return $ver_user;     
        }

    }

?>