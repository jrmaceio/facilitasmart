<?php
/**
 * VwContasreceberboleto Active Record
 * @author  <your-name-here>
 */
class VwContasreceberboleto extends TRecord
{
    const TABLENAME = 'vw_ContasReceberBoleto';
    const PRIMARYKEY= 'receber_mes_ref';
    const IDPOLICY =  'max'; // {max, serial}
    
    
  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
      parent::__construct($id, $callObjectLoad);
      parent::addAttribute('receber_valor_boleto');
      parent::addAttribute('receber_imovel_id');
      parent::addAttribute('receber_dt_vencimento');
      parent::addAttribute('receber_dt_lancamento');
      parent::addAttribute('receber_cobranca');
      parent::addAttribute('unidade_id');
      parent::addAttribute('unidade_descricao');
      parent::addAttribute('pes_nome');
      parent::addAttribute('pes_end');
      parent::addAttribute('pes_bairro');
      parent::addAttribute('pes_cidade');
      parent::addAttribute('pes_estado');
      parent::addAttribute('pes_cep');
      parent::addAttribute('cc_agencia');
      parent::addAttribute('cc_conta');
      parent::addAttribute('cc_dv_conta');
      parent::addAttribute('cc_cedente');
      parent::addAttribute('cc_carteira');
      parent::addAttribute('cc_especie_doc_boleto');
      parent::addAttribute('cc_especie_doc_remessa');
      parent::addAttribute('cc_dias_protesto');
      parent::addAttribute('cc_dias_devolucao');
      parent::addAttribute('cc_producao');
      parent::addAttribute('cc_conta_com_cod_cedente');
      parent::addAttribute('imovel_resumo');
      parent::addAttribute('imovel_nome');
      parent::addAttribute('imovel_endereco');
      parent::addAttribute('imovel_bairro');
      parent::addAttribute('imovel_cidade');
      parent::addAttribute('imovel_estado');
      parent::addAttribute('imovel_cep');
  }


}
