<?php
/**
 * ContasPagarListDescricao Listing
 * @author  <your name here>
 */
class ContasPagarListDescricao extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    private static $paginas = 1;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ContasPagar');
        $this->form->setFormTitle('Conferência de Lançamentos');
        
        // create the form fields
        $id = new TEntry('id');
        
        $mes_ref = new TEntry('mes_ref');
        
        $output_type = new TRadioGroup('output_type');
        
        $output_type->addValidation('Output', new TRequiredValidator);
        
        
        $id->setSize(100);
        $mes_ref->setSize(100);
        $output_type->setSize('100%');
        
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id] ); 
        $this->form->addFields( [ new TLabel('Mês Ref.') ], [ $mes_ref ] );
       
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );
                
        $mes_ref->setValue(TSession::getValue('mesref'));
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasPagar_filter_data') );
        
         // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Vencimento'.'</b><br>' . '{dt_vencimento}' 
        . '<br><b>'.'Doc. Pagamento'.'</b><br>' . '{numero_doc_pagamento}'
        . '<br><b>'.'Documento'.'</b><br>' . '{documento}');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_valor);
        
        $column_classe_id->setTransformer( function($value, $object, $row) {
            $classe = new PlanoContas($value);
            return $classe->descricao;
        });
        
        $column_valor->setTransformer( function($value, $object, $row) {
            return 'R$ '.number_format($value, 2, ',', '.');
        });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF', 'xls' => 'XLS'));
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
        // add the action button
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction('Imprimir',  new TAction(array($this, 'onGenerate')), 'fa:print green');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->labelTotal, $this->pageNavigation));
       
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
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('ContasPagar');
            $criteria   = new TCriteria;
            
            if ($data->id)
            {
                $criteria->add(new TFilter('id', '=', "{$data->id}"));
            }
            
            if ($data->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$data->mes_ref}"));
            }
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 
           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(50,250,450,50);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths, $orientation='L');
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths, $orientation='L');
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '8', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '8', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Conferência de lançamentos para fechamento', 'center', 'header', 4);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'title');
                $tr->addCell('Classe', 'center', 'title');
                $tr->addCell('Descrição', 'left', 'title');
                $tr->addCell('Valor', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    
                    $classe = new PlanoContas($object->classe_id);
                    
                    $tr->addCell($classe->descricao, 'center', $style);
                    
                    $tr->addCell($object->descricao, 'left', $style);
                    
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 3);
                
                // stores the file
                if (!file_exists("app/output/ContasPagar.{$format}") OR is_writable("app/output/ContasPagar.{$format}"))
                {
                    $tr->save("app/output/ContasPagar.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasPagar.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasPagar.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
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
            $object = new ContasPagar($key); // instantiates the Active Record
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
        $string = new StringsUtil;
        
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('ContasPagarList_filter_id',   NULL);
        TSession::setValue('ContasPagarList_filter_mes_ref',   NULL);
        
        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('ContasPagarList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "{$data->mes_ref}"); // create the filter
            TSession::setValue('ContasPagarList_filter_mes_ref',   $filter); // stores the filter in the session
        }
           
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasPagar_filter_data', $data);
        
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
            $string = new StringsUtil;

            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasPagar
            $repository = new TRepository('ContasPagar');
            $limit = 100;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'descricao';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 

            if (TSession::getValue('ContasPagarList_filter_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasPagarList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_mes_ref')); // add the session filter
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
                    
                    $conta = new ContaFechamento( $object->conta_fechamento_id );
                    $object->conta_fechamento_id = $conta->descricao;

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
}
