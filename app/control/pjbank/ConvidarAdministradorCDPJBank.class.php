<?php
/**
 * @author  <your name here>
 */
class ConvidarAdministradorCDPJBank extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ConvidarAdministradorCDPJBank');
        $this->form->setFormTitle('Convidar Administrador Conta Digital PJBank');

        $email = new TEntry('email');
        
        $this->form->addFields( [new TLabel('Email:')], [$email] );
        $email->setSize('70%');
        $email->addValidation( 'Email', new TRequiredValidator );
        
        
        $this->form->addAction('Enviar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
        $this->form->addAction('Verificar', new TAction(array($this, 'onVerify')), 'fa:arrow-circle-o-right');
                
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);

    } 
    
    public static function onVerify($param)
    {
        TTransaction::open('facilitasmart');
        
        $condominio = new Condominio(TSession::getValue('id_condominio')); 
                
        if ($condominio->credencial_cd_pjbank == '') {
            new TMessage('Conta Digital', 'Condomínio não possue uma conta digital !');
            TTransaction::close(); // close the transaction
            return;
        }
        
               
        try {
               
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$condominio->credencial_cd_pjbank."/administradores/".$param['email'],
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
                 $jsonRet=json_decode($response, true);
                    
                 //var_dump($jsonRet);
                            
                 if ($jsonRet->status < '300') {
                     new TMessage('info', 
                                  "Status   : ". $jsonRet['status']." </br >".
                                  "Mensagem : ". $jsonRet['msg']." </br >" );                    
                        
                       
                 } else {
                     new TMessage('Erro', $jsonRet->msg . "(cURL Error #:" . $err.")");
                 }
                  
             } 
                
             TTransaction::close(); // close the transaction
                    
         } catch (Exception $e) {
             new TMessage('error', $e->getMessage()); // shows the exception error message
             TTransaction::rollback(); // undo all pending operations
         }
        
     }
    
    public static function onNext($param)
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
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$condominio->credencial_cd_pjbank."/administradores",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_POSTFIELDS => $json,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE-CONTA: " . $condominio->chave_cd_pjbank,
                "Content-Type: application/json"
              ),));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                //var_dump(curl_getinfo($curl));
                
                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $jsonRet=json_decode($response, true);
                    
                    //var_dump($jsonRet);
                            
                    if ($jsonRet->status < '300') {
                        new TMessage('info', 
                                     "Status   : ". $jsonRet['status']." </br >".
                                     "Mensagem : ". $jsonRet['msg']." </br >" );                    
                        
                       
                    } else {
                        new TMessage('Erro', $jsonRet->msg . "(cURL Error #:" . $err.")");
                    }
                  
                } 
                
                TTransaction::close(); // close the transaction
                    
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }


}

