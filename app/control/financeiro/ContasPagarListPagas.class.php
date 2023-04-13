<?php
/**
 * ContasPagarList Listing
 * @author  <your name here>
 */
class ContasPagarListPagas extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    private static $paginas = 1;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ContasPagar');
        $this->form->setFormTitle('Listagem de Despesas Liquidadas e a Liquidar');
        

        // create the form fields
        $id = new TEntry('id');
        
        $dt_liquidacao_inicio = new TDate('dt_liquidacao_inicio');
        $dt_liquidacao_fim = new TDate('dt_liquidacao_fim');
        
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');
        $tipo_pagamento_id = new TDBCombo('tipo_pagamento_id', 'facilitasmart', 'TipoPagamento', 'id', 'descricao', 'descricao');
        
        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        $conta_fechamento_id->placeholder = 'Obrigatório';
        $conta_fechamento_id->setTip('Escolha a conta de fechamento vinculada e este lançamento.');
        
        $dt_liquidacao_inicio->setDatabaseMask('yyyy-mm-dd');
        $dt_liquidacao_inicio->setMask('dd/mm/yyyy');
        $dt_liquidacao_fim->setDatabaseMask('yyyy-mm-dd');
        $dt_liquidacao_fim->setMask('dd/mm/yyyy');
        
        $id->setSize(100);
        $dt_liquidacao_inicio->setSize(150);
        $dt_liquidacao_fim->setSize(150);
        $tipo_pagamento_id->setSize(250);
        $numero_doc_pagamento->setSize(150);
        
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Doc Pagamento')], [$numero_doc_pagamento] ); 
        
       
        $this->form->addFields( [new TLabel('Dt Liquidação Início')], [$dt_liquidacao_inicio],
                                 [new TLabel('Dt Liquidação Fim')], [$dt_liquidacao_fim] );

        $this->form->addFields( [new TLabel('Tipo Pagamento')], [$tipo_pagamento_id],
                                [new TLabel('Conta Fechamento')], [$conta_fechamento_id] );
        
        $conta_fechamento_id->addValidation('Conta Fechamento', new TRequiredValidator); // required field
                
        $change_data = new TAction(array($this, 'onChangeData'));
        $dt_liquidacao_inicio->setExitAction($change_data);
        $dt_liquidacao_fim->setExitAction($change_data);
         
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasPagar_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction('Relatório de Liquidações',  new TAction(array($this, 'onLiquidacoes')), 'bs:plus-sign green');
        $this->form->addAction('Relatório de Liquidações Excel',  new TAction(array($this, 'onLiquidacoesExcel')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Informações Complementares', '<b>'.'Vencimento'.'</b><br>' . '{dt_vencimento}' 
        . '<br><b>'.'Doc. Pagamento'.'</b><br>' . '{numero_doc_pagamento}'
        . '<br><b>'.'Documento'.'</b><br>' . '{documento}');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'left');
        //$column_documento = new TDataGridColumn('documento', 'Documento', 'left');
        //$column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Dt Vencimento', 'left');
        //$column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Despesa', 'left');
        //$column_situacao = new TDataGridColumn('situacao', 'Situacao', 'left');
        //$column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Dt Pagamento', 'left');
        $column_dt_liquidacao = new TDataGridColumn('dt_liquidacao', 'Liquidação', 'left');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Vlr Pago', 'left');
        $column_conta_fechamento_id = new TDataGridColumn('conta_fechamento_id', 'Conta', 'right');
        //$column_tipo_pagamento_id = new TDataGridColumn('tipo_pagamento_id', 'Tipo Pagamento Id', 'right');
        //$column_numero_doc_pagamento = new TDataGridColumn('numero_doc_pagamento', 'Doc. Pag.', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_classe_id);
        //$this->datagrid->addColumn($column_documento);
        //$this->datagrid->addColumn($column_dt_vencimento);
        //$this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_descricao);
        //$this->datagrid->addColumn($column_situacao);
        //$this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_dt_liquidacao);
        $this->datagrid->addColumn($column_valor_pago);
        $this->datagrid->addColumn($column_conta_fechamento_id);
        //$this->datagrid->addColumn($column_tipo_pagamento_id);
        //$this->datagrid->addColumn($column_numero_doc_pagamento);

        $column_valor_pago->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });
        
        
        $column_classe_id->setTransformer( function($value, $object, $row) {
            $classe = new PlanoContas($value);
            return $classe->descricao;
        });
        
        $column_dt_liquidacao->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_valor_pago->setTransformer( function($value, $object, $row) {
            return 'R$ '.number_format($value, 2, ',', '.');
        });
        
        // create EDIT action
        //$action_edit = new TDataGridAction(array('ContasPagarForm', 'onEdit'));
        ////$action_edit->setUseButton(TRUE);
        ////$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add(TPanelGroup::pack('Conferência de liquidações ou a liquidar', $this->form));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->labelTotal, $this->pageNavigation));
       
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
    
    function onLiquidacoesExcel($param)
    {
        try
        {
            $string = new StringsUtil;
            
            $condominio_id = TSession::getValue('id_condominio') ;
            $dt_inicial = $string->formatDate($param['dt_liquidacao_inicio']);
            $dt_final = $string->formatDate($param['dt_liquidacao_fim']);
            $conta_fechamento = $param['conta_fechamento_id'];
            
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            $conn = TTransaction::get();
            $sql = "SELECT 
                     descricao, 
                     valor_pago, 
                     dt_liquidacao 
                     FROM contas_pagar 
                     WHERE condominio_id={$condominio_id}
                      and dt_liquidacao >= '{$dt_inicial}' 
                      and dt_liquidacao <= '{$dt_final}'
                      and conta_fechamento_id = '{$conta_fechamento}'  
                     order by dt_liquidacao";
            
            $objects = $conn->query($sql);
            //var_dump($objects);
            
            $format  = 'xls';
            
                       
            //var_dump($objects);
            //return;
            
            if ($objects)
            {
                // largura das colunas
                $widths = array(400,150,150);
                
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
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
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '10', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '9', 'I',  '#000000', '#A3A3A3');
                
                //$resumo = condominio_resumo;
                
                if($format == 'pdf')
                {
                    $fpdf = $tr->getNativeWriter();
                    $fpdf->AliasNbPages();
                    $fpdf->setHeaderCallback(array($this,'header'));
                    $fpdf->setFooterCallback(array($this,'footer'));
                    $this->header($fpdf);   
                }


                // qtd colunas
                $colunas = 3;
                
                $condominio = new Condominio($condominio_id);
                
                //cabecalho
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Descrição', 'left', 'title');
                $tr->addCell('Valor', 'center', 'title');
                $tr->addCell('Dt Liquidação', 'center', 'title');
                
                // verifica o nivel de acesso do usuario
                // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
                // * 0 - Desenvolvedor
                // * 1 - Administradora
                // * 2 - Gestor
                // * 3 - Portaria
                // * 4 - Morador
                $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
                foreach ($users as $user)
                {
                    $nivel_acesso = $user->nivel_acesso_inf;
                }
                

                // controls the background filling
                $colour = FALSE;
                 
                $total = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    
                    $tr->addRow();
                    $tr->addCell(utf8_decode($object['descricao']), 'center', $style);
                    $tr->addCell(number_format($object['valor_pago'], 2, ',', '.'), 'center', $style);
                    $tr->addCell($string->formatDateBR($object['dt_liquidacao']), 'center', $style);
                    
                    $total += $object['valor_pago'];
                    
                    $colour = !$colour;
                } 
              
                // total              
                $tr->addRow();
                $tr->addCell(number_format($total, 2, ',', '.'), 'right', 'footer', 3);
                    
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s A'), 'center', 'footer', $colunas);
                
                // stores the file
                if (!file_exists("app/output/PagamentosLiquidados.{$format}") OR is_writable("app/output/PagamentosLiquidados.{$format}"))
                {
                    $tr->save("app/output/PagamentosLiquidados.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/PagamentosLiquidados.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/PagamentosLiquidados.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
                
            }
            else
            {
                new TMessage('error', 'No records found');
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

    function onLiquidacoes($param)
    {
        try
        {
            $string = new StringsUtil;
            
            $condominio_id = TSession::getValue('id_condominio') ;
            $dt_inicial = $string->formatDate($param['dt_liquidacao_inicio']);
            $dt_final = $string->formatDate($param['dt_liquidacao_fim']);
            $conta_fechamento = $param['conta_fechamento_id'];
            
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            $conn = TTransaction::get();
            $sql = "SELECT 
                     numero_doc_pagamento, 
                     sum(valor_pago) as valor, 
                     dt_liquidacao 
                     FROM contas_pagar 
                     WHERE condominio_id={$condominio_id}
                      and dt_liquidacao >= '{$dt_inicial}' 
                      and dt_liquidacao <= '{$dt_final}'
                      and conta_fechamento_id = '{$conta_fechamento}'  
                     group by numero_doc_pagamento, dt_liquidacao  
                     order by dt_liquidacao";
            
            $objects = $conn->query($sql);
            //var_dump($objects);
            
            $format  = 'pdf';
            
                       
            //var_dump($objects);
            //return;
            
            if ($objects)
            {
                // largura das colunas
                $widths = array(200,200,200);
                
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
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
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '10', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '9', 'I',  '#000000', '#A3A3A3');
                
                //$resumo = condominio_resumo;
                
                if($format == 'pdf')
                {
                    $fpdf = $tr->getNativeWriter();
                    $fpdf->AliasNbPages();
                    $fpdf->setHeaderCallback(array($this,'header'));
                    $fpdf->setFooterCallback(array($this,'footer'));
                    $this->header($fpdf);   
                }


                // qtd colunas
                $colunas = 3;
                
                $condominio = new Condominio($condominio_id);
                
                //cabecalho
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', $colunas);
                                                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Documento Pagamento', 'center', 'title');
                $tr->addCell('Valor', 'center', 'title');
                $tr->addCell('Dt Liquidação', 'center', 'title');
                
                // verifica o nivel de acesso do usuario
                // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
                // * 0 - Desenvolvedor
                // * 1 - Administradora
                // * 2 - Gestor
                // * 3 - Portaria
                // * 4 - Morador
                $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
                foreach ($users as $user)
                {
                    $nivel_acesso = $user->nivel_acesso_inf;
                }
                

                // controls the background filling
                $colour = FALSE;
                 
                $total = 0;
                                       
                // data rows
                foreach ($objects as $object)
                {
                                   
                    $style = $colour ? 'datap' : 'datai';
                    
                    $tr->addRow();
                    $tr->addCell($object['numero_doc_pagamento'], 'center', $style);
                    $tr->addCell(number_format($object['valor'], 2, ',', '.'), 'center', $style);
                    $tr->addCell($string->formatDateBR($object['dt_liquidacao']), 'center', $style);
                    
                    $total += $object['valor'];
                    
                    $colour = !$colour;
                } 
              
                // total              
                $tr->addRow();
                $tr->addCell(number_format($total, 2, ',', '.'), 'right', 'footer', 3);
                    
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s A'), 'center', 'footer', $colunas);
                
                // stores the file
                if (!file_exists("app/output/PagamentosLiquidados.{$format}") OR is_writable("app/output/PagamentosLiquidados.{$format}"))
                {
                    $tr->save("app/output/PagamentosLiquidados.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/PagamentosLiquidados.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/PagamentosLiquidados.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
                
            }
            else
            {
                new TMessage('error', 'No records found');
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

    public function header($pdf) 
    { 
        // Cor do texto
        $pdf->SetTextColor(0, 0, 0);
        
        // 90 x 97
        $pdf->Image('app/images/logo.png',110,6);

        $pdf->Ln(60); 


        $pdf->SetFont('Arial','B',15); 
        // Move to the right 
        $pdf->Cell(80); 
        // Title 
        $pdf->Cell(130,10, utf8_decode('Relação de Documentos de Liquidação') ,0,0,'C'); 
        // Line break 
        $pdf->Ln(20); 
    }

    public function footer($pdf) 
    { 
        $numeroPagina = self::$paginas;
        $pdf->SetY(-40);
        $pdf->SetFont('Arial','B',15); 
        // Move to the right 
        $pdf->Cell(110); 
        // Title 
        $pdf->PageNo();
        $pdf->Cell(0,10, utf8_decode("Página: {$numeroPagina} /{nb}") ,0,0,'R');

        // Line break 
        $pdf->Ln(20); 
        self::$paginas++;
    }

    public static function onChangeData($param)
    {
      
        $obj = new StdClass;
        $string = new StringsUtil;
        
        if(strlen($param['dt_liquidacao_inicio']) == 10 && strlen($param['dt_liquidacao_fim']) == 10)
        {
        
            if(strtotime($string->formatDate($param['dt_liquidacao_fim'])) < strtotime($string->formatDate($param['dt_liquidacao_inicio'])))
            {
    	        $obj->data_atividade_final = ''; 
    	        new TMessage('error', 'Data de liquidacao final menor que data de liquidacao inicial'); 
            }
        
        }
        
        TForm::sendData('form_search_ContasPagar', $obj, FALSE, FALSE);
       
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
            $object = new ContasPagar($key); // instantiates the Active Record
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
        $string = new StringsUtil;
        
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('ContasPagarList_filter_id',   NULL);
        TSession::setValue('ContasPagarList_filter_dt_liquidacao',   NULL);
        TSession::setValue('ContasPagarList_filter_numero_doc_pagamento',   NULL);
        TSession::setValue('ContasPagarList_filter_tipo_pagamento',   NULL);
        TSession::setValue('ContasPagarList_filter_conta_fechamento_id',   NULL);
        
        //$data->dt_liquidacao_inicio = $string->formatDate($data->dt_liquidacao_inicio);
        //$data->dt_liquidacao_fim = $string->formatDate($data->dt_liquidacao_fim);
            
        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "{$data->id}"); // create the filter
            TSession::setValue('ContasPagarList_filter_id',   $filter); // stores the filter in the session
        }
        
        // obrigatorio if (isset($data->conta_fechamento_id) AND ($data->conta_fechamento_id)) {
            $filter = new TFilter('conta_fechamento_id', '=', "{$data->conta_fechamento_id}"); // create the filter
            TSession::setValue('ContasPagarList_filter_conta_fechamento_id',   $filter); // stores the filter in the session
        //}

        if (isset($data->dt_liquidacao_inicio) AND ($data->dt_liquidacao_inicio)) {
            $filter = new TFilter('dt_liquidacao', 'between', "{$data->dt_liquidacao_inicio}", "{$data->dt_liquidacao_fim}"); // create the filter
            TSession::setValue('ContasPagarList_filter_dt_liquidacao',   $filter); // stores the filter in the session
        }


        if (isset($data->numero_doc_pagamento) AND ($data->numero_doc_pagamento)) {
            $filter = new TFilter('numero_doc_pagamento', '=', "{$data->numero_doc_pagamento}"); // create the filter
            TSession::setValue('ContasPagarList_filter_numero_doc_pagamento',   $filter); // stores the filter in the session
        }

        if (isset($data->tipo_pagamento_id) AND ($data->tipo_pagamento_id)) {
            $filter = new TFilter('tipo_pagamento_id', '=', "{$data->tipo_pagamento_id}"); // create the filter
            TSession::setValue('ContasPagarList_filter_tipo_pagamento',   $filter); // stores the filter in the session
        }

                    
        //$data->dt_liquidacao_inicio = $string->formatDateBR($data->dt_liquidacao_inicio);
        //$data->dt_liquidacao_fim = $string->formatDateBR($data->dt_liquidacao_fim);
           
           
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('ContasPagar_filter_data', $data);
        
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
            $string = new StringsUtil;

            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasPagar
            $repository = new TRepository('ContasPagar');
            $limit = 100;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_liquidacao, mes_ref';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // pagas
            $criteria->add(new TFilter('situacao', '=', '1')); // add the session filter

            // verifica o nivel de acesso do usuario para filtrar so as unidades do condominio
            // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
            // * 0 - Desenvolvedor
            // * 1 - Administradora
            // * 2 - Gestor
            // * 3 - Portaria
            // * 4 - Morador
            $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
            foreach ($users as $user)
            {
                if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                    $criteria->add(new TFilter('condominio_id', '=', $user->condominio_id)); // add the session filter
                }else {
                    $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter 
                }
            }
            
            if (TSession::getValue('ContasPagarList_filter_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasPagarList_filter_conta_fechamento_id')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_conta_fechamento_id')); // add the session filter
            }
            
            if (TSession::getValue('ContasPagarList_filter_tipo_pagamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_tipo_pagamento')); // add the session filter
            }

            if (TSession::getValue('ContasPagarList_filter_dt_liquidacao')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_dt_liquidacao')); // add the session filter
            }


            if (TSession::getValue('ContasPagarList_filter_numero_doc_pagamento')) {
                $criteria->add(TSession::getValue('ContasPagarList_filter_numero_doc_pagamento')); // add the session filter
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
                    
                    $conta = new ContaFechamento( $object->conta_fechamento_id );
                    $object->conta_fechamento_id = $conta->descricao;
                    $object->descricao = $object->descricao . ' ' .
                                         'Doc. Pag. ' . $object->numero_doc_pagamento . 
                                         ' ' . $string->formatDateBR($object->dt_vencimento);
                                           
                    
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
            $object = new ContasPagar($key, FALSE); // instantiates the Active Record
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
}
