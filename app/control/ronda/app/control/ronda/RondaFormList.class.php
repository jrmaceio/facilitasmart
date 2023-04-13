<?php
/**
 * RondaFormList Form List
 * @author  <your name here>
 */
class RondaFormList extends TPage
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
        
        
        $this->form = new BootstrapFormBuilder('form_Ronda');
        $this->form->setFormTitle('Ronda');
        

        // create the form fields
        $id = new TEntry('id');
        $unidade_id = new TEntry('unidade_id');
        $tipo_id = new TEntry('tipo_id');
        $hora_ronda = new TEntry('hora_ronda');
        $data_ronda = new TDate('data_ronda');
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
        $this->form->addFields( [ new TLabel('Patrulheiro') ], [ $patrulheiro_id ] );
        $this->form->addFields( [ new TLabel('Ponto Ronda') ], [ $ponto_ronda_id ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
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
        $column_unidade_id = new TDataGridColumn('unit->name', 'Unidade', 'left');
        $column_tipo_id = new TDataGridColumn('tipo_id', 'Tipo', 'left');
        $column_hora_ronda = new TDataGridColumn('hora_ronda', 'Hora', 'left');
        $column_data_ronda = new TDataGridColumn('data_ronda', 'Data', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_status_tratamento = new TDataGridColumn('status_tratamento', 'Status', 'left');
        $column_patrulheiro_id = new TDataGridColumn('patrulheiro->nome', 'Patrulheiro', 'left');
        $column_ponto_ronda_id = new TDataGridColumn('ponto_ronda->descricao', 'Ponto', 'left');
        $column_posto_id = new TDataGridColumn('posto->descricao', 'Posto', 'left');
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

        $column_tipo_id->setTransformer( function($value, $object, $row) {
            if ( $value == '8' ) {
              $label = 'Ronda';
              $div = new TElement("span");
              $div->class = "small-box-footer";
              $div->add("<i class=\"fa fa-star-o red\"></i>");
              $div->add($label);
              return $div;                

            }
            else {
                   $label = 'Indefinido';
                   $div = new TElement("span");
                   $div->class = "small-box-footer";
                   $div->add("<i class=\"fa fa-star green\"></i>");
                   $div->add($label);
                   return $div;
                 }
            
        });
        
        $column_status_tratamento->setTransformer( function($value, $object, $row) {
            if ( $value == '0' ) {
              $label = 'Não Tratada';
              $div = new TElement("span");
              $div->class = "small-box-footer";
              $div->add("<i class=\"fa fa-star-o red\"></i>");
              $div->add($label);
              return $div;                

            }
            else {
                   $label = 'Tratada';
                   $div = new TElement("span");
                   $div->class = "small-box-footer";
                   $div->add("<i class=\"fa fa-star green\"></i>");
                   $div->add($label);
                   return $div;
                 }
            
        });
        
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
            
            $filter = new TFilter('unidade_id', '=', TSession::getValue('userunitid'));
            $criteria->add($filter); // add the session filter
            
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
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('ronda'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Ronda;  // create an empty object
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
                TTransaction::open('ronda'); // open a transaction
                $object = new Ronda($key); // instantiates the Active Record
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
