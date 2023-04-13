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
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle('Listagem de Pesssoas por Condomínio');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $email = new TEntry('email');

        // add the fields
        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome]);
        $this->form->addFields([new TLabel('CPF:')],[$cpf],[new TLabel('Email:')],[$email]);


        // set sizes
        $id->setSize(100);
        $cpf->setSize('70%');
        $nome->setSize('70%');
        $email->setSize('70%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Pessoa_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['PessoaForm', 'onEdit']), 'fa:plus green');
        
        //$this->form->addActionLink('atualiza', new TAction([$this, 'onAtualiza']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center' , '50');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'CPF', 'left');
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
        $action_edit = new TDataGridAction(['PessoaForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
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
    
    // atualizar o cpf que tem menos de 11 digitos (correção tamanho do campo cpf e cpf menor)
    public function onAtualiza($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Pessoa
            $repository = new TRepository('Pessoa');
            $limit = 1000;
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


            if (TSession::getValue('PessoaList_filter_rg')) {
                $criteria->add(TSession::getValue('PessoaList_filter_rg')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_cpf')) {
                $criteria->add(TSession::getValue('PessoaList_filter_cpf')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_cnpj')) {
                $criteria->add(TSession::getValue('PessoaList_filter_cnpj')); // add the session filter
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
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $pes = new Pessoa($object->id);
                    
                    if ($pes->condominio_id == 24) {
                        $t = trim($pes->cnpj); // é um cpf
                        
                        if (strlen($t) <= 11) {
                            $pes->cpf = str_pad($t, 11 , '0' , STR_PAD_LEFT);
                            $pes->store(); 
                        }
                    }
                    
                    
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
    
    public function retornaUnidade($campo, $object, $row)
    {
        //$campo = Pessoa::getUnidade($object->id); 
        
        //return $campo;
        $contador = 0;
         
        $conn = TTransaction::get();
        $result = $conn->query("select 
                                  id, descricao, bloco_quadra
                                  from unidade as u
                                  where u.proprietario_id = {$object->id}");
        
        $data = '';
        
        foreach ($result as $row)
        {
            $data = $data . ' ' . $row['id'].'-'.$row['bloco_quadra'] . ' ' . $row['descricao'].'(P)';
            $contador++;
        }
        
        // PESSOA É UM INQUILINO
        if(!$data)
        {
          $conn = TTransaction::get();
          $result = $conn->query("select 
                                	id, descricao
                                    from unidade as u
                                    where u.morador_id = {$object->id}");
          
          $data = '';
        
          foreach ($result as $row)
          {
            $data = $data . ' ' . $row['id'].'-'.$row['bloco_quadra'] . ' ' . $row['descricao'].'(M)';
          }
        
        }
        
        return $data . ' - [' . $contador . '] unidade(s)';
         
         
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
        TSession::setValue('PessoaList_filter_data_nascimento',   NULL);
        TSession::setValue('PessoaList_filter_rg',   NULL);
        TSession::setValue('PessoaList_filter_cpf_cnpj',   NULL);
        TSession::setValue('PessoaList_filter_cpf',   NULL);
        TSession::setValue('PessoaList_filter_cnpj',   NULL);
        TSession::setValue('PessoaList_filter_pessoa_fisica_juridica',   NULL);
        TSession::setValue('PessoaList_filter_telefone1',   NULL);
        TSession::setValue('PessoaList_filter_telefone2',   NULL);
        TSession::setValue('PessoaList_filter_telefone3',   NULL);
        TSession::setValue('PessoaList_filter_email',   NULL);
        TSession::setValue('PessoaList_filter_observacao',   NULL);
        TSession::setValue('PessoaList_filter_cep',   NULL);
        TSession::setValue('PessoaList_filter_endereco',   NULL);
        TSession::setValue('PessoaList_filter_bairro',   NULL);
        TSession::setValue('PessoaList_filter_cidade',   NULL);
        TSession::setValue('PessoaList_filter_estado',   NULL);
        TSession::setValue('PessoaList_filter_condominio_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('PessoaList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('PessoaList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->data_nascimento) AND ($data->data_nascimento)) {
            $filter = new TFilter('data_nascimento', 'like', "%{$data->data_nascimento}%"); // create the filter
            TSession::setValue('PessoaList_filter_data_nascimento',   $filter); // stores the filter in the session
        }


        if (isset($data->rg) AND ($data->rg)) {
            $filter = new TFilter('rg', 'like', "%{$data->rg}%"); // create the filter
            TSession::setValue('PessoaList_filter_rg',   $filter); // stores the filter in the session
        }


        if (isset($data->cpf_cnpj) AND ($data->cpf_cnpj)) {
            $filter = new TFilter('cpf_cnpj', 'like', "%{$data->cpf_cnpj}%"); // create the filter
            TSession::setValue('PessoaList_filter_cpf_cnpj',   $filter); // stores the filter in the session
        }


        if (isset($data->cpf) AND ($data->cpf)) {
            $filter = new TFilter('cpf', 'like', "%{$data->cpf}%"); // create the filter
            TSession::setValue('PessoaList_filter_cpf',   $filter); // stores the filter in the session
        }


        if (isset($data->cnpj) AND ($data->cnpj)) {
            $filter = new TFilter('cnpj', 'like', "%{$data->cnpj}%"); // create the filter
            TSession::setValue('PessoaList_filter_cnpj',   $filter); // stores the filter in the session
        }


        if (isset($data->pessoa_fisica_juridica) AND ($data->pessoa_fisica_juridica)) {
            $filter = new TFilter('pessoa_fisica_juridica', 'like', "%{$data->pessoa_fisica_juridica}%"); // create the filter
            TSession::setValue('PessoaList_filter_pessoa_fisica_juridica',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone1) AND ($data->telefone1)) {
            $filter = new TFilter('telefone1', 'like', "%{$data->telefone1}%"); // create the filter
            TSession::setValue('PessoaList_filter_telefone1',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone2) AND ($data->telefone2)) {
            $filter = new TFilter('telefone2', 'like', "%{$data->telefone2}%"); // create the filter
            TSession::setValue('PessoaList_filter_telefone2',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone3) AND ($data->telefone3)) {
            $filter = new TFilter('telefone3', 'like', "%{$data->telefone3}%"); // create the filter
            TSession::setValue('PessoaList_filter_telefone3',   $filter); // stores the filter in the session
        }


        if (isset($data->email) AND ($data->email)) {
            $filter = new TFilter('email', 'like', "%{$data->email}%"); // create the filter
            TSession::setValue('PessoaList_filter_email',   $filter); // stores the filter in the session
        }


        if (isset($data->observacao) AND ($data->observacao)) {
            $filter = new TFilter('observacao', 'like', "%{$data->observacao}%"); // create the filter
            TSession::setValue('PessoaList_filter_observacao',   $filter); // stores the filter in the session
        }


        if (isset($data->cep) AND ($data->cep)) {
            $filter = new TFilter('cep', 'like', "%{$data->cep}%"); // create the filter
            TSession::setValue('PessoaList_filter_cep',   $filter); // stores the filter in the session
        }


        if (isset($data->endereco) AND ($data->endereco)) {
            $filter = new TFilter('endereco', 'like', "%{$data->endereco}%"); // create the filter
            TSession::setValue('PessoaList_filter_endereco',   $filter); // stores the filter in the session
        }


        if (isset($data->bairro) AND ($data->bairro)) {
            $filter = new TFilter('bairro', 'like', "%{$data->bairro}%"); // create the filter
            TSession::setValue('PessoaList_filter_bairro',   $filter); // stores the filter in the session
        }


        if (isset($data->cidade) AND ($data->cidade)) {
            $filter = new TFilter('cidade', 'like', "%{$data->cidade}%"); // create the filter
            TSession::setValue('PessoaList_filter_cidade',   $filter); // stores the filter in the session
        }


        if (isset($data->estado) AND ($data->estado)) {
            $filter = new TFilter('estado', 'like', "%{$data->estado}%"); // create the filter
            TSession::setValue('PessoaList_filter_estado',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', '=', "$data->condominio_id"); // create the filter
            TSession::setValue('PessoaList_filter_condominio_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Pessoa_filter_data', $data);
        
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
            
            // creates a repository for Pessoa
            $repository = new TRepository('Pessoa');
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
            

            if (TSession::getValue('PessoaList_filter_id')) {
                $criteria->add(TSession::getValue('PessoaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_nome')) {
                $criteria->add(TSession::getValue('PessoaList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_rg')) {
                $criteria->add(TSession::getValue('PessoaList_filter_rg')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_cpf')) {
                $criteria->add(TSession::getValue('PessoaList_filter_cpf')); // add the session filter
            }


            if (TSession::getValue('PessoaList_filter_cnpj')) {
                $criteria->add(TSession::getValue('PessoaList_filter_cnpj')); // add the session filter
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
            $object = new Pessoa($key, FALSE); // instantiates the Active Record
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
