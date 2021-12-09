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
        // * M_usuario -> alterar()
        // * M_unidMedida -> inserir()
        // * M_unidMedida -> alterar()
        

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
        // * M_usuario -> alterar()
        // * M_unidMedida -> inserir()
        // * M_unidMedida -> alterar()

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

        //---//---------------------------------------------------------------------------//---//
        //---//          Verificação durante o processo de Alteração do Usuário           //---//
        //---//         Os campos não podem ser iguais aos que já estão inseridos         //---//
        //---//---------------------------------------------------------------------------//---//

        //Models utilitárias da função abaixo
        // * M_usuario -> alterar()

        public function verificaCamposUsuario($usuario, $senha, $nome, $tipo_usuario){
            // Verificando os dados a serem atualizados 
            // (O MySQL não aceita a atualização dos dados exatamente igual)

            $sqlVerification = "select * from usuarios where ";

            if($senha != '') $sqlVerification = $sqlVerification . "senha = md5('$senha')";
 
            if($nome != '' && $senha != '') $sqlVerification = $sqlVerification . " and nome = '$nome'";
            elseif($nome != '') $sqlVerification = $sqlVerification . "nome = '$nome'";
 
            if($tipo_usuario && ( $senha != '' || $nome != '')) $sqlVerification = $sqlVerification . " and tipo = '$tipo_usuario'";
            elseif($nome != '') $sqlVerification = $sqlVerification . "tipo = '$tipo_usuario'";

            $sqlVerification = $sqlVerification . " and usuario = '$usuario' and estatus = ''";
 
            $result = $this->db->query($sqlVerification);

            if($result->num_rows() > 0){
                $ver_user = array('codigo' => 16,
                                  'msg' => 'Os campos estão iguais');        
            }else{
                $ver_user = array('codigo' => 17,
                                  'msg' => 'Os campos estão diferentes');
            }       
            return $ver_user;     
        }

        ///////////////////////////////////////////////////////////////////////////////////////
        //----------------//---------------//UnidadeMedida//---------------//----------------//
        ///////////////////////////////////////////////////////////////////////////////////////

        //---//-------------------------------------------------------------------------//---//
        //---//    Verificação durante o processo de Alteração da Unidade de Medida     //---//
        //---//        Os campos não podem ser iguais ao que já estão inseridos         //---//
        //---//-------------------------------------------------------------------------//---//

        //Models utilitárias da função abaixo
        // * M_unidMedida -> alterar()

        public function verificaCamposUnidMedida($codigo, $sigla, $descricao){
            // Verificando os dados a serem atualizados 
            // (O MySQL não aceita a atualização dos dados exatamente igual)

            $sqlVerification = "select * from unid_medida where ";

            if($sigla != '') $sqlVerification = $sqlVerification . "sigla = '$sigla'";
 
            if($descricao != '' && $sigla != '') $sqlVerification = $sqlVerification . " and descricao = '$descricao'";
            elseif($descricao != '') $sqlVerification = $sqlVerification . "descricao = '$descricao'";
 
            $sqlVerification = $sqlVerification . " and cod_unidade = '$codigo' and estatus = ''";
 
            $result = $this->db->query($sqlVerification);

            if($result->num_rows() > 0){
                $ver_unid = array('codigo' => 16,
                                  'msg' => 'Os campos estão iguais');        
            }else{
                $ver_unid = array('codigo' => 17,
                                  'msg' => 'Os campos estão diferentes');
            }       
            return $ver_unid;     
        }
    
        //---//------------------------------------------------------------------------//---//
        //---//   Verificação durante o processo de Desativação da Unidade de Medida   //---//
        //---//          Caso haver dependência com produto(s), informa erro           //---//
        //---//------------------------------------------------------------------------//---//

        //Models utilitárias da função abaixo
        // * M_unidMedida -> desativar()

        public function verificacaoDepProduto($codigo){
            
            $sqlVerification = "select * from produtos where unid_medida = '$codigo' and estatus=''";
            
            $resultVerification = $this->db->query($sqlVerification);

            if($resultVerification->num_rows() > 0){
                $ver_unid = array('codigo' => 18,
                           'msg' => 'Há dependências - UnidMedida e Produto');
            }else{
                $ver_unid = array('codigo' => 19,
                               'msg' => 'Não há dependências - UnidMedida e Produto');
            }
            return $ver_unid;
        }
    
        //Models utilitárias da função abaixo
        // * M_unidMedida -> alterar()

        public function verificacaoCodUnidMedida($codigo){

            $result = $this->db->query("select * from unid_medida where cod_unidade = '$codigo' and estatus=''");
            
            if($result->num_rows() > 0){
                $ver_unid = array('codigo' => 20,
                                  'msg' => 'Há a existência do código informado');        
            }else{
                $ver_unid = array('codigo' => 21,
                                  'msg' => 'Não há a existência do código informado');
            }       
            return $ver_unid;   
        }

        //Models utilitárias da função abaixo
        // * M_unidMedida -> alterar()

        public function verificacaoUnidMedidaDes($codigo){

            $result = $this->db->query("select * from unid_medida where cod_unidade = '$codigo' and estatus='D'");
            
            if($result->num_rows() > 0){
                $ver_unid = array('codigo' => 22,
                                  'msg' => 'A Unidade de Medida informada está desativada');        
            }else{
                $ver_unid = array('codigo' => 23,
                                  'msg' => 'A Unidade de Medida informada está ativa');
            }       
            return $ver_unid;   
        }

    }
?>