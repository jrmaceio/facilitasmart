<?php
/**
 * CondominioList Listing
 * @author  <your name here>
 */
class CondominioList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Condominio');
        $this->form->setFormTitle('Condomínio');
        

        // create the form fields
        $id = new TEntry('id');
        $resumo = new TEntry('resumo');
        $nome = new TEntry('nome');
        $cnpj = new TEntry('cnpj');
        //$inscricao_municipal = new TEntry('inscricao_municipal');
        //$cep = new TEntry('cep');
        //$endereco = new TEntry('endereco');
        //$bairro = new TEntry('bairro');
        //$cidade = new TEntry('cidade');
        //$estado = new TEntry('estado');
        //$site = new TEntry('site');
        //$email = new TEntry('email');
        //$telefone1 = new TEntry('telefone1');
        //$telefone2 = new TEntry('telefone2');
        //$active = new TEntry('active');
        //$dt_cadastro = new TEntry('dt_cadastro');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ], [ new TLabel('Resumo') ], [ $resumo ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('CNPJ') ], [ $cnpj ] );
        //$this->form->addFields( [ new TLabel('Inscricao Municipal') ], [ $inscricao_municipal ] );
        //$this->form->addFields( [ new TLabel('Cep') ], [ $cep ] );
        //$this->form->addFields( [ new TLabel('Endereco') ], [ $endereco ] );
        //$this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ] );
        //$this->form->addFields( [ new TLabel('Cidade') ], [ $cidade ] );
        //$this->form->addFields( [ new TLabel('Estado') ], [ $estado ] );
        //$this->form->addFields( [ new TLabel('Site') ], [ $site ] );
        //$this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        //$this->form->addFields( [ new TLabel('Telefone1') ], [ $telefone1 ] );
        //$this->form->addFields( [ new TLabel('Telefone2') ], [ $telefone2 ] );
        //$this->form->addFields( [ new TLabel('Active') ], [ $active ] );
        //$this->form->addFields( [ new TLabel('Dt Cadastro') ], [ $dt_cadastro ] );


        // set sizes
        $id->setSize('100%');
        $resumo->setSize('100%');
        $nome->setSize('100%');
        $cnpj->setSize('100%');
        //$inscricao_municipal->setSize('100%');
        //$cep->setSize('100%');
        //$endereco->setSize('100%');
        //$bairro->setSize('100%');
        //$cidade->setSize('100%');
        //$estado->setSize('100%');
        //$site->setSize('100%');
        //$email->setSize('100%');
        //$telefone1->setSize('100%');
        //$telefone2->setSize('100%');
        //$active->setSize('100%');
        //$dt_cadastro->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Condominio_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['CondominioForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_resumo = new TDataGridColumn('resumo', 'Resumo', 'left');
        //$column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cnpj = new TDataGridColumn('cnpj', 'CNPJ', 'left');
        //$column_inscricao_municipal = new TDataGridColumn('inscricao_municipal', 'Inscricao Municipal', 'left');
        //$column_cep = new TDataGridColumn('cep', 'Cep', 'left');
        //$column_endereco = new TDataGridColumn('endereco', 'Endereco', 'left');
        //$column_bairro = new TDataGridColumn('bairro', 'Bairro', 'left');
        $column_cidade = new TDataGridColumn('cidade', 'Cidade', 'left');
        //$column_estado = new TDataGridColumn('estado', 'Estado', 'left');
        //$column_site = new TDataGridColumn('site', 'Site', 'left');
        //$column_email = new TDataGridColumn('email', 'Email', 'left');
        //$column_telefone1 = new TDataGridColumn('telefone1', 'Telefone1', 'left');
        //$column_telefone2 = new TDataGridColumn('telefone2', 'Telefone2', 'left');
        $column_active = new TDataGridColumn('active', 'Ativo', 'left');
        $column_dt_cadastro = new TDataGridColumn('dt_cadastro', 'Dt Cadastro', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_resumo);
        //$this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cnpj);
        //$this->datagrid->addColumn($column_inscricao_municipal);
        //$this->datagrid->addColumn($column_cep);
        //$this->datagrid->addColumn($column_endereco);
        //$this->datagrid->addColumn($column_bairro);
        $this->datagrid->addColumn($column_cidade);
        //$this->datagrid->addColumn($column_estado);
        //$this->datagrid->addColumn($column_site);
        //$this->datagrid->addColumn($column_email);
        //$this->datagrid->addColumn($column_telefone1);
        //$this->datagrid->addColumn($column_telefone2);
        $this->datagrid->addColumn($column_active);
        $this->datagrid->addColumn($column_dt_cadastro);

        $column_active->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        // create EDIT action
        $action_edit = new TDataGridAction(['CondominioForm', 'onEdit']);
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
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off fa-lg orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
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
        
        parent::add($container);
    }
    
    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $user = Condominio::find($param['id']);
            
            if ($user instanceof Condominio)
            {
                $user->active = $user->active == 'Y' ? 'N' : 'Y';
                $user->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
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
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new Condominio($key); // instantiates the Active Record
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
        TSession::setValue('CondominioList_filter_id',   NULL);
        TSession::setValue('CondominioList_filter_resumo',   NULL);
        TSession::setValue('CondominioList_filter_nome',   NULL);
        TSession::setValue('CondominioList_filter_cnpj',   NULL);
        TSession::setValue('CondominioList_filter_inscricao_municipal',   NULL);
        TSession::setValue('CondominioList_filter_cep',   NULL);
        TSession::setValue('CondominioList_filter_endereco',   NULL);
        TSession::setValue('CondominioList_filter_bairro',   NULL);
        TSession::setValue('CondominioList_filter_cidade',   NULL);
        TSession::setValue('CondominioList_filter_estado',   NULL);
        TSession::setValue('CondominioList_filter_site',   NULL);
        TSession::setValue('CondominioList_filter_email',   NULL);
        TSession::setValue('CondominioList_filter_telefone1',   NULL);
        TSession::setValue('CondominioList_filter_telefone2',   NULL);
        TSession::setValue('CondominioList_filter_active',   NULL);
        TSession::setValue('CondominioList_filter_dt_cadastro',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('CondominioList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->resumo) AND ($data->resumo)) {
            $filter = new TFilter('resumo', 'like', "%{$data->resumo}%"); // create the filter
            TSession::setValue('CondominioList_filter_resumo',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('CondominioList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->cnpj) AND ($data->cnpj)) {
            $filter = new TFilter('cnpj', 'like', "%{$data->cnpj}%"); // create the filter
            TSession::setValue('CondominioList_filter_cnpj',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Condominio_filter_data', $data);
        
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
            
            // creates a repository for Condominio
            $repository = new TRepository('Condominio');
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
            

            if (TSession::getValue('CondominioList_filter_id')) {
                $criteria->add(TSession::getValue('CondominioList_filter_id')); // add the session filter
            }


            if (TSession::getValue('CondominioList_filter_resumo')) {
                $criteria->add(TSession::getValue('CondominioList_filter_resumo')); // add the session filter
            }


            if (TSession::getValue('CondominioList_filter_nome')) {
                $criteria->add(TSession::getValue('CondominioList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('CondominioList_filter_cnpj')) {
                $criteria->add(TSession::getValue('CondominioList_filter_cnpj')); // add the session filter
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
                    $object->dt_cadastro = TDate::date2br($object->dt_cadastro);
                    
                    // formata cnpj
	                $str = preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/", "$1.$2.$3/$4-$5", $object->cnpj);
                    
                    $object->cnpj = $str;
                    
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
            $object = new Condominio($key, FALSE); // instantiates the Active Record
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
