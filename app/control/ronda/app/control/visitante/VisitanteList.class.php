<?php
/**
 * VisitanteList Listing
 * @author  <your name here>
 */
class VisitanteList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_Visitante');
        $this->form->setFormTitle('Visitante');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $status = new TEntry('status');
        
        $postofilter = new TCriteria;
        $postofilter->add(new TFilter('unidade_id', '=', TSession::getValue('userunitid')));
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao', 'descricao asc', $postofilter); 
        
        $documento = new TEntry('documento');
        $telefone = new TEntry('telefone');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Documento') ], [ $documento ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $status->setSize('100%');
        $posto_id->setSize('100%');
        $documento->setSize('100%');
        $telefone->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['VisitanteForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_status = new TDataGridColumn('status', 'Status', 'left');
        $column_posto_id = new TDataGridColumn('posto_id', 'Posto Id', 'right');
        $column_motivo_funcao_finalidade = new TDataGridColumn('motivo_funcao_finalidade', 'Motivo Funcao Finalidade', 'left');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'left');
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        //$column_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');
        //$column_permissao_dom_ini = new TDataGridColumn('permissao_dom_ini', 'Permissao Dom Ini', 'left');
        //$column_permissao_dom_fim = new TDataGridColumn('permissao_dom_fim', 'Permissao Dom Fim', 'left');
        //$column_permissao_seg_ini = new TDataGridColumn('permissao_seg_ini', 'Permissao Seg Ini', 'left');
        //$column_permissao_seg_fim = new TDataGridColumn('permissao_seg_fim', 'Permissao Seg Fim', 'left');
        //$column_permissao_ter_ini = new TDataGridColumn('permissao_ter_ini', 'Permissao Ter Ini', 'left');
        //$column_permissao_ter_fim = new TDataGridColumn('permissao_ter_fim', 'Permissao Ter Fim', 'left');
        //$column_permissao_qua_ini = new TDataGridColumn('permissao_qua_ini', 'Permissao Qua Ini', 'left');
        //$column_permissao_qua_fim = new TDataGridColumn('permissao_qua_fim', 'Permissao Qua Fim', 'left');
        //$column_permissao_qui_ini = new TDataGridColumn('permissao_qui_ini', 'Permissao Qui Ini', 'left');
        //$column_permissao_qui_fim = new TDataGridColumn('permissao_qui_fim', 'Permissao Qui Fim', 'left');
        //$column_permissao_sex_ini = new TDataGridColumn('permissao_sex_ini', 'Permissao Sex Ini', 'left');
        //$column_permissao_sex_fim = new TDataGridColumn('permissao_sex_fim', 'Permissao Sex Fim', 'left');
        //$column_permissao_sab_ini = new TDataGridColumn('permissao_sab_ini', 'Permissao Sab Ini', 'left');
        //$column_permissao_sab_fim = new TDataGridColumn('permissao_sab_fim', 'Permissao Sab Fim', 'left');
        //$column_data_permitida = new TDataGridColumn('data_permitida', 'Data Permitida', 'left');
        //$column_data_ini = new TDataGridColumn('data_ini', 'Data Ini', 'left');
        //$column_data_fim = new TDataGridColumn('data_fim', 'Data Fim', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_posto_id);
        $this->datagrid->addColumn($column_motivo_funcao_finalidade);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_telefone);
        //$this->datagrid->addColumn($column_observacao);
        //$this->datagrid->addColumn($column_permissao_dom_ini);
        //$this->datagrid->addColumn($column_permissao_dom_fim);
        //$this->datagrid->addColumn($column_permissao_seg_ini);
        //$this->datagrid->addColumn($column_permissao_seg_fim);
        //$this->datagrid->addColumn($column_permissao_ter_ini);
        //$this->datagrid->addColumn($column_permissao_ter_fim);
        //$this->datagrid->addColumn($column_permissao_qua_ini);
        //$this->datagrid->addColumn($column_permissao_qua_fim);
        //$this->datagrid->addColumn($column_permissao_qui_ini);
        //$this->datagrid->addColumn($column_permissao_qui_fim);
        //$this->datagrid->addColumn($column_permissao_sex_ini);
        //$this->datagrid->addColumn($column_permissao_sex_fim);
        //$this->datagrid->addColumn($column_permissao_sab_ini);
        //$this->datagrid->addColumn($column_permissao_sab_fim);
        //$this->datagrid->addColumn($column_data_permitida);
        //$this->datagrid->addColumn($column_data_ini);
        //$this->datagrid->addColumn($column_data_fim);


        $action1 = new TDataGridAction(['VisitanteForm', 'onEdit'], ['id'=>'{id}']);
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
            
            TTransaction::open('ronda'); // open a transaction with database
            $object = new Visitante($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_status',   NULL);
        TSession::setValue(__CLASS__.'_filter_posto_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_documento',   NULL);
        TSession::setValue(__CLASS__.'_filter_telefone',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', 'like', "%{$data->status}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_status',   $filter); // stores the filter in the session
        }


        if (isset($data->posto_id) AND ($data->posto_id)) {
            $filter = new TFilter('posto_id', '=', $data->posto_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_posto_id',   $filter); // stores the filter in the session
        }


        if (isset($data->documento) AND ($data->documento)) {
            $filter = new TFilter('documento', 'like', "%{$data->documento}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_documento',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone) AND ($data->telefone)) {
            $filter = new TFilter('telefone', 'like', "%{$data->telefone}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_telefone',   $filter); // stores the filter in the session
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
            
            // creates a repository for Visitante
            $repository = new TRepository('Visitante');
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


            if (TSession::getValue(__CLASS__.'_filter_nome')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_nome')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_status')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_status')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_posto_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_posto_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_documento')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_documento')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_telefone')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_telefone')); // add the session filter
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
            $object = new Visitante($key, FALSE); // instantiates the Active Record
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
