<?php
/**
 * ContaCorrenteNossoNumero Active Record
 * @author  <your-name-here>
 */
class ContaCorrenteNossoNumero extends TRecord
{
    const TABLENAME = 'conta_corrente_nosso_numero';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $conta_corrente;
    private $contas_receber;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('id_conta_corrente');
        parent::addAttribute('sequencial');
        parent::addAttribute('id_contas_receber');
    }


    /**
     * Method set_conta_corrente
     * Sample of usage: $conta_corrente_nosso_numero->conta_corrente = $object;
     * @param $object Instance of ContaCorrente
     */
    public function set_conta_corrente(ContaCorrente $object)
    {
        $this->conta_corrente = $object;
        $this->id_conta_corrente = $object->id;
    }
    
    /**
     * Method get_conta_corrente
     * Sample of usage: $conta_corrente_nosso_numero->conta_corrente->attribute;
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
     * Method set_contas_receber
     * Sample of usage: $conta_corrente_nosso_numero->contas_receber = $object;
     * @param $object Instance of ContasReceber
     */
    public function set_contas_receber(ContasReceber $object)
    {
        $this->contas_receber = $object;
        $this->id_contas_receber = $object->id;
    }
    
    /**
     * Method get_contas_receber
     * Sample of usage: $conta_corrente_nosso_numero->contas_receber->attribute;
     * @returns ContasReceber instance
     */
    public function get_contas_receber()
    {
        // loads the associated object
        if (empty($this->contas_receber))
            $this->contas_receber = new ContasReceber($this->id_contas_receber);
    
        // returns the associated object
        return $this->contas_receber;
    }
    


}
