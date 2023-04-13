<?php
/**
 * TipoMovtoRetornoItem Active Record
 * @author  <your-name-here>
 */
class TipoMovtoRetornoItem extends TRecord
{
    const TABLENAME = 'tipo_movto_retorno_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $tipo_movto_retorno;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_movto_retorno');
        parent::addAttribute('codigo');
        parent::addAttribute('descricao');
    }

    
    /**
     * Method set_tipo_movto_retorno
     * Sample of usage: $tipo_movto_retorno_item->tipo_movto_retorno = $object;
     * @param $object Instance of TipoMovtoRetorno
     */
    public function set_tipo_movto_retorno(TipoMovtoRetorno $object)
    {
        $this->tipo_movto_retorno = $object;
        $this->id_tipo_movto_retorno = $object->id;
    }
    
    /**
     * Method get_tipo_movto_retorno
     * Sample of usage: $tipo_movto_retorno_item->tipo_movto_retorno->attribute;
     * @returns TipoMovtoRetorno instance
     */
    public function get_tipo_movto_retorno()
    {
        // loads the associated object
        if (empty($this->tipo_movto_retorno))
            $this->tipo_movto_retorno = new TipoMovtoRetorno($this->id_tipo_movto_retorno);
    
        // returns the associated object
        return $this->tipo_movto_retorno;
    }
    


}
