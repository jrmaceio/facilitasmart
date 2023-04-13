<?php
/**
 * Vistante Active Record
 * @author  <your-name-here>
 */
class Visitante extends TRecord
{
    const TABLENAME = 'visitante';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('status');
        parent::addAttribute('posto_id');
        parent::addAttribute('motivo_funcao_finalidade');
        parent::addAttribute('documento');
        parent::addAttribute('telefone');
        parent::addAttribute('observacao');
        parent::addAttribute('permissao_dom_ini');
        parent::addAttribute('permissao_dom_fim');
        parent::addAttribute('permissao_seg_ini');
        parent::addAttribute('permissao_seg_fim');
        parent::addAttribute('permissao_ter_ini');
        parent::addAttribute('permissao_ter_fim');
        parent::addAttribute('permissao_qua_ini');
        parent::addAttribute('permissao_qua_fim');
        parent::addAttribute('permissao_qui_ini');
        parent::addAttribute('permissao_qui_fim');
        parent::addAttribute('permissao_sex_ini');
        parent::addAttribute('permissao_sex_fim');
        parent::addAttribute('permissao_sab_ini');
        parent::addAttribute('permissao_sab_fim');
        parent::addAttribute('data_permitida');
        parent::addAttribute('data_ini');
        parent::addAttribute('data_fim');
        parent::addAttribute('unidade_id');
    }


}
