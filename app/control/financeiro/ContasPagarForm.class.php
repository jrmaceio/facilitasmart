<?php
/**
 * ContasPagarForm Registration
 * @author  <your name here>
 */
class ContasPagarForm extends TPage
{
    protected $form; // form
    private $attach_area;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
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
        
        
        $this->setDatabase('facilitasmart');              // defines the database
        $this->setActiveRecord('ContasPagar');     // defines the active record
        
        // creates the form
        //$this->form = new TQuickForm('form_ContasPagar');
        //$this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormBuilder('form_ContasPagar');
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('ContasPagar');
        
        $this->form->appendPage('Lançamento');

        $criteria = new TCriteria;
        //$criteria->add(new TFilter('id', '=', $user->condominio_id));
        //$criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio')));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
/*                
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        TTransaction::open('facilitasmart');
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        foreach ($users as $user)
        {
            if ($user->nivel_acesso_inf == '2') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                //$criteria->add(new TFilter('id', '=', $user->condominio_id));
                $criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
            }else {
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
            } 
            
        }
        TTransaction::close();
*/
        // create the form fields
        $id = new TEntry('id');
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        
        $mes_ref = new TEntry('mes_ref');
        //$tipo_lancamento = new TEntry('tipo_lancamento');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'D'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{codigo}-{descricao}', 'descricao',$criteria);
        
