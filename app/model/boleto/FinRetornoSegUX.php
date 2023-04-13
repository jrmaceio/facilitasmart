<?php
/**
 * FinRetornoSegUX Active Record
 * @author  <your-name-here>
 */
class FinRetornoSegUX extends TRecord
{
    const TABLENAME = 'fin_retorno_seg_ux';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $condominio;
    private $fin_retorno;
    private $tipo_movto_retorno;
    private $contas_receber;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_condominio');
        parent::addAttribute('id_fin_retorno');
        parent::addAttribute('seguimento');
        parent::addAttribute('sequencia');
        parent::addAttribute('id_movto_retorno');
        parent::addAttribute('dt_baixa');
        parent::addAttribute('dt_taxa');
        parent::addAttribute('dt_credito');
        parent::addAttribute('vlr_juros');
        parent::addAttribute('vlr_descto');
        parent::addAttribute('vlr_abatimento');
        parent::addAttribute('vlr_pago');
        parent::addAttribute('vlr_credito');
        parent::addAttribute('vlr_out_desp');
        parent::addAttribute('vlr_out_credito');
        parent::addAttribute('id_contas_receber');
        parent::addAttribute('docto');
        parent::addAttribute('id_fin_retornosegtx');
    }

    
    
    /**
     * Method set_fin_retorno
     * Sample of usage: $fin_retorno_seg_ux->fin_retorno = $object;
     * @param $object Instance of FinRetorno
     */
    public function set_fin_retorno(FinRetorno $object)
    {
        $this->fin_retorno = $object;
        $this->id_fin_retorno = $object->id;
    }
    
    /**
     * Method get_fin_retorno
     * Sample of usage: $fin_retorno_seg_ux->fin_retorno->attribute;
     * @returns FinRetorno instance
     */
    public function get_fin_retorno()
    {
        // loads the associated object
        if (empty($this->fin_retorno))
            $this->fin_retorno = new FinRetorno($this->id_fin_retorno);
    
        // returns the associated object
        return $this->fin_retorno;
    }
    
    
    /**
     * Method set_tipo_movto_retorno
     * Sample of usage: $fin_retorno_seg_ux->tipo_movto_retorno = $object;
     * @param $object Instance of TipoMovtoRetorno
     */
    public function set_tipo_movto_retorno(TipoMovtoRetorno $object)
    {
        $this->tipo_movto_retorno = $object;
        $this->id_movto_retorno = $object->id;
    }
    
    /**
     * Method get_tipo_movto_retorno
     * Sample of usage: $fin_retorno_seg_ux->tipo_movto_retorno->attribute;
     * @returns TipoMovtoRetorno instance
     */
    public function get_tipo_movto_retorno()
    {
        // loads the associated object
        if (empty($this->tipo_movto_retorno))
            $this->tipo_movto_retorno = new TipoMovtoRetorno($this->id_movto_retorno);
    
        // returns the associated object
        return $this->tipo_movto_retorno;
    }
    


    /**
     * Method set_contas_receber
     * Sample of usage: $fin_retorno_seg_ux->contas_receber = $object;
     * @param $object Instance of ContasReceber
     */
    public function set_contas_receber(ContasReceber $object)
    {
        $this->contas_receber = $object;
        $this->id_contas_receber = $object->id;
    }
    
    /**
     * Method get_contas_receber
     * Sample of usage: $fin_retorno_seg_ux->contas_receber->attribute;
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
