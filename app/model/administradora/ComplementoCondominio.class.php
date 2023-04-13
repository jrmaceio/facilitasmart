<?php
/**
 * ComplementoCondominio Active Record
 * @author  <your-name-here>
 */
class ComplementoCondominio extends TRecord
{
    const TABLENAME = 'complemento_condominio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('condominio_id');
        parent::addAttribute('taxa_condominio');
        parent::addAttribute('vencimento');
        parent::addAttribute('qtd_unidades');
        parent::addAttribute('fechamento_id');
        parent::addAttribute('plano_contas_id');
        parent::addAttribute('boleto_conf_id');
        parent::addAttribute('descricao');
        parent::addAttribute('caminho');
    }


}
