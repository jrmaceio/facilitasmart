<?php
/**
 * ContasPagarUpdateList Listing
 * @author  <your name here>
 */
class ContasPagarUpdateListDocPag extends TPage
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
        $this->setActiveRecord('ContasPagar');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('condominio_id', '=', 'condominio_id'); // filterField, operator, formField
        $this->addFilterField('mes_ref', '=', 'mes_ref'); // filterField, operator, formField
        $this->addFilterField('documento', 'like', 'documento'); // filterField, operator, formField
        $this->addFilterField('numero_doc_pagamento', 'like', 'numero_doc_pagamento'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasPagar');
        $this->form->setFormTitle('ContasPagar');
        

        // create the form fields
        $id = new TEntry('id');
        $condominio_id = new TEntry('condominio_id');
        $mes_ref = new TEntry('mes_ref');
        $documento = new TEntry('documento');
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio Id') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Mes Ref') ], [ $mes_ref ] );
        $this->form->addFields( [ new TLabel('Documento') ], [ $documento ] );
        $this->form->addFields( [ new TLabel('Numero Doc Pagamento') ], [ $numero_doc_pagamento ] );


        // set sizes
        $id->setSize('100%');
        $condominio_id->setSize('100%');
        $mes_ref->setSize('100%');
        $documento->setSize('100%');
        $numero_doc_pagamento->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasPagar_filter_data') );
        
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
        $column_documento = new TDataGridColumn('documento', 'Documento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_numero_doc_pagamento = new TDataGridColumn('numero_doc_pagamento_widget', 'Numero Doc Pagamento', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_numero_doc_pagamento);

        
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
            $object->numero_doc_pagamento_widget = new TEntry('numero_doc_pagamento' . '_' . $object->id);
            $object->numero_doc_pagamento_widget->setValue( $object->numero_doc_pagamento );
            $object->numero_doc_pagamento_widget->setSize('100%');
            $gridfields[] = $object->numero_doc_pagamento_widget; // important
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
                    $object = ContasPagar::find($id);
                    
                    if ($object AND isset($param[$name]))
                    {
                        $object->numero_doc_pagamento = $data->{$name};
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
