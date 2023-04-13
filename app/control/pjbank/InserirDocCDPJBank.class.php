<?php
/**
 * @author  <your name here>
 */
class InserirDocCDPJBank extends TPage
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
        $this->form = new BootstrapFormBuilder('form_InserirDocCDPJBank');
        $this->form->setFormTitle('Inserir Documento em Conta Digital PJBank');

        $filename = new TFile('filename');
        //$filename->setService('SystemDocumentUploaderService');
        $tipo_documento = new TEntry('tipo_documento');
        
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] );
        $filename->setSize('70%');
        $filename->addValidation( _t('File'), new TRequiredValidator );
        
        $this->form->addFields( [new TLabel('Tipo de Documento (contratosocial ou ata)')], [$tipo_documento] );
        $tipo_documento->setSize('70%');
        $tipo_documento->addValidation( 'Tipo de Documento', new TRequiredValidator );
        
        
        $this->form->addAction('Enviar', new TAction(array($this, 'onNext')), 'fa:arrow-circle-o-right');
                
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);

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
              
                $filePath = "tmp/".$param['filename'];
                $cfile = new CURLFile($filePath);
                
                $parameters['arquivos'] = $cfile;
                $parameters['tipo'] = $param['tipo_documento'];                
                
                $json = json_encode($parameters);
                
                $curl = curl_init();

                curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/contadigital/".$condominio->credencial_cd_pjbank."/documentos",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_POSTFIELDS => $parameters,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => array("X-CHAVE-CONTA: " . $condominio->chave_cd_pjbank),));
                    
                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $jsonRets=json_decode($response, true);
   
                            
                    if ($jsonRets['status'] < '300') {
                        new TMessage('info', 
                                     "Status   : ". $jsonRets['status']." </br >".
                                     "Mensagem : ". $jsonRets['msg']." </br >" );
                    } else {
                        new TMessage('info', "Mensagem : ". $jsonRets['message']." </br >" ); 
                    }
                  
                } 
                
                TTransaction::close(); // close the transaction
                    
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        
        }


}

