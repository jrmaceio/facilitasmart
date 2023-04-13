<?php
/**
 * @author  <your name here>
 */
class BoletoTesteRetorno extends TPage
{
    protected $form; // form
    
    private $datagrid; // listing
    
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
        $this->form->setFormTitle('Tratar retorno');

        // create the form fields
        $id = new THidden('id');
        $filename = new TFile('filename');
        $filename->setService('SystemDocumentUploaderService');

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        $this->form->addAction('Tratar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        
        ////
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
       
        // creates the datagrid columns
        $column_nossonumero = new TDataGridColumn('nossoNumero', 'Nosso Número', 'center');
        $column_tarifa = new TDataGridColumn('tarifa', 'Tarifa', 'center');
        $column_valor_titulo = new TDataGridColumn('valorTitulo', 'Vlr Título', 'center');
        $column_valor = new TDataGridColumn('valorRecebido', 'Recebido', 'center');
        $column_data_vencimento = new TDataGridColumn('dataVencimento', 'Vencimento', 'center');
        $column_data_pagamento = new TDataGridColumn('dataPag', 'Dt Pagam.', 'center');
        $column_valorPago = new TDataGridColumn('valorPago', 'Vlr Pago', 'center');
        $column_valorMulta = new TDataGridColumn('valorMulta', 'Multa', 'center');
        $column_valorDesconto = new TDataGridColumn('valorDesconto', 'Desconto', 'center');
        $column_valorAbatimento = new TDataGridColumn('valorAbatimento', 'Abatimento', 'center');
        $column_numDocumento = new TDataGridColumn('numDocumento', 'Documento', 'center');
        $column_numSequencial = new TDataGridColumn('numSequencial', 'Sequencial', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nossonumero);
        $this->datagrid->addColumn($column_tarifa);
        $this->datagrid->addColumn($column_valor_titulo);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_data_pagamento);
        $this->datagrid->addColumn($column_valorPago);
        $this->datagrid->addColumn($column_valorMulta);
        $this->datagrid->addColumn($column_valorDesconto);
        $this->datagrid->addColumn($column_valorAbatimento);
        $this->datagrid->addColumn($column_numDocumento);
        $this->datagrid->addColumn($column_numSequencial);
        $this->datagrid->addColumn($column_status);
        
