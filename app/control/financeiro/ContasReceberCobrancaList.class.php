<?php
/**
 * ContasReceberCobrancaList Listing
 * @author  <your name here>
 */
class ContasReceberCobrancaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ContasReceber');
        $this->form->setFormTitle('Cobrança');
        

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 'descricao','descricao',$criteria);



        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ], [ new TLabel('Mês Ref.') ], [ $mes_ref ] );
        $this->form->addFields( [ new TLabel('Classe Id') ], [ $classe_id ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );


        // set sizes
        $id->setSize('100%');
        $mes_ref->setSize('100%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['ContasReceberForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        //$column_classe_id = new TDataGridColumn('classe_id', 'Classe Id', 'right');
        //$column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade Id', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Dt Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situacao', 'left');
        $column_dt_primeira_cobranca = new TDataGridColumn('dt_primeira_cobranca', 'Dt 1a Cobranca', 'left');
        $column_tipo_primeira_cobranca = new TDataGridColumn('tipo_primeira_cobranca', 'Tipo 1a Cobranca', 'left');
        $column_dt_segunda_cobranca = new TDataGridColumn('dt_segunda_cobranca', 'Dt 2a Cobranca', 'left');
        $column_tipo_segunda_cobranca = new TDataGridColumn('tipo_segunda_cobranca', 'Tipo 2a Cobranca', 'left');
        $column_dt_terceira_cobranca = new TDataGridColumn('dt_terceira_cobranca', 'Dt 3a Cobranca', 'left');
        $column_tipo_terceira_cobranca = new TDataGridColumn('tipo_terceira_cobranca', 'Tipo 3a Cobranca', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        //$this->datagrid->addColumn($column_classe_id);
        //$this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_descricao);
        //$this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_dt_primeira_cobranca);
        $this->datagrid->addColumn($column_tipo_primeira_cobranca);
        $this->datagrid->addColumn($column_dt_segunda_cobranca);
        $this->datagrid->addColumn($column_tipo_segunda_cobranca);
        $this->datagrid->addColumn($column_dt_terceira_cobranca);
        $this->datagrid->addColumn($column_tipo_terceira_cobranca);

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        
        $column_valor->setTransformer( $format_value );
        
        // create EDIT action
        $action_edit = new TDataGridAction(array($this, 'onWhatsApp'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('WhatsApp'));
        $action_edit->setImage('far:clone green');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create E-Mail action
        $action_cobranca = new TDataGridAction(array($this, 'onEnviar'));
        ////$action_del->setUseButton(TRUE);
        ////$action_del->setButtonClass('btn btn-default');
        $action_cobranca->setLabel('Cobrança');
        $action_cobranca->setImage('far:clone blue');
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
    
    public static function onEnviar($param)
    {
        try
        {
            TTransaction::open('permission');
            $preferences = SystemPreference::getAllPreferences();
            TTransaction::close();
        
            TTransaction::open('facilitasmart');
            
            $titulo = ContasReceber::find($param['key']);
            
            $condominio = new Condominio($titulo->condominio_id);
            $unidade = new Unidade($titulo->unidade_id);
            $pessoa = new Pessoa($unidade->proprietario_id);
            
            $email_teste = 'jrmaceio09@gmail.com';
            
            
            $mail = new TMail;
            
            //$mail->setFrom( trim($preferences['mail_from']) , TSession::getValue('username'));
            $mail->setFrom( trim($email_teste) , TSession::getValue('username'));
            
            $mail->addAddress ($email_teste);
            
            $mail->setSubject(($condominio->resumo));
            
            if ($preferences['smtp_auth'])
            {
                $mail->SetUseSmtp();
                $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
                $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
            }
            
            $mail->setHtmlBody ("Olá ".($pessoa->nome).", "."sua taxa referente a competencia ".($titulo->mes_ref)." já está disponível."."<br><br>"."Clique no link para imprimir ".($titulo->pjbank_linkBoleto)."<br><br>"."Caso já tenha efetuado o pagamento desconsiderar essa mensagem."."<br><br>"."Att."."<br>".TSession::getValue('username')."<br>".($condominio->resumo));
             
            $mail->send();
            
            // shows the success message
            new TMessage('info', ('Boleto enviado para o e-mail do responsavel'));
            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
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
            $object = new ContasReceber($key); // instantiates the Active Record
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
        TSession::setValue('ContasReceberList_filter_id',   NULL);
        TSession::setValue('ContasReceberList_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberList_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberList_filter_unidade_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('ContasReceberList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', 'like', "%{$data->mes_ref}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_mes_ref',   $filter); // stores the filter in the session
        }


        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', 'like', "%{$data->classe_id}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_classe_id',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "$data->unidade_id"); // create the filter
            TSession::setValue('ContasReceberList_filter_unidade_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data', $data);
        
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
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'unidade_id, dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberList_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_unidade_id')); // add the session filter
            }

            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '0')); // add the session filter
            $criteria->add(new TFilter('dt_vencimento', '<', date('y-m-d'))); // add the session filter
            
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
     * Ask before whatsapp
     */
    public static function onWhatsApp($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'WhatsApp']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Enviando Whatsapp
     */
    public static function WhatsApp($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
         
         
            //TScript::create('window.open("https://api.whatsapp.com/send?1=pt_BR&phone=5582999943552","_blank")'); 
            
            //TScript::create('window.open("https://api.whatsapp.com/send?1=pt_BR&phone=5582999943552","_blank")'); 
            
            TScript::create('window.open("https://api.whatsapp.com/send?phone=5582999943552&text=mensagem texto 1","_blank")'); 
         
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
}
