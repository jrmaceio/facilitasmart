<?php

/**
 * ContasReceberListagem Listing
 * @author  <your name here>
 */
class ContasReceberListagem extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_ContasReceber');
        $this->form->setFormTitle('Contas a Receber');
        

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
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);

        $dt_vencimento = new TDate('dt_vencimento');
        $valor = new TEntry('valor');
       
        //$situacao = new TEntry('situacao');
        $situacao = new TCombo('situacao');
        $situacao->addItems(array(    0=>'Em Aberto',
                                        1=>'Pago',
                                        2=>'Em Acordo',
                                        3=>'Sub Júdice'));
        //$situacao->addValidation('situacao', new TRequiredValidator );        
        //nao funcionou, acho que é outro metodo ----$situacao->setValue('');
        
        $dt_pagamento = new TDate('dt_pagamento');
        $dt_ultima_alteracao = new TDate('dt_ultima_alteracao');



        $id->setSize(100);
        $mes_ref->setSize(100);
        $cobranca->setSize(100);
        $tipo_lancamento->setSize('20%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        $situacao->setSize('100%');
        $dt_vencimento->setSize('100%');
        $dt_pagamento->setSize('100%');
        $dt_ultima_alteracao->setSize('100%');
        $valor->setSize('100%');

        $this->form->addFields( [new TLabel('Id')], [$id],
                                [new TLabel('Situação')], [$situacao],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id],[new TLabel('Classe')], [$classe_id]);
        
        // mascaras
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_ultima_alteracao->setMask('dd/mm/yyyy');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        $this->form->addAction('Cadastrar', new TAction(['ContasReceberForm', 'onEdit']), 'fa:plus #69aa46');

         // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        //$this->datagrid->enablePopover(_t('Abstract'), '<b>'._t('Description').'</b><br>' . '{description}' . '<br><b>'._t('Solution').'</b><br>' . '{solution}');
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_boleto_status = new TDataGridColumn('boleto_status', 'St Bol.', 'right');
        $column_proprietario = new TDataGridColumn('proprietario_id', 'Proprietário', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'center');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Dt Pagamento', 'left');
        $column_dt_credito = new TDataGridColumn('dt_liquidacao', 'Dt Crédito', 'left');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'right');
        $column_tarifa = new TDataGridColumn('tarifa', 'Tarifa', 'right');
        $column_nosso_numero = new TDataGridColumn('nosso_numero', 'Nosso Numero', 'right');
        
        // order //
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_mes_ref->setAction(new TAction([$this, 'onReload']), ['order' => 'mes_ref']);
        $column_classe_id->setAction(new TAction([$this, 'onReload']), ['order' => 'classe_id']);
        $column_proprietario->setAction(new TAction([$this, 'onReload']), ['order' => 'proprietario']);
        $column_dt_vencimento->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_vencimento']);
                
                
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_boleto_status);
        $this->datagrid->addColumn($column_proprietario);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_dt_credito);
        $this->datagrid->addColumn($column_valor_pago);
        $this->datagrid->addColumn($column_tarifa);
        $this->datagrid->addColumn($column_nosso_numero);
        
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
            
            if ($value=='4') {
                $class = 'success';
                $label = 'Enviado';
            }

            if ($value=='5') {
                $class = 'success';
                $label = 'Registrado';
            }

            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });

        $column_situacao->setTransformer( function($value, $object, $row) {
            $class = ($value=='0') ? 'danger' : 'success';
            $label = 'Indefinido';

            if ($value=='0') {
                $class = 'danger';
                $label = 'Em Aberto';
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
        
        $action_baixar = new TDataGridAction(array($this, 'onBaixa'));
        $action_baixar->setButtonClass('btn btn-default btn-sm');
        $action_baixar->setLabel('Recibo');
        $action_baixar->setImage('fa:clipboard #108E2C');
        $action_baixar->setField('id');
        $this->datagrid->addAction($action_baixar);
        
        $action_edit = new TDataGridAction(array('ContasReceberForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default btn-sm');
        $action_edit->setLabel('Editar');
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        $action_delete = new TDataGridAction(array($this, 'onDelete'));
        $action_delete->setButtonClass('btn btn-default');
        $action_delete->setLabel('Excluir');
        $action_delete->setImage('far:trash-alt red');
        $action_delete->setField('id');
        $this->datagrid->addAction($action_delete);
           
        
        $action6 = new TDataGridAction(['ContasReceberListagemAux', 'onInputDialog']);
        //$action6 = new TDataGridAction([$this, 'onInputDialog']);
        $action6->setLabel('Boleto');
        $action6->setField('id');
        $action6->setImage('fa:table blue fa-lg');
		$this->datagrid->addAction($action6);
		
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        
        //$container->add(TPanelGroup::pack('Lançamentos', $this->form));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
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
            $object = new ContasReceber($key); // instantiates the Active Record
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
        
        TSession::setValue('ContasReceberListagem_filter_imovel_id',   NULL);

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
            $filter = new TFilter('situacao', '=', "{$data->situacao}"); // create the filter
            TSession::setValue('ContasReceberListagem_filter_situacao',   $filter); // stores the filter in the session
        }


        // filtros obrigatorios
        $filter = new TFilter('condominio_id', '=', TSession::getValue('id_condominio')); // create the filter
        TSession::setValue('ContasReceberListagem_filter_imovel_id',   $filter); // stores the filter in the session
        
        
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
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'desc';
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
                    $object->dt_liquidacao ? $object->dt_liquidacao = $this->string->formatDateBR($object->dt_liquidacao) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                    $object->tarifa ? $object->tarifa = number_format($object->tarifa, 2, ',', '.') : null;
                    
                    //switch ($object->situacao)
                    //{
                    //case '0':
                    //    $object->situacao = 'aberto';
                    //    break;
                    //case '1':
                    //    $object->situacao = 'pago';
                    //    break;
                    //case '2':
                    //    $object->situacao = 'acordo';
                    //    break;
                    //}
                    
                    $plano_contas = new PlanoContas($object->classe_id);
                    $object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    $unidade = new Unidade( $object->unidade_id );
                    $proprietario = new Pessoa( $unidade->proprietario_id );
                    
                    $object->proprietario_id = $unidade->bloco_quadra . ' ' . $unidade->descricao . '-' . $proprietario->nome;
                              
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
    
    public function onBaixa($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Baixar'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Gera impressão de Recibo ?', $action);
    }
    
    /**
     * Delete a record
     */
    public function Baixar($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado nao permite baixar
            if ( $object->situacao != 1 ) {
              new TMessage('info', 'Título sem movimentação, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            $condominio = new Condominio($object->condominio_id);
            $unidade = new Unidade($object->unidade_id);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $classe = new PlanoContas($object->classe_id);
                        
            // substitui o html
            $html = new THtmlRenderer('app/resources/recibo_taxa.html');
                
            $replace = array();
            $replace['condominio'] = $condominio->nome;
            $replace['proprietario'] = $pessoa->nome;
            $replace['unidade'] = $unidade->descricao;
            $replace['valor'] = number_format($object->valor_pago,2,',','.'); 
            $replace['extenso'] = Extenso::converte(number_format($object->valor_pago,2,',','.'), true, false);
            $replace['classe'] = $classe->descricao;
            $replace['mes_ref'] = $object->mes_ref;
            $replace['cidade'] = 'Arapiraca';
            
              
            $replace['dt_pagamento'] = strftime("%d de %B de %Y", strtotime($object->dt_pagamento)); 
                
            // replace the main section variables
            $html->enableSection('main', $replace);

                
            // apenas para debug
            //new TMessage('info', $html->getContents());
            
            //new TMessage('info', Extenso::converte('12,50', true, false));
                
            $output = $html->getContents();
            $document = 'tmp/'.uniqid().'.pdf'; 
            $html = AdiantiHTMLDocumentParser::newFromString($output);
            $html->saveAsPDF($document);
            parent::openFile($document);  
   
            TTransaction::close(); // close the transaction
            
            //$this->onReload( $param ); // reload the listing
            //new TMessage('info', 'Baixado'); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Show the input dialog data
     */
    public function onConfirm1( $param )
    {
        new TMessage('info', 'Confirm1 : ' . json_encode($param));
    }
    
    /**
     * Show the input dialog data
     */
    public function onConfirm2( $param )
    {
        new TMessage('info', 'Confirm2 : ' . json_encode($param));
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
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // verifica fechamento
            $status = ContasReceber::retornaStatusFechamento($object->condominio_id, 
                      $object->mes_ref,
                      $object->conta_fechamento_id);
            
            //default = 1 fechado, não permite nada
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Referência ! Operação Cancelada.');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return;
            }
            ////////////////////////////////////
            
            // se titulo estiver movimentado nao permite baixar
            if ( $object->situacao != 0 ) {
              new TMessage('info', 'Título com movimentação, exclusão não permitida !'); // success message
              TTransaction::close(); // close the transaction
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


    /*
    public static function onInputDialog( $param )
    {
        $id = $param['id'];
        
        TTransaction::open('facilitasmart');
        $reg_titulo = ContasReceber::find($id,false);
        $reg_cta = ContaCorrente::find( $reg_titulo->id_conta_corrente );
        TTransaction::rollback();
        
        $quick = new TQuickForm('input_form');
        $quick->style = 'padding:20px';
        
        $titulo = new TEntry('titulo');
        $titulo->setValue($id);
        $titulo->setEditable(FALSE);
        
        $conta_corrente = new TDBCombo('conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','id');
        if ($reg_titulo->id_conta_corrente != '') { $conta_corrente->setValue($reg_titulo->id_conta_corrente); }
        
        $banco_carteira = new TDBCombo('banco_carteira', 'facilitasmart', 'Banco', 'id', 'sigla');
        $banco_carteira->setEditable(FALSE);
        if ($reg_titulo->id_conta_corrente != '') { $banco_carteira->setValue($reg_cta->id_banco); }
        
        //$nosso_numero = new TEntry('nosso_numero');
        //$nosso_numero->setEditable(FALSE);
        
        $modelo = new TRadioGroup('modelo');
        $modelo->addItems(array('1'=>'Clássico', '2'=>'Carnê', '3'=>'Informativo'));
        $modelo->setLayout('horizontal');
        $modelo->setValue(2);
        
        $conta_corrente->setSize('70%');
        $banco_carteira->setSize('70%');
        $modelo->setSize('100%');
        //$nosso_numero->setSize('100%');
        
        $quick->addQuickField('Titulo', $titulo);
        $quick->addQuickField('Conta Corrente', $conta_corrente);
        $quick->addQuickField('Banco/Carteira', $banco_carteira);
        $quick->addQuickField('Modelo', $modelo);
        //$quick->addQuickField('Nosso Numero', $nosso_numero);
        $conta_corrente->setChangeAction(new TAction(array('ContasReceberListagem', 'onExitCtDialog')));
        $quick->addQuickAction('Avançar', new TAction(array('ContasReceberListagem', 'onBoleto')), 'fa:arrow-circle-right green');
        // show the input dialog
        new TInputDialog('Insira os Dados', $quick);
    }


    public static function onExitCtDialog( $param )
    {
        TTransaction::open('facilitasmart');
        $banco = ContaCorrente::find($param['conta_corrente'])->id_banco;
        $obj = new StdClass;
        $obj->banco_carteira = $banco;
        TTransaction::rollback();
        TForm::sendData('input_form', $obj);
    }


    public static function onBoleto($param)
    {
        try {
            TTransaction::open('facilitasmart');
            
            if (!isset($param['conta_corrente']) OR empty($param['conta_corrente']) ) {
                $pos_action = new TAction(['ContasReceberListagem', 'onInputDialog']);
                $pos_action->setParameter('id',$param['titulo']);
                new TMessage('warning','Selecione a Conta!!!', $pos_action);
            } else {
                $id_titulo      = $param['titulo'];
                $reg_fin_titulo = ContasReceber::find($id_titulo);
                
				//$id_favorecido  = $reg_fin_titulo->id_favorecido; 
                //$reg_favorecido = Pessoa::find($id_favorecido);
			
                $condominio = new Condominio($reg_fin_titulo->condominio_id);
                $classe = new PlanoContas($reg_fin_titulo->classe_id);
                $unidade = new Unidade($reg_fin_titulo->unidade_id);
                $id_favorecido = $unidade->proprietario_id;
                
                $reg_favorecido = new Pessoa($unidade->proprietario_id);
				
                //$reg_empresa    = ServCliErpEmp::BuscaEmpresa( $reg_fin_titulo->id_cliente_erp , $reg_fin_titulo->id_empresa );
                $reg_empresa = $condominio; // nesta tabela existem os dados da empresa/condomínio (endereco, etc.)
                
                //$reg_favorecido_endereco = PessoaEndereco::where('id_pessoa', '=', $id_favorecido)
                //                                            ->where('id_tipo_endereco', '=', 3)
                //                                            ->load();
                //if (empty($reg_favorecido_endereco)) {
                //    $reg_favorecido_endereco = PessoaEndereco::where('id_pessoa', '=', $id_favorecido)
                //                                                ->where('id_tipo_endereco', '=', 1)
                //                                                ->load();  
                //}
                //foreach ($reg_favorecido_endereco as $value_reg_favorecido_endereco) {
                //    $endereco1 = $value_reg_favorecido_endereco->endereco . " nr. " . $value_reg_favorecido_endereco->numero;
                //    $endereco2 = $value_reg_favorecido_endereco->bairro . " - " . $value_reg_favorecido_endereco->cidade->nome . " - " . $value_reg_favorecido_endereco->estado->uf . " - " . $value_reg_favorecido_endereco->cep;
                //}
                
                $endereco1 = $reg_favorecido->endereco . " nr. " . $reg_favorecido->numero . " - " . $unidade->bloco_quadra . "-" . $unidade->descricao;
                $endereco2 = $reg_favorecido->bairro . " - " . $reg_favorecido->cidade . " - " . $reg_favorecido->estado . " - " . 
                             $reg_favorecido->cep;

                $demonstrativo = 'Referência :' . 'Mês Ref.: ' . $reg_fin_titulo->mes_ref . ' Descrição: ' . $reg_fin_titulo->descricao;
                
                //$instrucoes = '- Sr. Caixa, cobrar multa de 2% após o vencimento <br> - Receber até 10 dias após o vencimento <br> - Em caso de dúvidas entre em contato conosco: email@email.com';
                
                $texto = $demonstrativo . '<br>SR CAIXA NÃO RECEBER APOS O VENCIMENTO';  $textom = $textoj = $textod = '';
                if ( ($reg_fin_titulo->multa_boleto_cobranca > 0) || ($reg_fin_titulo->multa_boleto_cobranca != '') ) 
                { 
                    //$vlr_multa = round ( ( ( $reg_fin_titulo->valor * $reg_fin_titulo->multa_boleto_cobranca ) / 100 ) , 2 );
                    //$textom = ' Multa de R$ ' . Uteis::numeroBrasil($vlr_multa) . ' - ' . Uteis::numeroBrasil($reg_fin_titulo->multa_boleto_cobranca) . ' % ao mes.'; 
                    $textom = ' Multa de ' . Uteis::numeroBrasil($reg_fin_titulo->multa_boleto_cobranca) . ' % ao mes.';
                } 
                if ( ($reg_fin_titulo->juros_boleto_cobranca > 0) || ($reg_fin_titulo->juros_boleto_cobranca != '') ) 
                { 
                    //$txa_juros = round ( ( $reg_fin_titulo->juros_boleto_cobranca / 30 ) , 4 ); 
                    //$vlr_juros = round ( ( ( $reg_fin_titulo->valor * $txa_juros ) / 100 ) , 2 );
                    //$textoj = ' Juros de R$ ' . Uteis::numeroBrasil($vlr_juros) . ' - ' . Uteis::numeroBrasil($txa_juros , 4) . ' % ao dia.'; 
                    $textoj = ' Juros de ' . Uteis::numeroBrasil($reg_fin_titulo->juros_boleto_cobranca) . ' % ao mes.';
                }
                if ( ( $textom != '') || ( $textoj != '' ) ) { $texto = $texto . '<br>Após vencimento: ' . $textom . $textoj; }
                if ( ($reg_fin_titulo->desconto_boleto_cobranca > 0) || ($reg_fin_titulo->desconto_boleto_cobranca != '') ) { $textod = '<br>Ate dia ' . Uteis::formataData($reg_fin_titulo->dt_limite_desconto_boleto_cobranca ,'','') . ' conceder desconto de R$ ' . Uteis::numeroBrasil($reg_fin_titulo->desconto_boleto_cobranca) . ', cobrar R$ ' . Uteis::numeroBrasil( ( $reg_fin_titulo->valor - $reg_fin_titulo->desconto_boleto_cobranca ) ) . '.'; }
                if ( $textod != '') { $texto = $texto . $textod; }
                $instrucoes = $texto;
                                    
                $nossonum = $reg_fin_titulo->nosso_numero; // busca nosso numero já atribuido (caso ja exista)
                
                $boleto = array();
                $boleto['id_titulo']          = $id_titulo;
                $boleto['numero_documento']   = $reg_fin_titulo->id;
                $boleto['data_documento']     = Uteis::formataData($reg_fin_titulo->dt_lancamento,'','');
                $boleto['data_vencimento']    = Uteis::formataData($reg_fin_titulo->dt_vencimento,'','');
                $boleto['valor_boleto']       = Uteis::numeroBrasil($reg_fin_titulo->valor);
                $boleto['sacado']             = Uteis::numeroEsquerda($reg_fin_titulo->id_favorecido,6) . " - " . $reg_favorecido->nome;
                $boleto['endereco1']          = $endereco1;
                $boleto['endereco2']          = $endereco2;
                $boleto['demonstrativo']      = $demonstrativo;
                $boleto['instrucoes']         = $instrucoes;
                $boleto['flag_sistema']       = 'S';
                $boleto['agencia']            = ContaCorrente::find($param['conta_corrente'])->agencia;
                $explode = explode("-", ContaCorrente::find($param['conta_corrente'])->conta );
                $boleto['conta']     = $explode[0];
                $boleto["conta_dv"]  = $explode[1];
                $boleto['id_conta']           = $param['conta_corrente'];
                $boleto['convenio']           = ContaCorrente::find($param['conta_corrente'])->convenio;
                $boleto['codigo_banco']       = Banco::find($param['banco_carteira'])->codigo_bacen;
                $boleto['id_banco']           = $param['banco_carteira'];
                $boleto['modelo']             = $param['modelo'];
                
                if ($param['banco_carteira'] == 7) { // sicred
                    $boleto["carteira"]             = '01';// alterado junior 20-10-2020 07:36//20;  // ano
                    $boleto["posto"]                = ContaCorrente::find($param['conta_corrente'])->posto; // 4;     //$boleto['posto']        = 4;
                    $boleto["byte_idt"]             = 2;     //$boleto['indicador']    = 2;  
                    $boleto["inicio_nosso_numero"]  = 20;    // $boleto['ano']          = 21;                          
                }
                
                if ($param['banco_carteira'] == 8) {
                    $boleto["carteira"]             = 1;     
                    $boleto["inicio_nosso_numero"]  = 20;        
                    $boleto["modalidade_cobranca"]  = '01';

                    $boleto["numero_parcela"]       = '01';
                }
                
                if ( (ContaCorrente::find( $param['conta_corrente'] )->tipo_inscricao) == 'F') { 
                    $boleto["cpf_cnpj"] = ContaCorrente::find( $param['conta_corrente'] )->inscricao_cpf; 
                }
                
                if ( (ContaCorrente::find( $param['conta_corrente'] )->tipo_inscricao) == 'J') { 
                    $boleto["cpf_cnpj"] = ContaCorrente::find( $param['conta_corrente'] )->inscricao_cnpj; 
                }
                
                $boleto["identificacao"] = $reg_empresa->nome; // "Sacador";
                $boleto["endereco"]   = $reg_empresa->endereco . " - " . $reg_empresa->numero . " - " . $reg_empresa->bairro . " - " . $reg_empresa->cep;
                $boleto["cidade_uf"]  = $reg_empresa->cidade . " - " . $reg_empresa->estado; 
                $boleto["cedente"]    = ContaCorrente::find( $param['conta_corrente'] )->titular;
                $boleto["quantidade"] = "";
                $boleto["valor_unitario"] = "";
                $boleto["aceite"] = "N";        
                $boleto["especie"] = "R$";
                $boleto["especie_doc"] = "DMI";
                
                // -- se nossonum for vazio (caso nao tenha) vai pegar sequencial novo
                if ($nossonum == '') {
                    $reg_cta_nossonr_gravado = ContaCorrenteNossoNumero::where('id_condominio', '=', $reg_fin_titulo->condominio_id)
                                                            ->where('id_conta_corrente', '=', $param['conta_corrente'])
                                                            ->load();
                                                            
                    $reg_cta_nossonr_gravado_reverse = array_reverse($reg_cta_nossonr_gravado); //inverte tabela 
                    $nseq = $ct = 0;
                    foreach ($reg_cta_nossonr_gravado_reverse as $value) {
                       if ($ct == 0) { $nseq = $value->sequencial + 1; }
                       $ct = $ct + 1;
                    } // fim foreach ($reg_cta_nossonr_gravado_reverse as $value)
                    if ($nseq == 0) { $nseq = 1; }
                    $reg_cta_nossonr = new ContaCorrenteNossoNumero;
                    $reg_cta_nossonr->id_condominio     = $reg_fin_titulo->condominio_id;
                    $reg_cta_nossonr->id_conta_corrente = $param['conta_corrente'];
                    $reg_cta_nossonr->sequencial        = $nseq;
                    $reg_cta_nossonr->id_contas_receber = $id_titulo;
                    $reg_cta_nossonr->store();
                    $boleto['nseq']                     = $nseq;
                    $boleto['nosso_numero']             = '';
                } // fim if ($nossonum == '')
                
                if ($nossonum != '') {
                    $boleto['nseq']             = (int)substr($nossonum,4,5);
                    $boleto['nosso_numero']     = $nossonum;
                }

                TApplication::loadPage('FinBoletoView', 'onGenerate', (array) $boleto);
            } // fim else   
            TTransaction::close();
        } // fim try
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            //TApplication::postData('input_form','ContasReceberListagem');
        }
    }
    */


}
