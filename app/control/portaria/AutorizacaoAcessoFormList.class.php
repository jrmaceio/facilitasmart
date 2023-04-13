<?php
/**
 * AutorizacaoAcessoFormList Form List
 * @author  <your name here>
 */
class AutorizacaoAcessoFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_AutorizacaoAcesso');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('AutorizacaoAcesso');
        
        // create the form fields
        $id = new TEntry('id');
        
        //$unidade_id = new TEntry('unidade_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}','descricao',$criteria);
        
        
        $system_user_login = new TEntry('system_user_login');
        $nome = new TEntry('nome');
        $data_inicial = new TDate('data_inicial');
        $data_final = new TDate('data_final');
        $documento = new TEntry('documento');
        $usa_vaga = new TCombo('usa_vaga');
        $observacao = new TText('observacao');
        //$atualizacao = new TEntry('atualizacao');

        //Pablo Dall'Oglio O addquickfield bota um tamanho default. Chama o setsize depois
        $observacao->setSize(650, 80);

        //adicionando opções de sim/nao ao combo
        $combo_items = array(1=>"Sim",2=>"Não");
        $usa_vaga->addItems($combo_items); 
        
        // add the fields
        $this->form->addQuickField('Id', $id,  '15%' );
        $this->form->addQuickField('Unidade', $unidade_id,  '50%' );
        $this->form->addQuickField('Login', $system_user_login,  '100%' );
        $this->form->addQuickField('Nome', $nome,  '100%' );
        $this->form->addQuickField('Data Inicial', $data_inicial,  '40%' );
        $this->form->addQuickField('Data Final', $data_final,  '40%' );
        $this->form->addQuickField('Documento', $documento,  '50%' );
        $this->form->addQuickField('Usa Vaga', $usa_vaga,  '15%' );
        $this->form->addQuickField('Observação', $observacao,  '100%' );
        //$this->form->addQuickField('Atualizacao', $atualizacao,  '100%' );

        $data_inicial->setMask('dd/mm/yyyy');
        $data_final->setMask('dd/mm/yyyy');
        
        $system_user_login->setEditable(FALSE);
        //$unidade_id->setEditable(FALSE);
   
        // preenchidos automaticamente
        $system_user_login->setValue(TSession::getValue('login'));

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
       
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Complemento', '<b>'.'Usuário'.'</b><br>' . '{system_user_login}' 
        . '<br><b>'.'Unidade'.'</b><br>' . '{unidade_id}'
        . '<br><b>'.'Observação'.'</b><br>' . '{observacao}');
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'left');
        $column_system_user_login = new TDataGridColumn('system_user_login', 'Login', 'left');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_data_inicial = new TDataGridColumn('data_inicial', 'Dt Inicial', 'left');
        $column_data_final = new TDataGridColumn('data_final', 'Dt Final', 'left');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'left');
        $column_usa_vaga = new TDataGridColumn('usa_vaga', 'Usa Vaga', 'left');
        //$column_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');
        //$column_atualizacao = new TDataGridColumn('atualizacao', 'Atualizacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_unidade_id);
        //$this->datagrid->addColumn($column_system_user_login);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_data_inicial);
        $this->datagrid->addColumn($column_data_final);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_usa_vaga);
        //$this->datagrid->addColumn($column_observacao);
        //$this->datagrid->addColumn($column_atualizacao);

        $column_data_inicial->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_data_final->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array($this, 'onEdit'));
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        //$action2->setUseButton(TRUE);
        //$action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
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
        $container->add(TPanelGroup::pack('Autorização de Acesso', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
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
            
            // creates a repository for AutorizacaoAcesso
            $repository = new TRepository('AutorizacaoAcesso');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // configura grid pelo perfil do usuario
            $info_usuario = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            if ($info_usuario)
            {
                foreach ($info_usuario as $info_user)
                {
                    $perfil = $info_user->nivel_acesso_inf;
                    $unidade = $info_user->unidade_id;
                    $condominio = $info_user->condominio_id;
                }
            }
            
            //var_dump($condominio);
            
            if ($perfil == 0) { //desenvolvedor
            
            }
            
            if ($perfil == 1) { //administradora
            
            }
            
            if ($perfil == 2) { //gestor
            
            }
            
            if ($perfil == 3) { //portaria, acesso somente os registros do condominio
                
                // procura qual condominio esse usuario de portaria esta vinculado para colher as unidades dele
                $unidades = Unidade::where('condominio_id', '=', $condominio)->load();
                //var_dump($unidades);
                
                if ($unidades)
                {
                    $unidade_ids = array();
                    
                    foreach ($unidades as $unidade)
                    {
                      $unidade_ids[]  = $unidade->id;
                    }
                }
            
                //var_dump($unidade_ids);
                $criteria->add(new TFilter('unidade_id', 'IN', $unidade_ids));
            }
            
            if ($perfil == 4) { //morador, acesso somente os registros da unidade dele
                $criteria->add(new TFilter('unidade_id', '=', $unidade));
            }
            
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('AutorizacaoAcesso_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('AutorizacaoAcesso_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    if ($object->usa_vaga == '1') {
                        $object->usa_vaga = 'Sim';
                    }else {
                        $object->usa_vaga = 'Não';
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
            $object = new AutorizacaoAcesso($key, FALSE); // instantiates the Active Record
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
        $string = new StringsUtil;
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new AutorizacaoAcesso;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

           
            //formato necessário no mysql
            $object->data_final = TDate::date2us($object->data_final);
            $object->data_inicial = TDate::date2us($object->data_inicial);  

            $object->store(); // save the object
            
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
                $object = new AutorizacaoAcesso($key); // instantiates the Active Record
                
                // necessário no mysql
                $object->data_inicial = TDate::date2br($object->data_inicial); 
                $object->data_final = TDate::date2br($object->data_final);
                
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
