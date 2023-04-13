<?php
/**
 * UnidadeForm Form
 * @author  <your name here>
 */
class UnidadeForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Unidade');
        $this->form->setFormTitle('Unidades');
        
        $table = new TTable;
        $table->style = 'width: 600px';
        $tableOcorrencia = new TTable;
        $tableOcorrencia->style = 'width: 600px';
        
        $notebook = new TNotebook(600, 850);
        $notebook->appendPage('Unidade - Cadastramento', $table);
        $notebook->appendPage('Unidade - Ocorrências', $tableOcorrencia);
        
        // creates the notebook page
        //$page1 = new TTable;
        //$page2 = new TTable;
        //$page3 = new TTable; 
        
        //$this->form->appendPage('Principal', $page1);
        
        // create the form fields
        $id = new TEntry('id');
        $bloco_quadra = new TEntry('bloco_quadra');
        $descricao = new TEntry('descricao');
        
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        TTransaction::open('facilitasmart');
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        foreach ($users as $user)
        {
            if ($user->nivel_acesso_inf == '2') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                $criteria->add(new TFilter('id', '=', $user->condominio_id));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', '{id} - {resumo}', 'resumo', $criteria);
            }else {
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', '{id} - {resumo}', 'resumo');
            } 
            
        }
        TTransaction::close();
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
                
        $proprietario_id = new TDBCombo('proprietario_id', 'facilitasmart', 'Pessoa', 'id', '{id} - {nome}', 'nome', $criteria);
        $morador_id = new TDBCombo('morador_id', 'facilitasmart', 'Pessoa', 'id', '{id} - {nome}', 'nome', $criteria);
        
        
        
        
        $fracao_ideal = new TEntry('fracao_ideal');
        $observacao = new TText('observacao');
        $envio_boleto = new TCombo('envio_boleto');

        $acesso_id = new THidden('acesso_id'); // campo que controle o acesso ao portal tabela usuario_condominio
        
                
        // add the fields
        $this->form->addFields([new TLabel('Id')], [$id] );
        $this->form->addFields([new TLabel('Bloco/Quadra')], [$bloco_quadra] );
        $this->form->addFields([new TLabel('Descrição')], [$descricao] );
        $this->form->addFields([new TLabel('Condomínio')], [$condominio_id] );
        $this->form->addFields([new TLabel('Proprietário')], [$proprietario_id]);
        $this->form->addFields([new TLabel('Morador')], [$morador_id]);
        $this->form->addFields([new TLabel('Observação')], [$observacao]);
        $this->form->addFields([new TLabel('Fração Ideal')], [$fracao_ideal]);        
        
        //$label_envio_boleto = new TLabel('1-Não definido, 2-Condomínio, 3-E-mail, 4-Correio');
        //$label_envio_boleto->setFontStyle('b');
        //$label_envio_boleto->style.=';float:left';
        
        $envio_boleto->addItems(array( 
        '1'=>'Não definido', 
        '2'=>'Condomínio',
        '3'=>'E-mail',
        '4'=>'Correio',
        ));
        
        
        //$this->form->addQuickField('Formas de envio do boleto ', $label_envio_boleto, '100%');
        
        $this->form->addFields([new TLabel('Envio Boleto')], [$envio_boleto]);
        $this->form->addFields([new TLabel('')], [$acesso_id]);
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o' );
        $btn->class = 'btn btn-sm btn-primary';
        
        //$page3->addRowSet( array($btn, $btn), '', '')->class = 'tformaction'; // CSS class 
        
        $this->form->addAction( _t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green' );
        
        $this->form->addAction( _t('List'),  new TAction(array('UnidadeList','onReload')), 'fa:table blue');
        
        // 2a aba
        //$this->form->appendPage('Ocorrências', $page2);
        
        
        // 3a aba
        //$this->form->appendPage('Reservas', $page3);
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
        
        // creates a frame
        $frame = new TFrame;
        $frame->oid = 'frame-measures';
        $frame->setLegend('Pagamentos:');
        
        $row=$tableOcorrencia->addRow();
        $cell=$row->addCell($frame);
        $cell->colspan=2;
        
        
        // creates a Datagrid
        $datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $datagrid->datatable = 'true';
        $datagrid->style = 'width: 100%';
        $datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_documento = new TDataGridColumn('documento', 'Documento', 'center');
        $column_cheque = new TDataGridColumn('cheque', 'Cheque', 'center');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Dt Liquidação', 'center');

        // add the columns to the DataGrid
        $datagrid->addColumn($column_id);
        $datagrid->addColumn($column_documento);
        $datagrid->addColumn($column_cheque);
        $datagrid->addColumn($column_dt_liquidacao);
        
        
        $row=$tableOcorrencia->addRow();
        $cell=$row->addCell($datagrid);
        $cell->colspan=2;
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        
        
        //$container->add($this->form);
        $row=$table->addRow();
        $cell=$row->addCell($this->form);
        $cell->colspan=2;
        
        
        $container->add($notebook);
        
        // add the vbox inside the page
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
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new Unidade;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // torna maiusculo
            $object->descricao = strtoupper($object->descricao);
            $object->bloco_quadra = strtoupper($object->bloco_quadra);
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
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
        $this->form->clear(TRUE);
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
                $object = new Unidade($key); // instantiates the Active Record
                
                $this->condominio_nome = 'teste';
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function onEnviaEmailSenha($selecionado)
    {       
      //verificar se ja foi enviada, se sim, so permitir reset !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
      
      try
        {
           TTransaction::open('facilita');

           $imoveis = new Imoveis($selecionado->imoveis_id);
           
           //var_dump($imoveis);
           
           $nome = $imoveis->nome;

           $usuario = new pessoas($selecionado->pessoa_id);
           $cliente = $usuario->nome;
           
           $imovel = $imoveis->resumo;
           
           $email1 = $selecionado->system_user_login;
           //$email1 = 'teste@teste.com.br';
           
           //$empresa = $solicitante->origem_nome;
           //$responsavel = new Pessoa($object->responsavel_id);
           //$colaborador = $responsavel->pessoa_nome;
           
           $email2 = 'facilitahomeservice@gmail.com';
           //$email2 = 'teste@teste.com.br';
           
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
           
           $imagem = new TImage('app/images/facilita.png');
           $imagem->height=63;
           $imagem->width=96;
           
           $row = $table->addRow();
           $cell = $row->addCell( $imagem );
           $cell->style = 'width: 100px;';
           
           $cell = $row->addCell("Prezado Sr(a). <br /> {$cliente} do(a) {$nome} <br /> Seu acesso ao portal foi configurado.");
           $cell->style = 'width: 700px;';
                  
           $row = $table->addRow();
           $row->addCell('<span style="color: DarkOliveGreen;"><br /><u>Dados:</b></u></span>');
           
           $row = $table1->addRow();
           $cell = $row->addCell('<b>No. Usuário:</b>');
           $cell->style = 'width: 200px;';
           
           $cell = $row->addCell($selecionado->system_user_login);    // seu e-mail
                     
           $cell->style = 'width: 600px;';
           
           $row = $table1->addRow();
           $row->addCell('<b>Senha:</b>');
           $row->addCell('123456');
           
           // A senha inicial ou recuperação de senha, deve-se cadastrar para o usuario a senha 123456 e enviar o email
           // pedir para o usuario entrar no perfil e alterar.
           
           //$usuario = SystemUser::newFromLogin($selecionado->system_user_login);
           //var_dump($usuario);
           //var_dump(md5($usuario->password));
           //$usuario->senha = $object->senha;
           
           $row = $table1->addRow();
           $row->addCell('<b>Data/Hora:</b>');
           $row->addCell(date('d/m/Y H:i'));
           
          
           $row = $table->addRow();
           $cell = $row->addCell($table1);
           $cell->colspan=2;
           
                                 
           $row = $table->addRow();
           $cell = $row->addCell($table3);
           $cell->colspan=2;
           
           $row = $table->addRow();
           $row = $table3->addRow();
           $cell = $row->addCell('<b>Portal :</b>');
           $cell->style = 'width: 200px;';
           $cell = $row->addCell('www.facilitahomeservice.com.br/gestor');
           
           
           $row = $table4->addRow();
           $cell = $row->addCell('<span style="color: red;"><b>Importante:</b></span> Não responda esse e-mail, dúvida e ajuda através do email facilitahomeservice@gmail.com');
           $row = $table4->addRow();
           $cell = $row->addCell('<span style="color: red;"><b>Observação:</b></span> Acesse editar Perfil e troque sua senha!');
           $cell->style = 'width: 800px;';
           $row = $table->addRow();
           $cell = $row->addCell($table4);
           $cell->colspan=2;
  
           //$row = $table4->addRow();
           //$cell = $row->addCell('<span style="color: red;"><b>Observação:</b></span> Acesso editar Perfil e troque imediatamente sua senha!');
           //$cell->style = 'width: 800px;';
           //$row = $table->addRow();
           //$cell = $row->addCell($table4);
           //$cell->colspan=2;

  
           
           // fecha a transação
           TTransaction::close();
           
           $ini = parse_ini_file('app/config/email.ini');
           
           $mail = new TMail;
           $mail->setFrom($ini['from'], $ini['name']);
           $mail->setSubject('Facilita criou um ticket para voce');
           $mail->setHtmlBody($table);
           $mail->addAddress($email1);
           $mail->addCC($email2);
           //$mail->addBCC('facilitahomeservice@gmail.com.br');
           
           // Se tiver anexo
           if (isset($target_file))
           {
           $mail->addAttach($target_file);
           }
           $mail->SetUseSmtp();
           $mail->SetSmtpHost($ini['host'], $ini['port']);
           $mail->SetSmtpUser($ini['user'], $ini['pass']);
           //senao configurar em ini, nao pode ficar em branco $mail->setReplyTo($ini['repl']);
           $mail->send();
          
           
           new TMessage('info', 'Email enviado com sucesso');
           
          try
          {
            TTransaction::open('facilita'); // open a transaction
            $object = UsuarioImovel::find($selecionado->id); // load the object
            $object->dt_envio_senha=date("Y-m-d");
            $object->store();
            TTransaction::close(); // close the transaction
          
          }
          catch (Exception $e) // in case of exception
          {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
          }
               
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
              
    }    
}
