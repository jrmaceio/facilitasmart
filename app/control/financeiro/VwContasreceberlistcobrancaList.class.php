<?php
/**
 * VwContasreceberlistcobrancaList Listing
 * @author  <your name here>
 */
class VwContasreceberlistcobrancaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_VwContasreceberlistcobranca');
        $this->form->setFormTitle('Cobrança Sem Correção');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);

        // add the fields
        $this->form->addFields( [ new TLabel('Unidade') ], [ $id ] );



        // set sizes
        $id->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('VwContasreceberlistcobranca_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Relatório XLS', new TAction(array($this, 'onProcessar')), 'fa:arrow-circle-o-right');

        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Unid. Id', 'right');
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco/Quadra', 'center');
        $column_descricao = new TDataGridColumn('unidade', 'Unidade', 'center');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cobrancas = new TDataGridColumn('cobrancas', 'Cobrança(s)', 'right');
        $column_valor = new TDataGridColumn('valor', 'Em Aberto (não atualizado)', 'right');
        

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_cobrancas);

        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        
        $column_valor->setTransformer( $format_value );
        
        // create EDIT action
        //$action_edit = new TDataGridAction(['VwContasreceberlistcobrancaForm', 'onEdit']);
        ////$action_edit->setUseButton(TRUE);
        ////$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        
        // create DELETE action
        //$action_del = new TDataGridAction(array($this, 'onDelete'));
        ////$action_del->setUseButton(TRUE);
        ////$action_del->setButtonClass('btn btn-default');
        //$action_del->setLabel(_t('Delete'));
        //$action_del->setImage('fa:trash-o red fa-lg');
        //$action_del->setField('id');
        //$this->datagrid->addAction($action_del);
        
        // create WhatsApp action
        $action_whats = new TDataGridAction(array($this, 'onWhatsApp'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_whats->setLabel('WhatsApp');
        //$action_whats->setImage('fa:whatsapp red');
        $action_whats->setField('id');
        $this->datagrid->addAction($action_whats);

        // create E-Mail action
        $action_cobranca = new TDataGridAction(array($this, 'onEnviar'));
        ////$action_del->setUseButton(TRUE);
        ////$action_del->setButtonClass('btn btn-default');
        $action_cobranca->setLabel('1a Cobrança E-Mail');
        //$action_cobranca->setImage('far:clone blue');
        $action_cobranca->setField('id');
        $this->datagrid->addAction($action_cobranca);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        // mostrar o mes ref e imovel selecionado
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
            $object = new VwContasreceberlistcobranca2($key); // instantiates the Active Record
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
        TSession::setValue('VwContasreceberlistcobrancaList_filter_id',   NULL);
        TSession::setValue('VwContasreceberlistcobrancaList_filter_descricao',   NULL);
        TSession::setValue('VwContasreceberlistcobrancaList_filter_nome',   NULL);
        TSession::setValue('VwContasreceberlistcobrancaList_filter_valor',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('VwContasreceberlistcobrancaList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade) AND ($data->unidade)) {
            $filter = new TFilter('unidade', '=', "%{$data->unidade}%"); // create the filter
            TSession::setValue('VwContasreceberlistcobrancaList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('VwContasreceberlistcobrancaList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->valor) AND ($data->valor)) {
            $filter = new TFilter('valor', 'like', "%{$data->valor}%"); // create the filter
            TSession::setValue('VwContasreceberlistcobrancaList_filter_valor',   $filter); // stores the filter in the session
        }

        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('VwContasreceberlistcobranca_filter_data', $data);
        
        $param = array();
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
            
            // creates a repository for VwContasreceberlistcobranca
            $repository = new TRepository('VwContasreceberlistcobranca2');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'bloco_quadra, unidade';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_id')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_descricao')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_nome')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_valor')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_valor')); // add the session filter
            }


            
            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
 
                    
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
     * 
    */
    public static function onWhatsApp($param)
    {
        $action = new TAction([__CLASS__, 'WhatsApp']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Enviar cobrança pelo WhatsApp ?', $action);
    }

    /**
     * 
    */
    public static function onEnviar($param)
    {
        $action = new TAction([__CLASS__, 'EMail']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Enviar 1a cobrança pelo E-mail ?', $action);

    }

    /**
     * 
    */
    public static function Email($param)
    {

        TTransaction::open('facilitasmart'); 
        $unidade_id = $param['id'];
        $unidade = new Unidade($unidade_id);
        $pessoa = new Pessoa($unidade->proprietario_id);
        TTransaction::close();

        // inicio enviando email
        TTransaction::open('permission'); 
        $preferences = SystemPreference::getAllPreferences();
        TTransaction::close();

        $mail = new TMail;
        $mail->setDebug(false);
        $mail->SMTPSecure = "ssl";

        $mail->setFrom( trim($preferences['mail_from']), 'FacilitaSmart' );
        $mail->addAddress( trim('jrmaceio09@gmail.com'), 'junior xxxxxxxxxx' );
        $mail->setSubject( 'Taxa de condomínio/manutenção em aberto' );
        if ($preferences['smtp_auth'])
        {
            $mail->SetUseSmtp();
            $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
            $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
        }
        $body2 = 'Prezado condômino,';

        $body = 'Prezado condômino,

        Encaminhamos este e-­mail para informar que consta em aberto em nossos registros, a sua taxa condominial. Efetue o pagamento do boleto utilizando o link de acesso.
        
        Lembramos que a sua contribuição permite efetuar as manutenções, manter a portaria e limpeza. A falta da contribuição desvaloriza o residencial e seu investimento.
        
        Para emitir uma segunda via de boleto ou verificar outras formas de pagamento, acesse nosso site e informe seus dados de acesso. 
        
        Link de acesso:

        https://facilitasmart.facilitahomeservice.com.br/index.php?class=SegundaViaBoletos
        
        Digite o CPF do proprietário cadastrado;
        Clique no ícone (código de barras) para baixar o boleto desejado.
        
        
        Você também pode solicitar a segunda ou fazer acordo via pelo Whatsapp  (82) 998171-3908 ou 98131-4213.
        
        Desejamos a você um ótimo dia.
        
        
        
        Atenciosamente,
        
        Central de Relacionamento com o Cliente.
        
        
        
        Esta mensagem pode conter informação confidencial e/ou privilegiada, sendo seu sigilo protegido por lei. Se você não for o destinatário ou a pessoa autorizada a receber esta mensagem, não use, copie ou divulgue as informações nela contidas ou tome qualquer ação baseada nessas informações. Caso tenha recebido esta mensagem por engano, por favor, avise imediatamente ao remetente, respondendo o e-mail e em seguida apague-a. Agradecemos sua cooperação.
         
        This message may contain confidential or privileged information and its confidentiality is protected by law. If you are not the addressed or authorized person to receive this message, you must not use, copy, disclose or take any action based on it or any information herein. If you have received this message by mistake, please advise the sender immediately by replying the e-mail and then deleting it. Thank you for your cooperation.';
        
        
        $mail->setTextBody($body2);    
        sleep(3);            
        $mail->send();

    }

    /**
     * 
    */
    public static function WhatsApp($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new VwContasreceberlistcobranca2($key, FALSE); // instantiates the Active Record
            
            //https://api.whatsapp.com/send?phone=seunumerodetelefone&text=sua%20mensagem
            // api com problema nos navegadores firefox e safira....................................
            
            $unidade = new Unidade($param['id']);
            $pessoa = new Pessoa($unidade->proprietario_id);
            
            //$pessoa->telefone1 = '82999943552'; // para teste
            
            //$novo_link = 'https://www.google.com.br';
            $mensagem = 'PREZADO CONDÔMINO, não detectamos o pagamento de R$ '. 
                number_format($object->valor, 2, ',', '.') . 
                '. Houve algum problema ? em que podemos lhe auxiliar ? Att. Administração.';
            $novo_link = 'https://api.whatsapp.com/send?phone=55'.$pessoa->telefone1.'&text='.$mensagem;
            
            TScript::create("var win = window.open('{$novo_link}', '_blank'); win.focus();");
 

            //TScript::create("var win = window.open('www.google.com.br', '_blank'); win.focus();");
            
                       
           
            //$object = new VwContasreceberlistcobranca($key, FALSE); // instantiates the Active Record
            //$object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            //$pos_action = new TAction([__CLASS__, 'onReload']);
            //new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new VwContasreceberlistcobranca2($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
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

    public function onProcessar($param)
    {       
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for VwContasreceberlistcobranca
            $repository = new TRepository('VwContasreceberlistcobranca2');
            $limit = 1000;
            // creates a criteria
            $criteria = new TCriteria;
            $param['order'] = 'bloco_quadra, unidade';
            $param['direction'] = 'asc';
            $criteria->setProperties($param); // order, offset

            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_id')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_id')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_descricao')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_nome')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('VwContasreceberlistcobrancaList_filter_valor')) {
                $criteria->add(TSession::getValue('VwContasreceberlistcobrancaList_filter_valor')); // add the session filter
            }
            
            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
 
                    
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $format  = 'xls';
            
            $string = new StringsUtil;

            if ($objects)
            {
                // largura das colunas
                $widths = array(30,50,140,100,50,50,60,60,60,30);
                
                
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
                $tr->addCell('Unidades Inadimplentes', 'center', 'header', $colunas);
                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Unid.Id', 'left', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Proprietario', 'left', 'title');
                $tr->addCell('Email', 'left', 'title');
                $tr->addCell('Valor Aberto', 'left', 'title');
                $tr->addCell('Qtd. Títulos', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                $tr->addCell('Envio', 'left', 'title'); 

                // controls the background filling
                $colour = FALSE;
                
                $qtd_unidades = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                    $unidade = new Unidade($object->id);
                    $pessoa = new Pessoa($unidade->proprietario_id);
                    //var_dump($object);
                    //var_dump($unidade);
                    //TTransaction::close();
                    //return;

                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    
                    //bloco/lote + unidade
                    $tr->addCell($unidade->bloco_quadra . '-' . $unidade->descricao, 'center', $style);
                    $tr->addCell($pessoa->nome, 'left', $style);
                    $tr->addCell($pessoa->email, 'left', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    $tr->addCell($object->cobrancas, 'center', $style);                       
                    $tr->addCell($pessoa->telefone1, 'left', $style);
                    $tr->addCell($pessoa->telefone2, 'left', $style);
                    $tr->addCell($pessoa->telefone3, 'left', $style);
                    
                    if ( $unidade->envio_boleto == 1 )
                    {
                      $tr->addCell('ND', 'center', $style);
                    } else if ( $unidade->envio_boleto == 2 )
                    {
                      $tr->addCell('Condom.', 'center', $style);
                    } else if ( $unidade->envio_boleto == 3 )
                    {
                      $tr->addCell('E-Mail', 'center', $style);
                    } else if ( $unidade->envio_boleto == 4 )
                    {
                      $tr->addCell('Correio', 'center', $style);
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
                if (!file_exists("app/output/UnidadesInad.{$format}") OR is_writable("app/output/UnidadesInad.{$format}"))
                {
                    $tr->save("app/output/UnidadesInad.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/UnidadesInad.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/UnidadesInad.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
            } else
            {
                new TMessage('error', 'No records found');
            } 
            
            TTransaction::close(); // close the transaction
               
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
        
    }





}


