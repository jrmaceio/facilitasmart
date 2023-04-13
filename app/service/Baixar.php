 <?php
 class Baixar
    {
        /**
         * 
         * 
         */
        public static function baixarPKB( $param )
        {
            TTransaction::open('facilitasmart');
            
            $cond = Condominio::all();
            
            foreach ($cond as $value) {
                $condominio = $value; 
                        
                if ($condominio->credencial_pjbank != '' and $condominio->credencial_pjbank != '0') {
                              
                    try 
                    {
                        //var_dump($value->resumo);
                        $data_inicial = date('m/d/Y');
                        $data_final = date('m/d/Y');
                        
                        for ($i = 1; $i <= 20; $i++) {
                            $num_pagina = $i;   
                    
                            $curl = curl_init();
                                curl_setopt_array($curl, array(
                                CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank.
                                 "/transacoes?data_inicio=".$data_inicial.
                                 "&data_fim=".$data_final."&pago=1&pagina=".$num_pagina,
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
                        
                                //se tiver 50 itens, pegar a 2a pagina
                    
                                foreach ($pjbank as $pagamento)
              	                {
                                    $object = new stdClass();
                                    $titulo = new ContasReceber($pagamento["pedido_numero"]);
                                    $object->status = 'NL'; // não localizado
                        
                                    if (isset($titulo)) {
                                        if ($titulo->situacao == '1') {
                                            $object->status = 'Já Baixado';
                                
                                        }else {
                                            $data = $pagamento["data_credito"];
              	                            $partes = explode("/", $data);
             	                            $ano = $partes[2];
              	                            $mes = $partes[0];
              	                            $mes_ref = $mes.'/'.$ano;
               
              	                            // verifica fechamento
              	                            $fechamentos = Fechamento::where('condominio_id', '=', $condominio->id)->
                                               where('mes_ref', '=', $mes_ref)->load();
                        
              	                            //default = 1 fechado, não permite nada
                  	                        $statusFech = 1;
        
              	                            foreach ($fechamentos as $fechamento)
              	                            {
                	                            $statusFech = $fechamento->status;
                	                        }
                        
              	                            if ($statusFech == 0) {
                	                            // faz a baixa
                	                            $object->status = 'Baixado';
                	                
                	                            $dtpagamento = date_format(new DateTime($pagamento['data_pagamento']),'Y-m-d');
                                                $dtcredito = date_format(new DateTime($pagamento['data_credito']),'Y-m-d');
                                                $juros = 0;
                                                $desconto = 0;
                                                  
                                                if ($pagamento['valor_pago'] > $titulo->valor) {
                                                    $juros = $pagamento['valor_pago'] - $titulo->valor;
                                                }
                            
                                                if ($pagamento['valor_pago'] < $titulo->valor) {
                                                    $desconto = $titulo->valor - $pagamento['valor_pago'];
                                                }
                                              
                                                   
                                                $titulo->situacao = '1';
                                                $titulo->dt_pagamento = $dtpagamento;
                                                $titulo->dt_liquidacao = $dtcredito; 
                                                $titulo->valor_pago = $pagamento['valor_pago'];
                                                $titulo->desconto = $desconto;
                                                $titulo->juros = $juros;
                                                $titulo->valor_creditado = $pagamento['valor_liquido'];
                                                $titulo->store(); // update the object in the database
                                
                	                    
              	                            } else {
              	                                $object->status = 'Problema';
              	                            }
                                                                
                                        }
                            
                                    }
                        
                                    $object->condominio = $condominio->resumo;
                                    $object->valor_pago = $pagamento["valor_pago"];
                                    $object->valor_creditado = $pagamento["valor_liquido"];
                                    $object->nosso_numero = $pagamento["nosso_numero"];
                                    $object->pedido_numero = $pagamento["pedido_numero"];
                                    $object->dt_vencimento = $pagamento["data_vencimento"];
                                    $object->dt_pagamento = $pagamento["data_pagamento"];
                                    $object->dt_credito = $pagamento["data_credito"];
                                    $object->pagador = $pagamento["pagador"];
                                    $object->mes_ref = $titulo->mes_ref;
                                
                                    //var_dump($object);
                                    new TMessage('Fim do Processo');
                                }
                  
                            }
                
                            //var_dump(count($pjbank));
                            if (count($pjbank) < 50) {
                                //parar o for 
                                break;
                            }
                     
                        } // fim for

                    
                    } catch (Exception $e) {
                        new TMessage('error', $e->getMessage()); // shows the exception error message
                        TTransaction::rollback(); // undo all pending operations
                    }
                 }
              } 
          
            TTransaction::close();
        }
    }
    
    ?>

