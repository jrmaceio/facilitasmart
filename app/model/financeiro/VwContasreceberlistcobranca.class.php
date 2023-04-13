<?php
/**
 * VwContasreceberlistcobranca Active Record
 * @author  <your-name-here>
 */
class VwContasreceberlistcobranca extends TRecord
{
    const TABLENAME = 'vw_ContasReceberListCobranca';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('nome');
        parent::addAttribute('valor');
        parent::addAttribute('condominio_id');
        parent::addAttribute('cobrancas');
    }


}
