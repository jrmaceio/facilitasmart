<?php
/**
 * LivroOcorrenciaList Listing
 * @author  <your name here>
 */
class LivroOcorrenciaList extends TPage
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
        $this->form = new TQuickForm('form_search_LivroOcorrencia');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Livro de Ocorrências');
        

        
        // keep the form filled during navigation with session data
        //$this->form->setData( TSession::getValue('LivroOcorrencia_filter_data') );
        
        // add the search form actions
        //$this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        //$this->form->addQuickAction(_t('New'),  new TAction(array('LivroOcorrenciaFormList', 'onEdit')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Ocorrência', 'Detalhe <b> {conclusao} </b>');

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        //$column_data_dia = new TDataGridColumn('data_dia', 'Data', 'left', 120);
        $coluna_descricao = new TDataGridColumn('descricao', 'Descrição', 'left', 200);
        $column_pessoa = new TDataGridColumn('pessoa', 'Pessoa', 'left', 80);
        $column_data_ocorrencia = new TDataGridColumn('data_ocorrencia', 'Data Ocorrência', 'left', 70);
        $column_hora_ocorrencia = new TDataGridColumn('hora_ocorrencia', 'Hora Ocorrência', 'left', 70);
        $column_status = new TDataGridColumn('status', 'Status', 'center', 20);
        
        $column_status->setTransformer(array($this, 'retornaStatus'));
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_data_dia);
        $this->datagrid->addColumn($coluna_descricao);
        $this->datagrid->addColumn($column_pessoa);
        $this->datagrid->addColumn($column_data_ocorrencia);
        $this->datagrid->addColumn($column_hora_ocorrencia);
        $this->datagrid->addColumn($column_status);

        
        // create EDIT action
        //$action_edit = new TDataGridAction(array('LivroOcorrenciaFormList', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        

        
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
            // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for LivroOcorrencia
            $repository = new TRepository('LivroOcorrencia');
            $limit = 15;
            // creates a criteria
            $criteria = new TCriteria;
            
            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel')));
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'data_dia';
                $param['direction'] = 'desc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
                        
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
                    // formato as datas
                    $object->data_ocorrencia = TDate::date2br($object->data_ocorrencia);
                    //$object->data_dia = TDate::date2br($object->data_dia);

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

    public function retornaStatus($campo, $object, $row)
    {
         $status = array(1 => 'Ativo', 2 => 'Pendente', 3 => 'Encerrado', 4 => 'Cancelado');           
        
         $row->popover = 'true';
         $row->popcontent = "<table class='popover-table' border='0'><tr><td>Status: {$status[$object->status]}</td></tr></table>";
         $row->poptitle = 'Ocorrência: '.$object->conclusao;
         
         $campo = new TImage($object->status.'.png');
         $campo->height=15;
         $campo->width=15;
         return $campo;
    }


}
