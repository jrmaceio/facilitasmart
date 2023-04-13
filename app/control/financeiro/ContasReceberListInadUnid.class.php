<?php
/**
 * ContasReceberList Listing
 * @author  <your name here>
 */
class ContasReceberListInadUnid extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ContasReceber');
        $this->form->setFormTitle('Inadimplência - A consulta não é garantia de quitação, para mais detalhe procure a administração.');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Descrição', ' <b> {descricao} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref.', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Proprietário', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        //$this->datagrid->addColumn($column_descricao);

        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        TTransaction::open('facilitasmart');           
        $unidade = new Unidade(TSession::getValue('id_unidade'));
        $proprietario = new Pessoa($unidade->proprietario_id);
        TTransaction::close();
            
        $label_unidade = new TLabel('Unidade [' . $unidade->id . '] - ' . $proprietario->nome);
           
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);               
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
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
            $object = new ContasReceber($key); // instantiates the Active Record
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
     * Register the filter in the



    session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters


        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data', $data);
        
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
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            $string = new StringsUtil;
            
            $this->datagrid->clear();
            
            $unidade_id = TSession::getValue('id_unidade');
            $condominio_id = TSession::getValue('id_condominio');
            $inadimplencia_ate = date('Y-m-d');
            
            $conn = TTransaction::get();
            $sql = "SELECT contas_receber.id, contas_receber.unidade_id, contas_receber.cobranca, 
                    contas_receber.classe_id, contas_receber.dt_vencimento, contas_receber.valor, 
                    contas_receber.situacao, contas_receber.nome_responsavel,
                    contas_receber.mes_ref, unidade.descricao FROM contas_receber 
                    INNER JOIN unidade on contas_receber.unidade_id = unidade.id 
                    where 
                        contas_receber.condominio_id = " . $condominio_id . " and " .
                        "contas_receber.unidade_id = " . $unidade_id . " and " .
                        "(
                        (contas_receber.situacao = 0 and contas_receber.dt_vencimento <= '".$inadimplencia_ate."') or 
                        (contas_receber.situacao = 1 and contas_receber.dt_pagamento > '" . $inadimplencia_ate."' and 
                          contas_receber.dt_vencimento <= '" . $inadimplencia_ate."') or
                        ((select data_base_acordo from acordo where id = contas_receber.numero_acordo) > '" . 
                        $inadimplencia_ate."' and situacao = 2 and contas_receber.dt_vencimento <= '".$inadimplencia_ate
                        ."')) order by dt_vencimento";
                                     
            
            //var_dump($sql);
                                   
            $colunas = $conn->query($sql);
            //////////////////////////////////////
                        
            if ($colunas)
            {
                $obj = new stdClass();
                
                // iterate the collection of active records
                foreach ($colunas as $coluna)
                {
                    // add the object inside the datagrid
                    $obj->id = $coluna['id'];
                    $obj->mes_ref = $coluna['mes_ref'];
                    
                    $planoconta = new PlanoContas($coluna['classe_id']);
                    $obj->classe_id = $planoconta->descricao;
                                        
                    $obj->unidade_id = $coluna['nome_responsavel'];
                    
                    $obj->dt_vencimento = $string->formatDateBR($coluna['dt_vencimento']);
                    $obj->valor = number_format($coluna['valor'], 2, ',', '.');
                    $obj->descricao = $coluna['descricao'];
                    
                    $this->datagrid->addItem($obj);
                }
            
            }
            
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
