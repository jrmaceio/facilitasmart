<?php
/**
 * PontoRondaList Listing
 * @author  <your name here>
 */
class PontoRondaList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_PontoRonda');
        $this->form->setFormTitle('PontoRonda');
        

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $intevalo_minutos = new TEntry('intevalo_minutos');
        $obrigatorio = new TEntry('obrigatorio');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $unidade_id = new TDBCombo('unidade_id','permission','SystemUnit','id','name', 'name', $criteria);

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Intevalo Minutos') ], [ $intevalo_minutos ] );
        $this->form->addFields( [ new TLabel('Obrigatorio') ], [ $obrigatorio ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );


        // set sizes
        $id->setSize('100%');
        $descricao->setSize('100%');
        $intevalo_minutos->setSize('100%');
        $obrigatorio->setSize('100%');
        $unidade_id->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('QRCode Pontos', new TAction([$this, 'qrcodeConfig']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_intevalo_minutos = new TDataGridColumn('intevalo_minutos', 'Intevalo Minutos', 'right');
        $column_obrigatorio = new TDataGridColumn('obrigatorio', 'Obrigatorio', 'left');
        $column_unidade_id = new TDataGridColumn('unit->name', 'Unidade', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_intevalo_minutos);
        $this->datagrid->addColumn($column_obrigatorio);
        $this->datagrid->addColumn($column_unidade_id);


        //$action1 = new TDataGridAction(['PontoRondaForm', 'onEdit'], ['id'=>'{id}']);
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
            $repository = new TRepository('PontoRonda');
            $limit = 100;
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
            $label .= '<b>Ponto</b>: {$descricao}' . "\n";
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
                    $posto = new Posto($object->posto_id);
                    
                    //criar o qrcode do ponde de ronda com:
                    //tipo=2,id_posto=50,id_patrulheiro=650,id_unidade=5,id_ponto_ronda
                    //a unidade é a unidade configurada no adianti admin
                    //o tipo=2 indica que é uma ronda
                    // neste momento o id_patrulheiro não precisa porque vai receber de quem está fazendo a ronda no momento
                    $object->codigo = '2,' .$posto->id . ',1,' . $posto->unidade_id . ',' .$object->id;
                    $generator->addObject($object);  
                   
                  
                   
                }
            }
            
            $generator->setBarcodeContent('{codigo}');
            $generator->generate();
            
            $arquivo = 'qrcodes' + rand(1, 1500) + '.pdf';
            
            $generator->save('app/output/' + arquivo);
            
            $window = TWindow::create('QRCode de pontos de ronda', 0.8, 0.8);
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
            $object = new PontoRonda($key); // instantiates the Active Record
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
        TSession::setValue(__CLASS__.'_filter_descricao',   NULL);
        TSession::setValue(__CLASS__.'_filter_intevalo_minutos',   NULL);
        TSession::setValue(__CLASS__.'_filter_obrigatorio',   NULL);
        TSession::setValue(__CLASS__.'_filter_unidade_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_descricao',   $filter); // stores the filter in the session
        }


        if (isset($data->intevalo_minutos) AND ($data->intevalo_minutos)) {
            $filter = new TFilter('intevalo_minutos', 'like', "%{$data->intevalo_minutos}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_intevalo_minutos',   $filter); // stores the filter in the session
        }


        if (isset($data->obrigatorio) AND ($data->obrigatorio)) {
            $filter = new TFilter('obrigatorio', 'like', "%{$data->obrigatorio}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_obrigatorio',   $filter); // stores the filter in the session
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
            
            // creates a repository for PontoRonda
            $repository = new TRepository('PontoRonda');
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


            if (TSession::getValue(__CLASS__.'_filter_descricao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_descricao')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_intevalo_minutos')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_intevalo_minutos')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_obrigatorio')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_obrigatorio')); // add the session filter
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
            $object = new PontoRonda($key, FALSE); // instantiates the Active Record
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
