<?php
/**
 * ArquivoCondominio Active Record
 * @author  <your-name-here>
 */
class ArquivoCondominio extends TRecord
{
    const TABLENAME = 'arquivo_condominio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('tipo_documento_id');
        parent::addAttribute('arquivo');
        parent::addAttribute('caminho');
        parent::addAttribute('condominio_id');
        parent::addAttribute('file');
    }


}
