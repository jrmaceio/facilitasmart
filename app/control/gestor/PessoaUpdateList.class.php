<?php
/**
 * PessoaUpdateList Listing
 * @author  <your name here>
 */
class PessoaUpdateList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $saveButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('facilitasmart');            // defines the database
        $this->setActiveRecord('Pessoa');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'condominio_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_update_Pessoa');
        $this->form->setFormTitle('Pessoa');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $condominio_id = new TEntry('condominio_id');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $condominio_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('cnpj', 'CNPF', 'left');
        $column_cpf = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cnpj = new TDataGridColumn('cnpj', 'Cnpj', 'left');
        $column_pessoa_fisica_juridica = new TDataGridColumn('pessoa_fisica_juridica', 'Pessoa Fisica Juridica', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_cnpj);
        $this->datagrid->addColumn($column_pessoa_fisica_juridica);
        $this->datagrid->addColumn($column_condominio_id);

        
        $column_nome->setTransformer( function($value, $object, $row) {
            $widget = new TEntry('cnpj' . '_' . $object->id);
            $widget->setValue( $object->cnpj );
            //$widget->setSize(120);
            $widget->setFormName('form_update_Pessoa');
            
            $action = new TAction( [$this, 'onSaveInline'], ['column' => 'cnpj' ] );
            $widget->setExitAction( $action );
            return $widget;
        });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Save the datagrid objects
     */
    public static function onSaveInline($param)
    {
        $name   = $param['_field_name'];
        $value  = $param['_field_value'];
        $column = $param['column'];
        
        $parts  = explode('_', $name);
        $id     = end($parts);
        
        try
        {
            // open transaction
            TTransaction::open('facilitasmart');
            
            $object = Pessoa::find($id);
            if ($object)
            {
                $object->$column = $value;
                $object->store();
            }
            
            TToast::show('success', 'Record saved', 'bottom center', 'far:check-circle');
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            // show the exception message
            TToast::show('error', $e->getMessage(), 'bottom center', 'fa:exclamation-triangle');
        }
    }
}
