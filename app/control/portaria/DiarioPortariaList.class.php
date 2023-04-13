<?php
/**
 * DiarioPortariaList Listing
 * @author  <your name here>
 */
class DiarioPortariaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_DiarioPortaria');
        $this->form->setFormTitle('Listagem de Diários de Portaria');
        

        // create the form fields
        $id = new TEntry('id');
        $colaborador = new TEntry('colaborador');
        $data_plantao = new TDate('data_plantao');
        $resumo = new TEntry('resumo');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Colaborador') ], [ $colaborador ] );
        $this->form->addFields( [ new TLabel('Data Plantao') ], [ $data_plantao ] );
        $this->form->addFields( [ new TLabel('Resumo') ], [ $resumo ] );

        $data_plantao->setMask('dd/mm/yyyy');
        $data_plantao->setDatabaseMask('yyyy-mm-dd');
        
        // set sizes
        $id->setSize('100%');
        $colaborador->setSize('100%');
        $data_plantao->setSize('100%');
        $resumo->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('DiarioPortaria_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['DiarioPortariaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_data_dia = new TDataGridColumn('data_dia', 'Registro', 'left');
        $column_colaborador = new TDataGridColumn('colaborador', 'Colaborador', 'left');
        $column_data_plantao = new TDataGridColumn('data_plantao', 'Dt Plantão', 'left');
        $column_condominio = new TDataGridColumn('condominio->resumo', 'Condomínio', 'left');
        $column_resumo = new TDataGridColumn('resumo', 'Resumo', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_data_dia);
        $this->datagrid->addColumn($column_colaborador);
        $this->datagrid->addColumn($column_data_plantao);
        $this->datagrid->addColumn($column_condominio);
        $this->datagrid->addColumn($column_resumo);

        $column_data_dia->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y H:m:s');
        });
        
        $column_data_plantao->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        // create EDIT action
        $action_edit = new TDataGridAction(['DiarioPortariaForm', 'onEdit']);
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
            $object = new DiarioPortaria($key); // instantiates the Active Record
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
        TSession::setValue('DiarioPortariaList_filter_id',   NULL);
        TSession::setValue('DiarioPortariaList_filter_colaborador',   NULL);
        TSession::setValue('DiarioPortariaList_filter_data_plantao',   NULL);
        TSession::setValue('DiarioPortariaList_filter_resumo',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('DiarioPortariaList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->colaborador) AND ($data->colaborador)) {
            $filter = new TFilter('colaborador', 'like', "%{$data->colaborador}%"); // create the filter
            TSession::setValue('DiarioPortariaList_filter_colaborador',   $filter); // stores the filter in the session
        }


        if (isset($data->data_plantao) AND ($data->data_plantao)) {
            $filter = new TFilter('data_plantao', '=', "$data->data_plantao"); // create the filter
            TSession::setValue('DiarioPortariaList_filter_data_plantao',   $filter); // stores the filter in the session
        }


        if (isset($data->resumo) AND ($data->resumo)) {
            $filter = new TFilter('resumo', 'like', "%{$data->resumo}%"); // create the filter
            TSession::setValue('DiarioPortariaList_filter_resumo',   $filter); // stores the filter in the session
        }

           
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('DiarioPortaria_filter_data', $data);
        
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
            
            // creates a repository for DiarioPortaria
            $repository = new TRepository('DiarioPortaria');
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
             
            if (TSession::getValue('DiarioPortariaList_filter_id')) {
                $criteria->add(TSession::getValue('DiarioPortariaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('DiarioPortariaList_filter_colaborador')) {
                $criteria->add(TSession::getValue('DiarioPortariaList_filter_colaborador')); // add the session filter
            }


            if (TSession::getValue('DiarioPortariaList_filter_data_plantao')) {
                $criteria->add(TSession::getValue('DiarioPortariaList_filter_data_plantao')); // add the session filter
            }


            if (TSession::getValue('DiarioPortariaList_filter_resumo')) {
                $criteria->add(TSession::getValue('DiarioPortariaList_filter_resumo')); // add the session filter
            }

               // verifica o nivel de acesso do usuario
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            foreach ($users as $user)
            {
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
            }
            
            if ( $nivel_acesso == 3 ) {
                $filter = new TFilter('system_user_login', '=', "{$user->system_user_login}"); // create the filter
                $criteria->add($filter); // add the session filter
            
            }
            
            if ( $nivel_acesso == 2 or $nivel_acesso == 4 ) {
                $filter = new TFilter('condominio_id', '=', "{$condominio_id}"); // create the filter
                $criteria->add($filter); // add the session filter
            
            }
            
            //var_dump($criteria);
            
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
            $object = new DiarioPortaria($key, FALSE); // instantiates the Active Record
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
