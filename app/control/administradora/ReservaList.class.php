<?php
/**
 * ReservaList Listing
 * @author  <your name here>
 */
class ReservaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_Reserva');
        $this->form->setFormTitle('Reserva');
        

        // create the form fields
        $id = new TEntry('id');
        $id_area_comum = new TDBUniqueSearch('id_area_comum', 'facilitasmart', 'AreaComum', 'id', 'descricao');
        $id_unidade = new TDBUniqueSearch('id_unidade', 'facilitasmart', 'Unidade', 'id', 'bloco_quadra');
        $data_reserva = new TEntry('data_reserva');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Id Area Comum') ], [ $id_area_comum ] );
        $this->form->addFields( [ new TLabel('Id Unidade') ], [ $id_unidade ] );
        $this->form->addFields( [ new TLabel('Data') ], [ $data_reserva ] );


        // set sizes
        $id->setSize('100%');
        $id_area_comum->setSize('100%');
        $id_unidade->setSize('100%');
        $data_reserva->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ReservaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_area_comum = new TDataGridColumn('id_area_comum', 'Id Area Comum', 'right');
        $column_id_unidade = new TDataGridColumn('id_unidade', 'Id Unidade', 'right');
        $column_confirmacao_cadastro = new TDataGridColumn('confirmacao_cadastro', 'Confirmacao Cadastro', 'left');
        $column_data = new TDataGridColumn('data', 'Data', 'left');
        $column_hora_inicio = new TDataGridColumn('hora_inicio', 'Hora Inicio', 'left');
        $column_hora_fim = new TDataGridColumn('hora_fim', 'Hora Fim', 'left');
        $column_valor_reserva = new TDataGridColumn('valor_reserva', 'Valor Reserva', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_area_comum);
        $this->datagrid->addColumn($column_id_unidade);
        $this->datagrid->addColumn($column_confirmacao_cadastro);
        $this->datagrid->addColumn($column_data);
        $this->datagrid->addColumn($column_hora_inicio);
        $this->datagrid->addColumn($column_hora_fim);
        $this->datagrid->addColumn($column_valor_reserva);
        $this->datagrid->addColumn($column_observacao);


        $action1 = new TDataGridAction(['ReservaForm', 'onEdit'], ['id'=>'{id}']);
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
            $object = new Reserva($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_id_area_comum',   NULL);
        TSession::setValue(__CLASS__.'_filter_id_unidade',   NULL);
        TSession::setValue(__CLASS__.'_filter_data',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->id_area_comum) AND ($data->id_area_comum)) {
            $filter = new TFilter('id_area_comum', '=', $data->id_area_comum); // create the filter
            TSession::setValue(__CLASS__.'_filter_id_area_comum',   $filter); // stores the filter in the session
        }


        if (isset($data->id_unidade) AND ($data->id_unidade)) {
            $filter = new TFilter('id_unidade', '=', $data->id_unidade); // create the filter
            TSession::setValue(__CLASS__.'_filter_id_unidade',   $filter); // stores the filter in the session
        }


        if (isset($data->data) AND ($data->data)) {
            $filter = new TFilter('data_reserva', 'like', "%{$data->data_reserva}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_data',   $filter); // stores the filter in the session
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
            
            // creates a repository for Reserva
            $repository = new TRepository('Reserva');
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


            if (TSession::getValue(__CLASS__.'_filter_id_area_comum')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id_area_comum')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_id_unidade')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id_unidade')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data')); // add the session filter
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
            $object = new Reserva($key, FALSE); // instantiates the Active Record
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
