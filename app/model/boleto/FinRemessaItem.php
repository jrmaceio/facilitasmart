<?php
/**
 * FinRemessaItem Active Record
 * @author  <your-name-here>
 */
class FinRemessaItem extends TRecord
{
    const TABLENAME = 'fin_remessa_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $fin_remessa;
    private $contas_receber;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('id_fin_remessa');
        parent::addAttribute('id_contas_receber');
    }

    
    /**
     * Method set_fin_remessa
     * Sample of usage: $fin_remessa_item->fin_remessa = $object;
     * @param $object Instance of FinRemessa
     */
    public function set_fin_remessa(FinRemessa $object)
    {
        $this->fin_remessa = $object;
        $this->id_fin_remessa = $object->id;
    }
    
    /**
     * Method get_fin_remessa
     * Sample of usage: $fin_remessa_item->fin_remessa->attribute;
     * @returns FinRemessa instance
     */
    public function get_fin_remessa()
    {
        // loads the associated object
        if (empty($this->fin_remessa))
            $this->fin_remessa = new FinRemessa($this->id_fin_remessa);
    
        // returns the associated object
        return $this->fin_remessa;
    }
    
    
    /**
     * Method set_contas_receber
     * Sample of usage: $fin_remessa_item->contas_receber = $object;
     * @param $object Instance of ContasReceber
     */
    public function set_contas_receber(ContasReceber $object)
    {
        $this->contas_receber = $object;
        $this->id_contas_receber = $object->id;
    }
    
    /**
     * Method get_contas_receber
     * Sample of usage: $fin_remessa_item->contas_receber->attribute;
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
