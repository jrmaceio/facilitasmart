<?php

class BoletoPJBank
{
    public static function EmitirBoletoRegistradoLote($cobrancas, $credencial_pjbank)
    {    
    
        $curl = curl_init();

        curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$credencial_pjbank."/transacoes",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $cobrancas,
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
              ),));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return false;
        } else {
            //echo $response;
            $pjbank=json_decode($response);
            return ($pjbank);
        }            
            
    
    }
    
    
    public static function EmitirBoletoRegistrado($titulo,$condominio,$unidade,$pessoa,$classe)
    {    
    
        $data_vencimento = new DateTime($titulo->dt_vencimento);
        $diasdesconto1 = $titulo->dt_vencimento - $titulo->dt_limite_desconto_boleto_cobranca;
        //var_dump($diasdesconto1);
        //return;
            
        if ( $pessoa->pessoa_fisica_juridica == 'J' ) {
            $cpf_cnpj = $pessoa->cnpj;
        } else {
            $cpf_cnpj = $pessoa->cpf;
        }
            
        $nome = substr($pessoa->nome, 0, 80);
                        
        $data = json_encode(array(
                        'vencimento'=>date_format($data_vencimento,'m/d/Y'),
                        'valor'=>$titulo->valor,
                        'juros'=>$condominio->juros,
                        'multa'=>$condominio->multa,
                        'desconto'=>$condominio->desconto,
                        'nome_cliente'=>$nome,
                        'cpf_cliente'=>$cpf_cnpj,
                        'endereco_cliente'=>$pessoa->endereco,
                        'numero_cliente'=>$pessoa->numero,
                        'complemento_cliente'=>'Bl/Qd-Unid.' . $unidade->bloco_quadra . '- ' . $unidade->descricao,
                        'bairro_cliente'=>$pessoa->bairro,
                        'cidade_cliente'=>$pessoa->cidade,
                        'estado_cliente'=>$pessoa->estado,
                        'cep_cliente'=>$pessoa->cep,
                        'logo_url'=>'http://www.facilitahomeservice.com.br/facilitasmart/app/images/logo.png',
                        'texto'=>$titulo->descricao,
                        'grupo'=>$titulo->mes_ref, // link para impressão em lote
                        'webhook'=>'http://www.facilitahomeservice.com.br/facilitasmart/retorno.php',
                        'pedido_numero'=>$titulo->id,
                        ));
            
                        //'logo_url'=>'http://vps12978.publiccloud.com.br/facilitasmart/app/images/logo.png',
                        
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
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
              ),));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return false;
        } else {
            //echo $response;
            $pjbank=json_decode($response);
            return ($pjbank);
        }            
            
    
    }
    
}
