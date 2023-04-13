<?php
/**
 * unidadesForm Registration
 * @author  <your name here>
 */
 
     //// habilitar erros da pagina
    //ini_set('display_errors', 1);
    //ini_set('display_startup_erros', 1);
    //error_reporting(E_ALL);

class MesRefImovel extends TPage
{
    protected $form;
    
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        // create the form
        //$this->form = new TForm;
        //$this->form->class = 'tform';
        $this->form = new BootstrapFormBuilder('form_mesref_imovel');
        $this->form->setFormTitle('Mês Referência e Condomínio');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $criteria->add(new TFilter('active', '=', 'Y'));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
            
        
        
        //$combo  = new TDBCombo('condominio', 'facilitasmart', 'Condominio', 'id', 'resumo');
        //$condominio_id->setValue(1);//default primeiro imovel
        $condominio_id->setSize(200);
        
        $mesref = new TEntry('mesref');
        //$mesref->setSize(100);
                
        $datahoje = date('Y-m-d');
        $partes = explode("-", $datahoje);
        //print var_dump($partes);
        //print '<br />'; 
        
        $ano_hoje = $partes[0];
        $mes_hoje = $partes[1];
        
        //$mes_ant  = ((int) $mes_hoje ) -1;
        $mes_ant  = ((int) $mes_hoje ); // usa o mes atual e nao o anterior como era antes
        $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
        
        $dia_hoje = $partes[2];
        //print var_dump($mes_hoje . '/' . $ano_hoje);
        //print '<br />'; 
        
        if ( $mes_hoje == '1' ) {
          $mes_ant= '12';
          $ano_hoje = ((int) $ano_hoje ) -1;
        }
        
        $mesref->setValue($mes_ant . '/' . $ano_hoje); 
             
        $this->form->addFields( [new TLabel('Condomínio <i>*</i>')], [$condominio_id] );
        $this->form->addFields( [new TLabel('Mês Referência :')], [$mesref] );
        
        $condominio_id->setSize('100%');
        $mesref ->setSize('30%');

        $btn = $this->form->addAction('Salvar Configuração', new TAction(array($this, 'onSave')), 'fa:arrow-circle-o-right');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink('Teste', new TAction(array($this, 'onTeste')), 'fa:plus green');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    public function onTeste($param)
    {
        //Como fazer POST usando file_get_contents, cURL
        
        // enviar para o receber.php
        $postData = http_build_query(array('chave' => '8989898989 120 2310 3201 231023 1'));
        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://www.facilitahomeservice.com.br/facilitasmart/receber.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        var_dump(file_get_contents('/tmp/dados-post.txt'));die;
    
    }
    
    /**
     * Simulates an save button
     * Show the form content
     */
    public function onSave($param)
    {
       
        $data = $this->form->getData(); // optional parameter: active record class
        
        // put the data back to the form
        $this->form->setData($data);
       

        // tratamento de segurança, so permite configuracao dia util
        //$diaSemana = date('w', date('m/d/Y')); // de 0 a 6
        $hora = date("H:m");
        //var_dump($hora>'19:00');
        //var_dump($hora<'07:30');
        //var_dump($hora);
        //var_dump(date('m/d/Y'));
        //var_dump($diaSemana);

        if (($hora>'22:00') and ($hora<'23:59')) {
            new TMessage('info', 'Horário de acesso não permitido! Volte em horário comercial!');
            TTransaction::open('facilitasmart'); // open a transaction
            $object = new OcorrenciaUnidade;  // create an empty object
                    $object->system_user_login = 'acesso_negado';
                    $object->condominio_id = 15;// registra um erro/problema na Facilita
                    $object->status = '1'; // concluído
                    $object->descricao = 'Tentativa de acesso negada: ' . $data->condominio_id;
                    $object->unidade_id = 5186;
                    $object->tipo_id = 1;
                    $object->data_ocorrencia = date('yyyy-m-d');
                    $object->hora_ocorrencia = date('H:i:s');
                    $object->store();
                TTransaction::close(); 
            return;

        }

        if (($hora>'00:01') and ($hora<'07:30')) {
            new TMessage('info', 'Horário de acesso não permitido! Volte em horário comercial!');
            TTransaction::open('facilitasmart'); // open a transaction
            $object = new OcorrenciaUnidade;  // create an empty object
                    $object->system_user_login = 'acesso_negado';
                    $object->condominio_id = 15;// registra um erro/problema na Facilita
                    $object->status = '1'; // concluído
                    $object->descricao = 'Tentativa de acesso negada: ' . $data->condominio_id;
                    $object->unidade_id = 5186;
                    $object->tipo_id = 1;
                    $object->data_ocorrencia = date('yyyy-m-d');
                    $object->hora_ocorrencia = date('H:i:s');
                    $object->store();
            TTransaction::close();
            return;

        }

       // grava resultado
       TSession::setValue('id_condominio', $data->condominio_id);
       TSession::setValue('mesref', $data->mesref);
       
       // buscar o caminho na tabela imoveis
       try
       {
           TTransaction::open('facilitasmart');
           
           $user0 = Condominio::find($data->condominio_id); 
           
           TSession::setValue('resumo', $user0->resumo);
           TSession::setValue('endereco', $user0->endereco  . ' - ' . $user0->bairro . ', ' . $user0->cidade . ', ' . $user0->cep);
           
           $user = ComplementoCondominio::find($data->condominio_id); 

           if ($user instanceof ComplementoCondominio)
            {
                TSession::setValue('caminho', $user->caminho);  // pasta dos arquivos publicados 
            }

           TTransaction::close();
       
       }
       catch (Exception $e) // in case of exception
       {
           new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        
       }

        // show the message
        //new TMessage('info', $TSession::getValue('id_imovel') ' - ' $TSession::getValue('mesref'));
        //new TMessage('info', '<b>Mês Ref.:</b> ' . $TSession::getValue('id_imovel') );
        new TMessage('info', 'Configurado : ' . '<br>' . 
               'Condomínio : [' . $user0->id . '] ' . TSession::getValue('resumo') . '<br>' . 
               'Mês Ref.: ' . TSession::getValue('mesref'));

        exit;
        // quando habilito ele não pausa a mensagem
      //  TApplication::gotoPage('EmptyPage'); // reload
       
    }
}
?>