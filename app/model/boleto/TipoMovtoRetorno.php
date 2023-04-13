<?php
/**
 * TipoMovtoRetorno Active Record
 * @author  <your-name-here>
 */
class TipoMovtoRetorno extends TRecord
{
    const TABLENAME = 'tipo_movto_retorno';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $banco;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_banco');
        parent::addAttribute('codigo');
        parent::addAttribute('descricao');
        parent::addAttribute('status');
    }

    
    /**
     * Method set_banco
     * Sample of usage: $tipo_movto_retorno->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $tipo_movto_retorno->banco->attribute;
     * @returns Banco instance
     */
    public function get_banco()
    {
        // loads the associated object
        if (empty($this->banco))
            $this->banco = new Banco($this->id_banco);
    
        // returns the associated object
        return $this->banco;
    }
    


}