        $column_tarifa->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valorMulta->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valor->setTransformer( function($value, $object, $row) {
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
        
        $column_valorAbatimento->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $column_valorPago->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
       
        // create the datagrid model
        $this->datagrid->createModel();
        
        ///
        
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
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onNext( $param )
    {
        try
        {
            $cnabFactory = new Cnab\Factory();
              
            $arquivo = $cnabFactory->createRetorno('boletos/retorno/'.$param['filename']);
            $detalhes = $arquivo->listDetalhes();
            //var_dump($detalhes);
        
            $this->datagrid->clear();
    
    /*
    getValorTitulo()
    getValorPago()
    getValorTarifa()
    getValorIOF()
    getValorDesconto()
    getValorAbatimento()
    getValorOutrasDespesas()
    getValorOutrosCreditos()
    getNumeroDocumento()
    getNossoNumero()
    getDataVencimento()
    getDataCredito()
    getValorMoraMulta()
    getDataOcorrencia()
    getCarteira()
    getAgencia()
    getAgenciaDv()
    getAgenciaCobradora()
    getAgenciaCobradoraDac()
    getNumeroSequencial()
    getCodigoNome()
    getCodigoLiquidacao()
    getDescricaoLiquidacao()
   
    */
   
            foreach($detalhes as $detalhe) {
                // faz de todos porque pode existir erro no aquivo retorno = 
                if($detalhe->getValorRecebido() > 0) {
                    //var_dump($detalhe);
                    $nossoNumero   = $detalhe->getNossoNumero();
                    $valorTitulo   = $detalhe->getValorTitulo(); 
                    $valorRecebido = $detalhe->getValorRecebido();
                    $dataVencimento = $detalhe->getDataVencimento();
                    $dataPagamento = $detalhe->getDataOcorrencia();
                    $tarifa        = $detalhe->getValorTarifa();
                    $valorPago     = $detalhe->getValorPago();
                    $valorMulta    = $detalhe->getValorMoraMulta();
                    $valorDesconto = $detalhe->getValorDesconto();
                    $valorAbatimento = $detalhe->getValorAbatimento();
                    $numSequencial  = $detalhe->getNumeroSequencial();
                    $numDocumento  = $detalhe->getNumeroDocumento();
                    $status        = $detalhe->getCodigoNome(); 
     
                  //       . ',' . $detalhe->getValorIOF()
                 //. ',' . $detalhe->getValorOutrasDespesas()
    //. ',' . $detalhe->getValorOutrosCreditos()
    //. ',' . $detalhe->getDataVencimento()
    //. ',' . $detalhe->getDataCredito()
   // . ',' . $detalhe->getCarteira()
    //. ',' . $detalhe->getAgencia()
    //. ',' . $detalhe->getAgenciaDv()
    //. ',' . $detalhe->getAgenciaCobradora()
    //. ',' . $detalhe->getAgenciaCobradoraDac()
    //. ',' . $detalhe->getCodigoNome()
    //. ',' . $detalhe->getCodigoLiquidacao()
    //. ',' . $detalhe->getDescricaoLiquidacao();
    
                  // você já tem as informações, pode dar baixa no boleto aqui
 
              
                    // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                    if (!isset($object))  
                        $object = new stdClass();

                    //var_dump($object);              
                    $object->nossoNumero = $nossoNumero;
                    $object->valorTitulo = $valorTitulo;
                    $object->valorRecebido = $valorRecebido;
                    $object->dataVencimento = $dataVencimento->format('d-m-Y');
                    $object->dataPag = $dataPagamento->format('d-m-Y');
                    $object->tarifa =  $tarifa;
                    $object->valorPago = $valorPago;
                    $object->valorMulta = $valorMulta;
                    $object->valorDesconto = $valorDesconto;
                    $object->valorAbatimento = $valorAbatimento;
                    $object->numDocumento = $numDocumento;
                    $object->numSequencial = $numSequencial;
                    $object->status = $status;
              
         
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

    
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message

        }
    }

    public function onVerifica() 
    { 
    //error_reporting(E_ALL);
    //ini_set('display_errors', 'On');

    $cnabFactory = new Cnab\Factory();
    
    $querie = file_get_contents("boletos/");
    
    $arquivo = $cnabFactory->createRetorno('boletos/retorno/retorno1.ret');
    //var_dump($arquivo);
    $detalhes = $arquivo->listDetalhes();
    echo '<br><br><pre>';
    foreach($detalhes as $detalhe) {
        if($detalhe->getValorRecebido() > 0) {
          $nossoNumero   = $detalhe->getNossoNumero();
          $valorRecebido = $detalhe->getValorRecebido();
          $dataPagamento = $detalhe->getDataOcorrencia();
          $carteira      = $detalhe->getCarteira();
      
          //var_dump($dataPagamento);
            
          // você já tem as informações, pode dar baixa no boleto aqui
        
          //echo htmlspecialchars('Nosso número : ' . $nossoNumero . ' Valor Recebido : ' . $valorRecebido . ' Data Pagamento : ' . $dataPagamento);
          echo htmlspecialchars('Nosso número : ' . $nossoNumero . ' Valor Recebido : ' . $valorRecebido );
          echo '<br><br>';
        }
    }

    echo '</pre><br><br><pre>';

    //echo '<br><br><pre>';
    //echo htmlspecialchars($nfe->soapDebug);
    //echo '</pre><br><br><pre>';
    //print_r($aResposta);
    //echo "</pre><br>";

    /* 
    parent::add(new TLabel($aResposta['cStat'])); 
    parent::add(new TLabel($aResposta['xMotivo'])); 
    parent::add(new TLabel($aResposta['dhRecbto'])); 
    */ 

    //new TMessage('info', $nossoNumero . ' ' . $valorRecebido . ' ' .$dataPagamento); 
    } 

}

