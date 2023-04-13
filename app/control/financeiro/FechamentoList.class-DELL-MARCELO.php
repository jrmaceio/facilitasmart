<?php
/**
 * FechamentoList Listing
 * @author  <your name here>
 */
class FechamentoList extends TPage
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

        $this->form = new BootstrapFormBuilder('form_search_Fechamento');
        $this->form->setFormTitle('Relação de Fechamentos');
        

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');

        $id->setSize('30%');
        $mes_ref->setSize('70%');
        
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Mês Referência')], [$mes_ref] );
        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Fechamento_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('FechamentoForm', 'onEdit')), 'fa:plus green');
        $this->form->addAction(_t('Open'),  new TAction(array($this, 'onAbrirCollection')), 'fa:plus blue');
        $this->form->addAction(_t('Close'),  new TAction(array($this, 'onFecharCollection')), 'fa:plus red');
    
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
  

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_imovel_id = new TDataGridColumn('condominio_id', 'Condomínio', 'center');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'center');
        $column_saldo_final = new TDataGridColumn('saldo_final', 'Saldo Final', 'right');
        $column_taxa_inadimplencia = new TDataGridColumn('taxa_inadimplencia', 'Taxa Inadimplencia', 'right');
        $column_mostra = new TDataGridColumn('mostra_fechamento', 'Mostra', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'left');

        $column_check->enableAutoHide(500);
        $column_id->enableAutoHide(500);
        $column_imovel_id->enableAutoHide(500);
        $column_mes_ref->enableAutoHide(500);
        $column_saldo_final->enableAutoHide(500);
        $column_taxa_inadimplencia->enableAutoHide(500);
        $column_mostra->enableAutoHide(500);
        $column_status->enableAutoHide(500);
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_imovel_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_saldo_final);
        $this->datagrid->addColumn($column_taxa_inadimplencia);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_mostra);
        
        $column_mostra->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');            
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        $column_imovel_id->setTransformer( function($value, $object, $row) {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio($value);
            $resumo = $condominio->resumo; 
            TTransaction::close();
            return utf8_decode($resumo);
        });
        
        $column_saldo_final->setTransformer( function($value, $object, $row) {
            if (is_null($value) or $value == '') {
                $value = 0;
            }
            return number_format($value, 2, ',', '.'); 
        });
        
        
        $column_taxa_inadimplencia->setTransformer( function($value, $object, $row) {
            return number_format($value, 2, ',', '.'); 
        });
        
        $column_status->setTransformer(function($value, $object, $row) {
            if ( $value == '0' ) {
              $label = ' Aberto';
              $div = new TElement("span");
              $div->class = "small-box-footer";
              $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
              $div->add("<i class=\"fa fa-unlock-alt green\"></i>");
              $div->add($label);
              return $div;                

            }else {
                     $label = ' Fechado';
                     $div = new TElement("span");
                     $div->class = "small-box-footer";
                     $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
                     $div->add("<i class=\"fa fa-lock red\"></i>");
                     $div->add($label);
                     return $div;
                 }
            
        });

        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel('Mostra/Esconde');
        $action_onoff->setImage('fa:power-off fa-lg orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('FechamentoForm', 'onEdit'));
        $action_edit->setUseButton(TRUE);
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        $action_edit->setDisplayCondition( array($this, 'displayColumn') );
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        $this->datagrid->disableDefaultClick();
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        // creates the delete collection button
        $this->abrirButton = new TButton('abrir_collection');
        $this->abrirButton->setAction(new TAction(array($this, 'onAbrirCollection')), 'Abrir Selecionados');
        $this->abrirButton->setImage('fa:remove green');
        $this->formgrid->addField($this->abrirButton);
        
        // creates the fechar collection button
        $this->fecharButton = new TButton('fechar_collection');
        $this->fecharButton->setAction(new TAction(array($this, 'onFecharCollection')), 'Fechar Selecionados');
        $this->fecharButton->setImage('fa:remove red');
        $this->formgrid->addField($this->fecharButton);
        
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->add($this->abrirButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        $gridpack->add($this->fecharButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $this->transformCallback = array($this, 'onBeforeLoad');
        
       // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
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
            $user = Fechamento::find($param['id']);
            
            //var_dump($user);
            
            if ($user instanceof Fechamento)
            {
                $user->mostra_fechamento = $user->mostra_fechamento == 'Y' ? 'N' : 'Y';
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
     * Define when the action can be displayed
     */
    public function displayColumn( $object )
    {
        if ($object->status == 0)
        {
            return TRUE;
        }
        return FALSE;
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
            $object = new Fechamento($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('FechamentoList_filter_id',   NULL);
        TSession::setValue('FechamentoList_filter_mes_ref',   NULL);
        TSession::setValue('FechamentoList_filter_status',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('FechamentoList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "{$data->mes_ref}"); // create the filter
            TSession::setValue('FechamentoList_filter_mes_ref',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', '=', "{$data->status}"); // create the filter
            TSession::setValue('FechamentoList_filter_status',   $filter); // stores the filter in the session
        }

           
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Fechamento_filter_data', $data);
        
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
            
            // creates a repository for Fechamento
            $repository = new TRepository('Fechamento');
            $limit = 4;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'mes_ref';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('FechamentoList_filter_id')) {
                $criteria->add(TSession::getValue('FechamentoList_filter_id')); // add the session filter
            }


            if (TSession::getValue('FechamentoList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('FechamentoList_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('FechamentoList_filter_status')) {
                $criteria->add(TSession::getValue('FechamentoList_filter_status')); // add the session filter
            }

            // filtros obrigatorios
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
            $object = new Fechamento($key, FALSE); // instantiates the Active Record
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
                    $object = new Fechamento;
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
        //$deleteAction = $this->deleteButton->getAction();
        //$deleteAction->setParameters($param); // important!
        
        //$gridfields = array( $this->deleteButton );
        
        $abrirAction = $this->abrirButton->getAction();
        $abrirAction->setParameters($param); // important!
        $gridfields1 = array( $this->abrirButton );
        
        $fecharAction = $this->fecharButton->getAction();
        $fecharAction->setParameters($param); // important!
        $gridfields2 = array( $this->fecharButton );
        
        foreach ($objects as $object)
        {
            $object->check = new TCheckButton('check' . $object->id);
            $object->check->setIndexValue('on');
            $gridfields1[] = $object->check; // important
            $gridfields2[] = $object->check; // important
        }
        
        $this->formgrid->setFields($gridfields1);
        $this->formgrid->setFields($gridfields2);
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
    
    public function onFecharCollection( $param )
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
                $action = new TAction(array($this, 'fecharCollection'));
                $action->setParameters($param); // pass the key parameter ahead
                
                // shows a dialog to the user
                new TQuestion('Confirma o fechamento do(s) selecionado(s) ?', $action);
            }
        }
    }
    

    public function fecharCollection($param)
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
                    $object = new Fechamento( $id );
                    $object->status = '1';
                    $object->store();
                    //$object->delete( $id );
                }
                $posAction = new TAction(array($this, 'onReload'));
                $posAction->setParameters( $param );
                new TMessage('info', 'Fechado(s).', $posAction);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onAbrirCollection( $param )
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
                $action = new TAction(array($this, 'abrirCollection'));
                $action->setParameters($param); // pass the key parameter ahead
                
                // shows a dialog to the user
                new TQuestion('Confirma a abertura do(s) selecionado(s) ?', $action);
            }
        }
    }
    

    public function abrirCollection($param)
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
                    $object = new Fechamento( $id );
                    $object->status = '0';
                    $object->store();
                    //$object->delete( $id );
                }
                $posAction = new TAction(array($this, 'onReload'));
                $posAction->setParameters( $param );
                new TMessage('info', 'Aberto(s).', $posAction);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

}
