<?php
/**
 * OcorrenciaUnidadeList Listing
 * @author  <your name here>
 */
class OcorrenciaUnidadeList extends TPage
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
        
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
   
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_OcorrenciaUnidade');
        $this->form->setFormTitle('Listagem de Ocorrências');
        

        // create the form fields
        $id = new TEntry('id');
        
        //$unidade_id = new TDBUniqueSearch('unidade_id', 'facilitasmart', 'Unidade', 'id', 'bloco_quadra');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
            
        $tipo_id = new TEntry('tipo_id');
        $data_ocorrencia = new TDate('data_ocorrencia');
        $data_proximo_contato = new TDate('data_proximo_contato');
        $status = new TEntry('status');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Tipo') ], [ $tipo_id ] );
        $this->form->addFields( [ new TLabel('Data') ], [ $data_ocorrencia ] );
        $this->form->addFields( [ new TLabel('Próximo Contato') ], [ $data_proximo_contato ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );

        $data_proximo_contato->setDatabaseMask('yyyy-mm-dd');
        $data_ocorrencia->setDatabaseMask('yyyy-mm-dd');
        $data_proximo_contato->setMask('dd/mm/yyyy');
        $data_ocorrencia->setMask('dd/mm/yyyy');
        
        // set sizes
        $id->setSize('100%');
        $unidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $data_ocorrencia->setSize('100%');
        $data_proximo_contato->setSize('100%');
        $status->setSize('100%');

       
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('OcorrenciaUnidade_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['OcorrenciaUnidadeForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Popover', 'Info <b> {descricao} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_tipo_id = new TDataGridColumn('tipo_id', 'Tipo', 'right');
        $column_data_ocorrencia = new TDataGridColumn('data_ocorrencia', 'Ocorrencia', 'left');
        $column_data_proximo_contato = new TDataGridColumn('data_proximo_contato', 'Próximo Contato', 'left');
        $column_status = new TDataGridColumn('status', 'Status', 'left');
        $column_system_user_login = new TDataGridColumn('system_user_login', 'Usuário', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_tipo_id);
        $this->datagrid->addColumn($column_data_ocorrencia);
        $this->datagrid->addColumn($column_data_proximo_contato);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_system_user_login);

        $column_data_ocorrencia->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_data_proximo_contato->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        // create EDIT action
        $action_edit = new TDataGridAction(['OcorrenciaUnidadeForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
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
            $object = new OcorrenciaUnidade($key); // instantiates the Active Record
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
        TSession::setValue('OcorrenciaUnidadeList_filter_id',   NULL);
        TSession::setValue('OcorrenciaUnidadeList_filter_unidade_id',   NULL);
        TSession::setValue('OcorrenciaUnidadeList_filter_tipo_id',   NULL);
        TSession::setValue('OcorrenciaUnidadeList_filter_data_ocorrencia',   NULL);
        TSession::setValue('OcorrenciaUnidadeList_filter_data_proximo_contato',   NULL);
        TSession::setValue('OcorrenciaUnidadeList_filter_status',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "$data->unidade_id"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_id) AND ($data->tipo_id)) {
            $filter = new TFilter('tipo_id', '=', "$data->tipo_id"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_tipo_id',   $filter); // stores the filter in the session
        }


        if (isset($data->data_ocorrencia) AND ($data->data_ocorrencia)) {
            $filter = new TFilter('data_ocorrencia', '=', "$data->data_ocorrencia"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_data_ocorrencia',   $filter); // stores the filter in the session
        }


        if (isset($data->data_proximo_contato) AND ($data->data_proximo_contato)) {
            $filter = new TFilter('data_proximo_contato', '=', "$data->data_proximo_contato"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_data_proximo_contato',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', '=', "$data->status"); // create the filter
            TSession::setValue('OcorrenciaUnidadeList_filter_status',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('OcorrenciaUnidade_filter_data', $data);
        
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
            
            // creates a repository for OcorrenciaUnidade
            $repository = new TRepository('OcorrenciaUnidade');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'desc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('OcorrenciaUnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('OcorrenciaUnidadeList_filter_unidade_id')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('OcorrenciaUnidadeList_filter_tipo_id')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_tipo_id')); // add the session filter
            }


            if (TSession::getValue('OcorrenciaUnidadeList_filter_data_ocorrencia')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_data_ocorrencia')); // add the session filter
            }


            if (TSession::getValue('OcorrenciaUnidadeList_filter_data_proximo_contato')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_data_proximo_contato')); // add the session filter
            }


            if (TSession::getValue('OcorrenciaUnidadeList_filter_status')) {
                $criteria->add(TSession::getValue('OcorrenciaUnidadeList_filter_status')); // add the session filter
            }

            // somente um condomínio
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

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
                    $unidade = new Unidade($object->unidade_id);
                    $responsavel = new Pessoa($unidade->proprietario_id);
                    $object->unidade_id = $responsavel->nome;
                    
                    $tipo = new TipoOcorrencia($object->tipo_id);
                    $object->tipo_id = $tipo->descricao;
                    
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
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
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
            $object = new OcorrenciaUnidade($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
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
