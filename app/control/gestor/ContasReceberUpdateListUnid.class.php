<?php
/**
 * ContasReceberUpdateList Listing
 * @author  <your name here>
 */
class ContasReceberUpdateListUnid extends TPage
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
        
         parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));


        $this->setDatabase('facilitasmart');            // defines the database
        $this->setActiveRecord('ContasReceber');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'condominio_id'); // filterField, operator, formField
        $this->addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        $this->addFilterField('classe_id', 'like', 'classe_id'); // filterField, operator, formField
        $this->addFilterField('unidade_id', '=', 'unidade_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_update_ContasReceber');
        $this->form->setFormTitle('ContasReceber');
        

        // create the form fields
        $id = new TEntry('id');
        $condominio_id = new TEntry('condominio_id');
        $mes_ref = new TEntry('mes_ref');
        $classe_id = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Mes Ref') ], [ $mes_ref ] );
        $this->form->addFields( [ new TLabel('Classe Id') ], [ $classe_id ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );


        // set sizes
        $id->setSize('100%');
        $condominio_id->setSize('100%');
        $mes_ref->setSize('100%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');

        
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
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe Id', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade Id', 'right');
        $column_nome_responsavel = new TDataGridColumn('nome_responsavel', 'Nome Responsavel', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Dt Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_nome_responsavel);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);

        
        $column_unidade_id->setTransformer( function($value, $object, $row) {
            $widget = new TEntry('unidade_id' . '_' . $object->id);
            $widget->setValue( $object->unidade_id );
            //$widget->setSize(120);
            $widget->setFormName('form_update_ContasReceber');
            
            $action = new TAction( [$this, 'onSaveInline'], ['column' => 'unidade_id' ] );
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
            
            $object = ContasReceber::find($id);
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
