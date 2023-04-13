<?php

/**
 
 */
class BoletosListagem extends TPage
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
            
            parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
                        
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
                
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
        $situacao->addItems(array( 1=>'Emitido',
                                   2=>'Enviado',
                                   3=>'Vinculado',
                                   4=>'Pendente',
                                   5=>'Registrado',
                                   6=>'Baixado',
                                   7=>'Invalidado',
                                   8=>'Rejeitado'
                                   ));
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
                                [new TLabel('Status Boleto')], [$situacao],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id],[new TLabel('Classe')], [$classe_id]);
        
        // mascaras
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_ultima_alteracao->setMask('dd/mm/yyyy');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data1') );
        
        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        //$this->form->addAction('Informe', new TAction([$this, 'onInform']), 'fa:barcode  #69aa46');
        $this->form->addAction('Registrar Lote', new TAction([$this, 'onRegLote']), 'fa:bank #69aa46');
        // so habilitada em casos administrativos 
        $this->form->addAction('Invalidar Lote', new TAction([$this, 'onInvalidarLote']), 'fa:bank #69aa46');
       
        //$this->form->addAction('PDFs', new TAction([$this, 'onSavePDF']), 'fa:barcode  #69aa46');
                
        // cria botão para imprimir boletos selecionados
        //$this->button2 = new TButton('imprimir_collection');
        //$this->impressaoAction = new TAction(array($this, 'onImpressaoEmLote'));
        //$this->button2->setAction($this->impressaoAction, 'Imprimir selecionados');
        //$this->button2->setImage('fa:barcode black fa-lg');
        //$this->form->addField($this->button2);
            
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
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'left');
        $column_boleto_status = new TDataGridColumn('boleto_status', 'St Bol.', 'right');
        $column_proprietario = new TDataGridColumn('proprietario_id', 'Proprietário', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_download = new TDataGridColumn('pjbank_linkBoleto', 'Download', 'center');
        //$column_situacao = new TDataGridColumn('situacao', 'Situação', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_boleto_status);
        $this->datagrid->addColumn($column_proprietario);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_download);
       // $this->datagrid->addColumn($column_situacao);
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);

        // define the transformer method over image
        $column_download->setTransformer( function($value, $object, $row) {
            $arquivo = 'arquivo1.pdf';
            //$nome_variavel = "<a target='_blank' style='width:100%' href='{$value}'>Boleto: <b style='color:blue;'>{$value}</b></a>";
            
            //$nome_variavel = "<a target='_blank' style='width:100%' href='{$value}' download='{$arquivo}' '>Click: <b style='color:blue;'>{$value}</b></a>";
            $nome_variavel = "<a href='{$value}' > Baixar </a>";
            return $nome_variavel ;
            
            //<a href="LocalDoArquivo" download="NomeDoArquivo"> Texto </a>
            //<a href="http://pt.stackoverflow.com/questions/122672/como-for%c3%a7ar-o-download-de-um-arquivo-de-texto" download="Baixou ao invés de acessar"> Baixar </a>
        });
        
        $column_proprietario->setTransformer( function($value, $object, $row) {
            $unidade = new Unidade( $object->unidade_id );
            $proprietario = new Pessoa( $unidade->proprietario_id );
                   
            $formatado = '<span style="color:black">'. $unidade->bloco_quadra . ' ' . $unidade->descricao . '-' . $proprietario->nome . ' ' . ' </span>' .
                         '<br> <i class="fa barcode "> '. ' ' . $object->pjbank_linhaDigitavel . '</i>' .
                         '<br> <i class="fa:link"> ' . $object->pjbank_linkBoleto . '</i>';
                       
            return $formatado;
        });

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
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
                $label = 'Enviado';
            }  
            
            if ($value=='3') {
                $class = 'warning';
                $label = 'Vinculado';
            } 

            if ($value=='4') {
                $class = 'warning';
                $label = 'Pendente';
            }   
            
            if ($value=='5') {
                $class = 'warning';
                $label = 'Registrado';
            }
            
            if ($value=='6') {
                $class = 'warning';
                $label = 'Baixado';
            }
            
            if ($value=='7') {
                $class = 'warning';
                $label = 'Invalidado';
            }

            if ($value=='8') {
                $class = 'warning';
                $label = 'Rejeitado';
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
        
        //$action_pjbankPDF = new TDataGridAction(array($this, 'onSavePDF'));
        //$action_pjbankPDF->setButtonClass('btn btn-default btn-sm');
        //$action_pjbankPDF->setLabel('PDF');
        //$action_pjbankPDF->setImage('far:folder-open black');
        //$action_pjbankPDF->setField('id');
        //$this->datagrid->addAction($action_pjbankPDF);

        $action_pjbank = new TDataGridAction(array($this, 'onPJBankReg'));
        $action_pjbank->setButtonClass('btn btn-default btn-sm');
        $action_pjbank->setLabel('Registrar');
        $action_pjbank->setImage('far:folder-open green');
        $action_pjbank->setField('id');
        $this->datagrid->addAction($action_pjbank);
        
        $action_pjbank_cancela = new TDataGridAction(array($this, 'onPJBankCancelar'));
        $action_pjbank_cancela->setButtonClass('btn btn-default btn-sm');
        $action_pjbank_cancela->setLabel('Invalidar');
        $action_pjbank_cancela->setImage('far:folder-open red');
        $action_pjbank_cancela->setField('id');
        $this->datagrid->addAction($action_pjbank_cancela);
        
        // create Informacao do regitro de boleto 
        $action_inf = new TDataGridAction(array($this, 'onPJBankInfo'));
        $action_inf->setButtonClass('btn btn-default');
        $action_inf->setLabel(('Informação'));
        $action_inf->setImage('fas:folder-open');
        $action_inf->setField('id');
        $this->datagrid->addAction($action_inf);
        
        // create IMPRIMIR action datagrid 
        $action_edit = new TDataGridAction(array($this, 'onPJBankBoleto'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(('Boleto'));
        $action_edit->setImage('fa:barcode fa-lg black');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create IMPRIMIR action datagrid - POR GRUPO
        //$action_infogrupo = new TDataGridAction(array($this, 'onPJBankInfoGrupo'));
        //$action_infogrupo->setButtonClass('btn btn-default');
        //$action_infogrupo->setLabel(('Boleto Grupo'));
        //$action_infogrupo->setImage('fa:barcode fa-lg green');
        //$action_infogrupo->setField('id');
        //$this->datagrid->addAction($action_infogrupo);
        
        // concede novo vencimento e desconto
        $action_edit = new TDataGridAction(array($this, 'onPJBankBoletoNewVenc'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(('Nova data'));
        $action_edit->setImage('fa:share-alt red');
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
    
               
    public function onPJBankCancelar($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onPJBankInvalidar'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Invalidar o boleto, este procedimento vai cancelar o boleto que esteja com o morador ?', $action);
    }
    
    public function onPJBankInvalidar($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record

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
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/".$object->pjbank_pedido_numero,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "DELETE",
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
                
                new TMessage('info', $pjbank['msg']);

                $object->boleto_status = 7;
                $object->store();
            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function onPJBankBoletoNewVenc($param)
    {
        $form = new BootstrapFormBuilder('input_form');

        $id_receber = new TEntry('id_receber');
        $id_receber->style='text-align:left;float:left;font-family:Arial Narrow;';
        
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_limite_desc = new TDate('dt_limite_desc');
        $desconto_boleto_cobranca = new TEntry('desconto_boleto_cobranca');

        $vlr_original = new TEntry('vlr_original');
        $vlr_original->setNumericMask(2, ',', '.');
        $vlr_original->setSize('50%');
        
        $vlr_boleto = new TEntry('vlr_boleto');
        $vlr_boleto->setNumericMask(2, ',', '.');
        $vlr_boleto->setSize('50%');
                
        //var_dump($param);
        $id_receber->setValue($param['id']);
        $id_receber->setEditable(FALSE); 
        $id_receber->setSize('50%');
        
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_vencimento->setSize('50%');
        $dt_vencimento->setValue(date('d/m/Y')); 
        $dt_vencimento->addValidation('dt_vencimento', new TRequiredValidator);
        
        TTransaction::open('facilitasmart');
        $receber = new ContasReceber($param['id']);
        TTransaction::close();
        
        $vlr_original->setValue(number_format($receber->valor, 2, ',', ' '));
        
        // inicio calculo correção
        // juros 0.033 = 1% ao mes
        $perc_juros = 0.033;
        $perc_multa = 2;
        $time_inicial = strtotime($receber->dt_vencimento);
        $time_final = strtotime(date("Y/m/d"));
                    
        // Calcula a diferença de segundos entre as duas datas:
        $diferenca = $time_final - $time_inicial; // 19522800 segundos
        // Calcula a diferença de dias
        $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    
        $juros = $perc_juros * $dias;
                    
        $juros = (($juros * $receber->valor) / 100);
                    
        if($dias<=0)
        {
            $multa = 0;
            $juros = 0;
            
        } else
        {
            $multa = (($perc_multa * $receber->valor) / 100);
            
        }
                    
        $vlr_corrigido = $receber->valor + $multa + $juros;
        $vlr_corrigido = number_format($vlr_corrigido, 2, ',', ' ');
        // fim calculo correção
        
        $vlr_boleto->setValue($vlr_corrigido);
        
        $dt_limite_desc->setMask('dd/mm/yyyy');
        $dt_limite_desc->setSize('50%');
        $dt_limite_desc->setValue(date('d/m/Y')); 
        $dt_limite_desc->addValidation('$dt_limite_desc', new TRequiredValidator);
        
        $desconto_boleto_cobranca->setNumericMask(2, ',', '.');  
        $desconto_boleto_cobranca->setSize('50%');
        
        $desconto_boleto_cobranca->setValue(number_format($receber->desconto_boleto_cobranca, 2, ',', ' ')); 
        
        $form->addFields( [new TLabel('Id')], [$id_receber]);
        $form->addFields( [new TLabel('Valor Original')], [$vlr_original]);
        $form->addFields( [new TLabel('Valor Boleto')], [$vlr_boleto]);
        $form->addFields( [new TLabel('Vencimento')], [$dt_vencimento]);
        $form->addFields( [new TLabel('Dt Limite Desc')], [$dt_limite_desc]);
        $form->addFields( [new TLabel('Valor Desc.')], [$desconto_boleto_cobranca] ); 

        $form->addAction('Confirma', new TAction([__CLASS__, 'onConfirm1']), 'fa:save green');
        $form->addAction('Cancela', new TAction([__CLASS__, 'onConfirm2']), 'far:check-circle blue');
        
        // show the input dialog
        new TInputDialog('Boleto - 2a via com novo vencimento e nova data limite de desconto !', $form);
        
    }
    
    /**
     * Show the input dialog data
     */
    public static function onConfirm1( $param )
    {
        $string = new StringsUtil;
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            $receber = new ContasReceber($param['id_receber']);
            
            //var_dump($receber);
            //return;
        
            $receber->dt_vencimento = TDate::date2us($param['dt_vencimento']);
            $receber->dt_limite_desconto_boleto_cobranca = TDate::date2us($param['dt_limite_desc']);
            $receber->valor = $string->desconverteReais($param['vlr_boleto']);
            $receber->desconto_boleto_cobranca = $string->desconverteReais($param['desconto_boleto_cobranca']);
             
            $receber->store(); // stores the object
        
            TTransaction::close();
            
            TApplication::loadPage(__CLASS__,'onReload');
            
            $param['key'] = $param['id_receber'];
            
            // chama o registro self::PJBankReg($param);
            self::PJBankReg($param);
                        
            //new TMessage('info', 'Confirm1 : ' . str_replace(',', '<br>', json_encode($param)));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Show the input dialog data
     */
    public static function onConfirm2( $param )
    {
        //new TMessage('info', 'Confirm2 : ' . str_replace(',', '<br>', json_encode($param)));
    }
    
    public function onRegLote($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'RegistraLote'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Registrar todos os títulos da tela ?', $action);
    }
    
    /**
    * Não faz a chamada por lote, faz individual cada boleto
    */
    public function RegistraLote($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 40;
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

            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            //$criteria->add(new TFilter('boleto_status', '=', '1')); // add the session filter
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
         
            // iterate the collection of active records
            foreach ($objects as $object) {
                // faz o registro somente dos nao emitidos
                if ($object->boleto_status != '2') {
                    $key = $object->id;
                    $titulo = new ContasReceber($key, FALSE); // instantiates the Active Record
                    
                    $condominio = new Condominio($titulo->condominio_id);
                    $unidade = new Unidade($titulo->unidade_id);
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    $classe = new PlanoContas($titulo->classe_id);
                        
                    // verifica se o condomínio está credenciado no pjbank
                    if ($condominio->credencial_pjbank == '') {
                        new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                        TTransaction::close(); // close the transaction
                        return;
                    }
            
                    $data_vencimento = new DateTime($titulo->dt_vencimento);
                    
                    $dias = 0;
                           
                    if ($object->desconto_boleto_cobranca > 0) {
                        $time_inicial = strtotime($object->dt_limite_desconto_boleto_cobranca);
                        $time_final = strtotime($object->dt_vencimento);
                        // Calcula a diferença de segundos entre as duas datas:
                        //$diferenca = $time_final - $time_inicial; // 19522800 segundos
                        $diferenca = $time_inicial - $time_final;
                        // Calcula a diferença de dias
                        $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                        $dias = $dias;
                
                    };
                     
                    if ( $pessoa->pessoa_fisica_juridica == 'J' ) {
                        $cpf_cnpj = $pessoa->cnpj;
                    } else {
                        $cpf_cnpj = $pessoa->cpf;
                    }
            
                    $nome = substr($pessoa->nome, 0, 80);
                        
                    $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid.' . $unidade->bloco_quadra . '- ' . $unidade->descricao;
                    
                    if ($unidade->texto_complemento_titulo != null) {
                        $texto = $texto . ' Dados Adicionais: ' . $unidade->texto_complemento_titulo; 
                    }
                    
                    // Faz o registro do boleto
                    $data = json_encode(array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$object->valor,
                        'juros'=>$object->juros_boleto_cobranca,
                        'multa'=>$object->multa_boleto_cobranca,
                        'desconto'=>$object->desconto_boleto_cobranca,
                        'diasdesconto1'=>$dias,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco . ',' . $unidade->bloco_quadra . '- ' . $unidade->descricao,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'',
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'https://facilitasmart.facilitahomeservice.com.br/app/images/logo.png',
                        'texto'=>$texto,
                        'grupo'=>$object->mes_ref, // link para impressão em lote
                        'webhook'=>'https://facilitasmart.facilitahomeservice.com.br/retorno.php',
                        'pedido_numero'=>$object->id));
                        ////'https://facilitagestor.000webhostapp.com/logo.png',
                    
                    //var_dump($data);
                        
                    $curl = curl_init();
            
                    curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => false,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "POST",
                          CURLOPT_POSTFIELDS => $data,
                          CURLOPT_HTTPHEADER => array("Content-Type: application/json"),));
                        
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                
                    //var_dump($response);
                    
                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        $pjbank=json_decode($response);
                             
                        // considerando que um valor maior ou igual a 300 sempre representa um erro
                        if ($pjbank->status < '300') {    
                            $objectRec = new ContasReceber($object->id);               
                            $objectRec->boleto_status = '2';
                            $objectRec->nosso_numero = $pjbank->nossonumero;
                            $objectRec->pjbank_pedido_numero = $object->id;
                            $objectRec->pjbank_id_unico = $pjbank->nossonumero;
                            $objectRec->pjbank_banco_numero = $pjbank->banco_numero;
                            $objectRec->pjbank_token_facilitador = $pjbank->token_facilitador;
                            $objectRec->pjbank_credencial = $pjbank->credencial;
                            $objectRec->pjbank_linkBoleto = $pjbank->linkBoleto;
                            $objectRec->pjbank_linkGrupo = $pjbank->linkGrupo;
                            $objectRec->pjbank_linhaDigitavel = $pjbank->linhaDigitavel;
                            $objectRec->store(); // update the object in the database
                                 //$link1 = $pjbank->linkBoleto;
                                 //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                    
                        } else {
                            new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                        } 
                                           
                    }//trataemento de erro
                    
                    //return;
                         
                }// boleto status
                    
            }//foreach
     
            //atualizar a tela
            //$this->datagrid->clear();
            //if ($objects)
            //{
                // iterate the collection of active records
            //    foreach ($objects as $object)
            //    {
                    // add the object inside the datagrid
            //        $this->datagrid->addItem($object);
            //    }
            //}
            
            // reset the criteria for record count
            //$criteria->resetProperties();
            //$count= $repository->count($criteria);
            
            //$this->pageNavigation->setCount($count); // count of records
            //$this->pageNavigation->setProperties($param); // order, page
            //$this->pageNavigation->setLimit($limit); // limit
            //fim da atualização da tela
            
            // close the transaction
            TTransaction::close();
            //$this->loaded = true;
            
            //////// ver se atualiza TApplication::loadPage(__CLASS__,'onReload');
        } //try
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }// funcao
    
    /**
    * chama ao registro de 50 boletos por vez
    *
    public function RegistraLote2($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 50;
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

            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            $criteria->add(new TFilter('boleto_status', '=', '1')); // add the session filter
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $cobranca = array();
            
            for ($i = 1; ; $i++) {
                if ($i > 50) {
                    break;
                }   
                
                foreach ($objects as $object) {
                // faz o registro somente dos nao emitidos
                if ($object->boleto_status!= '2') {
                    $key = $object->id;
                    $titulo = new ContasReceber($key, FALSE); // instantiates the Active Record
                    
                    $condominio = new Condominio($titulo->condominio_id);
                    $unidade = new Unidade($titulo->unidade_id);
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    $classe = new PlanoContas($titulo->classe_id);
                        
                    // verifica se o condomínio está credenciado no pjbank
                    if ($condominio->credencial_pjbank == '') {
                        new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                        TTransaction::close(); // close the transaction
                        return;
                    }
            
                    $data_vencimento = new DateTime($titulo->dt_vencimento);
                    
                    $dias = 0;
                           
                    if ($object->desconto_boleto_cobranca > 0) {
                        $time_inicial = strtotime($object->dt_limite_desconto_boleto_cobranca);
                        $time_final = strtotime($object->dt_vencimento);
                        // Calcula a diferença de segundos entre as duas datas:
                        //$diferenca = $time_final - $time_inicial; // 19522800 segundos
                        $diferenca = $time_inicial - $time_final;
                        // Calcula a diferença de dias
                        $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                        $dias = $dias;
                
                    };
                     
                    if ( $pessoa->pessoa_fisica_juridica == 'J' ) {
                        $cpf_cnpj = $pessoa->cnpj;
                    } else {
                        $cpf_cnpj = $pessoa->cpf;
                    }
            
                    $nome = substr($pessoa->nome, 0, 80);
                        
                    $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid.' . $unidade->bloco_quadra . '- ' . $unidade->descricao;
                    
                    if ($unidade->texto_complemento_titulo != null) {
                        $texto = $texto . ' Dados Adicionais: ' . $unidade->texto_complemento_titulo; 
                    }
                    
                    // Faz o registro do boleto
                    $data = json_encode(array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$object->valor,
                        'juros'=>$object->juros_boleto_cobranca,
                        'multa'=>$object->multa_boleto_cobranca,
                        'desconto'=>$object->desconto_boleto_cobranca,
                        'diasdesconto1'=>$dias,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'',
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'https://facilitasmart.facilitahomeservice.com.br/app/images/logo.png',
                        'texto'=>$texto,
                        'grupo'=>$object->mes_ref, // link para impressão em lote
                        'webhook'=>'https://facilitasmart.facilitahomeservice.com.br/retorno.php',
                        'pedido_numero'=>$object->id));
                        ////'https://facilitagestor.000webhostapp.com/logo.png',
                    
                array_push($cobranca, $data);
                }
                }  
            }
               
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => false,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "POST",
                          CURLOPT_POSTFIELDS => $cobranca,
                          CURLOPT_HTTPHEADER => array("Content-Type: application/json"),));
                        
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $pjbank=json_decode($response);
                
                // FALTA --- for dos 50 boletos para atualizar no contas a receber             
                        // considerando que um valor maior ou igual a 300 sempre representa um erro
                if ($pjbank->status < '300') {    
                            $objectRec = new ContasReceber($object->id);               
                            $objectRec->boleto_status = '2';
                            $objectRec->nosso_numero = $pjbank->nossonumero;
                            $objectRec->pjbank_pedido_numero = $object->id;
                            $objectRec->pjbank_id_unico = $pjbank->nossonumero;
                            $objectRec->pjbank_banco_numero = $pjbank->banco_numero;
                            $objectRec->pjbank_token_facilitador = $pjbank->token_facilitador;
                            $objectRec->pjbank_credencial = $pjbank->credencial;
                            $objectRec->pjbank_linkBoleto = $pjbank->linkBoleto;
                            $objectRec->pjbank_linkGrupo = $pjbank->linkGrupo;
                            $objectRec->pjbank_linhaDigitavel = $pjbank->linhaDigitavel;
                            $objectRec->store(); // update the object in the database
                                 //$link1 = $pjbank->linkBoleto;
                                 //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                    
                } else {
                    new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                } 
                                           
            }//trataemento de erro
                    
                 
            //atualizar a tela
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
            //fim da atualização da tela
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
            
            //////// ver se atualiza TApplication::loadPage(__CLASS__,'onReload');
        } //try
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }// funcao*/
    
    /**
     * 
     */
    public function onPJBankInfoGrupo($param)
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
            
            if ( $object->boleto_status == '1' ) {
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
                //var_dump($pjbank[0]);
                //$link1 = $pjbank[0]['link'];
                //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                
                $link2 = $pjbank[0]['linkGrupo'];
                TScript::create("var win = window.open('{$link2}', '_blank'); win.focus();");
                
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
     * teste recanto https://api.pjbank.com.br/recebimentos/e145cbe5192eca743bad7c95b65426a6a20ea06b/transacoes/50620249
     */
    public function onPJBankInfo($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado nao permite a operação
            //if ( $object->situacao != '0' ) {
            //  new TMessage('info', 'Título com movimentação, operação não permitida !'); // success message
            //  TTransaction::close(); // close the transaction
            //  return;
                
           // }
           // 
           if ( $object->boleto_status == '1'  ) {
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
            
            if ( $object->boleto_status == '1' ) {
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
               
                $link1 = $pjbank[0]['link'];
                TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                

                
                //$embed = new TElement('object');
                //$embed->data  = "download.php?file=files/documents/{$id}/".$object->filename;
                //$embed->type  = 'application/pdf';
                //$embed->style = "width: 100%; height:calc(100% - 10px)";
                        
                //$win = TWindow::create( $link1, 0.8, 0.8 );
                //$win->add( $embed );
                //$win->show();
                
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
    public function onSavePDF($param)
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
            
            if ( $object->boleto_status == '1' ) {
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
               
                $link1 = $pjbank[0]['link'];
                TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                
                //file://Air-de-Junior.lan/Users/juniorandrade/Downloads
                $linkboleto = explode("/", $object->pjbank_linkBoleto);
                
                //$source_file = "c:\Users\Leilane\Downloads\\" . $linkboleto[4] . ".pdf";
                $source_file = "Users/juniorandrade/Downloads/" . $linkboleto[4] . ".pdf";


                $target_file = "teste1.pdf";
                var_dump($target_file);
                var_dump($source_file);

                // renomea o arquivo
                if (file_exists($source_file))
                {
                    var_dump('entrei');
                    // move to the target directory
                    rename($source_file, $target_file);
                }

                // link para abrir doownload echo '<a href="{$link1}" download="ficheiro0503.pdf">Boleto</a>';
                
                ///$nome_pdf = $pjbank[0]['link'];
                //TPage::openFile($nome_pdf);
                
            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    
    public function onPJBankReg($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'PJBankReg'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Registrar o título ID '.$param['id'].' no PJBank ?', $action);
    }
    
    /**
     *
     */
    public function PJBankReg($param)
    {
        // atualizado para gerar split junto com o boleto, não implementado split em registro de boleto por lote
        
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
            
            if ( $object->boleto_status == '3' ) {
              new TMessage('info', 'Título vinculado a outro(s), operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            /*pode reenviar - o sistema entende como uma alteracao de dados, if ( $object->boleto_status != '1' ) {
              new TMessage('info', 'Título com instrução de boleto (status), operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            if ( $object->nosso_numero != '' ) {
              new TMessage('info', 'Título com instrução de boleto (nosso número), operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }*/
            
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
            
            //$object->dt_vencimento = DateTime::createFromFormat('Y-m-d', $object->dt_vencimento)->format( 'm/d/Y' ); 
            
            $data_vencimento = new DateTime($object->dt_vencimento);
            
            $dias = 0;
            
            if ($object->desconto_boleto_cobranca > 0) {
                //var_dump('aqui');
                $time_inicial = strtotime($object->dt_limite_desconto_boleto_cobranca);
                $time_final = strtotime($object->dt_vencimento);
                // Calcula a diferença de segundos entre as duas datas:
                //$diferenca = $time_final - $time_inicial; // 19522800 segundos
                $diferenca = $time_inicial - $time_final;
                // Calcula a diferença de dias
                $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                $dias = $dias;
                
            };
            
            if ( $pessoa->pessoa_fisica_juridica == 'J' ) {
                $cpf_cnpj = $pessoa->cnpj;
            } else {
                $cpf_cnpj = $pessoa->cpf;
            }
            
            $nome = substr($pessoa->nome, 0, 80);
            
            //$texto = '';
            $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid. ' . $unidade->bloco_quadra . '-' . $unidade->descricao;
            
            if ($condominio->id == 28 and $object->mes_ref == '10/2021') {
                $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid. ' . $unidade->bloco_quadra . '-' . $unidade->descricao .
                    '
                    
                    
                    OURO VERDE PRAIA
                    
                    BOLETO DA 1a ARRECADAÇÃO
                                        
                    R$ 395,00 - R$ 100,00 (DESCONTO ATÉ O VENCIMENTO) = R$ 295,00
                                              
                                              
                    * Pague seu boleto em dia, o inadimplente está sujeito a negativação e cobrança judicial.                                              
                    ';
                         
            }
            
            if ($condominio->id == 28 and $object->mes_ref == '11/2021') {
                $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid. ' . $unidade->bloco_quadra . '-' . $unidade->descricao .
                    '
                    
                    
                    OURO VERDE PRAIA
                    
                    BOLETO REFERENTE 11/2021
                                        
                    R$ 395,00 - R$ 100,00 (DESCONTO ATÉ O VENCIMENTO) = R$ 295,00
                                              
                                              
                    * Pague seu boleto em dia, o inadimplente está sujeito a negativação e cobrança judicial.                                            
                    ';
                         
            }
            
            if ($condominio->id == 28 and $object->mes_ref == '12/2021') {
                $texto = 'Referência :' . 'Mês Ref.: ' . $object->mes_ref . ' Descrição: ' . $object->descricao . 
                    ' Unidade ' . 'Bl/Qd-Unid. ' . $unidade->bloco_quadra . '-' . $unidade->descricao .
                    '
                    
                    
                    OURO VERDE PRAIA
                    
                    BOLETO REFERENTE 12/2021
                                        
                    R$ 395,00 - R$ 100,00 (DESCONTO ATÉ O VENCIMENTO) = R$ 295,00
                                              
                                              
                    * Pague seu boleto em dia, o inadimplente está sujeito a negativação e cobrança judicial.                                              
                    ';
                         
            }
            
                    
            if ($unidade->texto_complemento_titulo != null) {
                $texto = $texto . ' Dados Adicionais: ' . $unidade->texto_complemento_titulo; 
            }
            
            if ($object->split_active == 'Y') {
                $adm_split = new Condominio($object->split_administradora_id);
                
                //var_dump($adm_split);
                
                /*$split = '{
                        "nome":"' . $adm_split->split_nome . '",
  		                "cnpj":"' . $adm_split->split_cnpj . '",
  		                "banco_repasse":"' . $adm_split->split_banco_repasse . '",
  		                "agencia_repasse":"' . $adm_split->split_agencia_repasse . '",
  		                "conta_repasse":"' . $adm_split->split_conta_repasse . '",
  		                "tipo_conta_repasse":"' . $adm_split->split_tipo_conta_repasse . '",
  		                "valor_porcentagem":"0.00",
  		                "porcentagem_encargos":"0.00", 
  		                "porcentagem_multa":"0.00",
  		                "porcentagem_juros":"0.00",
  		                "porcentagem_desconto":"0.00", 
  		                "valor_fixo":"' . $object->split_valor_fixo . '"}';
                */
                $split = array(
                        'nome'=> $adm_split->split_nome,
  		                'cnpj'=> $adm_split->split_cnpj,
  		                'banco_repasse'=> $adm_split->split_banco_repasse,
  		                'agencia_repasse'=>$adm_split->split_agencia_repasse,
  		                'conta_repasse'=>$adm_split->split_conta_repasse,
  		                'tipo_conta_repasse'=>$adm_split->split_tipo_conta_repasse,
  		                'porcentagem_encargos'=>0.00, 
  		                'porcentagem_multa'=>0.00,
  		                'porcentagem_juros'=>0.00,
  		                'porcentagem_desconto'=>0.00, 
  		                'valor_fixo'=>$object->split_valor_fixo);
                
                //var_dump($split);
                
                $data = array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$object->valor,
                        'juros'=>$object->juros_boleto_cobranca,
                        'multa'=>$object->multa_boleto_cobranca,
                        'desconto'=>$object->desconto_boleto_cobranca,
                        'diasdesconto1'=>$dias,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco . ',' . $unidade->bloco_quadra . '- ' . $unidade->descricao,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'',
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'https://facilitasmart.facilitahomeservice.com.br/app/images/logo.png',
                        'texto'=>$texto,
                        'grupo'=>$object->mes_ref, // link para impressão em lote
                        'webhook'=>'https://facilitasmart.facilitahomeservice.com.br/retorno.php',
                        'pedido_numero'=>$object->id,
                        'pix'=>'pix-e-boleto',
                        'split'=>[$split]
                        );
                 //var_dump($data);
                 $data = json_encode($data);
            } else {
                $data = json_encode(array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$object->valor,
                        'juros'=>$object->juros_boleto_cobranca,
                        'multa'=>$object->multa_boleto_cobranca,
                        'desconto'=>$object->desconto_boleto_cobranca,
                        'diasdesconto1'=>$dias,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco . ',' . $unidade->bloco_quadra . '- ' . $unidade->descricao,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'',
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'https://facilitasmart.facilitahomeservice.com.br/app/images/logo.png',
                        'texto'=>$texto,
                        'grupo'=>$object->mes_ref, // link para impressão em lote
                        'webhook'=>'https://facilitasmart.facilitahomeservice.com.br/retorno.php',
                        'pedido_numero'=>$object->id,
                        'pix'=>'pix-e-boleto'                       
                        ));
            
            }
 
            //var_dump($data);         
                    
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
              ),));
            
            // executa 20 x tentando registrar
            for($i =1; $i < 20; $i++){
              //echo "O Valor de I = ".$id;
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                
                if ($err) {
                    echo "cURL Error #:" . $err;
                    //new TMessage('Erro', "cURL Error #:" . $err . ' - Não foi possível registrar, repita a operação!');
                } else {
                    $i = 20; // sair
                
                    //echo $response;
                    $pjbank=json_decode($response);
                    //var_dump($pjbank);
                
                    /// considerando que um valor maior ou igual a 300 sempre representa um erro
                
                    if (isset($pjbank->status) and $pjbank->status) {
                
                        if ($pjbank->status < '300') {    
                        
                            //var_dump($pjbank);
                        
                            $objectRec = new ContasReceber($object->id);               
                        
                            $objectRec->boleto_status = '2';
                            $objectRec->nosso_numero = $pjbank->nossonumero;
                                        
                            $objectRec->pjbank_pedido_numero = $object->id;
                            $objectRec->pjbank_id_unico = $pjbank->nossonumero;
                            $objectRec->pjbank_banco_numero = $pjbank->banco_numero;
                            $objectRec->pjbank_token_facilitador = $pjbank->token_facilitador;
                            $objectRec->pjbank_credencial = $pjbank->credencial;
                            $objectRec->pjbank_linkBoleto = $pjbank->linkBoleto;
                            $objectRec->pjbank_linkGrupo = $pjbank->linkGrupo;
                            $objectRec->pjbank_linhaDigitavel = $pjbank->linhaDigitavel;
                    
                            $objectRec->store(); // update the object in the database
                    
                            //var_dump($object);
                            new TMessage('Boleto', 'Registrado com sucesso!'); // success message
                            
                            $link1 = $pjbank->linkBoleto;
                            TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                 
                            break;
                            
                        }else{
                            new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                        } 
                
                    } else {
                        //var_dump($pjbank);
                        new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                        
                        if ( $i > 18 ) {
                            new TMessage('Erro', 'Erro, várias tentativas ('.$i.') ' . $pjbank->msg . "(cURL Error #:" . $err.")");
                        }
                    }
                
                }   
                
            }
            //$response = curl_exec($curl);
            //$err = curl_error($curl);
            //curl_close($curl);
            ////new TMessage('Erro', 'Enviado ao PJBank, vou gravar!');
            
            //var_dump($response);
            
            /*if ($err) {
                echo "cURL Error #:" . $err;
                //new TMessage('Erro', "cURL Error #:" . $err . ' - Não foi possível registrar, repita a operação!');
            } else {
                //echo $response;
                $pjbank=json_decode($response);
                //var_dump($pjbank);
                
                /// considerando que um valor maior ou igual a 300 sempre representa um erro
                
                if (isset($pjbank->status) and $pjbank->status) {
                
                    if ($pjbank->status < '300') {    
                        
                        //var_dump($pjbank);
                        
                        $objectRec = new ContasReceber($object->id);               
                        
                        $objectRec->boleto_status = '4';
                        $objectRec->nosso_numero = $pjbank->nossonumero;
                    
                        if ($pjbank->pedido_numero == '') {
                            $pjbank->pedido_numero = $object->id;
                        }
                    
                    $objectRec->pjbank_pedido_numero = $pjbank->pedido_numero;
                    $objectRec->pjbank_id_unico = $pjbank->nossonumero;
                    $objectRec->pjbank_banco_numero = $pjbank->banco_numero;
                    $objectRec->pjbank_token_facilitador = $pjbank->token_facilitador;
                    $objectRec->pjbank_credencial = $pjbank->credencial;
                    $objectRec->pjbank_linkBoleto = $pjbank->linkBoleto;
                    $objectRec->pjbank_linkGrupo = $pjbank->linkGrupo;
                    $objectRec->pjbank_linhaDigitavel = $pjbank->linhaDigitavel;
                    
                    $objectRec->store(); // update the object in the database
                    
                    //var_dump($object);
                    new TMessage('Boleto', 'Registrado com sucesso!'); // success message 
                    
                    $this->loaded = true;
                    
                    // recarregar dados alterados
                    //$this->form->setData($object); // keep form data
                    /// nao funciona $this->onReload(); 
                    } 
                
                } else {
                    //var_dump($pjbank);
                    new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                }
                
            }*/ 
            
                           
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
                $param['order'] = 'unidade_id';
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
            if ($objects)
            {
                  
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    //$object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    //$object->dt_pagamento ? $object->dt_pagamento = $this->string->formatDateBR($object->dt_pagamento) : null;
                    //$object->dt_liquidacao ? $object->dt_liquidacao = $this->string->formatDateBR($object->dt_liquidacao) : null;
                    
                    //$plano_contas = new PlanoContas($object->classe_id);
                    //$object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    //$unidade = new Unidade( $object->unidade_id );
                    //$proprietario = new Pessoa( $unidade->proprietario_id );
                   
                    //$object->proprietario_id = '<span style="color:black">'. $unidade->bloco_quadra . ' ' . $unidade->descricao . '-' . $proprietario->nome . ' ' . ' </span>' .
                    //                       '<br> <i class="fa barcode "> '. ' ' . $object->pjbank_linhaDigitavel . '</i>' .
                    //                       '<br> <i class="fa:link"> ' . $object->pjbank_linkBoleto . '</i>';
                                                
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
    
    //public static function onImpressaoEmLote($param)
    //    {
    //        try
    //        {  
    //            TTransaction::open('db_guiansoft'); 
    //            // creates a repository for AlunoParcela
    //            $repository = new TRepository('AlunoParcela');
    //            if ($object->id)
    //            {
    //                $criteria->add(new TFilter('id', '=', "%{$object->id}%"));
    //            }
            
    //            // creates a criteria, ordered by id
    //            $criteria = new TCriteria;
    //            $order    = isset($param['order']) ? $param['order'] : 'id';
    //            $criteria->setProperty('order', $order);
                
    //            // load the objects according to criteria
    //            $objects = $repository->load($criteria);
                
    //            foreach ($objects as $object) 
    //            {
    //            var_dump($object);
    //                if($this->form->check_.$object->id)
    //                {
    //                    $pjbank = new PJBank(false,false);
    //                    $pjbank->setApikey('17769ece23a3b44230bbdf45d719f185cac6eeaa');
    //                    $pjbank->setSecret("67d2645e546c96efe56b60cb5c7e1501a0642ace");
    //                    $imprimir = new Impressao('carne');
    //                    $imprimir->setPedidoNumero(array(['numero_pedido']));
    //                    $emitido = $pjbank->impressaoEmLote($imprimir->prepare());
    //                }
    //            }
                
    //        }
    //            catch (Exception $e)
    //            {
    //                new TMessage('error',$e->getMessage().' linha '.$e->getLine().' file '.$e->getFile());
    //            }
    //    }     

public function onInvalidarLote($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'InvalidarLote'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Invalidar todos os títulos da tela ?', $action);
    }
    
/**
    * Não faz a chamada por lote, faz individual cada boleto
*/
public function InvalidarLote($param = NULL)
    {
        try
        {
           // open a transaction with database 'facilitasmart'
           TTransaction::open('facilitasmart');
            
           // creates a repository for ContasReceber
           $repository = new TRepository('ContasReceber');
           $limit = 20;
           // creates a criteria
           $criteria = new TCriteria;
           
           // default order
           if (empty($param['order']))
           {
               $param['order'] = 'dt_vencimento';
               $param['direction'] = 'asc';
           }
           
           //$criteria->setProperties($param); // order, offset
           //$criteria->setProperty('limit', $limit);
           

           //if (TSession::getValue('ContasReceberListagem_filter_id')) {
           //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_id')); // add the session filter
           //}


           //if (TSession::getValue('ContasReceberListagem_filter_mes_ref')) {
           //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_mes_ref')); // add the session filter
           //}


           //if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
           //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
           //}


           //if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
           //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
           //}


           //if (TSession::getValue('ContasReceberListagem_filter_situacao')) {
           //    $criteria->add(TSession::getValue('ContasReceberListagem_filter_situacao')); // add the session filter
           //}
            
            // ATENCAO !!!!!!!!!!!!!!!!!! NÃO USA O FILTRO DA TELA
           // filtros obrigatorios
           $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); 
           $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
           //$criteria->add(new TFilter('mes_ref', '=', '06/2022')); 
           //$criteria->add(new TFilter('boleto_status', '!=', '5'));
           //$criteria->add(new TFilter('classe_id', '=', '2')); // taxa de manutencao    
        
           // load the objects according to criteria
           $objects = $repository->load($criteria, FALSE);
           
           if (is_callable($this->transformCallback))
           {
               call_user_func($this->transformCallback, $objects, $param);
           }
            
            
            // iterate the collection of active records
            foreach ($objects as $object) {
                // faz o cancelamento somente dos nao invalidados
                if ($object->boleto_status != '7') {
                    $key = $object->id;
                    $titulo = new ContasReceber($key, FALSE); // instantiates the Active Record
                    
                    $condominio = new Condominio($titulo->condominio_id);
                    $unidade = new Unidade($titulo->unidade_id);
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    $classe = new PlanoContas($titulo->classe_id);
                        
                    // verifica se o condomínio está credenciado no pjbank
                    if ($condominio->credencial_pjbank == '') {
                        new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                        TTransaction::close(); // close the transaction
                        return;
                    }
            
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/".$titulo->pjbank_pedido_numero,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "DELETE",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank
              ),));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    //var_dump($response);
                    //return;
                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        $pjbank=json_decode($response, true);
                
                        //new TMessage('info', $pjbank['msg']);
                        //var_dump($pjbank);

                        $objectRec = new ContasReceber($titulo->id);               
                        $objectRec->boleto_status = '7';
                        $objectRec->store(); // update the object in the database
                       // return;
                    }
                   
                         
                }// boleto status
                    
            }//foreach
     
            //atualizar a tela
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
            //fim da atualização da tela
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
            
            //////// ver se atualiza TApplication::loadPage(__CLASS__,'onReload');
        } //try
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }// funcao

        
}

?>