<?php
/**
 * FinRemessaList Listing
 * @author  <your name here>
 */
class FinRemessaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_FinRemessa');
        $this->form->setFormTitle('FinRemessa');
        
        //$unit_erp = TSession::getValue('cliente_ERP');
                
        // master fields
        $id = new TEntry('id');
       
        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
		
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'sigla');
        $id_banco->enableSearch();
        
        $criteria_cta_corrente = new TCriteria();        
        //$criteria_cta_corrente->add(new TFilter('id_cliente_erp','=',$unit_erp));
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','',$criteria_cta_corrente);
        
        $id_layout_cnab = new TDBCombo('id_layout_cnab', 'facilitasmart', 'LayoutCnab', 'id', 'tipo_transacao');
        
        $numero_remessa = new TEntry('numero_remessa');
        $dt_emissao = new TDate('dt_emissao');
        
        //$forma_selecao = new TEntry('forma_selecao');
        $forma_selecao = new TCombo('forma_selecao');
        $forma_selecao->addItems(array('1' => 'Digita Titulo', '2' => 'Busca faixa Vencto'));
        
        $dt_vecto_inicial = new TDate('dt_vecto_inicial');
        $dt_vecto_final = new TDate('dt_vecto_final');
        
        //$tipo_transacao = new TEntry('tipo_transacao');
        $tipo_transacao = new TCombo('tipo_transacao');
        $tipo_transacao->addItems(array('1' => 'Cobrança', '2' => 'Desconto'));
                
        //$carteira = new TEntry('carteira');
        $carteira = new TCombo('carteira');
        $carteira->addItems(array('1' => 'Simples banco emite', '2' => 'Rapida cedente emite'));
        
        $id_movto_remessa = new TDBCombo('id_movto_remessa', 'facilitasmart', 'TipoMovtoRemessa', 'id', 'descricao');
        
        //$codigo_protesto = new TEntry('codigo_protesto');
        $codigo_protesto = new TCombo('codigo_protesto');
        $codigo_protesto->addItems(array('1' => 'Protesto', '2' => 'Não protesto'));

        //$dias_protesto = new TEntry('dias_protesto');
        $dias_protesto = new TSpinner('dias_protesto');
        $dias_protesto->setRange(0, 10, 1);
        $dias_protesto->setSize('100%');
                        
        //$codigo_baixa_devolucao = new TEntry('codigo_baixa_devolucao');
        $codigo_baixa_devolucao = new TCombo('codigo_baixa_devolucao');
        $codigo_baixa_devolucao->addItems(array('1' => 'Baixa', '2' => 'Não baixa'));
                
        //$dias_baixa_devolucao = new TEntry('dias_baixa_devolucao');
        $dias_baixa_devolucao = new TSpinner('dias_baixa_devolucao');
        $dias_baixa_devolucao->setRange(0, 60, 1);
        $dias_baixa_devolucao->setSize('100%');
                        
        $vlr_total_titulos = new TEntry('vlr_total_titulos');
        $qtde_total_titulos = new TEntry('qtde_total_titulos');


        // master fields
        $this->form->addFields( [new TLabel('Id')], [$id] );
		$this->form->addFields( [ new TLabel('Condominio') ], [ $id_condominio ] );
        //$this->form->addFields( [new TLabel('CliErp')], [$id_cliente_erp] );
        //$this->form->addFields( [new TLabel('Empresa')], [$id_empresa] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] );
        $this->form->addFields( [new TLabel('Conta Corrente')], [$id_conta_corrente] );
