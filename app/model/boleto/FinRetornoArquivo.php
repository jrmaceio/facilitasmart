<?php
/**
 * FinRetornoArquivo Active Record
 * @author  <your-name-here>
 */
class FinRetornoArquivo extends TRecord
{
    const TABLENAME = 'fin_retorno_arquivo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $banco;
    private $conta_corrente;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('id_banco');
        parent::addAttribute('id_conta_corrente');
        parent::addAttribute('caminho');
        parent::addAttribute('arquivo');
        parent::addAttribute('importado');
    }

    
   
    /**
     * Method set_banco
     * Sample of usage: $fin_retorno_arquivo->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $fin_retorno_arquivo->banco->attribute;
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
     * Sample of usage: $fin_retorno_arquivo->conta_corrente = $object;
     * @param $object Instance of ContaCorrente
     */
    public function set_conta_corrente(ContaCorrente $object)
    {
        $this->conta_corrente = $object;
        $this->id_conta_corrente = $object->id;
    }
    
    /**
     * Method get_conta_corrente
     * Sample of usage: $fin_retorno_arquivo->conta_corrente->attribute;
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
    


}
