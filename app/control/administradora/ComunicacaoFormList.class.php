<?php
/**
 * ComunicacaoFormList Form List
 * @author  <your name here>
 */
class ComunicacaoFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    private $string;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_QuadroAvisos');
        $this->form->setFormTitle( 'Quadro de Avisos' );
        
        $this->string = new StringsUtil;

        // create the form fields
        $id = new TEntry('id');
        $data_lancamento = new TDate('data_lancamento');
        $tipo = new TCombo('tipo');
        $status = new TEntry('status');
        $titulo = new TEntry('titulo');
        $conteudo = new TText('conteudo');
        $rodape = new TEntry('rodape');
        
        $tipos = array(1 => 'Aviso' , 2 => 'Serviços', 3 => 'Colaboradores');         
        $tipo->addItems($tipos);
        $tipo->setDefaultOption(FALSE);
        $tipo->setSize(200);  

        
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
                $criteria = new TCriteria;
                $criteria->add(new TFilter('id', '=', $user->condominio_id));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
            }else {
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
            } 
            
        }
        TTransaction::close();


        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel('Condominio')], [$condominio_id]);
        $this->form->addFields( [new TLabel('Tipo Comunicação')], [$tipo]);
        $this->form->addFields( [new TLabel('Data')], [$data_lancamento]);
        $this->form->addFields( [new TLabel('Título')], [$titulo]);
        $this->form->addFields( [new TLabel('Conteúdo')], [$conteudo]);
        $this->form->addFields( [new TLabel('Rodapé')], [$rodape]);
        
        //$this->form->addQuickFields('Status : (Y - Ativo, N-Inativo)', array(new TLabel('Tipo:1-Aviso, 2-Serviços, 3-Colaboradores')));

        // define the sizes -  O addquickfield bota um tamanho default. Chama o setsize depois
        $id->setSize(50);
        $data_lancamento->setSize(200);
        $status->setSize(50);
        $titulo->setSize(400);
        $conteudo->setSize(600, 180);
        $rodape->setSize(400);
        $condominio_id->setSize(300);
        
        $id->setEditable(FALSE); 
        
        // mascaras
        $data_lancamento->setMask('dd/mm/yyyy');

        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $this->form->addAction(_t('Save'),  new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $this->form->addAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
         
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        $this->datagrid->enablePopover('Informações', '<b>'.'Conteúdo'.'</b><br>' . '{conteudo}' . '<br><b>'.'Informação'.'</b><br>' . '{rodape}');
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_data = new TDataGridColumn('data_lancamento', 'Data', 'left');
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');
        $column_titulo = new TDataGridColumn('titulo', 'Título', 'left');
        //$column_conteudo = new TDataGridColumn('conteudo', 'Conteúdo', 'left');
        //$column_rodape = new TDataGridColumn('rodape', 'Rodapé', 'left');
        $column_condominio = new TDataGridColumn('condominio_id', 'Condomínio', 'left');
        $column_status = new TDataGridColumn('status', 'Ativo', 'left');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_data);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_titulo);
        //$this->datagrid->addColumn($column_conteudo);
        //$this->datagrid->addColumn($column_rodape);
        $this->datagrid->addColumn($column_condominio);
        $this->datagrid->addColumn($column_status);
        
        $column_status->setTransformer( function($value, $object, $row) {
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
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off fa-lg orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
                
        // creates two datagrid actions
        $action1 = new TDataGridAction(array($this, 'onEdit'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        //$action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setUseButton(TRUE);
        $action2->setButtonClass('btn btn-default');
        //$action2->setLabel(_t('Delete'));
        $action2->setImage('fa:trash-o red fa-lg');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
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
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }

    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $comunicacao = Comunicacao::find($param['id']);
            
            if ($comunicacao instanceof Comunicacao)
            {
                $comunicacao->status = $comunicacao->status == 'Y' ? 'N' : 'Y';
                $comunicacao->store();
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


    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Comunicacao
            $repository = new TRepository('Comunicacao');
            $limit = 10;
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
            
            if (TSession::getValue('Comunicacao_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Comunicacao_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $object->data_lancamento ? $object->data_lancamento = $this->string->formatDateBR($object->data_lancamento) : null;
                    
                    $condominio = new Condominio($object->condominio_id);
                    $object->condominio_id = $condominio->resumo;
                    
                    if ($object->tipo == '1') {
                        $object->tipo = 'Aviso';
                    }

                    if ($object->tipo == '2') {
                        $object->tipo = 'Serviço';
                    }

                    if ($object->tipo == '3') {
                        $object->tipo = 'Colaborador';
                    }
                    
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
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
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
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
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
            $object = new Comunicacao($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
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
            
            $object = new Comunicacao;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            $object->data_lancamento ? $object->data_lancamento = $this->string->formatDate($object->data_lancamento) : null;
            
            $object->store(); // save the object
            
            $object->data_lancamento ? $object->data_lancamento = $this->string->formatDateBR($object->data_lancamento) : null;
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved')); // success message
            $this->onReload(); // reload the listing
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
                $object = new Comunicacao($key); // instantiates the Active Record
                
                $object->data_lancamento ? $object->data_lancamento = $this->string->formatDateBR($object->data_lancamento) : null;
                
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
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
