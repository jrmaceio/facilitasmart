<?php
/**
 * ContasReceberConferenciaMesIncorporadora Listing
 * @author  <your name here>
 */
class ContasReceberConferenciaMesIncorporadora extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ContasReceberConferenciaMesIncorporadora');
        $this->form->setFormTitle('Consulta titulos por unidade');
        

        // create the form fields
        $mes_ref = new TEntry('mes_ref');
        
        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);

        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);

        // add the fields
        $this->form->addFields( [ new TLabel('Mês de Referência') ], [ $mes_ref ], [ new TLabel('Conta Fechamento') ], [ $conta_fechamento_id ] );
        $this->form->addFields( [new TLabel('Classe')], [$classe_id], [new TLabel('Unidade')], [$unidade_id] );
        
        // set sizes
        $mes_ref->setSize('100%');
        $conta_fechamento_id->setSize('100%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        //$btn = $this->form->addAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog');
        //$btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        //$column_condominio_id = new TDataGridColumn('condominio_id', 'Condomínio', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref.', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'center');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_responsavel = new TDataGridColumn('nome_responsavel', 'Responsável', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_vlr_pago = new TDataGridColumn('valor_pago', 'Vlr Pago', 'right');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Dt Pag.', 'right');
        //$column_conta_fechamento_id = new TDataGridColumn('conta_fechamento_id', 'Cta Fech', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_responsavel);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_vlr_pago);
        $this->datagrid->addColumn($column_dt_pagamento);
        //$this->datagrid->addColumn($column_conta_fechamento_id);

        $column_classe_id->setTransformer( function($value, $object, $row) {
            $classe = new PlanoContas($value);
            return $classe->descricao;
        });
        
        $column_situacao->setTransformer( function($value, $object, $row) {
            $class = ($value=='0') ? 'danger' : 'success';
            $label = ($value=='1') ? 'Pago' : 'Aberto';            
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        
        $column_vlr_pago->setTransformer( $format_value );
        $column_valor->setTransformer( $format_value );
        
        
        //$column_dt_lancamento->setTransformer( function($value, $object, $row) {
        //    $date = new DateTime($value);
        //    return $date->format('d/m/Y');
        //});
        
        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_dt_pagamento->setTransformer( function($value, $object, $row) {
            if (empty($value)) {
                return ' ';
            } else {
                $date = new DateTime($value);
                return $date->format('d/m/Y');
            }
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
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
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
            
            if ( $object->boleto_status != '2' ) {
              new TMessage('info', 'Título não regitrado no PJBank, operação não permitida !'); // success message
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
              new TMessage('info', 'Título sem instrução de boleto (nosso número), operação não permitida !'); // success message
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
        TSession::setValue('ContasReceberList_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberList_filter_conta_fechamento_id',   NULL);
        TSession::setValue('ContasReceberList_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberList_filter_unidade_id',   NULL);

        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "$data->mes_ref"); // create the filter
            TSession::setValue('ContasReceberList_filter_mes_ref',   $filter); // stores the filter in the session
        }

        if (isset($data->conta_fechamento_id) AND ($data->conta_fechamento_id)) {
            $filter = new TFilter('conta_fechamento_id', '=', "$data->conta_fechamento_id"); // create the filter
            TSession::setValue('ContasReceberList_filter_conta_fechamento_id',   $filter); // stores the filter in the session
        }

        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', '=', "$data->classe_id"); // create the filter
            TSession::setValue('ContasReceberList_filter_classe_id',   $filter); // stores the filter in the session
        }

        // obrigatorio - if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "$data->unidade_id"); // create the filter
            TSession::setValue('ContasReceberList_filter_unidade_id',   $filter); // stores the filter in the session
        //}
        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data', $data);
        
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
            
            $string = new StringsUtil;
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 10;
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
            
            $data_ate = date('Y-m-d');
            
            if (TSession::getValue('ContasReceberList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_conta_fechamento_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_conta_fechamento_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberList_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_classe_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasReceberList_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_unidade_id')); // add the session filter
            }

            // somente um condomínio
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('dt_vencimento', '<=', $data_ate)); // mostra so os com vencimento menor que hoje

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
                    $unidade = new Unidade($object->unidade_id);
                    $object->unidade_id = $unidade->quadra_lote . ' ' .$unidade->descricao;
                    
                    $object->nome_responsavel = substr($object->nome_responsavel, 0, 25);

                    if( $object->situacao == '0' ){
                        $object->dt_pagamento = '';
                        
                    }
                                                            
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
