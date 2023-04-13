<?php
/**
 * Setor Active Record
 * @author  <your-name-here>
 */
class Setor extends TRecord
{
    const TABLENAME = 'setor';
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
