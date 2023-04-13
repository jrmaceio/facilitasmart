<?php
/**
 * Condominio Active Record
 * @author  <your-name-here>
 */
class Condominio extends TRecord
{
    const TABLENAME = 'condominio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('resumo');
        parent::addAttribute('nome');
        parent::addAttribute('cnpj');
        parent::addAttribute('inscricao_municipal');
        parent::addAttribute('cep');
        parent::addAttribute('endereco');
        parent::addAttribute('numero');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('estado');
        parent::addAttribute('site');
        parent::addAttribute('email');
        parent::addAttribute('telefone1');
        parent::addAttribute('telefone2');
        parent::addAttribute('active');
        parent::addAttribute('dt_cadastro');
  
        parent::addAttribute('agencia_repasse');
        parent::addAttribute('conta_repasse');
        parent::addAttribute('banco_repasse');
        parent::addAttribute('ddd_pjbank');
        parent::addAttribute('telefone_pjbank');
        parent::addAttribute('agencia_parceiro_pjbank');
        parent::addAttribute('email_pjbank');
        
        parent::addAttribute('status_pjbank');
        parent::addAttribute('msg_pjbank');
        parent::addAttribute('credencial_pjbank');
        parent::addAttribute('chave_pjbank');
        parent::addAttribute('conta_virtual_pjbank');
        parent::addAttribute('agencia_virtual_pjbank');
        
        parent::addAttribute('multa');
        parent::addAttribute('juros');
        parent::addAttribute('desconto');
        parent::addAttribute('classe_id_desconto');
        
        parent::addAttribute('status_cd_pjbank');
        parent::addAttribute('msg_cd_pjbank');
        parent::addAttribute('credencial_cd_pjbank');
        parent::addAttribute('chave_cd_pjbank');
        
        parent::addAttribute('administradora');
        parent::addAttribute('split_nome');
        parent::addAttribute('split_cnpj');
        parent::addAttribute('split_banco_repasse');
        parent::addAttribute('split_agencia_repasse');
        parent::addAttribute('split_conta_repasse');
        parent::addAttribute('split_tipo_conta_repasse');
        
        parent::addAttribute('cobranca_email'); // emite avisos de cobrança por email
        parent::addAttribute('prazo_cobranca'); // dias apos o vencimento para incluir nos avisos

        parent::addAttribute('chave_webhook_pjbank');
       
    }

}
