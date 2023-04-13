<?php
/**
 * ContasReceberUpdateList Listing
 * @author  <your name here>
 */
class ContasReceberUpdateList extends TStandardList
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
        // parent::setCriteria($criteria) // define a standard filter

        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('mes_ref', 'like', 'mes_ref'); // filterField, operator, formField
        parent::addFilterField('unidade_id', '=', 'unidade_id'); // filterField, operator, formField
        parent::addFilterField('nosso_numero', '=', 'nosso_numero'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_ContasReceber');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Atualização de Nosso Número');
        

        // create the form fields
        $id = new TEntry('id');
        $mes_ref = new TEntry('mes_ref');
        $unidade_id = new TEntry('unidade_id');
        $nosso_numero = new TEntry('nosso_numero');


        // add the fields
        $this->form->addQuickField('Id', $id,  200 );
        $this->form->addQuickField('Mes Ref', $mes_ref,  200 );
        $this->form->addQuickField('Unidade Id', $unidade_id,  200 );
        $this->form->addQuickField('Nosso Número', $nosso_numero,  200 );

        
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
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'center');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_nosso_numero = new TDataGridColumn('nosso_numero_widget', 'Nosso Numero', 'center');

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_classe_id->setTransformer( function($value, $object, $row) {
            $classe = PlanoContas::RetornaPlanoContasCodDescricao($value);
            return $classe;
        });
        
        $column_unidade_id->setTransformer( function($value, $object, $row) {
            $unidade = Unidades::RetornaProprietarioUnidade($value);
            return $unidade;
        });

        $column_valor->setTransformer( function($value, $object, $row) {
            $valor = number_format($value, 2, ',', '.');
            return $valor;
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_nosso_numero);

        
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
            $object->nosso_numero_widget = new TEntry('nosso_numero' . '_' . $object->id);
            $object->nosso_numero_widget->setValue( $object->nosso_numero );
            $gridfields[] = $object->nosso_numero_widget; // important
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
                if ($field instanceof TEntry)
                {
                    $parts = explode('_', $name);
                    $id = end($parts);
                    $object = ContasReceber::find($id);
                    
                    if ($object AND isset($param[$name]))
                    {
                        $object->nosso_numero = $data->{$name};
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
