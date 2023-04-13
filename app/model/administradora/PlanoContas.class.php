<?php
/**
 * PlanoContas Active Record
 * @author  <your-name-here>
 */
class PlanoContas extends TRecord
{
    const TABLENAME = 'plano_contas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('codigo');
        parent::addAttribute('descricao');
        parent::addAttribute('tipo');
    }

}
