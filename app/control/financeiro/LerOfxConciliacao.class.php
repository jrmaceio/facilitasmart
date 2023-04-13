<?php
/**
 * @author  <your name here>
 */
class LerOfxConciliacao extends TPage
{
    protected $form; // form
    
    private $datagrid; // listing
    
    private $_file;
    
    //ofx
    private $arquivo;
    public $bankTranList;
    public $dtStar;
    public $dtEnd;
    public $bankId;
    public $acctId;
    public $org;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_OfxConciliacao');
        $this->form->setFormTitle('Trata OFX e Conciliação');

        // create the form fields
        $id = new THidden('id');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        $filename->setService('SystemDocumentUploaderService');
        
        $TotalReceita = new TEntry('TotalReceita');
        $TotalReceita->setEditable(FALSE);
        $TotalDespesa = new TEntry('TotalDespesa');
        $TotalDespesa->setEditable(FALSE);
        $TotalCobBoleto = new TEntry('TotalCobBoleto');
        $TotalCobBoleto->setEditable(FALSE);
        $TotalRestDespBanc = new TEntry('TotalRestDespBanc');
        $TotalRestDespBanc->setEditable(FALSE);
        $TotalRestDespesa = new TEntry('TotalRestDespesa');
        $TotalRestDespesa->setEditable(FALSE);

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';

        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id] );
        $condominio_id->setSize('50%');
                
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        
        $this->form->addFields( [new TLabel('Receita')], [$TotalReceita], [new TLabel('Despesa')], [$TotalDespesa] );
        $this->form->addFields( [new TLabel('Cob Boleto')], [$TotalCobBoleto], [new TLabel('Restante Desp. Banc.')], [$TotalRestDespBanc] );
        $this->form->addFields( [new TLabel('Restante Despesa')], [$TotalRestDespesa] );

        
        $this->form->addAction('Simular', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        $this->form->addAction('Processar', new TAction(array($this, 'onProcessar')), 'fa:arrow-circle-o-right');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
       
        // creates the datagrid columns
        $column_data = new TDataGridColumn('data', 'Data', 'center');
        $column_tipo_movimentacao = new TDataGridColumn('tipo_movimentacao', 'Tipo Movimentação', 'center');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_status = new TDataGridColumn('status', 'Conciliado', 'center');
        $column_divergencia = new TDataGridColumn('divergencia', 'Divergência', 'center');
        
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_data);
        $this->datagrid->addColumn($column_tipo_movimentacao);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_divergencia);
        
        //$this->datagrid->enablePopover('Informações Complementares', '<b>'
         //   .'Data Crédito'.'</b><br>' . '{dataCredito}' 
         //   . '<br><b>'.'Id Cts Receber'.'</b><br>' . '{lancamento}'
         //   . '<br><b>'.'Documento'.'</b><br>' . '{documento}');
            
        $column_valor->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
      
        $column_divergencia->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
                
        $column_status->setTransformer( function($value, $object, $row) {
            // VERIFICA SEM TEM MAIS DE UM LANCAMENTO NO EXTRATO E SE FOI CHECADO
            //var_dump(substr($object->tipo_movimentacao,1,1));
            if (TSession::getValue($object->documento) and (substr($object->tipo_movimentacao,0,1) == 'C')) {
                //var_dump(substr($object->tipo_movimentacao,1,1));
                $value = 'Y';
            }
            
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
       
        // create the datagrid model
        $this->datagrid->createModel();
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        parent::add($container);

    } 
            
    public function onEdit( $param )
    {
        if ($param['id'])
        {
            $obj = new stdClass;
            $obj->id = $param['id'];
            $this->form->setData($obj);
        }
    }
    
    public function onNext( $param )
    {
      
      try
        {
          $string = new StringsUtil;
           
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          // Se existe o arquivo faz upload.
          $this->_file = $param['filename'];
          
          //var_dump($this->_file);
          if ($this->_file)
            {
                $target_folder = 'ofx';
                $target_file   = $target_folder . '/' .$this->_file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            }
         
          
          $ofxParser = new \OfxParser\Parser();
          $ofx = $ofxParser->loadFromFile('ofx/'.$param['filename']);

          $bankAccount = reset($ofx->bankAccounts);

          // Get the statement start and end dates
          $startDate = $bankAccount->statement->startDate;
          $endDate = $bankAccount->statement->endDate;

          // Get the statement transactions for the account
          $transactions = $bankAccount->statement->transactions;
          
          $this->datagrid->clear();
          
          // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
          if (!isset($object))  
            $object = new stdClass();
  
          //var_dump($bankAccount);
          //var_dump($startDate);
          //var_dump($endDate);

          $num_doc_recebimento = '';
          $valor_recebido = 0;
          $TotReceita = 0;
          $TotDespesa = 0;
          $TotCobBoleto = 0;
          $TotRestanteDespBanc = 0;
          $TotRestanteDespesa = 0;
          
          $DataSoma = null;
          $TotParcReceita = 0;
          $TotParcDespesa = 0;
          
          foreach ( $transactions as $transacao )
          {
            //var_dump( $transacao->date );
            //object(DateTime)#105 (3) { ["date"]=> string(26) "2018-05-02 12:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(17) "America/Sao_Paulo" } 
            $object->data = $transacao->date->format('d/m/Y');
            
            if ($DataSoma == null) {
                $DataSoma = $transacao->date->format('d/m/Y');
            }
            
            // coloca o total da data na tela
            if ($DataSoma != $transacao->date->format('d/m/Y')) {
                
                //$object->data = '';
                //$object->tipo_movimentacao = 'S';
                //$object->documento = '';
                //$object->valor = $TotParcReceita;
                //$object->status = '';
                //$object->divergencia = 0;
                //$object->descricao = 'SUB-TOTAL RECEITA';
                //// add the object inside the datagrid
                //$this->datagrid->addItem($object);
                
                //$object->data = '';
                //$object->tipo_movimentacao = 'S';
                //$object->documento = '';
                //$object->valor = $TotParcDespesa;
                //$object->status = '';
                //$object->divergencia = 0;
                //$object->descricao = 'SUB-TOTAL DESPESA';
                //// add the object inside the datagrid
                //$this->datagrid->addItem($object);
                
                $DataSoma = $transacao->date->format('d/m/Y');
                $TotParcReceita = 0;
                $TotParcDespesa = 0;
            }
            
            $object->tipo_movimentacao = $transacao->type;
            $object->documento = $transacao->uniqueId;
            $object->valor = $transacao->amount;
            $object->status = 'N';
            $object->divergencia = 0;
            $object->descricao = $transacao->memo;

            // verifica a conciliação
            if ( $object->tipo_movimentacao == 'DEBIT' ) {
                if ( $object->descricao != 'APL AUTOM' and $object->descricao != 'APLICACAO' ) {
                    
                    $total_pago_doc = ContasPagar::getValorPago( $transacao->uniqueId, TSession::getValue('id_condominio') );
                
                    if ( $total_pago_doc == abs($transacao->amount) ) {
                        $object->status = 'S';
                    }
                
                    $TotDespesa += abs($transacao->amount);
                    $TotParcDespesa += abs($transacao->amount);
                
                    //soma as despesas bancárias
                    if ( $object->descricao == 'COB MAN061' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB BX 063' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB AGENC' ) {
                        $TotCobBoleto += abs($transacao->amount);    
                    } elseif ( $object->descricao == 'COB LOTERI' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB COMPE' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB C BANC' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB AUTOAT' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB INTERN' ) {
                        $TotCobBoleto += abs($transacao->amount);
                    } elseif ( $object->descricao == 'COB LOT DH' ) {
                        $TotCobBoleto += abs($transacao->amount);    
                    } else {
                        if ( $object->descricao == 'DB CEST PJ' ) {
                            $TotRestanteDespBanc += abs($transacao->amount);
                        } elseif ( $object->descricao == 'TR TEV IBC' ) {
                            $TotRestanteDespBanc += abs($transacao->amount); 
                        } elseif ( $object->descricao == 'TAR FL CHQ' ) {
                            $TotRestanteDespBanc += abs($transacao->amount);         
                        } elseif ( $object->descricao == 'MANUT CTA' ) {
                            $TotRestanteDespBanc += abs($transacao->amount);
                        } elseif ( $object->descricao == 'MANUT CAD' ) {
                            $TotRestanteDespBanc += abs($transacao->amount);
                        } else {
                            $TotRestanteDespesa += abs($transacao->amount);
                        }
                    }
                    
                }
            }
            
            if ( $object->tipo_movimentacao == 'CREDIT' ) {
                if ( $object->descricao != 'RESG AUTOM' ) {
                    $total_pago_doc = ContasReceber::getValorRecebido( $transacao->date->format('Y-m-d'), TSession::getValue('id_condominio') );
                
                    // guarda o valor para possibilidade de vários creditos individualizados no extrato
                    if ( $num_doc_recebimento != $transacao->uniqueId ) {
                        $num_doc_recebimento = '';
                        $valor_recebido = 0;
                        $num_doc_recebimento = $transacao->uniqueId;
                    }
                
                    $valor_recebido += $transacao->amount;
                
                    if ( $total_pago_doc == $transacao->amount ) {
                      $object->status = 'S';
                    }
                
                    // verifica o acumulado
                    if ( $total_pago_doc == $valor_recebido ) {
                      // grava na seção que o documento foi checado, o set transforme vai analisar e mudar o status
                      TSession::setValue($num_doc_recebimento,  'Y');
                  
                      $object->status = 'S';
                      $num_doc_recebimento = '';
                      $valor_recebido = 0;
                    }
                
                    $TotReceita += abs($transacao->amount);
                    $TotParcReceita += abs($transacao->amount);
                 }
            }
            
            
            // add the object inside the datagrid
            $this->datagrid->addItem($object);
          }
          
          // colocar o resultado na tela
          $object2 = new stdClass();
          $sumSaldo = 0.00;
          $object2->result=$sumSaldo;
          $object2->TotalReceita=number_format($TotReceita,2,',','.');
          $object2->TotalDespesa=number_format($TotDespesa,2,',','.');
          $object2->TotalCobBoleto=number_format($TotCobBoleto,2,',','.');
          $object2->TotalRestDespBanc=number_format($TotRestanteDespBanc,2,',','.');
          $object2->TotalRestDespesa=number_format($TotRestanteDespesa,2,',','.');
          TForm::sendData('form_OfxConciliacao', $object2);
           
          // close the transaction
          TTransaction::close();
        }
      catch (Exception $e) // in case of exception
        {
          new TMessage('error', $e->getMessage()); // shows the exception error message

        }    
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onProcessar( $param )
    {
      try
        {
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          $cnabFactory = new Cnab\Factory();
          
          // Se existe o arquivo faz upload.
          $this->_file =$param['filename'];
          
          if ($this->_file)
            {
                $target_folder = 'boletos/retorno';
                $target_file   = $target_folder . '/' .$this->_file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            }
                
          $arquivo = $cnabFactory->createRetorno('boletos/retorno/'.$param['filename']);
          
          $detalhes = $arquivo->listDetalhes();
          
          $codCedente = $arquivo->getCodigoConvenio(); // tem outro nome no padrao 400
          $dataGeracao = $arquivo->getDataGeracao();
          $conta = $arquivo->getConta();
          $contaDac = $arquivo->getContaDac();
          $codigoBanco = $arquivo->getCodigoBanco();
          $dataCredito = $arquivo->getDataCredito();
 
          $this->datagrid->clear();

          foreach($detalhes as $detalhe) {
            // faz de todos porque pode existir erro no aquivo retorno = 
            if($detalhe->getValorRecebido() > 0) {
              //var_dump($detalhe);
              $nossoNumero   = $detalhe->getNossoNumero();
              $valorTitulo   = $detalhe->getValorTitulo(); 
              $valorRecebido = $detalhe->getValorRecebido();
              $dataVencimento = $detalhe->getDataVencimento();
              $dataPagamento = $detalhe->getDataOcorrencia();
              $dataCredito = $detalhe->getDataCredito();
              $tarifa        = $detalhe->getValorTarifa();
              $valorPago     = $detalhe->getValorPago();
              $valorMulta    = $detalhe->getValorMoraMulta();
              $valorDesconto = $detalhe->getValorDesconto();
              $valorAbatimento = $detalhe->getValorAbatimento();
              $numSequencial  = $detalhe->getNumeroSequencial();
              $numDocumento  = $detalhe->getNumeroDocumento();
              $modalidade = $detalhe->getCodigoNome(); 
         
              // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
              if (!isset($object))  
                $object = new stdClass();

              //var_dump($object);              
              $object->nossoNumero = $nossoNumero;
              $object->valorTitulo = $valorTitulo;
              $object->valorRecebido = $valorRecebido;
              $object->dataVencimento = $dataVencimento->format('d/m/Y');
              $object->dataPag = $dataPagamento->format('d/m/Y');
              $object->dataCredito = $dataCredito->format('d/m/Y');
              $object->tarifa =  $tarifa;
              $object->valorPago = $valorPago;
              $object->valorMulta = $valorMulta;
              $object->valorDesconto = $valorDesconto;
              $object->valorAbatimento = $valorAbatimento;
              $object->numDocumento = $numDocumento;
              $object->numSequencial = $numSequencial;
              $object->modalidade = $modalidade;
              $object->status = 'N';
              $object->lancamento = '';
              $object->documento = $numDocumento;
                    
              $lancamento = ContasReceber::retornaLancamentosNossoNumero($nossoNumero);
              
              // mes referencia
              $datahoje = $object->dataPag;
              $partes = explode("/", $datahoje);
              $ano_hoje = $partes[2];
              $mes_hoje = $partes[1];
              $mes_ref = $mes_hoje.'/'.$ano_hoje;
               
              //// verifica fechamento
              $fechamentos = Fechamento::where('condominio_id', '=', $param['condominio_id'])->
                                           where('mes_ref', '=', $mes_ref)->load();
                        
              //default = 1 fechado, não permite nada
              $statusFech = 1;
        
              foreach ($fechamentos as $fechamento)
              {
                $statusFech = $fechamento->status;
                $contaFechamentoId = $fechamento->conta_fechamento_id;
              }
                        
                        
              if ( $statusFech != 0 or $statusFech == ''){
                new TMessage('info', 'Não existe um fechamento em aberto para data baixa !');
                TTransaction::close(); // close the transaction
                return;
              }
              ////////////////////////////////////
                                     
              if (isset($lancamento->id)) {
                //var_dump($lancamento->id);
                $object->lancamento = $lancamento->id;
                $object->status = 'Y';
                
                // faz a baixa
                $objectReceber = new ContasReceber($lancamento->id); // instantiates the Active Record
                
                /* desabilitado em 08/08/2019, baixa so automatica pelo processamento do retorno ou pjbank
                if ( $objectReceber ->situacao == '0' )
                {
                    $objectReceber->situacao = '1';
                    $objectReceber->dt_pagamento = $dataPagamento->format('Y-m-d'); 
                    $objectReceber->dt_liquidacao = $dataCredito->format('Y-m-d'); 
                    $objectReceber->valor_pago = $valorPago;
                    $objectReceber->desconto = $valorDesconto;
                    $objectReceber->juros = 0;
                    $objectReceber->multa = $valorMulta;
                    $objectReceber->correcao = 0;
                    $objectReceber->conta_fechamento_id = $contaFechamentoId;
                    $objectReceber->dt_ultima_alteracao = date('Y-m-d');
                    $objectReceber->store(); // update the object in the database
                }
               */     
              }
            
              
              // add the object inside the datagrid
              $this->datagrid->addItem($object);
            }
          }

          
          // close the transaction
          TTransaction::close();
        }
      catch (Exception $e) // in case of exception
        {
          new TacesseseucondominioMessage('error', $e->getMessage()); // shows the exception error message

        }
    }

 
}

