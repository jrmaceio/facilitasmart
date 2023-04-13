<?php
/**
 * ContasReceberList Listing
 * @author  <your name here>
 */
class ContasReceberListNossoNumero extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
                
        // creates the form
        $this->form = new BootstrapFormBuilder('form_LerRemessa');
        $this->form->setFormTitle('Conferência de Nosso Número');

        // create the form fields
        $id = new TEntry('id');
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $mes_ref = new TEntry('mes_ref');
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}','descricao',$criteria);

        //$dt_vencimento = new TEntry('dt_vencimento');
    
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Condomínio')], [$condominio_id] );
        $this->form->addFields( ['Mes Ref'], [$mes_ref], ['Unidade'], [$unidade_id] );
        
        //$this->form->addQuickField('Dt Vencimento', $dt_vencimento,  '100%' );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $this->form->addAction('Relatório', new TAction(array($this, 'onGenerate')), 'fa:print');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nosso_numero = new TDataGridColumn('nosso_numero', 'Nosso Número', 'left');
        $column_condominio_id = new TDataGridColumn('condominio_id', 'Condomínio', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_desconto = new TDataGridColumn('desconto_boleto_cobranca', 'Desconto', 'left');
        $column_situacao = new TDataGridColumn('situacao', 'Situação', 'left');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Liquidacao', 'left');
        $column_conta_fechamento_id = new TDataGridColumn('conta_fechamento_id', 'Conta Fechamento', 'right');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'left');
        $column_nosso_numero_ant1 = new TDataGridColumn('nosso_numero_ant1', 'Nosso Número Ant1', 'left');
        $column_nosso_numero_ant2 = new TDataGridColumn('nosso_numero_ant2', 'Nosso Número Ant2', 'left');
        $column_nosso_numero_ant3 = new TDataGridColumn('nosso_numero_ant3', 'Nosso Número Ant3', 'left');
        $column_arquivo_retorno = new TDataGridColumn('arquivo_retorno', 'Retorno', 'left');
        $column_arquivo_remessa = new TDataGridColumn('arquivo_remessa', 'Remessa', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nosso_numero);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_desconto);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_dt_liquidacao);
        $this->datagrid->addColumn($column_conta_fechamento_id);
        $this->datagrid->addColumn($column_valor_pago);
        
        $this->datagrid->addColumn($column_nosso_numero_ant1);
        $this->datagrid->addColumn($column_nosso_numero_ant2);
        $this->datagrid->addColumn($column_nosso_numero_ant3);
        $this->datagrid->addColumn($column_arquivo_retorno);
        $this->datagrid->addColumn($column_arquivo_remessa);
     

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        parent::add($container);
    }
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new ContasReceber($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('ContasReceberList_filter_id',   NULL);
        TSession::setValue('ContasReceberList_filter_condominio_id',   NULL);
        TSession::setValue('ContasReceberList_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberList_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberList_filter_dt_vencimento',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->condominio_id) AND ($data->condominio_id)) {
            $filter = new TFilter('condominio_id', 'like', "%{$data->condominio_id}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_condominio_id',   $filter); // stores the filter in the session
        }


        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', 'like', "%{$data->mes_ref}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_mes_ref',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', 'like', "%{$data->unidade_id}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vencimento) AND ($data->dt_vencimento)) {
            $filter = new TFilter('dt_vencimento', 'like', "%{$data->dt_vencimento}%"); // create the filter
            TSession::setValue('ContasReceberList_filter_dt_vencimento',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasReceber_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberList_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_condominio_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_condominio_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberList_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberList_filter_dt_vencimento')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->dt_liquidacao ? $object->dt_liquidacao = $this->string->formatDateBR($object->dt_liquidacao) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                    
                    $plano_contas = new PlanoContas($object->classe_id);
                    $object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    $unidade = new Unidade( $object->unidade_id );
                    $proprietario = new Pessoa( $unidade->proprietario_id );
                    
                    $object->unidade_id = '['.$object->unidade_id.']'.$unidade->descricao . '-' . $proprietario->nome;
                    
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    



    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
    
    /**
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            $string = new StringsUtil;
            
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('ContasReceber');
            $criteria   = new TCriteria;
            
            if ($formdata->condominio_id)
            {
                $criteria->add(new TFilter('condominio_id', '=', "{$formdata->condominio_id}"));
            }
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$formdata->mes_ref}"));
            }

            $param['order'] = 'unidade_id';
            $param['direction'] = 'asc';
            $criteria->setProperties($param); // order, offset
           
            $objects = $repository->load($criteria, FALSE);
            
            $format  = 'pdf';//$formdata->output_type;
            
            if ($objects)
            {
                $widths = array(400, 50,60,50,90,75,75,100,80,100,100);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
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
                
                // add a header row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - Gestão Condominial', 'center', 'header', 10);
                $tr->addRow();
                $tr->addCell(utf8_decode('Condominio '.$condominio->resumo), 'center', 'title', 10);
                $tr->addRow();
                $tr->addCell(utf8_decode('CONFERENCIA DE NOSSO NÚMERO'), 'center', 'title', 10);
                $tr->addRow();
                //$tr->addCell('Período de '.$string->formatDateBR($dt_inicial).' a '.$string->formatDateBR($dt_final), 'center', 'title', 10);
                
                //$tr->addRow();
                //$tr->addCell('Unidade', 'right', 'title', 12);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Unidade', 'left', 'title');
                $tr->addCell('Id', 'center', 'title');
                //$tr->addCell('Condominio Id', 'right', 'title');
                $tr->addCell('Mes Ref', 'center', 'title');
                $tr->addCell('Classe', 'center', 'title');
                //$tr->addCell('Unidade', 'right', 'title');
                $tr->addCell('Vencimento', 'left', 'title');
                $tr->addCell('Valor', 'center', 'title');
                //$tr->addCell('Liquidacao', 'left', 'title');
                //$tr->addCell('Conta Fechamento Id', 'right', 'title');
                $tr->addCell('Pagam.', 'center', 'title');
                $tr->addCell('NossoNum', 'center', 'title');
                $tr->addCell('Retorno', 'center', 'title');
                $tr->addCell('Remessa', 'center', 'title');

                
                // controls the background filling
                $colour= FALSE;
                $unidade = '';
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    
                    if ($unidade != $object->unidade_id) {
                        $unidade = new Unidade( $object->unidade_id );
                        $proprietario = new Pessoa( $unidade->proprietario_id );
                        $unidade_prop = '['.$object->unidade_id.'] '.$unidade->descricao . ' - ' . $proprietario->nome;
                        
                        $unidade = $object->unidade_id;
                    }
                    
                    $tr->addRow();
                    $tr->addCell($unidade_prop, 'left', $style );

                    //$plano_contas = new PlanoContas($object->classe_id);
                    //$object->classe_id = '['.$plano_contas->id.']'.$plano_contas->descricao;
                    
                    $tr->addCell($object->id, 'right', $style);
                    //$tr->addCell($object->condominio_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'left', $style);
                    $tr->addCell($object->classe_id, 'center', $style);
                    //$tr->addCell($object->unidade_id, 'right', $style);
                    
                    //$tr->addCell($object->dt_vencimento, 'left', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    
                    $tr->addCell($object->valor, 'right', $style);
                    //$tr->addCell($object->dt_liquidacao, 'left', $style);
                    //$tr->addCell($object->conta_fechamento_id, 'right', $style);
                    $tr->addCell($object->valor_pago, 'right', $style);
                    $tr->addCell($object->nosso_numero, 'right', $style);
                    //$tr->addCell($object->nosso_numero_ant1, 'right', $style);
                    //$tr->addCell($object->nosso_numero_ant2, 'right', $style);
                    //$tr->addCell($object->nosso_numero_ant3, 'right', $style);
                    $tr->addCell($object->arquivo_retorno, 'right', $style);
                    $tr->addCell($object->arquivo_remessa, 'right', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 10);
                // stores the file
                if (!file_exists("app/output/ConfNossoNumero.{$format}") OR is_writable("app/output/ConfNossoNumero.{$format}"))
                {
                    $tr->save("app/output/ConfNossoNumero.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ConfNossoNumero.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ConfNossoNumero.{$format}");
                
                // shows the success message
                new TMessage('info', 'Report generated. Please, enable popups.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($formdata);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    
}
