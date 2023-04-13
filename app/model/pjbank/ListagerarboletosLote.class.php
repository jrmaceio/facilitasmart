<?php
/**
 * Listagerarboletoslote Active Record
 * @author  <your-name-here>
 */
class Listagerarboletoslote extends TRecord
{
    const TABLENAME = 'ListaGerarBoletosLote';
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
        parent::addAttribute('grupo_id');
        parent::addAttribute('valor_titulo');
        parent::addAttribute('desconto_titulo');
    }


}
