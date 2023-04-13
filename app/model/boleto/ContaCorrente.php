<?php
/**
 * ContaCorrente Active Record
 * @author  <your-name-here>
 */
class ContaCorrente extends TRecord
{
    const TABLENAME = 'conta_corrente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    private $condominio;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('conta');
        parent::addAttribute('descricao');
        parent::addAttribute('agencia');
        parent::addAttribute('titular');
        parent::addAttribute('tipo_conta');
        parent::addAttribute('id_banco');
		parent::addAttribute('convenio');
		parent::addAttribute('posto');
		parent::addAttribute('arq_remessa');
		parent::addAttribute('arq_retorno');
		parent::addAttribute('status');
		parent::addAttribute('tipo_inscricao');
		parent::addAttribute('inscricao_cnpj');
		parent::addAttribute('inscricao_cpf');
    }

    public function set_condominio(Condominio $object)
    {
        $this->condominio = $object;
        $this->id_condominio = $object->id;
    }

    public function get_condominio()
    {
        // loads the associated object
        if (empty($this->condominio))
            $this->condominio = new Condominio($this->id_condominio);
    
        // returns the associated object
        return $this->condominio;
    }

    /**
     * Method set_banco
     * Sample of usage: $conta_corrente->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $conta_corrente->banco->attribute;
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
