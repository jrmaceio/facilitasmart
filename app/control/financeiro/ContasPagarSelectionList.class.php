<?php
/**
 * ContasPagarSelectionList Record selection
 * @author  <your name here>
 */
  
class ContasPagarSelectionList extends TStandardList
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
        parent::setActiveRecord('ContasPagar');   // defines the active record
        parent::setDefaultOrder('id', 'desc');         // defines the default order
        
        // filtros obrigatorios
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        parent::setCriteria($criteria); // define a standard filter

        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('mes_ref', '=', 'mes_ref'); // filterField, operator, formField
        //parent::addFilterField('tipo_lancamento', '=', 'tipo_lancamento'); // filterField, operator, formField
        parent::addFilterField('descricao', 'like', 'descricao'); // filterField, operator, formField
        parent::addFilterField('dt_pagamento', 'like', 'dt_pagamento'); // filterField, operator, formField
        parent::addFilterField('dt_liquidacao', 'like', 'dt_liquidacao'); // filterField, operator, formField
        //parent::addFilterField('tipo_pagamento_id', 'like', 'tipo_pagamento_id'); // filterField, operator, formField
        parent::addFilterField('numero_doc_pagamento', '=', 'numero_doc_pagamento'); // filterField, operator, formField
        parent::addFilterField('documento', '=', 'documento'); // filterField, operator, formField
        parent::setLimit(60);
        
        $this->form = new BootstrapFormBuilder('form_search_ContasPagar');
        $this->form->setFormTitle('Baixar Despesas em Lote');

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');
        $documento = new TEntry('documento');
        $descricao = new TEntry('descricao');
        
        $dt_pagamento = new TDate('dt_pagamento');
               
        $dt_liquidacao = new TDate('dt_liquidacao');
        
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');

        $dt_baixa = new TDate('dt_baixa');

        $valor_lote = new TEntry('valor_lote');

        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        $tipo_pagamento_id = new TDBCombo('tipo_pagamento_id', 'facilitasmart', 'TipoPagamento', 'id', '{id}-{descricao}','descricao');
       
        $dt_baixa->setSize('50%');
        $valor_lote->setSize('50%');
        
        $dt_baixa->setMask('dd/mm/yyyy');
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_pagamento->setDatabaseMask('yyyy-mm-dd');
        $dt_liquidacao->setMask('dd/mm/yyyy');
        $dt_liquidacao->setDatabaseMask('yyyy-mm-dd');
        
        $valor_lote->setNumericMask(2, ',', '.');
                
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Mes Ref')], [$mes_ref], [new TLabel('Documento')], [$documento] );
        $this->form->addFields( [new TLabel('Descrição')], [$descricao], [new TLabel('Dt Pagamento')], [$dt_pagamento], [new TLabel('Dt Liquidacao')], [$dt_liquidacao] );
        $this->form->addContent([new TFormSeparator('Dados para Liquidação', '#333333', '18', '#eeeeee')]); 
        $this->form->addFields( [new TLabel('Tipo Pagamento')], [$tipo_pagamento_id], [new TLabel('Doc Pagamento')], [$numero_doc_pagamento] );
        $this->form->addFields( [new TLabel('Data baixa')], [$dt_baixa], [new TLabel('Valor do Lote')], [$valor_lote] );
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id] ); 
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasPagar_filter_data') );
        
       
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( 'Conferência', new TAction(array($this, 'showResults')), 'fa:check-circle-o green');
        $this->form->addAction( 'Limpar', new TAction(array($this, 'onClear')), 'fa:eraser yellow' );
        $this->form->addAction( 'Baixar Lote', new TAction(array($this, 'onConfBaixar')), 'fa:check-circle-o green');
        $this->form->addAction( 'Estornar Lote', new TAction(array($this, 'onConfEstorno')), 'fa:check-circle-o red' );
        $this->form->addAction( 'Sel./Des. Todos', new TAction(array($this, 'onSelecionarDesmarcarTodos')), 'fa:check-circle-o green' );
        $this->form->addAction( 'Import Csv', new TAction(array($this, 'onImportCSV')), 'fa:check-circle-o green' );
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        $this->datagrid->enablePopover('Informação', '<b>'.'Descrição'.'</b><br>' . '{descricao}' . '<br><b>'.'Documento Pagamento'.'</b><br>' . '{numero_doc_pagamento}');
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Liquidação', 'right');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Vlr Pago', 'right');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_dt_liquidacao);
        $this->datagrid->addColumn($column_valor_pago);
        
        $column_classe_id->setTransformer(function($value, $object, $row) {
            $classificacao = new PlanoContas($value);
            return substr($classificacao->descricao,0,25);
        });

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_dt_liquidacao->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_id->setTransformer(array($this, 'formatRow') );
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_valor->setTransformer( $format_value );
        $column_valor_pago->setTransformer( $format_value );
        
        // define totals
        $column_valor_pago->setTransformer(array($this, 'formatPagamento'));
       
        $column_valor->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });
        
        // creates the datagrid actions
        $action1 = new TDataGridAction(array($this, 'onSelect'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        $action1->setLabel(AdiantiCoreTranslator::translate('Select'));
        $action1->setImage('fa:check-circle-o blue');
        $action1->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        // add the vbox inside the page
        
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
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        // get the search form data
        $data = $this->form->getData();
        
        TTransaction::open('facilitasmart');
        $object = new ContasPagar($param['key']); // load the object
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
    }
    
    
    /**
     * Save the object reference in session
     */
    public function onSelect($param)
    {
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        TTransaction::open('facilitasmart');
        $object = new ContasPagar($param['key']); // load the object
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
        
        $datagrid->addQuickColumn('Id', 'id', 'right');
        $datagrid->addQuickColumn('Condominio Id', 'condominio_id', 'right');
        $datagrid->addQuickColumn('Mes Ref', 'mes_ref', 'left');
        $datagrid->addQuickColumn('Tipo Lancamento', 'tipo_lancamento', 'left');
        $datagrid->addQuickColumn('Classe Id', 'classe_id', 'right');
        $datagrid->addQuickColumn('Documento', 'documento', 'left');
        $datagrid->addQuickColumn('Dt Lancamento', 'dt_lancamento', 'left');
        $datagrid->addQuickColumn('Dt Vencimento', 'dt_vencimento', 'left');
        $datagrid->addQuickColumn('Valor', 'valor', 'left');
        $datagrid->addQuickColumn('Descricao', 'descricao', 'left');
        $datagrid->addQuickColumn('Situacao', 'situacao', 'left');
        $datagrid->addQuickColumn('Dt Pagamento', 'dt_pagamento', 'left');
        $datagrid->addQuickColumn('Dt Liquidacao', 'dt_liquidacao', 'left');
        $datagrid->addQuickColumn('Valor Pago', 'valor_pago', 'left');
        $datagrid->addQuickColumn('Desconto', 'desconto', 'left');
        //$datagrid->addQuickColumn('Juros', 'juros', 'left');
        //$datagrid->addQuickColumn('Multa', 'multa', 'left');
        //$datagrid->addQuickColumn('Correcao', 'correcao', 'left');
        //$datagrid->addQuickColumn('Conta Fechamento Id', 'conta_fechamento_id', 'right');
        //$datagrid->addQuickColumn('Tipo Pagamento Id', 'tipo_pagamento_id', 'right');
        //$datagrid->addQuickColumn('Numero Doc Pagamento', 'numero_doc_pagamento', 'left');
        //$datagrid->addQuickColumn('Linha Digitavel', 'linha_digitavel', 'left');
        //$datagrid->addQuickColumn('Parcela', 'parcela', 'right');
        //$datagrid->addQuickColumn('Usuario Id', 'usuario_id', 'right');
        //$datagrid->addQuickColumn('Data Atualizacao', 'data_atualizacao', 'left');
        
        // create the datagrid model
        $datagrid->createModel();
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        if ($selected_objects)
        {
            $datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                $datagrid->addItem( (object) $selected_object );
            }
        }
        
        $win = TWindow::create('Results', 0.6, 0.6);
        $win->add($datagrid);
        $win->show();
    }
    
    function onImportCSV()
    {
        
        $data = $this->form->getData();
        if($data->arq_plan == 'Nenhum arquivo selecionado' or $data->arq_plan == '')
        {
            new TMessage('error', 'Favor selecionar arquivo antes de importar.');
        } else {
            $csv = new TReadCsv('tmp/'.$data->arq_plan);
            $conteudo = $csv->abre();
            
            //seta conexão
            $dbconn = TConnection::open('changeman');
            
            //Inicializa a instrução de update
            $cont = 0;
            foreach ($conteudo as $dados)
            {
                $cont++;
                
                //if ($cont < 2) {
                   
                if($dados[0] != 'PRODUTO')
                {
                    $peso = 0;
                    if ($dados[10] != '') {
                        $peso = str_replace(',', '.', $dados[10]);
                    }
                    
                    $mssql = "update ficha_tecnica set 
                               descricao_longa = '{$dados[3]}',
                               tamanho_base = '{$dados[9]}',
                               peso = ".$peso." ";
                    
                    $mssql .= "where produto = '{$dados[0]}'";
                    
                    $result = $dbconn->exec($mssql);
                }
                
                //}//fim if
             
            }
            
            new TMessage('info', 'Arquivo importado com sucesso!');
        }
        
    } ?>

    public function onBaixar($param)
    {
        $string = new StringsUtil;
        
        if (!$param['dt_baixa']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a data da baixa !'); // shows the exception error message
            return;
        }
        
        if (!$param['conta_fechamento_id']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a conta do pagamento !'); // shows the exception error message
            return;
        }
    
        if (!$param['numero_doc_pagamento']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha o número do documento de pagamento !'); // shows the exception error message
            return;
        }
        
        if (!$param['tipo_pagamento_id']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha o tipo de pagamento !'); // shows the exception error message
            return;
        }
        
        $param['dt_baixa'] = $string->formatDate($param['dt_baixa']);
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
               
        // verifica se o lote tem o mesmo valor do informado pelo usuário
        if ($selected_objects)
        {
            $soma_lote = 0;
            
            foreach ($selected_objects as $selected_object)
            {
                try
                {
            
                    TTransaction::open('facilitasmart'); // open a transaction with database
                    
                    //var_dump($selected_objects);
                    //var_dump($selected_object);
                    //var_dump($selected_object['id']);
                    //return;
                              
                    $object = new ContasPagar($selected_object['id']); // instantiates the Active Record
                    
                    //var_dump($object);
                    //return;
                       
                    if ( $object->situacao == '1' )
                    { 
                        new TMessage('info', 'Lançamento já baixado, baixa cancelada !');
                        TTransaction::close(); // close the transaction
                        return;
                    }
                        
                                                 
                    if ( $object->situacao == '0' )
                    { 
                        $soma_lote += $object->valor;
                
                        $databaixa = explode("-", $param['dt_baixa']);
                        
                        //$status = ContasPagar::retornaStatusFechamento($object->condominio_id, $databaixa[1].'/'.$databaixa[0]);
                        // confirmação se pode fazer o lancamento / edicao / exclusao
                        // status 0 -> fechamento aberto possibilidade de fazer alterações
                        // status 1 -> fechado, nao altera nada
                        
                        $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                                   where('mes_ref', '=', $databaixa[1].'/'.$databaixa[0])->
                                                   where('conta_fechamento_id', '=', $param['conta_fechamento_id'])->load(); 
                        
                        //default = 1 fechado, não permite nada
                        $status = 1;
        
                        foreach ($fechamentos as $fechamento)
                        {
                            $status = $fechamento->status;
                        }
                        
                        
                        if ( $status != 0 or $status == ''){
                            new TMessage('info','Não existe um fechamento em aberto com o Mês Ref da data baixa !');
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
        
        if (number_format($soma_lote, 2, ',', '.') != $param['valor_lote']) {
            new TMessage('info', 'O lote selecionado tem valor divergente do total informado, verifique se existe acordo ! Baixa Cancelada.');
            return;  
        
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
                           
                    $object = new ContasPagar($selected_object['id']); // instantiates the Active Record
                
                    if ( $object->situacao == '0' )
                    {
                        $object->situacao = '1';
                        
                        if (!$object->dt_pagamento) {
                            $object->dt_pagamento = $param['dt_baixa'];
                        }
                        
                        $object->dt_liquidacao = $param['dt_baixa']; 
                        $object->valor_pago = $object->valor;
                        
                        $object->numero_doc_pagamento = $param['numero_doc_pagamento'];
                        
                        $object->conta_fechamento_id = $param['conta_fechamento_id'];
                        
                        $object->tipo_pagamento_id = $param['tipo_pagamento_id'];
                        
                        $object->desconto = 0;
                        $object->juros = 0;
                        $object->multa = 0;
                        $object->correcao = 0;
                
                        $object->dt_ultima_alteracao = date('Y-m-d');
                        //$object->usuario_id =  TSession::getValue('login');
                 
                        // verifica se existe fechamento aberto possivel de edicao
                        $databaixa = explode("-", $param['dt_baixa']);
                        
                        //$status = ContasPagar::retornaStatusFechamento($object->condominio_id, $databaixa[1].'/'.$databaixa[0]);
                        // confirmação se pode fazer o lancamento / edicao / exclusao
                        // status 0 -> fechamento aberto possibilidade de fazer alterações
                        // status 1 -> fechado, nao altera nada
                        
                        $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                                   where('mes_ref', '=', $databaixa[1].'/'.$databaixa[0])->
                                                   where('conta_fechamento_id', '=', $object->conta_fechamento_id)->load(); 
                        
                        //default = 1 fechado, não permite nada
                        $status = 1;
        
                        foreach ($fechamentos as $fechamento)
                        {
                            $status = $fechamento->status;
                            $conta_fecha = $fechamento->conta_fechamento_id;
                        }
                        
                        // verifica se a conta fechamento escolhida é do mesmo condominio
                        $verifica_conta = true;
                        if ( $object->conta_fechamento_id == $conta_fecha ) {
                          $verifica_conta = false;    
                        }

                        //var_dump($databaixa);
                        //var_dump($status);
                        if ( $status != 0 or $status == '' or $verifica_conta ){
                            new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa ou conta fechamento divergente !');
                        }else {
                            $object->store(); // update the object in the database
                            new TMessage('info', 'Baixa concluída!'); // success message 
                        
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
                             
                    $object = new ContasPagar($selected_object['id']); // instantiates the Active Record
                    
                    if ( $object->situacao == '0' )
                    { 
                        new TMessage('info', 'Lançamento em aberto, estorno cancelado !');
                        TTransaction::close(); // close the transaction
                        return;
                    }
                       
                    if ($object->situacao == '2' ) 
                    {
                        new TMessage('error', '<b>Error</b> ' . 'Título sub-júdice ou em acordo, o estorno não é possível.'); // shows the exception error message
                        TTransaction::close(); // close the transaction
                        return;
                     
                    }
                     
                    if ($object->situacao == '1' ) {
                        // verifica se existe fechamento aberto possivel de edicao
                        $databaixa = explode("-", $object->dt_liquidacao);
                        
                        // confirmação se pode fazer o lancamento / edicao / exclusao
                        // status 0 -> fechamento aberto possibilidade de fazer alterações
                        // status 1 -> fechado, nao altera nada
                        
                        $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                              where('mes_ref', '=', $databaixa[1].'/'.$databaixa[0])->load(); 
                        
                        //default = 1 fechado, não permite nada
                        $status = 1;
        
                        foreach ($fechamentos as $fechamento)
                        {
                            $status = $fechamento->status;
                        }
            
                        //var_dump($object->dt_pagamento);
                        //var_dump($status);
                        
                        if ( $status != 0 or $status == '')
                        {
                            new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                        }else 
                        {
                            $object->situacao = '0';
                            
                            if ( $object->dt_pagamento == $object->dt_liquidacao ) { 
                                $object->dt_pagamento = '';
                            
                            }
                            
                            $object->dt_liquidacao = '';
                            $object->valor_pago = 0;
                            $object->desconto = 0;
                            $object->juros = 0;
                            $object->multa = 0;
                            $object->correcao = 0;
                            $object->conta_fechamento_id = '';
                            $object->tipo_pagamento_id = '';
                
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
                             
            $object = new ContasPagar($param['id']); // instantiates the Active Record
                
            if ( $object->situacao == '0' )
            {
                $object->situacao = '1';
                $object->dt_pagamento = $data->dt_baixa; 
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
