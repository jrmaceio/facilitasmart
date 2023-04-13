<?php
/**
 * LayoutCnabList Listing
 * @author  <your name here>
 */
class LayoutCnabList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_LayoutCnab');
        $this->form->setFormTitle('LayoutCnab');
        

        // create the form fields
        $id = new TEntry('id');
        
        $tipo_transacao = new TRadioGroup('tipo_transacao');
        $tipo_transacao->addItems(array('1' => 'Cobrança', '2' => 'Desconto'));
        $tipo_transacao->setLayout('horizontal');
        
        $padrao_arquivo = new TRadioGroup('padrao_arquivo');
        $padrao_arquivo->addItems(array('240' => '240', '400' => '400'));
        $padrao_arquivo->setLayout('horizontal');
                
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'sigla' , 'sigla');
        
        $remesa_retorno = new TCombo('remesa_retorno');
        $remesa_retorno->addItems(array('1' => 'Remessa', '2' => 'Retorno'));
        
        $tipo_registro = new TCombo('tipo_registro');
        $tipo_registro->addItems(array('0' => 'Header', '1' => 'cobranca' , '3' => 'lote', '5'=> 'trailler-lote', '9' => 'trailler'));
        
        $seguimento = new TEntry('seguimento');    #(?????)
        
        $sequencia = new TEntry('sequencia');
        $descricao = new TEntry('descricao');
        
        $posicao_inicial = new TEntry('posicao_inicial');
        $posicao_final = new TEntry('posicao_final');
        $posicao_total = new TEntry('posicao_total');

        $formato = new TRadioGroup('formato');
        $formato->addItems(array('A' => 'A', 'N' => 'N' , 'B' => 'B', 'X'=> 'X', 'D' => 'D'));
        $formato->setLayout('horizontal');
                
        $padrao = new TEntry('padrao');
        $comando = new TEntry('comando');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Tipo Transacao') ],[ $tipo_transacao ] );
        $this->form->addFields( [ new TLabel('Padrao Arquivo') ], [ $padrao_arquivo ]);
        /*$this->form->addFields( [ new TLabel('Id Banco') ], [ $id_banco ] );
        $this->form->addFields( [ new TLabel('Remesa Retorno') ], [ $remesa_retorno ] );
        $this->form->addFields( [ new TLabel('Tipo Registro') ], [ $tipo_registro ] );
        $this->form->addFields( [ new TLabel('Seguimento') ], [ $seguimento ] );
        $this->form->addFields( [ new TLabel('Sequencia') ], [ $sequencia ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Posicao Inicial') ], [ $posicao_inicial ] );
        $this->form->addFields( [ new TLabel('Posicao Final') ], [ $posicao_final ] );
        $this->form->addFields( [ new TLabel('Posicao Total') ], [ $posicao_total ] );
        $this->form->addFields( [ new TLabel('Formato') ], [ $formato ] );
        $this->form->addFields( [ new TLabel('Padrao') ], [ $padrao ] );
        */$this->form->addFields( [ new TLabel('Comando') ], [ $comando ] );


        // set sizes
        $id->setSize('30%');
        //$tipo_transacao->setSize('100%');
        //$padrao_arquivo->setSize('100%');
        $id_banco->setSize('100%');
        $remesa_retorno->setSize('100%');
        $tipo_registro->setSize('100%');
        $seguimento->setSize('100%');
        $sequencia->setSize('100%');
        $descricao->setSize('100%');
        $posicao_inicial->setSize('100%');
        $posicao_final->setSize('100%');
        $posicao_total->setSize('100%');
        //$formato->setSize('100%');
        $padrao->setSize('100%');
        $comando->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('LayoutCnab_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['LayoutCnabForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_banco = new TDataGridColumn('banco->sigla', 'Banco', 'left');
        $column_tipo_transacao = new TDataGridColumn('tipo_transacao', 'Tipo', 'center');
        $column_padrao_arquivo = new TDataGridColumn('padrao_arquivo', 'Padrao', 'center');
        $column_remesa_retorno = new TDataGridColumn('remesa_retorno', 'Rem/Ret', 'center');
        $column_tipo_registro = new TDataGridColumn('tipo_registro', 'Tp.Reg', 'center');
        $column_seguimento = new TDataGridColumn('seguimento', 'Seguim', 'center');
        $column_sequencia = new TDataGridColumn('sequencia', 'Seq', 'center');
        $column_posicao_inicial = new TDataGridColumn('posicao_inicial', 'Pos.Ini', 'center');
        $column_posicao_final = new TDataGridColumn('posicao_final', 'Pos.Fim', 'center');
        $column_posicao_total = new TDataGridColumn('posicao_total', 'Pos.Tot', 'center');
        $column_formato = new TDataGridColumn('formato', 'Formato', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_padrao = new TDataGridColumn('padrao', 'Padrao', 'center');
        $column_comando = new TDataGridColumn('comando', 'Comando', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_banco);
        $this->datagrid->addColumn($column_tipo_transacao);
        $this->datagrid->addColumn($column_padrao_arquivo);
        $this->datagrid->addColumn($column_remesa_retorno);
        $this->datagrid->addColumn($column_tipo_registro);
        $this->datagrid->addColumn($column_seguimento);
        $this->datagrid->addColumn($column_sequencia);
        
        $this->datagrid->addColumn($column_posicao_inicial);
        $this->datagrid->addColumn($column_posicao_final);
        $this->datagrid->addColumn($column_posicao_total);
        $this->datagrid->addColumn($column_formato);
        $this->datagrid->addColumn($column_padrao);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_comando);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['LayoutCnabForm', 'onEdit']);
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
        
        // create action -- copia
        $action_copia = new TDataGridAction(['LayoutCnabList', 'onCopiaDigita']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_copia->setLabel('Copia');
        $action_copia->setImage('fa:save orange');
        $action_copia->setField('id');
        $this->datagrid->addAction($action_copia);        
        
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
            $object = new LayoutCnab($key); // instantiates the Active Record
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
        TSession::setValue('LayoutCnabList_filter_id',   NULL);
        TSession::setValue('LayoutCnabList_filter_tipo_transacao',   NULL);
        TSession::setValue('LayoutCnabList_filter_padrao_arquivo',   NULL);
        TSession::setValue('LayoutCnabList_filter_id_banco',   NULL);
        TSession::setValue('LayoutCnabList_filter_remesa_retorno',   NULL);
        TSession::setValue('LayoutCnabList_filter_tipo_registro',   NULL);
        TSession::setValue('LayoutCnabList_filter_seguimento',   NULL);
        TSession::setValue('LayoutCnabList_filter_sequencia',   NULL);
        TSession::setValue('LayoutCnabList_filter_descricao',   NULL);
        TSession::setValue('LayoutCnabList_filter_posicao_inicial',   NULL);
        TSession::setValue('LayoutCnabList_filter_posicao_final',   NULL);
        TSession::setValue('LayoutCnabList_filter_posicao_total',   NULL);
        TSession::setValue('LayoutCnabList_filter_formato',   NULL);
        TSession::setValue('LayoutCnabList_filter_padrao',   NULL);
        TSession::setValue('LayoutCnabList_filter_comando',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('LayoutCnabList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_transacao) AND ($data->tipo_transacao)) {
            $filter = new TFilter('tipo_transacao', 'like', "%{$data->tipo_transacao}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_tipo_transacao',   $filter); // stores the filter in the session
        }


        if (isset($data->padrao_arquivo) AND ($data->padrao_arquivo)) {
            $filter = new TFilter('padrao_arquivo', 'like', "%{$data->padrao_arquivo}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_padrao_arquivo',   $filter); // stores the filter in the session
        }


        if (isset($data->id_banco) AND ($data->id_banco)) {
            $filter = new TFilter('id_banco', '=', "$data->id_banco"); // create the filter
            TSession::setValue('LayoutCnabList_filter_id_banco',   $filter); // stores the filter in the session
        }


        if (isset($data->remesa_retorno) AND ($data->remesa_retorno)) {
            $filter = new TFilter('remesa_retorno', 'like', "%{$data->remesa_retorno}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_remesa_retorno',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_registro) AND ($data->tipo_registro)) {
            $filter = new TFilter('tipo_registro', 'like', "%{$data->tipo_registro}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_tipo_registro',   $filter); // stores the filter in the session
        }


        if (isset($data->seguimento) AND ($data->seguimento)) {
            $filter = new TFilter('seguimento', 'like', "%{$data->seguimento}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_seguimento',   $filter); // stores the filter in the session
        }


        if (isset($data->sequencia) AND ($data->sequencia)) {
            $filter = new TFilter('sequencia', 'like', "%{$data->sequencia}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_sequencia',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->posicao_inicial) AND ($data->posicao_inicial)) {
            $filter = new TFilter('posicao_inicial', 'like', "%{$data->posicao_inicial}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_posicao_inicial',   $filter); // stores the filter in the session
        }


        if (isset($data->posicao_final) AND ($data->posicao_final)) {
            $filter = new TFilter('posicao_final', 'like', "%{$data->posicao_final}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_posicao_final',   $filter); // stores the filter in the session
        }


        if (isset($data->posicao_total) AND ($data->posicao_total)) {
            $filter = new TFilter('posicao_total', 'like', "%{$data->posicao_total}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_posicao_total',   $filter); // stores the filter in the session
        }


        if (isset($data->formato) AND ($data->formato)) {
            $filter = new TFilter('formato', 'like', "%{$data->formato}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_formato',   $filter); // stores the filter in the session
        }


        if (isset($data->padrao) AND ($data->padrao)) {
            $filter = new TFilter('padrao', 'like', "%{$data->padrao}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_padrao',   $filter); // stores the filter in the session
        }


        if (isset($data->comando) AND ($data->comando)) {
            $filter = new TFilter('comando', 'like', "%{$data->comando}%"); // create the filter
            TSession::setValue('LayoutCnabList_filter_comando',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('LayoutCnab_filter_data', $data);
        
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
            
            // creates a repository for LayoutCnab
            $repository = new TRepository('LayoutCnab');
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
            

            if (TSession::getValue('LayoutCnabList_filter_id')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_id')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_tipo_transacao')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_tipo_transacao')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_padrao_arquivo')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_padrao_arquivo')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_id_banco')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_id_banco')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_remesa_retorno')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_remesa_retorno')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_tipo_registro')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_tipo_registro')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_seguimento')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_seguimento')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_sequencia')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_sequencia')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_descricao')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_posicao_inicial')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_posicao_inicial')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_posicao_final')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_posicao_final')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_posicao_total')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_posicao_total')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_formato')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_formato')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_padrao')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_padrao')); // add the session filter
            }


            if (TSession::getValue('LayoutCnabList_filter_comando')) {
                $criteria->add(TSession::getValue('LayoutCnabList_filter_comando')); // add the session filter
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
            $object = new LayoutCnab($key, FALSE); // instantiates the Active Record
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
                    $object = new LayoutCnab;
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



    static function onCopiaDigita ($param)
    {
        $id = $param['id'];
        TTransaction::open('facilitasmart');
        $reg_tab_cnab_origem = LayoutCnab::find($id);
        TTransaction::close();
        $cp_tp_trans  = $reg_tab_cnab_origem->tipo_transacao;
        $cp_pad_arq   = $reg_tab_cnab_origem->padrao_arquivo;
        $cp_id_banco  = $reg_tab_cnab_origem->id_banco;
        $cp_rem_ret   = $reg_tab_cnab_origem->remesa_retorno;
        try 
        {
            $quick = new BootstrapFormBuilder('copia_form');
            $quick->style = 'padding:20px;';
            $tipo = new TEntry('tipo');
            $tipo->setValue('copia');
            BootstrapFormBuilder::hideField('copia_form', 'tipo');
            BootstrapFormBuilder::hideField('copia_form', 'id_cnab');
            
            $id_cnab = new TEntry('id_cnab');
            $id_cnab->setValue($id);
            $id_cnab->setEditable(FALSE);
            $id_cnab->setSize('30%');
            
            $tipo_transacao = new TRadioGroup('tipo_transacao');
            $tipo_transacao->addItems(array('1' => 'Cobrança', '2' => 'Desconto'));
            $tipo_transacao->setValue($cp_tp_trans);
            $tipo_transacao->setLayout('horizontal');
            $tipo_transacao->setSize('100%');
            $tipo_transacao->setEditable(FALSE);
    
            $padrao_arquivo = new TRadioGroup('padrao_arquivo');
            $padrao_arquivo->addItems(array('240' => '240', '400' => '400'));
            $padrao_arquivo->setValue($cp_pad_arq);
            $padrao_arquivo->setLayout('horizontal');
            $padrao_arquivo->setSize('100%');
            $padrao_arquivo->setEditable(FALSE);
            
            $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'sigla','sigla');
            $id_banco->setValue($cp_id_banco);
            $id_banco->setSize('100%');
            $id_banco->setEditable(FALSE);
            
            $remesa_retorno = new TCombo('remesa_retorno');
            $remesa_retorno->addItems(array('1' => 'Remessa', '2' => 'Retorno'));
            $remesa_retorno->setValue($cp_rem_ret);
            $remesa_retorno->setSize('60%');
            $remesa_retorno->setEditable(FALSE);

            // --  
            $id_banco_dest = new TDBCombo('id_banco_dest', 'facilitasmart', 'Banco', 'id', 'sigla' , 'sigla');
            $id_banco_dest->setSize('100%');
            // --
            $quick->addFields( [$tipo] );        
            $quick->addFields( [new TLabel('Id')] , [$id_cnab] );
            $quick->addFields( [new TLabel('Tipo Transacao') ],[ $tipo_transacao ] , [new TLabel('Padrao Arquivo') ], [ $padrao_arquivo ] );
            $quick->addFields( [new TLabel('Remessa ou Retorno') ], [ $remesa_retorno ] );
            $quick->addFields( [new TLabel('Banco Origem') ], [ $id_banco ] );
            $quick->addFields( [new TLabel('Banco Destino') ], [ $id_banco_dest ] );
            
            // form action
            $quick->addAction('Avançar', new TAction(array('LayoutCnabList', 'onCopiaExecuta')), 'fa:arrow-circle-right green');
            $quick->addAction('Voltar',  new TAction(array('LayoutCnabList', 'onReload')), 'fa:arrow-circle-right blue');
            // show the input dialog
            new TInputDialog('Insira os Dados', $quick);
        } // fim try
        catch(Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } // fim catch        

        
    } // fim static function onCopia ($param)



    public function onCopiaExecuta($param)
    {
        try
        {
            $tipo_transacao_origem    = $param['tipo_transacao'];
            $padrao_arquivo_origem    = $param['padrao_arquivo'];
            $remesa_retorno_origem    = $param['remesa_retorno'];
            $id_banco_origem          = $param['id_banco'];
            $id_banco_destino         = $param['id_banco_dest'];
            TTransaction::open('facilitasmart');
            $nome_banco_origem        = Banco::find($id_banco_origem);
            $nome_banco_destino       = Banco::find($id_banco_destino);
            
            $reg_cnab_origem = LayoutCnab::where('tipo_transacao','=', $tipo_transacao_origem)
                                ->where('padrao_arquivo','=', $padrao_arquivo_origem)
                                ->where('remesa_retorno','=', $remesa_retorno_origem)
                                ->where('id_banco','=', $id_banco_origem)
                                ->load();
                                                
            foreach ($reg_cnab_origem as $value_cnab_origem)
            {
                $reg_cnab_destino = new LayoutCnab;
                $reg_cnab_destino->tipo_transacao           = $tipo_transacao_origem;
                $reg_cnab_destino->padrao_arquivo           = $padrao_arquivo_origem;
                $reg_cnab_destino->id_banco                 = $id_banco_destino;
                $reg_cnab_destino->remesa_retorno           = $remesa_retorno_origem;
                $reg_cnab_destino->tipo_registro            = $value_cnab_origem->tipo_registro;
                $reg_cnab_destino->seguimento               = $value_cnab_origem->seguimento;
                $reg_cnab_destino->sequencia                = $value_cnab_origem->sequencia;
                $reg_cnab_destino->descricao                = $value_cnab_origem->descricao;
                $reg_cnab_destino->posicao_inicial          = $value_cnab_origem->posicao_inicial;
                $reg_cnab_destino->posicao_final            = $value_cnab_origem->posicao_final;
                $reg_cnab_destino->posicao_total            = $value_cnab_origem->posicao_total;
                $reg_cnab_destino->formato                  = $value_cnab_origem->formato;
                $reg_cnab_destino->padrao                   = $value_cnab_origem->padrao;
                $reg_cnab_destino->comando                  = $value_cnab_origem->comando;
                $reg_cnab_destino->store();
            }
            TTransaction::close();
            $message = 'Layout Cnab Banco Origem ' . $id_banco_origem . ' - ' . $nome_banco_origem->sigla . '<br>Layout Cnab Banco Destino ' . $id_banco_destino  . ' - ' . $nome_banco_destino->sigla . '<br>Cópia Efetuada com Sucesso !!!';
    		$action = new TAction(array('LayoutCnabList','onReload'));
            new TMessage('info', $message, $action);
        } // fim try
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
       
    } // fim funcao public function onCopia($param)    





}
