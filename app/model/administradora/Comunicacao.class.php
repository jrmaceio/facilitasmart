<?php
/**
 * Comunicacao Active Record
 * @author  <your-name-here>
 */
class Comunicacao extends TRecord
{
    const TABLENAME = 'comunicacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('condominio_id');
        parent::addAttribute('data_lancamento');
        parent::addAttribute('tipo');
        parent::addAttribute('titulo');
        parent::addAttribute('conteudo');
        parent::addAttribute('rodape');
        parent::addAttribute('status');
    }


}
