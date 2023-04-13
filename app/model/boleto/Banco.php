<?php
/**
 * Banco Active Record
 * @author  <your-name-here>
 */
class Banco extends TRecord
{
    const TABLENAME = 'banco';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
 
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('codigo_bacen');
        parent::addAttribute('sigla');
        parent::addAttribute('descricao');
        parent::addAttribute('status');
    }

}
