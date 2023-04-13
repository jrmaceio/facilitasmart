<?php
/**
 * @author  <your name here>
 */
class SegundaViaBoletos extends TPage
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
        $this->form = new BootstrapFormBuilder('form_2aViaBoletos');
        $this->form->setFormTitle('2a via de Boletos');

        // create the form fields
        $id = new THidden('id');
        
        $cpf = new TEntry('cpf');

        $row = $this->form->addFields( [new TLabel('ID')], [$id] );
        $row->style = 'display:none';
        
        $this->form->addFields( [new TLabel('CPF')], [$cpf] );
        $cpf->setSize('50%');
         
        $this->form->addAction('Buscar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        
        ////
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->style = 'background: white';
        //$this->datagrid->datatable = 'true';

        // creates the datagridrem columns
        $column_id = new TDataGridColumn('id', 'Id', 'center');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref.', 'center');
        $column_sacado = new TDataGridColumn('sacado', 'Sacado', 'center');
        $column_condominio = new TDataGridColumn('condominio', 'Condomínio', 'center');
        $column_data_vencimento = new TDataGridColumn('data_vencimento', 'Vencimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_sacado);
        $this->datagrid->addColumn($column_condominio);
        $this->datagrid->addColumn($column_id);
        
        //$this->datagridrem->enablePopover('Informações Complementares', '<b>'.'Modalidade Carteira'.'</b><br>' . '{modalidadecarteira}' 
        //    . '<br><b>'.'Id Cts Receber'.'</b><br>' . '{id_ctsreceber}'
        //    . '<br><b>'.'Documento'.'</b><br>' . '{numDocumento}');
            
                       
        $column_valor->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        $this->datagrid->disableDefaultClick();
        

        $action_boleto = new TDataGridAction(array($this, 'onBoleto'));
        $action_boleto->setButtonClass('btn btn-default');
        $action_boleto->setLabel(('Boleto'));
        $action_boleto->setImage('fa:barcode fa-lg black');
        $action_boleto->setField('id');
        $this->datagrid->addAction($action_boleto);

        /*
        $action_boleto = new TDataGridAction(array($this, 'onPJBankBoleto'));
        $action_boleto->setButtonClass('btn btn-default');
        $action_boleto->setLabel(('Boleto'));
        $action_boleto->setImage('fa:barcode fa-lg black');
        $action_boleto->setField('id');
        $this->datagrid->addAction($action_boleto);
        
        $action6 = new TDataGridAction(['ContasReceberListagemAux', 'onInputDialog']);
        $action6->setLabel('Sicredi');
        $action6->setImage('fa:barcode fa-lg green');
        $action6->setField('id');
		$this->datagrid->addAction($action6);
        */
        
        // create the datagridrem model
        $this->datagrid->createModel();
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
                
        $container->add(new TLabel('Boletos'));
        
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

        if ( $param['cpf'] == '' ) {
            new TMessage('info', 'Informe o CPF !'); // success message
            TTransaction::close(); // close the transaction
            return;
                  
        }

          $string = new StringsUtil;
          
          $this->datagrid->clear();
          
          // open a transaction with database 'facilita'
          TTransaction::open('facilitasmart');
          
          $cpf = $param['cpf'];
          $cpf = str_replace('.', '', $cpf);
          $cpf = str_replace('-', '', $cpf);

          $conn = TTransaction::get();

          // nao mostra vencidos
          //$result = $conn->query("select * from contas_receber where unidade_id in 
          //    (select id from unidade where proprietario_id in (select id from pessoa where cpf = {$cpf}))
          //    and situacao = '0' and (dt_vencimento >= current_date()) order by dt_vencimento asc");
          
          // mostra vencido
          $result = $conn->query("select * from contas_receber where unidade_id in 
              (select id from unidade where proprietario_id in (select id from pessoa where cpf = {$cpf}))
              and situacao = '0' order by dt_vencimento asc");

          foreach ($result as $row)
          {

            $unidade = new Unidade($row['unidade_id']);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $object->sacado = $pessoa->nome;
            
            $condominio = new Condominio($row['condominio_id']);

            if ($condominio->active == 'Y') {
                $object = new stdClass();
                $object->id = $row['id'];
                $object->mes_ref = $row['mes_ref'];
                $object->condominio = $condominio->resumo;
            
                $data_vencimento = $string->formatDateBR($row['dt_vencimento']);
                $object->data_vencimento = $data_vencimento;
             
                $object->valor = $row['valor'];
            
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



    public function onBoleto($param)
    {
        $key=$param['key']; // get the parameter $key
          
        TTransaction::open('facilitasmart'); // open a transaction with database
        
        $object = new ContasReceber($key, FALSE); // instantiates the Active Record
    
        if ($object->id_conta_corrente != '')
        {
            ContasReceberListagemAux::onInputDialog($param);
        }
        
        
        if ($object->pjbank_id_unico != '')
        {        
            $this->onPJBankBoleto($param);
        }
        
        TTransaction::close();
    }


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

            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    

}

