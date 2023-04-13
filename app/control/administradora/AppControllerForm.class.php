<?php
/**
 * AppControllerForm Form
 * @author  <your name here>
 */
class AppControllerForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_AppController');
        $this->form->setFormTitle('AppController');
        

        // create the form fields
        $id = new TEntry('id');
        $email = new TEntry('email');
        $unidade_id = new TEntry('unidade_id');//new TDBUniqueSearch('unidade_id', 'facilitasmart', 'Unidade', 'id', 'bloco_quadra');
        $condominio_id = new TEntry('condominio_id');//new TDBUniqueSearch('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo');
        $nome = new TEntry('nome');
        $modelo_telefone = new TEntry('modelo_telefone');
        $codigo_validacao = new TEntry('codigo_validacao');
        $status_liberacao = new TEntry('status_liberacao');
        $tipo = new TEntry('tipo');
        $facilitasmart_user_id = new TEntry('facilitasmart_user_id');
        $ronda_user_id = new TEntry('ronda_user_id');
        //$unidade_informada = new TEntry('unidade_informada');
        //$telefone_informado = new TEntry('telefone_informado');
        //$registro_atualizacao = new TEntry('registro_atualizacao');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Modelo Telefone') ], [ $modelo_telefone ] );
        $this->form->addFields( [ new TLabel('Codigo Validacao') ], [ $codigo_validacao ] );
        $this->form->addFields( [ new TLabel('Status Liberacao') ], [ $status_liberacao ] );
        
        $this->form->addFields( [ new TLabel('Tipo (1-morador, 2-administradora, 3-supervidor de ronda') ], [ $tipo ] );
        $this->form->addFields( [ new TLabel('FacilitaSmart Id') ], [ $facilitasmart_user_id ] );
        $this->form->addFields( [ new TLabel('Ronda Id') ], [ $ronda_user_id ] );
        
        //$this->form->addFields( [ new TLabel('Unidade Informada') ], [ $unidade_informada ] );
        //$this->form->addFields( [ new TLabel('Telefone Informado') ], [ $telefone_informado ] );
        //$this->form->addFields( [ new TLabel('Registro Atualizacao') ], [ $registro_atualizacao ] );



        // set sizes
        $id->setSize('100%');
        $email->setSize('100%');
        $unidade_id->setSize('100%');
        $condominio_id->setSize('100%');
        $nome->setSize('100%');
        $modelo_telefone->setSize('100%');
        $codigo_validacao->setSize('100%');
        $status_liberacao->setSize('100%');
        
        $tipo->setSize('100%');
        $facilitasmart_user_id->setSize('100%');
        $ronda_user_id->setSize('100%');
        
        //$unidade_informada->setSize('100%');
        //$telefone_informado->setSize('100%');
        //$registro_atualizacao->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
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
            
            $object = new AppController;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
                    
            $object->store(); // save the object
            
            // avisa por email a liberação do app
            if ($object->status_liberacao == 'Y') {
                // inicio email
                TTransaction::open('permission'); 
                $preferences = SystemPreference::getAllPreferences();
                $mail = new TMail;
                $mail->setDebug(false);
                $mail->SMTPSecure = "ssl";
                $mail->setFrom( trim($preferences['mail_from']), 'FacilitaSmart' );
                $mail->addAddress( trim($object->email), 'Liberação' );
                $mail->setSubject( 'FacilitaSmart - Liberação de acesso ao app' );
                if ($preferences['smtp_auth'])
                {
                    $mail->SetUseSmtp();
                    $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
                    $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
                }
                $body = 'Aplicativo libero para acesso. E-mail: ' . $object->email;
                $mail->setTextBody($body);    
                sleep(3);            
                $mail->send();
                TTransaction::close();
                // fim teste email
        
            }
            
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
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
                $object = new AppController($key); // instantiates the Active Record
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