        $documento = new TEntry('documento');
        $dt_lancamento = new TDate('dt_lancamento');
        $dt_vencimento = new TDate('dt_vencimento');
        $valor = new TEntry('valor');
        $descricao = new TEntry('descricao');
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');
        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);  
        $file = new TMultiFile('file');
        $tipo_lancamento = new TDBCombo('tipo_lancamento', 'facilitasmart', 'TipoPagamento', 'id', '{id}-{descricao}','descricao');
        
        $dt_liquidacao = new TDate('dt_liquidacao');
        $valor_pago = new TEntry('valor_pago');
        
        $dt_liquidacao->setSize('50%');
        $valor_pago->setSize('50%');
        
        $dt_liquidacao->setMask('dd/mm/yyyy');
        
        $dt_liquidacao->setDatabaseMask('yyyy-mm-dd');
        
        $valor_pago->setNumericMask(2, ',', '.');


        $file->enableFileHandling();
        
        $file->setSize('100%');
         
        // add the fields
        //$this->form->addFields( [new TLabel('ID')], [$id], [new TLabel(_t('User'))], [$user] );
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id] );
        $this->form->addFields( [new TLabel('Mês Referência')], [$mes_ref], [new TLabel('Número Doc Pagamento')], [$numero_doc_pagamento] );
        $this->form->addFields( [new TLabel('Classe')], [$classe_id] );
        $this->form->addFields( [new TLabel('Documento')], [$documento] );
        $this->form->addFields( [new TLabel('Dt Vencimento')], [$dt_vencimento] );
        $this->form->addFields( [new TLabel('Valor')], [$valor] );
        $this->form->addFields( [new TLabel('Descrição')], [$descricao] );
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]);

        $this->form->addFields( [new TLabel('Tipo de Pagamento')], [$tipo_lancamento]);

        $this->form->addFields( [new TLabel('Data Liquidação')], [$dt_liquidacao]);
        $this->form->addFields( [new TLabel('Valor Pago')], [$valor_pago]);
       
        //$this->form->addFields( [new TLabel('Número Doc Pagamento')], [$numero_doc_pagamento] );
        $this->form->addFields( [new TLabel('Nota/Recibo/Comprovante Pag.')], [$file]);

        $valor->setSize('50%');
        $valor_pago->setSize('50%');
        
        $valor->setNumericMask(2, ',', '.');
        $dt_vencimento->setMask('dd/mm/yyyy');
        //$dt_pagamento->setMask('dd/mm/yyyy');
   
        // validations
        $classe_id->addValidation('planocontas_id', new TRequiredValidator);
        $dt_vencimento->addValidation('dt_vencimento', new TRequiredValidator);

        //atribue o imovel selecionado
        //$condominio_id->setValue( TSession::getValue('id_condominio') );
        //$condominio_id->setEditable(FALSE); 
        
        $mes_ref->setValue( TSession::getValue('mesref') );
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
        
        $this->form->appendPage('PJBank');

        $linha_digitavel = new TEntry('linha_digitavel');
        $nome_favorecido = new TEntry('nome_favorecido');
        $cnpj_cpf_favorecido = new TEntry('cnpj_cpf_favorecido');
        $solicitante_pagamento = new TEntry('solicitante_pagamento');

        $status_conta_digital = new TEntry('status_conta_digital');
        $id_operacao_conta_digital = new TEntry('id_operacao_conta_digital');

        $status_conta_digital->setEditable(FALSE);
        $id_operacao_conta_digital->setEditable(FALSE);

        $this->form->addFields( [new TLabel('Código de Barra')], [$linha_digitavel] );
        $this->form->addFields( [new TLabel('Nome Favorecido')], [$nome_favorecido] );
        $this->form->addFields( [new TLabel('CNPJ ou CPF do Favorecido')], [$cnpj_cpf_favorecido] );
        $this->form->addFields( [new TLabel('Solicitante do Pagamento')], [$solicitante_pagamento] );

        $this->form->addFields( [new TLabel('Status Pagamento')], [$status_conta_digital] );
        $this->form->addFields( [new TLabel('ID Operação Pagamento')], [$id_operacao_conta_digital] );

        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'bs:plus-sign green');
        //$this->form->addQuickAction(_t('List'),  new TAction(array('ContasPagarList','onReload')), 'fa:table blue');
        
        $this->form->addAction(_t('New'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addAction('Envia CD PJBank', new TAction(array($this, 'onEnviaCDPJBank')), 'fa:plus-circle green');
        $this->form->addAction(_t('Back to the listing'), new TAction(array('ContasPagarList','onReload')), 'fa:table blue');
        
        
        $this->attach_area = new TVBox;
        $this->form->addContent( [$this->attach_area] );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        // add the vbox inside the page
        parent::add($container);
    }

    public function onEnviaCDPJBank( $param )
    {
        try
        {
            $key=$param['id']; // get the parameter $key

            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new ContasPagar($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado cancela
            if ( $object->situacao == 1 ) {
              new TMessage('info', 'Despesa já liquidada, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              TApplication::loadPage('ContasPagarList', 'onReload', $param);
              return;
                
            }

             // se titulo estiver com o status de enviada, cancela
            //if ( isset($object->id_operacao_conta_digital) ) {
            //    new TMessage('info', 'Despesa já enviada, operação não permitida !'); // success message
            //    TTransaction::close(); // close the transaction
            //    TApplication::loadPage('ContasPagarList', 'onReload', $param);
            //    return;
                  
            //}

            $condominio = new Condominio($object->condominio_id);
            
            // verifica se o condomínio está credenciado no pjbank
            if ($condominio->credencial_cd_pjbank == '') {
                new TMessage('Credenciamento', 'Condomínio não possui conta digital credenciada no PJBank!');
                TTransaction::close(); // close the transaction
                TApplication::loadPage('ContasPagarList', 'onReload', $param);
                return;
            }

            // verifica se o condomínio está credenciado no pjbank
            if ($condominio->credencial_cd_pjbank == null) {
                new TMessage('Credenciamento', 'Condomínio não possui conta digital credenciada no PJBank!');
                TTransaction::close(); // close the transaction
                TApplication::loadPage('ContasPagarList', 'onReload', $param);
                return;
            }

            $data_vencimento = new DateTime($object->dt_vencimento);

            // inicio envio a api
            $data = json_encode(array(
                'data_vencimento'=>date_format($data_vencimento,'m/d/Y'),
                'data_pagamento'=>date_format($data_vencimento,'m/d/Y'),
                'valor'=>$object->valor,
                'codigo_barras'=>$object->linha_digitavel,
                'descricao'=>$object->descricao,
                'nome_favorecido'=>$object->nome_favorecido,
                'cnpj_favorecido'=>$object->cnpj_cpf_favorecido,
                'solicitante'=>$object->solicitante_pagamento
                ));
            
            //var_dump($data);
                ////'https://facilitagestor.000webhostapp.com/logo.png',
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$condominio->credencial_cd_pjbank."/transacoes",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n    \"lote\": [\n       " . $data . " \n    ]\n}",
      CURLOPT_HTTPHEADER => array(
        "X-CHAVE-CONTA: " . $condominio->chave_cd_pjbank,
        "Content-Type: application/json"
            ),));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                
            if ($err) {
                echo "cURL Error #:" . $err;
                //new TMessage('Erro', "cURL Error #:" . $err . ' - Não foi possível registrar, repita a operação!');
            } else {
                $pjbank=json_decode($response);

                //var_dump($pjbank);
                if (isset($pjbank->status) and $pjbank->status) {
                
                    if ($pjbank->status < '300') {
                        $object->status_conta_digital = $pjbank->status . ' ' . $pjbank->msg;
                        $object->id_operacao_conta_digital = $pjbank->id_operacao;
                        //$object->dt_pagamento = $pjbank->data_pagamento;
                        $object->store();
                        new TMessage('Pagamento', 'Despesa lançada na conta digital!'); // success message
                    }else{
                        new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                    } 
                } else {
                    //var_dump($pjbank);
                    new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                }
            }
            // fim envio a api

            TTransaction::close(); // close the transaction
            
            new TMessage('info', 'Despesa registrada na Conta Digital para pagamento.');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

        /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        $string = new StringsUtil;
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new ContasPagar;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            //// verifica fechamento
            $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                       where('mes_ref', '=', $object->mes_ref)->
                                       where('conta_fechamento_id', '=', $object->conta_fechamento_id)->load();
                        
            //default = 1 fechado, não permite nada
            $status = 1;
        
            foreach ($fechamentos as $fechamento)
            {
                $status = $fechamento->status;
            }
                        
                        
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                TTransaction::close(); // close the transaction
                return;
            }
            ////////////////////////////////////
                          
            if ( $object->situacao == '1') {
                new TMessage('info', 'Título já baixado, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            // varificacao se existe um fechamento disposivel para a data de liquidação 
            if ($object->valor_pago>0)
            {
                $dtliq = explode("-", $object->dt_liquidacao);
                $mesref = $dtliq[1].'/'.$dtliq[0];// feito assim porque está com mask de database

                $status = ContasReceber::retornaStatusFechamento($object->condominio_id, 
                    $mesref, $object->conta_fechamento_id);
            
                ////status 1 -> fechado, nao altera nada
                if ( $status == 1 ) {
                    new TMessage('info', 'Não existe um Fechamento aberto para a data de liquidação !');
                    TTransaction::close(); 
                    $this->form->setData($object); // mantem os dados digitados;
                    return;    
                }
                
            }
            
            //var_dump($dt_venc[1]);
            //var_dump($mes_referencia[0]);
            if ( $dt_venc[1] != $mes_referencia[0]) {
                new TMessage('info', 'Divergência entre o mês da data de vencimento e o Mês Ref !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            } 
            
            if ( $dt_venc[2] != $mes_referencia[1]) {
                new TMessage('info', 'Divergência entre o ano da data de vencimento e o Mês Ref !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            // alteração    
            if (!empty($object->id))
            {
                $dt_venc = explode("/", $object->dt_vencimento);
                $mes_referencia = $dt_venc[1].'/'.$dt_venc[2];
              
                $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $mes_referencia, $object->conta_fechamento_id);
            
                ////status 1 -> fechado, nao altera nada
                if ( $status == 1 ) {
                    new TMessage('info', 'Não existe um Fechamento aberto para a data de vencimento !');
                    TTransaction::close(); 
                    $this->form->setData($object); // mantem os dados digitados;
                    return;    
                }
                
            }
            
            //formato necessário no mysql
            $object->dt_vencimento = TDate::date2us($object->dt_vencimento );
            $object->dt_pagamento = TDate::date2us($object->dt_pagamento );
            $object->valor ? $object->valor = $string->desconverteReais($object->valor) : null;
            $object->valor_pago ? $object->valor_pago = $string->desconverteReais($object->valor_pago) : null;

            $object->dt_lancamento = date("Y-m-d");
            
            if ($object->valor_pago > 0) {
                $object->situacao = 1;

                // trata erro de não vir o tipo_pagamento
                if (empty($object->$tipo_pagamento_id))
                {
                    $object->tipo_pagamento_id = 4;
                }

            }

            // pega a chave do TDBMultiSearch 
            //troquei opor combo ===== >$object->planocontas_id = key($data->planocontas_id);
            ///var_dump($object->planocontas_id);
            
            $object->usuario = TSession::getValue('username'); 
            
            $object->store(); // save the object
            
            // copy file to target folder
            $this->saveFilesByComma($object, $data, 'file',     'files/ctspagar');
               //  saveFilesByComma($object, $data, $input_name, $target_path)
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            
            // guarda valores de campos para o proximo novo registro
            TSession::setValue('mesref', $object->mes_ref);
            TSession::setValue('documento', $object->documento);
            TSession::setValue('num_doc_pagamento', $object->numero_doc_pagamento);
            TSession::setValue('condominio_id', $object->condominio_id);
            
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
                $object = new ContasPagar($key); // instantiates the Active Record
                //var_dump($object->file);
                //var_dump("files/ctspagar/{$key}/$object->file");
                
                //if (file_exists("files/ctspagar/{$key}/$object->file"))
                if (file_exists($object->file))
                {
                    //var_dump("files/ctspagar/{$key}/$object->file");
                    $this->attach_area->add( TElement::tag('h4', 'Anexo(s)') );
                    //$this->attach_area->add( new THyperLink($object->file, "download.php?file=$object->file"));
                    $this->attach_area->add( new THyperLink($object->file, "download.php?file=files/ctspagar/{$key}/$object->file"));
                }
                
                // necessário no mysql
                $object->dt_vencimento = TDate::date2br($object->dt_vencimento);
                //nao precisa aqui porque esta com mascara de database ja configurada $object->dt_pagamento = TDate::date2br($object->dt_pagamento);
                
                $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $object = new ContasPagar();
                // recupera dados armazenados para o proximo registro
                $object->mes_ref = TSession::getValue('mesref');
                $object->documento = TSession::getValue('documento');
                $object->condominio_id = TSession::getValue('condominio_id');
                $object->numero_doc_pagamento = TSession::getValue('num_doc_pagamento');
                $object->dt_vencimento = date('01/' . TSession::getValue('mesref'));
                $this->form->setData($object);
                
                //$this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}