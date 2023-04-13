<?php
/**
 * BancoList Listing
 * @author  <your name here>
 */
class BancoList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Banco');
        $this->form->setFormTitle('Banco');
        
        // create the form fields
        $id = new TEntry('id');

        $codigo_bacen = new TEntry('codigo_bacen');
        $codigo_bacen->forceUpperCase();           
             
        $sigla = new TEntry('sigla');
        $sigla->forceUpperCase();          
              
        $descricao = new TEntry('descricao');
        $descricao->forceUpperCase();
        
        //status
        $status = new TCombo('status');
        $status->addItems(array('A' => 'Ativo', 'I' => 'Inativo'));


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Código Bacen') ], [ $codigo_bacen ] );
        $this->form->addFields( [ new TLabel('Sigla') ], [ $sigla ] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );

        // set sizes
        $id->setSize('100%');
        $codigo_bacen->setSize('100%');
        $sigla->setSize('100%');
        $descricao->setSize('100%');
        $status->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Banco_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['BancoForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'center');
        $column_codigo_bacen = new TDataGridColumn('codigo_bacen', 'Código Bacen', 'left');
        $column_sigla = new TDataGridColumn('sigla', 'Sigla', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_status = new TDataGridColumn('status', 'St', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_codigo_bacen);
        $this->datagrid->addColumn($column_sigla);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_status);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['BancoForm', 'onEdit']);
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
        
        TForm::sendData('form_Banco', $obj);
                       
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
            $object = new Banco($key); // instantiates the Active Record
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
        TSession::setValue('BancoList_filter_id',   NULL);
        TSession::setValue('BancoList_filter_codigo_bacen',   NULL);
        TSession::setValue('BancoList_filter_sigla',   NULL);
        TSession::setValue('BancoList_filter_descricao',   NULL);
        TSession::setValue('BancoList_filter_status',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('BancoList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->codigo_bacen) AND ($data->codigo_bacen)) {
            $filter = new TFilter('codigo_bacen', '=', "{$data->codigo_bacen}"); // create the filter
            TSession::setValue('BancoList_filter_codigo_bacen',   $filter); // stores the filter in the session
        }


        if (isset($data->sigla) AND ($data->sigla)) {
            $filter = new TFilter('sigla', 'like', "%{$data->sigla}%"); // create the filter
            TSession::setValue('BancoList_filter_sigla',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('BancoList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', '=', "$data->status"); // create the filter
            TSession::setValue('BancoList_filter_status',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Banco_filter_data', $data);
        
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
            
            // creates a repository for Banco
            $repository = new TRepository('Banco');
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
            

            if (TSession::getValue('BancoList_filter_id')) {
                $criteria->add(TSession::getValue('BancoList_filter_id')); // add the session filter
            }


            if (TSession::getValue('BancoList_filter_codigo_bacen')) {
                $criteria->add(TSession::getValue('BancoList_filter_codigo_bacen')); // add the session filter
            }


            if (TSession::getValue('BancoList_filter_sigla')) {
                $criteria->add(TSession::getValue('BancoList_filter_sigla')); // add the session filter
            }


            if (TSession::getValue('BancoList_filter_descricao')) {
                $criteria->add(TSession::getValue('BancoList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('BancoList_filter_status')) {
                $criteria->add(TSession::getValue('BancoList_filter_status')); // add the session filter
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
            $object = new Banco($key, FALSE); // instantiates the Active Record
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
                    $object = new Banco;
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
