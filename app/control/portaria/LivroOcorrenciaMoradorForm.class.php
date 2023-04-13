<?php
/**
 * LivroOcorrenciaMoradorForm Form
 * @author  <your name here>
 *
 * status : 0 - nao tratada, 1 - tratada
 */
class LivroOcorrenciaMoradorForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_LivroOcorrencia');
        $this->form->setFormTitle( 'Registro no Livro de Ocorrências' );
        
        // create the form fields
        //$id = new TEntry('id');
        $datahora_cadastro = new TEntry('datahora_cadastro');
        
        $pessoa = new TEntry('pessoa');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}','descricao',$criteria);
        
        $data_ocorrencia = new TDate('data_ocorrencia');
        $hora_ocorrencia = new TEntry('hora_ocorrencia');
        $descricao = new TText('descricao');
        $system_user_login = new TEntry('system_user_login');
        $system_user_email = new TEntry('system_user_email');

        // escondidos
        $id = new THidden('id');
        $condominio_id = new THidden('condominio_id');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel('Data Cadastro')], [$datahora_cadastro]);
        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id]);
        $this->form->addFields( [new TLabel('Pessoa')], [$pessoa]);
        $this->form->addFields( [new TLabel('Data Ocorrência')], [$data_ocorrencia],
        [new TLabel('Hora Ocorrência')], [$hora_ocorrencia]);
        $this->form->addFields( [new TLabel('Descrição')], [$descricao]);
        $this->form->addFields( [new TLabel('Login')], [$system_user_login],
                                [new TLabel('Nome Login')], [$system_user_email]);
        //$this->form->addFields( [new TLabel('Condomínio')], [$condominio_id]);
         
        // desabilitar edicao
        $datahora_cadastro->setEditable(false);
        $system_user_login->setEditable(false);
        $system_user_email->setEditable(false);

        // preenchidos automaticamente
        $datahora_cadastro->setValue(date('Y/m/d H:i:s'));
        
        $system_user_login->setValue(TSession::getValue('login')); 
        
        $system_user_email->setValue(TSession::getValue('username')); 
        
        $hora_ocorrencia->setMask('99:99:99');
        $data_ocorrencia->setMask('dd/mm/yyyy');

        $id->setEditable(FALSE);
           
        $id->setSize(50);
        $datahora_cadastro->setSize('100%');
        //Pablo Dall'Oglio O addquickfield bota um tamanho default. Chama o setsize depois
        $descricao->setSize('100%', 120);
        $data_ocorrencia->setSize('100%');
        $hora_ocorrencia->setSize('100%');
        $system_user_email->setSize('100%');
        $system_user_login->setSize('100%');
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
                
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green' );
        
        $container = new TVBox;
        $container->style = 'width:90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'LivroOcorrenciaMoradorForm'));
        $container->add($this->form);
        
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
                        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            $string = new StringsUtil;
            
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
      
            $this->form->validate(); // validate form data
            
            $object = new LivroOcorrencia;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
     
            // verifica o nivel de acesso do usuario
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            foreach ($users as $user)
            {
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
            }

            $object->condominio_id = $condominio_id;
            
              
              // esta dando erro na validacao          
            //$validador = new TDateValidator;
            //$validador->validate('Data Ocorrência', '', array($object->data_ocorrencia));
            //var_dump($object);
            
            //formato necessário no mysql
            $object->data_ocorrencia = TDate::date2us($object->data_ocorrencia ); 
            
           
            $object->store(); // save the object
            
            $object->data_ocorrencia ? $object->data_ocorrencia = $string->formatDateBR($object->data_ocorrencia) : null;
            
            //var_dump($object);
            
            // notificar a todos os colaborados da administrado pela tabela usuario condominio 
            $users = UsuarioCondominio::where('nivel_acesso_inf', '=', '1')->load();
            foreach ($users as $user)
            {
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
                
                //notifica ao user (Jr da ocorrencia de um registro)
                // Classe que gera notificação de sistema para usuário:
                // SystemNotification::register( $user, $title, $message, $action, $label, $icon );
                // &user    = Id do usuário (tabela permission > system_user);
                // &title   = Título da notificação;
                // &message = Mensagem da notificação;
                // &action  = Ação a ser executada pelo usuário;
                // &label   = Texto do botão que executa a ação;
                // &icon    = Ícone do botão que executa a ação;
                // Exemplo
                
                TTransaction::open('permission');
                
                // pega o id do user_login
                $usuario = SystemUser::where('login', '=', TSession::getValue('login'))->load();

                   
                SystemNotification::register( $usuario->id, 'Ocorrência', 'Registro Ocorrência de : ' . 
                    $object->pessoa, 'class=SystemNotificationList', 'Confirmar', 'fa fa-check-circle-o green' );
                 
                TTransaction::close();
            }
            
            //notifica ao user (Jr da ocorrencia de um registro)
            // Classe que gera notificação de sistema para usuário:
            // SystemNotification::register( $user, $title, $message, $action, $label, $icon );
            // &user    = Id do usuário (tabela permission > system_user);
            // &title   = Título da notificação;
            // &message = Mensagem da notificação;
            // &action  = Ação a ser executada pelo usuário;
            // &label   = Texto do botão que executa a ação;
            // &icon    = Ícone do botão que executa a ação;
            // Exemplo
            SystemNotification::register( 2, 'Ocorrência', 'Registro Ocorrência de : ' . $object->pessoa, 
                'class=SystemNotificationList', 'Confirmar', 'fa fa-check-circle-o green' ); 
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            
            // notifica administradora
            $this->EnviaEmail();

            TApplication::gotoPage('LivroOcorrenciaList'); 
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
        $this->form->clear();
        
        $data = new StdClass;
        $data->data_dia = date('Y/m/d H:i:s');
        $data->system_user_login = TSession::getValue('login'); 
        $data->system_user_email = TSession::getValue('email'); 
        $this->form->setData($data); 

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
                $object = new LivroOcorrencia($key); // instantiates the Active Record

                if ($object->system_user_login != TSession::getValue('login')) {
                  new TMessage('error', 'Não é possível alterar uma ocorrência de outro usuário !'); // shows the exception error message
                  TTransaction::close();
                  return;
                }
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
 /*
    * Envia email para o departamento (gestores e administradora)
    *
    *
    */
    public function EnviaEmail()
    {       
        try
        {
           TTransaction::open('facilitasmart');
           $object = $this->form->getData('livroocorrencia');
        
           $vars['tipo_origens']                 = 'x';//$object->tipo_origens;
           $vars['codigo_cadastro_origem']       = 'y';//$object->codigo_cadastro_origem;
           $vars['solicitante_id']               = 'z';//$object->solicitante_id;
                
           ///$this->onChangeOrigem($vars);
           //$this->onChangeTipoOrigem($vars);
           //$this->onSetarValoresCombo($vars);
           
           $status = 'N - Não Tratada';
                      
           $solicitante = $object->pessoa; //new Pessoa($object->solicitante_id);
           $cliente = 'xxxxxxxxxxx'; //$solicitante->pessoa_nome;
           $email1 = 'facilitahomeservice@gmail.com';//$solicitante->email1;
           $empresa = TSession::getValue('id_condominio');//$solicitante->origem_nome;
           
           $responsavel = 'responsavel';//new Pessoa($object->responsavel_id);
           $colaborador = 'colaborador';//$responsavel->pessoa_nome;
           
           $email2 = $object->system_user_email;//$responsavel->email1;
           
           $table = new TTable;
           $table->border = 0;
           $table1 = new TTable;
           $table1->border = 1;
           $table2 = new TTable;
           $table2->border = 1;
           $table3 = new TTable;
           $table3->border = 1;
           $table4 = new TTable;
           $table4->border = 1;
           
           $imagem = new TImage('app/images/facilita.jpg');
           $imagem->height=63;
           $imagem->width=96;
           
           $row = $table->addRow();
           $cell = $row->addCell( $imagem );
           $cell->style = 'width: 100px;';
           $cell = $row->addCell("Prezado(s), <br /> Foi registrada uma ocorrência conforme os dados a seguir:");
           $cell->style = 'width: 700px;';
                  
           $row = $table->addRow();
           $row->addCell('<span style="color: DarkOliveGreen;"><b><u>Cabeçalho:</b></u></span>');
           $row = $table1->addRow();
           $cell = $row->addCell('<b>Número de controle:</b>');
           $cell->style = 'width: 200px;';
           $cell = $row->addCell($object->id);
           $cell->style = 'width: 600px;';
           $row = $table1->addRow();
           $row->addCell('<b>Condomínio:</b>');
           
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
        
           $row->addCell($condominio->resumo);
           
           $row = $table1->addRow();
           $row->addCell('<b>Data/Hora:</b>');
           $row->addCell(date('d/m/Y H:i'));
           $row = $table1->addRow();
           $row->addCell('<b>Status:</b>');
           $row->addCell($status);
           $row = $table1->addRow();
           $row->addCell('<b>Solicitante:</b>');
           $row->addCell($object->pessoa);
           $row = $table1->addRow();
           $row->addCell('<b>Data Ocorrência:</b>');
           $row->addCell($object->data_ocorrencia);
           
           $row = $table->addRow();
           $cell = $row->addCell($table1);
           $cell->colspan=2;
           
           $row = $table->addRow();
           $row->addCell('<span style="color: DarkOliveGreen;"><b><u>Solicitação:</b></u></span>');
           
           $row = $table2->addRow();
           $cell = $row->addCell('<b>Descrição:</b>');
           $cell->style = 'width: 200px;';
           $cell = $row->addCell($object->descricao);
           $cell->style = 'width: 520px;';
           $cell = $row->addCell($object->data_ocorrencia);
           $cell->style = 'width: 80px;';
           
           $row = $table->addRow();
           $cell = $row->addCell($table2);
           $cell->colspan=2;
           
           $row = $table->addRow();
           $row->addCell('<span style="color: DarkOliveGreen;"><b><u>Conclusão:</b></u></span>');
           $row = $table3->addRow();
           $cell = $row->addCell('<b>Data Conclusão:</b>');
           $cell->style = 'width: 200px;';
           $cell = $row->addCell('  /  /    ');
           $cell->style = 'width: 600px;';
           $row = $table3->addRow();
           $row->addCell('<b>Conclusão:</b>');
           $row->addCell(' em aberto ');
           
           //$row = $table3->addRow();
           //$row->addCell('<b>Valor Total:</b>');
           //$row->addCell('R$ xxxxxxxxxxxxxxxxxxxxxx');
           //$row = $table3->addRow();
           //$row->addCell('<b>Forma de pagamento:</b>');
           //$row->addCell('ddddddddddddddddddddddddd');
           //$row = $table3->addRow();
                        
           $row = $table->addRow();
           $cell = $row->addCell($table3);
           $cell->colspan=2;
           
           $row = $table4->addRow();
           $cell = $row->addCell('<span style="color: red;"><b>Importante:</b></span> Não é necessário responder esse e-mail, tomaremos as providências. Caso necessário escreva para facilitahomeservice@gmail.com');
           $cell->style = 'width: 800px;';
           $row = $table->addRow();
           $cell = $row->addCell($table4);
           $cell->colspan=2;
           TTransaction::close();
           
           $ini = parse_ini_file('app/config/email.ini');
           
           $mail = new TMail;
           $mail->setFrom($ini['from'], $ini['name']);
           $mail->setSubject('Ocorrência registrada por um morador');
           $mail->setHtmlBody($table);
           $mail->addAddress($email1);
           $mail->addCC($email2);
           $mail->addBCC('jrmaceio09@gmail.com');
           
           // envia uma copia para quem registrou a ocorrencia
           //$mail->addCC($object->system_user_email);  // copia para quem registrou
           
           // Se tiver anexo
           if (isset($target_file))
           {
           $mail->addAttach($target_file);
           }
           $mail->SetUseSmtp();
           $mail->SetSmtpHost($ini['host'], $ini['port']);
           $mail->SetSmtpUser($ini['user'], $ini['pass']);
           
           //$mail->setReplyTo($ini['repl']);
           //se nao configurar em ini, nao pode ficar em branco 
           
           $mail->send();
           
           new TMessage('info', 'Email enviado com sucesso');
               
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        $this->form->setData($object);
        
    }

     
    
}
