<?php
/**
 * BoletoConfiguracao Active Record
 * @author  <your-name-here>
 */
class BoletoConfiguracao extends TRecord
{
    const TABLENAME = 'boleto_configuracao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('beneficiario');
        parent::addAttribute('descricao');
        parent::addAttribute('data_atualizacao');
    }


}
