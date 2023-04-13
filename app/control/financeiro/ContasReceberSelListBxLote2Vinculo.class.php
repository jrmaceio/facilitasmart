<?php
/**
 *
 */
class ContasReceberSelListBxLote2Vinculo extends TStandardList
{
    protected $form;     // search form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
        parent::setDatabase('facilitasmart');            // defines the database
        parent::setActiveRecord('ContasReceber');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        // parent::setCriteria($criteria) // define a standard filter

        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        //parent::addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        //parent::addFilterField('cobranca', '=', 'cobranca'); // filterField, operator, formField
        //parent::addFilterField('tipo_lancamento', '=', 'tipo_lancamento'); // filterField, operator, formField
        parent::addFilterField('unidade_id', 'like', 'unidade_id'); // filterField, operator, formField
        parent::addFilterField('unidade_desc', 'like', 'unidade_desc'); // filterField, operator, formField
        //parent::addFilterField('dt_vencimento', '=', 'dt_vencimento'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ContasReceberListBxLote2');
        $this->form->setFormTitle('Contas Receber - Baixa Títulos por Lote');
        
        
        /// fim controle situacao

        // create the form fields
        $id = new TEntry('id');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        $mes_ref = new TEntry('mes_ref');
        
        //$cobranca = new TEntry('cobranca');
        //$tipo_lancamento = new TEntry('tipo_lancamento');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 'descricao','descricao',$criteria);

        $pessoa_id = new TDBCombo('pessoa_id', 'facilitasmart', 'Pessoa', 'id', 'nome','nome',$criteria);

               
        $dt_baixa = new TDate('dt_baixa');
        $dt_baixa->setMask('dd/mm/yyyy');

        $dt_liquidacao = new TDate('dt_liquidacao');
        $dt_liquidacao->setMask('dd/mm/yyyy');

        $valor_lote = new TEntry('valor_lote');
        $valor_lote->setNumericMask(2, ',', '.');
        //$dt_baixa = TSession::getValue('data_baixa'); 
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao');
        
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Classe Id')], [$classe_id] );
        $this->form->addFields( [new TLabel('Mês Ref.')], [$mes_ref],
                                [new TLabel('Unidade Id')], [$unidade_id] );
        
        $this->form->addFields( [new TLabel('Pessoa')], [$pessoa_id] );

        $this->form->addContent([new TFormSeparator('Dados para Liquidação', '#333333', '18', '#eeeeee')]); 


        $multa = new TEntry('multa');
        $multa->setNumericMask(2, ',', '.');
        $juros = new TEntry('juros');
        $juros->setNumericMask(2, ',', '.');  
        $correcao = new TEntry('correcao');
        $correcao->setNumericMask(2, ',', '.');
        $desconto = new TEntry('desconto');
        $desconto->setNumericMask(2, ',', '.');
        
        $multa->setSize('100%');
        $juros->setSize('100%');
        $correcao->setSize('100%');
        $desconto->setSize('100%');
        
        $this->form->addFields( [new TLabel('Dt Baixa')], [$dt_baixa],
                                [new TLabel('Dt Liquidação')], [$dt_liquidacao],
                                [new TLabel('Valor Lote')], [$valor_lote]);
        
        $this->form->addFields( [new TLabel('Multa:')], [$multa],
                                 [new TLabel('Juros:')], [$juros],
                                 [new TLabel('Correção:')], [$correcao] );
        $this->form->addFields( [new TLabel('Desconto:')], [$desconto],
                                 [new TLabel('Conta Fechamento')], [$conta_fechamento_id] );  

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        //$id->setSize(50);
        
        $classe_id->setSize('50%');
        $mes_ref->setSize('100%');
        
        //$cobranca->setSize(50);
        //$tipo_lancamento->setSize(50);
        $unidade_id->setSize('50%');

        //$dt_vencimento->setSize('100%'');

        $dt_baixa->setSize('100%');
        $dt_liquidacao->setSize('100%');
        $valor_lote->setSize('100%');

