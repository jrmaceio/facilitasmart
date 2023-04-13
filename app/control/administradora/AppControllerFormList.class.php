<?php
/**
 * AppControllerFormList Form List
 * @author  <your name here>
 */
class AppControllerFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
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
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade Id', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'left');
        $column_modelo_telefone = new TDataGridColumn('modelo_telefone', 'Modelo Telefone', 'left');
        $column_codigo_validacao = new TDataGridColumn('codigo_validacao', 'Codigo Validacao', 'left');
        $column_status_liberacao = new TDataGridColumn('status_liberacao', 'Status Liberacao', 'left');
        $column_unidade_informada = new TDataGridColumn('unidade_informada', 'Unidade Informada', 'left');
        $column_telefone_informado = new TDataGridColumn('telefone_informado', 'Telefone Informado', 'left');
        $column_condominio_informado = new TDataGridColumn('condominio_informado', 'Condomínio Informado', 'left');
        $column_registro_atualizacao = new TDataGridColumn('registro_atualizacao', 'Registro Atualizacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_modelo_telefone);
        $this->datagrid->addColumn($column_codigo_validacao);
        $this->datagrid->addColumn($column_status_liberacao);
        $this->datagrid->addColumn($column_unidade_informada);
        $this->datagrid->addColumn($column_telefone_informado);
        $this->datagrid->addColumn($column_condominio_informado);
        $this->datagrid->addColumn($column_registro_atualizacao);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction([$this, 'onEdit']);
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('far:edit blue');
        $action1->setField('id');
        
        $action2 = new TDataGridAction([$this, 'onDelete']);
        //$action2->setUseButton(TRUE);
        //$action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
        $action2->setImage('far:trash-alt red');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for AppController
            $repository = new TRepository('AppController');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new AppController($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
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
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved')); // success message
            $this->onReload(); // reload the listing
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
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
