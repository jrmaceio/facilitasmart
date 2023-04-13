<?php
/**
 * GrupoContasReceberUnidade Active Record
 * @author  <your-name-here>
 */
class GrupoContasReceberUnidade extends TRecord
{
    const TABLENAME = 'grupo_contas_receber_unidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('grupo_id');
        parent::addAttribute('unidade_id');
    }


}
