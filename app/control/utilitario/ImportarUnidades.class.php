<?php
/**
 * @author  <your name here>
 */
class ImportarUnidades extends TPage
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
        $this->form->setFormTitle('Atualizar Unidades - 31 Canios');

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
        $column_cpf = new TDataGridColumn('cpf', 'CPF', 'center');
        $column_rg = new TDataGridColumn('rg', 'RG', 'center');
        $column_celular = new TDataGridColumn('celular1', 'Celular', 'center');
        $column_email = new TDataGridColumn('email', 'E-Mail', 'center');
        $column_endereco = new TDataGridColumn('endereco', 'Endereco', 'center');
        
        $column_bairro = new TDataGridColumn('bairro', 'Bairro', 'center');
        $column_cidade = new TDataGridColumn('cidade', 'Cidade', 'center');
        $column_estado = new TDataGridColumn('estado', 'Estado', 'center');
        
        $column_cep = new TDataGridColumn('cep', 'CEP', 'center');
        
        $column_complemento = new TDataGridColumn('complemento', 'Complemento', 'center');
        
        $column_nomebanco = new TDataGridColumn('nome_banco', 'Nome Banco', 'center');
        $column_nomeplani = new TDataGridColumn('nome_plani', 'Nome Planilha', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_quadra);
        $this->datagrid->addColumn($column_lote);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_rg);
        $this->datagrid->addColumn($column_celular);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_endereco);
        $this->datagrid->addColumn($column_bairro);
        $this->datagrid->addColumn($column_cidade);
        $this->datagrid->addColumn($column_estado);
        $this->datagrid->addColumn($column_cep);
        $this->datagrid->addColumn($column_complemento);
        $this->datagrid->addColumn($column_nomebanco);
        $this->datagrid->addColumn($column_nomeplani);
        
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
          $condominio_id = 31; /////// condominio canions

          while (!feof($FileHandle))
          {
            $Buffer = fgets($FileHandle,4096);
           
            // inicia o percorrer o arquivo para pegar os seguimentos P(titulo) e U(sacado)      
            if ( $primeiralinha and $segundalinha ) {
                              
                // resolvendo o erro : Warning: Creating default object from empty value in /var/www/html/app/control/boleto/BoletoTesteRetorno.class.php on line 218 
                if (!isset($object))  
                  $object = new stdClass();

                  $linha = explode(";", $Buffer); /////////////// separador ;
                
                  //var_dump($linha); 
                  $gera_titulo = "Y";
                
                  $object->quadra = $linha[7]; 
                  $object->lote = str_pad($linha[8] , 3 , '0' , STR_PAD_LEFT); //$linha[2];
                  $object->nome = $linha[3];
                  
                  $object->cpf = $linha[4];
                  $object->rg =  $linha[7];
                  $cel = str_replace('.','',$linha[14]);
                  $cel = str_replace('-','',$cel);
                  $cel = str_replace(' ','',$cel);
                  $object->celular1 = $cel;//$linha[7];
                  $object->celular2 = '';//$linha[12];
                  $object->celular3 = '';//$linha[6];
                  $object->email = $linha[15];
                  $object->complemento = $linha[10];
                  $object->observacao = $object->observacao . ' ' . $linha[3];
                  $object->endereco = $linha[8];
                  $object->bairro = $linha[11];
                  $object->numero = $linha[9];
                  //$cidade_uf = explode("/", $linha[9]);
                  //$object->cidade = $cidade_uf[0];
                  //$object->estado = $cidade_uf[1];
                  $object->cidade = $linha[12];
                  $object->estado = $linha[13];
                  $object->cep =$linha[11]; 
                  
                  // atualiza datagridrem
                  $this->datagrid->addItem($object);
                
                  // gravar a pessoa
                  // colocar o endereco no campo observacao e usar o endereco padrao do residencial
           
                  //if ( $linha[0] != '' ) {

                  //// localiizar em pessoa e nao criar uma nova $objectPes = new Pessoa;  // create an empty object
                  // rotina para atualizar dados de pessoas
              

                  $id_pessoa = Uteis::buscaNome($linha[3],'Pessoa','nome','id');
                  $objectPes = new Pessoa($id_pessoa);

                  //if ( $objectPes->nome == $linha[3]) {
                    //var_dump('achei ' . $linha[3] . ' id Pessoa' . $objectPes->id);  
                    //return;
                  //}


                  if ($object->nome=='') {
                      $objectPes->email = 'VAZIO';
                      $object->cpf = '01234567890';
                  } else {
                      $objectPes->nome = $object->nome;
                  }
                  
                  
                  $objectPes->rg = $object->rg;
                  
                  $cpf_cnpj = str_replace('.','',$object->cpf);
                  $cpf_cnpj = str_replace('-','',$cpf_cnpj);
                  $cpf_cnpj = str_replace('/','',$cpf_cnpj);
             
                  if (strlen($cpf_cnpj)>11) {
                    $objectPes->pessoa_fisica_juridica = 'J';
                    $objectPes->cnpj = $cpf_cnpj;
                  }else {
                    $objectPes->pessoa_fisica_juridica = 'F';  
                    $objectPes->cpf = $cpf_cnpj;
                    
                  }
                  
           
                  //$tel = explode("/", $linha[5]);
                  //if (isset($tel[0]))     
                  $objectPes->telefone1 = $object->celular1;
                  //if (strlen($objectPes->telefone1) == 8) {             
                   //   $objectPes->telefone1 = '9'.$object->celular1;
                  //    }
                  //if (isset($tel[1]))    
                 //     $objectPes->telefone2 = $object->celular2;
                  //if (isset($tel[2]))
                  //    $objectPes->telefone3 = $object->celular3;
                  
                  if ($object->email=='') {
                      $objectPes->email = 'teste@teste.com.br';
                  } else {
                      $objectPes->email = $object->email;
                  }
                  
                  $objectPes->observacao = $object->complemento; 
                  
                  $objectPes->endereco = $object->endereco;
                  $objectPes->numero = $object->numero;
                  $objectPes->bairro = $object->bairro;
                  
                  $cep = str_replace('.','',$object->cep);
                  $cep = str_replace('-','',$cep);
                  $objectPes->cep = $cep;
                  
                  $objectPes-> cidade = $object->cidade;
                  $objectPes->estado = $object->estado;
                  
                  ////////////////////////////$objectPes->condominio_id = 31; // CANIONS !!!!!!!!!!!
                  
                
                  if ($objectPes->condominio_id == 31) {
                    $objectPes->store();  
                  }
                  

             
                  
                  
                /*
                  // cadastro da unidade
                  $objectUni = new Unidade;  // create an empty object
                  $objectUni->bloco_quadra = $object->quadra;
                  ////$objectUni->descricao = 'QD ' . $object->quadra . ' LT '. $object->lote; ///////INCLUIR O ZERO PARA 2 DIGITOS
                  $objectUni->descricao = $object->lote; ///////INCLUIR O ZERO PARA 2 DIGITOS
                  $objectUni->condominio_id = 31;   //CANIONS  - CONDOMINIO !!!!!!!!!!!!!!
                  $objectUni->proprietario_id = $objectPes->id;
                  $objectUni->morador_id = $objectPes->id;
                  $objectUni->envio_boleto = '3';
                  $objectUni->observacao = $object->nome;
                  $objectUni->grupo_id = '1';
                  $objectUni->gera_titulo = $gera_titulo;
                  
                  $objectUni->store();

                  NAO FAZ NADA EM UNDIADE*/
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

