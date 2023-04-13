<?php
/**
 * ContaCorrenteFormList Registration
 * @author  <your name here>
 */
class ContaCorrenteFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardFormListTrait; // standard form/list methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('facilita');            // defines the database
        $this->setActiveRecord('ContaCorrente');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter
        
        // creates the form
        $this->form = new TQuickForm('form_ContaCorrente');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('ContaCorrente');
        


        // create the form fields
        $id = new TEntry('id');
        $banco_id = new TEntry('banco_id');
        $imovel_id = new TEntry('imovel_id');
        $agencia = new TEntry('agencia');
        $conta = new TEntry('conta');
        $natureza = new TEntry('natureza');
        $cedente = new TEntry('cedente');
        $tipo = new TEntry('tipo');
        $carteira = new TEntry('carteira');
        $dt_abertura = new TDate('dt_abertura');
        $dt_fechamento = new TDate('dt_fechamento');
        $saldo_inicial = new TEntry('saldo_inicial');
        $referencia = new TEntry('referencia');
        $especie_doc_boleto = new TEntry('especie_doc_boleto');
        $especie_doc_remessa = new TEntry('especie_doc_remessa');
        $dias_protesto = new TEntry('dias_protesto');
        $dias_devolucao = new TEntry('dias_devolucao');
        $numero_contrato = new TEntry('numero_contrato');
        $producao = new TEntry('producao');
        $nao_mostra_demonstrativo = new TEntry('nao_mostra_demonstrativo');
        $conta_com_cod_cedente = new TEntry('conta_com_cod_cedente');
        $observacao = new TText('observacao');


        // add the fields
        $this->form->addQuickField('Id', $id,  100 );
        $this->form->addQuickField('Banco Id', $banco_id,  100 );
        $this->form->addQuickField('Imovel Id', $imovel_id,  100 );
        $this->form->addQuickField('Agencia', $agencia,  200 , new TRequiredValidator);
        $this->form->addQuickField('Conta', $conta,  200 , new TRequiredValidator);
        $this->form->addQuickField('Natureza', $natureza,  200 );
        $this->form->addQuickField('Cedente', $cedente,  200 );
        $this->form->addQuickField('Tipo', $tipo,  200 );
        $this->form->addQuickField('Carteira', $carteira,  200 );
        $this->form->addQuickField('Dt Abertura', $dt_abertura,  100 );
        $this->form->addQuickField('Dt Fechamento', $dt_fechamento,  100 );
        $this->form->addQuickField('Saldo Inicial', $saldo_inicial,  200 );
        $this->form->addQuickField('Referencia', $referencia,  200 );
        $this->form->addQuickField('Especie Doc Boleto', $especie_doc_boleto,  200 );
        $this->form->addQuickField('Especie Doc Remessa', $especie_doc_remessa,  200 );
        $this->form->addQuickField('Dias Protesto', $dias_protesto,  200 );
        $this->form->addQuickField('Dias Devolucao', $dias_devolucao,  200 );
        $this->form->addQuickField('Numero Contrato', $numero_contrato,  200 );
        $this->form->addQuickField('Producao', $producao,  200 );
        $this->form->addQuickField('Nao Mostra Demonstrativo', $nao_mostra_demonstrativo,  200 );
        $this->form->addQuickField('Conta Com Cod Cedente', $conta_com_cod_cedente,  200 );
        $this->form->addQuickField('Observacao', $observacao,  200 );



        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        ##LIST_DECORATOR##
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_banco_id = new TDataGridColumn('banco_id', 'Banco Id', 'left');
        $column_imovel_id = new TDataGridColumn('imovel_id', 'Imovel Id', 'left');
        $column_agencia = new TDataGridColumn('agencia', 'Agencia', 'left');
        $column_conta = new TDataGridColumn('conta', 'Conta', 'left');
        $column_natureza = new TDataGridColumn('natureza', 'Natureza', 'left');
        $column_cedente = new TDataGridColumn('cedente', 'Cedente', 'left');
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');
        $column_carteira = new TDataGridColumn('carteira', 'Carteira', 'left');
        $column_dt_abertura = new TDataGridColumn('dt_abertura', 'Dt Abertura', 'left');
        $column_dt_fechamento = new TDataGridColumn('dt_fechamento', 'Dt Fechamento', 'left');
        $column_saldo_inicial = new TDataGridColumn('saldo_inicial', 'Saldo Inicial', 'left');
        $column_referencia = new TDataGridColumn('referencia', 'Referencia', 'left');
        $column_especie_doc_boleto = new TDataGridColumn('especie_doc_boleto', 'Especie Doc Boleto', 'left');
        $column_especie_doc_remessa = new TDataGridColumn('especie_doc_remessa', 'Especie Doc Remessa', 'left');
        $column_dias_protesto = new TDataGridColumn('dias_protesto', 'Dias Protesto', 'left');
        $column_dias_devolucao = new TDataGridColumn('dias_devolucao', 'Dias Devolucao', 'left');
        $column_numero_contrato = new TDataGridColumn('numero_contrato', 'Numero Contrato', 'left');
        $column_producao = new TDataGridColumn('producao', 'Producao', 'left');
        $column_nao_mostra_demonstrativo = new TDataGridColumn('nao_mostra_demonstrativo', 'Nao Mostra Demonstrativo', 'left');
        $column_conta_com_cod_cedente = new TDataGridColumn('conta_com_cod_cedente', 'Conta Com Cod Cedente', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_banco_id);
        $this->datagrid->addColumn($column_imovel_id);
        $this->datagrid->addColumn($column_agencia);
        $this->datagrid->addColumn($column_conta);
        $this->datagrid->addColumn($column_natureza);
        $this->datagrid->addColumn($column_cedente);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_carteira);
        $this->datagrid->addColumn($column_dt_abertura);
        $this->datagrid->addColumn($column_dt_fechamento);
        $this->datagrid->addColumn($column_saldo_inicial);
        $this->datagrid->addColumn($column_referencia);
        $this->datagrid->addColumn($column_especie_doc_boleto);
        $this->datagrid->addColumn($column_especie_doc_remessa);
        $this->datagrid->addColumn($column_dias_protesto);
        $this->datagrid->addColumn($column_dias_devolucao);
        $this->datagrid->addColumn($column_numero_contrato);
        $this->datagrid->addColumn($column_producao);
        $this->datagrid->addColumn($column_nao_mostra_demonstrativo);
        $this->datagrid->addColumn($column_conta_com_cod_cedente);
        $this->datagrid->addColumn($column_observacao);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array($this, 'onEdit'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setUseButton(TRUE);
        $action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
        $action2->setImage('fa:trash-o red fa-lg');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
}
