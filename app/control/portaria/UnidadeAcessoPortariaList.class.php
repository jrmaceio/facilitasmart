<?php
/**
 * UnidadeAcessoPortaria Listing
 * @author  <your name here>
 */
class UnidadeAcessoPortariaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Unidade');
        $this->form->setFormTitle('Unidade');
        

        // create the form fields
        $id = new TEntry('id');
        $bloco_quadra = new TEntry('bloco_quadra');
        $descricao = new TEntry('descricao');
       // $condominio_id = new TDBUniqueSearch('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo');
       
        
        $proprietario_id = new TDBSeekButton('proprietario_id', 'facilitasmart', 'form_Unidade', 'Pessoa', 'nome', 'proprietario_id', 'proprietario_name');
        $proprietario_name  = new TEntry('proprietario_name');
        
        //$morador_id = new TEntry('morador_id');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Bloco/Quadra') ], [ $bloco_quadra ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $descricao ] );
        //$this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Proprietário') ], [ $proprietario_id ] );
        //$this->form->addFields( [ new TLabel('Morador') ], [ $morador_id ] );


        // set sizes
        $proprietario_name->setEditable(FALSE);
        $id->setSize('100%');
        $bloco_quadra->setSize('100%');
        $descricao->setSize('100%');
        //$condominio_id->setSize('100%');
        $proprietario_id->setSize('calc(20% - 22px)');
        //$morador_id->setSize('100%');
        $proprietario_name->setSize('80%');
        $proprietario_id->setAuxiliar($proprietario_name);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Unidade_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Limpar', new TAction([$this, 'onClear']), 'fa:eraser');
       
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco/Quadra', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Unidade', 'left');
        //$column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_proprietario_id = new TDataGridColumn('proprietario_nome', 'Proprietário', 'right');
        $column_proprietario_telefone = new TDataGridColumn('proprietario_telefones', 'Telefones', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_descricao);
        //$this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_proprietario_id);
        $this->datagrid->addColumn($column_proprietario_telefone);

        
        // create EDIT action
        //$action_edit = new TDataGridAction(['UnidadeForm', 'onEdit']);
        ////$action_edit->setUseButton(TRUE);
        ////$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        
        $action1 = new TDataGridAction(array($this, 'onView'));
        // add the actions
        $this->datagrid->addQuickAction('View',   $action1, 'id', 'ico_find.png');
        
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
    
    /**
     * method onView()
     * Executed when the user clicks at the view button
     */
    function onView( $param )
    {
        $string = new StringsUtil;
        
        $key = $param['key'];
        
        $datagrid2 = new BootstrapDatagridWrapper(new TQuickGrid);
        
        $datagrid2->style = 'width: 100%';
        $datagrid2->datatable = 'true';
       // $this->datagrid2->enablePopover('Observação', ' <b> {observacao} </b>');
        

        // creates the datagrid columns
        $column2_id = new TDataGridColumn('id', 'Id', 'right');
        //$column2_unidade_id = new TDataGridColumn('unidade->descricao', 'Unidade', 'right');
        //$column2_system_user_login = new TDataGridColumn('system_user_login', 'System User Login', 'left');
        $column2_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column2_data_inicial = new TDataGridColumn('data_inicial', 'Dt Inicial', 'left');
        $column2_data_final = new TDataGridColumn('data_final', 'Dt Final', 'left');
        $column2_documento = new TDataGridColumn('documento', 'Documento', 'left');
        $column2_usa_vaga = new TDataGridColumn('usa_vaga', 'Usa Vaga', 'left');
        //$column2_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');
        //$column2_atualizacao = new TDataGridColumn('atualizacao', 'Atualizacao', 'left');


        // add the columns to the DataGrid
        $datagrid2->addColumn($column2_id);
        //$datagrid2->addColumn($column2_unidade_id);
        //$this->datagrid2->addColumn($column2_system_user_login);
        $datagrid2->addColumn($column2_nome);
        $datagrid2->addColumn($column2_data_inicial);
        $datagrid2->addColumn($column2_data_final);
        $datagrid2->addColumn($column2_documento);
        $datagrid2->addColumn($column2_usa_vaga);
        //$this->datagrid2->addColumn($column2_observacao);
        //$this->datagrid2->addColumn($column2_atualizacao);
        
        // create the datagrid model
        $datagrid2->createModel();
        
        TTransaction::open('facilitasmart');
        $conn = TTransaction::get();
        // busca as autorizações
        $result = $conn->query("select * from autorizacao_acesso
                                  where unidade_id = {$key}");
                                
        foreach ($result as $row)
        {
            if($row['usa_vaga'] == '1')
            {
                $row['usa_vaga'] = 'SIM';    
            }
            else
            {
                $row['usa_vaga'] = 'NÃO';
            }
        
            $row['data_inicial'] = $string->formatDateBR($row['data_inicial']);
            $row['data_final'] = $string->formatDateBR($row['data_final']);
                
            $datagrid2->addItem( (object) $row );
        }
        
        TTransaction::close();
        
        $win = TWindow::create('Autorizados', 0.7, 0.7);
        $win->add($datagrid2);
        $win->show();        
        
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
            $object = new Unidade($key); // instantiates the Active Record
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
        TSession::setValue('UnidadeAcessoPortaria_filter_id',   NULL);
        TSession::setValue('UnidadeAcessoPortaria_filter_bloco_quadra',   NULL);
        TSession::setValue('UnidadeAcessoPortaria_filter_descricao',   NULL);
        TSession::setValue('UnidadeAcessoPortaria_filter_condominio_id',   NULL);
        TSession::setValue('UnidadeAcessoPortaria_filter_proprietario_id',   NULL);
        TSession::setValue('UnidadeAcessoPortaria_filter_morador_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('UnidadeAcessoPortaria_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->bloco_quadra) AND ($data->bloco_quadra)) {
            $filter = new TFilter('bloco_quadra', '=', "{$data->bloco_quadra}"); // create the filter
            TSession::setValue('UnidadeAcessoPortaria_filter_bloco_quadra',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('UnidadeAcessoPortaria_filter_descricao',   $filter); // stores the filter in the session
        }


        //if (isset($data->condominio_id) AND ($data->condominio_id)) {
       //     $filter = new TFilter('condominio_id', '=', "$data->condominio_id"); // create the filter
       //     TSession::setValue('UnidadeAcessoPortaria_filter_condominio_id',   $filter); // stores the filter in the session
        //}


        if (isset($data->proprietario_id) AND ($data->proprietario_id)) {
            $filter = new TFilter('proprietario_id', '=', "{$data->proprietario_id}"); // create the filter
            TSession::setValue('UnidadeAcessoPortaria_filter_proprietario_id',   $filter); // stores the filter in the session
        }


        //if (isset($data->morador_id) AND ($data->morador_id)) {
        //    $filter = new TFilter('morador_id', '=', "{$data->morador_id}"); // create the filter
        //    TSession::setValue('UnidadeAcessoPortaria_filter_morador_id',   $filter); // stores the filter in the session
        //}

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Unidade_filter_data', $data);
        
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
            
            // creates a repository for Unidade
            $repository = new TRepository('Unidade');
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
            

            if (TSession::getValue('UnidadeAcessoPortaria_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeAcessoPortaria_filter_bloco_quadra')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_bloco_quadra')); // add the session filter
            }


            if (TSession::getValue('UnidadeAcessoPortaria_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('UnidadeAcessoPortaria_filter_condominio_id')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeAcessoPortaria_filter_proprietario_id')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_proprietario_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeAcessoPortaria_filter_morador_id')) {
                $criteria->add(TSession::getValue('UnidadeAcessoPortaria_filter_morador_id')); // add the session filter
            }

            $condominio_id = TSession::getValue('id_condominio');
            $criteria->add(new TFilter('condominio_id', '=', "{$condominio_id}"));
            
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
                    
                    $unidade = $object->id; 
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
            $object = new Unidade($key, FALSE); // instantiates the Active Record
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
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear();
        $param['proprietario_name'] = '';
        $this->onReload($param);
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
