<?php
/**
 * LivroOcorrenciaList Listing
 * @author  <your name here>
 */
class LivroOcorrenciaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_LivroOcorrencia');
        $this->form->setFormTitle('Listagem do Livro Ocorrência');
        

        // create the form fields
        $id = new TEntry('id');
        $pessoa = new TDBUniqueSearch('pessoa', 'facilitasmart', 'Pessoa', 'id', 'nome');
        $data_ocorrencia = new TEntry('data_ocorrencia');
        $status_tratamento = new TEntry('status_tratamento');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Pessoa') ], [ $pessoa ] );
        $this->form->addFields( [ new TLabel('Data Ocorrencia') ], [ $data_ocorrencia ] );
        $this->form->addFields( [ new TLabel('Status Tratamento') ], [ $status_tratamento ] );


        // set sizes
        $id->setSize('100%');
        $pessoa->setSize('100%');
        $data_ocorrencia->setSize('100%');
        $status_tratamento->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('LivroOcorrencia_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        //$column_datahora_cadastro = new TDataGridColumn('datahora_cadastro', 'Datahora Cadastro', 'left');
        $column_pessoa = new TDataGridColumn('pessoa', 'Pessoa', 'left');
        $column_data_ocorrencia = new TDataGridColumn('data_ocorrencia', 'Data', 'left');
        $column_hora_ocorrencia = new TDataGridColumn('hora_ocorrencia', 'Hora', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_status_tratamento = new TDataGridColumn('status_tratamento', 'Tratada', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_datahora_cadastro);
        $this->datagrid->addColumn($column_pessoa);
        $this->datagrid->addColumn($column_data_ocorrencia);
        $this->datagrid->addColumn($column_hora_ocorrencia);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_status_tratamento);

       //// $column_status_tratamento->setTransformer(array($this, 'retornaStatus'));
        
        $column_data_ocorrencia->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        // create EDIT action
        $action_edit = new TDataGridAction(['LivroOcorrenciaMoradorForm', 'onEdit']);
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
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
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
                        
        parent::add($container);
    }
    
    public function retornaStatus($campo, $object, $row)
    {
         $status = array(1 => 'Ativo', 2 => 'Pendente', 3 => 'Encerrado', 4 => 'Cancelado');           
        
         $row->popover = 'true';
         $row->popcontent = "<table class='popover-table' border='0'><tr><td>Status: {$status[$object->status]}</td></tr></table>";
         $row->poptitle = 'Ocorrência: '.$object->conclusao;
         
         $campo = new TImage($object->status.'.png');
         $campo->height=15;
         $campo->width=15;
         return $campo;
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
            $object = new LivroOcorrencia($key); // instantiates the Active Record
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
        TSession::setValue('LivroOcorrenciaList_filter_id',   NULL);
        TSession::setValue('LivroOcorrenciaList_filter_pessoa',   NULL);
        TSession::setValue('LivroOcorrenciaList_filter_data_ocorrencia',   NULL);
        TSession::setValue('LivroOcorrenciaList_filter_status_tratamento',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('LivroOcorrenciaList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->pessoa) AND ($data->pessoa)) {
            $filter = new TFilter('pessoa', '=', "$data->pessoa"); // create the filter
            TSession::setValue('LivroOcorrenciaList_filter_pessoa',   $filter); // stores the filter in the session
        }


        if (isset($data->data_ocorrencia) AND ($data->data_ocorrencia)) {
            $filter = new TFilter('data_ocorrencia', 'like', "%{$data->data_ocorrencia}%"); // create the filter
            TSession::setValue('LivroOcorrenciaList_filter_data_ocorrencia',   $filter); // stores the filter in the session
        }


        if (isset($data->status_tratamento) AND ($data->status_tratamento)) {
            $filter = new TFilter('status_tratamento', 'like', "%{$data->status_tratamento}%"); // create the filter
            TSession::setValue('LivroOcorrenciaList_filter_status_tratamento',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('LivroOcorrencia_filter_data', $data);
        
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
            
            // creates a repository for LivroOcorrencia
            $repository = new TRepository('LivroOcorrencia');
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
            

            if (TSession::getValue('LivroOcorrenciaList_filter_id')) {
                $criteria->add(TSession::getValue('LivroOcorrenciaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('LivroOcorrenciaList_filter_pessoa')) {
                $criteria->add(TSession::getValue('LivroOcorrenciaList_filter_pessoa')); // add the session filter
            }


            if (TSession::getValue('LivroOcorrenciaList_filter_data_ocorrencia')) {
                $criteria->add(TSession::getValue('LivroOcorrenciaList_filter_data_ocorrencia')); // add the session filter
            }


            if (TSession::getValue('LivroOcorrenciaList_filter_status_tratamento')) {
                $criteria->add(TSession::getValue('LivroOcorrenciaList_filter_status_tratamento')); // add the session filter
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
            $object = new LivroOcorrencia($key, FALSE); // instantiates the Active Record
            
            if ($object->system_user_login != TSession::getValue('login')) {
              new TMessage('error', 'Não é possível alterar uma ocorrência de outro usuário !'); // shows the exception error message
              TTransaction::close();
              return;
            }
            
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
