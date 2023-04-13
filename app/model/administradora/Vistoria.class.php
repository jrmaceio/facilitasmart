<?php
/**
 * Vistoria Active Record
 * @author  <your-name-here>
 */
class Vistoria extends TRecord
{
    const TABLENAME = 'vistoria';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_hora');
        parent::addAttribute('setor');
        parent::addAttribute('descricao');
        parent::addAttribute('vistoriante');
        parent::addAttribute('status');
        parent::addAttribute('condominio_id');
    }

    /**
     * Method set_cidade
     * Sample of usage: $var->cidade = $object;
     * @param $object Instance of Cidade
     */
    public function set_condominio(Condominio $object)
    {
        $this->condominio = $object;
        $this->condominio_id = $object->id;
    }
    
    /**
     * Method get_cidade
     * Sample of usage: $var->cidade->attribute;
     * @returns Cidade instance
     */
    public function get_condominio()
    {
        
        // loads the associated object
        if (empty($this->condominio))
            $this->condominio = new Condominio($this->condominio_id);
        
        // returns the associated object
        return $this->condominio;
    }
}