       // create the form actions
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Conferência', new TAction(array($this, 'showResults')), 'fa:check-circle-o green' );
        $this->form->addAction( 'Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser yellow');
        $this->form->addAction( 'Baixar Lote', new TAction(array($this, 'onConfBaixar')), 'fa:check-circle-o green' );
        $this->form->addAction( 'Estornar Lote', new TAction(array($this, 'onConfEstorno')), 'fa:check-circle-o green' );
        $this->form->addAction( 'Sel./Des. Todos', new TAction(array($this, 'onSelecionarDesmarcarTodos')), 'fa:check-circle-o green' );
        
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        //$this->datagrid->enablePopover(_t('Abstract'), '<b>'._t('Description').'</b><br>' . '{description}' . '<br><b>'._t('Solution').'</b><br>' . '{solution}');
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'center');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'center');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'ID Unid', 'center');
        $column_unidade_desc = new TDataGridColumn('unidade_desc', 'Unidade', 'left');
        $column_morador = new TDataGridColumn('morador', 'Morador', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        //$column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situacao', 'center');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Dt Pag.', 'center');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'right');
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_unidade_desc);
        $this->datagrid->addColumn($column_morador);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        //$this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_valor_pago);
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_id->setTransformer(array($this, 'formatRow') );
        $column_valor->setTransformer( $format_value );
        $column_valor_pago->setTransformer( $format_value );
        
        // define totals
        $column_valor_pago->setTransformer(array($this, 'formatPagamento'));
       
        $column_valor->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });
        
        $action1 = new TDataGridAction(array($this, 'onSelect'));
        $action1->setButtonClass('btn btn-default btn-sm');
        //$action_delete->setLabel('Sel');
        $action1->setImage('fa:check-circle-o green');
        $action1->setField('id');
        $this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        // mostrar o mes ref e imovel selecionado
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
    
    public function onSelecionarDesmarcarTodos($param)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 600;
            //$limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('ContasReceberListagem_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_id')); // add the session filter
            }            

            if (TSession::getValue('ContasReceberListagem_filter_pessoa_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_pessoa_id')); // add the session filter
            } 
            
            if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_unidade_desc')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_desc')); // add the session filter
            }

            if (TSession::getValue('ContasReceberListagem_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_mes_ref')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
            }
            
            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            // get the selected objects from session 
            $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
   
                    $object = new ContasReceber($object->id); // load the object
   
                                            
                    if (isset($selected_objects[$object->id]))
                    {
                        unset($selected_objects[$object->id]);
                    }
                    else
                    {
                        //var_dump($object->id);
                        $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
                    }
                   
                    TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
                    
        // reload datagrids
        $this->onReload( func_get_arg(0) );    
                    
                    
                    


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
    
        ///////////////////////////////////////////////
        
      
        // reload datagrids
        $this->onReload( func_get_arg(0) );
        
        //$dt_baixa = TSession::getValue('data_baixa'); 
    }
    
    /**
     * Format pagamento
     */
    public function formatPagamento($stock, $object, $row)
    {
      if($stock) { 
        $numero = number_format($stock, 2, ',', '.');
                   
        if ($stock > 0)
        {
            return "<span style='color:blue'>$numero</span>";
        }
        else
        {
            $row->style = "background: #FFF9A7";
            return "<span style='color:red'>$numero</span>";
        }
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

        TSession::setValue('ContasReceberListagem_filter_id',   NULL);

        TSession::setValue('ContasReceberListagem_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_unidade_desc',   NULL);
        
        TSession::setValue('ContasReceberListagem_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberListagem_filter_classe_id',   NULL);
        
        TSession::setValue('ContasReceberListagem_filter_pessoa_id',   NULL);
        
        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_id',   $filter); // stores the filter in the session
        }

        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "{$data->mes_ref}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_mes_ref',   $filter); // stores the filter in the session
        }
        
        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', '=', "{$data->classe_id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_classe_id',   $filter); // stores the filter in the session
        }
        
        if (isset($data->pessoa_id) AND ($data->pessoa_id)) {
            TTransaction::open('facilitasmart');  
            $criteriaPes = new TCriteria;
            $criteriaPes->add(new TFilter('proprietario_id', '=', "{$data->pessoa_id}") );
            $repositoryPes = new TRepository('Unidade');
        
            $unid = $repositoryPes->load($criteriaPes);
        
            $TodasUnidades[] = '';
            
            foreach ($unid as $row)
            {
                $TodasUnidades[] = $row->id;
            }
        
            $filter = new TFilter('unidade_id', 'IN', ($TodasUnidades)); // create the filter
            TSession::setValue('ContasReceberListagem_filter_pessoa_id',   $filter); // stores the filter in the session
            
            TTransaction::close();
        }
        
        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_unidade_id',   $filter); // stores the filter in the session
        }
        
               
                
        // fill the form with data again
        $this->form->setData($data); 
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data', $data);
        
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
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            //$limit = 24; // 2 anos
            $limit = 600;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('ContasReceberListagem_filter_pessoa_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_pessoa_id')); // add the session filter
            } 
            
            if (TSession::getValue('ContasReceberListagem_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_id')); // add the session filter
            }            

            if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); //dd the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_unidade_desc')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_desc')); // add the session filter
            }

            if (TSession::getValue('ContasReceberListagem_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_mes_ref')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
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
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->dt_pagamento ? $object->dt_pagamento = $this->string->formatDateBR($object->dt_pagamento) : null;
                    //$object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    //$object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                    
                    switch ($object->situacao)
                    {
                    case '0':
                        $object->situacao = 'aberto';
                        break;
                    case '1':
                        $object->situacao = 'pago';
                        break;
                    case '2':
                        $object->situacao = 'acordo';
                        break;
                    }
                    
                    $classificacao = new PlanoContas($object->classe_id);
                    $object->classe_id = substr($classificacao->descricao,0,25);
                    
                    $unidade = new Unidade($object->unidade_id);
                    $object->unidade_desc = $unidade->descricao;
                    
                    $conn = TTransaction::get();
                    $result = $conn->query("select b.nome from unidade as a
                                inner join pessoa as b 
                                on a.proprietario_id =  b.id
                                where a.id = {$object->unidade_id}");
                                
                    $proprietario ='';
                    
                    foreach ($result as $row)
                    {
                        $proprietario = substr(utf8_decode($row['nome']),0,25);
                        //$proprietario = substr($row['nome'],0,25);
                    }
                    
                    $object->morador = $proprietario;

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
     * Save the object reference in session
     */
    public function onSelect($param)
    {
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        TTransaction::open('facilitasmart');
        $object = new ContasReceber($param['key']); // load the object
        if (isset($selected_objects[$object->id]))
        {
            unset($selected_objects[$object->id]);
        }
        else
        {
            $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
        }
        TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
        TTransaction::close();
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
        
        //$dt_baixa = TSession::getValue('data_baixa'); 
    }
    
    /**
     * Highlight the selected rows
     */
    public function formatRow($value, $object, $row)
    {
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        if ($selected_objects)
        {
            if (in_array( (int) $value, array_keys( $selected_objects ) ) )
            {
                $row->style = "background: #FFD965";
            }
        }
        
        return $value;
    }
    
    /**
     * Show selected records
     */
    public function showResults()
    {
        $datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        
        $datagrid->addQuickColumn('Id', 'id', 'center');
        $datagrid->addQuickColumn('Nome', 'descricao', 'center');
        $datagrid->addQuickColumn('Unidade', 'unidade_id', 'center');
        $datagrid->addQuickColumn('Mes Ref', 'mes_ref', 'center');
        $datagrid->addQuickColumn('Vencimento', 'dt_vencimento', 'center');
        $datagrid->addQuickColumn('Valor', 'valor', 'center');
        $datagrid->addQuickColumn('Classe', 'classe_id', 'center');
        $datagrid->addQuickColumn('Situação', 'situacao', 'center');
       
        // create the datagrid model
        $datagrid->createModel();
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        
         
        $total = 0;
             
        if ($selected_objects)
        {
            $datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                
                // proprietario
                TTransaction::open('facilitasmart');
                $unidade = $selected_object['unidade_id'];
                
                //var_dump($selected_object);
                
                $conn = TTransaction::get();
                
                if (!$unidade) {
                    TTransaction::close();
                    new TMessage('info', 'Problema na seleção de registros!');
                    return;
                }
                
                $result = $conn->query("select b.nome from unidade as a
                                inner join pessoa as b 
                                on a.proprietario_id =  b.id
                                where a.id = {$unidade}");
                                
                
                $proprietario ='';
 
        
                foreach ($result as $row)
                {
                    $proprietario = $row['nome'];
                }
                
                // descricao da unidades
                $conn = TTransaction::get();
                $result = $conn->query("select descricao 
                                from unidade 
                                where id = {$unidade}");
      
                $descricao ='';
        
                foreach ($result as $row)
                {    
                    $descricao = $row['descricao'];
                }
                
                // classe
                $classe = $selected_object['classe_id'];
                $conn = TTransaction::get();
                $result = $conn->query("select descricao 
                                from plano_contas
                                where id = {$classe}");
      
                $classe ='';
        
                foreach ($result as $row)
                {    
                    $classe = $row['descricao'];
                }
                
                TTransaction::close();
                
                $selected_object['unidade_id'] = $descricao;
                $selected_object['classe_id'] = $classe;
                $selected_object['descricao'] = $proprietario;
                $selected_object['valor'] = number_format($selected_object['valor'], 2, ',', '.');
                $selected_object['dt_vencimento'] = $this->string->formatDateBR($selected_object['dt_vencimento']);
                
                
                $datagrid->addItem( (object) $selected_object );
                
                //$total += number_format($selected_object['valor'], 2, ',', '.');
            }
        }
        
        //$selected_object['id'] = '';
        //$selected_object['unidade_id'] = '';
        //$selected_object['mes_ref'] = '';
        //$selected_object['situacao'] = '';
        //$selected_object['classe_id'] = '';
        //$selected_object['descricao'] = 'Total';
        //$selected_object['valor'] = $total;
        //$selected_object['dt_vencimento'] = '';
        //$datagrid->addItem( (object) $selected_object );
             
        $win = TWindow::create('Baixar Lote Selecionado', 0.7, 0.7);
        $win->add($datagrid);
        
        //$i= 1;
        //$panel = new TPanelGroup('Total Baixado');
        //$panel->addFooter('records shown', "<b>{$i}</b>");
     
        //$button1=new TButton('action1');
        //$button1->setAction(new TAction(array($this, 'onBaixar')), 'Confirmar');
        //$button1->setImage('ico_save.png'); 
       // $panel->put($button1, 200,500 ); 

         
        //$win->add($button1);
        
        //$action1  = new TAction(array($this, 'confirm'));
         //   $action1->setParameter('data', json_encode($data));
            
         //   $question = new TQuestion('Confirma ?', $action1); 
        
        
        
        $win->show();
 
         ///TApplication::loadPage('MultiCheck2View');
         
        //$action1 = new TAction(array($this, 'onAction1'));
        //$action2 = new TAction(array($this, 'onReload'));
        //new TQuestion('Do you really want to perform this operation ?', $action1, $action2);
        
        /////// do form ---------------, $this->container->addRow()->addCell($panel);
    }
    
    public function onBaixar($param)
    {
        $string = new StringsUtil;
        
        if (!$param['dt_baixa']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a data da baixa!'); // shows the exception error message
            return;
        }

        if (!$param['dt_liquidacao']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a data da liquidação (Data do Crédito)!'); // shows the exception error message
            return;
        }
    
        if (!$param['conta_fechamento_id']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a conta de recebimento !'); // shows the exception error message
            return;
        }
        
        $param['dt_baixa'] = $string->formatDate($param['dt_baixa']);
        $param['dt_liquidacao'] = $string->formatDate($param['dt_liquidacao']);
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        
               
        // verifica se o lote tem o mesmo valor do informado pelo usuário
        if ($selected_objects)
        {
            $soma_lote = 0;
            $conta_titulos = 0;
            
            foreach ($selected_objects as $selected_object)
            {
              try
                {
            
                TTransaction::open('facilitasmart'); // open a transaction with database
                
                //var_dump($selected_object['id']);          
                $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                                                
                if ( $object->situacao == '0' )
                { 
                    $soma_lote += $object->valor;
                    $conta_titulos++;
                
                    $dataliquidacao = explode("-", $param['dt_liquidacao']);
                    $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $dataliquidacao[1].'/'.$dataliquidacao[0]);
            
                    if ( $status != 0 or $status == ''){
                        new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data de liquidação !');
                        TTransaction::close(); // close the transaction
                        return;
                    }
                }
                        
                TTransaction::close(); // close the transaction
            
                }
              catch (Exception $e) // in case of exception
                {
                new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
                }
                
            }
            
        }
        
        //var_dump(number_format($soma_lote, 2, ',', '.'));
        //var_dump($param['valor_lote']);
        //var_dump($param['desconto']);
   
        $diverge_lote_pago = false;
        if (number_format($soma_lote, 2, ',', '.') != $param['valor_lote']) {
            new TMessage('info', 'O lote selecionado ['.$conta_titulos.' título(s)] tem valor divergente do total informado, será considerado o valor com desconto.');
            
            if ((number_format($soma_lote, 2, ',', '.') - $param['desconto']) != $param['valor_lote']) {
              new TMessage('info', 'A soma do lote, menos o desconto, não é igual ao valor pago! Baixa cancelada');
              return;
            }
            
            $diverge_lote_pago = true;  
        
        }
      
        ///////////////
                
        if ($selected_objects)
        {
            //$datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                
                try
                {
            
                TTransaction::open('facilitasmart'); // open a transaction with database

                // verifica se a conta de fechamento é realmente do condominio selecionado
                $contafechamento = new ContaFechamento($param['conta_fechamento_id']);

                if ($contafechamento->condominio_id != TSession::getValue('id_condominio')) {
                  new TMessage('info', 'Divergência entre a conta de fechamento selecionada e o Condomínio atual! Baixa Cancelada');
                  return;      
                } 
                           
                $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                
                if ( $object->situacao == '0' )
                {
                    $object->situacao = '1';
                    $object->dt_pagamento = $param['dt_baixa']; 
                    $object->dt_liquidacao = $param['dt_liquidacao']; 
                    
                    $param['desconto'] = str_replace(",", ".", $param['desconto']);
                    $desconto_individual = $param['desconto'] / $conta_titulos;
                    
                    $object->valor_pago = $object->valor - $desconto_individual;
                    $object->valor_creditado = $object->valor - $desconto_individual;
                    
                    $object->desconto = $desconto_individual;
                    
                    $param['juros'] = str_replace(",", ".", $param['juros']);
                    $object->juros = $param['juros'];
                    $param['multa'] = str_replace(",", ".", $param['multa']);
                    $object->multa = $param['multa'];
                    $param['correcao'] = str_replace(",", ".", $param['correcao']);
                    $object->correcao = $param['correcao'];
                    
                    $object->conta_fechamento_id = $param['conta_fechamento_id'];
                    
                    $object->dt_ultima_alteracao = date('Y-m-d');
                    //$object->usuario_id =  TSession::getValue('login');
                 
                    // verifica se existe fechamento aberto possivel de edicao
                    $databaixa = explode("-", $param['dt_baixa']);
                    $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $databaixa[1].'/'.$databaixa[0]);
            
                    //var_dump($databaixa);
                    //var_dump($status);
                    if ( $status != 0 or $status == ''){
                        new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                    }else {
                        $object->store(); // update the object in the database
                        
                    }
                
                                      
                    // desarcar objeto
                    // get the selected objects from session 
                    $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
                    if (isset($selected_objects[$object->id]))
                    {
                        unset($selected_objects[$object->id]);
                    }
                    else
                    {
                        $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
                    }
                    
                    TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
        
                }
                 
                             
                TTransaction::close(); // close the transaction
            
                new TMessage('info', 'Baixa concluída!'); // success message 
                
                //$datagrid->addItem( (object) $selected_object );

                }
                catch (Exception $e) // in case of exception
                {
                new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
                }
                
            }
            
            $param['dt_baixa'] = $string->formatDateBR($param['dt_baixa']);
            
            // atualiza o grid apos desmarcar o titulo baixado
            // reload datagrids
            $this->onReload( func_get_arg(0) );
        }
        
        //$win = TWindow::create('Results', 0.6, 0.6);
        //$win->add($datagrid);
        //$win->show();
        
        // grava a data da baixa para reaproveitar
        //TSession::setValue('data_baixa', $param['dt_baixa']);
    }
    
    public function onEstornar($param)
    {
        $string = new StringsUtil;
        
        $param['dt_baixa'] = $string->formatDate($param['dt_baixa']);
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        
        if ($selected_objects)
        {
            foreach ($selected_objects as $selected_object)
            {
                try
                {
                    TTransaction::open('facilitasmart'); // open a transaction with database
                             
                    $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                        
                    if ($object->situacao != '1' ) 
                    {
                        new TMessage('error', '<b>Error</b> ' . 'Título sub-júdice ou em acordo, o estorno não é possível.'); // shows the exception error message
                        TTransaction::close(); // close the transaction
                        return;
                     
                    }else if ( $object->situacao == '1' ) 
                    {
                        // verifica se existe fechamento aberto possivel de edicao
                        $databaixa = explode("-", $object->dt_pagamento);
                        $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $databaixa[1].'/'.$databaixa[0]);
            
                        //var_dump($object->dt_pagamento);
                        //var_dump($status);
                        if ( $status != 0 or $status == '')
                        {
                            new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                        }else 
                        {
                            $object->situacao = '0';
                            $object->dt_pagamento = '';
                            $object->dt_liquidacao = '';
                            $object->valor_pago = 0;
                            $object->desconto = 0;
                            $object->juros = 0;
                            $object->multa = 0;
                            $object->correcao = 0;
                            $object->conta_fechamento_id = '';
                
                            $object->dt_ultima_alteracao = date('Y-m-d');
                            //$object->usuario_id =  TSession::getValue('login');
                            $object->store(); // update the object in the database
                            new TMessage('info', 'Estorno concluído!'); // success message 
                            
                            // desarcar objeto
                            // get the selected objects from session 
                            $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
                            
                            if (isset($selected_objects[$object->id]))
                            {
                                unset($selected_objects[$object->id]);
                            } else
                            {
                               $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
                            }
                    
                            TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
        
                         }
      
                       }
                       
                       TTransaction::close(); // close the transaction
                        
                    }
                    catch (Exception $e) // in case of exception
                    {
                        new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
                        TTransaction::rollback(); // undo all pending operations
                    }
                                               
                }
                
                $param['dt_baixa'] = $string->formatDateBR($param['dt_baixa']);
                
                // atualiza o grid apos desmarcar o titulo baixado
                // reload datagrids
                $this->onReload( func_get_arg(0) );
                
         }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $selected_objects = array(0);
        TSession::setValue(__CLASS__.'_selected_objects', $selected_objects);
        $this->form->clear();
        $this->onReload($param);
    } 
    
    public function onBaixa($param)
    {
                    
        // get the search form data
        $data = $this->form->getData();

        var_dump($param);
        var_dump($data);
            
        if (!$param['dt_baixa']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a data da baixa!'); // shows the exception error message
            return;
        }
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction with database
                             
            $object = new ContasReceber($param['id']); // instantiates the Active Record
                
            if ( $object->situacao == '0' )
            {
                $object->situacao = '1';
                $object->dt_pagamento = $data->dt_baixa;
                $object->dt_liquidacao = $data->dt_baixa; 
                $object->valor_pago = $object->valor;
                $object->desconto = 0;
                $object->juros = 0;
                $object->multa = 0;
                $object->correcao = 0;
                
                $object->dt_ultima_alteracao = date('Y-m-d');
                //$object->usuario_id =  TSession::getValue('login');
                 
                $object->store(); // update the object in the database
                       
            }
                 
                             
            TTransaction::close(); // close the transaction
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
                
        new TMessage('info', 'Baixa concluída!'); // success message 
            
            // atualiza o grid apos desmarcar o titulo baixado
            // reload datagrids
            $this->onReload( func_get_arg(0) );
     
    }
    
    public function onConfEstorno($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onEstornar'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Confirma o Estorno do(s) título(s) selecionado(s) ?', $action);
    }   
    
    public function onConfBaixar($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onBaixar'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Confirma a Baixa do(s) título(s) selecionado(s) ?', $action);
    }   


}
