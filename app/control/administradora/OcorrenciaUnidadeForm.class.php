<?php
/**
 * OcorrenciaUnidadeForm Form
 * @author  <your name here>
 */
class OcorrenciaUnidadeForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
   
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_OcorrenciaUnidade');
        $this->form->setFormTitle('Ocorrência da Unidade');
        

        // create the form fields
        $id = new TEntry('id');
        
        $data_ocorrencia = new TDate('data_ocorrencia');
        $hora_ocorrencia = new TEntry('hora_ocorrencia');
        
        $tipo_id = new TDBCombo('tipo_id', 'facilitasmart', 'TipoOcorrencia', 'id', '{id} - {descricao}','descricao');

        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
            
        
        $descricao = new TText('descricao');
        $data_proximo_contato = new TDate('data_proximo_contato');
        
        $status = new TEntry('status');
        $condominio_id = new THidden('condominio_id');
        $system_user_login = new THidden('system_user_login');
        $datahora_cadastro = new THidden('datahora_cadastro');
        //$datahora_cadastro = new TEntry('datahora_cadastro');
        
        $descricao->addValidation('Texto', new TRequiredValidator());
        $unidade_id->addValidation('Unidade', new TRequiredValidator());

        $id->setEditable(false);
        $id->setSize(100);
        $hora_ocorrencia->setSize(100);
        $data_ocorrencia->setValue(date('d/m/Y'));
        $data_ocorrencia->setDatabaseMask('yyyy-mm-dd');
        $data_proximo_contato->setValue(date('d/m/Y'));
        $data_proximo_contato->setDatabaseMask('yyyy-mm-dd');
        $data_ocorrencia->setMask('dd/mm/yyyy');
        $data_ocorrencia->setSize(190);
        $data_proximo_contato->setMask('dd/mm/yyyy');
        $data_proximo_contato->setSize(190);
        //$descricao->setSize('90%', 68);
        $descricao->setSize('90%', 200);
        $tipo_id->setSize('90%');
        $unidade_id->setSize('90%');
        ///$hora_ocorrencia->setMask('hh:ii');
        
        //$datahora_cadastro->setValue(date('d/m/Y h:i'));
        //$datahora_cadastro->setMask('dd/mm/yyyy hh:ii');
        //$datahora_cadastro->setDatabaseMask('yyyy-mm-dd hh:ii');
        //$datahora_cadastro->setSize(150);
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Data Ocorrência') ], [ $data_ocorrencia ] );
        $this->form->addFields( [ new TLabel('Hora Ocorrência') ], [ $hora_ocorrencia ] );
        $this->form->addFields( [ new TLabel('Tipo') ], [ $tipo_id ] );
        $this->form->addFields( [ new TLabel('Unidade', '#ff0000') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Descrição', '#ff0000') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Data Próximo Contato') ], [ $data_proximo_contato ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
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
            
            $object = new OcorrenciaUnidade;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            $object->system_user_login = TSession::getValue('username');
            $object->condominio_id = TSession::getValue('id_condominio');
            
            if (empty( $object->data_proximo_contato )) {
                $object->status = '1'; // concluído
            }
            
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
                $object = new OcorrenciaUnidade($key); // instantiates the Active Record
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
