<?php
/**
 * DiarioPortaria Active Record
 * @author  <your-name-here>
 */
class DiarioPortaria extends TRecord
{
    const TABLENAME = 'diario_portaria';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_dia');
        parent::addAttribute('colaborador');
        parent::addAttribute('data_plantao');
        parent::addAttribute('resumo');
        parent::addAttribute('descricao');
        parent::addAttribute('status');
        parent::addAttribute('condominio_id');
        parent::addAttribute('data_tratativa');
        parent::addAttribute('tratativa');
        parent::addAttribute('system_user_login');
        parent::addAttribute('system_user_email');
        parent::addAttribute('atualizacao');
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
