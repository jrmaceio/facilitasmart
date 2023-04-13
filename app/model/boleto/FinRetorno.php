<?php
/**
 * FinRetorno Active Record
 * @author  <your-name-here>
 */
class FinRetorno extends TRecord
{
    const TABLENAME = 'fin_retorno';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $banco;
    private $conta_corrente;
    private $layout_cnab;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('id_banco');
        parent::addAttribute('id_conta_corrente');
        parent::addAttribute('id_layout_cnab');
        parent::addAttribute('numero_retorno');
        parent::addAttribute('operacao');
        parent::addAttribute('dt_retorno');
        parent::addAttribute('dt_processamento');
        parent::addAttribute('nr_cedente');
    }

    
    
    /**
     * Method set_banco
     * Sample of usage: $fin_retorno->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $fin_retorno->banco->attribute;
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
    
    
    /**
     * Method set_conta_corrente
     * Sample of usage: $fin_retorno->conta_corrente = $object;
     * @param $object Instance of ContaCorrente
     */
    public function set_conta_corrente(ContaCorrente $object)
    {
        $this->conta_corrente = $object;
        $this->id_conta_corrente = $object->id;
    }
    
    /**
     * Method get_conta_corrente
     * Sample of usage: $fin_retorno->conta_corrente->attribute;
     * @returns ContaCorrente instance
     */
    public function get_conta_corrente()
    {
        // loads the associated object
        if (empty($this->conta_corrente))
            $this->conta_corrente = new ContaCorrente($this->id_conta_corrente);
    
        // returns the associated object
        return $this->conta_corrente;
    }
    
    
    /**
     * Method set_layout_cnab
     * Sample of usage: $fin_retorno->layout_cnab = $object;
     * @param $object Instance of LayoutCnab
     */
    public function set_layout_cnab(LayoutCnab $object)
    {
        $this->layout_cnab = $object;
        $this->id_layout_cnab = $object->id;
    }
    
    /**
     * Method get_layout_cnab
     * Sample of usage: $fin_retorno->layout_cnab->attribute;
     * @returns LayoutCnab instance
     */
    public function get_layout_cnab()
    {
        // loads the associated object
        if (empty($this->layout_cnab))
            $this->layout_cnab = new LayoutCnab($this->id_layout_cnab);
    
        // returns the associated object
        return $this->layout_cnab;
    }
    


}
