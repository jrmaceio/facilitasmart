<?php
/**
 * ContasReceberAcordoUpdateList Listing
 * @author  <your name here>
 */
class ContasReceberAcordoUpdateList extends TStandardList
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
        
        parent::setDatabase('facilita');            // defines the database
        parent::setActiveRecord('ContasReceber');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        
        // creates a criteria
        $criteria = new TCriteria;
        //$criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter
        ////$criteria->add(new TFilter('situacao', '=', "0"));
        $criteria->add(new TFilter('cobranca', '=', "3"));
        parent::setCriteria($criteria); // define a standard filter

        parent::addFilterField('numero_acordo', '=', 'numero_acordo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_ContasReceber');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Acordo - Alteração de vencimento');
        

        // create the form fields
        $numero_acordo = new TEntry('numero_acordo');


        // add the fields
        $this->form->addQuickField('Número do Acordo', $numero_acordo,  200 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref', 'left');
        $column_cobranca = new TDataGridColumn('cobranca', 'Cobranca', 'center');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento_widget', 'Data Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'center');
        $column_numero_acordo = new TDataGridColumn('numero_acordo', 'Número Acordo', 'center');
        $column_parcela = new TDataGridColumn('parcela', 'Parcela', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_cobranca);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_numero_acordo);
        $this->datagrid->addColumn($column_parcela);

        ///$column_dt_vencimento->setTransformer( function($value, $object, $row) {
         //   $date = new DateTime($value);
         //   return $date->format('d/m/Y');
        //});
        
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
        
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->add($this->saveButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        parent::setTransformer(array($this, 'onBeforeLoad'));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($gridpack);
        $container->add($this->pageNavigation);
        
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
            //var_dump($object->situacao);
            //if ( $object->situacao == '0' ) {
                $object->dt_vencimento_widget = new TDate('dt_vencimento' . '_' . $object->id);
                $object->dt_vencimento_widget->setValue( $object->dt_vencimento );
                $gridfields[] = $object->dt_vencimento_widget; // important
           // }else {
            //    $gridfields[] = $object->dt_vencimento;
           // }
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
            TTransaction::open('facilita');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                if ($field instanceof TDate)
                {
                    $parts = explode('_', $name);
                    $id = end($parts);
                    $object = ContasReceber::find($id);

                    if ($object AND isset($param[$name]))
                    {
                        $object->dt_vencimento = $data->{$name};
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
