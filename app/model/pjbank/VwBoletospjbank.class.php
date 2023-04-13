<?php
/**
 * VwBoletospjbank Active Record
 * @author  <your-name-here>
 */
class VwBoletospjbank extends TRecord
{
    const TABLENAME = 'vw_BoletosPjBank';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('nome');
        parent::addAttribute('mes_ref');
        parent::addAttribute('valor');
        parent::addAttribute('dt_vencimento');
        parent::addAttribute('pjbank_pedido_numero');
        parent::addAttribute('condominio_id');
        parent::addAttribute('multa_boleto_cobranca');
        parent::addAttribute('juros_boleto_cobranca');
        parent::addAttribute('desconto_boleto_cobranca');
        parent::addAttribute('dt_limite_desconto_boleto_cobranca');
    }


}
