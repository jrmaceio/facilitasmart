<?php
/**
 * FechamentoListConferencia Listing
 * @author  <your name here>
 */
class FechamentoListConferencia extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
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
        
        $this->setDatabase('facilitasmart');            // defines the database
        $this->setActiveRecord('Fechamento');   // defines the active record
        $this->setDefaultOrder('mes_ref', 'desc');         // defines the default order
        
        //$criteria = new TCriteria();
        //$criteria->setProperty('limit', 200);
        //$this->setCriteria($criteria); // define a standard filter
        
        $this->addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'id_condominio'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Fechamento');
        $this->form->setFormTitle('Listagem de Fechamentos');
        

        // create the form fields
        $id_condominio = new TEntry('id_condominio');
        $mes_ref = new TEntry('mes_ref');


        // add the fields
        $this->form->addFields( [new TLabel('Condomínio')], [$id_condominio] );
        $this->form->addFields( [ new TLabel('Mes Ref') ], [ $mes_ref ] );


        // set sizes
        $mes_ref->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Fechamento_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_conta_fechamento_id = new TDataGridColumn('conta_fechamento_id', 'Conta Fechamento Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_status = new TDataGridColumn('status', 'Status', 'left');
        $column_mostra_fechamento = new TDataGridColumn('mostra_fechamento', 'Mostra Fechamento', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_conta_fechamento_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_mostra_fechamento);

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    

}
