<?php
/**
 * AcordoForm Form
 * @author  <your name here>
 */
class AcordoForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Acordo');
        $this->form->setFormTitle('Acordo');
        

        // create the form fields
        $id = new TEntry('id');
        $data_base_acordo = new TDate('data_base_acordo');
        $parcelas = new TEntry('parcelas');
        $observacao = new TText('observacao');
        $valor_lancado = new TEntry('valor_lancado');
        $valor_projetado = new TEntry('valor_projetado');
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $correcao = new TEntry('correcao');
        $acrescimo = new TEntry('acrescimo');
        $desconto = new TEntry('desconto');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Data Base Acordo') ], [ $data_base_acordo ] );
        $this->form->addFields( [ new TLabel('Parcelas') ], [ $parcelas ] );
        $this->form->addFields( [ new TLabel('Observacao') ], [ $observacao ] );
        $this->form->addFields( [ new TLabel('Valor Lancado') ], [ $valor_lancado ] );
        $this->form->addFields( [ new TLabel('Valor Projetado') ], [ $valor_projetado ] );
        $this->form->addFields( [ new TLabel('Multa') ], [ $multa ] );
        $this->form->addFields( [ new TLabel('Juros') ], [ $juros ] );
        $this->form->addFields( [ new TLabel('Correcao') ], [ $correcao ] );
        $this->form->addFields( [ new TLabel('Acrescimo') ], [ $acrescimo ] );
        $this->form->addFields( [ new TLabel('Desconto') ], [ $desconto ] );



        // set sizes
        $id->setSize('100%');
        $data_base_acordo->setSize('100%');
        $parcelas->setSize('100%');
        $observacao->setSize('100%');
        $valor_lancado->setSize('100%');
        $valor_projetado->setSize('100%');
        $multa->setSize('100%');
        $juros->setSize('100%');
        $correcao->setSize('100%');
        $acrescimo->setSize('100%');
        $desconto->setSize('100%');



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
            
            $object = new Acordo;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
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
                $object = new Acordo($key); // instantiates the Active Record
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
