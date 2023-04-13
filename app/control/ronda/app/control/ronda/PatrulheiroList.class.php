<?php
/**
 * PatrulheiroList Listing
 * @author  <your name here>
 */
class PatrulheiroList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_Patrulheiro');
        $this->form->setFormTitle('Patrulheiro');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $cargo = new TEntry('cargo');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $unidade_id = new TDBCombo('unidade_id','permission','SystemUnit','id','name', 'name', $criteria);

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Cargo') ], [ $cargo ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $cargo->setSize('100%');
        $unidade_id->setSize('100%');

        // create the form fields
        //$template = new TText('template');
        
        // add the fields inside the form
        //$this->form->addFields( [new TLabel('Template')],  [$template] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('QRCode Config', new TAction([$this, 'qrcodeConfig']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cargo = new TDataGridColumn('cargo', 'Cargo', 'left');
        $column_unidade_id = new TDataGridColumn('unit->name', 'Unidade', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cargo);
        $this->datagrid->addColumn($column_unidade_id);


        //$action1 = new TDataGridAction(['PatrulheiroForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    public function qrcodeConfig($param)
    {
        try
        {
            // open a transaction with database 'ronda'
            TTransaction::open('ronda');
            
            // creates a repository for Patrulheiro
            $repository = new TRepository('Patrulheiro');
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
            

            if (TSession::getValue(__CLASS__.'_filter_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_nome')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_nome')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_cargo')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_cargo')); // add the session filter
            }

            if (TSession::getValue(__CLASS__.'_filter_unidade_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_id')); // add the session filter
            } else {
                $criteria->add(new TFilter('unidade_id', 'IN', TSession::getValue('userunitids')));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $properties['leftMargin']    = 12;
            $properties['topMargin']     = 12;
            $properties['labelWidth']    = 64;
            $properties['labelHeight']   = 54;
            $properties['spaceBetween']  = 4;
            $properties['rowsPerPage']   = 5;
            $properties['colsPerPage']   = 3;
            $properties['fontSize']      = 12;
            $properties['barcodeHeight'] = 20;
            $properties['imageMargin']   = 0;
            
            $label  = '' . "\n";
            $label .= '<b>Código</b>: {$id}' . "\n";
            $label .= '<b>Nome</b>: {$nome}' . "\n";
            $label .= '' . "\n";
            $label .= '#qrcode#' . "\n";
            //$label .= '{$id_pad}';
        
            $generator = new AdiantiBarcodeDocumentGenerator;
            $generator->setProperties($properties);
            $generator->setLabelTemplate($label);
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    //criar um qrcode de identificação do patrulheiro com: id_posto, id_patrulheiro
                    //tipo=1,id_posto=50,id_patrulheiro=650,id_unidade,id_ponto_ronda
                    //o tipo=1 indica uma configuração de inicio de ronda, onde o patrulheiro ler seu crachá com qrcode
                    // não importa neste emomento o posto, porque na hora do registro da ronda o sistema recebe qual o posto
                    $object->codigo = '1,1,' . $object->id . ',' . $object->unidade_id . ',1';
                    $generator->addObject($object);  
                  
                }
            }
            
            $generator->setBarcodeContent('{codigo}');
            $generator->generate();
            
            $arquivo = 'qrcodes' + rand(1, 1500) + '.pdf';
            
            $generator->save('app/output/' + $arquivo);
            
            $window = TWindow::create('QRCode de configuração inicial de ronda', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = 'app/output/' + $arquivo;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
            
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
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
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
            
            TTransaction::open('ronda'); // open a transaction with database
            $object = new Patrulheiro($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_cargo',   NULL);
        TSession::setValue(__CLASS__.'_filter_unidade_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->cargo) AND ($data->cargo)) {
            $filter = new TFilter('cargo', 'like', "%{$data->cargo}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_cargo',   $filter); // stores the filter in the session
        }

        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue(__CLASS__.'_filter_unidade_id',   $filter); // stores the filter in the session
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
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
            // open a transaction with database 'ronda'
            TTransaction::open('ronda');
            
            // creates a repository for Patrulheiro
            $repository = new TRepository('Patrulheiro');
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
            
                        
            if (TSession::getValue(__CLASS__.'_filter_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_nome')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_nome')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_cargo')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_cargo')); // add the session filter
            }

            if (TSession::getValue(__CLASS__.'_filter_unidade_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_unidade_id')); // add the session filter
            } else {
                $criteria->add(new TFilter('unidade_id', 'IN', TSession::getValue('userunitids')));
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
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
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
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('ronda'); // open a transaction with database
            $object = new Patrulheiro($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
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
