<?php
/**
 * PessoaListGeral Listing
 * @author  <your name here>
 */
class PessoaListGeral extends TPage
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
        
        $this->setDatabase('facilitasmart');            // defines the database
        $this->setActiveRecord('Pessoa');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('cpf', 'like', 'cpf'); // filterField, operator, formField
        $this->addFilterField('cnpj', 'like', 'cnpj'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle('Pessoa');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $cnpj = new TEntry('cnpj');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Cpf') ], [ $cpf ] );
        $this->form->addFields( [ new TLabel('Cnpj') ], [ $cnpj ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $cpf->setSize('100%');
        $cnpj->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Pessoa_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['PessoaForm', 'onEdit']), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'Cpf', 'left');
        $column_cnpj = new TDataGridColumn('cnpj', 'Cnpj', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_cnpj);
        $this->datagrid->addColumn($column_condominio_id);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['PessoaForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        

        
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
