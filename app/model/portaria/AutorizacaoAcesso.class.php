<?php
/**
 * AutorizacaoAcesso Active Record
 * @author  <your-name-here>
 */
class AutorizacaoAcesso extends TRecord
{
    const TABLENAME = 'autorizacao_acesso';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('unidade_id');
        parent::addAttribute('system_user_login');
        parent::addAttribute('nome');
        parent::addAttribute('data_inicial');
        parent::addAttribute('data_final');
        parent::addAttribute('documento');
        parent::addAttribute('usa_vaga');
        parent::addAttribute('observacao');
        parent::addAttribute('atualizacao');
    }

    /**
 
     */
    public function set_unidade(Unidade $object)
    {
        $this->unidade = $object;
        $this->unidade_id = $object->id;
    }
    
    /**
    
     */
    public function get_unidade()
    {
        
        // loads the associated object
        if (empty($this->unidade))
            $this->unidade = new Unidade($this->unidade_id);
        
        // returns the associated object
        return $this->unidade;
    }

}
