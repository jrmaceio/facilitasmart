<?php
/**
 * ContaFechamentoForm Form
 * @author  <your name here>
 */
class ContaFechamentoForm extends TPage
{
    private $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContaFechamento');
        // define the form title
        $this->form->setFormTitle('Contas Fechamento');

        // add the fields
        $this->form->appendPage('Principal');
        
        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', '{resumo}','id asc'  );
                
        $ativo = new TEntry('ativo');
        
        $boleto_configuracao_id = new TEntry('boleto_configuracao_id');

        $id->setSize(100);
        $descricao->setSize('72%');
        $condominio_id->setSize('72%');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id]);
        //$this->form->addQuickField('Id', $id,  '50%' );
        //$this->form->addQuickField('Descricao', $descricao,  '100%' );
        $this->form->addFields( [new TLabel('Descrição')], [$descricao]);
        //$this->form->addQuickField('Condominio Id', $condominio_id,  '50%' );
        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id]);
        $this->form->addFields( [new TLabel('Boleto Configuracao Id')], [$boleto_configuracao_id] );
        
        //$this->form->addQuickField('Ativo', $ativo,  '100%' );    

        $this->form->appendPage('Dados Conta');
        
        $banco = new TEntry('banco');
        $agencia = new TEntry('agencia');
        $dv_agencia = new TEntry('dv_agencia');
        $numero_conta = new TEntry('numero_conta');
        $dv_conta = new TEntry('dv_conta');
        $cedente = new TEntry('cedente');
        $carteira = new TEntry('carteira');
        $tipo_carteira = new TEntry('tipo_carteira'); // eletrinica, registrada, simples
        $modalidade = new TEntry('modalidade');
        $cod_transmissao = new TEntry('cod_transmissao');
        $cod_beneficiario = new TEntry('cod_beneficiario');
        $dv_beneficiario = new TEntry('dv_beneficiario');
        $num_convenio = new TEntry('num_convenio');
        $banco_emite_envia = new TEntry('banco_emite_envia');
        
        
        $banco->setSize('72%');
        
        $this->form->addFields([new TLabel('Banco')], [$banco]);
        $this->form->addFields([new TLabel('Agência')], [$agencia], [new TLabel('DV Agência')], [$dv_agencia]);
        $this->form->addFields([new TLabel('Conta')], [$numero_conta], [new TLabel('DV Conta')], [$dv_conta]);
        $this->form->addFields([new TLabel('Cedente')], [$cedente], [new TLabel('Carteira')], [$carteira]);
        $this->form->addFields([new TLabel('Tipo Carteira')], [$tipo_carteira], [new TLabel('Modalidade')], [$modalidade]);
        $this->form->addFields([new TLabel('Código Transmissão')], [$cod_transmissao]);
        $this->form->addFields([new TLabel('Código Beneficiário')], [$cod_beneficiario], [new TLabel('DV Beneficiário')], [$dv_beneficiario]);
        $this->form->addFields([new TLabel('Número Convênio')], [$num_convenio], [new TLabel('Banco Emite e Envia')], [$banco_emite_envia]);
        
        $this->form->appendPage('Característica');
        
        $local_pagamento = new TEntry('local_pagamento');
        $especie_documento = new TEntry('especie_documento');
        $especie_moeda = new TEntry('especie_moeda');
        $aceite = new TEntry('aceite');
        $ultimo_retorno = new TEntry('ultimo_retorno');
        $ultima_remessa = new TEntry('ultima_remessa');
        $dias_protesto = new TEntry('dias_protesto');
        $baixa_devolucao = new TEntry('baixa_devolucao');
        
        $local_pagamento->setSize('100%');
        $aceite->setSize(100);
        
        $this->form->addFields([new TLabel('Local de Pagamento')], [$local_pagamento]);
        $this->form->addFields([new TLabel('Espécie do Documento')], [$especie_documento], [new TLabel('Espécie Moeda')], [$especie_moeda]);
        $this->form->addFields([new TLabel('Aceite')], [$aceite]);
        $this->form->addFields([new TLabel('Último Retorno')], [$ultimo_retorno], [new TLabel('Última Remessa')], [$ultima_remessa]);
        $this->form->addFields([new TLabel('Dias Protesto')], [$dias_protesto], [new TLabel('Baixa/Devolução')], [$baixa_devolucao]);
        
        $this->form->appendPage('Instrução');
        
        $instrucao_pagamento = new TText('instrucao_pagamento');
        
        $instrucao_pagamento->setSize('89%', 68);
        
        $this->form->addFields([new TLabel('Instrucao de Pagamento')], [$instrucao_pagamento]); 
        
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green' );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        // add the vbox inside the page
        parent::add($container); 
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new ContaFechamento;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new ContaFechamento($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
