<?php
/**
 * @author  <your name here>
 */
class TrataRetornoRemessa extends TPage
{
    protected $form; // form
    
    private $datagridrem; // listing
    
    private $_file;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
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
        $this->form = new BootstrapFormBuilder('form_TrataRetornoRemessa');
        $this->form->setFormTitle('Ler Arquivo Retorno / Remessa e Sincroniza');

        // create the form fields
        $id = new THidden('id');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        //$filename->setService('SystemDocumentUploaderService');

        // controle dos pago ou aberto = situacao
        $processa = new TRadioGroup('processa'); 

        $processa->addValidation('Processa', new TRequiredValidator());  
        
        $processa->addItems(['1'=>'Processar','2'=>'Não Processa']);
        $processa->setLayout('horizontal');
        //$processa->setBooleanMode();
        $processa->setValue('2');

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id] );
        $condominio_id->setSize('50%');
         
        $this->form->addFields( [new TLabel('Aplicar')], [$processa] );
         
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        $this->form->addAction('Tratar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        
        ////
        // creates a Datagrid
        $this->datagridrem = new TDataGrid;
        $this->datagridrem = new BootstrapDatagridWrapper($this->datagridrem);
        $this->datagridrem->style = 'width: 100%';
        $this->datagridrem->datatable = 'true';

        // creates the datagridrem columns
        $column_nossonumero = new TDataGridColumn('nossoNumero', 'Nosso Número', 'center');
        $column_numdocumento = new TDataGridColumn('numDocumento', 'No. Documento', 'center');
        $column_valor_titulo = new TDataGridColumn('valorTitulo', 'Valor', 'center');
        $column_data_emissao = new TDataGridColumn('dataEmissao', 'Emissão', 'center');
        $column_data_vencimento = new TDataGridColumn('dataVencimento', 'Vencimento', 'center');
        $column_identificacao = new TDataGridColumn('identificacao', 'Identificação', 'center');
        $column_cpf_cnpj = new TDataGridColumn('cpf_cnpj', 'CPF / CNPJ', 'center');
        $column_pagador = new TDataGridColumn('pagador', 'Pagador', 'center');
        $column_unid = new TDataGridColumn('unid', 'Unidade', 'center');
        $column_numDocumento = new TDataGridColumn('numDocumento', 'Documento', 'center');
        $column_cod_mov_remessa = new TDataGridColumn('cod_mov_remessa', 'Cod.Movim.', 'center');
        $column_status = new TDataGridColumn('status', 'Identificado', 'center');
        $column_id_ctsreceber = new TDataGridColumn('id_ctsreceber', 'Id Cts Rebeber', 'center');
        $column_modcarteira = new TDataGridColumn('modalidadecarteira', 'Mod Carteira', 'center');
        
        // add the columns to the DataGrid
        $this->datagridrem->addColumn($column_nossonumero);
        //$this->datagridrem->addColumn($column_numDocumento);
        $this->datagridrem->addColumn($column_valor_titulo);
        $this->datagridrem->addColumn($column_data_emissao);
        $this->datagridrem->addColumn($column_data_vencimento);
        //$this->datagridrem->addColumn($column_identificacao);
        $this->datagridrem->addColumn($column_cpf_cnpj);
        $this->datagridrem->addColumn($column_pagador);
        $this->datagridrem->addColumn($column_unid);
        $this->datagridrem->addColumn($column_cod_mov_remessa);
        $this->datagridrem->addColumn($column_status);

        $this->datagridrem->enablePopover('Informações Complementares', '<b>'.'Modalidade Carteira'.'</b><br>' . '{modalidadecarteira}' 
            . '<br><b>'.'Id Cts Receber'.'</b><br>' . '{id_ctsreceber}'
            . '<br><b>'.'Documento'.'</b><br>' . '{numDocumento}');
            
        $column_status->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
               
        $column_valor_titulo->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        // create the datagridrem model
        $this->datagridrem->createModel();
        
        ///
        
        //retorno
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';

        //$this->datagrid->disableDefaultClick(); // important! check
       
        // creates the datagrid columns
        //$column_check = new TDataGridColumn('check', 'Baixa', 'center');
        $column_nossonumero = new TDataGridColumn('nossoNumero', 'Nosso Número', 'center');
        $column_tarifa = new TDataGridColumn('tarifa', 'Tarifa', 'center');
        $column_valor_titulo = new TDataGridColumn('valorTitulo', 'Valor', 'center');
        $column_valor = new TDataGridColumn('valorRecebido', 'Crédito', 'center');
        $column_data_vencimento = new TDataGridColumn('dataVencimento', 'Vencimento', 'center');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_data_credito = new TDataGridColumn('dataCredito', 'Dt Crédito', 'center');
        $column_data_pagamento = new TDataGridColumn('dataPag', 'Dt Pagam.', 'center');
        $column_valorPago = new TDataGridColumn('valorPago', 'Vlr Pago', 'center');
        $column_valorMulta = new TDataGridColumn('valorMulta', 'Multa', 'center');
        $column_valorDesconto = new TDataGridColumn('valorDesconto', 'Desconto', 'center');
        //$column_valorAbatimento = new TDataGridColumn('valorAbatimento', 'Abatimento', 'center');
        $column_pagador = new TDataGridColumn('pagador', 'Sacado', 'center');
        $column_unid = new TDataGridColumn('unid', 'Unidade', 'center');
        $column_numSequencial = new TDataGridColumn('numSequencial', 'Sequencial', 'center');
        $column_modalidade = new TDataGridColumn('modalidade', 'Modalidade', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'center');
        
        $column_lancamento = new TDataGridColumn('lancamento', 'Lançamento', 'center');
        
        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_nossonumero);
        $this->datagrid->addColumn($column_tarifa);
        $this->datagrid->addColumn($column_valor_titulo);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_data_pagamento);
        $this->datagrid->addColumn($column_data_credito);
        $this->datagrid->addColumn($column_valorPago);
        $this->datagrid->addColumn($column_valorMulta);
        $this->datagrid->addColumn($column_valorDesconto);
        //$this->datagrid->addColumn($column_valorAbatimento);
        $this->datagrid->addColumn($column_pagador);
        $this->datagrid->addColumn($column_unid);
        $this->datagrid->addColumn($column_modalidade);
        $this->datagrid->addColumn($column_status);
        
        $this->datagrid->enablePopover('Informações Complementares', '<b>'
            .'Data Crédito'.'</b><br>' . '{dataCredito}' 
            . '<br><b>'.'Crédito'.'</b><br>' . '{valor_creditado}' 
            . '<br><b>'.'Id Cts Receber'.'</b><br>' . '{lancamento}'
            . '<br><b>'.'Documento'.'</b><br>' . '{num_Documento}');
            
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
        ///////// fimr retorno
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        $container->add(new TLabel('Remessa'));
        
        $container->add($this->datagridrem);
        
        $container->add(new TLabel('Retorno'));
        
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
          $string = new StringsUtil;
          
          $this->datagridrem->clear();
          
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          $this->_file =$param['filename'];

          // Se existe o arquivo faz upload.
          //var_dump($this->_file);
          if ($this->_file)
            {
                $target_folder = 'boletos';
                $target_file   = $target_folder . '/' .$this->_file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            } 
            
          $FileHandle = @fopen('boletos/'.$param['filename'], "r");
        
          $primeiralinha = false;
          $segundalinha = false;
          $string = new StringsUtil;
          
          // totalizadores para o retorno 
          $total_titulo = 0;
          $total_pago = 0;
          $total_creditado = 0;
          $total_tarifa = 0;
          $total_desconto = 0;
          $total_acrescimo = 0;

          // contador de linhas do datagrid de retornos para colocar o total no final
          $i = 1; 

          while (!feof($FileHandle))
          {
            $Buffer = fgets($FileHandle,4096);

            // ver se é uma linha do header de arquivo de remessa
            // sendo 0 (zero) é um arquivo de remessa
            if ( $primeiralinha == false ) {
              //var_dump('LINHA DO HEADER DE ARQUIVO');
              // verificar se a remessa ja foi processada antes e etc.........
              
              //nao identifica por aqui - ler manual ---- if ( substr($Buffer,8,1) == '0' ) {
              //  new TMessage('info', 'Arquivo inválido ou não é um arquivo de Remessa.');
                // close the transaction
              //  TTransaction::close();
              //  return;
              //}

              $primeiralinha = true;
              
            }
            
            // a 2a linha é o cabecalho do lote, nao tem informações adicionais por isso nao foi tratada
            if ( $segundalinha == false ) {
              $segundalinha = true;
            }
            
            // inicia o percorrer o arquivo para pegar os seguimentos P(titulo) e U(sacado)      
            if ( $primeiralinha and $segundalinha ) {
              
              // Verifica que tipo de segmento para retorno, T ou U 
              /* MOVIMENTACAO
              06 Liquidação
              02 Entrada Confirmada
              03 Entrada Rejeitada
              09 Baixa
              23 Remessa a Cartório
              26 Instrução Rejeitada
              28 Débito de Tarifas/Custas
              */
              if ( substr($Buffer,13,1) == 'T' ) // MOVIMENTACAO NA CARTEIRA 
              {
                $movimentacao = substr($Buffer,15,2);  // linha começa na coluna zero
                
                // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                if (!isset($object))  
                  $objectret = new stdClass();
                  
                if ($movimentacao == '06') {
                  $objectret->modalidade = 'Liquidação';
                }
                
                if ($movimentacao == '02') {
                  $objectret->modalidade = 'Entrada Confirmada';
                }
                
                if ($movimentacao == '03') {
                  $objectret->modalidade = 'Entrada Rejeitada';
                }
                
                if ($movimentacao == '09') {
                  $objectret->modalidade = 'Baixa';

                  // verifica se a baixa foi um pedido do cliente, neste caso o valor pago = 0
                  // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                }  
                 
                if ($movimentacao == '23') {
                  $objectret->modalidade = 'Remessa a Cartório';
                }
                
                if ($movimentacao == '26') {
                  $objectret->modalidade = 'Instrução Rejeitada';
                }
                
                if ($movimentacao == '28') {
                  $objectret->modalidade = 'Débito de Tarifas/Custas';
                } 
                              
                $objectret->nossoNumero = intval(substr($Buffer,41,15));
                $objectret->valorTitulo = substr($Buffer,81,15)/100;
                $objectret->dataVencimento = $string->formatDateBR( 
                substr($Buffer,77,4). '-' .
                substr($Buffer,75,2). '-' .
                substr($Buffer,73,2));
                $objectret->numDocumento = ''.substr($Buffer,58,11);
                $objectret->tarifa =  substr($Buffer,198,15)/100;
                $objectret->status = 'N';
                $objectret->unid = '';
                $objectret->lancamento = '';
                
                $objectret->cpf_cnpj = substr($Buffer,133,15); 
                $objectret->pagador = substr($Buffer,148,40); 

                // acumuldores
                $total_titulo += $objectret->valorTitulo;
                $total_tarifa += $objectret->tarifa;

              }
              
              if ( substr($Buffer,13,1) == 'U' ) // retorno  - fazer a baixa do titulo
              {
                $objectret->valorRecebido = substr($Buffer,92,15)/100; // valor a creditar na conta
                $objectret->valorPago = substr($Buffer,77,15)/100;
                
                $objectret->dataPag = $string->formatDateBR( 
                substr($Buffer,141,4). '-' .
                substr($Buffer,139,2). '-' .
                substr($Buffer,137,2));
                
                $objectret->dataCredito = $string->formatDateBR( 
                substr($Buffer,149,4). '-' .
                substr($Buffer,147,2). '-' .
                substr($Buffer,145,2));
                $objectret->valor_creditado = substr($Buffer,92,15)/100;
                $objectret->valorMulta = substr($Buffer,17,15)/100;
                $objectret->valorDesconto = substr($Buffer,32,15)/100;
                $objectret->valorAbatimento = substr($Buffer,47,15)/100;
                
                $objectret->numSequencial = '';

                // acumuladores
                $total_pago += $objectret->valorPago;
                $total_creditado += $objectret->valor_creditado;
                $total_desconto += $objectret->valorDesconto;
                $total_acrescimo += $objectret->valorMulta;


				        //$objectret->check = new TCheckButton('check1');     
				        //$objectret->check->setIndexValue('off');

                // mudei para depois da baixa ---- $this->datagrid->addItem($objectret); 

        		    //$this->form->addField($item->check); // important!

                //busca a pessoa pelo cpf
                $objectret->cpf_cnpj = intval($objectret->cpf_cnpj);
                $objectret->cpf_cnpj = str_pad($objectret->cpf_cnpj , 11 , '0' , STR_PAD_LEFT);
                $pagador = Pessoa::where('condominio_id', '=', $param['condominio_id'])->
                                          where('cpf', '=', $objectret->cpf_cnpj)->load();
               
        		    // processa a baixa dos titulos
        		    //desativado para sicred $lancamento = ContasReceber::retornaLancamentosNossoNumero($objectret->nossoNumero);
                $lancamento = null;
                
                if (isset($lancamento->id)) {                
                    $unid = new Unidade($lancamento->unidade_id);
                    $pess = new Pessoa($unid->proprietario_id);
                    $objectret->unidade = $pess->nome;
                }
                  
        		// verifica se existe um fechamento aberto para processar
        		 // mes referencia
              	$datahoje = $objectret->dataPag;
              	$partes = explode("/", $datahoje);
             	  $ano_hoje = $partes[2];
              	$mes_hoje = $partes[1];
              	$mes_ref = $mes_hoje.'/'.$ano_hoje;
               	$fechamentos = Fechamento::where('condominio_id', '=', $param['condominio_id'])->
                                           where('mes_ref', '=', $mes_ref)->load();
               //default = 1 fechado, não permite nada
              	$statusFech = 1;
                foreach ($fechamentos as $fechamento)
              	{
                	$statusFech = $fechamento->status;
                	$contaFechamentoId = $fechamento->conta_fechamento_id;
              	}
                        
              	if ( ($statusFech != 0 or $statusFech == '') and $param['processa'] == '1' ){
                	new TMessage('info', 'Não existe um fechamento em aberto para data baixa !');
                	TTransaction::close(); // close the transaction
                	return;
              	}
                  
              	// inicio baixa
              	if (isset($lancamento->id)) {
                  //var_dump($lancamento->id);

              	  $objectret->lancamento = $lancamento->id;
                  $objectret->status = 'Y';
                
                  // faz a baixa
                  $objectReceber = new ContasReceber($lancamento->id); // instantiates the Active Record
                  
                  $unid = new Unidade($objectReceber->unidade_id);
                  $pess = new Pessoa($unid->proprietario_id);
                  $objectret->unid = $pess->nome;

                  if ( $objectReceber ->situacao != '0' and $objectret->modalidade == '09' ) {
                    new TMessage('info', 'Título '.$objectret->nossoNumero.' NÃO não está em aberto, somente simulado!');  
                  }

                  if ( $objectReceber->situacao == '0' )
                  {
                  	
                  	$objectret->dataPag      ? $objectret->dataPag      = $string->formatDate($objectret->dataPag) : null;
                  	$objectret->dataCredito ? $objectret->dataCredito = $string->formatDate($objectret->dataCredito) : null;

                    $objectReceber->situacao = '1';
                    $objectReceber->dt_pagamento = $objectret->dataPag; 
                    $objectReceber->dt_liquidacao = $objectret->dataCredito; 
                    $objectReceber->valor_pago = $objectret->valorPago;
                    $objectReceber->desconto = $objectret->valorDesconto;
                    $objectReceber->juros = 0;
                    $objectReceber->multa = $objectret->valorMulta;
                    $objectReceber->correcao = 0;

                    $objectReceber->tarifa = $objectret->tarifa;
                    $objectReceber->valor_creditado = $objectret->valor_creditado;

                    $objectReceber->conta_fechamento_id = $contaFechamentoId;
                    
                    $objectReceber->dt_ultima_alteracao = date('Y-m-d');
                    //$object->usuario_id =  TSession::getValue('login');

                    // em casos de pedido de baixa feito pelo cliente, é recebido um retorno com 09 baixa e valor pago = 0
                    if ($param['processa'] == '1' and $objectret->valorPago > 0) {
                      $objectReceber->store(); // update the object in the database
                      new TMessage('info', 'Título '.$objectret->nossoNumero.' baixado com sucesso!');
                    }

                    //if ($param['processa'] == '2') {
                    //  new TMessage('info', 'Título '.$objectret->nossoNumero.' NÃO baixado, simulação!');
                    //}

                    $objectret->dataPag  ? $objectret->dataPag  = $string->formatDateBR($objectret->dataPag ) : null;
            	    $objectret->dataCredito ? $objectret->dataCredito = $string->formatDateBR($objectret->dataCredito) : null;

                  }
                    
                }
              	// fim baixa

                $this->datagrid->addItem($objectret); 
                $i++; // somatorio das linhas para incluir o total

              }
              
              // Verifica que tipo de segmento para remessa, P Q ou R
              if ( substr($Buffer,13,1) == 'P' ) {//or $movimentacao == '02') {
                // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                if (!isset($object))  
                  $object = new stdClass();
                
                $object->modalidadecarteira = substr($Buffer,39,3);
                $object->nossoNumero = intval(substr($Buffer,42,15)); 
                 
                $object->valorTitulo = substr($Buffer,85,15)/100;
                $object->numDocumento = ''.substr($Buffer,62,11);
                $object->dataVencimento = $string->formatDateBR( 
                substr($Buffer,81,4). '-' .
                substr($Buffer,79,2). '-' .
                substr($Buffer,77,2));
                $object->dataEmissao = $string->formatDateBR( 
                substr($Buffer,113,4). '-' .
                substr($Buffer,111,2). '-' .
                substr($Buffer,109,2));
                $object->identificacao = substr($Buffer,195,25); 
                
              }
              
              if ( substr($Buffer,13,1) == 'Q' ) { // identificação sacado na remessa
             
                $object->cpf_cnpj = substr($Buffer,18,15); // campo do datagridrem, em pessoa é cpf ou cnpj
                $object->pagador = substr($Buffer,33,40);
                
                $object->cod_mov_remessa = substr($Buffer,15,2);
                
                $cod_mov_remessa = substr($Buffer,15,2);

                if ($cod_mov_remessa == '01') {
                  $object->cod_mov_remessa = 'Entrada de Título';

                }

                if ($cod_mov_remessa == '02') {
                  $object->cod_mov_remessa = 'Pedido de Baixa';

                }
                
                if ($cod_mov_remessa == '09') {
                  $object->cod_mov_remessa = 'Protestar';

                }

                $object->status = 'N';
                $object->id_ctsreceber = '';
                $object->unid = '';
                
                // trata a tabela contas_receber atualizando os dados da remessa com o lancamento
                
                // verifica se é cpf ou cnpj
                if (substr($object->cpf_cnpj,0,4) == '0000') { // é cpf
                  $proprietario = Pessoa::retornaPessoaCPF(substr($object->cpf_cnpj,4,11));
                } else { // é cnpj
                    //var_dump($object->cpf_cnpj);
                    
                  $proprietario = Pessoa::retornaPessoaCNPJ(substr($object->cpf_cnpj,1,14));
                  //var_dump($proprietario);
                  
                  
                }
                
                $object->unid = $proprietario->nome;
                
                if (isset($proprietario->id)) {
                  $conn = TTransaction::get();
                  $result = $conn->query("select 
                                	     id, descricao
                                         from unidade
                                         where proprietario_id = {$proprietario->id} 
                                         and condominio_id = {$param['condominio_id']}
                                         ");
        
                  $data = '';
                
                  foreach ($result as $row)
                  {
                    $unidade = $row['id'];
                    //var_dump($row['descricao']);
                  }   
                  
                  // o proprietario está cadastrado e agora busco o titulo dele para atualizar
                  $data_vencimento = $string->formatDate($object->dataVencimento);
                  
                  // verifica no contas a receber, se existir mais de um lancamento para formar o boleto não acha aqui (cond+extra)
                  $conn = TTransaction::get();
                  $result = $conn->query("select 
                                	     *
                                         from contas_receber
                                         where valor = {$object->valorTitulo} 
                                         and dt_vencimento = '{$data_vencimento}'
                                         and unidade_id = {$unidade}
                                         ");
        
                  $data = '';
                  
                  //var_dump($result);
                  
                  foreach ($result as $row)
                  {
                    $idreceber = $row['id'];
                    
                    $object->status = 'Y';
                    
                    // atualiza o contas a receber com os dados da remessa, grava o nosso numero para uma baixa automatica
                    $obj = new ContasReceber( $idreceber );
                    
                    $object->id_ctsreceber = $obj->id;
                    
                    // pedido baixa
                    if ($cod_mov_remessa == '02') {
                      $obj->nosso_numero_ant1 = $object->nossoNumero;
                    }
                    
                    //var_dump($object->cod_mov_remessa);

                    if ($cod_mov_remessa == '01') {
                      if (isset($obj->nosso_numero)) {
                        if (isset($obj->nosso_numero_ant1)) {
                          $obj->nosso_numero_ant2 = $obj->nosso_numero;
                          $obj->nosso_numero = $object->nossoNumero;
                        } else {
                          $obj->nosso_numero_ant1 = $obj->nosso_numero;
                          $obj->nosso_numero = $object->nossoNumero;
                        }  
                      } else {
                        $obj->nosso_numero = $object->nossoNumero;
                      
                      }
                    }                  
                    
                    $obj->store();
                                     
                  }
                  
                  //var_dump($idreceber);
                  
                  if ($idreceber == '') {
                  
                    // verifica no contas a receber, caso de varios lancamentos para um boleto (cond+extra)
                    $conn = TTransaction::get();
                    $result = $conn->query("select 
                                	     id, valor, nosso_numero, nosso_numero_ant1, nosso_numero_ant2, nosso_numero_ant3,
                                	     dt_vencimento, unidade_id
                                         from contas_receber
                                         where dt_vencimento = '{$data_vencimento}'
                                         and unidade_id = {$unidade}                                         
                                         ");
        
                    $data = '';
                    
                    //var_dump($result);
                  
                    // varios id do contas a receber para formar o boleto
                    $ids = 'Ids.: ';
                  
                    foreach ($result as $row)
                    {
                      $data = $row['id'];
                    
                      $object->status = 'Y';
                    
                      // atualiza o contas a receber
                      $obj = new ContasReceber( $data );
                    
                      $ids = $ids . $obj->id . ' ';
                                        
                      // pedido baixa
                      if ($cod_mov_remessa == '02') {
                        $obj->nosso_numero_ant1 = $object->nossoNumero;
                      }
                    
                      if ($cod_mov_remessa == '01') {
                        if (isset($obj->nosso_numero)) {
                          if (isset($obj->nosso_numero_ant1)) {
                            $obj->nosso_numero_ant2 = $obj->nosso_numero;
                            $obj->nosso_numero = $object->nossoNumero;
                          } else {
                            $obj->nosso_numero_ant1 = $obj->nosso_numero;
                            $obj->nosso_numero = $object->nossoNumero;
                          }  
                        } else {
                          $obj->nosso_numero = $object->nossoNumero;
                      
                        }
                      }                  
              
                      $obj->store();   
                                   
                    }
                  
                    $object->id_ctsreceber = $ids;
                    
                  }
                                
                ////////////////////
                
                }
                
                // atualiza datagridrem
                $this->datagridrem->addItem($object);
              }
              
            }
                
          }
           
          fclose($FileHandle);
          
          // close the transaction
          TTransaction::close();

          // coloca os talizadores na tela
          $row = new TTableRow;
          $row->style = "background-color: #E0DEF8";
          $cell = $row->addCell('Totais');
          $cell->{'style'}='text-align:center';
          $cell->colspan = 1;//quanto desejar
          $cell = $row->addCell(number_format($total_tarifa, 2, ',', '.'));
          $cell->{'style'}='text-align:center';
          $cell->colspan = 1;//quanto desejar
          $cell = $row->addCell(number_format($total_titulo, 2, ',', '.'));
          $cell->colspan = 1;//quanto desejar
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(number_format($total_creditado, 2, ',', '.'));
          $cell->colspan = 1;
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $cell = $row->addCell(number_format($total_pago, 2, ',', '.'));
          $cell->colspan = 1;
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(number_format($total_acrescimo, 2, ',', '.'));
          $cell->colspan = 1;
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(number_format($total_desconto, 2, ',', '.'), 'right');
          $cell->{'style'}='text-align:center';
          $cell->colspan = 1;
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $cell->{'style'}='text-align:center';
          $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $this->datagrid->insert($i +1 , $row);
           $cell = $row->addCell(' ');
          $cell->colspan = 1;
          $this->datagrid->insert($i +1 , $row);


   
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message

        }
    }



}

