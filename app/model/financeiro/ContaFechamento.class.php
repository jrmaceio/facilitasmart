<?php
/**
 * ContaFechamento Active Record
 * @author  <your-name-here>
 */
class ContaFechamento extends TRecord
{
    const TABLENAME = 'conta_fechamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('condominio_id');
        parent::addAttribute('ativo');
        parent::addAttribute('banco');
        parent::addAttribute('agencia');
        parent::addAttribute('dv_agencia');
        parent::addAttribute('numero_conta');
        parent::addAttribute('dv_conta');
        parent::addAttribute('cedente');
        parent::addAttribute('carteira');
        parent::addAttribute('tipo_carteira');
        parent::addAttribute('modalidade');
        parent::addAttribute('codigo_transmissao');
        parent::addAttribute('codigo_beneficiario');
        parent::addAttribute('dv_beneficiario');
        parent::addAttribute('numero_convenio');
        parent::addAttribute('tipo_documento');
        parent::addAttribute('caracteristica_titulo');
        parent::addAttribute('banco_emite_envia');
        parent::addAttribute('local_pagamento');
        parent::addAttribute('especie_documento');
        parent::addAttribute('especie_moeda');
        parent::addAttribute('instrucao_codificacao1');
        parent::addAttribute('instrucao_codificacao2');
        parent::addAttribute('instrucao_codificacao3');
        parent::addAttribute('aceite');
        parent::addAttribute('ultimo_boleto');
        parent::addAttribute('mais_dias_processamento');
        parent::addAttribute('vencimento_padrao');
        parent::addAttribute('numero_arquivo_remessa');
        parent::addAttribute('dias_protesto');
        parent::addAttribute('protesto_em_dia');
        parent::addAttribute('baixa_devolucao');
        parent::addAttribute('dias_desconto');
        parent::addAttribute('percentual_desconto');
        parent::addAttribute('dias_juros');
        parent::addAttribute('percentual_juros');
        parent::addAttribute('dias_multa');
        parent::addAttribute('percentual_multa');
        parent::addAttribute('instrucao_pagameno');
        parent::addAttribute('aplica_juros_percentual');
        parent::addAttribute('atualizacao');
        parent::addAttribute('boleto_configuracao_id');
    
    }


}
