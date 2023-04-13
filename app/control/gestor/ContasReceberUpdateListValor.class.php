<?php
/**
 * ContasReceberUpdateList Listing
 * @author  <your name here>
 */
class ContasReceberUpdateListValor extends TPage
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
        $this->setActiveRecord('ContasReceber');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'condominio_id'); // filterField, operator, formField
        $this->addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        $this->addFilterField('classe_id', 'like', 'classe_id'); // filterField, operator, formField
        $this->addFilterField('unidade_id', '=', 'unidade_id'); // filterField, operator, formField
        $this->addFilterField('situacao', 'like', 'situacao'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber');
        $this->form->setFormTitle('ContasReceber');
        

        // create the form fields
        $id = new TEntry('id');
        $condominio_id = new TEntry('condominio_id');
        $mes_ref = new TEntry('mes_ref');
        $classe_id = new TEntry('classe_id');
        $unidade_id = new TEntry('unidade_id');
        $situacao = new TEntry('situacao');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Mes Ref') ], [ $mes_ref ] );
        $this->form->addFields( [ new TLabel('Classe Id') ], [ $classe_id ] );
        $this->form->addFields( [ new TLabel('Unidade Id') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Situacao') ], [ $situacao ] );


        // set sizes
        $id->setSize('100%');
        $condominio_id->setSize('100%');
        $mes_ref->setSize('100%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        $situacao->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_condominio_id = new TDataGridColumn('condominio->resumo', 'Condominio Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe Id', 'right');
        $column_unidade_id = new TDataGridColumn('unidade->descricao', 'Unidade', 'right');
        $column_nome_responsavel = new TDataGridColumn('nome_responsavel', 'Nome', 'left');
        $column_valor = new TDataGridColumn('valor_widget', 'Valor', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_nome_responsavel);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_situacao);

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $this->datagrid->disableDefaultClick();
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        // creates the delete collection button
        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onSaveCollection')), AdiantiCoreTranslator::translate('Save'));
        $this->saveButton->setImage('fa:save green');
        $this->formgrid->addField($this->saveButton);
        
        $gridpack = new TPanelGroup;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->addFooter($this->saveButton);//->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $this->setTransformer(array($this, 'onBeforeLoad'));
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $gridpack, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Transform datagrid objects
     * Create one widget per element
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $saveAction = $this->saveButton->getAction();
        $saveAction->setParameters($param); // important!
        
        $gridfields = array( $this->saveButton );
        
        foreach ($objects as $object)
        {
            $object->valor_widget = new TEntry('valor' . '_' . $object->id);
            $object->valor_widget->setValue( $object->valor );
            $object->valor_widget->setSize('100%');
            $gridfields[] = $object->valor_widget; // important
        }
        
        $this->formgrid->setFields($gridfields);
    }
    

    /**
     * Save the datagrid objects
     */
    public function onSaveCollection($param)
    {
        $data = $this->formgrid->getData(); // get datagrid form data
        $this->formgrid->setData($data); // keep the form filled
        
        try
        {
            // open transaction
            TTransaction::open('facilitasmart');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                if ($field instanceof TEntry)
                {
                    $parts = explode('_', $name);
                    $id = end($parts);
                    $object = ContasReceber::find($id);
                    
                    if ($object AND isset($param[$name]))
                    {
                        $object->valor = $data->{$name};
                        $object->store();
                    }
                }
            }
            new TMessage('info', AdiantiCoreTranslator::translate('Records updated'));
            
            // close transaction
            TTransaction::close();
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
    }

}
