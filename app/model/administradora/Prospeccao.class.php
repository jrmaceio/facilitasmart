<?php

class Prospeccao extends TRecord
{
    const TABLENAME  = 'prospeccao';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'max'; // {max, serial}
    
    //use SystemChangeLogTrait;
    
    private $pessoa;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('unidade_id');
        parent::addAttribute('horario_inicial');
        parent::addAttribute('horario_final');
        parent::addAttribute('titulo');
        parent::addAttribute('cor');
        parent::addAttribute('observacao');
        parent::addAttribute('condominio_id');
    }

    /**
     * Method set_pessoa
     * Sample of usage: $var->pessoa = $object;
     * @param $object Instance of Pessoa
     */
    public function set_pessoa(Pessoa $object)
    {
        $this->pessoa = $object;
        $this->pessoa_id = $object->id;
    }
    
    /**
     * Method get_pessoa
     * Sample of usage: $var->pessoa->attribute;
     * @returns Pessoa instance
     */
    public function get_pessoa()
    {
        
        // loads the associated object
        if (empty($this->pessoa))
            $this->pessoa = new Unidade($this->unidade_id);
        
        // returns the associated object
        return $this->pessoa;
    }
    
}

