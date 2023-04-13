<?php
/**
 * BancoForm Form
 * @author  <your name here>
 */
class BancoForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Banco');
        $this->form->setFormTitle('Banco');
        
        // create the form fields
        $id = new TEntry('id');

        $codigo_bacen = new TEntry('codigo_bacen');
        $codigo_bacen->forceUpperCase();                
        $sigla = new TEntry('sigla');
        $sigla->forceUpperCase();                
        $descricao = new TEntry('descricao');
        $descricao->forceUpperCase();                
        //status
        $status = new TCombo('status');
        $status->addItems(array('A' => 'Ativo', 'I' => 'Inativo'));


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Código Bacen') ], [ $codigo_bacen ] );
        $this->form->addFields( [ new TLabel('Sigla') ], [ $sigla ] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );



        // set sizes
        $id->setSize('20%');
        $codigo_bacen->setSize('100%');
        $sigla->setSize('100%');
        $descricao->setSize('100%');
        $status->setSize('70%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus-circle green');
        $this->form->addAction(_t('Back'),new TAction(array('BancoList','onReload')),'far:arrow-alt-circle-left red');         
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        $status_obj = 'A';
                
        $obj = new StdClass;        

        if (!isset($param['id']))
        {
            $obj->status = $status_obj;
        }
        
        TForm::sendData('form_Banco', $obj);
                
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
            
            $object = new Banco;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $action = new TAction(['BancoList', 'onReload']);
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
                $object = new Banco($key); // instantiates the Active Record
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
