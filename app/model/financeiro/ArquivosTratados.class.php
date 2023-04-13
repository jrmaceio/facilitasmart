<?php
/**
 * ArquivosTratados Active Record
 * @author  <your-name-here>
 */
class ArquivosTratados extends TRecord
{
    const TABLENAME = 'arquivos_tratados';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('arquivo');
        parent::addAttribute('descricao');
        parent::addAttribute('data');
        parent::addAttribute('itens');
        parent::addAttribute('liquidados');
        parent::addAttribute('baixas');
        parent::addAttribute('remessa');
        parent::addAttribute('outros');
    }


}
