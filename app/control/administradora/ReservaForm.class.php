<?php
/**
 * ReservaForm Form
 * @author  <your name here>
 */
class ReservaForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Reserva');
        $this->form->setFormTitle('Reserva');
        

        // create the form fields
        $id = new TEntry('id');
        $id_area_comum = new TDBUniqueSearch('id_area_comum', 'facilitasmart', 'AreaComum', 'id', 'descricao');
        $id_unidade = new TDBUniqueSearch('id_unidade', 'facilitasmart', 'Unidade', 'id', 'bloco_quadra');
        $confirmacao_cadastro = new TEntry('confirmacao_cadastro');
        $data_reserva = new TDate('data_reserva');
        $hora_inicio = new TEntry('hora_inicio');
        $hora_fim = new TEntry('hora_fim');
        $valor_reserva = new TEntry('valor_reserva');
        $observacao = new TText('observação');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Area Comum') ], [ $id_area_comum ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $id_unidade ] );
        $this->form->addFields( [ new TLabel('Confirmacao CPF/CNPJ') ], [ $confirmacao_cadastro ] );
        $this->form->addFields( [ new TLabel('Data Reserva') ], [ $data_reserva ] );
        $this->form->addFields( [ new TLabel('Hora Inicio') ], [ $hora_inicio ] );
        $this->form->addFields( [ new TLabel('Hora Fim') ], [ $hora_fim ] );
        $this->form->addFields( [ new TLabel('Valor Reserva') ], [ $valor_reserva ] );
        $this->form->addFields( [ new TLabel('Observação') ], [ $observacao ] );



        // set sizes
        $id->setSize('100%');
        $id_area_comum->setSize('100%');
        $id_unidade->setSize('100%');
        $confirmacao_cadastro->setSize('100%');
        $data_reserva->setSize('100%');
        $hora_inicio->setSize('100%');
        $hora_fim->setSize('100%');
        $valor_reserva->setSize('100%');
        $observacao->setSize('100%');



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
            
            $object = new Reserva;  // create an empty object
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
                $object = new Reserva($key); // instantiates the Active Record
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
