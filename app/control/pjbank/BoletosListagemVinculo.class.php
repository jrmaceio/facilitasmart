<?php

/**
 
 */
class BoletosListagemVinculo extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    private $string;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
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

        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_BoletosListagemVinculo');
        $this->form->setFormTitle('Boletos vínculados');
        

        $this->string = new StringsUtil;

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');
        $cobranca = new TEntry('cobranca');
        $tipo_lancamento = new TEntry('tipo_lancamento');
        

        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);

        //$unidade_id = new TEntry('unidade_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $pessoa_id = new TDBCombo('pessoa_id', 'facilitasmart', 'Pessoa', 'id', 'nome','nome',$criteria);

    
        $dt_vencimento = new TDate('dt_vencimento');
        $valor = new TEntry('valor');
       
        //$situacao = new TEntry('situacao');
        $situacao = new TCombo('situacao');
        $situacao->addItems(array( 1=>'Emitido',
                                   2=>'Reg PJBank'));
        //$situacao->addValidation('situacao', new TRequiredValidator );        
        //nao funcionou, acho que é outro metodo ----$situacao->setValue('');
        
        $dt_pagamento = new TDate('dt_pagamento');
        $dt_ultima_alteracao = new TDate('dt_ultima_alteracao');



        $id->setSize(100);
        $mes_ref->setSize(100);
        $cobranca->setSize(100);
        $tipo_lancamento->setSize('20%');
        $classe_id->setSize('100%');
        $pessoa_id->setSize('100%');
        $situacao->setSize('100%');
        $dt_vencimento->setSize('100%');
        $dt_pagamento->setSize('100%');
        $dt_ultima_alteracao->setSize('100%');
        $valor->setSize('100%');

        $this->form->addFields( [new TLabel('Id')], [$id],
                                [new TLabel('Status Boleto')], [$situacao],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );

        $this->form->addFields([new TLabel('Classe')], [$classe_id]);
        
        $this->form->addFields( [new TLabel('Pessoa')], [$pessoa_id] );
        
        // mascaras
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_ultima_alteracao->setMask('dd/mm/yyyy');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data11') );
        
        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        //$this->form->addAction('Cadastrar', new TAction(['ContasReceberForm', 'onEdit']), 'fa:plus #69aa46');
        //$this->form->addAction('Informe', new TAction([$this, 'onInform']), 'fa:barcode  #69aa46');
        $this->form->addAction('Cria Boleto Vinculado', new TAction([$this, 'onNewBolVinculado']), 'fa:bank #69aa46');
       
        //$this->form->addAction('Show', new TAction([$this, 'onSave1']), 'fa:barcode  #69aa46');
                
        
        
        // inicio 1o datagrid 
        
        $this->datagridVinculo = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagridVinculo->datatable = 'true';
        $this->datagridVinculo->width = '100%';
        //$this->datagrid->enablePopover('Informações adicionais da cobrança', "Código Único:  {pjbank_id_unico}
        //Link:  {pjbank_linkBoleto}
        // ");
        
        $this->datagridVinculo->setHeight(320);

        // creates the datagrid columns
        $column_id1 = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref1 = new TDataGridColumn('mes_ref', 'Mês Ref', 'center');
        $column_boleto_status1 = new TDataGridColumn('boleto_status', 'St Bol.', 'center');
        $column_proprietario1 = new TDataGridColumn('proprietario_id', 'Proprietário', 'left');
        $column_dt_vencimento1 = new TDataGridColumn('dt_vencimento', 'Vencimento', 'right');
        $column_valor1 = new TDataGridColumn('valor', 'Valor', 'right');
        $column_desconto1 = new TDataGridColumn('desconto_boleto_cobranca', 'Desc.', 'right');
        $column_situacao1 = new TDataGridColumn('situacao', 'Situação', 'center');
        
        // add the columns to the DataGrid
        $this->datagridVinculo->addColumn($column_id1);
        $this->datagridVinculo->addColumn($column_mes_ref1);
        $this->datagridVinculo->addColumn($column_boleto_status1);
        $this->datagridVinculo->addColumn($column_proprietario1);
        $this->datagridVinculo->addColumn($column_dt_vencimento1);
        $this->datagridVinculo->addColumn($column_valor1);
        $this->datagridVinculo->addColumn($column_desconto1);
        $this->datagridVinculo->addColumn($column_situacao1);
        // fim 1o datagrid
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        //$this->datagrid->enablePopover('Informações adicionais da cobrança', "Código Único:  {pjbank_id_unico}
        //Link:  {pjbank_linkBoleto}
        // ");
        
        $this->datagrid->setHeight(320);

        
        // creates the datagrid columns
        //$column_check = new TDataGridColumn('check', 'Selecione', 'center');
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'center');
        $column_boleto_status = new TDataGridColumn('boleto_status', 'St Bol.', 'center');
        $column_proprietario = new TDataGridColumn('proprietario_id', 'Proprietário', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_desconto = new TDataGridColumn('desconto_boleto_cobranca', 'Desc.', 'right');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'center');
        //$column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Pagamento', 'left');
        //$column_dt_credito = new TDataGridColumn('dt_liquidacao', 'Crédito', 'left');
        //$column_valor_pago = new TDataGridColumn('valor_pago', 'Vlr Pago', 'left');
        
        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_check); 
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_boleto_status);
        $this->datagrid->addColumn($column_proprietario);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_desconto);
        $this->datagrid->addColumn($column_situacao);
        //$this->datagrid->addColumn($column_dt_pagamento);
        //$this->datagrid->addColumn($column_dt_credito);
        //$this->datagrid->addColumn($column_valor_pago);
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);

        $column_situacao->setTransformer( function($value, $object, $row) {
            $class = ($value=='0') ? 'danger' : 'success';
            $label = 'Indefinido';
            
            if ($value=='0') {
                //var_dump($object->dt_vencimento);
                //var_dump(Date('d/m/Y'));
                //var_dump($object->dt_vencimento < Date('d/m/Y'));
                if ($object->dt_vencimento < Date('d/m/Y')) {
                    $class = 'danger';
                    $label = 'Aberto';
                } else {
                    $class = 'success';
                    $label = 'Aberto';
                }
            }
            
            if ($value=='1') {
                $class = 'success';
                $label = 'Pago';
            }
            
            if ($value=='2') {
                $class = 'success';
                $label = 'Acordo';
            }                
            
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });          
                          
        $column_boleto_status->setTransformer( function($value, $object, $row) {
            $class = ($value=='0') ? 'danger' : 'success';
            
            $label = 'Indefinido';
            
            if ($value=='1') {
                $class = 'danger';
                $label = 'Emitido';
            }
            
            if ($value=='2') {
                $class = 'success';
                $label = 'PJBank';
            }                
            
            if ($value=='3') {
                $class = 'success';
                $label = 'Vinculado';
            }
            
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        // Formata o valor da parcela no datagrid, no padão -> R$ 1.000,99
        $column_valor->setTransformer(function($value, $object, $row)
            {
                //if (!is_numeric($value))
                //{
                //    return "R$ --";
                //}
                $class = 'label-primary';
                
                $value = "R$ " . number_format($value, 2, ",", ".");
                return '<span style="min-width: 200px" class="label '.$class.'">'. $value.'</span>';
            });
        
        // Formata o valor da parcela no datagrid, no padão -> R$ 1.000,99
        $column_desconto->setTransformer(function($value, $object, $row)
            {
                //if (!is_numeric($value))
                //{
                //    return "R$ --";
                //}
                $class = 'label-primary';
                
                $value = "R$ " . number_format($value, 2, ",", ".");
                return '<span style="min-width: 200px" class="label '.$class.'">'. $value.'</span>';
            });
            
        $this->datagrid->disableDefaultClick();
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        //contador
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        
        //$container->add(TPanelGroup::pack('Lançamentos', $this->form));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagridVinculo));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    public function onNewBolVinculado($param)
    {
        
    
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
        TSession::setValue('ContasReceberListagem_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberListagem_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_situacao',   NULL);
        
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


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->situacao) AND ($data->situacao)) {
            // pelo status do boleto (boleto_status)
            $filter = new TFilter('boleto_status', '=', "{$data->situacao}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_situacao',   $filter); // stores the filter in the session
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
        
        // filtros obrigatorios
        $filter = new TFilter('condominio_id', '=', TSession::getValue('id_condominio')); // create the filter
        
        // fill the form with data again
        $this->form->setData($data); 
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data1', $data);
        
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
            $limit = 12;
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


            if (TSession::getValue('ContasReceberListagem_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_situacao')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_situacao')); // add the session filter
            }

            if (TSession::getValue('ContasReceberListagem_filter_pessoa_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_pessoa_id')); // add the session filter
            }
            
            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            
            $this->datagridVinculo->clear();
            
            
            if ($objects)
            {
                  
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->dt_pagamento ? $object->dt_pagamento = $this->string->formatDateBR($object->dt_pagamento) : null;
                    $object->dt_liquidacao ? $object->dt_liquidacao = $this->string->formatDateBR($object->dt_liquidacao) : null;
                    
                    $plano_contas = new PlanoContas($object->classe_id);
                    $object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    $unidade = new Unidade( $object->unidade_id );
                    $proprietario = new Pessoa( $unidade->proprietario_id );
                    
                    //$object->proprietario_id = $unidade->descricao . '-' . $proprietario->nome;
                    
                    $object->proprietario_id = '<span style="color:black">'. $unidade->descricao . '-' . $proprietario->nome . ' ' . ' </span>' .
                                           '<br> <i class="fa barcode "> '. 'Linha Digitável ' . $object->pjbank_linhaDigitavel . '</i>' .
                                           ' <i class="fa:link"> Link ' . $object->pjbank_linkBoleto . '</i>';
                    
                    $object->check = new TCheckButton('check_'.$object->id);
                    $object->check->setIndexValue('on');
            
                                                  
                    $this->datagrid->addItem($object);
                    $this->form->addField($object->check);
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

?>