<?php
/**
 * Visitante Active Record
 * @author  <your-name-here>
 */
class Visitante extends TRecord
{
    const TABLENAME = 'visitante';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nomevisitante');
        parent::addAttribute('documento');
        parent::addAttribute('tipovisitante');
        parent::addAttribute('datainicial');
        parent::addAttribute('datafinal');
        parent::addAttribute('placaveiculo');
        parent::addAttribute('observacao');
        parent::addAttribute('condominio_id');
        parent::addAttribute('unidade_id');
    }


}
