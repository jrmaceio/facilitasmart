<?php
/**
 * UsuarioCondominioFormList Form List
 * @author  <your name here>
 *
 * Nivel Acesso Informações : 1-normal, 2-sindico, 3-conselho_fiscal. Default = 1
 * 
 * Preciso ter una unidade como o nome vazia e uma pessoa com a descricao "sem cadastro" para usar em alguns casos aqui
 */
class UsuarioCondominioFormList extends TPage
{
     protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->setDatabase('facilitasmart');              // defines the database
        $this->setActiveRecord('UsuarioCondominio');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_UsuarioCondominio');
        $this->form->setFormTitle('Nível Acesso Usuários');
        
        // create the form fields
        $id = new TEntry('id');
        $ativo = new TCombo('ativo');
        $pessoa_id = new TDBCombo('pessoa_id', 'facilitasmart', 'Pessoa', 'id', 'nome', 'nome');
        $system_user_login = new TEntry('system_user_login');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}','descricao');
        $nivel_acesso_inf = new TCombo('nivel_acesso_inf');
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ], [ new TLabel('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('System User Login') ], [ $system_user_login ], [ new TLabel('Nivel Acesso Inf') ], [ $nivel_acesso_inf ] );
        $this->form->addFields( [ new TLabel('Pessoa') ], [ $pessoa_id ], [ new TLabel('Unidade') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Condominio') ], [ $condominio_id ] );
        //$this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );


        // set sizes
        $id->setSize('100%');
        $ativo->setSize('100%');
        $pessoa_id->setSize('100%');
        $system_user_login->setSize('100%');
        $condominio_id->setSize('100%');
        
        $unidade_id->setSize('100%');
        $nivel_acesso_inf->setSize('100%');
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
    
        $ativo->addItems(array( 
        'Y'=>'Ativo', 
        'N'=>'Inativo'
        )); 
        
        $nivel_acesso_inf->addItems(array( 
        '0'=>'Desenvolvedor', 
        '1'=>'Administradora', 
        '2'=>'Gestor', 
        '3'=>'Portaria',
        '4'=>'Morador'
        )); 

        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
         
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        ##LIST_DECORATOR##
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');
        $column_pessoa_id = new TDataGridColumn('pessoa_id', 'Pessoa', 'left');
        $column_system_user_login = new TDataGridColumn('system_user_login', 'System User Login', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condomínio', 'left');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'left');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_ativo);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_system_user_login);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_unidade_id);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array($this, 'onEdit'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setUseButton(TRUE);
        $action2->setButtonClass('btn btn-default');
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
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
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
            
            // creates a repository for UsuarioCondominio
            $repository = new TRepository('UsuarioCondominio');
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
            
            if (TSession::getValue('UsuarioCondominio_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('UsuarioCondominio_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
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
            $object = new UsuarioCondominio($key, FALSE); // instantiates the Active Record
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
            
            $object = new UsuarioCondominio;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
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
                $object = new UsuarioCondominio($key); // instantiates the Active Record
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
