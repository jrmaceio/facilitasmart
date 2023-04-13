<?php
/**
 * ContaFechamentoList Listing
 * @author  <your name here>
 */
class ContaFechamentoList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_ContaFechamento');
        // define the form title
        $this->form->setFormTitle('Contas de Fechamento'); 

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        //$boleto_configuracao_id = new TEntry('boleto_configuracao_id');
        $ativo = new TRadioGroup('ativo'); 

        $ativo->addValidation('Ativo', new TRequiredValidator());  
        
        $ativo->addItems(['0'=>'Não','1'=>'Sim']);
        $ativo->setLayout('horizontal');
        $ativo->setBooleanMode();
        $ativo->setValue('1'); 
         
        // add the fields
        $this->form->addFields([new TLabel('id:')],[$id],[new TLabel('Ativo:', '#ff0000')],[$ativo]); 
        $this->form->addFields([new TLabel('Descrição:', '#ff0000')],[$descricao]);
        
        $this->form->addFields([new TLabel('Condomínio')],[$condominio_id]);
        //$this->form->addFields([new TLabel('Boleto Configuracao Id')],[$boleto_configuracao_id]);

        $ativo->setSize(80);
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContaFechamento_filter_data') );
        
        // add the search form actions
        //$btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        //$btn->class = 'btn btn-sm btn-primary';
        //$this->form->addQuickAction(_t('New'),  new TAction(array('ContaFechamentoForm', 'onEdit')), 'bs:plus-sign green');
        
        // create the form actions
        $btn_onsearch = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary');
        $this->form->addAction(_t('New'),  new TAction(array('ContaFechamentoForm', 'onEdit')), 'bs:plus-sign green');
         
        //$btn_onclear = $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43'); 
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio', 'right');
        $column_boleto_configuracao_id = new TDataGridColumn('boleto_configuracao_id', 'Boleto Configuracao', 'right');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');

         $column_ativo->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_boleto_configuracao_id);
        $this->datagrid->addColumn($column_ativo);

               
        // create EDIT action
        $action_edit = new TDataGridAction(array('ContaFechamentoForm', 'onEdit'));
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
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);    

        // vertical box container
        //$container = new TVBox;
        //$container->style = 'width: 90%';
        //// $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add(TPanelGroup::pack('Title', $this->form));
        //$container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        //parent::add($container);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        // add the vbox inside the page
        parent::add($container);
    }
    
    
    /* Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $user = ContaFechamento::find($param['id']);
            
            if ($user instanceof ContaFechamento)
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
            $object = new ContaFechamento($key); // instantiates the Active Record
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
        TSession::setValue('ContaFechamentoList_filter_id',   NULL);
        TSession::setValue('ContaFechamentoList_filter_descricao',   NULL);
        TSession::setValue('ContaFechamentoList_filter_condominio_id',   NULL);
        TSession::setValue('ContaFechamentoList_filter_boleto_configuracao_id',   NULL);
        TSession::setValue('ContaFechamentoList_filter_ativo',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('ContaFechamentoList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('ContaFechamentoList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', '=', "{$data->condominio_id}"); // create the filter
            TSession::setValue('ContaFechamentoList_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->boleto_configuracao_id) AND ($data->boleto_configuracao_id)) {
            $filter = new TFilter('boleto_configuracao_id', 'like', "%{$data->boleto_configuracao_id}%"); // create the filter
            TSession::setValue('ContaFechamentoList_filter_boleto_configuracao_id',   $filter); // stores the filter in the session
        }


        if (isset($data->ativo) AND ($data->ativo)) {
            $filter = new TFilter('ativo', '=', "{$data->ativo}"); // create the filter
            TSession::setValue('ContaFechamentoList_filter_ativo',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContaFechamento_filter_data', $data);
        
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
            
            // creates a repository for ContaFechamento
            $repository = new TRepository('ContaFechamento');
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
            

            if (TSession::getValue('ContaFechamentoList_filter_id')) {
                $criteria->add(TSession::getValue('ContaFechamentoList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContaFechamentoList_filter_descricao')) {
                $criteria->add(TSession::getValue('ContaFechamentoList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('ContaFechamentoList_filter_condominio_id')) {
                $criteria->add(TSession::getValue('ContaFechamentoList_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('ContaFechamentoList_filter_boleto_configuracao_id')) {
                $criteria->add(TSession::getValue('ContaFechamentoList_filter_boleto_configuracao_id')); // add the session filter
            }


            if (TSession::getValue('ContaFechamentoList_filter_ativo')) {
                $criteria->add(TSession::getValue('ContaFechamentoList_filter_ativo')); // add the session filter
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
                    //TTransaction::open('facilitasmart');
                    $condominio = new Condominio($object->condominio_id); 
                    $boleto_config = new BoletoConfiguracao($object->boleto_configuracao_id);            
                    //TTransaction::close();

                    $object->condominio_id = $condominio->resumo;
                    $object->boleto_configuracao_id = $boleto_config->descricao;
                    
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
            $object = new ContaFechamento($key, FALSE); // instantiates the Active Record
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
