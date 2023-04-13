<?php
/**
 * ArquivoCondominioFormList Form List
 * @author  <your name here>
 */
class ArquivoCondominioFormList extends TPage
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
        //$this->form = new TQuickForm('form_ArquivoCondominio');
        //$this->form->class = 'tform'; // change CSS class
        //$this->form = new BootstrapFormWrapper($this->form);
        //$this->form->style = 'display: table;width:100%'; // change style
        //$this->form->setFormTitle('ArquivoCondominio');
        $this->form = new BootstrapFormBuilder('form_ArquivoCondominio');
        $this->form->setFormTitle('Upload de Arquivos do Condomínio');
        
        
        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $tipo_documento_id = new TDBCombo('tipo_documento_id', 'facilitasmart', 'TipoDocumento', 'id', 'descricao', 'descricao');
        $arquivo = new TEntry('arquivo');
        $caminho = new TEntry('caminho');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        
        $file            = new TFile('file');
        $file->setCompleteAction(new TAction(array($this, 'onComplete'))); 
         
        $caminho->addValidation('Caminho', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);
        
        // define the sizes
        $id->setSize('50%');
        $descricao->setSize('100%');
        $file->setSize('100%');
        $condominio_id->setSize('100%');
        $tipo_documento_id->setSize('100%');
        
       
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Descrição')], [$descricao] );
        $this->form->addFields( [new TLabel('Condomínio')], [$condominio_id], [new TLabel('Tipo Documento')], [$tipo_documento_id]);
        $this->form->addFields( [new TLabel('Nome do Arquivo')], [$arquivo], [new TLabel('Caminho')], [$caminho]);
        
        //$this->form->addFields( [new TLabel(_t('File'))], [$file]);
        $this->form->addFields([new TLabel('Nome do Arquivo:', '#ff0000')],[$file]); 
         
        // Preview da imagem
        //$this->form->addContent([new TFormSeparator('Visualização do arquivo carregado', '#333333', '18', '#eeeeee')]); 
        //$this->frame = new TElement('div');
        //$this->frame->id = 'photo_frame';
        //$this->frame->style = 'width:400px;height:auto;min-height:200px;border:1px solid gray;padding:4px;margin:auto';
        //$this->form->addContent([$this->frame]); 
                
        $this->form->addContent([new TFormSeparator('Não utilize espaços no nome do arquivo !', '#333333', '16', '#eeeeee')]); 
        
        //TTransaction::open('permission');
        //$logged = SystemUser::newFromLogin(TSession::getValue('login'));
        //TTransaction::close();
        
        //$condominio_id->setExitAction(new TAction(array($this, 'onUpdateCaminho')));
        
        //$action1= new TAction(array($this, 'onUpdateCaminho'));
        //$condominio_id->setAction($action1); 

         
        //$caminho->setEditable(FALSE);
        $arquivo->setEditable(FALSE);
       
       
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'), new TAction(array($this, 'onClear')), 'fa:plus-square green' );
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_tipo_documento_id = new TDataGridColumn('tipo_documento_id', 'Tipo Documento', 'left');
        $column_arquivo = new TDataGridColumn('arquivo', 'Nome Arquivo', 'left');
        $column_caminho = new TDataGridColumn('caminho', 'Caminho', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condomínio', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_tipo_documento_id);
        $this->datagrid->addColumn($column_arquivo);
        $this->datagrid->addColumn($column_caminho);
        $this->datagrid->addColumn($column_condominio_id);

        
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
        //$container = new TVBox;
        //$container->style = 'width: 90%';
        //// $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add(TPanelGroup::pack('Upload de Arquivos do Condomínio', $this->form));
        //$container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        $container->add($this->frame);
        // add the vbox inside the page
        parent::add($container);
        
    }
    
    /**
    * On complete upload
    */
    public static function onComplete($param)
    {
        //TScript::create("$('#photo_frame').html('')");
  //      TScript::create("$('#photo_frame').append("<img style='width:100%' src='tmp/{$param['nome_arquivo']}'>");");
       
        // Tentativa de alterar a data de envio da imagem após fazer o carregamento
        //$data_envio->setValue(date("d-m-Y H:i"));
        
        
        /*
        
        Esse if é importante mas não está funcionando
        
         
        if ((strpos($param["nome_arquivo"],'.png')) && (strpos($param["nome_arquivo"],'.PNG')) && (strpos($param["nome_arquivo"],'.jpg')) && (strpos($param["nome_arquivo"],'.JPG'))){
            print_r($param["nome_arquivo"]);
            TScript::create("$('#photo_frame').html('')");
            TScript::create("$('#photo_frame').append("<img style='width:100%' src='tmp/{$param['nome_arquivo']}'>");");
        }*/
    } 
    

    public static function onUpdateCaminho($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $complementocondominios = ComplementoCondominio::where('condominio_id', '=', $param['condominio_id'])->load();
            
            var_dump($complementocondominios);
            foreach ($complementocondominios as $complementocondominio)
                {
                    $caminho    = $complementocondominios->caminho;
                
                }
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        $obj = new StdClass;
        $obj->caminho = $caminho;
        
        TForm::sendData('form_ArquivoCondominio', $obj);
        //new TMessage('info', 'Message on field exit. <br>You have typed: ' . $param['input_exit']);
    
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
            
            // creates a repository for ArquivoCondominio
            $repository = new TRepository('ArquivoCondominio');
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
            
            if (TSession::getValue('ArquivoCondominio_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('ArquivoCondominio_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $condominio = new Condominio($object->condominio_id); 
                    $object->condominio_id = $condominio->resumo;
                    
                    $tipodocumento = new TipoDocumento($object->tipo_documento_id); 
                    $object->tipo_documento_id = $tipodocumento->descricao;
                     
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
            $object = new ArquivoCondominio($key, FALSE); // instantiates the Active Record
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
            
            $object = new ArquivoCondominio;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $object->arquivo = $object->file;
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            // Se existe anexo registra e salva.
            //var_dump($object);
            if ($object->file)
            {
                $target_folder = $object->caminho;//  . $object->file    ;
                $target_file   = $target_folder . '/' .$object->file;
                //var_dump($target_file);
                @mkdir($target_folder);
                rename('tmp/'.$object->file, $target_file);
            } 
            
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
                $object = new ArquivoCondominio($key); // instantiates the Active Record
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
