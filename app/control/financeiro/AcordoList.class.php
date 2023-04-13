<?php
/**
 * AcordoList Listing
 * @author  <your name here>
 */
class AcordoList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    private static $database = 'facilitasmart';
    private static $activeRecord = 'Acordo';
    private static $primaryKey = 'id';
    private static $formName = 'form_AcordoList';

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);

        // define the form title
        $this->form->setFormTitle('Listagem de Acordos Criados');

        $id = new TEntry('id');
        $dt_criado = new TDateTime('dt_criado');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        
        $unidade = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{proprietario_nome}', 'descricao', $criteria);
        
        //$vendedor = new TEntry('vendedor');

        $dt_criado->setDatabaseMask('yyyy-mm-dd hh:ii');
        $dt_criado->setMask('dd/mm/yyyy hh:ii');

        $id->setSize(100);
        $dt_criado->setSize(150);
        $unidade->setSize('100%');
        //$vendedor->setSize('100%');

        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Data Criação:')],[$dt_criado]);
        $this->form->addFields([new TLabel('Unidade:')],[$unidade]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsearch = $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary');

        $btn_onexportcsv = $this->form->addAction('Exportar como CSV', new TAction([$this, 'onExportCsv']), 'fa:file-text-o #000000');

        //$btn_onshow = $this->form->addAction('Consultar', new TAction([$this, 'onView']), 'fa:fax #69aa46');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_id = new TDataGridColumn('id', 'Id', 'center' , '77.8px');
        $column_unidade = new TDataGridColumn('unidade_id', 'Unidade', 'center');
        //$column_vendedor = new TDataGridColumn('vendedor', 'Vendedor', 'left');
        $column_dt_criacao = new TDataGridColumn('data_dia', 'Data Criação', 'left');
        $column_parcela = new TDataGridColumn('parcelas', 'Parcela', 'center');
        $column_valor_total_transformed = new TDataGridColumn('valor_projetado', 'Valor Acordo', 'right');

        $column_valor_total_transformed->setTransformer(function($value, $object, $row)
        {
            if (!is_numeric($value))
            {
                return "R$ --";
            }

            $class = 'label-primary';
            //if ($value > 5000)
            //{
            //    $class = 'label-danger';
            //}
            $value = "R$ " . number_format($value, 2, ",", ".");
            return '<span style="min-width: 200px" class="label '.$class.'">'. $value.'</span>';
        });

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_unidade);
        $this->datagrid->addColumn($column_parcela);
        $this->datagrid->addColumn($column_dt_criacao);
        $this->datagrid->addColumn($column_valor_total_transformed);

        $action1 = new TDataGridAction(array('AcordoForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:edit blue');
        $action1->setField('id');
       
        $this->datagrid->addAction($action1);
        
        $action_onEdit = new TDataGridAction(array($this, 'onView'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel('Consultar');
        $action_onEdit->setImage('fa:building green');
        $action_onEdit->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array($this, 'onDelete'));
        $action_onDelete->setUseButton(false);
        $action_onDelete->setButtonClass('btn btn-default');
        $action_onDelete->setLabel('Cancelar');
        $action_onDelete->setImage('far:trash-alt red');
        $action_onDelete->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onDelete);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);

        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }

    /*
    *
    */
    function onView($param = null)
    {
        
        try
        {
            $string = new StringsUtil;
                                    
            $key = $param['key'];
            
            // acordo
            TTransaction::open('facilitasmart');
            $conn = TTransaction::get(); 
            $sql = "SELECT * FROM acordo where id = ".$key;
            $acordos = $conn->query($sql);
               
            foreach ($acordos as $acordo) 
            { 
                $unidade_acordo = $acordo['unidade_id'];
                $acordo_id  = $acordo['id'];
                $acordo_parcelas  = $acordo['parcelas'];
                $data_base_acordo  = $acordo['data_base_acordo'];
                $valor_projetado  = $acordo['valor_projetado'];
                $valor_lancado  = $acordo['valor_lancado'];
                $acrescimo  = $acordo['acrescimo'];
                $desconto  = $acordo['desconto'];
                $multa  = $acordo['multa'];
                $juros  = $acordo['juros'];
                $correcao  = $acordo['correcao'];
                 
            } 
           
           
            // proprietario
            $conn = TTransaction::get();
            $result = $conn->query("select b.nome from unidade as a
                                inner join pessoa as b 
                                on a.proprietario_id =  b.id
                                where a.id = {$unidade_acordo}");
      
        
            $proprietario ='';
        
            foreach ($result as $row)
            {
                $proprietario = $row['nome'];
            }
            
            // descricao da unidades
            $conn = TTransaction::get();
            $result = $conn->query("select descricao 
                                from unidade 
                                where id = {$unidade_acordo}");
      
            $descricao ='';
        
            foreach ($result as $row)
            {    
                $descricao = $row['descricao'];
            }
           
            $format  = 'PDF';
                               
            if ($acordos)
            {
                //$widths = array(30,45,55,80,70,50,60,40,40,60);
                $widths = array(55,55,55,55,55,55,55,55,55,55);
                $tr = new TTableWriterPDF($widths);
           
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#000000', '#EEEEEE');
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '8', 'I',  '#000000', '#A3A3A3');
                
                $tr->addRow();
                $tr->addCell('Facilita Home Service - Facilita Smart ', 'center', 'title', 10);
                
                $tr->addRow();
                $tr->addCell(utf8_decode(TSession::getValue('resumo')), 'center', 'title', 10);
                
                $tr->addRow();
                $tr->addCell('Acordo', 'center', 'title', 10);
                
                $tr->addRow();
                $tr->addCell('Detalhamento do Acordo - No. ' . $param['id'], 'center', 'header', 10);
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
               
                $tr->addRow();
                $tr->addCell('Unidade : ' . $unidade_acordo . ' - ' . $descricao . ' - ' . $proprietario, 'left', 'title', 10);
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                
                //cabecalho 1a parte
                $tr->addRow();
                $tr->addCell('No.', 'center', 'header');
                $tr->addCell('Parcelas', 'left', 'header');
                $tr->addCell('Data Base', 'center', 'header');
                $tr->addCell('Valor Base', 'center', 'header');
                $tr->addCell('Multa', 'center', 'header');
                $tr->addCell('Juros', 'center', 'header');
                $tr->addCell('Correção', 'center', 'header');
                $tr->addCell('Acréscimo', 'center', 'header');
                $tr->addCell('Desconto', 'center', 'header');
                $tr->addCell('Valor Acordo', 'center', 'header');
                
                $tr->addRow();
                $tr->addCell($acordo_id, 'center', 'datap');
                $tr->addCell($acordo_parcelas, 'center', 'datap');
                $tr->addCell($string->formatDateBR($data_base_acordo), 'center', 'datap');
                $tr->addCell(number_format($valor_lancado, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($multa, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($juros, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($correcao, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($acrescimo, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($desconto, 2, ',', '.'), 'right', 'datap');
                $tr->addCell(number_format($valor_projetado, 2, ',', '.'), 'right', 'datap');
                
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                $tr->addRow();
                
                $tr->addCell('RELAÇÃO DE CONTAS ORIGINAIS', 'center', 'header', 10);
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                
                // titulos originais
                $conn = TTransaction::get(); 
                $sql = "SELECT * FROM contas_receber where numero_acordo = ".$param['id']." and situacao = 2 order by dt_vencimento";
                $titulos = $conn->query($sql);
                $colour = FALSE;
                 
                $tr->addRow();
                $tr->addCell('Id', 'center', 'header');
                $tr->addCell('Mês Ref', 'center', 'header');
                $tr->addCell('Vencimento', 'center', 'header');
                $tr->addCell('Classe', 'left', 'header',2);
                $tr->addCell('Cobrança', 'center', 'header');
                $tr->addCell('Valor', 'right','header',4);
                
                $total = 0;
                
                foreach ($titulos as $titulo) 
                {
                    $colour = !$colour;
                    $style = $colour ? 'datap' : 'datai';
                
                    $tr->addRow();
                    $tr->addCell($titulo['id'], 'center', $style);
                    $tr->addCell($titulo['mes_ref'], 'center', $style);
                    $tr->addCell($string->formatDateBR($titulo['dt_vencimento']), 'center', $style);
                    
                    $classificacao = new PlanoContas($titulo['classe_id']);
                    
                    $tr->addCell($classificacao->descricao, 'left', $style,2);
                    $tr->addCell($titulo['cobranca'], 'center', $style);
                    $tr->addCell(number_format($titulo['valor'], 2, ',', '.'), 'right', $style,4);
                   
                    $total += $titulo['valor'];
                }
                
                $tr->addRow();
                $tr->addCell('Total ' . number_format($total, 2, ',', '.'), 'right', 'header', 10);
                
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                $tr->addRow();
                $tr->addCell('CONTAS GERADAS PELO ACORDO', 'center', 'header', 10);
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                
                // parcelas
                $conn = TTransaction::get(); 
                $sql = "SELECT * FROM contas_receber where numero_acordo = ".$param['id']. " and situacao != 2 order by dt_vencimento";
                $parcelas = $conn->query($sql);
                        
                $tr->addRow();
                $tr->addCell('Id', 'center', 'header');
                $tr->addCell('Mês Ref', 'center', 'header');
                $tr->addCell('Parcela', 'center', 'header');
                $tr->addCell('Vencimento', 'center', 'header');
                $tr->addCell('Classe', 'left', 'header',2);
                $tr->addCell('Cobrança', 'center', 'header');
                $tr->addCell('Valor', 'right','header');
                $tr->addCell('Dt Pagam.', 'right','header');
                $tr->addCell('Valor Pago', 'right','header');
                 
                $total = 0;
                $total_pago = 0;
                       
                foreach ($parcelas as $parcela) 
                {
                    $colour = !$colour;
                    $style = $colour ? 'datap' : 'datai';
                
                    $tr->addRow();
                    $tr->addCell($parcela['id'], 'center', $style);
                    $tr->addCell($parcela['mes_ref'], 'center', $style);
                    $tr->addCell($parcela['parcela'], 'center', $style);
                    $tr->addCell($string->formatDateBR($parcela['dt_vencimento']), 'center', $style);
                    
                    $classificacao = new PlanoContas($parcela['classe_id']);
                    
                    $tr->addCell($classificacao->descricao, 'left', $style,2);
                    $tr->addCell($parcela['cobranca'], 'center', $style);
                    $tr->addCell(number_format($parcela['valor'], 2, ',', '.'), 'right', $style);
                    $tr->addCell($string->formatDateBR($parcela['dt_pagamento']), 'right', $style);
                    $tr->addCell(number_format($parcela['valor_pago'], 2, ',', '.'), 'right', $style);
                    
                    $total += $parcela['valor'];
                    $total_pago += $parcela['valor_pago'];
                }  
                
                $tr->addRow();
                $tr->addCell('Tot. Parcelas ' . number_format($total, 2, ',', '.'), 'right', 'header', 8);
                $tr->addCell('Tot.Pago : ' . number_format($total_pago, 2, ',', '.'), 'right', 'header', 2);
                
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 10);
                
                // stores the file
                if (!file_exists("app/output/DetalheAcordo.pdf") OR is_writable("app/output/DetalheAcordo.pdf"))
                {
                    $tr->save("app/output/DetalheAcordo.pdf");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/DetalheAcordo.pdf");
                }
                
                // open the report file
                parent::openFile("app/output/DetalheAcordo.pdf");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por Favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrado.');
            }
            
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
    
    public function onExportCsv($param = null)
    {
        try
        {
            $this->onSearch();

            TTransaction::open(self::$database); // open a transaction
            $repository = new TRepository(self::$activeRecord); // creates a repository for Customer
            $criteria = new TCriteria; // creates a criteria

            if($filters = TSession::getValue(__CLASS__.'_filters'))
            {
                foreach ($filters as $filter)
                {
                    $criteria->add($filter);
                }
            }

            $records = $repository->load($criteria); // load the objects according to criteria
            if ($records)
            {
                $file = 'tmp/'.uniqid().'.csv';
                $handle = fopen($file, 'w');
                $columns = $this->datagrid->getColumns();

                $csvColumns = [];
                foreach($columns as $column)
                {
                    $csvColumns[] = $column->getLabel();
                }
                fputcsv($handle, $csvColumns, ';');

                foreach ($records as $record)
                {
                    $csvColumns = [];
                    foreach($columns as $column)
                    {
                        $name = $column->getName();
                        $csvColumns[] = $record->{$name};
                    }
                    fputcsv($handle, $csvColumns, ';');
                }
                fclose($handle);

                TPage::openFile($file);
            }
            else
            {
                new TMessage('info', _t('No records found'));
            }

            TTransaction::close(); // close the transaction
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

/**
     * Ask before deletion
     */
    public function onDelete($param = null)
    {
        // define the delete action
        $action = new TAction(array($this, 'Cancela'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Deseja Cancelar o acordo criado ?', $action);
    }
    
    /**
     * Cancela acordo
     */
    public function Cancela($param)
    {
       
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction with database
                        
            $key=$param['key']; // get the parameter $key
            $object = new Acordo($key, FALSE); // instantiates the Active Record
            
            $acordo_id = $object->id;
            
            //criando log 
            TTransaction::setLogger(new TLoggerTXT('log/acordoDel'.$acordo_id.'('.date('Y-m-d H:i:s').')'.'.txt')); 
            $user = TSession::getValue('login');
            TTransaction::Log($user . ' ' . ' - Acordo excluído, excluído No. ' . $object->id);
                
            // apaga o acordo criado
            $object->delete(); // deletes the object from the database
            
            // verifica se existe alguma parcela paga
            // parcelas
            $conn = TTransaction::get(); 
            $sql = "SELECT * FROM contas_receber where numero_acordo = ".$acordo_id." and parcela != '0'";
            $parcelas = $conn->query($sql);
                       
            foreach ($parcelas as $parcela) 
            {    
              
                if ( $parcela['valor_pago'] > 0 or $parcela['situacao'] == 1 ) {
                    new TMessage('error', '<b>Cancelado</b>  ' . 'Existe(m) parcela(s) paga(s), não é possível cancelar o acordo. '); // shows the exception error message
                    TTransaction::close(); // close the transaction
                    return;
                }
                 
            }
        
            // apaga os lancamentos criados pelo acordo
            $conn = TTransaction::get(); 
            $sql = "SELECT * FROM contas_receber where numero_acordo = ".$acordo_id. " and parcela != '0'";
            $parcelas = $conn->query($sql);
            
            foreach ($parcelas as $parcela) 
            {
                //criando log 
                TTransaction::setLogger(new TLoggerTXT('log/contas_receberDel'.$parcela['id'].'('.date('Y-m-d H:i:s').')'.'.txt')); 
                $user = TSession::getValue('login');
                TTransaction::Log($user . ' ' . ' - Titulo gerado pelo acordo, excluído No. ' . $parcela['id']);
                
                $titulo = new ContasReceber($parcela['id'], FALSE); // instantiates the Active Record
                $titulo->delete(); // deletes the object from the database
                
            }        
        
            // volta os titulos originais a situação de em aberto e cobranca 1
            $conn = TTransaction::get(); 
            $sql = "SELECT * FROM contas_receber where numero_acordo = ".$acordo_id." and situacao = 2";
            $titulos = $conn->query($sql);
            
            foreach ($titulos as $titulo) 
            {
                $lancamento = new ContasReceber($titulo['id'], FALSE); // instantiates the Active Record
                
                //criando log 
                TTransaction::setLogger(new TLoggerTXT('log/contas_receberUp'.$titulo['id'].'('.date('Y-m-d H:i:s').')'.'.txt')); 
                $user = TSession::getValue('login');
                TTransaction::Log($user . ' ' . ' - Alterado título para em aberto, numero acordo = null e classe id = 1. ' . $lancamento->id);
                
                $lancamento->situacao = '0'; // em acordo = 2
                $lancamento->numero_acordo = '';
                $lancamento->dt_ultima_alteracao = date('Y-m-d');
                $lancamento->store(); // update the object in the database
            } 
        
           
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', 'Acordo Cancelado !'); // success message
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
        $filters = [];

        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filters', NULL);

        if (isset($data->id) AND ($data->id))
        {

            $filters[] = new TFilter('id', '=', $data->id);// create the filter
        }
        if (isset($data->dt_criado) AND ($data->dt_criado))
        {

            $filters[] = new TFilter('data_dia', '=', $data->dt_criado);// create the filter
        }
        if (isset($data->unidade) AND ($data->unidade))
        {

            $filters[] = new TFilter('unidade_id', '=', "{$data->unidade}");// create the filter
        }
        //if (isset($data->vendedor) AND ($data->vendedor))
        //{

           // $filters[] = new TFilter('vendedor', 'like', "%{$data->vendedor}%");// create the filter
        //}

        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_data', $data);
        TSession::setValue(__CLASS__.'_filters', $filters);

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
            // open a transaction with database 'exemplos'
            TTransaction::open(self::$database);

            // creates a repository for Venda
            $repository = new TRepository(self::$activeRecord);
            $limit = 20;
            // creates a criteria
            $criteria = new TCriteria;

            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }

            if (empty($param['direction']))
            {
                $param['direction'] = 'desc';
            }

            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            if($filters = TSession::getValue(__CLASS__.'_filters'))
            {
                foreach ($filters as $filter)
                {
                    $criteria->add($filter);
                }
            }

             // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $unidade = new Unidade( $object->unidade_id );
                    //$object->unidade_id = '(' + $unidade->id + ') ' + $unidade->bloco_quadra + '-' + $unidade->descricao; 
                    $object->unidade_id = $unidade->descricao;
                    
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

    public function onShow()
    {

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
}

