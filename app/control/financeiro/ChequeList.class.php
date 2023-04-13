<?php
/**
*
*/
class ChequeList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
    
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Cheque');
        $this->form->setFormTitle( 'Cheques' );
        
               
        // create the form fields
        $id = new TEntry('id');
        $documento = new TEntry('documento');
        $cheque = new TEntry('cheque');
        
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Documento')], [$documento],
                    [new TLabel('Cheque')], [$cheque] ); 
        

        $id->setSize('30%');
        $documento->setSize('70%');
        $cheque->setSize('70%');
         
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Cheque_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('ChequeForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_cheque = new TDataGridColumn('cheque', 'Cheque', 'center');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Dt Liquidação', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_cheque);
        $this->datagrid->addColumn($column_dt_liquidacao);
        $this->datagrid->addColumn($column_valor);
        
        $column_dt_liquidacao->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return '<i class="fa fa-calendar red"/> '.$date->format('d/m/Y');
        });
        

        $column_valor->setTransformer(function($value, $object, $row) {
            $value = "R$ " . number_format($value, 2, ",", ".");
            return $value;
        });

        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_documento = new TAction(array($this, 'onReload'));
        $order_documento->setParameter('order', 'documento');
        $column_documento->setAction($order_documento);

        $order_cheque = new TAction(array($this, 'onReload'));
        $order_cheque->setParameter('order', 'cheque');
        $column_cheque->setAction($order_cheque);

        $order_dt_liquidacao = new TAction(array($this, 'onReload'));
        $order_dt_liquidacao->setParameter('order', 'dt_liquidacao');
        $column_dt_liquidacao->setAction($order_dt_liquidacao);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('ChequeForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        //$action_edit->setDisplayCondition( array($this, 'displayColumn') );
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Define when the action can be displayed
     */
    public function displayColumn( $object )
    {

        if ($object->dt_liquidacao) {
        
            $datahoje = date($object->dt_liquidacao);
            $partes = explode("-", $datahoje);
            $ano_hoje = $partes[0];
            $mes_hoje = $partes[1];
            $mes_ant  = ((int) $mes_hoje ) -1;
            $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
            $dia_hoje = $partes[2];
                
            $mesref = $mes_ant . '/' . $ano_hoje; 
        
            TTransaction::open('facilitasmart');
            $conn = TTransaction::get();                        
            $sqlFecha = "SELECT * FROM fechamento where  
                        condominio_id = " . $object->condominio_id . " and " .
                        "mes_ref = '" . $mesref . " '";
                     
            $fechamentos = $conn->query($sqlFecha);
            $retorna_status = 0;
        
            foreach ($fechamentos as $fechamento) // feito pelo select
            {
                $retorna_status = $fechamento['status'];
            }
        
            TTransaction::close();
                
            if ($retorna_status == 0)
            {
                return TRUE;
            }
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
            $object = new Cheque($key); // instantiates the Active Record
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
        TSession::setValue('ChequeList_filter_id',   NULL);
        TSession::setValue('ChequeList_filter_documento',   NULL);
        TSession::setValue('ChequeList_filter_condominio_id',   NULL);
        TSession::setValue('ChequeList_filter_mes_referencia',   NULL);
        TSession::setValue('ChequeList_filter_dt_emissao',   NULL);
        TSession::setValue('ChequeList_filter_dt_vencimento',   NULL);
        TSession::setValue('ChequeList_filter_dt_liquidacao',   NULL);
        TSession::setValue('ChequeList_filter_cheque',   NULL);
        TSession::setValue('ChequeList_filter_valor',   NULL);
        TSession::setValue('ChequeList_filter_nominal_a',   NULL);
        TSession::setValue('ChequeList_filter_conta_fechamento_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('ChequeList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->documento) AND ($data->documento)) {
            $filter = new TFilter('documento', 'like', "%{$data->documento}%"); // create the filter
            TSession::setValue('ChequeList_filter_documento',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', '=', "$data->condominio_id"); // create the filter
            TSession::setValue('ChequeList_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_referencia) AND ($data->mes_referencia)) {
            $filter = new TFilter('mes_referencia', 'like', "%{$data->mes_referencia}%"); // create the filter
            TSession::setValue('ChequeList_filter_mes_referencia',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_emissao) AND ($data->dt_emissao)) {
            $filter = new TFilter('dt_emissao', 'like', "%{$data->dt_emissao}%"); // create the filter
            TSession::setValue('ChequeList_filter_dt_emissao',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vencimento) AND ($data->dt_vencimento)) {
            $filter = new TFilter('dt_vencimento', 'like', "%{$data->dt_vencimento}%"); // create the filter
            TSession::setValue('ChequeList_filter_dt_vencimento',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_liquidacao) AND ($data->dt_liquidacao)) {
            $filter = new TFilter('dt_liquidacao', 'like', "%{$data->dt_liquidacao}%"); // create the filter
            TSession::setValue('ChequeList_filter_dt_liquidacao',   $filter); // stores the filter in the session
        }


        if (isset($data->cheque) AND ($data->cheque)) {
            $filter = new TFilter('cheque', '=', "$data->cheque"); // create the filter
            TSession::setValue('ChequeList_filter_cheque',   $filter); // stores the filter in the session
        }


        if (isset($data->valor) AND ($data->valor)) {
            $filter = new TFilter('valor', 'like', "%{$data->valor}%"); // create the filter
            TSession::setValue('ChequeList_filter_valor',   $filter); // stores the filter in the session
        }


        if (isset($data->nominal_a) AND ($data->nominal_a)) {
            $filter = new TFilter('nominal_a', 'like', "%{$data->nominal_a}%"); // create the filter
            TSession::setValue('ChequeList_filter_nominal_a',   $filter); // stores the filter in the session
        }


        if (isset($data->conta_fechamento_id) AND ($data->conta_fechamento_id)) {
            $filter = new TFilter('conta_fechamento_id', '=', "$data->conta_fechamento_id"); // create the filter
            TSession::setValue('ChequeList_filter_conta_fechamento_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Cheque_filter_data', $data);
        
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
            
            // creates a repository for Cheque
            $repository = new TRepository('Cheque');
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
            

            if (TSession::getValue('ChequeList_filter_id')) {
                $criteria->add(TSession::getValue('ChequeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_documento')) {
                $criteria->add(TSession::getValue('ChequeList_filter_documento')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_condominio_id')) {
                $criteria->add(TSession::getValue('ChequeList_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_mes_referencia')) {
                $criteria->add(TSession::getValue('ChequeList_filter_mes_referencia')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_dt_emissao')) {
                $criteria->add(TSession::getValue('ChequeList_filter_dt_emissao')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ChequeList_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_dt_liquidacao')) {
                $criteria->add(TSession::getValue('ChequeList_filter_dt_liquidacao')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_cheque')) {
                $criteria->add(TSession::getValue('ChequeList_filter_cheque')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_valor')) {
                $criteria->add(TSession::getValue('ChequeList_filter_valor')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_nominal_a')) {
                $criteria->add(TSession::getValue('ChequeList_filter_nominal_a')); // add the session filter
            }


            if (TSession::getValue('ChequeList_filter_conta_fechamento_id')) {
                $criteria->add(TSession::getValue('ChequeList_filter_conta_fechamento_id')); // add the session filter
            }

            // somente um condomínio
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            // verifica o nivel de acesso do usuario
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            //$users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            //foreach ($users as $user)
            //{
                //if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                    // somente um condomínio
                    //$criteria->add(new TFilter('condominio_id', '=', $user->condominio_id)); // add the session filter
                //} 
            
            //}
            
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
            $object = new Cheque($key, FALSE); // instantiates the Active Record
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


