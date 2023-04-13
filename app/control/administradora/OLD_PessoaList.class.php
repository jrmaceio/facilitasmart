<?php
/**
 * PessoaList Listing
 * @author  <your name here>
 */
class PessoaList extends TPage
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
        $this->form = new TQuickForm('form_search_Pessoa');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Pessoas');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $email = new TEntry('email');

        $label_nome = new TLabel('Nome');
        $label_nome->setFontStyle('b');
        $label_nome->style.=';float:left';
        
        $id->setSize('20%');
        $nome->setSize('70%');
        
        // add the fields
        $this->form->addQuickFields('Id', array( $id, $label_nome, $nome)); 
        //$this->form->addQuickField('Nome', $nome,  '100%' );
        $this->form->addQuickField('Cpf / Cnpj', $cpf,  '50%' );
        $this->form->addQuickField('E-mail', $email,  '100%' );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Pessoa_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('PessoaForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'Cpf/Cnpj', 'left');
        $column_email = new TDataGridColumn('email', 'E-mail', 'left');
        $unidade_descricao = new TDataGridColumn('unidade_descricao', 'Unidade', 'left');
        
        $unidade_descricao->setTransformer(array($this, 'retornaUnidade'));

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($unidade_descricao);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('PessoaForm', 'onEdit'));
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
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Pessoa', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    public function retornaUnidade($campo, $object, $row)
    {
         $campo = Pessoa::getUnidade($object->id); 
        
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
            $object = new Pessoa($key); // instantiates the Active Record
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
        TSession::setValue('PessoaList_filter_id',   NULL);
        TSession::setValue('PessoaList_filter_nome',   NULL);
        TSession::setValue('PessoaList_filter_cpf',   NULL);
        TSession::setValue('PessoaList_filter_email',   NULL);
        TSession::setValue('PessoaList_filter_condominio_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('PessoaList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('PessoaList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->cpf) AND ($data->cpf)) {
            $filter = new TFilter('cpf', 'like', "%{$data->cpf}%"); // create the filter
            TSession::setValue('PessoaList_filter_cpf',   $filter); // stores the filter in the session
        }


        if (isset($data->email) AND ($data->email)) {
            $filter = new TFilter('email', 'like', "%{$data->email}%"); // create the filter
            TSession::setValue('PessoaList_filter_email',   $filter); // stores the filter in the session
        }

        $filter = new TFilter('condominio_id', '=', TSession::getValue('id_condominio')); // create the filter
        TSession::setValue('PessoaList_filter_condominio_id',   $filter); // stores the filter in the session
            
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Pessoa_filter_data', $data);
        
        $param=array();
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
            
            // creates a repository for Pessoa
            $repository = new TRepository('Pessoa');
            $limit = 10;
            
            // usada na atualizacao do cpf e cnpj 
            //$limit = 100000;
            
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
            

            if (TSession::getValue('PessoaList_filter_id')) {
                $criteria->add(TSession::getValue('PessoaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_nome')) {
                $criteria->add(TSession::getValue('PessoaList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_cpf')) {
                $criteria->add(TSession::getValue('PessoaList_filter_cpf')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_email')) {
                $criteria->add(TSession::getValue('PessoaList_filter_email')); // add the session filter
            }

            // somente um imovel selecionado
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
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                    
                    /////////////////////////////////////// atualizar o campo cpf e cnpj
                    //var_dump($object->id);
                    //$obj = new Pessoa( $object->id );
                    //$cpf = str_replace('.', '', $object->cpf);
                    //$cpf = str_replace('-', '', $cpf);
                    
                    //if ($obj->cep == null or $obj->cep == '') {
                    //  $obj->cep = '57000000';
                      
                    //}
                    
                    //if ($obj->endereco == null or $obj->endereco == '') {
                    //  $obj->cep = '57000000';
                     // $obj->endereco = 'rua teste';
                     // $obj->bairro = 'teste';
                     // $obj->cidade = 'arapiraca';
                     // $obj->estado = 'AL';
                      
                    //}
                      
                    //if (strlen($cpf) == 11) { // é cpf
                      //var_dump($cpf);
                    //  $obj->cpf = $cpf;
                    //  $obj->pessoa_fisica_juridica = 'F';
                     // $obj->cnpj = '';
                    //}
                    
                    //if (strlen($cpf) == 14) { // cnpj
                      //var_dump($cpf);
                    //  $obj->cnpj = $cpf;
                    //  $obj->pessoa_fisica_juridica = 'J';
                    //  $obj->cpf = ''; 
                    //}
                    
                    //$obj->store();
                    ///////////////////////////////////////////////////////////////////////
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
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new Pessoa($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
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
