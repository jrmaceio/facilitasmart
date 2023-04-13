<?php
/**
 * RondaList Listing
 * @author  <your name here>
 */
class DistanciaRondaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_Ronda');
        $this->form->setFormTitle('Ronda');
        

        // create the form fields
        $id = new TEntry('id');
        $unidade_id = new TEntry('unidade_id');
        $tipo_id = new TEntry('tipo_id');
        $hora_ronda = new TEntry('hora_ronda');
        $data_ronda = new TEntry('data_ronda');
        $descricao = new TEntry('descricao');
        $status_tratamento = new TEntry('status_tratamento');
        $patrulheiro_id = new TDBUniqueSearch('patrulheiro_id', 'ronda', 'Patrulheiro', 'id', 'nome');
        $ponto_ronda_id = new TDBUniqueSearch('ponto_ronda_id', 'ronda', 'PontoRonda', 'id', 'descricao');
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao');
        $latitude = new TEntry('latitude');
        $longitude = new TEntry('longitude');
        $data_hora_atualizacao = new TEntry('data_hora_atualizacao');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Tipo Id') ], [ $tipo_id ] );
        $this->form->addFields( [ new TLabel('Hora Ronda') ], [ $hora_ronda ] );
        $this->form->addFields( [ new TLabel('Data Ronda') ], [ $data_ronda ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Status Tratamento') ], [ $status_tratamento ] );
        $this->form->addFields( [ new TLabel('Patrulheiro Id') ], [ $patrulheiro_id ] );
        $this->form->addFields( [ new TLabel('Ponto Ronda Id') ], [ $ponto_ronda_id ] );
        $this->form->addFields( [ new TLabel('Posto Id') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Latitude') ], [ $latitude ] );
        $this->form->addFields( [ new TLabel('Longitude') ], [ $longitude ] );
        $this->form->addFields( [ new TLabel('Data Hora Atualizacao') ], [ $data_hora_atualizacao ] );


        // set sizes
        $id->setSize('100%');
        $unidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $hora_ronda->setSize('100%');
        $data_ronda->setSize('100%');
        $descricao->setSize('100%');
        $status_tratamento->setSize('100%');
        $patrulheiro_id->setSize('100%');
        $ponto_ronda_id->setSize('100%');
        $posto_id->setSize('100%');
        $latitude->setSize('100%');
        $longitude->setSize('100%');
        $data_hora_atualizacao->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Distancia', new TAction([$this, 'onDistancia']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_unidade_id = new TDataGridColumn('unit->name', 'Unidade', 'right');
        $column_tipo_id = new TDataGridColumn('tipo_id', 'Tipo', 'right');
        $column_hora_ronda = new TDataGridColumn('hora_ronda', 'Hora', 'left');
        $column_data_ronda = new TDataGridColumn('data_ronda', 'Data', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_status_tratamento = new TDataGridColumn('status_tratamento', 'Status', 'left');
        $column_patrulheiro_id = new TDataGridColumn('patrulheiro->nome', 'Patrulheiro', 'right');
        $column_ponto_ronda_id = new TDataGridColumn('ponto_ronda->descricao', 'Ponto Ronda', 'right');
        $column_posto_id = new TDataGridColumn('posto->descricao', 'Posto', 'right');
        $column_latitude = new TDataGridColumn('latitude', 'Latitude', 'left');
        $column_longitude = new TDataGridColumn('longitude', 'Longitude', 'left');
        $column_data_hora_atualizacao = new TDataGridColumn('data_hora_atualizacao', 'Atualizacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_tipo_id);
        $this->datagrid->addColumn($column_hora_ronda);
        $this->datagrid->addColumn($column_data_ronda);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_status_tratamento);
        $this->datagrid->addColumn($column_patrulheiro_id);
        $this->datagrid->addColumn($column_ponto_ronda_id);
        $this->datagrid->addColumn($column_posto_id);
        $this->datagrid->addColumn($column_latitude);
        $this->datagrid->addColumn($column_longitude);
        $this->datagrid->addColumn($column_data_hora_atualizacao);


        //$action1 = new TDataGridAction(['RondaForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
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
    
    public function onDistancia($param)
    {
        try
        {
            // open a transaction with database 'ronda'
            TTransaction::open('ronda');
            
            // creates a repository for Ronda
            $repository = new TRepository('Ronda');
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


            if (TSession::getValue(__CLASS__.'_filter_unidade_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_tipo_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_tipo_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_hora_ronda')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_hora_ronda')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_ronda')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_ronda')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_descricao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_descricao')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_status_tratamento')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_status_tratamento')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_patrulheiro_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_patrulheiro_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_ponto_ronda_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ponto_ronda_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_posto_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_posto_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_latitude')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_latitude')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_longitude')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_longitude')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_hora_atualizacao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_hora_atualizacao')); // add the session filter
            }

          
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }   
           
            $lat1 = '';
            $lon1 = '';
            $lat2 = '';
            $lon2 = '';
            
            $alteracao = false;
            
            //$this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    if ($lat1 == '') {
                        $lat1 = $object->latitude;
                        $lon1 = $object->longitude;
                        
                    }else {
                        $alteracao = true;
                    }
                    
                    if ($lat2 == '' and $alteracao) {
                        $lat2 = $object->latitude;
                        $lon2 = $object->longitude;
                    }
                    
                }
            }
          
            // close the transaction
            TTransaction::close();
          
            //$lat1 = '-9.615708';
            //$lon1 = '-35.7348381';
            //$lat2 = '-9.6157158';
            //$lon2 = '-35.7348411';
            
            $lat1 = deg2rad($lat1);
            $lat2 = deg2rad($lat2);
            $lon1 = deg2rad($lon1);
            $lon2 = deg2rad($lon2);

            $latD = $lat2 - $lat1;
            $lonD = $lon2 - $lon1;

            $dist = 2 * asin(sqrt(pow(sin($latD / 2), 2) +
                    cos($lat1) * cos($lat2) * pow(sin($lonD / 2), 2)));
            $dist = $dist * 6371;
            //return number_format($dist, 2, '.', '');

            //new TMessage('Distância', $dist. " Km<br />");
            new TMessage('info', $dist * 1000 . " m<br />");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
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
            
            TTransaction::open('ronda'); // open a transaction with database
            $object = new Ronda($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_unidade_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_tipo_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_hora_ronda',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_ronda',   NULL);
        TSession::setValue(__CLASS__.'_filter_descricao',   NULL);
        TSession::setValue(__CLASS__.'_filter_status_tratamento',   NULL);
        TSession::setValue(__CLASS__.'_filter_patrulheiro_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_ponto_ronda_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_posto_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_latitude',   NULL);
        TSession::setValue(__CLASS__.'_filter_longitude',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_hora_atualizacao',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', 'like', "%{$data->unidade_id}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_id) AND ($data->tipo_id)) {
            $filter = new TFilter('tipo_id', 'like', "%{$data->tipo_id}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_tipo_id',   $filter); // stores the filter in the session
        }


        if (isset($data->hora_ronda) AND ($data->hora_ronda)) {
            $filter = new TFilter('hora_ronda', 'like', "%{$data->hora_ronda}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_hora_ronda',   $filter); // stores the filter in the session
        }


        if (isset($data->data_ronda) AND ($data->data_ronda)) {
            $filter = new TFilter('data_ronda', 'like', "%{$data->data_ronda}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_data_ronda',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->status_tratamento) AND ($data->status_tratamento)) {
            $filter = new TFilter('status_tratamento', 'like', "%{$data->status_tratamento}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_status_tratamento',   $filter); // stores the filter in the session
        }


        if (isset($data->patrulheiro_id) AND ($data->patrulheiro_id)) {
            $filter = new TFilter('patrulheiro_id', '=', $data->patrulheiro_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_patrulheiro_id',   $filter); // stores the filter in the session
        }


        if (isset($data->ponto_ronda_id) AND ($data->ponto_ronda_id)) {
            $filter = new TFilter('ponto_ronda_id', '=', $data->ponto_ronda_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_ponto_ronda_id',   $filter); // stores the filter in the session
        }


        if (isset($data->posto_id) AND ($data->posto_id)) {
            $filter = new TFilter('posto_id', '=', $data->posto_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_posto_id',   $filter); // stores the filter in the session
        }


        if (isset($data->latitude) AND ($data->latitude)) {
            $filter = new TFilter('latitude', 'like', "%{$data->latitude}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_latitude',   $filter); // stores the filter in the session
        }


        if (isset($data->longitude) AND ($data->longitude)) {
            $filter = new TFilter('longitude', 'like', "%{$data->longitude}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_longitude',   $filter); // stores the filter in the session
        }


        if (isset($data->data_hora_atualizacao) AND ($data->data_hora_atualizacao)) {
            $filter = new TFilter('data_hora_atualizacao', 'like', "%{$data->data_hora_atualizacao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_data_hora_atualizacao',   $filter); // stores the filter in the session
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
            // open a transaction with database 'ronda'
            TTransaction::open('ronda');
            
            // creates a repository for Ronda
            $repository = new TRepository('Ronda');
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
            
            $criteria->add(new TFilter('unidade_id', 'IN', TSession::getValue('userunitids')));
        
       
            if (TSession::getValue(__CLASS__.'_filter_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_unidade_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_tipo_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_tipo_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_hora_ronda')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_hora_ronda')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_ronda')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_ronda')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_descricao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_descricao')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_status_tratamento')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_status_tratamento')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_patrulheiro_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_patrulheiro_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_ponto_ronda_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ponto_ronda_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_posto_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_posto_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_latitude')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_latitude')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_longitude')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_longitude')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_hora_atualizacao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_hora_atualizacao')); // add the session filter
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
            TTransaction::open('ronda'); // open a transaction with database
            $object = new Ronda($key, FALSE); // instantiates the Active Record
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
