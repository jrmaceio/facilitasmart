<?php
/**
 * AppControllerList Listing
 * @author  <your name here>
 */
class AppControllerList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_AppController');
        $this->form->setFormTitle('AppController');
        

        // create the form fields
        $id = new TEntry('id');
        $email = new TEntry('email');
        $unidade_id = new TDBUniqueSearch('unidade_id', 'facilitasmart', 'Unidade', 'id', 'bloco_quadra');
        $condominio_id = new TDBUniqueSearch('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo');
        $nome = new TEntry('nome');
        $modelo_telefone = new TEntry('modelo_telefone');
        $status_liberacao = new TEntry('status_liberacao');
        $unidade_informada = new TEntry('unidade_informada');
        $telefone_informado = new TEntry('telefone_informado');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Modelo Telefone') ], [ $modelo_telefone ] );
        $this->form->addFields( [ new TLabel('Status Liberacao') ], [ $status_liberacao ] );
        $this->form->addFields( [ new TLabel('Unidade Informada') ], [ $unidade_informada ] );
        $this->form->addFields( [ new TLabel('Telefone Informado') ], [ $telefone_informado ] );


        // set sizes
        $id->setSize('100%');
        $email->setSize('100%');
        $unidade_id->setSize('100%');
        $condominio_id->setSize('100%');
        $nome->setSize('100%');
        $modelo_telefone->setSize('100%');
        $status_liberacao->setSize('100%');
        $unidade_informada->setSize('100%');
        $telefone_informado->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['AppControllerForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_modelo_telefone = new TDataGridColumn('modelo_telefone', 'Modelo Telefone', 'left');
        $column_codigo_validacao = new TDataGridColumn('codigo_validacao', 'Codigo Validacao', 'right');
        $column_status_liberacao = new TDataGridColumn('status_liberacao', 'Status Liberacao', 'left');
        $column_unidade_informada = new TDataGridColumn('unidade_informada', 'Unidade Informada', 'left');
        $column_telefone_informado = new TDataGridColumn('telefone_informado', 'Telefone Informado', 'left');
        $column_condominio_informado = new TDataGridColumn('condominio_informado', 'Telefone Informado', 'left');
        
        $column_registro_atualizacao = new TDataGridColumn('registro_atualizacao', 'Registro Atualizacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_modelo_telefone);
        $this->datagrid->addColumn($column_codigo_validacao);
        $this->datagrid->addColumn($column_status_liberacao);
        $this->datagrid->addColumn($column_unidade_informada);
        $this->datagrid->addColumn($column_telefone_informado);
        $this->datagrid->addColumn($column_condominio_informado);
        $this->datagrid->addColumn($column_registro_atualizacao);


        $action1 = new TDataGridAction(['AppControllerForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
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
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new AppController($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_email',   NULL);
        TSession::setValue(__CLASS__.'_filter_unidade_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_condominio_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_modelo_telefone',   NULL);
        TSession::setValue(__CLASS__.'_filter_status_liberacao',   NULL);
        TSession::setValue(__CLASS__.'_filter_unidade_informada',   NULL);
        TSession::setValue(__CLASS__.'_filter_telefone_informado',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->email) AND ($data->email)) {
            $filter = new TFilter('email', 'like', "%{$data->email}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_email',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', $data->unidade_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', '=', $data->condominio_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->modelo_telefone) AND ($data->modelo_telefone)) {
            $filter = new TFilter('modelo_telefone', 'like', "%{$data->modelo_telefone}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_modelo_telefone',   $filter); // stores the filter in the session
        }


        if (isset($data->status_liberacao) AND ($data->status_liberacao)) {
            $filter = new TFilter('status_liberacao', 'like', "%{$data->status_liberacao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_status_liberacao',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_informada) AND ($data->unidade_informada)) {
            $filter = new TFilter('unidade_informada', 'like', "%{$data->unidade_informada}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_unidade_informada',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone_informado) AND ($data->telefone_informado)) {
            $filter = new TFilter('telefone_informado', 'like', "%{$data->telefone_informado}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_telefone_informado',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
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
            

            if (TSession::getValue(__CLASS__.'_filter_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_email')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_email')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_unidade_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_condominio_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_modelo_telefone')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_modelo_telefone')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_status_liberacao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_status_liberacao')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_unidade_informada')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_informada')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_telefone_informado')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_telefone_informado')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
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
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
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
            $key=$param['key']; // get the parameter $key
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
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
