<?php
/**
 * ContaCorrenteList Listing
 * @author  <your name here>
 */
class ContaCorrenteList extends TPage
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
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContaCorrente');
        $this->form->setFormTitle('Conta Corrente');
        
        //$unit_erp = TSession::getValue('cliente_ERP');
        //$unit_emp = TSession::getValue('userempresa');
        
        // create the form fields
        $id = new TEntry('id');
        
        //$id_cliente_erp = new TDBCombo('id_cliente_erp', 'facilitasmart', 'ClienteErp', 'id', 'nome');
        //$id_cliente_erp->setEditable(FALSE);
        
        //$criteria_emp = new TCriteria();
        //$criteria_emp->add(new TFilter('id_cliente_erp','=',$unit_erp));
        //$id_empresa = new TDBCombo('id_empresa', 'facilitasmart', 'Empresa', 'id', 'nome','',$criteria_emp);

        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);

        $conta = new TEntry('conta');
        $conta->forceUpperCase();
        $descricao = new TEntry('descricao');
        $descricao->forceUpperCase();
        $agencia = new TEntry('agencia');
        $agencia->forceUpperCase();
        $titular = new TEntry('titular');
        $titular->forceUpperCase();
        $tipo_conta = new TCombo('tipo_conta');
        $tipo_conta->addItems(array('C'=>'CORRENTE', 'P'=>'POUPANÇA', 'A'=>'APLICAÇÃO'));
        $convenio = new TEntry('convenio');
        $convenio->forceUpperCase();
        $posto = new TEntry('posto');
        $posto->forceUpperCase();
        $arq_remessa = new TEntry('arq_remessa');
        $arq_retorno = new TEntry('arq_retorno');

        $criteria_bco = new TCriteria();
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'descricao');

        //status
        $status = new TCombo('status');
        $status->addItems(array('A' => 'Ativo', 'I' => 'Inativo'));

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio') ], [ $id_condominio ] );
        $this->form->addFields( [ new TLabel('Conta') ], [ $conta ] ,[ new TLabel('Agência') ], [ $agencia ]);
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Titular') ], [ $titular ] ,[ new TLabel('Tipo Conta') ], [ $tipo_conta ]);
        $this->form->addFields( [ new TLabel('Banco') ], [ $id_banco ] , [ new TLabel('Status') ], [ $status ]);
        $this->form->addFields( [ new TLabel('Convênio') ], [ $convenio ] , [ new TLabel('Posto') ], [ $posto ] );
        $this->form->addFields( [ new TLabel('Arq.Remessa') ], [ $arq_remessa ]);
        $this->form->addFields( [ new TLabel('Arq.Retorno') ], [ $arq_retorno ]);

        //BootstrapFormBuilder::hideField('form_ContaCorrente', 'id_cliente_erp');
        //BootstrapFormBuilder::hideField('form_ContaCorrente', 'id_empresa'); 

        // set sizes
        $id->setSize('100%');
        $id_condominio->setSize('100%');
        $conta->setSize('100%');
        $descricao->setSize('100%');
        $agencia->setSize('100%');
        $titular->setSize('100%');
        $convenio->setSize('100%');
        $posto->setSize('100%');        
        $tipo_conta->setSize('100%');
        $id_banco->setSize('100%');
        $status->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContaCorrente_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ContaCorrenteForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_condominio = new TDataGridColumn('condominio->resumo', 'Condomínio', 'left');
        $column_conta = new TDataGridColumn('conta', 'Conta', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_agencia = new TDataGridColumn('agencia', 'Agência', 'center');
        $column_convenio = new TDataGridColumn('convenio', 'Convenio', 'left');
        $column_posto = new TDataGridColumn('posto', 'Posto', 'left');        
        $column_titular = new TDataGridColumn('titular', 'Titular', 'left');        
        $column_tipo_conta = new TDataGridColumn('tipo_conta', 'Tipo Conta', 'center');
        $column_id_banco = new TDataGridColumn('banco->descricao', 'Banco', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_condominio);
        $this->datagrid->addColumn($column_conta);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_agencia);
        $this->datagrid->addColumn($column_convenio);
        $this->datagrid->addColumn($column_posto);        
        $this->datagrid->addColumn($column_titular);
        $this->datagrid->addColumn($column_tipo_conta);
        $this->datagrid->addColumn($column_id_banco);
        $this->datagrid->addColumn($column_status);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['ContaCorrenteForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $this->datagrid->disableDefaultClick();
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        // creates the delete collection button
        $this->deleteButton = new TButton('delete_collection');
        $this->deleteButton->setAction(new TAction(array($this, 'onDeleteCollection')), AdiantiCoreTranslator::translate('Delete selected'));
        $this->deleteButton->setImage('fas:times red');
        $this->formgrid->addField($this->deleteButton);
        
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->add($this->deleteButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $this->transformCallback = array($this, 'onBeforeLoad');


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $gridpack, $this->pageNavigation));
        
        $status_obj = 'A';
                       
        $obj = new StdClass;
        $obj->status = $status_obj;

        TForm::sendData('form_ContaCorrente', $obj);                
        
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
            $object = new ContaCorrente($key); // instantiates the Active Record
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
        TSession::setValue('ContaCorrenteList_filter_id',   NULL);
        TSession::setValue('ContaCorrenteList_filter_id_condominio',   NULL);
        TSession::setValue('ContaCorrenteList_filter_conta',   NULL);
        TSession::setValue('ContaCorrenteList_filter_descricao',   NULL);
        TSession::setValue('ContaCorrenteList_filter_agencia',   NULL);
        TSession::setValue('ContaCorrenteList_filter_convenio',   NULL);
        TSession::setValue('ContaCorrenteList_filter_posto',   NULL);        
        TSession::setValue('ContaCorrenteList_filter_titular',   NULL);
        TSession::setValue('ContaCorrenteList_filter_tipo_conta',   NULL);
        TSession::setValue('ContaCorrenteList_filter_id_banco',   NULL);
        TSession::setValue('ContaCorrenteList_filter_status',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->id_condominio) AND ($data->id_condominio)) {
            $filter = new TFilter('id_condominio', '=', "$data->id_condominio"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_id_condominio',   $filter); // stores the filter in the session
        }

        if (isset($data->conta) AND ($data->conta)) {
            $filter = new TFilter('conta', 'like', "%{$data->conta}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_conta',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->agencia) AND ($data->agencia)) {
            $filter = new TFilter('agencia', 'like', "%{$data->agencia}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_agencia',   $filter); // stores the filter in the session
        }


        if (isset($data->convenio) AND ($data->convenio)) {
            $filter = new TFilter('convenio', 'like', "%{$data->convenio}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_convenio',   $filter); // stores the filter in the session
        }

        if (isset($data->posto) AND ($data->posto)) {
            $filter = new TFilter('posto', 'like', "%{$data->posto}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_posto',   $filter); // stores the filter in the session
        }


        if (isset($data->titular) AND ($data->titular)) {
            $filter = new TFilter('titular', 'like', "%{$data->titular}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_titular',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_conta) AND ($data->tipo_conta)) {
            $filter = new TFilter('tipo_conta', 'like', "%{$data->tipo_conta}%"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_tipo_conta',   $filter); // stores the filter in the session
        }


        if (isset($data->id_banco) AND ($data->id_banco)) {
            $filter = new TFilter('id_banco', '=', "$data->id_banco"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_id_banco',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', '=', "$data->status"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_status',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContaCorrente_filter_data', $data);
        
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
            
            // creates a repository for ContaCorrente
            $repository = new TRepository('ContaCorrente');
            
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->add(new TFilter('id_condominio','=',TSession::getValue('id_condominio'))); 

            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContaCorrenteList_filter_id')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_id_condominio')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_id_condominio')); // add the session filter
            }

            if (TSession::getValue('ContaCorrenteList_filter_conta')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_conta')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_descricao')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_agencia')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_agencia')); // add the session filter
            }

            if (TSession::getValue('ContaCorrenteList_filter_convenio')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_convenio')); // add the session filter
            }

            if (TSession::getValue('ContaCorrenteList_filter_posto')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_posto')); // add the session filter
            }

            if (TSession::getValue('ContaCorrenteList_filter_titular')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_titular')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_tipo_conta')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_tipo_conta')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_id_banco')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_id_banco')); // add the session filter
            }


            if (TSession::getValue('ContaCorrenteList_filter_status')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_status')); // add the session filter
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
            $object = new ContaCorrente($key, FALSE); // instantiates the Active Record
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
     * Ask before delete record collection
     */
    public function onDeleteCollection( $param )
    {
        $data = $this->formgrid->getData(); // get selected records from datagrid
        $this->formgrid->setData($data); // keep form filled
        
        if ($data)
        {
            $selected = array();
            
            // get the record id's
            foreach ($data as $index => $check)
            {
                if ($check == 'on')
                {
                    $selected[] = substr($index,5);
                }
            }
            
            if ($selected)
            {
                // encode record id's as json
                $param['selected'] = json_encode($selected);
                
                // define the delete action
                $action = new TAction(array($this, 'deleteCollection'));
                $action->setParameters($param); // pass the key parameter ahead
                
                // shows a dialog to the user
                new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
            }
        }
    }
    
    /**
     * method deleteCollection()
     * Delete many records
     */
    public function deleteCollection($param)
    {
        // decode json with record id's
        $selected = json_decode($param['selected']);
        
        try
        {
            TTransaction::open('facilitasmart');
            if ($selected)
            {
                // delete each record from collection
                foreach ($selected as $id)
                {
                    $object = new ContaCorrente;
                    $object->delete( $id );
                }
                $posAction = new TAction(array($this, 'onReload'));
                $posAction->setParameters( $param );
                new TMessage('info', AdiantiCoreTranslator::translate('Records deleted'), $posAction);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }


    /**
     * Transform datagrid objects
     * Create the checkbutton as datagrid element
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $deleteAction = $this->deleteButton->getAction();
        $deleteAction->setParameters($param); // important!
        
        $gridfields = array( $this->deleteButton );
        
        foreach ($objects as $object)
        {
            $object->check = new TCheckButton('check' . $object->id);
            $object->check->setIndexValue('on');
            $gridfields[] = $object->check; // important
        }
        
        $this->formgrid->setFields($gridfields);
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
