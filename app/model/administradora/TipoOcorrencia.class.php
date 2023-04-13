<?php
/**
 * TipoOcorrencia Active Record
 * @author  <your-name-here>
 */
class TipoOcorrencia extends TRecord
{
    const TABLENAME = 'tipo_ocorrencia';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
    }


}
