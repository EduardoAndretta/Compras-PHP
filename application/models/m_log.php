<?php
defined('BASEPATH')OR exit('No direct script access allowed');

class M_log extends CI_Model {

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    ///-------------------------------------------Inserir-------------------------------------------///
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function inserir_log($usuario, $comando){
            
        //Instância do Banco de Log
        $dblog = $this->load->database('log',TRUE);
        
        //Chamada da função na Helper para nos auxiliar
        $comando = troca_caractere($comando);
        
        //Query de inserção de dados
        $dblog->query("insert into log(usuario, comando)
                       values ('$usuario','$comando')");
        
                       //Verificação da Inserção (Ocorreu com sucesso ou não)
        if($dblog->affected_rows() > 0){
            $dados = array('codigo' => 1,
                           'msg' => 'Log cadastrado com sucesso');
        }else{
            $dados = array('codigo' => 8,
                           'msg' => 'Houve algum problema na inserção do log');
        }
        
        //Finalizando a conexão com o banco Log
        $dblog->close();
        
        //Retorno (Array $dados com informações tratadas)
        return $dados;
      
    }

}

?>