/*        $this->form->addFields( [new TLabel('Id Layout Cnab')], [$id_layout_cnab] );
        $this->form->addFields( [new TLabel('Numero Remessa')], [$numero_remessa] );
        $this->form->addFields( [new TLabel('Forma Selecao')], [$forma_selecao] );
        $this->form->addFields( [new TLabel('Dt Emissao')], [$dt_emissao] , [new TLabel('Dt Vecto Inicial')], [$dt_vecto_inicial] , [new TLabel('Dt Vecto Final')], [$dt_vecto_final] );
        $this->form->addFields( [new TLabel('Tipo Transacao')], [$tipo_transacao] );
        $this->form->addFields( [new TLabel('Carteira')], [$carteira] ,[new TLabel('Id Movto Remessa')], [$id_movto_remessa] );
        $this->form->addFields( [new TLabel('Codigo Protesto')], [$codigo_protesto] );
        $this->form->addFields( [new TLabel('Dias Protesto')], [$dias_protesto] , [new TLabel('Dias Baixa Devolucao')], [$dias_baixa_devolucao]);
        $this->form->addFields( [new TLabel('Codigo Baixa Devolucao')], [$codigo_baixa_devolucao] );
        $this->form->addFields(  [new TLabel('Qtde Total Titulos')], [$qtde_total_titulos] , [new TLabel('Vlr Total Titulos')], [$vlr_total_titulos] );
 */       
        
        // set sizes
        $id->setSize('100%');
		$id_condominio->setSize('100%');
        //$id_cliente_erp->setSize('100%');
        //$id_empresa->setSize('100%');
        $id_banco->setSize('100%');
        $id_conta_corrente->setSize('100%');
        $id_layout_cnab->setSize('100%');
        $numero_remessa->setSize('100%');
        $dt_emissao->setSize('100%');
        $forma_selecao->setSize('100%');
        $dt_vecto_inicial->setSize('100%');
        $dt_vecto_final->setSize('100%');
        $tipo_transacao->setSize('100%');
        $carteira->setSize('100%');
        $id_movto_remessa->setSize('100%');
        $codigo_protesto->setSize('100%');
        $dias_protesto->setSize('100%');
        $codigo_baixa_devolucao->setSize('100%');
        $dias_baixa_devolucao->setSize('100%');
        $vlr_total_titulos->setSize('100%');
        $qtde_total_titulos->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('FinRemessa_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['FinRemessaNotebookForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'center');
		$column_id_condominio = new TDataGridColumn('condominio->resumo', 'Condomínio', 'left');
        //$column_id_cliente_erp = new TDataGridColumn('cliente_erp->nome', 'Cli.Erp', 'left');
        //$column_id_empresa = new TDataGridColumn('empresa->nome', 'Empresa', 'left');
        $column_id_banco = new TDataGridColumn('banco->sigla', 'Banco', 'left');
        $column_id_conta_corrente = new TDataGridColumn('conta_corrente->descricao', 'Conta Corrente', 'left');
        $column_id_layout_cnab = new TDataGridColumn('id_layout_cnab', 'Layout Cnab', 'center');
        $column_numero_remessa = new TDataGridColumn('numero_remessa', 'Nr.Remessa', 'center');
        $column_dt_emissao = new TDataGridColumn('dt_emissao', 'Dt Emissao', 'center');
        $column_forma_selecao = new TDataGridColumn('forma_selecao', 'Forma Selecao', 'right');
        $column_dt_vecto_inicial = new TDataGridColumn('dt_vecto_inicial', 'Dt Vecto Inicial', 'center');
        $column_dt_vecto_final = new TDataGridColumn('dt_vecto_final', 'Dt Vecto Final', 'center');
        $column_tipo_transacao = new TDataGridColumn('tipo_transacao', 'Tipo Transacao', 'right');
        $column_carteira = new TDataGridColumn('carteira', 'Carteira', 'right');
        $column_id_movto_remessa = new TDataGridColumn('id_movto_remessa', 'Id Movto Remessa', 'right');
        $column_codigo_protesto = new TDataGridColumn('codigo_protesto', 'Codigo Protesto', 'right');
        $column_dias_protesto = new TDataGridColumn('dias_protesto', 'Dias Protesto', 'right');
        $column_codigo_baixa_devolucao = new TDataGridColumn('codigo_baixa_devolucao', 'Codigo Baixa Devolucao', 'right');
        $column_dias_baixa_devolucao = new TDataGridColumn('dias_baixa_devolucao', 'Dias Baixa Devolucao', 'right');
        $column_vlr_total_titulos = new TDataGridColumn('vlr_total_titulos', 'Vlr Total Titulos', 'right');
        $column_qtde_total_titulos = new TDataGridColumn('qtde_total_titulos', 'Qtde Total Titulos', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_id);
		$this->datagrid->addColumn($column_id_condominio);
        //$this->datagrid->addColumn($column_id_cliente_erp);
        //$this->datagrid->addColumn($column_id_empresa);
        $this->datagrid->addColumn($column_id_banco);
        $this->datagrid->addColumn($column_id_conta_corrente);
        $this->datagrid->addColumn($column_id_layout_cnab);
        $this->datagrid->addColumn($column_numero_remessa);
        $this->datagrid->addColumn($column_dt_emissao);
        $this->datagrid->addColumn($column_forma_selecao);
        $this->datagrid->addColumn($column_dt_vecto_inicial);
        $this->datagrid->addColumn($column_dt_vecto_final);
        $this->datagrid->addColumn($column_tipo_transacao);
        $this->datagrid->addColumn($column_carteira);
        $this->datagrid->addColumn($column_id_movto_remessa);
        $this->datagrid->addColumn($column_codigo_protesto);
        $this->datagrid->addColumn($column_dias_protesto);
        $this->datagrid->addColumn($column_codigo_baixa_devolucao);
        $this->datagrid->addColumn($column_dias_baixa_devolucao);
        $this->datagrid->addColumn($column_vlr_total_titulos);
        $this->datagrid->addColumn($column_qtde_total_titulos);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['FinRemessaNotebookForm', 'onEdit']);
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

        $obj = new StdClass;        
        ////$obj->dt_emissao = $dt_emissao = date("d-m-Y");                                                  
        //$obj->id_cliente_erp = $id_cliente_erp = TSession::getValue('cliente_ERP');
        //$obj->id_empresa = $id_empresa = TSession::getValue('userempresa');
        //$obj->detail_id_cliente_erp = $detail_id_cliente_erp = TSession::getValue('cliente_ERP');
        //$obj->detail_id_empresa = $detail_id_empresa = TSession::getValue('userempresa');
        TForm::sendData('form_FinRemessa', $obj);

        //BootstrapFormBuilder::hideField('form_FinRemessa', $obj);
                            
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
            $object = new FinRemessa($key); // instantiates the Active Record
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
        TSession::setValue('FinRemessaList_filter_id',   NULL);
		TSession::setValue('ContaCorrenteList_filter_id_condominio',   NULL);
        //TSession::setValue('FinRemessaList_filter_id_cliente_erp',   NULL);
        //TSession::setValue('FinRemessaList_filter_id_empresa',   NULL);
        TSession::setValue('FinRemessaList_filter_id_banco',   NULL);
        TSession::setValue('FinRemessaList_filter_id_conta_corrente',   NULL);
        TSession::setValue('FinRemessaList_filter_id_layout_cnab',   NULL);
        TSession::setValue('FinRemessaList_filter_numero_remessa',   NULL);
        TSession::setValue('FinRemessaList_filter_dt_emissao',   NULL);
        TSession::setValue('FinRemessaList_filter_forma_selecao',   NULL);
        TSession::setValue('FinRemessaList_filter_dt_vecto_inicial',   NULL);
        TSession::setValue('FinRemessaList_filter_dt_vecto_final',   NULL);
        TSession::setValue('FinRemessaList_filter_tipo_transacao',   NULL);
        TSession::setValue('FinRemessaList_filter_carteira',   NULL);
        TSession::setValue('FinRemessaList_filter_id_movto_remessa',   NULL);
        TSession::setValue('FinRemessaList_filter_codigo_protesto',   NULL);
        TSession::setValue('FinRemessaList_filter_dias_protesto',   NULL);
        TSession::setValue('FinRemessaList_filter_codigo_baixa_devolucao',   NULL);
        TSession::setValue('FinRemessaList_filter_dias_baixa_devolucao',   NULL);
        TSession::setValue('FinRemessaList_filter_vlr_total_titulos',   NULL);
        TSession::setValue('FinRemessaList_filter_qtde_total_titulos',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('FinRemessaList_filter_id',   $filter); // stores the filter in the session
        }

        if (isset($data->id_condominio) AND ($data->id_condominio)) {
            $filter = new TFilter('id_condominio', '=', "$data->id_condominio"); // create the filter
            TSession::setValue('ContaCorrenteList_filter_id_condominio',   $filter); // stores the filter in the session
        }


        if (isset($data->id_banco) AND ($data->id_banco)) {
            $filter = new TFilter('id_banco', '=', "$data->id_banco"); // create the filter
            TSession::setValue('FinRemessaList_filter_id_banco',   $filter); // stores the filter in the session
        }


        if (isset($data->id_conta_corrente) AND ($data->id_conta_corrente)) {
            $filter = new TFilter('id_conta_corrente', '=', "$data->id_conta_corrente"); // create the filter
            TSession::setValue('FinRemessaList_filter_id_conta_corrente',   $filter); // stores the filter in the session
        }


        if (isset($data->id_layout_cnab) AND ($data->id_layout_cnab)) {
            $filter = new TFilter('id_layout_cnab', '=', "$data->id_layout_cnab"); // create the filter
            TSession::setValue('FinRemessaList_filter_id_layout_cnab',   $filter); // stores the filter in the session
        }


        if (isset($data->numero_remessa) AND ($data->numero_remessa)) {
            $filter = new TFilter('numero_remessa', 'like', "%{$data->numero_remessa}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_numero_remessa',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_emissao) AND ($data->dt_emissao)) {
            $filter = new TFilter('dt_emissao', 'like', "%{$data->dt_emissao}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_dt_emissao',   $filter); // stores the filter in the session
        }


        if (isset($data->forma_selecao) AND ($data->forma_selecao)) {
            $filter = new TFilter('forma_selecao', 'like', "%{$data->forma_selecao}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_forma_selecao',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vecto_inicial) AND ($data->dt_vecto_inicial)) {
            $filter = new TFilter('dt_vecto_inicial', 'like', "%{$data->dt_vecto_inicial}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_dt_vecto_inicial',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vecto_final) AND ($data->dt_vecto_final)) {
            $filter = new TFilter('dt_vecto_final', 'like', "%{$data->dt_vecto_final}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_dt_vecto_final',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_transacao) AND ($data->tipo_transacao)) {
            $filter = new TFilter('tipo_transacao', 'like', "%{$data->tipo_transacao}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_tipo_transacao',   $filter); // stores the filter in the session
        }


        if (isset($data->carteira) AND ($data->carteira)) {
            $filter = new TFilter('carteira', 'like', "%{$data->carteira}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_carteira',   $filter); // stores the filter in the session
        }


        if (isset($data->id_movto_remessa) AND ($data->id_movto_remessa)) {
            $filter = new TFilter('id_movto_remessa', 'like', "%{$data->id_movto_remessa}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_id_movto_remessa',   $filter); // stores the filter in the session
        }


        if (isset($data->codigo_protesto) AND ($data->codigo_protesto)) {
            $filter = new TFilter('codigo_protesto', 'like', "%{$data->codigo_protesto}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_codigo_protesto',   $filter); // stores the filter in the session
        }


        if (isset($data->dias_protesto) AND ($data->dias_protesto)) {
            $filter = new TFilter('dias_protesto', 'like', "%{$data->dias_protesto}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_dias_protesto',   $filter); // stores the filter in the session
        }


        if (isset($data->codigo_baixa_devolucao) AND ($data->codigo_baixa_devolucao)) {
            $filter = new TFilter('codigo_baixa_devolucao', 'like', "%{$data->codigo_baixa_devolucao}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_codigo_baixa_devolucao',   $filter); // stores the filter in the session
        }


        if (isset($data->dias_baixa_devolucao) AND ($data->dias_baixa_devolucao)) {
            $filter = new TFilter('dias_baixa_devolucao', 'like', "%{$data->dias_baixa_devolucao}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_dias_baixa_devolucao',   $filter); // stores the filter in the session
        }


        if (isset($data->vlr_total_titulos) AND ($data->vlr_total_titulos)) {
            $filter = new TFilter('vlr_total_titulos', 'like', "%{$data->vlr_total_titulos}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_vlr_total_titulos',   $filter); // stores the filter in the session
        }


        if (isset($data->qtde_total_titulos) AND ($data->qtde_total_titulos)) {
            $filter = new TFilter('qtde_total_titulos', 'like', "%{$data->qtde_total_titulos}%"); // create the filter
            TSession::setValue('FinRemessaList_filter_qtde_total_titulos',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('FinRemessa_filter_data', $data);
        
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
            
            // creates a repository for FinRemessa
            $repository = new TRepository('FinRemessa');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            //$criteria->add(new TFilter('id_cliente_erp','=',TSession::getValue('cliente_ERP')));  // valida cliente_erp
			$criteria->add(new TFilter('id_condominio','=',TSession::getValue('id_condominio'))); 
                        
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'desc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('FinRemessaList_filter_id')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_id')); // add the session filter
            }

            if (TSession::getValue('ContaCorrenteList_filter_id_condominio')) {
                $criteria->add(TSession::getValue('ContaCorrenteList_filter_id_condominio')); // add the session filter
            }

            if (TSession::getValue('FinRemessaList_filter_id_banco')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_id_banco')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_id_conta_corrente')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_id_conta_corrente')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_id_layout_cnab')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_id_layout_cnab')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_numero_remessa')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_numero_remessa')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_dt_emissao')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_dt_emissao')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_forma_selecao')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_forma_selecao')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_dt_vecto_inicial')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_dt_vecto_inicial')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_dt_vecto_final')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_dt_vecto_final')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_tipo_transacao')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_tipo_transacao')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_carteira')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_carteira')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_id_movto_remessa')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_id_movto_remessa')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_codigo_protesto')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_codigo_protesto')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_dias_protesto')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_dias_protesto')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_codigo_baixa_devolucao')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_codigo_baixa_devolucao')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_dias_baixa_devolucao')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_dias_baixa_devolucao')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_vlr_total_titulos')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_vlr_total_titulos')); // add the session filter
            }


            if (TSession::getValue('FinRemessaList_filter_qtde_total_titulos')) {
                $criteria->add(TSession::getValue('FinRemessaList_filter_qtde_total_titulos')); // add the session filter
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
            $key=$param['key'];
            TTransaction::open('facilitasmart');
            $object = new FinRemessa($key, FALSE);

            $obj_titulos = FinRemessaItem::where('id_fin_remessa', '=', $param['key'])
                                    ->where('id_condominio', '=', $object->id_condominio)
                                    ->delete();
            
            $object->delete();
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
                    $object = new FinRemessa;
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
