<?php
/**
 * DiarioPortariaForm Form
 * @author  <your name here>
 */
class DiarioPortariaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_DiarioPortaria');
        $this->form->setFormTitle('Registro do Diario de Portaria');
        

        // create the form fields
        $id = new TEntry('id');
        $data_dia = new TEntry('data_dia');
        $colaborador = new TEntry('colaborador');
        $data_plantao = new TDate('data_plantao');
        $resumo = new TEntry('resumo');
        $descricao = new TText('descricao');
        $status = new TEntry('status');
        $condominio_id = new THidden('condominio_id');
        $data_tratativa = new TDate('data_tratativa');
        $tratativa = new TText('tratativa');
        $system_user_login =  new THidden('system_user_login');
        $system_user_email = new THidden('system_user_email');
        //$atualizacao = new TEntry('atualizacao');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Data Dia') ], [ $data_dia ] );
        $this->form->addFields( [ new TLabel('Colaborador') ], [ $colaborador ] );
        $this->form->addFields( [ new TLabel('Data Plantão') ], [ $data_plantao ] );
        $this->form->addFields( [ new TLabel('Resumo') ], [ $resumo ] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        //$this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('') ], [ $condominio_id ],
        [ new TLabel('') ], [ $system_user_login ],
        [ new TLabel('') ], [ $system_user_email ]
         );
        //$this->form->addFields( [ new TLabel('Data Tratativa') ], [ $data_tratativa ] );
        //$this->form->addFields( [ new TLabel('Tratativa') ], [ $tratativa ] );
        //$this->form->addFields( [ new TLabel('System User Login') ], [ $system_user_login ] );
        //$this->form->addFields( [ new TLabel('System User Email') ], [ $system_user_email ] );
        //$this->form->addFields( [ new TLabel('Atualizacao') ], [ $atualizacao ] );



        // set sizes
        $id->setSize('100%');
        $data_dia->setSize('100%');
        $colaborador->setSize('100%');
        $data_plantao->setSize('100%');
        $resumo->setSize('100%');
        $descricao->setSize('100%');
        $status->setSize('100%');
        $condominio_id->setSize('100%');
        $data_tratativa->setSize('100%');
        $tratativa->setSize('100%');
        $system_user_login->setSize('100%');
        $system_user_email->setSize('100%');
        //$atualizacao->setSize('100%');

        // preenchidos automaticamente
        $data_dia->setValue(date('d/m/y H:i:s'));
        $system_user_login->setValue(TSession::getValue('login')); 
        $system_user_email->setValue(TSession::getValue('login')); 
        
        $data_plantao->setMask('dd/mm/yyyy');
        $data_plantao->setDatabaseMask('yyyy-mm-dd');
        
        $system_user_login->setValue(TSession::getValue('login')); 
        
        $system_user_email->setValue(TSession::getValue('username')); 
        

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
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
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
            
            // verifica o nivel de acesso do usuario
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            foreach ($users as $user)
            {
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
            }

                       
            $object = new DiarioPortaria;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            $object->condominio_id = TSession::getValue('id_condominio');
            
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
                $object = new DiarioPortaria($key); // instantiates the Active Record
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
