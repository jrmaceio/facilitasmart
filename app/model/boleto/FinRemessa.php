<?php
/**
 * FinRemessa Active Record
 * @author  <your-name-here>
 */
class FinRemessa extends TRecord
{
    const TABLENAME = 'fin_remessa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $banco;
    private $conta_corrente;
    private $tipo_movto_remessa;
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
        parent::addAttribute('numero_remessa');
        parent::addAttribute('dt_emissao');
        parent::addAttribute('forma_selecao');
        parent::addAttribute('dt_vecto_inicial');
        parent::addAttribute('dt_vecto_final');
        parent::addAttribute('tipo_transacao');
        parent::addAttribute('carteira');
        parent::addAttribute('id_movto_remessa');
        parent::addAttribute('codigo_protesto');
        parent::addAttribute('dias_protesto');
        parent::addAttribute('codigo_baixa_devolucao');
        parent::addAttribute('dias_baixa_devolucao');
        parent::addAttribute('vlr_total_titulos');
        parent::addAttribute('qtde_total_titulos');
        parent::addAttribute('caminho');
        parent::addAttribute('arquivo');
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
     * Sample of usage: $fin_remessa->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $fin_remessa->banco->attribute;
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
     * Sample of usage: $fin_remessa->conta_corrente = $object;
     * @param $object Instance of ContaCorrente
     */
    public function set_conta_corrente(ContaCorrente $object)
    {
        $this->conta_corrente = $object;
        $this->id_conta_corrente = $object->id;
    }
    
    /**
     * Method get_conta_corrente
     * Sample of usage: $fin_remessa->conta_corrente->attribute;
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
     * Method set_tipo_movto_remessa
     * Sample of usage: $fin_remessa->tipo_movto_remessa = $object;
     * @param $object Instance of TipoMovtoRemessa
     */
    public function set_tipo_movto_remessa(TipoMovtoRemessa $object)
    {
        $this->tipo_movto_remessa = $object;
        $this->id_movto_remessa = $object->id;
    }
    
    /**
     * Method get_tipo_movto_remessa
     * Sample of usage: $fin_remessa->tipo_movto_remessa->attribute;
     * @returns TipoMovtoRemessa instance
     */
    public function get_tipo_movto_remessa()
    {
        // loads the associated object
        if (empty($this->tipo_movto_remessa))
            $this->tipo_movto_remessa = new TipoMovtoRemessa($this->id_movto_remessa);
    
        // returns the associated object
        return $this->tipo_movto_remessa;
    }
    

    
    /**
     * Method set_layout_cnab
     * Sample of usage: $fin_remessa->layout_cnab = $object;
     * @param $object Instance of LayoutCnab
     */
    public function set_layout_cnab(LayoutCnab $object)
    {
        $this->layout_cnab = $object;
        $this->id_layout_cnab = $object->id;
    }
    
    /**
     * Method get_layout_cnab
     * Sample of usage: $fin_remessa->layout_cnab->attribute;
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
