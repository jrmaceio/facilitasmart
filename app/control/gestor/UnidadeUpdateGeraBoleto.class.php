<?php
/**
 * UnidadeUpdateList Listing
 * @author  <your name here>
 */
class UnidadeUpdateGeraBoleto extends TPage
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
        $this->setActiveRecord('Unidade');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'condominio_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_update_Unidade');
        $this->form->setFormTitle('Unidade');
        

        // create the form fields
        $id = new TEntry('id');
        //$bloco_quadra = new TEntry('bloco_quadra');
        //$descricao = new TEntry('descricao');
        $condominio_id = new TEntry('condominio_id');
        //$proprietario_id = new TEntry('proprietario_id');
        //$gera_titulo = new TEntry('gera_titulo');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        //$this->form->addFields( [ new TLabel('Bloco Quadra') ], [ $bloco_quadra ] );
       // $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        //$this->form->addFields( [ new TLabel('Proprietario Id') ], [ $proprietario_id ] );
        //$this->form->addFields( [ new TLabel('Gera Titulo') ], [ $gera_titulo ] );


        // set sizes
        $id->setSize('100%');
        //$bloco_quadra->setSize('100%');
        //$descricao->setSize('100%');
        $condominio_id->setSize('100%');
        //$proprietario_id->setSize('100%');
        //$gera_titulo->setSize('100%');

        
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
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco Quadra', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_proprietario_id = new TDataGridColumn('proprietario_id', 'Proprietario Id', 'right');
        $column_gera_titulo = new TDataGridColumn('gera_titulo', 'Gera Titulo', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_proprietario_id);
        $this->datagrid->addColumn($column_gera_titulo);

        
        $column_gera_titulo->setTransformer( function($value, $object, $row) {
            $widget = new TEntry('gera_titulo' . '_' . $object->id);
            $widget->setValue( $object->gera_titulo );
            //$widget->setSize(120);
            $widget->setFormName('form_update_Unidade');
            
            $action = new TAction( [$this, 'onSaveInline'], ['column' => 'gera_titulo' ] );
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
            
            $object = Unidade::find($id);
            if ($object)
            {
                $object->$column = $value;
                $object->store();
            }
            
            //TToast::show('success', 'Record saved', 'bottom center', 'far:check-circle');
            //new TMessage('info', AdiantiCoreTranslator::translate('Records updated'));
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            // show the exception message
            //TToast::show('error', $e->getMessage(), 'bottom center', 'fa:exclamation-triangle');
            new TMessage('error', $e->getMessage());
        }
    }
}
