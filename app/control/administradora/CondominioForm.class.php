<?php
/**
 * CondominioForm Form
 * @author  <your name here>
 */
class CondominioForm extends TPage
{
    protected $form; // form
    
   /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Condominio');
        $this->form->setFormTitle('Condomínio');
        
        // creates the notebook page
        $page1 = new TTable;
        $page2 = new TTable;
        $page3 = new TTable; 
        $page4 = new TTable; 
        $page5 = new TTable; 
        $page6 = new TTable;
        $page7 = new TTable; 
        
        $this->form->appendPage('Principal', $page1);
        
        // create the form fields
        $id = new TEntry('id');
        $resumo = new TEntry('resumo');
        $nome = new TEntry('nome');
        $cnpj = new TEntry('cnpj');
        $inscricao_municipal = new TEntry('inscricao_municipal');
        $cep = new TEntry('cep');
        $endereco = new TEntry('endereco');
        $numero = new TEntry('numero');
        $bairro = new TEntry('bairro');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $site = new TEntry('site');
        $email = new TEntry('email');
        $telefone1 = new TEntry('telefone1');
        $telefone2 = new TEntry('telefone2');
        //$active = new TEntry('active');
        //$dt_cadastro = new TEntry('dt_cadastro');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Resumo') ], [ $resumo ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('CNPJ') ], [ $cnpj ], [ new TLabel('Inscrição Municipal') ], [ $inscricao_municipal ] );
        $this->form->addFields( [ new TLabel('CEP') ], [ $cep ] );
        $this->form->addFields( [ new TLabel('Endereço') ], [ $endereco ], [ new TLabel('No.') ], [ $numero ] );
        $this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ], [ new TLabel('Cidade') ], [ $cidade ] );
        $this->form->addFields( [ new TLabel('Estado') ], [ $estado ] );
        $this->form->addFields( [ new TLabel('Site') ], [ $site ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Telefone 1') ], [ $telefone1 ], [ new TLabel('Telefone 2') ], [ $telefone2 ] );
        //$this->form->addFields( [ new TLabel('Active') ], [ $active ] );
        //$this->form->addFields( [ new TLabel('Dt Cadastro') ], [ $dt_cadastro ] );

        // set sizes
        $id->setSize('100%');
        $resumo->setSize('100%');
        $nome->setSize('100%');
        $cnpj->setSize('100%');
        $inscricao_municipal->setSize('100%');
        $cep->setSize('100%');
        $endereco->setSize('100%');
        $numero->setSize('50%');
        $bairro->setSize('100%');
        $cidade->setSize('100%');
        $estado->setSize('100%');
        $site->setSize('100%');
        $email->setSize('100%');
        $telefone1->setSize('100%');
        $telefone2->setSize('100%');
        //$active->setSize('100%');
        //$dt_cadastro->setSize('100%');

        $telefone1->setMask('(99)9999-99999'); 
        $telefone2->setMask('(99)9999-99999'); 
        $cep->setMask('99.999-999');
        $cnpj->setMask('99.999.999/9999-99');
        
        // buscar sep
        $buscaCep = new TAction(array($this, 'onCep'));
        $cep->setExitAction($buscaCep); 


        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:clone  green');
        
        $this->form->addAction('Credenciar PJBank',  new TAction([$this, 'onCredenciarPJBank']), 'fa:institution  black');
        $this->form->addAction('Extrato',  new TAction([$this, 'onExtratoPJBank']), 'fa:institution  black');
        $this->form->addAction('Criar CD',  new TAction([$this, 'onCriaCD']), 'fa:institution  black');
        $this->form->addAction('Dados CD',  new TAction([$this, 'onDadosCDPJBank']), 'fa:institution  black');
        
        // 2a aba
        $this->form->appendPage('PJBank', $page2);
        
        $conta_repasse = new TEntry('conta_repasse');
        $agencia_repasse = new TEntry('agencia_repasse');
        $banco_repasse = new TEntry('banco_repasse');
        $ddd_pjbank = new TEntry('ddd_pjbank');
        $telefone_pjbank = new TEntry('telefone_pjbank');
        $agencia_parceiro_pjbank = new TEntry('agencia_parceiro_pjbank');
        $email_pjbank = new TEntry('email_pjbank');
        
        $conta_repasse->setSize('40%');
        $agencia_repasse->setSize('40%');
        $banco_repasse->setSize('40%');
        $ddd_pjbank->setSize('40%');
        $telefone_pjbank->setSize('40%');
        $agencia_parceiro_pjbank->setSize('40%');
        $email_pjbank->setSize('40%');
        
        $telefone_pjbank->setMask('99999-9999'); 
        
        
        $this->form->addFields( [ new TLabel('Conta Repasse') ], [ $conta_repasse ] );
        $this->form->addFields( [ new TLabel('Agência Repasse') ], [ $agencia_repasse ] );
        $this->form->addFields( [ new TLabel('Banco Repasse') ], [ $banco_repasse ] );
        $this->form->addFields( [ new TLabel('DDD') ], [ $ddd_pjbank ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone_pjbank ] );
        $this->form->addFields( [ new TLabel('E-mail') ], [ $email_pjbank ] );
        $this->form->addFields( [ new TLabel('Agência Parceiro') ], [ $agencia_parceiro_pjbank ] );
        
        // 3a aba
        $this->form->appendPage('Staus PJBank', $page3);
        
        $status_pjbank = new TEntry('status_pjbank');
        $msg_pjbank = new TEntry('msg_pjbank');
        $credencial_pjbank = new TEntry('credencial_pjbank');
        $chave_pjbank = new TEntry('chave_pjbank');
        $conta_virtual_pjbank = new TEntry('conta_virtual_pjbank');
        $agencia_virtual_pjbank = new TEntry('agencia_virtual_pjbank');
        $agencia_parceiro_pjbank = new TEntry('agencia_parceiro_pjbank');
        
        $status_pjbank->setSize('40%');
        $msg_pjbank->setSize('40%');
        $credencial_pjbank->setSize('100%');
        $chave_pjbank->setSize('100%');
        $conta_virtual_pjbank->setSize('40%');
        $agencia_virtual_pjbank->setSize('40%');
        
        $this->form->addFields( [ new TLabel('Status') ], [ $status_pjbank ] );
        $this->form->addFields( [ new TLabel('Mensagem') ], [ $msg_pjbank ] );
        $this->form->addFields( [ new TLabel('Credencial') ], [ $credencial_pjbank ] );
        $this->form->addFields( [ new TLabel('Chave') ], [ $chave_pjbank ] );
        $this->form->addFields( [ new TLabel('Conta Virtual') ], [ $conta_virtual_pjbank ] );
        $this->form->addFields( [ new TLabel('Agência Virtual') ], [ $agencia_virtual_pjbank ] );
        
        // 4a aba
        $this->form->appendPage('Financeiro', $page4);
        
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $desconto = new TEntry('desconto');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id_desconto = new TDBCombo('classe_id_desconto', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);

        $multa->setSize('40%');
        $juros->setSize('40%');
        $desconto->setSize('40%');
        
        $multa->setNumericMask(2, ',', '.');
        $juros->setNumericMask(2, ',', '.'); 
        $desconto->setNumericMask(2, ',', '.');
        
        $this->form->addFields( [ new TLabel('Multa') ], [ $multa ] );
        $this->form->addFields( [ new TLabel('Juros ao mês') ], [ $juros ] );
        $this->form->addFields( [ new TLabel('Desconto') ], [ $desconto ] );
        $this->form->addFields( [ new TLabel('Classe do Desconto') ], [ $classe_id_desconto ] );

        // 5a aba
        $this->form->appendPage('Conta Digital', $page5);
        
        $status_cd_pjbank = new TEntry('status_cd_pjbank');
        $msg_cd_pjbank = new TEntry('msg_cd_pjbank');
        $credencial_cd_pjbank = new TEntry('credencial_cd_pjbank');
        $chave_cd_pjbank = new TEntry('chave_cd_pjbank');
        
        $status_cd_pjbank->setSize('40%');
        $msg_cd_pjbank->setSize('40%');
        $credencial_cd_pjbank->setSize('100%');
        $chave_cd_pjbank->setSize('100%');
        
        $this->form->addFields( [ new TLabel('Status') ], [ $status_cd_pjbank ] );
        $this->form->addFields( [ new TLabel('Mensagem') ], [ $msg_cd_pjbank ] );
        $this->form->addFields( [ new TLabel('Credencial') ], [ $credencial_cd_pjbank ] );
        $this->form->addFields( [ new TLabel('Chave') ], [ $chave_cd_pjbank ] );
        
        // 6a aba
        $this->form->appendPage('Split', $page6);
        
        $administradora = new TEntry('administradora');
        $split_nome = new TEntry('split_nome');
        $split_cnpj = new TEntry('split_cnpj');
        $split_banco_repasse = new TEntry('split_banco_repasse');
        $split_agencia_repasse = new TEntry('split_agencia_repasse');
        $split_conta_repasse = new TEntry('split_conta_repasse');
        $split_tipo_conta_repasse = new TEntry('split_tipo_conta_repasse');
        
        $administradora->setSize('40%');
        $split_nome->setSize('100%');
        $split_cnpj->setSize('100%');
        $split_banco_repasse->setSize('500%');
        $split_agencia_repasse->setSize('500%');
        $split_conta_repasse->setSize('500%');
        $split_tipo_conta_repasse->setSize('500%');
        
        $this->form->addFields( [ new TLabel('Administradora (Y/N)') ], [ $administradora ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $split_nome ] );
        $this->form->addFields( [ new TLabel('CNPJ ou CPF') ], [ $split_cnpj ] );
        $this->form->addFields( [ new TLabel('Banco Repasse') ], [ $split_banco_repasse ] );
        $this->form->addFields( [ new TLabel('Agência Repasse') ], [ $split_agencia_repasse ] );
        $this->form->addFields( [ new TLabel('Conta Repasse') ], [ $split_conta_repasse ] );
        $this->form->addFields( [ new TLabel('Tipo Conta Repasse (poupanca/corrente)') ], [ $split_tipo_conta_repasse ] );
        
        // fim 6a aba
       
        // 7a aba
        $this->form->appendPage('Cobrança', $page6);
        
        $cobranca_email = new TEntry('cobranca_email');
        $prazo_cobranca = new TEntry('prazo_cobranca');
        
        $cobranca_email->setSize('50%');
        $prazo_cobranca->setSize('50%');
        
        $this->form->addFields( [ new TLabel('Cobrança por e-mail (Y/N)') ], [ $cobranca_email ] );
        $this->form->addFields( [ new TLabel('Prazo em dias para cobrança') ], [ $prazo_cobranca ] );
        
        ///$subnotebook = new TNotebook(250, 160);
        //$subnotebook->appendPage('new page1', new TLabel('test1'));
        //$subnotebook->appendPage('new page2', new TText('test2'));
        
        //$row = $page3->addRow();
        //$row->addCell($subnotebook);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

/* 
    *   
    *   
    *   
    */
    public static function onCriaCD($param)
        {
            
            if ($param['credencial_cd_pjbank'] != '') {
                new TMessage('Conta Digital', 'Condomínio já possue uma conta digital !');
                return;
            }
            
            $sandbox    = 'https://sandbox.pjbank.com.br';
            $production = 'https://api.pjbank.com.br';
        
            try {
                //var_dump($param);
                //return;
                
                $vowels = array(".", "-");
                $onlyconsonants = str_replace($vowels, "", $param['cep']);

                $parameters = array();
                $parameters['nome_empresa'] = $param['nome'];
                $parameters['cnpj'] = $param['cnpj'];   
                $parameters['cep'] = $onlyconsonants;
                $parameters['endereco'] = $param['endereco'];
                $parameters['numero'] = $param['numero'];
                $parameters['bairro'] = $param['bairro'];
                $parameters['complemento'] = $param['complemento'];
                $parameters['cidade'] = $param['cidade'];
                $parameters['estado'] = $param['estado'];
                $parameters['ddd'] = $param['ddd_pjbank'];

                $vowels = array("(", ")", "-");
                $onlyconsonants = str_replace($vowels, "", $param['telefone1']);

                $parameters['telefone'] = $onlyconsonants;
                $parameters['email'] = $param['email_pjbank'];
                //$parameters['webhook'] = $param['webhook'];
                //$parameters['agencia'] = $param['agencia_parceiro_pjbank'];                
                
                //var_dump($parameters);
                
                $json = json_encode($parameters);
                
                $curl = curl_init();

                curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_POSTFIELDS => $json,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
                  ),
                  ));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $jsonRet=json_decode($response, true);
                    
                    var_dump($jsonRet);
                            
                    if ($jsonRet['status'] < '300') {
                        TTransaction::open('facilitasmart'); // open a transaction
                        $object = new Condominio($param['id']); // instantiates the Active Record
                        
                        //var_dump($param);
                        
                        $object->status_cd_pjbank = $jsonRet['status'];
                        $object->msg_cd_pjbank = $jsonRet['msg'];
                        $object->credencial_cd_pjbank = $jsonRet['credencial'];
                        $object->chave_cd_pjbank = $jsonRet['chave'];
                    
                        $object->store(); // update the object in the database
                        new TMessage('Info', 'Conta Digital cadastrada com sucesso!'); // success message 
                    
                        ////$this->form->setData($data); // fill form data
                            
                        TTransaction::close(); // close the transaction
                       
                    } else {
                        new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                    }
                  
                } 
                    
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }
            
    /* 
    *   
    *   
    *   
    */
    public static function onExtratoPJBank($param)
        {
            //$data = $this->form->getData(); 
            
            if ($param['credencial_pjbank'] == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado!');
                return;
            }
                    
            try {
                $curl = curl_init();

                curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$param['credencial_pjbank']."/transacoes?data_inicio=01/01/2019&data_fim=01/24/2019&pago=1&pagina=1",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                            "X-CHAVE: " . $param['chave_pjbank']
                          ),));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $pjbank=json_decode($response, true);
                            var_dump($pjbank[0]);
                            //var_dump(count($pjbank));
                            //return; 
                  
                } 
                
                //se tiver 50 itens, pegar a 2a pagina
                
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }

