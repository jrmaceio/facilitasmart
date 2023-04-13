/**
    * Não faz a chamada por lote, faz individual cada boleto
    *
    public function onRegLote($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 40;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListagem_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListagem_filter_situacao')) {
                $criteria->add(TSession::getValue('ContasReceberListagem_filter_situacao')); // add the session filter
            }

            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            $criteria->add(new TFilter('boleto_status', '=', '1')); // add the session filter
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            
            // iterate the collection of active records
            foreach ($objects as $object) {
                // faz o registro somente dos nao emitidos
                if ($object->boleto_status!= '2') {
                    $key = $object->id;
                    $titulo = new ContasReceber($key, FALSE); // instantiates the Active Record
                    
                    $condominio = new Condominio($titulo->condominio_id);
                    $unidade = new Unidade($titulo->unidade_id);
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    $classe = new PlanoContas($titulo->classe_id);
                        
                    // verifica se o condomínio está credenciado no pjbank
                    if ($condominio->credencial_pjbank == '') {
                        new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                        TTransaction::close(); // close the transaction
                        return;
                    }
            
                    $data_vencimento = new DateTime($titulo->dt_vencimento);
                    
                    $dias = 0;
                           
                    if ($object->desconto_boleto_cobranca > 0) {
                        $time_inicial = strtotime($object->dt_limite_desconto_boleto_cobranca);
                        $time_final = strtotime($object->dt_vencimento);
                        // Calcula a diferença de segundos entre as duas datas:
                        //$diferenca = $time_final - $time_inicial; // 19522800 segundos
                        $diferenca = $time_inicial - $time_final;
                        // Calcula a diferença de dias
                        $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                        $dias = $dias;
                
                    };
                     
                    if ( $pessoa->pessoa_fisica_juridica == 'J' ) {
                        $cpf_cnpj = $pessoa->cnpj;
                    } else {
                        $cpf_cnpj = $pessoa->cpf;
                    }
            
                    $nome = substr($pessoa->nome, 0, 80);
                        
                    $texto = 'Referência : '. $object->descricao . 
                            ' 
            
                            Unidade ' . 'Bl/Qd-Unid.' . $unidade->bloco_quadra . '- ' . $unidade->descricao .
                            '
            
                            2a via de boleto, prestação de conta, etc., acesse :
                
                            www.facilitahomeservice.com.br/facilitasmart
            
                            Utilize seu cpf no campo usuário e seu Id como senha.
            
                            Seu Id ' . $unidade->id .
                            '
            
                            .
            
                            '; 
                
                    // Faz o registro do boleto
                    $data = json_encode(array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$object->valor,
                        'juros'=>$object->juros_boleto_cobranca,
                        'multa'=>$object->multa_boleto_cobranca,
                        'desconto'=>$object->desconto_boleto_cobranca,
                        'diasdesconto1'=>$dias,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'Bl/Qd-Unid.' . $unidade->bloco_quadra . '- ' . $unidade->descricao,
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'http://vps12978.publiccloud.com.br/facilitasmart/app/images/logo.png',
                        'texto'=>$texto,
                        'grupo'=>$object->mes_ref, // link para impressão em lote
                        'pedido_numero'=>$object->id));
                        ////'https://facilitagestor.000webhostapp.com/logo.png',
                        
                    $curl = curl_init();
            
                    curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => false,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "POST",
                          CURLOPT_POSTFIELDS => $data,
                          CURLOPT_HTTPHEADER => array("Content-Type: application/json"),));
                        
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                
                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        $pjbank=json_decode($response);
                             
                        // considerando que um valor maior ou igual a 300 sempre representa um erro
                        if ($pjbank->status < '300') {    
                            $objectRec = new ContasReceber($object->id);               
                            $objectRec->boleto_status = '2';
                            $objectRec->nosso_numero = $pjbank->nossonumero;
                            $objectRec->pjbank_pedido_numero = $object->id;
                            $objectRec->pjbank_id_unico = $pjbank->nossonumero;
                            $objectRec->pjbank_banco_numero = $pjbank->banco_numero;
                            $objectRec->pjbank_token_facilitador = $pjbank->token_facilitador;
                            $objectRec->pjbank_credencial = $pjbank->credencial;
                            $objectRec->pjbank_linkBoleto = $pjbank->linkBoleto;
                            $objectRec->pjbank_linkGrupo = $pjbank->linkGrupo;
                            $objectRec->pjbank_linhaDigitavel = $pjbank->linhaDigitavel;
                            $objectRec->store(); // update the object in the database
                                 //$link1 = $pjbank->linkBoleto;
                                 //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                    
                        } else {
                            new TMessage('Erro', $pjbank->msg . "(cURL Error #:" . $err.")");
                        } 
                                           
                    }//trataemento de erro
                         
                }// boleto status
                    
            }//foreach
     
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        } //try
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }// funcao
    */

