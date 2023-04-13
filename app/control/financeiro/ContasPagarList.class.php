<?php
/**
 * ContasPagarList Listing
 * @author  <your name here>
 */
class ContasPagarList extends TPage
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
        //$this->form = new TQuickForm('form_search_ContasPagar');
        //$this->form->class = 'tform'; // change CSS class
        //$this->form = new BootstrapFormWrapper($this->form);
        //$this->form->style = 'display: table;width:100%'; // change style
        //$this->form->setFormTitle('ContasPagar');
        $this->form = new BootstrapFormBuilder('form_search_ContasPagar');
        $this->form->setFormTitle('Listagem de Despesas');

        // create the form fields
        $id = new TEntry('id');
        
        $mes_ref = new TEntry('mes_ref');
        $tipo_lancamento = new TDBCombo('tipo_lancamento', 'facilitasmart', 'TipoPagamento', 'id', 'descricao');
        
        //$classe_id = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'D'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);

        
        $documento = new TEntry('documento');
        $dt_lancamento = new TEntry('dt_lancamento');
        $dt_vencimento = new TEntry('dt_vencimento');
        $valor = new TEntry('valor');
        $descricao = new TEntry('descricao');
        $situacao = new TEntry('situacao');
        $dt_pagamento = new TEntry('dt_pagamento');
        $dt_liquidacao = new TEntry('dt_liquidacao');
        $valor_pago = new TEntry('valor_pago');
        $desconto = new TEntry('desconto');
        $juros = new TEntry('juros');
        $multa = new TEntry('multa');
        $correcao = new TEntry('correcao');
        $conta_fechamento_id = new TEntry('conta_fechamento_id');
        $tipo_pagamento_id = new TEntry('tipo_pagamento_id');
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');
        $linha_digitavel = new TEntry('linha_digitavel');
        $parcela = new TEntry('parcela');
        $usuario_id = new TEntry('usuario_id');
        $data_atualizacao = new TEntry('data_atualizacao');


        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Mês Ref')], [$mes_ref], [new TLabel('Tipo Lançamento')], [$tipo_lancamento] );

        $this->form->addFields( [new TLabel('Classe')], [$classe_id], [new TLabel('Dt Pagamento')], [$dt_pagamento], 
                                [new TLabel('Dt Liquidação')], [$dt_liquidacao] );
 
        $this->form->addFields( [new TLabel('Descrição')], [$descricao], 
                                [new TLabel('Doc Pagamento')], [$numero_doc_pagamento] );

        $this->form->addFields( [new TLabel('Documento')], [$documento], 
                                [new TLabel('Valor')], [$valor] ); 

        $this->form->addFields( [new TLabel('Dt Lancamento')], [$dt_lancamento], 
                                [new TLabel('Dt Vencimento')], [$dt_vencimento] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasPagar_filter_data') );
   
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'), new TAction(array('ContasPagarForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Classe', ' <b> {classe_descricao} </b>');
        $this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'left');
        $column_tipo_lancamento = new TDataGridColumn('tipo_lancamento', 'Tipo Lançamento', 'left');
        //$column_dt_lancamento = new TDataGridColumn('dt_lancamento', 'Lançamento', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'left');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Liquidação', 'left');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'right');
        $column_numero_doc_pagamento = new TDataGridColumn('numero_doc_pagamento', 'Doc Pag', 'right');
        $column_conta_fechamento_id = new TDataGridColumn('conta_fechamento_id', 'Ct Fech', 'right');

       // $column_link->setTransformer( function($value, $object, $row) {
        //    $value='http://www.facilitahomeservice.com.br/facilitasmart/'.$value;
            ////$nome_variavel = "<a target='_blank' style='width:100%' href='{$value}'>Clique Aqui: <b style='color:blue;'>{$value}</b></a>";
            //$nome_variavel = "<a target='_blank' style='width:100%' href='{$value}'> <b style='color:blue;'>link</b></a>";
            //return $nome_variavel ;
        //});
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_numero_doc_pagamento);
        //$this->datagrid->addColumn($column_dt_lancamento);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_dt_liquidacao);
        $this->datagrid->addColumn($column_valor_pago);
        $this->datagrid->addColumn($column_conta_fechamento_id);

        $column_situacao->setTransformer( function($value, $object, $row) {
            if ( $value == '0' ) {
              $label = ' Aberto';
              $div = new TElement("span");
              $div->class = "small-box-footer";
              $div->add("<i class=\"fa fa-star-o red\"></i>");
              $div->add($label);
              return $div;                

            }
            else {
                   $label = ' Pago';
                   $div = new TElement("span");
                   $div->class = "small-box-footer";
                   $div->add("<i class=\"fa fa-star green\"></i>");
                   $div->add($label);
                   return $div;
                 }
            
        });

        $column_valor_pago->setTotalFunction( function($values) {
            return number_format(array_sum((array) $values), 2, ',', '.');
        });
        
        $column_dt_liquidacao->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        //$column_dt_lancamento->setTransformer( function($value, $object, $row) {
        //    $date = new DateTime($value);
        //    return $date->format('d/m/Y');
        //});

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        // creates the datagrid actions
        $action1 = new TDataGridAction(array('ContasPagarFormBaixa', 'onEdit'));
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        $action1->setLabel('Liquidar');
        $action1->setImage('far:edit green');
        $action1->setField('id');
        $this->datagrid->addAction($action1);
        
        // create EDIT action
        $action_onEdit = new TDataGridAction(array('ContasPagarForm', 'onEdit'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel('Editar');
        $action_onEdit->setImage('far:edit blue');
        $action_onEdit->setField('id');
        $this->datagrid->addAction($action_onEdit);
        
        // create DELETE action
        $action_onDelete = new TDataGridAction(array($this, 'onDelete'));
        $action_onDelete->setUseButton(false);
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel('Excluir');
        $action_onDelete->setImage('far:trash-alt red');
        $action_onDelete->setField('id');
        $this->datagrid->addAction($action_onDelete);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
         
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        // add the vbox inside the page
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
            $object = new ContasPagar($key); // instantiates the Active Record
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
        TSession::setValue('ContasPagarList_filter_id',   NULL);
        TSession::setValue('ContasPagarList_filter_condominio_id',   NULL);
        TSession::setValue('ContasPagarList_filter_mes_ref',   NULL);
        TSession::setValue('ContasPagarList_filter_tipo_lancamento',   NULL);
        TSession::setValue('ContasPagarList_filter_classe_id',   NULL);
        TSession::setValue('ContasPagarList_filter_documento',   NULL);
        TSession::setValue('ContasPagarList_filter_dt_lancamento',   NULL);
        TSession::setValue('ContasPagarList_filter_dt_vencimento',   NULL);
        TSession::setValue('ContasPagarList_filter_valor',   NULL);
        TSession::setValue('ContasPagarList_filter_descricao',   NULL);
        TSession::setValue('ContasPagarList_filter_situacao',   NULL);
        TSession::setValue('ContasPagarList_filter_dt_pagamento',   NULL);
        TSession::setValue('ContasPagarList_filter_dt_liquidacao',   NULL);
        TSession::setValue('ContasPagarList_filter_valor_pago',   NULL);
        TSession::setValue('ContasPagarList_filter_desconto',   NULL);
        TSession::setValue('ContasPagarList_filter_juros',   NULL);
        TSession::setValue('ContasPagarList_filter_multa',   NULL);
        TSession::setValue('ContasPagarList_filter_correcao',   NULL);
        TSession::setValue('ContasPagarList_filter_conta_fechamento_id',   NULL);
        TSession::setValue('ContasPagarList_filter_tipo_pagamento_id',   NULL);
        TSession::setValue('ContasPagarList_filter_numero_doc_pagamento',   NULL);
        TSession::setValue('ContasPagarList_filter_linha_digitavel',   NULL);
        TSession::setValue('ContasPagarList_filter_parcela',   NULL);
        TSession::setValue('ContasPagarList_filter_usuario_id',   NULL);
        TSession::setValue('ContasPagarList_filter_data_atualizacao',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', 'like', "%{$data->condominio_id}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "{$data->mes_ref}"); // create the filter
            TSession::setValue('ContasPagarList_filter_mes_ref',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_lancamento) AND ($data->tipo_lancamento)) {
            $filter = new TFilter('tipo_lancamento', 'like', "%{$data->tipo_lancamento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_tipo_lancamento',   $filter); // stores the filter in the session
        }


        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', '=', "{$data->classe_id}"); // create the filter
            TSession::setValue('ContasPagarList_filter_classe_id',   $filter); // stores the filter in the session
        }


        if (isset($data->documento) AND ($data->documento)) {
            $filter = new TFilter('documento', 'like', "%{$data->documento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_documento',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_lancamento) AND ($data->dt_lancamento)) {
            $filter = new TFilter('dt_lancamento', 'like', "%{$data->dt_lancamento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_dt_lancamento',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vencimento) AND ($data->dt_vencimento)) {
            $filter = new TFilter('dt_vencimento', 'like', "%{$data->dt_vencimento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_dt_vencimento',   $filter); // stores the filter in the session
        }


        if (isset($data->valor) AND ($data->valor)) {
            $filter = new TFilter('valor', 'like', "%{$data->valor}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_valor',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->situacao) AND ($data->situacao)) {
            $filter = new TFilter('situacao', 'like', "%{$data->situacao}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_situacao',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_pagamento) AND ($data->dt_pagamento)) {
            $filter = new TFilter('dt_pagamento', 'like', "%{$data->dt_pagamento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_dt_pagamento',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_liquidacao) AND ($data->dt_liquidacao)) {
            $filter = new TFilter('dt_liquidacao', 'like', "%{$data->dt_liquidacao}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_dt_liquidacao',   $filter); // stores the filter in the session
        }


        if (isset($data->valor_pago) AND ($data->valor_pago)) {
            $filter = new TFilter('valor_pago', 'like', "%{$data->valor_pago}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_valor_pago',   $filter); // stores the filter in the session
        }


        if (isset($data->desconto) AND ($data->desconto)) {
            $filter = new TFilter('desconto', 'like', "%{$data->desconto}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_desconto',   $filter); // stores the filter in the session
        }


        if (isset($data->juros) AND ($data->juros)) {
            $filter = new TFilter('juros', 'like', "%{$data->juros}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_juros',   $filter); // stores the filter in the session
        }


        if (isset($data->multa) AND ($data->multa)) {
            $filter = new TFilter('multa', 'like', "%{$data->multa}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_multa',   $filter); // stores the filter in the session
        }


        if (isset($data->correcao) AND ($data->correcao)) {
            $filter = new TFilter('correcao', 'like', "%{$data->correcao}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_correcao',   $filter); // stores the filter in the session
        }


        if (isset($data->conta_fechamento_id) AND ($data->conta_fechamento_id)) {
            $filter = new TFilter('conta_fechamento_id', 'like', "%{$data->conta_fechamento_id}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_conta_fechamento_id',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_pagamento_id) AND ($data->tipo_pagamento_id)) {
            $filter = new TFilter('tipo_pagamento_id', 'like', "%{$data->tipo_pagamento_id}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_tipo_pagamento_id',   $filter); // stores the filter in the session
        }


        if (isset($data->numero_doc_pagamento) AND ($data->numero_doc_pagamento)) {
            $filter = new TFilter('numero_doc_pagamento', 'like', "%{$data->numero_doc_pagamento}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_numero_doc_pagamento',   $filter); // stores the filter in the session
        }


        if (isset($data->linha_digitavel) AND ($data->linha_digitavel)) {
            $filter = new TFilter('linha_digitavel', 'like', "%{$data->linha_digitavel}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_linha_digitavel',   $filter); // stores the filter in the session
        }


        if (isset($data->parcela) AND ($data->parcela)) {
            $filter = new TFilter('parcela', 'like', "%{$data->parcela}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_parcela',   $filter); // stores the filter in the session
        }


        if (isset($data->usuario_id) AND ($data->usuario_id)) {
            $filter = new TFilter('usuario_id', 'like', "%{$data->usuario_id}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_usuario_id',   $filter); // stores the filter in the session
        }


        if (isset($data->data_atualizacao) AND ($data->data_atualizacao)) {
            $filter = new TFilter('data_atualizacao', 'like', "%{$data->data_atualizacao}%"); // create the filter
            TSession::setValue('ContasPagarList_filter_data_atualizacao',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasPagar_filter_data', $data);
        
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
        $string = new StringsUtil;

        try
        {

            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasPagar
            $repository = new TRepository('ContasPagar');
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
            
            // somente um condomínio
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            if (TSession::getValue('ContasPagarList_filter_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_condominio_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_tipo_lancamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_tipo_lancamento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_documento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_documento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_dt_lancamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_dt_lancamento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_valor')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_valor')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_situacao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_situacao')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_dt_pagamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_dt_pagamento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_dt_liquidacao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_dt_liquidacao')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_valor_pago')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_valor_pago')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_desconto')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_desconto')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_juros')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_juros')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_multa')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_multa')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_correcao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_correcao')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_conta_fechamento_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_conta_fechamento_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_tipo_pagamento_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_tipo_pagamento_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_numero_doc_pagamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_numero_doc_pagamento')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_linha_digitavel')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_linha_digitavel')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_parcela')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_parcela')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_usuario_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_usuario_id')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_data_atualizacao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_data_atualizacao')); // add the session filter
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
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                    $conta = new ContaFechamento( $object->conta_fechamento_id );
                    $object->conta_fechamento_id = $conta->descricao;
                    
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
            $object = new ContasPagar($key, FALSE); // instantiates the Active Record
                        
            // verifica fechamento
            $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                      where('mes_ref', '=', $object->mes_ref)->load();
                        
            //default = 1 fechado, não permite nada
            $status = 1;
        
            foreach ($fechamentos as $fechamento)
            {
                $status = $fechamento->status;
            }
                        
                        
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                TTransaction::close(); // close the transaction
                return;
            }
            ////////////////////////////////////
                          
            if ( $object->situacao == '1') {
                new TMessage('info', 'Título já baixado, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
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
