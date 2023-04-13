<?php
/**
 * @author  <your name here>
 */
class LerRetorno extends TPage
{
    protected $form; // form
    
    private $datagrid; // listing
    
    private $_file;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_SystemUploadDocument');
        $this->form->setFormTitle('Tratar retorno - NOVO');

        // create the form fields
        $id = new THidden('id');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        $filename->setService('SystemDocumentUploaderService');

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';

        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id] );
        $condominio_id->setSize('50%');
                
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        
        $this->form->addAction('Simular', new TAction(array($this, 'onSimular')), 'fa:arrow-circle-o-right');
        $this->form->addAction('Processar', new TAction(array($this, 'onProcessar')), 'fa:arrow-circle-o-right');
        
        ////
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
       
        // creates the datagrid columns
        $column_nossonumero = new TDataGridColumn('nossoNumero', 'Nosso Número', 'center');
        $column_tarifa = new TDataGridColumn('tarifa', 'Tarifa', 'center');
        $column_valor_titulo = new TDataGridColumn('valorTitulo', 'Valor', 'center');
        
        $column_data_vencimento = new TDataGridColumn('dataVencimento', 'Vencimento', 'center');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_data_credito = new TDataGridColumn('dataCredito', 'Dt Créditoo', 'center');
        $column_data_pagamento = new TDataGridColumn('dataPag', 'Dt Pagam.', 'center');
        $column_valorPago = new TDataGridColumn('valorPago', 'Vlr Pago', 'center');
        $column_valorMulta = new TDataGridColumn('valorMulta', 'Multa/Juros', 'center');
        $column_valorDesconto = new TDataGridColumn('valorDesconto', 'Desconto', 'center');
        //$column_valorAbatimento = new TDataGridColumn('valorAbatimento', 'Abatimento', 'center');
        $column_numDocumento = new TDataGridColumn('numDocumento', 'Documento', 'center');
        $column_numSequencial = new TDataGridColumn('numSequencial', 'Sequencial', 'center');
        $column_modalidade = new TDataGridColumn('modalidade', 'Modalidade', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'center');
        
        $column_lancamento = new TDataGridColumn('lancamento', 'Lançamento', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nossonumero);
        $this->datagrid->addColumn($column_tarifa);
        $this->datagrid->addColumn($column_valor_titulo);
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_data_pagamento);
        $this->datagrid->addColumn($column_valorPago);
        $this->datagrid->addColumn($column_valorMulta);
        $this->datagrid->addColumn($column_valorDesconto);
        //$this->datagrid->addColumn($column_valorAbatimento);
        $this->datagrid->addColumn($column_numDocumento);
        $this->datagrid->addColumn($column_modalidade);
        $this->datagrid->addColumn($column_status);
        
        $this->datagrid->enablePopover('Informações Complementares', '<b>'
            .'Data Crédito'.'</b><br>' . '{dataCredito}' 
            . '<br><b>'.'Id Cts Receber'.'</b><br>' . '{lancamento}'
            . '<br><b>'.'Documento'.'</b><br>' . '{documento}');
            
        $column_tarifa->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valorMulta->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
               
        $column_valor_titulo->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valorDesconto->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valorPago->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_status->setTransformer( function($value, $object, $row) {
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
    
    public function onSimular( $param )
    {
      
      try
        {
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          // Se existe o arquivo faz upload.
          $this->_file =$param['filename'];
          //var_dump($this->_file);
          if ($this->_file)
            {
                $target_folder = 'boletos/retorno';
                $target_file   = $target_folder . '/' .$this->_file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            }
          
          $fileContent = file_get_contents('boletos/retorno/'.$param['filename']);
          $arquivo = new \CnabPHP\Retorno($fileContent); 
          $registros = $arquivo->getRegistros();
          $this->datagrid->clear();

          foreach($registros as $registro)
          {
                if($registro->R3U->codigo_movimento==6){
                         
                    if (!isset($object))  
                        $object = new stdClass();
                    
                    $object->status = 'N';
                    $object->nossoNumero = $registro->nosso_numero;
                    $object->tarifa = $registro->vlr_tarifas; 
                    $object->valorTitulo = $registro->valor;
                    $object->valorPago = $registro->vlr_pago;
                    $object->dataVencimento = $registro->data_vencimento;
                    $object->documento = $registro->R3T->seu_numero;
                    $object->dataPag = $registro->R3U->data_ocorrencia;
                    $object->valorMulta = $registro->R3U->vlr_juros_multa;
                    $object->valorDesconto = $registro->R3U->vlr_desconto;
                    //var_dump($registro);
                    
                    // data crédito $registro->R3U->data_credito
                    
                    $this->datagrid->addItem($object);
                }
          }


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

