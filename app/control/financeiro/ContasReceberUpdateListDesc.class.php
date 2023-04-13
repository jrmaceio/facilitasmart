<?php
/**
 * ContasReceberUpdateListDesc
 * @author  <your name here>
 */
class ContasReceberUpdateListDesc extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $saveButton;
    protected $transformCallback;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('facilitasmart');            // defines the database
        parent::setActiveRecord('ContasReceber');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        // parent::setCriteria($criteria) // define a standard filter

        // creates a criteria
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        ////$criteria->add(new TFilter('situacao', '=', "0"));
        //$criteria->add(new TFilter('cobranca', '=', "3"));
        parent::setCriteria($criteria); // define a standard filter
        
        parent::addFilterField('id', 'like', 'id'); // filterField, operator, formField
        parent::addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_ContasReceber');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('ContasReceber');
        

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');


        // add the fields
        $this->form->addQuickField('Id', $id,  '100%' );
        $this->form->addQuickField('Mes Ref', $mes_ref,  '100%' );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condominio Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_descricao = new TDataGridColumn('descricao_widget', 'Descricao', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        
        $column_valor->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_valor);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
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
        
        parent::setTransformer(array($this, 'onBeforeLoad'));
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Title', $this->form));
        $container->add($gridpack);
        $container->add($this->pageNavigation);
        
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
            $object->descricao_widget = new TEntry('descricao' . '_' . $object->id);
            $object->descricao_widget->setValue( $object->descricao );
            $gridfields[] = $object->descricao_widget; // important
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
                        $object->descricao = $data->{$name};
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
