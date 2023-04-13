<?php
/**
 * UnidadeList Listing
 * @author  <your name here>
 */
class UnidadeList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {   // verifica o nivel de acesso do usuario
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
            $nivel_acesso = $user->nivel_acesso_inf;
            if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                $criteria->add(new TFilter('id', '=', $user->condominio_id));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
            }else {
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
            } 
            
        }
        TTransaction::close();
        
        parent::__construct();

        // creates the form
        $this->form = new TQuickForm('form_search_Unidade');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Unidade');
        

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        //$condominio_id = new TEntry('condominio_id');
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo');
        $proprietario_id = new TEntry('proprietario_id');
        $morador_id = new TEntry('morador_id');

        $label_descricao = new TLabel('Unidade');
        $label_descricao->setFontStyle('b');
        $label_descricao->style.=';float:left';
        
        $id->setSize('20%');
        $descricao->setSize('50%');

        // add the fields
        $this->form->addQuickFields('Id', array( $id, $label_descricao, $descricao));
        $this->form->addQuickField('Condominio', $condominio_id,  '50%' );
        //$this->form->addQuickField('Proprietario Id', $proprietario_id,  '100%' );
        //$this->form->addQuickField('Morador Id', $morador_id,  '100%' );
        
        if ($nivel_acesso == '2' or $nivel_acesso == '3' or $nivel_acesso == '4') {
            $condominio_id->SetValue($user->condominio_id);
            //throw new Exception(_t('Not logged'));
        }

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Unidade_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addQuickAction(_t('New'),  new TAction(array('UnidadeForm', 'onEdit')), 'bs:plus-sign green');
        
        $this->form->addQuickAction('Configura Acessos',  new TAction(array($this, 'onTodosAcesso')), 'fa:address-card green');
        $this->form->addQuickAction('Relatório',  new TAction(array($this, 'onRelatorioUnidades')), 'fa:print green');
        $this->form->addQuickAction('E-mails',  new TAction(array($this, 'onListaEmails')), 'fa:file green');
        $this->form->addQuickAction('Exporta CSV',  new TAction(array($this, 'onExportCSV')), 'fa:file green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        
        if ($nivel_acesso == '2' or $nivel_acesso == '3' or $nivel_acesso == '4') {
          $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Morador'.'</b><br>' . '{morador_nome}' 
            . '<br><b>'.'Email'.'</b><br>' . '{proprietario_email}');
        
        }else {
          $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Morador'.'</b><br>' . '{morador_nome}' 
            . '<br><b>'.'Envio Boleto'.'</b><br>' . '{tipo_envio_boleto}'
            . '<br><b>'.'Email'.'</b><br>' . '{proprietario_email}'
            . '<br><b>'.'CPF/CNPJ'.'</b><br>' . '{proprietario_cpfcnpj}');
        }
        
        $this->datagrid->setHeight(320);

        //$envio_boleto = new TCombo('envio_boleto');
        //$envio_boleto->addItems(array(1 => "Vazio", 2 => "Imóvel", 3 => "E-mail", 4 => "Correio"));
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco Quadra', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Unidade', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_resumo', 'Condomínio', 'right');
        $column_proprietario_id = new TDataGridColumn('proprietario_nome', 'Proprietário', 'right');
        //$column_morador_id = new TDataGridColumn('morador_id', 'Morador Id', 'right');
        //$column_fracao_ideal = new TDataGridColumn('fracao_ideal', 'Fracao Ideal', 'left');
        //$column_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');
        //$column_envio_boleto = new TDataGridColumn('envio_boleto', 'Envio Boleto', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_proprietario_id);
        //$this->datagrid->addColumn($column_morador_id);
        //$this->datagrid->addColumn($column_fracao_ideal);
        //$this->datagrid->addColumn($column_observacao);
        //$this->datagrid->addColumn($column_envio_boleto);

        
        // create EDIT action
        $action_edit = new TDataGridAction(array('UnidadeForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        $action_edit->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        $action_del->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Verifica Acesso action
        $action_veracesso = new TDataGridAction(array($this, 'onVerificaAcesso'));
        //$action_veracesso->setUseButton(TRUE);
        //$action_veracesso->setButtonClass('btn btn-default');
        $action_veracesso->setLabel('Vertifica Acesso');
        $action_veracesso->setImage('fa:address-card blue');
        $action_veracesso->setField('id');
        $this->datagrid->addAction($action_veracesso);
        $action_veracesso->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Envia Senha action
        $action_enviasenha = new TDataGridAction(array($this, 'onCadEnviaSenha'));
        //$action_enviasenha->setUseButton(TRUE);
        //$action_enviasenha->setButtonClass('btn btn-default');
        $action_enviasenha->setLabel('Cadastra e Envia Senha');
        $action_enviasenha->setImage('fa:envelope-open blue');
        $action_enviasenha->setField('id');
        $this->datagrid->addAction($action_enviasenha);
        $action_enviasenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Reset Senha action
        $action_resetsenha = new TDataGridAction(array($this, 'onResetSenha'));
        //$action_resetsenha->setUseButton(TRUE);
        //$action_resetsenha->setButtonClass('btn btn-default');
        $action_resetsenha->setLabel('Reset da Senha de Acesso');
        $action_resetsenha->setImage('fa:refresh blue');
        $action_resetsenha->setField('id');
        $this->datagrid->addAction($action_resetsenha);
        $action_resetsenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
         
        // create Reset Senha action
        $action_resetsenha = new TDataGridAction(array($this, 'onFinanceiro'));
        //$action_resetsenha->setUseButton(TRUE);
        //$action_resetsenha->setButtonClass('btn btn-default');
        $action_resetsenha->setLabel('Financeiro');
        $action_resetsenha->setImage('fa:money green');
        $action_resetsenha->setField('id');
        $this->datagrid->addAction($action_resetsenha);
        $action_resetsenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
                
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
     
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Unidade', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    function onListaEmails($param)
    {
        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            // creates a repository for unidades
            $repository = new TRepository('Unidade');
            $limit = 3000;
            // creates a criteria
            $criteria = new TCriteria;
            
          
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id'])); // add the session filter

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
                  
            $emails = '';
            
            if ($objects)
            {
                
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // relaciona todos os emails
                    
                    $proprietario = new Pessoa($object->proprietario_id);
                    
                    if ( $proprietario->email != 'teste@teste.com.br' ) {
                        $emails = $emails . ', ' . $proprietario->email;
                        
                    }
                    
                    //if ( $object->proprietario_id != $object->morador_id ) {
                    
                    //    if ( $object->morador_email != 'teste@teste.com.br' and $object->morador_email != '' ) {
                   //         $emails += ', mmmm---> ' . $object->morador_email ;
                   //         var_dump($object->morador_email);
                     //   }
                    
                } 
                    
              }
            
            
            $win = TWindow::create( 'Emails dos proprietários do condomínio !', 0.6, 0.6 );
            $win->add( $emails );
            $win->show();
            //var_dump($emails);   
               
            // close the transaction
            TTransaction::close();

        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    function onExportCSV($param)
    {
        $this->onSearch();

        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            // creates a repository for unidades
            $repository = new TRepository('Unidade');
            $limit = 3000;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id'])); // add the session filter
         
            $csv = '"Title","First Name","Middle Name","Last Name","Suffix","Given Name Yomi","Family Name Yomi","Home Street","Home City","Home State","Home Postal Code","Home Country","Company","Department","Job Title","Office Location","Business Street","Business City","Business State","Business Postal Code","Business Country","Other Street","Other City","Other State","Other Postal Code","Other Country","Assistant s Phone","Business Fax","Business Phone","Business Phone 2","Callback","Car Phone","Company Main Phone","Home Fax","Home Phone","Home Phone 2","ISDN","Mobile Phone","Other Fax","Other Phone","Pager","Primary Phone","Radio Phone","TTY/TDD Phone","Telex","Anniversary","Birthday","E-mail Address","E-mail Type","E-mail 2 Address","E-mail 2 Type","E-mail 3 Address","E-mail 3 Type","Notes","Spouse","Web Page"'."\n";
            
            // load the objects according to criteria
            $customers = $repository->load($criteria);
            
            
            if ($customers)
            {
                foreach ($customers as $customer)
                {
                    //var_dump($customer);
                    if ( $customer->proprietario_email != 'teste@teste.com.br' ) {
                    //,"Jorge",,"Dantas",,,,,"Maceió",,,"Brasil",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,"30/08/1904","jorge.silvadantas@hotmail.com","SMTP",,,,,,,
                    $csv .= ',"' . $customer->id . ' - ' . $customer->descricao . ' - ' . $customer->proprietario_nome.'"'.
                    ',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,"'.
                    $customer->proprietario_email.'",,,,,,,'."\n";
                    }
                }
                file_put_contents('app/output/contatos.csv', $csv);
                TPage::openFile('app/output/contatos.csv');
            }
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }

    }
    
    public function onFinanceiro( $param )
    {
        $string = new StringsUtil;
        
        // banco de dados
        TTransaction::open('facilitasmart');
        $connreceber = TTransaction::get();
        $sqlreceber = "SELECT contas_receber.id, 
           contas_receber.condominio_id,
           contas_receber.mes_ref,
           contas_receber.unidade_id,
           contas_receber.classe_id,
           contas_receber.dt_lancamento,
           contas_receber.dt_vencimento,
           contas_receber.valor,
           contas_receber.descricao,
           contas_receber.situacao,
           contas_receber.dt_pagamento,
           contas_receber.dt_liquidacao,
           contas_receber.valor_pago,
           contas_receber.conta_fechamento_id,
           contas_receber.parcela, 
           plano_contas.codigo as classificacao_codigo,
           plano_contas.descricao as classificacao_descricao
            FROM contas_receber 
            INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
            where  
            contas_receber.unidade_id = " . $param['id'] . " ";
                        
        $sqlreceber = $sqlreceber . " order by dt_vencimento";
        $colunasreceber = $connreceber->query($sqlreceber);
        ///
        
        $datagrid1 = new TDataGrid;
        $datagrid1 = new BootstrapDatagridWrapper(new TQuickGrid);
        $datagrid1->style = 'width: 100%';
        $datagrid1->datatable = 'true';
        
        
        $coluna_id = new TDataGridColumn('id', 'Id', 'right');
        $coluna_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref.', 'left');
        $coluna_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $coluna_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $coluna_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $coluna_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $coluna_situacao = new TDataGridColumn('situacao', 'Situação', 'left');
        $coluna_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'left');
        
        $datagrid1->addColumn($coluna_id);
        $datagrid1->addColumn($coluna_mes_ref);
        $datagrid1->addColumn($coluna_classe_id);
        $datagrid1->addColumn($coluna_unidade_id);
        $datagrid1->addColumn($coluna_dt_vencimento);
        $datagrid1->addColumn($coluna_valor);
        $datagrid1->addColumn($coluna_situacao);
        $datagrid1->addColumn($coluna_valor_pago);
        
        // create the datagrid model
        $datagrid1->createModel();

        //var_dump($colunasreceber);
        
        if ($colunasreceber)
        {
            $datagrid1->clear();
            
            $unidade = new Unidade($param['id']);
            $pessoa = new Pessoa($unidade->proprietario_id);
            
            foreach ($colunasreceber as $object)
            {
                //var_dump($object);
                
                $conta = new PlanoContas($object['classe_id']);
                
                $dados = new StdClass;
                $dados->id = $object['id'];
                $dados->mes_ref = $object['mes_ref']; 
                $dados->classe_id = $conta->descricao;
                $dados->unidade_id = $object['unidade_id'];
                $dados->dt_vencimento = $string->formatDateBR($object['dt_vencimento']);
                $dados->valor = $object['valor'];
                
                if ($object['situacao'] == '0') {
                  $dados->situacao = 'Em Aberto';
                }
                
                if ($object['situacao'] == '1') {
                  $dados->situacao = 'Pago';
                }  
                
                if ($object['situacao'] == '2') {
                  $dados->situacao = 'Em Acordo';
                }
                
                $dados->valor_pago = $object['valor_pago'];
              
                $datagrid1->addItem( $dados );      
            }
            
          $win = TWindow::create('Resumo Financeiro da Unidade', 0.6, 0.6);
          $win->add(new TLabel('Unidade : ' . $pessoa->nome ));
          $win->add($datagrid1);
          $win->show();    
        }
        
        TTransaction::close();
        
      }
    
    
        /**
        * Define when the action can be displayed
         */
        public function StatusBotaoColumn( $object )
        {
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
            if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                TButton::disableField('form_search_Unidade', 'btn_novo');
                TButton::disableField('form_search_Unidade', 'btn_configura_acessos');
                //TButton::disableField('form_search_Unidade', 'btn_relatório');
                TTransaction::close();
                return FALSE;
                    
            }else {
                TTransaction::close();
                return TRUE;    
            } 
            
        }
     }
    
    function onRelatorioUnidades($param)
    {
        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            $conn = TTransaction::get();
            $sql = "SELECT 
            t0.id,
            t0.bloco_quadra,
            t0.descricao,
            t0.condominio_id,
            t0.proprietario_id,
            t0.morador_id,
            t0.fracao_ideal,
            t0.observacao,
            t0.envio_boleto,
            t0.senha_enviada,
            t0.acesso_id,
            t1.nome as proprietario_nome,
            t1.email as proprietario_email,
            t1.rg as proprietario_rg,
            t1.cpf_cnpj as proprietario_cpf,
            t1.telefone1 as proprietario_telefone1,
            t1.telefone2 as  proprietario_telefone2,
            t1.telefone3 as proprietario_telefone3,
            t2.nome as morador_nome,
            t2.email as morador_email,
            t2.rg as morador_rg,
            t2.cpf_cnpj as morador_cpf,
            t2.telefone1 as morador_telefone1,
            t2.telefone2 as  morador_telefone2,
            t2.telefone3 as morador_telefone3,
            t3.resumo as condominio_resumo,
            t3.nome as condominio_nome 
            FROM unidade t0 
            INNER JOIN pessoa t1 on t0.proprietario_id = t1.id 
            INNER JOIN pessoa t2 on t0.morador_id = t2.id
            INNER JOIN condominio t3 on t0.condominio_id = t3.id 
            where t0.condominio_id = " . $param['condominio_id'] .
            ' order by t0.descricao' ;
            
            $objects = $conn->query($sql);
            
            $format  = 'pdf';
            
            $string = new StringsUtil;
            
            //var_dump($objects);
            //return;
            
            if ($objects)
            {
                // largura das colunas
                $widths = array(30,60,160,140,90,90,65,65,65,30);
                
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths, $orientation='L');
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths, $orientation='L');
                        break;
                }
                
               
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '10', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '9', 'I',  '#000000', '#A3A3A3');
                
                //$resumo = condominio_resumo;
                
                // qtd colunas
                $colunas = 10;
                
                $condominio = new Condominio($param['condominio_id']);
                
                //cabecalho
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                
                // add a header row
                $tr->addRow();
                $tr->addCell('Unidades', 'center', 'header', $colunas);
                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'left', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Proprietario', 'left', 'title');
                $tr->addCell('Email', 'left', 'title');
                $tr->addCell('RG', 'left', 'title');
                $tr->addCell('CPF / CNPJ', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Envio', 'left', 'title'); 
                
                // verifica o nivel de acesso do usuario
                // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
                // * 0 - Desenvolvedor
                // * 1 - Administradora
                // * 2 - Gestor
                // * 3 - Portaria
                // * 4 - Morador
                //TTransaction::open('facilitasmart');
                $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
                foreach ($users as $user)
                {
                    $nivel_acesso = $user->nivel_acesso_inf;
                }
                //TTransaction::close();
        
                // uma linha de cada cor conforme datai e datap (linha impar e linha par)
                // controls the background filling
                $colour = FALSE;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object['id'], 'right', $style);
                    $tr->addCell($object['descricao'], 'center', $style);
                    
                    if ( $object['proprietario_nome'] == 'UNIDADE VAZIA / CADASTRO INCOMPLETO' )
                    {
                      $tr->addCell('', 'left', $style);
                    }
                    else
                    {
                      $tr->addCell($object['proprietario_nome'], 'left', $style);
                    }
                    
                    if ( $object['proprietario_email'] == 'teste@teste.com.br' ) 
                    {
                      $tr->addCell('', 'left', $style);
                    }
                    else
                    {
                      $tr->addCell($object['proprietario_email'], 'left', $style);
                    }
                    
                    if ($nivel_acesso == '3' or $nivel_acesso == '4') { // morador e portaria
                        $tr->addCell('suprimido', 'center', $style);
                        $tr->addCell('suprimido', 'center', $style);
                    } else {
                        $tr->addCell($object['proprietario_rg'], 'center', $style);
                        $tr->addCell($object['proprietario_cpf'], 'center', $style);
                    }
                    
                    $tr->addCell($object['proprietario_telefone1'], 'center', $style);
                    $tr->addCell($object['proprietario_telefone2'], 'center', $style);
                    $tr->addCell($object['proprietario_telefone3'], 'center', $style);
                    
                    if ( $object['envio_boleto'] == 1 )
                    {
                      $tr->addCell('ND', 'center', $style);
                    } else if ( $object['envio_boleto'] == 2 )
                    {
                      $tr->addCell('Condom.', 'center', $style);
                    } else if ( $object['envio_boleto'] == 3 )
                    {
                      $tr->addCell('E-Mail', 'center', $style);
                    } else if ( $object['envio_boleto'] == 4 )
                    {
                      $tr->addCell('Correio', 'center', $style);
                    } 
                    
                    if ( $object['proprietario_nome'] != $object['morador_nome'] )
                    {
                      $colour = !$colour;
                      $style = $colour ? 'datap' : 'datai';
                      $tr->addRow();
                      $tr->addCell('', 'left', $style);
                      $tr->addCell('Inquilino', 'center', $style);
                      $tr->addCell($object['morador_nome'], 'left', $style);
                      $tr->addCell($object['morador_email'], 'left', $style);
                      $tr->addCell($object['morador_rg'], 'left', $style);
                      $tr->addCell($object['morador_cpf'], 'center', $style);
                      $tr->addCell($object['morador_telefone1'], 'center', $style); 
                      $tr->addCell($object['morador_telefone2'], 'center', $style); 
                      $tr->addCell($object['morador_telefone3'], 'center', $style); 
                      $tr->addCell('', 'left', $style);                     
                    }
                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s A'), 'center', 'footer', $colunas);
                
                // stores the file
                if (!file_exists("app/output/Unidades.{$format}") OR is_writable("app/output/Unidades.{$format}"))
                {
                    $tr->save("app/output/Unidades.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Unidades.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Unidades.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    public function onTodosAcessoON( $param )
    {
      try
      {
        TTransaction::open('facilitasmart');
    
        if(!$param['condominio_id'] ) {
          new TMessage('info', 'Nenhuma condomínio selecionada!');
          return;
        }
      
        //verificar se existe alguem já configurado se continuar, reseta todas as senhas
        $acesso = UsuarioCondominio::where('condominio_id', '=', $param['condominio_id'])->load();
        
        if ($acesso)
          {
            new TMessage('info', 'Existe unidade(s) configurada(s) para acesso, se continuar todas as senhas serão resetadas para este condomínio!');
            //return;     
          }
          
        $condominio = new Condominio($param['condominio_id']); 
        
        //SystemUserProgram::where('system_user_id', '=', $this->id)->delete();
                
        new TMessage('info', 'Configuração de acesso a todas unidades do Condomínio : ' . $condominio->resumo);
        //return;
        
        // exclui em usuario_condominio todas as configurações do condomínio
        // está excluindo abaixo UsuarioCondominio::where('condominio_id', '=', $param['condominio_id'])->delete();
        
        $conn = TTransaction::get();
        $colunas = $conn->query("select * from unidade where condominio_id = ".$condominio->id. " group by proprietario_id");
        
        foreach ($colunas as $row)
          {
            // verifica se a unidade já está cadastrada para ter acesso ao portal
            TTransaction::open('facilitasmart'); // open a transaction
            $proprietario = new Pessoa($row['proprietario_id']);
            
            if ($proprietario->email !='teste@teste.com.br') {  
              
              // exclui o usuairo e o usuairo_imovel para o email da pessoa, para posterior cadastro dos novos dados
              $conn1 = TTransaction::get();
              $result1 = $conn1->query("SELECT * FROM usuario_condominio where system_user_login='{$proprietario->email}'");
              foreach ($result1 as $row1)
              {
                $usuario_condominio = new UsuarioCondominio($row1['id']); 
                $usuario_condominio->delete();
              }                        
              TTransaction::close(); // close the transaction
             
              // deleto o usuario           
              TTransaction::open('permission');
              $conn2 = TTransaction::get();
              $result2 = $conn2->query("SELECT * FROM system_user where login='{$proprietario->email}'");
              foreach ($result2 as $row2)
              {
                //var_dump($row);
                $system_user = new SystemUser($row2['id']); 
                $system_user->delete();
              }
              TTransaction::close(); // close the transaction
                        
              // cadastra o usuario e coloca ele no ususario imovel para acesso ao portal do imovel
              TTransaction::open('permission');
              $object1 = new SystemUser; 
              $object1->name = $row['descricao'];
              $object1->login = $proprietario->email;
              $object1->password = 'e10adc3949ba59abbe56e057f20f883e'; // 123456
              $object1->email = $proprietario->email;
              $object1->frontpage_id = 80; // quadro de aviso - importante proque tem a configuracao de id_condominio
              $object1->system_unit_id = $row['condominio_id']; // as unidades devem ter o mesmo id do cadastro de imoveis
              $object1->active = 'Y';
              $object1->store(); 
              
              // adiciono ele ao grupo de morador
              $object2 = new SystemUserGroup;
              $object2->system_user_id = $object1->id;
              $object2->system_group_id = 4; // grupo morador = 4
              $object2->store(); 
              TTransaction::close(); // close the transaction
              
              TTransaction::open('facilitasmart'); // open a transaction
              
              // adiciona usuario_condominio
              $object3 = new UsuarioCondominio;
              $object3->ativo = 'Y';
              $object3->pessoa_id = $proprietario->id;
              $object3->system_user_login = $object1->login;
              $object3->condominio_id = $row['condominio_id'];
              $object3->unidade_id = $row['id'];
              $object3->dt_envio_senha = date('Y-m-d');
              $object3->nivel_acesso_inf = '4';
              $object3->store(); 
              TTransaction::close(); // close the transaction
               
              if ($object3->system_user_login != 'teste@teste.com.br') { 
                $this->EnviaEmailSenha($object3);
              }
            }
          }     
        /// fim
        
        TTransaction::close();
        
        new TMessage('info', 'Usuário configurado com sucesso !'); 
        
      }
      catch(Exception $e)
        {
          new TMessage('error', $e->getMessage());
        }
      }
      
    public function onVerificaAcesso( $param )
    {               
      try
      {
        TTransaction::open('facilitasmart');
        $unidade =  new Unidade($param['id']);
        $acesso_portal = new UsuarioCondominio($unidade->acesso_id);
                
        if($acesso_portal->id)
        {
          new TMessage('info', 'Acesso ao portal configurado!');
        }else {
          new TMessage('info', 'Acesso ao portal NÃO configurado!');
        }
        
        TTransaction::close();
      }
      catch(Exception $e)
          {
            new TMessage('error', $e->getMessage());
          }
      }
        
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new Unidade($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('UnidadeList_filter_id',   NULL);
        TSession::setValue('UnidadeList_filter_descricao',   NULL);
        TSession::setValue('UnidadeList_filter_condominio_id',   NULL);
        TSession::setValue('UnidadeList_filter_proprietario_id',   NULL);
        TSession::setValue('UnidadeList_filter_morador_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('UnidadeList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', 'like', "%{$data->condominio_id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->proprietario_id) AND ($data->proprietario_id)) {
            $filter = new TFilter('proprietario_id', 'like', "%{$data->proprietario_id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_proprietario_id',   $filter); // stores the filter in the session
        }


        if (isset($data->morador_id) AND ($data->morador_id)) {
            $filter = new TFilter('morador_id', 'like', "%{$data->morador_id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_morador_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Unidade_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Unidade
            $repository = new TRepository('Unidade');
            $limit = 50;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            // verifica o nivel de acesso do usuario para filtrar so as unidades do condominio
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            foreach ($users as $user)
            {
                if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                    $criteria->add(new TFilter('condominio_id', '=', $user->condominio_id)); // add the session filter
                } 
            
            }
        
            if (TSession::getValue('UnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_condominio_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_proprietario_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_proprietario_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_morador_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_morador_id')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new Unidade($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
    
    public function onResetSenha($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onResetSenhaON'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Confirma o Reset da senha do Usuaário ?', $action);
    }

    public function onResetSenhaON( $param )
    {
  
        $unidade = new Unidade($param['id']);
        $proprietario = new Pessoa($unidade->proprietario_id);
      
        // cadastrar e eviar email, em producao fica so o enviar email
        try
        {
           
              // localiza o usuario 
              TTransaction::open('permission');
              $conn1 = TTransaction::get();
              $result1 = $conn1->query("SELECT * FROM System_User where login='{$proprietario->email}'");
              foreach ($result1 as $row1)
              {
                $usuario = $row1['id'];
              }                                 
                       
              // reset de senha
              $object1 = new SystemUser($usuario);
              $object1->password = 'e10adc3949ba59abbe56e057f20f883e'; // 123456
              $object1->store(); 
              
              TTransaction::close(); // close the transaction
              
              // fim verificação acesso ao portal
              new TMessage('info', 'Reset efetuado com sucesso !'); 
         
              $this->EnviaEmailSenha($unidade);
                       
        }
        catch(Exception $e)
        {
          new TMessage('error', $e->getMessage());
        }    
    }
    
    public function onTodosAcesso($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onTodosAcessoON'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Configura o acesso ao Portal de TODOS Usuários ?', $action);
    }
    
    public function onCadEnviaSenha($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'onCadEnviaSenhaON'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Configura o acesso ao Portal deste Usuário ?', $action);
    }
    
    public function onCadEnviaSenhaON( $param )
    {
        // VERIFICAR SE JA FOI ENVIADA A SENHA E SE SIM, SO PERMITER RESETAR !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        TTransaction::open('facilitasmart');
        
        //var_dump($param);
        //return;
        $unidade = new Unidade($param['id']);
        $proprietario = new Pessoa($unidade->proprietario_id);
        
        if ($proprietario->email == 'teste@teste.com.br') {
            new TMessage('info', 'Email do proprietário não configurado !');
            return;
        }
         
        // exclui o usuairo e o usuairo_imovel para o email da pessoa, para posterior cadastro dos novos dados
        $conn = TTransaction::get();
        $result = $conn->query("SELECT * FROM usuario_condominio where system_user_login='{$proprietario->email}'");
        foreach ($result as $row)
        {
            $usuario_condominio = new UsuarioCondominio($row['id']);
              
            if( $usuario_condominio ) { 
                $usuario_condominio->delete();
            }else {
                new TMessage('info', 'Falha apagando configuração antiga do usuário !');
                TTransaction::close(); // close the transaction
                return;
            }
            }
        TTransaction::close(); // close the transaction
      
        // deleto o usuario           
        TTransaction::open('permission');
        $conn = TTransaction::get();
        $result = $conn->query("SELECT * FROM system_user where login='{$proprietario->email}'");
        foreach ($result as $row)
            {
              //var_dump($row);
              $system_user = new SystemUser($row['id']); 
              $system_user->delete();
            }
        TTransaction::close(); // close the transaction
                        
        // cadastra o usuario e coloca ele no ususario imovel para acesso ao portal do imovel
        TTransaction::open('permission');
              
        $object1 = new SystemUser; 
        $object1->name = $proprietario->nome;
        $object1->login = $proprietario->email;
        $object1->password = 'e10adc3949ba59abbe56e057f20f883e'; // 123456
        $object1->email = $proprietario->email;
        $object1->frontpage_id = 77; // dashboard
        $object1->system_unit_id = $unidade->condominio_id; // as unidades devem ter o mesmo id do cadastro de condominio
        $object1->active = 'Y';
        $object1->store(); 
              
        // adiciono ele ao grupo de morador
        $object2 = new SystemUserGroup;
        $object2->system_user_id = $object1->id;
        $object2->system_group_id = 4; // grupo morador = 3
        $object2->store(); 
              
        TTransaction::close(); // close the transaction
      
        TTransaction::open('facilitasmart'); // open a transaction
        // adiciona usuario_condominio
        $object3 = new UsuarioCondominio;
        $object3->ativo = 'Y';
        $object3->pessoa_id = $proprietario->id;
        $object3->system_user_login = $object1->login;
        $object3->condominio_id = $unidade->condominio_id;
        $object3->unidade_id = $unidade->id;
        $object3->dt_envio_senha = date('Y-m-d');
        $object3->nivel_acesso_inf = '4';
        $object3->store(); 
        TTransaction::close(); // close the transaction
              
        if ($object3->system_user_login != 'teste@teste.com.br') { 
            self::EnviaEmailSenha($unidade);
        }
             
        // fim verificação acesso ao portal
        new TMessage('info', 'Configurado e enviada Senha de acesso ao portal !');      
                
    }

    public function EnviaEmailSenha($object) //unidade
    {       
        try
        {
            TTransaction::open('facilitasmart');

            $condominio = new Condominio($object->condominio_id);
            
            $nome = $condominio->nome;

            $usuario = new Pessoa($object->proprietario_id);
           
            $cliente = $usuario->nome;
           
            $imovel = $condominio->resumo;
           
            //XXXXXXXXX///mudar no ambiente de homologação =====> $selecionado->system_user_login
            $email1 = $usuario->email;
            //var_dump($usuario->email);
            //$email1 = 'jrmaceio09@gmail.com';
           
           //$empresa = $solicitante->origem_nome;
           //$responsavel = new Pessoa($object->responsavel_id);
           //$colaborador = $responsavel->pessoa_nome;
           
           
            $email2 = 'facilitahomeservice@gmail.com';
           
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
           
            //$imagem = new TImage('app/images/facilita.png');
            //$imagem->height=63;
            //$imagem->width=96;
           
            //$row = $table->addRow();
            //$cell = $row->addCell( $imagem );
            //$cell->style = 'width: 100px;';
           
            $row = $table->addRow();
            $cell = $row->addCell("Prezado Sr(a). <br /> {$cliente} do(a) {$nome} <br /> Seu acesso ao portal foi configurado.");
            $cell->style = 'width: 700px;';
            
            $row = $table->addRow();
            $cell = $row->addCell("<br /> Acesse e tenha informações sobre prestação de conta, inadimplência, etc.");
            $cell->style = 'width: 700px;';
                  
            $row = $table->addRow();
            $row->addCell('<span style="color: DarkOliveGreen;"><br /><u>Dados:</b></u></span>');
           
            $row = $table1->addRow();
            $cell = $row->addCell('<b>Login Usuário:</b>');
            $cell->style = 'width: 200px;';
           
            $cell = $row->addCell($usuario->email);    // seu e-mail
                     
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
            $cell = $row->addCell('www.facilitahomeservice.com.br/facilitasmart');
           
           
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
           
            $ini = parse_ini_file('app/config/email.ini');
           
            $mail = new TMail;
            $mail->setFrom($ini['from'], $ini['name']);
            $mail->setSubject('O Portal FacilitaSamart criou um ticket para o Senhor ');
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
                $object = UsuarioCondominio::find($object->id); // load the object
                $object->dt_envio_senha=date("Y-m-d");
                $object->store();
          
           }
           catch (Exception $e) // in case of exception
           {
             new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
             TTransaction::rollback(); // undo all pending operations
           }
                        
           TTransaction::close(); // close the transaction    
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
    }


}
