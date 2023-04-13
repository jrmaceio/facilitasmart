<?php

/**
 
 */
class BaixarPJBankListagem extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_BaixarPJBank');
        $this->form->setFormTitle('Baixar PJBank - Individual por Condomínio');
        
        $this->string = new StringsUtil;

        // create the form fields
        $dt_inicio = new TDate('dt_inicio');
        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_fim = new TDate('dt_fim');
        $dt_fim->setMask('dd/mm/yyyy');

        $this->form->addFields( [new TLabel('Data Inicial')], [$dt_inicio],
                                [new TLabel('Data Final')], [$dt_fim]                                
                            );
                            
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('BaixarPJBankListagem_filter_data') );
        
        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        $this->form->addAction('Processar Baixa', new TAction([$this, 'onBaixar']), 'fa:bank #69aa46');
        
        //$this->form->addAction('Show', new TAction([$this, 'onSave1']), 'fa:barcode  #69aa46');

         // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        $this->datagrid->setHeight(320);

        
        // creates the datagrid columns
        $column_situacao = new TDataGridColumn('status', 'Status', 'center');
        $column_condominio = new TDataGridColumn('condominio', 'Condomínio', 'right');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Pago', 'left');
        $column_valor_creditado = new TDataGridColumn('valor_creditado', 'Creditado', 'left');
        $column_nosso_numero = new TDataGridColumn('nosso_numero', 'Nosso Número', 'right');
        $column_pedido_numero = new TDataGridColumn('pedido_numero', 'Ped. Número', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Pagamento', 'left');
        $column_dt_credito = new TDataGridColumn('dt_credito', 'Crédito', 'left');
        $column_pagador = new TDataGridColumn('pagador', 'Pagador', 'left');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_situacao); 
        $this->datagrid->addColumn($column_condominio);
        $this->datagrid->addColumn($column_valor_pago);
        $this->datagrid->addColumn($column_valor_creditado);
        $this->datagrid->addColumn($column_nosso_numero);
        $this->datagrid->addColumn($column_pedido_numero);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_dt_credito);
        $this->datagrid->addColumn($column_pagador);
        
        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_dt_pagamento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_dt_credito->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_valor_pago->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valor_creditado->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
                
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);

        $this->datagrid->disableDefaultClick();
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        //$this->pageNavigation = new TPageNavigation;
        //contador
        //$this->pageNavigation->enableCounters();
        //$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        //$this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        //$panel->addFooter($this->pageNavigation);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        
        //$container->add(TPanelGroup::pack('Lançamentos', $this->form));
        $container->add($this->form);
        $container->add($panel);

        
        parent::add($container);
    }
    
    /* 
    *   
    *   
    *   
    */
    public function onBaixar($param)
        {
            $string = new StringsUtil;
          
            $this->datagrid->clear();
          
          
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
                        
            if ($condominio->credencial_pjbank == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado!');
                return;
            }
          
            $this->datagrid->clear();
                      
            try 
            {
              
                $data_inicial = TDate::date2us($param['dt_inicio']);
                $data_final = TDate::date2us($param['dt_fim']);
                
                //$data_inicial = date_format(new DateTime($param['dt_inicio']),'mm/dd/Y');
                $data_final = date_format(new DateTime($data_final),'m/d/Y');
                $data_inicial = date_format(new DateTime($data_inicial),'m/d/Y');
                
                //var_dump("https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank.
                  //           "/transacoes?data_inicio=".$data_inicial.
                   //          "&data_fim=".$data_final."&pago=1&pagina=1");
                
                //print_r($param); 
                
                for ($i = 1; $i <= 20; $i++) {
                    $num_pagina = $i;   
                    //var_dump($num_pagina);
                    
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank.
                             "/transacoes?data_inicio=".$data_inicial.
                             "&data_fim=".$data_final."&pago=1&pagina=".$num_pagina,
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
                            //var_dump(count($pjbank));
                            //return;
                    
                        //se tiver 50 itens, pegar a 2a pagina
                    
                        //var_dump($pjbank);
                        foreach ($pjbank as $pagamento)
              	        {
                            $object = new stdClass();
                            //var_dump($pagamento);
                            //return;
                        
                            $titulo = new ContasReceber($pagamento["pedido_numero"]);
                            
                            if (is_null($titulo->id)) {
                                //var_dump($pagamento);
                                //return;
                                new TMessage('error', 'Título não identificado no sistema! ' . var_dump($pagamento));
                            }else {
                            
                                $object->status = 'NL'; // não localizado
                                                
                                if (isset($titulo)) {
                                    if ($titulo->situacao == '1') {
                                        $object->status = 'Pago';
                                
                                    }else {
                                        $data = $pagamento["data_credito"];
              	                        $partes = explode("/", $data);
             	                        $ano = $partes[2];
              	                        $mes = $partes[0];
              	                        $mes_ref = $mes.'/'.$ano;

              	                        // verifica fechamento
              	                        $fechamentos = Fechamento::where('condominio_id', '=', $condominio->id)->
                                           where('mes_ref', '=', $mes_ref)->load();
                        
              	                        //default = 1 fechado, não permite nada
                  	                    $statusFech = 1;
        
              	                        foreach ($fechamentos as $fechamento)
              	                        {
                	                        $statusFech = $fechamento->status;
                	                    }
                        
              	                        if ($statusFech == 0) {
                	                        // faz a baixa
                	                        $object->status = 'Baixado';
                	                
                	                        $dtpagamento = date_format(new DateTime($pagamento['data_pagamento']),'Y-m-d');
                                            $dtcredito = date_format(new DateTime($pagamento['data_credito']),'Y-m-d');
                                            $juros = 0;
                                            $desconto = 0;
                                                  
                                            if ($pagamento['valor_pago'] > $titulo->valor) {
                                                $juros = $pagamento['valor_pago'] - $titulo->valor;
                                            }
                            
                                            if ($pagamento['valor_pago'] < $titulo->valor) {
                                                $desconto = $titulo->valor - $pagamento['valor_pago'];
                                            }
                                             
                                            $titulo->arquivo_retorno = 'AUTO-M';      
                                            $titulo->situacao = '1';
                                            $titulo->dt_pagamento = $dtpagamento;
                                            $titulo->dt_liquidacao = $dtcredito; 
                                            $titulo->valor_pago = $pagamento['valor_pago'];
                                            $titulo->desconto = $desconto;
                                            $titulo->juros = $juros;
                                            $titulo->valor_creditado = $pagamento['valor_liquido'];
                                            $titulo->tarifa = $pagamento['tarifa'];
                                            $titulo->store(); // update the object in the database
                                
                	                
              	                        } else {
              	                            $object->status = 'Problema';
              	                        }
                                                                
                                    }
                            
                                }
                                                    
                                $object->condominio = $condominio->resumo;
                                $object->valor_pago = $pagamento["valor_pago"];
                                $object->valor_creditado = $pagamento["valor_liquido"];
                                $object->nosso_numero = $pagamento["nosso_numero"];
                                $object->pedido_numero = $pagamento["pedido_numero"];
                                $object->dt_vencimento = $pagamento["data_vencimento"];
                                $object->dt_pagamento = $pagamento["data_pagamento"];
                                $object->dt_credito = $pagamento["data_credito"];
                                $object->pagador = $pagamento["pagador"];
                        
                                $this->datagrid->addItem($object);
                            
                            } // fim teste is_null 
                            
                        } // fim foreach
                  
                    }
                
                    //var_dump(count($pjbank));
                    if (count($pjbank) < 50) {
                        //parar o for 
                        break;
                    }
                     
                } // fim for
                
                TTransaction::close();    
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }
   
    
     
        
}

?>