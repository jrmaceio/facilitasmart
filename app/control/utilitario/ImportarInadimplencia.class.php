<?php
/**
 * @author  <your name here>
 */
class ImportarInadimplencia extends TPage
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
        $this->form = new BootstrapFormBuilder('form_TrataRetornoRemessa');
        $this->form->setFormTitle('Importar Inad. 2021 - 11 Canafístula');

        // create the form fields
        $id = new THidden('id');
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $filename = new TFile('filename');
        //$filename->setService('SystemDocumentUploaderService');

        // allow just these extensions
        $filename->setAllowedExtensions( ['csv'] );
        

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        
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

        // creates the datagridrem columns
        $column_quadra = new TDataGridColumn('quadra', 'Quadra', 'center');
        $column_lote = new TDataGridColumn('lote', 'Lote', 'center');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'center');
        $column_mes = new TDataGridColumn('mes', 'Mês Não Pago', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_quadra);
        $this->datagrid->addColumn($column_lote);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_mes);
        
        // create the datagridrem model
        $this->datagrid->createModel();
              
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        $container->add($this->datagrid);
        
        parent::add('Condomínio : 11 - Canafístula');
        
        
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
          $condominio_id = 11;

          while (!feof($FileHandle))
          {
            $Buffer = fgets($FileHandle,4096);
           
            // inicia o percorrer o arquivo para pegar os seguimentos P(titulo) e U(sacado)      
            if ( $primeiralinha and $segundalinha ) {
                              
                // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                if (!isset($object))  
                  $object = new stdClass();

                $linha = explode(",", $Buffer); // separador ,
                
                //var_dump($linha); 
                $gera_titulo = "Y";
                
                $object->quadra = $linha[1]; 
                $object->lote = $linha[2];
                                             
                              
                // verifico se no cadastro da unidade é o mesmo nome da planilha
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', 11)); // canafistula
                $criteria->add(new TFilter('bloco_quadra', '=', $object->quadra));
                $criteria->add(new TFilter('descricao', '=', $object->lote));                
                $repository = new TRepository('Unidade');
                $unidades = $repository->load($criteria);
                
                foreach ($unidades as $unidade)
                {
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    //var_dump($pessoa->nome);
                    $object->nome = $pessoa->nome;
                    
                    $unidade = $unidade->id;
                }
                
                /*
                // for do mes 01 ate 12/2020
                if ($linha[5] == '') { // janeiro
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '01/2020';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2020-01-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 01/2020';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';                 
                    $objectRec->store();    
                    
                    $object->mes = '01/2020';  
                    $this->datagrid->addItem($object);                  
                }
                
                if ($linha[6] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '02/2020';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2020-02-28';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 02/2020';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';
                    $objectRec->parcela = '0';  
                    $objectRec->usuario = 'importado';                     
                    $objectRec->store();    
                    
                    $object->mes = '02/2020';  
                    $this->datagrid->addItem($object);                  
                }
                */
                if ($linha[7] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '03/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-03-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 03/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';
                    $objectRec->parcela = '0'; 
                    $objectRec->usuario = 'importado';                      
                    $objectRec->store();    
                    
                    $object->mes = '03/2019';  
                    $this->datagrid->addItem($object);                  
                }
                
                if ($linha[8] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '04/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-04-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 04/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';
                    $objectRec->parcela = '0';   
                    $objectRec->usuario = 'importado';                    
                    $objectRec->store();    
                    
                    $object->mes = '04/2019';  
                    $this->datagrid->addItem($object);                  
                }
                
                if ($linha[9] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '05/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-05-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 05/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '05/2019';  
                    $this->datagrid->addItem($object);                  
                }
                
                if ($linha[10] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '06/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-06-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 06/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '06/2019';  
                    $this->datagrid->addItem($object);                  
                }
                
                if ($linha[11] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '07/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-07-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 07/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '07/2019';  
                    $this->datagrid->addItem($object);                  
                }

                if ($linha[12] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '08/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-08-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 08/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '08/2019';  
                    $this->datagrid->addItem($object);                  
                }   
                
                if ($linha[13] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '09/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-09-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 09/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '09/2019';  
                    $this->datagrid->addItem($object);                  
                } 
                
                if ($linha[14] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '10/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-10-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 10/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '10/2019';  
                    $this->datagrid->addItem($object);                  
                }  
                
                if ($linha[15] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '11/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-11-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 11/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '11/2019';  
                    $this->datagrid->addItem($object);                  
                } 
                
                if ($linha[16] == '') { 
                    // atualiza contas a receber
                    $objectRec = new ContasReceber;  // create an empty object
                    $objectRec->condominio_id = 11;
                    $objectRec->mes_ref = '12/2019';
                    $objectRec->cobranca = '1';
                    $objectRec->tipo_lancamento = 'M';
                    $objectRec->classe_id = 2;
                    $objectRec->unidade_id = $unidade;
                    $objectRec->nome_responsavel = $pessoa->nome;
                    $objectRec->dt_lancamento = '2021-07-17';
                    $objectRec->dt_vencimento = '2019-12-30';
                    $objectRec->valor = 50.00;
                    $objectRec->descricao = 'TAXA DE MANUTENCAO REF. 12/2019';
                    $objectRec->situacao = '0';
                    $objectRec->conta_fechamento_id = 9;
                    $objectRec->split_active = 'N';    
                    $objectRec->parcela = '0';    
                    $objectRec->usuario = 'importado';               
                    $objectRec->store();    
                    
                    $object->mes = '12/2019';  
                    $this->datagrid->addItem($object);                  
                }            
              }
              
              // teste com a 1a linha
              //TTransaction::close();
              //return;
              
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

