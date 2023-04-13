<?php

/**
 
 */
class BoletosListagemMorador extends TPage
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
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);
            

        $id->setSize(100);
        $mes_ref->setSize(100);
        $classe_id->setSize('100%');

        $this->form->addFields( [new TLabel('Id')], [$id],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data11') );
        
        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        
        // cria botão para imprimir boletos selecionados
        $this->button2 = new TButton('imprimir_collection');
        $this->impressaoAction = new TAction(array($this, 'onImpressaoEmLote'));
        $this->button2->setAction($this->impressaoAction, 'Imprimir selecionados');
        $this->button2->setImage('fa:barcode black fa-lg');
        $this->form->addField($this->button2);
            
         // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        //$this->datagrid->enablePopover('Informações adicionais da cobrança', "Código Único:  {pjbank_id_unico}
        //Link:  {pjbank_linkBoleto}
        // ");
         
        
        $this->datagrid->setHeight(320);

        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'left');
        $column_boleto_status = new TDataGridColumn('boleto_status', 'St Bol.', 'right');
        $column_proprietario = new TDataGridColumn('proprietario_id', 'Proprietário', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'left');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_boleto_status);
        $this->datagrid->addColumn($column_proprietario);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_situacao);
        
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
        
      
        // create Informacao do regitro de boleto 
        $action_inf = new TDataGridAction(array($this, 'onPJBankInfo'));
        $action_inf->setButtonClass('btn btn-default');
        $action_inf->setLabel(('Informação'));
        $action_inf->setImage('fa:info-circle  fa-lg black');
        $action_inf->setField('id');
        $this->datagrid->addAction($action_inf);
        
        // create IMPRIMIR action datagrid 
        $action_edit = new TDataGridAction(array($this, 'onPJBankBoleto'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(('Boleto'));
        $action_edit->setImage('fa:barcode fa-lg black');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
           
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
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
    
   
    
    public function onInform($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            
            $condominio = new Condominio(TSession::getValue('id_condominio'));
                       
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes?data_inicio=04/01/2018&data_fim=04/30/2020&pago=0&pagina=1",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank
              ),));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $pjbank=json_decode($response, true);
                var_dump($pjbank);
                
            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * 
     */
    public function onPJBankInfo($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado nao permite a operação
            if ( $object->situacao != '0' ) {
              new TMessage('info', 'Título com movimentação, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            if ( $object->boleto_status != '2' ) {
              new TMessage('info', 'Título não regitrado no PJBank, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
           // if ( $object->nosso_numero == '' ) {
           //   new TMessage('info', 'Título com instrução de boleto (nosso número), operação não permitida !'); // success message
           //   TTransaction::close(); // close the transaction
           //   return;
                
           // }
            
            $condominio = new Condominio($object->condominio_id);
            $unidade = new Unidade($object->unidade_id);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $classe = new PlanoContas($object->classe_id);
            
            // verifica se o condomínio está credenciado no pjbank
            if ($condominio->credencial_pjbank == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                TTransaction::close(); // close the transaction
                return;
            }
           
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/".$object->pjbank_id_unico,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank
              ),));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $pjbank=json_decode($response, true);
                //var_dump($pjbank);
                //var_dump($pjbank[0]['link_info']);
                
                //$link1 = $pjbank[0]['link'];
                //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                
                $link2 = $pjbank[0]['link_info'];
                TScript::create("var win = window.open('{$link2}', '_blank'); win.focus();");
                
                //file_put_contents('app/output/boleto.pdf', $link2);
                //TPage::openFile('app/output/boleto.pdf');
                //echo $response;
            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * 
     */
    public function onPJBankBoleto($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado nao permite a operação
            if ( $object->situacao != '0' ) {
              new TMessage('info', 'Título com movimentação, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            if ( $object->boleto_status != '2' ) {
              new TMessage('info', 'Título não regitrado no PJBank, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            if ( $object->nosso_numero == '' ) {
              new TMessage('info', 'Título com instrução de boleto (nosso número), operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            $condominio = new Condominio($object->condominio_id);
            $unidade = new Unidade($object->unidade_id);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $classe = new PlanoContas($object->classe_id);
            
            // verifica se o condomínio está credenciado no pjbank
            if ($condominio->credencial_pjbank == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                TTransaction::close(); // close the transaction
                return;
            }
           
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/".$object->pjbank_id_unico,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank
              ),));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $pjbank=json_decode($response, true);
                //var_dump($pjbank);
                //var_dump($pjbank[0]['link_info']);
                
                $link1 = $pjbank[0]['link'];
                TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                
                //$link1->save("app/output/teste.pdf");
                //parent::openFile("app/output/teste.pdf");
                
                //$target_folder = 'files/teste.pdf' ;
                ////$target_file   = $target_folder . '/' .$form-> anexo;
                //@mkdir($target_folder);
                //rename($link1, $target_file);
                
                //echo $response;
            } 
                            
            TTransaction::close();
            
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
        TSession::setValue('ContasReceberListagem_filter_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberListagem_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberListagem_filter_situacao',   NULL);
        
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


        // filtros obrigatorios
        //$filter = new TFilter('condominio_id', '=', TSession::getValue('id_condominio')); // create the filter
        //$criteria->add(new TFilter('unidade_id', '=', TSession::getValue('id_unidade'))); // add the session filter
            
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


            //if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
            //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
            //}


            //if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
            //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
           // }


            //if (TSession::getValue('ContasReceberListagem_filter_situacao')) {
            //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_situacao')); // add the session filter
            //}


            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            $criteria->add(new TFilter('unidade_id', '=', TSession::getValue('id_unidade'))); // add the session filter
            
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
                    
                    $plano_contas = new PlanoContas($object->classe_id);
                    $object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    $unidade = new Unidade( $object->unidade_id );
                    $proprietario = new Pessoa( $unidade->proprietario_id );
                    
                    //$object->proprietario_id = $unidade->descricao . '-' . $proprietario->nome;
                    
                    $object->proprietario_id = '<span style="color:black">'. $unidade->bloco_quadra . ' ' . $unidade->descricao . '-' . $proprietario->nome . ' ' . ' </span>' .
                                           '<br> <i class="fa barcode "> '. ' ' . $object->pjbank_linhaDigitavel . '</i>' .
                                           '<br> <i class="fa:link"> ' . $object->pjbank_linkBoleto . '</i>';
                    
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