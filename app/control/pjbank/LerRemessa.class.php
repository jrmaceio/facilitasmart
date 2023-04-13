<?php
/**
 * @author  <your name here>
 */
class LerRemessa extends TPage
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
        $this->form = new BootstrapFormBuilder('form_LerRemessa');
        $this->form->setFormTitle('Ler Arquivo Remessa e Sincroniza');

        // create the form fields
        $id = new THidden('id');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        //$filename->setService('SystemDocumentUploaderService');

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id] );
        $condominio_id->setSize('50%');
         
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        $this->form->addAction('Tratar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        
        ////
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        
        
        // creates the datagrid columns
        $column_nossonumero = new TDataGridColumn('nossoNumero', 'Nosso Número', 'center');
        $column_numdocumento = new TDataGridColumn('numDocumento', 'No. Documento', 'center');
        $column_valor_titulo = new TDataGridColumn('valorTitulo', 'Valor', 'center');
        $column_data_emissao = new TDataGridColumn('dataEmissao', 'Emissão', 'center');
        $column_data_vencimento = new TDataGridColumn('dataVencimento', 'Vencimento', 'center');
        $column_identificacao = new TDataGridColumn('identificacao', 'Identificação', 'center');
        $column_cpf_cnpj = new TDataGridColumn('cpf_cnpj', 'CPF / CNPJ', 'center');
        $column_pagador = new TDataGridColumn('pagador', 'Pagador', 'center');
        $column_numDocumento = new TDataGridColumn('numDocumento', 'Documento', 'center');
        $column_cod_mov_remessa = new TDataGridColumn('cod_mov_remessa', 'Cod.Movim.', 'center');
        $column_status = new TDataGridColumn('status', 'Identificado', 'center');
        $column_id_ctsreceber = new TDataGridColumn('id_ctsreceber', 'Id Cts Rebeber', 'center');
        $column_modcarteira = new TDataGridColumn('modalidadecarteira', 'Mod Carteira', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nossonumero);
        //$this->datagrid->addColumn($column_numDocumento);
        $this->datagrid->addColumn($column_valor_titulo);
        $this->datagrid->addColumn($column_data_emissao);
        $this->datagrid->addColumn($column_data_vencimento);
        //$this->datagrid->addColumn($column_identificacao);
        $this->datagrid->addColumn($column_cpf_cnpj);
        $this->datagrid->addColumn($column_pagador);
        $this->datagrid->addColumn($column_cod_mov_remessa);
        $this->datagrid->addColumn($column_status);

        $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Modalidade Carteira'.'</b><br>' . '{modalidadecarteira}' 
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
          $string = new StringsUtil;
          
          $this->datagrid->clear();
          
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          $this->_file =$param['filename'];
                    
          // Se existe o arquivo faz upload.
          //var_dump($this->_file);
          if ($this->_file)
            {
                $target_folder = 'boletos/remessa';
                $target_file   = $target_folder . '/' .$this->_file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            } 
            
          $FileHandle = @fopen('boletos/remessa/'.$param['filename'], "r");
          //var_dump($BasePath);
        
          $primeiralinha = false;
          $segundalinha = false;
          $string = new StringsUtil;
          
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
              
              // Verifica que tipo de segmento para remessa, P Q ou R
              // Verifica que tipo de segmento para retorno, T ou U
              if ( substr($Buffer,13,1) == 'P' ) {
                // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                if (!isset($object))  
                  $object = new stdClass();
                
                $object->modalidadecarteira = substr($Buffer,39,3);
                $object->nossoNumero = intval(substr($Buffer,42,15)); 
                 
                $object->valorTitulo = substr($Buffer,85,15)/100;
                $object->numDocumento = substr($Buffer,62,11);
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
              
              if ( substr($Buffer,13,1) == 'Q' ) {
             
                $object->cpf_cnpj = substr($Buffer,18,15); // campo do datagrid, em pessoa é cpf ou cnpj
                $object->pagador = substr($Buffer,33,40);
                $object->cod_mov_remessa = substr($Buffer,15,2);
                
                $object->status = 'N';
                $object->id_ctsreceber = '';
                
                // trata a tabela contas_receber atualizando os dados da remessa com o lancamento
                
                // verifica se é cpf ou cnpj
                if (substr($object->cpf_cnpj,0,4) == '0000') { // é cpf
                  $proprietario = Pessoa::retornaPessoaCPF(substr($object->cpf_cnpj,4,11));
                } else { // é cnpj
                  $proprietario = Pessoa::retornaPessoaCNPJ(substr($object->cpf_cnpj,0,14));
                }
                
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
                  
                  foreach ($result as $row)
                  {
                    $data = $row['id'];
                    
                    // atualiza o contas a receber
                    $obj = new ContasReceber( $data );
                          
                    // preenche o datagrid                  
                    $object->id_ctsreceber = $obj->id;
                    
                    // pedido baixa
                    if ($object->cod_mov_remessa == '02') {
                      $obj->nosso_numero_ant1 = $object->nossoNumero;
                    }
                    
                    if ($object->cod_mov_remessa == '01') {
                      if (isset($obj->nosso_numero)) // caso em que o morador tem mais de uma unidade tambem deu PAU 
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
                    
                    $object->status = 'Y';                 
                    $obj->store();
                                     
                  }
                  
                  if ($data == '') {
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
                    if ($object->cod_mov_remessa == '02') {
                      $obj->nosso_numero_ant1 = $object->nossoNumero;
                    }
                    
                    if ($object->cod_mov_remessa == '01') {
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
                // atualiza datagrid
                $this->datagrid->addItem($object);
              }
              
            }
                
            
          }
           
          fclose($FileHandle);
          
          // close the transaction
          TTransaction::close();
   
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message

        }
    }



}

