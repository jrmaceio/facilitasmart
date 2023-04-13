<?php
/**
 * @author  <your name here>
 * Faz a leitura do arquivo csv pego no site do banco (francesinha nova)
 * é necessário criar duas colunas no inicio, uma com a identificação da unidade e a 2a com o vencimento
 * 
 */
class LerRetornoSicred extends TPage
{
    protected $form; // form
    
    private $datagrid; // listing
    
    private $_file;
    
    // trait with onSave, onClear, onEdit, ...
    use Adianti\Base\AdiantiStandardFormTrait;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ImportaBaixa');
        $this->form->setFormTitle('Importar Baixas');

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

        // create the form fields
        $id = new THidden('id');
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        //$filename->setService('SystemDocumentUploaderService');

        // allow just these extensions
        $filename->setAllowedExtensions( ['csv'] );

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        // controle dos pago ou aberto = situacao
        $processa = new TRadioGroup('processa'); 

        $processa->addValidation('Processa', new TRequiredValidator());  
        
        $processa->addItems(['1'=>'Processar','2'=>'Não Processa']);
        $processa->setLayout('horizontal');
        //$processa->setBooleanMode();
        $processa->setValue('2');
        
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        $this->form->addAction('Tratar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        $this->form->addFields( [new TLabel('Aplicar')], [$processa] );
        ////
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';

        // creates the datagridrem columns
        $column_quadra = new TDataGridColumn('quadra', 'Quadra', 'center');
        $column_lote = new TDataGridColumn('lote', 'Lote', 'center');
        $column_nome_arq = new TDataGridColumn('nome_arq', 'Nome Retorno', 'center');
        $column_nome_cad = new TDataGridColumn('nome_cad', 'Nome Cadastro', 'center');
        $column_dtmovimento = new TDataGridColumn('dtmovimento', 'Dt Movimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor Pag.', 'center');
        $column_status = new TDataGridColumn('status', 'Status', 'center');

        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_quadra);
        $this->datagrid->addColumn($column_lote);
        $this->datagrid->addColumn($column_nome_arq);
        $this->datagrid->addColumn($column_nome_cad);
        $this->datagrid->addColumn($column_dtmovimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_status);
        
        // create the datagridrem model
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
          
          //DEBUG
          //TTransaction::SetLogger(new TLoggerSTD);

          $this->_file =$param['filename'];

          // Se existe o arquivo faz upload.
          if ($this->_file)
            {
                $target_folder = 'tmp';
                $target_file   = $target_folder . '/' .$this->_file;
                @mkdir($target_folder);
                rename('tmp/'.$this->_file, $target_file);
            } 
            
          $FileHandle = @fopen('tmp/'.$param['filename'], "r");
        
          $primeiralinha = true;
          $segundalinha = true;
          $string = new StringsUtil;

          while (!feof($FileHandle))
          {
            $Buffer = fgets($FileHandle,4096);
 
            // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
            //if (!isset($object)) $object = new stdClass();
            $object = new stdClass();


            $linha = explode(";", $Buffer); // separador ;
            
            $qd_lote = explode("-",$linha[0]);

            $object->quadra = $qd_lote[0];
            $object->lote = $qd_lote[1];

            $object->nome_arq = $linha[4];
            $object->dtmovimento = TDate::date2us( $linha[5]); 

            $str = $linha[12];
            $str = str_replace('R$', '', $str);
            $str = str_replace(',', '.', $str);
            $object->valor =  $str;
           
            $object->nome_cad = '';
                
            $object->status = 'erro32';
            

            if ( $object->quadra != '' ) {
              $id_condominio = TSession::getValue('id_condominio');

              $unidades = Unidade::where('condominio_id', '=', $id_condominio)->
                                         where('bloco_quadra', '=',  $object->quadra)->
                                         where('descricao', '=',  $object->lote)->load();

              //$repository = new TRepository('Unidade');
              //$unidade = $repository->where('condominio_id', '=', $id_condominio,TExpression::AND_OPERATOR)
              //                      ->where('bloco_quadra', '=', $object->quadra,TExpression::AND_OPERATOR)
              //                      ->where('descricao', '=', $object->lote,TExpression::AND_OPERATOR)
              //                      ->load();

              foreach ($unidades as $unidade)
              { 
                //var_dump($unidade->proprietario_id);
                $pessoa = new Pessoa($unidade->proprietario_id);
                $object->nome_cad = $pessoa->nome;

                $titulos = ContasReceber::where('dt_vencimento', '=', '2020-09-30')->
                                         where('unidade_id', '=',  $unidade->id)->
                                         where('condominio_id', '=',  $id_condominio)->load();

                foreach ($titulos as $titulo)
                { 
                  if ($titulo->id != '') {
                    $object->status = 'ok'; 

                    // faz a baixa
                    $objectReceber = new ContasReceber($titulo->id);
                    $objectReceber->nosso_numero = $linha[2];

                    $objectReceber->situacao = '1';
                    $objectReceber->dt_pagamento = TDate::date2us( $linha[5]); 
                    $objectReceber->dt_liquidacao = TDate::date2us( $linha[6]); 

                    $str = $linha[7];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->valor = $str;

                    $str = $linha[12];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->valor_pago = $str;

                    $str = $linha[9];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->desconto = $str;

                    $str = $linha[10];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->juros = $str;

                    $str = $linha[11];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->multa = $str;

                    $objectReceber->correcao = 0;

                    $objectReceber->tarifa = 0;

                    $str = $linha[12];
                    $str = str_replace('R$', '', $str);
                    $str = str_replace(',', '.', $str);
                    $str = str_replace(' ', '', $str);
                    $objectReceber->valor_creditado = $str;

                    $objectReceber->dt_ultima_alteracao = date('Y-m-d');
                    //$object->usuario_id =  TSession::getValue('login');

                    // em casos de pedido de baixa feito pelo cliente, é recebido um retorno com 09 baixa e valor pago = 0
                    if ($param['processa'] == '1') {
                      $objectReceber->store(); // update the object in the database
                      //new TMessage('info', 'Título '.$objectret->nossoNumero.' baixado com sucesso!');
                    }

                 
                  }

                  
                }
              }

                              
                           
            }
            
            // atualiza datagridrem
            $this->datagrid->addItem($object);

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

