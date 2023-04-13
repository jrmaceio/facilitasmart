<?php
/**
 * VwContasreceberlistcobranca2 Active Record
 * @author  <your-name-here>
 */
class VwContasreceberlistcobranca2 extends TRecord
{
    const TABLENAME = 'vw_ContasReceberListCobranca2';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('bloco_quadra');
        parent::addAttribute('unidade');
        parent::addAttribute('nome');
        parent::addAttribute('valor');
        parent::addAttribute('condominio_id');
        parent::addAttribute('cobrancas');
    }


}
