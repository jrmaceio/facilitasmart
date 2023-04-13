<?php
/**
 * GrupoContasReceber Active Record
 * @author  <your-name-here>
 */
class GrupoContasReceber extends TRecord
{
    const TABLENAME = 'grupo_contas_receber';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('condominio_id');
    }


}
