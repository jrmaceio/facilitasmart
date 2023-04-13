<?php
/**
 * Acesso Active Record
 * @author  <your-name-here>
 */
class Acesso extends TRecord
{
    const TABLENAME = 'acesso';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_patrulheiro');
        parent::addAttribute('id_posto');
        parent::addAttribute('id_visitante');
        parent::addAttribute('data');
        parent::addAttribute('fluxo');
        parent::addAttribute('veiculo');
        parent::addAttribute('observacao');
    }


}