/* 
    *   
    *   
    *   
    */
    public static function onDadosCDPJBank($param)
        {
            //$data = $this->form->getData(); 
            
            if ($param['credencial_cd_pjbank'] == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado!');
                return;
            }
                    
            try {
                $curl = curl_init();
                
                $parameters = array();
                $parameters['com_saldo'] = true;                
                $json = json_encode($parameters); //CURLOPT_POSTFIELDS => $json,
                
                curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$param['credencial_cd_pjbank'],
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                            "X-CHAVE-CONTA: " . $param['chave_cd_pjbank']
                          ),));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $pjbank=json_decode($response, true);
                    //var_dump($pjbank);
                    new TMessage('info', 
                                 "Status        : ". $pjbank['status']." </br >".
                                 "Webhook       : ". $pjbank['webhook']." </br >".
                                 "Webhook Chave : ". $pjbank['webhook_chave']." </br >" );
                  
                } 
                
                //se tiver 50 itens, pegar a 2a pagina
                
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }


    /* 
    *   
    *   
    *   
    */
    public static function onCredenciarPJBank($param)
        {
            //$data = $this->form->getData(); 
        
            if ($param['credencial_pjbank'] != '') {
                new TMessage('Credenciamento', 'Condomínio já credenciado!');
                return;
            }
            
            $sandbox    = 'https://sandbox.pjbank.com.br';
            $production = 'https://api.pjbank.com.br';
        
            try {
                $parameters = array();
                $parameters['nome_empresa'] = $param['nome'];
                $parameters['conta_repasse'] = $param['conta_repasse'];   
                $parameters['agencia_repasse'] = $param['agencia_repasse'];
                $parameters['banco_repasse'] = $param['banco_repasse'];
                $parameters['cnpj'] = $param['cnpj'];
                $parameters['ddd'] = $param['ddd_pjbank'];
                $parameters['telefone'] = $param['telefone_pjbank'];
                $parameters['email'] = $param['email_pjbank'];
                $parameters['agencia'] = $param['agencia_parceiro_pjbank'];
                
                //$url = $sandbox . "/recebimentos/" . http_build_query($parameters);
                
                
                $json = json_encode($parameters);

                //A chamada da função CURL deve ficar assim:
                //$ch = curl_init($sandbox . "/recebimentos/");
                $ch = curl_init($production . "/recebimentos/");

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json)));
                
                //O tratamento do retorno da chamada deve acontecer na variável que está recebendo o resultado do curl_exec, caso o retorno seja um JSON também, você deve usar a função json_decode.
                $jsonRet = json_decode(curl_exec($ch));
                
                new TMessage('PJBank', $jsonRet->msg);
                
                //var_dump($jsonRet);
                //return;
                
                if ($jsonRet->status == '201') {
                    TTransaction::open('facilitasmart'); // open a transaction
                    $object = new Condominio($param['id']); // instantiates the Active Record
                        
                    $object->status_pjbank = $jsonRet->status;
                    $object->msg_pjbank = $jsonRet->msg;
                    $object->credencial_pjbank = $jsonRet->credencial;
                    $object->chave_pjbank = $jsonRet->chave;
                    $object->conta_virtual_pjbank = $jsonRet->conta_virtual;
                    $object->agencia_virtual_pjbank = $jsonRet->agencia_virtual;
                         
                    $object->store(); // update the object in the database
                    new TMessage('Credenciamento', 'Concluído com sucesso!'); // success message 
                    
                    ////$this->form->setData($data); // fill form data
                            
                    TTransaction::close(); // close the transaction
                       
                } 
                  
                
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }
        
        
        
    /* 
    *  Função de busca de Endereço pelo CEP 
    *  -   Desenvolvido Felipe Olivaes para ajaxbox.com.br 
    *  -   Utilizando WebService de CEP da republicavirtual.com.br 
    */
    public static function onCep($param)
        {
            
            $resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.urlencode($param['cep']).'&formato=query_string');  
            if(!$resultado){  
                $resultado = "&resultado=0&resultado_txt=erro+ao+buscar+cep";  
            }  

            parse_str($resultado, $retorno);   
            
            $obj = new StdClass;
            //$obj->cep      = $param['cep'];
            $obj->endereco = strtoupper( $retorno['tipo_logradouro'].' '.$retorno['logradouro']);
            $obj->bairro  = strtoupper( $retorno['bairro']);
            $obj->cidade   = strtoupper( $retorno['cidade']);
            $obj->estado       = strtoupper( $retorno['uf']); 
            
            // envia dados ao form
            TForm::sendData('form_Condominio', $obj);
        }
        

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            $string = new StringsUtil;
            
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Condominio;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            // retira pontos, traço e barra do cnpj antes de gravar
            $cep  = str_replace('.', '', $object->cep);
            $cep  = str_replace('-', '', $cep);
            $object->cep = $cep;
            
            // retira pontos, traço e barra do cnpj antes de gravar
            $cnpj  = str_replace('.', '', $object->cnpj);
            $cnpj  = str_replace('-', '', $cnpj);
            $cnpj  = str_replace('/', '', $cnpj);
            $object->cnpj = $cnpj;
            
            // retira traço e parentese do telefone1 antes de gravar
            $telefone  = str_replace('(', '', $object->telefone1);
            $telefone  = str_replace(')', '', $telefone);
            $telefone  = str_replace('-', '', $telefone);
            $object->telefone1 = $telefone;
            
            // retira traço e parentese do telefone1 antes de gravar
            $telefone  = str_replace('(', '', $object->telefone2);
            $telefone  = str_replace(')', '', $telefone);
            $telefone  = str_replace('-', '', $telefone);
            $object->telefone2 = $telefone;
            
            // retira traço e parentese do telefone1 antes de gravar
            $telefone  = str_replace('(', '', $object->telefone_pjbank);
            $telefone  = str_replace(')', '', $telefone);
            $telefone  = str_replace('-', '', $telefone);
            $object->telefone_pjbank = $telefone; 
            
            $object->multa ? $object->multa = $string->desconverteReais($object->multa) : null;
            $object->juros ? $object->juros = $string->desconverteReais($object->juros) : null;
            $object->desconto ? $object->desconto = $string->desconverteReais($object->desconto) : null;
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new Condominio($key); // instantiates the Active Record
                
                $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                $object->desconto ? $object->desconto = number_format($object->desconto, 2, ',', '.') : null;
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
