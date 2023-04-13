<?php
/**
 * ArquivoList Listing
 * @author  <your name here>
 */
class ArquivoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $loaded;
    
   
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
       
        $this->form = new BootstrapFormBuilder('form_search_Documento');
        $this->form->setFormTitle( 'Arquivos' );

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        //$tipo_id = new TEntry('tipo_id');
        $tipo_id = new TDBCombo('tipo_id', 'facilitasmart', 'TipoDocumento', 'id', 'descricao', 'descricao');
        $template = new TText('template');

        // add the fields
        //$this->form->addQuickField('Id', $id,  50 );
        //$this->form->addQuickField('Descrição', $descricao,  400 );
        //$this->form->addQuickField('Tipo', $tipo_id,  200 );
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Descrição')], [$descricao] );
        $this->form->addFields( [new TLabel('Tipo')], [$tipo_id] );
        $this->form->addFields( [new TLabel('Template QRCode')], [$template] );       
        
        $template->setSize('80%', 100);
        
        //$label  = '' . "\n";
        //$label .= '<b>Id Arquivo</b>: {$id}' . "\n";
        //$label .= '<b>Descrição</b>: {$descricao}' . "\n";
        //$label .= '#qrcode#' . "\n";
        //$label .= '{$arquivo}';
        
        $label  = '' . "\n";
        $label .= '<b>Arquivos disponibilizados no portal FacilitaSmart</b> (Use o leitor de QRCode do telefone)' . "\n";
        $label .= '<b> </b> ' . "\n";
        $label .= '<b> </b> ' . "\n";
        $label .= '#qrcode#' . "\n";
        $label .= '<b>Id </b>: {$id} ' . '<b> - </b> {$descricao}' . "\n";

        $template->setValue($label);
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Documento_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('QRCode', new TAction(array($this, 'onQRCode')), 'fa:plus-square green' );
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        
        // creates two datagrid actions
        $action0 = new TDataGridAction(array($this, 'onAbreArquivo'));
        $action0->setUseButton(TRUE);
        $action0->setButtonClass('btn btn-default');
        $action0->setLabel('Ver');
        $action0->setImage('fa:eye green fa-lg');
        $action0->setField('id');

        //$action1 = new TDataGridAction(array($this, 'onQRCode'));
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        //$action1->setLabel('Ver');
        //$action1->setImage('fa:eye green fa-lg');
        //$action1->setField('id');


        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_tipo_id = new TDataGridColumn('tipo_documento', 'Tipo Documento', 'left');
        $column_arquivo = new TDataGridColumn('arquivo', 'Arquivo', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_tipo_id);
        $this->datagrid->addColumn($column_arquivo);

       
        // add the actions to the datagrid
        $this->datagrid->addAction($action0);
        //$this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        $this->datagrid->disableDefaultClick();
         
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
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
        // add the vbox inside the page
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
            $object = new ArquivoCondominio($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
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
        TSession::setValue('DocumentoList_filter_imovel_id',   NULL);
        TSession::setValue('DocumentoList_filter_id',   NULL);
        TSession::setValue('DocumentoList_filter_descricao',   NULL);
        TSession::setValue('DocumentoList_filter_tipo_id',   NULL);
        
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
            $nivel_acesso = $user->nivel_acesso_inf;
            $condominio_id = $user->condominio_id;
        }
        TTransaction::close();

        $filter = new TFilter('condominio_id', '=', "{$condominio_id}"); // create the filter
        TSession::setValue('DocumentoList_filter_imovel_id',   $filter); // stores the filter in the session

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('DocumentoList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('DocumentoList_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_id) AND ($data->tipo_id)) {
            $filter = new TFilter('tipo_id', 'like', "%{$data->tipo_id}%"); // create the filter
            TSession::setValue('DocumentoList_filter_tipo_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Documento_filter_data', $data);
        
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
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Documento
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
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
            }
            TTransaction::close();
        
            $filter = new TFilter('condominio_id', '=', "{$condominio_id}"); // create the filter
            TSession::setValue('DocumentoList_filter_imovel_id',   $filter); // stores the filter in the session
            
            if (TSession::getValue('DocumentoList_filter_imovel_id')) {
                $criteria->add(TSession::getValue('DocumentoList_filter_imovel_id')); // add the session filter
            }

            if (TSession::getValue('DocumentoList_filter_id')) {
                $criteria->add(TSession::getValue('DocumentoList_filter_id')); // add the session filter
            }


            if (TSession::getValue('DocumentoList_filter_descricao')) {
                $criteria->add(TSession::getValue('DocumentoList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('DocumentoList_filter_tipo_id')) {
                $criteria->add(TSession::getValue('DocumentoList_filter_tipo_id')); // add the session filter
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
                    $tipo_documentos = TipoDocumento::where('id', '=', $object->tipo_documento_id)->load();
                     
                    foreach ($tipo_documentos as $tipo_documento)
                    {  
                        $object->tipo_documento = $tipo_documento->descricao;     
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
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
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
    

    public function onQRCode( $param )
    {
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction with database
             
            $data = $this->form->getData(); // optional parameter: active record class

            $properties['leftMargin']    = 12;
            $properties['topMargin']     = 12;
            $properties['labelWidth']    = 64;
            $properties['labelHeight']   = 54;
            $properties['spaceBetween']  = 4;
            $properties['rowsPerPage']   = 5;
            $properties['colsPerPage']   = 1;
            $properties['fontSize']      = 12;
            $properties['barcodeHeight'] = 20;
            $properties['imageMargin']   = 0;
            
            $generator = new AdiantiBarcodeDocumentGenerator;
            $generator->setProperties($properties);
            $generator->setLabelTemplate($data->template);
            
            $products = ArquivoCondominio::all();
            
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
                $user_cond = $user->condominio_id;   
            }
                    
            $i = 1; // contador de arquivos para mostrar somente 10
            
            foreach ($products as $product)
            {     
                if ( ($product->condominio_id == $user_cond) and ($i <= 10) ) {
                    //$product->id      = str_pad($product->id, 10, '0', STR_PAD_LEFT);
                    //$product->descricao = substr($product->descricao, 0, 15);
                    $product->id      = str_pad($product->id, 5, '0', STR_PAD_LEFT);
                    $product->descricao = $product->descricao;
                    //$product->descricao = substr($product->descricao, 0, 100);
                    $generator->addObject($product);
                    
                    $i++;
                }
            }
            TTransaction::close();
            
            //$generator->setBarcodeContent('{id}-{descricao}');
            
            $generator->setBarcodeContent('www.facilitahomeservice.com.br/facilitasmart/'.'{caminho}'.'{arquivo}');
            
            $generator->generate();
            $generator->save('tmp/qrcodes.pdf');
            parent::openFile('tmp/qrcodes.pdf');
            
            // shows the success message
            new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    
    }
    
    public function onAbreArquivo( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new ArquivoCondominio($key); // instantiates the Active Record
               
                // AVISO PARA HABILITAR POOPUPS
                new TMessage('info', 'Habilite no seu navegador para que os pop-ups apareçam automaticamente na sua tela.');
           
                // alterado na versao gestor 2 $file = "app/imoveis/" . $object->caminho . $object->arquivo;
                $file = $object->caminho . '/' . $object->arquivo;
                
                //var_dump($object->caminho);
                //var_dump($object->arquivo);
                //var_dump($file);
                
                if(file_exists($file)){
                    //var_dump($object->caminho . $object->arquivo);
                    parent::openFile($object->caminho . $object->arquivo);
                    // shows the success message
                    new TMessage('info', 'Por favor, habilite popups no navegador.');
                }else{
                    throw new Exception('Arquivo inexistente');
                }
                
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
    
}
