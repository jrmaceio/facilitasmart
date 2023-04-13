<?php
/**
 * PlanoContasFormList Form List
 * @author  <your name here>
 */
class PlanoContasFormList extends TPage
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
        $this->form = new TQuickForm('form_PlanoContas');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('PlanoContas');
        


        // create the form fields
        $id = new TEntry('id');
        $codigo = new TEntry('codigo');
        $descricao = new TEntry('descricao');
        $tipo = new TEntry('tipo');


        // add the fields
        $this->form->addQuickField('Id', $id,  '50%' );
        $this->form->addQuickField('Código', $codigo,  '100%' );
        $this->form->addQuickField('Descrição', $descricao,  '100%' );
        //$this->form->addQuickField('Tipo', $tipo,  '100%' );

        $this->form->addQuickField('Tipo (T-Título,S-Sub-Título,D-Débito,C-Crédito)', $tipo,  300);
        
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
        $this->form->addQuickAction(_t('View'),  new TAction(array($this, 'onSend')), 'ico_new.png');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_codigo = new TDataGridColumn('codigo', 'Codigo', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
                
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_codigo);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_tipo);

        
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
        
        // creates the treeview
        $this->treeview = new TTreeView;
        $this->treeview->setSize(600);
        $this->treeview->setItemIcon('ico_file.png');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Plano Contas', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        parent::add($container);
    }

/**
     * Show selected records
     */
    public function onSend($param)
    {
  
        // creates the treeview
        $this->treeview = new TTreeView;
        $this->treeview->setSize(600);
        $this->treeview->setItemIcon('ico_file.png');
        
        TTransaction::open('facilitasmart'); 
        $conn = TTransaction::get();
        $sql = " (SELECT * FROM plano_contas order by codigo, descricao)";
        $colunas = $conn->query($sql);
        
        $data = array();
        $i=1;

        foreach ($colunas as $row) 
        { 
          if ($row['tipo'] == 'T')
          {
            //$data[$row['codigo']][$row['descricao']][$i] = '';
            //$data[$row['codigo']][$i]=$row['descricao'];
            $data[$row['codigo']][$i]= '';
            $codigo=$row['codigo'];
            $descricao="<strong>" .$row['descricao']. "</strong>" ;
            //$i=1;
            //$j++;
          }
          else if ($row['tipo'] == 'C' or $row['tipo'] == 'D')
          {
            $data[$codigo][$descricao][$i] = $row['codigo'] .' '. $row['descricao'] . '('.$row['id'].')';
          }
          else if ($row['tipo'] == 'S' or $row['tipo'] == 'R')
          {
             $data[$codigo][$descricao][$i] =  "<strong>" . $row['codigo'] . ' ' . $row['descricao'] . "</strong>" ;
          }
          
          $i++;
          
        }
        
         TTransaction::close(); 
         
         //var_dump($data[0]);
         //  var_dump("<br />");
         //var_dump($data[1]);
         // var_dump("<br />");
         //var_dump($data[2]);
         // var_dump("<br />");
         //var_dump($data[3]);

        
        
        $this->treeview->fromArray($data); // fill the treeview
        
        $win = TWindow::create('Plano de Conta', 0.6, 0.6);
        $win->add($this->treeview);
        $win->show();
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
            
            // creates a repository for PlanoContas
            $repository = new TRepository('PlanoContas');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'codigo';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('PlanoContas_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('PlanoContas_filter'));
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
            $object = new PlanoContas($key, FALSE); // instantiates the Active Record
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
            
            $object = new PlanoContas;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // torna maiusculo
            $object->descricao = strtoupper($object->descricao);
            $object->tipo = strtoupper($object->tipo);
            
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
                $object = new PlanoContas($key); // instantiates the Active Record
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
