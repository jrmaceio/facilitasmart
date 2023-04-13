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
    {   
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Unidade');

        // define the form title
        $this->form->setFormTitle('Unidades');
        
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
                        
        
        // create the form fields
        $id = new TEntry('            
            //var_dump($user);
            id');
        $descricao = new TEntry('descricao');
        $gera_titulo = new TEntry('gera_titulo');
        
               
        $bloco_quadra = new TEntry('bloco_quadra');

        $id->setSize('20%');
        $descricao->setSize('50%');
        $gera_titulo->setSize(50);
        $id->setSize(100);
        $descricao->setSize('100%');

        // add the fields
        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Descrição:')],[$descricao]);
        //$this->form->addFields([new TLabel('Condominio:')],[$condominio_id],[new TLabel('Bloco/Quadra:')],[$bloco_quadra]);
        $this->form->addFields([new TLabel('Bloco/Quadra:')],[$bloco_quadra]);
        
        //if ($nivel_acesso == '2' or $nivel_acesso == '3' or $nivel_acesso == '4') {
          //  $condominio_id->SetValue($user->condominio_id);
            ////throw new Exception(_t('Not logged'));
        //}


        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        $this->form->addAction('Cadastrar', new TAction(['UnidadeForm', 'onClear']), 'fa:plus #69aa46');
        
        // desabilitei para os administrativos não fazerm -------- 27/12/2019  $this->form->addAction('Configura Acessos', new TAction(array($this, 'onTodosAcesso')), 'fa:address-card green');
        $this->form->addAction('Rel. Unidades', new TAction(array($this, 'onRelatorioUnidades')), 'fa:print green');
        $this->form->addAction('Rel. Pagamentos', new TAction(array($this, 'onRelPagUnidades')), 'fa:print orange');
        $this->form->addAction('E-mails', new TAction(array($this, 'onListaEmails')), 'fa:file green');
        $this->form->addAction('Exporta CSV', new TAction(array($this, 'onExportCSV')), 'fa:file green');
        $this->form->addAction('Adimplentes', new TAction(array($this, 'onAdimplentes')), 'fa:file green');
        $this->form->addAction('uCondo', new TAction(array($this, 'onuCondo')), 'fa:file green');
             
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        // $this->datagrid->datatable = 'true';
        
        //if ($nivel_acesso == '2' or $nivel_acesso == '3' or $nivel_acesso == '4') {
        //  $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Morador'.'</b><br>' . '{morador_nome}' 
        //    . '<br><b> <i class="fa fa-envelope "> {proprietario_email} </i> </b><br>'
        //  );
        //}else {
          $this->datagrid->enablePopover('Informações Complementares', '<b>'
            .'Morador'.'</b><br>' . '{morador_nome}' 
            . '<br><b>'.'Envio Boleto'.'</b><br>' . '{tipo_envio_boleto}'
            . '<br><b> <i class="fa fa-envelope "> {proprietario_email} </i> </b><br>'//'<br><b>'.'Email'.'</b><br>' . '{proprietario_email}'
            . ' <i class="fa fa-mobile"> {proprietario_telefones} </i>'
            . ' <i class="fa fa-address-card-o"> {proprietario_cpfcnpj} </i>'
            );

         ////' <i class="fa fa-calendar green"> ' . $string->formatDateBR($object->dt_vencimento) . '</i>';
       // }
               
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', '50');
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco Quadra', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Unidade', 'center');
        $column_condominio_id = new TDataGridColumn('condominio_resumo', 'Condomínio', 'center');
        $column_proprietario_id = new TDataGridColumn('proprietario_nome', 'Proprietário', 'right');;
        $column_gera = new TDataGridColumn('gera_titulo', 'Boleto', 'center');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_proprietario_id);
        $this->datagrid->addColumn($column_gera);
        
        $column_gera->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');            
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel('Emite Boleto');
        $action_onoff->setImage('fa:power-off fa-lg orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
           
        // create EDIT action
        $action_edit = new TDataGridAction(array('UnidadeForm', 'onEdit'));
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        //$action_edit->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        //$action_del->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Verifica Acesso action
        //$action_veracesso = new TDataGridAction(array($this, 'onVerificaAcesso'));
        ////$action_veracesso->setUseButton(TRUE);
        ////$action_veracesso->setButtonClass('btn btn-default');
        //$action_veracesso->setLabel('Vertifica Acesso');
        //$action_veracesso->setImage('fa:address-card blue');
        //$action_veracesso->setField('id');
        //$this->datagrid->addAction($action_veracesso);
        //$action_veracesso->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Envia Senha action
        //$action_enviasenha = new TDataGridAction(array($this, 'onCadEnviaSenha'));
        ////$action_enviasenha->setUseButton(TRUE);
        ////$action_enviasenha->setButtonClass('btn btn-default');
        //$action_enviasenha->setLabel('Cadastra e Envia Senha');
        //$action_enviasenha->setImage('fa:envelope-open blue');
        //$action_enviasenha->setField('id');
        //$this->datagrid->addAction($action_enviasenha);
        //$action_enviasenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
        
        // create Reset Senha action
        //$action_resetsenha = new TDataGridAction(array($this, 'onResetSenha'));
        ////$action_resetsenha->setUseButton(TRUE);
        ////$action_resetsenha->setButtonClass('btn btn-default');
        //$action_resetsenha->setLabel('Reset da Senha de Acesso');
        //$action_resetsenha->setImage('fa:refresh blue');
        //$action_resetsenha->setField('id');
        //$this->datagrid->addAction($action_resetsenha);
        //$action_resetsenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
         
        // create Reset Senha action
        $action_resetsenha = new TDataGridAction(array($this, 'onFinanceiro'));
        //$action_resetsenha->setUseButton(TRUE);
        //$action_resetsenha->setButtonClass('btn btn-default');
        $action_resetsenha->setLabel('Financeiro');
        $action_resetsenha->setImage('fa:building green');
        $action_resetsenha->setField('id');
        $this->datagrid->addAction($action_resetsenha);
        //$action_resetsenha->setDisplayCondition( array($this, 'StatusBotaoColumn') );
                
        // create the datagrid model
        $this->datagrid->createModel();
        
        $this->datagrid->disableDefaultClick();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        //contador
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
     
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
        
      }
      
      
    public function onAdimplentes($param)
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
            t1.telefone1 as proprietario_telefone1,
            t1.telefone2 as proprietario_telefone2,
            t1.telefone3 as proprietario_telefone3,
            t2.nome as morador_nome,
            t2.email as morador_email,
            t2.rg as morador_rg,
            t2.telefone1 as morador_telefone1,
            t2.telefone2 as  morador_telefone2,
            t2.telefone3 as morador_telefone3,
            t3.resumo as condominio_resumo,
            t3.nome as condominio_nome 
            FROM unidade t0 
            INNER JOIN pessoa t1 on t0.proprietario_id = t1.id 
            INNER JOIN pessoa t2 on t0.morador_id = t2.id
            INNER JOIN condominio t3 on t0.condominio_id = t3.id 
            where t0.condominio_id = " . TSession::getValue('id_condominio');
  
                        
            $sql = $sql . " order by t0.bloco_quadra, t0.descricao";
            
            $objects = $conn->query($sql);
            
            $format  = 'xls';
            
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
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
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
                
                $condominio = new Condominio(TSession::getValue('id_condominio'));
                
                //cabecalho
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                
                // add a header row
                $tr->addRow();
                $tr->addCell('Unidades Adimplentes', 'center', 'header', $colunas);
                
               
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
                
                $qtd_unidades = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                    
                    $repo = new TRepository('VwContasreceberlistcobranca');
                    $criteria1 = new TCriteria;
                    $criteria1->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
                    $criteria1->add(new TFilter('id', '=', $object['id']));
                    $objetos = $repo->load($criteria1);
                    $debito = 0;

                    foreach ($objetos as $objeto)
                    {
                        $debito = $objeto->valor;
                    }
                    
                    if ($debito <= 0) {
                        $style = $colour ? 'datap' : 'datai';
                        $tr->addRow();
                        $tr->addCell($object['id'], 'right', $style);
                    
                        //bloco/lote + unidade
                        $tr->addCell($object['bloco_quadra'] . '-' . $object['descricao'], 'center', $style);
                    
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
                        
                            $pessoa = new Pessoa($object['proprietario_id']);
                        
                            if ($pessoa->pessoa_fisica_juridica == 'F') {
                                $tr->addCell($pessoa->cpf, 'center', $style);
                            } else {
                                $tr->addCell($pessoa->cnpj, 'center', $style);
                                                        
                            }
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
                      
                          if ($nivel_acesso == '3' or $nivel_acesso == '4') { // morador e portaria
                            $tr->addCell('suprimido', 'center', $style);
                            $tr->addCell('suprimido', 'center', $style);
                          } else {
                            $tr->addCell($object['morador_rg'], 'center', $style);
                        
                            $morador = new Pessoa($object['morador_id']);
                        
                           // var_dump($morador->pessoa_fisica_juridica);
                        
                            if ($morador->pessoa_fisica_juridica == 'F') {
                                $tr->addCell($morador->cpf, 'center', $style);
                            } else {
                                $tr->addCell($morador->cnpj, 'center', $style);
                                                        
                            }
                        
                            //$tr->addCell($object['morador_cpf'], 'center', $style);
                          }
                      
                          $tr->addCell($object['morador_telefone1'], 'center', $style); 
                          $tr->addCell($object['morador_telefone2'], 'center', $style); 
                          $tr->addCell($object['morador_telefone3'], 'center', $style); 
                          $tr->addCell('', 'left', $style);                     
                        }
                    
                        $colour = !$colour;
                    
                        $qtd_unidades++;
                    }
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total de Unidades : ' . $qtd_unidades, 'center', 'footer', $colunas);
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s A'), 'center', 'footer', $colunas);
                
                // stores the file
                if (!file_exists("app/output/UnidadesAdimplentes.{$format}") OR is_writable("app/output/UnidadesAdimplentes.{$format}"))
                {
                    $tr->save("app/output/UnidadesAdimplentes.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/UnidadesAdimplentes.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/UnidadesAdimplentes.{$format}");
                
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
    
    
    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $user = Unidade::find($param['id']);

            if ($user instanceof Unidade)
            {
                $user->gera_titulo = $user->gera_titulo == 'Y' ? 'N' : 'Y';
                $user->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
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
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            
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
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
         
            //$csv = '"Title","First Name","Middle Name","Last Name","Suffix","Given Name Yomi","Family Name Yomi","Home Street","Home City","Home State","Home Postal Code","Home Country","Company","Department","Job Title","Office Location","Business Street","Business City","Business State","Business Postal Code","Business Country","Other Street","Other City","Other State","Other Postal Code","Other Country","Assistant s Phone","Business Fax","Business Phone","Business Phone 2","Callback","Car Phone","Company Main Phone","Home Fax","Home Phone","Home Phone 2","ISDN","Mobile Phone","Other Fax","Other Phone","Pager","Primary Phone","Radio Phone","TTY/TDD Phone","Telex","Anniversary","Birthday","E-mail Address","E-mail Type","E-mail 2 Address","E-mail 2 Type","E-mail 3 Address","E-mail 3 Type","Notes","Spouse","Web Page"'."\n";
            $csv = '"First Name","Mobile Phone","Assistant s Phone","Primary Phone","E-mail Address"'."\n";
            
            //var_dump($criteria);
                       
            // load the objects according to criteria
            $customers = $repository->load($criteria);
            
            if ($customers)
            {
                foreach ($customers as $customer)
                {
                    $pessoa = new Pessoa($customer->proprietario_id);
                    
                    $csv .= '"' . $customer->proprietario_nome . '(' . $customer->descricao . ') - ' . 
                                    $customer->condominio_resumo .
                                    '"'.
                        ','.
                        $pessoa->telefone1.
                        ','.
                        $pessoa->telefone2.
                        ','.
                        $pessoa->telefone3.
                        ',"'.
                        $pessoa->email.'"'."\n";
                        
                    //var_dump($customer->proprietario_telefones);
                   
                    //$telefones = explode("/", $customer->proprietario_telefones); 
                    
                    //if (!isset($telefones[0])) {
                    //  $telefones[0] = '000000000';
                    //}
                    
                    //if (!isset($telefones[1])) {
                    //  $telefones[1] = '000000000';
                    //}
                    
                   // if (!isset($telefones[2])) {
                   //   $telefones[2] = '000000000';
                   // }
                    
                    //if ( $customer->proprietario_email != 'teste@teste.com.br' ) {
                        //,"Jorge",,"Dantas",,,,,"Maceió",,,"Brasil",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,"30/08/1904","jorge.silvadantas@hotmail.com","SMTP",,,,,,,
                        //$csv .= ',"' . $customer->proprietario_nome . '(' . $customer->descricao . ') - ' . 
                       //             $customer->condominio_resumo .
                       //             '"'.
                       // ',,,,,,,,,,,,,,,,,,,,,,,,,,,'.
                      //  $customer->telefone1.
                      //  ','.
                      //  $customer->telefone2.
                      //  ',,,,,,,,'.
                      //  $customer->telefone3.',,,,,,,,,,"'.
                      //  $customer->proprietario_email.'",,,,,,,'."\n";
                    //}
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
                TButton::disableField('form_search_Unidade', 'btn_cadastrar');
                TButton::disableField('form_search_Unidade', 'btn_configura_acessos');
                
                TButton::disableField('form_search_Unidade', 'btn_exporta_csv');
                TButton::disableField('form_search_Unidade', 'btn_e-mails');
                
                TButton::disableField('form_search_Unidade', 'btn_relatório');
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
                       
            // inclui novos filtros
            $filtro = ' ';
            
            //var_dump($formdata->mes_ref);
            if ($param['bloco_quadra'])
            {
                $filtro = $filtro . ' and bloco_quadra = "' . $param['bloco_quadra'] .'"';
                
            }
            
            if ($param['id'])
            {
                $filtro = $filtro . ' and id = "' . $param['id'] .'"';
                
            }
            
            if ($param['descricao'])
            {
                $filtro = $filtro . ' and descricao = "' . $param['descricao'] .'"';
                
            }
            
            
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
            t1.endereco as proprietario_endereco,
            t1.numero as proprietario_numero,
            t1.bairro as proprietario_bairro,
            t1.cidade as proprietario_cidade,
            t1.estado as proprietario_estado,
            t1.cep as proprietario_cep,
            t1.rg as proprietario_rg,
            t1.telefone1 as proprietario_telefone1,
            t1.telefone2 as proprietario_telefone2,
            t1.telefone3 as proprietario_telefone3,
            t2.nome as morador_nome,
            t2.email as morador_email,
            t2.rg as morador_rg,
            t2.telefone1 as morador_telefone1,
            t2.telefone2 as  morador_telefone2,
            t2.telefone3 as morador_telefone3,
            t3.resumo as condominio_resumo,
            t3.nome as condominio_nome 
            FROM unidade t0 
            INNER JOIN pessoa t1 on t0.proprietario_id = t1.id 
            INNER JOIN pessoa t2 on t0.morador_id = t2.id
            INNER JOIN condominio t3 on t0.condominio_id = t3.id 
            where t0.condominio_id = " . TSession::getValue('id_condominio');
            
            if ($filtro) {
                $sql = $sql . " " . $filtro;
            }
                        
            $sql = $sql . " order by t0.bloco_quadra, t0.descricao";
            
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
                
                $qtd_unidades = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object['id'], 'right', $style);
                    
                    //bloco/lote + unidade
                    $tr->addCell($object['bloco_quadra'] . '-' . $object['descricao'], 'center', $style);
                    
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
                        
                        $pessoa = new Pessoa($object['proprietario_id']);
                        
                        if ($pessoa->pessoa_fisica_juridica == 'F') {
                            $tr->addCell($pessoa->cpf, 'center', $style);
                        } else {
                            $tr->addCell($pessoa->cnpj, 'center', $style);
                                                        
                        }
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
                    
                    $tr->addRow();
                    $tr->addCell('', 'left', $style);
                    $tr->addCell('Endereço', 'center', $style);
                    $end = $object['proprietario_endereco'] . ', ' . $object['proprietario_numero'] . ', ' . $object['proprietario_bairro'] . ', ' . $object['proprietario_cidade'] . ', ' . $object['proprietario_estado'] . ', ' . $object['proprietario_cep'];  
                    $tr->addCell($end, 'left', $style, 8);
                                          
                    if ( $object['proprietario_nome'] != $object['morador_nome'] )
                    {
                      $colour = !$colour;
                      $style = $colour ? 'datap' : 'datai';
                      $tr->addRow();
                      $tr->addCell('', 'left', $style);
                      $tr->addCell('Inquilino', 'center', $style);
                      $tr->addCell($object['morador_nome'], 'left', $style);
                      $tr->addCell($object['morador_email'], 'left', $style);
                      
                      if ($nivel_acesso == '3' or $nivel_acesso == '4') { // morador e portaria
                        $tr->addCell('suprimido', 'center', $style);
                        $tr->addCell('suprimido', 'center', $style);
                      } else {
                        $tr->addCell($object['morador_rg'], 'center', $style);
                        
                        $morador = new Pessoa($object['morador_id']);
                        
                       // var_dump($morador->pessoa_fisica_juridica);
                        
                        if ($morador->pessoa_fisica_juridica == 'F') {
                            $tr->addCell($morador->cpf, 'center', $style);
                        } else {
                            $tr->addCell($morador->cnpj, 'center', $style);
                                                        
                        }
                        
                        //$tr->addCell($object['morador_cpf'], 'center', $style);
                      }
                      
                      $tr->addCell($object['morador_telefone1'], 'center', $style); 
                      $tr->addCell($object['morador_telefone2'], 'center', $style); 
                      $tr->addCell($object['morador_telefone3'], 'center', $style); 
                      $tr->addCell('', 'left', $style);                     
                    }
                    
                    $colour = !$colour;
                    
                    $qtd_unidades++;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total de Unidades : ' . $qtd_unidades, 'center', 'footer', $colunas);
                
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
    
    function onuCondo($param)
    {
        try
        {
                       
            // inclui novos filtros
            $filtro = ' ';
            
            //var_dump($formdata->mes_ref);
            if ($param['bloco_quadra'])
            {
                $filtro = $filtro . ' and bloco_quadra = "' . $param['bloco_quadra'] .'"';
                
            }
            
            if ($param['id'])
            {
                $filtro = $filtro . ' and id = "' . $param['id'] .'"';
                
            }
            
            if ($param['descricao'])
            {
                $filtro = $filtro . ' and descricao = "' . $param['descricao'] .'"';
                
            }
            
            
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
            t1.endereco as proprietario_endereco,
            t1.numero as proprietario_numero,
            t1.bairro as proprietario_bairro,
            t1.cidade as proprietario_cidade,
            t1.estado as proprietario_estado,
            t1.cep as proprietario_cep,
            t1.rg as proprietario_rg,
            t1.telefone1 as proprietario_telefone1,
            t1.telefone2 as proprietario_telefone2,
            t1.telefone3 as proprietario_telefone3,
            t2.nome as morador_nome,
            t2.email as morador_email,
            t2.rg as morador_rg,
            t2.telefone1 as morador_telefone1,
            t2.telefone2 as  morador_telefone2,
            t2.telefone3 as morador_telefone3,
            t3.resumo as condominio_resumo,
            t3.nome as condominio_nome 
            FROM unidade t0 
            INNER JOIN pessoa t1 on t0.proprietario_id = t1.id 
            INNER JOIN pessoa t2 on t0.morador_id = t2.id
            INNER JOIN condominio t3 on t0.condominio_id = t3.id 
            where t0.condominio_id = " . TSession::getValue('id_condominio');
            
            if ($filtro) {
                $sql = $sql . " " . $filtro;
            }
                        
            $sql = $sql . " order by t0.bloco_quadra, t0.descricao";
            
            $objects = $conn->query($sql);
            
            $format  = 'xls';
            
            $string = new StringsUtil;
            
            //var_dump($objects);
            //return;
            
            if ($objects)
            {
                // largura das colunas
                $widths = array(30, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60);
                
                
                $tr = new TTableWriterXLS($widths);
                
               
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '10', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '9', 'I',  '#000000', '#A3A3A3');
                
                //$resumo = condominio_resumo;
                
                // qtd colunas
                $colunas = 23;
                
                $condominio = new Condominio($param['condominio_id']);
                
                //cabecalho
                //$tr->addRow();
                //$tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                
                // add a header row
                //$tr->addRow();
                //$tr->addCell('Unidades', 'center', 'header', $colunas);
                
               
                // add titles row
                //$tr->addRow();
                //$tr->addCell('Id', 'left', 'title');
                //$tr->addCell('Unidade', 'center', 'title');
                //$tr->addCell('Proprietario', 'left', 'title');
                //$tr->addCell('Email', 'left', 'title');
                //$tr->addCell('RG', 'left', 'title');
                //$tr->addCell('CPF / CNPJ', 'left', 'title');
                //$tr->addCell('Telefone', 'left', 'title');
                //$tr->addCell('Telefone', 'left', 'title');
                //$tr->addCell('Telefone', 'left', 'title');
                //$tr->addCell('Envio', 'left', 'title'); 
                
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
                
                $qtd_unidades = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell('  ', 'right', $style);
                    
                    //bloco/lote + unidade
                    $tr->addCell($object['bloco_quadra'], 'center', $style);
                    $tr->addCell($object['descricao'], 'center', $style);
                    
                    $pessoa = new Pessoa($object['proprietario_id']);
                    
                    if ($pessoa->pessoa_fisica_juridica == 'F') {
                        $tr->addCell("'" . $pessoa->cpf, 'center', $style);
                    } else {
                        $tr->addCell("'" . $pessoa->cnpj, 'center', $style);
                                                        
                    }
                        
                    $tr->addCell($object['proprietario_nome'], 'center', $style);
                    $tr->addCell('82', 'center', $style);
                    $tr->addCell($object['proprietario_telefone1'], 'center', $style);
                    $tr->addCell('', 'center', $style);
                    $tr->addCell('', 'center', $style);
                    $tr->addCell('', 'center', $style);
                    $tr->addCell('', 'center', $style);
                    $tr->addCell($object['proprietario_email'], 'left', $style);
                    $tr->addCell($object['proprietario_cep'], 'left', $style);
                    $tr->addCell($object['proprietario_endereco'], 'left', $style);    
                    $tr->addCell($object['proprietario_numero'], 'left', $style);    
                    $tr->addCell('', 'center', $style);
                    $tr->addCell($object['proprietario_bairro'], 'left', $style);  
                    $tr->addCell($object['proprietario_cidade'], 'left', $style);  
                    $tr->addCell($object['proprietario_estado'], 'left', $style);  
                    $tr->addCell('Sim', 'center', $style);
                    $tr->addCell('Sim', 'center', $style);
                    $tr->addCell('Não', 'center', $style);
                    $tr->addCell('Sim', 'center', $style);                    
                    
                    
                    $colour = !$colour;
                    
                    $qtd_unidades++;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total de Unidades : ' . $qtd_unidades, 'center', 'footer', $colunas);
                
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
     
        $id_condominio = TSession::getValue('id_condominio');
        
        //TTransaction::open('permission');
        //verificar se existe alguem já configurado se continuar, reseta todas as senhas
        //$conn2 = TTransaction::get();
        //$result2 = $conn2->query("SELECT * FROM system_user where system_unit_id='{$id_condominio}'");
        //if ($result2)
        //{
        //    new TMessage('info', 'Existe(m) usuário(s) configurado(s), somente os não cadastrados serão configurados !');
            
        //}
        //TTransaction::close();
        
        TTransaction::open('facilitasmart');
        
        $condominio = new Condominio($id_condominio); 
        
        //SystemUserProgram::where('system_user_id', '=', $this->id)->delete();
                
        new TMessage('info', 'Configuração de acesso as unidades do Condomínio : ' . $condominio->resumo);
        
        // exclui em usuario_condominio todas as configurações do condomínio
        // está excluindo abaixo UsuarioCondominio::where('condominio_id', '=', $param['condominio_id'])->delete();
        
        $conn = TTransaction::get();
        //$colunas = $conn->query("select * from unidade where condominio_id = ".$condominio->id. " group by proprietario_id");
        $colunas = $conn->query("select * from unidade where condominio_id = ".$condominio->id);
        
        TTransaction::close();
        
        foreach ($colunas as $row)
        {
            TTransaction::open('facilitasmart');
            
            // verifica se a unidade já está cadastrada para ter acesso ao portal
            $proprietario = new Pessoa($row['proprietario_id']);
            
            //if ($proprietario->email !='teste@teste.com.br') {  
              
              // exclui o usuairo e o usuairo_imovel para o email da pessoa, para posterior cadastro dos novos dados
              //$conn1 = TTransaction::get();
              //$result1 = $conn1->query("SELECT * FROM usuario_condominio where system_user_login='{$proprietario->email}'");
              //foreach ($result1 as $row1)
              //{
              //  $usuario_condominio = new UsuarioCondominio($row1['id']); 
              //  $usuario_condominio->delete();
              //}                        
        
            TTransaction::close();
              
              TTransaction::open('permission');
                   
              // deleto o usuario           
              $conn2 = TTransaction::get();
              $result2 = $conn2->query("SELECT * FROM system_user where login='{$proprietario->cpf}'");
              
              foreach ($result2 as $row2)
              {
                //var_dump($row);
                $system_user = new SystemUser($row2['id']); 
                $system_user->delete();
              }
                        
              // cadastra o usuario e coloca ele no grupo para acesso ao portal como morador
              $object1 = new SystemUser; 
              $object1->name = $proprietario->nome;
              
              if ($proprietario->pessoa_fisica_juridica == 'F') {
                  $object1->login = $proprietario->cpf;
              } else {
                  $object1->login = $proprietario->cnpj;
              }
              
              //$object1->password = 'e10adc3949ba59abbe56e057f20f883e'; // 123456
              $object1->password = md5($proprietario->id);
              $object1->email = $proprietario->email;
              //$object1->frontpage_id = 80; // quadro de aviso - importante proque tem a configuracao de id_condominio
              $object1->frontpage_id = 10; //welcome
              $object1->system_unit_id = $id_condominio; // as unidades devem ter o mesmo id do cadastro de condominio
              $object1->active = 'Y';
              $object1->store(); 
              
              // adiciono ele ao grupo de morador
              $object2 = new SystemUserGroup;
              $object2->system_user_id = $object1->id;
              $object2->system_group_id = 4; // grupo morador = 4
              $object2->store(); 
              
              // atribui uma unidade 
              $object2 = new SystemUserUnit;
              $object2->system_user_id = $object1->id;
              $object2->system_unit_id = $id_condominio; // as unidades devem ter o mesmo id do cadastro de condominio
              $object2->store(); 
              
              TTransaction::close();
              
              // adiciona usuario_condominio
              //$object3 = new UsuarioCondominio;
              //$object3->ativo = 'Y';
              //$object3->pessoa_id = $proprietario->id;
              //$object3->system_user_login = $object1->login;
              //$object3->condominio_id = $row['condominio_id'];
              //$object3->unidade_id = $row['id'];
              //$object3->dt_envio_senha = date('Y-m-d');
              //$object3->nivel_acesso_inf = '4';
              //$object3->store(); 
               
              // enviar o email com a senha dele 
              //$this->EnviaEmailSenha($row);
              
              
           //}
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
        TSession::setValue('UnidadeList_filter_bloco_quadra',   NULL);
        TSession::setValue('UnidadeList_filter_morador_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('UnidadeList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('UnidadeList_filter_descricao',   $filter); // stores the filter in the session
        }

                    
        if (isset($data->bloco_quadra) AND ($data->bloco_quadra)) {
            $filter = new TFilter('bloco_quadra', '=', "{$data->bloco_quadra}"); // create the filter
            TSession::setValue('UnidadeList_filter_bloco_quadra',   $filter); // stores the filter in the session
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
            $limit = 20;
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
            

            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
 
        
            if (TSession::getValue('UnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_descricao')); // add the session filter
            }

            if (TSession::getValue('UnidadeList_filter_bloco_quadra')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_bloco_quadra')); // add the session filter
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
            
            // verifica se existe lancamento em contas a receber
            if (ContasReceber::where('unidade_id','=',$param['key'])->count() > 0) {
                new TMessage('info', 'Existe lançamento de título(s) no Contas a Receber, exclusão não permitida.');
                TTransaction::close(); // close the transaction
                return;    
            } 
            
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
            TTransaction::close(); // close the transaction
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
            //$email1 = $usuario->email;
            //var_dump($usuario->email);
            $email1 = 'jrmaceio09@gmail.com';
           
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
            $row->addCell('seu Id de cadastro que pode ser consultado no último boleto');
           
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
             TTransaction::close();
             TTransaction::rollback(); // undo all pending operations
           }
                        
           TTransaction::close(); // close the transaction    
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::close();
        }
        
    }

    function onRelPagUnidades($param)
    {
        try
        {
                        
            // get the search form data
            $data_form = $this->form->getData();
        
            // inclui novos filtros
            $filtro = ' ';
            
            //var_dump($formdata->mes_ref);
            if ($param['bloco_quadra'])
            {
                $filtro = $filtro . ' and bloco_quadra = "' . $param['bloco_quadra'] .'"';
                
            }
            
            if ($param['id'])
            {
                $filtro = $filtro . ' and id = "' . $param['id'] .'"';
                
            }
            
            if ($param['descricao'])
            {
                $filtro = $filtro . ' and descricao = "' . $param['descricao'] .'"';
                
            }
            
            
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
            where t0.condominio_id = " . TSession::getValue('id_condominio');
            
            if ($filtro) {
                $sql = $sql . " " . $filtro;
            }
                        
            $sql = $sql . " order by t0.descricao";
            
            $objects = $conn->query($sql);
            
            $format  = 'pdf';
            
            $string = new StringsUtil;
            
            //var_dump($objects);
            //return;
            
            if ($objects)
            {
                // largura das colunas
                // id, unidade, proprietario,
                $widths = array(30, 60, 160,
                                 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60);
                
                
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
                $colunas = 15;
                
                $condominio = new Condominio($param['condominio_id']);
                
                //cabecalho
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                
                // add a header row
                $tr->addRow();
                $tr->addCell('Relatório de Pagamentos por Data de Liquidação - Unidade e Mês', 'center', 'header', $colunas);
                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Proprietario', 'left', 'title');
                
                $tr->addCell('Jan', 'right', 'title');
                $tr->addCell('Fev', 'right', 'title');
                $tr->addCell('Mar', 'right', 'title');
                $tr->addCell('Abr', 'right', 'title');
                $tr->addCell('Mai', 'right', 'title');
                $tr->addCell('Jun', 'right', 'title');
                $tr->addCell('Jul', 'right', 'title');
                $tr->addCell('Ago', 'right', 'title');
                $tr->addCell('Set', 'right', 'title');
                $tr->addCell('Out', 'right', 'title');
                $tr->addCell('Nov', 'right', 'title');
                $tr->addCell('Dez', 'right', 'title'); 
                
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
                
                $qtd_unidades = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object['id'], 'right', $style);
                    //bloco/lote + unidade
                    $tr->addCell($object['bloco_quadra'] . '-' . $object['descricao'], 'center', $style);
                    
                    if ( $object['proprietario_nome'] == 'UNIDADE VAZIA / CADASTRO INCOMPLETO' )
                    {
                      $tr->addCell('', 'left', $style);
                    }
                    else
                    {
                      $tr->addCell($object['proprietario_nome'], 'left', $style);
                    }
                    
                    // pega o valor pago da unidade
                    
                    $meses = [ [1,0], [2, 0], [3, 0], [4, 0], [5, 0], [6, 0], [7, 0], 
                    [8, 0], [9, 0], [10, 0], [11, 0], [12, 0] ];
            
                    $data = array();
                    
                    $receitas_mes = ContasReceber::getReceitaAnaliticaMesUnidade( date('Y'), TSession::getValue('id_condominio'), $object['id']);
                    
                    foreach ($receitas_mes as $mes => $valor)
                    {
                        $meses[((int)$mes)-1][1] = (float) $valor;
                
                    }

                    $tr->addCell(number_format($meses[0][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[1][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[2][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[3][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[4][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[5][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[6][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[7][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[8][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[9][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[10][1], 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($meses[11][1], 2, ',', '.'), 'right', $style);
                     
                    
                    $colour = !$colour;
                    
                    $qtd_unidades++;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total de Unidades : ' . $qtd_unidades, 'center', 'footer', $colunas);
                
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
            
            // fill the form with data again
            $this->form->setData($data_form);
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }

}
