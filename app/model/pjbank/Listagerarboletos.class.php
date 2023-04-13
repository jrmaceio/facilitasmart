<?php
/**
 * Listagerarboletos Active Record
 * @author  <your-name-here>
 */
class Listagerarboletos extends TRecord
{
    const TABLENAME = 'ListaGerarBoletos';
    const PRIMARYKEY= 'condominio_id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('resumo');
        parent::addAttribute('bloco_quadra');
        parent::addAttribute('descricao');
        parent::addAttribute('nome');
        parent::addAttribute('gera_titulo');
        parent::addAttribute('valor_titulo');
        parent::addAttribute('desconto_titulo');
    }


}
