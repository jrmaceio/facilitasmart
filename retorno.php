<?php
header('Content-Type: application/json; charset=utf-8');

// initialization script
require_once 'init.php';

$request = json_decode(file_get_contents("php://input"));
//$chave   = isset($request['chave']) ? $request['chave']   : '';
          
if ($request)
{
    if ($request->chave) // pode verificar a variavel chave tambem ja tratada acima
    {
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction

            $condominios = Condominio::where('chave_webhook_pjbank', '=', $request->chave)->load();

            foreach ($condominios as $cond)
            {
                $condominio = $cond;
            }

            if (!isset($condominio)) // pode acontecer de ainda não existir uma chave e pode ser um tentativa de invasao
            {
                // vou pegar o condominio para saber se já tenho a chave 
                $titulo = new ContasReceber($request->pedido_numero);
                
                if (!isset($titulo->condominio_id)) {
                    // grava uma ocorrencia 
                    $object = new OcorrenciaUnidade;  // create an empty object
                    $object->system_user_login = 'webhook_pjbank';
                    $object->condominio_id = 15;// registra um erro/problema na Facilita
                    $object->status = '1'; // concluído
                    $object->descricao = 'chave: ' . $request->chave . 
                                         ' reg. sistema bancário: ' . $request->registro_sistema_bancario . 
                                         ' id único original: ' . $request->id_unico_original .
                                         ' id único: ' . $request->id_unico . 
                                         ' pedido número: ' . $request->pedido_numero .
                                         ' (CHAVE NAO REGISTRADA EM CONDOMINIO E TITULO INEXISTENTE)';
                    $object->unidade_id = 5186;
                    $object->tipo_id = 1;
                    $object->data_ocorrencia = date('yyyy-m-d');//'2022-05-15';
                    $object->hora_ocorrencia = date('H:i:s');//'12:00:00';
                    $object->store();
                    TTransaction::close(); 
                    
                    //exception("Acesso negado.");
                    throw new Exception(_t('Permission denied'));
                } 

                $condominio = new Condominio($titulo->condominio_id);
                
                if ($request->chave != $condominio->chave_webhook_pjbank) {  // A CHAVE PODE ESTÁ ERRADA
                    // grava uma ocorrencia 
                    $object = new OcorrenciaUnidade;  // create an empty object
                    $object->system_user_login = 'webhook_pjbank';
                    $object->condominio_id = 15;// registra um erro/problema na Facilita
                    $object->status = '1'; // concluído
                    $object->descricao = 'chave: ' . $request->chave . 
                                         ' reg. sistema bancário: ' . $request->registro_sistema_bancario . 
                                         ' id único: ' . $request->id_unico . 
                                         ' pedido número: ' . $request->pedido_numero .
                                         ' (CHAVE NÃO CORRESPONDE AO CONDOMINIO, VERIFICA SE PODE CRIAR EM CONDOMINIO)';
                    $object->unidade_id = 5186;
                    $object->tipo_id = 1;
                    $object->data_ocorrencia = date('yyyy-m-d');//'2022-05-15';
                    $object->hora_ocorrencia = date('H:i:s');//'12:00:00';
                    $object->store();
                    
                    if (!isset($condominio->chave_webhook_pjbank))
                    {
                        // gravando a chave pela primeira vez
                        $condominio->chave_webhook_pjbank = $request->chave;
                        $condominio->store();
                    }

                    TTransaction::close(); 
                    // retirei a excessao para continuar com a rotina throw new Exception(_t('Permission denied'));
                    //exception("Acesso negado."); // gera uma excessão para que o wehook repita a operação agora com a chave já gravada
                }
            }

            // "registro_sistema_bancario": "confirmado"
            // "registro_sistema_bancario": "rejeitado",
            // "registro_sistema_bancario": "baixado"
            // "registro_sistema_bancario": "pendente"
            // "valor_pago": "100", =========> PAGAMENTO
            // "pagamento_duplicado": "1", ============> DUPLICIDADE
            // Testa que alteração o titulo sofreu
            $titulo = new ContasReceber($request->pedido_numero);
            if ( $titulo->condominio_id == $condominio->id) // confirma se o tirulo é mesmo do condominio da chave recebida
            {
                if (isset($request->valor_pago))
                {
                    if (isset($request->pagamento_duplicado)){
                        // grava uma ocorrencia 
                        $object = new OcorrenciaUnidade;  // create an empty object
                        $object->system_user_login = 'webhook_pjbank';
                        $object->condominio_id = $condominio->id;
                        $object->status = '1'; // concluído
                        $object->descricao = 'chave: ' . $request->chave . 
                                         ' reg. sistema bancário: ' . $request->registro_sistema_bancario . 
                                         ' id único: ' . $request->id_unico . 
                                         ' pedido número: ' . $request->pedido_numero .
                                         ' (DUPLICIDADE DE PAGAMENTO) ' .
                                         ' data do crédito: ' . $request->data_credito;
                        $object->unidade_id = $titulo->unidade_id;
                        $object->tipo_id = 1;
                        $object->data_ocorrencia = date('yyyy-m-d');//'2022-05-15';
                        $object->hora_ocorrencia = date('H:i:s');//'12:00:00';
                        $object->store();
                    }

                    $titulo->status = 0; // quando baixa automatico deixa o statos zerado 
                    
                    $dtpagamento = date_format(new DateTime($request->data_pagamento),'Y-m-d');
                    $dtcredito = date_format(new DateTime($request->data_credito),'Y-m-d');
                    $juros = 0;
                    $desconto = 0;
                                                  
                    if ($request->valor_pago > $titulo->valor) {
                        $juros = $request->valor_pago - $titulo->valor;
                    }
                            
                    if ($request->valor_pago < $titulo->valor) {
                        $desconto = $titulo->valor - $request->valor_pago;
                    }
                                              
                    $titulo->arquivo_retorno = 'WEBHOO'; // AUTOMATICO POR ACAO MANUAL DA BAIXA GERAL       
                    $titulo->situacao = '1';
                    $titulo->dt_pagamento = $dtpagamento;
                    $titulo->dt_liquidacao = $dtcredito; 
                    $titulo->valor_pago = $request->valor_pago;
                    $titulo->desconto = $desconto;
                    $titulo->juros = $juros;
                    $titulo->valor_creditado = $request->valor_liquido;
                    $titulo->tarifa = $request->valor_tarifa;
                    $titulo->store(); // update the object in the database
                    TTransaction::close(); 
                }else // é uma atualização do estatus
                {
                    if ($request->registro_sistema_bancario == "confirmado") {
                        $titulo->status = 5; // registrado    
                        $titulo->store();
                    }

                    if ($request->registro_sistema_bancario == "rejeitado") {
                        $titulo->status = 7;    
                        $titulo->store();
                    }

                    if ($request->registro_sistema_bancario == "baixado") {
                        $titulo->status = 6;   
                        $titulo->store();
                    }

                    if ($request->registro_sistema_bancario == "pendente") {
                        $titulo->status = 4;   
                        $titulo->store();
                    }

                    TTransaction::close(); 

                }

            }else
            {
                TTransaction::rollback(); // undo all pending operations
                //throw new Exception(_t('Permission denied'));
                //exception("Acesso negado.");

            }

            TTransaction::close(); // close the transaction
            
        }
        catch (Exception $e) // in case of exception
        {
            TTransaction::rollback(); // undo all pending operations
            //retirei a exessao por erro no log throw new Exception(_t('Permission denied'));
            //exception("Acesso negado.");
        }
    }
}

/*
try
        {
            TTransaction::open('facilitasmart'); // open a transaction

            $object = new OcorrenciaUnidade;  // create an empty object
            
            $object->system_user_login = 'webhook_pjbank';
            $object->condominio_id = 6;// teste do planalto
    
            $object->status = '1'; // concluído
            $object->descricao = 'chave: ' . $request->chave . 
                                 ' reg. sistema bancário: ' . $request->registro_sistema_bancario . 
                                 ' id único original: ' . $request->id_unico_original .
                                 ' id único: ' . $request->id_unico . 
                                 ' pedido número: ' . $request->pedido_numero;
            $object->unidade_id = 291;
            $object->tipo_id = 1;
            $object->data_ocorrencia = '2022-05-15';
            $object->hora_ocorrencia = '12:00:15';

            $object->store(); // save the object
            TTransaction::close(); // close the transaction
            
        }
        catch (Exception $e) // in case of exception
        {
        
            TTransaction::rollback(); // undo all pending operations
        }
*/

