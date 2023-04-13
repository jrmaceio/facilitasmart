<?php
/**
 * AppController REST service
 */
class AppControllerService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'AppController';
    
    public static function getEmailConf( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;     
        $criteria->add(new TFilter('email', '=', $param['email'])); 
        $limite = 1;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = AppController::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    
    }
     
    public static function getCodigo( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('codigo_validacao', '=', $param['codigo']));     
        $criteria->add(new TFilter('email', '=', $param['email'])); 
        $limite = 1;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = AppController::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    
    }
    
    public static function getEmails( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        //$criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        
        //$limite = 6;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        //$criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = AppController::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    
    }
    
    public static function newEmail( $param )
    {
  
        $location =  'https://facilitasmart.facilitahomeservice.com.br/rest.php';
        
        $codigo_validacao = rand(1000, 9999);
        
        $parameters = array();
        $parameters['class'] = 'AppControllerService';
        $parameters['method'] = 'store';
        $parameters['data'] = [
                                'email' => $param['email'],
                                'unidade_informada' => $param['unidadeinformada'],
                                'telefone_informado' => $param['telefoneinformado'],
                                'condominio_informado' => $param['condominioinformado'],
                                'codigo_validacao' => $codigo_validacao,
                              ];
        $url = $location . '?' . http_build_query($parameters);
        //var_dump( json_decode( file_get_contents($url) ) );
        
        
        // inicio enviando email
        TTransaction::open('permission'); 
        $preferences = SystemPreference::getAllPreferences();
        $mail = new TMail;
        $mail->setDebug(false);
        $mail->SMTPSecure = "ssl";
        $mail->setFrom( trim($preferences['mail_from']), 'FacilitaSmart' );
        $mail->addAddress( trim($param['email']), 'Validação' );
        $mail->setSubject( 'FacilitaSmart - Ativação de app' );
        if ($preferences['smtp_auth'])
        {
            $mail->SetUseSmtp();
            $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
            $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
        }
        $body = 'Seu código de validação é ' . $codigo_validacao;
        $mail->setTextBody($body);    
        sleep(3);            
        $mail->send();
        TTransaction::close();
        // fim teste email
        
    }    
}    
