<?php
/**
 * ContaCorrenteForm Form
 * @author  <your name here>
 */
class ContaCorrenteForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContaCorrente');
        $this->form->setFormTitle('Conta Corrente');
        
        //$unit_erp = TSession::getValue('cliente_ERP');
       // $unit_emp = TSession::getValue('userempresa');

        // create the form fields
        $id = new TEntry('id');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);

        $conta = new TEntry('conta');
        $conta->forceUpperCase();
        $descricao = new TEntry('descricao');
        $descricao->forceUpperCase();
        $agencia = new TEntry('agencia');
        $agencia->forceUpperCase();
        $titular = new TEntry('titular');
        $titular->forceUpperCase();
        $tipo_conta = new TCombo('tipo_conta');
        $tipo_conta->addItems(array('C'=>'CORRENTE', 'P'=>'POUPANÇA', 'A'=>'APLICAÇÃO'));

        $convenio = new TEntry('convenio');
        $convenio->forceUpperCase();        

        $posto = new TEntry('posto');
        $posto->forceUpperCase();        

        $arq_remessa = new TEntry('arq_remessa');
        $arq_retorno = new TEntry('arq_retorno');
        
        $criteria_bco = new TCriteria();
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', '{codigo_bacen} - {sigla}','id');
        //status
        $status = new TCombo('status');
        $status->addItems(array('A' => 'ATIVO', 'I' => 'INATIVO'));
        
        $tipo_inscricao = new TCombo('tipo_inscricao');
        $tipo_inscricao->addItems(array('F' => 'FÍSICA', 'J' => 'JURÍDICA'));
        $tipo_inscricao->enableSearch();
        
        $inscricao_cnpj = new TEntry('inscricao_cnpj');
        $inscricao_cnpj->setMask('99.999.999/9999-99');
                                        
        $inscricao_cpf = new TEntry('inscricao_cpf');
        $inscricao_cpf->setMask('999.999.999-99');                        


        $tipo_inscricao->setChangeAction(new TAction(array($this, 'EscolheTipoPessoa')));

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio') ], [ $id_condominio ] );
        $this->form->addFields( [ new TLabel('Conta') ], [ $conta ] ,[ new TLabel('Agência') ], [ $agencia ]);
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Titular') ], [ $titular ] ,[ new TLabel('Tipo Conta') ], [ $tipo_conta ]);
        $this->form->addFields( [ new TLabel('Banco') ], [ $id_banco ] , [ new TLabel('Status') ], [ $status ]);
        $this->form->addFields( [ new TLabel('Convênio') ], [ $convenio ] , [ new TLabel('Posto') ], [ $posto ] );
        $this->form->addFields( [ new TLabel('Arq.Remessa') ], [ $arq_remessa ]);
        $this->form->addFields( [ new TLabel('Arq.Retorno') ], [ $arq_retorno ]);

        $this->form->addFields( [ new TLabel('Tipo Inscricao') ], [ $tipo_inscricao ]);
        $this->form->addFields( [ new TLabel('Cnpj') ], [ $inscricao_cnpj ] );
        $this->form->addFields( [ new TLabel('Cpf') ], [ $inscricao_cpf ] );


        // set sizes
        $id->setSize('100%');
        $id_condominio->setSize('100%');
        $conta->setSize('100%');
        $descricao->setSize('100%');
        $agencia->setSize('100%');
        $titular->setSize('100%');
        $tipo_conta->setSize('100%');
        $id_banco->setSize('100%');
        $status->setSize('100%');
        $convenio->setSize('100%');
        $posto->setSize('100%');        
        $tipo_inscricao->setSize('100%');
        $inscricao_cnpj->setSize('100%');
        $inscricao_cpf->setSize('100%');


        if (!empty($id))
        {
            $id->setEditable(FALSE);
            //BootstrapFormBuilder::hideField('form_ContaCorrente', 'id_cliente_erp');
            //BootstrapFormBuilder::hideField('form_ContaCorrente', 'id_empresa');                                      
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus-circle green');
        $this->form->addAction(_t('Back'),new TAction(array('ContaCorrenteList','onReload')),'far:arrow-alt-circle-left red');        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        $status_obj = 'A';
                
        $obj = new StdClass;        
        //$obj->id_cliente_erp = TSession::getValue('cliente_ERP');
        //$obj->id_empresa = TSession::getValue('userempresa');        

        if (!isset($param['id']))
        {
            $obj->status = $status_obj;
        }

        TForm::sendData('form_ContaCorrente', $obj);

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
            $data = $this->form->getData(); // get form data as array
            
            $tipo_inscricao = $data->tipo_inscricao;
            if ($tipo_inscricao == 'F'){
                $validacpf = new TCPFValidator;
                $validacpf->validate('CPF', $data->inscricao_cpf);
                TSession::setValue('pessoa','F');
            }
            if ($tipo_inscricao == 'J'){
                $validacpf = new TCNPJValidator;
                $validacpf->validate('CNPJ', $data->inscricao_cnpj);
                TSession::setValue('pessoa','J');
            }

            $data->inscricao_cnpj = str_replace('.','',$data->inscricao_cnpj);
            $data->inscricao_cnpj = str_replace('-','',$data->inscricao_cnpj);
            $data->inscricao_cnpj = str_replace('/','',$data->inscricao_cnpj);
                   
            $data->inscricao_cpf = str_replace('.','',$data->inscricao_cpf);
            $data->inscricao_cpf = str_replace('-','',$data->inscricao_cpf);

            $pessoa = TSession::getValue('pessoa');
            
            $object = new ContaCorrente;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            
            if ($pessoa == 'J'){
                BootstrapFormBuilder::showField('form_ContaCorrente', 'cnpj');
                BootstrapFormBuilder::hideField('form_ContaCorrente', 'cpf');
            }
            if ($pessoa == 'F'){
                BootstrapFormBuilder::showField('form_ContaCorrente', 'cpf');                
                BootstrapFormBuilder::hideField('form_ContaCorrente', 'cnpj');
            }
            
            TTransaction::close(); // close the transaction
            
            $action = new TAction(['ContaCorrenteList', 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'),$action);

            //$action = new TAction([$this, 'onClear']);
            //new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'),$action);

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
                $object = new ContaCorrente($key); // instantiates the Active Record
                
                $tipo_inscricao = $object->tipo_inscricao;
                if ($tipo_inscricao == 'F')
                {
                    BootstrapFormBuilder::showField('form_ContaCorrente', 'inscricao_cpf');
                    BootstrapFormBuilder::hideField('form_ContaCorrente', 'inscricao_cnpj');
                    //$object->inscricao_cpf = Uteis::formataCPF($object->inscricao_cpf,'','');  
                }
                if($tipo_inscricao == 'J')
                {
                    BootstrapFormBuilder::showField('form_ContaCorrente', 'inscricao_cnpj');
                    BootstrapFormBuilder::hideField('form_ContaCorrente', 'inscricao_cpf');
                    //$object->inscricao_cnpj = Uteis::formataCNPJ($object->inscricao_cnpj,'','');                  
                }
                
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


    public static function EscolheTipoPessoa($param) //validar F ou J
    {             
        try
        {
        $tipo_inscricao = $param['tipo_inscricao'];
        $inscricao_cpf = $param['inscricao_cpf'];
            if ($tipo_inscricao == 'F'){
                BootstrapFormBuilder::showField('form_PessoaCompleto', 'inscricao_cpf');
                BootstrapFormBuilder::hideField('form_PessoaCompleto', 'inscricao_cnpj');
                TSession::setValue('pessoa','F');                
            }
            if($tipo_inscricao == 'J'){
                BootstrapFormBuilder::showField('form_PessoaCompleto', 'inscricao_cnpj');
                BootstrapFormBuilder::hideField('form_PessoaCompleto', 'inscricao_cpf');
                TSession::setValue('pessoa','J');
            }
        }
        catch (Exception $e)
        {
            new TMessage ('error', $e->getMessage());
        }
    }



}
