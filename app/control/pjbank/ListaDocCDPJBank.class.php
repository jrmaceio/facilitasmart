<?php
/**
 * @author  <your name here>
 */
class ListaDocCDPJBank extends TPage
{
    protected $form; // form
   
    private $_file;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
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
        
        $this->string = new StringsUtil;
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ListaDocCDPJBank');
        $this->form->setFormTitle('Listar Documentos da Conta Digital PJBank');
        
        $this->form->addAction('Consultar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';

        //$this->datagrid->disableDefaultClick(); // important! check
       
        // creates the datagrid columns
        $column_imagem = new TDataGridColumn('imagem', 'Imagem', 'center');
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'center');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'center');
        $column_formato = new TDataGridColumn('formato', 'Formato', 'center');
        $column_tamanho = new TDataGridColumn('tamanho', 'Tamanho', 'center');
        $column_data = new TDataGridColumn('data', 'Data', 'center');
        
        $this->datagrid->addColumn($column_imagem);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_formato);
        $this->datagrid->addColumn($column_tamanho);
        $this->datagrid->addColumn($column_data);
        
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
    
    public function onNext($param)
    {
        TTransaction::open('facilitasmart');
        
        $condominio = new Condominio(TSession::getValue('id_condominio')); 
                
        if ($condominio->credencial_cd_pjbank == '') {
            new TMessage('Conta Digital', 'Condomínio não possue uma conta digital !');
            TTransaction::close(); // close the transaction
            return;
        }
        
               
        try {
            
                $parameters = array();
                $parameters['email'] = $param['email'];                
                
                $json = json_encode($parameters);
                
                $curl = curl_init();

                curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$condominio->credencial_cd_pjbank."/documentos",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE-CONTA: " . $condominio->chave_cd_pjbank
              ),));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                //var_dump(curl_getinfo($curl));
                
                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $jsonRets=json_decode($response, true);
                    
                    //var_dump($jsonRets);
                    
                    if ($jsonRets['status'] < '300') {
                        foreach ( $jsonRets as $jsonRet ) {
                            $object = new stdClass();
	                        $object->imagem = $jsonRet->imagem;
	                        $object->tipo = $jsonRet->tipo;
                            $object->nome = $jsonRet->nome;
                            $object->formato = $jsonRet->formato;
                            $object->tamanho = $jsonRet->tamanho;
                            $object->data = $jsonRet->data;
	                        $this->datagrid->addItem($object);
                        }                  
                        
                    } else {
                        new TMessage('info', 
                                     "Status   : ". $jsonRets['status']." </br >".
                                     "Mensagem : ". $jsonRets['msg']." </br >" ); 
                    }
                  
                } 
                
                TTransaction::close(); // close the transaction
                    
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }


}

