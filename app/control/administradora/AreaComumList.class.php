<?php
/**
 * AreaComumList Listing
 * @author  <your name here>
 */
class AreaComumList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_AreaComum');
        $this->form->setFormTitle('AreaComum');
        

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );


        // set sizes
        $id->setSize('100%');
        $descricao->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['AreaComumForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_valor_taxa_locacao = new TDataGridColumn('valor_taxa_locacao', 'Valor Taxa Locacao', 'left');
        $column_tem_taxa = new TDataGridColumn('tem_taxa', 'Tem Taxa', 'left');
        $column_intervalo_locacao = new TDataGridColumn('intervalo_locacao', 'Intervalo Locacao', 'left');
        $column_dias_antecedencia_locacao = new TDataGridColumn('dias_antecedencia_locacao', 'Dias Antecedencia Locacao', 'left');
        $column_hora_inicio = new TDataGridColumn('hora_inicio', 'Hora Inicio', 'left');
        $column_hora_fim = new TDataGridColumn('hora_fim', 'Hora Fim', 'left');
        $column_obrigatorio_lista_presenca = new TDataGridColumn('obrigatorio_lista_presenca', 'Obrigatorio Lista Presenca', 'left');
        $column_capacidade = new TDataGridColumn('capacidade', 'Capacidade', 'right');
        $column_intervalo_entre_locacacoes = new TDataGridColumn('intervalo_entre_locacacoes', 'Intervalo Entre Locacacoes', 'right');
        $column_disponibilidade_segunda = new TDataGridColumn('disponibilidade_segunda', 'Disponibilidade Segunda', 'left');
        $column_disponibilidade_terca = new TDataGridColumn('disponibilidade_terca', 'Disponibilidade Terca', 'left');
        $column_disponibilidade_quarta = new TDataGridColumn('disponibilidade_quarta', 'Disponibilidade Quarta', 'left');
        $column_disponibilidade_quinta = new TDataGridColumn('disponibilidade_quinta', 'Disponibilidade Quinta', 'left');
        $column_disponibilidade_sexta = new TDataGridColumn('disponibilidade_sexta', 'Disponibilidade Sexta', 'left');
        $column_disponibilidade_sabado = new TDataGridColumn('disponibilidade_sabado', 'Disponibilidade Sabado', 'left');
        $column_disponibilidade_domingo = new TDataGridColumn('disponibilidade_domingo', 'Disponibilidade Domingo', 'left');
        $column_qtd_reservas_dia = new TDataGridColumn('qtd_reservas_dia', 'Qtd Reservas Dia', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_valor_taxa_locacao);
        $this->datagrid->addColumn($column_tem_taxa);
        $this->datagrid->addColumn($column_intervalo_locacao);
        $this->datagrid->addColumn($column_dias_antecedencia_locacao);
        $this->datagrid->addColumn($column_hora_inicio);
        $this->datagrid->addColumn($column_hora_fim);
        $this->datagrid->addColumn($column_obrigatorio_lista_presenca);
        $this->datagrid->addColumn($column_capacidade);
        $this->datagrid->addColumn($column_intervalo_entre_locacacoes);
        $this->datagrid->addColumn($column_disponibilidade_segunda);
        $this->datagrid->addColumn($column_disponibilidade_terca);
        $this->datagrid->addColumn($column_disponibilidade_quarta);
        $this->datagrid->addColumn($column_disponibilidade_quinta);
        $this->datagrid->addColumn($column_disponibilidade_sexta);
        $this->datagrid->addColumn($column_disponibilidade_sabado);
        $this->datagrid->addColumn($column_disponibilidade_domingo);
        $this->datagrid->addColumn($column_qtd_reservas_dia);


        $action1 = new TDataGridAction(['AreaComumForm', 'onEdit'], ['id'=>'{id}']);
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
            $object = new AreaComum($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_descricao',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_descricao',   $filter); // stores the filter in the session
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
            
            // creates a repository for AreaComum
            $repository = new TRepository('AreaComum');
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


            if (TSession::getValue(__CLASS__.'_filter_descricao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_descricao')); // add the session filter
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
            $object = new AreaComum($key, FALSE); // instantiates the Active Record
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
