<?php
/**
 * Criar Acordo
 * ContasReceberCriarAcordo Record selection
 * @author  <your name here>
 */
class ContasReceberCriarAcordo extends TStandardList
{
    protected $form; // form
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
        parent::addFilterField('unidade_id', '=', 'unidade_id'); // filterField, operator, formField
        parent::addFilterField('classe_id', '=', 'classe_id'); // filterField, operator, formField
       
        // creates the form
        $this->form = new BootstrapFormBuilder('list_CriaAcordo');

        // define the form title
        $this->form->setFormTitle('Criar Acordos');

        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        
        //$unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}', 'descricao', $criteria);
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);


        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $desconto = new TEntry('desconto');
        $correcao = new TEntry('correcao');
        $acrescimo = new TEntry('acrescimo');
        
        $valor_lancado = new TEntry('valor_lancado');
        $valor_projetado = new TEntry('valor_projetado');
        $valor_parcela = new TEntry('valor_parcela');
        $parcelas = new TEntry('parcelas');
        $dt_acordo = new TDate('dt_acordo');
        
        $dt_acordo->setMask('dd/mm/yyyy');
        $dt_acordo->setDatabaseMask('yyyy-mm-dd');
        
        $valor_parcela->style="text-align: right";
        $parcelas->style="text-align: right";
        $dt_acordo->style="text-align: right";

        $unidade_id->setSize('100%');
        $classe_id->setSize('100%');
        
        $multa->setSize(80);
        $juros->setSize(80);
        $desconto->setSize(80);
        $correcao->setSize(80);
        $acrescimo->setSize(80);
        
        $valor_lancado->setSize('72%');
        $valor_projetado->setSize('72%');
        $valor_parcela->setSize('72%');
        $parcelas->setSize('72%');
        $dt_acordo->setSize('72%');
        
        $valor_lancado->setEditable(FALSE);
        $valor_projetado->setEditable(FALSE);
        $valor_parcela->setEditable(FALSE);
        
        $multa->setNumericMask(2, ',', '.');
        $juros->setNumericMask(3, ',', '.');
        $desconto->setNumericMask(2, ',', '.');
        $correcao->setNumericMask(2, ',', '.');
        $acrescimo->setNumericMask(2, ',', '.');
        $valor_lancado->setNumericMask(2, ',', '.');
        $valor_projetado->setNumericMask(2, ',', '.');
        

        $this->form->addFields([new TLabel('Unidade:')],[$unidade_id],[new TLabel('Classe:')],[$classe_id]);

        //$this->form->addQuickFields('Unidade Id', array($unidade_id,
        //new TLabel('Unidade Desc.'),$unidade_desc, 
        //new TLabel('Proprietario'),$unidade_nome)
        //);
        
        $this->form->addFields([new TLabel('Multa')], [$multa], [new TLabel('Juros:')], [$juros], [], []);
        $this->form->addFields([new TLabel('Desconto')], [$desconto], [new TLabel('Correção:')], [$correcao],
        [new TLabel('Acréscimo')], [$acrescimo]);

        //$this->form->addQuickFields('Multa', array($multa,
        //new TLabel('Juros'),$juros, 
        //new TLabel('Desconto'),$desconto,
        //new TLabel('Correção'), $correcao,
        //new TLabel('Acréscimo'), $acrescimo)
        //);
        
        $this->form->addFields([new TLabel('Lançado')], [$valor_lancado], [new TLabel('Projetado:')],[$valor_projetado],
        [new TLabel('Parcela')], [$valor_parcela]);
        
        $this->form->addFields([new TLabel('Qtd. Parcelas:')], [$parcelas], [new TLabel('Data Acordo')], [$dt_acordo]);

        $this->form->addFields([new TLabel('Conta Fechamento')], [$conta_fechamento_id] );    


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );

        
        // create the form actions
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Calcular', new TAction(array($this, 'onCalcular')), 'fa:check-circle-o green');
        $this->form->addAction('Atualizar', new TAction(array($this, 'onAtualizar')), 'fa:check-circle-o green' );
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser yellow');
        $this->form->addAction('Gerar Acordo', new TAction(array($this, 'onGerar')), 'fa:check-circle-o green' );

        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'center');
        $column_cobranca = new TDataGridColumn('cobranca', 'Cob', 'center');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_dias = new TDataGridColumn('dias', 'Dias', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_multa = new TDataGridColumn('multa', 'Multa', 'center');
        $column_juros = new TDataGridColumn('juros', 'Juros', 'center');
        $column_proj  = new TDataGridColumn('proj', 'Vlr Proj', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_cobranca);
        //$this->datagrid->addColumn($column_tipo_lancamento);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_dias);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_multa);
        $this->datagrid->addColumn($column_juros);
        $this->datagrid->addColumn($column_proj);
        
        $column_id->setTransformer(array($this, 'formatRow') );
        
        $column_dias->setTransformer(array($this, 'formatDias'));

        $column_classe_id->setTransformer(function($value, $object, $row)
        {
            if($value)
            {
                // instantiates object
                $plano = new PlanoContas($value, FALSE); 
                return $plano->descricao;
            }

        });

        // creates the datagrid actions
        $action1 = new TDataGridAction(array($this, 'onSelect'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        //$action1->setLabel(AdiantiCoreTranslator::translate('Select'));
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
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);

        $panel->addFooter($this->pageNavigation);


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        parent::add($container);
    }
    
    public function formatDias($stock, $object, $row)
    {
        $number = number_format($stock, 0, ',', '.');
        if ($stock > 0)
        {
            return "<span style='color:blue'>$number</span>";
        }
        else
        {
            return "<span style='color:red'>$number</span>";
        }
    }
    
    
        /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // limpa os selecinados
        //////////////////$selected_objects = array(0);
        //////////////////////TSession::setValue(__CLASS__.'_selected_objects', $selected_objects);
        
        // clear session filters
        TSession::setValue('ContasReceberListagem_filter_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_situacao',   NULL);
        TSession::setValue('ContasReceberListagem_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_unidade_desc',   NULL);
        
        // filtros obrigatorios
        $filter = new TFilter('condominio_id', '=', TSession::getValue('id_condominio')); // create the filter
        TSession::setValue('ContasReceberListagem_filter_imovel_id',   $filter); // stores the filter in the session
        $filter = new TFilter('situacao', '=', "0"); // create the filter
        TSession::setValue('ContasReceberListagem_filter_situacao',   $filter); // stores the filter in the session
        
        
        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_id',   $filter); // stores the filter in the session
        }


       
        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_unidade_id',   $filter); // stores the filter in the session
        }
        
        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', '=', "{$data->classe_id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_classe_id',   $filter); // stores the filter in the session
        }
        
        
        if (isset($data->unidade_desc) AND ($data->unidade_desc)) {
            
            try
            {
                TTransaction::open('facilitasmart');
                $unidades = Unidades::getUnidadesDesc($data->unidade_desc);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                new TMessage('error', $e->getMessage());
            }
            
            $filter = new TFilter('unidade_id', 'IN', ($unidades)); // create the filter
            TSession::setValue('ContasReceberListagem_filter_unidade_desc',   $filter); // stores the filter in the session
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
            $limit = 24;
            
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
            

            if (TSession::getValue('ContasReceberListagem_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberListagem_filter_unidade_desc')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_desc')); // add the session filter
            }


            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', "0"));

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            
            // pega os dados do formulário
            $dados = $this->form->getData();
            
            if ( empty( $dados->dt_acordo ) ) {
                $dados->dt_acordo = date("Y/m/d");    
            }
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // pega data escolhida como data do acordo e converte para uso no calculo dos juros
                    $data_acordo = $this->string->formatDate($dados->dt_acordo);
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime($data_acordo);
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                                                           
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $juros = 0;
                        $object->dias  = '+'.abs($dias);
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $unidade = new Unidade( $object->unidade_id );
                    $object->unidade_id = $unidade->descricao;    

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                    
                    
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
    
    public function onCalcular($param)
    {
        $string = new StringsUtil;
        
        if (!$param['dt_acordo']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a data do acordo!'); // shows the exception error message
            return;
        }
        
        if (!$param['parcelas']) 
        {
            new TMessage('error', '<b>Error</b> ' . 'Preencha a quantidade de parcelas do acordo!'); // shows the exception error message
            return;
        }
    
        $param['dt_acordo'] = $string->formatDate($param['dt_acordo']);
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        
        if (empty($selected_objects)) 
            {
               new TMessage('info', 'Selecione os lançamentos para o acordo. ' . '<br>');
           
            }

        $soma_lote = 0;
        $soma_vlr_projetado = 0;
        $soma_juros = 0;
        $soma_multa = 0;
        
        if ($selected_objects)
        {
            foreach ($selected_objects as $selected_object)
            {
                try
                {
            
                TTransaction::open('facilitasmart'); // open a transaction with database
                           
                $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                
                if ( $object->situacao == '0' )
                { 
                    // correção
                    // pega data escolhida como data do acordo e converte para uso no calculo dos juros
                    $data_acordo = $param['dt_acordo'];
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime($data_acordo);
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                   
                    //var_dump($object);
                    $soma_lote += $object->valor;
                    $soma_vlr_projetado +=  ($object->valor + $multa + $juros);
                    $soma_juros += $juros;
                    $soma_multa += $multa;
                
                    $databaixa = explode("-", $param['dt_acordo']);
                    $status = ContasReceber::retornaStatusFechamento(
                                $object->condominio_id, 
                                $databaixa[1].'/'.$databaixa[0],
                                $param['conta_fechamento_id']);
            
                    if ( $status != 0 or $status == ''){
                        new TMessage('info', 'Divergência no Fechamento, verique o mês de referência !');
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
        
        // preenche o valor lancado e o valor projetado
        $obj = new StdClass;
        //$obj->unidade_descricao = $unidade_desc;
        $obj->valor_lancado = number_format($soma_lote, 2, ',', '.');
        $obj->valor_projetado = number_format($soma_vlr_projetado, 2, ',', '.');
        $obj->valor_parcela = number_format(($soma_vlr_projetado/$param['parcelas']), 2, ',', '.');
        $obj->multa = number_format($soma_multa, 2, ',', '.');
        $obj->juros = number_format($soma_juros, 2, ',', '.');
        $obj->correcao = number_format(0.00, 2, ',', '.');
        $obj->acrescimo = number_format(0.00, 2, ',', '.');
        $obj->desconto = number_format(0.00, 2, ',', '.');
        $obj->dt_acordo = $string->formatDateBR($param['dt_acordo']);
        $obj->parcelas = $param['parcelas'];
        $obj->conta_fechamento_id = $param['conta_fechamento_id'];
        TForm::sendData('list_CriaAcordo', $obj);
        
        
        //if ($soma_lote != $param['valor_lote']) {
        //    new TMessage('info', 'O lote selecionado tem valor divergente do total informado! Baixa Cancelada.');
        //    return;  
        
        //}
            
        
        //$win = TWindow::create('Results', 0.6, 0.6);
        //$win->add($datagrid);
        //$win->show();
        
        // grava a data da baixa para reaproveitar
        //TSession::setValue('data_baixa', $param['dt_baixa']);
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
    

    
    public function onAtualizar( $param )
    {
        $source  = array('.', ',');
        $replace = array('', '.');
            
        $data = $this->form->getData(); 
        
        $multa = str_replace($source, $replace, $param['multa']); 
        $juros = str_replace($source, $replace, $param['juros']);
        $correcao = str_replace($source, $replace, $param['correcao']);
        $desconto = str_replace($source, $replace, $param['desconto']);
        $acrescimo = str_replace($source, $replace, $param['acrescimo']);
        
        $lancado = str_replace($source, $replace, $param['valor_lancado']);
        
        $projetado = $multa+$juros+$correcao+$acrescimo;
        $projetado = $projetado - $desconto;
        $projetado = $projetado + $lancado;
        
        // preenche o valor lancado e o valor projetado
        $obj = new StdClass;
        $obj->valor_projetado = number_format($projetado, 2, ',', '.');
        $obj->valor_parcela = number_format(($projetado/$param['parcelas']), 2, ',', '.');

        $this->form->setData($data);
        
        TForm::sendData('list_CriaAcordo', $obj);
      
    }  
    
    
    public function onGerar($param)
    {
           
        $string = new StringsUtil;
        
        if (empty($param['dt_acordo']) or empty($param['parcelas']) or empty($param['valor_projetado']) or
            empty($param['valor_lancado']))
        {
            new TMessage('error', '<b>Error</b> ' . 'Necessários todos campos calculados! Recalcule. '); // shows the exception error message
            return;
        }
        
        //$dadosboleto = $param;
        //$dadosboleto["valor_boleto"] = str_replace(".", "",$dadosboleto["valor_boleto"]);
        //$dadosboleto["valor_boleto"] = str_replace(",", ".",$dadosboleto["valor_boleto"]);
        //$dadosboleto["valor_boleto"] = number_format($dadosboleto["valor_boleto"], 2, ',', '');
        
        $dados = $param;
        $data_acordo = $string->formatDate($param['dt_acordo']);
        $dados["multa"] = str_replace(".", "",$dados["multa"]);
        $dados["multa"] = str_replace(",", ".",$dados["multa"]);
        $multa = $dados['multa'];
        $dados["juros"] = str_replace(".", "",$dados["juros"]);
        $dados["juros"] = str_replace(",", ".",$dados["juros"]);
        $juros = $dados['juros'];
        $dados["correcao"] = str_replace(".", "",$dados["correcao"]);
        $dados["correcao"] = str_replace(",", ".",$dados["correcao"]);
        $correcao = $dados['correcao'];
        $dados["acrescimo"] = str_replace(".", "",$dados["acrescimo"]);
        $dados["acrescimo"] = str_replace(",", ".",$dados["acrescimo"]);
        $acrescimo = $dados['acrescimo'];
        $dados["desconto"] = str_replace(".", "",$dados["desconto"]);
        $dados["desconto"] = str_replace(",", ".",$dados["desconto"]);
        $desconto = $dados['desconto'];
        $dados["valor_lancado"] = str_replace(".", "",$dados["valor_lancado"]);
        $dados["valor_lancado"] = str_replace(",", ".",$dados["valor_lancado"]);
        $valor_lancado = $dados['valor_lancado'];
        $dados["valor_projetado"] = str_replace(".", "",$dados["valor_projetado"]);
        $dados["valor_projetado"] = str_replace(",", ".",$dados["valor_projetado"]);
        $valor_projetado = $dados['valor_projetado'];
   
        $param['dt_acordo'] = $string->formatDate($param['dt_acordo']);

        TTransaction::open('facilitasmart'); // open a transaction
        
        // verifica se existe fechamento em aberto para poder criar as parcelas do acordo
        $data_parcela = '';

        for ($i = 1; $i <= $param['parcelas']; $i++) {
            if ( empty($data_parcela) ) {
                $data_parcela = $param['dt_acordo'];
            }else {
                $data_parcela = date('Y-m-d', strtotime($data_parcela. "+30 days"));
            } 
            
            $data_parcela_acordo = explode("-", $data_parcela);
            $status = ContasReceber::retornaStatusFechamento(
                        TSession::getValue('id_condominio'), 
                        $data_parcela_acordo[1] .'/'. 
                        $data_parcela_acordo[0],
                        $param['conta_fechamento_id']);
            
            if ( $status == 1 ){
                new TMessage('info', 'Inconsistência com o Fechamento e a data da parcela, ' . 
                $data_parcela_acordo[1] .'/'. $data_parcela_acordo[0] . ' ! Acordo cancelado.');
                TTransaction::close(); // close the transaction
                return;
            }
            
            /* verifica e nao deixa lancar se não existir fechamento aberto na data
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da parcela do acordo ' . 
                $data_parcela_acordo[1] .'/'. $data_parcela_acordo[0] . ' !');
                return;
            }*/
          
           
        }

        ///////

        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
       
        $unidade_id = ''; // caso selecione pela descricao da unidade, tenho que atibuir qual a unidade
        
        // verifica se o lote tem o mesmo valor do informado pelo usuário
        if ($selected_objects)
        {
            $soma_lote = 0;
            
            foreach ($selected_objects as $selected_object)
            {
                try
                {
                           
                $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                
                $unidade_id = $selected_object['unidade_id'];
                
                //var_dump($object);

                if ( $object->situacao == '0' )
                { 
                    $soma_lote += $object->valor;
                
                    $databaixa = explode("-", $param['dt_acordo']);

                    // verifica se existe fechamento aberto possivel de edicao
                    $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                      where('mes_ref', '=', $databaixa[1].'/'.$databaixa[0])->load();
            
                    $status = '';
                    foreach ($fechamentos as $fechamento) {
                        $status = $fechamento->status;
                    }
            
                    if ($status == 1 or $status == ''){
                        //var_dump($status);
                        new TMessage('info', 'Não existe um Fechamento aberto para a data do acordo !');
                        TTransaction::close(); 
                        $this->form->setData($object); // mantem os dados digitados;
                        return;     
                    }
                    
                }

                }
                catch (Exception $e) // in case of exception
                {
                  new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
                  TTransaction::rollback(); // undo all pending operations
                }
                
            }
            
        }
        
        //var_dump(number_format($soma_lote, 2, ',', '.'));
        //var_dump($param['valor_lancado']);
        if (number_format($soma_lote, 2, ',', '.') != $param['valor_lancado']) {
        //if ($soma_lote != $param['valor_lancado']) {
            new TMessage('info', 'O lote selecionado tem valor divergente do total! Acordo Cancelado.');
            TTransaction::close(); // close the transaction
            return;  
        
        }
        
       
        //criar um lancamento na tabela acordo com os dados do acriado
        try
        {
            
            $object = new Acordo();  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $condominio = TSession::getValue('id_condominio');
            $object->condominio_id = $condominio;
            $object->unidade_id = $unidade_id;
            $object->data_base_acordo = $data_acordo;
            $object->parcelas = $param['parcelas'];
            $object->classe_id = 3; // acordo
            $object->valor_lancado =  $valor_lancado;
            $object->valor_projetado = $valor_projetado;
            $object->desconto = $desconto;
            $object->juros = $juros;
            $object->multa = $multa;
            $object->correcao = $correcao;
            $object->acrescimo = $acrescimo;
            $object->observacao = ' acordo criado';
            $object->store(); // save the object
                      
            // pega o numero do acordo
            $acordo_id = $object->id;
            
            //criando log 
            //TTransaction::setLogger(new TLoggerTXT('log/acordoNew'.$acordo_id.'('.date('Y-m-d H:i:s').')'.'.txt')); 
            //$user = TSession::getValue('login');
            //TTransaction::Log($user . ' ' . ' - Acordo criado No. ' . $acordo_id);

        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
        // final rotina que cria o lancamento do acordo
        
        // inicio da rotina que coloca os lancamento em aberto como sendo acordo
        if ($selected_objects)
        {
            //$datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                try
                {
                
                    $object = new ContasReceber($selected_object['id']); // instantiates the Active Record
                    
                    if ( $object->situacao == '0' ) {
                        $object->situacao = '2'; // em acordo = 2
                        $object->numero_acordo = $acordo_id;
                        $object->dt_ultima_alteracao = date('Y-m-d');
                 
                        // verifica se existe fechamento aberto possivel de edicao
                        $dataacordo = explode("-", $param['dt_acordo']);
                        $status = ContasReceber::retornaStatusFechamento(
                                        $object->condominio_id, 
                                        $dataacordo[1].'/'.$dataacordo[0],
                                        $param['conta_fechamento_id']);
            
                        //var_dump($databaixa);
                        //var_dump($status);
                        if ( $status != 0 or $status == ''){
                            new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa ! Faça estorno dos lançamento escolhidos.');
                            TTransaction::close(); // close the transaction
                            return;
                        }else {
                           //criando log 
                           //TTransaction::setLogger(new TLoggerTXT('log/contas_receberUp'.$object->id.'('.date('Y-m-d H:i:s').')'.'txt')); 
                           // $user = TSession::getValue('login');
                           // TTransaction::Log($user . ' ' . ' - Alterado o lançamento para situacao = 2, numero acodo = novo acordo. Título ' . $object->id);
                
                            $object->store(); // update the object in the database
                        }                     
                        
                        // desarta os objeto
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

                }
                catch (Exception $e) // in case of exception
                {
                  new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
                  TTransaction::rollback(); // undo all pending operations
                }
                
            }
            
          
            // atualiza o grid apos desmarcar o titulo baixado
            // reload datagrids
            $this->onReload( func_get_arg(0) );
        }
        // final da rotina que altera os lancamentos que fazem parte do acordo, colocando-os com situacao = 2
        
        
        // inicio rotina que cria os lancamentos novos pelas parcelas do acordo
        try
        {
            //setar log para teste
            //TTransaction::setLogger(new TLoggerTXT('/var/www/html/facilitasmart/log.txt')); 
            //TTransaction::log("** inserting contas receber"); 
            
            $data_parcela = '';
            for ($i = 1; $i <= $param['parcelas']; $i++) {
                if ( empty($data_parcela) ) {
                    $data_parcela = $data_acordo;
                }else {
                    $data_parcela = date('Y-m-d', strtotime($data_parcela. "+30 days"));
                } 
            
                $data_parcela_acordo = explode("-", $data_parcela);
               
                $detail = new ContasReceber;
                
                $unid = new Unidade($unidade_id);
                $pess = new Pessoa($unid->proprietario_id);
                
                $condominio = new Condominio(TSession::getValue('id_condominio'));
                   
                $detail->condominio_id = TSession::getValue('id_condominio');
                $detail->unidade_id = $unidade_id;
                $detail->nome_responsavel = $pess->nome;
                $detail->mes_ref = $data_parcela_acordo[1] .'/'. $data_parcela_acordo[0];
                $detail->cobranca = '3';
                $detail->tipo_lancamento = 'A';
                $detail->classe_id = '3'; // pegar no configuracao do condominio
                $detail->dt_lancamento = date('Y/m/d');
                $detail->dt_vencimento = $data_parcela;
                $detail->valor = $valor_projetado/$param['parcelas'];
                $detail->descricao = 'Acordo No. ' . $acordo_id . ' Parcela ' . $i . '/' . $param['parcelas'];
                $detail->numero_acordo = $acordo_id;
                $detail->parcela = $i;
                $detail->dt_ultima_alteracao = date('Y/m/d');
                $detail->usuario_id = TSession::getValue('userid'); 
                
                $detail->multa_boleto_cobranca = $condominio->multa;
                $detail->juros_boleto_cobranca = $condominio->juros;
                        
                        
                $detail->store();
            }
         
         }
         catch(Exception $e)
         {
             new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
             TTransaction::rollback(); // undo all pending operations
         }
        /////////// fim rotina que cria lancamntos das parcelas do acordo
        
        TTransaction::close();
    }

}
