<?php
/**
 * OcorrenciaUnidade REST service
 */
class OcorrenciaUnidadeService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'OcorrenciaUnidade';
    
    public static function newPedido( $param )
    {
  
        $data = new DateTime();
             
        $location =  'https://facilitasmart.facilitahomeservice.com.br/rest.php';
        
        $parameters = array();
        $parameters['class'] = 'OcorrenciaUnidadeService';
        $parameters['method'] = 'store';
        $parameters['data'] = [
                                'data_ocorrencia' => $data->format('Y-m-d'),
                                'hora_ocorrencia' => date("H:i:s"),
                                'tipo_id' => '2', // atendimento
                                //'data_ocorrencia' => $param['unidadeinformada'],
                                'unidade_id' => $param['unidade_id'],
                                'condominio_id' => $param['condominio_id'],
                                'descricao' => $param['descricao'],
                              ];
        $url = $location . '?' . http_build_query($parameters);
        //var_dump( json_decode( file_get_contents($url) ) );
        
        $conteudo = file_get_contents($url);
        if ($conteudo)
        {
            // decodifica retorno JSON
            $retorno = (array) json_decode( $conteudo );
            // se retorno é íntegro
            if (json_last_error() == JSON_ERROR_NONE)
            {
                // se ocorreu erro lógico no servidor
                if ($retorno['status'] == 'error')
                {
                    throw new Exception($retorno['data']);
                }
            }

            // retorna dados ok
            return $retorno['data'];
        }
        else
        {
            // se conexão falhou
            throw new Exception('Connection failed');
        }
        
        // inicio enviando email
        //TTransaction::open('permission'); 
        //$preferences = SystemPreference::getAllPreferences();
        //$mail = new TMail;
        //$mail->setDebug(false);
        //$mail->SMTPSecure = "ssl";
        //$mail->setFrom( trim($preferences['mail_from']), 'FacilitaSmart' );
        //$mail->addAddress( trim($param['email']), 'Validação' );
        //$mail->setSubject( 'FacilitaSmart - Ativação de app' );
        //if ($preferences['smtp_auth'])
        //{
        //    $mail->SetUseSmtp();
        //    $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
        //    $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
        //}
        //$body = 'Seu código de validação é ' . $codigo_validacao;
        //$mail->setTextBody($body);    
        //sleep(3);            
        //$mail->send();
        //TTransaction::close();
        // fim teste email
        
    }
    
    /*
    5 - administradora
    6 - portaria
    
    http://www.facilitahomeservice.com.br/facilitasmart/rest.php?class=OcorrenciaUnidadeService&method=getOcorrenciaGeral&condominio_id=6
    */
    public static function getOcorrenciaGeral( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));
        $criteria->add(new TFilter('tipo_id', '=', $param['tipo_id']));
        //$criteria->add(new TFilter('tipo_id', '=', 5), TExpression::OR_OPERATOR); 
        //$criteria->add(new TFilter('tipo_id', '=', 6), TExpression::OR_OPERATOR);
 
        $limite = 6;
        $param1['order'] = 'datahora_cadastro'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
 
        // carrega
        $all = OcorrenciaUnidade::getObjects( $criteria );
        foreach ($all as $ocorrencia)
        {
            $response[] = $ocorrencia->toArray();
        }
        TTransaction::close();
        return $response;
    }
    
    /*
    http://www.facilitahomeservice.com.br/facilitasmart/rest.php?class=OcorrenciaUnidadeService&method=getOcorrenciaUnidade&condominio_id=6&unidade_id=312
    */
    public static function getOcorrenciaUnidade( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        $criteria->add(new TFilter('unidade_id', '=', $param['unidade_id']));
        $criteria->add(new TFilter('tipo_id', '!=', '1')); // não mostra as ocorrencias de cobranca, são exclusivas da administradora    
        $limite = 50;
        $param1['order'] = 'datahora_cadastro'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = OcorrenciaUnidade::getObjects( $criteria );
        foreach ($all as $ocorrencia)
        {
            $response[] = $ocorrencia->toArray();
        }
        TTransaction::close();
        return $response;
    }
}
